<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;

use App\Http\Requests;
use App\Role;
use App\User;
use Auth;
use Entrust;
use App\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as rep;
use DB;

class LeaveFormCertificateController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }



    public function generateLeaveCertificate($staffLeaveID = null)
    {
        try{
            if($staffLeaveID && DB::table('tblstaff_leave')->where('id', $staffLeaveID)->first())
            {
                $data['getStaff'] = DB::table('tblstaff_leave')->where('tblstaff_leave.id', $staffLeaveID)
                                    //->leftjoin('leave_comments', 'leave_comments.leaveID', '=', 'tblstaff_leave.id')
                                    //->select('tblstaff_leave.*', 'leave_comments.hodcomments')
                                    ->first();
                
                //Get LeaveID
                $leaveID = (isset($data['getStaff']) && $data['getStaff'] ? $data['getStaff']->id : null);
                //HOD Comment
                $data['HODComment'] = DB::table('leave_comments')->where('leaveID', $leaveID)->where('stage', 1)->value('comments');
                //Leave Matters
                $data['leaveMattersComment'] = DB::table('leave_comments')->where('leaveID', $leaveID)->where('stage', 5)->value('comments');
                //
                return view('leaveReport.leaveCertificate', $data);
            }
        }catch(\Throwable $e){}

        return redirect('/')->with('error', 'Sorry, record not found!');
    }




}//end class
