<?php

namespace App\Http\Controllers\funds;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;


class CreateContractVoucherController extends function24Controller
{


    public function setSes(Request $request)
    {
        Session::put('alloc', $request['id']);
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
        if (DB::table('tblcontractDetails')->where('ID', $request['hiddencontractid'])->update(['OC_staffId'    => $request['hiddenuserid'],])) {
            $this->UpdateAlertTable('untreated assigned voucher', 'raise/voucher', $request['hiddenuserid'], 0, 'tblcontractDetails', $request['hiddencontractid'], 1);
            $comment = Auth::user()->username . " assigned task to " . DB::table('users')->where('id', $request['hiddenuserid'])->value('name') . "to raise voucher";
            // dd($comment);
            DB::table('tblcomments')->insert(['commenttypeID' => 1, 'affectedID' => $request['hiddencontractid'], 'paymentID' => 0, 'username' => Auth::user()->username, 'comment' => $comment]);
        }

        $data['tablecontent'] = $this->getTable3($request['contracttype'], $request['contractor'], $request['fileno'], 'OC');
        foreach ($data['tablecontent'] as $key => $value) {
            $line = (array) $value;
            $line['contractBalance'] = $this->contractBalance($value->ID);
            $line['numofv'] = count(DB::table('tblpaymentTransaction')->where('contractID', $value->ID)->get());
            $line = (object) $line;
            $data['tablecontent'][$key] = $line;
        }
        //dd($data['tablecontent']);
        $data['companyDetails'] = $this->getBeneficiary();
        $data['fileRefer'] = [];
        $data['fileNos']            = $this->getFileNos();
        $data['UnitStaff'] = $this->UnitStaff('OC');
        return view('funds.CreateContract.precreatecontract', $data);
    }
    public function PrecreateContractVoucherAdvances(Request $request)
    {

        $data['warning']        = '';
        $data['success']        = '';
        $data['error']      = '';
        $data['contracttypes']       = $this->getContractType();
        $data['contracttype']       = $request['contracttype'];
        $data['contractor']       = $request['contractor'];
        $data['fileno']       = $request['fileno'];
        if (DB::table('tblcontractDetails')->where('ID', $request['hiddencontractid'])->update(['OC_staffId'    => $request['hiddenuserid'],])) {
            $this->UpdateAlertTable('untreated assigned voucher', 'raise/advances-voucher', $request['hiddenuserid'], 0, 'tblcontractDetails', $request['hiddencontractid'], 1);
            $comment = Auth::user()->username . " assigned task to " . DB::table('users')->where('id', $request['hiddenuserid'])->value('name') . "to raise voucher";
            DB::table('tblcomments')->insert(['commenttypeID' => 1, 'affectedID' => $request['hiddencontractid'], 'paymentID' => 0, 'username' => Auth::user()->username, 'comment' => $comment]);
        }

        $data['tablecontent'] = $this->getTable3($request['contracttype'], $request['contractor'], $request['fileno'], 'AD');
        //dd( $data['tablecontent']);
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
        $data['UnitStaff'] = $this->UnitStaff('ADS');
        return view('funds.CreateContract.precreateadvance', $data);
    }

    //staff advance assigned voucher
    public function PrecreateContractVoucherAdvancesStaff(Request $request)
    {
        $data['warning']        = '';
        $data['success']        = '';
        $data['error']      = '';
        $data['contracttypes']       = $this->getContractType();
        $data['contracttype']       = $request['contracttype'];
        $data['contractor']       = $request['contractor'];
        $data['fileno']       = $request['fileno'];

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
        $data['UnitStaff'] = $this->UnitStaff('ADS');

        return view('funds.CreateContract.precreateadvancestaff', $data);
    }
    public function PrecreateSalaryVoucher(Request $request)
    {

        $data['warning']        = '';
        $data['success']        = '';
        $data['error']      = '';
        $data['contracttypes']       = $this->getContractType();
        $data['contracttype']       = $request['contracttype'];
        $data['contractor']       = $request['contractor'];
        $data['fileno']       = $request['fileno'];
        if (DB::table('tblcontractDetails')->where('ID', $request['hiddencontractid'])->update(['OC_staffId'    => $request['hiddenuserid'],])) {
            $this->UpdateAlertTable('untreated assigned voucher', 'raise/advances-voucher', $request['hiddenuserid'], 0, 'tblcontractDetails', $request['hiddencontractid'], 1);
            $comment = Auth::user()->username . " assigned task to " . DB::table('users')->where('id', $request['hiddenuserid'])->value('name') . "to raise voucher";
            DB::table('tblcomments')->insert(['commenttypeID' => 1, 'affectedID' => $request['hiddencontractid'], 'paymentID' => 0, 'username' => Auth::user()->username, 'comment' => $comment]);
        }

        $data['tablecontent'] = $this->getTable3($request['contracttype'], $request['contractor'], $request['fileno'], 'HS');
        // dd($data['tablecontent']);
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
        $data['UnitStaff'] = $this->UnitStaff('SA');
        return view('funds.CreateContract.precreatesalary', $data);
    }
    public function RejectTask_Othercharges(Request $request)
    {
        //dd(URL::previous());
        $lastofficer = DB::table('tblcontractDetails')->where('ID', '=', $request['id'])->value('approval_last_action_by');
        DB::table('tblcontractDetails')->where('ID', $request['id'])
            ->update([
                'awaitingActionby'    => $lastofficer,
                'openclose' => 0,
                'isrejected' => 1,
                'OC_staffId' => null,
                'approvalStatus' => 0
            ]);
        $this->UpdateAlertTable('Rejected Task', 'procurement/approve', 0, $lastofficer, 'tblcontractDetails', $request['id'], 1);
        DB::table('tblcomments')->insert([
            'commenttypeID' => 1,
            'affectedID' => $request['id'],
            'username' => Auth::user()->username,
            'comment' => trim(preg_replace('/\s\s+/', ' ', $request['comment'])) . ' (refer to ' . DB::table('tblcontractDetails')->where('ID', '=', $request['id'])->value('approval_last_action_by') . ')'
        ]);
        return redirect(URL::previous())->with('message', 'record successfully updated.');
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

        return view('funds.CreateContract.createcontract', $data);
    }

    public function edit(Request $request, $id = "")
    {
        $data['warning'] = '';
        $data['success'] = '';
        $data['error'] = '';
        $fields = DB::table('tblpaymentTransaction')
            ->where('tblpaymentTransaction.ID', '=', $id)
            ->where('tblpaymentTransaction.vstage', '<=', 1)
            ->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
            ->select('tblpaymentTransaction.*', 'tblcontractDetails.contract_Type', 'tblcontractDetails.fileNo')
            ->first();

        if ($fields) {
            $conid = $fields->contractID;
            $data['contractID'] = $conid;
            $status = $fields->vstage;
            $vvalue = $fields->totalPayment;
        } else {
            return redirect('/');
        }
        //dd($fields->ID);
        $data['selectedid'] = $fields->ID;
        $com = DB::table('tblcomments')
            ->where('affectedID', $fields->contractID)
            ->orderby('id', 'asc')->select('tblcomments.comment', 'tblcomments.username', 'tblcomments.added')->get();

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
        $request['fileno']    = $fields->fileNo;

        if ($request['contracttype'] == '') $request['contracttype']       = $fields->contract_Type;
        $request['companyid']    = $fields->companyID;
        $request['contractoredit']         = $fields->companyID;
        if ($request['retainrecord'] == '') {
            $request['economicCode']    = $fields->economicCodeID;
            $request['amount']    = $fields->totalPayment;
            $request['vatselect']    = $fields->VAT;
            $request['whtOrTax']    = $fields->WHT;
            $request['vat']    = $fields->VATValue;
            $request['wht']    = $fields->WHTValue;
            $request['amtpayable']    = $fields->amtPayable;
            $request['stampduty']    = $fields->stampdutypercentage;
            $request['stampdutyval']    = $fields->stampduty;
            $request['prem']    = $fields->premiumcharge;
            $request['prempercentage']    = $fields->premiumpercentage;
            $request['whtPayeeID']    = $fields->WHTPayeeID;
            $request['vatPayeeID']    = $fields->VATPayeeID;
            $request['narration']    = $fields->paymentDescription;
            $request['pvno']    = $fields->PVNO;
            $request['todayDate']    = $fields->datePrepared;
            //dd($request['prempercentage']);
        }
        $data['allocationtype'] = 5;
        $data['economicCode'] = $request['economicCode'];
        $data['amount'] = $request['amount'];
        $data['vatselect'] = $request['vatselect'];
        $data['whtOrTax'] = $request['whtOrTax'];
        $data['vat'] = $request['vat'];
        $data['wht'] = $request['wht'];
        $data['stampduty'] = $request['stampduty'];
        $data['stampdutyval'] = $request['stampdutyval'];
        $data['prempercentage'] = $request['prempercentage'];
        $data['prem']     = $request['prem'];
        $data['amtpayable'] = $request['amtpayable'];
        $data['whtPayeeID'] = $request['whtPayeeID'];
        $data['vatPayeeID'] = $request['vatPayeeID'];
        $data['narration'] = $request['narration'];
        $data['pvno'] = $request['pvno'];
        $data['todayDate'] = $request['todayDate'];
        $data['fileno']    = $request['fileno'];
        $data['contracttype']  = $request['contracttype'];
        $data['contractoredit']    = $request['contractoredit'];
        $data['allocationlist']    = $this->getAllocation();
        $data['econocode'] = $this->getEconomicCode(5, $data['contracttype']);
        $data['staticcontr'] = ($fields->contract_Type) ? DB::table('tblcontractType')->where('ID', $fields->contract_Type)->select('ID', 'contractType')->first() : (object) array('contractType' => "");
        $details = $this->getInfo($fields->contractID);
        //$details = $this->getInfo($request['selectedid']);

        if ($details->companyID == 13) {
            $data['contractor']     = $details->beneficiary;
        } else {
            $data['contractor']         = $details->contractor;
        }
        $data['companyid']       = $details->id;
        $data['totalsumamount'] = ($request['selectedid'] != "") ? $this->ContractBalance($request['selectedid']) : "";
        $data['vatwhttable']        = DB::table('tblVATWHTPayee')->where('payee_status', 1)->get();
        if ($request['finalsubmit'] == "complete-edit") {

            $this->validate($request, [

                'contracttype'          => 'required',
                'companyid'             => 'required',
                //'pvno'                  => 'required',
                'amount'           => 'required',
                'narration'             => 'required',
                'amtpayable'            => 'required',
                'vatPayeeID'            => 'required_unless:vatselect,0',
                'whtPayeeID'            => 'required_unless:whtOrTax,0',
                'allocationtype'       => 'required',
                'economicCode'         => 'required',
                'todayDate'         => 'required'

            ], [], [

                'contracttype'          => 'Contract Type',
                'companyid'             => 'Contractor',
                //'pvno'                  => 'P V N O',
                'amount'           => 'Total Contract Value',
                'paymentdesc'           => 'Payment Description',
                'vat'               => 'Value Added Tax',
                'vatselect'         => 'Selected Vat Percent',
                'whtOrTax'          => 'Selected Wht Percent',
                'tax'               => 'Withheld Tax',
                'vatPayeeID'            => 'VAT Payee',
                'whtPayeeID'            => 'WHT Payee',
                'amtpayable'            => 'Amount Payable',
                'allocationtype'       => 'Allocation Type',
                'economiccode'         => 'Economic Code',
                'todayDate'         => 'Date Prepared'
            ]);
            $deno = ($request['vatselect']) + 100;
            $totalpayment = $request['amount'];
            $premvalue = $totalpayment * 0.01 * (is_numeric($request['prempercentage']) ? $request['prempercentage'] : 0);
            if ($premvalue > 0.001) {
                $paymentbal = $premvalue;
                $vat1 = ($request['vatselect'] / $deno) * $paymentbal;
                $mockval = $paymentbal - $vat1;
                $tax1 = ($request['whtOrTax'] / 100) * $mockval;
                $request['vat'] = round($vat1, 2);
                $request['wht'] = round($tax1, 2);
                $fstampduty = round(($request['stampduty'] / 100) * $mockval, 2);
                $request['amtpayable'] =  $totalpayment - ($request['vat']  +  $request['wht'] + $fstampduty);
            } else {
                // $deno = 100;
                $paymentbal = $totalpayment;
                $vat1 = ($request['vatselect'] / $deno) * $paymentbal;
                $mockval = $paymentbal - $vat1;
                $tax1 = ($request['whtOrTax'] / 100) * $mockval;
                $request['vat'] = round($vat1, 2);
                $request['wht'] = round($tax1, 2);
                $fstampduty = round(($request['stampduty'] / 100) * $mockval, 2);
                $request['amtpayable'] =  $totalpayment - ($request['vat']  +  $request['wht'] + $fstampduty);
            }
            if (DB::table('tblpaymentTransaction')->where('ID', $id)
                ->update([
                    'premiumpercentage'          => is_numeric($request['prempercentage']) ? $request['prempercentage'] : 0,
                    'premiumcharge'          => 0, //$premvalue,
                    'totalPayment'          => $request['amount'],
                    'paymentDescription'    => $request['narration'],
                    'VAT'                   => $request['vatselect'],
                    'VATValue'              => $request['vat'],
                    'WHT'                   => $request['whtOrTax'],
                    'WHTValue'              => $request['wht'],
                    'stampdutypercentage'   => $request['stampduty'] != '' ? $request['stampduty'] : 0,
                    'stampduty'             => $fstampduty,
                    'VATPayeeID'            => $request['vatPayeeID'],
                    'WHTPayeeID'            => $request['whtPayeeID'],
                    'amtPayable'            => $request['amtpayable'],
                    'preparedBy'            => Auth::user()->id,
                    'liabilityBy'           => "",
                    'allocationType'        => $request['allocationtype'],
                    'economicCodeID'        => $request['economicCode'],
                    'datePrepared'          => $request['todayDate'],
                    'vstage'          => -1,
                    'period'                => $this->ActivePeriod()
                ])
            ) {
                //$data['success'] = "Voucher was edited successfully!";
                //$vid = DB::table('tblpaymentTransaction')->where('PVNO', $request['pvno'])->first()->ID;
                $previous =    DB::table('tblpaymentTransaction')
                    ->join('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
                    ->where('tblpaymentTransaction.ID', '=', $id)->first();
                //dd($previous);
                $data1 = array(
                    "totalPayment" => $previous->totalPayment,
                    "Payment Description" => $previous->paymentDescription,
                    "Vat" => $previous->VAT,
                    "VATValue" => $previous->VATValue,
                    "WHT" => $previous->WHT,
                    "WHT Value" => $previous->WHTValue,
                    "Stamp Duty Percent" => $previous->stampdutypercentage,
                    "Stamp Duty" => $previous->stampduty,
                    "vat Payee" => $previous->VATPayeeID,
                    "WHT Payee" => $previous->WHTPayeeID,
                    "Amount Payable" => $previous->amtPayable,
                    "Prepared By" => $previous->preparedBy,
                    "Libility By" => $previous->liabilityBy,
                    "Allocation Type" => $previous->allocationType,
                    "Economic code" => $previous->economicCodeID
                );
                $post_encode = json_encode($data1);

                $data2 = array(
                    "Total Payment" => $request['amount'],
                    "Payment Description" => $request['narration'],
                    "Vat" => $request['vatselect'],
                    "VATValue" => $request['vat'],
                    "WHT" => $request['whtOrTax'],
                    "WHT Value" => $request['wht'],
                    "Stamp Duty Percent" => $request['stampduty'] != '' ? $request['stampduty'] : 0,
                    "Stamp Duty" => $fstampduty,
                    "vat Payee" => $request['vatPayeeID'],
                    "WHT Payee" => $request['whtPayeeID'],
                    "Amount Payable" => $request['amtpayable'],
                    "Prepared By" => Auth::user()->id,
                    "Libility By" => '',
                    "Allocation Type" => $request['allocationtype'],
                    "Economic code" => $request['economicCode']
                );
                $post_encode2 = json_encode($data2);
                $operation = "Voucher edited from $post_encode to $post_encode2";
                $title = "Voucher with ID $id Edited";
                $this->addLogg($operation, $title);
                return redirect('display/voucher/' . $id);
            } else {
                $data['error'] = "Whoops something went wrong!";
            }
        }
        switch ($status) {
            case 0;
                $data['BB'] = $this->ContractBalance($conid);
                break;
            default:
                $data['BB'] = $this->ContractBalance($conid) + $vvalue;
        }
        $data['fileattach'] = $this->ContractAttachment($fields->contractID);
        $data['contractlist'] = $this->getContract();
        return view('funds.CreateContract.edit', $data);
    }



    public function continu(Request $request, $selectedid = "", $ctype = "")
    {
        // dd($request['totalamount']);
        if ($request['todayDate'] == '') {
            $request['todayDate'] = date('Y-m-d');
        }

        $data['warning']        = '';
        $data['success']        = '';
        $data['error'] = "";

        if ($selectedid == "") {

            $data['error'] = "Go back and select a contract";
            return redirect('/raise/voucher');
        }
        $request['selectedid'] = decrypt($selectedid);
        $rawConDetails = $this->ContractDetails($request['selectedid']);
        if (!$rawConDetails) {
            $data['error'] = "Go back and select a valid contract";
            return redirect('/raise/voucher');
        }
        if ($rawConDetails->companyID == 13) {
            $data['error'] = "Go back and select a valid staffclaim";
            return redirect("/create/staff-voucher/" . $rawConDetails->ID);
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
        $data['contracttype'] = $request['contracttype'];

        if ($request['contracttype'] == '') {
            $data['contracttype'] = $data['ecogroup'];
        }

        $request['contracttype2'] = $ctype;
        $data['getBalance2'] = $request['amount'];
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
        $data['vatwhttable']        = DB::table('tblVATWHTPayee')->where('payee_status', 1)->get();
        $data['vatpayee']           = $request['vatPayee'];
        $data['whtpayee']           = $request['whtPayee'];
        $data['liabilityby']        = DB::table('users')->get();
        $data['vatpas']             = $request['vatselect'];
        $data['vatvas']         = $request['vat'];
        $data['whtpas']         = $request['whtOrTax'];
        $data['stampduty']     = $request['stampduty'];
        $data['prempercentage']     = $request['prempercentage'];
        $data['whtvas']         = $request['tax'];
        $data['prem']     = $request['prem'];
        $data['stampdutyval']     = $request['stampdutyval'];
        $data['amtpayble']      = $request['amtpayable'];
        $data['narration']      = $request['narration'];
        $data['liabilityByas']  = $request['liabilityBy'];
        $data['todayDateas']    = $request['todayDate'];
        $data['vatpayeeas']     = $request['vatPayeeID'];
        $data['whtpayeeas']     = $request['whtPayeeID'];
        $data['vatpaddas']      = $request['vatPayeeAddress'];
        $data['whtpaddas']      = $request['whtPayeeAddress'];
        $data['filenoas']       = $request['fileno'];
        $data['currentuser'] = Auth::user()->username;
        $data['econocode'] = $this->getEconomicCode(5, $data['contracttype']);
        $data['econocode2'] = $this->getEconomicCode(5, $data['contracttype']);
        $data['instructions'] = "";

        $data['getBalance'] = (int) ($request['selectedid'] != "") ? $this->ContractBalance($request['selectedid']) : "";

        $details = $this->getInfo($request['selectedid']);
        if ($details->companyID == 13) {
            $data['contractor']     = $details->beneficiary;
            $claimid = $details->claimid;
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

        $data['sel_id'] = $request['selectedid'];
        $data['file_ex'] = $details->file_ex;




        $data['selectedid'] = $request['selectedid'];
        $data['companyid']  = $request['companyid'];
        $data['getBalanceas'] = $request['amount'];

        if ($request['finalsubmit'] == "complete") {
            //dd($request['totalamount']."kdjdjd");
            //dd(round($request['totalamount'],2));
            //dd(round($this->ContractBalance($request['selectedid']),2));

            $voucherCountNo = 1;
            if ((float) $request['vat'] > 0) {
                $voucherCountNo++;
            }
            if ((float) $request['tax'] > 0) {
                $voucherCountNo++;
            }
            if ((float) $request['stampduty'] > 0) {
                $voucherCountNo++;
            }

            $tblcontractid                  = $request['selectedid'];
            $tblcompanyid                   = $request['companyid'];
            $tbltotalpayment                = $request['amount'];
            $tblpaymentDesc                 = $request['paymentdesc'];
            $vat                            = $request['vat'];
            $vatperc                        = $request['vatselect'];
            $whtselect                      = $request['whtOrTax'];
            $wht                            = $request['tax'];
            $tblamtPayable                  = $request['amtpayable'];
            $tblprepareby                   = $request['preparedBy'];
            $tblvatpayeeid                  = $request['vatPayeeID'];
            $tblwhtpayeeid                  = $request['whtPayeeID'];
            $liabilityby                    = $request['liabilityBy'];
            $allocationtype                 = $request['allocationtype1'];
            $economiccodeid                 = $request['economicCode1'];
            $dateprepared                   = $request['todayDate'];
            $totalamount                    = $request['totalamount'];
            $narration                      = $request['narration'];
            $data['vatpas']                 = $request['vatselect'];
            $data['vatvas']                 = $request['vat'];
            $data['whtpas']                 = $request['whtOrTax'];
            $data['stampduty']              = $request['stampduty'];
            $data['prempercentage']         = $request['prempercentage'];
            $data['whtvas']                 = $request['tax'];
            $data['stampdutyval']           = $request['stampdutyval'];
            $data['prem']                   = $request['prem'];

            if ($request['amtpayable'] == '') {
                $request['amtpayable'] = $this->ContractBalance($request['selectedid']);
            }

            $data['amtpayble']              = $request['amtpayable'];
            $data['narration']              = $request['narration'];
            $data['pvnoas']                 = $request['pvno'];
            $data['liabilityByas']          = $request['liabilityBy'];
            $data['todayDateas']            = $request['todayDate'];
            $data['vatpayeeas']             = $request['vatPayeeID'];
            $data['whtpayeeas']             = $request['whtPayeeID'];
            $data['vatpaddas']              = $request['vatPayeeAddress'];
            $data['whtpaddas']              = $request['whtPayeeAddress'];
            $data['filenoas']               = $request['filenoas'];
            $data['getBalanceas']           = $request['amount'];
            $request['economiccodeid']      = $economiccodeid;

            $validating = $this->validate($request, [
                'allocationtype1'       => 'required',
                'totalamount'           => 'required',
                'narration'             => 'required',
                'amtpayable'            => 'required',
                'preparedBy'            => 'required',
                'vatPayeeID'            => 'required_unless:vatselect,0',
                'whtPayeeID'            => 'required_unless:whtOrTax,0',
                'economiccodeid'         => 'required',
                'todayDate'             => 'required'
            ], [], [
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


            if ($request['vatPayeeID'] == "") {
                $request['vatPayeeID'] = 0;
            }
            if ($request['whtPayeeID'] == "") {
                $request['whtPayeeID'] = 0;
            }
            $data['getBalance2'] = $request['amount'];
            $premvalue = $tbltotalpayment * 0.01 * (is_numeric($request['prempercentage']) ? $request['prempercentage'] : 0);


            if ($premvalue > 0.001) {
                $paymentbal = $premvalue;
                $deno = ($vatperc) + 100;
                $vat1 = ($vatperc / $deno) * $paymentbal;
                $mockval = $paymentbal - $vat1;
                $tax1 = ($whtselect / 100) * $mockval;
                $vat = round($vat1, 2);
                $wht = round($tax1, 2);
                $fstampduty = round(($data['stampduty'] / 100) * $mockval, 2);
                $tblamtPayable =  $tbltotalpayment - ($vat  +  $wht + $fstampduty);
            } else {
                $paymentbal = $tbltotalpayment;
                $deno = ($vatperc) + 100;
                // dd($paymentbal);

                // $deno = 100;
                $vat1 = ($vatperc / $deno) * $paymentbal;
                $mockval = $paymentbal - $vat1;
                $tax1 = ($whtselect / 100) * $mockval;
                $vat = round($vat1, 2);
                $wht = round($tax1, 2);
                $fstampduty = round(($data['stampduty'] / 100) * $mockval, 2);
                $tblamtPayable =  $paymentbal - ($vat  +  $wht + $fstampduty);
            }

            $contractInfo = DB::table('tblcontractDetails')->where('ID', $request['selectedid'])->first();
            if ($contractInfo->contract_Type == 4) {
                $contractTypeInFileNo =  "SCN/OC/CAP/";
            }
            if ($contractInfo->contract_Type == 1) {
                if($contractInfo->awaitingActionby == 'AD'){
                    $contractTypeInFileNo = "SCN/ADV/";
                }else{
                    $contractTypeInFileNo = "SCN/OC/";
                }
            }
            if ($contractInfo->contract_Type == 6) {
                $contractTypeInFileNo =  "SCN/PE/";
            }
            $nextVoucherNo = $this->VnextNo($data['contracttype']);

            // dd($vatperc);
            if (round($this->ContractBalance($request['selectedid']), 2) < round($tblamtPayable, 2))  return back()->with('error', 'It seem this voucher have already been created');
            //dd($tblamtPayable);
            if ($vid = DB::table('tblpaymentTransaction')
                ->insertGetId([
                    'contractTypeID'        => $data['contracttype'],
                    'contractID'            => $tblcontractid,
                    'companyID'         => $tblcompanyid,
                    'FileNo'         => $data['FileNo'],
                    'totalPayment'          => $tbltotalpayment,
                    'premiumpercentage'          => is_numeric($request['prempercentage']) ? $request['prempercentage'] : 0,
                    'premiumcharge'          => 0, //$premvalue,
                    'paymentDescription'        => $narration,
                    'VAT'               => $vatperc,
                    'VATValue'          => $vat,
                    'WHT'               => $whtselect,
                    'WHTValue'          => $wht,
                    'VATPayeeID'            => $request['vatPayeeID'], //$tblvatpayeeid, //
                    'WHTPayeeID'            => $request['whtPayeeID'], //$tblwhtpayeeid, //
                    'stampdutypercentage'   => $data['stampduty'] != '' ? $data['stampduty'] : 0,
                    'stampduty'             => $fstampduty,
                    'amtPayable'            => $tblamtPayable,
                    'preparedBy'            => Auth::user()->id,
                    'allocationType'        => $allocationtype,
                    'economicCodeID'        => $economiccodeid,
                    'status'                => 0,
                    'vstage'                => $contractInfo->contract_Type == 6 ? -2 : -1,
                    'datePrepared'          => $dateprepared,
                    // 'vref_no'          => $this->VnextNo($data['contracttype']),
                    'vref_no'          => $nextVoucherNo,
                    'voucherFileNo'      => $contractTypeInFileNo."".$nextVoucherNo."/".date('Y'),
                    'period'        => $this->NewActivePeriod($data['contracttype']),
                    'voucherNoCount' => $voucherCountNo
                ])
            ) {
                if (DB::table('tblcontractDetails')->where('ID', $tblcontractid)->first()->paymentStatus == "") {
                    DB::table('tblcontractDetails')->where('ID', $tblcontractid)->update([
                        'paymentStatus' => 0
                    ]);
                }
                if (DB::table('tblcontractDetails')->where('ID', $tblcontractid)->first()->economicVoult == "") {
                    DB::table('tblcontractDetails')->where('ID', $tblcontractid)->update([
                        'economicVoult' => $economiccodeid,
                        'contract_Type' => $data['contracttype']
                    ]);
                }
                DB::table('tblcontractDetails')->where('ID', $tblcontractid)->update([
                    'openclose' => 0
                ]);
                $name = Auth::user()->name;
                $taskscheduled = $this->UpdateAlertTable("Voucher Clearance", 'occheckby/voucher', '0', 'OC', 'tblpaymentTransaction', $vid, 1);
                $taskscheduled = $this->UpdateAlertTable("", '', '', '', 'tblcontractDetails', $tblcontractid, 0);
                $this->addLogg("Voucher with id: $vid created by $name", "New voucher created");
                return redirect('display/voucher/' . $vid);
            } else {
                return back()->with('error', 'Something went wrong');
            }
            if ($this->VoultBalance($economiccodeid) > $tblamtPayable) {
                $gross = $data['getBalance'];
                if ($gross < $request['amount']) {
                    return back()->with('error', 'Gross amount cannot be greater than Total Sum (Contract Value)!');
                } else {
                }
            }
        } else {
            //dd("pls check later");
        }
        $data['ECONOMAIN'] = $this->getEconomicCode(5, $data['contracttype']);
        return view('funds.CreateContract.continue', $data);
    }

    public function statffVoucher(Request $request, $contractid = "")
    {
        // dd("pls check later");
        if ($request['todayDate'] == '') {
            $request['todayDate'] = date('Y-m-d');
        }

        if (Session::get('special') == 1)     return redirect('/voucher/continue-special/$contractid');
        $data['todayDate'] = $request['todayDate'];
        $data['warning']        = '';
        $data['success']        = '';
        $data['error'] = "";
        $data['contractid']        = $request['contractid'];
        $data['details']        = $request['details'];
        $data['contractid']        = $contractid;
        $data['economiccode']        = $request['economiccode'];

        $rawConDetails = $this->ContractDetails($data['contractid']);
        if (!$rawConDetails) {
            $data['error'] = "Go back and select a valid contract";
            return redirect('/raise/voucher');
        }

        if (!$rawConDetails->companyID == 13) {
            $data['error'] = "Go back and select a valid staffclaim";
            return redirect('/raise/voucher');
        }

        $clmid = $rawConDetails->claimid;
        $is_advance = ($rawConDetails->awaitingActionby == 'AD') ? 1 : 0;

        if ($rawConDetails->awaitingActionby == 'HS') {
            $is_advance = 3;
        }

        $bene_sum = DB::select("SELECT  sum(`staffamount`) as tsum FROM `tblselectedstaffclaim` WHERE `claimID`='$clmid'")[0]->tsum;

        if (round($bene_sum, 2) < round($rawConDetails->contractValue, 2)) return  redirect('create/procurement-staff-beneficiary/' . $rawConDetails->ID);

        if (isset($_POST['delete'])) {

            $this->validate($request, [
                'vid'         => 'required',
            ], [], [
                'vid'         => 'Selected voucher',
            ]);
            $vid = $request['vid'];
            //DB::DELETE("DELETE FROM `tblpaymentTransaction` WHERE `ID`='$vid'");

            DB::table('tblpaymentTransaction')->where('ID', $vid)
                ->update([
                    'totalPayment'      => 0,
                    'amtPayable'        => 0,
                    'is_restore'        => 1,
                ]);
            DB::DELETE("DELETE FROM `tblvoucherBeneficiary` WHERE `voucherID`='$vid'");
        }

        // dd(php_ini_loaded_file()."-".ini_get('max_input_vars'));
        if (isset($_POST['continue'])) {

            $this->validate($request, [
                'economiccode'         => 'required',
                'details'         => 'required',
            ], [], [
                'economiccode'          => 'Economic Code',
                'details'               => 'Payment Details',
            ]);

            // $data['beneficiary'] = $this->ClaimBenefeciary($rawConDetails->claimid); // old 10-02-2026
            $data['beneficiary'] = $this->ClaimBenefeciaryNew($rawConDetails->claimid);

            $sumtotal = 0;
            $selectedStaffClaimTotal = 0;

            foreach ($data['beneficiary'] as $value) {
                $full_name = $value->surname . " " . $value->first_name . " " . $value->othernames;
                if ($request['amount' . $value->selectedID] == '') $request['amount' . $value->selectedID] = 0;
                if ($request['amount' . $value->selectedID] != 0) {
                    if (!is_numeric($request['amount' . $value->selectedID])) {
                        $msg = "This action cannot be completed because of invalid input " . $request['amount' . $value->selectedID] .  " corresponding to " . $value->full_name;
                        return back()->with('error', $msg);
                    }
                    if (round($request['amount' . $value->selectedID], 2) > round($value->amtpending, 2)) {
                        $msg = "This action cannot be completed because " . $request['amount' . $value->selectedID] . " is greater than- " . $request['amount' . $value->selectedID] . "   " . $value->amtpending . " in record corresponding to " . $value->full_name;
                        return back()->with('error', $msg);
                    }

                    if ($request['amount' . $value->selectedID] > 0) $selectedStaffClaimTotal += 1;
                    if ($selectedStaffClaimTotal == 1) $selectedStaffClaimFullName = $full_name;
                    $sumtotal += $request['amount' . $value->selectedID];
                }
            }


            $Ecodetails = $this->Ecodetails($data['economiccode']);
            //dd($Ecodetails);
            if (!$Ecodetails) return back()->with('error', 'The economic code you selected is not valid! Pls try again');
            if ($sumtotal == 0) return back()->with('error', 'This action is not successful because the amount passed for the voucher');

            $Ecodetails = $Ecodetails[0];

            if ($selectedStaffClaimTotal == 1) {
                $beneficiary = $selectedStaffClaimFullName;
            } else if ($selectedStaffClaimTotal == 2) {
                $beneficiary = $selectedStaffClaimFullName . " and 1 other";
            } else if ($selectedStaffClaimTotal > 2) {
                $beneficiary = $selectedStaffClaimFullName . " and " . ($selectedStaffClaimTotal - 1) . " others";
            } else {
                //save code: in case
                $beneficiary = "";
            }


            //pitoff
            $getAwaitActionBy = DB::table('tblcontractDetails')->where('ID', $contractid)->value('awaitingActionby');
            if ($getAwaitActionBy == 'HC') {
                $getAwaitActionBy = 2;
            } elseif ($getAwaitActionBy == 'HEC') {
                $getAwaitActionBy = 1;
            } elseif ($getAwaitActionBy == 'HAUD') {
                $getAwaitActionBy = 3;
            } elseif ($getAwaitActionBy == 'HCPO') {
                $getAwaitActionBy = 4;
            } else {
                $getAwaitActionBy = -1;
            }
            //   dd($getAwaitActionBy);
            //pitoff

            $contractInfo = DB::table('tblcontractDetails')->where('ID', $contractid)->first();
            if ($contractInfo->contract_Type == 4) {
                $contractTypeInFileNo =  "SCN/OC/CAP/";
            }
            if ($contractInfo->contract_Type == 1) {
                if($contractInfo->awaitingActionby == 'AD'){
                    $contractTypeInFileNo = "SCN/ADV/";
                }else{
                    $contractTypeInFileNo = "SCN/OC/";
                }
            }
            if ($contractInfo->contract_Type == 6) {
                $contractTypeInFileNo =  "SCN/PE/";
            }
            $nextVoucherNo = $this->VnextNo($Ecodetails->contractGroupID);
            $vid = DB::table('tblpaymentTransaction')->where('contractID', $rawConDetails->ID)->where('is_restore', 1)->value('ID');
            if ($vid) {
                DB::table('tblpaymentTransaction')->where('ID', $vid)
                    ->update([
                        'contractTypeID'        => $Ecodetails->contractGroupID,
                        'contractID'            => $rawConDetails->ID,
                        'companyID'         => 13,
                        'FileNo'         => $rawConDetails->fileNo,
                        'totalPayment'          => $sumtotal,
                        'paymentDescription'        => $data['details'],
                        'VAT'               => 0,
                        'VATValue'          => 0,
                        'WHT'               => 0,
                        'WHTValue'          => 0,
                        'VATPayeeID'            => 0, //$tblvatpayeeid, //
                        'WHTPayeeID'            => 0, //$tblwhtpayeeid, //
                        'amtPayable'            => $sumtotal,
                        'preparedBy'            => Auth::user()->id,
                        'allocationType'        => $Ecodetails->allocationID,
                        'economicCodeID'        => $data['economiccode'],
                        'status'                => 0,
                        'vstage'                => $contractInfo->contract_Type == 6 ? -2 : -1,
                        'is_restore'        => 0,
                        'is_advances'        => $is_advance,
                        'datePrepared'          => $request['todayDate'],
                        'period'        => $this->NewActivePeriod($Ecodetails->contractGroupID),
                        //'vref_no'          => $this->VnextNo(),
                        'payment_beneficiary' => $beneficiary,
                    ]);
            } else {

                $vid = DB::table('tblpaymentTransaction')
                    ->insertGetId([
                        'contractTypeID'        => $Ecodetails->contractGroupID,
                        'contractID'            => $rawConDetails->ID,
                        'companyID'         => 13,
                        'FileNo'         => $rawConDetails->fileNo,
                        'totalPayment'          => $sumtotal,
                        'paymentDescription'        => $data['details'],
                        'VAT'               => 0,
                        'VATValue'          => 0,
                        'WHT'               => 0,
                        'WHTValue'          => 0,
                        'VATPayeeID'            => 0, //$tblvatpayeeid, //
                        'WHTPayeeID'            => 0, //$tblwhtpayeeid, //
                        'amtPayable'            => $sumtotal,
                        'preparedBy'            => Auth::user()->id,
                        'allocationType'        => $Ecodetails->allocationID,
                        'economicCodeID'        => $data['economiccode'],
                        'status'                => 0,
                        'vstage'                => $contractInfo->contract_Type == 6 ? -2 : -1,
                        'is_advances'        => $is_advance,
                        'datePrepared'          => $request['todayDate'],
                        'period'        => $this->NewActivePeriod($Ecodetails->contractGroupID),
                        // 'vref_no'          => $this->VnextNo($Ecodetails->contractGroupID),
                        'vref_no'          => $nextVoucherNo,
                        'voucherFileNo'      => $contractTypeInFileNo."".$nextVoucherNo."/".date('Y'),
                        'payment_beneficiary' => $beneficiary
                    ]);
            }

            // dd("pls check later  ");
            foreach ($data['beneficiary'] as $value) {
                $full_name = $value->surname . " " . $value->first_name . " " . $value->othernames;

                $claimAccountNo = $value->claimAccountNo ?? null;
                $claimBankId = $value->claimBankId ?? null;
                $claimBankSortCode = $value->claimBankSortCode ?? null;


                // dd($claimAccountNo);

                if ($request['amount' . $value->selectedID] != 0 and $request['amount' . $value->selectedID] != '') {
                    DB::table('tblvoucherBeneficiary')->insert(array(
                        'beneficiaryDetails'        => $full_name,
                        'amount'                    => $request['amount' . $value->selectedID],
                        'voucherID'                 => $vid,

                        'bankID'                    => $claimBankId ? $claimBankId : $value->bankID,
                        'accountNo'                 => $claimAccountNo ? $claimAccountNo : $value->AccNo,

                        // 'bankID'                    => $value->bankID,
                        // 'accountNo'                 => $value->AccNo,
                        // 'sort_code'              => $value->sort_code,
                        'sort_code'                 => "ChangeLater",

                        'claimid'                   => $rawConDetails->claimid,
                        'claim_selected_staff'      => $value->selectedID,
                        'remarks'                   => $value->remarks,
                        'fileNo'                    => $value->fileNo
                    ));

                    //check if contractDetail has uniqueCode then go to overtime trial and remove the record
                    if($contractInfo->overtuniqueCode != ''){
                        DB::table('overtime_trial')->where('overtime_trial.uniqueCode', '=', $contractInfo->overtuniqueCode)->delete();
                    }
                }
            }
            $taskscheduled = $this->UpdateAlertTable("", '', '', '', 'tblcontractDetails', $rawConDetails->ID, 0);
            $taskscheduled = $this->UpdateAlertTable("Voucher Clearance", 'occheckby/voucher', '0', 'OC', 'tblpaymentTransaction', $vid, 1);
            $name = Auth::user()->name;
            $this->addLogg("Voucher with id: $vid created by $name", "New voucher created");
            return back()->with('message', 'Voucher created successfully');
        }

        $data['amtpayable'] = $this->ContractBalance($data['contractid']);
        $claimid = $rawConDetails->claimid;
        if (DB::select("SELECT sum(`amount`)  as sumt FROM `tblvoucherBeneficiary` WHERE `claimid`='$claimid'")[0]->sumt == DB::select("SELECT sum(`staffamount`)as sumt FROM `tblselectedstaffclaim` WHERE `claimID`='$claimid'")[0]->sumt) {
            DB::table('tblcontractDetails')->where('ID', $rawConDetails->ID)->update(['openclose' => 0, 'approvalStatus' => 1]);
        } else {
            DB::table('tblcontractDetails')->where('ID', $rawConDetails->ID)->update(['openclose' => 1]);
        }

        $data['beneficiary'] = $this->ClaimBenefeciary($rawConDetails->claimid);
        // dd($data['beneficiary']);
        $data['voucherlist']  = $this->ContractVoucherList($data['contractid']);
        //dd($data['voucherlist']);
        $data['contractValue'] = $rawConDetails->contractValue;
        $data['claimdetails'] = $rawConDetails->ContractDescriptions;
        $data['econocodeList'] = $this->StaffEconomicsCode();
        return view('funds.CreateContract.staffvoucher', $data);
    }

    public function statffVoucherNoBeneficiary(Request $request, $contractid = "")
    {
        // dd("pls check later");
        if ($request['todayDate'] == '') {
            $request['todayDate'] = date('Y-m-d');
        }

        if (Session::get('special') == 1)     return redirect('/voucher/continue-special/$contractid');
        $data['todayDate'] = $request['todayDate'];
        $data['warning']        = '';
        $data['success']        = '';
        $data['error'] = "";
        $data['contractid']        = $request['contractid'];
        $data['details']        = $request['details'];
        $data['contractid']        = $contractid;
        $data['economiccode']        = $request['economiccode'];

        $rawConDetails = $this->ContractDetails($data['contractid']);
        if (!$rawConDetails) {
            $data['error'] = "Go back and select a valid contract";
            return redirect('/raise/voucher');
        }

        if (!$rawConDetails->companyID == 13) {
            $data['error'] = "Go back and select a valid staffclaim";
            return redirect('/raise/voucher');
        }

        $clmid = $rawConDetails->claimid;
        $is_advance = ($rawConDetails->awaitingActionby == 'AD') ? 1 : 0;

        if ($rawConDetails->awaitingActionby == 'HS') {
            $is_advance = 3;
        }

        $data['transID'] = '';
        $data['claimid'] = '';
        $bene_sum = DB::select("SELECT  sum(`staffamount`) as tsum FROM `tblselectedstaffclaim` WHERE `claimID`='$clmid'")[0]->tsum;
        if (isset($_POST['delete'])) {

            $this->validate($request, [
                'vid'         => 'required',
            ], [], [
                'vid'         => 'Selected voucher',
            ]);
            $vid = $request['vid'];
            //DB::DELETE("DELETE FROM `tblpaymentTransaction` WHERE `ID`='$vid'");

            DB::table('tblpaymentTransaction')->where('ID', $vid)
                ->update([
                    'totalPayment'      => 0,
                    'amtPayable'        => 0,
                    'is_restore'        => 1,
                ]);
            DB::DELETE("DELETE FROM `tblvoucherBeneficiary` WHERE `voucherID`='$vid'");
        }

        if (isset($_POST['continue'])) {
            $this->validate($request, [
                'economiccode'         => 'required',
                'details'         => 'required',
            ], [], [
                'economiccode'          => 'Economic Code',
                'details'               => 'Payment Details',
            ]);

            $sumtotal = 0;
            $selectedStaffClaimTotal = 0;

            $Ecodetails = $this->Ecodetails($data['economiccode']);
            //dd($Ecodetails);
            if (!$Ecodetails) return back()->with('error', 'The economic code you selected is not valid! Pls try again');

            $Ecodetails = $Ecodetails[0];


            //pitoff
            $getAwaitActionBy = DB::table('tblcontractDetails')->where('ID', $contractid)->value('awaitingActionby');
            if ($getAwaitActionBy == 'HC') {
                $getAwaitActionBy = 2;
            } elseif ($getAwaitActionBy == 'HEC') {
                $getAwaitActionBy = 1;
            } elseif ($getAwaitActionBy == 'HAUD') {
                $getAwaitActionBy = 3;
            } elseif ($getAwaitActionBy == 'HCPO') {
                $getAwaitActionBy = 4;
            } else {
                $getAwaitActionBy = -1;
            }
            //   dd($getAwaitActionBy);
            //pitoff

            $contractInfo = DB::table('tblcontractDetails')->where('ID', $contractid)->first();
            if ($contractInfo->contract_Type == 4) {
                $contractTypeInFileNo =  "SCN/OC/CAP/";
            }
            if ($contractInfo->contract_Type == 1) {
                if($contractInfo->awaitingActionby == 'AD'){
                    $contractTypeInFileNo = "SCN/ADV/";
                }else{
                    $contractTypeInFileNo = "SCN/OC/";
                }
            }
            $nextVoucherNo = $this->VnextNo($Ecodetails->contractGroupID);
            $vid = DB::table('tblpaymentTransaction')->where('contractID', $rawConDetails->ID)->where('is_restore', 1)->value('ID');
            if ($vid) {
                DB::table('tblpaymentTransaction')->where('ID', $vid)
                    ->update([
                        'contractTypeID'        => $Ecodetails->contractGroupID,
                        'contractID'            => $rawConDetails->ID,
                        'companyID'         => 13,
                        'FileNo'         => $rawConDetails->fileNo,
                        'totalPayment'          => $rawConDetails->contractValue,
                        'paymentDescription'        => $data['details'],
                        'VAT'               => 0,
                        'VATValue'          => 0,
                        'WHT'               => 0,
                        'WHTValue'          => 0,
                        'VATPayeeID'            => 0, //$tblvatpayeeid, //
                        'WHTPayeeID'            => 0, //$tblwhtpayeeid, //
                        'amtPayable'            => $rawConDetails->contractValue,
                        'preparedBy'            => Auth::user()->id,
                        'allocationType'        => $Ecodetails->allocationID,
                        'economicCodeID'        => $data['economiccode'],
                        'status'                => 0,
                        'vstage'                => -1,
                        'is_restore'        => 0,
                        'is_advances'        => $is_advance,
                        'datePrepared'          => $request['todayDate'],
                        'period'        => $this->NewActivePeriod($Ecodetails->contractGroupID),
                        //'vref_no'          => $this->VnextNo(),
                        'payment_beneficiary' => $rawConDetails->beneficiary
                    ]);
            } else {
                 
                $vid = DB::table('tblpaymentTransaction')
                    ->insertGetId([
                        'contractTypeID'        => $Ecodetails->contractGroupID,
                        'contractID'            => $rawConDetails->ID,
                        'companyID'         => 13,
                        'FileNo'         => $rawConDetails->fileNo,
                        'totalPayment'          => $rawConDetails->contractValue,
                        'paymentDescription'        => $data['details'],
                        'VAT'               => 0,
                        'VATValue'          => 0,
                        'WHT'               => 0,
                        'WHTValue'          => 0,
                        'VATPayeeID'            => 0, //$tblvatpayeeid, //
                        'WHTPayeeID'            => 0, //$tblwhtpayeeid, //
                        'amtPayable'            => $rawConDetails->contractValue,
                        'preparedBy'            => Auth::user()->id,
                        'allocationType'        => $Ecodetails->allocationID,
                        'economicCodeID'        => $data['economiccode'],
                        'status'                => 0,
                        'vstage'                => $getAwaitActionBy,
                        'is_advances'        => $is_advance,
                        'datePrepared'          => $request['todayDate'],
                        'period'        => $this->NewActivePeriod($Ecodetails->contractGroupID),
                        // 'vref_no'          => $this->VnextNo($Ecodetails->contractGroupID),
                        'vref_no'          => $nextVoucherNo,
                        'voucherFileNo'      => $contractTypeInFileNo."".$nextVoucherNo."/".date('Y'),
                        'payment_beneficiary' => $rawConDetails->beneficiary
                    ]);
            }
            $taskscheduled = $this->UpdateAlertTable("", '', '', '', 'tblcontractDetails', $rawConDetails->ID, 0);
            $taskscheduled = $this->UpdateAlertTable("Voucher Clearance", 'occheckby/voucher', '0', 'OC', 'tblpaymentTransaction', $vid, 1);
            $name = Auth::user()->name;
            $this->addLogg("Voucher with id: $vid created by $name", "New voucher created");
            return back()->with('message', 'Voucher created successfully');
        }

        $vtpayment = DB::table('tblpaymentTransaction')->where('contractID', $rawConDetails->ID)->value('ID');
        $data['transID'] = $vtpayment;
        $data['claimid'] = $rawConDetails->ID;
        
        $data['amtpayable'] = $this->ContractBalance($data['contractid']);

        DB::table('tblcontractDetails')->where('ID', $rawConDetails->ID)->update(['openclose' => 0, 'approvalStatus' => 1]);

        $data['beneficiary'] = $this->ClaimBenefeciary($rawConDetails->claimid);
        // dd($data['beneficiary']);
        $data['voucherlist']  = $this->ContractVoucherList($data['contractid']);
        //dd($data['voucherlist']);
        $data['contractValue'] = $rawConDetails->contractValue;
        $data['claimdetails'] = $rawConDetails->ContractDescriptions;
        $data['econocodeList'] = $this->StaffEconomicsCode();
        return view('funds.CreateContract.staffvoucher', $data);
    }

    public function statffVoucherSpecial(Request $request, $contractid = "")
    {


        if ($request['todayDate'] == '') {
            $request['todayDate'] = date('Y-m-d');
        }
        $data['todayDate'] = $request['todayDate'];
        $data['warning']        = '';
        $data['success']        = '';
        $data['error'] = "";
        $data['contractid']        = $request['contractid'];
        $data['details']        = $request['details'];
        $data['contractid']        = $contractid;
        $data['economiccode']        = $request['economiccode'];

        $rawConDetails = $this->ContractDetails($data['contractid']);
        if (!$rawConDetails) {
            $data['error'] = "Go back and select a valid contract";
            return redirect('/raise/voucher');
        }
        if (!$rawConDetails->companyID == 13) {
            $data['error'] = "Go back and select a valid staffclaim";
            return redirect('/raise/voucher');
        }
        $clmid = $rawConDetails->claimid;
        $is_advance = ($rawConDetails->awaitingActionby == 'AD') ? 1 : 0;
        if ($rawConDetails->awaitingActionby == 'HS') {
            $is_advance = 3;
        }
        $bene_sum = DB::select("SELECT  sum(`staffamount`) as tsum FROM `tblselectedstaffclaim` WHERE `claimID`='$clmid'")[0]->tsum;
        if (round($bene_sum, 2) < round($rawConDetails->contractValue, 2)) return  redirect('create/procurement-staff-beneficiary/' . $rawConDetails->ID);
        if (isset($_POST['delete'])) {

            $this->validate($request, [
                'vid'         => 'required',
            ], [], [
                'vid'         => 'Selected voucher',
            ]);
            $vid = $request['vid'];
            DB::table('tblpaymentTransaction')->where('ID', $vid)
                ->update([
                    'totalPayment'      => 0,
                    'amtPayable'        => 0,
                    'is_restore'        => 1,
                ]);
            DB::DELETE("DELETE FROM `tblvoucherBeneficiary` WHERE `voucherID`='$vid'");
        }
        if (isset($_POST['continue'])) {
            $this->validate($request, [
                'economiccode'         => 'required',
                'details'         => 'required',
            ], [], [
                'economiccode'          => 'Economic Code',
                'details'               => 'Payment Details',
            ]);
            $data['beneficiary'] = $this->ClaimBenefeciary($rawConDetails->claimid);
            $sumtotal = 0;
            $selectedStaffClaimTotal = 0;
            foreach ($data['beneficiary'] as $value) {
                if ($request['amount' . $value->selectedID] == '') $request['amount' . $value->selectedID] = 0;
                if ($request['amount' . $value->selectedID] != 0) {
                    if (!is_numeric($request['amount' . $value->selectedID])) {
                        $msg = "This action cannot be completed because of invalid input " . $request['amount' . $value->selectedID] .  " corresponding to " . $value->full_name;
                        return back()->with('error', $msg);
                    }
                    if (round($request['amount' . $value->selectedID], 2) > round($value->amtpending, 2)) {
                        $msg = "This action cannot be completed because " . $request['amount' . $value->selectedID] . " is greater thanfff " . $value->amtpending . " in record corresponding to " . $value->full_name;
                        return back()->with('error', $msg);
                    }

                    if ($request['amount' . $value->selectedID] > 0) $selectedStaffClaimTotal += 1;
                    if ($selectedStaffClaimTotal == 1) $selectedStaffClaimFullName = $value->full_name;
                    $sumtotal += $request['amount' . $value->selectedID];
                }
            }
            $Ecodetails = $this->Ecodetails($data['economiccode']);
            //dd($Ecodetails);
            if (!$Ecodetails) return back()->with('error', 'The economic code you selected is not valid! Pls try again');
            if ($sumtotal == 0) return back()->with('error', 'This action is not successful because the amount passed for the voucher');

            $Ecodetails = $Ecodetails[0];

            if ($selectedStaffClaimTotal == 1) {
                $beneficiary = $selectedStaffClaimFullName;
            } else if ($selectedStaffClaimTotal == 2) {
                $beneficiary = $selectedStaffClaimFullName . " and 1 other";
            } else if ($selectedStaffClaimTotal > 2) {
                $beneficiary = $selectedStaffClaimFullName . " and " . ($selectedStaffClaimTotal - 1) . " others";
            } else {
                //save code: in case
                $beneficiary = "";
            }

            $vid = DB::table('tblpaymentTransaction')->where('contractID', $rawConDetails->ID)->where('is_restore', 1)->value('ID');
            if ($vid) {
                DB::table('tblpaymentTransaction')->where('ID', $vid)
                    ->update([
                        'contractTypeID'        => $Ecodetails->contractGroupID,
                        'contractID'            => $rawConDetails->ID,
                        'companyID'         => 13,
                        'FileNo'         => $rawConDetails->fileNo,
                        'totalPayment'          => $sumtotal,
                        'paymentDescription'        => $data['details'],
                        'VAT'               => 0,
                        'VATValue'          => 0,
                        'WHT'               => 0,
                        'WHTValue'          => 0,
                        'VATPayeeID'            => 0, //$tblvatpayeeid, //
                        'WHTPayeeID'            => 0, //$tblwhtpayeeid, //
                        'amtPayable'            => $sumtotal,
                        'preparedBy'            => Auth::user()->id,
                        'allocationType'        => $Ecodetails->allocationID,
                        'economicCodeID'        => $data['economiccode'],
                        'status'                => 0,
                        'vstage'                => 1,
                        'is_restore'        => 0,
                        'is_special'        => 1,
                        'is_advances'        => $is_advance,
                        'datePrepared'          => $request['todayDate'],
                        'period'        => $this->NewActivePeriod($Ecodetails->contractGroupID),
                        //'vref_no'          => $this->VnextNo(),
                        'payment_beneficiary' => $beneficiary
                    ]);
            } else {
                $vid = DB::table('tblpaymentTransaction')
                    ->insertGetId([
                        'contractTypeID'        => $Ecodetails->contractGroupID,
                        'contractID'            => $rawConDetails->ID,
                        'companyID'         => 13,
                        'FileNo'         => $rawConDetails->fileNo,
                        'totalPayment'          => $sumtotal,
                        'paymentDescription'        => $data['details'],
                        'VAT'               => 0,
                        'VATValue'          => 0,
                        'WHT'               => 0,
                        'WHTValue'          => 0,
                        'VATPayeeID'            => 0, //$tblvatpayeeid, //
                        'WHTPayeeID'            => 0, //$tblwhtpayeeid, //
                        'amtPayable'            => $sumtotal,
                        'preparedBy'            => Auth::user()->id,
                        'allocationType'        => $Ecodetails->allocationID,
                        'economicCodeID'        => $data['economiccode'],
                        'status'                => 0,
                        'vstage'                => 1,
                        'is_special'        => 1,
                        'is_advances'        => $is_advance,
                        'datePrepared'          => $request['todayDate'],
                        'period'        => $this->NewActivePeriod($Ecodetails->contractGroupID),
                        'vref_no'          => $this->VnextNo($Ecodetails->contractGroupID),
                        'payment_beneficiary' => $beneficiary
                    ]);
            }
            foreach ($data['beneficiary'] as $value) {
                if ($request['amount' . $value->selectedID] != 0 and $request['amount' . $value->selectedID] != '') {
                    DB::table('tblvoucherBeneficiary')->insert(array(
                        'beneficiaryDetails'         => $value->full_name,
                        'amount'         => $request['amount' . $value->selectedID],
                        'bankID'         => $value->bankID,
                        'accountNo'         => $value->account_no,
                        'voucherID'         => $vid,
                        'sort_code'         => $value->sort_code,
                        'claimid'         => $rawConDetails->claimid,
                        'claim_selected_staff' => $value->selectedID,
                        'remarks' => $value->remarks
                    ));
                }
            }
            $taskscheduled = $this->UpdateAlertTable("", '', '', '', 'tblcontractDetails', $rawConDetails->ID, 0);
            $taskscheduled = $this->UpdateAlertTable("Voucher Clearance", 'occheckby/voucher', '0', 'OC', 'tblpaymentTransaction', $vid, 1);
            $name = Auth::user()->name;
            $this->addLogg("Voucher with id: $vid created by $name", "New voucher created");
            $claimid = $rawConDetails->claimid;
            if (DB::select("SELECT sum(`amount`)  as sumt FROM `tblvoucherBeneficiary` WHERE `claimid`='$claimid'")[0]->sumt == DB::select("SELECT sum(`staffamount`)as sumt FROM `tblselectedstaffclaim` WHERE `claimID`='$claimid'")[0]->sumt) {
                DB::table('tblcontractDetails')->where('ID', $rawConDetails->ID)->update(['openclose' => 0]);
            } else {
                DB::table('tblcontractDetails')->where('ID', $rawConDetails->ID)->update(['openclose' => 1]);
            }
            return back()->with('message', 'Voucher created successfully');
        }

        $data['amtpayable'] = $this->ContractBalance($data['contractid']);
        $claimid = $rawConDetails->claimid;


        $data['beneficiary'] = $this->ClaimBenefeciary($rawConDetails->claimid);
        //dd($data['beneficiary']);
        $data['voucherlist']  = $this->ContractVoucherList($data['contractid']);
        //dd($data['voucherlist']);
        $data['contractValue'] = $rawConDetails->contractValue;
        $data['claimdetails'] = $rawConDetails->ContractDescriptions;
        $data['econocodeList'] = $this->StaffEconomicsCode();
        return view('funds.CreateContract.staffvoucher', $data);
    }

    public function continuSpecial(Request $request, $selectedid = "")
    {
        if ($request['todayDate'] == '') {
            $request['todayDate'] = date('Y-m-d');
        }

        $data['warning']        = '';
        $data['success']        = '';
        $data['error'] = "";
        $request['selectedid'] = $selectedid;
        if ($selectedid == "") {
            $data['error'] = "Go back and select a contract";
            return back();
        }
        $rawConDetails = $this->ContractDetails($request['selectedid']);
        if (!$rawConDetails) {
            $data['error'] = "Go back and select a valid contract";
            return back();
        }
        if ($rawConDetails->companyID == 13) {
            $data['error'] = "Go back and select a valid staffclaim";
            return redirect("/create/staff-voucher-special/" . $rawConDetails->ID);
        }

        $data['fileattach'] = $this->ContractAttachment($request['selectedid']);

        if ($request['amtpayable'] == '') {
            $request['amtpayable'] = $this->ContractBalance($request['selectedid']);
        }
        $data['ecogroup'] = $rawConDetails->contract_Type;
        $data['FileNo'] = $rawConDetails->fileNo;
        $data['ecogrouptext'] = $rawConDetails->EcoGroup;
        $data['contractID'] = $rawConDetails->ID;
        $data['selected'] = $selectedid;
        $data['contracttype'] = $request['contracttype'];

        if ($request['contracttype'] == '') {
            $data['contracttype'] = $data['ecogroup'];
        }

        $request['contracttype2'] = $rawConDetails->contract_Type;
        $data['getBalance2'] = $request['amount'];
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
        $data['vatwhttable']        = DB::table('tblVATWHTPayee')->where('payee_status', 1)->get();
        $data['vatpayee']           = $request['vatPayee'];
        $data['whtpayee']           = $request['whtPayee'];
        $data['liabilityby']        = DB::table('users')->get();
        $data['vatpas']             = $request['vatselect'];
        $data['vatvas']         = $request['vat'];
        $data['whtpas']         = $request['whtOrTax'];
        $data['stampduty']     = $request['stampduty'];
        $data['prempercentage']     = $request['prempercentage'];
        $data['whtvas']         = $request['tax'];
        $data['prem']     = $request['prem'];
        $data['stampdutyval']     = $request['stampdutyval'];
        $data['amtpayble']      = $request['amtpayable'];
        $data['narration']      = $request['narration'];
        $data['liabilityByas']  = $request['liabilityBy'];
        $data['todayDateas']    = $request['todayDate'];
        $data['vatpayeeas']     = $request['vatPayeeID'];
        $data['whtpayeeas']     = $request['whtPayeeID'];
        $data['vatpaddas']      = $request['vatPayeeAddress'];
        $data['whtpaddas']      = $request['whtPayeeAddress'];
        $data['filenoas']       = $request['fileno'];
        $data['currentuser'] = Auth::user()->username;
        $data['econocode'] = $this->getEconomicCode(5, $data['contracttype']);
        $data['econocode2'] = $this->getEconomicCode(5, $data['contracttype']);
        $data['instructions'] = "";

        $data['getBalance'] = (int) ($request['selectedid'] != "") ? $this->ContractBalance($request['selectedid']) : "";





        $details = $this->getInfo($request['selectedid']);
        if ($details->companyID == 13) {
            $data['contractor']     = $details->beneficiary;
            $claimid = $details->claimid;
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

        $data['sel_id'] = $request['selectedid'];
        $data['file_ex'] = $details->file_ex;




        $data['selectedid'] = $request['selectedid'];
        $data['companyid']  = $request['companyid'];
        $data['getBalanceas'] = $request['amount'];
        if ($request['finalsubmit'] == "complete") {
            if (round(floatval($this->ContractBalance($request['selectedid'])), 2) < round(floatval($request['totalamount']), 2))  return back()->with('error', 'It seem this voucher have already been created');
            $tblcontractid      = $request['selectedid'];
            $tblcompanyid       = $request['companyid'];
            $tbltotalpayment    = $request['amount'];
            $tblpaymentDesc     = $request['paymentdesc'];
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
            $data['vatpas'] = $request['vatselect'];
            $data['vatvas'] = $request['vat'];
            $data['whtpas'] = $request['whtOrTax'];
            $data['stampduty']     = $request['stampduty'];
            $data['prempercentage']     = $request['prempercentage'];
            $data['whtvas'] = $request['tax'];
            $data['stampdutyval']     = $request['stampdutyval'];
            $data['prem']     = $request['prem'];

            if ($request['amtpayable'] == '') {
                $request['amtpayable'] = $this->ContractBalance($request['selectedid']);
            }
            $data['amtpayble'] = $request['amtpayable'];
            $data['narration'] = $request['narration'];
            $data['pvnoas'] = $request['pvno'];
            $data['liabilityByas'] = $request['liabilityBy'];
            $data['todayDateas'] = $request['todayDate'];
            $data['vatpayeeas'] = $request['vatPayeeID'];
            $data['whtpayeeas'] = $request['whtPayeeID'];
            $data['vatpaddas'] = $request['vatPayeeAddress'];
            $data['whtpaddas'] = $request['whtPayeeAddress'];
            $data['filenoas'] = $request['filenoas'];
            $data['getBalanceas'] = $request['amount'];
            $request['economiccodeid'] = $economiccodeid;

            $validating = $this->validate($request, [
                'allocationtype1'       => 'required',
                'totalamount'           => 'required',
                'narration'             => 'required',
                'amtpayable'            => 'required',
                'preparedBy'            => 'required',
                'vatPayeeID'            => 'required_unless:vatselect,0',
                'whtPayeeID'            => 'required_unless:whtOrTax,0',
                'economiccodeid'         => 'required',
                'todayDate'             => 'required'
            ], [], [
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
            if ($request['vatPayeeID'] == "") {
                $request['vatPayeeID'] = 0;
            }
            if ($request['whtPayeeID'] == "") {
                $request['whtPayeeID'] = 0;
            }
            $data['getBalance2'] = $request['amount'];
            $premvalue = $tbltotalpayment * 0.01 * (is_numeric($request['prempercentage']) ? $request['prempercentage'] : 0);
            $paymentbal = $tbltotalpayment - $premvalue;
            $deno = ($vatperc) + 100;
            $vat1 = ($vatperc / $deno) * $paymentbal;
            $mockval = $paymentbal - $vat1;
            $tax1 = ($whtselect / 100) * $mockval;
            $vat = round($vat1, 2);
            $wht = round($tax1, 2);
            $fstampduty = round(($data['stampduty'] / 100) * $mockval, 2);
            $tblamtPayable =  $paymentbal - ($vat  +  $wht + $fstampduty);

            //get contract type in file no
            $contractInfo = DB::table('tblcontractDetails')->where('ID', $request['selectedid'])->first();
            if ($contractInfo->contract_Type == 4) {
                $contractTypeInFileNo =  "SCN/OC/CAP/";
            }
            if ($contractInfo->contract_Type == 1) {
                if($contractInfo->awaitingActionby == 'AD'){
                    $contractTypeInFileNo = "SCN/ADV/";
                }else{
                    $contractTypeInFileNo = "SCN/OC/";
                }
            }
            $nextVoucherNo = $this->VnextNo($data['contracttype']);
            //
            if ($vid = DB::table('tblpaymentTransaction')
                ->insertGetId([
                    'contractTypeID'        => $data['contracttype'],
                    'contractID'            => $tblcontractid,
                    'companyID'         => $tblcompanyid,
                    'FileNo'         => $data['FileNo'],
                    'totalPayment'          => $tbltotalpayment,
                    'premiumpercentage'          => is_numeric($request['prempercentage']) ? $request['prempercentage'] : 0,
                    'premiumcharge'          => $premvalue,
                    'paymentDescription'        => $narration,
                    'VAT'               => $vatperc,
                    'VATValue'          => $vat,
                    'WHT'               => $whtselect,
                    'WHTValue'          => $wht,
                    'VATPayeeID'            => $request['vatPayeeID'], //$tblvatpayeeid, //
                    'WHTPayeeID'            => $request['whtPayeeID'], //$tblwhtpayeeid, //
                    'stampdutypercentage'   => $data['stampduty'] != '' ? $data['stampduty'] : 0,
                    'stampduty'             => $fstampduty,
                    'amtPayable'            => $tblamtPayable,
                    'preparedBy'            => Auth::user()->id,
                    'allocationType'        => $allocationtype,
                    'economicCodeID'        => $economiccodeid,
                    'status'                => 0,
                    'vstage'                => 1,
                    'is_special'                => 1,
                    'datePrepared'          => $dateprepared,
                    // 'vref_no'          => $this->VnextNo($data['contracttype']),
                    'vref_no'          => $nextVoucherNo,
                    'voucherFileNo'      => $contractTypeInFileNo."".$nextVoucherNo."/".date('Y'),
                    'period'        => $this->NewActivePeriod($data['contracttype'])
                ])
            ) {
                DB::table('tblcontractDetails')->where('ID', $tblcontractid)->update([
                    'openclose' => 0,
                    'paymentStatus' => 1
                ]);
                if (DB::table('tblcontractDetails')->where('ID', $tblcontractid)->first()->paymentStatus == "") {
                    DB::table('tblcontractDetails')->where('ID', $tblcontractid)->update([
                        'paymentStatus' => 0
                    ]);
                }
                if (DB::table('tblcontractDetails')->where('ID', $tblcontractid)->first()->economicVoult == "") {
                    DB::table('tblcontractDetails')->where('ID', $tblcontractid)->update([
                        'economicVoult' => $economiccodeid,
                        'contract_Type' => $data['contracttype']
                    ]);
                }

                $name = Auth::user()->name;
                $taskscheduled = $this->UpdateAlertTable("Voucher Clearance", 'occheckby/voucher', '0', 'OC', 'tblpaymentTransaction', $vid, 1);
                $taskscheduled = $this->UpdateAlertTable("", '', '', '', 'tblcontractDetails', $tblcontractid, 0);
                $this->addLogg("Voucher with id: $vid created by $name", "New voucher created");
                return redirect('display/voucher/' . $vid);
            } else {
                return back()->with('error', 'Something went wrong');
            }
            if ($this->VoultBalance($economiccodeid) > $tblamtPayable) {
                $gross = $data['getBalance'];
                if ($gross < $request['amount']) {
                    return back()->with('error', 'Gross amount cannot be greater than Total Sum (Contract Value)!');
                } else {
                }
            }
        }
        $data['ECONOMAIN'] = $this->getEconomicCode(5, $data['contracttype']);
        return view('funds.CreateContract.continue', $data);
    }
    public function DriverTour(Request $request, $vid = null)
    {
        //dd("Module in progress pls check later");
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $request['amount']         = preg_replace('/[^\d.]/', '', $request['amount']);
        $data['amount'] = $request['amount'];
        $data['departure_place'] = $request['departure_place'];
        $data['arrival_place'] = $request['arrival_place'];
        $data['nature_of_duty'] = $request['nature_of_duty'];
        $request['vid'] = $vid;
        $data['vid'] = $vid;
        $data['ID'] = '';
        $Vinfo = DB::table('tblpaymentTransaction')->where('ID', $vid)->first();
        if (!$Vinfo) return back()->with('err', 'This operation cannot be performed. Please try again');
        if ($Vinfo) {
            $data['totalclaim'] = $Vinfo->totalPayment;
            $data['ID'] = $Vinfo->ID;
            $data['Vinfo'] = $Vinfo;
        }

        $bene_sum = DB::select("SELECT  sum(`amount`) as tsum FROM `tbldrivertour` WHERE `voucherId`='$vid'")[0]->tsum;
        if (isset($_POST['add'])) {
            $this->validate($request, ['vid' => 'required', 'departure_place' => 'required', 'arrival_place' => 'required', 'nature_of_duty' => 'required', 'amount' => 'required|numeric']);
            if (round($data['totalclaim'], 2) < (round($bene_sum, 2) + round($data['amount'], 2))) return back()->with('err', 'This operation cannot be performed because the total sum will exceed approved value.');
            DB::table('tbldrivertour')->insert([
                'voucherId' => $data['vid'],
                'departure_place' =>  $data['departure_place'],
                'arrival_place' =>  $data['amount'],
                'nature_of_duty' =>  $data['nature_of_duty'],
                'amount' =>  $data['amount'],

            ]);
            return  back();
        }
        if (isset($_POST['updateold'])) {
            $this->validate($request, ['beneid' => 'required', 'amount' => 'required|numeric']);
            $prev_val = DB::table('tblselectedstaffclaim')->where('selectedID', $request['beneid'])->value('staffamount');
            if (round($data['totalclaim'], 2) < (round($bene_sum, 2) + round($data['amount'], 2) - round($prev_val, 2))) return back()->with('err', 'This operation cannot be performed because the total sum will exceed approved value.');
            DB::table('tblselectedstaffclaim')->where('selectedID', $request['beneid'])->update([
                'staffamount' =>  $data['amount'],
            ]);
            return  redirect('create/procurement-staff-beneficiary/' . $claiminfo->ID)->with('message', 'successfully modified.');
        }
        if (isset($_POST['delete'])) {
            $this->validate($request, ['beneid' => 'required']);
            DB::table('tbldrivertour')->where('id', $request['beneid'])->delete();
            return  back()->with('message', 'successfully removed.');
        }

        $data['Tourdetail'] = DB::table('tbldrivertour')->where('voucherId', $vid)->get();
        return view('CreateContract.drivertour', $data);
    }
}
