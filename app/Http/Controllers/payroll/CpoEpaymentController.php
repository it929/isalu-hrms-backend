<?php

namespace App\Http\Controllers\payroll;



use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class CpoEpaymentController extends ParentController
{
    public function index()
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

        $data['courtDivisions']  = DB::table('tbldivision')->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        // if (count($data['CourtInfo']) > 0) {
        $data['allbanklist']  = DB::table('tblbanklist')
            ->orderBy('tblbanklist.bank', 'Asc')
            ->get();
        // }
        return view('payroll.CpoEpaymentReport.index', $data);
    }

    public function Retrieve(Request $request)
    {
        $month     = trim($request->input('month'));
        $year      = trim($request->input('year'));
        $bankID    = trim($request->input('bankName'));
        $bankGroup = trim($request->input('bankGroup'));
        $division  = trim($request->input('divisionID'));
        $court     = trim($request->input('court'));
        $sn        = trim($request->input('sn'));
        $this->validate($request, [
            'month'     => 'required|regex:/^[\pL\s\-]+$/u',
            'year'      => 'required|integer',
            'sn'        => 'required'
        ]);
        // dd($division);
        $data['bat'] = DB::table('tblbat')
            ->where('year', $year)
            ->where('month', $month)
            ->first();
        $data['lock'] = DB::table('tblpayment_consolidated')->where('salary_lock', '=', 1)->where('month', '=', $month)->where('year', '=', $year)->count();
        $data['sn'] = $sn;
        $data['month'] = $month;
        Session::put('serialNo', 1);
        Session::put('bankID', $bankID);
        Session::put('bankGroup', $bankGroup);
        $getBank = DB::table('tblbanklist')->where('bankID', $bankID)->first();

        //$bankName = $getBank->bank;
        $bankCode = DB::table('tblbank')->where('bankID', $bankID)->first();
        $data['cpoPaymentAccount'] =  DB::table('tblpayer_account')->where('is_active', '=', 1)->where('is_staff', '=', 1)->value('account_no');

        $data['courtname'] = '';
        $data['courtDivisions']  = DB::table('tbl_court')
            ->join('tbldivision', 'tbldivision.courtID', '=', 'tbl_court.id')
            ->where('tbl_court.id', '=', $court)
            ->where('tbldivision.divisionID', '=', $division)
            ->first();

        $data['epayment_detail'] = DB::table('tblpayment_consolidated')
            ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
            ->leftJoin('tblbacklog', 'tblbacklog.staffid', '=', 'tblpayment_consolidated.staffid', 'tblbacklog.month', '=', $month, 'tblbacklog.year', '=', $year)
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblpayment_consolidated.divisionID')
            ->where('tblpayment_consolidated.month',     '=', $month)
            ->where('tblpayment_consolidated.year',      '=', $year)
            ->where('tblpayment_consolidated.courtID',  '=', $court)
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)
            ->where('tblpayment_consolidated.rank', '!=', 2)
            ->where(function ($q) {
                $q->where('tblpayment_consolidated.vstage', '=', 5)
                    ->orWhere('tblpayment_consolidated.vstage', '=', 6);
            })
            ->orderBy('tblpayment_consolidated.divisionID', 'ASC')
            ->orderBy('tblpayment_consolidated.bank', 'DESC')
            ->orderBy('tblpayment_consolidated.rank', 'DESC')
            ->orderBy('tblpayment_consolidated.name', 'ASC')
            ->select('*', 'tblpayment_consolidated.staffid as staffIDCond', 'tbldivision.division')
            ->get();

        $data['unionSum'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('UD');
        $data['taxSum'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('TAX');
        $gross = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('TEarn');
        $data['nhis'] = (5 / 100) * $gross;
        $data['totalPaySum'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('NetPay');


        $totalRows = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.month',     '=', $month)
            ->where('tblpayment_consolidated.year',      '=', $year)
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)
            // ->where('tblpayment_consolidated.courtID',  '=', $court)
            ->where('tblpayment_consolidated.rank', '!=', 2)
            ->where(function ($q) {
                $q->where('tblpayment_consolidated.vstage', '=', 5)
                    ->orWhere('tblpayment_consolidated.vstage', '=', 6);
            })
            ->count();



        if ($totalRows < 10) {
            Session::put('showTotal', "yes");
        } elseif ($totalRows == 10) {
            Session::put('showTotal', "yes");
        } else {
            Session::put('showTotal', "");
        }

        Session::put('month', $month);
        Session::put('year', $year);
        Session::put('schmonth', $month . " " . $year);

        $data['M_signatory'] = DB::table('tblmandatesignatory')
            ->leftJoin('tblmandatesignatoryprofiles', 'tblmandatesignatoryprofiles.id', '=', 'tblmandatesignatory.signatoryID')->orderby('tblmandatesignatory.id')->get();
        $data['nhisbal'] = DB::table('tblnhisbalances')
            ->where('month',     '=', $month)
            ->where('year',      '=', $year)
            ->first();
        // $data['nhisexist'] = count($data['nhisbal']);
        $data['nhisexist'] = $data['nhisbal'];

        //===================GET DYNAMIC DEDUCTION ON EPAYMENT=================
        $allStaffID = [];
        foreach ($data['epayment_detail'] as $staffID) {
            $allStaffID[] = $staffID->staffIDCond;
        }




        //not in use
        // $data['staffDeductionElement'] = DB::table('tblotherEarningDeduction')
        //     ->Join('tblcvSetup', 'tblotherEarningDeduction.CVID', '=', 'tblcvSetup.ID')
        //     ->where('tblotherEarningDeduction.year', $year)
        //     ->where('tblotherEarningDeduction.month', $month)
        //     ->where('tblotherEarningDeduction.divisionID', $division ? '=' : '<>',  $division)
        //     ->whereIn('tblotherEarningDeduction.staffid', $allStaffID)
        //     ->where('tblotherEarningDeduction.particularID', 2)
        //     ->where('tblotherEarningDeduction.amount', '<>', 0)
        //     ->orderBy('tblcvSetup.rank')
        //     ->groupBy('tblotherEarningDeduction.CVID')
        //     ->select('account_number', 'account_name', DB::raw("SUM(tblotherEarningDeduction.amount) as staffEarnings"), 'description', 'amount', 'tblotherEarningDeduction.CVID', 'tblcvSetup.ID as cvsetupID', 'tblotherEarningDeduction.ID', 'rank', 'tblcvSetup.particularID', 'tblotherEarningDeduction.staffid')
        //     ->get();

        //===================END EPAYMENT DEDUCTION============================
        return view('payroll.CpoEpaymentReport.retrieve', $data);
    }

    public function justiceIndex()
    {
        $data['courts'] =  DB::table('tbl_court')->get();
        $courtSessionId = session('anycourt');
        $data['courtDivisions']  = DB::table('tbldivision')->where('courtID', '=', $courtSessionId)->get();

        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        $data['courtDivisions']  = DB::table('tbldivision')->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        // if (count($data['CourtInfo']) > 0) {
        // $data['allbanklist']  = DB::table('tblbanklist')->get();
        // }

        $data['allbanklist'] = DB::table('tblbanklist')
            ->join('tblpayment_consolidated', 'tblpayment_consolidated.bank', '=', 'tblbanklist.bankID')
            ->where('tblpayment_consolidated.rank',  2)
            ->groupBy('tblpayment_consolidated.bank')
            ->orderBy('tblbanklist.bank', 'Asc')
            ->select('tblbanklist.bank', 'tblbanklist.bankID')
            ->get();
        return view('payroll.CpoEpaymentReport.justiceIndex', $data);
    }

    public function justiceRetrieve(Request $request)
    {
        $month     = trim($request->input('month'));
        $year      = trim($request->input('year'));
        $bankID    = trim($request->input('bankName'));
        $bankGroup = trim($request->input('bankGroup'));
        $division  = trim($request->input('divisionID'));
        $court     = trim($request->input('court'));
        $sn        = trim($request->input('sn'));
        $this->validate($request, [
            'month'     => 'required|regex:/^[\pL\s\-]+$/u',
            'year'      => 'required|integer',
            'sn'        => 'required'

        ]);
        // dd($division);
        $data['bat'] = DB::table('tblbat')
            ->where('year', $year)
            ->where('month', $month)
            ->first();
        $data['lock'] = DB::table('tblpayment_consolidated')->where('salary_lock', '=', 1)->where('month', '=', $month)->where('year', '=', $year)->count();
        $data['sn'] = $sn;
        $data['month'] = $month;
        Session::put('serialNo', 1);
        Session::put('bankID', $bankID);
        Session::put('bankGroup', $bankGroup);
        $getBank = DB::table('tblbanklist')->where('bankID', $bankID)->first();

        //$bankName = $getBank->bank;
        $bankCode = DB::table('tblbank')->where('bankID', $bankID)->first();
        $data['cpoPaymentAccount'] =  DB::table('tblpayer_account')->where('is_active', '=', 1)->where('is_staff', '=', 2)->value('account_no');

        $data['courtname'] = '';
        $data['courtDivisions']  = DB::table('tbl_court')
            ->join('tbldivision', 'tbldivision.courtID', '=', 'tbl_court.id')
            ->where('tbl_court.id', '=', $court)
            ->where('tbldivision.divisionID', '=', $division)
            ->first();

        $data['epayment_detail'] = DB::table('tblpayment_consolidated')
            ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
            ->leftJoin('tblbacklog', 'tblbacklog.staffid', '=', 'tblpayment_consolidated.staffid', 'tblbacklog.month', '=', $month, 'tblbacklog.year', '=', $year)
            ->where('tblpayment_consolidated.month',     '=', $month)
            ->where('tblpayment_consolidated.year',      '=', $year)
            ->where('tblpayment_consolidated.courtID',  '=', $court)
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)
            ->where('tblpayment_consolidated.rank', '=', 2)
            ->where(function ($q) {
                $q->where('tblpayment_consolidated.vstage', '=', 5)
                    ->orWhere('tblpayment_consolidated.vstage', '=', 6);
            })
            ->orderBy('tblpayment_consolidated.bank', 'DESC')
            ->orderBy('tblpayment_consolidated.rank', 'DESC')
            ->orderBy('tblpayment_consolidated.name', 'ASC')
            ->select('*', 'tblpayment_consolidated.staffid as staffIDCond')
            ->get();

        $data['unionSum'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('UD');
        $data['taxSum'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('TAX');
        $gross = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('TEarn');
        $data['nhis'] = (5 / 100) * $gross;
        $data['totalPaySum'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('NetPay');


        $totalRows = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.month',     '=', $month)
            ->where('tblpayment_consolidated.year',      '=', $year)
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)
            ->where('tblpayment_consolidated.rank', '=', 2)
            ->where('tblpayment_consolidated.vstage', '=', 5)
            ->orWhere('tblpayment_consolidated.vstage', '=', 6)
            ->count();



        if ($totalRows < 10) {
            Session::put('showTotal', "yes");
        } elseif ($totalRows == 10) {
            Session::put('showTotal', "yes");
        } else {
            Session::put('showTotal', "");
        }

        Session::put('month', $month);
        Session::put('year', $year);
        Session::put('schmonth', $month . " " . $year);

        $data['M_signatory'] = DB::table('tblmandatesignatory')
            ->leftJoin('tblmandatesignatoryprofiles', 'tblmandatesignatoryprofiles.id', '=', 'tblmandatesignatory.signatoryID')->orderby('tblmandatesignatory.id')->get();
        $data['nhisbal'] = DB::table('tblnhisbalances')
            ->where('month',     '=', $month)
            ->where('year',      '=', $year)
            ->first();
        // $data['nhisexist'] = count($data['nhisbal']);
        $data['nhisexist'] = $data['nhisbal'];

        //===================GET DYNAMIC DEDUCTION ON EPAYMENT=================
        $allStaffID = [];
        foreach ($data['epayment_detail'] as $staffID) {
            $allStaffID[] = $staffID->staffIDCond;
        }

        //===================END EPAYMENT DEDUCTION============================
        return view('payroll.CpoEpaymentReport.justiceRetrieve', $data);
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

    public function isPayableDeduction(Request $request)
    {
        $data['month'] = '';
        $data['year'] = '';
        $data['activeMonth'] = DB::table('tblactivemonth')->first();
        return view('payroll.CpoEpaymentReport.payableDeduction', $data);
    }

    public function isPayableDeductionRetrieve(Request $request)
    {
        // dd($request->all());
        $this->validate($request, [
            'year'      => 'required',
            'month' => 'required',
            'sn' => 'required|numeric'
        ]);

        $data['month'] = trim($request->input('month'));
        $data['year'] = trim($request->input('year'));
        $data['sn'] = $request['sn'];

        $data['allbanklist']  = DB::table('tblbanklist')->get();

        $data['activeMonth'] = DB::table('tblactivemonth')->first();
        $data['cpoPaymentAccount'] =  DB::table('tblpayer_account')->where('is_active', '=', 1)->where('is_staff', '=', 1)->value('account_no');
        //get all deductions where is payable is 1 join other earning table and sum
        $data['isPayable'] = DB::table('tblotherEarningDeduction')
            ->leftjoin('tblcvSetup', 'tblcvSetup.ID', '=', 'tblotherEarningDeduction.CVID')
            ->leftjoin('tblbanklist', 'tblcvSetup.bank', '=', 'tblbanklist.bankID')
            ->where('tblcvSetup.isPayable', '=', 1)
            ->where('tblcvSetup.ParticularID', '=', 2)
            ->where('tblotherEarningDeduction.year', '=', $data['year'])
            ->where('tblotherEarningDeduction.month', '=', $data['month'])
            ->groupBY('tblotherEarningDeduction.CVID')
            ->select('*', 'tblcvSetup.description', 'tblbanklist.Bankcode', (DB::raw("SUM(tblotherEarningDeduction.amount) as total")))
            ->get();
        // dd($data['isPayable']);
        $totals = 0;
        foreach ($data['isPayable'] as $month_total) {
            $totals += $month_total->total;
        }
        $data['grossTotal'] = $totals;
        // dd($data['grossTotal']);
        return view('payroll.CpoEpaymentReport.payableDeductionRetrieve', $data);
    }

    public function setAccountNumberIndex()
    {
        $data['bankList'] =  DB::table('tblbanklist')
            ->orderBy('tblbanklist.bank', 'Asc')
            ->get();
        $data['banks'] =  DB::table('tblpayer_account')->get();
        return view('payroll.CpoEpaymentReport.accountNumber.index', $data);
    }

    public function setAccountNumber(Request $request)
    {
        // dd($request->all());
        $this->validate($request, [
            // 'bank'     => 'required',
            'bankID'     => 'required',
            'accNumber'      => 'required',
            'status' => 'required',
            'desc' => 'required'

        ]);
        $getBankName = DB::table('tblbanklist')->where('bankID', $request['bankID'])->value('bank');
        $checkActiveBank = DB::table('tblpayer_account')->where('is_active', '=', 1)->where('is_staff', '=', 1)->first();
        // $checkActiveBankForJudge = DB::table('tblpayer_account')->where('is_active', '=', 1)->where('is_staff', '=', 2)->first();
        if ($request['status'] == 1 && $request['desc'] == 1 && $checkActiveBank) {
            return back()->with('error', 'you already have an active bank');
        }
        // if ($request['status'] == 1 && $request['desc'] == 2 && $checkActiveBankForJudge) {
        //     return back()->with('error', 'you already have an active bank');
        // }
        $saveBank = DB::table('tblpayer_account')->insert([
            'account_no' => $request['accNumber'],
            'bank' => $getBankName,
            'bankID' => $request['bankID'],
            'is_active' => $request['status'],
            'is_staff' => $request['desc']
        ]);

        if ($saveBank) {
            return back()->with('message', 'You have successfully created bank');
        } else {
            return back()->with('error', 'Opps! Something went wrong');
        }

        // return view('CpoEpaymentReport.accountNumber.index');
    }

    public function updateAccountNumber(Request $request)
    {
        $id = $request['id'];
        $bank = $request['new_bank'];
        $accNum = $request['new_account_no'];
        $status = $request['new_status'];
        $is_staff = $request['new_desc'];

        // if($status == 1 && $is_staff == 1){
        //     $checkActiveBank = DB::table('tblpayer_account')->where('id', '!=', $id)->where('is_active', '=', 1)->where('is_staff', '=', 1)->first();
        //     if($checkActiveBank){
        //         return back()->with('error', 'Please Deactivate Active bank account for Staff');
        //     }
        // }

        // if($status == 1 && $is_staff == 2){
        //     $checkActiveBankForJudge = DB::table('tblpayer_account')->where('id', '!=', $id)->where('is_active', '=', 1)->where('is_staff', '=', 2)->first();
        //     if($checkActiveBankForJudge){
        //         return back()->with('error', 'Please Deactivate Active bank account for Justice');
        //     }
        // }

        // if($checkActiveBank){
        //     return back()->with('error', 'Please Deactivate Active bank account');
        // }
        $updateBank = DB::table('tblpayer_account')->where('id', $id)->update([
            'account_no' => $accNum,
            'bank' => $bank,
            'is_active' => $status,
            'is_staff' => $is_staff
        ]);

        if ($updateBank) {
            return back()->with('message', 'You have successfully updated bank');
        } else {

            return back()->with('error', 'Opps! Something went wrong');
        }
    }

    public function deleteAccountNumber($id)
    {
        // dd($id);
        $destroy = DB::table('tblpayer_account')->where('id', '=', $id)->delete();
        // $destroy = $accNum->delete();
        if ($destroy) {
            return back()->with('message', 'You have successfully deleted bank details');
        }
    }

    public function wrongAccount()
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

        $data['courtDivisions']  = DB::table('tbldivision')->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        // if (count($data['CourtInfo']) > 0) {
        $data['allbanklist']  = DB::table('tblbanklist')->get();
        // }
        return view('payroll.CpoEpaymentReport.wrongAccount', $data);
    }

    public function retrieveWrongAccount(Request $request)
    {
        $month     = trim($request->input('month'));
        $year      = trim($request->input('year'));
        $bankID    = trim($request->input('bankName'));
        $bankGroup = trim($request->input('bankGroup'));
        $division  = trim($request->input('divisionID'));
        $court     = trim($request->input('court'));
        $this->validate($request, [
            'month'     => 'required|regex:/^[\pL\s\-]+$/u',
            'year'      => 'required|integer'
        ]);
        // dd($division);
        $data['bat'] = DB::table('tblbat')
            ->where('year', $year)
            ->where('month', $month)
            ->first();
        $data['lock'] = DB::table('tblpayment_consolidated')->where('salary_lock', '=', 1)->where('month', '=', $month)->where('year', '=', $year)->count();
        $data['month'] = $month;
        Session::put('serialNo', 1);
        Session::put('bankID', $bankID);
        Session::put('bankGroup', $bankGroup);
        $getBank = DB::table('tblbanklist')->where('bankID', $bankID)->first();
        $data['getAllBank'] = DB::table('tblbanklist')->get();
        //$bankName = $getBank->bank;
        $bankCode = DB::table('tblbank')->where('bankID', $bankID)->first();

        $data['courtname'] = '';
        $data['courtDivisions']  = DB::table('tbl_court')
            ->join('tbldivision', 'tbldivision.courtID', '=', 'tbl_court.id')
            ->where('tbl_court.id', '=', $court)
            ->where('tbldivision.divisionID', '=', $division)
            ->first();

        $data['epayment_detail'] = DB::table('tblpayment_consolidated')
            ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
            ->leftJoin('tblbacklog', 'tblbacklog.staffid', '=', 'tblpayment_consolidated.staffid', 'tblbacklog.month', '=', $month, 'tblbacklog.year', '=', $year)
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblpayment_consolidated.divisionID')
            ->where('tblpayment_consolidated.month',     '=', $month)
            ->where('tblpayment_consolidated.year',      '=', $year)
            ->where('tblpayment_consolidated.courtID',  '=', $court)
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)
            ->where('tblpayment_consolidated.rank', '!=', 2)
            ->orderBy('tblpayment_consolidated.divisionID', 'ASC')
            ->orderBy('tblpayment_consolidated.bank', 'DESC')
            ->orderBy('tblpayment_consolidated.grade', 'DESC')
            ->orderBy('tblpayment_consolidated.step', 'DESC')
            ->orderBy('tblpayment_consolidated.rank', 'DESC')
            ->orderBy('tblpayment_consolidated.name', 'ASC')
            ->select('*', 'tblpayment_consolidated.staffid as staffIDCond', 'tbldivision.division')
            ->get();

        $data['unionSum'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('UD');
        $data['taxSum'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('TAX');
        $gross = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('TEarn');
        $data['nhis'] = (5 / 100) * $gross;
        $data['totalPaySum'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('NetPay');


        $totalRows = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.month',     '=', $month)
            ->where('tblpayment_consolidated.year',      '=', $year)
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)
            ->where('tblpayment_consolidated.courtID',  '=', $court)
            ->count();



        if ($totalRows < 10) {
            Session::put('showTotal', "yes");
        } elseif ($totalRows == 10) {
            Session::put('showTotal', "yes");
        } else {
            Session::put('showTotal', "");
        }

        Session::put('month', $month);
        Session::put('year', $year);
        Session::put('schmonth', $month . " " . $year);

        // $data['M_signatory'] = DB::table('tblmandatesignatory')
        //     ->leftJoin('tblmandatesignatoryprofiles', 'tblmandatesignatoryprofiles.id', '=', 'tblmandatesignatory.signatoryID')->orderby('tblmandatesignatory.id')->get();
        // $data['nhisbal'] = DB::table('tblnhisbalances')
        //     ->where('month',     '=', $month)
        //     ->where('year',      '=', $year)
        //     ->first();
        // $data['nhisexist'] = count($data['nhisbal']);

        $data['M_signatory'] = DB::table('tblmandatesignatory')
            ->leftJoin('tblmandatesignatoryprofiles', 'tblmandatesignatoryprofiles.id', '=', 'tblmandatesignatory.signatoryID')
            ->orderBy('tblmandatesignatory.id')
            ->get();

        $data['nhisbal'] = DB::table('tblnhisbalances')
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        $data['nhisexist'] = $data['nhisbal'] ? 1 : 0;   // ✓ no error


        //===================GET DYNAMIC DEDUCTION ON EPAYMENT=================
        $allStaffID = [];
        foreach ($data['epayment_detail'] as $staffID) {
            $allStaffID[] = $staffID->staffIDCond;
        }

        return view('payroll.CpoEpaymentReport.retrieveWrongAccount', $data);
    }

    public function updateWrongAccount(Request $request, $staffID)
    {
        $data['activeMonth'] = DB::table('tblactivemonth')->first();
        $initialBank = DB::table('tblper')->where('ID', '=', $staffID)->first(['bankID']);
        try {
            $updateAccount = DB::table('tblpayment_consolidated')->where('staffid', '=', $staffID)
                ->where('month', '=', $data['activeMonth']->month)->where('year', '=', $data['activeMonth']->year)->update([
                    'bank' => $request['bank'] == 0 ? $initialBank->bankID : $request['bank'],
                    'AccNo' => $request['accNo']
                ]);

            $updateAccount2 = DB::table('tblper')->where('ID', '=', $staffID)->update([
                'bankID' => $request['bank'] == 0 ? $initialBank->bankID : $request['bank'],
                'AccNo' => $request['accNo']
            ]);

            if ($updateAccount && $updateAccount2) {
                return response()->json(['status' => 'success']);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function epaymentByDivision()
    {
        $data['courts'] =  DB::table('tbl_court')->get();
        $courtSessionId = session('anycourt');
        $data['courtDivisions']  = DB::table('tbldivision')
            ->where('courtID', '=', $courtSessionId)
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

        if (count($data['CourtInfo']) > 0) {
            $data['allbanklist']  = DB::table('tblbanklist')->get();
        }
        return view('payroll.CpoEpaymentReport.epaymentByDivision', $data);
    }

    public function retrieveEpaymentByDivision(Request $request)
    {
        $this->validate($request, [
            'year'      => 'required',
            'month' => 'required',
            'sn' => 'required|numeric'
        ]);
        $data['sNo'] = $request['sn'];
        $data['epayment_detail'] = DB::table('tblpayment_consolidated')
            ->leftJoin('tblbacklog', 'tblbacklog.staffid', '=', 'tblpayment_consolidated.staffid', 'tblbacklog.month', '=', $request['month'], 'tblbacklog.year', '=', $request['year'])
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblpayment_consolidated.divisionID')
            ->leftjoin('tblbanklist', 'tblbanklist.bankID', '=', 'tbldivision.bankID')
            ->where('tblpayment_consolidated.month',     '=', $request['month'])
            ->where('tblpayment_consolidated.year',      '=', $request['year'])
            ->where('tblpayment_consolidated.rank', '!=', 2)
            ->orderBy('tblpayment_consolidated.divisionID', 'ASC')
            ->groupBy('tblpayment_consolidated.divisionID')
            ->select('*', 'tbldivision.division', 'tblbanklist.bank', 'tblbanklist.Bankcode', 'tbldivision.acctName', 'tbldivision.acctNo', (DB::raw("SUM(tblpayment_consolidated.NetPay) as divisionTotal")))
            ->get();

        $data['cpoPaymentAccount'] =  DB::table('tblpayer_account')->where('is_active', '=', 1)->where('is_staff', '=', 1)->value('account_no');
        // dd($data['epayment_detail']);
        return view('payroll.CpoEpaymentReport.retrieveEpaymentByDivision', $data);
    }



    //////// SALARY MANDATE ///////////////

    public function salaryIndex()
    {
        $data['courts'] =  DB::table('tbl_court')->get();
        $courtSessionId = session('anycourt');
        $data['courtDivisions']  = DB::table('tbldivision')->where('courtID', '=', $courtSessionId)->get();

        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        $data['courtDivisions']  = DB::table('tbldivision')->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        // if (count($data['CourtInfo']) > 0) {
        //     $data['allbanklist']  = DB::table('tblbanklist')->get();
        // }
        return view('payroll.CpoEpaymentReport.salaryMandate', $data);
    }


    public function salaryMandateReport_09_03_2026(Request $request)
    {
        $month = trim($request->input('month'));
        $year  = trim($request->input('year'));

        $this->validate($request, [
            'month' => 'required|regex:/^[\pL\s\-]+$/u',
            'year'  => 'required|integer'
        ]);

        // Fetch all payment records for the selected month/year
        $epaymentDetail = DB::table('tblpayment_consolidated')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblpayment_consolidated.divisionID')
            ->where('tblpayment_consolidated.month', $month)
            ->where('tblpayment_consolidated.year', $year)
            ->orderBy('tblpayment_consolidated.bank', 'DESC')
            ->orderBy('tblpayment_consolidated.grade', 'DESC')
            ->orderBy('tblpayment_consolidated.step', 'DESC')
            ->orderBy('tblpayment_consolidated.rank', 'DESC')
            ->orderBy('tblpayment_consolidated.name', 'ASC')
            ->select('*', 'tblpayment_consolidated.staffid as staffIDCond', 'tbldivision.division')
            ->get();

        //////////// Check if audit has verified the payroll (vstage should be 5)////////////////////
        // if ($epaymentDetail[0]->vstage != 5) {
        //     return back()->with('err', 'Audit has not verified this payroll.');
        // }

        // Define bank IDs
        $microFinance  = [37, 39]; // Microfinance banks
        $cbnID         = 36;       // CBN
        $allBankIDs    = DB::table('tblbanklist')->pluck('bankID');
        $commercial    = $allBankIDs->filter(fn($id) => !in_array($id, [$cbnID, 37, 39]));

        // // Totals for Commercial and Micro Banks
        // $commercialBanks = $epaymentDetail->whereIn('bank', $commercial)->sum('gross');
        // $microBanks      = $epaymentDetail->whereIn('bank', $microFinance)->sum('gross');
        // // $microBanksTotal      = $epaymentDetail->whereIn('bank', $microFinance)->sum('gross');


        // // Totals for Commercial and Micro Banks Pension
        //  $commercialBanksTotalPension = $epaymentDetail->whereIn('bank', $commercial)->sum('PEN');

        //  $microBanksTotalPension      = $epaymentDetail->whereIn('bank', $microFinance)->sum('PEN');


        // // Totals for Commercial and Micro Banks Tax
        //  $commercialBanksTotalPension = $epaymentDetail->whereIn('bank', $commercial)->sum('TAX');

        //  $microBanksTotalPension      = $epaymentDetail->whereIn('bank', $microFinance)->sum('TAX');


        // Totals for Commercial and Micro Banks (Gross)
        $commercialBanks = $epaymentDetail->whereIn('bank', $commercial)->sum('gross');
        $microBanks      = $epaymentDetail->whereIn('bank', $microFinance)->sum('gross');

        // Pension
        $commercialBanksPension = $epaymentDetail->whereIn('bank', $commercial)->sum('PEN');
        $microBanksPension      = $epaymentDetail->whereIn('bank', $microFinance)->sum('PEN');

        // Tax
        $commercialBanksTax = $epaymentDetail->whereIn('bank', $commercial)->sum('TAX');
        $microBanksTax      = $epaymentDetail->whereIn('bank', $microFinance)->sum('TAX');

        // Net Amount = Gross - (Tax + Pension)
        $commercialBanksNet = $commercialBanks - ($commercialBanksPension + $commercialBanksTax);
        $microBanksNet      = $microBanks - ($microBanksPension + $microBanksTax);

        $commercialBanksNetTotal = $commercialBanksNet - $microBanksNet ;


        // total

        //  $commercialBanksTotal = $commercialBanks - $commercialBanksTotalPension;



        // CBN Gross and Detailed Deductions
        $cbnDeductions = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.year', $year)
            ->where('tblpayment_consolidated.month', $month)
            ->select(
                'tblpayment_consolidated.vstage',
                DB::raw('SUM(NHF) as total_nhf'),
                DB::raw('SUM(NSITF) as total_nsitf'),
                DB::raw('SUM(CASE WHEN current_state = 37 THEN TAX ELSE 0 END) as total_tax'),
                DB::raw('
                SUM(NHF)
              + SUM(NSITF)
              + SUM(CASE WHEN current_state = 37 THEN TAX ELSE 0 END)
              as grand_total
            ')

            )

            ->first();
        // dd($cbnDeductions);

        $cbnBanksTotal = $cbnDeductions->grand_total ?? 0;

        // Total Amount
        $totalAmount = $commercialBanksNetTotal + $microBanksNet + $cbnBanksTotal;

        // Prepare data array for view
        $data = [
            'epayment_detail'    => $epaymentDetail,
            'commercialBanks'    => $commercialBanksNetTotal,
            'microBanks'         => $microBanksNet,
            'cbnBanks'           => $cbnBanksTotal,
            'totalAmount'        => $totalAmount,
            // 'totalInWords'       => $totalInWords,
            'cbnDeductions'      => $cbnDeductions,
        ];

        return view('payroll.CpoEpaymentReport.displaySalaryMandate', $data);
    }



    public function salaryMandateReport(Request $request)
    {
        $month = trim($request->input('month'));
        $year  = trim($request->input('year'));

        $this->validate($request, [
            'month' => 'required|regex:/^[\pL\s\-]+$/u',
            'year'  => 'required|integer'
        ]);

        // Fetch all payment records for the selected month/year
        $epaymentDetail = DB::table('tblpayment_consolidated')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblpayment_consolidated.divisionID')
            ->where('tblpayment_consolidated.month', $month)
            ->where('tblpayment_consolidated.year', $year)
            ->orderBy('tblpayment_consolidated.bank', 'DESC')
            ->orderBy('tblpayment_consolidated.grade', 'DESC')
            ->orderBy('tblpayment_consolidated.step', 'DESC')
            ->orderBy('tblpayment_consolidated.rank', 'DESC')
            ->orderBy('tblpayment_consolidated.name', 'ASC')
            ->select('*', 'tblpayment_consolidated.staffid as staffIDCond', 'tbldivision.division')
            ->get();

        //////////// Check if audit has verified the payroll (vstage should be 5)////////////////////

        //a = get all gross (staff and justice), and nstif value
        //b = get all pension
        //a - b

        // Define bank IDs
        $microFinance  = [37, 39]; // Microfinance banks
        $cbnID         = 36;       // CBN
        $allBankIDs    = DB::table('tblbanklist')->pluck('bankID');
        $commercial    = $allBankIDs->filter(fn($id) => !in_array($id, [$cbnID,]));


        // Totals for Commercial and Micro Banks
        //  $commercialBanks = $epaymentDetail->whereIn('bank', $commercial)->sum('gross');
        // $microBanksTotal      = $epaymentDetail->whereIn('bank', $microFinance)->sum('gross');

        // Totals for Commercial and Micro Banks (Gross)
        $commercialBanks = $epaymentDetail->whereIn('bank', $commercial)->sum('gross');
        // dd($commercialBanks);
        $microBanks      = $epaymentDetail->whereIn('bank', $microFinance)->sum('NetPay');

        // Pension
        $commercialBanksPension = $epaymentDetail->whereIn('bank', $commercial )->sum('PEN');


        //  dd($commercialBanks, $commercialBanksPension);















        // CBN Gross and Detailed Deductions
        $cbnDeductions = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.year', $year)
            ->where('tblpayment_consolidated.month', $month)
            ->select(
                'tblpayment_consolidated.vstage',
                DB::raw('SUM(NHF) as total_nhf'),
                DB::raw('SUM(NSITF) as total_nsitf'),
                DB::raw('SUM(CASE WHEN current_state = 37 THEN TAX ELSE 0 END) as total_tax'),
                DB::raw('
                SUM(NHF)
              + SUM(NSITF)
              + SUM(CASE WHEN current_state = 37 THEN TAX ELSE 0 END)
              as grand_total
            ')

            )

            ->first();


        $cbnBanksTotal = $cbnDeductions->grand_total ?? 0;



        //  $totalMandate =  $commercialBanks + $cbnBanksTotal->total_nsitf;
        $totalMandate = $commercialBanks + ($cbnDeductions->total_nsitf ?? 0);

         $totalAmount  = $totalMandate -  $commercialBanksPension ;
        //  $commercialBanksNetTotal = $totalMandate -  $commercialBanksPension ;


        // Total commercial banks
       $commercialBanksNetTotal =  $totalAmount -   ($microBanks  + $cbnBanksTotal);

        // Convert totalAmount to words
        // $numberToWords     = new NumberToWords();
        // $numberTransformer = $numberToWords->getNumberTransformer('en');

        // $naira = floor($totalAmount);
        // $kobo  = round(($totalAmount - $naira) * 100);

        // $totalInWords = ucfirst($numberTransformer->toWords($naira)) . ' Naira';
        // if ($kobo > 0) {
        //     $totalInWords .= ' and ' . ucfirst($numberTransformer->toWords($kobo)) . ' Kobo';
        // }

        // Prepare data array for view
        $data = [
            'epayment_detail'    => $epaymentDetail,
            'commercialBanks'    => $commercialBanksNetTotal,
            'microBanks'         => $microBanks ,
            'cbnBanks'           => $cbnBanksTotal,
            'totalAmount'        => $totalAmount,
            // 'totalInWords'       => $totalInWords,
            'cbnDeductions'      => $cbnDeductions,
        ];

        return view('payroll.CpoEpaymentReport.displaySalaryMandate', $data);
    }
}
