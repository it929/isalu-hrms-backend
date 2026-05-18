<?php

namespace App\Http\Controllers\hr;

use Illuminate\Http\Request;
//use Auth;
use App\Http\Requests;
use DB;
use Auth;
use Session;
class LeaveApprovalController extends functionController
{
	public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->middleware('auth');
        $this->username = Session::get('userName');
            return $next($request);
        });
    }
    
    public function getNotification(Request $request)
   {  
	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	   $staffid=$this->StaffID($this->username);
	   $data['Notification']=$this->SelfNotification($staffid);
   	return view('Leave.notification', $data);
   }

   public function LeaveApproval()
   {
       $data['allLeave'] = DB::table('tblaction_stages')
       ->leftjoin('users', 'users.id', '=', 'userID')
       ->leftjoin('tbldepartment', 'tbldepartment.id', '=', 'departmentID')
       ->leftjoin('tblleave_approval_stages', 'tblleave_approval_stages.stage', '=', 'action_stageID')
       ->select('*', 'tblaction_stages.id as asid')
       ->get();
       $data['allUsers'] = DB::table('users')->get();
       $data['allDepartment'] = DB::table('tbldepartment')->get();
       $data['allStages'] = DB::table('tblleave_approval_stages')->get();

       return view('LeaveApproval.leaveApprovalStaff', $data);
   }

   public function saveApproval (Request $request)
   {
        $validated = $request->validate([
            'staff' => 'required',
            'dept' => 'required',
            'stage' => 'required',
        ]);

        $staff = $request->input('staff');
        $dept = $request->input('dept');
        $stage = $request->input('stage');

       // dd($request->all());

        $setApprovalStage = DB::table('tblaction_stages')->insert([
            'userID' => $staff,
            'departmentID' => $dept,
            'action_stageID' => $stage,
        ]);

        if($setApprovalStage){
            return redirect()->back()->with('success', 'Saved, record was created successfull.');
        }
        return redirect()->back()->with('danger', 'Sorry, we cannot process your record. Please try again.');
   }

   public function editApproval(Request $request)
   {
    $validated = $request->validate([
        'staff' => 'required',
        'dept' => 'required',
        'stage' => 'required',
    ]);

    $recordID = $request->input('recordID');
    $staff = $request->input('staff');
    $dept = $request->input('dept');
    $stage = $request->input('stage');
    //dd($stage);

    $saved =  DB::table('tblaction_stages')->where('id', $recordID)->update([
        'userID' => $staff,
        'departmentID' => $dept,
        'action_stageID' => $stage,
    ]);
    return redirect()->back()->with('success', 'record updated!');
   }

   public function delete($id)
   {
        DB::table('tblaction_stages')->where('id', $id)->delete();
        return back()->with('success', 'record was deleted successfully.');
   }
}