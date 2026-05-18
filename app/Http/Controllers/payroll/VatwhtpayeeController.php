<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;

class VatwhtpayeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexData()
    {
    	return DB::table('tblVATWHTPayee')
    	->leftjoin('tblbanklist','tblbanklist.bankID','=','tblVATWHTPayee.bankID')
    	->get();
    	
    }
     
    public function index()
    {	
        $data['banklist']= DB::table('tblbanklist')->get();
        $data['getDB']= $this->indexData();
        //
        return view('vatwhtpayee.vatwhtpayeePage', $data);
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
        $this->validate($request, [
            'payee' =>'required',
            //|unique:tblVATWHTPayee,payee
            'address' =>'required',
            /*'bankid'	=>'required',*/
            'accountno'	=>'required',
            
         ]);
 	//retrieve data from form
 	 $updateID 	= $request->input('updateID'); 
         $payee  	= $request->input('payee');
         $address     	= $request->input('address');
         $bank_branch 	= $request->input('bank_branch');
         $bankid 	= $request->input('bankid');
         $accountno	= $request->input('accountno');
         $sort_code	= $request->input('sort_code');
        
         
         $testing = DB::table('tblVATWHTPayee')->where('payee', $payee)->first();
         if(DB::table('tblVATWHTPayee')->where('ID', $updateID)->first())
         {
		//Update
         	 DB::table('tblVATWHTPayee')->where('ID', $updateID)->update([
         	 'payee' 	=> $payee, 
         	 'address' 	=> $address, 
         	 'bank_branch' 	=> $bank_branch, 
         	 'bankid'	=> $bankid, 
         	 'accountno' 	=> $accountno, 
         	 'sort_code' 	=> $sort_code 
         	 ]);
        	return redirect('/vat-wht-payee')->with('message', 'Your record was update successfully');
         }else{
           	//insert
          	$reallyStore = DB::table('tblVATWHTPayee')->insert(array(
	              'payee'     => $payee,
	              'address'   => $address,
	              'bank_branch' => $bank_branch,
	              'bankid'	=> $bankid,
	              'accountno' => $accountno,
	              'sort_code' => $sort_code,
	                                 
	      	  ));
         	return redirect('/vat-wht-payee')->with('message', 'New Payee was added to list');   
         }
         
         return redirect('/vat-wht-payee')->with('error', 'info not added');
          
         
			 
			//         if($reallyStore)
			  //       {
			   //          return redirect('/vat-wht-payee')->with('message', 'Payee was added to list');
			   //      }else{
			     //        
			       //  }
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
    public function editPayee($id)
    {
    	 $data['getDB']= $this->indexData();
    	 $data['banklist']= DB::table('tblbanklist')->get();
      	 $data['payeeRecord'] = DB::table('tblVATWHTPayee')->where('ID', $id)->first();
      	 //
      	 $data['getBankNameID'] = DB::table('tblbanklist')->where('bankID', DB::table('tblVATWHTPayee')->where('ID', $id)->value('bankid'))->select('bankID', 'bank')->first();
      
      return view('vatwhtpayee/vatwhtpayeePage', $data)->with('message', 'You can start editing your information now.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     
    /*public function update(Request $request)
    {
    	if(isset($payeeRecord)) {
    	
          $this->validate($request, [
            'payee' =>'required',
            'address' =>'required',
            //'bank_branch' =>'required',
            //'bank'	=>'required',
            ////'accountno'	=>'required',
            //'sort_code'	=>'required'
         ]);
         
	  $id = $request->input('vatid'); 
         $payee  = $request->input('payee');
         $address     = $request->input('address');
         $bank_branch = $request->input('bank_branch');
         $bank		= $request->input('bank');
         $accountno	= $request->input('accountno');
         $sort_code	= $request->input('sort_code');
         
         $getBankNameID = DB::table('tblbanklist')->where('bankID', DB::table('tblVATWHTPayee')->where('ID', $id)->value('bankid'))->select('bankID', 'bank')->first();
         dd($getBankNameID);
         
         ///$data['banklist'] = DB::tbale('tblbanklist')->where('bankid'

         DB::table('tblVATWHTPayee')->where('ID',$id)->update(['payee' => $payee, 'address' => $address, 'bank_branch' => $bank_branch, 'bankid' => $bankid, 'accountno' => $accountno, 'sort_code' => $sort_code ]);
         return redirect('/vat-wht-payee')->with('message', 'Payee was updated to list');
    	}
    	else{
    		return view ('/vat-wht-payee');
    	}
    }*/
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      
        DB::table('tblVATWHTPayee')->where('id', $id)->delete();

        return redirect('/vat-wht-payee')->with('message', 'Payee was deleted succesfully');
    }

}
