<?php

namespace App\Http\Controllers\payroll;
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


class VoucherDisplayController extends BasefunctionController
{

	public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        // $this->activeMonth = $request->session()->get('activeMonth');
        // $this->activeYear = $request->session()->get('activeYear');
                return $next($request);
        });
    }


    public function createContractVoucher() 
	{
		Session::forget('getYear');
		Session::forget('getFrom');
		Session::forget('getTo');
		//
		$data['subdescriptions']  = $this->recurrentSubdescriptionCode();
	    $data['companyDetails'] = DB::table('tblcompany')->get();
	    $data['fileRefer'] = DB::table('tbldepartment_fileno')
	    	->where('tbldepartment_fileno.account_type', 'OVERHEAD COST')
	    	->orderby('tbldepartment_fileno.filerefer', 'Asc')
	    	->get();
		return view('capitalRecurrent.journal', $data);
	}
	
    public function deleteMultipleVoucher(Request $request) 
	{
    	$ids = $request->input('ids');
    
        if ($ids && is_array($ids)) {
            try {
                // Delete selected rows from the database
                DB::table('tblvoucherBeneficiary')->whereIn('ID', $ids)->delete();
    
                return response()->json(['success' => true]);
            } catch (\Exception $e) {
                return response()->json(['success' => false]);
            }
        }

        return response()->json(['success' => false]);
	
	}



    //VIEW VOUCHER RAISED
    //update Amount
    public function updateAmountVoucherBeneficiary($transactionID)
    {
    	$successOperation = 0;
        if(DB::table('tblvoucherBeneficiary')->where('voucherID', $transactionID)->first())
        {
            //Update other Tables
            $getTotalAmount = (DB::table('tblvoucherBeneficiary')->where('voucherID', $transactionID)->first() ? (DB::table('tblvoucherBeneficiary')->where('voucherID', $transactionID)->select('amount')->sum('amount')) : 0.00);

    		$successOperation = DB::table('tblpaymentTransaction')->where('ID', $transactionID)->update(array(
                'totalPayment' 	=> $getTotalAmount,
                'amtPayable' 	=> $getTotalAmount
            ));
        	//
            if(DB::table('tblcontractDetails')->where('ID', DB::table('tblpaymentTransaction')->where('ID', $transactionID)->value('contractID'))->first()){
        	     $successOperation = DB::table('tblcontractDetails')->where('ID', DB::table('tblpaymentTransaction')->where('ID', $transactionID)->value('contractID'))->update(array(
            	   'contractValue' => $getTotalAmount
                ));
            }
        }
        //
        return;
    }
    
    
    //VIEW NORMAL VOUCHER
	public function viewVoucher($transactionID, $printable='') 
	{	
	    $data['printable']=$printable;
	     $data['transactionID']=$transactionID;
	     
	    //////////////////////////////////
	        //Increase Memory Size
		    ini_set('memory_limit', '-1');
		//////////////////////////////////
		$checkForVoucherType  = DB::table('tblpaymentTransaction')
	    	->leftjoin('tblcontractDetails','tblcontractDetails.ID','=','tblpaymentTransaction.contractID')
	    	->leftjoin('tblcontractor','tblcontractor.id','=','tblpaymentTransaction.companyID')
			->where('tblpaymentTransaction.ID', '=', $transactionID)
			->value('tblcontractDetails.companyID as companyIDContractD');
		
		//Recompute Amount
		if($checkForVoucherType == 13 )
		{
		    $this->updateAmountVoucherBeneficiary($transactionID);
		}
		//////////////////////////////////
		
		Session::forget('transactionID');
		Session::forget('currentDivisionID');

		if(!DB::table('tblpaymentTransaction')->where('ID', $transactionID)->first()){
			return redirect('voucher/check')->with('err', 'Voucher not found !!! Sorry, we cannot view your Voucher.');
		}
				
		$data['list']  = DB::table('tblpaymentTransaction')
	    		->leftjoin('tblcontractDetails','tblcontractDetails.ID','=','tblpaymentTransaction.contractID')
	    		->leftjoin('tblcontractor','tblcontractor.id','=','tblpaymentTransaction.companyID')
				->leftJoin('tbleconomicCode','tbleconomicCode.ID','=','tblpaymentTransaction.economicCodeID')
				->leftJoin('tbleconomicHead','tbleconomicHead.ID','=','tbleconomicCode.economicHeadID')
				->leftJoin('tblcontractType','tblcontractType.ID','=','tblpaymentTransaction.contractTypeID')
				->leftjoin('voucher_type', 'voucher_type.vouchertypeID', '=', 'tblpaymentTransaction.voucher_type_deptID')
				->where('tblpaymentTransaction.ID', '=', $transactionID)
				->select('*', 'tbleconomicCode.economicCode', 'tbleconomicCode.ID as economicodeID', 'tbleconomicCode.description as economicName', 'tblpaymentTransaction.ID as transID','tblpaymentTransaction.status as payStatus','tblcontractType.contractType as ecoHead', 'tblcontractDetails.companyID as companyIDContractD', 'tblcontractDetails.beneficiary as beneficiaryContractD')
				->first();
				
				$data['contractID'] = $data['list']->contractID;
				$data['vstage'] = $data['list']->vstage;
	
		if($data['list'] == null){
			return redirect()->back()->with('err', 'Some thing went wrong when trying to generate voucher. Please try again');
		}
	    
	    if(DB::table('tblvoucherBeneficiary')->where('voucherID', $transactionID)->first())
	    {
	        $data['allStaffVoucherBenefitiary'] = DB::table('tblvoucherBeneficiary')
		        ->where('tblvoucherBeneficiary.voucherID', $transactionID)
		        ->orderBy('grade', 'Desc')
		        ->orderBy('step', 'Desc')
		        ->get();
	    }else{
	        $data['allStaffVoucherBenefitiary'] = array();
	    }
	    ////////////////////////////
	    //
		$b   = $this->VoucherFinancialInfo($transactionID);

		$data['bbf'] = ($b ? $b->BBF : 0.00); //Balance Brought Forward Before
		$data['contractAmount'] = ($b ? $b->contractValue : 0.00);// Total Contract Valaue

        $data['status'] = DB::table('tblstatus')->where('code', '=', $data['list']->payStatus)->first();

		$data['preparedBy'] = DB::table('users')->where('username', '=', $data['list']->preparedBy)->first();
		$data['libilityBy'] = DB::table('users')->where('username', '=', $data['list']->liabilityBy)->first();
		$data['checkBy']    = DB::table('users')->where('username', '=', $data['list']->checkBy)->first();
		$data['approvedBy'] = DB::table('users')->where('username', '=', $data['list']->approvedBy)->first();
		$data['vatpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->VATPayeeID)->first();
		$data['whtpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->WHTPayeeID)->first();
        
        ////////////////////VOUCHER DATA///////////////////
		
		//get economic code
		$data['getEconomicCode'] = ($data['list'] ? ($data['list']->Code . substr($data['list']->economicCode, 2)) : '');
		
		///===========================================================
		//VAT
		$data['hideShowVAT'] = '';
		$data['displayVAT'] = 'block';
		$data['displayStaffVAT'] = 'block';
		if($data['list']->VAT == null or $data['list']->VAT <= 0)
		{
		    $data['hideShowVAT'] = 'hidden';
		    $data['displayVAT'] = 'none';
		    $data['displayStaffVAT'] = 'none';
		}
		
		//STAMP DUTY
		$data['hideShowSTAMP'] = '';
		$data['displaySTAMP'] = 'block';
		$data['displayStaffSTAMP'] = 'block';
		if($data['list']->stampduty == null or $data['list']->stampduty <= 0)
		{
		    $data['hideShowSTAMP'] = 'hidden';
		    $data['displaySTAMP'] = 'none';
		    $data['displayStaffSTAMP'] = 'none';
		}
		
		//WHT
		if(($data['list']->WHT == null or $data['list']->WHT <= 0)) 
		{
			$hideShow = 'hidden';
			$visible = 'none';
			$displayStaff ='none';
		}else{
			$hideShow = '';
			$visible = 'block';
			$displayStaff ='block'; 
		}
		
		//STAFF LIST
		$data['displaySTAFF'] = 'block';
		$data['displayMainV'] = 'none';
		$data['hideShowSTAFF'] = '';
		$data['hideShowMainV'] = 'hidden';
		if(($data['list']->WHT > 0) || ($data['list']->stampduty > 0) || ($data['list']->stampduty > 0)) 
		{
			$data['displaySTAFF'] = 'none';
			$data['hideShowSTAFF'] = 'hidden';
			$data['displayMainV'] = 'block';
			$data['hideShowMainV'] = '';
		}
		///======================================================================
		
		
		 $data['economicCodeID'] = $data['list']->economicCodeID;
		 $data['voucherDate'] = $data['list']->datePrepared;
		 //$data['totalAmount'] = (($data['list']->VATValue + $data['list']->WHTValue + $data['list']->amtPayable) ? $data['list']->VATValue + $data['list']->WHTValue + $data['list']->amtPayable : 0);
		 $data['totalAmount'] =  $data['list']->totalPayment;
		 $data['VATValue'] = ($data['list']->VATValue ? $data['list']->VATValue : 0);
		 $data['WHTValue'] = ($data['list']->WHTValue ? $data['list']->WHTValue : 0);
		 $data['VATRate'] = ($data['list']->VAT ? $data['list']->VAT : 0);
		 $data['WHTRate'] = ($data['list']->WHT ? $data['list']->WHT : 0);
		 $data['amountPayable'] = ($data['list']->amtPayable ? ($data['list']->amtPayable ) : 0);
		 $data['preparedby'] = DB::table('users')->where('id', $data['list']->preparedBy)->value('name');
		 $data['transactionID']= $data['list']->transID;
		 $data['departmentName'] = strtoupper($data['list']->contractType);
		 $data['description'] = $data['list']->paymentDescription;
		 $data['filerefer'] = $data['list']->file_referID;
		 $data['adjustmentvoucher'] = 0;
		 $data['typevoucher'] = $data['list']->companyID;
		 $data['voucherStatus'] = $data['list']->accept_voucher_status;
		//
		////////////////////////////////////////////
		
	   /*	if(DB::table('tblvoucherBeneficiary')->where('voucherID', $data['list']->transID)->first())
		{
			$data['showStaffList'] = 'block';
			//Update Transaction Amount
			$getTotalAmount = DB::table('tblvoucherBeneficiary')->where('voucherID', $data['list']->transID)->select('amount')->sum('amount');
			//dd($getTotalAmount);
			$successOperation = DB::table('tblpaymentTransaction')->where('ID', $data['list']->transID)->update(array(
            	'totalPayment' 	=> $getTotalAmount,
            	'amtPayable' 	=> $getTotalAmount,
        	));
        	//
        	if(DB::table('tblcontractDetails')->where('ID', $data['list']->contractID)->first()){
    	       $successOperation = DB::table('tblcontractDetails')->where('ID', $data['list']->contractID)->update(array(
        	        'contractValue' => $getTotalAmount,
               ));
            }
        //
		}else{
			$data['showStaffList'] = 'none';
		}
		*/
		
		$AllTransactionStaff = DB::table('tblvoucherBeneficiary')
		    ->where('tblvoucherBeneficiary.voucherID', $data['list']->transID)
		    ->get();
		//
		$data['hideShow'] = $hideShow;
		$data['display'] = $visible;
		$data['displayStaff'] = $displayStaff;
		$data['list'] 		  = $data['list'];
		$data['desCode'] 	  = 'ECode';//$desCode->code;
		$data['transid'] 	  = $data['list']->transID;
		$data['subcode'] 	  = 'SubHead'; //$list->subcode;
		$data['transactionID'] 	        = $data['list']->transID;
		$data['isRetiredVoucher'] 	  = ($data['list']->to_be_retired == 1 ? 1 : 0);
		$data['payeeName'] 	            = '';
		$data['address'] 	            = '';
		$data['whtpayee'] 	            = DB::table('tblVATWHTPayee')->where('ID', $data['list']->WHTPayeeID)->value('payee');
		$data['whtpayeeaddress']        = DB::table('tblVATWHTPayee')->where('ID', $data['list']->WHTPayeeID)->value('address');
		$data['vatpayee'] 	            = DB::table('tblVATWHTPayee')->where('ID', $data['list']->VATPayeeID)->value('payee');
		$data['vatpayeeaddress']        = DB::table('tblVATWHTPayee')->where('ID', $data['list']->VATPayeeID)->value('address');
		$data['contractDescriptions'] 	= strtoupper($data['list']->economicName); //strtoupper($data['list']->ContractDescriptions);
		
		//
		if($data['list']->companyIDContractD <> 13 ){
		    $replacement = $data['list']->contractor;
			$data['payeeName'] = strtoupper($replacement);
			$data['address'] = strtoupper($data['list']->address); 
		}
		else{
			$replacement = $data['list']->beneficiaryContractD;
			$data['payeeName'] = strtoupper($replacement);
			$data['address'] = (empty($data['list']->payee_address) ? strtoupper($data['list']->address) : strtoupper($data['list']->payee_address)); //DB::table('report_head_logo')->value('company_short_name'); //$data['list']->address;
		}
		//
		$strArray = explode('Vide', $data['list']->paymentDescription);
		$newDscription = $strArray[0];
		if(stripos($newDscription, 'the above named officer for') !== false){
			$end = 'for';
		}else if(stripos($newDscription, 'the above named as') !== false){
			$end = 'as';
		}else if(stripos($newDscription, 'other as') !== false){
			$end = 'as';
		}else{
			$end = 'for';
		}   
		//
		if(stripos($newDscription, 'Being refund of expenses incurred by') !== false){
			$start = 'by';
		}else if(stripos($newDscription, 'Being refund of') !== false){
			$start = 'of';
		}else if(stripos($newDscription, 'Being payment of') !== false){
			$start = 'of';
		}else{
			$start = 'to';
		}
		$str = $newDscription;
		$needle_start = $start;
		$needle_end = $end;
		$replacement = $replacement;

		$pos = strpos($str, $needle_start);
		$start = $pos === false ? 0 : $pos + strlen($needle_start);

		$pos = strpos($str, $needle_end, $start);
		$end = $start === false ? strlen($str) : $pos;
		$data['descriptionJournal'] = substr_replace($str,' '.$replacement.' ',  $start, $end - $start);
		$data['newDscription'] = $newDscription;
		$data['principalOfficer'] = strtoupper(DB::table('tblprincipal_officer')->where('status', 1)->value('names'));
		/////////////////END VOUCHER DATA///////////////
        
        
        ///VERY VITAL /////Attach Staff To Voucher
         $currentDivisionID = Session::get('currentDivisionID');
	     $getAllJudgesOnly  = Session::get('getJudgesOnly');
	     (Session::get('getCurrentStaffList') ? $data['getAllStaff'] = Session::get('getCurrentStaffList') : $data['getAllStaff'] = []);
	     //get all list of division
	     $data['getDivision'] = DB::table('tbldivision')->get();
	     $data['currentDivision'] = (Session::get('currentDivisionName') ? Session::get('currentDivisionName') : 'Select Division');
	    
	    //Get All Staff Attached to voucher
	    $data['count'] = DB::table('tblvoucherBeneficiary')
		    ->leftjoin('tblbanklist','tblbanklist.bankID','=','tblvoucherBeneficiary.bankID')
          	->where('voucherID', '=', $data['list']->transID)
          	->count();

	    $data['staff'] = DB::table('tblvoucherBeneficiary')
		  	->leftjoin('tblbanklist','tblbanklist.bankID','=','tblvoucherBeneficiary.bankID')
          	->where('tblvoucherBeneficiary.voucherID', '=', $data['list']->transID)
          	->orderby('tblvoucherBeneficiary.grade', 'Desc')
		    ->orderby('tblvoucherBeneficiary.step', 'Desc')
          	->get();
          	
        $data['departmentName'] = strtoupper(substr($this->getUserRole()->rolename, 0, 1)); 
        $data['getDepartmentName'] = ((strtoupper($data['list']->department_voucher) == strtoupper($this->getUserRole()->rolename)) ? 'block' : 'none');
	    
	    $data['VoucherUnitCode'] = $this->UnitVoucher($data['list']->contractType);
	     return view($this->getUserLoggedInRole($data['list']->contractType), $data);
	     //return view('voucherDisplay.displayVoucher', $data);
	}
	//END VIEW OF NORMAL VOUCHER
    
     
    
    
    //VIEW RETIRE VOUCHER
	public function viewRetireVoucher($transactionID) 
	{	
	    //Increase Memory Size
		ini_set('memory_limit', '-1');
		
		Session::forget('transactionID');
		Session::forget('currentDivisionID');

		if(!DB::table('tblpaymentTransaction')->where('ID', $transactionID)->first()){
			return redirect('voucher/check')->with('err', 'Voucher not found !!! Sorry, we cannot view your Voucher.');
		}

		$data['list']  = DB::table('tblpaymentTransaction')
	    		->leftjoin('tblcontractDetails','tblcontractDetails.ID','=','tblpaymentTransaction.contractID')
	    		->leftjoin('tblcontractor','tblcontractor.id','=','tblpaymentTransaction.companyID')
				->leftjoin('tbleconomicCode','tbleconomicCode.ID','=','tblpaymentTransaction.economicCodeID')
				->leftjoin('tbleconomicHead','tbleconomicHead.ID','=','tbleconomicCode.economicHeadID')
				->leftjoin('tblcontractType','tblcontractType.ID','=','tblpaymentTransaction.contractTypeID')
				->leftjoin('voucher_type', 'voucher_type.vouchertypeID', '=', 'tblpaymentTransaction.voucher_type_deptID')
				->where('tblpaymentTransaction.ID', '=', $transactionID)
				->select('*', 'tbleconomicCode.economicCode', 'tbleconomicCode.ID as economicodeID', 'tbleconomicCode.description as economicName', 'tblpaymentTransaction.ID as transID', 'tblpaymentTransaction.status as payStatus','tblcontractType.contractType as ecoHead', 'tblcontractDetails.companyID as companyIDContractD', 'tblcontractDetails.beneficiary as beneficiaryContractD')
				->first();
				
		//////////////////////////////
		if($data['list'] == null){
			return redirect()->back()->with('err', 'Some thing went wrong when trying to generate voucher. Please try again');
		}
	    
	    if(DB::table('tblvoucherBeneficiary')->where('voucherID', $transactionID)->first())
	    {
	        $data['allStaffVoucherBenefitiary'] = DB::table('tblvoucherBeneficiary')
		        ->where('tblvoucherBeneficiary.voucherID', $transactionID)
		        ->get();
	    }else{
	        $data['allStaffVoucherBenefitiary'] = array();
	    }
	    ////////////////////////////
	    //
		$b   = $this->VoucherFinancialInfo($transactionID);

		$data['bbf'] = ($b ? $b->BBF : 0.00); //Balance Brought Forward Before
		$data['contractAmount'] = ($b ? $b->contractValue : 0.00);// Total Contract Valaue

        $data['status'] = DB::table('tblstatus')->where('code', '=', $data['list']->payStatus)->first();

		$data['preparedBy'] = DB::table('users')->where('username', '=', $data['list']->preparedBy)->first();
		$data['libilityBy'] = DB::table('users')->where('username', '=', $data['list']->liabilityBy)->first();
		$data['checkBy']    = DB::table('users')->where('username', '=', $data['list']->checkBy)->first();
		$data['approvedBy'] = DB::table('users')->where('username', '=', $data['list']->approvedBy)->first();
		$data['vatpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->VATPayeeID)->first();
		$data['whtpayee']   = DB::table('tblVATWHTPayee')->where('ID', '=', $data['list']->WHTPayeeID)->first();
        
        ////////////////////VOUCHER DATA///////////////////
		
		//get economic code
		$data['getEconomicCode']  = ($data['list'] ? ($data['list']->Code . substr($data['list']->economicCode, 2)) : '');
		
		if(($data['list']->WHT == null or $data['list']->WHT == 0)) 
		{
			$hideShow = 'hidden';
			$visible = 'none';
			$displayStaff ='none';
		}else{
			$hideShow = '';
			$visible = 'block';
			$displayStaff ='block'; 
		}
		//
		
		 $data['economicCodeID'] = $data['list']->economicCodeID;
		 $data['voucherDate'] = $data['list']->retire_date;
		 //$data['totalAmount'] = (($data['list']->VATValue + $data['list']->WHTValue + $data['list']->retire_amount) ? $data['list']->VATValue + $data['list']->WHTValue + $data['list']->retire_amount : 'Nill');
		 $data['totalAmount'] =  $data['list']->totalPayment;
		 $data['VATValue'] = ($data['list']->VATValue ? $data['list']->VATValue : 0);
		 $data['WHTValue'] = ($data['list']->WHTValue ? $data['list']->WHTValue : 0);
		 $data['VATRate'] = ($data['list']->VAT ? $data['list']->VAT : 0);
		 $data['WHTRate'] = ($data['list']->WHT ? $data['list']->WHT : 0);
		 $data['amountPayable'] = ($data['list']->amtPayable ? ($data['VATValue'] + $data['list']->amtPayable - $data['WHTValue']) : 0);
		 $data['preparedby'] = DB::table('users')->where('id', $data['list']->preparedBy)->value('name');
		 $data['transactionID']= $data['list']->transID;
		 $data['departmentName'] = strtoupper($data['list']->contractType);
		 $data['description'] = $data['list']->retire_description;
		 $data['filerefer'] = $data['list']->file_referID;
		 $data['adjustmentvoucher'] = 0;
		 $data['typevoucher'] = $data['list']->companyID;
		 $data['voucherStatus'] = $data['list']->status;
		//
		////////////////////////////////////////////
		if(DB::table('tblvoucherBeneficiary')->where('voucherID', $data['list']->transID)->first())
		{
			$data['showStaffList'] = 'block';
        
		}else{
			$data['showStaffList'] = 'none';
		}
		
		
		$AllTransactionStaff = DB::table('tblvoucherBeneficiary')
		    ->where('tblvoucherBeneficiary.voucherID', $data['list']->transID)
		    ->get();
		//
		$data['hideShow'] = $hideShow;
		$data['display'] = $visible;
		$data['displayStaff'] = $displayStaff;
		$data['list'] 		  = $data['list'];
		$data['desCode'] 	  = 'ECode';//$desCode->code;
		$data['transid'] 	  = $data['list']->transID;
		$data['subcode'] 	  = 'SubHead'; //$list->subcode;
		$data['transactionID'] 	        = $data['list']->transID;
		$data['isRetiredVoucher'] 	  = ($data['list']->to_be_retired == 1 ? 1 : 0);
		$data['payeeName'] 	            = '';
		$data['address'] 	            = '';
		$data['whtpayee'] 	            = DB::table('tblVATWHTPayee')->where('ID', $data['list']->WHTPayeeID)->value('payee');
		$data['whtpayeeaddress']        = DB::table('tblVATWHTPayee')->where('ID', $data['list']->WHTPayeeID)->value('address');
		$data['vatpayee'] 	            = DB::table('tblVATWHTPayee')->where('ID', $data['list']->VATPayeeID)->value('payee');
		$data['vatpayeeaddress']        = DB::table('tblVATWHTPayee')->where('ID', $data['list']->VATPayeeID)->value('address');
		$data['contractDescriptions'] 	= strtoupper($data['list']->economicName); //strtoupper($data['list']->ContractDescriptions);
		
		//
		if($data['list']->companyIDContractD <> 13 ){
		    $replacement = $data['list']->contractor;
			$data['payeeName'] = strtoupper($replacement);
			$data['address'] = strtoupper($data['list']->address);
		}
		else{
			$replacement = $data['list']->beneficiaryContractD;
			$data['payeeName'] = strtoupper($replacement);
			$data['address'] = DB::table('report_head_logo')->value('company_short_name'); //$data['list']->address;
		}
		//
		$strArray = explode('Vide', $data['list']->retire_description);
		$newDscription = $strArray[0];
		if(stripos($newDscription, 'the above named officer for') !== false){
			$end = 'for';
		}else if(stripos($newDscription, 'the above named as') !== false){
			$end = 'as';
		}else if(stripos($newDscription, 'other as') !== false){
			$end = 'as';
		}else{
			$end = 'for';
		}   
		//
		if(stripos($newDscription, 'Being refund of expenses incurred by') !== false){
			$start = 'by';
		}else if(stripos($newDscription, 'Being refund of') !== false){
			$start = 'of';
		}else if(stripos($newDscription, 'Being payment of') !== false){
			$start = 'of';
		}else{
			$start = 'to';
		}
		$str = $newDscription;
		$needle_start = $start;
		$needle_end = $end;
		$replacement = $replacement;

		$pos = strpos($str, $needle_start);
		$start = $pos === false ? 0 : $pos + strlen($needle_start);
            
		$pos = strpos($str, $needle_end, $start);
		$end = $start === false ? strlen($str) : $pos;
		$data['descriptionJournal'] = $data['list']->retire_description; //substr_replace($str,' '.$replacement.' ',  $start, $end - $start);
		$data['newDscription'] = $data['list']->retire_description; //$newDscription;
		/////////////////END VOUCHER DATA///////////////
        
        ///VERY VITAL /////Attach Staff To Voucher
         $currentDivisionID = Session::get('currentDivisionID');
	     $getAllJudgesOnly  = Session::get('getJudgesOnly');
	     (Session::get('getCurrentStaffList') ? $data['getAllStaff'] = Session::get('getCurrentStaffList') : $data['getAllStaff'] = []);
	     //get all list of division
	     $data['getDivision'] = DB::table('tbldivision')->get();
	     $data['currentDivision'] = (Session::get('currentDivisionName') ? Session::get('currentDivisionName') : 'Select Division');
	    //END GET STAFF LIST
	    
	    //Get All Staff Attached to voucher
	    $data['count'] = DB::table('tblvoucherBeneficiary')
		    ->leftjoin('tblbanklist','tblbanklist.bankID','=','tblvoucherBeneficiary.bankID')
          	->where('voucherID', '=', $data['list']->transID)
          	->count();

	   $data['staff'] = DB::table('tblvoucherBeneficiary')
		  	->leftjoin('tblbanklist','tblbanklist.bankID','=','tblvoucherBeneficiary.bankID')
          	->where('voucherID', '=', $data['list']->transID)
          	->get();
        $data['departmentName'] = strtoupper(substr($this->getUserRole()->rolename, 0, 1));
	    $data['getDepartmentName'] = ((strtoupper($data['list']->department_voucher) == strtoupper($this->getUserRole()->rolename)) ? 'block' : 'none');
	    
	    $data['staff'] = DB::table('tblvoucherBeneficiary')
		  	->leftjoin('tblbanklist','tblbanklist.bankID','=','tblvoucherBeneficiary.bankID')
          	->where('voucherID', '=', $data['list']->transID)
          	->orderby('tblvoucherBeneficiary.grade', 'Desc')
		    ->orderby('tblvoucherBeneficiary.step', 'Desc')
          	->get();
          	
	     //Preview Retire Voucher
	     //return view($this->getUserLoggedInRole($data['list']->contractType), $data);
	     return view('nicnModuleViews/subAccount/viewRetireVoucher', $data);
	}
	//END VIEW RETIRE VOUCHER



	
	//update Parameters
	public function updateStaffVoucherParameter(Request $request) 
	{
		$this->validate($request, [
			'levelThreeToSixRate' 			=> 'required|numeric',
			'levelSevenToElevenRate' 		=> 'required|numeric',
			'levelTwelveToFourteenRate' 		=> 'required|numeric',
			'levelFifteenToSeventeenRate' 		=> 'required|numeric',
		]);
		//
		$rate6   	          = (trim($request['levelThreeToSixRate']));
		$rate11  		  = (trim($request['levelSevenToElevenRate']));
		$rate14   	  	  = (trim($request['levelTwelveToFourteenRate']));
		$rate17   		  = (trim($request['levelFifteenToSeventeenRate']));
		$date  			  = date('Y-m-d');
		//
		DB::table('staffvoucherparameters')->where('id', 1)->update(array( 
			'rate' => $rate6, 'updated_at' => $date
		));

		DB::table('staffvoucherparameters')->where('id', 2)->update(array( 
			'rate' => $rate11, 'updated_at' => $date
		));

		DB::table('staffvoucherparameters')->where('id', 3)->update(array( 
			'rate' => $rate14, 'updated_at' => $date
		));

		DB::table('staffvoucherparameters')->where('id', 4)->update(array( 
			'rate' => $rate17, 'updated_at' => $date
		));
		$this->addLog('Staff voucher parameters was updated');
		return redirect('/CR/staff/voucher/create');
	}


	//edit voucher
	public function updateVoucher(Request $request) 
	{	
		$this->validate($request, [ 
			'economicCode' 		    => 'required|string',
			'newAmount' 		    => 'required|numeric',
			'beneficiary' 		    => 'required|string',
			'description' 		    => 'required|string', 
			'transactionID'			=> 'required|integer',
			'vatRate' 		    	=> 'integer',
			'vatPayee'              => 'required|string',
			'vatPayeeAddress'       => 'required|string',
			'whtRate' 		    	=> 'integer',  
			'whtPayee'              => 'required|string',
			'whtPayeeAddress'       => 'required|string',
			'todayDate'       		=> 'required|date',
		]);

		//
		$todayDate 		  			= (trim($request['todayDate']));
		$newAmount 		  			= (($request['newAmount']));
		$vatRate 		 			= (trim($request['vatRate']));
		$whtRate 		 			= (trim($request['whtRate']));
		$beneficiary 		 		= (trim($request['beneficiary']));
		$description 		  		= (trim($request['description']));
		$transactionID 		  		= (trim($request['transactionID']));
		$companyID 		  			= (trim($request['companyID']));
		$designation 		  		= (trim($request['designation']));
		$whtPayee 		  	  		= ucfirst(trim($request['whtPayee']));
		$whtPayeeAddress 	  		= ucfirst(trim($request['whtPayeeAddress']));
		$vatPayee 		  	  		= ucfirst(trim($request['vatPayee']));
		$vatPayeeAddress 	  		= ucfirst(trim($request['vatPayeeAddress']));
		$economicCodeRaw 		  	= (trim($request['economicCode']));
		//
		$strArray 			  = explode('/', $economicCodeRaw);
		$descriptionCode 	  = $strArray[0];
		$economicCode 	 	  = $strArray[1];
		//
		$group 			  	  = (DB::table('tblsubdescription')
							  ->join('tblbudget', 'tblbudget.subdescriptionID', '=', 'tblsubdescription.subdescriptionID')
							  ->join('tbldescription', 'tbldescription.descriptionID', '=', 'tblsubdescription.descriptionID')
							  ->select('tblbudget.budgetID', 'tblsubdescription.groupID')
							  ->where('tblsubdescription.subcode', '=', $economicCode)
							  ->where('tbldescription.code', '=', $descriptionCode)
							  ->first());
		if($group == ''){
			return back()->with('err', 'No Budget and allocation have been added for the selected Economic code:'.$economicCode);
		}
		$budgetID			  = $group->budgetID;
		$groupID 			  = $group->groupID;
		
		//double check parameters
		if($transactionID == '' or $transactionID < 1){
			return back()->with('err', 'This record cannot be updated due to internal error or try again later');
		}else if($newAmount == ''){
			return back()->with('err', 'Amount cannot be empty !');
		}

		if(($whtRate) == "" || ($whtRate) == 0)
		{
			$whtOrTax  		  = 0;
			$vatRate   		  = 0;
		}else{
			$whtOrTax  	 	  = ($whtRate);
			$vatRate   		  = $vatRate;
		}

		$grossAmount 		  = ($newAmount);
		$whtOrTax  	          = $whtOrTax;
		$calVat 		      = substr((($vatRate/100) * $grossAmount), 0, strpos((($vatRate/100) * $grossAmount), '.') + 12);
		$calWht 			  = substr((($whtOrTax/100) * $grossAmount), 0, strpos((($whtOrTax/100) * $grossAmount), '.') + 12);
		$totalAmount 		  = substr(($grossAmount + $calVat), 0, strpos(($grossAmount + $calVat), '.') + 12);
		$balance 			  = substr(($grossAmount - $calWht), 0, strpos(($grossAmount - $calWht), '.') + 12); 
		
		//current voucher
		$getOldInfo = DB::table('tbltransaction')
					 ->where('transactionID', $transactionID)
					 ->select('capital_track', 'amount', 'wht')
					 ->first();
		$getCapitalTrack = $getOldInfo->capital_track;
		//master voucher
		$getMasterVoucher = DB::table('tbltransaction')
			->where('transactionID', $getCapitalTrack)
			->where('capital_rank', 'A')
			->select('capital_totalamount')
			->first();
		$newBalance = 0;
		if($getMasterVoucher)
		{
			$oldBalance  = $getMasterVoucher->capital_totalamount;
			//get old gross amount : i.e amount + wht
			$oldGrossAmount = $getOldInfo->amount + $getOldInfo->wht;
			//calculate new balance for part-payment
			$newBalance  = $oldBalance + ($oldGrossAmount - $grossAmount);
			if($newBalance < 0)
			{
				return redirect('/capital/voucher/view')->with('err', 'Gross amount cannot be greater than total amount');
			}
		}
		//

		//if beneficiary is from company list
		if(($companyID != '') or ($companyID != 0)){
			DB::table('tblcompany')->where('companyID', $companyID)->update(array( 
				'companyname'  			=> $beneficiary
			));
			DB::table('tbltransaction')->where('transactionID', $transactionID)->update(array( 
				'description'  			=> $description,
				'amount' 				=> $balance,
				'totalamount' 			=> $totalAmount,
				'wht'  					=> ($calWht),
				'vat'  					=> ($calVat),
				'whtrate'  				=> $whtOrTax,
				'vatrate'  				=> $vatRate,
				'designation'  		    => $designation,
				'whtpayee'  			=> $whtPayee,
				'whtpayeeaddress'  		=> $whtPayeeAddress,
				'vatpayee'  			=> $vatPayee,
				'vatpayeeaddress'  		=> $vatPayeeAddress,
				'groupID'  				=> $groupID, 
				'date'  				=> $todayDate,
				'budgetID'  			=> $budgetID
			));
		}else{ // beneficiary is from staff, that is, payee
			DB::table('tbltransaction')->where('transactionID', $transactionID)->update(array( 
				'description'  			=> $description,
				'payee'  				=> $beneficiary, 
				'amount' 				=> $balance,
				'totalamount' 			=> $totalAmount,
				'wht'  					=> ($calWht),
				'vat'  					=> ($calVat),
				'whtrate'  				=> $whtOrTax,
				'vatrate'  				=> $vatRate,
				'designation'  		    => $designation,
				'vatpayee'  			=> $vatPayee,
				'vatpayeeaddress'  		=> $vatPayeeAddress,
				'whtpayee'  			=> $whtPayee,
				'whtpayeeaddress'  		=> $whtPayeeAddress,
				'groupID'  				=> $groupID, 
				'date'  				=> $todayDate,
				'budgetID'  			=> $budgetID
			));
			DB::table('transaction_voucher_staff')->where('transactionID', $transactionID)->update(array( 
				'economiccode'  		=> $economicCode
			));
		}
		if($getMasterVoucher)
		{
			DB::table('tbltransaction')
				->where('transactionID', '=', $getCapitalTrack)
				->where('capital_rank', 'A')
				->update(array( 
				'capital_totalamount'  => $newBalance,
			));
		}

		return redirect('/voucher/view')->with('msg', 'Voucher was updated successfully');		
	}



	//Replecate VOUCHER
	public function replicateVoucher(Request $request) 
	{	
		$this->validate($request, [  
			'voucherID' 		    => 'required|numeric',
			'amount' 		        => 'required|numeric',
			'economicCode' 		    => 'required|string',
			'todayDate'       		=> 'required|date',
		]);
		//
		$todayDate  		  = (($request['todayDate']));
		$transactionIDUpdate  = (trim($request['voucherID']));
		$description 		  = (trim($request['description']));
		$amount 		  	  = (($request['amount']));
		$economicCodeRaw 	  = (trim($request['economicCode']));
		//
		$strArray 			  = explode('/', $economicCodeRaw);
		$descriptionCode 	  = $strArray[0];
		$economicCode 	 	  = $strArray[1];
		//
		$group 			  	  = (DB::table('tblsubdescription')
							  ->join('tblbudget', 'tblbudget.subdescriptionID', '=', 'tblsubdescription.subdescriptionID')
							  ->join('tbldescription', 'tbldescription.descriptionID', '=', 'tblsubdescription.descriptionID')
							  ->select('tblbudget.budgetID', 'tblsubdescription.groupID')
							  ->where('tblsubdescription.subcode', '=', $economicCode)
							  ->where('tbldescription.code', '=', $descriptionCode)
							  ->first());
		if($group == ''){
			return back()->with('err', 'No Budget or allocation have been added for the selected Economic code:'.$economicCode);
		}
		$budgetID			  = $group->budgetID;
		$groupID 			  = $group->groupID;
		//$subName 			  = $group->subname;

		$getPrevData = DB::table('tbltransaction')->where('transactionID', $transactionIDUpdate)->first();
		//
		if(($getPrevData->whtrate) == "" || ($getPrevData->whtrate == 0))
		{
			$whtOrTax  		  = 0;
			$vatRate   		  = 0;
		}else{
			$whtOrTax  	 	  = (trim($getPrevData->whtrate));
			$vatRate   		  = (trim($getPrevData->vatrate));
		}
		$grossAmount 		  = $amount;
		$whtOrTax  	          = $whtOrTax;
		$calVat 		      = substr((($vatRate/100) * $grossAmount), 0, strpos((($vatRate/100) * $grossAmount), '.') + 12);
		$calWht 			  = substr((($whtOrTax/100) * $grossAmount), 0, strpos((($whtOrTax/100) * $grossAmount), '.') + 12);
		$totalAmount 		  = substr(($grossAmount + $calVat), 0, strpos(($grossAmount + $calVat), '.') + 12);
		$balance 			  = substr(($grossAmount - $calWht), 0, strpos(($grossAmount - $calWht), '.') + 12); 

		//insert
		$getDate = Carbon::now();
		$transactionID = DB::table('tbltransaction')->insertGetId(array( 
			'description'  			=> $description,
			'month'  				=> date('F'),
			'companyID'  			=> $getPrevData->companyID,
			'groupID'  				=> $groupID,
			'filerefer'  			=> $getPrevData->filerefer,
			'amount' 				=> $balance,
			'totalamount' 			=> $totalAmount,
			'year' 					=> date('Y'),
			'date' 					=> $todayDate,
			'budgetID'  			=> $budgetID,
			'wht'  					=> ($calWht),
			'vat'  					=> ($calVat),
			'whtrate'  				=> $whtOrTax,
			'vatrate'  				=> $vatRate,
			'preparedby'  			=> $getPrevData->preparedby,
			'vatpayee'  			=> $getPrevData->vatpayee,
			'whtpayee'  			=> $getPrevData->whtpayee,
			'vatpayeeaddress'  		=> $getPrevData->vatpayeeaddress,
			'whtpayeeaddress'  		=> $getPrevData->whtpayeeaddress,
			'typevoucher' 			=> $getPrevData->typevoucher,
			'payee' 				=> $getPrevData->payee,
			'address' 				=> $getPrevData->address,
			'created_at' 	 		=> date('Y-m-d'),
			'departmentname'   		=> 'Recurrent'
		));
		if(DB::table('transaction_voucher_staff')->where('transactionID', $transactionIDUpdate)->first())
		{
			$allAttachedNames = DB::table('transaction_voucher_staff')->where('transactionID', $transactionIDUpdate)->get();
			foreach ($allAttachedNames as $staffID) {
				# code...
				DB::table('transaction_voucher_staff')->insert(array( 
					'fileNo' 		=> $staffID->fileNo,
					'fileNonew' 	=> $staffID->fileNonew,
					'fullname' 		=> $staffID->fullname,
					'designation' 	=> $staffID->designation,
					'transactionID' => $transactionID,
					'division' 		=> $staffID->division,
					'divisionID' 	=> $staffID->divisionID,
					'economiccode' 	=> $economicCode,
					'grade' 		=> $staffID->grade,
					'step' 			=> $staffID->step,
					'amount' 		=> $staffID->amount,
					'bankname' 		=> $staffID->bankname,
					'accountno' 	=> $staffID->accountno,
					'sortcode' 		=> $staffID->sortcode,
					'addeddate' 	=> date('Y-m-d')
				));
			}
		}
		$this->addLog('A copy of a Voucher was created successfully');
		//
		if($transactionID <> ''){
			return redirect('/print/voucher/'.$transactionID)->with('msg', 'A copy of the seleted Voucher was created successfully');
		}else{
			return back()->with('err', 'A copy of this voucher cannot be created due to internal error (or Internet access). Try again later or contact your admin');
		}
		
	}


	public function SoftDelete(Request $request) 
	{	
		Session::forget('transactionID');
		Session::forget('currentDivisionID');

		$this->validate($request, [  
			'voucherID' 		    => 'required|numeric',
		]);
		//
		$voucherID  		        = (trim($request['voucherID']));
		//
		DB::table('tbltransaction')->where('transactionID', $voucherID)->update(array( 
			'voucher_status'  		=> (0),
		));
		//
		$this->addLog('Voucher Deleted (SoftDelete) successfully from Recurrent');
		return redirect('/voucher/view')->with('msg', 'Voucher was DELETE successfully');	
	}


	public function addNewStaffToListVoucher(Request $request) 
	{	
		$this->validate($request, [  
			'fileNo' 		    => 'numeric',
			'fullName' 		    => 'required|regex:/^[a-zA-Z0-9,.!?\)\( ]*$/',
			//'bankName' 		=> 'alpha_num',
			//'accountNo'      	=> 'numeric',
			//'sortCode'       	=> 'numeric',
			'designation'       => 'string',
			'grade'       		=> 'numeric',
			'step'       		=> 'numeric',
			'division'       	=> 'alpha_num',
			'amount'       		=> 'numeric',
		]);
		//
		$fileNo  		  		= (($request['fileNo']));
		$fullName  				= (trim($request['fullName']));
		$bankName 		  		= (trim($request['bankName']));
		$accountNo 		  	  	= (($request['accountNo']));
		$sortCode 		  		= (trim($request['sortCode']));
		$designation  		  	= (($request['designation']));
		$grade  				= (trim($request['grade']));
		$step  					= (trim($request['step']));
		$division 		  		= (trim($request['division']));
		$amount 		  	  	= (($request['amount']));
		$transactionID 		  	= (($request['voucherID']));
		$economicCode 		  	= (($request['economicCode']));
		
		//get last inserted
		$fileNoTrack = DB::table('transaction_voucher_staff')
			->where('transactionID', '=', $transactionID)
			->where('economiccode', '=', $economicCode)
			->where('fileNo', '=', null)
			->where('divisionID', '=', null)
			->orderby('fileNonew', 'Desc')
			->first();
		if($fileNoTrack == ''){
			$nextFileNo = (1);
		}else{
			$nextFileNo = (($fileNoTrack->fileNonew) + 1);	
		}

		DB::table('transaction_voucher_staff')->insert(array( 
			'fileNonew'  		=> ($nextFileNo),
			'fullName'  		=> ($fullName),
			'designation'  		=> ($designation),
			'grade'  			=> ($grade),
			'step'  			=> ($step),
			'division'  		=> ($division),
			'amount'  			=> ($amount),
			'transactionID'  	=> ($transactionID),
			'economiccode'  	=> ($economicCode),
			'bankname'  		=> $bankName,
			'accountno'  		=> $accountNo,
			'sortcode'  		=> $sortCode,
			'addeddate'  		=> (date('Y-m-d'))
		));

		$this->addLog('New Beneficiary Details are successfully added to list from Recurrent');
		//return redirect('/print/voucher/'.$transactionID )->with('msg', 'New Beneficiary Details are successfully added to list');	
		return back()->with('msg', 'New Beneficiary Details are successfully added to list');	
	}



	public function updateVoucherAmountInStaffList(Request $request) 
	{	
		$this->validate($request, [  
			'staffSelectedChecked' => 'required|array',
		]);

		$staffAmount		  	= (($request['staffAmount']));
		$staffID		  	  	= (($request['staffSelectedChecked'])); 
		$voucherID		  	  	= (($request['voucherID']));

		$i = 0;
		//get amount as an array
		foreach ($staffAmount as $amount) {
		    $arrayAmount[] = $amount;
		}

		foreach($staffID as $val)
		{	
		   	DB::table('transaction_voucher_staff')
		    	->where('id', '=', $val)
		    	->update(array( 
					'amount' => ($arrayAmount[$i])
			)); 
		    $i ++;
		}
		
		/*foreach($staffID as $val)
		{	
		   if(DB::table('transaction_voucher_staff')->where('id', $val)->where('transactionID', $voucherID)->first()){
		   		DB::table('transaction_voucher_staff')
		    	->where('id', '=', $val)
		    	->update(array( 
					'amount' => ($arrayAmount[$i])
				)); 
		   }
		   $i ++;
		}*/
		$this->addLog('Beneficiaries Amount and/or Bank Details were updated successfully');
		return redirect('/print/voucher/'.$voucherID )->with('msg', 'Beneficiaries Details were updated successfully');	
	}



	public function storePartPayment(Request $request)
    { 
    	
		$this->validate($request, [
			'amount' 				=> 'required|numeric',
			'economicCode' 		    => 'required|string',
			'vatPayee'              => 'required|string',
			'whtPayee'              => 'required|string',
			'vatPayeeAddress'       => 'required|string',
			'whtPayeeAddress'       => 'required|string', 
			'todayDate'       		=> 'required|date', 
		]);
		//Assign 
		$todayDate			  = trim($request['todayDate']);
		$capitalTotalAmount   = ($request['totalAmount']);
		$vatselect 			  = trim($request['vatselect']);
		$grossAmount   		  = (trim($request['amount']));
		$description 		  = ucfirst(trim($request['narration']));
		$vatPayee   		  = ucfirst(trim($request['vatPayee']));
		$whtPayee 		  	  = ucfirst(trim($request['whtPayee']));
		$vatPayeeAddress 	  = ucfirst(trim($request['vatPayeeAddress']));
		$whtPayeeAddress 	  = ucfirst(trim($request['whtPayeeAddress'])); 
		$economicCodeRaw 		  = (trim($request['economicCode']));  
		$capitalRank 		  = (trim($request['capitalRank']));
		$capital_track 		  = (trim($request['capital_track']));
		//
		$strArray 			  = explode('/', $economicCodeRaw);
		$descriptionCode 	  = $strArray[0];
		$economicCode 	 	  = $strArray[1];
		//
		$group 			  	  = (DB::table('tblsubdescription')
							  ->join('tblbudget', 'tblbudget.subdescriptionID', '=', 'tblsubdescription.subdescriptionID')
							  ->join('tbldescription', 'tbldescription.descriptionID', '=', 'tblsubdescription.descriptionID')
							  ->select('tblbudget.budgetID', 'tblsubdescription.groupID')
							  ->where('tblsubdescription.subcode', '=', $economicCode)
							  ->where('tbldescription.code', '=', $descriptionCode)
							  ->first());
		if($grossAmount > $capitalTotalAmount){
			return redirect('voucher/view')->with('err', 'Gross Amount cannot be greater than the Total balance left !  Please review and try again.');
		}
		//
		if($group == ''){
			return redirect('voucher/view')->with('err', 'No Budget and allocation have been added for the selected Economic code:'.$economicCode);
		}

		$budgetID			  = $group->budgetID;
		$groupID 			  = $group->groupID;
		$year 			      = (date('Y'));
		$month  	          = (date('F'));

		if(($request['whtOrTax']) == "" || ($request['whtOrTax']) == 0)
		{
			$whtOrTax  		  = 0;
			$vatRate   		  = 0;
		}else{
			$whtOrTax  	 	  = (trim($request['whtOrTax']));
			$vatRate   		  = $vatselect;
			
		}

		$grossAmount 		  = $grossAmount;
		$whtOrTax  	          = $whtOrTax;
		$calVat 		      = substr((($vatRate/100) * $grossAmount), 0, strpos((($vatRate/100) * $grossAmount), '.') + 12);
		$calWht 			  = substr((($whtOrTax/100) * $grossAmount), 0, strpos((($whtOrTax/100) * $grossAmount), '.') + 12);
		$totalAmount 		  = substr(($grossAmount + $calVat), 0, strpos(($grossAmount + $calVat), '.') + 12);
		$balance 			  = substr(($grossAmount - $calWht), 0, strpos(($grossAmount - $calWht), '.') + 12);  

		//
		$getPrevDetails  = DB::table('tbltransaction')
						 ->where('capital_track', $capital_track)
						 ->where('departmentname', 'Recurrent')
						 ->orderBy('capital_rank', 'Asc')
						 ->first();
		
		if(($capitalTotalAmount == '') || ($capitalTotalAmount == 0) || ($capitalTotalAmount < 1))
		{
			$capital_totalamount = 0; 
			$capital_rank = '';
			
		}else{
			$capital_totalamount = ($capitalTotalAmount - $grossAmount);
			$getMAXRank  = DB::table('tbltransaction')
						  ->where('capital_track', $capital_track)
						  ->where('departmentname', 'Recurrent')
						  ->orderBy('capital_rank', 'Desc')
						  ->first();
			$capital_rank   = chr(ord( $getMAXRank->capital_rank )+1);
		}
		
		//
		$getDate = Carbon::now();
		$transactionID = DB::table('tbltransaction')->insertGetId(array( 
			'description'  			=> $description,
			'month'  				=> $month,
			'companyID'  			=> $getPrevDetails->companyID,
			'groupID'  				=> $groupID,
			'filerefer'  			=> $getPrevDetails->filerefer,
			'amount' 				=> $balance,
			'totalamount' 			=> $totalAmount,
			'year' 					=> $year,
			'date' 					=> $todayDate,
			'budgetID'  			=> $budgetID,
			'wht'  					=> ($calWht),
			'vat'  					=> ($calVat),
			'whtrate'  				=> $whtOrTax,
			'vatrate'  				=> $vatRate,
			'preparedby'  			=> $getPrevDetails->preparedby,
			'vatpayee'  			=> $vatPayee,
			'whtpayee'  			=> $whtPayee,
			'vatpayeeaddress'  		=> $vatPayeeAddress,
			'whtpayeeaddress'  		=> $whtPayeeAddress, 
			'typevoucher' 			=> $getPrevDetails->typevoucher,
			'capital_totalamount' 	=> $capital_totalamount,
			'capital_rank' 			=> $capital_rank,
			'capital_track'  		=> $capital_track,
			'created_at' 	 		=> date('Y-m-d'),
			'departmentname'  		=> 'Recurrent'
		));
		DB::table('tbltransaction')
		->where('capital_track', '=', $capital_track)
		->where('departmentname', 'Recurrent')
		->update(array( 
			'capital_totalamount'  		=> $capital_totalamount
		));
		$this->addLog('Another Part Payment ' . $capital_rank . ' Voucher was created successfully');

		return redirect('/print/voucher/'.$transactionID)->with('msg', 'Another Part Payment ' . $capital_rank . ' Voucher was created successfully');
	}


	//Revert part payment voucher
	public function revertPartPaymentPaid(Request $request)
	{

		$this->validate($request, [
			'voucherNumber' 		=> 'required|numeric'
		]);
		$this_transactionID =  trim($request['voucherNumber']);
		$getDetailsTransaction  = DB::table('tbltransaction')->where('transactionID', $this_transactionID)
			->select('capital_track', 'totalamount', 'wht')
			->first();
		//
		$capitalTrack 		= $getDetailsTransaction->capital_track;
		$totalamount 		= $getDetailsTransaction->totalamount;
		$vat 				= $getDetailsTransaction->wht;
		//
		$getMasterTotalAmount  = DB::table('tbltransaction')->where('transactionID', $capitalTrack)->select('capital_totalamount')->first();
		$newBalanceCapitalTotalAmount = (($totalamount - $vat) + ($getMasterTotalAmount->capital_totalamount));
		//update Master Voucher -Main capital
		$masterVoucher = DB::table('tbltransaction')
			->where('transactionID', '=', $capitalTrack)
			->update(array( 
				'capital_totalamount' 	  => $newBalanceCapitalTotalAmount
		));
		$updateAllInstance  = DB::table('tbltransaction')->where('capital_track', $capitalTrack)->get();
		foreach ($updateAllInstance as $value) {
			# code...
			$masterVoucher = DB::table('tbltransaction')
				->where('capital_track', '=', $value->capital_track)
				->update(array( 
					'capital_totalamount' => $newBalanceCapitalTotalAmount
			));
		}
		//Delete reverted voucher - delete from all instances
		$data_tran_approve = DB::table('tbltransaction_approval')->where('transaction_id', $this_transactionID)->delete();
		$data_staff        = DB::table('transaction_voucher_staff')->where('transactionID', $this_transactionID)->delete();
		$data_tran         = DB::table('tbltransaction')->where('transactionID', $this_transactionID)->delete();

		return redirect('recurrent/all-partpayment/voucher/'.$capitalTrack)->with('msg', 'Voucher reverted successfully and voucher was also deleted from the system');
	}



	public function viewAllPartPayment($transactionID = null) 
	{	
		Session::forget('getYear');
		Session::forget('getFrom');
		Session::forget('getTo');
		//
		Session::forget('transactionID');
		Session::forget('currentDivisionID');

		$data['getVoucher']  = DB::table('tbltransaction')
	    		->leftjoin('tblcompany','tblcompany.companyID','=','tbltransaction.companyID')
				->Join('tblgroup','tblgroup.groupID','=','tbltransaction.groupID')
				->Join('tblbudget','tblbudget.budgetID','=','tbltransaction.budgetID')
				->Join('tblsubdescription','tblsubdescription.subdescriptionID','=','tblbudget.subdescriptionID')
				->where('tbltransaction.voucher_status', '=', (1))
				->where('tbltransaction.capital_track', '=', $transactionID)
				->select('*', 'tbltransaction.date')
				->orderBy('tbltransaction.transactionID','DESC')
				->get();
		$data['subdescriptions']  = $this->recurrentSubdescriptionCode();
	    //
	    $data['allPartPayment'] = '';

		return view('capitalRecurrent.viewPartPayment', $data);
	}



	public function viewAllBeneficiaryPartPayment()
	{
		$data['getVoucher']  = DB::table('tbltransaction')
	    		->leftjoin('tblcompany','tblcompany.companyID','=','tbltransaction.companyID')
				->Join('tblgroup','tblgroup.groupID','=','tbltransaction.groupID')
				->Join('tblbudget','tblbudget.budgetID','=','tbltransaction.budgetID')
				->Join('tblsubdescription','tblsubdescription.subdescriptionID','=','tblbudget.subdescriptionID')
				->where('tbltransaction.voucher_status', '=', (1))
				->where('tbltransaction.capital_totalamount', '>', 0)
				->where('tbltransaction.departmentname', '=', 'Recurrent')
				->select('*', 'tbltransaction.date')
				->orderBy('tbltransaction.transactionID','DESC')
				->get();
		$data['subdescriptions']  = $this->recurrentSubdescriptionCode();
	    //
	    $data['allPartPayment'] = 'All Part Payment';

		return view('capitalRecurrent.viewPartPayment', $data);
	}


	//===NOTE:=====/THIS CODE SNIPPERS ARE FOR SUB-ACCOUNT AND RECURRENT========For deleting staff from staf List on voucher////

	public function deleteStaffFromListJSON(Request $request) 
	{	
		$this->validate($request, [  
			'staffIdList' => 'numeric',
		]);
		$staffIdList		  	= (($request['staffIdList']));
		$data = DB::table('transaction_voucher_staff')->where('id', $staffIdList)->delete();

		return response()->json($data);
		 
	}


	public function updateBankDetailsFromVoucherStaffList(Request $request) 
	{
		//Update Staff Bank Details: CPO
		$this->validate($request, [
			'bankName' 		  => 'regex:/[a-zA-Z.]/',
			'accountNumber'   => 'numeric',
			'sortCode'        => 'numeric',
			'staffAmount'     => 'numeric',
			'id'     		  => 'numeric',
		]);
		//Assign 
		$bankName		  = trim($request['bankName']);
		$accountNumber    = trim($request['accountNumber']);
		$sortCode 		  = trim($request['sortCode']);
		$staffAmount   	  = trim($request['staffAmount']);
		$staffRecordID    = trim($request['id']);
		$getRecord		  = (DB::table('transaction_voucher_staff')->where('id', '=', $staffRecordID)->first()); //for redirect back
		$voucherID        = $getRecord->transactionID;
		//update $this-record
		$data = DB::table('transaction_voucher_staff')
			->where('id', '=', $staffRecordID)
			->update(array( 
				'amount' 	  => $staffAmount,
				'bankname' 	  => $bankName,
				'accountno'   => $accountNumber,
				'sortcode'    => $sortCode
		)); 
		return response()->json($data);

	}
	//end=========/THIS CODE SNIPPERS ARE FOR SUB-ACCOUNT AND RECURRENT========////




	//SEARCH BY VOUCHER NUMBER
	public function searchByVoucherNumberRecurrent(Request $request)
	{
		$this->validate($request, [
			'voucherNumber'          => 'required|numeric',
		]);
		$voucherNumber =  trim($request['voucherNumber']);
		if($voucherNumber == '')
		{
			return redirect('/voucher/view');
		}
		if(!DB::table('tbltransaction')->where('tbltransaction.transactionID', '=', $voucherNumber)->where('tbltransaction.voucher_status', '=', (1))->first())
		{
			return redirect('/voucher/view')->with('err', 'Sorry, we cannot find the voucher you are looking for! Perhaps, the voucher number you entered is wrong. Check and try again');
			
		}else
		{
			$data['getVoucher']  = DB::table('tbltransaction')
	    		->leftjoin('tblcompany','tblcompany.companyID','=','tbltransaction.companyID')
				->Join('tblgroup','tblgroup.groupID','=','tbltransaction.groupID')
				->Join('tblbudget','tblbudget.budgetID','=','tbltransaction.budgetID')
				->Join('tblsubdescription','tblsubdescription.subdescriptionID','=','tblbudget.subdescriptionID') 
				->where('tbltransaction.departmentname', '=', ('Recurrent'))
				->where('tbltransaction.voucher_status', '=', (1))
				->where('tbltransaction.transactionID', $voucherNumber)
				->select('*', 'tbltransaction.date')
				->orderBy('tbltransaction.transactionID','DESC')
				->paginate(30);
			$data['subdescriptions']  = $this->recurrentSubdescriptionCode();
			return view('capitalRecurrent.allVouchers', $data);
		}
		
	}
	//



	
}//End class
