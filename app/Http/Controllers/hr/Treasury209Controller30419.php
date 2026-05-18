<?php

namespace App\Http\Controllers\hr;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;

class Treasury209Controller extends ParentController
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
            //->union($cvSetup)
            ->get();
   	    return view('treasury209.treasury', $data);
   }
   
    public function load()
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
            //->union($cvSetup)
            ->get();
   	    return view('treasury209.treasury2', $data);
   }
   
   public function view(Request $request)
    { 
		$this->validate($request, [
			'reporttype'    => 'required',
			'month'         => 'required|alpha',
			'year'          => 'required|numeric',
			//'bank'          => 'required|numeric',
			//'bankgroup'     => 'required|numeric',
			//'workingstate'  => 'required_if:reportType,tax|string',
		]);
		$type            = trim($request['reporttype']);
		$month          = trim($request['month']);
		$year           = trim($request['year']);
		$bank           = trim($request['bank']);
		
		$data['year']= $year;
		$data['month']= $month;
		$bankgroup      = trim($request['bankgroup']);
		$working_state   = trim($request['workingstate']);
		$data['selectedMonth'] = $month;
		//dd($rtype );
		if($type == 'gross')
		{
		 $rtype = 'NetPay';
		}
		else
		{
		 $rtype = trim($request['reporttype']);
		}
		$data['banklist'] = $data['bank']  = DB::table('tblbanklist')->where('bankID','=',$bank)->first();
		$data['rtype'] = $type;
		
		
		// coop
		if($type == 'coop' )
		{
		
		$data['reportTitle'] = "Cooperative";
		$f = DB::table('tblpayment_consolidated')->select('staffid')->get();
		$ar = array();
		
		if($bank =='')
		{
		$data['payment'] = DB::table('tblotherEarningDeduction')
		->join('tblpayment_consolidated','tblpayment_consolidated.staffid','=','tblotherEarningDeduction.staffid')
		->select('tblpayment_consolidated.name as fullname',"tblotherEarningDeduction.amount as amt",'tblpayment_consolidated.grade','tblpayment_consolidated.step','tblpayment_consolidated.Bs','tblpayment_consolidated.AEarn','tblpayment_consolidated.staffid')
		
		->where('tblotherEarningDeduction.CVID','=',15)
		//->where('tblotherEarningDeduction.CVID','=',16)
		
		->where('tblpayment_consolidated.month','=',$month)
		->where('tblpayment_consolidated.year','=',$year)
		//->where('tblpayment_consolidated.bank','=',$bank)
		->where('tblpayment_consolidated.rank','!=',2)
		->where('tblotherEarningDeduction.month','=',$month)
		->where('tblotherEarningDeduction.year','=',$year)	
		/*
		->where(function ($query) {
                 $query->where('tblpayment_consolidated.year', '=', $year)
                       ->where('tblpayment_consolidated.month', '=', $month);
            })
		*/
		/*->where(function ($query) {
                 $query->where('tblotherEarningDeduction.CVID', '=', 15)
                      ->orWhere('tblotherEarningDeduction.CVID', '=', 16);
            })*/
		->orderBy('tblpayment_consolidated.rank','DESC')
                ->orderBy('tblpayment_consolidated.grade','DESC')
                ->orderBy('tblpayment_consolidated.step','DESC')
                ->get();
                
                foreach($data['payment'] as $u)
                {
                
                                  
         
                }
                //dd(count($cooploan));
                 
                
		}
		else
		{
		$data['payment'] = DB::table('tblotherEarningDeduction')
		->join('tblpayment_consolidated','tblpayment_consolidated.staffid','=','tblotherEarningDeduction.staffid')
		->select('tblpayment_consolidated.name as fullname',"tblotherEarningDeduction.amount as amt",'tblpayment_consolidated.grade','tblpayment_consolidated.step','tblpayment_consolidated.Bs','tblpayment_consolidated.AEarn','tblpayment_consolidated.staffid')
		
		->where('tblotherEarningDeduction.CVID','=',15)
		//->where('tblotherEarningDeduction.CVID','=',16)
		
		->where('tblpayment_consolidated.month','=',$month)
		->where('tblpayment_consolidated.year','=',$year)
		->where('tblpayment_consolidated.bank','=',$bank)
		->where('tblpayment_consolidated.rank','!=',2)
		->where('tblotherEarningDeduction.month','=',$month)
		->where('tblotherEarningDeduction.year','=',$year)	
		/*
		->where(function ($query) {
                 $query->where('tblpayment_consolidated.year', '=', $year)
                       ->where('tblpayment_consolidated.month', '=', $month);
            })
		*/
		/*->where(function ($query) {
                 $query->where('tblotherEarningDeduction.CVID', '=', 15)
                      ->orWhere('tblotherEarningDeduction.CVID', '=', 16);
            })*/
		->orderBy('tblpayment_consolidated.rank','DESC')
                ->orderBy('tblpayment_consolidated.grade','DESC')
                ->orderBy('tblpayment_consolidated.step','DESC')
                ->get();
                }
                
               
                $noRecord = 0;
		$total = 0.00;
		$ar = 0.00;
		$jusuTotal = 0.00;
		$bs = 0.00;
		$sumTen = 0.00;
		$ten = 0.00;
		foreach ($data['payment'] as $row) 
		{
        	$total += ($row -> amt ); 
        	$jusuTotal = 0;
			$data['record']   =  $row;
		}
		$data['getValue'] =  $rtype;
		$data['totalSum'] =  $total;
		$data['jusuSum']  = $jusuTotal;
		$data['details']  = $data['payment'];
		//if($data['payment'] && $data['totalSum'] <> 0)
			return view('treasury209.testReport', $data);
		//
		}
		
		///end coop
		
		if($type == 'PEN')
		{
		if($bank == '')
		{
		 $data['payment'] = DB::table('tblpayment_consolidated')
		//->join('tblpayment_consolidated','tblpayment_consolidated.fileNo','=','tblotherEarningDeduction.fileNo')
		//->join('tblper','tblper.ID','=','tblpayment_consolidated.staffid')
		//->join('basicsalaryconsolidated','basicsalaryconsolidated.grade','=','tblper.grade')
		//->join('basicsalaryconsolidated','basicsalaryconsolidated.step','=','tblper.step')
		//>join('basicsalaryconsolidated','basicsalaryconsolidated.employee_type','=','tblper.type')
		->select('tblpayment_consolidated.name as fullname',"tblpayment_consolidated.PEN as amt",'tblpayment_consolidated.grade','tblpayment_consolidated.step','Bs','AEarn')
		->where('tblpayment_consolidated.rank','!=',2)
		->where('tblpayment_consolidated.PEN','!=',0)
		//->where('tblpayment_consolidated.bank','=',$bank)
		->where('tblpayment_consolidated.month','=',$month)->where('tblpayment_consolidated.year','=',$year)
->orderBy('tblpayment_consolidated.rank','DESC')
->orderBy('tblpayment_consolidated.grade','DESC')
                ->orderBy('tblpayment_consolidated.step','DESC')
->get();
}
else
{
$data['payment'] = DB::table('tblpayment_consolidated')
		//->join('tblpayment_consolidated','tblpayment_consolidated.fileNo','=','tblotherEarningDeduction.fileNo')
		//->join('tblper','tblper.ID','=','tblpayment_consolidated.staffid')
		//->join('basicsalaryconsolidated','basicsalaryconsolidated.grade','=','tblper.grade')
		//->join('basicsalaryconsolidated','basicsalaryconsolidated.step','=','tblper.step')
		//>join('basicsalaryconsolidated','basicsalaryconsolidated.employee_type','=','tblper.type')
		->select('tblpayment_consolidated.name as fullname',"tblpayment_consolidated.PEN as amt",'tblpayment_consolidated.grade','tblpayment_consolidated.step','Bs','AEarn')
		->where('tblpayment_consolidated.rank','!=',2)
		->where('tblpayment_consolidated.PEN','!=',0)
		->where('tblpayment_consolidated.bank','=',$bank)
		->where('tblpayment_consolidated.month','=',$month)->where('tblpayment_consolidated.year','=',$year)
->orderBy('tblpayment_consolidated.rank','DESC')
->orderBy('tblpayment_consolidated.grade','DESC')
                ->orderBy('tblpayment_consolidated.step','DESC')
->get();
}
//dd($data['payment']);

$noRecord = 0;
		$total = 0.00;
		$ar = 0.00;
		$jusuTotal = 0.00;
		$bs = 0.00;
		$sumTen = 0.00;
		$ten = 0.00;
                $data['payeAddress'] = $working_state. ' STATE INTERNAL REVENUE, '.$working_state;
               $data['reportTitle'] = DB::table('tbladmincode')->where('determinant','=',$type)->select('addressName as desc')->first();
		foreach ($data['payment'] as $row) 
		{
        	$total += ($row -> amt );
        	$ar += ($row->AEarn); 
        	$bs += ($row->Bs);
        	//$jusuTotal += $row->PEC;
        	$ten = (10/100) * ($row->Bs + $row->AEarn);
        	$sumTen += $ten;
		$data['record']   =  $row;
		}//end foreach
		
		$data['getValue'] =  $rtype;
		$data['totalSum'] =  $total;
		$data['jusuSum']  = $jusuTotal;
		$data['allPenGross'] = $bs + $ar;
		$data['tenpersum'] = $sumTen;
		$data['totalPen']  = $sumTen + $total;
		$data['details']  = $data['payment'];
		if($data['payment'] && $data['totalSum'] <> 0)
			return view('treasury209.testReport', $data);
		}
		
		
		//dd( $rtype);
		
		if(is_numeric($rtype ))
		{
		if($bank == '')
		{
		$data['reportTitle'] = DB::table('tblcvSetup')->where('ID','=',$type)->select('description as desc')->first();
		$f = DB::table('tblpayment_consolidated')->select('staffid')->get();
		$ar = array();
		foreach($f as $a)
		{
		$ar = array($a->staffid);
		}
		
		$data['payment'] = DB::table('tblotherEarningDeduction')
		->join('tblpayment_consolidated','tblpayment_consolidated.staffid','=','tblotherEarningDeduction.staffid')
		->select('tblpayment_consolidated.name as fullname',"tblotherEarningDeduction.amount as amt",'tblpayment_consolidated.grade','tblpayment_consolidated.step','tblpayment_consolidated.Bs','tblpayment_consolidated.AEarn')
		->where('tblotherEarningDeduction.CVID','=',$rtype)->where('tblotherEarningDeduction.month','=',$month)
		->where('tblpayment_consolidated.rank','!=',2)
		->where('tblotherEarningDeduction.year','=',$year)
		->where('tblpayment_consolidated.month','=',$month)
		->where('tblpayment_consolidated.year','=',$year)
		->orderBy('tblpayment_consolidated.rank','DESC')
                ->orderBy('tblpayment_consolidated.grade','DESC')
                ->orderBy('tblpayment_consolidated.step','DESC')
                ->get();
                
                //dd($data['payment']);
                $noRecord = 0;
		$total = 0.00;
		$ar = 0.00;
		$jusuTotal = 0.00;
		$bs = 0.00;
		$sumTen = 0.00;
		$ten = 0.00;
		foreach ($data['payment'] as $row) 
		{
        	$total += ($row -> amt ); 
        	$jusuTotal = 0;
			$data['record']   =  $row;
		}
		$data['getValue'] =  $rtype;
		$data['totalSum'] =  $total;
		$data['jusuSum']  = $jusuTotal;
		$data['details']  = $data['payment'];
		//if($data['payment'] && $data['totalSum'] <> 0)
			return view('treasury209.testReport', $data);
		//
		}
		else
		{
		  $data['reportTitle'] = DB::table('tblcvSetup')->where('ID','=',$type)->select('description as desc')->first();
		
		$data['payment'] = DB::table('tblotherEarningDeduction')
		->join('tblpayment_consolidated','tblpayment_consolidated.staffid','=','tblotherEarningDeduction.staffid')
		->select('tblpayment_consolidated.name as fullname',"tblotherEarningDeduction.amount as amt",'tblpayment_consolidated.grade','tblpayment_consolidated.step','tblpayment_consolidated.Bs','tblpayment_consolidated.AEarn')
		->where('tblotherEarningDeduction.CVID','=',$rtype)->where('tblotherEarningDeduction.month','=',$month)
		->where('tblpayment_consolidated.rank','!=',2)
		->where('tblotherEarningDeduction.year','=',$year)
		->where('tblpayment_consolidated.month','=',$month)
		->where('tblpayment_consolidated.year','=',$year)
		->where('tblpayment_consolidated.bank','=',$bank)
                ->orderBy('tblpayment_consolidated.rank','DESC')
                ->orderBy('tblpayment_consolidated.grade','DESC')
                ->orderBy('tblpayment_consolidated.step','DESC')
                ->get();
                
                $noRecord = 0;
		$total = 0.00;
		$ar = 0.00;
		$jusuTotal = 0.00;
		$bs = 0.00;
		$sumTen = 0.00;
		$ten = 0.00;
                foreach ($data['payment'] as $row) 
		{
        	$total += ($row -> amt ); 
        	$jusuTotal = 0;
			$data['record']   =  $row;
		}
		$data['getValue'] =  $rtype;
		$data['totalSum'] =  $total;
		$data['jusuSum']  = $jusuTotal;
		$data['details']  = $data['payment'];
		//if($data['payment'] && $data['totalSum'] <> 0)
			return view('treasury209.testReport', $data);
		}
		
		}
		else
		{
		if($bank == '')
		{
		$data['reportTitle'] = DB::table('tbladmincode')->where('determinant','=',$type)->select('addressName as desc')->first();
		$data['payment'] = DB::table('tblpayment_consolidated')->select('name as fullname',"$rtype as amt",'PEC','tblpayment_consolidated.grade','tblpayment_consolidated.step','tblpayment_consolidated.Bs','tblpayment_consolidated.AEarn')->where('month','=',$month)->where('tblpayment_consolidated.rank','!=',2)->where('year','=',$year)->orderBy('tblpayment_consolidated.rank','DESC')
->orderBy('tblpayment_consolidated.grade','DESC')

                ->orderBy('tblpayment_consolidated.step','DESC')
->get();
		}
		else
		{
		$data['reportTitle'] = DB::table('tbladmincode')->where('determinant','=',$type)->select('addressName as desc')->first();
		$data['payment'] = DB::table('tblpayment_consolidated')->select('name as fullname',"$rtype as amt",'PEC','tblpayment_consolidated.grade','tblpayment_consolidated.step','tblpayment_consolidated.Bs','tblpayment_consolidated.AEarn')->where('tblpayment_consolidated.rank','!=',2)->where('month','=',$month)->where('year','=',$year)->where('bank','=',$bank)->orderBy('tblpayment_consolidated.rank','DESC')
->orderBy('tblpayment_consolidated.grade','DESC')
                ->orderBy('tblpayment_consolidated.step','DESC')
->get();
		}
		
		}
		
		//dd($data['payment']);

/*$data['details'] = DB::table('tblpayment')
->join('tbladmincode','tbladmincode.determinant', '=', $val)
->where('tblpayment.year', '=', $year)
->where('tblpayment.month', '=', $month)
->where('tblpayment.bankGroup', '=', $bankgroup)
->where('tblpayment.division', '=', $this->division)
->where('tblpayment.bank', '=', $bank)
get();*/

		if( is_null($data['payment']))
			return back()->with('msg', 'Record not found!');	
		$noRecord = 0;
		$total = 0.00;
		$jusuTotal = 0.00;
                $data['payeAddress'] = $working_state. ' STATE INTERNAL REVENUE, '.$working_state;
                if(is_numeric($rtype ))
		{
		foreach ($data['payment'] as $row) 
		{
        	$total += ($row -> amt ); 
        	$jusuTotal = 0;
			$data['record']   =  $row;
		}
		}
		else
		{
		foreach ($data['payment'] as $row) 
		{
        	$total += ($row -> amt ); 
        	$jusuTotal += $row->PEC;
			$data['record']   =  $row;
		}//end foreach
		}
		$data['getValue'] =  $rtype;
		$data['totalSum'] =  $total;
		$data['jusuSum']  = $jusuTotal;
		$data['details']  = $data['payment'];
		if($data['payment'] && $data['totalSum'] <> 0)
			return view('treasury209.testReport', $data);
		else
			return back()->with('msg', 'Record not found/Empty Record!');	
	}
	
	

}