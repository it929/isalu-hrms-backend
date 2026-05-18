<?php

namespace App\Http\Controllers\payroll;

use App\Http\Requests;
use App\Role;
use App\User;
use Auth;
use Entrust;
use App\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as rep;
use DB;

class Basefunction extends BasefunctionController 
{

    Public function VoultBalance44($id) {
   	 	$period=$this->ActivePeriod();
   	 	$BAL= DB::select("SELECT (IFNULL(sum(`amount`),0)-(SELECT IFNULL( sum( `totalPayment` ) , 0 ) FROM `tblpaymentTransaction` 
		WHERE (`tblpaymentTransaction`.`status`='6' or `tblpaymentTransaction`.`status`='2')
		and `tblpaymentTransaction`.`economicCodeID` ='$id' and `tblpaymentTransaction`.`period`='$period' )) 
		as balance  FROM `tblmonthlyAllocation` WHERE `status`='1' and `economicID`='$id' and `year`='$period'");
		
		$BAL2= DB::select("SELECT (
		IFNULL(sum(`liability_amount`),0)- (SELECT IFNULL(sum( `totalPayment`),0) FROM `tblpaymentTransaction` WHERE exists( select null from `create_contract` where `create_contract`.`fileNo`= `tblpaymentTransaction`.`fileNo` and `create_contract`.`economic_code`='$id' and (`tblpaymentTransaction`.`status`='6' or `tblpaymentTransaction`.`status`='2')))) as balance 
		FROM `create_contract` WHERE `economic_code`='$id'");
		if($BAL) {return $BAL[0]->balance- $BAL2[0]->balance ;} else{return '0';}
	}
	Public function UnclearedLiability($fn) {
   	 	$BAL= DB::select("SELECT IFNULL(sum(`liability_amount`),0) as balance FROM `create_contract` WHERE `fileNo`='$fn'");
		$BAL2= DB::select("SELECT IFNULL(sum(`totalPayment`),0) as balance FROM `tblpaymentTransaction` WHERE `FileNo`='$fn' and (`status`='6' or `status`='2')");
		if($BAL) {return $BAL[0]->balance- $BAL2[0]->balance ;} else{return '0';}
	}
	Public function NOTUSEDVoultBalance2($id) {
   	 	$period=$this->ActivePeriod();
   	 	$BAL= DB::select("SELECT ((`January`+ `February`+ `March`+`April`+ `May`+ `June`+ `July`+ `August`+ `September`+ `October`+ `November`+ `December`)
   	 	-(SELECT IFNULL( sum( `totalPayment` ) , 0 ) FROM `tblpaymentTransaction` 
		WHERE (`tblpaymentTransaction`.`status`='6' or `tblpaymentTransaction`.`status`='2')
		and `tblpaymentTransaction`.`economicCodeID` ='$id' and `tblpaymentTransaction`.`period`='$period' )) 
		as balance FROM `tblbudget` WHERE `economicCodeID`='$id' and `Period`='$period'");
		if($BAL) {return $BAL[0]->balance ;} else{return '0';}
	}
	Public function ContractBalance($id) {
		$BAL= DB::select("SELECT 
		(`contractValue`-(SELECT IFNULL( sum( `totalPayment` ) , 0 ) FROM `tblpaymentTransaction` 
		WHERE `tblpaymentTransaction`.`contractID` ='$id')) 
		as balance FROM `tblcontractDetails` WHERE `ID`='$id'");
		if($BAL) {return $BAL[0]->balance ;} else{return '0';}
	}
	Public function UnraisedContractBalance($id) {
		$BAL= DB::select("SELECT 
		(`contractValue`-(SELECT IFNULL( sum( `totalPayment` ) , 0 ) FROM `tblpaymentTransaction` 
		WHERE `tblpaymentTransaction`.`contractID` ='$id')) 
		as balance FROM `tblcontractDetails` WHERE `ID`='$id'");
		if($BAL) {return $BAL[0]->balance ;} else{return '0';}
	}
	Public function ContractBalanceOld($id) {
		$BAL= DB::select("SELECT 
		(`contractValue`-(SELECT IFNULL( sum( `totalPayment` ) , 0 ) FROM `tblpaymentTransaction` 
		WHERE (`tblpaymentTransaction`.`status`='6' or `tblpaymentTransaction`.`status`='2')
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

	$data=  DB::select("SELECT ('$contractValue'-IFNULL(sum(`totalPayment`),0)) as BBF FROM `tblpaymentTransaction` WHERE `contractID`='$contractID' and (`datePrepared` < '$datePrepared' or (`datePrepared` = '$datePrepared' and `ID`<'$voucherID') )");
	if($data){$BBF =$data[0]->BBF ;}
	return DB::select("SELECT '$contractValue' as contractValue, '$BBF' as BBF")[0];
	
	}
	Public function EcoDiffMGT($voutID,$status) {
	//0 status mean cancellation
	//1 status mean activate
	}
	Public function ApprovalRank($userid) {
	//0 status mean cancellation
	//1 status mean activate
	}
	Public function VoteInfo($id) {
	$data=  DB::select("SELECT *
	,(SELECT `allocation` FROM `tblallocation_type` WHERE `tblallocation_type`.`ID`=`tbleconomicCode`.`allocationID`) as allocationtype 
	,(SELECT `contractType` FROM `tblcontractType` WHERE `tblcontractType`.`ID`=`tbleconomicCode`.`contractGroupID`) as EconomicGroup
	,(SELECT `economicHead` FROM `tbleconomicHead` WHERE `tbleconomicHead`.`ID`=`tbleconomicCode`.`economicHeadID`) as EconomicHead
	FROM `tbleconomicCode` WHERE `ID`='$id'");
	if ($data){return $data[0];}
	else{ return DB::select("SELECT '' as economicHeadID  ,''as contractGroupID ,'' as allocationID ,'' as allocationtype  ,'' as EconomicGroup,'' as EconomicHead,'' as economicCode, '' as description")[0];
	}
	}
	Public function Comments($valueID,$contractID) {
	$com = DB::table('tblcomments')->where('affectedID', $valueID)->orwhere('affectedID', $contractID)->get();	    	
	    	$lis['comment'] = "";
	    	if($com){
	        	
		        	foreach($com as $k => $list){
		        		$newline = (array) $list;
		        		$name = DB::table('users')->where('username', $list->username)->first()->name;
		        		$newline['name'] = $name;
		        		$date = strtotime($list->added);
		        		$newline['comment'] = str_replace("\r\n", "<br>", $list->comment);
		        		$newline['date_added'] = date("F j, Y", $date);
		        		$newline['time'] = date("g:i a", $date);
		        		$newline = (object) $newline;
		        		$com[$k] = $newline;
		        	}
	        	$lis['comment'] = json_encode($com);
	        	
	        		
	        }
	    	$value = (object) $lis;
	    	return $value;
	}
	Public function Giver($account,$amount, $transdate,$remarks,$refNo) {
	if ($amount>0){
		$acctoinfo= DB::select("SELECT * FROM `tblaccountledger` WHERE `accountNo`='$account'");
		if($acctoinfo){
			DB::table('tblledger_transaction')->insert(array(
				'groupid'    	=> $acctoinfo[0]->groupid,
	                        'categoryid'    => $acctoinfo[0]->categoryid,
				'typeid'	=> $acctoinfo[0]->typeid,
				'accountno'    	=> $account,	
				'debit'    	=> 0,
				'credit'  	=> $amount,
				'remarks'    	=> $remarks,
				'refno'    	=> $refNo,
				'transaction_date'  => $transdate,
				'action_by'    	=> $Auth::user()->username,
				
			));
		}
	}
	}
	Public function Receiver($account,$transdate,$remarks,$refNo) {
	if ($amount>0){
		$acctoinfo= DB::select("SELECT * FROM `tblaccountledger` WHERE `accountNo`='$account'");
		if($acctoinfo){
			DB::table('tblledger_transaction')->insert(array(
				'groupid'    	=> $acctoinfo[0]->groupid,
	                        'categoryid'    => $acctoinfo[0]->categoryid,
				'typeid'	=> $acctoinfo[0]->typeid,
				'accountno'    	=> $account,	
				'debit'    	=> $amount,
				'credit'  	=> 0,
				'remarks'    	=> $remarks, 
				'refno'    	=> $refNo,
				'transaction_date'  => $transdate,
				'action_by'    	=> Auth::user()->username,
				
			));
		}
		}
	}
	Public function ContractorAccount($contractorId) {
	//0 status mean cancellation
	//1 status mean activate
	}
	Public function VoteAccount($econimiccodeId) {
	//0 status mean cancellation
	//1 status mean activate
	}
	Public function ProcurementAccount($econimiccodeId) {
	//0 status mean cancellation
	//1 status mean activate
	}
	Public function VateeAccount($vateeId) {
	//0 status mean cancellation
	//1 status mean activate
	}
	
	
    //Update Reconciliation Table
    public function refundsReconciliation($intTransactionID, $stringReceivedFrom='NJC', $stringDescription, $intEconomicID, $IntNoTreasury='-', $floatAmount, $date)
    {
        if(DB::table('tbleconomicCode')->where('ID', $intEconomicID)->first())
        {
            $economicCode = DB::table('tbleconomicCode')->where('ID', $intEconomicID)->value('economicCode');
        }else{
            $economicCode = null;
        }
        DB::table('treasury_refund')->insert(array(
            'number_of_voucher'     => $intTransactionID,
            'from_whom_received'    => $stringReceivedFrom,
            'des_of_receipt'        => $stringDescription,
            'economic_code_ncoa'    => $economicCode,
            'number_of_treasury'    => $IntNoTreasury,
            'amount_tsa_bank'       => $floatAmount,
            'economicID'            => $intEconomicID,
            'date'                  => $date,
            'created_at'            => date('Y-m-d')
        ));
    }//end function
    

    //Economic Code
    public function getEconomicCode($allocationTypeID, $contractTypeID)
    {
        $data['ecoCode'] = DB::table('tbleconomicCode')
            ->join('tblallocation_type', 'tblallocation_type.ID', '=', 'tbleconomicCode.allocationID')
            ->join('tblcontractType', 'tblcontractType.ID', '=', 'tbleconomicCode.contractGroupID')
            ->select('*', 'tblallocation_type.ID as allocationTypeID', 'tblcontractType.ID as contractTypeID', 'tbleconomicCode.ID as economicID')
            ->where('tbleconomicCode.allocationID', $allocationTypeID)
            ->where('tbleconomicCode.contractGroupID', $contractTypeID)
            ->where('tbleconomicCode.status', 1)
            ->get();
        foreach($data['ecoCode'] as $key=> $value)
        {
            $lis=(array)$value;
            $lis['bal']=$this->VoultBalance($value->economicID);
            $value=(object)$lis;
            $data['ecoCode'][$key]=$value;
        }
        return $data;
    }
	
	
}
