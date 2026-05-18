<?php

namespace App\Http\Controllers\hr;

use Illuminate\Http\Request;
use App\Http\Requests;
use Carbon\carbon;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;


//use Illuminate\Support\Str;

class ApplyEventController extends ParentController
{
  
   public function Create ()
   {
      
        // $data['getEvent']        = DB::table('tblstaff_event')
        //                               ->leftjoin('tblper', 'tblstaff_event.staffid', '=', 'tblper.UserID')
        //                               ->leftjoin('tblevent_type', 'tblstaff_event.event_typeid', '=', 'tblevent_type.id')
        //                               ->get();
                                      
        $data['getEventOfUser']  = DB::table('tblstaff_event')
                                      
                                      ->leftjoin('tblper', 'tblstaff_event.staffid', '=', 'tblper.UserID')
                                      ->leftjoin('tblevent_type', 'tblstaff_event.event_typeid', '=', 'tblevent_type.id')
                                      ->where('tblstaff_event.staffid', Auth::user()->id)
                                      ->select('*', 'tblstaff_event.id as staffEventId')
                                      ->orderBy('tblstaff_event.id', 'DESC')
                                      ->get();
                                      //dd($data['getEventOfUser']);
        //->first();
       
        //$data['userinfo']        = DB::table('tblper')->where('UserID', Auth::id())->first();
        $data['userinfo']        = DB::table('tblper')->where('UserID', Auth::user()->id)->first();
      
        $data['getAllEvent']     = DB::table('tblevent_type')->get();
        //dd($data['userinfo']);
       return view ('eventType/addEvent', $data);
   }    
       
    public function store (Request $request)
    {	
       
    	$this->validate($request, [
		'surname'  => 'required',
		'firstname' => 'required',
		'eventid'     => 'required',
		'description'=> 'required',
		'start'     =>  'required|date|after:today',
		'end'       =>  'required|date|after:start',
		'venue'     =>  'required'
		]);
		
       $staffid      = $request->input('staffid');
       $eventid      = $request->input('eventid');
       $description  = $request->input('description');
       $start        = $request->input('start');
       $end          = $request->input('end');
       $venue        = $request->input('venue');
       
       //CALCULATING THE NUMBER OF DAYS APPLYING FOR EVENT LEAVE
        $datetime1 = \Carbon\Carbon::parse($start);
        $datetime2 = \Carbon\Carbon::parse($end);
        $workingday = $datetime2->diffInWeekdays($datetime1);
      //  dd($workingday);
       
	     $save  =   DB::table('tblstaff_event')->insert([
	         'staffid'               => $staffid,
    		 'event_typeid'         =>  $eventid,
    		 'description'          => $description,
		    'event_start_date'      => Carbon::parse($start)->format('Y-m-d'),
		    'event_end_date'        =>  Carbon::parse($end)->format('Y-m-d'),
		    'venue'                 =>  $venue,
		    'number_of_days'        =>  $workingday
		]);
    return redirect ('eventType/addEvent')->with('msg', 'Event application was successful');
    }
       
    public function CheckDelete($userid,$recordID) 
    {
      // dd($id."  - ". $recordID);
       //$del = Auth::user()->id;
       
       $applicant = DB::table('tblstaff_event')->where('staffid', $userid)->get();
        //dd($applicant);
        if (Auth::user()->id == $userid )
        {
             
                if(DB::table('tblstaff_event')->where('id', $recordID)->where('event_status', '=', 0)->delete()){
                    
                    return back()->with('msg','Staff event record deleted!');
                }
                 return back()->with('err','You cannot delete this record because record is in approval process...');
        }
        else
        {
              return back()->with('err','You cannot delete this record...');
        }
    }
    
}