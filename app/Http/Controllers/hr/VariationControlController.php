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

class VariationControlController extends ParentController
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

  public function index()
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

     return view('variationControl.indexVariation',$data);
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

  public function load(Request $request)
  {
    $month             = trim($request->input('month'));
    $year              = trim($request->input('year'));
    $court             = trim($request->input('court'));
    $division          = trim($request->input('division'));


    
    $bankID    = trim($request->input('bankName'));
    $bankGroup = trim($request->input('bankGroup'));


        Session::put('schmonth', $month." ".$year); 
        $activeMonth = DB::table('tblactivemonth')->where('courtID','=',9)->first();
        $month_number = date("n",strtotime($month));
        $number = cal_days_in_month(CAL_GREGORIAN, $month_number, $year);
        if($month_number < 10)
        {
         $init = 0;
        }
        else
        {
        $init = '';
        }
       
        $data['activeDate'] = "$activeMonth->year-$init$month_number-$number";
        
       // dd($data['activeDate']);
 
    
    if($bankID == '')
    {
     $data['bank'] = '';
    }
      $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

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
       Session::flash('bank', $bankName);
      }
     
      if($division == '' &&  $bankGroup != '' &&  $bankID != '')
      {
      $data['courtname']  = DB::table('tbl_court')
      ->where('id','=', $court)
      ->first();
      $data['courtDivisions'] = '';
       $data['payroll_detail'] = DB::table('tblpayment_consolidated')
       ->join('tblper','tblper.ID','=','tblpayment_consolidated.staffid')
       ->select('*','tblper.grade as staffGrade','tblper.step as staffStep','tblper.employee_type as emptype')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.bank',      '=',$bankID )
          ->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
          ->where('tblpayment_consolidated.variation_view', '=', 1)
          ->where('tblpayment_consolidated.rank','!=',2)
          ->orderBy('tblpayment_consolidated.rank','DESC')
          ->orderBy('tblpayment_consolidated.grade','DESC')
          ->orderBy('tblpayment_consolidated.step','DESC')
          ->get();
         return view('variationControl.detailVariation', $data);
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
        ->join('tblper','tblper.ID','=','tblpayment_consolidated.staffid')
       ->select('*','tblper.grade as staffGrade','tblper.step as staffStep','tblper.employee_type as emptype')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.divisionID',  '=', $division)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.bank',      '=',$bankID )
          ->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
          ->where('tblpayment_consolidated.variation_view', '=', 1)
          ->where('tblpayment_consolidated.rank','!=',2)
          //->select('*', 'tblper.ID')
          ->orderBy('tblpayment_consolidated.rank','DESC')
          ->orderBy('tblpayment_consolidated.grade','DESC')
          ->orderBy('tblpayment_consolidated.step','DESC')
          ->get();
          return view('variationControl.detailVariation', $data);
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
        ->join('tblper','tblper.ID','=','tblpayment_consolidated.staffid')
       ->select('*','tblper.grade as staffGrade','tblper.step as staffStep','tblper.employee_type as emptype')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.bank',      '=',$bankID )
          //->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
          ->where('tblpayment_consolidated.variation_view', '=', 1)
          ->orderBy('tblpayment_consolidated.rank','DESC')
          ->orderBy('tblpayment_consolidated.grade','DESC')
          ->orderBy('tblpayment_consolidated.step','DESC')
          ->get();
          return view('variationControl.detailVariation', $data);
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
        ->join('tblper','tblper.ID','=','tblpayment_consolidated.staffid')
       ->select('*','tblper.grade as staffGrade','tblper.step as staffStep','tblper.employee_type as emptype')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.divisionID',  '=', $division)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.bank',      '=',$bankID )
          //->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
          ->where('tblpayment_consolidated.variation_view', '=', 1)
          ->where('tblpayment_consolidated.rank','!=',2)
          ->orderBy('tblpayment_consolidated.rank','DESC')
          ->orderBy('tblpayment_consolidated.grade','DESC')
          ->orderBy('tblpayment_consolidated.step','DESC')
          ->get();
        return view('variationControl.detailVariation', $data);
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
        ->join('tblper','tblper.ID','=','tblpayment_consolidated.staffid')
       ->select('*','tblper.grade as staffGrade','tblper.step as staffStep','tblper.employee_type as emptype')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          //->where('tblpayment_consolidated.bank',      '=',$bankName )
          //->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
          ->where('tblpayment_consolidated.variation_view', '=', 1)
          ->where('tblpayment_consolidated.rank','!=',2)
          ->orderBy('tblpayment_consolidated.rank','DESC')
          ->orderBy('tblpayment_consolidated.grade','DESC')
          ->orderBy('tblpayment_consolidated.step','DESC')
          ->get();
        return view('variationControl.detailVariation', $data);
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
        ->join('tblper','tblper.ID','=','tblpayment_consolidated.staffid')
       ->select('*','tblper.grade as staffGrade','tblper.step as staffStep','tblper.employee_type as emptype')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.divisionID',  '=', $division)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.rank','!=',2)
          //->where('tblpayment_consolidated.bank',      '=',$bankID )
          //->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
          ->where('tblpayment_consolidated.variation_view', '=', 1)
          ->orderBy('tblpayment_consolidated.rank','DESC')
          ->orderBy('tblpayment_consolidated.grade','DESC')
          ->orderBy('tblpayment_consolidated.step','DESC')
          ->get();
return view('variationControl.detailVariation', $data);
      }
      }


    //dd($data['court'] );
    
   
          //dd($data['payroll_detail']);
  
    
    return view('variationControl.detailVariation', $data);
  }

  
  
  
}