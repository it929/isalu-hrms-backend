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
use App\Http\Controllers\payroll\ParentController;


class SummaryController extends ParentController
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

    public function create()
    {
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }
        $data['courts'] =  DB::table('tbl_court')->get();
        $courtSessionId = session('anycourt');
        $data['courtDivisions']  = DB::table('tbldivision')
            ->where('courtID', '=', $courtSessionId)
            ->get();


        $data['courtDivisions']  = DB::table('tbldivision')
            //  ->where('courtID', '=', $courtSessionId)
            ->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        // $data['allbanklist']  = DB::table('tblbanklist')
        //     ->orderBy('tblbanklist.bank', 'Asc')
        //     ->get();

        $data['allbanklist'] = DB::table('tblbanklist')
                ->join('tblpayment_consolidated', 'tblpayment_consolidated.bank', '=', 'tblbanklist.bankID')
                ->where('tblpayment_consolidated.rank', '!=', 2)
                ->groupBy('tblpayment_consolidated.bank')
                ->orderBy('tblbanklist.bank', 'Asc')
                ->select('tblbanklist.bank', 'tblbanklist.bankID')
                ->get();

        //   dd($data);

        return view('payroll.summary.index', $data);
    }

    public function Retrieve(Request $request)
    {

        $month     = trim($request->input('month'));
        $year      = trim($request->input('year'));
        $bankID    = trim($request->input('bankName'));
        $bankGroup = trim($request->input('bankGroup'));
        //$warrant   = trim($request->input('warrant'));
        $division  = trim($request->input('division'));
        $court    = trim($request->input('court'));
        $divisionDetails = DB::table('tbldivision')->where('divisionID', $division)->first(['division', 'abbrv']);
        $data['divisionName'] = ($divisionDetails ? $divisionDetails->division : '');
        $data['divisionAbbr'] = ($divisionDetails ? $divisionDetails->abbrv : '');
        $data['year'] = $year;
        $data['month'] = $month;

        $this->validate($request, [
            'month'     => 'required|string',
            'year'      => 'required|integer',
            //'bankName'  => 'required|integer',
            //'bankGroup' => 'required|integer',
            //'warrant'   => 'required|regex:/[a-zA-Z.]/'
        ]);
        $getBank  = DB::table('tblbanklist')
            ->where('bankID', $bankID)
            ->first();
        if ($bankID != '') {
            $bankName = $getBank->bank;
        }


        $data['summary_detail'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.month',     '=', $month)
            ->where('tblpayment_consolidated.year',      '=', $year)
            ->where('tblpayment_consolidated.bank',      $bankID ? '=' : '<>', $bankID)
            ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)
            ->where('tblpayment_consolidated.bankGroup', $bankGroup ? '=' : '<>', $bankGroup)
            ->where('tblpayment_consolidated.rank', '!=', 2)
            ->get();

        Session::put('schmonth', $month . " " . $year);
        if ($bankID != '') {
            Session::put('bank', $bankName . " " . $bankGroup);
        }

        //===================GET DYNAMIC DEDUCTION ON EPAYMENT=================
        $allPayments = [];
        foreach ($data['summary_detail'] as $payID) {
            $allPayments[] = $payID->staffid;
        }
        $data['staffDeductionElement'] = DB::table('tblotherEarningDeduction')
            ->Join('tblcvSetup', 'tblotherEarningDeduction.CVID', '=', 'tblcvSetup.ID')
            ->where('tblotherEarningDeduction.year', $year)
            ->where('tblotherEarningDeduction.month', $month)
            ->where('tblotherEarningDeduction.divisionID', $division ? '=' : '<>',  $division)
            ->whereIn('tblotherEarningDeduction.staffid', $allPayments)
            ->where('tblotherEarningDeduction.particularID', 2)
            ->where('tblotherEarningDeduction.amount', '<>', 0)
            ->orderBy('tblcvSetup.rank')
            ->groupBy('tblotherEarningDeduction.CVID')
            ->select('account_number', 'account_name', DB::raw("SUM((tblotherEarningDeduction.amount)) as  deductionAmount"), 'description', 'tblotherEarningDeduction.amount', 'tblotherEarningDeduction.CVID', 'tblcvSetup.ID as cvsetupID', 'tblotherEarningDeduction.ID', 'rank', 'tblcvSetup.particularID', 'tblotherEarningDeduction.staffid')
            ->get();
        //===================END EPAYMENT DEDUCTION============================

        return view('payroll.summary.summary', $data);
    }



    public function groupPayroll()
    {
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }
        $data['courts'] =  DB::table('tbl_court')->get();
        $courtSessionId = session('anycourt');
        $data['courtDivisions']  = DB::table('tbldivision')
            ->where('courtID', '=', $courtSessionId)
            ->get();

        $data['courtDivisions']  = DB::table('tbldivision')
            //  ->where('courtID', '=', $courtSessionId)
            ->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);


        $data['allbanklist']  = DB::table('tblbanklist')
            ->orderBy('tblbanklist.bank', 'Asc')
            ->get();

        return view('payroll.summary.groupParam', $data);
    }

    public function groupPayrollDisplay(Request $request)
    {
        $month     = trim($request->input('month'));
        $year      = trim($request->input('year'));
        $data['group'] = DB::table('tblpayment_consolidated')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
            ->where('tblpayment_consolidated.month',     '=', $month)
            ->where('tblpayment_consolidated.year',      '=', $year)
            ->where('tblpayment_consolidated.rank', '!=', 2)
            ->groupBy('tblpayment_consolidated.bank')
            ->select(DB::raw('SUM(Bs) as basic'), 'tblbanklist.bank', DB::raw('SUM(NetPay) as netpay'), DB::raw('SUM(TD) as totdeduct'), DB::raw('SUM(PEC) as jusu'), DB::raw('SUM(PEN) as pension'), DB::raw('SUM(UD) as dues'), DB::raw('SUM(TAX) as tax'), DB::raw('SUM(NHF) as nhf'), DB::raw('SUM(OEarn) as totAllowance'), DB::raw('SUM(AEarn) as totArr'), DB::raw('count(Bs) as totalStaff'), DB::raw('sum(TEarn) as totalEarn'), DB::raw('sum(OD) as coop'), 'tblpayment_consolidated.bank as bankid')
            ->get();

        foreach ($data['group'] as $key => $value) {
            $lis = (array) $value;
            $lis['coop2'] = $this->ContravariableSum($request->input('year'), $request->input('month'), '15', $value->bankid) + $this->ContravariableSum($request->input('year'), $request->input('month'), '16', $value->bankid);
            $lis['saladv'] = $this->ContravariableSum($request->input('year'), $request->input('month'), '18', $value->bankid);
            $lis['hloan'] = $this->ContravariableSum($request->input('year'), $request->input('month'), '2', $value->bankid);
            $value = (object) $lis;
            $data['group'][$key]  = $value;
        }
        $data['month'] = trim($request->input('month'));
        $data['year'] = trim($request->input('year'));

        //===================GET DYNAMIC DEDUCTION ON EPAYMENT=================
        //Get staff in the bank id
        $listOfStaff = [];
        foreach ($data['group'] as $staffId) {
            $listOfStaff[] = $staffId->staffid;
        }
        //all staff deductions
        $data['staffDeductionElement'] = DB::table('tblotherEarningDeduction')
            ->Join('tblcvSetup', 'tblotherEarningDeduction.CVID', '=', 'tblcvSetup.ID')
            ->where('tblotherEarningDeduction.year', $year)
            ->where('tblotherEarningDeduction.month', $month)
            ->where('tblotherEarningDeduction.particularID', 2)
            ->where('tblotherEarningDeduction.amount', '<>', 0)
            ->orderBy('tblcvSetup.rank')
            ->groupBy('tblotherEarningDeduction.CVID')
            ->select('description', 'amount', 'tblotherEarningDeduction.CVID', 'tblcvSetup.ID as cvsetupID', 'tblotherEarningDeduction.ID', 'rank', 'tblcvSetup.particularID', 'tblotherEarningDeduction.staffid')
            ->get();
        $bankGroupAmount = [];
        foreach ($data['group'] as $bankGroup) {
            foreach ($data['staffDeductionElement'] as $cvItem) {
                $bankGroupAmount[$bankGroup->bankid][$cvItem->CVID] = DB::table('tblotherEarningDeduction')
                    ->whereIn('tblotherEarningDeduction.staffid', $listOfStaff)
                    ->where('tblotherEarningDeduction.CVID', $cvItem->CVID)
                    ->first([DB::raw("SUM(tblotherEarningDeduction.amount) as staffDeduction")]);
            }
        }
        $data['bankGroupDeductionAmount'] = $bankGroupAmount;
        //===================END EPAYMENT DEDUCTION============================

        return view('payroll.summary.groupSummary', $data);
    }


    public function summaryByBank()
    {
        $data['CourtInfo'] = $this->CourtInfo();

        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        $data['courts'] =  DB::table('tbl_court')->get();
        $courtSessionId = session('anycourt');

        $data['courtDivisions']  = DB::table('tbldivision')
            ->where('courtID', '=', $courtSessionId)
            ->get();

        $data['courtDivisions']  = DB::table('tbldivision')
            //  ->where('courtID', '=', $courtSessionId)
            ->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        $data['allbanklist']  = DB::table('tblbanklist')
            ->orderBy('tblbanklist.bank', 'Asc')
            ->get();
        //  dd($courtSessionId);
        return view('payroll.summary.bybanks', $data);
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

    public function summaryPostBank(Request $request)
    {
        $month     = trim($request->input('month'));
        $year      = trim($request->input('year'));
        $bank      = trim($request->input('bankName'));
        $division  = trim($request->input('division'));
        $data['month'] = $month;
        $data['year'] = $year;
        $data['bank'] = DB::table('tblbanklist')->where('bankID', '=', $bank)->first();
        $data['bankID'] = '';

        /*$data['group'] = DB::table('tblpayment_consolidated')
    ->join('tblbanklist','tblbanklist.bankID','=','tblpayment_consolidated.bank')
    ->where('tblpayment_consolidated.month',     '=', $month)
    ->where('tblpayment_consolidated.year',      '=', $year)
    ->orderBy('tblpayment_consolidated.bank', 'Asc')
    ->select('*','tblbanklist.bank as staffbank','tblpayment_consolidated.bank as bk')

    ->get();*/
        if ($bank == '') {
            $data['epayment_detail'] = DB::table('tblpayment_consolidated')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
                ->leftJoin('tblbacklog', 'tblbacklog.staffid', '=', 'tblpayment_consolidated.staffid', 'tblbacklog.month', '=', 'tblpayment_consolidated.month', 'tblbacklog.year', '=', 'tblpayment_consolidated.year')
                //->where('tblbacklog.month',     '=', $month)
                //->where('tblbacklog.year',      '=', $year)
                ->where('tblpayment_consolidated.month',     '=', $month)
                ->where('tblpayment_consolidated.year',      '=', $year)
                ->where('tblpayment_consolidated.divisionID',      '=', $division)
                ->where('tblpayment_consolidated.rank', '!=', 2)
                //->where('tblpayment_consolidated.divisionID',  '=', $division)
                //->where('tblpayment_consolidated.courtID',  '=', $court)
                ->orderBy('tblpayment_consolidated.bank', 'DESC')
                ->orderBy('tblpayment_consolidated.rank', 'DESC')
                ->orderBy('tblpayment_consolidated.name', 'ASC')
                ->get();
            // dd($data['epayment_detail']);

            $data['epayment_total'] = DB::table('tblpayment_consolidated')

                ->where('tblpayment_consolidated.month',     '=', $month)
                ->where('tblpayment_consolidated.year',      '=', $year)
                ->where('tblpayment_consolidated.divisionID',  '=', $division)
                //->where('tblpayment_consolidated.courtID',  '=', $court)
                //->where('tblpayment_consolidated.bank',      '=', $bankID )
                // ->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
                ->where('tblpayment_consolidated.rank', '!=', 2)
                ->orderBy('tblpayment_consolidated.grade', 'DESC')
                //->orderBy('tblpayment_consolidated.step','DESC')
                ->orderBy('tblpayment_consolidated.bank', 'DESC')
                ->orderBy('tblpayment_consolidated.rank', 'DESC')
                ->orderBy('tblpayment_consolidated.name', 'ASC')
                ->get();

            // dd($data['epayment_total']);

            return view('payroll.summary.summaryByBanks2', $data);
        } else {

            $data['bankID'] = $bank;
            $data['epayment_detail'] = DB::table('tblpayment_consolidated')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
                ->leftJoin('tblbacklog', 'tblbacklog.staffid', '=', 'tblpayment_consolidated.staffid', 'tblbacklog.month', '=', 'tblpayment_consolidated.month', 'tblbacklog.year', '=', 'tblpayment_consolidated.year')
                //->where('tblbacklog.month',     '=', $month)
                //->where('tblbacklog.year',      '=', $year)
                ->where('tblpayment_consolidated.bank',     '=', $bank)
                ->where('tblpayment_consolidated.month',     '=', $month)
                ->where('tblpayment_consolidated.year',      '=', $year)
                ->where('tblpayment_consolidated.rank', '!=', 2)
                ->where('tblpayment_consolidated.divisionID',  '=', $division)
                //->where('tblpayment_consolidated.courtID',  '=', $court)
                ->orderBy('tblpayment_consolidated.bank', 'DESC')
                ->orderBy('tblpayment_consolidated.rank', 'DESC')
                ->orderBy('tblpayment_consolidated.name', 'ASC')
                ->get();
            //dd($data['epayment_detail']);
            $data['epayment_total'] = DB::table('tblpayment_consolidated')

                ->where('tblpayment_consolidated.month',     '=', $month)
                ->where('tblpayment_consolidated.year',      '=', $year)
                ->where('tblpayment_consolidated.bank',     '=', $bank)
                ->where('tblpayment_consolidated.divisionID',  '=', $division)
                //->where('tblpayment_consolidated.courtID',  '=', $court)
                //->where('tblpayment_consolidated.bank',      '=', $bankID )
                // ->where('tblpayment_consolidated.bankGroup', '=',$bankGroup)
                ->where('tblpayment_consolidated.rank', '!=', 2)
                ->orderBy('tblpayment_consolidated.grade', 'DESC')
                //->orderBy('tblpayment_consolidated.step','DESC')
                ->orderBy('tblpayment_consolidated.bank', 'DESC')
                ->orderBy('tblpayment_consolidated.rank', 'DESC')
                ->orderBy('tblpayment_consolidated.name', 'ASC')

                ->get();
            // dd($data);
            return view('payroll.summary.summaryByBanks2', $data);
        }
    }
    public function ContravariableSum($year, $month, $cvid, $bank)
    {
        $List = DB::Select("SELECT sum(`amount`) as sumtotal FROM `tblotherEarningDeduction` WHERE `CVID`='$cvid' and `tblotherEarningDeduction`.`month`='$month' and `tblotherEarningDeduction`.`year`='$year' and
	exists( SELECT * FROM `tblpayment_consolidated` WHERE `tblpayment_consolidated`.`staffid`=`tblotherEarningDeduction`.`staffid` and `tblpayment_consolidated`.`year`='$year'
	and `tblpayment_consolidated`.`month`='$month' and `tblpayment_consolidated`.`bank`='$bank' and tblpayment_consolidated.rank !=2)");
        if ($List) {
            return $List[0]->sumtotal;
        } else {
            return 0;
        }
    }

    //JUSTICE PAYROLL SUMMARY VOUCHER
    public function createJusticeSummaryVoucher()
    {
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }
        $data['courts'] =  DB::table('tbl_court')->get();
        $courtSessionId = session('anycourt');
        $data['courtDivisions']  = DB::table('tbldivision')
            ->where('courtID', '=', $courtSessionId)
            ->get();


        $data['courtDivisions']  = DB::table('tbldivision')
            //  ->where('courtID', '=', $courtSessionId)
            ->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        // $data['allbanklist']  = DB::table('tblbanklist')
        //     ->orderBy('tblbanklist.bank', 'Asc')
        //     ->get();

        $data['allbanklist'] = DB::table('tblbanklist')
                ->join('tblpayment_consolidated', 'tblpayment_consolidated.bank', '=', 'tblbanklist.bankID')
                ->where('tblpayment_consolidated.rank', '=', 2)
                ->groupBy('tblpayment_consolidated.bank')
                ->orderBy('tblbanklist.bank', 'Asc')
                ->select('tblbanklist.bank', 'tblbanklist.bankID')
                ->get();

        //   dd($data);

        return view('payroll.summary.justiceIndex', $data);
    }

    public function RetrieveJusticeSummaryVoucher(Request $request)
    {

        $month     = trim($request->input('month'));
        $year      = trim($request->input('year'));
        $bankID    = trim($request->input('bankName'));
        $bankGroup = trim($request->input('bankGroup'));
        //$warrant   = trim($request->input('warrant'));
        $division  = trim($request->input('division'));
        $court    = trim($request->input('court'));
        $divisionDetails = DB::table('tbldivision')->where('divisionID', $division)->first(['division', 'abbrv']);
        $data['divisionName'] = ($divisionDetails ? $divisionDetails->division : '');
        $data['divisionAbbr'] = ($divisionDetails ? $divisionDetails->abbrv : '');
        $data['year'] = $year;
        $data['month'] = $month;

        $this->validate($request, [
            'month'     => 'required|string',
            'year'      => 'required|integer',
            //'bankName'  => 'required|integer',
            //'bankGroup' => 'required|integer',
            //'warrant'   => 'required|regex:/[a-zA-Z.]/'
        ]);
        $getBank  = DB::table('tblbanklist')
            ->where('bankID', $bankID)
            ->first();
        if ($bankID != '') {
            $bankName = $getBank->bank;
        }


        // $data['summary_detail'] = DB::table('tblpayment_consolidated')
        //     ->where('tblpayment_consolidated.month',     '=', $month)
        //     ->where('tblpayment_consolidated.year',      '=', $year)
        //     ->where('tblpayment_consolidated.bank',      $bankID ? '=' : '<>', $bankID)
        //     ->where('tblpayment_consolidated.divisionID', $division ? '=' : '<>', $division)
        //     ->where('tblpayment_consolidated.bankGroup', $bankGroup ? '=' : '<>', $bankGroup)
        //     ->where('tblpayment_consolidated.rank', '=', 2)
        //     ->get();

        $data['summary_detail'] = DB::table('tblpayment_consolidated')
            ->where('month', $month)
            ->where('year', $year)
            ->when($bankID, function ($q) use ($bankID) {
                return $q->where('bank', $bankID);
            })
            ->when($division, function ($q) use ($division) {
                return $q->where('divisionID', $division);
            })
            ->when($bankGroup, function ($q) use ($bankGroup) {
                return $q->where('bankGroup', $bankGroup);
            })
            ->where('rank', 2)
            ->get();

        Session::put('schmonth', $month . " " . $year);
        if ($bankID != '') {
            Session::put('bank', $bankName . " " . $bankGroup);
        }

        //===================GET DYNAMIC DEDUCTION ON EPAYMENT=================
        $allPayments = [];
        foreach ($data['summary_detail'] as $payID) {
            $allPayments[] = $payID->staffid;
        }
        $data['staffDeductionElement'] = DB::table('tblotherEarningDeduction')
            ->Join('tblcvSetup', 'tblotherEarningDeduction.CVID', '=', 'tblcvSetup.ID')
            ->where('tblotherEarningDeduction.year', $year)
            ->where('tblotherEarningDeduction.month', $month)
            ->where('tblotherEarningDeduction.divisionID', $division ? '=' : '<>',  $division)
            ->whereIn('tblotherEarningDeduction.staffid', $allPayments)
            ->where('tblotherEarningDeduction.particularID', 2)
            ->where('tblotherEarningDeduction.amount', '<>', 0)
            ->orderBy('tblcvSetup.rank')
            ->groupBy('tblotherEarningDeduction.CVID')
            ->select('account_number', 'account_name', DB::raw("SUM((tblotherEarningDeduction.amount)) as  deductionAmount"), 'description', 'tblotherEarningDeduction.amount', 'tblotherEarningDeduction.CVID', 'tblcvSetup.ID as cvsetupID', 'tblotherEarningDeduction.ID', 'rank', 'tblcvSetup.particularID', 'tblotherEarningDeduction.staffid')
            ->get();
        //===================END EPAYMENT DEDUCTION============================

        return view('payroll.summary.justiceSummary', $data);
    }
}
