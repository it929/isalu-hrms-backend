<?php

namespace App\Http\Controllers\payroll;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Carbon\Carbon;
use Entrust;
use Session;
use Excel;
use Input;
use QrCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class ProcurementController extends function24Controller
{

    public function newprocurement_staff(Request $request) 
	{
	
	    $data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
	    if($request['date_awarded']==''){$request['date_awarded']=date('Y-m-d');}
	    $data['procurementlist'] = $this->getStaffProcurement();
		$data['procurementlistNew'] = [];
	   
	    $data['contractorList'] = [];
	    $data['contractlist'] = $this->getContract();
	    $data['contractlist2'] = $this->getContract();

	    $data['currentuser'] 		= Auth::user()->username;
	    $data['contract_desc'] 		= trim($request['contract-desc']);
        $data['contractvalue'] 		= preg_replace('/[^\d.]/','', $request['contractvalue']);
            //dd($data['contractvalue']);
	    $data['companyid'] 			= $request['companyid'];
	    $data['benef'] 			= $request['benef'];
	    $data['date_awarded'] 		= $request['date_awarded'];
	    $data['contracttype'] 		= $request['contracttype'];
	    $data['attension'] 		= $request['attension'];
	    
	    $data['fileno']			= trim($request['fileno']);
	    
	    if($request['contracttype'] && $request['contract-desc'] && $request['contractvalue'] && $request['companyid'] && $request['date_awarded'] && $request['createdby'] && $request['fileno']){

	    	foreach($data as $key => $value){
	    		$$key = $value;
	    	}

	    	if(!DB::table('tblcontractDetails')
	    	->where('fileNo', $fileno)
	    	->get()){
	    	
	    		$this->validate($request, ['filex' => 'file|mimes:pdf,jpeg,jpg,png,gif|max:3048'], [], ['filex' => 'Attached File']);
				
	    		$lastid = DB::table('tblcontractDetails')->insertGetId([
	    			'contract_Type' 			=> $contracttype,
	    			'fileNo'				=> $fileno,
	    			'ContractDescriptions'			=> $contract_desc,
	    			'contractValue'				=> $data['contractvalue'],
	    			'companyID'				=> $companyid,
	    			'beneficiary'				=> $benef,
	    			'dateAward'				=> $date_awarded,
	    			'approvalStatus'				=> 1,
	    			'createdby'				=> $currentuser,
	    			'voucherType'				=> 2,
	    			'awaitingActionby'			=> $attension,
	    			'datecreated'				=> date("F j, Y")
	    		]);
	    		
	    		if($request->file('filex') != null){
	    		
				$image = $request->file('filex');

				$input['imagename'] = $lastid.'.'.$image->getClientOriginalExtension();
				  
                    		$upload_path = env('UPLOAD_PATH', '');
                    		  
				$destinationPath = base_path('../').'/'.$upload_path;
				    
				$move = $image->move($destinationPath, $input['imagename']);
				
				if($move){
					$data['success'] = "Procurement created successful";
					DB::table('tblcontractDetails')->where('ID', $lastid)->update(['file_ex' => $image->getClientOriginalExtension()]);
				}
				  
				  
			}
	    	} else {
	    		$data['error'] = "This Procurement has been created earlier!";

	    	}
	    }

	    if(!empty($request['deleteid'])){
	    	$id = $request['deleteid'];
	    	if(DB::table('tblcontractDetails')->where('ID', $id)->delete()){
	    		$data['success'] = "Record was deleted successfully!";
			}
	    }

	    if($request['edit-hidden'] == 1){
	    	$fileno = $request['file_no'];
	    	$contracttyp = $request['contr_type'];
	    	$contratdesc = $request['contr_desc'];
	    	$contractval = $request['contr_val'];
	    	$compani     = $request['company'];
	    	$dateawd     = $request['dateawd'];
	    	$createdby   = $request['creatdby'];

	    	$chk = DB::table('tblcontractDetails')->where('fileNo', $fileno)->where('createdby', $createdby)->first();
	    	
	    	if($chk){
	    		
	    		DB::table('tblcontractDetails')->where('fileNo', $fileno)->where('createdby', $createdby)
	    			->update([
	    				'fileNo' 				=> $fileno,
	    				'contract_Type' 			=> $contracttyp,
	    				'ContractDescriptions' 			=> $contratdesc,
	    				'contractValue'				=> $contractval,
	    				'companyID'				=> $compani,
	    				'dateAward'				=> $dateawd,
	    				'createdby'				=> $createdby
	    			]);
	    			
	    			if($request->file('filex') != null){
	    				
					$image = $request->file('filex');
	
					$input['imagename'] = $chk->ID.'.'.$image->getClientOriginalExtension();
					  
	                    		$upload_path = env('UPLOAD_PATH', '');
	                    		  
					$destinationPath = base_path('../').'/'.$upload_path;
					
					if(file_exists($destinationPath.$input['imagename'])){
						unlink($destinationPath.$input['imagename']);
	    					$move = $image->move($destinationPath, $input['imagename']);
					
						if($move){
							$data['success'] = "Record was edited successfully!";
							
						}
	    				} else {
	    					$move = $image->move($destinationPath, $input['imagename']);
					
						if($move){
							$data['success'] = "Record was edited successfully!";
							
						}
	    				}
										    
									  
				  
				} else {
					$data['success'] = "Record was edited successfully!";
				}
	    	} else {
	    		$data['error'] = "Oops something went wrong!";
	    	}
	    }
	    
	    if($request['allocationtype'] && $request['contracttype']){
	    	$data['econocode'] = $this->getEconomicCode($request['allocationtype'], $request['contracttype']);
	    	$data['economiccode'] = $request['economicCode'];
	    }
	    
	    $data['companyDetails'] = $this->getBeneficiary();
	    $data['fileRefer'] = [];
	    $data['procurementlist'] = $this->getStaffProcurement();
	   
	     
	     foreach($data['procurementlist'] as $key => $value){
	         $line = (array) $value;
	         $reason = "";
	         if($line['approvalStatus'] == 2){
	             $reason = DB::table('tblcomments')->where('affectedID', $value->ID)->where('commenttypeID', 1)->latest('id')->first()->comment;
	         }
	         $line['reason'] = $reason;
	         $line['balance']=$this->ContractBalance($value->ID);
	         $data['procurementlist'][$key] = (object) $line;	         
	     }
	   
	   
	    return view('NewProcurement.staffprocurement', $data);
	}

 public function newprocurement(Request $request) 
	{

	    $data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
	    
	    $data['procurementlist'] = $this->getProcurement();
	   
	    $data['contractorList'] = [];
	    $data['contractlist'] = $this->getContract();
	    $data['contractlist2'] = $this->getContract();

	    $data['currentuser'] 		= Auth::user()->username;
	    $data['contract_desc'] 		= trim($request['contract-desc']);
            $data['contractvalue'] 		= preg_replace('/[^\d.]/','', $request['contractvalue']);
            //dd($data['contractvalue']);
            $data['attension'] 		= $request['attension'];
	    $data['companyid'] 			= $request['companyid'];
	    $data['date_awarded'] 		= $request['date_awarded'];
	    $data['contracttype'] 		= $request['contracttype'];
	    $data['fileno']				= trim($request['fileno']);
	    
	    if($request['contracttype'] && $request['contract-desc'] && $request['contractvalue'] && $request['companyid'] && $request['date_awarded'] && $request['createdby'] && $request['fileno']){

	    	foreach($data as $key => $value){
	    		$$key = $value;
	    	}

	    	if(!DB::table('tblcontractDetails')
	    	->where('fileNo', $fileno)
	    	->get()){
	    	
	    		$this->validate($request, ['filex' => 'file|mimes:pdf,jpeg,jpg,png,gif|max:3048'], [], ['filex' => 'Attached File']);
				
	    		$lastid = DB::table('tblcontractDetails')->insertGetId([
	    			'contract_Type' 			=> $contracttype,
	    			'fileNo'				=> $fileno,
	    			'ContractDescriptions'			=> $contract_desc,
	    			'contractValue'				=> $data['contractvalue'],
	    			'companyID'				=> $companyid,
	    			'dateAward'				=> $date_awarded,
	    			'voucherType'				=> 1,
	    			'awaitingActionby'			=> $data['attension'],
	    			'createdby'				=> $currentuser,
	    			'datecreated'				=> date("F j, Y")
	    		]);
	    		
	    		if($request->file('filex') != null){
	    		
				$image = $request->file('filex');

				$input['imagename'] = $lastid.'.'.$image->getClientOriginalExtension();
				  
                    		$upload_path = env('UPLOAD_PATH', '');
                    		  
				$destinationPath = base_path('../').'/'.$upload_path;
				    
				$move = $image->move($destinationPath, $input['imagename']);
				
				if($move){
					$data['success'] = "Procurement created successful";
					DB::table('tblcontractDetails')->where('ID', $lastid)->update(['file_ex' => $image->getClientOriginalExtension()]);
				}
				  
				  
			}
	    	} else {
	    		$data['error'] = "This Procurement has been created earlier!";

	    	}
              }

	    if(!empty($request['deleteid'])){
	    	$id = $request['deleteid'];
	    	if(DB::table('tblcontractDetails')->where('ID', $id)->delete()){
	    		$data['success'] = "Record was deleted successfully!";
			}
	    }

	    if($request['edit-hidden'] == 1){
	    	$fileno = $request['file_no'];
	    	$contracttyp = $request['contr_type'];
	    	$contratdesc = $request['contr_desc'];
	    	$contractval = $request['contr_val'];
	    	$compani     = $request['company'];
	    	$dateawd     = $request['dateawd'];
	    	$createdby   = $request['creatdby'];

	    	$chk = DB::table('tblcontractDetails')->where('fileNo', $fileno)->where('createdby', $createdby)->first();
	    	
	    	if($chk){
	    		
	    		DB::table('tblcontractDetails')->where('fileNo', $fileno)->where('createdby', $createdby)
	    			->update([
	    				'fileNo' 				=> $fileno,
	    				'contract_Type' 			=> $contracttyp,
	    				'ContractDescriptions' 			=> $contratdesc,
	    				'contractValue'				=> $contractval,
	    				'companyID'				=> $compani,
	    				'dateAward'				=> $dateawd,
	    				'createdby'				=> $createdby
	    			]);
	    			
	    			if($request->file('filex') != null){
	    				
					$image = $request->file('filex');
	
					$input['imagename'] = $chk->ID.'.'.$image->getClientOriginalExtension();
					  
	                    		$upload_path = env('UPLOAD_PATH', '');
	                    		  
					$destinationPath = base_path('../').'/'.$upload_path;
					//print_r(scandir($destinationPath));
					//var_dump(file_exists($destinationPath));
					//echo $input['imagename'];
					//die();
					
					if(file_exists($destinationPath.$input['imagename'])){
						unlink($destinationPath.$input['imagename']);
	    					$move = $image->move($destinationPath, $input['imagename']);
					
						if($move){
							$data['success'] = "Record was edited successfully!";
							
						}
	    				} else {
	    					$move = $image->move($destinationPath, $input['imagename']);
					
						if($move){
							$data['success'] = "Record was edited successfully!";
							
						}
	    				}
										    
									  
				  
				} else {
					$data['success'] = "Record was edited successfully!";
				}
	    	} else {
	    		$data['error'] = "Oops something went wrong!";
	    	}
	    }
	    
	    if($request['allocationtype'] && $request['contracttype']){
	    	$data['econocode'] = $this->getEconomicCode($request['allocationtype'], $request['contracttype']);
	    	$data['economiccode'] = $request['economicCode'];
	    }
	    
	    $data['companyDetails'] = $this->getBeneficiary();
	    $data['fileRefer'] = [];
	    $data['procurementlist'] = $this->getProcurement();
	    
	    $data['procurementlistNew'] = $this->getProcurement();
	   // dd($data['procurementlistNew']);
	    /*
	    	$data['subdescriptions']  =[];
	    	$data['companyDetails'] = [];
	    	$data['fileRefer'] = [];
	     */
	     
	     foreach($data['procurementlist'] as $key => $value){
	         $line = (array) $value;
	         $reason = "";
	         if($line['approvalStatus'] == 2){
	             $reason = DB::table('tblcomments')->where('affectedID', $value->ID)->where('commenttypeID', 1)->latest('id')->first()->comment;
	         }
	         $line['balance']=$this->ContractBalance($value->ID);
	         $line['reason'] = $reason;
	         $data['procurementlist'][$key] = (object) $line;	         
	     }
	   //dd($data['procurementlist']);
	    return view('Procurements.newprocurement', $data);
	}
	
	public function approveprocurement(Request $request){
	
	    $data['warning'] 				= '';
	    $data['success'] 				= '';
	    $data['error'] 					= '';

	    $data['procurementlist'] 		= $this->getProcurement();
	   
	    $data['contractorList'] 		= [];
	    $data['contractlist'] 			= $this->getContract();
	    $data['contractlist2'] 			= $this->getContract();

	    $data['currentuser'] 			= Auth::user()->username;
	    $data['contract_desc'] 			= trim($request['contract-desc']);
	    $data['contractvalue'] 			= $request['contractvalue'];
	    $data['companyid'] 				= $request['companyid'];
	    $data['date_awarded'] 			= $request['date_awarded'];
	    $data['contracttype'] 			= $request['contracttype'];
	    $data['fileno']					= trim($request['fileno']);
	    
	    $data['status']					= $request['status'];
	    
	    
	    
if ( isset( $_POST['s_remark'] ) ) {
//dd($request['contid']);
DB::table('tblcomments')->insert([
'commenttypeID' => 1
, 'affectedID' => $request['contid']
, 'username' => Auth::user()->username,
 'comment' => $request['instruction'].' (refer to '. $request['attension']. ')']);
$openclose=0;
$approvalStatus=0;
$contid=$request['contid'];
if ($request['attension']=='OC'){$openclose=1;$approvalStatus=1;}
DB::table('tblcontractDetails')->where('ID', $contid)->update([
	'awaitingActionby'=>$request['attension']
	,'openclose' 	=> $openclose
	,'approvalStatus' 	=> $approvalStatus
	,'approvedBy'		=> Auth::user()->username
	,'approvalDate'		=> date("F j, Y")
			    		]);
}
	   
		//$data['tablecontent']			= $this->getTable2($request['contracttype'], $request['status']);
		$data['tablecontent']			= $this->getTable2($request['contracttype'], $request['status'],Auth::user()->username);
	    foreach($data['tablecontent']  as $key => $value){
	        $line = (array) $value;
	        $line['contractBalance'] = $this->contractBalance($value->ID);
	        $line['comments'] = '0';
	        $line['comments2'] = '0';
	        $line['comments3'] = '0';
	        $com = DB::table('tblcomments')->where('affectedID', $value->ID)->where('commenttypeID', 1)->select('tblcomments.comment', 'tblcomments.username', 'tblcomments.added')->orderby('id', 'asc')->get();

	        $line['activate_again'] = 0;
	        
	        if($com){
	        	
	        	foreach($com as $k => $list){
	        		$newline = (array) $list;
	        		$name = DB::table('users')->where('username', $list->username)->first()->name;
	        		$newline['name'] = $name;
				$newline['comment'] = str_replace("\r\n", "<br>", $list->comment);
	        		$date = strtotime($list->added);
	        		$newline['date_added'] = date("F j, Y", $date);
	        		$newline['time'] = date("g:i a", $date);
	        		$newline = (object) $newline;
	        		$com[$k] = $newline;
	        	}
	        	$line['comments'] = json_encode($com);
	        		
	        } 
	        $com2 = DB::table('contract_comment')->where('fileNoID', $value->fileNo)->orderby('commentID', 'asc')->get();
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
	        	$line['comments2'] = json_encode($com2);
	        	
	        	//$line['comments2'] = json_encode($newline);
	        		
	        }
	       
	        if($value->companyID==13){
	        $com3 = DB::table('claim_comment')->where('claimID', $value->procurement_contractID)->orderby('id', 'asc')->get();
	        //if($value->procurement_contractID==22)
	        //dd( $com3);
	        if($com3){
	        	
	        	foreach($com3 as $k => $list){
	        		//$newline = (array) $list;
	        		$newline = (array) [];
	        		$name = DB::table('users')->where('id', $list->userID)->first()->name."(".$list->office. ")";
	        		$newline['name'] = $name;
				$newline['comment'] = str_replace("\r\n", "<br>", $list->comment);
	        		$date = strtotime($list->created_at);
	        		$newline['date_added'] = date("F j, Y", $date);
	        		$newline['time'] = date("g:i a", $date);
	        		$newline = (object) $newline;
	        		$com3[$k] = $newline;
	        	}
	        	$line['comments3'] = json_encode($com3);    		
	        } 
	        //dd($line['comments3']);
	        }
	
	        $line = (object) $line;
	        $data['tablecontent'][$key] = $line;
	        
	    }
//dd($data['tablecontent']);
	    
	  
	    
	    $data['companyDetails'] = $this->getBeneficiary();
	    $data['fileRefer'] = [];
	    $data['procurementlist'] = $this->getProcurement();
	    //dd($data['tablecontent']);
	 $data['ApprovalReferal'] = $this->ApprovalReferal(Auth::user()->username);
	return view('Procurements.approveprocurement', $data);
	}
	
	public function contractClaimReport(Request $request){
	
	    $data['warning'] 				= '';
	    $data['success'] 				= '';
	    $data['error'] 					= '';
		$data['datepicker1']=$request['datepicker1'];
	        $data['datepicker2']=$request['datepicker2'];
	        if($data['datepicker1']==null)
	        {
	            $data['datepicker1'] = Carbon::now()->subMonth();   
	        }

	        if($data['datepicker2']==null)
	        {
	            $data['datepicker2'] = Carbon::now();
	        }
        

	    $data['procurementlist'] 		= $this->getProcurement();
	   
	    $data['contractorList'] 		= [];
	    $data['contractlist'] 			= $this->getContract();
	    $data['contractlist2'] 			= $this->getContract();

	    $data['currentuser'] 			= Auth::user()->username;
	    $data['contract_desc'] 			= trim($request['contract-desc']);
	    $data['contractvalue'] 			= $request['contractvalue'];
	    $data['companyid'] 				= $request['companyid'];
	    $data['date_awarded'] 			= $request['date_awarded'];
	    $data['contracttype'] 			= $request['contracttype'];
	    $data['fileno']					= trim($request['fileno']);
	    
	    $data['status']					= $request['status'];
	    
		$data['tablecontent']			= $this->getContractQueryReport($request['contracttype'], $request['status'],$data['datepicker1'],$data['datepicker2']);
	    foreach($data['tablecontent']  as $key => $value){
	        $line = (array) $value;
	        $line['contractBalance'] = $this->contractBalance($value->ID);
	    	$line = (object) $line;
	        $data['tablecontent'][$key] = $line;
	  }
	    
	    $data['companyDetails'] = $this->getBeneficiary();
	 $data['procurementlist'] = $this->getProcurement();
	 $data['paymemtstatus'] = $this->paymemtstatus();
	 $data['ApprovalReferal'] = $this->ApprovalReferal(Auth::user()->username);
	return view('Procurements.contractclaim', $data);
	}
	public function viewfile($id){
		$data['name'] = $id;
		return view('filex.filex', $data);
	}
	public function procurement_ContractEntry(Request $request) 
	{

	    $data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
	    $data['contractorList'] = [];
	    $data['currentuser'] 		= Auth::user()->username;
	    $data['contract_desc'] 		= trim($request['contract-desc']);
            $request['contractvalue'] 		= preg_replace('/[^\d.]/','', $request['contractvalue']);
            $data['contractvalue'] 		= preg_replace('/[^\d.]/','', $request['contractvalue']);
            $data['attension'] 		= $request['attension'];
	        $data['companyid'] 			= $request['companyid'];
	        $data['date_awarded'] 		= $request['date_awarded'];
	        $data['contracttype'] 		= $request['contracttype'];
    	    $data['fileno']				= trim($request['fileno']);
	    
	    //if($request['contracttype'] && $request['contract-desc'] && $request['contractvalue'] && $request['companyid'] && $request['date_awarded'] && $request['createdby'] && $request['fileno']){
if ( isset( $_POST['update'] ) ){
	    	foreach($data as $key => $value){
	    		$key = $value;
	    	}
	    $this->validate($request, [
		'companyid'      	    => 'required'
		,'date_awarded'      => 'required'
		,'contract-desc'      => 'required'
		,'contractvalue'      => 'required|numeric|between:0,9999999999999999.99'
		]);
		if($data['fileno']!=null)
		$this->validate($request, [
		'fileno'      => 'unique:tblcontractDetails,fileNo'
		]);

	    	
	    	
	    		$this->validate($request, ['filex' => 'file|mimes:pdf,jpeg,jpg,png,gif|max:3048'], [], ['filex' => 'Attached File']);
				
	    		$lastid = DB::table('tblcontractDetails')->insertGetId([
	    			'fileNo'				=> $data['fileno'],
	    			'ContractDescriptions'			=> $data['contract_desc'],
	    			'contractValue'				=> preg_replace('/[^\d.]/','', $data['contractvalue']),
	    			'companyID'				=>$data['companyid'],
	    			'dateAward'				=> $data['date_awarded'],
	    			'voucherType'				=> 1,
	    			'awaitingActionby'			=> $data['attension'],
	    			'createdby'				=>Auth::user()->id,// $currentuser,
	    			'datecreated'				=> date("Y-m-d"),
	    			'isfrom_procurement'=>1,
	    			'beneficiary'				=> DB::table('tblcontractor')->where('id', $data['companyid'])->value('contractor')
	    		]);
	    		
	    		if($request->file('filex') != null){
	    		
				$image = $request->file('filex');

				$input['imagename'] = $lastid.'.'.$image->getClientOriginalExtension();
				  
                    		$upload_path = env('UPLOAD_PATH', '');
                    		  
				$destinationPath = base_path('../').'/'.$upload_path;
				    
				$move = $image->move($destinationPath, $input['imagename']);
				
				if($move){
					$data['success'] = "Procurement created successful";
					DB::table('tblcontractDetails')->where('ID', $lastid)->update(['file_ex' => $image->getClientOriginalExtension()]);
				}
			}
	    	
              }

	    if(!empty($request['deleteid'])){
	    	$id = $request['deleteid'];
	    	if(DB::table('tblcontractDetails')->where('ID', $id)->delete()){
	    		$data['success'] = "Record was deleted successfully!";
			}
	    }

	    if($request['edit-hidden'] == 1){
	    	$fileno = $request['file_no'];
	    	$contratdesc = $request['contr_desc'];
	    	$contractval = $request['contr_val'];
	    	$compani     = $request['company'];
	    	$dateawd     = $request['dateawd'];
	    	$createdby   = $request['creatdby'];

	    	$chk = DB::table('tblcontractDetails')->where('ID', $request['cid'])->first();
	    	
	    	if($chk){
	    		
	    		DB::table('tblcontractDetails')->where('ID', $request['cid'])
	    			->update([
	    				'fileNo' 				=> $fileno,
	    				'ContractDescriptions' 	=> $contratdesc,
	    				'contractValue'			=>  preg_replace('/[^\d.]/','', $contractval),
	    				'companyID'				=> $compani,
	    				'dateAward'				=> $dateawd,
	    				'createdby'				=> Auth::user()->id,
	    				'beneficiary'			=> DB::table('tblcontractor')->where('id', $compani)->value('contractor')
	    			]);
	    			
	    			if($request->file('filex') != null){
	    				
					$image = $request->file('filex');
	
					$input['imagename'] = $chk->ID.'.'.$image->getClientOriginalExtension();
					  
	                    		$upload_path = env('UPLOAD_PATH', '');
	                    		  
					$destinationPath = base_path('../').'/'.$upload_path;
					
					if(file_exists($destinationPath.$input['imagename'])){
						unlink($destinationPath.$input['imagename']);
	    					$move = $image->move($destinationPath, $input['imagename']);
					
						if($move){
							$data['success'] = "Record was edited successfully!";
							
						}
	    				} else {
	    					$move = $image->move($destinationPath, $input['imagename']);
					
						if($move){
							$data['success'] = "Record was edited successfully!";
							
						}
	    				}
										    
									  
				  
				} else {
					$data['success'] = "Record was edited successfully!";
				}
	    	} else {
	    		$data['error'] = "Oops something went wrong!";
	    	}
	    }
	    
	    if($request['allocationtype'] && $request['contracttype']){
	    	$data['econocode'] = $this->getEconomicCode($request['allocationtype'], $request['contracttype']);
	    	$data['economiccode'] = $request['economicCode'];
	    }
	    
	    $data['companyDetails'] = $this->getBeneficiary();
	    $data['fileRefer'] = [];
	    // $data['procurementlist'] = $this->getProcurementContractEntry();
		$data['procurementlist'] = $this->getProcurementContractEntryOptimized();
	    //  foreach($data['procurementlist'] as $key => $value){
	    //      $line = (array) $value;
	    //      $reason = "";
	    //      if($line['approvalStatus'] == 2){
	    //          $reason = DB::table('tblcomments')->where('affectedID', $value->ID)->where('commenttypeID', 1)->latest('id')->first()->comment;
	    //      }
	    //      $line['balance']=$this->ContractBalance($value->ID);
	    //      $line['reason'] = $reason;
	    //      $data['procurementlist'][$key] = (object) $line;	         
	    //  }
	   //dd($data['procurementlist']);
	    return view('Procurements.procurement_contract_entry', $data);
	}
}
