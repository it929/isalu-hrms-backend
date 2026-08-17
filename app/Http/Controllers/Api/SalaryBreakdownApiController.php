<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SalaryBreakdownApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * GET /api/nextjs/payroll/salary-breakdown/staff
     * Get staff list for admin/HOD selector.
     */
    public function getStaffList(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $employee = $ctx['employee'];
            $query = DB::table('tblper')
                ->where('rank', '!=', 2)
                ->where('staff_status', 1)
                ->select('ID as id', 'fileNo as file_no', 'surname', 'first_name', 'othernames', 'departmentID');

            if ($ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || $ctx['isAuditStaff']) {
                // All staff
            } elseif ($employee && $employee->is_hod == 1) {
                $query->where('departmentID', $employee->departmentID);
            } elseif ($employee) {
                $query->where('ID', $employee->ID);
            } else {
                $query->where('ID', 0);
            }

            $list = $query->orderBy('surname', 'asc')->get()->map(function ($s) {
                return [
                    'id' => $s->id,
                    'file_no' => $s->file_no,
                    'name' => trim("{$s->surname} {$s->first_name} {$s->othernames}"),
                ];
            });

            $canGenerateAll = ($ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || $ctx['isAuditStaff'] || $ctx['isFinanceStaff']);

            return response()->json([
                'status' => 'success',
                'data' => $list,
                'can_generate_all_staff' => $canGenerateAll,
                'is_admin' => ($ctx['isSuperAdmin'] || $ctx['isAdminStaff']),
                'is_super_admin' => $ctx['isSuperAdmin'],
                'is_hr_head' => $ctx['isAdminStaff'],
                'is_finance_head' => $ctx['isFinanceStaff'],
                'is_audit_head' => $ctx['isAuditStaff'],
                'is_hod' => ($employee && $employee->is_hod == 1),
            ]);
        } catch (\Throwable $th) {
            Log::error('SalaryBreakdownApiController getStaffList: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/salary-breakdown
     * Retrieve pre-compute or official breakdown for staff member.
     */
    public function getBreakdown(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $currentUser = $ctx['employee'];
            $requestedStaffId = $request->query('staff_id');
            $canGenerateAll = ($ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || $ctx['isAuditStaff'] || $ctx['isFinanceStaff']);

            // Determine effective staff ID based on role permissions
            $staffId = null;
            if ($canGenerateAll) {
                $staffId = $requestedStaffId ? (int)$requestedStaffId : null;
            } elseif ($currentUser && $currentUser->is_hod == 1) {
                if ($requestedStaffId) {
                    $targetStaff = DB::table('tblper')->where('ID', (int)$requestedStaffId)->first();
                    if ($targetStaff && $targetStaff->departmentID == $currentUser->departmentID) {
                        $staffId = (int)$requestedStaffId;
                    } else {
                        $staffId = $currentUser->ID;
                    }
                } else {
                    $staffId = $currentUser->ID;
                }
            } else {
                // Regular employee can only view own breakdown
                $staffId = $currentUser ? $currentUser->ID : null;
            }

            if (!$staffId) {
                return response()->json([
                    'status' => 'success',
                    'data' => null,
                    'staff' => null,
                    'earnings' => null,
                    'deductions' => null,
                    'summary' => null,
                    'message' => 'Please select a staff member.',
                    'can_generate_all_staff' => $canGenerateAll,
                    'is_admin' => ($ctx['isSuperAdmin'] || $ctx['isAdminStaff']),
                    'is_super_admin' => $ctx['isSuperAdmin'],
                    'is_hr_head' => $ctx['isAdminStaff'],
                    'is_finance_head' => $ctx['isFinanceStaff'],
                    'is_audit_head' => $ctx['isAuditStaff'],
                ]);
            }

            $staff = DB::table('tblper as p')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->leftJoin('tbldesignation as des', 'des.id', '=', 'p.designation')
                ->leftJoin('tblbanklist as bl', 'bl.bankID', '=', 'p.bankID')
                ->select(
                    'p.ID as id',
                    'p.fileNo as file_no',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'p.email',
                    'p.phone',
                    'p.AccNo as account_number',
                    'bl.bank as bank_name',
                    'd.department',
                    'des.designation'
                )
                ->where('p.ID', $staffId)
                ->first();

            if (!$staff) {
                return response()->json(['status' => 'error', 'message' => 'Employee not found.'], 404);
            }

            // Determine active month/year or use requested query params
            $defaultMonth = (int)date('n');
            $defaultYear = (int)date('Y');
            try {
                $activeMonthRow = DB::table('tblactivemonth')->first();
                if ($activeMonthRow) {
                    if (is_numeric($activeMonthRow->month)) {
                        $defaultMonth = (int)$activeMonthRow->month;
                    } else {
                        $mNum = date('n', strtotime("1 {$activeMonthRow->month} 2000"));
                        if ($mNum) $defaultMonth = (int)$mNum;
                    }
                    if (!empty($activeMonthRow->year)) {
                        $defaultYear = (int)$activeMonthRow->year;
                    }
                }
            } catch (\Throwable $e) {
                // fallback to current date
            }

            $month = (int)$request->query('month', $defaultMonth);
            $year = (int)$request->query('year', $defaultYear);
            $currentMonthStr = sprintf("%04d-%02d", $year, $month);
            $monthName = date('F', mktime(0, 0, 0, $month, 1, $year));

            // Check if payroll has already been computed for this period
            $computedRecord = null;
            try {
                $computedRecord = DB::table('payroll_conpt as pc')
                    ->join('payroll_runs as pr', 'pr.id', '=', 'pc.payroll_run_id')
                    ->where('pc.staffID', $staffId)
                    ->where('pr.month', $month)
                    ->where('pr.year', $year)
                    ->select('pc.*', 'pr.status as run_status')
                    ->first();
            } catch (\Throwable $e) {
                // Ignore if tables or records don't exist yet
            }

            $isComputed = ($computedRecord !== null);

            // Fetch current salary structure
            $struct = DB::table('salary_structures')->where('staffId', $staffId)->first();

            $basic = $struct ? (float)$struct->basic_salary : 0.00;
            $housing = $struct ? (float)$struct->housing_allowance : 0.00;
            $transport = $struct ? (float)$struct->transport_allowance : 0.00;
            $medical = $struct ? (float)$struct->medical_allowance : 0.00;
            $utility = $struct ? (float)$struct->utility_allowance : 0.00;
            $meal = $struct ? (float)$struct->meal_allowance : 0.00;
            $taxRate = $struct ? (float)$struct->tax_rate : 0.00;
            $pensionRate = $struct ? (float)$struct->pension_rate : 0.00;
            $declareSalary = $struct ? (float)$struct->declare_salary : 0.00;

            $totalBasicAllowances = $basic + $housing + $transport + $medical + $utility + $meal;

            // Fetch active earning variables / allowances
            $totalEarningVars = 0.00;
            $earningVarsList = [];
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('staffEarningAndDeduction')) {
                    $activeVars = DB::table('staffEarningAndDeduction as sed')
                        ->leftJoin('tblearningParticular as lp', 'lp.ID', '=', 'sed.particularID')
                        ->where('sed.staffID', $staffId)
                        ->where('sed.status', 1)
                        ->select('lp.Particular as name', 'sed.amount', 'sed.startDate as effective_date')
                        ->get();

                    foreach ($activeVars as $v) {
                        $amt = (float)$v->amount;
                        $totalEarningVars += $amt;
                        $earningVarsList[] = [
                            'name' => $v->name ?? 'Variable Earning',
                            'amount' => $amt,
                            'effective_date' => $v->effective_date
                        ];
                    }
                }
            } catch (\Throwable $e) {
                // Ignore if not present
            }

            $grossPay = $totalBasicAllowances + $totalEarningVars;

            // --- Deductions Breakdown ---

            // 1. PAYE Tax
            $annualGross = $declareSalary * 12.0;
            $annualPension = 0.00;
            if ($struct && $struct->pen_act == 1) {
                $rate = ($pensionRate > 0) ? ($pensionRate / 100.0) : 0.08;
                $annualPension = ($annualGross * 0.5) * $rate;
            }
            $annualTaxable = max(0.00, $annualGross - $annualPension);
            $annualTax = 0.00;
            if ($annualTaxable > 800000.00) {
                $taxableRemaining = $annualTaxable - 800000.00;
                $band1 = min(2200000.00, $taxableRemaining);
                $annualTax += $band1 * 0.15;
                $taxableRemaining -= $band1;
                if ($taxableRemaining > 0) {
                    $band2 = min(9000000.00, $taxableRemaining);
                    $annualTax += $band2 * 0.18;
                    $taxableRemaining -= $band2;
                }
                if ($taxableRemaining > 0) {
                    $band3 = min(13000000.00, $taxableRemaining);
                    $annualTax += $band3 * 0.21;
                    $taxableRemaining -= $band3;
                }
                if ($taxableRemaining > 0) {
                    $band4 = min(25000000.00, $taxableRemaining);
                    $annualTax += $band4 * 0.23;
                    $taxableRemaining -= $band4;
                }
                if ($taxableRemaining > 0) {
                    $annualTax += $taxableRemaining * 0.25;
                }
            }
            $payeTax = round($annualTax / 12.0, 2);

            // 2. Pension
            $pension = 0.00;
            $isPensionActive = ($struct && $struct->pen_act == 1);
            if ($isPensionActive) {
                $rate = ($pensionRate > 0) ? ($pensionRate / 100.0) : 0.08;
                $pension = round(($totalBasicAllowances * 0.5) * $rate, 2);
            }

            // 3. Retention
            $retention = 0.00;
            $retentionMonths = 0;
            $retentionRemainingMonths = 0;
            $isRetentionActive = false;
            $firstStruct = DB::table('first_salary_structure')->where('staffId', $staffId)->first();
            if ($firstStruct) {
                $isRetentionActive = ($firstStruct->reten_act == 1);
                $retentionMonths = (int)($firstStruct->num_rente_months ?? 0);
                $retentionRemainingMonths = max(0, 20 - $retentionMonths);
                if ($isRetentionActive && $retentionMonths < 20) {
                    $retentionBase = (float)$firstStruct->basic_salary +
                                     (float)$firstStruct->housing_allowance +
                                     (float)$firstStruct->transport_allowance +
                                     (float)$firstStruct->medical_allowance +
                                     (float)$firstStruct->utility_allowance +
                                     (float)$firstStruct->meal_allowance;
                    $retention = round(0.05 * $retentionBase, 2);
                }
            }

            // 4. Approved IOUs for selected month
            $firstDay = \Carbon\Carbon::create($year, $month, 1)->startOfMonth()->format('Y-m-d');
            $lastDay = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');
            $ious = DB::table('iou_records')
                ->where('staff_id', $staffId)
                ->where('status', 1)
                ->whereBetween('iou_date', [$firstDay, $lastDay])
                ->select('id', 'amount', 'iou_date', 'reason')
                ->get();
            $iouSum = (float)$ious->sum('amount');

            // 5. Medical Loan Setup
            $medLoanSetup = DB::table('medical_loan_deduction_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->where('balance_remaining', '>', 0)
                ->where('start_month', '<=', $currentMonthStr)
                ->where('end_month', '>=', $currentMonthStr)
                ->orderBy('id', 'desc')
                ->first();
            $medicalLoanDeduct = $medLoanSetup ? min((float)$medLoanSetup->monthly_deduction, (float)$medLoanSetup->balance_remaining) : 0.00;

            // 6. Coop Loan Setup
            $coopLoanSetup = DB::table('coop_loan_deduction_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->where('balance_remaining', '>', 0)
                ->where('start_month', '<=', $currentMonthStr)
                ->where('end_month', '>=', $currentMonthStr)
                ->orderBy('id', 'desc')
                ->first();
            $coopLoanDeduct = $coopLoanSetup ? min((float)$coopLoanSetup->monthly_deduction, (float)$coopLoanSetup->balance_remaining) : 0.00;

            // 7. Coop Savings Setup
            $coopSavingsSetup = DB::table('coop_savings_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->where('start_month', '<=', $currentMonthStr)
                ->orderBy('id', 'desc')
                ->first();
            $coopSavingsDeduct = $coopSavingsSetup ? (float)$coopSavingsSetup->monthly_saving : 0.00;

            // 8. Coop Asset Finance Setup
            $coopAssetSetup = DB::table('coop_asset_finance_deduction_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->where('balance_remaining', '>', 0)
                ->where('start_month', '<=', $currentMonthStr)
                ->where(function($q) use ($currentMonthStr) {
                    $q->whereNull('end_month')
                      ->orWhere('end_month', '=', '')
                      ->orWhere('end_month', '>=', $currentMonthStr);
                })
                ->orderBy('id', 'desc')
                ->first();
            $coopAssetDeduct = $coopAssetSetup ? min((float)$coopAssetSetup->monthly_deduction, (float)$coopAssetSetup->balance_remaining) : 0.00;

            // 9. Surcharge Setup
            $surchargeSetup = DB::table('surcharge_deduction_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->where('balance_remaining', '>', 0)
                ->where('start_month', '<=', $currentMonthStr)
                ->where(function($q) use ($currentMonthStr) {
                    $q->whereNull('end_month')
                      ->orWhere('end_month', '=', '')
                      ->orWhere('end_month', '>=', $currentMonthStr);
                })
                ->orderBy('id', 'desc')
                ->first();
            $surchargeDeduct = $surchargeSetup ? min((float)$surchargeSetup->monthly_deduction, (float)$surchargeSetup->balance_remaining) : 0.00;

            // 10. Absence Penalty Setup
            $absencePenaltySetup = DB::table('absence_penalty_deduction_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->where(function($q) {
                    $q->where('balance_remaining', '>', 0)
                      ->orWhere('total_amount', '>', 0);
                })
                ->where('start_month', '<=', $currentMonthStr)
                ->where(function($q) use ($currentMonthStr) {
                    $q->whereNull('end_month')
                      ->orWhere('end_month', '=', '')
                      ->orWhere('end_month', '>=', $currentMonthStr);
                })
                ->orderBy('id', 'desc')
                ->first();
            $absencePenaltyBal = 0.00;
            if ($absencePenaltySetup) {
                $absencePenaltyBal = (float)$absencePenaltySetup->balance_remaining > 0
                    ? (float)$absencePenaltySetup->balance_remaining
                    : ((float)$absencePenaltySetup->total_amount > 0 ? (float)$absencePenaltySetup->total_amount : (float)$absencePenaltySetup->monthly_deduction);
            }
            $absencePenaltyDeduct = $absencePenaltySetup ? min((float)$absencePenaltySetup->monthly_deduction, $absencePenaltyBal) : 0.00;

            // 11. Regular Employee Loan Setup
            $loanSetup = DB::table('loan_deduction_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->where('balance_remaining', '>', 0)
                ->where('start_month', '<=', $currentMonthStr)
                ->where('end_month', '>=', $currentMonthStr)
                ->orderBy('id', 'desc')
                ->first();
            $loanDeduct = 0.00;
            $loanBalance = 0.00;
            if ($loanSetup) {
                $loanDeduct = min((float)$loanSetup->monthly_deduction, (float)$loanSetup->balance_remaining);
                $loanBalance = (float)$loanSetup->balance_remaining;
            } else {
                $empLoan = DB::table('employee_loans')
                    ->where('staffId', $staffId)
                    ->whereRaw("LOWER(status) = 'approved'")
                    ->where('balance', '>', 0)
                    ->orderBy('id', 'desc')
                    ->first();
                if ($empLoan) {
                    $loanDeduct = min((float)$empLoan->monthly_deduction, (float)$empLoan->balance);
                    $loanBalance = (float)$empLoan->balance;
                }
            }

            // 12. Other Deduction Setup
            $otherDeductSetup = DB::table('other_deduction_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->where(function($q) {
                    $q->where('balance_remaining', '>', 0)
                      ->orWhere('total_amount', '>', 0);
                })
                ->where('start_month', '<=', $currentMonthStr)
                ->where(function($q) use ($currentMonthStr) {
                    $q->whereNull('end_month')
                      ->orWhere('end_month', '=', '')
                      ->orWhere('end_month', '>=', $currentMonthStr);
                })
                ->orderBy('id', 'desc')
                ->first();
            $otherDeductBal = 0.00;
            if ($otherDeductSetup) {
                $otherDeductBal = (float)$otherDeductSetup->balance_remaining > 0
                    ? (float)$otherDeductSetup->balance_remaining
                    : ((float)$otherDeductSetup->total_amount > 0 ? (float)$otherDeductSetup->total_amount : (float)$otherDeductSetup->monthly_deduction);
            }
            $otherDeduct = $otherDeductSetup ? min((float)$otherDeductSetup->monthly_deduction, $otherDeductBal) : 0.00;

            // 13. Leave of Absence Deduction
            $loaDays = 0;
            try {
                $firstDay = \Carbon\Carbon::create($year, $month, 1)->startOfMonth()->format('Y-m-d');
                $lastDay = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');

                if (\Illuminate\Support\Facades\Schema::hasTable('leave_of_absent')) {
                    $leaves = DB::table('leave_of_absent')
                        ->where('staffId', $staffId)
                        ->where('status', 2) // Approved
                        ->where(function($query) use ($firstDay, $lastDay) {
                            $query->whereBetween('start_date', [$firstDay, $lastDay])
                                  ->orWhereBetween('end_date', [$firstDay, $lastDay])
                                  ->orWhere(function($q) use ($firstDay, $lastDay) {
                                      $q->where('start_date', '<=', $firstDay)
                                        ->where('end_date', '>=', $lastDay);
                                  });
                        })
                        ->get();

                    $startOfMonth = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
                    $endOfMonth = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

                    foreach ($leaves as $leave) {
                        $start = \Carbon\Carbon::parse($leave->start_date);
                        $end = \Carbon\Carbon::parse($leave->end_date);
                        $overlapStart = $start->greaterThan($startOfMonth) ? $start : $startOfMonth;
                        $overlapEnd = $end->lessThan($endOfMonth) ? $end : $endOfMonth;
                        $loaDays += ($overlapStart->diffInDays($overlapEnd) + 1);
                    }
                }
            } catch (\Throwable $e) {
                // Ignore if LOA table is not available
            }

            if ($isComputed) {
                $basic = (float)$computedRecord->basic;
                $housing = (float)$computedRecord->housing;
                $transport = (float)$computedRecord->transport;
                $medical = (float)$computedRecord->medical;
                $utility = (float)$computedRecord->utility;
                $meal = (float)$computedRecord->meal;
                $totalBasicAllowances = $basic + $housing + $transport + $medical + $utility + $meal;
                $grossPay = (float)$computedRecord->gross_pay;
                $totalEarningVars = max(0.00, $grossPay - $totalBasicAllowances);

                $payeTax = (float)$computedRecord->paye_tax;
                $pension = (float)$computedRecord->pension;
                $isPensionActive = ($pension > 0);
                $retention = (float)$computedRecord->retention;
                $isRetentionActive = ($retention > 0);
                $iouSum = (float)$computedRecord->iou;
                $medicalLoanDeduct = (float)$computedRecord->medical_loan;
                $coopLoanDeduct = (float)$computedRecord->coop_loan_rpyt;
                $coopSavingsDeduct = (float)$computedRecord->coop_savings;
                $coopAssetDeduct = (float)$computedRecord->coop_asset_finance;
                $surchargeDeduct = (float)$computedRecord->surcharges;
                $absencePenaltyDeduct = (float)$computedRecord->absence_penalty;
                $loanDeduct = (float)$computedRecord->loan_deduction;
                $leaveOfAbsenceDeduct = (float)$computedRecord->leave_of_absence_deduction;
                $otherDeduct = (float)$computedRecord->other_deductions;
                $totalDeductions = (float)$computedRecord->total_deductions;
                $netPay = (float)$computedRecord->net_pay;
                $paidDays = isset($computedRecord->paid_days) ? (int)$computedRecord->paid_days : 30;
                $loaDays = max(0, 30 - $paidDays);
            } else {
                $leaveOfAbsenceDeduct = ($grossPay / 30.0) * $loaDays;
                $paidDays = max(0, 30 - $loaDays);

                $totalDeductions = $payeTax + $pension + $retention + $iouSum + $medicalLoanDeduct + $coopLoanDeduct +
                                   $coopSavingsDeduct + $coopAssetDeduct + $surchargeDeduct + $absencePenaltyDeduct +
                                   $loanDeduct + $otherDeduct + $leaveOfAbsenceDeduct;

                $netPay = max(0.00, $grossPay - $totalDeductions);
            }

            return response()->json([
                'status' => 'success',
                'staff' => [
                    'id' => $staff->id,
                    'file_no' => $staff->file_no,
                    'name' => trim("{$staff->surname} {$staff->first_name} {$staff->othernames}"),
                    'email' => $staff->email,
                    'phone' => $staff->phone,
                    'department' => $staff->department ?? 'General',
                    'designation' => $staff->designation ?? 'Staff',
                    'bank_name' => $staff->bank_name,
                    'account_number' => $staff->account_number,
                    'paid_days' => $paidDays,
                    'days_absent' => $loaDays,
                ],
                'period' => [
                    'month' => $month,
                    'year' => $year,
                    'month_name' => $monthName,
                    'period_str' => "{$monthName} {$year}",
                    'is_computed' => $isComputed,
                    'is_active_month' => ($month === $defaultMonth && $year === $defaultYear),
                    'paid_days' => $paidDays,
                    'days_absent' => $loaDays,
                ],
                'earnings' => [
                    'basic_salary' => $basic,
                    'housing_allowance' => $housing,
                    'transport_allowance' => $transport,
                    'medical_allowance' => $medical,
                    'utility_allowance' => $utility,
                    'meal_allowance' => $meal,
                    'total_basic_allowances' => $totalBasicAllowances,
                    'earning_variables' => $earningVarsList,
                    'total_earning_vars' => $totalEarningVars,
                    'gross_pay' => $grossPay,
                ],
                'deductions' => [
                    'paye_tax' => [
                        'amount' => $payeTax,
                        'annual_gross' => $annualGross,
                        'annual_taxable' => $annualTaxable,
                        'label' => 'PAYE Tax'
                    ],
                    'pension' => [
                        'amount' => $pension,
                        'rate' => ($pensionRate > 0 ? $pensionRate : 8),
                        'is_active' => $isPensionActive,
                        'label' => 'Pension (8%)'
                    ],
                    'retention' => [
                        'amount' => $retention,
                        'is_active' => $isRetentionActive,
                        'num_rente_months' => $retentionMonths,
                        'remaining_months' => $retentionRemainingMonths,
                        'target_months' => 20,
                        'label' => 'Staff Retention (5%)'
                    ],
                    'iou' => [
                        'amount' => $iouSum,
                        'count' => count($ious),
                        'records' => $ious,
                        'label' => 'IOU / Salary Advance'
                    ],
                    'medical_loan' => [
                        'amount' => $medicalLoanDeduct,
                        'balance_remaining' => $medLoanSetup ? (float)$medLoanSetup->balance_remaining : 0.00,
                        'monthly_rate' => $medLoanSetup ? (float)$medLoanSetup->monthly_deduction : $medicalLoanDeduct,
                        'is_active' => ($medicalLoanDeduct > 0 || $medLoanSetup !== null),
                        'label' => 'Medical Loan Repayment'
                    ],
                    'coop_loan' => [
                        'amount' => $coopLoanDeduct,
                        'balance_remaining' => $coopLoanSetup ? (float)$coopLoanSetup->balance_remaining : 0.00,
                        'is_active' => ($coopLoanDeduct > 0 || $coopLoanSetup !== null),
                        'label' => 'Cooperative Loan Repayment'
                    ],
                    'coop_savings' => [
                        'amount' => $coopSavingsDeduct,
                        'is_active' => ($coopSavingsDeduct > 0 || $coopSavingsSetup !== null),
                        'label' => 'Cooperative Monthly Savings'
                    ],
                    'coop_asset_finance' => [
                        'amount' => $coopAssetDeduct,
                        'balance_remaining' => $coopAssetSetup ? (float)$coopAssetSetup->balance_remaining : 0.00,
                        'is_active' => ($coopAssetDeduct > 0 || $coopAssetSetup !== null),
                        'label' => 'Cooperative Asset Finance'
                    ],
                    'surcharges' => [
                        'amount' => $surchargeDeduct,
                        'balance_remaining' => $surchargeSetup ? (float)$surchargeSetup->balance_remaining : 0.00,
                        'is_active' => ($surchargeDeduct > 0 || $surchargeSetup !== null),
                        'label' => 'Surcharge'
                    ],
                    'absence_penalty' => [
                        'amount' => $absencePenaltyDeduct,
                        'balance_remaining' => $absencePenaltySetup ? (float)$absencePenaltySetup->balance_remaining : 0.00,
                        'is_active' => ($absencePenaltyDeduct > 0 || $absencePenaltySetup !== null),
                        'label' => 'Absence Penalty'
                    ],
                    'regular_loan' => [
                        'amount' => $loanDeduct,
                        'balance_remaining' => $loanBalance,
                        'is_active' => ($loanDeduct > 0),
                        'label' => 'Employee Loan'
                    ],
                    'leave_of_absence' => [
                        'amount' => round($leaveOfAbsenceDeduct, 2),
                        'days_absent' => $loaDays,
                        'paid_days' => $paidDays,
                        'is_active' => ($leaveOfAbsenceDeduct > 0 || $loaDays > 0),
                        'label' => 'Leave of Absence (Unpaid Days)'
                    ],
                    'other_deductions' => [
                        'amount' => $otherDeduct,
                        'balance_remaining' => $otherDeductSetup ? (float)$otherDeductSetup->balance_remaining : 0.00,
                        'is_active' => ($otherDeduct > 0 || $otherDeductSetup !== null),
                        'label' => 'Other Deductions'
                    ],
                    'total_deductions' => round($totalDeductions, 2)
                ],
                'summary' => [
                    'gross_pay' => round($grossPay, 2),
                    'total_deductions' => round($totalDeductions, 2),
                    'net_pay' => round($netPay, 2),
                    'paid_days' => $paidDays,
                    'days_absent' => $loaDays,
                    'status' => $isComputed ? 'Computed Payroll' : 'Pre-Compute Estimate'
                ],
                'is_admin' => ($ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || $ctx['isAuditStaff'] || $ctx['isFinanceStaff']),
                'is_super_admin' => (bool)$ctx['isSuperAdmin'],
                'is_hr_head' => (bool)$ctx['isAdminStaff'],
                'is_finance_head' => (bool)$ctx['isFinanceStaff'],
                'is_audit_head' => (bool)$ctx['isAuditStaff'],
                'can_generate_all_staff' => (bool)($ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || $ctx['isFinanceStaff'] || $ctx['isAuditStaff']),
                'is_hod' => ($currentUser && $currentUser->is_hod == 1),
            ]);
        } catch (\Throwable $th) {
            Log::error('SalaryBreakdownApiController getBreakdown: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/salary-breakdown/all-staff
     * Retrieve full payroll sheet for all staff (live pre-compute projection or computed).
     * Accessible by: Super Admin, HR Head, Finance Head, Audit Head.
     */
    public function getAllStaffSheet(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $canAccess = ($ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || $ctx['isFinanceStaff'] || $ctx['isAuditStaff']);
            if (!$canAccess) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized. Super Admin, HR Head, Finance Head, or Audit Head access is required.'], 403);
            }

            // Determine active month/year or requested
            $defaultMonth = (int)date('n');
            $defaultYear = (int)date('Y');
            try {
                $activeMonthRow = DB::table('tblactivemonth')->first();
                if ($activeMonthRow) {
                    if (is_numeric($activeMonthRow->month)) {
                        $defaultMonth = (int)$activeMonthRow->month;
                    } else {
                        $mNum = date('n', strtotime("1 {$activeMonthRow->month} 2000"));
                        if ($mNum) $defaultMonth = (int)$mNum;
                    }
                    if (!empty($activeMonthRow->year)) {
                        $defaultYear = (int)$activeMonthRow->year;
                    }
                }
            } catch (\Throwable $e) { /* fallback */ }

            $month = (int)$request->query('month', $defaultMonth);
            $year = (int)$request->query('year', $defaultYear);
            $departmentId = $request->query('department_id');
            $search = trim($request->query('search', ''));

            $result = $this->computeOrFetchAllStaffPayroll($month, $year, $departmentId, $search);

            return response()->json([
                'status' => 'success',
                'data' => $result['records'],
                'summary' => $result['summary'],
                'period' => $result['period'],
                'departments' => $result['departments'],
                'is_computed' => $result['is_computed'],
                'user_role' => [
                    'is_super_admin' => (bool)$ctx['isSuperAdmin'],
                    'is_hr_head' => (bool)$ctx['isAdminStaff'],
                    'is_finance_head' => (bool)$ctx['isFinanceStaff'],
                    'is_audit_head' => (bool)$ctx['isAuditStaff'],
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('SalaryBreakdownApiController getAllStaffSheet: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/salary-breakdown/all-staff/export
     * Export full payroll sheet for all staff (live pre-compute projection or computed) to Excel (.xlsx).
     * Exact 35-column format matching the compute payroll spreadsheet.
     * Accessible by: Super Admin, HR Head, Finance Head, Audit Head.
     */
    public function exportAllStaffSheet(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            $canAccess = ($ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || $ctx['isFinanceStaff'] || $ctx['isAuditStaff']);
            if (!$canAccess) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
            }

            $defaultMonth = (int)date('n');
            $defaultYear = (int)date('Y');
            try {
                $activeMonthRow = DB::table('tblactivemonth')->first();
                if ($activeMonthRow) {
                    if (is_numeric($activeMonthRow->month)) {
                        $defaultMonth = (int)$activeMonthRow->month;
                    } else {
                        $mNum = date('n', strtotime("1 {$activeMonthRow->month} 2000"));
                        if ($mNum) $defaultMonth = (int)$mNum;
                    }
                    if (!empty($activeMonthRow->year)) {
                        $defaultYear = (int)$activeMonthRow->year;
                    }
                }
            } catch (\Throwable $e) { /* fallback */ }

            $month = (int)$request->query('month', $defaultMonth);
            $year = (int)$request->query('year', $defaultYear);
            $departmentId = $request->query('department_id');
            $search = trim($request->query('search', ''));

            $result = $this->computeOrFetchAllStaffPayroll($month, $year, $departmentId, $search);
            $records = $result['records'];
            $monthName = $result['period']['month_name'];

            // ── Column definitions matching compute payroll spreadsheet ────────
            $columns = [
                'IDNO', 'NAME', 'DEPARTMENT', 'BASIC', 'HOUSING', 'TRANSPORT',
                'MEDICAL', 'UTILITY', 'MEAL', 'TOTAL INCOME', 'DECLARED INCOME',
                'PAID DAYS', 'P.TAX', 'IOU', 'RETENTION', 'LOAN', 'SURCHARGES',
                'PENSION', 'MEDICAL LOAN', 'COOP. SAVING', 'COOP. LOAN RPYT',
                'ABSENCE PENALTY', 'LOA.DEDN', 'OTHER DEDUCTION', 'TOTAL DEDUCTION', 'NET PAY',
                'REVOLVING LOAN BAL', 'COOP. CONTR.', 'COOP. LOAN BAL',
                'COOP. ASSET', 'COOP. ASSET FIN.', 'MEDICAL DEBT',
                'ACCOUNT NO.', 'BANK', 'SORT CODE', 'PAYER ID',
            ];

            $dataKeys = [
                'IDNO', 'NAME', 'DEPERTMENT', 'BASIC', 'HOUSING', 'TRANSPORT',
                'MEDICAL', 'UTILITY', 'MEAL', 'TOTAL INCOME', 'DECLARED INCOME',
                'PAID DAYS', 'P.TAX', 'IOU', 'RETENTION', 'LOAN', 'SURGHARGES',
                'PENSION', 'MEDICAL LOAN', 'COOP. SAVING', 'COOP. LOAN RPYT',
                'ABSENCE PENALTY', 'LOA.DEDN', 'OTHER DEDUCTION', 'TOTAL DEDUCTION', 'NETPAY',
                'REVOLVING LOAN BAL', 'COP.CONTR', 'COP. LONE BAL',
                'COOP.ASSET.', 'COP. ASSET FIN', 'MEDICAL DEBT',
                'ACC. NO', 'BANK', 'CODE', 'PAYER ID',
            ];

            // Money column indices (1-based within $columns)
            $moneyColIndices = [4,5,6,7,8,9,10,11,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32];

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle(substr("{$monthName} {$year}", 0, 31));

            $totalCols = count($columns);
            $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

            // ── Row 1: Company Title ───────────────────────────────────────────
            $sheet->mergeCells("A1:{$lastColLetter}1");
            $sheet->setCellValue('A1', 'ISALU HRMS — PAYROLL SCHEDULE');
            $sheet->getStyle('A1')->applyFromArray([
                'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '000000']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension(1)->setRowHeight(28);

            // ── Row 2: Period subtitle ─────────────────────────────────────────
            $sheet->mergeCells("A2:{$lastColLetter}2");
            $sheet->setCellValue('A2', 'Period: ' . ucfirst(strtolower($monthName)) . ' ' . $year);
            $sheet->getStyle('A2')->applyFromArray([
                'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '000000']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension(2)->setRowHeight(20);

            // ── Row 3: Column Headers ──────────────────────────────────────────
            foreach ($columns as $i => $colName) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
                $sheet->setCellValue("{$colLetter}3", $colName);
            }
            $headerRange = "A3:{$lastColLetter}3";
            $sheet->getStyle($headerRange)->applyFromArray([
                'font'      => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '000000']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders'   => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
                ],
            ]);
            $sheet->getRowDimension(3)->setRowHeight(28);

            // ── Rows 4+: Data ──────────────────────────────────────────────────
            $rowNum = 4;
            foreach ($records as $record) {
                // Prepare mapped format identical to compute payroll
                $formattedRecord = [
                    'IDNO'               => $record['id'] ?? '',
                    'NAME'               => $record['name'] ?? '',
                    'DEPERTMENT'         => $record['department'] ?? '',
                    'BASIC'              => $record['basic_salary'] ?? 0,
                    'HOUSING'            => $record['housing_allowance'] ?? 0,
                    'TRANSPORT'          => $record['transport_allowance'] ?? 0,
                    'MEDICAL'            => $record['medical_allowance'] ?? 0,
                    'UTILITY'            => $record['utility_allowance'] ?? 0,
                    'MEAL'               => $record['meal_allowance'] ?? 0,
                    'TOTAL INCOME'       => $record['gross_pay'] ?? 0,
                    'DECLARED INCOME'    => $record['declare_salary'] ?? $record['gross_pay'] ?? 0,
                    'PAID DAYS'          => $record['paid_days'] ?? 30,
                    'P.TAX'              => $record['paye_tax'] ?? 0,
                    'IOU'                => $record['iou'] ?? 0,
                    'RETENTION'          => $record['retention'] ?? 0,
                    'LOAN'               => $record['regular_loan'] ?? 0,
                    'SURGHARGES'         => $record['surcharges'] ?? 0,
                    'PENSION'            => $record['pension'] ?? 0,
                    'MEDICAL LOAN'       => $record['medical_loan'] ?? 0,
                    'COOP. SAVING'       => $record['coop_savings'] ?? 0,
                    'COOP. LOAN RPYT'    => $record['coop_loan'] ?? 0,
                    'ABSENCE PENALTY'    => $record['absence_penalty'] ?? 0,
                    'LOA.DEDN'           => $record['leave_of_absence'] ?? 0,
                    'OTHER DEDUCTION'    => $record['other_deductions'] ?? 0,
                    'TOTAL DEDUCTION'    => $record['total_deductions'] ?? 0,
                    'NETPAY'             => $record['net_pay'] ?? 0,
                    'REVOLVING LOAN BAL' => $record['revolving_loan_bal'] ?? 0,
                    'COP.CONTR'          => $record['coop_contr'] ?? 0,
                    'COP. LONE BAL'      => $record['coop_loan_bal'] ?? 0,
                    'COOP.ASSET.'        => $record['coop_asset'] ?? 0,
                    'COP. ASSET FIN'     => $record['coop_asset_fin'] ?? 0,
                    'MEDICAL DEBT'       => $record['medical_debt'] ?? 0,
                    'ACC. NO'            => $record['account_number'] ?? '',
                    'BANK'               => $record['bank_name'] ?? '',
                    'CODE'               => '',
                    'PAYER ID'           => $record['payer_id'] ?? '',
                ];

                foreach ($dataKeys as $i => $key) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
                    $cellRef = "{$colLetter}{$rowNum}";
                    $colIdx  = $i + 1;

                    $rawVal = $formattedRecord[$key] ?? '';

                    if (in_array($colIdx, $moneyColIndices)) {
                        $clean = str_replace(',', '', (string) $rawVal);
                        $numVal = is_numeric($clean) ? (float) $clean : 0;
                        $sheet->setCellValue($cellRef, $numVal);
                    } else {
                        $sheet->setCellValue($cellRef, $rawVal);
                    }
                }
                $sheet->getRowDimension($rowNum)->setRowHeight(16);
                $rowNum++;
            }

            $dataEndRow = $rowNum - 1;

            if ($dataEndRow >= 4) {
                $moneyFormat = '#,##0.00';
                foreach ($moneyColIndices as $colIdx) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                    $sheet->getStyle("{$colLetter}4:{$colLetter}{$dataEndRow}")->getNumberFormat()->setFormatCode($moneyFormat);
                    $sheet->getStyle("{$colLetter}4:{$colLetter}{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                $sheet->getStyle("A4:A{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("L4:L{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("A4:{$lastColLetter}{$dataEndRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                    'font'    => ['size' => 8],
                ]);

                // Highlight Total Deductions in Red
                $sheet->getStyle("Y4:Y{$dataEndRow}")->applyFromArray([
                    'font' => ['color' => ['rgb' => 'DC2626'], 'bold' => true, 'size' => 8],
                ]);

                // Highlight Net Pay in Green
                $sheet->getStyle("Z4:Z{$dataEndRow}")->applyFromArray([
                    'font' => ['color' => ['rgb' => '008000'], 'bold' => true, 'size' => 8],
                ]);
            }

            // ── Totals Row ─────────────────────────────────────────────────────
            $totalRow = $rowNum;
            $sheet->setCellValue("A{$totalRow}", 'TOTAL');
            $sheet->mergeCells("A{$totalRow}:C{$totalRow}");
            $sheet->getStyle("A{$totalRow}")->applyFromArray([
                'font'      => ['bold' => true, 'size' => 9],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
            ]);

            $dataStartRow = 4;
            foreach ($moneyColIndices as $colIdx) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $cellRef = "{$colLetter}{$totalRow}";
                if ($dataEndRow >= $dataStartRow) {
                    $sheet->setCellValue($cellRef, "=SUM({$colLetter}{$dataStartRow}:{$colLetter}{$dataEndRow})");
                } else {
                    $sheet->setCellValue($cellRef, 0);
                }
                
                $fontColor = '000000';
                if ($colIdx === 25) {
                    $fontColor = 'DC2626'; // Red for Total Deductions
                } elseif ($colIdx === 26) {
                    $fontColor = '008000'; // Green for Net Pay
                }

                $sheet->getStyle($cellRef)->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 9, 'color' => ['rgb' => $fontColor]],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);
                $sheet->getStyle($cellRef)->getNumberFormat()->setFormatCode('#,##0.00');
            }
            $sheet->getRowDimension($totalRow)->setRowHeight(18);

            // ── Column Widths ──────────────────────────────────────────────────
            $manualWidths = [
                1  => 8,   // IDNO
                2  => 28,  // NAME
                3  => 20,  // DEPARTMENT
                12 => 9,   // PAID DAYS
                23 => 14,  // LOA.DEDN
                33 => 18,  // ACCOUNT NO
                34 => 16,  // BANK
                35 => 10,  // SORT CODE
                36 => 14,  // PAYER ID
            ];
            for ($c = 1; $c <= $totalCols; $c++) {
                $cl = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
                $width = $manualWidths[$c] ?? (in_array($c, $moneyColIndices) ? 14 : 12);
                $sheet->getColumnDimension($cl)->setWidth($width);
            }

            $sheet->freezePane('A4');
            $sheet->setAutoFilter("A3:{$lastColLetter}3");

            $filename = "Payroll_{$monthName}_{$year}.xlsx";

            if (class_exists(\XMLWriter::class) && class_exists(\ZipArchive::class)) {
                try {
                    $writer = new Xlsx($spreadsheet);

                    if (ob_get_length()) {
                        ob_clean();
                    }

                    ob_start();
                    $writer->save('php://output');
                    $content = ob_get_clean();

                    return response($content, 200, [
                        'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                        'Pragma'              => 'no-cache',
                        'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                        'Expires'             => '0',
                    ]);
                } catch (\Throwable $xlsxErr) {
                    Log::warning('PhpSpreadsheet Xlsx write error: ' . $xlsxErr->getMessage());
                    return $this->generateExcelHtmlResponse($columns, $dataKeys, $records, $moneyColIndices, 'ISALU HRMS — PAYROLL SCHEDULE', 'Period: ' . ucfirst(strtolower($monthName)) . ' ' . $year, $filename);
                }
            } else {
                return $this->generateExcelHtmlResponse($columns, $dataKeys, $records, $moneyColIndices, 'ISALU HRMS — PAYROLL SCHEDULE', 'Period: ' . ucfirst(strtolower($monthName)) . ' ' . $year, $filename);
            }
        } catch (\Throwable $th) {
            Log::error('SalaryBreakdownApiController exportAllStaffSheet: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/salary-breakdown/staff/export
     * Export individual staff payroll sheet to Excel (.xlsx) spreadsheet format matching compute payroll structure.
     */
    public function exportStaffSheet(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $currentUser = $ctx['employee'];
            $requestedStaffId = $request->query('staff_id');

            // Determine effective staff ID based on role permissions
            $staffId = null;
            if ($ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || $ctx['isAuditStaff'] || $ctx['isFinanceStaff']) {
                $staffId = $requestedStaffId ? (int)$requestedStaffId : ($currentUser ? $currentUser->ID : null);
            } elseif ($currentUser && $currentUser->is_hod == 1) {
                if ($requestedStaffId) {
                    $targetStaff = DB::table('tblper')->where('ID', (int)$requestedStaffId)->first();
                    if ($targetStaff && $targetStaff->departmentID == $currentUser->departmentID) {
                        $staffId = (int)$requestedStaffId;
                    } else {
                        $staffId = $currentUser->ID;
                    }
                } else {
                    $staffId = $currentUser->ID;
                }
            } else {
                $staffId = $currentUser ? $currentUser->ID : null;
            }

            if (!$staffId) {
                return response()->json(['status' => 'error', 'message' => 'Staff record not found.'], 404);
            }

            $month = (int)$request->query('month', date('n'));
            $year = (int)$request->query('year', date('Y'));

            $result = $this->computeOrFetchAllStaffPayroll($month, $year, null, (string)$staffId);
            $matched = collect($result['records'])->firstWhere('id', $staffId);

            if (!$matched) {
                // If search didn't directly match, fetch all and filter
                $fullResult = $this->computeOrFetchAllStaffPayroll($month, $year);
                $matched = collect($fullResult['records'])->firstWhere('id', $staffId);
            }

            $records = $matched ? [$matched] : [];
            $monthName = $result['period']['month_name'];

            $columns = [
                'IDNO', 'NAME', 'DEPARTMENT', 'BASIC', 'HOUSING', 'TRANSPORT',
                'MEDICAL', 'UTILITY', 'MEAL', 'TOTAL INCOME', 'DECLARED INCOME',
                'PAID DAYS', 'P.TAX', 'IOU', 'RETENTION', 'LOAN', 'SURCHARGES',
                'PENSION', 'MEDICAL LOAN', 'COOP. SAVING', 'COOP. LOAN RPYT',
                'ABSENCE PENALTY', 'LOA.DEDN', 'OTHER DEDUCTION', 'TOTAL DEDUCTION', 'NET PAY',
                'REVOLVING LOAN BAL', 'COOP. CONTR.', 'COOP. LOAN BAL',
                'COOP. ASSET', 'COOP. ASSET FIN.', 'MEDICAL DEBT',
                'ACCOUNT NO.', 'BANK', 'SORT CODE', 'PAYER ID',
            ];

            $dataKeys = [
                'IDNO', 'NAME', 'DEPERTMENT', 'BASIC', 'HOUSING', 'TRANSPORT',
                'MEDICAL', 'UTILITY', 'MEAL', 'TOTAL INCOME', 'DECLARED INCOME',
                'PAID DAYS', 'P.TAX', 'IOU', 'RETENTION', 'LOAN', 'SURGHARGES',
                'PENSION', 'MEDICAL LOAN', 'COOP. SAVING', 'COOP. LOAN RPYT',
                'ABSENCE PENALTY', 'LOA.DEDN', 'OTHER DEDUCTION', 'TOTAL DEDUCTION', 'NETPAY',
                'REVOLVING LOAN BAL', 'COP.CONTR', 'COP. LONE BAL',
                'COOP.ASSET.', 'COP. ASSET FIN', 'MEDICAL DEBT',
                'ACC. NO', 'BANK', 'CODE', 'PAYER ID',
            ];

            $moneyColIndices = [4,5,6,7,8,9,10,11,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32];

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle(substr("{$monthName} {$year}", 0, 31));

            $totalCols = count($columns);
            $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

            $sheet->mergeCells("A1:{$lastColLetter}1");
            $sheet->setCellValue('A1', 'ISALU HRMS — PAYROLL SCHEDULE (STAFF RECORD)');
            $sheet->getStyle('A1')->applyFromArray([
                'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '000000']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension(1)->setRowHeight(28);

            $staffName = $matched['name'] ?? "Staff {$staffId}";
            $sheet->mergeCells("A2:{$lastColLetter}2");
            $sheet->setCellValue('A2', 'Staff: ' . $staffName . '  |  Period: ' . ucfirst(strtolower($monthName)) . ' ' . $year);
            $sheet->getStyle('A2')->applyFromArray([
                'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '000000']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension(2)->setRowHeight(20);

            foreach ($columns as $i => $colName) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
                $sheet->setCellValue("{$colLetter}3", $colName);
            }
            $headerRange = "A3:{$lastColLetter}3";
            $sheet->getStyle($headerRange)->applyFromArray([
                'font'      => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '000000']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders'   => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
                ],
            ]);
            $sheet->getRowDimension(3)->setRowHeight(28);

            $rowNum = 4;
            foreach ($records as $record) {
                $formattedRecord = [
                    'IDNO'               => $record['id'] ?? '',
                    'NAME'               => $record['name'] ?? '',
                    'DEPERTMENT'         => $record['department'] ?? '',
                    'BASIC'              => $record['basic_salary'] ?? 0,
                    'HOUSING'            => $record['housing_allowance'] ?? 0,
                    'TRANSPORT'          => $record['transport_allowance'] ?? 0,
                    'MEDICAL'            => $record['medical_allowance'] ?? 0,
                    'UTILITY'            => $record['utility_allowance'] ?? 0,
                    'MEAL'               => $record['meal_allowance'] ?? 0,
                    'TOTAL INCOME'       => $record['gross_pay'] ?? 0,
                    'DECLARED INCOME'    => $record['declare_salary'] ?? $record['gross_pay'] ?? 0,
                    'PAID DAYS'          => $record['paid_days'] ?? 30,
                    'P.TAX'              => $record['paye_tax'] ?? 0,
                    'IOU'                => $record['iou'] ?? 0,
                    'RETENTION'          => $record['retention'] ?? 0,
                    'LOAN'               => $record['regular_loan'] ?? 0,
                    'SURGHARGES'         => $record['surcharges'] ?? 0,
                    'PENSION'            => $record['pension'] ?? 0,
                    'MEDICAL LOAN'       => $record['medical_loan'] ?? 0,
                    'COOP. SAVING'       => $record['coop_savings'] ?? 0,
                    'COOP. LOAN RPYT'    => $record['coop_loan'] ?? 0,
                    'ABSENCE PENALTY'    => $record['absence_penalty'] ?? 0,
                    'LOA.DEDN'           => $record['leave_of_absence'] ?? 0,
                    'OTHER DEDUCTION'    => $record['other_deductions'] ?? 0,
                    'TOTAL DEDUCTION'    => $record['total_deductions'] ?? 0,
                    'NETPAY'             => $record['net_pay'] ?? 0,
                    'REVOLVING LOAN BAL' => $record['revolving_loan_bal'] ?? 0,
                    'COP.CONTR'          => $record['coop_contr'] ?? 0,
                    'COP. LONE BAL'      => $record['coop_loan_bal'] ?? 0,
                    'COOP.ASSET.'        => $record['coop_asset'] ?? 0,
                    'COP. ASSET FIN'     => $record['coop_asset_fin'] ?? 0,
                    'MEDICAL DEBT'       => $record['medical_debt'] ?? 0,
                    'ACC. NO'            => $record['account_number'] ?? '',
                    'BANK'               => $record['bank_name'] ?? '',
                    'CODE'               => '',
                    'PAYER ID'           => $record['payer_id'] ?? '',
                ];

                foreach ($dataKeys as $i => $key) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
                    $cellRef = "{$colLetter}{$rowNum}";
                    $colIdx  = $i + 1;

                    $rawVal = $formattedRecord[$key] ?? '';

                    if (in_array($colIdx, $moneyColIndices)) {
                        $clean = str_replace(',', '', (string) $rawVal);
                        $numVal = is_numeric($clean) ? (float) $clean : 0;
                        $sheet->setCellValue($cellRef, $numVal);
                    } else {
                        $sheet->setCellValue($cellRef, $rawVal);
                    }
                }
                $sheet->getRowDimension($rowNum)->setRowHeight(16);
                $rowNum++;
            }

            $dataEndRow = $rowNum - 1;

            if ($dataEndRow >= 4) {
                $moneyFormat = '#,##0.00';
                foreach ($moneyColIndices as $colIdx) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                    $sheet->getStyle("{$colLetter}4:{$colLetter}{$dataEndRow}")->getNumberFormat()->setFormatCode($moneyFormat);
                    $sheet->getStyle("{$colLetter}4:{$colLetter}{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                $sheet->getStyle("A4:A{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("L4:L{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("A4:{$lastColLetter}{$dataEndRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                    'font'    => ['size' => 8],
                ]);

                // Highlight Total Deductions in Red
                $sheet->getStyle("Y4:Y{$dataEndRow}")->applyFromArray([
                    'font' => ['color' => ['rgb' => 'DC2626'], 'bold' => true, 'size' => 8],
                ]);

                // Highlight Net Pay in Green
                $sheet->getStyle("Z4:Z{$dataEndRow}")->applyFromArray([
                    'font' => ['color' => ['rgb' => '008000'], 'bold' => true, 'size' => 8],
                ]);
            }

            $manualWidths = [
                1  => 8,   // IDNO
                2  => 28,  // NAME
                3  => 20,  // DEPARTMENT
                12 => 9,   // PAID DAYS
                23 => 14,  // LOA.DEDN
                33 => 18,  // ACCOUNT NO
                34 => 16,  // BANK
                35 => 10,  // SORT CODE
                36 => 14,  // PAYER ID
            ];
            for ($c = 1; $c <= $totalCols; $c++) {
                $cl = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
                $width = $manualWidths[$c] ?? (in_array($c, $moneyColIndices) ? 14 : 12);
                $sheet->getColumnDimension($cl)->setWidth($width);
            }

            $sheet->freezePane('A4');
            $sheet->setAutoFilter("A3:{$lastColLetter}3");

            $safeStaffName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $staffName);
            $filename = "Payroll_{$safeStaffName}_{$monthName}_{$year}.xlsx";

            if (class_exists(\XMLWriter::class) && class_exists(\ZipArchive::class)) {
                try {
                    $writer = new Xlsx($spreadsheet);

                    if (ob_get_length()) {
                        ob_clean();
                    }

                    ob_start();
                    $writer->save('php://output');
                    $content = ob_get_clean();

                    return response($content, 200, [
                        'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                        'Pragma'              => 'no-cache',
                        'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                        'Expires'             => '0',
                    ]);
                } catch (\Throwable $xlsxErr) {
                    Log::warning('PhpSpreadsheet Xlsx write error: ' . $xlsxErr->getMessage());
                    return $this->generateExcelHtmlResponse($columns, $dataKeys, $records, $moneyColIndices, 'ISALU HRMS — PAYROLL SCHEDULE', 'Staff: ' . $staffName . ' | Period: ' . ucfirst(strtolower($monthName)) . ' ' . $year, $filename);
                }
            } else {
                return $this->generateExcelHtmlResponse($columns, $dataKeys, $records, $moneyColIndices, 'ISALU HRMS — PAYROLL SCHEDULE', 'Staff: ' . $staffName . ' | Period: ' . ucfirst(strtolower($monthName)) . ' ' . $year, $filename);
            }
        } catch (\Throwable $th) {
            Log::error('SalaryBreakdownApiController exportStaffSheet: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * Generate styled Excel (.xls / .xlsx) HTML document for servers lacking XMLWriter/ext-zip.
     */
    private function generateExcelHtmlResponse(array $columns, array $dataKeys, array $records, array $moneyColIndices, string $title, string $subtitle, string $filename)
    {
        $totalCols = count($columns);
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">' . "\n";
        $html .= '<head>' . "\n";
        $html .= '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Payroll</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->' . "\n";
        $html .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
        $html .= '<style>' . "\n";
        $html .= 'body { font-family: Calibri, Arial, sans-serif; font-size: 11px; }' . "\n";
        $html .= 'table { border-collapse: collapse; width: 100%; }' . "\n";
        $html .= '.title-row { background-color: #000000; color: #FFFFFF; font-size: 14px; font-weight: bold; text-align: center; height: 35px; }' . "\n";
        $html .= '.subtitle-row { background-color: #000000; color: #FFFFFF; font-size: 11px; font-weight: bold; text-align: center; height: 25px; }' . "\n";
        $html .= '.header-cell { background-color: #000000; color: #FFFFFF; font-size: 9px; font-weight: bold; text-align: center; border: 1px solid #000000; padding: 6px; }' . "\n";
        $html .= '.cell-text { border: 1px solid #000000; padding: 4px; font-size: 9px; text-align: left; mso-number-format:"\@"; }' . "\n";
        $html .= '.cell-center { border: 1px solid #000000; padding: 4px; font-size: 9px; text-align: center; }' . "\n";
        $html .= '.cell-money { border: 1px solid #000000; padding: 4px; font-size: 9px; text-align: right; mso-number-format:"\#\,\#\#0\.00"; }' . "\n";
        $html .= '.cell-deduct { border: 1px solid #000000; padding: 4px; font-size: 9px; text-align: right; color: #DC2626; font-weight: bold; mso-number-format:"\#\,\#\#0\.00"; }' . "\n";
        $html .= '.cell-netpay { border: 1px solid #000000; padding: 4px; font-size: 9px; text-align: right; color: #008000; font-weight: bold; mso-number-format:"\#\,\#\#0\.00"; }' . "\n";
        $html .= '.total-label { border: 1px solid #000000; font-weight: bold; font-size: 10px; text-align: center; }' . "\n";
        $html .= '.total-money { border: 1px solid #000000; font-weight: bold; font-size: 10px; text-align: right; mso-number-format:"\#\,\#\#0\.00"; }' . "\n";
        $html .= '.total-deduct { border: 1px solid #000000; font-weight: bold; font-size: 10px; text-align: right; color: #DC2626; mso-number-format:"\#\,\#\#0\.00"; }' . "\n";
        $html .= '.total-netpay { border: 1px solid #000000; font-weight: bold; font-size: 10px; text-align: right; color: #008000; mso-number-format:"\#\,\#\#0\.00"; }' . "\n";
        $html .= '</style>' . "\n";
        $html .= '</head>' . "\n";
        $html .= '<body>' . "\n";
        $html .= '<table>' . "\n";

        // Row 1: Title
        $html .= '<tr><td colspan="' . $totalCols . '" class="title-row">' . htmlspecialchars($title) . '</td></tr>' . "\n";
        // Row 2: Subtitle
        $html .= '<tr><td colspan="' . $totalCols . '" class="subtitle-row">' . htmlspecialchars($subtitle) . '</td></tr>' . "\n";

        // Row 3: Headers
        $html .= '<tr>';
        foreach ($columns as $col) {
            $html .= '<th class="header-cell">' . htmlspecialchars($col) . '</th>';
        }
        $html .= '</tr>' . "\n";

        $totals = array_fill(1, $totalCols, 0.0);

        // Data Rows
        foreach ($records as $record) {
            $formattedRecord = [
                'IDNO'               => $record['id'] ?? '',
                'NAME'               => $record['name'] ?? '',
                'DEPERTMENT'         => $record['department'] ?? '',
                'BASIC'              => $record['basic_salary'] ?? 0,
                'HOUSING'            => $record['housing_allowance'] ?? 0,
                'TRANSPORT'          => $record['transport_allowance'] ?? 0,
                'MEDICAL'            => $record['medical_allowance'] ?? 0,
                'UTILITY'            => $record['utility_allowance'] ?? 0,
                'MEAL'               => $record['meal_allowance'] ?? 0,
                'TOTAL INCOME'       => $record['gross_pay'] ?? 0,
                'DECLARED INCOME'    => $record['declare_salary'] ?? $record['gross_pay'] ?? 0,
                'PAID DAYS'          => $record['paid_days'] ?? 30,
                'P.TAX'              => $record['paye_tax'] ?? 0,
                'IOU'                => $record['iou'] ?? 0,
                'RETENTION'          => $record['retention'] ?? 0,
                'LOAN'               => $record['regular_loan'] ?? 0,
                'SURGHARGES'         => $record['surcharges'] ?? 0,
                'PENSION'            => $record['pension'] ?? 0,
                'MEDICAL LOAN'       => $record['medical_loan'] ?? 0,
                'COOP. SAVING'       => $record['coop_savings'] ?? 0,
                'COOP. LOAN RPYT'    => $record['coop_loan'] ?? 0,
                'ABSENCE PENALTY'    => $record['absence_penalty'] ?? 0,
                'LOA.DEDN'           => $record['leave_of_absence'] ?? 0,
                'OTHER DEDUCTION'    => $record['other_deductions'] ?? 0,
                'TOTAL DEDUCTION'    => $record['total_deductions'] ?? 0,
                'NETPAY'             => $record['net_pay'] ?? 0,
                'REVOLVING LOAN BAL' => $record['revolving_loan_bal'] ?? 0,
                'COP.CONTR'          => $record['coop_contr'] ?? 0,
                'COP. LONE BAL'      => $record['coop_loan_bal'] ?? 0,
                'COOP.ASSET.'        => $record['coop_asset'] ?? 0,
                'COP. ASSET FIN'     => $record['coop_asset_fin'] ?? 0,
                'MEDICAL DEBT'       => $record['medical_debt'] ?? 0,
                'ACC. NO'            => $record['account_number'] ?? '',
                'BANK'               => $record['bank_name'] ?? '',
                'CODE'               => '',
                'PAYER ID'           => $record['payer_id'] ?? '',
            ];

            $html .= '<tr>';
            foreach ($dataKeys as $i => $key) {
                $colIdx = $i + 1;
                $rawVal = $formattedRecord[$key] ?? '';
                if (in_array($colIdx, $moneyColIndices)) {
                    $clean = str_replace(',', '', (string)$rawVal);
                    $numVal = is_numeric($clean) ? (float)$clean : 0.0;
                    $totals[$colIdx] += $numVal;
                    $cls = 'cell-money';
                    if ($colIdx === 25) $cls = 'cell-deduct';
                    elseif ($colIdx === 26) $cls = 'cell-netpay';
                    $html .= '<td class="' . $cls . '">' . number_format($numVal, 2, '.', ',') . '</td>';
                } elseif ($colIdx === 1 || $colIdx === 12) {
                    $html .= '<td class="cell-center">' . htmlspecialchars((string)$rawVal) . '</td>';
                } else {
                    $html .= '<td class="cell-text">' . htmlspecialchars((string)$rawVal) . '</td>';
                }
            }
            $html .= '</tr>' . "\n";
        }

        // Totals row
        $html .= '<tr>';
        $html .= '<td colspan="3" class="total-label">TOTAL</td>';
        for ($c = 4; $c <= $totalCols; $c++) {
            if (in_array($c, $moneyColIndices)) {
                $cls = 'total-money';
                if ($c === 25) $cls = 'total-deduct';
                elseif ($c === 26) $cls = 'total-netpay';
                $html .= '<td class="' . $cls . '">' . number_format($totals[$c] ?? 0.0, 2, '.', ',') . '</td>';
            } else {
                $html .= '<td class="total-label"></td>';
            }
        }
        $html .= '</tr>' . "\n";
        $html .= '</table></body></html>';

        if (ob_get_length()) {
            ob_clean();
        }

        return response($html, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }

    /**
     * Helper: compute or fetch all-staff payroll data for a given month and year.
     */
    private function computeOrFetchAllStaffPayroll(int $month, int $year, ?string $departmentId = null, string $search = ''): array
    {
        $currentMonthStr = sprintf("%04d-%02d", $year, $month);
        $monthName = date('F', mktime(0, 0, 0, $month, 1, $year));
        $firstDay = \Carbon\Carbon::create($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $lastDay = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');

        // Check if payroll run exists and is completed for this period
        $run = null;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('payroll_runs')) {
                $run = DB::table('payroll_runs')
                    ->where('month', $month)
                    ->where('year', $year)
                    ->first();
            }
        } catch (\Throwable $e) { /* ignore */ }

        $isComputed = false;
        if ($run !== null && \Illuminate\Support\Facades\Schema::hasTable('payroll_conpt')) {
            $isComputed = DB::table('payroll_conpt')->where('payroll_run_id', $run->id)->exists();
        }

        // Fetch departments list for filter dropdown
        $departments = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('tbldepartment')) {
            $departments = DB::table('tbldepartment')
                ->select('id', 'department as name')
                ->orderBy('department', 'asc')
                ->get();
        }

        $hasPayerIdTblper = \Illuminate\Support\Facades\Schema::hasColumn('tblper', 'payer_id');
        $hasPayerIdPc = \Illuminate\Support\Facades\Schema::hasColumn('payroll_conpt', 'payer_id');
        $hasRetentionPc = \Illuminate\Support\Facades\Schema::hasColumn('payroll_conpt', 'retention');
        $hasSurchargesPc = \Illuminate\Support\Facades\Schema::hasColumn('payroll_conpt', 'surcharges');
        $hasMedicalLoanPc = \Illuminate\Support\Facades\Schema::hasColumn('payroll_conpt', 'medical_loan');
        $hasCoopLoanRpytPc = \Illuminate\Support\Facades\Schema::hasColumn('payroll_conpt', 'coop_loan_rpyt');
        $hasCoopAssetPc = \Illuminate\Support\Facades\Schema::hasColumn('payroll_conpt', 'coop_asset_finance');
        $hasLoaPc = \Illuminate\Support\Facades\Schema::hasColumn('payroll_conpt', 'leave_of_absence_deduction');

        if ($isComputed) {
            // Fetch directly from computed payroll_conpt table
            $query = DB::table('payroll_conpt as pc')
                ->join('tblper as p', 'p.ID', '=', 'pc.staffID')
                ->leftJoin('salary_structures as ss', 'ss.staffId', '=', 'pc.staffID')
                ->leftJoin('tbldepartment as dept', 'dept.id', '=', 'p.departmentID')
                ->leftJoin('tbldesignation as des', 'des.id', '=', 'p.designation')
                ->leftJoin('tblbanklist as bl', 'bl.bankID', '=', 'p.bankID')
                ->where('pc.payroll_run_id', $run->id);

            if (!empty($departmentId)) {
                $query->where('p.departmentID', $departmentId);
            }

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where(DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, ''))"), 'like', "%{$search}%")
                      ->orWhere('p.ID', 'like', "%{$search}%");
                });
            }

            $selectFields = [
                'pc.staffID as id',
                'p.fileNo as file_no',
                DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as name"),
                'dept.department',
                'des.designation',
                'pc.basic as basic_salary',
                'pc.housing as housing_allowance',
                'pc.transport as transport_allowance',
                'pc.medical as medical_allowance',
                'pc.utility as utility_allowance',
                'pc.meal as meal_allowance',
                'pc.paid_days',
                'pc.gross_pay',
                DB::raw('COALESCE(pc.declare_income, ss.declare_salary, 0) as declare_salary'),
                'pc.paye_tax',
                'pc.loan_deduction as regular_loan',
                'pc.pension',
                'pc.coop_savings',
                'pc.other_deductions',
                $hasRetentionPc ? 'pc.retention' : DB::raw('0 as retention'),
                $hasSurchargesPc ? 'pc.surcharges' : DB::raw('0 as surcharges'),
                $hasMedicalLoanPc ? 'pc.medical_loan' : DB::raw('0 as medical_loan'),
                $hasCoopLoanRpytPc ? 'pc.coop_loan_rpyt as coop_loan' : DB::raw('0 as coop_loan'),
                $hasCoopAssetPc ? DB::raw('COALESCE(pc.coop_asset_finance, 0) as coop_asset_finance') : DB::raw('0 as coop_asset_finance'),
                'pc.iou',
                'pc.absence_penalty',
                $hasLoaPc ? 'pc.leave_of_absence_deduction as leave_of_absence' : DB::raw('0 as leave_of_absence'),
                'pc.total_deductions',
                'pc.net_pay',
                'p.AccNo as account_number',
                'bl.bank as bank_name',
                $hasPayerIdPc ? 'pc.payer_id' : ($hasPayerIdTblper ? 'p.payer_id' : DB::raw("'' as payer_id")),
            ];

            $rows = $query->select($selectFields)->orderBy('p.surname', 'asc')->get();

            $staffIds = $rows->pluck('id')->toArray();
            $loanBals = \Illuminate\Support\Facades\Schema::hasTable('loan_deduction_setups') ? DB::table('loan_deduction_setups')->whereIn('staffId', $staffIds)->where('is_active', 1)->pluck('balance_remaining', 'staffId')->toArray() : [];
            $empLoanBals = \Illuminate\Support\Facades\Schema::hasTable('employee_loans') ? DB::table('employee_loans')->whereIn('staffId', $staffIds)->whereRaw("LOWER(status) = 'approved'")->pluck('balance', 'staffId')->toArray() : [];
            $coopSavingsBals = \Illuminate\Support\Facades\Schema::hasTable('coop_savings_setups') ? DB::table('coop_savings_setups')->whereIn('staffId', $staffIds)->where('is_active', 1)->pluck('saving_balance', 'staffId')->toArray() : [];
            $coopLoanBals = \Illuminate\Support\Facades\Schema::hasTable('coop_loan_deduction_setups') ? DB::table('coop_loan_deduction_setups')->whereIn('staffId', $staffIds)->where('is_active', 1)->pluck('balance_remaining', 'staffId')->toArray() : [];
            $coopAssetBals = \Illuminate\Support\Facades\Schema::hasTable('coop_asset_finance_deduction_setups') ? DB::table('coop_asset_finance_deduction_setups')->whereIn('staffId', $staffIds)->where('is_active', 1)->pluck('balance_remaining', 'staffId')->toArray() : [];
            $medLoanBals = \Illuminate\Support\Facades\Schema::hasTable('medical_loan_deduction_setups') ? DB::table('medical_loan_deduction_setups')->whereIn('staffId', $staffIds)->where('is_active', 1)->pluck('balance_remaining', 'staffId')->toArray() : [];

            $mapped = $rows->map(function($r) use ($loanBals, $empLoanBals, $coopSavingsBals, $coopLoanBals, $coopAssetBals, $medLoanBals) {
                $sid = $r->id;
                $basicSum = (float)$r->basic_salary + (float)$r->housing_allowance + (float)$r->transport_allowance + (float)$r->medical_allowance + (float)$r->utility_allowance + (float)$r->meal_allowance;
                $varAllowances = max(0.00, (float)$r->gross_pay - $basicSum);

                $revolvingLoanBal = isset($loanBals[$sid]) ? (float)$loanBals[$sid] : (isset($empLoanBals[$sid]) ? (float)$empLoanBals[$sid] : 0.00);
                $coopContr = (float)($coopSavingsBals[$sid] ?? 0.00);
                $coopLoanBal = (float)($coopLoanBals[$sid] ?? 0.00);
                $coopAssetDeduct = (float)($r->coop_asset_finance ?? 0);
                $coopAssetFin = (float)($coopAssetBals[$sid] ?? 0.00);
                $medDebt = (float)($medLoanBals[$sid] ?? 0.00);
                $paidDays = isset($r->paid_days) ? (int)$r->paid_days : 30;

                return [
                    'id' => $r->id,
                    'name' => trim($r->name),
                    'department' => $r->department ?? 'General',
                    'designation' => $r->designation ?? 'Staff',
                    'basic_salary' => (float)$r->basic_salary,
                    'housing_allowance' => (float)$r->housing_allowance,
                    'transport_allowance' => (float)$r->transport_allowance,
                    'medical_allowance' => (float)$r->medical_allowance,
                    'utility_allowance' => (float)$r->utility_allowance,
                    'meal_allowance' => (float)$r->meal_allowance,
                    'variable_allowances' => round($varAllowances, 2),
                    'gross_pay' => (float)$r->gross_pay,
                    'declare_salary' => (float)$r->declare_salary,
                    'paye_tax' => (float)$r->paye_tax,
                    'pension' => (float)$r->pension,
                    'retention' => (float)$r->retention,
                    'iou' => (float)$r->iou,
                    'medical_loan' => (float)$r->medical_loan,
                    'coop_loan' => (float)$r->coop_loan,
                    'coop_savings' => (float)$r->coop_savings,
                    'coop_asset_finance' => (float)($r->coop_asset_finance ?? 0),
                    'surcharges' => (float)$r->surcharges,
                    'absence_penalty' => (float)$r->absence_penalty,
                    'leave_of_absence' => (float)$r->leave_of_absence,
                    'regular_loan' => (float)$r->regular_loan,
                    'other_deductions' => (float)$r->other_deductions,
                    'total_deductions' => (float)$r->total_deductions,
                    'net_pay' => (float)$r->net_pay,
                    'bank_name' => $r->bank_name ?? '—',
                    'account_number' => $r->account_number ?? '—',
                    'payer_id' => $r->payer_id ?? '—',
                    'paid_days' => $paidDays,
                    'days_absent' => max(0, 30 - $paidDays),
                    'revolving_loan_bal' => $revolvingLoanBal,
                    'coop_contr' => $coopContr,
                    'coop_loan_bal' => $coopLoanBal,
                    'coop_asset' => $coopAssetDeduct,
                    'coop_asset_fin' => $coopAssetFin,
                    'medical_debt' => $medDebt,
                ];
            });

            $summary = [
                'total_staff' => $mapped->count(),
                'total_gross' => round($mapped->sum('gross_pay'), 2),
                'total_deductions' => round($mapped->sum('total_deductions'), 2),
                'total_net_pay' => round($mapped->sum('net_pay'), 2),
                'status' => 'Finalized Payroll Record'
            ];

            return [
                'records' => $mapped->values()->toArray(),
                'summary' => $summary,
                'period' => [
                    'month' => $month,
                    'year' => $year,
                    'month_name' => $monthName,
                    'period_str' => "{$monthName} {$year}",
                ],
                'departments' => $departments,
                'is_computed' => true
            ];
        }

        // --- PRE-COMPUTE: Live calculation from salary structures and deduction setups ---

        // 1. Fetch active staff
        $staffQuery = DB::table('tblper as p')
            ->leftJoin('tbldepartment as dept', 'dept.id', '=', 'p.departmentID')
            ->leftJoin('tbldesignation as des', 'des.id', '=', 'p.designation')
            ->leftJoin('tblbanklist as bl', 'bl.bankID', '=', 'p.bankID')
            ->where(function($q) {
                $q->where('p.rank', '!=', 2)
                  ->orWhereNull('p.rank');
            })
            ->where(function($q) {
                $q->where('p.staff_status', 1)
                  ->orWhere('p.staff_status', '1')
                  ->orWhereNull('p.staff_status');
            });

        if (!empty($departmentId)) {
            $staffQuery->where('p.departmentID', $departmentId);
        }

        if (!empty($search)) {
            $staffQuery->where(function($q) use ($search) {
                $q->where(DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, ''))"), 'like', "%{$search}%")
                  ->orWhere('p.ID', 'like', "%{$search}%");
            });
        }

        $allStaff = $staffQuery->select([
            'p.ID as id',
            'p.fileNo as file_no',
            DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as name"),
            'dept.department',
            'des.designation',
            'p.AccNo as account_number',
            'bl.bank as bank_name',
            $hasPayerIdTblper ? 'p.payer_id' : DB::raw("'' as payer_id"),
        ])->orderBy('p.surname', 'asc')->get();

        $staffIds = $allStaff->pluck('id')->toArray();

        // 2. Fetch salary structures
        $structures = \Illuminate\Support\Facades\Schema::hasTable('salary_structures') ? DB::table('salary_structures')->whereIn('staffId', $staffIds)->get()->keyBy('staffId') : collect();

        // 3. Fetch first salary structures (for retention)
        $firstStructures = \Illuminate\Support\Facades\Schema::hasTable('first_salary_structure') ? DB::table('first_salary_structure')->whereIn('staffId', $staffIds)->get()->keyBy('staffId') : collect();

        // 4. Fetch variable earnings from staffEarningAndDeduction
        $earningVarsByStaff = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('staffEarningAndDeduction')) {
            try {
                $eVars = DB::table('staffEarningAndDeduction')
                    ->whereIn('staffID', $staffIds)
                    ->where('status', 1)
                    ->get();
                foreach ($eVars as $ev) {
                    $sid = $ev->staffID;
                    $earningVarsByStaff[$sid] = ($earningVarsByStaff[$sid] ?? 0.00) + (float)$ev->amount;
                }
            } catch (\Throwable $e) { /* ignore */ }
        }

        // 5. Fetch IOUs for selected month
        $iousByStaff = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('iou_records')) {
            try {
                $ious = DB::table('iou_records')
                    ->whereIn('staff_id', $staffIds)
                    ->where('status', 1)
                    ->whereBetween('iou_date', [$firstDay, $lastDay])
                    ->groupBy('staff_id')
                    ->select('staff_id', DB::raw('SUM(amount) as total_iou'))
                    ->get();
                foreach ($ious as $i) {
                    $iousByStaff[$i->staff_id] = (float)$i->total_iou;
                }
            } catch (\Throwable $e) { /* ignore */ }
        }

        // 6. Fetch Medical Loan setups
        $medLoanSetups = \Illuminate\Support\Facades\Schema::hasTable('medical_loan_deduction_setups') ? DB::table('medical_loan_deduction_setups')->whereIn('staffId', $staffIds)->where('is_active', 1)->where('balance_remaining', '>', 0)->where('start_month', '<=', $currentMonthStr)->where('end_month', '>=', $currentMonthStr)->get()->keyBy('staffId') : collect();

        // 7. Fetch Coop Loan setups
        $coopLoanSetups = \Illuminate\Support\Facades\Schema::hasTable('coop_loan_deduction_setups') ? DB::table('coop_loan_deduction_setups')->whereIn('staffId', $staffIds)->where('is_active', 1)->where('balance_remaining', '>', 0)->where('start_month', '<=', $currentMonthStr)->where('end_month', '>=', $currentMonthStr)->get()->keyBy('staffId') : collect();

        // 8. Fetch Coop Savings setups
        $coopSavingsSetups = \Illuminate\Support\Facades\Schema::hasTable('coop_savings_setups') ? DB::table('coop_savings_setups')->whereIn('staffId', $staffIds)->where('is_active', 1)->where('start_month', '<=', $currentMonthStr)->get()->keyBy('staffId') : collect();

        // 9. Fetch Coop Asset Finance setups
        $coopAssetSetups = \Illuminate\Support\Facades\Schema::hasTable('coop_asset_finance_deduction_setups') ? DB::table('coop_asset_finance_deduction_setups')->whereIn('staffId', $staffIds)->where('is_active', 1)->where('balance_remaining', '>', 0)->where('start_month', '<=', $currentMonthStr)->where(function($q) use ($currentMonthStr) { $q->whereNull('end_month')->orWhere('end_month', '=', '')->orWhere('end_month', '>=', $currentMonthStr); })->get()->keyBy('staffId') : collect();

        // 10. Fetch Surcharge setups
        $surchargeSetups = \Illuminate\Support\Facades\Schema::hasTable('surcharge_deduction_setups') ? DB::table('surcharge_deduction_setups')->whereIn('staffId', $staffIds)->where('is_active', 1)->where('balance_remaining', '>', 0)->where('start_month', '<=', $currentMonthStr)->where(function($q) use ($currentMonthStr) { $q->whereNull('end_month')->orWhere('end_month', '=', '')->orWhere('end_month', '>=', $currentMonthStr); })->get()->keyBy('staffId') : collect();

        // 11. Fetch Absence Penalty setups
        $absencePenaltySetups = \Illuminate\Support\Facades\Schema::hasTable('absence_penalty_deduction_setups') ? DB::table('absence_penalty_deduction_setups')->whereIn('staffId', $staffIds)->where('is_active', 1)->where(function($q) { $q->where('balance_remaining', '>', 0)->orWhere('total_amount', '>', 0); })->where('start_month', '<=', $currentMonthStr)->where(function($q) use ($currentMonthStr) { $q->whereNull('end_month')->orWhere('end_month', '=', '')->orWhere('end_month', '>=', $currentMonthStr); })->get()->keyBy('staffId') : collect();

        // 12. Fetch Loan Deduction Setups & Employee Loans
        $loanSetups = \Illuminate\Support\Facades\Schema::hasTable('loan_deduction_setups') ? DB::table('loan_deduction_setups')
            ->whereIn('staffId', $staffIds)
            ->where('is_active', 1)
            ->where('balance_remaining', '>', 0)
            ->where('start_month', '<=', $currentMonthStr)
            ->where('end_month', '>=', $currentMonthStr)
            ->get()
            ->keyBy('staffId') : collect();

        $empLoans = \Illuminate\Support\Facades\Schema::hasTable('employee_loans') ? DB::table('employee_loans')
            ->whereIn('staffId', $staffIds)
            ->whereRaw("LOWER(status) = 'approved'")
            ->where('balance', '>', 0)
            ->orderBy('id', 'desc')
            ->get()
            ->keyBy('staffId') : collect();

        // 13. Fetch Other Deduction Setups
        $otherDeductSetups = \Illuminate\Support\Facades\Schema::hasTable('other_deduction_setups') ? DB::table('other_deduction_setups')
            ->whereIn('staffId', $staffIds)
            ->where('is_active', 1)
            ->where(function($q) {
                $q->where('balance_remaining', '>', 0)
                  ->orWhere('total_amount', '>', 0);
            })
            ->where('start_month', '<=', $currentMonthStr)
            ->where(function($q) use ($currentMonthStr) {
                $q->whereNull('end_month')
                  ->orWhere('end_month', '=', '')
                  ->orWhere('end_month', '>=', $currentMonthStr);
            })
            ->get()
            ->keyBy('staffId') : collect();

        // 14. Fetch LOA days
        $loaDaysByStaff = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('leave_of_absent')) {
                $leaves = DB::table('leave_of_absent')
                    ->whereIn('staffId', $staffIds)
                    ->where('status', 2)
                    ->where(function($query) use ($firstDay, $lastDay) {
                        $query->whereBetween('start_date', [$firstDay, $lastDay])
                              ->orWhereBetween('end_date', [$firstDay, $lastDay])
                              ->orWhere(function($q) use ($firstDay, $lastDay) {
                                  $q->where('start_date', '<=', $firstDay)
                                    ->where('end_date', '>=', $lastDay);
                              });
                    })
                    ->get();

                $startOfMonth = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
                $endOfMonth = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

                foreach ($leaves as $leave) {
                    $start = \Carbon\Carbon::parse($leave->start_date);
                    $end = \Carbon\Carbon::parse($leave->end_date);
                    $overlapStart = $start->greaterThan($startOfMonth) ? $start : $startOfMonth;
                    $overlapEnd = $end->lessThan($endOfMonth) ? $end : $endOfMonth;
                    $days = ($overlapStart->diffInDays($overlapEnd) + 1);
                    $loaDaysByStaff[$leave->staffId] = ($loaDaysByStaff[$leave->staffId] ?? 0) + $days;
                }
            }
        } catch (\Throwable $e) { /* ignore */ }

        // Compute rows for each staff member
        $calculatedRows = [];

        foreach ($allStaff as $staff) {
            $sid = $staff->id;
            $struct = $structures[$sid] ?? null;
            $firstStruct = $firstStructures[$sid] ?? null;

            $basic = $struct ? (float)$struct->basic_salary : 0.00;
            $housing = $struct ? (float)$struct->housing_allowance : 0.00;
            $transport = $struct ? (float)$struct->transport_allowance : 0.00;
            $medical = $struct ? (float)$struct->medical_allowance : 0.00;
            $utility = $struct ? (float)$struct->utility_allowance : 0.00;
            $meal = $struct ? (float)$struct->meal_allowance : 0.00;
            $pensionRate = $struct ? (float)$struct->pension_rate : 0.00;
            $declareSalary = $struct ? (float)$struct->declare_salary : 0.00;
            $penAct = $struct ? (int)$struct->pen_act : 0;

            $basicAllowances = $basic + $housing + $transport + $medical + $utility + $meal;
            $varAllowances = $earningVarsByStaff[$sid] ?? 0.00;
            $grossPay = $basicAllowances + $varAllowances;

            // PAYE Tax (Nigeria 2025/2026 progressive bands)
            $annualGross = $declareSalary * 12.0;
            $annualPension = 0.00;
            if ($penAct == 1) {
                $rate = ($pensionRate > 0) ? ($pensionRate / 100.0) : 0.08;
                $annualPension = ($annualGross * 0.5) * $rate;
            }
            $annualTaxable = max(0.00, $annualGross - $annualPension);
            $annualTax = 0.00;
            if ($annualTaxable > 800000.00) {
                $taxableRemaining = $annualTaxable - 800000.00;
                $band1 = min(2200000.00, $taxableRemaining);
                $annualTax += $band1 * 0.15;
                $taxableRemaining -= $band1;
                if ($taxableRemaining > 0) {
                    $band2 = min(9000000.00, $taxableRemaining);
                    $annualTax += $band2 * 0.18;
                    $taxableRemaining -= $band2;
                }
                if ($taxableRemaining > 0) {
                    $band3 = min(13000000.00, $taxableRemaining);
                    $annualTax += $band3 * 0.21;
                    $taxableRemaining -= $band3;
                }
                if ($taxableRemaining > 0) {
                    $band4 = min(25000000.00, $taxableRemaining);
                    $annualTax += $band4 * 0.23;
                    $taxableRemaining -= $band4;
                }
                if ($taxableRemaining > 0) {
                    $annualTax += $taxableRemaining * 0.25;
                }
            }
            $payeTax = round($annualTax / 12.0, 2);

            // Pension
            $pension = 0.00;
            if ($penAct == 1) {
                $rate = ($pensionRate > 0) ? ($pensionRate / 100.0) : 0.08;
                $pension = round(($basicAllowances * 0.5) * $rate, 2);
            }

            // Retention
            $retention = 0.00;
            if ($firstStruct && $firstStruct->reten_act == 1) {
                $retentionMonths = (int)($firstStruct->num_rente_months ?? 0);
                if ($retentionMonths < 20) {
                    $retentionBase = (float)$firstStruct->basic_salary +
                                     (float)$firstStruct->housing_allowance +
                                     (float)$firstStruct->transport_allowance +
                                     (float)$firstStruct->medical_allowance +
                                     (float)$firstStruct->utility_allowance +
                                     (float)$firstStruct->meal_allowance;
                    $retention = round(0.05 * $retentionBase, 2);
                }
            }

            // IOU
            $iou = $iousByStaff[$sid] ?? 0.00;

            // Medical Loan
            $medSetup = $medLoanSetups[$sid] ?? null;
            $medLoan = $medSetup ? min((float)$medSetup->monthly_deduction, (float)$medSetup->balance_remaining) : 0.00;

            // Coop Loan
            $cpLoanSetup = $coopLoanSetups[$sid] ?? null;
            $coopLoan = $cpLoanSetup ? min((float)$cpLoanSetup->monthly_deduction, (float)$cpLoanSetup->balance_remaining) : 0.00;

            // Coop Savings
            $cpSavSetup = $coopSavingsSetups[$sid] ?? null;
            $coopSavings = $cpSavSetup ? (float)$cpSavSetup->monthly_saving : 0.00;

            // Coop Asset Finance
            $cpAssetSetup = $coopAssetSetups[$sid] ?? null;
            $coopAsset = $cpAssetSetup ? min((float)$cpAssetSetup->monthly_deduction, (float)$cpAssetSetup->balance_remaining) : 0.00;

            // Surcharge
            $surSetup = $surchargeSetups[$sid] ?? null;
            $surcharge = $surSetup ? min((float)$surSetup->monthly_deduction, (float)$surSetup->balance_remaining) : 0.00;

            // Absence Penalty
            $absSetup = $absencePenaltySetups[$sid] ?? null;
            $absBal = 0.00;
            if ($absSetup) {
                $absBal = (float)$absSetup->balance_remaining > 0
                    ? (float)$absSetup->balance_remaining
                    : ((float)$absSetup->total_amount > 0 ? (float)$absSetup->total_amount : (float)$absSetup->monthly_deduction);
            }
            $absencePenalty = $absSetup ? min((float)$absSetup->monthly_deduction, $absBal) : 0.00;

            // Regular Loan
            $lSetup = $loanSetups[$sid] ?? null;
            $empLoan = $empLoans[$sid] ?? null;
            $regularLoan = 0.00;
            if ($lSetup) {
                $regularLoan = min((float)$lSetup->monthly_deduction, (float)$lSetup->balance_remaining);
            } elseif ($empLoan) {
                $regularLoan = min((float)$empLoan->monthly_deduction, (float)$empLoan->balance);
            }

            // Other Deductions
            $othSetup = $otherDeductSetups[$sid] ?? null;
            $othBal = 0.00;
            if ($othSetup) {
                $othBal = (float)$othSetup->balance_remaining > 0
                    ? (float)$othSetup->balance_remaining
                    : ((float)$othSetup->total_amount > 0 ? (float)$othSetup->total_amount : (float)$othSetup->monthly_deduction);
            }
            $otherDeduct = $othSetup ? min((float)$othSetup->monthly_deduction, $othBal) : 0.00;

            // Leave of Absence
            $loaDays = $loaDaysByStaff[$sid] ?? 0;
            $leaveOfAbsence = round(($grossPay / 30.0) * $loaDays, 2);

            $totalDeductions = round(
                $payeTax + $pension + $retention + $iou + $medLoan + $coopLoan +
                $coopSavings + $coopAsset + $surcharge + $absencePenalty +
                $regularLoan + $otherDeduct + $leaveOfAbsence,
                2
            );

            $netPay = max(0.00, round($grossPay - $totalDeductions, 2));

            // Balances for spreadsheet
            $revolvingLoanBal = $lSetup ? (float)$lSetup->balance_remaining : ($empLoan ? (float)$empLoan->balance : 0.00);
            $coopContr = $cpSavSetup ? (float)$cpSavSetup->saving_balance : 0.00;
            $coopLoanBal = $cpLoanSetup ? (float)$cpLoanSetup->balance_remaining : 0.00;
            $coopAssetDeduct = $coopAsset;
            $coopAssetFin = $cpAssetSetup ? (float)$cpAssetSetup->balance_remaining : 0.00;
            $medDebt = $medSetup ? (float)$medSetup->balance_remaining : 0.00;

            $calculatedRows[] = [
                'id' => $staff->id,
                'name' => trim($staff->name),
                'department' => $staff->department ?? 'General',
                'designation' => $staff->designation ?? 'Staff',
                'basic_salary' => $basic,
                'housing_allowance' => $housing,
                'transport_allowance' => $transport,
                'medical_allowance' => $medical,
                'utility_allowance' => $utility,
                'meal_allowance' => $meal,
                'variable_allowances' => round($varAllowances, 2),
                'gross_pay' => round($grossPay, 2),
                'declare_salary' => round($declareSalary, 2),
                'paye_tax' => $payeTax,
                'pension' => $pension,
                'retention' => $retention,
                'iou' => $iou,
                'medical_loan' => $medLoan,
                'coop_loan' => $coopLoan,
                'coop_savings' => $coopSavings,
                'coop_asset_finance' => $coopAsset,
                'surcharges' => $surcharge,
                'absence_penalty' => $absencePenalty,
                'leave_of_absence' => $leaveOfAbsence,
                'regular_loan' => $regularLoan,
                'other_deductions' => $otherDeduct,
                'total_deductions' => $totalDeductions,
                'net_pay' => $netPay,
                'bank_name' => $staff->bank_name ?? '—',
                'account_number' => $staff->account_number ?? '—',
                'payer_id' => $staff->payer_id ?? '—',
                'paid_days' => max(0, 30 - $loaDays),
                'days_absent' => $loaDays,
                'revolving_loan_bal' => $revolvingLoanBal,
                'coop_contr' => $coopContr,
                'coop_loan_bal' => $coopLoanBal,
                'coop_asset' => $coopAssetDeduct,
                'coop_asset_fin' => $coopAssetFin,
                'medical_debt' => $medDebt,
            ];
        }

        $calcCollection = collect($calculatedRows);

        $summary = [
            'total_staff' => $calcCollection->count(),
            'total_gross' => round($calcCollection->sum('gross_pay'), 2),
            'total_deductions' => round($calcCollection->sum('total_deductions'), 2),
            'total_net_pay' => round($calcCollection->sum('net_pay'), 2),
            'status' => 'Pre-Compute Projection'
        ];

        return [
            'records' => $calculatedRows,
            'summary' => $summary,
            'period' => [
                'month' => $month,
                'year' => $year,
                'month_name' => $monthName,
                'period_str' => "{$monthName} {$year}",
            ],
            'departments' => $departments,
            'is_computed' => false
        ];
    }
}
