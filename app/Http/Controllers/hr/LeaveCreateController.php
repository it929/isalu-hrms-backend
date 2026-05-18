<?php

namespace App\Http\Controllers\hr;

use Illuminate\Http\Request;
use App\Http\Requests;
use DB;
use Auth;
use Session;
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


    //apply leave
    public function ApplyLeave()
    {
        $user = auth()->user(); // Logged-in user

        // Super Admin role name (adjust to your system)
        // $isSuperAdmin = ($user->id == 6);
        $isSuperAdmin = DB::table('assign_user_role')
            ->where('userID', $user->id)
            ->where('roleID', 1) // 1 = Super Admin
            ->exists();

        // Check if user exists in tblper
        $employee = DB::table('tblper')->where('UserID', $user->id)->first();

        // HR DEPARTMENT ID
        // $HR_DEPT_ID = 80;

        //Admin Staff

        $adminStaff = DB::table('assign_user_role')
            ->where('userID', $user->id)
            ->where('roleID', 48)
            ->exists();


        // Leave types
        $data['getleave'] = $this->allLeave();

        // All employees
        $data['getEnployee'] = DB::table('tblper')->get();

        // Base leave query
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

        // ================================
        // SUPER ADMIN (see all leave)
        // ================================
        if ($isSuperAdmin) {
            $getleaveRecord = $baseQuery->get();
        }

        // ==========================================
        // HR – sees ALL leave (user exists in tblper)
        // ==========================================
        // elseif ($employee && $employee->departmentID == $HR_DEPT_ID) {
        //     $getleaveRecord = $baseQuery->get();
        // }
        elseif ($employee && $adminStaff) {
            $getleaveRecord = $baseQuery->get();
        }

        // ======================
        // HOD – sees dept leave
        // ======================
        elseif ($employee && $employee->is_hod == 1) {
            $getleaveRecord = $baseQuery
                ->where('tblper.departmentID', $employee->departmentID)
                ->get();
        }

        // ======================
        // STAFF – sees own leave
        // ======================
        elseif ($employee) {
            $getleaveRecord = $baseQuery
                ->where('leave_record.staffId', $employee->ID)
                ->get();
        }

        // ================================
        // User is NOT in tblper (safety)
        // ================================
        else {
            $getleaveRecord = collect(); // empty result
        }

        $data['isSuperAdmin'] = $isSuperAdmin;
        $data['isHod'] = $employee && $employee->is_hod == 1;
        $data['isAdminStaff'] = $adminStaff;

        $data['getleaveRecord'] = $getleaveRecord;

        return view('hr.Leave/applyLeave')->with($data);
    }

    //HOD Approved
    public function hodApprove($id)
    {
        DB::table('leave_record')->where('id', $id)->update([
            'status' => 1, // HOD approved
        ]);

        return back()->with('success', 'Leave approved by HOD');
    }

    // Admin Approve
    public function adminApprove($id)
    {
        DB::table('leave_record')->where('id', $id)->update([
            'status' => 2, // Admin approved
        ]);

        return back()->with('success', 'Leave fully approved by HR');
    }

    // HOD Reject
    public function hodReject($id)
    {
        DB::table('leave_record')->where('id', $id)->update([
            'status' => 3, // Hod Rejected
        ]);

        return back()->with('error', 'Leave rejected by HOD');
    }
    // Admin Reject
    public function adminReject($id)
    {
        DB::table('leave_record')->where('id', $id)->update([
            'status' => 4, // Admin Rejected
        ]);

        return back()->with('error', 'Leave rejected by Admin');
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


    //LOA management
    public function ApplyLoa()
    {
        $user = auth()->user(); // Logged-in user

        // Super Admin role name (adjust to your system)
        // $isSuperAdmin = ($user->id == 6);
        $isSuperAdmin = DB::table('assign_user_role')
            ->where('userID', $user->id)
            ->where('roleID', 1) // 1 = Super Admin
            ->exists();

        // Check if user exists in tblper
        $employee = DB::table('tblper')->where('UserID', $user->id)->first();

        // dd($employee);

        // HR DEPARTMENT ID
        // $HR_DEPT_ID = 80;

        //Admin Staff

        $adminStaff = DB::table('assign_user_role')
            ->where('userID', $user->id)
            ->where('roleID', 48)
            ->exists();


        // Leave types
        $data['getleave'] = $this->allLeave();

        // All employees
        $data['getEnployee'] = DB::table('tblper')->get();

        // Base leave query
        $baseQuery = DB::table('leave_of_absent')
            ->join('tblper', 'tblper.ID', '=', 'leave_of_absent.staffId')
            // ->join('tblleave_type', 'tblleave_type.id', '=', 'leave_record.leave_type_id')
            ->join('tbldepartment', 'tbldepartment.id', '=', 'tblper.departmentID')
            ->select(
                'leave_of_absent.*',
                'tblper.surname',
                'tblper.first_name',
                'tblper.othernames',
                'tbldepartment.department'
                // 'tblleave_type.leaveType'
            )
            ->orderBy('leave_of_absent.id', 'DESC');

        // ================================
        // SUPER ADMIN (see all leave)
        // ================================
        if ($isSuperAdmin) {
            $getleaveRecord = $baseQuery->get();
        }

        // ==========================================
        // HR – sees ALL leave (user exists in tblper)
        // ==========================================
        // elseif ($employee && $employee->departmentID == $HR_DEPT_ID) {
        //     $getleaveRecord = $baseQuery->get();
        // }
        elseif ($employee && $adminStaff) {
            $getleaveRecord = $baseQuery->get();
        }

        // ======================
        // HOD – sees dept leave
        // ======================
        elseif ($employee && $employee->is_hod == 1) {
            $getleaveRecord = $baseQuery
                ->where('tblper.departmentID', $employee->departmentID)
                ->get();
        }

        // ======================
        // STAFF – sees own leave
        // ======================
        elseif ($employee) {
            $getleaveRecord = $baseQuery
                ->where('leave_of_absent.staffId', $employee->ID)
                ->get();
        }

        // ================================
        // User is NOT in tblper (safety)
        // ================================
        else {
            $getleaveRecord = collect(); // empty result
        }

        $data['isSuperAdmin'] = $isSuperAdmin;
        $data['isHod'] = $employee && $employee->is_hod == 1;
        $data['isAdminStaff'] = $adminStaff;
        $data['employee'] = $employee;

        $data['getleaveRecord'] = $getleaveRecord;

        // dd($data['getleaveRecord']);

        return view('hr.Leave/applyLoa')->with($data);
    }

    public function saveApplyLoa(Request $request)
    {
        // Validate inputs
        $request->validate([
            // 'leave_type'     => 'required|integer',
            'employee_id'    => 'required|integer',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'leave_reason'   => 'required|string',
        ]);

        // Get leave type
        // $leaveType = DB::table('tblleave_type')
        //     ->where('id', $request->leave_type)
        //     ->first();

        // if (!$leaveType) {
        //     return back()->with('error', 'Invalid leave type selected.');
        // }

        // Get employee
        $employee = DB::table('tblper')
            ->where('ID', $request->employee_id)
            ->first();

        if (!$employee) {
            return back()->with('error', 'Employee not found.');
        }

        // Gender restriction - if leave type 3 = maternity leave
        // if ($employee->gender == 'Male' && $leaveType->id == 3) {
        //     return back()->with('error', 'You are not eligible for maternity leave.');
        // }

        // Total leave allowed
        // $totalAllowed = $leaveType->days;

        // Calculate requested days
        $start = Carbon::parse($request->start_date);
        $end   = Carbon::parse($request->end_date);
        // $requestedDays = $start->diffInDays($end) + 1;

        // Days already used
        // $usedDays = DB::table('leave_record')
        //     ->where('staffId', $request->employee_id)
        //     ->where('leave_type_id', $leaveType->id)
        //     ->where('status', 2)
        //     ->sum(DB::raw("DATEDIFF(end_date, start_date) + 1"));

        // Check remaining days
        // if (($usedDays + $requestedDays) > $totalAllowed) {

        //     $remaining = $totalAllowed - $usedDays;

        //     return back()->with(
        //         'error',
        //         "You have only $remaining remaining day(s). You cannot request $requestedDays days."
        //     );
        // }

        // Save leave application
        DB::table('leave_of_absent')->insert([
            // 'leave_type_id'     => $leaveType->id,
            'staffId'           => $request->employee_id,
            'start_date'        => $request->start_date,
            'end_date'          => $request->end_date,
            'reason_of_leave'   => $request->leave_reason,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return back()->with('message', 'Leave application submitted successfully.');
    }

    public function loaList()
    {
        $user = auth()->user();

        // Check user roles again
        $isSuperAdmin = DB::table('assign_user_role')
            ->where('userID', $user->id)
            ->where('roleID', 1)
            ->exists();

        $employee = DB::table('tblper')->where('UserID', $user->id)->first();

        $adminStaff = DB::table('assign_user_role')
            ->where('userID', $user->id)
            ->where('roleID', 48)
            ->exists();

        // Base query
        $baseQuery = DB::table('leave_of_absent')
            ->join('tblper', 'tblper.ID', '=', 'leave_of_absent.staffId')
            ->join('tbldepartment', 'tbldepartment.id', '=', 'tblper.departmentID')
            ->select(
                'leave_of_absent.*',
                'tblper.surname',
                'tblper.first_name',
                'tblper.othernames',
                'tbldepartment.department'
            )
            ->orderBy('leave_of_absent.id', 'DESC');

        // SUPER ADMIN
        if ($isSuperAdmin) {
            $getleaveRecord = $baseQuery->get();
        }
        // ADMIN STAFF
        elseif ($employee && $adminStaff) {
            $getleaveRecord = $baseQuery->get();
        }
        // HOD
        elseif ($employee && $employee->is_hod == 1) {
            $getleaveRecord = $baseQuery
                ->where('tblper.departmentID', $employee->departmentID)
                ->get();
        }
        // STAFF (own leave)
        elseif ($employee) {
            $getleaveRecord = $baseQuery
                ->where('leave_of_absent.staffId', $employee->ID)
                ->get();
        }
        // No employee
        else {
            $getleaveRecord = collect();
        }

        $data['isSuperAdmin'] = $isSuperAdmin;
        $data['isHod'] = $employee && $employee->is_hod == 1;
        $data['isAdminStaff'] = $adminStaff;

        $data['getleaveRecord'] = $getleaveRecord;

        return view('hr.Leave._loa_rows')->with($data);

        // return view('hr.Leave._loa_rows', compact('getleaveRecord'));
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
