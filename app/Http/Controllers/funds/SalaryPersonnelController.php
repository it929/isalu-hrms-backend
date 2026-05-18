<?php

namespace App\Http\Controllers\funds;



use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Helpers\FileUploadHelper;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SalaryPersonnelController extends function24Controller
{
    public function createPersonnelVoucher(Request $request, $uniqueCode = '')
    {
           //Increase Memory Size
		    ini_set('memory_limit', '-1');
            //////////////////////////////////
            $data['warning'] = '';
            $data['success'] = '';
            $data['error'] = '';
            $request['claimvalue'] = preg_replace('/[^\d.]/','', $request['claimvalue']);
            if($request['approvaldate']==''){$request['approvaldate']=date('Y-m-d');}
            $data['fileno'] =$request['fileno'];
            $data['contracttype'] =$request['contracttype'];
            $data['description'] =$request['description'];
            $data['claimvalue'] =$request['claimvalue'];
            $data['benef'] =$request['benef'];
            $data['approvaldate'] =$request['approvaldate'];
            $data['attension'] =$request['attension'];
            $data['eco_code'] = $request['economiccode'];
            $data['addr'] = $request['payeeAddress'];
            $data['overtimeData'] = '';
            if($uniqueCode){
                $data['overtimeData'] = DB::table('overtime_trial')
                    ->join('tblper', 'tblper.ID', '=', 'overtime_trial.staffID')
                    ->where('overtime_trial.uniqueCode', $uniqueCode)
                    ->select(
                        'overtime_trial.overtimeDesc',
                        'overtime_trial.uniqueCode',
                        DB::raw('SUM(overtime_trial.amount) as total_amount'),
                        DB::raw('COUNT(DISTINCT overtime_trial.staffID) as staff_count'),
                        DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(CONCAT(tblper.surname, " ", tblper.first_name, " ", tblper.othernames) ORDER BY tblper.ID ASC SEPARATOR "; "), "; ", 1) as first_beneficiary')
                    )
                    ->groupBy('overtime_trial.overtimeDesc')
                    ->get();

                // Construct beneficiaries string
                foreach ($data['overtimeData'] as $item) {
                    $remaining = $item->staff_count - 1;
                    $item->beneficiaries = $item->first_beneficiary . " and " . $remaining . " OTHERS";
                }
                $data['benef'] = $data['overtimeData'][0]->beneficiaries;
                $data['claimvalue'] = number_format($data['overtimeData'][0]->total_amount, 2);
                $data['description'] = $data['overtimeData'][0]->overtimeDesc;

            }
            // dd($data);
            
            if( isset( $_POST['delete'] )){
                $claimid= DB::table('tblcontractDetails')->where('ID', $request['deleteid'])->where('approvalStatus', 0)->value('claimid');
                DB::table('tblselectedstaffclaim')->where('claimID', $claimid)->delete();
                DB::table('staffclaimfile')->where('claimID', $claimid)->delete();
                DB::table('tblclaim')->where('ID', $claimid)->delete();
                if(DB::table('tblcontractDetails')->where('ID', $request['deleteid'])->where('approvalStatus', 0)->delete()){
                     $data['success'] = "Record was deleted successfully!";
                }
            }
                
            if( isset( $_POST['save'] )){
                $this->validate($request, [
                    'fileno'                    => 'required|string',
                    'contracttype'              => 'required|string',
                    'description'               => 'required|string',
                    'claimvalue'                => 'required|numeric',
                    'benef'                     => 'required|string',
                    'approvaldate'              => 'required|string',
                    'attension'                 => 'required|string',
                    'approvalpage'              => 'required|numeric',
                    'economiccode'				=> 'required',
                    'payeeAddress'				=> 'required',
                    'filex' => 'required|file|mimes:pdf,jpeg,jpg,png|max:5120',
                ], [
                    'filex.required' => 'Approval document is required.',
                ]); 
                if(DB::table('tblcontractDetails')->where('ref_no',$request['approvalpage'])->where('fileNo',$data['fileno'])->first()) return back()->with("err","Duplicate approval page ".$request['approvalpage']. " not allowed for " .  $data['fileno'].". The page number approval already captured");
               
                $claimid = DB::table('tblclaim')->insertGetId([
                    'user' 		    => Auth::user()->id,
                    'Title'			=> 'DTA/Staff Claim',
                    'claimFileNo'	=> $data['fileno'],
                    'details'		=> $data['description'],
                    'amount'		=> (float) str_replace(',', '', $data['claimvalue']),
                    'status'		=> 6,
                    'created_at'	=> date("Y-m-d"),
                ]);
                $lastid = DB::table('tblcontractDetails')->insertGetId([
                    'contract_Type' 		=> $data['contracttype'],
                    'claimid'				=> $claimid,
                    'fileNo'				=> $data['fileno'],
                    'ref_no'				=> $request['approvalpage'],
                    'ContractDescriptions'	=> $data['description'],
                    'economicVoult' 				=> $data['eco_code'],
                    'eco_code' 				=> $data['eco_code'],
                    'payee_address' 		=> $data['addr'],
                    'contractValue'			=> (float) str_replace(',', '', $data['claimvalue']),
                    'companyID'				=> 13,
                    'beneficiary'			=> $data['benef'],
                    'dateAward'				=> $data['approvaldate'],
                    'approvalDate'			=> $data['approvaldate'],
                    'approvalStatus'		=> 0,
                    'createdby'				=>  Auth::user()->id,
                    'voucherType'		    => 2,
                    'awaitingActionby'	    => $data['attension'],
                    'datecreated'			=> date("F j, Y"),
                    'overtuniqueCode'       => $data['overtimeData'] ? $data['overtimeData'][0]->uniqueCode : '',
                ]);
                if($request->file('filex') != null){
                    $file = $request->file('filex');
                    $customName = $lastid . "_" . $this->RefNo() . '.' . $file->getClientOriginalExtension();
                    $fileUrl = FileUploadHelper::upload($file, 'attachments', $customName);

                    DB::table('tblcontractfile')->insert([
                            'file_desc' => "Approval document" ,
                            'filename' =>  $fileUrl,
                            'contractid' =>$lastid ,
                            'createdby' =>Auth::user()->id ,
                        ]);
                }
                return back()->with('message','addedd  successfully added.'  );
            }

            if( isset( $_POST['update'] )){
                $this->validate($request, [
                    'cid'                   => 'required|string',
                    'fileno'                => 'required|string',
                    'contracttype'          => 'required|string',
                    'description'           => 'required|string',
                    'claimvalue'            => 'required|numeric',
                    'benef'                 => 'required|string',
                    'approvaldate'          => 'required|string',
                    'attension'             => 'required|string',
                    'approvalpage'          => 'required|numeric',
                    'economiccode'				=> 'required',
                    'payeeAddress'				=> 'required'
                ]); 
                $lastid=$request['cid'];
                DB::table('tblcontractDetails')->where('ID',$request['cid'])->update([
                    'contract_Type' 			=> $data['contracttype'],
                    'fileNo'				    => $data['fileno'],
                    'ref_no'				    => $request['approvalpage'],
                    'ContractDescriptions'		=> $data['description'],
                    'economicVoult' 				=> $data['eco_code'],
                    'eco_code' 				=> $data['eco_code'],
                    'payee_address' 		=> $data['addr'],
                    'contractValue'				=> $data['claimvalue'],
                    'companyID'				    => 13,
                    'beneficiary'				=> $data['benef'],
                    'dateAward'				    => $data['approvaldate'],
                    'approvalDate'			    => $data['approvaldate'],
                    'approvalStatus'			=> 0,
                    'createdby'				    =>  Auth::user()->id,
                    'voucherType'				=> 2,
                    'awaitingActionby'			=> $data['attension'],
                    'datecreated'				=> date("Y-m-d")
                ]);
                $claimid=DB::table('tblcontractDetails')->where('ID',$request['cid'])->value('claimid');
                 DB::table('tblclaim')->where('ID',$claimid)
                ->update([
                    'details'		=> $data['description'],
                    'amount'		=> $data['claimvalue'],
                ]);
                if($request->file('filex') != null){
                    $file = $request->file('filex');
                    $customName = $lastid . "_" . $this->RefNo() . '.' . $file->getClientOriginalExtension();
                    $fileUrl = FileUploadHelper::upload($file, 'attachments', $customName);

                    DB::table('tblcontractfile')->insert([
                            'file_desc' => "Approval document" ,
                            'filename' =>  $fileUrl,
                            'contractid' =>$lastid ,
                            'createdby' =>Auth::user()->id ,
                        ]);
                }
                return back()->with('message','updated successfully added.');
            }
    
            if(isset($_POST['ssvoucher'])){
                if($request->awaitActBy == 'HC'){
                    $getAwaitActionBy = 2;
                  }elseif($request->awaitActBy == 'HEC'){
                    $getAwaitActionBy = 1;
                  }elseif($request->awaitActBy == 'HAUD'){
                    $getAwaitActionBy = 3;
                  }elseif($request->awaitActBy == 'HCPO'){
                    $getAwaitActionBy = 4;
                  }else{
                    $getAwaitActionBy = -1;
                  }
                  
                $Ecodetails = DB::table('tbleconomicCode')->where('ID', '=', $request->eco_code)->first();
                $createSalaryV = DB::table('tblpaymentTransaction')->insertGetId([
                    'contractTypeID'        => $request->contracttype,
                    'contractID'            => $request->cID ,
                    'companyID'         => 13,
                    'FileNo'         => $request->fileno,
                    'totalPayment'          => $request->cValue,
                    'paymentDescription'        => $request->cDesc,
                    'VAT'               => 0,
                    'VATValue'          => 0,
                    'WHT'               =>0,
                    'WHTValue'          => 0,
                    'VATPayeeID'            => 0, //$tblvatpayeeid, //
                    'WHTPayeeID'            => 0, //$tblwhtpayeeid, //
                    'amtPayable'            => $request->cValue,
                    'preparedBy'            => Auth::user()->id,
                    'allocationType'        => $Ecodetails->allocationID,
                    'economicCodeID'        => $request->eco_code ,
                    'status'                => 0,
                    'vstage'                => $getAwaitActionBy,
                    'is_advances'        =>0,
                    'datePrepared'          => date('Y-m-d'),
                    'period'        => $this->NewActivePeriod($request->contracttype), 
                    // 'vref_no'          => $this->VnextNo(),
                    'vref_no'          => $request->vref,
                    'payment_beneficiary'=> $request->cBene,
                    'payee_address' => $request->payee_address
                ]);
    
                $updateApprovalStatus =  DB::table('tblcontractDetails')->where('ID', $request->cID)->update([
                    'approvalStatus' => 1
                ]);
    
                if($createSalaryV && $updateApprovalStatus){
                    return redirect('/create/personnel-voucher')->with('message','Voucher has been successfully processed!');
                }
            }
            // $data['userRoleId'] = DB::table('assign_user_role')->where('userID', Auth::user()->id)->first();
            $data['econocodeList'] = DB::table('tbleconomicCode')
                                        ->leftjoin('tblcontractType', 'tblcontractType.ID', '=', 'tbleconomicCode.contractGroupID')
                                        ->where('contractGroupID', '=', 6)
                                        ->where('tbleconomicCode.status', '=', 1)
                                        ->select('tbleconomicCode.*', 'tblcontractType.contractType')->get();
            // dd($data['econocodeList']);
            $data['salaryPersonnelList'] = DB::table('tblcontractDetails')
                                            ->where('companyID', '13')
                                            ->where('contract_type', 6)
                                            ->where('approvalStatus', '=', '0')
                                            ->where('is_archive', 0)
                                            ->leftjoin('assign_user_role', 'assign_user_role.userID', '=', 'tblcontractDetails.createdby')
                                            ->leftjoin('tblcontractType', 'tblcontractDetails.contract_Type', '=', 'tblcontractType.ID')
                                            ->leftjoin('tblcontractor', 'tblcontractDetails.companyID', '=', 'tblcontractor.id')
                                            ->leftjoin('users', 'tblcontractDetails.createdby', '=', 'users.id')
                                            ->leftjoin('tblaction_rank', 'tblcontractDetails.awaitingActionby', '=', 'tblaction_rank.code')
                                            ->select('tblcontractDetails.*', 'tblcontractType.contractType', 'tblcontractor.contractor','users.name','tblaction_rank.description')->orderBy('ID', 'DESC')->get();		
            
            $data['contractlist'] = $this->getContract();
            return view('funds.salaryPersonnel.salaryPersonnelVoucher', $data);
    }

    public function singlePersonnelVoucher($contractID)
	{
		$data['personnelVoucher'] = DB::table('tblcontractDetails')
										->where('tblcontractDetails.ID', '=', $contractID)
										->leftjoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblcontractDetails.eco_code')
										->leftjoin('users', 'tblcontractDetails.createdby', '=', 'users.id')
										->select('tblcontractDetails.*', 'users.name', 'tbleconomicCode.economicCode')
										->first();

		$data['economicHead'] = DB::table('tbleconomicCode')
									->where('tbleconomicCode.ID', '=', $data['personnelVoucher']->eco_code)
									->leftjoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
									->value('tbleconomicHead.Code');
		
        $data['vRef'] = DB::table('tblpaymentTransaction')->where('contractID', '=', $contractID)->where('FileNo', '=', $data['personnelVoucher']->fileNo)->value('vref_no');
        $data['transactionRef'] = DB::table('tblcontractDetails')->where('ID', '=', $contractID)->where('fileNo', '=', $data['personnelVoucher']->fileNo)->value('transaction_vref');
		return view('funds.salaryPersonnel.singlePersonnelVoucher', $data);
	}

    public function unprocessedPersonnelVoucher(Request $request)
	{
		$data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
		$data['econocodeList'] = $this->AllEconomicsCode();
		$data['userRoleId'] = DB::table('assign_user_role')->where('userID', Auth::user()->id)->first();
		$data['contractlist'] = $this->getContract();
		$data['officers']=DB::table('tblaction_rank')->where('preapproval',1)->orderby('rankorder')->get();

		if(isset( $_POST['update'] )){
			// dd($request->cid);
			if($request->attension == 'HC'){
				$stage = 2;
			}
			if($request->attension == 'HEC'){
				$stage = 1;
			}
			if($request->attension == 'HAUD'){
				$stage = 3;
			}
			if($request->attension == 'HCPO'){
				$stage = 4;
			}
			if(DB::table('tblpaymentTransaction')->where('contractID', $request->cid)->update([
				'vstage' 		=> $stage,
				'isrejected' 	=> 0
			]));
			$data['success'] = "Voucher has been sent!";
		}

		if(isset( $_POST['deleteVoucher'] )){
			$data['contractID'] = $request->vPid;

			$fromTransaction = DB::table('tblpaymentTransaction')->where('contractID', '=', $data['contractID'])->delete();
			$fromContract = DB::table('tblcontractDetails')->where('ID', $data['contractID'])->delete();
			if($fromTransaction && $fromContract){
				return back()->with('message', 'Details has been removed');
			}

		}

		$data['tablecontent'] = DB::table('tblpaymentTransaction')->where('vstage', '=', 5)
								->leftjoin('tblcontractDetails', 'tblcontractDetails.ID', '=', 'tblpaymentTransaction.contractID')
								->get();
		return view('funds.salaryPersonnel.unprocessedPersonnel', $data);
	}
	
	public function payingInForm(Request $request)
    {
        $data['warning'] = '';
	    $data['success'] = '';
	    $data['error'] = '';
		$data['econocodeList'] = $this->AllEconomicsCode();

            $request['claimvalue'] = preg_replace('/[^\d.]/','', $request['claimvalue']);
            if($request['approvaldate']==''){$request['approvaldate']=date('Y-m-d');}
            $data['fileno'] =$request['fileno'];
            $data['contracttype'] =$request['contracttype'];
            $data['description'] =$request['description'];
            $data['claimvalue'] =$request['claimvalue'];
            $data['benef'] =$request['benef'];
            $data['approvaldate'] =$request['approvaldate'];
            $data['attension'] =$request['attension'];
            $data['eco_code'] = $request['economiccode'];
            $data['addr'] = $request['payeeAddress'];

            if( isset( $_POST['save'] )){
                $this->validate($request, [
                    'fileno'                    => 'required|string',
                    'contracttype'              => 'required|string',
                    'description'               => 'required|string',
                    'claimvalue'                => 'required|numeric',
                    'benef'                     => 'required|string',
                    'approvaldate'              => 'required|string',
                    'attension'                 => 'required|string',
                    'approvalpage'              => 'required|numeric',
                    'economiccode'				=> 'required',
                    'payeeAddress'				=> 'required'
                ]); 
            if(DB::table('tblcontractDetails')->where('ref_no',$request['approvalpage'])->where('fileNo',$data['fileno'])->first()) return back()->with("err","Duplicate approval page ".$request['approvalpage']. " not allowed for " .  $data['fileno'].". The page number approval already captured");
               
                $claimid = DB::table('tblclaim')->insertGetId([
                    'user' 		    => Auth::user()->id,
                    'Title'			=> 'DTA/Staff Claim',
                    'claimFileNo'	=> $data['fileno'],
                    'details'		=> $data['description'],
                    'amount'		=> $data['claimvalue'],
                    'status'		=> 6,
                    'created_at'	=> date("Y-m-d"),
                ]);
             $lastid = DB::table('tblcontractDetails')->insertGetId([
                    'contract_Type' 		=> $data['contracttype'],
                    'claimid'				=> $claimid,
                    'fileNo'				=> $data['fileno'],
                    'ref_no'				    => $request['approvalpage'],
                    'ContractDescriptions'	=> $data['description'],
                    'eco_code' 				=> $data['eco_code'],
                    'payee_address' 		=> $data['addr'],
                    'contractValue'			=> $data['claimvalue'],
                    'companyID'				=> 13,
                    'beneficiary'			=> $data['benef'],
                    'dateAward'				=> $data['approvaldate'],
                    'approvalDate'			=> $data['approvaldate'],
                    'approvalStatus'		=> 0,
                    'createdby'				=>  Auth::user()->id,
                    'voucherType'		    => 2,
                    'is_pay_in_form'        => 1,
                    'awaitingActionby'	    => $data['attension'],
                    'datecreated'			=> date("F j, Y")
                ]);
                // if($request->file('filex') != null){
                //     $file=$request->file('filex');          
                //     $img=$lastid."_".$this->RefNo().'.'.$file->getClientOriginalExtension();
                //     $file->move(env('Public_Path', '')."/attachments", $img);
                //     //$pathToUploads = "/home/njcgov/fundsAppAttachments/";
                //     //$file->move($pathToUploads, $img);
                //     DB::table('tblcontractfile')->insert([
                //             'file_desc' => "Approval document" ,
                //             'filename' =>  '/attachments/'.$img,
                //             'contractid' =>$lastid ,
                //             'createdby' =>Auth::user()->id ,
                //         ]);
                // }

                
                return back()->with('message','addedd  successfully added.');
            }

            if( isset( $_POST['update'] )){
                $this->validate($request, [
                    'cid'                   => 'required|string',
                    'fileno'                => 'required|string',
                    'contracttype'          => 'required|string',
                    'description'           => 'required|string',
                    'claimvalue'            => 'required|numeric',
                    'benef'                 => 'required|string',
                    'approvaldate'          => 'required|string',
                    'attension'             => 'required|string',
                    'approvalpage'          => 'required|numeric',
                    'economiccode'				=> 'required',
                    'payeeAddress'				=> 'required'
                ]); 
                $lastid=$request['cid'];
                DB::table('tblcontractDetails')->where('ID',$request['cid'])->update([
                    'contract_Type' 			=> $data['contracttype'],
                    'fileNo'				    => $data['fileno'],
                    'ref_no'				    => $request['approvalpage'],
                    'ContractDescriptions'		=> $data['description'],
                    'eco_code' 				=> $data['eco_code'],
                    'payee_address' 		=> $data['addr'],
                    'contractValue'				=> $data['claimvalue'],
                    'companyID'				    => 13,
                    'beneficiary'				=> $data['benef'],
                    'dateAward'				    => $data['approvaldate'],
                    'approvalDate'			    => $data['approvaldate'],
                    'approvalStatus'			=> 0,
                    'createdby'				    =>  Auth::user()->id,
                    'voucherType'				=> 2,
                    'is_pay_in_form'            => 1,
                    'awaitingActionby'			=> $data['attension'],
                    'datecreated'				=> date("Y-m-d")
                ]);
                $claimid=DB::table('tblcontractDetails')->where('ID',$request['cid'])->value('claimid');
                 DB::table('tblclaim')->where('ID',$claimid)
                ->update([
                    'details'		=> $data['description'],
                    'amount'		=> $data['claimvalue'],
                ]);
                // if($request->file('filex') != null){
                //     $file=$request->file('filex');          
                //     $img=$lastid."_".$this->RefNo().'.'.$file->getClientOriginalExtension();
                //     $file->move(env('Public_Path', '')."/attachments", $img);
                //     //$pathToUpload = "/home/njcgov/fundsAppAttachments/";
                //     //$file->move($pathToUpload, $img);
                //     DB::table('tblcontractfile')->insert([
                //             'file_desc' => "Approval document" ,
                //             'filename' =>  '/attachments/'.$img,
                //             'contractid' =>$lastid ,
                //             'createdby' =>Auth::user()->id ,
                //         ]);
                // }
                return back()->with('message','addedd  successfully added.');
            }

            if(isset($_POST['savePayin'])){
                if($request->awaitActBy == 'HC'){
                    $getAwaitActionBy = 2;
                  }elseif($request->awaitActBy == 'HEC'){
                    $getAwaitActionBy = 1;
                  }elseif($request->awaitActBy == 'HAUD'){
                    $getAwaitActionBy = 3;
                  }elseif($request->awaitActBy == 'HCPO'){
                    $getAwaitActionBy = 4;
                  }else{
                    $getAwaitActionBy = -1;
                  }
                  
                $Ecodetails = DB::table('tbleconomicCode')->where('ID', '=', $request->eco_code)->first();
                $createSalaryV = DB::table('tblpaymentTransaction')->insertGetId([
                    'contractTypeID'        => $request->contracttype,
                    'contractID'            => $request->cID ,
                    'companyID'         => 13,
                    'FileNo'         => $request->fileno,
                    'totalPayment'          => $request->cValue,
                    'paymentDescription'        => $request->cDesc,
                    'VAT'               => 0,
                    'VATValue'          => 0,
                    'WHT'               =>0,
                    'WHTValue'          => 0,
                    'VATPayeeID'            => 0, //$tblvatpayeeid, //
                    'WHTPayeeID'            => 0, //$tblwhtpayeeid, //
                    'amtPayable'            => $request->cValue,
                    'preparedBy'            => Auth::user()->id,
                    'allocationType'        => $Ecodetails->allocationID,
                    'economicCodeID'        => $request->eco_code ,
                    'status'                => 0,
                    'vstage'                => $getAwaitActionBy,
                    'is_advances'        =>0,
                    'datePrepared'          => date('Y-m-d'),
                    'period'        => $this->NewActivePeriod($request->contracttype), 
                    // 'vref_no'          => $this->VnextNo(),
                    'is_pay_in_form'            => 1,
                    'vref_no'          => $request->vref,
                    'payment_beneficiary'=> $request->cBene,
                    'payee_address' => $request->payee_address
                ]);
    
                $updateApprovalStatus =  DB::table('tblcontractDetails')->where('ID', $request->cID)->update([
                    'approvalStatus' => 1
                ]);
    
                if($createSalaryV && $updateApprovalStatus){
                    return redirect('/create/paying-in-form')->with('message','Paying In Voucher has been successfully processed!');
                }
            }

            $data['salaryPayInForm'] = DB::table('tblcontractDetails')
                                            ->where('companyID', '13')
                                            // ->where('approvalStatus', '=', '0')
                                            ->where('is_archive', 0)
                                            ->leftjoin('assign_user_role', 'assign_user_role.userID', '=', 'tblcontractDetails.createdby')
                                            ->where('assign_user_role.roleID', '=', 21)
                                            ->where('tblcontractDetails.is_pay_in_form', '=', 1)
                                            ->leftjoin('tblcontractType', 'tblcontractDetails.contract_Type', '=', 'tblcontractType.ID')
                                            ->leftjoin('tblcontractor', 'tblcontractDetails.companyID', '=', 'tblcontractor.id')
                                            ->leftjoin('users', 'tblcontractDetails.createdby', '=', 'users.id')
                                            ->leftjoin('tblaction_rank', 'tblcontractDetails.awaitingActionby', '=', 'tblaction_rank.code')
                                            ->select('tblcontractDetails.*', 'tblcontractType.contractType', 'tblcontractor.contractor','users.name','tblaction_rank.description')
                                            ->orderBy('ID', 'DESC')->get();	
                                            
        return view('funds.salaryPersonnel.payingInForm', $data);
    }

    public function payingInVoucher($contractID)
    {
        $data['personnelVoucher'] = DB::table('tblcontractDetails')
										->where('tblcontractDetails.ID', '=', $contractID)
										->leftjoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblcontractDetails.eco_code')
										->leftjoin('users', 'tblcontractDetails.createdby', '=', 'users.id')
										->select('tblcontractDetails.*', 'users.name', 'tbleconomicCode.economicCode')
										->first();

		$data['economicHead'] = DB::table('tbleconomicCode')
									->where('tbleconomicCode.ID', '=', $data['personnelVoucher']->eco_code)
									->leftjoin('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tbleconomicCode.economicHeadID')
									->value('tbleconomicHead.Code');
		
        $data['vRef'] = DB::table('tblpaymentTransaction')->where('contractID', '=', $contractID)->where('FileNo', '=', $data['personnelVoucher']->fileNo)->value('vref_no');
        $data['transactionRef'] = DB::table('tblcontractDetails')->where('ID', '=', $contractID)->where('fileNo', '=', $data['personnelVoucher']->fileNo)->value('transaction_vref');
        return view('funds.salaryPersonnel.payingInVoucher', $data);
    }
    
    public function completeAndProcessedVoucher()
    {
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
	    						->where('tblpaymentTransaction.is_archive', 0)
	    						->where('tblpaymentTransaction.vstage','=', 7)->where('tblpaymentTransaction.is_restore', 0)->where('tblpaymentTransaction.is_advances', 3)
	    						 
	    						->select('tblpaymentTransaction.*', 'tbleconomicCode.description as ecotext', 'tblcontractor.contractor', 'tblcontractType.contractType', 'tbleconomicCode.economicCode', 'tblallocation_type.allocation', 'tblcontractDetails.contractValue', 'tblcontractDetails.dateAward', 'tblcontractDetails.file_ex', 'tblcontractDetails.ContractDescriptions','tblcontractDetails.beneficiary','tblcontractDetails.voucherType', DB::raw('tblcontractDetails.ID AS conID'))
	    						->orderBy('checkbyStatus', 'asc')->orderBy('dateAward', 'asc')->get();
	    						//dd($data['tablecontent']);
	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
	    	$value = (object) $lis;
	    	$data['tablecontent'][$key]  = $value;
	    }
        return view('funds.salaryPersonnel.completeAndProcessed', $data);
    }

    public function trackVoucher(Request $request)
    {
        $data['location'] = $request['location'];
		$data['fromdate'] = $request['fromdate'];
		$data['todate'] = $request['todate'];
		 if($data['fromdate']=='') {$data['fromdate']=Carbon::now()->format('Y-m-d');}
		  if($data['todate']=='') {$data['todate']=Carbon::now()->format('Y-m-d');}
	   $data['tablecontent'] =     $this->SalaryVoucherTrack($data['fromdate'], $data['todate'], $data['location']);
	    foreach ($data['tablecontent'] as $key => $value) {
	    	$lis = (array) $value;
	    	$lis['balance'] = $this->VoucherFinancialInfo($value->ID);
	    	$value = (object) $lis;
	    	$data['tablecontent'][$key]  = $value;
	    }
	   $data['UnitLocation']=$this->UnitLocation();
	   return view('funds.salaryPersonnel.trackVoucher', $data);
    }

    public function salaryApproveAwaiting(Request $request)
    {
        //Increase Memory Size
        ini_set('memory_limit', '-1');
        //////////////////////////////////
        $data['warning']                 = '';
        $data['success']                 = '';
        $data['error']                     = '';
        $data['awaitingby']             = $request['awaitingby'];
        $data['ptype']             = $request['ptype'];
        if (isset($_POST['archive'])) {
            DB::table('tblcomments')->insert([
                'commenttypeID' => 1,
                'affectedID' => $request['id'],
                'username' => Auth::user()->username,
                'comment' => preg_replace('/\s\s+/', ' ', $request['instruction']) . 'archived by ' . Auth::user()->username
            ]);
            DB::table('tblcontractDetails')->where('ID', $request['id'])->update(['is_archive' => 1, 'openclose'     => 0]);
        }

        if (isset($_POST['s_remark'])) {
            $request['instruction'] = $this->UpdateDefaultComment($request['commentid'], trim(preg_replace('/\s\s+/', ' ', $request['instruction'])), DB::table('tblaction_rank')->where('userid', Auth::user()->username)->value('code'));

            DB::table('tblcomments')->insert([
                'commenttypeID' => 1,
                'affectedID' => $request['contid'],
                'username' => Auth::user()->username,
                'comment' => $request['instruction'] . ' (refer to ' . $request['attension'] . ')'
            ]);
           
            DB::table('tblcontractDetails')->where('ID', $request['contid'])->update([
                'awaitingActionby' => $request['attension'],
                'openclose'     => 1,
                'approvalStatus'     => 1,
                'approvedBy'        => Auth::user()->name,
                'approval_last_action_by'        =>  DB::table('tblcontractDetails')->where('ID', '=', $request['contid'])->value('awaitingActionby'),
                'approvalDate'        => date("F j, Y")
            ]);
            $conDetail = DB::table('tblcontractDetails')->where('ID', $request['contid'])->first();
            $usr = Auth::user()->username;
            $this->addLogg("Approved voucher raising for contract with File Number:$conDetail->fileNo and Description: $conDetail->ContractDescriptions by $usr", "Approved for voucher raising");
        }
        $data['tablecontent']            = $this->UnprecessApprovedListSalary($data['awaitingby'], $data['ptype']);
        //dd($data['tablecontent']);
        foreach ($data['tablecontent']  as $key => $value) {
            $line = (array) $value;
            $line['contractBalance'] = $this->contractBalance($value->ID);
            $line['comments'] = '0';
            $line['comments2'] = '0';
            $line['comments3'] = '0';
            $com = DB::table('tblcomments')->where('affectedID', $value->ID)->where('commenttypeID', 1)->select('tblcomments.comment', 'tblcomments.username', 'tblcomments.added')->orderby('id', 'asc')->get();
            $line['activate_again'] = 0;

            if ($com) {
                foreach ($com as $k => $list) {
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

            if ($com2) {
                foreach ($com2 as $k => $list) {
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
            }
            if ($value->companyID == 13) {
                $com3 = DB::table('claim_comment')->where('claimID', $value->procurement_contractID)->orderby('id', 'asc')->get();
                if ($com3) {
                    foreach ($com3 as $k => $list) {
                        //$newline = (array) $list;
                        $newline = (array) [];
                        $name = DB::table('users')->where('id', $list->userID)->first()->name . "(" . $list->office . ")";
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
            }
            $line = (object) $line;
            $data['tablecontent'][$key] = $line;
        }
        $data['Staff_Contract'] = $this->Staff_Contract();
        $data['ApprovalReferal'] = $this->ApprovalReferal(Auth::user()->username);
        $data['ApprovalReferal2'] = $this->ApprovalReferal(0);
        $data['ApprovalReferal'] = $this->ApprovalReferal(Auth::user()->username);
        $data['DefaultComment'] = $this->DefaultComment(DB::table('tblaction_rank')->where('userid', Auth::user()->username)->value('code'));
        return view('funds.salaryPersonnel.salaryApproveforawaiting', $data);
    }

    public function PrecreateSalaryContractVoucher(Request $request)
    {

        $data['warning']        = '';
        $data['success']        = '';
        $data['error']      = '';
        $data['contracttypes']       = $this->getContractType();
        $data['contracttype']       = $request['contracttype'];
        $data['contractor']       = $request['contractor'];
        $data['fileno']       = $request['fileno'];
        if (DB::table('tblcontractDetails')->where('ID', $request['hiddencontractid'])->update(['OC_staffId'    => $request['hiddenuserid'],])) {
            $this->UpdateAlertTable('untreated assigned voucher', 'raise/voucher', $request['hiddenuserid'], 0, 'tblcontractDetails', $request['hiddencontractid'], 1);
            $comment = Auth::user()->username . " assigned task to " . DB::table('users')->where('id', $request['hiddenuserid'])->value('name') . "to raise voucher";
            // dd($comment);
            DB::table('tblcomments')->insert(['commenttypeID' => 1, 'affectedID' => $request['hiddencontractid'], 'paymentID' => 0, 'username' => Auth::user()->username, 'comment' => $comment]);
        }

        $data['tablecontent'] = $this->getTable3($request['contracttype'], $request['contractor'], $request['fileno'], 'SA');
        foreach ($data['tablecontent'] as $key => $value) {
            $line = (array) $value;
            $line['contractBalance'] = $this->contractBalance($value->ID);
            $line['numofv'] = count(DB::table('tblpaymentTransaction')->where('contractID', $value->ID)->get());
            $line = (object) $line;
            $data['tablecontent'][$key] = $line;
        }
        //dd($data['tablecontent']);
        $data['companyDetails'] = $this->getBeneficiary();
        $data['fileRefer'] = [];
        $data['fileNos']            = $this->getFileNos();
        $data['UnitStaff'] = $this->UnitStaff('SA');
        return view('funds.salaryPersonnel.precreatesalarycontract', $data);
    }

    public function clearPersonnelVoucher(Request $request)
    {

        //=============search by date=================
        $getYear    =  Session::get('getYear');
        $getFrom    =  Session::get('getFrom');
        $getTo      =  Session::get('getTo');
        //year
        if ($getYear == '' or $getYear < 1) {
            $getSearchYear = date('Y');
        } else {
            $getSearchYear = $getYear;
        }
        //date (Current days in the year: $dayNumber = date("z") + 1;)
        if ($getFrom == '' or $getTo == '') {
            $getSearchFrom = date('Y-01-01');
            $getSearchTo   = date('Y-m-d');
        } else {
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

        if (DB::table('tblpaymentTransaction')->where('ID', $request['vid'])->update(['liabilityBy'    => $request['as_user']])) {
            $taskscheduled = $this->UpdateAlertTable("Voucher Clearance", 'voucher/liability', $request['as_user'], '0', 'tblpaymentTransaction', $request['vid'], 1);
        }

        if (isset($_POST['process'])) {
            $id = $request['vid'];
            $lid = $request['lid'];
            $year = $request['year'];
            $ctType = $request['ctType'];

            $yearChanged = 0;

            //get active period
            $period = DB::table('tblactiveperiod')->where('contractTypeID', $ctType)->value('year');
            if ($ctType == 4) {
                $existingActiveYear = $period;
                if ($period != $year) {
                    DB::table('tblactiveperiod')->where('contractTypeID', $ctType)->update(['year' => $year]);
                    $yearChanged = 1;
                }
            }

            $Vdetails = DB::table('tblpaymentTransaction')->where('ID', $id)->first();
            $voultbal = $this->VoultBalance($Vdetails->economicCodeID);
            $selectliability = DB::select("SELECT IFNULL( sum(`amount`),0) as tsum FROM `tblliability_taken` WHERE `id`='$lid' and `is_cleared`=0 ")[0]->tsum;
            if ($selectliability > 0 and (floor($selectliability) != floor($Vdetails->totalPayment))) return back()->with('err', 'There is variance in the initial liability and actual voucher. Hence voucher not pass!');
            if (floor($voultbal + floor($selectliability) - floor($this->OutstandingLiabilityNew($Vdetails->economicCodeID))) < floor($Vdetails->totalPayment)) {
                $data['error'] = "Insufficient Vote Balance!!! Liability cannot be cleared for this transaction";
            } else {
                //dd($this->ProcessDATE($Vdetails->economicCodeID));
                $is_special = DB::table('tblpaymentTransaction')->where('ID', $id)->value('is_special');
                if (DB::table('tblpaymentTransaction')->where('ID', $id)->update([
                    'liabilityBy'           => Auth::user()->id,
                    'liabilityStatus'       => 1,
                    'vstage'                => ($is_special == 1) ? 4 : 2,
                    'checkbyStatus'         => ($is_special == 1) ? 1 : 0,
                    'auditStatus'           => ($is_special == 1) ? 1 : 0,
                    'status'                => 2,
                    'isrejected'            => 0,
                    'is_archive'            => 0,
                    'liability_ref'         => ($request['lid']) ? $request['lid'] : 0,
                    'period' => ($ctType == 4 && $yearChanged == 1) ? $year : $period,
                    'dateTakingLiability'   =>   $this->ProcessDATE($Vdetails->economicCodeID), //  '2020-12-31',// date('Y-m-j')
                ])) {
                    DB::table('tblliability_taken')->where('id', $request['lid'])->update([
                        'clear_by'          => Auth::user()->id,
                        'status'            => 0,
                        'ref_voucher_id'    => $id,
                        'status'            => 1,
                        'is_cleared'        => 1,
                        'time_cleared'      =>     $this->ProcessDATE($Vdetails->economicCodeID), // date('Y-m-d h:i:s')
                    ]);
                    $remark = $Vdetails->paymentDescription;
                    $this->VotebookUpdate($Vdetails->economicCodeID, $Vdetails->ID, $remark, $Vdetails->totalPayment, Date('Y-m-d h:i:s'), 2);
                    $comment = trim($request['comment']) . ": Liability cleared for checking by " . Auth::user()->name;
                    DB::table('tblcomments')->insert(['commenttypeID' => 2, 'affectedID' => $Vdetails->contractID, 'paymentID' => 0, 'username' => Auth::user()->username, 'comment' => $comment]);
                    $data['success'] = "Voucher Liability successfully cleared for further processing!";
                    $taskscheduled = $this->UpdateAlertTable("Voucher Clearance", 'pre-check', '0', 'HC', 'tblpaymentTransaction', $id, 1);
                    $this->addlogg("Liability Taken for Voucher with ID: $id and Payment Description:$Vdetails->paymentDescription  awaiting push to checking for further processing!", "Liability taken for Voucher with ID: $id");
                } else {
                    $data['error'] = "Whoops! something went wrong please try again";
                }
            }

            if ($yearChanged == 1) {
                DB::table('tblactiveperiod')->where('contractTypeID', $ctType)->update(['year' => $existingActiveYear]);
            }
        }

        if (isset($_POST['decline'])) {
            $id = $request['vid'];
            $this->validate($request, [
                'vid'       => 'required',
                'comment'  => 'required'
            ]);
            //pitoff
            $getContractType = DB::table('tblpaymentTransaction')->where('ID', $id)->value('contractTypeID');
            if ($getContractType == 6) {
                $declineVstage = 2;
            } else {
                $declineVstage = 0;
            }
            //pitoff

            if (DB::table('tblpaymentTransaction')->where('ID', $id)->update([
                'liabilityStatus'     => 0,
                'vstage'         => $declineVstage,
                'status'         => 0,
                'isrejected'         => 1,
            ])) {
                $theid = $id;
                $user = Auth::user()->username;
                $commenttypeID = 2;
                $Vdetails = DB::table('tblpaymentTransaction')->where('ID', $id)->first();
                $comment = trim($request['comment']) . ": Rejected by " . Auth::user()->name . " at funds clearance stage";
                $cid = $Vdetails->contractID;
                DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $cid, 'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
                $taskscheduled = $this->UpdateAlertTable("Voucher Rejection", 'occheckby/voucher', '0', 'OC', 'tblpaymentTransaction', $id, 1);
                $data['success'] = "Voucher has been Declined successfully!";
                $this->addlogg("Voucher with ID: $id Rejected with reason: $comment", "Voucher with ID: $id Rejected");
            } else {
                $data['error'] = "Whoops! something went wrong please try again";
            }
        }

        if (isset($_POST['switch'])) {
            $id = $request['vid'];
            $this->validate($request, [
                'vid'       => 'required',
                'economiccode'       => 'required'
            ]);
            if (DB::table('tblpaymentTransaction')->where('ID', $id)->update([
                'economicCodeID'     => $request['economiccode'],
                'contractTypeID'     => DB::table('tbleconomicCode')->where('ID', $request['economiccode'])->value('contractGroupID')
            ])) {


                // -----------------------------
                // UPDATE tblcontractdetails HERE
                // -----------------------------


                // Fetch the updated contractTypeID
                $updatedContractTypeID = DB::table('tblpaymentTransaction')
                    ->where('ID', $id)
                    ->value('contractTypeID');

                // Fetch contract ID
                $contractID = DB::table('tblpaymentTransaction')
                    ->where('ID', $id)
                    ->value('contractID');

                // Update tblcontractdetails.contract_Type with contractTypeID
                DB::table('tblcontractDetails')
                    ->where('ID', $contractID)
                    ->update([
                        'contract_Type' => $updatedContractTypeID
                    ]);

                $theid = $id;
                $user = Auth::user()->username;
                $commenttypeID = 2;
                $Vdetails = DB::table('tblpaymentTransaction')->where('ID', $id)->first();
                $comment = "Ecomonmic code changed by " . Auth::user()->name . "to " . $request['economiccode'];
                $cid = $Vdetails->contractID;
                DB::table('tblcomments')->insert(['commenttypeID' => $commenttypeID, 'affectedID' => $cid, 'paymentID' => 0, 'username' => $user, 'comment' => $comment]);
                $taskscheduled = $this->UpdateAlertTable("Voucher Rejection", 'occheckby/voucher', '0', 'OC', 'tblpaymentTransaction', $id, 0);
                $data['success'] = "Voucher has been switched successfully!";
                $this->addlogg("Voucher ID: $id $comment", "Voucher with ID: $id");
            } else {
                $data['error'] = "Whoops! something went wrong please try again";
            }
        }
        $data['UnitStaff'] = $this->UnitStaff('SA');
        $data['tablecontent'] = $this->VourcherGroupWithBalances($this->UnFundClearancePersonnel($getSearchFrom, $getSearchTo, $getSearchYear));
        $data['econocodeList'] = $this->AllEconomicsCode();
        return view('funds.salaryPersonnel.salaryPreliability', $data);
    }

}
