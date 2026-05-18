<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LeaveAdministratorController extends Controller
{
    public function index2()
    {
        $getRecord = DB::table('tblroaster')->where('Year', date('Y'))->orderBy('roasterID', 'desc')->simplePaginate(7);
        $staffDetails = DB::table('tblper')->select('first_name','surname', 'othernames','fileNo','ID')->get();

        return view('LeaveAdministrator.index2', compact('staffDetails','getRecord'));
    }

    public function fetchStaffLeave2(Request $request)
    {
        $staffDetails = DB::table('tblper')->select('first_name','surname', 'othernames','fileNo','ID')->get();

        $getStaffLeave = DB::table('tblroaster')
            ->join('tblper', 'tblper.ID', '=', 'tblroaster.staffID')
            ->join('tbldepartment', 'tbldepartment.id', '=', 'tblper.department')
            ->select('tblroaster.*', 'tbldepartment.department as staffDept')
            ->where('staffID', '=', "$request->staffID")
            ->where('Year', date('Y'))
            ->simplePaginate(7);

        return view('LeaveAdministrator.index2', [
            'getRecord' => $getStaffLeave,
            'staffDetails' => $staffDetails
        ]);
    }

    public function updateStaffLeave(Request $request)
    {
        $is_saved = 0;
        //get Holidays and remove from date range
        $holidays = DB::table('holidays')->select('*')->where('year', date('Y'))->pluck('holiday');
        $startDate = date('Y-m-d', strtotime('-1 day', strtotime($request->startDate)));
        $MyDateCarbon = Carbon::parse($startDate);
        $MyDateCarbon->addWeekdays($request->leaveDays);
        $newHolidays = $holidays->toArray();

        for ($i = 1; $i <= $request->leaveDays; $i++) {
            if (in_array(Carbon::parse($startDate)->addWeekdays($i)->toDateString(), $newHolidays)) {
                $MyDateCarbon->addWeekday();
            }
        }

        $endDate = $MyDateCarbon;
        //end holidays

        $this->validate($request, [
            'leaveDays'     => 'required|numeric',
            'startDate'         => 'required|date',
            'homeAddress'        => 'required|string',
            'description'        => 'required|string',
        ]);

        $getStaffLeave = DB::table('tblroaster')
            ->select('tblroaster.*', 'tblroaster.staffID as staffID')
            ->where('roasterID', $request->recordID)
            ->where('Year', date('Y'))
            ->first();

        $currentDate = date('Y-m-d');
        $getLastEndDate = DB::table('tblroaster')->where(['staffID' => $getStaffLeave->staffID, 'Year' => date('Y')])->max('endDate');
        $getFirstStartDate = DB::table('tblroaster')->where(['staffID' => $getStaffLeave->staffID, 'Year' => date('Y')])->min('startDate');

        //get details
        $details  = DB::table('tblper')->where('ID', $getStaffLeave->staffID)->first();
        $getLeaveDays = DB::table('tblgrade_leave_assignment')->where(['grade' => $details->grade, 'courtID' => 9])->value('noOfDays');

        //get other days for check
        $getOtherDays = DB::table('tblroaster')->select('*')->where(['staffID' => $getStaffLeave->staffID, 'Year' => date('Y')])->where('roasterID', '!=', $request->recordID)->sum('leaveDays');

        if($request->leaveDays > ($getLeaveDays - $getOtherDays)){
            try{
                DB::table('tblroaster')->where('roasterID', $request['recordID'])->update([
                    'homeAddress'   => $request['homeAddress'],
                    'description'   => $request['description']
                ]);
                $is_saved = 1;
            }catch(\Throwable $e){}
            return redirect()->back()->with('danger', 'Sorry Days must not exceed maximum');
        }

        if(($request->startDate > $currentDate) && ($endDate < $getFirstStartDate)){

            try{
                DB::table('tblroaster')->where('roasterID', $request['recordID'])->update([
                    'staff_name'    => (isset($details) && $details ? $details->surname .' '. $details->first_name .' '. $details->othernames : null),
                    'startDate'     => date('Y-m-d', strtotime($request['startDate'])),
                    'endDate'       => $endDate,
                    'Year'          => date('Y', strtotime($endDate)),
                    'leaveDays'     => $request['leaveDays'],
                    'homeAddress'   => $request['homeAddress'],
                    'description'   => $request['description']
                ]);
                $is_saved = 1;
            }catch(\Throwable $e){}
            if($is_saved){
                return redirect()->back()->with('success', 'Your record was updated successfully.');
            }

        }elseif(($request->startDate <= $getLastEndDate)){
            try{
                DB::table('tblroaster')->where('roasterID', $request['recordID'])->update([
                    'homeAddress'   => $request['homeAddress'],
                    'description'   => $request['description']
                ]);
                $is_saved = 1;
            }catch(\Throwable $e){}
            return redirect()->back()->with('danger', 'New start Date must be greater that last end date (Please Select New start Date)');
        }

        try{
            DB::table('tblroaster')->where('roasterID', $request['recordID'])->update([
                'staff_name'    => (isset($details) && $details ? $details->surname .' '. $details->first_name .' '. $details->othernames : null),
                'startDate'     => date('Y-m-d', strtotime($request['startDate'])),
                'endDate'       => $endDate,
                'Year'          => date('Y', strtotime($endDate)),
                'leaveDays'     => $request['leaveDays'],
                'homeAddress'   => $request['homeAddress'],
                'description'   => $request['description']
            ]);
            $is_saved = 1;
        }catch(\Throwable $e){}
        if($is_saved){
            return redirect()->back()->with('success', 'Your record was updated successfully.');
        }
        return redirect()->back()->with('danger', 'Sorry, we cannot process your record. Please try again.');

    }

    public function deleteStaffLeaveRoasterApplication($recordID = null)
    {
        try{
            if(DB::table('tblroaster')->where('roasterID', $recordID)->first())
            {
                DB::table('tblroaster')->where('roasterID', $recordID)->delete();

                return redirect()->back()->with('success', 'Your record was deleted successfully.');
            }
        }catch(\Throwable $e){}

        return redirect()->back()->with('error', 'Sorry, record not found!');
    }

}
