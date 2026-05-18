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

class AnalysisController extends ParentController
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
$data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
  $data['courts'] =  DB::table('tbl_court')->get();
      $courtSessionId = session('anycourt');
         $data['courtDivisions']  = DB::table('tbldivision')
         ->where('courtID', '=', $courtSessionId)
         ->get();

          $data['allbanklist']  = DB::table('tblbanklist')
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
    //$warrant   = trim($request->input('warrant'));
    $division  = trim($request->input('division'));
     $court    = trim($request->input('court'));
  
    $this->validate($request,[       
          'month'     => 'required|string', 
          'year'      => 'required|integer', 
          //'bankName'  => 'required|integer', 
          //'bankGroup' => 'required|integer',
          //'warrant'   => 'required|regex:/[a-zA-Z.]/'   
    ]);
    $getBank  = DB::table('tblbanklist')
              ->where('bankID', $bankID)
              ->first();
              if($bankID !='')
              {
    $bankName = $getBank -> bank;
    }
    /*$data['summary_detail'] = DB::table('tblpayment')
            ->where('tblpayment.month',     '=', $month)
            ->where('tblpayment.year',      '=', $year)
            ->where('tblpayment.division',  '=', $division)
            ->where('tblpayment.bank',      '=', $bankName )
            ->where('tblpayment.bankGroup', '=', $bankGroup)
            ->orderBy('basic_salary','DESC')
            ->get();*/



       if($bankID != '')
      {
      $data['courtname']  = DB::table('tbl_court')
      ->where('id','=', $court)
      ->first();
      $data['courtDivisions'] = '';
       $data['summary_detail'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.bank',      '=',$bankID )
          //->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
          ->where('tblpayment_consolidated.rank','!=',2)
          //->orderBy('basic_salary','DESC')
          ->get();
          return view('summary.summary', $data);
      }
      elseif($division != '' &&  $bankGroup != '')
      {

       $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
       $data['summary_detail'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.divisionID',  '=', $division)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.bank',      '=',$bankID )
          ->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
          ->where('tblpayment_consolidated.rank','!=',2)
          //->orderBy('basic_salary','DESC')
          ->get();
      }
      if($bankGroup == '' && $bankID=='')
      {
        if($division == '')
      {
      $data['courtname']  = DB::table('tbl_court')
      ->where('id','=', $court)
      ->first();
      $data['courtDivisions'] = '';
       $data['summary_detail'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('ttblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.rank','!=',2)
          //->where('tblpayment_consolidated.bank',      '=',$bankID )
          //->where('tblpayment.bankGroup', '=', $bankGroup)
          //->orderBy('basic_salary','DESC')
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
       $data['summary_detail'] = DB::table('tblpayment_consolidated')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.divisionID',  '=', $division)
          ->where('tblpayment_consolidated.courtID',  '=', $court)
          ->where('tblpayment_consolidated.rank','!=',2)
          //->where('tblpayment_consolidated.bank',      '=',$bankID )
          //->where('tblpayment.bankGroup', '=', $bankGroup)
          //->orderBy('basic_salary','DESC')
          ->get();
      }
      }

//dd($data['summary_detail']);

    Session::put('schmonth', $month." ".$year); 
    if($bankID != '')
    {
    Session::put('bank', $bankName ." ".$bankGroup);
    }
    //Session::put('warrant', $warrant);
    return view('summary.summary', $data);
  }
  
  public function calculateSum($month,$year,$field)
  {
       $data['basic'] = DB::table('tblpayment_consolidated')
    ->join('tblbanklist','tblbanklist.bankID','=','tblpayment_consolidated.bank')
    ->where('tblpayment_consolidated.month',     '=', $month)
    ->where('tblpayment_consolidated.year',      '=', $year)
    ->where('tblpayment_consolidated.rank','!=',2)
    ->groupBy('tblpayment_consolidated.bank')
    ->sum($field);
  }
   public function countStaff($month,$year)
  {
       $data['basic'] = DB::table('tblpayment_consolidated')
    ->join('tblbanklist','tblbanklist.bankID','=','tblpayment_consolidated.bank')
    ->where('tblpayment_consolidated.month','=', $month)
    ->where('tblpayment_consolidated.year', '=', $year)
    ->where('tblpayment_consolidated.rank','!=',2)
    ->groupBy('tblpayment_consolidated.bank')
    ->count();
  }
  
  public function analysis()
  {
  $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
  $data['courts'] =  DB::table('tbl_court')->get();
      $courtSessionId = session('anycourt');
         $data['courtDivisions']  = DB::table('tbldivision')
         ->where('courtID', '=', $courtSessionId)
         ->get();

          $data['allbanklist']  = DB::table('tblbanklist')
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();

  return view('summary/analysisParam',$data);
  }
  
  public function analysisDisplay(Request $request)
  {
    $month     = trim($request->input('month'));
    $year      = trim($request->input('year'));
  $data['group'] = DB::table('tblpayment_consolidated')
    ->join('tblbanklist','tblbanklist.bankID','=','tblpayment_consolidated.bank')
    ->where('tblpayment_consolidated.month',     '=', $month)
    ->where('tblpayment_consolidated.year',      '=', $year)
    ->where('tblpayment_consolidated.rank','!=',2)
    ->groupBy('tblpayment_consolidated.bank')
    ->select('*','tblbanklist.bank','tblpayment_consolidated.bank as bankid')
    ->get();
    
     /*$data['basic'] = $this->calculateSum($month,$year,'Bs');
     $data['netpay'] = $this->calculateSum($month,$year,'NetPay');
     $data['totdeduct'] = $this->calculateSum($month,$year,'TD');
     $data['jusu'] = $this->calculateSum($month,$year,'PEC');
     $data['pension'] = $this->calculateSum($month,$year,'PEN');
     $data['dues'] = $this->calculateSum($month,$year,'UD');
     $data['tax'] = $this->calculateSum($month,$year,'TAX');
     $data['nhf'] = $this->calculateSum($month,$year,'NHF');
     $data['totalAllowance'] = $this->calculateSum($month,$year,'OEarn');
     $data['totArr'] = $this->calculateSum($month,$year,'OEarn');
     $data['totalEarn'] = $this->calculateSum($month,$year,'TEarn');
     $data['coop'] = $this->calculateSum($month,$year,'OD');
     $data['totalStaff'] = $this->countStaff($month,$year);
     */
    
    /*foreach ($data['group'] as $key => $value) {
    	$lis = (array) $value;
    	$lis['coop2'] = $this->ContravariableSum($request->input('year'),$request->input('month'),'15',$value->bankid)+$this->ContravariableSum($request->input('year'),$request->input('month'),'16',$value->bankid);
    	$lis['saladv'] = $this->ContravariableSum($request->input('year'),$request->input('month'),'18',$value->bankid);
    	$lis['hloan'] = $this->ContravariableSum($request->input('year'),$request->input('month'),'2',$value->bankid);
	$value = (object) $lis;
    	$data['group'][$key]  = $value;	
	}*/
	//dd($data['group']);
	
//	dd($data['group']);
	$data['month'] = trim($request->input('month'));
	$data['year'] = trim($request->input('year'));
    return view('summary/analysis',$data);
  }
  
  
   public function summaryByBank()
  {
  $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
  $data['courts'] =  DB::table('tbl_court')->get();
      $courtSessionId = session('anycourt');
         $data['courtDivisions']  = DB::table('tbldivision')
         ->where('courtID', '=', $courtSessionId)
         ->get();

          $data['allbanklist']  = DB::table('tblbanklist')
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();

  return view('summary/bybanks',$data);
  }
  
  public function summaryPostBank(Request $request)
  {
    $month     = trim($request->input('month'));
    $year      = trim($request->input('year'));
    
    $data['month'] = $month;
    $data['year'] = $year;
    
  /*$data['group'] = DB::table('tblpayment_consolidated')
    ->join('tblbanklist','tblbanklist.bankID','=','tblpayment_consolidated.bank')
    ->where('tblpayment_consolidated.month',     '=', $month)
    ->where('tblpayment_consolidated.year',      '=', $year)
    ->orderBy('tblpayment_consolidated.bank', 'Asc')
    ->select('*','tblbanklist.bank as staffbank','tblpayment_consolidated.bank as bk')
    
    ->get();*/
    $data['epayment_detail'] = DB::table('tblpayment_consolidated')
       ->join('tblbanklist','tblbanklist.bankID','=','tblpayment_consolidated.bank')
        ->leftJoin('tblbacklog','tblbacklog.staffid','=','tblpayment_consolidated.staffid','tblbacklog.month','=','tblpayment_consolidated.month','tblbacklog.year','=','tblpayment_consolidated.year')
        //->where('tblbacklog.month',     '=', $month)
        //->where('tblbacklog.year',      '=', $year)
        ->where('tblpayment_consolidated.month',     '=', $month)
        ->where('tblpayment_consolidated.year',      '=', $year)
         ->where('tblpayment_consolidated.rank','!=',2)
        //->where('tblpayment_consolidated.divisionID',  '=', $division)
        //->where('tblpayment_consolidated.courtID',  '=', $court)
        ->orderBy('tblpayment_consolidated.bank','DESC')
        ->orderBy('tblpayment_consolidated.rank','DESC')
        ->orderBy('tblpayment_consolidated.name','ASC')
        ->get();
        //dd($data['epayment_detail']);
        $data['epayment_total'] = DB::table('tblpayment_consolidated')
        
        ->where('tblpayment_consolidated.month',     '=', $month)
        ->where('tblpayment_consolidated.year',      '=', $year)
        //->where('tblpayment_consolidated.divisionID',  '=', $division)
        //->where('tblpayment_consolidated.courtID',  '=', $court)
        //->where('tblpayment_consolidated.bank',      '=', $bankID )
       // ->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
       ->where('tblpayment_consolidated.rank','!=',2)
       ->orderBy('tblpayment_consolidated.grade','DESC')
        //->orderBy('tblpayment_consolidated.step','DESC')
        ->orderBy('tblpayment_consolidated.bank','DESC')
        ->orderBy('tblpayment_consolidated.rank','DESC')
        ->orderBy('tblpayment_consolidated.name','ASC')
        
        ->get();
        
    return view('summary/summaryByBanks2',$data);
  }
  Public function ContravariableSum($year,$month,$cvid,$bank){
	$List= DB::Select("SELECT sum(`amount`) as sumtotal FROM `tblotherEarningDeduction` WHERE `CVID`='$cvid' and `tblotherEarningDeduction`.`month`='$month' and `tblotherEarningDeduction`.`year`='$year' and 
	exists( SELECT * FROM `tblpayment_consolidated` WHERE `tblpayment_consolidated`.`staffid`=`tblotherEarningDeduction`.`staffid` and `tblpayment_consolidated`.`year`='$year' 
	and `tblpayment_consolidated`.`month`='$month' and `tblpayment_consolidated`.`bank`='$bank' and tblpayment_consolidated.rank !=2)");
	if ($List){return $List[0]->sumtotal;} else {return 0;}
	}
  
}