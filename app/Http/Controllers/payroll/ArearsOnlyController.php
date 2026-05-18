<?php

namespace App\Http\Controllers\payroll;

use App\Http\Requests;
use Illuminate\Http\Request;
use Carbon\Carbon;

//use \DateTime;
use App\Services\Formatter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;

class ArearsOnlyController extends ParentController
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->month = date("n", strtotime(Session::get('activeMonth')));
        $this->activemonth = Session::get('activeMonth');
        $this->year = Session::get('activeYear');
        $this->divisionID = Session::get('divisionID');
        $this->division = Session::get('division');
        // $this->details = Auth::user()->name.", ".$this->division;
            return $next($request);
        });
    }

    public function curDivision($userId){
	    $currentDivision = DB::table("users")
	                            ->join('tbldivision', 'tbldivision.divisionID', '=', 'users.divisionID')
	                            ->where('users.id', '=', $userId)
	                            ->select('tbldivision.division', 'tbldivision.divisionID')
	                            ->first();
	   return $currentDivision;
	  }

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
    }
    public function showForm()
    {
        // $data['staffList'] = DB::table('tblper')->where('divisionID','=', $data['curDivision'] = $this->curDivision(Auth::user()->id))
        // ->orderBy('surname')
        // ->get();
        $data['CourtInfo']=$this->CourtInfo();
        $data['courtdivision']  = DB::table('tbldivision')->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);
        $data['PayrollActivePeriod'] = DB::table('tblactivemonth')->first();
        // $data['fileNo']         = ($request['fileNo']) ? $request['fileNo'] : $request['fileNofordelete'];
        if (Auth::user()->is_global==1) {
          # code...
          $data['courtstaff'] = DB::table('tblper')
          ->where('tblper.staff_status', '=', 1)
          ->orderBy('surname', 'Asc')
          ->get();
        } else {
            # code...
            $data['courtstaff'] = DB::table('tblper')
            ->where('tblper.divisionID', '=', $data['curDivision']->divisionID)
            ->where('tblper.staff_status', '=', 1)
            ->orderBy('surname', 'Asc')
            ->get();
        }

        return view('arearsOnly/chooseParam', $data);
    }

    public function findStaff(Request $request)
    {
        $this->validate($request, [
          'staffList' => 'required|numeric',
          ]);
        $fileNo = $request->input('staffList');

      $staffRecord = DB::table('tblper')->select(
        'fileNo', 'surname', 'first_name', 'othernames', 'title', 'Designation', 'rank', 'grade', 'step', 'bankID',
        'bankGroup', 'bank_branch', 'AccNo', 'section', 'appointment_date', 'dob', 'home_address', 'government_qtr',
        'employee_type', 'gender', 'divisionID', 'current_state', 'incremental_date', 'nhfNo', 'phone','medical_health_section')
        ->where('ID', '=', $fileNo)->first();
      return response()->json($staffRecord);
    }

    public function showOverDueArrearsForm()
    {
        $data['staffList'] = DB::table('tblper')->where('divisionID','=',$this->divisionID)
        ->orderBy('surname')
        ->get();
        return view('arearsOnly/overdueArrearsParam', $data);
    }

    public function arrears(Request $request)
    {
        $this->validate($request, [
            'staffList'     => 'required|numeric',
            'arrearsType'   => 'required|alpha',
            'grade'         => 'required|numeric',
            'step'          => 'required|numeric',
            'newGrade'      => 'required_unless:arrearsType,newAppointment|numeric',
            'newStep'       => 'required_unless:arrearsType,newAppointment|numeric',
            'dueDate'       => 'required_unless:arrearsType,newAppointment|date',
            ]);

          $division = DB::table('tbldivision')->where('divisionID', $request['division'])->first('division');
          $request['division'] = $division->division;
        $rs = "";
         if($request->arrearsType === 'advancement')
            $rs = $this->advancement($request);
        else if($request->arrearsType === 'newAppointment')
            $rs = $this->newAppointment($request);
        else
            return back();
        if($rs != "")
            return back()->with('msg', $rs['msg'])->with('details', $rs['result_details']);

    }//end of arrears

    private function advancement($myarray)
    {
        // $f = new Formatter;
        /*$staff = DB::select('select a.*, b.*, d.*, c.NumofPA from tblper a inner join (select * from basicsalary where (grade, step) IN ( (?, ?), (?, ?) ) ) b on (a.employee_type = b.employee_type) INNER join tblcv d on (a.fileNo = d.fileNo) and a.fileNo = ? LEFT join (SELECT fileNo, count(*) as NumofPA from tblpersonalassistant group by fileNo) c on (c.fileNo = a.fileNo)', [$myarray->grade, $myarray->step, $myarray->newGrade, $myarray->newStep, $myarray->staffList]);*/

        $btn = $myarray->button;
        $month = $myarray->month;
        $year = $myarray->year;
        if($btn == 'Compute')
        {
        $data['PayrollActivePeriod'] = DB::table('tblactivemonth')->first();
         $exist = DB::table('tblarears_payment')
         ->where('fileNo','=',$myarray->staffList)
         ->where('year','=',$data['PayrollActivePeriod']->year)
         ->where('month','=',$data['PayrollActivePeriod']->month)
         ->count();
        //  dd($myarray);
        if($exist == 1)
        {
            //return array('msg'=>'This Staff Promotion arrears has already been computed', 'result_details'=>"Failed");
            return array('msg'=>'This Staff Promotion arrears has already been computed', 'result_details'=>"Failed");
        }
        
        $staffOld = DB::table('tblper')
        ->join('basicsalaryconsolidated','basicsalaryconsolidated.employee_type','=','tblper.employee_type')
        ->join('tblbanklist','tblbanklist.bankID','=','tblper.bankID')
        ->where('tblper.fileNo','=',$myarray->staffList)
        ->where('basicsalaryconsolidated.grade','=',$myarray->grade)
        ->where('basicsalaryconsolidated.step','=',$myarray->step)
        ->select('basicsalaryconsolidated.amount','basicsalaryconsolidated.tax','basicsalaryconsolidated.peculiar','basicsalaryconsolidated.pension','tblper.status_value','tblper.surname','tblper.first_name','tblper.othernames','tblper.bankGroup','tblbanklist.bank','tblper.current_state','tblper.AccNo', 'tblper.medical_health_section')
        ->first();

        $staffNew = DB::table('tblper')
        ->join('basicsalaryconsolidated','basicsalaryconsolidated.employee_type','=','tblper.employee_type')
        ->where('tblper.fileNo','=',$myarray->staffList)
        ->where('basicsalaryconsolidated.grade','=',$myarray->newGrade)
        ->where('basicsalaryconsolidated.step','=',$myarray->newStep)
        ->select('basicsalaryconsolidated.amount','basicsalaryconsolidated.tax','basicsalaryconsolidated.peculiar','basicsalaryconsolidated.pension','tblper.status_value','tblper.AccNo', 'tblper.medical_health_section')
        ->first();

        //dd($staffNew);
        $name = $staffOld->surname." ".$staffOld->first_name." ".$staffOld->othernames;
        $arr_sum = 0;
        $dueDate = $myarray->dueDate;
        $curDate = $data['PayrollActivePeriod']->year."-".date("n", strtotime($data['PayrollActivePeriod']->month))."-1";
        // dd($dueDate);
        $diff = $this->dateDiff($curDate, $dueDate);
        $month_diff = $diff['months'];
        $days_worked = $diff['days'];
        $days_month = $diff['days_of_month'];

        $com_msg = "<div class='col-md-4'>";
        $com_msg.= "Duration of arrears: ".$month_diff."month(s) ".$days_worked."day(s)<br>";

        $basicDiff = $staffNew->amount - $staffOld->amount;
        $basic_arr = ($month_diff * $basicDiff) + $basicDiff;
        $com_msg.= "Basic Arrears: ".$basic_arr."<br>";


         $accountNo = $staffOld->AccNo;
        $taxDiff = $staffNew->tax - $staffOld->tax;
        $tax_arr = ($month_diff * $taxDiff) + $taxDiff;
        $com_msg.= "Tax Arrears: ".$tax_arr."<br>";

        //dd($tax_arr);

        if ($staffOld->status_value == "Contract Service")
        {
            $unionDues = 0;
            $pension = 0;
            $nhf = 0;
            //$leave_bonus = 0;
            $peculiar = 0;
        }
        else
        {

        $peculiarDiff = $staffNew->peculiar - $staffOld->peculiar;
        $peculiar_arr = ($month_diff * $peculiarDiff) + $peculiarDiff;
        $com_msg.= "peculiar Arrears: ".$peculiar_arr."<br>";


        $pensionDiff = $staffNew->pension - $staffOld->pension;
        $pension_arr = ($month_diff * $pensionDiff) + $pensionDiff;
        $com_msg.= "Pension Arrears: ".$pension_arr."<br>";
       }

        $grossemolument = $basic_arr + $peculiar_arr;
        $totaldeduction = $tax_arr + $pension_arr;
        $netpay =  $grossemolument - $totaldeduction;

        $com_msg.= "Gross Emolument: ".$grossemolument."<br>";
        $com_msg.= "Total Deduction: ".$totaldeduction."<br>";
        $com_msg.= "Net pay: ".$netpay."<br>";

        //dd($this->month);
        $oldCallDutyAmount = DB::table('tblcall_duty')->where('employee_section', $staffOld->medical_health_section)
                        ->where('grade', $myarray->grade)
                        ->where('step', $myarray->step)
                        ->value('callduty_amount');
        $newCallDutyAmount = DB::table('tblcall_duty')->where('employee_section', $staffOld->medical_health_section)
                        ->where('grade', $myarray->newGrade)
                        ->where('step', $myarray->newStep)
                        ->value('callduty_amount');
        $callDutyDiff = $newCallDutyAmount - $oldCallDutyAmount;
        $getCallDutyAmount = ($month_diff * $callDutyDiff) + $callDutyDiff; //+ (($callDutyDiff/$days_month) * $days_worked);

        DB::beginTransaction();
        $query = DB::table('tblarears_payment')
        ->insert(['basic_salary'=> 0, 'arrearsBasic' => $basic_arr, 'tax'=>$tax_arr, 'pension'=>$pension_arr, 'name'=> $name, 'fileNo'=>$myarray->staffList,
                    'date'=> date('Y-m-d'), 'month'=> $data['PayrollActivePeriod']->month, 'year'=> $data['PayrollActivePeriod']->year,
            'nhf'=>0, 'unionDues'=>0, 'ugv' => 0, 'nicnCoop'=>0,
            'ctlsLab'=>0, 'ctlsFed' => 0, 'fedHousing' => 0, 'bank'=>$staffOld->bank,'bankGroup'=>$staffOld->bankGroup,
            'motorAdv'=> 0, 'bicycleAdv'=> 0, 'cumEmolu'=>$grossemolument,
            'motorBasicAll'=>0, 'totalDeduct'=>$totaldeduction, 'netpay'=>$netpay, 'grosspay'=>$netpay, 'division'=>$myarray->division, 'current_state'=>$staffOld->current_state, 'AccNo'=>$accountNo,
            'totalEmolu'=>0, 'peculiar' =>$peculiar_arr, 'hazard'=>0,
            'callDuty'=>$getCallDutyAmount, 'shiftAll'=>0, 'phoneCharges'=>0,
            'pa_deduct'=>0, 'surcharge'=>0, 'arrears'=>0, 'grade'=>$myarray->grade,
            'step'=>$myarray->step,'new_grade'=>$myarray->newGrade, 'new_step'=>$myarray->newStep, 'purpose'=>"Promotion Arears Computation"]);


        if($staffOld->status_value == "Contract Service")
        {
            $pension_arrears = 0;
            $leave_bonus_arrears = 0;
            $peculiar_arrears = 0;


            DB::insert('replace into tblarrearsonly set fileNo = ?, month = ?, year = ?, oldGrade = ?, oldStep = ?,
            newGrade = ?, newStep = ?, oldBasic = ?, newBasic = ?, oldTax = ?, newTax = ?, oldPeculiar = ?,
            newPeculiar = ?, oldPension = ?, newPension = ?, type = ?, dueDate = ?, date = ?', [$myarray->staffList,
            $month, $year, $myarray->grade, $myarray->step, $myarray->newGrade, $myarray->newStep,
            $staffOld->amount, $$staffNew->amount, $staffOld->tax, $staffNew->tax, $peculiar_arrears,
            $peculiar_arrears, $pension_arrears, $pension_arrears, 'advancement', $myarray->dueDate, date('Y-m-d')]);
        }
        else
        {
        DB::insert('replace into tblarrearsonly set fileNo = ?, month = ?, year = ?, oldGrade = ?, oldStep = ?,
            newGrade = ?, newStep = ?, oldBasic = ?, newBasic = ?, oldTax = ?, newTax = ?, oldPeculiar = ?,
            newPeculiar = ?, oldPension = ?, newPension = ?, type = ?, dueDate = ?, date = ?', [$myarray->staffList,
            $month, $year, $myarray->grade, $myarray->step, $myarray->newGrade, $myarray->newStep,
            $staffOld->amount, $staffNew->amount, $staffOld->tax, $staffNew->tax, $staffOld->peculiar,
            $staffNew->peculiar, $staffOld->pension, $staffNew->pension, 'Promotion', $myarray->dueDate,
            date('Y-m-d')]);
        }


        $this->addLog('Promotion/Advancement computation with fileNo = '.$myarray->staffList.' for '.$month.' '.$year);

        DB::commit();
        if($query == 1)
            return array('msg'=>'Computation completed successfully', 'result_details'=>$com_msg);
        else
            return array('msg'=>'An error occurred', 'result_details'=>"Error encountered");

     }//endif compute
     if($btn == 'ReCompute')
        {
         $exist = DB::table('tblarears_payment')
         ->where('fileNo','=',$myarray->staffList)
         ->where('year','=',$year)
         ->where('month','=',$month)
         ->count();

        if($exist == 0)
        {
            return array('msg'=>'You need to first Compute for this staff', 'result_details'=>"Failed");
        }

        DB::table('tblarrearsonly')
         ->where('fileNo','=',$myarray->staffList)
         ->where('year','=',$year)
         ->where('month','=',$month)
         ->delete();

        $staffOld = DB::table('tblper')
        ->join('basicsalaryconsolidated','basicsalaryconsolidated.employee_type','=','tblper.employee_type')
        ->join('tblbanklist','tblbanklist.bankID','=','tblper.bankID')
        ->where('tblper.fileNo','=',$myarray->staffList)
        ->where('basicsalaryconsolidated.grade','=',$myarray->grade)
        ->where('basicsalaryconsolidated.step','=',$myarray->step)
        ->select('basicsalaryconsolidated.amount','basicsalaryconsolidated.tax','basicsalaryconsolidated.peculiar','basicsalaryconsolidated.pension','tblper.status_value','tblper.surname','tblper.first_name','tblper.othernames','tblper.bankGroup','tblbanklist.bank','tblper.current_state','tblper.AccNo')
        ->first();

        $staffNew = DB::table('tblper')
        ->join('basicsalaryconsolidated','basicsalaryconsolidated.employee_type','=','tblper.employee_type')
        ->where('tblper.fileNo','=',$myarray->staffList)
        ->where('basicsalaryconsolidated.grade','=',$myarray->newGrade)
        ->where('basicsalaryconsolidated.step','=',$myarray->newStep)
        ->select('basicsalaryconsolidated.amount','basicsalaryconsolidated.tax','basicsalaryconsolidated.peculiar','basicsalaryconsolidated.pension','tblper.status_value','tblper.AccNo')
        ->first();

        //dd($staffNew);

        $name = $staffOld->surname." ".$staffOld->first_name." ".$staffOld->othernames;
        $arr_sum = 0;
        $dueDate = $myarray->dueDate;
        $curDate = $data['PayrollActivePeriod']->year."-".date("n", strtotime($data['PayrollActivePeriod']->month))."-1";
        $diff = $this->dateDiff($curDate, $dueDate);
        $month_diff = $diff['months'];
        $days_worked = $diff['days'];
        $days_month = $diff['days_of_month'];

        $com_msg = "<div class='col-md-4'>";
        $com_msg.= "Duration of arrears: ".$month_diff."month(s) ".$days_worked."day(s)<br>";

        $basicDiff = $staffNew->amount - $staffOld->amount;
        $basic_arr = ($month_diff * $basicDiff) + $basicDiff;
        $com_msg.= "Basic Arrears: ".$basic_arr."<br>";


        $accountNo = $staffOld->AccNo;
        $taxDiff = $staffNew->tax - $staffOld->tax;
        $tax_arr = (($month_diff * $taxDiff) + $taxDiff);

        $com_msg.= "Tax Arrears: ".$tax_arr."<br>";
        //dd($tax_arr);

        if ($staffOld->status_value == "Contract Service")
        {
            $unionDues = 0;
            $pension = 0;
            $nhf = 0;
            //$leave_bonus = 0;
            $peculiar = 0;
        }
        else
        {

        $peculiarDiff = $staffNew->peculiar - $staffOld->peculiar;
        $peculiar_arr = ($month_diff * $peculiarDiff) + $peculiarDiff;
        $com_msg.= "Total Peculiar: ".$peculiar_arr."<br>";

        $pensionDiff = $staffNew->pension - $staffOld->pension;
        $pension_arr = ($month_diff * $pensionDiff) + $pensionDiff;
        $com_msg.= "Pension Arrears: ".$pension_arr."<br>";
       }

        $grossemolument = ($basic_arr + $peculiar_arr);
        $totaldeduction = ($tax_arr + $pension_arr);
        $netpay = ( $grossemolument - $totaldeduction);
        $com_msg.= "Gross Emolument: ".$grossemolument."<br>";
        $com_msg.= "Total Deduction: ".$totaldeduction."<br>";
        $com_msg.= "Net pay: ".$netpay."<br>";

        //dd($this->month);

        DB::beginTransaction();
        $query = DB::table('tblarears_payment')
        ->where('fileNo','=',$myarray->staffList)
        ->where('year','=',$year)
        ->where('month','=',$month)
        ->update(['basic_salary'=> 0, 'arrearsBasic' => $basic_arr, 'tax'=>$tax_arr, 'pension'=>$pension_arr, 'name'=> $name, 'fileNo'=>$myarray->staffList,
                    'date'=> date('Y-m-d'), 'month'=> $month, 'year'=> $year,
            'nhf'=>0, 'unionDues'=>0, 'ugv' => 0, 'nicnCoop'=>0,
            'ctlsLab'=>0, 'ctlsFed' => 0, 'fedHousing' => 0, 'bank'=>$staffOld->bank,'bankGroup'=>$staffOld->bankGroup,
            'motorAdv'=> 0, 'bicycleAdv'=> 0, 'cumEmolu'=>$grossemolument,
            'motorBasicAll'=>0, 'totalDeduct'=>$totaldeduction, 'netpay'=>$netpay, 'grosspay'=>$netpay, 'division'=>$myarray->division, 'current_state'=>$staffOld->current_state, 'AccNo'=>$accountNo,
            'totalEmolu'=>0, 'peculiar' =>$peculiar_arr, 'hazard'=>0,
            'callDuty'=>$getCallDutyAmount, 'shiftAll'=>0, 'phoneCharges'=>0,
            'pa_deduct'=>0, 'surcharge'=>0, 'arrears'=>0, 'grade'=>$myarray->grade,
            'step'=>$myarray->step,'new_grade'=>$myarray->newGrade, 'new_step'=>$myarray->newStep, 'purpose'=>"Promotion Arears Computation"]);

         if ($staffOld->status_value == "Contract Service")
        {
            $pension_arrears = 0;
            $leave_bonus_arrears = 0;
            $peculiar_arrears = 0;


            DB::insert('replace into tblarrearsonly set fileNo = ?, month = ?, year = ?, oldGrade = ?, oldStep = ?,
            newGrade = ?, newStep = ?, oldBasic = ?, newBasic = ?, oldTax = ?, newTax = ?, oldPeculiar = ?,
            newPeculiar = ?, oldPension = ?, newPension = ?, type = ?, dueDate = ?, date = ?', [$myarray->staffList,
            $month, $year, $myarray->grade, $myarray->step, $myarray->newGrade, $myarray->newStep,
            $staffOld->amount, $$staffNew->amount, $staffOld->tax, $staffNew->tax, $peculiar_arrears,
            $peculiar_arrears, $pension_arrears, $pension_arrears, 'Promotion', $myarray->dueDate, date('Y-m-d')]);
        }
        else
        {
        DB::insert('replace into tblarrearsonly set fileNo = ?, month = ?, year = ?, oldGrade = ?, oldStep = ?,
            newGrade = ?, newStep = ?, oldBasic = ?, newBasic = ?, oldTax = ?, newTax = ?, oldPeculiar = ?,
            newPeculiar = ?, oldPension = ?, newPension = ?, type = ?, dueDate = ?, date = ?', [$myarray->staffList,
            $month, $year, $myarray->grade, $myarray->step, $myarray->newGrade, $myarray->newStep,
            $staffOld->amount, $staffNew->amount, $staffOld->tax, $staffNew->tax, $staffOld->peculiar,
            $staffNew->peculiar, $staffOld->pension, $staffNew->pension, 'Promotion', $myarray->dueDate,
            date('Y-m-d')]);
        }


        $this->addLog('Promotion/Advancement computation with fileNo = '.$myarray->staffList.' for '.$month.' '.$year);

        DB::commit();
        if($query == 1)
            return array('msg'=>'Computation completed successfully', 'result_details'=>$com_msg);
        else
            return array('msg'=>'An error occurred', 'result_details'=>"Error encountered");

     }//end if recompute

    }//end of advancement


public function promotion(Request $myarray)
    {
        $f = new Formatter;
        /*$staff = DB::select('select a.*, b.*, d.*, c.NumofPA from tblper a inner join (select * from basicsalaryconsolidated where (grade, step) IN ( (?, ?), (?, ?) ) ) b on (a.employee_type = b.employee_type) INNER join tblcv d on (a.fileNo = d.fileNo) and a.fileNo = ? LEFT join (SELECT fileNo, count(*) as NumofPA from tblpersonalassistant group by fileNo) c on (c.fileNo = a.fileNo)', [$myarray->grade, $myarray->step, $myarray->newGrade, $myarray->newStep, $myarray->staffList]);*/

        $btn = $myarray->button;
         $month = $myarray->month;
        $year = $myarray->year;
        if($btn == 'Compute')
        {

         $exist = DB::table('tblarears_payment')
         ->where('fileNo','=',$myarray->staffList)
         ->where('year','=',$this->year)
         ->where('month','=',$this->activemonth)
         ->count();

        if($exist == 1)
        {
            //return array('msg'=>'This Staff Promotion arrears has already been computed', 'result_details'=>"Failed");
            return back()->with('msg','This Staff Promotion arrears has already been computed');
        }

        $staffOld = DB::table('tblper')
        ->join('basicsalary','basicsalary.employee_type','=','tblper.employee_type')
        ->join('tblbanklist','tblbanklist.bankID','=','tblper.bankID')
        ->where('tblper.fileNo','=',$myarray->staffList)
        ->where('basicsalary.grade','=',$myarray->grade)
        ->where('basicsalary.step','=',$myarray->step)
        ->select('basicsalary.amount','basicsalary.tax','basicsalary.peculiar','basicsalary.pension','tblper.status_value','tblper.surname','tblper.first_name','tblper.othernames','tblper.bankGroup','tblbanklist.bank','tblper.current_state','tblper.AccNo')
        ->first();

        $staffNew = DB::table('tblper')
        ->join('basicsalary','basicsalary.employee_type','=','tblper.employee_type')
        ->where('tblper.fileNo','=',$myarray->staffList)
        ->where('basicsalary.grade','=',$myarray->newGrade)
        ->where('basicsalary.step','=',$myarray->newStep)
        ->select('basicsalary.amount','basicsalary.tax','basicsalary.peculiar','basicsalary.pension','tblper.status_value','tblper.AccNo')
        ->first();

        //dd($staffNew);
        $name = $staffOld->surname." ".$staffOld->first_name." ".$staffOld->othernames;
        $arr_sum = 0;
        $dueDate = $myarray->dueDate;
        $curDate = $myarray->endDate;
        $diff = $this->dateDiff($curDate, $dueDate);
        $month_diff = $diff['months'];
        $days_worked = $diff['days'];
        $days_month = $diff['days_of_month'];

        $com_msg = "<div class='col-md-4'>";
        $com_msg.= "Duration of arrears: ".$month_diff."month(s) ".$days_worked."day(s)<br>";

        $basicDiff = $staffNew->amount - $staffOld->amount;
        $basic_arr = ($month_diff * $basicDiff) + $basicDiff;
        $com_msg.= "Basic Arrears: ".$basic_arr."<br>";


         $accountNo = $staffOld->AccNo;
        $taxDiff = $staffNew->tax - $staffOld->tax;
        $tax_arr = ($month_diff * $taxDiff) + $taxDiff;
        $com_msg.= "Tax Arrears: ".$tax_arr."<br>";

        //dd($tax_arr);

        if ($staffOld->status_value == "Contract Service")
        {
            $unionDues = 0;
            $pension = 0;
            $nhf = 0;
            //$leave_bonus = 0;
            $peculiar = 0;
        }
        else
        {

        $peculiarDiff = $staffNew->peculiar - $staffOld->peculiar;
        $peculiar_arr = ($month_diff * $peculiarDiff) + $peculiarDiff;
        $com_msg.= "peculiar Arrears: ".$peculiar_arr."<br>";


        $pensionDiff = $staffNew->pension - $staffOld->pension;
        $pension_arr = ($month_diff * $pensionDiff) + $pensionDiff;
        $com_msg.= "Pension Arrears: ".$pension_arr."<br>";
       }

        $grossemolument = $basic_arr + $peculiar_arr;
        $totaldeduction = $f->format($tax_arr + $pension_arr);
        $netpay = $f->format( $grossemolument - $totaldeduction);

        $com_msg.= "Gross Emolument: ".$grossemolument."<br>";
        $com_msg.= "Total Deduction: ".$totaldeduction."<br>";
        $com_msg.= "Net pay: ".$netpay."<br>";

        //dd($this->month);

        DB::beginTransaction();
        $query = DB::table('tblarears_payment')
        ->insert(['basic_salary'=> 0, 'arrearsBasic' => $basic_arr, 'tax'=>$tax_arr, 'pension'=>$pension_arr, 'name'=> $name, 'fileNo'=>$myarray->staffList,
                    'date'=> date('Y-m-d'), 'month'=> $this->activemonth, 'year'=> $this->year,
            'nhf'=>0, 'unionDues'=>0, 'ugv' => 0, 'nicnCoop'=>0,
            'ctlsLab'=>0, 'ctlsFed' => 0, 'fedHousing' => 0, 'bank'=>$staffOld->bank,'bankGroup'=>$staffOld->bankGroup,
            'motorAdv'=> 0, 'bicycleAdv'=> 0, 'cumEmolu'=>$grossemolument,
            'motorBasicAll'=>0, 'totalDeduct'=>$totaldeduction, 'netpay'=>$netpay, 'grosspay'=>$netpay, 'division'=>$this->division, 'current_state'=>$staffOld->current_state, 'AccNo'=>$accountNo,
            'totalEmolu'=>0, 'peculiar' =>$peculiar_arr, 'hazard'=>0,
            'callDuty'=>0, 'shiftAll'=>0, 'phoneCharges'=>0,
            'pa_deduct'=>0, 'surcharge'=>0, 'arrears'=>0, 'grade'=>$myarray->grade,
            'step'=>$myarray->step,'new_grade'=>$myarray->newGrade, 'new_step'=>$myarray->newStep, 'purpose'=>"Promotion Arears Computation"]);


        if($staffOld->status_value == "Contract Service")
        {
            $pension_arrears = 0;
            $leave_bonus_arrears = 0;
            $peculiar_arrears = 0;


            DB::insert('replace into tblarrearsonly set fileNo = ?, month = ?, year = ?, oldGrade = ?, oldStep = ?,
            newGrade = ?, newStep = ?, oldBasic = ?, newBasic = ?, oldTax = ?, newTax = ?, oldPeculiar = ?,
            newPeculiar = ?, oldPension = ?, newPension = ?, type = ?, dueDate = ?, endDate = ?, date = ?', [$myarray->staffList,
            $month, $this->year, $myarray->grade, $myarray->step, $myarray->newGrade, $myarray->newStep,
            $staffOld->amount, $staffNew->amount, $staffOld->tax, $staffNew->tax, $peculiar_arrears,
            $peculiar_arrears, $pension_arrears, $pension_arrears, 'advancement', $myarray->dueDate, $myarray->endDate, date('Y-m-d')]);
        }
        else
        {
        DB::insert('replace into tblarrearsonly set fileNo = ?, month = ?, year = ?, oldGrade = ?, oldStep = ?,
            newGrade = ?, newStep = ?, oldBasic = ?, newBasic = ?, oldTax = ?, newTax = ?, oldPeculiar = ?,
            newPeculiar = ?, oldPension = ?, newPension = ?, type = ?, dueDate = ?, endDate = ?, date = ?', [$myarray->staffList,
            $month, $this->year, $myarray->grade, $myarray->step, $myarray->newGrade, $myarray->newStep,
            $staffOld->amount, $staffNew->amount, $staffOld->tax, $staffNew->tax, $staffOld->peculiar,
            $staffNew->peculiar, $staffOld->pension, $staffNew->pension, 'Promotion', $myarray->dueDate, $myarray->endDate,
            date('Y-m-d')]);
        }


        $this->addLog('Promotion/Advancement computation with fileNo = '.$myarray->staffList.' for '.Session::get('activeMonth').' '.$this->year);

        DB::commit();
       if($query == 1)
            return back()->with('msg','Computation completed successfully');
        else
            return back()->with('msg', 'An error occurred');

     }//endif compute
     if($btn == 'ReCompute')
        {
         $exist = DB::table('tblarears_payment')
         ->where('fileNo','=',$myarray->staffList)
         ->where('year','=',$this->year)
         ->where('month','=',$this->activemonth)
         ->count();

        if($exist == 0)
        {
            return array('msg'=>'You need to first Compute for this staff', 'result_details'=>"Failed");
        }

        DB::table('tblarrearsonly')
         ->where('fileNo','=',$myarray->staffList)
         ->where('year','=',$this->year)
         ->where('month','=',$this->activemonth)
         ->delete();

        $staffOld = DB::table('tblper')
        ->join('basicsalary','basicsalary.employee_type','=','tblper.employee_type')
        ->join('tblbanklist','tblbanklist.bankID','=','tblper.bankID')
        ->where('tblper.fileNo','=',$myarray->staffList)
        ->where('basicsalary.grade','=',$myarray->grade)
        ->where('basicsalary.step','=',$myarray->step)
        ->select('basicsalary.amount','basicsalary.tax','basicsalary.peculiar','basicsalary.pension','tblper.status_value','tblper.surname','tblper.first_name','tblper.othernames','tblper.bankGroup','tblbanklist.bank','tblper.current_state','tblper.AccNo')
        ->first();

        $staffNew = DB::table('tblper')
        ->join('basicsalary','basicsalary.employee_type','=','tblper.employee_type')
        ->where('tblper.fileNo','=',$myarray->staffList)
        ->where('basicsalary.grade','=',$myarray->newGrade)
        ->where('basicsalary.step','=',$myarray->newStep)
        ->select('basicsalary.amount','basicsalary.tax','basicsalary.peculiar','basicsalary.pension','tblper.status_value','tblper.AccNo')
        ->first();

        //dd($staffNew);

        $name = $staffOld->surname." ".$staffOld->first_name." ".$staffOld->othernames;
        $arr_sum = 0;
        $dueDate = $myarray->dueDate;
        $curDate = $myarray->endDate;
        $diff = $this->dateDiff($curDate, $dueDate);
        $month_diff = $diff['months'];
        $days_worked = $diff['days'];
        $days_month = $diff['days_of_month'];

        $com_msg = "<div class='col-md-4'>";
        $com_msg.= "Duration of arrears: ".$month_diff."month(s) ".$days_worked."day(s)<br>";

        $basicDiff = $staffNew->amount - $staffOld->amount;
        $basic_arr = ($month_diff * $basicDiff) + $basicDiff;
        $com_msg.= "Basic Arrears: ".$basic_arr."<br>";


        $accountNo = $staffOld->AccNo;
        $taxDiff = $staffNew->tax - $staffOld->tax;
        $tax_arr = $f->format(($month_diff * $taxDiff) + $taxDiff);

        $com_msg.= "Tax Arrears: ".$tax_arr."<br>";
        //dd($tax_arr);

        if ($staffOld->status_value == "Contract Service")
        {
            $unionDues = 0;
            $pension = 0;
            $nhf = 0;
            //$leave_bonus = 0;
            $peculiar = 0;
        }
        else
        {

        $peculiarDiff = $staffNew->peculiar - $staffOld->peculiar;
        $peculiar_arr = ($month_diff * $peculiarDiff) + $peculiarDiff;
        $com_msg.= "Total Peculiar: ".$peculiar_arr."<br>";

        $pensionDiff = $staffNew->pension - $staffOld->pension;
        $pension_arr = ($month_diff * $pensionDiff) + $pensionDiff;
        $com_msg.= "Pension Arrears: ".$pension_arr."<br>";
       }

        $grossemolument = $f->format($basic_arr + $peculiar_arr);
        $totaldeduction = $f->format($tax_arr + $pension_arr);
        $netpay = $f->format( $grossemolument - $totaldeduction);
        $com_msg.= "Gross Emolument: ".$grossemolument."<br>";
        $com_msg.= "Total Deduction: ".$totaldeduction."<br>";
        $com_msg.= "Net pay: ".$netpay."<br>";

        //dd($this->month);

        try
        {

        $query = DB::table('tblarears_payment')
        ->where('fileNo','=',$myarray->staffList)
        ->where('year','=',$this->year)
        ->where('month','=',$this->activemonth)
        ->update(['basic_salary'=> 0, 'arrearsBasic' => $basic_arr, 'tax'=>$tax_arr, 'pension'=>$pension_arr, 'name'=> $name, 'fileNo'=>$myarray->staffList,
                    'date'=> date('Y-m-d'), 'month'=> $this->activemonth, 'year'=> $this->year,
            'nhf'=>0, 'unionDues'=>0, 'ugv' => 0, 'nicnCoop'=>0,
            'ctlsLab'=>0, 'ctlsFed' => 0, 'fedHousing' => 0, 'bank'=>$staffOld->bank,'bankGroup'=>$staffOld->bankGroup,
            'motorAdv'=> 0, 'bicycleAdv'=> 0, 'cumEmolu'=>$grossemolument,
            'motorBasicAll'=>0, 'totalDeduct'=>$totaldeduction, 'netpay'=>$netpay, 'grosspay'=>$netpay, 'division'=>$this->division, 'current_state'=>$staffOld->current_state, 'AccNo'=>$accountNo,
            'totalEmolu'=>0, 'peculiar' =>$peculiar_arr, 'hazard'=>0,
            'callDuty'=>0, 'shiftAll'=>0, 'phoneCharges'=>0,
            'pa_deduct'=>0, 'surcharge'=>0, 'arrears'=>0, 'grade'=>$myarray->grade,
            'step'=>$myarray->step,'new_grade'=>$myarray->newGrade, 'new_step'=>$myarray->newStep, 'purpose'=>"Promotion Arears Computation"]);

         if ($staffOld->status_value == "Contract Service")
        {
            $pension_arrears = 0;
            $leave_bonus_arrears = 0;
            $peculiar_arrears = 0;


            DB::insert('replace into tblarrearsonly set fileNo = ?, month = ?, year = ?, oldGrade = ?, oldStep = ?,
            newGrade = ?, newStep = ?, oldBasic = ?, newBasic = ?, oldTax = ?, newTax = ?, oldPeculiar = ?,
            newPeculiar = ?, oldPension = ?, newPension = ?, type = ?, dueDate = ?, endDate = ?, date = ?', [$myarray->staffList,
            $month, $this->year, $myarray->grade, $myarray->step, $myarray->newGrade, $myarray->newStep,
            $staffOld->amount, $staffNew->amount, $staffOld->tax, $staffNew->tax, $peculiar_arrears,
            $peculiar_arrears, $pension_arrears, $pension_arrears, 'Promotion', $myarray->dueDate, $myarray->endDate, date('Y-m-d')]);
        }
        else
        {
        DB::insert('replace into tblarrearsonly set fileNo = ?, month = ?, year = ?, oldGrade = ?, oldStep = ?,
            newGrade = ?, newStep = ?, oldBasic = ?, newBasic = ?, oldTax = ?, newTax = ?, oldPeculiar = ?,
            newPeculiar = ?, oldPension = ?, newPension = ?, type = ?, dueDate = ?, endDate = ?, date = ?', [$myarray->staffList,
            $month, $this->year, $myarray->grade, $myarray->step, $myarray->newGrade, $myarray->newStep,
            $staffOld->amount, $staffNew->amount, $staffOld->tax, $staffNew->tax, $staffOld->peculiar,
            $staffNew->peculiar, $staffOld->pension, $staffNew->pension, 'Promotion', $myarray->dueDate,$myarray->endDate,
            date('Y-m-d')]);
        }


        $this->addLog('Promotion/Advancement computation with fileNo = '.$myarray->staffList.' for '.Session::get('activeMonth').' '.$this->year);
          return back()->with('msg','Computation completed successfully');
        }
        catch(\Exception $ex)
        {
        return back()->with('msg', 'An error occurred');
        }

     }//end if recompute

    }//end of promotio


 public function epaymentIndex()
    {
           $data['allbanklist']  = DB::table('tblper')
            //  ->where('tblper.divisionID', '=', $this->divisionID)
             ->select('tblbanklist.bank', 'tblper.bankID')
             ->distinct()
             ->join('tblbanklist', 'tblper.bankID', '=', 'tblbanklist.bankID')
             ->orderBy('bank', 'Asc')
             ->get();

             $data['CourtInfo'] = $this->CourtInfo();
            if ($data['CourtInfo']->courtstatus == 0) {
              $request['court'] = $data['CourtInfo']->courtid;
            }
            if ($data['CourtInfo']->divisionstatus == 0) {
              $request['division'] = $data['CourtInfo']->divisionid;
            }
             
            $data['courtDivisions']  = DB::table('tbldivision')->get();
        
            $data['curDivision'] = $this->curDivision(Auth::user()->id);
            
              return view('arearsOnly.epaymentForm',$data);
    }
      public function retrieveEpayment(Request $request)
{
  $month     = trim($request->input('month'));
  $year      = trim($request->input('year'));
  $bankID    = trim($request->input('bankName'));
  //$bankGroup = trim($request->input('bankGroup'));
  $division  = $this->division;
  $this->validate($request,[
    'month'     => 'required|regex:/^[\pL\s\-]+$/u',
    'year'      => 'required|integer',
    'bankName'  => 'required|integer',
    //'bankGroup' => 'required|integer'
  ]);
  Session::put('serialNo', 1);
  Session::put('bankID', $bankID);
  //Session::put('bankGroup', $bankGroup);
  $getBank = DB::table('tblbanklist')
           ->where('bankID', $bankID)
           ->first();
           //dd($getBank);
  $bankName = $getBank->bank;
  $bankCode = DB::table('tblbank')
            ->where('bankID',$bankID)
            ->first();
            Session::put('bank', $bankName);
  Session::put('bankCode', $bankCode->bank_code);
  Session::put('sortCode', $bankCode->sort_code);
  $data['epayment_detail'] = DB::table('tblarears_payment')
        ->where('tblarears_payment.division',  '=',$request->division)
        ->where('tblarears_payment.month',     '=', $month)
        ->where('tblarears_payment.year',      '=', $year)
        //->where('tblarears_payment.division',  '=', $division)
        ->where('tblarears_payment.bank',      '=', $bankName )
        //->where('tblarears_payment.bankGroup', '=',$bankGroup)
        ->orderBy('totalEmolu','DESC')
        ->orderBy('name','ASC')
        ->paginate(20);
  $data['epayment_total'] = DB::table('tblarears_payment')
        //->where('tblarears_payment.division',  '=',$division)
        ->where('tblarears_payment.month',     '=', $month)
        ->where('tblarears_payment.year',      '=', $year)
        ->where('tblarears_payment.division',  '=', $request->division)
        ->where('tblarears_payment.bank',      '=', $bankName )
        //->where('tblarears_payment.bankGroup', '=',$bankGroup)
        ->orderBy('totalEmolu','DESC')
        ->orderBy('name','ASC')
        ->get();
  $totalRows= DB::table('tblarears_payment')
        //->where('tblarears_payment.division',  '=',$division)
        ->where('tblarears_payment.month',     '=', $month)
        ->where('tblarears_payment.year',      '=', $year)
        ->where('tblarears_payment.division',  '=', $request->division)
        ->where('tblarears_payment.bank',      '=', $bankName )
        //->where('tblarears_payment.bankGroup', '=',$bankGroup)
        ->count();
  if($totalRows<20)
  {
    Session::put('showTotal', "yes");
  }
  elseif ($totalRows==20)
  {
    Session::put('showTotal', "yes");
  }
  else
  {
    Session::put('showTotal', "");
  }
  Session::put('month', $month);
  Session::put('year', $year);
  Session::put('schmonth', $month." ".$year);
  Session::put('bank', $bankName );

  //$data['getSignatory'] = DB::connection('mysql2')->table('tblsignatory')->get();

$check =  $data['sign1']  = DB::table('signatory')
       ->where('year', '=', $year)
       ->where('month', '=', $month)
       ->count();
    if($check === 0)
    {
      $data['name1'] = '';
      $data['name2'] = '';
      $data['name3'] = '';
      $data['phone1'] = '';
      $data['phone2'] = '';
      $data['phone3'] = '';
    }
    else
    {
  $sign1  = DB::table('signatory')
       ->where('year', '=', $year)
       ->where('month', '=', $month)
       ->where('sequence', '=',1)
       ->first();
  $sign2  = DB::table('signatory')
       ->where('year', '=', $year)
       ->where('month', '=', $month)
       ->where('sequence', '=',2)
       ->first();
   $sign3  = DB::table('signatory')
       ->where('year', '=', $year)
       ->where('month', '=', $month)
       ->where('sequence', '=',3)
       ->first();
//dd($sign1);
      $data['name1'] = $sign1->name;
      $data['name2'] = $sign2->name;
      $data['name3'] = $sign3->name;
      $data['phone1'] = $sign1->phone;
      $data['phone2'] = $sign2->phone;
      $data['phone3'] = $sign3->phone;
     }

  return view('arearsOnly.epayment', $data);
}


public function Retrieveget(Request $request)
{
  $division = $this->division;
  $serialNo = "";
  $pageNO   = "";
  $pageNO   = $request->get('page');
  if(is_null($pageNO))
  {
    $serialNo=1;
  }
  elseif( $pageNO==1)
  {
    $serialNo=1;
  }
  else
  {
    $serialNo=(($pageNO-1)*20)+1;          }
    Session::put('serialNo', $serialNo);
    $month     = session('month');
    $year      = session('year');
    $bankID    = session('bankID');
    $bankGroup = session('bankGroup');
    $getBank   = DB::table('tblbanklist')
               ->where('bankID',$bankID)
               ->first();
    $bankName  = $getBank->bank;
    $data['epayment_detail'] = DB::table('tblarears_payment')
              //->where('tblarears_payment.division',  '=',$division)
              ->where('tblarears_payment.month',     '=', $month)
              ->where('tblarears_payment.year',      '=', $year)
              //->where('tblarears_payment.division',  '=', $division)
              ->where('tblarears_payment.bank',      '=', $bankName )
              //->where('tblarears_payment.bankGroup', '=',$bankGroup)
              ->orderBy('totalEmolu','DESC')
              ->orderBy('name','ASC')
              ->paginate(20);
    $data['epayment_total'] = DB::table('tblarears_payment')
              //->where('tblarears_payment.division',  '=',$division)
              ->where('tblarears_payment.month',     '=', $month)
              ->where('tblarears_payment.year',      '=', $year)
              //->where('tblarears_payment.division',  '=', $division)
              ->where('tblarears_payment.bank',      '=', $bankName )
              //->where('tblarears_payment.bankGroup', '=',$bankGroup)
              ->orderBy('totalEmolu','DESC')
              ->orderBy('name','ASC')
              ->get();
    $totalRows = DB::table('tblarears_payment')
              //->where('tblarears_payment.division',  '=',$division)
              ->where('tblarears_payment.month',     '=', $month)
              ->where('tblarears_payment.year',      '=', $year)
              //->where('tblarears_payment.division',  '=', $division)
              ->where('tblarears_payment.bank',      '=', $bankName )
              //->where('tblarears_payment.bankGroup', '=',$bankGroup)
              ->count();
    $max_row    = 20;
    $totalPages = ceil($totalRows/$max_row);
    if($pageNO  == $totalPages)
    {
      Session::put('showTotal', "yes");
    }
    else
    {
      Session::put('showTotal', "");
    }

    $data['getSignatory'] = DB::connection('mysql2')->table('tblsignatory')
                      ->get();

    $check =  $data['sign1']  = DB::table('signatory')
       ->where('year', '=', $year)
       ->where('month', '=', $month)
       ->count();
    if($check === 0)
    {
      $data['name1'] = '';
      $data['name2'] = '';
      $data['name3'] = '';
      $data['phone1'] = '';
      $data['phone2'] = '';
      $data['phone3'] = '';
    }
    else
    {
  $sign1  = DB::table('signatory')
       ->where('year', '=', $year)
       ->where('month', '=', $month)
       ->where('sequence', '=',1)
       ->first();
  $sign2  = DB::table('signatory')
       ->where('year', '=', $year)
       ->where('month', '=', $month)
       ->where('sequence', '=',2)
       ->first();
   $sign3  = DB::table('signatory')
       ->where('year', '=', $year)
       ->where('month', '=', $month)
       ->where('sequence', '=',3)
       ->first();

      $data['name1'] = $sign1->name;
      $data['name2'] = $sign2->name;
      $data['name3'] = $sign3->name;
      $data['phone1'] = $sign1->phone;
      $data['phone2'] = $sign2->phone;
      $data['phone3'] = $sign3->phone;
     }

    return view('arearsOnly.epayment', $data);
  }

  public function softcopyIndex()
    {
        $data['type'] = DB::table('tbladmincode')
                        ->where('determinant','!=','loan')
                        ->select('determinant', 'addressName')
                      ->orderBy('addressName', 'Asc')
                        ->get();
        $data['currentDivision'] = DB::table('tbldivision')
       ->select('divisionID', 'division')
       ->orderBy('division', 'Asc')
       ->get();
        $data['StateList'] = DB::table('tblstates')->select('StateID', 'State')->orderBy('State')->get();
        
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
          $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
          $request['division'] = $data['CourtInfo']->divisionid;
        }
        
        $data['courtDivisions']  = DB::table('tbldivision')->get();

        $data['curDivision'] = $this->curDivision(Auth::user()->id);

      return view('arearsOnly.softcopy', $data);
    }

    public function softcopy(Request $request)
    {

        $this->validate($request,
            [
            'rptType' => 'required|alpha_dash',
            'month'   => 'required|alpha',
            'year'       => 'required|numeric',
            'currentState'  => 'required_if:reportType,tax'
            ]);

        // dd($request->all());

        $reportType         = trim($request['rptType']);
        $month              = trim($request['month']);
        $year               = trim($request['year']);
        $currentState       = trim($request['currentState']);
        $addressName        = ($request['make_text']);

        if ($reportType == "tax")
        {
                // $this->validate($request,
                //  [
                //      'currentState' => 'required|alpha_dash'
                //  ]);
                //DB::enableQueryLog();

                $currentWorkingState        = ($request['currentWorkingState']);

                $st = DB::select('select fileNo,tax, netpay, callDuty, name, ' . $reportType .' as amt, bankGroup, "' . $month .' '. $year .' staff Tax" As purpose
                from tblarears_payment where month = "'.$month .'" and year = "'.$year . '" and current_state = "' .  $currentState .'"');
                //dd(DB::getQueryLog());
        }


        elseif ($reportType == "totalDeduct")
       {
          $purpose = "$month $year staff $addressName";


            //$st = DB::select('select fileNo, name, `tblarears_payment.bank`, AccNo,tax, grosspay - totalEmolu as amt, netpay, sort_code, ? as purpose FROM tblarears_payment a left join tbldivision b on a.division = b.division left join tblbanklist c on a.bank = c.bank left join tblbank d on c.bankID = d.bankID and b.divisionID = d.divisionID where month = ? and year = ? and a.division = ? order by a.bank, a.bankGroup asc', [$purpose, $month, $year, $this->division]);

           $st= DB::table('tblarears_payment')
           ->leftjoin('tbldivision','tbldivision.division','=','tblarears_payment.division')
           ->leftjoin('tblbanklist','tblbanklist.bank','=','tblarears_payment.bank')
           //->join('tblbank','tblbank.bankID','=','tblbanklist.bankID')
           ->where('tblarears_payment.month','=',$month)
           ->where('tblarears_payment.year','=',$year)
           ->where('tblarears_payment.division','=',$request['division'])
           ->select('fileNo', 'callDuty', 'name','tblarears_payment.bank','AccNo','tax',"totalDeduct as amt",'sortcode')
           ->orderBy('tblbanklist.bank')

           ->get();

       }


       else
       {
          $purpose = "$month $year staff $addressName";

            //$st = DB::select('select fileNo, name,netpay,tax,`tblarears_payment.bank`, AccNo, '.$reportType.' as amt, sort_code, ? as purpose FROM tblarears_payment a left join tbldivision b on a.division = b.division left join tblbanklist c on a.bank = c.bank left join tblbank d on c.bankID = d.bankID and b.divisionID = d.divisionID where '.$reportType.' > 0 and month = ? and year = ? order by a.bank, a.bankGroup asc', [$purpose, $month, $year]);
            //dd($st);
            $st= DB::table('tblarears_payment')
           ->leftjoin('tbldivision','tbldivision.division','=','tblarears_payment.division')
           ->leftjoin('tblbanklist','tblbanklist.bank','=','tblarears_payment.bank')
           //->join('tblbank','tblbank.bankID','=','tblbanklist.bankID')
           ->where('tblarears_payment.month','=',$month)
           ->where('tblarears_payment.year','=',$year)
           ->where('tblarears_payment.division','=',$request['division'])

           ->select('fileNo', 'callDuty', 'name','tblarears_payment.bank','AccNo','tax',"$reportType as amt",'sortcode')
           ->orderBy('tblbanklist.bank')
           ->get();
          // dd($st);
       }

       $divisionName  = DB::table('tbldivision')->where('divisionID', $request['division'])->value('division');

        $staff = json_decode(json_encode($st), true);
        //dd($staff);
        if($reportType == "tax")
        {
            $text = str_replace(' ', '_', $currentState);
        $file_name =  $text . '_' . $month . '_' . $year . '_' .$reportType . '.csv';

        }

        else
        {
          $file_name =  $divisionName . '_' . $month . '_' . $year . '_' .$reportType . '.csv';

        }
        //dd($st);


        //$file_name =  $this->division . '_' . $month . '_' . $year . '_' .$reportType . '.csv';

        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=$file_name");
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");

        # Start the ouput
        $output = fopen("php://output", "w");

         # Then loop through the rows

        if($reportType == "tax")
        {
            fputcsv($output, array('File Number', 'Name', 'Amount', 'Purpose of payment'));
        }
        else
        {
            fputcsv($output, array('File Number', 'Name', 'Bank', 'Account No.', 'Amount', 'Sort code',  'Purpose of payment'));
        }
        //dd($st);
        foreach ($st as $row)
        {
           //dd($row);
            # Add the rows to the body

            if($reportType == "tax")
            {
            $name      = $row->name;
            $fileno   = $row->fileNo;
            $amount   = $row->tax;
           // $sortcode = "\t".$row->sort_code;
            $purpose  = 'Promotion Arrears';
            $d     = array($fileno,$name,$amount,$purpose);

            }
            else
            {
            $name      = $row->name;
            $fileno   = $row->fileNo;
            $acctno   = "\t".$row->AccNo;
            $amount   = $row->amt + $row->callDuty;
            $sortcode = "\t".$row->sortcode;
            $purpose  = 'Promotion Arrears';
            $bank  = $row->bank;
            $d     = array($fileno,$name,$bank,$acctno,$amount,$sortcode,$purpose);

            }
            fputcsv($output, $d);
             // here you can change delimiter/enclosure
        }

        # Close the stream off
        fclose($output);

    }

     public function createBankSchedule()
    {
      $data['allbanklist']  = DB::table('tblper')
            // ->where('tblper.divisionID', '=', $this->divisionID)
            ->select('tblbanklist.bank', 'tblper.bankID')
            ->distinct()
            ->join('tblbanklist', 'tblper.bankID', '=', 'tblbanklist.bankID')
            ->orderBy('bank', 'Asc')
            ->get();

            $data['CourtInfo'] = $this->CourtInfo();
            if ($data['CourtInfo']->courtstatus == 0) {
              $request['court'] = $data['CourtInfo']->courtid;
            }
            if ($data['CourtInfo']->divisionstatus == 0) {
              $request['division'] = $data['CourtInfo']->divisionid;
            }
             
            $data['courtDivisions']  = DB::table('tbldivision')->get();
        
            $data['curDivision'] = $this->curDivision(Auth::user()->id);

      return view('arearsOnly.bankScheduleIndex',$data);
    }

    public function retrieveBankSchedule(Request $request)
{
 $month = trim($request->input('month'));
  $year = trim($request->input('year'));
  $bankName = trim($request->input('bankName'));
  //$bankGroup = trim($request->input('bankGroup'));


  $division = $this->division;
  $this->validate($request,[
        'month'     => 'required|regex:/^[\pL\s\-]+$/u',
        'year'      => 'required|integer',
        'bankName'  => 'required|integer',
        //'bankGroup' => 'required|integer'
  ]);
  Session::put('serialNos', 1);
  $getBank  = DB::table('tblbanklist')->where('bankID',$bankName)->first();
  $bankName = $getBank->bank;
  $data['schedule_detail'] = DB::table('tblarears_payment')
            ->join('tblbanklist', 'tblbanklist.bank', '=','tblarears_payment.bank')
            ->where('tblarears_payment.month',     '=', $month)
            ->where('tblarears_payment.year',      '=', $year)
            ->where('tblarears_payment.division',  '=', $request->division)
            ->where('tblarears_payment.bank',      '=',$bankName )
            //->where('tblarears_payment.bankGroup', '=', $bankGroup)
            ->orderBy('name','ASC')
            ->paginate(20);
 // Session::put('schmonth', $month." ".$year);
 // Session::put('date', date('d/m/Y'));
  Session::put('bank', $bankName);
   $totalRows = DB::table('tblarears_payment')
              //->join('tblbanklist', 'tblbanklist.bank', '=','tblarears_payment.bank')
            ->where('tblarears_payment.month',     '=', $month)
            ->where('tblarears_payment.year',      '=', $year)
            ->where('tblarears_payment.division',  '=', $request->division)
            ->where('tblarears_payment.bank',      '=',$bankName )
            //->where('tblarears_payment.bankGroup', '=', $bankGroup)
              ->count();

   if($totalRows<20)
  {
    Session::put('showTotals', "yes");
  }
  elseif ($totalRows==20)
  {
    Session::put('showTotals', "yes");
  }
  else
  {
    Session::put('showTotals', "");
  }

  Session::put('monthSelected', $month);
  Session::put('yearSelected', $year);
  //Session::put('schmonth', $month." ".$year);
  Session::put('bankSelected', $bankName );
  return view('arearsOnly.reportBankSchedule', $data);
}

public function retrieveSchedulePaged(Request $request)
{
  $division = $this->division;
  $serialNo = "";
  $pageNO   = "";
  $pageNO   = $request->get('page');
  if(is_null($pageNO))
  {
    $serialNo=1;
  }
  elseif( $pageNO==1)
  {
    $serialNo=1;
  }
  else
  {
    $serialNo=(($pageNO-1)*20)+1;          }
    Session::put('serialNos', $serialNo);
    $month     = session('monthSelected');
    $year      = session('yearSelected');
    $bankName    = session('bankSelected');



    $data['schedule_detail'] = DB::table('tblarears_payment')
            ->join('tblbanklist', 'tblbanklist.bank', '=','tblarears_payment.bank')
            ->where('tblarears_payment.month',     '=', $month)
            ->where('tblarears_payment.year',      '=', $year)
            //->where('tblarears_payment.division',  '=', $division)
            ->where('tblarears_payment.bank',      '=',$bankName )
            //->where('tblarears_payment.bankGroup', '=', $bankGroup)
            ->orderBy('name','ASC')
            ->paginate(20);

    $totalRows = DB::table('tblarears_payment')
              //->join('tblbanklist', 'tblbanklist.bank', '=','tblarears_payment.bank')
            ->where('tblarears_payment.month',     '=', $month)
            ->where('tblarears_payment.year',      '=', $year)
            //->where('tblarears_payment.division',  '=', $division)
            ->where('tblarears_payment.bank',      '=',$bankName )
            //->where('tblarears_payment.bankGroup', '=', $bankGroup)
              ->count();
    $max_row    = 20;
    $totalPages = ceil($totalRows/$max_row);
    if($pageNO  == $totalPages)
    {
      Session::put('showTotals', "yes");
    }
    else
    {
      Session::put('showTotals', "");
    }


    return view('arearsOnly.reportBankSchedule', $data);
  }

      public function createBankScheduleTest()
    {
      $data['allbanklist']  = DB::table('tblper')
            ->where('tblper.divisionID', '=', $this->divisionID)
            ->select('tblbanklist.bank', 'tblper.bankID')
            ->distinct()
            ->join('tblbanklist', 'tblper.bankID', '=', 'tblbanklist.bankID')
            ->orderBy('bank', 'Asc')
            ->get();
      return view('arearsOnly.bankScheduleIndexTest',$data);
    }

    public function retrieveBankScheduleTest(Request $request)
{
  $month = trim($request->input('month'));
  $year = trim($request->input('year'));
  $bankName = trim($request->input('bankName'));
  //$bankGroup = trim($request->input('bankGroup'));


  $division = $this->division;
  $this->validate($request,[
        'month'     => 'required|regex:/^[\pL\s\-]+$/u',
        'year'      => 'required|integer',
        'bankName'  => 'required|integer',
        //'bankGroup' => 'required|integer'
  ]);
  Session::put('serialNos', 1);
  $getBank  = DB::table('tblbanklist')->where('bankID',$bankName)->first();
  $bankName = $getBank->bank;
  $data['schedule_detail'] = DB::table('tblarears_payment')
            ->join('tblbanklist', 'tblbanklist.bank', '=','tblarears_payment.bank')
            ->where('tblarears_payment.month',     '=', $month)
            ->where('tblarears_payment.year',      '=', $year)
            //->where('tblarears_payment.division',  '=', $division)
            ->where('tblarears_payment.bank',      '=',$bankName )
            //->where('tblarears_payment.bankGroup', '=', $bankGroup)
            ->orderBy('name','ASC')
            ->paginate(20);
 // Session::put('schmonth', $month." ".$year);
 // Session::put('date', date('d/m/Y'));
  //Session::put('bank', $bankName ." ".$bankGroup);
   $totalRows = DB::table('tblarears_payment')
              //->join('tblbanklist', 'tblbanklist.bank', '=','tblarears_payment.bank')
            ->where('tblarears_payment.month',     '=', $month)
            ->where('tblarears_payment.year',      '=', $year)
            //->where('tblarears_payment.division',  '=', $division)
            ->where('tblarears_payment.bank',      '=',$bankName )
            //->where('tblarears_payment.bankGroup', '=', $bankGroup)
              ->count();

   if($totalRows<20)
  {
    Session::put('showTotals', "yes");
  }
  elseif ($totalRows==20)
  {
    Session::put('showTotals', "yes");
  }
  else
  {
    Session::put('showTotals', "");
  }

  Session::put('monthSelected', $month);
  Session::put('yearSelected', $year);
  //Session::put('schmonth', $month." ".$year);
  Session::put('bankSelected', $bankName );

  return view('arearsOnly.reportBankScheduleTest', $data);
}


 public function createWorking()
 {
    $data['staff'] =DB::table('tblper')->where('divisionID','=',$this->divisionID)
    ->orderBy('surname')
    ->get();
    return view('arearsOnly.workingsIndex',$data);
 }

 public function viewWorking(Request $request)
{


    $year = $request['year'];
    $mth = $request['month'];
    $fileno = $request['staff'];

    $exist = DB::table('tblarears_payment')
         ->where('fileNo','=',$fileno)
         ->where('year','=',$year)
         ->where('month','=',$mth)
         ->count();

        if($exist == 0)
        {
            //return array('msg'=>'This Staff Promotion arrears has already been computed', 'result_details'=>"Failed");
            return back()->with('err',"There is no arrears for this staff for the month of $mth $year");
        }

    $mth = $mth;
    DB::enableQueryLog();
    $result= DB::table('tblper')
    ->select('tblper.fileNo', 'name','nhf','unionDues', 'tblarears_payment.month', 'tblarears_payment.year', 'employee_type','tblbanklist.bank','tblper.bankGroup','tblarrearsonly.dueDate','tblarrearsonly.endDate','tblarrearsonly.*')
    ->join('tblarrearsonly', 'tblarrearsonly.fileno', '=','tblper.fileno')
    ->join('tblarears_payment', function($join) {
        $join->on('tblarears_payment.fileNo', '=', 'tblper.fileNo');
        $join->on('tblarears_payment.month', '=', 'tblarrearsonly.month');
        $join->on('tblarears_payment.year', '=', 'tblarrearsonly.year');
    })
    ->join('tblbanklist', 'tblbanklist.bankID', '=','tblper.bankID')
    ->where('tblper.fileno', '=', $fileno)
    ->where('tblarears_payment.year', '=', $year)
    ->where('tblarears_payment.month', '=', $mth)
    ->first();



    $data['query']=$result;
    $month = date_parse($mth);
    //dd($month);
    $month=$month['month'];
    if(empty($result))
    {
        return back()->with('err','No Arrears found for this staff');
    }
    if($result->endDate == '')
    {
    $date2 ="$year-$month-01";
    }
    else
    {
        $date2 = $result->endDate;
    }

    $date1 =$result->dueDate;
    $diff =$this->dateDiff($date2, $date1);
        //dd($diff);
    $data['month_diff'] = $diff['months'] +1;
    //dd($diff['months']);
// dd($date2);
    $data['day_diff']=$day_diff=$diff['days'];
    $data['daysOfMonth'] =$daysOfMonth= $diff['days_of_month'];

    return view('arearsOnly.workingReport',$data);
}

public function viewArrearsStaff(Request $request)
{
    // $mth = Session::get('activeMonth');
    $data['PayrollActivePeriod'] = DB::table('tblactivemonth')->first();
    $data['CourtInfo'] = $this->CourtInfo();
            if ($data['CourtInfo']->courtstatus == 0) {
              $request['court'] = $data['CourtInfo']->courtid;
            }
            if ($data['CourtInfo']->divisionstatus == 0) {
              $request['division'] = $data['CourtInfo']->divisionid;
            }
             
            $data['courtDivisions']  = DB::table('tbldivision')->get();
        
            $data['curDivision'] = $this->curDivision(Auth::user()->id);
  if (request()->isMethod('get'))
    {
    $data['staffList'] = DB::table('tblarears_payment')
    ->where('tblarears_payment.year', '=', $data['PayrollActivePeriod']->year)
    ->where('tblarears_payment.month', '=', $data['PayrollActivePeriod']->month)
    ->get();
      // dd($data['staffList']);
    }
    elseif (request()->isMethod('post'))
    {
    $request->session()->flash('yearSelected', $request['year']);
    $request->session()->flash('monthSelected', $request['month']);
    $data['year'] = $request['year'];
    $data['month'] = $request['month'];
     $data['staffList'] = DB::table('tblarears_payment')
    ->where('tblarears_payment.year', '=', $request['year'])
    ->where('tblarears_payment.month', '=', $request['month'])
    ->get();

    }
    return view('arearsOnly.list',$data);

}

public function deleteStaff($id)
{
     $staff = DB::table('tblarears_payment')->where('id','=',$id)->first();
     DB::table('tblarears_payment')->where('id','=',$id)->delete();
     DB::table('tblarrearsonly')->where('fileNo','=',$staff->fileNo)->delete();
     return redirect('/arrears-only/list-staff')->with('message','Successfully Deleted');
}

public function newScaleArrearsIndex()
{
    return view('arearsOnly.newScaleArrearsIndex');
}

public function newScaleArrearsCompute(Request $request)
{
    $totalMonths = $request['totalMonths'];
    $count = DB::table('tblnewscale_arrears')->count();
    if($count > 0)
    {
        return redirect('/newscale/arrears')->with('err','Arrears Already computed ');
    }
    $staff = DB::table('tblper')->where('staff_status','=',1)->where('employee_type','<>','CONSOLIDATED')->where('appointment_date','<','2019-12-01')->get();
    $no_staff = 0;
    foreach($staff as $list)
    {
        $newScale = DB::table('basicsalary')->where('employee_type','=',$list->employee_type)->where('grade','=',$list->grade)->where('step','=',$list->step)->first();
        $oldScale = DB::table('basicsalary07_02_2020')->where('employee_type','=',$list->employee_type)->where('grade','=',$list->grade)->where('step','=',$list->step)->first();
        $basic = ($newScale->amount - $oldScale->amount) * $totalMonths;
        DB::table('tblnewscale_arrears')->insert([
            'fileNo'               => $list->fileNo,
            'name'                 => "$list->surname $list->first_name $list->othernames",
            'grade'                => $list->grade,
            'step'                 => $list->step,
            'amount'               => $basic,
            'month_computed'       => $request['month'],
            'year_computed'        => $request['year'],
            'updated_at'           => date('Y-m-d'),

            ]);

            $no_staff++;

    }

    return redirect('/newscale/arrears')->with('msg','Successfully computed '.$no_staff.' staff arrears');

}

public function newScaleArrearsView()
{
    return view('arearsOnly.newScaleArrearsExport');
}

public function newScaleArrearsExport(Request $request)
{
     $this->validate($request,
        [

      'month'   => 'required|alpha',
      'year'       => 'required|numeric',

    ]);

       $month              = trim($request['month']);
       $year               = trim($request['year']);



            $st =  DB::table('tblnewscale_arrears')->where('year_computed','=',$year)->where('month_computed','=',$month)->select('fileNo','name','grade','step','amount')->orderBy('grade','DESC')->orderBy('step','DESC')->get();



       $staff = json_decode(json_encode($st), true);

          $file_name =  "new_scale_arrears_for " . '_' . $month . '_' . $year . '_'.'.csv';



        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=$file_name");
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");

        # Start the ouput
        $output = fopen("php://output", "w");


            fputcsv($output, array('File Number', 'Name', 'Grade', 'Step', 'Amount'));

        foreach ($staff as $row)
        {
            # Add the rows to the body
            fputcsv($output, $row); // here you can change delimiter/enclosure
        }
        # Close the stream off
        fclose($output);
}



public function peculiarArrearsIndex()
{
    return view('arearsOnly.peculiarArrearsIndex');
}

public function peculiarArrearsCompute(Request $request)
{
    $totalMonths = $request['totalMonths'];
    $staff = DB::table('tblper')->where('staff_status','=',1)->where('employee_type','<>','CONSOLIDATED')->where('employee_type','=','JUDICIAL')->get();
    $no_staff = 0;
    $count = DB::table('tblpeculiar_arrears')->count();
    if($count == 0)
    {
    foreach($staff as $list)
    {
        $newScale = DB::table('basicsalary')->where('employee_type','=',$list->employee_type)->where('grade','=',$list->grade)->where('step','=',$list->step)->first();
        $oldScale = DB::table('basicsalary_02_27_2020')->where('employee_type','=',$list->employee_type)->where('grade','=',$list->grade)->where('step','=',$list->step)->first();
        $basic = ($newScale->peculiar - $oldScale->peculiar) * $totalMonths;
        DB::table('tblpeculiar_arrears')->insert([
            'fileNo'               => $list->fileNo,
            'name'                 => "$list->surname $list->first_name $list->othernames",
            'grade'                => $list->grade,
            'step'                 => $list->step,
            'amount'               => $basic,
            'month_computed'       => $request['month'],
            'year_computed'        => $request['year'],
            'updated_at'           => date('Y-m-d'),

            ]);

            $no_staff++;

    }

    return redirect('/peculiar/arrears')->with('msg','Successfully computed '.$no_staff.' staff arrears');
    }
    else
    {

         foreach($staff as $list)
    {
        $newScale = DB::table('basicsalary_peculiar')->where('employee_type','=',$list->employee_type)->where('grade','=',$list->grade)->where('step','=',$list->step)->first();
        $oldScale = DB::table('basicsalary')->where('employee_type','=',$list->employee_type)->where('grade','=',$list->grade)->where('step','=',$list->step)->first();
        $basic = ($newScale->peculiar - $oldScale->peculiar) * $totalMonths;
        DB::table('tblpeculiar_arrears')->where('fileNo','=',$list->fileNo)->where('grade','=',$list->grade)->where('step','=',$list->step)->update([
            'fileNo'               => $list->fileNo,
            'name'                 => "$list->surname $list->first_name $list->othernames",
            'grade'                => $list->grade,
            'step'                 => $list->step,
            'amount'               => $basic,
            'month_computed'       => $request['month'],
            'year_computed'        => $request['year'],
            'updated_at'           => date('Y-m-d'),

            ]);

            $no_staff++;

    }

    return redirect('/peculiar/arrears')->with('msg','Successfully computed '.$no_staff.' staff arrears');

    }

}

public function peculiarArrearsView()
{
    return view('arearsOnly.peculiarArrearsExport');
}

public function peculiarArrearsExport(Request $request)
{
     $this->validate($request,
        [

      'month'   => 'required|alpha',
      'year'       => 'required|numeric',

    ]);

       $month              = trim($request['month']);
       $year               = trim($request['year']);



            $st =  DB::table('tblpeculiar_arrears')->where('year_computed','=',$year)->where('month_computed','=',$month)->select('fileNo','name','grade','step','amount')->orderBy('grade','DESC')->orderBy('step','DESC')->get();



       $staff = json_decode(json_encode($st), true);

          $file_name =  "peculiar_arrears_for " . '_' . $month . '_' . $year . '_'.'.csv';



        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=$file_name");
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");

        # Start the ouput
        $output = fopen("php://output", "w");


            fputcsv($output, array('File Number', 'Name', 'Grade', 'Step', 'Amount'));

        foreach ($staff as $row)
        {
            # Add the rows to the body
            fputcsv($output, $row); // here you can change delimiter/enclosure
        }
        # Close the stream off
        fclose($output);
}




}
