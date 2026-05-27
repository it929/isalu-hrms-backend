<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class PensionActivationApiController extends Controller
{
    /**
     * GET /api/nextjs/payroll/pension-activation
     * Fetch active staff along with their pension activation status.
     */
    public function index(Request $request)
    {
        try {
            $search = trim($request->input('search', ''));
            $query = DB::table('tblper as p')
                ->leftJoin('salary_structures as ss', 'ss.staffId', '=', 'p.ID')
                ->where('p.rank', '!=', 2) // Exclude terminated/retired staff
                ->select(
                    'p.ID as id',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    DB::raw('COALESCE(ss.pen_act, 0) as pen_act'),
                    DB::raw('(
                        COALESCE(ss.basic_salary, 0.00) +
                        COALESCE(ss.housing_allowance, 0.00) +
                        COALESCE(ss.transport_allowance, 0.00) +
                        COALESCE(ss.medical_allowance, 0.00) +
                        COALESCE(ss.utility_allowance, 0.00) +
                        COALESCE(ss.meal_allowance, 0.00)
                    ) as basic_salary')
                );

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('p.fileNo', 'like', "%{$search}%")
                      ->orWhere('p.surname', 'like', "%{$search}%")
                      ->orWhere('p.first_name', 'like', "%{$search}%")
                      ->orWhere('p.othernames', 'like', "%{$search}%");
                });
            }

            $records = $query->orderBy('p.surname', 'asc')->get()->map(function ($row) {
                $row->name = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                $row->pen_act = (int) $row->pen_act;
                $row->basic_salary = (float) $row->basic_salary;
                return $row;
            });

            return response()->json([
                'status' => 'success',
                'data' => $records
            ]);
        } catch (\Throwable $th) {
            Log::error('PensionActivationAPI index: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/pension-activation/toggle
     * Toggle a single staff member's pension activation status.
     */
    public function togglePension(Request $request)
    {
        try {
            $validated = $request->validate([
                'staff_id' => 'required|integer|exists:tblper,ID',
                'pen_act'  => 'required|integer|in:0,1'
            ]);

            $staffId = $validated['staff_id'];
            $penAct = $validated['pen_act'];

            $existing = DB::table('salary_structures')->where('staffId', $staffId)->first();

            if ($existing) {
                DB::table('salary_structures')->where('staffId', $staffId)->update([
                    'pen_act' => $penAct,
                    // If pen_act is activated and pension_rate is 0, let's keep it as is or default
                ]);
            } else {
                // Insert a default structure with active/inactive pension status
                DB::table('salary_structures')->insert([
                    'staffId' => $staffId,
                    'basic_salary' => 0.00,
                    'declare_salary' => 0.00,
                    'housing_allowance' => 0.00,
                    'transport_allowance' => 0.00,
                    'medical_allowance' => 0.00,
                    'utility_allowance' => 0.00,
                    'meal_allowance' => 0.00,
                    'pension_rate' => 0.00,
                    'tax_rate' => 0.00,
                    'pen_act' => $penAct,
                    'created_at' => now(),
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Pension status updated successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('PensionActivationAPI togglePension: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/pension-activation/import
     * Bulk activate pension for multiple staff via Excel/CSV spreadsheet.
     */
    public function importPension(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file'
        ]);

        $file = $request->file('excel_file');
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid file format. Only .xlsx, .xls, and .csv files are allowed.'
            ], 422);
        }

        try {
            $rows = Excel::toArray([], $request->file('excel_file'))[0];
            if (empty($rows) || count($rows) <= 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The uploaded spreadsheet is empty or contains no records.'
                ], 422);
            }

            // Normalize headers from the first row
            $headers = array_map(function ($h) {
                return strtolower(trim((string)$h));
            }, $rows[0]);

            $staffIdIndex = -1;
            $fileNoIndex = -1;

            foreach ($headers as $index => $header) {
                if (strpos($header, 'staff id') !== false || strpos($header, 'staff_id') !== false || $header === 'id' || $header === 'staffid') {
                    $staffIdIndex = $index;
                } elseif (strpos($header, 'file') !== false || $header === 'fileno') {
                    $fileNoIndex = $index;
                }
            }

            // Fallbacks if header names are not exact
            if ($staffIdIndex === -1 && $fileNoIndex === -1) {
                // Try checking by column positions (0 = Staff ID / File No)
                $staffIdIndex = 0;
            }

            $activatedCount = 0;
            $warnings = [];

            DB::beginTransaction();

            for ($r = 1; $r < count($rows); $r++) {
                $row = $rows[$r];

                // Skip empty row
                $isEmptyRow = true;
                foreach ($row as $cell) {
                    if (trim((string)$cell) !== '') {
                        $isEmptyRow = false;
                        break;
                    }
                }
                if ($isEmptyRow) continue;

                $staff = null;

                // 1. Try matching by Staff ID (if index is valid and value is numeric)
                if ($staffIdIndex !== -1 && isset($row[$staffIdIndex]) && is_numeric(trim($row[$staffIdIndex]))) {
                    $staffId = intval(trim($row[$staffIdIndex]));
                    $staff = DB::table('tblper')->where('ID', $staffId)->first();
                }

                // 2. Try matching by File Number if not matched by ID
                if (!$staff && $fileNoIndex !== -1 && isset($row[$fileNoIndex]) && trim($row[$fileNoIndex]) !== '') {
                    $fileNo = trim($row[$fileNoIndex]);
                    $staff = DB::table('tblper')->where('fileNo', $fileNo)->first();
                }

                // 3. Fallback: Check if the first column itself contains a File Number (e.g. "FILE-123" or similar)
                if (!$staff && $staffIdIndex !== -1 && isset($row[$staffIdIndex]) && trim($row[$staffIdIndex]) !== '') {
                    $val = trim($row[$staffIdIndex]);
                    $staff = DB::table('tblper')->where('fileNo', $val)->first();
                }

                if (!$staff) {
                    $val = ($staffIdIndex !== -1 && isset($row[$staffIdIndex])) ? trim($row[$staffIdIndex]) : '';
                    $val2 = ($fileNoIndex !== -1 && isset($row[$fileNoIndex])) ? trim($row[$fileNoIndex]) : '';
                    $searchVal = $val !== '' ? $val : ($val2 !== '' ? $val2 : "Row " . ($r + 1));
                    $warnings[] = "Row " . ($r + 1) . ": Staff with identifier '{$searchVal}' not found in database.";
                    continue;
                }

                // Activate pension in salary_structures
                $existing = DB::table('salary_structures')->where('staffId', $staff->ID)->first();

                if ($existing) {
                    DB::table('salary_structures')->where('staffId', $staff->ID)->update([
                        'pen_act' => 1
                    ]);
                } else {
                    DB::table('salary_structures')->insert([
                        'staffId' => $staff->ID,
                        'basic_salary' => 0.00,
                        'declare_salary' => 0.00,
                        'housing_allowance' => 0.00,
                        'transport_allowance' => 0.00,
                        'medical_allowance' => 0.00,
                        'utility_allowance' => 0.00,
                        'meal_allowance' => 0.00,
                        'pension_rate' => 0.00,
                        'tax_rate' => 0.00,
                        'pen_act' => 1,
                        'created_at' => now(),
                    ]);
                }

                $activatedCount++;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Successfully activated pension for {$activatedCount} staff members.",
                'activated_count' => $activatedCount,
                'warnings' => $warnings
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('PensionActivationAPI importPension: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
