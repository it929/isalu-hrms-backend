<?php

namespace App\Http\Controllers\hr;

use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\Http\Requests;
//use \DateTime;
use App\Services\Formatter;
use Auth;
use Session;
class ComputeProcessorController extends ParentController
{
	private $month;
	private $year;
	private $divisionID;
	private $division;
	private $details;
	
	public function __construct()
	{
        $this->middleware(function ($request, $next) {
            $this->month = date("n", strtotime(Session::get('activeMonth')));
		$this->year = Session::get('activeYear');
		$this->divisionID = Session::get('divisionID');
		$this->division = Session::get('division');
		$this->details = Auth::user()->name.", ".$this->division;
            return $next($request);
        });
    }

	public function computeAll(Request $request)
	{
		$this->validate($request, [
			'month'      => 'required|alpha',
			'year'       => 'required|numeric',
			'btn'        => 'required|alpha_dash',
			'court'      => 'required|numeric',
			]);
		$msg = "";
        
		$month = $request->input('month');
		$year = $request->input('year');
		$court = $request['court'];
		$divisionID = $request['division'];


                if($divisionID == '')
		{
			$value = $court;
			$field = 'courtID';
		}
		else
		{
			$field = 'divisionID';
			$value = $divisionID;
		}
       
       
		if($request->input('btn') == 'Re-Compute')
		{
			if($divisionID == '')
			{
				DB::delete('delete from tblpayment where month = ? and year = ? and courtID = ? ', [$month, $year, $court]);
			    DB::delete('delete from tblarrears where month = ? and year = ? and courtID = ?',  [$month, $year, $court]);
			}
			else
			{
				DB::delete('delete from tblpayment where month = ? and year = ? and division = ? ', [$month, $year, $divisionID]);
			    DB::delete('delete from tblarrears where month = ? and year = ? and divisionID = ?', [$month, $year, $divisionID]);
			}
			
			//DB::delete('delete from tblnhf where month = ? and year = ? and division =? ', [$month, $year, $this->division]);
            
       //Begin Recomputation
		if($divisionID == '')
		{
		$query = DB::select('select a.fileNo, a.divisionID, title, surname, AccNo , first_name, othernames, a.grade, a.step, rank, a.bankID, bank, bankgroup, bank_branch, status_value, amount, section, tax, peculiar, leave_bonus, pension, nhf, unionDues, appointment_date, ugv, nicnCoop, motorAdv, bicycleAdv, ctlsLab, ctlsFed, fedHousing, hazard, callDuty, phoneCharges, c.pa_deduct, surcharge, a.employee_type, shiftAll, MONTH(appointment_date) AS MTH, YEAR(appointment_date) AS YR, current_state, e.NumofPA, nhfNo from tblper a inner join basicsalary b on 
			(a.grade = b.grade and a.step = b.step) and (a.employee_type = 
			b.employee_type) inner JOIN tblcv c on (a.fileNo = c.fileNo) INNER JOIN tblbanklist d on 
			(a.bankID = d.bankID) left join (SELECT fileNo, count(Name) as NumofPA from tblpersonalassistant group
			by fileNo) e on (a.fileNo = e.fileNo) where a.courtID = ? and a.employee_type <> \'CONSOLIDATED\' 
			and a.staff_status = 1', [$court]);
        }
        else
        {
        	$query = DB::select('select a.fileNo, a.divisionID, title, surname, AccNo , first_name, othernames, a.grade, a.step, rank, a.bankID, bank, bankgroup, bank_branch, status_value, amount, section, tax, peculiar, leave_bonus, pension, nhf, unionDues, appointment_date, ugv, nicnCoop, motorAdv, bicycleAdv, ctlsLab, ctlsFed, fedHousing, hazard, callDuty, phoneCharges, c.pa_deduct, surcharge, a.employee_type, shiftAll, MONTH(appointment_date) AS MTH, YEAR(appointment_date) AS YR, current_state, e.NumofPA, nhfNo from tblper a inner join basicsalary b on 
			(a.grade = b.grade and a.step = b.step) and (a.employee_type = 
			b.employee_type) inner JOIN tblcv c on (a.fileNo = c.fileNo) INNER JOIN tblbanklist d on 
			(a.bankID = d.bankID) left join (SELECT fileNo, count(Name) as NumofPA from tblpersonalassistant group
			by fileNo) e on (a.fileNo = e.fileNo) where divisionID = ? and a.employee_type <> \'CONSOLIDATED\' 
			and a.staff_status = 1', [$divisionID]);
        }
		 		
		$no_staff = 0; // the number of staff computed
		foreach ($query as $row) 
		{
			$leave_b = 0.00;
			if ($row->status_value == "Contract Service")
			{
				$unionDues = 0;
				$pension = 0;
				$nhf = 0;
			}
			else
			{
				$pension = $row->pension;
				$nhf = $row->nhf;
				$unionDues = $row->unionDues;
			}

			$divisionID = $row->divisionID;

			$pa_deduct = $row->pa_deduct * $row->NumofPA;			
			$motorbasic = $row->peculiar + $row->leave_bonus + $row->callDuty + $row->hazard+ $row->shiftAll;
			$grossemolument = $row->amount;
			$totaldeduction = $pa_deduct + $row->tax + $pension + $nhf + $unionDues + $row->ugv + $row->nicnCoop; 
			$totaldeduction += $row->ctlsLab + $row->ctlsFed + $row->motorAdv + $row->bicycleAdv + $row->phoneCharges;
			$totaldeduction += $row->fedHousing + $row->surcharge;
			$netpay = $grossemolument - $totaldeduction;
			$grosspay = $totaldeduction + $motorbasic + $netpay;
			$totalemolument = $netpay + $motorbasic;
			$name = $row->surname." ".$row->first_name." ".$row->othernames;

			$purpose = "General salary Re-Computation";
			try 
			{
				DB::beginTransaction();
				$msg = DB::table('tblpayment')->insert(array('fileNo'=> $row->fileNo, 'name'=> $name, 
					'date'=> date('Y-m-d'), 'month'=> $month, 'year'=> $year, 'basic_salary'=> $row->amount,
					'actingAllow'=> '','arrearsBasic' => '','tax'=> $row->tax, 'pension'=> $pension,'nhf'=> $nhf, 
					'unionDues'=> $unionDues, 'ugv' => $row->ugv, 'nicncoop' => $row->nicnCoop, 
					'ctlsLab' => $row->ctlsLab, 'fedHousing' => $row->fedHousing, 'cumEmolu' => $grossemolument, 
					'motorbasicAll' => $motorbasic, 'totalDeduct' => $totaldeduction, 'netpay' => $netpay, 
					'grosspay' => $grosspay, 'totalEmolu' => $totalemolument, 'peculiar' => $row->peculiar, 
					'leave_bonus' => $row->leave_bonus, 'hazard' => $row->hazard, 'callDuty' => $row->callDuty, 
					'shiftAll' => $row->shiftAll, 'phoneCharges' => $row->phoneCharges, 'pa_deduct' => $pa_deduct, 
					'surcharge' => $row->surcharge, 'grade' => $row->grade, 'step' => $row->step, 
					'purpose' => $purpose, 'bank' => $row->bank, 'bankGroup'=> $row->bankgroup, 
					'bank_branch' => $row->bank_branch, 'AccNo'=> $row->AccNo, 'nhfNo'=> $row->nhfNo, 
					'division' => $divisionID, 'courtID'=>$court, 'current_state' => $row->current_state, 
					'salary_status' => 'newly computed', 'audited_by' => ''));

                                         //inserting to nhf table
				/*$nhfinsert = DB::table('tblnhf')->insert(array('fileNo'=> $row->fileNo, 'name'=> $name,'date'=> date('Y-m-d'), 'month'=> $month, 'year'=> $year,'nhf'=>$nhf,'division' => $this->division,'current_state'=>$row->current_state,'grade' => $row->grade, 'step' => $row->step,'audited_by' => ''
					));*/

				$this->addLog('General computation for '.$month.' '.$year.', '.$this->division);
				DB::commit();

			}catch(\Illuminate\Database\QueryException $ex)
			{
				//dd($ex->getMessage());
				return back()->with('err', 'Salary already computed.');
			}
			$no_staff++;
			//dd($msg);
		}//end of foreach

		/* Arrears Computation*/

		

		$allstaff = DB::table('tblstaff_for_arrears')->where('month_payment','=', $month)->where('year_payment','=', $year)->orWhere('payment_status','=', 0)->where($field,'=',$value)->get();
		    
		 foreach ($allstaff as $d)
		 { 
		 	if($d->arrears_type == 'increment')
		 	{
			$this->increment(date("n", strtotime($month)),$year,$month,$divisionID,$court);
		    }
		   if($d->arrears_type == 'advancement')
		 	{
			$this->advancement(date("n", strtotime($month)),$year,$month,$divisionID,$court);
		    }
		    if($d->arrears_type == 'newAppointment')
		 	{
			$this->newAppointment(date("n", strtotime($month)),$year,$month,$divisionID,$court);
		    }
		 }


		/* End Arrears Computation*/
	
		if($msg == True)
			$msg = 'General Re-Computation completed; '.$no_staff.' staff salar(ies) computed!';
		return back()->with('msg', $msg);

			//dd(DB::getQueryLog());
			//$this->addLog('General re-computation for '.$month.' '.$year.', '.$this->division.' starting');
			//$msg = 'Salary successfully recomputed';				
		}
         
         elseif ($request->input('btn') == 'Compute') {
         
		//DB::enableQueryLog();

                 $checkStaff = DB::table('tblper')
				->where($field, '=', $value)
				->count();
				if($checkStaff == 0)
				{
			      return back()->with('err', 'No Staff Available for Salary Computation');
		        }    	



		$num = DB::table('tblpayment')
				->where('tblpayment.month', $month)
				->where('tblpayment.year', $year)
				->where('tblpayment.division', $divisionID)
				->orWhere('tblpayment.courtID', $court)
				->count();

		//dd($num);
		if($num > 0)
		{
			return back()->with('err', 'Salary already computed.');
		}

		if($divisionID == '')
		{
		$query = DB::select('select a.fileNo, a.divisionID, title, surname, AccNo , first_name, othernames, a.grade, a.step, rank, a.bankID, bank, bankgroup, bank_branch, status_value, amount, section, tax, peculiar, leave_bonus, pension, nhf, unionDues, appointment_date, ugv, nicnCoop, motorAdv, bicycleAdv, ctlsLab, ctlsFed, fedHousing, hazard, callDuty, phoneCharges, c.pa_deduct, surcharge, a.employee_type, shiftAll, MONTH(appointment_date) AS MTH, YEAR(appointment_date) AS YR, current_state, e.NumofPA, nhfNo from tblper a inner join basicsalary b on 
			(a.grade = b.grade and a.step = b.step) and (a.employee_type = 
			b.employee_type) inner JOIN tblcv c on (a.fileNo = c.fileNo) INNER JOIN tblbanklist d on 
			(a.bankID = d.bankID) left join (SELECT fileNo, count(Name) as NumofPA from tblpersonalassistant group
			by fileNo) e on (a.fileNo = e.fileNo) where a.courtID = ? and a.employee_type <> \'CONSOLIDATED\' 
			and a.staff_status = 1', [$court]);
        }
        else
        {
        	$query = DB::select('select a.fileNo, a.divisionID, title, surname, AccNo , first_name, othernames, a.grade, a.step, rank, a.bankID, bank, bankgroup, bank_branch, status_value, amount, section, tax, peculiar, leave_bonus, pension, nhf, unionDues, appointment_date, ugv, nicnCoop, motorAdv, bicycleAdv, ctlsLab, ctlsFed, fedHousing, hazard, callDuty, phoneCharges, c.pa_deduct, surcharge, a.employee_type, shiftAll, MONTH(appointment_date) AS MTH, YEAR(appointment_date) AS YR, current_state, e.NumofPA, nhfNo from tblper a inner join basicsalary b on 
			(a.grade = b.grade and a.step = b.step) and (a.employee_type = 
			b.employee_type) inner JOIN tblcv c on (a.fileNo = c.fileNo) INNER JOIN tblbanklist d on 
			(a.bankID = d.bankID) left join (SELECT fileNo, count(Name) as NumofPA from tblpersonalassistant group
			by fileNo) e on (a.fileNo = e.fileNo) where a.divisionID = ? and a.employee_type <> \'CONSOLIDATED\' 
			and a.staff_status = 1', [$divisionID]);
			//dd($query);
        }
		 		
		$no_staff = 0; // the number of staff computed
		foreach ($query as $row) 
		{
			$leave_b = 0.00;
			if ($row->status_value == "Contract Service")
			{
				$unionDues = 0;
				$pension = 0;
				$nhf = 0;
			}
			else
			{
				$pension = $row->pension;
				$nhf = $row->nhf;
				$unionDues = $row->unionDues;
			}

			$divisionID = $row->divisionID;

			$pa_deduct = $row->pa_deduct * $row->NumofPA;			
			$motorbasic = $row->peculiar + $row->leave_bonus + $row->callDuty + $row->hazard+ $row->shiftAll;
			$grossemolument = $row->amount;
			$totaldeduction = $pa_deduct + $row->tax + $pension + $nhf + $unionDues + $row->ugv + $row->nicnCoop; 
			$totaldeduction += $row->ctlsLab + $row->ctlsFed + $row->motorAdv + $row->bicycleAdv + $row->phoneCharges;
			$totaldeduction += $row->fedHousing + $row->surcharge;
			$netpay = $grossemolument - $totaldeduction;
			$grosspay = $totaldeduction + $motorbasic + $netpay;
			$totalemolument = $netpay + $motorbasic;
			$name = $row->surname." ".$row->first_name." ".$row->othernames;

			$purpose = "General salary computation";
			try 
			{
				DB::beginTransaction();
				$msg = DB::table('tblpayment')->insert(array('fileNo'=> $row->fileNo, 'name'=> $name, 
					'date'=> date('Y-m-d'), 'month'=> $month, 'year'=> $year, 'basic_salary'=> $row->amount,
					'actingAllow'=> '','arrearsBasic' => '','tax'=> $row->tax, 'pension'=> $pension,'nhf'=> $nhf, 
					'unionDues'=> $unionDues, 'ugv' => $row->ugv, 'nicncoop' => $row->nicnCoop, 
					'ctlsLab' => $row->ctlsLab, 'fedHousing' => $row->fedHousing, 'cumEmolu' => $grossemolument, 
					'motorbasicAll' => $motorbasic, 'totalDeduct' => $totaldeduction, 'netpay' => $netpay, 
					'grosspay' => $grosspay, 'totalEmolu' => $totalemolument, 'peculiar' => $row->peculiar, 
					'leave_bonus' => $row->leave_bonus, 'hazard' => $row->hazard, 'callDuty' => $row->callDuty, 
					'shiftAll' => $row->shiftAll, 'phoneCharges' => $row->phoneCharges, 'pa_deduct' => $pa_deduct, 
					'surcharge' => $row->surcharge, 'grade' => $row->grade, 'step' => $row->step, 
					'purpose' => $purpose, 'bank' => $row->bank, 'bankGroup'=> $row->bankgroup, 
					'bank_branch' => $row->bank_branch, 'AccNo'=> $row->AccNo, 'nhfNo'=> $row->nhfNo, 
					'division' => $divisionID, 'courtID'=>$court, 'current_state' => $row->current_state, 
					'salary_status' => 'newly computed', 'audited_by' => ''));

                                         //inserting to nhf table
				/*$nhfinsert = DB::table('tblnhf')->insert(array('fileNo'=> $row->fileNo, 'name'=> $name,'date'=> date('Y-m-d'), 'month'=> $month, 'year'=> $year,'nhf'=>$nhf,'division' => $this->division,'current_state'=>$row->current_state,'grade' => $row->grade, 'step' => $row->step,'audited_by' => ''
					));*/

				$this->addLog('General computation for '.$month.' '.$year.', '.$this->division);
				DB::commit();

			}catch(\Illuminate\Database\QueryException $ex)
			{
				//dd($ex->getMessage());
				return back()->with('err', 'Salary already computed.');
			}
			$no_staff++;
			//dd($msg);
		}//end of foreach
	
        
        //Start Arrears Computation

        if($divisionID == '')
		{
			$value = $court;
			$field = 'courtID';
		}
		else
		{
			$field = 'divisionID';
			$value = $divisionID;
		}


		
        $allstaff = DB::table('tblstaff_for_arrears')->where('payment_status','=', 0)->where($field,'=',$value)->get();
		    
		 foreach ($allstaff as $d)
		 { 
		 	if($d->arrears_type == 'increment')
		 	{
			$this->increment(date("n", strtotime($month)),$year,$month,$divisionID,$court);
		    }
		   if($d->arrears_type == 'advancement')
		 	{
			$this->advancement(date("n", strtotime($month)),$year,$month,$divisionID,$court);
		    }
		    if($d->arrears_type == 'newAppointment')
		 	{
			$this->newAppointment(date("n", strtotime($month)),$year,$month,$divisionID,$court);
		    }
		 }
		 
		 //End arrears Computation

		if($msg == True)

		 
			$msg = 'General computation completed; '.$no_staff.' staff salar(ies) computed!';
		return back()->with('msg', $msg);
		}//end else if for General computation	


	}//end of computeAll

	public function oneStaff(Request $request)
	{
		$this->validate($request, [
			'staffList'  => 'required|numeric',
			]);
		$msg = "";
		$month  = $request->input('month');
		$year   = $request->input('year');
		$fileNo = $request->staffList;	

		//DB::enableQueryLog();

		 //$locked = DB::table('tblpayment')->where('account_locked','=','yes')->where('division','=',$this->division)->where('month','=',$month)->where('year','=',$year)->count();
		 $checked = DB::table('tblpayment')->where('fileNo','=',$fileNo)->where('division','=',$this->division)->where('month','=',$month)->where('year','=',$year)->count();

	
		 $ifStaffLock = DB::table('tblpayment')->where('fileNo','=',$fileNo)->where('division','=',$this->division)->where('month','=',$month)->where('year','=',$year)->first();
         $count = DB::table('tblpayment')->where('accountLock','=','yes')->where('fileNo','=',$fileNo)->where('division','=',$this->division)->where('month','=',$month)->where('year','=',$year)->count();

         
   
		// if($checked == 1)
		// {      
          
          	//if($ifStaffLock->accountLock == 'no')
          	//{
		  $row = DB::select('select a.fileNo, title, surname, AccNo , first_name, othernames, a.grade, a.step, rank, a.bankID, bank, bankgroup, bank_branch, status_value, amount, section, tax, peculiar, leave_bonus, pension, nhf, unionDues, appointment_date, ugv, nicnCoop, motorAdv, bicycleAdv, ctlsLab, ctlsFed, fedHousing, hazard, callDuty, phoneCharges, c.pa_deduct, a.employee_type, surcharge, shiftAll, MONTH(appointment_date) AS MTH, YEAR(appointment_date) AS YR, current_state, e.NumofPA,
			nhfNo from tblper a inner join basicsalary b on (a.grade = b.grade and a.step = b.step) and (a.employee_type = 
			b.employee_type) inner JOIN tblcv c on (a.fileNo = c.fileNo) INNER JOIN tblbanklist d on (a.bankID = d.bankID) left join 
			(SELECT fileNo, count(Name) as NumofPA from tblpersonalassistant group by fileNo) e on (a.fileNo = e.fileNo) where
			divisionID = ? and a.fileNo = ? and a.employee_type <> \'CONSOLIDATED\' and a.staff_status = 1', [$this->divisionID, 
			$request->staffList]);
			
		$no_staff = 0; // the number of staff computed

		$leave_b = 0.00;
		if ($row[0]->status_value == "Contract Service")
		{
			$unionDues = 0;
			$pension = 0;
			$nhf = 0;
		}
		else
		{
			$pension = $row[0]->pension;
			$nhf = $row[0]->nhf;
			$unionDues = $row[0]->unionDues;
		}
		$pa_deduct = $row[0]->pa_deduct * $row[0]->NumofPA;			
		$motorbasic = $row[0]->peculiar + $row[0]->leave_bonus + $row[0]->callDuty + $row[0]->hazard+ $row[0]->shiftAll;
		//$motorbasic = $row[0]->peculiar + $row[0]->leave_bonus + $row[0]->callDuty + $row[0]->hazard+ $row[0]->shiftAll;
		$grossemolument = $row[0]->amount;
		$totaldeduction = $pa_deduct + $row[0]->tax + $pension + $nhf + $unionDues + $row[0]->ugv + $row[0]->nicnCoop; 
		$totaldeduction += $row[0]->ctlsLab + $row[0]->ctlsFed + $row[0]->motorAdv + $row[0]->bicycleAdv + $row[0]->phoneCharges;
		$totaldeduction += $row[0]->fedHousing + $row[0]->surcharge;
		$netpay = $grossemolument - $totaldeduction;
		$grosspay = $totaldeduction + $motorbasic + $netpay;
		$totalemolument = $netpay + $motorbasic;
		$name = $row[0]->surname." ".$row[0]->first_name." ".$row[0]->othernames;
		$purpose = "General salary computation";
		try 
		{				
			DB::beginTransaction();
			DB::delete('delete from tblpayment where fileNo = ? and month = ? and year = ?', [$row[0]->fileNo, $month, $year]);

                        //delete from nhf table
			DB::delete('delete from tblnhf where fileNo = ? and month = ? and year = ?', [$row[0]->fileNo, $month, $year]);

			$msg = DB::table('tblpayment')->insert(array('fileNo'=> $row[0]->fileNo, 'name'=> $name, 'date'=> date('Y-m-d'), 'month'=> $month, 
				'year'=> $year, 'basic_salary'=> $row[0]->amount,'actingAllow'=> '','arrearsBasic' => '','tax'=> $row[0]->tax,
				'pension'=> $pension,'nhf'=> $nhf, 'unionDues'=> $unionDues, 'ugv' => $row[0]->ugv, 'nicnCoop' => $row[0]->nicnCoop,
				'ctlsLab' => $row[0]->ctlsLab, 'fedHousing' => $row[0]->fedHousing, 'cumEmolu' => $grossemolument, 
				'motorbasicAll' => $motorbasic, 'totalDeduct' => $totaldeduction, 'netpay' => $netpay, 'grosspay' => $grosspay,
				'totalEmolu' => $totalemolument, 'peculiar' => $row[0]->peculiar, 'leave_bonus' => $row[0]->leave_bonus, 
				'hazard' => $row[0]->hazard, 'callDuty' => $row[0]->callDuty, 'shiftAll' => $row[0]->shiftAll, 
				'phoneCharges' => $row[0]->phoneCharges, 'pa_deduct' => $pa_deduct, 'surcharge' => $row[0]->surcharge, 
				'grade' => $row[0]->grade, 'step' => $row[0]->step, 'purpose' => $purpose, 'bank' => $row[0]->bank, 
				'bankGroup'=> $row[0]->bankgroup, 'bank_branch' => $row[0]->bank_branch, 'AccNo'=> $row[0]->AccNo, 
				'nhfNo'=> $row[0]->nhfNo, 'division' => $this->division, 'current_state' => $row[0]->current_state, 
				'salary_status' => 'newly computed',  'audited_by' => ''));

                                //inserting to nhf table
				$nhfinsert = DB::table('tblnhf')->insert(array('fileNo'=> $row[0]->fileNo, 'name'=> $name,'date'=> date('Y-m-d'), 'month'=> $month, 'year'=> $year,'nhf'=>$nhf,'division' => $this->division,'current_state'=>$row[0]->current_state,'grade' => $row[0]->grade, 'step' => $row[0]->step,'audited_by' => ''
					));

			$this->addLog("Re-computing for staff with fileno = ".$request->staffList.", $month, $year.");				
			DB::commit();
			DB::delete('delete from tblarrears where fileNo = ? and month = ? and year = ?', [$row[0]->fileNo, $month, $year]);

		}catch(\Illuminate\Database\QueryException $ex)
		{
			//dd($ex->getMessage());
			return back()->with('warning', 'An error occurred.');
		}
		if($msg == True)
			$msg = 'One staff payroll re-computed successfully';
		return back()->with('msg', $msg);

	
	

   //}// end general locked

	}//end of oneStaff

	private function dateDiff($date2, $date1)
	{
		list($year2, $mth2, $day2) = explode("-", $date2);
		list($year1, $mth1, $day1) = explode("-", $date1);
		if ($year1 > $year2) dd('Invalid Input - dates do not match');
		//$days_month = 0;
		$days_month = cal_days_in_month(CAL_GREGORIAN, $mth1, $year1);
		$day_diff = 0;

		if($year2 == $year1){
			$mth_diff = $mth2 - $mth1;
		}
		else{
			$yr_diff = $year2 - $year1;
			$mth_diff = (12 * $yr_diff) - $mth1 + $mth2;
		}
		if($day1 > 1){
			$mth_diff--;
			//dd($mth1.",".$year1);
			$day_diff = $days_month - $day1 + 1;
		}

		$result = array('months'=>$mth_diff, 'days'=> $day_diff, 'days_of_month'=>$days_month);
		return($result);
	} //end of dateDiff

	public function arrearsNew(Request $request)
	{
		//dd($this->month);
		$this->validate($request, [
			'staffList'		=> 'required|numeric',
			'arrearsType'	=> 'required|alpha',
			'grade'			=> 'required|numeric',
			'step'			=> 'required|numeric',
			'newGrade'  	=> 'required_unless:arrearsType,newAppointment|numeric',
			'newStep'   	=> 'required_unless:arrearsType,newAppointment|numeric',
			'dueDate'   	=> 'required_unless:arrearsType,newAppointment|date',
			]);
		$rs = "";
		if($request->arrearsType === 'increment')
		{
			$rs = $this->increment(date("n", strtotime('JUNE')),2018);
		}
		else if($request->arrearsType === 'advancement')
		{
			$rs = $this->advancement($request);
		}
		else if($request->arrearsType === 'newAppointment')
		{
			$rs = $this->newAppointment($request);
		}
		else 
		{
			return back();
		}
		if($rs != "")
			return back()->with('msg', $rs['msg'])->with('details', $rs['result_details']);

	}//end of arrears
	/*$staffDue = DB::table('tblstaff_for_arrears')->where('payment_status','=', 0)->where('arrears_type','=', 'increment')->get();
	$rs = '';
		foreach ($staffDue as $due) {
			if($due->arrears_type == 'increment')
			{
				$rs = $this->increment();
			}
			}*/

	private function increment($activemonth, $year,$activeMonthString,$divisionID,$court)
	{
		
		$leave_b = 0;
		$arr_sum = 0;

		//dd(date("n", strtotime('JUNE')));

        if($divisionID == '')
		{
			$value = $court;
			$field = 'courtID';
		}
		else
		{
			$field = 'divisionID';
			$value = $divisionID;
		}

		$staffDue = DB::table('tblstaff_for_arrears')->where('payment_status','=', 0)->where('arrears_type','=', 'increment')->where($field,'=',$value)->get();
		foreach ($staffDue as $due) {
			
		
		$staff = DB::select('select b.*, c.*, d.NumofPA,a.divisionID,b.courtID from tblper a inner join ( select * from basicsalary where (grade, step) IN ( (?, ?), (?, ?) ) ) b on (a.employee_type = b.employee_type) inner join (SELECT * from tblcv) c on (a.fileNo = c.fileNo) left join (select fileNo, count(*) as NumofPA from tblpersonalassistant group by fileNo) d on (a.fileNo = d.fileNo) where a.fileNo = ?', [$due->old_grade, $due->old_step, $due->new_grade, $due->new_step, $due->fileNo]);

		$dueDate = $due->due_date;
		
		
		//dd(DB::getQueryLog());
		//dd($staff);
		/*if(count($staff) != 2 )
			return array('msg'=>'An error occurred. Invalid selection', 'result_details'=>'');*/
		
		//dd($staff[0]->amount);
		$sum = $staff[1]->amount - $staff[0]->amount;
		//$curDate = ;
		//dd(Session::get('activeMonth'));
		//$dueDate = $myarray->dueDate;
		list($i_year, $i_mth, $i_day) = explode('-', $dueDate);
		$diff = $this->dateDiff($year."-".$activemonth."-1", $i_year.'-'.$i_mth.'-1');
		$month_diff = $diff['months'];
		//dd($month_diff);
		$com_msg = "Duration of arrears: ".$month_diff."month(s)<br>";
		$basicsalary = $staff[1]->amount;
		$com_msg.= "Basic Salary: ".$basicsalary."<br>";
		$basic_arr = $month_diff * $sum; // the basic arrears
		//dd($basic_arr);
		$com_msg.= "Arrear Basic: ".$basic_arr."<br>";

		$pec_diff = ($staff[1]->peculiar - $staff[0]->peculiar) * $month_diff;
		$com_msg.= "Peculiar arrears: ".$pec_diff."<br>";
		$arr_sum = $arr_sum + $pec_diff; 
		$peculiar =  $pec_diff + $staff[1]->peculiar;
		$com_msg.= "Peculiar allowance: ".$staff[1]->peculiar."<br>";
		$com_msg.= "Total Peculiar: ".$peculiar."<br>";

		$leave_diff = ($staff[1]->leave_bonus - $staff[0]->leave_bonus) * $month_diff;
		$com_msg.= "Leave Bonus arrears: ".$leave_diff."<br>";
		$arr_sum = $arr_sum + $leave_diff; 
		$leave_bonus =  $leave_diff + $staff[1]->leave_bonus;
		$com_msg.= "Leave Bonus allowance: ".$staff[1]->leave_bonus."<br>";
		$com_msg.= "Total leave bonus: ".$leave_bonus."<br>";
		
		$com_msg.= "<b>Deductions </b><br>";
		$pen_diff =  $staff[1]->pension - $staff[0]->pension;	
		$com_msg.= "Pension difference: ".$pen_diff."<br>";
		$com_msg.= "Pension arrears: ".($pen_diff * $month_diff)."<br>";
		$pension = ($pen_diff * $month_diff) + $staff[1]->pension;
		//$pension = $this ->format($pension);	//echo "$mul, $pension";
		$com_msg.= "Total Pension: ".$pension."<br>";
		
		$tax_diff =  $staff[1]->tax - $staff[0]->tax;	
		$com_msg.= "Tax difference: ".$tax_diff."<br>";
		$com_msg.= "Tax arrears: ".($tax_diff * $month_diff)."<br>";
		$tax = ($tax_diff * $month_diff) + $staff[1]->tax;
		//$tax = $this ->format($tax);	//echo "$mul, $pension";
		$com_msg.= "Total Tax: ".$tax."<br>";
		//dd(DB::getQueryLog());

		$com_msg.= "Use of govt vehicle: ".$staff[0]->ugv."<br>";
		$com_msg.= "NICN Coop: ".$staff[0]->nicnCoop."<br>";
		$unionDues = $staff[1]->unionDues;
		$com_msg.= "Union Dues: ".$staff[1]->unionDues."<br>";
		$nhf = $staff[1]->nhf;
		$com_msg.= "NHF: ".$nhf."<br>";
		$com_msg.= "Federal Housing Loan: ".$staff[1]->fedHousing."<br>";
		$com_msg.= "Leave bonus: ".$staff[1]->leave_bonus."<br>";
		//$motorbasic = $housing + $transport + $utility + $meal + $furniture + $servant + $driver + $peculiar ;
		$motorbasic = $leave_bonus + $staff[1]->callDuty + $staff[1]->hazard + $staff[1]->shiftAll + $peculiar;
		$pa_deduct = $staff[1]->pa_deduct * $staff[1]->NumofPA;

		$com_msg.= "Motor Basic Allowance: ".$motorbasic."<br>";
		$grossemolument = $staff[1]->amount + $basic_arr;
		$com_msg.= "Gross Emolument: ".$grossemolument."<br>";
		$totaldeduction = $tax + $pension + $staff[1]->nhf + $staff[1]->unionDues + $staff[1]->ugv + $staff[1]->nicnCoop + 
		$staff[1]->fedHousing + $staff[1]->phoneCharges + $pa_deduct;
		$totaldeduction += $staff[1]->ctlsLab + $staff[1]->ctlsFed + $staff[1]->motorAdv + $staff[1]->bicycleAdv + 
		$staff[1]->surcharge;
		$com_msg.= "Total Deduction: ".$totaldeduction."<br>";
		$netpay = $grossemolument - $totaldeduction;
		$com_msg.= "Total Net pay: ".$netpay."<br>";
		$grosspay = $totaldeduction + $motorbasic + $netpay; // they dont have it on the payroll
		$com_msg.= "Gross Pay: ".$grosspay."<br>";
		$totalemolument = $netpay + $motorbasic;
		$com_msg.= "Total Emolument: ".$totalemolument."<br>";
		$details = "Increment computation by ".$this->details;
		$month = Session::get('activeMonth');
		//$this->oneStaff($myarray); //clear previous salary first	

		try 
		{
			DB::beginTransaction();	
			$query = DB::table('tblpayment')->where(['fileNo'=>$due->fileNo, 'month'=>$activeMonthString, 'year'=>$year])
			->update(['basic_salary'=> $basicsalary, 'arrearsBasic' => $basic_arr, 'tax'=>$tax, 'pension'=>$pension, 
				'nhf'=>$nhf, 'unionDues'=>$unionDues, 'ugv' => $staff[0]->ugv, 'nicnCoop'=>$staff[0]->nicnCoop, 
				'ctlsLab'=>$staff[0]->ctlsLab, 'ctlsFed' => $staff[0]->ctlsFed, 'fedHousing' => $staff[0]->fedHousing, 
				'motorAdv'=> $staff[0]->motorAdv, 'bicycleAdv'=> $staff[0]->bicycleAdv, 'cumEmolu'=>$grossemolument,
				'motorBasicAll'=>$motorbasic, 'totalDeduct'=>$totaldeduction, 'netpay'=>$netpay, 'grosspay'=>$grosspay, 
				'totalEmolu'=>$totalemolument, 'peculiar' =>$peculiar, 'hazard'=>$staff[0]->hazard, 
				'callDuty'=>$staff[0]->callDuty, 'shiftAll'=>$staff[0]->shiftAll, 
				'phoneCharges'=>$staff[0]->phoneCharges, 'pa_deduct'=>$pa_deduct, 'surcharge'=>$staff[0]->surcharge,
				'arrears'=>$arr_sum,'division'=>$staff[0]->divisionID,'courtID'=>$staff[0]->courtID, 'grade'=>$staff[1]->grade, 'step'=>$staff[1]->step, 'date'=> date('Y-m-d'), 
				'purpose'=>DB::raw("concat(purpose, '$details')") ]);

		$this->addLog('Increment computation for '.$due->fileNo.' in ' .$activemonth.' '.$year.', '.$this->division);
		DB::insert('replace into tblarrears set fileNo = ?, month = ?, year = ?, oldGrade = ?, oldStep = ?, 
			newGrade = ?, newStep = ?, oldBasic = ?, newBasic = ?, oldTax = ?, newTax = ?, oldPeculiar = ?, 
			newPeculiar = ?, oldLeave_bonus = ?, newLeave_bonus = ?, oldPension = ?, newPension = ?, oldNhf = ?, 
			newNhf= ?, oldUnionDues = ?, newUnionDues = ?, type = ?, dueDate = ?,divisionID = ?,courtID = ?, date = ?', [$due->fileNo, 
			$activemonth, $year, $due->old_grade, $due->old_step, $due->new_grade, $due->new_step, 
			$staff[0]->amount, $staff[1]->amount, $staff[0]->tax, $staff[1]->tax, $staff[0]->peculiar, 
			$staff[1]->peculiar, $staff[0]->leave_bonus, $staff[1]->leave_bonus, $staff[0]->pension, $staff[1]->pension,
			$staff[0]->nhf, $staff[1]->nhf, $staff[0]->unionDues, $staff[1]->unionDues, 'increment', $due->due_date, $due->divisionID,$due->courtID, 
			date('Y-m-d')]);
		DB::update('update tblper set grade = ?, step = ? where fileNo = ?', [$staff[1]->grade, $staff[1]->step, 
			$due->fileNo]);
		DB::update('update tblstaff_for_arrears set payment_status = 1, month_payment = ?, year_payment = ? where fileNo = ?', [$activeMonthString,$year,$due->fileNo]);
		DB::commit();				
		} catch (Exception $e) 
		{		
			//dd($e);
		}			
		
		//dd(DB::getQueryLog());
		if($query == 1)
			return array('msg'=>'Increment completed successfully', 'result_details'=>$com_msg);
		else
			return array('msg'=>'An error occurred', 'result_details'=>$com_msg);
	}
	} //end of increment

	private function advancement($activemonth,$year,$activeMonthString,$divisionID,$court)
	{
		$f = new Formatter;	

		 if($divisionID == '')
		{
			$value = $court;
			$field = 'courtID';
		}
		else
		{
			$field = 'divisionID';
			$value = $divisionID;
		}
 //$staff = 0;
		$staffDue = DB::table('tblstaff_for_arrears')->where('payment_status','=', 0)->where('arrears_type','=', 'advancement')->where($field,'=',$value)->get();
		foreach ($staffDue as $due) {
			//dd($due->fileNo);
		$staff = DB::select('select b.*, c.*, d.NumofPA,a.divisionID,b.courtID from tblper a inner join ( select * from basicsalary where (grade, step) IN ( (?, ?), (?, ?) ) ) b on (a.employee_type = b.employee_type) inner join (SELECT * from tblcv) c on (a.fileNo = c.fileNo) left join (select fileNo, count(*) as NumofPA from tblpersonalassistant group by fileNo) d on (a.fileNo = d.fileNo) where a.fileNo = ?', [$due->old_grade, $due->old_step, $due->new_grade, $due->new_step, $due->fileNo]);
         
			/*$old = DB::table('tblper')
			->join('basicsalary','basicsalary.grade','=','tblper.grade')
		    ->join('basicsalary','basicsalary.step','=','tblper.step')
		    ->join('tblcv','tblcv.fileNo','=','tblper.fileNo')
		    ->leftJoin('tblpersonalassistant','tblpersonalassistant.fileNo','=','tblper.fileNo')
		    ->where('basicsalary.grade','=',$due->grade)
		    ->where('basicsalary.step','=',$due->step)
		    ->where('tblper.fileNo','=',$due->fileNo)
		    ->get();*/
		$arr_sum = 0;
		$dueDate = $due->due_date;
		$curDate = $year."-".$activemonth."-1";
		$diff = $this->dateDiff($curDate, $dueDate);
		$month_diff = $diff['months'];
		$days_worked = $diff['days'];
		$days_month = $diff['days_of_month'];
		//$leave_b = 0.00;
		//$com_msg = "<div class='col-md-4'>";
		$com_msg= "Duration of arrears: ".$month_diff."month(s) ".$days_worked."day(s)<br>";
		$sum = $staff[1]->amount - $staff[0]->amount;
		$com_msg.= "basic salary difference: ".$staff[1]->amount." - ".$staff[0]->amount." = ".$sum."<br>";

		$f = new Formatter;
		//amount of pay per month
		$mul = $f->format(($sum/$days_month) * $days_worked);
		$com_msg.= "For $days_worked day(s): (".$sum."/".$days_month.") *"." $days_worked = ".$mul."<br>";
		//for the number of days worked
		$basicsalary = $staff[1]->amount;	
		$basic_arr = $month_diff * $sum; //the basic arrears
		$com_msg.= "For $month_diff month(s): ".$month_diff." * ".$sum." = ".$basic_arr."<br>";
		$basic_arr = $f->format($basic_arr + $mul);
		$com_msg.= "Basic arrears: ".$basic_arr	."<br>";	
		$com_msg.="<br>";	

		//  peculiar allowance and arrears
		$peculiar = (($staff[1]->peculiar - $staff[0]->peculiar) * $month_diff ) ;
		$mul = (($staff[1]->peculiar - $staff[0]->peculiar)/$days_month) * $days_worked ;
		$peculiar = $f->format($peculiar + $mul);
		$com_msg.= "Peculiar arrears: ".$peculiar."<br>";
		$arr_sum = $arr_sum + $peculiar;
		$a_peculiar = $staff[1]->peculiar;
		$com_msg.= "Peculiar allowance: ".$staff[1]->peculiar."<br>";
		$peculiar =  $f->format($peculiar + $staff[1]->peculiar);
		$com_msg.= "Total Peculiar: ".$peculiar."<br>";
		$com_msg.="<br>";
		// leave bonus allowance and arrears
		$leave_bonus = (($staff[1]->leave_bonus - $staff[0]->leave_bonus) * $month_diff ) ;
		$mul = (($staff[1]->leave_bonus - $staff[0]->leave_bonus)/$days_month) * $days_worked ;
		$leave_bonus = $f->format($leave_bonus + $mul);
		$com_msg.= "Leave Bonus arrears: ".$leave_bonus."<br>";
		$arr_sum = $arr_sum + $leave_bonus;
		$a_leave_bonus = $staff[1]->leave_bonus;
		$com_msg.= "Leave Bonus allowance: ".$staff[1]->leave_bonus."<br>";
		$leave_bonus =  $f->format($leave_bonus + $staff[1]->leave_bonus);
		$com_msg.= "Total Leave Bonus: ".$leave_bonus."<br>";
		$com_msg.="<br>";

		$com_msg .="<b>Deductions</b><br>";
		$com_msg.="<br>";							//pension and ppension arrear
		$pen_diff =  $staff[1]->pension - $staff[0]->pension;	
		$pension = ((($pen_diff  * $days_worked)/$days_month) + ($pen_diff * $month_diff));
		$pension = $f->format($pension);
													//Tax  and tax arrears
		$tax_diff =  $staff[1]->tax - $staff[0]->tax ;	
		$tax = ((($tax_diff  * $days_worked)/$days_month) + ($tax_diff * $month_diff));
		$tax = $f->format($tax);

		$com_msg.= "Pension arrears: ".$pension."<br>";
		$com_msg.= "Pension deduction: ".$staff[1]->pension."<br>";
		$pension = $f->format($pension + $staff[1]->pension);
		$com_msg .="Total pension: ".$pension."<br>";
		$com_msg .="Tax arrears: ".$tax."<br>";
		$com_msg.= "Tax deduction: ".$staff[1]->tax."<br>";
		$tax = $f->format($tax + $staff[1]->tax);
		$com_msg .="Total tax: ".$tax."<br>";
		$com_msg .="Use of govt vehicle: ".$staff[1]->ugv."<br>";
		$com_msg .="NICN Coop: ".$staff[1]->nicnCoop."<br>";
		$nhf = $staff[1]->nhf;	 //nhf deduction		
		$com_msg .="Nhf: $nhf<br>";
		$unionDues = $staff[1]->unionDues; //union due deduction
		
		$com_msg .="Union Dues : $unionDues<br>";
		$fedHousing = $staff[1]->fedHousing; //federal housing deduction
		$com_msg .="Federal Housing Loan: $fedHousing<br>";
		$pa_deduct = $staff[1]->NumofPA * $staff[1]->pa_deduct;
		$motorbasic =  $leave_bonus + $staff[1]->callDuty + $staff[1]->hazard + $staff[1]->shiftAll + $peculiar;

		$com_msg .="Motor basic allowance: $motorbasic<br>";
		$grossemolument = $f->format($staff[1]->amount + $basic_arr);
		$com_msg .="Gross emolument: $grossemolument<br>";
		$totaldeduction = $f->format($tax + $pension + $nhf + $unionDues + $staff[1]->ugv + $staff[1]->nicnCoop + $fedHousing);
		$totaldeduction += $staff[1]->ctlsLab + $staff[1]->ctlsFed + $staff[1]->motorAdv + $staff[1]->bicycleAdv + 
		$staff[1]->phoneCharges + $staff[1]->pa_deduct + $staff[1]->surcharge;
		$totaldeduction = $f->format($totaldeduction);
		$com_msg .="Total deduction: $totaldeduction<br>";
		$netpay = $f->format( $grossemolument - $totaldeduction);
		$com_msg .="Net pay: $netpay<br>";
		$grosspay = $f->format($totaldeduction + $motorbasic + $netpay);
		$com_msg .="Gross Pay: $grosspay<br>";

		//$totalemolument = $f->format($netpay + $motorbasic);
                 
                //my own test calculations for total emolument
                $newbasic = $staff[1]->amount + $basic_arr;
		$totalnewbasic = $peculiar + $newbasic + $leave_bonus;
		$totalemolument = $totalnewbasic - $totaldeduction;
 		// end my own test calculations for total emolument


		$com_msg .="Total emolument: $totalemolument<br>";
		$details = "advancement computation by ".$this->details;
		$month = Session::get('activeMonth');
		//$this->oneStaff($myarray); //clear previous salary first		

		try 
		{
			DB::beginTransaction();	
			$query = DB::table('tblpayment')->where(['fileNo'=>$due->fileNo, 'month'=>$activeMonthString, 'year'=>$year])
			->update(['basic_salary'=> $basicsalary, 'arrearsBasic' => $basic_arr, 'tax'=>$tax, 'pension'=>$pension, 
				'nhf'=>$nhf, 'unionDues'=>$unionDues, 'ugv' => $staff[0]->ugv, 'nicnCoop'=>$staff[0]->nicnCoop, 
				'ctlsLab'=>$staff[0]->ctlsLab, 'ctlsFed' => $staff[0]->ctlsFed, 'fedHousing' => $staff[0]->fedHousing, 
				'motorAdv'=> $staff[0]->motorAdv, 'bicycleAdv'=> $staff[0]->bicycleAdv, 'cumEmolu'=>$grossemolument,
				'motorBasicAll'=>$motorbasic, 'totalDeduct'=>$totaldeduction, 'netpay'=>$netpay, 'grosspay'=>$grosspay, 
				'totalEmolu'=>$totalemolument, 'peculiar' =>$peculiar, 'hazard'=>$staff[0]->hazard, 
				'callDuty'=>$staff[0]->callDuty, 'shiftAll'=>$staff[0]->shiftAll, 
				'phoneCharges'=>$staff[0]->phoneCharges, 'pa_deduct'=>$pa_deduct, 'surcharge'=>$staff[0]->surcharge,
				'arrears'=>$arr_sum,'division'=>$staff[0]->divisionID,'courtID'=>$staff[0]->courtID, 'grade'=>$staff[1]->grade, 'step'=>$staff[1]->step, 'date'=> date('Y-m-d'), 
				'purpose'=>DB::raw("concat(purpose, '$details')") ]);

		$this->addLog('Increment computation for '.$due->fileNo.' in ' .$activemonth.' '.$year.', '.$this->division);
		DB::insert('replace into tblarrears set fileNo = ?, month = ?, year = ?, oldGrade = ?, oldStep = ?, 
			newGrade = ?, newStep = ?, oldBasic = ?, newBasic = ?, oldTax = ?, newTax = ?, oldPeculiar = ?, 
			newPeculiar = ?, oldLeave_bonus = ?, newLeave_bonus = ?, oldPension = ?, newPension = ?, oldNhf = ?, 
			newNhf= ?, oldUnionDues = ?, newUnionDues = ?, type = ?, dueDate = ?, divisionID = ?,courtID = ?, date = ?', [$due->fileNo, 
			$activemonth, $year, $due->old_grade, $due->old_step, $due->new_grade, $due->new_step, 
			$staff[0]->amount, $staff[1]->amount, $staff[0]->tax, $staff[1]->tax, $staff[0]->peculiar, 
			$staff[1]->peculiar, $staff[0]->leave_bonus, $staff[1]->leave_bonus, $staff[0]->pension, $staff[1]->pension,
			$staff[0]->nhf, $staff[1]->nhf, $staff[0]->unionDues, $staff[1]->unionDues, 'increment', $due->due_date, $due->divisionID,$due->courtID, 
			date('Y-m-d')]);
		DB::update('update tblper set grade = ?, step = ? where fileNo = ?', [$staff[1]->grade, $staff[1]->step, 
			$due->fileNo]);
		DB::update('update tblstaff_for_arrears set payment_status = 1,month_payment = ?, year_payment = ? where fileNo = ?', [$activeMonthString, $year, $due->fileNo]);
		DB::commit();				
		} catch (Exception $e) 
		{		
			//dd($e);
		}	
		if($query == 1)
			return array('msg'=>'Computation completed successfully', 'result_details'=>$com_msg);
		else
			return array('msg'=>'An error occurred', 'result_details'=>$com_msg);
	 }//end foreach due

	}//end of advancement

	private function newAppointment($activemonth,$year,$activeMonthString,$divisionID,$court)
	{
		$f = new Formatter;	

		 if($divisionID == '')
		{
			$value = $court;
			$field = 'courtID';
		}
		else
		{
			$field = 'divisionID';
			$value = $divisionID;
		}

		$staffDue = DB::table('tblstaff_for_arrears')->where('payment_status','=', 0)->where('arrears_type','=', 'newAppointment')->where($field,'=',$value)->get();
		foreach ($staffDue as $due) {
		$staff = DB::select('select a.fileNo, a.divisionID, a.courtID, title, surname, AccNo , first_name, othernames, a.grade, a.step, rank, a.bankID, bank, bankgroup, bank_branch, status_value, amount, section, tax, peculiar, leave_bonus, pension, nhf, unionDues, appointment_date, ugv, nicnCoop, motorAdv, bicycleAdv, ctlsLab, ctlsFed, fedHousing, hazard, callDuty, phoneCharges, c.pa_deduct, surcharge, shiftAll, MONTH(appointment_date) AS MTH, YEAR(appointment_date) AS YR, current_state, e.NumofPA, nhfNo from tblper a inner join basicsalary b on
			(a.grade = b.grade and a.step = b.step) and (a.employee_type = b.employee_type) inner JOIN tblcv c on 
			(a.fileNo = c.fileNo) INNER JOIN tblbanklist d on (a.bankID = d.bankID) left join (SELECT fileNo, 
			count(Name) as NumofPA from tblpersonalassistant group by fileNo) e on (a.fileNo = e.fileNo) where a.fileNo = ? and a.staff_status = 1', [$due->fileNo]);
		///dd($staff[0]->appointment_date);

		list($YEAR, $MTH, $DAY) = explode('-', $staff[0]->appointment_date);
		$curDate = $year."-".$activemonth."-1";
		$diff = $this->dateDiff($curDate, $staff[0]->appointment_date);
		//dd($diff);
		$arr_sum = 0;
		$basicsalary = $staff[0]->amount;
		//dd($diff['months']);
		$basicArrears = $f->format($f->format(($basicsalary/$diff['days_of_month']) * $diff['days']) 
			+ ($diff['months'] * $basicsalary));

             $leaveAllArrears = $f->format($f->format(($staff[0]->leave_bonus/$diff['days_of_month']) * $diff['days']) 
			+ ($diff['months'] * $staff[0]->leave_bonus));


		$peculiarArrears = $f->format($f->format(($staff[0]->peculiar/$diff['days_of_month']) * $diff['days']) 
			+ ($diff['months'] * $staff[0]->peculiar) + $staff[0]->peculiar);
		$hazardArrears = $f->format($f->format(($staff[0]->hazard/$diff['days_of_month']) * $diff['days']) 
			+ ($diff['months'] * $staff[0]->hazard));
		$callDutyArrears = $f->format($f->format(($staff[0]->callDuty/$diff['days_of_month']) * $diff['days']) 
			+ ($diff['months'] * $staff[0]->callDuty));

		$taxArrears = $f->format($f->format(($staff[0]->tax/$diff['days_of_month']) * $diff['days']) 
			+ ($diff['months'] * $staff[0]->tax) + $staff[0]->tax);	
		$unionDuesArrears = $f->format($f->format(($staff[0]->unionDues/$diff['days_of_month']) * $diff['days']) 
			+ ($diff['months'] * $staff[0]->unionDues) + $staff[0]->unionDues);
		$nhfArrears = $f->format($f->format(($staff[0]->nhf/$diff['days_of_month']) * $diff['days']) 
			+ ($diff['months'] * $staff[0]->nhf) + $staff[0]->nhf);	
		$pensionArrears = $f->format($f->format(($staff[0]->pension/$diff['days_of_month']) * $diff['days']) 
			+ ($diff['months'] * $staff[0]->pension) + $staff[0]->pension);
		$com_msg = "Duration of arrears: ".$diff['months']."month(s) ".$diff['days']. "days of ". $diff['days_of_month']."day(s)<br>";	
		$com_msg .= "Basic salary: ".$basicsalary."<br>";
		$com_msg .= "Day Arrears: ".$f->format(($basicsalary/$diff['days_of_month']) * $diff['days'])."<br>";
		$com_msg .= "Basic arrears: ".$basicArrears."<br>";
		$com_msg .= "Cummulative Emoluments: ". $f->format($basicsalary + $basicArrears)."<br>";
		$com_msg .= "Peculiar: ".$staff[0]->peculiar."<br>";
		$com_msg .= "Tax: ".$staff[0]->tax."<br>"; 
		$com_msg .= "Union Dues: ".$staff[0]->unionDues."<br>";
		$com_msg .= "Use of government vehicle: ".$staff[0]->ugv."<br>";
		$com_msg .= "NHF: ".$staff[0]->nhf."<br>";
		$com_msg .= "Pension: ".$staff[0]->pension."<br>";
		//$motorbasic = $peculiarArrears + $staff[0]->leave_bonus;

                $motorbasic = $peculiarArrears + $leaveAllArrears + $staff[0]->leave_bonus;

		$grossemolument = $basicsalary + $basicArrears;
		$totaldeduction = $taxArrears + $unionDuesArrears + $nhfArrears + $pensionArrears + $staff[0]->ugv + $staff[0]->nicnCoop + $staff[0]->phoneCharges;
		$totaldeduction += $staff[0]->ctlsLab + $staff[0]->ctlsFed + $staff[0]->motorAdv;
		$com_msg .= "Total Deduction: ".$f->format($totaldeduction)."<br>";

		$netpay = $grossemolument - $totaldeduction;
		$grosspay = $totaldeduction + $motorbasic + $netpay;
		$totalemolument = $netpay + $motorbasic;
		$com_msg .= "Net Pay: ".$netpay."<br>";
		$com_msg .= "Motor Basic Allowance: ".$motorbasic."<br>";
		$com_msg .= "Total Net Emoluments: ".$totalemolument."<br>";
		$details = "advancement computation by ".Auth::user()->name.", ".$this->division;
		//$this->oneStaff($myarray); //clear previous salary first		
		
		try 
		{
			DB::beginTransaction();
		$query = DB::table('tblpayment')->where(['fileNo'=>$due->fileNo, 'month'=>$activeMonthString, 
			'year'=>$year])->update(['basic_salary'=> $basicsalary, 'rank' => $staff[0]->rank, 'arrearsBasic' => $basicArrears, 
			'tax'=>$taxArrears, 'pension'=>$pensionArrears, 'nhf'=>$nhfArrears, 'unionDues'=>$unionDuesArrears, 
			'ugv' => $staff[0]->ugv, 'nicnCoop'=>$staff[0]->nicnCoop, 'ctlsLab'=>$staff[0]->ctlsLab, 
			'ctlsFed' => $staff[0]->ctlsFed, 'fedHousing' => $staff[0]->fedHousing, 'motorAdv'=> $staff[0]->motorAdv,
			'bicycleAdv'=> $staff[0]->bicycleAdv, 'cumEmolu'=>$grossemolument, 'motorBasicAll'=>$motorbasic, 
			'totalDeduct'=>$totaldeduction, 'netpay'=>$netpay, 'grosspay'=>$grosspay, 'totalEmolu'=>$totalemolument, 
			'peculiar' =>$peculiarArrears, 'hazard'=>$staff[0]->hazard, 'callDuty'=>$staff[0]->callDuty, 
			'shiftAll'=>$staff[0]->shiftAll, 'phoneCharges'=>$staff[0]->phoneCharges, 'pa_deduct'=>$staff[0]->pa_deduct,
			'surcharge'=>$staff[0]->surcharge, 'arrears'=>$arr_sum, 'grade'=>$staff[0]->grade, 
			'step'=>$staff[0]->step, 'purpose'=>DB::raw("concat(purpose, '$details')") ]);

		DB::insert('replace into tblarrears set fileNo = ?, month = ?, year = ?, oldGrade = ?, oldStep = ?, 
			newGrade = ?, newStep = ?, oldBasic = ?, oldTax = ?, oldPeculiar = ?, oldLeave_bonus = ?, oldPension = ?,
			oldNhf = ?, oldUnionDues = ?, type = ?, dueDate = ?, divisionID = ?,courtID = ?, date = ?', [$due->fileNo, $activeMonthString, $year, 
			$due->old_grade, $due->old_step, $due->new_grade, $due->new_step, $staff[0]->amount, $staff[0]->tax, 
			$staff[0]->peculiar, $staff[0]->leave_bonus, $staff[0]->pension, $staff[0]->nhf, $staff[0]->unionDues, 
			'new-appointment', $staff[0]->appointment_date, $due->divisionID,$due->courtID, date('Y-m-d')]);
		DB::update('update tblstaff_for_arrears set payment_status = 1,month_payment = ?, year_payment = ? where fileNo = ?', [$activeMonthString, $year, $staff[0]->fileNo]);
		DB::commit();
	}
	catch (Exception $e) 
		{		
			//dd($e);
		}
		//dd(DB::getQueryLog());
		if($query)
			return array('msg'=>'New appointment computation completed successfully', 'result_details'=>$com_msg);
		else
			return array('msg'=>'An error occurred', 'result_details'=>$com_msg);
		return;
		//dd(DB::getQueryLog());
	  }//end due for new appointment
	}//end of new appointment

	public function payment(Request $request) 
	{
		//short/over payment
		/*$this->validate($request, [
			'staffList'			=> 'required|numeric',
			'paymentType'		=> 'required|alpha_dash',
			'effectivePoint'	=> 'required|alpha_dash',
			'amount'			=> 'required|numeric',
			]);

		$month = $request['month'];
		$year = $request['year'];
		$court = $request['court'];
		$query = DB::select('select PY.basic_salary, PY.actingAllow, PY.pension, PY.motorBasicAll, PY.totalDeduct, PY.actingAllow, PY.arrearsBasic, PY.nhf, PY.unionDues, PY.netpay, PY.tax FROM tblper as PER,tblpayment AS PY WHERE PER.fileNo = PY.fileNo and PY.fileNo = ? and month = ? and year = ? and courtID=?', [$request->staffList, $request['month'], $request['year'], $request['court']]);
		$rs = 0;

		if($request->paymentType == 'short-payment')
		{
			if($request->effectivePoint == 'basic-salary')
			{
				$newBs = $query[0]->basic_salary + $request->amount;
				$newGrossEmolu = $newBs + $query[0]->actingAllow + $query[0]->arrearsBasic;
				$newNetpay = $newGrossEmolu - $query[0]->totalDeduct;
				$grosspay = $query[0]->totalDeduct +  $newNetpay + $query[0]->motorBasicAll;
				$newTotalEmolu = $newNetpay + $query[0]->motorBasicAll;
				$details = ' Short payment added to Basic salary by '.$this->details;
				
				$rs = DB::table('tblpayment')->where(['fileNo'=>$request->staffList, 'month'=> $month, 'year'=>$this->year])->update(['basic_salary'=> $newBs, 'cumEmolu'=>$newGrossEmolu, 'netpay'=>$newNetpay, 'grosspay'=>$grosspay, 'totalEmolu'=>$newTotalEmolu, 'purpose'=>DB::raw("concat(purpose, '$details')") ]);
			}
			elseif ($request->effectivePoint == 'acting-allowance') 
			{
				$newActingAllowance = $query[0]->actingAllow + $request->amount;
				$newGrossEmolu = $query[0]->basic_salary + $newActingAllowance + $query[0]->arrearsBasic;
				$newNetpay = $newGrossEmolu - $query[0]->totalDeduct;
				$grosspay = $query[0]->totalDeduct + $newNetpay + $query[0]->motorBasicAll;
				$newTotalEmolu = $newNetpay + $query[0]->motorBasicAll;
				$details = ' Short payment added to acting allowance by '.$this->details;
				$rs = DB::table('tblpayment')->where(['fileNo'=>$request->staffList, 'month'=> $month, 'year'=>$this->year])->update(['actingAllow'=> $newActingAllowance, 'cumEmolu'=>$newGrossEmolu, 'netpay'=>$newNetpay, 'grosspay'=>$grosspay, 'totalEmolu'=>$newTotalEmolu, 'purpose'=>DB::raw("concat(purpose, '$details')") ]);
			}

                          elseif($request->effectivePoint == 'basic-arrears')
			{
				$newBasicArrears = $query[0]->arrearsBasic + $request->amount;
				$newGrossEmolu = $query[0]->basic_salary + $newBasicArrears + $query[0]->actingAllow;
				$newNetpay = $newGrossEmolu - $query[0]->totalDeduct;
				$grosspay = $query[0]->totalDeduct +  $newNetpay + $query[0]->motorBasicAll;
				$newTotalEmolu = $newNetpay + $query[0]->motorBasicAll;
				$details = ' Short payment added to Basic Arrears by '.$this->details;
				
				$rs = DB::table('tblpayment')->where(['fileNo'=>$request->staffList, 'month'=> $month, 'year'=>$this->year])->update(['arrearsBasic'=> $newBasicArrears, 'cumEmolu'=>$newGrossEmolu, 'netpay'=>$newNetpay, 'grosspay'=>$grosspay, 'totalEmolu'=>$newTotalEmolu, 'purpose'=>DB::raw("concat(purpose, '$details')") ]);
			}

			elseif ($request->effectivePoint == 'motor-basic') 
			{
				$newMb = $query[0]->motorBasicAll + $request->amount;
				$grosspay = $query[0]->totalDeduct + $query[0]->netpay + $newMb;
				$newTotalEmolu = $query[0]->netpay + $newMb;
				$details = ' Short payment added to motor basic allowance by '.$this->details;
				$rs = DB::table('tblpayment')->where(['fileNo'=>$request->staffList, 'month'=> $month, 'year'=>$this->year,'courtID'=>$court])
					->update(['motorBasicAll'=> $newMb, 'grosspay'=>$grosspay, 'totalEmolu'=>$newTotalEmolu, 
						'purpose'=>DB::raw("concat(purpose, '$details')") ]);
			}
			elseif ($request->effectivePoint == 'pension') 
			{
				$pension = $query[0]->pension + $request->amount;
				$totalDeduct = $query[0]->totalDeduct + $request->amount;
				$newGrossEmolu = $query[0]->basic_salary + $query[0]->actingAllow + $query[0]->arrearsBasic;
				$newNetpay = $newGrossEmolu - $totalDeduct;
				$grosspay = $totalDeduct + $newNetpay + $query[0]->motorBasicAll;
				$newTotalEmolu = $newNetpay + $query[0]->motorBasicAll;
				$details = ' Short payment added to pension by '.$this->details;
				$rs = DB::table('tblpayment')->where(['fileNo'=>$request->staffList, 'month'=> $month, 'year'=>$this->year,'courtID'=>$court])
					->update(['pension'=> $pension, 'totalDeduct'=>$totalDeduct, 'grosspay'=>$grosspay, 'netpay'=>$newNetpay, 'totalEmolu'=>$newTotalEmolu, 
						'purpose'=>DB::raw("concat(purpose, '$details')") ]);
			}
			elseif ($request->effectivePoint == 'tax') 
			{
				$tax = $query[0]->tax + $request->amount;
				$totalDeduct = $query[0]->totalDeduct + $request->amount;
				$newGrossEmolu = $query[0]->basic_salary + $query[0]->actingAllow + $query[0]->arrearsBasic;
				$newNetpay = $newGrossEmolu - $totalDeduct;
				$grosspay = $totalDeduct + $newNetpay + $query[0]->motorBasicAll;
				$newTotalEmolu = $newNetpay + $query[0]->motorBasicAll;
				$details = ' Short payment added to pension by '.$this->details;
				$rs = DB::table('tblpayment')->where(['fileNo'=>$request->staffList, 'month'=> $month, 'year'=>$year,'courtID'=>$court])->update(['tax'=> $tax, 'totalDeduct'=>$totalDeduct, 'grosspay'=>$grosspay, 'netpay'=>$newNetpay, 'totalEmolu'=>$newTotalEmolu, 'purpose'=>DB::raw("concat(purpose, '$details')") ]);
			}
			elseif ($request->effectivePoint == 'nhf') 
			{
				$nhf = $query[0]->nhf + $request->amount;
				$totalDeduct = $query[0]->totalDeduct + $request->amount;
				$newGrossEmolu = $query[0]->basic_salary + $query[0]->actingAllow + $query[0]->arrearsBasic;
				$newNetpay = $newGrossEmolu - $totalDeduct;
				$grosspay = $totalDeduct + $newNetpay + $query[0]->motorBasicAll;
				$newTotalEmolu = $newNetpay + $query[0]->motorBasicAll;
				$details = ' Short payment added to NHF by '.$this->details;
				$rs = DB::table('tblpayment')->where(['fileNo'=>$request->staffList, 'month'=> $month, 'year'=>$year])->update(['nhf'=> $nhf, 'totalDeduct'=>$totalDeduct, 'grosspay'=>$grosspay, 'netpay'=>$newNetpay, 'totalEmolu'=>$newTotalEmolu, 'purpose'=>DB::raw("concat(purpose, '$details')") ]);
			}
			elseif ($request->effectivePoint == 'unionDues') 
			{
				$unionDues = $query[0]->unionDues + $request->amount;
				$totalDeduct = $query[0]->totalDeduct + $request->amount;
				$newGrossEmolu = $query[0]->basic_salary + $query[0]->actingAllow + $query[0]->arrearsBasic;
				$newNetpay = $newGrossEmolu - $totalDeduct;
				$grosspay = $totalDeduct + $newNetpay + $query[0]->motorBasicAll;
				$newTotalEmolu = $newNetpay + $query[0]->motorBasicAll;
				$details = ' Short payment added to union Dues by '.$this->details;
				$rs = DB::table('tblpayment')->where(['fileNo'=>$request->staffList, 'month'=> $month, 'year'=>$year,'courtID'=>$court])->update(['unionDues'=> $unionDues, 'totalDeduct'=>$totalDeduct, 'grosspay'=>$grosspay, 'netpay'=>$newNetpay, 'totalEmolu'=>$newTotalEmolu, 'purpose'=>DB::raw("concat(purpose, '$details')") ]);
			}
		}
		else
		{
			if($request->effectivePoint == 'basic-salary')
			{
				$newBs = $query[0]->basic_salary - $request->amount;
				$newGrossEmolu = $newBs + $query[0]->actingAllow + $query[0]->arrearsBasic; 
				$newNetpay = $newGrossEmolu - $query[0]->totalDeduct;
				$grosspay = $query[0]->totalDeduct  + $newNetpay + $newBs;
				$newTotalEmolu = $newNetpay + $query[0]->motorBasicAll;
				$details = ' Over payment added to basic salary by '.$this->details;
				$rs = DB::table('tblpayment')->where(['fileNo'=>$request->staffList, 'month'=> $month, 'year'=>$year,'courtID'=>$court])
					->update(['basic_salary'=> $newBs, 'cumEmolu'=>$newGrossEmolu, 'netpay'=>$newNetpay, 'grosspay'=>$grosspay, 'totalEmolu'=>$newTotalEmolu, 'purpose'=>DB::raw("concat(purpose, '$details')") ]);
			}
			elseif ($request->effectivePoint == 'acting-allowance') 
			{
				$actingAllow = $query[0]->actingAllow - $request->amount;
				$newGrossEmolu = $query[0]->basic_salary + $actingAllow + $query[0]->arrearsBasic;
				$newNetpay = $newGrossEmolu - $query[0]->totalDeduct;
				$grosspay = $query[0]->totalDeduct + $newNetpay + $query[0]->motorBasicAll;
				$newTotalEmolu = $newNetpay + $query[0]->motorBasicAll; 
				$details = ' Over payment added to acting allowance by '.$this->details;
				$rs = DB::table('tblpayment')->where(['fileNo'=>$request->staffList, 'month'=> $month, 'year'=>$year])
					->update(['actingAllow'=> $actingAllow, 'cumEmolu'=>$newGrossEmolu, 'netpay'=>$newNetpay, 'grosspay'=>$grosspay, 
						'totalEmolu'=>$newTotalEmolu, 'purpose'=>DB::raw("concat(purpose, '$details')") ]);
			}

                        elseif($request->effectivePoint == 'basic-arrears')
			{
				$newBasicArrears = $query[0]->arrearsBasic - $request->amount;
				$newGrossEmolu = $query[0]->basic_salary + $newBasicArrears + $query[0]->actingAllow;
				$newNetpay = $newGrossEmolu - $query[0]->totalDeduct;
				$grosspay = $query[0]->totalDeduct +  $newNetpay + $query[0]->motorBasicAll;
				$newTotalEmolu = $newNetpay + $query[0]->motorBasicAll;
				$details = ' Over payment added to Basic Arrears by '.$this->details;
				
				$rs = DB::table('tblpayment')->where(['fileNo'=>$request->staffList, 'month'=> $month, 'year'=>$year,'courtID'=>$court])->update(['arrearsBasic'=> $newBasicArrears, 'cumEmolu'=>$newGrossEmolu, 'netpay'=>$newNetpay, 'grosspay'=>$grosspay, 'totalEmolu'=>$newTotalEmolu, 'purpose'=>DB::raw("concat(purpose, '$details')") ]);
			}

			elseif ($request->effectivePoint == 'motor-basic') 
			{
				$newMb = $query[0]->motorBasicAll - $request->amount;
				$grosspay = $query[0]->totalDeduct + $query[0]->netpay + $newMb;
				$newTotalEmolu = $query[0]->netpay + $newMb;
				$details = ' Over payment added to motor basic allowance by '.$this->details;
				$rs = DB::table('tblpayment')->where(['fileNo'=>$request->staffList, 'month'=> $month, 'year'=>$year,'courtID'=>$court])
					->update(['motorBasicAll'=> $newMb, 'grosspay'=>$grosspay, 'totalEmolu'=>$newTotalEmolu, 
						'purpose'=>DB::raw("concat(purpose, '$details')") ]);
			}
			elseif ($request->effectivePoint == 'pension') 
			{
				$pension = $query[0]->pension - $request->amount;
				$totaldeduction = $query[0]->totalDeduct - $request->amount;
				$newGrossEmolu = $query[0]->basic_salary + $query[0]->actingAllow + $query[0]->arrearsBasic;
				$newNetpay = $newGrossEmolu - $totaldeduction;
				$grosspay = $totaldeduction + $newNetpay + $query[0]->motorBasicAll;
				$newTotalEmolu = $newNetpay + $query[0]->motorBasicAll;
				$details = ' Over payment added to pension by '.$this->details;
				$rs = DB::table('tblpayment')->where(['fileNo'=>$request->staffList, 'month'=> $month, 'year'=>$year,,'courtID'=>$court])
					->update(['pension'=> $pension, 'totalDeduct'=>$totaldeduction, 'grosspay'=>$grosspay, 'netpay'=>$newNetpay, 
						'totalEmolu'=>$newTotalEmolu, 'purpose'=>DB::raw("concat(purpose, '$details')") ]);
			}
			elseif ($request->effectivePoint == 'tax') 
			{
				$tax = $query[0]->tax - $request->amount;
				$totaldeduction = $query[0]->totalDeduct - $request->amount;
				$newGrossEmolu = $query[0]->basic_salary + $query[0]->actingAllow + $query[0]->arrearsBasic;
				$newNetpay = $newGrossEmolu - $totaldeduction;
				$grosspay = $totaldeduction + $newNetpay + $query[0]->motorBasicAll;
				$newTotalEmolu = $newNetpay + $query[0]->motorBasicAll;
				$details = ' Over payment added to tax by '.$this->details;
				$rs = DB::table('tblpayment')->where(['fileNo'=>$request->staffList, 'month'=> $month, 'year'=>$year,'courtID'=>$court])
					->update(['tax'=> $tax, 'totalDeduct'=>$totaldeduction, 'grosspay'=>$grosspay, 'netpay'=>$newNetpay, 
						'totalEmolu'=>$newTotalEmolu, 'purpose'=>DB::raw("concat(purpose, '$details')") ]);
			}
			elseif ($request->effectivePoint == 'nhf') 
			{
				$nhf = $query[0]->nhf - $request->amount;
				$totaldeduction = $query[0]->totalDeduct - $request->amount;
				$newGrossEmolu = $query[0]->basic_salary + $query[0]->actingAllow + $query[0]->arrearsBasic;
				$newNetpay = $newGrossEmolu - $totaldeduction;
				$grosspay = $totaldeduction + $newNetpay + $query[0]->motorBasicAll;
				$newTotalEmolu = $newNetpay + $query[0]->motorBasicAll;
				$details = ' Over payment added to nhf by '.$this->details;
				$rs = DB::table('tblpayment')->where(['fileNo'=>$request->staffList, 'month'=> $month, 'year'=>$year,'courtID'=>$court])
					->update(['nhf'=> $nhf, 'totalDeduct'=>$totaldeduction, 'grosspay'=>$grosspay, 'netpay'=>$newNetpay, 
						'totalEmolu'=>$newTotalEmolu, 'purpose'=>DB::raw("concat(purpose, '$details')") ]);
			}
			elseif ($request->effectivePoint == 'unionDues') 
			{
				$unionDues = $query[0]->unionDues - $request->amount;
				$totaldeduction = $query[0]->totalDeduct - $request->amount;
				$newGrossEmolu = $query[0]->basic_salary + $query[0]->actingAllow + $query[0]->arrearsBasic;
				$newNetpay = $newGrossEmolu - $totaldeduction;
				$grosspay = $totaldeduction + $newNetpay + $query[0]->motorBasicAll;
				$newTotalEmolu = $newNetpay + $query[0]->motorBasicAll;
				$details = ' Over payment added to union dues by '.$this->details;
				$rs = DB::table('tblpayment')
				->where(['fileNo'=>$request->staffList, 'month'=> $month, 'year'=>$year,'courtID'=>$court])
					->update(['unionDues'=> $unionDues, 'totalDeduct'=>$totaldeduction, 'grosspay'=>$grosspay, 'netpay'=>$newNetpay, 'totalEmolu'=>$newTotalEmolu, 'purpose'=>DB::raw("concat(purpose, '$details')")]);
			}
		} //end of else
		$this->addLog($request->paymentType." computation for staff, fileno = ".$request->staffList.", amount = ".$request->amount.", effective point= ".
			$request->effectivePoint." for "."$month $year.");
		if($rs)
			//return back()->with('msg', $request->paymentType. ' computed successfully');
			 return response()->json('Successfull');
			 */
		
	} //end of payment
	public function suspension(Request $request)
	{
		$this->validate($request, [
			'staffList'			=> 'required|numeric',
			'numMonth'			=> 'required|numeric',
			]);
		$month = $this->month;
		$year = $this->year;
		$staff = DB::select('select a.fileNo, title, surname, AccNo , first_name, othernames, a.grade, a.step, rank, a.bankID, bank, bankgroup, bank_branch, status_value, amount, section, tax, peculiar, leave_bonus, pension, nhf, unionDues, appointment_date, ugv, nicnCoop, motorAdv, bicycleAdv, ctlsLab, ctlsFed, fedHousing, hazard, callDuty, phoneCharges, c.pa_deduct, surcharge, shiftAll, MONTH(appointment_date) AS MTH, YEAR(appointment_date) AS YR, current_state, e.NumofPA, nhfNo from tblper a inner join basicsalary b on 
			(a.grade = b.grade and a.step = b.step) and (a.employee_type = b.employee_type) inner JOIN tblcv c on 
			(a.fileNo = c.fileNo) INNER JOIN tblbanklist d on (a.bankID = d.bankID) left join (SELECT fileNo, 
			count(Name) as NumofPA from tblpersonalassistant group by fileNo) e on (a.fileNo = e.fileNo) where
			divisionID = ? and a.fileNo = ? and a.employee_type <> \'CONSOLIDATED\' and a.staff_status = 1', 
			[$this->divisionID, $request->staffList]);
		
		$actingAllow = $staff[0]->amount * ($request->numMonth -1);
		$arrearsBasic = 0;
		$tax = $staff[0]->tax * $request->numMonth;
		$pension = $staff[0]->pension * $request->numMonth;
		$nhf = $staff[0]->nhf * $request->numMonth;
		$unionDues = $staff[0]->unionDues * $request->numMonth;
		$ugv = $staff[0]->ugv * $request->numMonth;
		$nicnCoop = $staff[0]->nicnCoop * $request->numMonth;
		$ctlsLab = $staff[0]->ctlsLab * $request->numMonth;
		$ctlsFed = $staff[0]->ctlsFed * $request->numMonth;
		$fedHousing = $staff[0]->fedHousing * $request->numMonth;
		$motorAdv = $staff[0]->motorAdv * $request->numMonth;
		$callDuty = $staff[0]->callDuty * $request->numMonth;
		$hazard = $staff[0]->hazard * $request->numMonth;
		$shiftAll = $staff[0]->shiftAll * $request->numMonth;
		$phoneCharges = $staff[0]->phoneCharges * $request->numMonth;
		$pa_deduct = $staff[0]->pa_deduct * $request->numMonth;
		$surcharge = $staff[0]->surcharge * $request->numMonth;
		if ($staff[0]->status_value == "Contract Service")
		{
			$unionDues = 0;
			$pension = 0;
			$nhf = 0;
		}
		$pa_deduct = $staff[0]->pa_deduct * $staff[0]->NumofPA;
		$peculiar = $staff[0]->peculiar * $request->numMonth;
		$leave_b = $staff[0]->leave_bonus * $request->numMonth;
		//$motorbasic = $peculiar + $staff[0]->leave_bonus + $callDuty + $hazard + $shiftAll;
		$motorbasic = $peculiar + $leave_b;
		$grossemolument = $staff[0]->amount + $actingAllow + $arrearsBasic;
		$totaldeduction = $tax + $pension + $nhf + $unionDues + $ugv + $nicnCoop + $fedHousing + $pa_deduct;
		$totaldeduction += $ctlsLab + $ctlsFed + $motorAdv  + $phoneCharges + $surcharge;
		$netpay = $grossemolument - $totaldeduction;
		$grosspay = $totaldeduction + $motorbasic + $netpay;
		$totalemolument = $netpay + $motorbasic;
		$details = ' Suspension computation by '.$this->details;

		try {
			//DB::enableQueryLog();
			$query = DB::table('tblpayment')->where(['fileNo'=>$request->staffList, 'month'=>Session::get('activeMonth'), 'year'=>$this->year])
			->update(['basic_salary'=> $staff[0]->amount, 'actingAllow'=>$actingAllow, 'tax'=>$tax, 'pension'=>$pension, 'nhf'=>$nhf, 'unionDues'=>$unionDues, 'ugv' => $ugv, 'nicnCoop'=>$nicnCoop, 'ctlsLab'=>$ctlsLab, 
				'ctlsFed' => $ctlsFed, 'fedHousing' => $fedHousing, 'motorAdv'=> $motorAdv, 'cumEmolu'=>$grossemolument,
				'motorBasicAll'=>$motorbasic, 'totalDeduct'=>$totaldeduction, 'netpay'=>$netpay, 'grosspay'=>$grosspay, 
				'totalEmolu'=>$totalemolument, 'peculiar' =>$peculiar, 'hazard'=>$hazard, 'callDuty'=>$callDuty, 
				'shiftAll'=>$shiftAll, 'phoneCharges'=>$phoneCharges, 'pa_deduct'=>$pa_deduct, 'surcharge'=>$surcharge,
				'grade'=>$staff[0]->grade, 'step'=>$staff[0]->step, 'purpose'=>DB::raw("concat(purpose, '$details')") ]);
			$this->addLog(" Suspension computation for staff, fileno = ".$request->staffList.", for $this->month $year.");
			//dd(DB::getQueryLog());
		} catch(\Illuminate\Database\QueryException $ex)
			{
				//dd($ex->getMessage());
				return back()->with('warning', 'An error occurred.');
			}
		
		//dd(DB::getQueryLog());
		if($query == 1)
			return back()->with('msg', 'Suspension pay computation completed successfully');
		else
			return back()->with('msg', 'Record could not be saved');
		return;
	} //end of suspension

	public function retirement(Request $request)
	{
		$f = new Formatter;

		$this->validate($request,  [
			'staffList'			=> 'required|numeric',
			'retirementDate'	=> 'required|date',
			]);

		$month = $request['month'];
		$year  = $request['year'];
		$staff  = $request['staffList'];

		//DB::enableQueryLog();
		//$query = DB::select("select b.*, c.*, status_value, NumofPA from tblper a inner join tblcv b on a.fileNo = b.fileNo inner join basicsalary c on a.employee_type = c.employee_type and a.grade = c.grade and a.step = c.step left join (SELECT fileNo, count(*) as NumofPA from tblpersonalassistant group by fileNo) d on (d.fileNo = a.fileNo) and a.fileNo = 127");
 
        
		$staffList = DB::table('tblper')->where('fileNo','=',$staff)->first();
		$NumofPA = DB::table('tblper')->where('fileNo','=',$staff)->count();

		$query = DB::table('tblper')
		->join('tblcv','tblcv.fileNo','=', 'tblper.fileNo')
		->join('basicsalary','basicsalary.employee_type','=', 'tblper.employee_type')
		->where('basicsalary.grade','=',$staffList->grade)
		->where('basicsalary.step','=',$staffList->step)
		//->join('basicsalary','basicsalary.step','=', 'tblper.step')
		->where('tblper.fileNo','=',$staff)
		->first();
		//dd($query);
		//dd(DB::getQueryLog());
		list($year1, $mth1, $day1) = explode("-", $request->retirementDate);
		$days_month = cal_days_in_month(CAL_GREGORIAN, $mth1, $year1);

		//dd($day1);
		
		$basicsalary =  $f->format(($query->amount * $day1)/$days_month);
		//dd($basicsalary);
		$tax =  $f->format(($query->tax * $day1)/$days_month);
		$pension =  $f->format(($query->pension * $day1)/$days_month);
		$nhf =  $f->format(($query->nhf * $day1)/$days_month);
		$unionDues =  $f->format(($query->unionDues * $day1)/$days_month);
		$ugv = $f->format($query->ugv);
		$nicnCoop = $f->format($query->nicnCoop);
		//dd($nicnCoop);
		$ctlsLab = $f->format($query->ctlsLab);
		$leave_bonus = $f->format(($query->leave_bonus * $day1)/$days_month);
		$ctlsFed =  $f->format(($query->ctlsFed * $day1)/$days_month);
		$motorAdv =  $f->format(($query->motorAdv * $day1)/$days_month);
		$nhf =  $f->format(($query->nhf * $day1)/$days_month);
		$callDuty = $f->format($query->callDuty);
		$hazard = $f->format($query->hazard);
		$shiftAll = $f->format($query->shiftAll);
		if($query->status_value == 'Contract Service')
		{
			$unionDues = $f->format($query->unionDues);
			$pension = $f->format($query->pension);
			$nhf = $f->format($query->nhf);
		}
		$pa_deduct = $query->pa_deduct * $NumofPA;
		$peculiar = $f->format(($query->peculiar * $day1)/$days_month);
		$phoneCharges = $f->format($query->phoneCharges);
		$motorbasic = $peculiar + $leave_bonus + $callDuty + $hazard + $shiftAll;
		$grossemolument = $basicsalary; 
		$totaldeduction = $tax + $pension + $nhf + $unionDues + $ugv + $nicnCoop + $pa_deduct + $phoneCharges;
		//$totaldeduction += $ctlsLab + $ctlsFed + $motorAdv;
		$netpay = $grossemolument - $totaldeduction;
		$grosspay = $totaldeduction + $motorbasic + $netpay;
		$totalemolument = $netpay + $motorbasic;
		$details = "retirement computation by ".$this->details;
		try {
			$query = DB::table('tblpayment')->where(['fileNo'=>$request->staffList, 'month'=>Session::get('activeMonth'), 'year'=>$this->year])
			->update(['basic_salary'=> $basicsalary, 'tax'=>$tax, 'pension'=>$pension, 'nhf'=>$nhf, 'unionDues'=>$unionDues, 
			'ugv' => $ugv, 'nicnCoop'=>$nicnCoop, 'ctlsLab'=>$ctlsLab, 'ctlsFed' => $ctlsFed, 'motorAdv'=> $motorAdv,
			'cumEmolu'=>$grossemolument, 'motorBasicAll'=>$motorbasic, 'totalDeduct'=>$totaldeduction, 'netpay'=>$netpay, 'grosspay'=>$grosspay, 
			'totalEmolu'=>$totalemolument, 'peculiar' =>$peculiar, 'hazard'=>$hazard, 'callDuty'=>$callDuty, 'shiftAll'=>$shiftAll, 
			'phoneCharges'=>$phoneCharges, 'pa_deduct'=>$pa_deduct, 'surcharge'=>$query->surcharge,  
			'grade'=>$query->grade, 'step'=>$query->step, 'purpose'=>$details ]);
			$this->addLog(" Retirement computation for staff, fileno = ".$request->staffList.", for $month $year.");
			//dd($query);
		} catch(\Illuminate\Database\QueryException $ex)
			{
				//dd($ex->getMessage());
				return back()->with('err', 'An error occurred.');
			}
		
		//dd(DB::getQueryLog());
		if($query == 1)
			return back()->with('msg', 'Retirement pay computation completed successfully');
		else
			return back()->with('msg', 'Record could not be saved');	
	} //end of retirement

	public function overtime(Request $request)
	{
		$f = new Formatter;
		$this->validate($request,  [
			'staffList'			=> 'required|numeric',
			'numHr'	            => 'required|numeric',
			]);
		$month = Session::get('activeMonth');
		$year = $this->year;
		DB::enableQueryLog();
		$query = DB::select('select basic_salary, arrearsBasic, actingAllow, totalDeduct, motorBasicAll, netpay, c.amount from tblpayment a inner join tblper b on a.fileNo = b.fileNo inner join basicsalary c on b.employee_type = c.employee_type and b.grade = c.grade and b.step = c.step where a.fileNo = ? and month = ? and year = ?', [$request->staffList, $month, $year]);
		$overtime = $f->format($query[0]->amount * $request->numHr * 0.007) + $query[0]->actingAllow;
		$cumEmolu = $f->format($overtime + $query[0]->basic_salary + $query[0]->arrearsBasic);
		$netpay = $f->format($cumEmolu - $query[0]->totalDeduct);
		$grosspay = $f->format($query[0]->totalDeduct + $netpay + $query[0]->motorBasicAll);
		$totalemolument = $f->format($netpay + $query[0]->motorBasicAll);
		$details = "Overtime computation by ".$this->details;
		try 
		{
			$query = DB::table('tblpayment')->where(['fileNo'=>$request->staffList, 'month'=>Session::get('activeMonth'), 'year'=>$this->year])
			->update(['actingAllow'=> $overtime, 'cumEmolu'=> $cumEmolu, 'netpay'=>$netpay, 'grosspay'=>$grosspay, 
			'totalEmolu'=>$totalemolument, 'purpose'=>DB::raw("concat(purpose, '$details')") ]);
			$this->addLog(" Overtime computation for staff, fileno = ".$request->staffList.", for $month $year.");
			//dd($query);
		} 
		catch(\Illuminate\Database\QueryException $ex)
		{
				//dd($ex->getMessage());
				return back()->with('err', 'An error occurred.');
		}		
		if($query == 1)
			return back()->with('msg', 'Overtime pay computation completed successfully');
		else
			return back()->with('msg', 'Record could not be saved');
	} //end of overtime

	public function leaveGrant(Request $request)
	{
		$f = new Formatter;
		$this->validate($request,  [
			'staffList'			=> 'required|numeric',
			'numMonth'	=> 'required|numeric',
			]);
		$month = Session::get('activeMonth');
		$year = Session::get('activeYear');
		// $payment = DB::select('select * from tblper a, basicsalary b, tblpayment c where a.employee_type = b.employee_type and a.grade = b.grade and a.step = b.step and a.fileNo = c.fileNo and c.month = ? and c.year = ? and a.fileNo = ?', [$month, $year, $request->staffList]);
		$payment = DB::select('select a.fileNo, a.grade, a.step, b.leave_bonus as b_leavebonus, c.leave_bonus, netpay, totalDeduct, e.NumofPA, c.motorBasicAll from tblper a inner join basicsalary b on (a.grade = b.grade and a.step = b.step) and (a.employee_type = b.employee_type) inner JOIN tblpayment c on (a.fileNo = c.fileNo) left join (SELECT fileNo, count(Name) as NumofPA from tblpersonalassistant group by fileNo) e on (a.fileNo = e.fileNo) WHERE a.fileNo = ? and month = ? and year = ?', [$request->staffList, $month, $year]);

		//dd($payment[0]->leave_bonus);
		$leaveArrears = $request->numMonth * $payment[0]->leave_bonus;
		//$pa_deduct = $row->pa_deduct * $row->NumofPA;			
		$motorbasic = $payment[0]->motorBasicAll + $leaveArrears;
		//$grossemolument = $row->amount;
		//$totaldeduction = $pa_deduct + $row->tax + $row->pension + $row->nhf + $row->unionDues + $row->ugv + $row->nicnCoop; 
		//$totaldeduction += $row->ctlsLab + $row->ctlsFed + $row->motorAdv + $row->bicycleAdv + $row->phoneCharges;
		//$totaldeduction += $row->fedHousing + $row->surcharge;
		$netpay = $payment[0]->netpay;
		$grosspay = $payment[0]->totalDeduct + $motorbasic + $netpay;
		$totalemolument = $netpay + $motorbasic;
		$details = "Leave grant arrears computation by ".$this->details;
		try 
		{
			$query = DB::table('tblpayment')->where(['fileNo'=>$request->staffList, 'month'=>$month, 
				'year'=>$year])
			->update(['motorBasicAll'=> $motorbasic, 'netpay'=>$netpay, 'grosspay'=>$grosspay, 
			'totalEmolu'=>$totalemolument, 'purpose'=>DB::raw("concat(purpose, '$details')") ]);
			$this->addLog(" Leave grant computation for staff, fileno = ".$request->staffList.", for $month $year.");
			//dd($query);
		} 
		catch(\Illuminate\Database\QueryException $ex)
		{
				//dd($ex->getMessage());
				return back()->with('err', 'An error occurred.');
		}		
		if($query == 1)
			return back()->with('msg', 'Leave grant computation completed successfully');
		else
			return back()->with('msg', 'Record could not be saved');
	}

}//end of ComputeProcessorController