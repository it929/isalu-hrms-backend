<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use DB;

class BankListController extends Controller
{
   public function create()
   {
	   $data['allbanklist'] = DB::table('tblbanklist')->select('bankID', 'bank', 'Bankcode')
	   ->orderBy('bank', 'asc')->get();
	   
   	   return view('banklist.banklist', $data);

   }
   
   public function store(Request $request)
    { 
		$this->validate($request, [
			'bankName' => 'required|regex:/^[\pL\s\-]+$/u|unique:tblbanklist,bank',
			'Bankcode' => 'required|unique:tblbanklist,Bankcode'
		]);
		
		//Assign
		$bankname   = strtoupper(trim($request['bankName']));
		$Bankcode =trim($request['Bankcode']);
		
		//insert
		DB::table('tblbanklist')->insert(array( 
		'bank'  => $bankname,
		'Bankcode'  => $Bankcode
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
   
      public function update(Request $request){

$bankID             = $request['bankID'];
$bank               = $request['bank'];
$Bankcode                     = $request['Bankcode'];

DB::table('tblbanklist')->where('bankID','=',$bankID)->update(array( 
'bank'            => $bank,
'Bankcode'            => $Bankcode,
));
return redirect()->back()->with('message','Bank Updated Successfully ');

   }
}
