<?php

namespace App\Http\Controllers\payroll;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class VoteManagement extends functionFundsController
{
	public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->middleware('auth');
        $this->username = Session::get('userName');
            return $next($request);
        });
    }//
 
   public function LendingBorrow(Request $request)
   {  


   	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	  $data['economicsource'] = trim($request['economicsource']);
	  $data['economicdest'] = trim($request['economicdest']);
	  $data['amount'] = trim($request['amount']);
	  $data['remarks'] = trim($request['remarks']);
	   //if($this->AccessNotGranted("allocation/totalmonthly")){return redirect('/')->with('message','Sorry! You do not have permission to access this page!!');}
	   //$data['period']	=$request->input('period');//$request['period'];
	   //if($data['period']=='') {$data['period']=session('period');}
	   $data['economicGroup'] = trim($request['economicGroup']);
	   if($data['economicGroup']=='') {$data['economicGroup']=session('economicGroup');}
	   $data['amount']	=$request['amount'];
	  if ( isset( $_POST['update'] ) ) {
		$this->validate($request, [
		'economicsource'      	    => 'required'
		,'economicdest'      => 'required'
		,'remarks'      => 'required'
		,'amount'      	        => 'required|numeric|between:0,9999999999999999.99'
		]);
			$period=$this->ActivePeriod();
			$ecoid=$data['economicdest'];
			$thisyearbudget= DB::Select("SELECT IFNULL(sum(`allocationValue`),0) as allocationValue FROM `tblbudget` WHERE `Period`='$period' and `economicCodeID`='$ecoid' and `AllocationStatus`=1")[0]->allocationValue;
// 			if(floor($this->AvailableBal($data['economicsource']))+floor($data['amount'])>floor($thisyearbudget)){$data['warning'] = "Record Not Updated! The target vote will exceed the year budget if allowed to borrow this amount";
// 			    $data['EconomicCode'] = $this->EconomicCode2('5',$data['economicGroup']);
//         	    $data['EconomicGroup'] = $this->BudgetType();
//           	    return view('allocation.lendingborrow', $data); 
// 			}
			if(floor($this->AvailableBal($data['economicsource'])) < floor($data['amount']))
	  		{$data['warning'] = "Insufficient Vote Balance!!! The Source vote does not have enough balance to fund the Target vote";
	  		    $data['EconomicCode'] = $this->EconomicCode2('5',$data['economicGroup']);
        	    $data['EconomicGroup'] = $this->BudgetType();
           	    return view('allocation.lendingborrow', $data); 
	  		    
	  		}else{
			
				DB::table('tbllendborrow')
                                ->insert([
                                    'eco_source'            => $data['economicsource'],
                                    'eco_destination'       => $data['economicdest'],
                                    'amount'                => $data['amount'],
                                    'remarks'                => $data['remarks'],
                                    'updated_by'            => Auth::user()->id, 
                                    'period'                => $this->ActivePeriod(), 
                                ]);
			
			
			$data['success'] = "successfully updated";
			Session::put('economicGroup',  $data['economicGroup']);
			
			return redirect()->back()->with('message', $data['success']);
	  		    
	  		}
		
		 }
	   
	  
	  
	   $data['EconomicCode'] = $this->EconomicCode2('5',$data['economicGroup']);
		$data['EconomicGroup'] = $this->BudgetType();
   	return view('allocation.lendingborrow', $data);
   }
   public function LendingBorrowReport(Request $request)
   {  


   	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	  $data['economicsource'] = trim($request['economicsource']);
	  $data['economicdest'] = trim($request['economicdest']);
	  $data['amount'] = trim($request['amount']);
	   //if($this->AccessNotGranted("allocation/totalmonthly")){return redirect('/')->with('message','Sorry! You do not have permission to access this page!!');}
	   //$data['period']	=$request->input('period');//$request['period'];
	   //if($data['period']=='') {$data['period']=session('period');}
	   $data['economicGroup'] = trim($request['economicGroup']);
	   if($data['economicGroup']=='') {$data['economicGroup']=session('economicGroup');}
	  $data['LendBorrowReport'] = $this->LendBorrowReport($data['economicsource']); 
	  //dd($data['LendBorrowReport']);
	 $data['EconomicCode'] = $this->EconomicCode2('5',$data['economicGroup']);
	$data['EconomicGroup'] = $this->BudgetType();
   	return view('allocation.lendingborrow_report', $data);
   }
   
   
    public function UnsettledLendingBorrowReport(Request $request)
   {  


   	  $data['error'] = "";
	  $data['warning'] = "";
	  $data['success'] = "";
	  //$UnsettleFund=$this->UnsettleFund("1");
	  $data['status'] = trim($request['status']);
	  $data['creditid'] = trim($request['creditid']);
	  $data['debitid'] = trim($request['debitid']);
	  $data['amount'] = trim($request['amount']);
	  $data['economicGroup'] = trim($request['economicGroup']);
	  if($data['economicGroup']=='') {$data['economicGroup']=session('economicGroup');}
	 if ( isset( $_POST['save'] ) ) {
		$this->validate($request, [
		'debitid'      	    => 'required'
		,'creditid'      => 'required'
		//,'remarks'      => 'required'
		,'amount'      	        => 'required|numeric|between:0,9999999999999999.99'
		]);
			$period=$this->ActivePeriod();
			$ecoid=$data['debitid'];
			$thisyearbudget= DB::Select("SELECT IFNULL(sum(`allocationValue`),0) as allocationValue FROM `tblbudget` WHERE `Period`='$period' and `economicCodeID`='$ecoid' and `AllocationStatus`=1")[0]->allocationValue;
			if(floor($this->AvailableBal($data['debitid'])) < floor($data['amount']))
	  		{$data['warning'] = "Insufficient Vote Balance!!! The Source vote does not have enough balance to fund the Target vote";
           	return redirect()->back()->with('message', $data['warning']);
	  		    
	  		}else{
	  		    //dd('Ready to go');
				DB::table('tbllendborrow')
                                ->insert([
                                    'eco_source'            => $data['debitid'],
                                    'eco_destination'       => $data['creditid'],
                                    'amount'                => $data['amount'],
                                    'remarks'                => "Refunds",
                                    'updated_by'            => Auth::user()->id, 
                                    'period'                => $this->ActivePeriod(), 
                                ]);
			$data['success'] = "successfully updated";
			Session::put('economicGroup',  $data['economicGroup']);
			
			return redirect()->back()->with('message', $data['success']);
	  		    
	  		}
		
		 }
	 
	$data['EconomicGroup'] = $this->BudgetType();
	$data['UnsettleFund'] =$this->UnsettleFund($data['economicGroup']);
//dd($data['UnsettleFund']);
   	return view('allocation.unsettledlendingborrow_report', $data);
   }
  

}