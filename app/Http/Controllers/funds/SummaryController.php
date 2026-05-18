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

class SummaryController extends ParentController
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
         $data['courtDivisions']  = DB::table('tbldivision')
         ->where('courtID', '=', $courtSessionId)
         ->get();

          $data['allbanklist']  = DB::table('tblbank')
         ->where('tblbank.courtID', '=', $courtSessionId)
         ->distinct()
         ->join('tblbanklist', 'tblbank.bankID', '=', 'tblbanklist.bankID')
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();

  return view('summary.index',$data);    
}

public function Retrieve(Request $request)
{
    $month     = trim($request->input('month'));
    $year      = trim($request->input('year'));
    $bankID    = trim($request->input('bankName'));
    $bankGroup = trim($request->input('bankGroup'));
    $warrant   = trim($request->input('warrant'));
    $division  = trim($request->input('division'));
     $court    = trim($request->input('court'));
  
    $this->validate($request,[       
          'month'     => 'required|string', 
          'year'      => 'required|integer', 
          'bankName'  => 'required|integer', 
          //'bankGroup' => 'required|integer',
          'warrant'   => 'required|regex:/[a-zA-Z.]/'   
    ]);
    $getBank  = DB::table('tblbanklist')
              ->where('bankID', $bankID)
              ->first();
    $bankName = $getBank -> bank;
    /*$data['summary_detail'] = DB::table('tblpayment')
            ->where('tblpayment.month',     '=', $month)
            ->where('tblpayment.year',      '=', $year)
            ->where('tblpayment.division',  '=', $division)
            ->where('tblpayment.bank',      '=', $bankName )
            ->where('tblpayment.bankGroup', '=', $bankGroup)
            ->orderBy('basic_salary','DESC')
            ->get();*/



       if($division == '' &&  $bankGroup != '')
      {
      $data['courtname']  = DB::table('tbl_court')
      ->where('id','=', $court)
      ->first();
      $data['courtDivisions'] = '';
       $data['summary_detail'] = DB::table('tblpayment')
          ->where('tblpayment.month',     '=', $month)
          ->where('tblpayment.year',      '=', $year)
          ->where('tblpayment.courtID',  '=', $court)
          ->where('tblpayment.bank',      '=',$bankName )
          ->where('tblpayment.bankGroup', '=', $bankGroup)
          ->orderBy('basic_salary','DESC')
          ->get();
      }
      elseif($division != '' &&  $bankGroup != '')
      {

       $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
       $data['summary_detail'] = DB::table('tblpayment')
          ->where('tblpayment.month',     '=', $month)
          ->where('tblpayment.year',      '=', $year)
          ->where('tblpayment.division',  '=', $division)
          ->where('tblpayment.courtID',  '=', $court)
          ->where('tblpayment.bank',      '=',$bankName )
          ->where('tblpayment.bankGroup', '=', $bankGroup)
          ->orderBy('basic_salary','DESC')
          ->get();
      }
      if($bankGroup == '')
      {
        if($division == '')
      {
      $data['courtname']  = DB::table('tbl_court')
      ->where('id','=', $court)
      ->first();
      $data['courtDivisions'] = '';
       $data['summary_detail'] = DB::table('tblpayment')
          ->where('tblpayment.month',     '=', $month)
          ->where('tblpayment.year',      '=', $year)
          ->where('tblpayment.courtID',  '=', $court)
          ->where('tblpayment.bank',      '=',$bankName )
          //->where('tblpayment.bankGroup', '=', $bankGroup)
          ->orderBy('basic_salary','DESC')
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
       $data['summary_detail'] = DB::table('tblpayment')
          ->where('tblpayment.month',     '=', $month)
          ->where('tblpayment.year',      '=', $year)
          ->where('tblpayment.division',  '=', $division)
          ->where('tblpayment.courtID',  '=', $court)
          ->where('tblpayment.bank',      '=',$bankName )
          //->where('tblpayment.bankGroup', '=', $bankGroup)
          ->orderBy('basic_salary','DESC')
          ->get();
      }
      }



    Session::put('schmonth', $month." ".$year); 
    Session::put('bank', $bankName ." ".$bankGroup);
    Session::put('warrant', $warrant);
    return view('summary.summary', $data);
  }
  
}