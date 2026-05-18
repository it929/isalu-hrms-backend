<?php

namespace App\Http\Controllers\funds;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class UpdatePaymentController extends BaseParentController
{

	public function __construct()
	{
		// $this->middleware('auth');
	}

	//create
	public function createPaymentKickOff(Request $request)
	{
		$getAllType = $this->getContractAllocationType();
		$data['contractType1']  = $request['contractType1'];
		//dd($request['contractType1']);
		$contT = $data['contractType1'];
		$data['contractType']  = $getAllType['contractType'];
		$data['allocationType'] = $getAllType['allocationType'];
		if (isset($_POST['reload'])) {
			if ($contT != '') {
				Session::put('contT', $contT);
			} else {
				$contT = Session::get('contT');
			}
			return redirect('/update-payment-transaction');
			return redirect()->route('createUpdatePayment')->with('message', '');
			//return redirect()->route('createUpdatePayment');
		}
		$contT = Session::get('contT');
		$data['record'] = DB::Select("SELECT tblpaymentTransaction.*,tblcontractType.contractType,tblallocation_type.allocation,tbleconomicCode.economicCode ,tbleconomicCode.description   ,tblpaymentTransaction.ID as recordID, tblallocation_type.ID as allID, tblcontractType.ID as conID, tbleconomicCode.ID as ecoCodeID
	 FROM `tblpaymentTransaction` join tbleconomicCode on tbleconomicCode.id=tblpaymentTransaction.`economicCodeID` join tbleconomicHead on tbleconomicHead.ID=tbleconomicCode.economicHeadID join tblallocation_type on tblallocation_type.ID=tbleconomicCode.allocationID join tblcontractType on tblcontractType.ID = tblpaymentTransaction.contractTypeID
	 WHERE tblpaymentTransaction.trackID<>'' 
	 and exists( select null FROM `tbleconomicCode` WHERE `tbleconomicCode`.`ID`=tblpaymentTransaction.`economicCodeID` and tbleconomicCode.`contractGroupID`='$contT') order by tbleconomicCode.economicCode");

		(Session::get('edit') ? $data['edit'] = Session::get('edit') : '');

		return view('funds.UpdatePayment.home', $data);
	}


	//save/update
	public function SavePaymentKickOff(Request $request)
	{

		$this->validate($request, [
			'contractType'      	=> 'required|alpha_num',
			'allocationType'    	=> 'required|alpha_num',
			'economicCode'      	=> 'required|string', //|unique:tbleconomicCode,ID,'.$this->trackID.',NULL,> 0,
			'totalPaymnet'    	=> 'required|string',
			'paymentDescription'  => 'required|string',
			'cutOffDate'    	=> 'required|date',
		]);
		//
		$editRecordID = trim($request['recordID']);
		//dd($editRecordID);
		$timestamp = strtotime($request['cutOffDate']);
		$getDate = date('Y-m-d', $timestamp);
		$message = "Sorry, you cannot submit your record now! Please try again.";
		$success = 0;
		$checkEconomicCode = DB::table('tblpaymentTransaction')
			->join('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
			->where('tbleconomicCode.ID', $request['economicCode'])
			->where('tblpaymentTransaction.trackID', '<>', null)
			->first();

		$previous =	DB::table('tblpaymentTransaction')
			->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
			->where('tblpaymentTransaction.ID', '=', $editRecordID)->first();
		if ($editRecordID) {
			$success = 1;
			DB::table('tblpaymentTransaction')->where('ID', $editRecordID)->update([
				'contractTypeID' 	=> $request['contractType'],
				'totalPayment' 	=> $request['totalPaymnet'],
				'paymentDescription' => $request['paymentDescription'],
				'preparedBy' 	=> Auth::User()->id,
				'allocationType' 	=> $request['allocationType'],
				'economicCodeID' 	=> $request['economicCode'],
				'status' 		=> 6,
				'datePrepared' 	=> $getDate,
				'period' 		=> DB::table('tblactiveperiod')->value('year'),
				'cpo_payment' 	=> 1,
				'cpo_payment_date' 	=> $getDate,
				'checkbyStatus' 	=> 1,
				'mandate_status' 	=> 1,
				'vstage' 		=> 4,
				'VAT' 		=> 0,
				'VATValue' 		=> 0,
				'WHT' 		=> 0,
				'WHTValue' 		=> 0,
				'amtPayable' 	=> 0,
				'pay_confirmation' 	=> 1,
				'accept_voucher_status' 	=> 1,
			]);
			Session::forget('edit');


			$data1 = array(
				"totalPayment" => $previous->totalPayment,
				"Payment Description" => $previous->paymentDescription,
				"Vat" => $previous->VAT,
				"VATValue" => $previous->VATValue,
				"WHT" => $previous->WHT,
				"WHT Value" => $previous->WHTValue,
				"Amount Payable" => $previous->amtPayable,
				"Prepared By" => $previous->preparedBy,
				"Allocation Type" => $previous->allocationType,
				"Economic code" => $previous->economicCodeID
			);
			$post_encode = json_encode($data1);

			$data2 = array(
				"Total Payment" => $request['totalPaymnet'],
				"Payment Description" => $request['paymentDescription'],
				"Vat" => 0,
				"VATValue" => 0,
				"WHT" => 0,
				"WHT Value" => 0,
				"Amount Payable" => 0,
				"Prepared By" => Auth::user()->id,
				"Allocation Type" => $request['allocationType'],
				"contract Type ID" => $request['contractType'],
				"Economic code" => $request['economicCode']
			);
			$post_encode2 = json_encode($data2);
			$operation = "Payment Updated from $post_encode to $post_encode2";
			$this->addLogg($operation, "Payment Updated");
		} else {
			if ($checkEconomicCode) {
				return back()->with('error', 'The economic code has already been taken.');
			}
			Session::forget('edit');
			$success = DB::table('tblpaymentTransaction')->insertGetId([
				'contractTypeID' 	=> $request['contractType'],
				'totalPayment' 	=> $request['totalPaymnet'],
				'paymentDescription' => $request['paymentDescription'],
				'preparedBy' 	=> Auth::User()->id,
				'allocationType' 	=> $request['allocationType'],
				'economicCodeID' 	=> $request['economicCode'],
				'status' 		=> 6,
				'datePrepared' 	=> $getDate,
				'period' 		=> DB::table('tblactiveperiod')->value('year'),
				'cpo_payment' 	=> 1,
				'cpo_payment_date' 	=> $getDate,
				'dateTakingLiability' 	=> $getDate,
				'checkbyStatus' 	=> 1,
				'mandate_status' 	=> 1,
				'vstage' 		=> 4,
				'VAT' 		=> 0,
				'VATValue' 		=> 0,
				'WHT' 		=> 0,
				'WHTValue' 		=> 0,
				'amtPayable' 	=> 0,
				'pay_confirmation' 	=> 1,
				'accept_voucher_status' 	=> 1,
				'trackID' 		=> DB::table('tblpaymentTransaction')->orderBy('ID', 'Desc')->value('ID') + 1,
			]);
		}
		if ($success) {
			$payDesc = $request['paymentDescription'];
			$usr = Auth::user()->name;
			$message = "Your payment information was added successfully";
			$this->addLogg("Payment Information with ID:$editRecordID and Descriprtion: $payDesc was added by $usr", "Payment Information added");
			return redirect()->route('createUpdatePayment')->with('message', $message);
		} else {
			return redirect()->route('createUpdatePayment')->with('error', $message);
		}
	}


	// show edit data
	public function edit($ID)
	{
		if (DB::table('tblpaymentTransaction')->where('ID', $ID)->first()) {
			$editData = DB::table('tblpaymentTransaction')->where('tblpaymentTransaction.ID', $ID)
				->join('tblallocation_type', 'tblallocation_type.ID', '=', 'tblpaymentTransaction.allocationType')
				->join('tblcontractType', 'tblcontractType.ID', '=', 'tblpaymentTransaction.contractTypeID')
				->join('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
				->select('*', 'tblpaymentTransaction.ID as recordID', 'tblallocation_type.ID as allID', 'tblcontractType.ID as conID', 'tbleconomicCode.ID as ecoCodeID')
				->first();
			Session::put('edit', $editData);
		} else {
			Session::forget('edit');
		}
		//return redirect('/update-payment-transaction');
		return redirect()->route('createUpdatePayment');
	}

	// cancel edit
	public function cancelEdit()
	{
		Session::forget('edit');

		return redirect()->route('createUpdatePayment');
	}

	//Delecte User
	public function removeRecord($ID)
	{
		$voucher = DB::table('tblpaymentTransaction')->where('ID', $ID)->first();
		$usr = Auth::user()->name;
		$success = 0;
		if (DB::table('tblpaymentTransaction')->where('ID', $ID)->first()) {
			$success = DB::table('tblpaymentTransaction')->where('ID', $ID)->delete();
			$this->addLogg("Voucher with ID: $ID and Payment Description:$voucher->paymentDescription Deleted by $usr" . "Voucher Delete", "Voucher Deleted");
		}
		if ($success) {
			return redirect()->route('createUpdatePayment')->with('message', 'Your record has been deleted succefully');
		}
		return redirect()->route('createUpdatePayment')->with('error', 'Sorry, we cannot delete this record from our system.');
	}
}//end class
