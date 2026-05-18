<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\User;
use Auth;
use DB;

class LeaveResumptionController extends Controller
{
    protected $userID;

    //Contruct
    public function __construct()
    {
        $this->middleware('auth');
        $this->userID = (Auth::check() ? Auth::user()->id : null);
    }

    //get staff details
    public function staffDetails()
    {
        $details = null;
        try{
            $details  = DB::table('tblper')->where('UserID', $this->userID)
                        ->leftJoin('tbldesignation', 'tbldesignation.id', '=', 'tblper.Designation')
                        ->leftJoin('tblsections', 'tblsections.id', '=', 'tblper.section')
                        ->select('tblper.*', 'tbldesignation.designation', 'tblsections.id', 'tblsections.section as staff_section')
                        ->first();
        }catch(\Throwable $e){}
        return $details;
    }

    //Create Page
    public function create()
    {
        $id = $this->userID;

        $data = [];
        if( $id <= 0){
            return redirect('/login')->with('error', 'Please you need to login again.');
        }
        try{

            $staffID = DB::table('tblper')->where('UserId', $id)->value('ID');

            $data['getLeaveRoaster'] = DB::table('tblroaster')->where('staffID', $staffID)->where('Year', date('Y'))->get();
            $data['details']  = $this->staffDetails();
            $data['getRecord'] = DB::table('tblleave_resumption_form')->where('tblleave_resumption_form.staffID', $id)->orderBy('tblleave_resumption_form.id', 'Desc')
                                ->leftJoin('tblroaster', 'tblroaster.roasterID', '=', 'tblleave_resumption_form.leave_roasterID')
                                ->select('tblleave_resumption_form.*', 'tblroaster.leaveDays as staff_leave_days')
                                ->paginate(20);
        }catch(\Throwable $e){}

        // return $data;

        return view('LeaveResumption.leaveResumptionApplication', $data);
    }


    //Save new recor
    public function store(Request $request)
    {
        $is_saved = 0;
        $id = $this->userID;

        if( $id <= 0){
            return redirect('/login')->with('error', 'Please you need to login again.');
        }
        $this->validate($request, [
            'staffLeave'        => 'required|numeric',
			'departureDate'		=> 'required|date',
			'resumptionDate'	=> 'required|date',
		]);

        try{
            $details  = $this->staffDetails();
            if($details)
            {
                $is_saved =  DB::table('tblleave_resumption_form')->insertGetId([
                    'staff_name'          => (isset($details) && $details ? $details->surname .' '. $details->first_name .' '. $details->othernames : null),
                    'staffID'             => $id,
                    'leave_roasterID'     => $request['staffLeave'],
                    'rank'                => ($details ? $details->designation : ''),
                    'posting_section'     => ($details ? $details->staff_section : ''),
                    'departure_date'      => date('Y-m-d', strtotime($request['departureDate'])),
                    'resumption_date'     => date('Y-m-d', strtotime($request['resumptionDate'])),
                ]);
            }else{
                $is_saved = null;
            }
        }catch(\Throwable $e){}
        if($is_saved){
            return redirect()->back()->with('success', 'Saved, record was saved.');
        }
        return redirect()->back()->with('danger', 'Sorry, we cannot process your record. Please try again.');
    }


    //Submit Application
    public function submitLeaveResumptionForm($getID = null)
    {
        $isSave = 0;
        try{
            if(DB::table('tblleave_resumption_form')->where('id', $getID)->first())
            {
                $isSave = DB::table('tblleave_resumption_form')->where('id', $getID)->update(['is_submitted' => 1]);
                if($isSave)
                {
                    return redirect()->route('create')->with('success', 'Your application was submitted successfully.');
                }
            }
        }catch(\Throwable $e){}
        return redirect()->route('create')->with('error', 'Sorry, we cannot update this record or record not found!');
    }


    //Update record
    public function update(Request $request)
    {
        $is_saved = 0;
        $id = $this->userID;

        if( $id <= 0){
            return redirect('/login')->with('error', 'Please you need to login again.');
        }
        $this->validate($request, [
            'staffLeave'        => 'required|numeric',
			'departureDate'		=> 'required|date',
			'resumptionDate'	=> 'required|date',
		]);
        try{
            $details  = $this->staffDetails();
            if($details)
            {
                $is_saved =  DB::table('tblleave_resumption_form')->where('id', $request['recordID'])->update([
                    'staff_name'          => (isset($details) && $details ? $details->surname .' '. $details->first_name .' '. $details->othernames : null),
                    'leave_roasterID'     => $request['staffLeave'],
                    'rank'                => ($details ? $details->designation : ''),
                    'posting_section'     => ($details ? $details->staff_section : ''),
                    'departure_date'      => date('Y-m-d', strtotime($request['departureDate'])),
                    'resumption_date'     => date('Y-m-d', strtotime($request['resumptionDate'])),
                ]);
            }else{
                $is_saved = null;
            }
        }catch(\Throwable $e){}
        if($is_saved){
            return redirect()->back()->with('success', 'Saved, record was update successfull.');
        }
        return redirect()->back()->with('danger', 'Sorry, we cannot process your record. Please try again.');
    }


    public function delete($recordID = null)
    {
        try{
            if(DB::table('tblleave_resumption_form')->where('id', $recordID)->first())
            {
                DB::table('tblleave_resumption_form')->where('id', $recordID)->delete();

                return redirect()->back()->with('success', 'Your record was deleted successfully.');
            }
        }catch(\Throwable $e){}

        return redirect()->back()->with('error', 'Sorry, record not found!');
    }


    //Create Page
    public function viewReport($recordID = null)
    {
        $id = $this->userID;
        $data = [];
        if( $id <= 0){
            return redirect('/login')->with('error', 'Please you need to login again.');
        }
        try{
            $getRecord = DB::table('tblleave_resumption_form')
                        ->where('tblleave_resumption_form.id', $recordID)
                        ->first();
            if($getRecord)
            {
                $data['getDetails'] = $getRecord;
                return view('LeaveResumption.reportLeaveResumptionApplication', $data);
            }
        }catch(\Throwable $e){}
        return redirect()->back()->with('error', 'Sorry, record not found!');
    }



}//end class
