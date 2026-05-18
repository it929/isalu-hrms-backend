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




class PaySlipController extends ParentController
{
   
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

     $divisionID = $this->divisionID;
       $divisionsession = session('divsession');
        $data['division'] =  DB::table('tbldivision')->where('courtID','=', session('anycourt'))->get();
        $data['court'] = DB::table('tbl_court')->get();
        $data['users'] = DB::table('tblper')
        //->where('tblper.divisionID', '=', $divisionID)
         //->where('courtID','=', $data['CourtInfo']->courtid)
        ->orderBy('surname', 'Asc')
        ->get();

      

      return view('payslip.index',$data);    
  }

 public function Retrieve(Request $request)
  {
//  dd(date('d/m/Y'));
$month  = trim($request->input('month'));
$year   = trim($request->input('year'));
$fileNo = trim($request->input('fileNo'));
$division = trim($request->input('division'));
$court = trim($request->input('court'));
//dd($month);
 //$division = $this->division;
    $this->validate    
    ($request,[       
      'month'  => 'required|regex:/^[\pL\s\-]+$/u', 
      'year'   => 'required|integer',
      'fileNo' => 'required|string'   
      ]);

  $courtName= DB::table('tbl_court')->where('id','=',$court)->first();
  $data['courtName'] = $courtName->court_name;
  $data['division'] = DB::table('tbldivision')->where('divisionID','=',$division)->first();

$count =  DB::table('tblpayment_consolidated')
        ->join('tblper', 'tblper.ID', '=','tblpayment_consolidated.staffid')      
        ->where('tblpayment_consolidated.staffid', '=',$fileNo)
        ->where('tblpayment_consolidated.year', '=', $year)
        ->where('tblpayment_consolidated.month', '=', $month)
        ->where('tblpayment_consolidated.courtID', '=', $court)
        //->select();
        ->count();
        if($count == 0)
        {
          return redirect('/payslip/create')->with('message','No Record Found');
        }
        else{
//dd($bankName);
$data['reports'] = DB::table('tblpayment_consolidated')
        ->join('tblper', 'tblper.ID', '=','tblpayment_consolidated.staffid')  
             
        ->where('tblpayment_consolidated.staffid', '=',$fileNo)
        ->where('tblpayment_consolidated.year', '=', $year)
        ->where('tblpayment_consolidated.month', '=', $month)
        ->where('tblpayment_consolidated.courtID', '=', $court)
        //->select();
        ->first();
        //dd($data['reports'] );

$detail = DB::table('tblpayment_consolidated')
        ->join('tblper', 'tblper.ID', '=','tblpayment_consolidated.staffid')      
        ->where('tblpayment_consolidated.staffid', '=',$fileNo)
        ->where('tblpayment_consolidated.year', '=', $year)
        ->where('tblpayment_consolidated.month', '=', $month)
        ->where('tblpayment_consolidated.courtID', '=', $court)
        //->select();
        ->first();
       
    $data['bank'] = DB::table('tblbanklist')->where('bankID','=',$detail->bank)->first();


    $data['other_deduct'] = DB::table('tblotherEarningDeduction')
                ->join('tblcvSetup','tblcvSetup.ID','=','tblotherEarningDeduction.CVID')
                ->where('tblotherEarningDeduction.particularID', '=', 2)
                ->where('tblotherEarningDeduction.staffid', '=', $fileNo)
                ->where('tblotherEarningDeduction.year', '=', $year)
        ->where('tblotherEarningDeduction.month', '=', $month)
                ->get();

            $data['other_earn'] = DB::table('tblotherEarningDeduction')
                ->join('tblcvSetup','tblcvSetup.ID','=','tblotherEarningDeduction.CVID')
                ->where('tblotherEarningDeduction.particularID', '=', 1)
 ->where('tblotherEarningDeduction.year', '=', $year)
        ->where('tblotherEarningDeduction.month', '=', $month)
                ->where('tblotherEarningDeduction.staffid', '=', $fileNo)
                ->get();



    return view('payslip.summary', $data);
  }
     }
     
     
      public function personal()
    {
        $data['CourtInfo']=$this->CourtInfo();
        if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
        if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

        $divisionID = $this->divisionID;
        $divisionsession = session('divsession');
        $data['division'] =  DB::table('tbldivision')->where('courtID','=', session('anycourt'))->get();
        $data['court'] = DB::table('tbl_court')->get();
        $data['users'] = DB::table('tblper')
            //->where('tblper.divisionID', '=', $divisionID)
            ->where('divisionID','=', $divisionsession)
            ->orderBy('surname', 'Asc')
            ->get();

        return view('payslip.indexPersonal',$data);
    }


    public function getPersonal(Request $request)
    {
//  dd(date('d/m/Y'));
        //$user = DB::table('users')->where('username','=',auth::user()->fileNo)->first();
        $user = DB::table('tblper')->where('userID','=',auth::user()->id)->first();
        if(!($user))
        {
            return back()->with('err','User does not exist');
        }
        $month  = trim($request->input('month'));
        $year   = trim($request->input('year'));
        $fileNo = $user->ID;
        $division = trim($request->input('division'));
        $court = trim($request->input('court'));

//dd($month);
        //$division = $this->division;
        $this->validate
        ($request,[
            'month'  => 'required|regex:/^[\pL\s\-]+$/u',
            'year'   => 'required|integer',
            //'fileNo' => 'required|string'
        ]);

        $courtName= DB::table('tbl_court')->where('id','=',$court)->first();
        $data['courtName'] = $courtName->court_name;
        $data['division'] = DB::table('tbldivision')->where('divisionID','=',$division)->first();

        $count =  DB::table('tblpayment_consolidated')
        ->join('tblper', 'tblper.ID', '=','tblpayment_consolidated.staffid')      
        ->where('tblpayment_consolidated.staffid', '=',$fileNo)
        ->where('tblpayment_consolidated.year', '=', $year)
        ->where('tblpayment_consolidated.month', '=', $month)
        ->where('tblpayment_consolidated.courtID', '=', $court)
            //->select();
            ->count();
        if($count == 0)
        {
            return redirect('/payslip/personal')->with('message','No Record Found');
        }
        else{
//dd($bankName);
           $data['reports'] = DB::table('tblpayment_consolidated')
        ->join('tblper', 'tblper.ID', '=','tblpayment_consolidated.staffid')  
             
        ->where('tblpayment_consolidated.staffid', '=',$fileNo)
        ->where('tblpayment_consolidated.year', '=', $year)
        ->where('tblpayment_consolidated.month', '=', $month)
        ->where('tblpayment_consolidated.courtID', '=', $court)
        //->select();
        ->first();
        //dd($data['reports'] );

$detail = DB::table('tblpayment_consolidated')
        ->join('tblper', 'tblper.ID', '=','tblpayment_consolidated.staffid')      
        ->where('tblpayment_consolidated.staffid', '=',$fileNo)
        ->where('tblpayment_consolidated.year', '=', $year)
        ->where('tblpayment_consolidated.month', '=', $month)
        ->where('tblpayment_consolidated.courtID', '=', $court)
        //->select();
        ->first();
       
    $data['bank'] = DB::table('tblbanklist')->where('bankID','=',$detail->bank)->first();

$data['leave_grant'] = DB::table('basicsalary')
        ->where('basicsalary.grade', '=',$detail->grade)
        ->where('basicsalary.step', '=', $detail->step)
        ->where('basicsalary.courtID', '=', $court)
        ->where('basicsalary.employee_type', '=', 'JUDICIAL')
        ->first();

    $data['other_deduct'] = DB::table('tblotherEarningDeduction')
                ->join('tblcvSetup','tblcvSetup.ID','=','tblotherEarningDeduction.CVID')
                ->where('tblotherEarningDeduction.particularID', '=', 2)
                ->where('tblotherEarningDeduction.staffid', '=', $fileNo)
                ->where('tblotherEarningDeduction.year', '=', $year)
        ->where('tblotherEarningDeduction.month', '=', $month)
                ->get();

            $data['other_earn'] = DB::table('tblotherEarningDeduction')
                ->join('tblcvSetup','tblcvSetup.ID','=','tblotherEarningDeduction.CVID')
                ->where('tblotherEarningDeduction.particularID', '=', 1)
 ->where('tblotherEarningDeduction.year', '=', $year)
        ->where('tblotherEarningDeduction.month', '=', $month)
                ->where('tblotherEarningDeduction.staffid', '=', $fileNo)
                ->get();
            return view('payslip.personalSlip', $data);
        }
    }


    

}