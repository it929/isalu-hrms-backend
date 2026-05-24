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
     * GET /api/nextjs/payroll/salary-structures/staff
     * Retrieve all active staff for the dropdown menu.
     */
    public function getStaffList(Request $request)
    {
        try {
            $staff = DB::table('tblper')
                ->where('rank', '!=', 2) // Exclude terminated/retired if applicable (rank 2 is inactive in NextJsPayrollApiController)
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
                'basic_salary' => 'nullable|numeric|min:0',
                'declare_salary' => 'nullable|numeric|min:0',
                'housing_allowance' => 'nullable|numeric|min:0',
                'transport_allowance' => 'nullable|numeric|min:0',
                'medical_allowance' => 'nullable|numeric|min:0',
                'utility_allowance' => 'nullable|numeric|min:0',
                'meal_allowance' => 'nullable|numeric|min:0',
                'pension_rate' => 'nullable|numeric|min:0|max:100',
                'tax_rate' => 'nullable|numeric|min:0|max:100',
            ]);

            // Ensure the staff member exists in tblper
            $staffExists = DB::table('tblper')->where('ID', $validated['staffId'])->exists();
            if (!$staffExists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The selected staff member does not exist.'
                ], 404);
            }

            $fields = [
                'basic_salary', 'declare_salary', 'housing_allowance',
                'transport_allowance', 'medical_allowance', 'utility_allowance',
                'meal_allowance', 'pension_rate', 'tax_rate'
            ];

            $data = ['staffId' => $validated['staffId']];
            foreach ($fields as $field) {
                $data[$field] = isset($validated[$field]) ? (float) $validated[$field] : 0.00;
            }

            $existing = DB::table('salary_structures')->where('staffId', $validated['staffId'])->first();
            if ($existing) {
                DB::table('salary_structures')->where('staffId', $validated['staffId'])->update($data);
            } else {
                $data['created_at'] = now();
                DB::table('salary_structures')->insert($data);
            }

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
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

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
            $basicIndex = -1;
            $declareIndex = -1;
            $housingIndex = -1;
            $transportIndex = -1;
            $medicalIndex = -1;
            $utilityIndex = -1;
            $mealIndex = -1;
            $pensionIndex = -1;
            $taxIndex = -1;

            foreach ($headers as $index => $header) {
                if (strpos($header, 'staff') !== false || strpos($header, 'id') !== false) {
                    if ($staffIdIndex === -1) $staffIdIndex = $index;
                } elseif (strpos($header, 'basic') !== false) {
                    $basicIndex = $index;
                } elseif (strpos($header, 'declare') !== false) {
                    $declareIndex = $index;
                } elseif (strpos($header, 'housing') !== false) {
                    $housingIndex = $index;
                } elseif (strpos($header, 'transport') !== false) {
                    $transportIndex = $index;
                } elseif (strpos($header, 'medical') !== false) {
                    $medicalIndex = $index;
                } elseif (strpos($header, 'utility') !== false) {
                    $utilityIndex = $index;
                } elseif (strpos($header, 'meal') !== false) {
                    $mealIndex = $index;
                } elseif (strpos($header, 'pension') !== false) {
                    $pensionIndex = $index;
                } elseif (strpos($header, 'tax') !== false) {
                    $taxIndex = $index;
                }
            }

            // Fallbacks to default column indices (0-9)
            if ($staffIdIndex === -1) $staffIdIndex = 0;
            if ($basicIndex === -1) $basicIndex = 1;
            if ($declareIndex === -1) $declareIndex = 2;
            if ($housingIndex === -1) $housingIndex = 3;
            if ($transportIndex === -1) $transportIndex = 4;
            if ($medicalIndex === -1) $medicalIndex = 5;
            if ($utilityIndex === -1) $utilityIndex = 6;
            if ($mealIndex === -1) $mealIndex = 7;
            if ($pensionIndex === -1) $pensionIndex = 8;
            if ($taxIndex === -1) $taxIndex = 9;

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
                    $warnings[] = "Row " . ($rowIndex + 1) . ": Staff with ID '{$staffId}' not found in database.";
                    continue;
                }

                // Extract fields
                $basic = (float) ($row[$basicIndex] ?? 0.00);
                $declare = (float) ($row[$declareIndex] ?? 0.00);
                $housing = (float) ($row[$housingIndex] ?? 0.00);
                $transport = (float) ($row[$transportIndex] ?? 0.00);
                $medical = (float) ($row[$medicalIndex] ?? 0.00);
                $utility = (float) ($row[$utilityIndex] ?? 0.00);
                $meal = (float) ($row[$mealIndex] ?? 0.00);
                $pension = (float) ($row[$pensionIndex] ?? 0.00);
                $tax = (float) ($row[$taxIndex] ?? 0.00);

                $saveData = [
                    'staffId' => $staffId,
                    'basic_salary' => $basic,
                    'declare_salary' => $declare,
                    'housing_allowance' => $housing,
                    'transport_allowance' => $transport,
                    'medical_allowance' => $medical,
                    'utility_allowance' => $utility,
                    'meal_allowance' => $meal,
                    'pension_rate' => $pension,
                    'tax_rate' => $tax,
                ];

                $existing = DB::table('salary_structures')->where('staffId', $staffId)->first();
                if ($existing) {
                    DB::table('salary_structures')->where('staffId', $staffId)->update($saveData);
                } else {
                    $saveData['created_at'] = now();
                    DB::table('salary_structures')->insert($saveData);
                }

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
