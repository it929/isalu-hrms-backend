<?php

namespace App\Http\Controllers\payroll;



use App\Http\Requests;
use App\Role;
use App\User;
use Entrust;
use App\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ConPayrollReportController extends ParentController
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
        set_time_limit(0);
        $this->division = $request->session()->get('division');
        // $this->divisionID = $request->session()->get('divisionID');
                return $next($request);
        });
    }

    //   public function create()
    //   {
    //     $data['courts'] =  DB::table('tbl_court')->get();
    //     $courtSessionId = session('anycourt');
    //     $data['CourtInfo'] = $this->CourtInfo();
    //     if ($data['CourtInfo']->courtstatus == 0) {
    //       $request['court'] = $data['CourtInfo']->courtid;
    //     }
    //     if ($data['CourtInfo']->divisionstatus == 0) {
    //       $request['division'] = $data['CourtInfo']->divisionid;
    //     }


    //     $data['courtDivisions']  = DB::table('tbldivision')
    //       //  ->where('courtID', '=', $courtSessionId)
    //       ->get();

    //     $data['curDivision'] = $this->curDivision(Auth::user()->id);

    //     $data['allbanklist']  = DB::table('tblbank')
    //       ->where('tblbank.courtID', '=', $courtSessionId)
    //       ->distinct()
    //       ->join('tblbanklist', 'tblbank.bankID', '=', 'tblbanklist.bankID')
    //       ->orderBy('tblbanklist.bank', 'Asc')
    //       ->get();

    //     if (count($data['CourtInfo']) > 0) {

    //       $data['allbanklist']  = DB::table('tblbanklist')
    //         //->where('tblbank.courtID', '=', $data['CourtInfo']->courtid)
    //         ->orderBy('tblbanklist.bank', 'Asc')
    //         ->get();
    //     }

    //     return view('payrollReport_con.index', $data);
    //   }



    public function create()
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
        // dd($data['curDivision']);

        if (!Auth::user()->is_global == 1) {
            $data['allbanklist']  = DB::table('tblbank')
                ->where('tblbank.divisionID', '=', $data['curDivision']->divisionID)
                ->join('tblbanklist', 'tblbank.bankID', '=', 'tblbanklist.bankID')
                ->orderBy('tblbanklist.bank', 'Asc')
                ->get();
        }

        // $data['allbanklist']  = DB::table('tblbank')
        //     ->where('tblbank.courtID', '=', $courtSessionId)
        //     ->distinct()
        //     ->join('tblbanklist', 'tblbank.bankID', '=', 'tblbanklist.bankID')
        //     ->orderBy('tblbanklist.bank', 'Asc')
        //     ->get();

        return view('payrollReport_con.index', $data);
    }

    public function getBank(Request $request)
    {
        // $court =  $request['courtID'];
        $allbanklist  = DB::table('tblbank')
            ->where('tblbank.divisionID', '=', $request['divisionID'])
            ->join('tblbanklist', 'tblbank.bankID', '=', 'tblbanklist.bankID')
            ->orderBy('tblbanklist.bank', 'Asc')
            ->get();
        return response()->json($allbanklist);
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

    //view comments on payroll
    public function payrollCommentsOLD($division, $year, $month)
    {
        $data['division'] = DB::table('tbldivision')->where('divisionID', $division)->value('division');
        $data['month'] = $month;
        $data['year'] = $year;
        $data['allcomments'] = DB::table('tblsalary_comments')
            ->leftjoin('users', 'users.id', '=', 'tblsalary_comments.by_who')
            ->where('tblsalary_comments.year', '=', $year)
            ->where('tblsalary_comments.divisionID', '=', $division)
            ->where('tblsalary_comments.month', $month)
            ->orderBy('tblsalary_comments.ID', 'DESC')
            ->select('users.name', 'tblsalary_comments.divisionID as divisionID', 'tblsalary_comments.comment', 'tblsalary_comments.updated_at')
            ->get();

        return view('payrollReport_con.payrollComments', $data);
    }

    public function payrollComments111111111111111111($division, $year, $month)
    {

        // dd(56566);
        $data['division'] = DB::table('tbldivision')->where('divisionID', $division)->value('division');
        $data['month'] = $month;
        $data['year'] = $year;

        $data['allcomments'] = DB::table('tblsalary_comments')
            ->leftJoin('users', 'users.id', '=', 'tblsalary_comments.by_who')
            ->where('tblsalary_comments.year', $year)
            ->where('tblsalary_comments.divisionID', $division)
            ->whereRaw('UPPER(tblsalary_comments.month) = ?', [strtoupper(trim($month))])
            ->orderBy('tblsalary_comments.ID', 'DESC')
            ->select('users.name', 'tblsalary_comments.divisionID as divisionID', 'tblsalary_comments.comment', 'tblsalary_comments.updated_at')
            ->get();

        dd($month, strtoupper(trim($month)), $data['allcomments']);

        return view('payrollReport_con.payrollComments', $data);
    }



    ///Function start
    public function Retrieve(Request $request, $division = null, $year = null, $month = null)
    {
        $request['month']  = $month ? $month : trim($request->input('month'));
        $request['year']             = $year ? $year : trim($request->input('year'));
        $court             = 9; // trim($request->input('court'));
        $division          = $division ? $division : trim($request->input('division'));

        $month             = $month ? $month : trim($request->input('month'));
        $year              = $year ? $year : trim($request->input('year'));
        $court             = 9; // trim($request->input('court'));
        $division          = $division ? $division : trim($request->input('division'));
        $data['allcomments'] = [];
        $data['divisionName'] = DB::table('tbldivision')->where('divisionID', $division)->value('division');

        $bankID    = trim($request->input('bankName'));
        $data['bankName'] = $bankID;
        $bankGroup = trim($request->input('bankGroup'));
        $data['year'] = $year;
        $data['month'] = $month;
        $data['userRole'] = DB::table('user_role')
            ->leftjoin('assign_user_role', 'assign_user_role.roleID', '=', 'user_role.roleID')
            ->where('userID', '=', Auth::user()->id)->first();

        $data['allcomments'] = DB::table('tblsalary_comments')
            ->leftjoin('users', 'users.id', '=', 'tblsalary_comments.by_who')
            ->where('tblsalary_comments.year', '=', $year)
            ->where('tblsalary_comments.divisionID', '=', $division)
            ->where('tblsalary_comments.month', $month)
            ->orderBy('tblsalary_comments.ID', 'DESC')
            ->select('users.name', 'tblsalary_comments.divisionID as divisionID', 'tblsalary_comments.comment', 'tblsalary_comments.updated_at')
            ->get();


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

        $data['courtName']  = DB::table('tbl_court')->where('id', '=', $court)->value('court_name');
        $data['divisionName'] = DB::table('tbldivision')->where('divisionID', '=', $division)->value('division');


        if ($bankID != '') {
            $getBank  = DB::table('tblbanklist')
                ->where('bankID', $bankID)
                ->first();

            $bankName = $getBank->bank;
            Session::flash('bank', $bankName);
        }

        //===================== query starts ====================
        $data['courtname']  = DB::table('tbl_court')->where('id', '=', $court)->first();
        $courtDiv = DB::table('tbl_court')
            ->join('tbldivision', 'tbldivision.courtID', '=', 'tbl_court.id')
            ->where('tbl_court.id', '=', $court)
            ->where('tbldivision.divisionID', $division ? '=' : '<>', $division)
            ->first();
        $data['courtDivisions'] = $courtDiv ? $courtDiv : '';
        $payroll_detail = DB::table('tblpayment_consolidated')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblpayment_consolidated.divisionID')
            ->where('tblpayment_consolidated.month',     '=', $month)
            ->where('tblpayment_consolidated.year',      '=', $year)
            ->where('tblpayment_consolidated.courtID',  '=', $court)
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.bankGroup', $bankGroup ? '=' : '<>', $bankGroup)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)
            ->where('tblpayment_consolidated.rank', '!=', 2)
            ->orderBy('rank', 'DESC')
            ->orderBy('grade', 'DESC')
            ->orderBy('step', 'DESC')
            ->get();
        // ->paginate(100);
        $getPayroll_detail = [];
        $listOfStaff = [];
        foreach ($payroll_detail as $key => $value) {
            $lis = (array) $value;
            $lis['hazard'] = $this->OtherParameter($value->staffid, $year, $month, $year, 4);
            $lis['callduty'] = $this->OtherParameter($value->staffid, $year, $month, $year, 22);
            $value = (object) $lis;
            $getPayroll_detail[$key]  = $value;
            //Get staff
            if ($bankID != null) {
                if ($bankID == $value->bank) {
                    $listOfStaff[] = $value->staffid;
                }
            } else {
                $listOfStaff[] = $value->staffid;
            }
        }
        $data['payroll_detail'] = $getPayroll_detail;
        $getdynamicData = $this->DynamicEaringDeduction($listOfStaff, $year, $month, $division, $bankID);
        $data['staffEarnElement'] = $getdynamicData['staffEarnElement'];
        $data['staffDeductionElement'] = $getdynamicData['staffDeductionElement'];
        $data['getStaffMonthEarnAmount'] = $getdynamicData['getStaffMonthEarnAmount'];
        $data['getStaffMonthDeductionAmount'] = $getdynamicData['getStaffMonthDeductionAmount'];

        return view('payrollReport_con.summary', $data);
        //===================== query ends ====================


    }


    public function DynamicEaringDeduction($listOfStaff = [], $year = null, $month = null, $division = null, $bankID = null)
    {
        //=========================START===================================
        $getStaffMonthEarn = [];
        $getStaffMonthDeduction = [];
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
        foreach ($listOfStaff as $staffID) {
            foreach ($data['staffEarnElement'] as $staffCVEarn) {
                $getStaffMonthEarn[$staffID][$staffCVEarn->CVID] = DB::table('tblotherEarningDeduction')
                    ->where('tblotherEarningDeduction.staffid', $staffID)
                    ->where('tblotherEarningDeduction.CVID', $staffCVEarn->CVID)
                    ->where('tblotherEarningDeduction.year', $year)
                    ->where('tblotherEarningDeduction.month', $month)
                    ->where('tblotherEarningDeduction.divisionID', $division ? '=' : '<>', $division)
                    ->first([DB::raw("SUM(tblotherEarningDeduction.amount) as staffEarnings")]);
            }

            //Deduction - Get Element amount for each and sum up duplicate element amount
            foreach ($data['staffDeductionElement'] as $staffCVEarn) {
                $getStaffMonthDeduction[$staffID][$staffCVEarn->CVID] = DB::table('tblotherEarningDeduction')
                    ->where('tblotherEarningDeduction.staffid', $staffID)
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


    public function BulkPayRoll(Request $request)
    {
        $this->validate($request, [
            'month'     => 'required|regex:/^[\pL\s\-]+$/u',
            'year'      => 'required|integer'
        ]);
        $month              = trim($request->input('month'));
        $year               = trim($request->input('year'));
        $court             = trim($request->input('court'));
        $division          = trim($request->input('division'));

        $division           = $this->division;
        Session::put('schmonth', $month . " " . $year);
        DB::enableQueryLog();
        $data['month']      = $month;
        $data['year']       = $year;
        $data['division']   = $this->division;
        return view('payrollReport.bulkPayroll', $data);
    }



    public function arrearsOearn($court, $fileNo, $year, $month)
    {
        $fNo = $fileNo;
        $data['courtName'] = DB::table('tbl_court')->where('id', '=', $court)->first();
        $data['fn'] = DB::table('tblper')->where('ID', '=', $fileNo)->first();
        $check = DB::table('tblarrears')
            ->where('month',     '=', $month)
            ->where('year',      '=', $year)
            ->where('courtID',   '=', $court)
            ->where('staffid',      '=', $fNo)
            ->count();
        if ($check == 0) {
            return back()->with('err', 'No Arrears Found');
        }


        $data['oarrears'] = DB::table('tblarrears')
            /*->select('fileNo','month','year','oldGrade','OldStep','newGrade','newStep','newBasic','oldBasic','oldTax','newTax','oldPeculiar','newPeculiar','oldLeave_bonus',
'newLeave_bonus','oldPension','newPension','oldNhf','newNhf','oldUnionDues','newUnionDues','dueDate','date as date_computed')*/
            ->where('month',     '=', $month)
            ->where('year',      '=', $year)
            ->where('courtID',   '=', $court)
            ->where('staffid',      '=', $fNo)
            ->first();

        $data['overDues'] = DB::table('tblarrears_overdue')
            ->join('tblstaff_for_arrears_overdue', 'tblstaff_for_arrears_overdue.due_date', '=', 'tblarrears_overdue.dueDate')
            ->where('tblarrears_overdue.month',     '=', $month)
            ->where('tblarrears_overdue.year',      '=', $year)
            ->where('tblarrears_overdue.courtID',   '=', $court)
            ->where('tblarrears_overdue.staffid',      '=', $fNo)
            ->where('tblstaff_for_arrears_overdue.month_payment',     '=', $month)
            ->where('tblstaff_for_arrears_overdue.year_payment',      '=', $year)
            ->where('tblstaff_for_arrears_overdue.staffid',      '=', $fNo)
            ->get();

        //dd($data['oarrears']);
        $activemonth = date("n", strtotime($data['oarrears']->month));
        $data['varimonth'] = $this->dateDiff($data['oarrears']->year . "-" . $activemonth . "-1", $data['oarrears']->dueDate);

        $activemonthOverdue = date("n", strtotime($data['oarrears']->month));
        $data['varimonthOver'] = $this->dateDiff($data['oarrears']->year . "-" . $activemonthOverdue . "-1", $data['oarrears']->dueDate);

        //dd( $data['fn']);


        return view('payrollReport_con/otherArrears', $data);
    }


    public function arrearsOearnTest($court, $fileNo, $year, $month)
    {
        //$fNo = str_replace('-', '/', $fileNo);
        $fNo = $fileNo;
        $data['courtName'] = DB::table('tbl_court')->where('id', '=', $court)->first();
        $data['fn'] = DB::table('tblper')->where('ID', '=', $fileNo)->first();
        $check = DB::table('tblarrears')
            ->where('month',     '=', $month)
            ->where('year',      '=', $year)
            ->where('courtID',   '=', $court)
            ->where('staffid',      '=', $fNo)
            ->count();
        if ($check == 0) {
            return back()->with('err', 'No Arrears Found');
        }


        $data['oarrears'] = DB::table('tblarrears')
            /*->select('fileNo','month','year','oldGrade','OldStep','newGrade','newStep','newBasic','oldBasic','oldTax','newTax','oldPeculiar','newPeculiar','oldLeave_bonus',
'newLeave_bonus','oldPension','newPension','oldNhf','newNhf','oldUnionDues','newUnionDues','dueDate','date as date_computed')*/
            ->where('month',     '=', $month)
            ->where('year',      '=', $year)
            ->where('courtID',   '=', $court)
            ->where('staffid',      '=', $fNo)
            ->first();

        $data['overDues'] = DB::table('tblarrears_overdue')
            ->join('tblstaff_for_arrears_overdue', 'tblstaff_for_arrears_overdue.due_date', '=', 'tblarrears_overdue.dueDate')
            ->where('tblarrears_overdue.month',     '=', $month)
            ->where('tblarrears_overdue.year',      '=', $year)
            ->where('tblarrears_overdue.courtID',   '=', $court)
            ->where('tblarrears_overdue.staffid',      '=', $fNo)
            ->where('tblstaff_for_arrears_overdue.month_payment',     '=', $month)
            ->where('tblstaff_for_arrears_overdue.year_payment',      '=', $year)
            ->where('tblstaff_for_arrears_overdue.staffid',      '=', $fNo)
            ->get();

        //dd($data['oarrears']);
        $activemonth = date("n", strtotime($data['oarrears']->month));
        $data['varimonth'] = $this->dateDiff($data['oarrears']->year . "-" . $activemonth . "-1", $data['oarrears']->dueDate);

        $activemonthOverdue = date("n", strtotime($data['oarrears']->month));
        $data['varimonthOver'] = $this->dateDiff($data['oarrears']->year . "-" . $activemonthOverdue . "-1", $data['oarrears']->dueDate);

        //dd( $data['overDue']);
        return view('payrollReport_con/otherArrears_17_06_2019', $data);
    }



    function dateDiff($date2, $date1)
    {
        list($year2, $mth2, $day2) = explode("-", $date2);
        list($year1, $mth1, $day1) = explode("-", $date1);
        if ($year1 > $year2) dd('Invalid Input - dates do not match');
        $days_month = 0;
        $days_month = $this->days_in_month($mth1, $year1);
        //$days_month = cal_days_in_month(CAL_GREGORIAN, $mth1, $year1);
        $day_diff = 0;

        if ($year2 == $year1) {
            $mth_diff = $mth2 - $mth1;
        } else {
            $yr_diff = $year2 - $year1;
            $mth_diff = (12 * $yr_diff) - $mth1 + $mth2;
        }
        if ($day1 > 1) {
            $mth_diff--;
            //dd($mth1.",".$year1);
            $day_diff = $days_month - $day1 + 1;
        }

        $result = array('months' => $mth_diff, 'days' => $day_diff, 'days_of_month' => $days_month);
        return ($result);
    } //end


    public function payrollBreakdown()
    {
        $data['courts'] =  DB::table('tbl_court')->get();
        $courtSessionId = session('anycourt');
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

            $data['courtDivisions']  = DB::table('tbldivision')
                //  ->where('courtID', '=', $courtSessionId)
                ->get();
            $data['curDivision'] = $this->curDivision(Auth::user()->id);

            $data['allbanklist']  = DB::table('tblbanklist')
                ->orderBy('tblbanklist.bank', 'Asc')
                ->get();
        }

        return view('payrollReport_con.payrollBreakdown', $data);
    }

    public function payrollBreakdownReport(Request $request)
    {
        $month             = trim($request->input('month'));
        $year              = trim($request->input('year'));
        $court             = trim($request->input('court'));
        $division          = trim($request->input('division'));



        $bankID    = trim($request->input('bankName'));
        $bankGroup = trim($request->input('bankGroup'));

        $data['count_sot'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.month',     '=', $month)
            ->where('tblpayment_consolidated.year',      '=', $year)
            //->where('tblpayment_consolidated.divisionID',  '=', $division)
            //->where('tblpayment_consolidated.courtID',  '=', $court)
            ->where('tblpayment_consolidated.SOT', '>', 0)
            ->where('tblpayment_consolidated.rank', '!=', 2)
            ->count();


        $data['year'] = $year;
        $data['month'] = $month;

        Session::put('schmonth', $month . " " . $year);

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

        if ($division == '' &&  $bankGroup != '' &&  $bankID != '') {
            $data['courtname']  = DB::table('tbl_court')
                ->where('id', '=', $court)
                ->first();
            $data['courtDivisions'] = '';
            $data['payroll_detail'] = DB::table('tblpayment_consolidated')
                ->where('tblpayment_consolidated.month',     '=', $month)
                ->where('tblpayment_consolidated.year',      '=', $year)
                ->where('tblpayment_consolidated.courtID',  '=', $court)
                ->where('tblpayment_consolidated.bank',      '=', $bankID)
                ->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
                ->where('tblpayment_consolidated.rank', '!=', 2)
                ->orderBy('rank', 'DESC')
                ->orderBy('grade', 'DESC')
                ->orderBy('step', 'DESC')
                ->get();
            return view('payrollReport_con.payrollReportBreakdown', $data);
        } elseif ($division != '' &&  $bankGroup != '' &&  $bankID != '') {

            $data['courtname'] = '';
            $data['courtDivisions']  = DB::table('tbl_court')
                ->join('tbldivision', 'tbldivision.courtID', '=', 'tbl_court.id')
                ->where('tbl_court.id', '=', $court)
                ->where('tbldivision.divisionID', '=', $division)
                ->first();
            $data['payroll_detail'] = DB::table('tblpayment_consolidated')
                ->where('tblpayment_consolidated.month',     '=', $month)
                ->where('tblpayment_consolidated.year',      '=', $year)
                ->where('tblpayment_consolidated.divisionID',  '=', $division)
                ->where('tblpayment_consolidated.courtID',  '=', $court)
                ->where('tblpayment_consolidated.bank',      '=', $bankID)
                ->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
                ->where('tblpayment_consolidated.rank', '!=', 2)
                //->select('*', 'tblper.ID')
                ->orderBy('rank', 'DESC')
                ->orderBy('grade', 'DESC')
                ->orderBy('step', 'DESC')
                ->get();
            return view('payrollReport_con.payrollReportBreakdown', $data);
        }
        if ($bankGroup == '' &&  $bankID != '') {
            if ($division == '') {
                $data['courtname']  = DB::table('tbl_court')
                    ->where('id', '=', $court)
                    ->first();
                $data['courtDivisions'] = '';
                $data['payroll_detail'] = DB::table('tblpayment_consolidated')
                    ->where('tblpayment_consolidated.month',     '=', $month)
                    ->where('tblpayment_consolidated.year',      '=', $year)
                    ->where('tblpayment_consolidated.courtID',  '=', $court)
                    ->where('tblpayment_consolidated.bank',      '=', $bankID)
                    //->where('tblpayment_consolidated.bankGroup', '=', $bankGroup)
                    ->where('tblpayment_consolidated.rank', '!=', 2)
                    ->orderBy('rank', 'DESC')
                    ->orderBy('grade', 'DESC')
                    ->orderBy('step', 'DESC')
                    ->get();
                return view('payrollReport_con.payrollReportBreakdown', $data);
            } else {

                $data['courtname'] = '';
                $data['courtDivisions']  = DB::table('tbl_court')
                    ->join('tbldivision', 'tbldivision.courtID', '=', 'tbl_court.id')
                    ->where('tbl_court.id', '=', $court)
                    ->where('tbldivision.divisionID', '=', $division)
                    ->first();
                $data['payroll_detail'] = DB::table('tblpayment_consolidated')
                    ->where('tblpayment_consolidated.month',     '=', $month)
                    ->where('tblpayment_consolidated.year',      '=', $year)
                    ->where('tblpayment_consolidated.divisionID',  '=', $division)
                    ->where('tblpayment_consolidated.courtID',  '=', $court)
                    ->where('tblpayment_consolidated.bank',      '=', $bankID)
                    ->where('tblpayment_consolidated.rank', '!=', 2)
                    ->orderBy('rank', 'DESC')
                    ->orderBy('grade', 'DESC')
                    ->orderBy('step', 'DESC')
                    ->get();
                return view('payrollReport_con.payrollReportBreakdown', $data);
            }
        }

        if ($bankGroup == '' && $bankID == '') {
            if ($division == '') {
                $data['courtname']  = DB::table('tbl_court')
                    ->where('id', '=', $court)
                    ->first();
                $data['courtDivisions'] = '';
                $data['payroll_detail'] = DB::table('tblpayment_consolidated')
                    ->where('tblpayment_consolidated.month',     '=', $month)
                    ->where('tblpayment_consolidated.year',      '=', $year)
                    ->where('tblpayment_consolidated.courtID',  '=', $court)
                    ->where('tblpayment_consolidated.rank', '!=', 2)
                    ->orderBy('rank', 'DESC')
                    ->orderBy('grade', 'DESC')
                    ->orderBy('step', 'DESC')
                    ->get();
                return view('payrollReport_con.payrollReportBreakdown', $data);
            } else {

                $data['courtname'] = '';
                $data['courtDivisions']  = DB::table('tbl_court')
                    ->join('tbldivision', 'tbldivision.courtID', '=', 'tbl_court.id')
                    ->where('tbl_court.id', '=', $court)
                    ->where('tbldivision.divisionID', '=', $division)
                    ->first();
                $data['payroll_detail'] = DB::table('tblpayment_consolidated')
                    ->where('tblpayment_consolidated.month',     '=', $month)
                    ->where('tblpayment_consolidated.year',      '=', $year)
                    ->where('tblpayment_consolidated.divisionID',  '=', $division)
                    ->where('tblpayment_consolidated.courtID',  '=', $court)
                    ->where('tblpayment_consolidated.rank', '!=', 2)
                    ->orderBy('rank', 'DESC')
                    ->orderBy('grade', 'DESC')
                    ->orderBy('step', 'DESC')
                    ->get();
                return view('payrollReport_con.payrollReportBreakdown', $data);
            }
        }

        return view('payrollReport.payrollReportBreakdown', $data);
    }

    public function OtherParameter($staffid, $year, $month, $toYear, $pera)
    {
        //$listOfMonths = join("','", $monthsToSearch);
        $List = DB::Select("SELECT IFNULL(sum(`amount`),0) as Taxable FROM `tblotherEarningDeduction` WHERE `year`='$year' AND `month`='months' AND `CVID`='$pera' AND `staffid`='$staffid' GROUP BY staffid ");
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
    public function CompareEarning(Request $request)
    {
        $month1             = $request->input('month1');
        $month2             = $request->input('month2');
        $year1              = $request->input('year1');
        $year2              = $request->input('year2');
        $bank               = $request->input('bank');
        $division           = trim($request->input('division'));

        $bankID             = trim($request->input('bankName'));
        $data['bankName']   = $bankID;

        $data['sp']         = $request->input('sp');
        $data['month1']     = $request->input('month1');
        $data['month2']     = $request->input('month2');
        $data['year1']      = $request->input('year1');
        $data['year2']      = $request->input('year2');
        $data['courts']     =  DB::table('tbl_court')->get();
        $courtSessionId     = session('anycourt');

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
        if (!Auth::user()->is_global == 1) {
            $data['allbanklist']  = DB::table('tblbank')
                ->where('tblbank.divisionID', '=', $data['curDivision']->divisionID)
                ->join('tblbanklist', 'tblbank.bankID', '=', 'tblbanklist.bankID')
                ->orderBy('tblbanklist.bank', 'Asc')
                ->get();
        }


        $qbank = 1;
        if ($request->input('bankName') != '') {
            $qbank = "`tblpayment_consolidated`.`bank`='$bankID'";
        }
        $affectedStaff = DB::Select("SELECT tblpayment_consolidated.*, tblbanklist.bank as bankname FROM `tblpayment_consolidated`
    left join tblbanklist on tblbanklist.bankID=tblpayment_consolidated.bank
    WHERE (`divisionID`='$division')
    and ((`year`='$year1' and `month`='$month1' and $qbank) or (`year`='$year2' and `month`='$month2' and $qbank)) and rank<>2 group by `staffid` order by tblbanklist.bank ");
        // dd($affectedStaff);
        $data['record'] = [];
        foreach ($affectedStaff as $v) {
            $total1 = ($data['sp'] != '1') ? $this->ThisMonthEarn($v->staffid, $year1, $month1) : $this->ThisMonthEarnExludeSP($v->staffid, $year1, $month1);
            $total2 = ($data['sp'] != '1') ? $this->ThisMonthEarn($v->staffid, $year2, $month2) : $this->ThisMonthEarnExludeSP($v->staffid, $year2, $month2);
            $diff = $total1 - $total2;
            if (round($diff, 2) != 0.00) $data['record'][] = array('Names' => $v->name, 'Banks' => $v->bankname, 'year' => $v->year, 'staffid' => $v->staffid, 'StaffID' => $v->staffid, 'net1' => $total1, 'net2' => $total2, 'diff' => $diff);
            //Geting Remarks
            $data['getStaffRemark'][$v->staffid] = DB::table('compare_earning_remarks')->where('name', $v->name)->where([['month', '=', $month1], ['year', '=', $year1], ['month2', '=', $month2], ['year2', '=', $year2],])->value('remark');
        }
        // dd($data['record']);
        return view('payrollReport_con.compare_earning', $data);
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

    public function CompareEarningRemark(Request $request)
    {
        //insert or update
        $status = DB::table('compare_earning_remarks')->updateOrInsert(
            ['month' => $request->month1, 'year' => $request->year1, 'month2' => $request->month2, 'year2' => $request->year2, 'staffID' => $request->staffID],
            ['remark' => $request->remark, 'name' => $request->staffName]
        );

        if ($status) {
            $data = DB::table('compare_earning_remarks')->where('name', $request->staffName)
                ->where('month', $request->month1)->where('month2', $request->month2)
                ->where('year', $request->year1)->where('year2', $request->year2)
                ->first();

            return response()->json($data);
        }
    }


    public function ComparePension(Request $request)
    {
        $month1             = $request->input('month1');
        $month2             = $request->input('month2');
        $year1             = $request->input('year1');
        $year2             = $request->input('year2');
        $data['sp']  = $request->input('sp');
        $data['month1']  = $request->input('month1');
        $data['month2']             = $request->input('month2');
        $data['year1']            = $request->input('year1');
        $data['year2']             = $request->input('year2');
        $data['courts'] =  DB::table('tbl_court')->get();
        $courtSessionId = session('anycourt');

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
        $affectedStaff = DB::Select("SELECT * FROM `tblpayment_consolidated` WHERE (`year`='$year1' and `month`='$month1') or (`year`='$year2' and `month`='$month2') group by `staffid`");
        //dd($year2.$month2 );
        $data['record'] = [];
        foreach ($affectedStaff as $v) {
            $total1 = $this->ThisPension($v->staffid, $year1, $month1);
            $total2 = $this->ThisPension($v->staffid, $year2, $month2);
            $diff = $total1 - $total2;
            $reason = $this->VariationRemarks($v->staffid, $year1, $month1, $year2, $month2);
            if (round($diff, 2) != 0.00) $data['record'][] = array('Names' => $v->name, 'net1' => $total1, 'net2' => $total2, 'diff' => $diff, 'reason' => $reason);
        }
        //dd( $data['record']);
        return view('payrollReport_con.compare_pension', $data);
    }

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
            //  ->where('courtID', '=', $courtSessionId)
            ->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

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
        // dd($data);
        return view('payrollReport_con.newPayrolIndex', $data);
    }


    public function newPayrollReport(Request $request)
    {
        $month             = trim($request->input('month'));
        $toMonth             = trim($request->input('toMonth'));
        $year              = trim($request->input('year'));
        $toYear              = trim($request->input('toYear'));
        $court             = trim($request->input('court'));
        $division          = trim($request->input('division'));


        //*****************************SORTING WITH MONTHS AND YEARS PROVIDED ****************/
        //Array of months
        $months = [
            'JANUARY',
            'FEBRUARY',
            'MARCH',
            'APRIL',
            'MAY',
            'JUNE',
            'JULY',
            'AUGUST',
            'SEPTEMBER',
            'OCTOBER',
            'NOVEMBER',
            'DECEMBER'
        ];

        //getting the position of the provided months and years (to & from) in the array
        $fromMonthPosition = array_search($month, $months);
        $toMonthPosition = array_search($toMonth, $months);

        $length =  $toMonthPosition - $fromMonthPosition + 1;

        $monthsToSearch = array_slice($months, $fromMonthPosition, $length); //contains a new array of months to search with

        //*****************************SORTING WITH MONTHS AND YEARS PROVIDED END****************/


        //$data['alhisan'] = DB::table('tblotherEarningDeduction')->where('month','=',$month)->where('year','=',$year)->where('CVID','=',31)->count();
        $data['alhisan'] = DB::table('tblotherEarningDeduction')->whereBetween('year', [$year, $toYear])->whereIn('month', $monthsToSearch)->where('CVID', '=', 31)->count();

        $bankID    = trim($request->input('bankName'));
        $bankGroup = trim($request->input('bankGroup'));
        $data['year'] = $year;
        $data['month'] = $month;
        $data['toMonth'] = $toMonth;
        $data['toYear'] = $toYear;


        Session::put('schmonth', $month . " " . $year);

        $data['count_sot'] = DB::table('tblpayment_consolidated')
            //->where('tblpayment_consolidated.month',     '=', $month)
            ->whereIn('tblpayment_consolidated.month', $monthsToSearch)
            //->where('tblpayment_consolidated.year',      '=', $year)
            ->whereBetween('tblpayment_consolidated.year', [$year, $toYear])
            ->where('tblpayment_consolidated.divisionID',  '=', $division)
            ->where('tblpayment_consolidated.courtID',  '=', $court)
            ->where('tblpayment_consolidated.SOT', '>', 0)
            ->where('tblpayment_consolidated.rank', '!=', 2)
            ->groupBy('fileNo')
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
                //->where('tblpayment_consolidated.month',     '=', $month)
                ->whereIn('tblpayment_consolidated.month', $monthsToSearch)
                //->where('tblpayment_consolidated.year',      '=', $year)
                ->whereBetween('tblpayment_consolidated.year', [$year, $toYear])
                //->where('tblpayment_consolidated.courtID',  '=', $court)
                ->where('tblpayment_consolidated.rank', '!=', 2)
                ->groupBy('fileNo')
                ->orderBy('rank', 'DESC')
                ->orderBy('grade', 'DESC')
                ->orderBy('step', 'DESC')
                ->orderBy('employment_type', 'DESC')
                ->get();

            foreach ($data['payroll_detail'] as $key => $value) {
                $lis = (array) $value;
                //$lis['hazard'] = $this->OtherParameter($value->staffid, $year, $month, 4);
                $lis['hazard'] = $this->OtherParameter($value->staffid, $year, $monthsToSearch, $toYear, 4);
                //$lis['callduty'] = $this->OtherParameter($value->staffid, $year, $month, 22);
                $lis['callduty'] = $this->OtherParameter($value->staffid, $year, $monthsToSearch, $toYear, 22);
                $value = (object) $lis;
                $data['payroll_detail'][$key]  = $value;
            }
            return view('payrollReport_con.newPayrollReport', $data);
        } else {
            $data['payroll_detail'] = DB::table('tblpayment_consolidated')
                //->where('tblpayment_consolidated.month',     '=', $month)
                ->whereIn('tblpayment_consolidated.month', $monthsToSearch)
                //->where('tblpayment_consolidated.year',      '=', $year)
                ->whereBetween('tblpayment_consolidated.year', [$year, $toYear])
                //->where('tblpayment_consolidated.courtID',  '=', $court)
                ->where('tblpayment_consolidated.rank', '!=', 2)
                ->where('tblpayment_consolidated.bank', '=', $bankID)
                ->groupBy('fileNo')
                ->orderBy('rank', 'DESC')
                ->orderBy('grade', 'DESC')
                ->orderBy('step', 'DESC')
                ->orderBy('employment_type', 'DESC')
                ->get();
            foreach ($data['payroll_detail'] as $key => $value) {
                $lis = (array) $value;
                //$lis['hazard'] = $this->OtherParameter($value->staffid, $year, $month, 4);
                $lis['hazard'] = $this->OtherParameter($value->staffid, $year, $monthsToSearch, $toYear, 4);
                //$lis['callduty'] = $this->OtherParameter($value->staffid, $year, $month, 22);
                $lis['callduty'] = $this->OtherParameter($value->staffid, $year, $monthsToSearch, $toYear, 22);
                $value = (object) $lis;
                $data['payroll_detail'][$key]  = $value;

                //====== Get Some Computation
                //====== Get total deductions

                // $allDeducts =  $refunds + $reports->TAX + $reports->PEN + $vpenAmount + $reports->UD + $reports->NHF + (count($coopSaving) ==0 ? 0 : $coopSaving->amount) + (count($coopLoan) ==0 ? 0 : $coopLoan->amount) + ($alhisanAmount + $alhisansLoanAmount) + (count($salAdvance) ==0 ? 0 : $salAdvance->amount);

                // $getTotalGrossEmolument = $reports->Bs + $reports->AEarn + $medAll_ + $reports->SOT + $ov + $sa + $reports->PEC;
                // $grossEmolument += $getTotalGrossEmolument;

                // $totalNetEmolu += ($getTotalGrossEmolument - $allDeducts)

                //Update new netpay for each staff
                //DB::table('tblpayment_consolidated')->where('staffid','=', $reports->staffid)->where('month','=',$reports->month)->where('year','=',$reports->year)->update(['staff_amount_paid' => ($getTotalGrossEmolument - $allDeducts)  ]);

            }

            //return $data;
            return view('payrollReport_con.newPayrollReport', $data);
        }




        //return view('payrollReport.summary', $data);
    }
}
