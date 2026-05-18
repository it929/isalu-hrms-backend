<?php

namespace App\Http\Controllers\payroll;



use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class BankSalaryScheduleController extends ParentController
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

        if ($data['CourtInfo']) {

            $data['allbanklist']  = DB::table('tblbanklist')
                ->get();
        }
        return view('payroll.bankSalarySchedule.index', $data);
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

    public function retrieve(Request $request)
    {
        $month     = trim($request->input('month'));
        $year      = trim($request->input('year'));
        $bankID    = trim($request->input('bankName'));
        $bankGroup = trim($request->input('bankGroup'));
        $division  = trim($request->input('division'));
        $court     = trim($request->input('court'));
        $this->validate($request, [
            'month'     => 'required|regex:/^[\pL\s\-]+$/u',
            'year'      => 'required|integer',
        ]);
        $data['bat'] = DB::table('tblbat')->where('year', $year)->where('month', $month)->first();
        $data['lock'] = DB::table('tblpayment_consolidated')->where('salary_lock', '=', 1)->where('month', '=', $month)->where('year', '=', $year)->count();

        $data['month'] = $month;
        $data['year'] = $year;
        Session::put('serialNo', 1);
        Session::put('bankID', $bankID);
        Session::put('bankGroup', $bankGroup);
        $getBank = DB::table('tblbanklist')->where('bankID', $bankID)->first();
        $data['bank_name'] = $getBank->bank ?? '';

        //$bankName = $getBank->bank;
        $bankCode = DB::table('tblbank')->where('bankID', $bankID)->first();
        $data['divisionName'] = DB::table('tbldivision')->where('divisionID', $division)->value('division');

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
            ->where('tblpayment_consolidated.bankGroup', $bankGroup ? '=' : '<>', $bankGroup)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)
            ->where('tblpayment_consolidated.rank', '!=', 2)
            ->orderBy('tblpayment_consolidated.grade', 'DESC')
            ->orderBy('tblpayment_consolidated.step', 'DESC')
            ->orderBy('tblpayment_consolidated.rank', 'DESC')
            ->orderBy('tblpayment_consolidated.bank', 'DESC')
            ->select('*', 'tblpayment_consolidated.staffid as staffIDCond')
            ->get();

        Session::put('month', $month);
        Session::put('year', $year);
        Session::put('schmonth', $month . " " . $year);

        $data['M_signatory'] = DB::table('tblmandatesignatory')
            ->leftJoin('tblmandatesignatoryprofiles', 'tblmandatesignatoryprofiles.id', '=', 'tblmandatesignatory.signatoryID')->orderby('tblmandatesignatory.id')->get();
        return view('payroll.bankSalarySchedule.retrieve', $data);
    }
}
