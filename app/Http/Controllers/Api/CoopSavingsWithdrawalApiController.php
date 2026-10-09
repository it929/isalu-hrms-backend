<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\CoopSavingsWithdrawal;
use App\Helpers\FileUploadHelper;

class CoopSavingsWithdrawalApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * GET /api/nextjs/payroll/coop-savings-withdrawal/staff-list
     * Returns staff members with current savings balance, active loan balance, and max withdrawable amount.
     */
    public function staffList(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $isPrivileged = $ctx['isSuperAdmin'] || !empty($ctx['isHrHead']) || $ctx['isAdminStaff'] || !empty($ctx['isFinanceHead']) || ($ctx['isFinanceStaff'] ?? false) || ($ctx['isAuditStaff'] ?? false);
            $search = trim($request->input('search', ''));

            $query = DB::table('tblper as p')
                ->where('p.rank', '!=', 2)
                ->where('p.staff_status', 1)
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->leftJoin('coop_savings_setups as css', function ($join) {
                    $join->on('css.staffId', '=', 'p.ID')
                         ->where('css.is_active', 1);
                })
                ->select(
                    'p.ID as staffId',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'p.gender',
                    'd.department',
                    DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as name"),
                    DB::raw('COALESCE(MAX(css.saving_balance), 0.00) as saving_balance'),
                    DB::raw('COALESCE(MAX(css.monthly_saving), 0.00) as monthly_saving'),
                    DB::raw('MAX(css.id) as savings_setup_id')
                )
                ->groupBy('p.ID', 'p.fileNo', 'p.surname', 'p.first_name', 'p.othernames', 'p.gender', 'd.department');

            if (!$isPrivileged) {
                if ($ctx['employee']) {
                    $query->where('p.ID', $ctx['employee']->ID);
                } else {
                    $query->where('p.ID', 0);
                }
            } elseif ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('p.ID', 'like', "%{$search}%")
                      ->orWhere('p.fileNo', 'like', "%{$search}%")
                      ->orWhere('p.surname', 'like', "%{$search}%")
                      ->orWhere('p.first_name', 'like', "%{$search}%")
                      ->orWhere('p.othernames', 'like', "%{$search}%");
                });
            }

            $staff = $query->orderBy('p.surname', 'asc')->get()->map(function ($row) {
                // Check active loan balance
                $activeLoanBalance = (float)DB::table('coop_loan_deduction_setups')
                    ->where('staffId', $row->staffId)
                    ->where('is_active', 1)
                    ->where('balance_remaining', '>', 0)
                    ->sum('balance_remaining');

                $savingBalance = (float)$row->saving_balance;
                $maxWithdrawable = max(0.00, $savingBalance - $activeLoanBalance);

                return [
                    'id'                  => $row->staffId,
                    'staffId'             => $row->staffId,
                    'fileNo'              => $row->fileNo ?? '',
                    'name'                => trim($row->name),
                    'department'          => $row->department ?? '',
                    'gender'              => strtolower(trim($row->gender ?? '')),
                    'saving_balance'      => $savingBalance,
                    'monthly_saving'      => (float)$row->monthly_saving,
                    'savings_setup_id'    => $row->savings_setup_id ? (int)$row->savings_setup_id : null,
                    'active_loan_balance' => $activeLoanBalance,
                    'max_withdrawable'    => $maxWithdrawable,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $staff,
                'isPrivileged' => $isPrivileged,
                'isSuperAdmin' => $ctx['isSuperAdmin'],
                'isHrHead' => !empty($ctx['isHrHead']),
                'isAdminStaff' => $ctx['isAdminStaff'],
                'isAuditHead' => !empty($ctx['isAuditHead']),
                'isAuditStaff' => $ctx['isAuditStaff'] ?? false,
                'isFinanceHead' => !empty($ctx['isFinanceHead']),
                'isFinanceStaff' => $ctx['isFinanceStaff'] ?? false,
                'currentEmployee' => $ctx['employee'] ? [
                    'id' => $ctx['employee']->ID,
                    'fileNo' => $ctx['employee']->fileNo ?? '',
                    'name' => trim("{$ctx['employee']->surname} {$ctx['employee']->first_name} {$ctx['employee']->othernames}"),
                ] : null,
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopSavingsWithdrawalApiController staffList: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/coop-savings-withdrawal/staff-details/{staffId}
     * Returns real-time savings balance, active loan collateral hold, bank payout details, and active withdrawal requests.
     */
    public function staffDetails(Request $request, $staffId)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $isPrivileged = $ctx['isSuperAdmin'] || !empty($ctx['isHrHead']) || $ctx['isAdminStaff'] || !empty($ctx['isFinanceHead']) || ($ctx['isFinanceStaff'] ?? false) || ($ctx['isAuditStaff'] ?? false);
            if (!$isPrivileged && (!$ctx['employee'] || (int)$ctx['employee']->ID !== (int)$staffId)) {
                return response()->json(['status' => 'error', 'message' => 'Access denied.'], 403);
            }

            $staff = DB::table('tblper as p')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->leftJoin('tblbanklist as b', 'b.bankID', '=', 'p.bankID')
                ->where('p.ID', $staffId)
                ->select(
                    'p.ID as staffId',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'p.AccNo as account_number',
                    'p.bankID',
                    'b.bank as bank_name',
                    'd.department'
                )
                ->first();

            if (!$staff) {
                return response()->json(['status' => 'error', 'message' => 'Staff record not found.'], 404);
            }

            // Savings setup
            $savings = DB::table('coop_savings_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->orderBy('id', 'desc')
                ->first();

            $savingBalance = $savings ? (float)$savings->saving_balance : 0.00;
            $monthlySaving = $savings ? (float)$savings->monthly_saving : 0.00;
            $savingsSetupId = $savings ? (int)$savings->id : null;

            // Active cooperative loan setups
            $activeLoans = DB::table('coop_loan_deduction_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->where('balance_remaining', '>', 0)
                ->get();

            $activeLoanBalance = (float)$activeLoans->sum('balance_remaining');
            $collateralHold = $activeLoanBalance; // 100% of outstanding loan is retained as collateral
            $maxWithdrawable = max(0.00, $savingBalance - $collateralHold);

            // Active / pending withdrawal requests
            $hasPendingRequest = DB::table('coop_savings_withdrawals')
                ->where('staffId', $staffId)
                ->whereIn('status', ['pending', 'hr_approved', 'audit_approved'])
                ->exists();

            $fullName = trim("{$staff->surname} {$staff->first_name} {$staff->othernames}");

            return response()->json([
                'status' => 'success',
                'data' => [
                    'staffId'                 => (int)$staff->staffId,
                    'fileNo'                  => $staff->fileNo ?? '',
                    'name'                    => $fullName,
                    'department'              => $staff->department ?? 'General',
                    'savings_setup_id'        => $savingsSetupId,
                    'saving_balance'          => $savingBalance,
                    'monthly_saving'          => $monthlySaving,
                    'active_loan_balance'     => $activeLoanBalance,
                    'collateral_locked_amount'=> $collateralHold,
                    'max_withdrawable'        => $maxWithdrawable,
                    'has_pending_request'     => $hasPendingRequest,
                    'default_bank' => [
                        'bank_name'      => $staff->bank_name ?? '',
                        'account_number' => $staff->account_number ?? '',
                        'account_name'   => $fullName,
                    ],
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopSavingsWithdrawalApiController staffDetails: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/coop-savings-withdrawal/apply
     * Submit an application for cooperative savings withdrawal.
     */
    public function apply(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $validator = Validator::make($request->all(), [
                'staffId'         => 'required|integer',
                'withdrawal_type' => 'required|in:partial,full',
                'requested_amount'=> 'required|numeric|min:1',
                'reason'          => 'required|string|max:1000',
                'bank_name'       => 'required|string|max:100',
                'account_number'  => 'required|string|max:50',
                'account_name'    => 'required|string|max:150',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
            }

            $staffId = (int)$request->input('staffId');
            $withdrawalType = $request->input('withdrawal_type');
            $requestedAmount = round((float)$request->input('requested_amount'), 2);

            $isPrivileged = $ctx['isSuperAdmin'] || $ctx['isAdminStaff'];
            if (!$isPrivileged && (!$ctx['employee'] || (int)$ctx['employee']->ID !== $staffId)) {
                return response()->json(['status' => 'error', 'message' => 'You are only authorized to apply for your own cooperative savings withdrawal.'], 403);
            }

            // Check if there is already an ongoing pending/unpaid application for this staff
            $existingPending = DB::table('coop_savings_withdrawals')
                ->where('staffId', $staffId)
                ->whereIn('status', ['pending', 'hr_approved', 'audit_approved'])
                ->first();

            if ($existingPending) {
                return response()->json([
                    'status' => 'error',
                    'message' => "An active withdrawal application ({$existingPending->withdrawal_reference}) is already in progress. Please await its completion or cancellation before submitting a new one."
                ], 422);
            }

            // Verify active savings setup
            $savings = DB::table('coop_savings_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->first();

            if (!$savings || (float)$savings->saving_balance <= 0) {
                return response()->json(['status' => 'error', 'message' => 'Staff has no active cooperative savings balance.'], 422);
            }

            $savingBalance = (float)$savings->saving_balance;

            // Check active loans and collateral
            $activeLoanBalance = (float)DB::table('coop_loan_deduction_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->where('balance_remaining', '>', 0)
                ->sum('balance_remaining');

            $collateralHold = $activeLoanBalance;
            $maxWithdrawable = max(0.00, $savingBalance - $collateralHold);

            if ($withdrawalType === 'full') {
                if ($activeLoanBalance > 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Cannot liquidate full cooperative savings while you have an active cooperative loan balance of ₦" . number_format($activeLoanBalance, 2) . ". Please offset or settle your loan before closing your savings account."
                    ], 422);
                }
                $requestedAmount = $savingBalance;
            } else {
                // Partial withdrawal
                if ($requestedAmount > $maxWithdrawable) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Requested withdrawal amount (₦" . number_format($requestedAmount, 2) . ") exceeds maximum withdrawable balance (₦" . number_format($maxWithdrawable, 2) . "). ₦" . number_format($collateralHold, 2) . " is held as active loan collateral."
                    ], 422);
                }
            }

            // Generate unique reference
            $ref = 'CSW-' . date('Ym') . '-' . strtoupper(substr(uniqid(), -5));

            $withdrawal = CoopSavingsWithdrawal::create([
                'withdrawal_reference'    => $ref,
                'staffId'                 => $staffId,
                'savings_setup_id'        => $savings->id,
                'withdrawal_type'         => $withdrawalType,
                'current_savings_balance' => $savingBalance,
                'active_loan_balance'     => $activeLoanBalance,
                'collateral_locked_amount'=> $collateralHold,
                'max_withdrawable_amount' => $maxWithdrawable,
                'requested_amount'        => $requestedAmount,
                'approved_amount'         => $requestedAmount, // Default to requested
                'reason'                  => trim($request->input('reason')),
                'bank_name'               => trim($request->input('bank_name')),
                'account_number'          => trim($request->input('account_number')),
                'account_name'            => trim($request->input('account_name')),
                'status'                  => 'pending',
                'created_by'              => $ctx['user']->id ?? null,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "Cooperative savings withdrawal application {$ref} submitted successfully. It will now be reviewed by HR Head.",
                'data' => $withdrawal,
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopSavingsWithdrawalApiController apply: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/coop-savings-withdrawal/requests
     * Paginated list of withdrawal applications with search, status filters, and summary stats.
     */
    public function requests(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $isPrivileged = $ctx['isSuperAdmin'] || !empty($ctx['isHrHead']) || $ctx['isAdminStaff'] || !empty($ctx['isFinanceHead']) || ($ctx['isFinanceStaff'] ?? false) || ($ctx['isAuditStaff'] ?? false);

            $page = max(1, (int)$request->input('page', 1));
            $perPage = min(100, max(5, (int)$request->input('per_page', 15)));
            $status = trim($request->input('status', 'all'));
            $withdrawalType = trim($request->input('withdrawal_type', 'all'));
            $search = trim($request->input('search', ''));

            $query = DB::table('coop_savings_withdrawals as w')
                ->join('tblper as p', 'p.ID', '=', 'w.staffId')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->select(
                    'w.*',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'p.fileNo',
                    'd.department',
                    DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as staff_name")
                );

            if (!$isPrivileged) {
                if ($ctx['employee']) {
                    $query->where('w.staffId', $ctx['employee']->ID);
                } else {
                    $query->where('w.staffId', 0);
                }
            }

            if ($status !== 'all') {
                if ($status === 'rejected') {
                    $query->whereIn('w.status', ['hr_rejected', 'audit_rejected', 'finance_rejected']);
                } elseif ($status === 'hr_approved') {
                    $query->whereIn('w.status', ['hr_approved', 'audit_approved']);
                } else {
                    $query->where('w.status', $status);
                }
            }

            if ($withdrawalType !== 'all') {
                $query->where('w.withdrawal_type', $withdrawalType);
            }

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('w.withdrawal_reference', 'like', "%{$search}%")
                      ->orWhere('p.ID', 'like', "%{$search}%")
                      ->orWhere('p.fileNo', 'like', "%{$search}%")
                      ->orWhere('p.surname', 'like', "%{$search}%")
                      ->orWhere('p.first_name', 'like', "%{$search}%")
                      ->orWhere('p.othernames', 'like', "%{$search}%");
                });
            }

            $total = $query->count();
            $records = $query->orderBy('w.id', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            // Summary stats
            $baseStatsQuery = DB::table('coop_savings_withdrawals as w');
            if (!$isPrivileged && $ctx['employee']) {
                $baseStatsQuery->where('w.staffId', $ctx['employee']->ID);
            }

            $stats = [
                'total_applications' => (clone $baseStatsQuery)->count(),
                'pending_count'      => (clone $baseStatsQuery)->where('status', 'pending')->count(),
                'hr_review_count'    => (clone $baseStatsQuery)->whereIn('status', ['hr_approved', 'audit_approved'])->count(),
                'audit_review_count' => (clone $baseStatsQuery)->where('status', 'audit_approved')->count(),
                'paid_count'         => (clone $baseStatsQuery)->where('status', 'paid')->count(),
                'rejected_count'     => (clone $baseStatsQuery)->whereIn('status', ['hr_rejected', 'audit_rejected', 'finance_rejected'])->count(),
                'total_paid_amount'  => (float)(clone $baseStatsQuery)->where('status', 'paid')->sum('approved_amount'),
            ];

            return response()->json([
                'status' => 'success',
                'data' => $records,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'stats' => $stats,
                'userRoleCtx' => [
                    'isSuperAdmin'  => $ctx['isSuperAdmin'],
                    'isAdminStaff'  => $ctx['isAdminStaff'],
                    'isAuditStaff'  => $ctx['isAuditStaff'] ?? false,
                    'isFinanceStaff'=> $ctx['isFinanceStaff'] ?? false,
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopSavingsWithdrawalApiController requests: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/coop-savings-withdrawal/request/{id}
     * Full request details with audit stamps.
     */
    public function show(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $w = DB::table('coop_savings_withdrawals as w')
                ->join('tblper as p', 'p.ID', '=', 'w.staffId')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->leftJoin('users as hr_u', 'hr_u.id', '=', 'w.hr_reviewed_by')
                ->leftJoin('users as au_u', 'au_u.id', '=', 'w.audit_reviewed_by')
                ->leftJoin('users as fi_u', 'fi_u.id', '=', 'w.finance_paid_by')
                ->where('w.id', $id)
                ->select(
                    'w.*',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'p.fileNo',
                    'd.department',
                    DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as staff_name"),
                    'hr_u.name as hr_reviewer_name',
                    'au_u.name as audit_reviewer_name',
                    'fi_u.name as finance_payer_name'
                )
                ->first();

            if (!$w) {
                return response()->json(['status' => 'error', 'message' => 'Withdrawal request not found.'], 404);
            }

            $isPrivileged = $ctx['isSuperAdmin'] || !empty($ctx['isHrHead']) || $ctx['isAdminStaff'] || !empty($ctx['isFinanceHead']) || ($ctx['isFinanceStaff'] ?? false) || ($ctx['isAuditStaff'] ?? false);
            if (!$isPrivileged && (!$ctx['employee'] || (int)$ctx['employee']->ID !== (int)$w->staffId)) {
                return response()->json(['status' => 'error', 'message' => 'Access denied.'], 403);
            }

            return response()->json(['status' => 'success', 'data' => $w]);
        } catch (\Throwable $th) {
            Log::error('CoopSavingsWithdrawalApiController show: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/coop-savings-withdrawal/hr-review/{id}
     * HR HEAD review step: Recommend / Approve or Reject.
     */
    public function hrReview(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            if (!$ctx['isSuperAdmin'] && empty($ctx['isHrHead'])) {
                return response()->json(['status' => 'error', 'message' => 'Access denied. Only Super Admin or staff with HR Head role can perform HR review.'], 403);
            }

            $withdrawal = CoopSavingsWithdrawal::find($id);
            if (!$withdrawal) {
                return response()->json(['status' => 'error', 'message' => 'Withdrawal record not found.'], 404);
            }

            if ($withdrawal->status !== 'pending' && !$ctx['isSuperAdmin']) {
                return response()->json(['status' => 'error', 'message' => "Application cannot be reviewed at current status ({$withdrawal->status})."], 422);
            }

            $action = $request->input('action'); // 'approve' or 'reject'
            $notes = trim($request->input('notes', ''));

            if ($action === 'approve') {
                $approvedAmount = $request->input('approved_amount') !== null 
                    ? round((float)$request->input('approved_amount'), 2)
                    : (float)$withdrawal->requested_amount;

                if ($approvedAmount <= 0) {
                    return response()->json(['status' => 'error', 'message' => 'Approved amount must be greater than zero.'], 422);
                }

                $withdrawal->update([
                    'status'          => 'hr_approved',
                    'approved_amount' => $approvedAmount,
                    'hr_reviewed_by'  => $ctx['user']->id ?? null,
                    'hr_reviewed_at'  => now(),
                    'hr_notes'        => $notes,
                    'rejection_reason'=> null,
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => "Application {$withdrawal->withdrawal_reference} approved by HR Head and forwarded to Finance for payment.",
                    'data' => $withdrawal,
                ]);
            } elseif ($action === 'reject') {
                if (empty($notes)) {
                    return response()->json(['status' => 'error', 'message' => 'Please provide a reason for rejection.'], 422);
                }

                $withdrawal->update([
                    'status'          => 'hr_rejected',
                    'hr_reviewed_by'  => $ctx['user']->id ?? null,
                    'hr_reviewed_at'  => now(),
                    'hr_notes'        => $notes,
                    'rejection_reason'=> $notes,
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => "Application {$withdrawal->withdrawal_reference} has been rejected by HR Head.",
                    'data' => $withdrawal,
                ]);
            }

            return response()->json(['status' => 'error', 'message' => 'Invalid action.'], 422);
        } catch (\Throwable $th) {
            Log::error('CoopSavingsWithdrawalApiController hrReview: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/coop-savings-withdrawal/audit-review/{id}
     * Audit review step: Verify compliance, check loan collateral, Approve or Reject.
     */
    public function auditReview(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            if (!$ctx['isSuperAdmin'] && !($ctx['isAuditStaff'] ?? false)) {
                return response()->json(['status' => 'error', 'message' => 'Access denied. Only Audit department or SuperAdmin can perform audit review.'], 403);
            }

            $withdrawal = CoopSavingsWithdrawal::find($id);
            if (!$withdrawal) {
                return response()->json(['status' => 'error', 'message' => 'Withdrawal record not found.'], 404);
            }

            if ($withdrawal->status !== 'hr_approved' && !$ctx['isSuperAdmin']) {
                return response()->json(['status' => 'error', 'message' => "Application must be approved by HR Head before Audit review."], 422);
            }

            $action = $request->input('action'); // 'approve' or 'reject'
            $notes = trim($request->input('notes', ''));

            if ($action === 'approve') {
                $withdrawal->update([
                    'status'            => 'audit_approved',
                    'audit_reviewed_by' => $ctx['user']->id ?? null,
                    'audit_reviewed_at' => now(),
                    'audit_notes'       => $notes,
                    'rejection_reason'  => null,
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => "Application {$withdrawal->withdrawal_reference} audited and passed. Ready for Finance payment.",
                    'data' => $withdrawal,
                ]);
            } elseif ($action === 'reject') {
                if (empty($notes)) {
                    return response()->json(['status' => 'error', 'message' => 'Please provide an audit rejection reason.'], 422);
                }

                $withdrawal->update([
                    'status'            => 'audit_rejected',
                    'audit_reviewed_by' => $ctx['user']->id ?? null,
                    'audit_reviewed_at' => now(),
                    'audit_notes'       => $notes,
                    'rejection_reason'  => $notes,
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => "Application {$withdrawal->withdrawal_reference} rejected by Audit.",
                    'data' => $withdrawal,
                ]);
            }

            return response()->json(['status' => 'error', 'message' => 'Invalid action.'], 422);
        } catch (\Throwable $th) {
            Log::error('CoopSavingsWithdrawalApiController auditReview: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/coop-savings-withdrawal/finance-payout/{id}
     * Finance Payment step: Record payment details, disburse funds, deduct savings balance.
     */
    public function financePayout(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            if (!$ctx['isSuperAdmin'] && empty($ctx['isFinanceHead'])) {
                return response()->json(['status' => 'error', 'message' => 'Access denied. Only Super Admin or staff with Finance Head role can process payout.'], 403);
            }

            $withdrawal = CoopSavingsWithdrawal::find($id);
            if (!$withdrawal) {
                return response()->json(['status' => 'error', 'message' => 'Withdrawal record not found.'], 404);
            }

            if (!in_array($withdrawal->status, ['hr_approved', 'audit_approved']) && !$ctx['isSuperAdmin']) {
                return response()->json(['status' => 'error', 'message' => "Application must be approved by HR Head before payment."], 422);
            }

            $action = $request->input('action', 'pay');
            $notes = trim($request->input('notes', ''));

            if ($action === 'reject') {
                if (empty($notes)) {
                    return response()->json(['status' => 'error', 'message' => 'Please provide a reason for Finance rejection.'], 422);
                }

                $withdrawal->update([
                    'status'          => 'finance_rejected',
                    'finance_paid_by' => $ctx['user']->id ?? null,
                    'finance_paid_at' => now(),
                    'finance_notes'   => $notes,
                    'rejection_reason'=> $notes,
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => "Application {$withdrawal->withdrawal_reference} rejected by Finance.",
                    'data' => $withdrawal,
                ]);
            }

            // Pay action
            $validator = Validator::make($request->all(), [
                'payment_method'    => 'required|string|in:bank_transfer,cheque,cash',
                'payment_reference' => 'nullable|string|max:100',
                'payment_date'      => 'required|date',
                'proof_file'        => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
            }

            // Handle optional proof of payment upload
            $proofUrl = null;
            if ($request->hasFile('proof_file')) {
                $file = $request->file('proof_file');
                $filename = 'csw_proof_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $destinationPath = public_path('uploads/coop_withdrawals');
                if (!file_exists($destinationPath)) {
                    @mkdir($destinationPath, 0777, true);
                }
                $file->move($destinationPath, $filename);
                $proofUrl = asset('uploads/coop_withdrawals/' . $filename);
            }

            // Database transaction with balance lock
            $result = DB::transaction(function () use ($withdrawal, $request, $ctx, $proofUrl, $notes) {
                $savingsSetup = DB::table('coop_savings_setups')
                    ->where('id', $withdrawal->savings_setup_id)
                    ->lockForUpdate()
                    ->first();

                if (!$savingsSetup) {
                    throw new \Exception('Cooperative savings setup not found for this staff member.');
                }

                $payoutAmount = (float)($withdrawal->approved_amount ?? $withdrawal->requested_amount);
                $balanceBefore = (float)$savingsSetup->saving_balance;

                if ($payoutAmount > $balanceBefore) {
                    throw new \Exception("Insufficient balance. Current savings balance is ₦" . number_format($balanceBefore, 2) . ", cannot disburse ₦" . number_format($payoutAmount, 2) . ".");
                }

                $balanceAfter = max(0.00, $balanceBefore - $payoutAmount);

                // Update savings setup balance
                $updateData = [
                    'saving_balance' => $balanceAfter,
                    'updated_at'     => now(),
                ];

                // If full withdrawal, deactivate savings setup
                if ($withdrawal->withdrawal_type === 'full') {
                    $updateData['is_active'] = 0;
                }

                DB::table('coop_savings_setups')
                    ->where('id', $savingsSetup->id)
                    ->update($updateData);

                // Update withdrawal record
                $withdrawal->update([
                    'status'                => 'paid',
                    'balance_before_payout' => $balanceBefore,
                    'balance_after_payout'  => $balanceAfter,
                    'finance_paid_by'       => $ctx['user']->id ?? null,
                    'finance_paid_at'       => now(),
                    'payment_method'        => $request->input('payment_method'),
                    'payment_reference'     => trim($request->input('payment_reference', '')),
                    'payment_date'          => $request->input('payment_date'),
                    'payment_proof'         => $proofUrl ?? $withdrawal->payment_proof,
                    'finance_notes'         => $notes,
                    'rejection_reason'      => null,
                ]);

                return $withdrawal;
            });

            return response()->json([
                'status' => 'success',
                'message' => "Payment of ₦" . number_format($result->approved_amount, 2) . " recorded successfully. Cooperative savings balance has been debited.",
                'data' => $result,
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopSavingsWithdrawalApiController financePayout: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/coop-savings-withdrawal/voucher/{id}
     * Official printable payment voucher & settlement receipt.
     */
    public function voucher(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $w = DB::table('coop_savings_withdrawals as w')
                ->join('tblper as p', 'p.ID', '=', 'w.staffId')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->leftJoin('users as hr_u', 'hr_u.id', '=', 'w.hr_reviewed_by')
                ->leftJoin('users as au_u', 'au_u.id', '=', 'w.audit_reviewed_by')
                ->leftJoin('users as fi_u', 'fi_u.id', '=', 'w.finance_paid_by')
                ->where('w.id', $id)
                ->select(
                    'w.*',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'p.fileNo',
                    'd.department',
                    DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as staff_name"),
                    'hr_u.name as hr_reviewer_name',
                    'au_u.name as audit_reviewer_name',
                    'fi_u.name as finance_payer_name'
                )
                ->first();

            if (!$w) {
                return response()->json(['status' => 'error', 'message' => 'Withdrawal record not found.'], 404);
            }

            $isPrivileged = $ctx['isSuperAdmin'] || !empty($ctx['isHrHead']) || $ctx['isAdminStaff'] || !empty($ctx['isFinanceHead']) || ($ctx['isFinanceStaff'] ?? false) || ($ctx['isAuditStaff'] ?? false);
            if (!$isPrivileged && (!$ctx['employee'] || (int)$ctx['employee']->ID !== (int)$w->staffId)) {
                return response()->json(['status' => 'error', 'message' => 'Access denied.'], 403);
            }

            return response()->json([
                'status' => 'success',
                'data' => $w,
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopSavingsWithdrawalApiController voucher: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/nextjs/payroll/coop-savings-withdrawal/{id}
     * Cancel/delete a pending withdrawal application.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $withdrawal = CoopSavingsWithdrawal::find($id);
            if (!$withdrawal) {
                return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
            }

            $isOwner = $ctx['employee'] && (int)$ctx['employee']->ID === (int)$withdrawal->staffId;
            $canDelete = $ctx['isSuperAdmin'] || ($isOwner && $withdrawal->status === 'pending');

            if (!$canDelete) {
                return response()->json(['status' => 'error', 'message' => 'Only pending applications can be cancelled by staff, or contact SuperAdmin.'], 403);
            }

            if ($withdrawal->status === 'paid') {
                return response()->json(['status' => 'error', 'message' => 'Completed / paid withdrawals cannot be deleted.'], 422);
            }

            $ref = $withdrawal->withdrawal_reference;
            $withdrawal->delete();

            return response()->json([
                'status' => 'success',
                'message' => "Withdrawal application {$ref} successfully cancelled."
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopSavingsWithdrawalApiController destroy: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
