<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use DB;
use App\staffModel;
use Session;

class CurrentStateController extends ParentController
{
    //
    public function create()
    {	
    	$data['currentBank']    = DB::table('tblbanklist')->get();
    	$data['currentState']   = DB::table('tblcurrent_state')->leftjoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblcurrent_state.bank')->select('*', 'tblcurrent_state.id as stateID', 'tblcurrent_state.bank as stateBankID', 'tblbanklist.bank as bankName')->orderBy('tblcurrent_state.id', 'Desc')->get();

	    return view('currentState/create', $data);
    }
    
    
    public function store(Request $request)
    {	
    	$this->validate($request, [
		    'stateName'     => 'required|string',
		    'stateAddress'  => 'string',
		    'bankName'      => 'required|numeric',
		    'accountNumber' => 'required|numeric',
		]);
		
		try
        {
    	    $is_save = DB::table('tblcurrent_state')->insert([
    		    'state'         => $request['stateName'],
    		    'address'       => $request['stateAddress'],
    		    'bank'          => $request['bankName'],
    		    'account_no'    => $request['accountNumber'],
    		    'status'        => 1
    		]);
        }catch (\exception $e) {
            return redirect()->route('createCurrentState')->with('error', 'An error occurred while processing your record!');
        }
        
		if($is_save)
		{
		    return redirect()->route('createCurrentState')->with('message', 'Your record has been saved successfully.');
		}
    	return redirect()->route('createCurrentState')->with('error', 'Sorry, we are unable to save your record! Try again.');
    }
   
   
   
    
    public function update(Request $request)
    {
		$this->validate($request, [
		    'stateName'     => 'required|string',
		    'stateAddress'  => 'string',
		    'bankName'      => 'required|numeric',
		    'accountNumber' => 'required|numeric',
		    'stateStatus'   => 'numeric'
		]);
		
		try
        {
    	    $is_save = DB::table('tblcurrent_state')->where('tblcurrent_state.id', $request['stateID'])->update([
    		    'state'         => $request['stateName'],
    		    'address'       => $request['stateAddress'],
    		    'bank'          => $request['bankName'],
    		    'account_no'    => $request['accountNumber'],
    		    'status'        => $request['stateStatus']
    		]);
        }catch (\exception $e) {
            return redirect()->route('createCurrentState')->with('error', 'An error occurred while processing your record!');
        }
        
		if($is_save)
		{
		    return redirect()->route('createCurrentState')->with('message', 'Your record has been saved successfully.');
		}
    	return redirect()->route('createCurrentState')->with('error', 'Sorry, we are unable to save your record! Try again.');
    }
    
    

    public function destroy($id)
    {
        try
        {
            if(DB::table('tblcurrent_state')->where('tblcurrent_state.id', $id)->first())
            {
                if(!DB::table('tblper')->where('tblper.current_state', $id)->first())
                {
                    if(DB::table('tblcurrent_state')->where('tblcurrent_state.id', $id)->delete())
                    {
                        return redirect()->route('createCurrentState')->with('message', 'Your record has been deleted successfully.');
                    }
                    return redirect()->route('createCurrentState')->with('error', 'Sorry, we cannot delete this record now. An error occurred.');
                }
            }
        }catch (\exception $e) {
            return redirect()->route('createCurrentState')->with('error', 'An error occurred while processing your record!');
        }
        //
        return redirect()->route('createCurrentState')->with('error', 'Sorry, we cannot delete this record now. Is in use.');
        
    }
    
    
}//
