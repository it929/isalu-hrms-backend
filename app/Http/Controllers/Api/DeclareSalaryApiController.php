<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class DeclareSalaryApiController extends Controller
{
    /**
     * GET /api/nextjs/payroll/declare-salary
     * Fetch existing salary structures with declared salary and staff information.
     */
    public function index(Request $request)
    {
        try {
            $search = trim($request->input('search', ''));
            $query = DB::table('salary_structures as ss')
                ->join('tblper as p', 'p.ID', '=', 'ss.staffId')
                ->select(
                    'ss.id',
                    'ss.staffId',
                    'ss.basic_salary',
                    'ss.declare_salary',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames'
                );

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('p.fileNo', 'like', "%{$search}%")
                      ->orWhere('p.surname', 'like', "%{$search}%")
                      ->orWhere('p.first_name', 'like', "%{$search}%")
                      ->orWhere('p.othernames', 'like', "%{$search}%");
                });
            }

            $records = $query->orderBy('ss.id', 'desc')->get()->map(function ($row) {
                $row->name = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                return $row;
            });

            return response()->json([
                'status' => 'success',
                'data' => $records
            ]);
        } catch (\Throwable $th) {
            Log::error('DeclareSalaryAPI index: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/declare-salary
     * Save/update a single staff member's declared salary if they have a salary structure.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'staffId' => 'required|integer',
                'declare_salary' => 'required|numeric|min:0',
            ]);

            // Ensure the staff member exists in salary_structures
            $structureExists = DB::table('salary_structures')->where('staffId', $validated['staffId'])->exists();
            if (!$structureExists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The selected staff member has no salary structure setup.'
                ], 422);
            }

            // Update declare_salary column in salary_structures
            DB::table('salary_structures')
                ->where('staffId', $validated['staffId'])
                ->update([
                    'declare_salary' => $validated['declare_salary'],
                ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Declared salary updated successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('DeclareSalaryAPI store: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/declare-salary/import
     * Bulk update declared salary via Excel/CSV matching staff by staffId.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file'
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'file' => ['The file must be a file of type: xlsx, xls, csv.']
                ]
            ], 422);
        }

        try {
            $data = Excel::toArray([], $request->file('file'))[0];
            if (empty($data)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The uploaded file is empty.'
                ], 422);
            }

            // Headers mapping to match column order
            $headers = array_map(function ($h) {
                return strtolower(trim($h));
            }, $data[0]);

            $staffIdIndex = -1;
            $declareSalaryIndex = -1;

            foreach ($headers as $index => $header) {
                if (strpos($header, 'staff') !== false || strpos($header, 'id') !== false) {
                    if ($staffIdIndex === -1) $staffIdIndex = $index;
                } elseif (strpos($header, 'declare') !== false || strpos($header, 'salary') !== false) {
                    if ($declareSalaryIndex === -1) $declareSalaryIndex = $index;
                }
            }

            // Fallbacks to default column indices
            if ($staffIdIndex === -1) $staffIdIndex = 0;
            if ($declareSalaryIndex === -1) $declareSalaryIndex = 1;

            unset($data[0]); // Remove the header row

            $updatedCount = 0;
            $warnings = [];

            DB::beginTransaction();

            foreach ($data as $rowIndex => $row) {
                // Skip empty row or if staffId is missing
                if (!isset($row[$staffIdIndex]) || trim($row[$staffIdIndex]) === '') {
                    continue;
                }

                $staffId = intval(trim($row[$staffIdIndex]));
                $declareVal = (float) (trim($row[$declareSalaryIndex] ?? 0.00));

                // Check if staff has salary structure
                $structureExists = DB::table('salary_structures')->where('staffId', $staffId)->exists();
                if (!$structureExists) {
                    // Check if staff exists in tblper to give a better warning
                    $staff = DB::table('tblper')->where('ID', $staffId)->first();
                    if ($staff) {
                        $fullName = trim("{$staff->surname} {$staff->first_name} {$staff->othernames}");
                        $warnings[] = "Row " . ($rowIndex + 1) . ": Staff '{$fullName}' (ID: {$staffId}) has no salary structure setup.";
                    } else {
                        $warnings[] = "Row " . ($rowIndex + 1) . ": Staff with identifier '{$staffId}' does not exist.";
                    }
                    continue;
                }

                // Update declare_salary
                DB::table('salary_structures')
                    ->where('staffId', $staffId)
                    ->update([
                        'declare_salary' => $declareVal,
                    ]);

                $updatedCount++;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Successfully processed {$updatedCount} records.",
                'updated_count' => $updatedCount,
                'warnings' => $warnings
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('DeclareSalaryAPI import: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
