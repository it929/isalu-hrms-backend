<?php

namespace App\Http\Controllers\payroll;



use Illuminate\Http\Request;
//
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class PayrollController extends functionController
{
	public function __construct()
	{
        $this->middleware(function ($request, $next) {
            $this->middleware('auth');
		$this->username = Session::get('userName');
            return $next($request);
        });
    } //


	public function ControlVariable(Request $request)
	{
		$data['error'] = "";
		$data['warning'] = "";

		$data['success'] = "";
		$data['showcourt'] = true;


		$court = trim($request['court']);
		$data['court'] = $court;
		$division = trim($request['division']);
		$data['division'] = $division;
		$staffName = trim($request['staffName']);
		$data['staffName'] = $staffName;
		$hiddenstaffName = trim($request['hiddenstaffName']);
		$vehicle = trim($request['vehicle']);
		$data['vehicle'] = $vehicle;
		$nicnCoop = trim($request['nicnCoop']);
		$data['nicnCoop'] = $nicnCoop;
		$motor = trim($request['motor']);
		$data['motor'] = $motor;
		$bicycle = trim($request['bicycle']);
		$data['bicycle'] = $bicycle;
		$labour = trim($request['labour']);
		$data['labour'] = $labour;
		$fedsec = trim($request['fedsec']);
		$data['fedsec'] = $fedsec;
		$fedhouse = trim($request['fedhouse']);
		$data['fedhouse'] = $fedhouse;
		$hazard = trim($request['hazard']);
		$data['hazard'] = $hazard;
		$duty = trim($request['duty']);
		$data['duty'] = $duty;
		$allowances = trim($request['allowances']);
		$data['allowances'] = $allowances;
		$phonecharges = trim($request['phonecharges']);
		$data['phonecharges'] = $phonecharges;
		$assistant = trim($request['assistant']);
		$data['assistant'] = $assistant;
		$surcharge = trim($request['surcharge']);
		$data['surcharge'] = $surcharge;
		$court = trim($request['court']);
		$data['court'] = $court;
		$submittype = trim($request['submittype']);
		$data['submittype'] = $submittype;
		$data['staffList'] = $this->DivisionStaffList($court, $division);

		$del = trim($request['delcode']);


		$data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
		$data['DivisionList'] = $this->DivisionList1($court);
		$data['cv'] = $this->FullStaffDetails($staffName);
		if ($hiddenstaffName <> $staffName) {
			$staffcv = $this->FStaffCV($staffName);
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
			$data['submittype'] = $staffcv->submittype;
		}
		if (isset($_POST['add'])) {
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
			$data['submittype'] = '1';
		}
		if (isset($_POST['update'])) {
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

	public function ComputeConsolidatedSalary(Request $request)
	{
		$penper = DB::table('tbldeduction_percentage')->value('pension') * 0.01;
		$nhfper = DB::table('tbldeduction_percentage')->value('nhf') * 0.01;
		$nhisper = DB::table('tbldeduction_percentage')->value('nhis') * 0.01;
		$nsitfper = DB::table('tbldeduction_percentage')->value('nsitf') * 0.01;

		$data['error'] = "";
		$data['warning'] = "";
		$data['CourtInfo'] = $this->CourtInfo();
		if ($data['CourtInfo']->courtstatus == 0) {
			$request['court'] = $data['CourtInfo']->courtid;
		}
		if ($data['CourtInfo']->divisionstatus == 0) {
			$request['division'] = $data['CourtInfo']->divisionid;
		}
		$data['success'] = "";
		$data['showcourt'] = true;
		$court = trim($request['court']);
		$data['court'] = $court;
		$division = trim($request['division']);
		$data['division'] = $division;
		$year = trim($request['year']);
		$data['year'] = $year;
		$month = trim($request['month']);
		$data['month'] = $month;
		$data['bank'] = $request['bank'];
		$data['banklist'] = $this->BankList();
		$data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
		$data['DivisionList'] = $this->AllDivision();
		//get current logged in user division if not global user
		$data['curDivision'] = $this->curDivision(Auth::user()->id);
		$data['PayrollActivePeriod'] = $this->PayrollActivePeriod($court);

		$payrollActivePeriod = DB::table('tblactivemonth')->first();
		$data['currentSalary'] = DB::table('tblpayment_consolidated')->where('checking_view', '=', 1)->where('year', '=', $payrollActivePeriod->year)->where('month', '=', $payrollActivePeriod->month)->count();


		//Recompute
		if (isset($_POST['Re-Compute'])) {
			if ($this->ConfirmCheckAudit($division, $data['PayrollActivePeriod']->year, $data['PayrollActivePeriod']->month)) {
				$data['warning'] = "The computation is already passed Checking. It cannot be recompute again!!!";
				return view('salarycomputation.compute', $data);
			}
			if ($this->ConfirmCheckLock($division, $data['PayrollActivePeriod']->year, $data['PayrollActivePeriod']->month)) {
				$data['warning'] = "This month computation is already locked. It cannot be recomputed again!!!";
				return view('salarycomputation.compute', $data);
			}


			$this->DeleteConsolidatedPayrollperiod($division, $data['PayrollActivePeriod']->year, $data['PayrollActivePeriod']->month, $data['bank']);
			$this->DeletePayrollArrearperiod($division, $data['PayrollActivePeriod']->year, $data['PayrollActivePeriod']->month, $data['bank']); /// not for council members
			$this->DeletePayrollOverdueArrearperiod($division, $data['PayrollActivePeriod']->year, $data['PayrollActivePeriod']->month, $data['bank']); // not for council members
			$this->DeletePayrollStaffCV($division, $data['PayrollActivePeriod']->year, $data['PayrollActivePeriod']->month, $data['bank']);
		}

		// compute
		if (isset($_POST['Compute']) || isset($_POST['Re-Compute'])) {
			//dd($request->all());
			if ($this->ConfirmConsolidatedPayrollperiod($court, $division, $data['PayrollActivePeriod']->year, $data['PayrollActivePeriod']->month, $data['bank'])) {
				$data['warning'] = "The computation is already done for this period";
				return view('salarycomputation.compute', $data);
			}
			//Lock June 2022 salary
			if ((trim($request['year'])) == 2022 && (trim($request['month']) == 'JUNE')) {
				$data['warning'] = "This month computation is already locked. It cannot be recomputed again!!!";
				return view('salarycomputation.compute', $data);
			}


			$period = $data['PayrollActivePeriod']->year . "-" . date("n", strtotime($data['PayrollActivePeriod']->month)) . "-1";
			$payrolldata = $this->PayrollStaffParameterCon($division, $data['bank']);
			$IsSOTPeriod = $this->IsSOTPeriod($data['PayrollActivePeriod']->year, $data['PayrollActivePeriod']->month, $court);
			foreach ($payrolldata as $b) {
				$LEAV = 0;
				$sot = 0;
				$tax_sot = 0;
				if ($IsSOTPeriod) {
					$SpecialOverTime = $this->SpecialOverTime($b->staffid, $court, $b->grade);
					$sot = $SpecialOverTime->gross;
					$tax_sot = $SpecialOverTime->tax;
				}
				$icount = $this->MonthCount($b->staffid, $data['PayrollActivePeriod']->month, $data['PayrollActivePeriod']->year);
				$ArrearComputation = $this->ArrearComputationCosolidatedNew($b->staffid, $data['PayrollActivePeriod']->year, $data['PayrollActivePeriod']->month);
				$OverdueArrearComputation = $this->OverdueArrearComputationCosolidatedNew($b->staffid, $data['PayrollActivePeriod']->year, $data['PayrollActivePeriod']->month);
				$othercomputation = $this->OtherEarn($b->staffid, $data['PayrollActivePeriod']->year, $data['PayrollActivePeriod']->month, 1);
				$AEarn = $ArrearComputation->basic + $OverdueArrearComputation->basic;
				$OEarn = $othercomputation->Earn;
				$AD = $ArrearComputation->Deduction + $OverdueArrearComputation->Deduction;
				$OD = $othercomputation->Deduction;
				$TEarn = (($b->amount + $b->housing + $b->transport + $b->furniture + $b->driver + $b->servant + $b->meal + $b->utility + $b->leave_bonus) * $icount) + $LEAV + $AEarn + $OEarn + $sot + ($b->peculiar * $icount) + ($b->peculiarFG * $icount) + $ArrearComputation->peculiar + $ArrearComputation->peculiarFG + $OverdueArrearComputation->peculiar + $OverdueArrearComputation->peculiarFG;
				$Pensionables = $this->Pensionable($b->staffid, $data['PayrollActivePeriod']->year, $data['PayrollActivePeriod']->month);
				$o_pension = $Pensionables * $penper;
				$o_nhf = $Pensionables * $nhfper;
				$o_nhis = $Pensionables * $nhisper;
				$o_nsitf = $Pensionables * $nsitfper;
				($b->is_retired == 1) ? $TD = ($b->tax) * $icount + $tax_sot
					: $TD = ($b->tax + $b->nhf + $b->unionDues + $b->pension) * $icount + $o_pension + $o_nhf + $AD + $OD + $tax_sot;
				$NetPay = $TEarn - $TD;
				DB::table('tblpayment_consolidated')->insert(array(
					'courtID'	    	=> $b->courtID,
					'divisionID'    	=> $b->divisionID,
					'current_state'    	=> $b->current_state,
					'staffid'    	=> $b->staffid,
					'fileNo'    	=> $b->fileNo,
					'employment_type'    	=> $b->employee_type,
					'name'    	=> $b->surname . ' ' . $b->first_name . ' ' . $b->othernames,
					'year'    => $data['PayrollActivePeriod']->year,
					'month'    	=> $data['PayrollActivePeriod']->month,
					'rank'    	=> $b->rank,
					'grade'    	=> $b->grade,
					'step'      => $b->step,
					'bank'      => $b->bankID,
					'bankGroup' => $b->bankGroup,
					'bank_branch' => $b->bank_branch,
					'AccNo'    	=> $b->AccNo,
					'SOT'    	=> round($sot, 2),
					'TAX_SOT'   => round($tax_sot, 2),
					'Bs'    	=> round($b->amount * $icount, 2),
					'HA'    	=> round($b->housing * $icount, 2),
					'TR'    	=> round($b->transport * $icount, 2),
					'FUR'       => round($b->furniture * $icount, 2),
					'PEC'       => round(($b->peculiar * $icount) + $ArrearComputation->peculiar + $OverdueArrearComputation->peculiar, 2),
					'PECFG'     => round(($b->peculiarFG * $icount) + $ArrearComputation->peculiarFG + $OverdueArrearComputation->peculiarFG, 2),
					'UTI'    	=> round($b->utility * $icount, 2),
					'DR'    	=> round($b->driver * $icount, 2),
					'SER'    	=> round($b->servant * $icount, 2),
					'ML'    	=> round($b->meal * $icount, 2),
					'LEAV'    	=> round($b->leave_bonus * $icount, 2),
					'AEarn'    	=> round($AEarn, 2),
					'OEarn'    	=> round($OEarn, 2),
					'TAX'    	=> round(($b->tax * $icount) + $ArrearComputation->tax + $OverdueArrearComputation->tax + $tax_sot, 2),
					'NHF'       => ($b->is_retired == 1) ? 0 : round(($b->nhf * $icount) + $ArrearComputation->nhf + $OverdueArrearComputation->nhf + $o_nhf, 2),
					//'PEN'     => round(($b->employee_type==5)? ($b->amount*$icount+($b->peculiar*$icount) + $ArrearComputation->peculiar+ $OverdueArrearComputation->peculiar+$this->TaxableEarning($b->staffid, $data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month)) * 0.08 : ($b->pension*$icount)+$ArrearComputation->pension+$OverdueArrearComputation->pension,2),
					'PEN'       => ($b->is_retired == 1) ? 0 : round(($b->pension * $icount) + $ArrearComputation->pension + $OverdueArrearComputation->pension + $o_pension, 2),
					'UD'        => ($b->is_retired == 1) ? 0 : round(($b->unionDues * $icount) + $ArrearComputation->unionDues + $OverdueArrearComputation->unionDues, 2),
					'AD'    	=> round($AD, 2),
					'OD'    	=> round($OD, 2),
					'TEarn'    	=> round($TEarn, 2),
					'TD'    	=> round($TD, 2),
					'NetPay'    => round($NetPay, 2),
					'gross'    	=> round($TEarn - $sot, 2),
					'payment_status'    	=> 1,
					'basic_real'    	=> round($b->basic, 2),
					'NHIS'    	=> round($b->NHIS * $icount + $o_nhis, 2), // $b->NHIS,
					'NSITF'      => round($b->NSITF * $icount + $o_nsitf, 2), //$b->NSITF,           

				));
			}
			DB::table('tblstaff_for_retirement')->where('divisionID', $division)
			->where('payment_status', 0)->update([	
                        'month_payment' 		=>  $data['PayrollActivePeriod']->month
                        ,'year_payment' 		=>  $data['PayrollActivePeriod']->year
                        ,'payment_status' 	=> 1
                        ]);
			$retirementStaff= DB::table('tblstaff_for_retirement')->where('divisionID', $division)->where('month_payment', $data['PayrollActivePeriod']->month)->where('year_payment', $data['PayrollActivePeriod']->year)->get();
    		foreach ($retirementStaff as $b) {	

    		    if(!DB::table('tblpayment_consolidated')->where('staffid',$b->staffid)->where('year',$data['PayrollActivePeriod']->year)->where('month',$data['PayrollActivePeriod']->month)->first())
			    $this->RetirementComputation($b->staffid, $data['PayrollActivePeriod']->year, $data['PayrollActivePeriod']->month,$b->due_date);
    		}
			$this->addLog("Salary Computation for $month $year");
			$data['success'] = "Salary computation is successfully done!";
		}


		if (isset($_POST['Re-Compute'])) {
			$data['success'] = "Recomputation complete!";
		}


		$payrollActivePeriod = DB::table('tblactivemonth')->first();
		$data['currentSalary'] = DB::table('tblpayment_consolidated')->where('checking_view', '=', 1)->where('year', '=', $payrollActivePeriod->year)->where('month', '=', $payrollActivePeriod->month)->count();


		return view('salarycomputation.compute', $data);
	}


	public function ComputeConsolidatedSalaryCouncil(Request $request)
	{
		$penper = DB::table('tbldeduction_percentage')->value('pension') * 0.01;
		$nhfper = DB::table('tbldeduction_percentage')->value('nhf') * 0.01;
		$nhisper = DB::table('tbldeduction_percentage')->value('nhis') * 0.01;
		$nsitfper = DB::table('tbldeduction_percentage')->value('nsitf') * 0.01;
$data['currentSalary']='';
		$data['error'] = "";
		$data['warning'] = "";
		$data['CourtInfo'] = $this->CourtInfo();
		if ($data['CourtInfo']->courtstatus == 0) {
			$request['court'] = $data['CourtInfo']->courtid;
		}
		if ($data['CourtInfo']->divisionstatus == 0) {
			$request['division'] = $data['CourtInfo']->divisionid;
		}
		$data['success'] = "";
		$data['showcourt'] = true;
		$court = trim($request['court']);
		$data['court'] = $court;
		$division = trim($request['division']);
		$data['division'] = $division;
		$year = trim($request['year']);
		$data['year'] = $year;
		$month = trim($request['month']);
		$data['month'] = $month;
		$data['bank'] = $request['bank'];
		$data['banklist'] = $this->BankList();
		$data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
		//$data['DivisionList'] = $this->DivisionList1($court);
		$data['DivisionList'] = $this->AllDivision();
		//get current logged in user division if not global user
		$data['curDivision'] = $this->curDivision(Auth::user()->id);
		$data['PayrollActivePeriod'] = $this->PayrollActivePeriod($court);
		if (isset($_POST['Re-Compute'])) {
			if ($this->ConfirmCheckAuditCon($division, $data['PayrollActivePeriod']->year, $data['PayrollActivePeriod']->month)) {
				$data['warning'] = "The computation is already passed Checking. It cannot be recompute again!!!";
				return view('salarycomputation.compute', $data);
			}
// 			if ($this->ConfirmCheckLockCon($division, $data['PayrollActivePeriod']->year, $data['PayrollActivePeriod']->month)) {
// 				$data['warning'] = "This month computation is already locked. It cannot be recomputed again!!!";
// 				return view('salarycomputation.compute', $data);
// 			}

			$this->DeleteConsolidatedPayrollperiodCouncil($court, $division, $data['PayrollActivePeriod']->year, $data['PayrollActivePeriod']->month, $data['bank']);
			$this->DeletePayrollCouncilCV($court, $division, $data['PayrollActivePeriod']->year, $data['PayrollActivePeriod']->month, $data['bank']);
		}
		if (isset($_POST['Compute']) || isset($_POST['Re-Compute'])) {
			if ($this->ConfirmConsolidatedPayrollperiodCouncil($court, $division, $data['PayrollActivePeriod']->year, $data['PayrollActivePeriod']->month, $data['bank'])) {
				$data['warning'] = "The computation is already done for this period";
				return view('salarycomputation.compute', $data);
			}

			$period = $data['PayrollActivePeriod']->year . "-" . date("n", strtotime($data['PayrollActivePeriod']->month)) . "-1";
			$payrolldata = $this->PayrollCouncilParameterCon($division, $data['bank']);
			$IsSOTPeriod = $this->IsSOTPeriod($data['PayrollActivePeriod']->year, $data['PayrollActivePeriod']->month, $court);
			foreach ($payrolldata as $b) {
				$LEAV = 0;
				$sot = 0;
				$tax_sot = 0;
				if ($IsSOTPeriod) {
					$SpecialOverTime = $this->SpecialOverTime($b->staffid, $court, $b->grade);
					$sot = $SpecialOverTime->gross;
					$tax_sot = $SpecialOverTime->tax;
				}
				$icount = $this->MonthCount($b->staffid, $data['PayrollActivePeriod']->month, $data['PayrollActivePeriod']->year);
				$ArrearComputation = $this->ArrearComputationCosolidatedNew($b->staffid, $data['PayrollActivePeriod']->year, $data['PayrollActivePeriod']->month);
				$OverdueArrearComputation = $this->OverdueArrearComputationCosolidatedNew($b->staffid, $data['PayrollActivePeriod']->year, $data['PayrollActivePeriod']->month);
				$othercomputation = $this->OtherEarn($b->staffid, $data['PayrollActivePeriod']->year, $data['PayrollActivePeriod']->month, 0);
				$AEarn = $ArrearComputation->basic + $OverdueArrearComputation->basic;
				$OEarn = $othercomputation->Earn;
				$AD = $ArrearComputation->Deduction + $OverdueArrearComputation->Deduction;
				$OD = $othercomputation->Deduction;
				$TEarn = (($b->amount + $b->housing + $b->transport + $b->furniture + $b->driver + $b->servant + $b->meal + $b->utility + $b->leave_bonus) * $icount) + $LEAV + $AEarn + $OEarn + $sot + ($b->peculiar * $icount) + $ArrearComputation->peculiar + $OverdueArrearComputation->peculiar;
				$Pensionables = $this->Pensionable($b->staffid, $data['PayrollActivePeriod']->year, $data['PayrollActivePeriod']->month);
				$o_pension = $Pensionables * $penper;
				$o_nhf = $Pensionables * $nhfper;
				$o_nhis = $Pensionables * $nhisper;
				$o_nsitf = $Pensionables * $nsitfper;
				($b->is_retired == 1) ? $TD = ($b->tax) * $icount + $tax_sot
					: $TD = ($b->tax + $b->nhf + $b->unionDues + $b->pension) * $icount + $o_pension + $o_nhf + $AD + $OD + $tax_sot;
				$NetPay = $TEarn - $TD;
				DB::table('tblpayment_consolidated')->insert(array(
					'courtID'	    	=> $b->courtID,
					'divisionID'    	=> $b->divisionID,
					'current_state'    	=> $b->current_state,
					'staffid'    	=> $b->staffid,
					'fileNo'    	=> $b->fileNo,
					'employment_type'    	=> $b->employee_type,
					'judge_rank'    	=> $b->judge_rank,
					'name'    	=> $b->surname . ' ' . $b->first_name . ' ' . $b->othernames,
					'year'    => $data['PayrollActivePeriod']->year,
					'month'    	=> $data['PayrollActivePeriod']->month,
					'rank'    	=> $b->rank,
					'grade'    	=> $b->grade,
					'step'      => $b->step,
					'bank'      => $b->bankID,
					'bankGroup' => $b->bankGroup,
					'bank_branch' => $b->bank_branch,
					'AccNo'    	=> $b->AccNo,
					'SOT'    	=> round($sot, 2),
					'TAX_SOT'   => round($tax_sot, 2),
					'Bs'    	=> round($b->amount * $icount, 2),
					'HA'    	=> round($b->housing * $icount, 2),
					'TR'    	=> round($b->transport * $icount, 2),
					'FUR'       => round($b->furniture * $icount, 2),
					'PEC'       => round(($b->peculiar * $icount) + $ArrearComputation->peculiar + $OverdueArrearComputation->peculiar, 2),
					'UTI'    	=> round($b->utility * $icount, 2),
					'DR'    	=> round($b->driver * $icount, 2),
					'SER'    	=> round($b->servant * $icount, 2),
					'ML'    	=> round($b->meal * $icount, 2),
					'LEAV'    	=> round($b->leave_bonus * $icount, 2),
					'AEarn'    	=> round($AEarn, 2),
					'OEarn'    	=> round($OEarn, 2),
					'TAX'    	=> round(($b->tax * $icount) + $ArrearComputation->tax + $OverdueArrearComputation->tax + $tax_sot, 2),
					'NHF'       => ($b->is_retired == 1) ? 0 : round(($b->nhf * $icount) + $ArrearComputation->nhf + $OverdueArrearComputation->nhf + $o_nhf, 2),
					//'PEN'     => round(($b->employee_type==5)? ($b->amount*$icount+($b->peculiar*$icount) + $ArrearComputation->peculiar+ $OverdueArrearComputation->peculiar+$this->TaxableEarning($b->staffid, $data['PayrollActivePeriod']->year,$data['PayrollActivePeriod']->month)) * 0.08 : ($b->pension*$icount)+$ArrearComputation->pension+$OverdueArrearComputation->pension,2),
					'PEN'       => ($b->is_retired == 1) ? 0 : round(($b->pension * $icount) + $ArrearComputation->pension + $OverdueArrearComputation->pension + $o_pension, 2),
					'UD'        => ($b->is_retired == 1) ? 0 : round(($b->unionDues * $icount) + $ArrearComputation->unionDues + $OverdueArrearComputation->unionDues, 2),
					'AD'    	=> round($AD, 2),
					'OD'    	=> round($OD, 2),
					'TEarn'    	=> round($TEarn, 2),
					'TD'    	=> round($TD, 2),
					'NetPay'    => round($NetPay, 2),
					'gross'    	=> round($TEarn - $sot, 2),
					'payment_status'    	=> 1,
					'basic_real'    	=> round($b->basic, 2),
					'NHIS'    	=> round($b->NHIS * $icount + $o_nhis, 2), // $b->NHIS,
					'NSITF'      => round($b->NSITF * $icount + $o_nsitf, 2), //$b->NSITF,           

				));
			}
			$this->addLog("Salary Computation for $month $year");
			$data['success'] = "Salary computation is successfully done!";
		}


		if (isset($_POST['Re-Compute'])) {
			$data['success'] = "Recomputation complete!";
		}


		$payrollActivePeriod = DB::table('tblactivemonth')->first();
		$data['currentSalary'] = DB::table('tblpayment_consolidated')->where('checking_view', '=', 1)->where('year', '=', $payrollActivePeriod->year)->where('month', '=', $payrollActivePeriod->month)->count();


		return view('salarycomputation.compute_council', $data);
	}


	public function SalaryStructure(Request $request)
	{
		$data['error'] = "";
		$data['warning'] = "";
		$data['success'] = "";
		$data['CourtInfo'] = $this->CourtInfo();

		if ($data['CourtInfo']->courtstatus == 0) {
			$request['court'] = $data['CourtInfo']->courtid;
		}

		$data['court'] = $request['court'];
		$data['grade'] = $request['grade'];
		$data['step'] = $request['step'];
		$data['employeetype'] = $request['employeetype'];
		$data['Rate'] = $this->RateCode();
		$data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
		$data['EmploymentTypeList'] = DB::table('tblemployment_type')->select('id', 'employmentType')->get();
		$data['PayStructure'] = $this->SalaryPayStructure($data['court'], $data['grade'], $data['step'], $data['employeetype']);
		return view('salarycomputation.structuresetup', $data);
	}

	public function LockPeriod(Request $request)
	{
		$payrollActivePeriod = DB::table('tblactivemonth')->first();
		$data['month'] = $request["month"];
		$data['year'] = $request["year"];
		$data['id'] = $request["id"];
		
		if (isset($_POST['process'])) {
			//  dd($data['year'] );
			$this->validate($request, [
				'year'      => 'required|string',
				'month'      => 'required|string',
				'id'      => 'required|string',
			  ]);
			  if (!DB::table('tblpayment_consolidated')->where('year',$request['year'])->where('divisionID',$request['id'])->where('rank', '!=', 2)->where('month', $request['month'])->update(['salary_lock' => 1,'vstage' => 1, ])){
			  return back()->with('error_message', 'The Selected period is not active!');}
			  return back()->with('message', 'Period locked Successfully');;
			
			// if (!DB::table('tblpayment_consolidated')->where('divisionID', '=', $request['id'])->where('year', $request['year'])->where('month', $request['month'])->update(['salary_lock' => 1,])) {
			// 	return back()->with('error_message', 'The Selected period is not active!');
			// }
			// return back()->with('message', 'Period locked Successfully');;
		}
		if (isset($_POST['unlock'])) {
		   
			if (DB::table('tblpayment_consolidated')->where('year',$request['year'])->where('divisionID',$request['id'])->where('rank', '!=', 2)->where('month',$request['month'])->where('vstage','>',1)->first()) {
				return back()->with('error_message', 'cannot be unlock');
			
			}else{
		    
			$this->validate($request, [
				'year'      => 'required|string',
				'month'      => 'required|string',
				'id'      => 'required|string',
			  ]);
			  if (!DB::table('tblpayment_consolidated')->where('year',$request['year'])->where('divisionID',$request['id'])->where('rank', '!=', 2)->where('month',$request['month'])->update(['salary_lock' => 0, 'vstage'=>0 ])){
			      
			  return back()->with('error_message', 'The Selected period is already active!');
			      
			  }
			  return back()->with('message', 'Period unlocked Successfully');
			
		}
		}
		
		//$data['CurrentPeriod'] = $this->CurrentPeriod();
		//$data['activemonth'] = DB::select("SELECT `year`,`month`, (CASE WHEN salary_lock=1 THEN 'Lock' ELSE 'Open' END) AS status FROM `tblpayment_consolidated` group by`year`,`month`  order by`ID`")
		if (auth()->user()->is_global ==1) {
			//dd($payrollActivePeriod->year);
		$data['activemonth']=DB::table('tblpayment_consolidated' )
		->join('tbldivision','tbldivision.divisionID','=', 'tblpayment_consolidated.divisionID')
		->select('tbldivision.division','tblpayment_consolidated.ID','tblpayment_consolidated.vstage','tblpayment_consolidated.month','tblpayment_consolidated.year','tblpayment_consolidated.year','tblpayment_consolidated.salary_lock', 'tblpayment_consolidated.month', 'tblpayment_consolidated.divisionID')
		->where('year', '=', $payrollActivePeriod->year)->where('month', '=', $payrollActivePeriod->month)
		->where('tblpayment_consolidated.rank', '!=', 2)
		->groupby('tbldivision.division')
		->orderby('tblpayment_consolidated.ID')
		->groupby('tbldivision.divisionID')
        ->get();
		}


else {
$data['activemonth']=DB::table('tblpayment_consolidated' )
		->join('tbldivision','tbldivision.divisionID','=', 'tblpayment_consolidated.divisionID')
		->select('tbldivision.division','tblpayment_consolidated.ID','tblpayment_consolidated.vstage','tblpayment_consolidated.month','tblpayment_consolidated.year','tblpayment_consolidated.year','tblpayment_consolidated.salary_lock', 'tblpayment_consolidated.month', 'tblpayment_consolidated.divisionID')
		->where('year', '=', $payrollActivePeriod->year)
		->where('month', '=', $payrollActivePeriod->month)
		->where('tblpayment_consolidated.rank', '!=', 2)
		->where('tbldivision.divisionID', '=', auth()->user()->divisionID)
        ->groupby('tbldivision.division')
		->orderby('tblpayment_consolidated.ID')
		->groupby('tbldivision.divisionID')
		->get();
}

		return view('activeMonth.active_monthlock', $data)->with('payrollActivePeriod', $payrollActivePeriod);
	}
	public function UnLockPeriod(Request $request)
	{
		$data['error'] = "";
		$data['warning'] = "";
		$data['success'] = "";
		$data['month'] = $request["month"];
		$data['year'] = $request["year"];
		$data['CourtInfo'] = $this->CourtInfo();
		// dd($data);

		if ($data['CourtInfo']->courtstatus == 0) {
			$request['court'] = $data['CourtInfo']->courtid;
		}
		if ($data['CourtInfo']->divisionstatus == 0) {
			$request['division'] = $data['CourtInfo']->divisionid;
		}

        $data['courtDivisions']  = DB::table('tbldivision')
        //  ->where('courtID', '=', $courtSessionId)
        ->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        
		// dd($data);

		$data['court'] = trim($request['court']);
		$data['division'] = trim($request['division']);
		$data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
		$data['DivisionList'] = $this->DivisionList1($data['court']);
		$data['PayrollActivePeriod'] = $this->PayrollActivePeriod($data['court']);

		// dd($data);

		if (isset($_POST['unlock'])) {
			$this->validate($request, [
				'year'      => 'required|string',
				'month'      => 'required|string',
			]);
			if (!DB::table('tblpayment_consolidated')->where('year', $request['year'])->where('month', $request['month'])->update(['salary_lock' => 0,])) {
				return back()->with('error_message', 'The Selected period is active!');
			}
			return back()->with('message', 'Period unlocked Successfully');;
		}

		// dd($data);

		$payrollActivePeriod = DB::table('tblactivemonth')->first();
		// dd($payrollActivePeriod->month); // 1, 2022, dec

		$data['currentSalary'] = DB::table('tblpayment_consolidated')
			->where('checking_view', '=', 1)->where('year', '=', $payrollActivePeriod->year)
			->where('month', '=', $payrollActivePeriod->month)->count();


		$data['activemonth'] = DB::select("SELECT `year`,`month`, (CASE WHEN salary_lock=1 THEN 'Lock' ELSE 'Open' END) AS status FROM `tblpayment_consolidated` group by`year`,`month`  order by`ID`");

		// dd($data);


		return view('activeMonth.periodunlock', $data);
	}
	public function ChartRevalidation(Request $request)
	{
		$data['error'] = "";
		$data['warning'] = "";
		$data['success'] = "";

		if (isset($_POST['Compute'])) {
			$rawdata = DB::SELECT("SELECT * FROM `basicsalaryconsolidatedtesting` where  `employee_type`=1 or `employee_type`=5 or `employee_type`=3  ");
			$percentages = DB::SELECT("SELECT * FROM `tbldeduction_percentage`")[0];
			foreach ($rawdata as $value) {
				// $pen= round(($value->amount+ $value->peculiar)*0.08,2);
				// $nhf= round(($value->amount+ $value->peculiar)*0.025,2);
				// $nhis= round(($value->amount+ $value->peculiar)*0.05,2);
				// $nsitf= round(($value->amount+ $value->peculiar)*0.01,2);
				$pen = round(($value->amount + $value->peculiar) * $percentages->pension * 0.01, 2);
				$nhf = round(($value->amount) * $percentages->nhf * 0.01, 2);
				$nhis = round(($value->amount) * $percentages->nhis * 0.01, 2);
				$nsitf = round(($value->amount) * $percentages->nsitf * 0.01, 2);

				$union_due = ((int)$value->grade < 14) ? round(($value->peculiar) * $percentages->union_due * 0.01, 2) : 0;

				DB::table('basicsalaryconsolidatedtesting')->where('ID', $value->ID)
					->update(['pension' => $pen, 'nhf' => $nhf, 'NHIS' => $nhis, 'NSITF' => $nsitf, 'unionDues' => $union_due,]);
			}
		}
		return view('salarycomputation.chartrevalidation', $data);
	}
	public function SeparateSpecialOvertime(Request $request)
	{
		$data['error'] = "";
		$data['warning'] = "";
		$data['success'] = "";

		$data['month'] = $request["month"];
		$data['year'] = $request["year"];
		if (isset($_POST['compute'])) {
			$this->validate($request, [
				'year'      => 'required|string',
				'month'      => 'required|string',
			]);
			DB::table('tblseparate_special_overtime')->where('year', $data['year'])->where('month', $data['month'])->delete();
			$stafflist = DB::table('tblper')
				->join('tblquarterly_allowance', 'tblquarterly_allowance.grade', '=', 'tblper.grade')
				->where('staff_status', 1)
				->where('employee_type', '<>', 2)
				->select('tblper.*', 'tblquarterly_allowance.gross', 'tblquarterly_allowance.tax')->get();

			foreach ($stafflist as $b) {

				$gross = $b->gross;
				$tax = $b->tax;
				if (DB::table('tblspecial_overtime_overide')->where('staffid', $b->ID)->first()) {
					// dd($b->ID);
					$gross = DB::table('tblspecial_overtime_overide')->where('staffid', $b->ID)->value('gross');
					$tax = DB::table('tblspecial_overtime_overide')->where('staffid', $b->ID)->value('tax');
				}
				if ($gross > 0)
					DB::table('tblseparate_special_overtime')->insert(array(
						'staffid'	    => $b->ID,
						'fileno'    	=> $b->fileNo,
						'names'    	    => $b->surname . ' ' . $b->first_name . ' ' . $b->othernames,
						'grade'    	    => $b->grade,
						'year'    	    => $data['year'],
						'month'    	    => $data['month'],
						'gross'    	    => $gross,
						'tax'    	    => $tax,
					));
			}
			//dd($stafflist);

		}
		return view('salarycomputation.compute_special_overtime', $data);
	}
	
	public function RetirementComputation($staffid, $year, $month,$retirememt_date)
	{
		

		

                $activemonth = date("n", strtotime($month));
                $current_period= $year."-".$activemonth."-1";
            	// $dif =31- $this->dateDiff($current_period, $retirememt_date)['days'];
            // 	dd("$dif, $current_period, $retirememt_date");
				$xyz= $this->dateDiff($current_period, $retirememt_date);
                $dif= -$xyz['days_of_month']*$xyz['months'] - $xyz['days'] +1;
                
	            $icount=$dif/$xyz['days_of_month'];
		
                $b=$this->PayrolldataSingleStaff($staffid)[0];
		
				$ArrearComputation = $this->ArrearComputationCosolidatedNew($staffid, $year, $month);
				$OverdueArrearComputation = $this->OverdueArrearComputationCosolidatedNew($staffid, $year, $month);
				$othercomputation = $this->OtherEarn($staffid, $year, $month, 1);
				$AEarn = $ArrearComputation->basic + $OverdueArrearComputation->basic;
				$OEarn = $othercomputation->Earn;
				$AD = $ArrearComputation->Deduction + $OverdueArrearComputation->Deduction;
				$OD = $othercomputation->Deduction;
				$TEarn = round(($b->amount ) * $icount,2) + $AEarn + $OEarn +  round(($b->peculiar * $icount),2) + round(($b->peculiarFG * $icount),2) + $ArrearComputation->peculiar + $ArrearComputation->peculiarFG + $OverdueArrearComputation->peculiar + $OverdueArrearComputation->peculiarFG;
			
			
				
				($b->is_retired == 1) ? $TD = ($b->tax) * $icount 
					: $TD = (round($b->tax* $icount,2) + round($b->nhf* $icount,2) + round($b->unionDues* $icount,2) + round($b->pension* $icount,2))   + $AD + $OD;
				$NetPay = $TEarn - $TD;
				DB::table('tblpayment_consolidated')->insert(array(
					'courtID'	    	=> $b->courtID,
					'divisionID'    	=> $b->divisionID,
					'current_state'    	=> $b->current_state,
					'staffid'    	=> $staffid,
					'fileNo'    	=> $b->fileNo,
					'employment_type'    	=> $b->employee_type,
					'name'    	=> $b->surname . ' ' . $b->first_name . ' ' . $b->othernames,
					'year'    => $year,
					'month'    	=> $month,
					'rank'    	=> $b->rank,
					'grade'    	=> $b->grade,
					'step'      => $b->step,
					'bank'      => $b->bankID,
					'bankGroup' => $b->bankGroup,
					'bank_branch' => $b->bank_branch,
					'AccNo'    	=> $b->AccNo,
					'SOT'    	=> 0,
					'TAX_SOT'   => 0,
					'Bs'    	=> round($b->amount * $icount, 2),
					'HA'    	=> round($b->housing * $icount, 2),
					'TR'    	=> round($b->transport * $icount, 2),
					'FUR'       => round($b->furniture * $icount, 2),
					'PEC'       => round(($b->peculiar * $icount) + $ArrearComputation->peculiar + $OverdueArrearComputation->peculiar, 2),
					'PECFG'       => round(($b->peculiarFG * $icount) + $ArrearComputation->peculiarFG + $OverdueArrearComputation->peculiarFG, 2),
					'UTI'    	=> round($b->utility * $icount, 2),
					'DR'    	=> round($b->driver * $icount, 2),
					'SER'    	=> round($b->servant * $icount, 2),
					'ML'    	=> round($b->meal * $icount, 2),
					'LEAV'    	=> round($b->leave_bonus * $icount, 2),
					'AEarn'    	=> round($AEarn, 2),
					'OEarn'    	=> round($OEarn, 2),
					'TAX'    	=> round(($b->tax * $icount) + $ArrearComputation->tax + $OverdueArrearComputation->tax , 2),
					'NHF'       => ($b->is_retired == 1) ? 0 : round(($b->nhf * $icount) + $ArrearComputation->nhf + $OverdueArrearComputation->nhf , 2),
					'PEN'       => ($b->is_retired == 1) ? 0 : round(($b->pension * $icount) + $ArrearComputation->pension + $OverdueArrearComputation->pension , 2),
					'UD'        => ($b->is_retired == 1) ? 0 : round(($b->unionDues * $icount) + $ArrearComputation->unionDues + $OverdueArrearComputation->unionDues, 2),
					'AD'    	=> round($AD, 2),
					'OD'    	=> round($OD, 2),
					'TEarn'    	=> round($TEarn, 2),
					'TD'    	=> round($TD, 2),
					'NetPay'    => round($NetPay, 2),
					'gross'    	=> round($TEarn , 2),
					'payment_status'    	=> 1,
					'basic_real'    	=> round($b->basic, 2),
					'NHIS'    	=> round($b->NHIS * $icount , 2), // $b->NHIS,
					'NSITF'      => round($b->NSITF * $icount , 2), //$b->NSITF,           

				));
			
		
		

	
	}

	//Checking for disparity in payments by any given month against the another month

	public function checkPaymentDifferenceByMonths()
	{
		return view('payrollReport.get-difference-in-months');
	}

public function checkNewPersonnel(Request $request)
{
    $request->validate([
        'current_year' => 'required|integer',
        'current_month' => 'required|string|in:JANUARY,FEBRUARY,MARCH,APRIL,MAY,JUNE,JULY,AUGUST,SEPTEMBER,OCTOBER,NOVEMBER,DECEMBER',
        'comparison_year' => 'required|integer',
        'comparison_month' => 'required|string|in:JANUARY,FEBRUARY,MARCH,APRIL,MAY,JUNE,JULY,AUGUST,SEPTEMBER,OCTOBER,NOVEMBER,DECEMBER',
    ]);

    $currentYear = $request->input('current_year');
    $currentMonth = $request->input('current_month');
    $comparisonYear = $request->input('comparison_year');
    $comparisonMonth = $request->input('comparison_month');

    $currentMonthPaidIds = DB::table('tblpayment_consolidated')
        ->where('year', $currentYear)
        ->where('month', strtoupper($currentMonth))
        ->pluck('staffid');

    $comparisonMonthPaidIds = DB::table('tblpayment_consolidated')
        ->where('year', $comparisonYear)
        ->where('month', strtoupper($comparisonMonth))
        ->pluck('staffid');

    $currentMonthPaidIdsArray = array_map('intval', $currentMonthPaidIds->toArray());
    $comparisonMonthPaidIdsArray = array_map('intval', $comparisonMonthPaidIds->toArray());
    // Find IDs that are in the comparison month but not in the current month
    $diffIds = array_diff($comparisonMonthPaidIdsArray, $currentMonthPaidIdsArray);


    if (empty($diffIds)) {
        return response()->json([
            'message' => 'No new personnel found for the selected months.',
            'newPersonnel' => [],
        ]);
    }

    $newEmployees = DB::table('tblper')
        ->join('tbldivision', 'tblper.divisionID', '=', 'tbldivision.divisionID')
        ->join('tblpayment_consolidated', 'tblper.ID', '=', 'tblpayment_consolidated.staffID')
        ->whereIn('tblper.ID', $diffIds)
        ->select(
            'tblper.ID', 'tblper.title', 'tblper.surname', 'tblper.first_name', 'tblper.othernames', 
            'tblper.fileNo', 'tbldivision.division', 'tblpayment_consolidated.accNo', 
            'tblpayment_consolidated.Bs', 'tblpayment_consolidated.Tax', 'tblpayment_consolidated.NetPay', 
            'tblpayment_consolidated.gross'
        )
        ->get();


    return response()->json([
        'message' => 'Personnel comparison successful.',
        'newPersonnel' => $newEmployees,
    ]);
}


public function checkRemovedPersonnel(Request $request)
{
    $request->validate([
        'current_year' => 'required|integer',
        'current_month' => 'required|string|in:JANUARY,FEBRUARY,MARCH,APRIL,MAY,JUNE,JULY,AUGUST,SEPTEMBER,OCTOBER,NOVEMBER,DECEMBER',
        'comparison_year' => 'required|integer',
        'comparison_month' => 'required|string|in:JANUARY,FEBRUARY,MARCH,APRIL,MAY,JUNE,JULY,AUGUST,SEPTEMBER,OCTOBER,NOVEMBER,DECEMBER',
    ]);

    $currentYear = $request->input('current_year');
    $currentMonth = $request->input('current_month');
    $comparisonYear = $request->input('comparison_year');
    $comparisonMonth = $request->input('comparison_month');

    $currentMonthPaidIds = DB::table('tblpayment_consolidated')
        ->where('year', $currentYear)
        ->where('month', strtoupper($currentMonth))
        ->pluck('staffid');

    $comparisonMonthPaidIds = DB::table('tblpayment_consolidated')
        ->where('year', $comparisonYear)
        ->where('month', strtoupper($comparisonMonth))
        ->pluck('staffid');

    $currentMonthPaidIdsArray = array_map('intval', $currentMonthPaidIds->toArray());
    $comparisonMonthPaidIdsArray = array_map('intval', $comparisonMonthPaidIds->toArray());

    \Log::info('Personnel Data in Current month: ' . $currentMonth, $currentMonthPaidIdsArray);
    \Log::info('Personnel Data in compared month: ' . $comparisonMonth, $comparisonMonthPaidIdsArray);

    // Find IDs that are in the current month but not in the comparison month (removed personnel)
    $diffIds = array_diff($currentMonthPaidIdsArray, $comparisonMonthPaidIdsArray);

    \Log::info('Difference (diffIds):', $diffIds);

    if (empty($diffIds)) {
        \Log::info('No personnel removed for the selected months.');
        return response()->json([
            'message' => 'No personnel removed for the selected months.',
            'removedPersonnel' => [],
        ]);
    }

    $removedEmployees = DB::table('tblper')
        ->join('tbldivision', 'tblper.divisionID', '=', 'tbldivision.divisionID')
        ->join('tblpayment_consolidated', 'tblper.ID', '=', 'tblpayment_consolidated.staffID')
        ->whereIn('tblper.ID', $diffIds)
        ->select(
            'tblper.ID', 'tblper.title', 'tblper.surname', 'tblper.first_name', 'tblper.othernames', 
            'tblper.fileNo', 'tbldivision.division', 'tblpayment_consolidated.accNo', 
            'tblpayment_consolidated.Bs', 'tblpayment_consolidated.Tax', 'tblpayment_consolidated.NetPay', 
            'tblpayment_consolidated.gross'
        )
        ->get();

    \Log::info('Removed Personnel Data:', $removedEmployees->toArray());

    return response()->json([
        'message' => 'Personnel comparison successful.',
        'removedPersonnel' => $removedEmployees,
    ]);
}







// Helper method to convert month name to month number
	protected function getMonthNumber($monthName)
	{
		$months = [
			'JANUARY' => 1,
			'FEBRUARY' => 2,
			'MARCH' => 3,
			'APRIL' => 4,
			'MAY' => 5,
			'JUNE' => 6,
			'JULY' => 7,
			'AUGUST' => 8,
			'SEPTEMBER' => 9,
			'OCTOBER' => 10,
			'NOVEMBER' => 11,
			'DECEMBER' => 12
		];

		return $months[$monthName] ?? null; // Convert month name to uppercase and get number
	}


}