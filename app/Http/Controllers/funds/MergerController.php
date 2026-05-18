<?php

namespace App\Http\Controllers\funds;

use App\Http\Requests;
use App\Role;
use App\User;
use Entrust;
use App\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MergerController extends ParentController
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
    //public $division;
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->middleware('auth');
                return $next($request);
        });
    }

    public function index()
    {
        $data['allbanklist']  = DB::table('tblbanklist')->get();

        $data['economic_code'] = "";
        $data['month']       = "";
        $data['year']        = "";
        $data['message']     = "";
        $data['errormessage'] = "";

        return view('funds.merger.index', $data);
    }


    public function MergeOLD(Request $request)
    {

        $economic_code = trim($request->input('economic_code'));
        $month = trim($request->input('month'));
        $year = trim($request->input('year'));
        $date = date('Y-m-d');

        $data['economic_code'] =    $request->input('economic_code');
        $data['month']         =    $request->input('month');
        $data['year']          =    $request->input('year');

        $this->validate($request, [

            'economic_code' => 'required',
            'month'         => 'required|regex:/^[\pL\s\-]+$/u',
            'year'          => 'required|integer',

        ]);

        if ($economic_code == 71) {
            //salary sum
            $data['sumx'] = DB::connection('npaydb')->table('tblpayment')
                ->where('tblpayment.month', '=', $month)
                ->where('tblpayment.year',  '=', $year)
                ->sum('grosspay');

            //allowance
            $data['sumy'] = DB::connection('npaydb')->table('tblpayment')
                ->where('tblpayment.month', '=', $month)
                ->where('tblpayment.year',  '=', $year)
                ->sum('actingAllow');

            $data['sumAmount'] = $data['sumx'];

            $beneficiaryx = DB::connection('npaydb')->table('tblpayment')->where('month', $month)->where('year', $year)->value('name');
            $count_beneficiary = DB::connection('npaydb')->table('tblpayment')
                ->where('month', $month)
                ->where('year', $year)
                ->count();
            $is_salary = 2;

            //dd($data['sumAmount']);

            $payment = 'Salary';
        } elseif ($economic_code == 72) {
            $data['sumAmount'] = DB::connection('npaydb')->table('tblpayment')
                ->where('tblpayment.month', '=', $month)
                ->where('tblpayment.year',  '=', $year)
                ->sum('actingAllow');

            $beneficiaryx = DB::connection('npaydb')->table('tblpayment')->where('month', $month)->where('year', $year)->value('name');
            $count_beneficiary = DB::connection('npaydb')->table('tblpayment')
                ->where('month', $month)
                ->where('year', $year)
                ->count();
            $is_salary = 0;
            $payment = 'Overtime Payment';
        } elseif ($economic_code == 73) {
            $data['sumAmount'] = DB::connection('npayrollcondb')->table('tbl_conpayment')
                ->where('tbl_conpayment.month', '=', $month)
                ->where('tbl_conpayment.year',  '=', $year)
                ->sum('gross_pay');

            $beneficiaryx = DB::connection('npayrollcondb')->table('tbl_conpayment')->where('month', $month)->where('year', $year)->value('name');
            $count_beneficiary = DB::connection('npayrollcondb')->table('tbl_conpayment')
                ->where('month', $month)
                ->where('year', $year)
                ->count();
            $is_salary = 1;

            $payment = 'Consolidated Revenue Fund Charges';
        }


        $beneficiary = $beneficiaryx . ' and ' . $count_beneficiary . ' others.';
        $description = 'Being ' . $payment . ' for the month of ' . $month . ', ' . $year;

        // First day of the month.
        $firstday = date('Y-m-01', strtotime($date));

        $check_contracts = DB::table('tblcontractDetails')->where('month', $month)->where('year', $year)->where('economic_code_status', $economic_code)->where('is_salary', '=', $is_salary)->exists();
        $check_trans = DB::table('tblpaymentTransaction')->where('month', $month)->where('year', $year)->where('economic_code_status', $economic_code)->where('is_salary', '=', $is_salary)->exists();

        $check_code = DB::table('tblcontractDetails')->where('economicVoult', $economic_code)->where('month', $month)->where('year', $year)->where('economic_code_status', $economic_code)->where('is_salary', '=', $is_salary)->exists();
        $check_liabiity = DB::table('tblpaymentTransaction')->where('liabilityStatus', 1)->where('month', $month)->where('year', $year)->where('economic_code_status', $economic_code)->where('is_salary', '=', $is_salary)->exists();

        if (($check_trans) && ($check_contracts)) {

            $data['message'] = '';
            $data['errormessage'] = 'Cannot Transfer. It has already been Processed';
            return view('funds.merger.index', $data);
        } else {
            //dd('OK Again');
            if ($check_liabiity) {
                dd('do nothing88'); //do nothing
            } else {
                if ($check_code) {
                    dd('do nothing30');
                } else {
                    $contract_id = DB::table('tblcontractDetails')->insertGetId([
                        'economicVoult' => 71,
                        'contractValue' => $data['sumAmount'],
                        'companyID' => 13,
                        'dateAward' => $firstday,
                        'contract_Type' => 6,
                        'ContractDescriptions' => $description,
                        'beneficiary' => $beneficiary,
                        'datecreated' => $date,
                        'approvedBy' => Auth::user()->id,
                        'createdby' => 1,
                        'approvalStatus' => 1,
                        'openclose' => 1,
                        'paymentStatus' => 0,
                        'voucherType' => 2,
                        'period' => $year,
                        'isfrom_procurement' => 0,
                        'is_salary' => $is_salary,
                        'month' => $month,
                        'year' => $year,
                        'economic_code_status' => $economic_code
                    ]);

                    DB::table('tblpaymentTransaction')->insert([
                        'contractTypeID' => 6,
                        'contractID' => $contract_id,
                        'companyID' => 13,
                        'totalPayment' => $data['sumAmount'],
                        'paymentDescription' => $description,
                        'amtPayable' => $data['sumAmount'],
                        'preparedBy' => Auth::user()->id,
                        'allocationType' => 5,
                        'economicCodeID' => $economic_code,
                        'status' => 0,
                        'datePrepared' => $date,
                        'vstage' => 1,
                        'accept_voucher_status' => 1,
                        'is_salary' => $is_salary,
                        'month' => $month,
                        'year' => $year,
                        'period' => $year,
                        'economic_code_status' => $economic_code
                    ]);
                }
            }
        }

        $data['message'] = 'Successfully Submitted';
        $data['errormessage'] = '';
        return view('funds.merger.index', $data);
    }

    public function Merge(Request $request)
    {

        $economic_code = trim($request->input('economic_code'));
        $month = trim($request->input('month'));
        $year = trim($request->input('year'));
        $date = date('Y-m-d');

        $data['economic_code'] =    $request->input('economic_code');
        $data['month']         =    $request->input('month');
        $data['year']          =    $request->input('year');

        $this->validate($request, [

            'economic_code' => 'required',
            'month'         => 'required|regex:/^[\pL\s\-]+$/u',
            'year'          => 'required|integer',

        ]);

        // dd($economic_code);

        if ($economic_code == 257) {
            //salary sum
            $data['sumx'] = DB::table('tblpayment_consolidated')
                ->where('tblpayment_consolidated.month', '=', $month)
                ->where('tblpayment_consolidated.year',  '=', $year)
                ->sum('gross');

            //allowance
            $data['sumy'] = DB::table('tblpayment_consolidated')
                ->where('tblpayment_consolidated.month', '=', $month)
                ->where('tblpayment_consolidated.year',  '=', $year)
                ->sum('actingAllow');

            $data['sumAmount'] = $data['sumx'];

            $beneficiaryx = DB::table('tblpayment_consolidated')->where('month', $month)->where('year', $year)->value('name');
            $count_beneficiary = DB::table('tblpayment_consolidated')
                ->where('month', $month)
                ->where('year', $year)
                ->count();
            $is_salary = 2;


            $payment = 'Salary';
        } elseif ($economic_code == 72) {
            $data['sumAmount'] = DB::table('tblpayment_consolidated')
                ->where('tblpayment_consolidated.month', '=', $month)
                ->where('tblpayment_consolidated.year',  '=', $year)
                ->sum('actingAllow');

            $beneficiaryx = DB::table('tblpayment_consolidated')->where('month', $month)->where('year', $year)->value('name');
            $count_beneficiary = DB::table('tblpayment_consolidated')
                ->where('month', $month)
                ->where('year', $year)
                ->count();
            $is_salary = 0;
            $payment = 'Overtime Payment';
        } elseif ($economic_code == 73) {
            $data['sumAmount'] = DB::table('tblpayment_consolidated')
                ->where('tblpayment_consolidated.month', '=', $month)
                ->where('tblpayment_consolidated.year',  '=', $year)
                ->sum('gross');

            $beneficiaryx = DB::table('tblpayment_consolidated')->where('month', $month)->where('year', $year)->value('name');
            $count_beneficiary = DB::table('tblpayment_consolidated')
                ->where('month', $month)
                ->where('year', $year)
                ->count();
            $is_salary = 1;

            $payment = 'Consolidated Revenue Fund Charges';
        }


        $beneficiary = $beneficiaryx . ' and ' . ($count_beneficiary - 1) . ' others.';
        $description = 'Being ' . $payment . ' for the month of ' . $month . ', ' . $year;

        // First day of the month.
        $firstday = date('Y-m-01', strtotime($date));

        $check_contracts = DB::table('tblcontractDetails')->where('month', $month)->where('year', $year)->where('economic_code_status', $economic_code)->where('is_salary', '=', $is_salary)->exists();
        $check_trans = DB::table('tblpaymentTransaction')->where('month', $month)->where('year', $year)->where('economic_code_status', $economic_code)->where('is_salary', '=', $is_salary)->exists();

        $check_code = DB::table('tblcontractDetails')->where('economicVoult', $economic_code)->where('month', $month)->where('year', $year)->where('economic_code_status', $economic_code)->where('is_salary', '=', $is_salary)->exists();
        $check_liabiity = DB::table('tblpaymentTransaction')->where('liabilityStatus', 1)->where('month', $month)->where('year', $year)->where('economic_code_status', $economic_code)->where('is_salary', '=', $is_salary)->exists();

        if (($check_trans) && ($check_contracts)) {

            $data['message'] = '';
            $data['errormessage'] = 'Cannot Transfer. It has already been Processed';
            return view('funds.merger.index', $data);
        } else {
            //dd('OK Again');
            if ($check_liabiity) {
                $data['message'] = '';
                $data['error'] = 'Record not found';
                return view('funds.merger.index', $data);
            } else {
                if ($check_code) {
                    $data['message'] = '';
                    $data['error'] = 'Record not found';
                    return view('funds.merger.index', $data);
                } else {
                    $contract_id = DB::table('tblcontractDetails')->insertGetId([
                        'economicVoult' => 257,
                        'contractValue' => $data['sumAmount'],
                        'companyID' => 1,
                        'dateAward' => $firstday,
                        'contract_Type' => 6,
                        'ContractDescriptions' => $description,
                        'beneficiary' => $beneficiary,
                        'datecreated' => $date,
                        'approvedBy' => Auth::user()->id,
                        'createdby' => 1,
                        'is_advances' => 3,
                        'approvalStatus' => 1,
                        'openclose' => 1,
                        'paymentStatus' => 0,
                        'voucherType' => 2,
                        'period' => $year,
                        'isfrom_procurement' => 0,
                        'is_salary' => $is_salary,
                        'month' => $month,
                        'year' => $year,
                        'economic_code_status' => $economic_code
                    ]);

                    DB::table('tblpaymentTransaction')->insert([
                        'contractTypeID' => 6,
                        'contractID' => $contract_id,
                        'companyID' => 1,
                        'totalPayment' => $data['sumAmount'],
                        'paymentDescription' => $description,
                        'amtPayable' => $data['sumAmount'],
                        'preparedBy' => Auth::user()->id,
                        'allocationType' => 5,
                        'economicCodeID' => $economic_code,
                        'status' => 0,
                        'is_advances' => 3,
                        'datePrepared' => $date,
                        'vstage' => 1,
                        'accept_voucher_status' => 1,
                        'is_salary' => $is_salary,
                        'month' => $month,
                        'year' => $year,
                        'period' => $year,
                        'economic_code_status' => $economic_code,
                        'payment_beneficiary' => $beneficiary
                    ]);
                }
            }
        }

        $data['message'] = 'Successfully Submitted';
        $data['errormessage'] = '';
        return view('funds.merger.index', $data);
    }
}
