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
        // 1. Base query for payment consolidated records
        $query = DB::table('tblpayment_consolidated as pc')
            ->leftJoin('tbldivision as d', 'd.divisionID', '=', 'pc.divisionID')
            ->leftJoin('tblbanklist as bl', 'bl.bankID', '=', 'pc.bank')
            ->where('pc.month', $month)
            ->where('pc.year', $year)
            ->where('pc.rank', '!=', 2)
            ->select(
                'pc.fileNo',
                'pc.name',
                'd.division',
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
                'DEPERTMENT'         => $row->division ?? '',
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
}
