<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class SalaryStructureApiController extends Controller
{
    /**
     * Helper to compute salary breakdown from Gross Salary.
     */
    private function calculateSalaryFields($grossSalary)
    {
        $gross = (float) $grossSalary;
        return [
            'basic_salary' => round($gross * 0.20, 2),
            'declare_salary' => null,
            'housing_allowance' => round($gross * 0.20, 2),
            'transport_allowance' => round($gross * 0.10, 2),
            'medical_allowance' => round($gross * 0.10, 2),
            'utility_allowance' => round($gross * 0.20, 2),
            'meal_allowance' => round($gross * 0.20, 2),
            'pension_rate' => 8.00,
            'tax_rate' => null,
        ];
    }

    /**
     * GET /api/nextjs/payroll/salary-structures/staff
     * Retrieve all active staff for the dropdown menu.
     */
    public function getStaffList(Request $request)
    {
        try {
            $staff = DB::table('tblper')
                // ->where('rank', '!=', 2) // Exclude terminated/retired if applicable (rank 2 is inactive in NextJsPayrollApiController)
                ->where('staff_status', 1)
                ->select('ID as id', 'fileNo', 'surname', 'first_name', 'othernames')
                ->orderBy('surname', 'asc')
                ->get()
                ->map(function ($row) {
                    $fullName = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                    return [
                        'id' => $row->id,
                        'fileNo' => $row->fileNo ?? '',
                        'name' => $fullName,
                        'label' => $fullName,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $staff
            ]);
        } catch (\Throwable $th) {
            Log::error('SalaryStructureAPI getStaffList: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/salary-structures
     * Fetch existing salary structures, joined with staff information.
     */
    public function index(Request $request)
    {
        try {
            $search = trim($request->input('search', ''));
            $query = DB::table('salary_structures as ss')
                ->join('tblper as p', 'p.ID', '=', 'ss.staffId')
                ->select(
                    'ss.*',
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
            Log::error('SalaryStructureAPI index: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/salary-structures
     * Save/update a single staff member's salary structure.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'staffId' => 'required|integer',
                'gross_salary' => 'required|numeric|min:0',
                'structure_type' => 'nullable|string|in:first,current',
            ]);

            // Ensure the staff member exists in tblper
            $staffExists = DB::table('tblper')->where('ID', $validated['staffId'])->exists();
            if (!$staffExists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The selected staff member does not exist.'
                ], 404);
            }

            $calculated = $this->calculateSalaryFields($validated['gross_salary']);
            $data = array_merge(['staffId' => $validated['staffId']], $calculated);

            $existing = DB::table('salary_structures')->where('staffId', $validated['staffId'])->first();
            if ($existing) {
                DB::table('salary_structures')->where('staffId', $validated['staffId'])->update($data);
            } else {
                $data['created_at'] = now();
                DB::table('salary_structures')->insert($data);
            }

            // Save to first_salary_structure table if type is 'first'
            $structureType = $validated['structure_type'] ?? 'current';
            if ($structureType === 'first') {
                $firstData = [
                    'staffId' => $validated['staffId'],
                    'basic_salary' => $data['basic_salary'],
                    'declare_salary' => $data['declare_salary'],
                    'housing_allowance' => $data['housing_allowance'],
                    'transport_allowance' => $data['transport_allowance'],
                    'medical_allowance' => $data['medical_allowance'],
                    'utility_allowance' => $data['utility_allowance'],
                    'meal_allowance' => $data['meal_allowance'],
                ];
                $existingFirst = DB::table('first_salary_structure')->where('staffId', $validated['staffId'])->first();
                if ($existingFirst) {
                    $firstData['updated_at'] = now();
                    DB::table('first_salary_structure')->where('staffId', $validated['staffId'])->update($firstData);
                } else {
                    $firstData['reten_act'] = 0;
                    $firstData['num_rente_months'] = 0;
                    $firstData['created_at'] = now();
                    $firstData['updated_at'] = now();
                    DB::table('first_salary_structure')->insert($firstData);
                }
            }

            // Update staff_status to 1 in tblper
            DB::table('tblper')->where('ID', $validated['staffId'])->update(['staff_status' => 1]);

            return response()->json([
                'status' => 'success',
                'message' => 'Salary structure saved successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('SalaryStructureAPI store: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/salary-structures/upload
     * Bulk upload salary structures via Excel/CSV matching staff by staffId.
     */
    public function upload(Request $request)
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
            $grossSalaryIndex = -1;

            foreach ($headers as $index => $header) {
                if (strpos($header, 'staff') !== false || strpos($header, 'id') !== false) {
                    if ($staffIdIndex === -1) $staffIdIndex = $index;
                } elseif (strpos($header, 'gross') !== false || strpos($header, 'salary') !== false) {
                    if ($grossSalaryIndex === -1) $grossSalaryIndex = $index;
                }
            }

            // Fallbacks to default column indices
            if ($staffIdIndex === -1) $staffIdIndex = 0;
            if ($grossSalaryIndex === -1) $grossSalaryIndex = 1;

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

                // Look up staff
                $staff = DB::table('tblper')->where('ID', $staffId)->first();
                if (!$staff) {
                    $warnings[] = "Row " . ($rowIndex + 1) . ": Staff with identifier '{$staffId}' does not exist.";
                    continue;
                }

                // Extract gross salary
                $grossVal = (float) (trim($row[$grossSalaryIndex] ?? 0.00));
                $calculated = $this->calculateSalaryFields($grossVal);
                $saveData = array_merge(['staffId' => $staffId], $calculated);

                $existing = DB::table('salary_structures')->where('staffId', $staffId)->first();
                if ($existing) {
                    DB::table('salary_structures')->where('staffId', $staffId)->update($saveData);
                } else {
                    $saveData['created_at'] = now();
                    DB::table('salary_structures')->insert($saveData);
                }

                // Update staff_status to 1 in tblper
                DB::table('tblper')->where('ID', $staffId)->update(['staff_status' => 1]);

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
            Log::error('SalaryStructureAPI upload: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
