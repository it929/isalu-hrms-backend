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
     $divisionID = $this->divisionID;
       $divisionsession = session('divsession');
        $data['division'] =  DB::table('tbldivision')->where('courtID','=', session('anycourt'))->get();
        $data['court'] = DB::table('tbl_court')->get();
        $data['users'] = DB::table('tblper')
        //->where('tblper.divisionID', '=', $divisionID)
         ->where('divisionID','=', $divisionsession)
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

$count =  DB::table('tblpayment')
        ->join('tblper', 'tblper.fileNo', '=','tblpayment.fileNo')      
        ->where('tblpayment.fileNo', '=',$fileNo)
        ->where('tblpayment.year', '=', $year)
        ->where('tblpayment.month', '=', $month)
        ->where('tblpayment.courtID', '=', $court)
        //->select();
        ->count();
        if($count == 0)
        {
          return redirect('/payslip/create')->with('message','No Record Found');
        }
        else{
//dd($bankName);
$data['payslip_detail'] = DB::table('tblpayment')
        ->join('tblper', 'tblper.fileNo', '=','tblpayment.fileNo')
             
        ->where('tblpayment.fileNo', '=',$fileNo)
        ->where('tblpayment.year', '=', $year)
        ->where('tblpayment.month', '=', $month)
        ->where('tblpayment.courtID', '=', $court)
        //->select();
        ->get();

$detail = DB::table('tblpayment')
        ->join('tblper', 'tblper.fileNo', '=','tblpayment.fileNo')      
        ->where('tblpayment.fileNo', '=',$fileNo)
        ->where('tblpayment.year', '=', $year)
        ->where('tblpayment.month', '=', $month)
        ->where('tblpayment.courtID', '=', $court)
        //->select();
        ->first();
    $data['bank'] = DB::table('tblbanklist')->where('bankID','=',$detail->bank)->first();

$data['leave_grant'] = DB::table('basicsalary')
        ->where('basicsalary.grade', '=',$detail->grade)
        ->where('basicsalary.step', '=', $detail->step)
        ->where('basicsalary.courtID', '=', $court)
        ->where('basicsalary.employee_type', '=', 'JUDICIAL')
        ->first();


//dd($data);
         // Session::put('schmonth', $month." ".$year); 
       //   Session::put('date', date('d/m/Y'));
     //     Session::put('bank', $bankName ." ".$bankGroup);
//dd($data);
    return view('payslip.summary', $data);
  }
     }

}