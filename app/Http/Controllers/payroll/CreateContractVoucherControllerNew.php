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
use Illuminate\Support\Facades\Input;
use DB;
use QrCode;
use Illuminate\Support\Facades\Crypt;


class CreateContractVoucherControllerNew extends function24Controller
{


public function setSes(Request $request)
{
  session::put('alloc',$request['id']);
  return response()->json('Successful');
}

    
    
    
    public function newvourcher(Request $request, $cid = "")
    {
    
        //dd($request['FileNo']);
    if($request['todayDate']==''){$request['todayDate']=date('Y-m-d');}
    
        $data['warning']        = '';
        $data['success']        = '';
        $data['error'] = "";
		$cid=$request['contractid'];
		//dd($cid);
		$data['contractid']=$request['contractid'];
		$data['ecoid']=$request['ecoid'];
		$data['FileNo']=$request['FileNo'];
		$rawConDetails= $this->ContractDetails($cid);
		//dd($rawConDetails);
    
		$data['rawConDetails']=$rawConDetails;
        if($rawConDetails->ID==0){
            $data['error'] = "The contract tracking number does not exist!";
            return view('CreateContract.pre-newcontract', $data);
        }
            //Get Voucher Details
        	$data['getVoucherDetails']  = DB::table('tblpaymentTransaction')
	    		->leftjoin('tblcontractDetails','tblcontractDetails.ID','=','tblpaymentTransaction.contractID')
	    		->leftjoin('tblcontractor','tblcontractor.id','=','tblpaymentTransaction.companyID')
				->Join('tbleconomicCode','tbleconomicCode.ID','=','tblpaymentTransaction.economicCodeID')
				->Join('tbleconomicHead','tbleconomicHead.ID','=','tbleconomicCode.economicHeadID')
				->Join('tblcontractType','tblcontractType.ID','=','tblpaymentTransaction.contractTypeID')
				->where('tblpaymentTransaction.contractID', '=', $cid)
				->leftjoin('voucher_type', 'voucher_type.vouchertypeID', '=', 'tblpaymentTransaction.voucher_type_deptID')
				->select('*', 'tbleconomicCode.economicCode', 'tbleconomicCode.ID as economicodeID', 'tbleconomicCode.description as economicName', 'tblpaymentTransaction.ID as transID', 'tblpaymentTransaction.status as payStatus','tblcontractType.contractType as ecoHead', 'tblcontractDetails.companyID as companyIDContractD', 'tblcontractDetails.beneficiary as beneficiaryContractD')
				->first();
        		if(!$data['getVoucherDetails']){
        		    $data['getVoucherDetails']=DB::select("SELECT 0 as VAT,0 as WHT,'' as VATPayeeID, '' as WHTPayeeID,0 as file_referID, ' Vide Letter of award, invoice, job completion certificate and approval minutes for payment attached for details.' as paymentDescription ")[0];
        		}
		    //////////////
		    
		    
        if( isset( $_POST['attach'] )){
		$this->validate($request, [
		'attachcaption' => 'required'
		,'filex'      	=> 'required'
		]);
		
		$fid = DB::table('tblcontractfile')
		->insertGetId([
			'file_desc'            => $request['attachcaption'],
			'contractid'         => $request['selectedid'],
			'createdby'        => Auth::user()->username,                       
		]);
		
		$image =  $request->file('filex');
		$imagename = $id.'_'.$fid.'_'.$image->getClientOriginalName();
		$upload_path = env('UPLOAD_PATH', '');
		$destinationPath = base_path('../').'/'.$upload_path;
		//die($destinationPath );
		$image->move($destinationPath, $imagename);
		DB::table('tblcontractfile')->where('id', $fid )
				->update(['filename' => $imagename]);
		}
	    $data['fileattach']=$this->ContractAttachment($request['selectedid']);
	    if ($request['amtpayable']==''){$request['amtpayable']=$this->ContractBalance($request['selectedid']);}		
		
        
        //for different stuff
            $data['vatpas']         = $request['vatselect'];
            $data['vatselect']      = $request['vatselect'];
            $data['vatvas']         = $request['vat'];
            $data['whtpas']         = $request['whtOrTax'];
            $data['whtOrTax']       = $request['whtOrTax'];
            $data['stampduty']      = $request['stampduty'];
            $data['whtvas']         = $request['tax'];
            $data['stampdutyv']         = $request['stampdutyv'];
            $data['amtpayble']      = $request['amtpayable'];
            $data['narration']      = $request['narration'];
            //$data['pvnoas']         = $request['pvno'];
            $data['liabilityByas']  = $request['liabilityBy'];
            $data['todayDateas']    = $request['todayDate'];
            $data['vatpayeeas']     = $request['vatPayeeID'];
            $data['whtpayeeas']     = $request['whtPayeeID'];
            $data['vatPayeeID']     = $request['vatPayeeID'];
            $data['whtPayeeID']     = $request['whtPayeeID'];
            $data['vatpaddas']      = $request['vatPayeeAddress'];
            $data['whtpaddas']      = $request['whtPayeeAddress'];
            $data['filenoas']       = $request['FileNo'];
            
            //end of different stuff

        
        $data['currentuser'] = Auth::user()->username;
       $data['instructions'] = "";
        $data['getBalance'] = round($this->ContractBalance($cid),2);//(int) $this->ContractBalance($cid) ;
            $details = $this->getInfo($cid);
            if($details->companyID==13){
            $data['contractor']     = $details->beneficiary;
            }
            else{ 
            $data['contractor']     = $details->contractor;
            }
            $data['companyidhid']   = $details->id;
            $data['paymentdesc']    = $details->ContractDescriptions;
            $data['filenoas']   = $details->fileNo;
            $data['economicCode_as']    = $details->economicVoult;
            //dd($data['economicCode_as']);
            if($data['economicCode_as'] != ""){
            
                $vll = DB::table('tbleconomicCode')->where('ID', $data['economicCode_as'])->first();
                if($vll){
                $data['alloc5'] = $vll->allocationID;
                $data['alloc3'] = DB::Table('tblallocation_type')->where('ID', $vll->allocationID)->first()->allocation;
                $data['econ3'] = '(' . $vll->economicCode .') ' . $vll->description;}
                else{$data['economicCode_as']='';}
            }
            
            $data['instructions'] = "";
            //dd($request['selectedid']);
            $com    = DB::table('tblcomments')->where('affectedID', $request['selectedid'])->select('tblcomments.comment', 'tblcomments.username', 'tblcomments.added')->orderby('id', 'asc')->get();

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
                $data['instructions'] = json_encode($com);                  
            }
            $data['instructions1'] = '';
		$com2 = DB::table('contract_comment')->where('fileNoID', $rawConDetails->fileNo)->orderby('commentID', 'asc')->get();
	        if($com2){
	        	
	        	foreach($com2 as $k => $list){
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
	        	 $data['instructions1'] = json_encode($com2);	
	        } 
            
            $data['sel_id'] = $request['selectedid'];
            $data['file_ex'] = $details->file_ex;
        $data['companyid']  = $request['companyid'];
        $data['getBalanceas'] = $request['amount'];
        if($request['finalsubmit'] == "complete"){
            
            if (round($this->ContractBalance($cid),2)<=0){
            $data['error'] = "Complete voucher has already been raised on this contract!";
            return view('CreateContract.pre-newcontract', $data);
            }
            $tblcompanyid       = $request['companyid'];
            $tbltotalpayment    = $request['amount'];
            $tblpaymentDesc     = $request['paymentdesc'];
            $vat                = $request['vat'];
            $vatperc            = $request['vatselect'];
            $whtselect          = $request['whtOrTax'];
            $wht                = $request['tax'];
            $tblamtPayable      = $request['amtpayable'];
            $tblprepareby       = $request['preparedBy'];
            $tblvatpayeeid      = $request['vatPayeeID'];
            $tblwhtpayeeid      = $request['whtPayeeID'];
            $liabilityby        = $request['liabilityBy'];
            $allocationtype     = $request['allocationtype1'];
            $economiccodeid     = $request['economicCode1'];
            $dateprepared       = $request['todayDate'];
            $totalamount        = $request['totalamount'];
            $narration      = $request['narration'];
            
            

            //$data['getBalance']     = (int)$request['amount'];
            $data['vatpas'] = $request['vatselect'];
             $data['vatselect']     = $request['vatselect'];
            $data['vatvas'] = $request['vat'];
            $data['whtpas'] = $request['whtOrTax'];
            $data['whtOrTax'] = $request['whtOrTax'];
            
            $data['whtvas'] = $request['tax'];
		if ($request['amtpayable']==''){
		    $request['amtpayable']=$this->ContractBalance($request['selectedid']);}
		//dd($request['vatPayeeID'].' '.$request['whtPayeeID'] .' '.$request['ecoid'] );
            $data['amtpayble'] = (int)$request['amtpayable'];
			 
            $data['narration'] = $request['narration'];
            $data['pvnoas'] = $request['pvno'];
            $data['liabilityByas'] = $request['liabilityBy'];
            $data['todayDateas'] = $request['todayDate'];
            $data['vatpayeeas'] = $request['vatPayeeID'];
            $data['whtpayeeas'] = $request['whtPayeeID'];
            $data['vatPayeeID']     = $request['vatPayeeID'];
            $data['whtPayeeID']     = $request['whtPayeeID'];
            $data['vatpaddas'] = $request['vatPayeeAddress'];
            $data['whtpaddas'] = $request['whtPayeeAddress'];
            $data['filenoas'] = $request['FileNo'];
            $data['getBalanceas'] = $request['amount'];
            $request['economiccodeid']=$economiccodeid;
           $validating = $this->validate($request, [
           	'allocationtype1'       => 'required',
                'totalamount'           => 'required',
                'narration'             => 'required',
                'amtpayable'            => 'required',
                'preparedBy'            => 'required',
                'vatPayeeID'            => 'required_unless:vatselect,0',
                'whtPayeeID'            => 'required_unless:whtOrTax,0',
                'ecoid'         => 'required',
                'todayDate'         	=> 'required'

            ],[], [

                'allocationtype1'       => 'Allocation type',
                'totalamount'           => 'Total Contract Value',
                'narration'             => 'Payment Description',
                'vat'               	=> 'Value Added Tax',
                'vatselect'         	=> 'Selected Vat Percent',
                'whtOrTax'          	=> 'Selected Wht Percent',
                'tax'              	=> 'Withheld Tax',
                'vatPayeeID'            => 'VAT Payee',
                'whtPayeeID'            => 'WHT Payee',
                'amtpayable'            => 'Amount Payable',
                'ecoid'         => 'Economic code',
                'todayDate'         	=> 'Date Prepared'                  
                    
            ]);
            if($request['vatPayeeID'] == ""){$request['vatPayeeID']=0;}
            if($request['whtPayeeID'] == ""){$request['whtPayeeID']=0;}
            
            //dd($request['vatPayeeID']."   ".$request['whtPayeeID']);
            $data['getBalance2'] = $request['amount'];
                $deno= ($vatperc)+100;
			    $vat1 = ($vatperc/$deno) * $tbltotalpayment;
			    $mockval=$tbltotalpayment-$vat1;
			    $tax1 = ( $whtselect/ 100 ) * $mockval;
			    $vat=round($vat1,2); 
			    $wht=round($tax1,2); 
			    $fstampduty=round(( $data['stampduty']/ 100 ) * $mockval,2);
			    $tblamtPayable =  $tbltotalpayment - (   $vat  +  $wht+ $fstampduty ) ;
			    
			    //dd($data['ecoid']);
                        if($vid = DB::table('tblpaymentTransaction')
                                 ->insertGetId([
                                    'contractTypeID'        => DB::table('tbleconomicCode')->where('ID', $data['ecoid'])->value('contractGroupID'),
                                    'contractID'            => $cid,
                                    'companyID'             => $tblcompanyid,
                                    'FileNo'                =>  DB::table('tblcontractDetails')->where('ID', $cid)->value('fileNo'),
                                    'totalPayment'          => $tbltotalpayment,
                                    'paymentDescription'    => $narration,
                                    'VAT'                   => $vatperc,
                                    'VATValue'              => $vat,
                                    'WHT'                   => $whtselect,
                                    'WHTValue'              => $wht,
                                    'VATPayeeID'            => $request['vatPayeeID'], //$tblvatpayeeid, //
                                    'WHTPayeeID'            => $request['whtPayeeID'], //$tblwhtpayeeid, //
                                    'stampdutypercentage'   => $data['stampduty']!=''? $data['stampduty']:0,
                                    'stampduty'             => $fstampduty,
                                    'amtPayable'            => $tblamtPayable,
                                    'preparedBy'            => Auth::user()->id,//$tblprepareby,
                                    'allocationType'        => $allocationtype,
                                    'economicCodeID'        => $data['ecoid'],
                                    'status'                => 0,
                                    'vstage'                => 1,
                                    'cpo_payment'          => 0,
                                    'datePrepared'          => $dateprepared,
                                    'period'                => $this->ActivePeriod(),
                                    'file_referID'          => $data['FileNo'] == null ? DB::table('tblpaymentTransaction')->where('contractID', $cid)->value('file_referID'):$data['FileNo'] ,
                                    'department_voucher'    => strtoupper($this->getUserRole()->rolename),
                                    'voucher_type_dept'     => null, 
                                    'voucher_type_deptID'   => 0,
                                    'retire_voucher'  		=> 0,
                            ]))
                            
                            
                        {
                                
                                    if(DB::table('tblcontractDetails')->where('ID', $cid)->first()->paymentStatus == "")
                                    {
                                        DB::table('tblcontractDetails')->where('ID', $cid)->update([
                                            'paymentStatus' => 0
                                        ]);
                                    }
                                    if(DB::table('tblcontractDetails')->where('ID', $cid)->first()->economicVoult == "")
                                    {
                                        DB::table('tblcontractDetails')->where('ID', $cid)->update([
                                            'economicVoult' => $economiccodeid
                                        ]);
                                    }
                                    DB::table('tblcontractDetails')->where('ID', $cid)->update([
                                            'openclose' => 0
                                    ]); 
                                    return redirect('display/voucher/'.$vid);
                        } else {
                                    //$data['error'] = "Something went wrong";
                                    return back()->with('error','Something went wrong');

                        }
                            			
                        if($this->VoultBalance($economiccodeid) > $tblamtPayable)
                        {
                            //dd($request['totalamount']);
                            $gross = $data['getBalance'];
                            if($gross < $request['amount'])
                            {
                                //$data['error'] = "Gross amount cannot be greater than Total Sum (Contract Value)!";
                                return back()->with('error','Gross amount cannot be greater than Total Sum (Contract Value)!');
                            } else{
                                //....
                            }
                        } 
                }

                        $data['companyDetails'] = $this->getBeneficiary();
                    $data['fileRefer'] 	  =  DB::table('tbldepartment_fileno')
        	    	//->where('tbldepartment_fileno.account_type', strtoupper($this->getUserRole()->rolename))
        	    	->orderby('tbldepartment_fileno.filerefer', 'Asc')
        	    	->get();
                        $ctypecode=4;
                        if(strtoupper($this->getUserRole()->rolename)=="RECURRENT")$ctypecode=1;
                        $data['ECONOMAIN'] = $this->getDepartmentEconomicCode($ctypecode); 
                		$data['ecogrouptext']=strtoupper($this->getUserRole()->rolename);//$rawConDetails->EcoGroup;
                		$data['vatwhttable']        = DB::table('tblVATWHTPayee')->orderBy('payee', 'Asc')->get();
                        $data['vatpayee']           = $request['vatPayee'];
                        $data['whtpayee']           = $request['whtPayee'];
            return view('CreateContract.newcontract', $data);
        }
    
    
     //Get all economic codes
     public function getDepartmentEconomicCode($contractType){
         if($contractType == 1)
         {
            $economicCode= DB::table('tbleconomicCode')
                ->join('tbleconomicHead', 'tbleconomicHead.ID','=','tbleconomicCode.economicHeadID')
                ->leftjoin('tblcontractType', 'tblcontractType.ID','=','tbleconomicCode.contractGroupID')
                ->where('tbleconomicCode.status', 1)
                ->where('tblcontractType.ID', $contractType)
                ->orwhere('tbleconomicCode.contractGroupID', 6)
                ->select('*', 'tbleconomicCode.ID as economicID', 'tbleconomicCode.description as economicName' )
                ->orderby('tbleconomicCode.economicCode', 'Asc')
                ->get();
         }else{
            $economicCode= DB::table('tbleconomicCode')
                ->join('tbleconomicHead', 'tbleconomicHead.ID','=','tbleconomicCode.economicHeadID')
                ->leftjoin('tblcontractType', 'tblcontractType.ID','=','tbleconomicCode.contractGroupID')
                ->where('tbleconomicCode.status', 1)
                ->where('tblcontractType.ID', $contractType)
                ->select('*', 'tbleconomicCode.ID as economicID', 'tbleconomicCode.description as economicName' )
                ->orderby('tbleconomicCode.economicCode', 'Asc')
                ->get();
         }
        
        return $economicCode;
    }
    
    
    //Get Contract Details
    public function getContractorDetails(){
        $contractorDetails = DB::table('tblcontractor')
            ->where('tblcontractor.status', 1)
            ->where('tblcontractor.type', 1)
            ->orderby('tblcontractor.contractor', 'Asc')
            ->get();
        return $contractorDetails;
        
    }//end function
    
    
        
}//end class