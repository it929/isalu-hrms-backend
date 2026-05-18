<?php

namespace App\Http\Controllers\hr;

use Illuminate\Http\Request;
//use Auth;
use App\Http\Requests;
use DB;
use Auth;
use Session;
class PayrollArrearOnlyController extends functionController_arrear
{
	public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->middleware('auth');
        $this->username = Session::get('userName');
            return $next($request);
        });
    }//
 
   
  
   
   public function ArrearsComputation(Request $request)
   {   
   	   $data['error'] = "";
	   $data['warning'] = "";
	 $data['CourtInfo']=$this->CourtInfo();
	 if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
	 if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
	  $data['success'] = "";
	  $data['showcourt'] = true;  
	   $court= trim($request['court']);
	   //die($court);
	   $data['court'] = $court;   
	   $division= trim($request['division']);
	   $data['division'] = $division; 
	   
	   $year= trim($request['year']);
	   $data['year'] = $year;
	   $month= trim($request['month']);
	   $data['month'] = $month; 
	   $data['bank'] = $request['bank']; 
	   $data['banklist']=$this->BankList();
	   

	    
	   $data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
	   $data['DivisionList'] = $this->DivisionList1($court);
	   $data['PayrollActivePeriod'] =$this->PayrollActivePeriod($court);
	   if ( isset( $_POST['Re-Compute'] ) ) {
	   }
	   if ( isset( $_POST['Compute'] ) || isset( $_POST['Re-Compute'] ) ) {
	   DB::DELETE ("DELETE FROM `tblpayment_arrears` where year='$year' and month='$month'");
	   $period=$data['PayrollActivePeriod']->year."-".date("n", strtotime($data['PayrollActivePeriod']->month))."-1";
	   $payrolldata=$this->PayrollStaffParameterCon($court,$division,$data['bank']);
	  foreach ($payrolldata as $b){
	  $OverdueArrearComputation=$this->OverdueArrear_old_new($b->staffid,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month);
	  $AEarn=$OverdueArrearComputation->basic;
	  $AD=$OverdueArrearComputation->Deduction;
	  $OD=0;
	  $TEarn=$AEarn;
	  $TD=$AD;
	  $NetPay=$TEarn-$TD;
	   DB::table('tblpayment_arrears')->insert(array(
			'current_state'    	=> $b->current_state,
			'staffid'    	=> $b->staffid,
			'fileNo'    	=> $b->fileNo,
			'employment_type'    	=> $b->employee_type,
			'name'    	=> $b->surname.' '.$b->first_name.' '.$b->othernames,
			'year'    => $data['PayrollActivePeriod']->year,
			'month'    	=> $data['PayrollActivePeriod']->month,
			'rank'    	=> $b->rank,
			'grade'    	=> $b->grade,
			'step'      => $b->step,
			'bank'  => $b->bankID,
			'bankGroup'    	=> $b->bankGroup,
			'bank_branch'    	=> $b->bank_branch,
            'AccNo'    	=> $b->AccNo,
            'Bs'    	=> 0,
            'HA'    	=> 0,//$b->housing*$icount,
			'TR'    	=> 0, //$b->transport*$icount,
			'FUR'      => 0,//$b->furniture*$icount,
			'PEC'  =>  $OverdueArrearComputation->peculiar,
			'UTI'    	=> 0,
			'DR'    	=>0, 
            'SER'    	=> 0,
			'ML'    	=>0,
            'LEAV'    	=>0, 
            'AEarn'    	=> $AEarn,
            'OEarn'    	=> $OEarn,
			'TAX'    	=> $OverdueArrearComputation->tax,//($b->tax*$icount)+$ArrearComputation->tax+$OverdueArrearComputation->tax+$tax_sot,
			'NHF'      => $OverdueArrearComputation->nhf,
			'PEN'  => ($b->employee_type==5)? (  $OverdueArrearComputation->peculiar) * 0.08 : $OverdueArrearComputation->pension,
		    'UD'  => ($b->unionDues*$icount)+$ArrearComputation->unionDues+$OverdueArrearComputation->unionDues,
			'AD'    	=> $AD,
			'OD'    	=> $OD,
            'TEarn'    	=> ($b->employee_type==5)? $TEarn : $TEarn + $OverdueArrearComputation->peculiar,
            'TD'    	=> $TD,
            'NetPay'    => ($b->employee_type==5)? $NetPay : $NetPay + $OverdueArrearComputation->peculiar,
            'payment_status'    	=> 1,
		));
		
	   }
	   $this->addLog("Arrear computation from old to new structure for $month $year");
	   $data['success'] = "Salary computation is successfully done!";
	   }
	   
	   
	   if ( isset( $_POST['Re-Compute'] ) ) {
	   $data['success'] = "Recomputation complete!";
	   }
	   
	   return view('salarycomputation.compute', $data);
	   
	   
	
   } 


}