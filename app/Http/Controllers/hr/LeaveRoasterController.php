<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\User;
use Auth;
use DB;
use Illuminate\Support\Carbon;

class LeaveRoasterController extends Controller
{
    protected $userID;

    //Contruct
    public function __construct()
    {
        $this->middleware('auth');
        $this->userID = (Auth::check() ? Auth::user()->id : null);
        $this->staffID = DB::table('tblper')->where('UserID', $this->userID)->value('ID');
    }

    //Create Page
    public function createLeaveRoaster()
    {
        $getLeaveDays = null;
        $id = $this->userID;
        $staffID = DB::table('tblper')->where('UserID', $id)->value('ID');
        if(empty($staffID))
        {
            return back()->with('message','You are not Legible to view that page');
        }
        if( $id <= 0){
            return redirect('/login')->with('error', 'Please you need to login again.');
        }
        try{
            $data['details']  = DB::table('tblper')->where('UserID', $id)->first();

            $data['department'] = DB::table('tblper')
                                ->join('tbldepartment', 'tbldepartment.id', '=', 'tblper.department')
                                ->where('UserID', $id)
                                ->select('tbldepartment.department as staffDept', 'tbldepartment.id as department_id')
                                ->first();

            if($data['details']){
                $getLeaveDays = DB::table('tblgrade_leave_assignment')->where(['grade' => $data['details']->grade, 'courtID' => 9])->value('noOfDays');
            }
            $data['getLeaveDays'] = $getLeaveDays;
            $data['getRecord'] = DB::table('tblroaster')->where('staffID', $staffID)->orderBy('created', 'Desc')->paginate(20);

            /**
             * Newly Added Code
             */
            $noOfUsedDays = 0;

            //this gets all the number of accepted leave days and sums them up
            foreach($data['getRecord'] as $records)
            {
                if($records->is_approved === 1)
                {
                    $noOfUsedDays +=  $records->leaveDays;
                }
            }
            /**
             * END Newly Added Code
             */

            $data['daysLeft']   = ($getLeaveDays - $data['leaveUsed']);


            if( $data['leaveUsed'] >= $getLeaveDays )
            {
                return view('LeaveRoaster.leaveRoasterApplication', $data)->with('warning', 'Sorry you cannot apply for more leave. You have used up your leave days. Thanks');
            }
        }catch(\Throwable $e){ }


            /****************
             * ******IF ANY LEAVE IS DISAPPROVED BY HEAD, THE DAYS LEFT VALUE WILL NOT INCLUDE
             * THE DAY COUNT FOR THAT PARTICULAR ROASTER**************************************
             */
            foreach($data['getRecord'] as $record)
            {
                if($record->is_approved === 2)
                {
                    //Gets the total number of allowable days and minus all used or accpted days from it ;)
                    $data['daysLeft'] = ($getLeaveDays - $noOfUsedDays);
                }
            }

            /************END OF THAT LOGIC **************** */

        return view('LeaveRoaster.leaveRoasterApplication', $data);
    }

    //Save new record
    public function storeLeaveRoaster(Request $request)
    {
        $getLeaveDays = null;
        $details = null;
        $is_saved = 0;
        $id = $this->userID;
        if( $id <= 0){
            return redirect('/login')->with('error', 'Please you need to login again.');
        }
        $this->validate($request, [
            'leaveDays'         => 'required|numeric',
            'homeAddress'		=> 'required|string',
            'description'		=> 'required|string',
            'startDate'         => 'required|date',
		]);

        //get Holidays and remove from date range
        $holidays = DB::table('holidays')->select('*')->where('year', date('Y'))->pluck('holiday');
        $startDate = date('Y-m-d', strtotime('-1 day', strtotime($request->startDate)));
        $MyDateCarbon = Carbon::parse($startDate);
        $MyDateCarbon->addWeekdays($request->leaveDays);
        $newHolidays = $holidays->toArray();
        for($i = 1; $i <= $request->leaveDays; $i++) {
            if(in_array(Carbon::parse($startDate)->addWeekdays($i)->toDateString(), $newHolidays)) {
                $MyDateCarbon->addWeekday();
            }
        }
        $endDate = $MyDateCarbon;
        //end holidays
        $dd = DB::table('tblroaster')->where('staffID', $this->staffID)->orderBy('roasterID', 'Desc')->value('endDate');

        if(($request->getleaveDays) === ($request->leaveUsed)){
            return redirect()->back()->with('danger', 'Sorry! You can no longer add to Roster');
        }
        if(($request->leaveDays) > ($request->getleaveDays) || ($request->leaveDays) > ($request->getleaveDays - $request->leaveUsed)){
            return redirect()->back()->with('danger', 'Leave working days cannot be greater than Annual leave working days Or Already Used leave Days');
        }
        if(($request->leaveDays) <= 0){
            return redirect()->back()->with('danger', 'Leave working days cannot be less than or equal zero');
        }
        if($request['startDate'] <= (DB::table('tblroaster')->where('staffID', $this->staffID)->orderBy('roasterID', 'Desc')->value('endDate')))
        {
            return redirect()->back()->with('danger', 'Sorry, the start date should be greater than your previous end date')->with($request->all());
        }
        //dd($request['startDate'] <= $dd);
        try{
            //check user system gets user details
            $details  = DB::table('tblper')->where('UserID', $id)->first();
            if($details){
                $getLeaveDays = DB::table('tblgrade_leave_assignment')->where(['grade' => $details->grade, 'courtID' => 9])->value('noOfDays');
            }
            $daysLeft = ($getLeaveDays - DB::table('tblroaster')->where('staffID', $id)->where('Year', date('Y'))->sum('leaveDays'));
            //validate the leave days
            if($request['leaveDays'] > $daysLeft)
            {
                return redirect()->back()->with('danger', 'Sorry you have exhausted your leave days from the roaster. Leave days left (i.e ' . $daysLeft . ').');
            }
        }catch(\Throwable $e){}

        try{
            //revalidate if user has not used up his/her leave for the year
            if( DB::table('tblroaster')->where('staffID', $id)->where('Year', date('Y'))->sum('leaveDays') < $getLeaveDays )
            {
                $is_saved =  DB::table('tblroaster')->insertGetId([
                    'staffID'       => (isset($details) && $details ? $details->ID : null), //$id,
                    'staff_name'    => (isset($details) && $details ? $details->surname .' '. $details->first_name .' '. $details->othernames : null),
                    'startDate'     => date('Y-m-d', strtotime($request['startDate'])),
                    'endDate'       => $endDate,
                    'Year'          => date('Y', strtotime($endDate)),
                    'leaveDays'     => $request['leaveDays'],
                    'homeAddress'   => $request['homeAddress'],
                    'description'   => $request['description'],
                    'department_id' => $request['department_id'],
                    'is_submitted' => 1
                ]);
            }else{
                $is_saved = null;
            }
        }catch(\Throwable $e){}
        if($is_saved){
            return redirect()->back()->with('success', 'Saved, application was submitted.');
        }
        return redirect()->back()->with('danger', 'Sorry, we cannot process your application. Please try again.');
    }


    //Submit Application
    public function submitLeaveRoasterApplication($getRoasterID = null)
    {
        $isSave = 0;
        try{
            if(DB::table('tblroaster')->where('roasterID', $getRoasterID)->first())
            {
                $isSave = DB::table('tblroaster')->where('roasterID', $getRoasterID)->update(['is_submitted' => 1]);

                if($isSave)
                {
                    return redirect()->route('createLeaveRoaster')->with('success', 'Your application was submitted successfully.');
                }
            }
        }catch(\Throwable $e){}

        return redirect()->route('createLeaveRoaster')->with('error', 'Sorry, we cannot update this record or record not found!');
    }


    //Update record
    public function updateLeaveRoasterApplication(Request $request)
    {
        $getLeaveDays = null;
        $is_saved = 0;
        $id = $this->userID;
        if( $id <= 0){
            return redirect('/login')->with('error', 'Please you need to login again.');
        }

        //get Holidays and remove from date range
        $holidays = DB::table('holidays')->select('*')->where('year', date('Y'))->pluck('holiday');
        $startDate = date('Y-m-d', strtotime('-1 day', strtotime($request->startDate)));
        $MyDateCarbon = Carbon::parse($startDate);
        $MyDateCarbon->addWeekdays($request->leaveDays);
        $newHolidays = $holidays->toArray();
        for($i = 1; $i <= $request->leaveDays; $i++) {
            if(in_array(Carbon::parse($startDate)->addWeekdays($i)->toDateString(), $newHolidays)) {
                $MyDateCarbon->addWeekday();
            }
        }
        $endDate = $MyDateCarbon;
        //end holidays

        $this->validate($request, [
            'leaveDays'     => 'required|numeric',
			'startDate'         => 'required|date',
            'homeAddress'		=> 'required|string',
            'description'		=> 'required|string',
		]);

        $getOtherDays = DB::table('tblroaster')->select('*')->where(['staffID'=>$this->staffID, 'Year'=>date('Y')])->where('roasterID' ,'!=', $request->recordID)->sum('leaveDays');
        if($request->leaveDays > ($request->getleaveDays - $getOtherDays)){
            return redirect()->back()->with('danger', 'Sorry Days must not exceed maximum');
        }

        $currentDate = date('Y-m-d');
        $getLastEndDate = DB::table('tblroaster')->where(['staffID' => $this->staffID, 'Year' => date('Y')])->max('endDate');
        $getFirstStartDate = DB::table('tblroaster')->where(['staffID' => $this->staffID, 'Year' => date('Y')])->min('startDate');
        $details  = DB::table('tblper')->where('UserID', $id)->first();

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

        }
       /*  elseif(($request->startDate <= $getLastEndDate)){
            return redirect()->back()->with('danger', 'New start Date must be greater that last end date (Please Select New start Date)');
        } */

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

    public function deleteLeaveRoasterApplication($recordID = null)
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


    //Create Page
    public function leaveRoasterReport(Request $request)
    {
        $data['year'] = ($request['year'] ? $request['year'] : date('Y'));
        $getLeaveDays = null;
        try{
            $data['getRecord'] = DB::table('tblroaster')->where('Year', $data['year'])->orderBy('startDate', 'Desc')->orderBy('staff_name', 'ASC')->paginate(25);
        }catch(\Throwable $e){}

        return view('LeaveRoaster.leaveRoasterReport', $data);
    }

}//end class
