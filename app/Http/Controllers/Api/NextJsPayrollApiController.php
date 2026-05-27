<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NextJsPayrollApiController extends Controller
{
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
    private function getUserContext(Request $request): ?array
    {
        $userId = $request->header('X-User-Id');
        if (!$userId) {
            return null;
        }

        $isSuperAdmin = DB::table('assign_user_role')
            ->where('userID', $userId)
            ->where('roleID', 1)
            ->exists();

        $employee = DB::table('tblper')->where('UserID', $userId)->first();

        return [
            'userId'       => $userId,
            'isSuperAdmin' => $isSuperAdmin,
            'employee'     => $employee,
            'divisionID'   => $employee ? $employee->divisionID : null,
        ];
    }

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
                'COP. ASSET FIN', 'MEDICAL DEBT', 'ACC. NO', 'BANK', 'CODE', 'PAYER ID',
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

            $mapped = $allRows->map(function ($row) {
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
                    'LOAN'               => number_format((float)$row->loan_deduction, 2, '.', ''),
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
                    'REVOLVING LOAN BAL' => '0.00',
                    'COP.CONTR'          => '0.00',
                    'COP. LONE BAL'      => '0.00',
                    'COP. ASSET FIN'     => '0.00',
                    'MEDICAL DEBT'       => '0.00',
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
        $mapped = $allRows->map(function ($row) use ($dynamicByStaff) {
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
            $loan       = ($cvs[self::CVID_LAWYERS_LOAN] ?? 0) + ($cvs[self::CVID_VISA_LOAN] ?? 0);
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
                'REVOLVING LOAN BAL' => '0.00',
                'COP.CONTR'          => '0.00',
                'COP. LONE BAL'      => '0.00',
                'COP. ASSET FIN'     => '0.00',
                'MEDICAL DEBT'       => '0.00',
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

            $existingRun = DB::table('payroll_runs')
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if ($existingRun) {
                // Retrieve old conpt records to revert total_deducted in staffEarningAndDeduction
                $oldDetails = DB::table('payroll_conpt')
                    ->where('payroll_run_id', $existingRun->id)
                    ->get();

                foreach ($oldDetails as $od) {
                    if (!empty($od->applied_amounts)) {
                        $appliedMap = json_decode($od->applied_amounts, true);
                        if (is_array($appliedMap)) {
                            foreach ($appliedMap as $sedId => $amount) {
                                DB::table('staffEarningAndDeduction')
                                    ->where('id', $sedId)
                                    ->decrement('total_deducted', $amount);
                            }
                        }
                    }
                }

                // If it already exists, delete old payroll details
                DB::table('payroll_conpt')->where('payroll_run_id', $existingRun->id)->delete();
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

                // 3. Process earnings and deductions from staffEarningAndDeduction
                 $variables = DB::table('staffEarningAndDeduction')
                     ->where('staffId', $emp->ID)
                     ->get();

                $loanDeduction = 0.00;
                $coopSavings = 0.00;
                $otherDeductions = 0.00;
                $retention = 0.00;
                $surcharges = 0.00;
                $medicalLoan = 0.00;
                $coopLoanRpyt = 0.00;
                $absencePenalty = 0.00;
                $leaveOfAbsenceDeduction = 0.00;
                $totalEarningVars = 0.00;
                $appliedAmountsMap = [];
                $hasPensionDeduction = false;

                foreach ($variables as $v) {
                    $amount = (float)$v->amount;
                    $totalDeducted = (float)($v->total_deducted ?? 0.00);
                    $targetAmount = $v->target_amount !== null ? (float)$v->target_amount : 0.00;
                    $appliedAmount = 0.00;

                    // Check if it's a pension deduction flag
                    if (strtolower($v->variable_type) === 'deduction' && (stripos($v->description, 'pension') !== false || stripos($v->description, 'pention') !== false)) {
                        $hasPensionDeduction = true;
                        continue; // Skip raw amount deduction since it is calculated as a percentage dynamically
                    }

                    // Skip raw retention deduction since it is calculated dynamically as 5% of salary structures if active
                    if (strtolower($v->variable_type) === 'deduction' && stripos($v->description, 'retention') !== false) {
                        continue;
                    }

                    if (strtolower($v->variable_type) === 'deduction') {
                        if ($v->one_time == 1) {
                            if ($totalDeducted == 0) {
                                $appliedAmount = $amount;
                            }
                        } else {
                            if ($v->no_limit == 1) {
                                $appliedAmount = $amount;
                            } else {
                                if ($targetAmount > 0) {
                                    $remaining = max(0.00, $targetAmount - $totalDeducted);
                                    if ($remaining > 0) {
                                        $appliedAmount = min($amount, $remaining);
                                    }
                                } else {
                                    $appliedAmount = 0.00;
                                }
                            }
                        }

                        if ($appliedAmount > 0) {
                            // Update cumulative deducted amount
                            DB::table('staffEarningAndDeduction')
                                ->where('id', $v->id)
                                ->increment('total_deducted', $appliedAmount);

                            $appliedAmountsMap[$v->id] = $appliedAmount;

                            // Categorize deduction
                            $desc = strtolower($v->description);
                            if (strpos($desc, 'retention') !== false) {
                                $retention += $appliedAmount;
                            } elseif (strpos($desc, 'absence pen') !== false || strpos($desc, 'absence penalty') !== false) {
                                $absencePenalty += $appliedAmount;
                            } elseif (strpos($desc, 'surcharge') !== false) {
                                $surcharges += $appliedAmount;
                            } elseif (strpos($desc, 'med. loan') !== false || strpos($desc, 'medical loan') !== false) {
                                $medicalLoan += $appliedAmount;
                            } elseif (
                                (strpos($desc, 'coop') !== false || strpos($desc, 'cooperative') !== false) &&
                                (strpos($desc, 'loan') !== false || strpos($desc, 'rpyt') !== false || strpos($desc, 'rpty') !== false || strpos($desc, 'repay') !== false || $v->cv_setup_id == 24)
                            ) {
                                $coopLoanRpyt += $appliedAmount;
                            } elseif (strpos($desc, 'coop') !== false || strpos($desc, 'saving') !== false || in_array($v->cv_setup_id, [2, 5, 6])) {
                                $coopSavings += $appliedAmount;
                            } elseif (strpos($desc, 'loan') !== false || strpos($desc, 'debt') !== false || in_array($v->cv_setup_id, [3, 7])) {
                                $loanDeduction += $appliedAmount;
                            } else {
                                $otherDeductions += $appliedAmount;
                            }
                        }
                    } elseif (strtolower($v->variable_type) === 'earning') {
                        if ($v->one_time == 1) {
                            if ($totalDeducted == 0) {
                                $appliedAmount = $amount;
                            }
                        } else {
                            if ($v->no_limit == 1) {
                                $appliedAmount = $amount;
                            } else {
                                if ($targetAmount > 0) {
                                    $remaining = max(0.00, $targetAmount - $totalDeducted);
                                    if ($remaining > 0) {
                                        $appliedAmount = min($amount, $remaining);
                                    }
                                } else {
                                    $appliedAmount = 0.00;
                                }
                            }
                        }

                        if ($appliedAmount > 0) {
                            DB::table('staffEarningAndDeduction')
                                ->where('id', $v->id)
                                ->increment('total_deducted', $appliedAmount);

                            $appliedAmountsMap[$v->id] = $appliedAmount;

                            $totalEarningVars += $appliedAmount;
                        }
                    }
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

                // Compute dynamic retention if active in salary structure and count of previous deductions is < 20 months
                if ($struct && $struct->reten_act == 1) {
                    $previousDeductionsCount = DB::table('payroll_conpt as pc')
                        ->join('payroll_runs as pr', 'pr.id', '=', 'pc.payroll_run_id')
                        ->where('pc.staffID', $emp->ID)
                        ->where('pc.retention', '>', 0)
                        ->where(function($q) use ($year, $month) {
                            $q->where('pr.year', '<', $year)
                              ->orWhere(function($sq) use ($year, $month) {
                                  $sq->where('pr.year', '=', $year)
                                     ->where('pr.month', '<', $month);
                              });
                        })
                        ->count();

                    if ($previousDeductionsCount < 20) {
                        $retentionBase = $basic + $housing + $transport + $medical + $utility + $meal;
                        $retention = round(0.05 * $retentionBase, 2);
                    }
                }

                $totalDeductions = $payeTax + $pension + $loanDeduction + $coopSavings + $otherDeductions + $iouSum + $absencePenalty + $retention + $surcharges + $medicalLoan + $coopLoanRpyt + $leaveOfAbsenceDeduction;
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
                    'surcharges'       => round($surcharges, 2),
                    'medical_loan'     => round($medicalLoan, 2),
                    'coop_loan_rpyt'   => round($coopLoanRpyt, 2),
                    'total_deductions' => round($totalDeductions, 2),
                    'net_pay'          => round($netPay, 2),
                    'total_income'     => round($totalIncome, 2),
                    'declare_income'   => round($declareIncome, 2),
                    'iou'              => round($iouSum, 2),
                    'absence_penalty'  => round($absencePenalty, 2),
                    'leave_of_absence_deduction' => round($leaveOfAbsenceDeduction, 2),
                    'applied_amounts'  => !empty($appliedAmountsMap) ? json_encode($appliedAmountsMap) : null,
                    'created_at'       => now()
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
