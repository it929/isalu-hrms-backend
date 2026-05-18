<?php

namespace App\Http\Controllers\payroll;



use App\Http\Requests;
use App\Role;
use App\User;
use Entrust;
use App\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\payroll\ParentController;
use File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class ConPecardController extends ParentController
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
    public $division;
    public $divisionID;

    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        // $this->division = $request->session()->get('division');
        // $this->divisionID = $request->session()->get('divisionID');
                return $next($request);
        });
    }

    public function create()
    {
        $data['staff'] = '';
        $data['CourtInfo'] = $this->CourtInfo();
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


        //  $data['staff'] = DB::table('tblper')
        // ->join('tblemployment_type','tblemployment_type.id','=','tblper.employee_type')
        // ->where('tblper.ID','=', $request['fileNo'])->orderBy('surname')->first();


        if ($data['CourtInfo'] && Auth::user()->is_global == 1) {
            $data['staffData'] = DB::table('tblper')
                ->orderBy('surname', 'ASC')->get();
        } else {
            $data['staffData'] = DB::table('tblper')
                // ->where('courtID', '=', $data['CourtInfo']->courtid)
                ->where('tblper.divisionID', '=', $data['curDivision']->divisionID)
                ->orderBy('surname', 'ASC')->get();
        }

        return view('payroll.pecard.conIndex', $data);
    }

    public function curDivision($userId)
    {
        $currentDivision = DB::table("users")
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'users.divisionID')
            ->where('users.id', '=', $userId)
            ->select('tbldivision.division', 'tbldivision.divisionID')
            ->first();
        return $currentDivision;
    }


    public function viewCard(Request $request)
    {
        $fileno = $request['staffName'];
        $year   = $request['year'];

        $request->session()->flash('staff', $request['staffName']);
        $request->session()->flash('yr', $request['year']);

        $data['getLevel'] = DB::table('tblpayment_consolidated')
            ->where('staffid', '=', $fileno)
            ->where('year', '=', $year)
            ->first();
        if (!$data['getLevel']) {
            return back()->with('err', 'No Record Found');
        }

        $data['details'] = DB::table('tblper')
            ->leftJoin('tblbanklist', 'tblper.bankID', '=', 'tblbanklist.bankID')
            ->select(
                'fileNo',
                'surname',
                'first_name',
                'othernames',
                'rank',
                'grade',
                'step',
                'bank',
                'AccNo',
                'appointment_date',
                'dob',
                'home_address',
                'employee_type',
                'control_no'
            )->where('tblper.ID', '=', $fileno)
            ->first();

        $arrears = DB::table('tblarrears')
            ->where('year', '=', $year)
            ->where('staffid', '=', $fileno)
            ->get();
        $arr = array();
        $app = array();
        foreach ($arrears as $key) {
            if ($key->type == 'new-appointment')
                $app[] = $key->month;
            else
                $arr[] = $key->month;
        }
        $data['year'] = $year;
        $data['arr'] = $arr;
        $data['app'] = $app;
        //dd($arr);
        //DB::enableQueryLog();
        $query = DB::table('tblper')
            //->select('month', 'basic_salary','actingAllow','arrearsBasic','tax','pension','nhf','unionDues','ugv','nicncoop','ctlsLab','ctlsFed','fedhousing','surcharge','bicycleAdv','cumEmolu','motorBasicAll','employee_type','phoneCharges','pa_deduct','totalDeduct','netpay','grosspay','totalEmolu','callDuty','hazard')
            ->join('tblpayment_consolidated', 'tblpayment_consolidated.staffid', '=', 'tblper.ID')
            ->where('tblper.ID', '=', $fileno)
            ->where('tblpayment_consolidated.year', '=', $year)
            ->get();
        //dd(DB::getQueryLog());
        //dd($query);
        $result = array();
        foreach ($query as  $value) {
            $q1 = DB::table('tblotherEarningDeduction')->where('staffid', '=', $value->staffid)->where('month', '=', $value->month)->where('year', '=', $year)->where('CVID', '=', 15)->first();

            $q2 = DB::table('tblotherEarningDeduction')->where('staffid', '=', $value->staffid)->where('month', '=', $value->month)->where('year', '=', $year)->where('CVID', '=', 16)->first();
            $q3 = DB::table('tblotherEarningDeduction')->where('staffid', '=', $value->staffid)->where('month', '=', $value->month)->where('year', '=', $year)->where('CVID', '=', 18)->first();
            $q4 = DB::table('tblotherEarningDeduction')->where('staffid', '=', $value->staffid)->where('month', '=', $value->month)->where('year', '=', $year)->where('CVID', '=', 13)->first();
            $hazard = DB::table('tblotherEarningDeduction')->where('staffid', '=', $value->staffid)->where('month', '=', $value->month)->where('year', '=', $year)->where('CVID', '=', 4)->first();
            $callDuty = DB::table('tblotherEarningDeduction')->where('staffid', '=', $value->staffid)->where('month', '=', $value->month)->where('year', '=', $year)->where('CVID', '=', 22)->first();

            $alhisanSaving = DB::table('tblotherEarningDeduction')->where('staffid', '=', $value->staffid)->where('month', '=', $value->month)->where('year', '=', $year)->where('CVID', '=', 31)->first();
            $alhisanLoan = DB::table('tblotherEarningDeduction')->where('staffid', '=', $value->staffid)->where('month', '=', $value->month)->where('year', '=', $year)->where('CVID', '=', 33)->first();

            $month = $value->month;
            $result[$month]['Bs'] = $value->Bs;
            //	var_dump($value);
            //$result[$month]['actingAllow'] = $value->actingAllow;
            $result[$month]['AEarn'] = $value->AEarn; //arrearsBasic
            $result[$month]['OEarn'] = $value->OEarn;
            $result[$month]['TAX'] = $value->TAX;
            $result[$month]['PEN'] = $value->PEN;
            $result[$month]['NHF'] = $value->NHF;
            $result[$month]['UD'] = $value->UD;
            //$result[$month]['ugv'] = $value->ugv;
            $result[$month]['HA'] = $value->HA;
            $result[$month]['PEC'] = $value->PEC;
            $result[$month]['PECFG'] = $value->PECFG;
            $result[$month]['ML'] = $value->ML;
            $result[$month]['TR'] = $value->TR;
            $result[$month]['FUR'] = $value->FUR;
            $result[$month]['LEAV'] = $value->LEAV;
            $result[$month]['TD'] = $value->TD;
            $result[$month]['NetPay'] = $value->NetPay;
            $result[$month]['TEarn'] = $value->TEarn;
            if ($q1 != '') {
                $result[$month]['coopSaving'] = $q1->amount;
            } else {
                $result[$month]['coopSaving'] = 0;
            }
            if ($q2 != '') {
                $result[$month]['coopLoan'] = $q2->amount;
            } else {
                $result[$month]['coopLoan'] = 0;
            }
            if ($q3 != '') {
                $result[$month]['salAdvance'] = $q3->amount;
            } else {
                $result[$month]['salAdvance'] = 0;
            }
            if ($q4 != '') {
                $result[$month]['overTime'] = $q4->amount;
            } else {
                $result[$month]['overTime'] = 0;
            }

            if ($hazard != '') {
                $result[$month]['hazard'] = $hazard->amount;
            } else {
                $result[$month]['hazard'] = 0;
            }

            if ($callDuty != '') {
                $result[$month]['callDuty'] = $callDuty->amount;
            } else {
                $result[$month]['callDuty'] = 0;
            }
            if ($alhisanSaving != '') {
                $result[$month]['alhisanSaving'] = $alhisanSaving->amount;
            } else {
                $result[$month]['alhisanSaving'] = 0;
            }
            if ($alhisanLoan != '') {
                $result[$month]['alhisanLoan'] = $alhisanLoan->amount;
            } else {
                $result[$month]['alhisanLoan'] = 0;
            }
            //var_dump($result);
        }
        $fullmonth = array("JANUARY", "FEBRUARY", "MARCH", "APRIL", "MAY", "JUNE", "JULY", "AUGUST", "SEPTEMBER", "OCTOBER", "NOVEMBER", "DECEMBER");
        $rowcount = 0;
        $empty = 0.0;
        for ($row = 0; $row <= 11; $row++) {
            $currentmonth = $fullmonth[$row];
            if (!isset($result[$currentmonth]['Bs'])) {
                $result[$currentmonth]['Bs'] = $empty;
                $result[$currentmonth]['AEarn'] = $empty;
                $result[$currentmonth]['OEarn'] = $empty;
                $result[$currentmonth]['TAX'] = $empty;
                $result[$currentmonth]['PEN'] = $empty;
                $result[$currentmonth]['NHF'] = $empty;
                $result[$currentmonth]['UD'] = $empty;
                $result[$currentmonth]['HA'] = $empty;
                $result[$currentmonth]['ML'] = $empty;
                $result[$currentmonth]['TR'] = $empty;
                $result[$currentmonth]['FUR'] = $empty;
                $result[$currentmonth]['LEAV'] = $empty;
                $result[$currentmonth]['TD'] = $empty;
                $result[$currentmonth]['PEC'] = $empty;
                $result[$currentmonth]['PECFG'] = $empty;
                $result[$currentmonth]['NetPay'] = $empty;
                $result[$currentmonth]['TEarn'] = $empty;

                $result[$currentmonth]['coopSaving'] = $empty;
                $result[$currentmonth]['coopLoan']   = $empty;
                $result[$currentmonth]['salAdvance'] = $empty;
                $result[$currentmonth]['overTime']   = $empty;

                $result[$currentmonth]['hazard']     = $empty;
                $result[$currentmonth]['callDuty']   = $empty;

                $result[$currentmonth]['alhisanSaving']     = $empty;
                $result[$currentmonth]['alhisanLoan']   = $empty;
            }
        }
        $imageNewName        = $fileno . '.jpg';
        $path                = base_path() . '/public/passport/';
        if (File::exists(base_path() . '/public/passport/' . $imageNewName)) //check folder
        {
            $user_picture = $imageNewName;
        } else {
            $user_picture =  '0.png';
        }
        $data['image'] = $user_picture;
        $data['result'] = $result;

        //=================START==================

        $getDynamicData = $this->DynamicPECardReport($fileno, $year, $month);
        $data['getStaffMonthEarnAmount']        = $getDynamicData['getStaffMonthEarnAmount'];
        $data['getStaffMonthDeductionAmount']   = $getDynamicData['getStaffMonthDeductionAmount'];
        $data['staffEarnElement']               = $getDynamicData['staffEarnElement'];
        $data['staffDeductionElement']          = $getDynamicData['staffDeductionElement'];
        $data['monthList']                      = $getDynamicData['monthList'];
        //====================END==================


        return view('payroll.pecard.conPeReport', $data);
    }

    public function DynamicPECardReport($fileno = null, $year = null, $month = null)
    {
        //=================START==================
        //Get all PECard table dynamic header
        $getStaffMonthEarn = [];
        $getStaffMonthDeduction = [];
        $data['monthList'] = [
            1 => 'JANUARY',
            2 => 'FEBRUARY',
            3 => 'MARCH',
            4 => 'APRIL',
            5 => 'MAY',
            6 => 'JUNE',
            7 => 'JULY',
            8 => 'AUGUST',
            9 => 'SEPTEMBER',
            10 => 'OCTOBER',
            11 => 'NOVEMBER',
            12 => 'DECEMBER',
        ];
        //Get all staff Earns and Deductions
        $data['staffEarnElement'] = DB::table('tblcvSetup')
            ->leftJoin('tblotherEarningDeduction', 'tblotherEarningDeduction.CVID', '=', 'tblcvSetup.ID')
            ->where('tblotherEarningDeduction.staffid', $fileno)
            ->where('tblotherEarningDeduction.year', $year)
            ->where('tblcvSetup.particularID', 1)
            ->orderBy('tblcvSetup.rank')
            ->groupBy('tblotherEarningDeduction.CVID')
            ->select('description', 'tblotherEarningDeduction.CVID', 'tblcvSetup.ID as cvsetupID', 'tblotherEarningDeduction.ID', 'rank', 'tblcvSetup.particularID', 'tblotherEarningDeduction.staffid')
            ->get();

        $data['staffDeductionElement'] = DB::table('tblcvSetup')
            ->leftJoin('tblotherEarningDeduction', 'tblotherEarningDeduction.CVID', '=', 'tblcvSetup.ID')
            ->where('tblotherEarningDeduction.staffid', $fileno)
            ->where('tblotherEarningDeduction.year', $year)
            ->where('tblcvSetup.particularID', 2)
            ->groupBy('tblotherEarningDeduction.CVID')
            ->orderBy('tblcvSetup.rank')
            ->select('description', 'tblotherEarningDeduction.CVID', 'tblcvSetup.ID as cvsetupID', 'rank', 'tblotherEarningDeduction.particularID', 'tblotherEarningDeduction.staffid')
            ->get();

        foreach ($data['monthList'] as $key => $month) {
            foreach ($data['staffEarnElement'] as $key => $staffCV1) {
                $getStaffMonthEarn[$month][$staffCV1->CVID] = DB::table('tblotherEarningDeduction')
                    ->where('tblotherEarningDeduction.month', $month)
                    ->where('tblotherEarningDeduction.year', $year)
                    ->where('tblotherEarningDeduction.staffid', $fileno)
                    ->where('tblotherEarningDeduction.particularID', 1)
                    ->where('tblotherEarningDeduction.CVID', $staffCV1->CVID)
                    ->first([DB::raw("SUM(tblotherEarningDeduction.amount) as staffEarning")]);
            }

            foreach ($data['staffDeductionElement'] as $key => $staffCV2) {
                $getStaffMonthDeduction[$month][$staffCV2->CVID] = DB::table('tblotherEarningDeduction')
                    ->where('tblotherEarningDeduction.month', $month)
                    ->where('tblotherEarningDeduction.year', $year)
                    ->where('tblotherEarningDeduction.staffid', $fileno)
                    ->where('tblotherEarningDeduction.particularID', 2)
                    ->where('tblotherEarningDeduction.CVID', $staffCV2->CVID)
                    ->first([DB::raw("SUM(tblotherEarningDeduction.amount) as staffDeduction")]);
            }
        }

        $data['getStaffMonthEarnAmount']        = $getStaffMonthEarn;
        $data['getStaffMonthDeductionAmount']   = $getStaffMonthDeduction;
        //====================END==================
        return $data;
    }

    public function getPecard($fileno, $year)
    {
        //$fileno = $request['staffName'];
        //$year   = $request['year'];

        Session::flash('staff', $fileno);
        Session::flash('yr', $year);


        $data['getLevel'] = DB::table('tblpayment_consolidated')
            ->where('staffid', '=', $fileno)
            ->where('year', '=', $year)
            ->first();
        if (!$data['getLevel']) {
            return back()->with('err', 'No Record Found');
        }

        $data['details'] = DB::table('tblper')
            ->leftJoin('tblbanklist', 'tblper.bankID', '=', 'tblbanklist.bankID')
            ->select(
                'fileNo',
                'surname',
                'first_name',
                'othernames',
                'rank',
                'grade',
                'step',
                'bank',
                'AccNo',
                'appointment_date',
                'dob',
                'home_address',
                'employee_type',
                'control_no'
            )->where('tblper.ID', '=', $fileno)
            ->first();

        $arrears = DB::table('tblarrears')
            ->where('year', '=', $year)
            ->where('staffid', '=', $fileno)
            ->get();
        $arr = array();
        $app = array();
        foreach ($arrears as $key) {
            if ($key->type == 'new-appointment')
                $app[] = $key->month;
            else
                $arr[] = $key->month;
        }
        $data['year'] = $year;
        $data['arr'] = $arr;
        $data['app'] = $app;
        //dd($arr);
        //DB::enableQueryLog();
        $query = DB::table('tblper')
            //->select('month', 'basic_salary','actingAllow','arrearsBasic','tax','pension','nhf','unionDues','ugv','nicncoop','ctlsLab','ctlsFed','fedhousing','surcharge','bicycleAdv','cumEmolu','motorBasicAll','employee_type','phoneCharges','pa_deduct','totalDeduct','netpay','grosspay','totalEmolu','callDuty','hazard')
            ->join('tblpayment_consolidated', 'tblpayment_consolidated.staffid', '=', 'tblper.ID')
            ->where('tblper.ID', '=', $fileno)
            ->where('tblpayment_consolidated.year', '=', $year)
            ->get();
        //dd(DB::getQueryLog());
        //dd($query);
        $result = array();
        foreach ($query as  $value) {
            $q1 = DB::table('tblotherEarningDeduction')->where('staffid', '=', $value->staffid)->where('month', '=', $value->month)->where('year', '=', $year)->where('CVID', '=', 15)->first();

            $q2 = DB::table('tblotherEarningDeduction')->where('staffid', '=', $value->staffid)->where('month', '=', $value->month)->where('year', '=', $year)->where('CVID', '=', 16)->first();
            $q3 = DB::table('tblotherEarningDeduction')->where('staffid', '=', $value->staffid)->where('month', '=', $value->month)->where('year', '=', $year)->where('CVID', '=', 18)->first();
            $q4 = DB::table('tblotherEarningDeduction')->where('staffid', '=', $value->staffid)->where('month', '=', $value->month)->where('year', '=', $year)->where('CVID', '=', 13)->first();
            $hazard = DB::table('tblotherEarningDeduction')->where('staffid', '=', $value->staffid)->where('month', '=', $value->month)->where('year', '=', $year)->where('CVID', '=', 4)->first();
            $callDuty = DB::table('tblotherEarningDeduction')->where('staffid', '=', $value->staffid)->where('month', '=', $value->month)->where('year', '=', $year)->where('CVID', '=', 22)->first();

            $alhisanSaving = DB::table('tblotherEarningDeduction')->where('staffid', '=', $value->staffid)->where('month', '=', $value->month)->where('year', '=', $year)->where('CVID', '=', 31)->first();
            $alhisanLoan = DB::table('tblotherEarningDeduction')->where('staffid', '=', $value->staffid)->where('month', '=', $value->month)->where('year', '=', $year)->where('CVID', '=', 33)->first();


            $month = $value->month;
            $result[$month]['Bs'] = $value->Bs;
            //	var_dump($value);
            //$result[$month]['actingAllow'] = $value->actingAllow;
            $result[$month]['AEarn'] = $value->AEarn; //arrearsBasic
            $result[$month]['OEarn'] = $value->OEarn;
            $result[$month]['TAX'] = $value->TAX;
            $result[$month]['PEN'] = $value->PEN;
            $result[$month]['NHF'] = $value->NHF;
            $result[$month]['UD'] = $value->UD;
            //$result[$month]['ugv'] = $value->ugv;
            $result[$month]['HA'] = $value->HA;
            $result[$month]['PEC'] = $value->PEC;
            $result[$month]['PECFG'] = $value->PECFG;
            $result[$month]['ML'] = $value->ML;
            $result[$month]['TR'] = $value->TR;
            $result[$month]['FUR'] = $value->FUR;
            $result[$month]['LEAV'] = $value->LEAV;
            $result[$month]['TD'] = $value->TD;
            $result[$month]['NetPay'] = $value->NetPay;
            $result[$month]['TEarn'] = $value->TEarn;
            if ($q1 != '') {
                $result[$month]['coopSaving'] = $q1->amount;
            } else {
                $result[$month]['coopSaving'] = 0;
            }
            if ($q2 != '') {
                $result[$month]['coopLoan'] = $q2->amount;
            } else {
                $result[$month]['coopLoan'] = 0;
            }
            if ($q3 != '') {
                $result[$month]['salAdvance'] = $q3->amount;
            } else {
                $result[$month]['salAdvance'] = 0;
            }
            if ($q4 != '') {
                $result[$month]['overTime'] = $q4->amount;
            } else {
                $result[$month]['overTime'] = 0;
            }

            if ($hazard != '') {
                $result[$month]['hazard'] = $hazard->amount;
            } else {
                $result[$month]['hazard'] = 0;
            }

            if ($callDuty != '') {
                $result[$month]['callDuty'] = $callDuty->amount;
            } else {
                $result[$month]['callDuty'] = 0;
            }

            if ($alhisanSaving != '') {
                $result[$month]['alhisanSaving'] = $alhisanSaving->amount;
            } else {
                $result[$month]['alhisanSaving'] = 0;
            }
            if ($alhisanLoan != '') {
                $result[$month]['alhisanLoan'] = $alhisanLoan->amount;
            } else {
                $result[$month]['alhisanLoan'] = 0;
            }
            //var_dump($result);
        }
        $fullmonth = array("JANUARY", "FEBRUARY", "MARCH", "APRIL", "MAY", "JUNE", "JULY", "AUGUST", "SEPTEMBER", "OCTOBER", "NOVEMBER", "DECEMBER");
        $rowcount = 0;
        $empty = 0.0;
        for ($row = 0; $row <= 11; $row++) {
            $currentmonth = $fullmonth[$row];
            if (!isset($result[$currentmonth]['Bs'])) {
                $result[$currentmonth]['Bs'] = $empty;
                $result[$currentmonth]['AEarn'] = $empty;
                $result[$currentmonth]['OEarn'] = $empty;
                $result[$currentmonth]['TAX'] = $empty;
                $result[$currentmonth]['PEN'] = $empty;
                $result[$currentmonth]['NHF'] = $empty;
                $result[$currentmonth]['UD'] = $empty;
                $result[$currentmonth]['HA'] = $empty;
                $result[$currentmonth]['ML'] = $empty;
                $result[$currentmonth]['TR'] = $empty;
                $result[$currentmonth]['FUR'] = $empty;
                $result[$currentmonth]['LEAV'] = $empty;
                $result[$currentmonth]['TD'] = $empty;
                $result[$currentmonth]['PEC'] = $empty;
                $result[$currentmonth]['PECFG'] = $empty;
                $result[$currentmonth]['NetPay'] = $empty;
                $result[$currentmonth]['TEarn'] = $empty;

                $result[$currentmonth]['coopSaving'] = $empty;
                $result[$currentmonth]['coopLoan']   = $empty;
                $result[$currentmonth]['salAdvance'] = $empty;
                $result[$currentmonth]['overTime']   = $empty;

                $result[$currentmonth]['hazard']     = $empty;
                $result[$currentmonth]['callDuty']   = $empty;

                $result[$currentmonth]['alhisanSaving']     = $empty;
                $result[$currentmonth]['alhisanLoan']   = $empty;
            }
        }
        $imageNewName        = $fileno . '.jpg';
        $path                = base_path() . '/public/passport/';
        if (File::exists(base_path() . '/public/passport/' . $imageNewName)) //check folder
        {
            $user_picture = $imageNewName;
        } else {
            $user_picture =  '0.png';
        }
        $data['image'] = $user_picture;
        $data['result'] = $result;

        //=================START==================

        $getDynamicData = $this->DynamicPECardReport($fileno, $year, $month);
        $data['getStaffMonthEarnAmount']        = $getDynamicData['getStaffMonthEarnAmount'];
        $data['getStaffMonthDeductionAmount']   = $getDynamicData['getStaffMonthDeductionAmount'];
        $data['staffEarnElement']               = $getDynamicData['staffEarnElement'];
        $data['staffDeductionElement']          = $getDynamicData['staffDeductionElement'];
        $data['monthList']                      = $getDynamicData['monthList'];
        //====================END==================

        return view('payroll/pecard/conPeReport', $data);
    }


    function dateDiff($date2, $date1)
    {
        list($year2, $mth2, $day2) = explode("-", $date2);
        list($year1, $mth1, $day1) = explode("-", $date1);
        if ($year1 > $year2) die('Invalid Input - dates do not match');
        $days_month = 0;
        $day_diff = 0;
        if ($year2 == $year1)
            $mth_diff = $mth2 - $mth1;
        else {
            $yr_diff = $year2 - $year1;
            $mth_diff = (12 * $yr_diff) - $mth1 + $mth2;
        }
        if ($day1 > 1) {
            $mth_diff--;
            $days_month = cal_days_in_month(CAL_GREGORIAN, $mth1, $year1);
            $day_diff = $days_month - $day1 + 1;
        }
        $result = array('months' => $mth_diff, 'days' => $day_diff, 'days_of_month' => $days_month);
        return ($result);
    }
}
