<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;
//use Illuminate\Http\Support\Facade\View;
use App\Http\Requests;
use DB;

class ContractorController extends ParentController
{
  
  
  public function index(Request $request){


   	$data['error'] = "";
	$data['warning'] = "";
	$data['success'] = "";
   	$contractor = trim($request['contractor']);
   	$phone = trim($request['phone']);
   	$address = trim($request['address']);
   	$email = trim($request['email']);
   	$bank = trim($request['bank']);
   	$account = trim($request['account']);
   	$sortcode = trim($request['sortcode']);
   	$tin = trim($request['tin']);
   	$id = trim($request['C_id']);
   	$status = trim($request['status']);



   	$data['contractorList'] = $this->getContractors();
   	$data['banklist'] = $this->GetBankList();//Pass the bank list to the data array

	   	if(isset($_POST['add'])){

		    $this->validate($request, [
			'contractor'      	=> 'required',
			//'phone'      	   	=> 'required',
			]);



			//DB::insert("INSERT INTO `tblcontractor`(`contractor`, `address`, `phoneNo`, `emailAddress`, `Banker`, `AccountNo`, `sortCode`, `TIN`,`status`) 
			//VALUES ('$contractor','$address','$phone','$email','$bank','$account','$sortcode','$tin','1')");
            DB::table('tblcontractor')->insertGetId(array( 
			'contractor' 		 => $contractor,
			'address'  		 => $address,
			'phoneNo' 	  	 => $phone,
			'emailAddress' 	     => $email,
			'Banker' 		 	 => $bank,
			'AccountNo'  		 => $account,
			'sortCode' 	  	 	 => $sortcode,
			'TIN' 	     	 => $tin,
			'status' 	  	 => 1,
			
		));
			$data['success'] = "$contractor successfully added";
			$data['contractorList'] = $this->getContractors();
			return view('contractor.contractor', $data);
	   	} else {

		   		if(isset($_POST['edit'])){

			    $this->validate($request, [
				'contractor'      	=> 'required',
				//'phone'      	   	=> 'required',
				]);



				DB::table('tblcontractor')->where('id',$id)->update([
					'contractor' => $contractor, 
					'address' => $address, 
					'phoneNo' => $phone,
					'emailAddress' => $email, 
					'Banker' => $bank, 
					'AccountNo' => $account,
					'sortCode' => $sortcode, 
					'TIN' => $tin,
					 'status' => $status,
				]);

				$data['success'] = "$contractor successfully Edited";
				$data['contractorList'] = $this->getContractors();
				return view('contractor.contractor', $data);

		   	} elseif (isset($_POST['delete'])) {
		   		# code...
		   		$id = trim($request['C_id']);

		   		$confirm = $this->checkContractor($id);

		   		if ($confirm == TRUE) {
		   			
		   			$data['warning'] = "Contractor cannot be deleted";
					$data['contractorList'] = $this->getContractors();
					return view('contractor.contractor', $data);

		   		} else {

			   		DB::table('tblcontractor')->where('id', $id)->delete();
			   		$data['success'] = "$contractor successfully Deleted";
					$data['contractorList'] = $this->getContractors();
					return view('contractor.contractor', $data);

				}
		   	}

	   	}



   	return view('contractor.contractor', $data);

   }




   /********** THIS FUNCTION GETS ALL BANKS TO BE DISPLAYED ON THE LAYOUT ***************/

   public function GetBankList(){

   	$bank = DB::table('tblbanklist')->select('bankID', 'bank')->get(); //Select all banks form database
   	return $bank;

   }
   
   
   public function checkContractor($companyID){

   	$bank = DB::table('tblcontractDetails')
   	->select('*')
   	->where('companyID', $companyID)
   	->get(); //Select all banks form database
   	return $bank;

   }


	 public function getContractors(){

   	$list = DB::table('tblcontractor')
            ->leftjoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblcontractor.Banker')
            ->where('tblcontractor.id', '<>', 13)
            ->select('*')
            ->orderBy('contractor', 'Asc')
            ->get();

   	return $list;
   }

	
   
}