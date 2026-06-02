<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CoopLoanDeductionSetupApiController extends Controller
{
    /**
     * Resolve the current user context from the X-User-Id header.
     */
    private function getUserContext(Request $request): ?array
    {
        $userId = $request->header('X-User-Id');

        if (!$userId) {
            return null;
        }

        $isSuperAdmin = DB::table('assign_user_role')
            ->where('userID', $userId)
            ->where('roleID', 1) // Super Admin
            ->exists();

        $adminStaff = DB::table('assign_user_role')
            ->where('userID', $userId)
            ->where('roleID', 48) // Admin Staff
            ->exists();

        $isAuditStaff = DB::table('assign_user_role')
            ->where('userID', $userId)
            ->whereIn('roleID', [34, 35]) // Audit Head, Audit Staff
            ->exists();

        $employee = DB::table('tblper')->where('UserID', $userId)->first();

        return [
            'userId'       => $userId,
            'isSuperAdmin' => $isSuperAdmin,
            'isAdminStaff' => $adminStaff,
            'isAuditStaff' => $isAuditStaff,
            'employee'     => $employee,
            'isHod'        => $employee && $employee->is_hod == 1,
        ];
    }

    /**
     * GET /api/nextjs/payroll/coop-loan-deduction-setups
     * Fetch existing coop loan deduction setups.
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $search = trim($request->input('search', ''));
            $query = DB::table('coop_loan_deduction_setups as clds')
                ->join('tblper as p', 'p.ID', '=', 'clds.staffId')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->select(
                    'clds.*',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'd.department'
                );

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('p.fileNo', 'like', "%{$search}%")
                      ->orWhere('p.surname', 'like', "%{$search}%")
                      ->orWhere('p.first_name', 'like', "%{$search}%")
                      ->orWhere('p.othernames', 'like', "%{$search}%");
                });
            }

            $employee = $ctx['employee'];

            if ($ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || $ctx['isAuditStaff']) {
                // Admins/Audit see all setups
            } elseif ($employee && $employee->is_hod == 1) {
                // HOD sees department staff setups
                $query->where('p.departmentID', $employee->departmentID);
            } elseif ($employee) {
                // Regular staff see only their own
                $query->where('clds.staffId', $employee->ID);
            } else {
                $query->where('clds.id', 0); // fallback empty
            }

            $records = $query->orderBy('clds.id', 'desc')->get()->map(function ($row) {
                $row->name = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                $row->is_active = (int) $row->is_active;
                return $row;
            });

            return response()->json([
                'status' => 'success',
                'data' => $records,
                'isSuperAdmin' => $ctx['isSuperAdmin'],
                'isHod' => $ctx['isHod'],
                'isAdminStaff' => $ctx['isAdminStaff'],
                'isAuditStaff' => $ctx['isAuditStaff'],
                'employee' => $employee,
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopLoanDeductionSetupApiController index: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/coop-loan-deduction-setups
     * Save or update a cooperative loan deduction setup.
     */
    public function store(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $validated = $request->validate([
                'id' => 'nullable|integer',
                'staffId' => 'required|integer|exists:tblper,ID',
                'loan_amount' => 'required|numeric|min:0',
                'interest_rate' => 'required|numeric|min:0|max:100',
                'duration_months' => 'required|integer|min:1',
                'monthly_deduction' => 'required|numeric|min:0',
                'balance_remaining' => 'nullable|numeric|min:0',
                'start_month' => 'required|string|regex:/^\d{4}-\d{2}$/',
                'end_month' => 'required|string|regex:/^\d{4}-\d{2}$/',
                'is_active' => 'nullable|integer|in:0,1',
            ]);

            // Restriction check: Only Admins can modify settings
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access denied: Only administrators can configure cooperative loan deduction setups.'
                ], 403);
            }

            $id = $validated['id'] ?? null;
            $loanAmount = (float) $validated['loan_amount'];
            $interestRate = (float) $validated['interest_rate'];
            $totalRepayment = $loanAmount * (1 + $interestRate / 100);
            $balanceRemaining = (isset($validated['balance_remaining']) && $validated['balance_remaining'] !== null) ? (float) $validated['balance_remaining'] : $totalRepayment;

            $data = [
                'staffId' => $validated['staffId'],
                'loan_amount' => $loanAmount,
                'interest_rate' => (float) $validated['interest_rate'],
                'duration_months' => (int) $validated['duration_months'],
                'monthly_deduction' => (float) $validated['monthly_deduction'],
                'balance_remaining' => $balanceRemaining,
                'start_month' => $validated['start_month'],
                'end_month' => $validated['end_month'],
                'is_active' => $validated['is_active'] ?? 1,
                'updated_at' => now(),
            ];

            if ($id) {
                $exists = DB::table('coop_loan_deduction_setups')->where('id', $id)->exists();
                if (!$exists) {
                    return response()->json(['status' => 'error', 'message' => 'Deduction setup not found.'], 404);
                }
                DB::table('coop_loan_deduction_setups')->where('id', $id)->update($data);
                $message = 'Cooperative loan deduction setup updated successfully.';
            } else {
                $data['created_at'] = now();
                DB::table('coop_loan_deduction_setups')->insert($data);
                $message = 'Cooperative loan deduction setup created successfully.';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopLoanDeductionSetupApiController store: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/coop-loan-deduction-setups/toggle/{id}
     * Toggle activation status of cooperative loan deduction setup.
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            // Only Admins can modify settings
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access denied: Only administrators can toggle setup status.'
                ], 403);
            }

            $setup = DB::table('coop_loan_deduction_setups')->where('id', $id)->first();
            if (!$setup) {
                return response()->json(['status' => 'error', 'message' => 'Setup not found.'], 404);
            }

            $newStatus = $setup->is_active == 1 ? 0 : 1;

            DB::table('coop_loan_deduction_setups')->where('id', $id)->update([
                'is_active' => $newStatus,
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => $newStatus == 1 ? 'Deduction setup activated successfully.' : 'Deduction setup deactivated successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopLoanDeductionSetupApiController toggleStatus: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/nextjs/payroll/coop-loan-deduction-setups/{id}
     * Delete a cooperative loan deduction setup.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            // Only Admins can delete
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access denied: Only administrators can delete setups.'
                ], 403);
            }

            $exists = DB::table('coop_loan_deduction_setups')->where('id', $id)->exists();
            if (!$exists) {
                return response()->json(['status' => 'error', 'message' => 'Setup not found.'], 404);
            }

            DB::table('coop_loan_deduction_setups')->where('id', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Cooperative loan deduction setup deleted successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopLoanDeductionSetupApiController destroy: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
