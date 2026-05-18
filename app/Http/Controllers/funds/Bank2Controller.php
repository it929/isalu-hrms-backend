<?php

namespace App\Http\Controllers\funds;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use DB;

class BankController extends ParentController
{
	public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
    	$this->division = $request->session()->get('division');
		$this->divisionID = $request->session()->get('divisionID');
                return $next($request);
        });
    }
	
    public function create()
    {
	  $data['bank_name']  = DB::table('tblbanklist')->get();
	  // $data['allCode'] = DB::table('tblbank')
	  // ->leftJoin('tblbanklist', 'tblbank.bankID', '=', 'tblbanklist.bankID')
	  // ->select('tblbanklist.bank', 'bank_code', 'sort_code')
	  // ->where('divisionID', $this->divisionID)
	  // ->get();
   	  return view('bank.bank', $data);
   }
   
   public function store(Request $request)
    { 
		$this->validate($request, [
			'bankName'    => 'required|numeric',
			'bankCode'    => 'required|alpha_num',
			'sortCode'    => 'required|alpha_num',
		]);
		$bankID    = trim($request['bankName']);
		$bankCode  = trim($request['bankCode']);
		$sortCode  = trim($request['sortCode']);
		if (is_null(DB::table('tblbank')->where('bankID', $bankID)->where('divisionID', $this->divisionID)->first())) 
		{
			DB::table('tblbank')->insert(array( 
				'bankID'       => $bankID,
				'bank_code'    => $bankCode,
				'sort_code'    => $sortCode,
				'divisionID'   => $this->divisionID,
			));
			$this->addLog('New bank added with bankID ' .$bankID.' and division ' . $this->division);
			return redirect('bank/create')->with('msg', 'New bank successfully added!');
		}
		else
		{
			DB::update('update tblbank set bank_code = ?, sort_code = ? where bankID = ? and divisionID = ?', [$bankCode, 
				$sortCode, $bankID, $this->divisionID]);
			// DB::table('tblbank')->where('bankID', $bankID)->update(array( 
			// 	'bank_code'    => $bankCode, 
			// 	'sort_code'    => $sortCode,
			// 	'divisionID'   => $this->divisionID,			
			// ));
			$this->addLog('Bank updated with bankID ' .$bankID.' and division ' . $this->division);
			return redirect('bank/create')->with('msg', 'Bank updated successful!');
		}
    }

	public function findBank(Request $request)
    {    	
    	$this->validate($request, [
    		'bankName' => 'required|numeric',
    		]);
    	$bankID = $request->input('bankName');
		$bankRecord = DB::table('tblbank')
				    ->select('sort_code', 'bank_code')
					->where('bankID', '=', $bankID)
					->where('divisionID', $this->divisionID)
					->first();
		return response()->json($bankRecord);
    }

   
   
}
