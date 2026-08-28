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

            // Non-admins can only select themselves, but HODs can select department staff
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                if ($ctx['isHod']) {
                    $deptId = (isset($ctx['isDelegatedHod']) && $ctx['isDelegatedHod']) ? $ctx['delegated_department_id'] : $ctx['employee']->departmentID;
                    $query->where('p.departmentID', $deptId);
                } elseif ($ctx['employee']) {
                    $query->where('p.ID', $ctx['employee']->ID);
                } else {
                    $query->where('p.ID', 0); // fallback empty
                }
            }

            $firstStructures = \Illuminate\Support\Facades\Schema::hasTable('first_salary_structure') 
                ? DB::table('first_salary_structure')->get()->keyBy('staffId') 
                : collect();

            $staff = $query->get()->map(function ($row) use ($firstStructures) {
                $fullName = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                
                $grossSalary = (float)($row->basic_salary ?? 0.00) +
                               (float)($row->housing_allowance ?? 0.00) +
                               (float)($row->transport_allowance ?? 0.00) +
                               (float)($row->medical_allowance ?? 0.00) +
                               (float)($row->utility_allowance ?? 0.00) +
                               (float)($row->meal_allowance ?? 0.00);

                $canTakeIou = (int)($row->can_take_iou ?? 1);
                $maxIouAmount = (float)($row->max_iou_amount ?? 0.00);

                // Check staff retention completion status
                $firstStruct = $firstStructures[$row->id] ?? null;
                $isRetentionActive = false;
                $hasCompletedRetention = true;
                $retentionMonths = 0;
                if ($firstStruct && (int)($firstStruct->reten_act ?? 0) === 1) {
                    $isRetentionActive = true;
                    $retentionMonths = (int)($firstStruct->num_rente_months ?? 0);
                    $hasCompletedRetention = ($retentionMonths >= 20);
                }

                // If staff has NOT yet completed retention (reten_act = 1 and months < 20), limit is 50% of salary
                $limitPercentage = ($isRetentionActive && !$hasCompletedRetention) ? 50 : 70;
                $maxIou = $maxIouAmount > 0.00 ? $maxIouAmount : ($grossSalary * ($limitPercentage / 100.0));

                $hasUploadedEducation = DB::table('tbleducations')
                    ->where('staffid', $row->id)
                    ->whereNotNull('document')
                    ->where('document', '!=', '')
                    ->exists();

                return [
                    'id'                        => $row->id,
                    'fileNo'                    => $row->fileNo ?? '',
                    'name'                      => $fullName,
                    'label'                     => $fullName,
                    'salary'                    => $grossSalary,
                    'max_iou'                   => $maxIou,
                    'limit_percentage'          => $limitPercentage,
                    'is_retention_active'       => $isRetentionActive,
                    'has_completed_retention'   => $hasCompletedRetention,
                    'retention_months'          => $retentionMonths,
                    'remaining_retention_months'=> max(0, 20 - $retentionMonths),
                    'can_take_iou'              => $canTakeIou,
                    'max_iou_amount'            => $maxIouAmount,
                    'has_uploaded_education'    => $hasUploadedEducation,
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

            // Check staff retention completion status
            $firstStruct = DB::table('first_salary_structure')->where('staffId', $staffId)->first();
            $isRetentionActive = false;
            $hasCompletedRetention = true;
            $retentionMonths = 0;
            if ($firstStruct && (int)($firstStruct->reten_act ?? 0) === 1) {
                $isRetentionActive = true;
                $retentionMonths = (int)($firstStruct->num_rente_months ?? 0);
                $hasCompletedRetention = ($retentionMonths >= 20);
            }

            // If staff has NOT yet completed retention, limit is 50% of salary
            $limitPercentage = ($isRetentionActive && !$hasCompletedRetention) ? 50 : 70;
            $maxLimit = $maxIouAmount > 0.00 ? $maxIouAmount : ($grossSalary * ($limitPercentage / 100.0));

            // Sum already used amount for this month and year (all non-rejected: status != 2)
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

            $netPayInfo = $this->calculateStaffNetPayBeforeIou($staffId, $year, $month, $excludeId);
            $availableNetPay = $netPayInfo['available_net_pay'];

            $day = (int) date('d', $time);
            $monthName = date('F Y', $time);
            $currentDate = \Carbon\Carbon::now();
            $targetMonthStart = \Carbon\Carbon::createFromDate((int)$year, (int)$month, 1)->startOfMonth();
            $currentMonthStart = $currentDate->copy()->startOfMonth();

            $isPastDeadline = ($day > 25) || $targetMonthStart->lt($currentMonthStart) || ($targetMonthStart->eq($currentMonthStart) && $currentDate->day > 25);
            $deadlineMessage = $isPastDeadline
                ? "The deadline to apply for IOU for {$monthName} is the 25th of the month. Applications for this month are now closed."
                : null;

            return response()->json([
                'status' => 'success',
                'data' => [
                    'gross_salary'               => $grossSalary,
                    'max_limit'                  => $maxLimit,
                    'limit_percentage'           => $limitPercentage,
                    'is_retention_active'        => $isRetentionActive,
                    'has_completed_retention'    => $hasCompletedRetention,
                    'retention_months'           => $retentionMonths,
                    'remaining_retention_months' => max(0, 20 - $retentionMonths),
                    'used_amount'                => $usedAmount,
                    'remaining_limit'            => $remainingLimit,
                    'available_net_pay'          => $availableNetPay,
                    'month_name'                 => $monthName,
                    'can_take_iou'               => $canTakeIou,
                    'max_iou_amount'             => $maxIouAmount,
                    'is_past_deadline'           => $isPastDeadline,
                    'deadline_message'           => $deadlineMessage,
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
                    'p.departmentID as department_id',
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
                // HOD sees staff in their department (or delegated department)
                $deptId = (isset($ctx['isDelegatedHod']) && $ctx['isDelegatedHod']) ? $ctx['delegated_department_id'] : $employee->departmentID;
                $query->where('p.departmentID', $deptId);
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

                $dateStr = !empty($row->iou_date) ? $row->iou_date : (!empty($row->created_at) ? $row->created_at : null);
                $time = $dateStr ? strtotime($dateStr) : time();
                $month = (int) date('m', $time);
                $year = (int) date('Y', $time);

                // Sum only HR-approved IOUs for this staff member in the same month
                $totalMonthIou = (float) DB::table('iou_records')
                    ->where('staff_id', $row->staff_id)
                    ->where('admin_status', 1)
                    ->whereYear('iou_date', $year)
                    ->whereMonth('iou_date', $month)
                    ->sum('amount');


                $row->gross_salary = $grossSalary;
                $row->percentage_of_salary = $grossSalary > 0 ? round(((float)$row->amount / $grossSalary) * 100, 2) : 0;
                $row->total_collected_month = $totalMonthIou;
                $row->total_month_percentage = $grossSalary > 0 ? round(($totalMonthIou / $grossSalary) * 100, 2) : 0;
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
                'isDelegatedHod' => $ctx['isDelegatedHod'] ?? false,
                'delegated_department_id' => $ctx['delegated_department_id'] ?? null,
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

            // Check staff retention completion status
            $firstStruct = DB::table('first_salary_structure')->where('staffId', $validated['staff_id'])->first();
            $isRetentionActive = false;
            $hasCompletedRetention = true;
            $retentionMonths = 0;
            if ($firstStruct && (int)($firstStruct->reten_act ?? 0) === 1) {
                $isRetentionActive = true;
                $retentionMonths = (int)($firstStruct->num_rente_months ?? 0);
                $hasCompletedRetention = ($retentionMonths >= 20);
            }

            // If staff has NOT yet completed retention, limit is 50% of salary
            $limitPercentage = ($isRetentionActive && !$hasCompletedRetention) ? 50 : 70;
            $maxAllowed = $maxIouAmount > 0.00 ? $maxIouAmount : ($grossSalary * ($limitPercentage / 100.0));
            $amount = (float) $validated['amount'];
            $id = $validated['id'] ?? null;

            // Extract month and year from the application date
            $time = strtotime($validated['iou_date']);
            $day = (int) date('d', $time);
            $month = date('m', $time);
            $year = date('Y', $time);
            $monthName = date('F Y', $time);

            // Deadline check: The deadline to apply for IOU is 25th of the month
            $currentDate = \Carbon\Carbon::now();
            $targetMonthStart = \Carbon\Carbon::createFromDate((int)$year, (int)$month, 1)->startOfMonth();
            $currentMonthStart = $currentDate->copy()->startOfMonth();

            $isPastDeadline = ($day > 25) || $targetMonthStart->lt($currentMonthStart) || ($targetMonthStart->eq($currentMonthStart) && $currentDate->day > 25);

            if ($isPastDeadline) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "The deadline to apply for IOU for {$monthName} is the 25th of the month. You can no longer apply for an IOU for this month."
                ], 422);
            }

            // Calculate active requests for this month (excluding rejected status = 2)
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
                
                if ($maxIouAmount > 0.00) {
                    $limitReason = "custom allowed limit";
                } elseif ($limitPercentage === 50) {
                    $limitReason = "maximum allowed limit of 50% of the employee's salary (retention in progress: Month {$retentionMonths} of 20)";
                } else {
                    $limitReason = "maximum allowed limit of 70% of the employee's salary";
                }

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

            // Net Pay check: The requested amount must not reduce available net pay to zero or negative
            $monthName = date('F Y', $time);
            $netPayInfo = $this->calculateStaffNetPayBeforeIou($validated['staff_id'], $year, $month, $id);
            $availableNetPay = $netPayInfo['available_net_pay'];

            if ($availableNetPay <= 0.00 || $amount >= $availableNetPay) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Cannot apply for IOU: This employee available net pay for {$monthName} can not be negative."
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

                // Block editing once HOD has approved — regardless of role
                if ((int)$existing->hod_status === 1) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'This IOU application has been approved by the HOD and can no longer be edited.'
                    ], 403);
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

            $remarks = trim($request->input('remarks', ''));
            if (empty($remarks)) {
                return response()->json(['status' => 'error', 'message' => 'Rejection remarks are compulsory when rejecting an IOU application.'], 422);
            }

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

            $remarks = trim($request->input('remarks', ''));
            if (empty($remarks)) {
                return response()->json(['status' => 'error', 'message' => 'Rejection remarks are compulsory when rejecting an IOU application.'], 422);
            }

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

            $remarks = trim($request->input('remarks', ''));
            if (empty($remarks)) {
                return response()->json(['status' => 'error', 'message' => 'Rejection remarks are compulsory when rejecting an IOU application.'], 422);
            }

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

            $remarks = trim($request->input('remarks', ''));
            if (empty($remarks)) {
                return response()->json(['status' => 'error', 'message' => 'Rejection remarks are compulsory when rejecting an IOU application.'], 422);
            }

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

            $firstStructures = \Illuminate\Support\Facades\Schema::hasTable('first_salary_structure')
                ? DB::table('first_salary_structure')->get()->keyBy('staffId')
                : collect();

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
                ->map(function ($row) use ($firstStructures) {
                    $grossSalary = (float)($row->basic_salary ?? 0.00) +
                                   (float)($row->housing_allowance ?? 0.00) +
                                   (float)($row->transport_allowance ?? 0.00) +
                                   (float)($row->medical_allowance ?? 0.00) +
                                   (float)($row->utility_allowance ?? 0.00) +
                                   (float)($row->meal_allowance ?? 0.00);

                    $firstStruct = $firstStructures[$row->id] ?? null;
                    $isRetentionActive = false;
                    $hasCompletedRetention = true;
                    $retentionMonths = 0;
                    if ($firstStruct && (int)($firstStruct->reten_act ?? 0) === 1) {
                        $isRetentionActive = true;
                        $retentionMonths = (int)($firstStruct->num_rente_months ?? 0);
                        $hasCompletedRetention = ($retentionMonths >= 20);
                    }

                    $limitPercentage = ($isRetentionActive && !$hasCompletedRetention) ? 50 : 70;

                    return [
                        'id'                        => $row->id,
                        'fileNo'                    => $row->fileNo ?? '',
                        'name'                      => trim("{$row->surname} {$row->first_name} {$row->othernames}"),
                        'can_take_iou'              => (int) $row->can_take_iou,
                        'max_iou_amount'            => (float) $row->max_iou_amount,
                        'limit_percentage'          => $limitPercentage,
                        'is_retention_active'       => $isRetentionActive,
                        'has_completed_retention'   => $hasCompletedRetention,
                        'retention_months'          => $retentionMonths,
                        'remaining_retention_months'=> max(0, 20 - $retentionMonths),
                        'gross_salary'              => $grossSalary,
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

            $firstStruct = DB::table('first_salary_structure')->where('staffId', $staffId)->first();
            $isRetentionActive = false;
            $hasCompletedRetention = true;
            $retentionMonths = 0;
            if ($firstStruct && (int)($firstStruct->reten_act ?? 0) === 1) {
                $isRetentionActive = true;
                $retentionMonths = (int)($firstStruct->num_rente_months ?? 0);
                $hasCompletedRetention = ($retentionMonths >= 20);
            }

            $limitPercentage = ($isRetentionActive && !$hasCompletedRetention) ? 50 : 70;

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
                    'id'                        => $person->ID,
                    'fileNo'                    => $person->fileNo,
                    'name'                      => trim("{$person->surname} {$person->first_name} {$person->othernames}"),
                    'gross_salary'              => $grossSalary,
                    'can_take_iou'              => $canTakeIou,
                    'max_iou_amount'            => $maxIouAmount,
                    'limit_percentage'          => $limitPercentage,
                    'is_retention_active'       => $isRetentionActive,
                    'has_completed_retention'   => $hasCompletedRetention,
                    'retention_months'          => $retentionMonths,
                    'remaining_retention_months'=> max(0, 20 - $retentionMonths),
                    'remaining_coop_loan'       => $remainingCoopLoan,
                    'remaining_medical_loan'    => $remainingMedicalLoan,
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

    /**
     * Calculate an employee's estimated net pay for a specific month and year before applying the specified IOU.
     */
    public function calculateStaffNetPayBeforeIou($staffId, $year, $month, $excludeIouId = null, $excludeType = null, $excludeId = null)
    {
        $month = (int)$month;
        $year = (int)$year;
        $currentMonthStr = sprintf("%04d-%02d", $year, $month);

        $struct = DB::table('salary_structures')->where('staffId', $staffId)->first();
        $firstStruct = DB::table('first_salary_structure')->where('staffId', $staffId)->first();

        $basic = 0.00;
        $housing = 0.00;
        $transport = 0.00;
        $medical = 0.00;
        $utility = 0.00;
        $meal = 0.00;
        $taxRate = 0.00;
        $pensionRate = 0.00;
        $penAct = 0;
        $declareSalary = 0.00;

        if ($struct) {
            $basic = (float)($struct->basic_salary ?? 0);
            $housing = (float)($struct->housing_allowance ?? 0);
            $transport = (float)($struct->transport_allowance ?? 0);
            $medical = (float)($struct->medical_allowance ?? 0);
            $utility = (float)($struct->utility_allowance ?? 0);
            $meal = (float)($struct->meal_allowance ?? 0);
            $taxRate = (float)($struct->tax_rate ?? 0);
            $pensionRate = (float)($struct->pension_rate ?? 8.0);
            $penAct = (int)($struct->pen_act ?? 0);
            $declareSalary = (float)($struct->declare_salary ?? 0);
        }

        $totalBasicAllowances = $basic + $housing + $transport + $medical + $utility + $meal;

        if ($totalBasicAllowances <= 0 && $firstStruct) {
            $basic = (float)($firstStruct->basic_salary ?? 0);
            $housing = (float)($firstStruct->housing_allowance ?? 0);
            $transport = (float)($firstStruct->transport_allowance ?? 0);
            $medical = (float)($firstStruct->medical_allowance ?? 0);
            $utility = (float)($firstStruct->utility_allowance ?? 0);
            $meal = (float)($firstStruct->meal_allowance ?? 0);
            $totalBasicAllowances = $basic + $housing + $transport + $medical + $utility + $meal;
            if ($declareSalary <= 0 && (float)($firstStruct->declare_salary ?? 0) > 0) {
                $declareSalary = (float)$firstStruct->declare_salary;
            }
        }

        // If still 0, use declareSalary if available
        if ($totalBasicAllowances <= 0 && $declareSalary > 0) {
            $totalBasicAllowances = $declareSalary;
        }

        // Custom Allowances / Bonuses for month
        $totalEarningVars = 0.00;
        if (\Illuminate\Support\Facades\Schema::hasTable('bonus_allowance_setups')) {
            $bonuses = DB::table('bonus_allowance_setups')
                ->where('staff_id', $staffId)
                ->where('is_active', 1)
                ->where('start_month', '<=', $currentMonthStr)
                ->where(function ($q) use ($currentMonthStr) {
                    $q->whereNull('end_month')
                      ->orWhere('end_month', '=', '')
                      ->orWhere('end_month', '>=', $currentMonthStr);
                })
                ->sum('amount');
            $totalEarningVars = (float)$bonuses;
        }

        $grossPay = $totalBasicAllowances + $totalEarningVars;
        $taxBase = ($declareSalary > 0) ? $declareSalary : $totalBasicAllowances;

        // 1. PAYE Tax
        $annualGross = $taxBase * 12.0;
        $annualPension = 0.00;
        if ($penAct === 1) {
            $rate = ($pensionRate > 0) ? ($pensionRate / 100.0) : 0.08;
            $annualPension = ($annualGross * 0.5) * $rate;
        }
        $annualTaxable = max(0.00, $annualGross - $annualPension);
        $annualTax = 0.00;
        if ($annualTaxable > 800000.00) {
            $taxableRemaining = $annualTaxable - 800000.00;
            $band1 = min(2200000.00, $taxableRemaining);
            $annualTax += $band1 * 0.15;
            $taxableRemaining -= $band1;
            if ($taxableRemaining > 0) {
                $band2 = min(9000000.00, $taxableRemaining);
                $annualTax += $band2 * 0.18;
                $taxableRemaining -= $band2;
            }
            if ($taxableRemaining > 0) {
                $band3 = min(13000000.00, $taxableRemaining);
                $annualTax += $band3 * 0.21;
                $taxableRemaining -= $band3;
            }
            if ($taxableRemaining > 0) {
                $band4 = min(25000000.00, $taxableRemaining);
                $annualTax += $band4 * 0.23;
                $taxableRemaining -= $band4;
            }
            if ($taxableRemaining > 0) {
                $annualTax += $taxableRemaining * 0.25;
            }
        }
        $payeTax = ($taxRate > 0) ? round(($taxRate / 100.0) * $taxBase, 2) : round($annualTax / 12.0, 2);

        // 2. Pension
        $pension = 0.00;
        if ($penAct === 1) {
            $rate = ($pensionRate > 0) ? ($pensionRate / 100.0) : 0.08;
            $pension = round(($totalBasicAllowances * 0.5) * $rate, 2);
        }

        // 3. Retention
        $retention = 0.00;
        if ($firstStruct && (int)($firstStruct->reten_act ?? 0) === 1 && (int)($firstStruct->num_rente_months ?? 0) < 20) {
            $retentionBase = (float)($firstStruct->basic_salary ?? 0) +
                             (float)($firstStruct->housing_allowance ?? 0) +
                             (float)($firstStruct->transport_allowance ?? 0) +
                             (float)($firstStruct->medical_allowance ?? 0) +
                             (float)($firstStruct->utility_allowance ?? 0) +
                             (float)($firstStruct->meal_allowance ?? 0);
            if ($retentionBase <= 0 && (float)($firstStruct->declare_salary ?? 0) > 0) {
                $retentionBase = (float)$firstStruct->declare_salary;
            }
            $retention = round(0.05 * $retentionBase, 2);
        }

        // 4. Other Approved/Active IOUs in this month (excluding current IOU ID)
        $otherIouSum = 0.00;
        if ($excludeType !== 'iou') {
            $iouQuery = DB::table('iou_records')
                ->where('staff_id', $staffId)
                ->where('status', 1)
                ->whereYear('iou_date', $year)
                ->whereMonth('iou_date', $month);
            if ($excludeIouId) {
                $iouQuery->where('id', '!=', $excludeIouId);
            }
            $otherIouSum = (float)$iouQuery->sum('amount');
        }

        // 5. Medical Loan Setup
        $medicalLoanDeduct = 0.00;
        if ($excludeType !== 'medical_loan' && \Illuminate\Support\Facades\Schema::hasTable('medical_loan_deduction_setups')) {
            $medLoans = DB::table('medical_loan_deduction_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->where('balance_remaining', '>', 0)
                ->where('start_month', '<=', $currentMonthStr)
                ->where('end_month', '>=', $currentMonthStr);
            if ($excludeId && $excludeType === 'medical_loan_id') {
                $medLoans->where('id', '!=', $excludeId);
            }
            foreach ($medLoans->get() as $ms) {
                $medicalLoanDeduct += min((float)$ms->monthly_deduction, (float)$ms->balance_remaining);
            }
        }

        // 6. Coop Loan Setup
        $coopLoanDeduct = 0.00;
        if ($excludeType !== 'coop_loan' && \Illuminate\Support\Facades\Schema::hasTable('coop_loan_deduction_setups')) {
            $coopLoans = DB::table('coop_loan_deduction_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->where('balance_remaining', '>', 0)
                ->where('start_month', '<=', $currentMonthStr)
                ->where('end_month', '>=', $currentMonthStr);
            if ($excludeId && $excludeType === 'coop_loan_id') {
                $coopLoans->where('id', '!=', $excludeId);
            }
            foreach ($coopLoans->get() as $cls) {
                $coopLoanDeduct += min((float)$cls->monthly_deduction, (float)$cls->balance_remaining);
            }
        }

        // 7. Coop Savings Setup
        $coopSavingsDeduct = 0.00;
        if ($excludeType !== 'coop_savings' && \Illuminate\Support\Facades\Schema::hasTable('coop_savings_setups')) {
            $coopSavings = DB::table('coop_savings_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->where('start_month', '<=', $currentMonthStr);
            if ($excludeId && $excludeType === 'coop_savings_id') {
                $coopSavings->where('id', '!=', $excludeId);
            }
            foreach ($coopSavings->get() as $cs) {
                $coopSavingsDeduct += (float)$cs->monthly_saving;
            }
        }

        // 8. Coop Asset Finance Setup
        $coopAssetDeduct = 0.00;
        if ($excludeType !== 'coop_asset_finance' && \Illuminate\Support\Facades\Schema::hasTable('coop_asset_finance_deduction_setups')) {
            $coopAssets = DB::table('coop_asset_finance_deduction_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->where('balance_remaining', '>', 0)
                ->where('start_month', '<=', $currentMonthStr)
                ->where(function ($q) use ($currentMonthStr) {
                    $q->whereNull('end_month')
                      ->orWhere('end_month', '=', '')
                      ->orWhere('end_month', '>=', $currentMonthStr);
                });
            if ($excludeId && $excludeType === 'coop_asset_finance_id') {
                $coopAssets->where('id', '!=', $excludeId);
            }
            foreach ($coopAssets->get() as $ca) {
                $coopAssetDeduct += min((float)$ca->monthly_deduction, (float)$ca->balance_remaining);
            }
        }

        // 9. Surcharge Setup
        $surchargeDeduct = 0.00;
        if ($excludeType !== 'surcharge' && \Illuminate\Support\Facades\Schema::hasTable('surcharge_deduction_setups')) {
            $surcharges = DB::table('surcharge_deduction_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->where('balance_remaining', '>', 0)
                ->where('start_month', '<=', $currentMonthStr)
                ->where(function ($q) use ($currentMonthStr) {
                    $q->whereNull('end_month')
                      ->orWhere('end_month', '=', '')
                      ->orWhere('end_month', '>=', $currentMonthStr);
                });
            if ($excludeId && $excludeType === 'surcharge_id') {
                $surcharges->where('id', '!=', $excludeId);
            }
            foreach ($surcharges->get() as $sur) {
                $surchargeDeduct += min((float)$sur->monthly_deduction, (float)$sur->balance_remaining);
            }
        }

        // 10. Absence Penalty Setup
        $absencePenaltyDeduct = 0.00;
        if ($excludeType !== 'absence_penalty' && \Illuminate\Support\Facades\Schema::hasTable('absence_penalty_deduction_setups')) {
            $absencePenalties = DB::table('absence_penalty_deduction_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->where('start_month', '<=', $currentMonthStr)
                ->where(function ($q) use ($currentMonthStr) {
                    $q->whereNull('end_month')
                      ->orWhere('end_month', '=', '')
                      ->orWhere('end_month', '>=', $currentMonthStr);
                });
            if ($excludeId && $excludeType === 'absence_penalty_id') {
                $absencePenalties->where('id', '!=', $excludeId);
            }
            foreach ($absencePenalties->get() as $ab) {
                $rem = (float)$ab->balance_remaining > 0 ? (float)$ab->balance_remaining : ((float)$ab->total_amount > 0 ? (float)$ab->total_amount : (float)$ab->monthly_deduction);
                $absencePenaltyDeduct += min((float)$ab->monthly_deduction, $rem);
            }
        }

        // 11. Loan Deduction
        $loanDeduct = 0.00;
        if ($excludeType !== 'loan') {
            if (\Illuminate\Support\Facades\Schema::hasTable('loan_deduction_setups')) {
                $loans = DB::table('loan_deduction_setups')
                    ->where('staffId', $staffId)
                    ->where('is_active', 1)
                    ->where('balance_remaining', '>', 0)
                    ->where('start_month', '<=', $currentMonthStr)
                    ->where('end_month', '>=', $currentMonthStr);
                if ($excludeId && $excludeType === 'loan_id') {
                    $loans->where('id', '!=', $excludeId);
                }
                foreach ($loans->get() as $ls) {
                    $loanDeduct += min((float)$ls->monthly_deduction, (float)$ls->balance_remaining);
                }
            }
            if ($loanDeduct == 0.00 && \Illuminate\Support\Facades\Schema::hasTable('employee_loans')) {
                $empLoan = DB::table('employee_loans')
                    ->where('staffId', $staffId)
                    ->whereRaw("LOWER(status) = 'approved'")
                    ->where('balance', '>', 0)
                    ->orderBy('id', 'desc')->first();
                if ($empLoan) {
                    $loanDeduct = min((float)$empLoan->monthly_deduction, (float)$empLoan->balance);
                }
            }
        }

        // 12. Other Deductions Setup
        $otherDeduct = 0.00;
        if ($excludeType !== 'other_deduction' && \Illuminate\Support\Facades\Schema::hasTable('other_deduction_setups')) {
            $otherSetups = DB::table('other_deduction_setups')
                ->where('staffId', $staffId)
                ->where('is_active', 1)
                ->where('start_month', '<=', $currentMonthStr)
                ->where(function ($q) use ($currentMonthStr) {
                    $q->whereNull('end_month')
                      ->orWhere('end_month', '=', '')
                      ->orWhere('end_month', '>=', $currentMonthStr);
                });
            if ($excludeId && $excludeType === 'other_deduction_id') {
                $otherSetups->where('id', '!=', $excludeId);
            }
            foreach ($otherSetups->get() as $ods) {
                $rem = (float)$ods->balance_remaining > 0 ? (float)$ods->balance_remaining : ((float)$ods->total_amount > 0 ? (float)$ods->total_amount : (float)$ods->monthly_deduction);
                $otherDeduct += min((float)$ods->monthly_deduction, $rem);
            }
        }

        $totalDeductions = $payeTax + $pension + $retention + $otherIouSum + $medicalLoanDeduct +
                           $coopLoanDeduct + $coopSavingsDeduct + $coopAssetDeduct + $surchargeDeduct +
                           $absencePenaltyDeduct + $loanDeduct + $otherDeduct;

        $availableNetPay = max(0.00, round($grossPay - $totalDeductions, 2));

        return [
            'gross_pay' => round($grossPay, 2),
            'total_deductions_before_iou' => round($totalDeductions, 2),
            'available_net_pay' => $availableNetPay,
            'total_deductions' => round($totalDeductions, 2),
            'net_pay' => $availableNetPay,
        ];
    }
}
