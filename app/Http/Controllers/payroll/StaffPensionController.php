<?php
namespace App\Http\Controllers\payroll;
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

class StaffPensionController extends ParentController
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
//public $division; 
public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
  $this->middleware('auth');
            return $next($request);
        });
    }

public function index()
{
   $data['allbanklist']  = DB::table('tblbanklist')->get();
   
   $data['staffpension']= "";
   $data['month']       = "";
   $data['year']        = "";
   $data['message']     = "";
   $data['errormessage'] = "";
   
   return view('staffpension.index',$data);    
}
public function Calculate(Request $request)
{
        
       $data['staffpension']= "";
       $data['message']     = "";
       $data['errormessage'] = "";
   
      $month = trim($request->input('month'));
      $year = trim($request->input('year'));
      $date = date('Y-m-d');
      
      $data['month']         =    $request->input('month');
      $data['year']          =    $request->input('year');
       
      $this->validate($request,[      
         
        'month'         => 'required|regex:/^[\pL\s\-]+$/u', 
        'year'          => 'required|integer', 
       
      ]);
        
        //salary sum
        $data['staffpension'] = DB::table('tblpayment_consolidated')
        ->where('tblpayment_consolidated.rank', '=', 0)
        ->where('tblpayment_consolidated.month', '=', $month)
        ->where('tblpayment_consolidated.year',  '=', $year)
        ->leftjoin('tblper','tblper.fileNo','=','tblpayment_consolidated.fileNo')
        ->orderby('tblper.fileNo','asc')
        ->get();
      
        $data['message'] = 'Query successfull';
        //$data['errormessage'] = 'Query unsuccessfull';
        
        return view('staffpension.index',$data);    
   

 }
}