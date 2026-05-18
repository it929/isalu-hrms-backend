<?php

namespace App\Http\Controllers\funds;



use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class VoucherDisplayController extends BasefunctionController
{

    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        // $this->activeMonth = $request->session()->get('activeMonth');
        // $this->activeYear = $request->session()->get('activeYear');
                return $next($request);
        });
    }

    public function createContractVoucher()
    {
        Session::forget('getYear');
        Session::forget('getFrom');
        Session::forget('getTo');
        //
        $data['subdescriptions']  = $this->recurrentSubdescriptionCode();
        $data['companyDetails'] = DB::table('tblcompany')->get();
        $data['fileRefer'] = DB::table('tbldepartment_fileno')
            ->where('tbldepartment_fileno.account_type', 'OVERHEAD COST')
            ->orderby('tbldepartment_fileno.filerefer', 'Asc')
            ->get();
        return view('capitalRecurrent.journal', $data);
    }



    public function viewVoucher($transactionID = null, $claimID = null)
    {
        Session::forget('transactionID');
        Session::forget('currentDivisionID');

        if (!DB::table('tblpaymentTransaction')->where('ID', '=', $transactionID)->first()) {
            return redirect('voucherDisplay/displayVoucher')->with('err', 'Voucher not found !!!');
        }
        $checkDriverVoucher = DB::table('tbldrivertour')->where('voucherId', '=', $transactionID)->count();
        if ($checkDriverVoucher == 1) {
            //********* Drivers Vouchers


            $data['list']  = DB::table('tblpaymentTransaction')
                ->leftJoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
                ->leftJoin('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
                //->leftJoin('users','users.ID','=','tblpaymentTransaction.preparedBy')
                ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
                ->leftJoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
                ->leftJoin('tblcontractType', 'tblcontractType.ID', '=', 'tblpaymentTransaction.contractTypeID')
                ->where('tblpaymentTransaction.ID', '=', $transactionID)
                ->select('*', 'tblpaymentTransaction.ID as transID', 'tblpaymentTransaction.status as payStatus', 'tblcontractType.contractType as ecoHead', 'tblcontractDetails.claimid as isClaimId')
                ->first();

            $b   = $this->VoucherFinancialInfo($transactionID);
            $data['bbf'] = $b->BBF;
            $data['contractAmount'] = $b->contractValue;

            $data['status']     = DB::table('tblstatus')->where('code', '=', $data['list']->payStatus)->first();

            $data['preparedBy'] = DB::table('users')->where('username', '=', $data['list']->preparedBy)->orWhere('id', '=', $data['list']->preparedBy)->first();

            $data['libilityBy'] = DB::table('users')->where('username', '=', $data['list']->liabilityBy)->orWhere('id', '=', $data['list']->liabilityBy)->first();

            $data['checkBy']    = DB::table('users')->where('username', '=', $data['list']->checkBy)->orWhere('id', '=', $data['list']->checkBy)->first();

            $data['approvedBy'] = DB::table('users')->where('username', '=', $data['list']->approvedBy)->orWhere('id', '=', $data['list']->approvedBy)->first();

            $data['auditedBy'] = DB::table('users')->where('username', '=', $data['list']->auditedBy)->orWhere('id', '=', $data['list']->auditedBy)->first();
            //	dd($data['approvedBy']);

            $data['vatpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->VATPayeeID)->first();
            $data['whtpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->WHTPayeeID)->first();
            //dd($data['list']->totalPayment );


            $data['count'] = DB::table('tblvoucherBeneficiary')
                ->where('voucherID', '=', $data['list']->transID)
                ->count();

            // dd($data['count']);
            $data['staff'] = DB::table('tblvoucherBeneficiary')
                ->leftjoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblvoucherBeneficiary.bankID')
                ->where('voucherID', '=', $data['list']->transID)
                ->get();
            // dd($data['staff']);
            $data['details'] = DB::table('tbldrivertour')->where('voucherId', '=', $transactionID)->get();

            $data['beneficiary'] = DB::table('tblvoucherBeneficiary')->where('voucherID', '=', $transactionID)->first();

            return view('funds.voucherDisplay.driversVouchers', $data);



            //********* End Drivers Voucher
        }
        $data['isClaim'] = $claimID;
        $data['discr'] = db::table('tblcontractDetails')->where('ID', db::table('tblpaymentTransaction')->where('ID', $transactionID)->value('contractID'))->value('ContractDescriptions');
        // dd($data['discr']);

        $isAdvance = DB::table('tblpaymentTransaction')->where('ID', '=', $transactionID)->first();
        if ($isAdvance->is_advances == 1) {


            $data['list']  = DB::table('tblpaymentTransaction')
                ->leftJoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
                ->leftJoin('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
                //->leftJoin('users','users.ID','=','tblpaymentTransaction.preparedBy')
                ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
                ->leftJoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
                ->leftJoin('tblcontractType', 'tblcontractType.ID', '=', 'tblpaymentTransaction.contractTypeID')
                ->where('tblpaymentTransaction.ID', '=', $transactionID)
                ->select('*', 'tblpaymentTransaction.ID as transID', 'tblpaymentTransaction.is_advances as isAdvances', 'tblpaymentTransaction.status as payStatus', 'tblcontractType.contractType as ecoHead', 'tblpaymentTransaction.is_retired as retiredStatus', 'tbleconomicHead.Code as ecoHeadCode', 'tbleconomicCode.description as ecoCodeDesc', 'tblcontractDetails.claimid as isClaimId')
                ->first();
            //dd($data['list'] );

            $b   = $this->VoucherFinancialInfo($transactionID);
            $data['bbf'] = $b->BBF;
            $data['contractAmount'] = $b->contractValue;

            $data['status']     = DB::table('tblstatus')->where('code', '=', $data['list']->payStatus)->first();

            $data['preparedBy'] = DB::table('users')->where('username', '=', $data['list']->preparedBy)->orWhere('id', '=', $data['list']->preparedBy)->first();

            $data['libilityBy'] = DB::table('users')->where('username', '=', $data['list']->liabilityBy)->orWhere('id', '=', $data['list']->liabilityBy)->first();

            $data['checkBy']    = DB::table('users')->where('username', '=', $data['list']->checkBy)->orWhere('id', '=', $data['list']->checkBy)->first();

            $data['approvedBy'] = DB::table('users')->where('username', '=', $data['list']->approvedBy)->orWhere('id', '=', $data['list']->approvedBy)->first();

            $data['auditedBy'] = DB::table('users')->where('username', '=', $data['list']->auditedBy)->orWhere('id', '=', $data['list']->auditedBy)->first();
            //	dd($data['approvedBy']);

            $data['vatpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->VATPayeeID)->first();
            $data['whtpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->WHTPayeeID)->first();
            //dd($data['list']->totalPayment );


            $data['count'] = DB::table('tblvoucherBeneficiary')
                ->where('voucherID', '=', $data['list']->transID)
                ->count();

            // dd($data['count']);
            $data['staff'] = DB::table('tblvoucherBeneficiary')
                ->leftjoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblvoucherBeneficiary.bankID')
                ->where('voucherID', '=', $data['list']->transID)
                ->get();
            // dd($data['staff']);

            return view('funds.voucherDisplay.advancesVoucher', $data);
        }

        if ($isAdvance->is_advances == 2) {


            $data['list']  = DB::table('tblpaymentTransaction')
                ->leftJoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
                ->leftJoin('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
                //->leftJoin('users','users.ID','=','tblpaymentTransaction.preparedBy')
                ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
                ->leftJoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
                ->leftJoin('tblcontractType', 'tblcontractType.ID', '=', 'tblpaymentTransaction.contractTypeID')
                ->where('tblpaymentTransaction.ID', '=', $transactionID)
                ->select('*', 'tblpaymentTransaction.ID as transID', 'tblpaymentTransaction.status as payStatus', 'tblcontractType.contractType as ecoHead', 'tblcontractDetails.claimid as isClaimId')
                ->first();

            $b   = $this->VoucherFinancialInfo($transactionID);
            $data['bbf'] = $b->BBF;
            $data['contractAmount'] = $b->contractValue;

            $data['status']     = DB::table('tblstatus')->where('code', '=', $data['list']->payStatus)->first();

            $data['preparedBy'] = DB::table('users')->where('username', '=', $data['list']->preparedBy)->orWhere('id', '=', $data['list']->preparedBy)->first();

            $data['libilityBy'] = DB::table('users')->where('username', '=', $data['list']->liabilityBy)->orWhere('id', '=', $data['list']->liabilityBy)->first();

            $data['checkBy']    = DB::table('users')->where('username', '=', $data['list']->checkBy)->orWhere('id', '=', $data['list']->checkBy)->first();

            $data['approvedBy'] = DB::table('users')->where('username', '=', $data['list']->approvedBy)->orWhere('id', '=', $data['list']->approvedBy)->first();

            $data['auditedBy'] = DB::table('users')->where('username', '=', $data['list']->auditedBy)->orWhere('id', '=', $data['list']->auditedBy)->first();
            //	dd($data['approvedBy']);

            $data['vatpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->VATPayeeID)->first();
            $data['whtpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->WHTPayeeID)->first();
            //dd($data['list']->totalPayment );


            $data['count'] = DB::table('tblvoucherBeneficiary')
                ->where('voucherID', '=', $data['list']->transID)
                ->count();

            // dd($data['count']);
            $data['staff'] = DB::table('tblvoucherBeneficiary')
                ->leftjoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblvoucherBeneficiary.bankID')
                ->where('voucherID', '=', $data['list']->transID)
                ->get();
            // dd($data['staff']);

            return view('funds.voucherDisplay.salaryAdvanceVoucher', $data);
        }


        // 		if($isAdvance->is_pay_in_form == 1){
        // 			return redirect(route('payingInVoucher', $isAdvance->contractID));
        // 		}

        /* Salary personnel Voucher */
        if ($isAdvance->is_advances == 3) {

            $data['personnelVoucher'] = DB::table('tblpaymentTransaction')
                ->where('tblpaymentTransaction.ID', '=', $transactionID)
                ->leftjoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
                ->leftjoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
                ->leftJoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
                ->leftJoin('tblcontractType', 'tblcontractType.ID', '=', 'tblpaymentTransaction.contractTypeID')
                ->leftjoin('users', 'tblpaymentTransaction.preparedBy', '=', 'users.id')
                // ->select('tblpaymentTransaction.*', 'users.name', 'tbleconomicCode.economicCode')
                ->select('*', 'tblpaymentTransaction.ID as transID', 'users.name', 'tblpaymentTransaction.status as payStatus', 'tblcontractType.contractType as ecoHead', 'tbleconomicHead.Code as ecoHeadCode', 'tbleconomicCode.description as ecoCodeDesc', 'tblcontractDetails.claimid as isClaimId')
                ->first();
                $b   = $this->VoucherFinancialInfo($transactionID);
                $data['bbf'] = $b->BBF;
                $data['contractAmount'] = $b->contractValue;

                $data['status']     = DB::table('tblstatus')->where('code', '=', $data['personnelVoucher']->payStatus)->first();

                $data['preparedBy'] = DB::table('users')->where('username', '=', $data['personnelVoucher']->preparedBy)->orWhere('id', '=', $data['personnelVoucher']->preparedBy)->first();

                $data['libilityBy'] = DB::table('users')->where('username', '=', $data['personnelVoucher']->liabilityBy)->orWhere('id', '=', $data['personnelVoucher']->liabilityBy)->first();

                $data['checkBy']    = DB::table('users')->where('username', '=', $data['personnelVoucher']->checkBy)->orWhere('id', '=', $data['personnelVoucher']->checkBy)->first();

                $data['approvedBy'] = DB::table('users')->where('username', '=', $data['personnelVoucher']->approvedBy)->orWhere('id', '=', $data['personnelVoucher']->approvedBy)->first();

                $data['auditedBy'] = DB::table('users')->where('username', '=', $data['personnelVoucher']->auditedBy)->orWhere('id', '=', $data['personnelVoucher']->auditedBy)->first();

                $data['economicHead'] = DB::table('tbleconomicCode')
                ->where('tbleconomicCode.ID', '=', $data['personnelVoucher']->economicCodeID)
                ->leftjoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
                ->value('tbleconomicHead.Code');

            $data['vRef'] = DB::table('tblpaymentTransaction')->where('ID', '=', $transactionID)->value('vref_no');
            $data['transactionRef'] = DB::table('tblcontractDetails')->where('ID', '=', $data['personnelVoucher']->contractID)->value('transaction_vref');
            return view('funds.salaryPersonnel.singlePersonnelVoucher', $data);
        }

        if ($isAdvance->is_pay_in_form == 1) {
            return redirect(route('payingInVoucher', $isAdvance->contractID));
        }
        /* Salary Personnel Voucher */
        // dd($isAdvance->is_advances);

        if ($claimID == '') {

            $data['list']  = DB::table('tblpaymentTransaction')
                ->leftJoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
                ->leftJoin('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
                //->leftJoin('users','users.ID','=','tblpaymentTransaction.preparedBy')
                ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
                ->leftJoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
                ->leftJoin('tblcontractType', 'tblcontractType.ID', '=', 'tblpaymentTransaction.contractTypeID')
                ->where('tblpaymentTransaction.ID', '=', $transactionID)
                ->select('*', 'tblpaymentTransaction.ID as transID', 'tblpaymentTransaction.is_advances as isAdvances', 'tblpaymentTransaction.status as payStatus', 'tblcontractType.contractType as ecoHead', 'tbleconomicHead.Code as ecoHeadCode', 'tbleconomicCode.description as ecoCodeDesc', 'tblcontractDetails.claimid as isClaimId')
                ->first();

            $b   = $this->VoucherFinancialInfo($transactionID);
            $data['bbf'] = $b->BBF;
            $data['contractAmount'] = $b->contractValue;

            $data['status']     = DB::table('tblstatus')->where('code', '=', $data['list']->payStatus)->first();

            $data['preparedBy'] = DB::table('users')->where('username', '=', $data['list']->preparedBy)->orWhere('id', '=', $data['list']->preparedBy)->first();

            $data['libilityBy'] = DB::table('users')->where('username', '=', $data['list']->liabilityBy)->orWhere('id', '=', $data['list']->liabilityBy)->first();

            $data['checkBy']    = DB::table('users')->where('username', '=', $data['list']->checkBy)->orWhere('id', '=', $data['list']->checkBy)->first();

            $data['approvedBy'] = DB::table('users')->where('username', '=', $data['list']->approvedBy)->orWhere('id', '=', $data['list']->approvedBy)->first();

            $data['auditedBy'] = DB::table('users')->where('username', '=', $data['list']->auditedBy)->orWhere('id', '=', $data['list']->auditedBy)->first();
            //	dd($data['approvedBy']);

            $data['vatpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->VATPayeeID)->first();
            $data['whtpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->WHTPayeeID)->first();
            //dd($data['list']->totalPayment );
            // dd($data['list']->stampduty );
            //   dd($data['whtpayee'] );


            $data['count'] = DB::table('tblvoucherBeneficiary')
                ->where('voucherID', '=', $data['list']->transID)
                ->count();

            // dd($data['count']);
            $data['staff'] = DB::table('tblvoucherBeneficiary')
                ->leftjoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblvoucherBeneficiary.bankID')
                ->where('voucherID', '=', $data['list']->transID)
                ->get();
            // dd($data['staff']);

            //get user who prepared voucher role
            $data['voucherPreparedByRole'] = DB::table('assign_user_role')->where('userID', '=', $data['list']->preparedBy)->value('roleID');

            return view('funds.voucherDisplay.displayVoucher', $data);
        } else {

            $data['trans']  = DB::table('tblpaymentTransaction')
                ->leftJoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
                ->leftJoin('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
                ->leftJoin('users', 'users.ID', '=', 'tblpaymentTransaction.preparedBy')
                ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
                ->leftJoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
                ->leftJoin('tblcontractType', 'tblcontractType.ID', '=', 'tblpaymentTransaction.contractTypeID')
                ->where('tblpaymentTransaction.contractID', '=', $claimID)
                ->select('*', 'tblpaymentTransaction.ID as transID', 'tblpaymentTransaction.is_advances as isAdvances', 'tblpaymentTransaction.status as payStatus', 'tblcontractType.contractType as ecoHead', 'tbleconomicHead.Code as ecoHeadCode', 'tbleconomicCode.description as ecoCodeDesc', 'tblcontractDetails.claimid as isClaimId')
                ->get();
            $data['list']  = DB::table('tblpaymentTransaction')
                ->leftJoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
                ->leftJoin('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
                ->leftJoin('users', 'users.ID', '=', 'tblpaymentTransaction.preparedBy')
                ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
                ->leftJoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
                ->leftJoin('tblcontractType', 'tblcontractType.ID', '=', 'tblpaymentTransaction.contractTypeID')
                ->where('tblpaymentTransaction.contractID', '=', $claimID)
                ->select('*', 'tblpaymentTransaction.ID as transID', 'tblpaymentTransaction.is_advances as isAdvances', 'tblpaymentTransaction.status as payStatus', 'tblcontractType.contractType as ecoHead', 'tbleconomicHead.Code as ecoHeadCode', 'tbleconomicCode.description as ecoCodeDesc', 'tblcontractDetails.claimid as isClaimId')
                ->first();
            $data['totalAmount']  = DB::table('tblpaymentTransaction')
                ->where('tblpaymentTransaction.contractID', '=', $claimID)
                ->sum('totalPayment');
            //dd($data['trans']);



            foreach ($data['trans'] as $list) {
                $data['status'] = DB::table('tblstatus')->where('code', '=', $list->payStatus)->first();
                $data['preparedBy'] = DB::table('users')->where('username', '=', $list->preparedBy)->orWhere('id', '=', $list->preparedBy)->value('name');

                $data['libilityBy'] = DB::table('users')->where('username', '=', $list->liabilityBy)->orWhere('id', '=', $list->liabilityBy)->value('name');
                $data['checkBy'] = DB::table('users')->where('username', '=', $list->checkBy)->where('id', '=', $list->checkBy)->value('name');
                $data['approvedBy'] = DB::table('users')->where('username', '=', $list->approvedBy)->where('id', '=', $list->approvedBy)->value('name');


                /*$data['count'] = DB::table('tblvoucherBeneficiary')
        ->where('voucherID', '=', $list->transID)
        ->count();

        $data['staff'] = DB::table('tblvoucherBeneficiary')
        ->join('tblbanklist','tblbanklist.bankID','=','tblvoucherBeneficiary.bankID')
        ->where('voucherID', '=', $list->transID)
        ->get();*/
            }

            return view('funds.voucherDisplay.displayStaffVoucher', $data);
        }
    }

    public function viewVoucherOld($transactionID = null, $claimID = null)
    {
        Session::forget('transactionID');
        Session::forget('currentDivisionID');

        if (!DB::table('tblpaymentTransaction')->where('ID', '=', $transactionID)->first()) {
            return redirect('voucherDisplay/displayVoucher')->with('err', 'Voucher not found !!!');
        }
        $checkDriverVoucher = DB::table('tbldrivertour')->where('voucherId', '=', $transactionID)->count();
        if ($checkDriverVoucher == 1) {
            //********* Drivers Vouchers


            $data['list']  = DB::table('tblpaymentTransaction')
                ->leftJoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
                ->leftJoin('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
                //->leftJoin('users','users.ID','=','tblpaymentTransaction.preparedBy')
                ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
                ->leftJoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
                ->leftJoin('tblcontractType', 'tblcontractType.ID', '=', 'tblpaymentTransaction.contractTypeID')
                ->where('tblpaymentTransaction.ID', '=', $transactionID)
                ->select('*', 'tblpaymentTransaction.ID as transID', 'tblpaymentTransaction.status as payStatus', 'tblcontractType.contractType as ecoHead')
                ->first();

            $b   = $this->VoucherFinancialInfo($transactionID);
            $data['bbf'] = $b->BBF;
            $data['contractAmount'] = $b->contractValue;

            $data['status']     = DB::table('tblstatus')->where('code', '=', $data['list']->payStatus)->first();

            $data['preparedBy'] = DB::table('users')->where('username', '=', $data['list']->preparedBy)->orWhere('id', '=', $data['list']->preparedBy)->first();

            $data['libilityBy'] = DB::table('users')->where('username', '=', $data['list']->liabilityBy)->orWhere('id', '=', $data['list']->liabilityBy)->first();

            $data['checkBy']    = DB::table('users')->where('username', '=', $data['list']->checkBy)->orWhere('id', '=', $data['list']->checkBy)->first();

            $data['approvedBy'] = DB::table('users')->where('username', '=', $data['list']->approvedBy)->orWhere('id', '=', $data['list']->approvedBy)->first();

            $data['auditedBy'] = DB::table('users')->where('username', '=', $data['list']->auditedBy)->orWhere('id', '=', $data['list']->auditedBy)->first();
            //	dd($data['approvedBy']);

            $data['vatpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->VATPayeeID)->first();
            $data['whtpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->WHTPayeeID)->first();
            //dd($data['list']->totalPayment );


            $data['count'] = DB::table('tblvoucherBeneficiary')
                ->where('voucherID', '=', $data['list']->transID)
                ->count();

            // dd($data['count']);
            $data['staff'] = DB::table('tblvoucherBeneficiary')
                ->leftjoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblvoucherBeneficiary.bankID')
                ->where('voucherID', '=', $data['list']->transID)
                ->get();
            // dd($data['staff']);
            $data['details'] = DB::table('tbldrivertour')->where('voucherId', '=', $transactionID)->get();

            $data['beneficiary'] = DB::table('tblvoucherBeneficiary')->where('voucherID', '=', $transactionID)->first();

            return view('funds.voucherDisplay.driversVouchers', $data);



            //********* End Drivers Voucher
        }
        $data['isClaim'] = $claimID;
        $data['discr'] = db::table('tblcontractDetails')->where('ID', db::table('tblpaymentTransaction')->where('ID', $transactionID)->value('contractID'))->value('ContractDescriptions');
        // dd($data['discr']);

        $isAdvance = DB::table('tblpaymentTransaction')->where('ID', '=', $transactionID)->first();
        if ($isAdvance->is_advances == 1) {


            $data['list']  = DB::table('tblpaymentTransaction')
                ->leftJoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
                ->leftJoin('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
                //->leftJoin('users','users.ID','=','tblpaymentTransaction.preparedBy')
                ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
                ->leftJoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
                ->leftJoin('tblcontractType', 'tblcontractType.ID', '=', 'tblpaymentTransaction.contractTypeID')
                ->where('tblpaymentTransaction.ID', '=', $transactionID)
                ->select('*', 'tblpaymentTransaction.ID as transID', 'tblpaymentTransaction.status as payStatus', 'tblcontractType.contractType as ecoHead', 'tblpaymentTransaction.is_retired as retiredStatus')
                ->first();
            //dd($data['list'] );

            $b   = $this->VoucherFinancialInfo($transactionID);
            $data['bbf'] = $b->BBF;
            $data['contractAmount'] = $b->contractValue;

            $data['status']     = DB::table('tblstatus')->where('code', '=', $data['list']->payStatus)->first();

            $data['preparedBy'] = DB::table('users')->where('username', '=', $data['list']->preparedBy)->orWhere('id', '=', $data['list']->preparedBy)->first();

            $data['libilityBy'] = DB::table('users')->where('username', '=', $data['list']->liabilityBy)->orWhere('id', '=', $data['list']->liabilityBy)->first();

            $data['checkBy']    = DB::table('users')->where('username', '=', $data['list']->checkBy)->orWhere('id', '=', $data['list']->checkBy)->first();

            $data['approvedBy'] = DB::table('users')->where('username', '=', $data['list']->approvedBy)->orWhere('id', '=', $data['list']->approvedBy)->first();

            $data['auditedBy'] = DB::table('users')->where('username', '=', $data['list']->auditedBy)->orWhere('id', '=', $data['list']->auditedBy)->first();
            //	dd($data['approvedBy']);

            $data['vatpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->VATPayeeID)->first();
            $data['whtpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->WHTPayeeID)->first();
            //dd($data['list']->totalPayment );


            $data['count'] = DB::table('tblvoucherBeneficiary')
                ->where('voucherID', '=', $data['list']->transID)
                ->count();

            // dd($data['count']);
            $data['staff'] = DB::table('tblvoucherBeneficiary')
                ->leftjoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblvoucherBeneficiary.bankID')
                ->where('voucherID', '=', $data['list']->transID)
                ->get();
            // dd($data['staff']);

            return view('funds.voucherDisplay.advancesVoucher', $data);
        }

        if ($isAdvance->is_advances == 2) {


            $data['list']  = DB::table('tblpaymentTransaction')
                ->leftJoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
                ->leftJoin('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
                //->leftJoin('users','users.ID','=','tblpaymentTransaction.preparedBy')
                ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
                ->leftJoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
                ->leftJoin('tblcontractType', 'tblcontractType.ID', '=', 'tblpaymentTransaction.contractTypeID')
                ->where('tblpaymentTransaction.ID', '=', $transactionID)
                ->select('*', 'tblpaymentTransaction.ID as transID', 'tblpaymentTransaction.status as payStatus', 'tblcontractType.contractType as ecoHead')
                ->first();

            $b   = $this->VoucherFinancialInfo($transactionID);
            $data['bbf'] = $b->BBF;
            $data['contractAmount'] = $b->contractValue;

            $data['status']     = DB::table('tblstatus')->where('code', '=', $data['list']->payStatus)->first();

            $data['preparedBy'] = DB::table('users')->where('username', '=', $data['list']->preparedBy)->orWhere('id', '=', $data['list']->preparedBy)->first();

            $data['libilityBy'] = DB::table('users')->where('username', '=', $data['list']->liabilityBy)->orWhere('id', '=', $data['list']->liabilityBy)->first();

            $data['checkBy']    = DB::table('users')->where('username', '=', $data['list']->checkBy)->orWhere('id', '=', $data['list']->checkBy)->first();

            $data['approvedBy'] = DB::table('users')->where('username', '=', $data['list']->approvedBy)->orWhere('id', '=', $data['list']->approvedBy)->first();

            $data['auditedBy'] = DB::table('users')->where('username', '=', $data['list']->auditedBy)->orWhere('id', '=', $data['list']->auditedBy)->first();
            //	dd($data['approvedBy']);

            $data['vatpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->VATPayeeID)->first();
            $data['whtpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->WHTPayeeID)->first();
            //dd($data['list']->totalPayment );


            $data['count'] = DB::table('tblvoucherBeneficiary')
                ->where('voucherID', '=', $data['list']->transID)
                ->count();

            // dd($data['count']);
            $data['staff'] = DB::table('tblvoucherBeneficiary')
                ->leftjoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblvoucherBeneficiary.bankID')
                ->where('voucherID', '=', $data['list']->transID)
                ->get();
            // dd($data['staff']);

            return view('funds.voucherDisplay.salaryAdvanceVoucher', $data);
        }


        // 		if($isAdvance->is_pay_in_form == 1){
        // 			return redirect(route('payingInVoucher', $isAdvance->contractID));
        // 		}

        /* Salary personnel Voucher */
        if ($isAdvance->is_advances == 3) {

            $data['personnelVoucher'] = DB::table('tblpaymentTransaction')
                ->where('tblpaymentTransaction.ID', '=', $transactionID)
                ->leftjoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
                ->leftjoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
                ->leftjoin('users', 'tblpaymentTransaction.preparedBy', '=', 'users.id')
                ->select('tblpaymentTransaction.*', 'users.name', 'tbleconomicCode.economicCode')
                ->first();

            $data['economicHead'] = DB::table('tbleconomicCode')
                ->where('tbleconomicCode.ID', '=', $data['personnelVoucher']->economicCodeID)
                ->leftjoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
                ->value('tbleconomicHead.Code');

            $data['vRef'] = DB::table('tblpaymentTransaction')->where('ID', '=', $transactionID)->value('vref_no');
            $data['transactionRef'] = DB::table('tblcontractDetails')->where('ID', '=', $data['personnelVoucher']->contractID)->value('transaction_vref');
            return view('funds.salaryPersonnel.singlePersonnelVoucher', $data);
        }

        if ($isAdvance->is_pay_in_form == 1) {
            return redirect(route('payingInVoucher', $isAdvance->contractID));
        }
        /* Salary Personnel Voucher */
        // dd($isAdvance->is_advances);

        if ($claimID == '') {

            $data['list']  = DB::table('tblpaymentTransaction')
                ->leftJoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
                ->leftJoin('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
                //->leftJoin('users','users.ID','=','tblpaymentTransaction.preparedBy')
                ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
                ->leftJoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
                ->leftJoin('tblcontractType', 'tblcontractType.ID', '=', 'tblpaymentTransaction.contractTypeID')
                ->where('tblpaymentTransaction.ID', '=', $transactionID)
                ->select('*', 'tblpaymentTransaction.ID as transID', 'tblpaymentTransaction.status as payStatus', 'tblcontractType.contractType as ecoHead', 'tbleconomicHead.Code as ecoHeadCode', 'tbleconomicCode.description as ecoCodeDesc')
                ->first();

            $b   = $this->VoucherFinancialInfo($transactionID);
            $data['bbf'] = $b->BBF;
            $data['contractAmount'] = $b->contractValue;

            $data['status']     = DB::table('tblstatus')->where('code', '=', $data['list']->payStatus)->first();

            $data['preparedBy'] = DB::table('users')->where('username', '=', $data['list']->preparedBy)->orWhere('id', '=', $data['list']->preparedBy)->first();

            $data['libilityBy'] = DB::table('users')->where('username', '=', $data['list']->liabilityBy)->orWhere('id', '=', $data['list']->liabilityBy)->first();

            $data['checkBy']    = DB::table('users')->where('username', '=', $data['list']->checkBy)->orWhere('id', '=', $data['list']->checkBy)->first();

            $data['approvedBy'] = DB::table('users')->where('username', '=', $data['list']->approvedBy)->orWhere('id', '=', $data['list']->approvedBy)->first();

            $data['auditedBy'] = DB::table('users')->where('username', '=', $data['list']->auditedBy)->orWhere('id', '=', $data['list']->auditedBy)->first();
            //	dd($data['approvedBy']);

            $data['vatpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->VATPayeeID)->first();
            $data['whtpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->WHTPayeeID)->first();
            //dd($data['list']->totalPayment );
            // dd($data['list']->stampduty );
            //   dd($data['whtpayee'] );


            $data['count'] = DB::table('tblvoucherBeneficiary')
                ->where('voucherID', '=', $data['list']->transID)
                ->count();

            // dd($data['count']);
            $data['staff'] = DB::table('tblvoucherBeneficiary')
                ->leftjoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblvoucherBeneficiary.bankID')
                ->where('voucherID', '=', $data['list']->transID)
                ->get();
            // dd($data['staff']);

            //get user who prepared voucher role
            $data['voucherPreparedByRole'] = DB::table('assign_user_role')->where('userID', '=', $data['list']->preparedBy)->value('roleID');

            return view('funds.voucherDisplay.displayVoucher_2025_01_20', $data);
        } else {

            $data['trans']  = DB::table('tblpaymentTransaction')
                ->leftJoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
                ->leftJoin('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
                ->leftJoin('users', 'users.ID', '=', 'tblpaymentTransaction.preparedBy')
                ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
                ->leftJoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
                ->leftJoin('tblcontractType', 'tblcontractType.ID', '=', 'tblpaymentTransaction.contractTypeID')
                ->where('tblpaymentTransaction.contractID', '=', $claimID)
                ->select('*', 'tblpaymentTransaction.ID as transID', 'tblpaymentTransaction.status as payStatus', 'tblcontractType.contractType as ecoHead')
                ->get();
            $data['list']  = DB::table('tblpaymentTransaction')
                ->leftJoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
                ->leftJoin('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
                ->leftJoin('users', 'users.ID', '=', 'tblpaymentTransaction.preparedBy')
                ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
                ->leftJoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
                ->leftJoin('tblcontractType', 'tblcontractType.ID', '=', 'tblpaymentTransaction.contractTypeID')
                ->where('tblpaymentTransaction.contractID', '=', $claimID)
                ->select('*', 'tblpaymentTransaction.ID as transID', 'tblpaymentTransaction.status as payStatus', 'tblcontractType.contractType as ecoHead')
                ->first();
            $data['totalAmount']  = DB::table('tblpaymentTransaction')
                ->where('tblpaymentTransaction.contractID', '=', $claimID)
                ->sum('totalPayment');
            //dd($data['trans']);



            foreach ($data['trans'] as $list) {
                $data['status'] = DB::table('tblstatus')->where('code', '=', $list->payStatus)->first();
                $data['preparedBy'] = DB::table('users')->where('username', '=', $list->preparedBy)->orWhere('id', '=', $list->preparedBy)->value('name');

                $data['libilityBy'] = DB::table('users')->where('username', '=', $list->liabilityBy)->orWhere('id', '=', $list->liabilityBy)->value('name');
                $data['checkBy'] = DB::table('users')->where('username', '=', $list->checkBy)->where('id', '=', $list->checkBy)->value('name');
                $data['approvedBy'] = DB::table('users')->where('username', '=', $list->approvedBy)->where('id', '=', $list->approvedBy)->value('name');


                /*$data['count'] = DB::table('tblvoucherBeneficiary')
                ->where('voucherID', '=', $list->transID)
                ->count();

                $data['staff'] = DB::table('tblvoucherBeneficiary')
                ->join('tblbanklist','tblbanklist.bankID','=','tblvoucherBeneficiary.bankID')
                ->where('voucherID', '=', $list->transID)
                ->get();*/
            }

            return view('funds.voucherDisplay.displayStaffVoucher', $data);
        }
    }


    /****************** Test voucher display with capital **************************/

    public function testVoucher($transactionID = null, $claimID = null)
    {
        Session::forget('transactionID');
        Session::forget('currentDivisionID');

        if (!DB::table('tblpaymentTransaction')->where('ID', '=', $transactionID)->first()) {
            return redirect('voucherDisplay/displayVoucher')->with('err', 'Voucher not found !!!');
        }
        $data['isClaim'] = $claimID;
        $data['discr'] = db::table('tblcontractDetails')->where('ID', db::table('tblpaymentTransaction')->where('ID', $transactionID)->value('contractID'))->value('ContractDescriptions');
        //dd($data['discr']);
        if ($claimID == '') {

            $data['list']  = DB::table('tblpaymentTransaction')
                ->leftJoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
                ->leftJoin('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
                //->leftJoin('users','users.ID','=','tblpaymentTransaction.preparedBy')
                ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
                ->leftJoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
                ->leftJoin('tblcontractType', 'tblcontractType.ID', '=', 'tblpaymentTransaction.contractTypeID')
                ->where('tblpaymentTransaction.ID', '=', $transactionID)
                ->select('*', 'tblpaymentTransaction.ID as transID', 'tblpaymentTransaction.status as payStatus', 'tblcontractType.contractType as ecoHead')
                ->first();

            $b   = $this->VoucherFinancialInfo($transactionID);
            $data['bbf'] = $b->BBF;
            $data['contractAmount'] = $b->contractValue;

            $data['status']     = DB::table('tblstatus')->where('code', '=', $data['list']->payStatus)->first();

            $data['preparedBy'] = DB::table('users')->where('username', '=', $data['list']->preparedBy)->orWhere('id', '=', $data['list']->preparedBy)->first();

            $data['libilityBy'] = DB::table('users')->where('username', '=', $data['list']->liabilityBy)->orWhere('id', '=', $data['list']->liabilityBy)->first();

            $data['checkBy']    = DB::table('users')->where('username', '=', $data['list']->checkBy)->orWhere('id', '=', $data['list']->checkBy)->first();

            $data['approvedBy'] = DB::table('users')->where('username', '=', $data['list']->approvedBy)->orWhere('id', '=', $data['list']->approvedBy)->first();

            $data['auditedBy'] = DB::table('users')->where('username', '=', $data['list']->auditedBy)->orWhere('id', '=', $data['list']->auditedBy)->first();
            //	dd($data['approvedBy']);

            $data['vatpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->VATPayeeID)->first();
            $data['whtpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->WHTPayeeID)->first();
            //dd($data['list']->totalPayment );


            $data['count'] = DB::table('tblvoucherBeneficiary')
                ->where('voucherID', '=', $data['list']->transID)
                ->count();

            // dd($data['count']);
            $data['staff'] = DB::table('tblvoucherBeneficiary')
                ->leftjoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblvoucherBeneficiary.bankID')
                ->where('voucherID', '=', $data['list']->transID)
                ->get();
            // dd($data['staff']);

            return view('funds.voucherDisplay.testVoucher', $data);
        } else {

            $data['trans']  = DB::table('tblpaymentTransaction')
                ->leftJoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
                ->leftJoin('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
                ->leftJoin('users', 'users.ID', '=', 'tblpaymentTransaction.preparedBy')
                ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
                ->leftJoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
                ->leftJoin('tblcontractType', 'tblcontractType.ID', '=', 'tblpaymentTransaction.contractTypeID')
                ->where('tblpaymentTransaction.contractID', '=', $claimID)
                ->select('*', 'tblpaymentTransaction.ID as transID', 'tblpaymentTransaction.status as payStatus', 'tblcontractType.contractType as ecoHead')
                ->get();
            $data['list']  = DB::table('tblpaymentTransaction')
                ->leftJoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
                ->leftJoin('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
                ->leftJoin('users', 'users.ID', '=', 'tblpaymentTransaction.preparedBy')
                ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
                ->leftJoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
                ->leftJoin('tblcontractType', 'tblcontractType.ID', '=', 'tblpaymentTransaction.contractTypeID')
                ->where('tblpaymentTransaction.contractID', '=', $claimID)
                ->select('*', 'tblpaymentTransaction.ID as transID', 'tblpaymentTransaction.status as payStatus', 'tblcontractType.contractType as ecoHead')
                ->first();
            $data['totalAmount']  = DB::table('tblpaymentTransaction')
                ->where('tblpaymentTransaction.contractID', '=', $claimID)
                ->sum('totalPayment');
            //dd($data['trans']);


            foreach ($data['trans'] as $list) {
                $data['status'] = DB::table('tblstatus')->where('code', '=', $list->payStatus)->first();
                $data['preparedBy'] = DB::table('users')->where('username', '=', $list->preparedBy)->orWhere('id', '=', $list->preparedBy)->value('name');

                $data['libilityBy'] = DB::table('users')->where('username', '=', $list->liabilityBy)->orWhere('id', '=', $list->liabilityBy)->value('name');
                $data['checkBy'] = DB::table('users')->where('username', '=', $list->checkBy)->where('id', '=', $list->checkBy)->value('name');
                $data['approvedBy'] = DB::table('users')->where('username', '=', $list->approvedBy)->where('id', '=', $list->approvedBy)->value('name');


                /*$data['count'] = DB::table('tblvoucherBeneficiary')
        ->where('voucherID', '=', $list->transID)
        ->count();

        $data['staff'] = DB::table('tblvoucherBeneficiary')
        ->join('tblbanklist','tblbanklist.bankID','=','tblvoucherBeneficiary.bankID')
        ->where('voucherID', '=', $list->transID)
        ->get();*/
            }

            return view('funds.voucherDisplay.testVoucher', $data);
        }
    }
    /************************* End test voucher display with capital *************************/




    //update Parameters
    public function updateStaffVoucherParameter(Request $request)
    {
        $this->validate($request, [
            'levelThreeToSixRate'                 => 'required|numeric',
            'levelSevenToElevenRate'             => 'required|numeric',
            'levelTwelveToFourteenRate'         => 'required|numeric',
            'levelFifteenToSeventeenRate'         => 'required|numeric',
        ]);
        //
        $rate6                 = (trim($request['levelThreeToSixRate']));
        $rate11                = (trim($request['levelSevenToElevenRate']));
        $rate14                   = (trim($request['levelTwelveToFourteenRate']));
        $rate17                  = (trim($request['levelFifteenToSeventeenRate']));
        $date                    = date('Y-m-d');
        //
        DB::table('staffvoucherparameters')->where('id', 1)->update(array(
            'rate' => $rate6,
            'updated_at' => $date
        ));

        DB::table('staffvoucherparameters')->where('id', 2)->update(array(
            'rate' => $rate11,
            'updated_at' => $date
        ));

        DB::table('staffvoucherparameters')->where('id', 3)->update(array(
            'rate' => $rate14,
            'updated_at' => $date
        ));

        DB::table('staffvoucherparameters')->where('id', 4)->update(array(
            'rate' => $rate17,
            'updated_at' => $date
        ));
        $this->addLog('Staff voucher parameters was updated');
        return redirect('/CR/staff/voucher/create');
    }


    //edit voucher
    public function updateVoucher(Request $request)
    {
        $this->validate($request, [
            'economicCode'             => 'required|string',
            'newAmount'             => 'required|numeric',
            'beneficiary'             => 'required|string',
            'description'             => 'required|string',
            'transactionID'            => 'required|integer',
            'vatRate'                 => 'integer',
            'vatPayee'              => 'required|string',
            'vatPayeeAddress'       => 'required|string',
            'whtRate'                 => 'integer',
            'whtPayee'              => 'required|string',
            'whtPayeeAddress'       => 'required|string',
            'todayDate'               => 'required|date',
        ]);

        //
        $todayDate                       = (trim($request['todayDate']));
        $newAmount                       = (($request['newAmount']));
        $vatRate                      = (trim($request['vatRate']));
        $whtRate                      = (trim($request['whtRate']));
        $beneficiary                  = (trim($request['beneficiary']));
        $description                   = (trim($request['description']));
        $transactionID                   = (trim($request['transactionID']));
        $companyID                       = (trim($request['companyID']));
        $designation                   = (trim($request['designation']));
        $whtPayee                         = ucfirst(trim($request['whtPayee']));
        $whtPayeeAddress               = ucfirst(trim($request['whtPayeeAddress']));
        $vatPayee                         = ucfirst(trim($request['vatPayee']));
        $vatPayeeAddress               = ucfirst(trim($request['vatPayeeAddress']));
        $economicCodeRaw               = (trim($request['economicCode']));
        //
        $strArray               = explode('/', $economicCodeRaw);
        $descriptionCode       = $strArray[0];
        $economicCode            = $strArray[1];
        //
        $group                     = (DB::table('tblsubdescription')
            ->join('tblbudget', 'tblbudget.subdescriptionID', '=', 'tblsubdescription.subdescriptionID')
            ->join('tbldescription', 'tbldescription.descriptionID', '=', 'tblsubdescription.descriptionID')
            ->select('tblbudget.budgetID', 'tblsubdescription.groupID')
            ->where('tblsubdescription.subcode', '=', $economicCode)
            ->where('tbldescription.code', '=', $descriptionCode)
            ->first());
        if ($group == '') {
            return back()->with('err', 'No Budget and allocation have been added for the selected Economic code:' . $economicCode);
        }
        $budgetID              = $group->budgetID;
        $groupID               = $group->groupID;

        //double check parameters
        if ($transactionID == '' or $transactionID < 1) {
            return back()->with('err', 'This record cannot be updated due to internal error or try again later');
        } else if ($newAmount == '') {
            return back()->with('err', 'Amount cannot be empty !');
        }

        if (($whtRate) == "" || ($whtRate) == 0) {
            $whtOrTax            = 0;
            $vatRate             = 0;
        } else {
            $whtOrTax             = ($whtRate);
            $vatRate             = $vatRate;
        }

        $grossAmount           = ($newAmount);
        $whtOrTax                = $whtOrTax;
        $calVat               = substr((($vatRate / 100) * $grossAmount), 0, strpos((($vatRate / 100) * $grossAmount), '.') + 12);
        $calWht               = substr((($whtOrTax / 100) * $grossAmount), 0, strpos((($whtOrTax / 100) * $grossAmount), '.') + 12);
        $totalAmount           = substr(($grossAmount + $calVat), 0, strpos(($grossAmount + $calVat), '.') + 12);
        $balance               = substr(($grossAmount - $calWht), 0, strpos(($grossAmount - $calWht), '.') + 12);

        //current voucher
        $getOldInfo = DB::table('tbltransaction')
            ->where('transactionID', $transactionID)
            ->select('capital_track', 'amount', 'wht')
            ->first();
        $getCapitalTrack = $getOldInfo->capital_track;
        //master voucher
        $getMasterVoucher = DB::table('tbltransaction')
            ->where('transactionID', $getCapitalTrack)
            ->where('capital_rank', 'A')
            ->select('capital_totalamount')
            ->first();
        $newBalance = 0;
        if ($getMasterVoucher) {
            $oldBalance  = $getMasterVoucher->capital_totalamount;
            //get old gross amount : i.e amount + wht
            $oldGrossAmount = $getOldInfo->amount + $getOldInfo->wht;
            //calculate new balance for part-payment
            $newBalance  = $oldBalance + ($oldGrossAmount - $grossAmount);
            if ($newBalance < 0) {
                return redirect('/capital/voucher/view')->with('err', 'Gross amount cannot be greater than total amount');
            }
        }
        //

        //if beneficiary is from company list
        if (($companyID != '') or ($companyID != 0)) {
            DB::table('tblcompany')->where('companyID', $companyID)->update(array(
                'companyname'              => $beneficiary
            ));
            DB::table('tbltransaction')->where('transactionID', $transactionID)->update(array(
                'description'              => $description,
                'amount'                 => $balance,
                'totalamount'             => $totalAmount,
                'wht'                      => ($calWht),
                'vat'                      => ($calVat),
                'whtrate'                  => $whtOrTax,
                'vatrate'                  => $vatRate,
                'designation'              => $designation,
                'whtpayee'              => $whtPayee,
                'whtpayeeaddress'          => $whtPayeeAddress,
                'vatpayee'              => $vatPayee,
                'vatpayeeaddress'          => $vatPayeeAddress,
                'groupID'                  => $groupID,
                'date'                  => $todayDate,
                'budgetID'              => $budgetID
            ));
        } else { // beneficiary is from staff, that is, payee
            DB::table('tbltransaction')->where('transactionID', $transactionID)->update(array(
                'description'              => $description,
                'payee'                  => $beneficiary,
                'amount'                 => $balance,
                'totalamount'             => $totalAmount,
                'wht'                      => ($calWht),
                'vat'                      => ($calVat),
                'whtrate'                  => $whtOrTax,
                'vatrate'                  => $vatRate,
                'designation'              => $designation,
                'vatpayee'              => $vatPayee,
                'vatpayeeaddress'          => $vatPayeeAddress,
                'whtpayee'              => $whtPayee,
                'whtpayeeaddress'          => $whtPayeeAddress,
                'groupID'                  => $groupID,
                'date'                  => $todayDate,
                'budgetID'              => $budgetID
            ));
            DB::table('transaction_voucher_staff')->where('transactionID', $transactionID)->update(array(
                'economiccode'          => $economicCode
            ));
        }
        if ($getMasterVoucher) {
            DB::table('tbltransaction')
                ->where('transactionID', '=', $getCapitalTrack)
                ->where('capital_rank', 'A')
                ->update(array(
                    'capital_totalamount'  => $newBalance,
                ));
        }

        return redirect('/voucher/view')->with('msg', 'Voucher was updated successfully');
    }



    //COPY VOUCHER
    public function replicateVoucher(Request $request)
    {
        $this->validate($request, [
            'voucherID'             => 'required|numeric',
            'amount'                 => 'required|numeric',
            'economicCode'             => 'required|string',
            'todayDate'               => 'required|date',
        ]);
        //
        $todayDate            = (($request['todayDate']));
        $transactionIDUpdate  = (trim($request['voucherID']));
        $description           = (trim($request['description']));
        $amount                 = (($request['amount']));
        $economicCodeRaw       = (trim($request['economicCode']));
        //
        $strArray               = explode('/', $economicCodeRaw);
        $descriptionCode       = $strArray[0];
        $economicCode            = $strArray[1];
        //
        $group                     = (DB::table('tblsubdescription')
            ->join('tblbudget', 'tblbudget.subdescriptionID', '=', 'tblsubdescription.subdescriptionID')
            ->join('tbldescription', 'tbldescription.descriptionID', '=', 'tblsubdescription.descriptionID')
            ->select('tblbudget.budgetID', 'tblsubdescription.groupID')
            ->where('tblsubdescription.subcode', '=', $economicCode)
            ->where('tbldescription.code', '=', $descriptionCode)
            ->first());
        if ($group == '') {
            return back()->with('err', 'No Budget or allocation have been added for the selected Economic code:' . $economicCode);
        }
        $budgetID              = $group->budgetID;
        $groupID               = $group->groupID;
        //$subName 			  = $group->subname;

        $getPrevData = DB::table('tbltransaction')->where('transactionID', $transactionIDUpdate)->first();
        //
        if (($getPrevData->whtrate) == "" || ($getPrevData->whtrate == 0)) {
            $whtOrTax            = 0;
            $vatRate             = 0;
        } else {
            $whtOrTax             = (trim($getPrevData->whtrate));
            $vatRate             = (trim($getPrevData->vatrate));
        }
        $grossAmount           = $amount;
        $whtOrTax                = $whtOrTax;
        $calVat               = substr((($vatRate / 100) * $grossAmount), 0, strpos((($vatRate / 100) * $grossAmount), '.') + 12);
        $calWht               = substr((($whtOrTax / 100) * $grossAmount), 0, strpos((($whtOrTax / 100) * $grossAmount), '.') + 12);
        $totalAmount           = substr(($grossAmount + $calVat), 0, strpos(($grossAmount + $calVat), '.') + 12);
        $balance               = substr(($grossAmount - $calWht), 0, strpos(($grossAmount - $calWht), '.') + 12);

        //insert
        $getDate = Carbon::now();
        $transactionID = DB::table('tbltransaction')->insertGetId(array(
            'description'              => $description,
            'month'                  => date('F'),
            'companyID'              => $getPrevData->companyID,
            'groupID'                  => $groupID,
            'filerefer'              => $getPrevData->filerefer,
            'amount'                 => $balance,
            'totalamount'             => $totalAmount,
            'year'                     => date('Y'),
            'date'                     => $todayDate,
            'budgetID'              => $budgetID,
            'wht'                      => ($calWht),
            'vat'                      => ($calVat),
            'whtrate'                  => $whtOrTax,
            'vatrate'                  => $vatRate,
            'preparedby'              => $getPrevData->preparedby,
            'vatpayee'              => $getPrevData->vatpayee,
            'whtpayee'              => $getPrevData->whtpayee,
            'vatpayeeaddress'          => $getPrevData->vatpayeeaddress,
            'whtpayeeaddress'          => $getPrevData->whtpayeeaddress,
            'typevoucher'             => $getPrevData->typevoucher,
            'payee'                 => $getPrevData->payee,
            'address'                 => $getPrevData->address,
            'created_at'              => date('Y-m-d'),
            'departmentname'           => 'Recurrent'
        ));
        if (DB::table('transaction_voucher_staff')->where('transactionID', $transactionIDUpdate)->first()) {
            $allAttachedNames = DB::table('transaction_voucher_staff')->where('transactionID', $transactionIDUpdate)->get();
            foreach ($allAttachedNames as $staffID) {
                # code...
                DB::table('transaction_voucher_staff')->insert(array(
                    'fileNo'         => $staffID->fileNo,
                    'fileNonew'     => $staffID->fileNonew,
                    'fullname'         => $staffID->fullname,
                    'designation'     => $staffID->designation,
                    'transactionID' => $transactionID,
                    'division'         => $staffID->division,
                    'divisionID'     => $staffID->divisionID,
                    'economiccode'     => $economicCode,
                    'grade'         => $staffID->grade,
                    'step'             => $staffID->step,
                    'amount'         => $staffID->amount,
                    'bankname'         => $staffID->bankname,
                    'accountno'     => $staffID->accountno,
                    'sortcode'         => $staffID->sortcode,
                    'addeddate'     => date('Y-m-d')
                ));
            }
        }
        $this->addLog('A copy of a Voucher was created successfully');
        //
        if ($transactionID <> '') {
            return redirect('/print/voucher/' . $transactionID)->with('msg', 'A copy of the seleted Voucher was created successfully');
        } else {
            return back()->with('err', 'A copy of this voucher cannot be created due to internal error (or Internet access). Try again later or contact your admin');
        }
    }


    public function SoftDelete(Request $request)
    {
        Session::forget('transactionID');
        Session::forget('currentDivisionID');

        $this->validate($request, [
            'voucherID'             => 'required|numeric',
        ]);
        //
        $voucherID                  = (trim($request['voucherID']));
        //
        DB::table('tbltransaction')->where('transactionID', $voucherID)->update(array(
            'voucher_status'          => (0),
        ));
        //
        $this->addLog('Voucher Deleted (SoftDelete) successfully from Recurrent');
        return redirect('/voucher/view')->with('msg', 'Voucher was DELETE successfully');
    }


    public function addNewStaffToListVoucher(Request $request)
    {
        $this->validate($request, [
            'fileNo'             => 'numeric',
            'fullName'             => 'required|regex:/^[a-zA-Z0-9,.!?\)\( ]*$/',
            //'bankName' 		=> 'alpha_num',
            //'accountNo'      	=> 'numeric',
            //'sortCode'       	=> 'numeric',
            'designation'       => 'string',
            'grade'               => 'numeric',
            'step'               => 'numeric',
            'division'           => 'alpha_num',
            'amount'               => 'numeric',
        ]);
        //
        $fileNo                    = (($request['fileNo']));
        $fullName                  = (trim($request['fullName']));
        $bankName                   = (trim($request['bankName']));
        $accountNo                     = (($request['accountNo']));
        $sortCode                   = (trim($request['sortCode']));
        $designation                = (($request['designation']));
        $grade                  = (trim($request['grade']));
        $step                      = (trim($request['step']));
        $division                   = (trim($request['division']));
        $amount                     = (($request['amount']));
        $transactionID               = (($request['voucherID']));
        $economicCode               = (($request['economicCode']));

        //get last inserted
        $fileNoTrack = DB::table('transaction_voucher_staff')
            ->where('transactionID', '=', $transactionID)
            ->where('economiccode', '=', $economicCode)
            ->where('fileNo', '=', null)
            ->where('divisionID', '=', null)
            ->orderby('fileNonew', 'Desc')
            ->first();
        if ($fileNoTrack == '') {
            $nextFileNo = (1);
        } else {
            $nextFileNo = (($fileNoTrack->fileNonew) + 1);
        }

        DB::table('transaction_voucher_staff')->insert(array(
            'fileNonew'          => ($nextFileNo),
            'fullName'          => ($fullName),
            'designation'          => ($designation),
            'grade'              => ($grade),
            'step'              => ($step),
            'division'          => ($division),
            'amount'              => ($amount),
            'transactionID'      => ($transactionID),
            'economiccode'      => ($economicCode),
            'bankname'          => $bankName,
            'accountno'          => $accountNo,
            'sortcode'          => $sortCode,
            'addeddate'          => (date('Y-m-d'))
        ));

        $this->addLog('New Beneficiary Details are successfully added to list from Recurrent');
        //return redirect('/print/voucher/'.$transactionID )->with('msg', 'New Beneficiary Details are successfully added to list');
        return back()->with('msg', 'New Beneficiary Details are successfully added to list');
    }



    public function updateVoucherAmountInStaffList(Request $request)
    {
        $this->validate($request, [
            'staffSelectedChecked' => 'required|array',
        ]);

        $staffAmount              = (($request['staffAmount']));
        $staffID                    = (($request['staffSelectedChecked']));
        $voucherID                    = (($request['voucherID']));

        $i = 0;
        //get amount as an array
        foreach ($staffAmount as $amount) {
            $arrayAmount[] = $amount;
        }

        foreach ($staffID as $val) {
            DB::table('transaction_voucher_staff')
                ->where('id', '=', $val)
                ->update(array(
                    'amount' => ($arrayAmount[$i])
                ));
            $i++;
        }

        /*foreach($staffID as $val)
		{
		   if(DB::table('transaction_voucher_staff')->where('id', $val)->where('transactionID', $voucherID)->first()){
		   		DB::table('transaction_voucher_staff')
		    	->where('id', '=', $val)
		    	->update(array(
					'amount' => ($arrayAmount[$i])
				));
		   }
		   $i ++;
		}*/
        $this->addLog('Beneficiaries Amount and/or Bank Details were updated successfully');
        return redirect('/print/voucher/' . $voucherID)->with('msg', 'Beneficiaries Details were updated successfully');
    }



    public function storePartPayment(Request $request)
    {

        $this->validate($request, [
            'amount'                 => 'required|numeric',
            'economicCode'             => 'required|string',
            'vatPayee'              => 'required|string',
            'whtPayee'              => 'required|string',
            'vatPayeeAddress'       => 'required|string',
            'whtPayeeAddress'       => 'required|string',
            'todayDate'               => 'required|date',
        ]);
        //Assign
        $todayDate              = trim($request['todayDate']);
        $capitalTotalAmount   = ($request['totalAmount']);
        $vatselect               = trim($request['vatselect']);
        $grossAmount             = (trim($request['amount']));
        $description           = ucfirst(trim($request['narration']));
        $vatPayee             = ucfirst(trim($request['vatPayee']));
        $whtPayee                 = ucfirst(trim($request['whtPayee']));
        $vatPayeeAddress       = ucfirst(trim($request['vatPayeeAddress']));
        $whtPayeeAddress       = ucfirst(trim($request['whtPayeeAddress']));
        $economicCodeRaw           = (trim($request['economicCode']));
        $capitalRank           = (trim($request['capitalRank']));
        $capital_track           = (trim($request['capital_track']));
        //
        $strArray               = explode('/', $economicCodeRaw);
        $descriptionCode       = $strArray[0];
        $economicCode            = $strArray[1];
        //
        $group                     = (DB::table('tblsubdescription')
            ->join('tblbudget', 'tblbudget.subdescriptionID', '=', 'tblsubdescription.subdescriptionID')
            ->join('tbldescription', 'tbldescription.descriptionID', '=', 'tblsubdescription.descriptionID')
            ->select('tblbudget.budgetID', 'tblsubdescription.groupID')
            ->where('tblsubdescription.subcode', '=', $economicCode)
            ->where('tbldescription.code', '=', $descriptionCode)
            ->first());
        if ($grossAmount > $capitalTotalAmount) {
            return redirect('voucher/view')->with('err', 'Gross Amount cannot be greater than the Total balance left !  Please review and try again.');
        }
        //
        if ($group == '') {
            return redirect('voucher/view')->with('err', 'No Budget and allocation have been added for the selected Economic code:' . $economicCode);
        }

        $budgetID              = $group->budgetID;
        $groupID               = $group->groupID;
        $year                   = (date('Y'));
        $month                = (date('F'));

        if (($request['whtOrTax']) == "" || ($request['whtOrTax']) == 0) {
            $whtOrTax            = 0;
            $vatRate             = 0;
        } else {
            $whtOrTax             = (trim($request['whtOrTax']));
            $vatRate             = $vatselect;
        }

        $grossAmount           = $grossAmount;
        $whtOrTax                = $whtOrTax;
        $calVat               = substr((($vatRate / 100) * $grossAmount), 0, strpos((($vatRate / 100) * $grossAmount), '.') + 12);
        $calWht               = substr((($whtOrTax / 100) * $grossAmount), 0, strpos((($whtOrTax / 100) * $grossAmount), '.') + 12);
        $totalAmount           = substr(($grossAmount + $calVat), 0, strpos(($grossAmount + $calVat), '.') + 12);
        $balance               = substr(($grossAmount - $calWht), 0, strpos(($grossAmount - $calWht), '.') + 12);

        //
        $getPrevDetails  = DB::table('tbltransaction')
            ->where('capital_track', $capital_track)
            ->where('departmentname', 'Recurrent')
            ->orderBy('capital_rank', 'Asc')
            ->first();

        if (($capitalTotalAmount == '') || ($capitalTotalAmount == 0) || ($capitalTotalAmount < 1)) {
            $capital_totalamount = 0;
            $capital_rank = '';
        } else {
            $capital_totalamount = ($capitalTotalAmount - $grossAmount);
            $getMAXRank  = DB::table('tbltransaction')
                ->where('capital_track', $capital_track)
                ->where('departmentname', 'Recurrent')
                ->orderBy('capital_rank', 'Desc')
                ->first();
            $capital_rank   = chr(ord($getMAXRank->capital_rank) + 1);
        }

        //
        $getDate = Carbon::now();
        $transactionID = DB::table('tbltransaction')->insertGetId(array(
            'description'              => $description,
            'month'                  => $month,
            'companyID'              => $getPrevDetails->companyID,
            'groupID'                  => $groupID,
            'filerefer'              => $getPrevDetails->filerefer,
            'amount'                 => $balance,
            'totalamount'             => $totalAmount,
            'year'                     => $year,
            'date'                     => $todayDate,
            'budgetID'              => $budgetID,
            'wht'                      => ($calWht),
            'vat'                      => ($calVat),
            'whtrate'                  => $whtOrTax,
            'vatrate'                  => $vatRate,
            'preparedby'              => $getPrevDetails->preparedby,
            'vatpayee'              => $vatPayee,
            'whtpayee'              => $whtPayee,
            'vatpayeeaddress'          => $vatPayeeAddress,
            'whtpayeeaddress'          => $whtPayeeAddress,
            'typevoucher'             => $getPrevDetails->typevoucher,
            'capital_totalamount'     => $capital_totalamount,
            'capital_rank'             => $capital_rank,
            'capital_track'          => $capital_track,
            'created_at'              => date('Y-m-d'),
            'departmentname'          => 'Recurrent'
        ));
        DB::table('tbltransaction')
            ->where('capital_track', '=', $capital_track)
            ->where('departmentname', 'Recurrent')
            ->update(array(
                'capital_totalamount'          => $capital_totalamount
            ));
        $this->addLog('Another Part Payment ' . $capital_rank . ' Voucher was created successfully');

        return redirect('/print/voucher/' . $transactionID)->with('msg', 'Another Part Payment ' . $capital_rank . ' Voucher was created successfully');
    }


    //Revert part payment voucher
    public function revertPartPaymentPaid(Request $request)
    {

        $this->validate($request, [
            'voucherNumber'         => 'required|numeric'
        ]);
        $this_transactionID =  trim($request['voucherNumber']);
        $getDetailsTransaction  = DB::table('tbltransaction')->where('transactionID', $this_transactionID)
            ->select('capital_track', 'totalamount', 'wht')
            ->first();
        //
        $capitalTrack         = $getDetailsTransaction->capital_track;
        $totalamount         = $getDetailsTransaction->totalamount;
        $vat                 = $getDetailsTransaction->wht;
        //
        $getMasterTotalAmount  = DB::table('tbltransaction')->where('transactionID', $capitalTrack)->select('capital_totalamount')->first();
        $newBalanceCapitalTotalAmount = (($totalamount - $vat) + ($getMasterTotalAmount->capital_totalamount));
        //update Master Voucher -Main capital
        $masterVoucher = DB::table('tbltransaction')
            ->where('transactionID', '=', $capitalTrack)
            ->update(array(
                'capital_totalamount'       => $newBalanceCapitalTotalAmount
            ));
        $updateAllInstance  = DB::table('tbltransaction')->where('capital_track', $capitalTrack)->get();
        foreach ($updateAllInstance as $value) {
            # code...
            $masterVoucher = DB::table('tbltransaction')
                ->where('capital_track', '=', $value->capital_track)
                ->update(array(
                    'capital_totalamount' => $newBalanceCapitalTotalAmount
                ));
        }
        //Delete reverted voucher - delete from all instances
        $data_tran_approve = DB::table('tbltransaction_approval')->where('transaction_id', $this_transactionID)->delete();
        $data_staff        = DB::table('transaction_voucher_staff')->where('transactionID', $this_transactionID)->delete();
        $data_tran         = DB::table('tbltransaction')->where('transactionID', $this_transactionID)->delete();

        return redirect('recurrent/all-partpayment/voucher/' . $capitalTrack)->with('msg', 'Voucher reverted successfully and voucher was also deleted from the system');
    }



    public function viewAllPartPayment($transactionID = null)
    {
        Session::forget('getYear');
        Session::forget('getFrom');
        Session::forget('getTo');
        //
        Session::forget('transactionID');
        Session::forget('currentDivisionID');

        $data['getVoucher']  = DB::table('tbltransaction')
            ->leftjoin('tblcompany', 'tblcompany.companyID', '=', 'tbltransaction.companyID')
            ->Join('tblgroup', 'tblgroup.groupID', '=', 'tbltransaction.groupID')
            ->Join('tblbudget', 'tblbudget.budgetID', '=', 'tbltransaction.budgetID')
            ->Join('tblsubdescription', 'tblsubdescription.subdescriptionID', '=', 'tblbudget.subdescriptionID')
            ->where('tbltransaction.voucher_status', '=', (1))
            ->where('tbltransaction.capital_track', '=', $transactionID)
            ->select('*', 'tbltransaction.date')
            ->orderBy('tbltransaction.transactionID', 'DESC')
            ->get();
        $data['subdescriptions']  = $this->recurrentSubdescriptionCode();
        //
        $data['allPartPayment'] = '';

        return view('capitalRecurrent.viewPartPayment', $data);
    }



    public function viewAllBeneficiaryPartPayment()
    {
        $data['getVoucher']  = DB::table('tbltransaction')
            ->leftjoin('tblcompany', 'tblcompany.companyID', '=', 'tbltransaction.companyID')
            ->Join('tblgroup', 'tblgroup.groupID', '=', 'tbltransaction.groupID')
            ->Join('tblbudget', 'tblbudget.budgetID', '=', 'tbltransaction.budgetID')
            ->Join('tblsubdescription', 'tblsubdescription.subdescriptionID', '=', 'tblbudget.subdescriptionID')
            ->where('tbltransaction.voucher_status', '=', (1))
            ->where('tbltransaction.capital_totalamount', '>', 0)
            ->where('tbltransaction.departmentname', '=', 'Recurrent')
            ->select('*', 'tbltransaction.date')
            ->orderBy('tbltransaction.transactionID', 'DESC')
            ->get();
        $data['subdescriptions']  = $this->recurrentSubdescriptionCode();
        //
        $data['allPartPayment'] = 'All Part Payment';

        return view('capitalRecurrent.viewPartPayment', $data);
    }


    //===NOTE:=====/THIS CODE SNIPPERS ARE FOR SUB-ACCOUNT AND RECURRENT========For deleting staff from staf List on voucher////

    public function deleteStaffFromListJSON(Request $request)
    {
        $this->validate($request, [
            'staffIdList' => 'numeric',
        ]);
        $staffIdList              = (($request['staffIdList']));
        $data = DB::table('transaction_voucher_staff')->where('id', $staffIdList)->delete();

        return response()->json($data);
    }


    public function updateBankDetailsFromVoucherStaffList(Request $request)
    {
        //Update Staff Bank Details: CPO
        $this->validate($request, [
            'bankName'           => 'regex:/[a-zA-Z.]/',
            'accountNumber'   => 'numeric',
            'sortCode'        => 'numeric',
            'staffAmount'     => 'numeric',
            'id'               => 'numeric',
        ]);
        //Assign
        $bankName          = trim($request['bankName']);
        $accountNumber    = trim($request['accountNumber']);
        $sortCode           = trim($request['sortCode']);
        $staffAmount         = trim($request['staffAmount']);
        $staffRecordID    = trim($request['id']);
        $getRecord          = (DB::table('transaction_voucher_staff')->where('id', '=', $staffRecordID)->first()); //for redirect back
        $voucherID        = $getRecord->transactionID;
        //update $this-record
        $data = DB::table('transaction_voucher_staff')
            ->where('id', '=', $staffRecordID)
            ->update(array(
                'amount'       => $staffAmount,
                'bankname'       => $bankName,
                'accountno'   => $accountNumber,
                'sortcode'    => $sortCode
            ));
        return response()->json($data);
    }
    //end=========/THIS CODE SNIPPERS ARE FOR SUB-ACCOUNT AND RECURRENT========////




    //SEARCH BY VOUCHER NUMBER
    public function searchByVoucherNumberRecurrent(Request $request)
    {
        $this->validate($request, [
            'voucherNumber'          => 'required|numeric',
        ]);
        $voucherNumber =  trim($request['voucherNumber']);
        if ($voucherNumber == '') {
            return redirect('/voucher/view');
        }
        if (!DB::table('tbltransaction')->where('tbltransaction.transactionID', '=', $voucherNumber)->where('tbltransaction.voucher_status', '=', (1))->first()) {
            return redirect('/voucher/view')->with('err', 'Sorry, we cannot find the voucher you are looking for! Perhaps, the voucher number you entered is wrong. Check and try again');
        } else {
            $data['getVoucher']  = DB::table('tbltransaction')
                ->leftjoin('tblcompany', 'tblcompany.companyID', '=', 'tbltransaction.companyID')
                ->Join('tblgroup', 'tblgroup.groupID', '=', 'tbltransaction.groupID')
                ->Join('tblbudget', 'tblbudget.budgetID', '=', 'tbltransaction.budgetID')
                ->Join('tblsubdescription', 'tblsubdescription.subdescriptionID', '=', 'tblbudget.subdescriptionID')
                ->where('tbltransaction.departmentname', '=', ('Recurrent'))
                ->where('tbltransaction.voucher_status', '=', (1))
                ->where('tbltransaction.transactionID', $voucherNumber)
                ->select('*', 'tbltransaction.date')
                ->orderBy('tbltransaction.transactionID', 'DESC')
                ->paginate(30);
            $data['subdescriptions']  = $this->recurrentSubdescriptionCode();
            return view('capitalRecurrent.allVouchers', $data);
        }
    }
    //

    public function driversVoucher()
    {
        $data['list']  = DB::table('tblpaymentTransaction')
            ->leftJoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
            ->leftJoin('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
            //->leftJoin('users','users.ID','=','tblpaymentTransaction.preparedBy')
            ->leftJoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
            ->leftJoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
            ->leftJoin('tblcontractType', 'tblcontractType.ID', '=', 'tblpaymentTransaction.contractTypeID')
            ->where('tblpaymentTransaction.ID', '=', 54)
            ->select('*', 'tblpaymentTransaction.ID as transID', 'tblpaymentTransaction.status as payStatus', 'tblcontractType.contractType as ecoHead')
            ->first();

        $b   = $this->VoucherFinancialInfo(54);
        $data['bbf'] = $b->BBF;
        $data['contractAmount'] = $b->contractValue;

        $data['status']     = DB::table('tblstatus')->where('code', '=', $data['list']->payStatus)->first();

        $data['preparedBy'] = DB::table('users')->where('username', '=', $data['list']->preparedBy)->orWhere('id', '=', $data['list']->preparedBy)->first();

        $data['libilityBy'] = DB::table('users')->where('username', '=', $data['list']->liabilityBy)->orWhere('id', '=', $data['list']->liabilityBy)->first();

        $data['checkBy']    = DB::table('users')->where('username', '=', $data['list']->checkBy)->orWhere('id', '=', $data['list']->checkBy)->first();

        $data['approvedBy'] = DB::table('users')->where('username', '=', $data['list']->approvedBy)->orWhere('id', '=', $data['list']->approvedBy)->first();

        $data['auditedBy'] = DB::table('users')->where('username', '=', $data['list']->auditedBy)->orWhere('id', '=', $data['list']->auditedBy)->first();
        //	dd($data['approvedBy']);

        $data['vatpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->VATPayeeID)->first();
        $data['whtpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->WHTPayeeID)->first();
        //dd($data['list']->totalPayment );


        $data['count'] = DB::table('tblvoucherBeneficiary')
            ->where('voucherID', '=', $data['list']->transID)
            ->count();

        // dd($data['count']);
        $data['staff'] = DB::table('tblvoucherBeneficiary')
            ->leftjoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblvoucherBeneficiary.bankID')
            ->where('voucherID', '=', $data['list']->transID)
            ->get();
        // dd($data['staff']);
        $data['details'] = DB::table('tbldrivertour')->where('voucherId', '=', 396)->get();

        $data['beneficiary'] = DB::table('tblvoucherBeneficiary')->where('voucherID', '=', 396)->first();

        return view('funds.voucherDisplay.driversVouchers', $data);
    }


    public function updateVref(Request $request)
    {
        $date = date('Y');
        $vref = $request['vref'];
        $datePre = $request['datePrepaid'];
        $previousVref =  DB::table('tblpaymentTransaction')->where('ID', '=', $request['transactionID'])->first();
        $check =  DB::table('tblpaymentTransaction')->where('period', $request['datePrepaid'])->where('vref_no', '=', $request['vref'])->count();
        //$checkx = DB::select('select * from tblpaymentTransaction where year(datePrepared) = "$datePre" and vref_no = "$vref"');
        //$check = count((array)$checkx);
        //return response()->json($check);

        DB::table('tblcontractDetails')->where('ID', '=', $request['transactionID'])->update([
            'transaction_vref'  => $request['vref'],
        ]);
        DB::table('tblpaymentTransaction')->where('contractID', '=', $request['transactionID'])->update([
            'vref_no'  => $request['vref'],
        ]);

        if ($check > 0) {
            return response()->json(['check' => $check, 'previous' => $previousVref->vref_no,]);
        } else {
            $update =  DB::table('tblpaymentTransaction')->where('ID', '=', $request['transactionID'])->update([
                'vref_no'  => $request['vref'],
            ]);

            if ($update) {
                $data = DB::table('tblpaymentTransaction')->where('ID', '=', $request['transactionID'])->first();
                return response()->json($data);
            }
        }
    }

    public function updatePayeeAddress(Request $request)
    {
        $payAddr = $request['payeeAddress'];
        $voucherDetail =  DB::table('tblpaymentTransaction')->where('ID', '=', $request['transactionID'])->first();
        DB::table('tblpaymentTransaction')->where('ID', '=', $request['transactionID'])->update([
            'payee_address' => $request['payeeAddress']
        ]);
        DB::table('tblcontractDetails')->where('ID', '=', $voucherDetail->contractID)->update([
            'payee_address' => $request['payeeAddress']
        ]);
        return response()->json(['msg' => $voucherDetail->payee_address]);
    }
}//End class
