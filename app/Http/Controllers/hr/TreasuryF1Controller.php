<?php
//
namespace App\Http\Controllers\hr;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;

class TreasuryF1Controller extends ParentController
{
	public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
    	$this->division   = $request->session()->get('division');
		$this->divisionID = $request->session()->get('divisionID');
                return $next($request);
        });
    }
    public function loadView()
   {
	   $data['bank']  = DB::table('tblbanklist')
			 ->select('bank', 'bankID')
			 ->orderBy('bank', 'Asc')
			 ->get();
		/*$data['reporttype'] = DB::table('tbladmincode')
			 ->select('codeID', 'addressName', 'determinant')
	    	 ->orderBy('addressName', 'Asc')->get();*/
	    	 
		$data['workingstate'] = DB::table('tblstates')
		 	 ->select('StateID', 'State')
			 ->distinct()
	    	 ->orderBy('State', 'Asc')
			 ->get();
			 
	     
	     $data['cvSetup'] = DB::table('tblcvSetup')->select('ID', 'description')->get();

            $data['reporttype'] = DB::table('tbladmincode')  
             ->select('addressName', 'determinant') 
            ->get();
   	    return view('treasuryF1.treasury', $data);
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
			//'workingState'  => 'required_if:reportType,tax|string',
		]);
		$type            = trim($request['reportType']);
		$month          = trim($request['month']);
		$year           = trim($request['year']);
		$bank           = trim($request['bank']);
		
		
		$bankgroup      = trim($request['bankgroup']);
		$working_state   = trim($request['workingstate']);
		$data['selectedMonth'] = $month;
		
		DB::enableQueryLog();
		if($type == 'gross')
		{
		 $rtype = 'NetPay';
		}
		else
		{
		 $rtype = trim($request['reportType']);
		}
		$data['banklist'] = $data['bank']  = DB::table('tblbanklist')->where('bankID','=',$bank)->first();
		$data['rtype'] = $type;
		
		if(is_numeric($rtype ))
		{
		 
		if($bank == '')
		{
		$data['reportTitle'] = DB::table('tblcvSetup')->where('ID','=',$type)->select('description as desc')->first();
		
		$data['payment'] = DB::table('tblotherEarningDeduction')
		->join('tblpayment_consolidated','tblpayment_consolidated.fileNo','=','tblotherEarningDeduction.fileNo')
		->select('tblpayment_consolidated.name as fullname',"tblotherEarningDeduction.amount as amt")
		->where('tblotherEarningDeduction.CVID','=',$rtype)->where('tblotherEarningDeduction.month','=',$month)->where('tblotherEarningDeduction.year','=',$year)->get();
		}
		else
		{
		 $data['reportTitle'] = DB::table('tbladmincode')->where('determinant','=',$request['reportType'])->select('address as desc')->first();
		 
		//$data['reportTitle'] = DB::table('tblcvSetup')->where('ID','=',$type)->select('description as desc')->first();
		$data['payment'] = DB::table('tblotherEarningDeduction')
		->join('tblpayment_consolidated','tblpayment_consolidated.fileNo','=','tblotherEarningDeduction.fileNo')
		->select('tblpayment_consolidated.name as fullname',"tblotherEarningDeduction.amount as amt")
		->where('tblotherEarningDeduction.CVID','=',$rtype)->where('tblotherEarningDeduction.month','=',$month)->where('tblotherEarningDeduction.year','=',$year)->where('tblpayment_consolidated.bank','=',$bank)->get();
		}
		
		}
		else
		{
		if($bank == '')
		{
		$data['reportTitle'] = DB::table('tbladmincode')->where('determinant','=',$type)->select('addressName as desc')->first();
		$data['payment'] = DB::table('tblpayment_consolidated')->select('name as fullname',"$rtype as amt",'PEC')->where('month','=',$month)->where('year','=',$year)->get();
		}
		else
		{
		$data['reportTitle'] = DB::table('tbladmincode')->where('determinant','=',$type)->select('addressName as desc')->first();
		$data['payment'] = DB::table('tblpayment_consolidated')->select('name as fullname',"$rtype as amt",'PEC')->where('month','=',$month)->where('year','=',$year)->where('bank','=',$bank)->get();
		}
		
		}
		$data['selectedYear'] = $year;
		$data['month'] = $month;
		
		if( is_null($data['payment']))
			return back()->with('msg', 'Record not found!');	
		$noRecord = 0;
		$amount     = 0.0;
		$total = 0.00;
		$jusuTotal = 0.00;
		$totalUser  = 0;
                $data['payeAddress'] = $working_state. ' STATE INTERNAL REVENUE, '.$working_state;
		foreach ($data['payment'] as $row) 
		{
        	$amount += ($row -> amt ); 
        	$jusuTotal += $row->PEC;
        	if( ($amount <> 0 ) )
		 {
			$totalUser   += 1;
			$total       += ($row -> amt );
		 }
		$data['record']   =  $row;
		}//end foreach
		if($totalUser > 1)
		$userStatus = "and $totalUser others";
		else
		$userStatus = "";
		$data['getValue'] =  $rtype;
		$data['totalSum'] =  $total;
		$data['jusuSum']  = $jusuTotal;
		$data['details']  = $data['payment'];
		$data['getStatus']  =  $userStatus;
		if($data['payment'] && $data['totalSum'] <> 0)
			//return view('treasuryF1.treasuryF1Report', $data);
			return view('treasuryF1.tf1voucher', $data);
		else
			return back()->with('msg', 'Record not found/Empty Record!');	

	}

}