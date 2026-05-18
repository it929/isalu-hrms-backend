<?php

namespace App\Http\Controllers\hr;

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
	 $data['CourtInfo']=$this->CourtInfo();
	 if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
	 if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
	 
	  $data['success'] = "";
	  $data['showcourt'] = true;  
	   $court= trim($request['court']);
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
	   if ($this->ConfirmCheckAudit($court,$division,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month))
	   {
	   $data['warning'] = "The computation is already passed Checking. It cannot be recompute again!!!";
	   return view('salarycomputation.compute', $data);
	   }
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
	   $period=$data['PayrollActivePeriod']->year."-".date("n", strtotime($data['PayrollActivePeriod']->month))."-1";
	   
	   $payrolldata=$this->PayrollStaffParameter($court,$division);
	   
	  foreach ($payrolldata as $b){
	  $LEAV=0;
	  
	  $ArrearComputation=$this->ArrearComputation($b->staffid,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month);
	  $othercomputation=$this->OtherEarn($b->staffid,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month);
	  $AEarn=$ArrearComputation->Earn;
	  $OEarn=$othercomputation->Earn;
	  $AD=$ArrearComputation->Deduction;
	  $OD=$othercomputation->Deduction;;
	  $TEarn=$b->amount+$b->housing+$b->transport+$b->furniture+$b->peculiar+$b->driver+$b->servant+$b->meal+$b->utility+$b->leave_bonus+$LEAV+$AEarn+$OEarn;
	  $TD=$b->tax+$b->nhf+$b->unionDues+$b->pension+$AD+$OD;
	  $NetPay=$TEarn-$TD;
	   DB::table('tblpayment')->insert(array(
			'courtID'	    	=> $b->courtID,
			'divisionID'    	=> $b->divisionID,
			'current_state'    	=> $b->current_state,
			'staffid'    	=> $b->staffid,
			'fileNo'    	=> $b->fileNo,
			'name'    	=> $b->surname.' '.$b->first_name.' '.$b->othernames,
			'year'    => $data['PayrollActivePeriod']->year,
			'month'    	=> $data['PayrollActivePeriod']->month,
			'grade'    	=> $b->grade,
			'step'      	=> $b->step,
			'bank'  	=> $b->bankID,
			'bankGroup'    	=> $b->bankGroup,
			'bank_branch'   => $b->bank_branch,
                        'AccNo'    	=> $b->AccNo,
                        'Bs'    	=> $b->amount,
                        'HA'    	=> $b->housing,
			'TR'    	=> $b->transport,
			'FUR'      	=> $b->furniture,
			'PEC'  		=> $b->peculiar,
			'UTI'    	=> $b->utility,
			'DR'    	=> $b->driver,
                        'SER'    	=> $b->servant,
			'ML'    	=> $b->meal,
                        'LEAV'    	=> $b->leave_bonus,
                        'AEarn'    	=> $AEarn,
                        'OEarn'    	=> $OEarn,
			'TAX'    	=> $b->tax,
			'NHF'      => $b->nhf,
			'PEN'  => $b->pension,
			'UD'  => $b->unionDues,
			'AD'    	=> $AD,
			'OD'    	=> $OD,
                        'TEarn'    	=> $TEarn,
                        'TD'    	=> $TD,
                        'NetPay'    	=> $NetPay,
                        'payment_status'    	=> 1,
                        
                       
		));
		
	   }
	   $data['success'] = "Salary computation is successfully done!";
	   }
	   
	   
	   if ( isset( $_POST['Re-Compute'] ) ) {
	   $data['success'] = "Recomputation complete!";
	   }
	   
	   return view('salarycomputation.compute', $data);
	   
	   
	
   }
   public function ComputeConsolidatedSalary(Request $request)
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
	   if ($this->ConfirmCheckAuditCon($court,$division,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month))
	   {
	   $data['warning'] = "The computation is already passed Checking. It cannot be recompute again!!!";
	   return view('salarycomputation.compute', $data);
	   }
	   if ($this->ConfirmCheckLockCon($court,$division,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month))
	   {
	   $data['warning'] = "This month computation is already locked. It cannot be recomputed again!!!";
	   return view('salarycomputation.compute', $data);
	   }
	   
	   $this->DeleteConsolidatedPayrollperiod($court,$division,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month,$data['bank']);
	   $this->DeletePayrollArrearperiod($court,$division,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month,$data['bank']);
	   $this->DeletePayrollOverdueArrearperiod($court,$division,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month,$data['bank']);
	   $this->DeletePayrollStaffCV($court,$division,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month,$data['bank']);
	   
	   }
	   if ( isset( $_POST['Compute'] ) || isset( $_POST['Re-Compute'] ) ) {
	   if ($this->ConfirmConsolidatedPayrollperiod($court,$division,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month,$data['bank']))
	   {
	   $data['warning'] = "The computation is already done for this period";
	   return view('salarycomputation.compute', $data);
	   }
	   
	   $period=$data['PayrollActivePeriod']->year."-".date("n", strtotime($data['PayrollActivePeriod']->month))."-1";
	   $payrolldata=$this->PayrollStaffParameterCon($court,$division,$data['bank']);
	  $IsSOTPeriod=$this->IsSOTPeriod($data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month,$court);
	  foreach ($payrolldata as $b){
	  $LEAV=0;
	  $sot=0;
	  $tax_sot=0;
	  if($IsSOTPeriod){
	        $SpecialOverTime=$this->SpecialOverTime($b->staffid,$court,$b->grade);
	        //dd($SpecialOverTime);
	        $sot=$SpecialOverTime->gross;
	        $tax_sot=$SpecialOverTime->tax;   
	  }
	  $icount=$this->MonthCount($b->staffid,$data['PayrollActivePeriod']->month,$data['PayrollActivePeriod']->year);
	  $ArrearComputation=$this->ArrearComputationCosolidatedNew($b->staffid,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month);
	  $OverdueArrearComputation=$this->OverdueArrearComputationCosolidatedNew($b->staffid,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month);
	  $othercomputation=$this->OtherEarn($b->staffid,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month);
	  $AEarn=$ArrearComputation->basic+$OverdueArrearComputation->basic;
	  $OEarn=$othercomputation->Earn;
	  $AD=$ArrearComputation->Deduction+$OverdueArrearComputation->Deduction;
	  $OD=$othercomputation->Deduction;
	  $TEarn=($b->employee_type==5)?(($b->amount+$b->housing+$b->transport+$b->furniture+$b->driver+$b->servant+$b->meal+$b->utility+$b->leave_bonus)*$icount) +$LEAV+$AEarn+$OEarn+$sot+($b->peculiar*$icount) + $ArrearComputation->peculiar+ $OverdueArrearComputation->peculiar
	  : (($b->amount+$b->housing+$b->transport+$b->furniture+$b->peculiar+$b->driver+$b->servant+$b->meal+$b->utility+$b->leave_bonus)*$icount) +$LEAV+$AEarn+$OEarn+$sot;
	  $TD=($b->employee_type==5)?(($b->tax+$b->nhf+$b->unionDues)*$icount)+($b->amount*$icount+($b->peculiar*$icount) + $ArrearComputation->peculiar+ $OverdueArrearComputation->peculiar +$this->TaxableEarning($b->staffid, $data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month)) * 0.08 + $AD + $OD + $tax_sot:(($b->tax+$b->nhf+$b->unionDues+$b->pension)*$icount)+$AD+$OD+$tax_sot;
	  $NetPay=$TEarn-$TD;
	   DB::table('tblpayment_consolidated')->insert(array(
			'courtID'	    	=> $b->courtID,
			'divisionID'    	=> $b->divisionID,
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
            'SOT'    	=> round($sot,2),
            'TAX_SOT'    	=> round($tax_sot,2),
            'Bs'    	=> round($b->amount*$icount,2),
            'HA'    	=> round($b->housing*$icount,2),
			'TR'    	=> round($b->transport*$icount,2),
			'FUR'      => round($b->furniture*$icount,2),
			'PEC'  => round(($b->peculiar*$icount) + $ArrearComputation->peculiar+ $OverdueArrearComputation->peculiar,2) ,
			'UTI'    	=>round( $b->utility*$icount,2),
			'DR'    	=> round($b->driver*$icount,2),
            'SER'    	=> round($b->servant*$icount,2),
			'ML'    	=> round($b->meal*$icount,2),
            'LEAV'    	=> round($b->leave_bonus*$icount,2),
            'AEarn'    	=> round($AEarn,2),
            'OEarn'    	=> round($OEarn,2),
			'TAX'    	=> round(($b->tax*$icount)+$ArrearComputation->tax+$OverdueArrearComputation->tax+$tax_sot,2),
			'NHF'      => round(($b->nhf*$icount)+$ArrearComputation->nhf+$OverdueArrearComputation->nhf,2),
			'PEN'  => round(($b->employee_type==5)? ($b->amount*$icount+($b->peculiar*$icount) + $ArrearComputation->peculiar+ $OverdueArrearComputation->peculiar+$this->TaxableEarning($b->staffid, $data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month)) * 0.08 : ($b->pension*$icount)+$ArrearComputation->pension+$OverdueArrearComputation->pension,2),
			'UD'  => round(($b->unionDues*$icount)+$ArrearComputation->unionDues+$OverdueArrearComputation->unionDues,2),
			'AD'    	=> round($AD,2),
			'OD'    	=> round($OD,2),
            'TEarn'    	=> round(($b->employee_type==5)? $TEarn : $TEarn + $ArrearComputation->peculiar + $OverdueArrearComputation->peculiar,2),
            'TD'    	=> round($TD,2),
            'NetPay'    	=> round(($b->employee_type==5)? $NetPay : $NetPay+ $ArrearComputation->peculiar + $OverdueArrearComputation->peculiar,2),
            'payment_status'    	=> 1,
            'basic_real'    	=> round($b->basic,2),
			'NHIS'    	=> round((($b->employee_type==5)? $TEarn : $TEarn + $ArrearComputation->peculiar + $OverdueArrearComputation->peculiar-$OEarn)*0.05,2),// $b->NHIS,
			'NSITF'      => round((($b->employee_type==5)? $TEarn : $TEarn + $ArrearComputation->peculiar + $OverdueArrearComputation->peculiar-$OEarn)*0.01,2),//$b->NSITF,           
                       
		));
		
	   }
	   $this->addLog("Salary Computation for $month $year");
	   $data['success'] = "Salary computation is successfully done!";
	   }
	   
	   
	   if ( isset( $_POST['Re-Compute'] ) ) {
	   $data['success'] = "Recomputation complete!";
	   }
	   
	   return view('salarycomputation.compute', $data);
	   
	   
	
   } 
public function ComputeConsolidatedSalaryOld(Request $request)
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
	  //dd( $data['PayrollActivePeriod']);
	   if ( isset( $_POST['Re-Compute'] ) ) {
	   if ($this->ConfirmCheckAuditCon($court,$division,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month))
	   {
	   $data['warning'] = "The computation is already passed Checking. It cannot be recompute again!!!";
	   return view('salarycomputation.compute', $data);
	   }
	   $this->DeleteConsolidatedPayrollperiod($court,$division,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month,$data['bank']);
	   $this->DeletePayrollArrearperiod($court,$division,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month,$data['bank']);
	   $this->DeletePayrollStaffCV($court,$division,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month,$data['bank']);
	   
	   }
	   if ( isset( $_POST['Compute'] ) || isset( $_POST['Re-Compute'] ) ) {
	   if ($this->ConfirmConsolidatedPayrollperiod($court,$division,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month,$data['bank']))
	   {
	   $data['warning'] = "The computation is already done for this period";
	   return view('salarycomputation.compute', $data);
	   }
	   //dd($data['PayrollActivePeriod']->year);
	   $period=$data['PayrollActivePeriod']->year."-".date("n", strtotime($data['PayrollActivePeriod']->month))."-1";
	   //dd($period);
	   //$this->StaffDueforArrear($court,$division, $period );
	   $payrolldata=$this->PayrollStaffParameterCon($court,$division,$data['bank']);
	   //dd($payrolldata);
	   foreach ($payrolldata as $b){
	  $LEAV=0;
	  $ArrearComputation=$this->ArrearComputationCosolidated($b->staffid,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month);
	  $othercomputation=$this->OtherEarn($b->staffid,$data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month);
	  $AEarn=$ArrearComputation->Earn;
	  $OEarn=$othercomputation->Earn;
	  $AD=$ArrearComputation->Deduction;
	  $OD=$othercomputation->Deduction;;
	  $TEarn=$b->amount+$b->housing+$b->transport+$b->furniture+$b->peculiar+$b->driver+$b->servant+$b->meal+$b->utility+$b->leave_bonus+$LEAV+$AEarn+$OEarn;
	  $TD=$b->tax+$b->nhf+$b->unionDues+$b->pension+$AD+$OD;
	  $NetPay=$TEarn-$TD;
	   DB::table('tblpayment_consolidated')->insert(array(
			'courtID'	    	=> $b->courtID,
			'divisionID'    	=> $b->divisionID,
			'current_state'    	=> $b->current_state,
			'staffid'    	=> $b->staffid,
			'fileNo'    	=> $b->fileNo,
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
                        'Bs'    	=> $b->amount,
                        'HA'    	=> $b->housing,
			'TR'    	=> $b->transport,
			'FUR'      => $b->furniture,
			'PEC'  => $b->peculiar,
			'UTI'    	=> $b->utility,
			'DR'    	=> $b->driver,
                        'SER'    	=> $b->servant,
			'ML'    	=> $b->meal,
                        'LEAV'    	=> $b->leave_bonus,
                        'AEarn'    	=> $AEarn,
                        'OEarn'    	=> $OEarn,
			'TAX'    	=> $b->tax,
			'NHF'      => $b->nhf,
			'PEN'  => $b->pension,
			'UD'  => $b->unionDues,
			'AD'    	=> $AD,
			'OD'    	=> $OD,
                        'TEarn'    	=> $TEarn,
                        'TD'    	=> $TD,
                        'NetPay'    	=> $NetPay,
                        'payment_status'    	=> 1,
                        
                       
		));
		
	   }
	   $data['success'] = "Salary computation is successfully done!";
	   }
	   
	   
	   if ( isset( $_POST['Re-Compute'] ) ) {
	   $data['success'] = "Recomputation complete!";
	   }
	   
	   return view('salarycomputation.compute', $data);
	   
	   
	
   } 
	public function SalaryStructure(Request $request)
	 {   
   	   	$data['error'] = "";
	   	$data['warning'] = "";
	  	$data['success'] = "";
	  	$data['CourtInfo']=$this->CourtInfo();
	 	if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
	  	$data['court'] = $request['court'];
	  	$data['grade'] = $request['grade'];
	  	$data['step'] = $request['step'];
	  	$data['employeetype'] = $request['employeetype'];
	  	$data['Rate'] =$this->RateCode();
	  	$data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
	  	$data['EmploymentTypeList'] = DB::table('tblemployment_type')->select('id', 'employmentType')->get();
	  	$data['PayStructure'] =$this->SalaryPayStructure($data['court'],$data['grade'],$data['step'],$data['employeetype']);
	  	return view('salarycomputation.structuresetup', $data);
	}
	public function LockPeriod(Request $request)
	   {     	
	   	
	   	$data['month'] = $request["month"];
	   	$data['year'] = $request["year"];
	   	if ( isset( $_POST['process'] ) ) {
	   	 $this->validate($request, [
	          'year'      => 'required|string',
	          'month'      => 'required|string',
	        ]);
	        if (!DB::table('tblpayment_consolidated')->where('year',$request['year'])->where('month',$request['month'])->update(['salary_lock' => 1, ])){
	        return back()->with('error_message', 'The Selected period is not active!');}
	        return back()->with('message', 'Period locked Successfully');;
	   	}
	   	//$data['CurrentPeriod'] = $this->CurrentPeriod();
	   	$data['activemonth'] = DB::select("SELECT `year`,`month`, (CASE WHEN salary_lock=1 THEN 'Lock' ELSE 'Open' END) AS status FROM `tblpayment_consolidated` group by`year`,`month`  order by`ID`");
	   	//$data['Quarterslist'] = $this->Quarterslist();
	   	return view('activeMonth.active_monthlock', $data);
	   	
	   }
	   public function UnLockPeriod(Request $request)
	   {  
	       $data['error'] = "";
	   	$data['warning'] = "";
	  	$data['success'] = "";
	   	$data['month'] = $request["month"];
	   	$data['year'] = $request["year"];
	   	$data['CourtInfo']=$this->CourtInfo();
    	if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
    	if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
        $data['court']= trim($request['court']);
	    $data['division']= trim($request['division']);
    	$data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
	    $data['DivisionList'] = $this->DivisionList1($data['court']);
	    $data['PayrollActivePeriod'] =$this->PayrollActivePeriod($data['court']);
	   	if ( isset( $_POST['unlock'] ) ) {
	   	 $this->validate($request, [
	          'year'      => 'required|string',
	          'month'      => 'required|string',
	        ]);
	        if (!DB::table('tblpayment_consolidated')->where('year',$request['year'])->where('month',$request['month'])->update(['salary_lock' => 0, ])){
	        return back()->with('error_message', 'The Selected period is active!');}
	        return back()->with('message', 'Period unlocked Successfully');;
	   	}
	   
	   	$data['activemonth'] = DB::select("SELECT `year`,`month`, (CASE WHEN salary_lock=1 THEN 'Lock' ELSE 'Open' END) AS status FROM `tblpayment_consolidated` group by`year`,`month`  order by`ID`");
	   	
	   	return view('activeMonth.periodunlock', $data);
	   	
	   }

}