<?php

namespace App\Http\Controllers\payroll;



use Illuminate\Http\Request;
use App\Http\Requests;
use File;
use Carbon\carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;

class UpdatePaymentController extends BaseParentController
{

    public function __construct()
    {
        $this->middleware('auth');
    }


  //create
   public function createPaymentKickOff(Request $request)
   {
       $period=DB::table('tblactiveperiod')->value('year');
   	 $getAllType = $this->getContractAllocationType();
	 $data['contractType1']  = $request['contractType1'];
	 //dd($request['contractType1']);
	 $contT=$data['contractType1']  ;
	 $data['contractType']  = $getAllType['contractType'];
	 $data['allocationType'] = $getAllType['allocationType'];
	 if ( isset( $_POST['reload'] ) ) {
	     if($contT!=''){Session::put('contT', $contT); 
	     
	     }else{
	        $contT=Session::get('contT'); 
	     }
	        return redirect('/update-payment-transaction');
	     		return redirect()->route('createUpdatePayment')->with('message', '');
	     	//return redirect()->route('createUpdatePayment');
	 }
	 $contT=Session::get('contT');
	 $data['record'] = DB::Select("SELECT tblpaymentTransaction.*,tblcontractType.contractType,tblallocation_type.allocation,tbleconomicCode.economicCode ,tbleconomicCode.description   ,tblpaymentTransaction.ID as recordID, tblallocation_type.ID as allID, tblcontractType.ID as conID, tbleconomicCode.ID as ecoCodeID
	 FROM `tblpaymentTransaction` join tbleconomicCode on tbleconomicCode.id=tblpaymentTransaction.`economicCodeID` join tbleconomicHead on tbleconomicHead.ID=tbleconomicCode.economicHeadID join tblallocation_type on tblallocation_type.ID=tbleconomicCode.allocationID join tblcontractType on tblcontractType.ID = tblpaymentTransaction.contractTypeID
	 WHERE tblpaymentTransaction.trackID<>'' and `period`='$period'
	 and exists( select null FROM `tbleconomicCode` WHERE `tbleconomicCode`.`ID`=tblpaymentTransaction.`economicCodeID` and tbleconomicCode.`contractGroupID`='$contT') ");
// 	 $data['record'] = DB::table('tblpaymentTransaction')->where('tblpaymentTransaction.trackID', '<>', null)
// 	    	->join('tblallocation_type', 'tblallocation_type.ID', '=', 'tblpaymentTransaction.allocationType')
//             	->join('tblcontractType', 'tblcontractType.ID', '=', 'tblpaymentTransaction.contractTypeID')
//             	->join('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
//             	->select('*', 'tblpaymentTransaction.ID as recordID', 'tblallocation_type.ID as allID', 'tblcontractType.ID as conID', 'tbleconomicCode.ID as ecoCodeID' ) 
//             	->orderBy('tblpaymentTransaction.ID', 'Desc')
// 	    	->get();
	 (Session::get('edit') ? $data['edit'] = Session::get('edit') : '');
	 
         return view('UpdatePayment.home', $data);
   }
   
   
    //save/update
    public function SavePaymentKickOff(Request $request)
    {

        $this->validate($request, [
          'contractType'      	=> 'required|alpha_num',
          'allocationType'    	=> 'required|alpha_num',
          'economicCode'      	=> 'required|string', //|unique:tbleconomicCode,ID,'.$this->trackID.',NULL,> 0,
          'totalPaymnet'    	=> 'required|string',
          'paymentDescription'  => 'required|string',
          'cutOffDate'    	=> 'required|date',
        ]);
        //
        $editRecordID = trim($request['recordID']);
        $timestamp = strtotime( $request['cutOffDate'] );  
	$getDate = date('Y-m-d', $timestamp );
        $message = "Sorry, you cannot submit your record now! Please try again.";
        $success = 0;
        $checkEconomicCode = DB::table('tblpaymentTransaction')
        	->join('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
        	->where('tbleconomicCode.ID', $request['economicCode'])
        	->where('tblpaymentTransaction.period', DB::table('tblactiveperiod')->value('year'))
        	->where('tblpaymentTransaction.trackID', '<>', null)
        	->first();
        
        
        if($editRecordID)
        {	$success = 1;
           	DB::table('tblpaymentTransaction')->where('ID', $editRecordID)->update([
		   'contractTypeID' 	=> $request['contractType'],
		   'totalPayment' 	=> $request['totalPaymnet'],
		   'paymentDescription' => $request['paymentDescription'],
		   'preparedBy' 	=> Auth::User()->id,
		   'allocationType' 	=> $request['allocationType'],
		   'economicCodeID' 	=> $request['economicCode'],
		   'status' 		=> 6,
		   'datePrepared' 	=> $getDate,
		   'period' 		=> DB::table('tblactiveperiod')->value('year'),
		   'cpo_payment' 	=> 1,
		   'cpo_payment_date' 	=> $getDate,
		   'checkbyStatus' 	=> 1,
		   'mandate_status' 	=> 1,
		   'vstage' 		=> 4,
		   'VAT' 		=> 0,
		   'VATValue' 		=> 0,
		   'WHT' 		=> 0,
		   'WHTValue' 		=> 0,
		   'amtPayable' 	=> 0,
		   'pay_confirmation' 	=> 1,
		   'accept_voucher_status' 	=> 1,
 		]);
 		Session::forget('edit');
 	}else{
 	    if($checkEconomicCode)
        {
        	return back()->with('error', 'The economic code has already been taken.');
        }
 		Session::forget('edit');
 		$success = DB::table('tblpaymentTransaction')->insertGetId([
		   'contractTypeID' 	=> $request['contractType'],
		   'totalPayment' 	=> $request['totalPaymnet'],
		   'paymentDescription' => $request['paymentDescription'],
		   'preparedBy' 	=> Auth::User()->id,
		   'allocationType' 	=> $request['allocationType'],
		   'economicCodeID' 	=> $request['economicCode'],
		   'status' 		=> 6,
		   'datePrepared' 	=> $getDate,
		   'period' 		=> DB::table('tblactiveperiod')->value('year'),
		   'cpo_payment' 	=> 1,
		   'cpo_payment_date' 	=> $getDate,
		   'checkbyStatus' 	=> 1,
		   'mandate_status' 	=> 1,
		   'vstage' 		=> 4,
		   'VAT' 		=> 0,
		   'VATValue' 		=> 0,
		   'WHT' 		=> 0,
		   'WHTValue' 		=> 0,
		   'amtPayable' 	=> 0,
		   'pay_confirmation' 	=> 1,
		   'accept_voucher_status' 	=> 1,
		   'trackID' 		=> DB::table('tblpaymentTransaction')->orderBy('ID', 'Desc')->value('ID') +1,
 		]);
 	}
 	if($success)
 	{	 $message = "Your payment information was added successfully";
 		return redirect()->route('createUpdatePayment')->with('message', $message);
 	}else{
 		return redirect()->route('createUpdatePayment')->with('error', $message );
 	}
    }
 	
 	
    // show edit data
    public function edit($ID)
    {
        if(DB::table('tblpaymentTransaction')->where('ID', $ID)->first()){
	    $editData = DB::table('tblpaymentTransaction')->where('tblpaymentTransaction.ID', $ID)
	    	->join('tblallocation_type', 'tblallocation_type.ID', '=', 'tblpaymentTransaction.allocationType')
            	->join('tblcontractType', 'tblcontractType.ID', '=', 'tblpaymentTransaction.contractTypeID')
            	->join('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblpaymentTransaction.economicCodeID')
            	->select('*', 'tblpaymentTransaction.ID as recordID', 'tblallocation_type.ID as allID', 'tblcontractType.ID as conID', 'tbleconomicCode.ID as ecoCodeID')
	    	->first();
            Session::put('edit', $editData);
        }else{
            Session::forget('edit');
        }
        //return redirect('/update-payment-transaction');
        return redirect()->route('createUpdatePayment');
        
    }

    // cancel edit
    public function cancelEdit()
    {
        Session::forget('edit');

        return redirect()->route('createUpdatePayment');
    }
    
    //Delecte User
    public function removeRecord($ID)
    {
        $success = 0;
        if(DB::table('tblpaymentTransaction')->where('ID', $ID)->first()){
            $success = DB::table('tblpaymentTransaction')->where('ID', $ID)->delete();
        }
        if($success){
            return redirect()->route('createUpdatePayment')->with('message', 'Your record has been deleted succefully');
        }
        return redirect()->route('createUpdatePayment')->with('error', 'Sorry, we cannot delete this record from our system.');
        
    }
 		
  

 }//end class
