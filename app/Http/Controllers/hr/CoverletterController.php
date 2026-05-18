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

class CoverletterController extends ParentController
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

public function index()
{
    return view('coverletter.select');
}

public function view(Request $request)
{
    
    $data['year']    =   $request->input('year');
    $data['month']   =   $request->input('month');
    
    $data['M']=strtolower($data['month']);
    //dd(ucwords($M));
    
    $data['tax']=DB::table('tblpayment_consolidated')
    ->where('year',$data['year'])
    ->where('month',$data['month'])
    ->where('rank','!=',2)
    //->leftjoin('')
    ->sum('TAX');
    
    $data['nhf']=DB::table('tblpayment_consolidated')
    ->where('year',$data['year'])
    ->where('month',$data['month'])
    ->where('rank','!=',2)
    //->leftjoin('')
    ->sum('NHF');
    
    $data['ud']=DB::table('tblpayment_consolidated')
    ->where('year',$data['year'])
    ->where('month',$data['month'])
    ->where('rank','!=',2)
    //->leftjoin('')
    ->sum('UD');
    
    $data['nhis']=DB::table('tblpayment_consolidated')
    ->where('year',$data['year'])
    ->where('month',$data['month'])
    ->where('rank','!=',2)
    //->leftjoin('')
    ->sum('NHIS');
    
    $data['nsitf']=DB::table('tblpayment_consolidated')
    ->where('year',$data['year'])
    ->where('month',$data['month'])
    ->where('rank','!=',2)
    //->leftjoin('')
    ->sum('NSITF');
    
    $data['pen']=DB::table('tblpayment_consolidated')
    ->where('year',$data['year'])
    ->where('month',$data['month'])
    ->where('rank','!=',2)
    //->leftjoin('')
    ->sum('PEN');
    
    $data['coopsaving']=DB::table('tblotherEarningDeduction')
    ->where('tblotherEarningDeduction.particularID',2)
    ->where('tblotherEarningDeduction.CVID',15)
    ->where('year',$data['year'])
    ->where('month',$data['month'])
    ->leftjoin('tblcvSetup','tblotherEarningDeduction.CVID','=','tblcvSetup.ID')
    ->sum('tblotherEarningDeduction.amount');
    
    $data['cooploan']=DB::table('tblotherEarningDeduction')
    ->where('tblotherEarningDeduction.particularID',2)
    ->where('tblotherEarningDeduction.CVID',16)
    ->where('year',$data['year'])
    ->where('month',$data['month'])
    ->leftjoin('tblcvSetup','tblotherEarningDeduction.CVID','=','tblcvSetup.ID')
    ->sum('tblotherEarningDeduction.amount');
    
    $data['housing_loan']=DB::table('tblotherEarningDeduction')
    ->where('tblotherEarningDeduction.particularID',2)
    ->where('tblotherEarningDeduction.CVID',2)
    ->where('year',$data['year'])
    ->where('month',$data['month'])
    //->leftjoin('tblcvSetup','tblotherEarningDeduction.CVID','=','tblcvSetup.ID')
    //->leftjoin('tblearningParticular','tblotherEarningDeduction.particularID','=','tblearningParticular.ID')
    ->sum('tblotherEarningDeduction.amount');


    $data['salary_advance']=DB::table('tblotherEarningDeduction')
    ->where('tblotherEarningDeduction.particularID',2)
    ->where('tblotherEarningDeduction.CVID',18)
    ->where('year',$data['year'])
    ->where('month',$data['month'])
    //->where('rank','!=',2)
    //->leftjoin('tblcvSetup','tblotherEarningDeduction.CVID','=','tblcvSetup.ID')
    //->leftjoin('tblearningParticular','tblotherEarningDeduction.particularID','=','tblearningParticular.ID')
    ->sum('tblotherEarningDeduction.amount');

    $data['total']=$data['tax']+$data['nhf']+$data['ud']+$data['nhis']+$data['nsitf']+$data['coopsaving']+$data['cooploan']+$data['housing_loan'];
    
    $earning=DB::table('tblpayment_consolidated')
    ->where('year',$data['year'])
    ->where('month',$data['month'])
    ->where('rank','!=',2)
    //->leftjoin('')
    ->sum('TEarn');
    $harzard=DB::table('tblotherEarningDeduction')
    ->where('tblotherEarningDeduction.CVID',4)
    ->where('year',$data['year'])
    ->where('month',$data['month'])
    ->sum('tblotherEarningDeduction.amount');
    $callduty=DB::table('tblotherEarningDeduction')
    ->where('tblotherEarningDeduction.CVID',22)
    ->where('year',$data['year'])
    ->where('month',$data['month'])
    ->sum('tblotherEarningDeduction.amount');
    
    $data['gross'] = $earning + $harzard + $callduty;
    
    //dd($fractions);
    return view('coverletter.create',$data);

}

public function council()
{
    return view('coverletter.council');
}

public function councilview(Request $request)
{
    
    $data['year']    =   $request->input('year');
    $data['month']   =   $request->input('month');
    
    $data['M']=strtolower($data['month']);
    //dd(ucwords($M));
    
    $data['tax']=DB::table('tblpayment_consolidated')
    ->where('year',$data['year'])
    ->where('month',$data['month'])
    ->where('rank','=',2)
    //->leftjoin('')
    ->sum('TAX');
    
    /*if(DB::table('tblotherEarningDeduction')
    ->where('tblotherEarningDeduction.CVID',24)
    ->where('year',$data['year'])
    ->where('month',$data['month'])
    ->exists()){
     */   
    $data['council_tax']=DB::table('tblotherEarningDeduction')
    ->where('tblotherEarningDeduction.CVID',24)
    ->where('year',$data['year'])
    ->where('month',$data['month'])
    ->sum('tblotherEarningDeduction.amount');

   

    $data['netpay']=DB::table('tblpayment_consolidated')
    ->where('year',$data['year'])
    ->where('month',$data['month'])
    ->where('rank','=',2)
    //->leftjoin('')
    ->sum('NetPay');
    
    $data['total']=$data['netpay'];
    
    $data['total_six'] = strlen($data['total']);
    $whole = floor($data['total']);      // 1
    $fraction = $data['total'] - $whole; // .25
    $data['fractions']=str_replace(".", "", number_format($fraction,2));
    
    $data['nhis_six'] = strlen($data['tax']);
    $nhiswhole = floor($data['tax']);      // 1
    $nhisfraction = $data['tax'] - $nhiswhole; // .25
    $data['taxfractions']=str_replace(".", "", number_format($nhisfraction,2));
    
    //dd($fractions);
    return view('coverletter.council_view',$data);

}

}