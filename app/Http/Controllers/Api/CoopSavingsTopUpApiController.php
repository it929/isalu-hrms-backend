<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\FileUploadHelper;

class CoopSavingsTopUpApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * GET /api/nextjs/payroll/coop-savings-top-up/staff-list
     * Returns staff members. For Admins/Finance, all active staff with their current savings balance.
     * For regular staff, returns only their own staff profile.
     */
    public function staffList(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $isPrivileged = $ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || ($ctx['isFinanceStaff'] ?? false);
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
                return [
                    'id'               => $row->staffId,
                    'staffId'          => $row->staffId,
                    'fileNo'           => $row->fileNo ?? '',
                    'name'             => trim($row->name),
                    'department'       => $row->department ?? '',
                    'gender'           => strtolower(trim($row->gender ?? '')),
                    'saving_balance'   => (float)$row->saving_balance,
                    'monthly_saving'   => (float)$row->monthly_saving,
                    'savings_setup_id' => $row->savings_setup_id ? (int)$row->savings_setup_id : null,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $staff,
                'isPrivileged' => $isPrivileged,
                'isSuperAdmin' => $ctx['isSuperAdmin'],
                'isAdminStaff' => $ctx['isAdminStaff'],
                'isFinanceStaff' => $ctx['isFinanceStaff'] ?? false,
                'currentEmployee' => $ctx['employee'] ? [
                    'id' => $ctx['employee']->ID,
                    'fileNo' => $ctx['employee']->fileNo ?? '',
                    'name' => trim("{$ctx['employee']->surname} {$ctx['employee']->first_name} {$ctx['employee']->othernames}"),
                ] : null,
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopSavingsTopUpApiController staffList: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/coop-savings-top-up/staff-balance/{staffId}
     * Returns cooperative savings balance and top-up statistics for a specific staff member.
     */
    public function staffBalance(Request $request, $staffId)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $isPrivileged = $ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || ($ctx['isFinanceStaff'] ?? false) || ($ctx['isAuditStaff'] ?? false);

            // Non-privileged users can only query their own balance
            if (!$isPrivileged) {
                if (!$ctx['employee'] || $ctx['employee']->ID != $staffId) {
                    return response()->json(['status' => 'error', 'message' => 'Access denied to this staff profile.'], 403);
                }
            }

            $staff = DB::table('tblper as p')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->where('p.ID', $staffId)
                ->select(
                    'p.ID as staffId',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'p.gender',
                    'd.department',
                    DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as name")
                )
                ->first();

            if (!$staff) {
                return response()->json(['status' => 'error', 'message' => 'Staff member not found.'], 404);
            }

            // Get active savings setup or most recent setup
            $savings = DB::table('coop_savings_setups')
                ->where('staffId', $staffId)
                ->orderBy('is_active', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            // Aggregated statistics for this staff member
            $totalToppedUp = (float) DB::table('coop_savings_top_ups')
                ->where('staffId', $staffId)
                ->sum('amount');

            $topUpCount = (int) DB::table('coop_savings_top_ups')
                ->where('staffId', $staffId)
                ->count();

            $lastTopUp = DB::table('coop_savings_top_ups')
                ->where('staffId', $staffId)
                ->orderBy('id', 'desc')
                ->first();

            return response()->json([
                'status' => 'success',
                'staff' => [
                    'staffId'    => $staff->staffId,
                    'fileNo'     => $staff->fileNo ?? '',
                    'name'       => trim($staff->name),
                    'department' => $staff->department ?? 'General',
                    'gender'     => strtolower(trim($staff->gender ?? '')),
                ],
                'savings' => $savings ? [
                    'id'             => $savings->id,
                    'saving_balance' => (float)$savings->saving_balance,
                    'monthly_saving' => (float)$savings->monthly_saving,
                    'start_month'    => $savings->start_month,
                    'is_active'      => (int)$savings->is_active,
                ] : [
                    'id'             => null,
                    'saving_balance' => 0.00,
                    'monthly_saving' => 0.00,
                    'start_month'    => date('Y-m'),
                    'is_active'      => 0,
                ],
                'stats' => [
                    'total_topped_up' => $totalToppedUp,
                    'top_up_count'    => $topUpCount,
                    'last_top_up'     => $lastTopUp ? [
                        'amount'           => (float)$lastTopUp->amount,
                        'payment_date'     => $lastTopUp->payment_date,
                        'top_up_reference' => $lastTopUp->top_up_reference,
                        'payment_method'   => $lastTopUp->payment_method,
                    ] : null,
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopSavingsTopUpApiController staffBalance: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/coop-savings-top-up
     * Record a new cooperative savings top-up for a staff member.
     * Authorized: SuperAdmin, AdminStaff, FinanceStaff.
     */
    public function store(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $isPrivileged = $ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || ($ctx['isFinanceStaff'] ?? false);
            if (!$isPrivileged) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access denied: Only Administrators and Finance Officers are authorized to record cooperative savings top-ups.'
                ], 403);
            }

            $validated = $request->validate([
                'staffId'           => 'required|integer|exists:tblper,ID',
                'amount'            => 'required|numeric|min:1',
                'payment_method'    => 'required|string|in:bank_transfer,direct_deposit,cash,cheque,salary_deduction,other',
                'payment_reference' => 'nullable|string|max:100',
                'payment_date'      => 'required|date',
                'proof_of_payment'  => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
                'notes'             => 'nullable|string|max:1000',
            ]);

            $staffId          = (int)$validated['staffId'];
            $amount           = (float)$validated['amount'];
            $paymentMethod    = $validated['payment_method'];
            $paymentReference = $validated['payment_reference'] ?? null;
            $paymentDate      = $validated['payment_date'];
            $notes            = $validated['notes'] ?? null;

            // Handle Proof of Payment file upload
            $proofOfPaymentPath = null;
            if ($request->hasFile('proof_of_payment')) {
                $file = $request->file('proof_of_payment');
                $proofOfPaymentPath = FileUploadHelper::upload($file, 'coop_topups');
            }

            // Generate unique top-up reference
            $yearMonth = date('Ym', strtotime($paymentDate));
            $attempts = 0;
            do {
                $randomCode = strtoupper(substr(uniqid(), -5));
                $topUpReference = "CST-{$yearMonth}-{$randomCode}";
                $exists = DB::table('coop_savings_top_ups')->where('top_up_reference', $topUpReference)->exists();
                $attempts++;
            } while ($exists && $attempts < 10);

            DB::beginTransaction();

            // Find or lock cooperative savings setup
            $setup = DB::table('coop_savings_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->lockForUpdate()
                ->first();

            // If no active setup, look for any existing setup
            if (!$setup) {
                $setup = DB::table('coop_savings_setups')
                    ->where('staffId', $staffId)
                    ->orderBy('id', 'desc')
                    ->lockForUpdate()
                    ->first();
            }

            if ($setup) {
                $balanceBefore = (float)$setup->saving_balance;
                $balanceAfter = round($balanceBefore + $amount, 2);
                $setupId = $setup->id;

                DB::table('coop_savings_setups')
                    ->where('id', $setupId)
                    ->update([
                        'saving_balance' => $balanceAfter,
                        'is_active'      => 1,
                        'updated_at'     => now(),
                    ]);
            } else {
                $balanceBefore = 0.00;
                $balanceAfter = round($amount, 2);

                $setupId = DB::table('coop_savings_setups')->insertGetId([
                    'staffId'        => $staffId,
                    'monthly_saving' => 0.00,
                    'saving_balance' => $balanceAfter,
                    'start_month'    => date('Y-m', strtotime($paymentDate)),
                    'is_active'      => 1,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }

            // Insert top-up transaction record
            $topUpId = DB::table('coop_savings_top_ups')->insertGetId([
                'staffId'           => $staffId,
                'savings_setup_id'  => $setupId,
                'top_up_reference'  => $topUpReference,
                'amount'            => $amount,
                'balance_before'    => $balanceBefore,
                'balance_after'     => $balanceAfter,
                'payment_method'    => $paymentMethod,
                'payment_reference' => $paymentReference,
                'payment_date'      => $paymentDate,
                'proof_of_payment'  => $proofOfPaymentPath,
                'notes'             => $notes,
                'processed_by'      => $ctx['userId'] ?? null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Cooperative savings balance successfully topped up with ' . number_format($amount, 2) . ' Naira.',
                'data'    => [
                    'id'               => $topUpId,
                    'top_up_reference' => $topUpReference,
                    'amount'           => $amount,
                    'balance_before'   => $balanceBefore,
                    'balance_after'    => $balanceAfter,
                    'payment_method'   => $paymentMethod,
                    'payment_date'     => $paymentDate,
                    'proof_of_payment' => $proofOfPaymentPath,
                ]
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('CoopSavingsTopUpApiController store: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/coop-savings-top-up/history
     * Retrieve paginated history of top-up transactions with staff and processor info.
     */
    public function history(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $isPrivileged = $ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || ($ctx['isFinanceStaff'] ?? false) || ($ctx['isAuditStaff'] ?? false);

            $staffId       = $request->input('staffId');
            $search        = trim($request->input('search', ''));
            $paymentMethod = $request->input('payment_method');
            $dateFrom      = $request->input('date_from');
            $dateTo        = $request->input('date_to');
            $perPage       = (int) $request->input('perPage', 15);
            $page          = (int) $request->input('page', 1);

            // If non-privileged regular staff, force to own ID
            if (!$isPrivileged) {
                if ($ctx['employee']) {
                    $staffId = $ctx['employee']->ID;
                } else {
                    $staffId = -1; // No access
                }
            }

            $query = DB::table('coop_savings_top_ups as t')
                ->join('tblper as p', 'p.ID', '=', 't.staffId')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->leftJoin('users as u', 'u.id', '=', 't.processed_by')
                ->leftJoin('tblper as admin', 'admin.UserID', '=', 'u.id')
                ->select(
                    't.*',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'd.department',
                    DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as staff_name"),
                    DB::raw("COALESCE(CONCAT(admin.surname, ' ', admin.first_name), u.name, 'System') as processed_by_name")
                );

            if ($staffId && $staffId > 0) {
                $query->where('t.staffId', $staffId);
            }

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('t.top_up_reference', 'like', "%{$search}%")
                      ->orWhere('t.payment_reference', 'like', "%{$search}%")
                      ->orWhere('t.staffId', 'like', "%{$search}%")
                      ->orWhere('p.fileNo', 'like', "%{$search}%")
                      ->orWhere('p.surname', 'like', "%{$search}%")
                      ->orWhere('p.first_name', 'like', "%{$search}%")
                      ->orWhere('t.notes', 'like', "%{$search}%");
                });
            }

            if ($paymentMethod && $paymentMethod !== 'all') {
                $query->where('t.payment_method', $paymentMethod);
            }

            if ($dateFrom) {
                $query->where('t.payment_date', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->where('t.payment_date', '<=', $dateTo);
            }

            // Summary metrics before pagination
            $totalAmount = (float) (clone $query)->sum('t.amount');
            $totalCount  = (int) (clone $query)->count();

            // Order and paginate
            $records = $query->orderBy('t.id', 'desc')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get()
                ->map(function ($row) {
                    $row->amount         = (float)$row->amount;
                    $row->balance_before = (float)$row->balance_before;
                    $row->balance_after  = (float)$row->balance_after;
                    $row->staff_name     = trim($row->staff_name);
                    return $row;
                });

            return response()->json([
                'status'       => 'success',
                'data'         => $records,
                'total'        => $totalCount,
                'page'         => $page,
                'perPage'      => $perPage,
                'lastPage'     => (int) ceil($totalCount / $perPage),
                'total_amount' => $totalAmount,
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopSavingsTopUpApiController history: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/coop-savings-top-up/receipt/{id}
     * Returns detailed receipt information for printing or modal display.
     */
    public function receipt(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $record = DB::table('coop_savings_top_ups as t')
                ->join('tblper as p', 'p.ID', '=', 't.staffId')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->leftJoin('users as u', 'u.id', '=', 't.processed_by')
                ->leftJoin('tblper as admin', 'admin.UserID', '=', 'u.id')
                ->where('t.id', $id)
                ->select(
                    't.*',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'd.department',
                    DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as staff_name"),
                    DB::raw("COALESCE(CONCAT(admin.surname, ' ', admin.first_name), u.name, 'Finance Officer') as processed_by_name")
                )
                ->first();

            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Top-up record not found.'], 404);
            }

            $isPrivileged = $ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || ($ctx['isFinanceStaff'] ?? false) || ($ctx['isAuditStaff'] ?? false);
            if (!$isPrivileged && ($ctx['employee'] && $ctx['employee']->ID != $record->staffId)) {
                return response()->json(['status' => 'error', 'message' => 'Access denied.'], 403);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id'                => $record->id,
                    'top_up_reference'  => $record->top_up_reference,
                    'staff_name'        => trim($record->staff_name),
                    'staffId'           => $record->staffId,
                    'fileNo'            => $record->fileNo ?? 'N/A',
                    'department'        => $record->department ?? 'General',
                    'amount'            => (float)$record->amount,
                    'balance_before'    => (float)$record->balance_before,
                    'balance_after'     => (float)$record->balance_after,
                    'payment_method'    => $record->payment_method,
                    'payment_reference' => $record->payment_reference ?? '—',
                    'payment_date'      => $record->payment_date,
                    'proof_of_payment'  => $record->proof_of_payment,
                    'notes'             => $record->notes,
                    'processed_by_name' => $record->processed_by_name,
                    'created_at'        => $record->created_at,
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopSavingsTopUpApiController receipt: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/nextjs/payroll/coop-savings-top-up/{id}
     * Reverse a cooperative top-up transaction. Restricted to Super Administrators.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || !$ctx['isSuperAdmin']) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: Only Super Administrators can reverse top-up transactions.'], 403);
            }

            DB::beginTransaction();

            $topUp = DB::table('coop_savings_top_ups')->where('id', $id)->lockForUpdate()->first();
            if (!$topUp) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Top-up transaction not found.'], 404);
            }

            // Deduct the top-up amount from the savings setup
            if ($topUp->savings_setup_id) {
                $setup = DB::table('coop_savings_setups')->where('id', $topUp->savings_setup_id)->lockForUpdate()->first();
                if ($setup) {
                    $newBalance = max(0.00, round((float)$setup->saving_balance - (float)$topUp->amount, 2));
                    DB::table('coop_savings_setups')->where('id', $setup->id)->update([
                        'saving_balance' => $newBalance,
                        'updated_at'     => now(),
                    ]);
                }
            }

            DB::table('coop_savings_top_ups')->where('id', $id)->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Top-up transaction {$topUp->top_up_reference} successfully reversed."
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('CoopSavingsTopUpApiController destroy: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
