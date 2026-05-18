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

class EpaymentController extends ParentController
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
         $data['courtDivisions']  = DB::table('tbldivision')
         ->where('courtID', '=', $courtSessionId)
         ->get();

          $data['allbanklist']  = DB::table('tblbank')
         ->where('tblbank.courtID', '=', $courtSessionId)
         ->distinct()
         ->join('tblbanklist', 'tblbank.bankID', '=', 'tblbanklist.bankID')
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();
  return view('epayment.index',$data);    
}
public function Retrieve(Request $request)
{
  $month     = trim($request->input('month'));
  $year      = trim($request->input('year'));
  $bankID    = trim($request->input('bankName'));
  $bankGroup = trim($request->input('bankGroup'));
  $division  = trim($request->input('division'));
  $court     = trim($request->input('court'));
  $this->validate($request,[       
    'month'     => 'required|regex:/^[\pL\s\-]+$/u', 
    'year'      => 'required|integer', 
    'bankName'  => 'required|integer', 
    //'bankGroup' => 'required|integer'   
  ]);
  Session::put('serialNo', 1);
  Session::put('bankID', $bankID);
  Session::put('bankGroup', $bankGroup);
  $getBank = DB::table('tblbanklist')
           ->where('bankID', $bankID)
           ->first();
  $bankName = $getBank->bank;
  $bankCode = DB::table('tblbank')
            ->where('bankID',$bankID)
            ->first();
  Session::put('bankCode', $bankCode->bank_code);
  Session::put('sortCode', $bankCode->sort_code);

  if($division == '' &&  $bankGroup != '')
      {
      $data['courtname']  = DB::table('tbl_court')
      ->where('id','=', $court)
      ->first();
      $data['courtDivisions'] = '';
       $data['epayment_detail'] = DB::table('tblpayment') 
        ->where('tblpayment.month',     '=', $month)
        ->where('tblpayment.year',      '=', $year)
        ->where('tblpayment.courtID',  '=', $court)
        ->where('tblpayment.bank',      '=', $bankID )
        ->where('tblpayment.bankGroup', '=',$bankGroup)
        ->orderBy('NetPay','DESC')
        ->orderBy('name','ASC')
        ->paginate(10);
  $data['epayment_total'] = DB::table('tblpayment')
        
        ->where('tblpayment.month',     '=', $month)
        ->where('tblpayment.year',      '=', $year)
        ->where('tblpayment.courtID',  '=', $court)
        ->where('tblpayment.bank',      '=', $bankID )
        ->where('tblpayment.bankGroup', '=',$bankGroup)
        ->orderBy('NetPay','DESC')
        ->orderBy('name','ASC')
        ->get();
  $totalRows= DB::table('tblpayment')
        
        ->where('tblpayment.month',     '=', $month)
        ->where('tblpayment.year',      '=', $year)
        ->where('tblpayment.courtID',  '=', $court)
        ->where('tblpayment.bank',      '=', $bankID )
        ->where('tblpayment.bankGroup', '=',$bankGroup)
        ->count();

      }
      elseif($division != '' &&  $bankGroup != '')
      {

       $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
      $data['epayment_detail'] = DB::table('tblpayment')
       
        ->where('tblpayment.month',     '=', $month)
        ->where('tblpayment.year',      '=', $year)
        ->where('tblpayment.divisionID',  '=', $division)
        ->where('tblpayment.courtID',  '=', $court)
        ->where('tblpayment.bank',      '=', $bankID )
        ->where('tblpayment.bankGroup', '=',$bankGroup)
        ->orderBy('NetPay','DESC')
        ->orderBy('name','ASC')
        ->paginate(10);
  $data['epayment_total'] = DB::table('tblpayment')
        
        ->where('tblpayment.month',     '=', $month)
        ->where('tblpayment.year',      '=', $year)
        ->where('tblpayment.divisionID',  '=', $division)
        ->where('tblpayment.courtID',  '=', $court)
        ->where('tblpayment.bank',      '=', $bankID )
        ->where('tblpayment.bankGroup', '=',$bankGroup)
        ->orderBy('NetPay','DESC')
        ->orderBy('name','ASC')
        ->get();
  $totalRows= DB::table('tblpayment')
        
        ->where('tblpayment.month',     '=', $month)
        ->where('tblpayment.year',      '=', $year)
        ->where('tblpayment.divisionID',  '=', $division)
        ->where('tblpayment.courtID',  '=', $court)
        ->where('tblpayment.bank',      '=', $bankID )
        ->where('tblpayment.bankGroup', '=',$bankGroup)
        ->count();

      }
      if($bankGroup == '')
      {
        if($division == '')
      {
      $data['courtname']  = DB::table('tbl_court')
      ->where('id','=', $court)
      ->first();
      $data['courtDivisions'] = '';
       $data['epayment_detail'] = DB::table('tblpayment') 
        ->where('tblpayment.month',     '=', $month)
        ->where('tblpayment.year',      '=', $year)
        ->where('tblpayment.courtID',  '=', $court)
        ->where('tblpayment.bank',      '=', $bankID )
        //->where('tblpayment.bankGroup', '=',$bankGroup)
        ->orderBy('NetPay','DESC')
        ->orderBy('name','ASC')
        ->paginate(10);
  $data['epayment_total'] = DB::table('tblpayment')
        
        ->where('tblpayment.month',     '=', $month)
        ->where('tblpayment.year',      '=', $year)
        ->where('tblpayment.$courtID',  '=', $court)
        ->where('tblpayment.bank',      '=', $bankID )
        //->where('tblpayment.bankGroup', '=',$bankGroup)
        ->orderBy('NetPay','DESC')
        ->orderBy('name','ASC')
        ->get();
  $totalRows= DB::table('tblpayment')
        
        ->where('tblpayment.month',     '=', $month)
        ->where('tblpayment.year',      '=', $year)
        ->where('tblpayment.courtID',  '=', $courtID)
        ->where('tblpayment.bank',      '=', $bankID )
        //->where('tblpayment.bankGroup', '=',$bankGroup)
        ->count();
      }
      else
      {

       $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
       $data['epayment_detail'] = DB::table('tblpayment')
       
        ->where('tblpayment.month',     '=', $month)
        ->where('tblpayment.year',      '=', $year)
        ->where('tblpayment.divisionID',  '=', $division)
        ->where('tblpayment.courtID',  '=', $court)
        ->where('tblpayment.bank',      '=', $bankID )
        //->where('tblpayment.bankGroup', '=',$bankGroup)
        ->orderBy('NetPay','DESC')
        ->orderBy('name','ASC')
        ->paginate(10);
  $data['epayment_total'] = DB::table('tblpayment')
        
        ->where('tblpayment.month',     '=', $month)
        ->where('tblpayment.year',      '=', $year)
        ->where('tblpayment.divisionID',  '=', $division)
        ->where('tblpayment.courtID',  '=', $court)
        ->where('tblpayment.bank',      '=', $bankID )
        //->where('tblpayment.bankGroup', '=',$bankGroup)
        ->orderBy('NetPay','DESC')
        ->orderBy('name','ASC')
        ->get();
  $totalRows= DB::table('tblpayment')
        
        ->where('tblpayment.month',     '=', $month)
        ->where('tblpayment.year',      '=', $year)
        ->where('tblpayment.divisionID',  '=', $division)
        ->where('tblpayment.courtID',  '=', $court)
        ->where('tblpayment.bank',      '=', $bankID )
        //->where('tblpayment.bankGroup', '=',$bankGroup)
        ->count();
      }
      }


  
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
  Session::put('bank', $bankName ." ".$bankGroup);

  $data['getSignatory'] = DB::table('tblsignatory')
                      ->get();

$check =  $data['sign1']  = DB::table('tblassign_signatory')
       ->where('year', '=', $year)
       ->where('month', '=', $month)
       ->count();
    if($check === 0)
    {
      $data['name1'] = '';
      $data['name2'] = '';
      $data['name3'] = '';
      $data['phone1'] = '';
      $data['phone2'] = '';
      $data['phone3'] = '';
    }
    else
    {
  $sign1  = DB::table('tblassign_signatory')
       ->where('year', '=', $year)
       ->where('month', '=', $month)
       ->where('sequence', '=',1)
       ->first();
  $sign2  = DB::table('tblassign_signatory')
       ->where('year', '=', $year)
       ->where('month', '=', $month)
       ->where('sequence', '=',2)
       ->first();
   $sign3  = DB::table('tblassign_signatory')
       ->where('year', '=', $year)
       ->where('month', '=', $month)
       ->where('sequence', '=',3)
       ->first(); 
//dd($sign1);
      $data['name1'] = $sign1->name;
      $data['name2'] = $sign2->name;
      $data['name3'] = $sign3->name;
      $data['phone1'] = $sign1->phone;
      $data['phone2'] = $sign2->phone;
      $data['phone3'] = $sign3->phone;
     }   

  return view('epayment.summary', $data);
}

public function Retrieveget(Request $request)
{
  $division = $this->division;
  $serialNo = "";
  $pageNO   = "";
  $pageNO   = $request->get('page');
  if(is_null($pageNO))
  {
    $serialNo=1;
  }
  elseif( $pageNO==1) 
  {
    $serialNo=1;      
  }
  else 
  {
    $serialNo=(($pageNO-1)*10)+1;          }
    Session::put('serialNo', $serialNo);
    $month     = session('month');  
    $year      = session('year');  
    $bankID    = session('bankID');
    $bankGroup = session('bankGroup'); 
    $getBank   = DB::table('tblbanklist')
               ->where('bankID',$bankID)
               ->first();
    $bankName  = $getBank->bank;
    $data['epayment_detail'] = DB::table('tblpayment')
              ->where('tblpayment.division',  '=',$division)
              ->where('tblpayment.month',     '=', $month)
              ->where('tblpayment.year',      '=', $year)
              ->where('tblpayment.division',  '=', $division)
              ->where('tblpayment.bank',      '=', $bankName )
              ->where('tblpayment.bankGroup', '=',$bankGroup)
              ->orderBy('totalEmolu','DESC')
              ->orderBy('name','ASC')
              ->paginate(10);
    $data['epayment_total'] = DB::table('tblpayment')
              ->where('tblpayment.division',  '=',$division)
              ->where('tblpayment.month',     '=', $month)
              ->where('tblpayment.year',      '=', $year)
              ->where('tblpayment.division',  '=', $division)
              ->where('tblpayment.bank',      '=', $bankName )
              ->where('tblpayment.bankGroup', '=',$bankGroup)
              ->orderBy('totalEmolu','DESC')
              ->orderBy('name','ASC')
              ->get();
    $totalRows = DB::table('tblpayment')
              ->where('tblpayment.division',  '=',$division)
              ->where('tblpayment.month',     '=', $month)
              ->where('tblpayment.year',      '=', $year)
              ->where('tblpayment.division',  '=', $division)
              ->where('tblpayment.bank',      '=', $bankName )
              ->where('tblpayment.bankGroup', '=',$bankGroup)
              ->count();
    $max_row    = 10;
    $totalPages = ceil($totalRows/$max_row);
    if($pageNO  == $totalPages)
    {
      Session::put('showTotal', "yes");
    }
    else
    {
      Session::put('showTotal', "");
    }

    $data['getSignatory'] = DB::connection('mysql2')->table('tblsignatory')
                      ->get();

    $check =  $data['sign1']  = DB::table('signatory')
       ->where('year', '=', $year)
       ->where('month', '=', $month)
       ->count();
    if($check === 0)
    {
      $data['name1'] = '';
      $data['name2'] = '';
      $data['name3'] = '';
      $data['phone1'] = '';
      $data['phone2'] = '';
      $data['phone3'] = '';
    }
    else
    {
  $sign1  = DB::table('signatory')
       ->where('year', '=', $year)
       ->where('month', '=', $month)
       ->where('sequence', '=',1)
       ->first();
  $sign2  = DB::table('signatory')
       ->where('year', '=', $year)
       ->where('month', '=', $month)
       ->where('sequence', '=',2)
       ->first();
   $sign3  = DB::table('signatory')
       ->where('year', '=', $year)
       ->where('month', '=', $month)
       ->where('sequence', '=',3)
       ->first(); 

      $data['name1'] = $sign1->name;
      $data['name2'] = $sign2->name;
      $data['name3'] = $sign3->name;
      $data['phone1'] = $sign1->phone;
      $data['phone2'] = $sign2->phone;
      $data['phone3'] = $sign3->phone;
     }

    return view('epayment.summary', $data);
  }

  public function getPhone(Request $request)
  {
     $id = $request['signid'];
    $val = DB::connection('mysql2')->table('tblsignatory')
    ->where('signatoryID','=', $id)
    ->first(); 
    return response()->json($val);
  }
   public function tests(Request $request)
  {
    
 
    return view('epayment.test');
  }

  public function test(Request $request)
  {
    $id = $request['signid'];
    $val = DB::connection('mysql2')->table('tblsignatory')
    ->where('signatoryID','=', $id)
    ->first(); 
    return response()->json($val);
  }


}