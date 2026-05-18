<?php

namespace App\Http\Controllers\payroll;

use App\Http\Requests;
use App\Role;
use App\User;
use Auth;
use Entrust;
use App\Permission;
use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\Controller;
use Session;
use DateTime;

class ApprovalProcessController extends ParentController
{
    public $division;

    public function checkingPayroll()
    {
        $data['CourtInfo']=$this->CourtInfo();
        if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
        if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

        $staffCourt  = DB::table('tblper')->where('UserID','=',auth::user()->id)->first();


        $active  = DB::table('tblactivemonth')->where('courtID','=',$staffCourt->courtID)->first();
         if(count($active)==0)
         {
         return back()->with('msg','Access Denied: User Not a staff');
         }
        $data['courtname']  = DB::table('tbl_court')
            ->where('id','=', $staffCourt->courtID)
            ->first();

        $data['courtDivisions']  = DB::table('tbl_court')
            ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
            ->where('tbl_court.id','=',$staffCourt->courtID)
            ->where('tbldivision.divisionID','=',$staffCourt->divisionID)
            ->first();
        $data['payroll_detail'] = DB::table('tblpayment')
            ->where('tblpayment.month',     '=', $active->month)
            ->where('tblpayment.year',      '=', $active->year)
            //->where('tblpayment.divisionID',  '=', $staffCourt->divisionID)
            ->where('tblpayment.courtID',  '=', $staffCourt->courtID)
            ->where('tblpayment.payment_status',  '=', 1)
            ->orderBy('Bs','DESC')
            ->get();

        $data['comments'] = DB::table('tblsalary_comments')
            ->join('users','users.username','=','tblsalary_comments.by_who')
            ->where('tblsalary_comments.month',     '=', $active->month)
            ->where('tblsalary_comments.year',      '=', $active->year)
            ->where('tblsalary_comments.courtID',  '=', $staffCourt->courtID)
            ->get();

        $data['action'] = DB::table('tblaction_rank')->where('userid','=',auth::user()->username)->first();
        if(!is_null($data['action'])) {
            $data['to'] = DB::table('tblapproval_track')
                ->where('month',     '=', $active->month)
                ->where('year',      '=', $active->year)
                ->where('sent_to','=',$data['action']->code)->first();
        }
        if(is_null($data['action']))
        {
            $code = 0;
        }
        else {
            $code = $data['action']->code;
        }


        //dd( $active->year);
        $data['year'] = $active->year;
        $data['month'] = $active->month;
        $data['court'] = $active->courtID;
        return view('payrollApproval.checking', $data);


    }
    public function checkAndClear(Request $request)
    {
        $data['insert']    = DB::table('tblpayment')
            ->where('courtID','=',$request['court'])
            ->where('year','=',$request['year'])
            ->where('month','=',$request['month'])->update(array(
                'payment_status' => 2,
            ));
            if($request['submit'] == 'Reject')
        {
        $data['insert'] = DB::table('tblpayment')
            ->where('courtID', '=', $request['court'])
            ->where('year', '=', $request['year'])
            ->where('month', '=', $request['month'])->update(array(
                'payment_status' => 0,
            ));
        }
        $data['insert']    = DB::table('tblsalary_comments')
            ->insert(array(
                'year'                    => $request['year'],
                'month'                   => $request['month'],
                'courtID'                 => $request['court'],
                'comment'                 => $request['remark'],
                'by_who'                  => auth::user()->username,
                'to_who'                  => "Audit",
                'updated_at'               => date('Y-m-d'),
            ));

        $countTrack = DB::table('tblapproval_track')
            ->where('month',     '=', $request['month'])
            ->where('year',      '=', $request['year'])
            ->where('courtID',  '=', $request['court'])
            ->count();
        if($countTrack ==1)
        {
            $data['update']    = DB::table('tblapproval_track')
                ->where('courtID','=',$request['court'])
                ->where('year','=',$request['year'])
                ->where('month','=',$request['month'])->update(array(
                    'year'                    => $request['year'],
                    'month'                   => $request['month'],
                    'courtID'                 => $request['court'],

                    'sent_by'                  => auth::user()->username,
                    'sent_to'                  => "Audit",
                    'updated_at'               => date('Y-m-d'),
                ));
        }
        else
        {
            $data['insert']    = DB::table('tblapproval_track')
                ->insert(array(
                    'year'                    => $request['year'],
                    'month'                   => $request['month'],
                    'courtID'                 => $request['court'],

                    'sent_by'                  => auth::user()->username,
                    'sent_to'                  => "Audit",
                    'updated_at'               => date('Y-m-d'),
                ));
        }
        
        return back()->with('msg','Successfully Checked and transfered to Audit unit');
    }

    public function auditPayroll()
    {
        $data['CourtInfo']=$this->CourtInfo();
        if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
        if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

        $staffCourt  = DB::table('tblper')->where('UserID','=',auth::user()->id)->first();

        $active  = DB::table('tblactivemonth')->where('courtID','=',$staffCourt->courtID)->first();
        $data['courtname']  = DB::table('tbl_court')
            ->where('id','=', $staffCourt->courtID)
            ->first();

        $data['courtDivisions']  = DB::table('tbl_court')
            ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
            ->where('tbl_court.id','=',$staffCourt->courtID)
            ->where('tbldivision.divisionID','=',$staffCourt->divisionID)
            ->first();
        $data['payroll_detail'] = DB::table('tblpayment')
            ->where('tblpayment.month',     '=', $active->month)
            ->where('tblpayment.year',      '=', $active->year)
            //->where('tblpayment.divisionID',  '=', $staffCourt->divisionID)
            ->where('tblpayment.courtID',  '=', $staffCourt->courtID)
            ->where('tblpayment.payment_status',  '=', 2)
            ->orderBy('Bs','DESC')
            ->get();
        $data['comments'] = DB::table('tblsalary_comments')
            ->join('users','users.username','=','tblsalary_comments.by_who')
            ->where('tblsalary_comments.month',     '=', $active->month)
            ->where('tblsalary_comments.year',      '=', $active->year)
            ->where('tblsalary_comments.courtID',  '=', $staffCourt->courtID)
            ->get();
        //dd( $active->year);
        $data['year'] = $active->year;
        $data['month'] = $active->month;
        $data['court'] = $active->courtID;
        return view('payrollApproval.audit', $data);


    }
    public function auditAndClear(Request $request)
    {
        $btn = $request['submit'];
        if ($btn == 'Clear and Proceed')
        {
            $data['insert'] = DB::table('tblpayment')
                ->where('courtID', '=', $request['court'])
                ->where('year', '=', $request['year'])
                ->where('month', '=', $request['month'])->update(array(
                    'payment_status' => 3,
                ));
        $data['insert'] = DB::table('tblsalary_comments')
            ->insert(array(
                'year' => $request['year'],
                'month' => $request['month'],
                'courtID' => $request['court'],
                'comment' => $request['remark'],
                'by_who' => auth::user()->username,
                'to_who' => $request['attension'],
            ));
        return back()->with('msg', 'Successfully Checked and transfered to Audit unit');
    }
    elseif($btn == 'Return to Checking'){
        $data['insert'] = DB::table('tblpayment')
            ->where('courtID', '=', $request['court'])
            ->where('year', '=', $request['year'])
            ->where('month', '=', $request['month'])->update(array(
                'payment_status' => 1,
            ));
        $data['insert'] = DB::table('tblsalary_comments')
            ->insert(array(
                'year' => $request['year'],
                'month' => $request['month'],
                'courtID' => $request['court'],
                'comment' => $request['remark'],
                'by_who' => auth::user()->username,
                'to_who' => 'Ckecking',
                'updated_at'               => date('Y-m-d'),
            ));
        $countTrack = DB::table('tblapproval_track')
            ->where('month',     '=', $request['month'])
            ->where('year',      '=', $request['year'])
            ->where('courtID',  '=', $request['court'])
            ->count();
        if($countTrack ==1)
        {
            $data['update']    = DB::table('tblapproval_track')
                ->where('courtID','=',$request['court'])
                ->where('year','=',$request['year'])
                ->where('month','=',$request['month'])->update(array(
                    'year'                    => $request['year'],
                    'month'                   => $request['month'],
                    'courtID'                 => $request['court'],
                    'sent_by'                  => auth::user()->username,
                    'sent_to'                  => "CPO",
                    'updated_at'               => date('Y-m-d'),
                ));
        }
        else
        {
            $data['insert']    = DB::table('tblapproval_track')
                ->insert(array(
                    'year'                    => $request['year'],
                    'month'                   => $request['month'],
                    'courtID'                 => $request['court'],
                    'sent_by'                  => auth::user()->username,
                    'sent_to'                  => "CPO",
                    'updated_at'               => date('Y-m-d'),
                ));
        }
        return back()->with('msg', 'Successfully ');
    }
    }

    public function cpoPayroll()
    {
        $data['CourtInfo']=$this->CourtInfo();
        if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
        if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

        $staffCourt  = DB::table('tblper')->where('UserID','=',auth::user()->id)->first();

        $active  = DB::table('tblactivemonth')->where('courtID','=',$staffCourt->courtID)->first();
        $data['courtname']  = DB::table('tbl_court')
            ->where('id','=', $staffCourt->courtID)
            ->first();

        $data['courtDivisions']  = DB::table('tbl_court')
            ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
            ->where('tbl_court.id','=',$staffCourt->courtID)
            ->where('tbldivision.divisionID','=',$staffCourt->divisionID)
            ->first();
        $data['payroll_detail'] = DB::table('tblpayment')
            ->where('tblpayment.month',     '=', $active->month)
            ->where('tblpayment.year',      '=', $active->year)
            //->where('tblpayment.divisionID',  '=', $staffCourt->divisionID)
            ->where('tblpayment.courtID',  '=', $staffCourt->courtID)
            ->where('tblpayment.payment_status',  '=', 3)
            ->orderBy('Bs','DESC')
            ->get();
        $data['comments'] = DB::table('tblsalary_comments')
            ->join('users','users.username','=','tblsalary_comments.by_who')
            ->where('tblsalary_comments.month',     '=', $active->month)
            ->where('tblsalary_comments.year',      '=', $active->year)
            ->where('tblsalary_comments.courtID',  '=', $staffCourt->courtID)
            ->get();
        //dd( $active->year);
        $data['year'] = $active->year;
        $data['month'] = $active->month;
        $data['court'] = $active->courtID;
        return view('payrollApproval.cpo', $data);


    }
    public function cpoProcess(Request $request)
    {
        $data['insert']    = DB::table('tblpayment')
            ->where('courtID','=',$request['court'])
            ->where('year','=',$request['year'])
            ->where('month','=',$request['month'])->update(array(
                'payment_status' => 4,
            ));
        $data['insert']    = DB::table('tblsalary_comments')
            ->insert(array(
                'year'                    => $request['year'],
                'month'                   => $request['month'],
                'courtID'                 => $request['court'],
                'comment'                 => $request['remark'],
                'by_who'                  => auth::user()->username,
                'to_who'                  => "Audit",
            ));
        $countTrack = DB::table('tblapproval_track')
            ->where('month',     '=', $request['month'])
            ->where('year',      '=', $request['year'])
            ->where('courtID',  '=', $request['court'])
            ->count();
        if($countTrack ==1)
        {
            $data['update']    = DB::table('tblapproval_track')
                ->where('courtID','=',$request['court'])
                ->where('year','=',$request['year'])
                ->where('month','=',$request['month'])->update(array(
                    'year'                    => $request['year'],
                    'month'                   => $request['month'],
                    'courtID'                 => $request['court'],
                    'sent_by'                  => auth::user()->username,
                    'sent_to'                  => $request['attension'],
                    'updated_at'               => date('Y-m-d'),
                ));
        }
        else
        {
            $data['insert']    = DB::table('tblapproval_track')
                ->insert(array(
                    'year'                    => $request['year'],
                    'month'                   => $request['month'],
                    'courtID'                 => $request['court'],
                    'sent_by'                  => auth::user()->username,
                    'sent_to'                  => $request['attension'],
                    'updated_at'               => date('Y-m-d'),
                ));
        }
        return back()->with('msg','Successfully Checked and transfered to Audit unit');
    }

    public function ca()
{
    $data['CourtInfo']=$this->CourtInfo();
    if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
    if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

    $staffCourt  = DB::table('tblper')->where('UserID','=',auth::user()->id)->first();


    $active  = DB::table('tblactivemonth')->where('courtID','=',$staffCourt->courtID)->first();
    $data['courtname']  = DB::table('tbl_court')
        ->where('id','=', $staffCourt->courtID)
        ->first();

    $data['courtDivisions']  = DB::table('tbl_court')
        ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
        ->where('tbl_court.id','=',$staffCourt->courtID)
        ->where('tbldivision.divisionID','=',$staffCourt->divisionID)
        ->first();
    //dd($active);
    $data['payroll_detail'] = DB::table('tblpayment')
        ->where('tblpayment.month',     '=', $active->month)
        ->where('tblpayment.year',      '=', $active->year)
        //->where('tblpayment.divisionID',  '=', $staffCourt->divisionID)
        ->where('tblpayment.courtID',  '=', $staffCourt->courtID)
        ->where('tblpayment.payment_status',  '=', 4)
        ->orderBy('Bs','DESC')
        ->get();
    $data['comments'] = DB::table('tblsalary_comments')
        ->join('users','users.username','=','tblsalary_comments.by_who')
        ->where('tblsalary_comments.month',     '=', $active->month)
        ->where('tblsalary_comments.year',      '=', $active->year)
        ->where('tblsalary_comments.courtID',  '=', $staffCourt->courtID)
        ->get();
    //dd( $data['payroll_detail']);
    $data['year'] = $active->year;
    $data['month'] = $active->month;
    $data['court'] = $active->courtID;
    $data['to'] = "";
    $data['action'] = DB::table('tblaction_rank')->where('userid','=',auth::user()->username)->first();
    if(!is_null($data['action'])) {
        $data['to'] = DB::table('tblapproval_track')
            ->where('month',     '=', $active->month)
            ->where('year',      '=', $active->year)
            ->where('sent_to','=',$data['action']->code)->first();
    }
    
    $data['track'] = DB::table('tblapproval_track')
        ->where('month',     '=', $active->month)
        ->where('year',      '=', $active->year)
        ->where('courtID',  '=', $staffCourt->courtID)
        ->first();

    return view('payrollApproval.ca', $data);

}
    public function caProcess(Request $request)
    {

        $btn = $request['btn'];


            $d = $request['attension'];
            if ($d === 'return') {
                $val = 0;
            } elseif ($d === 'CA') {
                $val = 4;
            } elseif ($d === 'DDFA') {
                $val = 5;
            } elseif ($d === 'DFA') {
                $val = 6;
            } elseif ($d === 'ES') {
                $val = 7;
            } elseif ($d === 'FA') {
                $val = 8;
            }

            $data['insert'] = DB::table('tblpayment')
                ->where('courtID', '=', $request['court'])
                ->where('year', '=', $request['year'])
                ->where('month', '=', $request['month'])->update(array(
                    'payment_status' => $val,
                ));
            $data['insert'] = DB::table('tblsalary_comments')
                ->insert(array(
                    'year' => $request['year'],
                    'month' => $request['month'],
                    'courtID' => $request['court'],
                    'comment' => $request['remark'],
                    'by_who' => auth::user()->username,
                    'to_who' => $request['attension'],
                ));
            $countTrack = DB::table('tblapproval_track')
                ->where('month', '=', $request['month'])
                ->where('year', '=', $request['year'])
                ->where('courtID', '=', $request['court'])
                ->count();
            if ($countTrack == 1) {
                $data['update'] = DB::table('tblapproval_track')
                    ->where('courtID', '=', $request['court'])
                    ->where('year', '=', $request['year'])
                    ->where('month', '=', $request['month'])->update(array(
                        'year' => $request['year'],
                        'month' => $request['month'],
                        'courtID' => $request['court'],
                        'sent_by' => auth::user()->username,
                        'sent_to' => $request['attension'],
                        'updated_at' => date('Y-m-d'),
                    ));
            } else {
                $data['insert'] = DB::table('tblapproval_track')
                    ->insert(array(
                        'year' => $request['year'],
                        'month' => $request['month'],
                        'courtID' => $request['court'],
                        'sent_by' => auth::user()->username,
                        'sent_to' => $request['attension'],
                        'updated_at' => date('Y-m-d'),
                    ));
            }
            return back()->with('msg', 'Successfully Checked and transfered to Audit unit');


    }

    public function DFA()
    {
        $data['CourtInfo']=$this->CourtInfo();
        if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
        if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

        $staffCourt  = DB::table('tblper')->where('UserID','=',auth::user()->id)->first();

        $active  = DB::table('tblactivemonth')->where('courtID','=',$staffCourt->courtID)->first();
        $data['courtname']  = DB::table('tbl_court')
            ->where('id','=', $staffCourt->courtID)
            ->first();

        $data['courtDivisions']  = DB::table('tbl_court')
            ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
            ->where('tbl_court.id','=',$staffCourt->courtID)
            ->where('tbldivision.divisionID','=',$staffCourt->divisionID)
            ->first();
        $data['payroll_detail'] = DB::table('tblpayment')
            ->where('tblpayment.month',     '=', $active->month)
            ->where('tblpayment.year',      '=', $active->year)
            //->where('tblpayment.divisionID',  '=', $staffCourt->divisionID)
            ->where('tblpayment.courtID',  '=', $staffCourt->courtID)
            ->where('tblpayment.payment_status',  '=', 5)
            ->orWhere('tblpayment.payment_status',  '=', 6)
            ->orderBy('Bs','DESC')
            ->get();
        $data['comments'] = DB::table('tblsalary_comments')
            ->join('users','users.username','=','tblsalary_comments.by_who')
            ->where('tblsalary_comments.month',     '=', $active->month)
            ->where('tblsalary_comments.year',      '=', $active->year)
            ->where('tblsalary_comments.courtID',  '=', $staffCourt->courtID)
            ->get();
        //dd( $active->year);
        $data['year'] = $active->year;
        $data['month'] = $active->month;
        $data['court'] = $active->courtID;
        $data['action'] = DB::table('tblaction_rank')->where('userid','=',auth::user()->username)->first();
        if(!is_null($data['action'])) {
            $data['to'] = DB::table('tblapproval_track')
                ->where('month',     '=', $active->month)
                ->where('year',      '=', $active->year)
                ->where('sent_to','=',$data['action']->code)->first();
        }
        //dd($data['to']);
        if(is_null($data['action']))
        {
            $code = 0;
        }
        else {
            $code = $data['action']->code;
        }
        $data['track'] = DB::table('tblapproval_track')
            ->where('month',     '=', $active->month)
            ->where('year',      '=', $active->year)
            ->where('courtID',  '=', $staffCourt->courtID)
            ->first();
        //dd($data['to']);
        return view('payrollApproval.dfa', $data);

    }
    public function DFAProcess(Request $request)
    {
        $d = $request['attension'];
        $a = $request['action'];

        if($d === 'return')
        {
            $val = 0;
        }
        elseif($d === 'CA')
        {
            $val = 4;
        }
        elseif($d === 'DDFA')
        {
            $val = 5;
        }
        elseif($d === 'DFA')
        {
            $val = 6;
        }

        elseif($d === 'ES')
        {
            $val = 7;
        }
        elseif($d === 'FA')
        {
            $val = 8;
        }
        /*elseif($a === 'DDFA'){
            $val = 6;
        }
        elseif($a === 'DFA'){
            $val = 7;
        }
        elseif($a === 'FA'){
            $val = 8;
        }*/
        $data['insert'] = DB::table('tblpayment')
            ->where('courtID', '=', $request['court'])
            ->where('year', '=', $request['year'])
            ->where('month', '=', $request['month'])->update(array(
                'payment_status' => $val,
            ));
        $data['insert'] = DB::table('tblsalary_comments')
            ->insert(array(
                'year'    => $request['year'],
                'month'   => $request['month'],
                'courtID' => $request['court'],
                'comment' => $request['remark'],
                'by_who'  => auth::user()->username,
                'to_who'  => $request['attension'],
            ));
        $countTrack = DB::table('tblapproval_track')
            ->where('month',     '=', $request['month'])
            ->where('year',      '=', $request['year'])
            ->where('courtID',  '=', $request['court'])
            ->count();
        if($countTrack ==1)
        {
            $data['update']    = DB::table('tblapproval_track')
                ->where('courtID','=',$request['court'])
                ->where('year','=',$request['year'])
                ->where('month','=',$request['month'])->update(array(
                    'year'                    => $request['year'],
                    'month'                   => $request['month'],
                    'courtID'                 => $request['court'],
                    'sent_by'                  => auth::user()->username,
                    'sent_to'                  => $request['attension'],
                    'updated_at'               => date('Y-m-d'),
                ));
        }
        else
        {
            $data['insert']    = DB::table('tblapproval_track')
                ->insert(array(
                    'year'                    => $request['year'],
                    'month'                   => $request['month'],
                    'courtID'                 => $request['court'],
                    'sent_by'                  => auth::user()->username,
                    'sent_to'                  => $request['attension'],
                    'updated_at'               => date('Y-m-d'),
                ));
        }
        return back()->with('msg', 'Successfully Checked and transfered to Audit unit');

    }

    public function es()
    {

        $data['CourtInfo']=$this->CourtInfo();
        if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
        if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

        $staffCourt  = DB::table('tblper')->where('UserID','=',auth::user()->id)->first();

        //$active  = DB::table('tblactivemonth')->where('courtID','=',$staffCourt->courtID)->first();
        $active  = DB::table('tblactivemonth')->first();
        $data['courtname']  = DB::table('tbl_court')
            //->where('id','=', $staffCourt->courtID)
            ->first();

        $data['courtDivisions']  = DB::table('tbl_court')
            ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
            //->where('tbl_court.id','=',$staffCourt->courtID)
            //->where('tbldivision.divisionID','=',$staffCourt->divisionID)
            ->first();
        $data['payroll_detail'] = DB::table('tblpayment_consolidated')

            ->where('tblpayment_consolidated.payment_status',  '=', 6)
            ->orWhere('tblpayment_consolidated.payment_status',  '=', 7)
            ->where('tblpayment_consolidated.month',     '=', $active->month)
            ->where('tblpayment_consolidated.year',      '=', $active->year)
            //->where('tblpayment.divisionID',  '=', $staffCourt->divisionID)
            //->where('tblpayment.courtID',  '=', $staffCourt->courtID)
            ->orderBy('Bs','DESC')
            ->get();
        $data['comments'] = DB::table('tblsalary_comments')
            ->join('users','users.username','=','tblsalary_comments.by_who')
            ->where('tblsalary_comments.month',     '=', $active->month)
            ->where('tblsalary_comments.year',      '=', $active->year)
            //->where('tblsalary_comments.courtID',  '=', $staffCourt->courtID)
            ->get();
        //dd( $active->year);
        $data['year'] = $active->year;
        $data['month'] = $active->month;
        $data['court'] = $active->courtID;
        $data['to'] ="";
        $data['action'] = DB::table('tblaction_rank')->where('userid','=',auth::user()->username)->first();
        if(!is_null($data['action'])) {
            $data['to'] = DB::table('tblapproval_track')
                ->where('month',     '=', $active->month)
                ->where('year',      '=', $active->year)
                ->where('sent_to','=',$data['action']->code)->first();
        }
        $data['track'] = DB::table('tblapproval_track')
            ->where('month',     '=', $active->month)
            ->where('year',      '=', $active->year)
            //->where('courtID',  '=', $staffCourt->courtID)
            ->first();
        return view('payrollApproval.es', $data);

    }
    public function esProcess(Request $request)
    {
        $this->validate($request, [
            'remark'         => 'required',
            'attension'      => 'required',
        ],
        [
            'attension:required' => ' Please, select who will take next action',
        ]

        );
        $d = $request['attension'];

        if($d === 'return')
        {
            $val = 0;
        }
        elseif($d === 'CA')
        {
            $val = 4;
        }
        elseif($d === 'DDFA')
        {
            $val = 5;
        }
        elseif($d === 'DFA')
        {
            $val = 6;
        }

        elseif($d === 'ES')
        {
            $val = 7;
        }
        elseif($d === 'FA')
        {
            $val = 8;
        }
        $data['insert'] = DB::table('tblpayment')
            ->where('courtID', '=', $request['court'])
            ->where('year', '=', $request['year'])
            ->where('month', '=', $request['month'])->update(array(
                'payment_status' => $val,
            ));
        $data['insert'] = DB::table('tblsalary_comments')
            ->insert(array(
                'year'    => $request['year'],
                'month'   => $request['month'],
                'courtID' => $request['court'],
                'comment' => $request['remark'],
                'by_who'  => auth::user()->username,
                'to_who'  => $request['attension'],
            ));
        $countTrack = DB::table('tblapproval_track')
            ->where('month',     '=', $request['month'])
            ->where('year',      '=', $request['year'])
            ->where('courtID',  '=', $request['court'])
            ->count();
        if($countTrack ==1)
        {
            $data['update']    = DB::table('tblapproval_track')
                ->where('courtID','=',$request['court'])
                ->where('year','=',$request['year'])
                ->where('month','=',$request['month'])->update(array(
                    'year'                    => $request['year'],
                    'month'                   => $request['month'],
                    'courtID'                 => $request['court'],
                    'sent_by'                  => auth::user()->username,
                    'sent_to'                  => $request['attension'],
                    'updated_at'               => date('Y-m-d'),
                ));
        }
        else
        {
            $data['insert']    = DB::table('tblapproval_track')
                ->insert(array(
                    'year'                    => $request['year'],
                    'month'                   => $request['month'],
                    'courtID'                 => $request['court'],
                    'sent_by'                  => auth::user()->username,
                    'sent_to'                  => $request['attension'],
                    'updated_at'               => date('Y-m-d'),
                ));
        }
        return back()->with('msg', 'Successfully Checked and transfered to Audit unit');

    }
    public function recall(Request $request)
    {

            $track = DB::table('tblapproval_track')
                ->where('month',     '=', $request['month'])
                ->where('year',      '=', $request['year'])
                ->where('courtID',  '=', $request['court'])
                ->first();

            if($track->sent_to == 'CA')
            {
                $value = 3;
            }
            if($track->sent_to == 'DDFA')
            {
                $value = 4;
                $sento = 'CA';
            }
            if($track->sent_to == 'DFA')
            {
                $value = 5;
                $sento = 'DDFA';
            }
            if($track->sent_to == 'ES')
            {
                $value = 6;
                $sento = 'DFA';
            }
            if($track->sent_to == 'FA')
            {
                $value = 7;
                $sento = 'ES';
            }

            $u = DB::table('tblpayment')
                ->where('courtID', '=', $request['court'])
                ->where('year', '=', $request['year'])
                ->where('month', '=', $request['month'])->update(array(
                    'payment_status' => $value,
                ));
            DB::table('tblapproval_track')
                ->where('courtID', '=', $request['court'])
                ->where('year', '=', $request['year'])
                ->where('month', '=', $request['month'])->update(array(
                    'sent_to' => $sento,
                ));
            return back();

    }




}