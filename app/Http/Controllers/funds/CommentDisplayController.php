<?php

namespace App\Http\Controllers\funds;



use App\Helpers\FileUploadHelper;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Carbon\Carbon;
use Entrust;
use Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Input;
use Illuminate\Support\Facades\Session;
use QrCode;


class CommentDisplayController extends BasefunctionController
{

    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        // $this->activeMonth = $request->session()->get('activeMonth');
        // $this->activeYear = $request->session()->get('activeYear');
                return $next($request);
        });
    }






    public function viewComment(Request $request, $contractID = null)
    {
        Session::forget('contractID');

        if (!DB::table('tblcontractDetails')->where('ID', '=', $contractID)->first()) {
            return redirect('voucherDisplay/displayVoucher')->with('err', 'Record  not found or yet to be approved  !!!');
        }

        $data['contractinfo'] = DB::table('tblcontractDetails')
            ->select('tblcontractDetails.*', 'tblcontractor.contractor')
            ->where('tblcontractDetails.ID', '=', $contractID)
            ->Join('tblcontractor', 'tblcontractor.id', '=', 'tblcontractDetails.companyID')
            ->first();
        $claimid = $data['contractinfo']->claimid;

        Log::info($contractID);
        $data['claimcomments'] = DB::table('claim_comment')
            ->select('claim_comment.*', 'users.name')
            ->Join('users', 'users.id', '=', 'claim_comment.userID')->where('claimID', '=', $claimid)->get();

        // $data['claimclaim_beneficiaries'] = DB::Select("SELECT *,tblStaffInformation.full_name,tblStaffInformation.fileNo
		// FROM `tblselectedstaffclaim`
		// left JOIN  tblStaffInformation on tblStaffInformation.staffID=tblselectedstaffclaim.`staffID`
		// WHERE `claimID`='$claimid' order by `tblselectedstaffclaim`.selectedID");

        $data['claimclaim_beneficiaries'] = DB::Select("SELECT *,tblper.first_name,tblper.surname,tblper.othernames,tblper.fileNo
		FROM `tblselectedstaffclaim`
		left JOIN  tblper on tblper.ID=tblselectedstaffclaim.`staffID`
		WHERE `claimID`='$claimid' order by `tblselectedstaffclaim`.selectedID");


        $data['contractcomments'] = DB::table('tblcomments')
            ->select('tblcomments.*', 'users.name')
            ->leftJoin('users', 'users.username', '=', 'tblcomments.username')->where('affectedID', '=', $contractID)->orderby('tblcomments.id')->get();

        $data['precontractcomments'] = DB::table('contract_comment')
            ->select('contract_comment.*', 'users.name')
            ->Join('users', 'users.id', '=', 'contract_comment.userID')->where('fileNoID', '=', $data['contractinfo']->fileNo)
            ->where('fileNoID', '<>', '')->get();
        $data['fileattach'] = $this->ContractAttachment($contractID);
        $data['ClaimAttachment'] = $this->ClaimAttachment($claimid);

        // Log::info($contractID);
        Log::info($data['claimclaim_beneficiaries']);




        if (isset($_POST['btn-attachment'])) {
            $this->validate($request, [
                'attachment_description' => 'required|string',
                'filename' => 'required|mimes:jpg,png,jpeg,bmp,pdf|max:5120',
            ]);

            $file = $request->file('filename');
            $customName = $request->input('id') . "_" . $this->RefNo() . '.' . $file->getClientOriginalExtension();

            // Use helper (automatically stores to local or S3)
            $fileUrl = FileUploadHelper::upload($file, 'attachments', $customName);
            Log::info($fileUrl);

            DB::table('tblcontractfile')->insert([
                'file_desc' => $request->input('attachment_description'),
                'filename' => $fileUrl, // full URL
                'contractid' => $contractID,
                'createdby' => Auth::user()->id,
            ]);

            return back()->with('message', 'Attachment successfully added.');
        }


        return view('funds.Report.comments', $data);
    }

    public function viewClaimComment($claimid = null)
    {
        $data['claiminfo'] = DB::table('tblclaim')
            ->select('tblclaim.*', 'users.name')
            ->where('tblclaim.ID', '=', $claimid)
            ->leftjoin('users', 'tblclaim.user', '=', 'users.id')
            ->first();

        $data['claimcomments'] = DB::table('claim_comment')
            ->select('claim_comment.*', 'users.name')
            ->Join('users', 'users.id', '=', 'claim_comment.userID')->where('claimID', '=', $claimid)->get();

        $data['claimclaim_beneficiaries'] = DB::Select("SELECT *,tblStaffInformation.full_name,tblStaffInformation.fileNo
		FROM `tblselectedstaffclaim`
		left JOIN  tblStaffInformation on tblStaffInformation.staffID=tblselectedstaffclaim.`staffID`
		WHERE `claimID`='$claimid'");


        //
        $data['fileattach'] = DB::table('claim_comment')->where('tblclaim.ID', '=', $claimid);
        return view('Report.claim_comment', $data);
    }

    public function ClaimAdjustment(Request $request, $claimid = null)
    {
        $data['claiminfo'] = DB::table('tblclaim')
            ->select('tblclaim.*', 'users.name')
            ->where('tblclaim.ID', '=', $claimid)
            ->leftjoin('users', 'tblclaim.user', '=', 'users.id')
            ->first();

        if ($data['claiminfo']->status == 6) return back()->with('error', 'Oops!!! this record have been approved. You can not make adjustment again!!');
        $data['claimcomments'] = DB::table('claim_comment')
            ->select('claim_comment.*', 'users.name')
            ->Join('users', 'users.id', '=', 'claim_comment.userID')->where('claimID', '=', $claimid)->get();

        $data['claimclaim_beneficiaries'] = DB::Select("SELECT *,tblStaffInformation.full_name,tblStaffInformation.fileNo
		FROM `tblselectedstaffclaim`
		left JOIN  tblStaffInformation on tblStaffInformation.staffID=tblselectedstaffclaim.`staffID`
		WHERE `claimID`='$claimid'");
        if (isset($_POST['update'])) {
            foreach ($data['claimclaim_beneficiaries'] as $value) {
                if ($request['amount' . $value->selectedID] == '') $request['amount' . $value->selectedID] = 0;
                if (is_numeric($request['amount' . $value->selectedID])) {
                    DB::table('tblselectedstaffclaim')->where('selectedID', $value->selectedID)->update(['staffamount' => $request['amount' . $value->selectedID]]);
                }
            }
            $totalclaim =    DB::select("SELECT sum(`staffamount`)as sumt FROM `tblselectedstaffclaim` WHERE `claimID`='$claimid'")[0]->sumt;
            DB::table('tblclaim')->where('ID', $claimid)->update(['amount' => $totalclaim]);
            return back()->with('message', 'updated successfully');
        }
        //
        $data['fileattach'] = DB::table('claim_comment')->where('tblclaim.ID', '=', $claimid);
        return view('Report.claim_adjustment', $data);
    }
}//End class
