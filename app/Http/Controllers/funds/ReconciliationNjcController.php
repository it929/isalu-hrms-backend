<?php

namespace App\Http\Controllers\funds;



use App\Http\Requests;
use App\Permission;
use Illuminate\Http\Request;
use App\Role;
use App\User;
use Auth;
use Carbon\Carbon;
use Entrust;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ReconciliationNjcController extends Basefunction
{

    //load entry form
    public function index()
    {
        // Contract Type
        $data['contractType'] = DB::table('tblcontractType')->where('status', 1)->get();
        $data['allocationType'] = DB::table('tblallocation_type')->where('status', 1)->get();
        ///
        return view('funds.njcReconciliation.addRefunds', $data);
    }

    //fetch Economic Code : JSON CALL
    public function fetchEconomicCode(Request $request)
    {
        $data = $this->getEconomicCode(trim($request['allocationTypeID']), trim($request['contractTypeID']));
        return response()->json($data['ecoCode']);
    }

    //add new entry
    public function postRefunds(Request $request)
    {
        $this->validate($request, [
            'numberOfVoucher'         => 'integer',
            'fromWhomReceived'         => 'required',
            'descriptionOfReceipt'     => 'required|string',
            //'economicCode'			=> 'numeric',
            'numberOfTreasury'        => 'numeric',
            'tsaBank'                => 'required',
            'refundsDate'           => 'required|date'
        ]);
        //Assign
        $numberOfVoucher         = trim($request['numberOfVoucher']);
        $fromWhomReceived         = trim($request['fromWhomReceived']);
        $descriptionOfReceipt     = trim($request['descriptionOfReceipt']);
        $economicCodeID         = trim($request['economicCode']);
        $numberOfTreasury         = trim($request['numberOfTreasury']);
        $tsaBank                 = trim($request['tsaBank']);
        $refundsDate             = trim($request['refundsDate']);
        //
        if (DB::table('tbleconomicCode')->where('ID', $economicCodeID)->first()) {
            $economicCode = DB::table('tbleconomicCode')->where('ID', $economicCodeID)->value('economicCode');
        } else {
            $economicCode = null;
        }
        //
        $success = DB::table('treasury_refund')->insert(array(
            'number_of_voucher'      => $numberOfVoucher,
            'from_whom_received'     => $fromWhomReceived,
            'des_of_receipt'          => $descriptionOfReceipt,
            'economic_code_ncoa'      => $economicCode,
            'number_of_treasury'      => $numberOfTreasury,
            'amount_tsa_bank'          => $tsaBank,
            'economicID'              => $economicCodeID,
            'date'                  => $refundsDate,
            'created_at'              => date('Y-m-d')
        ));
        if ($success) {
            return redirect()->route('createRefunds')->with('message', 'successfully added');
        } else {
            return redirect()->route('createRefunds')->with('error', 'Sorry, we cannot complete this operation. Please try again');
        }
        //
    }


    //select report
    public function createTreasuryReport()
    {

        return view('funds.njcReconciliation.index');
    }


    public function postReport(Request $request)
    {
        $this->validate($request, [
            'getYear'         => 'required|integer',
            'getFrom'         => 'required|date',
            'getTo'         => 'required|date',
            'reportType'    =>  'numeric',
        ]);
        //Assign
        Session::put('getYear', strtoupper(trim($request['getYear'])));
        Session::put('getFrom', strtoupper(trim($request['getFrom'])));
        Session::put('getTo', strtoupper(trim($request['getTo'])));
        Session::put('reportType', (trim($request['reportType'])));

        return redirect()->route('viewTreasuryReport');
    }

    //
    public function viewReport()
    {
        $getFrom        =  Session::get('getFrom');
        $getTo        =  Session::get('getTo');
        $getYear        =  Session::get('getYear');
        $reportType     =  Session::get('reportType');
        $data['reportType'] = $reportType;
        if (empty($getFrom) or empty($getTo) or empty($getYear)) {
            return redirect()->route('treasuryReport')->with('No data/year selected');
        }
        //PAYMENT MADE BY CPO
        // $allPaymentMadeByCPO  = DB::table('tblepayment')
        //     ->Join('tblpaymentTransaction', 'tblpaymentTransaction.ID', '=', 'tblepayment.transactionID')
        //     ->leftjoin('tblallocation_type', 'tblallocation_type.ID', '=', 'tblpaymentTransaction.allocationType')
        //     ->leftjoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
        //     ->where('tblepayment.mandate_status', 0)
        //     ->whereBetween('tblepayment.date', [$getFrom, $getTo])
        //     ->select('*', 'tblepayment.date as approveDate', 'tblepayment.amount as amountPaid')
        //     ->orderBy('tblepayment.date', 'Desc')
        //     ->get();


        // Optional (prevents GROUP_CONCAT truncation if many beneficiaries)
        DB::statement("SET SESSION group_concat_max_len = 100000");

        $allPaymentMadeByCPO  = DB::table('tblepayment')
            ->join('tblpaymentTransaction', 'tblpaymentTransaction.ID', '=', 'tblepayment.transactionID')
            ->leftJoin('tblallocation_type', 'tblallocation_type.ID', '=', 'tblpaymentTransaction.allocationType')
            ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
            ->where('tblepayment.mandate_status', 0)
            ->whereBetween('tblepayment.date', [$getFrom, $getTo])
            ->groupBy('tblepayment.transactionID')
            ->selectRaw("
                -- keep same name used everywhere
                tblepayment.transactionID AS transactionID,

                -- blade uses ->date, your loop uses ->approveDate
                MAX(tblepayment.date) AS date,
                MAX(tblepayment.date) AS approveDate,

                -- blade uses ->amount, your loop uses ->amountPaid
                SUM(tblepayment.amount) AS amount,
                SUM(tblepayment.amount) AS amountPaid,

                -- your loop uses ->economicCode
                MAX(tbleconomicCode.economicCode) AS economicCode,

                -- blade uses ->purpose
                SUBSTRING_INDEX(
                    GROUP_CONCAT(DISTINCT tblepayment.purpose ORDER BY tblepayment.id SEPARATOR ' | '),
                    ' | ',
                    1
                ) AS purpose,

                -- blade uses ->contractor (Safia Abdullahi and 20 others)
                CASE
                    WHEN COUNT(DISTINCT tblepayment.contractor) > 1 THEN CONCAT(
                        SUBSTRING_INDEX(
                            GROUP_CONCAT(DISTINCT tblepayment.contractor ORDER BY tblepayment.contractor SEPARATOR ', '),
                            ', ',
                            1
                        ),
                        ' and ',
                        (COUNT(DISTINCT tblepayment.contractor) - 1),
                        ' others'
                    )
                    ELSE MAX(tblepayment.contractor)
                END AS contractor
            ")
            ->orderByDesc(DB::raw('MAX(tblepayment.date)'))
            ->get();


        $data['allPaymentMadeByCPO'] = $allPaymentMadeByCPO;
        $data['getMonthAndYearFrom'] = date('F', strtotime($getFrom)) . ', ' . date('Y', strtotime($getFrom));
        $data['getMonthAndYearTo']   = date('F', strtotime($getTo)) . ', ' . date('Y', strtotime($getTo));

        // Get CPO Payment Records
        $dateCPO  = array();
        $transactionID  = array();
        $dVBN = array();
        $payee     = array();
        $description = array();
        $economicCode_NCOA = array();
        $amount_CPO_bank    = array();
        $arrayCPOKey = 1;
        foreach ($allPaymentMadeByCPO as $listCPO) {
            $dateCPO[$arrayCPOKey]     = $listCPO->approveDate;
            $transactionID[$arrayCPOKey]     = $listCPO->transactionID;
            $dVBN[$arrayCPOKey]             = ' - ';
            $payee[$arrayCPOKey] = $listCPO->contractor;
            $description[$arrayCPOKey] = substr($listCPO->purpose, 0, 1000);
            $economicCode_NCOA[$arrayCPOKey] = $listCPO->economicCode;
            $amount_CPO_bank[$arrayCPOKey] = $listCPO->amountPaid;
            $arrayCPOKey++;
        }
        //
        $data['dateCPO']          = $dateCPO;
        $data['transactionID']  = $transactionID;
        $data['dVBN']             = $dVBN;
        $data['payee']             = $payee;
        $data['description']     = $description;
        $data['economicCode_NCOA'] = $economicCode_NCOA;
        $data['amount_CPO_bank'] = $amount_CPO_bank;
        //end CPO Payment Records

        //AMOUNT REFUND
        $allPaymentRefund  = DB::table('treasury_refund')
            ->whereBetween('treasury_refund.created_at', [$getFrom, $getTo])
            ->where('status', 1)
            ->select('*', 'treasury_refund.economic_code_ncoa as economicCode')
            ->orderBy('treasury_refund.id', 'Desc')
            ->get();

        // Get Refunds Records
        $date  = array();
        $number_of_voucher  = array();
        $from_whom_received = array();
        $des_of_receipt     = array();
        $economic_code_ncoa = array();
        $number_of_treasury = array();
        $amount_tsa_bank    = array();
        $arrayKey = 1;

        foreach ($allPaymentRefund as $listAll) {
            $date[$arrayKey]                 = $listAll->created_at;
            $number_of_voucher[$arrayKey]     = $listAll->number_of_voucher;
            $from_whom_received[$arrayKey]     = $listAll->from_whom_received;
            $des_of_receipt[$arrayKey]          = $listAll->des_of_receipt;
            $economic_code_ncoa[$arrayKey]     = $listAll->economic_code_ncoa;
            $number_of_treasury[$arrayKey]     = $listAll->number_of_treasury;
            $amount_tsa_bank[$arrayKey]     = $listAll->amount_tsa_bank;
            $arrayKey++;
        }
        $data['allPaymentRefund']   = $allPaymentRefund;
        //
        $data['date']                  = $date;
        $data['number_of_voucher']  = $number_of_voucher;
        $data['from_whom_received'] = $from_whom_received;
        $data['des_of_receipt']     = $des_of_receipt;
        $data['economic_code_ncoa'] = $economic_code_ncoa;
        $data['number_of_treasury'] = $number_of_treasury;
        $data['amount_tsa_bank']    = $amount_tsa_bank;
        //end Refund Records
        // dd($data);

        return view('funds.njcReconciliation.show', $data);
    } //End Function

    //Update Reconciliation Table
    /*public function refundsReconciliation($intTransactionID, $stringReceivedFrom='NJC', $stringDescription, $IntEconomicCode, $intEconomicID, $IntNoTreasury='-', $floatAmount, $date)
    {
        if(DB::table('tbleconomiccode')->where('ID', $intEconomicID)->first())
        {
            $economicCode = DB::table('tbleconomiccode')->where('ID', $intEconomicID)->value('economicCode');
        }else{
            $economicCode = null;
        }
        DB::table('treasury_refund')->insert(array(
            'number_of_voucher'     => $intTransactionID,
            'from_whom_received'    => $stringReceivedFrom,
            'des_of_receipt'        => $stringDescription,
            'economic_code_ncoa'    => $economicCode,
            'number_of_treasury'    => $IntNoTreasury,
            'amount_tsa_bank'       => $floatAmount,
            'economicID'            => $intEconomicID,
            'date'                  => $date,
            'created_at'            => date('Y-m-d')
        ));
    }*/ //end function

}//END CLASS
