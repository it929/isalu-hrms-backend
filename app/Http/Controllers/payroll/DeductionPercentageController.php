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

class DeductionPercentageController extends ParentController
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
    $data['data']=DB::table('tbldeduction_percentage')->first();
    return view('deduction_percentage.create',$data);
}

public function add(Request $request)
{
    $this->validate($request,[
        
         'pension'  =>  'required|numeric',
         'tax'      =>  'required|numeric',
         'nhf'      =>  'required|numeric',
         'union'    =>  'required|numeric',
         'nhis'     =>  'required|numeric',
         'nsitf'    =>  'required|numeric',
        ]);
    $id         =   $request->input('id');
    $pension    =   $request->input('pension');
    $tax        =   $request->input('tax');
    $nhf        =   $request->input('nhf');
    $union      =   $request->input('union');
    $nhis       =   $request->input('nhis');
    $nsitf      =   $request->input('nsitf');
    
    DB::table('tbldeduction_percentage')->where('id',$id)->update(['pension'=>$pension, 'tax'=>$tax,'nhf'=>$nhf, 'union_due'=>$union, 'nhis'=>$nhis, 'nsitf'=>$nsitf]);
   	   $rawdata= DB::SELECT ("SELECT * FROM `basicsalaryconsolidated` where  `employee_type`<>2");
   	    $percentages= DB::SELECT ("SELECT * FROM `tbldeduction_percentage`")[0];
   	    foreach ($rawdata as $value) {
   	        $pen= round(($value->amount+ $value->peculiar)*$percentages->pension*0.01,2);
   	        $nhf= round(($value->amount+ $value->peculiar)*$percentages->nhf*0.01,2);
   	        $nhis= round(($value->amount+ $value->peculiar)*$percentages->nhis*0.01,2);
   	        $nsitf= round(($value->amount+ $value->peculiar)*$percentages->nsitf*0.01,2);
   	        $union_due= ((int)$value->grade<14)? round(($value->peculiar)*$percentages->union_due*0.01,2):0;
   	       ($value->employee_type!=5)?DB::table('basicsalaryconsolidated')->where('ID', $value->ID)
        	  ->update([ 'pension' => $pen,'nhf' => $nhf,'NHIS' => $nhis,'NSITF' => $nsitf,'unionDues' => $union_due,]):
        	      DB::table('basicsalaryconsolidated')->where('ID', $value->ID)
        	  ->update([ 'pension' => $pen,'nhf' => $nhf,'NHIS' => $nhis,'NSITF' => $nsitf]);  ;  
   	    }
	  	
    return back()->with('message','Successfully added');

}


}