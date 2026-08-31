<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

class HrLeaveApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * Resolve the current user context from the X-User-Id header.
     */
    

    /**
     * Get the number of annual leave days an employee has used in a given calendar year.
     */
    private function getUsedAnnualLeaveDays($employeeId, $year): int
    {
        $employee = DB::table('tblper')->where('ID', $employeeId)->first();
        if (!$employee) {
            return 0;
        }

        // Sum from Next.js leave_record (where leave_type is Annual and status is approved = 2)
        $annualDaysNext = 0;
        $annualType = DB::table('tblleave_type')->where('leaveType', 'Annual')->first();
        if ($annualType) {
            $annualDaysNext = DB::table('leave_record')
                ->where('staffId', $employeeId)
                ->where('leave_type_id', $annualType->id)
                ->where('status', 2)
                ->whereYear('start_date', $year)
                ->sum(DB::raw("DATEDIFF(end_date, start_date) + 1"));
        }

        // Sum from legacy annual_leave (where finalapprstatus is 2)
        $annualDaysLegacy = 0;
        if ($employee->UserID) {
            $annualDaysLegacy = DB::table('annual_leave')
                ->where('staffid', $employee->UserID)
                ->where('year', $year)
                ->where('finalapprstatus', 2)
                ->sum('nod');
        }

        return (int) ($annualDaysNext + $annualDaysLegacy);
    }

    /**
     * Check if an employee is eligible to apply for leave (minimum 1 year of service in the company ONLY for Annual Leave).
     */
    private function checkLeaveEligibility($employee, $startDate, $leaveType = null): array
    {
        if (!$employee) {
            return ['eligible' => false, 'message' => 'Employee record not found.'];
        }

        // The 1-year service rule applies ONLY to Annual Leave
        $isAnnualLeave = false;
        if ($leaveType) {
            if (is_object($leaveType)) {
                $isAnnualLeave = ((int)$leaveType->id === 5 || stripos($leaveType->leaveType, 'annual') !== false);
            } elseif (is_numeric($leaveType)) {
                $lt = DB::table('tblleave_type')->where('id', (int)$leaveType)->first();
                $isAnnualLeave = ($lt && ((int)$lt->id === 5 || stripos($lt->leaveType, 'annual') !== false));
            } elseif (is_string($leaveType)) {
                $isAnnualLeave = (stripos($leaveType, 'annual') !== false);
            }
        }

        // If it is NOT annual leave, staff can apply regardless of 1-year tenure
        if (!$isAnnualLeave) {
            return ['eligible' => true];
        }

        $employmentDate = $employee->doj 
            ?: ($employee->appointment_date 
            ?: ($employee->date_present_appointment 
            ?: ($employee->resumption_date 
            ?: ($employee->created_at ?: null))));

        if ($employmentDate) {
            try {
                $joined = Carbon::parse($employmentDate);
                $start = Carbon::parse($startDate);
                $eligibleFrom = (clone $joined)->addYear();

                if ($start->lt($eligibleFrom)) {
                    return [
                        'eligible' => false,
                        'message'  => "Cannot apply for Annual Leave: Staff must have worked for at least one (1) full year in the company before becoming eligible for Annual Leave. (Date of Employment: {$joined->format('d M, Y')}, Eligible From: {$eligibleFrom->format('d M, Y')})",
                        'employment_date' => $joined->format('d M, Y'),
                        'eligible_from'   => $eligibleFrom->format('d M, Y'),
                    ];
                }
            } catch (\Throwable $e) {
                // If date parsing fails, allow fallback
            }
        }

        return ['eligible' => true];
    }

    /**
     * GET /api/nextjs/hr/apply-leave
     * Returns leave types, employees list, and roles context instantly.
     */
    public function getApplyLeaveData(Request $request)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
        }

        $leaveTypes = DB::table('tblleave_type')->orderBy('id', 'DESC')->get();
        
        $enrichEmp = function($emp) {
            $emp->has_uploaded_education = DB::table('tbleducations')
                ->where('staffid', $emp->ID)
                ->whereNotNull('document')
                ->where('document', '!=', '')
                ->exists();

            $empDate = $emp->doj ?: ($emp->appointment_date ?: ($emp->created_at ?: null));
            if ($empDate) {
                try {
                    $joined = Carbon::parse($empDate);
                    $eligibleFrom = (clone $joined)->addYear();
                    $emp->employment_date = $joined->format('d M, Y');
                    $emp->eligible_from = $eligibleFrom->format('d M, Y');
                    $emp->is_leave_eligible = $eligibleFrom->lte(Carbon::now());
                } catch (\Throwable $e) {
                    $emp->is_leave_eligible = true;
                }
            } else {
                $emp->is_leave_eligible = true;
            }
            return $emp;
        };

        if ($ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || $ctx['isAuditStaff']) {
            $employees = DB::table('tblper')
                ->select('ID', 'surname', 'first_name', 'othernames', 'office_shift', 'gender', 'doj', 'appointment_date', 'created_at', 'departmentID')
                ->get()
                ->map($enrichEmp);
        } elseif ($ctx['isHod'] && $ctx['employee']) {
            $hodDeptId = ($ctx['isDelegatedHod'] ?? false) && !empty($ctx['delegated_department_id'])
                ? $ctx['delegated_department_id']
                : $ctx['employee']->departmentID;

            if ($hodDeptId) {
                $employees = DB::table('tblper')
                    ->where('departmentID', $hodDeptId)
                    ->select('ID', 'surname', 'first_name', 'othernames', 'office_shift', 'gender', 'doj', 'appointment_date', 'created_at', 'departmentID')
                    ->get()
                    ->map($enrichEmp);
            } else {
                $employees = collect();
            }
        } else {
            $employees = collect();
        }

        $employee = $ctx['employee'];
        if ($employee) {
            $employee = $enrichEmp($employee);
        }

        return response()->json([
            'status'        => 'success',
            'leaveTypes'    => $leaveTypes,
            'employees'     => $employees,
            'isSuperAdmin'  => $ctx['isSuperAdmin'],
            'isHod'         => $ctx['isHod'],
            'isAdminStaff'  => $ctx['isAdminStaff'],
            'isAuditStaff'  => $ctx['isAuditStaff'],
            'employee'      => $employee,
        ]);
    }

    /**
     * GET /api/nextjs/hr/apply-leave/records
     * Returns leave records filtered by role.
     */
    public function getLeaveRecords(Request $request)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
        }

        // ── Base leave-record query ────────────────────────────────────────────
        $baseQuery = DB::table('leave_record')
            ->join('tblper', 'tblper.ID', '=', 'leave_record.staffId')
            ->join('tblleave_type', 'tblleave_type.id', '=', 'leave_record.leave_type_id')
            ->join('tbldepartment', 'tbldepartment.id', '=', 'tblper.departmentID')
            ->leftJoin('users as recalled_user', 'recalled_user.id', '=', 'leave_record.recalled_by')
            ->select(
                'leave_record.*',
                'tblper.surname',
                'tblper.first_name',
                'tblper.othernames',
                'tblper.office_shift',
                'tbldepartment.department',
                'tblleave_type.leaveType',
                'recalled_user.name as recalled_by_name'
            )
            ->orderBy('leave_record.id', 'DESC');

        $employee = $ctx['employee'];

        if ($ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || $ctx['isAuditStaff']) {
            // Super Admin, HR Head, and Audit Head see ALL leave records
            $records = $baseQuery->get();
        } elseif ($ctx['isHod'] && $ctx['employee']) {
            // HOD sees all leave records of staff in their department as well as their own
            $hodDeptId = ($ctx['isDelegatedHod'] ?? false) && !empty($ctx['delegated_department_id'])
                ? $ctx['delegated_department_id']
                : $ctx['employee']->departmentID;

            $baseQuery->where(function ($query) use ($employee, $hodDeptId) {
                if ($employee) {
                    $query->where('leave_record.staffId', $employee->ID);
                }
                if ($hodDeptId) {
                    $query->orWhere('tblper.departmentID', $hodDeptId);
                }
            });
            $records = $baseQuery->get();
        } else {
            // Regular staff and non-HR staff strictly see only their own leave applications
            if ($employee) {
                $baseQuery->where('leave_record.staffId', $employee->ID);
            } else {
                $baseQuery->where('leave_record.id', 0);
            }
            $records = $baseQuery->get();
        }

        // Augment each record with computed duration & formatted date
        $records = $records->map(function ($r) {
            $start = Carbon::parse($r->start_date);
            $end   = Carbon::parse($r->end_date);
            
            $calcDays = function(Carbon $s, Carbon $e, $shift) {
                if ($s->gt($e)) return 0;
                if ($shift == 1) {
                    $days = 0;
                    $current = $s->copy();
                    while ($current->lte($e)) {
                        if (!$current->isWeekend()) {
                            $days++;
                        }
                        $current->addDay();
                    }
                    return $days;
                }
                return (int)$s->diffInDays($e) + 1;
            };

            if (!empty($r->is_recalled) && $r->days_used !== null) {
                $r->duration_days = (int)$r->days_used;
            } else {
                $r->duration_days = $calcDays($start, $end, $r->office_shift);
            }

            if (!empty($r->original_end_date)) {
                $origEnd = Carbon::parse($r->original_end_date);
                $r->original_duration_days = $calcDays($start, $origEnd, $r->office_shift);
            } else {
                $r->original_duration_days = $r->duration_days;
            }
            
            $r->is_recalled = (int)($r->is_recalled ?? 0);
            $r->days_used = $r->days_used !== null ? (int)$r->days_used : null;
            $r->unused_days_returned = $r->unused_days_returned !== null ? (int)$r->unused_days_returned : null;
            $r->date_applied  = Carbon::parse($r->created_at)->format('d M, Y');
            $r->recalled_at_formatted = $r->recalled_at ? Carbon::parse($r->recalled_at)->format('d M, Y H:i') : null;
            return $r;
        });

        return response()->json([
            'status'       => 'success',
            'leaveRecords' => $records,
        ]);
    }

    /**
     * POST /api/nextjs/hr/apply-leave
     * Submit a new leave application (mirrors saveApplyLeave in LeaveCreateController).
     */
    public function saveApplyLeave(Request $request)
    {
        $request->validate([
            'leave_type'   => 'required|integer',
            'employee_id'  => 'required|integer',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'leave_reason' => 'required|string',
        ]);

        // Validate leave type
        $leaveType = DB::table('tblleave_type')->where('id', $request->leave_type)->first();
        if (!$leaveType) {
            return response()->json(['status' => 'error', 'message' => 'Invalid leave type selected.']);
        }

        // Validate employee
        $employee = DB::table('tblper')->where('ID', $request->employee_id)->first();
        if (!$employee) {
            return response()->json(['status' => 'error', 'message' => 'Employee not found.'], 404);
        }

        // Service Duration Check: Employee must have worked for at least 1 full year in the company before applying for Annual Leave
        $eligibility = $this->checkLeaveEligibility($employee, $request->start_date, $leaveType);
        if (!$eligibility['eligible']) {
            return response()->json([
                'status'  => 'error',
                'message' => $eligibility['message'],
            ], 422);
        }

        // Check for any pending application (status 0) of the same leave type for this employee
        $hasPending = DB::table('leave_record')
            ->where('staffId', $request->employee_id)
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 0)
            ->exists();

        if ($hasPending) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You already have a pending application for ' . $leaveType->leaveType . ' that is waiting for approval.'
            ]);
        }

        // Gender restriction: maternity leave strictly for female staff
        $isMaternity = ($leaveType->id == 3 || stripos($leaveType->leaveType, 'maternity') !== false);
        $staffGender = strtolower(trim($employee->gender ?? ''));
        if ($isMaternity && $staffGender !== 'female' && $staffGender !== 'f') {
            return response()->json(['status' => 'error', 'message' => 'Maternity leave is strictly for female staff and cannot be captured for male staff.']);
        }

        $totalAllowed = $leaveType->days;

        if ($leaveType->id == 3) {
            $year = Carbon::parse($request->start_date)->year;
            $annualDaysUsed = $this->getUsedAnnualLeaveDays($request->employee_id, $year);
            $totalAllowed = max(0, $totalAllowed - $annualDaysUsed);
        }

        // Days already used (fully approved – status 2)
        $usedDays = DB::table('leave_record')
            ->where('staffId', $request->employee_id)
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 2)
            ->sum(DB::raw("DATEDIFF(end_date, start_date) + 1"));

        $start         = Carbon::parse($request->start_date);
        $end           = Carbon::parse($request->end_date);
        $requestedDays = $start->diffInDays($end) + 1;

        if (($usedDays + $requestedDays) > $totalAllowed) {
            $remaining = $totalAllowed - $usedDays;
            return response()->json([
                'status'  => 'error',
                'message' => "You have only {$remaining} remaining day(s). You cannot request {$requestedDays} days.",
            ]);
        }

        DB::table('leave_record')->insert([
            'leave_type_id'   => $leaveType->id,
            'staffId'         => $request->employee_id,
            'start_date'      => $request->start_date,
            'end_date'        => $request->end_date,
            'reason_of_leave' => $request->leave_reason,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Leave application submitted successfully.']);
    }

    /**
     * GET /api/nextjs/hr/apply-leave/calculate-end-date
     * Auto-calculates the end date and remaining days (mirrors calculateEndDate in Blade controller).
     */
    public function calculateEndDate(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'leave_type'  => 'required|integer',
            'start_date'  => 'required|date',
        ]);

        $employee = DB::table('tblper')->where('ID', $request->employee_id)->first();
        if (!$employee) {
            return response()->json(['status' => 'error', 'message' => 'Employee not found'], 404);
        }

        $leaveType = DB::table('tblleave_type')->where('id', $request->leave_type)->first();
        if (!$leaveType) {
            return response()->json(['status' => 'error', 'message' => 'Leave type not found'], 404);
        }

        // Service Duration Check: Employee must have worked for at least 1 full year in the company before applying for Annual Leave
        $eligibility = $this->checkLeaveEligibility($employee, $request->start_date, $leaveType);
        if (!$eligibility['eligible']) {
            return response()->json([
                'status'  => 'error',
                'message' => $eligibility['message'],
                'remaining_days' => 0,
                'end_date' => null,
            ], 422);
        }

        // Gender restriction: maternity leave strictly for female staff
        $isMaternity = ($leaveType->id == 3 || stripos($leaveType->leaveType, 'maternity') !== false);
        $staffGender = strtolower(trim($employee->gender ?? ''));
        if ($isMaternity && $staffGender !== 'female' && $staffGender !== 'f') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Maternity leave is strictly for female staff and cannot be captured for male staff.'
            ]);
        }

        $totalAllowed = $leaveType->days;

        if ($leaveType->id == 3) {
            $year = Carbon::parse($request->start_date)->year;
            $annualDaysUsed = $this->getUsedAnnualLeaveDays($request->employee_id, $year);
            $totalAllowed = max(0, $totalAllowed - $annualDaysUsed);
        }

        $usedDays = DB::table('leave_record')
            ->where('staffId', $request->employee_id)
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 2)
            ->sum(DB::raw("DATEDIFF(end_date, start_date) + 1"));

        $remainingDays = $totalAllowed - $usedDays;

        if ($remainingDays <= 0) {
            return response()->json(['status' => 'success', 'end_date' => null, 'remaining_days' => 0]);
        }

        $startDate = Carbon::parse($request->start_date);

        if ($employee->office_shift == 1) {
            // Exclude weekends
            $current = $startDate->copy();
            $added   = 0;
            while ($added < $remainingDays) {
                if (!in_array($current->dayOfWeek, [0, 6])) {
                    $added++;
                }
                if ($added < $remainingDays) {
                    $current->addDay();
                }
            }
            $endDate = $current->format('Y-m-d');
        } else {
            // Include weekends
            $endDate = $startDate->copy()->addDays($remainingDays - 1)->format('Y-m-d');
        }

        return response()->json([
            'status'         => 'success',
            'end_date'       => $endDate,
            'remaining_days' => $remainingDays,
        ]);
    }

    // ── Approval / Rejection Actions ─────────────────────────────────────────

    /** GET /api/nextjs/hr/apply-leave/hod-approve/{id} */
    public function hodApprove(\Illuminate\Http\Request $request, $id)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx || !$this->hasHodPermission($ctx, 'approve_leave')) {
            return response()->json(['status' => 'error', 'message' => 'HOD or delegated leave approval privileges required.'], 403);
        }

        if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
            $record = DB::table('leave_record')->where('id', $id)->first();
            if ($record) {
                $employee = DB::table('tblper')->where('ID', $record->staffId)->first();
                if (!$employee || $employee->departmentID != $ctx['employee']->departmentID) {
                    return response()->json(['status' => 'error', 'message' => 'Access denied: staff belongs to a different department.'], 403);
                }
            }
        }

        DB::table('leave_record')->where('id', $id)->update(['status' => 1]);
        return response()->json(['status' => 'success', 'message' => 'Leave approved by HOD.']);
    }

    /** GET /api/nextjs/hr/apply-leave/hod-reject/{id} */
    public function hodReject(\Illuminate\Http\Request $request, $id)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx || !$this->hasHodPermission($ctx, 'approve_leave')) {
            return response()->json(['status' => 'error', 'message' => 'HOD or delegated leave approval privileges required.'], 403);
        }

        if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
            $record = DB::table('leave_record')->where('id', $id)->first();
            if ($record) {
                $employee = DB::table('tblper')->where('ID', $record->staffId)->first();
                if (!$employee || $employee->departmentID != $ctx['employee']->departmentID) {
                    return response()->json(['status' => 'error', 'message' => 'Access denied: staff belongs to a different department.'], 403);
                }
            }
        }

        DB::table('leave_record')->where('id', $id)->update(['status' => 3]);
        return response()->json(['status' => 'success', 'message' => 'Leave rejected by HOD.']);
    }

    /** GET /api/nextjs/hr/apply-leave/admin-approve/{id} */
    public function adminApprove(\Illuminate\Http\Request $request, $id)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx || !$this->hasHrPermission($ctx, 'hr_approve_leave')) {
            return response()->json(['status' => 'error', 'message' => 'HR or delegated leave approval privileges required.'], 403);
        }
        DB::table('leave_record')->where('id', $id)->update(['status' => 2]);
        return response()->json(['status' => 'success', 'message' => 'Leave fully approved by HR.']);
    }

    /** GET /api/nextjs/hr/apply-leave/admin-reject/{id} */
    public function adminReject(\Illuminate\Http\Request $request, $id)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx || !$this->hasHrPermission($ctx, 'hr_approve_leave')) {
            return response()->json(['status' => 'error', 'message' => 'HR or delegated leave approval privileges required.'], 403);
        }
        DB::table('leave_record')->where('id', $id)->update(['status' => 4]);
        return response()->json(['status' => 'success', 'message' => 'Leave rejected by Admin.']);
    }

    /**
     * GET /api/nextjs/hr/apply-leave/recall-preview/{id}
     * Preview recall calculations before submitting.
     */
    public function previewRecallLeave(Request $request, $id)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'])) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized: Only HR Head and Super Admin can recall staff from leave.'], 403);
        }

        $record = DB::table('leave_record')
            ->join('tblper', 'tblper.ID', '=', 'leave_record.staffId')
            ->join('tblleave_type', 'tblleave_type.id', '=', 'leave_record.leave_type_id')
            ->join('tbldepartment', 'tbldepartment.id', '=', 'tblper.departmentID')
            ->where('leave_record.id', $id)
            ->select(
                'leave_record.*',
                'tblper.surname',
                'tblper.first_name',
                'tblper.othernames',
                'tblper.office_shift',
                'tbldepartment.department',
                'tblleave_type.leaveType'
            )
            ->first();

        if (!$record) {
            return response()->json(['status' => 'error', 'message' => 'Leave record not found.'], 404);
        }

        if ($record->status != 2) {
            return response()->json(['status' => 'error', 'message' => 'Only fully approved leave records can be recalled.'], 400);
        }

        if (!empty($record->is_recalled)) {
            return response()->json(['status' => 'error', 'message' => 'This leave record has already been recalled.'], 400);
        }

        $resumptionDateStr = $request->query('resumption_date');
        if (!$resumptionDateStr) {
            return response()->json(['status' => 'error', 'message' => 'Resumption date is required for recall preview.'], 422);
        }

        $startDate = Carbon::parse($record->start_date);
        $endDate = Carbon::parse($record->end_date);
        $resumptionDate = Carbon::parse($resumptionDateStr);

        if ($resumptionDate->lt($startDate)) {
            return response()->json(['status' => 'error', 'message' => "Resumption date cannot be earlier than the leave start date ({$startDate->format('d M, Y')})."], 422);
        }

        if ($resumptionDate->gt($endDate)) {
            return response()->json(['status' => 'error', 'message' => "Resumption date cannot be later than the leave end date ({$endDate->format('d M, Y')})."], 422);
        }

        $calcDays = function(Carbon $s, Carbon $e, $shift) {
            if ($s->gt($e)) return 0;
            if ($shift == 1) {
                $days = 0;
                $current = $s->copy();
                while ($current->lte($e)) {
                    if (!$current->isWeekend()) {
                        $days++;
                    }
                    $current->addDay();
                }
                return $days;
            }
            return (int)$s->diffInDays($e) + 1;
        };

        $originalTotalDays = $calcDays($startDate, $endDate, $record->office_shift);

        if ($resumptionDate->equalTo($startDate)) {
            $daysUsed = 0;
            $curtailedEndDate = $startDate->toDateString();
            $unusedDaysReturned = $originalTotalDays;
        } else {
            $lastLeaveDay = $resumptionDate->copy()->subDay();
            $daysUsed = $calcDays($startDate, $lastLeaveDay, $record->office_shift);
            $unusedDaysReturned = max(0, $originalTotalDays - $daysUsed);
            $curtailedEndDate = $lastLeaveDay->toDateString();
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'record_id'            => $record->id,
                'staff_name'           => trim("{$record->surname} {$record->first_name} {$record->othernames}"),
                'department'           => $record->department,
                'leave_type'           => $record->leaveType,
                'office_shift'         => $record->office_shift,
                'start_date'           => $startDate->toDateString(),
                'start_date_formatted' => $startDate->format('d M, Y'),
                'original_end_date'    => $endDate->toDateString(),
                'original_end_date_formatted' => $endDate->format('d M, Y'),
                'original_total_days'  => $originalTotalDays,
                'resumption_date'      => $resumptionDate->toDateString(),
                'resumption_date_formatted' => $resumptionDate->format('d M, Y'),
                'curtailed_end_date'   => $curtailedEndDate,
                'curtailed_end_date_formatted' => Carbon::parse($curtailedEndDate)->format('d M, Y'),
                'days_used'            => $daysUsed,
                'unused_days_returned' => $unusedDaysReturned,
            ]
        ]);
    }

    /**
     * POST /api/nextjs/hr/apply-leave/recall/{id}
     * Process staff leave recall and return unused days to staff balance.
     */
    public function recallLeave(Request $request, $id)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'])) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized: Only HR Head and Super Admin can recall staff from leave.'], 403);
        }

        $validated = $request->validate([
            'resumption_date' => 'required|date',
            'recall_reason'   => 'required|string|max:1000',
        ]);

        $record = DB::table('leave_record')
            ->join('tblper', 'tblper.ID', '=', 'leave_record.staffId')
            ->join('tblleave_type', 'tblleave_type.id', '=', 'leave_record.leave_type_id')
            ->where('leave_record.id', $id)
            ->select('leave_record.*', 'tblper.surname', 'tblper.first_name', 'tblper.othernames', 'tblper.office_shift', 'tblleave_type.leaveType')
            ->first();

        if (!$record) {
            return response()->json(['status' => 'error', 'message' => 'Leave record not found.'], 404);
        }

        if ($record->status != 2) {
            return response()->json(['status' => 'error', 'message' => 'Only fully approved leave records can be recalled.'], 400);
        }

        if (!empty($record->is_recalled)) {
            return response()->json(['status' => 'error', 'message' => 'This leave record has already been recalled.'], 400);
        }

        $startDate = Carbon::parse($record->start_date);
        $endDate = Carbon::parse($record->end_date);
        $resumptionDate = Carbon::parse($validated['resumption_date']);

        if ($resumptionDate->lt($startDate)) {
            return response()->json(['status' => 'error', 'message' => "Resumption date cannot be earlier than the leave start date ({$startDate->format('d M, Y')})."], 422);
        }

        if ($resumptionDate->gt($endDate)) {
            return response()->json(['status' => 'error', 'message' => "Resumption date cannot be later than the leave end date ({$endDate->format('d M, Y')})."], 422);
        }

        $calcDays = function(Carbon $s, Carbon $e, $shift) {
            if ($s->gt($e)) return 0;
            if ($shift == 1) {
                $days = 0;
                $current = $s->copy();
                while ($current->lte($e)) {
                    if (!$current->isWeekend()) {
                        $days++;
                    }
                    $current->addDay();
                }
                return $days;
            }
            return (int)$s->diffInDays($e) + 1;
        };

        $originalTotalDays = $calcDays($startDate, $endDate, $record->office_shift);

        if ($resumptionDate->equalTo($startDate)) {
            $daysUsed = 0;
            $curtailedEndDate = $startDate->toDateString();
            $unusedDaysReturned = $originalTotalDays;
            $newStatus = 5; // Recalled before start -> 0 days counted
        } else {
            $lastLeaveDay = $resumptionDate->copy()->subDay();
            $daysUsed = $calcDays($startDate, $lastLeaveDay, $record->office_shift);
            $unusedDaysReturned = max(0, $originalTotalDays - $daysUsed);
            $curtailedEndDate = $lastLeaveDay->toDateString();
            $newStatus = 2; // Approved & curtailed
        }

        $userId = $ctx['userId'] ?? null;

        DB::table('leave_record')->where('id', $id)->update([
            'is_recalled'          => 1,
            'original_end_date'    => $record->end_date,
            'end_date'             => $curtailedEndDate,
            'recall_date'          => $resumptionDate->toDateString(),
            'days_used'            => $daysUsed,
            'unused_days_returned' => $unusedDaysReturned,
            'recall_reason'        => $validated['recall_reason'],
            'recalled_by'          => $userId,
            'recalled_at'          => now(),
            'status'               => $newStatus,
            'updated_at'           => now(),
        ]);

        $staffName = trim("{$record->surname} {$record->first_name} {$record->othernames}");

        return response()->json([
            'status'  => 'success',
            'message' => "Staff {$staffName} successfully recalled from leave. {$unusedDaysReturned} unused day(s) have been returned to their leave balance.",
            'data'    => [
                'days_used'            => $daysUsed,
                'unused_days_returned' => $unusedDaysReturned,
                'recall_date'          => $resumptionDate->toDateString(),
                'curtailed_end_date'   => $curtailedEndDate,
            ]
        ]);
    }

    /**
     * PUT /api/nextjs/hr/apply-leave/{id}
     * Update an existing pending leave application.
     */
    public function updateApplyLeave(Request $request, $id)
    {
        $request->validate([
            'leave_type'   => 'required|integer',
            'employee_id'  => 'required|integer',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'leave_reason' => 'required|string',
        ]);

        // Find the record
        $record = DB::table('leave_record')->where('id', $id)->first();
        if (!$record) {
            return response()->json(['status' => 'error', 'message' => 'Leave record not found.'], 404);
        }

        // Only pending leave (status 0) can be edited
        if ($record->status != 0) {
            return response()->json(['status' => 'error', 'message' => 'This leave application has already been processed and cannot be edited.']);
        }

        // Validate leave type
        $leaveType = DB::table('tblleave_type')->where('id', $request->leave_type)->first();
        if (!$leaveType) {
            return response()->json(['status' => 'error', 'message' => 'Invalid leave type selected.']);
        }

        // Validate employee
        $employee = DB::table('tblper')->where('ID', $request->employee_id)->first();
        if (!$employee) {
            return response()->json(['status' => 'error', 'message' => 'Employee not found.'], 404);
        }

        // Service Duration Check: Employee must have worked for at least 1 full year in the company before applying for Annual Leave
        $eligibility = $this->checkLeaveEligibility($employee, $request->start_date, $leaveType);
        if (!$eligibility['eligible']) {
            return response()->json([
                'status'  => 'error',
                'message' => $eligibility['message'],
            ], 422);
        }

        // Gender restriction: maternity leave strictly for female staff
        $isMaternity = ($leaveType->id == 3 || stripos($leaveType->leaveType, 'maternity') !== false);
        $staffGender = strtolower(trim($employee->gender ?? ''));
        if ($isMaternity && $staffGender !== 'female' && $staffGender !== 'f') {
            return response()->json(['status' => 'error', 'message' => 'Maternity leave is strictly for female staff and cannot be captured for male staff.']);
        }

        // Check for any OTHER pending application of this leave type (excluding this one)
        $hasPending = DB::table('leave_record')
            ->where('staffId', $request->employee_id)
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 0)
            ->where('id', '!=', $id)
            ->exists();

        if ($hasPending) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You already have a pending application for ' . $leaveType->leaveType . ' that is waiting for approval.'
            ]);
        }

        $totalAllowed = $leaveType->days;

        if ($leaveType->id == 3) {
            $year = Carbon::parse($request->start_date)->year;
            $annualDaysUsed = $this->getUsedAnnualLeaveDays($request->employee_id, $year);
            $totalAllowed = max(0, $totalAllowed - $annualDaysUsed);
        }

        // Days already used (excluding this one)
        $usedDays = DB::table('leave_record')
            ->where('staffId', $request->employee_id)
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 2)
            ->where('id', '!=', $id)
            ->sum(DB::raw("DATEDIFF(end_date, start_date) + 1"));

        $start         = Carbon::parse($request->start_date);
        $end           = Carbon::parse($request->end_date);
        $requestedDays = $start->diffInDays($end) + 1;

        if (($usedDays + $requestedDays) > $totalAllowed) {
            $remaining = $totalAllowed - $usedDays;
            return response()->json([
                'status'  => 'error',
                'message' => "You have only {$remaining} remaining day(s). You cannot request {$requestedDays} days.",
            ]);
        }

        DB::table('leave_record')->where('id', $id)->update([
            'leave_type_id'   => $leaveType->id,
            'staffId'         => $request->employee_id,
            'start_date'      => $request->start_date,
            'end_date'        => $request->end_date,
            'reason_of_leave' => $request->leave_reason,
            'updated_at'      => now(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Leave application updated successfully.']);
    }

    /**
     * GET /api/nextjs/hr/apply-loa
     * Returns employees list and user session/role context for LOA module.
     */
    public function getApplyLoaData(Request $request)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
        }

        $isExecutive = $ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || $ctx['isAuditStaff'] || $ctx['isFinanceStaff'];

        $salaryMap = DB::table('salary_structures')->get()->keyBy('staffId');
        $firstSalaryMap = DB::table('first_salary_structure')->get()->keyBy('staffId');

        $calcSalary = function($staffId) use ($salaryMap, $firstSalaryMap) {
            $struct = $salaryMap[$staffId] ?? null;
            $monthlySalary = 0.00;
            if ($struct) {
                $monthlySalary = (float)$struct->basic_salary +
                                 (float)$struct->housing_allowance +
                                 (float)$struct->transport_allowance +
                                 (float)$struct->medical_allowance +
                                 (float)$struct->utility_allowance +
                                 (float)$struct->meal_allowance;
                if ($monthlySalary <= 0 && (float)$struct->declare_salary > 0) {
                    $monthlySalary = (float)$struct->declare_salary;
                }
            }
            if ($monthlySalary <= 0) {
                $first = $firstSalaryMap[$staffId] ?? null;
                if ($first) {
                    $monthlySalary = (float)$first->basic_salary +
                                     (float)$first->housing_allowance +
                                     (float)$first->transport_allowance +
                                     (float)$first->medical_allowance +
                                     (float)$first->utility_allowance +
                                     (float)$first->meal_allowance;
                }
            }
            return $monthlySalary;
        };

        if ($isExecutive) {
            $employees = DB::table('tblper')->select('ID', 'surname', 'first_name', 'othernames')->get()->map(function($emp) use ($calcSalary) {
                $emp->has_uploaded_education = DB::table('tbleducations')
                    ->where('staffid', $emp->ID)
                    ->whereNotNull('document')
                    ->where('document', '!=', '')
                    ->exists();
                $emp->monthly_salary = $calcSalary($emp->ID);
                return $emp;
            });
        } else {
            $employees = collect();
        }

        $employee = $ctx['employee'];
        if ($employee) {
            $employee->has_uploaded_education = DB::table('tbleducations')
                ->where('staffid', $employee->ID)
                ->whereNotNull('document')
                ->where('document', '!=', '')
                ->exists();
            $employee->monthly_salary = $calcSalary($employee->ID);
        }

        return response()->json([
            'status'        => 'success',
            'employees'     => $employees,
            'isSuperAdmin'  => $ctx['isSuperAdmin'],
            'isHod'         => $ctx['isHod'],
            'isAdminStaff'  => $ctx['isAdminStaff'],
            'isAuditStaff'  => $ctx['isAuditStaff'],
            'isFinanceStaff'=> $ctx['isFinanceStaff'],
            'employee'      => $employee,
        ]);
    }

    /**
     * GET /api/nextjs/hr/apply-loa/records
     * Returns LOA records filtered by role.
     */
    public function getLoaRecords(Request $request)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
        }

        $baseQuery = DB::table('leave_of_absent')
            ->join('tblper', 'tblper.ID', '=', 'leave_of_absent.staffId')
            ->join('tbldepartment', 'tbldepartment.id', '=', 'tblper.departmentID')
            ->select(
                'leave_of_absent.*',
                'leave_of_absent.id as loa_id',
                'leave_of_absent.id as id',
                'tblper.surname',
                'tblper.first_name',
                'tblper.othernames',
                'tblper.office_shift',
                'tblper.departmentID as department_id',
                'tbldepartment.department'
            )
            ->orderBy('leave_of_absent.id', 'DESC');

        $employee = $ctx['employee'];
        $isExecutive = $ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || $ctx['isAuditStaff'] || $ctx['isFinanceStaff'];

        if ($isExecutive) {
            // Super Admin, HR Head, Audit Head, and Finance Head see all records of all staff
            $records = $baseQuery->get();
        } else {
            $baseQuery->where(function ($query) use ($ctx, $employee) {
                $hasCondition = false;

                // 1. Own records
                if ($employee) {
                    $query->where('leave_of_absent.staffId', $employee->ID);
                    $hasCondition = true;
                }

                // 2. HOD sees records of staff in their department
                if ($employee && $ctx['isHod']) {
                    $hodDeptId = ($ctx['isDelegatedHod'] ?? false) ? $ctx['delegated_department_id'] : $employee->departmentID;
                    if ($hasCondition) {
                        $query->orWhere('tblper.departmentID', $hodDeptId);
                    } else {
                        $query->where('tblper.departmentID', $hodDeptId);
                    }
                    $hasCondition = true;
                }

                // Fallback if no roles matched
                if (!$hasCondition) {
                    $query->where('leave_of_absent.id', 0);
                }
            });
            $records = $baseQuery->get();
        }

        $staffIds = $records->pluck('staffId')->unique()->filter()->values();
        $salaryMap = DB::table('salary_structures')->whereIn('staffId', $staffIds)->get()->keyBy('staffId');
        $firstSalaryMap = DB::table('first_salary_structure')->whereIn('staffId', $staffIds)->get()->keyBy('staffId');

        $records = $records->map(function ($r) use ($salaryMap, $firstSalaryMap) {
            $start = Carbon::parse($r->start_date);
            $end   = Carbon::parse($r->end_date);
            
            if ($r->office_shift == 1) {
                $days = 0;
                $current = $start->copy();
                while ($current->lte($end)) {
                    if (!$current->isWeekend()) {
                        $days++;
                    }
                    $current->addDay();
                }
                $r->duration_days = $days;
            } else {
                $r->duration_days = $start->diffInDays($end) + 1;
            }

            // Calculate salary structure
            $struct = $salaryMap[$r->staffId] ?? null;
            $monthlySalary = 0.00;
            if ($struct) {
                $monthlySalary = (float)$struct->basic_salary +
                                 (float)$struct->housing_allowance +
                                 (float)$struct->transport_allowance +
                                 (float)$struct->medical_allowance +
                                 (float)$struct->utility_allowance +
                                 (float)$struct->meal_allowance;
                if ($monthlySalary <= 0 && (float)$struct->declare_salary > 0) {
                    $monthlySalary = (float)$struct->declare_salary;
                }
            }
            if ($monthlySalary <= 0) {
                $first = $firstSalaryMap[$r->staffId] ?? null;
                if ($first) {
                    $monthlySalary = (float)$first->basic_salary +
                                     (float)$first->housing_allowance +
                                     (float)$first->transport_allowance +
                                     (float)$first->medical_allowance +
                                     (float)$first->utility_allowance +
                                     (float)$first->meal_allowance;
                }
            }

            // Cross-month prorated calculation
            $currentMonth = $start->copy()->startOfMonth();
            $endMonth = $end->copy()->startOfMonth();
            $totalEstimatedDeduction = 0.00;
            $monthBreakdowns = [];

            while ($currentMonth->lte($endMonth)) {
                $mStart = $currentMonth->copy()->startOfMonth();
                $mEnd = $currentMonth->copy()->endOfMonth();
                $daysInThisMonth = (int)$currentMonth->daysInMonth;
                $mName = $currentMonth->format('F Y');

                $overlapStart = $start->greaterThan($mStart) ? $start : $mStart;
                $overlapEnd = $end->lessThan($mEnd) ? $end : $mEnd;

                $mDays = 0;
                if ($r->office_shift == 1) {
                    $cur = $overlapStart->copy();
                    while ($cur->lte($overlapEnd)) {
                        if (!$cur->isWeekend()) {
                            $mDays++;
                        }
                        $cur->addDay();
                    }
                } else {
                    $mDays = $overlapStart->diffInDays($overlapEnd) + 1;
                }

                $mDailyRate = $daysInThisMonth > 0 ? round($monthlySalary / $daysInThisMonth, 2) : 0.00;
                $mDeduction = round($mDailyRate * $mDays, 2);
                $totalEstimatedDeduction += $mDeduction;

                $monthBreakdowns[] = [
                    'month_name'    => $mName,
                    'days_in_month' => $daysInThisMonth,
                    'days_on_leave' => $mDays,
                    'daily_rate'    => $mDailyRate,
                    'deduction'     => $mDeduction,
                ];

                $currentMonth->addMonth();
            }

            $primaryDaysInMonth = (int)$start->daysInMonth;
            $primaryDailyRate = $primaryDaysInMonth > 0 ? round($monthlySalary / $primaryDaysInMonth, 2) : 0.00;

            $r->monthly_salary = $monthlySalary;
            $r->daily_rate = $primaryDailyRate;
            $r->days_in_month = $primaryDaysInMonth;
            $r->month_name = count($monthBreakdowns) > 1 
                ? ($start->format('M Y') . ' – ' . $end->format('M Y'))
                : $start->format('F Y');
            $r->estimated_deduction = round($totalEstimatedDeduction, 2);
            $r->month_breakdowns = $monthBreakdowns;
            $r->date_applied = Carbon::parse($r->created_at)->format('d M, Y');
            return $r;
        });

        return response()->json([
            'status'     => 'success',
            'loaRecords' => $records,
        ]);
    }

    /**
     * GET /api/nextjs/hr/apply-loa/export
     * Export Leave of Absence (LOA) records to a formatted CSV spreadsheet.
     */
    public function exportLoaSpreadsheet(Request $request)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
        }

        // Fetch records with role filtering applied
        $res = $this->getLoaRecords($request);
        $data = $res->getData(true);
        $records = $data['loaRecords'] ?? [];

        // Apply filters if passed
        $statusFilter = $request->query('status', 'all');
        $startDateFilter = $request->query('start_date');
        $endDateFilter = $request->query('end_date');
        $search = trim($request->query('search', ''));

        $records = collect($records)->filter(function ($rec) use ($statusFilter, $startDateFilter, $endDateFilter, $search) {
            if ($search !== '') {
                $q = strtolower($search);
                $name = strtolower(trim(($rec['surname'] ?? '') . ' ' . ($rec['first_name'] ?? '') . ' ' . ($rec['othernames'] ?? '')));
                $dept = strtolower($rec['department'] ?? '');
                $reason = strtolower($rec['reason_of_leave'] ?? '');
                $staffId = (string)($rec['staffId'] ?? '');
                if (!str_contains($name, $q) && !str_contains($dept, $q) && !str_contains($reason, $q) && !str_contains($staffId, $q)) {
                    return false;
                }
            }

            if ($startDateFilter) {
                $start = Carbon::parse($startDateFilter)->startOfDay();
                $applied = Carbon::parse($rec['created_at'] ?? $rec['date_applied'])->startOfDay();
                if ($applied->lt($start)) return false;
            }

            if ($endDateFilter) {
                $end = Carbon::parse($endDateFilter)->endOfDay();
                $applied = Carbon::parse($rec['created_at'] ?? $rec['date_applied'])->startOfDay();
                if ($applied->gt($end)) return false;
            }

            if ($statusFilter !== 'all') {
                $status = (int)$rec['status'];
                $isPending = !in_array($status, [1, 2, 3, 4]);
                $isApproved = in_array($status, [1, 2]);
                $isRejected = in_array($status, [3, 4]);

                if ($statusFilter === 'pending' && !$isPending) return false;
                if ($statusFilter === 'approved' && !$isApproved) return false;
                if ($statusFilter === 'rejected' && !$isRejected) return false;
            }

            return true;
        })->values();

        $filename = "Leave_of_Absence_Records_" . date('Y_m_d_His') . ".csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            // Title block
            fputcsv($handle, ['ISALU HRMS — LEAVE OF ABSENCE (LOA) APPLICATIONS & DEDUCTION REPORT']);
            fputcsv($handle, ['Generated on: ' . date('d M Y, h:i A') . ' | Total Records: ' . $records->count()]);
            fputcsv($handle, []);

            // Column Headers
            fputcsv($handle, [
                'S/N',
                'Staff ID',
                'Staff Name',
                'Department',
                'Start Date',
                'End Date',
                'Duration (Days)',
                'Days in Month',
                'Target Month',
                'Monthly Gross Salary (NGN)',
                'Daily Salary Rate (NGN)',
                'Estimated LOA Deduction (NGN)',
                'Date Applied',
                'Status',
                'Reason for Leave of Absence'
            ]);

            $totalDeductions = 0.0;
            $totalDays = 0;

            foreach ($records as $index => $r) {
                $name = trim(($r['surname'] ?? '') . ' ' . ($r['first_name'] ?? '') . ' ' . ($r['othernames'] ?? ''));
                $statusText = match ((int)($r['status'] ?? 0)) {
                    1 => 'HOD Approved',
                    2 => 'HR Approved',
                    3 => 'HOD Rejected',
                    4 => 'HR Rejected',
                    default => 'Pending',
                };

                $deduction = (float)($r['estimated_deduction'] ?? 0.0);
                $duration = (int)($r['duration_days'] ?? 0);
                $totalDeductions += $deduction;
                $totalDays += $duration;

                fputcsv($handle, [
                    $index + 1,
                    $r['staffId'] ?? '',
                    $name,
                    $r['department'] ?? '',
                    Carbon::parse($r['start_date'])->format('d M, Y'),
                    Carbon::parse($r['end_date'])->format('d M, Y'),
                    $duration,
                    $r['days_in_month'] ?? '',
                    $r['month_name'] ?? '',
                    number_format((float)($r['monthly_salary'] ?? 0.0), 2),
                    number_format((float)($r['daily_rate'] ?? 0.0), 2),
                    number_format($deduction, 2),
                    $r['date_applied'] ?? Carbon::parse($r['created_at'])->format('d M, Y'),
                    $statusText,
                    $r['reason_of_leave'] ?? ''
                ]);
            }

            // Summary Totals Row
            fputcsv($handle, []);
            fputcsv($handle, [
                'TOTAL',
                '',
                '',
                '',
                '',
                '',
                $totalDays . ' days',
                '',
                '',
                '',
                '',
                number_format($totalDeductions, 2),
                '',
                '',
                ''
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }


    /**
     * POST /api/nextjs/hr/apply-loa
     * Submit a new Leave of Absence request.
     */
    public function saveApplyLoa(Request $request)
    {
        $request->validate([
            'employee_id'  => 'required|integer',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'leave_reason' => 'required|string',
        ]);

        $employee = DB::table('tblper')->where('ID', $request->employee_id)->first();
        if (!$employee) {
            return response()->json(['status' => 'error', 'message' => 'Employee not found.']);
        }

        // Check for any pending application (status 0) of LOA for this employee
        $hasPending = DB::table('leave_of_absent')
            ->where('staffId', $request->employee_id)
            ->where('status', 0)
            ->exists();

        if ($hasPending) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You already have a pending leave of absence application that is waiting for approval.'
            ], 422);
        }

        // Net Pay check: Leave of Absence deduction must not cause net pay to reach 0.00 or negative
        $impact = $this->checkLoaNetPayImpact($request->employee_id, $request->start_date, $request->end_date);
        if (!$impact['valid']) {
            return response()->json([
                'status'  => 'error',
                'message' => $impact['message']
            ], 422);
        }

        DB::table('leave_of_absent')->insert([
            'staffId'           => $request->employee_id,
            'start_date'        => $request->start_date,
            'end_date'          => $request->end_date,
            'reason_of_leave'   => $request->leave_reason,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Leave of absence application submitted successfully.']);
    }

    /**
     * PUT /api/nextjs/hr/apply-loa/{id}
     * Update an existing pending Leave of Absence request.
     */
    public function updateApplyLoa(Request $request, $id)
    {
        $request->validate([
            'employee_id'  => 'required|integer',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'leave_reason' => 'required|string',
        ]);

        $record = DB::table('leave_of_absent')->where('id', $id)->first();
        if (!$record) {
            return response()->json(['status' => 'error', 'message' => 'Leave of absence record not found.'], 404);
        }

        if ($record->status != 0) {
            return response()->json(['status' => 'error', 'message' => 'This leave of absence application has already been processed and cannot be edited.'], 422);
        }

        $employee = DB::table('tblper')->where('ID', $request->employee_id)->first();
        if (!$employee) {
            return response()->json(['status' => 'error', 'message' => 'Employee not found.'], 404);
        }

        // Net Pay check: Leave of Absence deduction must not cause net pay to reach 0.00 or negative
        $impact = $this->checkLoaNetPayImpact($request->employee_id, $request->start_date, $request->end_date, $id);
        if (!$impact['valid']) {
            return response()->json([
                'status'  => 'error',
                'message' => $impact['message']
            ], 422);
        }

        DB::table('leave_of_absent')->where('id', $id)->update([
            'staffId'           => $request->employee_id,
            'start_date'        => $request->start_date,
            'end_date'          => $request->end_date,
            'reason_of_leave'   => $request->leave_reason,
            'updated_at'        => now(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Leave of absence application updated successfully.']);
    }

    /** GET /api/nextjs/hr/apply-loa/hod-approve/{id} */
    public function hodApproveLoa(\Illuminate\Http\Request $request, $id)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx || !$this->hasHodPermission($ctx, 'approve_leave')) {
            return response()->json(['status' => 'error', 'message' => 'HOD or delegated leave approval privileges required.'], 403);
        }

        $record = DB::table('leave_of_absent')->where('id', $id)->first();
        if (!$record) {
            return response()->json(['status' => 'error', 'message' => 'Leave record not found.'], 404);
        }

        if ($record->status != 0) {
            return response()->json(['status' => 'error', 'message' => 'This Leave of Absence application is not in pending HOD status.'], 422);
        }

        if (!$ctx['isSuperAdmin']) {
            $employee = DB::table('tblper')->where('ID', $record->staffId)->first();
            $activeDeptId = (isset($ctx['isDelegatedHod']) && $ctx['isDelegatedHod']) 
                ? $ctx['delegated_department_id'] 
                : ($ctx['employee'] ? $ctx['employee']->departmentID : null);

            if (!$employee || $employee->departmentID != $activeDeptId) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: You can only approve applications for staff in your department.'], 403);
            }
        }

        DB::table('leave_of_absent')->where('id', $id)->update(['status' => 1]);
        return response()->json(['status' => 'success', 'message' => 'Leave of absence approved by HOD.']);
    }

    /** GET /api/nextjs/hr/apply-loa/hod-reject/{id} */
    public function hodRejectLoa(\Illuminate\Http\Request $request, $id)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx || !$this->hasHodPermission($ctx, 'approve_leave')) {
            return response()->json(['status' => 'error', 'message' => 'HOD or delegated leave approval privileges required.'], 403);
        }

        $record = DB::table('leave_of_absent')->where('id', $id)->first();
        if (!$record) {
            return response()->json(['status' => 'error', 'message' => 'Leave record not found.'], 404);
        }

        if ($record->status != 0) {
            return response()->json(['status' => 'error', 'message' => 'This Leave of Absence application is not in pending HOD status.'], 422);
        }

        if (!$ctx['isSuperAdmin']) {
            $employee = DB::table('tblper')->where('ID', $record->staffId)->first();
            $activeDeptId = (isset($ctx['isDelegatedHod']) && $ctx['isDelegatedHod']) 
                ? $ctx['delegated_department_id'] 
                : ($ctx['employee'] ? $ctx['employee']->departmentID : null);

            if (!$employee || $employee->departmentID != $activeDeptId) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: You can only reject applications for staff in your department.'], 403);
            }
        }

        DB::table('leave_of_absent')->where('id', $id)->update(['status' => 3]);
        return response()->json(['status' => 'success', 'message' => 'Leave of absence rejected by HOD.']);
    }

    /** GET /api/nextjs/hr/apply-loa/admin-approve/{id} */
    public function adminApproveLoa(\Illuminate\Http\Request $request, $id)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx || !$this->hasHrPermission($ctx, 'hr_approve_leave')) {
            return response()->json(['status' => 'error', 'message' => 'HR or delegated leave approval privileges required.'], 403);
        }

        $record = DB::table('leave_of_absent')->where('id', $id)->first();
        if (!$record) {
            return response()->json(['status' => 'error', 'message' => 'Leave record not found.'], 404);
        }

        if ($record->status != 1) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cannot approve: The Head of Department (HOD) must approve this Leave of Absence application before HR HEAD can approve.'
            ], 422);
        }

        DB::table('leave_of_absent')->where('id', $id)->update(['status' => 2]);
        return response()->json(['status' => 'success', 'message' => 'Leave of absence fully approved by HR.']);
    }

    /** GET /api/nextjs/hr/apply-loa/admin-reject/{id} */
    public function adminRejectLoa(\Illuminate\Http\Request $request, $id)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx || !$this->hasHrPermission($ctx, 'hr_approve_leave')) {
            return response()->json(['status' => 'error', 'message' => 'HR or delegated leave approval privileges required.'], 403);
        }

        $record = DB::table('leave_of_absent')->where('id', $id)->first();
        if (!$record) {
            return response()->json(['status' => 'error', 'message' => 'Leave record not found.'], 404);
        }

        if ($record->status != 1) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cannot reject at HR stage: HOD has not approved this Leave of Absence application yet.'
            ], 422);
        }

        DB::table('leave_of_absent')->where('id', $id)->update(['status' => 4]);
        return response()->json(['status' => 'success', 'message' => 'Leave of absence rejected by Admin.']);
    }

    /**
     * Helper to validate that a proposed Leave of Absence does not reduce employee net pay to <= 0 in any month.
     */
    public function checkLoaNetPayImpact($staffId, $startDate, $endDate, $excludeLoaId = null)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $employee = DB::table('tblper')->where('ID', $staffId)->first();
        if (!$employee) {
            return ['valid' => true];
        }

        // Iterate through each month spanned by the start and end dates
        $currentMonth = $start->copy()->startOfMonth();
        $endMonth = $end->copy()->startOfMonth();

        while ($currentMonth->lte($endMonth)) {
            $year = (int)$currentMonth->year;
            $month = (int)$currentMonth->month;
            $daysInMonth = (int)$currentMonth->daysInMonth;
            $monthName = $currentMonth->format('F Y');
            $currentMonthStr = sprintf("%04d-%02d", $year, $month);

            $startOfMonth = $currentMonth->copy()->startOfMonth();
            $endOfMonth = $currentMonth->copy()->endOfMonth();

            // Calculate LOA days for the proposed application in this month
            $overlapStart = $start->greaterThan($startOfMonth) ? $start : $startOfMonth;
            $overlapEnd = $end->lessThan($endOfMonth) ? $end : $endOfMonth;

            $proposedLoaDaysInMonth = 0;
            if ($employee->office_shift == 1) {
                $cur = $overlapStart->copy();
                while ($cur->lte($overlapEnd)) {
                    if (!$cur->isWeekend()) {
                        $proposedLoaDaysInMonth++;
                    }
                    $cur->addDay();
                }
            } else {
                $proposedLoaDaysInMonth = $overlapStart->diffInDays($overlapEnd) + 1;
            }

            // Existing approved/active LOAs in this month (excluding $excludeLoaId)
            $existingLoaQuery = DB::table('leave_of_absent')
                ->where('staffId', $staffId)
                ->whereIn('status', [0, 1, 2]) // Pending or Approved
                ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                    $query->whereBetween('start_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                          ->orWhereBetween('end_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                          ->orWhere(function ($q) use ($startOfMonth, $endOfMonth) {
                              $q->where('start_date', '<=', $startOfMonth->toDateString())
                                ->where('end_date', '>=', $endOfMonth->toDateString());
                          });
                });

            if ($excludeLoaId) {
                $existingLoaQuery->where('id', '!=', $excludeLoaId);
            }

            $existingLoas = $existingLoaQuery->get();
            $otherLoaDays = 0;
            foreach ($existingLoas as $leave) {
                $lStart = Carbon::parse($leave->start_date);
                $lEnd = Carbon::parse($leave->end_date);
                $oStart = $lStart->greaterThan($startOfMonth) ? $lStart : $startOfMonth;
                $oEnd = $lEnd->lessThan($endOfMonth) ? $lEnd : $endOfMonth;
                if ($employee->office_shift == 1) {
                    $cur = $oStart->copy();
                    while ($cur->lte($oEnd)) {
                        if (!$cur->isWeekend()) {
                            $otherLoaDays++;
                        }
                        $cur->addDay();
                    }
                } else {
                    $otherLoaDays += $oStart->diffInDays($oEnd) + 1;
                }
            }

            $totalLoaDaysInMonth = min($daysInMonth, $proposedLoaDaysInMonth + $otherLoaDays);

            // Fetch Salary Structure
            $struct = DB::table('salary_structures')->where('staffId', $staffId)->first();
            $basic = 0.00;
            $housing = 0.00;
            $transport = 0.00;
            $medical = 0.00;
            $utility = 0.00;
            $meal = 0.00;
            $pensionRate = 0.00;
            $declareSalary = 0.00;

            if ($struct) {
                $basic = (float)($struct->basic_salary ?? 0);
                $housing = (float)($struct->housing_allowance ?? 0);
                $transport = (float)($struct->transport_allowance ?? 0);
                $medical = (float)($struct->medical_allowance ?? 0);
                $utility = (float)($struct->utility_allowance ?? 0);
                $meal = (float)($struct->meal_allowance ?? 0);
                $pensionRate = (float)($struct->pension_rate ?? 0);
                $declareSalary = (float)($struct->declare_salary ?? 0);
            }

            $totalBasicAllowances = $basic + $housing + $transport + $medical + $utility + $meal;

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
                    ->get();
                $totalEarningVars = (float)$bonuses->sum('amount');
            }

            $grossPay = $totalBasicAllowances + $totalEarningVars;
            $taxBase = ($declareSalary > 0) ? $declareSalary : $totalBasicAllowances;

            // LOA Deduction for this month
            $loaDeduction = ($daysInMonth > 0) ? round(($grossPay / (float)$daysInMonth) * $totalLoaDaysInMonth, 2) : 0.00;
            $paidDays = max(0, $daysInMonth - $totalLoaDaysInMonth);

            // PAYE Tax (Prorated based on paid days)
            $annualGross = $taxBase * 12.0;
            $annualPension = 0.00;
            if ($struct && (int)($struct->pen_act ?? 0) === 1) {
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
            $fullMonthlyTax = round($annualTax / 12.0, 2);
            $payeTax = ($daysInMonth > 0) ? round($fullMonthlyTax * ($paidDays / (float)$daysInMonth), 2) : 0.00;

            // Pension
            $pension = 0.00;
            if ($struct && (int)($struct->pen_act ?? 0) === 1) {
                $rate = ($pensionRate > 0) ? ($pensionRate / 100.0) : 0.08;
                $pension = round(($totalBasicAllowances * 0.5) * $rate, 2);
            }

            // Retention
            $retention = 0.00;
            if (\Illuminate\Support\Facades\Schema::hasTable('first_salary_structure')) {
                $firstStruct = DB::table('first_salary_structure')->where('staffId', $staffId)->first();
                if ($firstStruct && (int)($firstStruct->reten_act ?? 0) === 1 && (int)($firstStruct->num_rente_months ?? 0) < 20) {
                    $retentionBase = (float)($firstStruct->basic_salary ?? 0) +
                                     (float)($firstStruct->housing_allowance ?? 0) +
                                     (float)($firstStruct->transport_allowance ?? 0) +
                                     (float)($firstStruct->medical_allowance ?? 0) +
                                     (float)($firstStruct->utility_allowance ?? 0) +
                                     (float)($firstStruct->meal_allowance ?? 0);
                    $retention = round(0.05 * $retentionBase, 2);
                }
            }

            // IOUs for this month
            $firstDayStr = $startOfMonth->toDateString();
            $lastDayStr = $endOfMonth->toDateString();
            $iouSum = 0.00;
            if (\Illuminate\Support\Facades\Schema::hasTable('iou_records')) {
                $iouSum = (float)DB::table('iou_records')
                    ->where('staff_id', $staffId)
                    ->where('status', '!=', 2)
                    ->whereBetween('iou_date', [$firstDayStr, $lastDayStr])
                    ->sum('amount');
            }

            // Medical Loan Setup
            $medicalLoanDeduct = 0.00;
            if (\Illuminate\Support\Facades\Schema::hasTable('medical_loan_deduction_setups')) {
                $medLoan = DB::table('medical_loan_deduction_setups')
                    ->where('staffId', $staffId)
                    ->where('is_active', 1)
                    ->where('balance_remaining', '>', 0)
                    ->where('start_month', '<=', $currentMonthStr)
                    ->where('end_month', '>=', $currentMonthStr)
                    ->orderBy('id', 'desc')->first();
                if ($medLoan) {
                    $medicalLoanDeduct = min((float)$medLoan->monthly_deduction, (float)$medLoan->balance_remaining);
                }
            }

            // Coop Loan Setup
            $coopLoanDeduct = 0.00;
            if (\Illuminate\Support\Facades\Schema::hasTable('coop_loan_deduction_setups')) {
                $coopLoan = DB::table('coop_loan_deduction_setups')
                    ->where('staffId', $staffId)
                    ->where('is_active', 1)
                    ->where('balance_remaining', '>', 0)
                    ->where('start_month', '<=', $currentMonthStr)
                    ->where('end_month', '>=', $currentMonthStr)
                    ->orderBy('id', 'desc')->first();
                if ($coopLoan) {
                    $coopLoanDeduct = min((float)$coopLoan->monthly_deduction, (float)$coopLoan->balance_remaining);
                }
            }

            // Coop Savings Setup
            $coopSavingsDeduct = 0.00;
            if (\Illuminate\Support\Facades\Schema::hasTable('coop_savings_setups')) {
                $coopSavings = DB::table('coop_savings_setups')
                    ->where('staffId', $staffId)
                    ->where('is_active', 1)
                    ->where('start_month', '<=', $currentMonthStr)
                    ->orderBy('id', 'desc')->first();
                if ($coopSavings) {
                    $coopSavingsDeduct = (float)$coopSavings->monthly_saving;
                }
            }

            // Coop Asset Finance Setup
            $coopAssetDeduct = 0.00;
            if (\Illuminate\Support\Facades\Schema::hasTable('coop_asset_finance_deduction_setups')) {
                $coopAsset = DB::table('coop_asset_finance_deduction_setups')
                    ->where('staffId', $staffId)
                    ->where('is_active', 1)
                    ->where('balance_remaining', '>', 0)
                    ->where('start_month', '<=', $currentMonthStr)
                    ->where(function ($q) use ($currentMonthStr) {
                        $q->whereNull('end_month')
                          ->orWhere('end_month', '=', '')
                          ->orWhere('end_month', '>=', $currentMonthStr);
                    })
                    ->orderBy('id', 'desc')->first();
                if ($coopAsset) {
                    $coopAssetDeduct = min((float)$coopAsset->monthly_deduction, (float)$coopAsset->balance_remaining);
                }
            }

            // Surcharge Setup
            $surchargeDeduct = 0.00;
            if (\Illuminate\Support\Facades\Schema::hasTable('surcharge_deduction_setups')) {
                $surcharge = DB::table('surcharge_deduction_setups')
                    ->where('staffId', $staffId)
                    ->where('is_active', 1)
                    ->where('balance_remaining', '>', 0)
                    ->where('start_month', '<=', $currentMonthStr)
                    ->where(function ($q) use ($currentMonthStr) {
                        $q->whereNull('end_month')
                          ->orWhere('end_month', '=', '')
                          ->orWhere('end_month', '>=', $currentMonthStr);
                    })
                    ->orderBy('id', 'desc')->first();
                if ($surcharge) {
                    $surchargeDeduct = min((float)$surcharge->monthly_deduction, (float)$surcharge->balance_remaining);
                }
            }

            // Absence Penalty Setup
            $absencePenaltyDeduct = 0.00;
            if (\Illuminate\Support\Facades\Schema::hasTable('absence_penalty_deduction_setups')) {
                $absencePenalty = DB::table('absence_penalty_deduction_setups')
                    ->where('staffId', $staffId)
                    ->where('is_active', 1)
                    ->where('start_month', '<=', $currentMonthStr)
                    ->where(function ($q) use ($currentMonthStr) {
                        $q->whereNull('end_month')
                          ->orWhere('end_month', '=', '')
                          ->orWhere('end_month', '>=', $currentMonthStr);
                    })
                    ->orderBy('id', 'desc')->first();
                if ($absencePenalty) {
                    $absencePenaltyDeduct = min((float)$absencePenalty->monthly_deduction, (float)($absencePenalty->balance_remaining > 0 ? $absencePenalty->balance_remaining : $absencePenalty->total_amount));
                }
            }

            // Loan Deduction
            $loanDeduct = 0.00;
            if (\Illuminate\Support\Facades\Schema::hasTable('loan_deduction_setups')) {
                $loanSetup = DB::table('loan_deduction_setups')
                    ->where('staffId', $staffId)
                    ->where('is_active', 1)
                    ->where('balance_remaining', '>', 0)
                    ->where('start_month', '<=', $currentMonthStr)
                    ->where('end_month', '>=', $currentMonthStr)
                    ->orderBy('id', 'desc')->first();
                if ($loanSetup) {
                    $loanDeduct = min((float)$loanSetup->monthly_deduction, (float)$loanSetup->balance_remaining);
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

            // Other Deductions Setup
            $otherDeduct = 0.00;
            if (\Illuminate\Support\Facades\Schema::hasTable('other_deduction_setups')) {
                $otherSetup = DB::table('other_deduction_setups')
                    ->where('staffId', $staffId)
                    ->where('is_active', 1)
                    ->where('balance_remaining', '>', 0)
                    ->where('start_month', '<=', $currentMonthStr)
                    ->where(function ($q) use ($currentMonthStr) {
                        $q->whereNull('end_month')
                          ->orWhere('end_month', '=', '')
                          ->orWhere('end_month', '>=', $currentMonthStr);
                    })
                    ->orderBy('id', 'desc')->first();
                if ($otherSetup) {
                    $otherDeduct = min((float)$otherSetup->monthly_deduction, (float)$otherSetup->balance_remaining);
                }
            }

            $totalDeductions = $payeTax + $pension + $retention + $iouSum + $medicalLoanDeduct +
                               $coopLoanDeduct + $coopSavingsDeduct + $coopAssetDeduct + $surchargeDeduct +
                               $absencePenaltyDeduct + $loanDeduct + $otherDeduct + $loaDeduction;

            $projectedNetPay = round($grossPay - $totalDeductions, 2);

            if ($projectedNetPay <= 0.00) {
                return [
                    'valid' => false,
                    'month_name' => $monthName,
                    'projected_net_pay' => $projectedNetPay,
                    'loa_days' => $proposedLoaDaysInMonth,
                    'message' => "Cannot apply for Leave of Absence: This employee available net pay for {$monthName} can not be negative."
                ];
            }

            $currentMonth->addMonth();
        }

        return ['valid' => true];
    }
}
