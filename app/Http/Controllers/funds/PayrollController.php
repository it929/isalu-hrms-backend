<?php

namespace App\Http\Controllers\funds;

use Illuminate\Http\Request;
//use Auth;
use App\Http\Requests;
use DB;
use Auth;
use Session;
class PayrollController extends functionController
{
	public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->middleware('auth');
        $this->username = Session::get('userName');
            return $next($request);
        });
    }//
 
   
   public function ControlVariable(Request $request)
   {   
   	   $data['error'] = "";
	   $data['warning'] = "";
	   
	  $data['success'] = "";
	  $data['showcourt'] = true;
	  
	   
	   $court= trim($request['court']);
	   $data['court'] = $court;   
	   $division= trim($request['division']);
	   $data['division'] = $division; 
	   $staffName= trim($request['staffName']);
	   $data['staffName'] = $staffName;
	   $hiddenstaffName= trim($request['hiddenstaffName']);
	   $vehicle= trim($request['vehicle']);
	   $data['vehicle'] = $vehicle;
	   $nicnCoop= trim($request['nicnCoop']);
	   $data['nicnCoop'] = $nicnCoop; 
	   $motor= trim($request['motor']);
	   $data['motor'] = $motor; 
	   $bicycle= trim($request['bicycle']);
	   $data['bicycle'] = $bicycle;
	   $labour= trim($request['labour']);
	   $data['labour'] = $labour;
	   $fedsec= trim($request['fedsec']);
	   $data['fedsec'] = $fedsec;
	   $fedhouse= trim($request['fedhouse']);
	   $data['fedhouse'] = $fedhouse;
	   $hazard= trim($request['hazard']);
	   $data['hazard'] = $hazard;
	   $duty= trim($request['duty']);
	   $data['duty'] = $duty;
	   $allowances= trim($request['allowances']);
	   $data['allowances'] = $allowances; 
	   $phonecharges= trim($request['phonecharges']);
	   $data['phonecharges'] = $phonecharges;
	   $assistant= trim($request['assistant']);
	   $data['assistant'] = $assistant; 
	   $surcharge= trim($request['surcharge']);
	   $data['surcharge'] = $surcharge;
	   $court= trim($request['court']);
	   $data['court'] = $court; 
	   $submittype= trim($request['submittype']);
	   $data['submittype'] = $submittype; 
	   $data['staffList'] = $this->DivisionStaffList($court,$division);
	   
	   $del= trim($request['delcode']);

	    
	   $data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
	   $data['DivisionList'] = $this->DivisionList1($court);
	$data['cv']=$this->FullStaffDetails($staffName);
	if ($hiddenstaffName<>$staffName)
	{
		$staffcv=$this->FStaffCV($staffName);
	   $data['vehicle'] = $staffcv->ugv;
	   $data['nicnCoop'] = $staffcv->nicnCoop;
	   $data['motor'] = $staffcv->motorAdv;
	   $data['bicycle'] = $staffcv->bicycleAdv;  
	   $data['labour'] = $staffcv->ctlsLab;
	   $data['fedsec'] = $staffcv->ctlsFed;
	   $data['fedhouse'] = $staffcv->fedHousing;
	   $data['hazard'] = $staffcv->hazard;
	   $data['duty'] = $staffcv->callDuty;
	   $data['allowances'] = $staffcv->shiftAll;   
	   $data['phonecharges'] = $staffcv->phoneCharges;   
	   $data['assistant'] = $staffcv->pa_deduct;  
	   $data['surcharge'] = $staffcv->surcharge;
	   $data['submittype']=$staffcv->submittype;
	   
	
	}
	if ( isset( $_POST['add'] ) ) {
	DB::table('tblcv')->insert(array(
			'ugv'	    	=> $vehicle,
			'nicnCoop'    	=> $nicnCoop,
			'motorAdv'    	=> $motor,
			'bicycleAdv'    => $bicycle,
			'ctlsLab'    	=> $labour,
			'ctlsFed'    	=> $fedsec,
			'fedHousing'    => $fedhouse,
			'hazard'    	=> $hazard,
			'callDuty'    	=> $duty,
			'shiftAll'      => $allowances,
			'phonecharges'  => $phonecharges,
			'pa_deduct'    	=> $assistant,
			'surcharge'    	=> $surcharge,
                        'fileNo'    	=> $staffName,
                        'courtID'    	=> $court,
		));
		$data['submittype']='1';
		}
		if ( isset( $_POST['update'] ) ) {
		DB::table('tblcv')->where('fileNo', $staffName)->update(array(
			'ugv'	    	=> $vehicle,
			'nicnCoop'    	=> $nicnCoop,
			'motorAdv'    	=> $motor,
			'bicycleAdv'    => $bicycle,
			'ctlsLab'    	=> $labour,
			'ctlsFed'    	=> $fedsec,
			'fedHousing'    => $fedhouse,
			'hazard'    	=> $hazard,
			'callDuty'    	=> $duty,
			'shiftAll'      => $allowances,
			'phonecharges'  => $phonecharges,
			'pa_deduct'    	=> $assistant,
			'surcharge'    	=> $surcharge,
                        'courtID'    	=> $court,
		));
		}
   	return view('payroll.variable.ControlVariable2', $data);
   } 
   public function ComputeSalary(Request $request)
   {   
   	   $data['error'] = "";
	   $data['warning'] = "";
	 
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
	   

	    
	   $data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
	   $data['DivisionList'] = $this->DivisionList1($court);
	   $data['PayrollActivePeriod'] =$this->PayrollActivePeriod($court);
	   
	   if ( isset( $_POST['Re-Compute'] ) ) {
	   $this->DeletePayrollperiod($court,$division,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month);
	   $this->DeletePayrollArrearperiod($court,$division,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month);
	   $this->DeletePayrollStaffCV($court,$division,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month);
	   
	   }
	   if ( isset( $_POST['Compute'] ) || isset( $_POST['Re-Compute'] ) ) {
	   if ($this->ConfirmPayrollperiod($court,$division,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month))
	   {
	   $data['warning'] = "The computation is already do for this period";
	   return view('salarycomputation.compute', $data);
	   }
	   $payrolldata=$this->PayrollStaffParameter($court,$division);
	   //die($payrolldata);
	   foreach ($payrolldata as $b){
	  $LEAV=0;
	  $ArrearComputation=$this->ArrearComputation($b->fileNo,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month);
	  $othercomputation=$this->OtherEarn($b->fileNo,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month);
	  $AEarn=$ArrearComputation->Earn;
	  $OEarn=$othercomputation->Earn;
	  $AD=$ArrearComputation->Deduction;
	  $OD=$othercomputation->Deduction;;
	  $TEarn=$b->amount+$b->housing+$b->transport+$b->furniture+$b->peculiar+$b->driver+$b->servant+$LEAV+$AEarn+$OEarn;
	  $TD=$b->tax+$b->nhf+$b->pension+$AD+$OD;
	  $NetPay=$TEarn-$TD;
	   DB::table('tblpayment')->insert(array(
			'courtID'	    	=> $b->courtID,
			'divisionID'    	=> $b->divisionID,
			'current_state'    	=> $b->current_state,
			'fileNo'    	=> $b->fileNo,
			'name'    	=> $b->surname.' '.$b->first_name.' '.$b->othernames,
			'year'    => $data['PayrollActivePeriod']->year,
			'month'    	=> $data['PayrollActivePeriod']->month,
			'grade'    	=> $b->grade,
			'step'      => $b->step,
			'bank'  => $b->bankID,
			'bankGroup'    	=> $b->bankGroup,
			'bank_branch'    	=> $b->bank_branch,
                        'AccNo'    	=> $b->AccNo,
                        'Bs'    	=> $b->amount,
                        'HA'    	=> $b->housing,
			'TR'    	=> $b->transport,
			'FUR'      => $b->furniture,
			'PEC'  => $b->peculiar,
			'UTI'    	=> $b->utility,
			'DR'    	=> $b->driver,
                        'SER'    	=> $b->servant,
                        'LEAV'    	=> $LEAV,
                        'AEarn'    	=> $AEarn,
                        'OEarn'    	=> $OEarn,
			'TAX'    	=> $b->tax,
			'NHF'      => $b->nhf,
			'PEN'  => $b->pension,
			'AD'    	=> $AD,
			'OD'    	=> $OD,
                        'TEarn'    	=> $TEarn,
                        'TD'    	=> $TD,
                        'NetPay'    	=> $NetPay,
                        'current_state'=>'none'
                        ,'bank'=>'none'
                        ,'bankGroup'=>'none'
                        ,'bank_branch'=>'none'
                        ,'AccNo'=>'none'
		));
		
	   }
	   $data['success'] = "Salary computation is successfully done!";
	   }
	   
	   
	   if ( isset( $_POST['Re-Compute'] ) ) {
	   $data['success'] = "Recomputation complete!";
	   }
	   
	   return view('salarycomputation.compute', $data);
	   
	   
	
   } 


}