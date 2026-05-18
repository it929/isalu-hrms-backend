<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use DB;
use Auth;
use Session;

class LeaveApplicantsController extends Controller
{
    
    public function index()
    {
        $data['stage'] = '';
        $data['next'] = '';
        $stage = DB::table('tblaction_stages')->where('userID','=',Auth::user()->id)->first();
        //dd($stage);
        if(!empty($stage))
        {
            $data['stage'] = $stage->action_stageID;
           
        }
        if(empty($stage))
        {
            $stageVal = 0;
            $dept = 0;
        }
        else{
            $stageVal = $stage->action_stageID;
            $dept = $stage->departmentID;
            if($stageVal == 1)
            {
                $data['next'] = 'Registry';
            }
            
            elseif($stageVal == 2)
            {
                $data['next'] = 'Director Admin';
            }
            elseif($stageVal == 3)
            {
                $data['next'] = 'Assistant Director Admin';
            }
            elseif($stageVal == 4)
            {
                $data['next'] = 'Leave Matters';
            }

        }
        $year = date('Y');
        $adminHeadAvailability = DB::table('head_admin_available')->first();
        if($stageVal == 1)
        {
            $data['leave'] = DB::table('tblstaff_leave')
            ->join('tblper','tblper.ID','=','tblstaff_leave.staffid')
            ->join('tblleave_type','tblleave_type.id','=','tblstaff_leave.leaveType')
            ->select('*','tblstaff_leave.id as leaveAppID')
            ->where('period','=',$year)
            ->where('tblstaff_leave.approval_stages_status','=',1)
            ->where('tblstaff_leave.department','=',$dept)
            ->where('tblper.grade','<',7)
            ->where('tblstaff_leave.resumption_status','=',0)
            ->get();
            return view('Leave.allApplications',$data);
        }
        elseif($stageVal == 3 && $adminHeadAvailability->is_available ==0)
        {
        $data['leave'] = DB::table('tblstaff_leave')
        ->join('tblper','tblper.ID','=','tblstaff_leave.staffid')
        ->join('tblleave_type','tblleave_type.id','=','tblstaff_leave.leaveType')
        ->select('*','tblstaff_leave.id as leaveAppID')
        ->where('period','=',$year)
        ->where('tblstaff_leave.resumption_status','=',0)
        //->where('tblper.grade','<',7)
        ->where('tblstaff_leave.approval_stages_status','=',3)
        ->orWhere('tblstaff_leave.approval_stages_status','=',4)
        ->get();
        return view('Leave.allApplications',$data);
        }
        else{
        $data['leave'] = DB::table('tblstaff_leave')
        ->join('tblper','tblper.ID','=','tblstaff_leave.staffid')
        ->join('tblleave_type','tblleave_type.id','=','tblstaff_leave.leaveType')
        ->select('*','tblstaff_leave.id as leaveAppID')
        ->where('period','=',$year)
        ->where('tblstaff_leave.resumption_status','=',0)
        //->where('tblper.grade','<',7)
        ->where('tblstaff_leave.approval_stages_status','=',$stageVal)
        ->get();

        return view('Leave.allApplications',$data);
        }
        
      
    }

    public function show()
    {
        /**
            **Getting all leave applications made to the head of department
        **/

        //getting the logged in user ID
        $userId = Auth::user()->id;

        //getting the user details using the logged in user id
        $userDetails =  DB::table('tblper')->where('UserID', $userId)->first();
        $id = $userDetails->ID;
        $department = DB::table('tbldepartment')->where('head', $id)->first();
        
        if($department)
        {
            $departmentId = $department->id;
            $leaveRoasters = DB::table('tblroaster')->where('department_id', $departmentId)->where('is_submitted', 1)->get();

            if($leaveRoasters)
            {
                return view('Leave.departmentLeaveRoaster', compact('leaveRoasters','department'));
            }
        }
        else
        {
            return redirect()->back();
        }
        
    }

    public function approveLeave2(Request $request)
    {
        $roasterId = $request->roasterID;
        $approvedStatus =  DB::table('tblroaster')->where('roasterID', $roasterId)->update(['is_approved' => 1] );

        if($approvedStatus)
        {
            return redirect()->back()->with('message', 'The leave application was approved successfully');
        }
    }

    public function reverse2(Request $request)
    {
        $roasterId = $request->roasterID;
        $approvedStatus =  DB::table('tblroaster')->where('roasterID', $roasterId)->update(['is_approved' => 2] );
        if($approvedStatus)
        {
            return redirect()->back()->with('message', 'The leave application was reverted successfully');
        }
    }

    
    public function approveLeave(Request $request)
    {
        if($request->status == "approve")
        {
        $data['leave'] = DB::table('leave_comments')->insert([
            'leaveID'        => $request->leaveID,
            'comments'        => $request->comment,
            'userID'         => Auth::user()->id,
            'stage'          => $request->stage,
            'status'         => 1,
        ]);
        
        DB::table('tblstaff_leave')->where('id','=',$request->leaveID)->update([
            'approval_status'        => 1,
        ]);
        return back()->with('message','Successfull');
       }
       elseif($request->status == "reject")
        {
        $data['leave'] = DB::table('leave_comments')->insert([
            'leaveID'        => $request->leaveID,
            'comments'        => $request->comment,
            'userID'         => Auth::user()->id,
            'stage'          => $request->stage,
            'status'         => 1,
        ]);
        
        DB::table('tblstaff_leave')->where('id','=',$request->leaveID)->update([
            'approval_status'        => 2,
            
        ]);
        return back()->with('message','Successfull');
        }
    }
    public function moveToNext(Request $request)
    {
        if(!empty($request->comment))
        {
        $data['leave'] = DB::table('leave_comments')->insert([
            'leaveID'        => $request->leaveID,
            'comments'        => $request->comment,
            'userID'         => Auth::user()->id,
            'stage'          => $request->stage,
            'status'         => 1,
        ]);
        
       }
       
        if($request->stage == 1)
        {
            $nextstage = 2;
        }
        elseif($request->stage == 2)
        {
            $nextstage = 3;
        }
        elseif($request->stage == 3)
        {
            $nextstage = 4;
        }
        elseif($request->stage == 4)
        {
            $nextstage = 5;
        }
        
        DB::table('tblstaff_leave')->where('id','=',$request->leaveID)->update([
            'approval_stages_status'        => $nextstage,
            
        ]);
        return back()->with('message','Successfull');
    }

    
    public function reverse(Request $request)
    {
        DB::table('tblstaff_leave')->where('id','=',$request->leaveID)->update([
            'approval_status'        => 0,
            
        ]);
        DB::table('leave_comments')->where('leaveID','=',$request->leaveID)->where('userID',"=",Auth::user()->id)->delete();
        return back()->with('message','Successfully Reversed');
    }

    
    public function seniorStaff()
    {
        $data['stage'] = '';
        $data['next'] ='';
        $stage = DB::table('tblaction_stages')->where('userID','=',Auth::user()->id)->first();
        if(!empty($stage))
        {
            $data['stage'] = $stage->action_stageID;
           
        }
        if(empty($stage))
        {
            $stageVal = 0;
            $dept = 0;
        }
        else{
            $stageVal = $stage->action_stageID;
            $dept = $stage->departmentID;
            if($stageVal == 1)
            {
                $data['next'] = 'Registry';
            }
            elseif($stageVal == 2)
            {
                $data['next'] = 'Executive Secretary';
            }
            elseif($stageVal == 6)
            {
                $data['next'] = 'Director Admin';
            }
            
        }
        $year = date('Y');
        $adminHeadAvailability = DB::table('head_admin_available')->first();
        if($stageVal == 1)
        {
            $data['leave'] = DB::table('tblstaff_leave')
            ->join('tblper','tblper.ID','=','tblstaff_leave.staffid')
            ->join('tblleave_type','tblleave_type.id','=','tblstaff_leave.leaveType')
            ->select('*','tblstaff_leave.id as leaveAppID')
            ->where('period','=',$year)
            ->where('tblstaff_leave.approval_stages_status','=',1)
            ->where('tblstaff_leave.department','=',$dept)
            ->where('tblper.grade','>',6)
            ->get();
            return view('Leave.seniorStaffApplications',$data);
        }

       /* elseif($stageVal == 2)
        {
            $data['leave'] = DB::table('tblstaff_leave')
            ->join('tblper','tblper.ID','=','tblstaff_leave.staffid')
            ->join('tblleave_type','tblleave_type.id','=','tblstaff_leave.leaveType')
            ->select('*','tblstaff_leave.id as leaveAppID')
            ->where('period','=',$year)
            ->where('tblstaff_leave.approval_stages_status','=',2)
           
            ->where('tblper.grade','>',6)
            ->get();
            return view('Leave.seniorStaffApplications',$data);
        }*/
        
        else{
        $data['leave'] = DB::table('tblstaff_leave')
        ->join('tblper','tblper.ID','=','tblstaff_leave.staffid')
        ->join('tblleave_type','tblleave_type.id','=','tblstaff_leave.leaveType')
        ->select('*','tblstaff_leave.id as leaveAppID')
        ->where('period','=',$year)
        ->where('tblper.grade','>',6)
        ->where('tblstaff_leave.approval_stages_status','=',$stageVal)
        ->get();
        return view('Leave.seniorStaffApplications',$data);
        }
        
       
    }

    public function moveToNextSeniorStaff(Request $request)
    {
        /*$data['leave'] = DB::table('leave_comments')->insert([
            'leaveID'        => $request->leaveID,
            'comments'        => $request->comment,
            'userID'         => Auth::user()->id,
            'stage'          => $request->stage,
            'status'         => 1,
        ]);
        */
       
        if($request->stage == 1)
        {
            $nextstage = 2;
        }
        elseif($request->stage == 2)
        {
            $nextstage = 6;
        }
        elseif($request->stage == 6)
        {
            $nextstage = 3;
        }
       
        
        DB::table('tblstaff_leave')->where('id','=',$request->leaveID)->update([
            'approval_stages_status'        => $nextstage,
            
        ]);
        return back()->with('message','Successfull');
    }
   
    public function leaveReport()
    {
        $year = date('Y');
        $data['leave'] = DB::table('tblstaff_leave')
        ->join('tblper','tblper.ID','=','tblstaff_leave.staffid')
        ->join('tblleave_type','tblleave_type.id','=','tblstaff_leave.leaveType')
        ->select('*','tblstaff_leave.id as leaveAppID')
        ->where('period','=',$year)
        ->where('tblstaff_leave.approval_status','=',1)
        ->where('tblstaff_leave.memo_status','=',1)
        ->get();
        $data['staff'] = DB::table('tblper')->where('staff_status','=',1)->get();
        return view('Leave.leaveReport',$data);
    }
    public function leaveReportSearch(Request $request)
    {
        $year = date('Y');
        $data['leave'] = DB::table('tblstaff_leave')
        ->join('tblper','tblper.ID','=','tblstaff_leave.staffid')
        ->join('tblleave_type','tblleave_type.id','=','tblstaff_leave.leaveType')
        ->select('*','tblstaff_leave.id as leaveAppID')
        ->where('staffID','=',$request->staffID)
        ->where('tblstaff_leave.approval_status','=',1)
        ->where('tblstaff_leave.memo_status','=',1)
        ->get();
        $data['staff'] = DB::table('tblper')->where('staff_status','=',1)->get();
        return view('Leave.leaveReport',$data);
    }

    
    public function sendMemo(Request $request)
    {
        $staff = DB::table('tblper')->where('ID','=',$request->staffid)->first();
        $memo = DB::table('tblleave_memo')->where('staffID','=',$request->staffid)->first();
        DB::table('tblstaff_leave')->where('id','=',$request->leaveID)->update([
            'memo_status'        => 1,
            
        ]);
        /*$to = $staff->email;
        $subject= $memo->subject;
        $from =  "hr.njc.gov.ng";
        $header = "From:".$from."\r\n";
        $header .= "MIME-Version: 1.0 \r\n";
        $header .= "Content-type: text/html \r\n";
        $msg= $request->message;
        $message = "$message , Please, login to https://hr.njc.gov.ng to view the memo.";
        $retval = mail ($to,$subject,$msg,$header); 
        */
        return back()->with('message','Successfull');
    }
}
