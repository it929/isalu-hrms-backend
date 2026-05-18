<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;
//use Illuminate\Http\Support\Facade\View;
use App\Http\Requests;
use DB;
use Auth;

class TransactionsController extends ParentController
{

	public function index(Request $request){

		$data['error'] = "";
	   	$data['warning'] = "";
	   	$data['success'] = "";
	   	$dateToday  = date('Y-m-d');
	   	$data['contractor'] = trim($request['contractor']);
	   	$contractor = $data['contractor'];
	   	$data['allocationType'] = trim($request['allocationType']);
	   	$allocationType = $data['allocationType'];
	   	$data['contract'] = trim($request['contract']);
	   	$contract = $data['contract'];
	   	$data['economicCode'] = trim($request['economicCode']);
	   	$economicCode = $data['economicCode'];
	   	$data['status'] = trim($request['status']);
	   	$status = $data['status'];
	   	$data['datefrom'] = trim($request['dateFrom']);
	   	$datefrom = $data['datefrom'];
	   	$data['dateto'] = trim($request['dateTo']);
	   	$dateto = $data['dateto'];
	   	if($data['datefrom']==''){ $data['datefrom'] = date('Y-m-d', strtotime($dateToday. ' - 1 month')); }
	   	if($data['dateto']==''){ $data['dateto'] = $dateToday; }
	   	
	   	
	   	$data['min'] = trim($request['min']);
	   	$min = $data['min'];
	   	$data['max'] = trim($request['max']);
	   	$max = $data['max'];
	   	$data['contractorDetails'] = $this->GetContractor();
	   	$data['allocation'] = $this->GetAllocation();
	   	$data['contractType'] = $this->GetAContractType();
      	$data['economic'] = $this->GetEconomicCode($allocationType,$contract);
      	$data['Mainstatus'] = $this->GetStatus();
      	$data['transactions'] = $this->GetTransactions($contractor,$allocationType,$contract,$economicCode,$status,$datefrom,$dateto,$min,$max);
//dd($data['transactions']);

		return view('Report.transactions', $data);
	}


	public function GetContractor(){

   	$bank = DB::table('tblcontractor')->select('*')->orderby('contractor', 'Asc')->get(); //Select all banks form database
   	return $bank;

   	}

   	public function GetAllocation(){

   	$bank = DB::table('tblallocation_type')->select('*')->get(); //Select all banks form database
   	return $bank;

   	}

   	public function GetAContractType(){

   	$bank = DB::table('tblcontractType')->select('*')->get(); //Select all banks form database
   	return $bank;

   	}

   	public function GetEconomicCode($allocationType,$contract){

   	$bank = DB::table('tbleconomicCode')
   	->where('allocationID',$allocationType)
	->where('contractGroupID',$contract)
   	->select('*')
   	->get(); //Select all banks form database
   	return $bank;

   	}

   	public function GetStatus(){

   	$bank = DB::table('tblstatus')->select('*')->get(); //Select all banks form database
   	return $bank;

   	}

   	public function GetTransactions1($contractor,$allocationType,$contract,$economicCode,$status){

	   		if ($contractor != '' && $allocationType != '' && $contract != '' && $economicCode != '' ) {
	   		 $list = DB::table('tblpaymentTransaction')
	            ->join('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
	            ->join('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
	            ->where('companyID',$contractor)
	            ->where('contractTypeID',$allocationType)
	            ->where('contractID',$contract)
	            ->where('economicCodeID',$economicCode)
	            ->select('tblcontractor.*','tblpaymentTransaction.*','tblcontractDetails.beneficiary')
	            ->orderby('tblpaymentTransaction.ID', 'DESC')
	            ->paginate(50);
	            return $list;
	        } elseif($contractor == '' && $allocationType != '' && $contract != '' && $economicCode != '' &&$status != ''){

	        	 $list = DB::table('tblpaymentTransaction')
	            ->join('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
	            ->join('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
	           	 ->where('contractTypeID',$allocationType)
	            ->orwhere('contractID',$contract)
	            ->orwhere('economicCodeID',$economicCode)
	            ->orwhere('tblpaymentTransaction.status',$status)
	            ->select('tblcontractor.*','tblpaymentTransaction.*','tblcontractDetails.beneficiary')
	            ->orderby('tblpaymentTransaction.ID', 'DESC')
	            ->paginate(50);
	            return $list;
	        } elseif($contractor == '' && $allocationType != '' && $contract != '' && $economicCode != '' &&$status != ''){

	        	 $list = DB::table('tblpaymentTransaction')
	            ->join('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
	            ->join('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
	           	 ->where('contractTypeID',$allocationType)
	            ->orwhere('contractID',$contract)
	            ->orwhere('economicCodeID',$economicCode)
	            ->orwhere('tblpaymentTransaction.status',$status)
	            ->select('tblcontractor.*','tblpaymentTransaction.*','tblcontractDetails.beneficiary')
	            ->orderby('tblpaymentTransaction.ID', 'DESC')
	            ->paginate(50);
	            return $list;
	        }else{

	        	 $list = DB::table('tblpaymentTransaction')
	            ->join('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
	            ->join('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
	           ->select('tblcontractor.*','tblpaymentTransaction.*','tblcontractDetails.beneficiary')
	            ->orderby('tblpaymentTransaction.ID', 'DESC')
	            ->paginate(50);
	            return $list;
	       
	        }
   	}

   	Public function GetTransactions($contractor,$allocationType,$contract,$economicCode,$status,$datefrom,$dateto,$min,$max){

   		//dd($contractor,$allocationType,$contract,$economicCode,$status,$datefrom,$dateto);
	
	$table1  ="companyID";
	$table2  ="allocationType";
	$table3  ="contractTypeID";
	$table4  ="economicCodeID";
	$table5  ="tblpaymentTransaction.status";

	$qcontractor=1;
	if($contractor!=''){	$qcontractor ="`companyID`='$contractor'";}
	
	$qallocationType=1;
	if($allocationType!=''){	$qallocationType= "`allocationType`='$allocationType'" ;}
	
	$qcontract=1;
	if($contract!=''){	$qcontract = "`contractTypeID`='$contract'";}
	
	$qeconomicCode=1;
	if($economicCode!=''){	$qeconomicCode = "`economicCodeID`='$economicCode'" ;}
	
	$qstatus=1;
	if($status!=""){$qstatus = "`tblpaymentTransaction`.`status`='$status'" ;}

	$dateToday = date('Y-m-d');
	$dateNext = date('Y-m-d', strtotime($dateToday. ' - 1 month'));


	$timedate = "(DATE_FORMAT(`dateCreated`,'%Y-%m-%d') BETWEEN '$dateNext' AND '$dateToday')";
	if ($datefrom && $dateto != "") 
	{$timedate= "(DATE_FORMAT(`dateCreated`,'%Y-%m-%d') BETWEEN '$datefrom' AND '$dateto')";}

	$range =1;
	if ($min && $max != "") 
	{$range= " `totalPayment` BETWEEN '$min' AND '$max'";}

	


	            $List= DB::Select("SELECT *,(SELECT  `contractor` FROM `tblcontractor` WHERE `tblcontractor`.`id`=`tblpaymentTransaction`.`companyID` ) as contractor
	            ,(SELECT `beneficiary` FROM `tblcontractDetails` WHERE `tblcontractDetails`.`ID`=tblpaymentTransaction.contractID) as Bene
	            FROM `tblpaymentTransaction` WHERE $qcontractor and $qallocationType and $qcontract and $timedate and $qeconomicCode and $range  and $qstatus  ORDER BY  `tblpaymentTransaction`.`status` DESC  ");
				return $List;
	}

}
