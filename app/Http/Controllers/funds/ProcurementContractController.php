<?php

namespace App\Http\Controllers\funds;

use Illuminate\Http\Request;
use App\Models\Contract;
use App\Models\ContractFile;
use App\Models\ContractDetails;
use App\Models\Comment;
use Validator;
use file;
use Auth;
use Session;
use DateTime;
use DB;

class ProcurementContractController extends BaseParentController
{

    public function basePath()
    {
        return "/home/njcgov/funds.njc.gov.ng/";
    }


    public function create()
    {
        //create new contract ///code....
        return view('njc.createProcurement', $this->getAllContractParameters('all',0));
    }
    
    public function createdirect()
    {
        //create new contract ///code....
        return view('njc.createdirectProcurement', $this->getAllContractParameters('all',1));
    }


    //get first contract value
    public static function getContractValue($fileNo)
    {
        $getRawData = DB::table('create_contract')->where('fileNo', $fileNo)->select('amount')->first();
        return $getRawData->amount;
    }


    //view report
    public function viewAllContract()
    {
        //view all contract, view  by Role or action rank
        return view('njc.allContractCreated', $this->getAllContractParameters('role'));
    }


    //Start saving New contract
    public function save(Request $request)
    {
        Session::put('alertMessage', 0);
        $this->validate($request, [
            'fileNo' => 'required|string|max:255|unique:create_contract',
            //'fileNo' => 'required|alpha_num|max:255',
            'contractorName' => 'required|numeric',
            'amount' => 'required|between:0,9999999.99|min:1',
            'contractDescription' => 'string',
            'awardDate' => 'required|date',
            'attachFile' => 'max:10240',
        ]);
        //
        $myDate = new DateTime($request['awardDate']);
    	$amount = str_replace(',', '', trim($request['amount']));
    	//
        $newContract = new Contract;
        $newContract->fileNo = strtoupper(trim($request['fileNo']));
        $newContract->contractorID = trim($request['contractorName']);
        $newContract->amount = $amount;
        $newContract->award_date = trim($myDate->format('Y-m-d'));
        $newContract->description = trim($request['contractDescription']);
        $newContract->token       = str_random(100);
        $newContract->awaitingActionBy = "DP";

        if ($newContract->save()) {
            //success
            // Session::put('getCurrentID', $newContract::where('fileNo', trim($request['fileNo']))->value('contractID')); //set session
            Session::put('alertMessage', 1);
            return redirect()->route('loadProcurement')->with('message', 'Your operation was successfully added.');
        } else {
            //could not save - perhaps network
            Session::put('alertMessage', 1);
            return redirect()->route('loadProcurement')->with('error', 'Sorry we could not add this record! <br /> Please try again.');
        }
    }
    //End saving new contract
//Start saving New contract direct
    public function savedirect(Request $request)
    {
        Session::put('alertMessage', 0);
        $this->validate($request, [
            'fileNo' => 'required|string|max:255|unique:create_contract',
            //'fileNo' => 'required|alpha_num|max:255',
            'contractorName' => 'required|numeric',
            'amount' => 'required|between:0,9999999.99|min:1',
            'contractDescription' => 'string',
            'awardDate' => 'required|date',
            'attachFile' => 'max:10240',
        ]);
        //
        $myDate = new DateTime($request['awardDate']);
    	$amount = str_replace(',', '', trim($request['amount']));
    	//
        $newContract = new Contract;
        $newContract->fileNo = strtoupper(trim($request['fileNo']));
        $newContract->contractorID = trim($request['contractorName']);
        $newContract->amount = $amount;
        $newContract->liability_amount = $amount;
        $newContract->award_date = trim($myDate->format('Y-m-d'));
        $newContract->description = trim($request['contractDescription']);
        $newContract->token       = str_random(100);
        $newContract->awaitingActionBy = "ES";
        $newContract->is_direct = 1;

        if ($newContract->save()) {
            //success
            // Session::put('getCurrentID', $newContract::where('fileNo', trim($request['fileNo']))->value('contractID')); //set session
            Session::put('alertMessage', 1);
            return redirect()->route('loadProcurementdirect')->with('message', 'Your operation was successfully added.');
        } else {
            //could not save - perhaps network
            Session::put('alertMessage', 1);
            return redirect()->route('loadProcurementdirect')->with('error', 'Sorry we could not add this record! <br /> Please try again.');
        }
    }
    //End saving new contract

    //get all contract
    public function newLiabilityContract()
    {   //create new contract from existing one....code.....
        return view('njc.createProcurementFromLiability', $this->getAllContractParameters('all'));
    }


    //Start saving Contract from Liability
    public function saveContractLiability(Request $request)
    {
        Session::put('alertMessage', 1);
        $this->validate($request, [
            'fileNo' => 'required|max:255',
        ]);
        $newContract = new Contract;
        if ($newContract::where('fileNo', trim($request['fileNo']))->where('active', 0)->first()) {
            $getRecord = $newContract::where('fileNo', trim($request['fileNo']))->first();
            $newContract->fileNo = $getRecord->fileNo;
            $newContract->contractorID = $getRecord->contractorID;
            $newContract->award_date = $getRecord->award_date;
            $newContract->description = $getRecord->description;
            $newContract->token = str_random(100);
            $newContract->account_type = $getRecord->account_type;
            $newContract->economic_code = $getRecord->economic_code;
            $newContract->awaitingActionBy = "DP";
            if ($newContract->save()) {
                //success
                Session::put('alertMessage', 1);
                return redirect()->route('liabilityContract')->with('message', 'Your operation was successfully added.');
            } else {
                //could not save - perhaps network
                Session::put('alertMessage', 1);
                return redirect()->route('liabilityContract')->with('error', 'Sorry we could not create your record! <br /> A recent copy of the contract is processing.');
            }
        }else{
            if ($newContract::where('fileNo', trim($request['fileNo']))->where('active', 1)->first()) {
                return back()->with('error', 'Sorry, we notice that a copy of this contract is still in process! With this, we cannot create your new contract. <br /> Please try some other time.');
            }else{
                return back()->with('error', 'File number cannot be found on our system! But what are you trying to do?');
            }
        }
    }//end function
    //End saving Contract from Liability


    //Get all unpaid contract...
    public  function getUnpaidContractDetail(Request $request){
        $data = $this->getUnpaidLiability($request['fileNo']);
        return response()->json($data);
    }


    //Attach file to contract
    public function attachNewFileForContract(Request $request)
    {
        Session::put('alertMessage', 1);
        $this->validate($request, [
            //'file' => 'mimes:jpeg,jpg,bmp,png,gif,svg,doc,docx,pdf',
            'file' => 'mimes:jpeg,jpg,bmp,png,gif,svg,pdf|max:6000',
        ]);
        $file = $request->file('file');
        $contractID = trim($request['contractID']);
        //
        $messageCode = 0;
        $message     = 'Sorry, we cannot upload this file! Pls try again.';
        if($file or !empty($file) or $file != null) {
            //start uploading
            //$fileFolder = '/public/contractFile'; //Local
            $fileFolder = 'contractFile'; //Live
            $filePath = $this->basePath() . $fileFolder;
            $fileOriginalExtension = $file->getClientOriginalExtension();
            if (DB::table('create_contract')->where('contractID', $contractID)->first())
            {
                $getFileNoDB = DB::table('create_contract')->where('contractID', $contractID)->value('fileNo');
                $fileNewName = $getFileNoDB . '-' .$this->randomNo() . '.' . $fileOriginalExtension;
                if ($file->move($filePath, $fileNewName)) {
                    $fileModel = new ContractFile;
                    $fileModel->contractID = $contractID;
                    $fileModel->userID = Auth::user()->id;
                    $fileModel->file_name = $fileNewName;
                    $fileModel->fileNo = $getFileNoDB;
                    $fileModel->caption = trim($request['caption']);
                    $fileModel->file_extension = $fileOriginalExtension;
                    $fileModel->created_at = date('Y-m-d');
                    if ($fileModel->save()) {
                        $messageCode = 1;
                        $message = 'File successfully uploaded.';
                        return redirect()->back()->with('message', $message);
                        //return redirect()->route('startProcess')->with('message', $message);

                    } else {
                        return back()->with('error', $message);
                    }
                }
            }else{
                return back()->with('error', $message);
            }
        }else{
            return back()->with('error', $message);
        }//end if
    }


    //Edit Contract
    public function editContract(Request $request)
    {
        /*$this->validate($request, [
            'fileNo'        => 'required|string',
            'amount'        => 'required|numeric',
            'id'            => 'required|numeric',
            'contractorID'  => 'required|numeric',
            'awardDate'     => 'required|date',
        ]);*/

        $data['messageCode'] = 0;
        $data['message'] = 'Sorry we cannot update this record! Try again.';
        $data['getRecord'] = null;
        $id = trim($request['id']);
        $newContract = new Contract;
        if($newContract::where('contractID', $id)->first()) {
            if ($this->checkIfLiabilityTaken($id) > 0) {
                $checkUpdate = $newContract::where('contractID', $id)->update([
                    'description' => trim($request['description'])
                ]);
            }else{
                $checkUpdate = $newContract::where('contractID', $id)->update([
                    'fileNo' => trim($request['fileNo']),
                    'contractorID' => trim($request['contractorID']),
                    'amount' => trim($request['amount']),
                    'award_date' => trim($request['awardDate']),
                    'description' => trim($request['description'])
                ]);
            }
            if ($checkUpdate) {
                $data['messageCode'] = 1;
                $data['message'] = 'Your record was updated successfully.';
                $data['getRecord'] = $this->getPrecurementRecordID($id);
                return response()->json($data);
            } else {
                $data['message'] = 'No update was done on your information.';
                return response()->json($data);
            }
        }else{
            return response()->json($data);
        }
    }


    public function processContract(Request $request)
    {
        $data['messageCode'] = 0;
        $data['message'] = 'Sorry, we could not update this contract! May be your Session has expired or try to refresh this page.';
        //
        $contractID     	= trim($request['id']);
        $pushTo         	= trim($request['pushTo']);
        $comment        	= trim($request['comment']);
        $amount         	= str_replace(",", "", trim($request['amount']));
        $accountType    	= trim($request['accountType']);
        $economicCode   	= trim($request['economicCode']);
        $liabilityAmount 	= str_replace(",", "", trim($request['liabilityAmount']));
        $userTakeLiability 	= trim($request['userTakeLiability']);
        $ESReturn          	= trim($request['ESReturn']);
        //
        $newContract = new Contract;
        if ($newContract::where('contractID', $contractID)->first()) {
            if ($this->getUserRoleAndPermission() == "ES") {
                if($ESReturn == 1){
                    if(DB::table('tblaction_rank')->where('code', $pushTo)->where('contract_active', 0)->first())
                    {
                        $data['messageCode'] = 0;
                        $data['message'] = 'Sorry, you cannot return this contract to the selected name! Review and try again.';
                        return response()->json($data);
                    }else{
                        $newContract::where('contractID', $contractID)->update([
                            'awaitingActionby' => $pushTo,
                            'pushFrom' => $this->getUserRoleAndPermission(),
                        ]);
                        $data['messageCode'] = 1;
                        $data['message'] = 'Operation was successful. Click OK to refresh your records. Thank you';
                        $this->addComment($comment, $contractID, $liabilityAmount);
                        return response()->json($data);
                    }
                }else {
                    //check if user can approve and send/push to this push to
                    if(DB::table('tblaction_rank')->where('code', $pushTo)->where('cont_payment_active', 0)->first())
                    {
                        $data['messageCode'] = 0;
                        $data['message'] = 'Sorry, you cannot approve this contract and at the same time send it to the seleted name! Review the name and try again.';
                        return response()->json($data);
                    }
                    //check if contract has liability or amount
                    if(empty(DB::table('create_contract')->where('contractID', $contractID)->value('liability_amount')))
                    {
                        $data['messageCode'] = 0;
                        $data['message'] = 'You cannot approve this contract because no liability has been taken!';
                        return response()->json($data);
                    }
                    //Insert into new Contract Details Table
                    $ContractDetails = new ContractDetails;
                    $CD = $newContract::where('contractID', $contractID)->first();
                    $checkSuccess = $ContractDetails::updateOrCreate(['procurement_contractID' => $contractID], [
                        'fileNo' => $CD->fileNo,
                        'procurement_contractID' => $CD->contractID,
                        'contract_Type' => $CD->account_type,
                        'ContractDescriptions' => $CD->description,
                        'economicVoult' => $CD->economic_code,
                        'contractValue' => $CD->amount,
                        'companyID' => $CD->contractorID,
                        'beneficiary' => '',
                        'dateAward' => $CD->award_date,
                        'approvedBy' => Auth::user()->username,
                        'approvalStatus' => 1,
                        'approvalDate' => date('Y-m-d'),
                        'createdby' => '',
                        'datecreated' => date('Y-m-d'),
                        'openclose' => 1,
                        'paymentStatus' => 0,
                        'file_ex' => '',
                        'awaitingActionby' => $pushTo,
                        'voucherType' => 1,
                        'period' => DB::table('tblactiveperiod')->value('year'),
                    ]);
                    if ($checkSuccess) {
                        $newContract::where('contractID', $contractID)->update([
                            'awaitingActionby' => $pushTo,
                            'pushFrom' => $this->getUserRoleAndPermission(),
                            'period' => DB::table('tblactiveperiod')->value('year'),
                            'active' => 0,
                        ]);
                        $esCommentAndLiabilityAmount = $comment .' ('. DB::table('create_contract')->where('contractID', $contractID)->value('liability_amount'). ' liability approved)';
                        $this->addComment($esCommentAndLiabilityAmount, $contractID, '');
                        $data['messageCode'] = 1;
                        $data['message'] = 'Operation was successful. Click OK to refresh your records. Thank you';
                        return response()->json($data);
                    } else {
                        $data['messageCode'] = 0;
                        $data['message'] = 'Sorry, we could not update this contract! May be your Session has expired or try to refresh this page.';
                        return response()->json($data);
                    }
                    $data['messageCode'] = 0;
                    $data['message'] = 'Sorry, we cannot process your request! Refresh/Login again. Thanks';
                    return response()->json($data);
                }
            }elseif ($this->getUserRoleAndPermission() == "HEC") {
            	//Start Liability here
                if($userTakeLiability > 0) {
                	//take liability
                	//check if we have enough bal. to cater for liability
                     if( ($this->getRealBalanceValue($economicCode) < $liabilityAmount) || ($this->getRealBalanceValue($economicCode) <= 0)){
                	$data['messageCode'] = 0;
                	$data['message'] = 'Operation is not successful. Balance on this Economic Code is not enough!';
                	return response()->json($data); //stop further execution
                     }
                    if( ($this->getRealBalanceValue($economicCode) > $liabilityAmount) ){
                    	$newContract::where('contractID', $contractID)->update([
                        	'account_type' => $accountType,
                        	'economic_code' => $economicCode,
                        	'liability_amount' => $liabilityAmount,
                        	'awaitingActionby' => $pushTo,
                        	'pushFrom' => $this->getUserRoleAndPermission(),
                        	'period' => DB::table('tblactiveperiod')->value('year'),
                        	'updated_at' => date('Y-m-d'),
                    	]);
                    	//Voult Book Record Log
                    	$contractD = DB::table('create_contract')->where('contractID', $contractID)->first();
                    	$this->VotebookUpdate($contractD->economic_code, $contractD->contractID, $contractD->description, $contractD->amount, date('Y-m-d'), '1', $contractD->period);
                    	//VotebookUpdate($ecoID,$refNo,$remark,$amount,$trandate,$transtype,$period='')
                     	$hecCommentAndLiabilityAmount = $comment .' ('. DB::table('create_contract')->where('contractID', $contractID)->value('liability_amount'). ' liability taken)';
                    }
                }else{
                    //I dont want to take liability
                    $newContract::where('contractID', $contractID)->update([
                        'liability_amount' => null,
                        'awaitingActionby' => $pushTo,
                        'pushFrom' => $this->getUserRoleAndPermission(),
                    ]);
                    $hecCommentAndLiabilityAmount = $comment;
                }
                $this->addComment($hecCommentAndLiabilityAmount, $contractID, '');
                $data['messageCode'] = 1;
                $data['message'] = 'Operation was successful. Click OK to refresh your records. Thank you';
                return response()->json($data);
            }elseif ($this->getUserRoleAndPermission() <> "ES" && $this->getUserRoleAndPermission() <> null){
                $newContract::where('contractID', $contractID)->update([
                    'awaitingActionby' => $pushTo,
                    'pushFrom'         => $this->getUserRoleAndPermission(),
                ]);
                $this->addComment($comment, $contractID, '');
                $data['messageCode'] = 1;
                $data['message'] = 'Operation was successful. Click OK to refresh your records. Thank you';
                return response()->json($data);
            }else{
                return response()->json($data);
            }
            return response()->json($data);
        }//end check for valid contrac;tID
        return response()->json($data);
    }//end class


    //fetch Economic Code
    public function fetchEconomicCode(Request $request)
    {
        $data = $this->getEconomicCode(trim($request['allocationTypeID']), trim($request['contractTypeID']));
        return response()->json($data['ecoCode']);
    }


    public  function getBalance(Request $request){
        $value = $this->getRealBalanceValue($request['economicID']);
        return response()->json($value);
    }


    //generate random numbers
    public function randomNo()
    {
        return (uniqid().rand().uniqid());
    }


    //delete attached file
    public function deleteAttachedFile(Request $request)
    {
        $message = 'Not successful !';
        if(DB::table('contract_file')->where('id', trim($request['fileID']))->delete()) {
            $message = 'Successful.';
        }
        return response()->json($message);
    }
    //


    //delete attached file
    public function getAllComment(Request $request)
    {
        $data = $this->getAllCommentPerUser(trim($request['contractID']));
        return response()->json($data);
    }

    public function addComment($comment, $contractID, $notUse)
    {
        //add comment
        $newContract = new Contract;
        $newComment = new Comment;
        $newComment->comment = trim(preg_replace('/\s\s+/', ' ', $comment));
        $newComment->contractID = $contractID;
        $newComment->fileNoID = $newContract::where('contractID', $contractID)->value('fileNo');
        $newComment->userID = Auth::user()->id;
        $newComment->date = date('Y-m-d');
        $newComment->save();
        //
        return;
    }


    //delete attached file
    public function isUserHEC()
    {
        $data = strtoupper($this->getUserRoleAndPermission());
        return response()->json($data);
    }


    //get Unpaid Balance
    public function getUnpaidBalance(Request $request)
    {
        //$contract = new Contract;
        //$getUnpaidData = $contract::where('fileNo', $contract::where('contractID', $request['contractID'])->value('fileNo'))
        //   ->select('fileNo', 'amount', DB::raw("sum(liability_amount) as totalLiability"), DB::raw("amount - sum(liability_amount) as unPaidBalance"))
        //   ->first();
        $contract = new Contract;
        $getUnpaidData = $contract::where('fileNo', $contract::where('contractID', $request['contractID'])->value('fileNo'))
            ->where('create_contract.economic_code', '<>', null)
            ->leftjoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'create_contract.economic_code')
            ->leftjoin('tblcontractType', 'tblcontractType.ID', '=', 'tbleconomicCode.contractGroupID')
            ->leftjoin('tblallocation_type', 'tblallocation_type.ID', '=', 'tbleconomicCode.allocationID')
            ->select('*', DB::raw('count(create_contract.fileNo) AS countRecord'), 'tbleconomicCode.description as newDescription', 'tblcontractType.contractType as newContractType', 'tblallocation_type.allocation as newAllocation', 'fileNo', 'amount', DB::raw("sum(liability_amount) as totalLiability"), DB::raw("(amount) - sum(liability_amount) as unPaidBalance"))
            ->first();
        //
        return response()->json($getUnpaidData);
    }


    //get Amount For Contract Edit
    public static function getAmountForContractEdit($fileNo)
    {
        $contract = new Contract;
        $getAmount = $contract::where('fileNo', $fileNo)->where('amount', '<>', null)->value('amount');
        return $getAmount;
    }


    //check If Liability Taken
    public static function checkIfLiabilityTaken($contractID)
    {
        $contract = new Contract;
        $liabilityAmount = $contract::where('fileNo', $contract::where('contractID', $contractID)->value('fileNo'))->first();
        return $liabilityAmount->economic_code;
    }


    //get Previous Liability Economic Details
    public static function getPreviousLiabilityEconomicDetails(Request $request)
    {
        $contract = new Contract;
        $dataEconomicLiability = $contract::where('contractID', trim($request['contractID']))
            ->leftjoin('tbleconomicCode', 'tbleconomicCode.ID', '=', 'create_contract.economic_code')
            ->leftjoin('tblcontractType', 'tblcontractType.ID', '=', 'tbleconomicCode.contractGroupID')
            ->leftjoin('tblallocation_type', 'tblallocation_type.ID', '=', 'tbleconomicCode.allocationID')
            ->select('*', DB::raw('count(create_contract.fileNo) AS countRecord'), 'tbleconomicCode.description as newDescription', 'tblcontractType.contractType as newContractType', 'tblallocation_type.allocation as newAllocation', DB::raw("sum(liability_amount) as totalLiability"), DB::raw("amount - sum(liability_amount) as unPaidBalance"))
            ->first();
        return $dataEconomicLiability;
    }
	
	public function getAllContractWithDetails(){
		 return view('njc.viewAllContract', $this->getAllContractParameters('role'));
	}


}
