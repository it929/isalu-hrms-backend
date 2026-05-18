<?php

namespace App\Http\Controllers\funds;



use Illuminate\Http\Request;
use App\Http\Controllers\funds\function24Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Exports\CbnEpaymentExport;
use Maatwebsite\Excel\Facades\Excel;

class CPOController extends BasefunctionController
{
    private $instanceFunction24;

    public function __construct()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '0');
        $this->instanceFunction24 = new function24Controller;
    }

    public function index()
    {
        //
    }

    public function NewBatchNo()
    {

        $myData = DB::Select("SELECT max(`batch`) as BTN FROM `tblepayment`");
        //return $myData[0]->BTN;
        if ($myData[0]->BTN == '') {
            return 'BTN00000001';
        }

        $BTN1 = $myData[0]->BTN;
        $arr = explode("BTN", $BTN1);

        $newcode = $arr[1] + 1;
        while (strlen($newcode) < 8) {
            $newcode = "0" . $newcode;
        }
        return 'BTN' . $newcode;
    }

    public function NewBatchNoByContractType19022026($contractType, $bank_abbr)
    {
        $year = date('Y');

        // Determine Contract Type Abbreviation
        switch ($contractType) {
            case 1:
                $contractTypeAbrv = 'REC';
                break;
            case 4:
                $contractTypeAbrv = 'CAP';
                break;
            case 5:
                $contractTypeAbrv = 'LPPC';
                break;
            case 6:
                $contractTypeAbrv = 'PE';
                break;
            default:
                $contractTypeAbrv = 'CPO';
        }

        // Find last batch for this year + contract type
        $lastBatch = DB::table('tblepayment')
            ->where('contract_typeID', $contractType)
            ->whereYear('date', $year) // Make sure you have created_at column
            ->where('batch', 'like', "SCN-$year-%-$contractTypeAbrv-%")
            ->orderBy('id', 'desc')
            ->value('batch');

        if (!$lastBatch) {
            $newNumber = 1;
        } else {
            // Extract last number
            $parts = explode('-', $lastBatch);
            $lastNumber = (int) end($parts);
            $newNumber = $lastNumber + 1;
        }

        $formattedNumber = str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        return "SCN-$year-$bank_abbr-$contractTypeAbrv-$formattedNumber";
    }

    public function NewBatchNoByContractType($contractType, $bank_abbr)
    {
        $year = date('Y');

        // Contract Type Abbreviation
        switch ($contractType) {
            case 1:
                $contractTypeAbrv = 'REC';
                $defaultStart = 208; // 🔥 REC starts from 31
                break;
            case 4:
                $contractTypeAbrv = 'CAP';
                $defaultStart = 73;
                break;
            case 5:
                $contractTypeAbrv = 'LPPC';
                $defaultStart = 1;
                break;
            case 6:
                $contractTypeAbrv = 'PE';
                $defaultStart = 1;
                break;
            default:
                $contractTypeAbrv = 'CPO';
                $defaultStart = 1;
        }

        // Get last batch number for this contract type
        $lastBatch = DB::table('tblepayment')
            ->where('contract_typeID', $contractType)
            ->whereYear('date', $year)
            ->where('batch', 'like', "SCN-$year-$bank_abbr-$contractTypeAbrv-%")
            ->orderBy('id', 'desc')
            ->value('batch');

        if (!$lastBatch) {
            $newNumber = $defaultStart; // 🔥 Use default start
        } else {
            $parts = explode('-', $lastBatch);
            $lastNumber = (int) end($parts);
            $newNumber = $lastNumber + 1;
        }

        $formattedNumber = str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        return "SCN-$year-$bank_abbr-$contractTypeAbrv-$formattedNumber";
    }



    public function mergedBatchNo()
    {

        $myData = DB::Select("SELECT max(`adjusted_batch`) as btc FROM `tblmerged_payment`");
        //return $myData[0]->BTN;
        if ($myData[0]->btc == '') {
            return 'btc000001';
        }

        $BTN1 = $myData[0]->btc;
        $arr = explode("btc", $BTN1);

        $newcode = $arr[1] + 1;
        while (strlen($newcode) < 6) {
            $newcode = "0" . $newcode;
        }
        return 'btc' . $newcode;
    }

    public function mergedBatchNoByContractType($contractType, $bank_abbr)
    {
        $year = date('Y');

        // Contract Type Abbreviation
        switch ($contractType) {
            case 1:
                $contractTypeAbrv = 'REC';
                $defaultStart = 1;
                break;
            case 4:
                $contractTypeAbrv = 'CAP';
                $defaultStart = 1;
                break;
            case 5:
                $contractTypeAbrv = 'LPPC';
                $defaultStart = 1;
                break;
            case 6:
                $contractTypeAbrv = 'PE';
                $defaultStart = 1;
                break;
            default:
                $contractTypeAbrv = 'CPO';
                $defaultStart = 1;
        }

        // Get last adjusted batch number for this contract type
        $lastBatch = DB::table('tblmerged_payment')
            ->where('contract_typeID', $contractType)
            ->whereYear('date', $year)
            ->where('adjusted_batch', 'like', "SCN-$year-$bank_abbr-$contractTypeAbrv-%")
            ->orderBy('ID', 'desc')
            ->value('adjusted_batch');

        if (!$lastBatch) {
            $newNumber = $defaultStart; // 🔥 Use default start
        } else {
            $parts = explode('-', $lastBatch);
            $lastNumber = (int) end($parts);
            $newNumber = $lastNumber + 1;
        }

        $formattedNumber = str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        return "SCN-M$year-$bank_abbr-$contractTypeAbrv-$formattedNumber";
    }

    public function batchNo()
    {
        $row = DB::table('tblepayment')->orderBy('batch', 'desc')->first();
        $clent = 5;
        if ($row) {
            $data = $row->batch;
            $intc = strlen($data);
            $data = substr($data, $clent, ($intc - $clent));
            $count = $data + 1;
        } else {
            $count = 1;
        }
        $tempdata = "EP";
        $newcode = $tempdata . $count;

        $totalLength = 7;

        while (strlen($newcode) < $totalLength) {
            $tempdata = $tempdata . "0";
            $newcode = $tempdata . $count;
        }
        return $newcode;
    }

    public function auditVouchers()
    {
        $data['company'] = DB::table('tblcompany')->first();

        $data['audited'] = DB::table('tblpaymentTransaction')
            ->join('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
            ->join('tblcontractType', 'tblcontractType.id', '=', 'tblpaymentTransaction.contractTypeID')
            ->leftJoin('tblbanklist', 'tblcontractor.Banker', '=', 'tblbanklist.bankID')
            ->leftJoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
            ->leftJoin('tblclaim', 'tblclaim.ID', '=', 'tblcontractDetails.claimID')
            ->leftJoin('users', 'users.id', '=', 'tblpaymentTransaction.cpo_assign_userID')
            ->where('tblpaymentTransaction.auditStatus', '=', 1)
            ->where('tblpaymentTransaction.cpo_payment', '=', 0)
            ->where('tblpaymentTransaction.vstage', '=', 4)
            ->select('*', 'tblpaymentTransaction.ID as transID', 'tblcontractDetails.beneficiary as claimBene', 'users.name as assignedTo', 'tblclaim.ID as beneClaimID')
            ->orderBy('auditDate', 'Asc')
            ->paginate(100);
        // dd($data['audited']);
        $data['contractTypes'] = DB::table('tblcontractType')->get();

        $data['totalRows'] = count((array)$data['audited']);

        return view('funds/cpo/displayReport', $data);
    }

    public function batchToMerge()
    {

        $data['company'] = DB::table('tblcompany')->first();
        $data['codes'] = DB::table('tblaction_rank')->where('cont_payment_active', '=', 1)->get();
        $data['toMerge'] = DB::select('select *, sum(`amount`) as sum,sum(`VATValue`) as vsum,sum(`WHTValue`) as wsum from tblepayment group by batch');

        return view('funds.cpo.merge', $data);
    }

    /***************** MERGING **********************/


    public function merge(Request $request)
    {

        $ch          = $request['checkname'];
        $id          = $request['id'];
        if ($ch == '') {
            return back()->with('err', 'Please, Select at least one item');
        }
        // $mergebatch = $this->mergedBatchNo();
        // Session::put('mergedBatch', $mergebatch);

        $firstBatch = DB::table('tblepayment')
            ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblepayment.bank')
            ->where('batch', $ch[0])
            ->first();

        if (!$firstBatch) {
            return back()->with('err', 'Invalid batch selected');
        }

        $njcAcct = DB::table('tblmandate_address_account')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
            ->where('tblmandate_address_account.id', '=', $firstBatch->NJCAccount)
            ->first();

        $mergebatch = $this->mergedBatchNoByContractType(
            $firstBatch->contract_typeID,
            $njcAcct->bank_abbr
        );

        foreach ($ch as $key => $value) {

            $b = DB::table('tblepayment')
                ->where('batch', '=', $ch[$key])
                ->get();

            foreach ($b as $list) {
                DB::table('tblmerged_payment')
                    ->insert(array(
                        'transactionID'     => $list->transactionID,
                        'contractor'        => $list->contractor,
                        'amount'            => $list->amount,
                        'accountNo'         => $list->accountNo,
                        'bank'              => $list->bank,
                        'date'              => $list->date,
                        'batch'             => $list->batch,
                        'VATValue'            => $list->VATValue,
                        'WHTValue'            => $list->WHTValue,
                        'vat_bank'            => $list->vat_bank,
                        'vat_bank_branch'     => $list->vat_bank_branch,
                        'vat_accountNo'       => $list->vat_accountNo,
                        'wht_bank'            => $list->wht_bank,
                        'wht_bank_branch'     => $list->wht_bank_branch,
                        'wht_accountNo'       => $list->wht_accountNo,
                        'purpose'             => $list->purpose,
                        'vat_payee'            => $list->vat_payee,
                        'wht_payee'            => $list->wht_payee,
                        'bank_branch'            => $list->bank_branch,
                        'vat_sortcode'            => $list->vat_sortcode,
                        'wht_sortcode'            => $list->wht_sortcode,
                        'adjusted_batch'             => $mergebatch,
                        'accountName'             => $list->accountName,
                        'contract_typeID'         => $list->contract_typeID,
                        'NJCAccount'              => $list->NJCAccount,
                        'bank_sortcode'           => $list->bank_sortcode,
                        'tin'                     => $list->tin,
                        'stampduty'               => $list->stampduty

                    ));
            }

            $b = DB::table('tblepayment')
                ->where('batch', '=', $ch[$key])
                ->update([
                    'is_merged'      => 1,
                ]);
        }
        return redirect('/view/merge-payments/' . $mergebatch)->with('msg', 'Successfully Merged');
    }

    public function viewMerger($id = null)
    {
        $count = DB::table('tblmerged_payment')->where('mandate_status', '!=', 3)->where('batch', session('mergedBatch'))->count();

        //dd(session('mergedBatch') );
        if (session('mergedBatch') != '' && $count != 0) {
            $data['current_batch'] = session('mergedBatch');

            $data['company'] = DB::table('tblcompany')->first();
            $data['mandate'] = DB::table('tblmerged_payment')
                ->where('mandate_status', '!=', 3)
                ->where('adjusted_batch', session('mergedBatch'))
                ->selectRaw('*,sum(amount) as amount, sum(VATValue) as vat,sum(WHTValue) as wht')
                ->groupBy('accountNo')
                ->groupBy('contractor')
                ->get();
            $data['status'] = DB::table('tblmerged_payment')
                ->where('adjusted_batch', session('mergedBatch'))
                ->first();
            $data['checkApproval'] = DB::table('tblmerged_payment')
                ->where('adjusted_batch', session('mergedBatch'))
                ->where('mandate_status', 3)
                ->count();
            $data['sum'] = DB::table('tblmerged_payment')
                ->where('adjusted_batch', session('mergedBatch'))
                //->groupBy('adjusted_batch')
                ->sum('amount');

            $data['vatsum'] = DB::table('tblmerged_payment')
                ->where('batch', session('mergedBatch'))
                //->groupBy('adjusted_batch')
                ->sum('VATValue');
            $data['whtsum'] = DB::table('tblmerged_payment')
                ->where('adjusted_batch', session('mergedBatch'))
                //->groupBy('adjusted_batch')
                ->sum('WHTValue');
            $data['sigA'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'A')->orderBy('rank')->get();
            $data['sigB'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'B')->orderBy('rank')->get();
            $data['sig1'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'A')->where('batch', '=', session('batchNo'))->first();
            $data['sig2'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'B')->where('batch', '=', session('batchNo'))->first();

            $data['accountDetails'] =  DB::table('tblmandate_address_account')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                ->where('tblmandate_address_account.status', '=', 1)
                ->get();
            $data['accountAddress'] =  DB::table('tblmandate_address_account')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                ->where('tblmandate_address_account.status', '=', 1)
                ->first();

            $data['banks'] = DB::table('tblbanklist')->get();

            return view('funds/cpo/mergedEpayment', $data);
        } else {
            $data['current_batch'] = $id;
            $data['company'] = DB::table('tblcompany')->first();
            $data['mandate'] = DB::table('tblmerged_payment')
                ->where('mandate_status', '!=', 3)
                ->where('adjusted_batch', $id)
                ->selectRaw('*,sum(amount) as amount,sum(VATValue) as vat,sum(WHTValue) as wht')
                ->groupBy('accountNo')
                ->groupBy('contractor')
                ->get();
            $data['status'] = DB::table('tblmerged_payment')
                ->where('adjusted_batch', $id)
                ->first();
            $data['checkApproval'] = DB::table('tblmerged_payment')
                ->where('adjusted_batch', $id)
                ->where('mandate_status', 3)
                ->count();
            $data['sum'] = DB::table('tblmerged_payment')
                ->where('adjusted_batch', $id)
                //->groupBy('adjusted_batch')
                ->sum('amount');
            $data['vatsum'] = DB::table('tblmerged_payment')
                ->where('adjusted_batch', $id)
                //->groupBy('adjusted_batch')
                ->sum('VATValue');
            $data['whtsum'] = DB::table('tblmerged_payment')
                ->where('adjusted_batch', $id)
                //->groupBy('adjusted_batch')
                ->sum('WHTValue');

            $data['sigA'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'A')->orderBy('rank')->get();
            $data['sigB'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'B')->orderBy('rank')->get();
            $data['sig1'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'A')->where('batch', '=', $id)->first();
            $data['sig2'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'B')->where('batch', '=', $id)->first();

            $data['accountDetails'] =  DB::table('tblmandate_address_account')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                ->where('tblmandate_address_account.status', '=', 1)
                ->get();
            $data['accountAddress'] =  DB::table('tblmandate_address_account')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                ->where('tblmandate_address_account.status', '=', 1)
                ->first();

            $data['banks'] = DB::table('tblbanklist')->get();

            return view('funds/cpo/mergedEpayment', $data);
        }
    }


    public function mergedBatch()
    {

        $data['company'] = DB::table('tblcompany')->first();
        $data['codes'] = DB::table('tblaction_rank')->where('cont_payment_active', '=', 1)->get();
        $data['audited'] = DB::select('select *, sum(`amount`) as sum,sum(`VATValue`) as vsum,sum(`WHTValue`) as wsum from tblmerged_payment where  `date` >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) group by adjusted_batch');

        return view('funds.cpo.mergedBatch', $data);
    }

    public function postMergedBatch(Request $request)
    {
        //$fromdate            = date('Y-m-d', strtotime(trim($request['dateFrom'])));
        //$todate             = date('Y-m-d', strtotime(trim($request['dateTo'])));
        $data['codes'] = DB::table('tblaction_rank')->where('cont_payment_active', '=', 1)->get();
        $fromdate             = $request['dateFrom'];
        $todate               = $request['dateTo'];
        $batchToRestore       = $request['toRestore'];
        $btn = $request['submit'];
        $batch = $request['batch'];



        if ($btn == 'View') {
            $data['batch'] = DB::table('tblmerged_payment')->groupBy('batch')->get();
            /*$data['audited'] = DB::table('tblepayment')
         ->where('batch','=',$batch)
         ->get();*/
            if ($fromdate != '') {
                Session::put('from',  $fromdate);
            }
            Session::put('to',    $todate);
            if ($fromdate == '') {
                $data['audited'] = DB::table('tblmerged_payment')
                    ->where('date', $todate)
                    ->groupBy('batch')
                    ->selectRaw('*, sum(amount) as sum')
                    ->get();

                return view('funds.cpo.mergedBatch', $data);
            } else {
                $data['audited'] = DB::table('tblmerged_payment')
                    ->whereBetween('date', [$fromdate, $todate])
                    ->groupBy('batch')
                    ->selectRaw('*, sum(amount) as sum, sum(`VATValue`) as vsum,sum(`WHTValue`) as wsum ')
                    ->get();

                return view('funds.cpo.mergedBatch', $data);
            }
        } else if ($btn == 'Restore') {
            foreach ($batchToRestore as $key => $value) {
                DB::table('tblepayment')
                    ->where('batch', '=', $batchToRestore[$key])
                    ->update([
                        'is_merged'   => 0,
                    ]);

                DB::table('tblmerged_payment')->where('batch', '=', $batchToRestore[$key])->delete();
            }
            return redirect('/merged-batch/search')->with('msg', 'Process Completed');
        }
    }


    /*****************End Merging ******************/


    public function updateSelected(Request $request)
    {

        $ch          = $request['checkname'];
        $id             = $request['id'];

        if ($ch == '') {
            return back()->with('err', 'Please, Select at least one item');
        }

        foreach ($ch as $key => $value) {

            DB::table('tblpaymentTransaction')
                ->where('ID', '=', $ch[$key])
                ->update(array(
                    'cpo_payment'          => 1,

                    'cpo_payment_date'     => date('Y-m-d'),

                ));
        }
        return redirect('/cpo/generated');
    }

    public function payGenerated(Request $request)
    {
        $data['company'] = DB::table('tblcompany')->first();
        //if cpo head fetch all initiated payment transaction else fetch only cpo_assigned_userID
        // cpo head role id = 32
        if (DB::table('assign_user_role')->where('roleID', 32)->value('userID') == Auth::user()->id || Auth::user()->id == 6) {
            $data['audited'] = DB::table('tblpaymentTransaction')
                ->join('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
                ->leftJoin('tblbanklist', 'tblcontractor.Banker', '=', 'tblbanklist.bankID')
                ->leftJoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
                ->leftJoin('tblvoucherBeneficiary', 'tblvoucherBeneficiary.voucherID', '=', 'tblpaymentTransaction.ID')
                ->leftJoin('tblcontractType', 'tblcontractType.ID', 'tblpaymentTransaction.contractTypeID')
                ->where('auditStatus', '=', 1)
                ->where('cpo_payment', '=', 1)
                ->where('pay_confirmation', '=', 0)
                ->where('tblpaymentTransaction.status', '<>', 6)
                ->where('tblpaymentTransaction.confirm_for_mandate', 0)
                ->select('*', 'tblpaymentTransaction.ID as transID', 'tblcontractDetails.claimid as isClaimId', 'tblcontractDetails.beneficiary as claimBene', 'tblcontractType.contractType as epaymentCT', 'tblcontractDetails.ContractDescriptions as contDesc', 'tblbanklist.sortcode as bsortcode', DB::raw('COUNT(tblvoucherBeneficiary.voucherID) as beneficiary_count'))
                ->groupBy('tblpaymentTransaction.ID')
                ->get();
        } else {
            $data['audited'] = DB::table('tblpaymentTransaction')
                ->join('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
                ->leftJoin('tblbanklist', 'tblcontractor.Banker', '=', 'tblbanklist.bankID')
                ->leftJoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
                ->leftJoin('tblvoucherBeneficiary', 'tblvoucherBeneficiary.voucherID', '=', 'tblpaymentTransaction.ID')
                ->leftJoin('tblcontractType', 'tblcontractType.ID', 'tblpaymentTransaction.contractTypeID')
                ->where('auditStatus', '=', 1)
                ->where('cpo_payment', '=', 1)
                ->where('pay_confirmation', '=', 0)
                ->where('tblpaymentTransaction.status', '<>', 6)
                ->where('tblpaymentTransaction.confirm_for_mandate', 0)
                ->where('tblpaymentTransaction.cpo_assign_userID', Auth::user()->id)
                ->select('*', 'tblpaymentTransaction.ID as transID', 'tblcontractDetails.claimid as isClaimId', 'tblcontractDetails.beneficiary as claimBene', 'tblcontractType.contractType as epaymentCT', 'tblcontractDetails.ContractDescriptions as contDesc', 'tblbanklist.sortcode as bsortcode', DB::raw('COUNT(tblvoucherBeneficiary.voucherID) as beneficiary_count'))
                ->groupBy('tblpaymentTransaction.ID')
                ->get();
        }
        if (count($data['audited']) > 0) {
            $data['contractTypeBanks'] = DB::table('tblmandate_address_account')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                ->leftjoin('tblcontractType', 'tblcontractType.ID', 'tblmandate_address_account.contractTypeID')
                // ->where('tblmandate_address_account.contractTypeID', '=', $data['audited'][0]->contractTypeID)
                ->where('tblmandate_address_account.status', '=', 1)
                ->select('tblmandate_address_account.id', 'tblbanklist.bank', 'tblmandate_address_account.account_no', 'tblcontractType.contractType')
                ->get();
        }
        // dd($data);
        return view('funds/cpo/payGeneratedNew', $data);
    }

    public function StaffBenficiaryCPO(Request $request, $transId)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $data['staffid'] = $request['staffid'];
        $request->merge([
            'amount'           => str_replace(',', '', $request->amount),
        ]);
        $request['amount']         = preg_replace('/[^\d.]/', '', $request['amount']);
        $data['amount'] = $request['amount'];
        // $data['claimid'] = $request['claimid'];
        $cid = DB::table('tblpaymentTransaction')->where('ID', $transId)->value('contractID');
        $request['cid'] = $cid;
        $data['ID'] = '';
        // if (!$data['claimid']) 
        $data['claimid'] = DB::table('tblcontractDetails')->where('ID', $request['cid'])->value('claimid');
        // dd($data['claimid']);
        $data['totalclaim'] = 0;
        // dd($data['claimid']);
        $claiminfo = DB::table('tblcontractDetails')->where('claimid', $data['claimid'])->first();
        if ($claiminfo) {
            $data['totalclaim'] = $claiminfo->contractValue;
            $data['ID'] = $claiminfo->ID;
        }
        $clmid = $data['claimid'];
        // dd($clmid);
        $bene_sum = DB::select("SELECT  sum(`staffamount`) as tsum FROM `tblselectedstaffclaim` WHERE `claimID`='$clmid'")[0]->tsum;
        if (isset($_POST['add'])) {
            $staffExists = DB::table('tblselectedstaffclaim')
                ->where('claimID', $data['claimid'])
                ->where('staffID', $data['staffid'])
                ->exists();
            if ($staffExists) {
                return back()->with('err', 'This staff has already been added to the claim.');
            }
            $this->validate($request, ['claimid' => 'required', 'staffid' => 'required', 'amount' => 'required|numeric']);
            if ((float)$data['totalclaim'] < ((float)$bene_sum + (float)$data['amount'])) return back()->with('err', 'This operation cannot be performed because the total sum will exceed approved value.');
            DB::table('tblselectedstaffclaim')->insert([
                'claimID' => $data['claimid'],
                'staffamount' =>  $data['amount'],
                'staffID' => $data['staffid'],
            ]);
            //return back()->with('message','addedd  successfully added.'  );
            return  redirect('cpo-add-beneficiaries/' . $transId)
                ->with('message', 'addedd  successfully added.')
                ->with('claimid', $data['claimid']);
        }

        if (isset($_POST['addWithVoucherParameters'])) {

            $this->validate($request, ['claimid' => 'required']);
            $claimId = $data['claimid'];
            $totalClaim = (float)$data['totalclaim'];

            DB::beginTransaction();

            try {
                // Get all voucher parameters sorted by employee_type, hr_employment_type, gradelevel
                $voucherParams = DB::table('staffvoucherparameters')
                    ->orderBy('employee_type')
                    ->orderBy('hr_employment_type')
                    ->orderBy('gradelevel')
                    ->get();

                // -------------------------------
                // 1️⃣ Handle CJN, Justices, CR, SA (exact grade match)
                // -------------------------------
                foreach ($voucherParams as $param) {
                    $rate = (float)$param->rate;
                    $employeeType = $param->employee_type;
                    $hrEmploymentType = $param->hr_employment_type;
                    $grade = (int)$param->gradelevel;

                    if (in_array($employeeType, [2, 6, 7])) {
                        $staffList = DB::table('tblper')
                            ->where('staff_status', 1)
                            ->where('employee_type', $employeeType)
                            ->where('hremploymentType', $hrEmploymentType)
                            ->where('grade', $grade)
                            ->get();

                        foreach ($staffList as $staff) {
                            DB::table('tblselectedstaffclaim')->insert([
                                'claimID' => $claimId,
                                'staffamount' => $rate,
                                'staffID' => $staff->ID,
                            ]);
                        }
                    }
                }

                // -------------------------------
                // 2️⃣ Handle Permanent Staff (employee_type=1, hremploymentType=1)
                // -------------------------------
                $permanentParams = DB::table('staffvoucherparameters')
                    ->where('employee_type', 1)
                    ->where('hr_employment_type', 1)
                    ->orderBy('gradelevel', 'asc')
                    ->get();

                $prevMaxGrade = 0;

                foreach ($permanentParams as $param) {
                    $minGrade = $prevMaxGrade + 1;
                    $maxGrade = $param->gradelevel;
                    $rate = (float)$param->rate;

                    $staffList = DB::table('tblper')
                        ->where('staff_status', 1)
                        ->whereIn('employee_type', [1, 3, 4])
                        ->where('hremploymentType', 1)
                        ->whereBetween('grade', [$minGrade, $maxGrade])
                        ->get();

                    foreach ($staffList as $staff) {
                        DB::table('tblselectedstaffclaim')->insert([
                            'claimID' => $claimId,
                            'staffamount' => $rate,
                            'staffID' => $staff->ID,
                        ]);
                    }

                    $prevMaxGrade = $maxGrade; // update for next iteration
                }

                // -------------------------------
                // 3️⃣ Handle Contract Staff (employee_type=1, hremploymentType=2)
                // -------------------------------
                $contractParams = DB::table('staffvoucherparameters')
                    ->where('employee_type', 1)
                    ->where('hr_employment_type', 2)
                    ->orderBy('gradelevel', 'asc')
                    ->get();

                $prevMaxGrade = 0;

                foreach ($contractParams as $param) {
                    $minGrade = $prevMaxGrade + 1;
                    $maxGrade = $param->gradelevel;
                    $rate = (float)$param->rate;

                    $staffList = DB::table('tblper')
                        ->where('staff_status', 1)
                        ->where('employee_type', 1)
                        ->where('hremploymentType', 2)
                        ->whereBetween('grade', [$minGrade, $maxGrade])
                        ->get();

                    foreach ($staffList as $staff) {
                        DB::table('tblselectedstaffclaim')->insert([
                            'claimID' => $claimId,
                            'staffamount' => $rate,
                            'staffID' => $staff->ID,
                        ]);
                    }

                    $prevMaxGrade = $maxGrade; // update for next iteration
                }

                // -------------------------------
                // 4️⃣ Total sum validation
                // -------------------------------
                $totalInserted = DB::table('tblselectedstaffclaim')
                    ->where('claimID', $claimId)
                    ->sum('staffamount');

                if ($totalInserted > $totalClaim) {
                    // Rollback everything if exceeds
                    DB::table('tblselectedstaffclaim')->where('claimID', $claimId)->delete();
                    DB::rollBack();
                    return back()->with('err', 'This operation cannot be performed because the total sum will exceed approved value.');
                }

                DB::commit();

                return redirect('cpo-add-beneficiaries/' . $transId)
                    ->with('message', 'Staff successfully added using voucher parameters.')
                    ->with('claimid', $claimId);
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('err', 'An error occurred: ' . $e->getMessage());
            }
        }

        if (isset($_POST['update'])) {
            $this->validate($request, ['beneid' => 'required', 'amount' => 'required|numeric']);
            $prev_val = DB::table('tblselectedstaffclaim')->where('selectedID', $request['beneid'])->value('staffamount');
            if ((float)$data['totalclaim'] < ((float)$bene_sum + (float)$data['amount'] - (float)$prev_val)) return back()->with('err', 'This operation cannot be performed because the total sum will exceed approved value.');
            DB::table('tblselectedstaffclaim')->where('selectedID', $request['beneid'])->update([
                'staffamount' =>  $data['amount'],
            ]);
            return  redirect('cpo-add-beneficiaries/' . $transId)->with('message', 'successfully modified.');
        }

        if (isset($_POST['delete'])) {
            $this->validate($request, ['beneid' => 'required']);
            DB::table('tblselectedstaffclaim')->where('selectedID', $request['beneid'])->delete();
            return  redirect('cpo-add-beneficiaries/' . $transId)->with('message', 'successfully modified.');
        }

        $data['Claimlist'] = (Session::get('special') == 1) ? DB::table('tblcontractDetails')->where('openclose', 0)->where('companyID', 13)->where('approvalStatus', 1)->get() :
            DB::table('tblcontractDetails')->where('approvalStatus', 1)->where('openclose', 0)->where('companyID', 13)->get();
        //dd($data['Claimlist']); and `awaitingActionby`<>'OC'and `awaitingActionby`<>'AD'
        $data['StaffInformation_Claim'] = DB::table('tblselectedstaffclaim')->select('tblselectedstaffclaim.*', 'tblper.surname', 'tblper.first_name', 'tblper.othernames', 'tblper.bankID', 'tblper.AccNo')
            ->leftJoin('tblper', 'tblper.ID', '=', 'tblselectedstaffclaim.staffID')
            ->where('claimID', $data['claimid'])->get();
        // $data['StaffInformation'] = db::table('tblStaffInformation')->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblStaffInformation.bankID')->where('active', 1)->orderby('full_name')->get();
        $data['StaffInformation'] = db::table('tblper')
            ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->where('staff_status', 1)
            ->orWhere('isClaimed', 1)
            ->orderby('surname')->get();
        return view('funds.Procurements.beneficiarylistcpo', $data);
    }

    public function submitAllVoucherBeneficiary(Request $request)
    {
        $rawConDetails = DB::table('tblcontractDetails')->where('claimid', $request['cid'])->first();

        $bene_sum = DB::select("SELECT  sum(`staffamount`) as tsum FROM `tblselectedstaffclaim` WHERE `claimID`='$rawConDetails->claimid'")[0]->tsum;

        $cid = DB::table('tblpaymentTransaction')->where('contractID', $rawConDetails->ID)->where('vstage', '>', 0)->value('ID');
        $transId = $cid;
        if (round($bene_sum, 2) < round($rawConDetails->contractValue, 2)) return  redirect('cpo-add-beneficiaries/' . $transId);

        $data['beneficiary'] = $this->instanceFunction24->ClaimBenefeciaryNew($rawConDetails->claimid);
        $sumtotal = 0;
        $selectedStaffClaimTotal = 0;

        foreach ($data['beneficiary'] as $value) {
            $full_name = $value->surname . " " . $value->first_name . " " . $value->othernames ?? $value->othernames;
            $amount = (float) $value->staffamount;
            if ($amount <= 0) continue;

            if ($amount > (float) $value->amtpending) {
                $msg = "Cannot complete: staffamount $amount exceeds pending amount {$value->amtpending} for $full_name";
                return back()->with('error', $msg);
            }

            $sumtotal += $amount;
            $selectedStaffClaimTotal += 1;

            if ($selectedStaffClaimTotal == 1) {
                $selectedStaffClaimFullName = $full_name;
            }
        }

        if ($sumtotal == 0) return back()->with('error', 'This action is not successful because the amount passed for the voucher');

        foreach ($data['beneficiary'] as $value) {
            // dd("here 1");
            $full_name = $value->surname . " " . $value->first_name . " " . $value->othernames;

            $claimAccountNo = $value->claimAccountNo ?? null;
            $claimBankId = $value->claimBankId ?? null;
            $claimBankSortCode = $value->claimBankSortCode ?? null;

            if ($sumtotal == $rawConDetails->contractValue) {
                DB::table('tblvoucherBeneficiary')->insert(array(
                    'beneficiaryDetails'        => $full_name,
                    'amount'                    => $value->staffamount,
                    'voucherID'                 => $transId,
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
            }
        }
        $taskscheduled = $this->UpdateAlertTable("", '', '', '', 'tblcontractDetails', $rawConDetails->ID, 0);
        $taskscheduled = $this->UpdateAlertTable("Voucher Staff Beneficiary Clearance", 'cpo/voucher', '0', 'OC', 'tblpaymentTransaction', $transId, 1);
        $name = Auth::user()->name;
        $this->instanceFunction24->addLogg("Voucher with id: $transId beneficiary added by $name", "New voucher beneficiary submitted");

        $claimid = $rawConDetails->claimid;
        if (DB::select("SELECT sum(`amount`)  as sumt FROM `tblvoucherBeneficiary` WHERE `claimid`='$claimid'")[0]->sumt == DB::select("SELECT sum(`staffamount`)as sumt FROM `tblselectedstaffclaim` WHERE `claimID`='$claimid'")[0]->sumt) {
            DB::table('tblcontractDetails')->where('ID', $rawConDetails->ID)->update(['openclose' => 0, 'approvalStatus' => 1]);
        } else {
            DB::table('tblcontractDetails')->where('ID', $rawConDetails->ID)->update(['openclose' => 1]);
        }
        return redirect(url('cpo/generated'));
    }


    public function payGenerated2(Request $request)
    {
        $data['company'] = DB::table('tblcompany')->first();
        $data['audited'] = DB::table('tblpaymentTransaction2day')
            ->join('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction2day.companyID')
            ->leftJoin('tblbanklist', 'tblcontractor.Banker', '=', 'tblbanklist.bankID')
            ->where('auditStatus', '=', 1)
            ->where('cpo_payment', '=', 1)
            ->where('pay_confirmation', '=', 0)
            ->select('*', 'tblpaymentTransaction2day.ID as transID')
            ->orderBy('tblpaymentTransaction2day.ID', 'DESC')
            ->get();
        return view('funds.cpo.payGeneratedNew', $data);
    }

    public function updatePayGenerated(Request $request)
    {
        $id = $request['transID'];
        $ischecked = $request['ischecked'];

        if ($ischecked == 'false') {
            DB::table('tblpaymentTransaction')->where('ID', '=', $id)->update([
                'cpo_payment'  => 0,
            ]);
        } elseif ($ischecked == "true") {
            DB::table('tblpaymentTransaction')->where('ID', '=', $id)->update([
                'cpo_payment'  => 1,
            ]);
        }
        return response()->json('Success');
    }

    public function payRestore()
    {
        $data['company'] = DB::table('tblcompany')->first();

        //needs modification so it does not restore the one there is already a partial payment
        $data['audited'] = DB::table('tblepayment')
            ->where('is_paid_from_bank', '=', 0)
            ->select('*', 'tblepayment.transactionID as transID')
            ->groupBy('tblepayment.batch')
            ->get();
        //dd($data['audited']);
        return view('funds/cpo/payRestore', $data);
    }


    public function postRestore(Request $request)
    {
        $data['banks'] = DB::table('tblbanklist')->get();
        $btn = $request['submit'];
        if ($btn == 'Restore') {

            $ch          = $request['checkname'];
            if ($ch == '') {
                return back()->with('err', 'Please, Select at least one item');
            }
            /*foreach ($ch as $key=>$value) {
          DB::table('tblpaymentTransaction')
          ->where('ID','=',$ch[$key])
          ->update(array(
            'cpo_payment'          => 0,

            'cpo_payment_date'     => date('Y-m-d'),

           ));
      }*/


            return redirect('/cpo/restore');
        } elseif ($btn == 'Confirm') {
            $ch          = $request['checkname'];

            if ($ch == '') {
                return back()->with('err', 'Please, Select at least one item');
            }
            foreach ($ch as $key => $value) {
                DB::table('tblpaymentTransaction')
                    ->where('ID', '=', $ch[$key])
                    ->update(array(
                        'pay_confirmation'   => 1,

                    ));
            }
            $batch1 = $this->NewBatchNo();

            foreach ($ch as $key => $value) {

                $count = DB::table('tblvoucherBeneficiary')
                    ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblvoucherBeneficiary.bankID')
                    ->where('voucherID', '=', $ch[$key])
                    ->count();

                if ($count >= 1) {
                    $staff = DB::table('tblvoucherBeneficiary')
                        ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblvoucherBeneficiary.bankID')
                        ->where('voucherID', '=', $ch[$key])
                        ->get();

                    foreach ($staff as $list) {
                        DB::table('tblepayment')
                            ->insert(array(
                                'transactionID'   => $list->voucherID,
                                'contractor'      => $list->beneficiaryDetails,
                                'amount'          => $list->amount,
                                'accountNo'       => $list->accountNo,
                                'bank'            => $list->bank,

                                'date'            => date('Y-m-d'),
                                'batch'           => $batch1,
                            ));
                    }
                } else {
                    DB::table('tblepayment')
                        ->insert(array(
                            'transactionID'   => $ch[$key],
                            'contractor'      => $request['contractor'][$key],
                            'amount'          => $request['amount'][$key],
                            'accountNo'       => $request['accountNo'][$key],
                            'bank'            => $request['bank'][$key],

                            'date'            => date('Y-m-d'),
                            'batch'           => $batch1,
                        ));
                }
            }

            Session::put('batchNo',  $batch1);
            $data['sigA'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'A')->orderBy('rank')->get();
            $data['sigB'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'B')->orderBy('rank')->get();
            return redirect('/cpo/epayment');
        }
    }

    //cpo payment restore

    public function CPOPayRestore(Request $request)
    {
        $data['banks'] = DB::table('tblbanklist')->get();
        $btn = $request['submit'];
        if ($btn == 'Restore') {

            $ch          = $request['checkname'];
            if ($ch == '') {
                return back()->with('err', 'Please, Select at least one item');
            }
            foreach ($ch as $key => $value) {
                $batch = DB::table('tblepayment')->where('batch', '=', $ch[$key])->get();
                foreach ($batch as $list) {
                    if ($list->merge_group == '') {
                        DB::table('tblpaymentTransaction')
                            ->where('ID', '=', $list->transactionID)
                            ->update(array(
                                'cpo_payment'          => 0,
                                'pay_confirmation'     => 0,
                                'status'              => 2,
                                'cpo_payment_date'     => '',
                                'confirm_for_mandate' => 0,

                            ));
                        DB::table('tblepayment')->where('transactionID', '=', $list->transactionID)->delete();
                    } else {
                        $epayBreakdown = DB::table('tblepayment_breakdown')->where('merge_group', '=', $list->merge_group)->groupBy('transactionID')->get();
                        foreach ($epayBreakdown as $epay) {
                            DB::table('tblpaymentTransaction')
                                ->where('ID', '=', $epay->transactionID)
                                ->update(array(
                                    'cpo_payment'          => 0,
                                    'pay_confirmation'     => 0,
                                    'status'              => 2,
                                    'cpo_payment_date'     => '',
                                    'confirm_for_mandate' => 0,

                                ));
                        }
                        //check if it is merged group voucher, then remove from epaymentbreakdown where merge_group
                        DB::table('tblepayment_breakdown')->where('merge_group', '=', $list->merge_group)->delete();
                        //remove from epayment table
                        DB::table('tblepayment')->where('merge_group', '=', $list->merge_group)->delete();
                    }
                }
            }

            return redirect('/cpo/restore')->with('msg', 'Payment Mandate Restore Completed');
        }
    }


    // end cpo payment restore


    public function staffVoucherList($id)
    {
        $data['staff'] = DB::table('tblvoucherBeneficiary')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblvoucherBeneficiary.bankID')
            ->where('voucherID', '=', $id)
            ->get();

        return view('cpo/staffList', $data);
    }

    public function confirm2day(Request $request)
    {
        $ch          = isset($request['checkname']);
        dd($request['checkname']);
        if ($ch == '') {
            return back()->with('err', 'Please, Select at least one item');
        }
        if ($request->submit == "Return All") {

            // dd($request->checkAll);

            foreach ($ch as $key => $value) {
                DB::table('tblpaymentTransaction')
                    ->where('ID', '=', $ch[$key])
                    ->update(array(
                        'cpo_payment'          => 0,
                        'pay_confirmation'      => 0,
                    ));
            }
            return redirect('/cpo/report')->with('msg', 'Succcessfully Returned');
        }
    }


    public function confirm(Request $request)
    {
        $data['banks'] = DB::table('tblbanklist')->get();
        $contractTypeBank = $request['contractTypeBank'];

        $njcAcct = DB::table('tblmandate_address_account')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
            ->where('tblmandate_address_account.id', '=', $contractTypeBank)
            ->first();

        $ch = $request['checkname'];

        if ($ch == '') {
            return back()->with('err', 'Please, Select at least one item');
        }

        if ($request->submit == "Return All") {

            foreach ($ch as $key => $value) {
                DB::table('tblpaymentTransaction')
                    ->where('ID', '=', $ch[$key])
                    ->update([
                        'cpo_payment'        => 0,
                        'pay_confirmation'   => 0,
                        'confirm_for_mandate' => 0,
                    ]);
            }

            return redirect()->back()->with('msg', 'Succcessfully Returned');
        }

        if (!$contractTypeBank) {
            return back()->with('err', 'Please Select Payment Bank');
        }

        // ✅ MERGE FLAG
        $isMerge = $request->merge == 1;
        $mergeGroup = $isMerge ? 'MG-' . date('YmdHis') . '-' . rand(100, 999) : null;

        /*
        |--------------------------------------------------------------------------
        | VALIDATION BLOCK (UNCHANGED)
        |--------------------------------------------------------------------------
        */
        foreach ($ch as $key => $value) {

            $voucherContractType = DB::table('tblpaymentTransaction')
                ->where('ID', '=', $value)
                ->value('contractTypeID');

            $payTrans = DB::table('tblpaymentTransaction')
                ->where('ID', '=', $value)
                ->value('contractID');

            $claimid = DB::table('tblcontractDetails')
                ->where('ID', '=', $payTrans)
                ->value('claimid');

            if ($claimid != 0) {

                $beneficiarySum = DB::table('tblvoucherBeneficiary')
                    ->where('claimid', $claimid)
                    ->sum('amount');

                $paymentAmount = DB::table('tblpaymentTransaction')
                    ->where('ID', '=', $value)
                    ->value('amtPayable');

                if (round($beneficiarySum, 2) != round($paymentAmount, 2)) {
                    return back()->with(
                        'err',
                        "Cannot proceed because beneficiary total ($beneficiarySum) does not tally with payment amount ($paymentAmount)."
                    );
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE PAYMENT TRANSACTION STATUS
        |--------------------------------------------------------------------------
        */
        foreach ($ch as $key => $value) {
            DB::table('tblpaymentTransaction')
                ->where('ID', '=', $value)
                ->update([
                    'pay_confirmation'   => 1,
                    'status'             => 6,
                    'confirm_for_mandate' => 1,
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | MAIN TRANSACTION
        |--------------------------------------------------------------------------
        */
        DB::transaction(function () use (
            $ch,
            $voucherContractType,
            $njcAcct,
            $request,
            $isMerge,
            $mergeGroup
        ) {

            $smallBeneficiaries = [];

            foreach ($ch as $value) {

                $staff = DB::table('tblvoucherBeneficiary')
                    ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblvoucherBeneficiary.bankID')
                    ->where('voucherID', $value)
                    ->orderBy('tblvoucherBeneficiary.ID', 'ASC')
                    ->get();

                $count = $staff->count();

                /*
                |--------------------------------------------------------------------------
                | LARGE BENEFICIARIES (>=10) AND NOT MERGING
                |--------------------------------------------------------------------------
                */
                if ($count >= 10 && !$isMerge) {

                    $batchCounter = 0;
                    $batch1 = $this->NewBatchNoByContractType(
                        $voucherContractType,
                        $njcAcct->bank_abbr
                    );

                    foreach ($staff as $list) {

                        if ($batchCounter == 10) {
                            $batch1 = $this->NewBatchNoByContractType(
                                $voucherContractType,
                                $njcAcct->bank_abbr
                            );
                            $batchCounter = 0;
                        }

                        DB::table('tblepayment')->insert([
                            'transactionID'   => $list->voucherID,
                            'contractor'      => $list->beneficiaryDetails,
                            'accountName'     => $list->beneficiaryDetails,
                            'amount'          => $list->amount,
                            'accountNo'       => $list->accountNo,
                            'bank'            => $list->bank,
                            'bank_branch'     => 'ABUJA',
                            'date'            => $request['cpoPaymentDate'] ?? date('Y-m-d'),
                            'batch'           => $batch1,
                            'purpose'         => $request['cpoPaymentPurpose'][$value] ?? null,
                            'remark'          => $list->remarks,
                            'merge_group'     => $mergeGroup,
                            'adjusted_batch'  => $batch1,
                            'NJCAccount'      => $njcAcct->id,
                            'contract_typeID' => $voucherContractType,
                            'fileNo'          => $list->fileNo
                        ]);

                        $batchCounter++;
                    }
                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | SMALL BENEFICIARIES OR MERGE MODE
                    |--------------------------------------------------------------------------
                    */
                    if ($count > 0) {

                        foreach ($staff as $list) {
                            $smallBeneficiaries[] = [
                                'transactionID' => $list->voucherID,
                                'contractor'    => $list->beneficiaryDetails,
                                'accountName'   => $list->beneficiaryDetails,
                                'amount'        => $list->amount,
                                'accountNo'     => $list->accountNo,
                                'bank'          => $list->bank,
                                'date'          => $request['cpoPaymentDate'] ?? date('Y-m-d'),
                                'remark'        => $list->remarks,
                                'purpose'       => $request['cpoPaymentPurpose'][$value] ?? null,
                                'VATValue'      => 0,
                                'WHTValue'      => 0,
                                'stampduty'     => 0,
                                'fileNo'        => $list->fileNo,
                            ];
                        }
                    } else {

                        $smallBeneficiaries[] = [
                            'transactionID'   => $value,
                            'contractor'      => $request->contractor[$value],
                            'accountName'     => $request->contractor[$value],
                            'amount'          => $request->amount[$value],
                            'accountNo'       => $request->accountNo[$value],
                            'bank'            => $request->bank[$value],
                            'date'            => $request['cpoPaymentDate'] ?? date('Y-m-d'),
                            'purpose'         => $request['cpoPaymentPurpose'][$value] ?? '',
                            'VATValue'        => $request->vatAmount[$value] ?? 0,
                            'WHTValue'        => $request->whtAmount[$value] ?? 0,
                            'stampduty'       => $request->stampDuty[$value] ?? 0,
                            'tin'             => $request->contractorTIN[$value] ?? '',
                            'vat_bank'        => $request->vatBank[$value] ?? '',
                            'vat_bank_branch' => $request->vatBranch[$value] ?? '',
                            'vat_accountNo'   => $request->vatAccount[$value] ?? '',
                            'wht_bank'        => $request->whtBank[$value] ?? '',
                            'wht_bank_branch' => $request->whtBranch[$value] ?? '',
                            'wht_accountNo'   => $request->whtAccount[$value] ?? '',
                            'vat_payee'       => $request->vatPayee[$value] ?? '',
                            'wht_payee'       => $request->whtPayee[$value] ?? '',
                            'vat_sortcode'    => $request->vatSortCode[$value] ?? '',
                            'wht_sortcode'    => $request->whtSortCode[$value] ?? '',
                            'bank_sortcode'   => $request->bankSortCode[$value] ?? '',
                        ];
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | STEP 2: BATCH SMALL OR MERGED
            |--------------------------------------------------------------------------
            */
            if (!empty($smallBeneficiaries)) {

                $batchCounter = 0;

                $batch1 = null;

                if ($isMerge) {

                    $grouped = collect($smallBeneficiaries)
                        ->groupBy(function ($item) {
                            return $item['accountNo'] . '-' . $item['bank'];
                        });

                    foreach ($grouped as $items) {

                        if ($batchCounter % 10 == 0) {
                            $batch1 = $this->NewBatchNoByContractType(
                                $voucherContractType,
                                $njcAcct->bank_abbr
                            );
                            // $batchCounter = 0;
                        }
                        $allPurposes = $items->pluck('purpose')->filter()->unique()->implode(' | ');
                        // 1️⃣ Insert merged payment
                        $paymentId = DB::table('tblepayment')->insertGetId([
                            'transactionID'   => $items->pluck('transactionID')->implode(','),
                            'contractor'      => $items->first()['contractor'],
                            'accountName'     => $items->first()['accountName'],
                            'amount'          => $items->sum('amount'),
                            'accountNo'       => $items->first()['accountNo'],
                            'bank'            => $items->first()['bank'],
                            'bank_branch'     => 'ABUJA',
                            'date'            => $items->first()['date'] ?? date('Y-m-d'),
                            'batch'           => $batch1,
                            'VATValue'        => $items->sum('VATValue'),
                            'WHTValue'        => $items->sum('WHTValue'),
                            'stampduty'       => $items->sum('stampduty'),
                            'adjusted_batch'  => $batch1,
                            'merge_group'     => $mergeGroup,
                            'NJCAccount'      => $njcAcct->id,
                            'contract_typeID' => $voucherContractType,
                            'purpose'         => $allPurposes,
                            'fileNo'          => $items->first()['fileNo']
                        ]);

                        // 2️⃣ Insert breakdown rows (same batch!)
                        foreach ($items as $entry) {

                            DB::table('tblepayment_breakdown')->insert([
                                'payment_id'     => $paymentId,
                                'transactionID'  => $entry['transactionID'],
                                'contractor'     => $entry['contractor'],
                                'accountName'    => $entry['accountName'],
                                'accountNo'      => $entry['accountNo'],
                                'bank'           => $entry['bank'],
                                'amount'         => $entry['amount'],
                                'batch'          => $batch1, // ✅ SAME BATCH
                                'merge_group'    => $mergeGroup,
                                'NJCAccount'     => $njcAcct->id,
                                'contract_typeID' => $voucherContractType,
                                'fileNo'          => $entry['fileNo']
                            ]);
                        }

                        $batchCounter++;
                    }
                } else {
                    foreach ($smallBeneficiaries as $entry) {

                        if ($batchCounter % 10 == 0) {
                            $batch1 = $this->NewBatchNoByContractType(
                                $voucherContractType,
                                $njcAcct->bank_abbr
                            );
                            // $batchCounter = 0;
                        }

                        DB::table('tblepayment')->insert([
                            'transactionID'   => $entry['transactionID'],
                            'contractor'      => $entry['contractor'],
                            'accountName'     => $entry['accountName'],
                            'amount'          => $entry['amount'],
                            'accountNo'       => $entry['accountNo'],
                            'bank'            => $entry['bank'],
                            'bank_branch'     => 'ABUJA',
                            'date'            => $entry['date'] ?? date('Y-m-d'),
                            'batch'           => $batch1,
                            'purpose'         => $entry['purpose'] ?? '',
                            'remark'          => $entry['remark'] ?? '',
                            'VATValue'        => $entry['VATValue'] ?? 0,
                            'WHTValue'        => $entry['WHTValue'] ?? 0,
                            'stampduty'       => $entry['stampduty'] ?? 0,
                            'tin'             => $entry['tin'] ?? '',
                            'vat_bank'        => $entry['vat_bank'] ?? '',
                            'vat_bank_branch' => $entry['vat_bank_branch'] ?? '',
                            'vat_accountNo'   => $entry['vat_accountNo'] ?? '',
                            'wht_bank'        => $entry['wht_bank'] ?? '',
                            'wht_bank_branch' => $entry['wht_bank_branch'] ?? '',
                            'wht_accountNo'   => $entry['wht_accountNo'] ?? '',
                            'vat_payee'       => $entry['vat_payee'] ?? '',
                            'wht_payee'       => $entry['wht_payee'] ?? '',
                            'vat_sortcode'    => $entry['vat_sortcode'] ?? '',
                            'wht_sortcode'    => $entry['wht_sortcode'] ?? '',
                            'bank_sortcode'   => $entry['bank_sortcode'] ?? '',
                            'adjusted_batch'  => $batch1,
                            'NJCAccount'      => $njcAcct->id,
                            'contract_typeID' => $voucherContractType,
                            'fileNo'          => $entry['fileNo'] ?? '',
                        ]);

                        $batchCounter++;
                    }
                }
            }
        });

        return redirect('/batch/search');
    }

    public function confirmOld04032026(Request $request)
    {
        $data['banks'] = DB::table('tblbanklist')->get();
        $contractTypeBank = $request['contractTypeBank'];

        $njcAcct = DB::table('tblmandate_address_account')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
            ->where('tblmandate_address_account.id', '=', $contractTypeBank)
            ->first();

        $ch = $request['checkname'];

        if ($ch == '') {
            return back()->with('err', 'Please, Select at least one item');
        }
        if ($request->submit == "Return All") {

            // dd($request->checkAll);

            foreach ($ch as $key => $value) {
                DB::table('tblpaymentTransaction')
                    ->where('ID', '=', $ch[$key])
                    ->update(array(
                        'cpo_payment'          => 0,
                        'pay_confirmation'      => 0,
                        'confirm_for_mandate'  => 0,
                    ));
            }
            return redirect()->back()->with('msg', 'Succcessfully Returned');
        }

        if (!$contractTypeBank) {
            return back()->with('err', 'Please Select Payment Bank');
        }

        // $this->validate(
        //     $request,
        //     [
        //         'vatSortCode'     => 'required',
        //         'whtSortCode'     => 'required',
        //         //'contractType'    => 'required',
        //     ],
        //     [
        //         'vatSortCode.required' => 'VAT Payee Sort Code is Required. Please, set it up First',
        //         'whtSortCode.required' => 'Tax Payee Sort Code is Required. Please, set it up First',
        //         //'contractType.required' => 'Please, select the contract type, that is, either CAPITAL or RECCURRENT',
        //     ]

        // );

        foreach ($ch as $key => $value) {
            //DB::table('tblpaymentTransaction')->where('ID','=',$ch[$key])->update(array());
            $voucherContractType = DB::table('tblpaymentTransaction')->where('ID', '=', $value)->value('contractTypeID');

            //check of any of the selected transactions that is claim have beneficiaries already added
            //and that the sum amount tallies with total payment amount
            $payTrans = DB::table('tblpaymentTransaction')->where('ID', '=', $value)->value('contractID');
            $claimid = DB::table('tblcontractDetails')->where('ID', '=', $payTrans)->value('claimid');
            if ($claimid != 0) {
                $beneficiarySum = DB::table('tblvoucherBeneficiary')->where('claimid', $claimid)->sum('amount');
                $paymentAmount = DB::table('tblpaymentTransaction')->where('ID', '=', $value)->value('amtPayable');
                if (round($beneficiarySum, 2) != round($paymentAmount, 2)) {
                    return back()->with('err', "Cannot proceed the transaction because the sum of beneficiary amounts ($beneficiarySum) does not tally with the payment amount ($paymentAmount). Please, ensure that all beneficiaries are added and amounts are correct before confirming.");
                }
            }
        }

        foreach ($ch as $key => $value) {
            DB::table('tblpaymentTransaction')->where('ID', '=', $value)->update(array(
                'pay_confirmation'   => 1,
                'status'  => 6,
                'confirm_for_mandate' => 1,
            ));
        }

        DB::transaction(function () use (
            $ch,
            $voucherContractType,
            $njcAcct,
            $request
        ) {

            $smallBeneficiaries = [];
            foreach ($ch as $value) {
                $staff = DB::table('tblvoucherBeneficiary')
                    ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblvoucherBeneficiary.bankID')
                    ->where('voucherID', $value)
                    ->orderBy('tblvoucherBeneficiary.ID', 'ASC')
                    ->get();

                $count = $staff->count();

                if ($count >= 10) {

                    $batchCounter = 0;
                    $batch1 = $this->NewBatchNoByContractType(
                        $voucherContractType,
                        $njcAcct->bank_abbr
                    );

                    foreach ($staff as $list) {

                        if ($batchCounter == 10) {
                            $batch1 = $this->NewBatchNoByContractType(
                                $voucherContractType,
                                $njcAcct->bank_abbr
                            );
                            $batchCounter = 0;
                        }

                        DB::table('tblepayment')->insert([
                            'transactionID'   => $list->voucherID,
                            'contractor'      => $list->beneficiaryDetails,
                            'accountName'     => $list->beneficiaryDetails,
                            'amount'          => $list->amount,
                            'accountNo'       => $list->accountNo,
                            'bank'            => $list->bank,
                            'bank_branch'     => 'ABUJA',
                            'date'            => date('Y-m-d'),
                            'batch'           => $batch1,
                            'purpose'         => $request['cpoPaymentPurpose'][$value] ?? null,
                            'remark'          => $list->remarks,
                            'adjusted_batch'  => $batch1,
                            'NJCAccount'      => $njcAcct->id,
                            'contract_typeID' => $request->contractType,
                        ]);

                        $batchCounter++;
                    }
                } else {
                    if ($count > 0) {
                        foreach ($staff as $list) {
                            $smallBeneficiaries[] = [
                                'transactionID'   => $list->voucherID,
                                'contractor'      => $list->beneficiaryDetails,
                                'accountName'     => $list->beneficiaryDetails,
                                'amount'          => $list->amount,
                                'accountNo'       => $list->accountNo,
                                'bank'            => $list->bank,
                                'remark'          => $list->remarks,
                                'purpose'         => $request['cpoPaymentPurpose'][$value] ?? null,
                            ];
                        }
                    } else {
                        $smallBeneficiaries[] = [
                            'transactionID'   => $value,
                            'contractor'      => $request->contractor[$value],
                            'accountName'     => $request->contractor[$value],
                            'amount'          => $request->amount[$value],
                            'accountNo'       => $request->accountNo[$value],
                            'bank'            => $request->bank[$value],
                            // 'remark'          => null,
                            'purpose'         => $request['cpoPaymentPurpose'][$value] ?? '',
                            'VATValue'        => $request->vatAmount[$value] ?? 0,
                            'WHTValue'        => $request->whtAmount[$value] ?? 0,
                            'stampduty'       => $request->stampDuty[$value] ?? 0,
                            'tin'             => $request->contractorTIN[$value] ?? '',
                            'vat_bank'        => $request->vatBank[$value] ?? '',
                            'vat_bank_branch' => $request->vatBranch[$value] ?? '',
                            'vat_accountNo'   => $request->vatAccount[$value] ?? '',
                            'wht_bank'        => $request->whtBank[$value] ?? '',
                            'wht_bank_branch' => $request->whtBranch[$value] ?? '',
                            'wht_accountNo'   => $request->whtAccount[$value] ?? '',
                            'vat_payee'       => $request->vatPayee[$value] ?? '',
                            'wht_payee'       => $request->whtPayee[$value] ?? '',
                            'vat_sortcode'    => $request->vatSortCode[$value] ?? '',
                            'wht_sortcode'    => $request->whtSortCode[$value] ?? '',
                            'bank_sortcode'   => $request->bankSortCode[$value] ?? '',
                        ];
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | STEP 2: Batch Small Beneficiaries Together
            |--------------------------------------------------------------------------
            */
            if (!empty($smallBeneficiaries)) {

                $batchCounter = 0;
                $batch1 = $this->NewBatchNoByContractType(
                    $voucherContractType,
                    $njcAcct->bank_abbr
                );

                foreach ($smallBeneficiaries as $entry) {

                    if ($batchCounter == 10) {
                        $batch1 = $this->NewBatchNoByContractType(
                            $voucherContractType,
                            $njcAcct->bank_abbr
                        );
                        $batchCounter = 0;
                    }

                    DB::table('tblepayment')->insert([
                        'transactionID'   => $entry['transactionID'],
                        'contractor'      => $entry['contractor'],
                        'accountName'     => $entry['accountName'],
                        'amount'          => $entry['amount'],
                        'accountNo'       => $entry['accountNo'],
                        'bank'            => $entry['bank'],
                        'bank_branch'     => 'ABUJA',
                        'date'            => date('Y-m-d'),
                        'batch'           => $batch1,
                        'purpose'         => $entry['purpose'] ?? '',
                        'remark'          => $entry['remark'] ?? '',
                        'VATValue'        => $entry['VATValue'] ?? 0,
                        'WHTValue'        => $entry['WHTValue'] ?? 0,
                        'stampduty'       => $entry['stampduty'] ?? 0,
                        'tin'             => $entry['tin'] ?? '',
                        'vat_bank'        => $entry['vat_bank'] ?? '',
                        'vat_bank_branch' => $entry['vat_bank_branch'] ?? '',
                        'vat_accountNo'   => $entry['vat_accountNo'] ?? '',
                        'wht_bank'        => $entry['wht_bank'] ?? '',
                        'wht_bank_branch' => $entry['wht_bank_branch'] ?? '',
                        'wht_accountNo'   => $entry['wht_accountNo'] ?? '',
                        'vat_payee'       => $entry['vat_payee'] ?? '',
                        'wht_payee'       => $entry['wht_payee'] ?? '',
                        'vat_sortcode'    => $entry['vat_sortcode'] ?? '',
                        'wht_sortcode'    => $entry['wht_sortcode'] ?? '',
                        'bank_sortcode'   => $entry['bank_sortcode'] ?? '',
                        'adjusted_batch'  => $batch1,
                        'NJCAccount'      => $njcAcct->id,
                        'contract_typeID' => $request->contractType,
                    ]);

                    $batchCounter++;
                }
            }
        });

        // Session::put('batchNo',  $batch1);
        $data['sigA'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'A')->orderBy('rank')->get();
        $data['sigB'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'B')->orderBy('rank')->get();
        // return redirect('/view/batch');
        return redirect('/batch/search');
    }

    public function confirmOld24022026(Request $request)
    {
        $data['banks'] = DB::table('tblbanklist')->get();
        $contractTypeBank = $request['contractTypeBank'];
        // $getContractTypeOfContract = DB::table('tblpaymentTransaction')->where('ID', '=', $request['checkname'])->value('contractTypeID');

        // $njcAcct = DB::table('tblmandate_address_account')
        //     ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
        //     ->where('tblmandate_address_account.contractTypeID', '=', $getContractTypeOfContract)
        //     ->where('tblmandate_address_account.status', '=', 1)
        //     ->first();
        $njcAcct = DB::table('tblmandate_address_account')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
            ->where('tblmandate_address_account.id', '=', $contractTypeBank)
            ->first();

        $ch = $request['checkname'];

        if ($ch == '') {
            return back()->with('err', 'Please, Select at least one item');
        }
        if ($request->submit == "Return All") {

            // dd($request->checkAll);

            foreach ($ch as $key => $value) {
                DB::table('tblpaymentTransaction')
                    ->where('ID', '=', $ch[$key])
                    ->update(array(
                        'cpo_payment'          => 0,
                        'pay_confirmation'      => 0,
                        'confirm_for_mandate'  => 0,
                    ));
            }
            return redirect()->back()->with('msg', 'Succcessfully Returned');
        }

        if (!$contractTypeBank) {
            return back()->with('err', 'Please Select Payment Bank');
        }

        // $this->validate(
        //     $request,
        //     [
        //         'vatSortCode'     => 'required',
        //         'whtSortCode'     => 'required',
        //         //'contractType'    => 'required',
        //     ],
        //     [
        //         'vatSortCode.required' => 'VAT Payee Sort Code is Required. Please, set it up First',
        //         'whtSortCode.required' => 'Tax Payee Sort Code is Required. Please, set it up First',
        //         //'contractType.required' => 'Please, select the contract type, that is, either CAPITAL or RECCURRENT',
        //     ]

        // );

        foreach ($ch as $key => $value) {
            //DB::table('tblpaymentTransaction')->where('ID','=',$ch[$key])->update(array());
            $voucherContractType = DB::table('tblpaymentTransaction')->where('ID', '=', $value)->value('contractTypeID');

            //check of any of the selected transactions that is claim have beneficiaries already added
            //and that the sum amount tallies with total payment amount
            $payTrans = DB::table('tblpaymentTransaction')->where('ID', '=', $value)->value('contractID');
            $claimid = DB::table('tblcontractDetails')->where('ID', '=', $payTrans)->value('claimid');
            if ($claimid != 0) {
                $beneficiarySum = DB::table('tblvoucherBeneficiary')->where('claimid', $claimid)->sum('amount');
                $paymentAmount = DB::table('tblpaymentTransaction')->where('ID', '=', $value)->value('amtPayable');
                if (round($beneficiarySum, 2) != round($paymentAmount, 2)) {
                    return back()->with('err', "Cannot proceed the transaction because the sum of beneficiary amounts ($beneficiarySum) does not tally with the payment amount ($paymentAmount). Please, ensure that all beneficiaries are added and amounts are correct before confirming.");
                }
            }
        }

        foreach ($ch as $key => $value) {
            DB::table('tblpaymentTransaction')->where('ID', '=', $value)->update(array(
                'pay_confirmation'   => 1,
                'status'  => 6,
                'confirm_for_mandate' => 1,
            ));
        }

        foreach ($ch as $key => $value) {
            $batchCounter = 0;
            $batch1 = $this->NewBatchNoByContractType($voucherContractType, $njcAcct->bank_abbr);

            $count = DB::table('tblvoucherBeneficiary')
                ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblvoucherBeneficiary.bankID')
                ->where('voucherID', '=', $ch[$key])
                ->count();

            if ($count >= 1) {
                $staff = DB::table('tblvoucherBeneficiary')
                    ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblvoucherBeneficiary.bankID')
                    //->join('tblpaymentTransaction','tblpaymentTransaction.ID','=','tblvoucherBeneficiary.voucherID')
                    ->where('voucherID', '=', $ch[$key])
                    ->orderBy('tblvoucherBeneficiary.ID', 'ASC')
                    ->get();

                foreach ($staff as $list) {
                    if ($batchCounter > 0 && $batchCounter % 10 == 0) {
                        $batch1 = $this->NewBatchNoByContractType($voucherContractType, $njcAcct->bank_abbr);
                    }
                    DB::table('tblepayment')
                        ->insert(array(
                            'transactionID'   => $list->voucherID,
                            'contractor'      => $list->beneficiaryDetails,
                            'accountName'     => $list->beneficiaryDetails,
                            'amount'          => $list->amount,
                            'accountNo'       => $list->accountNo,
                            'bank'            => $list->bank,
                            'bank_branch'     => 'ABUJA',
                            'date'            => date('Y-m-d'),
                            'batch'           => $batch1,
                            // 'purpose'        => $request['purpose'][$key],
                            'purpose'        => $request['cpoPaymentPurpose'][$value],
                            'remark'         => $list->remarks,
                            'adjusted_batch'             => $batch1,
                            'NJCAccount'      => $njcAcct->id,
                            'contract_typeID' => $request['contractType'],
                        ));
                    $batchCounter++;
                }
            } else {
                DB::table('tblepayment')
                    ->insert(array(
                        'transactionID'     => $value,
                        'contractor'    => $request->contractor[$value],
                        'amount'        => $request->amount[$value],
                        'accountNo'     => $request->accountNo[$value],
                        'accountName'     => $request->contractor[$value],
                        'bank'          => $request->bank[$value],
                        'bank_branch'     => 'ABUJA',
                        'date'          => date('Y-m-d'),
                        'batch'         => $batch1,
                        'VATValue'      => $request->vatAmount[$value],
                        'WHTValue'      => $request->whtAmount[$value],
                        'stampduty'      => $request->stampDuty[$value],
                        'tin'            => $request->contractorTIN[$value],
                        'vat_bank'      => $request->vatBank[$value],
                        'vat_bank_branch' => $request->vatBranch[$value],
                        'vat_accountNo' => $request->vatAccount[$value],
                        'wht_bank'      => $request->whtBank[$value],
                        'wht_bank_branch' => $request->whtBranch[$value],
                        'wht_accountNo' => $request->whtAccount[$value],
                        'purpose'       => $request->cpoPaymentPurpose[$value] ?? null,
                        'vat_payee'     => $request->vatPayee[$value],
                        'wht_payee'     => $request->whtPayee[$value],
                        'bank_branch'   => $request->bankBranch[$value],
                        'vat_sortcode'  => $request->vatSortCode[$value],
                        'wht_sortcode'  => $request->whtSortCode[$value],
                        'adjusted_batch' => $batch1,
                        'NJCAccount'    => $njcAcct->id,
                        'contract_typeID' => $request->contractType,
                    ));
            }
        }

        // Session::put('batchNo',  $batch1);
        $data['sigA'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'A')->orderBy('rank')->get();
        $data['sigB'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'B')->orderBy('rank')->get();
        // return redirect('/view/batch');
        return redirect('/batch/search');
    }


    public function epayment()
    {
        $data['company'] = DB::table('tblcompany')->first();
        $data['batch'] = DB::table('tblepayment')->groupBy('batch')->get();

        $data['current_batch'] = session('batchNo');

        if (session('batchNo') != '') {
            $data['mandate'] = DB::table('tblepayment')
                ->where('batch', '=', session('batchNo'))
                ->get();
            $data['sum'] = DB::table('tblepayment')
                ->where('batch', session('batchNo'))
                ->groupBy('batch')
                ->sum('amount');
            $data['vatsum'] = DB::table('tblepayment')
                ->where('batch', session('batchNo'))
                ->groupBy('batch')
                ->sum('VATValue');
            $data['whtsum'] = DB::table('tblepayment')
                ->where('batch', session('batchNo'))
                ->groupBy('batch')
                ->sum('WHTValue');
            $data['trans'] = DB::table('tblpaymentTransaction')
                ->join('tblepayment', 'tblepayment.transactionID', '=', 'tblpaymentTransaction.ID')
                ->where('tblepayment.batch', '=', session('batchNo'))
                ->where('tblpaymentTransaction.pay_confirmation', '=', 1)
                //->where('tblepayment.mandate_status','=',3)
                ->get();
            $data['status'] = DB::table('tblepayment')
                ->where('batch', '=', session('batchNo'))
                ->first();
            //dd($data['status']);

            $data['sigA'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'A')->orderBy('rank')->get();
            $data['sigB'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'B')->orderBy('rank')->get();
            $data['sig1'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'A')->where('batch', '=', session('batchNo'))->first();
            $data['sig2'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'B')->where('batch', '=', session('batchNo'))->first();
            $data['banks'] = DB::table('tblbanklist')->get();
            return view('funds/cpo/mandate', $data);
        } else {

            $data['mandate'] = DB::select('select * from tblepayment where  `date` >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) group by batch');
            $v = 0;
            $w = 0;
            $a = 0;
            foreach ($data['mandate'] as $list) {
                $a += $list->amount;
                $v += $list->VATValue;
                $w += $list->WHTValue;
            }
            $data['sum'] = $a;
            $data['vatsum'] = $v;
            $data['whtsum'] = $w;
            return view('funds/cpo/mandate', $data);
        }
    }



    public function batch()
    {

        $data['company'] = DB::table('tblcompany')->first();
        $data['codes'] = DB::table('tblaction_rank')->where('cont_payment_active', '=', 1)->get();
        // $data['audited'] = DB::select('select *, sum(`amount`) as sum,sum(`VATValue`) as vsum,sum(`WHTValue`) as wsum from tblepayment where  `date` >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) group by batch');
        // $data['audited'] = DB::select('select *, sum(`amount`) as sum,sum(`VATValue`) as vsum,sum(`WHTValue`) as wsum from tblepayment where  `date` >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) group by transactionID');
        $data['audited'] = DB::select("
            SELECT 
                tblepayment.*,
                SUM(tblepayment.amount) as sum,
                SUM(tblepayment.VATValue) as vsum,
                SUM(tblepayment.WHTValue) as wsum,
                SUM(tblepayment.stampduty) as stampsum,
                COUNT(DISTINCT batch) as batch_count
            FROM tblepayment
            WHERE tblepayment.date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
            GROUP BY tblepayment.batch
        ");
        $data['contractlist'] = DB::table('tblcontractType')->where('status', 1)->get();
        $data['selectedfromDate'] = "";
        $data['selectedtoDate'] = "";
        $data['selectedContractType'] = "";
        $data['selectedVoucherNumber'] = "";
        return view('funds/cpo/batch', $data);
    }

    public function postBatch(Request $request)
    {
        //$fromdate            = date('Y-m-d', strtotime(trim($request['dateFrom'])));
        //$todate             = date('Y-m-d', strtotime(trim($request['dateTo'])));
        $data['codes'] = DB::table('tblaction_rank')->where('cont_payment_active', '=', 1)->get();
        $fromdate             = $request['dateFrom'];
        $todate               = $request['dateTo'];
        $contractTypeID      = $request['contracttype'];
        $voucherNumber = $request['voucherNumber'];

        $data['selectedfromDate'] = $request['dateFrom'];
        $data['selectedtoDate'] = $request['dateTo'];
        $data['selectedContractType'] = $request['contracttype'];
        $data['selectedVoucherNumber'] = $request['voucherNumber'];

        $batch = $request['batch'];
        $data['batch'] = DB::table('tblepayment')->groupBy('batch')->get();

        $data['audited'] = DB::table('tblepayment as ep')
            ->join('tblpaymentTransaction as pt', 'ep.transactionID', '=', 'pt.ID')
            ->when($voucherNumber, function ($query) use ($voucherNumber) {
                $query->where('pt.vref_no', '=', $voucherNumber);
            })
            ->when($contractTypeID, function ($query) use ($contractTypeID) {
                $query->where('ep.contract_typeID', $contractTypeID);
            })
            ->when($fromdate, function ($query) use ($fromdate) {
                $query->whereDate('ep.date', '>=', $fromdate);
            })
            ->when($todate, function ($query) use ($todate) {
                $query->whereDate('ep.date', '<=', $todate);
            })
            ->groupBy('ep.batch')
            ->selectRaw('
                ep.*,
                SUM(ep.amount) as sum,
                SUM(ep.VATValue) as vsum,
                SUM(ep.WHTValue) as wsum,
                SUM(ep.stampduty) as stampsum
            ')
            ->get();
        $data['contractlist'] = DB::table('tblcontractType')->where('status', 1)->get();
        return view('funds/cpo/batch', $data);
    }

    /********************* Capital Voucher Generaation ********************************/

    public function capitalMandate($id)
    {
        //dd('ok');
        $count = DB::table('tblepayment')->where('mandate_status', '!=', 3)->where('batch', session('batchNo'))->count();
        $data['cbn'] = DB::table('tblmandate_address_account')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
            ->where('tblmandate_address_account.bankId', '=', 38)
            ->first();

        $data['current_batch'] = $id;
        $data['company'] = DB::table('tblcompany')->first();
        $data['mandate'] = DB::table('tblepayment')
            ->where('mandate_status', '!=', 3)
            ->where('batch', $id)
            ->where('contract_typeID', '=', 4)
            ->groupBy('bank')
            ->selectRaw('*,count(bank) as NumOfBanks,tblepayment.bank as bankName, sum(amount) as totalAmount, sum(VATValue) as vat,sum(WHTValue) as tax')
            ->get();
        $data['breakdown'] = DB::table('tblepayment')
            ->join('tblbanklist', 'tblbanklist.bank', '=', 'tblepayment.bank')
            ->where('mandate_status', '!=', 3)
            ->where('batch', $id)
            ->select('*', 'tblepayment.bank as BankName')
            ->get();

        $data['status'] = DB::table('tblepayment')
            ->where('batch', $id)
            ->first();
        $data['checkApproval'] = DB::table('tblepayment')
            ->where('batch', $id)
            ->where('mandate_status', 3)
            ->count();
        $data['sum'] = DB::table('tblepayment')
            ->where('batch', $id)
            ->groupBy('batch')
            ->sum('amount');
        $data['vatsum'] = DB::table('tblepayment')
            ->where('batch', $id)
            ->groupBy('batch')
            ->sum('VATValue');
        $data['whtsum'] = DB::table('tblepayment')
            ->where('batch', $id)
            ->groupBy('batch')
            ->sum('WHTValue');
        $data['sigA'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'A')->where('is_capital', '=', 1)->orderBy('rank')->get();
        $data['sigB'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'B')->where('is_capital', '=', 1)->orderBy('rank')->get();
        $data['sig1'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'A')->where('batch', '=', $id)->first();
        $data['sig2'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'B')->where('batch', '=', $id)->first();

        $data['accountDetails'] =  DB::table('tblmandate_address_account')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
            ->where('tblmandate_address_account.status', '=', 1)
            ->get();
        $data['accountAddress'] =  DB::table('tblmandate_address_account')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
            ->where('tblmandate_address_account.status', '=', 1)
            ->first();

        $data['banks'] = DB::table('tblbanklist')->get();
        $data['date'] = $data['status']->date;

        return view('funds.cpo.contractEpayment', $data);
    }


    public function viewBatch19_02_2026($id = null)
    {
        $count = DB::table('tblepayment')->where('mandate_status', '!=', 3)->where('batch', session('batchNo'))->count();

        if (session('batchNo') != '' && $count != 0) {
            $checkCap = DB::table('tblepayment')->where('mandate_status', '!=', 3)->where('contract_typeID', '=', 4)->where('batch', session('batchNo'))->count();
            $checkCapWithCtType = DB::table('tblepayment')->where('mandate_status', '!=', 3)->where('batch', session('batchNo'))->first();
            if ($checkCap > 0) {
                $count = DB::table('tblepayment')->where('mandate_status', '!=', 3)->where('batch', session('batchNo'))->count();
                $data['cbn'] = DB::table('tblmandate_address_account')
                    ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                    ->where('tblmandate_address_account.id', '=', $checkCapWithCtType->NJCAccount)
                    ->first();

                $data['current_batch'] = session('batchNo');
                $data['company'] = DB::table('tblcompany')->first();
                $data['mandate'] = DB::table('tblepayment')
                    ->where('mandate_status', '!=', 3)
                    ->where('batch', session('batchNo'))
                    ->where('contract_typeID', '=', 4)
                    ->groupBy('bank')
                    ->selectRaw('*,count(bank) as NumOfBanks,tblepayment.bank as bankName, sum(amount) as totalAmount, sum(VATValue) as vat,sum(WHTValue) as tax')
                    ->get();
                $data['breakdown'] = DB::table('tblepayment')
                    ->join('tblbanklist', 'tblbanklist.bank', '=', 'tblepayment.bank')
                    ->where('mandate_status', '!=', 3)
                    ->where('batch', session('batchNo'))
                    //->where('tblepayment.contract_typeID','=', 4)
                    ->select('*', 'tblepayment.bank as BankName')
                    ->get();

                $data['status'] = DB::table('tblepayment')
                    ->where('batch', session('batchNo'))
                    ->first();
                $data['checkApproval'] = DB::table('tblepayment')
                    ->where('batch', session('batchNo'))
                    ->where('mandate_status', 3)
                    ->count();
                $data['sum'] = DB::table('tblepayment')
                    ->where('batch', session('batchNo'))
                    ->groupBy('batch')
                    ->sum('amount');
                $data['vatsum'] = DB::table('tblepayment')
                    ->where('batch', session('batchNo'))
                    ->groupBy('batch')
                    ->sum('VATValue');
                $data['whtsum'] = DB::table('tblepayment')
                    ->where('batch', session('batchNo'))
                    ->groupBy('batch')
                    ->sum('WHTValue');
                $data['sigA'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'A')->where('is_capital', '=', 1)->orderBy('rank')->get();
                $data['sigB'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'B')->where('is_capital', '=', 1)->orderBy('rank')->get();
                $data['sig1'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'A')->where('batch', '=', session('batchNo'))->first();
                $data['sig2'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'B')->where('batch', '=', session('batchNo'))->first();

                $data['accountDetails'] =  DB::table('tblmandate_address_account')
                    ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                    ->where('tblmandate_address_account.contractTypeID', '=', $checkCapWithCtType->contract_typeID)
                    ->where('tblmandate_address_account.status', '=', 1)
                    ->get();
                $data['accountAddress'] =  DB::table('tblmandate_address_account')
                    ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                    ->where('tblmandate_address_account.id', '=', $checkCapWithCtType->NJCAccount)
                    ->first();

                $data['banks'] = DB::table('tblbanklist')->get();
                $data['date'] = $data['status']->date;

                return view('funds.cpo.contractEpaymentToday', $data);

                //$this->capitalMandate(session('batchNo'));
            }
            $data['current_batch'] = session('batchNo');

            $data['company'] = DB::table('tblcompany')->first();
            $data['mandate'] = DB::table('tblepayment')
                ->where('mandate_status', '!=', 3)
                ->where('batch', session('batchNo'))
                //->selectRaw('*,sum(amount) as amt,sum(VATValue) as vat,sum(WHTValue) as wht')
                ->get();

            $data['status'] = DB::table('tblepayment')
                ->where('batch', session('batchNo'))
                ->first();
            $data['checkApproval'] = DB::table('tblepayment')
                ->where('batch', session('batchNo'))
                ->where('mandate_status', 3)
                ->count();
            $data['sum'] = DB::table('tblepayment')
                ->where('batch', session('batchNo'))
                ->groupBy('batch')
                ->sum('amount');
            $data['vatsum'] = DB::table('tblepayment')
                ->where('batch', session('batchNo'))
                ->groupBy('batch')
                ->sum('VATValue');
            $data['whtsum'] = DB::table('tblepayment')
                ->where('batch', session('batchNo'))
                ->groupBy('batch')
                ->sum('WHTValue');
            $data['sigA'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'A')->where('is_capital', '=', 0)->orderBy('rank')->get();
            $data['sigB'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'B')->where('is_capital', '=', 0)->orderBy('rank')->get();
            $data['sig1'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'A')->where('batch', '=', session('batchNo'))->first();
            $data['sig2'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'B')->where('batch', '=', session('batchNo'))->first();

            $data['accountDetails'] =  DB::table('tblmandate_address_account')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                ->where('tblmandate_address_account.contractTypeID', '=', $checkCapWithCtType->contract_typeID)
                ->where('tblmandate_address_account.status', '=', 1)
                ->get();
            $data['accountAddress'] =  DB::table('tblmandate_address_account')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                ->where('tblmandate_address_account.id', '=', $checkCapWithCtType->NJCAccount)
                ->first();

            $data['banks'] = DB::table('tblbanklist')->get();

            $data['date'] = $data['status']->date;

            return view('funds/cpo/epayment', $data);
        } else {
            $checkCap = DB::table('tblepayment')->where('mandate_status', '!=', 3)->where('contract_typeID', '=', 4)->where('batch', $id)->count();
            $checkCapWithCtType = DB::table('tblepayment')->where('mandate_status', '!=', 3)->where('batch', $id)->first();
            if ($checkCap > 0) {
                $count = DB::table('tblepayment')->where('mandate_status', '!=', 3)->where('batch', session('batchNo'))->count();
                $data['cbn'] = DB::table('tblmandate_address_account')
                    ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                    ->where('tblmandate_address_account.id', '=', $checkCapWithCtType->NJCAccount)
                    ->first();

                $data['current_batch'] = $id;
                $data['company'] = DB::table('tblcompany')->first();
                $data['mandate'] = DB::table('tblepayment')
                    ->where('mandate_status', '!=', 3)
                    ->where('batch', $id)
                    ->where('contract_typeID', '=', 4)
                    ->groupBy('bank')
                    ->selectRaw('*,count(bank) as NumOfBanks,tblepayment.bank as bankName, sum(amount) as totalAmount, sum(VATValue) as vat,sum(WHTValue) as tax')
                    ->get();
                $data['breakdown'] = DB::table('tblepayment')
                    ->join('tblbanklist', 'tblbanklist.bank', '=', 'tblepayment.bank')
                    ->where('mandate_status', '!=', 3)
                    ->where('batch', $id)
                    //->where('tblepayment.contract_typeID','=', 4)
                    //->selectRaw('*, tblepayment.bank as BankName, sum(amount) as totalAmount, sum(VATValue) as vat,sum(WHTValue) as tax')
                    ->select('*', 'tblepayment.bank as BankName')
                    ->get();

                $data['status'] = DB::table('tblepayment')
                    ->where('batch', $id)
                    ->first();
                $data['checkApproval'] = DB::table('tblepayment')
                    ->where('batch', $id)
                    ->where('mandate_status', 3)
                    ->count();
                $data['sum'] = DB::table('tblepayment')
                    ->where('batch', $id)
                    ->groupBy('batch')
                    ->sum('amount');
                $data['vatsum'] = DB::table('tblepayment')
                    ->where('batch', $id)
                    ->groupBy('batch')
                    ->sum('VATValue');
                $data['whtsum'] = DB::table('tblepayment')
                    ->where('batch', $id)
                    ->groupBy('batch')
                    ->sum('WHTValue');
                $data['sigA'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'A')->where('is_capital', '=', 1)->orderBy('rank')->get();
                $data['sigB'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'B')->where('is_capital', '=', 1)->orderBy('rank')->get();
                $data['sig1'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'A')->where('batch', '=', $id)->first();
                $data['sig2'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'B')->where('batch', '=', $id)->first();

                $data['accountDetails'] =  DB::table('tblmandate_address_account')
                    ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                    ->where('tblmandate_address_account.contractTypeID', '=', $checkCapWithCtType->contract_typeID)
                    ->where('tblmandate_address_account.status', '=', 1)
                    ->get();
                $data['accountAddress'] =  DB::table('tblmandate_address_account')
                    ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                    ->where('tblmandate_address_account.id', '=', $checkCapWithCtType->NJCAccount)
                    ->first();

                $data['banks'] = DB::table('tblbanklist')->get();
                $data['date'] = $data['status']->date;

                return view('funds.cpo.contractEpaymentToday', $data);
            }

            $data['current_batch'] = $id;
            $data['company'] = DB::table('tblcompany')->first();
            $data['mandate'] = DB::table('tblepayment')
                ->where('mandate_status', '!=', 3)
                ->where('batch', $id)
                //->selectRaw('*,sum(amount) as amt,sum(VATValue) as vat,sum(WHTValue) as wht')
                ->get();
            $data['status'] = DB::table('tblepayment')
                ->where('batch', $id)
                ->first();
            $data['checkApproval'] = DB::table('tblepayment')
                ->where('batch', $id)
                ->where('mandate_status', 3)
                ->count();
            $data['sum'] = DB::table('tblepayment')
                ->where('batch', $id)
                ->groupBy('batch')
                ->sum('amount');
            $data['vatsum'] = DB::table('tblepayment')
                ->where('batch', $id)
                ->groupBy('batch')
                ->sum('VATValue');
            $data['whtsum'] = DB::table('tblepayment')
                ->where('batch', $id)
                ->groupBy('batch')
                ->sum('WHTValue');
            $data['sigA'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'A')->where('is_capital', '=', 0)->orderBy('rank')->get();
            $data['sigB'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'B')->where('is_capital', '=', 0)->orderBy('rank')->get();
            $data['sig1'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'A')->where('batch', '=', $id)->first();
            $data['sig2'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'B')->where('batch', '=', $id)->first();

            $data['accountDetails'] =  DB::table('tblmandate_address_account')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                ->where('tblmandate_address_account.contractTypeID', '=', $checkCapWithCtType->contract_typeID)
                ->where('tblmandate_address_account.status', '=', 1)
                ->get();
            $data['accountAddress'] =  DB::table('tblmandate_address_account')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                ->where('tblmandate_address_account.id', '=', $checkCapWithCtType->NJCAccount)
                ->first();

            $data['banks'] = DB::table('tblbanklist')->get();
            $data['date'] = $data['status']->date;

            return view('funds/cpo/epayment', $data);
        }
    }

    public function viewBatch($id = null)
    {
        $checkCap = DB::table('tblepayment')->where('mandate_status', '!=', 3)->where('contract_typeID', '=', 4)->where('batch', $id)->count();
        $checkCapWithCtType = DB::table('tblepayment')->where('mandate_status', '!=', 3)->where('batch', $id)->first();
        if ($checkCap > 0) {
            $count = DB::table('tblepayment')->where('mandate_status', '!=', 3)->where('batch', session('batchNo'))->count();
            $data['cbn'] = DB::table('tblmandate_address_account')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                ->where('tblmandate_address_account.id', '=', $checkCapWithCtType->NJCAccount)
                ->first();

            $data['current_batch'] = $id;
            $data['company'] = DB::table('tblcompany')->first();
            $data['mandate'] = DB::table('tblepayment')
                ->where('mandate_status', '!=', 3)
                ->where('batch', $id)
                ->where('contract_typeID', '=', 4)
                // ->groupBy('bank')
                // ->selectRaw('*,count(bank) as NumOfBanks,tblepayment.bank as bankName, sum(amount) as totalAmount, sum(VATValue) as vat,sum(WHTValue) as tax')
                ->get();
            $data['breakdown'] = DB::table('tblepayment')
                ->join('tblbanklist', 'tblbanklist.bank', '=', 'tblepayment.bank')
                ->where('mandate_status', '!=', 3)
                ->where('batch', $id)
                //->where('tblepayment.contract_typeID','=', 4)
                //->selectRaw('*, tblepayment.bank as BankName, sum(amount) as totalAmount, sum(VATValue) as vat,sum(WHTValue) as tax')
                ->select('*', 'tblepayment.bank as BankName')
                ->get();

            $data['status'] = DB::table('tblepayment')
                ->where('batch', $id)
                ->first();
            $data['checkApproval'] = DB::table('tblepayment')
                ->where('batch', $id)
                ->where('mandate_status', 3)
                ->count();
            $data['sum'] = DB::table('tblepayment')
                ->where('batch', $id)
                ->groupBy('batch')
                ->sum('amount');
            $data['vatsum'] = DB::table('tblepayment')
                ->where('batch', $id)
                ->groupBy('batch')
                ->sum('VATValue');
            $data['whtsum'] = DB::table('tblepayment')
                ->where('batch', $id)
                ->groupBy('batch')
                ->sum('WHTValue');
            $data['stampdutysum'] = DB::table('tblepayment')
                ->where('batch', $id)
                ->groupBy('batch')
                ->sum('stampduty');
            $data['sigA'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'A')->where('is_capital', '=', 1)->orderBy('rank')->get();
            $data['sigB'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'B')->where('is_capital', '=', 1)->orderBy('rank')->get();
            $data['sig1'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'A')->where('batch', '=', $id)->first();
            $data['sig2'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'B')->where('batch', '=', $id)->first();

            $data['accountDetails'] =  DB::table('tblmandate_address_account')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                ->where('tblmandate_address_account.contractTypeID', '=', $checkCapWithCtType->contract_typeID)
                ->where('tblmandate_address_account.status', '=', 1)
                ->get();
            $data['accountAddress'] =  DB::table('tblmandate_address_account')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                ->where('tblmandate_address_account.id', '=', $checkCapWithCtType->NJCAccount)
                ->first();

            $data['banks'] = DB::table('tblbanklist')->get();
            $data['date'] = $data['status']->date;

            return view('funds.cpo.contractEpaymentToday', $data);
        }

        $data['current_batch'] = $id;
        $data['company'] = DB::table('tblcompany')->first();
        $data['mandate'] = DB::table('tblepayment')
            ->where('mandate_status', '!=', 3)
            ->where('batch', $id)
            //->selectRaw('*,sum(amount) as amt,sum(VATValue) as vat,sum(WHTValue) as wht')
            // ->orderBy('ID', 'desc')
            ->get();
        $data['status'] = DB::table('tblepayment')
            ->where('batch', $id)
            ->first();
        $data['checkApproval'] = DB::table('tblepayment')
            ->where('batch', $id)
            ->where('mandate_status', 3)
            ->count();
        $data['sum'] = DB::table('tblepayment')
            ->where('batch', $id)
            ->groupBy('batch')
            ->sum('amount');
        $data['vatsum'] = DB::table('tblepayment')
            ->where('batch', $id)
            ->groupBy('batch')
            ->sum('VATValue');
        $data['whtsum'] = DB::table('tblepayment')
            ->where('batch', $id)
            ->groupBy('batch')
            ->sum('WHTValue');
        $data['stampdutysum'] = DB::table('tblepayment')
            ->where('batch', $id)
            ->groupBy('batch')
            ->sum('stampduty');
        $data['sigA'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'A')->where('is_capital', '=', 0)->orderBy('rank')->get();
        $data['sigB'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'B')->where('is_capital', '=', 0)->orderBy('rank')->get();
        $data['sig1'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'A')->where('batch', '=', $id)->first();
        $data['sig2'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'B')->where('batch', '=', $id)->first();

        $data['accountDetails'] =  DB::table('tblmandate_address_account')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
            ->where('tblmandate_address_account.contractTypeID', '=', $checkCapWithCtType->contract_typeID)
            ->where('tblmandate_address_account.status', '=', 1)
            ->get();
        $data['accountAddress'] =  DB::table('tblmandate_address_account')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
            ->where('tblmandate_address_account.id', '=', $checkCapWithCtType->NJCAccount)
            ->first();

        // dd($data['mandate'][0]->NJCAccount);

        $data['banks'] = DB::table('tblbanklist')->get();
        $data['date'] = $data['status']->date;

        return view('funds/cpo/epayment', $data);
    }

    public function viewBatchByTransID($id)
    {
        $data['codes'] = DB::table('tblaction_rank')->where('cont_payment_active', '=', 1)->get();
        $data['audited'] = DB::select(
            'SELECT *, 
                    SUM(`amount`) AS sum, 
                    SUM(`VATValue`) AS vsum, 
                    SUM(`WHTValue`) AS wsum,
                    SUM(`stampduty`) AS stampsum
            FROM tblepayment 
            WHERE `date` >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) 
            AND `transactionID` = ? 
            GROUP BY batch',
            [$id]
        );

        $data['company'] = DB::table('tblcompany')->first();

        return view('funds/cpo/batchbytransid', $data);

        // dd($epayment);

    }

    public function exportBatch($id = null)
    {
        $batch = session('batchNo') ?: $id;

        if (!$batch) {
            return redirect()->back()->with('error', 'No batch selected');
        }

        $checkCap = DB::table('tblepayment')
            ->where('mandate_status', '!=', 3)
            ->where('contract_typeID', '=', 4)
            ->where('batch', $batch)
            ->count();

        $checkCapWithCtType = DB::table('tblepayment')
            ->where('mandate_status', '!=', 3)
            ->where('batch', $batch)
            ->first();

        $data = [];

        $data['sum'] = DB::table('tblepayment')
            ->where('batch', $batch)
            ->groupBy('batch')
            ->sum('amount');

        $data['vatsum'] = DB::table('tblepayment')
            ->where('batch', $batch)
            ->groupBy('batch')
            ->sum('VATValue');

        $data['whtsum'] = DB::table('tblepayment')
            ->where('batch', $batch)
            ->groupBy('batch')
            ->sum('WHTValue');

        $data['accountDetails'] = DB::table('tblmandate_address_account')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
            ->where('tblmandate_address_account.contractTypeID', '=', $checkCapWithCtType->contract_typeID ?? 0)
            ->where('tblmandate_address_account.status', '=', 1)
            ->get();

        $data['accountAddress'] = DB::table('tblmandate_address_account')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
            ->where('tblmandate_address_account.id', '=', $checkCapWithCtType->NJCAccount ?? 0)
            ->first();

        // Always fetch individual records for the main table (like breakdown)
        $data['breakdown'] = DB::table('tblepayment')
            ->join('tblbanklist', 'tblbanklist.bank', '=', 'tblepayment.bank')
            ->where('mandate_status', '!=', 3)
            ->where('batch', $batch)
            ->select('*', 'tblepayment.bank as BankName')
            ->get();

        // Grouped mandate (if needed for other sections, but we'll use breakdown for data)
        if ($checkCap > 0) {
            $data['mandate'] = DB::table('tblepayment')
                ->where('mandate_status', '!=', 3)
                ->where('batch', $batch)
                ->where('contract_typeID', '=', 4)
                ->groupBy('bank')
                ->selectRaw('*, count(bank) as NumOfBanks, tblepayment.bank as bankName, sum(amount) as totalAmount, sum(VATValue) as vat, sum(WHTValue) as tax')
                ->get();
        } else {
            $data['mandate'] = DB::table('tblepayment')
                ->where('mandate_status', '!=', 3)
                ->where('batch', $batch)
                ->get();
        }

        return Excel::download(
            new CbnEpaymentExport($data),
            "SCN_EPayment_{$batch}_" . date('Ymd_His') . ".xlsx"
        );
    }

    public function updateAccountNo(Request $request)
    {
        $batch = $request['batch'];
        $id = $request['epaymentID'];
        $acct = $request['accountNo'];
        $bank = $request['bank'];
        // dd($request->all());
        DB::table('tblepayment')
            ->where('ID', '=', $id)
            ->update([
                'accountName' => strtoupper($request['accountName']),
                'accountNo'  => $acct,
                'bank'       => $bank,

            ]);
        return redirect('/view/batch/' . $batch);
    }

    public function updateBatchNo(Request $request)
    {
        $batch = $request['batch'];
        $newBatch = $request['newBatch'];

        DB::table('tblepayment')
            ->where('batch', '=', $batch)
            ->update([

                'adjusted_batch'  => $newBatch,

            ]);
        return redirect('/batch/search')->with('msg', 'Successfully Updated');
    }

    public function esMandateView($id)
    {
        $data['current_batch'] = $id;
        $data['company'] = DB::table('tblcompany')->first();
        $threshold = DB::table('tblvoucherThreshold')->first();
        $data['mandate'] = DB::table('tblepayment')
            ->join('tblpaymentTransaction', 'tblpaymentTransaction.ID', '=', 'tblepayment.transactionID')
            ->where('tblpaymentTransaction.totalPayment', '>', $threshold->amount)
            ->where('tblepayment.mandate_status', '!=', 3)
            ->where('tblepayment.batch', $id)
            //->selectRaw('*,sum(amount) as amt,sum(VATValue) as vat,sum(WHTValue) as wht')
            ->get();
        $data['status'] = DB::table('tblepayment')
            ->where('batch', $id)
            ->first();
        $data['checkApproval'] = DB::table('tblepayment')
            ->where('batch', $id)
            ->where('mandate_status', 3)
            ->count();
        $data['sum'] = DB::table('tblepayment')
            ->where('batch', $id)
            ->groupBy('batch')
            ->sum('amount');
        $data['vatsum'] = DB::table('tblepayment')
            ->where('batch', $id)
            ->groupBy('batch')
            ->sum('VATValue');
        $data['whtsum'] = DB::table('tblepayment')
            ->where('batch', $id)
            ->groupBy('batch')
            ->sum('WHTValue');
        $data['sigA'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'A')->orderBy('rank')->get();
        $data['sigB'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'B')->orderBy('rank')->get();
        return view('funds/cpo/epaymentES', $data);
    }

    public function addComment($contractID, $transactionID, $comment)
    {
        DB::table('tblcomments')->insert([
            'commenttypeID'     => 1,
            'affectedID'        => $contractID,
            'paymentID'         => $transactionID,
            'commenttypeID'     => 1,
            'username'          => Auth::user()->username,
            'comment'           => $comment,

        ]);
    }

    public function cpoReject(Request $request)
    {
        $transid = $request['transid'];
        $remark = $request['remark'];
        $returnTo = $request['attension'];
        $date = date('Y-m-d');

        if ($returnTo == 1) {
            $value = "liabilityStatus";
        } elseif ($returnTo == 2) {
            $value = "checkStatus";
        } elseif ($returnTo == 3) {
            $value = "auditStatus";
        }
        $status = 2;
        $id = DB::table('tblpaymentTransaction')->where('ID', '=', $transid)->first();
        if ($returnTo == 1 || $returnTo == 0) {
            $status = 0;
            $this->VotebookUpdate($id->economicCodeID, $transid, $remark, $id->totalPayment, $date, 5, $id->period);

            DB::table('tblpaymentTransaction')
                ->where('ID', '=', $transid)
                ->update(array(
                    'vstage'            => $returnTo,
                    'returnstatus'      => 1,
                    'liabilityStatus'   => 0,
                    'checkbyStatus'       => 0,
                    'auditStatus'       => 0,
                    'status'            => $status,
                    'isrejected'        => 1,
                ));
            $contractID = $id->contractID;
            $this->addComment($contractID, $transid, $remark);
            return redirect('/cpo/report')->with('msg', 'Successfully Reject');
        } else {
            if ($returnTo == 2) {
                DB::table('tblpaymentTransaction')
                    ->where('ID', '=', $transid)
                    ->update(array(
                        'vstage'          => $returnTo,
                        'returnstatus'      => 1,
                        'checkbyStatus'       => 0,
                        'auditStatus'       => 0,
                        'isrejected'        => 1,
                    ));

                $contractID = $id->contractID;
                $this->addComment($contractID, $transid, $remark);
            }
            if ($returnTo == 3) {
                DB::table('tblpaymentTransaction')
                    ->where('ID', '=', $transid)
                    ->update(array(
                        'vstage'          => $returnTo,
                        'returnstatus'      => 1,
                        'checkbyStatus'       => 0,
                        'isrejected'        => 1,

                    ));

                $contractID = $id->contractID;
                $this->addComment($contractID, $transid, $remark);
            }
            return redirect('cpo/report')->with('msg', 'Successfully Reject');
        }
    }

    public function getPhone(Request $request)
    {
        $data = DB::table('tblmandatesignatoryprofiles')->where('id', '=', $request['signid'])->first();
        //$getBatch = DB::table('tblmandatesignatoryprofiles')->where('batchId','=',$request['batch'])->first();
        $countBatch = DB::table('tblpayment_signatories')->where('signatory_type', '=', $data->signatory_type)->where('batch', '=', $request['batch'])->count();
        if ($countBatch == 1) {
            DB::table('tblpayment_signatories')->where('batch', '=', $request['batch'])->where('signatory_type', '=', $data->signatory_type)->update([
                'batch'   => $request['batch'],
                'signatoryId'   => $request['signid'],
                'signatory_type'   => $data->signatory_type,
                'name'   => $data->Name,
                'phone'   => $data->phone,

            ]);
        } else {
            DB::table('tblpayment_signatories')->insert([
                'batch'   => $request['batch'],
                'signatoryId'   => $request['signid'],
                'signatory_type'   => $data->signatory_type,
                'name'   => $data->Name,
                'phone'   => $data->phone,

            ]);
        }
        return response()->json($data);
    }

    public function nextAction(Request $request)
    {
        $this->validate($request, [
            'instruction'     => 'required|string',
            'nextAction'      => 'required',
        ]);
        $batch = $request['batch'];
        $comment = $request['instruction'];
        $to   = $request['nextAction'];
        DB::table('tblepayment')->where('batch', '=', $request['batch'])->update([
            'next_action'   => $request['nextAction'],
        ]);

        DB::table('tblmandate_comments')
            ->insert(array(
                'batch' => $batch,
                'to_who' => $to,
                'updated_at' => date('Y-m-d'),
                'by_who' => auth::user()->username,
                'comment' => $comment,
            ));
        return redirect('funds/batch/search')->with('msg', 'Action Successfull');
    }

    public function markBankPaymentAsPaid(Request $request)
    {
        // dd($request->all());
        try {
            //code...
            $batch = $request['batch2'];
            $comment = $request['instruction'];
            $epaymentRecords = DB::table('tblepayment')->where('batch', '=', $request['batch2'])->get();
            foreach ($epaymentRecords as $key => $record) {
                // 1️⃣ Insert the MAIN record into new table
                $mainId = DB::table('tblepayment_bank_paid')->insertGetId([
                    'transactionID'  => $record->transactionID,
                    'contractor' => $record->contractor ?? null,
                    'amount'     => $record->amount,
                    'accountNo'     => $record->accountNo,
                    'bank'     => $record->bank,
                    'bank_branch'     => $record->bank_branch,
                    'date'     => $record->date,
                    'batch'      => $record->batch,
                    'mandate_status'     => $record->mandate_status,
                    'purpose'     => $record->purpose,
                    'remark'    => $comment,
                    'contract_typeID'     => $record->contract_typeID,
                    'NJCAccount'     => $record->NJCAccount,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'payment_status' => 0,
                    'accountName' => $record->accountName,
                    'bank_sortcode' => $record->bank_sortcode,
                ]);

                // 2️⃣ Check VATValue > 0 → insert VAT row
                if ($record->VATValue > 0) {
                    DB::table('tblepayment_bank_paid')->insert([
                        'transactionID'  => $record->transactionID,
                        'contractor' => $record->vat_payee ?? null,
                        'amount'     => $record->VATValue,
                        'accountNo'     => $record->vat_accountNo,
                        'bank'     => $record->vat_bank,
                        'bank_branch'     => $record->vat_bank_branch,
                        'date'     => $record->date,
                        'batch'      => $record->batch,
                        'mandate_status'     => $record->mandate_status,
                        'purpose'     => "FIRS Remittance (VAT)",
                        'remark'    => $comment,
                        'contract_typeID'     => $record->contract_typeID,
                        'NJCAccount'     => $record->NJCAccount,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'payment_status' => 0,
                        'accountName' => 'FIRS TAX PROMAX VAT',
                        'bank_sortcode' => $record->vat_sortcode,
                    ]);
                }

                // 3️⃣ Check WHTValue > 0 → insert WHT row
                if ($record->WHTValue > 0) {
                    DB::table('tblepayment_bank_paid')->insert([
                        'transactionID'  => $record->transactionID,
                        'contractor' => $record->wht_payee ?? null,
                        'amount'     => $record->WHTValue,
                        'accountNo'     => $record->wht_accountNo,
                        'bank'     => $record->wht_bank,
                        'bank_branch'     => $record->wht_bank_branch,
                        'date'     => $record->date,
                        'batch'      => $record->batch,
                        'mandate_status'     => $record->mandate_status,
                        'purpose'     => "FIRS Remittance (TAX)",
                        'remark'    => $comment,
                        'contract_typeID'     => $record->contract_typeID,
                        'NJCAccount'     => $record->NJCAccount,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'payment_status' => 0,
                        'accountName' => 'FIRS TAX PROMAX WHT',
                        'bank_sortcode' => $record->wht_sortcode,
                    ]);
                }
            }
            //update mark as paid
            DB::table('tblepayment')->where('batch', '=', $request['batch2'])->update([
                'is_paid_from_bank' => 1
            ]);

            return redirect('batch/search')->with('msg', 'Successfully Marked Payment as Paid');
        } catch (\Throwable $th) {
            //throw $th;
            return redirect('batch/search')->with('err', 'Failed to Mark Payment as Paid');
        }
    }

    public function accountDetails()
    {
        $data['banks'] = DB::table('tblbanklist')->get();
        $data['contracttypes'] = DB::table('tblcontractType')->get();
        $data['accounts'] = DB::table('tblmandate_address_account')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
            ->join('tblcontractType', 'tblcontractType.ID', '=', 'tblmandate_address_account.contractTypeID')
            ->select(
                'tblmandate_address_account.*',
                'tblbanklist.bank',
                'tblcontractType.contractType'
            )
            ->get();
        return view('funds.mandate.accountDetails', $data);
    }

    public function saveAccountDetails(Request $request)
    {
        $this->validate($request, [
            'bank'           => 'required',
            'accountNo'      => 'required',
            'contractTypeID' => 'required',
            'address'        => 'required',
        ]);

        // Check if bank + account number combination already exists
        $existingRecord = DB::table('tblmandate_address_account')
            ->where('bankId', $request['bank'])
            ->where('account_no', $request['accountNo'])
            ->first();

        if ($existingRecord) {
            return redirect()->back()
                ->withInput()
                ->with('err', 'This bank account number already exists! Bank and account number combination must be unique.');
        }

        // Insert new record with status = 1 (active)
        DB::table('tblmandate_address_account')->insert([
            'bankId'         => $request['bank'],
            'account_no'     => $request['accountNo'],
            'contractTypeID' => $request['contractTypeID'],
            'address'        => $request['address'],
            'status'         => 1,
            'updated_at'     => date('Y-m-d'),
            // 'created_at'     => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()
            ->with('msg', 'Account added successfully');
    }

    public function editAccountDetails($id)
    {
        $data['banks'] = DB::table('tblbanklist')->get();
        $data['contracttypes'] = DB::table('tblcontractType')->get();

        // Get the specific account to edit
        $data['account'] = DB::table('tblmandate_address_account')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
            ->join('tblcontractType', 'tblcontractType.ID', '=', 'tblmandate_address_account.contractTypeID')
            ->where('tblmandate_address_account.id', $id)
            ->first();

        if (!$data['account']) {
            return redirect('account/details')->with('err', 'Account not found');
        }

        return view('funds.mandate.editAccountDetails', $data);
    }

    // public function updateAccountDetails(Request $request, $id)
    // {
    //     $messages = [
    //         'bank.unique' => 'This bank already exists with another record.',
    //     ];

    //     $validator = \Validator::make($request->all(), [
    //         'bank'           => 'required|unique:tblmandate_address_account,bankId,' . $id, // Unique except current record
    //         'accountNo'      => 'required',
    //         'contractTypeID' => 'required',
    //         'address'        => 'required',
    //     ], $messages);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     // Get current record data
    //     $currentRecord = DB::table('tblmandate_address_account')
    //         ->where('id', $id)
    //         ->first();

    //     // Check if contract type is being changed to one that already exists with another bank
    //     if ($request['contractTypeID'] != $currentRecord->contractTypeID) {
    //         $existingContractType = DB::table('tblmandate_address_account')
    //             ->where('contractTypeID', $request['contractTypeID'])
    //             ->where('status', 1) // Check only active records
    //             ->where('id', '!=', $id) // Exclude current record
    //             ->first();

    //         // If contract type already exists with another bank, disable that record
    //         if ($existingContractType) {
    //             DB::table('tblmandate_address_account')
    //                 ->where('contractTypeID', $request['contractTypeID'])
    //                 ->where('id', '!=', $id) // Don't disable current record
    //                 ->update(['status' => 0]);
    //         }
    //     }

    //     // Update current record and set status to active
    //     $update = DB::table('tblmandate_address_account')
    //         ->where('id', $id)
    //         ->update([
    //             'bankId'         => $request['bank'],
    //             'account_no'     => $request['accountNo'],
    //             'contractTypeID' => $request['contractTypeID'],
    //             'address'        => $request['address'],
    //             'status'         => 1, // Set to active
    //             'updated_at'     => date('Y-m-d'),
    //         ]);



    //     return redirect('account/details')->with('msg', 'Account updated successfully');
    // }

    public function updateAccountDetails(Request $request, $id)
    {
        $validator = \Validator::make($request->all(), [
            'bank'           => 'required',
            'accountNo'      => 'required',
            'contractTypeID' => 'required',
            'address'        => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if bank + account number combination already exists (excluding current record)
        $existingRecord = DB::table('tblmandate_address_account')
            ->where('bankId', $request['bank'])
            ->where('account_no', $request['accountNo'])
            ->where('id', '!=', $id)
            ->first();

        if ($existingRecord) {
            return redirect()->back()
                ->withInput()
                ->with('err', 'This bank account number already exists! Bank and account number combination must be unique.');
        }

        // Update current record
        $update = DB::table('tblmandate_address_account')
            ->where('id', $id)
            ->update([
                'bankId'         => $request['bank'],
                'account_no'     => $request['accountNo'],
                'contractTypeID' => $request['contractTypeID'],
                'address'        => $request['address'],
                'updated_at'     => date('Y-m-d'),
            ]);

        return redirect('account/details')->with('msg', 'Account updated successfully');
    }

    // public function toggleAccountStatus(Request $request, $id)
    // {
    //     // Validate request
    //     $request->validate([
    //         'status' => 'required|in:0,1'
    //     ]);

    //     // Get the current record
    //     $record = DB::table('tblmandate_address_account')->where('id', $id)->first();

    //     if (!$record) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Record not found'
    //         ], 404);
    //     }

    //     $newStatus = $request->status;

    //     DB::beginTransaction();

    //     try {
    //         // If activating a record
    //         if ($newStatus == 1) {
    //             // Deactivate ALL other active records with same contract type
    //             DB::table('tblmandate_address_account')
    //                 ->where('contractTypeID', $record->contractTypeID)
    //                 ->where('id', '!=', $id)
    //                 ->where('status', 1)
    //                 ->update([
    //                     'status' => 0,
    //                     'updated_at' => date('Y-m-d')
    //                 ]);

    //             // Also deactivate any record with same bank that's active
    //             // This prevents the same bank from being active in multiple contract types
    //             DB::table('tblmandate_address_account')
    //                 ->where('bankId', $record->bankId)
    //                 ->where('id', '!=', $id)
    //                 ->where('status', 1)
    //                 ->update([
    //                     'status' => 0,
    //                     'updated_at' => date('Y-m-d')
    //                 ]);
    //         } 
    //         // If deactivating a record AND it's currently active
    //         elseif ($record->status == 1) {
    //             // Find another record with same contract type to activate
    //             $alternativeRecord = DB::table('tblmandate_address_account')
    //                 ->where('contractTypeID', $record->contractTypeID)
    //                 ->where('id', '!=', $id)
    //                 ->where('status', 0)
    //                 ->orderBy('id', 'asc')
    //                 ->first();

    //             if ($alternativeRecord) {
    //                 // Before activating, check if this bank is already active elsewhere
    //                 $bankAlreadyActive = DB::table('tblmandate_address_account')
    //                     ->where('bankId', $alternativeRecord->bankId)
    //                     ->where('id', '!=', $alternativeRecord->id)
    //                     ->where('status', 1)
    //                     ->exists();

    //                 if (!$bankAlreadyActive) {
    //                     // Activate the alternative record
    //                     DB::table('tblmandate_address_account')
    //                         ->where('id', $alternativeRecord->id)
    //                         ->update([
    //                             'status' => 1,
    //                             'updated_at' => date('Y-m-d')
    //                         ]);
    //                 }
    //             }
    //         }

    //         // Update the current record
    //         $updated = DB::table('tblmandate_address_account')
    //             ->where('id', $id)
    //             ->update([
    //                 'status' => $newStatus,
    //                 'updated_at' => date('Y-m-d')
    //             ]);

    //         DB::commit();

    //         if ($updated) {
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Status updated successfully'
    //             ]);
    //         } else {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Failed to update status'
    //             ]);
    //         }

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An error occurred: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function toggleAccountStatus(Request $request, $id)
    {
        // Validate request
        $request->validate([
            'status' => 'required|in:0,1'
        ]);

        // Get the current record
        $record = DB::table('tblmandate_address_account')->where('id', $id)->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found'
            ], 404);
        }

        $newStatus = $request->status;

        // SIMPLE UPDATE: Just toggle the status of this specific record
        // Multiple records can be active at the same time
        $updated = DB::table('tblmandate_address_account')
            ->where('id', $id)
            ->update([
                'status' => $newStatus,
                'updated_at' => date('Y-m-d')
            ]);

        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status'
            ]);
        }
    }

    public function checkBankAccountUnique(Request $request)
    {
        $exists = DB::table('tblmandate_address_account')
            ->where('bankId', $request->bankId)
            ->where('account_no', $request->accountNo)
            ->exists();

        return response()->json(['exists' => $exists]);
    }

    public function checkBankAccountUniqueEdit(Request $request)
    {
        $exists = DB::table('tblmandate_address_account')
            ->where('bankId', $request->bankId)
            ->where('account_no', $request->accountNo)
            ->where('id', '!=', $request->recordId) // Exclude current record
            ->exists();

        return response()->json(['exists' => $exists]);
    }

    public function delete($id)
    {
        DB::table('tblmandate_address_account')->where('id', '=', $id)->delete();
        return back()->with('msg', 'Successfully Deleted');
    }

    public function getAccountAddress(Request $request)
    {
        $acct = $request['accountNo'];
        $data = DB::table('tblmandate_address_account')->where('account_no', '=', $acct)->first();
        return response()->json($data);
    }

    public function referenceUpdate(Request $request)
    {

        $batch = $request['batch'];
        $newBatch = $request['newBatch'];

        $data = DB::table('tblepayment')
            ->where('batch', '=', $batch)
            ->update([

                'adjusted_batch'  => $newBatch,

            ]);
        return response()->json($data);
    }

    public function referenceNo(Request $request)
    {

        $batch = $request['batch'];
        $newRef = $request['newBatch'];

        $data = DB::table('tblepayment')
            ->where('batch', '=', $batch)
            ->update([

                'capital_refno'  => $newRef,

            ]);
        return response()->json($data);
    }

    public function dateUpdate(Request $request)
    {

        $batch       = $request['batch'];
        $date        = date('Y-m-d', strtotime(trim($request['preparedate'])));

        $data = DB::table('tblepayment')
            ->where('batch', '=', $batch)
            ->update([
                'date'  => $date,
            ]);
        return response()->json($data);
    }

    public function narrationUpdate(Request $request)
    {

        $narration = $request['narration'];
        $id = $request['eid'];

        $data = DB::table('tblepayment')
            ->where('ID', '=', $id)
            ->update([
                'purpose'  => $narration,
            ]);
        return back()->with('msg', 'Process Completed');
    }

    public function purposeUpdate(Request $request)
    {

        $narration = $request['purpose'];
        $bank = $request['bankname'];

        $data = DB::table('tblepayment')
            ->where('bank', '=', $bank)
            ->update([
                'capital_bank_purpose'  => $narration,
            ]);
        return back()->with('msg', 'Process Completed');
    }




    public function payeeAccount(Request $request)
    {
        $paye = $request['paye'];
        $id = $request['epaymentID'];
        $batch = $request['batch'];

        $this->validate($request, [
            'bank'                 => 'required',
            'accountNo'                 => 'required',

        ]);

        if ($paye == "tax") {
            $data['save'] = DB::table('tblepayment')->where('ID', '=', $id)->update([
                'wht_payee'        => $request['beneficiary'],
                'wht_accountNo'    => $request['accountNo'],
                'wht_bank'         => $request['bank'],
            ]);
        }
        if ($paye == "vat") {
            $data['save'] = DB::table('tblepayment')->where('ID', '=', $id)->update([
                'vat_payee'        => $request['beneficiary'],
                'vat_accountNo'    => $request['accountNo'],
                'vat_bank'         => $request['bank'],
            ]);
        }
        return redirect('view/batch/' . $batch);
    }


    /************************ Merged Details Update **********************************/

    public function referenceUpdateMerged(Request $request)
    {

        $batch     = $request['batch'];
        $newBatch  = $request['newBatch'];

        $data = DB::table('tblmerged_payment')
            ->where('batch', '=', $batch)
            ->update([

                'adjusted_batch'  => $newBatch,

            ]);
        return response()->json($data);
    }

    public function narrationUpdateMerged(Request $request)
    {

        $narration = $request['narration'];
        $id = $request['eid'];

        $data = DB::table('tblmerged_payment')
            ->where('ID', '=', $id)
            ->update([
                'purpose'  => $narration,
            ]);
        return back()->with('msg', 'Process Completed');
    }


    public function payeeAccountMerged(Request $request)
    {
        $paye = $request['paye'];
        $id = $request['epaymentID'];
        $batch = $request['batch'];

        $this->validate($request, [
            'bank'                 => 'required',
            'accountNo'                 => 'required',

        ]);

        if ($paye == "tax") {
            $data['save'] = DB::table('tblmerged_payment')->where('ID', '=', $id)->update([
                'wht_payee'        => $request['beneficiary'],
                'wht_accountNo'    => $request['accountNo'],
                'wht_bank'         => $request['bank'],
            ]);
        }
        if ($paye == "vat") {
            $data['save'] = DB::table('tblmerged_payment')->where('ID', '=', $id)->update([
                'vat_payee'        => $request['beneficiary'],
                'vat_accountNo'    => $request['accountNo'],
                'vat_bank'         => $request['bank'],
            ]);
        }
        return redirect('funds/view/batch/' . $batch);
    }

    public function accountDetailsMerge()
    {
        $data['banks'] = DB::table('tblbanklist')->get();
        $data['accounts'] = DB::table('tblmandate_address_account')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
            ->get();
        return view('funds.mandate.accountDetails', $data);
    }

    public function updateAccountNoMerge(Request $request)
    {
        $batch = $request['actbatch'];
        $id = $request['epaymentID'];
        $acct = $request['accountNo'];
        $bank = $request['bank'];
        $contr = $request['contractor'];
        DB::table('tblmerged_payment')
            ->where('contractor', '=', $contr)
            ->where('adjusted_batch', '=', $batch)
            ->update([

                'accountNo'  => $acct,
                'bank'       => $bank,

            ]);
        return redirect('view/merge-payments/' . $batch);
    }

    public function updateBatchNoMerged(Request $request)
    {
        $batch = $request['batch'];
        $newBatch = $request['newBatch'];

        DB::table('tblmerged_payment')
            ->where('batch', '=', $batch)
            ->update([

                'adjusted_batch'  => $newBatch,

            ]);
        return redirect('/batch/search')->with('msg', 'Successfully Updated');
    }

    public function edit($id)
    {
        $data['banks'] = DB::table('tblbanklist')->get();
        $data['acct'] = DB::table('tblmandate_address_account')->where('id', '=', $id)->first();
        return view('funds.mandate.editAccount', $data);
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'bank'                    => 'required',
            'accountNo'               => 'required',
            'address'                 => 'required',

        ]);
        $data['save'] = DB::table('tblmandate_address_account')->where('id', '=', $request['id'])->update([
            'bankId'        => $request['bank'],
            'account_no'    => $request['accountNo'],
            'address'       => $request['address'],
            'status'       => $request['status'],
        ]);
        return redirect('/account/details')->with('msg', 'Action Completed Successfully');
    }

    public function autocomplete(Request $request)
    {
        $query = $request->input('query');
        $search = DB::table('tblepayment')
            ->where('transactionID', 'LIKE', '%' . $query . '%')
            ->take(15)
            ->get();
        $return_array = null;
        foreach ($search as $s) {
            $return_array[]  =  ["value" => "The Voucher batch is :- " . $s->adjusted_batch, "data" => $s->batch];
        }
        return response()->json(array("suggestions" => $return_array));
    }

    /************** CAPITAL CONTRACT MANDATE GENERATION **************/


    public function capitalMandateTest($id)
    {
        $count = DB::table('tblepayment')->where('mandate_status', '!=', 3)->where('batch', session('batchNo'))->count();
        $data['cbn'] = DB::table('tblmandate_address_account')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
            ->where('tblmandate_address_account.bankId', '=', 38)
            ->first();

        if (session('batchNo') != '' && $count != 0) {
            $data['current_batch'] = session('batchNo');

            $data['company'] = DB::table('tblcompany')->first();
            $data['mandate'] = DB::table('tblepayment')
                ->where('mandate_status', '!=', 3)
                ->where('batch', session('batchNo'))
                ->groupBy('bank')
                ->selectRaw('*,count(bank) as NumOfBanks,tblepayment.bank as bankName, sum(amount) as totalAmount')
                ->get();
            $data['breakdown'] = DB::table('tblepayment')
                ->join('tblbanklist', 'tblbanklist.bank', '=', 'tblepayment.bank')
                ->where('mandate_status', '!=', 3)
                ->where('batch', session('batchNo'))
                ->select('*', 'tblepayment.bank as BankName')
                ->get();

            $data['status'] = DB::table('tblepayment')
                ->where('batch', session('batchNo'))
                ->first();
            $data['checkApproval'] = DB::table('tblepayment')
                ->where('batch', session('batchNo'))
                ->where('mandate_status', 3)
                ->count();
            $data['sum'] = DB::table('tblepayment')
                ->where('batch', session('batchNo'))
                ->groupBy('batch')
                ->sum('amount');
            $data['vatsum'] = DB::table('tblepayment')
                ->where('batch', session('batchNo'))
                ->groupBy('batch')
                ->sum('VATValue');
            $data['whtsum'] = DB::table('tblepayment')
                ->where('batch', session('batchNo'))
                ->groupBy('batch')
                ->sum('WHTValue');
            $data['sigA'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'A')->where('is_capital', '=', 1)->orderBy('rank')->get();
            $data['sigB'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'B')->where('is_capital', '=', 1)->orderBy('rank')->get();
            $data['sig1'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'A')->where('is_capital', '=', 1)->where('batch', '=', session('batchNo'))->first();
            $data['sig2'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'B')->where('is_capital', '=', 1)->where('batch', '=', session('batchNo'))->first();

            $data['accountDetails'] =  DB::table('tblmandate_address_account')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                ->where('tblmandate_address_account.status', '=', 1)
                ->get();
            $data['accountAddress'] =  DB::table('tblmandate_address_account')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                ->where('tblmandate_address_account.status', '=', 1)
                ->first();

            $data['banks'] = DB::table('tblbanklist')->get();

            $data['date'] = $data['status']->date;

            return view('funds/cpo/contractEpayment', $data);
        } else {
            $data['current_batch'] = $id;
            $data['company'] = DB::table('tblcompany')->first();
            $data['mandate'] = DB::table('tblepayment')
                ->where('mandate_status', '!=', 3)
                ->where('batch', $id)
                ->groupBy('bank')
                ->selectRaw('*,count(bank) as NumOfBanks,tblepayment.bank as bankName, sum(amount) as totalAmount, sum(VATValue) as vat,sum(WHTValue) as tax')
                ->get();
            $data['breakdown'] = DB::table('tblepayment')
                ->join('tblbanklist', 'tblbanklist.bank', '=', 'tblepayment.bank')
                ->where('mandate_status', '!=', 3)
                ->where('batch', $id)
                ->select('*', 'tblepayment.bank as BankName')
                ->get();

            $data['status'] = DB::table('tblepayment')
                ->where('batch', $id)
                ->first();
            $data['checkApproval'] = DB::table('tblepayment')
                ->where('batch', $id)
                ->where('mandate_status', 3)
                ->count();
            $data['sum'] = DB::table('tblepayment')
                ->where('batch', $id)
                ->groupBy('batch')
                ->sum('amount');
            $data['vatsum'] = DB::table('tblepayment')
                ->where('batch', $id)
                ->groupBy('batch')
                ->sum('VATValue');
            $data['whtsum'] = DB::table('tblepayment')
                ->where('batch', $id)
                ->groupBy('batch')
                ->sum('WHTValue');
            $data['sigA'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'A')->orderBy('rank')->get();
            $data['sigB'] = DB::table('tblmandatesignatoryprofiles')->where('signatory_type', '=', 'B')->orderBy('rank')->get();
            $data['sig1'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'A')->where('batch', '=', $id)->first();
            $data['sig2'] = DB::table('tblpayment_signatories')->where('signatory_type', '=', 'B')->where('batch', '=', $id)->first();

            $data['accountDetails'] =  DB::table('tblmandate_address_account')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                ->where('tblmandate_address_account.status', '=', 1)
                ->get();
            $data['accountAddress'] =  DB::table('tblmandate_address_account')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                ->where('tblmandate_address_account.status', '=', 1)
                ->first();

            $data['banks'] = DB::table('tblbanklist')->get();
            $data['date'] = $data['status']->date;

            return view('funds/cpo/contractEpayment', $data);
        }
    }

    public function addCommentTest($contractID, $transactionID, $comment)
    {
        DB::table('tblcomments2')->insert([
            'commenttypeID'     => 1,
            'affectedID'        => $contractID,
            'paymentID'         => $transactionID,
            'commenttypeID'     => 1,
            'username'          => Auth::user()->username,
            'comment'           => $comment,

        ]);
    }

    public function displayVoucherTest()
    {
        $data['company'] = DB::table('tblcompany')->first();
        $data['audited'] = DB::table('tblpaymentTransaction2')
            ->join('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction2.companyID')
            ->join('tblcontractType', 'tblcontractType.id', '=', 'tblpaymentTransaction2.contractTypeID')
            ->leftJoin('tblbanklist', 'tblcontractor.Banker', '=', 'tblbanklist.bankID')
            ->where('tblpaymentTransaction2.auditStatus', '=', 1)
            ->where('tblpaymentTransaction2.cpo_payment', '=', 0)
            ->select('*', 'tblpaymentTransaction2.ID as transID')
            ->get();

        $data['totalRows'] = count((array)$data['audited']);

        return view('funds/cpo/displayReportTest', $data);
    }

    public function cpoRejectTest(Request $request)
    {
        $transid = $request['transid'];
        $remark = $request['remark'];
        $returnTo = $request['attension'];
        $date = date('Y-m-d');

        if ($returnTo == 1) {
            $value = "liabilityStatus";
        } elseif ($returnTo == 2) {
            $value = "checkStatus";
        } elseif ($returnTo == 3) {
            $value = "auditStatus";
        }
        $status = 2;
        $id = DB::table('tblpaymentTransaction2')->where('ID', '=', $transid)->first();
        if ($returnTo == 1 || $returnTo == 0) {
            $status = 0;
            //$this->VotebookUpdate($id->economicCodeID,$transid,$remark,$id->totalPayment,$date,5,$id->period);

            DB::table('tblpaymentTransaction2')
                ->where('ID', '=', $transid)
                ->update(array(
                    'vstage'            => $returnTo,
                    'returnstatus'      => 1,
                    'liabilityStatus'   => 0,
                    'checkbyStatus'       => 0,
                    'auditStatus'       => 0,
                    'status'            => $status,
                    'isrejected'        => 1,
                ));
            $contractID = $id->contractID;
            $this->addCommentTest($contractID, $transid, $remark);
            return redirect('/cpo/report')->with('msg', 'Successfully Reject');
        } else {
            if ($returnTo == 2) {
                DB::table('tblpaymentTransaction2')
                    ->where('ID', '=', $transid)
                    ->update(array(
                        'vstage'          => $returnTo,
                        'returnstatus'      => 1,
                        'checkbyStatus'       => 0,
                        'auditStatus'       => 0,
                        'isrejected'        => 1,
                    ));

                $contractID = $id->contractID;
                $this->addCommentTest($contractID, $transid, $remark);
            }
            if ($returnTo == 3) {
                DB::table('tblpaymentTransaction2')
                    ->where('ID', '=', $transid)
                    ->update(array(
                        'vstage'          => $returnTo,
                        'returnstatus'      => 1,
                        'checkbyStatus'       => 0,
                        'isrejected'        => 1,

                    ));

                $contractID = $id->contractID;
                $this->addCommentTest($contractID, $transid, $remark);
            }
            return redirect('/display/vouchers')->with('msg', 'Successfully Reject');
        }
    } //end




    //Assign CPO unprocessed
    public function unprocessedCPOVoucher(Request $request)
    {

        $data['warning']        = '';
        $data['success']        = '';
        $data['error']          = '';

        //Assign user to voucher

        //DB::table('tblpaymentTransaction')->where('ID', $request['vid'])->update(['cpo_assign_userID'    => $request['as_user'],]);

        if (DB::table('tblpaymentTransaction')->where('ID', $request['vid'])->update(['cpo_assign_userID'    => ($request['as_user'] ? $request['as_user'] : null),])) {
            $taskscheduled = $this->UpdateAlertTable("CPO Voucher Assigned", 'assign-voucher-cpo', $request['as_user'], '0', 'tblpaymentTransaction', $request['vid'], 1);
        }
        //decline and Query documentent
        if (isset($_POST['decline'])) {
            $id = $request['vid'];
            $this->validate($request, [
                'vid'       => 'required',
                'comment'  => 'required'
            ]);

            //pitoff
            if (DB::table('tblpaymentTransaction')->where('ID', $id)->update([
                'checkbyStatus'         => 2,
                'dateCheck'             => '',
                'checkBy'                => '',
                'liabilityStatus'         => 0,
                'vstage'                 => 3 //audit
                ,
                'isrejected'             => 1,
                'status'                 => 0,
            ])) {
                $Vdetails = DB::table('tblpaymentTransaction')->where('ID', $id)->first();
                $remark = $Vdetails->paymentDescription . " Rejected for " . $request['comment'] . " at auditing stage by: " . Auth::user()->name;
                $this->VotebookUpdate($Vdetails->economicCodeID, $Vdetails->ID, $remark, $Vdetails->totalPayment, Date('Y-m-d'), 5);
                $data['success'] = "Voucher has been rejected successfully";
                $theid = $id;
                $Vdetails->contractID;
                $user = Auth::user()->username;
                $commenttypeID = 2;
                $comment = trim($request['comment']) . ": Rejected by " . Auth::user()->name . " at audit stage";
                DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $Vdetails->contractID, 'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
                DB::table('tblcomments')->where('affectedID', '=', $Vdetails->contractID)->update(['is_cpo_comment' => 0]);
                $taskscheduled = $this->UpdateAlertTable("Voucher Rejection", 'occheckby/voucher', '0', 'OC', 'tblpaymentTransaction', $id, 1);
                $this->instanceFunction24->addLogg("Voucher with ID: $id Rejected with Reason:$comment", "Voucher with ID: $id Rejected");
            } else {
                $data['error'] = "Whoops! something went wrong please try again";
            }
        }
        if (isset($_POST['moredocument'])) {
            $id = $request['vid'];
            $this->validate($request, [
                'vid'       => 'required',
                'comment'  => 'required'
            ]);
            if (DB::table('tblpaymentTransaction')->where('ID', $id)->update([
                'is_need_more_doc' => 1,
            ])) {
                $Vdetails = DB::table('tblpaymentTransaction')->where('ID', $id)->first();
                $remark = $Vdetails->paymentDescription . " Rejected for " . $request['comment'] . " at auditing stage by: " . Auth::user()->name;
                $data['success'] = "Voucher has been for additional document";
                $theid = $id;
                $Vdetails->contractID;
                $user = Auth::user()->username;
                $commenttypeID = 2;
                $comment = trim($request['comment']) . ": queried by " . Auth::user()->name . " at audit stage";
                DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $Vdetails->contractID, 'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
                DB::table('tblcomments')->where('affectedID', '=', $Vdetails->contractID)->update(['is_cpo_comment' => 0]);
                $taskscheduled = $this->UpdateAlertTable("Queried Voucher", 'queried/voucher', '0', 'OC', 'tblpaymentTransaction', $id, 1);
                $this->instanceFunction24->addLogg("Voucher with ID: $id queried with reason:$comment", "Voucher with ID: $id queried");
            } else {
                $data['error'] = "Whoops! something went wrong please try again";
            }
        } //

        //Get Data on page load
        $data['tablecontent'] = $this->instanceFunction24->VourcherGroup($this->UnproccessVouchers()); //$this->VourcherGroup($this->UnproccessVouchers());

        $data['UnitStaff'] = $this->instanceFunction24->UnitStaff('CPO'); //$this->UnitStaff('AU');

        return view('funds.cpo.assignUnprocessVoucher', $data);
    }



    public function UnproccessVouchers()
    {
        return DB::table('tblpaymentTransaction')
            ->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
            ->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
            ->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
            ->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
            //->where('tblpaymentTransaction.vstage', 3)
            ->where('tblpaymentTransaction.auditStatus', '=', 1)
            ->where('tblpaymentTransaction.cpo_payment', '=', 0)
            ->where('tblpaymentTransaction.vstage', '=', 4)
            ->where('tblpaymentTransaction.datePrepared', '>', '2021-11-30')
            ->select(
                'tblpaymentTransaction.*',
                'tbleconomicCode.description as ecotext',
                'tblcontractor.contractor',
                'tblcontractType.contractType',
                'tbleconomicCode.economicCode',
                'tblcontractDetails.contractValue',
                'tblcontractDetails.dateAward',
                'tblcontractDetails.ContractDescriptions',
                'tblcontractDetails.beneficiary',
                'tblcontractDetails.voucherType',
                DB::raw('tblcontractDetails.ID AS conID')
            )
            ->orderBy('auditDate', 'Asc')
            ->get();
    }

    public function cpoProcessAssignedVouchers()
    {
        $data['company'] = DB::table('tblcompany')->first();
        $data['contractTypes'] = DB::table('tblcontractType')->get();

        $data['audited'] = DB::table('tblpaymentTransaction')
            ->join('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
            ->join('tblcontractType', 'tblcontractType.id', '=', 'tblpaymentTransaction.contractTypeID')
            ->leftJoin('tblbanklist', 'tblcontractor.Banker', '=', 'tblbanklist.bankID')
            ->leftJoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
            ->leftJoin('tblclaim', 'tblclaim.ID', '=', 'tblcontractDetails.claimID')
            ->where('tblpaymentTransaction.auditStatus', '=', 1)
            ->where('tblpaymentTransaction.cpo_payment', '=', 0)
            ->where('tblpaymentTransaction.vstage', '=', 4)
            ->where('tblpaymentTransaction.cpo_assign_userID', '=', Auth::user()->id)
            ->select('*', 'tblpaymentTransaction.ID as transID', 'tblclaim.ID as beneClaimID')
            ->orderBy('auditDate', 'Asc')
            ->paginate(100);

        // dd($data['audited']);

        $data['totalRows'] = count((array)$data['audited']);

        return view('funds/cpo/displayAssignedCpoReport', $data);
    }

    public function cpoRejectAssigned(Request $request)
    {
        // dd($request->all());
        $id = $request['transid'];
        $this->validate($request, [
            'transid' => 'required',
            'remark' => 'required'
        ]);
        $Vdetails = DB::table('tblpaymentTransaction')->where('ID', $id)->first();
        $user = Auth::user()->username;
        $commenttypeID = 2;
        $comment = trim($request['remark']) . ": comment by " . Auth::user()->name . " at cpo section";
        $saveComment = DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $Vdetails->contractID, 'paymentID' => 0, 'username' => $user, 'comment' => $comment, 'is_cpo_comment' => 1]);

        if ($saveComment) {
            return back()->with('msg', 'Thank You, you have successfully commented on voucher...');
        } else {
            return back()->with('err', 'Sorry, request could not be completed');
        }
    }

    public function viewVoucherParam()
    {
        $all = DB::table('staffvoucherparameters')->get();

        // Group by logical sections
        $sections = [
            'CJN' => $all->where('employee_type', 2)->where('gradelevel', 2)->where('hr_employment_type', 1),
            'Justices' => $all->where('employee_type', 2)->where('gradelevel', 1)->where('hr_employment_type', 1),
            'Chief Registrar' => $all->where('employee_type', 6)->where('gradelevel', 17)->where('hr_employment_type', 1),
            'Special Assistant' => $all->where('employee_type', 7)->where('gradelevel', 1)->where('hr_employment_type', 1),
            'Permanent Staff' => $all->where('employee_type', 1)->where('hr_employment_type', 1),
            'Contract Staff' => $all->where('employee_type', 1)->where('hr_employment_type', 2),
        ];

        return view('funds.cpo.viewVoucherParameter', compact('sections'));
    }

    public function createVoucherParam()
    {
        $grades = range(1, 17);
        return view('funds.cpo.voucherParameter', compact('grades'));
    }

    public function storeVoucherParam(Request $request)
    {
        $request->validate([
            'sections' => 'required|array',
        ]);

        foreach ($request->sections as $section) {

            $employeeType = $section['employee_type'];
            $hrEmploymentType = $section['hr_employment_type'];

            foreach ($section['rows'] as $row) {

                if (!empty($row['gradelevel']) && !empty($row['totalamount'])) {

                    DB::table('staffvoucherparameters')->updateOrInsert(
                        [
                            'gradelevel' => $row['gradelevel'],
                            'employee_type' => $employeeType,
                            'hr_employment_type' => $hrEmploymentType,
                        ],
                        [
                            'rate' => str_replace(',', '', $row['totalamount']), // since you removed rate
                            'totalamount' => str_replace(',', '', $row['totalamount']),
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }
        }

        return redirect()->back()->with('success', 'Parameters saved successfully!');
    }

    public function StaffBenficiaryCPOConfirm($transId)
    {
        $data['beneficiaries'] = DB::table('tblpaymentTransaction')
            ->where('tblpaymentTransaction.ID', '=', $transId)
            ->leftjoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
            ->leftJoin('tblvoucherBeneficiary', 'tblvoucherBeneficiary.voucherID', '=', 'tblpaymentTransaction.ID')
            ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblvoucherBeneficiary.bankID')
            ->leftJoin('tblselectedstaffclaim', 'tblselectedstaffclaim.selectedID', '=', 'tblvoucherBeneficiary.claim_selected_staff')
            ->leftjoin('tblper', 'tblper.ID', '=', 'tblselectedstaffclaim.staffID')
            ->select(
                'tblpaymentTransaction.ID as transID',
                'tblpaymentTransaction.paymentDescription',
                'tblpaymentTransaction.amtPayable',
                'tblbanklist.bank',
                'tblvoucherBeneficiary.ID as vBeneID',
                'tblvoucherBeneficiary.accountNo',
                'tblvoucherBeneficiary.beneficiaryDetails',
                'tblvoucherBeneficiary.amount',
                'tblvoucherBeneficiary.isChecked',
                'tblper.fileNo',
            )
            ->get();
        return view('funds/cpo/displayVoucherBeneToConfirm', $data);
    }

    public function StaffBenficiaryCPOConfirmSubmit(Request $request)
    {
        $request->validate([
            'transID' => 'required|integer',
            'checkname' => 'required|array',
            'checkname.*' => 'required|integer',
        ]);

        $transID = $request->input('transID');
        $checkedIds = $request->input('checkname', []);
        $userId = Auth::id();
        $dateCheck = now()->toDateString();

        DB::transaction(function () use ($transID, $checkedIds, $userId, $dateCheck) {
            // First, reset all beneficiaries for this voucher
            DB::table('tblvoucherBeneficiary')
                ->where('voucherID', $transID)
                ->update([
                    'isChecked' => 0,
                    'checkedBy' => null,
                    'date_check' => null,
                ]);

            // Then, update only the checked ones
            if (!empty($checkedIds)) {
                DB::table('tblvoucherBeneficiary')
                    ->where('voucherID', $transID)
                    ->whereIn('ID', $checkedIds)
                    ->update([
                        'isChecked' => 1,
                        'checkedBy' => $userId,
                        'date_check' => $dateCheck,
                    ]);
            }
        });
        //if it's CPO Head redirect to the main page else redirect to assigned page
        if(DB::table('assign_user_role')->where('userID', $userId)->whereIn('roleID', [32, 1])->first()){
            // return redirect("/voucher-beneficiary/confirm/{$transID}")
            return redirect("/cpo/report")->with('msg', 'Beneficiaries confirmation updated successfully.');
        }else{
            return redirect("/cpo/process-assigned-vouchers")->with('msg', 'Beneficiaries confirmation updated successfully.');
        }
        
    }

    public function paymentSentToBankOld()
    {
       $data['codes'] = DB::table('tblaction_rank')->where('cont_payment_active', '=', 1)->get();
       $data['audited'] = DB::select("
            SELECT 
                tblepayment.*,
                SUM(tblepayment.amount) as sum,
                SUM(tblepayment.VATValue) as vsum,
                SUM(tblepayment.WHTValue) as wsum,
                SUM(tblepayment.stampduty) as stampsum,
                COUNT(DISTINCT batch) as batch_count
            FROM tblepayment
            WHERE tblepayment.is_paid_from_bank = 1
            AND tblepayment.date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
            GROUP BY tblepayment.batch
        ");
        $data['contractlist'] = DB::table('tblcontractType')->where('status', 1)->get();
        $data['selectedfromDate'] = "";
        $data['selectedtoDate'] = "";
        $data['selectedContractType'] = "";
        $data['selectedVoucherNumber'] = "";
        return view('funds/cpo/batchSentToBank', $data);
    }

    public function paymentSentToBank()
    {
        $data['codes'] = DB::table('tblaction_rank')
            ->where('cont_payment_active', 1)
            ->get();

        $data['audited'] = DB::table('tblepayment_bank_paid')
            ->select(
                'batch',
                DB::raw('SUM(amount) as sum'),
                DB::raw('COUNT(*) as batch_count'),
                DB::raw('MAX(date) as date')
            )
            ->where('date', '>=', DB::raw('DATE_SUB(CURDATE(), INTERVAL 1 MONTH)'))
            ->groupBy('batch')
            ->orderBy('batch', 'desc')
            ->get();

        $data['contractlist'] = DB::table('tblcontractType')
            ->where('status', 1)
            ->get();

        $data['selectedfromDate'] = "";
        $data['selectedtoDate'] = "";
        $data['selectedContractType'] = "";
        $data['selectedVoucherNumber'] = "";

        return view('funds/cpo/batchSentToBank', $data);
    }

    public function paymentSentToBankByBatch($batch)
    {
        $data['bankComponents'] = DB::table('tblepayment_bank_paid')->where('batch', $batch)->get();
        return view('funds/cpo/batchSentToBankComponents', $data);
    }

    public function regeneratedNeft(Request $request)
    {
        $selected = json_decode($request->selected, true);
        $datePaid = $request->date_paid;

        if (!$selected || count($selected) == 0) {
            return back()->with('err', 'No records selected.');
        }

        $ids = array_column($selected, 'id');
        $batch = $selected[0]['batch'];

        // remove letter if batch is like 0073A
        $baseBatch = preg_replace('/[A-Z]$/', '', $batch);

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | 1. Mark Selected Rows As Paid
            |--------------------------------------------------------------------------
            */

            DB::table('tblepayment_bank_paid')
                ->whereIn('ID', $ids)
                ->update([
                    'payment_status' => 1,
                    'date_paid' => $datePaid,
                    'updated_at' => now()
                ]);

            /*
            |--------------------------------------------------------------------------
            | 2. Get Latest Batch Version
            |--------------------------------------------------------------------------
            */

            $lastBatch = DB::table('tblepayment_bank_paid')
                ->where('batch', 'like', $baseBatch . '%')
                ->orderByRaw('LENGTH(batch) DESC, batch DESC')
                ->value('batch');

            if ($lastBatch == $baseBatch) {

                // first regeneration
                $newBatch = $baseBatch . 'A';

            } else {

                $lastLetter = substr($lastBatch, -1);
                $nextLetter = chr(ord($lastLetter) + 1);

                $newBatch = $baseBatch . $nextLetter;
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Get Rows That Were NOT Selected
            |--------------------------------------------------------------------------
            */

            $unpaidRows = DB::table('tblepayment_bank_paid')
                ->where('batch', $batch)
                ->whereNotIn('ID', $ids)
                ->get();

            /*
            |--------------------------------------------------------------------------
            | 4. Insert New Mandate Rows
            |--------------------------------------------------------------------------
            */

            foreach ($unpaidRows as $row) {

                DB::table('tblepayment_bank_paid')->insert([
                    'transactionID' => $row->transactionID,
                    'contractor' => $row->contractor,
                    'amount' => $row->amount,
                    'accountNo' => $row->accountNo,
                    'bank' => $row->bank,
                    'bank_branch' => $row->bank_branch,
                    'date' => now(),
                    'batch' => $newBatch,
                    'mandate_status' => 0,
                    'purpose' => $row->purpose,
                    'vat_sortcode' => $row->vat_sortcode,
                    'wht_sortcode' => $row->wht_sortcode,
                    'remark' => $row->remark,
                    'contract_typeID' => $row->contract_typeID,
                    'NJCAccount' => $row->NJCAccount,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'payment_status' => 0,
                    'date_paid' => null,
                    'accountName' => $row->accountName,
                    'bank_sortcode' => $row->bank_sortcode,
                ]);
            }

            DB::commit();

            return redirect('cpo/payments/sent-to-bank')->with(
                'msg',
                'Mandate regenerated successfully.'
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('err', $e->getMessage());
        }
    }

    public function viewRegeneratedBatch($batch)
    {
            $data['company'] = DB::table('tblcompany')->first();
            $data['mandate'] = DB::table('tblepayment_bank_paid')->where('batch', $batch)->get();
            $data['sum'] = DB::table('tblepayment_bank_paid')->where('batch', $batch)->sum('amount');
            $data['current_batch'] = $batch;
            $data['date'] = $data['mandate'][0]->date;
            $data['accountDetails'] =  DB::table('tblmandate_address_account')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                ->where('tblmandate_address_account.contractTypeID', '=', $data['mandate'][0]->contract_typeID)
                ->where('tblmandate_address_account.status', '=', 1)
                ->get();
            $data['accountAddress'] =  DB::table('tblmandate_address_account')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblmandate_address_account.bankId')
                ->where('tblmandate_address_account.id', '=', $data['mandate'][0]->NJCAccount)
                ->first();
            $data['banks'] = DB::table('tblbanklist')->get();
        return view('funds/cpo/contractEpaymentTodayRegenerated', $data);
    }

    public function toggleAmount(Request $request)
    {
        DB::table('tblepayment')
            ->where('ID', $request->id)
            ->update(['amount_is_paid' => $request->status]);

        return response()->json(['success' => true]);
    }

    public function toggleVAT(Request $request)
    {
        DB::table('tblepayment')
            ->where('ID', $request->id)
            ->update(['vat_is_paid' => $request->status]);

        return response()->json(['success' => true]);
    }

    public function toggleWHT(Request $request)
    {
        DB::table('tblepayment')
            ->where('ID', $request->id)
            ->update(['wht_is_paid' => $request->status]);

        return response()->json(['success' => true]);
    }

}
