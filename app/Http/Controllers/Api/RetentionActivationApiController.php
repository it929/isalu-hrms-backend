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
                    DB::raw('COALESCE(fss.num_rente_months, 0) as num_rente_months'),
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

            $payrollDeductions = DB::table('payroll_conpt')
                ->select('staffID', DB::raw('SUM(retention) as total_retention_deducted'))
                ->groupBy('staffID')
                ->pluck('total_retention_deducted', 'staffID');

            $records = $query->orderBy('p.surname', 'asc')->get()->map(function ($row) use ($payrollDeductions) {
                $row->name = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                $row->reten_act = (int) $row->reten_act;
                $row->basic_salary = (float) $row->basic_salary;
                $row->num_rente_months = (int) ($row->num_rente_months ?? 0);
                $row->remaining_months = max(0, 20 - $row->num_rente_months);
                $row->monthly_retention = round($row->basic_salary * 0.05, 2);
                
                $fromPayroll = isset($payrollDeductions[$row->id]) ? (float)$payrollDeductions[$row->id] : 0.00;
                $calculatedDeducted = round($row->num_rente_months * $row->monthly_retention, 2);
                $row->total_retention_deducted = max($fromPayroll, $calculatedDeducted);
                $row->total_retention_target = round($row->monthly_retention * 20, 2);

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
     * POST /api/nextjs/payroll/retention-activation/bulk-toggle
     * Bulk toggle retention activation status for multiple staff members.
     */
    public function bulkToggleRetention(Request $request)
    {
        if (!$this->checkAuth($request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        try {
            $request->validate([
                'staff_ids' => 'required|array|min:1',
                'staff_ids.*' => 'integer|exists:tblper,ID',
                'reten_act' => 'required|integer|in:0,1'
            ]);

            $staffIds = $request->input('staff_ids');
            $retenAct = $request->input('reten_act');
            $successCount = 0;

            DB::beginTransaction();

            foreach ($staffIds as $staffId) {
                $existing = DB::table('first_salary_structure')->where('staffId', $staffId)->first();

                if ($existing) {
                    DB::table('first_salary_structure')->where('staffId', $staffId)->update([
                        'reten_act' => $retenAct,
                        'updated_at' => now(),
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
                $successCount++;
            }

            DB::commit();

            $action = $retenAct === 1 ? 'activated' : 'deactivated';
            return response()->json([
                'status' => 'success',
                'message' => "Successfully {$action} retention for {$successCount} staff member(s).",
                'count' => $successCount,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('RetentionActivationAPI bulkToggleRetention: ' . $th->getMessage());
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

            $cleanMoney = function ($val) {
                if ($val === null || $val === false) return 0.0;
                $str = trim((string)$val);
                if ($str === '' || $str === '-' || strtolower($str) === 'nil' || strtolower($str) === 'null' || strtolower($str) === 'n/a') return 0.0;
                $cleaned = preg_replace('/[^0-9.-]/', '', $str);
                return is_numeric($cleaned) ? (float)$cleaned : 0.0;
            };

            $staffIdIndex = -1;
            $firstSalaryIndex = -1;
            $totalDeductedIndex = -1;
            $balanceDeductIndex = -1;
            $numRetenMonthsIndex = -1;
            $retenActIndex = -1;

            foreach ($headers as $index => $header) {
                $h = strtolower(preg_replace('/[^a-z0-9]/', '', (string)$header));
                if (in_array($h, ['staffid', 'id', 'staff', 'fileno', 'staffno', 'empid', 'employeeid'])) {
                    $staffIdIndex = $index;
                } elseif (in_array($h, ['firstsalary', 'grosssalary', 'gross', 'salary', 'basicsalary', 'monthlysalary'])) {
                    $firstSalaryIndex = $index;
                } elseif (in_array($h, ['totaldeducted', 'totaldeduction', 'amountdeducted', 'deducted', 'totalretentiondeducted', 'retentiondeducted', 'totalretention'])) {
                    $totalDeductedIndex = $index;
                } elseif (in_array($h, ['balancetobededucted', 'balancetobededuct', 'balanceremaining', 'remainingbalance', 'balance', 'remaining', 'balanceretention'])) {
                    $balanceDeductIndex = $index;
                } elseif (in_array($h, ['numretenmonths', 'numrentemonths', 'monthsdeducted', 'months'])) {
                    $numRetenMonthsIndex = $index;
                } elseif (in_array($h, ['retenact', 'status', 'active', 'activation'])) {
                    $retenActIndex = $index;
                }
            }

            // Fallback by column position if header names were slightly different
            if ($staffIdIndex === -1) $staffIdIndex = 0;
            if ($firstSalaryIndex === -1) $firstSalaryIndex = 1;
            if ($totalDeductedIndex === -1 && $numRetenMonthsIndex === -1) $totalDeductedIndex = 2;
            if ($balanceDeductIndex === -1 && $totalDeductedIndex !== 3) $balanceDeductIndex = 3;

            $activatedCount = 0;
            $warnings = [];

            DB::beginTransaction();

            for ($r = 1; $r < count($rows); $r++) {
                $row = $rows[$r];

                // Skip completely empty row
                $isEmptyRow = true;
                foreach ($row as $cell) {
                    if (trim((string)$cell) !== '') {
                        $isEmptyRow = false;
                        break;
                    }
                }
                if ($isEmptyRow) continue;

                $staff = null;
                $rawStaffId = ($staffIdIndex !== -1 && isset($row[$staffIdIndex])) ? trim((string)$row[$staffIdIndex]) : '';

                if ($rawStaffId !== '') {
                    if (is_numeric($rawStaffId)) {
                        $staff = DB::table('tblper')->where('ID', intval($rawStaffId))->first();
                    }
                    if (!$staff) {
                        $staff = DB::table('tblper')->where('fileNo', $rawStaffId)->first();
                    }
                    if (!$staff) {
                        $digits = preg_replace('/\D/', '', $rawStaffId);
                        if ($digits !== '') {
                            $staff = DB::table('tblper')->where('ID', intval($digits))->orWhere('fileNo', $digits)->first();
                        }
                    }
                }

                if (!$staff) {
                    $searchVal = $rawStaffId !== '' ? $rawStaffId : "Row " . ($r + 1);
                    $warnings[] = "Row " . ($r + 1) . ": Staff with identifier '{$searchVal}' does not exist.";
                    continue;
                }

                // Extract values from Excel row
                $grossVal = ($firstSalaryIndex !== -1 && isset($row[$firstSalaryIndex])) ? $cleanMoney($row[$firstSalaryIndex]) : 0.00;

                // Fallback to existing salary in database if 0 in Excel
                if ($grossVal <= 0) {
                    $existingStruct = DB::table('first_salary_structure')->where('staffId', $staff->ID)->first();
                    if ($existingStruct) {
                        $grossVal = (float)($existingStruct->basic_salary ?? 0) +
                                    (float)($existingStruct->housing_allowance ?? 0) +
                                    (float)($existingStruct->transport_allowance ?? 0) +
                                    (float)($existingStruct->medical_allowance ?? 0) +
                                    (float)($existingStruct->utility_allowance ?? 0) +
                                    (float)($existingStruct->meal_allowance ?? 0);
                    }
                    if ($grossVal <= 0) {
                        $currStruct = DB::table('salary_structures')->where('staffId', $staff->ID)->first();
                        if ($currStruct) {
                            $grossVal = (float)($currStruct->basic_salary ?? 0) +
                                        (float)($currStruct->housing_allowance ?? 0) +
                                        (float)($currStruct->transport_allowance ?? 0) +
                                        (float)($currStruct->medical_allowance ?? 0) +
                                        (float)($currStruct->utility_allowance ?? 0) +
                                        (float)($currStruct->meal_allowance ?? 0);
                        }
                    }
                }

                $basic = round($grossVal * 0.20, 2);
                $housing = round($grossVal * 0.20, 2);
                $transport = round($grossVal * 0.10, 2);
                $medical = round($grossVal * 0.10, 2);
                $utility = round($grossVal * 0.20, 2);
                $meal = round($grossVal * 0.20, 2);

                $monthlyRetention = round($grossVal * 0.05, 2);
                $numRetenMonths = 0;

                $rawTotalDeducted = ($totalDeductedIndex !== -1 && isset($row[$totalDeductedIndex])) ? trim((string)$row[$totalDeductedIndex]) : '';
                $totalDeductedVal = $cleanMoney($rawTotalDeducted);

                $rawBalance = ($balanceDeductIndex !== -1 && isset($row[$balanceDeductIndex])) ? trim((string)$row[$balanceDeductIndex]) : null;
                $balanceVal = $cleanMoney($rawBalance);

                // Balance is '-' or empty or 0 means retention is completed
                $balanceIsComplete = ($rawBalance !== null && in_array(strtolower($rawBalance), ['-', '0', '0.00', 'nil', 'none', 'completed', 'complete', 'n/a', 'done', '']));

                if ($balanceIsComplete) {
                    // Completed retention
                    $numRetenMonths = 20;
                } elseif ($totalDeductedVal > 0 && $monthlyRetention > 0) {
                    // If total_deducted covers the full salary or more, it's 20 months
                    if ($grossVal > 0 && $totalDeductedVal >= $grossVal) {
                        $numRetenMonths = 20;
                    } else {
                        $numRetenMonths = (int) round($totalDeductedVal / $monthlyRetention);
                    }
                } elseif ($rawBalance !== null && $rawBalance !== '' && $monthlyRetention > 0) {
                    $remainingMonths = (int) round($balanceVal / $monthlyRetention);
                    $numRetenMonths = max(0, 20 - $remainingMonths);
                } elseif ($numRetenMonthsIndex !== -1 && isset($row[$numRetenMonthsIndex]) && trim((string)$row[$numRetenMonthsIndex]) !== '') {
                    $numRetenMonths = (int) $cleanMoney($row[$numRetenMonthsIndex]);
                }

                $numRetenMonths = max(0, min(20, $numRetenMonths));
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
