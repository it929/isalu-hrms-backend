<?php

namespace App\Http\Controllers\funds;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AdvanceRetirementController extends BasefunctionController
{


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
		//dd($data['contractinfo']);
		$claimid = $data['contractinfo']->claimid;


		$data['claimcomments'] = DB::table('claim_comment')
			->select('claim_comment.*', 'users.name')
			->Join('users', 'users.id', '=', 'claim_comment.userID')->where('claimID', '=', $claimid)->get();

		$data['claimclaim_beneficiaries'] = DB::Select("SELECT *,tblStaffInformation.full_name,tblStaffInformation.fileNo 
		FROM `tblselectedstaffclaim` 
		left JOIN  tblStaffInformation on tblStaffInformation.staffID=tblselectedstaffclaim.`staffID` 
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

		if (isset($_POST['retire'])) {
			$this->validate($request, [
				'remark' => 'required|string',
				'clearid' => 'required|string',
				'amount' => 'required|numeric',
			]);
			// dd($request->input('clearid'));
			$preid = DB::table('tblpaymentTransaction')->where('ID', $request->input('clearid'))->value('contractID');
			if (floor(DB::table('tblpaymentTransaction')->where('ID', $request->input('clearid'))->value('totalPayment')) != floor($data['contractinfo']->contractValue))	return back()->with('err', 'Amount retired cannot be greater than total approved.');
			if (floor($request->input('amount')) > floor($data['contractinfo']->contractValue))	return back()->with('err', 'Amount retired cannot be greater than total approved.');
			if (floor($request->input('amount')) > floor(DB::table('tblpaymentTransaction')->where('ID', $request->input('clearid'))->value('totalPayment')))	return back()->with('err', 'Amount retired cannot be greater than voucher amount.');
			//die("kskkdjkd");
			DB::table('tblpaymentTransaction')->where('ID', $request->input('clearid'))->update(
				[
					'retired_ref' 	=> $contractID,
					'is_retired' 	=> 1,
					'retirement_remark' 	=> $request->input('remark'),
					'amount_retired' => $request->input('amount'),
					'retiredby' 	=> Auth::user()->id,
					'date_retired' 	=> date('Y-m-d')
				]
			);
			DB::table('tblcontractDetails')->where('ID', $contractID)->update(['openclose' => 0, 'is_retired' => 1]);
			DB::table('tblcontractDetails')->where('ID', $preid)->update(['is_retired' => 1]);
			return redirect('/create/advances')->with('message', 'Advance successfully retired.');
		}

		$data['tablecontent'] = DB::table('tblpaymentTransaction')
			->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
			->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
			->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
			->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
			->leftjoin('tblallocation_type', 'tblpaymentTransaction.allocationType', '=', 'tblallocation_type.ID')
			->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
			->where('tblpaymentTransaction.is_archive', 0)
			->where('tblpaymentTransaction.is_restore', 0)
			->where('tblpaymentTransaction.status', '>', 1)
			->where('tblpaymentTransaction.is_retired', 0)->where('tblpaymentTransaction.is_advances', 1)
			->select('tblpaymentTransaction.*', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tblallocation_type.allocation', 'tblcontractDetails.contractValue', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions', 'tblcontractDetails.beneficiary', 'tblcontractDetails.voucherType', DB::raw('tblcontractDetails.ID AS conID'))
			->orderBy('checkbyStatus', 'asc')->orderBy('dateAward', 'asc')->get();

		foreach ($data['tablecontent'] as $key => $value) {
			$lis = (array) $value;
			$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
			$value = (object) $lis;
			$data['tablecontent'][$key]  = $value;
		}
		return view('funds.Advance.retirement', $data);
	}
	public function Unsolicited(Request $request)
	{

		if (isset($_POST['retire'])) {
			// dd($request->input('clearid'));
			$this->validate($request, [
				'remark' => 'required|string',
				'clearid' => 'required|string',
				'amount' => 'required|numeric',
			]);
			$preid = DB::table('tblpaymentTransaction')->where('ID', $request->input('clearid'))->value('contractID');
			//if(floor(DB::table('tblpaymentTransaction')->where('ID', $request->input('clearid'))->value('totalPayment'))!=floor($data['contractinfo']->contractValue))	return back()->with('err','Amount retired cannot be greater than total approved.'  );
			// if(floor($request->input('amount'))>floor($data['contractinfo']->contractValue))	return back()->with('err','Amount retired cannot be greater than total approved.'  );
			if (floor($request->input('amount')) > floor(DB::table('tblpaymentTransaction')->where('ID', $request->input('clearid'))->value('totalPayment')))	return back()->with('err', 'Amount retired cannot be greater than voucher amount.');
			//die("kskkdjkd");
			DB::table('tblpaymentTransaction')->where('ID', $request->input('clearid'))->update(
				[
					//'retired_ref' 	=> $contractID,
					'is_retired' 	=> 1,
					'retirement_remark' 	=> $request->input('remark'),
					'amount_retired' => $request->input('amount'),
					'retiredby' 	=> Auth::user()->id,
					'date_retired' 	=> date('Y-m-d')
				]
			);
			//DB::table('tblcontractDetails')->where('ID', $contractID )->update(['openclose' => 0,'is_retired' => 1]);
			DB::table('tblcontractDetails')->where('ID', $preid)->update(['is_retired' => 1]);

			//update liability_taken table is_cleared to 1
			//remember to add voucherID to liability_taken table when taking liability for advances


			return back()->with('message', 'Advance successfully retired.');
		}

		$data['tablecontent'] = DB::table('tblpaymentTransaction')
			->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
			->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
			->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
			->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
			->leftjoin('tblallocation_type', 'tblpaymentTransaction.allocationType', '=', 'tblallocation_type.ID')
			//->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
			->where('tblpaymentTransaction.is_archive', 0)
			->where('tblpaymentTransaction.is_restore', 0)
			->where('tblpaymentTransaction.status', '>', 1)
			->where('tblpaymentTransaction.is_retired', 0)->where('tblpaymentTransaction.is_advances', 1)
			->where('tblpaymentTransaction.dateTakingLiability', '!=', '0000-00-00')
			->select('tblpaymentTransaction.*', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tblallocation_type.allocation', 'tblcontractDetails.contractValue', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions', 'tblcontractDetails.beneficiary', 'tblcontractDetails.voucherType', DB::raw('tblcontractDetails.ID AS conID'))
			->orderBy('checkbyStatus', 'asc')->orderBy('dateAward', 'asc')->get();

		foreach ($data['tablecontent'] as $key => $value) {
			$value->balance = $this->VoucherFinancialInfo($value->ID);
		}
		return view('funds.Advance.unsolicited', $data);
	}
}//End class
