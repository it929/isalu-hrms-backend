<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IouApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * Resolve the current user context from the X-User-Id header.
     */
    

    /**
     * GET /api/nextjs/payroll/ious/staff
     * Fetch active employees along with their monthly gross salary and max allowed IOU (50%).
     */
    public function getStaffList(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $query = DB::table('tblper as p')
                ->leftJoin('salary_structures as ss', 'ss.staffId', '=', 'p.ID')
                ->where('p.rank', '!=', 2) // Exclude terminated/retired
                ->where('p.staff_status', 1)
                ->select(
                    'p.ID as id',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'ss.basic_salary',
                    'ss.housing_allowance',
                    'ss.transport_allowance',
                    'ss.medical_allowance',
                    'ss.utility_allowance',
                    'ss.meal_allowance',
                    'ss.can_take_iou',
                    'ss.max_iou_amount'
                )
                ->orderBy('p.surname', 'asc');

            // Non-admins can only select themselves
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                if ($ctx['employee']) {
                    $query->where('p.ID', $ctx['employee']->ID);
                } else {
                    $query->where('p.ID', 0); // fallback empty
                }
            }

            $staff = $query->get()->map(function ($row) {
                $fullName = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                
                $grossSalary = (float)($row->basic_salary ?? 0.00) +
                               (float)($row->housing_allowance ?? 0.00) +
                               (float)($row->transport_allowance ?? 0.00) +
                               (float)($row->medical_allowance ?? 0.00) +
                               (float)($row->utility_allowance ?? 0.00) +
                               (float)($row->meal_allowance ?? 0.00);

                $canTakeIou = (int)($row->can_take_iou ?? 1);
                $maxIouAmount = (float)($row->max_iou_amount ?? 0.00);
                $maxIou = $maxIouAmount > 0.00 ? $maxIouAmount : ($grossSalary * 0.50);

                return [
                    'id'             => $row->id,
                    'fileNo'         => $row->fileNo ?? '',
                    'name'           => $fullName,
                    'label'          => $fullName,
                    'salary'         => $grossSalary,
                    'max_iou'        => $maxIou,
                    'can_take_iou'   => $canTakeIou,
                    'max_iou_amount' => $maxIouAmount,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data'   => $staff
            ]);
        } catch (\Throwable $th) {
            Log::error('IouApiController getStaffList: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/ious/used-limit
     * Fetch already applied IOU total for the staff in the month and year of the specified date.
     */
    public function getUsedLimit(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $staffId = $request->input('staff_id');
            $date = $request->input('date');
            $excludeId = $request->input('exclude_id');

            if (!$staffId || !$date) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'staff_id and date query parameters are required.'
                ], 400);
            }

            // Extract month and year from the date
            $time = strtotime($date);
            if (!$time) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid date format.'
                ], 400);
            }
            $month = date('m', $time);
            $year = date('Y', $time);

            // Fetch salary structure
            $struct = DB::table('salary_structures')
                ->where('staffId', $staffId)
                ->first();

            $grossSalary = 0.00;
            $canTakeIou = 1;
            $maxIouAmount = 0.00;

            if ($struct) {
                $grossSalary = (float)$struct->basic_salary +
                               (float)$struct->housing_allowance +
                               (float)$struct->transport_allowance +
                               (float)$struct->medical_allowance +
                               (float)$struct->utility_allowance +
                               (float)$struct->meal_allowance;
                $canTakeIou = (int)($struct->can_take_iou ?? 1);
                $maxIouAmount = (float)($struct->max_iou_amount ?? 0.00);
            }

            $maxLimit = $maxIouAmount > 0.00 ? $maxIouAmount : ($grossSalary * 0.50);

            // Sum already used amount for this month and year (excluding rejected status = 2)
            $query = DB::table('iou_records')
                ->where('staff_id', $staffId)
                ->where('status', '!=', 2)
                ->whereYear('iou_date', $year)
                ->whereMonth('iou_date', $month);

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            $usedAmount = (float) $query->sum('amount');
            $remainingLimit = max(0.00, $maxLimit - $usedAmount);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'gross_salary' => $grossSalary,
                    'max_limit' => $maxLimit,
                    'used_amount' => $usedAmount,
                    'remaining_limit' => $remainingLimit,
                    'month_name' => date('F Y', $time),
                    'can_take_iou' => $canTakeIou,
                    'max_iou_amount' => $maxIouAmount,
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('IouApiController getUsedLimit: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/ious
     * List all IOUs matching user credentials.
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $search = trim($request->input('search', ''));
            $query = DB::table('iou_records as ir')
                ->join('tblper as p', 'p.ID', '=', 'ir.staff_id')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->leftJoin('users as u_hod', 'u_hod.id', '=', 'ir.hod_id')
                ->leftJoin('users as u_admin', 'u_admin.id', '=', 'ir.admin_id')
                ->leftJoin('users as u_audit', 'u_audit.id', '=', 'ir.audit_id')
                ->leftJoin('users as u_finance', 'u_finance.id', '=', 'ir.finance_id')
                ->leftJoin('salary_structures as ss', 'ss.staffId', '=', 'ir.staff_id')
                ->select(
                    'ir.*',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'd.department',
                    'u_hod.name as hod_name',
                    'u_admin.name as admin_name',
                    'u_audit.name as audit_name',
                    'u_finance.name as finance_name',
                    'ss.basic_salary',
                    'ss.housing_allowance',
                    'ss.transport_allowance',
                    'ss.medical_allowance',
                    'ss.utility_allowance',
                    'ss.meal_allowance'
                );

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('p.fileNo', 'like', "%{$search}%")
                      ->orWhere('p.surname', 'like', "%{$search}%")
                      ->orWhere('p.first_name', 'like', "%{$search}%")
                      ->orWhere('p.othernames', 'like', "%{$search}%")
                      ->orWhere('ir.reason', 'like', "%{$search}%");
                });
            }

            $employee = $ctx['employee'];

            if ($ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || $ctx['isFinanceStaff'] || $ctx['isAuditStaff']) {
                // Admins, Finance, and Audit see all IOU applications
            } elseif ($employee && $ctx['isHod']) {
                // HOD sees staff in their department
                $query->where('p.departmentID', $employee->departmentID);
            } elseif ($employee) {
                // Regular staff see only their own
                $query->where('ir.staff_id', $employee->ID);
            } else {
                $query->where('ir.id', 0); // fallback empty
            }

            $records = $query->orderBy('ir.id', 'desc')->get()->map(function ($row) {
                $row->name = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                
                $grossSalary = (float)($row->basic_salary ?? 0.00) +
                               (float)($row->housing_allowance ?? 0.00) +
                               (float)($row->transport_allowance ?? 0.00) +
                               (float)($row->medical_allowance ?? 0.00) +
                               (float)($row->utility_allowance ?? 0.00) +
                               (float)($row->meal_allowance ?? 0.00);

                $row->gross_salary = $grossSalary;
                $row->percentage_of_salary = $grossSalary > 0 ? round(((float)$row->amount / $grossSalary) * 100, 2) : 0;
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
            Log::error('IouApiController index: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/ious
     * Save or update an IOU application.
     */
    public function store(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $validated = $request->validate([
                'id'             => 'nullable|integer',
                'staff_id'       => 'required|integer',
                'amount'         => 'required|numeric|min:0.01',
                'reason'         => 'required|string',
                'iou_date'       => 'required|date',
                'repayment_date' => 'nullable|date',
            ]);

            // Staff check
            $staff = DB::table('tblper')->where('ID', $validated['staff_id'])->first();
            if (!$staff) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'The selected staff member does not exist.'
                ], 404);
            }

            // Limit check: Gross monthly salary from salary_structures
            $struct = DB::table('salary_structures')
                ->where('staffId', $validated['staff_id'])
                ->first();

            $grossSalary = 0.00;
            $canTakeIou = 1;
            $maxIouAmount = 0.00;

            if ($struct) {
                $grossSalary = (float)$struct->basic_salary +
                               (float)$struct->housing_allowance +
                               (float)$struct->transport_allowance +
                               (float)$struct->medical_allowance +
                               (float)$struct->utility_allowance +
                               (float)$struct->meal_allowance;
                $canTakeIou = (int)($struct->can_take_iou ?? 1);
                $maxIouAmount = (float)($struct->max_iou_amount ?? 0.00);
            }

            if ($canTakeIou === 0) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'This employee is not eligible to take IOU.'
                ], 422);
            }

            $maxAllowed = $maxIouAmount > 0.00 ? $maxIouAmount : ($grossSalary * 0.50);
            $amount = (float) $validated['amount'];
            $id = $validated['id'] ?? null;

            // Extract month and year from the application date
            $time = strtotime($validated['iou_date']);
            $month = date('m', $time);
            $year = date('Y', $time);

            // Calculate other active requests for this month (excluding rejected status = 2)
            $query = DB::table('iou_records')
                ->where('staff_id', $validated['staff_id'])
                ->where('status', '!=', 2)
                ->whereYear('iou_date', $year)
                ->whereMonth('iou_date', $month);

            if ($id) {
                $query->where('id', '!=', $id);
            }

            $alreadyUsed = (float) $query->sum('amount');
            $totalPlanned = $alreadyUsed + $amount;

            if ($totalPlanned > $maxAllowed) {
                $formattedMax = number_format($maxAllowed, 2);
                $formattedAlready = number_format($alreadyUsed, 2);
                $formattedRequested = number_format($amount, 2);
                $monthName = date('F Y', $time);
                
                $limitReason = $maxIouAmount > 0.00 ? "custom allowed limit" : "maximum allowed limit of 50% of the employee's salary";
                if ($alreadyUsed > 0) {
                    $msg = "The IOU amount (₦{$formattedRequested}) plus already applied IOUs for {$monthName} (₦{$formattedAlready}) exceeds the {$limitReason} (₦{$formattedMax}).";
                } else {
                    $msg = "The IOU amount (₦{$formattedRequested}) exceeds the {$limitReason} (₦{$formattedMax}).";
                }

                return response()->json([
                    'status'  => 'error',
                    'message' => $msg
                ], 422);
            }

            // Ownership check for non-admins
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                if (!$ctx['employee'] || $ctx['employee']->ID != $validated['staff_id']) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'You can only apply for your own IOUs.'
                    ], 403);
                }
            }

            $id = $validated['id'] ?? null;
            $status = 0; // Default pending

            if ($id) {
                $existing = DB::table('iou_records')->where('id', $id)->first();
                if (!$existing) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'IOU record not found.'
                    ], 404);
                }

                // Non-admins can only edit pending applications
                if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                    if ($existing->status !== 0) {
                        return response()->json([
                            'status'  => 'error',
                            'message' => 'This IOU application has already been processed and cannot be edited.'
                        ], 403);
                    }
                }
                $status = $existing->status;
            }

            $data = [
                'staff_id'       => $validated['staff_id'],
                'amount'         => $amount,
                'reason'         => trim($validated['reason']),
                'iou_date'       => $validated['iou_date'],
                'repayment_date' => $validated['repayment_date'] ?? null,
                'status'         => $status,
                'updated_at'     => now(),
            ];

            if ($id) {
                DB::table('iou_records')->where('id', $id)->update($data);
                $message = 'IOU application updated successfully.';
            } else {
                $data['hod_status']     = 0;
                $data['admin_status']   = 0;
                $data['audit_status']   = 0;
                $data['finance_status'] = 0;
                $data['created_at']     = now();
                $id = DB::table('iou_records')->insertGetId($data);
                $message = 'IOU application submitted successfully.';
            }

            return response()->json([
                'status'  => 'success',
                'message' => $message,
                'id'      => $id
            ]);
        } catch (\Throwable $th) {
            Log::error('IouApiController store: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/nextjs/payroll/ious/{id}
     * Remove IOU application.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $record = DB::table('iou_records')->where('id', $id)->first();
            if (!$record) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'IOU record not found.'
                ], 404);
            }

            // Non-admins can only delete pending and self-owned
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                if (!$ctx['employee'] || $record->staff_id != $ctx['employee']->ID) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'You cannot delete another staff member\'s IOU application.'
                    ], 403);
                }

                if ($record->status !== 0) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'This IOU application has already been processed and cannot be deleted.'
                    ], 403);
                }
            }

            DB::table('iou_records')->where('id', $id)->delete();
            DB::table('iou_approvals')->where('iou_id', $id)->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'IOU application deleted successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('IouApiController destroy: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Helper to log detailed approvals/rejections audits.
     */
    private function logApproval(int $iouId, string $level, int $approverId, int $status, ?string $remarks)
    {
        DB::table('iou_approvals')->insert([
            'iou_id'      => $iouId,
            'level'       => $level,
            'approver_id' => $approverId,
            'status'      => $status,
            'remarks'     => $remarks,
            'created_at'  => now(),
        ]);
    }

    /**
     * GET /api/nextjs/payroll/ious/hod-approve/{id}
     */
    public function hodApprove(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$this->hasHodPermission($ctx, 'approve_iou'))) {
                return response()->json(['status' => 'error', 'message' => 'HOD or delegated administrative privileges required.'], 401);
            }

            $record = DB::table('iou_records')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'IOU record not found.'], 404);
            }

            if ($record->hod_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This IOU application is not in a pending HOD state.'], 400);
            }

            // HOD department check
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                $employee = DB::table('tblper')->where('ID', $record->staff_id)->first();
                if (!$employee || $employee->departmentID != $ctx['employee']->departmentID) {
                    return response()->json(['status' => 'error', 'message' => 'Access denied: staff belongs to a different department.'], 403);
                }
            }

            $remarks = $request->input('remarks');
            DB::table('iou_records')->where('id', $id)->update([
                'hod_status' => 1,
                'hod_id'     => $ctx['userId'],
                'hod_date'   => now(),
                'remarks'    => $remarks,
                'updated_at' => now(),
            ]);

            $this->logApproval($id, 'HOD', (int)$ctx['userId'], 1, $remarks);

            return response()->json(['status' => 'success', 'message' => 'IOU recommended successfully by HOD.']);
        } catch (\Throwable $th) {
            Log::error('IouApiController hodApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/ious/hod-reject/{id}
     */
    public function hodReject(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$this->hasHodPermission($ctx, 'approve_iou'))) {
                return response()->json(['status' => 'error', 'message' => 'HOD or delegated administrative privileges required.'], 401);
            }

            $record = DB::table('iou_records')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'IOU record not found.'], 404);
            }

            if ($record->hod_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This IOU application is not in a pending HOD state.'], 400);
            }

            // HOD department check
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                $employee = DB::table('tblper')->where('ID', $record->staff_id)->first();
                if (!$employee || $employee->departmentID != $ctx['employee']->departmentID) {
                    return response()->json(['status' => 'error', 'message' => 'Access denied: staff belongs to a different department.'], 403);
                }
            }

            $remarks = $request->input('remarks');
            DB::table('iou_records')->where('id', $id)->update([
                'hod_status' => 2,
                'status'     => 2, // Rejects overall application immediately
                'hod_id'     => $ctx['userId'],
                'hod_date'   => now(),
                'remarks'    => $remarks,
                'updated_at' => now(),
            ]);

            $this->logApproval($id, 'HOD', (int)$ctx['userId'], 2, $remarks);

            return response()->json(['status' => 'success', 'message' => 'IOU rejected by HOD.']);
        } catch (\Throwable $th) {
            Log::error('IouApiController hodReject: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/ious/finance-approve/{id}
     */
    public function financeApprove(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$ctx['isFinanceStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'Finance or administrative privileges required.'], 401);
            }

            $record = DB::table('iou_records')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'IOU record not found.'], 404);
            }

            // Finance approves after Audit recommends (audit_status === 1)
            if ($record->audit_status !== 1 || $record->finance_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This IOU is not recommended by Audit or already processed by Finance.'], 400);
            }

            $remarks = $request->input('remarks');
            DB::table('iou_records')->where('id', $id)->update([
                'finance_status' => 1,
                'status'         => 1, // Final step approves overall application
                'finance_id'     => $ctx['userId'],
                'finance_date'   => now(),
                'remarks'        => $remarks,
                'updated_at'     => now(),
            ]);

            $this->logApproval($id, 'Finance', (int)$ctx['userId'], 1, $remarks);

            return response()->json(['status' => 'success', 'message' => 'IOU application marked as paid.']);
        } catch (\Throwable $th) {
            Log::error('IouApiController financeApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/ious/finance-reject/{id}
     */
    public function financeReject(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$ctx['isFinanceStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'Finance or administrative privileges required.'], 401);
            }

            $record = DB::table('iou_records')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'IOU record not found.'], 404);
            }

            if ($record->audit_status !== 1 || $record->finance_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This IOU is not recommended by Audit or already processed by Finance.'], 400);
            }

            $remarks = $request->input('remarks');
            DB::table('iou_records')->where('id', $id)->update([
                'finance_status' => 2,
                'status'         => 2, // Rejects overall application immediately
                'finance_id'     => $ctx['userId'],
                'finance_date'   => now(),
                'remarks'        => $remarks,
                'updated_at'     => now(),
            ]);

            $this->logApproval($id, 'Finance', (int)$ctx['userId'], 2, $remarks);

            return response()->json(['status' => 'success', 'message' => 'IOU application rejected by Finance.']);
        } catch (\Throwable $th) {
            Log::error('IouApiController financeReject: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/ious/hr-approve/{id}
     */
    public function hrApprove(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || !$this->hasHrPermission($ctx, 'hr_approve_iou')) {
                return response()->json(['status' => 'error', 'message' => 'HR or delegated administrative privileges required.'], 401);
            }

            $record = DB::table('iou_records')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'IOU record not found.'], 404);
            }

            // HR recommends after HOD recommends
            if ($record->hod_status !== 1 || $record->admin_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This IOU is not recommended by HOD or already processed by HR.'], 400);
            }

            $remarks = $request->input('remarks');
            DB::table('iou_records')->where('id', $id)->update([
                'admin_status' => 1,
                'admin_id'     => $ctx['userId'],
                'admin_date'   => now(),
                'remarks'      => $remarks,
                'updated_at'   => now(),
            ]);

            $this->logApproval($id, 'HR', (int)$ctx['userId'], 1, $remarks);

            return response()->json(['status' => 'success', 'message' => 'IOU recommended successfully by HR.']);
        } catch (\Throwable $th) {
            Log::error('IouApiController hrApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/ious/hr-reject/{id}
     */
    public function hrReject(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || !$this->hasHrPermission($ctx, 'hr_approve_iou')) {
                return response()->json(['status' => 'error', 'message' => 'HR or delegated administrative privileges required.'], 401);
            }

            $record = DB::table('iou_records')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'IOU record not found.'], 404);
            }

            if ($record->hod_status !== 1 || $record->admin_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This IOU is not recommended by HOD or already processed by HR.'], 400);
            }

            $remarks = $request->input('remarks');
            DB::table('iou_records')->where('id', $id)->update([
                'admin_status' => 2,
                'status'       => 2, // Rejects overall application immediately
                'admin_id'     => $ctx['userId'],
                'admin_date'   => now(),
                'remarks'      => $remarks,
                'updated_at'   => now(),
            ]);

            $this->logApproval($id, 'HR', (int)$ctx['userId'], 2, $remarks);

            return response()->json(['status' => 'success', 'message' => 'IOU rejected by HR.']);
        } catch (\Throwable $th) {
            Log::error('IouApiController hrReject: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/ious/audit-approve/{id}
     */
    public function auditApprove(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$ctx['isAuditStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'Audit or administrative privileges required.'], 401);
            }

            $record = DB::table('iou_records')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'IOU record not found.'], 404);
            }

            // Audit recommends after HR recommends
            if ($record->admin_status !== 1 || $record->audit_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This IOU is not recommended by HR or already processed by Audit.'], 400);
            }

            $remarks = $request->input('remarks');
            DB::table('iou_records')->where('id', $id)->update([
                'audit_status' => 1,
                'audit_id'     => $ctx['userId'],
                'audit_date'   => now(),
                'remarks'      => $remarks,
                'updated_at'   => now(),
            ]);

            $this->logApproval($id, 'Audit', (int)$ctx['userId'], 1, $remarks);

            return response()->json(['status' => 'success', 'message' => 'IOU recommended successfully by Audit.']);
        } catch (\Throwable $th) {
            Log::error('IouApiController auditApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/ious/audit-reject/{id}
     */
    public function auditReject(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$ctx['isAuditStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'Audit or administrative privileges required.'], 401);
            }

            $record = DB::table('iou_records')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'IOU record not found.'], 404);
            }

            if ($record->admin_status !== 1 || $record->audit_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This IOU is not recommended by HR or already processed by Audit.'], 400);
            }

            $remarks = $request->input('remarks');
            DB::table('iou_records')->where('id', $id)->update([
                'audit_status' => 2,
                'status'       => 2, // Rejects overall application immediately
                'audit_id'     => $ctx['userId'],
                'audit_date'   => now(),
                'remarks'      => $remarks,
                'updated_at'   => now(),
            ]);

            $this->logApproval($id, 'Audit', (int)$ctx['userId'], 2, $remarks);

            return response()->json(['status' => 'success', 'message' => 'IOU rejected by Audit.']);
        } catch (\Throwable $th) {
            Log::error('IouApiController auditReject: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/ious/limit-config
     */
    public function getLimitConfig(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'Administrative privileges required.'], 401);
            }

            $staff = DB::table('tblper as p')
                ->leftJoin('salary_structures as ss', 'ss.staffId', '=', 'p.ID')
                ->where('p.rank', '!=', 2)
                ->where('p.staff_status', 1)
                ->select(
                    'p.ID as id',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    DB::raw('COALESCE(ss.can_take_iou, 1) as can_take_iou'),
                    DB::raw('COALESCE(ss.max_iou_amount, 0.00) as max_iou_amount'),
                    'ss.basic_salary',
                    'ss.housing_allowance',
                    'ss.transport_allowance',
                    'ss.medical_allowance',
                    'ss.utility_allowance',
                    'ss.meal_allowance'
                )
                ->orderBy('p.surname', 'asc')
                ->get()
                ->map(function ($row) {
                    $grossSalary = (float)($row->basic_salary ?? 0.00) +
                                   (float)($row->housing_allowance ?? 0.00) +
                                   (float)($row->transport_allowance ?? 0.00) +
                                   (float)($row->medical_allowance ?? 0.00) +
                                   (float)($row->utility_allowance ?? 0.00) +
                                   (float)($row->meal_allowance ?? 0.00);

                    return [
                        'id' => $row->id,
                        'fileNo' => $row->fileNo ?? '',
                        'name' => trim("{$row->surname} {$row->first_name} {$row->othernames}"),
                        'can_take_iou' => (int) $row->can_take_iou,
                        'max_iou_amount' => (float) $row->max_iou_amount,
                        'gross_salary' => $grossSalary,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $staff
            ]);
        } catch (\Throwable $th) {
            Log::error('IouApiController getLimitConfig: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/ious/limit-config/{staffId}
     */
    public function getStaffLimitConfig(Request $request, $staffId)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'Administrative privileges required.'], 401);
            }

            $person = DB::table('tblper')->where('ID', $staffId)->first();
            if (!$person) {
                return response()->json(['status' => 'error', 'message' => 'Staff record not found.'], 404);
            }

            $struct = DB::table('salary_structures')->where('staffId', $staffId)->first();

            $grossSalary = 0.00;
            $canTakeIou = 1;
            $maxIouAmount = 0.00;

            if ($struct) {
                $grossSalary = (float)$struct->basic_salary +
                               (float)$struct->housing_allowance +
                               (float)$struct->transport_allowance +
                               (float)$struct->medical_allowance +
                               (float)$struct->utility_allowance +
                               (float)$struct->meal_allowance;
                $canTakeIou = (int)($struct->can_take_iou ?? 1);
                $maxIouAmount = (float)($struct->max_iou_amount ?? 0.00);
            }

            $remainingCoopLoan = (float) DB::table('coop_loan_deduction_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->sum('balance_remaining');

            $remainingMedicalLoan = (float) DB::table('medical_loan_deduction_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->sum('balance_remaining');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $person->ID,
                    'fileNo' => $person->fileNo,
                    'name' => trim("{$person->surname} {$person->first_name} {$person->othernames}"),
                    'gross_salary' => $grossSalary,
                    'can_take_iou' => $canTakeIou,
                    'max_iou_amount' => $maxIouAmount,
                    'remaining_coop_loan' => $remainingCoopLoan,
                    'remaining_medical_loan' => $remainingMedicalLoan,
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('IouApiController getStaffLimitConfig: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/ious/limit-config
     */
    public function saveLimitConfig(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'Administrative privileges required.'], 401);
            }

            $validated = $request->validate([
                'staff_id' => 'required|integer',
                'can_take_iou' => 'required|integer|in:0,1',
                'max_iou_amount' => 'required|numeric|min:0',
            ]);

            $staffId = $validated['staff_id'];

            $exists = DB::table('salary_structures')->where('staffId', $staffId)->exists();
            if ($exists) {
                DB::table('salary_structures')->where('staffId', $staffId)->update([
                    'can_take_iou' => $validated['can_take_iou'],
                    'max_iou_amount' => $validated['max_iou_amount']
                ]);
            } else {
                DB::table('salary_structures')->insert([
                    'staffId' => $staffId,
                    'can_take_iou' => $validated['can_take_iou'],
                    'max_iou_amount' => $validated['max_iou_amount']
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'IOU limits and configuration updated successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('IouApiController saveLimitConfig: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
