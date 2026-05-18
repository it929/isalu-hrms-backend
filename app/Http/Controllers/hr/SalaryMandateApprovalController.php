<?php

namespace App\Http\Controllers\hr;

use Illuminate\Http\Request;
use App\Http\Requests;

use DB;
use Auth;
use Session;
use App\Event;
use File;

class SalaryMandateApprovalController extends ParentController
{
    public function salaryAction()
    {
        $activemonth = DB::table('tblactivemonth')
		->first();
		//dd($activemonth);
		$data['activemonth'] = $activemonth;
		$user = Auth::user()->username;
		$data['action'] = DB::table('tblaction_rank')->where('userid','=',$user)->first();
		if($data['action'] == '')
		{
		    return back()->with('msg','You are not permitted to view that page');
		}
		$data['codes'] = DB::table('tblaction_rank')->where('code','!=',$data['action']->code)->where('approval_type','=','SAL')->get();
        $data['mandate'] = DB::table('tblpayment_consolidated')->where('mandate_approval','=',0)->where('year','=',$activemonth->year)->where('month','=',$activemonth->month)->first();
        //->where('next_action','=',$data['action']->code)
        return view('mandateApproval.salaryMandate',$data);
    }
    
    public function salaryHeadComment(Request $request)
    {
        $btns = $request['submit'];
        $year = $request['year'];
        $month = $request['month'];
        $comment = $request['instruction'];
        $to = $request['attension'];
        
        
        /***************** Approval State*********************/
        /*
        CA =1
        DDFA =2
        DFA = 3
        ES = 4
        FA(Final Approval) = 5
        
        */
        /***************** end Approval State*********************/
        
        $action = DB::table('tblaction_rank')->where('userid','=',auth::user()->username)->first();
        if($to == 'FA')
        {
            $mandate = 5;
            $msg = 'Succesfully Approved';
        }
        elseif($to == 'DFA'){
            $mandate = 3;
            $msg = 'Successfully Transfered to Director Finance for Approval';
        }
        elseif($to == 'DDFA'){
            $mandate = 2;
            $msg = 'Successfully Transfered to Deputy Director Finance for Approval';
        }
        elseif($to == 'CA'){
            $mandate = 1;
            $msg = 'Succesfully Transfered to Director Finance for checking and clearing';
        }
         elseif($to == 'CAC'){
            $mandate = 1;
            $msg = 'Succesfully Transfered to Chief Accountant Recurrent';
        }
        elseif($to == 'ES'){
            $mandate = 4;
            $msg = 'Succesfully Transfered to Executive Secretary for Approval';
        }

        $q = DB::table('tblaction_rank')->where('code', '=', $to)->first();
        
        if($btns == 'Check & Clear')
        {
           
            DB::table('tblsalarymandate_comments')
                ->insert(array(
                    'year' => $year,
                    'month' => $month,
                    'to_who' => $to,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'by_who' => auth::user()->id,
                    'comment' => $comment,
                ));

            
            DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)
                    ->update(array(
                        'mandate_approval' => $mandate,
                        'next_action' => $to,
                        'is_rejected' => 0,
                    ));
                    
                    

            return redirect('/salary/view')->with('msg', "Successfully Transfered to $q->description for Verification");
        
    }//process btn ends
    elseif($btns == 'Reject')
    {
      
      
        $action = DB::table('tblaction_rank')->where('userid','=',auth::user()->username)->first();
        if($to == 'FA')
        {
            $mandate = 5;
            $msg = 'Succesfully Approved';
        }
        elseif($to == 'DFA'){
            $mandate = 3;
            $msg = 'Successfully Transfered to Director Finance for Approval';
        }
        elseif($to == 'DDFA'){
            $mandate = 2;
            $msg = 'Successfully Transfered to Deputy Director Finance for Approval';
        }
        elseif($to == 'CA'){
            $mandate = 1;
            $msg = 'Succesfully Transfered to Director Finance for checking and clearing';
        }
         elseif($to == 'CAC'){
            $mandate = 1;
            $msg = 'Succesfully Transfered to Chief Accountant Recurrent';
        }
        elseif($to == 'ES'){
            $mandate = 4;
            $msg = 'Succesfully Transfered to Executive Secretary for Approval';
        }

        
        
            DB::table('tblsalarymandate_comments')
                ->insert(array(
                    'year' => $year,
                    'month' => $month,
                    'to_who' => $to,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'by_who' => auth::user()->id,
                    'comment' => $comment,
                ));

            
            DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)
                    ->update(array(
                        'mandate_approval' => $mandate,
                        'next_action' => $to,
                        'is_rejected' => 1,
                    ));
                    
            return redirect('/salary/view')->with('msg', "rejected");
      
      
    }

    }
    public function mandateView()
    {
        $activemonth = DB::table('tblactivemonth')
		->first();
		//dd($activemonth);
		$data['activemonth'] = $activemonth;
		$user = Auth::user()->username;
		$data['action'] = DB::table('tblaction_rank')->where('userid','=',$user)->first();
		
		if($data['action'] == '')
		{
		    return back()->with('msg','You are not permitted to view that page');
		}
		if($data['action']->code == 'CA')
		{
		    $appr = 1;
		}
		elseif($data['action']->code == 'DDFA')
		{
		    $appr = 2;
		}
		elseif($data['action']->code == 'DFA')
		{
		    $appr = 3;
		}
		elseif($data['action']->code == 'ES')
		{
		    $appr = 4;
		}
		$data['codes'] = DB::table('tblaction_rank')->where('code','!=',$data['action']->code)->where('approval_type','=','SAL')->get();
        $data['mandate'] = DB::table('tblpayment_consolidated')->where('next_action','=',$data['action']->code)->where('mandate_approval','=',$appr)->where('year','=',$activemonth->year)->where('month','=',$activemonth->month)->first();
        //->where('next_action','=',$data['action']->code)
        
        return view('mandateApproval.othersView',$data);
    }

    public function mandateComment(Request $request)
    {
        $btns = $request['submit'];
        $year = $request['year'];
        $month = $request['month'];
        $comment = $request['instruction'];
        $to = $request['attension'];
        
        
        /***************** Approval State*********************/
        /*
        CA =1
        DDFA =2
        DFA = 3
        ES = 4
        FA(Final Approval) = 5
        
        */
        /***************** end Approval State*********************/
        
        $action = DB::table('tblaction_rank')->where('userid','=',auth::user()->username)->first();
        if($to == 'FA')
        {
            $mandate = 5;
            $msg = 'Succesfully Approved';
        }
        elseif($to == 'DFA'){
            $mandate = 3;
            $msg = 'Successfully Transfered to Director Finance for Approval';
        }
        elseif($to == 'DDFA'){
            $mandate = 2;
            $msg = 'Successfully Transfered to Deputy Director Finance for Approval';
        }
        elseif($to == 'CA'){
            $mandate = 1;
            $msg = 'Succesfully Transfered to Director Finance for checking and clearing';
        }
         elseif($to == 'CAC'){
            $mandate = 1;
            $msg = 'Succesfully Transfered to Chief Accountant Recurrent';
        }
        elseif($to == 'ES'){
            $mandate = 4;
            $msg = 'Succesfully Transfered to Executive Secretary for Approval';
        }

        $q = DB::table('tblaction_rank')->where('code', '=', $to)->first();
        
        if($btns == 'Check & Clear')
        {
           
            DB::table('tblsalarymandate_comments')
                ->insert(array(
                    'year' => $year,
                    'month' => $month,
                    'to_who' => $to,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'by_who' => auth::user()->id,
                    'comment' => $comment,
                ));

            
            DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)
                    ->update(array(
                        'mandate_approval' => $mandate,
                        'next_action' => $to,
                        'is_rejected' => 0,
                    ));
                    
                    

            return redirect('/mandate/view')->with('msg', "Successfully Transfered to $q->description for Verification");
        
    }//process btn ends
    elseif($btns == 'Reject')
    {
      
      
        
            DB::table('tblsalarymandate_comments')
                ->insert(array(
                    'year' => $year,
                    'month' => $month,
                    'to_who' => $to,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'by_who' => auth::user()->id,
                    'comment' => $comment,
                ));

            
            DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)
                    ->update(array(
                        'mandate_approval' => $mandate,
                        'next_action' => $to,
                        'is_rejected' => 1,
                    ));
                    
            return redirect('/mandate/view')->with('msg', "rejected");
      
      
    }

    }
    
    public function mandate($year,$month)
    {
  
  $data['bat'] = DB::table('tblbat')
           ->where('year', $year)
           ->where('month', $month)
           ->first();
   $data['lock'] = DB::table('tblpayment_consolidated')->where('salary_lock','=',1)->where('month','=',$month)->where('year','=',$year)->count();


 $data['month'] = $month;
  Session::put('serialNo', 1);
  
 
       $data['courtname'] = '';
     
     
 
      $data['epayment_detail'] = DB::table('tblpayment_consolidated')
       ->leftJoin('tblbanklist','tblbanklist.bankID','=','tblpayment_consolidated.bank')
       ->leftJoin('tblbacklog','tblbacklog.staffid','=','tblpayment_consolidated.staffid','tblbacklog.month','=',$month,'tblbacklog.year','=',$year)
        ->where('tblpayment_consolidated.month',     '=', $month)
        ->where('tblpayment_consolidated.year',      '=', $year)
        
        ->where('tblpayment_consolidated.rank','!=',2)
        ->orderBy('tblpayment_consolidated.bank','DESC')
        ->orderBy('tblpayment_consolidated.rank','DESC')
        ->orderBy('tblpayment_consolidated.name','ASC')
         ->get();
         
         //dd($data['epayment_detail']); 
       $data['coopSumSaving'] = DB::table('tblotherEarningDeduction')->where('CVID','=',15)->where('year','=',$year)->where('month','=',$month)->sum('amount');
        $data['coopSumLoan'] = DB::table('tblotherEarningDeduction')->where('CVID','=',16)->where('year','=',$year)->where('month','=',$month)->sum('amount');
        $data['refundSum'] = DB::table('tblotherEarningDeduction')->where('CVID','=',2)->where('year','=',$year)->where('month','=',$month)->sum('amount');
        $data['unionSum'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('UD');
        $data['taxSum'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('TAX');
        $gross = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('TEarn');
       $data['nhis'] = (5/100) * $gross;
       $data['totalPaySum'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('NetPay');

       
  $data['epayment_total'] = DB::table('tblpayment_consolidated')
        
        ->where('tblpayment_consolidated.month',     '=', $month)
        ->where('tblpayment_consolidated.year',      '=', $year)
       ->orderBy('tblpayment_consolidated.grade','DESC')
        ->orderBy('tblpayment_consolidated.bank','DESC')
        ->orderBy('tblpayment_consolidated.rank','DESC')
        ->orderBy('tblpayment_consolidated.name','ASC')
        
        ->get();
        
  $totalRows = DB::table('tblpayment_consolidated')
        ->where('tblpayment_consolidated.month',     '=', $month)
        ->where('tblpayment_consolidated.year',      '=', $year)
        ->count();

      
  
  if($totalRows<10)
  {
    Session::put('showTotal', "yes");
  }
  elseif ($totalRows==10) 
  {
    Session::put('showTotal', "yes"); 
  }
  else
  {
    Session::put('showTotal', "");  
  }





  Session::put('month', $month);
  Session::put('year', $year);
  Session::put('schmonth', $month." ".$year); 
  //Session::put('bank', $bankName ." ".$bankGroup);

  //DD($data['epayment_detail']);
  $data['M_signatory'] = DB::table('tblmandatesignatory')
       ->leftJoin('tblmandatesignatoryprofiles','tblmandatesignatoryprofiles.id','=','tblmandatesignatory.signatoryID')->orderby('tblmandatesignatory.id')->get();
//dd($data['epayment_detail']);

  //return view('con_epayment.summary200319', $data);
  //dd($data['epayment_detail']); 
  
  $data['nhisbal'] = DB::table('tblnhisbalances')
   ->where('month',     '=', $month)
   ->where('year',      '=', $year)
   ->first();
   $data['nhisexist'] = count($data['nhisbal']);
  return view('mandateApproval.mandate', $data);
    }
    
    public function displayComments($year,$month)
    {
        $data['cmt'] = DB::table('tblsalarymandate_comments')
                    ->join('users', 'users.id', '=', "tblsalarymandate_comments.by_who")
                    ->where('tblsalarymandate_comments.year', '=', $year)
                    ->where('tblsalarymandate_comments.month', '=', $month)
                    ->select('*','tblsalarymandate_comments.updated_at as lastUpdated')
                    ->get();
                    
                    return view('mandateApproval.comments',$data);
    }
    
     public function rejectionReason(Request $request)
    {
        $user = Auth::user()->username;
       $action = DB::table('tblaction_rank')->where('userid','=',$user)->first();
       
       $reason = DB::table('tblsalarymandate_comments')
       ->where('tblsalarymandate_comments.year', '=', $year)
       ->where('tblsalarymandate_comments.month', '=', $month)
       ->where('tblsalarymandate_comments.rejection_comment', '=', 1)
       ->where('tblsalarymandate_comments.to_who','=',$action->code)
       ->first();
       
         return response()->json($reason);
        
    }
    
    

}