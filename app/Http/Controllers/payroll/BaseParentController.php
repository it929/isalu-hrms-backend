<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use App\Models\Contractor;
use App\Models\Contract;
use App\Models\Comment;
use DB;
use Auth;
use Session;

class BaseParentController extends Basefunction
{
    //get all contractors
    public function getAllContractor($paginate)
    {
        $Contractor = new Contractor;
        if(is_numeric($paginate)){
            return $Contractor::where('status', 1)->where('type', 1)->orderBy('id', 'Desc')->paginate($paginate);
        }else{
            return $Contractor::where('status', 1)->where('type', 1)->orderBy('id', 'Desc')->get();
        }
    }

    //get all procurement records
    public function getAllPrecurementRecord($paginate, $viewStatus)
    {
        $Contract = new Contract;
        if(is_numeric($paginate)){
            if($viewStatus == 'role')
            {
                $data = $Contract::where('create_contract.active', 1)
                    ->Join('tblcontractor', 'tblcontractor.id', '=', 'create_contract.contractorID')
                    ->where('awaitingActionby', $this->getUserRoleAndPermission())
                    ->orderBy('create_contract.contractID', 'Desc')
                    ->paginate($paginate);
            }else // $viewStatus ==all
            {
                $data = $Contract::where('create_contract.active', 1)
                    ->Join('tblcontractor', 'tblcontractor.id', '=', 'create_contract.contractorID')
                    ->orderBy('create_contract.contractID', 'Desc')
                    ->paginate($paginate);
            }

        }else{
            if($viewStatus == 'role') {
                $data = $Contract::where('create_contract.active', 1)
                    ->Join('tblcontractor', 'tblcontractor.id', '=', 'create_contract.contractorID')
                    ->where('awaitingActionby', $this->getUserRoleAndPermission())
                    ->orderBy('create_contract.contractID', 'Desc')
                    ->get();
            }else{
                $data = $Contract::where('create_contract.active', 1)
                    ->Join('tblcontractor', 'tblcontractor.id', '=', 'create_contract.contractorID')
                    //->select('*', DB::raw("(sum(amount) - sum(liability_amount)) as newAmount"))
                    ->orderBy('create_contract.contractID', 'Desc')
                    ->get();
            }
        }
        return $data;

    }

    //find one record procurement
    public function getPrecurementRecordID($id)
    {
        return $data = DB::table('create_contract')
            ->Join('tblcontractor', 'tblcontractor.id', '=', 'create_contract.contractorID')
            ->where('create_contract.contractID', $id)
            ->first();
    }

    //get Account type and Allocation type
    public function getContractAllocationType()
    {
        //Contract Type
        $data['contractType'] = DB::table('tblcontractType')->where('status', 1)->get();
        //Allocation Type
        $data['allocationType'] = DB::table('tblallocation_type')->where('status', 1)->get();
        //ACTION CODES
        $data['actionRank'] = DB::table('tblaction_rank')
            ->orWhere('code', 'DFA')
            ->orWhere('code', 'DDFA')
            ->orWhere('code', 'CA')
            ->get();
        return $data;
    }

    //Economic Code
    public function getEconomicCode($contractTypeID, $allocationTypeID)
    {
        //return ['contractTypeID'=>$contractTypeID];
        $data['ecoCode'] = DB::table('tbleconomicCode')
            ->join('tblallocation_type', 'tblallocation_type.ID', '=', 'tbleconomicCode.allocationID')
            ->join('tblcontractType', 'tblcontractType.ID', '=', 'tbleconomicCode.contractGroupID')
            ->select('*', 'tblallocation_type.ID as allocationTypeID', 'tblcontractType.ID as contractTypeID', 'tbleconomicCode.ID as economicID')
            //->where('tbleconomicCode.allocationID', $allocationTypeID)
            ->where('tbleconomicCode.contractGroupID', $contractTypeID)
            ->where('tbleconomicCode.status', 1)
            ->get();
        foreach($data['ecoCode'] as $key=> $value)
        {
            $lis=(array)$value;
            $lis['bal']=$this->VoultBalance($value->economicID);
            $value=(object)$lis;
            $data['ecoCode'][$key]=$value;
        }
        return $data;
    }

    //get Account type and Allocation type
    public function getRealBalanceValue($economicID)
    {
        $data = $this->VoultBalance($economicID); //remember to plugin the right function
        return $data;
    }


    //get user Role and permission
    public function getUserRoleAndPermission()
    {
        $getUserRole = DB::table('tblaction_rank')
            ->leftJoin('users', 'users.username', '=', 'tblaction_rank.userid')
            ->where('tblaction_rank.userid', Auth::user()->username)
            ->value('tblaction_rank.code');
        if($getUserRole)
        {
            return $getUserRole;
        }
        return null;
    }


    //get all liability records
    public function getAllLiability()
    {
        $contract = new Contract;
        return $contract::where('liability_amount', '<>', null)
            ->Join('tblcontractor', 'tblcontractor.id', '=', 'create_contract.contractorID')
            ->havingRaw('amount <> sum(liability_amount)')
            ->where('liability_amount', '<>', 0)
            ->select('fileNo', 'liability_amount', 'amount', DB::raw("sum(liability_amount) as totalLiability"), DB::raw("amount - sum(liability_amount) as unpaidAmount"))
            ->orderBy('contractID', 'Desc')
            ->distinct()
            ->groupby('fileNo')
            ->get();
    }

    ///
    public function getUnpaidLiability($fileNo)
    {
        $contract = new Contract;
        return $contract::where('fileNo', $fileNo)
            ->Join('tblcontractor', 'tblcontractor.id', '=', 'create_contract.contractorID')
            ->havingRaw('amount <> sum(liability_amount)')
            ->select('*', DB::raw("sum(liability_amount) as totalLiability"), DB::raw("amount - sum(liability_amount) as unpaidAmount"))
            ->orderBy('contractID', 'Desc')
            ->distinct()
            ->groupby('fileNo')
            ->first();
    }
    //

    //View all comments
    public function getAllCommentPerUser($contractID)
    {
        $comment = new Comment;
        return $comment::where('contract_comment.contractID', $contractID)
            ->leftJoin('users', 'users.id', '=', 'contract_comment.userID')
            ->orderBy('contract_comment.commentID', 'Desc')
            ->get();
    }

    //get all action rank
    public function getAllActionRank()
    {
        if($this->getUserRoleAndPermission() == "ES")
        {
            return DB::table('tblaction_rank')
                ->where('code', '<>', $this->getUserRoleAndPermission())
                ->where('contract_active', 1)
                ->Orwhere('cont_payment_active', 1)
                ->where('status', 1)->get();
        }else{
            return DB::table('tblaction_rank')
                ->where('code', '<>', $this->getUserRoleAndPermission())
                ->where('contract_active', 1)
                ->where('status', 1)->get();
        }

    }


    //View all contracts
    public function getAllContractParameters($viewStatus)
    {
        $data['alertMessage'] = Session::get('alertMessage');
        $data['getUnpaidContract'] = $this->getAllLiability();
        $data['contractor'] = $this->getAllContractor(null);
        $data['allContract'] = $this->getAllPrecurementRecord(20, $viewStatus);
        $getAllData = $this->getContractAllocationType();
        $data['contractType'] = $getAllData['contractType'];
        $data['allocationType'] = $getAllData['allocationType'];
        $data['actionRank'] = $getAllData['actionRank'];
        $data['userRole']    = $this->getUserRoleAndPermission();
        $data['getTraackAction'] = $this->getAllActionRank();
        Session::put('currentUser', $this->getUserRoleAndPermission());
        $key = 1;
        $totalUpload = array();
        foreach ($data['allContract'] as $list)
        {
            $totalUpload[$key][$list->contractID] = DB::table('contract_file')->where('contractID', $list->contractID)->count();
            $key++;
        }
        $data['totalUpload'] = $totalUpload;
        $data['allFileAttached'] = DB::table('contract_file')->get();
        //Set User Role Name
        $getUserRoleDescription = DB::table('tblaction_rank')
            ->leftJoin('users', 'users.username', '=', 'tblaction_rank.userid')
            ->where('tblaction_rank.userid', Auth::user()->username)
            ->value('tblaction_rank.description');
        Session::put('UserRoleName', $getUserRoleDescription);
        //
        return $data;
    }


}