<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;
//use Auth;
use App\Http\Requests;
use DB;
use Auth;
use Session;
use Carbon\carbon;
use Illuminate\Support\Facades\Input;

class AnnualLeaveController extends functionController
{
	public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->middleware('auth');
        $this->username = Session::get('userName');
            return $next($request);
        });
    }//
    
    public function getNotification(Request $request)
   {  
	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	   $staffid=$this->StaffID($this->username);
	   $data['Notification']=$this->SelfNotification($staffid);
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
	   
	   $courtid= $this->StaffCourt($this->username);
	   $data['LeaveGradetList']=$this->LeaveGradetList($courtid) ; 
	   
   	return view('Leave.gradeleavedefinition', $data);
   }
   
   
   
   public function postDefinition(Request $request)
   {   
   	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['grade'] =trim($request['grade']);
	   $data['ald'] = trim($request['ald']);
	   $data['success'] = "";
	   $delcode=trim($request['delcode']);
	   
	  DB::DELETE ("DELETE FROM `tblgrade_leave_assignment` WHERE `id`='$delcode'");
	  
	   $courtid= Auth::user()->courtID;//$this->StaffCourt($this->username);
	   
	$data['LeaveGradetList']=$this->LeaveGradetList($courtid) ; 
	$updatedby = $this->username;
	    
		$grade=trim($request['grade']);
		$ald=trim($request['ald']);
		if ( isset( $_POST['Update'] ) ) {
		$this->validate($request, [
		'grade'      	=> 'required',
-		'ald'      	=> 'required',
		]);
			if ($this->ConfirmGradeLeave($courtid,$grade))
			{
				DB::UPDATE ("UPDATE `tblgrade_leave_assignment` SET `noOfDays`='$ald' WHERE `courtID`='$courtid' and `grade`='$grade'");
			}
			else
			{
				DB::INSERT ("INSERT INTO `tblgrade_leave_assignment`(`courtID`, `grade`, `noOfDays`) VALUES ('$courtid','$grade','$ald')");
			}
		
			$data['success'] = "successfully updated";
			$data['grade'] = "";
			$data['ald'] = "";
			$data['LeaveGradetList']=$this->LeaveGradetList($courtid) ; 
			return view('Leave.gradeleavedefinition', $data);
		 }
		 
		    	return view('Leave.gradeleavedefinition', $data);
   }
   public function ApplicationForm(Request $request)
   {  
       //dd('y');
       $today = carbon::today();
           
       //getting the user login details
       $u= Auth::user()->username;
       $p= Auth::user()->temp_pass;
       $uid= Auth::user()->id;
       //dd($uid);
              
	   //getting user input
	   $userID = trim($request['userID']);
	   $data['userID'] = trim($request['userID']);
	   $data['deptID'] = trim($request['deptID']);
	   $data['year'] = trim($request['year']);
	   $data['startdate'] = trim($request['startdate']);
	   $data['enddate'] = trim($request['enddate']);
	   $data['nod'] = trim($request['nod']);
	   $data['status'] = "1";
	   $data['leaveType'] = DB::table('tblleave_type')->where('leaveType','Annual')->first();
	   $leaveType = DB::table('tblleave_type')->where('leaveType','Annual')->first();

	   $apply = Input::get('apply');
	  
	   //calling the function GetUserDetail()
	   $data['userdetail']=$this->GetUserDetail($u,$p);
	   $checkstaff=$this->GetUserDetail($u,$p);
	   
	   //calling the function DisplayDetail() to display single user information
	   $data['displaydetail']=$this->DisplayDetail($u,$p);
	  
	   $getUserGrade=$this->GetUserGrade($u,$p); //function to get user grade
	   $data['sumleave']=$this->SumUserLeave($u,$p); //function to sum all user leave
	   
	   $data['getleavestatus']=DB::table('tblleave_status')->get();
	   $data['getdept']=DB::table('tbldepartment')->get();
	   
	   if(DB::table('tblgrade_leave_assignment')->where('leavetype',$leaveType->id)->where('grade',$getUserGrade->grade)->count()>0)
	    {
	       //checking if staff belongs to a dept
    	   if(DB::table('tbldepartment')->where('id','=',$checkstaff->department)->count()>0)
    	   {
    	          //if staff belongs to the dept, get the leave days 
        		  $data['leavedays']=DB::table('tblgrade_leave_assignment')->where('leavetype',$leaveType->id)->where('grade',$getUserGrade->grade)->first();
        		  $data['userdetail']=$this->GetUserDetail($u,$p);
        		  
        		  //$data['gethod_comment']=$this->getHODReply($uid);
            	  
                  return view('Leave.annualleaveapplication', $data);
               
    	   }
    	   else
    	   {
        	   	 //dd('None staff');
        	     return view('Leave.nonStaffPage', $data);
    	   	
    	   }
	   }
	   else
	   {
	       echo "<script>alert('User grade not yet assigned ')</script>";
	       return redirect('/');
	   }

   }
   
   //function to get hod reply
   public function getHODReply($staffID)
   {
       $hodreply = DB::table('annual_leave_comments')
       ->leftjoin('annual_leave','annual_leave_comments.leaveID','=','annual_leave.ID')
       ->where('annual_leave_comments.applicant','=',1)
       ->where('annual_leave_comments.userID','=',$staffID)
       ->get();
       
       return $hodreply;
   }
   
   public function getLeaveDaysSum(Request $request)
   { 
       $startdate = trim($request['startdate']);
	   $enddate = trim($request['enddate']);
	   
	   $from = \Carbon\Carbon::parse($startdate);
	   $to = \Carbon\Carbon::parse($enddate);
	   $days= $to->diffInWeekdays($from);
	   return response()->json($days); 
   }
   
   //save application
   public function saveApplicationForm(Request $request)
   {  
        
        
        $this->validate($request,[
            
            'Year'          => 'required|numeric',
            'StartDate'     => 'required|date',
            'EndDate'       => 'required|date',
            'NumberOfDays'  => 'required|numeric',
            
            ]);
        $today = carbon::today();
           
        //getting the user login details
        $u= Auth::user()->username;
        $p= Auth::user()->temp_pass;
           
	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	   $data['RstaffAction'] = "";
	   $data['status'] = "";
	   
	   //getting user input
	   $data['userID'] = trim($request['userID']);
	   $data['deptID'] = trim($request['deptID']);
	   $data['hodID'] = trim($request['hodID']);
	   $data['year'] = trim($request['Year']);
	   $data['startdate'] = trim($request['StartDate']);
	   $data['enddate'] = trim($request['EndDate']);
	   $data['nod'] = trim($request['NumberOfDays']);
	   $data['status'] = "1";
	   
	   
	   //get and assigned user input
	   $leaveType  = trim($request["leaveType"]);
	   $grade = trim($request["grade"]);
	   //dd("$grade");
	   $userID = trim($request['userID']);
	   $deptID = trim($request['deptID']);
	   $hodID = trim($request['hodID']);
	   $year = trim($request['Year']);
	   $startdate = trim($request['StartDate']);
	   $enddate = trim($request['EndDate']);
	   $nod = trim($request['NumberOfDays']);
	   $comment = trim($request['comment']);
	   $status = "1";
   
	   $apply = Input::get('apply');
	  
	   //calling the function GetUserDetail()
	   $data['userdetail']=$this->GetUserDetail($u,$p);
	   
	   //calling the function DisplayDetail() to display user information
	   $data['displaydetail']=$this->DisplayDetail($u,$p);
	            
       $from = \Carbon\Carbon::parse($startdate);
	   $to = \Carbon\Carbon::parse($enddate);
	   $days= $to->diffInWeekdays($from);
	   $actdays=$days+1;
	   //dd($days);
	   
	   $sumleave=$this->SumUserLeave($u,$p);
	   
	   $data['getleavestatus']=DB::table('tblleave_status')->get();
	   $data['getdept']=DB::table('tbldepartment')->get();
	   $getleaveType=DB::table('tblleave_type')->where('id',$leaveType)->first();
	   if(DB::table('tblgrade_leave_assignment')->where('leavetype',$leaveType)->where('grade',$grade)->count()>0)
	   {
	     $checkleaveDays=DB::table('tblgrade_leave_assignment')->where('leavetype',$leaveType)->where('grade',$grade)->first();
	      
	     //dd($checkleaveDays->noOfDays);
	     $actualLeaveDays=$checkleaveDays->noOfDays-$sumleave;
	   
        	 if( $actdays!=$nod ){
        	 
        	     return back()->with('error_message','No. of Days not correct with Start date and End date of leave. Suggestions: '. $actdays ." days");
        	     //return back()->with($data);
        	     //return redirect()->action('AnnualLeaveController@ApplicationForm');
        	     //return redirect()->away('https://www.google.com');
        	 
        	 }else{
        
        		   if($actualLeaveDays==0){
        		   
        		    return back()->with('error_message','You have exhausted your leave.');
        		   
        		   }
        		   elseif($actualLeaveDays<$nod){
        		   
        		    return back()->with('error_message','Please select either '.$actualLeaveDays.'days or lesser');
        		   
        		   }
        		 
        		   else{
        		   
        			   if (isset($apply)) {
        			   
        			    $id= DB::table('annual_leave')->insert([
        		
        			          'staffid' => $userID,
        			          'deptid' => $deptID,
        			          'year'    => $year,
        			          'startdate'    => $startdate,
        			          'enddate'         => $enddate,
        			          'nod' => $nod,
        			          'statusid' => '1',
        			          'hodstatus' => '1',
        			          'leavetype' => $getleaveType->leaveType,
        			          'datetime'    => $today->toDateString(),
        			          'hodid' => $hodID,
        			          'grade'=>$grade,
        			          'comment'=>$comment,
        			          'submit_application' =>'1',
        			
        			        ]);
        			        
        			      
        			   }
        			   else
        			   {
        			     //$data['error']="wrong activity";
        			   }
        		   
        	   	        return back()->with('message','Application submitted successfully!');
        	   	
        	          }
        	   }
	       }
	   
   }
   
 //reapply leave
   public function reApply(Request $request)
   {  
          
        $today = carbon::today();
           
        //getting the user login details
        $u= Auth::user()->username;
        $p= Auth::user()->temp_pass;
           
	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	   $data['RstaffAction'] = "";
	   $data['status'] = "";
	   
	   //getting user input
	   $data['userID'] = trim($request['userID']);
	   $data['deptID'] = trim($request['deptID']);
	   $data['hodID'] = trim($request['hodID']);
	   $data['year'] = trim($request['year']);
	   $data['startdate'] = trim($request['startdate']);
	   $data['enddate'] = trim($request['enddate']);
	   $data['nod'] = trim($request['nod']);
	   $data['status'] = "1";
	   
	   
	   
	   //get and assigned user input
	   $UserLeaveID  = trim($request["UserLeaveID"]);
	   $leaveType  = trim($request["leaveType"]);
	   $grade = trim($request["grade"]);
	   //dd("$grade");
	   $userID = trim($request['userID']);
	   $deptID = trim($request['deptID']);
	   $hodID = trim($request['hodID']);
	   $year = trim($request['year']);
	   $startdate = trim($request['startdate']);
	   $enddate = trim($request['enddate']);
	   $nod = trim($request['nod']);
	   $comment = trim($request['comment']);
	   $status = "1";
   
	   //$apply = Input::get('apply');
	  
	   //calling the function GetUserDetail()
	   $data['userdetail']=$this->GetUserDetail($u,$p);
	   
	   //calling the function DisplayDetail() to display user information
	   $data['displaydetail']=$this->DisplayDetail($u,$p);
	            
       $from = \Carbon\Carbon::parse($startdate);
	   $to = \Carbon\Carbon::parse($enddate);
	   $days= $to->diffInWeekdays($from);
	   $actdays=$days+1;
	   //dd($actdays);
	   
	   //dd($days);
	   
	   $sumleave=$this->SumUserLeave($u,$p);
	   
	   $data['getleavestatus']=DB::table('tblleave_status')->get();
	   $data['getdept']=DB::table('tbldepartment')->get();
	   $getleaveType=DB::table('tblleave_type')->where('id',$leaveType)->first();
	   if(DB::table('tblgrade_leave_assignment')->where('leavetype',$leaveType)->where('grade',$grade)->count()>0)
	   {
	     $checkleaveDays=DB::table('tblgrade_leave_assignment')->where('leavetype',$leaveType)->where('grade',$grade)->first();
	      
	     //dd($checkleaveDays->noOfDays);
	     $actualLeaveDays=$checkleaveDays->noOfDays-$sumleave;
	   //}
	   //else
	   //{
	   
        	 if( $actdays!=$nod ){
        	 
        	     return back()->with('error_message','No. of Days not correct with Start date and End date of leave. Suggestions: '. $actdays ." days");
        	 
        	 }else{
        
        		   if($actualLeaveDays==0){
        		   
        		    return back()->with('error_message','You have exhausted your leave.');
        		   
        		   }
        		   elseif($actualLeaveDays<$nod){
        		   
        		    return back()->with('error_message','Please select either '.$actualLeaveDays.'days or lesser');
        		   
        		   }
        		 
        		   else{
        		   
        			      
        			      /*
        			       $id_data= DB::table('annual_leave')->where('id',$UserLeaveID)->first();
        			        
        			        DB::table('annual_leave')->insert([
        		
        			          'staffid'         => $id_data->staffid,
        			          'deptid'          => $id_data->deptid,
        			          'year'            => $id_data->year,
        			          'startdate'       => $id_data->startdate,
        			          'enddate'         => $id_data->enddate,
        			          'nod'             => $id_data->nod,
        			          'statusid'        => $id_data->statusid,
        			          'hodstatus'       => $id_data->hodstatus,
        			          'hodcomment'       => $id_data->hodcomment,
        			          'finalapprstatus'  => $id_data->finalapprstatus,
        			          'finalapprcomment' => $id_data->finalapprcomment,
        			          'leavetype'       => $id_data->leavetype,
        			          'datetime'        => $id_data->datetime,
        			          'hodid'           => $id_data->hodid,
        			          'grade'           => $id_data->grade,
        			          'comment'         => $id_data->comment,
        			          'trail'=>'0',
        			
        			        ]);
        			        */
        			      
        			   
        			      DB::table('annual_leave')->where('id',$UserLeaveID)->update([
        		
        			          //'staffid' => $userID,
        			          //'deptid' => $deptID,
        			          'year'    => $year,
        			          'startdate'    => $startdate,
        			          'enddate'         => $enddate,
        			          'nod' => $nod,
        			          'statusid' => '1',
        			          'hodstatus' => '1',
        			          'finalapprstatus' => '1',
        			          //'leavetype' => $getleaveType->leaveType,
        			          'datetime'    => $today->toDateString(),
        			          //'hodID' => $hodID,
        			          //'grade'=>$grade,
        			          'comment'=>$comment,
        			          'record_status'=>'0',
        			          'trail'=>'1',
        			          'reapply' =>'1',
        			          'reapply_status'=>0,
        			
        			        ]);
        			        
        			       
        			      
        			    return back()->with('message','Application submitted successfully!');
        	   	
        	          }
        	   }
	       }
	   
   }
   
   //edit leave
   public function editLeave(Request $request)
   {  
          
        $today = carbon::today();
           
        //getting the user login details
        $u= Auth::user()->username;
        $p= Auth::user()->temp_pass;
           
	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	   $data['RstaffAction'] = "";
	   $data['status'] = "";
	   
	   //getting user input
	   $data['userID'] = trim($request['userID']);
	   $data['deptID'] = trim($request['deptID']);
	   $data['hodID'] = trim($request['hodID']);
	   $data['year'] = trim($request['year']);
	   $data['startdate'] = trim($request['startdate']);
	   $data['enddate'] = trim($request['enddate']);
	   $data['nod'] = trim($request['nod']);
	   $data['status'] = "1";
	   
	   
	   
	   //get and assigned user input
	   $UserLeaveID  = trim($request["UserLeaveID"]);
	   $leaveType  = trim($request["leaveType"]);
	   $grade = trim($request["grade"]);
	   //dd("$grade");
	   $userID = trim($request['userID']);
	   $deptID = trim($request['deptID']);
	   $hodID = trim($request['hodID']);
	   $year = trim($request['year']);
	   $startdate = trim($request['startdate']);
	   $enddate = trim($request['enddate']);
	   $nod = trim($request['nod']);
	   $comment = trim($request['comment']);
	   $status = "1";
   
	   //$apply = Input::get('apply');
	  
	   //calling the function GetUserDetail()
	   $data['userdetail']=$this->GetUserDetail($u,$p);
	   
	   //calling the function DisplayDetail() to display user information
	   $data['displaydetail']=$this->DisplayDetail($u,$p);
	            
       $from = \Carbon\Carbon::parse($startdate);
	   $to = \Carbon\Carbon::parse($enddate);
	   $days= $to->diffInWeekdays($from);
	   $actdays=$days+1;
	   //dd($actdays);
	   
	   //dd($days);
	   
	   $sumleave=$this->SumUserLeave($u,$p);
	   
	   $data['getleavestatus']=DB::table('tblleave_status')->get();
	   $data['getdept']=DB::table('tbldepartment')->get();
	   $getleaveType=DB::table('tblleave_type')->where('id',$leaveType)->first();
	   if(DB::table('tblgrade_leave_assignment')->where('leavetype',$leaveType)->where('grade',$grade)->count()>0)
	   {
	     $checkleaveDays=DB::table('tblgrade_leave_assignment')->where('leavetype',$leaveType)->where('grade',$grade)->first();
	      
	     //dd($checkleaveDays->noOfDays);
	     $actualLeaveDays=$checkleaveDays->noOfDays-$sumleave;
	   //}
	   //else
	   //{
	   
        	 if( $actdays!=$nod ){
        	 
        	     return back()->with('error_message','No. of Days not correct with Start date and End date of leave. Suggestions: '. $actdays ." days");
        	 
        	 }else{
        
        		   if($actualLeaveDays==0){
        		   
        		    return back()->with('error_message','You have exhausted your leave.');
        		   
        		   }
        		   elseif($actualLeaveDays<$nod){
        		   
        		    return back()->with('error_message','Please select either '.$actualLeaveDays.'days or lesser');
        		   
        		   }
        		 
        		   else{
        		   
        			      DB::table('annual_leave')->where('id',$UserLeaveID)->update([
        		
        			          //'staffid' => $userID,
        			          //'deptid' => $deptID,
        			          'year'    => $year,
        			          'startdate'    => $startdate,
        			          'enddate'         => $enddate,
        			          'nod' => $nod,
        			          //'statusid' => '1',
        			          //'hodstatus' => '1',
        			          //'finalapprstatus' => '1',
        			          //'leavetype' => $getleaveType->leaveType,
        			          //'datetime'    => $today->toDateString(),
        			          //'hodID' => $hodID,
        			          //'grade'=>$grade,
        			          'comment'=>$comment,
        			          //'record_status'=>'0',
        			          //'trail'=>'1',
        			
        			        ]);
        			        
        			       
        			      
        			    return back()->with('message','Application edited successfully!');
        	   	
        	          }
        	   }
	       }
	   
   }
   
  //hod approval
  public function HodApproval(Request $request)
   {  
       $today = carbon::today();
           
       //getting the user login details
       $u= Auth::user()->username;
       $p= Auth::user()->temp_pass;
              
	   //getting user input
	   $data['userID'] = trim($request['userID']);
	   $data['deptID'] = trim($request['deptID']);
	   $data['year'] = trim($request['year']);
	   $data['startdate'] = trim($request['startdate']);
	   $data['enddate'] = trim($request['enddate']);
	   $data['nod'] = trim($request['nod']);
	   $data['status'] = "1";
	   $data['leaveType'] = DB::table('tblleave_type')->where('leaveType','Annual')->first();
	   $leaveType = DB::table('tblleave_type')->where('leaveType','Annual')->first();

	   $apply = Input::get('apply');
	  
	   //calling the function GetUserDetail()
	   $data['userdetail']=$this->GetUserDetail($u,$p);
	   $checkstaff=$this->GetUserDetail($u,$p);
	   
	   //dd($f);
	   
	   //calling the function DisplayDetail() to display single user information
	   $data['displaydetail']=$this->DisplayDetail($u,$p);
	   $getUserGrade=$this->GetUserGrade($u,$p);
	   $data['sumleave']=$this->SumUserLeave($u,$p);
	   
	   //dd($data['sumleave']);
	   	   	   
	   $data['getleavestatus']=DB::table('tblleave_status')->get();
	   $data['getdept']=DB::table('tbldepartment')->get();
	   
	   if($data['leavedays']=DB::table('tblgrade_leave_assignment')->where('leavetype',$leaveType->id)->where('grade',$getUserGrade->grade)->count()>0)
	    {
	   //getting the head field of tbldepartment
	   
	   $depthead=0;
	   
	   if($checkNonStaff=DB::table('tbldepartment')->where('id','=',$checkstaff->department)->count()>0)
	   {
	      //dd($checkstaff->department);
	     if( $head=DB::table('tbldepartment')->where('head',$checkstaff->UID)->count()>0 )
	      {
    		 
    		   $data['staffdata']=DB::table('tblper')
    		   ->select('UserID','surname','first_name','othernames')
    		   ->get();
    		   
    		   $data['displaystaffdetail']=$this->DisplayDetailForHead($checkstaff->department);
    		   $data['displaystaffdetail_All']=$this->DisplayDetailForHeadAll($checkstaff->department);	   	
    		   
    		   return view('Leave.annualleaveapproval', $data);
    		  
	       }
	       else
	       {
	            return view('Leave.nonHead', $data);
	       }
	       /*
	       else
           {
              //$data['staffdata']=DB::table('tblper')
    		   //->select('UserID','surname','first_name','othernames')
    		   //->get();
    		   
    		   //$data['displaystaffdetail']=$this->DisplayDetailForHead($u,$p);
    		  $data['leavedays']=DB::table('tblgrade_leave_assignment')->where('leavetype',$leaveType->id)->where('grade',$getUserGrade->grade)->first();
    		  //$data['userdetail']=$this->GetUserDetail($u,$p);
    		  $data['userdetail']=$this->GetUserDetail($u,$p);
        		    //dd('Whats really happening?');
              return view('Leave.annualleaveapplication', $data);
          }
          */
	      
	   }
	   else
	   {
	   	//dd('None staff');
	    return view('Leave.nonStaffPage', $data);
	   	
	   }
	 }
	 else
	   {
	       echo "<script>alert('User grade not yet assigned ')</script>";
	       return redirect('/');
	   }

   }
   
  //final approval admin
   public function FinalApproval(Request $request)
   {  
       
       $today = carbon::today();
           
       //getting the user login details
       $u= Auth::user()->username;
       $p= Auth::user()->temp_pass;
              
	   //getting user input
	   $data['userID'] = trim($request['userID']);
	   $data['deptID'] = trim($request['deptID']);
	   $data['year'] = trim($request['year']);
	   $data['startdate'] = trim($request['startdate']);
	   $data['enddate'] = trim($request['enddate']);
	   $data['nod'] = trim($request['nod']);
	   $data['status'] = "1";
	   $data['leaveType'] = DB::table('tblleave_type')->where('leaveType','Annual')->first();
	   $leaveType = DB::table('tblleave_type')->where('leaveType','Annual')->first();
       
	   $apply = Input::get('apply');
	  
	   //calling the function GetUserDetail()
	   $data['userdetail']=$this->GetUserDetail($u,$p);
	   $checkstaff=$this->GetUserDetail($u,$p);
	   //dd($f);
       	   
	   //calling the function DisplayDetail() to display single user information
	   $data['displaydetail']=$this->DisplayDetail($u,$p);
	   $getUserGrade=$this->GetUserGrade($u,$p);
	   $data['sumleave']=$this->SumUserLeave($u,$p);
	   
	   //dd($data['sumleave']);
	   $data['getleavestatus']=DB::table('tblleave_status')->get();
	   $data['getdept']=DB::table('tbldepartment')->get();
	   
	   if($data['leavedays']=DB::table('tblgrade_leave_assignment')->where('leavetype',$leaveType->id)->where('grade',$getUserGrade->grade)->count()>0)
	    {
	   
    	   if($checkNonStaff=DB::table('tbldepartment')->where('id','=',$checkstaff->department)->count()>0)
    	   {
    	      //dd('staff');
    	     if( $head=DB::table('tbldepartment')->where('head',$checkstaff->UID)->count()>0 )
    	      {
        		 
        		   $data['staffdata']=DB::table('tblper')
        		   ->select('UserID','surname','first_name','othernames')
        		   ->get();
        		   
        		   $data['finalapproval']=$this->FinalApprovalAdmin();
        		   $data['finalapproval_all']=$this->FinalApprovalAdminAll();
        		   return view('Leave.annualleavefinalapproval', $data);
        		  
    	       }
    	       else
    	       {
    	           return view('Leave.nonHead', $data);
    	       }
    	       
    	       /*
    	       else
               {
                  //$data['staffdata']=DB::table('tblper')
        		  //->select('UserID','surname','first_name','othernames')
        		  //->get();
        		   
        		  //$data['displaystaffdetail']=$this->DisplayDetailForHead($u,$p);
        		  $data['leavedays']=DB::table('tblgrade_leave_assignment')->where('leavetype',$leaveType->id)->where('grade',$getUserGrade->grade)->first();
        		  //$data['userdetail']=$this->GetUserDetail($u,$p);
        		  $data['userdetail']=$this->GetUserDetail($u,$p);
            		    //dd('Whats really happening?');
                  return view('Leave.annualleaveapplication', $data);
              }
              */
    	   }
    	   else
    	   {
        	   	//dd('None staff');
        	    return view('Leave.nonStaffPage', $data);
    	   	
    	   }
	 }
	 else
	   {
	       echo "<script>alert('User grade not yet assigned ')</script>";
	       return redirect('/');
	   }
	 
   }
   
    //final approval executive secretary (ES)
   public function FinalApproval_ES(Request $request)
   {  
       
       $today = carbon::today();
           
       //getting the user login details
       $u= Auth::user()->username;
       $p= Auth::user()->temp_pass;
              
	   //getting user input
	   $data['userID'] = trim($request['userID']);
	   $data['deptID'] = trim($request['deptID']);
	   $data['year'] = trim($request['year']);
	   $data['startdate'] = trim($request['startdate']);
	   $data['enddate'] = trim($request['enddate']);
	   $data['nod'] = trim($request['nod']);
	   $data['status'] = "1";
	   $data['leaveType'] = DB::table('tblleave_type')->where('leaveType','Annual')->first();
	   $leaveType = DB::table('tblleave_type')->where('leaveType','Annual')->first();
       
	   $apply = Input::get('apply');
	  
	   //calling the function GetUserDetail()
	   $data['userdetail']=$this->GetUserDetail($u,$p);
	   $checkstaff=$this->GetUserDetail($u,$p);
	   //dd($f);
       	   
	   //calling the function DisplayDetail() to display single user information
	   $data['displaydetail']=$this->DisplayDetail($u,$p);
	   $getUserGrade=$this->GetUserGrade($u,$p);
	   $data['sumleave']=$this->SumUserLeave($u,$p);
	   
	   //dd($data['sumleave']);
	   $data['getleavestatus']=DB::table('tblleave_status')->get();
	   $data['getdept']=DB::table('tbldepartment')->get();
	   
	   if($data['leavedays']=DB::table('tblgrade_leave_assignment')->where('leavetype',$leaveType->id)->where('grade',$getUserGrade->grade)->count()>0)
	    {
	   
    	   if($checkNonStaff=DB::table('tbldepartment')->where('id','=',$checkstaff->department)->count()>0)
    	   {
    	      //dd('staff');
    	     if( $head=DB::table('tbldepartment')->where('head',$checkstaff->UID)->count()>0 )
    	      {
        		 
        		   $data['staffdata']=DB::table('tblper')
        		   ->select('UserID','surname','first_name','othernames')
        		   ->get();
        		   
        		   
        		   $data['finalapproval']=$this->FinalApprovalES();
        		   $data['finalapproval_all']=$this->FinalApprovalESAll();
        		  
        		   return view('Leave.annualleavefinalapproval_ES', $data);
        		  
    	       }
    	       else
    	       {
    	           return view('Leave.nonHead', $data);
    	       }
    	       
    	       /*
    	       else
               {
                  //$data['staffdata']=DB::table('tblper')
        		  //->select('UserID','surname','first_name','othernames')
        		  //->get();
        		   
        		  //$data['displaystaffdetail']=$this->DisplayDetailForHead($u,$p);
        		  $data['leavedays']=DB::table('tblgrade_leave_assignment')->where('leavetype',$leaveType->id)->where('grade',$getUserGrade->grade)->first();
        		  //$data['userdetail']=$this->GetUserDetail($u,$p);
        		  $data['userdetail']=$this->GetUserDetail($u,$p);
            		    //dd('Whats really happening?');
                  return view('Leave.annualleaveapplication', $data);
              }
              */
    	   }
    	   else
    	   {
        	   	//dd('None staff');
        	    return view('Leave.nonStaffPage', $data);
    	   	
    	   }
	 }
	 else
	   {
	       echo "<script>alert('User grade not yet assigned ')</script>";
	       return redirect('/');
	   }
	 
   }

//hod approval
public function RecommendLeave(Request $request)
{
    
    $this->validate($request,[
      
      'ApprovalComment'     =>  'required|string',
      'id'                  =>  'required|numeric',
      'userID'              =>  'required|numeric',
      
      ]);
      
    $leaveid=$request["id"];
    $recommend=$request["ApprovalComment"];
    $userID=$request["userID"];
    
    $update=DB::table('annual_leave')->where('id',$leaveid)->update(['statusid' => '2','hodstatus' => '2','hodcomment' => $recommend, 'finalapprstatus' => '1','reapply' =>'0','approve' =>'1']);
    DB::table('annual_leave_comments')->insert(['leaveID' => $leaveid,'userID' => $userID,'hodcomments' => $recommend]);
  
    return back()->with('message','Leave Recommended for approval!');

}

public function RejectLeave(Request $request)
{
    
    $this->validate($request,[
      
      'ReasonForRejection'  =>  'required|string',
      'id'                  =>  'required|numeric',
      'userID'              =>  'required|numeric',
      ]);
      
    $leaveid=$request["id"];
    $reject=$request["ReasonForRejection"];
    $userID=$request["userID"];
    
    $update=DB::table('annual_leave')->where('id',$leaveid)->update(['statusid' => '4','hodstatus' => '4','hodcomment' => $reject,'reapply' =>'0','hodreply' => '0']);
    DB::table('annual_leave_comments')->insert(['leaveID' => $leaveid,'userID' => $userID,'hodcomments' => $reject,'applicant' => 1]);
    DB::table('annual_leave')->where('id',$leaveid)->update(['reapply_status' => 1]);
   
    return back()->with('message','Leave Rejected!');

}

    public function CancelLeave(Request $request)
    {
        
        $this->validate($request,[
          
          'ReasonForCancellation'  =>  'required|string',
          'id'                     =>  'required|numeric',
          'userID'                 =>  'required|numeric',
          ]);
          
        $leaveid=$request["id"];
        $cancel=$request["ReasonForCancellation"];
        $userID=$request["userID"];
      
        $update=DB::table('annual_leave')->where('id',$leaveid)->update(['statusid' => '3','hodstatus' => '3','hodcomment' => $cancel,'reapply' =>'0']);
        DB::table('annual_leave_comments')->insert(['leaveID' => $leaveid,'userID' => $userID,'hodcomments' => $cancel,'applicant' => 1]);
      
        return back()->with('message','Leave Cancel!');
    
    }

   //processing json for notifying staff
     public function dontnotifyStaff(Request $request)
    {
      $recordId = Input::get('applicationid');
      
      $data = DB::table('annual_leave')->where('id','=',$recordId)->update(['reapply_status'=>0]);
      return response()->json($data); 
    }
    public function notifyStaff(Request $request)
    {
      $recordId = Input::get('applicationid');
      
      $data = DB::table('annual_leave')->where('id','=',$recordId)->update(['reapply_status'=>1]);
      return response()->json($data); 
    }
    
    public function notifyApplicant(Request $request)
    {
      
    $this->validate($request,[
      
      'ApprovalComment'     =>  'required|string',
      'id'                  =>  'required|numeric',
      'userID'              =>  'required|numeric',
      
      ]);
      
    $leaveid=$request["id"];
    $recommend=$request["ApprovalComment"];
    $userID=$request["userID"];
    
    $update=DB::table('annual_leave')->where('id',$leaveid)->update(['reapply_status' => '1','hodreply' => '1']);
    DB::table('annual_leave_comments')->insert(['leaveID' => $leaveid,'userID' => $userID,'hodcomments' => $recommend,'applicant' => 1]);
  
    return back()->with('message','Comment Posted!');

    }
    
    public function notifyAdmin(Request $request)
    {
      
    $this->validate($request,[
      
      'ApprovalComment'     =>  'required|string',
      'id'                  =>  'required|numeric',
      'userID'              =>  'required|numeric',
      
      ]);
      
    $leaveid=$request["id"];
    $recommend=$request["ApprovalComment"];
    $userID=$request["userID"];
    
    $update=DB::table('annual_leave')->where('id',$leaveid)->update(['hodreply' => '1','approve' => '1']);
    DB::table('annual_leave_comments')->insert(['leaveID' => $leaveid,'userID' => $userID,'hodcomments' => $recommend,'es_reply' => 1]);
  
    return back()->with('message','Comment Posted!');

    }
    
    //applicant view hod reply json
   public function viewHODReply(Request $request)
    {
      $MId = Input::get('appID');
      $data = DB::table('annual_leave_comments')
      ->leftjoin('annual_leave', 'annual_leave_comments.leaveID', '=', 'annual_leave.id')
      ->leftjoin('users', 'annual_leave_comments.userID', '=', 'users.id')
      ->where('annual_leave_comments.leaveID', '=',$MId)
      ->where('annual_leave_comments.hodcomments', '<>',null)
      ->where('annual_leave_comments.applicant', '=',1)
       ->orderby('annual_leave_comments.id','desc')
      ->get();
      //dd($data);
     return response()->json($data); 
    }
    
    //SYNC HOD-APPLICANT DISCUSSION
 public function viewCommentHODES(Request $request)
    {
      $MId = Input::get('appID');
      $data = DB::table('annual_leave_comments')
      ->leftjoin('annual_leave', 'annual_leave_comments.leaveID', '=', 'annual_leave.id')
      ->leftjoin('users', 'annual_leave_comments.userID', '=', 'users.id')
      ->where('annual_leave_comments.leaveID', '=',$MId)
      ->where('annual_leave_comments.hodcomments', '<>',null)
      //->where('annual_leave_comments.applicant', '=',1)
      ->orderby('annual_leave_comments.id','desc')
      ->get();
      //dd($data);
     return response()->json($data); 
    }
     //hod view comments json
   public function viewComment(Request $request)
    {
      $MId = Input::get('appID');
      $data = DB::table('annual_leave_comments')
      ->leftjoin('annual_leave', 'annual_leave_comments.leaveID', '=', 'annual_leave.id')
      ->leftjoin('users', 'annual_leave_comments.userID', '=', 'users.id')
      ->where('annual_leave_comments.leaveID', '=',$MId)
      ->where('annual_leave_comments.hodcomments', '<>',null)
      ->where('annual_leave_comments.applicant', '=',0)
      ->orderby('annual_leave_comments.id','desc')
      ->get();
      //dd($data);
     return response()->json($data); 
    }
    
    public function viewComment2(Request $request)
    {
      $MId = Input::get('appID');
      $data = DB::table('annual_leave_comments')
      ->leftjoin('annual_leave', 'annual_leave_comments.leaveID', '=', 'annual_leave.id')
      ->leftjoin('users', 'annual_leave_comments.userID', '=', 'users.id')
      ->where('annual_leave_comments.leaveID', '=',$MId)
      ->where('annual_leave_comments.applicant', '=',0)
      //->where('annual_leave_comments.admincomments', '!=',null)
      ->orderby('annual_leave_comments.id','desc')
      ->get();
      //dd($data);
     return response()->json($data); 
    }
    
     public function viewComment3(Request $request)
    {
      $MId = Input::get('appID');
      $data = DB::table('annual_leave_comments')
      ->leftjoin('annual_leave', 'annual_leave_comments.leaveID', '=', 'annual_leave.id')
      ->leftjoin('users', 'annual_leave_comments.userID', '=', 'users.id')
      ->where('annual_leave_comments.leaveID', '=',$MId)
      ->where('annual_leave_comments.applicant', '=',0)
      ->where('annual_leave_comments.admincomments', '!=',null)
      ->orderby('annual_leave_comments.id','desc')
      ->get();
      //dd($data);
     return response()->json($data); 
    }
    
    public function viewCommente(Request $request)
    {
      $MId = Input::get('appID');
      $data = DB::table('annual_leave_comments')
      ->leftjoin('annual_leave', 'annual_leave_comments.leaveID', '=', 'annual_leave.id')
      ->leftjoin('users', 'annual_leave_comments.userID', '=', 'users.id')
      ->where('annual_leave_comments.leaveID', '=',$MId)
      ->where('annual_leave_comments.escomments', '<>',null)
      ->where('annual_leave_comments.applicant', '=',0)
      ->orderby('annual_leave_comments.id','desc')
      ->get();
      //dd($data);
     return response()->json($data); 
    }
    

//final approval by admin
public function FinalApproveLeave(Request $request)
{
    
    $this->validate($request,[
      
      'ApprovalComment'     =>  'required|string',
      'id'                  =>  'required|numeric',
      'userID'              =>  'required|numeric',
      
      ]);
      
    $leaveid=$request["id"];
    $approve=$request["ApprovalComment"];
    $userID=$request["userID"];
    
    $update=DB::table('annual_leave')->where('id',$leaveid)->update(['statusid' => '2','finalapprstatus' => '2','finalapprcomment' => $approve,'hodreply' => '1','approve' => '0','submit_application' =>'0']);
    DB::table('annual_leave_comments')->insert(['leaveID' => $leaveid,'userID' => $userID,'admincomments' => $approve,'status' =>1]);
    
    return back()->with('message','Leave Approved!');

}

public function FinalRejectLeave(Request $request)
{
    
    $this->validate($request,[
      
      'ReasonForRejection'  =>  'required|string',
      'id'                  =>  'required|numeric',
      'userID'              =>  'required|numeric',
      ]);
      
    $leaveid=$request["id"];
    $reject=$request["ReasonForRejection"];
    $userID=$request["userID"];
     
    $update=DB::table('annual_leave')->where('id',$leaveid)->update(['statusid' => '4','finalapprstatus' => '4','finalapprcomment' => $reject,'hodreply' => '0','approve' => 0]);
    DB::table('annual_leave_comments')->insert(['leaveID' => $leaveid,'userID' => $userID,'admincomments' => $reject,'status' =>1]);
    
    return back()->with('message','Leave Rejected!');

}

public function FinalCancelLeave(Request $request)
{
    
    $this->validate($request,[
      
      'ReasonForCancellation'   =>  'required|string',
      'id'                      =>  'required|numeric',
      'userID'                  =>  'required|numeric',
      ]);
      
  $leaveid=$request["id"];
  $cancel=$request["ReasonForCancellation"];
  $userID=$request["userID"];
  
  $update=DB::table('annual_leave')->where('id',$leaveid)->update(['statusid' => '3','finalapprstatus' => '3','finalapprcomment' => $cancel,'hodreply' => '0']);
  DB::table('annual_leave_comments')->insert(['leaveID' => $leaveid,'userID' => $userID,'admincomments' => $cancel,'status' =>1]);
 
  return back()->with('message','Leave Cancel!');

}

//final approval by Executive Secretary (ES)
public function FinalApproveLeaveES(Request $request)
{
    
    $this->validate($request,[
      
      'ApprovalComment'     =>  'required|string',
      'id'                  =>  'required|numeric',
      'userID'              =>  'required|numeric',
      
      ]);
      
    $leaveid=$request["id"];
    $approve=$request["ApprovalComment"];
    $userID=$request["userID"];
    
    $update=DB::table('annual_leave')->where('id',$leaveid)->update(['statusid' => '2','finalapprstatus' => '2','finalapprcomment' => $approve,'hodreply' => '1','approve' => '0','submit_application' =>'0']);
    DB::table('annual_leave_comments')->insert(['leaveID' => $leaveid,'userID' => $userID,'escomments' => $approve,'status' =>0]);
    
    return back()->with('message','Leave Approved!');

}

public function FinalRejectLeaveES(Request $request)
{
    
    $this->validate($request,[
      
      'ReasonForRejection'  =>  'required|string',
      'id'                  =>  'required|numeric',
      'userID'              =>  'required|numeric',
      ]);
      
    $leaveid=$request["id"];
    $reject=$request["ReasonForRejection"];
    $userID=$request["userID"];
     
    $update=DB::table('annual_leave')->where('id',$leaveid)->update(['statusid' => '4','finalapprstatus' => '4','finalapprcomment' => $reject,'hodreply' => '0','approve' => 0]);
    DB::table('annual_leave_comments')->insert(['leaveID' => $leaveid,'userID' => $userID,'escomments' => $reject,'status' =>0]);
    //DB::table('annual_leave')->where('id','=',$leaveid)->update(['approve' => 0]);
    
    return back()->with('message','Leave Rejected!');

}

public function FinalCancelLeaveES(Request $request)
{
    
    $this->validate($request,[
      
      'ReasonForCancellation'   =>  'required|string',
      'id'                      =>  'required|numeric',
      'userID'                  =>  'required|numeric',
      ]);
      
  $leaveid=$request["id"];
  $cancel=$request["ReasonForCancellation"];
  $userID=$request["userID"];
  
  $update=DB::table('annual_leave')->where('id',$leaveid)->update(['statusid' => '3','finalapprstatus' => '3','finalapprcomment' => $cancel,'hodreply' => '0']);
  DB::table('annual_leave_comments')->insert(['leaveID' => $leaveid,'userID' => $userID,'escomments' => $cancel,'status' =>0]);
  DB::table('annual_leave')->where('id','=',$leaveid)->update(['approve' => 0]);
  
  return back()->with('message','Leave Cancel!');

}

//remove leave application by applicant
public function RemoveApplication(Request $request)
{
  $leaveid=$request["id"];
  $update=DB::table('annual_leave')->where('id',$leaveid)->update(['remove' => '0']);
  
  return back()->with('message','Application Remove!');

}
public function ReleaveResponse(Request $request)
   {  
	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	   $data['leaveid'] = trim($request['leaveid']);
	   $data['remarks'] = trim($request['remarks']);
	   $remarks= trim($request['remarks']);
	   $leaveid=trim($request['leaveid']);
	   $leaveData=$this->AppliedLeave($leaveid);
	   if ($leaveData){
	   if ( isset( $_POST['accept'] ) ) {
	   DB::UPDATE ("UPDATE `tblstaff_leave` SET `RstaffAction`='Accept', `RStaffComment`='$remarks' where `id`='$leaveid'");
	   DB::UPDATE ("UPDATE `tblnotification` SET `status`='Complete' WHERE `url`='/self-service/releaveaction' and `actionID`='$leaveid'");
	   }
	   if ( isset( $_POST['reject'] ) ) {
	   DB::UPDATE ("UPDATE `tblstaff_leave` SET `RstaffAction`='Reject', `RStaffComment`='$remarks' where `id`='$leaveid'");
	   DB::UPDATE ("UPDATE `tblnotification` SET `status`='Complete' WHERE `url`='/self-service/releaveaction' and `actionID`='$leaveid'");
	   }
	   $data['AppliedLeave'] = $this->AppliedLeave($leaveid);
	   return view('Leave.reaveacceptance', $data);
	   }
	   else
	   {
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
	   $remarks= trim($request['remarks']);
	   $leaveid=trim($request['leaveid']);
	   $staffid=$this->StaffID($this->username);
	   $leaveData=$this->AppliedLeave($leaveid);
	   if ($leaveData){
	   if ( isset( $_POST['accept'] ) ) {
	   DB::UPDATE ("UPDATE `tblstaff_leave` SET `status`='Approved', `approvalComment`='$remarks',`approvedBy`='$staffid' where `id`='$leaveid'");
	   }
	   if ( isset( $_POST['reject'] ) ) {
	   DB::UPDATE ("UPDATE `tblstaff_leave` SET `status`='Reject', `approvalComment`='$remarks',`approvedBy`='$staffid' where `id`='$leaveid'");
	   }
	   $data['AppliedLeave'] = $this->AppliedLeave($leaveid);
	   return view('Leave.leaveapproval', $data);
	   }
	   else
	   {
	   return redirect('/self-service/notification');
	   }
	   

	   
   } 
   public function LeaveQuery(Request $request)
   {  
	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	   $court= trim($request['court']);
	   if ($court==""){$court=$this->StaffCourt($this->username);}
	   $period= trim($request['period']);
	   if ($period==""){$period=$this->LeavePeriod();}
	   $data['court'] = $court;
	   $data['period'] = $period;
	   $division= trim($request['division']);
	   $data['division'] = $division;
	   $status= trim($request['status']);
	   $data['status'] = $status;
	   $department = trim($request['department']);
	   $data['department']=$department ;
	   $data['courtList']=$this->CourtList();
	   $data['depatmentList']=$this->DepartmentList($court);
	   $data['Division']=$this->DivisionList($court);
	   $data['LeaveStatus']=$this->LeaveStatus();
	   $data['LeavePeriod']=$this->LeavePeriodList();
	   
	   //die(json_encode($this->LeavesQuery($period,$court,$division,$department,$status)));
	   $data['LeaveQuery']=$this->LeavesQuery($period,$court,$division,$department,$status);
	   
	   return view('Leave.leavequery', $data);

	   
   } 
  

}