<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ResignationApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * GET /api/nextjs/payroll/resignations/staff
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

            // Non-admins can only select themselves (unless they are HODs, who can select staff in their department)
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                if ($ctx['isHod']) {
                    $hodDeptId = ($ctx['isDelegatedHod'] ?? false) ? $ctx['delegated_department_id'] : ($ctx['employee'] ? $ctx['employee']->departmentID : null);
                    $query->where('p.departmentID', $hodDeptId);
                } elseif ($ctx['employee']) {
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
            Log::error('ResignationApiController getStaffList: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/resignations
     * List all resignation requests.
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $search = trim($request->input('search', ''));
            $query = DB::table('resignation_requests as rr')
                ->join('tblper as p', 'p.ID', '=', 'rr.staff_id')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->leftJoin('users as u_hod', 'u_hod.id', '=', 'rr.hod_id')
                ->leftJoin('users as u_admin', 'u_admin.id', '=', 'rr.admin_id')
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

            if ($ctx['isSuperAdmin'] || $ctx['isFinanceStaff'] || $ctx['isAuditStaff']) {
                // Admins, Finance and Audit see all requests
            } else {
                $query->where(function ($q) use ($ctx, $employee) {
                    $hasCondition = false;

                    // 1. Own records
                    if ($employee) {
                        $q->where('rr.staff_id', $employee->ID);
                        $hasCondition = true;
                    }

                    // 2. HR HEAD sees HOD approved (hod_status = 1) or processed (admin_status != 0) records
                    if ($ctx['isAdminStaff']) {
                        if ($hasCondition) {
                            $q->orWhere('rr.hod_status', 1)->orWhere('rr.admin_status', '!=', 0);
                        } else {
                            $q->where(function($sub) {
                                $sub->where('rr.hod_status', 1)->orWhere('rr.admin_status', '!=', 0);
                            });
                        }
                        $hasCondition = true;
                    }

                    // 3. HOD sees department staff requests
                    if ($employee && $ctx['isHod']) {
                        $hodDeptId = ($ctx['isDelegatedHod'] ?? false) ? $ctx['delegated_department_id'] : $employee->departmentID;
                        if ($hasCondition) {
                            $q->orWhere('p.departmentID', $hodDeptId);
                        } else {
                            $q->where('p.departmentID', $hodDeptId);
                        }
                        $hasCondition = true;
                    }

                    // Fallback if no roles matched
                    if (!$hasCondition) {
                        $q->where('rr.id', 0);
                    }
                });
            }

            $records = $query->orderBy('rr.id', 'desc')->get()->map(function ($row) {
                $row->name = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                $row->last_day = date('Y-m-d', strtotime($row->resignation_date . ' + 30 days'));
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
            Log::error('ResignationApiController index: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/resignations
     * Submit or update resignation request.
     */
    public function store(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $validated = $request->validate([
                'id'               => 'nullable|integer',
                'staff_id'         => 'required|integer',
                'reason'           => 'required|string',
                'resignation_date' => 'required|date',
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
                'staff_id'         => $validated['staff_id'],
                'reason'           => $validated['reason'],
                'resignation_date' => $validated['resignation_date'],
                'updated_at'       => now(),
            ];

            if ($id) {
                $record = DB::table('resignation_requests')->where('id', $id)->first();
                if (!$record) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Resignation request not found.'
                    ], 404);
                }

                if ($record->status !== 0) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'This request has already been processed and cannot be modified.'
                    ], 422);
                }

                DB::table('resignation_requests')->where('id', $id)->update($data);
                $msg = 'Resignation request updated successfully.';
            } else {
                $data['status']         = 0;
                $data['hod_status']     = 0;
                $data['admin_status']   = 0;
                $data['finance_status'] = 0;
                $data['created_at']     = now();

                DB::table('resignation_requests')->insert($data);
                $msg = 'Resignation request submitted successfully.';
            }

            return response()->json([
                'status'  => 'success',
                'message' => $msg
            ]);
        } catch (\Throwable $th) {
            Log::error('ResignationApiController store: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/nextjs/payroll/resignations/{id}
     * Remove pending resignation request.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $record = DB::table('resignation_requests')->where('id', $id)->first();
            if (!$record) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Resignation request not found.'
                ], 404);
            }

            // Non-admins can only delete pending and self-owned
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                if (!$ctx['employee'] || $record->staff_id != $ctx['employee']->ID) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'You cannot delete another staff member\'s resignation request.'
                    ], 403);
                }

                if ($record->status !== 0) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'This request has already been processed and cannot be deleted.'
                    ], 403);
                }
            }

            DB::table('resignation_requests')->where('id', $id)->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Resignation request deleted successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('ResignationApiController destroy: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/resignations/hod-approve/{id}
     */
    public function hodApprove(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$this->hasHodPermission($ctx, 'approve_resignation'))) {
                return response()->json(['status' => 'error', 'message' => 'HOD or delegated administrative privileges required.'], 401);
            }

            $record = DB::table('resignation_requests')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Resignation request not found.'], 404);
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
            DB::table('resignation_requests')->where('id', $id)->update([
                'hod_status' => 1,
                'hod_id'     => $ctx['userId'],
                'hod_date'   => now(),
                'remarks'    => $remarks,
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Resignation request approved by HOD.']);
        } catch (\Throwable $th) {
            Log::error('ResignationApiController hodApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/resignations/hod-reject/{id}
     */
    public function hodReject(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$this->hasHodPermission($ctx, 'approve_resignation'))) {
                return response()->json(['status' => 'error', 'message' => 'HOD or delegated administrative privileges required.'], 401);
            }

            $record = DB::table('resignation_requests')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Resignation request not found.'], 404);
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
            DB::table('resignation_requests')->where('id', $id)->update([
                'hod_status' => 2,
                'status'     => 2, // rejected
                'hod_id'     => $ctx['userId'],
                'hod_date'   => now(),
                'remarks'    => $remarks,
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Resignation request rejected by HOD.']);
        } catch (\Throwable $th) {
            Log::error('ResignationApiController hodReject: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/resignations/hr-approve/{id}
     */
    public function hrApprove(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || !$this->hasHrPermission($ctx, 'hr_approve_resignation')) {
                return response()->json(['status' => 'error', 'message' => 'HR or delegated administrative privileges required.'], 401);
            }

            $record = DB::table('resignation_requests')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Resignation request not found.'], 404);
            }

            if ($record->hod_status !== 1 || $record->admin_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This request is not in a pending HR state.'], 400);
            }

            $remarks = $request->input('remarks');
            DB::beginTransaction();

            DB::table('resignation_requests')->where('id', $id)->update([
                'admin_status' => 1,
                'status'       => 1, // Approved overall (HR is now final stage)
                'admin_id'     => $ctx['userId'],
                'admin_date'   => now(),
                'remarks'      => $remarks,
                'updated_at'   => now(),
            ]);

            // Automatically remove staff from payroll on HR Head approval
            DB::table('tblper')->where('ID', $record->staff_id)->update([
                'staff_status' => 0,
                'status_value' => 'resignation',
                'updated_at'   => now(),
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Resignation request approved by HR Admin. Staff has been removed from active payroll.'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('ResignationApiController hrApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/resignations/approved
     * Fetch all resignation requests that have been approved by HR HEAD.
     */
    public function getApprovedResignations(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $search = trim($request->input('search', ''));
            $fromDate = trim($request->input('from_date', ''));
            $toDate = trim($request->input('to_date', ''));

            $query = DB::table('resignation_requests as rr')
                ->join('tblper as p', 'p.ID', '=', 'rr.staff_id')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->leftJoin('users as u_admin', 'u_admin.id', '=', 'rr.admin_id')
                ->leftJoin('users as u_audit', 'u_audit.id', '=', 'rr.audit_id')
                ->leftJoin('users as u_finance', 'u_finance.id', '=', 'rr.finance_id')
                ->leftJoin('salary_structures as ss', 'ss.staffId', '=', 'p.ID')
                ->leftJoin('first_salary_structure as fss', 'fss.staffId', '=', 'p.ID')
                ->where('rr.admin_status', 1) // Only HR Head approved resignations
                ->select(
                    'rr.*',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'p.staff_status',
                    'p.status_value',
                    'd.department',
                    'u_admin.name as approved_by_name',
                    'u_audit.name as audit_by_name',
                    'u_finance.name as finance_by_name',
                    DB::raw('COALESCE(ss.basic_salary, fss.basic_salary, 0) as basic_salary'),
                    DB::raw('COALESCE(ss.housing_allowance, fss.housing_allowance, 0) as housing_allowance'),
                    DB::raw('COALESCE(ss.transport_allowance, fss.transport_allowance, 0) as transport_allowance'),
                    DB::raw('COALESCE(ss.medical_allowance, fss.medical_allowance, 0) as medical_allowance'),
                    DB::raw('COALESCE(ss.utility_allowance, fss.utility_allowance, 0) as utility_allowance'),
                    DB::raw('COALESCE(ss.meal_allowance, fss.meal_allowance, 0) as meal_allowance'),
                    DB::raw('COALESCE(ss.declare_salary, fss.declare_salary, 0) as declare_salary'),
                    DB::raw('COALESCE(ss.declare_salary, fss.declare_salary, 0) as declared_salary'),
                    DB::raw('(COALESCE(fss.basic_salary, 0) + COALESCE(fss.housing_allowance, 0) + COALESCE(fss.transport_allowance, 0) + COALESCE(fss.medical_allowance, 0) + COALESCE(fss.utility_allowance, 0) + COALESCE(fss.meal_allowance, 0)) as entry_allowances_total'),
                    DB::raw('COALESCE(fss.declare_salary, 0) as entry_declare_salary'),
                    'fss.num_rente_months',
                    'fss.reten_act',
                    'ss.pen_act',
                    'ss.pension_rate'
                );

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('p.surname', 'like', "%{$search}%")
                      ->orWhere('p.first_name', 'like', "%{$search}%")
                      ->orWhere('p.othernames', 'like', "%{$search}%")
                      ->orWhere('p.fileNo', 'like', "%{$search}%")
                      ->orWhere('d.department', 'like', "%{$search}%");
                });
            }

            if ($fromDate !== '') {
                $query->where('rr.resignation_date', '>=', $fromDate);
            }

            if ($toDate !== '') {
                $query->where('rr.resignation_date', '<=', $toDate);
            }

            $rawRecords = $query->orderBy('rr.admin_date', 'desc')->orderBy('rr.id', 'desc')->get();

            // Compute summary metrics for list view
            $records = $rawRecords->map(function ($row) {
                $row->name = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                $calc = $this->calculateQuickSettlement($row);
                $row->exit_date = $calc['exit_date'];
                $row->notice_days = $calc['notice_days'];
                $row->monthly_gross = $calc['monthly_gross'];
                $row->declared_salary = $calc['declared_salary'];
                $row->notice_salary_total = $calc['notice_salary_total'];
                $row->retention_refund = $calc['retention_refund'];
                $row->coop_savings_refund = $calc['coop_savings_refund'];
                $row->bonuses_total = $calc['bonuses_total'];
                $row->total_earnings = $calc['total_earnings'];
                $row->total_deductions = $calc['total_deductions'];
                $row->net_settlement = $calc['net_settlement'];
                $row->settlement_type = $calc['settlement_type']; // payable, recoverable, balanced
                $row->audit_status = (int)($row->audit_status ?? 0);
                $row->finance_status = (int)($row->finance_status ?? 0);
                return $row;
            });

            $summary = [
                'total_approved_resigned'     => $records->count(),
                'total_notice_earnings'       => round($records->sum('notice_salary_total'), 2),
                'total_retention_refunds'     => round($records->sum('retention_refund'), 2),
                'total_coop_savings_refunds'  => round($records->sum('coop_savings_refund'), 2),
                'total_deductions'            => round($records->sum('total_deductions'), 2),
                'total_net_settlement'        => round($records->sum('net_settlement'), 2),
            ];

            return response()->json([
                'status'           => 'success',
                'data'             => $records,
                'summary'          => $summary,
                'user_permissions' => [
                    'is_super_admin'   => (bool)$ctx['isSuperAdmin'],
                    'is_admin_staff'   => (bool)$ctx['isAdminStaff'],
                    'is_audit_staff'   => (bool)$ctx['isAuditStaff'],
                    'is_finance_staff' => (bool)$ctx['isFinanceStaff'],
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('ResignationApiController getApprovedResignations: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/resignations/settlement/{id}
     * Comprehensive Exit Settlement Breakdown for an HR-approved resigned staff member.
     */
    public function getSettlementBreakdown(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $settlementData = $this->computeDetailedSettlement($id);
            if (!$settlementData) {
                return response()->json(['status' => 'error', 'message' => 'Approved resignation record not found.'], 404);
            }

            $settlementData['user_permissions'] = [
                'is_super_admin'   => (bool)$ctx['isSuperAdmin'],
                'is_admin_staff'   => (bool)$ctx['isAdminStaff'],
                'is_audit_staff'   => (bool)$ctx['isAuditStaff'],
                'is_finance_staff' => (bool)$ctx['isFinanceStaff'],
            ];

            return response()->json([
                'status' => 'success',
                'data'   => $settlementData
            ]);
        } catch (\Throwable $th) {
            Log::error('ResignationApiController getSettlementBreakdown: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST|GET /api/nextjs/payroll/resignations/settlement/{id}/send-email
     * Send or re-send Exit Settlement Breakdown Slip to staff email.
     */
    public function sendSettlementEmail(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$ctx['isAuditStaff'] && !$ctx['isFinanceStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'Administrative, Audit, or Finance privileges required to email settlement slip.'], 401);
            }

            $settlementData = $this->computeDetailedSettlement($id);
            if (!$settlementData) {
                return response()->json(['status' => 'error', 'message' => 'Approved resignation record not found.'], 404);
            }

            $overrideEmail = trim($request->input('email', ''));
            $customNote = trim($request->input('note', ''));

            $result = $this->sendSettlementBreakdownEmail($settlementData, $overrideEmail ?: null, $customNote ?: null);

            if (!$result['sent']) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $result['message'] ?? 'Could not send settlement slip email.'
                ], 422);
            }

            return response()->json([
                'status'  => 'success',
                'message' => "Exit Settlement Breakdown Slip successfully emailed to {$result['email']}."
            ]);
        } catch (\Throwable $th) {
            Log::error('ResignationApiController sendSettlementEmail: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * Compute comprehensive Exit Settlement Breakdown data for an HR-approved resigned staff member.
     */
    public function computeDetailedSettlement($id): ?array
    {
        $resignation = DB::table('resignation_requests as rr')
            ->join('tblper as p', 'p.ID', '=', 'rr.staff_id')
            ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
            ->leftJoin('tblbanklist as b', 'b.bankID', '=', 'p.bankID')
            ->leftJoin('users as u_admin', 'u_admin.id', '=', 'rr.admin_id')
            ->leftJoin('users as u_hod', 'u_hod.id', '=', 'rr.hod_id')
            ->leftJoin('users as u_audit', 'u_audit.id', '=', 'rr.audit_id')
            ->leftJoin('users as u_finance', 'u_finance.id', '=', 'rr.finance_id')
            ->leftJoin('users as u_staff', 'u_staff.id', '=', 'p.UserID')
            ->leftJoin('salary_structures as ss', 'ss.staffId', '=', 'p.ID')
            ->leftJoin('first_salary_structure as fss', 'fss.staffId', '=', 'p.ID')
            ->where('rr.id', $id)
            ->select(
                'rr.*',
                'p.fileNo',
                'p.surname',
                'p.first_name',
                'p.othernames',
                'p.email as staff_email',
                'p.alternate_email as staff_alternate_email',
                'p.phone as staff_phone',
                'u_staff.email as user_account_email',
                'p.appointment_date',
                'p.AccNo as account_no',
                'p.staff_status',
                'p.status_value',
                'd.department',
                'b.bank as bank_name',
                'u_admin.name as approved_by_name',
                'u_hod.name as hod_name',
                'u_audit.name as audit_by_name',
                'u_finance.name as finance_by_name',
                DB::raw('COALESCE(ss.basic_salary, fss.basic_salary, 0) as basic_salary'),
                DB::raw('COALESCE(ss.housing_allowance, fss.housing_allowance, 0) as housing_allowance'),
                DB::raw('COALESCE(ss.transport_allowance, fss.transport_allowance, 0) as transport_allowance'),
                DB::raw('COALESCE(ss.medical_allowance, fss.medical_allowance, 0) as medical_allowance'),
                DB::raw('COALESCE(ss.utility_allowance, fss.utility_allowance, 0) as utility_allowance'),
                DB::raw('COALESCE(ss.meal_allowance, fss.meal_allowance, 0) as meal_allowance'),
                DB::raw('COALESCE(ss.declare_salary, fss.declare_salary, 0) as declare_salary'),
                DB::raw('COALESCE(ss.declare_salary, fss.declare_salary, 0) as declared_salary'),
                DB::raw('(COALESCE(fss.basic_salary, 0) + COALESCE(fss.housing_allowance, 0) + COALESCE(fss.transport_allowance, 0) + COALESCE(fss.medical_allowance, 0) + COALESCE(fss.utility_allowance, 0) + COALESCE(fss.meal_allowance, 0)) as entry_allowances_total'),
                DB::raw('COALESCE(fss.declare_salary, 0) as entry_declare_salary'),
                'fss.num_rente_months',
                'fss.reten_act',
                'ss.pen_act',
                'ss.pension_rate'
            )
            ->first();

        if (!$resignation) {
            return null;
        }

        $staffId = (int)$resignation->staff_id;
        $resignationDate = $resignation->resignation_date;
        
        // ── 1. Calculate Monthly Gross & Breakdown (Active Salary from salary_structures) ──
        $basicSalary      = (float)($resignation->basic_salary ?? 0);
        $housingAllowance = (float)($resignation->housing_allowance ?? 0);
        $transportAllowance = (float)($resignation->transport_allowance ?? 0);
        $medicalAllowance = (float)($resignation->medical_allowance ?? 0);
        $utilityAllowance = (float)($resignation->utility_allowance ?? 0);
        $mealAllowance    = (float)($resignation->meal_allowance ?? 0);

        $sumAllowances = $basicSalary + $housingAllowance + $transportAllowance + $medicalAllowance + $utilityAllowance + $mealAllowance;
        $monthlyGross = $sumAllowances > 0 ? $sumAllowances : (float)($resignation->declare_salary ?? 0);

        // ── 2. Notice Period & Calendar-Aware Salary Proration ──
        // Notice: 1 Month (30 Days) from resignation_date
        $noticeStartDate = new \DateTime($resignationDate);
        $exitDateObj = (clone $noticeStartDate)->modify('+30 days');
        $exitDate = $exitDateObj->format('Y-m-d');

        $startYear = (int)$noticeStartDate->format('Y');
        $startMonthNum = (int)$noticeStartDate->format('n'); // 1-12
        $startDay = (int)$noticeStartDate->format('j');       // 1-31
        $startMonthName = $noticeStartDate->format('F Y');
        $daysInStartMonth = (int)$noticeStartDate->format('t'); // 28, 29, 30, 31

        $exitYear = (int)$exitDateObj->format('Y');
        $exitMonthNum = (int)$exitDateObj->format('n');
        $exitDay = (int)$exitDateObj->format('j');
        $exitMonthName = $exitDateObj->format('F Y');
        $daysInExitMonth = (int)$exitDateObj->format('t');

        $noticeBreakdown = [];
        $totalNoticeSalary = 0.00;

        if ("$startYear-$startMonthNum" === "$exitYear-$exitMonthNum") {
            // If notice start & exit fall within the exact same month
            $daysWorked = min($daysInStartMonth, ($exitDay - $startDay + 1));
            $amount = round($monthlyGross * ($daysWorked / $daysInStartMonth), 2);
            $noticeBreakdown[] = [
                'month_name'     => $startMonthName,
                'period_label'   => "{$startMonthName} (Full Month / Notice Days: {$daysWorked}/{$daysInStartMonth} days)",
                'days_in_month'  => $daysInStartMonth,
                'days_worked'    => $daysWorked,
                'is_full_month'  => ($daysWorked >= $daysInStartMonth),
                'monthly_gross'  => $monthlyGross,
                'earned_salary'  => $amount,
            ];
            $totalNoticeSalary += $amount;
        } else {
            // Month 1: Staff submitted during this month
            $m1Amount = round($monthlyGross, 2);
            $noticeBreakdown[] = [
                'month_name'     => $startMonthName,
                'period_label'   => "{$startMonthName} (Full Resignation Month: {$daysInStartMonth}/{$daysInStartMonth} days)",
                'days_in_month'  => $daysInStartMonth,
                'days_worked'    => $daysInStartMonth,
                'is_full_month'  => true,
                'monthly_gross'  => $monthlyGross,
                'earned_salary'  => $m1Amount,
            ];
            $totalNoticeSalary += $m1Amount;

            // Month 2: Staff completes their 1-month notice into the subsequent month
            $m2DaysWorked = max(1, $exitDay);
            $m2Amount = round($monthlyGross * ($m2DaysWorked / $daysInExitMonth), 2);
            $noticeBreakdown[] = [
                'month_name'     => $exitMonthName,
                'period_label'   => "{$exitMonthName} (Prorated Exit Month: {$m2DaysWorked}/{$daysInExitMonth} days)",
                'days_in_month'  => $daysInExitMonth,
                'days_worked'    => $m2DaysWorked,
                'is_full_month'  => false,
                'monthly_gross'  => $monthlyGross,
                'earned_salary'  => $m2Amount,
            ];
            $totalNoticeSalary += $m2Amount;
        }

        // ── 3. Retention Fund Calculation & 100% Refund (Based on Entry Salary from first_salary_structure) ──
        $entryAllowances = (float)($resignation->entry_allowances_total ?? 0);
        $entryDeclare = (float)($resignation->entry_declare_salary ?? 0);
        $entryGross = $entryAllowances > 0 ? $entryAllowances : $entryDeclare;
        $retentionBaseSalary = $entryGross > 0 ? $entryGross : $monthlyGross;

        $monthlyRetentionRate = round(0.05 * $retentionBaseSalary, 2);
        $retentionMonthsRecorded = (int)($resignation->num_rente_months ?? 0);

        $retentionFromPayroll = 0.00;
        if (\Illuminate\Support\Facades\Schema::hasTable('payroll_conpt')) {
            $retentionFromPayroll = (float)DB::table('payroll_conpt')
                ->where('staffID', $staffId)
                ->sum('retention');
        }

        $retentionFromStructure = round($retentionMonthsRecorded * $monthlyRetentionRate, 2);
        $totalRetentionDeducted = max($retentionFromPayroll, $retentionFromStructure);
        $retentionRefundAmount = $totalRetentionDeducted;

        // ── 3b. Cooperative Savings Accumulated Balance Refund ──
        $coopSavingsRefund = 0.00;
        if (\Illuminate\Support\Facades\Schema::hasTable('coop_savings_setups')) {
            $coopSavingsRecord = DB::table('coop_savings_setups')->where('staffId', $staffId)->first();
            if ($coopSavingsRecord) {
                $coopSavingsRefund = (float)($coopSavingsRecord->saving_balance ?? 0);
            }
        }

        // ── 3c. Active Bonuses, Custom Allowances & Variable Earnings ──
        $activeBonusesTotal = 0.00;
        $activeBonusesList = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('staff_bonuses_and_allowances')) {
            $startMonthStr = $noticeStartDate->format('Y-m');
            $exitMonthStr = $exitDateObj->format('Y-m');
            $bRecords = DB::table('staff_bonuses_and_allowances')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->where(function($q) use ($startMonthStr, $exitMonthStr) {
                    $q->where(function($sq) use ($startMonthStr, $exitMonthStr) {
                        $sq->where('frequency', 'one_time')
                           ->whereBetween('start_month', [$startMonthStr, $exitMonthStr]);
                    })->orWhere(function($sq) use ($startMonthStr) {
                        $sq->where('frequency', 'recurring')
                           ->where('start_month', '<=', $startMonthStr)
                           ->where(function($sub) use ($startMonthStr) {
                               $sub->whereNull('end_month')
                                   ->orWhere('end_month', '>=', $startMonthStr);
                           });
                    });
                })
                ->get();
            foreach ($bRecords as $br) {
                $amt = (float)$br->amount;
                $activeBonusesTotal += $amt;
                $activeBonusesList[] = [
                    'title'     => $br->title ?? 'Allowance / Bonus',
                    'category'  => $br->category,
                    'type'      => $br->type,
                    'amount'    => $amt,
                ];
            }
        }

        $earningVarsTotal = 0.00;
        $variableDeductionsTotal = 0.00;
        if (\Illuminate\Support\Facades\Schema::hasTable('staffEarningAndDeduction')) {
            $earningVarsTotal = (float)DB::table('staffEarningAndDeduction')
                ->where('staffId', $staffId)
                ->where('status', 1)
                ->where('variable_type', 'Earning')
                ->sum('amount');

            $variableDeductionsTotal = (float)DB::table('staffEarningAndDeduction')
                ->where('staffId', $staffId)
                ->where('status', 1)
                ->where('variable_type', 'Deduction')
                ->sum('amount');
        }

        // ── 4. Statutory PAYE Tax & Pension Calculation for Notice Period ──
        $targetDeclaredSalary = (float)($resignation->declared_salary ?? 0);
        $taxBaseSalary = $targetDeclaredSalary > 0 ? $targetDeclaredSalary : $monthlyGross;

        $isPensionActive = ((int)($resignation->pen_act ?? 0) === 1);
        $pensionRatePercent = !empty($resignation->pension_rate) ? ((float)$resignation->pension_rate / 100.0) : 0.08;
        $monthlyPension = $isPensionActive ? round(($monthlyGross * 0.5) * $pensionRatePercent, 2) : 0.00;
        
        $monthlyPayeTax = $this->calculateMonthlyPayeTax($taxBaseSalary, $monthlyPension);

        $totalNoticePayeTax = 0.00;
        $totalNoticePension = 0.00;
        foreach ($noticeBreakdown as $b) {
            if (!empty($b['is_full_month'])) {
                $totalNoticePayeTax += $monthlyPayeTax;
                $totalNoticePension += $monthlyPension;
            } else {
                $ratio = $b['days_in_month'] > 0 ? ($b['days_worked'] / (float)$b['days_in_month']) : 0;
                $totalNoticePayeTax += round($monthlyPayeTax * $ratio, 2);
                $totalNoticePension += round($monthlyPension * $ratio, 2);
            }
        }
        $totalNoticePayeTax = round($totalNoticePayeTax, 2);
        $totalNoticePension = round($totalNoticePension, 2);

        // ── 5. Itemized Outstanding Liabilities & Deductions ──
        $medLoan = 0.00;
        if (\Illuminate\Support\Facades\Schema::hasTable('medical_loan_deduction_setups')) {
            $medSetup = DB::table('medical_loan_deduction_setups')->where('staffId', $staffId)->first();
            if ($medSetup) {
                $medLoan = (float)($medSetup->balance_remaining ?? 0);
            }
        }

        $coopLoan = 0.00;
        if (\Illuminate\Support\Facades\Schema::hasTable('coop_loan_deduction_setups')) {
            $coopSetup = DB::table('coop_loan_deduction_setups')->where('staffId', $staffId)->first();
            if ($coopSetup) {
                $coopLoan = (float)($coopSetup->balance_remaining ?? 0);
            }
        }

        $coopAsset = 0.00;
        if (\Illuminate\Support\Facades\Schema::hasTable('coop_asset_finance_deduction_setups')) {
            $assetSetup = DB::table('coop_asset_finance_deduction_setups')->where('staffId', $staffId)->first();
            if ($assetSetup) {
                $coopAsset = (float)($assetSetup->balance_remaining ?? 0);
            }
        }

        $iouTotal = 0.00;
        if (\Illuminate\Support\Facades\Schema::hasTable('iou_records')) {
            $iouTotal = (float)DB::table('iou_records')
                ->where('staff_id', $staffId)
                ->where('status', '!=', 2)
                ->sum('amount');
        }

        $surchargeTotal = 0.00;
        if (\Illuminate\Support\Facades\Schema::hasTable('surcharge_deduction_setups')) {
            $surcharges = DB::table('surcharge_deduction_setups')->where('staffId', $staffId)->first();
            if ($surcharges) {
                $surchargeTotal = (float)($surcharges->balance_remaining ?? 0);
            }
        }

        $absencePenalty = 0.00;
        if (\Illuminate\Support\Facades\Schema::hasTable('absence_penalty_deduction_setups')) {
            $absPenalty = DB::table('absence_penalty_deduction_setups')->where('staffId', $staffId)->first();
            if ($absPenalty) {
                $absencePenalty = (float)($absPenalty->balance_remaining ?? $absPenalty->penalty_amount ?? 0);
            }
        }

        $regularLoan = 0.00;
        if (\Illuminate\Support\Facades\Schema::hasTable('employee_loans')) {
            $regLoan = DB::table('employee_loans')->where('staffId', $staffId)->whereRaw("LOWER(status) = 'approved'")->first();
            if ($regLoan) {
                $regularLoan = (float)($regLoan->balance ?? 0);
            }
        }

        $otherDeductTotal = 0.00;
        if (\Illuminate\Support\Facades\Schema::hasTable('other_deduction_setups')) {
            $otherD = DB::table('other_deduction_setups')->where('staffId', $staffId)->first();
            if ($otherD) {
                $otherDeductTotal = (float)($otherD->balance_remaining ?? 0);
            }
        }

        // Structured deductions mapping matching Salary Breakdown modal
        $itemizedDeductions = [
            ['name' => 'PAYE Tax',                         'amount' => $totalNoticePayeTax, 'note' => $totalNoticePayeTax > 0 ? 'Notice period tax' : 'Tax exempt (<= ₦800k threshold)'],
            ['name' => 'Pension (Employee Contribution)',   'amount' => $totalNoticePension, 'note' => $totalNoticePension > 0 ? 'Notice period pension (8% of 50%)' : ($isPensionActive ? 'Active' : 'Nil')],
            ['name' => 'Retention Savings',                 'amount' => 0.00, 'note' => '100% Refunded under Earnings'],
            ['name' => 'IOU Repayment',                     'amount' => $iouTotal, 'note' => $iouTotal > 0 ? 'Active advance balance' : 'Nil'],
            ['name' => 'Medical Loan',                      'amount' => $medLoan, 'note' => $medLoan > 0 ? 'Outstanding loan balance' : 'Nil'],
            ['name' => 'Cooperative Loan',                  'amount' => $coopLoan, 'note' => $coopLoan > 0 ? 'Outstanding coop loan balance' : 'Nil'],
            ['name' => 'Cooperative Savings',               'amount' => 0.00, 'note' => $coopSavingsRefund > 0 ? 'Accumulated balance refunded under Earnings' : 'Nil'],
            ['name' => 'Coop. Asset Financing',             'amount' => $coopAsset, 'note' => $coopAsset > 0 ? 'Outstanding asset finance' : 'Nil'],
            ['name' => 'Surcharges / Penalties',            'amount' => $surchargeTotal, 'note' => $surchargeTotal > 0 ? 'Unpaid penalty charges' : 'Nil'],
            ['name' => 'Absence Penalty',                   'amount' => $absencePenalty, 'note' => $absencePenalty > 0 ? 'Unpaid penalty' : 'Nil'],
            ['name' => 'Leave of Absence (Unpaid Days)',    'amount' => 0.00, 'note' => 'Nil'],
            ['name' => 'Regular Loan Repayment',            'amount' => $regularLoan, 'note' => $regularLoan > 0 ? 'Outstanding loan balance' : 'Nil'],
            ['name' => 'Other Deductions',                  'amount' => $otherDeductTotal, 'note' => $otherDeductTotal > 0 ? 'Other liabilities' : 'Nil'],
        ];

        $totalDeductions = round(array_sum(array_column($itemizedDeductions, 'amount')), 2);

        // ── 6. Total Earnings & Net Settlement Calculation ──
        $totalFinalEarnings = round($totalNoticeSalary + $retentionRefundAmount + $coopSavingsRefund + $activeBonusesTotal + $earningVarsTotal, 2);
        $netSettlement = round($totalFinalEarnings - $totalDeductions, 2);

        $settlementType = 'payable';
        if ($netSettlement < 0) {
            $settlementType = 'recoverable';
        } elseif ($netSettlement == 0.00) {
            $settlementType = 'balanced';
        }

        $staffFullName = trim("{$resignation->surname} {$resignation->first_name} {$resignation->othernames}");
        $staffEmail = trim($resignation->staff_email ?: ($resignation->staff_alternate_email ?: ($resignation->user_account_email ?: '')));

        return [
            'resignation_id'     => $resignation->id,
            'staff'              => [
                'id'               => $staffId,
                'name'             => $staffFullName,
                'file_no'          => $resignation->fileNo,
                'email'            => $staffEmail,
                'phone'            => $resignation->staff_phone,
                'department'       => $resignation->department ?? 'N/A',
                'appointment_date' => $resignation->appointment_date,
                'bank_name'        => $resignation->bank_name ?? 'N/A',
                'account_no'       => $resignation->account_no ?? 'N/A',
                'staff_status'     => (int)$resignation->staff_status,
                'status_value'     => $resignation->status_value,
            ],
            'timeline'           => [
                'resignation_date'  => $resignationDate,
                'notice_start_date' => $resignationDate,
                'notice_period_days'=> 30,
                'notice_completed'  => true,
                'exit_date'         => $exitDate,
                'admin_approved_at' => $resignation->admin_date,
                'approved_by'       => $resignation->approved_by_name ?? 'HR Head',
                'reason'            => $resignation->reason,
                'remarks'           => $resignation->remarks,
            ],
            'salary_structure'   => [
                'monthly_gross'       => $monthlyGross,
                'declared_salary'     => $taxBaseSalary,
                'basic_salary'        => $basicSalary,
                'housing_allowance'   => $housingAllowance,
                'transport_allowance' => $transportAllowance,
                'medical_allowance'   => $medicalAllowance,
                'utility_allowance'   => $utilityAllowance,
                'meal_allowance'      => $mealAllowance,
            ],
            'notice_earnings'    => [
                'breakdown'           => $noticeBreakdown,
                'total_notice_salary' => $totalNoticeSalary,
            ],
            'retention_refund'   => [
                'is_eligible'         => true,
                'monthly_rate'        => $monthlyRetentionRate,
                'months_deducted'     => $retentionMonthsRecorded,
                'total_refund_amount' => $retentionRefundAmount,
                'policy_note'         => '100% refund approved: Staff successfully satisfied compulsory 1-month notice period.',
            ],
            'coop_savings_refund' => [
                'total_savings_balance' => $coopSavingsRefund,
                'is_eligible'           => $coopSavingsRefund > 0,
            ],
            'bonuses_and_allowances' => [
                'total_amount' => $activeBonusesTotal + $earningVarsTotal,
                'items'        => $activeBonusesList,
            ],
            'deductions'         => [
                'itemized_deductions' => $itemizedDeductions,
                'total_deductions'    => $totalDeductions,
            ],
            'settlement_summary' => [
                'total_gross_notice_salary'   => $totalNoticeSalary,
                'total_retention_refund'      => $retentionRefundAmount,
                'total_coop_savings_refund'   => $coopSavingsRefund,
                'total_bonuses_allowances'    => $activeBonusesTotal + $earningVarsTotal,
                'total_final_earnings'        => $totalFinalEarnings,
                'total_final_deductions'      => $totalDeductions,
                'net_settlement_amount'       => $netSettlement,
                'settlement_type'             => $settlementType,
            ],
            'clearance_workflow' => [
                'hr_approval' => [
                    'status'      => (int)$resignation->admin_status,
                    'approved_at' => $resignation->admin_date,
                    'approved_by' => $resignation->approved_by_name ?? 'HR Head',
                    'remarks'     => $resignation->remarks,
                ],
                'audit_approval' => [
                    'status'      => (int)($resignation->audit_status ?? 0),
                    'audited_at'  => $resignation->audit_date,
                    'audited_by'  => $resignation->audit_by_name ?? 'Audit Head',
                    'remarks'     => $resignation->audit_remarks,
                ],
                'finance_payment' => [
                    'status'            => (int)($resignation->finance_status ?? 0),
                    'paid_at'           => $resignation->finance_date,
                    'paid_by'           => $resignation->finance_by_name ?? 'Finance Head',
                    'payment_reference' => $resignation->payment_reference,
                    'remarks'           => $resignation->finance_remarks,
                ],
            ],
        ];
    }

    /**
     * Dispatch Exit Settlement Breakdown HTML email with PDF Attachment to the resigned staff member.
     */
    public function sendSettlementBreakdownEmail(array $settlementData, ?string $overrideEmail = null, ?string $customNote = null): array
    {
        $recipientEmail = trim($overrideEmail ?: ($settlementData['staff']['email'] ?? ''));
        if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            Log::warning("Resignation settlement email skipped for staff ID {$settlementData['staff']['id']}: No valid email configured.");
            return [
                'sent'    => false,
                'email'   => null,
                'message' => 'Staff does not have a valid email address configured.'
            ];
        }

        $staffName = $settlementData['staff']['name'] ?? 'Staff Member';
        $fileNo = $settlementData['staff']['file_no'] ?? ($settlementData['staff']['id'] ?? '');
        $subject = "Exit Settlement Breakdown & Clearance Slip — {$staffName}" . ($fileNo ? " ({$fileNo})" : '');
        $sender = config('mail.from.address') ?: 'payroll@isalu.gov.ng';
        $senderName = config('mail.from.name') ?: 'Isalu Hospitals Limited — Finance & Payroll';

        $htmlContent = $this->buildSettlementSlipHtml($settlementData, $customNote);
        $pdfHtml = $this->buildSettlementSlipPdfHtml($settlementData, $customNote);

        // Generate official PDF slip attachment using DomPDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($pdfHtml)->setPaper('a4', 'portrait');
        $pdfData = $pdf->output();

        $safeFileNo = preg_replace('/[^A-Za-z0-9_-]/', '_', $fileNo ?: 'EXIT');
        $safeStaffName = preg_replace('/[^A-Za-z0-9_-]/', '_', $staffName);
        $pdfFileName = "Exit_Settlement_Slip_{$safeFileNo}_{$safeStaffName}.pdf";

        try {
            Mail::send([], [], function ($message) use ($recipientEmail, $staffName, $subject, $sender, $senderName, $htmlContent, $pdfData, $pdfFileName) {
                $message->to($recipientEmail, $staffName)
                    ->from($sender, $senderName)
                    ->subject($subject)
                    ->attachData($pdfData, $pdfFileName, [
                        'mime' => 'application/pdf',
                    ]);

                $message->setBody($htmlContent, 'text/html');
            });

            Log::info("Exit settlement breakdown email with PDF attachment sent successfully to {$recipientEmail} for Resignation ID {$settlementData['resignation_id']}");

            return [
                'sent'    => true,
                'email'   => $recipientEmail,
                'message' => "Settlement breakdown slip & PDF attachment emailed successfully to {$recipientEmail}."
            ];
        } catch (\Throwable $e) {
            Log::error("Failed sending exit settlement email to {$recipientEmail}: " . $e->getMessage());
            return [
                'sent'    => false,
                'email'   => $recipientEmail,
                'message' => 'Failed to deliver email: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Build HTML template replicating the Exit Settlement Breakdown & Clearance modal.
     */
    public function buildSettlementSlipHtml(array $data, ?string $customNote = null): string
    {
        $staff = $data['staff'] ?? [];
        $timeline = $data['timeline'] ?? [];
        $structure = $data['salary_structure'] ?? [];
        $notice = $data['notice_earnings'] ?? [];
        $retention = $data['retention_refund'] ?? [];
        $coopSavings = $data['coop_savings_refund'] ?? [];
        $bonuses = $data['bonuses_and_allowances'] ?? [];
        $deductions = $data['deductions'] ?? [];
        $summary = $data['settlement_summary'] ?? [];
        $workflow = $data['clearance_workflow'] ?? [];

        $fmt = function ($val) {
            return number_format((float)$val, 2);
        };

        $formatDate = function ($dateStr) {
            if (!$dateStr) return 'N/A';
            try {
                $d = new \DateTime($dateStr);
                return $d->format('d M, Y');
            } catch (\Throwable $e) {
                return $dateStr;
            }
        };

        $isRecoverable = ($summary['settlement_type'] ?? '') === 'recoverable' || ((float)($summary['net_settlement_amount'] ?? 0) < 0);
        $netAmountAbs = abs((float)($summary['net_settlement_amount'] ?? 0));

        // Format Notice rows
        $noticeRowsHtml = '';
        if (!empty($notice['breakdown'])) {
            foreach ($notice['breakdown'] as $b) {
                $daysWorked = $b['days_worked'] ?? 0;
                $daysInMonth = $b['days_in_month'] ?? 0;
                $monthName = htmlspecialchars($b['month_name'] ?? '');
                $earned = $fmt($b['earned_salary'] ?? 0);
                $isFull = !empty($b['is_full_month']) ? ' (Full Month)' : '';
                $noticeRowsHtml .= "
                    <tr style='background-color: #f0f7ff;'>
                        <td style='padding: 8px 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px;'>
                            <strong>Notice Salary: {$monthName}</strong>
                            <div style='font-size: 11px; color: #475569;'>Notice Days: {$daysWorked}/{$daysInMonth} days{$isFull}</div>
                        </td>
                        <td style='padding: 8px 12px; border-bottom: 1px solid #e2e8f0; text-align: right; font-size: 13px; font-weight: 600; color: #2563eb;'>
                            ₦{$earned}
                        </td>
                    </tr>
                ";
            }
        }

        // Retention refund row
        $retentionRefundAmount = (float)($retention['total_refund_amount'] ?? 0);
        $retentionMonths = $retention['months_deducted'] ?? 0;
        $retentionRate = $fmt($retention['monthly_rate'] ?? 0);
        $retentionRowHtml = "
            <tr style='background-color: #ecfdf5;'>
                <td style='padding: 8px 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px;'>
                    <strong style='color: #065f46;'>Retention Savings (100% Refund)</strong>
                    <div style='font-size: 11px; color: #047857;'>({$retentionMonths} mos @ ₦{$retentionRate} — compulsory notice satisfied)</div>
                </td>
                <td style='padding: 8px 12px; border-bottom: 1px solid #e2e8f0; text-align: right; font-size: 13px; font-weight: 700; color: #059669;'>
                    +₦{$fmt($retentionRefundAmount)}
                </td>
            </tr>
        ";

        // Coop Savings refund row
        $coopSavingsRowHtml = '';
        $coopSavingsBal = (float)($coopSavings['total_savings_balance'] ?? 0);
        if ($coopSavingsBal > 0) {
            $coopSavingsRowHtml = "
                <tr style='background-color: #ecfdf5;'>
                    <td style='padding: 8px 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px;'>
                        <strong style='color: #065f46;'>Cooperative Savings (Total Accumulated Refund)</strong>
                        <div style='font-size: 11px; color: #047857;'>(Staff Total Saved Personal Asset)</div>
                    </td>
                    <td style='padding: 8px 12px; border-bottom: 1px solid #e2e8f0; text-align: right; font-size: 13px; font-weight: 700; color: #059669;'>
                        +₦{$fmt($coopSavingsBal)}
                    </td>
                </tr>
            ";
        }

        // Bonuses row
        $bonusesRowHtml = '';
        $bonusesTotal = (float)($bonuses['total_amount'] ?? 0);
        if ($bonusesTotal > 0) {
            $itemNames = !empty($bonuses['items']) ? implode(', ', array_map(function ($i) { return htmlspecialchars($i['title'] ?? ''); }, $bonuses['items'])) : 'Allowances';
            $bonusesRowHtml = "
                <tr style='background-color: #fffbeb;'>
                    <td style='padding: 8px 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px;'>
                        <strong style='color: #92400e;'>Bonuses & Special Allowances</strong>
                        <div style='font-size: 11px; color: #b45309;'>({$itemNames})</div>
                    </td>
                    <td style='padding: 8px 12px; border-bottom: 1px solid #e2e8f0; text-align: right; font-size: 13px; font-weight: 700; color: #d97706;'>
                        +₦{$fmt($bonusesTotal)}
                    </td>
                </tr>
            ";
        }

        // Deductions itemized rows
        $deductionRowsHtml = '';
        if (!empty($deductions['itemized_deductions'])) {
            foreach ($deductions['itemized_deductions'] as $d) {
                $dName = htmlspecialchars($d['name'] ?? '');
                $dAmt = (float)($d['amount'] ?? 0);
                $dNote = htmlspecialchars($d['note'] ?? '');
                $color = $dAmt > 0 ? '#dc2626' : '#94a3b8';
                $formattedAmt = $fmt($dAmt);

                $extraBadge = '';
                if (stripos($dName, 'Retention') !== false) {
                    $extraBadge = "<span style='color: #059669; font-size: 11px; font-weight: 600;'> (Refunded)</span>";
                } elseif ($dName === 'PAYE Tax' && $dAmt == 0) {
                    $extraBadge = "<span style='color: #64748b; font-size: 11px;'> (Exempt ≤₦800k)</span>";
                } elseif (stripos($dName, 'Pension') !== false && $dAmt == 0) {
                    $extraBadge = "<span style='color: #64748b; font-size: 11px;'> (Not Enrolled)</span>";
                } elseif (stripos($dName, 'Savings') !== false) {
                    $extraBadge = "<span style='color: #059669; font-size: 11px; font-weight: 600;'> (Refunded under Earnings)</span>";
                }

                $deductionRowsHtml .= "
                    <tr>
                        <td style='padding: 8px 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #1e293b;'>
                            {$dName}{$extraBadge}
                        </td>
                        <td style='padding: 8px 12px; border-bottom: 1px solid #e2e8f0; text-align: right; font-size: 13px; font-weight: 600; color: {$color};'>
                            ₦{$formattedAmt}
                        </td>
                    </tr>
                ";
            }
        }

        $customNoteHtml = '';
        if (!empty($customNote)) {
            $customNoteEsc = nl2br(htmlspecialchars($customNote));
            $customNoteHtml = "
                <div style='margin-top: 16px; padding: 12px 16px; background-color: #f1f5f9; border-left: 4px solid #3b82f6; border-radius: 4px; font-size: 13px; color: #334155;'>
                    <strong>Finance Note:</strong> {$customNoteEsc}
                </div>
            ";
        }

        $staffName = htmlspecialchars($staff['name'] ?? 'N/A');
        $staffId = htmlspecialchars($staff['id'] ?? 'N/A');
        $department = htmlspecialchars($staff['department'] ?? 'N/A');
        $bankAccount = htmlspecialchars(($staff['bank_name'] ?? 'N/A') . ' — ' . ($staff['account_no'] ?? 'N/A'));
        $noticeDate = $formatDate($timeline['resignation_date'] ?? '');
        $exitDate = $formatDate($timeline['exit_date'] ?? '');
        $declaredSalary = $fmt($structure['declared_salary'] ?? 0);

        $totalEarnings = $fmt($summary['total_final_earnings'] ?? 0);
        $totalDeductions = $fmt($summary['total_final_deductions'] ?? 0);
        $netSettlementFormatted = $fmt($netAmountAbs);

        $netBannerTitle = $isRecoverable ? 'FINAL NET RECOVERABLE FROM STAFF' : 'FINAL NET PAYABLE TO STAFF';
        $netBannerBg = $isRecoverable ? '#fff1f2' : '#ecfdf5';
        $netBannerBorder = $isRecoverable ? '#f43f5e' : '#10b981';
        $netBannerColor = $isRecoverable ? '#be123c' : '#047857';

        // Workflow Details
        $hrName = htmlspecialchars($workflow['hr_approval']['approved_by'] ?? 'HR Head');
        $hrDate = $formatDate($workflow['hr_approval']['approved_at'] ?? '');
        $auditName = htmlspecialchars($workflow['audit_approval']['audited_by'] ?? 'Audit Head');
        $auditDate = $formatDate($workflow['audit_approval']['audited_at'] ?? '');
        $financeName = htmlspecialchars($workflow['finance_payment']['paid_by'] ?? 'Finance Head');
        $financeDate = $formatDate($workflow['finance_payment']['paid_at'] ?? now());
        $paymentRef = htmlspecialchars($workflow['finance_payment']['payment_reference'] ?: 'N/A');
        $financeRemarks = htmlspecialchars($workflow['finance_payment']['remarks'] ?: ($isRecoverable ? 'Debt recovery recorded and cleared.' : 'Settlement paid and cleared.'));

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Exit Settlement Breakdown & Clearance</title>
        </head>
        <body style='margin: 0; padding: 20px; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; color: #1e293b; line-height: 1.5;'>
            <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='max-width: 680px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #cbd5e1; box-shadow: 0 4px 12px rgba(0,0,0,0.06);'>
                
                <!-- HEADER BAR -->
                <tr>
                    <td style='background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 24px 28px; text-align: center; color: #ffffff;'>
                        <div style='font-size: 13px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #38bdf8; margin-bottom: 4px;'>ISALU HOSPITALS LIMITED</div>
                        <h1 style='margin: 0; font-size: 20px; font-weight: 700; color: #ffffff;'>Exit Settlement Breakdown & Clearance</h1>
                        <div style='font-size: 12px; color: #94a3b8; margin-top: 4px;'>Official Final Settlement Statement & Statutory Clearance Slip</div>
                    </td>
                </tr>

                <!-- MAIN CONTENT AREA -->
                <tr>
                    <td style='padding: 24px 28px;'>
                        
                        <!-- PDF ATTACHMENT NOTICE BANNER -->
                        <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; margin-bottom: 20px;'>
                            <tr>
                                <td style='padding: 12px 16px; font-size: 12px; color: #1e40af;'>
                                    <strong>&#128206; Official PDF Slip Attached:</strong> Your formal Exit Settlement Breakdown & Clearance Slip is attached to this email as a PDF document for your records and download.
                                </td>
                            </tr>
                        </table>

                        <!-- STAFF METADATA BOX -->
                        <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 20px;'>
                            <tr>
                                <td style='padding: 16px 18px;'>
                                    <table role='presentation' width='100%' cellpadding='0' cellspacing='0'>
                                        <tr>
                                            <td style='width: 50%; padding-bottom: 10px; vertical-align: top;'>
                                                <div style='font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;'>STAFF NAME:</div>
                                                <div style='font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 2px;'>{$staffName}</div>
                                            </td>
                                            <td style='width: 50%; padding-bottom: 10px; vertical-align: top;'>
                                                <div style='font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;'>STAFF ID:</div>
                                                <div style='font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 2px;'>{$staffId}</div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style='width: 50%; padding-bottom: 10px; vertical-align: top;'>
                                                <div style='font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;'>DEPARTMENT:</div>
                                                <div style='font-size: 13px; font-weight: 600; color: #334155; margin-top: 2px;'>{$department}</div>
                                            </td>
                                            <td style='width: 50%; padding-bottom: 10px; vertical-align: top;'>
                                                <div style='font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;'>BANK & ACCOUNT:</div>
                                                <div style='font-size: 13px; font-weight: 600; color: #334155; margin-top: 2px;'>{$bankAccount}</div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style='width: 50%; padding-bottom: 10px; vertical-align: top;'>
                                                <div style='font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;'>NOTICE SUBMISSION DATE:</div>
                                                <div style='font-size: 13px; font-weight: 600; color: #0f172a; margin-top: 2px;'>{$noticeDate} (30 Days)</div>
                                            </td>
                                            <td style='width: 50%; padding-bottom: 10px; vertical-align: top;'>
                                                <div style='font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;'>EFFECTIVE EXIT DATE:</div>
                                                <div style='font-size: 13px; font-weight: 700; color: #db2777; margin-top: 2px;'>{$exitDate}</div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style='width: 50%; vertical-align: top;'>
                                                <div style='font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;'>PAYROLL STATUS:</div>
                                                <div style='font-size: 13px; font-weight: 700; color: #d97706; margin-top: 2px;'>Removed from Active Payroll</div>
                                            </td>
                                            <td style='width: 50%; vertical-align: top;'>
                                                <div style='font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;'>DECLARED BASE SALARY:</div>
                                                <div style='font-size: 13px; font-weight: 700; color: #2563eb; margin-top: 2px;'>₦{$declaredSalary}</div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- DUAL COLUMN BREAKDOWN TABLES -->
                        <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='margin-bottom: 24px;'>
                            
                            <!-- SECTION 1: EARNINGS & REFUNDS -->
                            <tr>
                                <td>
                                    <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='border: 1px solid #bfdbfe; border-radius: 8px; overflow: hidden; margin-bottom: 20px;'>
                                        <thead>
                                            <tr style='background-color: #eff6ff;'>
                                                <th style='padding: 10px 12px; text-align: left; font-size: 12px; font-weight: 700; color: #1d4ed8; letter-spacing: 0.5px; text-transform: uppercase;'>EARNINGS & REFUNDS (ASSETS)</th>
                                                <th style='padding: 10px 12px; text-align: right; font-size: 12px; font-weight: 700; color: #1d4ed8; letter-spacing: 0.5px; text-transform: uppercase; width: 130px;'>AMOUNT (₦)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style='padding: 7px 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px;'>Basic Salary</td>
                                                <td style='padding: 7px 12px; border-bottom: 1px solid #e2e8f0; text-align: right; font-size: 13px;'>{$fmt($structure['basic_salary'] ?? 0)}</td>
                                            </tr>
                                            <tr>
                                                <td style='padding: 7px 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px;'>Housing Allowance</td>
                                                <td style='padding: 7px 12px; border-bottom: 1px solid #e2e8f0; text-align: right; font-size: 13px;'>{$fmt($structure['housing_allowance'] ?? 0)}</td>
                                            </tr>
                                            <tr>
                                                <td style='padding: 7px 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px;'>Transport Allowance</td>
                                                <td style='padding: 7px 12px; border-bottom: 1px solid #e2e8f0; text-align: right; font-size: 13px;'>{$fmt($structure['transport_allowance'] ?? 0)}</td>
                                            </tr>
                                            <tr>
                                                <td style='padding: 7px 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px;'>Medical Allowance</td>
                                                <td style='padding: 7px 12px; border-bottom: 1px solid #e2e8f0; text-align: right; font-size: 13px;'>{$fmt($structure['medical_allowance'] ?? 0)}</td>
                                            </tr>
                                            <tr>
                                                <td style='padding: 7px 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px;'>Utility Allowance</td>
                                                <td style='padding: 7px 12px; border-bottom: 1px solid #e2e8f0; text-align: right; font-size: 13px;'>{$fmt($structure['utility_allowance'] ?? 0)}</td>
                                            </tr>
                                            " . (((float)($structure['meal_allowance'] ?? 0) > 0) ? "
                                            <tr>
                                                <td style='padding: 7px 12px; border-bottom: 1px solid #e2e8f0; font-size: 13px;'>Meal Allowance</td>
                                                <td style='padding: 7px 12px; border-bottom: 1px solid #e2e8f0; text-align: right; font-size: 13px;'>{$fmt($structure['meal_allowance'] ?? 0)}</td>
                                            </tr>" : "") . "
                                            {$noticeRowsHtml}
                                            {$retentionRowHtml}
                                            {$coopSavingsRowHtml}
                                            {$bonusesRowHtml}
                                            <tr style='background-color: #f0fdf4;'>
                                                <td style='padding: 10px 12px; font-size: 13px; font-weight: 700; color: #166534; text-transform: uppercase;'>TOTAL FINAL EARNINGS & REFUNDS</td>
                                                <td style='padding: 10px 12px; text-align: right; font-size: 14px; font-weight: 700; color: #166534;'>₦{$totalEarnings}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>

                            <!-- SECTION 2: ITEMIZED DEDUCTIONS & LIABILITIES -->
                            <tr>
                                <td>
                                    <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='border: 1px solid #fca5a5; border-radius: 8px; overflow: hidden; margin-bottom: 20px;'>
                                        <thead>
                                            <tr style='background-color: #fef2f2;'>
                                                <th style='padding: 10px 12px; text-align: left; font-size: 12px; font-weight: 700; color: #b91c1c; letter-spacing: 0.5px; text-transform: uppercase;'>ITEMIZED DEDUCTIONS & LIABILITIES</th>
                                                <th style='padding: 10px 12px; text-align: right; font-size: 12px; font-weight: 700; color: #b91c1c; letter-spacing: 0.5px; text-transform: uppercase; width: 130px;'>AMOUNT (₦)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {$deductionRowsHtml}
                                            <tr style='background-color: #fff1f2;'>
                                                <td style='padding: 10px 12px; font-size: 13px; font-weight: 700; color: #991b1b; text-transform: uppercase;'>TOTAL DEDUCTIONS & LIABILITIES</td>
                                                <td style='padding: 10px 12px; text-align: right; font-size: 14px; font-weight: 700; color: #b91c1c;'>- ₦{$totalDeductions}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- FINAL NET TAKE-HOME / SETTLEMENT BANNER -->
                        <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='background-color: {$netBannerBg}; border: 2px solid {$netBannerBorder}; border-radius: 10px; margin-bottom: 20px; text-align: center;'>
                            <tr>
                                <td style='padding: 18px 20px;'>
                                    <div style='font-size: 12px; font-weight: 800; color: {$netBannerColor}; text-transform: uppercase; letter-spacing: 1px;'>{$netBannerTitle}</div>
                                    <div style='font-size: 11px; color: #64748b; margin-top: 3px;'>Total Final Earnings & Refunds minus Total Itemized Deductions & Liabilities</div>
                                    <div style='font-size: 28px; font-weight: 800; color: {$netBannerColor}; margin-top: 8px; letter-spacing: 0.5px;'>
                                        ₦{$netSettlementFormatted}
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <!-- CLEARANCE & PAYMENT AUDIT TRAIL -->
                        <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 15px;'>
                            <tr>
                                <td style='padding: 14px 16px;'>
                                    <div style='font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;'>Clearance Workflow & Verification Record</div>
                                    <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='font-size: 12px; color: #334155;'>
                                        <tr>
                                            <td style='padding: 3px 0;'><strong>1. HR Head Approval:</strong> Approved by {$hrName} on {$hrDate}</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 3px 0;'><strong>2. Audit Head Review:</strong> Audited & Cleared by {$auditName} on {$auditDate}</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 3px 0;'><strong>3. Finance Head Clearance:</strong> Cleared & Marked Paid by {$financeName} on {$financeDate} (Ref: <strong>{$paymentRef}</strong>)</td>
                                        </tr>
                                        <tr>
                                            <td style='padding: 3px 0; color: #64748b;'><em>Remarks: {$financeRemarks}</em></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        {$customNoteHtml}

                    </td>
                </tr>

                <!-- FOOTER -->
                <tr>
                    <td style='background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 18px 24px; text-align: center; font-size: 11px; color: #64748b;'>
                        <p style='margin: 0 0 4px 0;'>This is an official Exit Settlement & Clearance notification issued by the Finance & Payroll Department of <strong>Isalu Hospitals Limited</strong>.</p>
                        <p style='margin: 0;'>Please retain this document for your personal records. For inquiries, kindly contact the Finance / HR Office.</p>
                    </td>
                </tr>

            </table>
        </body>
        </html>
        ";
    }

    /**
     * GET /api/nextjs/payroll/resignations/settlement/{id}/download-pdf
     * Download the official Exit Settlement Breakdown & Clearance PDF Slip.
     */
    public function downloadSettlementPdf(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $settlementData = $this->computeDetailedSettlement($id);
            if (!$settlementData) {
                return response()->json(['status' => 'error', 'message' => 'Approved resignation record not found.'], 404);
            }

            $pdfHtml = $this->buildSettlementSlipPdfHtml($settlementData);
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($pdfHtml)->setPaper('a4', 'portrait');

            $fileNo = preg_replace('/[^A-Za-z0-9_-]/', '_', $settlementData['staff']['file_no'] ?? $id);
            $staffName = preg_replace('/[^A-Za-z0-9_-]/', '_', $settlementData['staff']['name'] ?? 'Staff');
            $fileName = "Exit_Settlement_Slip_{$fileNo}_{$staffName}.pdf";

            return $pdf->download($fileName);
        } catch (\Throwable $th) {
            Log::error('ResignationApiController downloadSettlementPdf: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * Build clean, standalone printable PDF HTML template specifically styled for DomPDF conversion.
     */
    public function buildSettlementSlipPdfHtml(array $data, ?string $customNote = null): string
    {
        $staff = $data['staff'] ?? [];
        $timeline = $data['timeline'] ?? [];
        $structure = $data['salary_structure'] ?? [];
        $notice = $data['notice_earnings'] ?? [];
        $retention = $data['retention_refund'] ?? [];
        $coopSavings = $data['coop_savings_refund'] ?? [];
        $bonuses = $data['bonuses_and_allowances'] ?? [];
        $deductions = $data['deductions'] ?? [];
        $summary = $data['settlement_summary'] ?? [];
        $workflow = $data['clearance_workflow'] ?? [];

        $fmt = function ($val) {
            return number_format((float)$val, 2);
        };

        $formatDate = function ($dateStr) {
            if (!$dateStr) return 'N/A';
            try {
                $d = new \DateTime($dateStr);
                return $d->format('d M, Y');
            } catch (\Throwable $e) {
                return $dateStr;
            }
        };

        $isRecoverable = ($summary['settlement_type'] ?? '') === 'recoverable' || ((float)($summary['net_settlement_amount'] ?? 0) < 0);
        $netAmountAbs = abs((float)($summary['net_settlement_amount'] ?? 0));

        // Format Notice rows
        $noticeRowsHtml = '';
        if (!empty($notice['breakdown'])) {
            foreach ($notice['breakdown'] as $b) {
                $daysWorked = $b['days_worked'] ?? 0;
                $daysInMonth = $b['days_in_month'] ?? 0;
                $monthName = htmlspecialchars($b['month_name'] ?? '');
                $earned = $fmt($b['earned_salary'] ?? 0);
                $isFull = !empty($b['is_full_month']) ? ' (Full Month)' : '';
                $noticeRowsHtml .= "
                    <tr style='background-color: #f0f7ff;'>
                        <td style='padding: 5px 8px; border: 1px solid #cbd5e1; font-size: 10px;'>
                            <strong>Notice Salary: {$monthName}</strong>
                            <div style='font-size: 9px; color: #475569;'>Notice Days: {$daysWorked}/{$daysInMonth} days{$isFull}</div>
                        </td>
                        <td style='padding: 5px 8px; border: 1px solid #cbd5e1; text-align: right; font-size: 10px; font-weight: bold; color: #1d4ed8;'>
                            &#8358;{$earned}
                        </td>
                    </tr>
                ";
            }
        }

        // Retention refund row
        $retentionRefundAmount = (float)($retention['total_refund_amount'] ?? 0);
        $retentionMonths = $retention['months_deducted'] ?? 0;
        $retentionRate = $fmt($retention['monthly_rate'] ?? 0);
        $retentionRowHtml = "
            <tr style='background-color: #ecfdf5;'>
                <td style='padding: 5px 8px; border: 1px solid #cbd5e1; font-size: 10px;'>
                    <strong style='color: #065f46;'>Retention Savings (100% Refund)</strong>
                    <div style='font-size: 9px; color: #047857;'>({$retentionMonths} mos @ &#8358;{$retentionRate} — 1-month notice satisfied)</div>
                </td>
                <td style='padding: 5px 8px; border: 1px solid #cbd5e1; text-align: right; font-size: 10px; font-weight: bold; color: #059669;'>
                    +&#8358;{$fmt($retentionRefundAmount)}
                </td>
            </tr>
        ";

        // Coop Savings refund row
        $coopSavingsRowHtml = '';
        $coopSavingsBal = (float)($coopSavings['total_savings_balance'] ?? 0);
        if ($coopSavingsBal > 0) {
            $coopSavingsRowHtml = "
                <tr style='background-color: #ecfdf5;'>
                    <td style='padding: 5px 8px; border: 1px solid #cbd5e1; font-size: 10px;'>
                        <strong style='color: #065f46;'>Cooperative Savings (Total Refund)</strong>
                        <div style='font-size: 9px; color: #047857;'>(Staff Total Saved Personal Asset)</div>
                    </td>
                    <td style='padding: 5px 8px; border: 1px solid #cbd5e1; text-align: right; font-size: 10px; font-weight: bold; color: #059669;'>
                        +&#8358;{$fmt($coopSavingsBal)}
                    </td>
                </tr>
            ";
        }

        // Bonuses row
        $bonusesRowHtml = '';
        $bonusesTotal = (float)($bonuses['total_amount'] ?? 0);
        if ($bonusesTotal > 0) {
            $itemNames = !empty($bonuses['items']) ? implode(', ', array_map(function ($i) { return htmlspecialchars($i['title'] ?? ''); }, $bonuses['items'])) : 'Allowances';
            $bonusesRowHtml = "
                <tr style='background-color: #fffbeb;'>
                    <td style='padding: 5px 8px; border: 1px solid #cbd5e1; font-size: 10px;'>
                        <strong style='color: #92400e;'>Bonuses & Special Allowances</strong>
                        <div style='font-size: 9px; color: #b45309;'>({$itemNames})</div>
                    </td>
                    <td style='padding: 5px 8px; border: 1px solid #cbd5e1; text-align: right; font-size: 10px; font-weight: bold; color: #d97706;'>
                        +&#8358;{$fmt($bonusesTotal)}
                    </td>
                </tr>
            ";
        }

        // Deductions itemized rows
        $deductionRowsHtml = '';
        if (!empty($deductions['itemized_deductions'])) {
            foreach ($deductions['itemized_deductions'] as $d) {
                $dName = htmlspecialchars($d['name'] ?? '');
                $dAmt = (float)($d['amount'] ?? 0);
                $dNote = htmlspecialchars($d['note'] ?? '');
                $color = $dAmt > 0 ? '#b91c1c' : '#64748b';
                $formattedAmt = $fmt($dAmt);

                $extraBadge = '';
                if (stripos($dName, 'Retention') !== false) {
                    $extraBadge = "<span style='color: #059669; font-size: 9px; font-weight: bold;'> (Refunded)</span>";
                } elseif ($dName === 'PAYE Tax' && $dAmt == 0) {
                    $extraBadge = "<span style='color: #64748b; font-size: 9px;'> (Exempt)</span>";
                } elseif (stripos($dName, 'Pension') !== false && $dAmt == 0) {
                    $extraBadge = "<span style='color: #64748b; font-size: 9px;'> (Not Enrolled)</span>";
                }

                $deductionRowsHtml .= "
                    <tr>
                        <td style='padding: 5px 8px; border: 1px solid #cbd5e1; font-size: 10px;'>
                            {$dName}{$extraBadge}
                        </td>
                        <td style='padding: 5px 8px; border: 1px solid #cbd5e1; text-align: right; font-size: 10px; font-weight: bold; color: {$color};'>
                            &#8358;{$formattedAmt}
                        </td>
                    </tr>
                ";
            }
        }

        $customNoteHtml = '';
        if (!empty($customNote)) {
            $customNoteEsc = nl2br(htmlspecialchars($customNote));
            $customNoteHtml = "
                <div style='margin-top: 10px; padding: 8px 12px; background-color: #f1f5f9; border-left: 3px solid #2563eb; font-size: 9.5px; color: #334155;'>
                    <strong>Finance Note:</strong> {$customNoteEsc}
                </div>
            ";
        }

        $staffName = htmlspecialchars($staff['name'] ?? 'N/A');
        $staffFileNo = htmlspecialchars($staff['file_no'] ?? ($staff['id'] ?? 'N/A'));
        $department = htmlspecialchars($staff['department'] ?? 'N/A');
        $bankAccount = htmlspecialchars(($staff['bank_name'] ?? 'N/A') . ' (' . ($staff['account_no'] ?? 'N/A') . ')');
        $noticeDate = $formatDate($timeline['resignation_date'] ?? '');
        $exitDate = $formatDate($timeline['exit_date'] ?? '');
        $declaredSalary = $fmt($structure['declared_salary'] ?? 0);

        $totalEarnings = $fmt($summary['total_final_earnings'] ?? 0);
        $totalDeductions = $fmt($summary['total_final_deductions'] ?? 0);
        $netSettlementFormatted = $fmt($netAmountAbs);

        $netBannerTitle = $isRecoverable ? 'FINAL NET AMOUNT RECOVERABLE FROM STAFF' : 'FINAL NET AMOUNT PAYABLE TO STAFF';
        $netBannerBg = $isRecoverable ? '#fff1f2' : '#ecfdf5';
        $netBannerBorder = $isRecoverable ? '#e11d48' : '#059669';
        $netBannerColor = $isRecoverable ? '#9f1239' : '#065f46';

        // Workflow Details
        $hrName = htmlspecialchars($workflow['hr_approval']['approved_by'] ?? 'HR Head');
        $hrDate = $formatDate($workflow['hr_approval']['approved_at'] ?? '');
        $auditName = htmlspecialchars($workflow['audit_approval']['audited_by'] ?? 'Audit Head');
        $auditDate = $formatDate($workflow['audit_approval']['audited_at'] ?? '');
        $financeName = htmlspecialchars($workflow['finance_payment']['paid_by'] ?? 'Finance Head');
        $financeDate = $formatDate($workflow['finance_payment']['paid_at'] ?? now());
        $paymentRef = htmlspecialchars($workflow['finance_payment']['payment_reference'] ?: 'N/A');
        $financeRemarks = htmlspecialchars($workflow['finance_payment']['remarks'] ?: ($isRecoverable ? 'Debt recovery recorded and cleared.' : 'Settlement paid and cleared.'));

        $generatedAt = date('d M, Y H:i:s');

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
            <title>Exit Settlement Breakdown & Clearance Slip</title>
            <style>
                @page {
                    margin: 10mm 12mm 10mm 12mm;
                    size: a4 portrait;
                }
                body {
                    font-family: 'DejaVu Sans', sans-serif;
                    color: #0f172a;
                    font-size: 9.5px;
                    line-height: 1.35;
                    margin: 0;
                    padding: 0;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .header-table {
                    margin-bottom: 8px;
                    border-bottom: 2px solid #0f172a;
                    padding-bottom: 6px;
                }
                .meta-table {
                    background-color: #f8fafc;
                    border: 1px solid #cbd5e1;
                    margin-bottom: 10px;
                }
                .meta-table td {
                    padding: 5px 8px;
                    vertical-align: top;
                }
                .label {
                    font-size: 8px;
                    font-weight: bold;
                    color: #64748b;
                    text-transform: uppercase;
                }
                .val {
                    font-size: 10.5px;
                    font-weight: bold;
                    color: #0f172a;
                }
                .section-header {
                    padding: 5px 8px;
                    font-size: 9.5px;
                    font-weight: bold;
                    text-transform: uppercase;
                    border: 1px solid #cbd5e1;
                }
                .table-earnings th {
                    background-color: #eff6ff;
                    color: #1e40af;
                }
                .table-deductions th {
                    background-color: #fef2f2;
                    color: #991b1b;
                }
                .banner-box {
                    border: 1.5px solid {$netBannerBorder};
                    background-color: {$netBannerBg};
                    padding: 8px;
                    text-align: center;
                    margin-top: 8px;
                    margin-bottom: 10px;
                }
                .audit-box {
                    background-color: #f8fafc;
                    border: 1px solid #e2e8f0;
                    padding: 6px 8px;
                    margin-bottom: 10px;
                }
                .sign-table {
                    margin-top: 14px;
                    margin-bottom: 8px;
                }
                .sign-table td {
                    padding: 4px 6px;
                    vertical-align: bottom;
                }
                .sign-line {
                    border-top: 1px solid #0f172a;
                    margin-top: 25px;
                    font-size: 8.5px;
                    font-weight: bold;
                    text-align: center;
                    padding-top: 2px;
                }
                .footer {
                    border-top: 1px solid #cbd5e1;
                    padding-top: 4px;
                    font-size: 7.5px;
                    color: #64748b;
                    text-align: center;
                }
            </style>
        </head>
        <body>
            <!-- HEADER -->
            <table class='header-table'>
                <tr>
                    <td style='width: 70%;'>
                        <div style='font-size: 14px; font-weight: bold; color: #0f172a; letter-spacing: 0.5px;'>ISALU HOSPITALS LIMITED</div>
                        <div style='font-size: 11px; font-weight: bold; color: #2563eb; margin-top: 1px;'>EXIT SETTLEMENT BREAKDOWN & STATUTORY CLEARANCE SLIP</div>
                        <div style='font-size: 8px; color: #64748b;'>Finance & Payroll Department &bull; Official Settlement Document</div>
                    </td>
                    <td style='width: 30%; text-align: right; vertical-align: top;'>
                        <div style='font-size: 8px; color: #64748b;'>Generated On:</div>
                        <div style='font-size: 9px; font-weight: bold;'>{$generatedAt}</div>
                        <div style='font-size: 8px; color: #059669; font-weight: bold; margin-top: 2px;'>Status: HR & Audit Cleared</div>
                    </td>
                </tr>
            </table>

            <!-- STAFF METADATA -->
            <table class='meta-table'>
                <tr>
                    <td style='width: 25%;'>
                        <div class='label'>Staff Name</div>
                        <div class='val'>{$staffName}</div>
                    </td>
                    <td style='width: 25%;'>
                        <div class='label'>File No / Staff ID</div>
                        <div class='val'>{$staffFileNo}</div>
                    </td>
                    <td style='width: 25%;'>
                        <div class='label'>Department</div>
                        <div class='val'>{$department}</div>
                    </td>
                    <td style='width: 25%;'>
                        <div class='label'>Bank & Account</div>
                        <div class='val' style='font-size: 9.5px;'>{$bankAccount}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class='label'>Notice Date</div>
                        <div class='val'>{$noticeDate}</div>
                    </td>
                    <td>
                        <div class='label'>Effective Exit Date</div>
                        <div class='val' style='color: #db2777;'>{$exitDate}</div>
                    </td>
                    <td>
                        <div class='label'>Payroll Status</div>
                        <div class='val' style='color: #d97706;'>Resigned / Off-Payroll</div>
                    </td>
                    <td>
                        <div class='label'>Declared Base Salary</div>
                        <div class='val' style='color: #2563eb;'>&#8358;{$declaredSalary}</div>
                    </td>
                </tr>
            </table>

            <!-- DUAL TABLE: EARNINGS AND DEDUCTIONS -->
            <table>
                <tr>
                    <!-- LEFT COLUMN: EARNINGS & REFUNDS -->
                    <td style='width: 50%; vertical-align: top; padding-right: 5px;'>
                        <table class='table-earnings'>
                            <thead>
                                <tr>
                                    <th class='section-header' style='text-align: left; background-color: #eff6ff; color: #1e40af;'>Final Earnings & Refunds</th>
                                    <th class='section-header' style='text-align: right; width: 85px; background-color: #eff6ff; color: #1e40af;'>Amount (&#8358;)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style='padding: 4px 8px; border: 1px solid #cbd5e1;'>Basic Salary</td>
                                    <td style='padding: 4px 8px; border: 1px solid #cbd5e1; text-align: right;'>{$fmt($structure['basic_salary'] ?? 0)}</td>
                                </tr>
                                <tr>
                                    <td style='padding: 4px 8px; border: 1px solid #cbd5e1;'>Housing Allowance</td>
                                    <td style='padding: 4px 8px; border: 1px solid #cbd5e1; text-align: right;'>{$fmt($structure['housing_allowance'] ?? 0)}</td>
                                </tr>
                                <tr>
                                    <td style='padding: 4px 8px; border: 1px solid #cbd5e1;'>Transport Allowance</td>
                                    <td style='padding: 4px 8px; border: 1px solid #cbd5e1; text-align: right;'>{$fmt($structure['transport_allowance'] ?? 0)}</td>
                                </tr>
                                <tr>
                                    <td style='padding: 4px 8px; border: 1px solid #cbd5e1;'>Medical Allowance</td>
                                    <td style='padding: 4px 8px; border: 1px solid #cbd5e1; text-align: right;'>{$fmt($structure['medical_allowance'] ?? 0)}</td>
                                </tr>
                                <tr>
                                    <td style='padding: 4px 8px; border: 1px solid #cbd5e1;'>Utility Allowance</td>
                                    <td style='padding: 4px 8px; border: 1px solid #cbd5e1; text-align: right;'>{$fmt($structure['utility_allowance'] ?? 0)}</td>
                                </tr>
                                " . (((float)($structure['meal_allowance'] ?? 0) > 0) ? "
                                <tr>
                                    <td style='padding: 4px 8px; border: 1px solid #cbd5e1;'>Meal Allowance</td>
                                    <td style='padding: 4px 8px; border: 1px solid #cbd5e1; text-align: right;'>{$fmt($structure['meal_allowance'] ?? 0)}</td>
                                </tr>" : "") . "
                                {$noticeRowsHtml}
                                {$retentionRowHtml}
                                {$coopSavingsRowHtml}
                                {$bonusesRowHtml}
                                <tr style='background-color: #f0fdf4;'>
                                    <td style='padding: 6px 8px; border: 1px solid #cbd5e1; font-weight: bold; color: #166534;'>TOTAL FINAL EARNINGS & REFUNDS</td>
                                    <td style='padding: 6px 8px; border: 1px solid #cbd5e1; text-align: right; font-weight: bold; color: #166534;'>&#8358;{$totalEarnings}</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>

                    <!-- RIGHT COLUMN: DEDUCTIONS & LIABILITIES -->
                    <td style='width: 50%; vertical-align: top; padding-left: 5px;'>
                        <table class='table-deductions'>
                            <thead>
                                <tr>
                                    <th class='section-header' style='text-align: left; background-color: #fef2f2; color: #991b1b;'>Itemized Deductions & Liabilities</th>
                                    <th class='section-header' style='text-align: right; width: 85px; background-color: #fef2f2; color: #991b1b;'>Amount (&#8358;)</th>
                                </tr>
                            </thead>
                            <tbody>
                                {$deductionRowsHtml}
                                <tr style='background-color: #fff1f2;'>
                                    <td style='padding: 6px 8px; border: 1px solid #cbd5e1; font-weight: bold; color: #991b1b;'>TOTAL DEDUCTIONS & LIABILITIES</td>
                                    <td style='padding: 6px 8px; border: 1px solid #cbd5e1; text-align: right; font-weight: bold; color: #b91c1c;'>- &#8358;{$totalDeductions}</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- NET SETTLEMENT BANNER -->
            <div class='banner-box'>
                <div style='font-size: 9.5px; font-weight: bold; color: {$netBannerColor}; text-transform: uppercase; letter-spacing: 0.5px;'>{$netBannerTitle}</div>
                <div style='font-size: 20px; font-weight: bold; color: {$netBannerColor}; margin-top: 2px;'>
                    &#8358;{$netSettlementFormatted}
                </div>
            </div>

            <!-- CLEARANCE & AUDIT TRAIL -->
            <div class='audit-box'>
                <div style='font-size: 8.5px; font-weight: bold; color: #475569; text-transform: uppercase; margin-bottom: 3px;'>Clearance Workflow & Verification Record</div>
                <table style='font-size: 8.5px;'>
                    <tr>
                        <td style='width: 33.3%;'><strong>1. HR Approval:</strong> {$hrName} ({$hrDate})</td>
                        <td style='width: 33.3%;'><strong>2. Audit Clearance:</strong> {$auditName} ({$auditDate})</td>
                        <td style='width: 33.3%;'><strong>3. Finance Status:</strong> {$financeName} ({$paymentRef})</td>
                    </tr>
                    <tr>
                        <td colspan='3' style='color: #64748b; padding-top: 2px;'><em>Remarks: {$financeRemarks}</em></td>
                    </tr>
                </table>
            </div>

            {$customNoteHtml}

            <!-- SIGNATURES -->
            <table class='sign-table'>
                <tr>
                    <td style='width: 25%;'>
                        <div class='sign-line'>Head of HR<br><span style='font-size: 7.5px; font-weight: normal; color: #64748b;'>{$hrName}</span></div>
                    </td>
                    <td style='width: 25%;'>
                        <div class='sign-line'>Internal Audit<br><span style='font-size: 7.5px; font-weight: normal; color: #64748b;'>{$auditName}</span></div>
                    </td>
                    <td style='width: 25%;'>
                        <div class='sign-line'>Head of Finance<br><span style='font-size: 7.5px; font-weight: normal; color: #64748b;'>{$financeName}</span></div>
                    </td>
                    <td style='width: 25%;'>
                        <div class='sign-line'>Staff Acknowledgment<br><span style='font-size: 7.5px; font-weight: normal; color: #64748b;'>{$staffName}</span></div>
                    </td>
                </tr>
            </table>

            <!-- FOOTER -->
            <div class='footer'>
                This is an official Exit Settlement & Clearance Certificate issued by Isalu Hospitals Limited. Please retain this slip for statutory & employment records.
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Helper to compute quick settlement summary for listing.
     */
    private function calculateQuickSettlement($row): array
    {
        $basicSalary      = (float)($row->basic_salary ?? 0);
        $housingAllowance = (float)($row->housing_allowance ?? 0);
        $transportAllowance = (float)($row->transport_allowance ?? 0);
        $medicalAllowance = (float)($row->medical_allowance ?? 0);
        $utilityAllowance = (float)($row->utility_allowance ?? 0);
        $mealAllowance    = (float)($row->meal_allowance ?? 0);

        $sumAllowances = $basicSalary + $housingAllowance + $transportAllowance + $medicalAllowance + $utilityAllowance + $mealAllowance;
        $monthlyGross = $sumAllowances > 0 ? $sumAllowances : (float)($row->declare_salary ?? 0);

        $resignationDate = $row->resignation_date;
        $noticeStartDate = new \DateTime($resignationDate);
        $exitDateObj = (clone $noticeStartDate)->modify('+30 days');
        $exitDate = $exitDateObj->format('Y-m-d');

        $startYear = (int)$noticeStartDate->format('Y');
        $startMonthNum = (int)$noticeStartDate->format('n');
        $exitYear = (int)$exitDateObj->format('Y');
        $exitMonthNum = (int)$exitDateObj->format('n');
        $exitDay = (int)$exitDateObj->format('j');
        $daysInExitMonth = (int)$exitDateObj->format('t');

        $totalNoticeSalary = 0.00;
        if ("$startYear-$startMonthNum" === "$exitYear-$exitMonthNum") {
            $totalNoticeSalary = round($monthlyGross, 2);
        } else {
            $m1Amount = round($monthlyGross, 2); // Full 1st month
            $m2Amount = round($monthlyGross * ($exitDay / $daysInExitMonth), 2); // Prorated 2nd month
            $totalNoticeSalary = round($m1Amount + $m2Amount, 2);
        }

        // Retention calculated from entry salary in first_salary_structure
        $entryAllowances = (float)($row->entry_allowances_total ?? 0);
        $entryDeclare = (float)($row->entry_declare_salary ?? 0);
        $entryGross = $entryAllowances > 0 ? $entryAllowances : $entryDeclare;
        $retentionBaseSalary = $entryGross > 0 ? $entryGross : $monthlyGross;

        $monthlyRetentionRate = round(0.05 * $retentionBaseSalary, 2);
        $retentionMonthsRecorded = (int)($row->num_rente_months ?? 0);
        $retentionRefund = round($retentionMonthsRecorded * $monthlyRetentionRate, 2);

        $staffId = (int)$row->staff_id;

        // Cooperative Savings refund
        $coopSavingsRefund = 0.00;
        if (\Illuminate\Support\Facades\Schema::hasTable('coop_savings_setups')) {
            $coopSavingsRecord = DB::table('coop_savings_setups')->where('staffId', $staffId)->first();
            if ($coopSavingsRecord) {
                $coopSavingsRefund = (float)($coopSavingsRecord->saving_balance ?? 0);
            }
        }

        // Active Bonuses & Variable Earnings
        $activeBonusesTotal = 0.00;
        if (\Illuminate\Support\Facades\Schema::hasTable('staff_bonuses_and_allowances')) {
            $startMonthStr = $noticeStartDate->format('Y-m');
            $exitMonthStr = $exitDateObj->format('Y-m');
            $activeBonusesTotal = (float)DB::table('staff_bonuses_and_allowances')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->where(function($q) use ($startMonthStr, $exitMonthStr) {
                    $q->where(function($sq) use ($startMonthStr, $exitMonthStr) {
                        $sq->where('frequency', 'one_time')
                           ->whereBetween('start_month', [$startMonthStr, $exitMonthStr]);
                    })->orWhere(function($sq) use ($startMonthStr) {
                        $sq->where('frequency', 'recurring')
                           ->where('start_month', '<=', $startMonthStr)
                           ->where(function($sub) use ($startMonthStr) {
                               $sub->whereNull('end_month')
                                   ->orWhere('end_month', '>=', $startMonthStr);
                           });
                    });
                })
                ->sum('amount');
        }

        $earningVarsTotal = 0.00;
        $variableDeductionsTotal = 0.00;
        if (\Illuminate\Support\Facades\Schema::hasTable('staffEarningAndDeduction')) {
            $earningVarsTotal = (float)DB::table('staffEarningAndDeduction')
                ->where('staffId', $staffId)
                ->where('status', 1)
                ->where('variable_type', 'Earning')
                ->sum('amount');

            $variableDeductionsTotal = (float)DB::table('staffEarningAndDeduction')
                ->where('staffId', $staffId)
                ->where('status', 1)
                ->where('variable_type', 'Deduction')
                ->sum('amount');
        }

        // Statutory Pension & PAYE Tax for notice period
        $targetDeclaredSalary = (float)($row->declared_salary ?? 0);
        $taxBaseSalary = $targetDeclaredSalary > 0 ? $targetDeclaredSalary : $monthlyGross;

        $isPensionActive = ((int)($row->pen_act ?? 0) === 1);
        $pensionRatePercent = !empty($row->pension_rate) ? ((float)$row->pension_rate / 100.0) : 0.08;
        $monthlyPension = $isPensionActive ? round(($monthlyGross * 0.5) * $pensionRatePercent, 2) : 0.00;
        
        // Statutory PAYE Tax targets declared salary
        $monthlyPayeTax = $this->calculateMonthlyPayeTax($taxBaseSalary, $monthlyPension);

        $totalNoticePayeTax = 0.00;
        $totalNoticePension = 0.00;
        if ("$startYear-$startMonthNum" === "$exitYear-$exitMonthNum") {
            $totalNoticePayeTax = $monthlyPayeTax;
            $totalNoticePension = $monthlyPension;
        } else {
            $m1Tax = $monthlyPayeTax;
            $m2Tax = round($monthlyPayeTax * ($exitDay / $daysInExitMonth), 2);
            $totalNoticePayeTax = round($m1Tax + $m2Tax, 2);

            $m1Pen = $monthlyPension;
            $m2Pen = round($monthlyPension * ($exitDay / $daysInExitMonth), 2);
            $totalNoticePension = round($m1Pen + $m2Pen, 2);
        }

        // Fetch total active liabilities and statutory deductions
        $totalDeductions = round($totalNoticePayeTax + $totalNoticePension, 2);

        if (\Illuminate\Support\Facades\Schema::hasTable('medical_loan_deduction_setups')) {
            $totalDeductions += (float)DB::table('medical_loan_deduction_setups')->where('staffId', $staffId)->value('balance_remaining');
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('coop_loan_deduction_setups')) {
            $totalDeductions += (float)DB::table('coop_loan_deduction_setups')->where('staffId', $staffId)->value('balance_remaining');
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('coop_asset_finance_deduction_setups')) {
            $totalDeductions += (float)DB::table('coop_asset_finance_deduction_setups')->where('staffId', $staffId)->value('balance_remaining');
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('surcharge_deduction_setups')) {
            $totalDeductions += (float)DB::table('surcharge_deduction_setups')->where('staffId', $staffId)->value('balance_remaining');
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('absence_penalty_deduction_setups')) {
            $totalDeductions += (float)DB::table('absence_penalty_deduction_setups')->where('staffId', $staffId)->value('balance_remaining');
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('employee_loans')) {
            $totalDeductions += (float)DB::table('employee_loans')->where('staffId', $staffId)->whereRaw("LOWER(status) = 'approved'")->value('balance');
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('other_deduction_setups')) {
            $totalDeductions += (float)DB::table('other_deduction_setups')->where('staffId', $staffId)->value('balance_remaining');
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('iou_records')) {
            $totalDeductions += (float)DB::table('iou_records')->where('staff_id', $staffId)->where('status', '!=', 2)->sum('amount');
        }

        $totalFinalEarnings = round($totalNoticeSalary + $retentionRefund + $coopSavingsRefund + $activeBonusesTotal + $earningVarsTotal, 2);
        $totalDeductions = round($totalDeductions, 2);
        $netSettlement = round($totalFinalEarnings - $totalDeductions, 2);

        $settlementType = 'payable';
        if ($netSettlement < 0) {
            $settlementType = 'recoverable';
        } elseif ($netSettlement == 0.00) {
            $settlementType = 'balanced';
        }

        return [
            'exit_date'           => $exitDate,
            'notice_days'         => 30,
            'monthly_gross'       => $monthlyGross,
            'declared_salary'     => $taxBaseSalary,
            'notice_salary_total' => $totalNoticeSalary,
            'retention_refund'    => $retentionRefund,
            'coop_savings_refund' => $coopSavingsRefund,
            'bonuses_total'       => $activeBonusesTotal + $earningVarsTotal,
            'total_earnings'      => $totalFinalEarnings,
            'total_deductions'    => $totalDeductions,
            'net_settlement'      => $netSettlement,
            'settlement_type'     => $settlementType,
        ];
    }

    /**
     * Calculate monthly PAYE tax based on Nigerian statutory progressive tax bands (Act 2025/2026).
     */
    protected function calculateMonthlyPayeTax($monthlyGross, $monthlyPension = 0.0)
    {
        $annualGross = $monthlyGross * 12.0;
        $annualPension = $monthlyPension * 12.0;
        $annualTaxable = max(0.00, $annualGross - $annualPension);

        $annualTax = 0.00;
        if ($annualTaxable > 800000.00) {
            $taxableRemaining = $annualTaxable - 800000.00;

            // Band 1: Next ₦2,200,000 @ 15%
            $band1 = min(2200000.00, $taxableRemaining);
            $annualTax += $band1 * 0.15;
            $taxableRemaining -= $band1;

            // Band 2: Next ₦9,000,000 @ 18%
            if ($taxableRemaining > 0) {
                $band2 = min(9000000.00, $taxableRemaining);
                $annualTax += $band2 * 0.18;
                $taxableRemaining -= $band2;
            }

            // Band 3: Next ₦13,000,000 @ 21%
            if ($taxableRemaining > 0) {
                $band3 = min(13000000.00, $taxableRemaining);
                $annualTax += $band3 * 0.21;
                $taxableRemaining -= $band3;
            }

            // Band 4: Next ₦25,000,000 @ 23%
            if ($taxableRemaining > 0) {
                $band4 = min(25000000.00, $taxableRemaining);
                $annualTax += $band4 * 0.23;
                $taxableRemaining -= $band4;
            }

            // Band 5: Above ₦50,000,000 @ 25%
            if ($taxableRemaining > 0) {
                $annualTax += $taxableRemaining * 0.25;
            }
        }

        return round($annualTax / 12.0, 2);
    }


    /**
     * GET /api/nextjs/payroll/resignations/hr-reject/{id}
     */
    public function hrReject(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || !$this->hasHrPermission($ctx, 'hr_approve_resignation')) {
                return response()->json(['status' => 'error', 'message' => 'HR or delegated administrative privileges required.'], 401);
            }

            $record = DB::table('resignation_requests')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Resignation request not found.'], 404);
            }

            if ($record->hod_status !== 1 || $record->admin_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This request is not in a pending HR state.'], 400);
            }

            $remarks = $request->input('remarks');
            DB::table('resignation_requests')->where('id', $id)->update([
                'admin_status' => 2,
                'status'       => 2, // rejected
                'admin_id'     => $ctx['userId'],
                'admin_date'   => now(),
                'remarks'      => $remarks,
                'updated_at'   => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Resignation request rejected by HR Admin.']);
        } catch (\Throwable $th) {
            Log::error('ResignationApiController hrReject: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST|GET /api/nextjs/payroll/resignations/audit-approve/{id}
     * Audit Head checks calculations and approves clearance for payment.
     */
    public function auditApprove(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$ctx['isAuditStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'Audit Head or delegated audit privileges required.'], 401);
            }

            $record = DB::table('resignation_requests')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Resignation record not found.'], 404);
            }

            if ($record->admin_status !== 1) {
                return response()->json(['status' => 'error', 'message' => 'This request must be approved by HR Head before audit review.'], 400);
            }

            if ($record->audit_status === 1) {
                return response()->json(['status' => 'error', 'message' => 'This exit clearance has already been checked and approved by Audit.'], 400);
            }

            $remarks = $request->input('remarks', 'Audited and verified accurate for exit payment clearance.');
            DB::table('resignation_requests')->where('id', $id)->update([
                'audit_status'  => 1, // Approved for payment
                'audit_id'      => $ctx['userId'],
                'audit_date'    => now(),
                'audit_remarks' => $remarks,
                'updated_at'    => now(),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Exit settlement clearance verified and approved for payment by Audit Head.'
            ]);
        } catch (\Throwable $th) {
            Log::error('ResignationApiController auditApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST|GET /api/nextjs/payroll/resignations/audit-reject/{id}
     * Audit Head queries/rejects clearance calculations with remarks.
     */
    public function auditReject(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$ctx['isAuditStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'Audit Head or delegated audit privileges required.'], 401);
            }

            $record = DB::table('resignation_requests')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Resignation record not found.'], 404);
            }

            $remarks = trim($request->input('remarks', ''));
            if (empty($remarks)) {
                return response()->json(['status' => 'error', 'message' => 'Audit query / rejection remarks are mandatory.'], 422);
            }

            DB::table('resignation_requests')->where('id', $id)->update([
                'audit_status'  => 2, // Audit Queried / Held
                'audit_id'      => $ctx['userId'],
                'audit_date'    => now(),
                'audit_remarks' => $remarks,
                'updated_at'    => now(),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Exit clearance queried/held by Audit Head.'
            ]);
        } catch (\Throwable $th) {
            Log::error('ResignationApiController auditReject: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST|GET /api/nextjs/payroll/resignations/finance-pay/{id}
     * Finance Head marks settlement as paid or recovered.
     */
    public function financePay(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$ctx['isFinanceStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'Finance Head or delegated finance privileges required.'], 401);
            }

            $record = DB::table('resignation_requests')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Resignation record not found.'], 404);
            }

            if ($record->admin_status !== 1) {
                return response()->json(['status' => 'error', 'message' => 'This request must be approved by HR Head.'], 400);
            }

            if ($record->audit_status !== 1) {
                return response()->json(['status' => 'error', 'message' => 'This exit clearance must first be checked & approved by Audit Head before Finance can process payment or recovery.'], 400);
            }

            if ($record->finance_status === 1) {
                return response()->json(['status' => 'error', 'message' => 'This exit settlement has already been cleared.'], 400);
            }

            $settlementData = $this->computeDetailedSettlement($id);
            $isRecoverable = ($settlementData && ($settlementData['settlement_summary']['settlement_type'] ?? '') === 'recoverable');

            $defaultRemarks = $isRecoverable ? 'Outstanding debt recovery processed and confirmed.' : 'Payment processed and disbursed.';
            $paymentReference = trim($request->input('payment_reference', ''));
            $remarks = $request->input('remarks', $defaultRemarks);
            $paymentDate = $request->input('payment_date', now()->format('Y-m-d H:i:s'));

            DB::table('resignation_requests')->where('id', $id)->update([
                'finance_status'    => 1, // Cleared (Paid / Recovered)
                'finance_id'        => $ctx['userId'],
                'finance_date'      => $paymentDate,
                'payment_reference' => $paymentReference,
                'finance_remarks'   => $remarks,
                'updated_at'        => now(),
            ]);

            // Automatically send detailed Exit Settlement Breakdown & Clearance email to staff
            $settlementData = $this->computeDetailedSettlement($id);
            $emailStatusMsg = '';
            $emailResult = ['sent' => false, 'email' => null];

            if ($settlementData) {
                $emailResult = $this->sendSettlementBreakdownEmail($settlementData);
                if ($emailResult['sent']) {
                    $emailStatusMsg = " Breakdown slip emailed to {$emailResult['email']}.";
                } elseif (!empty($emailResult['email'])) {
                    $emailStatusMsg = " (Note: Email could not be delivered: {$emailResult['message']})";
                } else {
                    $emailStatusMsg = " (Note: Staff has no email address configured).";
                }
            }

            $successMsg = $isRecoverable
                ? "Exit settlement debt marked as recovered and cleared successfully.{$emailStatusMsg}"
                : "Exit settlement marked as paid successfully.{$emailStatusMsg}";

            return response()->json([
                'status'        => 'success',
                'message'       => $successMsg,
                'email_sent'    => $emailResult['sent'] ?? false,
                'email_address' => $emailResult['email'] ?? null,
            ]);
        } catch (\Throwable $th) {
            Log::error('ResignationApiController financePay: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}

