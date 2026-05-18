<?php

namespace App\Http\Controllers\payroll;


use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ControlVariableController extends functionController 
{
    public function index(Request $request)
    {
        $data['particulars']= $request['particulars'];
        $data['accounthead']= $request['accounthead'];
        $data['allocationtype']= $request['allocationtype'];
        $data['economiccode']= $request['economiccode'];
        $data['rank'] = $request['rank'];
        $data['description']= trim($request['description']);
        $data['address'] = '';
        // $data['bank']= trim($request['bank']);
        // $data['account_name']= trim($request['account_name']);
        // $data['account_number']= trim($request['account_number']);
        $data['getep'] = DB::table('tblearningParticular')->get();
        
        $data['ID'] = Session::get('particularSession');
       
        
        if ( isset( $_POST['add'] ) ) {
		$this->validate($request, [
            'particulars'      	=> 'required',
            'description'      	=> 'required|unique:tblcvSetup',
            // 'bank'      	=> 'required',
            // 'account_name'      	=> 'required',
            // 'account_number'      	=> 'required',
            'rank'      	=> 'required'
		]);
		//if ($data['particulars']==1){
		//$this->validate($request, ['economiccode'      	=> 'required']);
		//}
			$reallyStore = DB::table('tblcvSetup')->insert(array(
                'particularID' =>$request['particulars'],
                'rank'=>$request['rank'],
                'description' => trim($request['description']),
                // 'bank' => trim($request['bank']),
                // 'account_name' => trim($request['account_name']),
                // 'account_number' => trim($request['account_number']),
                'economiccode' => 1,
                'status' =>1,
		     ));
		 }
		//  dd("ugjgjtttt");
        $data['getedj'] = $this->EarningDeductionList($data['particulars']);
        // dd($data['getedj']);
        return view('payroll.controlVariable.controlVariable',$data)->with('message', 'particular added');
        
    }

   
    public function store(Request $request)
    {
        $this->validate($request, [
            'particulars' =>'required',
           
            'description' =>'required|unique:tblcvSetup,description',
            'bank' =>'required',
            'account_name' =>'required',
            'account_number' =>'required',
         ]);

         $particulars = trim($request['particulars']);
         
         $description = trim($request['description']);
         $bank = trim($request['bank']);
         $account_name = trim($request['account_name']);
         $account_number = trim($request['account_number']);
         $description = str_replace('\'', '', $description); 
        log::info('char Description',  ['is' => $description]);

         $reallyStore = DB::table('tblcvSetup')->insert(array(
            'particularID' =>$particulars,
            'description' => $description,
            'bank' => $bank,
            'account_name' => $account_name,
            'account_number' => $account_number,
            'rank' => $ranks,
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
        $partBank = $request['bank'];
        $partAccName = $request['account_name'];
        $partAccNum = $request['account_number'];
        $partId = $request['partid'];
        $ranks = $request['rank'];
        $partStatus = $request['partStatus'];
        log::info('Edited Description',  ['partDesc' => $partDesc]);
        //log::info('Edited status',   ['partStatus' => $partStatus]);
        DB::table('tblcvSetup')->where('ID',$partId)
        ->update(['description' => $partDesc, 'bank' => $partBank, 'account_name' => $partAccName, 'account_number' => $partAccNum,'status' => $partStatus, 'rank' => $ranks,
        ]);
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
        // dd($id);
        
        $setupid=DB::table('tblstaffCV')->where('cvID', $id)->exists();
        $learndingid=DB::table('tblotherEarningDeduction')->where('CVID', $id)->exists();
        if ($setupid && $learndingid) {
            return redirect('/control-variable')
            ->with('alert','Control Variable is in use');
        }
        $reallyDel = DB::table('tblcvSetup')->where('ID', $id)->delete();
        return redirect('/control-variable')->with('alert','deleted successfully');
    }
    
    //Create
    public function getDeduction()
    {
		$deductions = DB::table('tblcvSetup')->where("particularID", "=", 2)->paginate(500); //->get(['ID', 'description', 'isPayable']); //
        return view('controlVariable.deduction', compact("deductions"));
    }

    //=======Function to update deduction STARTS=========
    public function updateDeduction(Request $request)
    {   
        $this->validate($request, [
            'isPayableValue' =>'required|array',
            'isRemitaDeductionValue' =>'required|array',
         ]);
         $payableStatus = $request['isPayableValue'];
         $remitaDeductionStatus = $request['isRemitaDeductionValue'];

         $deductions = DB::table("tblcvSetup")->where("particularID", 2)->get(['ID']);
         try{
            foreach ($deductions as $key=>$deduction)
            {
                $payableValue = ($payableStatus[$key] ? 1 : 0);
                $remitaDedValue = ($remitaDeductionStatus[$key] ? 1 : 0);

                DB::table("tblcvSetup")
                    ->where('ID', $deduction->ID)
                    ->update([
                        'isPayable' => $payableValue, 
                        'remitaDeduction' => $remitaDedValue
                ]);
            }
         }catch(\Throwable $err){
            return redirect()->back()->with('error', 'Sorry, unable to update your record! Try again.'); 
         }
         return redirect()->back()->with('message', 'Record was updated successfully.'); 
    }
    
}
