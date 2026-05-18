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

class PromotionPayController extends ParentController
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
   $data['economic_code'] = "";
   $data['month']       = "";
   $data['year']        = "";
   $data['message']     = "";
   $data['errormessage'] = "";
   
   return view('promotionPay.getData',$data);    
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
        $data['staffpromotion'] = DB::connection('npaydb')->table('tblarears_payment')
        ->where('tblarears_payment.month', '=', $month)
        ->where('tblarears_payment.year',  '=', $year)
        ->sum(DB::raw('cumEmolu + callDuty'));
        
        //->leftjoin('tblper','tblper.fileNo','=','tblarears_payment.fileNo')
        //->orderby('tblper.fileNo','asc')
        //->sum('cumEmolu');
      
        $data['message'] = 'Query successfull';
        //$data['errormessage'] = 'Query unsuccessfull';
        
        return view('promotionPay.getData',$data);    
   

 }
 
 
 public function getPromotionArrears(Request $request)
{
  
  $economic_code = trim($request->input('economic_code'));  
  $month = trim($request->input('month'));
  $year = trim($request->input('year'));
  $date = date('Y-m-d');
  
  $data['economic_code'] =    $request->input('economic_code');
  $data['month']         =    $request->input('month');
  $data['year']          =    $request->input('year');
   
  $this->validate($request,[      
     
    'economic_code' => 'required', 
    'month'         => 'required|regex:/^[\pL\s\-]+$/u', 
    'year'          => 'required|integer', 
   
  ]);
    
    if($economic_code==71)
    {
        
        //salary sum
        $data['sumx'] = DB::connection('npaydb')->table('tblarears_payment')
        ->where('tblarears_payment.month', '=', $month)
        ->where('tblarears_payment.year',  '=', $year)
        ->sum(DB::raw('cumEmolu + callDuty'));
        //->sum('cumEmolu'); 
        if($data['sumx'] == 0)
        {
            $data['message'] = 'No record was found';
            return view('promotionPay.getData',$data);
        }
        //allowance
        $data['sumy'] = DB::connection('npaydb')->table('tblarears_payment')
        ->where('tblarears_payment.month', '=', $month)
        ->where('tblarears_payment.year',  '=', $year)
        ->sum(DB::raw('actingAllow + callDuty'));
        //->sum('actingAllow');
        
        $data['sumAmount'] = $data['sumx']; 
        
        $beneficiaryx = DB::connection('npaydb')->table('tblarears_payment')->where('month',$month)->where('year',$year)->value('name');
        $count_beneficiary = DB::connection('npaydb')->table('tblarears_payment')
        ->where('month',$month)
        ->where('year',$year)
        ->count();
        
        //dd($data['sumAmount']);
        $payment = 'arrears';
        
        $payment = 'Promotion Arrears Payment';
    }
    elseif($economic_code==72)
    {
         $data['sumAmount'] = DB::connection('npaydb')->table('tblarears_payment')
        ->where('tblarears_payment.month', '=', $month)
        ->where('tblarears_payment.year',  '=', $year)
        ->sum(DB::raw('actingAllow + callDuty'));
        //->sum('actingAllow');
        
        $beneficiaryx = DB::connection('npaydb')->table('tblarears_payment')->where('month',$month)->where('year',$year)->value('name');
        $count_beneficiary = DB::connection('npaydb')->table('tblarears_payment')
        ->where('month',$month)
        ->where('year',$year)
        ->count();
        $payment = 'Overtime Payment';
    }
    elseif($economic_code==73)
    {
        $data['sumAmount'] = DB::connection('npayrollcondb')->table('tbl_conpayment')
        ->where('tbl_conpayment.month', '=', $month)
        ->where('tbl_conpayment.year',  '=', $year)
        ->sum('gross_pay');
        
        $beneficiaryx = DB::connection('npayrollcondb')->table('tbl_conpayment')->where('month',$month)->where('year',$year)->value('name');
        $count_beneficiary = DB::connection('npayrollcondb')->table('tbl_conpayment')
        ->where('month',$month)
        ->where('year',$year)
        ->count();
    
        $payment = 'Consolidated Revenue Fund Charges';
    }
        
    
    $beneficiary = $beneficiaryx .' and '. $count_beneficiary.' others.';
    $description = 'Being '.$payment.' for the month of '.$month.', '.$year;
   
   // First day of the month.
    $firstday=date('Y-m-01', strtotime($date));
    
    $check_contracts=DB::table('tblcontractDetails')->where('month',$month)->where('year',$year)->where('economic_code_status',$economic_code)->where('is_salary','=',3)->exists();
    $check_trans=DB::table('tblpaymentTransaction')->where('month',$month)->where('year',$year)->where('economic_code_status',$economic_code)->where('is_salary','=',3)->exists();
    
    $check_code=DB::table('tblcontractDetails')->where('economicVoult',$economic_code)->where('month',$month)->where('year',$year)->where('economic_code_status',$economic_code)->where('is_salary','=',3)->exists();
    $check_liabiity=DB::table('tblpaymentTransaction')->where('liabilityStatus',1)->where('month',$month)->where('year',$year)->where('economic_code_status',$economic_code)->where('is_salary','=',3)->exists();
    
    if(($check_trans)&&($check_contracts) && ($payment !='arrears')){
        
       $contract_id = DB::table('tblcontractDetails')->where('month',$month)->where('year',$year)->where('economic_code_status',$economic_code)->where('is_salary','=',3)->update(['economicVoult'=>71,'contractValue'=>$data['sumAmount'],'companyID'=>13,'dateAward'=>$firstday,'contract_Type'=>6,
                'ContractDescriptions'=>$description,'beneficiary'=>$beneficiary,'datecreated'=>$date,'approvedBy'=>Auth::user()->id,'createdby'=>1,'approvalStatus'=>1,'openclose'=>1,'paymentStatus'=>0,'voucherType'=>2,
                'period'=>$year,'isfrom_procurement'=>0,'is_salary'=>2,'month'=>$month,'year'=>$year,'economic_code_status'=>$economic_code]);
                
                DB::table('tblpaymentTransaction')->where('month',$month)->where('year',$year)->where('economic_code_status',$economic_code)->where('is_salary','=',3)->update(['contractTypeID'=>6,'contractID'=>$contract_id,'companyID'=>13,'totalPayment'=>$data['sumAmount'],'paymentDescription'=>$description,
                'amtPayable'=>$data['sumAmount'],'preparedBy'=>Auth::user()->id,'allocationType'=>5,'economicCodeID'=>$economic_code,'status'=>0,'datePrepared'=>$date,'vstage'=>1,'accept_voucher_status'=>1,
                'is_salary'=>2,'month'=>$month,'year'=>$year,'period'=>$year,'economic_code_status'=>$economic_code]);   
    
    }    
    else
    {
        if($check_liabiity)
        {
            //do nothing
        }
        else
        {
            if($check_code){
                
            }
            else
            {
                $contract_id = DB::table('tblcontractDetails')->insertGetId(['economicVoult'=>71,'contractValue'=>$data['sumAmount'],'companyID'=>13,'dateAward'=>$firstday,'contract_Type'=>6,
                'ContractDescriptions'=>$description,'beneficiary'=>$beneficiary,'datecreated'=>$date,'approvedBy'=>Auth::user()->id,'createdby'=>1,'approvalStatus'=>1,'openclose'=>1,'paymentStatus'=>0,'voucherType'=>2,
                'period'=>$year,'isfrom_procurement'=>0,'is_salary'=>2,'month'=>$month,'year'=>$year,'economic_code_status'=>$economic_code]);
                
                DB::table('tblpaymentTransaction')->insert(['contractTypeID'=>6,'contractID'=>$contract_id,'companyID'=>13,'totalPayment'=>$data['sumAmount'],'paymentDescription'=>$description,
                'amtPayable'=>$data['sumAmount'],'preparedBy'=>Auth::user()->id,'allocationType'=>5,'economicCodeID'=>$economic_code,'status'=>0,'datePrepared'=>$date,'vstage'=>1,'accept_voucher_status'=>1,
                'is_salary'=>2,'month'=>$month,'year'=>$year,'period'=>$year,'economic_code_status'=>$economic_code]);
            }
            
                
        }
    }
    
     

    $data['message'] = 'Successfully Submitted';
    $data['errormessage'] = '';
    return view('promotionPay.getData',$data);    
    
}
 
}