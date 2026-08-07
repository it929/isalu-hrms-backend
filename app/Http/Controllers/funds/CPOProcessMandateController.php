<?php

namespace App\Http\Controllers\funds;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use Carbon\Carbon;
use Entrust;
use Session;
use Excel;
use Input;
use DB;
use QrCode; 


class CPOProcessMandateController extends function24Controller
{
    
    public function __construct()
    {
       
    }


    public function MyAssignedLiability(Request $request) 
	{
	
	    $data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
	  if( isset( $_POST['process'] )){
       	  $id = $request['vid'];
       	   $lid = $request['lid'];
	  		$Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  		$voultbal =$this->VoultBalance($Vdetails->economicCodeID);
	  		$selectliability=DB::select("SELECT IFNULL( sum(`amount`),0) as tsum FROM `tblliability_taken` WHERE `id`='$lid' and `is_cleared`=0 ")[0]->tsum ;
	  		if($selectliability>0 and (floor($selectliability)!=floor($Vdetails->totalPayment))) return back()->with('err','There is variance in the initial liability and actual voucher. Hence voucher not pass!');
	  		if(floor($voultbal+floor($selectliability)-floor($this->OutstandingLiabilityNew($Vdetails->economicCodeID))) < floor($Vdetails->totalPayment))
	  		{$data['error'] = "Insufficient Vote Balance!!! Liability cannot be cleared for this transaction";}
	  		else{
	  		$is_special =DB::table('tblpaymentTransaction')->where('ID', $id)->value('is_special');
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'liabilityBy' 		=> Auth::user()->id,
	  			'liabilityStatus' 		=> 1,
	  			'vstage' 		=> ($is_special==1)? 4 : 2,
	  			'checkbyStatus' 		=> ($is_special==1)? 1 : 0,
	  			'auditStatus' 		=> ($is_special==1)? 1 : 0,
	  			'status' 		=> 2,
	  			'isrejected' 		=> 0,
	  			'is_archive' 		=> 0,
	  			'liability_ref'=>($request['lid'])? $request['lid']: 0,
	  			'dateTakingLiability' 	=>    $this->ProcessDATE($Vdetails->economicCodeID) ,// '2020-12-01',//date('Y-m-j')
	  		])){
	  		    DB::table('tblliability_taken')->where('id', $request['lid'])->update([
	  			'clear_by' 		    => Auth::user()->id,
	  			'status' 		    => 0,
	  			'ref_voucher_id'    => $id,
	  			'status' 		=> 1,
	  			'is_cleared' =>1,
	  			'time_cleared' => $this->ProcessDATE($Vdetails->economicCodeID) ,//'2020-12-31',//tobe deleted'time_cleared' 	=>     date('Y-m-d h:i:s')
	  			]);
	  			$remark=$Vdetails->paymentDescription;
	  			$usr = Auth::user()->name;
	  			$this->VotebookUpdate($Vdetails->economicCodeID,$Vdetails->ID,$remark,$Vdetails->totalPayment,Date('Y-m-d'),2);
	  			$comment = trim($request['comment']).": Liability cleared and passed for checking by ". Auth::user()->name;
	  		    DB::table('tblcomments')->insert(['commenttypeID' => 2, 'affectedID' => $Vdetails->contractID,'paymentID' => 0, 'username' => Auth::user()->username, 'comment' => $comment]);
	  			$data['success'] = "Voucher Liability successfully cleared and passed to Head, Expenditure Control for final clearance";
	  			$taskscheduled=$this->UpdateAlertTable("Voucher Clearance",'voucher/liability-2','0','HEC','tblpaymentTransaction',$id,1);
	  			$this->addLogg("Liability for voucher with ID: $id and Description: $Vdetails->paymentDescription taken and moved to checking by $usr","Libility Taken");
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	  		}
	  		
	   	} 
                
               if( isset( $_POST['decline'] )){
               	  $id = $request['vid'];
               	  $this->validate($request, [
        		    'vid'       => 'required'
        		    ,'comment'  => 'required'
        		    ]); 		
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'liabilityStatus' 	=> 0
	  			,'vstage' 		=> 0,
	  			'isrejected' 		=> 1
	  			,'status' 		=> 0,
	  		])){
	  			$theid = $id;
	  		        $user = Auth::user()->username;
	  		        $commenttypeID = 2;
	  		        $Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  		       $comment = trim($request['comment']).": Rejected by ". Auth::user()->name." at funds clearance stage";
	  		        $cid=$Vdetails->contractID;
	  		        DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID,'affectedID' => $cid,'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
	  		        $taskscheduled=$this->UpdateAlertTable("Voucher Rejection",'occheckby/voucher','0','OC','tblpaymentTransaction',$id,1);  
	  			$data['success'] = "Voucher has been Declined successfully!";
	  			$this->addLogg("Voucher with ID: $id and Description: $Vdetails->paymentDescription by $usr Reason: $comment","Voucher Rejected");
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	   	}
	   	
	   	if( isset( $_POST['switch'] )){
               	  $id = $request['vid'];
               	  $this->validate($request, [
        		    'vid'       => 'required',
        		    'economiccode'       => 'required'
        		    ]); 		
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'economicCodeID' 	=> $request['economiccode'],
	  			'contractTypeID' 	=> DB::table('tbleconomicCode')->where('ID', $request['economiccode'])->value('contractGroupID')
	  		])){
	  			$theid = $id;
	  		        $user = Auth::user()->username;
	  		        $commenttypeID = 2;
	  		        $Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  		       $comment = "Ecomonmic code changed by ". Auth::user()->name. "to ". $request['economiccode'] ;
	  		        $cid=$Vdetails->contractID;
	  		    DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID,'affectedID' => $cid,'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
	  		    $taskscheduled=$this->UpdateAlertTable("Voucher Rejection",'occheckby/voucher','0','OC','tblpaymentTransaction',$id,0);   
	  			$data['success'] = "Voucher has been Declined successfully!";
	  			$this->addlogg("Voucher ID: $id $comment","Voucher with ID: $id");
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	   	}
   $data['econocodeList'] = $this->AllEconomicsCode();
    $data['tablecontent'] = $this->VourcherGroupWithBalances($this->MyUnFundClearance(Auth::user()->id));
	    return view('Liability.newform', $data);
	}
	public function LiabilityFinalClearance(Request $request) 
	{
	
	    $data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
	  if( isset( $_POST['process'] )){
       	  $id = $request['vid'];
	  		$Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  		$voultbal =$this->VoultBalance($Vdetails->economicCodeID);
	  	//	if(floor($voultbal+floor($this->UnclearedLiability($Vdetails->FileNo))) < floor($Vdetails->totalPayment))
	  	//	{$data['error'] = "Insufficient Vote Balance!!! Liability cannot be cleared for this transaction";}
	  	//	else{
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'vstage' 		=> 2,
	  		])){
	  			$remark=$Vdetails->paymentDescription;
	  			$usr = Auth::user()->name;
	  			$comment = trim($request['comment']).": Voucher passed for checking by ". Auth::user()->name;
	  		    DB::table('tblcomments')->insert(['commenttypeID' => 2, 'affectedID' => $Vdetails->contractID,'paymentID' => 0, 'username' => Auth::user()->username, 'comment' => $comment]);
	  			$data['success'] = "Voucher Liability successfully cleared and passed to checking for further processing!";
	  			$taskscheduled=$this->UpdateAlertTable("Voucher Clearance",'pre-check','0','HC','tblpaymentTransaction',$id,1);
	  			$this->addLogg("Liability for voucher with ID: $id and Description: $Vdetails->paymentDescription taken and moved to checking by $usr","Libility Taken");
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	  		//}
	  		
	   	} 
                
               if( isset( $_POST['decline'] )){
               	  $id = $request['vid'];
               	  $this->validate($request, [
        		    'vid'       => 'required'
        		    ,'comment'  => 'required'
        		    ]); 		
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'liabilityStatus' 	=> 0
	  			,'vstage' 		=> 0,
	  			'isrejected' 		=> 1
	  			,'status' 		=> 0,
	  		])){
	  			$theid = $id;
	  		        $user = Auth::user()->username;
	  		        $commenttypeID = 2;
	  		        $Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  		       $comment = trim($request['comment']).": Rejected by ". Auth::user()->name." at funds clearance stage";
	  		        $cid=$Vdetails->contractID;
	  		        DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID,'affectedID' => $cid,'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
	  		        $taskscheduled=$this->UpdateAlertTable("Voucher Rejection",'occheckby/voucher','0','OC','tblpaymentTransaction',$id,1);  
	  			$data['success'] = "Voucher has been Declined successfully!";
	  			$this->addLogg("Voucher with ID: $id and Description: $Vdetails->paymentDescription by $usr Reason: $comment","Voucher Rejected");
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	   	}
   
    $data['tablecontent'] = $this->VourcherGroupWithBalances($this->ECfinalClearance());
	    return view('Liability.liabilityfinalclearance', $data);
	}
	
	//
	public function PreLiability(Request $request) 
	{
	
	//=============search by date=================
   	    $getYear    =  Session::get('getYear');
		$getFrom    =  Session::get('getFrom');
		$getTo      =  Session::get('getTo');
		//year
		if($getYear == '' or $getYear < 1){
			$getSearchYear = date('Y');
		}else{
			$getSearchYear = $getYear;
		}
		//date (Current days in the year: $dayNumber = date("z") + 1;)
		if($getFrom == '' or $getTo == ''){
			$getSearchFrom = date('Y-01-01');
			$getSearchTo   = date('Y-m-d');
		}else{
			$getSearchFrom = $getFrom;
			$getSearchTo   = $getTo;
		}
		//pass data back
		$data['getYear'] = $request['getYear'];
		$data['getFrom'] = $request['getFrom'];
		$data['getTo']   = $request['getTo'];
	//============end search by date===============
	
	    $data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
	    if(DB::table('tblpaymentTransaction')->where('ID', $request['vid'])
				->update(['liabilityBy'    => $request['as_user'],])){
				 $taskscheduled=$this->UpdateAlertTable("Voucher Clearance",'voucher/liability',$request['as_user'],'0','tblpaymentTransaction',$request['vid'],1); 			  
		}
	  if( isset( $_POST['process'] )){
       	  $id = $request['vid'];
       	   $lid = $request['lid'];
	  		$Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  		$voultbal =$this->VoultBalance($Vdetails->economicCodeID);
	  		$selectliability=DB::select("SELECT IFNULL( sum(`amount`),0) as tsum FROM `tblliability_taken` WHERE `id`='$lid' and `is_cleared`=0 ")[0]->tsum ;
	  		if($selectliability>0 and (floor($selectliability)!=floor($Vdetails->totalPayment))) return back()->with('err','There is variance in the initial liability and actual voucher. Hence voucher not pass!');
	  		if(floor($voultbal+floor($selectliability)-floor($this->OutstandingLiabilityNew($Vdetails->economicCodeID))) < floor($Vdetails->totalPayment))
	  		{$data['error'] = "Insufficient Vote Balance!!! Liability cannot be cleared for this transaction";}
	  		else{
	  		    //dd($this->ProcessDATE($Vdetails->economicCodeID));
	  		$is_special =DB::table('tblpaymentTransaction')->where('ID', $id)->value('is_special');
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'liabilityBy' 		=> Auth::user()->id,
	  			'liabilityStatus' 		=> 1,
	  			'vstage' 		=> ($is_special==1)? 4 : 2,
	  			'checkbyStatus' 		=> ($is_special==1)? 1 : 0,
	  			'auditStatus' 		=> ($is_special==1)? 1 : 0,
	  			'status' 		=> 2,
	  			'isrejected' 		=> 0,
	  			'is_archive' 		=> 0,
	  			'liability_ref'=>($request['lid'])? $request['lid']: 0,
	  			'dateTakingLiability' 	=>   $this->ProcessDATE($Vdetails->economicCodeID) ,//  '2020-12-31',// date('Y-m-j')
	  		])){
	  		    DB::table('tblliability_taken')->where('id', $request['lid'])->update([
	  			'clear_by' 		    => Auth::user()->id,
	  			'status' 		    => 0,
	  			'ref_voucher_id'    => $id,
	  			'status' 		=> 1,
	  			'is_cleared' =>1,
	  			'time_cleared' 	=>     $this->ProcessDATE($Vdetails->economicCodeID) ,// date('Y-m-d h:i:s')
	  			]);
	  			$remark=$Vdetails->paymentDescription;
	  			$this->VotebookUpdate($Vdetails->economicCodeID,$Vdetails->ID,$remark,$Vdetails->totalPayment,Date('Y-m-d'),2);
	  			$comment = trim($request['comment']).": Liability cleared and passed for checking by ". Auth::user()->name;
	  		    DB::table('tblcomments')->insert(['commenttypeID' => 2, 'affectedID' => $Vdetails->contractID,'paymentID' => 0, 'username' => Auth::user()->username, 'comment' => $comment]);
	  			$data['success'] = "Voucher Liability successfully cleared and passed to checking for further processing!";
	  			$taskscheduled=$this->UpdateAlertTable("Voucher Clearance",'pre-check','0','HC','tblpaymentTransaction',$id,1);
	  			$this->addlogg("Liability Taken for Voucher with ID: $id and Payment Description:$Vdetails->paymentDescription  moved to Checking for further processing!","Liability taken for Voucher with ID: $id");
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	  		}
	  		
	   	} 
                
               if( isset( $_POST['decline'] )){
               	  $id = $request['vid'];
               	  $this->validate($request, [
        		    'vid'       => 'required'
        		    ,'comment'  => 'required'
        		    ]); 		
        		    //pitoff
			$getContractType = DB::table('tblpaymentTransaction')->where('ID', $id)->value('contractTypeID');
			if($getContractType == 6){
				$declineVstage = 2;
			}else{
				$declineVstage = 0;
			}
			//pitoff
			
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'liabilityStatus' 	=> 0
	  			,'vstage' 		=> $declineVstage
	  			,'status' 		=> 0,
	  			'isrejected' 		=> 1,
	  		])){
	  			$theid = $id;
	  		        $user = Auth::user()->username;
	  		        $commenttypeID = 2;
	  		        $Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  		       $comment = trim($request['comment']).": Rejected by ". Auth::user()->name." at funds clearance stage";
	  		        $cid=$Vdetails->contractID;
	  		    DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID,'affectedID' => $cid,'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
	  		    $taskscheduled=$this->UpdateAlertTable("Voucher Rejection",'occheckby/voucher','0','OC','tblpaymentTransaction',$id,1);   
	  			$data['success'] = "Voucher has been Declined successfully!";
	  			$this->addlogg("Voucher with ID: $id Rejected with reason: $comment","Voucher with ID: $id Rejected");
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	   	}
	   	
	   	if( isset( $_POST['switch'] )){
               	  $id = $request['vid'];
               	  $this->validate($request, [
        		    'vid'       => 'required',
        		    'economiccode'       => 'required'
        		    ]); 		
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'economicCodeID' 	=> $request['economiccode'],
	  			'contractTypeID' 	=> DB::table('tbleconomicCode')->where('ID', $request['economiccode'])->value('contractGroupID')
	  		])){
	  			$theid = $id;
	  		        $user = Auth::user()->username;
	  		        $commenttypeID = 2;
	  		        $Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  		       $comment = "Ecomonmic code changed by ". Auth::user()->name. "to ". $request['economiccode'] ;
	  		        $cid=$Vdetails->contractID;
	  		    DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID,'affectedID' => $cid,'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
	  		    $taskscheduled=$this->UpdateAlertTable("Voucher Rejection",'occheckby/voucher','0','OC','tblpaymentTransaction',$id,0);   
	  			$data['success'] = "Voucher has been Declined successfully!";
	  			$this->addlogg("Voucher ID: $id $comment","Voucher with ID: $id");
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	   	}
    $data['UnitStaff']=$this->UnitStaff('EC');
    $data['tablecontent'] = $this->VourcherGroupWithBalances($this->UnFundClearance($getSearchFrom, $getSearchTo, $getSearchYear));
    $data['econocodeList'] = $this->AllEconomicsCode();
	    return view('Liability.preliability', $data);
	}
	
	
	
	//
	public function VoteSwitch(Request $request) 
	{
	
	    $data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
	     $data['fromdate'] = $request['fromdate'];
		$data['todate'] = $request['todate'];
		 if($data['fromdate']=='') {$data['fromdate']=Carbon::now()->format('Y-m-d');}
		  if($data['todate']=='') {$data['todate']=Carbon::now()->format('Y-m-d');}
	   	
	   	if( isset( $_POST['switch'] )){
               	  $id = $request['vid'];
               	  $this->validate($request, [
        		    'vid'       => 'required',
        		    'economiccode'       => 'required'
        		    ]); 		
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'economicCodeID' 	=> $request['economiccode'],
	  			'contractTypeID' 	=> DB::table('tbleconomicCode')->where('ID',$request['economiccode'])->value('contractGroupID')
	  		])){
	  			$theid = $id;
	  		        $user = Auth::user()->username;
	  		        $commenttypeID = 2;
	  		        $Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  		       $comment = "Ecomonmic code changed by ". Auth::user()->name. "to ". $request['economiccode'] ;
	  		        $cid=$Vdetails->contractID;
	  		    DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID,'affectedID' => $cid,'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
	  		      
	  		
	  		} else {
	  			$data['error'] = "Whoops! Nothing change";
	  		}
	   	}
   
    $data['tablecontent'] = $this->VourcherGroupWithBalances($this->ClearedTransaction($data['fromdate'],$data['todate']));
    $data['econocodeList'] = $this->AllEconomicsCode();
    //dd($data['tablecontent']);
	    return view('Liability.clearedlog', $data);
	}
public function FinalApproval(Request $request) 
	{
	
	    $data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
	  	if($request['reason'] == 1){
	  		$id = $request['paymentTransID'];
	  		$id = $request['paymentTransID'];
	  		$Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  		$voultbal =$this->VoultBalance($Vdetails->economicCodeID);
	  		if(floor($voultbal+floor($this->UnclearedLiability($Vdetails->FileNo))) < floor($Vdetails->totalPayment))
	  		{$data['error'] = "Insufficient Vote Balance!!! Liability cannot be cleared for this transaction";}
	  		else{
	  		DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'f_commitment' 		=> 1,
	  		]);
	  		}
	   	} 
	   	$data['tablecontent'] = DB::table('tblpaymentTransaction')
		->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
		->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
		->leftjoin('tblallocation_type', 'tblpaymentTransaction.allocationType', '=', 'tblallocation_type.ID')
		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
		->where('tblpaymentTransaction.vstage', 2)
		->where('tblpaymentTransaction.status', 2)
		->where('tblpaymentTransaction.f_commitment', 0)->select('tblpaymentTransaction.*', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tblallocation_type.allocation', 'tblcontractDetails.contractValue', 'tblcontractDetails.file_ex','tblcontractDetails.beneficiary','tblcontractDetails.voucherType', 'tblcontractDetails.ContractDescriptions', DB::raw('tblcontractDetails.ID AS conID'))
		->orderBy('liabilityStatus', 'asc')
		->orderBy('dateAward', 'asc')
		->get();
	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
	    	$whtpayee = ($value->WHTPayeeID == "") ? "" : DB::table('tblVATWHTPayee')->where('ID', $value->WHTPayeeID)->first()->payee;
	    	$vatpayee = ($value->VATPayeeID == "") ? "" : DB::table('tblVATWHTPayee')->where('ID', $value->VATPayeeID)->first()->payee;
	    	$lis['whtpayee'] = $whtpayee;
	    	$lis['vatpayee'] = $vatpayee;
	    	$lis['votebal'] = $this->VoultBalance($value->economicCodeID);
	    	$lis['OutstandingLiability'] = $this->UnclearedLiability($value->FileNo);
	    	$voteinfo = $this->VoteInfo($value->economicCodeID);
	    	$lis['voteinfo']=$voteinfo->description;
	    	$com = DB::table('tblcomments')->where('affectedID', $value->ID)->orwhere('affectedID', $value->contractID)->get();	    	
	    	$lis['comments'] = "";
	    	$lis['comments2']='';
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
	        	$lis['comments'] = json_encode($com);
	        }
	        $com2 = DB::table('contract_comment')->where('fileNoID', $value->FileNo)->orderby('commentID', 'asc')->get();
	        if($com2){
	        	foreach($com2 as $k => $list){
	        		//$newline = (array) $list;
	        		$newline = (array) [];
	        		$name = DB::table('users')->where('id', $list->userID)->first()->name;
	        		$newline['name'] = $name;
				$newline['comment'] = str_replace("\r\n", "<br>", $list->comment);
	        		$date = strtotime($list->date);
	        		$newline['date_added'] = date("F j, Y", $date);
	        		$newline['time'] = date("g:i a", $date);
	        		$newline = (object) $newline;
	        		$com2[$k] = $newline;
	        	}
	        	$lis['comments2'] = json_encode($com2);
	        } 
	    	$value = (object) $lis;
	    	$data['tablecontent'][$key]  = $value;
	    }
	    return view('Liability.voucher_clarance', $data);
	}

	public function check(Request $request){
		$data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
	    $data['tablecontent'] = DB::table('tblpaymentTransaction')
	    						->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
	    						->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    						->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    						->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
	    						->leftjoin('tblallocation_type', 'tblpaymentTransaction.allocationType', '=', 'tblallocation_type.ID')
	    						->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    						->where('tblpaymentTransaction.preparedBy', Auth::user()->id)
	    						->select('tblpaymentTransaction.*', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tblallocation_type.allocation', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions', DB::raw('tblcontractDetails.ID AS conID'))
	    						->orderBy('liabilityStatus', 'asc')->orderBy('dateCreated', 'asc')->orderBy('dateAward', 'asc')
	    						->get();
	    
	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
	    	$lis['comment'] = "";
	    	$com = DB::table('tblcomments')->where('affectedID', $value->ID)->orwhere('affectedID', $value->contractID)->get();	    	
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
	    	$data['tablecontent'][$key]  = $value;
	    }
	   //dd($data['tablecontent']);
	    return view('Liability.check', $data);
	}
    
    //Other charges - list voucher
	public function editableVoucher(Request $request){
		$data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
	    $data['tablecontent'] = DB::table('tblpaymentTransaction')
		->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
		->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
		->leftjoin('tblallocation_type', 'tblpaymentTransaction.allocationType', '=', 'tblallocation_type.ID')
		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
		->leftjoin('tblvoucherstages', 'tblvoucherstages.id', '=', 'tblpaymentTransaction.vstage')
		->Where('tblpaymentTransaction.vstage','<', 1)
		->Where('tblpaymentTransaction.is_archive','=', 0)
		->Where('tblpaymentTransaction.is_restore','=', 0)
		->select('tblpaymentTransaction.*', 'tblpaymentTransaction.ID as payTranID', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tblallocation_type.allocation', 'tblcontractDetails.contractValue', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions','tblcontractDetails.beneficiary','tblcontractDetails.voucherType', 'tblvoucherstages.unit', DB::raw('tblcontractDetails.ID AS conID'))
		->orderBy('checkbyStatus', 'asc')
		->orderBy('dateAward', 'asc')
		->get();
	    return view('Liability.pending', $data);
	}
	
	//Process Rejected Voucher by other charges back to other unit
	public function processRejectedVoucher(Request $request)
	{
	    //dd('under maintenance...');
	    
	     DB::table('tblpaymentTransaction')
            ->where('ID', '=', $request['transid'] )
            ->update(array( 
                'vstage'            => $request['attension'],
                'returnstatus'      => 0,  
                'liabilityStatus'   => 1,
                'checkbyStatus'     => 1,
                'auditStatus'       => 1,
                'status'            => ($request['attension'] == 1 ? 0 : 2),
                'isrejected'        => 0,
           ));
            return redirect()->back()->with('msg','Successfully Send back.');
    }
	
	
	
	//Checking unit
	public function checkbypage(Request $request){
		//dd(Auth::user());
		$data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';


if( isset( $_POST['process'] )){
       	  $id = $request['vid'];
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'checkbyStatus' 	=> 1,
	  			'status'       		=> 2
	  			,'vstage' 		    => 3
	  			,'dateCheck' 		=> date('Y-m-j'),
	  			'isrejected' 		=> 0,
	  			'checkBy'			=> Auth::user()->id
	  			,'is_need_more_doc' => 0
	  		])){
	  		    $theVoucher = DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  			$conID = DB::table('tblpaymentTransaction')->where('ID', $id)->first()->contractID;
	  			DB::table('tblcontractDetails')->where('ID', $conID)->update([
	  				'paymentStatus' => 4
	  			]);
	  			$comment = trim($request['comment']).": checked and cleared  by ". Auth::user()->name." at checking stage";
	  		   DB::table('tblcomments')->insert(['commenttypeID' => 2, 'affectedID' => $conID,'paymentID' => 0, 'username' => Auth::user()->username, 'comment' => $comment]);
	  			$data['success'] = "Voucher has successfully been passed to Audit for further processing!";
	  			$usr = Auth::user()->name;
	  			addLogg("Voucher with file Number:  $theVoucher->fileNo and Description:  $theVoucher->paymentDescription Check and Passed to Audit by $usr","Voucher Moved to Audit");
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
       	    
       	}
       	if( isset( $_POST['decline'] )){
       	  $id = $request['vid'];
       	  $this->validate($request, [
		    'vid'       => 'required'
		    ,'comment'  => 'required'
		    ]);
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'checkbyStatus' 		=> 2,
	  			'dateCheck' 			=> '',
	  			'checkBy'				=> ''
	  			,'liabilityStatus' 	=> 0
	  			,'vstage' 		=> 0
	  			,'isrejected' 		=> 1
	  			,'status' 		=> 0,
	  		])){
	  		    $theVoucher = DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  			$Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  			$remark= $Vdetails->paymentDescription. " Rejected for ". $request['comment']." at checking stage by: ". Auth::user()->name ;
	  			$this->VotebookUpdate($Vdetails->economicCodeID,$Vdetails->ID,$remark,$Vdetails->totalPayment,Date('Y-m-d'),5);
	  			$data['success'] = "Voucher has been rejected successfully";
	  			$theid =$id;
	  			$Vdetails->contractID;
	  		        $user = Auth::user()->username;
	  		        $commenttypeID = 2;
	  		        $comment = trim($request['comment']).": Rejected by ". Auth::user()->name." at checking stage";
	  		        
	  		        DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $Vdetails->contractID,'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
	  		    $usr = Auth::user()->name;
	  			addLogg("Voucher with file Number:  $theVoucher->fileNo and Description:  $theVoucher->paymentDescription Rejected by $usr","Voucher Rejected");
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}  
       	}
       		if( isset( $_POST['moredocument'] )){
       	  $id = $request['vid'];
       	  $this->validate($request, [
		    'vid'       => 'required'
		    ,'comment'  => 'required'
		    ]); 
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'is_need_more_doc' => 1,
	  		])){
	  			$Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  			$remark= $Vdetails->paymentDescription. " Rejected for ". $request['comment']." at auditing stage by: ". Auth::user()->name ;
	  			$data['success'] = "Voucher has been for additional document";
	  			$theid =$id;
	  			$Vdetails->contractID;
	  		        $user = Auth::user()->username;
	  		        $commenttypeID = 2;
	  		        $comment = trim($request['comment']).": queried by ". Auth::user()->name." at checking stage";
	  		        DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $Vdetails->contractID,'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
	  		         $taskscheduled=$this->UpdateAlertTable("Queried Voucher",'queried/voucher','0','OC','tblpaymentTransaction',$id,1);  
	  		        $this->addLogg("Voucher with ID: $id queried with reason:$comment","Voucher with ID: $id queried");
	  		        
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}  
       	}
	  
	   	$data['tablecontent'] = $this->VourcherGroup($this->MyUnChecked(Auth::user()->id));
	    return view('Liability.checking', $data);
	}

	public function Pre_Vericfication(Request $request){
		$data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
	  	if($request['reason'] == 1){
	  		$id = $request['paymentTransID'];
	  		
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update(['vstage' => 0])){
	  		    $this->addlogg("Voucher with ID $id Moved to Head, Other Charges","Voucher with ID $id to be cleared to Expenditure Control");
	  			$data['success'] = "Voucher Passed to Head, Other Charges for final Clearance!";
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	   	}

if( isset( $_POST['clear'] )){
	  		$id = $request['clearid'];
	  		$cid=DB::table('tblpaymentTransaction')->where('ID', $id)->value('contractID');
	  		$comment = trim($request['remark']).": comment by ". Auth::user()->name." at other charges";
	  		if(trim($request['remark'])=='') $comment =  "Reviewed and passed  by ". Auth::user()->name." to head other charges for final clearence to expenditure control";
	  		DB::table('tblcomments')->insert(['commenttypeID' => 1, 'affectedID' => $cid,'paymentID' => $id, 'username' => Auth::user()->username, 'comment' => $comment]);
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update(['vstage' => 0,'isrejected' 	=> 0])){
	  		    $taskscheduled=$this->UpdateAlertTable("Voucher Clearance",'pre-liabilty','0','HEC','tblpaymentTransaction',$id,1);
	  		    $this->addlogg("Voucher with ID $id Moved to Head, Other Charges","Voucher with ID $id to be cleared to Expenditure Control");
	  			$data['success'] = "Voucher Passed to Expenditure Control!";
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	  		return back()->with('msg','Voucher Passed to Head, Other charges for final clearance!');
	   	}

	   	if( isset( $_POST['reject'] )){
	   		$id = $request['chosen1'];
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->first()){
	  		     $this->addlogg("Voucher with ID $id Rejected","Voucher with ID: $id Rejected");
	  			$Vdetails=DB::table('tblcontractDetails')->where('ID', DB::table('tblpaymentTransaction')->where('ID', $id)->value('contractID'))->first();
	  			$data['success'] = "Voucher has been rejected successfully";
	  			$theid =$id;
	  			$cid=$Vdetails->ID;
	  		    $user = Auth::user()->username;
	  		    $commenttypeID =2;
	  		   $comment = trim($request['declinemess']).": Rejected by ".Auth::user()->username."";
	  		   DB::table('tblcontractDetails')->where('ID', $cid)->update([
	  			'awaitingActionby' 	=> $Vdetails->approval_last_action_by,
	  			'openclose'			=> 0
	  		    ,'approvalStatus' 	=> 0
	  		    ,'isrejected' 	=> 1
	  		    ,'OC_staffId' => null
	  		]);
	  		        $taskscheduled=$this->UpdateAlertTable("Rejected task",'procurement/approve','',$Vdetails->approval_last_action_by,'tblcontractDetails',$cid,1);
	  		        DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $cid,'paymentID' => $theid, 'username' => $user, 'comment' => $comment]);
	  		        DB::table('tblpaymentTransaction')->where('ID', $id)->update(['is_archive'=>1]);
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	   	}
	   	$data['tablecontent'] = DB::table('tblpaymentTransaction')
			->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
			->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
			->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
			->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
			->leftjoin('tblallocation_type', 'tblpaymentTransaction.allocationType', '=', 'tblallocation_type.ID')
			->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
			->where('tblpaymentTransaction.is_archive', 0)
			->where('tblpaymentTransaction.vstage', -1)->where('tblpaymentTransaction.is_restore', 0)->where('tblpaymentTransaction.is_advances', 0)
			->select('tblpaymentTransaction.*', 'tbleconomicCode.description as ecotext',
			'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode'
			, 'tblallocation_type.allocation', 'tblcontractDetails.contractValue', 'tblcontractDetails.dateAward'
			, 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions','tblcontractDetails.beneficiary'
			,'tblcontractDetails.voucherType', DB::raw('tblcontractDetails.ID AS conID'))
			->orderBy('checkbyStatus', 'asc')->orderBy('dateAward', 'asc')->get();
	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
	    	$value = (object) $lis;
	    	$data['tablecontent'][$key]  = $value;
	    }
	    return view('Liability.voucher_verication', $data);
	}
	public function OCclearance(Request $request){
		$data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
	  	if($request['reason'] == 1){
	  		$id = $request['paymentTransID'];
	  		
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update(['vstage' => 1])){
	  		    $this->addlogg("Voucher with ID $id Moved to Expenditure Control","Voucher with ID $id Moved to Expenditure Control");
	  			$data['success'] = "Voucher Passed to Expenditure Control!";
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	   	}

if( isset( $_POST['clear'] )){
	  		$id = $request['clearid'];
	  		$cid=DB::table('tblpaymentTransaction')->where('ID', $id)->value('contractID');
	  		$comment = trim($request['remark']).": comment by ". Auth::user()->name." at other charges";
	  		if(trim($request['remark'])=='') $comment =  "Reviewed and passed  by ". Auth::user()->name." at other charges";
	  		DB::table('tblcomments')->insert(['commenttypeID' => 1, 'affectedID' => $cid,'paymentID' => $id, 'username' => Auth::user()->username, 'comment' => $comment]);
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update(['vstage' => 1,'isrejected' 	=> 0])){
	  		    $taskscheduled=$this->UpdateAlertTable("Voucher Clearance",'pre-liabilty','0','HEC','tblpaymentTransaction',$id,1);
        	   
	  		    $this->addlogg("Voucher with ID $id Moved to Expenditure Control","Voucher with ID $id Moved to Expenditure Control");
	  			$data['success'] = "Voucher Passed to Expenditure Control!";
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	  		return back()->with('msg','Voucher Passed to Expenditure Control!');
	   	}

	   	if( isset( $_POST['reject'] )){
	   	    //dd("ksjsjsj");
	   		$id = $request['chosen1'];
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->first()){
	  		     $this->addlogg("Voucher with ID $id Rejected","Voucher with ID: $id Rejected");
	  			//$Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  			$Vdetails=DB::table('tblcontractDetails')->where('ID', DB::table('tblpaymentTransaction')->where('ID', $id)->value('contractID'))->first();
	  			$data['success'] = "Voucher has been rejected successfully";
	  			$theid =$id;
	  			$cid=$Vdetails->ID;
	  		    $user = Auth::user()->username;
	  		    $commenttypeID =2;
	  		   $comment = trim($request['declinemess']).": Rejected by ".Auth::user()->username."";
	  		   DB::table('tblcontractDetails')->where('ID', $cid)->update([
	  			'awaitingActionby' 	=> $Vdetails->approval_last_action_by,
	  			'openclose'			=> 0
	  		    ,'approvalStatus' 	=> 0
	  		    ,'isrejected' 	=> 1
	  		    ,'OC_staffId' => null
	  		]);
	  		        $taskscheduled=$this->UpdateAlertTable("Rejected task",'procurement/approve','',$Vdetails->approval_last_action_by,'tblcontractDetails',$cid,1);
	  		        DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $cid,'paymentID' => $theid, 'username' => $user, 'comment' => $comment]);
	  		        //DB::table('tblpaymentTransaction')->where('ID', $id)->delete();
	  		        DB::table('tblpaymentTransaction')->where('ID', $id)->update(['is_archive'=>1]);
	  		        //dd($id);
	  		       
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	   	}

	   	$data['tablecontent'] = DB::table('tblpaymentTransaction')
	    						->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
	    						->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    						->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    						->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
	    						->leftjoin('tblallocation_type', 'tblpaymentTransaction.allocationType', '=', 'tblallocation_type.ID')
	    						->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    						->where('tblpaymentTransaction.is_archive', 0)
	    						->where('tblpaymentTransaction.vstage', 0)->where('tblpaymentTransaction.is_restore', 0)->where('tblpaymentTransaction.is_advances', 0)
	    						 //->where(function($query) {
            //                      $query->where('tblpaymentTransaction.vstage', -1)
            //                      ->orwhere('tblpaymentTransaction.vstage', 0);
            //                       })
	    						->select('tblpaymentTransaction.*', 'tbleconomicCode.description as ecotext', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tblallocation_type.allocation', 'tblcontractDetails.contractValue', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions','tblcontractDetails.beneficiary','tblcontractDetails.voucherType', DB::raw('tblcontractDetails.ID AS conID'))
	    						->orderBy('checkbyStatus', 'asc')->orderBy('dateAward', 'asc')->get();
	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
	    	$value = (object) $lis;
	    	$data['tablecontent'][$key]  = $value;
	    }
	    return view('Liability.occhecking', $data);
	}
	public function DocClearance(Request $request){
		$data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
	  
if( isset( $_POST['clear'] )){
	  		$id = $request['clearid'];
	  		$cid=DB::table('tblpaymentTransaction')->where('ID', $id)->value('contractID');
	  		$comment = trim($request['remark']).": comment by ". Auth::user()->name." at other charges";
	  		if(trim($request['remark'])=='') $comment =  "Queried review passed  by ". Auth::user()->name." for further action";
	  		DB::table('tblcomments')->insert(['commenttypeID' => 1, 'affectedID' => $cid,'paymentID' => $id, 'username' => Auth::user()->username, 'comment' => $comment]);
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update(['is_need_more_doc' 	=> 0])){
	  		    $taskscheduled=$this->UpdateAlertTable("Voucher Clearance",'pre-liabilty','0','HEC','tblpaymentTransaction',$id,0);
	  		    $this->addlogg("Voucher with ID $id $comment","Voucher with ID $id $comment");
	  			$data['success'] = "Voucher Passed for further processing!";
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	  		return back()->with('msg','Voucher Passed  for further processing!');
	   	}
	   	if( isset( $_POST['reject'] )){
	   		$id = $request['chosen1'];
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->first()){
	  		     $this->addlogg("Voucher with ID $id Rejected","Voucher with ID: $id Rejected");
	  			$Vdetails=DB::table('tblcontractDetails')->where('ID', DB::table('tblpaymentTransaction')->where('ID', $id)->value('contractID'))->first();
	  			$data['success'] = "Voucher has been rejected successfully";
	  			$theid =$id;
	  			$cid=$Vdetails->ID;
	  		    $user = Auth::user()->username;
	  		    $commenttypeID =2;
	  		   $comment = trim($request['declinemess']).": Rejected by ".Auth::user()->username."";
	  		   DB::table('tblcontractDetails')->where('ID', $cid)->update([
	  			'awaitingActionby' 	=> $Vdetails->approval_last_action_by,
	  			'openclose'			=> 0
	  		    ,'approvalStatus' 	=> 0
	  		    ,'isrejected' 	=> 1
	  		    ,'OC_staffId' => null
	  		    ,'status' 		=> 0
	  		    ,'liabilityStatus' 	=> 0
	  		    ,'checkBy'				=> ''
	  		    ,'vstage' 		=> 0
	  		]);
	  		        $taskscheduled=$this->UpdateAlertTable("Rejected task",'procurement/approve','',$Vdetails->approval_last_action_by,'tblcontractDetails',$cid,1);
	  		        DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $cid,'paymentID' => $theid, 'username' => $user, 'comment' => $comment]);
	  		        DB::table('tblpaymentTransaction')->where('ID', $id)->update(['is_archive'=>1]);
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	   	}
	   	$data['tablecontent'] = DB::table('tblpaymentTransaction')
	    						->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
	    						->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    						->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    						->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
	    						->leftjoin('tblallocation_type', 'tblpaymentTransaction.allocationType', '=', 'tblallocation_type.ID')
	    						->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    						->where('tblpaymentTransaction.is_archive', 0)
	    						->where('tblpaymentTransaction.is_need_more_doc', 1)->where('tblpaymentTransaction.is_advances', 0)
	    						->select('tblpaymentTransaction.*', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tblallocation_type.allocation', 'tblcontractDetails.contractValue', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions','tblcontractDetails.beneficiary','tblcontractDetails.voucherType', DB::raw('tblcontractDetails.ID AS conID'))
	    						->orderBy('checkbyStatus', 'asc')->orderBy('dateAward', 'asc')->get();
	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
	    	$value = (object) $lis;
	    	$data['tablecontent'][$key]  = $value;
	    }
	    return view('Liability.documentclearance', $data);
	}
	
	public function OCclearanceAdvances(Request $request){
		$data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
	  	if($request['reason'] == 1){
	  		$id = $request['paymentTransID'];
	  		
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update(['vstage' => 1])){
	  		    $this->addlogg("Voucher with ID $id Moved to Expenditure Control","Voucher with ID $id Moved to Expenditure Control");
	  			$data['success'] = "Voucher Passed to Expenditure Control!";
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	   	}

if( isset( $_POST['clear'] )){
	  		$id = $request['clearid'];
	  		$cid=DB::table('tblpaymentTransaction')->where('ID', $id)->value('contractID');
	  		$comment = trim($request['remark']).": comment by ". Auth::user()->name." at other charges";
	  		if(trim($request['remark'])=='') $comment =  "Reviewed and passed  by ". Auth::user()->name." at advance unit";
	  		DB::table('tblcomments')->insert(['commenttypeID' => 1, 'affectedID' => $cid,'paymentID' => $id, 'username' => Auth::user()->username, 'comment' => $comment]);
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update(['vstage' => 1,'isrejected' 	=> 0])){
	  		    $taskscheduled=$this->UpdateAlertTable("Voucher Clearance",'pre-liabilty','0','HEC','tblpaymentTransaction',$id,1);
        	   
	  		    $this->addlogg("Voucher with ID $id Moved to Expenditure Control","Voucher with ID $id Moved to Expenditure Control");
	  			$data['success'] = "Voucher Passed to Expenditure Control!";
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	  		return back()->with('msg','Voucher Passed to Expenditure Control!');
	   	}

	   	if( isset( $_POST['reject'] )){
	   	    //dd("ksjsjsj");
	   		$id = $request['chosen1'];
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->first()){
	  		     $this->addlogg("Voucher with ID $id Rejected","Voucher with ID: $id Rejected");
	  			//$Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  			$Vdetails=DB::table('tblcontractDetails')->where('ID', DB::table('tblpaymentTransaction')->where('ID', $id)->value('contractID'))->first();
	  			$data['success'] = "Voucher has been rejected successfully";
	  			$theid =$id;
	  			$cid=$Vdetails->ID;
	  		    $user = Auth::user()->username;
	  		    $commenttypeID =2;
	  		   $comment = trim($request['declinemess']).": Rejected by ".Auth::user()->username."";
	  		   DB::table('tblcontractDetails')->where('ID', $cid)->update([
	  			'awaitingActionby' 	=> $Vdetails->approval_last_action_by,
	  			'openclose'			=> 0
	  		    ,'approvalStatus' 	=> 0
	  		    ,'isrejected' 	=> 1
	  		    ,'OC_staffId' => null
	  		]);
	  		        $taskscheduled=$this->UpdateAlertTable("Rejected task",'procurement/approve','',$Vdetails->approval_last_action_by,'tblcontractDetails',$cid,1);
	  		        DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $cid,'paymentID' => $theid, 'username' => $user, 'comment' => $comment]);
	  		        //DB::table('tblpaymentTransaction')->where('ID', $id)->delete();
	  		        DB::table('tblpaymentTransaction')->where('ID', $id)->update(['is_archive'=>1]);
	  		        //dd($id);
	  		       
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	   	}

	   	$data['tablecontent'] = DB::table('tblpaymentTransaction')
	    						->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
	    						->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    						->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    						->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
	    						->leftjoin('tblallocation_type', 'tblpaymentTransaction.allocationType', '=', 'tblallocation_type.ID')
	    						->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    						->where('tblpaymentTransaction.is_archive', 0)
	    						->where('tblpaymentTransaction.vstage', '<',1)->where('tblpaymentTransaction.is_restore', 0)->where('tblpaymentTransaction.is_advances', 1)
	    						 //->where(function($query) {
            //                      $query->where('tblpaymentTransaction.vstage', -1)
            //                      ->orwhere('tblpaymentTransaction.vstage', 0);
            //                       })
	    						->select('tblpaymentTransaction.*', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tblallocation_type.allocation', 'tblcontractDetails.contractValue', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions','tblcontractDetails.beneficiary','tblcontractDetails.voucherType', DB::raw('tblcontractDetails.ID AS conID'))
	    						->orderBy('checkbyStatus', 'asc')->orderBy('dateAward', 'asc')->get();

	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
	    	$value = (object) $lis;
	    	$data['tablecontent'][$key]  = $value;
	    }

	    
	    return view('Liability.advancesclearance', $data);
	}

public function Auditcheck(Request $request){
		$data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
	  if( isset( $_POST['process'] )){
       	  $id = $request['vid'];
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'auditStatus' 		=> 1,
	  			'status'       			=> 2
	  			,'vstage' 		=> 4
	  			,'isrejected' 		=> 0
	  			,'auditDate' 			=> date('Y-m-j'),
	  			'auditedBy'			=> Auth::user()->id
	  			,'is_need_more_doc' => 0
	  		])){
	  			$conID = DB::table('tblpaymentTransaction')->where('ID', $id)->first()->contractID;
	  			DB::table('tblcontractDetails')->where('ID', $conID)->update([
	  				'paymentStatus' => 4
	  			]);
	  			$comment = trim($request['comment']).": Audited and cleared  by ". Auth::user()->name." at auditing stage";
	  		   DB::table('tblcomments')->insert(['commenttypeID' => 2, 'affectedID' => $conID,'paymentID' => 0, 'username' => Auth::user()->username, 'comment' => $comment]);
	  			$data['success'] = "Voucher has successfully been passed to CPO for further processing!";
	  			 $taskscheduled=$this->UpdateAlertTable("Unprocessed Voucher",'cpo/report','0','HCPO','tblpaymentTransaction',$id,1);
	  			 $this->addLogg("Voucher with ID: $id Moved to CPO","Voucher with ID $id CPO");
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
       	    
       	}
       		if( isset( $_POST['moredocument'] )){
       	  $id = $request['vid'];
       	  $this->validate($request, [
		    'vid'       => 'required'
		    ,'comment'  => 'required'
		    ]); 
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'is_need_more_doc' => 1,
	  		])){
	  			$Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  			$remark= $Vdetails->paymentDescription. " Rejected for ". $request['comment']." at auditing stage by: ". Auth::user()->name ;
	  			$data['success'] = "Voucher has been for additional document";
	  			$theid =$id;
	  			$Vdetails->contractID;
	  		        $user = Auth::user()->username;
	  		        $commenttypeID = 2;
	  		        $comment = trim($request['comment']).": queried by ". Auth::user()->name." at audit stage";
	  		        DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $Vdetails->contractID,'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
	  		         $taskscheduled=$this->UpdateAlertTable("Queried Voucher",'queried/voucher','0','OC','tblpaymentTransaction',$id,1);  
	  		        $this->addLogg("Voucher with ID: $id queried with reason:$comment","Voucher with ID: $id queried");
	  		        
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}  
       	}
       	if( isset( $_POST['decline'] )){
       	  $id = $request['vid'];
       	  $this->validate($request, [
		    'vid'       => 'required'
		    ,'comment'  => 'required'
		    ]);
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'checkbyStatus' 		=> 2,
	  			'dateCheck' 			=> '',
	  			'checkBy'				=> ''
	  			,'liabilityStatus' 	=> 0
	  			,'vstage' 		=> 0
	  			,'isrejected' 		=> 1
	  			,'status' 		=> 0,
	  		])){
	  			$Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  			$remark= $Vdetails->paymentDescription. " Rejected for ". $request['comment']." at auditing stage by: ". Auth::user()->name ;
	  			$this->VotebookUpdate($Vdetails->economicCodeID,$Vdetails->ID,$remark,$Vdetails->totalPayment,Date('Y-m-d'),5);
	  			$data['success'] = "Voucher has been rejected successfully";
	  			$theid =$id;
	  			$Vdetails->contractID;
	  		        $user = Auth::user()->username;
	  		        $commenttypeID = 2;
	  		        $comment = trim($request['comment']).": Rejected by ". Auth::user()->name." at auditing stage";
	  		        
	  		        DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $Vdetails->contractID,'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
	  		        $taskscheduled=$this->UpdateAlertTable("Voucher Rejection",'occheckby/voucher','0','OC','tblpaymentTransaction',$id,1);
	  		        $this->addLogg("Voucher with ID: $id Rejected with reason : $comment","Voucher with ID: $id Rejected");
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}  
       	}

	   		$data['tablecontent'] = $this->VourcherGroup($this->MyUnAuditted(Auth::user()->id));

	    return view('Liability.auditing', $data);
	}
	public function PreChecking(Request $request) 
    {
        
        $data['warning']        = '';
        $data['success']        = '';
        $data['error']      = '';
           if(DB::table('tblpaymentTransaction')->where('ID', $request['vid'])
				->update(['checkBy'    => $request['as_user'],])){
				 $taskscheduled=$this->UpdateAlertTable("Voucher Clearance",'checkby/voucher',$request['as_user'],'0','tblpaymentTransaction',$request['vid'],1);   
				}
       
       	if( isset( $_POST['process'] )){
       	  $id = $request['vid'];
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'checkbyStatus' 		=> 1,
	  			'status'       			=> 2
	  			,'vstage' 		=> 3
	  			,'isrejected' 		=> 0
	  			,'dateCheck' 			=> date('Y-m-j')
	  			,'checkBy'			=> Auth::user()->id
	  			,'is_need_more_doc' => 0
	  		])){
	  			$conID = DB::table('tblpaymentTransaction')->where('ID', $id)->first()->contractID;
	  			DB::table('tblcontractDetails')->where('ID', $conID)->update([
	  				'paymentStatus' => 4
	  			]);
	  			$comment = trim($request['comment']).": checked and cleared  by ". Auth::user()->name." at checking stage";
	  		
	  		   DB::table('tblcomments')->insert(['commenttypeID' => 2, 'affectedID' => $conID,'paymentID' => 0, 'username' => Auth::user()->username, 'comment' => $comment]);
	  		   $taskscheduled=$this->UpdateAlertTable("Voucher Clearance",'pre-audit','0','HAUD','tblpaymentTransaction',$id,1);  
 
	  			$data['success'] = "Voucher has successfully been passed to Audit for further processing!";
	  				$this->addLogg("Voucher with ID: $id Checked and cleared to Audit","Voucher with ID: $id Moved to audit");
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
       	    
       	}
       	if( isset( $_POST['decline'] )){
       	  $id = $request['vid'];
       	  $this->validate($request, [
		    'vid'       => 'required'
		    ,'comment'  => 'required'
		    ]);
		    
		    //pitoff
			$getContractType = DB::table('tblpaymentTransaction')->where('ID', $id)->value('contractTypeID');
			if($getContractType == 6){
				$declineVstage = 5;
			}else{
				$declineVstage = 0;
			}
			//pitoff
			
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'checkbyStatus' 		=> 2,
	  			'dateCheck' 			=> '',
	  			'checkBy'				=> ''
	  			,'liabilityStatus' 	=> 0
	  			,'vstage' 		=> $declineVstage
	  			,'isrejected' 		=> 1
	  			,'status' 		=> 0,
	  		])){
	  			$Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  			$remark= $Vdetails->paymentDescription. " Rejected for ". $request['comment']." at checking stage by: ". Auth::user()->name ;
	  			$this->VotebookUpdate($Vdetails->economicCodeID,$Vdetails->ID,$remark,$Vdetails->totalPayment,Date('Y-m-d'),5);
	  			$data['success'] = "Voucher has been rejected successfully";
	  			$theid =$id;
	  			$Vdetails->contractID;
	  		        $user = Auth::user()->username;
	  		        $commenttypeID = 2;
	  		        $comment = trim($request['comment']).": Rejected by ". Auth::user()->name." at checking stage";
	  		        $taskscheduled=$this->UpdateAlertTable("Voucher Rejection",'occheckby/voucher','0','OC','tblpaymentTransaction',$id,1);  
 
	  		        DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $Vdetails->contractID,'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
	  		        	$this->addLogg("Voucher with ID: $id Rejected with reason: $comment","Voucher with ID: $id Rejected");
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}  
       	}
       		if( isset( $_POST['moredocument'] )){
       	  $id = $request['vid'];
       	  $this->validate($request, [
		    'vid'       => 'required'
		    ,'comment'  => 'required'
		    ]); 
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'is_need_more_doc' => 1,
	  		])){
	  			$Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  			$remark= $Vdetails->paymentDescription. " Rejected for ". $request['comment']." at auditing stage by: ". Auth::user()->name ;
	  			$data['success'] = "Voucher has been for additional document";
	  			$theid =$id;
	  			$Vdetails->contractID;
	  		        $user = Auth::user()->username;
	  		        $commenttypeID = 2;
	  		        $comment = trim($request['comment']).": queried by ". Auth::user()->name." at checking";
	  		        DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $Vdetails->contractID,'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
	  		         $taskscheduled=$this->UpdateAlertTable("Queried Voucher",'queried/voucher','0','OC','tblpaymentTransaction',$id,1);  
	  		        $this->addLogg("Voucher with ID: $id queried with reason:$comment","Voucher with ID: $id queried");
	  		        
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}  
       	}
       $data['tablecontent'] = $this->VourcherGroup($this->UnChecked());
        $data['UnitStaff']=$this->UnitStaff('CK');
      //dd( $data['tablecontent'] );
        return view('Liability.prechecking', $data);
    }
    
    public function PreAudit(Request $request) 
    {
        $data['warning']        = '';
        $data['success']        = '';
        $data['error']      = '';
            if(DB::table('tblpaymentTransaction')->where('ID', $request['vid'])->update(['auditedBy'    => $request['as_user'],]))
            {
				 $taskscheduled = $this->UpdateAlertTable("Voucher Clearance", 'voucher/audit', $request['as_user'], '0', 'tblpaymentTransaction', $request['vid'], 1); 			 
			}
       
       	if( isset( $_POST['process'] )){
       	  $id = $request['vid'];
			$salaryV = DB::table('tblpaymentTransaction')->where('ID', $id)->value('is_advances');
			// dd($salaryV);
			//if voucher is_advances == 3 which is for salary tk to vstage of 7 which rep completed and paid voucher
		  if($salaryV == 3){
			if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
				'auditStatus' 		=> 1,
				'status'       		=> 2
				,'vstage' 		    => 7
				,'isrejected' 		=> 0
				,'auditDate' 		=> date('Y-m-j'),
				'auditedBy'			=> Auth::user()->id
				,'is_need_more_doc' => 0
			])){
				$conID = DB::table('tblpaymentTransaction')
						->where('ID', $id)
						->first()
						->contractID;
						
						DB::table('tblcontractDetails')
						->where('ID', $conID)
						->update([
							'paymentStatus' => 4
						]);
				$comment = trim($request['comment']).": Audited and cleared  by ". Auth::user()->name." at checking audit unit";
				DB::table('tblcomments')->insert([
					'commenttypeID' => 2, 
					'affectedID' => $conID,
					'paymentID' => 0, 
					'username' => Auth::user()->username, 
					'comment' => $comment
					]);
				$data['success'] = "Voucher has successfully been passed to CPO for further processing!";
				$this->addLogg("Voucher with ID: $id passed back to Salary CPO for further processing!","Voucher with ID: $id Moved to CPO");
			} else {
				$data['error'] = "Whoops! something went wrong please try again";
			}
		  }else{
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'auditStatus' 		=> 1,
	  			'status'       		=> 2
	  			,'vstage' 		    => 4
	  			,'isrejected' 		=> 0
	  			,'auditDate' 		=> date('Y-m-j'),
	  			'auditedBy'			=> Auth::user()->id
	  			,'is_need_more_doc' => 0
	  		])){
	  			$conID = DB::table('tblpaymentTransaction')
	  			        ->where('ID', $id)
	  			        ->first()
	  			        ->contractID;
	  			        
	  			        DB::table('tblcontractDetails')
	  			        ->where('ID', $conID)
	  			        ->update([
	  				        'paymentStatus' => 4
	  			        ]);
	  			$comment = trim($request['comment']).": Audited and cleared  by ". Auth::user()->name." at checking audit unit";
	  		    DB::table('tblcomments')->insert([
	  		        'commenttypeID' => 2, 
	  		        'affectedID' => $conID,
	  		        'paymentID' => 0, 
	  		        'username' => Auth::user()->username, 
	  		        'comment' => $comment
	  		        ]);
	  			$data['success'] = "Voucher has successfully been passed to CPO for further processing!";
	  			$taskscheduled=$this->UpdateAlertTable(
	  			    "Unprocessed Voucher",
	  			    'cpo/report',
	  			    '0',
	  			    'HCPO',
	  			    'tblpaymentTransaction',
	  			    $id,
	  			    1
	  			);
	  			$this->addLogg("Voucher with ID: $id passed to CPO for further processing!","Voucher with ID: $id Moved to CPO");
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
		}
       	    
       	}
       	if( isset( $_POST['decline'] ))
       	{
       	  $id = $request['vid'];
       	  $this->validate($request, [
		    'vid'       => 'required'
		    ,'comment'  => 'required'
		    ]);
		    //pitoff
			$getContractType = DB::table('tblpaymentTransaction')->where('ID', $id)->value('contractTypeID');
			if($getContractType == 6){
				$declineVstage = 2;
			}else{
				$declineVstage = 0;
			}
			//pitoff
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'checkbyStatus' 		=> 2,
	  			'dateCheck' 			=> '',
	  			'checkBy'				=> ''
	  			,'liabilityStatus' 	    => 0
	  			,'vstage' 		        => $declineVstage
	  			,'isrejected' 		    => 1
	  			,'status' 		        => 0,
	  		])){
	  			$Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  			$remark= $Vdetails->paymentDescription. " Rejected for ". $request['comment']." at auditing stage by: ". Auth::user()->name ;
	  			$this->VotebookUpdate($Vdetails->economicCodeID,$Vdetails->ID,$remark,$Vdetails->totalPayment,Date('Y-m-d'),5);
	  			$data['success'] = "Voucher has been rejected successfully";
	  			$theid =$id;
	  			$Vdetails->contractID;
	  		        $user = Auth::user()->username;
	  		        $commenttypeID = 2;
	  		        $comment = trim($request['comment']).": Rejected by ". Auth::user()->name." at audit stage";
	  		        DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $Vdetails->contractID,'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
	  		         $taskscheduled=$this->UpdateAlertTable("Voucher Rejection",'occheckby/voucher','0','OC','tblpaymentTransaction',$id,1);  
	  		        $this->addLogg("Voucher with ID: $id Rejected with Reason:$comment","Voucher with ID: $id Rejected");
	  		        
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}  
       	}
       		if( isset( $_POST['moredocument'] )){
       	  $id = $request['vid'];
       	  $this->validate($request, [
		    'vid'       => 'required'
		    ,'comment'  => 'required'
		    ]); 
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'is_need_more_doc' => 1,
	  		])){
	  			$Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  			$remark= $Vdetails->paymentDescription. " Rejected for ". $request['comment']." at auditing stage by: ". Auth::user()->name ;
	  			$data['success'] = "Voucher has been for additional document";
	  			$theid =$id;
	  			$Vdetails->contractID;
	  		        $user = Auth::user()->username;
	  		        $commenttypeID = 2;
	  		        $comment = trim($request['comment']).": queried by ". Auth::user()->name." at audit stage";
	  		        DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $Vdetails->contractID,'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
	  		         $taskscheduled=$this->UpdateAlertTable("Queried Voucher",'queried/voucher','0','OC','tblpaymentTransaction',$id,1);  
	  		        $this->addLogg("Voucher with ID: $id queried with reason:$comment","Voucher with ID: $id queried");
	  		        
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}  
       	}//
       	
       	//Get Data on page load
       $data['tablecontent'] = $this->VourcherGroup($this->UnAuditted());
       
      
        $data['UnitStaff']=$this->UnitStaff('AU');
      
        return view('Liability.preaudit', $data);
    }
    
    public function AllVoucher(Request $request){
		$data['location'] = $request['location'];
		$data['fromdate'] = $request['fromdate'];
		$data['todate'] = $request['todate'];
		 if($data['fromdate']=='') {$data['fromdate']=Carbon::now()->format('Y-m-d');}
		  if($data['todate']=='') {$data['todate']=Carbon::now()->format('Y-m-d');}
	   $data['tablecontent'] =     $this->AllVouchers($data['fromdate'], $data['todate'],$data['location']);
	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
	    	$value = (object) $lis;
	    	$data['tablecontent'][$key]  = $value;
	    }
	   $data['UnitLocation']=$this->UnitLocation();
	   return view('Liability.allvouchers', $data);
	}
	 public function ReversableVoucher(Request $request){
	     if( isset( $_POST['decline'] )){
               	  $id = $request['vid'];
               	  $this->validate($request, [
        		    'vid'       => 'required'
        		    ,'comment'  => 'required'
        		    ]); 		
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'liabilityStatus' 	=> 0
	  			,'vstage' 		=> 0,
	  			'isrejected' 		=> 1
	  			,'is_archive' 		=> 1
	  			,'status' 		=> 0,
	  		])){
	  			$theid = $id;
	  		        $user = Auth::user()->username;
	  		        $commenttypeID = 2;
	  		        $Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  		       $comment = trim($request['comment']).": Recalled by ". Auth::user()->name;
	  		        $cid=$Vdetails->contractID;
	  		        DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID,'affectedID' => $cid,'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
	  		        if(!DB::table('tblpaymentTransaction')->where('contractID', $cid)->where('is_archive', 0)->first())
	  		        DB::table('tblcontractDetails')->where('ID', $cid)->update([
	  			    'awaitingActionby' 	=> 'CA',
	  			    'openclose'			=> 0
	  		        ,'approvalStatus' 	=> 0
	  		        ,'isrejected' 	=> 1
	  		        ,'OC_staffId' => null
	  		]);
	  		        $taskscheduled=$this->UpdateAlertTable("Voucher archiving",'occheckby/voucher','0','-','tblpaymentTransaction',$id,0);  
	  			$data['success'] = "Voucher has been archived successfully!";
	  			$usr=Auth::user()->name;
	  			$this->addLogg("Voucher with ID: $id and Description: $Vdetails->paymentDescription recalled by $usr Reason: $comment","Voucher Archiving");
	  			return back()->with('msg','This voucher have been successfully recalled!');
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	   	}
		$data['location'] = $request['location'];
	   $data['tablecontent'] =     $this->AllRecallableVouchers($data['location']);
	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
	    	$value = (object) $lis;
	    	$data['tablecontent'][$key]  = $value;
	    }
	   $data['UnitLocation']=$this->UnitLocation();
	   return view('Liability.allrecallablevouchers', $data);
	}
	
	
	
	
	//This function haddles Lock and Unlock Voucher
	public function lockUnlockVoucher(Request $request){ 
	    $this->validate($request, [
        	'voucherAction'     => 'required|integer',
        	'voucherID'         => 'required|integer'
        ]); 
        //Update
         $action     = $request['voucherAction'];
         $voucherID = $request['voucherID'];
         try{
            DB::table('tblpaymentTransaction')->where('ID', $voucherID)->update(['voucher_lock' => $action]);
        	return back()->with('msg','This voucher have been successfully ' . ($action ? 'locked and cannot be moved.': 'open to be moved.'));
         }catch(\Throwable $err){
            return back()->with('err','Sorry we are unable to ' . ($action ? 'lock this voucher.': 'open this voucher.'));
         }
	}
	//ends lock/unlock voucher
	
	
	
	//Voucher Recall
	 public function RecalledVoucher(Request $request){
	     if( isset( $_POST['decline'] )){
               	  $id = $request['vid'];
               	  $this->validate($request, [
        		    'vid'       => 'required'
        		    ,'comment'  => 'required'
        		    ]); 		
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'liabilityStatus' 	=> 0
	  			,'vstage' 		=> 0,
	  			'isrejected' 		=> 1
	  			,'is_archive' 		=> 1
	  			,'status' 		=> 0,
	  		])){
	  			$theid = $id;
	  		        $user = Auth::user()->username;
	  		        $commenttypeID = 2;
	  		        $Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  		       $comment = trim($request['comment']).": Recalled by ". Auth::user()->name;
	  		        $cid=$Vdetails->contractID;
	  		        DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID,'affectedID' => $cid,'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
	  		        $taskscheduled=$this->UpdateAlertTable("Voucher archiving",'occheckby/voucher','0','-','tblpaymentTransaction',$id,0);  
	  			$data['success'] = "Voucher has been archived successfully!";
	  			$usr=Auth::user()->name;
	  			$this->addLogg("Voucher with ID: $id and Description: $Vdetails->paymentDescription by $usr Reason: $comment","Voucher Archiving");
	  			return back()->with('msg','This voucher have been successfully recalled!');
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	   	}
		$data['location'] = $request['location'];
	   $data['tablecontent'] =     $this->AllRecallableVouchers($data['location']);
	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
	    	$value = (object) $lis;
	    	$data['tablecontent'][$key]  = $value;
	    }
	   $data['UnitLocation']=$this->UnitLocation();
	   return view('Liability.allrecallablevouchers', $data);
	}
	
	public function AdvanceVoucher(Request $request){
		$data['location'] = $request['location'];
		$data['fromdate'] = $request['fromdate'];
		$data['todate'] = $request['todate'];
		 if($data['fromdate']=='') {$data['fromdate']=Carbon::now()->format('Y-m-d');}
		  if($data['todate']=='') {$data['todate']=Carbon::now()->format('Y-m-d');}
	   $data['tablecontent'] =     $this->AllAdvanceVouchers($data['fromdate'], $data['todate'],$data['location']);
	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
	    	$lis['comment'] = "";
	    	$value = (object) $lis;
	    	$data['tablecontent'][$key]  = $value;
	    }
	   //dd($data['tablecontent']);
	   $data['UnitLocation']=$this->UnitLocation();
	    return view('Liability.alladvancevouchers', $data);
	}
	
	public function AllArchiveVoucher(Request $request){
		$data['location'] = $request['location'];
		$data['fromdate'] = $request['fromdate'];
		$data['todate'] = $request['todate'];
		 if($data['fromdate']=='') {$data['fromdate']=Carbon::now()->format('Y-m-d');}
		  if($data['todate']=='') {$data['todate']=Carbon::now()->format('Y-m-d');}
	   $data['tablecontent'] =     $this->AllArchiveVouchers($data['fromdate'], $data['todate'],$data['location']);
	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
	    	$lis['comment'] = "";
	    	$value = (object) $lis;
	    	$data['tablecontent'][$key]  = $value;
	    }
	   //dd($data['tablecontent']);
	   $data['UnitLocation']=$this->UnitLocation();
	    return view('Liability.allarchivevouchers', $data);
	}
	
		public function PreLiability2(Request $request) 
	{
	
	    $data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
	    
	     if(DB::table('tblpaymentTransaction20200707')->where('ID', $request['vid'])
				->update(['liabilityBy'    => $request['as_user'],])){
				  //$taskscheduled=$this->UpdateAlertTable("Voucher Clearance",'voucher/liability',$request['as_user'],'0','tblpaymentTransaction20200707',$request['vid'],1); 			  
				}
	  if( isset( $_POST['process'] )){
	      //dd(date('Y-m-d'));
	      dd($request['lid']);
	      
       	  $id = $request['vid'];
	  		$Vdetails=DB::table('tblpaymentTransaction20200707')->where('ID', $id)->first();
	  		$voultbal =$this->VoultBalance($Vdetails->economicCodeID);
	  		if(floor($voultbal+floor($this->UnclearedLiability($Vdetails->FileNo))) < floor($Vdetails->totalPayment))
	  		{$data['error'] = "Insufficient Vote Balance!!! Liability cannot be cleared for this transaction";}
	  		else{
	  		
	  		if(DB::table('tblpaymentTransaction20200707')->where('ID', $id)->update([
	  			'liabilityBy' 		=> Auth::user()->id,
	  			'liabilityStatus' 		=> 1,
	  			'vstage' 		=> 2,
	  			'status' 		=> 2,
	  			'isrejected' 		=> 0,
	  			'dateTakingLiability' 	=>  $this->ProcessDATE($Vdetails->economicCodeID) ,// '2020-12-31',//tobe deleted    date('Y-m-d')
	  		])){
	  		    DB::table('tblliability_taken')->where('id', $request['lid'])->update([
	  			'clear_by' 		    => Auth::user()->id,
	  			'status' 		    => 0,
	  			'ref_voucher_id'    => $id,
	  			'status' 		=> 1,
	  			'is_cleared' =>1,
	  			'time_cleared' =>  $this->ProcessDATE($Vdetails->economicCodeID) ,//'2020-12-31',//tobe deleted 'date_cleared' 	=>     date('Y-m-d h:i:s')
	  			]);
	  			$remark=$Vdetails->paymentDescription;
	  			//$this->VotebookUpdate($Vdetails->economicCodeID,$Vdetails->ID,$remark,$Vdetails->totalPayment,Date('Y-m-d'),2);
	  			//$comment = trim($request['comment']).": Liability cleared and passed for checking by ". Auth::user()->name;
	  		    DB::table('tblcomments')->insert(['commenttypeID' => 2, 'affectedID' => $Vdetails->contractID,'paymentID' => 0, 'username' => Auth::user()->username, 'comment' => $comment]);
	  			$data['success'] = "Voucher Liability successfully cleared and passed to checking for further processing!";
	  			//$taskscheduled=$this->UpdateAlertTable("Voucher Clearance",'pre-check','0','HC','tblpaymentTransaction',$id,1);
	  			//$this->addlogg("Liability Taken for Voucher with ID: $id and Payment Description:$Vdetails->paymentDescription  moved to Checking for further processing!","Liability taken for Voucher with ID: $id");
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	  		}
	  		
	   	} 
                
               if( isset( $_POST['decline'] )){
               	  $id = $request['vid'];
               	  $this->validate($request, [
        		    'vid'       => 'required'
        		    ,'comment'  => 'required'
        		    ]); 		
	  		if(DB::table('tblpaymentTransaction20200707')->where('ID', $id)->update([
	  			'liabilityStatus' 	=> 0
	  			,'vstage' 		=> 0
	  			,'status' 		=> 0,
	  			'isrejected' 		=> 1,
	  		])){
	  			$theid = $id;
	  		        $user = Auth::user()->username;
	  		        $commenttypeID = 2;
	  		        $Vdetails=DB::table('tblpaymentTransaction20200707')->where('ID', $id)->first();
	  		       $comment = trim($request['comment']).": Rejected by ". Auth::user()->name." at funds clearance stage";
	  		        $cid=$Vdetails->contractID;
	  		    //DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID,'affectedID' => $cid,'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
	  		    //$taskscheduled=$this->UpdateAlertTable("Voucher Rejection",'occheckby/voucher','0','OC','tblpaymentTransaction20200707',$id,1);   
	  			$data['success'] = "Voucher has been Declined successfully!";
	  			//$this->addlogg("Voucher with ID: $id Rejected with reason: $comment","Voucher with ID: $id Rejected");
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	   	}
    $data['UnitStaff']=$this->UnitStaff('EC');
    $data['tablecontent'] = $this->VourcherGroupWithBalances2($this->UnFundClearance2());
    //dd($data['tablecontent'] );
	    return view('Liability.preliability2', $data);
	}
 public function FindLiability(Request $request)
    {       
        $id        = $request['code'];
        $ct=db::table('tbleconomicCode')->where('ID',$id)->value('contractGroupID');
	   $period=db::table('tblactiveperiod')->where('contractTypeID',$ct)->value('year');
    $q=db::table('tblliability_taken')
    ->where('economic_id',$id)
    ->where('period',$period)
    ->where('status',1)
    ->where('is_cleared',0)
    ->get();
        return response()->json($q);
    }
    
public function AuditedVoucher(Request $request){
		$data['fromdate'] = $request['fromdate'];
		$data['todate'] = $request['todate'];
		 if($data['fromdate']=='') {$data['fromdate']=Carbon::now()->format('Y-m-d');}
		  if($data['todate']=='') {$data['todate']=Carbon::now()->format('Y-m-d');}
	   $data['tablecontent'] =     $this->AllAuditVouchers($data['fromdate'], $data['todate']);
	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
	    	$value = (object) $lis;
	    	$data['tablecontent'][$key]  = $value;
	    }
	   return view('Liability.audited_vouchers', $data);
	}    
	public function MyAuditedVoucher(Request $request){
		$data['fromdate'] = $request['fromdate'];
		$data['todate'] = $request['todate'];
		 if($data['fromdate']=='') {$data['fromdate']=Carbon::now()->format('Y-m-d');}
		  if($data['todate']=='') {$data['todate']=Carbon::now()->format('Y-m-d');}
	   $data['tablecontent'] =     $this->MyAuditVouchers($data['fromdate'], $data['todate']);
	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
	    	$value = (object) $lis;
	    	$data['tablecontent'][$key]  = $value;
	    }
	   return view('Liability.myaudited_vouchers', $data);
	}    
	
	
	
	
	
	//SEARCH BY DATE
	public function searchVoucher_POST(Request $request)
	{
		Session::forget('getYear');
		Session::forget('getFrom');
		Session::forget('getTo');
		//
		$this->validate($request, [
			'getYear' 		=> 'required|integer',
			'getFrom' 		=> 'required|date',
			'getTo' 		=> 'required|date',
		]);
		Session::put('getYear', strtoupper(trim($request['getYear'])));
		Session::put('getFrom', strtoupper(trim($request['getFrom'])));
		Session::put('getTo', strtoupper(trim($request['getTo'])));
        
		return redirect()->back();
	}
	
	public function Salaryclearance(Request $request){
		$data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
	  	if($request['reason'] == 1){
	  		$id = $request['paymentTransID'];
	  		
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update(['vstage' => 1])){
	  		    $this->addlogg("Voucher with ID $id Moved to Checking","Voucher with ID $id Moved to Checking");
	  			$data['success'] = "Voucher Passed to Checking!";
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	   	}

if( isset( $_POST['clear'] )){
	  		$id = $request['clearid'];
	  		$cid=DB::table('tblpaymentTransaction')->where('ID', $id)->value('contractID');
	  		$comment = trim($request['remark']).": comment by ". Auth::user()->name." at other charges";
	  		if(trim($request['remark'])=='') $comment =  "Reviewed and passed  by ". Auth::user()->name." at other charges";
	  		DB::table('tblcomments')->insert(['commenttypeID' => 1, 'affectedID' => $cid,'paymentID' => $id, 'username' => Auth::user()->username, 'comment' => $comment]);
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  		    'vstage' => 2,
	  		    'isrejected' 	=> 0,
	  		    'liabilityBy' 		=> Auth::user()->id,
	  			'liabilityStatus' 		=> 1,
	  			'status' 		=> 2,
	  		    ])){
	  		    $taskscheduled=$this->UpdateAlertTable("Voucher Clearance",'pre-check','0','CK','tblpaymentTransaction',$id,1);
        	   
	  		    $this->addlogg("Voucher with ID $id Moved to Checking","Voucher with ID $id Moved to Checking");
	  			$data['success'] = "Voucher Passed to Checking!";
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	  		return back()->with('msg','Voucher Passed to Checking!');
	   	}

	   	if( isset( $_POST['reject'] )){
	   	    //dd("ksjsjsj");
	   		$id = $request['chosen1'];
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->first()){
	  		     $this->addlogg("Voucher with ID $id Rejected","Voucher with ID: $id Rejected");
	  			//$Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  			$Vdetails=DB::table('tblcontractDetails')->where('ID', DB::table('tblpaymentTransaction')->where('ID', $id)->value('contractID'))->first();
	  			$data['success'] = "Voucher has been rejected successfully";
	  			$theid =$id;
	  			$cid=$Vdetails->ID;
	  		    $user = Auth::user()->username;
	  		    $commenttypeID =2;
	  		   $comment = trim($request['declinemess']).": Rejected by ".Auth::user()->username."";
	  		   DB::table('tblcontractDetails')->where('ID', $cid)->update([
	  			'awaitingActionby' 	=> $Vdetails->approval_last_action_by,
	  			'openclose'			=> 0
	  		    ,'approvalStatus' 	=> 0
	  		    ,'isrejected' 	=> 1
	  		    ,'OC_staffId' => null
	  		]);
	  		        $taskscheduled=$this->UpdateAlertTable("Rejected task",'procurement/approve','',$Vdetails->approval_last_action_by,'tblcontractDetails',$cid,1);
	  		        DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $cid,'paymentID' => $theid, 'username' => $user, 'comment' => $comment]);
	  		        DB::table('tblpaymentTransaction')->where('ID', $id)->update(['is_archive'=>1]);
	  		        //dd($id);
	  		       
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	   	}

	   	$data['tablecontent'] = DB::table('tblpaymentTransaction')
	    						->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
	    						->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    						->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    						->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
	    						->leftjoin('tblallocation_type', 'tblpaymentTransaction.allocationType', '=', 'tblallocation_type.ID')
	    						->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    						->where('tblpaymentTransaction.is_archive', 0)
	    						->where('tblpaymentTransaction.vstage','<=', 0)->where('tblpaymentTransaction.is_restore', 0)->where('tblpaymentTransaction.is_advances', 3)
	    						 
	    						->select('tblpaymentTransaction.*', 'tbleconomicCode.description as ecotext', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tblallocation_type.allocation', 'tblcontractDetails.contractValue', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions','tblcontractDetails.beneficiary','tblcontractDetails.voucherType', DB::raw('tblcontractDetails.ID AS conID'))
	    						->orderBy('checkbyStatus', 'asc')->orderBy('dateAward', 'asc')->get();
	    						//dd($data['tablecontent']);
	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
	    	$value = (object) $lis;
	    	$data['tablecontent'][$key]  = $value;
	    }
	    return view('Liability.salary', $data);
	}
	
	
	
	
	
}//end class
