<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                ->leftJoin('first_salary_structure as fss', 'fss.staffId', '=', 'p.ID')
                ->leftJoin('salary_structures as ss', 'ss.staffId', '=', 'p.ID')
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
                    'fss.basic_salary',
                    'fss.housing_allowance',
                    'fss.transport_allowance',
                    'fss.medical_allowance',
                    'fss.utility_allowance',
                    'fss.meal_allowance',
                    'fss.declare_salary',
                    DB::raw('COALESCE(ss.declare_salary, fss.declare_salary, 0) as declared_salary'),
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

            $resignation = DB::table('resignation_requests as rr')
                ->join('tblper as p', 'p.ID', '=', 'rr.staff_id')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->leftJoin('tblbanklist as b', 'b.bankID', '=', 'p.bankID')
                ->leftJoin('users as u_admin', 'u_admin.id', '=', 'rr.admin_id')
                ->leftJoin('users as u_hod', 'u_hod.id', '=', 'rr.hod_id')
                ->leftJoin('users as u_audit', 'u_audit.id', '=', 'rr.audit_id')
                ->leftJoin('users as u_finance', 'u_finance.id', '=', 'rr.finance_id')
                ->leftJoin('first_salary_structure as fss', 'fss.staffId', '=', 'p.ID')
                ->leftJoin('salary_structures as ss', 'ss.staffId', '=', 'p.ID')
                ->where('rr.id', $id)
                ->select(
                    'rr.*',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
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
                    'fss.basic_salary',
                    'fss.housing_allowance',
                    'fss.transport_allowance',
                    'fss.medical_allowance',
                    'fss.utility_allowance',
                    'fss.meal_allowance',
                    'fss.declare_salary',
                    DB::raw('COALESCE(ss.declare_salary, fss.declare_salary, 0) as declared_salary'),
                    'fss.num_rente_months',
                    'fss.reten_act',
                    'ss.pen_act',
                    'ss.pension_rate'
                )
                ->first();

            if (!$resignation) {
                return response()->json(['status' => 'error', 'message' => 'Approved resignation record not found.'], 404);
            }

            $staffId = (int)$resignation->staff_id;
            $resignationDate = $resignation->resignation_date;
            
            // ── 1. Calculate Monthly Gross & Breakdown ──
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
                // If notice start & exit fall within the exact same month (e.g. submitted on 1st and ends on 30th/31st)
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
                // Month 1: Staff submitted during this month (e.g. 15th August).
                // They worked the entire month (1st through end of month) because they were in active service prior to resignation date.
                $m1Amount = round($monthlyGross, 2); // 100% full month
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

                // Month 2: Staff completes their 1-month notice into the subsequent month (e.g. 14 or 15 days in September).
                // Prorated by exact days worked in this month / total days in this calendar month (28, 29, 30, or 31).
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

            // ── 3. Retention Fund Calculation & 100% Refund ──
            // Monthly retention rate is 5% of gross
            $monthlyRetentionRate = round(0.05 * $monthlyGross, 2);
            $retentionMonthsRecorded = (int)($resignation->num_rente_months ?? 0);

            // Sum actual retention deducted from historical payroll records if table exists
            $retentionFromPayroll = 0.00;
            if (\Illuminate\Support\Facades\Schema::hasTable('payroll_conpt')) {
                $retentionFromPayroll = (float)DB::table('payroll_conpt')
                    ->where('staffID', $staffId)
                    ->sum('retention');
            }

            $retentionFromStructure = round($retentionMonthsRecorded * $monthlyRetentionRate, 2);
            $totalRetentionDeducted = max($retentionFromPayroll, $retentionFromStructure);

            // Because the staff member completed their compulsory 1-month notice, they are entitled to 100% refund
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
            
            // Statutory PAYE Tax targets declared salary
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
            // Note: Cooperative Savings is staff's own asset/savings (refunded under earnings), not deducted against them upon exit
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

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'resignation_id'     => $resignation->id,
                    'staff'              => [
                        'id'               => $staffId,
                        'name'             => $staffFullName,
                        'file_no'          => $resignation->fileNo,
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
                    'user_permissions' => [
                        'is_super_admin'   => (bool)$ctx['isSuperAdmin'],
                        'is_admin_staff'   => (bool)$ctx['isAdminStaff'],
                        'is_audit_staff'   => (bool)$ctx['isAuditStaff'],
                        'is_finance_staff' => (bool)$ctx['isFinanceStaff'],
                    ],
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('ResignationApiController getSettlementBreakdown: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
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

        $monthlyRetentionRate = round(0.05 * $monthlyGross, 2);
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

            $calc = $this->calculateQuickSettlement($record);
            $isRecoverable = ($calc['settlement_type'] === 'recoverable');

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

            $successMsg = $isRecoverable
                ? 'Exit settlement debt marked as recovered and cleared successfully by Finance Head.'
                : 'Exit settlement marked as paid successfully by Finance Head.';

            return response()->json([
                'status'  => 'success',
                'message' => $successMsg
            ]);
        } catch (\Throwable $th) {
            Log::error('ResignationApiController financePay: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}

