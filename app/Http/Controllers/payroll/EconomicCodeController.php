<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use Session;

class EconomicCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $getallocationId = Session::get('allocationId');
        $getcontractGroupId = Session::get('contractGroupId');
        //dd($getcontractGroupId);
        $data['getallocationId'] =  $getallocationId;
        $data['getcontractGroupId'] =  $getcontractGroupId;
        $data['allocationtype'] =DB::Select("SELECT * FROM `tblallocation_type` where status=1");
        $data['contracttype'] = DB::table('tblcontractType')->get();
        
       
        $data['EconHead']= DB::table('tbleconomicHead')->where('contractTypeID',$getcontractGroupId)->get();
            

        $data['getEconCode'] = $this->QueryTable($getallocationId,$getcontractGroupId) ;
            
        return view('economicCode.economicCode', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, 
        [
        'allocationId' =>'required',
        'contractGroupId' =>'required',
        'employeeHeadId' =>'required',
        'economicCode' =>'required',
        'economicDescription' =>'required',]
        );

        $allocationID = $request->input('allocationId');
        $contractGroupID = $request->input('contractGroupId');
        $employeeHeadID = $request->input('employeeHeadId');
        $EconomicCode = $request->input('economicCode');
        $EconomicDescription = $request->input('economicDescription');
        
      

        $reallyStore = DB::table('tbleconomicCode')->insert(array(
            'allocationID' =>$allocationID,
            'contractGroupID' => $contractGroupID,
            'economicHeadID' => $employeeHeadID,
            'economicCode' =>$EconomicCode,
            'description' => $EconomicDescription,
     ));

     if($reallyStore)
        {
            Session::put('allocationId', $allocationID);
            Session::put('contractGroupId', $contractGroupID);
            return redirect('/economic-code')->with('message', 'Economic Code added successfully');;
    
        }else{
            
            return redirect('/economic-code')->with('error', 'Economic Code not added');
        }
        
    }

    public function reload(Request $request)
    {
        Session::forget('allocationId');
        $getallocationId = $request->input('getAlloId');

        Session::forget('contractGroupId');
        $getcontractGroupId = $request->input('getContractId');

        Session::put('allocationId', $getallocationId);
        Session::put('contractGroupId', $getcontractGroupId);
        return redirect('/economic-code');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request, 
        [
        'economicCode' =>'required',
        'economicDescription' =>'required',
        'ecStatus' =>'required',]
        );

        $updateID = $request->input('economicCodeId');
        $updateCode = $request->input('economicCode');
        $updateDesc = $request->input('economicDescription');
        $EcStatus = $request->input('ecStatus');

      

        $reallyUpdate =DB::table('tbleconomicCode')->where('ID',$updateID)
        ->update(['economicCode' => $updateCode,'description' => $updateDesc, 'status' => $EcStatus]);
        
        return redirect('/economic-code')->with('message', 'Updated successfully');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
        $inBudget = DB::table('tblbudget')->where('economicCodeID', $id)->value('economicCodeID');
        $inContract = DB::table('tblcontractDetails')->where('ecnomicVoult', $id)->value('ecnomicVoult');
       
        if($inBudget==$id && $inContract==$id)
        {
            return redirect('/economic-code')->with('error','Cannot delete active economic code');
        }
        else
        {
            DB::table('tbleconomicCode')->where('ID', $id)->delete();
            return redirect('/economic-code')->with('message','Economic Code successfully deleted');
           
        }
        
    }

    Public function QueryTable($getallocationId,$getcontractGroupId){
        $qallocation=1;
        if($getallocationId!=''){$qallocation="`allocationID`='$getallocationId'";}
        
        $qcourtGroup=1;
        if($getcontractGroupId!=''){$qcourtGroup="`contractGroupID`='$getcontractGroupId'";}
        
        $List= DB::Select("SELECT tbleconomicCode.ID as IDs, tblallocation_type.allocation,
         tblcontractType.contractType, tbleconomicHead.economicHead, tbleconomicCode.economicCode,
         tbleconomicCode.description, tbleconomicCode.status
          FROM `tbleconomicCode` INNER JOIN tblallocation_type ON 
          tbleconomicCode.allocationID = tblallocation_type.ID
          INNER JOIN tblcontractType ON tbleconomicCode.contractGroupID=tblcontractType.ID 
          INNER JOIN tbleconomicHead ON tbleconomicCode.economicHeadID=tbleconomicHead.ID WHERE $qallocation and $qcourtGroup");
        
        return $List;
        }

        
}
