<?php

namespace App\Http\Controllers\payroll;



use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\payroll\functions22Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class StaffCooperativeController extends functions22Controller
{
    public function staffCooperative(Request $request)
    {
        // Remove division-related data
        $data['EorDSession'] = '';
        $data['edses'] = '';
        $data['result'] = [];
        $data['selected_year'] = date('Y');
        $data['selected_month'] = date('m');

        $List = DB::table("tblsole_court")->where('courtid', '=', 9)->first();
        if ($List && $List->divisionstatus == 1 && Auth::user()->is_global == 1) {
            $data['EorDSession'] = session('earnDeduction');
            $data['edses'] = session('ed');
        } else {
            $data['EorDSession'] = session('earnDeduction');
            $data['edses'] = session('ed');
        }

        if (isset($_GET['staffCooperativeReport'])) {
            $this->validate($request, [
                'controlvariable' => 'required',
                'earnordeduction' => 'required',
                'year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
                'month' => 'required|integer|min:1|max:12'
            ]);

            $data['controlvariable'] = $request['controlvariable'];
            $data['earnordeduction'] = $request['earnordeduction'];
            $data['selected_year'] = $request['year'];
            $data['selected_month'] = $request['month'];

            // Get results with year and month filter
            $data['result'] = $this->staffEarnAndDeductionNoDivision(
                $data['earnordeduction'],
                $data['selected_year'],
                $data['selected_month']
            );

            // Calculate total amount
            $data['totalAmount'] = 0;
            if (!empty($data['result'])) {
                foreach ($data['result'] as $record) {
                    $data['totalAmount'] += floatval($record->amount);
                }
            }
        }

        // Generate year options
        $currentYear = date('Y');
        $data['years'] = range($currentYear - 5, $currentYear + 1);

        // Month options with names for display
        $data['months'] = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];

        return view('payroll.staffcooperative.staffcooperative', $data);
    }

    public function viewStaffCooperative(Request $request)
    {
        // dd($request->all());
        $this->validate($request, [
            'division' => 'required',
            'controlvariable' => 'required',
            'earnordeduction' => 'required'
        ]);

        $data['division'] = $request['division'];
        $data['controlvariable'] = $request['controlvariable'];
        $data['earnordeduction'] = $request['earnordeduction'];

        $data['staffEarnDeductionReport'] = DB::table('tblotherEarningDeduction')
            ->leftjoin('tblper', 'tblper.ID', '=', 'tblotherEarningDeduction.staffid')
            ->leftjoin('tbldivision', 'tbldivision.divisionID', '=', 'tblotherEarningDeduction.divisionID')
            ->leftjoin('tblcvSetup', 'tblcvSetup.ID', '=', 'tblotherEarningDeduction.CVID')
            ->where('tblotherEarningDeduction.divisionID', '=', $data['division'])
            ->where('tblotherEarningDeduction.CVID', '=', $data['earnordeduction'])
            ->where('tblotherEarningDeduction.particularID', '=', $data['controlvariable'])
            ->groupBY('tblotherEarningDeduction.staffid')
            ->select('*', 'tblper.surname', 'tblper.first_name', 'tblper.othernames', 'tbldivision.division', 'tblcvSetup.description')
            ->paginate(50);
        return view('payroll.staffcooperative.viewStaffCooperativeResult', $data);
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

    public function courtSession(Request $request)
    {
        $court = $request['courtID'];
        $division = $request['divisionID'];
        $check = $request['check'];
        if ($check == 'court') {
            $ses    = Session::put('selected_court', $court);
            return response()->json("Successfully Set");
        } elseif ($check == 'division') {
            $ses    = Session::put('selected_division', $division);
            return response()->json("Successfully Set");
        } else {
            return response()->json("Not Set");
        }
    }

    public function getEarnOrDeduction(Request $request)
    {
        $data['earnOrDeduction'] = $request['controlvariable'];
        $data['allEarnOrDeduction'] = DB::table('tblcvSetup')->where('particularID', '=', $data['earnOrDeduction'])
            ->where('status', '=', 1)
            ->select('tblcvSetup.ID', 'tblcvSetup.particularID', 'tblcvSetup.description')
            ->get();

        if ($data['allEarnOrDeduction']) {
            $ses = Session::put('earnDeduction', $data['allEarnOrDeduction']);
        }
    }

    public function setCurED(Request $request)
    {
        $data['ed'] = $request['ed'];
        Session::put('ed', $data['ed']);
        return response()->json($data['ed']);
    }

    public function staffCooperativeBankSum()
    {
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }
        $data['courts'] =  DB::table('tbl_court')->get();
        $data['courtDivisions']  = DB::table('tbldivision')
            ->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        $data['allbanklist']  = DB::table('tblbanklist')
            ->join('tblcvSetup', 'tblcvSetup.bank', '=', 'tblbanklist.bankID')
            ->orderBy('tblbanklist.bank', 'Asc')
            ->select('tblbanklist.bankID', 'tblbanklist.bank')
            ->distinct()
            ->get();

        return view('staffCooperativeBankSum.index', $data);
    }

    public function staffCooperativeBankSumDisplay(Request $request)
    {
        $data['month']              = trim($request->input('month'));
        $data['year']               = trim($request->input('year'));
        $data['bank']             = trim($request->input('bank'));

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

        $data['monthNumber'] = date('n', strtotime($data['month']));

        $data['reportData'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.rank', '!=', 2)
            ->where('tblpayment_consolidated.year', $data['year'])
            ->where('tblpayment_consolidated.month', $data['month'])
            ->first();

        $data['staffEarnDeductionReport'] = DB::table('tblotherEarningDeduction')
            ->join('tblcvSetup', 'tblcvSetup.ID', '=', 'tblotherEarningDeduction.CVID')
            ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblcvSetup.bank')
            ->where('tblcvSetup.particularID', 2)
            ->where('tblcvSetup.bank', $data['bank'])
            ->where('tblotherEarningDeduction.year', $data['year'])
            ->where('tblotherEarningDeduction.month', $data['month'])
            ->select(
                DB::raw("CASE WHEN tblotherEarningDeduction.CVID IN (5,24) THEN 5 ELSE tblotherEarningDeduction.CVID END as CVID_group"),
                DB::raw("CASE WHEN tblotherEarningDeduction.CVID IN (5,24) THEN 'Muslim Women Cooperative' ELSE tblcvSetup.description END as deduction_name"),
                'tblbanklist.bank as bank_name',
                'tblcvSetup.account_number',
                DB::raw('SUM(tblotherEarningDeduction.amount) as total_amount')
            )
            ->groupBy(
                DB::raw("CASE WHEN tblotherEarningDeduction.CVID IN (5,24) THEN 5 ELSE tblotherEarningDeduction.CVID END"),
                DB::raw("CASE WHEN tblotherEarningDeduction.CVID IN (5,24) THEN 'Muslim Women Cooperative' ELSE tblcvSetup.description END"),
                'tblbanklist.bank',
                'tblcvSetup.account_number'
            )
            ->orderByRaw("CASE WHEN tblotherEarningDeduction.CVID IN (5,24) THEN 'Muslim Women Cooperative' ELSE tblcvSetup.description END ASC")
            ->get();



        $data['cooperativeGrandTotal'] = $data['staffEarnDeductionReport']->sum('total_amount');

        return view('staffCooperativeBankSum.retrieve', $data);
    }

    public function CourtInfo()
    {
        $List = DB::Select("SELECT * FROM `tblsole_court`");
        return $List[0];
    }
}
