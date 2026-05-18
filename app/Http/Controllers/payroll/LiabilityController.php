<?php

namespace App\Http\Controllers\payroll;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Carbon\Carbon;
use Entrust;
use Excel;
use Input;
use QrCode; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class LiabilityController extends function24Controller
{



    public function index(Request $request) 
	{
	
	    $data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
	    //dd($this->TotalSpent(86,$period=''));
	  	if($request['reason'] == 1){
	  		$id = $request['paymentTransID'];
	  		$id = $request['paymentTransID'];
	  		$Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  		
	  		$voultbal =$this->AvailableBal($Vdetails->economicCodeID);
	  		//$voultbal =$this->VoultBalance($Vdetails->economicCodeID);
	  		$use_variable="1";
	  		switch ($use_variable){
	  		    case '1':
	  		 $period=$this->ActivePeriod();
	  		$ecoid=$Vdetails->economicCodeID;
	  		$IsBudgetable=$this->IsBudgetable($ecoid);
	  		  $thisyearbudget= DB::Select("SELECT IFNULL(sum(`allocationValue`),0) as allocationValue FROM `tblbudget` WHERE `Period`='$period' and `economicCodeID`='$ecoid' and `AllocationStatus`=1")[0]->allocationValue;
			$refund=DB::table('treasury_refund')->where('receipt_period',$period)->where('economicID',$Vdetails->economicCodeID)->value('amount_tsa_bank');
// 			if(((floor($this->TotalSpent($Vdetails->economicCodeID,$period=''))+floor($Vdetails->totalPayment)-floor($refund))>$thisyearbudget) && $IsBudgetable){
// 			    dd("djdjddj");
// 			    dd($this->TotalSpent($Vdetails->economicCodeID,$period=''));
// 			    dd($Vdetails->economicCodeID);
// 			    dd($thisyearbudget);
// 			    $data['error'] = "This payment cannot be made on this vote because it will exceed annual budget for the vote!";
// 			    break; 
// 			}
			//dd("Pls check later");
	  		//if((floor($voultbal+floor($this->UnclearedLiability($Vdetails->FileNo))) < floor($Vdetails->totalPayment))  && $IsBudgetable)// use this is liability taken is considered
	  		if((round($voultbal,2) < round($Vdetails->totalPayment,2))  && $IsBudgetable)
	  	
	  		{$data['error'] = "Insufficient Vote Balance!!! Liability cannot be cleared for this transaction ";}
	  		else{
	  		    //dd("error corrected");
	  		$contractID=DB::table('tblcontractDetails')->where('ID', $Vdetails->contractID)->value('procurement_contractID');
	  		if(DB::table('create_contract')->where('contractID',$contractID )->update([
	  			'liability_amount' 		=> $Vdetails->totalPayment,
	  			'active'            => 0,
	  			
	  		])){
	  		$contractD = DB::table('create_contract')->where('contractID', $contractID)->first();
                    	$this->VotebookUpdate($contractD->economic_code, $contractD->contractID, $contractD->description, $contractD->amount, date('Y-m-d'), '1', $contractD->period);
	  		}
	  		
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'liabilityBy' 		=> Auth::user()->username,
	  			'liabilityStatus' 		=> 1,
	  			'vstage' 		=> 4,
	  			//'vstage' 		=> 2,
	  			'status' 		=> 2,
	  			'f_commitment' 		=> 1,
	  			'dateTakingLiability' 	=>     date('Y-m-j'),
	  			//'auditedBy'		=> Auth::user()->username,
	  			//'auditStatus' 	=> 1,
	  			//'auditDate' 	=> date('Y-m-j'),
	  			'cpo_payment'                => 0,
	  			
	  		])){
	  			$remark=$Vdetails->paymentDescription;
	  			//$this->VotebookUpdate($Vdetails->economicCodeID,$Vdetails->ID,$remark,$Vdetails->totalPayment,Date('Y-m-d'),2);
	  			$this->VotebookUpdate($Vdetails->economicCodeID,$Vdetails->ID,$remark,$Vdetails->totalPayment,Date('Y-m-d'),2);
	  			$data['success'] = "Liability is  successfully cleard and pass to checking!";
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	  		}
	  	}
	  		
	   	} 
                
                if($request['decliner']){
                 //die($request['declineid']);
	   		$id = $request['declineid'];   		
	   		
	  		 
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'liabilityStatus' 	=> 0
	  			,'vstage' 		=> 0
	  			,'f_commitment' => 0
	  			,'status' 		=> 0,
	  		])){
	  			$theid = $id;
	  		        $user = Auth::user()->username;
	  		        $commenttypeID = 2;
	  		        $Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  		        $comment = $request['decliner'];
	  		        $cid=$Vdetails->contractID;
	  		        DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID,'affectedID' => $cid,'paymentID' => $theid, 'username' => $user, 'comment' => $comment]);
	  		        
	  			$data['success'] = "Voucher has been Declined successfully!";
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
	    						->where('tblpaymentTransaction.accept_voucher_status', '=', 1)
	    						->where('tblpaymentTransaction.vstage', 1)	    						
	    						->select('tblpaymentTransaction.*', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tblallocation_type.allocation', 'tblcontractDetails.contractValue', 'tblcontractDetails.file_ex','tblcontractDetails.beneficiary','tblcontractDetails.voucherType', 'tblcontractDetails.ContractDescriptions', DB::raw('tblcontractDetails.ID AS conID'))
	    						->orderBy('tbleconomicCode.contractGroupID', 'asc')->orderBy('tbleconomicCode.economicCode')
	    						->orderBy('dateAward', 'asc')
	    						->get();
	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);

	    	$whtpayee = ($value->WHTPayeeID == "") ? "" : DB::table('tblVATWHTPayee')->where('ID', $value->WHTPayeeID)->first()->payee;
	    	$vatpayee = ($value->VATPayeeID == "") ? "" : DB::table('tblVATWHTPayee')->where('ID', $value->VATPayeeID)->first()->payee;
	    	$lis['whtpayee'] = $whtpayee;
	    	$lis['vatpayee'] = $vatpayee;
	    	$lis['votebal'] =   $this->VoultBalance($value->economicCodeID);
	    	$lis['AvailBal'] =  $this->AvailableBal($value->economicCodeID);
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
	        	
	        	//dd($com);
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
        

	    return view('Liability.newform', $data);
	}

	
	   public function FinalApproval(Request $request) 
	{
	
	    $data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';

	  	if($request['reason'] == 1){
	  		$id = $request['paymentTransID'];
	  		$id = $request['paymentTransID'];
	  		DB::table('tblpaymentTransaction')->where('ID', $id)->update([
  				'f_commitment' 		=> 1,
  				'f_approval_by'=>Auth::user()->name,
	  		]);
	  		}
                
                if($request['decliner']){
                 //die($request['declineid']);
	   		$id = $request['declineid'];   		
	   		
	  		 
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'f_approval_by'=>Auth::user()->name,
	  			'f_commitment' 		=> 2,
	  		])){
	  			$theid = $id;
	  		        $user = Auth::user()->username;
	  		        $commenttypeID = 2;
	  		        $Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  		        $comment = $request['decliner'];
	  		        $cid=$Vdetails->contractID;
	  		        DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID,'affectedID' => $cid,'paymentID' => $theid, 'username' => $user, 'comment' => $comment]);
	  		        
	  			$data['success'] = "Voucher has been Declined successfully!";
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
	    						->where('tblpaymentTransaction.accept_voucher_status', '=', 1)
	    						->where('tblpaymentTransaction.vstage', 4)
	    						->where('tblpaymentTransaction.status', 2)
	    						->where('tblpaymentTransaction.f_commitment', 0)
	    						->select('tblpaymentTransaction.*', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tblallocation_type.allocation', 'tblcontractDetails.contractValue', 'tblcontractDetails.file_ex','tblcontractDetails.beneficiary','tblcontractDetails.voucherType', 'tblcontractDetails.ContractDescriptions', DB::raw('tblcontractDetails.ID AS conID'))
	    						->orderBy('tbleconomicCode.contractGroupID', 'asc')->orderBy('tbleconomicCode.economicCode')
	    						->orderBy('dateAward', 'asc')
	    						->get();
	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);

	    	$whtpayee = ($value->WHTPayeeID == "") ? "" : DB::table('tblVATWHTPayee')->where('ID', $value->WHTPayeeID)->first()->payee;
	    	$vatpayee = ($value->VATPayeeID == "") ? "" : DB::table('tblVATWHTPayee')->where('ID', $value->VATPayeeID)->first()->payee;
	    	$lis['whtpayee'] = $whtpayee;
	    	$lis['vatpayee'] = $vatpayee;
	    	$lis['votebal'] =   $this->VoultBalance($value->economicCodeID);
	    	$lis['AvailBal'] =  $this->AvailableBal($value->economicCodeID);
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
	        	
	        	//dd($com);
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
	
	
	
	
	
	//View all created voucher
	public function check(Request $request)
	{ 
	    
	    $data['warning'] = '';
	    $data['success'] = ''; 
	    $data['error'] = '';
	    $data['vourcherid']  = $request['vourcherid'];
	    $voucherNoArray = explode(',', $request['vourcherid']);
	    $data['numberRecordReturned'] = 0;
	    $data['dateReturned'] = 0;

	    //Start search
	    if( ($request['startDate'])!= "" or ($request['endDate']) != "")
	    { 
	        $data['tablecontent'] = DB::table('tblpaymentTransaction')
	       	    ->where('tblpaymentTransaction.trackID', '=', null)
	    		->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
	    		->leftjoin('tblallocation_type', 'tblpaymentTransaction.allocationType', '=', 'tblallocation_type.ID')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		->leftjoin('voucher_type', 'voucher_type.vouchertypeID', '=', 'tblpaymentTransaction.voucher_type_deptID')
	    		->whereBetween('tblpaymentTransaction.dateCreated', [$request['startDate']." 00:00:00", $request['endDate']." 23:59:59"])
	    		->select('voucher_type.voucher_type_name as voucherType', 'tblpaymentTransaction.*', 'tblpaymentTransaction.ID as transID', 'tblcontractDetails.beneficiary as beneficiaryName', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tbleconomicCode.ID as economicCodeID', 'tblallocation_type.allocation', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions', DB::raw('tblcontractDetails.ID AS conID'))
	    		->orderBy('tblpaymentTransaction.accept_voucher_status', 'Asc')
	    		->orderBy('tblpaymentTransaction.ID', 'Desc')
	    		->paginate(100);
	    		$data['dateReturned'] = '   Searched Voucher(s): ' . $request['searchDate'];
	    		
	    }else if( $data['vourcherid'] != '' or !empty($data['vourcherid']))
	    {
	        $data['tablecontent'] = DB::table('tblpaymentTransaction')
	       	    ->where('tblpaymentTransaction.trackID', '=', null)
	    		->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
	    		->leftjoin('tblallocation_type', 'tblpaymentTransaction.allocationType', '=', 'tblallocation_type.ID')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		->leftjoin('voucher_type', 'voucher_type.vouchertypeID', '=', 'tblpaymentTransaction.voucher_type_deptID')
	    		->whereIn('tblpaymentTransaction.ID', $voucherNoArray)
	    		->select('voucher_type.voucher_type_name as voucherType', 'tblpaymentTransaction.*', 'tblpaymentTransaction.ID as transID', 'tblcontractDetails.beneficiary as beneficiaryName', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tbleconomicCode.ID as economicCodeID', 'tblallocation_type.allocation', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions', DB::raw('tblcontractDetails.ID AS conID'))
	    		->orderBy('tblpaymentTransaction.accept_voucher_status', 'Asc')
	    		->orderBy('tblpaymentTransaction.ID', 'Desc')
	    		->paginate(100);
	    		$data['numberRecordReturned'] = 'Total Voucher Found: ' . count($data['tablecontent']);
	    		
	    }else if(strtoupper($this->getUserRole()->contract_type) == strtoupper("Administrator") or strtoupper($this->getUserRole()->contract_type) == strtoupper("Admin") or strtoupper($this->getUserRole()->contract_type) == null)
	    { 
	         $data['tablecontent'] = DB::table('tblpaymentTransaction')
	         ->where('tblpaymentTransaction.trackID', '=', null)
	    		->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
	    		->leftjoin('tblallocation_type', 'tblpaymentTransaction.allocationType', '=', 'tblallocation_type.ID')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		->leftjoin('voucher_type', 'voucher_type.vouchertypeID', '=', 'tblpaymentTransaction.voucher_type_deptID')
	    		->select('voucher_type.voucher_type_name as voucherType', 'tblpaymentTransaction.*', 'tblpaymentTransaction.ID as transID', 'tblcontractDetails.beneficiary as beneficiaryName', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tbleconomicCode.ID as economicCodeID', 'tblallocation_type.allocation', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions', DB::raw('tblcontractDetails.ID AS conID'))
	    		->orderBy('tblpaymentTransaction.accept_voucher_status', 'Asc')
	    		//->orderBy('tblpaymentTransaction.dateCreated', 'Desc')
	    		->orderBy('tblpaymentTransaction.ID', 'Desc')
	    		->paginate(50);
	    
	    }else if($this->getUserRole() <> null){ 
	       	$data['tablecontent'] = DB::table('tblpaymentTransaction')
	       	->where('tblpaymentTransaction.trackID', '=', null)
	    		->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
	    		->leftjoin('tblallocation_type', 'tblpaymentTransaction.allocationType', '=', 'tblallocation_type.ID')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		->leftjoin('voucher_type', 'voucher_type.vouchertypeID', '=', 'tblpaymentTransaction.voucher_type_deptID')
	    		->where('tblpaymentTransaction.department_voucher', ucfirst($this->getUserRole()->rolename))
	    		->select('voucher_type.voucher_type_name as voucherType', 'tblpaymentTransaction.*', 'tblpaymentTransaction.ID as transID', 'tblcontractDetails.beneficiary as beneficiaryName', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tbleconomicCode.ID as economicCodeID', 'tblallocation_type.allocation', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions', DB::raw('tblcontractDetails.ID AS conID'))
	    		->orderBy('tblpaymentTransaction.accept_voucher_status', 'Asc')
	    		//->orderBy('tblpaymentTransaction.dateCreated', 'Desc')
	    		->orderBy('tblpaymentTransaction.ID', 'Desc')
	    		->paginate(50); 
	    }else{
	        $data['tablecontent'] = array();
	    }
	    
	    
	    foreach ($data['tablecontent'] as $key => $value) 
	    {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
	    	$lis['comment'] = "";
	    	$com = DB::table('tblcomments')->where('affectedID', $value->ID)->orwhere('affectedID', $value->contractID)->get();	    	
	    	
	    	if($com){
	        	
		        	foreach($com as $k => $list){
		        		$newline = (array) $list;
		        		//$name = DB::table('users')->where('username', $list->username)->first()->name;
		        		//$newline['name'] = $name;
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
	     $data['economicCode'] = DB::table('tbleconomicCode')
                ->join('tbleconomicHead', 'tbleconomicHead.ID','=','tbleconomicCode.economicHeadID')
                ->leftjoin('tblcontractType', 'tblcontractType.ID','=','tbleconomicCode.contractGroupID')
                ->where('tbleconomicCode.status', 1)
                //->where('tblcontractType.ID', $contractType)
                ->select('*', 'tbleconomicCode.ID as economicID', 'tbleconomicCode.description as economicName' )
                ->orderby('tbleconomicCode.economicCode', 'Asc')
                ->get();
	    $data['departmentName'] = strtoupper(substr($this->getUserRole()->rolename, 0, 1));
	   
	    return view('Liability.check', $data);
	}





	public function editableVoucher(Request $request){
		$data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
	    
	    if(strtoupper($this->getUserRole()->contract_type) == strtoupper("Administrator") or strtoupper($this->getUserRole()->contract_type) == strtoupper("Admin") or strtoupper($this->getUserRole()->contract_type) == null)
	    { 
	         $data['tablecontent'] = DB::table('tblpaymentTransaction')
	    			->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
	    			->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    			->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    			->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
	    			->leftjoin('tblallocation_type', 'tblpaymentTransaction.allocationType', '=', 'tblallocation_type.ID')
	    			->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    			->orWhere('tblpaymentTransaction.vstage', 0)
	    			->orWhere('tblpaymentTransaction.vstage', 1)
	    			->orWhere('tblpaymentTransaction.vstage', -1)
	    			->select('tblpaymentTransaction.*', 'tblcontractDetails.beneficiary as beneficiaryName', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tblallocation_type.allocation', 'tblcontractDetails.contractValue', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions','tblcontractDetails.beneficiary','tblcontractDetails.voucherType', DB::raw('tblcontractDetails.ID AS conID'))
	    			->orderBy('checkbyStatus', 'asc')->orderBy('dateAward', 'asc')
	    			->get();
	    						
	    }else if($this->getUserRole() <> null){ 
	        $data['tablecontent'] = DB::table('tblpaymentTransaction')
	    			->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
	    			->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    			->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    			->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
	    			->leftjoin('tblallocation_type', 'tblpaymentTransaction.allocationType', '=', 'tblallocation_type.ID')
	    			->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    			->orWhere('tblpaymentTransaction.vstage', 0)
	    			->orWhere('tblpaymentTransaction.vstage', 1)
	    			->orWhere('tblpaymentTransaction.vstage', -1)
	    			//->where('tblpaymentTransaction.preparedBy', Auth::user()->username)
	    		    ->where('tblcontractType.contractType', $this->getUserRole()->contract_type)
	    			->select('tblpaymentTransaction.*', 'tblcontractDetails.beneficiary as beneficiaryName', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tblallocation_type.allocation', 'tblcontractDetails.contractValue', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions','tblcontractDetails.beneficiary','tblcontractDetails.voucherType', DB::raw('tblcontractDetails.ID AS conID'))
	    			->orderBy('checkbyStatus', 'asc')->orderBy('dateAward', 'asc')
	    			->get();
	  
	    }else{
	        $data['tablecontent'] = array();
	    }
	    
	    
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
	   
	    return view('Liability.pending', $data);
	}
	
	
	public function checkbypage(Request $request){
	//dd(Auth::user());
	$data['warning'] = '';
	$data['success'] = '';
	$data['error'] = '';



	  	if($request['reason'] == 1){
	  		$id = $request['paymentTransID'];
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'checkbyStatus' 		=> 1,
	  			'status'       			=> 2
	  			,'vstage' 		=> 3
	  			,'dateCheck' 			=> date('Y-m-j'),
	  			'checkBy'			=> Auth::user()->username
	  		])){
	  			$conID = DB::table('tblpaymentTransaction')->where('ID', $id)->first()->contractID;
	  			DB::table('tblcontractDetails')->where('ID', $conID)->update([
	  				'paymentStatus' => 4
	  			]);
	  			$data['success'] = "Voucher has passed checking!";
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	   	}

	   	if($request['reason'] == 2){
	   		$id = $request['chosen1'];
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'checkbyStatus' 		=> 2,
	  			'dateCheck' 			=> '',
	  			'checkBy'				=> ''
	  			,'liabilityStatus' 	=> 0
	  			,'vstage' 		=> 0
	  			,'status' 		=> 0,
	  		])){
	  			//$id = $request['paymentTransID'];
	  			$Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  			$remark= $Vdetails->paymentDescription. " Rejected for ". $request['declinemess']." at checking stage" ;
	  			$this->VotebookUpdate($Vdetails->economicCodeID,$Vdetails->ID,$remark,$Vdetails->totalPayment,Date('Y-m-d'),5);
	  			$data['success'] = "Voucher has been rejected successfully";
	  			$theid =$id;
	  			$Vdetails->contractID;
	  		        $user = Auth::user()->username;
	  		        $commenttypeID = 2;
	  		        $comment = trim($request['declinemess']).": Rejected by ".Auth::user()->username." at checking stage";
	  		        
	  		        DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $theid,'paymentID' => $theid, 'username' => $user, 'comment' => $comment]);
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
	    						->where('tblpaymentTransaction.vstage', 2)
	    						->select('tblpaymentTransaction.*', 'tblcontractDetails.beneficiary as beneficiaryName','tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tblallocation_type.allocation', 'tblcontractDetails.contractValue', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions','tblcontractDetails.beneficiary','tblcontractDetails.voucherType', DB::raw('tblcontractDetails.ID AS conID'))
	    						->orderBy('checkbyStatus', 'asc')->orderBy('dateAward', 'asc')->get();

	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
	    	$com = DB::table('tblcomments')->where('paymentID', $value->ID)->orwhere('affectedID', $value->contractID)->get();	    	
	    	$lis['comment'] = "";
	    	$lis['comment2'] = "";
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
	        	$lis['comment2'] = json_encode($com2);
	        	
	        } 
	    	$value = (object) $lis;
	    	$data['tablecontent'][$key]  = $value;
	    }
	    
	    return view('Liability.checking', $data);
	}
	
	
	
	public function OCclearance(Request $request){
		//dd(Auth::user());
		$data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';



	  	if($request['reason'] == 1){
	  		$id = $request['paymentTransID'];
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update(['vstage' => 1])){
	  			$data['success'] = "Voucher Passed to Expenditure Control!";
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	   	}

	   	if($request['reason'] == 2){
	   		$id = $request['chosen1'];
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			
	  			'dateCheck' 			=> '',
	  			'checkBy'				=> ''
	  			,'liabilityStatus' 	=> 0
	  			,'vstage' 		=> 0
	  			,'status' 		=> 0,
	  		])){
	  			//$id = $request['paymentTransID'];
	  			$Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  			//$remark= $Vdetails->paymentDescription. " Rejected for ". $request['declinemess']." at checking stage" ;
	  			//$this->VotebookUpdate($Vdetails->economicCodeID,$Vdetails->ID,$remark,$Vdetails->totalPayment,Date('Y-m-d'),5);
	  			$data['success'] = "Voucher has been rejected successfully";
	  			$theid =$id;
	  			$cid=$Vdetails->contractID;
	  		        $user = Auth::user()->username;
	  		        $commenttypeID =2;
	  		        $comment = trim($request['declinemess']).": Rejected by ".Auth::user()->username."";
	  		        
	  		        DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $cid,'paymentID' => $theid, 'username' => $user, 'comment' => $comment]);
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
	    						->where('tblpaymentTransaction.vstage', -1)
	    						->select('tblpaymentTransaction.*', 'tblcontractDetails.beneficiary as beneficiaryName', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tblallocation_type.allocation', 'tblcontractDetails.contractValue', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions','tblcontractDetails.beneficiary','tblcontractDetails.voucherType', DB::raw('tblcontractDetails.ID AS conID'))
	    						->orderBy('checkbyStatus', 'asc')->orderBy('dateAward', 'asc')->get();

	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
	    	$com = DB::table('tblcomments')->where('paymentID', $value->ID)->orwhere('affectedID', $value->contractID)->get();	    	
	    	$lis['comment'] = "";
	    	$lis['comment2'] = "";
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
	        	$lis['comment2'] = json_encode($com2);
	        	
	        		
	        } 
	    	$value = (object) $lis;
	    	$data['tablecontent'][$key]  = $value;
	    }

	    
	    return view('Liability.occhecking', $data);
	}

public function Auditcheck(Request $request){
		//dd(Auth::user());
		$data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';



	  	if($request['reason'] == 1){
	  		$id = $request['paymentTransID'];
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'auditStatus' 		=> 1,
	  			'status'       			=> 2
	  			,'vstage' 		=> 4
	  			,'auditDate' 			=> date('Y-m-j'),
	  			'auditedBy'			=> Auth::user()->username
	  		])){
	  			$conID = DB::table('tblpaymentTransaction')->where('ID', $id)->first()->contractID;
	  			DB::table('tblcontractDetails')->where('ID', $conID)->update([
	  				'paymentStatus' => 4
	  			]);
	  			$data['success'] = "Voucher has passed Auditing successfully!";
	  		} else {
	  			$data['error'] = "Whoops! something went wrong please try again";
	  		}
	   	}

	   	if($request['reason'] == 2){
	   		$id = $request['chosen1'];
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			

	  			'vstage' 		=> 0
	  			,'status' 		=> 0,
	  		])){
	  			$data['success'] = "Voucher has been rejected successfully";
	  			$Vdetails=DB::table('tblpaymentTransaction')->where('ID', $id)->first();
	  			$cid=$Vdetails->contractID;
	  			$theid =$id;
	  		        $user = Auth::user()->username;
	  		        $commenttypeID = 2;
	  		        $comment = trim($request['declinemess']).": Rejected by ".Auth::user()->username." at Auditing stage";
	  		        
	  		        DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $cid,'affectedID' => $cid,'paymentID' => $theid, 'username' => $user, 'comment' => $comment]);
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
	    						->where('tblpaymentTransaction.vstage', 3)
	    						->select('tblpaymentTransaction.*', 'tblcontractDetails.beneficiary as beneficiaryName', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tblallocation_type.allocation', 'tblcontractDetails.contractValue', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions','tblcontractDetails.beneficiary','tblcontractDetails.voucherType', DB::raw('tblcontractDetails.ID AS conID'))
	    					->orderBy('checkbyStatus', 'asc')->orderBy('dateAward', 'asc')->get();
//dd($data['tablecontent'] );	
	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
	    	$com = DB::table('tblcomments')->where('paymentID', $value->ID)->orwhere('affectedID', $value->contractID)->get();	    	
	    	$lis['comment'] = "";
	    	$lis['comment2'] = "";
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
	        	$lis['comment2'] = json_encode($com2);
	        	
	        		
	        } 
	    	$value = (object) $lis;
	    	$data['tablecontent'][$key]  = $value;
	    }

	    //dd($data['tablecontent']);
	    return view('Liability.auditing', $data);
	}//end function
	
	
	
	
	
	//Accept of Approve for commitment
	public function acceptOrApprove(Request $request)
	{
	    $data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
	    $data['vourcherid']  = $request['vourcherid'];
	    
	    if(strtoupper($this->getUserRole()->contract_type) == strtoupper("Administrator") or strtoupper($this->getUserRole()->contract_type) == strtoupper("Admin") or strtoupper($this->getUserRole()->contract_type) == null)
	    { 
	         $data['tablecontent'] = DB::table('tblpaymentTransaction')
	    		->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
	    		->leftjoin('tblallocation_type', 'tblpaymentTransaction.allocationType', '=', 'tblallocation_type.ID')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		->where('tblpaymentTransaction.accept_voucher_status', 0)
	    		//->where('tblpaymentTransaction.preparedBy', Auth::user()->username)
	    		//->where('tblcontractType.contractType', $this->getUserRole()->contract_type)
	    		->select('tblpaymentTransaction.*', 'tblcontractDetails.beneficiary as beneficiaryName', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tblallocation_type.allocation', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions', DB::raw('tblcontractDetails.ID AS conID'))
	    		//->orderBy('tbleconomicCode.contractGroupID', 'asc')->orderBy('tbleconomicCode.economicCode')
	    		->orderBy('tbleconomicCode.contractGroupID', 'asc')->orderBy('tblpaymentTransaction.ID')->orderBy('tbleconomicCode.economicCode')
	    		->paginate(30);
	    
	    }else if($this->getUserRole() <> null){ 
	       	$data['tablecontent'] = DB::table('tblpaymentTransaction')
	    		->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
	    		->leftjoin('tblallocation_type', 'tblpaymentTransaction.allocationType', '=', 'tblallocation_type.ID')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		->where('tblpaymentTransaction.accept_voucher_status', 0)
	    		->where('tblcontractType.contractType', $this->getUserRole()->contract_type)
	    		->select('tblpaymentTransaction.*', 'tblcontractDetails.beneficiary as beneficiaryName', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tblallocation_type.allocation', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions', DB::raw('tblcontractDetails.ID AS conID'))
	    		->orderBy('tbleconomicCode.contractGroupID', 'asc')->orderBy('tblpaymentTransaction.ID')->orderBy('tbleconomicCode.economicCode')
	    		->paginate(30); 
	    }else{
	        $data['tablecontent'] = array();
	    }
	    //Search
	    if( $data['vourcherid']!='')
	    {
	         $data['tablecontent'] = DB::table('tblpaymentTransaction')
	    		->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
	    		->leftjoin('tblallocation_type', 'tblpaymentTransaction.allocationType', '=', 'tblallocation_type.ID')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		->where('tblpaymentTransaction.accept_voucher_status', 0)->where('tblpaymentTransaction.ID', $data['vourcherid'])
	    		->select('tblpaymentTransaction.*', 'tblcontractDetails.beneficiary as beneficiaryName', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tblallocation_type.allocation', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions', DB::raw('tblcontractDetails.ID AS conID'))
	    		->orderBy('tbleconomicCode.contractGroupID', 'asc')->orderBy('tblpaymentTransaction.ID')->orderBy('tbleconomicCode.economicCode')
	    		->paginate(100);
	    }
	    
	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
	    	$lis['comment'] = "";
	    	$com = DB::table('tblcomments')->where('affectedID', $value->ID)->orwhere('affectedID', $value->contractID)->get();	    	
	    	
	    	if($com){
	        	
		        	foreach($com as $k => $list){
		        		$newline = (array) $list;
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
	    //
	    return view("Liability.acceptOrApprove", $data);
	}
	
	
	
	//Accept of Approve for commitment
	public function postAcceptOrApprove($transID)
	{   
	    $success = 0;
	    if($transID)
	    {
	        $success = DB::table('tblpaymentTransaction')->where('ID', $transID)->update([
	            'accept_voucher_status' => 1,
	       ]);
	    }
	    if($success)
	    {
	         return redirect()->back()->with('success', 'Your voucher was successfully accepted for commitment');
	    }
	    return redirect()->back()->with('err', 'Sorry, we cannot process this voucher at this moment. Try again.');
	}
	
	//Reject or Disapprove for commitment
	public function postRejectOrDisapprove($transID)
	{   
	    $success = 0;
	    
	    $salary_record_exists = DB::table('tblpaymentTransaction')->where('is_salary', 1)->where('ID', $transID)->exists();
	       
	    if($salary_record_exists)  
	    {
	         $getID =  DB::table('tblpaymentTransaction')->where('is_salary', 1)->where('ID', $transID)->first();
	         
    	     DB::table('tblcontractDetails')->where('is_salary', 1)->where('ID',$getID->contractID)->delete();
    	     DB::table('tblpaymentTransaction')->where('is_salary', 1)->where('ID', $transID)->delete();
    	   
	         return redirect()->back()->with('err', 'Record trashed');
	    }
	    else
	    {
	         if($transID)
    	    {
    	        $success = DB::table('tblpaymentTransaction')->where('ID', $transID)->update([
    	            'accept_voucher_status' => 0,
    	            'status' => 0,
    	       ]);
    	    }
    	    if($success)
    	    {
    	         return redirect()->back()->with('success', 'Your voucher was successfully reject for commitment');
    	    }
	        //return redirect()->back()->with('err', 'Sorry, we cannot process this voucher at this moment. Try again.');
	    }
	}
	
	
	public function VourcherLocation(Request $request)
	{
	    $data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
	    $data['vourcherid']  = $request['vourcherid'];
	    
        $data['tablecontent'] = array();
	    if( $data['vourcherid']!='')
	    {
	         $data['tablecontent'] = DB::table('tblpaymentTransaction')
	    		->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
	    		->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    		->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    		->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
	    		->leftjoin('tblallocation_type', 'tblpaymentTransaction.allocationType', '=', 'tblallocation_type.ID')
	    		->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    		
	    		->leftjoin('temp_epayment', 'temp_epayment.transaction_id', '=', 'tblpaymentTransaction.ID')
	    		->leftjoin('users', 'users.id', '=', 'temp_epayment.user_id')
	    		
	    		->where('tblpaymentTransaction.ID', $data['vourcherid'])
	    		->select('tblpaymentTransaction.*', 'tblcontractDetails.beneficiary as beneficiaryName', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tblallocation_type.allocation', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions', DB::raw('tblcontractDetails.ID AS conID'), 'users.name as cpoStaff')
	    		->orderBy('tbleconomicCode.contractGroupID', 'asc')->orderBy('tblpaymentTransaction.ID')->orderBy('tbleconomicCode.economicCode')
	    		->paginate(100);
	    }
	    
	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
	    	$lis['comment'] = "";
	    	$com = DB::table('tblcomments')->where('affectedID', $value->ID)->orwhere('affectedID', $value->contractID)->get();	    	
	    	
	    	if($com){
	        	
		        	foreach($com as $k => $list){
		        		$newline = (array) $list;
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
	    return view("Liability.vourcher_location", $data);
	}
	
	//Go to the Voucher Retirement Page under Voucher Management
	public function editVoucherPage(Request $request)
	{

		$data['warning'] = '';
		$data['success'] = '';
		$data['error'] = '';
		$data['vourcherid']  = $request['vourcherid'];
		$voucherNoArray = explode(',', $request['vourcherid']);
		$data['numberRecordReturned'] = 0;
		$data['dateReturned'] = 0;

		//Start search
		if (($request['startDate']) != "" or ($request['endDate']) != "") {
			$data['tablecontent'] = DB::table('tblpaymentTransaction')
				->where('tblpaymentTransaction.trackID', '=', null)
				->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
				->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
				->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
				->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
				->leftjoin('tblallocation_type', 'tblpaymentTransaction.allocationType', '=', 'tblallocation_type.ID')
				->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
				->leftjoin('voucher_type', 'voucher_type.vouchertypeID', '=', 'tblpaymentTransaction.voucher_type_deptID')
				->whereBetween('tblpaymentTransaction.dateCreated', [$request['startDate'] . " 00:00:00", $request['endDate'] . " 23:59:59"])
				->select('voucher_type.voucher_type_name as voucherType', 'tblpaymentTransaction.*', 'tblpaymentTransaction.ID as transID', 'tblcontractDetails.beneficiary as beneficiaryName', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tbleconomicCode.ID as economicCodeID', 'tblallocation_type.allocation', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions', DB::raw('tblcontractDetails.ID AS conID'))
				->orderBy('tblpaymentTransaction.accept_voucher_status', 'Asc')
				->orderBy('tblpaymentTransaction.ID', 'Desc')
				->paginate(50);
			$data['dateReturned'] = '   Searched Voucher(s): ' . $request['searchDate'];
		} else if ($data['vourcherid'] != '' or !empty($data['vourcherid'])) {
			$data['tablecontent'] = DB::table('tblpaymentTransaction')
				->where('tblpaymentTransaction.trackID', '=', null)
				->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
				->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
				->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
				->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
				->leftjoin('tblallocation_type', 'tblpaymentTransaction.allocationType', '=', 'tblallocation_type.ID')
				->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
				->leftjoin('voucher_type', 'voucher_type.vouchertypeID', '=', 'tblpaymentTransaction.voucher_type_deptID')
				->whereIn('tblpaymentTransaction.ID', $voucherNoArray)
				->select('voucher_type.voucher_type_name as voucherType', 'tblpaymentTransaction.*', 'tblpaymentTransaction.ID as transID', 'tblcontractDetails.beneficiary as beneficiaryName', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tbleconomicCode.ID as economicCodeID', 'tblallocation_type.allocation', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions', DB::raw('tblcontractDetails.ID AS conID'))
				->orderBy('tblpaymentTransaction.accept_voucher_status', 'Asc')
				->orderBy('tblpaymentTransaction.ID', 'Desc')
				->paginate(50);
			$data['numberRecordReturned'] = 'Total Voucher Found: ' . count($data['tablecontent']);
		}  else {
			$data['tablecontent'] = array();
		}


		foreach ($data['tablecontent'] as $key => $value) {
			$lis = (array) $value;
			$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
			$lis['comment'] = "";
			$com = DB::table('tblcomments')->where('affectedID', $value->ID)->orwhere('affectedID', $value->contractID)->get();

			if ($com) {

				foreach ($com as $k => $list) {
					$newline = (array) $list;
					//$name = DB::table('users')->where('username', $list->username)->first()->name;
					//$newline['name'] = $name;
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
		$data['economicCode'] = DB::table('tbleconomicCode')
			->join('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
			->leftjoin('tblcontractType', 'tblcontractType.ID', '=', 'tbleconomicCode.contractGroupID')
			->where('tbleconomicCode.status', 1)
			//->where('tblcontractType.ID', $contractType)
			->select('*', 'tbleconomicCode.ID as economicID', 'tbleconomicCode.description as economicName')
			->orderby('tbleconomicCode.economicCode', 'Asc')
			->get();
		$data['departmentName'] = strtoupper(substr($this->getUserRole()->rolename, 0, 1));

		return view('Liability.editVoucher', $data);
	}
	
	
	//search for voucher by recieving post requests from the the search and edit Voucher Page
	public function editVoucherRetireStatus(Request $request)
	{
		$id = $request->id;
		$retired = $request->retired;
		$description = $request->retire_description;

		DB::table('tblpaymentTransaction')->where('ID', $id)->update([
			'retired' => $retired,
			'retire_description' => $description
		]);

		return redirect()->back()->with('message', 'Voucher was edited successfully!!');
	}
	
}//end class
