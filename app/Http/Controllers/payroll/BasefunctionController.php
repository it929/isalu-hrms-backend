<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;

use App\Http\Requests;
use App\Role;
use App\User;
use Auth;
use Entrust;
use App\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as rep;
use DB;

class BasefunctionController extends Controller
{
    	Public function VoultBalance($id) {
   	 	$period=$this->ActivePeriod();
   	 	return $this->RealVoultBalance($id,$period);
	}
	Public function RealVoultBalance($id,$period) {
   	 	$BAL= DB::select("SELECT (IFNULL(sum(`amount`),0)-(SELECT IFNULL( sum( `totalPayment` ) , 0 ) FROM `tblpaymentTransaction` 
		WHERE (`tblpaymentTransaction`.`status`>'0')
		and `tblpaymentTransaction`.`economicCodeID` ='$id' and `tblpaymentTransaction`.`period`='$period' )) 
		as balance  FROM `tblmonthlyAllocation` WHERE `status`='1' and `economicID`='$id' and `year`='$period'");
		$refBal= DB::select("SELECT IFNULL(sum(`amount_tsa_bank`),0) as amount FROM `treasury_refund` WHERE `economicID`='$id' and receipt_period='$period'");
		if($refBal) { $refb= $refBal[0]->amount ;} else{ $refb = 0;}
		if($BAL) {return $BAL[0]->balance + $refb ;} else{return $refb+0;}
	}
	Public function TotalSpent($id,$period='') {
	    if($period=='')$period=$this->ActivePeriod();
   	 	$BAL= DB::select("SELECT  IFNULL( sum( `totalPayment` ) , 0 ) as Spent FROM `tblpaymentTransaction` 
		WHERE (`tblpaymentTransaction`.`status`='1' or `tblpaymentTransaction`.`status`='2' or `tblpaymentTransaction`.`status`='6')
		and `tblpaymentTransaction`.`economicCodeID` ='$id' and `tblpaymentTransaction`.`period`='$period' ");
		if($BAL) {return $BAL[0]->Spent ;} else{return '0';}
	}
	Public function AvailableBal($id) {
	    $period=$this->ActivePeriod();
	    $BAL= DB::select("SELECT *,
	    SUM(CASE WHEN `eco_destination`='$id' THEN `amount` ELSE 0 END) -SUM(CASE WHEN `eco_source`='$id' THEN `amount` ELSE 0 END) AS balance 
        FROM `tbllendborrow` WHERE period='$period' and (`eco_source`='$id' or `eco_destination`='$id')");
        //return $BAL[0]->balance;
   	 	return $this->VoultBalance($id)+$BAL[0]->balance;
	}
	Public function Refunds($id) {
	    $period=$this->ActivePeriod();
	    return DB::table("treasury_refund")->where('economicID',$id)->where('receipt_period',$period)->sum('amount_tsa_bank');
	}
	Public function ContractBalance($id) {
		$BAL= DB::select("SELECT 
		(`contractValue`-(SELECT IFNULL( sum( `totalPayment` ) , 0 ) FROM `tblpaymentTransaction` 
		WHERE `tblpaymentTransaction`.`contractID` ='$id')) 
		as balance FROM `tblcontractDetails` WHERE `ID`='$id'");
		if($BAL) {return $BAL[0]->balance ;} else{return '0';}
	}
	Public function PaidContractBalance($id) {
		$BAL= DB::select("SELECT 
		(`contractValue`-(SELECT IFNULL( sum( `totalPayment` ) , 0 ) FROM `tblpaymentTransaction` 
		WHERE (`tblpaymentTransaction`.`vstage`>'0')
		and `tblpaymentTransaction`.`contractID` ='$id')) 
		as balance FROM `tblcontractDetails` WHERE `ID`='$id'");
		if($BAL) {return $BAL[0]->balance ;} else{return '0';}
	}
	Public function ContractBalanceForEdit($id) {
		$BAL= DB::select("SELECT 
		(`contractValue`-(SELECT IFNULL( sum( `totalPayment` ) , 0 ) FROM `tblpaymentTransaction` 
		WHERE (`tblpaymentTransaction`.`status`>'0')
		and `tblpaymentTransaction`.`contractID` ='$id')) 
		as balance FROM `tblcontractDetails` WHERE `ID`='$id'");
		if($BAL) {return $BAL[0]->balance ;} else{return '0';}
	}
	
	Public function ActivePeriod() {
	$data=  DB::select("SELECT * FROM `tblactiveperiod`");
	if($data){return $data[0]->year;}else{return "";}	
	}
	
	Public function TotalUnallocated($ecoID,$month,$year) {
	$totalalocation=0;
	$totalreceived=0;
	$data=  DB::select("SELECT `allocationValue` FROM `tblbudget` WHERE `economicCodeID`='$ecoID' and `Period`='$year'");
	if($data){$totalalocation=$data[0]->allocationValue;}
	$data=  DB::select("SELECT sum(`amount`) as totalreceived FROM `tblmonthlyAllocation` WHERE `economicID`='$ecoID' and `year`='$year' and `month`<>'$month' and `status`=1");
	if($data){$totalreceived=$data[0]->totalreceived;}
	return $totalalocation-$totalreceived;
	}
	
	Public function VoucherFinancialInfo($voucherID) {
	$contractValue=0;
	$contractID='';
	$data=  DB::select("SELECT `contractID`,`datePrepared` FROM `tblpaymentTransaction` WHERE `ID`='$voucherID'");
	if($data){$contractID=$data[0]->contractID;
	$datePrepared=$data[0]->datePrepared;}
	$data=  DB::select("SELECT `contractValue` FROM `tblcontractDetails` WHERE `ID`='$contractID'");
	if($data){$contractValue=$data[0]->contractValue;}

	$data=  DB::select("SELECT ('$contractValue'-IFNULL(sum(`totalPayment`),0)) as BBF FROM `tblpaymentTransaction` WHERE `contractID`='$contractID' and (`datePrepared` < '$datePrepared' or (`datePrepared` = '$datePrepared' and `ID`<'$voucherID' ))");
	if($data){$BBF =$data[0]->BBF ;}
	return DB::select("SELECT '$contractValue' as contractValue, '$BBF' as BBF")[0];
	
	}
	Public function VotebookUpdate($ecoID,$refNo,$remark,$amount,$trandate,$transtype,$period='') {
	//$transtype=1 when liabilty taken is approved
	//$transtype=2 when liability is cleared
	//$transtype=3 when economic vote is funded from monthly allocation
	//$transtype=4 when already funded economic vote is reversed
	//$transtype=5 when liability cleared is rejected
	//$transtype=6 when an already liability taken is rejected
	
	switch($transtype){
		case "1":
			$ref1='';
			$remark1='';
			$amt1='';
			$total='';
			$bal1=$this->VoultBalance($ecoID);
			$ref2=$refNo;
			$amt2=$amount;
			$amt3='0';
			$liatotaloutstanding=$this->OutstandingLiability($ecoID);
			$remark2=$remark;
			$bal2=$this->VoultBalance($ecoID);
			break;
		case "2":
			$ref1=$refNo;
			$remark1=$remark;
			$amt1=$amount;
			$total=$this->TotalCleared($ecoID);;
			$bal1=$this->VoultBalance($ecoID);
			$ref2='';
			$amt2='0';
			$amt3=$this->LiiabilityCleared($refNo);
			$liatotaloutstanding=$this->OutstandingLiability($ecoID);
			$remark2="";
			$bal2=$this->VoultBalance($ecoID);
			break;
		case "5":
			$ref1=$refNo;
			$remark1=$remark;
			$amt1="( $amount)";
			$total=$this->TotalCleared($ecoID);;
			$bal1=$this->RealVoultBalance($ecoID,$period);
			$ref2='';
			$amt2='0';
			$amt3=$this->LiiabilityCleared($refNo);
			$liatotaloutstanding=$this->OutstandingLiability($ecoID);
			$remark2="";
			$bal2=$this->VoultBalance($ecoID);
			break;
		case "3":
			$ref1=$refNo;
			$remark1=$remark;
			$amt1="( $amount )";
			$total='';
			$bal1=$this->RealVoultBalance($ecoID,$period);
			$ref2='';
			$amt2='0';
			$amt3='';
			$liatotaloutstanding=$this->OutstandingLiability($ecoID);
			$remark2="";
			$bal2=$this->RealVoultBalance($ecoID,$period);
			break;
		case "4":
			$ref1=$refNo;
			$remark1=$remark;
			$amt1="$amount";
			$total='';
			$bal1=$this->RealVoultBalance($ecoID,$period);
			$ref2='';
			$amt2='0';
			$amt3='';
			$liatotaloutstanding=$this->OutstandingLiability($ecoID);
			$remark2="";
			$bal2=$this->VoultBalance($ecoID);
			break;
	}
	
	DB::table('tblvotebookrecord')->insert(array(
			'ecoID'	    	=> $ecoID,
			'refNo'    	=> $ref1,
			'particular'    => $remark1,
			'payment'    	=> $amt1,
			'total'    	=> $total,
			'balance'    => $bal1,
			'liaref'    	=> $ref2,
			'incurred'    	=> $amt2,
			'cleared'      => $amt3,
			'liatotaloutstanding'  => $liatotaloutstanding,
			'remark'    	=> $remark2,
			'availablebal'   => $bal2,
                        'trandate'    	=> $trandate,
                        'transtype'    	=> $transtype,
                        'period'    	=> $this->ActivePeriod(),
                        
		));
	
	}
	Public function OutstandingLiability($id) {
	$value='';
	//die("SELECT IFNULL(sum(`liability_amount`),0) as tliability FROM `create_contract` WHERE `economic_code`='$id' and `active`='0' ");
	$tliability= DB::select("SELECT IFNULL( sum(`liability_amount`),0) as tliability FROM `create_contract` WHERE `economic_code`='$id' and `active`='0' ")[0]->tliability ;
	$tliabilitycleared= DB::select("SELECT IFNULL( sum(`totalPayment`),0)as tliabilitycleared FROM `tblpaymentTransaction` WHERE `economicCodeID`='$id' and `status`>1 
	and exists(SELECT null FROM `create_contract` WHERE `create_contract`.`fileNo`=`tblpaymentTransaction`.`FileNo`) ")[0]->tliabilitycleared ;
	//dd(" $tliability - $tliabilitycleared ");
	$value=$tliability-$tliabilitycleared;
	return $value;
	}
	Public function LiiabilityCleared($id) {
	$value='';
	$data=  DB::select("SELECT `totalPayment` FROM `tblpaymentTransaction` WHERE `ID`='$id' 
	and exists(SELECT null FROM `create_contract` WHERE `create_contract`.`fileNo`=`tblpaymentTransaction`.`FileNo`) ");
	if($data){$value=$data[0]->totalPayment;}
	return $value;
	}
	Public function TotalCleared($id) {
		return '0';
	}
	Public function UnclearedLiability($fn) {
   	 	$BAL= DB::select("SELECT IFNULL(sum(`liability_amount`),0) as balance FROM `create_contract` WHERE `fileNo`='$fn'");
		$BAL2= DB::select("SELECT IFNULL(sum(`totalPayment`),0) as balance FROM `tblpaymentTransaction` WHERE `FileNo`='$fn' and (`status`='6' or `status`='2')");
		if($BAL) {return $BAL[0]->balance- $BAL2[0]->balance ;} else{return '0';}
	}
	Public function VoteInfo($id) {
	$data=  DB::select("SELECT *
	,(SELECT `allocation` FROM `tblallocation_type` WHERE `tblallocation_type`.`ID`=`tbleconomicCode`.`allocationID`) as allocationtype 
	,(SELECT `contractType` FROM `tblcontractType` WHERE `tblcontractType`.`ID`=`tbleconomicCode`.`contractGroupID`) as EconomicGroup
	,(SELECT `economicHead` FROM `tbleconomicHead` WHERE `tbleconomicHead`.`ID`=`tbleconomicCode`.`economicHeadID`) as EconomicHead
	FROM `tbleconomicCode` WHERE `ID`='$id'");
	if ($data){return $data[0];}
	else{ return DB::select("SELECT '' as economicHeadID ,''as contractGroupID ,'' as allocationID ,'' as allocationtype  ,'' as EconomicGroup,'' as EconomicHead,'' as economicCode, '' as description")[0];
	}
	}
	Public function ContractAttachment($id) {
   	 	//return DB::table('tblcontractfile')->SELECT('*')->where('contractid', $id)->get();
   	 	return DB::SELECT ("SELECT * FROM `tblcontractfile` WHERE `contractid`='$id'");
		
	}
	Public function ClaimAttachment($id) {
   	 	//return DB::table('tblcontractfile')->SELECT('*')->where('contractid', $id)->get();
   	 	return DB::SELECT ("SELECT * FROM `tblcontractfile` WHERE `contractid`='$id'");
		
	}
	Public function preContractAttachment($id) {
   	 	//return DB::table('tblcontractfile')->SELECT('*')->where('contractid', $id)->get();
   	 	return DB::SELECT ("SELECT * FROM `tblcontractfile` WHERE `contractid`='$id'");
		
	}
	Public function ApprovalReferal($id) {
   	 	return DB::SELECT ("SELECT * FROM `tblaction_rank` WHERE `cont_payment_active`=1 and `status`=1 and `userid`<>'$id'");
	}
	Public function paymemtstatus() {
   	 	return DB::SELECT ("SELECT * FROM `tblstatus` WHERE `payment`=1");
	}
	
	
	//public function getUserLoggedInRole($transactionID)
	public function getUserLoggedInRole($contractType)
	{
	    
	    $role = DB::table('assign_user_role')
			->leftjoin('user_role', 'user_role.roleID', '=', 'assign_user_role.roleID')
			->where('assign_user_role.userID', Auth::User()->id)
			->select('user_role.rolename', 'user_role.roleID')
			->first();
			
	    if(strtoupper($role->rolename) == strtoupper("Recurrent"))
	    {
	     	$return = 'nicnModuleViews/capitalRecurrent/viewVoucher';
	     }
	     else if(strtoupper($role->rolename) == strtoupper("Capital"))
	     {
	     	//$return = 'nicnModuleViews/capitalRecurrent/viewVoucher';
	     	$return = 'nicnModuleViews/capitalVoucher/viewVoucher';
	     }
	     else if(strtoupper($role->rolename) == strtoupper("Subaccount"))
	     {
	     	$return = 'nicnModuleViews/subAccount/viewVoucher';
	     }else{
	     	$return = 'nicnModuleViews/capitalRecurrent/viewVoucher';
	     }
	     return $return; 
	}
	public function UnitVoucher($contractType)
	{
	    
	    $role = DB::table('assign_user_role')
			->leftjoin('user_role', 'user_role.roleID', '=', 'assign_user_role.roleID')
			->where('assign_user_role.userID', Auth::User()->id)
			->select('user_role.rolename', 'user_role.roleID')
			->first();
			
	    if(strtoupper($role->rolename) == strtoupper("Recurrent"))
	    {
	     	$return = 'R';
	     }
	     else if(strtoupper($role->rolename) == strtoupper("Capital"))
	     {
	     	$return = 'C';
	     }
	     return 'R'; 
	}
	
	
	//
	public function getUserRole()
	{
	
	    return DB::table('assign_user_role')
			->join('user_role', 'user_role.roleID', '=', 'assign_user_role.roleID')
			->where('assign_user_role.userID', Auth::User()->id)
			->select('*', 'user_role.rolename', 'user_role.roleID')
			->first();
	}
	
	
	public function getNumberOfDaysFromJanuaryTillDate()
	{   //written from scratch by AJAX
           $totalDay = 0;
           if(date('m') > 1)
           {
               for($i=1; $i <= (date('m')-1); $i++){ 
                    $totalDay += cal_days_in_month(0, $i, date('Y'));
               }
               $totalDay = $totalDay + date('d');
           }else{
               $totalDay = date('d');
           }
           
           return $totalDay; //returns days
	}
	
	
}//end clas
