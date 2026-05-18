<?php

namespace App\Http\Controllers\funds;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use DB;

class BankListController extends ParentController
{
   public function create()
   {
	   $data['allbanklist'] = DB::table('tblbanklist')->select('bankID', 'bank')
	   ->orderBy('bank', 'asc')->get();
	   
   	   return view('banklist.banklist', $data);

   }
   
   public function store(Request $request)
    { 
		$this->validate($request, [
			'bankName' => 'required|regex:/^[\pL\s\-]+$/u|unique:tblbanklist,bank'
		]);
		
		//Assign
		$bankname   = strtoupper(trim($request['bankName']));
		
		//insert
		DB::table('tblbanklist')->insert(array( 
		'bank'  => $bankname
		));
		
		//$this->addLog('New bank List added');
		//Redirect
		return redirect('banklist/create')->with('msg', 'New bank successfully added to bank List!');
	}
	
	
	public function delete($bankID = Null)
   {
	    //delete
		if($bankID != Null){
			DB::table('tblbanklist')->where('bankID', '=', $bankID)->delete();
			//this->addLog('Bank list deleted');
			$data['allbanklist'] = DB::table('tblbanklist')->select('bankID', 'bank')
			->orderBy('bank', 'Asc')->get();
		}
   	    return view('banklist.banklist', $data);
   }
}
