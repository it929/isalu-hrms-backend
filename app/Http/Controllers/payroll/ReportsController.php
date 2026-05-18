<?php

namespace App\Http\Controllers\payroll;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
class ReportsController extends functionFundsController
{
	public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->middleware('auth');
        $this->username = Session::get('userName');
            return $next($request);
        });
    }//
 public function VoultTransReport(Request $request)
   {  


   	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	   
	   
	   $period= trim($request['period']);
	   $allocationsource= "5";//trim($request['allocationsource']);
	   $budgettype= trim($request['budgettype']);
	   $economichead= trim($request['economichead']);
	   $economiccode= trim($request['economiccode']);
	   $status= trim($request['status']);
	   
	   $data['allocationsource'] = $allocationsource;
	   $data['period'] = $period;
	    $data['budgettype'] = $budgettype;
	   $data['economichead'] = $economichead;
	   $data['economiccode'] = $economiccode;
	   $data['status'] = $status;
	  
	   $data['Statuss'] =  DB::Select("SELECT * FROM `tblstatus` WHERE `fundstatus`=1 ");
	   $data['EconomicHead'] = $this->EconomicHead($budgettype); 
	   $data['AllocationSource'] = $this->AllocationSource();
	   $data['BudgetType'] = $this->BudgetType();
	   $data['YearPeriod'] = $this->YearPeriod();
	   $data['EconomicCode'] = $this->EconomicCode($allocationsource,$economichead);
	   switch ($data['status']){
	       case '0':
	           $data['VoteTrans'] = DB::Select("SELECT * FROM `tblpaymentTransaction` WHERE `economicCodeID`='$economiccode' and `period`='$period' and status<=1 order by datePrepared");
	           break;
	        case '2':
	            $data['VoteTrans'] = DB::Select("SELECT * FROM `tblpaymentTransaction` WHERE `economicCodeID`='$economiccode' and `period`='$period' and status>1 order by dateTakingLiability");
	           break;
	           
	       default:
	    $data['VoteTrans'] = DB::Select("SELECT * FROM `tblpaymentTransaction` WHERE `economicCodeID`='$economiccode' and `period`='$period' order by datePrepared");
	   }
	//dd( $data['VoteTrans']);
   	return view('Report.votetrans', $data);
   }
   public function TotalMonthlyAllocation(Request $request)
   {  


   	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	   $data['economicGroup'] = trim($request['economicGroup']);
	   if($this->AccessNotGranted("allocation/totalmonthly")){return redirect('/')->with('message','Sorry! You do not have permission to access this page!!');}
	   $data['period']	=$request->input('period');//$request['period'];
	   //dd(get(old('period')));
	   if($data['period']=='') {$data['period']=session('period');}
	   if($data['economicGroup']=='') {$data['economicGroup']=session('economicGroup');}
	   $data['month']	=$request['month'];
	   $data['amount']	=$request['amount'];
	   $data['remarks']	=$request['remarks'];
	  if ( isset( $_POST['update'] ) ) {
		$this->validate($request, [
		'period'      	    => 'required'
		,'month'      	    => 'required'
		,'amount'      	    => 'required|numeric|between:0,9999999999999999.99'
		,'economicGroup'    => 'required',
		
		]);
			
			if(!DB::table('tbltotalMonthlyAllocation')->where('year','=', $data['period'])->where('month','=', $data['month'])->where('budgetType','=', $data['economicGroup'])->first())
			{
				DB::table('tbltotalMonthlyAllocation')
                                ->insert([
                                    'year'          => $data['period'],
                                    'month'         => $data['month'],
                                    'amount'        => $data['amount'],
                                    'budgetType'    => $data['economicGroup'], 
                                    'remarks'    => $data['remarks'] 
                                ]);
			}
			else
			{
			DB::table('tbltotalMonthlyAllocation')
			->where('year','=', $data['period'])
			->where('month','=', $data['month'])->where('budgetType','=', $data['economicGroup'])->update([
                          'amount' => ($data['amount']== "") ? 0 : $data['amount'],'remarks'    => $data['remarks']]);
			}
			$data['success'] = "successfully updated";
			Session::put('period',  $data['period']);
			Session::put('economicGroup',  $data['economicGroup']);
			//session('period')=$data['period'];
			return redirect()->back()->withInput();
			//return Redirect::back()->withInput(Input::all());
		 }
	   
	   $data['QReport'] = $this->TotalMonthAllocation($data['period'],$data['economicGroup']); 
	//   dd($data['QReport']);
	   $data['YearPeriod'] = $this->YearPeriod();
		$data['EconomicGroup'] = $this->GetEconomicGroup();
   	return view('allocation.monthlytotalallocation', $data);
   }
   public function VoultBalanceReport(Request $request)
   {  

//die($this->VoultBalance('20')) ;
   	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	   
	   
	   $period= trim($request['period']);
	   $allocationsource= trim($request['allocationsource']);
	   $budgettype= trim($request['budgettype']);
	   $economichead= trim($request['economichead']);
	   $economiccode= trim($request['economiccode']);
	   
	   $data['allocationsource'] = $allocationsource;
	   $data['period'] = $period;
	    $data['budgettype'] = $budgettype;
	   $data['economichead'] = $economichead;
	   $data['economiccode'] = $economiccode;
	  
	   
	   $data['EconomicHead'] = $this->EconomicHead($budgettype); 
	   $data['AllocationSource'] = $this->AllocationSource();
	   $data['BudgetType'] = $this->BudgetType();
	   $data['YearPeriod'] = $this->YearPeriod();
	   $data['EconomicCode'] = $this->EconomicCode($allocationsource,$economichead);
	  $data['QueryVoultReport'] = $this->QueryVoultReport($period,$allocationsource,$budgettype,$economichead,$economiccode);
	
   	return view('Report.VoultBalance', $data);
   }
   
   
   public function MonthlyVoultBalanceReport(Request $request)
   {  

   	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	   
	   
	   $month= trim($request['month']);
	   $period= trim($request['period']);
	   $allocationsource= trim($request['allocationsource']);
	   $budgettype= trim($request['budgettype']);
	   $economichead= trim($request['economichead']);
	   $economiccode= trim($request['economiccode']);
	   
	   $data['allocationsource'] = $allocationsource;
	   $data['month'] = $month;
	   $data['period'] = $period;
	    $data['budgettype'] = $budgettype;
	   $data['economichead'] = $economichead;
	   $data['economiccode'] = $economiccode;
	  
	   
	   $data['EconomicHead'] = $this->EconomicHead($budgettype); 
	   $data['AllocationSource'] = $this->AllocationSource();
	   $data['BudgetType'] = $this->BudgetType();
	   $data['YearPeriod'] = $this->YearPeriod();
	   $data['EconomicCode'] = $this->EconomicCode($allocationsource,$economichead);
	   $yearmoth=$period."-". date("m",strtotime($month));
	   //die($yearmoth);
	   $data['QueryVoultReport'] = $this->QueryVoultReportmonth($yearmoth,$allocationsource,$budgettype,$economichead,$economiccode,$month,$period);
	
   	return view('Report.VoultBalancemonthly', $data);
   }
   
   
   
   //View Daily, Weekly, Monthly and Yearly Expenditure Report
   public function ExpenditureBalanceReportAtAnyTime(Request $request)
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
	    
	    
	    //////
	    $data['QueryVoultReport'] = $this->queryVoteReportDailyWeeklyMonth($yearmoth, $allocationsource, $budgettype, $economichead, $economiccode, $month, $period, $startDate, $endDate);
	    $listLastRelease = $this->queryVoteReportDailyWeeklyMonth($yearmoth, $allocationsource, $budgettype, $economichead, $economiccode, $month, $oldPeroid, $startDate, $endDate);
	    
	    $listLastReleaseArray = array();
	    
	    foreach($data['QueryVoultReport'] as $listKey=>$list)
        {
    	   if($listLastRelease)
    	   {
    	        $listLastReleaseArray[$listKey][$list->economicCodeID] = $listLastRelease[$listKey]->allocationValue;  
    	   }else{
    	       $listLastReleaseArray[$listKey][$list->economicCodeID] = 0;
    	   }
    	   
    	}
	    
	    
	        
	    $data['QueryVoteReportLastYear'] = $listLastReleaseArray;

   	    return view('Report.ExpenditureBalanceDailyWeeklyMonthlyYearly', $data);
   
       
   }
   
   
   
   
   
   public function VoultExpendictureReport333(Request $request)
   {  

   	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	   
	   
	   $month= $request['month'];
	   $period= $request['period'];
	  $data['fromdate']= $request['fromdate'];
	   $data['todate']= $request['todate'];
	   $allocationsource= $request['allocationsource'];
	   $budgettype= $request['budgettype'];
	   $economichead=$request['economichead'];
	   $economiccode= $request['economiccode'];
	   
	   $data['allocationsource'] = $allocationsource;
	   $data['month'] = $month;
	   $data['period'] = $period;
	    $data['budgettype'] = $budgettype;
	   $data['economichead'] = $economichead;
	   $data['economiccode'] = $economiccode;
	  
	   
	   $data['EconomicHead'] = $this->EconomicHead($budgettype); 
	   $data['AllocationSource'] = $this->AllocationSource();
	   $data['BudgetType'] = $this->BudgetType();
	   $data['YearPeriod'] = $this->YearPeriod();
	   $data['EconomicCode'] = $this->EconomicCode($allocationsource,$economichead);
	   $yearmoth=$period."-". date("m",strtotime($month));
	   //die($yearmoth);
	   $data['QueryVoultReport'] = $this->QueryVoultReportmonth($yearmoth,$allocationsource,$budgettype,$economichead,$economiccode,$month,$period);
	
   	return view('Report.VoultExpenditure', $data);
   }
 
 public function VoultExpendictureReport(Request $request)
   {  

   	  $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	 $dateToday  = date('Y-m-d'); 
	$data['fromdate'] = $request['fromdate'];
   	$data['todate'] = $request['todate'];
   	$data['period']= $request['period'];
   	if($data['fromdate']==''){ $data['fromdate'] = date('Y-m-d', strtotime($dateToday. ' - 1 month')); }
   	if($data['todate']==''){ $data['todate'] = $dateToday; }
   	if($data['period']==''){ $data['period'] = $this->ActivePeriod(); }
	  
	   
	   $data['allocationsource']= $request['allocationsource'];
	   $data['budgettype']= $request['budgettype'];
	   $data['economichead']= $request['economichead'];
	   $data['economiccode']= $request['economiccode'];
	
	   //$data['month'] = $month;
	   //$activemonth = date("n", strtotime($month));
	   //$active_date=$period."-".$activemonth."-1";
	   //die($active_date);
	   $data['EconomicHead'] = $this->EconomicHead($data['budgettype']); 
	   $data['AllocationSource'] = $this->AllocationSource();
	   $data['BudgetType'] = $this->BudgetType();
	   $data['YearPeriod'] = $this->YearPeriod();
	   $data['EconomicCode'] = $this->EconomicCode($data['allocationsource'],$data['economichead']);
	   //$yearmoth=$period."-". date("m",strtotime($month));
	   //die($yearmoth);
	   $data['VoteBookRecord'] = $this->VoteBookRecord($data['economiccode'],$data['fromdate'],$data['todate'],$data['period']);
	
   	return view('Report.votebookrecord', $data);
   }
 
    public function GetEconomicGroup(){
    
       	$bank = DB::table('tblcontractType')->select('*')->get(); //Select all banks form database
       	return $bank;

   }
   
   
   //Query Daily, weekly, Monthly etc
   Public function queryVoteReportDailyWeeklyMonth($yearmoth, $allocationsource, $budgettype, $economichead, $economiccode, $month, $period, $startDate, $endDate)
   {
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
    	
	return $List;
	
	}
    public function RangeExpenditureReport(Request $request)
   {  

   	   $data['from']	=$request->input('from');
	   //if($data['from']=='') {$data['from']=session('from');}
	   //if($data['from']=='') {$data['from']=Carbon::now()->subMonth()->format('Y-m-d');}
	   if($data['from']=='') {$data['from']=Carbon::now()->format('Y-m-d');}
	   //Session::put('from',  $data['from']);
	   $data['to']	=$request->input('to');
	   //if($data['to']=='') {$data['to']=session('to');}
	   if($data['to']=='') {$data['to']=Carbon::now()->format('Y-m-d');}
	   //Session::put('to',  $data['to']);
	   //die(date('F', strtotime($data['to'])));
	   
	   $month= trim($request['month']);
	   $period= trim($request['period']);
	   if($period=='') {$period='2020';}
	   $allocationsource= trim($request['allocationsource']);
	   $budgettype= trim($request['budgettype']);
	   $economichead= trim($request['economichead']);
	   $economiccode= trim($request['economiccode']);
	   
	   $data['allocationsource'] = $allocationsource;
	   $data['month'] = date('F', strtotime($data['to']));
	   $data['period'] = $period;
	    $data['budgettype'] = $budgettype;
	   $data['economichead'] = $economichead;
	   $data['economiccode'] = $economiccode;
	  
	   
	   $data['EconomicHead'] = $this->EconomicHead($budgettype); 
	   $data['AllocationSource'] = $this->AllocationSource();
	   $data['BudgetType'] = $this->BudgetType();
	   $data['YearPeriod'] = $this->YearPeriod();
	   $data['EconomicCode'] = $this->EconomicCode($allocationsource,$economichead);
	   $yearmoth=$period."-". date("m",strtotime($month));

	   $data['QueryVoultReport'] = $this->VoteBalRangeReport($yearmoth,$allocationsource,$budgettype,$economichead,$economiccode,date('F', strtotime($data['to'])),$period,$data['from'],$data['to']);
	//dd($data['QueryVoultReport']);
   	return view('Report.general_expenditure', $data);
   }
   

}//end class