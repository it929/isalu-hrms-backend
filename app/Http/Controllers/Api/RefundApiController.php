<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefundApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * GET /api/nextjs/payroll/refunds/staff
     * Fetch active employees.
     */
    public function getStaffList(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $query = DB::table('tblper as p')
                ->where('p.rank', '!=', 2) // Exclude terminated/retired
                ->where('p.staff_status', 1)
                ->select(
                    'p.ID as id',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames'
                )
                ->orderBy('p.surname', 'asc');

            // Non-admins can only select themselves
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                if ($ctx['employee']) {
                    $query->where('p.ID', $ctx['employee']->ID);
                } else {
                    $query->where('p.ID', 0);
                }
            }

            $staff = $query->get()->map(function ($row) {
                $fullName = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                return [
                    'id'     => $row->id,
                    'fileNo' => $row->fileNo ?? '',
                    'name'   => $fullName,
                    'label'  => $fullName,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data'   => $staff
            ]);
        } catch (\Throwable $th) {
            Log::error('RefundApiController getStaffList: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/refunds
     * List all refund requests matching user credentials.
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $search = trim($request->input('search', ''));
            $query = DB::table('refund_requests as rr')
                ->join('tblper as p', 'p.ID', '=', 'rr.staff_id')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->leftJoin('users as u_hod', 'u_hod.id', '=', 'rr.hod_id')
                ->leftJoin('users as u_admin', 'u_admin.id', '=', 'rr.admin_id')
                ->leftJoin('users as u_audit', 'u_audit.id', '=', 'rr.audit_id')
                ->leftJoin('users as u_finance', 'u_finance.id', '=', 'rr.finance_id')
                ->select(
                    'rr.*',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'd.department',
                    'u_hod.name as hod_name',
                    'u_admin.name as admin_name',
                    'u_audit.name as audit_name',
                    'u_finance.name as finance_name'
                );

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('p.surname', 'like', "%{$search}%")
                      ->orWhere('p.first_name', 'like', "%{$search}%")
                      ->orWhere('p.othernames', 'like', "%{$search}%")
                      ->orWhere('rr.reason', 'like', "%{$search}%");
                });
            }

            $employee = $ctx['employee'];

            if ($ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || $ctx['isFinanceStaff'] || $ctx['isAuditStaff']) {
                // Administrative staff see all requests
            } elseif ($employee && $ctx['isHod']) {
                // HOD sees department staff requests
                $query->where('p.departmentID', $employee->departmentID);
            } elseif ($employee) {
                // Regular employees only see their own requests
                $query->where('rr.staff_id', $employee->ID);
            } else {
                $query->where('rr.id', 0); // fallback empty
            }

            $records = $query->orderBy('rr.id', 'desc')->get()->map(function ($row) {
                $row->name = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                return $row;
            });

            return response()->json([
                'status'         => 'success',
                'data'           => $records,
                'isSuperAdmin'   => $ctx['isSuperAdmin'],
                'isAdminStaff'   => $ctx['isAdminStaff'],
                'isFinanceStaff' => $ctx['isFinanceStaff'],
                'isAuditStaff'   => $ctx['isAuditStaff'],
                'isHod'          => $ctx['isHod'],
                'employee'       => $employee,
            ]);
        } catch (\Throwable $th) {
            Log::error('RefundApiController index: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/refunds
     * Save or update a refund request.
     */
    public function store(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $validated = $request->validate([
                'id'          => 'nullable|integer',
                'staff_id'    => 'required|integer',
                'amount'      => 'required|numeric|min:0.01',
                'reason'      => 'required|string',
                'refund_date' => 'required|date',
            ]);

            // Staff check
            $staff = DB::table('tblper')->where('ID', $validated['staff_id'])->first();
            if (!$staff) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'The selected staff member does not exist.'
                ], 404);
            }

            $id = $validated['id'] ?? null;
            $data = [
                'staff_id'    => $validated['staff_id'],
                'amount'      => (float) $validated['amount'],
                'reason'      => $validated['reason'],
                'refund_date' => $validated['refund_date'],
                'updated_at'  => now(),
            ];

            if ($id) {
                // Update existing record
                $record = DB::table('refund_requests')->where('id', $id)->first();
                if (!$record) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Refund request not found.'
                    ], 404);
                }

                if ($record->status !== 0) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'This request has already been processed and cannot be updated.'
                    ], 422);
                }

                DB::table('refund_requests')->where('id', $id)->update($data);
                $msg = 'Refund request updated successfully.';
            } else {
                // Insert new record
                $data['status']         = 0;
                $data['hod_status']     = 0;
                $data['admin_status']   = 0;
                $data['audit_status']   = 0;
                $data['finance_status'] = 0;
                $data['created_at']     = now();

                DB::table('refund_requests')->insert($data);
                $msg = 'Refund request submitted successfully.';
            }

            return response()->json([
                'status'  => 'success',
                'message' => $msg
            ]);
        } catch (\Throwable $th) {
            Log::error('RefundApiController store: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/nextjs/payroll/refunds/{id}
     * Remove a refund request.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $record = DB::table('refund_requests')->where('id', $id)->first();
            if (!$record) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Refund request not found.'
                ], 404);
            }

            // Non-admins can only delete pending and self-owned
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                if (!$ctx['employee'] || $record->staff_id != $ctx['employee']->ID) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'You cannot delete another staff member\'s refund request.'
                    ], 403);
                }

                if ($record->status !== 0) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'This request has already been processed and cannot be deleted.'
                    ], 403);
                }
            }

            DB::table('refund_requests')->where('id', $id)->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Refund request deleted successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('RefundApiController destroy: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/refunds/hod-approve/{id}
     */
    public function hodApprove(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$this->hasHodPermission($ctx, 'approve_refund'))) {
                return response()->json(['status' => 'error', 'message' => 'HOD or delegated administrative privileges required.'], 401);
            }

            $record = DB::table('refund_requests')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Refund request not found.'], 404);
            }

            if ($record->hod_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This request is not in a pending HOD state.'], 400);
            }

            // HOD department check
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                $employee = DB::table('tblper')->where('ID', $record->staff_id)->first();
                if (!$employee || $employee->departmentID != $ctx['employee']->departmentID) {
                    return response()->json(['status' => 'error', 'message' => 'Access denied: staff belongs to a different department.'], 403);
                }
            }

            $remarks = $request->input('remarks');
            DB::table('refund_requests')->where('id', $id)->update([
                'hod_status' => 1,
                'hod_id'     => $ctx['userId'],
                'hod_date'   => now(),
                'remarks'    => $remarks,
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Refund request approved by HOD.']);
        } catch (\Throwable $th) {
            Log::error('RefundApiController hodApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/refunds/hod-reject/{id}
     */
    public function hodReject(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$this->hasHodPermission($ctx, 'approve_refund'))) {
                return response()->json(['status' => 'error', 'message' => 'HOD or delegated administrative privileges required.'], 401);
            }

            $record = DB::table('refund_requests')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Refund request not found.'], 404);
            }

            if ($record->hod_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This request is not in a pending HOD state.'], 400);
            }

            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                $employee = DB::table('tblper')->where('ID', $record->staff_id)->first();
                if (!$employee || $employee->departmentID != $ctx['employee']->departmentID) {
                    return response()->json(['status' => 'error', 'message' => 'Access denied: staff belongs to a different department.'], 403);
                }
            }

            $remarks = $request->input('remarks');
            DB::table('refund_requests')->where('id', $id)->update([
                'hod_status' => 2,
                'status'     => 2, // Over-all status marked rejected
                'hod_id'     => $ctx['userId'],
                'hod_date'   => now(),
                'remarks'    => $remarks,
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Refund request rejected by HOD.']);
        } catch (\Throwable $th) {
            Log::error('RefundApiController hodReject: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/refunds/hr-approve/{id}
     */
    public function hrApprove(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || !$this->hasHrPermission($ctx, 'hr_approve_refund')) {
                return response()->json(['status' => 'error', 'message' => 'HR or delegated administrative privileges required.'], 401);
            }

            $record = DB::table('refund_requests')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Refund request not found.'], 404);
            }

            if ($record->hod_status !== 1 || $record->admin_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This request is not in a pending HR state.'], 400);
            }

            $remarks = $request->input('remarks');
            DB::table('refund_requests')->where('id', $id)->update([
                'admin_status' => 1,
                'admin_id'     => $ctx['userId'],
                'admin_date'   => now(),
                'remarks'      => $remarks,
                'updated_at'   => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Refund request approved by HR Admin.']);
        } catch (\Throwable $th) {
            Log::error('RefundApiController hrApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/refunds/hr-reject/{id}
     */
    public function hrReject(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || !$this->hasHrPermission($ctx, 'hr_approve_refund')) {
                return response()->json(['status' => 'error', 'message' => 'HR or delegated administrative privileges required.'], 401);
            }
 
            $record = DB::table('refund_requests')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Refund request not found.'], 404);
            }
 
            if ($record->hod_status !== 1 || $record->admin_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This request is not in a pending HR state.'], 400);
            }
 
            $remarks = $request->input('remarks');
            DB::table('refund_requests')->where('id', $id)->update([
                'admin_status' => 2,
                'status'       => 2, // Over-all status marked rejected
                'admin_id'     => $ctx['userId'],
                'admin_date'   => now(),
                'remarks'      => $remarks,
                'updated_at'   => now(),
            ]);
 
            return response()->json(['status' => 'success', 'message' => 'Refund request rejected by HR Admin.']);
        } catch (\Throwable $th) {
            Log::error('RefundApiController hrReject: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
 
    /**
     * GET /api/nextjs/payroll/refunds/audit-approve/{id}
     */
    public function auditApprove(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$ctx['isAuditStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'Audit or administrative privileges required.'], 401);
            }
 
            $record = DB::table('refund_requests')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Refund request not found.'], 404);
            }
 
            // Audit recommends after HR recommends
            if ($record->admin_status !== 1 || $record->audit_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This request is not recommended by HR or already processed by Audit.'], 400);
            }
 
            $remarks = $request->input('remarks');
            DB::table('refund_requests')->where('id', $id)->update([
                'audit_status' => 1,
                'audit_id'     => $ctx['userId'],
                'audit_date'   => now(),
                'remarks'      => $remarks,
                'updated_at'   => now(),
            ]);
 
            return response()->json(['status' => 'success', 'message' => 'Refund request recommended successfully by Audit.']);
        } catch (\Throwable $th) {
            Log::error('RefundApiController auditApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
 
    /**
     * GET /api/nextjs/payroll/refunds/audit-reject/{id}
     */
    public function auditReject(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$ctx['isAuditStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'Audit or administrative privileges required.'], 401);
            }
 
            $record = DB::table('refund_requests')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Refund request not found.'], 404);
            }
 
            if ($record->admin_status !== 1 || $record->audit_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This request is not recommended by HR or already processed by Audit.'], 400);
            }
 
            $remarks = $request->input('remarks');
            DB::table('refund_requests')->where('id', $id)->update([
                'audit_status' => 2,
                'status'       => 2, // Rejects overall application immediately
                'audit_id'     => $ctx['userId'],
                'audit_date'   => now(),
                'remarks'      => $remarks,
                'updated_at'   => now(),
            ]);
 
            return response()->json(['status' => 'success', 'message' => 'Refund request rejected by Audit.']);
        } catch (\Throwable $th) {
            Log::error('RefundApiController auditReject: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/refunds/finance-approve/{id}
     */
    public function financeApprove(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isFinanceStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'Finance head privileges required.'], 401);
            }
 
            $record = DB::table('refund_requests')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Refund request not found.'], 404);
            }
 
            // Finance approves after Audit recommends (audit_status === 1)
            if ($record->audit_status !== 1 || $record->finance_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This request is not recommended by Audit or already processed by Finance.'], 400);
            }
 
            $remarks = $request->input('remarks');
            DB::table('refund_requests')->where('id', $id)->update([
                'finance_status' => 1,
                'status'         => 1, // Approved overall
                'finance_id'     => $ctx['userId'],
                'finance_date'   => now(),
                'remarks'        => $remarks,
                'updated_at'     => now(),
            ]);
 
            return response()->json(['status' => 'success', 'message' => 'Refund request marked as paid and completed by Finance.']);
        } catch (\Throwable $th) {
            Log::error('RefundApiController financeApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/refunds/finance-reject/{id}
     */
    public function financeReject(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isFinanceStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'Finance head privileges required.'], 401);
            }
 
            $record = DB::table('refund_requests')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Refund request not found.'], 404);
            }
 
            if ($record->audit_status !== 1 || $record->finance_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This request is not recommended by Audit or already processed by Finance.'], 400);
            }
 
            $remarks = $request->input('remarks');
            DB::table('refund_requests')->where('id', $id)->update([
                'finance_status' => 2,
                'status'         => 2, // Rejected overall
                'finance_id'     => $ctx['userId'],
                'finance_date'   => now(),
                'remarks'        => $remarks,
                'updated_at'     => now(),
            ]);
 
            return response()->json(['status' => 'success', 'message' => 'Refund request rejected by Finance.']);
        } catch (\Throwable $th) {
            Log::error('RefundApiController financeReject: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
