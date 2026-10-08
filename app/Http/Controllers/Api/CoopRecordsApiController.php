<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CoopRecordsApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * Month integer to 3-letter abbreviation.
     */
    protected function getMonthName($monthInt)
    {
        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
        ];
        return $months[(int)$monthInt] ?? "M{$monthInt}";
    }

    /**
     * GET /api/nextjs/payroll/coop-records/staff-list
     * Retrieve staff members with active cooperative membership or history.
     */
    public function getStaffList(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $isPrivileged = $ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || ($ctx['isFinanceStaff'] ?? false) || ($ctx['isAuditStaff'] ?? false);

            $query = DB::table('tblper as p')
                ->where('p.rank', '!=', 2)
                ->where('p.staff_status', 1)
                ->leftJoin('tbldepartment as d', 'p.departmentID', '=', 'd.id')
                ->leftJoin('coop_savings_setups as css', function ($join) {
                    $join->on('css.staffId', '=', 'p.ID')
                         ->where('css.is_active', 1);
                })
                ->select(
                    'p.ID as id',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'd.department',
                    DB::raw('COALESCE(css.saving_balance, 0) as saving_balance'),
                    DB::raw('COALESCE(css.monthly_saving, 0) as monthly_saving')
                )
                ->orderBy('p.surname', 'asc');

            if (!$isPrivileged) {
                if ($ctx['employee']) {
                    $query->where('p.ID', $ctx['employee']->ID);
                } else {
                    $query->where('p.ID', 0);
                }
            }

            $staff = $query->get()->map(function ($row) {
                $fullName = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                return [
                    'id'             => $row->id,
                    'fileNo'         => $row->fileNo ?? '',
                    'name'           => $fullName,
                    'department'     => $row->department ?? 'General',
                    'saving_balance' => (float)$row->saving_balance,
                    'monthly_saving' => (float)$row->monthly_saving,
                ];
            });

            return response()->json([
                'status'       => 'success',
                'data'         => $staff,
                'isPrivileged' => $isPrivileged,
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopRecordsApiController getStaffList: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/coop-records/summary
     * Executive totals across all categories (or scoped to selected staff).
     */
    public function getSummary(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $isPrivileged = $ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || ($ctx['isFinanceStaff'] ?? false) || ($ctx['isAuditStaff'] ?? false);
            $staffId = $request->input('staff_id');

            if (!$isPrivileged && $ctx['employee']) {
                $staffId = $ctx['employee']->ID;
            }

            // 1. Total monthly savings deducted
            $savingsQuery = DB::table('payroll_conpt')->where('coop_savings', '>', 0);
            if ($staffId) $savingsQuery->where('staffID', $staffId);
            $totalSavingsDeducted = (float)$savingsQuery->sum('coop_savings');
            $savingsDeductionCount = $savingsQuery->count();

            // 2. Total monthly loan deductions
            $loanQuery = DB::table('payroll_conpt')->where('coop_loan_rpyt', '>', 0);
            if ($staffId) $loanQuery->where('staffID', $staffId);
            $totalLoanDeducted = (float)$loanQuery->sum('coop_loan_rpyt');
            $loanDeductionCount = $loanQuery->count();

            // 3. Total voluntary top-ups
            $topUpQuery = DB::table('coop_savings_top_ups');
            if ($staffId) $topUpQuery->where('staffId', $staffId);
            $totalTopUps = (float)$topUpQuery->sum('amount');
            $topUpCount = $topUpQuery->count();

            // 4. Total withdrawals disbursed (paid)
            $withdrawalQuery = DB::table('coop_savings_withdrawals')->where('status', 'paid');
            if ($staffId) $withdrawalQuery->where('staffId', $staffId);
            $totalWithdrawals = (float)$withdrawalQuery->sum('approved_amount');
            $withdrawalCount = $withdrawalQuery->count();

            // 5. Total applications approved by HR Head
            // Loans approved by HR
            $approvedLoansQuery = DB::table('coop_loans')->whereIn(DB::raw('LOWER(status)'), ['hr_approved', 'approved']);
            if ($staffId) $approvedLoansQuery->where('staffId', $staffId);
            $approvedLoansCount = $approvedLoansQuery->count();
            $approvedLoansAmount = (float)$approvedLoansQuery->sum('loan_amount');

            // Withdrawals approved by HR
            $approvedWithdrawalsQuery = DB::table('coop_savings_withdrawals')->whereIn(DB::raw('LOWER(status)'), ['hr_approved', 'paid']);
            if ($staffId) $approvedWithdrawalsQuery->where('staffId', $staffId);
            $approvedWithdrawalsCount = $approvedWithdrawalsQuery->count();
            $approvedWithdrawalsAmount = (float)$approvedWithdrawalsQuery->sum('approved_amount');

            // 6. Current active balances
            $balanceQuery = DB::table('coop_savings_setups')->where('is_active', 1);
            if ($staffId) $balanceQuery->where('staffId', $staffId);
            $currentSavingsBalance = (float)$balanceQuery->sum('saving_balance');

            $loanBalQuery = DB::table('coop_loan_deduction_setups')->where('is_active', 1);
            if ($staffId) $loanBalQuery->where('staffId', $staffId);
            $currentLoanOutstanding = (float)$loanBalQuery->sum('balance_remaining');

            // 7. Active member count
            $activeMembersCount = DB::table('coop_savings_setups')->where('is_active', 1)->distinct('staffId')->count();

            return response()->json([
                'status' => 'success',
                'summary' => [
                    'total_savings_deducted'      => $totalSavingsDeducted,
                    'savings_deduction_count'     => $savingsDeductionCount,
                    'total_loan_deducted'         => $totalLoanDeducted,
                    'loan_deduction_count'        => $loanDeductionCount,
                    'total_top_ups'               => $totalTopUps,
                    'top_up_count'                => $topUpCount,
                    'total_withdrawals'           => $totalWithdrawals,
                    'withdrawal_count'            => $withdrawalCount,
                    'approved_loans_count'        => $approvedLoansCount,
                    'approved_loans_amount'       => $approvedLoansAmount,
                    'approved_withdrawals_count'  => $approvedWithdrawalsCount,
                    'approved_withdrawals_amount' => $approvedWithdrawalsAmount,
                    'total_hr_approved_count'     => $approvedLoansCount + $approvedWithdrawalsCount,
                    'total_hr_approved_amount'    => $approvedLoansAmount + $approvedWithdrawalsAmount,
                    'current_savings_balance'     => $currentSavingsBalance,
                    'current_loan_outstanding'    => $currentLoanOutstanding,
                    'active_members_count'        => $activeMembersCount,
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopRecordsApiController getSummary: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/coop-records/ledger
     * Main ledger with multi-category filtering, unified passbook view, search, and pagination.
     */
    public function getLedger(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $isPrivileged = $ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || ($ctx['isFinanceStaff'] ?? false) || ($ctx['isAuditStaff'] ?? false);
            $category = $request->input('category', 'all'); // 'all' | 'savings_deductions' | 'loan_deductions' | 'top_ups' | 'withdrawals' | 'hr_approved'
            $staffId = $request->input('staff_id');
            $year = $request->input('year', 'all');
            $month = $request->input('month', 'all');
            $fromDate = $request->input('from_date');
            $toDate = $request->input('to_date');
            $search = trim($request->input('search', ''));
            $page = max(1, (int)$request->input('page', 1));
            $perPage = max(1, min(100, (int)$request->input('per_page', 15)));

            if (!$isPrivileged && $ctx['employee']) {
                $staffId = $ctx['employee']->ID;
            }

            $items = collect();

            // ── A. Monthly Savings Deductions
            if (in_array($category, ['all', 'savings_deductions'])) {
                $q = DB::table('payroll_conpt as pc')
                    ->join('tblper as p', 'p.ID', '=', 'pc.staffID')
                    ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                    ->where('pc.coop_savings', '>', 0);

                if ($staffId) $q->where('pc.staffID', $staffId);
                if ($year !== 'all') $q->where('pc.year', (int)$year);
                if ($month !== 'all') $q->where('pc.month', (int)$month);

                $savingsRows = $q->select(
                    'pc.id as raw_id',
                    'pc.staffID',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'd.department',
                    'pc.month',
                    'pc.year',
                    'pc.coop_savings as amount',
                    'pc.created_at',
                    'pc.payroll_run_id'
                )->get();

                foreach ($savingsRows as $r) {
                    $monthName = $this->getMonthName($r->month);
                    $periodStr = "{$monthName} {$r->year}";
                    $staffName = trim("{$r->surname} {$r->first_name} {$r->othernames}");
                    $items->push([
                        'id'            => "sav_{$r->raw_id}",
                        'category'      => 'savings_deductions',
                        'type_label'    => 'Monthly Savings Deduction',
                        'flow_type'     => 'credit', // Inflow to savings
                        'reference'     => "CSD-{$r->year}-" . str_pad($r->month, 2, '0', STR_PAD_LEFT) . "-#{$r->staffID}",
                        'staff_id'      => $r->staffID,
                        'staff_fileno'  => $r->fileNo ?? '',
                        'staff_name'    => $staffName,
                        'department'    => $r->department ?? 'General',
                        'amount'        => (float)$r->amount,
                        'period'        => $periodStr,
                        'month'         => (int)$r->month,
                        'year'          => (int)$r->year,
                        'date'          => $r->created_at ? substr($r->created_at, 0, 10) : "{$r->year}-" . str_pad($r->month, 2, '0', STR_PAD_LEFT) . "-28",
                        'status'        => 'deducted',
                        'status_label'  => 'Deducted (Payroll)',
                        'notes'         => "Monthly cooperative savings contribution deducted via payroll ({$periodStr})",
                        'extra'         => [
                            'payroll_run_id' => $r->payroll_run_id,
                        ]
                    ]);
                }
            }

            // ── B. Monthly Loan Repayments Deducted
            if (in_array($category, ['all', 'loan_deductions'])) {
                $q = DB::table('payroll_conpt as pc')
                    ->join('tblper as p', 'p.ID', '=', 'pc.staffID')
                    ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                    ->where('pc.coop_loan_rpyt', '>', 0);

                if ($staffId) $q->where('pc.staffID', $staffId);
                if ($year !== 'all') $q->where('pc.year', (int)$year);
                if ($month !== 'all') $q->where('pc.month', (int)$month);

                $loanRows = $q->select(
                    'pc.id as raw_id',
                    'pc.staffID',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'd.department',
                    'pc.month',
                    'pc.year',
                    'pc.coop_loan_rpyt as amount',
                    'pc.created_at',
                    'pc.payroll_run_id'
                )->get();

                foreach ($loanRows as $r) {
                    $monthName = $this->getMonthName($r->month);
                    $periodStr = "{$monthName} {$r->year}";
                    $staffName = trim("{$r->surname} {$r->first_name} {$r->othernames}");
                    $items->push([
                        'id'            => "loan_{$r->raw_id}",
                        'category'      => 'loan_deductions',
                        'type_label'    => 'Monthly Loan Deduction',
                        'flow_type'     => 'debit_payroll', // Loan repayment
                        'reference'     => "CLD-{$r->year}-" . str_pad($r->month, 2, '0', STR_PAD_LEFT) . "-#{$r->staffID}",
                        'staff_id'      => $r->staffID,
                        'staff_fileno'  => $r->fileNo ?? '',
                        'staff_name'    => $staffName,
                        'department'    => $r->department ?? 'General',
                        'amount'        => (float)$r->amount,
                        'period'        => $periodStr,
                        'month'         => (int)$r->month,
                        'year'          => (int)$r->year,
                        'date'          => $r->created_at ? substr($r->created_at, 0, 10) : "{$r->year}-" . str_pad($r->month, 2, '0', STR_PAD_LEFT) . "-28",
                        'status'        => 'repaid',
                        'status_label'  => 'Repayment Deducted',
                        'notes'         => "Monthly cooperative loan repayment deducted via payroll ({$periodStr})",
                        'extra'         => [
                            'payroll_run_id' => $r->payroll_run_id,
                        ]
                    ]);
                }
            }

            // ── C. Cooperative Savings Top-Ups
            if (in_array($category, ['all', 'top_ups'])) {
                $q = DB::table('coop_savings_top_ups as tu')
                    ->join('tblper as p', 'p.ID', '=', 'tu.staffId')
                    ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID');

                if ($staffId) $q->where('tu.staffId', $staffId);

                $topUpRows = $q->select(
                    'tu.id as raw_id',
                    'tu.staffId',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'd.department',
                    'tu.amount',
                    'tu.payment_method',
                    'tu.payment_reference',
                    'tu.top_up_reference',
                    'tu.payment_date',
                    'tu.notes',
                    'tu.created_at'
                )->get();

                foreach ($topUpRows as $r) {
                    $staffName = trim("{$r->surname} {$r->first_name} {$r->othernames}");
                    $payDate = $r->payment_date ?: substr($r->created_at, 0, 10);
                    $dt = new \DateTime($payDate);
                    $mInt = (int)$dt->format('n');
                    $yInt = (int)$dt->format('Y');

                    if ($year !== 'all' && $yInt !== (int)$year) continue;
                    if ($month !== 'all' && $mInt !== (int)$month) continue;

                    $items->push([
                        'id'            => "top_{$r->raw_id}",
                        'category'      => 'top_ups',
                        'type_label'    => 'Savings Top-Up',
                        'flow_type'     => 'credit', // Inflow to savings
                        'reference'     => $r->top_up_reference ?: ($r->payment_reference ?: "TOP-{$r->raw_id}"),
                        'staff_id'      => $r->staffId,
                        'staff_fileno'  => $r->fileNo ?? '',
                        'staff_name'    => $staffName,
                        'department'    => $r->department ?? 'General',
                        'amount'        => (float)$r->amount,
                        'period'        => $this->getMonthName($mInt) . " {$yInt}",
                        'month'         => $mInt,
                        'year'          => $yInt,
                        'date'          => $payDate,
                        'status'        => 'credited',
                        'status_label'  => 'Credited to Savings',
                        'notes'         => $r->notes ?: "Direct voluntary top-up via " . ucwords(str_replace('_', ' ', $r->payment_method ?? 'transfer')),
                        'extra'         => [
                            'payment_method'    => $r->payment_method,
                            'payment_reference' => $r->payment_reference,
                            'top_up_reference'  => $r->top_up_reference,
                        ]
                    ]);
                }
            }

            // ── D. Cooperative Savings Withdrawals
            if (in_array($category, ['all', 'withdrawals'])) {
                $q = DB::table('coop_savings_withdrawals as w')
                    ->join('tblper as p', 'p.ID', '=', 'w.staffId')
                    ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID');

                if ($staffId) $q->where('w.staffId', $staffId);

                $withdrawalRows = $q->select(
                    'w.id as raw_id',
                    'w.staffId',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'd.department',
                    'w.withdrawal_reference',
                    'w.withdrawal_type',
                    'w.requested_amount',
                    'w.approved_amount',
                    'w.status',
                    'w.payment_method',
                    'w.payment_reference',
                    'w.payment_date',
                    'w.reason',
                    'w.bank_name',
                    'w.account_number',
                    'w.account_name',
                    'w.created_at',
                    'w.updated_at'
                )->get();

                foreach ($withdrawalRows as $r) {
                    $staffName = trim("{$r->surname} {$r->first_name} {$r->othernames}");
                    $payDate = $r->payment_date ?: ($r->created_at ? substr($r->created_at, 0, 10) : date('Y-m-d'));
                    $dt = new \DateTime($payDate);
                    $mInt = (int)$dt->format('n');
                    $yInt = (int)$dt->format('Y');

                    if ($year !== 'all' && $yInt !== (int)$year) continue;
                    if ($month !== 'all' && $mInt !== (int)$month) continue;

                    $isPaid = $r->status === 'paid';
                    $statusLabel = $isPaid ? 'Disbursed (Paid)' : ($r->status === 'hr_approved' ? 'HR Approved' : ($r->status === 'pending' ? 'Pending HR' : 'Rejected'));

                    $items->push([
                        'id'            => "wth_{$r->raw_id}",
                        'category'      => 'withdrawals',
                        'type_label'    => $r->withdrawal_type === 'full' ? 'Full Liquidation Withdrawal' : 'Partial Savings Withdrawal',
                        'flow_type'     => 'debit_savings', // Outflow from savings
                        'reference'     => $r->withdrawal_reference,
                        'staff_id'      => $r->staffId,
                        'staff_fileno'  => $r->fileNo ?? '',
                        'staff_name'    => $staffName,
                        'department'    => $r->department ?? 'General',
                        'amount'        => (float)($r->approved_amount ?: $r->requested_amount),
                        'period'        => $this->getMonthName($mInt) . " {$yInt}",
                        'month'         => $mInt,
                        'year'          => $yInt,
                        'date'          => $payDate,
                        'status'        => $r->status,
                        'status_label'  => $statusLabel,
                        'notes'         => $r->reason ?: "Cooperative savings withdrawal ({$r->withdrawal_type})",
                        'extra'         => [
                            'withdrawal_type'   => $r->withdrawal_type,
                            'requested_amount'  => (float)$r->requested_amount,
                            'approved_amount'   => (float)$r->approved_amount,
                            'bank_name'         => $r->bank_name,
                            'account_number'    => $r->account_number,
                            'payment_reference' => $r->payment_reference,
                        ]
                    ]);
                }
            }

            // ── E. HR Head Approved Applications (Loans + Withdrawals)
            if (in_array($category, ['all', 'hr_approved'])) {
                // 1. Loans approved by HR Head
                $loanAppQ = DB::table('coop_loans as cl')
                    ->join('tblper as p', 'p.ID', '=', 'cl.staffId')
                    ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                    ->whereIn(DB::raw('LOWER(cl.status)'), ['hr_approved', 'approved']);

                if ($staffId) $loanAppQ->where('cl.staffId', $staffId);

                $loanApps = $loanAppQ->select(
                    'cl.id as raw_id',
                    'cl.staffId',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'd.department',
                    'cl.loan_type',
                    'cl.loan_amount',
                    'cl.monthly_deduction',
                    'cl.balance',
                    'cl.status',
                    'cl.created_at',
                    'cl.updated_at'
                )->get();

                foreach ($loanApps as $r) {
                    $staffName = trim("{$r->surname} {$r->first_name} {$r->othernames}");
                    $appDate = $r->updated_at ? substr($r->updated_at, 0, 10) : substr($r->created_at, 0, 10);
                    $dt = new \DateTime($appDate);
                    $mInt = (int)$dt->format('n');
                    $yInt = (int)$dt->format('Y');

                    if ($year !== 'all' && $yInt !== (int)$year) continue;
                    if ($month !== 'all' && $mInt !== (int)$month) continue;

                    // When category is hr_approved specifically, include them
                    // When category is 'all', avoid duplicate with loan_deductions by setting category 'hr_approved_loan'
                    if ($category === 'hr_approved' || $category === 'all') {
                        $items->push([
                            'id'            => "hra_loan_{$r->raw_id}",
                            'category'      => 'hr_approved',
                            'type_label'    => 'HR Head Approved: Coop Loan',
                            'flow_type'     => 'approval',
                            'reference'     => "HR-LOAN-#{$r->raw_id}",
                            'staff_id'      => $r->staffId,
                            'staff_fileno'  => $r->fileNo ?? '',
                            'staff_name'    => $staffName,
                            'department'    => $r->department ?? 'General',
                            'amount'        => (float)$r->loan_amount,
                            'period'        => $this->getMonthName($mInt) . " {$yInt}",
                            'month'         => $mInt,
                            'year'          => $yInt,
                            'date'          => $appDate,
                            'status'        => $r->status,
                            'status_label'  => 'HR Head Approved',
                            'notes'         => "Cooperative loan application for " . ucwords($r->loan_type) . " approved by HR Head. Monthly repayment: ₦" . number_format($r->monthly_deduction, 2),
                            'extra'         => [
                                'loan_type'         => $r->loan_type,
                                'monthly_deduction' => (float)$r->monthly_deduction,
                                'balance'           => (float)$r->balance,
                            ]
                        ]);
                    }
                }

                // 2. Withdrawals approved by HR Head
                $wthAppQ = DB::table('coop_savings_withdrawals as w')
                    ->join('tblper as p', 'p.ID', '=', 'w.staffId')
                    ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                    ->whereIn(DB::raw('LOWER(w.status)'), ['hr_approved', 'paid']);

                if ($staffId) $wthAppQ->where('w.staffId', $staffId);

                $wthApps = $wthAppQ->select(
                    'w.id as raw_id',
                    'w.staffId',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'd.department',
                    'w.withdrawal_reference',
                    'w.withdrawal_type',
                    'w.requested_amount',
                    'w.approved_amount',
                    'w.status',
                    'w.hr_notes',
                    'w.hr_reviewed_at',
                    'w.created_at'
                )->get();

                foreach ($wthApps as $r) {
                    $staffName = trim("{$r->surname} {$r->first_name} {$r->othernames}");
                    $appDate = $r->hr_reviewed_at ? substr($r->hr_reviewed_at, 0, 10) : substr($r->created_at, 0, 10);
                    $dt = new \DateTime($appDate);
                    $mInt = (int)$dt->format('n');
                    $yInt = (int)$dt->format('Y');

                    if ($year !== 'all' && $yInt !== (int)$year) continue;
                    if ($month !== 'all' && $mInt !== (int)$month) continue;

                    if ($category === 'hr_approved') {
                        $items->push([
                            'id'            => "hra_wth_{$r->raw_id}",
                            'category'      => 'hr_approved',
                            'type_label'    => 'HR Head Approved: Withdrawal',
                            'flow_type'     => 'approval',
                            'reference'     => $r->withdrawal_reference,
                            'staff_id'      => $r->staffId,
                            'staff_fileno'  => $r->fileNo ?? '',
                            'staff_name'    => $staffName,
                            'department'    => $r->department ?? 'General',
                            'amount'        => (float)($r->approved_amount ?: $r->requested_amount),
                            'period'        => $this->getMonthName($mInt) . " {$yInt}",
                            'month'         => $mInt,
                            'year'          => $yInt,
                            'date'          => $appDate,
                            'status'        => $r->status,
                            'status_label'  => 'HR Head Approved',
                            'notes'         => $r->hr_notes ?: "Withdrawal application approved by HR Head ({$r->withdrawal_type})",
                            'extra'         => [
                                'withdrawal_type'  => $r->withdrawal_type,
                                'requested_amount' => (float)$r->requested_amount,
                                'approved_amount'  => (float)$r->approved_amount,
                            ]
                        ]);
                    }
                }
            }

            // ── Search & Date Filtering
            if ($fromDate) {
                $items = $items->filter(fn($it) => $it['date'] >= $fromDate);
            }
            if ($toDate) {
                $items = $items->filter(fn($it) => $it['date'] <= $toDate);
            }

            if ($search !== '') {
                $sLower = strtolower($search);
                $items = $items->filter(function ($it) use ($sLower) {
                    return str_contains(strtolower($it['reference']), $sLower)
                        || str_contains(strtolower($it['staff_name']), $sLower)
                        || str_contains(strtolower($it['staff_fileno']), $sLower)
                        || str_contains(strtolower((string)$it['staff_id']), $sLower)
                        || str_contains(strtolower($it['type_label']), $sLower)
                        || str_contains(strtolower($it['notes']), $sLower);
                });
            }

            // Sort chronologically descending
            $sorted = $items->sortByDesc('date')->values();
            $totalCount = $sorted->count();

            // Paginate
            $paginated = $sorted->slice(($page - 1) * $perPage, $perPage)->values();

            return response()->json([
                'status'       => 'success',
                'data'         => $paginated,
                'total'        => $totalCount,
                'page'         => $page,
                'per_page'     => $perPage,
                'category'     => $category,
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopRecordsApiController getLedger: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/coop-records/statement-print
     * Comprehensive printable member passbook / official statement.
     */
    public function getPrintStatement(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $isPrivileged = $ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || ($ctx['isFinanceStaff'] ?? false) || ($ctx['isAuditStaff'] ?? false);
            $staffId = $request->input('staff_id');

            if (!$isPrivileged && $ctx['employee']) {
                $staffId = $ctx['employee']->ID;
            }

            if (!$staffId) {
                return response()->json(['status' => 'error', 'message' => 'Please select a staff member to print their statement.'], 422);
            }

            $staff = DB::table('tblper as p')
                ->leftJoin('tbldepartment as d', 'p.departmentID', '=', 'd.id')
                ->leftJoin('coop_savings_setups as css', 'css.staffId', '=', 'p.ID')
                ->leftJoin('coop_loan_deduction_setups as clds', function ($j) {
                    $j->on('clds.staffId', '=', 'p.ID')->where('clds.is_active', 1);
                })
                ->where('p.ID', $staffId)
                ->select(
                    'p.ID as staffId',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'p.phone',
                    'd.department',
                    DB::raw('COALESCE(css.saving_balance, 0) as saving_balance'),
                    DB::raw('COALESCE(css.monthly_saving, 0) as monthly_saving'),
                    DB::raw('COALESCE(clds.balance_remaining, 0) as loan_balance'),
                    DB::raw('COALESCE(clds.monthly_deduction, 0) as loan_monthly_deduction')
                )
                ->first();

            if (!$staff) {
                return response()->json(['status' => 'error', 'message' => 'Staff record not found.'], 404);
            }

            $staff->name = trim("{$staff->surname} {$staff->first_name} {$staff->othernames}");

            // Gather all chronologically ordered savings transactions
            $transactions = collect();

            // 1. Savings deductions
            $savings = DB::table('payroll_conpt')
                ->where('staffID', $staffId)
                ->where('coop_savings', '>', 0)
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

            foreach ($savings as $s) {
                $monthName = $this->getMonthName($s->month);
                $dateStr = "{$s->year}-" . str_pad($s->month, 2, '0', STR_PAD_LEFT) . "-28";
                $transactions->push([
                    'date'        => $dateStr,
                    'reference'   => "CSD-{$s->year}-" . str_pad($s->month, 2, '0', STR_PAD_LEFT),
                    'description' => "Monthly Cooperative Savings Contribution ({$monthName} {$s->year})",
                    'type'        => 'SAVINGS_DEDUCTION',
                    'credit'      => (float)$s->coop_savings,
                    'debit'       => 0.00,
                ]);
            }

            // 2. Top-ups
            $topUps = DB::table('coop_savings_top_ups')
                ->where('staffId', $staffId)
                ->orderBy('payment_date', 'asc')
                ->get();

            foreach ($topUps as $tu) {
                $transactions->push([
                    'date'        => $tu->payment_date ?: substr($tu->created_at, 0, 10),
                    'reference'   => $tu->top_up_reference ?: ($tu->payment_reference ?: "TOP-{$tu->id}"),
                    'description' => "Direct Savings Top-Up (" . ucwords(str_replace('_', ' ', $tu->payment_method ?? 'transfer')) . ") Ref: " . ($tu->payment_reference ?: '—'),
                    'type'        => 'SAVINGS_TOPUP',
                    'credit'      => (float)$tu->amount,
                    'debit'       => 0.00,
                ]);
            }

            // 3. Withdrawals
            $withdrawals = DB::table('coop_savings_withdrawals')
                ->where('staffId', $staffId)
                ->where('status', 'paid')
                ->orderBy('payment_date', 'asc')
                ->get();

            foreach ($withdrawals as $w) {
                $transactions->push([
                    'date'        => $w->payment_date ?: substr($w->updated_at, 0, 10),
                    'reference'   => $w->withdrawal_reference,
                    'description' => "Savings Withdrawal (" . ($w->withdrawal_type === 'full' ? 'Full Liquidation' : 'Partial') . ") Paid to " . ($w->bank_name ? "{$w->bank_name} • {$w->account_number}" : 'Account'),
                    'type'        => 'SAVINGS_WITHDRAWAL',
                    'credit'      => 0.00,
                    'debit'       => (float)($w->approved_amount ?: $w->requested_amount),
                ]);
            }

            // 4. Loan repayments
            $loanRepayments = DB::table('payroll_conpt')
                ->where('staffID', $staffId)
                ->where('coop_loan_rpyt', '>', 0)
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

            $loanTransactions = collect();
            foreach ($loanRepayments as $lr) {
                $monthName = $this->getMonthName($lr->month);
                $dateStr = "{$lr->year}-" . str_pad($lr->month, 2, '0', STR_PAD_LEFT) . "-28";
                $loanTransactions->push([
                    'date'        => $dateStr,
                    'reference'   => "CLD-{$lr->year}-" . str_pad($lr->month, 2, '0', STR_PAD_LEFT),
                    'description' => "Monthly Loan Repayment Deduction ({$monthName} {$lr->year})",
                    'amount'      => (float)$lr->coop_loan_rpyt,
                ]);
            }

            // Calculate running savings balance
            $sortedTxns = $transactions->sortBy('date')->values();
            $runningBalance = 0.00;
            $finalTxns = [];

            foreach ($sortedTxns as $t) {
                $runningBalance += ($t['credit'] - $t['debit']);
                $t['balance'] = max(0.00, $runningBalance);
                $finalTxns[] = $t;
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'staff'             => $staff,
                    'transactions'      => $finalTxns,
                    'loan_transactions' => $loanTransactions,
                    'total_credited'    => array_sum(array_column($finalTxns, 'credit')),
                    'total_debited'     => array_sum(array_column($finalTxns, 'debit')),
                    'total_loan_repaid' => $loanTransactions->sum('amount'),
                    'closing_balance'   => $runningBalance,
                    'printed_at'        => now()->toDateTimeString(),
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopRecordsApiController getPrintStatement: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
