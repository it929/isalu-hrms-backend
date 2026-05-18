<?php

namespace App\Http\Controllers\hr;

use Illuminate\Http\Request;
use App\Http\Requests;

use DB;
use Auth;
use Session;
use App\Event;
use File;

class SalaryApprovalProcessControllerTest extends ParentController
{
    
  public function viewData($month,$year,$court,$bankID,$field)
  {
       $payroll_detail = DB::table('tblpayment_consolidated_test')
          ->where('tblpayment_consolidated_test.month',     '=', $month)
          ->where('tblpayment_consolidated_test.year',      '=', $year)
          ->where('tblpayment_consolidated_test.courtID',  '=', $court)
          ->where('tblpayment_consolidated_test.bank',      '=',$bankID )
          ->where("tblpayment_consolidated_test.$field",      '=',1 )
          ->where('tblpayment_consolidated_test.rank','!=',2)
          ->orderBy('rank','DESC')
          ->orderBy('grade','DESC')
          ->orderBy('step','DESC')
          ->get();
          return $payroll_detail; 
  }
  
  public function viewData2($month,$year,$court,$field)
  {
       $payroll_detail = DB::table('tblpayment_consolidated_test')
          ->where('tblpayment_consolidated_test.month',     '=', $month)
          ->where('tblpayment_consolidated_test.year',      '=', $year)
          ->where('tblpayment_consolidated_test.courtID',  '=', $court)
          ->where("tblpayment_consolidated_test.$field",      '=',1 )
          ->where('tblpayment_consolidated_test.rank','!=',2)
          ->orderBy('rank','DESC')
          ->orderBy('grade','DESC')
          ->orderBy('step','DESC')
          ->get();
          return $payroll_detail; 
  }
  public function viewAnalylis($month,$year,$field)
  {
      $group = DB::table('tblpayment_consolidated_test')
    ->join('tblbanklist','tblbanklist.bankID','=','tblpayment_consolidated_test.bank')
    ->where('tblpayment_consolidated_test.month',     '=', $month)
    ->where('tblpayment_consolidated_test.year',      '=', $year)
    ->where("tblpayment_consolidated_test.$field",      '=',1 )
    ->where('tblpayment_consolidated_test.rank','!=',2)
    ->groupBy('tblpayment_consolidated_test.bank')
    ->select('*','tblbanklist.bank','tblpayment_consolidated_test.bank as bankid')
    ->get();
    return $group;
  }
  
  public function schedule($month,$year,$field)
  {
      $epayment_detail = DB::table('tblpayment_consolidated_test')
       ->join('tblbanklist','tblbanklist.bankID','=','tblpayment_consolidated_test.bank')
        ->leftJoin('tblbacklog','tblbacklog.staffid','=','tblpayment_consolidated_test.staffid','tblbacklog.month','=','tblpayment_consolidated_test.month','tblbacklog.year','=','tblpayment_consolidated_test.year')
        //->where('tblbacklog.month',     '=', $month)
        //->where('tblbacklog.year',      '=', $year)
        ->where('tblpayment_consolidated_test.month',     '=', $month)
        ->where('tblpayment_consolidated_test.year',      '=', $year)
        ->where('tblpayment_consolidated_test.rank','!=',2)
        ->where("tblpayment_consolidated_test.$field",'=',1)
        //->where('tblpayment_consolidated_test.divisionID',  '=', $division)
        //->where('tblpayment_consolidated_test.courtID',  '=', $court)
        ->orderBy("tblpayment_consolidated_test.bank",'DESC')
        ->orderBy('tblpayment_consolidated_test.rank','DESC')
        ->orderBy('tblpayment_consolidated_test.name','ASC')
        ->get();
        return $epayment_detail;
  }
  
  public function epaymentTotal($month,$year,$field)
  {
      $epayment_total = DB::table('tblpayment_consolidated_test')
        
        ->where('tblpayment_consolidated_test.month',     '=', $month)
        ->where('tblpayment_consolidated_test.year',      '=', $year)
        //->where('tblpayment_consolidated_test.divisionID',  '=', $division)
        //->where('tblpayment_consolidated_test.courtID',  '=', $court)
        //->where('tblpayment_consolidated_test.bank',      '=', $bankID )
       // ->where('tblpayment_consolidated_test.bankGroup', '=',$bankGroup)
       ->where("tblpayment_consolidated_test.$field",'=',1)
       ->where('tblpayment_consolidated_test.rank','!=',2)
       ->orderBy('tblpayment_consolidated_test.grade','DESC')
        //->orderBy('tblpayment_consolidated_test.step','DESC')
        ->orderBy('tblpayment_consolidated_test.bank','DESC')
        ->orderBy('tblpayment_consolidated_test.rank','DESC')
        ->orderBy('tblpayment_consolidated_test.name','ASC')
        
        ->get();
        return $epayment_total;
  }
  
  public function OtherParameter($staffid, $year,$month,$pera){
	$List= DB::Select("SELECT IFNULL(sum(`amount`),0) as Taxable FROM `tblotherEarningDeduction` WHERE `CVID`='$pera' and `staffid`='$staffid' and `month`='$month' and `year`='$year'");
	if($List)return $List[0]->Taxable;
	return 0;
	}
 public function salaryPush()
 {
     	$data['activemonth'] = DB::table('tblactivemonth')
		->join('tbl_court','tbl_court.id','=','tblactivemonth.courtID')
		->first();
		
		$staff = DB::table('tblstaff_section')->where('user_id','=',Auth::user()->id)->first();
		//dd($staff);
		if($staff == '')
        {
         return back()->with('msg',"You are not permitted to view this page");
        }
		
		if($staff->section == 'AU')
		{
		    $data['vstage'] = 3;
		    $data['view'] = DB::table('tblpayment_consolidated_test')->where('year','=',$data['activemonth']->year)->where('month','=',$data['activemonth']->month)->value('audit_view');
		    
		}
		elseif($staff->section == 'CK')
		{
		    $data['vstage'] = 2;
		    $data['view'] = DB::table('tblpayment_consolidated_test')->where('year','=',$data['activemonth']->year)->where('month','=',$data['activemonth']->month)->value('checking_view');
		    
		}
		elseif($staff->section == 'CA')
		{
		    $data['vstage'] = 5;
		     $data['view'] = DB::table('tblpayment_consolidated_test')->where('year','=',$data['activemonth']->year)->where('month','=',$data['activemonth']->month)->value('ca_view');
		    
		}
		elseif($staff->section == 'DR')
		{
		    $data['vstage'] = 6;
		    $data['view'] = DB::table('tblpayment_consolidated_test')->where('year','=',$data['activemonth']->year)->where('month','=',$data['activemonth']->month)->value('director_view');
		    
		}
		elseif($staff->section == 'SAL')
		{
		    $data['vstage'] = 0;
		     $data['view'] = 1;
		    
		}
		elseif($staff->section == 'VC')
		{
		     $data['view'] = DB::table('tblpayment_consolidated_test')->where('year','=',$data['activemonth']->year)->where('month','=',$data['activemonth']->month)->value('variation_view');
		    
		}
		else
        {
         return back()->with('message',"You are not permitted to view this page");
        }
		$data['loggedStaff'] = $staff->section;
		$data['stage'] =  DB::table('tblpayment_consolidated_test')->where('year','=',$data['activemonth']->year)->where('month','=',$data['activemonth']->month)->first();
		switch ($data['stage']->vstage) {
        case "2":
        $data['section'] = "CHECKING";
        break;
        case "3":
        $data['section'] = "AUDIT";
        break;
        case "5":
        $data['section'] = "CHIEF ACCOUNTANT";
        
        break;
        case "6":
        $data['section'] = "DIRECTOR";
        
        break;
        case "0":
        $data['section'] = "SALARY";
        break;
        }
		//dd()
        return view('salaryApprovalProcessTest.nextaction',$data);
     
 }
 public function process(Request $request)
 {
        $to          = $request['pushTo'];
        $activeYear  = $request['activeYear'];
        $activeMonth = $request['activeMonth'];
        
        $this->validate($request, [       
        'activeMonth'     => 'required', 
        'activeYear'      => 'required|integer', 
        'pushTo'          => 'required', 
        ]);
        
        switch ($to) {
        case "DR":
        $field = 'director_view';
        $vstage = 6;
        
        break;
        case "CA":
       $field = 'ca_view';
       $vstage = 5;
      
        break;
        case "CK":
        $field = 'checking_view';
        $vstage = 2;
        
        break;
        case "AU":
        $field = 'audit_view';
        $vstage = 3;
        break;
        
        case "VC":
        $field = 'variation_view';
        $vstage = 7;
        break;
        
        }
        if($to == 10)
        {
           $update = DB::table('tblpayment_consolidated_test')->where('year','=',$activeYear)->where('month','=',$activeMonth)->update([
            'vstage'  => 10,
            ]); 
        }
        else
        {
        $update = DB::table('tblpayment_consolidated_test')->where('year','=',$activeYear)->where('month','=',$activeMonth)->update([
            "$field"  => 1,
            'vstage'  => $vstage
            ]);
            DB::table('tblsalary_comments_test')->insert([
            'comment'  => $request['minute'],
            'year'     => $request['activeYear'],
            'month'    => $request['activeMonth'],
            'by_who'   => Auth::user()->id,
            'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
            if(!$update)
            {
                return redirect('/approval-process')->with('err','Could not Process, something went wrong');
            }
            else
            {
                return redirect('/approval-process')->with('msg','Successfully Processed');
            }
        //return redirect('/approval-process')->with('msg','');
     
 }
 
 public function rejection(Request $request)
 {
        $to          = $request['pushTo'];
        $activeYear  = $request['activeYear'];
        $activeMonth = $request['activeMonth'];
        
        $this->validate($request, [       
        
        'pushTo'      => 'required', 
        ]);
        
        $staff = DB::table('tblstaff_section')->where('user_id','=',Auth::user()->id)->first();
        
        if($staff->section == 'AU')
        {
        $update = DB::table('tblpayment_consolidated_test')->where('year','=',$activeYear)->where('month','=',$activeMonth)->update([
            'vstage'  => 0,
            'audit_view' =>0,
            'checking_view' =>0,
            ]);
            DB::table('tblsalary_comments_test')->insert([
            'comment'  => $request['minute'],
            'year'     => $request['activeYear'],
            'month'    => $request['activeMonth'],
            'by_who'   => Auth::user()->id,
            'updated_at' => date('Y-m-d H:i:s'),
            ]);
            if(!$update)
            {
                return redirect('/approval-process')->with('err','Could not Process, something went wrong');
            }
            else
            {
                return redirect('/approval-process')->with('msg','Successfully Processed');
            }
        }
        elseif($staff->section == 'CK')
        {
        $update = DB::table('tblpayment_consolidated_test')->where('year','=',$activeYear)->where('month','=',$activeMonth)->update([
            'vstage'  => 0,
            'checking_view' =>0,
            ]);
            DB::table('tblsalary_comments_test')->insert([
            'comment'  => $request['minute'],
            'year'     => $request['activeYear'],
            'month'    => $request['activeMonth'],
            'by_who'   => Auth::user()->id,
            'updated_at' => date('Y-m-d H:i:s'),
            ]);
            if(!$update)
            {
                return redirect('/approval-process')->with('err','Could not Process, something went wrong');
            }
            else
            {
                return redirect('/approval-process')->with('msg','Successfully Processed');
            }
        }
        //return redirect('/approval-process')->with('msg','');
     
 }
 
 public function processToVariationControl(Request $request)
 {
        $to          = $request['pushTo'];
        $activeYear  = $request['activeYear'];
        $activeMonth = $request['activeMonth'];
        
        $this->validate($request, [       
        'activeMonth'     => 'required', 
        'activeYear'      => 'required|integer', 
        'pushTo'      => 'required', 
        ]);
       
       if($to == 'VC')
       {
           
        $update = DB::table('tblpayment_consolidated_test')->where('year','=',$activeYear)->where('month','=',$activeMonth)->update([
            "variation_view"  => 1,
            ]);
            
            DB::table('tblsalary_comments_test')->insert([
            'comment'  => $request['minute'],
            'year'     => $request['activeYear'],
            'month'    => $request['activeMonth'],
            'by_who'   => Auth::user()->id,
            'updated_at' => date('Y-m-d H:i:s'),
            ]);
            
            if(!$update)
            {
                return redirect('/approval-process')->with('err','Could not Process, something went wrong');
            }
            else
            {
                return redirect('/approval-process')->with('msg','Successfully Processed');
            }
       }
       elseif($to == 'SAL')
       {
       
            DB::table('tblsalary_comments_test')->insert([
            'comment'  => $request['minute'],
            'year'     => $request['activeYear'],
            'month'    => $request['activeMonth'],
            'by_who'   => Auth::user()->id,
            'updated_at' => date('Y-m-d H:i:s'),
            ]);
  
            return redirect('/approval-process')->with('msg','Successfully Processed');
            
       }
        //return redirect('/approval-process')->with('msg','');
     
 }
 
 public function create()
  {
     $data['courts'] =  DB::table('tbl_court')->get();
      $courtSessionId = session('anycourt');
    
      $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}


      $data['courtDivisions']  = DB::table('tbldivision')
         ->where('courtID', '=', $courtSessionId)
         ->get();

          $data['allbanklist']  = DB::table('tblbank')
         ->where('tblbank.courtID', '=', $courtSessionId)
         ->distinct()
         ->join('tblbanklist', 'tblbank.bankID', '=', 'tblbanklist.bankID')
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();

         if(count($data['CourtInfo']) > 0)
      {
        
    $data['allbanklist']  = DB::table('tblbanklist')
         //->where('tblbank.courtID', '=', $data['CourtInfo']->courtid)
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();
      }

     return view('salaryApprovalProcessTest.index',$data);
  }

  public function getBank(Request $request)
  {
     $court =  $request['courtID'];
     $allbanklist  = DB::table('tblbank')
         ->where('tblbank.courtID', '=', $court)
         //->distinct()
         ->join('tblbanklist', 'tblbank.bankID', '=', 'tblbanklist.bankID')
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();
     return response()->json($allbanklist);   
  }

  public function Retrieve(Request $request)
  {
    $month             = trim($request->input('month'));
    $year              = trim($request->input('year'));
    $court             = trim($request->input('court'));
    $division          = trim($request->input('division'));

    
    $bankID    = trim($request->input('bankName'));
    $bankGroup = trim($request->input('bankGroup'));
     $data['year'] = $year;
    $data['month'] = $month;

    $staff = DB::table('tblstaff_section')->where('user_id','=',Auth::user()->id)->first();
    
        Session::put('schmonth', $month." ".$year); 
        
        $data['count_sot'] = DB::table('tblpayment_consolidated_test')
          ->where('tblpayment_consolidated_test.month',     '=', $month)
          ->where('tblpayment_consolidated_test.year',      '=', $year)
          ->where('tblpayment_consolidated_test.divisionID',  '=', $division)
          ->where('tblpayment_consolidated_test.courtID',  '=', $court)
          ->where('tblpayment_consolidated_test.SOT', '>', 0 )
          ->where('tblpayment_consolidated_test.rank','!=',2)
          ->count();
    
    if($bankID == '')
    {
     $data['bank'] = '';
    }
      $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
    $this->validate($request, [       
        'month'     => 'required|regex:/^[\pL\s\-]+$/u', 
        'year'      => 'required|integer', 
    ]);
    if($bankID != '')
    {
    $getBank  = DB::table('tblbanklist')
              ->where('bankID',$bankID)
              ->first();

      $bankName = $getBank->bank;
       Session::flash('bank', $bankName);
       
       if($staff->section == "CK")
       {
       $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
       $data['payroll_detail'] = $this->viewData($month,$year,$court,$bankID,"checking_view");
        foreach ($data['payroll_detail'] as $key => $value) {
		            $lis = (array) $value;
		            $lis['hazard'] = $this->OtherParameter($value->staffid, $year,$month,4);
		            $lis['callduty'] = $this->OtherParameter($value->staffid, $year,$month,22);;
		            $value = (object) $lis;
		            $data['payroll_detail'][$key]  = $value;
		        }
		         return view('salaryApprovalProcessTest.summary', $data);
		         
       }
       elseif($staff->section == "AU")
       {
           $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
       $data['payroll_detail'] = $this->viewData($month,$year,$court,$bankID,"audit_view");
        foreach ($data['payroll_detail'] as $key => $value) {
		            $lis = (array) $value;
		            $lis['hazard'] = $this->OtherParameter($value->staffid, $year,$month,4);
		            $lis['callduty'] = $this->OtherParameter($value->staffid, $year,$month,22);;
		            $value = (object) $lis;
		            $data['payroll_detail'][$key]  = $value;
		        }
		         return view('salaryApprovalProcessTest.summary', $data);
       }
        elseif($staff->section == "CA")
       {
           $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
       $data['payroll_detail'] = $this->viewData($month,$year,$court,$bankID,"ca_view");
        foreach ($data['payroll_detail'] as $key => $value) {
		            $lis = (array) $value;
		            $lis['hazard'] = $this->OtherParameter($value->staffid, $year,$month,4);
		            $lis['callduty'] = $this->OtherParameter($value->staffid, $year,$month,22);;
		            $value = (object) $lis;
		            $data['payroll_detail'][$key]  = $value;
		        }
		         return view('salaryApprovalProcessTest.summary', $data);
       }
        elseif($staff->section == "DR")
       {
           $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
       $data['payroll_detail'] = $this->viewData($month,$year,$court,$bankID,"ca_view");
        foreach ($data['payroll_detail'] as $key => $value) {
		            $lis = (array) $value;
		            $lis['hazard'] = $this->OtherParameter($value->staffid, $year,$month,4);
		            $lis['callduty'] = $this->OtherParameter($value->staffid, $year,$month,22);;
		            $value = (object) $lis;
		            $data['payroll_detail'][$key]  = $value;
		        }
		         return view('salaryApprovalProcessTest.summary', $data);
       }
       
		         
    }
    else
    {
       // dd($this->viewData2($month,$year,$court,"audit_view"));
         if($staff->section == "CK")
       {
        $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
        $data['payroll_detail'] = $this->viewData2($month,$year,$court,"checking_view");
         foreach ($data['payroll_detail'] as $key => $value) {
		            $lis = (array) $value;
		            $lis['hazard'] = $this->OtherParameter($value->staffid, $year,$month,4);
		            $lis['callduty'] = $this->OtherParameter($value->staffid, $year,$month,22);;
		            $value = (object) $lis;
		            $data['payroll_detail'][$key]  = $value;
		        }
		         return view('salaryApprovalProcessTest.summary', $data);
       }
        elseif($staff->section == "AU")
       {
        $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
        $data['payroll_detail'] = $this->viewData2($month,$year,$court,"audit_view");
        
         foreach ($data['payroll_detail'] as $key => $value) {
		            $lis = (array) $value;
		            $lis['hazard'] = $this->OtherParameter($value->staffid, $year,$month,4);
		            $lis['callduty'] = $this->OtherParameter($value->staffid, $year,$month,22);;
		            $value = (object) $lis;
		            $data['payroll_detail'][$key]  = $value;
		        }
		         return view('salaryApprovalProcessTest.summary', $data);
       }
       elseif($staff->section == "CA")
       {
        $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
        $data['payroll_detail'] = $this->viewData2($month,$year,$court,"ca_view");
         foreach ($data['payroll_detail'] as $key => $value) {
		            $lis = (array) $value;
		            $lis['hazard'] = $this->OtherParameter($value->staffid, $year,$month,4);
		            $lis['callduty'] = $this->OtherParameter($value->staffid, $year,$month,22);;
		            $value = (object) $lis;
		            $data['payroll_detail'][$key]  = $value;
		        }
		         return view('salaryApprovalProcessTest.summary', $data);
       }
       elseif($staff->section == "DR")
       {
        $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
        $data['payroll_detail'] = $this->viewData2($month,$year,$court,"director_view");
         foreach ($data['payroll_detail'] as $key => $value) {
		            $lis = (array) $value;
		            $lis['hazard'] = $this->OtherParameter($value->staffid, $year,$month,4);
		            $lis['callduty'] = $this->OtherParameter($value->staffid, $year,$month,22);;
		            $value = (object) $lis;
		            $data['payroll_detail'][$key]  = $value;
		        }
		         return view('salaryApprovalProcessTest.summary', $data);
       }
       
       
    }
     
    
  }
  
   public function analysis()
  {
  $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
  $data['courts'] =  DB::table('tbl_court')->get();
      $courtSessionId = session('anycourt');
         $data['courtDivisions']  = DB::table('tbldivision')
         ->where('courtID', '=', $courtSessionId)
         ->get();

          $data['allbanklist']  = DB::table('tblbanklist')
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();

  return view('salaryApprovalProcessTest.analysisParam',$data);
  }
  
  public function analysisDisplay(Request $request)
  {
    $month     = trim($request->input('month'));
    $year      = trim($request->input('year'));
   $staff = DB::table('tblstaff_section')->where('user_id','=',Auth::user()->id)->first();
  
    if($staff->section =='CK')
    {
        $data['group'] = $this->viewAnalylis($month,$year,'checking_view');
    }
    elseif($staff->section =='AU')
    {
        $data['group'] = $this->viewAnalylis($month,$year,'audit_view');
    }
    elseif($staff->section =='CA')
    {
        $data['group'] = $this->viewAnalylis($month,$year,'ca_view');
    }
    elseif($staff->section =='DR')
    {
        $data['group'] = $this->viewAnalylis($month,$year,'director_view');
    }
    
	$data['month'] = trim($request->input('month'));
	$data['year'] = trim($request->input('year'));
    return view('salaryApprovalProcessTest.analysis',$data);
  }
  
  public function payrollSummary()
{
$data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
  $data['courts'] =  DB::table('tbl_court')->get();
      $courtSessionId = session('anycourt');
         $data['courtDivisions']  = DB::table('tbldivision')
         ->where('courtID', '=', $courtSessionId)
         ->get();

          $data['allbanklist']  = DB::table('tblbanklist')
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();

  return view('salaryApprovalProcessTest.index',$data);    
}

public function viewPayrollSummary(Request $request)
{
    $month     = trim($request->input('month'));
    $year      = trim($request->input('year'));
    $bankID    = trim($request->input('bankName'));
   
    //$warrant   = trim($request->input('warrant'));
    $division  = trim($request->input('division'));
     $court    = trim($request->input('court'));
  
    $this->validate($request,[       
          'month'     => 'required|string', 
          'year'      => 'required|integer', 
          
    ]);
    
$staff = DB::table('tblstaff_section')->where('user_id','=',Auth::user()->id)->first();
if($staff == '')
{
    return back()->with('err','You can not Access the page you are requesting');
}

    if($bankID != '')
    {
    $getBank  = DB::table('tblbanklist')
              ->where('bankID',$bankID)
              ->first();

      $bankName = $getBank->bank;
       Session::flash('bank', $bankID);
       
       if($staff->section == "CK")
       {
       $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
       $data['payroll_detail'] = $this->viewData($month,$year,$court,$bankID,"checking_view");
        foreach ($data['payroll_detail'] as $key => $value) {
		            $lis = (array) $value;
		            $lis['hazard'] = $this->OtherParameter($value->staffid, $year,$month,4);
		            $lis['callduty'] = $this->OtherParameter($value->staffid, $year,$month,22);;
		            $value = (object) $lis;
		            $data['payroll_detail'][$key]  = $value;
		        }
		         return view('salaryApprovalProcessTest.viewSummary', $data);
		         
       }
       elseif($staff->section == "AU")
       {
           $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
       $data['payroll_detail'] = $this->viewData($month,$year,$court,$bankID,"audit_view");
        
		         return view('salaryApprovalProcessTest.viewSummary', $data);
       }
        elseif($staff->section == "CA")
       {
           $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
       $data['summary_detail'] = $this->viewData($month,$year,$court,$bankID,"ca_view");
       
       }
        elseif($staff->section == "DR")
       {
           $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
       $data['summary_detail'] = $this->viewData($month,$year,$court,$bankID,"ca_view");
        
		         return view('salaryApprovalProcessTest.viewSummary', $data);
       }
       
		         
    }
    else
    {
       // dd($this->viewData2($month,$year,$court,"audit_view"));
         if($staff->section == "CK")
       {
        $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
        $data['summary_detail'] = $this->viewData2($month,$year,$court,"checking_view");
         
		         return view('salaryApprovalProcessTest.viewSummary', $data);
       }
        elseif($staff->section == "AU")
       {
        $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
        $data['summary_detail'] = $this->viewData2($month,$year,$court,"audit_view");
        
		         return view('salaryApprovalProcessTest.viewSummary', $data);
       }
       elseif($staff->section == "CA")
       {
        $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
        $data['summary_detail'] = $this->viewData2($month,$year,$court,"ca_view");
        
		         return view('salaryApprovalProcessTest.viewSummary', $data);
       }
       elseif($staff->section == "DR")
       {
        $data['courtname'] = '';
      $data['courtDivisions']  = DB::table('tbl_court')
      ->join('tbldivision','tbldivision.courtID','=', 'tbl_court.id')
      ->where('tbl_court.id','=',$court)
      ->where('tbldivision.divisionID','=',$division)
      ->first();
        $data['summary_detail'] = $this->viewData2($month,$year,$court,"director_view");
        
		         return view('salaryApprovalProcessTest.viewSummary', $data);
       }
       
       
    }
//dd($data['summary_detail']);

    Session::put('schmonth', $month." ".$year); 
    if($bankID != '')
    {
    Session::put('bank', $bankName ." ".$bankGroup);
    }
    //Session::put('warrant', $warrant);
    return view('salaryApprovalProcessTest.summary', $data);
  }
  
  /****************** PECARD ***********************/
  
  public function pecardData($fileno,$year,$field)
  {
       $query = DB::table('tblper')
        ->join('tblpayment_consolidated_test', 'tblpayment_consolidated_test.staffid', '=','tblper.ID')
        ->where('tblper.ID', '=', $fileno)
        ->where('tblpayment_consolidated_test.year', '=', $year)
        ->where("tblpayment_consolidated_test.$field", '=',1 )
        ->get();
       return $query;
  }
  
  public function checkCard()
{
    $data['CourtInfo']=$this->CourtInfo();
    if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
    if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

  if($data['CourtInfo'])
  {
      $data['staff'] = DB::table('tblper')->where('courtID','=',$data['CourtInfo']->courtid)->orderBy('surname','ASC')->get();
  }
  else{
      $data['staff'] = DB::table('tblper')->where('courtID','=',$data['CourtInfo']->courtid)->orderBy('surname','ASC')->get();
  }

	return view('salaryApprovalProcessTest.conIndex',$data);
}



public function viewCard(Request $request)
{
    $fileno = $request['staffName'];
    $year   = $request['year'];

   $request->session()->flash('staff',$request['staffName']);
   $request->session()->flash('yr',$request['year']);

    $data['getLevel'] = DB::table('tblpayment_consolidated_test')
        ->where('staffid', '=', $fileno)
        ->where('year', '=', $year)
        ->first();
      if(!$data['getLevel'])
      {
        return back()->with('err','No Record Found');
      }    

$data['details'] = DB::table('tblper')
        ->leftJoin('tblbanklist', 'tblper.bankID', '=', 'tblbanklist.bankID')
        ->select('fileNo', 'surname', 'first_name', 'othernames', 'rank', 'grade', 'step', 'bank', 'AccNo',
            'appointment_date', 'dob', 'home_address', 'employee_type')
        ->where('tblper.ID', '=', $fileno)
        
        ->first();

    //dd($fileno );

    $arrears = DB::table('tblarrears')
        ->where('year', '=', $year)
        ->where('staffid', '=', $fileno)
        ->get();
    $arr = array();
    $app = array();
    foreach ($arrears as $key) {
        if($key->type == 'new-appointment')
            $app[] = $key->month;
        else
            $arr[] = $key->month;
    }
    $data['year'] = $year;
    $data['arr'] = $arr;
    $data['app'] = $app;
    //dd($this->pecardData($fileno,$year,"audit_view"));
    //DB::enableQueryLog();
    
    $staff = DB::table('tblstaff_section')->where('user_id','=',Auth::user()->id)->first();
    if($staff == '')
    {
         return back()->with('err',"You are not permitted to view this page");
    }
    if($staff->section == "CK")
    {
    $query = $this->pecardData($fileno,$year,"checking_view");
    }
    elseif($staff->section == "AU")
    {
    $query = $this->pecardData($fileno,$year,"audit_view");
    }
     elseif($staff->section == "CA")
    {
    $query = $this->pecardData($fileno,$year,"ca_view");
    }
     elseif($staff->section == "DR")
    {
    $query = $this->pecardData($fileno,$year,"director_view");
    }
    else
    {
        return back()->with('message',"You are not permitted to view this page");
    }
    //dd(DB::getQueryLog());
    //dd($query);
    $result = array();
    foreach ($query as  $value)
    {
     $q1 = DB::table('tblotherEarningDeduction')->where('staffid','=',$value->staffid)->where('month','=',$value->month)->where('year','=',$year)->where('CVID','=',15)->first();
     
        $q2 = DB::table('tblotherEarningDeduction')->where('staffid','=',$value->staffid)->where('month','=',$value->month)->where('year','=',$year)->where('CVID','=',16)->first();
        $q3 = DB::table('tblotherEarningDeduction')->where('staffid','=',$value->staffid)->where('month','=',$value->month)->where('year','=',$year)->where('CVID','=',18)->first();
        $q4 = DB::table('tblotherEarningDeduction')->where('staffid','=',$value->staffid)->where('month','=',$value->month)->where('year','=',$year)->where('CVID','=',13)->first();
        $hazard = DB::table('tblotherEarningDeduction')->where('staffid','=',$value->staffid)->where('month','=',$value->month)->where('year','=',$year)->where('CVID','=',4)->first();
        $callDuty = DB::table('tblotherEarningDeduction')->where('staffid','=',$value->staffid)->where('month','=',$value->month)->where('year','=',$year)->where('CVID','=',22)->first();

        $month = $value->month;
        $result[$month]['Bs'] = $value->Bs;
        //	var_dump($value);
        //$result[$month]['actingAllow'] = $value->actingAllow;
        $result[$month]['AEarn'] = $value->AEarn; //arrearsBasic
        $result[$month]['OEarn'] = $value->OEarn;
        $result[$month]['TAX'] = $value->TAX;
        $result[$month]['PEN'] = $value->PEN;
        $result[$month]['NHF'] = $value->NHF;
        $result[$month]['UD'] = $value->UD;
        //$result[$month]['ugv'] = $value->ugv;
        $result[$month]['HA'] = $value->HA;
        $result[$month]['PEC'] = $value->PEC;
        $result[$month]['ML'] = $value->ML;
        $result[$month]['TR'] = $value->TR;
        $result[$month]['FUR'] = $value->FUR;
        $result[$month]['LEAV'] = $value->LEAV;
        $result[$month]['TD'] = $value->TD;
        $result[$month]['NetPay'] = $value->NetPay;
        $result[$month]['TEarn'] = $value->TEarn;
        if($q1 !='') {
            $result[$month]['coopSaving'] = $q1->amount;
        }
        else
        {
            $result[$month]['coopSaving'] = 0;
        }
        if($q2 !='') {
            $result[$month]['coopLoan'] = $q2->amount;
        }
        else
        {
            $result[$month]['coopLoan'] = 0;
        }
        if($q3 !='') {
            $result[$month]['salAdvance'] = $q3->amount;
        }
        else
        {
            $result[$month]['salAdvance'] = 0;
        }
         if($q4 !='') {
            $result[$month]['overTime'] = $q4->amount;
        }
        else
        {
            $result[$month]['overTime'] = 0;
        }
        
        if($hazard !='') {
            $result[$month]['hazard'] = $hazard->amount;
        }
        else
        {
            $result[$month]['hazard'] = 0;
        }
        
        if($callDuty !='') {
            $result[$month]['callDuty'] = $callDuty->amount;
        }
        else
        {
            $result[$month]['callDuty'] = 0;
        }
        //var_dump($result);
    }
    $fullmonth=array("JANUARY","FEBRUARY","MARCH","APRIL","MAY","JUNE","JULY", "AUGUST","SEPTEMBER","OCTOBER","NOVEMBER", "DECEMBER" );
    $rowcount=0;
    $empty=0.0;
    for ($row = 0; $row <=11; $row++)
    {
        $currentmonth=$fullmonth[$row];
        if (!isset($result[$currentmonth]['Bs']))
        {
            $result[$currentmonth]['Bs'] = $empty;
            $result[$currentmonth]['AEarn'] = $empty;
            $result[$currentmonth]['OEarn'] = $empty;
            $result[$currentmonth]['TAX'] = $empty;
            $result[$currentmonth]['PEN'] = $empty;
            $result[$currentmonth]['NHF'] = $empty;
            $result[$currentmonth]['UD'] = $empty;
            $result[$currentmonth]['HA'] = $empty;
            $result[$currentmonth]['ML'] = $empty;
            $result[$currentmonth]['TR'] = $empty;
            $result[$currentmonth]['FUR'] = $empty;
            $result[$currentmonth]['LEAV'] = $empty;
            $result[$currentmonth]['TD'] = $empty;
            $result[$currentmonth]['PEC'] = $empty;
            $result[$currentmonth]['NetPay'] = $empty;
            $result[$currentmonth]['TEarn'] = $empty;
            
            $result[$currentmonth]['coopSaving'] = $empty;
            $result[$currentmonth]['coopLoan']   = $empty;
            $result[$currentmonth]['salAdvance'] = $empty;
            $result[$currentmonth]['overTime']   = $empty;
            
            $result[$currentmonth]['hazard']     = $empty;
            $result[$currentmonth]['callDuty']   = $empty;


        }
    }
    $imageNewName        = $fileno . '.jpg';
    $path                = base_path() . '/public/passport/';
    if(File::exists(base_path() . '/public/passport/' . $imageNewName )) //check folder
    {
        $user_picture = $imageNewName;
    }else{
        $user_picture =  '0.png';
    }
    $data['image'] = $user_picture;
    $data['result'] = $result;

    return view('/salaryApprovalProcessTest.conPeReport',$data);
}

/**************************treasury 209***************************/
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
   	    return view('salaryApprovalProcessTest.treasury', $data);
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
		
		 $staff = DB::table('tblstaff_section')->where('user_id','=',Auth::user()->id)->first();
        if($staff == '')
        {
             return back()->with('err',"You are not permitted to view this page");
        }
        if($staff->section == "CK")
        {
       	$reportess=$this->Tr2019('','',$year,$month,$type,$bank,$currentstate,"checking_view");
        }
        elseif($staff->section == "AU")
        {
             
          $reportess=$this->Tr2019('','',$year,$month,$type,$bank,$currentstate,"audit_view");
        }
        elseif($staff->section == "CA")
        {
          $reportess=$this->Tr2019('','',$year,$month,$type,$bank,$currentstate,"ca_view");
        }
        elseif($staff->section == "DR")
        {
          $reportess=$this->Tr2019('','',$year,$month,$type,$bank,$currentstate,"director_view");
        }
        else
        {
            return back()->with('err',"You are not permitted to view this page");
        }
	

		$data['payment']=$reportess;
		$data['Tr2019Head']=$this->Tr2019Head($type);
		$data['selectedMonth'] = $request['month'];
		$data['selectedYear'] = $request['year'];
		return view('salaryApprovalProcessTest.treasuryReport', $data);
		//dd($reportess);
			
	}
	
	Public function Tr2019($court,$division,$year,$month,$para,$bank,$residential='',$field){
	  $qresidential=1;
	  if($residential!=''){$qresidential="`current_state`='$residential'";}
	if($para=='' || $para=='Select'){return [];}
	$qbank=1;
	$vpara='';
	$qpara=1;
	if($bank!=''){$qbank="`bank`='$bank'";}
	switch ($para) {
	    case "coop":
	        $vpara=",(SELECT sum(`amount`) FROM `tblotherEarningDeduction` WHERE (`CVID`='15' or `CVID`='16')  and `tblotherEarningDeduction`.`year`='$year' and `tblotherEarningDeduction`.`month`='$month' and `tblotherEarningDeduction`.`staffid`=`tblpayment_consolidated_test`.`staffid`) as Vpara";
	        $qpara= "exists(SELECT null FROM `tblotherEarningDeduction` WHERE (`CVID`='15' or `CVID`='16')  and `tblotherEarningDeduction`.`year`='$year' and `tblotherEarningDeduction`.`month`='$month' and `tblotherEarningDeduction`.`staffid`=`tblpayment_consolidated_test`.`staffid`)";
	        break;
	        case "2":
	        $vpara=",(SELECT sum(`amount`) FROM `tblotherEarningDeduction` WHERE (`CVID`='$para')  and `tblotherEarningDeduction`.`year`='$year' and `tblotherEarningDeduction`.`month`='$month' and `tblotherEarningDeduction`.`staffid`=`tblpayment_consolidated_test`.`staffid`) as Vpara";
	        $qpara= "exists(SELECT null FROM `tblotherEarningDeduction` WHERE (`CVID`='$para')  and `tblotherEarningDeduction`.`year`='$year' and `tblotherEarningDeduction`.`month`='$month' and `tblotherEarningDeduction`.`staffid`=`tblpayment_consolidated_test`.`staffid`)";
	        break;
	        case "18":
	        $vpara=",(SELECT sum(`amount`) FROM `tblotherEarningDeduction` WHERE (`CVID`='$para')  and `tblotherEarningDeduction`.`year`='$year' and `tblotherEarningDeduction`.`month`='$month' and `tblotherEarningDeduction`.`staffid`=`tblpayment_consolidated_test`.`staffid`) as Vpara";
	        $qpara= "exists(SELECT null FROM `tblotherEarningDeduction` WHERE (`CVID`='$para')  and `tblotherEarningDeduction`.`year`='$year' and `tblotherEarningDeduction`.`month`='$month' and `tblotherEarningDeduction`.`staffid`=`tblpayment_consolidated_test`.`staffid`)";
	        break;
	        case "15":
	        $vpara=",(SELECT sum(`amount`) FROM `tblotherEarningDeduction` WHERE (`CVID`='$para')  and `tblotherEarningDeduction`.`year`='$year' and `tblotherEarningDeduction`.`month`='$month' and `tblotherEarningDeduction`.`staffid`=`tblpayment_consolidated_test`.`staffid`) as Vpara";
	        $qpara= "exists(SELECT null FROM `tblotherEarningDeduction` WHERE (`CVID`='$para')  and `tblotherEarningDeduction`.`year`='$year' and `tblotherEarningDeduction`.`month`='$month' and `tblotherEarningDeduction`.`staffid`=`tblpayment_consolidated_test`.`staffid`)";
	        break;
	        case "16":
	        $vpara=",(SELECT sum(`amount`) FROM `tblotherEarningDeduction` WHERE (`CVID`='$para')  and `tblotherEarningDeduction`.`year`='$year' and `tblotherEarningDeduction`.`month`='$month' and `tblotherEarningDeduction`.`staffid`=`tblpayment_consolidated_test`.`staffid`) as Vpara";
	        $qpara= "exists(SELECT null FROM `tblotherEarningDeduction` WHERE (`CVID`='$para')  and `tblotherEarningDeduction`.`year`='$year' and `tblotherEarningDeduction`.`month`='$month' and `tblotherEarningDeduction`.`staffid`=`tblpayment_consolidated_test`.`staffid`)";
	        break;
	        
	    default:
	       $vpara=" ,`$para` as Vpara";
	       
		} 
	
	$List= DB::Select("SELECT * $vpara FROM `tblpayment_consolidated_test` WHERE `tblpayment_consolidated_test`.$field = 1 and `year`='$year' and `month`='$month' and $qbank and $qpara and rank<>2 and $qresidential order by rank DESC, grade DESC, step DESC");
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
	
	$List= DB::Select("SELECT * $vpara FROM `tblpayment_consolidated_test` WHERE `year`='$year' and `month`='$month' and $qbank and $qpara and rank<>2");
	return $List;
	}
	
	public function displayComments($year,$month)
    {
        $data['cmt'] = DB::table('tblsalary_comments_test')
                    ->join('users', 'users.id', '=', "tblsalary_comments_test.by_who")
                    ->where('tblsalary_comments_test.year', '=', $year)
                    ->where('tblsalary_comments_test.month', '=', $month)
                    ->get();
                    
                    return view('salaryApprovalProcessTest.comments',$data);
    }
    
    
  public function bankSchedule()
  {
      $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
      $data['courts'] =  DB::table('tbl_court')->get();
      $courtSessionId = session('anycourt');
         $data['courtDivisions']  = DB::table('tbldivision')
         ->where('courtID', '=', $courtSessionId)
         ->get();

          $data['allbanklist']  = DB::table('tblbanklist')
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();

      return view('salaryApprovalProcessTest.bankSchedule',$data);
  }
  
  public function postBankSchedule(Request $request)
  {
    $month     = trim($request->input('month'));
    $year      = trim($request->input('year'));
    
    $data['month'] = $month;
    $data['year'] = $year;
    
  /*$data['group'] = DB::table('tblpayment_consolidated_test')
    ->join('tblbanklist','tblbanklist.bankID','=','tblpayment_consolidated_test.bank')
    ->where('tblpayment_consolidated_test.month',     '=', $month)
    ->where('tblpayment_consolidated_test.year',      '=', $year)
    ->orderBy('tblpayment_consolidated_test.bank', 'Asc')
    ->select('*','tblbanklist.bank as staffbank','tblpayment_consolidated_test.bank as bk')
    
    ->get();*/
    
    $staff = DB::table('tblstaff_section')->where('user_id','=',Auth::user()->id)->first();
    
    if($staff == '')
    {
        return back()->with('msg','You are not permitted to view that page');
    }
    
    if($staff->section == 'AU')
    {
    $data['epayment_detail'] = $this->schedule($month,$year,"audit_view");
    $data['epayment_total'] = $this->epaymentTotal($month,$year,"audit_view");
    }
    else if($staff->section == 'CK')
    {
    $data['epayment_detail'] = $this->schedule($month,$year,"checking_view");
    $data['epayment_total'] = $this->epaymentTotal($month,$year,"checking_view");
    }
    
    else if($staff->section == 'CA')
    {
    $data['epayment_detail'] = $this->schedule($month,$year,"ca_view");
    $data['epayment_total'] = $this->epaymentTotal($month,$year,"ca_view");
    }
    else if($staff->section == 'DR')
    {
    $data['epayment_detail'] = $this->schedule($month,$year,"director_view");
    $data['epayment_total'] = $this->epaymentTotal($month,$year,"director_view");
    }
        
    return view('salaryApprovalProcessTest.viewBankShedule',$data);
  }
   
}