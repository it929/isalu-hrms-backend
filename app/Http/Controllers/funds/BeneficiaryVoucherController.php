<?php

namespace App\Http\Controllers\funds;

use Illuminate\Http\Request;
//use Illuminate\Http\Support\Facade\View;
use App\Http\Requests;
use DB;
use Auth;

class BeneficiaryVoucherController extends ParentController
{
	public function index(Request $request){

		$data['error'] = "";
	   	$data['warning'] = "";
	   	$data['success'] = "";
	   
	   	$data['voucher'] = trim($request['voucher']);
	   	$voucher = $data['voucher'];
	   	$data['beneficiary'] = trim($request['beneficiary']);
	   	$beneficiary = $data['beneficiary'];
	   	$data['amount'] = trim($request['amount']);
	   	$amount = $data['amount'];
	   	$data['total'] = trim($request['total']);
	   	$total = $data['total'];
	   	$data['bank'] = trim($request['bank']);
	   	$bank = $data['bank'];
	   	$data['account'] = trim($request['account']);
	   	$account = $data['account'];
	   	$data['id'] = trim($request['id']);
	   	$id = $data['id'];


	   	$data['banks'] = $this->GetBanks();
	   	$data['vouchers'] = $this->GetVouchers();
	   	$data['beneficaries'] = $this->GetBeneficiaries($voucher);
	   	$data['vouch'] = $this->GetOneVoucher($voucher);
	  


	   	if(isset($_POST['add'])){

	   		

	   		$value = $amount + $total;

	   		$fetch = DB::table('tblpaymentTransaction')->where('ID','=',$voucher)->first();

	   		$totalPayment = $fetch->totalPayment;

       				if($value <= $totalPayment){

                     	DB::table('tblvoucherBeneficiary')->insert([ 
	                      'beneficiaryDetails' => $beneficiary,
	                      'voucherID'    => $voucher,
	                      'accountNo'    => $account,
	                      'amount'   => $amount,
	                      'bankID'       => $bank,
                   		]);

                     	$data['beneficiary'] = '';
                   		$beneficiary = $data['beneficiary'];
					   	$data['amount'] = '';
					   	$amount = $data['amount'];
					   	$data['bank'] = "";
					   	$bank = $data['bank'];
					   	$data['account'] = "";
					   	$account = $data['account'];

                        $data['success'] = " successfully added";
                        $data['beneficaries'] = $this->GetBeneficiaries($voucher);
                        return view('beneficiary.voucher', $data);
                    } else{

                    	$data['warning'] = "Amount entered is greater than the voucher value";
                        $data['beneficaries'] = $this->GetBeneficiaries($voucher);
                        return view('beneficiary.voucher', $data);
                    }
                  
         		} else {

               if(isset($_POST['edit'])){

                  	$value = $amount + $total;

	   				$fetch = DB::table('tblpaymentTransaction')->where('ID','=',$voucher)->first();

	   				$totalPayment = $fetch->totalPayment;

	   				if($value <= $totalPayment){

                  		DB::table('tblvoucherBeneficiary')->where('ID',$id)->update([
	                     	'beneficiaryDetails' => $beneficiary,
	                      'voucherID'    => $voucher,
	                      'accountNo'    => $account,
	                      'amount'   => $amount,
	                      'bankID'       => $bank,
                        ]);

                        $data['beneficiary'] = '';
                   		$beneficiary = $data['beneficiary'];
					   	$data['amount'] = '';
					   	$amount = $data['amount'];
					   	$data['bank'] = "";
					   	$bank = $data['bank'];
					   	$data['account'] = "";
					   	$account = $data['account'];

	                  $data['success'] = "successfully Edited";
	                  $data['beneficaries'] = $this->GetBeneficiaries($voucher);
	                  return view('beneficiary.voucher', $data);
              		}else{

                    	$data['warning'] = "Amount entered is greater than the voucher value";
                        $data['beneficaries'] = $this->GetBeneficiaries($voucher);
                        return view('beneficiary.voucher', $data);
                    }
              

            } elseif (isset($_POST['delete'])) {
               # code...
               

               

               // if ($confirm == TRUE) {
                  
               //    $data['warning'] = "Budget has been approved and therefore not be deleted";
               // $data['budget'] = $this->getBudget();
               // return view('beneficiary.voucher', $data);

               // } else {

                  DB::table('tblvoucherBeneficiary')->where('ID', $id)->delete();
                  $data['success'] = " successfully Deleted";
               $data['beneficaries'] = $this->GetBeneficiaries($voucher);
               return view('beneficiary.voucher', $data);

            //}
            }

         }



		return view('beneficiary.voucher', $data);
	}


	public function GetBanks(){

		$bank = DB::table('tblbanklist')->select('*')->get(); //Select all banks form database
   		return $bank;
	}

	public function GetBeneficiaries($id){

		$bank = DB::table('tblvoucherBeneficiary')
		->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblvoucherBeneficiary.bankID')
		->where('voucherID', $id)
        ->select('*')
        ->orderby('tblvoucherBeneficiary.ID', 'DESC')
        ->paginate(50);
		 //Select all banks form database
   		return $bank;

	}

	public function GetVouchers(){

	$data=  DB::select("SELECT *,
	(SELECT `beneficiary` FROM `tblcontractDetails` WHERE tblcontractDetails.ID=tblpaymentTransaction.contractID) as bene
	,(SELECT `ContractDescriptions` FROM `tblcontractDetails` WHERE tblcontractDetails.ID=tblpaymentTransaction.contractID) as disc
	 FROM `tblpaymentTransaction` WHERE (`vstage`='0' or `vstage`='1' or `vstage`='-1') and exists (select null from tblcontractDetails where tblcontractDetails.ID=tblpaymentTransaction.`contractID` and tblcontractDetails.voucherType=2)");
	return $data;

	}

	public function GetOneVoucher($id){
	$data=  DB::select("SELECT *,
	(SELECT `beneficiary` FROM `tblcontractDetails` WHERE tblcontractDetails.ID=tblpaymentTransaction.contractID) as bene
	,(SELECT `ContractDescriptions` FROM `tblcontractDetails` WHERE tblcontractDetails.ID=tblpaymentTransaction.contractID) as disc
	 FROM `tblpaymentTransaction` WHERE  tblpaymentTransaction.`ID`='$id' ");
	return $data;

		$bank = DB::table('tblpaymentTransaction')
		->where('ID', $id)
		->select('*')
		->get(); //Select all banks form database
   		return $bank;

	}
}