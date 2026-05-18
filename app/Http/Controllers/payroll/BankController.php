<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use DB;
use Session;

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
          $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

	  $data['bank_name']  = DB::table('tblbanklist')->get();
	  $data['court'] =  DB::table('tbl_court')->get();

	  $courtSessionId = session('anycourt');

	   $data['allCode'] = DB::table('tblbank')
	  ->leftJoin('tblbanklist', 'tblbank.bankID', '=', 'tblbanklist.bankID')
	   ->join('tbl_court', 'tbl_court.id','=','tblbank.courtID')
	   ->paginate(30);

	   if($courtSessionId)
	   {
	   	  $data['allCode'] = DB::table('tblbank')
	   ->leftJoin('tblbanklist', 'tblbank.bankID', '=', 'tblbanklist.bankID')
	   ->join('tbl_court', 'tbl_court.id','=','tblbank.courtID')
	   ->where('tblbank.courtID','=',$courtSessionId)
	   ->paginate(30);
	   }
   	  return view('bank.bank', $data);
   }
   
   public function store(Request $request)
    { 
		$this->validate($request, [
			'bankName'    => 'required|numeric',
			'bankCode'    => 'required|alpha_num',
			'sortCode'    => 'required|alpha_num',
			'court'       => 'required|numeric',
		]);
		$bankID    = trim($request['bankName']);
		$bankCode  = trim($request['bankCode']);
		$sortCode  = trim($request['sortCode']);
		$court     = trim($request['court']);
		if (is_null(DB::table('tblbank')->where('bankID', $bankID)->where('courtID', $court)->where('divisionID', $this->divisionID)->first())) 
		{
			DB::table('tblbank')->insert(array( 
				'bankID'       => $bankID,
				'bank_code'    => $bankCode,
				'sort_code'    => $sortCode,
				'courtID'   => $court,
			));
			$this->addLog('New bank added with bankID ' .$bankID.' and division ' . $this->division);
			return redirect('bank/create')->with('msg', 'New bank successfully added!');
		}
		else
		{
			DB::update('update tblbank set bank_code = ?, sort_code = ? where bankID = ? and courtID = ?', [$bankCode, 
				$sortCode, $bankID, $court]);
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
					->where('courtID','=', session('anycourt'))
					->first();
		return response()->json($bankRecord);
    }

     public function sessionset(Request $request)
    {
         $ses    = Session::put('anycourt', $request['courtID']);
        
         return response()->json('Successfull');
         

    }

   
   
}
