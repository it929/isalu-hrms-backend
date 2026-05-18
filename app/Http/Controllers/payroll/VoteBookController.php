<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;
//use Auth;
use App\Http\Requests;
use DB;
use Auth;
use Session;


class VoteBookController extends functionFundsController
{
    
	public function __construct()
    {
        $this->middleware('auth');
    }
    

   //VIEW VOTE BOOK
   public function createVoteBook(Request $request)
   { 
       //Get Request
	    $allocationsource = 5; //trim($request['allocationsource']);
	    $startDate = trim($request['startDate']);
	    $endDate = trim($request['endDate']); 
	    $budgettype = trim($request['budgettype']);
	    $economichead = trim($request['economichead']);
	    $economiccode = trim($request['economiccode']);
	    //
	    $period = date_format(date_create($endDate), "Y");
	    $month = date_format(date_create($endDate), "F");
	    $monthInt = date_format(date_create($endDate), "m");
	    $data['getDateSelected'] = date_format(date_create($endDate), "jS F, Y");
	    $data['unitName'] = ((DB::table('tblcontractType')->where('ID', $budgettype)->first()) ? DB::table('tblcontractType')->where('ID', $budgettype)->value('contractType') : 'All ');
	    $yearmoth=$period."-". date("m",strtotime($month));
	    
	    //Retain request values
	    $data['allocationsource'] = $allocationsource;
    	$data['month'] = $month;
    	$data['period'] = $period;
    	$data['budgettype'] = $budgettype;
    	$data['economichead'] = $economichead;
    	$data['economiccode'] = $economiccode;
	    
        //Get Data
        $data['EconomicHead'] = $this->EconomicHead($budgettype); 
	    $data['AllocationSource'] = $this->AllocationSource();
	    $data['BudgetType'] = $this->BudgetType();
	    $data['YearPeriod'] = $this->YearPeriod();
	    $data['EconomicCode'] = $this->EconomicCode($allocationsource, $economichead);
	    $data['year'] = $period;
	    //Track and get Previous Year Allocation Releases
	    $data['getPreviousPeroid'] = (($period > 0) ? ($period - 1) : 0);
	    $oldPeroid = (($period > 0) ? ($period - 1) : $period);

        if($economiccode != "")
        {
            $data['queryVoteBook'] = $this->queryVoteBookReport($economiccode, $startDate, $endDate);
    	    $getAllocation = DB::table('tblmonthlyAllocation')->where('economicID', $economiccode)->where('year', $period)->orderBy('ID', 'Desc')->get(); //->where('status', 1)
    	    $totalAllocationReceived = DB::table('tblmonthlyAllocation')->where('economicID', $economiccode)->where('year', $period)->sum('amount');
    	    $data['getTotalSumAllocation'] = (!empty($totalAllocationReceived) ? $totalAllocationReceived : 0);

    	    $data['getAllocationReceived'] = array();
    	    $arrayAllocationReceive = array();
            $sumAllocation = 0.0;
            $count = 1;
        	foreach($data['queryVoteBook'] as $mainKey=>$list)
        	{
        	   if((count($getAllocation) <= count($data['queryVoteBook'])))
    	       {
            	   if( $mainKey <= (count($getAllocation) -1) )
            	   {
            	       $arrayAllocationReceive[$mainKey] = $getAllocation[$mainKey]->amount;
            	   }else{
            	       $arrayAllocationReceive[$mainKey] = "";
            	   }
    	       }else{
    	           if( $count++ <= (count($data['queryVoteBook'])-1) )
            	   {
            	       $arrayAllocationReceive[$mainKey] = $getAllocation[$mainKey]->amount;
            	       $sumAllocation += $getAllocation[$mainKey]->amount;
            	   }else{
            	       $arrayAllocationReceive[$mainKey] = $totalAllocationReceived - $sumAllocation;
            	   }
    	           //$arrayAllocationReceive[$mainKey] = "";
    	       }
        	        
        	}
        	$data['getAllocationReceived'] = $arrayAllocationReceive;
    	   
        }else{
            $data['queryVoteBook'] = [];
            $data['getAllocationReceived'] = [];
        }
	    $data['numberOfDayTillDate'] = $this->getNumberOfDaysFromJanuaryTillDate();
	    
   	    return view('voteBookReport.voteBookLedger', $data);
   
       
   }
   
   
 
    public function GetEconomicGroup(){
    
       	$bank = DB::table('tblcontractType')->select('*')->get(); //Select all banks form database
       	return $bank;

   }
   
   
   //Query Daily, weekly, Monthly etc
   Public function queryVoteBookReport($economicCodeID, $startDate, $endDate)
   {
       if(($startDate != "") and  ($endDate !=""))
       {
            $query = DB::table('tblpaymentTransaction')
                ->where('tblpaymentTransaction.economicCodeID', $economicCodeID)
                ->whereBetween('tblpaymentTransaction.datePrepared', [$startDate, $endDate])
                ->select('tblpaymentTransaction.paymentDescription', 'tblpaymentTransaction.amtPayable as paymentAmount', 
                'tblpaymentTransaction.economicCodeID', 'tblpaymentTransaction.contractID')
                ->orderBy('tblpaymentTransaction.ID', 'Desc')
                ->get();
       }else{
           $query = DB::table('tblpaymentTransaction')
                ->where('tblpaymentTransaction.economicCodeID', $economicCodeID)
                ->select('tblpaymentTransaction.paymentDescription', 'tblpaymentTransaction.amtPayable as paymentAmount', 
                'tblpaymentTransaction.economicCodeID', 'tblpaymentTransaction.contractID')
                ->orderBy('tblpaymentTransaction.ID', 'Desc')
                ->get();
       }
       
       /*
       ///////
    	$qallocationsource=1;
    	if($allocationsource!=''){$qallocationsource="`allocationType`='$allocationsource'";} 
    	$qbudgettype=1;
    	if($budgettype!=''){$qbudgettype="`economicGroupID`='$budgettype'";} 
    	$qeconomichead=1;
    	if($economichead!=''){$qeconomichead="`economicHeadID`='$economichead'";} 
    	$qeconomiccode=1;
    	if($economiccode!=''){$qeconomiccode="`economicCodeID`='$economiccode'";} 
    	
    	$timedate= " DATE_FORMAT(`tblpaymentTransaction`.`datePrepared`,'%Y-%m') ='$yearmoth'";
    	$timedatetodate= " DATE_FORMAT(`tblpaymentTransaction`.`datePrepared`,'%Y-%m') <='$yearmoth'";
    	$ydate= " DATE_FORMAT(`tblpaymentTransaction`.`datePrepared`,'%Y') ='$period'";
    	$dataBetween = (($startDate && $endDate) ? (" and `tblpaymentTransaction`.`datePrepared` BETWEEN $startDate AND $endDate") : "");
    	$ret="SELECT * 	 
    	,(SELECT `allocation` FROM `tblallocation_type` WHERE `tblallocation_type`.`ID`=`tblbudget`.`allocationType`) as allocationsource
    	,(SELECT `contractType` FROM `tblcontractType` WHERE `tblcontractType`.`ID`=`tblbudget`.`economicGroupID`) as economicgroup 
    	,(SELECT `economicHead` FROM `tbleconomicHead` WHERE `tbleconomicHead`.`ID`=`tblbudget`.`economicHeadID`) as economichead
    	,(SELECT `Code` FROM `tbleconomicHead` WHERE `tbleconomicHead`.`ID`=`tblbudget`.`economicHeadID`) as economicheadcode
    	,(SELECT `economicCode` FROM `tbleconomicCode` WHERE `tbleconomicCode`.`ID`=`tblbudget`.`economicCodeID`) as economiccode
    	,(SELECT `description` FROM `tbleconomicCode` WHERE `tbleconomicCode`.`ID`=`tblbudget`.`economicCodeID`) as economicdisc
    	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='January') as January
    	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='February') as February
    	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='March') as March
    	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='April') as April
    	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='May') as May
    	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='June') as June
    	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='July') as July
    	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='August') as August
    	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='September') as September
    	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='October') as October
    	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='November') as November
    	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='December') as December
    	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1') as receivedallocation
    	,(SELECT IFNULL( sum( `totalPayment` ) , 0 ) FROM `tblpaymentTransaction`
    	WHERE `tblpaymentTransaction`.`status`='2' 
    	and `tblpaymentTransaction`.`economicCodeID` =`tblbudget`.`economicCodeID` and  $timedate) 
    	as bookonhold
    	,(SELECT IFNULL( sum( `totalPayment` ) , 0 ) FROM `tblpaymentTransaction` 
    	WHERE `tblpaymentTransaction`.`status`='6' 
    	and `tblpaymentTransaction`.`economicCodeID` =`tblbudget`.`economicCodeID` and  $timedate) 
    	as expend
    	,(SELECT IFNULL( sum( `totalPayment` ) , 0 ) FROM `tblpaymentTransaction` 
    	WHERE `tblpaymentTransaction`.`status`='2' 
    	and `tblpaymentTransaction`.`economicCodeID` =`tblbudget`.`economicCodeID` and  $timedatetodate and $ydate $dataBetween) 
    	as bookonholdtodate
    	,(SELECT IFNULL( sum( `totalPayment` ) , 0 ) FROM `tblpaymentTransaction` 
    	WHERE `tblpaymentTransaction`.`status`='6' 
    	and `tblpaymentTransaction`.`economicCodeID` =`tblbudget`.`economicCodeID` and  $timedatetodate and $ydate $dataBetween) 
    	as expendtodate
    	FROM `tblbudget` WHERE `Period`='$period' and $qallocationsource and  $qbudgettype and $qeconomichead and $qeconomiccode order by `economicGroupID`,`economicHeadID`,`economicCodeID`";
    	//die($ret);
    	$List= DB::Select($ret);
    	*/
    	
	return $query;
	
	}
   
   

}//end class