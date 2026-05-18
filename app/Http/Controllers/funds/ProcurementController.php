<?php

namespace App\Http\Controllers\funds;



use App\Helpers\FileUploadHelper;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ProcurementController extends function24Controller
{
    public function __construct() {}

    public function newprocurement_staff(Request $request)
    {

        //Increase Memory Size
        ini_set('memory_limit', '-1');
        //////////////////////////////////
        $data['warning'] = '';
        $data['success'] = '';
        $data['error'] = '';
        if ($request['date_awarded'] == '') {
            $request['date_awarded'] = date('Y-m-d');
        }
        $data['procurementlist'] = $this->getStaffProcurement();

        $data['contractorList'] = [];
        $data['contractlist'] = $this->getContract();
        $data['contractlist2'] = $this->getContract();

        $data['currentuser']         = Auth::user()->username;
        $data['contract_desc']         = trim($request['contract-desc']);
        $data['contractvalue']         = preg_replace('/[^\d.]/', '', $request['contractvalue']);
        //dd($data['contractvalue']);
        $data['companyid']             = $request['companyid'];
        $data['benef']             = $request['benef'];
        $data['date_awarded']         = $request['date_awarded'];
        $data['contracttype']         = $request['contracttype'];
        $data['attension']         = $request['attension'];

        $data['fileno']            = trim($request['fileno']);

        if (isset($_POST['delete'])) {

            $claimid = DB::table('tblcontractDetails')->where('ID', $request['deleteid'])->where('approvalStatus', 0)->value('claimid');

            DB::table('tblselectedstaffclaim')->where('claimID', $claimid)->delete();
            DB::table('staffclaimfile')->where('claimID', $claimid)->delete();
            DB::table('tblclaim')->where('ID', $claimid)->delete();
            if (DB::table('tblcontractDetails')->where('ID', $request['deleteid'])->where('approvalStatus', 0)->delete()) {
                $data['success'] = "Record was deleted successfully!";
            }
        }
        if ($request['contracttype'] && $request['contract-desc'] && $request['contractvalue'] && $request['companyid'] && $request['date_awarded'] && $request['createdby'] && $request['fileno']) {

            foreach ($data as $key => $value) {
                $$key = $value;
            }

            if (!DB::table('tblcontractDetails')
                ->where('fileNo', $fileno)
                ->get()) {

                $this->validate($request, ['filex' => 'file|mimes:pdf,jpeg,jpg,png,gif'], [], ['filex' => 'Attached File']);

                $lastid = DB::table('tblcontractDetails')->insertGetId([
                    'contract_Type'             => $contracttype,
                    'fileNo'                => $fileno,
                    'ContractDescriptions'            => $contract_desc,
                    'contractValue'                => $data['contractvalue'],
                    'companyID'                => $companyid,
                    'beneficiary'                => $benef,
                    'dateAward'                => $date_awarded,
                    'approvalStatus'                => 1,
                    'createdby'                =>  Auth::user()->id,
                    'voucherType'                => 2,
                    'awaitingActionby'            => $attension,
                    'datecreated'                => date("F j, Y")
                ]);

                if ($request->file('filex') != null) {

                    $image = $request->file('filex');

                    $input['imagename'] = $lastid . '.' . $image->getClientOriginalExtension();

                    $upload_path = env('UPLOAD_PATH', '');

                    $destinationPath = base_path('../') . '/' . $upload_path;


                    $move = $image->move($destinationPath, $input['imagename']);

                    if ($move) {
                        $data['success'] = "Procurement created successful";
                        DB::table('tblcontractDetails')->where('ID', $lastid)->update(['file_ex' => $image->getClientOriginalExtension()]);
                    }
                }
                $usr = Auth::user()->username;
                $this->addLogg("New Staff Procurement Created with File Number:$fileno and Contract Description: $contract_desc by $usr", "New staff Procurement Created");
            } else {
                $data['error'] = "This Procurement has been created earlier!";
            }
        }


        if ($request['edit-hidden'] == 1) {
            $fileno = $request['file_no'];
            $contracttyp = $request['contr_type'];
            $contratdesc = $request['contr_desc'];
            $contractval = $request['contr_val'];
            $compani     = $request['company'];
            $dateawd     = $request['dateawd'];
            $createdby   = $request['creatdby'];

            $chk = DB::table('tblcontractDetails')->where('fileNo', $fileno)->where('createdby', $createdby)->first();
            $comp = DB::table('tblcontractor')->where('id', '=', $chk->companyID)->first();
            if ($chk) {

                DB::table('tblcontractDetails')->where('fileNo', $fileno)->where('createdby', $createdby)
                    ->update([
                        'fileNo'                 => $fileno,
                        'contract_Type'             => $contracttyp,
                        'ContractDescriptions'             => $contratdesc,
                        'contractValue'                => $contractval,
                        'companyID'                => $compani,
                        'dateAward'                => $dateawd,
                        'createdby'                =>  Auth::user()->id,
                    ]);

                if ($request->file('filex') != null) {

                    $image = $request->file('filex');

                    $input['imagename'] = $chk->ID . '.' . $image->getClientOriginalExtension();

                    $upload_path = env('UPLOAD_PATH', '');

                    $destinationPath = base_path('../') . '/' . $upload_path;

                    if (file_exists($destinationPath . $input['imagename'])) {
                        unlink($destinationPath . $input['imagename']);
                        $move = $image->move($destinationPath, $input['imagename']);

                        if ($move) {
                            $data['success'] = "Record was edited successfully!";
                        }
                    } else {
                        $move = $image->move($destinationPath, $input['imagename']);

                        if ($move) {
                            $data['success'] = "Record was edited successfully!";
                        }
                    }

                    $contVal = is_numeric($contractval) ? $contractval : 0;
                    $usr = Auth::user()->username;
                    $data1 = array("File Number" => $chk->fileNo, "Contract Type" => $chk->contract_Type, "Contract Descriptions" => $chk->ContractDescriptions, "Contract Value" => $chk->contractValue, "Company" => $comp->contractor, "Date Awarded" => $chk->dateAward);
                    $post_encode = json_encode($data1);
                    $data2 = array("File Number" => $fileno, "Contract Type" => $contracttyp, "Contract Descriptions" => $contratdesc, "Contract Value" => $contVal, "Company" => $compani, "Date Awarded" => $dateawd);
                    $post_encode2 = json_encode($data2);
                    $operation = "Staff Contract edited from $post_encode to $post_encode2 by $usr";
                    $this->addLogg($operation, "Contract Edited");
                } else {
                    $data['success'] = "Record was edited successfully!";
                }
            } else {
                $data['error'] = "Oops something went wrong!";
            }
        }

        if ($request['allocationtype'] && $request['contracttype']) {
            $data['econocode'] = $this->getEconomicCode($request['allocationtype'], $request['contracttype']);
            $data['economiccode'] = $request['economicCode'];
        }

        $data['companyDetails'] = $this->getBeneficiary();
        $data['fileRefer'] = [];
        $data['procurementlist'] = $this->getStaffProcurement();



        foreach ($data['procurementlist'] as $key => $value) {
            $line = (array) $value;
            $reason = "";
            if ($line['approvalStatus'] == 2) {
                $reason = DB::table('tblcomments')->where('affectedID', $value->ID)->where('commenttypeID', 1)->latest('id')->first()->comment;
            }
            $line['reason'] = $reason;
            $data['procurementlist'][$key] = (object) $line;
        }

        return view('funds.NewProcurement.staffprocurement', $data);
    }
    public function Pre_procurement_staff(Request $request)
    {
        //Increase Memory Size
        ini_set('memory_limit', '-1');
        //////////////////////////////////
        $data['warning'] = '';
        $data['success'] = '';
        $data['error'] = '';
        $request['claimvalue'] = preg_replace('/[^\d.]/', '', $request['claimvalue']);
        if ($request['approvaldate'] == '') {
            $request['approvaldate'] = date('Y-m-d');
        }
        $data['fileno'] = $request['fileno'];
        $data['contracttype'] = $request['contracttype'];
        $data['description'] = $request['description'];
        $data['claimvalue'] = $request['claimvalue'];
        $data['benef'] = $request['benef'];
        $data['approvaldate'] = $request['approvaldate'];
        $data['attension'] = $request['attension'];

        if (isset($_POST['delete'])) {
            $claimid = DB::table('tblcontractDetails')->where('ID', $request['deleteid'])->where('approvalStatus', 0)->value('claimid');
            DB::table('tblselectedstaffclaim')->where('claimID', $claimid)->delete();
            DB::table('staffclaimfile')->where('claimID', $claimid)->delete();
            DB::table('tblclaim')->where('ID', $claimid)->delete();
            if (DB::table('tblcontractDetails')->where('ID', $request['deleteid'])->where('approvalStatus', 0)->delete()) {
                $data['success'] = "Record was deleted successfully!";
            }
        }


        if (isset($_POST['save'])) {
            $this->validate($request, [
                'fileno'                    => 'required|string',
                'contracttype'              => 'required|string',
                'description'               => 'required|string',
                'claimvalue'                => 'required|numeric',
                'benef'                     => 'required|string',
                'approvaldate'              => 'required|string',
                'attension'                 => 'required|string',
                'approvalpage'              => 'required|numeric',
                'filex' => 'required|file|mimes:pdf,jpeg,jpg,png|max:5120',
            ], [
                'filex.required' => 'Approval document is required.',
            ]);
            if (DB::table('tblcontractDetails')->where('ref_no', $request['approvalpage'])->where('fileNo', $data['fileno'])->first()) return back()->with("err", "Duplicate approval page " . $request['approvalpage'] . " not allowed for " .  $data['fileno'] . ". The page number approval already captured");

            $claimid = DB::table('tblclaim')->insertGetId([
                'user'             => Auth::user()->id,
                'Title'            => 'DTA/Staff Claim',
                'claimFileNo'    => $data['fileno'],
                'details'        => $data['description'],
                'amount'        => $data['claimvalue'],
                'status'        => 6,
                'created_at'    => date("Y-m-d"),
            ]);
            $lastid = DB::table('tblcontractDetails')->insertGetId([
                'contract_Type'         => $data['contracttype'],
                'claimid'                => $claimid,
                'fileNo'                => $data['fileno'],
                'ref_no'                    => $request['approvalpage'],
                'ContractDescriptions'    => $data['description'],
                'contractValue'            => $data['claimvalue'],
                'companyID'                => 13,
                'beneficiary'            => $data['benef'],
                'dateAward'                => $data['approvaldate'],
                'approvalDate'            => $data['approvaldate'],
                'approvalStatus'        => 0,
                'createdby'                =>  Auth::user()->id,
                'voucherType'            => 2,
                'awaitingActionby'        => $data['attension'],
                'datecreated'            => date("F j, Y")
            ]);
            if ($request->file('filex') != null) {
                $file = $request->file('filex');
                // $img = $lastid . "_" . $this->RefNo() . '.' . $file->getClientOriginalExtension();
                // $file->move(env('Public_Path', '') . "/attachments", $img);
                //$pathToUploads = "/home/njcgov/fundsAppAttachments/";
                //$file->move($pathToUploads, $img);

                $file = $request->file('filex');
                $customName = $lastid . "_" . $this->RefNo() . '.' . $file->getClientOriginalExtension();

                // Use helper (automatically stores to local or S3)
                $fileUrl = FileUploadHelper::upload($file, 'attachments', $customName);

                DB::table('tblcontractfile')->insert([
                    'file_desc' => "Approval document",
                    'filename' =>  $fileUrl,
                    'contractid' => $lastid,
                    'createdby' => Auth::user()->id,
                ]);
            }
            return back()->with('message', 'addedd  successfully added.');
        }
        if (isset($_POST['update'])) {
            $this->validate($request, [
                'cid'                   => 'required|string',
                'fileno'                => 'required|string',
                'contracttype'          => 'required|string',
                'description'           => 'required|string',
                'claimvalue'            => 'required|numeric',
                'benef'                 => 'required|string',
                'approvaldate'          => 'required|string',
                'attension'             => 'required|string',
                'approvalpage'          => 'required|numeric',
            ]);
            $lastid = $request['cid'];
            DB::table('tblcontractDetails')->where('ID', $request['cid'])->update([
                'contract_Type'             => $data['contracttype'],
                'fileNo'                    => $data['fileno'],
                'ref_no'                    => $request['approvalpage'],
                'ContractDescriptions'        => $data['description'],
                'contractValue'                => $data['claimvalue'],
                'companyID'                    => 13,
                'beneficiary'                => $data['benef'],
                'dateAward'                    => $data['approvaldate'],
                'approvalDate'                => $data['approvaldate'],
                'approvalStatus'            => 0,
                'createdby'                    =>  Auth::user()->id,
                'voucherType'                => 2,
                'awaitingActionby'            => $data['attension'],
                'datecreated'                => date("Y-m-d")
            ]);
            $claimid = DB::table('tblcontractDetails')->where('ID', $request['cid'])->value('claimid');
            DB::table('tblclaim')->where('ID', $claimid)
                ->update([
                    'details'        => $data['description'],
                    'amount'        => $data['claimvalue'],
                ]);
            if ($request->file('filex') != null) {
                $file = $request->file('filex');
                // $img = $lastid . "_" . $this->RefNo() . '.' . $file->getClientOriginalExtension();
                // $file->move(env('Public_Path', '') . "/attachments", $img);
                //$pathToUpload = "/home/njcgov/fundsAppAttachments/";
                //$file->move($pathToUpload, $img);

                $file = $request->file('filex');
                $customName = $lastid . "_" . $this->RefNo() . '.' . $file->getClientOriginalExtension();

                // Use helper (automatically stores to local or S3)
                $fileUrl = FileUploadHelper::upload($file, 'attachments', $customName);

                DB::table('tblcontractfile')->insert([
                    'file_desc' => "Approval document",
                    'filename' =>  $fileUrl,
                    'contractid' => $lastid,
                    'createdby' => Auth::user()->id,
                ]);
            }
            return back()->with('message', 'addedd  successfully added.');
        }

        $data['contractlist'] = $this->getContract();
        $data['procurementlist'] = $this->getUnproccessedStaffClaim();
        //dd($data['procurementlist']);
        $data['officers'] = DB::table('tblaction_rank')->where('preapproval', 1)->orderby('rankorder')->get();
        return view('funds.NewProcurement.prestaffprocurement', $data);
    }

    public function newprocurement_02_03_2026(Request $request)
    {
        //Increase Memory Size
        ini_set('memory_limit', '-1');
        ini_set('upload_max_filesize', '10M');
        ini_set('post_max_size', '10M');
        ini_set('max_input_time', 300);
        ini_set('max_execution_time', 300);
        //////////////////////////////////
        $data['warning'] = '';
        $data['success'] = '';
        $data['error'] = '';

        if ($request->has('btn-attachment')) {
            $this->validate($request, [
                'attachment_description' => 'required|string',
                'filename' => 'required|mimes:jpg,png,jpeg,bmp,pdf|max:5120',
                // 'filename' => 'required|mimes:jpg,png,jpeg,bmp,pdf|max:1024',

            ]);

            $file = $request->file('filename');
            $customName = $request->input('id') . "_" . $this->RefNo() . '.' . $file->getClientOriginalExtension();

            // Use helper (automatically stores to local or S3)
            $fileUrl = FileUploadHelper::upload($file, 'attachments', $customName);

            DB::table('tblcontractfile')->insert([
                'file_desc' => $request->input('attachment_description'),
                'filename' => $fileUrl, // full URL
                'contractid' => $request->input('id'),
                'createdby' => Auth::user()->id,
            ]);

            return back()->with('message', 'Attachment successfully added.');
        }

        $data['procurementlist'] = $this->getProcurementReassignable();

        $data['contractorList'] = [];
        $data['contractlist'] = $this->getContract();
        $data['contractlist2'] = $this->getContract();

        $data['currentuser']         = Auth::user()->username;
        $data['contract_desc']         = trim($request['contract-desc']);
        $data['contractvalue']         = preg_replace('/[^\d.]/', '', $request['contractvalue']);
        $data['attension']         = $request['attension'];
        $data['companyid']             = $request['companyid'];
        $data['date_awarded']         = $request['date_awarded'];
        $data['contracttype']         = $request['contracttype'];
        $data['fileno']                = trim($request['fileno']);
        $contracttype = $data['contracttype'];
        $companyid = $data['companyid'];
        $contract_desc = $data['contract_desc'];
        $fileno = $data['fileno'];
        $date_awarded = $data['date_awarded'];


        if ($request['contract-desc'] && $request['contractvalue'] && $request['companyid'] && $request['date_awarded'] && $request['createdby'] && $request['fileno']) {

            // Log::info($request);
            $this->validate($request, [
                'approvalpage'          => 'required|numeric',
            ]);

            $checkContractDetail = DB::table('tblcontractDetails')
                ->where('ref_no', $request['approvalpage'])
                ->where('fileNo', $data['fileno'])->first();


            if ($checkContractDetail) {
                return back()->with("err", "Duplicate approval page " . $request['approvalpage'] . " not allowed for " .  $data['fileno'] . ". The page number approval already captured");
            }

            // $this->validate($request, ['filex' => 'file|mimes:pdf,jpeg,jpg,png,gif'], [], ['filex' => 'Attached File']);
            $this->validate($request, ['file|mimes:pdf,jpeg,jpg,png,gif|max:1024'], [], ['filex' => 'Attached File']);

            $lastid = DB::table('tblcontractDetails')->insertGetId([
                'contract_Type'         => ($contracttype != '') ? $contracttype : 0,
                'fileNo'                => $fileno,
                'ref_no'                => $request['approvalpage'],
                'ContractDescriptions'    => $contract_desc,
                'contractValue' => is_numeric(str_replace(',', '', $data['contractvalue']))
                    ? str_replace(',', '', $data['contractvalue'])
                    : 0,

                'companyID'                => $companyid,
                'dateAward'                => $date_awarded,
                'voucherType'            => 1,
                'awaitingActionby'        => $data['attension'],
                'tin'                    => $request['tin'],
                'createdby'                => Auth::user()->id,
                'datecreated'            => date("F j, Y"),
                // 'isPartPayment' => $request->is_part_payment ? $request->is_part_payment : 0,
            ]);

            //if part payment is true
            // if($request->is_part_payment){
            //     $convertedContractVal = is_numeric(str_replace(',', '', $data['contractvalue']))
            //         ? str_replace(',', '', $data['contractvalue'])
            //         : 0;
            //     $convertedPartAmt = is_numeric(str_replace(',', '', $request['part_amount']))
            //         ? str_replace(',', '', $request['part_amount'])
            //         : 0;
            //     $balance = $convertedContractVal - $convertedPartAmt;
            //     DB::table('tblcontractDetailsPartPay')->insert([
            //         'contractID' => $lastid,
            //         'initiated_amt' => $convertedPartAmt,
            //         'balance' => $balance,
            //     ]);
            // }

            if ($request->file('filex') != null) {
                $file = $request->file('filex');
                $customName = $request->input('id') . "_" . $this->RefNo() . '.' . $file->getClientOriginalExtension();

                // Use helper (automatically stores to local or S3)
                $fileUrl = FileUploadHelper::upload($file, 'attachments', $customName);
                // Log::info($fileUrl);


                DB::table('tblcontractfile')->insert([
                    'file_desc' => "Approval document",
                    'filename' => $fileUrl, // full URL
                    'contractid' => $lastid,
                    'createdby' => Auth::user()->id,
                ]);

                return back()->with('message', 'Attachment successfully added.');
            }

            $taskscheduled = $this->UpdateAlertTable("Payment approval review", 'procurement/approve', '', $data['attension'], 'tblcontractDetails', $lastid, 1);
            $usr = Auth::user()->username;
            $this->addLogg("New Procurement Created with File Number:$fileno and Contract Description: $contract_desc by $usr", "New Procurement Created");
        }


        // dd('here');

        if (!empty($request['deleteid'])) {
            $id = $request['deleteid'];
            if (DB::table('tblcontractDetails')->where('ID', $id)->delete()) {
                $taskscheduled = $this->UpdateAlertTable("", '', '', '', 'tblcontractDetails', $id, 0);
                $data['success'] = "Record was deleted successfully!";
            }
        }


        if ($request['edit-hidden'] == 1) {
            $fileno = $request['file_no'];
            $contracttyp = $request['contr_type'];
            $contratdesc = $request['contr_desc'];
            $contractval = preg_replace('/[^\d.]/', '', $request['contr_val']);
            $compani     = $request['company'];
            $dateawd     = $request['dateawd'];
            $createdby   = $request['creatdby'];
            //dd($request['id']);
            $chk = DB::table('tblcontractDetails')->where('ID', $request['id'])->first();
            $comp = DB::table('tblcontractor')->where('id', '=', $chk->companyID)->first();
            $this->validate($request, [
                'company'           => 'required|string',
                'contr_desc'        => 'required',
                'actionby'           => 'required',
                'contr_desc'        => 'required',
                'approvalpage'      => 'required|numeric',
            ]);
            if ($chk) {

                DB::table('tblcontractDetails')->where('ID', $request['id'])
                    ->update([
                        'fileNo'                 => $fileno,
                        'ref_no'                    => $request['approvalpage'],
                        'contract_Type'         => $contracttyp,
                        'ContractDescriptions'     => $contratdesc,
                        'contractValue'            => is_numeric($contractval) ? $contractval : 0,
                        'companyID'                => $compani,
                        'dateAward'                => $dateawd,
                        'tin'                    => $request['tin'],
                        'awaitingActionby'        => $request['actionby'],
                        'createdby'                =>  Auth::user()->id
                    ]);

                if ($request->file('filex') != null) {
                    $file = $request->file('filex');
                    $customName = $request->input('id') . "_" . $this->RefNo() . '.' . $file->getClientOriginalExtension();

                    // Use helper (automatically stores to local or S3)
                    $fileUrl = FileUploadHelper::upload($file, 'attachments', $customName);
                    // Log::info($fileUrl);

                    DB::table('tblcontractfile')->insert([
                        'file_desc' => "Approval document",
                        'filename' => $fileUrl, // full URL
                        'contractid' => $request['id'],
                        'createdby' => Auth::user()->id,
                    ]);

                    return back()->with('message', 'Attachment successfully added.');
                }


                $taskscheduled = $this->UpdateAlertTable("Payment approval review", 'procurement/approve', '', $request['actionby'], 'tblcontractDetails', $request['id'], 1);

                $contVal = is_numeric($contractval) ? $contractval : 0;
                $usr = Auth::user()->username;
                $data1 = array("File Number" => $chk->fileNo, "Contract Type" => $chk->contract_Type, "Contract Descriptions" => $chk->ContractDescriptions, "Contract Value" => $chk->contractValue, "Company" => $comp->contractor, "Date Awarded" => $chk->dateAward);
                $post_encode = json_encode($data1);
                $data2 = array("File Number" => $fileno, "Contract Type" => $contracttyp, "Contract Descriptions" => $contratdesc, "Contract Value" => $contVal, "Company" => $compani, "Date Awarded" => $dateawd);
                $post_encode2 = json_encode($data2);
                $operation = "Contract edited from $post_encode to $post_encode2 by $usr";
                $this->addLogg($operation, "Contract Edited");

                return back()->with('message', 'Modification successful.');
            } else {
                $data['error'] = "Oops something went wrong!";
            }
        }

        $data['companyDetails'] = $this->getBeneficiary();
        $data['fileRefer'] = [];
        $data['procurementlist'] = $this->getProcurementReassignable();
        //dd($data['procurementlist']);
        $data['officers'] = DB::table('tblaction_rank')->where('preapproval', 1)->orderby('rankorder')->get();
        return view('funds.Procurements.newprocurement', $data);
    }


    public function newprocurement(Request $request)
    {
        // Increase Memory Size
        ini_set('memory_limit', '-1');
        ini_set('upload_max_filesize', '10M');
        ini_set('post_max_size', '10M');
        ini_set('max_input_time', 300);
        ini_set('max_execution_time', 300);

        $data['warning'] = '';
        $data['success'] = '';
        $data['error'] = '';

        /*
    |--------------------------------------------------------------------------
    | ATTACHMENT UPLOAD (Separate Button)
    |--------------------------------------------------------------------------
    */
        if ($request->has('btn-attachment')) {

            $this->validate($request, [
                'attachment_description' => 'required|string',
                'filename' => 'required|file|mimes:jpg,png,jpeg,bmp,pdf|max:5120',
            ]);

            $file = $request->file('filename');
            $customName = $request->input('id') . "_" . $this->RefNo() . '.' . $file->getClientOriginalExtension();
            $fileUrl = FileUploadHelper::upload($file, 'attachments', $customName);

            DB::table('tblcontractfile')->insert([
                'file_desc'  => $request->input('attachment_description'),
                'filename'   => $fileUrl,
                'contractid' => $request->input('id'),
                'createdby'  => Auth::user()->id,
            ]);

            return back()->with('message', 'Attachment successfully added.');
        }

        /*
    |--------------------------------------------------------------------------
    | CREATE NEW PROCUREMENT
    |--------------------------------------------------------------------------
    */
        if ($request->isMethod('post') && !$request->has('btn-attachment') && !$request->has('edit-hidden')) {

            $this->validate($request, [
                'contract-desc' => 'required|string',
                'contractvalue' => 'required',
                'companyid'     => 'required',
                'date_awarded'  => 'required|date',
                'createdby'     => 'required',
                'fileno'        => 'required|string',
                'approvalpage'  => 'required|numeric',
                'filex'         => 'required|file|mimes:pdf,jpeg,jpg,png,gif|max:5120'
            ], [
                'filex.required' => 'Approval document is required.',
                'filex.mimes'    => 'File must be pdf, jpeg, jpg, png or gif.',
                'filex.max'      => 'File must not exceed 5MB.'
            ]);

            $fileno        = trim($request['fileno']);
            $contracttype  = $request['contracttype'];
            $companyid     = $request['companyid'];
            $contract_desc = trim($request['contract-desc']);
            $date_awarded  = $request['date_awarded'];
            $contractvalue = preg_replace('/[^\d.]/', '', $request['contractvalue']);

            // Prevent duplicate approval page
            $checkContractDetail = DB::table('tblcontractDetails')
                ->where('ref_no', $request['approvalpage'])
                ->where('fileNo', $fileno)
                ->first();

            if ($checkContractDetail) {
                return back()->with(
                    "err",
                    "Duplicate approval page {$request['approvalpage']} not allowed for {$fileno}."
                );
            }

            $lastid = DB::table('tblcontractDetails')->insertGetId([
                'contract_Type'        => $contracttype ?: 0,
                'fileNo'               => $fileno,
                'ref_no'               => $request['approvalpage'],
                'ContractDescriptions' => $contract_desc,
                'contractValue'        => is_numeric($contractvalue) ? $contractvalue : 0,
                'companyID'            => $companyid,
                'dateAward'            => $date_awarded,
                'voucherType'          => 1,
                'awaitingActionby'     => $request['attension'],
                'tin'                  => $request['tin'],
                'createdby'            => Auth::user()->id,
                'datecreated'          => date("F j, Y"),
            ]);

            // Upload approval file
            $file = $request->file('filex');
            $customName = $fileno . "_" . $this->RefNo() . '.' . $file->getClientOriginalExtension();
            $fileUrl = FileUploadHelper::upload($file, 'attachments', $customName);

            DB::table('tblcontractfile')->insert([
                'file_desc'  => "Approval document",
                'filename'   => $fileUrl,
                'contractid' => $lastid,
                'createdby'  => Auth::user()->id,
            ]);

            // Update alert table
            $this->UpdateAlertTable(
                "Payment approval review",
                'procurement/approve',
                '',
                $request['attension'],
                'tblcontractDetails',
                $lastid,
                1
            );

            $usr = Auth::user()->username;
            $this->addLogg(
                "New Procurement Created with File Number:$fileno and Contract Description: $contract_desc by $usr",
                "New Procurement Created"
            );

            return back()->with('message', 'Procurement created successfully.');
        }

        /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
        if (!empty($request['deleteid'])) {

            $id = $request['deleteid'];

            if (DB::table('tblcontractDetails')->where('ID', $id)->delete()) {

                $this->UpdateAlertTable("", '', '', '', 'tblcontractDetails', $id, 0);
                $data['success'] = "Record was deleted successfully!";
            }
        }

        /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
        if ($request['edit-hidden'] == 1) {

            $this->validate($request, [
                'company'      => 'required|string',
                'contr_desc'   => 'required',
                'actionby'     => 'required',
                'approvalpage' => 'required|numeric',
            ]);

            $chk = DB::table('tblcontractDetails')->where('ID', $request['id'])->first();

            if ($chk) {

                DB::table('tblcontractDetails')
                    ->where('ID', $request['id'])
                    ->update([
                        'fileNo'               => $request['file_no'],
                        'ref_no'               => $request['approvalpage'],
                        'contract_Type'        => $request['contr_type'],
                        'ContractDescriptions' => $request['contr_desc'],
                        'contractValue'        => preg_replace('/[^\d.]/', '', $request['contr_val']),
                        'companyID'            => $request['company'],
                        'dateAward'            => $request['dateawd'],
                        'tin'                  => $request['tin'],
                        'awaitingActionby'     => $request['actionby'],
                        'createdby'            => Auth::user()->id
                    ]);

                if ($request->file('filex')) {

                    $file = $request->file('filex');
                    $customName = $request->input('id') . "_" . $this->RefNo() . '.' . $file->getClientOriginalExtension();
                    $fileUrl = FileUploadHelper::upload($file, 'attachments', $customName);

                    DB::table('tblcontractfile')->insert([
                        'file_desc'  => "Approval document",
                        'filename'   => $fileUrl,
                        'contractid' => $request['id'],
                        'createdby'  => Auth::user()->id,
                    ]);
                }

                $this->UpdateAlertTable(
                    "Payment approval review",
                    'procurement/approve',
                    '',
                    $request['actionby'],
                    'tblcontractDetails',
                    $request['id'],
                    1
                );

                return back()->with('message', 'Modification successful.');
            }

            $data['error'] = "Oops something went wrong!";
        }

        /*
    |--------------------------------------------------------------------------
    | LOAD PAGE DATA
    |--------------------------------------------------------------------------
    */
        $data['companyDetails']  = $this->getBeneficiary();
        $data['procurementlist'] = $this->getProcurementReassignable();
        $data['contractlist']    = $this->getContract();
        $data['contractlist2']   = $this->getContract();
        $data['officers']        = DB::table('tblaction_rank')
            ->where('preapproval', 1)
            ->orderby('rankorder')
            ->get();

        return view('funds.Procurements.newprocurement', $data);
    }




    public function create(Request $request)
    {
        // Setup memory and upload limits
        ini_set('memory_limit', '-1');
        ini_set('upload_max_filesize', '10M');
        ini_set('post_max_size', '10M');
        ini_set('max_input_time', 300);
        ini_set('max_execution_time', 300);

        $data['warning'] = '';
        $data['success'] = '';
        $data['error'] = '';

        // Fetch dropdown and list data for view
        $data['procurementlist'] = $this->getProcurementReassignable();
        $data['contractorList'] = [];
        $data['contractlist'] = $this->getContract();
        $data['contractlist2'] = $this->getContract();
        $data['companyDetails'] = $this->getBeneficiary();
        $data['officers'] = DB::table('tblaction_rank')->where('preapproval', 1)->orderby('rankorder')->get();

        // Add current user for the form
        $data['currentuser'] = Auth::user()->username;

        // return view('funds.Procurements.newprocurement', $data);
    }



    public function store(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('upload_max_filesize', '10M');
        ini_set('post_max_size', '10M');
        ini_set('max_input_time', 300);
        ini_set('max_execution_time', 300);

        // // ✅ Handle Attachment Upload
        if ($request->has('btn-attachment')) {
            $this->validate($request, [
                'attachment_description' => 'required|string',
                'filename' => 'required|mimes:jpg,png,jpeg,bmp,pdf|max:5120',
            ]);

            $file = $request->file('filename');
            $customName = $request->input('id') . "_" . $this->RefNo() . '.' . $file->getClientOriginalExtension();

            // Upload using the helper
            $fileUrl = FileUploadHelper::upload($file, 'attachments', $customName);

            DB::table('tblcontractfile')->insert([
                'file_desc' => $request->input('attachment_description'),
                'filename' => $fileUrl,
                'contractid' => $request->input('id'),
                'createdby' => Auth::user()->id,
            ]);

            return back()->with('message', 'Attachment successfully added.');
        }


        // ✅ Create New Procurement Record
        $this->validate($request, [
            'contract-desc' => 'required|string',
            'contractvalue' => 'required',
            'companyid' => 'required',
            'date_awarded' => 'required|date',
            'fileno' => 'required',
            'approvalpage' => 'required|numeric',
            'filex' => 'required|file|mimes:pdf,jpeg,jpg,png|max:5120',
        ]);

        // Prevent duplicate approval page for file number
        $checkContractDetail = DB::table('tblcontractDetails')
            ->where('ref_no', $request['approvalpage'])
            ->where('fileNo', $request['fileno'])
            ->first();

        if ($checkContractDetail) {
            return back()->with("err", "Duplicate approval page {$request['approvalpage']} not allowed for {$request['fileno']}.");
        }

        $lastid = DB::table('tblcontractDetails')->insertGetId([
            'contract_Type' => $request['contracttype'] ?? 0,
            'fileNo' => $request['fileno'],
            'ref_no' => $request['approvalpage'],
            'ContractDescriptions' => trim($request['contract-desc']),
            'contractValue' => preg_replace('/[^\d.]/', '', $request['contractvalue']),
            'companyID' => $request['companyid'],
            'dateAward' => $request['date_awarded'],
            'voucherType' => 1,
            'awaitingActionby' => $request['attension'],
            'tin' => $request['tin'],
            'createdby' => Auth::user()->id,
            'datecreated' => now()->format('F j, Y'),
        ]);

        // ✅ Save Attachment (if any)
        if ($request->file('filex')) {
            $file = $request->file('filex');
            $img = $lastid . "_" . $this->RefNo() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('attachments'), $img);

            DB::table('tblcontractfile')->insert([
                'file_desc' => "Approval document",
                'filename' => '/attachments/' . $img,
                'contractid' => $lastid,
                'createdby' => Auth::user()->id,
            ]);
        }

        // Log and notify
        $this->UpdateAlertTable("Payment approval review", 'procurement/approve', '', $request['attension'], 'tblcontractDetails', $lastid, 1);
        $usr = Auth::user()->username;
        $this->addLogg("New Procurement Created with File Number: {$request['fileno']} and Contract Description: {$request['contract-desc']} by $usr", "New Procurement Created");

        return redirect()->route('procurement.create')->with('message', 'Procurement created successfully.');
    }


    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'company' => 'required|string',
            'contr_desc' => 'required',
            'actionby' => 'required',
            'approvalpage' => 'required|numeric',
        ]);

        $chk = DB::table('tblcontractDetails')->where('ID', $id)->first();
        if (!$chk) {
            return back()->with('error', 'Record not found.');
        }

        DB::table('tblcontractDetails')->where('ID', $id)->update([
            'fileNo' => $request['file_no'],
            'ref_no' => $request['approvalpage'],
            'contract_Type' => $request['contr_type'],
            'ContractDescriptions' => $request['contr_desc'],
            'contractValue' => preg_replace('/[^\d.]/', '', $request['contr_val']),
            'companyID' => $request['company'],
            'dateAward' => $request['dateawd'],
            'tin' => $request['tin'],
            'awaitingActionby' => $request['actionby'],
            'createdby' => Auth::user()->id,
        ]);

        if ($request->file('filex')) {
            $file = $request->file('filex');
            $customName = $id . "_" . $this->RefNo() . '.' . $file->getClientOriginalExtension();
            $fileUrl = FileUploadHelper::upload($file, 'attachments', $customName);

            DB::table('tblcontractfile')->insert([
                'file_desc' => 'Approval document',
                'filename' => $fileUrl,
                'contractid' => $id,
                'createdby' => Auth::user()->id,
            ]);
        }

        $this->UpdateAlertTable("Payment approval review", 'procurement/approve', '', $request['actionby'], 'tblcontractDetails', $id, 1);
        $usr = Auth::user()->username;
        $this->addLogg("Procurement (ID: $id) updated by $usr", "Procurement Updated");

        return back()->with('message', 'Modification successful.');
    }


    public function destroy($id)
    {
        $deleted = DB::table('tblcontractDetails')->where('ID', $id)->delete();

        if ($deleted) {
            $this->UpdateAlertTable("", '', '', '', 'tblcontractDetails', $id, 0);
            return back()->with('message', 'Record was deleted successfully!');
        }

        return back()->with('error', 'Failed to delete record.');
    }



    public function approveprocurement(Request $request)
    {

        //Increase Memory Size
        ini_set('memory_limit', '-1');
        //////////////////////////////////
        $data['warning']                 = '';
        $data['success']                 = '';
        $data['error']                     = '';

        $data['procurementlist']         = $this->getProcurement();

        $data['contractorList']         = [];
        $data['contractlist']             = $this->getContract();
        $data['contractlist2']             = $this->getContract();

        $data['currentuser']             = Auth::user()->username;
        $data['contract_desc']             = trim($request['contract-desc']);
        $data['contractvalue']             = $request['contractvalue'];
        $data['companyid']                 = $request['companyid'];
        $data['date_awarded']             = $request['date_awarded'];
        $data['contracttype']             = $request['contracttype'];
        $data['fileno']                    = trim($request['fileno']);

        $data['status']                    = $request['status'];

        if (isset($_POST['archive'])) {
            DB::table('tblcomments')->insert([
                'commenttypeID' => 1,
                'affectedID' => $request['id'],
                'username' => Auth::user()->username,
                'comment' => preg_replace('/\s\s+/', ' ', $request['instruction']) . 'archived by ' . Auth::user()->username
            ]);
            DB::table('tblcontractDetails')->where('ID', $request['id'])->update(['is_archive' => 1, 'openclose'     => 0]);
        }

        if (isset($_POST['s_remark'])) {

            $request['instruction'] = $this->UpdateDefaultComment($request['commentid'], trim(preg_replace('/\s\s+/', ' ', $request['instruction'])), DB::table('tblaction_rank')->where('userid', Auth::user()->username)->value('code'));
            DB::table('tblcomments')->insert([
                'commenttypeID' => 1,
                'affectedID' => $request['contid'],
                'username' => Auth::user()->username,
                'comment' => $request['instruction'] . ' (refer to ' . $request['attension'] . ')'
            ]);
            $openclose = 0;
            $approvalStatus = 0;
            $contid = $request['contid'];
            $url = 'procurement/approve';
            $alertdisc = 'Payment approval review';
            $status = 1;
            if ($request['attension'] == 'OC') {
                $openclose = 1;
                $isrejected = 0;
                $approvalStatus = 1;
                $url = 'create/contract';
                $alertdisc = 'Unassigned payment';
                $status = 1;
                if (DB::table('tblpaymentTransaction')->where('contractID', $request['contid'])->update(['is_archive' => 0, 'isrejected' => 0, 'vstage' => -1])) $status = 0;
            }
            if ($request['attension'] == 'AD') {
                $openclose = 1;
                $isrejected = 0;
                $approvalStatus = 1;
                $url = 'create/advances';
                $alertdisc = 'Unassigned payment';
                $status = 1;
                if (DB::table('tblpaymentTransaction')->where('contractID', $request['contid'])->update(['is_archive' => 0, 'isrejected' => 0, 'vstage' => -1])) $status = 0;
            }
            if ($request['attension'] == 'HEC') {
                $openclose = 1;
                $isrejected = 0;
                $approvalStatus = 1;
                $url = 'take-liability';
                $alertdisc = 'Incoming Liability';
                $status = 1;
                $c_details = DB::table('tblcontractDetails')->where('ID', $contid)->first();
                if (DB::table('tblliability_taken')->where('contractID', $contid)->first()) {
                    DB::table('tblliability_taken')->where('contractID', $contid)->update([
                        'fileno' => $c_details->fileNo,
                        'economic_id' => 0,
                        'amount' =>  $c_details->contractValue,
                        'decription' => $c_details->ContractDescriptions,
                        'beneficiary_id' => $c_details->companyID,
                        'beneficiary' => $c_details->beneficiary,
                        'date_awarded' => $c_details->dateAward,
                        'status' => 0,
                        'created_by' => Auth::user()->id,
                        'time_cleared' => '2020-12-31', //tobe deleted
                        'created_at' => '2020-12-31', //tobe deleted
                        'period' => $this->ActivePeriod(),

                    ]);
                } else {
                    DB::table('tblliability_taken')->insert([
                        'contractID' => $contid,
                        'fileno' => $c_details->fileNo,
                        'economic_id' => 0,
                        'amount' =>  $c_details->contractValue,
                        'decription' => $c_details->ContractDescriptions,
                        'beneficiary_id' => $c_details->companyID,
                        'beneficiary' => $c_details->beneficiary,
                        'date_awarded' => $c_details->dateAward,
                        'status' => 0,
                        'created_by' => Auth::user()->id,
                        'time_cleared' => '2020-12-31', //tobe deleted
                        'created_at' => '2020-12-31', //tobe deleted
                        'period' => $this->ActivePeriod(),

                    ]);
                }
            }
            DB::table('tblcontractDetails')->where('ID', $contid)->update([
                'awaitingActionby' => $request['attension'],
                'openclose'     => $openclose,
                'approvalStatus'     => $approvalStatus,
                'isrejected'     => 0,
                'approvedBy'        => Auth::user()->username,
                'approval_last_action_by'        =>  DB::table('tblcontractDetails')->where('ID', '=', $contid)->value('awaitingActionby'),
                'approvalDate'        => date("F j, Y")
            ]);
            $taskscheduled = $this->UpdateAlertTable($alertdisc, $url, '', $request['attension'], 'tblcontractDetails', $contid, $status);
            $conDetail = DB::table('tblcontractDetails')->where('ID', $contid)->first();
            $usr = Auth::user()->username;
            $this->addLogg("Approval remark for contract with File Number:$conDetail->fileNo and Description: $conDetail->ContractDescriptions by $usr", "Approval Remark");
        }
        $data['tablecontent']            = $this->getTable2($request['contracttype'], $request['status'], Auth::user()->username);
        foreach ($data['tablecontent']  as $key => $value) {
            $line = (array) $value;
            $line['contractBalance'] = $this->contractBalance($value->ID);
            $line['comments'] = '0';
            $line['comments2'] = '0';
            $line['comments3'] = '0';
            $com = DB::table('tblcomments')->where('affectedID', $value->ID)->where('commenttypeID', 1)->select('tblcomments.comment', 'tblcomments.username', 'tblcomments.added')->orderby('id', 'asc')->get();
            $line['activate_again'] = 0;
            if ($com) {
                foreach ($com as $k => $list) {
                    $newline = (array) $list;
                    $name = DB::table('users')->where('username', $list->username)->first()->name;
                    $newline['name'] = $name;
                    $newline['comment'] = str_replace("\r\n", "<br>", $list->comment);
                    $date = strtotime($list->added);
                    $newline['date_added'] = date("F j, Y", $date);
                    $newline['time'] = date("g:i a", $date);
                    $newline = (object) $newline;
                    $com[$k] = $newline;
                }
                $line['comments'] = json_encode($com);
            }
            $com2 = DB::table('contract_comment')->where('fileNoID', $value->fileNo)->orderby('commentID', 'asc')->get();
            if ($com2) {
                foreach ($com2 as $k => $list) {
                    //$newline = (array) $list;
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
                $line['comments2'] = json_encode($com2);
            }

            if ($value->companyID == 13) {
                $com3 = DB::table('claim_comment')->where('claimID', $value->procurement_contractID)->orderby('id', 'asc')->get();
                if ($com3) {
                    foreach ($com3 as $k => $list) {
                        //$newline = (array) $list;
                        $newline = (array) [];
                        $name = DB::table('users')->where('id', $list->userID)->first()->name . "(" . $list->office . ")";
                        $newline['name'] = $name;
                        $newline['comment'] = str_replace("\r\n", "<br>", $list->comment);
                        $date = strtotime($list->created_at);
                        $newline['date_added'] = date("F j, Y", $date);
                        $newline['time'] = date("g:i a", $date);
                        $newline = (object) $newline;
                        $com3[$k] = $newline;
                    }
                    $line['comments3'] = json_encode($com3);
                }
            }
            $line = (object) $line;
            $data['tablecontent'][$key] = $line;
        }
        $data['companyDetails'] = $this->getBeneficiary();
        $data['fileRefer'] = [];
        $data['ApprovalReferal'] = $this->ApprovalReferal(Auth::user()->username);
        $data['DefaultComment'] = $this->DefaultComment(DB::table('tblaction_rank')->where('userid', Auth::user()->username)->value('code'));
        //dd($data['DefaultComment']);
        return view('funds.Procurements.approveprocurement', $data);
    }

    public function contractClaimReport(Request $request)
    {
        //Increase Memory Size
        ini_set('memory_limit', '-1');
        //////////////////////////////////
        $data['warning']                 = '';
        $data['success']                 = '';
        $data['error']                     = '';
        $data['datepicker1'] = $request['datepicker1'];
        $data['datepicker2'] = $request['datepicker2'];
        if ($data['datepicker1'] == null) {
            $data['datepicker1'] = Carbon::now()->subMonth();
        }

        if ($data['datepicker2'] == null) {
            $data['datepicker2'] = Carbon::now();
        }


        $data['procurementlist']         = $this->getProcurement();

        $data['contractorList']         = [];
        $data['contractlist']             = $this->getContract();
        $data['contractlist2']             = $this->getContract();

        $data['currentuser']             = Auth::user()->username;
        $data['contract_desc']             = trim($request['contract-desc']);
        $data['contractvalue']             = $request['contractvalue'];
        $data['companyid']                 = $request['companyid'];
        $data['date_awarded']             = $request['date_awarded'];
        $data['contracttype']             = $request['contracttype'];
        $data['fileno']                    = trim($request['fileno']);

        $data['status']                    = $request['status'];

        $data['tablecontent']            = $this->getContractQueryReport($request['contracttype'], $request['status'], $data['datepicker1'], $data['datepicker2']);
        foreach ($data['tablecontent']  as $key => $value) {
            $line = (array) $value;
            $line['contractBalance'] = $this->contractBalance($value->ID);
            $line = (object) $line;
            $data['tablecontent'][$key] = $line;
        }

        $data['companyDetails'] = $this->getBeneficiary();
        $data['procurementlist'] = $this->getProcurement();
        $data['paymemtstatus'] = $this->paymemtstatus();
        $data['ApprovalReferal'] = $this->ApprovalReferal(Auth::user()->username);
        return view('funds.Procurements.contractclaim', $data);
    }
    public function viewfile($id)
    {
        $data['name'] = $id;
        return view('filex.filex', $data);
    }

    public function approveforAwaitingAction(Request $request)
    {
        //Increase Memory Size
        ini_set('memory_limit', '-1');
        //////////////////////////////////
        $data['warning']                 = '';
        $data['success']                 = '';
        $data['error']                     = '';
        $data['awaitingby']             = $request['awaitingby'];
        $data['ptype']             = $request['ptype'];
        if (isset($_POST['archive'])) {
            DB::table('tblcomments')->insert([
                'commenttypeID' => 1,
                'affectedID' => $request['id'],
                'username' => Auth::user()->username,
                'comment' => preg_replace('/\s\s+/', ' ', $request['instruction']) . 'archived by ' . Auth::user()->username
            ]);
            DB::table('tblcontractDetails')->where('ID', $request['id'])->update(['is_archive' => 1, 'openclose'     => 0]);
        }

        if (isset($_POST['s_remark'])) {
            $request['instruction'] = $this->UpdateDefaultComment($request['commentid'], trim(preg_replace('/\s\s+/', ' ', $request['instruction'])), DB::table('tblaction_rank')->where('userid', Auth::user()->username)->value('code'));

            DB::table('tblcomments')->insert([
                'commenttypeID' => 1,
                'affectedID' => $request['contid'],
                'username' => Auth::user()->username,
                'comment' => $request['instruction'] . ' (refer to ' . $request['attension'] . ')'
            ]);
            $openclose = 0;
            $approvalStatus = 0;
            $url = 'procurement/approve';
            $alertdisc = 'Payment approval review';

            if ($request['attension'] == 'HC') {
                $openclose = 1;
                $isrejected = 0;
                $approvalStatus = 1;
                $url = 'create/salary-voucher';
                $alertdisc = 'Unassigned payment';
                $status = 1;
                if (DB::table('tblpaymentTransaction')->where('contractID', $request['contid'])->update(['is_archive' => 0, 'isrejected' => 0, 'vstage' => -1])) $status = 0;
            }
            if ($request['attension'] == 'OC') {
                $openclose = 1;
                $isrejected = 0;
                $approvalStatus = 1;
                $url = 'create/contract';
                $alertdisc = 'Unassigned payment';
                $status = 1;
                if (DB::table('tblpaymentTransaction')->where('contractID', $request['contid'])->update(['is_archive' => 0, 'isrejected' => 0, 'vstage' => -1])) $status = 0;
            }
            if ($request['attension'] == 'HS') {
                $openclose = 1;
                $isrejected = 0;
                $approvalStatus = 1;
                $url = 'create/salary-voucher';
                $alertdisc = 'Unassigned payment';
                $status = 1;
                if (DB::table('tblpaymentTransaction')->where('contractID', $request['contid'])->update(['is_archive' => 0, 'isrejected' => 0, 'vstage' => -1])) $status = 0;
            }
            if ($request['attension'] == 'AD') {
                $openclose = 1;
                $isrejected = 0;
                $approvalStatus = 1;
                $url = 'create/advances';
                $alertdisc = 'Unassigned payment';
                $status = 1;
                if (DB::table('tblpaymentTransaction')->where('contractID', $request['contid'])->update(['is_archive' => 0, 'isrejected' => 0, 'vstage' => -1])) $status = 0;
            }
            if ($request['attension'] == 'HEC') {
                $openclose = 1;
                $isrejected = 0;
                $approvalStatus = 1;
                $url = 'take-liability';
                $alertdisc = 'Incoming Liability';
                $status = 1;
                $c_details = DB::table('tblcontractDetails')->where('ID', $request['contid'])->first();
                if (DB::table('tblliability_taken')->where('contractID', $request['contid'])->first()) {
                    //dd($request['contid']);
                    DB::table('tblliability_taken')->where('contractID', $request['contid'])->update([
                        'fileno' => $c_details->fileNo,
                        'economic_id' => 0,
                        'amount' =>  $c_details->contractValue,
                        'decription' => $c_details->ContractDescriptions,
                        'beneficiary_id' => $c_details->companyID,
                        'beneficiary' => $c_details->beneficiary,
                        'date_awarded' => $c_details->dateAward,
                        'status' => 0,
                        'created_by' => Auth::user()->id,
                        'time_cleared' => '2020-12-31', //tobe deleted
                        'created_at' => '2020-12-31', //tobe deleted
                        'period' => $this->ActivePeriod(),

                    ]);
                } else {
                    //dd("1223");
                    DB::table('tblliability_taken')->insert([
                        'contractID' => $request['contid'],
                        'fileno' => $c_details->fileNo,
                        'economic_id' => 0,
                        'amount' =>  $c_details->contractValue,
                        'decription' => $c_details->ContractDescriptions,
                        'beneficiary_id' => $c_details->companyID,
                        'beneficiary' => $c_details->beneficiary,
                        'date_awarded' => $c_details->dateAward,
                        'status' => 0,
                        'time_cleared' => '2020-12-31', //tobe deleted
                        'created_at' => '2020-12-31', //tobe deleted
                        'created_by' => Auth::user()->id,
                        'period' => $this->ActivePeriod(),

                    ]);
                }
            }
            DB::table('tblcontractDetails')->where('ID', $request['contid'])->update([
                'awaitingActionby' => $request['attension'],
                'openclose'     => $openclose,
                'approvalStatus'     => $approvalStatus,
                'approvedBy'        => Auth::user()->name,
                'approval_last_action_by'        =>  DB::table('tblcontractDetails')->where('ID', '=', $request['contid'])->value('awaitingActionby'),
                'approvalDate'        => date("F j, Y")
            ]);
            $taskscheduled = $this->UpdateAlertTable($alertdisc, $url, '', $request['attension'], 'tblcontractDetails', $request['contid'], 1);
            $conDetail = DB::table('tblcontractDetails')->where('ID', $request['contid'])->first();
            $usr = Auth::user()->username;
            $this->addLogg("Awaiting Approval remark for contract with File Number:$conDetail->fileNo and Description: $conDetail->ContractDescriptions by $usr", "Awaiting Approval Remark");
        }
        $data['tablecontent']            = $this->UnprecessApprovedList($data['awaitingby'], $data['ptype']);
        //dd($data['tablecontent']);
        foreach ($data['tablecontent']  as $key => $value) {
            $line = (array) $value;
            $line['contractBalance'] = $this->contractBalance($value->ID);
            $line['comments'] = '0';
            $line['comments2'] = '0';
            $line['comments3'] = '0';
            $com = DB::table('tblcomments')->where('affectedID', $value->ID)->where('commenttypeID', 1)->select('tblcomments.comment', 'tblcomments.username', 'tblcomments.added')->orderby('id', 'asc')->get();
            $line['activate_again'] = 0;

            if ($com) {
                foreach ($com as $k => $list) {
                    $newline = (array) $list;
                    $name = DB::table('users')->where('username', $list->username)->first()->name;
                    $newline['name'] = $name;
                    $newline['comment'] = str_replace("\r\n", "<br>", $list->comment);
                    $date = strtotime($list->added);
                    $newline['date_added'] = date("F j, Y", $date);
                    $newline['time'] = date("g:i a", $date);
                    $newline = (object) $newline;
                    $com[$k] = $newline;
                }
                $line['comments'] = json_encode($com);
            }

            $com2 = DB::table('contract_comment')->where('fileNoID', $value->fileNo)->orderby('commentID', 'asc')->get();

            if ($com2) {
                foreach ($com2 as $k => $list) {
                    //$newline = (array) $list;
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
                $line['comments2'] = json_encode($com2);
            }
            if ($value->companyID == 13) {
                $com3 = DB::table('claim_comment')->where('claimID', $value->procurement_contractID)->orderby('id', 'asc')->get();
                if ($com3) {
                    foreach ($com3 as $k => $list) {
                        //$newline = (array) $list;
                        $newline = (array) [];
                        $name = DB::table('users')->where('id', $list->userID)->first()->name . "(" . $list->office . ")";
                        $newline['name'] = $name;
                        $newline['comment'] = str_replace("\r\n", "<br>", $list->comment);
                        $date = strtotime($list->created_at);
                        $newline['date_added'] = date("F j, Y", $date);
                        $newline['time'] = date("g:i a", $date);
                        $newline = (object) $newline;
                        $com3[$k] = $newline;
                    }
                    $line['comments3'] = json_encode($com3);
                }
            }
            $line = (object) $line;
            $data['tablecontent'][$key] = $line;
        }
        $data['Staff_Contract'] = $this->Staff_Contract();
        $data['ApprovalReferal'] = $this->ApprovalReferal(Auth::user()->username);
        $data['ApprovalReferal2'] = $this->ApprovalReferal(0);
        $data['ApprovalReferal'] = $this->ApprovalReferal(Auth::user()->username);
        $data['DefaultComment'] = $this->DefaultComment(DB::table('tblaction_rank')->where('userid', Auth::user()->username)->value('code'));
        return view('funds.Procurements.approveforawaiting', $data);
    }

    public function ArchiveApproval(Request $request)
    {
        //Increase Memory Size
        ini_set('memory_limit', '-1');
        //////////////////////////////////
        $data['warning']                 = '';
        $data['success']                 = '';
        $data['error']                     = '';
        $data['awaitingby']             = $request['awaitingby'];
        $data['ptype']             = $request['ptype'];


        if (isset($_POST['archive'])) {
            DB::table('tblcomments')->insert([
                'commenttypeID' => 1,
                'affectedID' => $request['id'],
                'username' => Auth::user()->username,
                'comment' => preg_replace('/\s\s+/', ' ', $request['instruction']) . ' restored from archive by ' . Auth::user()->name
            ]);
            DB::table('tblcontractDetails')->where('ID', $request['id'])->update(['is_archive' => 0, 'openclose'     => 0]);
            DB::table('tblpaymentTransaction')->where('contractID', $request['id'])->update(['is_archive' => 0]);
        }

        $data['tablecontent']            = $this->ArchiveApprovedList($data['awaitingby'], $data['ptype']);
        foreach ($data['tablecontent']  as $key => $value) {
            $line = (array) $value;
            $line['contractBalance'] = $this->contractBalance($value->ID);
            $line['comments'] = '0';
            $line['comments2'] = '0';
            $line['comments3'] = '0';
            $com = DB::table('tblcomments')->where('affectedID', $value->ID)->where('commenttypeID', 1)->select('tblcomments.comment', 'tblcomments.username', 'tblcomments.added')->orderby('id', 'asc')->get();
            $line['activate_again'] = 0;

            if ($com) {
                foreach ($com as $k => $list) {
                    $newline = (array) $list;
                    $name = DB::table('users')->where('username', $list->username)->first()->name;
                    $newline['name'] = $name;
                    $newline['comment'] = str_replace("\r\n", "<br>", $list->comment);
                    $date = strtotime($list->added);
                    $newline['date_added'] = date("F j, Y", $date);
                    $newline['time'] = date("g:i a", $date);
                    $newline = (object) $newline;
                    $com[$k] = $newline;
                }
                $line['comments'] = json_encode($com);
            }
            $com2 = DB::table('contract_comment')->where('fileNoID', $value->fileNo)->orderby('commentID', 'asc')->get();

            if ($com2) {
                foreach ($com2 as $k => $list) {
                    //$newline = (array) $list;
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
                $line['comments2'] = json_encode($com2);
            }
            if ($value->companyID == 13) {
                $com3 = DB::table('claim_comment')->where('claimID', $value->procurement_contractID)->orderby('id', 'asc')->get();
                if ($com3) {
                    foreach ($com3 as $k => $list) {
                        //$newline = (array) $list;
                        $newline = (array) [];
                        $name = DB::table('users')->where('id', $list->userID)->first()->name . "(" . $list->office . ")";
                        $newline['name'] = $name;
                        $newline['comment'] = str_replace("\r\n", "<br>", $list->comment);
                        $date = strtotime($list->created_at);
                        $newline['date_added'] = date("F j, Y", $date);
                        $newline['time'] = date("g:i a", $date);
                        $newline = (object) $newline;
                        $com3[$k] = $newline;
                    }
                    $line['comments3'] = json_encode($com3);
                }
            }
            $line = (object) $line;
            $data['tablecontent'][$key] = $line;
        }
        $data['Staff_Contract'] = $this->Staff_Contract();
        $data['ApprovalReferal'] = $this->ApprovalReferal(Auth::user()->username);
        $data['ApprovalReferal2'] = $this->ApprovalReferal(0);
        return view('funds.Procurements.archive', $data);
    }

    public function ClearedApproval(Request $request)
    {
        //Increase Memory Size
        ini_set('memory_limit', '-1');
        //////////////////////////////////
        $data['warning']                 = '';
        $data['success']                 = '';
        $data['error']                     = '';
        $data['ptype']             = $request['ptype'];
        $data['tablecontent']            = $this->ClearedApprovedList($data['ptype']);
        $data['Staff_Contract'] = $this->Staff_Contract();
        return view('Procurements.clearedapproval', $data);
    }
    //public function ContractLiability(Request $request)
    public function newprocurement2(Request $request)
    {

        //Increase Memory Size
        ini_set('memory_limit', '-1');
        ini_set('upload_max_filesize', '10M');
        ini_set('post_max_size', '10M');
        ini_set('max_input_time', 300);
        ini_set('max_execution_time', 300);
        //////////////////////////////////
        $data['warning'] = '';
        $data['success'] = '';
        $data['error'] = '';



        $data['procurementlist'] = $this->getProcurementReassignable2();

        $data['contractorList'] = [];
        $data['contractlist'] = $this->getContract();
        $data['contractlist2'] = $this->getContract();

        $data['currentuser']         = Auth::user()->username;
        $data['contract_desc']         = trim($request['contract-desc']);
        $data['contractvalue']         = preg_replace('/[^\d.]/', '', $request['contractvalue']);
        $data['attension']         = $request['attension'];
        $data['companyid']             = $request['companyid'];
        $data['date_awarded']         = $request['date_awarded'];
        $data['contracttype']         = $request['contracttype'];
        $data['fileno']                = trim($request['fileno']);
        $contracttype = $data['contracttype'];
        $companyid = $data['companyid'];
        $contract_desc = $data['contract_desc'];
        $fileno = $data['fileno'];
        $date_awarded = $data['date_awarded'];

        if (!empty($request['deleteid'])) {
            $id = $request['deleteid'];
            $claimid = DB::table('tblcontractDetails')->where('ID', $request['deleteid'])->where('approvalStatus', 0)->value('claimid');
            DB::table('tblselectedstaffclaim')->where('claimID', $claimid)->delete();
            DB::table('staffclaimfile')->where('claimID', $claimid)->delete();
            DB::table('tblclaim')->where('ID', $claimid)->delete();
            if (DB::table('tblcontractDetails')->where('ID', $request['deleteid'])->where('approvalStatus', 0)->delete()) {
                $taskscheduled = $this->UpdateAlertTable("", '', '', '', 'tblcontractDetails', $request['deleteid'], 0);
                $data['success'] = "Record was deleted successfully!";
            }
        }

        if ($request['edit-hidden'] == 1) {
            $createdby   = $request['creatdby'];
            $chk = DB::table('tblcontractDetails')->where('ID', $request['id'])->first();
            $comp = DB::table('tblcontractor')->where('id', '=', $chk->companyID)->first();
            $this->validate($request, ['actionby' => 'required', 'contr_desc' => 'required', 'bene' => 'required',]);
            if ($chk) {

                DB::table('tblcontractDetails')->where('ID', $request['id'])
                    ->update([
                        'awaitingActionby' => $request['actionby'],
                        'ContractDescriptions' => $request['contr_desc'],
                        'beneficiary' => $request['bene'],
                        //'createdby'	=>  Auth::user()->id
                    ]);

                $taskscheduled = $this->UpdateAlertTable("Payment approval review", 'procurement/approve', '', $request['actionby'], 'tblcontractDetails', $request['id'], 1);
                if ($request->file('filex') != null) {
                    $file = $request->file('filex');
                    $img = $request['id'] . "_" . $this->RefNo() . '.' . $file->getClientOriginalExtension();
                    $file->move(env('Public_Path', '') . "/attachments", $img);
                    DB::table('tblcontractfile')->insert([
                        'file_desc' => "Approval document",
                        'filename' =>  '/attachments/' . $img,
                        'contractid' => $request['id'],
                        'createdby' => Auth::user()->id,
                    ]);
                }

                return back()->with('message', 'Modification successful.');
            } else {
                $data['error'] = "Oops something went wrong!";
            }
        }

        $data['companyDetails'] = $this->getBeneficiary();
        $data['fileRefer'] = [];
        $data['procurementlist'] = $this->getProcurementReassignable2();
        foreach ($data['procurementlist'] as $key => $value) {
            $line = (array) $value;
            $reason = "";
            if ($line['approvalStatus'] == 2) {
                $reason = DB::table('tblcomments')->where('affectedID', $value->ID)->where('commenttypeID', 1)->latest('id')->first()->comment;
            }
            $line['reason'] = $reason;
            $data['procurementlist'][$key] = (object) $line;
        }
        $data['officers'] = DB::table('tblaction_rank')->where('preapproval', 1)->orderby('rankorder')->get();
        return view('funds.Procurements.newprocurement2', $data);
    }

    public function StaffBenficiaryController(Request $request, $cid = null)
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
        $data['claimid'] = $request['claimid'];
        //dd($cid);
        $request['cid'] = $cid;
        $data['ID'] = '';
        if (!$data['claimid']) $data['claimid'] = DB::table('tblcontractDetails')->where('ID', $request['cid'])->value('claimid');
        $data['totalclaim'] = 0;
        $claiminfo = DB::table('tblcontractDetails')->where('claimid', $data['claimid'])->first();
        if ($claiminfo) {
            $data['totalclaim'] = $claiminfo->contractValue;
            $data['ID'] = $claiminfo->ID;
        }
        $clmid = $data['claimid'];
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
            //add manual to session
            //return back()->with('message','addedd  successfully added.'  );
            return  redirect('create/procurement-staff-beneficiary/' . $claiminfo->ID)
                ->with('message', 'addedd  successfully added.')
                ->with('claimid', $data['claimid'])
                ->with('active_tab', 'manual');
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
                    return back()->with('err', 'This operation cannot be performed because the total sum will exceed approved value.')->with('active_tab', 'welfare');
                }

                DB::commit();

                return  redirect('create/procurement-staff-beneficiary/' . $claiminfo->ID)
                    ->with('message', 'Staff successfully added using voucher parameters.')
                    ->with('claimid', $claimId)
                    ->with('active_tab', 'welfare');
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
            return  redirect('create/procurement-staff-beneficiary/' . $claiminfo->ID)->with('message', 'successfully modified.')->with('claimid', $data['claimid'])->with('active_tab', 'manual');
        }
        if (isset($_POST['delete'])) {
            $this->validate($request, ['beneid' => 'required']);
            DB::table('tblselectedstaffclaim')->where('selectedID', $request['beneid'])->delete();
            return  redirect('create/procurement-staff-beneficiary/' . $claiminfo->ID)->with('message', 'successfully modified.')->with('claimid', $data['claimid'])->with('active_tab', 'manual');
        }
        //check if has overtimeUniqueCode then insert all to selectedstaffclaim table
        if($claiminfo->overtuniqueCode != ''){
            $trials = DB::table('overtime_trial')
            ->where('overtime_trial.uniqueCode', '=', $claiminfo->overtuniqueCode)
            ->leftjoin('tblper', 'tblper.ID', 'overtime_trial.staffID')
            ->leftJoin('overtime_template', 'overtime_template.id', 'tblper.overtimeType')
            ->select('overtime_trial.*', 'tblper.surname', 'tblper.first_name', 'tblper.othernames', 'tblper.grade', 'tblper.step', 'overtime_template.hrs', 'overtime_template.description')
            ->orderBy('tblper.grade', 'desc')
            ->orderBy('tblper.step', 'desc')->get();

            if(DB::table('tblselectedstaffclaim')->where('claimID', '=', $data['claimid'])->first()){
                DB::table('tblselectedstaffclaim')->where('claimID', '=', $data['claimid'])->delete();
            }
            
            foreach($trials as $key => $trial){
                DB::table('tblselectedstaffclaim')->insert([
                    'claimID' => $data['claimid'],
                    'staffamount' =>  $trial->amount,
                    'staffID' => $trial->staffID,
                ]);
            }
            
        }

        $data['Claimlist'] = (Session::get('special') == 1) ? DB::table('tblcontractDetails')->where('openclose', 0)->where('companyID', 13)->where('awaitingActionby', '<>', 'OC')->where('awaitingActionby', '<>', 'AD')->where('approvalStatus', 0)->get() :
            DB::table('tblcontractDetails')->where('approvalStatus', 1)->where('openclose', 1)->where('companyID', 13)->get();
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
        return view('funds.Procurements.beneficiarylist', $data);
    }

    public function LiabilityTaken(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $data['companyid'] = $request['companyid'];
        $data['economics'] = $request['economics'];
        $data['description'] = $request['description'];
        $data['contracttype'] = $request['contracttype'];
        $data['date_awarded'] = $request['date_awarded'];
        $data['fileno'] = $request['fileno'];
        $request['contractvalue']         = preg_replace('/[^\d.]/', '', $request['contractvalue']);
        $data['contractvalue'] = $request['contractvalue'];
        $data['description'] = $request['description'];
        if (isset($_POST['add'])) {
            $this->validate($request, [
                'economics' => 'required',
                'description' => 'required',
                'companyid' => 'required',
                'date_awarded' => 'required',
                'fileno' => 'required',
                'contractvalue' => 'required|numeric'
            ]);
            DB::table('tblliability_taken')->insert([
                'fileno' => $data['fileno'],
                'economic_id' => $data['economics'],
                'amount' =>  $data['contractvalue'],
                'decription' => $data['description'],
                'beneficiary_id' => $data['companyid'],
                'beneficiary' => db::table('tblcontractor')->where('id', $data['companyid'])->value('contractor'),
                'date_awarded' => $data['date_awarded'],
                'time_cleared' => $this->ProcessDATE($data['economics']), //'2020-12-31',//tobe deleted
                'created_at' => $this->ProcessDATE($data['economics']), //tobe deleted
                'is_cleared' => 0,
                'created_by' => Auth::user()->id,
                'period' => $this->ActivePeriod(),

            ]);
            return back()->with('message', 'addedd  successfully added.');
        }
        if (isset($_POST['update'])) {
            $this->validate($request, [
                'economics' => 'required',
                'description' => 'required',
                'companyid' => 'required',
                'date_awarded' => 'required',
                'fileno' => 'required',
                'contractvalue' => 'required|numeric'
            ]);
            $id = $request['id'];
            DB::table('tblliability_taken')->where('id', $id)->update([
                'fileno' => $data['fileno'],
                'economic_id' => $data['economics'],
                'amount' =>  $data['contractvalue'],
                'decription' => $data['description'],
                'beneficiary_id' => $data['companyid'],
                'status' => ($request['istaken']) ? $request['istaken'] : 0,
                'is_cleared' => ($request['iscleared']) ? $request['iscleared'] : 0,
                'beneficiary' => db::table('tblcontractor')->where('id', $data['companyid'])->value('contractor'),
                'date_awarded' => $data['date_awarded'],
                'time_cleared' => $this->ProcessDATE($data['economics']), //'2020-12-31',//tobe deleted
                'created_at' => $this->ProcessDATE($data['economics']), //tobe deleted
                'period' => $this->ActivePeriod(),
            ]);
        }
        if (isset($_POST['decline'])) {
            $id = $request['lid'];
            $this->validate($request, [
                'lid'       => 'required',
                'comment'  => 'required'
            ]);
            $Vdetails = DB::table('tblcontractDetails')->where('ID', $id)->first();
            if (DB::table('tblcontractDetails')->where('ID', $id)->update([
                'awaitingActionby'     => $Vdetails->approval_last_action_by,
                'openclose'            => 0,
                'approvalStatus'     => 0,
                'isrejected'     => 1,
                'OC_staffId' => null
            ])) {
                $theid = $id;
                $user = Auth::user()->username;
                $commenttypeID = 2;
                $comment = trim($request['comment']) . ": Rejected by " . Auth::user()->name . " at Expenditure";
                DB::Select("DELETE FROM `tblliability_taken` WHERE `id`='$id'");
                DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $id, 'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
                $taskscheduled = $this->UpdateAlertTable("Liability Rejection", '0', '0', 'OC', 'tblcontractDetails', $id, 0);
                $data['success'] = "Voucher has been Declined successfully!";
                $this->addLogg("Liablity Rejection with ID: $id and Description: $Vdetails->ContractDescriptions by $user Reason: $comment", "Voucher Rejected");
            } else {
                $data['error'] = "Whoops! something went wrong please try again";
            }
        }
        $data['contractlist'] = $this->getContract();
        $data['companyDetails'] = $this->getBeneficiary();

        //=============search by date=================
        $getYear    =  Session::get('getYear');
        $getFrom    =  Session::get('getFrom');
        $getTo      =  Session::get('getTo');
        //year
        if ($getYear == '' or $getYear < 1) {
            $getSearchYear = date('Y');
        } else {
            $getSearchYear = $getYear;
        }
        //date (Current days in the year: $dayNumber = date("z") + 1;)
        if ($getFrom == '' or $getTo == '') {
            $getSearchFrom = date('Y-01-01');
            $getSearchTo   = date('Y-m-d');
        } else {
            $getSearchFrom = $getFrom;
            $getSearchTo   = $getTo;
        }
        //pass data back
        $data['getYear'] = $getYear;
        $data['getFrom'] = $getFrom;
        $data['getTo']   = $getTo;
        //============end search by date===============

        $data['procurementlist'] = $this->ContractLiabilityRecord(1, 0, $getSearchFrom, $getSearchTo, $getSearchYear);
        //dd($data['procurementlist']);
        $data['economiccodes'] = db::table('tbleconomicCode')->leftJoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
            ->leftjoin('tblcontractType', 'tbleconomicCode.contractGroupID', '=', 'tblcontractType.ID')
            ->select('tbleconomicCode.*', 'tbleconomicHead.economicHead', 'tblcontractType.contractType')->orderby('economicCode')->get();
        //dd($data['economiccodes']);
        $data['ClearanceStatus'] = db::table('tblliability_status')->where('is_cleared', 1)->Get();
        $data['TakenStatus'] = db::table('tblliability_status')->where('is_taken', 1)->Get();
        return view('funds.Procurements.liability', $data);
    }

    public function LiabilityTakenStaffOld05032026(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $data['companyid'] = $request['companyid'];
        $data['economics'] = $request['economics'];
        $data['description'] = $request['description'];
        $data['contracttype'] = $request['contracttype'];
        $data['date_awarded'] = $request['date_awarded'];
        $data['fileno'] = $request['fileno'];
        $request['contractvalue']         = preg_replace('/[^\d.]/', '', $request['contractvalue']);
        $data['contractvalue'] = $request['contractvalue'];
        $data['description'] = $request['description'];
        if (isset($_POST['update'])) {
            $this->validate($request, [
                'economics' => 'required',
                'description' => 'required',
                // 'companyid' => 'required',
                'date_awarded' => 'required',
                'fileno' => 'required',
                'contractvalue' => 'required|numeric'
            ]);
            $liabilityDetails = DB::table('tblliability_taken')->where('id', $request['id'])->first();
            $contractDetails = DB::table('tblcontractDetails')->where('ID', $liabilityDetails->contractID)->first();
            //when you add voucher ID to tblliability_taken do not forget to change the code below to get voucher details using voucher ID instead of contract ID
            $Vdetails = DB::table('tblpaymentTransaction')->where('contractID', $contractDetails->ID)->first();
            $id = $request['id'];
            $remark = "Liability Taken" . " " . $data['description'];

            //check for enough money before taking liability
            // $Vdetails = DB::table('tblpaymentTransaction')->where('ID', $id)->first();
            // $voultbal = $this->VoultBalance($data['economics']);
            // // $selectliability = DB::select("SELECT IFNULL( sum(`amount`),0) as tsum FROM `tblliability_taken` WHERE `id`='$lid' and `is_cleared`=0 ")[0]->tsum;
            // if ($liabilityDetails->amount > 0 and (floor($liabilityDetails->amount) != floor($Vdetails->totalPayment))) return back()->with('err', 'There is variance in the initial liability and actual voucher. Hence voucher not pass!');
            // if (floor($voultbal + floor($liabilityDetails->amount) - floor($this->OutstandingLiabilityNew($Vdetails->economicCodeID))) < floor($Vdetails->totalPayment)) {
            //     $data['error'] = "Insufficient Vote Balance!!! Liability cannot be taken for this transaction";
            // }

            DB::table('tblliability_taken')->where('id', $id)->update([
                'fileno' => $data['fileno'],
                'economic_id' => $data['economics'],
                'amount' =>  $data['contractvalue'],
                'decription' => $data['description'],
                'beneficiary_id' => $data['companyid'],
                'status' => ($request['istaken']) ? $request['istaken'] : 0,
                // 'is_cleared' => ($request['iscleared']) ? $request['iscleared'] : 0,
                // 'beneficiary' => db::table('tblcontractor')->where('id', $data['companyid'])->value('contractor'),
                // 'date_awarded' => $data['date_awarded'],
                'time_cleared' => $this->ProcessDATE($data['economics']),
                // 'created_at' => $this->ProcessDATE($data['economics']),
                'period' => $this->ActivePeriod(),
            ]);

            if ($request['istaken'] == 1) {
                DB::table('tblpaymentTransaction')->where('contractID', $contractDetails->ID)->update([
                    'liabilityBy'           => Auth::user()->id,
                    'liabilityStatus'       => 1
                ]);
                $this->VotebookUpdate($data['economics'], $Vdetails->ID, $remark, $Vdetails->amtPayable, Date('Y-m-d'), 2);
            }
            if ($request['istaken'] == 0) {
                $this->VotebookUpdate($data['economics'], $Vdetails->ID, $remark, $Vdetails->amtPayable, Date('Y-m-d'), 5);
            }
            if ($request['iscleared'] == 1) {
                DB::table('tblpaymentTransaction')->where('contractID', $contractDetails->ID)->update([
                    'dateTakingLiability'           => $this->ProcessDATE($liabilityDetails->economic_id)
                ]);
                // $this->VotebookUpdate($data['economics'],$Vdetails->ID,$remark,$Vdetails->amtPayable,Date('Y-m-d'),2);
            }
        }
        if (isset($_POST['decline'])) {
            $id = $request['lid'];
            $this->validate($request, [
                'lid'       => 'required',
                'comment'  => 'required'
            ]);
            $Vdetails = DB::table('tblcontractDetails')->where('ID', $id)->first();
            if (DB::table('tblcontractDetails')->where('ID', $id)->update([
                'awaitingActionby'     => $Vdetails->approval_last_action_by,
                'openclose'            => 0,
                'approvalStatus'     => 0,
                'isrejected'     => 1,
                'OC_staffId' => null
            ])) {
                $theid = $id;
                $user = Auth::user()->username;
                $commenttypeID = 2;
                $comment = trim($request['comment']) . ": Rejected by " . Auth::user()->name . " at Expenditure";
                DB::Select("DELETE FROM `tblliability_taken` WHERE `id`='$id'");
                DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $id, 'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
                $taskscheduled = $this->UpdateAlertTable("Liability Rejection", '0', '0', 'OC', 'tblcontractDetails', $id, 0);
                $data['success'] = "Voucher has been Declined successfully!";
                $this->addLogg("Liablity Rejection with ID: $id and Description: $Vdetails->ContractDescriptions by $user Reason: $comment", "Voucher Rejected");
            } else {
                $data['error'] = "Whoops! something went wrong please try again";
            }
        }
        $data['contractlist'] = $this->getContract();
        $data['companyDetails'] = $this->getBeneficiary();

        //=============search by date=================
        $getYear    =  Session::get('getYear');
        $getFrom    =  Session::get('getFrom');
        $getTo      =  Session::get('getTo');
        //year
        if ($getYear == '' or $getYear < 1) {
            $getSearchYear = date('Y');
        } else {
            $getSearchYear = $getYear;
        }
        //date (Current days in the year: $dayNumber = date("z") + 1;)
        if ($getFrom == '' or $getTo == '') {
            $getSearchFrom = date('Y-01-01');
            $getSearchTo   = date('Y-m-d');
        } else {
            $getSearchFrom = $getFrom;
            $getSearchTo   = $getTo;
        }
        //pass data back
        $data['getYear'] = $getYear;
        $data['getFrom'] = $getFrom;
        $data['getTo']   = $getTo;
        //============end search by date===============

        $data['procurementlist'] = $this->ContractLiabilityRecord2(1, 0, $getSearchFrom, $getSearchTo, $getSearchYear);
        //dd($data['procurementlist']);
        $data['economiccodes'] = db::table('tbleconomicCode')->leftJoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
            ->leftjoin('tblcontractType', 'tbleconomicCode.contractGroupID', '=', 'tblcontractType.ID')
            ->select('tbleconomicCode.*', 'tbleconomicHead.economicHead', 'tblcontractType.contractType')->orderby('economicCode')->get();
        //dd($data['economiccodes']);
        $data['ClearanceStatus'] = db::table('tblliability_status')->where('is_cleared', 1)->Get();
        $data['TakenStatus'] = db::table('tblliability_status')->where('is_taken', 1)->Get();
        return view('funds.Procurements.staffliability', $data);
    }

    public function LiabilityTakenStaff(Request $request)
    {

        //=============search by date=================
        $getYear    =  Session::get('getYear');
        $getFrom    =  Session::get('getFrom');
        $getTo      =  Session::get('getTo');
        //year
        if ($getYear == '' or $getYear < 1) {
            $getSearchYear = date('Y');
        } else {
            $getSearchYear = $getYear;
        }
        //date (Current days in the year: $dayNumber = date("z") + 1;)
        if ($getFrom == '' or $getTo == '') {
            $getSearchFrom = date('Y-01-01');
            $getSearchTo   = date('Y-m-d');
        } else {
            $getSearchFrom = $getFrom;
            $getSearchTo   = $getTo;
        }
        //pass data back
        $data['getYear'] = $request['getYear'];
        $data['getFrom'] = $request['getFrom'];
        $data['getTo']   = $request['getTo'];
        //============end search by date===============

        $data['warning'] = '';
        $data['success'] = '';
        $data['error'] = '';

        // if (DB::table('tblpaymentTransaction')->where('ID', $request['vid'])->update(['liabilityBy'    => $request['as_user']])) {
        //     $taskscheduled = $this->UpdateAlertTable("Voucher Clearance", 'voucher/liability', $request['as_user'], '0', 'tblpaymentTransaction', $request['vid'], 1);
        // }

        if (isset($_POST['process'])) {
            $id = $request['vid'];
            $lid = $request['lid'];
            $year = $request['year'];
            $ctType = $request['ctType'];

            $yearChanged = 0;

            //get active period
            $period = DB::table('tblactiveperiod')->where('contractTypeID', $ctType)->value('year');
            if ($ctType == 4) {
                $existingActiveYear = $period;
                if ($period != $year) {
                    DB::table('tblactiveperiod')->where('contractTypeID', $ctType)->update(['year' => $year]);
                    $yearChanged = 1;
                }
            }

            $Vdetails = DB::table('tblpaymentTransaction')->where('ID', $id)->first();
            $voultbal = $this->VoultBalance($Vdetails->economicCodeID);
            $selectliability = DB::select("SELECT IFNULL( sum(`amount`),0) as tsum FROM `tblliability_taken` WHERE `id`='$lid' and `is_cleared`=0 ")[0]->tsum;
            if ($selectliability > 0 and (floor($selectliability) != floor($Vdetails->totalPayment))) return back()->with('err', 'There is variance in the initial liability and actual voucher. Hence voucher not pass!');
            if (floor($voultbal + floor($selectliability) - floor($this->OutstandingLiabilityNew($Vdetails->economicCodeID))) < floor($Vdetails->totalPayment)) {
                $data['error'] = "Insufficient Vote Balance!!! Liability cannot be cleared for this transaction";
            } else {
                //dd($this->ProcessDATE($Vdetails->economicCodeID));
                $is_special = DB::table('tblpaymentTransaction')->where('ID', $id)->select('is_special', 'is_advances')->first();
                // dd($is_special);
                if (DB::table('tblpaymentTransaction')->where('ID', $id)->update([
                    'liabilityBy'           => Auth::user()->id,
                    'liabilityStatus'       => 1,
                    'vstage'                => ($is_special->is_special == 1 && $is_special->is_advances == 1) ? 4 : 999, //maintain on advances desk
                    'checkbyStatus'         => ($is_special->is_special == 1) ? 1 : 0,
                    'auditStatus'           => ($is_special->is_special == 1) ? 1 : 0,
                    'status'                => 2,
                    'isrejected'            => 0,
                    'is_archive'            => 0,
                    'liability_ref'         => ($request['lid']) ? $request['lid'] : 0,
                    'period' => ($ctType == 4 && $yearChanged == 1) ? $year : $period,
                    'dateTakingLiability'   =>   $this->ProcessDATE($Vdetails->economicCodeID), //  '2020-12-31',// date('Y-m-j')
                ])) {
                    DB::table('tblliability_taken')->where('id', $request['lid'])->update([
                        'clear_by'          => Auth::user()->id,
                        'status'            => 0,
                        'ref_voucher_id'    => $id,
                        'status'            => 1,
                        'is_cleared'        => 1,
                        'time_cleared'      =>     $this->ProcessDATE($Vdetails->economicCodeID), // date('Y-m-d h:i:s')
                    ]);
                    $remark = $Vdetails->paymentDescription;
                    $this->VotebookUpdate($Vdetails->economicCodeID, $Vdetails->ID, $remark, $Vdetails->totalPayment, Date('Y-m-d h:i:s'), 2);
                    $comment = trim($request['comment']) . ": Liability cleared for advances voucher by " . Auth::user()->name;
                    DB::table('tblcomments')->insert(['commenttypeID' => 2, 'affectedID' => $Vdetails->contractID, 'paymentID' => 0, 'username' => Auth::user()->username, 'comment' => $comment]);
                    $data['success'] = "Voucher Liability successfully cleared to Advances for further processing!";
                    $taskscheduled = $this->UpdateAlertTable("Voucher Clearance", 'advance-to-checking', '0', 'AD', 'tblpaymentTransaction', $id, 1);
                    $this->addlogg("Liability Taken for Voucher with ID: $id and Payment Description:$Vdetails->paymentDescription  awaiting advance push to checking for further processing!", "Liability taken for Voucher with ID: $id");
                } else {
                    $data['error'] = "Whoops! something went wrong please try again";
                }
            }

            if ($yearChanged == 1) {
                DB::table('tblactiveperiod')->where('contractTypeID', $ctType)->update(['year' => $existingActiveYear]);
            }
        }

        if (isset($_POST['decline'])) {
            $id = $request['vid'];
            $this->validate($request, [
                'vid'       => 'required',
                'comment'  => 'required'
            ]);
            //pitoff
            $getContractType = DB::table('tblpaymentTransaction')->where('ID', $id)->value('contractTypeID');
            if ($getContractType == 6) {
                $declineVstage = 2;
            } else {
                $declineVstage = 0;
            }
            //pitoff

            if (DB::table('tblpaymentTransaction')->where('ID', $id)->update([
                'liabilityStatus'     => 0,
                'vstage'         => $declineVstage,
                'status'         => 0,
                'isrejected'         => 1,
            ])) {
                $theid = $id;
                $user = Auth::user()->username;
                $commenttypeID = 2;
                $Vdetails = DB::table('tblpaymentTransaction')->where('ID', $id)->first();
                $comment = trim($request['comment']) . ": Rejected by " . Auth::user()->name . " at funds clearance stage";
                $cid = $Vdetails->contractID;
                DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $cid, 'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
                $taskscheduled = $this->UpdateAlertTable("Voucher Rejection", 'occheckby/voucher', '0', 'OC', 'tblpaymentTransaction', $id, 1);
                $data['success'] = "Voucher has been Declined successfully!";
                $this->addlogg("Voucher with ID: $id Rejected with reason: $comment", "Voucher with ID: $id Rejected");
            } else {
                $data['error'] = "Whoops! something went wrong please try again";
            }
        }

        if (isset($_POST['switch'])) {
            $id = $request['vid'];
            $this->validate($request, [
                'vid'       => 'required',
                'economiccode'       => 'required'
            ]);
            if (DB::table('tblpaymentTransaction')->where('ID', $id)->update([
                'economicCodeID'     => $request['economiccode'],
                'contractTypeID'     => DB::table('tbleconomicCode')->where('ID', $request['economiccode'])->value('contractGroupID')
            ])) {


                // -----------------------------
                // UPDATE tblcontractdetails HERE
                // -----------------------------


                // Fetch the updated contractTypeID
                $updatedContractTypeID = DB::table('tblpaymentTransaction')
                    ->where('ID', $id)
                    ->value('contractTypeID');

                // Fetch contract ID
                $contractID = DB::table('tblpaymentTransaction')
                    ->where('ID', $id)
                    ->value('contractID');

                // Update tblcontractdetails.contract_Type with contractTypeID
                DB::table('tblcontractDetails')
                    ->where('ID', $contractID)
                    ->update([
                        'contract_Type' => $updatedContractTypeID
                    ]);

                $theid = $id;
                $user = Auth::user()->username;
                $commenttypeID = 2;
                $Vdetails = DB::table('tblpaymentTransaction')->where('ID', $id)->first();
                $comment = "Ecomonmic code changed by " . Auth::user()->name . "to " . $request['economiccode'];
                $cid = $Vdetails->contractID;
                DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $cid, 'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
                $taskscheduled = $this->UpdateAlertTable("Voucher Rejection", 'occheckby/voucher', '0', 'OC', 'tblpaymentTransaction', $id, 0);
                $data['success'] = "Voucher has been switched successfully!";
                $this->addlogg("Voucher ID: $id $comment", "Voucher with ID: $id");
            } else {
                $data['error'] = "Whoops! something went wrong please try again";
            }
        }
        $data['UnitStaff'] = $this->UnitStaff('EC');
        $data['tablecontent'] = $this->VourcherGroupWithBalances($this->UnFundClearanceAdvance($getSearchFrom, $getSearchTo, $getSearchYear));
        $data['econocodeList'] = $this->AllEconomicsCode();
        return view('funds.Procurements.staffadvanceliability', $data);
    }

    public function UnprocessedRequest(Request $request)
    {
        //Increase Memory Size
        Session(['special' => 1]);
        ini_set('memory_limit', '-1');
        //////////////////////////////////
        $data['warning']                 = '';
        $data['success']                 = '';
        $data['error']                     = '';
        $data['awaitingby']             = $request['awaitingby'];
        //dd($request['awaitingby']);
        $data['ptype']             = $request['ptype'];

        $data['tablecontent']            = $this->UnprecessApprovedList($data['awaitingby'], $data['ptype']);
        foreach ($data['tablecontent']  as $key => $value) {
            $line = (array) $value;
            $line['contractBalance'] = $this->contractBalance($value->ID);
            $line['is_raised'] = 0; //DB::table('tblpaymentTransaction')->where('contractID',$value->ID)->value('contractID')? 1:0;
            $line = (object) $line;
            $data['tablecontent'][$key] = $line;
        }
        $data['Staff_Contract'] = $this->Staff_Contract();
        return view('funds.Procurements.unprocessedlist', $data);
    }

    public function gettin(Request $request, $id)
    {
        // Log::info("fecth". $id);


        $company = DB::table('tblcontractor')
            ->select('TIN')
            ->where('id', $id)
            ->first();

        return response()->json([
            'tin' => $company?->TIN
        ]);
    }


    public function downloadBeneficiaryTemplate233(Request $request)
    {
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=beneficiary_template.csv",
        ];

        $columns = ['fileNo', 'amount'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function downloadBeneficiaryTemplate(Request $request)
    {
        // Create a new spreadsheet
        $spreadsheet = new Spreadsheet();

        // Set active sheet and title
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Beneficiaries');

        // Add header row
        $sheet->setCellValue('A1', 'fileNo');
        $sheet->setCellValue('B1', 'amount');

        // Optional: add sample data
        $sheet->setCellValue('A2', 1);
        $sheet->setCellValue('B2', 50);
        $sheet->setCellValue('A3', 2);
        $sheet->setCellValue('B3', 70);

        // Make header bold and colored (optional, looks professional)
        $sheet->getStyle('A1:B1')->getFont()->setBold(true);
        $sheet->getStyle('A1:B1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9EDF7'); // light blue background

        // Create writer
        $writer = new Xlsx($spreadsheet);

        // Output to browser for download
        $fileName = 'beneficiary_template.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }


    public function uploadBeneficiaries(Request $request)
    {
        // Validate file exists and type
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv',
            'claimid'    => 'required',
        ]);

        $file = $request->file('excel_file');

        // Load spreadsheet
        $spreadsheet = IOFactory::load($file->getPathName());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // First row must be headers
        $headers = array_map('strtolower', $rows[0]);

        if ($headers !== ['fileno', 'amount']) {
            return back()->with('err', 'Excel headers must be exactly: fileno, amount');
        }

        $claimid = $request->claimid;
        $claiminfo = DB::table('tblcontractDetails')->where('claimid', $claimid)->first();
        if (!$claiminfo) return back()->with('err', 'Claim not found.');

        $totalClaim = $claiminfo->contractValue;

        // Sum of existing beneficiaries
        $existingSum = DB::table('tblselectedstaffclaim')
            ->where('claimID', $claimid)
            ->sum('staffamount');

        $newTotal = $existingSum;
        $errors = [];

        // First pass: validate all rows without inserting
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            $fileNo = trim($row[0]);
            $amount = preg_replace('/[^\d.]/', '', $row[1]);

            // Skip empty rows
            if (!$fileNo && !$amount) continue;

            // Validate numeric
            if (!is_numeric($amount)) {
                $errors[] = "Row " . ($i + 1) . ": Amount must be numeric.";
                continue;
            }

            // Check staff exists
            $staff = DB::table('tblper')->where('fileNo', $fileNo)->first();
            if (!$staff) {
                $errors[] = "Row " . ($i + 1) . ": Staff file number $fileNo does not exist.";
                continue;
            }

            // Check total claim if we add this row
            $newTotal += $amount;
        }

        // Reject entire upload if any errors or total exceeds contract
        if (!empty($errors)) {
            return back()->with('err', "Upload failed due to errors: " . implode(' | ', $errors))->with('active_tab', 'bulk');
        }

        if ($newTotal > $totalClaim) {
            return back()->with('err', "Upload failed. Total amount in Excel exceeds approved claim value.")->with('active_tab', 'bulk');
        }

        // Validation passed — insert all rows
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            $fileNo = trim($row[0]);
            $amount = preg_replace('/[^\d.]/', '', $row[1]);

            // Skip empty rows
            if (!$fileNo && !$amount) continue;

            $staff = DB::table('tblper')->where('fileNo', $fileNo)->first();

            DB::table('tblselectedstaffclaim')->insert([
                'claimID'     => $claimid,
                'staffamount' => $amount,
                'staffID'     => $staff->ID,
            ]);
        }

        return back()->with('message', "All beneficiaries successfully added.")->with('active_tab', 'bulk');
    }
}
