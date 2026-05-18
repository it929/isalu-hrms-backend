<?php

namespace App\Http\Controllers\hr;

namespace App\Http\Controllers\hr;

use Illuminate\Http\Request;
use File;
use App\Http\Requests;
use Carbon\Carbon;
use Session;
use DB;

class functionezeController extends ParentController
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
	 
	 
	Public function DepartmentList2($department){
	$qdepartment=1;
	if ($department !=''){$qdepartment="`departmentID`='$department'";}
		$DepartmentList= DB::Select("SELECT * FROM `tblsection` where $qdepartment");
		return $DepartmentList;
	}
	Public function Gender(){
		$List= DB::Select("SELECT * FROM `tblgender`");
		return $List;
	}
	Public function Divisions(){
		$List= DB::Select("SELECT * FROM `tbldivision`");
		return $List;
	}
	Public function DivisionList($court){
		$List= DB::Select("SELECT * FROM `tbldivision` WHERE `courtID`='$court'");
		return $List;
	}
	Public function ConfirmDepartment2($department){
		$confir= DB::Select("SELECT * FROM `tblsection` WHERE `section`='$department'");
		if(($confir))
		   {
			   return true;
		   }
		   else
		   {
			return false;
		   }
	}
	
	Public function DesignationList2(){
		$DepartmentList= DB::Select("SELECT * FROM `tbldesignation` ");
		return $DepartmentList;
	}
	
	Public function ConfirmDesignation2($designation){
		$confir= DB::Select("SELECT * FROM `tbldesignation` WHERE  `designation`='$designation'");
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
		$staffAuth=DB::table('tblper')->select('tblper.courtID')
		->join('users', 'users.id', '=', 'tblper.UserID')
		->where('users.username', '=', $username)->first();
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
		$List= DB::Select("SELECT * FROM `tbl_court`");
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
		Public function LeaveType(){
		$List= DB::Select("SELECT * FROM `tblleave_type`");
		return $List;
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
	Public function QueryStaffReport($division,$department,$designation, $grade,$step,$gender,$fromdate,$todate,$type,$orderlist){
	$qorderlist="";
	if($orderlist=="fileNo"){$qorderlist=",`fileNo` ASC";}
	if($orderlist=="grade"){$qorderlist=",`grade` DESC";}
	if($orderlist=="appointment_date"){$qorderlist=",`appointment_date` ASC";}
	if($orderlist=="dob"){$qorderlist=",`dob` ASC";}
	$qtype= " `employee_type` <>'CONSOLIDATED'";
	if($type!=''){$qtype="`employee_type`='$type'";}
	$qdesignation=1;
	if($designation!=''){$qdesignation=" exists (select null from tblPost where tblPost.cadreID=tblper.departmentID and tblPost.grade=tblper.grade and tblPost.ID='designation')";}
	$qdivision=1;
	if($division!=''){$qdivision="`divisionID`='$division'";}
	$qsection=1;
	if($department!=''){$qsection="`departmentID`='$department'";}
	
	$qgrade=1;
	if($grade!=''){$qgrade="`grade`='$grade'";}
	
	$qstep=1;
	if($step!=''){$qstep="`step`='$step'";}
	
	$qgender=1;
	if($gender!=''){$qgender="`gender`='$gender'";}
	
	$qgender=1;
	if($gender!=''){$qgender="`gender`='$gender'";}
	$qualication=" (SELECT GROUP_CONCAT((SELECT  tblqualification.qualification from tblqualification where tblqualification.ID= tbleducations.degreequalification),'( ',
	(Select tblcertificateHeld.certHeld from tblcertificateHeld where tblcertificateHeld.id=tbleducations.certificateheld ),')-',DATE_FORMAT(tbleducations.schoolto,'%Y') ORDER BY  tbleducations.`categoryID` ASC SEPARATOR ', ') FROM `tbleducations` WHERE tbleducations.fileNo=tblper.fileNo  ) as qualifications ";
	//$qualication=" (SELECT GROUP_CONCAT((SELECT  tblqualification.qualification from tblqualification where tblqualification.ID= tbleducations.degreequalification)  ORDER BY tbleducations.degreequalification ASC SEPARATOR ', ') FROM `tbleducations` WHERE tbleducations.fileNo=tblper.fileNo) as qualifications ";
	
	$timedate= "(DATE_FORMAT(`appointment_date`,'%Y-%m-%d') BETWEEN '$fromdate' AND '$todate')";
	
	$List= DB::Select("SELECT *
	,(SELECT concat(`surname`,' ',`first_name`, ' ',`othernames`) ) as StaffName 
	,(SELECT `designation` FROM `tbldesignation` WHERE `tbldesignation`.`id`=tblper.designationID  ) as designations
	,(SELECT `section` FROM `tblsection` WHERE `tblsection`.`id`=tblper.sectionID ) as sections
	,(SELECT `division` FROM `tbldivision` WHERE tbldivision.divisionID=tblper.divisionID ) as divisions
	,(SELECT tblstates.`state` FROM `tblstates` WHERE tblstates.stateId=tblper.stateID ) as State
	,(SELECT `lga`.`lga` FROM `lga` WHERE `lga`.`lgaId`=tblper.lgaID ) as LGA
	,(SELECT `lga`.`lga` FROM `lga` WHERE `lga`.`lgaId`=tblper.lgaID ) as MStatus

	,$qualication
	  FROM `tblper` WHERE $qdivision and $qsection and $qgrade and $qstep and $qdesignation and $qgender and $timedate and $qtype ORDER BY `tblper`.`display_order` DESC $qorderlist, `tblper`.`grade` DESC,`tblper`.`date_present_appointment` ASC,`tblper`.`appointment_date` ASC");
	return $List;
	}
	
	Public function QualifationList(){
		$List= DB::Select("SELECT * FROM `tblqualification`");
		return $List;
	}
	Public function OrderList(){
		$List= DB::Select("SELECT * FROM `tblstaffQueryOrder`");
		return $List;
	}
	Public function DepartmentList(){
		$List= DB::Select("SELECT * FROM `tbldepartment`");
		return $List;
	}
	Public function CadreList(){
		$List= DB::Select("SELECT * FROM `tblcadre`");
		return $List;
	}
	Public function EmployeeTypeList(){
		$List= DB::Select("SELECT * FROM `tblemployee_type`");
		return $List;
	}
	Public function Sectionformating(){
	$List= DB::Select("SELECT * FROM `tblsection2`");
	
	if($List){
	foreach ($List as $b)
	{
	$sectioid=$b->id;
	$section=$b->section;
	DB::UPDATE("UPDATE `tblper` SET `sectionID`='$sectioid' WHERE `section`='$section'");
	}
	
	}
	return null;
	}
	
	Public function QueryCertHeldList($fileNo){
	$List= DB::Select("SELECT *,(select tblqualification.qualification from tblqualification where tblqualification.ID=`degreequalification`) as Qual
	,( select tblcertificateHeld.certHeld from tblcertificateHeld where tblcertificateHeld.id=`certificateheld`) as Certheld
	,(SELECT concat(tblper.`surname`,' ',tblper.`first_name`, ' ',tblper.`othernames`) from tblper where tblper.fileNo='$fileNo' ) as StaffName 
	,(SELECT `tblqualification_category`.`category_name` FROM `tblqualification_category` WHERE `tblqualification_category`.`categoryID`=tbleducations.categoryID) as qualificationCat
    	FROM `tbleducations` WHERE `fileNo`='$fileNo' 
    	order by (SELECT `tblqualification_category`.`rank` FROM `tblqualification_category` WHERE `tblqualification_category`.`categoryID`=tbleducations.categoryID)" );
		return $List;
	}
	Public function CertList($qual){
		$List= DB::Select("SELECT * FROM `tblcertificateHeld` WHERE `qualificationID`='$qual'");
		return $List;
	}
	Public function Qualificationdetails($qual){
		$List= DB::Select("SELECT * FROM `tblqualification` WHERE `ID`='$qual'");
		if($List){
		$details=$List[0]->qualification. ':'. $List[0]->description;
		return $details;}else{return "";}
		
	}
	Public function StaffList(){
		$List= DB::Select("SELECT * FROM `tblper`");
		return $List; 
	}
	Public function Staff($fileNo){
		$List= DB::Select("SELECT * FROM `tblper` WHERE `fileNo`='$fileNo'");
		return $List;
	}
	Public function QualificationCategory(){
		$List= DB::Select("SELECT * FROM `tblqualification_category`");
		return $List;
	}
	Public function Institutions(){
		$List= DB::Select("SELECT * FROM `tblinstitution` order by `INSTITUTION`");
		return $List;
	}
	Public function InstExist($inst){
		$List= DB::Select("SELECT `INSTITUTION` FROM `tblinstitution` WHERE `INSTITUTION`='$inst'");
		if(! $List){DB::INSERT ("INSERT INTO `tblinstitution`(`INSTITUTION`) VALUES ('$inst')");}
		return $List;
	}
	
	public function StateList(){
		$List= DB::Select("SELECT * FROM `tblstates`");
		return $List; 
		dd($List);
	}
	
	public function LgaList(){
		$list = DB::Select("SELECT * FROM `lga`");
		return $list;
	}
	
	public function NOK($fileNo){
		$list = DB::Select("SELECT * FROM `tblnextofkin` WHERE `fileNo` = '$fileNo'");
		return $list;
	}

	public function getChildren($fileNo){
		$list = DB::SELECT("SELECT * FROM `tblchildren_particulars` WHERE `fileNo` = '$fileNo'");
		return $list;
	}

	public function getPrevEmp($fileNo){
		$list = DB::SELECT("SELECT * FROM tblpreviousemployment_rec WHERE `fileNo` = '$fileNo'");
		return $list;
	}

	public function getOtherInfo($fileNo){
		$list = DB::SELECT("SELECT * FROM tblotherinfoforstaffdocumentation WHERE `fileNo` = '$fileNo'");
		return $list;
	}
	
	public function getDocumentationData($getvariable){
		$fileNo = $getvariable;
		//tblper
		//nok
		//previousemployment
		//children particulars
		//otherinformation

		$staffbasic = DB::SELECT("SELECT * FROM tblper WHERE `fileNo` = '$fileNo'");
		$staffnok = DB::SELECT("SELECT * FROM tblnextofkin WHERE `fileNo` = '$fileNo'");
		$staffpreemp = DB::SELECT("SELECT * FROM tblpreviousemployment_rec WHERE `fileNo` = '$fileNo'");
		$staffchildren = DB::SELECT("SELECT * FROM tblchildren_particulars WHERE `fileNo` = '$fileNo'");
        $staffotherinfo = DB::SELECT("SELECT * FROM tblotherinfoforstaffdocumentation WHERE `fileNo` = '$fileNo'");
        $staffmarriage = DB::table("tbldateofbirth_wife")->where("fileNo", "$fileNo")->first();
        
		$array = array(
            'basicinfo' => $staffbasic,
            'marriage'  => $staffmarriage,
			'nok'  => $staffnok,
			'previousemployment' => $staffpreemp,
			'children' => $staffchildren,
			'otherinfo' => $staffotherinfo
		);

		return $array;
	}
	
	public function uploadPhoto($owner, $value){
    	if(isset($owner) AND isset($value)){
    		
    			$filename 		= $value['name'];
				$file_temp_name = $value['tmp_name'];
				$filetype 		= $value['type'];
				$filesize 		= $value['size'];
				$fileerror 		= $value['error'];

				//first we check for extention
				$extFile = explode('.', $filename);
				$fileExt = strtolower(end($extFile));


				if(!in_array($fileExt, array('jpg', 'png', 'jpeg', 'gif'))){

					return 'file not accepted!';

				} else {
					//we need to check the file size
					
					if($filesize > 10000000){
						return 'file is too large!';
					} else {
						//we need to check for error
						if($fileerror !== 0){
							return 'file contains some errors';
						} else {
							//time to upload

							//generate a name
								$fileNewname = $owner .'.' . $fileExt;
								
								$destination = asset('passport') . '/' . $fileNewname;
						

							$fileDestination = $destination;
							
							if(file_exists($fileDestination)){
							  unlink($fileDestination);
							 }

							if(!move_uploaded_file($file_temp_name, $fileDestination)){
								return 'file was not uploaded!';
							} else {
								
								return 'file was uploaded';
							}
						}
					}
				}
    	}
    }

    public function getMaritalStatus(){
        $list = DB::table("tblmaritalStatus")->get();
        return $list;
	}
	
	public function getMarriageInfo( $fileNo ){
		$list = DB::table("tbldateofbirth_wife")->where('fileNo', $fileNo)->first();
        return $list;
	}

    public function getValueName($id, $table, $col)
    {
        if(!empty($id) && !empty($table)){
            $result = DB::Table("{$table}")->where("{$col}", "{$id}")->first();
            return ($result === null) ? [] : $result;
        }
    }

    public function getEducationRecord( $fileNo ){
        $result = DB::table('tbleducations')
                ->leftjoin('tblqualification', 'tblqualification.ID', '=', 'tbleducations.degreequalification')
                ->leftjoin('tblqualification_category', 'tblqualification_category.categoryID', '=', 'tbleducations.categoryID')
                ->leftjoin('tblcertificateHeld', 'tblcertificateHeld.id', '=', 'tbleducations.certificateheld')
				->where('tbleducations.fileNo', $fileNo)
				->orderBy('tbleducations.categoryID')
                ->get();
        return $result;
    }
	
}
