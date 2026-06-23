<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class RetentionActivationApiController extends Controller
{
    private function checkAuth(Request $request): bool
    {
        $userId = $request->header('X-User-Id');
        return !empty($userId);
    }

    /**
     * GET /api/nextjs/payroll/retention-activation
     * Fetch active staff along with their retention activation status.
     */
    public function index(Request $request)
    {
        if (!$this->checkAuth($request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        try {
            $search = trim($request->input('search', ''));
            $query = DB::table('tblper as p')
                ->leftJoin('first_salary_structure as fss', 'fss.staffId', '=', 'p.ID')
                ->where('p.rank', '!=', 2) // Exclude terminated/retired staff
                ->select(
                    'p.ID as id',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    DB::raw('COALESCE(fss.reten_act, 0) as reten_act'),
                    DB::raw('(
                        COALESCE(fss.basic_salary, 0.00) +
                        COALESCE(fss.housing_allowance, 0.00) +
                        COALESCE(fss.transport_allowance, 0.00) +
                        COALESCE(fss.medical_allowance, 0.00) +
                        COALESCE(fss.utility_allowance, 0.00) +
                        COALESCE(fss.meal_allowance, 0.00)
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
                $row->reten_act = (int) $row->reten_act;
                $row->basic_salary = (float) $row->basic_salary;
                return $row;
            });

            return response()->json([
                'status' => 'success',
                'data' => $records
            ]);
        } catch (\Throwable $th) {
            Log::error('RetentionActivationAPI index: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/retention-activation/toggle
     * Toggle a single staff member's retention activation status.
     */
    public function toggleRetention(Request $request)
    {
        if (!$this->checkAuth($request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        try {
            $request->validate([
                'staff_id'  => 'required|integer|exists:tblper,ID',
                'reten_act' => 'required|integer|in:0,1'
            ]);

            $staffId = $request->input('staff_id');
            $retenAct = $request->input('reten_act');

            $existing = DB::table('first_salary_structure')->where('staffId', $staffId)->first();

            if ($existing) {
                DB::table('first_salary_structure')->where('staffId', $staffId)->update([
                    'reten_act' => $retenAct,
                ]);
            } else {
                DB::table('first_salary_structure')->insert([
                    'staffId' => $staffId,
                    'basic_salary' => 0.00,
                    'declare_salary' => 0.00,
                    'housing_allowance' => 0.00,
                    'transport_allowance' => 0.00,
                    'medical_allowance' => 0.00,
                    'utility_allowance' => 0.00,
                    'meal_allowance' => 0.00,
                    'reten_act' => $retenAct,
                    'num_rente_months' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Retention status updated successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('RetentionActivationAPI toggleRetention: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/retention-activation/import
     * Bulk activate retention for multiple staff via Excel/CSV spreadsheet.
     */
    public function importRetention(Request $request)
    {
        if (!$this->checkAuth($request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

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
            $grossSalaryIndex = -1;
            $numRetenMonthsIndex = -1;
            $retenActIndex = -1;

            foreach ($headers as $index => $header) {
                $h = str_replace(['_', ' '], '', $header);
                if ($h === 'staffid' || $h === 'id') {
                    $staffIdIndex = $index;
                } elseif ($h === 'grosssalary' || $h === 'grosssalry' || $h === 'gross') {
                    $grossSalaryIndex = $index;
                } elseif ($h === 'numretenmonths' || $h === 'numrentemonths') {
                    $numRetenMonthsIndex = $index;
                } elseif ($h === 'retenact') {
                    $retenActIndex = $index;
                }
            }

            // Fallback if header name is not found
            if ($staffIdIndex === -1) {
                $staffIdIndex = 0;
            }
            if ($grossSalaryIndex === -1) {
                $grossSalaryIndex = 1;
            }
            if ($numRetenMonthsIndex === -1) {
                $numRetenMonthsIndex = 2;
            }
            if ($retenActIndex === -1) {
                $retenActIndex = 3;
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

                // Match by Staff ID (Excel column) in tblper
                if ($staffIdIndex !== -1 && isset($row[$staffIdIndex]) && trim($row[$staffIdIndex]) !== '') {
                    $staffVal = trim($row[$staffIdIndex]);
                    if (is_numeric($staffVal)) {
                        $staff = DB::table('tblper')->where('ID', intval($staffVal))->first();
                    }
                }

                if (!$staff) {
                    $searchVal = isset($row[$staffIdIndex]) ? trim($row[$staffIdIndex]) : "Row " . ($r + 1);
                    $warnings[] = "Row " . ($r + 1) . ": Staff with identifier '{$searchVal}' does not exist.";
                    continue;
                }

                // Extract values from Excel row and compute allowances
                $grossVal = $grossSalaryIndex !== -1 && isset($row[$grossSalaryIndex]) && trim((string)$row[$grossSalaryIndex]) !== '' ? (float)$row[$grossSalaryIndex] : 0.00;
                $basic = round($grossVal * 0.20, 2);
                $housing = round($grossVal * 0.20, 2);
                $transport = round($grossVal * 0.10, 2);
                $medical = round($grossVal * 0.10, 2);
                $utility = round($grossVal * 0.20, 2);
                $meal = round($grossVal * 0.20, 2);

                $numRetenMonths = $numRetenMonthsIndex !== -1 && isset($row[$numRetenMonthsIndex]) && trim((string)$row[$numRetenMonthsIndex]) !== '' ? (int)$row[$numRetenMonthsIndex] : 0;
                $retenAct = $retenActIndex !== -1 && isset($row[$retenActIndex]) && trim((string)$row[$retenActIndex]) !== '' ? (int)$row[$retenActIndex] : 1;

                // Activate/Create record in first_salary_structure
                $existing = DB::table('first_salary_structure')->where('staffId', $staff->ID)->first();

                $saveData = [
                    'staffId' => $staff->ID,
                    'basic_salary' => $basic,
                    'declare_salary' => null,
                    'housing_allowance' => $housing,
                    'transport_allowance' => $transport,
                    'medical_allowance' => $medical,
                    'utility_allowance' => $utility,
                    'meal_allowance' => $meal,
                    'reten_act' => $retenAct,
                    'num_rente_months' => $numRetenMonths,
                ];

                if ($existing) {
                    $saveData['updated_at'] = now();
                    DB::table('first_salary_structure')->where('staffId', $staff->ID)->update($saveData);
                } else {
                    $saveData['created_at'] = now();
                    $saveData['updated_at'] = now();
                    DB::table('first_salary_structure')->insert($saveData);
                }

                $activatedCount++;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Successfully activated retention for {$activatedCount} staff members.",
                'activated_count' => $activatedCount,
                'warnings' => $warnings
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('RetentionActivationAPI importRetention: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
