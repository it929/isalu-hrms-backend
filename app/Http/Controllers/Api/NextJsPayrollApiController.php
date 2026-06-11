<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NextJsPayrollApiController extends Controller
{
    use ResolveUserContextTrait;

    // ─── CV IDs mapped to template columns ────────────────────────────────────
    // (based on tblcvSetup rows confirmed from database dump)
    const CVID_MEDICAL_ALLOWANCE  = 19;  // Earning  → MEDICAL column
    const CVID_OVERPAYMENT        = 27;  // Deduction → IOU column
    const CVID_LAWYERS_LOAN       = 3;   // Deduction → LOAN (part)
    const CVID_VISA_LOAN          = 7;   // Deduction → LOAN (part)
    const CVID_COOP_CHRISTIAN     = 2;   // Deduction → COOP. SAVING (part)
    const CVID_COOP_MUSLIM_WOMEN  = 5;   // Deduction → COOP. SAVING (part)
    const CVID_COOP_SCN           = 6;   // Deduction → COOP. SAVING (part)
    const CVID_MUSLIM_WOMEN_LOAN  = 24;  // Deduction → COOP. LOAN RPYT

    /**
     * Resolve user context from X-User-Id header.
     */
    

    /**
     * GET /api/nextjs/payroll/metadata
     * Returns filter options: divisions, banks, years, months.
     */
    public function getMetadata(Request $request)
    {
        try {
            $divisions = DB::table('tbldivision')
                ->select('divisionID as id', 'division as name')
                ->orderBy('division', 'asc')
                ->get();

            $banks = DB::table('tblbanklist')
                ->select('bankID as id', 'bank as name')
                ->orderBy('bank', 'asc')
                ->get();

            $currentYear = (int) date('Y');
            $years = collect(range($currentYear - 5, $currentYear))->map(fn($y) => ['id' => $y, 'name' => (string) $y]);

            $months = collect([
                'JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE',
                'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER',
            ])->map(fn($m) => ['id' => $m, 'name' => ucfirst(strtolower($m))]);

            return response()->json([
                'status'    => 'success',
                'divisions' => $divisions,
                'banks'     => $banks,
                'years'     => $years->values(),
                'months'    => $months->values(),
            ]);
        } catch (\Throwable $th) {
            Log::error('PayrollAPI getMetadata: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll
     * Returns paginated payroll records with all 34 computed columns.
     */
    public function getPayrollList(Request $request)
    {
        $userCtx = $this->getUserContext($request);
        if (!$userCtx) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        try {
            $month      = strtoupper(trim($request->input('month', '')));
            $year       = trim($request->input('year', ''));
            $divisionID = trim($request->input('divisionID', ''));
            $bankID     = trim($request->input('bankID', ''));
            $perPage    = (int) $request->input('perPage', 50);
            $page       = (int) $request->input('page', 1);

            if (!$month || !$year) {
                return response()->json(['status' => 'error', 'message' => 'Month and Year are required.'], 422);
            }

            [$records, $total, $summary] = $this->fetchPayrollData(
                $month, $year, $divisionID, $bankID, $perPage, $page
            );

            return response()->json([
                'status'   => 'success',
                'data'     => $records,
                'summary'  => $summary,
                'total'    => $total,
                'perPage'  => $perPage,
                'page'     => $page,
                'lastPage' => (int) ceil($total / $perPage),
            ]);
        } catch (\Throwable $th) {
            Log::error('PayrollAPI getPayrollList: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/export
     * Streams the exact 34-column CSV file.
     */
    public function exportPayroll(Request $request)
    {
        $userCtx = $this->getUserContext($request);
        if (!$userCtx) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        try {
            $month      = strtoupper(trim($request->input('month', '')));
            $year       = trim($request->input('year', ''));
            $divisionID = trim($request->input('divisionID', ''));
            $bankID     = trim($request->input('bankID', ''));

            if (!$month || !$year) {
                return response()->json(['status' => 'error', 'message' => 'Month and Year are required.'], 422);
            }

            // Fetch ALL records (no pagination)
            [$records] = $this->fetchPayrollData($month, $year, $divisionID, $bankID, PHP_INT_MAX, 1);

            $headers = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"Payroll_{$month}_{$year}.csv\"",
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Expires'             => '0',
            ];

            $columns = [
                'IDNO', 'NAME', 'DEPERTMENT', 'BASIC', 'HOUSING', 'TRANSPORT',
                'MEDICAL', 'UTILITY', 'MEAL', 'TOTAL INCOME', 'DECLARED INCOME',
                'PAID DAYS', 'P.TAX', 'IOU', 'RETENTION', 'LOAN', 'SURGHARGES',
                'PENSION', 'MEDICAL LOAN', 'COOP. SAVING', 'COOP. LOAN RPYT',
                'ABSENCE PENALTY', 'OTHER DEDUCTION', 'TOTAL DEDUCTION', 'NETPAY',
                'REVOLVING LOAN BAL', 'COP.CONTR', 'COP. LONE BAL',
                'COOP.ASSET.', 'COP. ASSET FIN', 'MEDICAL DEBT', 'ACC. NO', 'BANK', 'CODE', 'PAYER ID',
            ];

            $callback = function () use ($records, $columns) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $columns);
                foreach ($records as $row) {
                    fputcsv($handle, [
                        $row['IDNO'],
                        $row['NAME'],
                        $row['DEPERTMENT'],
                        $row['BASIC'],
                        $row['HOUSING'],
                        $row['TRANSPORT'],
                        $row['MEDICAL'],
                        $row['UTILITY'],
                        $row['MEAL'],
                        $row['TOTAL INCOME'],
                        $row['DECLARED INCOME'],
                        $row['PAID DAYS'],
                        $row['P.TAX'],
                        $row['IOU'],
                        $row['RETENTION'],
                        $row['LOAN'],
                        $row['SURGHARGES'],
                        $row['PENSION'],
                        $row['MEDICAL LOAN'],
                        $row['COOP. SAVING'],
                        $row['COOP. LOAN RPYT'],
                        $row['ABSENCE PENALTY'],
                        $row['OTHER DEDUCTION'],
                        $row['TOTAL DEDUCTION'],
                        $row['NETPAY'],
                        $row['REVOLVING LOAN BAL'],
                        $row['COP.CONTR'],
                        $row['COP. LONE BAL'],
                        $row['COOP.ASSET.'] ?? '0.00',
                        $row['COP. ASSET FIN'],
                        $row['MEDICAL DEBT'],
                        $row['ACC. NO'],
                        $row['BANK'],
                        $row['CODE'],
                        $row['PAYER ID'],
                    ]);
                }
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Throwable $th) {
            Log::error('PayrollAPI exportPayroll: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    // ─── Core Data Fetcher ────────────────────────────────────────────────────

    /**
     * Fetches, maps, and paginates payroll records.
     * Returns [$pagedRows, $totalCount, $summary].
     */
    private function fetchPayrollData(
        string $month,
        string $year,
        string $divisionID,
        string $bankID,
        int $perPage,
        int $page
    ): array {
        $loanBalances = DB::table('employee_loans')
            ->whereRaw("LOWER(status) = 'approved'")
            ->pluck('balance', 'staffId')
            ->toArray();

        $loanSetupBalances = DB::table('loan_deduction_setups')
            ->where('is_active', 1)
            ->pluck('balance_remaining', 'staffId')
            ->toArray();

        $loanSetupDeductions = DB::table('loan_deduction_setups')
            ->where('is_active', 1)
            ->pluck('monthly_deduction', 'staffId')
            ->toArray();

        $revolvingLoanBalances = [];
        foreach ($loanBalances as $sid => $bal) {
            $revolvingLoanBalances[$sid] = $bal;
        }
        foreach ($loanSetupBalances as $sid => $bal) {
            $revolvingLoanBalances[$sid] = $bal;
        }

        $coopLoanBalances = DB::table('coop_loan_deduction_setups')
            ->where('is_active', 1)
            ->pluck('balance_remaining', 'staffId')
            ->toArray();

        $medicalLoanBalances = DB::table('medical_loan_deduction_setups')
            ->where('is_active', 1)
            ->pluck('balance_remaining', 'staffId')
            ->toArray();

        $coopAssetFinanceBalances = DB::table('coop_asset_finance_deduction_setups')
            ->where('is_active', 1)
            ->pluck('balance_remaining', 'staffId')
            ->toArray();

        $legacyCoopLoanBalances = DB::table('staffEarningAndDeduction as sc')
            ->leftJoin('tblcvSetup as cv', 'cv.ID', '=', 'sc.cv_setup_id')
            ->where(function($q) {
                $q->where('cv.system_code', 'coop_loan_rpyt')
                  ->orWhere(function($sq) {
                      $sq->where(function($sub) {
                             $sub->where('sc.description', 'like', '%coop%')
                                 ->orWhere('sc.description', 'like', '%cooperative%');
                         })
                         ->where(function($sub) {
                             $sub->where('sc.description', 'like', '%loan%')
                                 ->orWhere('sc.description', 'like', '%rpyt%')
                                 ->orWhere('sc.description', 'like', '%rpty%')
                                 ->orWhere('sc.description', 'like', '%repay%');
                         });
                  })
                  ->orWhere('sc.cv_setup_id', 24);
            })
            ->groupBy('sc.staffId')
            ->select('sc.staffId', DB::raw('SUM(CASE WHEN sc.target_amount IS NOT NULL AND sc.target_amount > sc.total_deducted THEN sc.target_amount - sc.total_deducted ELSE 0 END) as balance'))
            ->pluck('balance', 'staffId')
            ->toArray();

        foreach ($legacyCoopLoanBalances as $sid => $bal) {
            if (!isset($coopLoanBalances[$sid])) {
                $coopLoanBalances[$sid] = $bal;
            }
        }

        $coopSavingsBalances = DB::table('coop_savings_setups')
            ->where('is_active', 1)
            ->pluck('saving_balance', 'staffId')
            ->toArray();

        $yearInt = (int)$year;
        $monthInt = 0;
        if (is_numeric($month)) {
            $monthInt = (int)$month;
        } else {
            $monthNames = [
                'JANUARY' => 1, 'FEBRUARY' => 2, 'MARCH' => 3, 'APRIL' => 4,
                'MAY' => 5, 'JUNE' => 6, 'JULY' => 7, 'AUGUST' => 8,
                'SEPTEMBER' => 9, 'OCTOBER' => 10, 'NOVEMBER' => 11, 'DECEMBER' => 12
            ];
            $monthInt = $monthNames[strtoupper(trim($month))] ?? 0;
        }

        $run = DB::table('payroll_runs')
            ->where('month', $monthInt)
            ->where('year', $yearInt)
            ->first();

        if ($run) {
            $query = DB::table('payroll_conpt as pc')
                ->join('tblper as p', 'p.ID', '=', 'pc.staffID')
                ->leftJoin('tbldivision as d', 'd.divisionID', '=', 'p.divisionID')
                ->leftJoin('tbldepartment as dept', 'dept.id', '=', 'p.departmentID')
                ->leftJoin('tblbanklist as bl', 'bl.bankID', '=', 'p.bankID')
                ->where('pc.payroll_run_id', $run->id);

            if ($divisionID !== '') {
                $query->where('p.divisionID', $divisionID);
            }
            if ($bankID !== '') {
                $query->where('p.bankID', $bankID);
            }

            $total   = $query->count();
            $allRows = $query->select(
                'pc.staffID',
                'p.fileNo',
                DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as name"),
                'dept.department',
                'pc.basic',
                'pc.housing',
                'pc.transport',
                'pc.medical',
                'pc.utility',
                'pc.meal',
                'pc.gross_pay',
                'pc.paye_tax',
                'pc.loan_deduction',
                'pc.pension',
                'pc.coop_savings',
                'pc.other_deductions',
                'pc.retention',
                'pc.surcharges',
                'pc.medical_loan',
                'pc.coop_loan_rpyt',
                DB::raw('COALESCE(pc.coop_asset_finance, 0) as coop_asset_finance'),
                'pc.total_deductions',
                'pc.net_pay',
                'pc.total_income',
                'pc.declare_income',
                'pc.iou',
                'pc.absence_penalty',
                'pc.leave_of_absence_deduction',
                'p.AccNo',
                'bl.bank as bankName',
                'pc.paid_days'
            )->get();

            $mapped = $allRows->map(function ($row) use ($revolvingLoanBalances, $coopLoanBalances, $coopSavingsBalances, $medicalLoanBalances, $coopAssetFinanceBalances, $loanSetupDeductions) {
                return [
                    'IDNO'               => $row->fileNo ?? '',
                    'NAME'               => $row->name   ?? '',
                    'DEPERTMENT'         => $row->department ?? '',
                    'BASIC'              => number_format((float)$row->basic,    2, '.', ''),
                    'HOUSING'            => number_format((float)$row->housing,  2, '.', ''),
                    'TRANSPORT'          => number_format((float)$row->transport,2, '.', ''),
                    'MEDICAL'            => number_format((float)$row->medical,  2, '.', ''),
                    'UTILITY'            => number_format((float)$row->utility,  2, '.', ''),
                    'MEAL'               => number_format((float)$row->meal,     2, '.', ''),
                    'TOTAL INCOME'       => number_format((float)$row->total_income,2, '.', ''),
                    'DECLARED INCOME'    => number_format((float)$row->declare_income,2, '.', ''),
                    'PAID DAYS'          => $row->paid_days,
                    'P.TAX'              => number_format((float)$row->paye_tax, 2, '.', ''),
                    'IOU'                => number_format((float)$row->iou, 2, '.', ''),
                    'RETENTION'          => number_format((float)$row->retention, 2, '.', ''),
                    'LOAN'               => number_format((float)($loanSetupDeductions[$row->staffID] ?? $row->loan_deduction), 2, '.', ''),
                    'SURGHARGES'         => number_format((float)$row->surcharges, 2, '.', ''),
                    'PENSION'            => number_format((float)$row->pension,  2, '.', ''),
                    'MEDICAL LOAN'       => number_format((float)$row->medical_loan, 2, '.', ''),
                    'COOP. SAVING'       => number_format((float)$row->coop_savings, 2, '.', ''),
                    'COOP. LOAN RPYT'    => number_format((float)$row->coop_loan_rpyt, 2, '.', ''),
                    'ABSENCE PENALTY'    => number_format((float)$row->absence_penalty, 2, '.', ''),
                    'LEAVE OF ABSENCE DEDUCTION' => number_format((float)$row->leave_of_absence_deduction, 2, '.', ''),
                    'OTHER DEDUCTION'    => number_format((float)$row->other_deductions, 2, '.', ''),
                    'TOTAL DEDUCTION'    => number_format((float)$row->total_deductions, 2, '.', ''),
                    'NETPAY'             => number_format((float)$row->net_pay,   2, '.', ''),
                    'REVOLVING LOAN BAL' => number_format((float)($revolvingLoanBalances[$row->staffID] ?? 0.00), 2, '.', ''),
                    'COP.CONTR'          => number_format((float)($coopSavingsBalances[$row->staffID] ?? 0.00), 2, '.', ''),
                    'COP. LONE BAL'      => number_format((float)($coopLoanBalances[$row->staffID] ?? 0.00), 2, '.', ''),
                    'COOP.ASSET.'        => number_format((float)($row->coop_asset_finance ?? 0.00), 2, '.', ''),
                    'COP. ASSET FIN'     => number_format((float)($coopAssetFinanceBalances[$row->staffID] ?? 0.00), 2, '.', ''),
                    'MEDICAL DEBT'       => number_format((float)($medicalLoanBalances[$row->staffID] ?? 0.00), 2, '.', ''),
                    'LEAVE OF ABSENCE DEDUCTION' => number_format((float)($row->leave_of_absence_deduction ?? 0.00), 2, '.', ''),
                    'ACC. NO'            => $row->AccNo   ?? '',
                    'BANK'               => $row->bankName ?? '',
                    'CODE'               => '',
                    'PAYER ID'           => '',
                ];
            });

            $summary = [
                'totalStaff'       => $total,
                'totalGrossIncome' => number_format($allRows->sum(fn($r) => (float)$r->gross_pay), 2, '.', ''),
                'totalDeductions'  => number_format($allRows->sum(fn($r) => (float)$r->total_deductions), 2, '.', ''),
                'totalNetPay'      => number_format($allRows->sum(fn($r) => (float)$r->net_pay), 2, '.', ''),
            ];

            $offset    = ($page - 1) * $perPage;
            $paged     = $mapped->slice($offset, $perPage)->values()->toArray();

            return [$paged, $total, $summary];
        }

        // 1. Base query for payment consolidated records
        $query = DB::table('tblpayment_consolidated as pc')
            ->leftJoin('tblper as p', 'p.ID', '=', 'pc.staffid')
            ->leftJoin('tbldepartment as dept', 'dept.id', '=', 'p.departmentID')
            ->leftJoin('tblbanklist as bl', 'bl.bankID', '=', 'pc.bank')
            ->where('pc.month', $month)
            ->where('pc.year', $year)
            ->where('pc.rank', '!=', 2)
            ->select(
                'pc.fileNo',
                'pc.name',
                'dept.department',
                'pc.Bs',
                'pc.HA',
                'pc.TR',
                'pc.UTI',
                'pc.ML',
                'pc.TEarn',
                'pc.TAX',
                'pc.PEN',
                'pc.TD',
                'pc.NetPay',
                'pc.AccNo',
                'pc.bank as bankID',
                'bl.bank as bankName',
                'pc.staffid',
                'pc.divisionID',
                DB::raw("COALESCE(pc.OD, 0) as OD")
            );

        if ($divisionID !== '') {
            $query->where('pc.divisionID', $divisionID);
        }
        if ($bankID !== '') {
            $query->where('pc.bank', $bankID);
        }

        $total   = $query->count();
        $allRows = $query->get(); // we need all rows to look up dynamic elements

        // 2. Collect all staffIDs for dynamic lookup
        $staffIds = $allRows->pluck('staffid')->filter()->unique()->values()->toArray();

        // 3. Fetch ALL dynamic earning/deduction records for these staff in one query
        $dynamicRows = collect();
        if (!empty($staffIds)) {
            $dynamicRows = DB::table('tblotherEarningDeduction as oed')
                ->join('tblcvSetup as cv', 'cv.ID', '=', 'oed.CVID')
                ->where('oed.year', $year)
                ->where('oed.month', $month)
                ->whereIn('oed.staffid', $staffIds)
                ->where('oed.amount', '>', 0.0099)
                ->select('oed.staffid', 'oed.CVID', 'oed.amount', 'cv.particularID')
                ->get();
        }

        // Build a lookup: staffid -> [ CVID -> total_amount ]
        $dynamicByStaff = [];
        foreach ($dynamicRows as $dr) {
            $sid  = $dr->staffid;
            $cvid = $dr->CVID;
            if (!isset($dynamicByStaff[$sid][$cvid])) {
                $dynamicByStaff[$sid][$cvid] = 0;
            }
            $dynamicByStaff[$sid][$cvid] += (float) $dr->amount;
        }

        // 4. Map each row to the 34-column template
        $mapped = $allRows->map(function ($row) use ($dynamicByStaff, $revolvingLoanBalances, $coopLoanBalances, $coopSavingsBalances, $medicalLoanBalances, $coopAssetFinanceBalances, $loanSetupDeductions) {
            $sid = $row->staffid;
            $cvs = $dynamicByStaff[$sid] ?? [];

            $basic     = (float)($row->Bs    ?? 0);
            $housing   = (float)($row->HA    ?? 0);
            $transport = (float)($row->TR    ?? 0);
            $utility   = (float)($row->UTI   ?? 0);
            $meal      = (float)($row->ML    ?? 0);
            $totalEarn = (float)($row->TEarn ?? 0);
            $pTax      = (float)($row->TAX   ?? 0);
            $pension   = (float)($row->PEN   ?? 0);
            $totalDedn = (float)($row->TD    ?? 0);
            $netPay    = (float)($row->NetPay ?? 0);

            // Dynamic columns from CV IDs
            $medical    = $cvs[self::CVID_MEDICAL_ALLOWANCE] ?? 0.00;
            $iou        = $cvs[self::CVID_OVERPAYMENT]       ?? 0.00;
            $loan       = isset($loanSetupDeductions[$sid]) ? (float)$loanSetupDeductions[$sid] : (($cvs[self::CVID_LAWYERS_LOAN] ?? 0) + ($cvs[self::CVID_VISA_LOAN] ?? 0));
            $coopSaving = ($cvs[self::CVID_COOP_CHRISTIAN] ?? 0)
                        + ($cvs[self::CVID_COOP_MUSLIM_WOMEN] ?? 0)
                        + ($cvs[self::CVID_COOP_SCN] ?? 0);
            $coopLoan   = $cvs[self::CVID_MUSLIM_WOMEN_LOAN] ?? 0.00;

            // Other Deduction = Total Deductions minus all explicitly mapped deductions
            $explicitDeductions = $pTax + $pension + $iou + $loan + $coopSaving + $coopLoan;
            $otherDeduction     = max(0, $totalDedn - $explicitDeductions);

            return [
                'IDNO'               => $row->fileNo ?? '',
                'NAME'               => $row->name   ?? '',
                'DEPERTMENT'         => $row->department ?? '',
                'BASIC'              => number_format($basic,    2, '.', ''),
                'HOUSING'            => number_format($housing,  2, '.', ''),
                'TRANSPORT'          => number_format($transport,2, '.', ''),
                'MEDICAL'            => number_format($medical,  2, '.', ''),
                'UTILITY'            => number_format($utility,  2, '.', ''),
                'MEAL'               => number_format($meal,     2, '.', ''),
                'TOTAL INCOME'       => number_format($totalEarn,2, '.', ''),
                'DECLARED INCOME'    => number_format($totalEarn,2, '.', ''),
                'PAID DAYS'          => 30,
                'P.TAX'              => number_format($pTax,     2, '.', ''),
                'IOU'                => number_format($iou,      2, '.', ''),
                'RETENTION'          => '0.00',
                'LOAN'               => number_format($loan,     2, '.', ''),
                'SURGHARGES'         => '0.00',
                'PENSION'            => number_format($pension,  2, '.', ''),
                'MEDICAL LOAN'       => '0.00',
                'COOP. SAVING'       => number_format($coopSaving,2, '.', ''),
                'COOP. LOAN RPYT'    => number_format($coopLoan,  2, '.', ''),
                'ABSENCE PENALTY'    => '0.00',
                'OTHER DEDUCTION'    => number_format($otherDeduction,2, '.', ''),
                'TOTAL DEDUCTION'    => number_format($totalDedn,2, '.', ''),
                'NETPAY'             => number_format($netPay,   2, '.', ''),
                'REVOLVING LOAN BAL' => number_format((float)($revolvingLoanBalances[$row->staffid] ?? 0.00), 2, '.', ''),
                'COP.CONTR'          => number_format((float)($coopSavingsBalances[$row->staffid] ?? 0.00), 2, '.', ''),
                'COP. LONE BAL'      => number_format((float)($coopLoanBalances[$row->staffid] ?? 0.00), 2, '.', ''),
                'COOP.ASSET.'        => '0.00',
                'COP. ASSET FIN'     => number_format((float)($coopAssetFinanceBalances[$row->staffid] ?? 0.00), 2, '.', ''),
                'MEDICAL DEBT'       => number_format((float)($medicalLoanBalances[$row->staffid] ?? 0.00), 2, '.', ''),
                'LEAVE OF ABSENCE DEDUCTION' => '0.00',
                'ACC. NO'            => $row->AccNo   ?? '',
                'BANK'               => $row->bankName ?? '',
                'CODE'               => '',
                'PAYER ID'           => '',
            ];
        });

        // 5. Compute summary totals from all rows
        $summary = [
            'totalStaff'       => $total,
            'totalGrossIncome' => number_format($allRows->sum(fn($r) => (float)($r->TEarn  ?? 0)), 2, '.', ''),
            'totalDeductions'  => number_format($allRows->sum(fn($r) => (float)($r->TD     ?? 0)), 2, '.', ''),
            'totalNetPay'      => number_format($allRows->sum(fn($r) => (float)($r->NetPay ?? 0)), 2, '.', ''),
        ];

        // 6. Paginate in PHP (all records were fetched for dynamic join; pagination returned to client)
        $offset    = ($page - 1) * $perPage;
        $paged     = $mapped->slice($offset, $perPage)->values()->toArray();

        return [$paged, $total, $summary];
    }

    /**
     * POST /api/nextjs/payroll/compute
     * Run salary computation for all active staff for the given month and year.
     */
    public function computeSalary(Request $request)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx || !$ctx['isSuperAdmin']) {
            return response()->json(['status' => 'error', 'message' => 'Super Admin privileges required.'], 403);
        }

        try {
            $monthInput = strtoupper(trim($request->input('month', '')));
            $yearInput  = trim($request->input('year', ''));

            if (!$monthInput || !$yearInput) {
                return response()->json(['status' => 'error', 'message' => 'Month and Year are required.'], 422);
            }

            $year = (int)$yearInput;
            $month = 0;

            if (is_numeric($monthInput)) {
                $month = (int)$monthInput;
            } else {
                $monthNames = [
                    'JANUARY' => 1, 'FEBRUARY' => 2, 'MARCH' => 3, 'APRIL' => 4,
                    'MAY' => 5, 'JUNE' => 6, 'JULY' => 7, 'AUGUST' => 8,
                    'SEPTEMBER' => 9, 'OCTOBER' => 10, 'NOVEMBER' => 11, 'DECEMBER' => 12
                ];
                $month = $monthNames[$monthInput] ?? 0;
            }

            if ($month < 1 || $month > 12) {
                return response()->json(['status' => 'error', 'message' => 'Invalid month specified.'], 422);
            }

            // Start transaction
            DB::beginTransaction();

            // Retrieve old conpt records for this month and year to revert total_deducted in staffEarningAndDeduction
            $oldDetails = DB::table('payroll_conpt')
                ->where('month', $month)
                ->where('year', $year)
                ->get();

             foreach ($oldDetails as $od) {
                // Revert coop_loan_deduction_setups balance_remaining
                if ($od->coop_loan_rpyt > 0) {
                    $currentMonthStr = sprintf("%04d-%02d", $year, $month);
                    $setup = DB::table('coop_loan_deduction_setups')
                        ->where('staffId', $od->staffID)
                        ->where('start_month', '<=', $currentMonthStr)
                        ->where('end_month', '>=', $currentMonthStr)
                        ->orderBy('is_active', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    if ($setup) {
                        DB::table('coop_loan_deduction_setups')
                            ->where('id', $setup->id)
                            ->update([
                                'balance_remaining' => DB::raw('balance_remaining + ' . (float)$od->coop_loan_rpyt),
                                'is_active' => 1
                            ]);
                    }
                }

                // Revert coop_savings_setups saving_balance
                if ($od->coop_savings > 0) {
                    $currentMonthStr = sprintf("%04d-%02d", $year, $month);
                    $setup = DB::table('coop_savings_setups')
                        ->where('staffId', $od->staffID)
                        ->where('start_month', '<=', $currentMonthStr)
                        ->orderBy('is_active', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    if ($setup) {
                        DB::table('coop_savings_setups')
                            ->where('id', $setup->id)
                            ->decrement('saving_balance', $od->coop_savings);
                    }
                }

                // Revert surcharge_deduction_setups balance_remaining
                if ($od->surcharges > 0) {
                    $currentMonthStr = sprintf("%04d-%02d", $year, $month);
                    $setup = DB::table('surcharge_deduction_setups')
                        ->where('staffId', $od->staffID)
                        ->where('start_month', '<=', $currentMonthStr)
                        ->where(function($q) use ($currentMonthStr) {
                            $q->whereNull('end_month')
                              ->orWhere('end_month', '=', '')
                              ->orWhere('end_month', '>=', $currentMonthStr);
                        })
                        ->orderBy('is_active', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    if ($setup) {
                        DB::table('surcharge_deduction_setups')
                            ->where('id', $setup->id)
                            ->update([
                                'balance_remaining' => DB::raw('balance_remaining + ' . (float)$od->surcharges),
                                'is_active' => 1
                            ]);
                    }
                }

                // Revert medical_loan_deduction_setups balance_remaining
                if ($od->medical_loan > 0) {
                    $currentMonthStr = sprintf("%04d-%02d", $year, $month);
                    $setup = DB::table('medical_loan_deduction_setups')
                        ->where('staffId', $od->staffID)
                        ->where('start_month', '<=', $currentMonthStr)
                        ->where('end_month', '>=', $currentMonthStr)
                        ->orderBy('is_active', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    if ($setup) {
                        DB::table('medical_loan_deduction_setups')
                            ->where('id', $setup->id)
                            ->update([
                                'balance_remaining' => DB::raw('balance_remaining + ' . (float)$od->medical_loan),
                                'is_active' => 1
                            ]);
                    }
                }

                // Revert absence_penalty_deduction_setups balance_remaining
                if ($od->absence_penalty > 0) {
                    $currentMonthStr = sprintf("%04d-%02d", $year, $month);
                    $setup = DB::table('absence_penalty_deduction_setups')
                        ->where('staffId', $od->staffID)
                        ->where('start_month', '<=', $currentMonthStr)
                        ->where(function($q) use ($currentMonthStr) {
                            $q->whereNull('end_month')
                              ->orWhere('end_month', '=', '')
                              ->orWhere('end_month', '>=', $currentMonthStr);
                        })
                        ->orderBy('is_active', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    if ($setup) {
                        DB::table('absence_penalty_deduction_setups')
                            ->where('id', $setup->id)
                            ->update([
                                'balance_remaining' => DB::raw('balance_remaining + ' . (float)$od->absence_penalty),
                                'is_active' => 1
                            ]);
                    }
                }

                // Revert other_deduction_setups balance_remaining
                if ($od->other_deductions > 0) {
                    $currentMonthStr = sprintf("%04d-%02d", $year, $month);
                    $setup = DB::table('other_deduction_setups')
                        ->where('staffId', $od->staffID)
                        ->where('start_month', '<=', $currentMonthStr)
                        ->where(function($q) use ($currentMonthStr) {
                            $q->whereNull('end_month')
                              ->orWhere('end_month', '=', '')
                              ->orWhere('end_month', '>=', $currentMonthStr);
                        })
                        ->orderBy('is_active', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    if ($setup) {
                        DB::table('other_deduction_setups')
                            ->where('id', $setup->id)
                            ->update([
                                'balance_remaining' => DB::raw('balance_remaining + ' . (float)$od->other_deductions),
                                'is_active' => 1
                            ]);
                    }
                }

                // Revert coop_asset_finance_deduction_setups balance_remaining
                $coopAssetFinanceCol = isset($od->coop_asset_finance) ? (float)$od->coop_asset_finance : 0.00;
                if ($coopAssetFinanceCol > 0) {
                    $currentMonthStr = sprintf("%04d-%02d", $year, $month);
                    $setup = DB::table('coop_asset_finance_deduction_setups')
                        ->where('staffId', $od->staffID)
                        ->where('start_month', '<=', $currentMonthStr)
                        ->where(function($q) use ($currentMonthStr) {
                            $q->whereNull('end_month')
                              ->orWhere('end_month', '=', '')
                              ->orWhere('end_month', '>=', $currentMonthStr);
                        })
                        ->orderBy('is_active', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    if ($setup) {
                        DB::table('coop_asset_finance_deduction_setups')
                            ->where('id', $setup->id)
                            ->update([
                                'balance_remaining' => DB::raw('balance_remaining + ' . $coopAssetFinanceCol),
                                'is_active' => 1
                            ]);
                    }
                }

                // Revert loan_deduction_setups balance_remaining OR fallback to employee_loans balance
                if ($od->loan_deduction > 0) {
                    $currentMonthStr = sprintf("%04d-%02d", $year, $month);
                    $setup = DB::table('loan_deduction_setups')
                        ->where('staffId', $od->staffID)
                        ->where('start_month', '<=', $currentMonthStr)
                        ->where('end_month', '>=', $currentMonthStr)
                        ->orderBy('is_active', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();
                    if ($setup) {
                        DB::table('loan_deduction_setups')
                            ->where('id', $setup->id)
                            ->update([
                                'balance_remaining' => DB::raw('balance_remaining + ' . (float)$od->loan_deduction),
                                'is_active' => 1
                            ]);
                    } else {
                        $empLoan = DB::table('employee_loans')
                            ->where('staffId', $od->staffID)
                            ->whereRaw("LOWER(status) = 'approved'")
                            ->orderBy('id', 'desc')
                            ->first();
                        if ($empLoan) {
                            DB::table('employee_loans')
                                ->where('id', $empLoan->id)
                                ->increment('balance', $od->loan_deduction);
                        }
                    }
                }

                // Revert retention count in first_salary_structure if old conpt record had a retention deduction
                if (isset($od->retention) && $od->retention > 0) {
                    DB::table('first_salary_structure')
                        ->where('staffId', $od->staffID)
                        ->where('num_rente_months', '>', 0)
                        ->decrement('num_rente_months');
                }
            }

            // Delete old payroll details for this month and year
            DB::table('payroll_conpt')
                ->where('month', $month)
                ->where('year', $year)
                ->delete();

            $existingRun = DB::table('payroll_runs')
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if ($existingRun) {
                $runId = $existingRun->id;

                DB::table('payroll_runs')->where('id', $runId)->update([
                    'status' => 'processed',
                    'processed_by' => $ctx['userId'] ?? null,
                    'processed_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                $runId = DB::table('payroll_runs')->insertGetId([
                    'month' => $month,
                    'year' => $year,
                    'status' => 'processed',
                    'processed_by' => $ctx['userId'] ?? null,
                    'processed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $employees = DB::table('tblper')
                ->where('rank', '!=', 2) // Exclude terminated/retired
                ->where('staff_status', 1) // Only active service status
                ->get();

            foreach ($employees as $emp) {
                // 1. Fetch salary structure
                $struct = DB::table('salary_structures')
                    ->where('staffId', $emp->ID)
                    ->first();

                $basic = 0.00;
                $housing = 0.00;
                $transport = 0.00;
                $medical = 0.00;
                $utility = 0.00;
                $meal = 0.00;
                $taxRate = 0.00;
                $pensionRate = 0.00;
                $declareSalary = 0.00;

                if ($struct) {
                    $basic = (float)$struct->basic_salary;
                    $housing = (float)$struct->housing_allowance;
                    $transport = (float)$struct->transport_allowance;
                    $medical = (float)$struct->medical_allowance;
                    $utility = (float)$struct->utility_allowance;
                    $meal = (float)$struct->meal_allowance;
                    $taxRate = (float)$struct->tax_rate;
                    $pensionRate = (float)$struct->pension_rate;
                    $declareSalary = (float)$struct->declare_salary;
                }

                // 2. Paid days (30 - Leave of Absence days)
                $loaDays = $this->getLoaDaysForMonth($emp->ID, $year, $month);
                $paidDays = max(0, 30 - $loaDays);

                // Check if there is an active coop loan setup
                $currentMonthStr = sprintf("%04d-%02d", $year, $month);
                $coopLoanSetup = DB::table('coop_loan_deduction_setups')
                    ->where('staffId', $emp->ID)
                    ->where('is_active', 1)
                    ->where('balance_remaining', '>', 0)
                    ->where('start_month', '<=', $currentMonthStr)
                    ->where('end_month', '>=', $currentMonthStr)
                    ->orderBy('id', 'desc')
                    ->first();

                // Check if there is an active coop savings setup
                $coopSavingsSetup = DB::table('coop_savings_setups')
                    ->where('staffId', $emp->ID)
                    ->where('is_active', 1)
                    ->where('start_month', '<=', $currentMonthStr)
                    ->orderBy('id', 'desc')
                    ->first();

                // Check if there is an active surcharge setup
                $surchargeSetup = DB::table('surcharge_deduction_setups')
                    ->where('staffId', $emp->ID)
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

                // Check if there is an active medical loan setup
                $medicalLoanSetup = DB::table('medical_loan_deduction_setups')
                    ->where('staffId', $emp->ID)
                    ->where('is_active', 1)
                    ->where('balance_remaining', '>', 0)
                    ->where('start_month', '<=', $currentMonthStr)
                    ->where('end_month', '>=', $currentMonthStr)
                    ->orderBy('id', 'desc')
                    ->first();

                // Check if there is an active absence penalty setup
                $absencePenaltySetup = DB::table('absence_penalty_deduction_setups')
                    ->where('staffId', $emp->ID)
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

                // Check if there is an active other deduction setup
                $otherDeductionSetup = DB::table('other_deduction_setups')
                    ->where('staffId', $emp->ID)
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

                // Check if there is an active coop asset finance deduction setup
                $coopAssetFinanceSetup = DB::table('coop_asset_finance_deduction_setups')
                    ->where('staffId', $emp->ID)
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

                // Check if there is an active loan setup
                $loanSetup = DB::table('loan_deduction_setups')
                    ->where('staffId', $emp->ID)
                    ->where('is_active', 1)
                    ->where('balance_remaining', '>', 0)
                    ->where('start_month', '<=', $currentMonthStr)
                    ->where('end_month', '>=', $currentMonthStr)
                    ->orderBy('id', 'desc')
                    ->first();

                // Check if there is an active approved employee loan (fallback)
                $employeeLoanSetup = null;
                if (!$loanSetup) {
                    $employeeLoanSetup = DB::table('employee_loans')
                        ->where('staffId', $emp->ID)
                        ->whereRaw("LOWER(status) = 'approved'")
                        ->where('balance', '>', 0)
                        ->orderBy('id', 'desc')
                        ->first();
                }

                $loanDeduction = 0.00;
                $coopSavings = 0.00;
                $otherDeductions = 0.00;
                $retention = 0.00;
                $surcharges = 0.00;
                $medicalLoan = 0.00;
                $coopLoanRpyt = 0.00;
                $absencePenalty = 0.00;
                $coopAssetFinance = 0.00;
                $leaveOfAbsenceDeduction = 0.00;
                $totalEarningVars = 0.00;
                $appliedAmountsMap = [];

                // Process Coop Loan Repayment Setup
                if ($coopLoanSetup) {
                    $coopLoanRpyt = min((float)$coopLoanSetup->monthly_deduction, (float)$coopLoanSetup->balance_remaining);
                    
                    // Update remaining balance on setups table
                    $newBalance = max(0.00, (float)$coopLoanSetup->balance_remaining - $coopLoanRpyt);
                    $updateData = ['balance_remaining' => $newBalance];
                    if ($newBalance <= 0) {
                        $updateData['is_active'] = 0;
                    }
                    DB::table('coop_loan_deduction_setups')
                        ->where('id', $coopLoanSetup->id)
                        ->update($updateData);
                }

                // Process Coop Savings Setup
                if ($coopSavingsSetup) {
                    $coopSavings = (float)$coopSavingsSetup->monthly_saving;
                    
                    // Increment saving_balance on setups table
                    DB::table('coop_savings_setups')
                        ->where('id', $coopSavingsSetup->id)
                        ->increment('saving_balance', $coopSavings);
                }

                // Process Surcharge Setup
                if ($surchargeSetup) {
                    $surcharges = min((float)$surchargeSetup->monthly_deduction, (float)$surchargeSetup->balance_remaining);
                    
                    // Update remaining balance on setups table
                    $newBalance = max(0.00, (float)$surchargeSetup->balance_remaining - $surcharges);
                    $updateData = ['balance_remaining' => $newBalance];
                    if ($newBalance <= 0) {
                        $updateData['is_active'] = 0;
                    }
                    DB::table('surcharge_deduction_setups')
                        ->where('id', $surchargeSetup->id)
                        ->update($updateData);
                }

                // Process Medical Loan Setup
                if ($medicalLoanSetup) {
                    $medicalLoan = min((float)$medicalLoanSetup->monthly_deduction, (float)$medicalLoanSetup->balance_remaining);
                    
                    // Update remaining balance on setups table
                    $newBalance = max(0.00, (float)$medicalLoanSetup->balance_remaining - $medicalLoan);
                    $updateData = ['balance_remaining' => $newBalance];
                    if ($newBalance <= 0) {
                        $updateData['is_active'] = 0;
                    }
                    DB::table('medical_loan_deduction_setups')
                        ->where('id', $medicalLoanSetup->id)
                        ->update($updateData);
                }

                // Process Absence Penalty Setup
                if ($absencePenaltySetup) {
                    $absencePenalty = min((float)$absencePenaltySetup->monthly_deduction, (float)$absencePenaltySetup->balance_remaining);
                    
                    // Update remaining balance on setups table
                    $newBalance = max(0.00, (float)$absencePenaltySetup->balance_remaining - $absencePenalty);
                    $updateData = ['balance_remaining' => $newBalance];
                    if ($newBalance <= 0) {
                        $updateData['is_active'] = 0;
                    }
                    DB::table('absence_penalty_deduction_setups')
                        ->where('id', $absencePenaltySetup->id)
                        ->update($updateData);
                }

                // Process Other Deduction Setup
                if ($otherDeductionSetup) {
                    $otherDeductions = min((float)$otherDeductionSetup->monthly_deduction, (float)$otherDeductionSetup->balance_remaining);
                    
                    // Update remaining balance on setups table
                    $newBalance = max(0.00, (float)$otherDeductionSetup->balance_remaining - $otherDeductions);
                    $updateData = ['balance_remaining' => $newBalance];
                    if ($newBalance <= 0) {
                        $updateData['is_active'] = 0;
                    }
                    DB::table('other_deduction_setups')
                        ->where('id', $otherDeductionSetup->id)
                        ->update($updateData);
                }

                // Process Coop Asset Finance Deduction Setup
                if ($coopAssetFinanceSetup) {
                    $coopAssetFinance = min((float)$coopAssetFinanceSetup->monthly_deduction, (float)$coopAssetFinanceSetup->balance_remaining);

                    // Update remaining balance on setups table
                    $newBalance = max(0.00, (float)$coopAssetFinanceSetup->balance_remaining - $coopAssetFinance);
                    $updateData = ['balance_remaining' => $newBalance];
                    if ($newBalance <= 0) {
                        $updateData['is_active'] = 0;
                    }
                    DB::table('coop_asset_finance_deduction_setups')
                        ->where('id', $coopAssetFinanceSetup->id)
                        ->update($updateData);
                }

                // Process Employee Loan (Regular Employee Loans - new setup or fallback)
                if ($loanSetup) {
                    $loanDeduction = min((float)$loanSetup->monthly_deduction, (float)$loanSetup->balance_remaining);
                    
                    // Update remaining balance on loan_deduction_setups table
                    $newBalance = max(0.00, (float)$loanSetup->balance_remaining - $loanDeduction);
                    $updateData = ['balance_remaining' => $newBalance];
                    if ($newBalance <= 0) {
                        $updateData['is_active'] = 0;
                    }
                    DB::table('loan_deduction_setups')
                        ->where('id', $loanSetup->id)
                        ->update($updateData);
                } elseif ($employeeLoanSetup) {
                    $loanDeduction = min((float)$employeeLoanSetup->monthly_deduction, (float)$employeeLoanSetup->balance);
                    
                    // Update remaining balance on employee_loans table
                    DB::table('employee_loans')
                        ->where('id', $employeeLoanSetup->id)
                        ->decrement('balance', $loanDeduction);
                }

                // 4. Perform salary calculation
                // Note: Basic salary and allowances are NOT prorated; they remain full
                $basicProrated = $basic;
                $housingProrated = $housing;
                $transportProrated = $transport;
                $medicalProrated = $medical;
                $utilityProrated = $utility;
                $mealProrated = $meal;

                $totalIncome = $basicProrated + $housingProrated + $transportProrated + $medicalProrated + $utilityProrated + $mealProrated;
                $grossPay = $totalIncome + $totalEarningVars;
                $declareIncome = $declareSalary;

                // PAYE and Pension calculated as percentage of computed incomes
                $payeTax = $declareIncome * ($taxRate / 100.0);
                $pension = 0.00;
                if ($struct && $struct->pen_act == 1) {
                    $pension = $grossPay * ($pensionRate / 100.0);
                }

                // Fetch monthly IOU taken from iou_records table
                $firstDay = \Carbon\Carbon::create($year, $month, 1)->startOfMonth()->format('Y-m-d');
                $lastDay = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');

                $iouSum = DB::table('iou_records')
                    ->where('staff_id', $emp->ID)
                    ->where('status', 1) // Approved
                    ->whereBetween('iou_date', [$firstDay, $lastDay])
                    ->sum('amount');

                // Compute leave of absence deduction: (grossPay / 30) * days_of_absent
                $leaveOfAbsenceDeduction = ($grossPay / 30.0) * $loaDays;

                // Compute retention using first_salary_structure if active and num_rente_months is less than 20
                $firstStruct = DB::table('first_salary_structure')->where('staffId', $emp->ID)->first();
                if ($firstStruct && $firstStruct->reten_act == 1) {
                    if ($firstStruct->num_rente_months < 20) {
                        $retentionBase = (float)$firstStruct->basic_salary +
                                         (float)$firstStruct->housing_allowance +
                                         (float)$firstStruct->transport_allowance +
                                         (float)$firstStruct->medical_allowance +
                                         (float)$firstStruct->utility_allowance +
                                         (float)$firstStruct->meal_allowance;
                        $retention = round(0.05 * $retentionBase, 2);

                        // Increment num_rente_months by 1
                        DB::table('first_salary_structure')
                            ->where('staffId', $emp->ID)
                            ->increment('num_rente_months');
                    }
                }


                $totalDeductions = $payeTax + $pension + $loanDeduction + $coopSavings + $otherDeductions + $iouSum + $absencePenalty + $retention + $surcharges + $medicalLoan + $coopLoanRpyt + $coopAssetFinance + $leaveOfAbsenceDeduction;
                $netPay = $grossPay - $totalDeductions;

                // 5. Submit detailed row into payroll_conpt table
                DB::table('payroll_conpt')->insert([
                    'payroll_run_id'   => $runId,
                    'staffID'          => $emp->ID,
                    'month'            => $month,
                    'year'             => $year,
                    'basic'            => round($basicProrated, 2),
                    'housing'          => round($housingProrated, 2),
                    'transport'        => round($transportProrated, 2),
                    'medical'          => round($medicalProrated, 2),
                    'utility'          => round($utilityProrated, 2),
                    'meal'             => round($mealProrated, 2),
                    'paid_days'        => $paidDays,
                    'gross_pay'        => round($grossPay, 2),
                    'paye_tax'         => round($payeTax, 2),
                    'loan_deduction'   => round($loanDeduction, 2),
                    'pension'          => round($pension, 2),
                    'coop_savings'     => round($coopSavings, 2),
                    'other_deductions' => round($otherDeductions, 2),
                    'retention'        => round($retention, 2),
                    'surcharges'          => round($surcharges, 2),
                    'medical_loan'        => round($medicalLoan, 2),
                    'coop_loan_rpyt'      => round($coopLoanRpyt, 2),
                    'coop_asset_finance'  => round($coopAssetFinance, 2),
                    'total_deductions'    => round($totalDeductions, 2),
                    'net_pay'             => round($netPay, 2),
                    'total_income'        => round($totalIncome, 2),
                    'declare_income'      => round($declareIncome, 2),
                    'iou'                 => round($iouSum, 2),
                    'absence_penalty'     => round($absencePenalty, 2),
                    'leave_of_absence_deduction' => round($leaveOfAbsenceDeduction, 2),
                    'applied_amounts'     => !empty($appliedAmountsMap) ? json_encode($appliedAmountsMap) : null,
                    'created_at'          => now()
                ]);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Salary payroll run computed and saved successfully for all active staff.',
                'payroll_run_id' => $runId
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('PayrollAPI computeSalary: ' . $th->getMessage() . "\n" . $th->getTraceAsString());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * Helper to sum leave of absence days for a staff member in a specific month and year.
     */
    private function getLoaDaysForMonth($staffId, $year, $month): int
    {
        $firstDay = \Carbon\Carbon::create($year, $month, 1)->startOfMonth()->format('Y-m-d');
        $lastDay = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->format('Y-m-d');

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

        $totalDays = 0;
        $startOfMonth = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        foreach ($leaves as $leave) {
            $start = \Carbon\Carbon::parse($leave->start_date);
            $end = \Carbon\Carbon::parse($leave->end_date);

            $overlapStart = $start->greaterThan($startOfMonth) ? $start : $startOfMonth;
            $overlapEnd = $end->lessThan($endOfMonth) ? $end : $endOfMonth;

            $totalDays += $overlapStart->diffInDays($overlapEnd) + 1;
        }
        return (int)$totalDays;
    }
}
