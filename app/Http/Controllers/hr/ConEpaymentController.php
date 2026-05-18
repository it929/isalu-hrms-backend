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

class ConEpaymentController extends ParentController
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

         /* $data['allbanklist']  = DB::table('tblbanklist')
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();*/
    $data['CourtInfo']=$this->CourtInfo();
    if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
    if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
    if(count($data['CourtInfo']) > 0)
      {
        
$data['allbanklist']  = DB::table('tblbanklist')
        
         
         ->get();
      }
    return view('con_epayment.index',$data);
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
    //'bankName'  => 'required|integer', 
    //'bankGroup' => 'required|integer'   
  ]);
  
  $data['bat'] = DB::table('tblbat')
           ->where('year', $year)
           ->where('month', $month)
           ->first();
   $data['lock'] = DB::table('tblpayment_consolidated')->where('salary_lock','=',1)->where('month','=',$month)->where('year','=',$year)->count();


 $data['month'] = $month;
  Session::put('serialNo', 1);
  Session::put('bankID', $bankID);
  Session::put('bankGroup', $bankGroup);
  $getBank = DB::table('tblbanklist')
           ->where('bankID', $bankID)
           ->first();
           
  //$bankName = $getBank->bank;
  $bankCode = DB::table('tblbank')
            ->where('bankID',$bankID)
            ->first();
 
       $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
      if($bankID == '')
      {
 
      $data['epayment_detail'] = DB::table('tblpayment_consolidated')
       ->leftJoin('tblbanklist','tblbanklist.bankID','=','tblpayment_consolidated.bank')
       ->leftJoin('tblbacklog','tblbacklog.staffid','=','tblpayment_consolidated.staffid','tblbacklog.month','=',$month,'tblbacklog.year','=',$year)
        ->where('tblpayment_consolidated.month',     '=', $month)
        ->where('tblpayment_consolidated.year',      '=', $year)
        //->where('tblpayment_consolidated.divisionID',  '=', $division)
        ->where('tblpayment_consolidated.courtID',  '=', $court)
        //->where('tblpayment_consolidated.bank',      '=', $bankID )
        //->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
        //->where('tblbank.courtID', '=',$court)
        //->orderBy('tblpayment_consolidated.grade','DESC')
        //->orderBy('tblpayment_consolidated.step','DESC')
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

        }
        else
        {
        $data['epayment_detail'] = DB::table('tblpayment_consolidated')
       ->leftJoin('tblbanklist','tblbanklist.bankID','=','tblpayment_consolidated.bank')
       ->leftJoin('tblbacklog','tblbacklog.staffid','=','tblpayment_consolidated.staffid','tblbacklog.month','=',$month,'tblbacklog.year','=',$year)
        ->where('tblpayment_consolidated.month',     '=', $month)
        ->where('tblpayment_consolidated.year',      '=', $year)
        //->where('tblpayment_consolidated.divisionID',  '=', $division)
        ->where('tblpayment_consolidated.courtID',  '=', $court)
        ->where('tblpayment_consolidated.bank', '=', $bankID )
        //->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
        //->where('tblbank.courtID', '=',$court)
        //->orderBy('tblpayment_consolidated.grade','DESC')
        //->orderBy('tblpayment_consolidated.step','DESC')
        ->where('tblpayment_consolidated.rank','!=',2)
        ->orderBy('tblpayment_consolidated.bank','DESC')
        ->orderBy('tblpayment_consolidated.rank','DESC')
        ->orderBy('tblpayment_consolidated.name','ASC')
        
        ->get();
          $data['coopSumSaving'] = DB::table('tblotherEarningDeduction')->where('CVID','=',15)->where('year','=',$year)->where('month','=',$month)->sum('amount');
        $data['coopSumLoan'] = DB::table('tblotherEarningDeduction')->where('CVID','=',16)->where('year','=',$year)->where('month','=',$month)->sum('amount');
        $data['refundSum'] = DB::table('tblotherEarningDeduction')->where('CVID','=',2)->where('year','=',$year)->where('month','=',$month)->sum('amount');
        $data['unionSum'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('UD');
        $data['taxSum'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('TAX');
        $gross = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('TEarn');
       $data['nhis'] = (5/100) * $gross;
       $data['totalPaySum'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('NetPay');

        }
        
        
        
        
       
  $data['epayment_total'] = DB::table('tblpayment_consolidated')
        
        ->where('tblpayment_consolidated.month',     '=', $month)
        ->where('tblpayment_consolidated.year',      '=', $year)
        //->where('tblpayment_consolidated.divisionID',  '=', $division)
        ->where('tblpayment_consolidated.courtID',  '=', $court)
        //->where('tblpayment_consolidated.bank',      '=', $bankID )
       // ->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
       ->orderBy('tblpayment_consolidated.grade','DESC')
        //->orderBy('tblpayment_consolidated.step','DESC')
        ->orderBy('tblpayment_consolidated.bank','DESC')
        ->orderBy('tblpayment_consolidated.rank','DESC')
        ->orderBy('tblpayment_consolidated.name','ASC')
        
        ->get();
         $data['coopSumSaving'] = DB::table('tblotherEarningDeduction')->where('CVID','=',15)->where('year','=',$year)->where('month','=',$month)->sum('amount');
        $data['coopSumLoan'] = DB::table('tblotherEarningDeduction')->where('CVID','=',16)->where('year','=',$year)->where('month','=',$month)->sum('amount');
        $data['refundSum'] = DB::table('tblotherEarningDeduction')->where('CVID','=',2)->where('year','=',$year)->where('month','=',$month)->sum('amount');
        $data['unionSum'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('UD');
        $data['taxSum'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('TAX');
        $gross = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('TEarn');
       $data['nhis'] = (5/100) * $gross;
       $data['totalPaySum'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('NetPay');

  $totalRows= DB::table('tblpayment_consolidated')
        
        ->where('tblpayment_consolidated.month',     '=', $month)
        ->where('tblpayment_consolidated.year',      '=', $year)
        //->where('tblpayment_consolidated.divisionID',  '=', $division)
        ->where('tblpayment_consolidated.courtID',  '=', $court)
        //->where('tblpayment_consolidated.bank',      '=', $bankID )
        //->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
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
  return view('con_epayment.summary33', $data);
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
    //$bankName  = $getBank->bank;
    /*$data['epayment_detail'] = DB::table('tblpayment_consolidated')
              ->where('tblpayment_consolidated.divisionID',  '=',$division)
              ->where('tblpayment_consolidated.month',     '=', $month)
              ->where('tblpayment_consolidated.year',      '=', $year)
              //->where('tblpayment_consolidated.division',  '=', $division)
              ->where('tblpayment_consolidated.bank',      '=', $bankName )
              ->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
              //->orderBy('totalEmolu','DESC')
              //->orderBy('name','ASC')
              ->paginate(10);
    $data['epayment_total'] = DB::table('tblpayment_consolidated')
              ->where('tblpayment_consolidated.divisionID',  '=',$division)
              ->where('tblpayment_consolidated.month',     '=', $month)
              ->where('tblpayment_consolidated.year',      '=', $year)
              //->where('tblpayment_consolidated.division',  '=', $division)
              ->where('tblpayment_consolidated.bank',      '=', $bankName )
              ->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
              //->orderBy('totalEmolu','DESC')
              //->orderBy('name','ASC')
              ->get();
    $totalRows = DB::table('tblpayment_consolidated')
              ->where('tblpayment_consolidated.divisionID',  '=',$division)
              ->where('tblpayment_consolidated.month',     '=', $month)
              ->where('tblpayment_consolidated.year',      '=', $year)
              //->where('tblpayment_consolidated.division',  '=', $division)
              ->where('tblpayment_consolidated.bank',      '=', $bankName )
              ->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
              ->count();*/
              
       $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
      $data['epayment_detail'] = DB::table('tblpayment_consolidated')
       ->join('tblbank','tblbank.bankID','=','tblpayment_consolidated.bank')
        ->where('tblpayment_consolidated.month',     '=', $month)
        ->where('tblpayment_consolidated.year',      '=', $year)
        //->where('tblpayment_consolidated.divisionID',  '=', $division)
        ->where('tblpayment_consolidated.courtID',  '=', $court)
        //->where('tblpayment_consolidated.bank',      '=', $bankID )
        //->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
        ->where('tblbank.courtID', '=',$court)
        ->orderBy('NetPay','DESC')
        ->orderBy('name','ASC')
        ->paginate(10);
        dd($data['epayment_detail']);
  $data['epayment_total'] = DB::table('tblpayment_consolidated')
        
        ->where('tblpayment_consolidated.month',     '=', $month)
        ->where('tblpayment_consolidated.year',      '=', $year)
        //->where('tblpayment_consolidated.divisionID',  '=', $division)
        ->where('tblpayment_consolidated.courtID',  '=', $court)
        //->where('tblpayment_consolidated.bank',      '=', $bankID )
       // ->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
        ->orderBy('NetPay','DESC')
        ->orderBy('name','ASC')
        ->get();
  $totalRows= DB::table('tblpayment_consolidated')
        
        ->where('tblpayment_consolidated.month',     '=', $month)
        ->where('tblpayment_consolidated.year',      '=', $year)
        //->where('tblpayment_consolidated.divisionID',  '=', $division)
        ->where('tblpayment_consolidated.courtID',  '=', $court)
        //->where('tblpayment_consolidated.bank',      '=', $bankID )
        //->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
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

    
    return view('con_epayment.summary', $data);
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
  
  
  
  
  
  public function indexNew()
{
 $data['courts'] =  DB::table('tbl_court')->get();
      $courtSessionId = session('anycourt');
         $data['courtDivisions']  = DB::table('tbldivision')
         ->where('courtID', '=', $courtSessionId)
         ->get();

         /* $data['allbanklist']  = DB::table('tblbanklist')
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();*/
    $data['CourtInfo']=$this->CourtInfo();
    if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
    if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
    if(count($data['CourtInfo']) > 0)
      {
        
$data['allbanklist']  = DB::table('tblbanklist')
        
         
         ->get();
      }
    return view('con_epayment.indexMandate',$data);
}
public function RetrieveNew(Request $request)
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
    //'bankName'  => 'required|integer', 
    //'bankGroup' => 'required|integer'   
  ]);
  $data['year']  = $year;
  $data['month'] = $month;
  $data['bat'] = DB::table('tblbat')
           ->where('year', $year)
           ->where('month', $month)
           ->first();
   $data['lock'] = DB::table('tblpayment_consolidated')->where('salary_lock','=',1)->where('month','=',$month)->where('year','=',$year)->count();

$data['xyz1']=DB::table('tblotherEarningDeduction')->where('CVID','=',3)->where('year','=',$year)->where('month','=',$month)->sum('amount');
$data['xyz2']=DB::table('tblotherEarningDeduction')->where('CVID','=',13)->where('year','=',$year)->where('month','=',$month)->sum('amount');
$data['xyz3']=DB::table('tblotherEarningDeduction')->where('CVID','=',17)->where('year','=',$year)->where('month','=',$month)->sum('amount');
$data['xyz4']=($data['xyz1']+$data['xyz2']+$data['xyz3'])*0.05;
//$data['nhisNew'] = round(DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('TEarn')*0.05-$data['xyz4'],2);
 $percentages= DB::SELECT ("SELECT * FROM `tbldeduction_percentage`")[0];
$data['nhisNew'] = round((DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('TEarn') -
DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('PEC')-
DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('SOT'))

*$percentages->nhis*0.01,2);
//dd($data['nhisNew']);
//dd($data['xyz4']);
 $data['month'] = $month;
  Session::put('serialNo', 1);
  Session::put('bankID', $bankID);
  Session::put('bankGroup', $bankGroup);
  $getBank = DB::table('tblbanklist')
           ->where('bankID', $bankID)
           ->first();
           
  //$bankName = $getBank->bank;
  $bankCode = DB::table('tblbank')
            ->where('bankID',$bankID)
            ->first();
 
       $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
      if($bankID == '')
      {
 
      $data['epayment_detail'] = DB::table('tblpayment_consolidated')
       ->leftJoin('tblbanklist','tblbanklist.bankID','=','tblpayment_consolidated.bank')
       ->leftJoin('tblbacklog','tblbacklog.staffid','=','tblpayment_consolidated.staffid','tblbacklog.month','=',$month,'tblbacklog.year','=',$year)
        ->where('tblpayment_consolidated.month',     '=', $month)
        ->where('tblpayment_consolidated.year',      '=', $year)
        //->where('tblpayment_consolidated.divisionID',  '=', $division)
        ->where('tblpayment_consolidated.courtID',  '=', $court)
        //->where('tblpayment_consolidated.bank',      '=', $bankID )
        //->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
        //->where('tblbank.courtID', '=',$court)
        //->orderBy('tblpayment_consolidated.grade','DESC')
        //->orderBy('tblpayment_consolidated.step','DESC')
        ->where('tblpayment_consolidated.rank','!=',2)
        ->orderBy('tblpayment_consolidated.bank','DESC')
        ->orderBy('tblpayment_consolidated.rank','DESC')
        ->orderBy('tblpayment_consolidated.name','ASC')
         ->get();
         
         $data['taxPayee'] = DB::table('tblcurrent_state')
         ->join('tblbanklist','tblbanklist.bankID','=','tblcurrent_state.bank')
         ->select('*','tblbanklist.bank as bankname')
         ->where('tblcurrent_state.status','=',1)->get();
         
         //dd($data['epayment_detail']); 
 $data['coopSumSaving'] = DB::table('tblotherEarningDeduction')->where('CVID','=',15)->where('year','=',$year)->where('month','=',$month)->sum('amount');
        $data['coopSumLoan'] = DB::table('tblotherEarningDeduction')->where('CVID','=',16)->where('year','=',$year)->where('month','=',$month)->sum('amount');
        $data['refundSum'] = DB::table('tblotherEarningDeduction')->where('CVID','=',2)->where('year','=',$year)->where('month','=',$month)->sum('amount');
        $data['unionSum'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('UD');
        $data['taxSum'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('TAX');
        $gross = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('TEarn');
       //$data['nhis'] = (5/100) * $gross;
       $basic = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('Bs');
       
       
         //$data['nhisNew'] = (5.25/100) * $basic;
         //dd($data['nhis']);
        if($year > 2019)
        {
            //$data['nhisNew'] = (5/100) * $basic;
            //$data['nhisNew'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('NHIS') ;//- $data['xyz4'];
        //dd($data['nhisNew']);
            
        }
        else
        {
          $data['nhisNew'] = (5/100) * $gross;
        }
       $data['totalPaySum'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('NetPay');
       

        }
        else
        {
            $data['taxPayee'] = DB::table('tblcurrent_state')
         ->join('tblbanklist','tblbanklist.bankID','=','tblcurrent_state.bank')
         ->select('*','tblbanklist.bank as bankname')
         ->where('tblcurrent_state.status','=',1)->get();
         
        $data['epayment_detail'] = DB::table('tblpayment_consolidated')
       ->leftJoin('tblbanklist','tblbanklist.bankID','=','tblpayment_consolidated.bank')
       ->leftJoin('tblbacklog','tblbacklog.staffid','=','tblpayment_consolidated.staffid','tblbacklog.month','=',$month,'tblbacklog.year','=',$year)
        ->where('tblpayment_consolidated.month',     '=', $month)
        ->where('tblpayment_consolidated.year',      '=', $year)
        //->where('tblpayment_consolidated.divisionID',  '=', $division)
        ->where('tblpayment_consolidated.courtID',  '=', $court)
        ->where('tblpayment_consolidated.bank', '=', $bankID )
        //->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
        //->where('tblbank.courtID', '=',$court)
        //->orderBy('tblpayment_consolidated.grade','DESC')
        //->orderBy('tblpayment_consolidated.step','DESC')
        ->where('tblpayment_consolidated.rank','!=',2)
        ->orderBy('tblpayment_consolidated.bank','DESC')
        ->orderBy('tblpayment_consolidated.rank','DESC')
        ->orderBy('tblpayment_consolidated.name','ASC')
        ->get();
        
          $data['coopSumSaving'] = DB::table('tblotherEarningDeduction')->where('CVID','=',15)->where('year','=',$year)->where('month','=',$month)->sum('amount');
        $data['coopSumLoan'] = DB::table('tblotherEarningDeduction')->where('CVID','=',16)->where('year','=',$year)->where('month','=',$month)->sum('amount');
        $data['refundSum'] = DB::table('tblotherEarningDeduction')->where('CVID','=',2)->where('year','=',$year)->where('month','=',$month)->sum('amount');
        $data['unionSum'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('UD');
        $data['taxSum'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('TAX');
        $gross = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('TEarn');
        $basic = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('Bs');
         //$data['nhis'] = (5.25/100) * $basic;
        if($year > 2019)
        {
            $data['nhisNew'] = round((DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('TEarn') -
DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('PEC')-
DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('SOT'))

*$percentages->nhis*0.01,2);
            //$data['nhisNew'] = (5.25/100) * $basic;
          // $data['nhisNew'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('NHIS') - $data['xyz4'];
        }
        else
        {
          //$data['nhisNew'] = (5/100) * $gross;
        }
       $data['totalPaySum'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('NetPay');

        }
        
        
        
        
       
  $data['epayment_total'] = DB::table('tblpayment_consolidated')
        
        ->where('tblpayment_consolidated.month',     '=', $month)
        ->where('tblpayment_consolidated.year',      '=', $year)
        //->where('tblpayment_consolidated.divisionID',  '=', $division)
        ->where('tblpayment_consolidated.courtID',  '=', $court)
        //->where('tblpayment_consolidated.bank',      '=', $bankID )
       // ->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
       ->orderBy('tblpayment_consolidated.grade','DESC')
        //->orderBy('tblpayment_consolidated.step','DESC')
        ->orderBy('tblpayment_consolidated.bank','DESC')
        ->orderBy('tblpayment_consolidated.rank','DESC')
        ->orderBy('tblpayment_consolidated.name','ASC')
        
        ->get();
         $data['coopSumSaving'] = DB::table('tblotherEarningDeduction')->where('CVID','=',15)->where('year','=',$year)->where('month','=',$month)->sum('amount');
        $data['coopSumLoan'] = DB::table('tblotherEarningDeduction')->where('CVID','=',16)->where('year','=',$year)->where('month','=',$month)->sum('amount');
        $data['refundSum'] = DB::table('tblotherEarningDeduction')->where('CVID','=',2)->where('year','=',$year)->where('month','=',$month)->sum('amount');
        $data['unionSum'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('UD');
        $data['taxSum'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('TAX');
        $gross = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('Bs');
       $data['nhis'] = (5/100) * $gross;
       $data['totalPaySum'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('NetPay');

  $totalRows= DB::table('tblpayment_consolidated')
        
        ->where('tblpayment_consolidated.month',     '=', $month)
        ->where('tblpayment_consolidated.year',      '=', $year)
        //->where('tblpayment_consolidated.divisionID',  '=', $division)
        ->where('tblpayment_consolidated.courtID',  '=', $court)
        //->where('tblpayment_consolidated.bank',      '=', $bankID )
        //->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
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
   //$d = ' + '.$data['nhisbal']->amount;
   //DD($d);
   //$data['nhisNew'] = round(DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('TEarn')*0.05-$data['xyx4'],2);
  return view('con_epayment.mandate', $data);
}


}