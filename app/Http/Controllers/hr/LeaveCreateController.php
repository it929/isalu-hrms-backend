<?php

namespace App\Http\Controllers\hr;

use Illuminate\Http\Request;
use App\Http\Requests;
use DB;
use Auth;
use session;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class LeaveCreateController extends Controller
{
    public function allLeave()
    {
        return $data['getleave'] = DB::table('tblleave_type')->orderBy('id', 'Desc')->get();
    }

    public function index()
    {

        $data['getleave'] = $this->allLeave();
        //$data['getDepartment'] = DB::table('tblleave_type')->get();
        //dd($data);
        return view('hr.Leave/leavetype')->with($data);
    }
    public function ApplyLeave()
    {

        $data['getleave'] = $this->allLeave();
        // $data['getleaveRecord'] = DB::table('leave_record')->orderBy('id', 'Desc')->get();
        //$data['getDepartment'] = DB::table('tblleave_type')->get();
        $data['getEnployee'] = DB::table('tblper')->get();
        $data['getleaveRecord'] = DB::table('leave_record')
            ->join('tblper', 'tblper.ID', '=', 'leave_record.staffId')
            ->join('tblleave_type', 'tblleave_type.id', '=', 'leave_record.leave_type_id')
            ->join('tbldepartment', 'tbldepartment.id', '=', 'tblper.departmentID') // if this is your dept field
            ->select(
                'leave_record.*',
                'tblper.surname',
                'tblper.first_name',
                'tblper.othernames',
                'tbldepartment.department',
                'tblleave_type.leaveType'
            )
            ->orderBy('leave_record.id', 'DESC')
            ->get();
        //dd($data);
        return view('hr.Leave/applyLeave')->with($data);
    }




    public function saveApplyLeaveOLD(Request $request)
    {
        // Validate form inputs
        $request->validate([
            'leave_type'     => 'required|integer',
            'employee_id'    => 'required|integer',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'leave_reason'   => 'required|string',
        ]);

        // Insert into database table
        DB::table('leave_record')->insert([
            'leave_type_id'     => $request->leave_type,
            'staffId'           => $request->employee_id,
            'start_date'        => $request->start_date,
            'end_date'          => $request->end_date,
            'reason_of_leave'   => $request->leave_reason,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return redirect()->back()->with('message', 'Leave application submitted successfully.');
    }

    public function saveApplyLeave_06_05_2026(Request $request)
    {
        // Validate inputs
        $request->validate([
            'leave_type'     => 'required|integer',
            'employee_id'    => 'required|integer',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'leave_reason'   => 'required|string',
        ]);

        $leaveType = DB::table('tblleave_type')->where('id', $request->leave_type)->get();

        $employeeId = $request->employee_id;

        //check gender of employeee

        $employeeGender = DB::table('tblper')->where('ID', $employeeId)->get();

        if ($employeeGender->gender = 'Male' && $leaveType->id = 3) {
            return redirect()->back()
                ->with('error', "You are not eligible for this leave, is for female staff");
        }

        // Total leave allowed
        $totalAllowed = $leaveType->days;

        // Calculate days requested
        $start = Carbon::parse($request->start_date);
        $end   = Carbon::parse($request->end_date);

        $requestedDays = $start->diffInDays($end) + 1;

        // Check days already used
        $usedDays = DB::table('leave_record')
            ->where('staffId', $employeeId)
            ->where('leave_type_id', $leaveType->id)
            ->sum(DB::raw("DATEDIFF(end_date, start_date) + 1"));

        // Validation: ensure total does not exceed 14
        if (($usedDays + $requestedDays) > $totalAllowed) {

            $remaining = $totalAllowed - $usedDays;

            return redirect()->back()
                ->with('error', "You have only $remaining day(s) remaining. You cannot take $requestedDays days.");
        }

        // Store leave
        DB::table('leave_record')->insert([
            'leave_type_id'     => $request->leave_type,
            'staffId'           => $employeeId,
            'start_date'        => $request->start_date,
            'end_date'          => $request->end_date,
            'reason_of_leave'   => $request->leave_reason,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return redirect()->back()->with('message', 'Leave application submitted successfully.');
    }

    public function saveApplyLeave(Request $request)
    {
        // Validate inputs
        $request->validate([
            'leave_type'     => 'required|integer',
            'employee_id'    => 'required|integer',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'leave_reason'   => 'required|string',
        ]);

        // Get leave type
        $leaveType = DB::table('tblleave_type')
            ->where('id', $request->leave_type)
            ->first();

        if (!$leaveType) {
            return back()->with('error', 'Invalid leave type selected.');
        }

        // Get employee
        $employee = DB::table('tblper')
            ->where('ID', $request->employee_id)
            ->first();

        if (!$employee) {
            return back()->with('error', 'Employee not found.');
        }

        // Gender restriction - if leave type 3 = maternity leave
        if ($employee->gender == 'Male' && $leaveType->id == 3) {
            return back()->with('error', 'You are not eligible for maternity leave.');
        }

        // Total leave allowed
        $totalAllowed = $leaveType->days;

        // Calculate requested days
        $start = Carbon::parse($request->start_date);
        $end   = Carbon::parse($request->end_date);
        $requestedDays = $start->diffInDays($end) + 1;

        // Days already used
        $usedDays = DB::table('leave_record')
            ->where('staffId', $request->employee_id)
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 2)
            ->sum(DB::raw("DATEDIFF(end_date, start_date) + 1"));

        // Check remaining days
        if (($usedDays + $requestedDays) > $totalAllowed) {

            $remaining = $totalAllowed - $usedDays;

            return back()->with(
                'error',
                "You have only $remaining remaining day(s). You cannot request $requestedDays days."
            );
        }

        // Save leave application
        DB::table('leave_record')->insert([
            'leave_type_id'     => $leaveType->id,
            'staffId'           => $request->employee_id,
            'start_date'        => $request->start_date,
            'end_date'          => $request->end_date,
            'reason_of_leave'   => $request->leave_reason,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return back()->with('message', 'Leave application submitted successfully.');
    }

    public function calculateEndDate_06_05_2026(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'start_date'  => 'required|date',
        ]);

        $employee = DB::table('tblper')->where('ID', $request->employee_id)->first();

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        // Get leave type
        $leaveType = DB::table('tblleave_type')
            ->where('id', $request->leave_type)
            ->first();

        // Days already used
        $usedDays = DB::table('leave_record')
            ->where('staffId', $request->employee_id)
            ->where('leave_type_id', $leaveType->id)
            ->sum(DB::raw("DATEDIFF(end_date, start_date) + 1"));

        // $startDate = Carbon\Carbon::parse($request->start_date);
        $startDate = Carbon::parse($request->start_date);
        $leaveDays = 14;

        // If office shift is 1 → exclude weekends
        if ($employee->office_shift == 1) {
            $current = $startDate->copy();
            $added = 0;

            while ($added < $leaveDays) {
                if (!in_array($current->dayOfWeek, [0, 6])) {
                    $added++;
                }
                if ($added < $leaveDays) {
                    $current->addDay();
                }
            }

            $endDate = $current->format('Y-m-d');
        } else {
            // office_shift = 2 → include weekends
            $endDate = $startDate->copy()->addDays($leaveDays - 1)->format('Y-m-d');
        }

        return response()->json(['end_date' => $endDate]);
    }

    public function calculateEndDateOLD(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'leave_type'  => 'required|integer',
            'start_date'  => 'required|date',
        ]);

        // Get employee
        $employee = DB::table('tblper')->where('ID', $request->employee_id)->first();
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        // Get leave type
        $leaveType = DB::table('tblleave_type')->where('id', $request->leave_type)->first();
        if (!$leaveType) {
            return response()->json(['error' => 'Leave type not found'], 404);
        }

        // Total allowed leave days
        $totalAllowed = $leaveType->days;

        // Days already used by employee for this leave type
        $usedDays = DB::table('leave_record')
            ->where('staffId', $request->employee_id)
            ->where('leave_type_id', $leaveType->id)
            ->sum(DB::raw("DATEDIFF(end_date, start_date) + 1"));

        // Remaining leave days
        $remainingDays = $totalAllowed - $usedDays;

        if ($remainingDays <= 0) {
            return response()->json(['end_date' => null, 'message' => 'No remaining leave days']);
        }

        // Start date
        $startDate = Carbon::parse($request->start_date);

        // Admin staff = exclude weekends (office_shift = 1)
        // Shift staff = include weekends (office_shift = 2)
        if ($employee->office_shift == 1) {
            // Admin staff → exclude weekends
            $current = $startDate->copy();
            $added = 0;

            while ($added < $remainingDays) {
                if (!in_array($current->dayOfWeek, [0, 6])) { // 0 = Sunday, 6 = Saturday
                    $added++;
                }
                if ($added < $remainingDays) {
                    $current->addDay();
                }
            }

            $endDate = $current->format('Y-m-d');
        } else {
            // Shift staff → count weekends
            $endDate = $startDate->copy()->addDays($remainingDays - 1)->format('Y-m-d');
        }

        return response()->json(['end_date' => $endDate]);
    }

    public function calculateEndDate(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'leave_type'  => 'required|integer',
            'start_date'  => 'required|date',
        ]);

        $employee = DB::table('tblper')->where('ID', $request->employee_id)->first();
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $leaveType = DB::table('tblleave_type')->where('id', $request->leave_type)->first();
        if (!$leaveType) {
            return response()->json(['error' => 'Leave type not found'], 404);
        }

        $totalAllowed = $leaveType->days;

        $usedDays = DB::table('leave_record')
            ->where('staffId', $request->employee_id)
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 2)
            ->sum(DB::raw("DATEDIFF(end_date, start_date) + 1"));

        $remainingDays = $totalAllowed - $usedDays;

        if ($remainingDays <= 0) {
            return response()->json([
                'end_date' => null,
                'remaining_days' => 0
            ]);
        }

        $startDate = Carbon::parse($request->start_date);

        if ($employee->office_shift == 1) {
            // EXCLUDE WEEKENDS
            $current = $startDate->copy();
            $added = 0;

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
            // INCLUDE WEEKENDS
            $endDate = $startDate->copy()->addDays($remainingDays - 1)->format('Y-m-d');
        }

        return response()->json([
            'end_date'       => $endDate,
            'remaining_days' => $remainingDays
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'leave' => 'required', //:tblleave_type, leaveType'
            'days' => 'required',
        ]);
        $leave = $request->input('leave');
        $days = $request->input('days');

        $save = DB::table('tblleave_type')->insert([
            'leaveType' => $leave,
            'days' => $days
        ]);

        return redirect('Leave/leavetype')->with('message', 'New leave type was added successfully.');
    }


    public function edit($id)
    {
        $data['getleave'] = DB::table('tblleave_type')->get();
        $data['editLeave'] = DB::table('tblleave_type')->where('id', $id)->first();
        $data['getLeaveID'] = $id;

        return view('hr.Leave/leavetype', $data);
    }


    public function update(Request $request)
    {

        $this->validate($request, [
            'leaveId' => 'required|numeric',
            'leave' => 'required',


        ]);

        $data['leave'] = $request->input('leave');
        $data['days'] = $request->input('days');
        $leaveID     = $request->get('leaveId');

        $update = DB::table('tblleave_type')->where('id', $leaveID)->update([
            'leaveType' => $data['leave'],
            'days' => $data['days'],
        ]);

        return redirect('Leave/leavetype')->with('message', 'leave type was successfully Updated.');
    }

    public function updateLeaveType(Request $request)
    {
        $this->validate($request, [
            'leaveId' => 'required|numeric',
            'leave' => 'required'
        ]);
    }

    public function delete($id)
    {
        if (DB::table('tblleave_type')->where('id', $id)->first()) {
            $success = DB::table('tblleave_type')->where('id', $id)->delete();
            return redirect('Leave/leavetype')->with('message', 'Deleted successfully');
        } else {
            return redirect('Leave/leavetype')->with('error', 'Sorry, we cannot delete this record. Try again');
        }
        return redirect('Leave/leavetype')->with('error', 'Record not found!');
    }
}
