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
        
        if ($ctx['isSuperAdmin'] || $ctx['isAdminStaff']) {
            $employees  = DB::table('tblper')->select('ID', 'surname', 'first_name', 'othernames')->get();
        } else {
            $employees  = collect();
        }

        $employee = $ctx['employee'];

        return response()->json([
            'status'        => 'success',
            'leaveTypes'    => $leaveTypes,
            'employees'     => $employees,
            'isSuperAdmin'  => $ctx['isSuperAdmin'],
            'isHod'         => $ctx['isHod'],
            'isAdminStaff'  => $ctx['isAdminStaff'],
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
                'tbldepartment.department',
                'tblleave_type.leaveType'
            )
            ->orderBy('leave_record.id', 'DESC');

        $employee = $ctx['employee'];

        if ($ctx['isSuperAdmin'] || $ctx['isAdminStaff']) {
            $records = $baseQuery->get();
        } elseif ($employee && $employee->is_hod == 1) {
            $records = $baseQuery
                ->where('tblper.departmentID', $employee->departmentID)
                ->get();
        } elseif ($employee) {
            $records = $baseQuery
                ->where('leave_record.staffId', $employee->ID)
                ->get();
        } else {
            $records = collect();
        }

        // Augment each record with computed duration & formatted date
        $records = $records->map(function ($r) {
            $start = Carbon::parse($r->start_date);
            $end   = Carbon::parse($r->end_date);
            $r->duration_days = $start->diffInDays($end) + 1;
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

        // Gender restriction: maternity leave (id == 3) strictly for female staff
        if ($leaveType->id == 3 && strtolower($employee->gender) !== 'female') {
            return response()->json(['status' => 'error', 'message' => 'You are not eligible for maternity leave.']);
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
    public function hodApprove($id)
    {
        DB::table('leave_record')->where('id', $id)->update(['status' => 1]);
        return response()->json(['status' => 'success', 'message' => 'Leave approved by HOD.']);
    }

    /** GET /api/nextjs/hr/apply-leave/hod-reject/{id} */
    public function hodReject($id)
    {
        DB::table('leave_record')->where('id', $id)->update(['status' => 3]);
        return response()->json(['status' => 'success', 'message' => 'Leave rejected by HOD.']);
    }

    /** GET /api/nextjs/hr/apply-leave/admin-approve/{id} */
    public function adminApprove($id)
    {
        DB::table('leave_record')->where('id', $id)->update(['status' => 2]);
        return response()->json(['status' => 'success', 'message' => 'Leave fully approved by HR.']);
    }

    /** GET /api/nextjs/hr/apply-leave/admin-reject/{id} */
    public function adminReject($id)
    {
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

        // Gender restriction: maternity leave (id == 3) strictly for female staff
        if ($leaveType->id == 3 && strtolower($employee->gender) !== 'female') {
            return response()->json(['status' => 'error', 'message' => 'You are not eligible for maternity leave.']);
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

        if ($ctx['isSuperAdmin'] || $ctx['isAdminStaff']) {
            $employees  = DB::table('tblper')->select('ID', 'surname', 'first_name', 'othernames')->get();
        } else {
            $employees  = collect();
        }

        $employee = $ctx['employee'];

        return response()->json([
            'status'        => 'success',
            'employees'     => $employees,
            'isSuperAdmin'  => $ctx['isSuperAdmin'],
            'isHod'         => $ctx['isHod'],
            'isAdminStaff'  => $ctx['isAdminStaff'],
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
                'tbldepartment.department'
            )
            ->orderBy('leave_of_absent.id', 'DESC');

        $employee = $ctx['employee'];

        if ($ctx['isSuperAdmin'] || $ctx['isAdminStaff']) {
            $records = $baseQuery->get();
        } elseif ($employee && $employee->is_hod == 1) {
            $records = $baseQuery
                ->where('tblper.departmentID', $employee->departmentID)
                ->get();
        } elseif ($employee) {
            $records = $baseQuery
                ->where('leave_of_absent.staffId', $employee->ID)
                ->get();
        } else {
            $records = collect();
        }

        $records = $records->map(function ($r) {
            $start = Carbon::parse($r->start_date);
            $end   = Carbon::parse($r->end_date);
            $r->duration_days = $start->diffInDays($end) + 1;
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
    public function hodApproveLoa($id)
    {
        DB::table('leave_of_absent')->where('id', $id)->update(['status' => 1]);
        return response()->json(['status' => 'success', 'message' => 'Leave of absence approved by HOD.']);
    }

    /** GET /api/nextjs/hr/apply-loa/hod-reject/{id} */
    public function hodRejectLoa($id)
    {
        DB::table('leave_of_absent')->where('id', $id)->update(['status' => 3]);
        return response()->json(['status' => 'success', 'message' => 'Leave of absence rejected by HOD.']);
    }

    /** GET /api/nextjs/hr/apply-loa/admin-approve/{id} */
    public function adminApproveLoa($id)
    {
        DB::table('leave_of_absent')->where('id', $id)->update(['status' => 2]);
        return response()->json(['status' => 'success', 'message' => 'Leave of absence fully approved by HR.']);
    }

    /** GET /api/nextjs/hr/apply-loa/admin-reject/{id} */
    public function adminRejectLoa($id)
    {
        DB::table('leave_of_absent')->where('id', $id)->update(['status' => 4]);
        return response()->json(['status' => 'success', 'message' => 'Leave of absence rejected by Admin.']);
    }
}
