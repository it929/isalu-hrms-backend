<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;

use App\Http\Requests;
use App\Role;
use App\User;
use Auth;
use Entrust;
use App\Permission;

use Illuminate\Support\Facades\Request;
use DB;
use Carbon\Carbon;
use Session;

class functionController_arrear extends Controller
{

    Public function RandomPassword() {
		$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
		$pass = array(); 
		$alphaLength = strlen($alphabet) - 1; 
		for ($i = 0; $i < 8; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode($pass);
	}//
	
	Public function senderemail() {
		return "info@mbrcomputers.net";
	}//
	
	Public function UserName($id) {
		$staffAuth=DB::table('users')->select('username')->where('ID', '=', $id)->first();
		return $staffAuth->username;
	}
	
	
    public function addLog($operation)
    {
        $ip = Request::ip();
        $url = Request::fullUrl();
        $userID = Auth::user()->id;
        $nowInNigeria = Carbon::now('Africa/Lagos');
        $cmpname = php_uname('a');
        $host = $_SERVER['HTTP_HOST'];
        DB::table('audit_log')->insert(
            ['comp_name' => $cmpname, 'user_id' => $userID, 'date' => $nowInNigeria, 'ip_addr' => $ip, 'operation' => $operation,
            'host' => $host, 'referer' => $url]);
        return;
    }
    
    public function getOneStaff($staffid)
    {
        //DB::enableQueryLog();
        $staffList = DB::table('tblper')
            //->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
            ->select('fileNo', 'surname', 'first_name', 'othernames')
            
            ->where('tblper.ID', '=', $staffid)
            ->where('tblper.staff_status', 1)
            ->first();
        //dd(DB::getQueryLog());
        return $staffList;
    }
     public function staffCV($cvid)
    {
        //DB::enableQueryLog();
        $cv = DB::table('tblcvSetup')
            //->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')   
            ->where('ID', '=', $cvid)
            ->first();
        //dd(DB::getQueryLog());
        return $cv;
    }
	
Public function StaffNo($username) {
		$staffAuth=DB::table('tblper')->select('tblper.fileNo')
->join('users', 'users.id', '=', 'tblper.UserID')
->where('users.username', '=', $username)->first();
if($staffAuth)
	   {
			return $staffAuth->fileNo;
	   }
	   else
	   {
	   return "";
	   }
		
	}//
	
	
	Public function getUserBasicData(){
		$staffName = DB::table('tblper')
	   	->select('fileNo', 'surname', 'first_name')
	   	->get();
		return $staffName ;
	}//end f
	 
	 
	Public function DepartmentList($court){
		$DepartmentList= DB::Select("SELECT *,
		(SELECT `court_name`  FROM `tbl_court` WHERE `tbl_court`.`id`=`tbldepartment`.`courtID`) as court_names 
		FROM `tbldepartment` WHERE `tbldepartment`.`courtID`='$court'");
		return $DepartmentList;
	}
	Public function DivisionList1 ($court){
		$DepartmentList= DB::Select("SELECT *,
		(SELECT `court_name`  FROM `tbl_court` WHERE `tbl_court`.`id`=`tbldivision`.`courtID`) as court_names 
		FROM `tbldivision` WHERE `tbldivision`.`courtID`='$court'");
		return $DepartmentList;
	}
	Public function DivisionList($court){
		$List= DB::Select("SELECT * FROM `tbldivision` WHERE `courtID`='$court'");
		return $List;
	}
	Public function ConfirmDepartment($court,$department){
		$confir= DB::Select("SELECT * FROM `tbldepartment` WHERE `courtID`='$court' and `department`='$department'");
		if(($confir))
		   {
			   return true;
		   }
		   else
		   {
			return false;
		   }
	}
	Public function ConfirmDivision($court,$division){
		$confir= DB::Select("SELECT * FROM `tbldivision` WHERE `courtID`='$court' and `division`='$division'");
		if(($confir))
		   {
			   return true;
		   }
		   else
		   {
			return false;
		   }
	}
	
	Public function DesignationList($court){
		$DepartmentList= DB::Select("SELECT *,
		(SELECT `court_name`  FROM `tbl_court` WHERE `tbl_court`.`id`=`tbldesignation`.`courtID` ) and (SELECT `department`  FROM `tbldepartment` WHERE `tbldepartment`.`id`=`tbldesignation`.`departmentID`)  as court_names 
		FROM `tbldesignation` WHERE `tbldesignation`.`courtID`='$court' ");
		return $DepartmentList;
	}
	
	Public function ConfirmDesignation($court,$designation){
		$confir= DB::Select("SELECT * FROM `tbldesignation` WHERE `courtID`='$court' and `designation`='$designation'");
	if(($confir)){return true;}else{return false;}
	}
	Public function ConfirmGradeLeave($court,$grade){
		$confir= DB::Select("SELECT * FROM `tblgrade_leave_assignment` WHERE `courtID`='$court' and `grade`='$grade'");
	if(($confir)){return true;}else{return false;}
	}
	Public function UserType($username){
		$staffAuth=DB::table('users')->select('user_type')
		->where('users.username', '=', $username)->first();
	if($staffAuth){return $staffAuth->user_type;}else{return "";}
	}//
	
	Public function StaffCourt($username) {
		$staffAuth=DB::table('users')->select('courtID')
		->where('username', '=', $username)->first();
		//dd($username);
		if($staffAuth){return $staffAuth->courtID;}else{return "";}
	}//
	Public function StaffSection($username) {
		$staffAuth=DB::table('tblper')->select('tblper.section')
		->join('users', 'users.id', '=', 'tblper.UserID')
		->where('users.username', '=', $username)->first();
		if($staffAuth){return $staffAuth->section;}else{return "";}
	}
	Public function StaffGradeLevel($username) {
		$staffAuth=DB::table('tblper')->select('tblper.grade')
		->join('users', 'users.id', '=', 'tblper.UserID')
		->where('users.username', '=', $username)->first();
		if($staffAuth){return $staffAuth->grade;}else{return "";}
	}//
	Public function StaffID($username) {
		$staffAuth=DB::table('tblper')->select('tblper.ID')
		->join('users', 'users.id', '=', 'tblper.UserID')
		->where('users.username', '=', $username)->first();
		if($staffAuth){return $staffAuth->ID;}else{return "";}
	}//
	Public function CourtName($id){
		$returndata=DB::table('tbl_court')->select('court_name')
		->where('id', '=', $id)->first();
	if($returndata){return $returndata->court_name;}else{return "";}
	}//
	Public function LeaveGradetList($courtid){
		$List= DB::Select("SELECT * FROM `tblgrade_leave_assignment` WHERE `courtID`='$courtid'");
		return $List;
	}
	Public function CourtList(){
		$List= DB::Select("SELECT * FROM `tbl_court` WHERE `active`=1");
		return $List;
	}
	Public function LeaveStatus(){
		$List= DB::Select("SELECT * FROM `tblleave_status`");
		return $List;
	}
	Public function LeavePeriodList(){
		$List= DB::Select("SELECT * FROM `tblleave_period` group by `period`");
		return $List;
	}
	Public function DependantList($fileno){
		$DependantList= DB::Select("SELECT *,
		(SELECT  `relationship` FROM `tbldependant_relationship` WHERE tbldependant_relationship.`id`=`tblstaff_dependants`.relationshipID) as dependantRelationships
		 FROM `tblstaff_dependants` WHERE `fileNo`='$fileno'");
		return $DependantList;
	}
	Public function ConfirmDependant($fileno,$dependant){
		$confir= DB::Select("SELECT * FROM `tblstaff_dependants` WHERE `fileNo`='$fileno' and `dependantName`='$dependant'");
		if(($confir)){return true;}else{return false;}
	}
	Public function StaffDetails($username) {
	$staffAuth=DB::table('tblper')->select('tblper.fileNo','tblper.surname','tblper.first_name','tblper.othernames')
	->join('users', 'users.id', '=', 'tblper.UserID')
	->where('users.username', '=', $username)->first();
	if($staffAuth)
		   {
				return $staffAuth->fileNo. ":".$staffAuth->surname. " ".$staffAuth->first_name. " ".$staffAuth->othernames ;
		   }
		   else
		   {return "";}
			
		}//
	Public function StaffDetails2($court,$division, $staffName){
          $DepartmentList= DB::Select("SELECT *
          , (SELECT `department` FROM `tbldepartment` WHERE `tbldepartment`.`id`=`tblper`.department ) as DepartmentCap
           , (SELECT `designation` FROM `tbldesignation` WHERE `tbldesignation`.`departmentID`=`tblper`.department and `tbldesignation`.`grade`=`tblper`.grade) as DesignationCap
        FROM `tblper` WHERE `divisionID` ='$division' AND `courtID`='$court' AND `fileNo`= '$staffName'  ");
        return $DepartmentList;
    }
		Public function LeaveType(){
		$List= DB::Select("SELECT * FROM `tblleave_type`");
		return $List;
	}
	Public function FullStaffDetails($fileNo) {
	$staffDetail=DB::table('tblper')
	->where('fileNo', '=', $fileNo)->first();
	if($staffDetail){return $staffDetail;}
	else
		{
		
		$staffDetail=  DB::select("select '' as 'fileNo',''as 'surname','' as 'first_name','' as 'othernames','' as 'grade','' as 'step','' as 'employee_type'");
		return $staffDetail[0];
		//return json_decode('{"fileNo":"","surname":""}');}//'','first_name'=>'','othernames'=>'','grade'=>'','step'=>'','employee_type'=>'');}
	}	
	}
	Public function LeavePeriod(){
		$returndata=DB::Select("SELECT * FROM `tblleave_period` WHERE `status`=1");
	if($returndata){return $returndata[0]->period;}else{return "";}
	}//
	Public function leaveEntitle($courtid,$grade) {
		$List= DB::Select("SELECT `noOfDays` FROM `tblgrade_leave_assignment` WHERE `courtID`='$courtid' and `grade`='$grade'");
		if($List){return $List[0]->noOfDays;} else {return '0';}
	}
	Public function leaveRemain($totalEntitled,$period,$staffid) {
		$myData= DB::Select("SELECT sum(`noOfDays`) as totalApply FROM `tblstaff_leave` WHERE `staffID`='$staffid' and `period`='$period'");
		return $totalEntitled-$myData[0]->totalApply;
	}
	Public function leaveID($staffid,$releavestaff) {
		$myData= DB::Select("SELECT `id` FROM `tblstaff_leave` WHERE `staffID`='$staffid' and `releavingStaff`='$releavestaff' and `status`= 'Pending'");
		return $myData[0]->id;
	}
	Public function SelfNotification($staffid){
		$List= DB::Select("SELECT *
		FROM `tblnotification` WHERE `staffid`='$staffid'");
		return $List;
	}
	Public function AppliedLeave($leaveid){
		$List= DB::Select("SELECT *
		,(SELECT `leaveType` FROM `tblleave_type` WHERE `tblleave_type`.`id`=`tblstaff_leave`.`leaveType` ) as LeavesType
		,(SELECT concat(`fileNo`,': ',`surname`,' ',`first_name`, ' ',`othernames`) FROM `tblper` WHERE `tblper`.`ID`=`tblstaff_leave`.`staffID`) as principalStaff 
		,(SELECT concat(`fileNo`,': ',`surname`,' ',`first_name`, ' ',`othernames`) FROM `tblper` WHERE `tblper`.`ID`=`tblstaff_leave`.`releavingStaff`) as releaveStaff FROM `tblstaff_leave` WHERE `id`='$leaveid'");
		return $List;
	}
	//function for annual leave application
	Public function ApplyForLeave($leaveid){
		$List= DB::Select("SELECT *
		,(SELECT `leaveType` FROM `tblleave_type` WHERE `tblleave_type`.`id`=`tblstaff_leave`.`leaveType` ) as LeavesType
		,(SELECT concat(`fileNo`,': ',`surname`,' ',`first_name`, ' ',`othernames`) FROM `tblper` WHERE `tblper`.`ID`=`tblstaff_leave`.`staffID`) as principalStaff 
		,(SELECT concat(`fileNo`,': ',`surname`,' ',`first_name`, ' ',`othernames`) FROM `tblper` WHERE `tblper`.`ID`=`tblstaff_leave`.`releavingStaff`) as releaveStaff FROM `tblstaff_leave` WHERE `id`='$leaveid'");
		return $List;
	}
	
	//get annual leave user details after login
	Public function GetUserDetail($uname,$pwd)
	{
	
	    $UserDetail = DB::table('users')
	    ->where('username',$uname)->where('temp_pass',$pwd)
		->leftjoin('tblper', 'tblper.UserID', '=', 'users.id')
		//->leftjoin('tbldepartment', 'tblper.department', '=', 'users.id')
		->select("*",'tblper.ID as UID','users.id','tblper.UserID','tblper.surname','tblper.first_name','tblper.othernames','tblper.department','tblper.grade')
		->first();
	   
		return $UserDetail;
	}
	
	//query annual leave for single user
	Public function DisplayDetail($uname,$pwd){
	
	    $displayDetail = DB::table('users')
	    ->where('username',$uname)->where('temp_pass',$pwd)
        ->where('annual_leave.remove', '=', 1)
        ->where('annual_leave.trail', '=', 1)
	    ->leftjoin('tblper', 'tblper.UserID', '=', 'users.id')
		->leftjoin('annual_leave', 'annual_leave.staffid', '=', 'users.id')
		//->rightjoin('annual_leave_comments','annual_leave.ID','=','annual_leave_comments.leaveID')
        ->select('users.id','tblper.UserID','tblper.department','tblper.surname','tblper.first_name','tblper.othernames','annual_leave.id','annual_leave.year','annual_leave.startdate','annual_leave.enddate',
        'annual_leave.nod', 'annual_leave.statusid','annual_leave.hodstatus','annual_leave.hodcomment','annual_leave.finalapprstatus','annual_leave.finalapprcomment','annual_leave.leavetype',
        'annual_leave.comment','annual_leave.record_status','annual_leave.reapply','annual_leave.reapply_status','annual_leave.datetime','annual_leave.hodreply')
        ->orderby('annual_leave.id','asc')
		->get();
	   
		return $displayDetail;
	}
	

	Public function GetUserGrade($uname,$pwd){
	
	    $getUserGrade= DB::table('users')
	    ->where('username',$uname)->where('temp_pass',$pwd)
	    ->leftjoin('tblper', 'tblper.UserID', '=', 'users.id')
		->select('users.id','tblper.UserID','tblper.department','tblper.surname','tblper.first_name','tblper.othernames','tblper.grade')
		->first();
	   
		return $getUserGrade;
	}
	
       Public function SumUserLeave($uname,$pwd){
	
	        $sumUserLeave= DB::table('users')
	        ->where('username',$uname)->where('temp_pass',$pwd)
            ->where('annual_leave.finalapprstatus', '=', 2)
	       	->leftjoin('annual_leave', 'annual_leave.staffid', '=', 'users.id')
		    ->sum('annual_leave.nod');
	   
		    return $sumUserLeave;
	}
	
	//getting information for head approval
	Public function DisplayDetailForHead($deptid){
	
	    $displayStaffDetail = DB::table('annual_leave')
	    ->where('annual_leave.remove','=',1)
	    ->where('annual_leave.deptid','=',$deptid)
	    //->where('annual_leave.approve','=',1)
	    ->where('submit_application','=',1)
	    ->leftjoin('users', 'annual_leave.staffid', '=', 'users.id')
		->select('users.id','users.name','annual_leave.id','annual_leave.year','annual_leave.startdate','annual_leave.enddate','annual_leave.nod',
		'annual_leave.statusid','annual_leave.hodstatus','annual_leave.hodcomment','annual_leave.finalapprstatus','annual_leave.finalapprcomment',
		'annual_leave.leavetype','annual_leave.comment','annual_leave.record_status','annual_leave.reapply','annual_leave.reapply_status','annual_leave.datetime','annual_leave.staffid','annual_leave.hodreply')
		->orderby('annual_leave.id','asc')
		->get();
	   
		return $displayStaffDetail;
	}
	
	//display hod achives
	Public function DisplayDetailForHeadAll($deptid){
	
	    $displayStaffDetailAll = DB::table('annual_leave')
	    ->where('annual_leave.remove','=',1)
	    ->where('annual_leave.deptid','=',$deptid)
	    ->where('annual_leave.approve','=',0)
	    ->leftjoin('users', 'annual_leave.staffid', '=', 'users.id')
		->select('users.id','users.name','annual_leave.id','annual_leave.year','annual_leave.startdate','annual_leave.enddate','annual_leave.nod',
		'annual_leave.statusid','annual_leave.hodstatus','annual_leave.hodcomment','annual_leave.finalapprstatus','annual_leave.finalapprcomment',
		'annual_leave.leavetype','annual_leave.comment','annual_leave.record_status','annual_leave.reapply','annual_leave.reapply_status','annual_leave.datetime','annual_leave.staffid','annual_leave.hodreply')
		->orderby('annual_leave.id','asc')
		->get();
	   
		return $displayStaffDetailAll;
	}
	
	//getting user comment
	Public function GetComments(){
	
	    $getcomments = DB::table('annual_leave')
	    ->where('annual_leave.grade','>=',15)
	    ->leftjoin('users', 'annual_leave.staffid', '=', 'users.id')
	    ->leftjoin('annual_leave_comments', 'annual_leave.id', '=', 'annual_leave_comments.leaveID')
		->get();
	   
		return $getcomments;
	}

   //getting information for final admin approval when grade level is less than or equal 14
	Public function FinalApprovalAdmin(){
	
	    $finalapproval= DB::table('annual_leave')
	    ->where('annual_leave.remove','=',1)
	    ->where('annual_leave.grade','<=',14)
	    ->where('annual_leave.approve','=',1)
	    ->leftjoin('users', 'annual_leave.staffid', '=', 'users.id')
		->join('tbldepartment','annual_leave.hodid','=','tbldepartment.head')
		->select('users.id','users.name','annual_leave.id','annual_leave.year','annual_leave.startdate','annual_leave.enddate','annual_leave.nod',
		'annual_leave.statusid','annual_leave.hodstatus','annual_leave.hodcomment','annual_leave.finalapprstatus','annual_leave.finalapprcomment','annual_leave.leavetype',
		'annual_leave.comment','annual_leave.reapply','annual_leave.reapply_status','annual_leave.datetime','annual_leave.staffid','annual_leave.hodreply')
		->orderby('annual_leave.id','asc')
		->get();
	   
		return $finalapproval;
	}
	
	//display achives for admin
	Public function FinalApprovalAdminAll(){
	
	    $finalapprovalAll= DB::table('annual_leave')
	    ->where('annual_leave.remove','=',1)
	    ->where('annual_leave.grade','<=',14)
	    ->where('annual_leave.approve','=',0)
	    ->leftjoin('users', 'annual_leave.staffid', '=', 'users.id')
		->join('tbldepartment','annual_leave.hodid','=','tbldepartment.head')
		->select('users.id','users.name','annual_leave.id','annual_leave.year','annual_leave.startdate','annual_leave.enddate','annual_leave.nod',
		'annual_leave.statusid','annual_leave.hodstatus','annual_leave.hodcomment','annual_leave.finalapprstatus','annual_leave.finalapprcomment','annual_leave.leavetype',
		'annual_leave.comment','annual_leave.reapply','annual_leave.reapply_status','annual_leave.datetime','annual_leave.staffid','annual_leave.hodreply')
		->orderby('annual_leave.id','asc')
		->get();
	   
		return $finalapprovalAll;
	}
	
	//getting information for final admin approval when grade level is greater than or equal 15
	Public function FinalApprovalES(){
	    
	    $finalapprovalES= DB::table('annual_leave')
	    ->where('annual_leave.remove','=',1)
	    ->where('annual_leave.grade','>=',15)
	    ->where('annual_leave.approve','=',1)
	    ->leftjoin('users', 'annual_leave.staffid', '=', 'users.id')
		->join('tbldepartment','annual_leave.hodid','=','tbldepartment.head')
		//->join('annual_leave_comments', 'annual_leave.staffid', '=', 'annual_leave_comments.userID')
		->select('users.id','users.name','annual_leave.id','annual_leave.year','annual_leave.startdate','annual_leave.enddate','annual_leave.nod',
		'annual_leave.statusid','annual_leave.hodstatus','annual_leave.hodcomment','annual_leave.finalapprstatus','annual_leave.finalapprcomment','annual_leave.leavetype',
		'annual_leave.comment','annual_leave.reapply','annual_leave.reapply_status','annual_leave.datetime','annual_leave.staffid','annual_leave.hodreply')
		->orderby('annual_leave.id','asc')
		->get();
	   
		return $finalapprovalES;
	}
	
	//display archives for ES
	Public function FinalApprovalESAll(){
	    
	    $finalapprovalESAll= DB::table('annual_leave')
	    ->where('annual_leave.remove','=',1)
	    ->where('annual_leave.grade','>=',15)
	    ->where('annual_leave.approve','=',0)
	    ->leftjoin('users', 'annual_leave.staffid', '=', 'users.id')
		->join('tbldepartment','annual_leave.hodid','=','tbldepartment.head')
		//->join('annual_leave_comments', 'annual_leave.staffid', '=', 'annual_leave_comments.userID')
		->select('users.id','users.name','annual_leave.id','annual_leave.year','annual_leave.startdate','annual_leave.enddate','annual_leave.nod',
		'annual_leave.statusid','annual_leave.hodstatus','annual_leave.hodcomment','annual_leave.finalapprstatus','annual_leave.finalapprcomment','annual_leave.leavetype',
		'annual_leave.comment','annual_leave.reapply','annual_leave.reapply_status','annual_leave.datetime','annual_leave.staffid','annual_leave.hodreply')
		->orderby('annual_leave.id','asc')
		->get();
	   
		return $finalapprovalESAll;
	}
	
	Public function Comments($appid, $staffid){
	    
	    $comments= DB::table('annual_leave_comments')
	    ->where('annual_leave_comments.leaveID','=',$appid)
	    ->where('annual_leave_comments.staffID','=',$staffid)
	    //->leftjoin('users', 'annual_leave.staffid', '=', 'users.id')
		//->join('tbldepartment','annual_leave.hodid','=','tbldepartment.head')
		//->select('users.id','users.name','annual_leave.id','annual_leave.year','annual_leave.startdate','annual_leave.enddate','annual_leave.nod',
		//'annual_leave.statusid','annual_leave.hodstatus','annual_leave.hodcomment','annual_leave.finalapprstatus','annual_leave.finalapprcomment','annual_leave.leavetype',
		//'annual_leave.comment','annual_leave.reapply','annual_leave.reapply_status','annual_leave.datetime','annual_leave.staffid')
		//->orderby('annual_leave.id','asc')
		->first();
	   
		return $comments;
	}
	
	Public function LeavesQuery($period,$court,$division,$department,$status){
	$qcourt=1;
	if($court!=''){$qcourt="exists(SELECT * FROM `tblper` WHERE `courtID`='$court' and `tblper`.`ID`=`tblstaff_leave`.`staffID`)";}
	$qdivision=1;
	if($division!=''){$qdivision="exists(SELECT * FROM `tblper` WHERE `divisionID`='$division' and `tblper`.`ID`=`tblstaff_leave`.`staffID`)";}
	$qdepartment=1;
	if($department!=''){$qdepartment="exists(SELECT * FROM `tblper` WHERE `section`='$department' and `tblper`.`ID`=`tblstaff_leave`.`staffID`)";}
	$qstatus=1;
	if($status!=''){$qstatus="`tblstaff_leave`.`status`='$status'";}
	$List= DB::Select("SELECT *
	,(SELECT concat(`fileNo`,': ',`surname`,' ',`first_name`, ' ',`othernames`) FROM `tblper` WHERE `tblper`.`ID`=`tblstaff_leave`.`staffID`) as principalStaff 
	,(SELECT `leaveType` FROM `tblleave_type` WHERE `tblleave_type`.`id`=`tblstaff_leave`.`leaveType` ) as LeavesType
	 ,(SELECT CONCAT(`fileNo`,': ',`surname`,' ',`first_name`,' ',`othernames`) FROM `tblper` WHERE `tblper`.`ID`=`tblstaff_leave`.`releavingStaff`) as Rstaff 
	  FROM `tblstaff_leave` WHERE `period`='$period' 
	and $qcourt and $qdivision and $qdepartment and $qstatus");
	return $List;
	}
	Public function NewFileNo($courtid){
	$myData= DB::Select("SELECT max(`fileNo`) as LastFileNo FROM `tblper` WHERE `courtID` ='$courtid'");
	//return "SELECT max(`fileNo`) as LastFileNo FROM `tblper` WHERE `courtID` ='$courtid'";
	if($myData[0]->LastFileNo==''){ 
	$myData1= DB::Select("SELECT * FROM `tbl_court` WHERE `id`='$courtid'");
	return $myData1[0]->courtAbbr.'/P/00001';
	}
	
	$LastFileNo =$myData[0]->LastFileNo; 
	$arr = explode("/", $LastFileNo);
	$newcode=$arr[2]+1;
	while(strlen($newcode)<5)
            {$newcode="0".$newcode;}
	return $arr[0].'/'.$arr[1].'/'.$newcode;
	}
	Public function DivisionStaffList($court,$division){
	$myData= DB::SELECT ("SELECT * FROM `tblper` WHERE `courtID`='$court' and `divisionID`='$division' and `staff_status`=1");
            
            return $myData;
	}
	
	Public function FStaffCV($fileNo) {
	$cvdata=  DB::select("SELECT *,'1' as submittype  FROM `tblcv` WHERE `fileNo`='$fileNo'");
	
	if($cvdata){return $cvdata[0];}
	else
		{
		
		$cvdata=  DB::select("select '0' as 'ugv','0'as 'nicnCoop','0' as 'motorAdv','0' as 'bicycleAdv','0' as 'ctlsLab','0' as 'ctlsFed','0' as 'fedHousing'
		,'0' as 'hazard','0' as 'callDuty','0' as 'shiftAll','0' as 'phoneCharges','0' as 'surcharge','0' as 'pa_deduct' ,'0' as 'submittype'");
		return $cvdata[0];
		
	}	
	}
	Public function PayrollActivePeriod($court) {
	$cvdata=  DB::select("SELECT * FROM `tblactivemonth` WHERE `courtID`='$court'");
	
	if($cvdata){return $cvdata[0];}
	else
		{
		
		$cvdata=  DB::select("select '' as 'month','' as 'year'");
		return $cvdata[0];
		
	}	
	}
	Public function PayrollStaffParameter($court,$division){
	$qdivision=1;
	if ($division!='All'){$qdivision="`tblper`.`divisionID`='$division'";}
	
		$List= DB::Select(" SELECT * ,`tblper`.`ID` as `staffid` FROM `tblper` join `basicsalary` 
		on `basicsalary`.`employee_type`=`tblper`.`employee_type` 
		and `basicsalary`.`courtID`=`tblper`.`courtID` and `basicsalary`.`grade`=`tblper`.`grade` 
		and `basicsalary`.`step`=`tblper`.`step` and `basicsalary`.`employee_type`=`tblper`.`employee_type`
 		WHERE `tblper`.`courtID`= '$court' and $qdivision and `staff_status`='1'and `fileNo`<>''");
		return $List;
	}
	Public function PayrollStaffParameterCon($court,$division,$bank){
	$qdivision=1;
	if ($division!='All'){$qdivision="`tblper`.`divisionID`='$division'";}
	$qbank=1;
	if ($bank!='All'){$qbank="`tblper`.`bankID`='$bank'";}
	//die(" SELECT * FROM `tblper` WHERE `tblper`.`courtID`= '$court' and $qdivision and `staff_status`='1' and `fileNo`<>''");
	//$List= DB::Select(" SELECT * FROM `tblper` WHERE `tblper`.`courtID`= '$court' and $qdivision and `staff_status`='1' and `fileNo`<>''");
	//dd($List);
	
		$List= DB::Select(" SELECT *,`tblper`.`ID` as `staffid` FROM `tblper` join `basicsalaryconsolidated` 
		on `basicsalaryconsolidated`.`employee_type`=`tblper`.`employee_type` 
		and `basicsalaryconsolidated`.`courtID`=`tblper`.`courtID` and `basicsalaryconsolidated`.`grade`=`tblper`.`grade` 
		and `basicsalaryconsolidated`.`step`=`tblper`.`step` and `basicsalaryconsolidated`.`employee_type`=`tblper`.`employee_type`
 		WHERE `tblper`.`courtID`= '$court' and $qdivision and $qbank and  `staff_status`='1' ");
		return $List;
		$List= DB::Select(" SELECT *,`tblper`.`ID` as `staffid` FROM `tblper` join `basicsalaryconsolidated` 
		on `basicsalaryconsolidated`.`employee_type`=`tblper`.`employee_type` 
		and `basicsalaryconsolidated`.`courtID`=`tblper`.`courtID` and `basicsalaryconsolidated`.`grade`=`tblper`.`grade` 
		and `basicsalaryconsolidated`.`step`=`tblper`.`step` and `basicsalaryconsolidated`.`employee_type`=`tblper`.`employee_type`
 		WHERE `tblper`.`courtID`= '$court' and $qdivision and `staff_status`='1' and `fileNo`<>''");
		return $List;
	}
	Public function DesignationList2($court,$department){
	$qdepart=1;
	if ($department!=''){$qdepart="`departmentID`='$department'";}
		$List= DB::Select("SELECT *,
		(SELECT `court_name`  FROM `tbl_court` WHERE `tbl_court`.`id`=`tbldesignation`.`courtID` )  as court_name
		,(SELECT `department` FROM `tbldepartment` WHERE `tbldepartment`.`id`=`tbldesignation`.`departmentID`) as department
		FROM `tbldesignation` WHERE `tbldesignation`.`courtID`='$court' and $qdepart ORDER BY  `tbldesignation`.`grade` desc ");
		return $List;
	}
	
	Public function ConfirmGrade2($level, $department, $designation,$court){
	
		$confir= DB::Select("SELECT * FROM `tbldesignation` WHERE `departmentID` ='$department' && `courtID`='$court' AND  `grade`='$level' OR `designation`='$designation' ");
	if(($confir)){return true;}else{return false;}
	}
	
	
	Public function ConfirmPayrollperiod($court,$division,$year,$month){
	$qdivision=1;
	if ($division!='All'){$qdivision="`divisionID`='$division'";}
		$confir= DB::Select("SELECT * FROM `tblpayment` WHERE `courtID`='$court' and $qdivision and `year`='$year' and `month`='$month'");
		if(($confir)){return true;}else{return false;}
	}
	Public function ConfirmConsolidatedPayrollperiod($court,$division,$year,$month,$bank){
	$qdivision=1;
	if ($division!='All'){$qdivision="`divisionID`='$division'";}
	$qbank=1;
	if ($bank!='All'){$qbank="`bank`='$bank'";}
		$confir= DB::Select("SELECT * FROM `tblpayment_consolidated` WHERE `courtID`='$court' and $qdivision and `year`='$year' and `month`='$month' and $qbank");
		if(($confir)){return true;}else{return false;}
	}
	
	Public function ConfirmCheckAuditCon($court,$division,$year,$month){
	$qdivision=1;
	if ($division!='All'){$qdivision="`divisionID`='$division'";}
	
		$confir= DB::Select("SELECT * FROM `tblpayment_consolidated` WHERE `courtID`='$court' and $qdivision and `year`='$year' and `month`='$month' and `payment_status`>1");
		if(($confir)){return true;}else{return false;}
	}
	Public function ConfirmCheckLockCon($court,$division,$year,$month){
	$qdivision=1;
	if ($division!='All'){$qdivision="`divisionID`='$division'";}
	
		$confir= DB::Select("SELECT * FROM `tblpayment_consolidated` WHERE `courtID`='$court' and $qdivision and `year`='$year' and `month`='$month' and `salary_lock`=1");
		//dd("SELECT * FROM `tblpayment_consolidated` WHERE `courtID`='$court' and $qdivision and `year`='$year' and `month`='$month' and `salary_lock`=1");
		if(($confir)){return true;}else{return false;}
	}
	Public function ConfirmCheckAudit($court,$division,$year,$month){
	$qdivision=1;
	if ($division!='All'){$qdivision="`divisionID`='$division'";}
		$confir= DB::Select("SELECT * FROM `tblpayment` WHERE `courtID`='$court' and $qdivision and `year`='$year' and `month`='$month' and `payment_status`>1");
		if(($confir)){return true;}else{return false;}
	}
	Public function DeletePayrollperiod($court,$division,$year,$month){
	$qdivision=1;
	if ($division!='All'){$qdivision="`divisionID`='$division'";}
	DB::DELETE ("DELETE FROM `tblpayment` WHERE `courtID`='$court' and $qdivision and `year`='$year' and `month`='$month'");
		
	}
	Public function DeleteConsolidatedPayrollperiod($court,$division,$year,$month,$bank){
	$qdivision=1;
	if ($division!='All'){$qdivision="`divisionID`='$division'";}
	$qbank=1;
	if ($bank!='All'){$qbank=" (exists(SELECT null FROM `tblper` WHERE `tblper`.`bankID`='$bank' and `tblper`.`ID`=`tblpayment_consolidated`.`staffid`))";}
	DB::DELETE ("DELETE FROM `tblpayment_consolidated` WHERE `courtID`='$court' and $qdivision and `year`='$year' and `month`='$month' and $qbank");
		
	}
	Public function DeletePayrollArrearperiod($court,$division,$year,$month,$bank){
	$qdivision=1;
	if ($division!='All'){$qdivision="`divisionID`='$division'";}
	$qbank=1;
	if ($bank!='All'){$qbank=" ( exists(SELECT null FROM `tblper` WHERE `tblper`.`bankID`='$bank' and `tblper`.`ID`=`tblarrears`.`staffid`))";}
	DB::DELETE ("DELETE FROM `tblarrears` WHERE `courtID`='$court' and $qdivision and `year`='$year' and `month`='$month' and $qbank");
		
	}
	Public function DeletePayrollOverdueArrearperiod($court,$division,$year,$month,$bank){
	$qdivision=1;
	if ($division!='All'){$qdivision="`divisionID`='$division'";}
	$qbank=1;
	if ($bank!='All'){$qbank=" ( exists(SELECT null FROM `tblper` WHERE `tblper`.`bankID`='$bank' and `tblper`.`ID`=`tblarrears_overdue`.`staffid`))";}
	DB::DELETE ("DELETE FROM `tblarrears_overdue` WHERE `courtID`='$court' and $qdivision and `year`='$year' and `month`='$month' and $qbank");
		
	}
	Public function DeletePayrollStaffCV($court,$division,$year,$month,$bank){
	$qdivision=1;
	if ($division!='All'){$qdivision="`divisionID`='$division'";}
	$qbank=1;
	if ($bank!='All'){$qbank=" ( exists(SELECT null FROM `tblper` WHERE `tblper`.`bankID`='$bank' and `tblper`.`ID`=`tblotherEarningDeduction`.`staffid`))";}
	DB::DELETE ("DELETE FROM `tblotherEarningDeduction` WHERE `courtID`='$court' and $qdivision and `year`='$year' and `month`='$month' and $qbank");
	}
	
	Public function ArrearComputation($staffid,$year,$month){
	$Earn=0;
	$Deduction=0;
	$checkArrear= DB::select("SELECT * FROM `tblstaff_for_arrears` WHERE ((`month_payment`='$month' and `year_payment`='$year') or `payment_status`=0) 
	 and `staffid`='$staffid'");
	if($checkArrear)
	{
	
	$checkArrearID=$checkArrear[0]->ID;
	$oldGrade=$checkArrear[0]->old_grade;
	$newGrade=$checkArrear[0]->new_grade;
	$OldStep=$checkArrear[0]->old_step;
	$newStep=$checkArrear[0]->new_step;
	$oldEmploymentType=$checkArrear[0]->oldEmploymentType;
	$newEmploymentType=$checkArrear[0]->newEmploymentType;
	$court=$checkArrear[0]->courtID;
	$division=$checkArrear[0]->divisionID;
	$duedate=$checkArrear[0]->due_date;
	
	if($oldGrade=="0" && $OldStep=="0" ){$oldpay= DB::select("SELECT 0 as 'amount' ,0 as 'tax',0 as 'peculiar', 0 as 'leave_bonus', 0 as 'pension', 0 as 'unionDues',0 as 'utility',0 as 'nhf',0 as 'transport',0 as 'housing' ,0 as 'servant',0 as 'meal',0 as 'driver',0 as 'furniture'");}
	else{$oldpay= DB::select("SELECT * FROM `basicsalary` WHERE `employee_type`='$oldEmploymentType' and `courtID`='$court' and `grade`='$oldGrade' and `step`='$OldStep'");}
	$newpay= DB::select("SELECT * FROM `basicsalary` WHERE `employee_type`='$newEmploymentType' and `courtID`='$court' and `grade`='$newGrade' and `step`='$newStep'");
	DB::table('tblarrears')->insert(array(
			'staffid'    	=> $staffid,
			'fileNo'    	=> $fileNo,
                        'courtID'    	=> $court,
                        'divisionID'    	=> $division,
			'month'	    	=> $month,
			'year'    	=> $year,
			'oldGrade'    	=> $oldGrade,
			'OldStep'    => $OldStep,
			'newGrade'    	=> $newGrade,
			'newStep'    	=> $newStep,
			'oldEmploymentType'    	=> $oldEmploymentType,
			'newEmploymentType'    	=> $newEmploymentType,
			'oldBasic'    => $oldpay[0]->amount,
			'newBasic'    	=> $newpay[0]->amount,
			'oldTax'    	=> $oldpay[0]->tax,
			'newTax'      => $newpay[0]->tax,
			'oldPeculiar'  => $oldpay[0]->peculiar,
			'newPeculiar'    	=> $newpay[0]->peculiar,
			'oldLeave_bonus'    	=> $oldpay[0]->leave_bonus,
                        'newLeave_bonus'  => $newpay[0]->leave_bonus,
			'oldPension'    	=> $oldpay[0]->pension,
			'newPension'    	=> $newpay[0]->pension,
			'oldNhf'    	=> $oldpay[0]->nhf,
                        'newNhf'  => $newpay[0]->nhf,
			'oldUnionDues'    	=> $oldpay[0]->unionDues,
			'newUnionDues'    	=> $newpay[0]->unionDues,
			'oldUtility'    	=> $oldpay[0]->utility,
			'newUtility'    	=> $newpay[0]->utility,
			'oldTransport'    	=> $oldpay[0]->transport,
			'newTransport'    	=> $newpay[0]->transport,
			'oldHousing'    	=> $oldpay[0]->housing,
			'newHousing'    	=> $newpay[0]->housing,
			
			
			'oldServant'    	=> $oldpay[0]->servant,
			'newServant'    	=> $newpay[0]->servant,
			'oldMeal'    	=> $oldpay[0]->meal,
			'newMeal'    	=> $newpay[0]->meal,
			'oldDriver'    	=> $oldpay[0]->driver,
			'newDriver'    	=> $newpay[0]->driver,
			'oldFurniture'    	=> $oldpay[0]->furniture,
			'newFurniture'    	=> $newpay[0]->furniture,
			
			
			'dueDate'    	=> $duedate,
			'date'    	=> $data=date("Y-m-d H:i:s"),
		));
	$Earn1=($newpay[0]->amount -$oldpay[0]->amount)+($newpay[0]->peculiar-$oldpay[0]->peculiar)+($newpay[0]->leave_bonus-$oldpay[0]->leave_bonus)
		+($newpay[0]->utility-$oldpay[0]->utility)+($newpay[0]->transport-$oldpay[0]->transport)+($newpay[0]->housing-$oldpay[0]->housing)+($newpay[0]->servant-$oldpay[0]->servant)+($newpay[0]->meal-$oldpay[0]->meal)+($newpay[0]->driver-$oldpay[0]->driver)+($newpay[0]->furniture-$oldpay[0]->furniture);
	$Deduction1=($newpay[0]->tax-$oldpay[0]->tax)+($newpay[0]->pension-$oldpay[0]->pension)+($newpay[0]->nhf-$oldpay[0]->nhf)+($newpay[0]->unionDues-$oldpay[0]->unionDues);
	$activemonth = date("n", strtotime($month));
	$dif = $this->dateDiff($year."-".$activemonth."-1", $checkArrear[0]->due_date);
	$Earn=$Earn1*$dif['months']+($Earn1*$dif['days'])/$dif['days_of_month'];
	$Deduction=$Deduction1*$dif['months']+($Deduction1*$dif['days'])/$dif['days_of_month'];
	DB::table('tblstaff_for_arrears')->where('ID', $checkArrearID)->update(array(
			'payment_status'	=> 1,
			'year_payment'    	=> $year,
			'month_payment'    	=> $month,
			
		));
	}
	
	
	$arreardata=  DB::select("select $Earn as 'Earn',$Deduction as 'Deduction'");
	return $arreardata[0];
	}


Public function OverdueArrear_old_new($staffid,$year,$month){
	$Earn=0;
	$Deduction=0;
	$m_basic=0;
	$m_peculiar=0;
	$m_tax=0;
	$m_pension=0;
	$m_nhf=0;
	$m_unionDues=0;

	$checkArrear= DB::select("SELECT * FROM `tblstaff_for_arrears_old_new` WHERE ((`month_payment`='$month' and `year_payment`='$year') or `payment_status`=0) 
	 and `staffid`='$staffid'");
	foreach ($checkArrear as $over_d)
	{
	
	$checkArrearID=$over_d->ID;
	$oldGrade=$over_d->grade;
	$newGrade=$over_d->grade;
	$OldStep=$over_d->step;
	$newStep=$over_d->step;
	$oldEmploymentType=$over_d->employmentType;
	$newEmploymentType=$over_d->employmentType;
	$duedate=$over_d->due_date;
	
	if($oldGrade=="0" && $OldStep=="0" ){$oldpay= DB::select("SELECT 0 as 'amount' ,0 as 'tax',0 as 'peculiar', 0 as 'leave_bonus', 0 as 'pension', 0 as 'unionDues',0 as 'utility',0 as 'nhf',0 as 'transport',0 as 'housing' ,0 as 'servant',0 as 'meal',0 as 'driver',0 as 'furniture'");}
	else{$oldpay= DB::select("SELECT * FROM `basicsalaryconsolidated` WHERE `employee_type`='$oldEmploymentType' and `grade`='$oldGrade' and `step`='$OldStep'");}
	$newpay= DB::select("SELECT * FROM `basicsalaryconsolidated_new` WHERE `employee_type`='$newEmploymentType'  and `grade`='$newGrade' and `step`='$newStep'");
	DB::table('tblarrears_old_new')->insert(array(
			'staffid'    	    => $staffid,
			'month'	    	    => $month,
			'year'    	        => $year,
			'oldGrade'    	    => $oldGrade,
			'OldStep'           => $OldStep,
			'newGrade'    	    => $newGrade,
			'newStep'    	    => $newStep,
			'oldEmploymentType' => $oldEmploymentType,
			'newEmploymentType' => $newEmploymentType,
			'oldBasic'          => $oldpay[0]->amount,
			'newBasic'    	    => $newpay[0]->amount,
			'oldTax'    	    => $oldpay[0]->tax,
			'newTax'            => $newpay[0]->tax,
			'oldPeculiar'       => $oldpay[0]->peculiar,
			'newPeculiar'    	=> $newpay[0]->peculiar,
			'oldLeave_bonus'    => $oldpay[0]->leave_bonus,
            'newLeave_bonus'    => $newpay[0]->leave_bonus,
			'oldPension'    	=> $oldpay[0]->pension,
			'newPension'    	=> $newpay[0]->pension,
			'oldNhf'    	    => $oldpay[0]->nhf,
            'newNhf'            =>$newpay[0]->nhf,
			'oldUnionDues'    	=> $oldpay[0]->unionDues,
			'newUnionDues'    	=> $newpay[0]->unionDues,
			'oldUtility'    	=> $oldpay[0]->utility,
			'newUtility'    	=> $newpay[0]->utility,
			'oldTransport'    	=> $oldpay[0]->transport,
			'newTransport'    	=> $newpay[0]->transport,
			'oldHousing'    	=> $oldpay[0]->housing,
			'newHousing'    	=> $newpay[0]->housing,
			'oldServant'    	=> $oldpay[0]->servant,
			'newServant'    	=> $newpay[0]->servant,
			'oldMeal'    	    => $oldpay[0]->meal,
			'newMeal'    	    => $newpay[0]->meal,
			'oldDriver'    	    => $oldpay[0]->driver,
			'newDriver'    	    => $newpay[0]->driver,
			'oldFurniture'    	=> $oldpay[0]->furniture,
			'newFurniture'    	=> $newpay[0]->furniture,	
			'dueDate'       	=> $duedate,
			'date'    	        => $data=date("Y-m-d H:i:s"),
		));
	$Earn1=($newpay[0]->amount -$oldpay[0]->amount)+($newpay[0]->peculiar-$oldpay[0]->peculiar)+($newpay[0]->leave_bonus-$oldpay[0]->leave_bonus)
		+($newpay[0]->utility-$oldpay[0]->utility)+($newpay[0]->transport-$oldpay[0]->transport)+($newpay[0]->housing-$oldpay[0]->housing)+($newpay[0]->servant-$oldpay[0]->servant)+($newpay[0]->meal-$oldpay[0]->meal)+($newpay[0]->driver-$oldpay[0]->driver)+($newpay[0]->furniture-$oldpay[0]->furniture);
	$basic=($newpay[0]->amount -$oldpay[0]->amount);
	$peculiar=($newpay[0]->peculiar-$oldpay[0]->peculiar);
	$Deduction1=($newpay[0]->tax-$oldpay[0]->tax)+($newpay[0]->pension-$oldpay[0]->pension)+($newpay[0]->nhf-$oldpay[0]->nhf)+($newpay[0]->unionDues-$oldpay[0]->unionDues);
	$tax=($newpay[0]->tax-$oldpay[0]->tax);
	$pension=($newpay[0]->pension-$oldpay[0]->pension);
	$nhf=($newpay[0]->nhf-$oldpay[0]->nhf);
	$unionDues=($newpay[0]->unionDues-$oldpay[0]->unionDues);
	$dif = $this->dateDiff($over_d->overdueDate, $over_d->due_date);
	$Earn+=$Earn1*($dif['months'])+($Earn1*$dif['days'])/$dif['days_of_month'];
	$Deduction+=$Deduction1*($dif['months'])+($Deduction1*$dif['days'])/$dif['days_of_month'];
	$m_basic+=$basic*($dif['months'])+($basic*$dif['days'])/$dif['days_of_month'];
		
	$m_peculiar+=$peculiar*($dif['months'])+($peculiar*$dif['days'])/$dif['days_of_month'];
	$m_tax+=$tax*($dif['months'])+($tax*$dif['days'])/$dif['days_of_month'];
	$m_pension+=$pension*($dif['months'])+($pension*$dif['days'])/$dif['days_of_month'];
	$m_nhf+=$nhf*($dif['months'])+($nhf*$dif['days'])/$dif['days_of_month'];
	$m_unionDues+=$unionDues*($dif['months'])+($unionDues*$dif['days'])/$dif['days_of_month'];
	DB::table('tblstaff_for_arrears_old_new')->where('ID', $checkArrearID)->update(array(
			'payment_status'	=> 1,
			'year_payment'    	=> $year,
			'month_payment'    	=> $month,
		));
	}
	$arreardata=  DB::select("select $Earn as 'Earn',$Deduction as 'Deduction',$m_basic as 'basic',$m_peculiar as 'peculiar',$m_tax as 'tax',$m_pension as 'pension',$m_nhf as 'nhf',$m_unionDues as 'unionDues'");
	return $arreardata[0];
}


	
function dateDiff($date2, $date1)
  {
    list($year2, $mth2, $day2) = explode("-", $date2);
    list($year1, $mth1, $day1) = explode("-", $date1);
    if ($year1 > $year2) dd('Invalid Input - dates do not match');
    $days_month = 0;
    $days_month = cal_days_in_month(CAL_GREGORIAN, $mth1, $year1);
    $day_diff = 0;

    if($year2 == $year1){
      $mth_diff = $mth2 - $mth1;
    }
    else{
      $yr_diff = $year2 - $year1;
      $mth_diff = (12 * $yr_diff) - $mth1 + $mth2;
    }
    if($day1 > 1){
      $mth_diff--;
      //dd($mth1.",".$year1);
      $day_diff = $days_month - $day1 + 1;
    }

    $result = array('months'=>$mth_diff, 'days'=> $day_diff, 'days_of_month'=>$days_month);
    return($result);
  } //end of
	
Public function EmployeeTypeList(){
		$List= DB::Select("SELECT * FROM `tblemployment_type` WHERE `active`=1");
		return $List;
	}
	Public function OrderList(){
		$List= DB::Select("SELECT * FROM `tblstaffQueryOrder`");
		return $List;
	}
	Public function DesignationList3($court,$department){
	$qdepartment="1";
	if(!$department=''){$qdepartment=" `departmentID`='$department'";}
		$DepartmentList= DB::Select("SELECT * FROM `tbldesignation` WHERE `tbldesignation`.`courtID`='$court' and $qdepartment order by `grade`");
		return $DepartmentList;
	}
	
	
	Public function QueryStaffReportFxter($court,$division,$department,$designation, $grade,$gender,$fromdate,$todate,$type,$orderlist){
	$qorderlist="";
	if($orderlist=="fileNo"){$qorderlist=",`fileNo` ASC";}
	if($orderlist=="grade"){$qorderlist=",`grade` DESC";}
	if($orderlist=="appointment_date"){$qorderlist=",`appointment_date` ASC";}
	if($orderlist=="dob"){$qorderlist=",`dob` ASC";}
	$qtype= " 1 ";
	if($type!=''){$qtype="`employee_type`='$type'";}
	$qdesignation=1;
	if($designation!=''){$qdesignation=" exists (select null from tbldesignation where tbldesignation.departmentID=tblper.departmentID and tbldesignation.grade=tblper.grade and tbldesignation.id='$designation')";}
	$qcourt=1;
	if($court!=''){$qcourt="`courtID`='$court'";}
	
	$qdivision=1;
	if($division!=''){$qdivision="`divisionID`='$division'";}
	
	$qsection=1;
	if($department!=''){$qsection="`department`='$department'";}
	
	$qgrade=1;
	if($grade!=''){$qgrade="`grade`='$grade'";}

	$qgender=1;
	if($gender!=''){$qgender="`gender`='$gender'";}
	
	$qgender=1;
	if($gender!=''){$qgender="`gender`='$gender'";}
	$qualication=" (SELECT GROUP_CONCAT((SELECT  tblqualification.qualification from tblqualification where tblqualification.ID= tbleducations.degreequalification),'( ',
	(Select tblcertificateHeld.certHeld from tblcertificateHeld where tblcertificateHeld.id=tbleducations.certificateheld ),')-',DATE_FORMAT(tbleducations.schoolto,'%Y') ORDER BY  tbleducations.`categoryID` ASC SEPARATOR ', ') FROM `tbleducations` WHERE tbleducations.fileNo=tblper.fileNo  ) as qualifications ";
	$qualication=" 'nil' as qualifications";
	$timedate= "(DATE_FORMAT(`appointment_date`,'%Y-%m-%d') BETWEEN '$fromdate' AND '$todate')";
	$timedate=1 ;
	
	
	$List= DB::Select("SELECT *
	,(SELECT concat(`surname`,' ',`first_name`, ' ',`othernames`) ) as StaffName 
	,(SELECT  `designation` FROM `tbldesignation` WHERE `tbldesignation`.`id`=`tblper`.`Designation`  ) as designations
	,(SELECT `division` FROM `tbldivision` WHERE tbldivision.divisionID=tblper.divisionID ) as divisions
	,(SELECT `tblstates`.`State` FROM `tblstates` WHERE `tblstates`.`StateID`=`tblper`.`stateID` ) as State
	
	,(SELECT `lga`.`lga` FROM `lga` WHERE `lga`.`lgaId`=tblper.lgaID ) as LGA
	,(SELECT `tblmaritalStatus`.`marital_status` FROM `tblmaritalStatus` WHERE `tblmaritalStatus`.`ID`=tblper.maritalstatus) as MStatus

	,$qualication
	  FROM `tblper` WHERE $qcourt and $qdivision and $qsection and $qgrade  and $qdesignation and $qgender and $timedate and $qtype and `employee_type`<>2  and `staff_status`=1 and 
	  `tblper`.`fileNo`<>'' ORDER BY   `tblper`.`rank` DESC 
	  ,(case when `tblper`.`fileNo`<>'' then 1 else 2 end) asc
	  ,(case when LEFT(`tblper`.`fileNo`, 5)='NJC/P' then 1 else 2 end) asc
	  ,`tblper`.`fileNo` 
	  
	  ,`tblper`.`grade` DESC ,DATE_FORMAT(`tblper`.`date_present_appointment`,'%Y-%m-%d') ASC,DATE_FORMAT(`tblper`.`appointment_date`,'%Y-%m-%d') ASC");
	
	return $List;
	
	
	}
	Public function QueryStaffReport($court,$division,$department,$designation, $grade,$gender,$fromdate,$todate,$type,$orderlist){
	
	
	$qorderlist="";
	if($orderlist=="fileNo"){$qorderlist=",`fileNo` ASC";}
	if($orderlist=="grade"){$qorderlist=",`grade` DESC";}
	if($orderlist=="appointment_date"){$qorderlist=",`appointment_date` ASC";}
	if($orderlist=="dob"){$qorderlist=",`dob` ASC";}
	$qtype= " 1 ";
	if($type!=''){$qtype="`employee_type`='$type'";}
	$qdesignation=1;
	//if($designation!=''){$qdesignation=" exists (select null from tbldesignation where tbldesignation.departmentID=tblper.departmentID and tbldesignation.grade=tblper.grade and tbldesignation.id='$designation')";}
	$qcourt=1;
	if($court!=''){$qcourt="`courtID`='$court'";}
	
	$qdivision=1;
	if($division !=''){
        $qdivision="`divisionID`='$division'";
    }
	
	$qsection=1;
	if($department!=''){$qsection="`department`='$department'";}
	
	$qgrade=1;
	if($grade!=''){$qgrade="`grade`='$grade'";}

	$qgender=1;
	if($gender!=''){$qgender="`gender`='$gender'";}
	
	$qgender=1;
	if($gender!=''){$qgender="`gender`='$gender'";}
	$qualication=" (SELECT GROUP_CONCAT((SELECT  tblqualification.qualification from tblqualification where tblqualification.ID= tbleducations.degreequalification),'( ',
	(Select tblcertificateHeld.certHeld from tblcertificateHeld where tblcertificateHeld.id=tbleducations.certificateheld ),')-',DATE_FORMAT(tbleducations.schoolto,'%Y') ORDER BY  tbleducations.`categoryID` ASC SEPARATOR ', ') FROM `tbleducations` WHERE tbleducations.fileNo=tblper.fileNo  ) as qualifications ";
	$qualication=" 'nil' as qualifications";
	$timedate= "(DATE_FORMAT(`appointment_date`,'%Y-%m-%d') BETWEEN '$fromdate' AND '$todate')";
	$timedate=1 ;
	
	
	$List= DB::Select("SELECT *
	,(SELECT concat(`surname`,' ',`first_name`, ' ',`othernames`) ) as StaffName 
	,(SELECT  `designation` FROM `tbldesignation` WHERE `tbldesignation`.`id`=`tblper`.`Designation`  ) as designations
	,(SELECT `division` FROM `tbldivision` WHERE tbldivision.divisionID=tblper.divisionID ) as divisions
	,(SELECT `tblstates`.`State` FROM `tblstates` WHERE `tblstates`.`StateID`=`tblper`.`stateID` ) as State
	
	,(SELECT `lga`.`lga` FROM `lga` WHERE `lga`.`lgaId`=tblper.lgaID ) as LGA
	,(SELECT `tblmaritalStatus`.`marital_status` FROM `tblmaritalStatus` WHERE `tblmaritalStatus`.`ID`=tblper.maritalstatus) as MStatus

	,$qualication
	  FROM `tblper` WHERE $qcourt and $qdivision and $qsection and $qgrade  and $qdesignation and $qgender and $timedate and $qtype and `employee_type`<>2  and `staff_status`=1 ORDER BY   `tblper`.`rank` DESC ,`tblper`.`grade` DESC ,DATE_FORMAT(`tblper`.`date_present_appointment`,'%Y-%m-%d') ASC,DATE_FORMAT(`tblper`.`appointment_date`,'%Y-%m-%d') ASC");
	
	return $List;
	
	$rawdata= DB::SELECT ("SELECT * FROM `tblper`  ");
	foreach ($rawdata as $value) {
        $raw=explode("/",$value->appointment_date);
        if (count($raw)==3){
            DB::table('tblper')->where('ID', $value->ID)->update([	
                'appointment_date' 		=> $raw[2].'-'.$raw[1].'-'.$raw[0]
            ]);
        }
    }
}









	Public function Gender(){
		$List= DB::Select("SELECT * FROM `tblgender`");
		return $List;
	}
	
	Public function AllocationSource(){
		$List= DB::Select("SELECT * FROM `tblallocation_type` where status=1");
		return $List;
	}
	Public function BudgetType(){
		//$List= DB::Connection("mysql2")->Select("SELECT * FROM `tblcontractType` where status=1");
		$List= DB::table('tblcontractType')
		->where('status', 1)
		->get();
		return $List;
	}
	Public function EconomicHead($budgettype){
		$List= DB::Select("SELECT * FROM `tbleconomicHead` WHERE `contractTypeID`='$budgettype' and status=1");
		return $List;
	}
	Public function EconomicText($id){
	$data='Not Applicatable';
		$List= DB::Select("SELECT * FROM `tbleconomicCode` WHERE `ID`='$id'");
		if($List){ $data=$List[0]->description.'('.$List[0]->economicCode.')';}
		return $data;
	}
	Public function EarningDeductionList($id){
		$list=DB::table('tblcvSetup')
	        ->join('tblearningParticular','tblearningParticular.ID','=','tblcvSetup.particularID')
	        ->select('tblcvSetup.ID','Particular','description' ,'rank','status','economiccode')
	        ->orderBy('tblcvSetup.particularID', 'asc')->get();
	        foreach ($list as $key => $value) {
		            $lis = (array) $value;
		            $lis['vote'] = $this->EconomicText($value->economiccode);
		            $value = (object) $lis;
		            $list[$key]  = $value;
		        }
		return $list ;
	}
	Public function EconomicCode($allocationsource,$economichead){
		$List= DB::Select("SELECT * FROM `tbleconomicCode` WHERE `allocationID`='$allocationsource' and `contractGroupID`='$economichead' and status=1");
		//die("SELECT * FROM `tbleconomicCode` WHERE `allocationID`='$allocationsource' and `economicHeadID`='$economichead' and status=1");
		//return $List;
		return $List;
	}
	Public function RateCode(){
		$a = array();
		$d = DB::table('tblsalaryfunction')->get();
		foreach($d as $k)
		{
			$a[$k->code] = $k->rate;
		}
		
		return $a;
	}
	Public function SalaryPayStructure($courtid,$grade,$step,$employeetype){
		$List= DB::Select("SELECT * FROM `basicsalary` WHERE `employee_type`='$employeetype' and `courtID`='$courtid' and `grade`='$grade' and `step`='$step'");
		if($List){
		return $List[0];
		}
		else{
		return DB::Select("SELECT 0 as  `amount`, 0 as `tax`, 0 as `servant`, 0 as `meal`, 0 as `driver`, 0 as `housing`, 0 as `transport`, 0 as `utility`, 0 as `furniture`, 0 as `peculiar`, 0 as `leave_bonus`, 0 as `pension`, 0 as `nhf`, 0 as `unionDues`")[0];
		}
	}
	Public function CourtInfo(){
	$List= DB::Select("SELECT * FROM `tblsole_court`");
	return $List[0];
	}
	
	Public function CVRembalance($id,$amount,$tamount,$recycling){
	if($recycling==1){return $amount;}
	$List= DB::Select("SELECT IFNULL(sum(`amount`),0) as TSum FROM `tblotherEarningDeduction` WHERE `staffcvid`='$id'");
	$rem=$tamount-$List[0]->TSum;
	if($rem >= $amount){return $amount;}
	else{return $rem;}
	}
	Public function StaffDueforArrear($court,$division,$period){
	$qcourt=1;
	if ($court!=''){}
	$qdivision=1;
	if ($division!=''){}
	//$List= DB::Select("SELECT * FROM `tblper` WHERE DATEDIFF('$period',`date_present_appointment`)>=365 and $qdivision and $qcourt");
	$List= DB::Select("SELECT *,(CASE WHEN `incremental_date` THEN `incremental_date` ELSE `date_present_appointment` END) as lastduedate FROM `tblper` WHERE DATEDIFF('$period',(CASE WHEN `incremental_date` THEN `incremental_date` ELSE `date_present_appointment` END ) )>=365 and $qdivision and $qcourt");
	

	if ($List){
	foreach ($List as $b){
	$newstep=$b->step + 1;
	//dd("$court, $b->employment_type, $b->grade, $newstep");
	if ($this->IsStructureExist($court, $b->employee_type, $b->grade, $newstep)==true){
	DB::table('tblstaff_for_arrears')->insert(array(
		'fileNo'    		=> $b->fileNo,
		'courtID'    		=> $b->courtID,
		'divisionID'    	=> $b->divisionID,
		'oldEmploymentType'	=> $b->employee_type,
		'newEmploymentType' 	=> $b->employee_type,
		'arrears_type'    	=> 'Increment',
		'old_grade'    		=> $b->grade,
		'old_step'    		=> $b->step,
		'new_grade'    		=> $b->grade,
		'new_step'    		=> $newstep,
		'due_date'    		=>  Carbon::createFromFormat('Y-m-d', $b->lastduedate )->addYear(1) ,
		'month_payment'    	=> '',
		'year_payment'    	=> '',
		'payment_status'    	=> 0,
		'approvedBy'      	=> 'System',
		'approvedDate'  	=> $data=date("Y-m-d H:i:s"),
	));
	DB::table('tblper')->where('fileNo',$b->fileNo)->update([
					'incremental_date' => Carbon::createFromFormat('Y-m-d', $b->lastduedate )->addYear(1),
					'step' =>$b->step+1,
				]);
	}
	}
	}
	return null;
	}
	Public function IsStructureExist($court,$employment_type,$grade,$step){
	if(DB::Select("SELECT * FROM `basicsalaryconsolidated` WHERE `employee_type`='$employment_type' and `courtID`='$court' and `grade`='$grade' and `step`='$step'"))
	{return true;}else{return false;}
	}
	Public function getstaffDesignation($departmentID,$grade){
      	$List= DB::Select("SELECT `designation` as designation FROM `tbldesignation` WHERE `departmentID` ='$departmentID' AND `grade`='$grade' ");
      	if ($List){return $List[0]->designation;}else {return '';}
    	}
    	Public function RankDesignationList(){
		$List= DB::Select("SELECT `id`, `code`, `description`, `userid`,( select `name` from users where users.username=`tblaction_rank`.userid) as userdetails FROM `tblaction_rank`");
		return $List;
	}
	Public function UserList(){
		$List= DB::Select("SELECT * FROM `users`");
		return $List;
	}
	Public function BankList(){
		$List= DB::Select("SELECT * FROM `tblbanklist`");
		return $List;
	}
	Public function MonthCount($id,$month,$year){
	  	DB::UPDATE ("UPDATE `tblbacklog` SET `year`='$year',`month`='$month' WHERE `year` IS NULL  and `month` IS NULL ");
	  	
	  	//die("UPDATE `tblbacklog` SET `year`='$year',`month`='$month' WHERE `year`='' and `month`=''");
		$List= DB::Select("SELECT * FROM `tblbacklog` WHERE `month`='$month' AND `year`='$year' AND`staffid`='$id'");
		if ($List){
		return $List[0]->mcount;
		}
		return 1;
	}
	Public function StaffForArrearList($court,$year,$month,$duedate){
	$qyear=1;
	$qmonth=1;
	$qduedate=1;
	//dd("$year $month");
	if($year!='All'){$qyear="`year_payment`='$year'";}
	if($month!='All'){$qmonth="`month_payment`='$month'";}
	if($year==''){$qyear="`year_payment` is null";}
	if($month==''){$qmonth="`month_payment` is null";}
	//dd($qyear .'  '.$qmonth );

		return DB::Select("SELECT *
		,(SELECT fileNo FROM `tblper` WHERE `tblper`.`ID`=`tblstaff_for_arrears`.staffid) as Fnumber
		,(SELECT Concat(`surname`,' ',`othernames`, ' ', `first_name`) FROM `tblper` WHERE `tblper`.`ID`=`tblstaff_for_arrears`.staffid) as Names
		 FROM `tblstaff_for_arrears` WHERE $qyear and $qmonth order by year_payment, ID");
	}
	Public function SingleStaffSalaryComputation($staffid,$month,$year){
		DB::DELETE ("DELETE FROM `tblpayment_consolidated` WHERE `staffid`='$staffid'  and `year`='$year' and `month`='$month'");
		DB::DELETE ("DELETE FROM `tblarrears` WHERE  `staffid`='$staffid' and `year`='$year' and `month`='$month' ");
		DB::DELETE ("DELETE FROM `tblarrears_overdue` WHERE  `staffid`='$staffid' and `year`='$year' and `month`='$month' ");
		DB::DELETE ("DELETE FROM `tblotherEarningDeduction` WHERE `staffid`='$staffid' and `year`='$year' and `month`='$month'");
		
		$List= DB::Select(" SELECT *,`tblper`.`ID` as `staffid` FROM `tblper` join `basicsalaryconsolidated` 
		on `basicsalaryconsolidated`.`employee_type`=`tblper`.`employee_type` 
		and `basicsalaryconsolidated`.`courtID`=`tblper`.`courtID` and `basicsalaryconsolidated`.`grade`=`tblper`.`grade` 
		and `basicsalaryconsolidated`.`step`=`tblper`.`step` and `basicsalaryconsolidated`.`employee_type`=`tblper`.`employee_type`
 		WHERE `tblper`.`ID`= '$staffid'  and  `tblper`.`staff_status`='1'");
 		//dd($List);
 		$LEAV=0;
 		if($List){
 		$b=$List[0];
		  $icount=$this->MonthCount($staffid,$month,$year);
		  $ArrearComputation=$this->ArrearComputationCosolidatedNew($staffid,$year,$month);
		  $OverdueArrearComputation=$this->OverdueArrearComputationCosolidatedNew($staffid,$year,$month);
		  $othercomputation=$this->OtherEarn($staffid,$year,$month);
		  $AEarn=$ArrearComputation->basic+$OverdueArrearComputation->basic;
		  $OEarn=$othercomputation->Earn;
		  $AD=$ArrearComputation->Deduction+$OverdueArrearComputation->Deduction;
		  $OD=$othercomputation->Deduction;
		  $TEarn=(($b->amount+$b->housing+$b->transport+$b->furniture+$b->peculiar+$b->driver+$b->servant+$b->meal+$b->utility+$b->leave_bonus)*$icount) +$LEAV+$AEarn+$OEarn;
		  $TD=(($b->tax+$b->nhf+$b->unionDues+$b->pension)*$icount)+$AD+$OD;
		  $NetPay=$TEarn-$TD;
		   DB::table('tblpayment_consolidated')->insert(array(
			'courtID'	    	=> $b->courtID,
			'divisionID'    	=> $b->divisionID,
			'current_state'    	=> $b->current_state,
			'staffid'    	=> $b->staffid,
			'fileNo'    	=> $b->fileNo,
			'name'    	=> $b->surname.' '.$b->first_name.' '.$b->othernames,
			'year'    => $year,
			'month'    	=> $month,
			'rank'    	=> $b->rank,
			'grade'    	=> $b->grade,
			'step'      => $b->step,
			'bank'  => $b->bankID,
			'bankGroup'    	=> $b->bankGroup,
			'bank_branch'    	=> $b->bank_branch,
                        'AccNo'    	=> $b->AccNo,
                        'Bs'    	=> $b->amount*$icount,
                        'HA'    	=> $b->housing*$icount,
			'TR'    	=> $b->transport*$icount,
			'FUR'      => $b->furniture*$icount,
			'PEC'  => ($b->peculiar*$icount) + $ArrearComputation->peculiar+ $OverdueArrearComputation->peculiar ,
			'UTI'    	=> $b->utility*$icount,
			'DR'    	=> $b->driver*$icount,
                        'SER'    	=> $b->servant*$icount,
			'ML'    	=> $b->meal*$icount,
                        'LEAV'    	=> $b->leave_bonus*$icount,
                        'AEarn'    	=> $AEarn,
                        'OEarn'    	=> $OEarn,
			'TAX'    	=> ($b->tax*$icount)+$ArrearComputation->tax+$OverdueArrearComputation->tax,
			'NHF'      => ($b->nhf*$icount)+$ArrearComputation->nhf+$OverdueArrearComputation->nhf,
			'PEN'  => ($b->pension*$icount)+$ArrearComputation->pension+$OverdueArrearComputation->pension,
			'UD'  => ($b->unionDues*$icount)+$ArrearComputation->unionDues+$OverdueArrearComputation->unionDues,
			'AD'    	=> $AD,
			'OD'    	=> $OD,
                        'TEarn'    	=> $TEarn + $ArrearComputation->peculiar + $OverdueArrearComputation->peculiar,
                        'TD'    	=> $TD,
                        'NetPay'    	=> $NetPay+ $ArrearComputation->peculiar + $OverdueArrearComputation->peculiar,
                        'payment_status'    	=> 1,
                        
                       
		));
 		}
		//dd($List);
		
		return $List;
		
		return DB::Select("SELECT *
		,(SELECT fileNo FROM `tblper` WHERE `tblper`.`ID`=`tblstaff_for_arrears`.staffid) as Fnumber
		,(SELECT Concat(`surname`,' ',`othernames`, ' ', `first_name`) FROM `tblper` WHERE `tblper`.`ID`=`tblstaff_for_arrears`.staffid) as Names
		 FROM `tblstaff_for_arrears` WHERE 1");
	}
	Public function StaffOverdueArrear($staffid){
		$List= DB::Select("SELECT * FROM `tblstaff_for_arrears_overdue` WHERE `staffid`='$staffid' order by `due_date`");
		return $List;
	}
	Public function IsSOTPeriod($year,$month,$court){
		$List= DB::Select("SELECT * FROM `tblactive_sot_month` WHERE `courtID`='$court' and `month`='$month' and `year`='$year'");
		return $List;
	}
	Public function SpecialOverTime($staffid, $court,$grade){
	    	if(DB::table('tblspecial_overtime_overide')->where('staffid', $staffid)->first())
        	{
        	    return DB::table('tblspecial_overtime_overide')->where('staffid', $staffid)->first();
        	}
		$List= DB::Select("SELECT * FROM `tblquarterly_allowance` WHERE `courtID`='$court' and `grade`='$grade'");
		if($List)return $List[0];
		return DB::Select("SELECT 0 as `gross`, 0 as `tax`")[0];
	}
	Public function TaxableEarning($staffid, $year,$month){
		$List= DB::Select("SELECT IFNULL(sum(`amount`),0) as Taxable FROM `tblotherEarningDeduction` WHERE (`CVID`='4' or `CVID`='22') and `staffid`='$staffid' and `month`='$month' and `year`='$year'");
		//dd($List[0]->Taxable);
		if($List)return $List[0]->Taxable;
		return 0;
	}
}
