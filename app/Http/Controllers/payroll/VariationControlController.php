<?php

namespace App\Http\Controllers\payroll;



use App\Http\Requests;
use App\Role;
use App\User;
use Entrust;
use App\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use DateTime;
use App\Http\Controllers\payroll\ParentController;

class VariationControlController extends ParentController
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
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        //  $this->division = $request->session()->get('division');
        //  $this->divisionID = $request->session()->get('divisionID');
                return $next($request);
        });
    }

    public function index()
    {
        $data['courts'] =  DB::table('tbl_court')->get();
        $courtSessionId = session('anycourt');
        /*$allbanklist  = DB::table('tblper')
  			 ->where('tblper.divisionID', '=', $this->divisionID)
  			 ->select('tblbanklist.bank', 'tblper.bankID')
  			 ->distinct()
  			 ->join('tblbanklist', 'tblper.bankID', '=', 'tblbanklist.bankID')
  			 ->orderBy('bank', 'Asc')
  			 ->get();*/
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

        $data['allbanklist']  = DB::table('tblbank')
            ->where('tblbank.courtID', '=', $courtSessionId)
            ->distinct()
            ->join('tblbanklist', 'tblbank.bankID', '=', 'tblbanklist.bankID')
            ->orderBy('tblbanklist.bank', 'Asc')
            ->get();
        //dd(count($data['CourtInfo']));
        if (count($data['CourtInfo']) > 0) {

            $data['allbanklist']  = DB::table('tblbanklist')
                //->where('tblbank.courtID', '=', $data['CourtInfo']->courtid)
                ->orderBy('tblbanklist.bank', 'Asc')
                ->get();
        }

        return view('payroll.variationControl.indexVariation', $data);
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

    public function getBank(Request $request)
    {
        $court =  $request['courtID'];
        $allbanklist  = DB::table('tblbank')
            ->where('tblbank.courtID', '=', $court)
            //->distinct()
            ->join('tblbanklist', 'tblbank.bankID', '=', 'tblbanklist.bankID')
            ->orderBy('tblbanklist.bank', 'Asc')
            ->get();
        return response()->json($allbanklist);
    }

    public function load(Request $request)
    {
        $month             = trim($request->input('month'));
        $year              = trim($request->input('year'));
        $court             = trim($request->input('court'));
        $division          = trim($request->input('division'));



        $bankID    = trim($request->input('bankName'));
        $bankGroup = trim($request->input('bankGroup'));


        Session::put('schmonth', $month . " " . $year);
        $activeMonth = DB::table('tblactivemonth')->where('courtID', '=', 9)->first();
        $month_number = date("n", strtotime($month));
        $number = cal_days_in_month(CAL_GREGORIAN, $month_number, $year);
        //dd($number);
        if ($month_number < 10) {
            $init = 0;
        } else {
            $init = '';
        }

        $data['activeDate'] = "$activeMonth->year-$init$month_number-$number";

        // dd($data['activeDate']);


        if ($bankID == '') {
            $data['bank'] = '';
        }
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        //dd($bankGroup);

        // $division  = $this->division;
        $this->validate($request, [
            'month'     => 'required|regex:/^[\pL\s\-]+$/u',
            'year'      => 'required|integer',
            //'bankName'  => 'required|integer',
            //'bankGroup' => 'required|integer'
        ]);
        if ($bankID != '') {
            $getBank  = DB::table('tblbanklist')
                ->where('bankID', $bankID)
                ->first();

            $bankName = $getBank->bank;
            Session::flash('bank', $bankName);
        }

        if ($division == '' &&  $bankGroup != '' &&  $bankID != '') {
            $data['courtname']  = DB::table('tbl_court')
                ->where('id', '=', $court)
                ->first();
            $data['courtDivisions'] = '';
            $data['payroll_detail'] = DB::table('tblpayment_consolidated')
                ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                ->select('*', 'tblper.grade as staffGrade', 'tblper.step as staffStep', 'tblper.employee_type as emptype')
                ->where('tblpayment_consolidated.month',     '=', $month)
                ->where('tblpayment_consolidated.year',      '=', $year)
                ->where('tblpayment_consolidated.courtID',  '=', $court)
                ->where('tblpayment_consolidated.bank',      '=', $bankID)
                ->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
                ->where('tblpayment_consolidated.variation_view', '=', 1)
                ->where('tblpayment_consolidated.rank', '!=', 2)
                ->orderBy('tblpayment_consolidated.rank', 'DESC')
                ->orderBy('tblpayment_consolidated.grade', 'DESC')
                ->orderBy('tblpayment_consolidated.step', 'DESC')
                ->get();
            return view('payroll.variationControl.detailVariation', $data);
        } elseif ($division != '' &&  $bankGroup != '' &&  $bankID != '') {

            $data['courtname'] = '';
            $data['courtDivisions']  = DB::table('tbl_court')
                ->join('tbldivision', 'tbldivision.courtID', '=', 'tbl_court.id')
                ->where('tbl_court.id', '=', $court)
                ->where('tbldivision.divisionID', '=', $division)
                ->first();
            $data['payroll_detail'] = DB::table('tblpayment_consolidated')
                ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                ->select('*', 'tblper.grade as staffGrade', 'tblper.step as staffStep', 'tblper.employee_type as emptype')
                ->where('tblpayment_consolidated.month',     '=', $month)
                ->where('tblpayment_consolidated.year',      '=', $year)
                ->where('tblpayment_consolidated.divisionID',  '=', $division)
                ->where('tblpayment_consolidated.courtID',  '=', $court)
                ->where('tblpayment_consolidated.bank',      '=', $bankID)
                ->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
                ->where('tblpayment_consolidated.variation_view', '=', 1)
                ->where('tblpayment_consolidated.rank', '!=', 2)
                //->select('*', 'tblper.ID')
                ->orderBy('tblpayment_consolidated.rank', 'DESC')
                ->orderBy('tblpayment_consolidated.grade', 'DESC')
                ->orderBy('tblpayment_consolidated.step', 'DESC')
                ->get();
            return view('payroll.variationControl.detailVariation', $data);
        }
        if ($bankGroup == '' &&  $bankID != '') {
            if ($division == '') {
                $data['courtname']  = DB::table('tbl_court')
                    ->where('id', '=', $court)
                    ->first();
                $data['courtDivisions'] = '';
                $data['payroll_detail'] = DB::table('tblpayment_consolidated')
                    ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                    ->select('*', 'tblper.grade as staffGrade', 'tblper.step as staffStep', 'tblper.employee_type as emptype')
                    ->where('tblpayment_consolidated.month',     '=', $month)
                    ->where('tblpayment_consolidated.year',      '=', $year)
                    ->where('tblpayment_consolidated.courtID',  '=', $court)
                    ->where('tblpayment_consolidated.bank',      '=', $bankID)
                    //->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
                    ->where('tblpayment_consolidated.variation_view', '=', 1)
                    ->orderBy('tblpayment_consolidated.rank', 'DESC')
                    ->orderBy('tblpayment_consolidated.grade', 'DESC')
                    ->orderBy('tblpayment_consolidated.step', 'DESC')
                    ->get();
                return view('payroll.variationControl.detailVariation', $data);
            } else {

                $data['courtname'] = '';
                $data['courtDivisions']  = DB::table('tbl_court')
                    ->join('tbldivision', 'tbldivision.courtID', '=', 'tbl_court.id')
                    ->where('tbl_court.id', '=', $court)
                    ->where('tbldivision.divisionID', '=', $division)
                    ->first();
                $data['payroll_detail'] = DB::table('tblpayment_consolidated')
                    ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                    ->select('*', 'tblper.grade as staffGrade', 'tblper.step as staffStep', 'tblper.employee_type as emptype')
                    ->where('tblpayment_consolidated.month',     '=', $month)
                    ->where('tblpayment_consolidated.year',      '=', $year)
                    ->where('tblpayment_consolidated.divisionID',  '=', $division)
                    ->where('tblpayment_consolidated.courtID',  '=', $court)
                    ->where('tblpayment_consolidated.bank',      '=', $bankID)
                    //->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
                    ->where('tblpayment_consolidated.variation_view', '=', 1)
                    ->where('tblpayment_consolidated.rank', '!=', 2)
                    ->orderBy('tblpayment_consolidated.rank', 'DESC')
                    ->orderBy('tblpayment_consolidated.grade', 'DESC')
                    ->orderBy('tblpayment_consolidated.step', 'DESC')
                    ->get();
                return view('payroll.variationControl.detailVariation', $data);
            }
        }

        if ($bankGroup == '' && $bankID == '') {
            if ($division == '') {
                $data['courtname']  = DB::table('tbl_court')
                    ->where('id', '=', $court)
                    ->first();
                $data['courtDivisions'] = '';
                $data['payroll_detail'] = DB::table('tblpayment_consolidated')
                    ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                    ->select('*', 'tblper.grade as staffGrade', 'tblper.step as staffStep', 'tblper.employee_type as emptype')
                    ->where('tblpayment_consolidated.month',     '=', $month)
                    ->where('tblpayment_consolidated.year',      '=', $year)
                    ->where('tblpayment_consolidated.courtID',  '=', $court)
                    //->where('tblpayment_consolidated.bank',      '=',$bankName )
                    //->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
                    ->where('tblpayment_consolidated.variation_view', '=', 1)
                    ->where('tblpayment_consolidated.rank', '!=', 2)
                    ->orderBy('tblpayment_consolidated.rank', 'DESC')
                    ->orderBy('tblpayment_consolidated.grade', 'DESC')
                    ->orderBy('tblpayment_consolidated.step', 'DESC')
                    ->get();
                return view('payroll.variationControl.detailVariation', $data);
            } else {

                $data['courtname'] = '';
                $data['courtDivisions']  = DB::table('tbl_court')
                    ->join('tbldivision', 'tbldivision.courtID', '=', 'tbl_court.id')
                    ->where('tbl_court.id', '=', $court)
                    ->where('tbldivision.divisionID', '=', $division)
                    ->first();
                $data['payroll_detail'] = DB::table('tblpayment_consolidated')
                    ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                    ->select('*', 'tblper.grade as staffGrade', 'tblper.step as staffStep', 'tblper.employee_type as emptype')
                    ->where('tblpayment_consolidated.month',     '=', $month)
                    ->where('tblpayment_consolidated.year',      '=', $year)
                    ->where('tblpayment_consolidated.divisionID',  '=', $division)
                    ->where('tblpayment_consolidated.courtID',  '=', $court)
                    ->where('tblpayment_consolidated.rank', '!=', 2)
                    //->where('tblpayment_consolidated.bank',      '=',$bankID )
                    //->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
                    ->where('tblpayment_consolidated.variation_view', '=', 1)
                    ->orderBy('tblpayment_consolidated.rank', 'DESC')
                    ->orderBy('tblpayment_consolidated.grade', 'DESC')
                    ->orderBy('tblpayment_consolidated.step', 'DESC')
                    ->get();
                return view('payroll.variationControl.detailVariation', $data);
            }
        }


        //dd($data['court'] );


        //dd($data['payroll_detail']);


        return view('payroll.variationControl.detailVariation', $data);
    }


    ///payroll report for variation control
    public function newPayrollIndex()
    {
        $data['courts'] =  DB::table('tbl_court')->get();
        $courtSessionId = session('anycourt');
        /*$allbanklist  = DB::table('tblper')
  			 ->where('tblper.divisionID', '=', $this->divisionID)
  			 ->select('tblbanklist.bank', 'tblper.bankID')
  			 ->distinct()
  			 ->join('tblbanklist', 'tblper.bankID', '=', 'tblbanklist.bankID')
  			 ->orderBy('bank', 'Asc')
  			 ->get();*/
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }


        $data['courtDivisions']  = DB::table('tbldivision')
            ->where('courtID', '=', $courtSessionId)
            ->get();

        $data['allbanklist']  = DB::table('tblbank')
            ->where('tblbank.courtID', '=', $courtSessionId)
            ->distinct()
            ->join('tblbanklist', 'tblbank.bankID', '=', 'tblbanklist.bankID')
            ->orderBy('tblbanklist.bank', 'Asc')
            ->get();

        if (count($data['CourtInfo']) > 0) {

            $data['allbanklist']  = DB::table('tblbanklist')
                //->where('tblbank.courtID', '=', $data['CourtInfo']->courtid)
                ->orderBy('tblbanklist.bank', 'Asc')
                ->get();
        }

        return view('payroll.variationControl.newPayrolIndex', $data);
    }


    public function newPayrollReport(Request $request)
    {
        $month             = trim($request->input('month'));
        $year              = trim($request->input('year'));
        $court             = trim($request->input('court'));
        $division          = trim($request->input('division'));

        $data['alhisan'] = DB::table('tblotherEarningDeduction')->where('month', '=', $month)->where('year', '=', $year)->where('CVID', '=', 31)->count();
        //dd($data['alhisan']) ;

        $bankID    = trim($request->input('bankName'));
        $bankGroup = trim($request->input('bankGroup'));
        $data['year'] = $year;
        $data['month'] = $month;



        Session::put('schmonth', $month . " " . $year);

        $data['count_sot'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.month',     '=', $month)
            ->where('tblpayment_consolidated.year',      '=', $year)
            ->where('tblpayment_consolidated.divisionID',  '=', $division)
            ->where('tblpayment_consolidated.courtID',  '=', $court)
            ->where('tblpayment_consolidated.SOT', '>', 0)
            ->where('tblpayment_consolidated.rank', '!=', 2)
            ->count();

        if ($bankID == '') {
            $data['bank'] = '';
        }
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }
        $this->validate($request, [
            'month'     => 'required|regex:/^[\pL\s\-]+$/u',
            'year'      => 'required|integer',
        ]);
        if ($bankID != '') {
            $getBank  = DB::table('tblbanklist')
                ->where('bankID', $bankID)
                ->first();

            $bankName = $getBank->bank;
            Session::flash('bank', $bankName);
        }

        $data['courtname']  = DB::table('tbl_court')
            ->where('id', '=', $court)
            ->first();
        $data['courtDivisions'] = '';


        if ($bankID == '') {
            $data['payroll_detail'] = DB::table('tblpayment_consolidated')
                ->where('tblpayment_consolidated.month',     '=', $month)
                ->where('tblpayment_consolidated.year',      '=', $year)
                //->where('tblpayment_consolidated.courtID',  '=', $court)
                ->where('tblpayment_consolidated.rank', '!=', 2)
                ->where('tblpayment_consolidated.variation_view', '=', 1)
                ->orderBy('rank', 'DESC')
                ->orderBy('grade', 'DESC')
                ->orderBy('step', 'DESC')
                ->get();
            foreach ($data['payroll_detail'] as $key => $value) {
                $lis = (array) $value;
                $lis['hazard'] = $this->OtherParameter($value->staffid, $year, $month, 4);
                $lis['callduty'] = $this->OtherParameter($value->staffid, $year, $month, 22);
                $value = (object) $lis;
                $data['payroll_detail'][$key]  = $value;
            }
            return view('payroll.variationControl.newPayrollReport', $data);
        } else {
            $data['payroll_detail'] = DB::table('tblpayment_consolidated')
                ->where('tblpayment_consolidated.month',     '=', $month)
                ->where('tblpayment_consolidated.year',      '=', $year)
                //->where('tblpayment_consolidated.courtID',  '=', $court)
                ->where('tblpayment_consolidated.rank', '!=', 2)
                ->where('tblpayment_consolidated.bank',      '=', $bankID)
                ->where('tblpayment_consolidated.variation_view', '=', 1)
                ->orderBy('rank', 'DESC')
                ->orderBy('grade', 'DESC')
                ->orderBy('step', 'DESC')
                ->get();
            foreach ($data['payroll_detail'] as $key => $value) {
                $lis = (array) $value;
                $lis['hazard'] = $this->OtherParameter($value->staffid, $year, $month, 4);
                $lis['callduty'] = $this->OtherParameter($value->staffid, $year, $month, 22);
                $value = (object) $lis;
                $data['payroll_detail'][$key]  = $value;
            }
            return view('payroll.variationControl.newPayrollReport', $data);
        }




        //return view('payrollReport.summary', $data);
    }

    public function OtherParameter($staffid, $year, $month, $pera)
    {
        $List = DB::Select("SELECT IFNULL(sum(`amount`),0) as Taxable FROM `tblotherEarningDeduction` WHERE `CVID`='$pera' and `staffid`='$staffid' and `month`='$month' and `year`='$year'");
        if ($List) return $List[0]->Taxable;
        return 0;
    }
    public function ThisMonthEarn($staffid, $year, $month)
    {
        $List = DB::Select("SELECT IFNULL(sum(`NetPay`),0) as amount FROM `tblpayment_consolidated`  WHERE `staffid`='$staffid' and `year`='$year' AND `month`='$month'");
        if ($List) return $List[0]->amount;
        return 0;
    }
    public function ThisMonthEarnExludeSP($staffid, $year, $month)
    {
        $List = DB::Select("SELECT IFNULL(sum(`NetPay`-`SOT`+`TAX_SOT`),0) as amount FROM `tblpayment_consolidated`  WHERE `staffid`='$staffid' and `year`='$year' AND `month`='$month'");
        if ($List) return $List[0]->amount;
        return 0;
    }
    public function ThisPension($staffid, $year, $month)
    {
        $List = DB::Select("SELECT IFNULL(sum(`PEN`),0) as amount FROM `tblpayment_consolidated`  WHERE `staffid`='$staffid' and `year`='$year' AND `month`='$month'");
        if ($List) return $List[0]->amount;
        return 0;
    }
    public function VariationRemarks($staffid, $year1, $month1, $year2, $month2)
    {
        $List = DB::Select("SELECT `arrears_type` FROM `tblstaff_for_arrears` WHERE `staffid`='$staffid' and(( `year_payment`='$year1' and `month_payment`='$month1') or ( `year_payment`='$year2' and `month_payment`='$month2'))");
        if ($List) return $List[0]->arrears_type;
        $List = DB::Select("SELECT `arrears_type` FROM `tblstaff_for_arrears_overdue` WHERE `staffid`='$staffid' and(( `year_payment`='$year1' and `month_payment`='$month1') or ( `year_payment`='$year2' and `month_payment`='$month2'))");
        if ($List) return $List[0]->arrears_type;
        return "Others";
    }



    public function councilPayrollIndex123()
    {
        $data['courts'] =  DB::table('tbl_court')->get();
        $courtSessionId = session('anycourt');
        $data['courtDivisions']  = DB::table('tbldivision')
            ->where('courtID', '=', $courtSessionId)
            ->get();

        /* $data['allbanklist']  = DB::table('tblbanklist')
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();*/
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
        $data['allbanklist'] = DB::table('tblbanklist')
            ->join('tblpayment_consolidated', 'tblpayment_consolidated.bank', '=', 'tblbanklist.bankID')
            ->where('tblpayment_consolidated.rank', '=', 2)
            ->groupBy('tblpayment_consolidated.bank')
            ->orderBy('tblbanklist.bank', 'Asc')
            ->select('tblbanklist.bank', 'tblbanklist.bankID')
            ->get();

        return view('payroll.variationControl.councilPayroll', $data);
    }


    //     public function councilPayrollIndex()
    // {
    //     $data['courts'] = DB::table('tbl_court')->get();

    //     $courtSessionId = session('anycourt');

    //     $data['courtDivisions'] = DB::table('tbldivision')
    //         ->where('courtID', $courtSessionId)
    //         ->get();

    //     $data['CourtInfo'] = $this->CourtInfo();

    //     if ($data['CourtInfo']->courtstatus == 0) {
    //         $request['court'] = $data['CourtInfo']->courtid;
    //     }

    //     if ($data['CourtInfo']->divisionstatus == 0) {
    //         $request['division'] = $data['CourtInfo']->divisionid;
    //     }

    //     $data['curDivision'] = $this->curDivision(Auth::user()->id);

    //     // ✅ SIMPLE, CORRECT BANK LIST
    //     $data['allbanklist'] = DB::table('tblbanklist')
    //         ->orderBy('bank', 'ASC')
    //         ->get();

    //     return view('payroll.variationControl.councilPayroll', $data);
    // }



    public function councilPayrollIndex6789()
    {
        $data['courts'] = DB::table('tbl_court')->get();

        $data['CourtInfo'] = $this->CourtInfo();

        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }

        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        $data['courtDivisions'] = DB::table('tbldivision')->get();

        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        // 🔥 ADD THIS BLOCK
        // if (!Auth::user()->is_global == 1 && $data['curDivision']) {
        if (Auth::user()->is_global != 1 && $data['curDivision']) {
            $data['allbanklist'] = DB::table('tblbank')
                ->where('tblbank.divisionID', '=', $data['curDivision']->divisionID)
                ->join('tblbanklist', 'tblbank.bankID', '=', 'tblbanklist.bankID')
                ->orderBy('tblbanklist.bank', 'Asc')
                ->get();
        }

        return view('payroll.variationControl.councilPayroll', $data);
    }



    public function councilPayrollIndex()
    {
        $data['courts'] = DB::table('tbl_court')->get();
        $data['CourtInfo'] = $this->CourtInfo();

        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }

        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        $data['courtDivisions'] = DB::table('tbldivision')->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);
        $data['allbanklist'] = DB::table('tblbanklist')
            ->join('tblpayment_consolidated', 'tblpayment_consolidated.bank', '=', 'tblbanklist.bankID')
            ->where('tblpayment_consolidated.rank', '=', 2)
            ->groupBy('tblpayment_consolidated.bank')
            ->orderBy('tblbanklist.bank', 'Asc')
            ->select('tblbanklist.bank', 'tblbanklist.bankID')
            ->get();

        return view('payroll.variationControl.councilPayroll', $data);
    }






    // Recieves an ajax request with a division ID and returns all  bank under that division
    public function bankToDisplay(Request $request)
    {
        $divisionsBank = DB::table('tblpayment_consolidated')
            ->leftJoin(
                'tblbanklist',
                'tblbanklist.bankID',
                '=',
                'tblpayment_consolidated.bank'
            )
            ->leftJoin(
                'tbldivision',
                'tbldivision.divisionID',
                '=',
                'tblpayment_consolidated.divisionID'
            )
            ->where('tbldivision.divisionID', $request->divisionID)
            ->where('tblpayment_consolidated.bank', '<>', 0)
            ->select(
                'tblpayment_consolidated.name',
                'tblpayment_consolidated.bank',
                'tbldivision.division as divisionName',
                'tblbanklist.bank as bankName'
            )
            ->groupBy('tblpayment_consolidated.bank')
            ->orderBy('tblbanklist.bank', 'asc')
            ->get();

        return response()->json($divisionsBank);
    }






    public function councilPayrollReport(Request $request)
    {
        // \Log::info('Received councilPayrollReport request with data:', $request->all()); // Debug log
        // dd($request->all());
        $month = trim($request->input('month'));
        $year = trim($request->input('year'));
        $division = trim($request->input('division'));
        $bankID = trim($request->input('bankName')); // Capture the selected bank
        // dd($bankID);
        $data['year'] = $year;
        $data['month'] = $month;
        $data['divisionDiv'] = $division;
        $data['selectedBank'] = DB::table('tblbanklist')->where('bankID', $bankID)->value('bank'); // Pass selected bank to the view

        Session::put('schmonth', $month . " " . $year);

        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        // Validate form inputs
        $this->validate($request, [
            'month' => 'required|regex:/^[\pL\s\-]+$/u',
            'year' => 'required|integer',
            'division' => 'required|integer',
            'bankName' => 'nullable|integer', // bank selection is optional
        ]);

        $data['userRole'] = DB::table('user_role')
            ->leftJoin('assign_user_role', 'assign_user_role.roleID', '=', 'user_role.roleID')
            ->where('userID', '=', Auth::user()->id)
            ->first();

        $data['allcomments'] = DB::table('tblsalary_council_comments')
            ->leftJoin('users', 'users.id', '=', 'tblsalary_council_comments.by_who')
            ->where('tblsalary_council_comments.year', '=', $year)
            ->where('tblsalary_council_comments.divisionID', '=', $division)
            ->where('tblsalary_council_comments.month', $month)
            ->orderBy('tblsalary_council_comments.ID', 'DESC')
            ->select(
                'users.name',
                'tblsalary_council_comments.divisionID as divisionID',
                'tblsalary_council_comments.comment',
                'tblsalary_council_comments.updated_at'
            )
            ->get();

        $data['courtDivisions'] = '';

        // ======= Payroll Query with Bank Filter =======
        $query = DB::table('tblpayment_consolidated')
            ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblpayment_consolidated.divisionID')
            ->join('tblemployment_type', 'tblemployment_type.ID', '=', 'tblpayment_consolidated.employment_type')
            ->where('tblpayment_consolidated.month', '=', $month)
            ->where('tblpayment_consolidated.year', '=', $year)
            ->where('tblpayment_consolidated.divisionID', '=', $division)
            ->where('tblpayment_consolidated.rank', '=', 2)
            ->where('tblpayment_consolidated.checking_view', '=', 1);

        // Apply bank filter only if a bank is selected
        if (!empty($bankID)) {
            $query->where('tblpayment_consolidated.bank', '=', $bankID);
        }

        $data['payroll_detail'] = $query
            ->orderBy('tblpayment_consolidated.judge_rank', 'ASC')
            ->orderBy('tblpayment_consolidated.grade', 'DESC')
            ->orderBy('tblpayment_consolidated.step', 'DESC')
            ->get();

        if ($data['payroll_detail']->isEmpty()) {
            return back()->with('error', 'No payroll data found for the selected criteria. Please check the month, year, division, or bank.');
        }

        // ======= Dynamic Earnings & Deductions =======
        try {
            $getdynamicData = $this->DynamicEaringDeduction($data['payroll_detail'], $year, $month);
            $data['staffEarnElement'] = $getdynamicData['staffEarnElement'] ?? [];
            $data['staffDeductionElement'] = $getdynamicData['staffDeductionElement'] ?? [];
            $data['getStaffMonthEarnAmount'] = $getdynamicData['getStaffMonthEarnAmount'] ?? [];
            $data['getStaffMonthDeductionAmount'] = $getdynamicData['getStaffMonthDeductionAmount'] ?? [];
        } catch (\Exception $e) {
            dd($e->getMessage());
            \Log::error('Error in DynamicEaringDeduction: ' . $e->getMessage());
            return back()->with('error', 'Error processing payroll data: ' . $e->getMessage());
        }

        return view('payroll.variationControl.viewCouncilPayroll', $data);
    }










    public function DynamicEaringDeduction($payrollDetails = [], $year = null, $month = null, $division = null, $bankID = null)
    {
        //=========================START===================================
        $getStaffMonthEarn = [];
        $getStaffMonthDeduction = [];
        $data['payroll_detail'] = $payrollDetails;

        //Get staff in the bank id
        $listOfStaff = [];
        foreach ($data['payroll_detail'] as $key11 => $staffId) {
            if ($bankID != null) {
                if ($bankID == $staffId->bank) {
                    $listOfStaff[] = $staffId->staffid;
                }
            } else {
                $listOfStaff[] = $staffId->staffid;
            }
        }
        //Get list of Earning for all staff
        $data['staffEarnElement'] = DB::table('tblotherEarningDeduction')
            ->Join('tblcvSetup', 'tblotherEarningDeduction.CVID', '=', 'tblcvSetup.ID')
            ->where('tblotherEarningDeduction.year', $year)
            ->where('tblotherEarningDeduction.month', $month)
            ->where('tblotherEarningDeduction.divisionID', $division ? '=' : '<>', $division)
            ->where('tblotherEarningDeduction.particularID', 1)
            ->where('tblotherEarningDeduction.amount', '<>', 0)
            ->whereIn('tblotherEarningDeduction.staffid', $listOfStaff)
            ->orderBy('tblcvSetup.rank')
            ->groupBy('tblotherEarningDeduction.CVID')
            ->select('description', 'amount', 'tblotherEarningDeduction.CVID', 'tblcvSetup.ID as cvsetupID', 'tblotherEarningDeduction.ID', 'rank', 'tblcvSetup.particularID', 'tblotherEarningDeduction.staffid')
            ->get();
        //Get list of Deduction for all staff
        $data['staffDeductionElement'] = DB::table('tblotherEarningDeduction')
            ->Join('tblcvSetup', 'tblotherEarningDeduction.CVID', '=', 'tblcvSetup.ID')
            ->where('tblotherEarningDeduction.year', $year)
            ->where('tblotherEarningDeduction.month', $month)
            ->where('tblotherEarningDeduction.divisionID', $division ? '=' : '<>', $division)
            ->where('tblotherEarningDeduction.particularID', 2)
            ->where('tblotherEarningDeduction.amount', '<>', 0)
            ->whereIn('tblotherEarningDeduction.staffid', $listOfStaff)
            ->orderBy('tblcvSetup.rank')
            ->groupBy('tblotherEarningDeduction.CVID')
            ->select('description', 'amount', 'tblotherEarningDeduction.CVID', 'tblcvSetup.ID as cvsetupID', 'tblotherEarningDeduction.ID', 'rank', 'tblcvSetup.particularID', 'tblotherEarningDeduction.staffid')
            ->get();

        ///Earning - Get Element amount for each and sum up duplicate element amount
        foreach ($data['payroll_detail'] as $key1 => $staffID) {
            foreach ($data['staffEarnElement'] as $key2 => $staffCVEarn) {
                $getStaffMonthEarn[$staffID->staffid][$staffCVEarn->CVID] = DB::table('tblotherEarningDeduction')
                    ->where('tblotherEarningDeduction.staffid', $staffID->staffid)
                    ->where('tblotherEarningDeduction.CVID', $staffCVEarn->CVID)
                    ->where('tblotherEarningDeduction.year', $year)
                    ->where('tblotherEarningDeduction.month', $month)
                    ->where('tblotherEarningDeduction.divisionID', $division ? '=' : '<>', $division)
                    ->first([DB::raw("SUM(tblotherEarningDeduction.amount) as staffEarnings")]);
            }
        }
        //Deduction - Get Element amount for each and sum up duplicate element amount
        foreach ($data['payroll_detail'] as $key1 => $staffID) {
            foreach ($data['staffDeductionElement'] as $key2 => $staffCVEarn) {
                $getStaffMonthDeduction[$staffID->staffid][$staffCVEarn->CVID] = DB::table('tblotherEarningDeduction')
                    ->where('tblotherEarningDeduction.staffid', $staffID->staffid)
                    ->where('tblotherEarningDeduction.CVID', $staffCVEarn->CVID)
                    ->where('tblotherEarningDeduction.year', $year)
                    ->where('tblotherEarningDeduction.month', $month)
                    ->where('tblotherEarningDeduction.divisionID', $division ? '=' : '<>', $division)
                    ->first([DB::raw("SUM(tblotherEarningDeduction.amount) as staffDeductions")]);
            }
        }

        $data['getStaffMonthEarnAmount']        = $getStaffMonthEarn;
        $data['getStaffMonthDeductionAmount']   = $getStaffMonthDeduction;
        //=========================END=====================================

        return $data;
    }
}
