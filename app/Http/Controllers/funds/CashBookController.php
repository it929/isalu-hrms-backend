<?php

namespace App\Http\Controllers\funds;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;

class CashBookController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    //Create/ load page
    public function createCashBook()
    {
        $data = [];
        Session::forget('accountType');
        Session::forget('accountYear');

        try {
            //GET ACCOUNT TYPE 
            $data['getContractType'] = DB::table('tblcontract_category')
                ->select('id as ID', 'category as contractType')
                ->get();

            //GET ECONOMIC CODES
            $data['getEconomicCode'] = DB::table('tbleconomicCode')->where('status', 1)->get();
        } catch (\Throwable $e) {
        }

        return view('funds.treasureCashBook.home', $data);
    }


    //View Report
    public function viewCashBookReport($accountType = null, $accountYear = null)
    {
        $accountType = Session::get('accountType');
        $accountYear = Session::get('accountYear');

        //GET REPORT TYPE NAME
        $data['accountTypeName'] = DB::table('tblcontract_category')->where('id', $accountType)->value('category') . ' EXPENDITURES  |  Year: ' . Session::get('accountYear');

        //GET ALL ACCOUNT TYPE
        $data['getContractType'] = DB::table('tblcontractType')->where('status', 1)->where('contract_category', $accountType)->select('ID')->get();

        //Array Months of the Year
        $data['monthOfTheYear'] = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");

        //GROUP ACCOUNT TYPE
        $allAccountType = [];
        foreach ($data['getContractType'] as $acctType) {
            $allAccountType[] = $acctType->ID;
        }

        ########### START TO COMUTE ECONOMIC CODE EXPENDITURES ############
        $eCodeExp                   = [];
        $getTotalMonthlyExp         = [];
        $getTotalMonthlyAllocation  = [];
        $getTotalMonthlyRefund      = [];
        $getTotalEcodeExpenditureYear = [];
        $totalEcodeExpenses         = [];
        $sumTotalExpForYear         = 0.0;
        $sumTotalAllocForYear       = 0.0;
        $sumTotalRefundForYear      = 0.0;
        $getAllEconomicCode         = [];

        //GET ECONOMIC CODES
        $data['getEconomicCode'] = DB::table('tbleconomicCode')->where('tbleconomicCode.status', 1)->whereIn('tbleconomicCode.contractGroupID', $allAccountType)
            ->leftJoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
            ->select('contractGroupID', 'Code', 'economicHead', 'tbleconomicCode.ID as eCodeID', 'economicCode', 'tbleconomicCode.description')
            ->get();

        if (empty($accountType) || $data['getEconomicCode'] == null) {
            return redirect()->back()->with('info', 'Sorry, no record found on the account type selected');
        }



        if ($data['monthOfTheYear']) {
            foreach ($data['monthOfTheYear'] as $monthKey => $month) {
                $getMonthInt = date('m', strtotime($month));
                $totalAmount            = 0.0;
                $totalAmountAllocation  = 0.0;
                $totalAmountRefund      = 0.0;
                $refundAmount           = 0.0;

                if ($data['getEconomicCode']) {
                    foreach ($data['getEconomicCode'] as $eCodeKey => $eCode) {
                        $getAllEconomicCode[]   = $eCode->eCodeID;
                        $eCodeIDArray[]         = $eCode->eCodeID;
                        //Expenditure
                        $getExp = $this->getEconomicCodeAmount($eCode->economicCode, $eCode->Code, $accountYear, $getMonthInt);

                        //remove refund
                        $refundAmount = DB::table('treasury_refund')
                            ->whereBetween('treasury_refund.date', [$accountYear . '-' . $getMonthInt . '-01', $accountYear . '-' . $getMonthInt . '-31'])
                            //->where('treasury_refund.economic_code_ncoa', $eCode->economicCode)
                            ->where('treasury_refund.economicID', $eCode->eCodeID)
                            ->sum('treasury_refund.amount_tsa_bank');
                        //Get Amount
                        $eCodeExp[$month][$eCode->eCodeID]  = ($getExp - $refundAmount);
                        //Total Expenditure
                        $totalAmount                        += $getExp;
                    }
                }

                //Total Alocation - tbltotalMonthlyAllocation
                $totalAmountAllocation = DB::table('tbltotalMonthlyAllocation')
                    ->where('tbltotalMonthlyAllocation.month', strtolower($month))
                    ->where('tbltotalMonthlyAllocation.year', $accountYear)
                    ->whereIn('tbltotalMonthlyAllocation.budgetType', $allAccountType)
                    ->sum('tbltotalMonthlyAllocation.amount');

                //Total Refund
                $totalAmountRefund += DB::table('treasury_refund')
                    ->whereBetween('treasury_refund.date', [$accountYear . '-' . $getMonthInt . '-01', $accountYear . '-' . $getMonthInt . '-31'])
                    ->whereIn('treasury_refund.economicID', $getAllEconomicCode)
                    ->sum('treasury_refund.amount_tsa_bank');

                //
                $getTotalMonthlyExp[$month]         = $totalAmount;
                $getTotalMonthlyAllocation[$month]  = $totalAmountAllocation;
                $getTotalMonthlyRefund[$month]      = $totalAmountRefund;
                //Sum up
                $sumTotalExpForYear                 += $totalAmount;
                $sumTotalAllocForYear               += $totalAmountAllocation;
                $sumTotalRefundForYear              += $totalAmountRefund;
            }
            //get Total E-code Expenses Year
            $totalEcodeExpenses = [];
            foreach ($data['getEconomicCode'] as $eCode) {
                $totalEconomicAmount    = 0.0;
                foreach ($data['monthOfTheYear'] as $monthKey => $month) {
                    $getTotalMonthInt = date('m', strtotime($month));
                    $getTotalAmount = $this->getEconomicCodeAmount($eCode->economicCode, $eCode->Code, $accountYear, $getTotalMonthInt);
                    $totalEconomicAmount += $getTotalAmount;
                }
                $totalEcodeExpenses[$eCode->eCodeID] = $totalEconomicAmount;
            }
        }
        //pass exp. data
        $data['eCodeExpense']                   = $eCodeExp;
        $data['totalMonthlyExp']                = $getTotalMonthlyExp;
        $data['getTotalMonthlyAllocation']      = $getTotalMonthlyAllocation;
        $data['getTotalMonthlyRefund']          = $getTotalMonthlyRefund;
        //
        $data['sumTotalExpForYear']             = $sumTotalExpForYear;
        $data['sumTotalAllocForYear']           = $sumTotalAllocForYear;
        $data['sumTotalRefundForYear']          = $sumTotalRefundForYear;
        $data['getTotalEcodeExpenditureYear']   = $totalEcodeExpenses;
        $data['getAllEconomicCode']             = $getAllEconomicCode;
        $data['getYear']                        = $accountYear;
        ########### END ECONOMIC CODE EXPENDITURES ############

        return view('funds.treasureCashBook.report', $data);
    }


    //process 
    public function generateReortCashBook(Request $request)
    {
        Session::forget('accountType');
        Session::forget('accountYear');

        $accountType = $request['accountType'];
        $accountYear = $request['accountYear'];
        Session::put('accountType', $accountType);
        Session::put('accountYear', $accountYear);

        if ($accountType <> null && $accountYear <> null) {
            try {
                if (DB::table('tbleconomicCode')->where('contractGroupID', $accountType)->first()) {
                    return redirect()->route('viewReport', ['at' => $accountType, 'y' => $accountYear]);
                }
            } catch (\Throwable $e) {
            }
        }
        return redirect()->back()->with('info', 'Account Type not Found! Please select the type of report you want to view.');
    }


    //Get economic code amount and return only amount as a float value
    public function getEconomicCodeAmount($eCode = null, $headCode = null, $accountYearInt = null, $monthInt = null)
    {

        $getTotalAmount = DB::table('tblpaymentTransaction')->where('tbleconomicCode.economicCode', $eCode)->where('tbleconomicHead.Code', $headCode)
            ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
            ->leftJoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
            ->whereBetween('tblpaymentTransaction.dateTakingLiability', [$accountYearInt . '-' . $monthInt . '-01', $accountYearInt . '-' . $monthInt . '-31'])
            ->sum('tblpaymentTransaction.totalPayment');

        return $getTotalAmount;
    }

    //View Refund details
    public function getRefundDetails($economicCode = [], $year = null, $month = null)
    {
        $getMonthInt = date('m', strtotime($month));
        $data['getRefund'] = null;
        if ($economicCode && $year && $getMonthInt) {
            //Refund Details
            $data['getRefund'] = DB::table('treasury_refund')
                ->whereBetween('treasury_refund.date', [$year . '-' . $getMonthInt . '-01', $year . '-' . $getMonthInt . '-31'])
                ->whereIn('treasury_refund.economicID', json_decode($economicCode))
                ->get();
            return view('funds.treasureCashBook.refundDetails', $data);
        } else {
            return redirect()->route('cashbook')->with('warning', 'Sorry, we cannot get your record. Please try again.');
        }
    }


    //View Cashbook Payment details
    public function getCashBookPaymentDetails($economicCodeID = null, $economicHead = null, $year = null, $month = null)
    {
        $getMonthInt = date('m', strtotime($month));
        $data['getPaymentDetails'] = null;
        if ($economicCodeID && $year && $getMonthInt) {
            //Cash book payment Details
            $data['getPaymentDetails'] = DB::table('tblpaymentTransaction')->where('tbleconomicCode.economicCode', $economicCodeID)->where('tbleconomicHead.Code', $economicHead)
                ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
                ->leftJoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
                ->whereBetween('tblpaymentTransaction.dateTakingLiability', [$year . '-' . $getMonthInt . '-01', $year . '-' . $getMonthInt . '-31'])
                ->select('tblpaymentTransaction.*')
                ->get();

            $data['getEconomicDetails'] = DB::table('tbleconomicCode')->where('ID', $economicCodeID)->value('description') . ' | Year: ' . $year . ' | Month: ' . $month;
            return view('funds.treasureCashBook.paymentDetails', $data);
        } else {
            return redirect()->route('cashbook')->with('warning', 'Sorry, we cannot get your record. Please try again.');
        }
    }
}//end class
