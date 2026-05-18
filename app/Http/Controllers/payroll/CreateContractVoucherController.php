<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use Carbon\Carbon;
use Entrust;
use Session;
use Excel;
use Illuminate\Support\Facades\Input;
use DB;
use QrCode;
use Illuminate\Support\Facades\Crypt;


class CreateContractVoucherController extends function24Controller
{


    public function setSes(Request $request)
    {
        session::put('alloc', $request['id']);
        return response()->json('Successful');
    }
    public function PrecreateContractVoucher(Request $request)
    {

        $data['warning']        = '';
        $data['success']        = '';
        $data['error']      = '';
        $data['contracttypes']       = $this->getContractType();
        $data['contracttype']       = $request['contracttype'];
        $data['contractor']       = $request['contractor'];
        $data['fileno']       = $request['fileno'];
        DB::table('tblcontractDetails')->where('ID', $request['hiddencontractid'])
            ->update(['OC_staffId'    => $request['hiddenuserid'],]);

        $data['tablecontent'] = $this->getTable3($request['contracttype'], $request['contractor'], $request['fileno']);
        foreach ($data['tablecontent'] as $key => $value) {
            $line = (array) $value;
            $line['contractBalance'] = $this->contractBalance($value->ID);
            $line['numofv'] = count(DB::table('tblpaymentTransaction')->where('contractID', $value->ID)->get());
            $line = (object) $line;
            $data['tablecontent'][$key] = $line;
        }
        $data['companyDetails'] = $this->getBeneficiary();
        $data['fileRefer'] = [];
        $data['fileNos']            = $this->getFileNos();
        $data['UnitStaff'] = $this->UnitStaff('OC');

        return view('CreateContract.precreatecontract', $data);
    }


    public function createContractVoucher(Request $request)
    {

        $data['warning']        = '';
        $data['success']        = '';
        $data['error']      = '';
        $data['contracttypes']       = $this->getContractType();
        $data['contracttype']       = $request['contracttype'];
        $data['contractor']       = $request['contractor'];
        $data['fileno']       = $request['fileno'];
        //die($data['fileno']);

        $data['tablecontent'] = $this->AllocationVoucher(auth::user()->id);
        foreach ($data['tablecontent'] as $key => $value) {
            $line = (array) $value;
            $line['contractBalance'] = $this->contractBalance($value->ID);
            $line['numofv'] = count(DB::table('tblpaymentTransaction')->where('contractID', $value->ID)->get());
            $line = (object) $line;
            $data['tablecontent'][$key] = $line;
        }
        $data['companyDetails'] = $this->getBeneficiary();
        $data['fileRefer'] = [];
        $data['fileNos']            = $this->getFileNos();
        $data['UnitStaff'] = $this->UnitStaff('OC');

        return view('CreateContract.createcontract', $data);
    }


    //create Edit
    public function edit($id = "")
    {
        $data['paymentTransactionID'] = $id;
        $data['warning'] = '';
        $data['success'] = '';
        $data['error'] = '';
        $data['fileReferID'] = '';
        $fields = DB::table('tblpaymentTransaction')
            ->where('tblpaymentTransaction.ID', '=', $id)
            ->where('tblpaymentTransaction.vstage', '<=', 1)
            ->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
            ->select('tblpaymentTransaction.*', 'tblcontractDetails.payee_address', 'tblcontractDetails.contract_Type', 'tblcontractDetails.fileNo')
            ->first();

        if ($fields) {
            $conid = $fields->contractID;
            $data['contractID'] = $conid;
            $status = $fields->vstage;
            $vvalue = $fields->totalPayment;
            $data['fileReferID'] = $fields->file_referID;
        } else {
            return redirect()->back()->with('err', 'Record not found !!!');
        }

        $data['contractDetails'] = $fields->contractID;
        $data['selectedid'] = $fields->ID;
        $com = DB::table('tblcomments')
            ->where('affectedID', $fields->contractID)
            ->orderby('id', 'asc')
            ->select('tblcomments.comment', 'tblcomments.username', 'tblcomments.added')
            ->get();
        $data['reasons'] = "";

        if ($com) {
            foreach ($com as $k => $list) {
                $newline = (array) $list;
                $name = DB::table('users')->where('username', $list->username)->first()->name;
                $newline['name'] = $name;
                $date = strtotime($list->added);
                $newline['comment'] = str_replace("\r\n", "<br>", $list->comment);
                $newline['date_added'] = date("F j, Y", $date);
                $newline['time'] = date("g:i a", $date);
                $newline = (object) $newline;
                $com[$k] = $newline;
            }
            $data['reasons'] = json_encode($com);
        }
        $data['selectedid'] = $fields->ID;
        $com = DB::table('tblcomments')
            ->where('affectedID', $fields->contractID)
            ->orderby('id', 'asc')->select('tblcomments.comment', 'tblcomments.username', 'tblcomments.added')->get();
        $data['reasons'] = "";
        //dd($data);
        $request['fileno']                  = $fields->fileNo;
        $request['contracttype']            = $fields->contract_Type;
        $request['companyid']               = $fields->companyID;
        $request['contractoredit']          = $fields->companyID;
        $request['allocationtype']      = $fields->allocationType;
        $request['economicCode']        = $fields->economicCodeID;
        $request['amount']              = $fields->totalPayment;
        $request['vatselect']           = $fields->VAT;
        $request['whtOrTax']            = $fields->WHT;
        $request['vat']                 = $fields->VATValue;
        $request['wht']                 = $fields->WHTValue;
        $data['stampduty']           = $fields->stampdutypercentage;
        $data['stampdutyv']          = $fields->stampduty;
        $request['amtpayable']          = $fields->amtPayable;
        $request['whtPayeeID']          = $fields->WHTPayeeID;
        $request['vatPayeeID']          = $fields->VATPayeeID;
        $request['narration']           = $fields->paymentDescription;
        $request['pvno']                = $fields->PVNO;
        $request['todayDate']           = $fields->datePrepared;
        $data['payeeAddress']          = $fields->payee_address;

        $data['allocationtype']             = $request['allocationtype'];
        $data['economicCode']               = $request['economicCode'];
        $data['economicName']               = DB::table('tbleconomicCode')->where('ID', $data['economicCode'])->value('description');
        $data['amount']                     = $request['amount'];
        $data['vatselect']                  = $request['vatselect'];
        $data['whtOrTax']                   = $request['whtOrTax'];
        $data['vat']                        = $request['vat'];
        $data['wht']                        = $request['wht'];
        $data['amtpayable']                 = $request['amtpayable'];
        $data['whtPayeeID']                 = $request['whtPayeeID'];
        $data['vatPayeeID']                 = $request['vatPayeeID'];
        $data['narration']                  = $request['narration'];
        $data['pvno']                       = $request['pvno'];
        $data['todayDate']                  = $request['todayDate'];
        $data['fileno']                     = $request['fileno'];
        $data['contracttype']               = $request['contracttype'];
        $data['contractoredit']             = $request['contractoredit'];
        $data['allocationlist']             = $this->getAllocation();
        $data['econocode']                  = DB::table('tbleconomicCode')->where('contractGroupID', $fields->contractTypeID)->get(); //$this->getEconomicCode($data['allocationtype'], $data['contracttype']);
        $data['staticcontr']                = ($fields->contract_Type) ? DB::table('tblcontractType')->where('ID', $fields->contract_Type)->select('ID', 'contractType')->first() : (object) array('contractType' => "");
        $request['selectedid']              = $data['selectedid'];

        $details = DB::table('tblcontractDetails')
            ->leftjoin('tblpaymentTransaction', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
            ->leftjoin('tblcontractType', 'tblcontractDetails.contract_Type', '=', 'tblcontractType.ID')
            ->leftjoin('tblcontractor', 'tblcontractDetails.companyID', '=', 'tblcontractor.id')
            ->where('tblcontractDetails.ID', $fields->contractID)
            ->first();

        if ($details and $details->companyID == 13) {
            $data['contractor']     = ($details ? $details->beneficiary : '');
        } else {
            $data['contractor']     = ($details ? $details->contractor : '');
        }
        $data['companyid']          = ($details ? $details->id : '');
        $data['totalsumamount']     = ($request['selectedid'] != "") ? $this->ContractBalance($request['selectedid']) : "";
        $data['vatwhttable']       = DB::table('tblVATWHTPayee')->orderBy('payee', 'Asc')->get();

        switch ($status) {
            case 0;
                $data['BB'] = $this->ContractBalance($conid);
                break;
            default:
                $data['BB'] = $this->ContractBalance($conid) + $vvalue;
        }
        $data['fileattach'] = $this->ContractAttachment($fields->contractID);

        //Contract List
        $data['companyDetails']   = $this->getContractorDetails();

        //Department Name
        $data['fileRefer'] = DB::table('tbldepartment_fileno')
            ->orderby('tbldepartment_fileno.filerefer', 'Asc')
            ->get();
        $data['totalv_count'] = DB::select("select sum(1) as totalv_count from tblpaymentTransaction where contractID='$conid'")[0]->totalv_count;

        return view('CreateContract.edit', $data);
    } //end class



    //post edit
    public function postEdit(Request $request)
    {
        //Start Validation
        //dd("jsjsjs");
        $this->validate($request, [
            //'contracttype'          => 'required',
            //'companyid'             => 'required',
            'amount'                => 'required',
            'narration'             => 'required',
            'amtpayable'            => 'required',
            'allocationtype'        => 'required',
            'economicCode'          => 'required',
            'todayDate'             => 'required',
            'voucherId'             => 'required',
            'beneficiaryName'       => 'required',
            'contractDetails'       => 'required',

        ], [], [

            'amount'                => 'Total Contract Value',
            'paymentdesc'           => 'Payment Description',
            'amtpayable'            => 'Amount Payable',
            'allocationtype'        => 'Allocation Type',
            'economiccode'          => 'Economic Code',
            'todayDate'             => 'Date Prepared',
            'voucherId'             => 'Voucher Transaction Unique ID not found',
            'beneficiaryName'      => 'You have not set or enter beneficiary to this voucher',
            'contractDetails'       => 'One of the vital parameter of this voucher not found'
        ]);

        //start computing variables and update
        if (is_numeric($request['companyid']) and $request['companyid'] <> 13) {
            $this->validate($request, [
                'companyid'     => 'required',
            ]);

            $deno                   = ($request['vatselect']) + 100;
            $vat1                   = ((($request['vatselect'] / $deno) * $request['amount']));
            $mockval = $request['amount'] - $vat1;
            $tax1                   = ($request['whtOrTax'] / 100) * $mockval;
            $vat = round($vat1, 2);
            $wht = round($tax1, 2);
            $fstampduty = round(($request['stampduty'] / 100) * $mockval, 2);
            $tblamtPayable =  $request['amount'] - ($vat  +  $wht + $fstampduty);
            //$tblamtPayable =  $request['amount'] - (   $vat  +  $wht ) ;

            //Update Contrator Voucher
            $V_details = DB::table('tblpaymentTransaction')->where('ID', $request['voucherId'])->first();
            if ($V_details) {
                $C_details = DB::table('tblcontractDetails')->where('ID', $V_details->contractID)->first();
                $updateIsSuccess = DB::table('tblpaymentTransaction')->where('ID', $request['voucherId'])->update([
                    'totalPayment'          => $request['amount'],
                    'paymentDescription'    => $request['narration'],
                    'VAT'                   => $request['vatselect'],
                    'VATValue'              => $vat, //$request['vat'],
                    'WHT'                   => $request['whtOrTax'],
                    'WHTValue'              => $wht, //$request['wht'],
                    'VATPayeeID'            => $request['vatPayeeID'] != '' ? $request['vatPayeeID'] : 0,
                    'WHTPayeeID'            => $request['whtPayeeID'] != '' ? $request['whtPayeeID'] : 0,
                    'stampdutypercentage'   => $request['stampduty'] != '' ? $request['stampduty'] : 0,
                    'stampduty'             => $fstampduty,
                    'amtPayable'            => $tblamtPayable,
                    'liabilityBy'           => "",
                    'economicCodeID'        => $request['economicCode'],
                    'datePrepared'          => $request['todayDate'],
                    'status'                => 0,
                    'vstage'                => 1,
                    'cpo_payment'           => 0,
                    'period'                => $this->ActivePeriod(),
                    'file_referID'          => $request['fileno'],
                    'companyID'            => $request['beneficiaryName'],
                ]);
                $getContractor = DB::table('tblcontractor')->where('id', $request['beneficiaryName'])->select('contractor', 'address')->first();
                //Contract Details
                ($updateIsSuccess ? (DB::table('tblcontractDetails')->where('ID', $request['contractDetails'])->update(['beneficiary' => $getContractor->contractor, 'payee_address' => $getContractor->address]))  : null);
            } else {
                $data['error'] = "Sorry, we cannot edit this voucher now! Please try again later.";
            }
            return redirect('display/voucher/' . $request['voucherId']);
        } else {
            //Update Staff Voucher
            $this->validate($request, [
                'payeeAddress'     => 'required',
            ]);

            if (DB::table('tblpaymentTransaction')->where('ID', $request['voucherId'])->first()) {
                $updateIsSuccess = DB::table('tblpaymentTransaction')->where('ID', $request['voucherId'])->update([
                    'totalPayment'          => $request['amount'],
                    'paymentDescription'    => $request['narration'],
                    'amtPayable'            => $request['amtpayable'],
                    //'preparedBy'            => Auth::user()->username,
                    'allocationType'        => $request['allocationtype'],
                    'economicCodeID'        => $request['economicCode'],
                    'datePrepared'          => $request['todayDate'],
                    'status'                => 0,
                    'vstage'                => 1,
                    'cpo_payment'           => 0,
                    'period'                => $this->ActivePeriod(),
                    'file_referID'          => $request['fileno'],

                ]);
                //Contract Details
                (DB::table('tblcontractDetails')->where('ID', $request['contractDetails'])->update(['contractValue' => $request['amount'], 'beneficiary' => $request['beneficiaryName'], 'payee_address' => $request['payeeAddress']]));
            } else {
                $data['error'] = "Whoops something went wrong!";
            }
            return redirect('display/voucher/' . $request['voucherId']);
        }
    } //end function



    public function continu(Request $request, $selectedid = "", $ctype = "")
    {


        if ($request['todayDate'] == '') {
            $request['todayDate'] = date('Y-m-d');
        }

        $data['warning']        = '';
        $data['success']        = '';
        $data['error'] = "";

        if ($selectedid == "") {
            $data['error'] = "Go back and select a contract";
            return redirect('/create/contract');
        }
        $request['selectedid'] = decrypt($selectedid);
        $rawConDetails = $this->ContractDetails($request['selectedid']);
        if (!$rawConDetails) {
            $data['error'] = "Go back and select a valid contract";
            return redirect('/create/contract');
        }
        //Get Voucher Details
        $data['getVoucherDetails']  = DB::table('tblpaymentTransaction')
            ->leftjoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
            ->leftjoin('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
            ->Join('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
            ->Join('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
            ->Join('tblcontractType', 'tblcontractType.ID', '=', 'tblpaymentTransaction.contractTypeID')
            ->where('tblpaymentTransaction.contractID', '=', $request['selectedid'])
            ->leftjoin('voucher_type', 'voucher_type.vouchertypeID', '=', 'tblpaymentTransaction.voucher_type_deptID')
            ->select('*', 'tbleconomicCode.economicCode', 'tbleconomicCode.ID as economicodeID', 'tbleconomicCode.description as economicName', 'tblpaymentTransaction.ID as transID', 'tblpaymentTransaction.status as payStatus', 'tblcontractType.contractType as ecoHead', 'tblcontractDetails.companyID as companyIDContractD', 'tblcontractDetails.beneficiary as beneficiaryContractD')
            ->first();
        if (!$data['getVoucherDetails']) {
            return redirect()->back();
        }
        //////////////


        if (isset($_POST['attach'])) {
            $this->validate($request, [
                'attachcaption' => 'required',
                'filex'          => 'required'
            ]);

            $fid = DB::table('tblcontractfile')
                ->insertGetId([
                    'file_desc'            => $request['attachcaption'],
                    'contractid'         => $request['selectedid'],
                    'createdby'        => Auth::user()->username,
                ]);

            $image =  $request->file('filex');
            $imagename = $id . '_' . $fid . '_' . $image->getClientOriginalName();
            $upload_path = env('UPLOAD_PATH', '');
            $destinationPath = base_path('../') . '/' . $upload_path;
            //die($destinationPath );
            $image->move($destinationPath, $imagename);
            DB::table('tblcontractfile')->where('id', $fid)
                ->update(['filename' => $imagename]);
        }
        $data['fileattach'] = $this->ContractAttachment($request['selectedid']);

        if ($request['amtpayable'] == '') {
            $request['amtpayable'] = $this->ContractBalance($request['selectedid']);
        }
        $data['ecogroup'] = $rawConDetails->contract_Type;
        $data['FileNo'] = $rawConDetails->fileNo;
        $data['ecogrouptext'] = $rawConDetails->EcoGroup;
        $data['contractID'] = $rawConDetails->ID;
        $data['selected'] = decrypt($selectedid);


        $request['contracttype2'] = $ctype;
        $data['getBalance2'] = $request['amount'];
        //$data['getBal'] = $request->old('name');

        $data['econocode']          = [];
        $data['contractorList']         = [];
        $data['economiccode']       = $request['economicCode'];
        $data['alloc']              = $request['allocationtype'];
        $data['allocationlist']         = $this->getAllocation();
        $ttall = $request['allocationtype1'];
        if ($ttall == '') {
            $request['allocationtype1'] = $request->old('allocationtype1');
        }
        $data['econocode']         = $this->getEconomicCode($request['allocationtype1'], $request['contracttype2']);

        $data['contr']              = $request['contracttype'];
        $data['staticcontr']        = ($request['contracttype2']) ? DB::table('tblcontractType')->where('ID', $request['contracttype2'])->select('ID', 'contractType')->first() : (object) array('contractType' => "");
        $data['pstatus']            = $request['pstatus'];
        $data['fileno']             = $request['fileno'];
        $data['ct']                 = $request['contractor'];
        $data['contr5']             = $request['contracttype2'];
        $data['alloc1']             = $request['allocationtype1'];
        $data['economiccode1']      = $request['economicCode1'];
        $data['oldbalanceinform']       = $request['amount'];
        $data['sel_id']             = "";
        $data['file_ex']            = "";




        $data['econocode2']         = [];
        $data['contractorList']         = [];
        $data['economiccode2']      = $request['sececonomicCode'];
        $data['alloc2']             = $request['secallocationtype'];
        $data['allocationlist2']        = $this->getAllocation();
        $data['contr2']             = $request['seccontracttype'];
        $data['contractlist']       = $this->getContract();
        $data['contractlist2']      = $this->getContract();
        $data['fileNos']            = $this->getFileNos();
        $data['tablecontent']       = [];
        $data['contractor']         = "";
        $data['companyidhid']       = "";
        $data['paymentdesc']        = "";
        $data['vatwhttable']        = DB::table('tblVATWHTPayee')->orderBy('payee', 'Asc')->get();
        $data['vatpayee']           = $request['vatPayee'];
        $data['whtpayee']           = $request['whtPayee'];
        $data['liabilityby']        = DB::table('users')->get();

        //for different stuff
        $data['vatpas']             = $request['vatselect'];
        $data['vatselect']             = $request['vatselect'];
        $data['vatvas']         = $request['vat'];
        $data['whtpas']         = $request['whtOrTax'];
        $data['whtOrTax']         = $request['whtOrTax'];
        $data['whtvas']         = $request['tax'];
        $data['amtpayble']      = $request['amtpayable'];
        $data['narration']      = $request['narration'];
        //$data['pvnoas']         = $request['pvno'];
        $data['liabilityByas']  = $request['liabilityBy'];
        $data['todayDateas']    = $request['todayDate'];
        $data['vatpayeeas']     = $request['vatPayeeID'];
        $data['whtpayeeas']     = $request['whtPayeeID'];
        $data['vatPayeeID']     = $request['vatPayeeID'];
        $data['whtPayeeID']     = $request['whtPayeeID'];
        $data['vatpaddas']      = $request['vatPayeeAddress'];
        $data['whtpaddas']      = $request['whtPayeeAddress'];
        $data['filenoas']       = $request['fileno'];

        //end of different stuff


        $data['currentuser'] = Auth::user()->username;


        $data['econocode'] = $this->getEconomicCode($request['allocationtype1'], $request['contracttype2']);
        $data['econocode2'] = $this->getEconomicCode($request['secallocationtype'], $request['contracttype2']);

        // dd($request['allocationtype1']);
        $data['instructions'] = "";

        $data['getBalance'] = round(($request['selectedid'] != "") ? $this->ContractBalance($request['selectedid']) : "0", 2);
        $data['alloc3'] = "";
        $data['economicCode_as'] = "";
        $data['econ3'] = "";



        $details = $this->getInfo($request['selectedid']);
        if ($details->companyID == 13) {
            $data['contractor']     = $details->beneficiary;
        } else {
            $data['contractor']     = $details->contractor;
        }
        $data['companyidhid']   = $details->id;
        $data['paymentdesc']    = $details->ContractDescriptions;
        $data['filenoas']   = $details->fileNo;
        $data['economicCode_as']    = $details->economicVoult;
        //dd($data['economicCode_as']);
        if ($data['economicCode_as'] != "") {

            $vll = DB::table('tbleconomicCode')->where('ID', $data['economicCode_as'])->first();
            if ($vll) {
                $data['alloc5'] = $vll->allocationID;
                $data['alloc3'] = DB::Table('tblallocation_type')->where('ID', $vll->allocationID)->first()->allocation;
                $data['econ3'] = '(' . $vll->economicCode . ') ' . $vll->description;
            } else {
                $data['economicCode_as'] = '';
            }
        }

        $data['instructions'] = "";
        //dd($request['selectedid']);
        $com    = DB::table('tblcomments')->where('affectedID', $request['selectedid'])->select('tblcomments.comment', 'tblcomments.username', 'tblcomments.added')->orderby('id', 'asc')->get();

        if ($com) {

            foreach ($com as $k => $list) {
                $newline = (array) $list;
                $name = DB::table('users')->where('username', $list->username)->first()->name;
                $newline['name'] = $name;
                $date = strtotime($list->added);
                $newline['comment'] = str_replace("\r\n", "<br>", $list->comment);
                $newline['date_added'] = date("F j, Y", $date);
                $newline['time'] = date("g:i a", $date);
                $newline = (object) $newline;
                $com[$k] = $newline;
            }
            //dd($com);
            $data['instructions'] = json_encode($com);
        }
        $data['instructions1'] = '';
        $com2 = DB::table('contract_comment')->where('fileNoID', $rawConDetails->fileNo)->orderby('commentID', 'asc')->get();
        if ($com2) {

            foreach ($com2 as $k => $list) {
                $newline = (array) [];
                $name = DB::table('users')->where('id', $list->userID)->first()->name;
                $newline['name'] = $name;
                $newline['comment'] = str_replace("\r\n", "<br>", $list->comment);
                $date = strtotime($list->date);
                $newline['date_added'] = date("F j, Y", $date);
                $newline['time'] = date("g:i a", $date);
                $newline = (object) $newline;
                $com2[$k] = $newline;
            }
            $data['instructions1'] = json_encode($com2);
        }

        $data['sel_id'] = $request['selectedid'];
        $data['file_ex'] = $details->file_ex;




        $data['selectedid'] = $request['selectedid'];
        $data['companyid']  = $request['companyid'];
        $data['getBalanceas'] = $request['amount'];
        $oldloctype = $request['allocationtype1'];

        session::put('alloc', $oldloctype);



        //ll
        //ll
        if ($request['finalsubmit'] == "complete") {



            //fully submitted form
            $data['allocationlist']         = $this->getAllocation();

            $oldloctype = $request['allocationtype1'];

            session::put('alloc', $oldloctype);
            $tblcontracttype    = $request['contracttype2'];
            $tblcontractid      = $request['selectedid'];
            $tblcompanyid       = $request['companyid'];
            $tbltotalpayment    = $request['amount'];
            $tblpaymentDesc     = $request['paymentdesc'];
            //$pvno               = $request['pvno'];
            $vat                = $request['vat'];
            $vatperc            = $request['vatselect'];
            $whtselect          = $request['whtOrTax'];
            $wht                = $request['tax'];
            $tblamtPayable      = $request['amtpayable'];
            $tblprepareby       = $request['preparedBy'];
            $tblvatpayeeid      = $request['vatPayeeID'];
            $tblwhtpayeeid      = $request['whtPayeeID'];
            $liabilityby        = $request['liabilityBy'];
            $allocationtype     = $request['allocationtype1'];
            $economiccodeid     = $request['economicCode1'];
            $dateprepared       = $request['todayDate'];
            $totalamount        = $request['totalamount'];
            $narration      = $request['narration'];



            //$data['getBalance']     = (int)$request['amount'];
            $data['vatpas'] = $request['vatselect'];
            $data['vatselect']     = $request['vatselect'];
            $data['vatvas'] = $request['vat'];
            $data['whtpas'] = $request['whtOrTax'];
            $data['whtOrTax'] = $request['whtOrTax'];

            $data['whtvas'] = $request['tax'];
            if ($request['amtpayable'] == '') {
                $request['amtpayable'] = $this->ContractBalance($request['selectedid']);
            }

            $data['amtpayble'] = (int)$request['amtpayable'];

            $data['narration'] = $request['narration'];
            $data['pvnoas'] = $request['pvno'];
            $data['liabilityByas'] = $request['liabilityBy'];
            $data['todayDateas'] = $request['todayDate'];
            $data['vatpayeeas'] = $request['vatPayeeID'];
            $data['whtpayeeas'] = $request['whtPayeeID'];
            $data['vatPayeeID']     = $request['vatPayeeID'];
            $data['whtPayeeID']     = $request['whtPayeeID'];
            $data['vatpaddas'] = $request['vatPayeeAddress'];
            $data['whtpaddas'] = $request['whtPayeeAddress'];
            $data['filenoas'] = $request['filenoas'];
            $data['getBalanceas'] = $request['amount'];
            $request['economiccodeid'] = $economiccodeid;
            //$p = "voucher/continu/{$selectedid/$ctype}";
            //
            $validating = $this->validate($request, [
                'allocationtype1'       => 'required',
                //'pvno'              	=> 'required',
                'totalamount'           => 'required',
                'narration'             => 'required',
                'amtpayable'            => 'required',
                'preparedBy'            => 'required',
                'vatPayeeID'            => 'required_unless:vatselect,0',
                'whtPayeeID'            => 'required_unless:whtOrTax,0',
                'economiccodeid'         => 'required',
                'todayDate'             => 'required'

            ], [], [

                //'pvno'                  => 'P V N O',
                'allocationtype1'       => 'Allocation type',
                'totalamount'           => 'Total Contract Value',
                'narration'             => 'Payment Description',
                'vat'                   => 'Value Added Tax',
                'vatselect'             => 'Selected Vat Percent',
                'whtOrTax'              => 'Selected Wht Percent',
                'tax'                  => 'Withheld Tax',
                'vatPayeeID'            => 'VAT Payee',
                'whtPayeeID'            => 'WHT Payee',
                'amtpayable'            => 'Amount Payable',
                'economiccodeid'         => 'Economic code',
                'todayDate'             => 'Date Prepared'

            ]);




            //$tblvatpayeeid = ($request['vatPayeeID'] == "") ? 0 : "0";
            //$tblwhtpayeeid = ($request['whtPayeeID'] == "") ? 0 : "0";
            if ($request['vatPayeeID'] == "") {
                $request['vatPayeeID'] = 0;
            }
            if ($request['whtPayeeID'] == "") {
                $request['whtPayeeID'] = 0;
            }

            //dd($request['vatPayeeID']."   ".$request['whtPayeeID']);
            $data['getBalance2'] = $request['amount'];
            //if(!DB::Table('tblpaymentTransaction')->where('PVNO', $pvno)->get()){
            //$tblamtPayable='';
            $deno = ($vatperc) + 100;
            $vat1 = ($vatperc / $deno) * $tbltotalpayment;
            $mockval = $tbltotalpayment - $vat1;
            $tax1 = ($whtselect / 100) * $mockval;
            $vat = round($vat1, 2);
            $wht = round($tax1, 2);
            $tblamtPayable =  $tbltotalpayment - ($vat  +  $wht);
            if ($vid = DB::table('tblpaymentTransaction')
                ->insertGetId([
                    'contractTypeID'        => $data['ecogroup'],
                    'contractID'            => $tblcontractid,
                    'companyID'             => $tblcompanyid,
                    'FileNo'                => $data['FileNo'] == null ? DB::table('tblpaymentTransaction')->where('contractID', $tblcontractid)->value('FileNo') : $data['FileNo'],
                    // 'PVNO'                => $pvno,
                    'totalPayment'          => $tbltotalpayment,
                    'paymentDescription'    => $narration,
                    'VAT'                   => $vatperc,
                    'VATValue'              => $vat,
                    'WHT'                   => $whtselect,
                    'WHTValue'              => $wht,
                    'VATPayeeID'            => $request['vatPayeeID'], //$tblvatpayeeid, //
                    'WHTPayeeID'            => $request['whtPayeeID'], //$tblwhtpayeeid, //
                    'amtPayable'            => $tblamtPayable,
                    'preparedBy'            => Auth::user()->id, //$tblprepareby,
                    'allocationType'        => $allocationtype,
                    'economicCodeID'        => $economiccodeid,
                    'status'                => 0,
                    'vstage'                => 1,
                    'cpo_payment'          => 0,
                    'datePrepared'          => $dateprepared,
                    'period'                => $this->ActivePeriod(),
                    'file_referID'          => DB::table('tblpaymentTransaction')->where('contractID', $tblcontractid)->value('file_referID'),
                    'department_voucher'    => strtoupper($this->getUserRole()->rolename),
                    'voucher_type_dept'     => null,
                    'voucher_type_deptID'   => 0,
                    'retire_voucher'          => 0,
                ])
            ) {

                if (DB::table('tblcontractDetails')->where('ID', $tblcontractid)->first()->paymentStatus == "") {
                    DB::table('tblcontractDetails')->where('ID', $tblcontractid)->update([
                        'paymentStatus' => 0
                    ]);
                }
                if (DB::table('tblcontractDetails')->where('ID', $tblcontractid)->first()->economicVoult == "") {
                    DB::table('tblcontractDetails')->where('ID', $tblcontractid)->update([
                        'economicVoult' => $economiccodeid
                    ]);
                }
                DB::table('tblcontractDetails')->where('ID', $tblcontractid)->update([
                    'openclose' => 0
                ]);
                return redirect('display/voucher/' . $vid);
            } else {
                //$data['error'] = "Something went wrong";
                return back()->with('error', 'Something went wrong');
            }

            if ($this->VoultBalance($economiccodeid) > $tblamtPayable) {
                //dd($request['totalamount']);
                $gross = $data['getBalance'];
                if ($gross < $request['amount']) {
                    //$data['error'] = "Gross amount cannot be greater than Total Sum (Contract Value)!";
                    return back()->with('error', 'Gross amount cannot be greater than Total Sum (Contract Value)!');
                } else {
                    //....
                }
            }
        }
        //Die("here");
        $data['companyDetails'] = $this->getBeneficiary();
        $data['fileRefer'] = [];
        $data['allocationlist']         = $this->getAllocation();
        $data['econocode']         = $this->getEconomicCode($request['allocationtype1'], $request['contracttype2']);
        //dd(strtoupper($this->getUserRole()->rolename));
        $ctypecode = 4;
        if (strtoupper($this->getUserRole()->rolename) == "RECURRENT") $ctypecode = 1;

        $data['ECONOMAIN'] = $this->getDepartmentEconomicCode($ctypecode);  //$this->getEconomicCode(session('alloc'), $data['ecogroup']);
        $data['econocode2'] = $this->getEconomicCode($request['secallocationtype'], $data['ecogroup']);

        $data['fileRefer']       =  DB::table('tbldepartment_fileno')
            ->where('tbldepartment_fileno.account_type', strtoupper($this->getUserRole()->rolename))
            ->orderby('tbldepartment_fileno.filerefer', 'Asc')
            ->get();
        return view('CreateContract.continue', $data);
    }


    //Get all economic codes
    public function getDepartmentEconomicCode($contractType)
    {
        if ($contractType == 1) {
            $economicCode = DB::table('tbleconomicCode')
                ->join('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
                ->leftjoin('tblcontractType', 'tblcontractType.ID', '=', 'tbleconomicCode.contractGroupID')
                ->where('tbleconomicCode.status', 1)
                ->where('tblcontractType.ID', $contractType)
                ->orwhere('tbleconomicCode.contractGroupID', 6)
                ->select('*', 'tbleconomicCode.ID as economicID', 'tbleconomicCode.description as economicName')
                ->orderby('tbleconomicCode.economicCode', 'Asc')
                ->get();
        } else {
            $economicCode = DB::table('tbleconomicCode')
                ->join('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
                ->leftjoin('tblcontractType', 'tblcontractType.ID', '=', 'tbleconomicCode.contractGroupID')
                ->where('tbleconomicCode.status', 1)
                ->where('tblcontractType.ID', $contractType)
                ->select('*', 'tbleconomicCode.ID as economicID', 'tbleconomicCode.description as economicName')
                ->orderby('tbleconomicCode.economicCode', 'Asc')
                ->get();
        }

        return $economicCode;
    }


    //Get Contract Details
    public function getContractorDetails()
    {
        $contractorDetails = DB::table('tblcontractor')
            ->where('tblcontractor.status', 1)
            ->where('tblcontractor.type', 1)
            ->orderby('tblcontractor.contractor', 'Asc')
            ->get();
        return $contractorDetails;
    } //end function



}//end class
