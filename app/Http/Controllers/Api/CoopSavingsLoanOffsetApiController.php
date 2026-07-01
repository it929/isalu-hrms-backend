<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CoopSavingsLoanOffsetApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * Resolve the current user context from the X-User-Id header.
     */
    

    /**
     * GET /api/nextjs/payroll/coop-savings-loan-offset/staff-list
     * Returns all staff that have both an active coop savings and coop loan setup.
     */
    public function staffList(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                return response()->json(['status' => 'error', 'message' => 'Access denied.'], 403);
            }

            $search = trim($request->input('search', ''));

            $query = DB::table('tblper as p')
                ->join('coop_loan_deduction_setups as clds', 'clds.staffId', '=', 'p.ID')
                ->leftJoin('coop_savings_setups as css', function($join) {
                    $join->on('css.staffId', '=', 'p.ID')
                         ->where('css.is_active', '=', 1);
                })
                ->where('clds.is_active', 1)
                ->where('clds.balance_remaining', '>', 0)
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->select(
                    'p.ID as staffId',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'd.department',
                    DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as name"),
                    DB::raw('COALESCE(MAX(css.saving_balance), 0) as saving_balance'),
                    DB::raw('MAX(clds.balance_remaining) as loan_balance')
                )
                ->groupBy('p.ID', 'p.fileNo', 'p.surname', 'p.first_name', 'p.othernames', 'd.department');

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('p.fileNo', 'like', "%{$search}%")
                      ->orWhere('p.surname', 'like', "%{$search}%")
                      ->orWhere('p.first_name', 'like', "%{$search}%")
                      ->orWhere('p.othernames', 'like', "%{$search}%");
                });
            }

            $staff = $query->orderBy('p.surname')->get();

            return response()->json(['status' => 'success', 'data' => $staff]);
        } catch (\Throwable $th) {
            Log::error('CoopSavingsLoanOffsetApiController staffList: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/coop-savings-loan-offset/staff-balances
     * Returns the active savings + loan balances for a given staffId.
     */
    public function staffBalances(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                return response()->json(['status' => 'error', 'message' => 'Access denied.'], 403);
            }

            $staffId = $request->input('staffId');
            if (!$staffId) {
                return response()->json(['status' => 'error', 'message' => 'staffId is required.'], 422);
            }

            $savings = DB::table('coop_savings_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->where('saving_balance', '>', 0)
                ->orderBy('saving_balance', 'desc')
                ->first();

            // Fallback: any active savings record (even if balance is 0)
            if (!$savings) {
                $savings = DB::table('coop_savings_setups')
                    ->where('staffId', $staffId)
                    ->where('is_active', 1)
                    ->orderBy('id', 'desc')
                    ->first();
            }

            // Prefer the active loan with the highest remaining balance
            $loan = DB::table('coop_loan_deduction_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->where('balance_remaining', '>', 0)
                ->orderBy('balance_remaining', 'desc')
                ->first();

            // Fallback: most recently created active loan
            if (!$loan) {
                $loan = DB::table('coop_loan_deduction_setups')
                    ->where('staffId', $staffId)
                    ->where('is_active', 1)
                    ->orderBy('id', 'desc')
                    ->first();
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
                    'd.department',
                    DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as name")
                )
                ->first();

            if (!$staff) {
                return response()->json(['status' => 'error', 'message' => 'Staff not found.'], 404);
            }

            return response()->json([
                'status' => 'success',
                'staff'  => $staff,
                'savings' => $savings ? [
                    'id'              => $savings->id,
                    'saving_balance'  => (float) $savings->saving_balance,
                    'monthly_saving'  => (float) $savings->monthly_saving,
                    'is_active'       => (int) $savings->is_active,
                ] : null,
                'loan' => $loan ? [
                    'id'               => $loan->id,
                    'loan_amount'      => (float) $loan->loan_amount,
                    'balance_remaining'=> (float) $loan->balance_remaining,
                    'monthly_deduction'=> (float) $loan->monthly_deduction,
                    'end_month'        => $loan->end_month,
                    'is_active'        => (int) $loan->is_active,
                ] : null,
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopSavingsLoanOffsetApiController staffBalances: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/coop-savings-loan-offset
     * Perform the offset transaction.
     */
    public function store(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: Only administrators can process offsets.'], 403);
            }

            $validated = $request->validate([
                'staffId'          => 'required|integer|exists:tblper,ID',
                'offset_type'      => 'required|string|in:savings,bank',
                'savings_setup_id' => 'required_if:offset_type,savings|nullable|integer|exists:coop_savings_setups,id',
                'loan_setup_id'    => 'required|integer|exists:coop_loan_deduction_setups,id',
                'offset_amount'    => 'required|numeric|min:0.01',
                'notes'            => 'nullable|string|max:500',
                'proof_of_payment' => 'required_if:offset_type,bank|nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            ]);

            $staffId       = (int) $validated['staffId'];
            $offsetType    = $validated['offset_type'];
            $savingsId     = $validated['savings_setup_id'] ? (int) $validated['savings_setup_id'] : null;
            $loanId        = (int) $validated['loan_setup_id'];
            $offsetAmount  = (float) $validated['offset_amount'];
            $notes         = $validated['notes'] ?? null;

            // Handle Proof of Payment file upload
            $proofOfPaymentPath = null;
            if ($offsetType === 'bank' && $request->hasFile('proof_of_payment')) {
                $file = $request->file('proof_of_payment');
                $proofOfPaymentPath = \App\Helpers\FileUploadHelper::upload($file, 'coop_offsets');
            }

            DB::beginTransaction();

            $loan = DB::table('coop_loan_deduction_setups')
                ->where('id', $loanId)
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->lockForUpdate()
                ->first();

            if (!$loan) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Active coop loan setup not found.'], 404);
            }

            $loanBalance = (float) $loan->balance_remaining;

            if ($offsetAmount > $loanBalance) {
                DB::rollBack();
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Offset amount (' . number_format($offsetAmount, 2) . ') exceeds the outstanding loan balance (' . number_format($loanBalance, 2) . ').'
                ], 422);
            }

            $savingsBalance = null;
            $newSavingsBalance = null;

            if ($offsetType === 'savings') {
                if (!$savingsId) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => 'Savings setup ID is required for coop savings offset.'], 422);
                }

                $savings = DB::table('coop_savings_setups')
                    ->where('id', $savingsId)
                    ->where('staffId', $staffId)
                    ->where('is_active', 1)
                    ->lockForUpdate()
                    ->first();

                if (!$savings) {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => 'Active coop savings setup not found.'], 404);
                }

                $savingsBalance = (float) $savings->saving_balance;

                if ($offsetAmount > $savingsBalance) {
                    DB::rollBack();
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Offset amount (' . number_format($offsetAmount, 2) . ') exceeds the available savings balance (' . number_format($savingsBalance, 2) . ').'
                    ], 422);
                }

                $newSavingsBalance = round($savingsBalance - $offsetAmount, 2);

                // Update savings balance
                DB::table('coop_savings_setups')
                    ->where('id', $savingsId)
                    ->update([
                        'saving_balance' => $newSavingsBalance,
                        'updated_at'     => now(),
                    ]);
            } else {
                // Bank offset: read savings details if savingsId is provided, but don't deduct
                if ($savingsId) {
                    $savings = DB::table('coop_savings_setups')
                        ->where('id', $savingsId)
                        ->first();
                    if ($savings) {
                        $savingsBalance = (float) $savings->saving_balance;
                        $newSavingsBalance = $savingsBalance;
                    }
                }
            }

            $newLoanBalance = round($loanBalance - $offsetAmount, 2);

            // Update loan balance; deactivate if fully cleared
            $loanUpdate = ['balance_remaining' => $newLoanBalance, 'updated_at' => now()];
            if ($newLoanBalance <= 0) {
                $loanUpdate['is_active'] = 0;
            }
            DB::table('coop_loan_deduction_setups')
                ->where('id', $loanId)
                ->update($loanUpdate);

            // Write audit record
            DB::table('coop_savings_loan_offsets')->insert([
                'staffId'               => $staffId,
                'savings_setup_id'      => $savingsId,
                'loan_setup_id'         => $loanId,
                'offset_type'           => $offsetType,
                'proof_of_payment'      => $proofOfPaymentPath,
                'offset_amount'         => $offsetAmount,
                'savings_balance_before'=> $savingsBalance,
                'savings_balance_after' => $newSavingsBalance,
                'loan_balance_before'   => $loanBalance,
                'loan_balance_after'    => $newLoanBalance,
                'processed_by'          => $ctx['userId'],
                'notes'                 => $notes,
                'created_at'            => now(),
            ]);

            DB::commit();

            $loanCleared = $newLoanBalance <= 0;

            return response()->json([
                'status'  => 'success',
                'message' => $loanCleared
                    ? 'Offset processed successfully. The coop loan has been fully cleared!'
                    : 'Offset processed successfully.',
                'loan_cleared'          => $loanCleared,
                'savings_balance_after' => $newSavingsBalance,
                'loan_balance_after'    => $newLoanBalance,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('CoopSavingsLoanOffsetApiController store: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/coop-savings-loan-offset/history
     * Returns paginated offset history, optionally filtered by staffId.
     */
    public function history(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                return response()->json(['status' => 'error', 'message' => 'Access denied.'], 403);
            }

            $staffId = $request->input('staffId');
            $perPage = (int) $request->input('perPage', 20);
            $page    = (int) $request->input('page', 1);

            $query = DB::table('coop_savings_loan_offsets as o')
                ->join('tblper as p', 'p.ID', '=', 'o.staffId')
                ->leftJoin('tblper as admin', 'admin.UserID', '=', 'o.processed_by')
                ->select(
                    'o.*',
                    DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames,'')) as staff_name"),
                    'p.fileNo',
                    DB::raw("CONCAT(COALESCE(admin.surname,''), ' ', COALESCE(admin.first_name,'')) as processed_by_name")
                )
                ->orderBy('o.created_at', 'desc');

            if ($staffId) {
                $query->where('o.staffId', $staffId);
            }

            $total  = $query->count();
            $offset = ($page - 1) * $perPage;
            $data   = $query->skip($offset)->take($perPage)->get();

            return response()->json([
                'status'   => 'success',
                'data'     => $data,
                'total'    => $total,
                'page'     => $page,
                'lastPage' => (int) ceil($total / $perPage),
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopSavingsLoanOffsetApiController history: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
