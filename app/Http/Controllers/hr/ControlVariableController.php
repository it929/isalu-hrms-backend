<?php

namespace App\Http\Controllers\hr;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use Session;
class ControlVariableController extends functionController 
{
    public function index(Request $request)
    {
        
        
       
        
        $data['particulars']= $request['particulars'];
        $data['accounthead']= $request['accounthead'];
        $data['allocationtype']= $request['allocationtype'];
        $data['economiccode']= $request['economiccode'];
        $data['description']= trim($request['description']);
        $data['getep'] = DB::table('tblearningParticular')->get();
        
        $data['ID'] = Session::get('particularSession');
        $data['BudgetType'] =$this->BudgetType();
        $data['AllocationSource'] =$this->AllocationSource();
        $data['EconomicCode'] =$this->EconomicCode($data['allocationtype'],$data['accounthead']);
        
        if ( isset( $_POST['add'] ) ) {
		$this->validate($request, [
		'particulars'      	=> 'required'
		,'description'      	=> 'required'
		
		]);
		//if ($data['particulars']==1){
		//$this->validate($request, ['economiccode'      	=> 'required']);
		//}
			$reallyStore = DB::table('tblcvSetup')->insert(array(
		            'particularID' =>$request['particulars'],
		            'description' => trim($request['description']),
		            'economiccode' => $request['economiccode'],
		            'status' =>1,
		     ));
		 }
		 
        $data['getedj'] = $this->EarningDeductionList($data['particulars']);
        return view('controlVariable.controlVariable',$data)->with('message', 'particular added');
        die("In use");
        
        
        
        // die("In use finished coop repayment");
         
         // coop savings
         $rawdata= DB::SELECT ("SELECT * FROM `TABLE 133`  ");
	  foreach ($rawdata as $value) {
	  $reallyStore = DB::table('tblstaffCV')->insert(array(
		            'courtID' =>9,
		            'divisionID' => 15,
		            'staffid' => $value->ID,
		            'cvtype' => 2,
		            'cvID' => 15,
		            'amount' => $value->Contribution,
		            'targetAmount' => '',
		            'status' => 1,
		            'recycling' =>1,
		     ));
	  }
        die("In use finished coop savings");
        // Loan repayment
   	   $rawdata= DB::SELECT ("SELECT * FROM `TABLE 131`  ");
	  foreach ($rawdata as $value) {
	  $reallyStore = DB::table('tblstaffCV')->insert(array(
		            'courtID' =>9,
		            'divisionID' => 15,
		            'staffid' => $value->ID,
		            'cvtype' => 2,
		            'cvID' => 16,
		            'amount' => $value->rf,
		            'targetAmount' => $value->bal,
		            'status' => 1,
		            'recycling' =>0,
		     ));
	  }
    }

   
    public function store(Request $request)
    {
        $this->validate($request, [
            'particulars' =>'required',
            'description' =>'required|unique:tblcvSetup,description',
         ]);

         $particulars = trim($request['particulars']);
         $description = trim($request['description']);
         $description = str_replace('\'', '', $description); 
        log::info('char Description',  ['is' => $description]);

         $reallyStore = DB::table('tblcvSetup')->insert(array(
            'particularID' =>$particulars,
            'description' => $description,
            'status' =>1,
     ));

     if($reallyStore)
    {
        Session::put('particularSession', $particulars);
        return redirect('/control-variable')->with('message', 'particular added');

    }else{
        Session::put('particularSession', $particulars);
        return redirect('/control-variable')->with('error', 'info not added');
    }
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
        $this->validate($request, [
            'descriptions' =>'required',
         ]);

        $partDesc = $request['descriptions'];
        $partId = $request['partid'];
        $partStatus = $request['partStatus'];
        log::info('Edited Description',  ['partDesc' => $partDesc]);
        //log::info('Edited status',   ['partStatus' => $partStatus]);
        DB::table('tblcvSetup')->where('ID',$partId)
        ->update(['description' => $partDesc,'status' => $partStatus]);
        return redirect('/control-variable')->with('message', 'particular edited');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
