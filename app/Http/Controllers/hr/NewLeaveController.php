<?php

namespace App\Http\Controllers\hr;

use Illuminate\Http\Request;
//use Auth;
use App\Http\Requests;
use DB;
use Auth;
use Session;
use Response;

class NewLeaveController extends functionController
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->middleware('auth');
        $this->username = Session::get('userName');
            return $next($request);
        });
    }

    public function getWorkingDays($startDate, $endDate)
    {
        // do strtotime calculations just once
        $endDate = strtotime($endDate);
        $startDate = strtotime($startDate);


        //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
        //We add one to inlude both dates in the interval.
        $days = ($endDate - $startDate) / 86400 + 1;

        $no_full_weeks = floor($days / 7);
        $no_remaining_days = fmod($days, 7);

        //It will return 1 if it's Monday,.. ,7 for Sunday
        $the_first_day_of_week = date("N", $startDate);
        $the_last_day_of_week = date("N", $endDate);

        //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
        //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
        if ($the_first_day_of_week <= $the_last_day_of_week) {
            if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
            if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
        } else {
            // (edit by Tokes to fix an edge case where the start day was a Sunday
            // and the end day was NOT a Saturday)

            // the day of the week for start is later than the day of the week for end
            if ($the_first_day_of_week == 7) {
                // if the start date is a Sunday, then we definitely subtract 1 day
                $no_remaining_days--;

                if ($the_last_day_of_week == 6) {
                    // if the end date is a Saturday, then we subtract another day
                    $no_remaining_days--;
                }
            } else {
                // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
                // so we skip an entire weekend and subtract 2 days
                $no_remaining_days -= 2;
            }
        }

        //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
        //---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
        $workingDays = $no_full_weeks * 5;
        if ($no_remaining_days > 0) {
            $workingDays += $no_remaining_days;
        }


        return (int) $workingDays;
    }

    // This will return 4



    public function getNotification(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $staffid = $this->StaffID($this->username);
        $data['Notification'] = $this->SelfNotification($staffid);
        return view('Leave.notification', $data);
    }
    public function getDefinition(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['dependant'] = "";
        $data['grade'] = "";
        $data['ald'] = "";
        $data['gender'] = "";
        $data['success'] = "";

        $courtid = $this->StaffCourt($this->username);
        $data['LeaveGradetList'] = $this->LeaveGradetList($courtid);

        return view('Leave.gradeleavedefinition', $data);
    }



    public function postDefinition(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['grade'] = trim($request['grade']);
        $data['ald'] = trim($request['ald']);
        $data['success'] = "";
        $delcode = trim($request['delcode']);

        DB::DELETE("DELETE FROM `tblgrade_leave_assignment` WHERE `id`='$delcode'");

        $courtid = Auth::user()->courtID; //$this->StaffCourt($this->username);

        $data['LeaveGradetList'] = $this->LeaveGradetList($courtid);
        $updatedby = $this->username;

        $grade = trim($request['grade']);
        $ald = trim($request['ald']);
        if (isset($_POST['Update'])) {
            $this->validate($request, [
                'grade'          => 'required',
                'ald'          => 'required',
            ]);
            if ($this->ConfirmGradeLeave($courtid, $grade)) {
                DB::UPDATE("UPDATE `tblgrade_leave_assignment` SET `noOfDays`='$ald' WHERE `courtID`='$courtid' and `grade`='$grade'");
            } else {
                DB::INSERT("INSERT INTO `tblgrade_leave_assignment`(`courtID`, `grade`, `noOfDays`) VALUES ('$courtid','$grade','$ald')");
            }

            $data['success'] = "successfully updated";
            $data['grade'] = "";
            $data['ald'] = "";
            $data['LeaveGradetList'] = $this->LeaveGradetList($courtid);
            return view('Leave.gradeleavedefinition', $data);
        }
        return view('Leave.gradeleavedefinition', $data);
    }

    public function LeaveApplication(Request $request)
    {

        $st = DB::table('tblper')->where('UserID', '=', Auth::user()->id)->first();

        if (!empty($st)) {
            $data['roasterCheck'] = DB::table('tblroaster')->where('staffid', '=', $st->ID)->where('is_submitted', '=', 0)->first();
            //dd($data['roasterCheck']);
        }

        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $data['RstaffAction'] = "";
        $data['status'] = "";

        $data['startdate'] = trim($request['startdate']);
        $data['enddate'] = trim($request['enddate']);
        $data['nod'] = trim($request['nod']);
        $data['leaveType'] = trim($request['leaveType']);
        $leaveType = trim($request['leaveType']);
        $data['releavestaff'] = trim($request['releavestaff']);
        $data['purpose'] = trim($request['purpose']);
        $data['address'] = trim($request['address']);

        $courtid = $this->StaffCourt($this->username);
        $staffgrade = $this->StaffGradeLevel($this->username);
        $staffid = $this->StaffID($this->username);
        $period = $this->LeavePeriod();
        $data['period'] = $period;

        $data['LeaveTypeList'] = $this->LeaveType();
        $data['totalAllowable'] = $this->leaveEntitle($courtid, $staffgrade);
        $data['dayRem'] = $this->leaveRemain($data['totalAllowable'], $data['period'], $staffid);
        //$data['daysConsumed'] = $this->leaveRemain($data['totalAllowable'],$data['period'],$staffid);

        $userExistsInTable = DB::table('tblper')->where('UserID', Auth::user()->id)->exists();

        if ($userExistsInTable) {

            $data['staffDetails'] = DB::table('tblper')->where('UserID', Auth::user()->id)->first();
            if (DB::table('tblroaster')->where('staffID', $data['staffDetails']->ID)->exists()) {
                $data['noOfDays'] = DB::table('tblgrade_leave_assignment')->where('grade', $data['staffDetails']->grade)->first();
                $data['daysConsumed'] = DB::table('tblstaff_leave')->where('staffID', $data['staffDetails']->ID)->where('leaveType', 4)->where('period', date('Y'))->sum('noOfDays');
                $data['daysRemaining'] = $data['noOfDays']->noOfDays - $data['daysConsumed'];
                $basicSalary = DB::table('basicsalaryconsolidated')
                    ->where('grade', $data['staffDetails']->grade)
                    ->where('step', $data['staffDetails']->step)
                    ->first();
                //dd($basicSalary);
            } else {
                $data['notPresent'] = "false";
            }
        }

        if ($leaveType == 4) {
            $noOfDays = DB::table('tblgrade_leave_assignment')->where('grade', $data['staffDetails']->grade)->first();
            $numberOfDays = $noOfDays->noOfDays;
        } elseif ($leaveType == 3) {
            $noOfDays = DB::table('tblleave_type')->where('id', $leaveType)->first();
            $numberOfDays = $noOfDays->numberOfDays;
        } elseif ($leaveType == 2) {
            $noOfDays = DB::table('tblleave_type')->where('id', $leaveType)->first();
            $numberOfDays = $noOfDays->numberOfDays;
        } elseif ($leaveType == 1) {
            $noOfDays = DB::table('tblleave_type')->where('id', $leaveType)->first();
            $numberOfDays = $noOfDays->numberOfDays;
        }


        if (isset($_POST['reset'])) {
        }

        if (isset($_POST['Save'])) {

            $data['startdate'] = "";
            $data['enddate'] = "";
            $data['nod1'] = "";
            $data['leaveType'] = "";
            $data['releavestaff'] = "";
            $data['purpose'] = "";
            $data['address'] = "";
            $data['viewid'] = "";
            $start = date('Y-m-d', strtotime(trim($request['startdates'])));
            $end = date('Y-m-d', strtotime(trim($request['enddates'])));
            //dd($end);
            $dateRange = $this->getWorkingDays($start, $end);
            //dd($request['nod1']);

            if ($dateRange != $request['nod1']) {
                return back()->with('error_message', "Number of days selected does not tally with the selected date range");
            }

            if (DB::table('tblstaff_leave')->where('staffID', $data['staffDetails']->ID)->where('period', date('Y'))->where('LeaveType', $request['leaveType'])->exists()) {
                return back()->with('error_message', 'You cannot apply for leave while on leave!');
            }


            $startdate = trim($request['startdates']);
            $enddate = trim($request['enddates']);
            $nod = trim($request['nod1']);
            $leaveType = trim($request['leaveType']);
            $purpose = trim($request['purposes']);
            $address = trim($request['addresss']);

            //dd($startdate);
            $from = \Carbon\Carbon::parse($startdate);
            $to = \Carbon\Carbon::parse($enddate);
            $days = $from->diffInWeekdays($to);
            $actdays = $days + 1;

            $getHolidays = DB::table('tblpublic_holidays')->where('year', date('Y'))->whereBetween('holidays', [$from, $to])->count();
            $nod = $actdays - $getHolidays;

            $daysConsumed = DB::table('tblstaff_leave')->where('staffID', $data['staffDetails']->ID)->where('leaveType', $leaveType)->where('period', date('Y'))->sum('noOfDays');
            $daysRemaining = $numberOfDays - $daysConsumed;
            ///($nod);
            if ($daysRemaining < $nod) {
                return back()->with('error_message', 'You do not enough days remaining!');
            }

            DB::table('tblstaff_leave')->insert([
                'staffID'    => $data['staffDetails']->ID,
                'startDate'  => $startdate,
                'endDate'    => $enddate,
                'noOfDays'   => $nod,
                'leaveType'  => $leaveType,
                'period'     => date('Y'),
                'addressDuringLeave'  => $address,
                'purpose'             => $purpose,
                'datetime'   => date('Y-m-d'),
                'staff_name' => $data['staffDetails']->surname . ' ' . $data['staffDetails']->first_name . ' ' . $data['staffDetails']->othernames,
                'department' => $data['staffDetails']->department,
                'apointment_date' => $data['staffDetails']->appointment_date,
                'designation'     => $data['staffDetails']->Designation,
                'section_or_unit' => $data['staffDetails']->section,
                'basic_salary'    => $basicSalary->basic,
                'eligible_days'   => $numberOfDays,
                'first_appointment_date' => $data['staffDetails']->date_present_appointment,
                'resumption_date'     => $data['staffDetails']->resumption_date,
                //'registered_domicile' => $data['staffDetails']->surname,
            ]);
        }
        if (isset($_POST['Save2x'])) {

            $data['startdate'] = "";
            $data['enddate'] = "";
            $data['nod'] = "";
            $data['leaveType'] = "";
            $data['releavestaff'] = "";
            $data['purpose'] = "";
            $data['address'] = "";
            $data['viewid'] = "";
            $start = date('Y-m-d', strtotime(trim($request['startdates'])));
            $end = date('Y-m-d', strtotime(trim($request['enddates'])));
            $dateRange = $this->getWorkingDays($start, $end);
            if ($dateRange != $request['nod2']) {
                return back()->with('error_message', "Number of days selected does not tally with the selected date range");
            }

            if (DB::table('tblstaff_leave')->where('staffID', $data['staffDetails']->ID)->where('period', date('Y'))->where('leaveType', $request['leaveType'])->exists()) {
                return back()->with('error_message', 'You cannot apply for leave while on leave!');
            }

            $startdate = trim($request['startdate']);
            $enddate = trim($request['enddate']);
            $nod = trim($request['nod2']);
            $leaveType = trim($request['leaveType']);
            $purpose = trim($request['purpose']);
            $address = trim($request['address']);

            //dd($getHolidays);
            $from = \Carbon\Carbon::parse($startdate);
            $to = \Carbon\Carbon::parse($enddate);
            $days = $from->diffInWeekdays($to);
            $actdays = $days + 1;

            $getHolidays = DB::table('tblpublic_holidays')->where('year', date('Y'))->whereBetween('holidays', [$from, $to])->count();
            $nod = $actdays - $getHolidays;

            DB::table('tblstaff_leave')->insert([
                'staffID'    => $data['staffDetails']->ID,
                'startDate'  => $startdate,
                'endDate'    => $enddate,
                'noOfDays'   => $nod,
                'leaveType'  => $leaveType,
                'period'     => date('Y'),
                'addressDuringLeave'  => $address,
                'purpose'             => $purpose,
                'datetime'   => date('Y-m-d'),
                'staff_name' => $data['staffDetails']->surname . ' ' . $data['staffDetails']->first_name . ' ' . $data['staffDetails']->othernames,
                'department' => $data['staffDetails']->department,
                'apointment_date' => $data['staffDetails']->appointment_date,
                'designation'     => $data['staffDetails']->Designation,
                'section_or_unit' => $data['staffDetails']->section,
                'basic_salary'    => $basicSalary->basic,
                'eligible_days'   => $numberOfDays,
                'first_appointment_date' => $data['staffDetails']->date_present_appointment,
                'resumption_date'     => $data['staffDetails']->resumption_date,
            ]);
        }

        $data['LeaveHistory'] = DB::table('tblstaff_leave')->where('staffID', $data['staffDetails']->ID)
            ->leftjoin('tblleave_type', 'tblstaff_leave.leaveType', '=', 'tblleave_type.id')
            ->select('*', 'tblstaff_leave.id as leaveID')
            ->get();

        return view('Leave.apply', $data);
    }

    public function checkRoaster(Request $request)
    {

        $leaveType = $request->get('id');

        $userExistsInTable = DB::table('tblper')->where('UserID', Auth::user()->id)->exists();

        if ($leaveType == 4) {
            if ($userExistsInTable) {
                $data['staffDetails'] = DB::table('tblper')->where('UserID', Auth::user()->id)->first();
                if ($data = DB::table('tblroaster')->where('staffID', $data['staffDetails']->ID)->where('Year', date('Y'))->exists()) {
                    $recordId = $request->get('id');
                    return response()->json($data);
                }
            }
        } elseif ($leaveType == 3) {

            $datas = DB::table('tblleave_type')->where('id', $leaveType)->first();
            //return response()->json($data);

            $userExistsInTable = DB::table('tblper')->where('UserID', Auth::user()->id)->exists();
            if ($userExistsInTable) {
                $data['staffDetails'] = DB::table('tblper')->where('UserID', Auth::user()->id)->first();
                $data['noOfDays'] = DB::table('tblleave_type')->where('id', $leaveType)->first();
                $daysConsumed = DB::table('tblstaff_leave')->where('staffID', $data['staffDetails']->ID)->where('leaveType', $leaveType)->where('period', date('Y'))->sum('noOfDays');
                $daysRemaining = $data['noOfDays']->numberOfDays - $daysConsumed;
            }

            return Response::json(array(
                'allowableDays' => $data['noOfDays']->numberOfDays,
                'daysConsumed'  => $daysConsumed,
                'daysRemaining' => $daysRemaining,
            ));
        } elseif ($leaveType == 2) {

            $datas = DB::table('tblleave_type')->where('id', $leaveType)->first();
            //return response()->json($data);

            $userExistsInTable = DB::table('tblper')->where('UserID', Auth::user()->id)->exists();
            if ($userExistsInTable) {
                $data['staffDetails'] = DB::table('tblper')->where('UserID', Auth::user()->id)->first();
                $data['noOfDays'] = DB::table('tblleave_type')->where('id', $leaveType)->first();
                $daysConsumed = DB::table('tblstaff_leave')->where('staffID', $data['staffDetails']->ID)->where('leaveType', $leaveType)->where('period', date('Y'))->sum('noOfDays');
                $daysRemaining = $data['noOfDays']->numberOfDays - $daysConsumed;
            }

            return Response::json(array(
                'allowableDays' => $data['noOfDays']->numberOfDays,
                'daysConsumed'  => $daysConsumed,
                'daysRemaining' => $daysRemaining,
            ));
        } elseif ($leaveType == 1) {

            $datas = DB::table('tblleave_type')->where('id', $leaveType)->first();

            $userExistsInTable = DB::table('tblper')->where('UserID', Auth::user()->id)->exists();
            if ($userExistsInTable) {
                $data['staffDetails'] = DB::table('tblper')->where('UserID', Auth::user()->id)->first();
                $data['noOfDays'] = DB::table('tblleave_type')->where('id', $leaveType)->first();
                $daysConsumed = DB::table('tblstaff_leave')->where('staffID', $data['staffDetails']->ID)->where('leaveType', $leaveType)->where('period', date('Y'))->sum('noOfDays');
                $daysRemaining = $data['noOfDays']->numberOfDays - $daysConsumed;
            }

            return Response::json(array(
                'allowableDays' => $data['noOfDays']->numberOfDays,
                'daysConsumed'  => $daysConsumed,
                'daysRemaining' => $daysRemaining,
            ));
        }
    }

    public function pushHod($id)
    {

        DB::table('tblstaff_leave')->where('id', $id)->update(['approval_stages_status' => 1]);

        return back()->with('message', 'Leave pushed to HOD');
    }

    public function memo($id)
    {

        $data['leaveType'] = "";
        $data['LeaveTypeList'] = $this->LeaveType();

        $data['staffDetails'] = DB::table('tblstaff_leave')
            ->where('tblstaff_leave.staffID', $id)
            ->leftjoin('tblper', 'tblstaff_leave.staffID', '=', 'tblper.ID')
            ->select('*', 'tblper.ID as pID')
            ->first();

        $data['LeaveHistory'] = DB::table('tblleave_memo')
            ->where('tblleave_memo.staffID', $id)
            ->leftjoin('tblper', 'tblleave_memo.staffID', '=', 'tblper.ID')
            ->leftjoin('tblleave_type', 'tblleave_memo.leaveType', '=', 'tblleave_type.id')
            ->select('*', 'tblleave_memo.date as mdate')
            ->get();

        return view('Leave.memo', $data);
    }

    public function saveMemo(Request $request)
    {

        $leaveType = trim($request['leaveType']);
        $staffID = trim($request['staffID']);
        $from = trim($request['from']);
        $subject = trim($request['subject']);
        $date = $request['memo_date'];
        $content = trim($request['content']);
        //dd($date);
        DB::table('tblleave_memo')->insert([
            'leaveType'   => $leaveType,
            'staffID'     => $staffID,
            'subjectFrom' => $from,
            'subject'     => $subject,
            'date'        => $date,
            'content'     => $content,
        ]);

        return back()->with('message', 'Memo created!');
    }

    public function printMemo($id)
    {

        //dd('tttt');
        $count = DB::table('tblleave_memo')->where('tblleave_memo.staffID', $id)->count();
        if ($count == 0) {
            return back()->with('message', 'Memo not yet generated');
        }

        $data['leaveType'] = "";
        $data['LeaveTypeList'] = $this->LeaveType();

        $data['LeaveHistory'] = DB::table('tblleave_memo')
            ->where('tblleave_memo.staffID', $id)
            ->leftjoin('tblper', 'tblleave_memo.staffID', '=', 'tblper.ID')
            ->leftjoin('tblleave_type', 'tblleave_memo.leaveType', '=', 'tblleave_type.id')
            ->select('*', 'tblleave_memo.date as mdate')
            ->first();

        return view('Leave.print-memo', $data);
    }

    public function LeaveAlert()
    {
        $data['leave'] = DB::table('tblroaster')->where(['is_submitted' => 1, 'Year' => date('Y')])->get();
        $data['period'] = DB::table('tblalert_date')->value('alertperiod');

        return view('Leave.leavealert', $data);
    }

    public function LeaveAlertSettings()
    {

        $data['period'] = DB::table('tblalert_date')->get();

        return view('Leave.alertsettings', $data);
    }

    public function LeaveAlertSettingsSave(Request $request)
    {
        $this->validate($request, [
            'NumberOfDays' => 'required',
        ]);

        if (DB::table('tblalert_date')->where('alertperiod', $request->NumberOfDays)->exists()) {

            DB::table('tblalert_date')->where('alertperiod', $request->NumberOfDays)->update(['alertperiod' => $request->NumberOfDays]);
        } else {
            DB::table('tblalert_date')->update(['alertperiod' => $request->NumberOfDays]);
        }

        return redirect()->back();
    }

    public function ReleaveResponse(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $data['leaveid'] = trim($request['leaveid']);
        $data['remarks'] = trim($request['remarks']);
        $remarks = trim($request['remarks']);
        $leaveid = trim($request['leaveid']);
        $leaveData = $this->AppliedLeave($leaveid);
        if ($leaveData) {
            if (isset($_POST['accept'])) {
                DB::UPDATE("UPDATE `tblstaff_leave` SET `RstaffAction`='Accept', `RStaffComment`='$remarks' where `id`='$leaveid'");
                DB::UPDATE("UPDATE `tblnotification` SET `status`='Complete' WHERE `url`='/self-service/releaveaction' and `actionID`='$leaveid'");
            }
            if (isset($_POST['reject'])) {
                DB::UPDATE("UPDATE `tblstaff_leave` SET `RstaffAction`='Reject', `RStaffComment`='$remarks' where `id`='$leaveid'");
                DB::UPDATE("UPDATE `tblnotification` SET `status`='Complete' WHERE `url`='/self-service/releaveaction' and `actionID`='$leaveid'");
            }
            $data['AppliedLeave'] = $this->AppliedLeave($leaveid);
            return view('Leave.reaveacceptance', $data);
        } else {
            return redirect('/self-service/notification');
        }
    }
    public function Approval(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $data['leaveid'] = trim($request['leaveid']);
        $data['remarks'] = trim($request['remarks']);
        $remarks = trim($request['remarks']);
        $leaveid = trim($request['leaveid']);
        $staffid = $this->StaffID($this->username);
        $leaveData = $this->AppliedLeave($leaveid);
        if ($leaveData) {
            if (isset($_POST['accept'])) {
                DB::UPDATE("UPDATE `tblstaff_leave` SET `status`='Approved', `approvalComment`='$remarks',`approvedBy`='$staffid' where `id`='$leaveid'");
            }
            if (isset($_POST['reject'])) {
                DB::UPDATE("UPDATE `tblstaff_leave` SET `status`='Reject', `approvalComment`='$remarks',`approvedBy`='$staffid' where `id`='$leaveid'");
            }
            $data['AppliedLeave'] = $this->AppliedLeave($leaveid);
            return view('Leave.leaveapproval', $data);
        } else {
            return redirect('/self-service/notification');
        }
    }
    public function LeaveQuery(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $court = trim($request['court']);
        if ($court == "") {
            $court = $this->StaffCourt($this->username);
        }
        $period = trim($request['period']);
        if ($period == "") {
            $period = $this->LeavePeriod();
        }
        $data['court'] = $court;
        $data['period'] = $period;
        $division = trim($request['division']);
        $data['division'] = $division;
        $status = trim($request['status']);
        $data['status'] = $status;
        $department = trim($request['department']);
        $data['department'] = $department;
        $data['courtList'] = $this->CourtList();
        $data['depatmentList'] = $this->DepartmentList($court);
        $data['Division'] = $this->DivisionList($court);
        $data['LeaveStatus'] = $this->LeaveStatus();
        $data['LeavePeriod'] = $this->LeavePeriodList();

        //die(json_encode($this->LeavesQuery($period,$court,$division,$department,$status)));
        $data['LeaveQuery'] = $this->LeavesQuery($period, $court, $division, $department, $status);

        return view('Leave.leavequery', $data);
    }

    public function getNumberOfDays(Request $request)
    {


        $start = date('Y-m-d', strtotime($request['start']));
        $end = date('Y-m-d', strtotime($request['end']));
        $dateRange = $this->getWorkingDays($start, $end);
        return response()->json($dateRange);
    }
}
