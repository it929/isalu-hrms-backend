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
			 
			 $data['currentstate'] = DB::table('tblcurrent_state')->get();
			 
	     
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
		$currentstate      = trim($request['currentState']);
		//dd($rtype );
		
		$reportess=$this->Tr2019('','',$year,$month,$type,$bank,$currentstate);

		$data['payment']=$reportess;
		$data['Tr2019Head']=$this->Tr2019Head($type);
		$data['selectedMonth'] = $request['month'];
		$data['selectedYear'] = $request['year'];
		$data['reportType'] = $type;
		return view('treasury209.testReport', $data);
		//dd($reportess);
			
	}
	
	Public function Tr2019($court,$division,$year,$month,$para,$bank,$residential=''){
	  $qresidential=1;
	  if($residential!=''){$qresidential="`current_state`='$residential'";}
	if($para=='' || $para=='Select'){return [];}
	$qbank=1;
	$vpara='';
	$qpara=1;
	if($bank!=''){$qbank="`bank`='$bank'";}
	switch ($para) {
	    case "coop":
	        $vpara=",(SELECT sum(`amount`) FROM `tblotherEarningDeduction` WHERE (`CVID`='15' or `CVID`='16')  and `tblotherEarningDeduction`.`year`='$year' and `tblotherEarningDeduction`.`month`='$month' and `tblotherEarningDeduction`.`staffid`=`tblpayment_consolidated`.`staffid`) as Vpara";
	        $qpara= "exists(SELECT null FROM `tblotherEarningDeduction` WHERE (`CVID`='15' or `CVID`='16')  and `tblotherEarningDeduction`.`year`='$year' and `tblotherEarningDeduction`.`month`='$month' and `tblotherEarningDeduction`.`staffid`=`tblpayment_consolidated`.`staffid`)";
	        break;
	        case "2":
	        $vpara=",(SELECT sum(`amount`) FROM `tblotherEarningDeduction` WHERE (`CVID`='$para')  and `tblotherEarningDeduction`.`year`='$year' and `tblotherEarningDeduction`.`month`='$month' and `tblotherEarningDeduction`.`staffid`=`tblpayment_consolidated`.`staffid`) as Vpara";
	        $qpara= "exists(SELECT null FROM `tblotherEarningDeduction` WHERE (`CVID`='$para')  and `tblotherEarningDeduction`.`year`='$year' and `tblotherEarningDeduction`.`month`='$month' and `tblotherEarningDeduction`.`staffid`=`tblpayment_consolidated`.`staffid`)";
	        break;
	        case "18":
	        $vpara=",(SELECT sum(`amount`) FROM `tblotherEarningDeduction` WHERE (`CVID`='$para')  and `tblotherEarningDeduction`.`year`='$year' and `tblotherEarningDeduction`.`month`='$month' and `tblotherEarningDeduction`.`staffid`=`tblpayment_consolidated`.`staffid`) as Vpara";
	        $qpara= "exists(SELECT null FROM `tblotherEarningDeduction` WHERE (`CVID`='$para')  and `tblotherEarningDeduction`.`year`='$year' and `tblotherEarningDeduction`.`month`='$month' and `tblotherEarningDeduction`.`staffid`=`tblpayment_consolidated`.`staffid`)";
	        break;
	        case "15":
	        $vpara=",(SELECT sum(`amount`) FROM `tblotherEarningDeduction` WHERE (`CVID`='$para')  and `tblotherEarningDeduction`.`year`='$year' and `tblotherEarningDeduction`.`month`='$month' and `tblotherEarningDeduction`.`staffid`=`tblpayment_consolidated`.`staffid`) as Vpara";
	        $qpara= "exists(SELECT null FROM `tblotherEarningDeduction` WHERE (`CVID`='$para')  and `tblotherEarningDeduction`.`year`='$year' and `tblotherEarningDeduction`.`month`='$month' and `tblotherEarningDeduction`.`staffid`=`tblpayment_consolidated`.`staffid`)";
	        break;
	        case "16":
	        $vpara=",(SELECT sum(`amount`) FROM `tblotherEarningDeduction` WHERE (`CVID`='$para')  and `tblotherEarningDeduction`.`year`='$year' and `tblotherEarningDeduction`.`month`='$month' and `tblotherEarningDeduction`.`staffid`=`tblpayment_consolidated`.`staffid`) as Vpara";
	        $qpara= "exists(SELECT null FROM `tblotherEarningDeduction` WHERE (`CVID`='$para')  and `tblotherEarningDeduction`.`year`='$year' and `tblotherEarningDeduction`.`month`='$month' and `tblotherEarningDeduction`.`staffid`=`tblpayment_consolidated`.`staffid`)";
	        break;
	        
	    default:
	       $vpara=" ,`$para` as Vpara";
	       
		} 
	
	$List= DB::Select("SELECT * $vpara FROM `tblpayment_consolidated` join tblper on tblpayment_consolidated.staffid = tblper.ID  WHERE `year`='$year' and `month`='$month' and $qbank and $qpara and tblpayment_consolidated.rank<>2 and $qresidential order by tblpayment_consolidated.rank DESC, tblpayment_consolidated.grade DESC, tblpayment_consolidated.step DESC");
	//dd($List);
	return $List;
	}
	Public function Tr2019Head($para){
	if($para=='' || $para=='Select'){return '';}
	
	
	switch ($para) {
	    case "coop":
	        return 'Cooperative';
	        break;
	        case "2":
	        return 'Housing Loan Refunds';
	        break;
	        case "18":
	        return 'Salary Advance';
	        break;
	        case "15":
	         return 'Cooperative Saving';
	        break;
	        case "16":
	        return 'Cooperative Loan Repayment';
	        break;
	    default:
	       return DB::table('tbladmincode')->where('determinant',$para)->first()->addressName ;
	       
		} 
	
	$List= DB::Select("SELECT * $vpara FROM `tblpayment_consolidated` WHERE `year`='$year' and `month`='$month' and $qbank and $qpara and rank<>2");
	return $List;
	}
	
	

}