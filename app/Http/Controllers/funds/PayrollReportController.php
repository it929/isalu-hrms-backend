<?php

namespace App\Http\Controllers\funds;

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

class PayrollReportController extends ParentController
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

         $data['courtDivisions']  = DB::table('tbldivision')
         ->where('courtID', '=', $courtSessionId)
         ->get();

          $data['allbanklist']  = DB::table('tblbank')
         ->where('tblbank.courtID', '=', $courtSessionId)
         ->distinct()
         ->join('tblbanklist', 'tblbank.bankID', '=', 'tblbanklist.bankID')
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();

     return view('payrollReport.index',$data);    
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
    
    if($bankID == '')
    {
     $data['bank'] = '';
    }

     //dd($bankGroup);

   // $division  = $this->division;
    $this->validate($request, [       
        'month'     => 'required|regex:/^[\pL\s\-]+$/u', 
        'year'      => 'required|integer', 
        //'bankName'  => 'required|integer', 
        //'bankGroup' => 'required|integer'   
    ]);
    if($bankID != '')
    {
    $getBank  = DB::table('tblbanklist')
              ->where('bankID',$bankID)
              ->first();

      $bankName = $getBank->bank;
      }
      if($division == '' &&  $bankGroup != '' &&  $bankID != '')
      {
      $data['courtname']  = DB::table('tbl_court')
      ->where('id','=', $court)
      ->first();
      $data['courtDivisions'] = '';
       $data['payroll_detail'] = DB::table('tblpayment')
          ->where('tblpayment.month',     '=', $month)
          ->where('tblpayment.year',      '=', $year)
          ->where('tblpayment.courtID',  '=', $court)
          ->where('tblpayment.bank',      '=',$bankID )
          ->where('tblpayment.bankGroup', '=', $bankGroup)
          ->orderBy('Bs','DESC')
          ->get();
      }
      elseif($division != '' &&  $bankGroup != '' &&  $bankID != '')
      {

       $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
       $data['payroll_detail'] = DB::table('tblpayment')
          ->where('tblpayment.month',     '=', $month)
          ->where('tblpayment.year',      '=', $year)
          ->where('tblpayment.divisionID',  '=', $division)
          ->where('tblpayment.courtID',  '=', $court)
          ->where('tblpayment.bank',      '=',$bankID )
          ->where('tblpayment.bankGroup', '=', $bankGroup)
          ->orderBy('Bs','DESC')
          ->get();
      }
      if($bankGroup == '' &&  $bankID != '')
      {
        if($division == '')
      {
      $data['courtname']  = DB::table('tbl_court')
      ->where('id','=', $court)
      ->first();
      $data['courtDivisions'] = '';
       $data['payroll_detail'] = DB::table('tblpayment')
          ->where('tblpayment.month',     '=', $month)
          ->where('tblpayment.year',      '=', $year)
          ->where('tblpayment.courtID',  '=', $court)
          ->where('tblpayment.bank',      '=',$bankID )
          //->where('tblpayment.bankGroup', '=', $bankGroup)
          ->orderBy('Bs','DESC')
          ->get();
      }
      else
      {

       $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
       $data['payroll_detail'] = DB::table('tblpayment')
          ->where('tblpayment.month',     '=', $month)
          ->where('tblpayment.year',      '=', $year)
          ->where('tblpayment.divisionID',  '=', $division)
          ->where('tblpayment.courtID',  '=', $court)
          ->where('tblpayment.bank',      '=',$bankID )
          //->where('tblpayment.bankGroup', '=', $bankGroup)
          ->orderBy('Bs','DESC')
          ->get();
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
       $data['payroll_detail'] = DB::table('tblpayment')
          ->where('tblpayment.month',     '=', $month)
          ->where('tblpayment.year',      '=', $year)
          ->where('tblpayment.courtID',  '=', $court)
          //->where('tblpayment.bank',      '=',$bankName )
          //->where('tblpayment.bankGroup', '=', $bankGroup)
          ->orderBy('Bs','DESC')
          ->get();
      }
      else
      {

       $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
       $data['payroll_detail'] = DB::table('tblpayment')
          ->where('tblpayment.month',     '=', $month)
          ->where('tblpayment.year',      '=', $year)
          ->where('tblpayment.divisionID',  '=', $division)
          ->where('tblpayment.courtID',  '=', $court)
          //->where('tblpayment.bank',      '=',$bankID )
          //->where('tblpayment.bankGroup', '=', $bankGroup)
          ->orderBy('Bs','DESC')
          ->get();
      }
      }


    //dd($data['court'] );
    
   
          //dd($data['payroll_detail']);
    Session::put('schmonth', $month." ".$year); 
    //Session::put('bank', $bankName ." ".$bankGroup);
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
    /*$data['getBank']    = DB::table('tblpayment')
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

}