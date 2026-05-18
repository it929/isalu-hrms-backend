<?php

namespace App\Http\Controllers\funds;

use Illuminate\Http\Request;
//use Auth;
use App\Http\Requests;
use DB;
use Auth;
use Session;
class AccountController extends functionController
{
	public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->middleware('auth');
        $this->username = Session::get('userName');
            return $next($request);
        });
    }//
    
     public function AccountType(Request $request)
   {   
   	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['division'] = trim($request['division']);
	   $data['court'] = trim($request['court']);
	   $court= trim($request['court']);
	   $del= trim($request['delcode']);
	   $data['category']= $request['category'];
	   $data['type']= $request['type'];
	   $data['success'] = "";
	 if ( isset( $_POST['add'] ) ) {
	 $this->validate($request, [
		'category'      	=> 'required',
		'type'      	   	=> 'required',
		]);
	$para=$this->LedgerCategoryPara($request['category']);
	if ($para){
	 DB::table('tblaccounttype')->insert(array(
		'groupid'    	=> $para[0]->groupid,
                'categoryid'    	=> $data['category'],
		'typecode'	    	=> $this->NextTypeCode( $para[0]->categorycode,$data['category']),
		'accounttype'    	=> $data['type'],	
	));
	}
	}
	 $data['LedgerCategory'] = $this->LedgerCategory();
	 $data['LedgerType'] = $this->LedgerType($data['category']);
   	return view('Account.type', $data);
   } 

   public function AccounLedger(Request $request)
   {   
   	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['division'] = trim($request['division']);
	   $data['court'] = trim($request['court']);
	   $court= trim($request['court']);
	   $del= trim($request['delcode']);
	   $data['category']= $request['category'];
	   $data['type']= $request['type'];
	   $data['ledger']= $request['ledger'];
	   $data['success'] = "";
	 if ( isset( $_POST['add'] ) ) {
	 $this->validate($request, [
		'category'      	=> 'required',
		'type'      	   	=> 'required',
		'ledger'      	   	=> 'required',
		]);
	$para=$this->LedgerTypePara($request['type']);
	if ($para){
	 DB::table('tblaccountledger')->insert(array(
		'groupid'    	=> $para[0]->groupid,
                'categoryid'    	=> $data['category'],
                'typeid'    	=> $data['type'],
		'accountNo'	    	=> $this->NextLedgerCode( $para[0]->typecode,$data['type']),
		'accountname'    	=> $data['ledger'],	
	));
	}
	}
	 $data['LedgerCategory'] = $this->LedgerCategory();
	 $data['LedgerType'] = $this->LedgerType($data['category']);
	 $data['Ledger'] = $this->Ledger($data['type'],$data['category']);
   	return view('Account.ledger', $data);
   }
   
   
   
   public function postDepartment(Request $request)
   {   
   	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['department'] = trim($request['department']);
	   $data['court'] = trim($request['court']);
	   $court= trim($request['court']);
	   $del= trim($request['delcode']);
	   $department= trim($request['department']);
	   $data['success'] = "";
	  $data['showcourt'] = true;
	    if ($this->UserType($this->username)=='NONTECHNICAL'){$data['showcourt'] = false;
	    $data['court'] = $this->StaffCourt($this->username);
	    $data['courtname'] = $this->CourtName($this->StaffCourt($this->username));
	    }
	   $data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
	   $data['DepartmentList'] = $this->DepartmentList($court);
		DB::Delete("DELETE FROM `tbldepartment` WHERE `id`='$del'");
		$updatedby = $this->username;
	    
	   if ($this->ConfirmDepartment($court,$department)){$data['warning'] = "$department  section already exist with the selected court";
	   return view('basicparameter.department', $data);
	   }
	   
		if ( isset( $_POST['add'] ) ) {
		$this->validate($request, [
		'department'      	=> 'required'
		,'court'      	   	=> 'required'
		
		]);
			DB::insert("INSERT INTO `tbldepartment`(`courtID`, `department`) VALUES ('$court','$department')");
			$data['DepartmentList'] = $this->DepartmentList($court);
			$data['department'] = "";
			$data['success'] = "$department section successfully updated";
			return view('basicparameter.department', $data);
		 }
	   
	  $data['username'] = "";
   	return view('basicparameter.department', $data);
   }
 public function getDesignation(Request $request)
   {  
	
	   //die($this->NewFileNo("1"));
	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['designation'] = "";
	   $data['court'] = "";
	   if(session::get('departmentID','CourtID') == "")
           {
	   	$department = trim($request['department']);
	   	$court= trim($request['court']);
	   }else {
	   	$department = session::get('departmentID');
	   	$court = session::get('CourtID');
	   }
	   $data['success'] = "";
	   $data['showcourt'] = true;
	    if ($this->UserType($this->username)=='NONTECHNICAL'){$data['showcourt'] = false;
	    $data['court'] = $this->StaffCourt($this->username);
	    $data['courtname'] = $this->CourtName($this->StaffCourt($this->username));
		}
		$courtID = DB::table('tbl_court')->get();
		$data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
		
	   //$data['DesignationList'] = $this->DesignationList($court);

	   $data['DesignationList'] = DB::table('tbldesignation')
	   ->join('tbl_court','tbl_court.id','=','tbldesignation.courtID')
	   ->join('tbldepartment','tbldepartment.id','=','tbldesignation.departmentID')
	   ->where('tbldesignation.courtID','=',$court)
		->orderBy('tbldesignation.grade', 'desc')
	   ->get();
// dd($data['DesignationList']);
	  
   	return view('basicparameter.designation', $data);
   }
   
   
   //Victor New Function
   
   public function Designation(Request $request)
   {   
   	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['designation'] = "";
	   $data['court'] = "";
	     $data['success'] = "";
	   $data['showcourt'] = true;
        if(session::get('departmentID','CourtID') == "")
           {
	   	$department = trim($request['department']);
	   	$court= trim($request['court']);
	   }else {
	   	$department = session::get('departmentID');
	   	$court = session::get('CourtID');
	   }
	   
	   $data['department'] =$department;
	   $data['grade'] = trim($request['grade']);
	   $grade= trim($request['grade']);
	   $data['designation'] = trim($request['designation']);
	   $designation= trim($request['designation']);
	   
	   $data['success'] = "";
	   
	   if ($this->UserType($this->username)=='NONTECHNICAL'){$data['showcourt'] = false;
	    $data['court'] = $this->StaffCourt($this->username);
	    $data['courtname'] = $this->CourtName($this->StaffCourt($this->username));
	    }
	   $data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
	   $data['DepartmentList'] = $this->DepartmentList($court);
	
	  $del= trim($request['delcode']);
	DB::DELETE ("DELETE FROM `tblPost` WHERE `postID`='$del'");
	$data['DesignationList'] = $this->DesignationList($court);
	session::forget('departmentID');
	session::forget('CourtID');
		if ( isset( $_POST['add'] ) ) {
		
	    $this->validate($request, [
		'department'      	=> 'required',
		'grade'      	=> 'required',
		]);
		
		if ($this->ConfirmGrade2($grade, $department, $designation))
		
		// DB::table('tblPost')->where('grade',$ID)->update(['Post' => $designation]);
		
		{$data['warning'] = "$designation or Grade $grade  already exist with the selected department";
	   	return view('basicparameter.designation', $data);
	   } else{
			DB::insert("INSERT INTO `tblPost`( `Post`,`cadreID`,`grade`) VALUES ('$designation','$department','$grade')");
			$data['DesignationList'] = $this->DesignationList2($department);
			$data['DeptList'] = $this->DeptList2();
			$data['designation'] = "";
			$data['success'] = "$designation section successfully Added";
			return view('basicparameter.designation', $data);
			
			}
		 }
	   
	  $data['username'] = "";
   	return view('basicparameter.designation', $data);
   } 
   
   
   public function postDesignation(Request $request)
   {   
   	   $data['error'] = "";
	   $data['warning'] = "";
	      
        if(session::get('departmentID','CourtID') == "")
           {
	   	$department = trim($request['department']);
	   	$court= trim($request['court']);
	   }else {
	   	$department = session::get('departmentID');
	   	$court = session::get('CourtID');
	   }
	   $data['designation'] = trim($request['designation']);
	   $data['level'] = trim($request['level']);
	   $data['court'] = trim($request['court']);
	   
	   $designation= trim($request['designation']);
	   $data['department'] = trim($request['department']);
	   $level = trim($request['level']);
	   $data['success'] = "";
	  $data['showcourt'] = true;
	  $del= trim($request['delcode']);
	    if ($this->UserType($this->username)=='NONTECHNICAL'){$data['showcourt'] = false;
	    $data['court'] = $this->StaffCourt($this->username);
	    $data['courtname'] = $this->CourtName($this->StaffCourt($this->username));
	    }
	   $data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
	
	DB::DELETE ("DELETE FROM `tbldesignation` WHERE `id`='$del'");
	$data['DesignationList'] = $this->DesignationList($court);  
	   
		
		$updatedby = $this->username;
	    $this->validate($request, [
		'designation'      	=> 'required',
		'court'      	   	=> 'required',
		]);
		if ($this->ConfirmDesignation($court,$designation)){
		$data['warning'] = "$designation designation already exist with the selected court";
	   	return view('basicparameter.designation', $data);
	         }
			DB::insert("INSERT INTO `tbldesignation`(`courtID`, `designation`,`departmentID`,`grade`) VALUES ('$court','$designation','$department','$level')");
			$data['DesignationList'] = $this->DesignationList($court);
			$data['designation'] = "";
			$data['success'] = "$designation section successfully updated";
			
		
	   
		   
	
		  $data['DesignationList'] = DB::table('tbldesignation')
		 ->join('tbl_court','tbl_court.id','=','tbldesignation.courtID')
		 ->join('tbldepartment','tbldepartment.id','=','tbldesignation.departmentID')
		 ->where('tbldesignation.courtID','=',$court)
		 ->orderBy('tbldesignation.grade', 'desc')
		 ->get();
		 //dd($data['DesignationList']);
	
	
			return view('basicparameter.designation', $data);
   }
   
   
    public function updateDesignation(Request $request){
   	$data['error'] = "";
	   $data['warning'] = "";
	    $data['success'] = "";
	$CourtID = trim($request['CourtID']);    
	$department = trim($request['DeptID']);
	Session::put('DepartmentID', $department);
	Session::put('CourtID', $CourtID);
   	$designation = trim($request['designation']);
        $PostID = trim($request['PostID']);
        
        DB::table('tbldesignation')->where('id',$PostID)->update(['designation' => $designation]);
        //$data['success'] = "$designation section successfully Updated";
        //$data['DesignationList'] = $this->DesignationList2($department);
	//$data['DeptList'] = $this->DeptList2();
	//return view('basicparameter.designation', $data);
	return redirect('basic/designation')->with('message',' successfully updated');;
   }
   
    public function deletePost(Request $request){
   
   $postID = trim($request['PostID']);
   
   $department = trim($request['depty']);
   $court = trim($request['courty']);
   Session::put('CourtID', $court);
	
   
   	DB::table('tbldesignation')->where('id', $postID)->delete();
   	
        return redirect('basic/designation')->with('message','Post successfully deleted');
   
   }
   
   
      public function companyInfo(Request $request)
   {   
   	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	  $shortcode= trim($request['shortcode']);
	   $data['shortcode'] = $shortcode;
	   $organisationname= trim($request['organisationname']);
	   $data['organisationname'] = $organisationname;
	   $phoneno= trim($request['phoneno']);
	   $data['phoneno'] = $phoneno;
	   $email= trim($request['email']);
	   $data['email'] = $email;
	   $address= trim($request['address']);
	   $data['address'] = $address;
	   $data['imgpath'] = $address;
	   
	    
	   
	   
		if ( isset( $_POST['update'] ) ) {
		if (DB::table('tblcompany')->get()){
		DB::update("UPDATE `tblcompany` SET `companyName`='',`shortCode`='',`logoPath`='',`phoneNo`='',`emailAddress`='',`Contact Address`='' ");
		$data['success'] = "Information successfully updated";
		   return view('basicparameter.companyinfo', $data);
		   }
		
			DB::insert("INSERT INTO `tblcompany`(`companyName`, `shortCode`, `logoPath`, `phoneNo`, `emailAddress`, `Contact Address`) VALUES ('','','','','','')");
			$data['DepartmentList'] = $this->DepartmentList($court);
			$data['department'] = "";
			$data['success'] = "Information successfully updated";
			return view('basicparameter.companyinfo', $data);
		 }
	   
	  $data['username'] = "";
   	return view('basicparameter.companyinfo', $data);
   } 
   
  
   
   
   public function Divisionsetup(Request $request)
   {   
   	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['division'] = trim($request['division']);
	   $data['court'] = trim($request['court']);
	   $court= trim($request['court']);
	   $del= trim($request['delcode']);
	   $division= trim($request['division']);
	   $data['success'] = "";
	  $data['showcourt'] = true;
	    if ($this->UserType($this->username)=='NONTECHNICAL'){$data['showcourt'] = false;
	    $data['court'] = $this->StaffCourt($this->username);
	    $data['courtname'] = $this->CourtName($this->StaffCourt($this->username));
	    }
	   $data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
	   $data['DivisionList'] = $this->DivisionList1($court);
		DB::Delete("DELETE FROM `tbldepartment` WHERE `id`='$del'");
		//$updatedby = $this->username;
	    
	  
	   
		if ( isset( $_POST['add'] ) ) {
		$this->validate($request, [
		'division'      	=> 'required'
		,'court'      	   	=> 'required'
		
		]);
		 if ($this->ConfirmDivision($court,$division)){$data['warning'] = "$division already exist with the selected court";
	   return view('basicparameter.division', $data);
	   }
			DB::insert("INSERT INTO `tbldivision`( `division`, `courtID`) VALUES ('$division ','$court')");
			$data['DivisionList'] = $this->DivisionList1($court);
			$data['division'] = "";
			$data['success'] = "$division section successfully updated";
			return view('basicparameter.division', $data);
		 }

		 $data['DesignationList'] = DB::table('tbldesignation')
	   ->join('tbl_court','tbl_court.id','=','tbldesignation.courtID')
	   ->join('tbldepartment','tbldepartment.id','=','tbldesignation.departmentID')
	   ->where('tbl_court.id','=',$court)
	   ->get();
	   
	 $data['DivisionList'] = $this->DivisionList1($court);
   	return view('basicparameter.division', $data);
   } 


   public function ControlVariable(Request $request)
   {   
   	   $data['error'] = "";
	   $data['warning'] = "";
	   
	
	  $data['showcourt'] = true;
	  
  	
	   
	   if(session::get('CourtID') == "" AND session::get('DepartmentIDID') == ""  )
           {
	   	$department = trim($request['department']);
	   	$court= trim($request['court']);
	   }else {
	 
	   	$court = session::get('CourtID');
	   	$department = session::get('DepartmentID');
	   }
	  
   	
	   $data['court'] = $court;   
	   
	   $data['department'] = $department; 
	   $level= trim($request['level']);
	   $data['level'] = $level;
	   $designation= trim($request['designation']);
	   $data['designation'] = $designation;
	   
	   $del= trim($request['delcode']);

	      $data['success'] = "";
	   $data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
	   $data['DesignationList'] =$this->DesignationList2($court,$department);
	   
	   $data['DepartmentList'] = $this->DepartmentList($court);
	   session::forget('CourtID');
	   
	
		
		if ( isset( $_POST['add'] ) ) {
		
	    $this->validate($request, [
		'department'      	=> 'required',
		'level'      	=> 'required',
		]);
		
		if ($this->ConfirmGrade2($level, $department, $designation,$court))
		
		// DB::table('tblPost')->where('grade',$ID)->update(['Post' => $designation]);
		
		{$data['warning'] = "$designation or Grade $level already exist with the selected department";
	   	return view('basicparameter.designation', $data);
	   } else{
			DB::insert("INSERT INTO `tbldesignation`( `courtId`,`departmentId`,`grade`,`designation`) VALUES ('$court','$department','$level','$designation')");
			$data['DesignationList'] = $this->DesignationList2($court,$department);
			$data['DepartmentList'] = $this->DepartmentList($court);
			$data['designation'] = "";
			$data['success'] = "$designation section successfully Added";
			return view('basicparameter.designation', $data);
			
			}
		 }	
			
   	return view('basicparameter.designation', $data);
   }
   
      public function UpdateRankDesignation(Request $request)
   {   
   	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	  
	   $username= $request['username'];
	   $data['username'] = $username;
	   $designation= $request['designation'];
	   $data['designation'] = $designation;
		if ( isset( $_POST['update'] ) ) {
		
		DB::update("UPDATE `tblaction_rank` SET `userid`='$username' WHERE `code`='$designation' ");
		$data['success'] = "Information successfully updated";
		   
		 }
	   
	  $data['DesignationList'] = $this->RankDesignationList();
	  $data['UserList'] = $this->UserList();
   	return view('basicparameter.actiondesignation', $data);
   } 
   

}