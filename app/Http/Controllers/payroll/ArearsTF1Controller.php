<?php
//
namespace App\Http\Controllers\payroll;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;

class ArearsTF1Controller extends ParentController
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
			 $data['courts'] =  DB::table('tbl_court')->get();
		
		$data['CourtInfo'] = $this->CourtInfo();
		if ($data['CourtInfo']->courtstatus == 0) {
			$request['court'] = $data['CourtInfo']->courtid;
		}
		if ($data['CourtInfo']->divisionstatus == 0) {
			$request['division'] = $data['CourtInfo']->divisionid;
		}
		 
		$data['courtDivisions']  = DB::table('tbldivision')->get();

		$data['curDivision'] = $this->curDivision(Auth::user()->id);
   	    return view('arearsTF1.loadView', $data);
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
		$this->validate($request,  
		[
			'reportType'    => 'required|string',
			'month'         => 'required|alpha',
			'year'          => 'required|numeric',
			//'bank'          => 'required|string',
			//'bankGroup'     => 'required|numeric',
			// 'workingState'  => 'required_if:reportType,tax|string',
			'workingState'  => 'required_if:reportType,tax'
		]);
		$val            = trim($request['reportType']);
		$month          = trim($request['month']);
		$year           = trim($request['year']);
		$bank           = trim($request['bank']);
	//	$bankgroup      = trim($request['bankGroup']);
		$working_state  = trim($request['workingState']);
		
		$data['bankName'] = $bank;

		$data['determinant'] = DB::table('tbladmincode')
		   ->where('determinant','=',$val)
           ->first();
           $data['year']  = $year;
           $data['month'] = $month;
           $data['type'] = $val;
		
		DB::enableQueryLog();
		if($val == 'tax')
		{
			$data['payment'] = DB::table('tblarears_payment')
		   //->join('tbladmincode','tbladmincode.determinant','=',$val)
		   //->where("tblarears_payment.$val",'<>',0)
		   ->where('tblarears_payment.month','=',$month)
           ->where('tblarears_payment.year','=',$year)
           //->where('tblarears_payment.bank','=',$bank)
           ->where('tblarears_payment.division','=',$request['division'])
           ->where('tblarears_payment.current_state','=',$working_state)
           
           //->selectRaw('*,SUM(cumEmolu) as grossEmolument,SUM(totalDeduct) as totalDeduction,SUM(netpay) as netEmolument')
           ->get();
		   $working_state = DB::table('tblstates')->where('StateID', $working_state)->value('State');
         //dd($data['payment']);
           if($working_state == 'ABUJA')
           {
				$data['payeAddress'] = 'FEDERAL INLAND REVENUE SERVICE, ABUJA';
           }
			else
			{
				$data['payeAddress'] = $working_state. ' STATE INTERNAL REVENUE, '.$working_state;
			}

			//$data['payment'] = DB::select('select a.fileNo, grosspay, a.name, bank, bankGroup, division, month, year, b.*, bicycleAdv, ctlsLab, ctlsFed, fedHousing, motorAdv, nhf, pension, tax, nicnCoop, pension, pa_deduct,vrsa, phoneCharges, unionDues, ugv, surcharge, totalEmolu from tblarears_payment a, tbladmincode b where b.determinant = "'.$val.'" and a.'.$val.' <> 0 and a.month = ? and a.year = ? and a.division = ? and bank = ? and bankGroup = ? and current_state = ?',[$month, $year, $this->division, $bank, $bankgroup, $working_state]);
			if($working_state == 'ABUJA')
				$data['payeAddress'] = 'FEDERAL INLAND REVENUE SERVICE, ABUJA';
			else
				$data['payeAddress'] = $working_state. ' STATE INTERNAL REVENUE, '.$working_state;
							
		}

		else
		{
			if($bank == "")
			{
			    $data['payment'] = DB::table('tblarears_payment')
		  // ->join('tbladmincode','tbladmincode.determinant','=',$val)
		   //->where("tblarears_payment.$val",'<>',0)
		   ->where('tblarears_payment.month','=',$month)
           ->where('tblarears_payment.year','=',$year)
           ->where('tblarears_payment.division','=',$request['division'])
           //->where('tblarears_payment.bank','=',$bank)
           //->where('tblarears_payment.bankGroup','=',$bankgroup)
           //->selectRaw('*,SUM(cumEmolu) as grossEmolument,SUM(totalDeduct) as totalDeduction,SUM(netpay) as netEmolument')
           ->get();
			}
			else
			{
            	$data['payment'] = DB::table('tblarears_payment')
		  // ->join('tbladmincode','tbladmincode.determinant','=',$val)
		   //->where("tblarears_payment.$val",'<>',0)
		   ->where('tblarears_payment.month','=',$month)
           ->where('tblarears_payment.year','=',$year)
           ->where('tblarears_payment.division','=',$request['division'])
           ->where('tblarears_payment.bank','=',$bank)
           //->where('tblarears_payment.bankGroup','=',$bankgroup)
           //->selectRaw('*,SUM(cumEmolu) as grossEmolument,SUM(totalDeduct) as totalDeduction,SUM(netpay) as netEmolument')
           ->get();
			}
           
           
       }
       //dd($data['payment']);
		if( count($data['payment']) ==0)
		{
			return back()->with('msg', 'Record not found! here');
		}
			//get sum total
			$noRecord   = 0;
			$amount     = 0.0;
			$totalUser  = 0;
			$total      = 0.0;
			//$getValue   = session::get("getval");
			foreach ($data['payment'] as $row) 
			{
                 $amount = ($row -> $val + $row->callDuty); 
                 
                 if( ($amount <> 0 ) )
				 {
					$totalUser   += 1;
					$total       += ($row -> $val + $row->callDuty);
				 }
				 $data['record']   =  $row;
			}//end foreach
			$totalUsers = $totalUser - 1;
			if($totalUser > 1)
				$userStatus = "and $totalUsers others";
			else
				$userStatus = "";
			$data['totalSum']   =  $total;
			$data['getStatus']  =  $userStatus;
			

			//if($data['payment'] && $data['totalSum'] <> 0)
				return view('arearsTF1.report', $data);
			//else
				//return back()->with('msg', 'Record not found!');	
 
	}

}