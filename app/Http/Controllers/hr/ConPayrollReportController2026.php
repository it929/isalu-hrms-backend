<?php

namespace App\Http\Controllers\hr;

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

class ConPayrollReportController extends ParentController
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */

  /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Http\Response
   */

    public $division; 
   public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
     $this->division = $request->session()->get('division');
     $this->divisionID = $request->session()->get('divisionID');
               return $next($request);
        });
    }

  public function create()
  {
     $data['courts'] =  DB::table('tbl_court')->get();
      $courtSessionId = session('anycourt');
     /*$allbanklist  = DB::table('tblper')
  			 ->where('tblper.divisionID', '=', $this->divisionID)
  			 ->select('tblbanklist.bank', 'tblper.bankID')
  			 ->distinct()
  			 ->join('tblbanklist', 'tblper.bankID', '=', 'tblbanklist.bankID')
  			 ->orderBy('bank', 'Asc')
  			 ->get();*/
      $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}


      $data['courtDivisions']  = DB::table('tbldivision')
         ->where('courtID', '=', $courtSessionId)
         ->get();

          $data['allbanklist']  = DB::table('tblbank')
         ->where('tblbank.courtID', '=', $courtSessionId)
         ->distinct()
         ->join('tblbanklist', 'tblbank.bankID', '=', 'tblbanklist.bankID')
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();

         if(count($data['CourtInfo']) > 0)
      {
        
    $data['allbanklist']  = DB::table('tblbanklist')
         //->where('tblbank.courtID', '=', $data['CourtInfo']->courtid)
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();
      }

     return view('payrollReport_con.index',$data);
  }

  public function getBank(Request $request)
  {
     $court =  $request['courtID'];
     $allbanklist  = DB::table('tblbank')
         ->where('tblbank.courtID', '=', $court)
         //->distinct()
         ->join('tblbanklist', 'tblbank.bankID', '=', 'tblbanklist.bankID')
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();
     return response()->json($allbanklist);   
  }

  public function Retrieve(Request $request)
  {
    $month             = trim($request->input('month'));
    $year              = trim($request->input('year'));
    $court             = trim($request->input('court'));
    $division          = trim($request->input('division'));


    
    $bankID    = trim($request->input('bankName'));
    $bankGroup = trim($request->input('bankGroup'));
     $data['year'] = $year;
    $data['month'] = $month;

  

        Session::put('schmonth', $month." ".$year); 
        
        $data['count_sot'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.divisionID',  '=', $division)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.SOT', '>', 0 )
          ->where('tblpayment_consolidated.rank','!=',2)
          ->count();
    
    if($bankID == '')
    {
     $data['bank'] = '';
    }
      $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
    $this->validate($request, [       
        'month'     => 'required|regex:/^[\pL\s\-]+$/u', 
        'year'      => 'required|integer', 
    ]);
    if($bankID != '')
    {
    $getBank  = DB::table('tblbanklist')
              ->where('bankID',$bankID)
              ->first();

      $bankName = $getBank->bank;
       Session::flash('bank', $bankName);
      }
     
      if($division == '' &&  $bankGroup != '' &&  $bankID != '')
      {
      $data['courtname']  = DB::table('tbl_court')
      ->where('id','=', $court)
      ->first();
      $data['courtDivisions'] = '';
       $data['payroll_detail'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.bank',      '=',$bankID )
          ->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
          ->where('tblpayment_consolidated.rank','!=',2)
          
          ->orderBy('rank','DESC')
          ->orderBy('grade','DESC')
          ->orderBy('step','DESC')
          ->get();
          foreach ($data['payroll_detail'] as $key => $value) {
		            $lis = (array) $value;
		            $lis['hazard'] = $this->OtherParameter($value->staffid, $year,$month,4);
		            $lis['callduty'] = $this->OtherParameter($value->staffid, $year,$month,22);
		            $value = (object) $lis;
		            $data['payroll_detail'][$key]  = $value;
		        }
         return view('payrollReport_con.summary', $data);
      }
      elseif($division != '' &&  $bankGroup != '' &&  $bankID != '')
      {

       $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
       $data['payroll_detail'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.divisionID',  '=', $division)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.bank',      '=',$bankID )
          ->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
          ->where('tblpayment_consolidated.rank','!=',2)
          //->select('*', 'tblper.ID')
          ->orderBy('rank','DESC')
          ->orderBy('grade','DESC')
          ->orderBy('step','DESC')
          ->get();
          foreach ($data['payroll_detail'] as $key => $value) {
		            $lis = (array) $value;
		            $lis['hazard'] = $this->OtherParameter($value->staffid, $year,$month,4);
		            $lis['callduty'] = $this->OtherParameter($value->staffid, $year,$month,22);;
		            $value = (object) $lis;
		            $data['payroll_detail'][$key]  = $value;
		        }
          return view('payrollReport_con.summary', $data);
      }
      if($bankGroup == '' &&  $bankID != '')
      {
        if($division == '')
      {
      $data['courtname']  = DB::table('tbl_court')
      ->where('id','=', $court)
      ->first();
      $data['courtDivisions'] = '';
       $data['payroll_detail'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.bank',      '=',$bankID )
          //->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
          ->where('tblpayment_consolidated.rank','!=',2)
          ->orderBy('rank','DESC')
          ->orderBy('grade','DESC')
          ->orderBy('step','DESC')
          ->get();
          foreach ($data['payroll_detail'] as $key => $value) {
		            $lis = (array) $value;
		            $lis['hazard'] = $this->OtherParameter($value->staffid, $year,$month,4);
		            $lis['callduty'] = $this->OtherParameter($value->staffid, $year,$month,22);;
		            $value = (object) $lis;
		            $data['payroll_detail'][$key]  = $value;
		        }
          return view('payrollReport_con.summary', $data);
      }
      else
      {

       $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
       $data['payroll_detail'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.divisionID',  '=', $division)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.bank',      '=',$bankID )
          //->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
          ->where('tblpayment_consolidated.rank','!=',2)
          ->orderBy('rank','DESC')
          ->orderBy('grade','DESC')
          ->orderBy('step','DESC')
          ->get();
          foreach ($data['payroll_detail'] as $key => $value) {
		            $lis = (array) $value;
		            $lis['hazard'] = $this->OtherParameter($value->staffid, $year,$month,4);
		            $lis['callduty'] = $this->OtherParameter($value->staffid, $year,$month,22);;
		            $value = (object) $lis;
		            $data['payroll_detail'][$key]  = $value;
		        }
        return view('payrollReport_con.summary', $data);
      }
      }

      if($bankGroup == '' && $bankID == '')
      {
        if($division == '')
      {
      $data['courtname']  = DB::table('tbl_court')
      ->where('id','=', $court)
      ->first();
      $data['courtDivisions'] = '';
       $data['payroll_detail'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.rank','!=',2)
          ->orderBy('rank','DESC')
          ->orderBy('grade','DESC')
          ->orderBy('step','DESC')
          ->get();
          foreach ($data['payroll_detail'] as $key => $value) {
		            $lis = (array) $value;
		            $lis['hazard'] = $this->OtherParameter($value->staffid, $year,$month,4);
		            $lis['callduty'] = $this->OtherParameter($value->staffid, $year,$month,22);;
		            $value = (object) $lis;
		            $data['payroll_detail'][$key]  = $value;
		        }
        return view('payrollReport_con.summary', $data);
      }
      else
      {

       $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
       $data['payroll_detail'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.divisionID',  '=', $division)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.rank','!=',2)
          ->orderBy('rank','DESC')
          ->orderBy('grade','DESC')
          ->orderBy('step','DESC')
          ->get();
          foreach ($data['payroll_detail'] as $key => $value) {
		            $lis = (array) $value;
		            $lis['hazard'] = $this->OtherParameter($value->staffid, $year,$month,4);
		            $lis['callduty'] = $this->OtherParameter($value->staffid, $year,$month,22);;
		            $value = (object) $lis;
		            $data['payroll_detail'][$key]  = $value;
		        }
        return view('payrollReport_con.summary', $data);
      }
      }


    return view('payrollReport.summary', $data);
  }

  public function BulkPayRoll(Request $request)
  {
    $this->validate($request,[       
        'month'     => 'required|regex:/^[\pL\s\-]+$/u', 
        'year'      => 'required|integer'  
    ]);
    $month              = trim($request->input('month'));
    $year               = trim($request->input('year'));
    $court             = trim($request->input('court'));
    $division          = trim($request->input('division'));

    $division           = $this->division;
    Session::put('schmonth', $month." ".$year);
    DB::enableQueryLog();
    /*$data['getBank']    = DB::table('tblpayment_consolidated')
          ->where('month', $month)
          ->where('year', $year)
          ->select('bank', 'bankGroup')
          ->distinct()
          ->orderBy('bank','ASC')
          ->orderBy('bankGroup', 'ASC')
          ->get();*/
    //dd(DB::getQueryLog());
    $data['month']      = $month;
    $data['year']       = $year;
    $data['division']   = $this->division;
    return view('payrollReport.bulkPayroll', $data);
  }
  
  

  public function arrearsOearn($court,$fileNo,$year,$month)
  {
    //$fNo = str_replace('-', '/', $fileNo);
    $fNo = $fileNo;
    $data['courtName'] = DB::table('tbl_court')->where('id','=',$court)->first();
    $data['fn'] = DB::table('tblper')->where('ID','=',$fileNo)->first();
 $check = DB::table('tblarrears')
          ->where('month',     '=', $month)
          ->where('year',      '=', $year)
          ->where('courtID',   '=', $court)
          ->where('staffid',      '=',$fNo )
          ->count();
          if($check == 0)
          {
            return back()->with('err', 'No Arrears Found');
          }

  
          $data['oarrears'] = DB::table('tblarrears')
/*->select('fileNo','month','year','oldGrade','OldStep','newGrade','newStep','newBasic','oldBasic','oldTax','newTax','oldPeculiar','newPeculiar','oldLeave_bonus',
'newLeave_bonus','oldPension','newPension','oldNhf','newNhf','oldUnionDues','newUnionDues','dueDate','date as date_computed')*/
          ->where('month',     '=', $month)
          ->where('year',      '=', $year)
          ->where('courtID',   '=', $court)
          ->where('staffid',      '=',$fNo )
          ->first();
          
          $data['overDues'] = DB::table('tblarrears_overdue')
          ->join('tblstaff_for_arrears_overdue','tblstaff_for_arrears_overdue.due_date','=','tblarrears_overdue.dueDate')
          ->where('tblarrears_overdue.month',     '=', $month)
          ->where('tblarrears_overdue.year',      '=', $year)
          ->where('tblarrears_overdue.courtID',   '=', $court)
          ->where('tblarrears_overdue.staffid',      '=',$fNo )
          ->where('tblstaff_for_arrears_overdue.month_payment',     '=', $month)
          ->where('tblstaff_for_arrears_overdue.year_payment',      '=', $year)
          ->where('tblstaff_for_arrears_overdue.staffid',      '=',$fNo )
          ->get();
          
          //dd($data['oarrears']);
          	$activemonth = date("n", strtotime($data['oarrears']->month));
          	$data['varimonth'] = $this->dateDiff($data['oarrears']->year."-".$activemonth."-1", $data['oarrears']->dueDate);
          	
          	$activemonthOverdue = date("n", strtotime($data['oarrears']->month));
          	$data['varimonthOver'] = $this->dateDiff($data['oarrears']->year."-".$activemonthOverdue."-1", $data['oarrears']->dueDate);

		//dd( $data['overDue']);

		
          return view('payrollReport_con/otherArrears', $data);
  }
  
  
   public function arrearsOearnTest($court,$fileNo,$year,$month)
  {
    //$fNo = str_replace('-', '/', $fileNo);
    $fNo = $fileNo;
    $data['courtName'] = DB::table('tbl_court')->where('id','=',$court)->first();
    $data['fn'] = DB::table('tblper')->where('ID','=',$fileNo)->first();
 $check = DB::table('tblarrears')
          ->where('month',     '=', $month)
          ->where('year',      '=', $year)
          ->where('courtID',   '=', $court)
          ->where('staffid',      '=',$fNo )
          ->count();
          if($check == 0)
          {
            return back()->with('err', 'No Arrears Found');
          }

  
          $data['oarrears'] = DB::table('tblarrears')
/*->select('fileNo','month','year','oldGrade','OldStep','newGrade','newStep','newBasic','oldBasic','oldTax','newTax','oldPeculiar','newPeculiar','oldLeave_bonus',
'newLeave_bonus','oldPension','newPension','oldNhf','newNhf','oldUnionDues','newUnionDues','dueDate','date as date_computed')*/
          ->where('month',     '=', $month)
          ->where('year',      '=', $year)
          ->where('courtID',   '=', $court)
          ->where('staffid',      '=',$fNo )
          ->first();
          
          $data['overDues'] = DB::table('tblarrears_overdue')
          ->join('tblstaff_for_arrears_overdue','tblstaff_for_arrears_overdue.due_date','=','tblarrears_overdue.dueDate')
          ->where('tblarrears_overdue.month',     '=', $month)
          ->where('tblarrears_overdue.year',      '=', $year)
          ->where('tblarrears_overdue.courtID',   '=', $court)
          ->where('tblarrears_overdue.staffid',      '=',$fNo )
          ->where('tblstaff_for_arrears_overdue.month_payment',     '=', $month)
          ->where('tblstaff_for_arrears_overdue.year_payment',      '=', $year)
          ->where('tblstaff_for_arrears_overdue.staffid',      '=',$fNo )
          ->get();
          
          //dd($data['oarrears']);
          	$activemonth = date("n", strtotime($data['oarrears']->month));
          	$data['varimonth'] = $this->dateDiff($data['oarrears']->year."-".$activemonth."-1", $data['oarrears']->dueDate);
          	
          	$activemonthOverdue = date("n", strtotime($data['oarrears']->month));
          	$data['varimonthOver'] = $this->dateDiff($data['oarrears']->year."-".$activemonthOverdue."-1", $data['oarrears']->dueDate);

		//dd( $data['overDue']);
          return view('payrollReport_con/otherArrears_17_06_2019', $data);
  }
  
  
  
function dateDiff($date2, $date1)
  {
    list($year2, $mth2, $day2) = explode("-", $date2);
    list($year1, $mth1, $day1) = explode("-", $date1);
    if ($year1 > $year2) dd('Invalid Input - dates do not match');
    $days_month = 0;
    $days_month = cal_days_in_month(CAL_GREGORIAN, $mth1, $year1);
    $day_diff = 0;

    if($year2 == $year1){
      $mth_diff = $mth2 - $mth1;
    }
    else{
      $yr_diff = $year2 - $year1;
      $mth_diff = (12 * $yr_diff) - $mth1 + $mth2;
    }
    if($day1 > 1){
      $mth_diff--;
      //dd($mth1.",".$year1);
      $day_diff = $days_month - $day1 + 1;
    }

    $result = array('months'=>$mth_diff, 'days'=> $day_diff, 'days_of_month'=>$days_month);
    return($result);
  } //end
  
  
  public function payrollBreakdown()
  {
     $data['courts'] =  DB::table('tbl_court')->get();
      $courtSessionId = session('anycourt');
      $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}


      $data['courtDivisions']  = DB::table('tbldivision')
         ->where('courtID', '=', $courtSessionId)
         ->get();

          $data['allbanklist']  = DB::table('tblbank')
         ->where('tblbank.courtID', '=', $courtSessionId)
         ->distinct()
         ->join('tblbanklist', 'tblbank.bankID', '=', 'tblbanklist.bankID')
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();

         if(count($data['CourtInfo']) > 0)
      {
        
    $data['allbanklist']  = DB::table('tblbanklist')
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();
      }

     return view('payrollReport_con.payrollBreakdown',$data);
  }
  
  public function payrollBreakdownReport(Request $request)
  {
    $month             = trim($request->input('month'));
    $year              = trim($request->input('year'));
    $court             = trim($request->input('court'));
    $division          = trim($request->input('division'));


    
    $bankID    = trim($request->input('bankName'));
    $bankGroup = trim($request->input('bankGroup'));
    
    $data['count_sot'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
         //->where('tblpayment_consolidated.divisionID',  '=', $division)
          //->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.SOT', '>', 0 )
          ->where('tblpayment_consolidated.rank','!=',2)
          ->count();
    
    
    $data['year'] = $year;
    $data['month'] = $month;

        Session::put('schmonth', $month." ".$year); 
    
    if($bankID == '')
    {
     $data['bank'] = '';
    }
      $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

    $this->validate($request, [       
        'month'     => 'required|regex:/^[\pL\s\-]+$/u', 
        'year'      => 'required|integer', 
    ]);
    if($bankID != '')
    {
    $getBank  = DB::table('tblbanklist')
              ->where('bankID',$bankID)
              ->first();

      $bankName = $getBank->bank;
       Session::flash('bank', $bankName);
      }
     
      if($division == '' &&  $bankGroup != '' &&  $bankID != '')
      {
      $data['courtname']  = DB::table('tbl_court')
      ->where('id','=', $court)
      ->first();
      $data['courtDivisions'] = '';
       $data['payroll_detail'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.bank',      '=',$bankID )
          ->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
          ->where('tblpayment_consolidated.rank','!=',2)
          ->orderBy('rank','DESC')
          ->orderBy('grade','DESC')
          ->orderBy('step','DESC')
          ->get();
         return view('payrollReport_con.payrollReportBreakdown', $data);
      }
      elseif($division != '' &&  $bankGroup != '' &&  $bankID != '')
      {

       $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
       $data['payroll_detail'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.divisionID',  '=', $division)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.bank',      '=',$bankID )
          ->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
          ->where('tblpayment_consolidated.rank','!=',2)
          //->select('*', 'tblper.ID')
          ->orderBy('rank','DESC')
          ->orderBy('grade','DESC')
          ->orderBy('step','DESC')
          ->get();
          return view('payrollReport_con.payrollReportBreakdown', $data);
      }
      if($bankGroup == '' &&  $bankID != '')
      {
        if($division == '')
      {
      $data['courtname']  = DB::table('tbl_court')
      ->where('id','=', $court)
      ->first();
      $data['courtDivisions'] = '';
       $data['payroll_detail'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.bank',      '=',$bankID )
          //->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
          ->where('tblpayment_consolidated.rank','!=',2)
          ->orderBy('rank','DESC')
          ->orderBy('grade','DESC')
          ->orderBy('step','DESC')
          ->get();
          return view('payrollReport_con.payrollReportBreakdown', $data);
      }
      else
      {

       $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
       $data['payroll_detail'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.divisionID',  '=', $division)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.bank',      '=',$bankID )
          ->where('tblpayment_consolidated.rank','!=',2)
          ->orderBy('rank','DESC')
          ->orderBy('grade','DESC')
          ->orderBy('step','DESC')
          ->get();
        return view('payrollReport_con.payrollReportBreakdown', $data);
      }
      }

      if($bankGroup == '' && $bankID == '')
      {
        if($division == '')
      {
      $data['courtname']  = DB::table('tbl_court')
      ->where('id','=', $court)
      ->first();
      $data['courtDivisions'] = '';
       $data['payroll_detail'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.rank','!=',2)
          ->orderBy('rank','DESC')
          ->orderBy('grade','DESC')
          ->orderBy('step','DESC')
          ->get();
        return view('payrollReport_con.payrollReportBreakdown', $data);
      }
      else
      {

        $data['courtname'] = '';
        $data['courtDivisions']  = DB::table('tbl_court')
        ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
        ->where('tbl_court.id','=',$court)
        ->where('tbldivision.divisionID','=',$division)
        ->first();
        $data['payroll_detail'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.divisionID',  '=', $division)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.rank','!=',2)
          ->orderBy('rank','DESC')
          ->orderBy('grade','DESC')
          ->orderBy('step','DESC')
          ->get();
return view('payrollReport_con.payrollReportBreakdown', $data);
      }
      }

    return view('payrollReport.payrollReportBreakdown', $data);
  }
  
  Public function OtherParameter($staffid, $year,$month,$pera){
	$List= DB::Select("SELECT IFNULL(sum(`amount`),0) as Taxable FROM `tblotherEarningDeduction` WHERE `CVID`='$pera' and `staffid`='$staffid' and `month`='$month' and `year`='$year'");
	if($List)return $List[0]->Taxable;
	return 0;
	}
Public function ThisMonthEarn($staffid, $year,$month){
	$List= DB::Select("SELECT IFNULL(sum(`NetPay`),0) as amount FROM `tblpayment_consolidated`  WHERE `staffid`='$staffid' and `year`='$year' AND `month`='$month'");
	if($List)return $List[0]->amount;
	return 0;
	}
Public function ThisMonthEarnExludeSP($staffid, $year,$month){
	$List= DB::Select("SELECT IFNULL(sum(`NetPay`-`SOT`+`TAX_SOT`),0) as amount FROM `tblpayment_consolidated`  WHERE `staffid`='$staffid' and `year`='$year' AND `month`='$month'");
	if($List)return $List[0]->amount;
	return 0;
	}
	Public function ThisPension($staffid, $year,$month){
	$List= DB::Select("SELECT IFNULL(sum(`PEN`),0) as amount FROM `tblpayment_consolidated`  WHERE `staffid`='$staffid' and `year`='$year' AND `month`='$month'");
	if($List)return $List[0]->amount;
	return 0;
	}
Public function VariationRemarks($staffid, $year1,$month1, $year2,$month2){
	$List= DB::Select("SELECT `arrears_type` FROM `tblstaff_for_arrears` WHERE `staffid`='$staffid' and(( `year_payment`='$year1' and `month_payment`='$month1') or ( `year_payment`='$year2' and `month_payment`='$month2'))");
	if($List)return $List[0]->arrears_type;
	$List= DB::Select("SELECT `arrears_type` FROM `tblstaff_for_arrears_overdue` WHERE `staffid`='$staffid' and(( `year_payment`='$year1' and `month_payment`='$month1') or ( `year_payment`='$year2' and `month_payment`='$month2'))");
    if($List)return $List[0]->arrears_type;
	return "Others";
	}
public function CompareEarning(Request $request)
  {
    $month1             = $request->input('month1');
    $month2             = $request->input('month2');
    $year1             = $request->input('year1');
    $year2             = $request->input('year2');
    $data['sp']  = $request->input('sp');
    $data['month1']  = $request->input('month1');
    $data['month2']             = $request->input('month2');
    $data['year1']            = $request->input('year1');
    $data['year2']             = $request->input('year2');
     $data['courts'] =  DB::table('tbl_court')->get();
      $courtSessionId = session('anycourt');
     
      $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
      $data['courtDivisions']  = DB::table('tbldivision')
         ->where('courtID', '=', $courtSessionId)
         ->get();
$affectedStaff= DB::Select("SELECT * FROM `tblpayment_consolidated` WHERE (`year`='$year1' and `month`='$month1') or (`year`='$year2' and `month`='$month2') group by `staffid`");
         //dd($year2.$month2 );
    $data['record'] = [];
    foreach($affectedStaff as $v){
      $total1=($data['sp']!='1')? $this->ThisMonthEarn($v->staffid, $year1,$month1) : $this->ThisMonthEarnExludeSP($v->staffid, $year1,$month1);
      $total2=($data['sp']!='1')? $this->ThisMonthEarn($v->staffid, $year2,$month2) : $this->ThisMonthEarnExludeSP($v->staffid, $year2,$month2);
      $diff=$total1-$total2;
      if(round($diff,2) !=0.00)$data['record'][]=array('Names'=>$v->name,'net1'=>$total1,'net2'=>$total2,'diff'=>$diff);
    }
    //dd( $data['record']);
     return view('payrollReport_con.compare_earning',$data);
  }
  public function ComparePension(Request $request)
  {
    $month1             = $request->input('month1');
    $month2             = $request->input('month2');
    $year1             = $request->input('year1');
    $year2             = $request->input('year2');
    $data['sp']  = $request->input('sp');
    $data['month1']  = $request->input('month1');
    $data['month2']             = $request->input('month2');
    $data['year1']            = $request->input('year1');
    $data['year2']             = $request->input('year2');
     $data['courts'] =  DB::table('tbl_court')->get();
      $courtSessionId = session('anycourt');
     
      $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
      $data['courtDivisions']  = DB::table('tbldivision')
         ->where('courtID', '=', $courtSessionId)
         ->get();
$affectedStaff= DB::Select("SELECT * FROM `tblpayment_consolidated` WHERE (`year`='$year1' and `month`='$month1') or (`year`='$year2' and `month`='$month2') group by `staffid`");
         //dd($year2.$month2 );
    $data['record'] = [];
    foreach($affectedStaff as $v){
      $total1=$this->ThisPension($v->staffid, $year1,$month1) ;
      $total2=$this->ThisPension($v->staffid, $year2,$month2) ;
      $diff=$total1-$total2;
      $reason=$this->VariationRemarks($v->staffid, $year1,$month1, $year2,$month2);
      if(round($diff,2) !=0.00)$data['record'][]=array('Names'=>$v->name,'net1'=>$total1,'net2'=>$total2,'diff'=>$diff,'reason'=>$reason);
    }
    //dd( $data['record']);
     return view('payrollReport_con.compare_pension',$data);
  }
  
  public function newPayrollIndex()
  {
       $data['courts'] =  DB::table('tbl_court')->get();
      $courtSessionId = session('anycourt');
     /*$allbanklist  = DB::table('tblper')
  			 ->where('tblper.divisionID', '=', $this->divisionID)
  			 ->select('tblbanklist.bank', 'tblper.bankID')
  			 ->distinct()
  			 ->join('tblbanklist', 'tblper.bankID', '=', 'tblbanklist.bankID')
  			 ->orderBy('bank', 'Asc')
  			 ->get();*/
      $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}


      $data['courtDivisions']  = DB::table('tbldivision')
         ->where('courtID', '=', $courtSessionId)
         ->get();

          $data['allbanklist']  = DB::table('tblbank')
         ->where('tblbank.courtID', '=', $courtSessionId)
         ->distinct()
         ->join('tblbanklist', 'tblbank.bankID', '=', 'tblbanklist.bankID')
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();

         if(count($data['CourtInfo']) > 0)
      {
        
    $data['allbanklist']  = DB::table('tblbanklist')
         //->where('tblbank.courtID', '=', $data['CourtInfo']->courtid)
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();
      }

     return view('payrollReport_con.newPayrolIndex',$data);
  }
  
  
  public function newPayrollReport(Request $request)
  {
    $month             = trim($request->input('month'));
    $year              = trim($request->input('year'));
    $court             = trim($request->input('court'));
    $division          = trim($request->input('division'));


    
    $bankID    = trim($request->input('bankName'));
    $bankGroup = trim($request->input('bankGroup'));
     $data['year'] = $year;
    $data['month'] = $month;

  

        Session::put('schmonth', $month." ".$year); 
        
        $data['count_sot'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.divisionID',  '=', $division)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.SOT', '>', 0 )
          ->where('tblpayment_consolidated.rank','!=',2)
          ->count();
    
    if($bankID == '')
    {
     $data['bank'] = '';
    }
      $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
    $this->validate($request, [       
        'month'     => 'required|regex:/^[\pL\s\-]+$/u', 
        'year'      => 'required|integer', 
    ]);
    if($bankID != '')
    {
    $getBank  = DB::table('tblbanklist')
              ->where('bankID',$bankID)
              ->first();

      $bankName = $getBank->bank;
       Session::flash('bank', $bankName);
      }
      
      $data['courtname']  = DB::table('tbl_court')
      ->where('id','=', $court)
      ->first();
      $data['courtDivisions'] = '';
     
     if($bankID == '')
     {
       $data['payroll_detail'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          //->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.rank','!=',2)
          
          ->orderBy('rank','DESC')
          ->orderBy('grade','DESC')
          ->orderBy('step','DESC')
          ->get();
          foreach ($data['payroll_detail'] as $key => $value) {
		            $lis = (array) $value;
		            $lis['hazard'] = $this->OtherParameter($value->staffid, $year,$month,4);
		            $lis['callduty'] = $this->OtherParameter($value->staffid, $year,$month,22);
		            $value = (object) $lis;
		            $data['payroll_detail'][$key]  = $value;
		        }
		        return view('payrollReport_con.newPayrollReport', $data);
     }
     else
     {
         $data['payroll_detail'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          //->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.rank','!=',2)
          ->where('tblpayment_consolidated.bank',      '=',$bankID )
          ->orderBy('rank','DESC')
          ->orderBy('grade','DESC')
          ->orderBy('step','DESC')
          ->get();
          foreach ($data['payroll_detail'] as $key => $value) {
		            $lis = (array) $value;
		            $lis['hazard'] = $this->OtherParameter($value->staffid, $year,$month,4);
		            $lis['callduty'] = $this->OtherParameter($value->staffid, $year,$month,22);
		            $value = (object) $lis;
		            $data['payroll_detail'][$key]  = $value;
		        }
       return view('payrollReport_con.newPayrollReport', $data);  
     }
        
     
  

    //return view('payrollReport.summary', $data);
  }

  
}