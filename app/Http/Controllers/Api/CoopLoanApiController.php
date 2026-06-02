<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CoopLoanApiController extends Controller
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
     * GET /api/nextjs/payroll/coop-loans/staff
     * Retrieve active staff for the dropdown menu, filtered by user access.
     */
    public function getStaffList(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $query = DB::table('tblper')
                ->where('rank', '!=', 2)
                ->where('staff_status', 1)
                ->select('ID as id', 'fileNo', 'surname', 'first_name', 'othernames')
                ->orderBy('surname', 'asc');

            // Non-admins can only select themselves
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                if ($ctx['employee']) {
                    $query->where('ID', $ctx['employee']->ID);
                } else {
                    $query->where('ID', 0); // fallback to empty
                }
            }

            $staff = $query->get()
                ->map(function ($row) {
                    $fullName = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                    return [
                        'id' => $row->id,
                        'fileNo' => $row->fileNo ?? '',
                        'name' => $fullName,
                        'label' => $fullName,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $staff
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopLoanApiController getStaffList: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/coop-loans
     * Fetch existing coop loans, filtered by role access.
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $search = trim($request->input('search', ''));
            $query = DB::table('coop_loans as cl')
                ->join('tblper as p', 'p.ID', '=', 'cl.staffId')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->select(
                    'cl.*',
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
                      ->orWhere('p.othernames', 'like', "%{$search}%")
                      ->orWhere('cl.loan_type', 'like', "%{$search}%");
                });
            }

            $employee = $ctx['employee'];

            if ($ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || $ctx['isAuditStaff']) {
                // Admins and Audit see all
            } elseif ($employee && $employee->is_hod == 1) {
                // HOD sees department staff
                $query->where('p.departmentID', $employee->departmentID);
            } elseif ($employee) {
                // Regular staff see only their own
                $query->where('cl.staffId', $employee->ID);
            } else {
                $query->where('cl.id', 0); // fallback empty
            }

            $records = $query->orderBy('cl.id', 'desc')->get()->map(function ($row) {
                $row->name = trim("{$row->surname} {$row->first_name} {$row->othernames}");
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
            Log::error('CoopLoanApiController index: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/coop-loans
     * Save/update a staff member's coop loan record.
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
                'staffId' => 'required|integer',
                'loan_type' => 'required|string|max:255',
                'loan_amount' => 'required|numeric|min:0',
                'balance' => 'nullable|numeric|min:0',
                'monthly_deduction' => 'required|numeric|min:0',
                'status' => 'nullable|string|max:50',
            ]);

            // Ensure the staff member exists
            $staff = DB::table('tblper')->where('ID', $validated['staffId'])->first();
            if (!$staff) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The selected staff member does not exist.'
                ], 404);
            }

            // Regular staff restrictions
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                if (!$ctx['employee'] || $ctx['employee']->ID != $validated['staffId']) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'You can only apply for your own cooperative loans.'
                    ], 403);
                }
            }

            $loanId = $validated['id'] ?? null;
            $loanAmount = (float) $validated['loan_amount'];

            // Prevent new applications if the staff already has an outstanding loan
            if (!$loanId) {
                $hasOutstanding = DB::table('coop_loans')
                    ->where('staffId', $validated['staffId'])
                    ->whereRaw("LOWER(status) = 'approved'")
                    ->where('balance', '>', 0)
                    ->exists();

                if ($hasOutstanding) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'This staff member already has an outstanding cooperative loan and cannot apply for a new one.'
                    ], 400);
                }
            }

            $status = 'pending';
            if ($loanId) {
                $existingLoan = DB::table('coop_loans')->where('id', $loanId)->first();
                if (!$existingLoan) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Cooperative loan record not found.'
                    ], 404);
                }

                // Regular staff can only edit pending records
                if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                    if (strtolower($existingLoan->status) !== 'pending') {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'This cooperative loan application has already been processed and cannot be edited.'
                        ], 403);
                    }
                }
                $status = $existingLoan->status;
            }

            // Admins can specify status
            if (($ctx['isSuperAdmin'] || $ctx['isAdminStaff']) && isset($validated['status'])) {
                $status = $validated['status'];
            }

            // Determine balance: only approved loans have an outstanding balance, others are 0.00 until approved
            if (strtolower($status) === 'approved') {
                $balance = isset($validated['balance']) ? (float) $validated['balance'] : $loanAmount;
            } else {
                $balance = 0.00;
            }

            $data = [
                'staffId' => $validated['staffId'],
                'loan_type' => $validated['loan_type'],
                'loan_amount' => $loanAmount,
                'balance' => $balance,
                'monthly_deduction' => (float) $validated['monthly_deduction'],
                'status' => $status,
                'updated_at' => now(),
            ];

            if ($loanId) {
                DB::table('coop_loans')->where('id', $loanId)->update($data);
                $message = 'Cooperative loan record updated successfully.';
            } else {
                $data['created_at'] = now();
                DB::table('coop_loans')->insert($data);
                $message = 'Cooperative loan applied successfully.';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopLoanApiController store: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/nextjs/payroll/coop-loans/{id}
     * Delete a coop loan record.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $loan = DB::table('coop_loans')->where('id', $id)->first();
            if (!$loan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cooperative loan record not found.'
                ], 404);
            }

            // Restrictions for non-admins
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                if (!$ctx['employee'] || $loan->staffId != $ctx['employee']->ID) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'You cannot delete another employee\'s cooperative loan record.'
                    ], 403);
                }

                if (strtolower($loan->status) !== 'pending') {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'This cooperative loan application has already been processed and cannot be deleted.'
                    ], 403);
                }
            }

            DB::table('coop_loans')->where('id', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Cooperative loan record deleted successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopLoanApiController destroy: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/coop-loans/hod-approve/{id}
     * Recommend a pending coop loan application.
     */
    public function hodApprove(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$ctx['isHod'])) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – HOD or admin privileges required.'], 401);
            }

            $loan = DB::table('coop_loans')->where('id', $id)->first();
            if (!$loan) {
                return response()->json(['status' => 'error', 'message' => 'Cooperative loan record not found.'], 404);
            }

            if (strtolower($loan->status) !== 'pending') {
                return response()->json(['status' => 'error', 'message' => 'Only pending cooperative loan applications can be recommended.'], 400);
            }

            // HOD department check
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                $employee = DB::table('tblper')->where('ID', $loan->staffId)->first();
                if (!$employee || $employee->departmentID != $ctx['employee']->departmentID) {
                    return response()->json(['status' => 'error', 'message' => 'Access denied: staff belongs to a different department.'], 403);
                }
            }

            DB::table('coop_loans')->where('id', $id)->update([
                'status' => 'recommended',
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Cooperative loan application successfully recommended by HOD.']);
        } catch (\Throwable $th) {
            Log::error('CoopLoanApiController hodApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/coop-loans/hod-reject/{id}
     * Reject a pending coop loan application.
     */
    public function hodReject(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$ctx['isHod'])) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – HOD or admin privileges required.'], 401);
            }

            $loan = DB::table('coop_loans')->where('id', $id)->first();
            if (!$loan) {
                return response()->json(['status' => 'error', 'message' => 'Cooperative loan record not found.'], 404);
            }

            if (strtolower($loan->status) !== 'pending') {
                return response()->json(['status' => 'error', 'message' => 'Only pending cooperative loan applications can be rejected.'], 400);
            }

            // HOD department check
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                $employee = DB::table('tblper')->where('ID', $loan->staffId)->first();
                if (!$employee || $employee->departmentID != $ctx['employee']->departmentID) {
                    return response()->json(['status' => 'error', 'message' => 'Access denied: staff belongs to a different department.'], 403);
                }
            }

            DB::table('coop_loans')->where('id', $id)->update([
                'status' => 'hod_rejected',
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Cooperative loan application rejected by HOD.']);
        } catch (\Throwable $th) {
            Log::error('CoopLoanApiController hodReject: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/coop-loans/audit-approve/{id}
     * Verify/recommend a coop loan application from audit.
     */
    public function auditApprove(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$ctx['isAuditStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – Audit or admin privileges required.'], 401);
            }

            $loan = DB::table('coop_loans')->where('id', $id)->first();
            if (!$loan) {
                return response()->json(['status' => 'error', 'message' => 'Cooperative loan record not found.'], 404);
            }

            if (strtolower($loan->status) !== 'hr_approved') {
                return response()->json(['status' => 'error', 'message' => 'Only HR approved cooperative loan applications can be fully approved by Audit.'], 400);
            }

            DB::table('coop_loans')->where('id', $id)->update([
                'status' => 'approved',
                'balance' => (float) $loan->loan_amount, // set balance to the full loan amount upon approval
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Cooperative loan application fully approved by Audit.']);
        } catch (\Throwable $th) {
            Log::error('CoopLoanApiController auditApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/coop-loans/audit-reject/{id}
     * Reject a recommended coop loan application from audit.
     */
    public function auditReject(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$ctx['isAuditStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – Audit or admin privileges required.'], 401);
            }

            $loan = DB::table('coop_loans')->where('id', $id)->first();
            if (!$loan) {
                return response()->json(['status' => 'error', 'message' => 'Cooperative loan record not found.'], 404);
            }

            if (strtolower($loan->status) !== 'hr_approved') {
                return response()->json(['status' => 'error', 'message' => 'Only HR approved cooperative loan applications can be rejected by Audit.'], 400);
            }

            DB::table('coop_loans')->where('id', $id)->update([
                'status' => 'audit_rejected',
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Cooperative loan application rejected by Audit.']);
        } catch (\Throwable $th) {
            Log::error('CoopLoanApiController auditReject: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/coop-loans/admin-approve/{id}
     * Fully approve a recommended coop loan application (final stage).
     */
    public function adminApprove(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – HR or Admin privileges required.'], 401);
            }

            $loan = DB::table('coop_loans')->where('id', $id)->first();
            if (!$loan) {
                return response()->json(['status' => 'error', 'message' => 'Cooperative loan record not found.'], 404);
            }

            if (strtolower($loan->status) !== 'recommended') {
                return response()->json(['status' => 'error', 'message' => 'Only HOD recommended cooperative loan applications can be approved by HR.'], 400);
            }

            DB::table('coop_loans')->where('id', $id)->update([
                'status' => 'hr_approved',
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Cooperative loan application approved by HR.']);
        } catch (\Throwable $th) {
            Log::error('CoopLoanApiController adminApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/coop-loans/admin-reject/{id}
     * Reject a recommended coop loan application (final stage).
     */
    public function adminReject(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – HR or Admin privileges required.'], 401);
            }

            $loan = DB::table('coop_loans')->where('id', $id)->first();
            if (!$loan) {
                return response()->json(['status' => 'error', 'message' => 'Cooperative loan record not found.'], 404);
            }

            if (strtolower($loan->status) !== 'recommended') {
                return response()->json(['status' => 'error', 'message' => 'Only HOD recommended cooperative loan applications can be rejected by HR.'], 400);
            }

            DB::table('coop_loans')->where('id', $id)->update([
                'status' => 'hr_rejected',
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Cooperative loan application rejected by HR.']);
        } catch (\Throwable $th) {
            Log::error('CoopLoanApiController adminReject: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/coop-loans/approved/{staffId}
     * Get the approved cooperative loan for a staff member.
     */
    public function getApprovedLoan($staffId)
    {
        try {
            $loan = DB::table('coop_loans')
                ->where('staffId', $staffId)
                ->whereRaw("LOWER(status) = 'approved'")
                ->where('balance', '>', 0)
                ->first();

            return response()->json([
                'status' => 'success',
                'data' => $loan
            ]);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
