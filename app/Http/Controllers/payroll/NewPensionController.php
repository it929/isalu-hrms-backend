<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use DB;
use Auth;
use Session;

class NewPensionController extends Controller
{
    public function viewPensionDeduction()
    {
        $data['authUser'] = Auth::user()->divisionID;
        $data['divisions'] = DB::table('tbldivision')
            ->get();
        $data['getauthUser'] = DB::table('tbldivision')
            ->where('divisionID', '=', $data['authUser'])
            ->get();
        // dd($data);

        return view('payroll.newPensionReport.newPensionReportIndex', $data);
    }


    public function viewPensionDeductionReport(Request $request)
    {
        // dd($request->all());
        // Session::put('month', $request->month);
        // Session::put('year', $request->year);
        if (Auth::user()->is_global == 1) {
            $month = $request['month'];
            $year = $request['year'];
            $data['month'] = $month;
            $data['year'] = $year;
            if ($request['div'] != '') {
                $data['pensionReport'] = DB::table('tblpayment_consolidated')
                    ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                    ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
                    ->where('tblpayment_consolidated.month', '=', $request['month'])
                    ->where('tblpayment_consolidated.year', '=', $request['year'])
                    ->where('tblpayment_consolidated.divisionID', '=', $request['div'])
                    ->where('tblpayment_consolidated.rank', '!=', 2)
                    // ->where('tblpayment_consolidated.PEN','!=',0)
                    ->orderBy('tblpayment_consolidated.rank', 'DESC')
                    ->orderBy('tblpayment_consolidated.grade', 'DESC')
                    ->orderBy('tblpayment_consolidated.step', 'DESC')

                    ->select('*', 'tblbanklist.bank as bankname')
                    ->get();
                $data['division'] = DB::table('tbldivision')
                    ->where('divisionID', '=', $request['div'])
                    ->get();
                // dd($data);
            } else {
                $data['pensionReport'] = DB::table('tblpayment_consolidated')
                    ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                    ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
                    ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblpayment_consolidated.divisionID')
                    ->where('tblpayment_consolidated.month', '=', $request['month'])
                    ->where('tblpayment_consolidated.year', '=', $request['year'])
                    ->where('tblpayment_consolidated.rank', '!=', 2)
                    // ->where('tblpayment_consolidated.NHF','!=',0)
                    ->orderBy('tblpayment_consolidated.divisionID', 'DESC')
                    ->orderBy('tblpayment_consolidated.rank', 'DESC')
                    ->orderBy('tblpayment_consolidated.grade', 'DESC')
                    ->orderBy('tblpayment_consolidated.step', 'DESC')

                    ->select('*', 'tblbanklist.bank as bankname')
                    ->get();
                $data['division'] = '';
            }
        } else {
            $user = Auth::user()->divisionID;
            $month = $request['month'];
            $year = $request['year'];
            $data['month'] = $month;
            $data['year'] = $year;
            $data['pensionReport'] = DB::table('tblpayment_consolidated')
                ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                //->join('basicsalaryconsolidated','basicsalaryconsolidated.employee_type','=','tblpayment_consolidated.employment_type')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
                ->where('tblpayment_consolidated.month', '=', $request['month'])
                ->where('tblpayment_consolidated.year', '=', $request['year'])
                ->where('tblpayment_consolidated.rank', '!=', 2)
                ->where('tblpayment_consolidated.divisionID', $user)
                ->where('tblpayment_consolidated.NHF', '!=', 0)
                ->orderBy('tblpayment_consolidated.rank', 'DESC')
                ->orderBy('tblpayment_consolidated.grade', 'DESC')
                ->orderBy('tblpayment_consolidated.step', 'DESC')

                ->select('*', 'tblbanklist.bank as bankname')
                ->get();
            $data['division'] = DB::table('tbldivision')
                ->where('divisionID', '=', $user)
                ->get();
        }
        return view('payroll.newPensionReport.pensionDetailReport', $data);
    }
}
