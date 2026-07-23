<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeLoanApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * Resolve the current user context from the X-User-Id header.
     */
    

    /**
     * GET /api/nextjs/payroll/loans/staff
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
            Log::error('EmployeeLoanApiController getStaffList: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/loans
     * Fetch existing loans, filtered by role access.
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $search = trim($request->input('search', ''));
            $query = DB::table('employee_loans as el')
                ->join('tblper as p', 'p.ID', '=', 'el.staffId')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->select(
                    'el.*',
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
                      ->orWhere('el.loan_type', 'like', "%{$search}%");
                });
            }

            $employee = $ctx['employee'];

            if ($ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || $ctx['isAuditStaff']) {
                // Admins and Audit see all
            } elseif ($employee && $ctx['isHod']) {
                // HOD sees department staff
                $query->where('p.departmentID', $employee->departmentID);
            } elseif ($employee) {
                // Regular staff see only their own
                $query->where('el.staffId', $employee->ID);
            } else {
                $query->where('el.id', 0); // fallback empty
            }

            $records = $query->orderBy('el.id', 'desc')->get()->map(function ($row) {
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
            Log::error('EmployeeLoanApiController index: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/loans
     * Save/update a staff member's loan record.
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
                        'message' => 'You can only apply for your own loans.'
                    ], 403);
                }
            }

            $loanId = $validated['id'] ?? null;
            $loanAmount = (float) $validated['loan_amount'];

            // Prevent new applications if the staff already has an outstanding loan
            if (!$loanId) {
                $hasOutstanding = DB::table('employee_loans')
                    ->where('staffId', $validated['staffId'])
                    ->whereRaw("LOWER(status) = 'approved'")
                    ->where('balance', '>', 0)
                    ->exists();

                if ($hasOutstanding) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'This staff member already has an outstanding loan and cannot apply for a new one.'
                    ], 400);
                }
            }

            $status = 'pending';
            if ($loanId) {
                $existingLoan = DB::table('employee_loans')->where('id', $loanId)->first();
                if (!$existingLoan) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Loan record not found.'
                    ], 404);
                }

                // Regular staff can only edit pending records
                if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                    if (strtolower($existingLoan->status) !== 'pending') {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'This loan application has already been processed and cannot be edited.'
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
                DB::table('employee_loans')->where('id', $loanId)->update($data);
                $message = 'Loan record updated successfully.';
            } else {
                $data['created_at'] = now();
                DB::table('employee_loans')->insert($data);
                $message = 'Loan applied successfully.';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message
            ]);
        } catch (\Throwable $th) {
            Log::error('EmployeeLoanApiController store: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/nextjs/payroll/loans/{id}
     * Delete a loan record.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $loan = DB::table('employee_loans')->where('id', $id)->first();
            if (!$loan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Loan record not found.'
                ], 404);
            }

            // Restrictions for non-admins
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                if (!$ctx['employee'] || $loan->staffId != $ctx['employee']->ID) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'You cannot delete another employee\'s loan record.'
                    ], 403);
                }

                if (strtolower($loan->status) !== 'pending') {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'This loan application has already been processed and cannot be deleted.'
                    ], 403);
                }
            }

            DB::table('employee_loans')->where('id', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Loan record deleted successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('EmployeeLoanApiController destroy: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/loans/types
     * Fetch standard loan types.
     */
    public function getLoanTypes(Request $request)
    {
        try {
            $types = DB::table('loan_types')
                ->select('id', 'name')
                ->orderBy('id', 'asc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $types
            ]);
        } catch (\Throwable $th) {
            Log::error('EmployeeLoanApiController getLoanTypes: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/loans/types
     * Create or update a loan type.
     */
    public function storeLoanType(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'nullable|integer',
                'name' => 'required|string|max:191',
            ]);

            $id = $validated['id'] ?? null;
            $name = trim($validated['name']);

            $query = DB::table('loan_types')->where('name', $name);
            if ($id) {
                $query->where('id', '!=', $id);
            }
            if ($query->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'A loan type with this name already exists.'
                ], 422);
            }

            $data = [
                'name' => $name,
                'updated_at' => now(),
            ];

            if ($id) {
                $exists = DB::table('loan_types')->where('id', $id)->exists();
                if (!$exists) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Loan type not found.'
                    ], 404);
                }
                DB::table('loan_types')->where('id', $id)->update($data);
                $message = 'Loan type updated successfully.';
            } else {
                $data['created_at'] = now();
                DB::table('loan_types')->insert($data);
                $message = 'Loan type created successfully.';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message
            ]);
        } catch (\Throwable $th) {
            Log::error('EmployeeLoanApiController storeLoanType: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/nextjs/payroll/loans/types/{id}
     * Delete a loan type.
     */
    public function destroyLoanType($id)
    {
        try {
            $loanType = DB::table('loan_types')->where('id', $id)->first();
            if (!$loanType) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Loan type not found.'
                ], 404);
            }

            $inUse = DB::table('employee_loans')->where('loan_type', $loanType->name)->exists();
            if ($inUse) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This loan type cannot be deleted because it is currently assigned to one or more employee loans.'
                ], 422);
            }

            DB::table('loan_types')->where('id', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Loan type deleted successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('EmployeeLoanApiController destroyLoanType: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/loans/hod-approve/{id}
     * Recommend a pending loan application.
     */
    public function hodApprove(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$this->hasHodPermission($ctx, 'approve_loan'))) {
                return response()->json(['status' => 'error', 'message' => 'HOD or delegated administrative privileges required.'], 401);
            }

            $loan = DB::table('employee_loans')->where('id', $id)->first();
            if (!$loan) {
                return response()->json(['status' => 'error', 'message' => 'Loan record not found.'], 404);
            }

            if (strtolower($loan->status) !== 'pending') {
                return response()->json(['status' => 'error', 'message' => 'Only pending loan applications can be recommended.'], 400);
            }

            // HOD department check
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                $employee = DB::table('tblper')->where('ID', $loan->staffId)->first();
                if (!$employee || $employee->departmentID != $ctx['employee']->departmentID) {
                    return response()->json(['status' => 'error', 'message' => 'Access denied: staff belongs to a different department.'], 403);
                }
            }

            DB::table('employee_loans')->where('id', $id)->update([
                'status' => 'recommended',
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Loan application successfully recommended by HOD.']);
        } catch (\Throwable $th) {
            Log::error('EmployeeLoanApiController hodApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/loans/hod-reject/{id}
     * Reject a pending loan application.
     */
    public function hodReject(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$this->hasHodPermission($ctx, 'approve_loan'))) {
                return response()->json(['status' => 'error', 'message' => 'HOD or delegated administrative privileges required.'], 401);
            }

            $loan = DB::table('employee_loans')->where('id', $id)->first();
            if (!$loan) {
                return response()->json(['status' => 'error', 'message' => 'Loan record not found.'], 404);
            }

            if (strtolower($loan->status) !== 'pending') {
                return response()->json(['status' => 'error', 'message' => 'Only pending loan applications can be rejected.'], 400);
            }

            // HOD department check
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                $employee = DB::table('tblper')->where('ID', $loan->staffId)->first();
                if (!$employee || $employee->departmentID != $ctx['employee']->departmentID) {
                    return response()->json(['status' => 'error', 'message' => 'Access denied: staff belongs to a different department.'], 403);
                }
            }

            DB::table('employee_loans')->where('id', $id)->update([
                'status' => 'hod_rejected',
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Loan application rejected by HOD.']);
        } catch (\Throwable $th) {
            Log::error('EmployeeLoanApiController hodReject: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/loans/audit-approve/{id}
     * Verify/recommend a loan application from audit.
     */
    public function auditApprove(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$ctx['isAuditStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – Audit or admin privileges required.'], 401);
            }

            $loan = DB::table('employee_loans')->where('id', $id)->first();
            if (!$loan) {
                return response()->json(['status' => 'error', 'message' => 'Loan record not found.'], 404);
            }

            if (strtolower($loan->status) !== 'hr_approved') {
                return response()->json(['status' => 'error', 'message' => 'Only HR approved loan applications can be fully approved by Audit.'], 400);
            }

            DB::table('employee_loans')->where('id', $id)->update([
                'status' => 'approved',
                'balance' => (float) $loan->loan_amount, // set balance to the full loan amount upon approval
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Loan application fully approved by Audit.']);
        } catch (\Throwable $th) {
            Log::error('EmployeeLoanApiController auditApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/loans/audit-reject/{id}
     * Reject a recommended loan application from audit.
     */
    public function auditReject(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$ctx['isAuditStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – Audit or admin privileges required.'], 401);
            }

            $loan = DB::table('employee_loans')->where('id', $id)->first();
            if (!$loan) {
                return response()->json(['status' => 'error', 'message' => 'Loan record not found.'], 404);
            }

            if (strtolower($loan->status) !== 'hr_approved') {
                return response()->json(['status' => 'error', 'message' => 'Only HR approved loan applications can be rejected by Audit.'], 400);
            }

            DB::table('employee_loans')->where('id', $id)->update([
                'status' => 'audit_rejected',
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Loan application rejected by Audit.']);
        } catch (\Throwable $th) {
            Log::error('EmployeeLoanApiController auditReject: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/loans/admin-approve/{id}
     * Fully approve a recommended loan application (final stage).
     */
    public function adminApprove(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || !$this->hasHrPermission($ctx, 'hr_approve_loan')) {
                return response()->json(['status' => 'error', 'message' => 'HR or delegated administrative privileges required.'], 401);
            }

            $loan = DB::table('employee_loans')->where('id', $id)->first();
            if (!$loan) {
                return response()->json(['status' => 'error', 'message' => 'Loan record not found.'], 404);
            }

            if (strtolower($loan->status) !== 'recommended') {
                return response()->json(['status' => 'error', 'message' => 'Only HOD recommended loan applications can be approved by HR.'], 400);
            }

            DB::table('employee_loans')->where('id', $id)->update([
                'status' => 'hr_approved',
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Loan application approved by HR.']);
        } catch (\Throwable $th) {
            Log::error('EmployeeLoanApiController adminApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/loans/admin-reject/{id}
     * Reject a recommended loan application (final stage).
     */
    public function adminReject(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || !$this->hasHrPermission($ctx, 'hr_approve_loan')) {
                return response()->json(['status' => 'error', 'message' => 'HR or delegated administrative privileges required.'], 401);
            }

            $loan = DB::table('employee_loans')->where('id', $id)->first();
            if (!$loan) {
                return response()->json(['status' => 'error', 'message' => 'Loan record not found.'], 404);
            }

            if (strtolower($loan->status) !== 'recommended') {
                return response()->json(['status' => 'error', 'message' => 'Only HOD recommended loan applications can be rejected by HR.'], 400);
            }

            DB::table('employee_loans')->where('id', $id)->update([
                'status' => 'hr_rejected',
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Loan application rejected by HR.']);
        } catch (\Throwable $th) {
            Log::error('EmployeeLoanApiController adminReject: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
