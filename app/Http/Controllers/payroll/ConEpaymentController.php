<?php

namespace App\Http\Controllers\payroll;



use App\Exports\EPaymentScheduleExport;
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
use App\Http\Controllers\payroll\ParentController;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AllBanksExport;

class ConEpaymentController extends ParentController
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
        // $this->division = $request->session()->get('division');
        // $this->divisionID = $request->session()->get('divisionID');
                return $next($request);
        });
    }

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

        $data['courtDivisions']  = DB::table('tbldivision')
            //->select('tbldivision.divisionID', 'tbldivision.division')
            //  ->where('courtID', '=', $courtSessionId)
            ->get();
        //dd($data['courtDivisions'] );
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        if ($data['CourtInfo']) {

            $data['allbanklist'] = DB::table('tblbanklist')
                ->join('tblpayment_consolidated', 'tblpayment_consolidated.bank', '=', 'tblbanklist.bankID')
                ->where('tblpayment_consolidated.rank', '!=', 2)
                ->groupBy('tblpayment_consolidated.bank')
                ->orderBy('tblbanklist.bank', 'Asc')
                ->select('tblbanklist.bank', 'tblbanklist.bankID')
                ->get();
        }
        return view('payroll.con_epayment.index', $data);
    }

    // All Banks with Justice, Staff and Deduction
    public function indexAllBanks()
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
            //->select('tbldivision.divisionID', 'tbldivision.division')
            //  ->where('courtID', '=', $courtSessionId)
            ->get();
        //dd($data['courtDivisions'] );
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        if ($data['CourtInfo']) {

            $data['allbanklist'] = DB::table('tblbanklist')
                ->join('tblpayment_consolidated', 'tblpayment_consolidated.bank', '=', 'tblbanklist.bankID')
                ->where('tblpayment_consolidated.rank', '!=', 2)
                ->groupBy('tblpayment_consolidated.bank')
                ->orderBy('tblbanklist.bank', 'Asc')
                ->select('tblbanklist.bank', 'tblbanklist.bankID')
                ->get();
        }
        return view('payroll.con_epayment.indexAllBank', $data);
    }

    public function indexJustices()
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
            //->select('tbldivision.divisionID', 'tbldivision.division')
            //  ->where('courtID', '=', $courtSessionId)
            ->get();
        //dd($data['courtDivisions'] );
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        if ($data['CourtInfo']) {

            $data['allbanklist'] = DB::table('tblbanklist')
                ->join('tblpayment_consolidated', 'tblpayment_consolidated.bank', '=', 'tblbanklist.bankID')
                ->where('tblpayment_consolidated.rank', '=', 2)
                ->groupBy('tblpayment_consolidated.bank')
                ->orderBy('tblbanklist.bank', 'Asc')
                ->select('tblbanklist.bank', 'tblbanklist.bankID')
                ->get();
        }
        return view('payroll.con_epayment.indexJustices', $data);
    }

    public function Retrieve(Request $request)
    {


        $month     = trim($request->input('month'));
        $year      = trim($request->input('year'));
        $bankID    = trim($request->input('bankName'));
        $bankGroup = trim($request->input('bankGroup'));
        $division  = trim($request->input('divisionID'));
        $court     = trim($request->input('court'));
        $this->validate($request, [
            'month'     => 'required|regex:/^[\pL\s\-]+$/u',
            'year'      => 'required|integer',
        ]);
        // dd($division);

        $data['monthNumber'] = date('n', strtotime($month));

        $data['bat'] = DB::table('tblbat')
            ->where('year', $year)
            ->where('month', $month)
            ->first();
        $data['lock'] = DB::table('tblpayment_consolidated')->where('salary_lock', '=', 1)->where('month', '=', $month)->where('year', '=', $year)->count();

        $data['month'] = $month;
        Session::put('serialNo', 1);
        Session::put('bankID', $bankID);
        Session::put('bankGroup', $bankGroup);

        $getBank = DB::table('tblbanklist')
            ->where('bankID', $bankID)
            ->first();

        //$bankName = $getBank->bank;
        $bankCode = DB::table('tblbank')
            ->where('bankID', $bankID)
            ->first();

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
            ->where('tblpayment_consolidated.bankGroup', $bankGroup ? '=' : '<>', $bankGroup)
            ->where('tblpayment_consolidated.rank', '!=', 2)
            ->orderBy('tblpayment_consolidated.bank', 'DESC')
            ->orderBy('tblpayment_consolidated.rank', 'DESC')
            ->orderBy('tblpayment_consolidated.name', 'ASC')
            ->select('*', 'tblpayment_consolidated.staffid as staffIDCond')
            ->get();
        $data['epayment_total'] = DB::table('tblpayment_consolidated')
            ->where('month', $month)
            ->where('year', $year)
            ->when($bankID, fn($q) => $q->where('bank', $bankID))
            ->when($division, fn($q) => $q->where('divisionID', $division))
            ->when($bankGroup, fn($q) => $q->where('bankGroup', $bankGroup))
            ->groupBy('bank')
            ->select('bank', DB::raw('SUM(NetPay) as NetPay'))
            ->get();


        // dd(count($data['epayment_detail']));
        $data['unionSum'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.bankGroup', $bankGroup ? '=' : '<>', $bankGroup)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('UD');
        $data['taxSum'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.bankGroup', $bankGroup ? '=' : '<>', $bankGroup)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('TAX');
        $gross = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.bankGroup', $bankGroup ? '=' : '<>', $bankGroup)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('TEarn');
        $data['nhis'] = (5 / 100) * $gross;
        $data['nhf'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.bankGroup', $bankGroup ? '=' : '<>', $bankGroup)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('NHF');
        $data['totalPaySum'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.bankGroup', $bankGroup ? '=' : '<>', $bankGroup)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('NetPay');


        $totalRows = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.month',     '=', $month)
            ->where('tblpayment_consolidated.year',      '=', $year)
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.bankGroup', $bankGroup ? '=' : '<>', $bankGroup)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)
            ->where('tblpayment_consolidated.courtID',  '=', $court)
            ->count();


        // \Log::info($request);

        // dd(3432);
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

        // Fetch account details from tblmandate_address_account
        $data['accountDetails'] = DB::table('tblmandate_address_account')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
            ->join('tblcontractType', 'tblcontractType.ID', '=', 'tblmandate_address_account.contractTypeID')
            ->select(
                'tblmandate_address_account.*',
                'tblbanklist.bank',
                'tblcontractType.contractType'
            )
            ->where('tblmandate_address_account.contractTypeID', 6)
            ->where('tblmandate_address_account.status', 1)
            ->first(); // Use first() since you want only one record
        $data['monthNumber'] = date('n', strtotime($month));

        //===================GET DYNAMIC DEDUCTION ON EPAYMENT=================
        $allStaffID = [];
        foreach ($data['epayment_detail'] as $staffID) {
            $allStaffID[] = $staffID->staffIDCond;
        }
        $data['staffDeductionElement'] = DB::table('tblotherEarningDeduction')
            ->Join('tblcvSetup', 'tblotherEarningDeduction.CVID', '=', 'tblcvSetup.ID')
            ->where('tblotherEarningDeduction.year', $year)
            ->where('tblotherEarningDeduction.month', $month)
            ->where('tblotherEarningDeduction.divisionID', $division ? '=' : '<>',  $division)
            ->whereIn('tblotherEarningDeduction.staffid', $allStaffID)
            ->where('tblotherEarningDeduction.particularID', 2)
            ->where('tblotherEarningDeduction.amount', '<>', 0)
            ->orderBy('tblcvSetup.rank')
            ->groupBy('tblotherEarningDeduction.CVID')
            ->select('account_number', 'account_name', DB::raw("SUM(tblotherEarningDeduction.amount) as staffEarnings"), 'description', 'amount', 'tblotherEarningDeduction.CVID', 'tblcvSetup.ID as cvsetupID', 'tblotherEarningDeduction.ID', 'rank', 'tblcvSetup.particularID', 'tblotherEarningDeduction.staffid')
            ->get();
        //===================END EPAYMENT DEDUCTION============================

        return view('payroll.con_epayment.summary', $data);
    } //end class


    public function RetrieveAllBanks(Request $request)
    {


        $month     = trim($request->input('month'));
        $year      = trim($request->input('year'));
        $bankID    = trim($request->input('bankName'));
        $bankGroup = trim($request->input('bankGroup'));
        $division  = trim($request->input('divisionID'));
        $court     = trim($request->input('court'));
        $this->validate($request, [
            'month'     => 'required|regex:/^[\pL\s\-]+$/u',
            'year'      => 'required|integer',
        ]);
        // dd($division);

        $data['monthNumber'] = date('n', strtotime($month));

        $data['bat'] = DB::table('tblbat')
            ->where('year', $year)
            ->where('month', $month)
            ->first();
        $data['lock'] = DB::table('tblpayment_consolidated')->where('salary_lock', '=', 1)->where('month', '=', $month)->where('year', '=', $year)->count();

        $data['month'] = $month;
        Session::put('serialNo', 1);
        Session::put('bankID', $bankID);
        Session::put('bankGroup', $bankGroup);

        $getBank = DB::table('tblbanklist')
            ->where('bankID', $bankID)
            ->first();

        //$bankName = $getBank->bank;
        $bankCode = DB::table('tblbank')
            ->where('bankID', $bankID)
            ->first();

        $data['courtname'] = '';
        $data['courtDivisions']  = DB::table('tbl_court')
            ->join('tbldivision', 'tbldivision.courtID', '=', 'tbl_court.id')
            ->where('tbl_court.id', '=', $court)
            ->where('tbldivision.divisionID', '=', $division)
            ->first();


        $data['epayment_detail'] = DB::table('tblpayment_consolidated')
            ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
            ->leftJoin('tblbacklog', 'tblbacklog.staffid', '=', 'tblpayment_consolidated.staffid')
            ->where('tblpayment_consolidated.month', $month)
            ->where('tblpayment_consolidated.year', $year)
            ->where('tblpayment_consolidated.courtID', $court)
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)
            ->where('tblpayment_consolidated.bankGroup', $bankGroup ? '=' : '<>', $bankGroup)
            ->orderBy('tblbanklist.bank', 'ASC') // ★ alphabetical sorting
            ->orderBy('tblpayment_consolidated.rank', 'DESC')
            ->orderBy('tblpayment_consolidated.name', 'ASC')
            ->select('*', 'tblpayment_consolidated.staffid as staffIDCond')
            ->get();
        $data['epayment_total'] = DB::table('tblpayment_consolidated')
            ->where('month', $month)
            ->where('year', $year)
            ->when($bankID, fn($q) => $q->where('bank', $bankID))
            ->when($division, fn($q) => $q->where('divisionID', $division))
            ->when($bankGroup, fn($q) => $q->where('bankGroup', $bankGroup))
            ->groupBy('bank')
            ->select('bank', DB::raw('SUM(NetPay) as NetPay'))
            ->get();


        // dd(count($data['epayment_detail']));
        $data['unionSum'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.bankGroup', $bankGroup ? '=' : '<>', $bankGroup)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('UD');
        $data['taxSum'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.bankGroup', $bankGroup ? '=' : '<>', $bankGroup)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('TAX');
        $gross = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.bankGroup', $bankGroup ? '=' : '<>', $bankGroup)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('TEarn');
        $data['nhis'] = (5 / 100) * $gross;
        $data['nhf'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.bankGroup', $bankGroup ? '=' : '<>', $bankGroup)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('NHF');
        $data['totalPaySum'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.bankGroup', $bankGroup ? '=' : '<>', $bankGroup)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('NetPay');


        $totalRows = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.month',     '=', $month)
            ->where('tblpayment_consolidated.year',      '=', $year)
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.bankGroup', $bankGroup ? '=' : '<>', $bankGroup)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)
            ->where('tblpayment_consolidated.courtID',  '=', $court)
            ->count();


        // \Log::info($request);

        // dd(3432);
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

        // Fetch account details from tblmandate_address_account
        $data['accountDetails'] = DB::table('tblmandate_address_account')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
            ->join('tblcontractType', 'tblcontractType.ID', '=', 'tblmandate_address_account.contractTypeID')
            ->select(
                'tblmandate_address_account.*',
                'tblbanklist.bank',
                'tblcontractType.contractType'
            )
            ->where('tblmandate_address_account.contractTypeID', 6)
            ->where('tblmandate_address_account.status', 1)
            ->first(); // Use first() since you want only one record
        $data['monthNumber'] = date('n', strtotime($month));

        //===================GET DYNAMIC DEDUCTION ON EPAYMENT=================



        // $allStaffID = $data['epayment_detail']->pluck('staffIDCond')->toArray();
        // $chunks = array_chunk($allStaffID, 500); // 500 IDs per chunk


        // Get all staff IDs in chunks
        // $allStaffID = $data['epayment_detail']->pluck('staffIDCond')->toArray();
        // $chunks = array_chunk($allStaffID, 500); // 500 IDs per chunk
        // $staffDeductionElement = collect();

        // // Fetch deductions in chunks
        // foreach ($chunks as $chunk) {
        //     $staffDeductionElement = $staffDeductionElement->merge(
        //         DB::table('tblotherEarningDeduction')
        //             ->join('tblcvSetup', 'tblotherEarningDeduction.CVID', '=', 'tblcvSetup.ID')
        //             ->join('tblbanklist', 'tblcvSetup.bank', '=', 'tblbanklist.bankID')
        //             ->where('tblotherEarningDeduction.year', $year)
        //             ->where('tblotherEarningDeduction.month', $month)
        //             ->whereIn('tblotherEarningDeduction.staffid', $chunk)
        //             ->where('tblotherEarningDeduction.particularID', 2)
        //             ->where('tblotherEarningDeduction.amount', '>', 0)
        //             ->when($division, fn($q) => $q->where('tblotherEarningDeduction.divisionID', $division))
        //             ->select(
        //                 'tblbanklist.bank AS bank_name',
        //                 'tblcvSetup.account_number',
        //                 'tblcvSetup.description AS beneficiary_name',
        //                 'tblotherEarningDeduction.amount'
        //             )
        //             ->get()
        //     );
        // }

        // // Aggregate duplicates across all chunks
        // $staffDeductionElement = $staffDeductionElement
        //     ->groupBy(function ($item) {
        //         return $item->bank_name . '||' . $item->beneficiary_name . '||' . $item->account_number;
        //     })
        //     ->map(function ($group) {
        //         $first = $group->first();
        //         return (object)[
        //             'bank_name' => $first->bank_name,
        //             'beneficiary_name' => $first->beneficiary_name,
        //             'account_number' => $first->account_number,
        //             'totalDeduction' => $group->sum('amount')
        //         ];
        //     })
        //     ->values();

        // $staffDeductionElement = $staffDeductionElement->sortBy('bank_name')->values();

        $allStaffID = $data['epayment_detail']->pluck('staffIDCond')->toArray();
        $chunks = array_chunk($allStaffID, 500); // handle large data in chunks
        $staffDeductionElement = collect();

        foreach ($chunks as $chunk) {
            $staffDeductionElement = $staffDeductionElement->merge(
                DB::table('tblotherEarningDeduction')
                    ->join('tblcvSetup', 'tblotherEarningDeduction.CVID', '=', 'tblcvSetup.ID')
                    ->join('tblbanklist', 'tblcvSetup.bank', '=', 'tblbanklist.bankID')
                    ->where('tblotherEarningDeduction.year', $year)
                    ->where('tblotherEarningDeduction.month', $month)
                    ->whereIn('tblotherEarningDeduction.staffid', $chunk)
                    ->where('tblotherEarningDeduction.particularID', 2)
                    ->where('tblotherEarningDeduction.amount', '>', 0)
                    ->when($division, fn($q) => $q->where('tblotherEarningDeduction.divisionID', $division))
                    ->select(
                        'tblbanklist.bank AS bank_name',
                        'tblcvSetup.account_number',
                        'tblcvSetup.description AS beneficiary_name',
                        'tblotherEarningDeduction.amount',
                        'tblotherEarningDeduction.CVID'
                    )
                    ->get()
            );
        }

        // Combine CVID 5 and 24 into one using CVID 5 description
        $staffDeductionElement = $staffDeductionElement->map(function ($item) {
            if ($item->CVID == 24) {
                $item->CVID = 5; // assign CVID 5
                $item->beneficiary_name = 'Muslim Women Cooperative'; // description of CVID 5
            }
            return $item;
        });

        // Group by bank_name, beneficiary_name, account_number, and sum amounts
        $staffDeductionElement = $staffDeductionElement
            ->groupBy(function ($item) {
                return $item->bank_name . '||' . $item->beneficiary_name . '||' . $item->account_number;
            })
            ->map(function ($group) {
                $first = $group->first();
                return (object)[
                    'bank_name'        => $first->bank_name,
                    'beneficiary_name' => $first->beneficiary_name,
                    'account_number'   => $first->account_number,
                    'totalDeduction'   => $group->sum('amount'),
                ];
            })
            ->values()
            ->sortBy('bank_name')
            ->values();


        /*==============================================
                NASARAWA TAX (current_state = 30)
        ==============================================*/
        $data['nasarawa_tax'] = DB::table('tblpayment_consolidated')
            ->where('year', $year)
            ->where('month', $month)
            ->select(
                'vstage',
                DB::raw('SUM(CASE WHEN current_state = 30 THEN TAX ELSE 0 END) AS total_tax'),
                DB::raw('SUM(CASE WHEN current_state = 30 THEN TAX ELSE 0 END) AS grand_total')
            )
            ->first();

        $nasarawaTax = $data['nasarawa_tax']->grand_total ?? 0;

        $nasarawaDeduction = (object)[
            'bank_name'        => 'FIRST BANK',
            'beneficiary_name' => 'NASARAWA STATE TAX',
            'account_number'   => '2005675850',
            'purpose'          => 'REVENUE COLLECTION NASARAWA STATE GOVERNMENT',
            'totalDeduction'   => $nasarawaTax,
        ];

        /*==============================================
        NIGER TAX (current_state = 25)
    ==============================================*/
        $data['niger_tax'] = DB::table('tblpayment_consolidated')
            ->where('year', $year)
            ->where('month', $month)
            ->select(
                'vstage',
                DB::raw('SUM(CASE WHEN current_state = 25 THEN TAX ELSE 0 END) AS total_tax'),
                DB::raw('SUM(CASE WHEN current_state = 25 THEN TAX ELSE 0 END) AS grand_total')
            )
            ->first();

        $nigerTax = $data['niger_tax']->grand_total ?? 0;

        $nigerDeduction = (object)[
            'bank_name'        => 'FIRST BANK',
            'beneficiary_name' => 'NIGER STATE TAX',
            'account_number'   => '2005675850',
            'purpose'          => 'NIGER STATE BOARD OF INTERNAL REVENUE',
            'totalDeduction'   => $nigerTax,
        ];

        /*==============================================
        UNION DUES (UD column)
    ==============================================*/
        $data['union_dues'] = DB::table('tblpayment_consolidated')
            ->where('year', $year)
            ->where('month', $month)
            ->sum('UD');

        $unionDues = $data['union_dues'] ?? 0;

        $unionDuesDeduction = (object)[
            'bank_name'        => 'FIRST BANK',
            'beneficiary_name' => 'UNION DUES',
            'account_number'   => '2009822140',
            'purpose'          => 'JUDICIAL STAFF UNION OF NIGERIA',
            'totalDeduction'   => $unionDues,
        ];

        /*==============================================
        PREPEND IN CORRECT ORDER
    ==============================================*/

        $staffDeductionElement->prepend($unionDuesDeduction);
        $staffDeductionElement->prepend($nigerDeduction);
        $staffDeductionElement->prepend($nasarawaDeduction);

        $data['staffDeductionElement'] = $staffDeductionElement;

        // dd($data['staffDeductionElement']);


        //===================END EPAYMENT DEDUCTION============================

        // return view('payroll.con_epayment.summaryAllBanks', $data);
        return Excel::download(new AllBanksExport($data), 'All-Banks-Payroll.xlsx');
    } //end class





    public function RetrieveJustices(Request $request)
    {
        $month     = trim($request->input('month'));
        $year      = trim($request->input('year'));
        $bankID    = trim($request->input('bankName'));
        $bankGroup = trim($request->input('bankGroup'));
        $division  = trim($request->input('divisionID'));
        $court     = trim($request->input('court'));
        $this->validate($request, [
            'month'     => 'required|regex:/^[\pL\s\-]+$/u',
            'year'      => 'required|integer',
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
        $getBank = DB::table('tblbanklist')
            ->where('bankID', $bankID)
            ->first();

        //$bankName = $getBank->bank;
        $bankCode = DB::table('tblbank')
            ->where('bankID', $bankID)
            ->first();

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
            ->orderBy('tblpayment_consolidated.bank', 'DESC')
            ->orderBy('tblpayment_consolidated.rank', 'DESC')
            ->orderBy('tblpayment_consolidated.name', 'ASC')
            ->select('*', 'tblpayment_consolidated.staffid as staffIDCond')
            ->get();

        $data['accountDetails'] = DB::table('tblmandate_address_account')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
            ->join('tblcontractType', 'tblcontractType.ID', '=', 'tblmandate_address_account.contractTypeID')
            ->select(
                'tblmandate_address_account.*',
                'tblbanklist.bank',
                'tblcontractType.contractType'
            )
            ->where('tblmandate_address_account.contractTypeID', 6)
            ->where('tblmandate_address_account.status', 1)
            ->first();
        $data['monthNumber'] = date('n', strtotime($month));
        // dd(count($data['epayment_detail']));
        $data['unionSum'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '=', 2)->sum('UD');
        $data['taxSum'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '=', 2)->sum('TAX');
        $gross = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '=', 2)->sum('TEarn');
        $data['nhis'] = (5 / 100) * $gross;
        $data['nhf'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '=', 2)->sum('NHF');
        $data['totalPaySum'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '=', 2)->sum('NetPay');


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

        $data['M_signatory'] = DB::table('tblmandatesignatory')
            ->leftJoin('tblmandatesignatoryprofiles', 'tblmandatesignatoryprofiles.id', '=', 'tblmandatesignatory.signatoryID')->orderby('tblmandatesignatory.id')->get();
        $data['nhisbal'] = DB::table('tblnhisbalances')
            ->where('month',     '=', $month)
            ->where('year',      '=', $year)
            ->first();
        $data['nhisexist'] = $data['nhisbal'];

        //===================GET DYNAMIC DEDUCTION ON EPAYMENT=================
        $allStaffID = [];
        foreach ($data['epayment_detail'] as $staffID) {
            $allStaffID[] = $staffID->staffIDCond;
        }
        $data['staffDeductionElement'] = DB::table('tblotherEarningDeduction')
            ->Join('tblcvSetup', 'tblotherEarningDeduction.CVID', '=', 'tblcvSetup.ID')
            ->where('tblotherEarningDeduction.year', $year)
            ->where('tblotherEarningDeduction.month', $month)
            ->where('tblotherEarningDeduction.divisionID', $division ? '=' : '<>',  $division)
            ->whereIn('tblotherEarningDeduction.staffid', $allStaffID)
            ->where('tblotherEarningDeduction.particularID', 2)
            ->where('tblotherEarningDeduction.amount', '<>', 0)
            ->orderBy('tblcvSetup.rank')
            ->groupBy('tblotherEarningDeduction.CVID')
            ->select('account_number', 'account_name', DB::raw("SUM(tblotherEarningDeduction.amount) as staffEarnings"), 'description', 'amount', 'tblotherEarningDeduction.CVID', 'tblcvSetup.ID as cvsetupID', 'tblotherEarningDeduction.ID', 'rank', 'tblcvSetup.particularID', 'tblotherEarningDeduction.staffid')
            ->get();
        //===================END EPAYMENT DEDUCTION============================

        // dd($data['staffDeductionElement']);

        return view('payroll.con_epayment.summaryJustices33', $data);
    } //end class retrieve justices


    //Update Account Number and Name with AJAx Call
    public function UpdateControlVariable(Request $request)
    {
        $cvID = $request['cvID'];
        $accName =  $request['accountName'];
        $accNumber = $request['accountNumber'];
        $bank = $request['bank'] ?? '';
        $updated =  DB::table('tblcvSetup')
            ->where('ID',  $cvID)
            ->update([
                'bank' => $bank,
                'account_number' => $accNumber,
                'account_name' => $accName,
            ]);
        if ($updated) {
            $data['message'] = "Record was updated.";
            $data['status'] = 200;
        } else {
            $data['message'] = "Error ! Unable to update record.";
            $data['status'] = 500;
        }
        return $data;
    }



    public function Retrieveget(Request $request)
    {
        $division = $this->division;
        $serialNo = "";
        $pageNO   = "";
        $pageNO   = $request->get('page');
        if (is_null($pageNO)) {
            $serialNo = 1;
        } elseif ($pageNO == 1) {
            $serialNo = 1;
        } else {
            $serialNo = (($pageNO - 1) * 10) + 1;
        }
        Session::put('serialNo', $serialNo);
        $month     = session('month');
        $year      = session('year');
        $bankID    = session('bankID');
        $bankGroup = session('bankGroup');
        $getBank   = DB::table('tblbanklist')
            ->where('bankID', $bankID)
            ->first();
        $data['courtname'] = '';
        $data['courtDivisions']  = DB::table('tbl_court')
            ->join('tbldivision', 'tbldivision.courtID', '=', 'tbl_court.id')
            // ->where('tbl_court.id', '=', $court)
            ->where('tbldivision.divisionID', '=', $division)
            ->first();
        $data['epayment_detail'] = DB::table('tblpayment_consolidated')
            ->join('tblbank', 'tblbank.bankID', '=', 'tblpayment_consolidated.bank')
            ->where('tblpayment_consolidated.month',     '=', $month)
            ->where('tblpayment_consolidated.year',      '=', $year)
            //->where('tblpayment_consolidated.divisionID',  '=', $division)
            // ->where('tblpayment_consolidated.courtID',  '=', $court)
            //->where('tblpayment_consolidated.bank',      '=', $bankID )
            //->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
            // ->where('tblbank.courtID', '=', $court)
            ->orderBy('NetPay', 'DESC')
            ->orderBy('name', 'ASC')
            ->paginate(10);
        // dd($data['epayment_detail']);
        $data['epayment_total'] = DB::table('tblpayment_consolidated')

            ->where('tblpayment_consolidated.month',     '=', $month)
            ->where('tblpayment_consolidated.year',      '=', $year)
            //->where('tblpayment_consolidated.divisionID',  '=', $division)
            // ->where('tblpayment_consolidated.courtID',  '=', $court)
            //->where('tblpayment_consolidated.bank',      '=', $bankID )
            // ->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
            ->orderBy('NetPay', 'DESC')
            ->orderBy('name', 'ASC')
            ->get();
        $totalRows = DB::table('tblpayment_consolidated')

            ->where('tblpayment_consolidated.month',     '=', $month)
            ->where('tblpayment_consolidated.year',      '=', $year)
            //->where('tblpayment_consolidated.divisionID',  '=', $division)
            // ->where('tblpayment_consolidated.courtID',  '=', $court)
            //->where('tblpayment_consolidated.bank',      '=', $bankID )
            //->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
            ->count();


        $max_row    = 10;
        $totalPages = ceil($totalRows / $max_row);
        if ($pageNO  == $totalPages) {
            Session::put('showTotal', "yes");
        } else {
            Session::put('showTotal', "");
        }


        return view('payroll.con_epayment.summary', $data);
    }

    public function getPhone(Request $request)
    {
        $id = $request['signid'];
        $val = DB::table('tblsignatory')
            ->where('signatoryID', '=', $id)
            ->first();
        return response()->json($val);
    }
    public function tests(Request $request)
    {


        return view('payroll.epayment.test');
    }

    public function test(Request $request)
    {
        $id = $request['signid'];
        $val = DB::table('tblsignatory')
            ->where('signatoryID', '=', $id)
            ->first();
        return response()->json($val);
    }





    public function indexNew()
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

        if (count($data['CourtInfo']) > 0) {

            $data['courtDivisions']  = DB::table('tbldivision')
                // ->where('courtID', '=', $courtSessionId)
                ->get();
            $data['curDivision'] = $this->curDivision(Auth::user()->id);
            $data['allbanklist']  = DB::table('tblbanklist')->get();
        }
        //   dd($data);
        return view('payroll.con_epayment.indexMandate', $data);
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

    public function RetrieveNew(Request $request)
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
            //'bankName'  => 'required|integer',
            //'bankGroup' => 'required|integer'
        ]);
        $data['year']  = $year;
        $data['month'] = $month;
        $data['bat'] = DB::table('tblbat')
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        //if no data is retrieved in $data['bat'] run a different query
        if ($data['bat'] === ' ' || $data['bat'] === null) {
            $data['bat'] = DB::table('tblbat')
                ->where('tblbat.year', $year)
                ->where('tblbat.month', $month)
                ->first();
        }

        // dd($data['bat']);


        $data['lock'] = DB::table('tblpayment_consolidated')->where('salary_lock', '=', 1)->where('month', '=', $month)->where('year', '=', $year)->count();

        $data['xyz1'] = DB::table('tblotherEarningDeduction')->where('CVID', '=', 3)->where('year', '=', $year)->where('month', '=', $month)->sum('amount');
        $data['xyz2'] = DB::table('tblotherEarningDeduction')->where('CVID', '=', 13)->where('year', '=', $year)->where('month', '=', $month)->sum('amount');
        $data['xyz3'] = DB::table('tblotherEarningDeduction')->where('CVID', '=', 17)->where('year', '=', $year)->where('month', '=', $month)->sum('amount');
        $data['xyz4'] = ($data['xyz1'] + $data['xyz2'] + $data['xyz3']) * 0.05;
        //$data['nhisNew'] = round(DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('TEarn')*0.05-$data['xyz4'],2);
        $percentages = DB::SELECT("SELECT * FROM `tbldeduction_percentage`")[0];
        $data['nhisNew'] = round((DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('TEarn') -
            DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('PEC') -
            DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('SOT'))

            * $percentages->nhis * 0.01, 2);
        //dd($data['nhisNew']);
        //dd($data['xyz4']);

        $data['month'] = $month;
        Session::put('serialNo', 1);
        Session::put('bankID', $bankID);
        Session::put('bankGroup', $bankGroup);
        $getBank = DB::table('tblbanklist')
            ->where('bankID', $bankID)
            ->first();

        //$bankName = $getBank->bank;
        $bankCode = DB::table('tblbank')
            ->where('bankID', $bankID)
            ->first();

        $data['courtname'] = '';
        $data['courtDivisions']  = DB::table('tbl_court')
            ->join('tbldivision', 'tbldivision.courtID', '=', 'tbl_court.id')
            ->where('tbl_court.id', '=', $court)
            ->where('tbldivision.divisionID', '=', $division)
            ->first();
        $data['alhisan'] = DB::table('tblotherEarningDeduction')->where('CVID', '=', 31)->where('year', '=', $year)->where('month', '=', $month)->sum('amount');
        $data['alhisansLoan'] = DB::table('tblotherEarningDeduction')->where('month', '=', $month)->where('year', '=', $year)->where('CVID', '=', 33)->sum('amount');
        $data['alhisanAcct'] = DB::table('tblalhisan_accounts')->first();

        $data['nhisAcct'] = DB::table('tblnhis_account')->first();

        if ($bankID == '' and $division == '') {

            $data['epayment_detail'] = DB::table('tblpayment_consolidated')
                ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
                ->leftJoin('tblbacklog', 'tblbacklog.staffid', '=', 'tblpayment_consolidated.staffid', 'tblbacklog.month', '=', $month, 'tblbacklog.year', '=', $year)
                ->where('tblpayment_consolidated.month',     '=', $month)
                ->where('tblpayment_consolidated.year',      '=', $year)
                //->where('tblpayment_consolidated.divisionID',  '=', $division)
                ->where('tblpayment_consolidated.courtID',  '=', $court)
                //->where('tblpayment_consolidated.bank',      '=', $bankID )
                //->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
                //->where('tblbank.courtID', '=',$court)
                //->orderBy('tblpayment_consolidated.grade','DESC')
                //->orderBy('tblpayment_consolidated.step','DESC')
                ->where('tblpayment_consolidated.rank', '!=', 2)
                ->orderBy('tblpayment_consolidated.bank', 'DESC')
                ->orderBy('tblpayment_consolidated.rank', 'DESC')
                ->orderBy('tblpayment_consolidated.name', 'ASC')
                ->get();

            $data['taxPayee'] = DB::table('tblcurrent_state')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblcurrent_state.bank')
                ->select('*', 'tblbanklist.bank as bankname')
                ->where('tblcurrent_state.status', '=', 1)->get();

            //dd($data['epayment_detail']);
            $data['coopSumSaving'] = DB::table('tblotherEarningDeduction')->where('CVID', '=', 15)->where('year', '=', $year)->where('month', '=', $month)->sum('amount');
            $data['coopSumLoan'] = DB::table('tblotherEarningDeduction')->where('CVID', '=', 16)->where('year', '=', $year)->where('month', '=', $month)->sum('amount');
            $data['refundSum'] = DB::table('tblotherEarningDeduction')->where('CVID', '=', 2)->where('year', '=', $year)->where('month', '=', $month)->sum('amount');
            $data['unionSum'] = DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('UD');
            $data['taxSum'] = DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('TAX');
            //$gross = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('TEarn');
            $gross = DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('gross');
            $sot = DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('SOT');
            //$data['nhis'] = (5/100) * $gross;
            $basic = DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('Bs');
            $exceptThisGross = DB::table('tblpayment_consolidated')->where('staffid', '=', 385)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('gross');
            $exceptThisSOT = DB::table('tblpayment_consolidated')->where('staffid', '=', 385)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('SOT');



            //$data['nhisNew'] = (5.25/100) * $basic;
            //dd($data['nhis']);
            if ($year > 2019) {
                $totalGross = ($gross) - ($exceptThisGross);

                $data['nhisNew'] = (5 / 100) * ($totalGross);
                //$data['nhisNew'] = (5/100) * $basic;
                //$data['nhisNew'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('NHIS') ;//- $data['xyz4'];
                //dd($data['nhisNew']);

            } else {
                $data['nhisNew'] = (5 / 100) * ($gross + $sot);
            }

            $data['totalPaySum'] = DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('NetPay');
        } elseif ($division != '' && $bankID == '') {

            $data['epayment_detail'] = DB::table('tblpayment_consolidated')
                ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
                ->leftJoin('tblbacklog', 'tblbacklog.staffid', '=', 'tblpayment_consolidated.staffid', 'tblbacklog.month', '=', $month, 'tblbacklog.year', '=', $year)
                ->where('tblpayment_consolidated.month',     '=', $month)
                ->where('tblpayment_consolidated.year',      '=', $year)
                ->where('tblpayment_consolidated.divisionID',  '=', $division)
                ->where('tblpayment_consolidated.courtID',  '=', $court)
                //->where('tblpayment_consolidated.bank',      '=', $bankID )
                //->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
                //->where('tblbank.courtID', '=',$court)
                //->orderBy('tblpayment_consolidated.grade','DESC')
                //->orderBy('tblpayment_consolidated.step','DESC')
                ->where('tblpayment_consolidated.rank', '!=', 2)
                ->orderBy('tblpayment_consolidated.bank', 'DESC')
                ->orderBy('tblpayment_consolidated.rank', 'DESC')
                ->orderBy('tblpayment_consolidated.name', 'ASC')
                ->get();

            $data['taxPayee'] = DB::table('tblcurrent_state')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblcurrent_state.bank')
                ->select('*', 'tblbanklist.bank as bankname')
                ->where('tblcurrent_state.status', '=', 1)->get();

            //dd($data['epayment_detail']);
            $data['coopSumSaving'] = DB::table('tblotherEarningDeduction')->where('CVID', '=', 15)->where('year', '=', $year)->where('month', '=', $month)->sum('amount');
            $data['coopSumLoan'] = DB::table('tblotherEarningDeduction')->where('CVID', '=', 16)->where('year', '=', $year)->where('month', '=', $month)->sum('amount');
            $data['refundSum'] = DB::table('tblotherEarningDeduction')->where('CVID', '=', 2)->where('year', '=', $year)->where('month', '=', $month)->sum('amount');
            $data['unionSum'] = DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('UD');
            $data['taxSum'] = DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('TAX');
            //$gross = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('TEarn');
            $gross = DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('gross');
            $sot = DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('SOT');
            //$data['nhis'] = (5/100) * $gross;
            $basic = DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('Bs');
            $exceptThisGross = DB::table('tblpayment_consolidated')->where('staffid', '=', 385)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('gross');
            $exceptThisSOT = DB::table('tblpayment_consolidated')->where('staffid', '=', 385)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('SOT');



            //$data['nhisNew'] = (5.25/100) * $basic;
            //dd($data['nhis']);
            if ($year > 2019) {
                $totalGross = ($gross) - ($exceptThisGross);

                $data['nhisNew'] = (5 / 100) * ($totalGross);
                //$data['nhisNew'] = (5/100) * $basic;
                //$data['nhisNew'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('NHIS') ;//- $data['xyz4'];
                //dd($data['nhisNew']);

            } else {
                $data['nhisNew'] = (5 / 100) * ($gross + $sot);
            }
            $data['totalPaySum'] = DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('NetPay');
        } else {

            $data['taxPayee'] = DB::table('tblcurrent_state')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblcurrent_state.bank')
                ->select('*', 'tblbanklist.bank as bankname')
                ->where('tblcurrent_state.status', '=', 1)->get();

            $data['epayment_detail'] = DB::table('tblpayment_consolidated')
                ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
                ->leftJoin('tblbacklog', 'tblbacklog.staffid', '=', 'tblpayment_consolidated.staffid', 'tblbacklog.month', '=', $month, 'tblbacklog.year', '=', $year)
                ->where('tblpayment_consolidated.month',     '=', $month)
                ->where('tblpayment_consolidated.year',      '=', $year)
                ->where('tblpayment_consolidated.courtID',  '=', $court)
                ->where('tblpayment_consolidated.bank', '=', $bankID)
                //->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
                //->where('tblbank.courtID', '=',$court)
                //->orderBy('tblpayment_consolidated.grade','DESC')
                //->orderBy('tblpayment_consolidated.step','DESC')
                ->where('tblpayment_consolidated.rank', '!=', 2)
                ->orderBy('tblpayment_consolidated.bank', 'DESC')
                ->orderBy('tblpayment_consolidated.rank', 'DESC')
                ->orderBy('tblpayment_consolidated.name', 'ASC')
                ->get();

            $data['coopSumSaving'] = DB::table('tblotherEarningDeduction')->where('CVID', '=', 15)->where('year', '=', $year)->where('month', '=', $month)->sum('amount');
            $data['coopSumLoan'] = DB::table('tblotherEarningDeduction')->where('CVID', '=', 16)->where('year', '=', $year)->where('month', '=', $month)->sum('amount');
            $data['refundSum'] = DB::table('tblotherEarningDeduction')->where('CVID', '=', 2)->where('year', '=', $year)->where('month', '=', $month)->sum('amount');
            $data['unionSum'] = DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('UD');
            $data['taxSum'] = DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('TAX');
            //$gross = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('TEarn');
            $gross = DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('gross');
            $sot = DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('SOT');
            $basic = DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('Bs');

            $exceptThisGross = DB::table('tblpayment_consolidated')->where('staffid', '=', 385)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('gross');
            $exceptThisSOT = DB::table('tblpayment_consolidated')->where('staffid', '=', 385)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('SOT');
            //$data['nhis'] = (5.25/100) * $basic;
            if ($year > 2019) {
                /*$data['nhisNew'] = round((DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('TEarn') -
            DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('PEC')-
            DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('SOT'))

            *$percentages->nhis*0.01,2);*/
                $totalGross = ($gross + $sot) - ($exceptThisGross + $exceptThisSOT);

                $data['nhisNew'] = (5 / 100) * ($totalGross);
                // $data['nhisNew'] = DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('NHIS') - $data['xyz4'];
            } else {
                //$data['nhisNew'] = (5/100) * $gross;
            }
            $data['totalPaySum'] = DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('NetPay');
        } // end else


        $data['epayment_total'] = DB::table('tblpayment_consolidated')

            ->where('tblpayment_consolidated.month',     '=', $month)
            ->where('tblpayment_consolidated.year',      '=', $year)
            //->where('tblpayment_consolidated.divisionID',  '=', $division)
            ->where('tblpayment_consolidated.courtID',  '=', $court)
            //->where('tblpayment_consolidated.bank',      '=', $bankID )
            // ->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
            ->orderBy('tblpayment_consolidated.grade', 'DESC')
            //->orderBy('tblpayment_consolidated.step','DESC')
            ->orderBy('tblpayment_consolidated.bank', 'DESC')
            ->orderBy('tblpayment_consolidated.rank', 'DESC')
            ->orderBy('tblpayment_consolidated.name', 'ASC')
            ->get();

        $data['coopSumSaving'] = DB::table('tblotherEarningDeduction')->where('CVID', '=', 15)->where('year', '=', $year)->where('month', '=', $month)->sum('amount');
        $data['coopSumLoan'] = DB::table('tblotherEarningDeduction')->where('CVID', '=', 16)->where('year', '=', $year)->where('month', '=', $month)->sum('amount');
        $data['refundSum'] = DB::table('tblotherEarningDeduction')->where('CVID', '=', 2)->where('year', '=', $year)->where('month', '=', $month)->sum('amount');
        $data['unionSum'] = DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('UD');
        $data['taxSum'] = DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('TAX');
        $gross = DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('Bs');
        $data['nhis'] = (5 / 100) * $gross;
        $data['totalPaySum'] = DB::table('tblpayment_consolidated')->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('NetPay');

        $totalRows = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.month',     '=', $month)
            ->where('tblpayment_consolidated.year',      '=', $year)
            //->where('tblpayment_consolidated.divisionID',  '=', $division)
            ->where('tblpayment_consolidated.courtID',  '=', $court)
            //->where('tblpayment_consolidated.bank',      '=', $bankID )
            //->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
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
        //Session::put('bank', $bankName ." ".$bankGroup);

        //DD($data['epayment_detail']);
        $data['M_signatory'] = DB::table('tblmandatesignatory')
            ->leftJoin('tblmandatesignatoryprofiles', 'tblmandatesignatoryprofiles.id', '=', 'tblmandatesignatory.signatoryID')->orderby('tblmandatesignatory.id')->get();
        //dd($data['epayment_detail']);

        //return view('con_epayment.summary200319', $data);
        //dd($data['epayment_detail']);
        $data['nhisbal'] = DB::table('tblnhisbalances')
            ->where('month',     '=', $month)
            ->where('year',      '=', $year)
            ->first();
        $data['nhisexist'] = count($data['nhisbal']);


        // Passing Mandate Accounts data
        $data['mandateAccounts'] = DB::table('tblcurrent_state')
            ->leftjoin('tblbanklist', 'tblcurrent_state.bank', '=', 'tblbanklist.bankID')
            ->select('tblcurrent_state.*', 'tblbanklist.bank')
            ->orderBy('rank', 'ASC')
            ->get();


        //$d = ' + '.$data['nhisbal']->amount;
        //DD($d);
        //$data['nhisNew'] = round(DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->where('rank','!=',2)->sum('TEarn')*0.05-$data['xyx4'],2);

        return view('payroll.con_epayment.mandate', $data);
    }



    //Go to generate Mandate Account page
    public function generateMandateAccountPage(Request $request)
    {
        // Getting Banks
        $data['allbanklist']  = DB::table('tblbanklist')->get();


        //Getting states
        $data['statelist'] = DB::table('tblstates')->orderBy('State', 'Asc')->get();

        // Getting mandate accounts details
        $data['mandatesAccounts'] = DB::table('tblcurrent_state')
            ->leftjoin('tblbanklist', 'tblcurrent_state.bank', '=', 'tblbanklist.bankID')
            ->select('tblcurrent_state.*', 'tblbanklist.bank')
            ->get();

        return view('payroll.con_epayment.mandateAccount', $data);
    }

    //Generate Mandate Account
    public function generateMandateAccount(Request $request)
    {
        // Saving Data
        DB::table('tblcurrent_state')->insert([
            'bank' => $request->bankName,
            'account_no' => $request->accountNumber,
            'address' => $request->beneficiary,
            'rank' => $request->rank,
            'deduction_caption' => $request->description,
            'state' => $request->state
        ]);

        return redirect()->back()->with('message', 'Mandate account was created');
    }

    //Updaate Mandate Account
    public function updateMandateAccount(Request $request)
    {
        DB::table('tblcurrent_state')
            ->where('id', $request->id)
            ->update([
                'bank' => $request->bankName,
                'account_no' => $request->accountNumber,
                'address' => $request->beneficiary,
                'rank' => $request->rank,
                'deduction_caption' => $request->description,
                'state' => $request->state
            ]);

        return redirect()->back()->with('message', 'Mandate account was updated');
    }

    //Delete Mandate Account
    public function deleteMandateAccount(Request $request)
    {
        DB::table('tblcurrent_state')
            ->where('id', $request->mandateID)
            ->delete();

        return redirect()->back()->with('message', 'Mandate was deleted');
    }

    //Summary of payroll earnings and deductions for a particular month and year by bank
    public function earningAndDeductionByBank()
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
            //->select('tbldivision.divisionID', 'tbldivision.division')
            //  ->where('courtID', '=', $courtSessionId)
            ->get();
        //dd($data['courtDivisions'] );
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        if ($data['CourtInfo']) {

            $data['allbanklist']  = DB::table('tblbanklist')->orderBy('tblbanklist.bank', 'Asc')


                ->get();
        }
        return view('payroll.con_epayment.earningAndDeduction', $data);
    }
    public function justiceEarningAndDeductionByBank_22_02_2026()
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
            //->select('tbldivision.divisionID', 'tbldivision.division')
            //  ->where('courtID', '=', $courtSessionId)
            ->get();
        //dd($data['courtDivisions'] );
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        if ($data['CourtInfo']) {

            $data['allbanklist']  = DB::table('tblbanklist')


                ->get();
        }
        return view('payroll.con_epayment.justiceEarningAndDeduction', $data);
    }
    public function justiceEarningAndDeductionByBank()
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
            //->select('tbldivision.divisionID', 'tbldivision.division')
            //  ->where('courtID', '=', $courtSessionId)
            ->get();
        //dd($data['courtDivisions'] );
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        if ($data['CourtInfo']) {

            $data['allbanklist'] = DB::table('tblbanklist')
                ->join('tblpayment_consolidated', 'tblpayment_consolidated.bank', '=', 'tblbanklist.bankID')
                ->where('tblpayment_consolidated.rank', '=', 2)
                ->groupBy('tblpayment_consolidated.bank')
                ->orderBy('tblbanklist.bank', 'Asc')
                ->select('tblbanklist.bank', 'tblbanklist.bankID')
                ->get();
        }
        return view('payroll.con_epayment.justiceEarningAndDeduction', $data);
    }



    //Receive earning and deduction report


    public function retrieveEarningAndDeduction(Request $request)
    {
        $request->validate([
            'month'     => 'required|regex:/^[\pL\s\-]+$/u',
            'year'      => 'required|integer',
            'bankName'  => 'required'
        ]);

        $month     = trim($request->month);
        $year      = trim($request->year);
        $bankID    = trim($request->bankName);
        $division  = trim($request->division);
        $court     = trim($request->court);

        // Get names
        $courtName    = DB::table('tbl_court')->where('id', $court)->first();
        $divisionName = DB::table('tbldivision')->where('divisionID', $division)->first();
        $bankName     = DB::table('tblbanklist')->where('bankID', $bankID)->first();


        // MAIN consolidated base query (rank != 2)
        $consolidated = DB::table('tblpayment_consolidated')
            ->where('year', $year)
            ->where('month', $month)
            ->where('bank', $bankID)
            ->where('rank', '!=', 2);


        // FUNCTION to compute earning/deduction amount
        $otherED = function ($cvid, $particular, $cvtype) use ($year, $month, $bankID) {

            // staff for this cvID + cvtype
            $staffIDs = DB::table('tblstaffCV')
                ->where('cvID', $cvid)
                ->where('cvtype', $cvtype)
                ->pluck('staffid');

            return DB::table('tblotherEarningDeduction as oed')
                ->where('oed.particularID', $particular)
                ->where('oed.CVID', $cvid)
                ->where('oed.year', $year)
                ->where('oed.month', $month)
                ->whereIn('oed.staffid', $staffIDs)

                // Must exist in consolidated for same month/year/bank
                ->whereIn('oed.staffid', function ($q) use ($year, $month, $bankID) {
                    $q->select('staffid')
                        ->from('tblpayment_consolidated')
                        ->where('year', $year)
                        ->where('month', $month)
                        ->where('bank', $bankID)
                        ->where('rank', '!=', 2);
                })

                ->sum('oed.amount');
        };

        // GET ALL CV SETUP rows dynamically
        $cvSetups = DB::table('tblcvSetup')->get();

        $dynamicEarnings   = [];
        $dynamicDeductions = [];

        foreach ($cvSetups as $cv) {

            $cvid       = $cv->ID;
            $particular = $cv->particularID;   // 1 = earning, 2 = deduction
            $cvtype     = $cv->particularID;   // mapping rule
            $desc       = $cv->description;

            $amount = $otherED($cvid, $particular, $cvtype);

            if ($particular == 1) {
                $dynamicEarnings[] = [
                    'description' => $desc,
                    'amount'      => $amount
                ];
            }

            if ($particular == 2) {
                $dynamicDeductions[] = [
                    'description' => $desc,
                    'amount'      => $amount
                ];
            }
        }

        // ---- FIXED SUMS FROM CONSOLIDATED ----
        $sumBs    = $consolidated->sum('Bs');
        $sumPecfg = $consolidated->sum('PECFG');
        $sumPec   = $consolidated->sum('PEC');
        $arrears  = $consolidated->sum('AEarn');

        $nhf = $consolidated->sum('NHF');
        $pen = $consolidated->sum('PEN');
        $tax = $consolidated->sum('TAX');
        $ud  = $consolidated->sum('UD');

        // ---- CALCULATE TOTAL EARNINGS ----
        $totalDynamicEarnings = array_sum(array_column($dynamicEarnings, 'amount'));

        $totalEarnings =
            $sumBs +
            $sumPecfg +
            $sumPec +
            $arrears +
            $totalDynamicEarnings;

        // ---- CALCULATE TOTAL DEDUCTIONS ----
        $totalDynamicDeductions = array_sum(array_column($dynamicDeductions, 'amount'));

        $totalDeductions =
            $nhf +
            $pen +
            $tax +
            $ud +
            $totalDynamicDeductions;

        // ---- NET EMOLUMENT ----
        $netEmolument = $totalEarnings - $totalDeductions;


        // Final Data to View
        $data = [

            // fixed earnings
            'sumBs'    => $sumBs,
            'sumPecfg' => $sumPecfg,
            'sumPec'   => $sumPec,
            'arrears'  => $arrears,

            // fixed deductions
            'nhf' => $nhf,
            'pen' => $pen,
            'tax' => $tax,
            'ud'  => $ud,

            // dynamic earning/deductions
            'dynamicEarnings'   => $dynamicEarnings,
            'dynamicDeductions' => $dynamicDeductions,

            // totals
            'totalEarnings'     => $totalEarnings,
            'totalDeductions'   => $totalDeductions,
            'netEmolument'      => $netEmolument,

            // Names
            'courtName' => $courtName->court_name,
            'division'  => $divisionName,
            'bankName'  => $bankName->bank,

            // Date
            'month' => $month,
            'year'  => $year,
        ];

        return view('payroll.con_epayment.summaryEarningAndDeduction', $data);
    }
    public function retrieveJusticeEarningAndDeduction(Request $request)
    {
        $request->validate([
            'month'     => 'required|regex:/^[\pL\s\-]+$/u',
            'year'      => 'required|integer',
            'bankName'  => 'required'
        ]);

        $month     = trim($request->month);
        $year      = trim($request->year);
        $bankID    = trim($request->bankName);
        $division  = trim($request->division);
        $court     = trim($request->court);

        // Get names
        $courtName    = DB::table('tbl_court')->where('id', $court)->first();
        $divisionName = DB::table('tbldivision')->where('divisionID', $division)->first();
        $bankName     = DB::table('tblbanklist')->where('bankID', $bankID)->first();


        // MAIN consolidated base query (rank != 2)
        $consolidated = DB::table('tblpayment_consolidated')
            ->where('year', $year)
            ->where('month', $month)
            ->where('bank', $bankID)
            ->where('rank', 2);


        // FUNCTION to compute earning/deduction amount
        $otherED = function ($cvid, $particular, $cvtype) use ($year, $month, $bankID) {

            // staff for this cvID + cvtype
            $staffIDs = DB::table('tblstaffCV')
                ->where('cvID', $cvid)
                ->where('cvtype', $cvtype)
                ->pluck('staffid');

            return DB::table('tblotherEarningDeduction as oed')
                ->where('oed.particularID', $particular)
                ->where('oed.CVID', $cvid)
                ->where('oed.year', $year)
                ->where('oed.month', $month)
                ->whereIn('oed.staffid', $staffIDs)

                // Must exist in consolidated for same month/year/bank
                ->whereIn('oed.staffid', function ($q) use ($year, $month, $bankID) {
                    $q->select('staffid')
                        ->from('tblpayment_consolidated')
                        ->where('year', $year)
                        ->where('month', $month)
                        ->where('bank', $bankID)
                        ->where('rank',  2);
                })

                ->sum('oed.amount');
        };

        // GET ALL CV SETUP rows dynamically
        $cvSetups = DB::table('tblcvSetup')->get();

        $dynamicEarnings   = [];
        $dynamicDeductions = [];

        foreach ($cvSetups as $cv) {

            $cvid       = $cv->ID;
            $particular = $cv->particularID;   // 1 = earning, 2 = deduction
            $cvtype     = $cv->particularID;   // mapping rule
            $desc       = $cv->description;

            $amount = $otherED($cvid, $particular, $cvtype);

            if ($particular == 1) {
                $dynamicEarnings[] = [
                    'description' => $desc,
                    'amount'      => $amount
                ];
            }

            if ($particular == 2) {
                $dynamicDeductions[] = [
                    'description' => $desc,
                    'amount'      => $amount
                ];
            }
        }

        // ---- FIXED SUMS FROM CONSOLIDATED ----
        $sumBs    = $consolidated->sum('Bs');
        $sumPecfg = $consolidated->sum('PECFG');
        $sumPec   = $consolidated->sum('PEC');
        $arrears  = $consolidated->sum('AEarn');

        $nhf = $consolidated->sum('NHF');
        $pen = $consolidated->sum('PEN');
        $tax = $consolidated->sum('TAX');
        $ud  = $consolidated->sum('UD');

        // ---- CALCULATE TOTAL EARNINGS ----
        $totalDynamicEarnings = array_sum(array_column($dynamicEarnings, 'amount'));

        $totalEarnings =
            $sumBs +
            $sumPecfg +
            $sumPec +
            $arrears +
            $totalDynamicEarnings;

        // ---- CALCULATE TOTAL DEDUCTIONS ----
        $totalDynamicDeductions = array_sum(array_column($dynamicDeductions, 'amount'));

        $totalDeductions =
            $nhf +
            $pen +
            $tax +
            $ud +
            $totalDynamicDeductions;

        // ---- NET EMOLUMENT ----
        $netEmolument = $totalEarnings - $totalDeductions;


        // Final Data to View
        $data = [

            // fixed earnings
            'sumBs'    => $sumBs,
            'sumPecfg' => $sumPecfg,
            'sumPec'   => $sumPec,
            'arrears'  => $arrears,

            // fixed deductions
            'nhf' => $nhf,
            'pen' => $pen,
            'tax' => $tax,
            'ud'  => $ud,

            // dynamic earning/deductions
            'dynamicEarnings'   => $dynamicEarnings,
            'dynamicDeductions' => $dynamicDeductions,

            // totals
            'totalEarnings'     => $totalEarnings,
            'totalDeductions'   => $totalDeductions,
            'netEmolument'      => $netEmolument,

            // Names
            'courtName' => $courtName->court_name,
            'division'  => $divisionName,
            'bankName'  => $bankName->bank,

            // Date
            'month' => $month,
            'year'  => $year,
        ];

        return view('payroll.con_epayment.summaryJusticeEarningAndDeduction', $data);
    }


    public function exportEpaymentExcel(Request $request)
    {
        // Pull same filters (prefer request, fallback to session)
        $month     = trim($request->get('month', Session::get('month')));
        $year      = trim($request->get('year', Session::get('year')));
        $bankID    = trim($request->get('bankName', Session::get('bankID')));
        $bankGroup = trim($request->get('bankGroup', Session::get('bankGroup')));
        $division  = trim($request->get('divisionID', Session::get('divisionID')));
        $court     = trim($request->get('court', Session::get('court')));

        if (!$month || !$year) {
            abort(422, "Month and Year are required for export.");
        }

        $monthNumber = date('n', strtotime($month));
        $monthName   = Carbon::create()->month((int)$monthNumber)->format('F');

        // ✅ Your account details source (same as Retrieve)
        $accountDetails = DB::table('tblmandate_address_account')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
            ->join('tblcontractType', 'tblcontractType.ID', '=', 'tblmandate_address_account.contractTypeID')
            ->select('tblmandate_address_account.*', 'tblbanklist.bank', 'tblcontractType.contractType')
            ->where('tblmandate_address_account.contractTypeID', 6)
            ->where('tblmandate_address_account.status', 1)
            ->first();

        // ✅ Your exact epayment query (same filters as Retrieve)
        $epayment_detail = DB::table('tblpayment_consolidated')
            ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
            ->leftJoin('tblbacklog', 'tblbacklog.staffid', '=', 'tblpayment_consolidated.staffid') // keep as you have it
            ->where('tblpayment_consolidated.month', '=', $month)
            ->where('tblpayment_consolidated.year', '=', $year)
            ->where('tblpayment_consolidated.courtID', '=', $court)
            ->where('tblpayment_consolidated.bank', $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)
            ->where('tblpayment_consolidated.bankGroup', $bankGroup ? '=' : '<>', $bankGroup)
            ->where('tblpayment_consolidated.rank', '!=', 2)
            ->orderBy('tblpayment_consolidated.bank', 'DESC')
            ->orderBy('tblpayment_consolidated.rank', 'DESC')
            ->orderBy('tblpayment_consolidated.name', 'ASC')
            ->select(
                'tblpayment_consolidated.name',
                'tblpayment_consolidated.bank',
                'tblpayment_consolidated.bank_branch',
                'tblpayment_consolidated.AccNo',
                'tblpayment_consolidated.NetPay'
            )
            ->get();

        // Build Excel rows (same subtotal logic)
        $rows = [];
        $rows[] = ['S/N', 'BENEFICIARY', 'BANK', 'BRANCH', 'ACC NUMBER', 'AMOUNT (₦)', 'PURPOSE OF PAYMENT'];

        $sn = 1;
        $subTotal = 0;
        $total = 0;
        $currentBank = null;

        foreach ($epayment_detail as $r) {
            // if bank changes → add subtotal row
            if ($currentBank !== null && $currentBank !== $r->bank) {
                $rows[] = ['', '', '', '', 'Sub Total', $subTotal, ''];
                $subTotal = 0;
            }

            $currentBank = $r->bank;
            $subTotal += (float) $r->NetPay;
            $total += (float) $r->NetPay;

            $rows[] = [
                $sn++,
                $r->name,
                $r->bank,
                $r->bank_branch,
                (string) $r->AccNo,
                (float) $r->NetPay,
                "{$month} {$year} Staff Salary",
            ];
        }

        // final subtotal + total
        $rows[] = ['', '', '', '', 'Sub Total', $subTotal, ''];
        $rows[] = ['', '', '', '', 'TOTAL', $total, ''];

        $meta = [
            'account_no' => $accountDetails->account_no ?? 'N/A',
            'ref_no'     => "SCN/SALPE/{$monthNumber}/{$year}",
            'title'      => 'E-PAYMENT SCHEDULE',
        ];

        $fileName = "epayment_schedule_{$monthNumber}_{$year}.xlsx";

        return Excel::download(new EPaymentScheduleExport($rows, $meta), $fileName);
    }
}
