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


class AuditorController extends function24Controller
{
	public function audit(Request $request){
		//dd(Auth::user());
	    $data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
	    
	    $data['contractlist'] = $this->getContract();
	    $data['statuslist'] = DB::table('tblstatus')->get();
	    $data['contracttype'] = ($request['contracttype'] == "") ? $request['contracttype2'] : $request['contracttype'];
	    $data['contracttype2'] = $request['contracttype'];
	    //dd($data['contracttype2']);
	    $data['status'] = ($request['status'] == "") ? $request['status2'] : $request['status'];
	    $data['status2'] = $request['status'];


	  	

	   	if($request['reason'] == 2){
	   		$id = $request['chosen1'];
	  		if(DB::table('tblpaymentTransaction')->where('ID', $id)->update([
	  			'auditStatus' 			=> '',
	  			'auditDate' 				=> '',
	  			'auditedBy'				=> '',
	  			'checkBy'				=> '',
	  			'checkbyStatus'				=> '',
	  			'liabilityBy'				=> '',
	  			'liabilityStatus'			=> ''
	  		])){
	  			$data['success'] = "Voucher has been rejected successfully";
	  			$theid =$id;
	  		        $user = Auth::user()->username;
	  		        $commenttypeID = DB::table('tblcommenttype')->where('type', 'voucherstage')->where('status', 1)->first()->id;
	  		        $comment = trim($request['declinemess']);
	  		        //dd($comment);
	  		        DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $theid, 'username' => $user, 'comment' => $comment]);
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
	    						->where('tblpaymentTransaction.contractTypeID', '=', $request['contracttype2'])
	    						->orwhere('tblpaymentTransaction.contractTypeID', '=', $request['contracttype'])
	    						->orwhere('tblpaymentTransaction.status', '=', $request['status'])
	    						->where('tblpaymentTransaction.status', '=', $request['status2'])
	    						->where('tblpaymentTransaction.dateTakingLiability', "<>", "")
	    						->where('tblpaymentTransaction.liabilityStatus', "=", 1)
	    						->where('tblpaymentTransaction.dateCheck', "<>", "")
	    						->where('tblpaymentTransaction.checkbyStatus', "=", 1)
	    						->select('tblpaymentTransaction.*', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tblallocation_type.allocation', 'tblcontractDetails.contractValue', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions', DB::raw('tblcontractDetails.ID AS conID'))
	    						->orderBy('tblpaymentTransaction.ID', 'desc')
	    						->orderBy('auditStatus', 'asc')
	    						->orderBy('dateAward', 'asc')
	    						->get();
	    						
	    if($request['reason'] == 1){
	  		//$id = $request['paymentTransID'];
	  		DB::table('tblcomments')->insert(['commenttypeID' => 2, 'comment' => $request['message'], 'affectedID' => $request['chosen2'], 'username' => Auth::user()->username]);
	  		if(DB::Table('tblpaymentTransaction')->where('ID', $request['chosen2'])->update(['auditStatus' => 1, 'auditedBy'=>Auth::user()->username, 'auditDate'=> date('Y-m-j')])){
	  		$data['success']= 'Audit approved!';
	  		} else {
	  		$data['error'] = 'Oops! something went wrong';
	  		}
	  		$data['tablecontent'] = DB::table('tblpaymentTransaction')
	    						->leftjoin('tblcontractType', 'tblpaymentTransaction.contractTypeID', '=', 'tblcontractType.ID')
	    						->leftjoin('tblcontractDetails', 'tblpaymentTransaction.contractID', '=', 'tblcontractDetails.ID')
	    						->leftjoin('tblcontractor', 'tblpaymentTransaction.companyID', '=', 'tblcontractor.id')
	    						->leftjoin('tbleconomicCode', 'tblpaymentTransaction.economicCodeID', '=', 'tbleconomicCode.ID')
	    						->leftjoin('tblallocation_type', 'tblpaymentTransaction.allocationType', '=', 'tblallocation_type.ID')
	    						->leftjoin('tblVATWHTPayee', 'tblpaymentTransaction.VATPayeeID', '=', 'tblVATWHTPayee.ID')
	    						->where('tblpaymentTransaction.contractTypeID', '=', $request['contracttype2'])
	    						->orwhere('tblpaymentTransaction.contractTypeID', '=', $request['contracttype'])
	    						->orwhere('tblpaymentTransaction.status', '=', $request['status'])
	    						->where('tblpaymentTransaction.status', '=', $request['status2'])
	    						->where('tblpaymentTransaction.dateTakingLiability', "<>", "")
	    						->where('tblpaymentTransaction.liabilityStatus', "=", 1)
	    						->where('tblpaymentTransaction.dateCheck', "<>", "")
	    						->where('tblpaymentTransaction.checkbyStatus', "=", 1)
	    						->select('tblpaymentTransaction.*', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tblallocation_type.allocation', 'tblcontractDetails.contractValue', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions', DB::raw('tblcontractDetails.ID AS conID'))
	    						->orderBy('tblpaymentTransaction.ID', 'desc')
	    						->orderBy('auditStatus', 'asc')
	    						->orderBy('dateAward', 'asc')
	    						->get();
	   	}

	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
	    	$com = DB::table('tblcomments')->where('affectedID', $value->ID)->orwhere('affectedID', $value->contractID)->get();	    	
	    	$lis['comment'] = "";
	    	if($com){
	        	
		        	foreach($com as $k => $list){
		        		$newline = (array) $list;
		        		$name = DB::table('users')->where('username', $list->username)->first()->name;
		        		$newline['name'] = $name;
		        		$date = strtotime($list->added);
		        		$newline['date_added'] = date("F j, Y", $date);
		        		$newline['time'] = date("g:i a", $date);
		        		$newline = (object) $newline;
		        		$com[$k] = $newline;
		        	}
	        	
	        	//dd($com);
	        	$lis['comment'] = json_encode($com);
	        	//dd($data['instructions']);
	        		
	        }
	    	$value = (object) $lis;
	    	$data['tablecontent'][$key]  = $value;
	    }
	    
		return view('Auditor.auditor', $data);
	}

}