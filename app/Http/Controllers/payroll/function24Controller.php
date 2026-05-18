<?php


namespace App\Http\Controllers\payroll;
use Illuminate\Support\Facades\Log;

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





class function24Controller extends BasefunctionController {

	public $valerrors;
	public function getList($table){
		$list = DB::table($table)->orderby('status', 'desc')->get();
		return $list;
	}
	
	public function getEconomicCode($allocation, $accthead){
	//dd($allocation." ".$contract);
	
		$list = DB::table('tbleconomicCode')->where('allocationID', $allocation)->where('contractGroupID', $accthead)->get();
		if($list){
		return $list;
		} else {
		 return [];
		 }
	}
	public function getEconomicCode2($allocation, $contract){
	
		$list = DB::table('tbleconomicCode')->where('allocationID', session('alloc'))->where('contractGroupID', $contract)->get();
		if($list){
		return $list;
		} else {
		 return [];
		 }
	}
	
	public function getAllocation(){
		$list = DB::table('tblallocation_type')->where('status', 1)->get();
		if($list){
		return $list;
		} else {
		 return [];
		 }
	}
	
	public function getContract(){
		$list = DB::table('tblcontractType')->where('status', 1)->get();
		if($list){
		return $list;
		} else {
		 return [];
		 }
	}
	public function getContractType(){
		$list = DB::table('tblcontractType')->where('status', 1)->get();
		if($list){
		return $list;
		} else {
		 return [];
		 }
	}
	
	public function getBeneficiary(){
		$list = DB::table('tblcontractor')->where('status', 1)->where('id','<>', 13)->get();
		return $list;
	}

	public function getProcurement(){
		$list = DB::table('tblcontractDetails')->leftjoin('tblcontractType', 'tblcontractDetails.contract_Type', '=', 'tblcontractType.ID')
		->leftjoin('tblpaymentTransaction', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
		->where('tblcontractDetails.companyID', '!=', '13')
		->where('tblpaymentTransaction.department_voucher', ucfirst($this->getUserRole()->rolename))
		->leftjoin('tblcontractor', 'tblcontractDetails.companyID', '=', 'tblcontractor.id')
		->select('tblcontractDetails.*', 'tblcontractType.contractType', 'tblcontractor.contractor')
		->orderBy('approvalStatus', 'asc')
		->orderBy('dateAward', 'asc')
		->groupBy('tblpaymentTransaction.contractID')
		->get();
		return $list;
	}
		public function getProcurementContractEntry(){
		$list = DB::table('tblcontractDetails')->leftjoin('tblcontractType', 'tblcontractDetails.contract_Type', '=', 'tblcontractType.ID')
		->where('tblcontractDetails.companyID', '!=', '13')
		->leftjoin('tblcontractor', 'tblcontractDetails.companyID', '=', 'tblcontractor.id')
		->leftjoin('users', 'tblcontractDetails.createdby', '=', 'users.id')
		->select('tblcontractDetails.*', 'tblcontractType.contractType', 'tblcontractor.contractor', 'users.name')
		->orderBy('approvalStatus', 'asc')
		->orderBy('dateAward', 'asc')
		
		->get();
		return $list;
	}

	public function getProcurementContractEntryOptimized()
{
    // Fetch contract list
    $contracts = DB::table('tblcontractDetails')
        ->leftJoin('tblcontractType', 'tblcontractDetails.contract_Type', '=', 'tblcontractType.ID')
        ->where('tblcontractDetails.companyID', '!=', '13')
        ->leftJoin('tblcontractor', 'tblcontractDetails.companyID', '=', 'tblcontractor.id')
        ->leftJoin('users', 'tblcontractDetails.createdby', '=', 'users.id')
        ->select(
            'tblcontractDetails.*',
            'tblcontractType.contractType',
            'tblcontractor.contractor',
            'users.name'
        )
        ->orderBy('approvalStatus', 'asc')
        ->orderBy('dateAward', 'asc')
        // ->get();
		->paginate(20);
		dd($contracts);

    // Extract IDs for entries with approvalStatus == 2
    $idsWithStatus2 = $contracts->where('approvalStatus', 2)->pluck('ID')->all();
	dd($idsWithStatus2);

    // Fetch latest comments for those contracts
    $latestComments = DB::table('tblcomments')
        ->select('affectedID', 'comment')
        ->whereIn('affectedID', $idsWithStatus2)
        ->where('commenttypeID', 1)
        ->orderBy('id', 'desc')
        ->get()
        ->unique('affectedID') // get the latest comment per affectedID
        ->keyBy('affectedID');

    // Prepare final result
    foreach ($contracts as $key => $contract) {
        $line = (array) $contract;
        $line['reason'] = $latestComments[$contract->ID]->comment ?? '';
        $line['balance'] = $this->ContractBalance($contract->ID); // Could be optimized further
        $contracts[$key] = (object) $line;
    }

    return $contracts;
}


	public function getStaffProcurement(){
		$list = DB::table('tblcontractDetails')
		->where('tblcontractDetails.companyID', '13')
		->leftjoin('tblpaymentTransaction', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
		->where('tblpaymentTransaction.department_voucher', ucfirst($this->getUserRole()->rolename))
		->leftjoin('tblcontractType', 'tblcontractDetails.contract_Type', '=', 'tblcontractType.ID')
		->leftjoin('tblcontractor', 'tblcontractDetails.companyID', '=', 'tblcontractor.id')
		->select('tblcontractDetails.*', 'tblcontractType.contractType', 'tblcontractor.contractor')
		->orderBy('approvalStatus', 'asc')
		->orderBy('dateAward', 'asc')
		->groupBy('tblpaymentTransaction.contractID')
		->get();
		return $list;
	}

public function getTable($contracttype, $status){
		if($status == "All"){
	    		$list = DB::table('tblcontractDetails')
		    	->where('contract_Type', $contracttype)
		    	->leftjoin('tblcontractType', 'tblcontractDetails.contract_Type', '=', 'tblcontractType.ID')
				->leftjoin('tblcontractor', 'tblcontractDetails.companyID', '=', 'tblcontractor.id')
				->select('tblcontractDetails.*', 'tblcontractType.contractType', 'tblcontractor.contractor')
				->orderBy('approvalStatus', 'asc')
				->orderBy('paymentStatus', 'asc')
		    	->get();
	    	} else {
	    		$list = DB::table('tblcontractDetails')
		    	->where('contract_Type', $contracttype)->where('approvalStatus', $status)
		    	->leftjoin('tblcontractType', 'tblcontractDetails.contract_Type', '=', 'tblcontractType.ID')
				->leftjoin('tblcontractor', 'tblcontractDetails.companyID', '=', 'tblcontractor.id')
				->select('tblcontractDetails.*', 'tblcontractType.contractType', 'tblcontractor.contractor')
				->orderBy('approvalStatus', 'asc')
				->orderBy('paymentStatus', 'asc')
		    	->get();
	    	}
	    return $list;
	}
	public function getTable2($contracttype, $status,$suerid){
	$qstatus="`approvalStatus`='$status'";
	if($status=="All" || $status==""){$qstatus=1;}
	//$qstatus=1;
	$qcontracttype=1;
	if($contracttype!=""){$qcontracttype="`contract_Type`='$contracttype'";}
	
	 
	$data=  DB::select("SELECT *
	,(SELECT `contractor` FROM `tblcontractor` WHERE `tblcontractor`.`id`=`tblcontractDetails`.`companyID`) as contractor
	 FROM `tblcontractDetails` WHERE $qstatus and $qcontracttype 
	 and exists (SELECT null FROM `tblaction_rank` WHERE `tblaction_rank`.`code`=`tblcontractDetails`.`awaitingActionby` and `tblaction_rank`.`userid`='$suerid') order by `approvalStatus` asc, `paymentStatus` asc");
	 return $data;
		
	}
	public function getContractQueryReport($contracttype, $status,$getTime1,$getTime2){
	$qstatus="`paymentStatus`='$status'";
	if($status=="All" || $status==""){$qstatus=1;}
	$qcontracttype=1;
	if($contracttype!=""){$qcontracttype="`contract_Type`='$contracttype'";}
	
	 
	$data=  DB::select("SELECT *
	,(SELECT `contractor` FROM `tblcontractor` WHERE `tblcontractor`.`id`=`tblcontractDetails`.`companyID`) as contractor
	 FROM `tblcontractDetails` WHERE $qstatus and $qcontracttype and (dateAward BETWEEN '$getTime1' AND '$getTime2') 
	  order by `approvalStatus` asc, `paymentStatus` asc");
	 return $data;
		
	}
	public function getTable3($type, $bene,$contId){
	//die();
	$qcontracttype=1;
	if($type!=""){$qcontracttype="`contract_Type`='$type'";}
	$qbene=1;
	if($bene!=""){$qbene="`companyID`='$bene'";}
	$qcontId=1;
	if($contId!=""){$qcontId="`fileNo`='$contId'";}
	
	$data=  DB::select("SELECT *
	,(SELECT `contractor` FROM `tblcontractor` WHERE `tblcontractor`.`id`=`tblcontractDetails`.`companyID`) as contractor
	 FROM `tblcontractDetails` WHERE $qbene and $qcontracttype and $qcontId
	 and approvalStatus = 1 AND openclose = 1 ");
	 //dd($data);
	 return $data;
		
	}
	
	public function AllocationVoucher($userId){
	$data=  DB::select("SELECT *
	,(SELECT `contractor` FROM `tblcontractor` WHERE `tblcontractor`.`id`=`tblcontractDetails`.`companyID`) as contractor
	 FROM `tblcontractDetails` WHERE `OC_staffId`='$userId'
	 and approvalStatus = 1 AND openclose = 1 ");
	 //dd($data);
	 return $data;
		
	}
	
	public function getFileNos(){
		$list = DB::table('tblcontractDetails')->select('fileNo', 'ID')->get();
		return $list;
	}

	public function getInfo($id){
		$res = DB::table('tblcontractDetails')
		->where('tblcontractDetails.ID', $id)
		->leftjoin('tblcontractType', 'tblcontractDetails.contract_Type', '=', 'tblcontractType.ID')
		->leftjoin('tblcontractor', 'tblcontractDetails.companyID', '=', 'tblcontractor.id')
		->first();
		return $res;
	}
	
	public function validater($source, $values = [], $names = [], $view = 'CreateContract.createcontract')
	{
		if(!empty($source) && !empty($values)){
			foreach($values as $key => $value){
				if(strpos($value, '|') !== false){
					$exp = explode('|', $value);
					$emp_index = array_search('', $exp);//remove empty array index
					array_slice($exp, $emp_index);
					$checks = $exp;
					foreach ($checks as $check) {
						if($check == 'required'){
							if($source[$key] == "" || empty($source[$key])){
								if(isset($names[$key])){
									$this->valerrors[] = "{$names[$key]} is required";
								} else {
									$this->valerrors[] = "{$source[$key]} is required";
								}
							}
						}

						if(strpos($check, 'required_unless') !== false){
							$breakcoma = explode(':', $check)[1];
							
							$fieldunless = explode(',', $breakcoma)[0];
							$valueunless = explode(',', $breakcoma)[1];

							if($source[$fieldunless] === $valueunless){
								if($source[$key] == "" || empty($source[$key])){
									if(isset($names[$key])){
										$this->valerrors[] = "{$names[$key]} is required";
									} else {
										$this->valerrors[] = "{$source[$key]} is required";
									}
								}
							}
						}
					}
				} else {
					if($value == 'required'){
							if($source[$key] == "" || empty($source[$key])){
								if(isset($names[$key])){
									$this->valerrors[] = "{$names[$key]} is required";
								} else {
									$this->valerrors[] = "{$source[$key]} is required";
								}
							}
						}

						if(strpos($value, 'required_unless') !== false){
							$breakcoma = explode(':', $value)[1];
							
							$fieldunless = explode(',', $breakcoma)[0];
							$valueunless = explode(',', $breakcoma)[1];

							if($source[$fieldunless] === $valueunless){
								if($source[$key] == "" || empty($source[$key])){
									if(isset($names[$key])){
										$this->valerrors[] = "{$names[$key]} is required";
										  
    										//this->valerrors[] = "{$source[$key]} is required";);
									} else {
										$this->valerrors[] = "{$source[$key]} is required";
									}
								}
							}
						}
				}
			}
		}

			if(!empty($this->valerrors)){
				return false;
			}
		
	}
	
	public function ContractDetails($contId){
	$data=  DB::select("SELECT *
	,(SELECT `contractor` FROM `tblcontractor` WHERE `tblcontractor`.`id`=`tblcontractDetails`.`companyID`) as contractor
	,(SELECT `contractType` FROM `tblcontractType` WHERE `tblcontractType`.`ID`=`tblcontractDetails`.`contract_Type`) as EcoGroup
	 FROM `tblcontractDetails` WHERE `ID`='$contId'");
	 //dd($data);
	    if($data)return $data[0];
	    return DB::select("SELECT 0 as ID ")[0];
	}
	
	public function UnitStaff($unit){
	
	$data=  DB::select("SELECT *,  (SELECT `name` FROM `users` WHERE `users`.`id`=`tblstaff_section`.`user_id`) as Names FROM `tblstaff_section` WHERE `section`='$unit'");
	
	 return $data;
		
	}
	public function IsBudgetable($ecoid){
		if(DB::table('tbleconomicCode')->where('ID', $ecoid)->where('Isbudgetable', 0)->first())return false;
		return true;
	}
	
}