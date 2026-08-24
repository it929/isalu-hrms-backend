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
        
        if ($ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || $ctx['isAuditStaff']) {
            $employees  = DB::table('tblper')->select('ID', 'surname', 'first_name', 'othernames', 'office_shift', 'gender')->get()->map(function($emp) {
                $emp->has_uploaded_education = DB::table('tbleducations')
                    ->where('staffid', $emp->ID)
                    ->whereNotNull('document')
                    ->where('document', '!=', '')
                    ->exists();
                return $emp;
            });
        } else {
            $employees  = collect();
        }

        $employee = $ctx['employee'];
        if ($employee) {
            $employee->has_uploaded_education = DB::table('tbleducations')
                ->where('staffid', $employee->ID)
                ->whereNotNull('document')
                ->where('document', '!=', '')
                ->exists();
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
            ->select(
                'leave_record.*',
                'tblper.surname',
                'tblper.first_name',
                'tblper.othernames',
                'tblper.office_shift',
                'tbldepartment.department',
                'tblleave_type.leaveType'
            )
            ->orderBy('leave_record.id', 'DESC');

        $employee = $ctx['employee'];

        if ($ctx['isSuperAdmin'] || $ctx['isAuditStaff']) {
            $records = $baseQuery->get();
        } else {
            $baseQuery->where(function ($query) use ($ctx, $employee) {
                $hasCondition = false;

                // 1. Own records
                if ($employee) {
                    $query->where('leave_record.staffId', $employee->ID);
                    $hasCondition = true;
                }

                // 2. HR Head sees HOD-approved (1) or finalized (2, 4) records
                if ($ctx['isAdminStaff']) {
                    if ($hasCondition) {
                        $query->orWhereIn('leave_record.status', [1, 2, 4]);
                    } else {
                        $query->whereIn('leave_record.status', [1, 2, 4]);
                    }
                    $hasCondition = true;
                }

                // 3. HOD sees records of staff in their department
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
                    $query->where('leave_record.id', 0);
                }
            });
            $records = $baseQuery->get();
        }

        // Augment each record with computed duration & formatted date
        $records = $records->map(function ($r) {
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
            
            $r->date_applied  = Carbon::parse($r->created_at)->format('d M, Y');
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
            return response()->json(['status' => 'error', 'message' => 'Employee not found.']);
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
            return response()->json(['status' => 'error', 'message' => 'Employee not found.']);
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

        if ($isExecutive) {
            $employees  = DB::table('tblper')->select('ID', 'surname', 'first_name', 'othernames')->get()->map(function($emp) {
                $emp->has_uploaded_education = DB::table('tbleducations')
                    ->where('staffid', $emp->ID)
                    ->whereNotNull('document')
                    ->where('document', '!=', '')
                    ->exists();
                return $emp;
            });
        } else {
            $employees  = collect();
        }

        $employee = $ctx['employee'];
        if ($employee) {
            $employee->has_uploaded_education = DB::table('tbleducations')
                ->where('staffid', $employee->ID)
                ->whereNotNull('document')
                ->where('document', '!=', '')
                ->exists();
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

        $records = $baseQuery->get();

        $records = $records->map(function ($r) {
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
            
            $r->date_applied  = Carbon::parse($r->created_at)->format('d M, Y');
            return $r;
        });

        return response()->json([
            'status'     => 'success',
            'loaRecords' => $records,
        ]);
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
            ]);
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
            return response()->json(['status' => 'error', 'message' => 'This leave of absence application has already been processed and cannot be edited.']);
        }

        $employee = DB::table('tblper')->where('ID', $request->employee_id)->first();
        if (!$employee) {
            return response()->json(['status' => 'error', 'message' => 'Employee not found.']);
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

        if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
            $record = DB::table('leave_of_absent')->where('id', $id)->first();
            if ($record) {
                $employee = DB::table('tblper')->where('ID', $record->staffId)->first();
                $activeDeptId = (isset($ctx['isDelegatedHod']) && $ctx['isDelegatedHod']) 
                    ? $ctx['delegated_department_id'] 
                    : ($ctx['employee'] ? $ctx['employee']->departmentID : null);

                if (!$employee || $employee->departmentID != $activeDeptId) {
                    return response()->json(['status' => 'error', 'message' => 'Access denied: staff belongs to a different department.'], 403);
                }
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

        if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
            $record = DB::table('leave_of_absent')->where('id', $id)->first();
            if ($record) {
                $employee = DB::table('tblper')->where('ID', $record->staffId)->first();
                $activeDeptId = (isset($ctx['isDelegatedHod']) && $ctx['isDelegatedHod']) 
                    ? $ctx['delegated_department_id'] 
                    : ($ctx['employee'] ? $ctx['employee']->departmentID : null);

                if (!$employee || $employee->departmentID != $activeDeptId) {
                    return response()->json(['status' => 'error', 'message' => 'Access denied: staff belongs to a different department.'], 403);
                }
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
        DB::table('leave_of_absent')->where('id', $id)->update(['status' => 4]);
        return response()->json(['status' => 'success', 'message' => 'Leave of absence rejected by Admin.']);
    }
}
