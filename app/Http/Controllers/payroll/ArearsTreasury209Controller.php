<?php

namespace App\Http\Controllers\payroll;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;

class ArearsTreasury209Controller extends ParentController
{
	public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
    	// $this->division   = $request->session()->get('division');
		// $this->divisionID = $request->session()->get('divisionID');
                return $next($request);
        });
    }
    public function loadView()
   {
		$data['bank']  = DB::table('tblper')
			//  ->where('tblper.divisionID', '=', $this->divisionID)
			 ->select('tblbanklist.bank', 'tblper.bankID')
			 ->distinct()
			 ->join('tblbanklist', 'tblper.bankID', '=', 'tblbanklist.bankID')
			 ->orderBy('bank', 'Asc')
			 ->get();
		$data['reporttype'] = DB::table('tbladmincode')
			 ->select('codeID', 'addressName', 'determinant')
	    	 ->orderBy('addressName', 'Asc')
			 ->get();
		$data['workingstate'] = DB::table('tblstates')
		 	 ->select('StateID', 'State')
			 ->distinct()
	    	 ->orderBy('State', 'Asc')
			 ->get();

		$data['CourtInfo'] = $this->CourtInfo();
		if ($data['CourtInfo']->courtstatus == 0) {
			$request['court'] = $data['CourtInfo']->courtid;
		}
		if ($data['CourtInfo']->divisionstatus == 0) {
			$request['division'] = $data['CourtInfo']->divisionid;
		}
		 
		$data['courtDivisions']  = DB::table('tbldivision')->get();

		$data['curDivision'] = $this->curDivision(Auth::user()->id);
   	    return view('arearsT209.loadT209', $data);
   }
   
   public function curDivision($userId){
	$currentDivision = DB::table("users")
							->join('tbldivision', 'tbldivision.divisionID', '=', 'users.divisionID')
							->where('users.id', '=', $userId)
							->select('tbldivision.division', 'tbldivision.divisionID')
							->first();
   return $currentDivision;
  }

   public function view(Request $request)
    { 
		$this->validate($request, [
			'reportType'    => 'required|string',
			'month'         => 'required|alpha',
			'year'          => 'required|numeric',
			//'bank'          => 'required|string',
			//'bankGroup'     => 'required|numeric',
			// 'workingState'  => 'required_if:reportType,tax|string',
			'workingState'  => 'required_if:reportType,tax',
		]);
		$val            = trim($request['reportType']);

		$month          = strtoupper(trim($request['month']));
		$year           = trim($request['year']);
		$bank           = trim($request['bank']);
		//$bankgroup      = trim($request['bankGroup']);
		$working_state   = trim($request['workingState']);
		$data['bank'] = $bank;
		
		$noRecord = 0;
		$total = 0.00;
		$data['type'] = $val;

     $data['scode'] = DB::table('tbladmincode')->where('determinant', '=', $val)->first();

		if($val == 'tax')
		{
			 //if($bank == '')
			//{
			$data['details'] = DB::table('tblarears_payment')
			->where('year', '=', $year)
			->where('month', '=', $month)
			//->where('bankGroup', '=', $bankgroup)
			->where('division', '=', $request['division'])
			//->where('bank', '=', $bank)
			->where('current_state', '=', $working_state)
			->get();
			$data['total'] = DB::table('tblarears_payment')
			->where('year', '=', $year)
			->where('month', '=', $month)
			->where('current_state', '=', $working_state)
			//->where('bankGroup', '=', $bankgroup)
			->where('division', '=', $request['division'])
			//->where('bank', '=', $bank)
			->sum($val);
		    /*}
		    else
		    {
		    	$data['details'] = DB::table('tblarears_payment')
			->where('year', '=', $year)
			->where('month', '=', $month)
			//->where('bankGroup', '=', $bankgroup)
			//->where('division', '=', $this->division)
			->where('bank', '=', $bank)
			->where('current_state', '=', $working_state)
			->get();
			$data['total'] = DB::table('tblarears_payment')
			->where('year', '=', $year)
			->where('month', '=', $month)
			//->where('bankGroup', '=', $bankgroup)
			->where('division', '=', $this->division)
			->where('bank', '=', $bank)
			->sum($val);
		    }*/

			//dd($data['details']);
		}
		else
		{
           if($bank == '')
			{
	
				$data['details'] = DB::table('tblarears_payment')
			->where('year', '=', $year)
			->where('month', '=', $month)
			//->where('bankGroup', '=', $bankgroup)
			->where('division', '=', $request['division'])
			//->where('bank', '=', $bank)
			->get();


			$data['total'] = DB::table('tblarears_payment')
			->where('year', '=', $year)
			->where('month', '=', $month)
			//->where('bankGroup', '=', $bankgroup)
			->where('division', '=', $request['division'])
			//->where('bank', '=', $bank)
			//->selectRaw('sum(".$val.") as total')
			->sum($val);
			   //dd($data['details']);
		   }
		   else
		   {
		   	$data['details'] = DB::table('tblarears_payment')
			->where('year', '=', $year)
			->where('month', '=', $month)
			//->where('bankGroup', '=', $bankgroup)
			->where('division', '=', $request['division'])
			->where('bank', '=', $bank)
			->get();


			$data['total'] = DB::table('tblarears_payment')
			->where('year', '=', $year)
			->where('month', '=', $month)
			//->where('bankGroup', '=', $bankgroup)
			->where('division', '=', $request['division'])
			->where('bank', '=', $bank)
			//->selectRaw('sum(".$val.") as total')
			->sum($val);
		   }
      }
		
			$data['getValue'] =  $val;
			//$data['bank'] =  $bank;
			$data['year'] =  $year;
			$data['month'] =  $month;
			$data['division'] =  $request['division'];
			//$data['bankgroup'] =  $bankgroup;
			//$data['totalSum'] =  $total;
			//$data['details']  = $data['payment'];
			$data['getvalue'] =  $val;
			$working_state = DB::table('tblstates')->where('StateID', $working_state)->value('State');
				if($working_state == 'ABUJA')
		{
		    $data['payeAddress'] =  'ABUJA INTERNAL REVENUE, '.$working_state;
		}
		else
		{
		
		    $data['payeAddress'] = $working_state. ' STATE INTERNAL REVENUE, '.$working_state;
		}
		
			return view('arearsT209.report', $data);
		

	}

}