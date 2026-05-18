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

class CouncilMembersController extends ParentController
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
    return view('councilMembers.mandateForm',$data);
}

public function Retrieve(Request $request)
{
  $month     = trim($request->input('month'));
  $year      = trim($request->input('year'));
 
  $division  = trim($request->input('division'));
  $court     = trim($request->input('court'));
  $this->validate($request,[       
    'month'     => 'required|regex:/^[\pL\s\-]+$/u', 
    'year'      => 'required|integer', 
    
  ]);
  
  $data['bat'] = DB::table('tblbat')
           ->where('year', $year)
           ->where('month', $month)
           ->first();
   $data['lock'] = DB::table('tblpayment_consolidated')->where('salary_lock','=',1)->where('month','=',$month)->where('year','=',$year)->count();


 $data['month'] = $month;
  Session::put('serialNo', 1);
 
  $data['bat'] = DB::table('tblcouncil_bat')
           ->where('year', $year)
           ->where('month', $month)
           ->first();
           
  
 
       $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
     
      $data['epayment_detail'] = DB::table('tblpayment_consolidated')
       ->leftJoin('tblbanklist','tblbanklist.bankID','=','tblpayment_consolidated.bank')
       ->join('tblper','tblper.ID','=','tblpayment_consolidated.staffid')
       //->leftJoin('tblbacklog','tblbacklog.staffid','=','tblpayment_consolidated.staffid','tblbacklog.month','=',$month,'tblbacklog.year','=',$year)
        ->where('tblpayment_consolidated.month',     '=', $month)
        ->where('tblpayment_consolidated.year',      '=', $year)
        //->where('tblpayment_consolidated.courtID',  '=', $court)
        ->where('tblpayment_consolidated.rank','=',2)
        ->orderBy('tblper.ordering','ASC')
        ->select('*','tblpayment_consolidated.AccNo as accountNo')
        //->orderBy('tblpayment_consolidated.rank','DESC')
        //->orderBy('tblpayment_consolidated.name','ASC')
        ->get();
       // dd($data['epayment_detail']);
        
        $data['epayment_total'] = DB::table('tblpayment_consolidated')
        ->where('tblpayment_consolidated.month',     '=', $month)
        ->where('tblpayment_consolidated.year',      '=', $year)
        ->where('tblpayment_consolidated.courtID',  '=', $court)
        ->where('tblpayment_consolidated.rank','=',2)
        ->orderBy('tblpayment_consolidated.grade','DESC')
        ->orderBy('tblpayment_consolidated.bank','DESC')
        ->orderBy('tblpayment_consolidated.rank','DESC')
        ->orderBy('tblpayment_consolidated.name','ASC')
        //->orderBy('tblper.ordering','ASC')
        
        ->get();
        
         $data['firs'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','=',2)->sum('TAX');
          
 $data['coopSumSaving'] = DB::table('tblotherEarningDeduction')->where('CVID','=',15)->where('year','=',$year)->where('month','=',$month)->sum('amount');
        $data['coopSumLoan'] = DB::table('tblotherEarningDeduction')->where('CVID','=',16)->where('year','=',$year)->where('month','=',$month)->sum('amount');
        $data['refundSum'] = DB::table('tblotherEarningDeduction')->where('CVID','=',2)->where('year','=',$year)->where('month','=',$month)->sum('amount');
        $data['unionSum'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('UD');
        $data['taxSum'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('TAX');
        $gross = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('TEarn');
        
        
       $data['nhis'] = (5/100) * $gross;
       $data['totalPaySum'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','=',2)->sum('NetPay');

        
   
  

  Session::put('month', $month);
  Session::put('year', $year);
  Session::put('schmonth', $month." ".$year); 
  //Session::put('bank', $bankName ." ".$bankGroup);

  //DD($data['epayment_detail']);
  $data['M_signatory'] = DB::table('tblmandatesignatory')
       ->leftJoin('tblmandatesignatoryprofiles','tblmandatesignatoryprofiles.id','=','tblmandatesignatory.signatoryID')->orderby('tblmandatesignatory.id')->get();
//dd($data['epayment_detail']);

  //return view('con_epayment.summary200319', $data);
  return view('councilMembers.displayMandate', $data);
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
  
  public function createCouncilMember()
  {
        $data['CourtInfo']=$this->CourtInfo();
    if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
    if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
    $data['banks'] = DB::table('tblbanklist')->get();
    
    $data['titles'] = DB::table('tbltitle')->get();
    $data['cm'] = DB::table('tblper')
    ->leftJoin('tblbanklist','tblbanklist.bankID','=','tblper.bankID')
    ->where('employee_type','=',2)
    ->where('staff_status','=',1)
    ->get();
    
      return view('councilMembers.addCouncilMember',$data);
  }
  
   public function editCouncilMember($id)
  {
    $data['CourtInfo']=$this->CourtInfo();
    if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
    if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
    $data['banks'] = DB::table('tblbanklist')->get();
    
    $data['titles'] = DB::table('tbltitle')->get();
    
    $data['cm'] = DB::table('tblper')
    ->leftJoin('tblbanklist','tblbanklist.bankID','=','tblper.bankID')
    
    ->where('ID','=',$id)
    ->first();
    
      return view('councilMembers.editCouncilMember',$data);
  }
  
  public function saveCouncilMember(Request $request)
  {
     $checkupdate=DB::table('tblper')->insert(array( 
                'grade'                => 1,
                'step'                 => 1,
                'bankID'               => $request['bank'],  
                'fileNo'               => $request['pvNumber'],
                'AccNo'                => $request['accountNumber'],
                'rank'                 => 2,
                'courtID'              => 9,
                'divisionID'           => 15,
                'first_name'           => $request['firstName'],
                'surname'              => $request['surname'],
                'othernames'           => $request['otherNames'],
                'staff_status'         =>  1,
                'status_value'         => 'Active Service',
                'employee_type'        => 2,
                'council_title'                => $request['title'],
                'updated_at'           => date("Y-m-d"),
            )); 
            return redirect('/council-members/create')->with('msg','successfully Created');
  }
  
  public function updateCouncilMember(Request $request)
  {
         $id = $request['id'];
     $update = DB::table('tblper')->where('ID', $id)->update(array( 
                'grade'                => 1,
                'step'                 => 1,
                'bankID'               => $request['bank'],  
                'AccNo'                => $request['accountNumber'],
                'rank'                 => 2,
                'courtID'              => 9,
                'divisionID'           => 15,
                'first_name'           => $request['firstName'],
                'surname'              => $request['surname'],
                'othernames'           => $request['otherNames'],
                'staff_status'         => $request['status'],
                //'status_value'         => 'Active Service',
                'fileNo'               => $request['pvNumber'],
                'employee_type'        => 2,
                'council_title'                => $request['title'],
                'updated_at'           => date("Y-m-d"),
            )); 
            return redirect('/council-members/create')->with('msg','successfully Updated');
  }
  
  public function councilBankSchedule()
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

  return view('councilMembers.bankScheduleForm',$data);
  }
  
  public function postCouncilBankSchedule(Request $request)
  {
    $month     = trim($request->input('month'));
    $year      = trim($request->input('year'));
    
    $data['month'] = $month;
    $data['year'] = $year;
  
    $data['epayment_detail'] = DB::table('tblpayment_consolidated')
       ->leftJoin('tblbanklist','tblbanklist.bankID','=','tblpayment_consolidated.bank')
       ->join('tblper','tblper.ID','=','tblpayment_consolidated.staffid')
        ->where('tblpayment_consolidated.month',     '=', $month)
        ->where('tblpayment_consolidated.year',      '=', $year)
        ->where('tblpayment_consolidated.rank','=',2)
        ->orderBy('tblper.ordering','ASC')
        ->get();
        
        $data['epayment_total'] = DB::table('tblpayment_consolidated')
        ->where('tblpayment_consolidated.month',     '=', $month)
        ->where('tblpayment_consolidated.year',      '=', $year)
       ->where('tblpayment_consolidated.rank','=',2)
       ->orderBy('tblpayment_consolidated.grade','DESC')
        ->orderBy('tblpayment_consolidated.bank','DESC')
        ->orderBy('tblpayment_consolidated.rank','DESC')
        ->orderBy('tblpayment_consolidated.name','ASC')
        
        ->get();
        
    return view('councilMembers.bankSchedule',$data);
  }
  
  public function analysis()
  {
       $data['CourtInfo']=$this->CourtInfo();
    if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
    if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
      return view('councilMembers.analysis',$data);
  }
  
  public function viewAnalysis(Request $request)
  {
      $year = $request['year'];
      $month = $request['month'];
      $data['month'] = $month;
      $data['year']  = $year;
      $data['analysis'] = DB::table('tblpayment_consolidated')
      ->join('tblbanklist','tblbanklist.bankID','=','tblpayment_consolidated.bank')
      ->where('tblpayment_consolidated.month',     '=', $month)
        ->where('tblpayment_consolidated.year',      '=', $year)
       ->where('tblpayment_consolidated.rank','=',2)
       ->select('*','tblbanklist.bank as bankname')
       ->get();
       return view('councilMembers.viewAnalysis',$data);
  }
  
  public function councilPayrollIndex()
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
   
    return view('councilMembers.councilPayroll',$data);
}

public function councilPayrollReport(Request $request)
  {
    $month             = trim($request->input('month'));
    $year              = trim($request->input('year'));
    

  
     $data['year'] = $year;
    $data['month'] = $month;

        Session::put('schmonth', $month." ".$year); 

      $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
    $this->validate($request, [       
        'month'     => 'required|regex:/^[\pL\s\-]+$/u', 
        'year'      => 'required|integer', 
    ]);
   
    
      
      $data['courtDivisions'] = '';
       $data['payroll_detail'] = DB::table('tblpayment_consolidated')
         ->join('tblper','tblper.ID','=','tblpayment_consolidated.staffid')
          ->where('tblpayment_consolidated.month',     '=', $month)
          ->where('tblpayment_consolidated.year',      '=', $year)
          ->where('tblpayment_consolidated.rank','=',2)
          ->orderBy('tblpayment_consolidated.rank','DESC')
          ->get();
          
        return view('councilMembers.viewCouncilPayroll', $data);
  }



}