<?php

namespace App\Http\Controllers\payroll;

use App\Http\Requests;
use App\Role;
use Entrust;
use App\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as rep;

class functionFundsController extends Basefunction 
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
	
		$List= DB::Select(" SELECT * FROM `tblper` join `basicsalary` 
		on `basicsalary`.`employee_type`=`tblper`.`employee_type` 
		and `basicsalary`.`courtID`=`tblper`.`courtID` and `basicsalary`.`grade`=`tblper`.`grade` 
		and `basicsalary`.`step`=`tblper`.`step` and `basicsalary`.`employee_type`=`tblper`.`employee_type`
 		WHERE `tblper`.`courtID`= '$court' and $qdivision and `staff_status`='1'");
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
	Public function DeletePayrollperiod($court,$division,$year,$month){
	$qdivision=1;
	if ($division!='All'){$qdivision="`divisionID`='$division'";}
	DB::DELETE ("DELETE FROM `tblpayment` WHERE `courtID`='$court' and $qdivision and `year`='$year' and `month`='$month'");
		
	}
	Public function DeletePayrollArrearperiod($court,$division,$year,$month){
	$qdivision=1;
	if ($division!='All'){$qdivision="`divisionID`='$division'";}
	DB::DELETE ("DELETE FROM `tblarrears` WHERE `courtID`='$court' and $qdivision and `year`='$year' and `month`='$month'");
		
	}
	Public function DeletePayrollStaffCV($court,$division,$year,$month){
	$qdivision=1;
	if ($division!='All'){$qdivision="`divisionID`='$division'";}
	DB::DELETE ("DELETE FROM `tblotherEarningDeduction` WHERE `courtID`='$court' and $qdivision and `year`='$year' and `month`='$month'");
	}
	
	Public function ArrearComputation($fileNo,$year,$month){
	$Earn=0;
	$Deduction=0;
	$checkArrear= DB::select("SELECT * FROM `tblstaff_for_arrears` WHERE ((`month_payment`='$month' and `year_payment`='$year') or `payment_status`=0) 
	 and `fileNo`='$fileNo'");
	if($checkArrear)
	{
	
	$checkArrearID=$checkArrear[0]->ID;
	$oldGrade=$checkArrear[0]->oldGrade;
	$newGrade=$checkArrear[0]->newGrade;
	$OldStep=$checkArrear[0]->OldStep;
	$newStep=$checkArrear[0]->newStep;
	$oldEmploymentType=$checkArrear[0]->oldEmploymentType;
	$newEmploymentType=$checkArrear[0]->newEmploymentType;
	$court=$checkArrear[0]->courtID;
	$oldpay= DB::select("SELECT * FROM `basicsalary` WHERE `employee_type`='$oldEmploymentType' and `courtID`='$court' and `grade`='$oldGrade' and `step`='$OldStep'");
	$newpay= DB::select("SELECT * FROM `basicsalary` WHERE `employee_type`='$newEmploymentType' and `courtID`='$court' and `grade`='$newGrade' and `step`='$newStep'");
	DB::table('tblarrears')->insert(array(
			'fileNo'    	=> $fileNo,
                        'courtID'    	=> $court,
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
		));
	$Earn1=($newpay[0]->amount -$oldpay[0]->amount)+($newpay[0]->peculiar-$oldpay[0]->peculiar)+($newpay[0]->leave_bonus-$oldpay[0]->leave_bonus)
		+($newpay[0]->utility-$oldpay[0]->utility)+($newpay[0]->transport-$oldpay[0]->transport)+($newpay[0]->housing-$oldpay[0]->housing);
	$Deduction1=($newpay[0]->tax-$oldpay[0]->tax)+($newpay[0]->pension-$oldpay[0]->pension)+($newpay[0]->nhf-$oldpay[0]->nhf)+($newpay[0]->unionDues-$oldpay[0]->unionDues);
	$activemonth = date("n", strtotime('June'));
	$dif = dateDiff($year."-".$activemonth."-1", $checkArrear[0]->due_date);
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
	
	Public function OtherEarn($fileNo,$year,$month){
	$checkCV= DB::select("SELECT *,(SELECT `particularID` FROM `tblcvSetup` WHERE `tblcvSetup`.`ID` =`tblstaffCV`.`cvID`) as `particularID` FROM `tblstaffCV` WHERE `fileNo`='$fileNo'");
	if($checkCV)
	{
	foreach ($checkCV as $b){
	DB::table('tblotherEarningDeduction')->insert(array(
			'fileNo'    	=> $fileNo,
                        'courtID'    	=> $b->courtID,
			'month'	    	=> $month,
			'year'    	=> $year,	
			'divisionID'    => $b->divisionID,
			'particularID'  => $b->particularID,
			'CVID'    	=> $b->cvID,
			'amount'    	=> $b->amount,
			
		));
	}
	}
		$data=  DB::select("SELECT sum(`amount`) as  Total FROM `tblotherEarningDeduction` WHERE `fileNo`='$fileNo' and `year`='$year' and `month`='$month' and `particularID`='1'");
		$tearn=$data[0]->Total;
		$data=  DB::select("SELECT sum(`amount`) as  Total FROM `tblotherEarningDeduction` WHERE `fileNo`='$fileNo' and `year`='$year' and `month`='$month' and `particularID`='2'");
		
		$tdeduction=$data[0]->Total ;
		
		
		DB::UPDATE ("UPDATE `tblstaffEarningDeduction` SET `year`='$year', `month`='$month',`status`='3' WHERE `year`='' and `month`='' and `fileNo`='$fileNo' and `status`=1");
		
		$data=  DB::select("SELECT sum(`amount`) as Total FROM `tblstaffEarningDeduction` WHERE (`month`='$month' and `year`='$year') and `status`='3' and `fileNo`='$fileNo'  
and exists(SELECT null FROM `tblearningDeductions` WHERE `tblearningDeductions`.`ID`=`tblstaffEarningDeduction`.`earningDeductionID` and `tblearningDeductions`.`particularID`='1')");
		$tearn+=$data[0]->Total;
		$data=  DB::select("SELECT sum(`amount`) as Total FROM `tblstaffEarningDeduction` WHERE (`month`='$month' and `year`='$year') and `status`='3' and `fileNo`='$fileNo'  
and exists(SELECT null FROM `tblearningDeductions` WHERE `tblearningDeductions`.`ID`=`tblstaffEarningDeduction`.`earningDeductionID` and `tblearningDeductions`.`particularID`='2')");
		
		$tdeduction+=$data[0]->Total ;
		
		$arreardata=  DB::select("select '$tearn' as 'Earn','$tdeduction' as 'Deduction'");
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
	
	Public function QueryStaffReport($court,$division,$department,$designation, $grade,$gender,$fromdate,$todate,$type,$orderlist){
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
	
	$List= DB::Select("SELECT *
	,(SELECT concat(`surname`,' ',`first_name`, ' ',`othernames`) ) as StaffName 
	,(SELECT  `designation` FROM `tbldesignation` WHERE `tbldesignation`.`grade`=`tblper`.`grade` and `tbldesignation`.`departmentID`=`tblper`.`department` ) as designations
	,(SELECT `division` FROM `tbldivision` WHERE tbldivision.divisionID=tblper.divisionID ) as divisions
	,(SELECT tblstates.`state` FROM `tblstates` WHERE tblstates.stateId=tblper.stateID ) as State
	,(SELECT `lga`.`lga` FROM `lga` WHERE `lga`.`lgaId`=tblper.lgaID ) as LGA
	,(SELECT `tblmaritalStatus`.`marital_status` FROM `tblmaritalStatus` WHERE `tblmaritalStatus`.`ID`=tblper.maritalstatus) as MStatus

	,$qualication
	  FROM `tblper` WHERE $qcourt and $qdivision and $qsection and $qgrade  and $qdesignation and $qgender and $timedate and $qtype ORDER BY   `tblper`.`grade` DESC,`tblper`.`date_present_appointment` ASC,`tblper`.`appointment_date` ASC");
	
	return $List;
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
		$List= DB::Select("SELECT * FROM `tblcontractType` where status=1");
		return $List;
	}
	Public function EconomicHead($budgettype){
		$List= DB::Select("SELECT * FROM `tbleconomicHead` WHERE `contractTypeID`='$budgettype' and status=1");
		return $List;
	}
	
	
	Public function EconomicCode($allocationsource,$economichead){
		$List= DB::Select("SELECT * FROM `tbleconomicCode` WHERE `allocationID`='$allocationsource' and `economicHeadID`='$economichead' and `status`=1");
		return $List;
	}
	
	Public function EconomicCode2($allocationsource,$group){
		$List= DB::Select("SELECT * FROM `tbleconomicCode` WHERE `allocationID`='$allocationsource' and `contractGroupID`='$group' and status=1 order by`economicCode`");
		foreach($List as $k => $list){
		        		$newline = (array) $list;
		        		$newline['AvailBal'] = $this->AvailableBal($list->ID);
		        		$newline = (object) $newline;
		        		$List[$k] = $newline;
		        	}
		return $List;
	}
	Public function YearPeriod(){
		$List= DB::Select("SELECT `Period` FROM `tblbudget` group by `Period` order by `Period`");
		return $List;
	}
	
		Public function QueryVoultReport($period,$allocationsource,$budgettype,$economichead,$economiccode){
	$qallocationsource=1;
	if($allocationsource!=''){$qallocationsource="`allocationType`='$allocationsource'";} 
	$qbudgettype=1;
	if($budgettype!=''){$qbudgettype="`economicGroupID`='$budgettype'";} 
	$qeconomichead=1;
	if($economichead!=''){$qeconomichead="`economicHeadID`='$economichead'";} 
	$qeconomiccode=1;
	if($economiccode!=''){$qeconomiccode="`economicCodeID`='$economiccode'";} 
	
	
	
	
	$List= DB::Select("SELECT *
	,(SELECT `allocation` FROM `tblallocation_type` WHERE `tblallocation_type`.`ID`=`tblbudget`.`allocationType`) as allocationsource
	,(SELECT `contractType` FROM `tblcontractType` WHERE `tblcontractType`.`ID`=`tblbudget`.`economicGroupID`) as economicgroup 
	,(SELECT `economicHead` FROM `tbleconomicHead` WHERE `tbleconomicHead`.`ID`=`tblbudget`.`economicHeadID`) as economichead
	,(SELECT `economicCode` FROM `tbleconomicCode` WHERE `tbleconomicCode`.`ID`=`tblbudget`.`economicCodeID`) as economiccode
	,(SELECT `description` FROM `tbleconomicCode` WHERE `tbleconomicCode`.`ID`=`tblbudget`.`economicCodeID`) as economicdisc
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='January') as January
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='February') as February
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='March') as March
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='April') as April
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='May') as May
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='June') as June
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='July') as July
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='August') as August
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='September') as September
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='October') as October
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='November') as November
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='December') as December
	
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1') as receivedallocation
	
	FROM `tblbudget` WHERE `Period`='$period' and $qallocationsource and  $qbudgettype and $qeconomichead and $qeconomiccode order by `economicGroupID`,`economicHeadID`,economiccode");
	foreach ($List as $key => $value) {
    	$lis = (array) $value;
		
	$lis['bookonhold']=$this->OutstandingLiability($value->economicCodeID);
	$lis['bal']=$this->VoultBalance($value->economicCodeID);
	$lis['av_bal']=$this->AvailableBal($value->economicCodeID);
	$lis['refunds']=$this->Refunds($value->economicCodeID);
	$value = (object) $lis;
    	$List[$key]  = $value;
			}
	//dd($List);
	return $List;
	
	}
	Public function QueryVoultReportold($period,$allocationsource,$budgettype,$economichead,$economiccode){
	$qallocationsource=1;
	if($allocationsource!=''){$qallocationsource="`allocationType`='$allocationsource'";} 
	$qbudgettype=1;
	if($budgettype!=''){$qbudgettype="`economicGroupID`='$budgettype'";} 
	$qeconomichead=1;
	if($economichead!=''){$qeconomichead="`economicHeadID`='$economichead'";} 
	$qeconomiccode=1;
	if($economiccode!=''){$qeconomiccode="`economicCodeID`='$economiccode'";} 
	
	
	
	
	$List= DB::Select("SELECT *
	,(SELECT `allocation` FROM `tblallocation_type` WHERE `tblallocation_type`.`ID`=`tblbudget`.`allocationType`) as allocationsource
	,(SELECT `contractType` FROM `tblcontractType` WHERE `tblcontractType`.`ID`=`tblbudget`.`economicGroupID`) as economicgroup 
	,(SELECT `economicHead` FROM `tbleconomicHead` WHERE `tbleconomicHead`.`ID`=`tblbudget`.`economicHeadID`) as economichead
	,(SELECT `economicCode` FROM `tbleconomicCode` WHERE `tbleconomicCode`.`ID`=`tblbudget`.`economicCodeID`) as economiccode
	,(SELECT `description` FROM `tbleconomicCode` WHERE `tbleconomicCode`.`ID`=`tblbudget`.`economicCodeID`) as economicdisc
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='January') as January
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='February') as February
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='March') as March
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='April') as April
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='May') as May
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='June') as June
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='July') as July
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='August') as August
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='September') as September
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='October') as October
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='November') as November
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='December') as December
	
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1') as receivedallocation
	,(SELECT IFNULL( sum( `totalPayment` ) , 0 ) FROM `tblpaymentTransaction` 
	WHERE `tblpaymentTransaction`.`status`='2' 
	and `tblpaymentTransaction`.`economicCodeID` =`tblbudget`.`economicCodeID` and  `tblpaymentTransaction`.`period`='$period') 
	as bookonhold
	,(SELECT IFNULL( sum( `totalPayment` ) , 0 ) FROM `tblpaymentTransaction` 
	WHERE `tblpaymentTransaction`.`status`='6' 
	and `tblpaymentTransaction`.`economicCodeID` =`tblbudget`.`economicCodeID` and  `tblpaymentTransaction`.`period`='$period') 
	as expend
	FROM `tblbudget` WHERE `Period`='$period' and $qallocationsource and  $qbudgettype and $qeconomichead and $qeconomiccode order by `economicGroupID`,`economicHeadID`,`economicCodeID`");
	foreach ($List as $key => $value) {
    	$lis = (array) $value;
		
	$lis['bookonhold']=$this->OutstandingLiability($value->economicCodeID);
	$lis['expend']=$this->VoultBalance($value->economicCodeID);
	$value = (object) $lis;
    	$List[$key]  = $value;
			}
	
	return $List;
	
	}
	Public function QueryVoultReportmonth($yearmoth,$allocationsource,$budgettype,$economichead,$economiccode,$month,$period){
	$qallocationsource=1;
	if($allocationsource!=''){$qallocationsource="`allocationType`='$allocationsource'";} 
	$qbudgettype=1;
	if($budgettype!=''){$qbudgettype="`economicGroupID`='$budgettype'";} 
	$qeconomichead=1;
	if($economichead!=''){$qeconomichead="`economicHeadID`='$economichead'";} 
	$qeconomiccode=1;
	if($economiccode!=''){$qeconomiccode="`economicCodeID`='$economiccode'";} 
	
	$timedate= " DATE_FORMAT(`tblpaymentTransaction`.`datePrepared`,'%Y-%m') ='$yearmoth'";
	$timedatetodate= " DATE_FORMAT(`tblpaymentTransaction`.`datePrepared`,'%Y-%m') <='$yearmoth'";
	$ydate= " DATE_FORMAT(`tblpaymentTransaction`.`datePrepared`,'%Y') ='$period'";
	$ret="SELECT *
	,(SELECT `allocation` FROM `tblallocation_type` WHERE `tblallocation_type`.`ID`=`tblbudget`.`allocationType`) as allocationsource
	,(SELECT `contractType` FROM `tblcontractType` WHERE `tblcontractType`.`ID`=`tblbudget`.`economicGroupID`) as economicgroup 
	,(SELECT `economicHead` FROM `tbleconomicHead` WHERE `tbleconomicHead`.`ID`=`tblbudget`.`economicHeadID`) as economichead
	,(SELECT `Code` FROM `tbleconomicHead` WHERE `tbleconomicHead`.`ID`=`tblbudget`.`economicHeadID`) as economicheadcode
	,(SELECT `economicCode` FROM `tbleconomicCode` WHERE `tbleconomicCode`.`ID`=`tblbudget`.`economicCodeID`) as economiccode
	,(SELECT `description` FROM `tbleconomicCode` WHERE `tbleconomicCode`.`ID`=`tblbudget`.`economicCodeID`) as economicdisc
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='January') as January
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='February') as February
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='March') as March
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='April') as April
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='May') as May
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='June') as June
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='July') as July
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='August') as August
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='September') as September
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='October') as October
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='November') as November
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and `month`='December') as December
	
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1') as receivedallocation
	,(SELECT IFNULL( sum( `totalPayment` ) , 0 ) FROM `tblpaymentTransaction` 
	WHERE `tblpaymentTransaction`.`status`='2' 
	and `tblpaymentTransaction`.`economicCodeID` =`tblbudget`.`economicCodeID` and  $timedate) 
	as bookonhold
	,(SELECT IFNULL( sum( `totalPayment` ) , 0 ) FROM `tblpaymentTransaction` 
	WHERE `tblpaymentTransaction`.`status`='6' 
	and `tblpaymentTransaction`.`economicCodeID` =`tblbudget`.`economicCodeID` and  $timedate) 
	as expend
	,(SELECT IFNULL( sum( `totalPayment` ) , 0 ) FROM `tblpaymentTransaction` 
	WHERE `tblpaymentTransaction`.`status`='2' 
	and `tblpaymentTransaction`.`economicCodeID` =`tblbudget`.`economicCodeID` and  $timedatetodate and $ydate) 
	as bookonholdtodate
	,(SELECT IFNULL( sum( `totalPayment` ) , 0 ) FROM `tblpaymentTransaction` 
	WHERE `tblpaymentTransaction`.`status`='6' 
	and `tblpaymentTransaction`.`economicCodeID` =`tblbudget`.`economicCodeID` and  $timedatetodate and $ydate) 
	as expendtodate
	FROM `tblbudget` WHERE `Period`='$period' and $qallocationsource and  $qbudgettype and $qeconomichead and $qeconomiccode order by `economicGroupID`,`economicHeadID`,`economicCodeID`";
	//die($ret);
	$List= DB::Select($ret);
	return $List;
	
	}
	
	Public function UserList(){
		$List= DB::Select("SELECT * FROM `users`");
		return $List;
	}
	Public function RankDesignationList(){
		$List= DB::Select("SELECT `id`, `code`, `description`, `userid`,( select `name` from users where users.username=`tblaction_rank`.userid) as userdetails FROM `tblaction_rank`");
		return $List;
	}
	Public function ParticularMonthAllocation($year,$month){
		$List= DB::Select("SELECT * FROM `tbltotalMonthlyAllocation` WHERE `year`='$year' and `month`='$year'");
		return $List;
	}
	
	Public function TotalMonthAllocation($year,$budgettype){
		$List= DB::Select("SELECT *
		,(SELECT sum( tblmonthlyAllocation.`amount` )
			FROM `tblmonthlyAllocation`
			WHERE `tblmonthlyAllocation`.`status` =1
			AND `tblmonthlyAllocation`.`year` = `tbltotalMonthlyAllocation`.`year`
			AND tblmonthlyAllocation.`month` = tbltotalMonthlyAllocation.`month` 
			and 
			EXISTS( select null  from tbleconomicCode  where `tblmonthlyAllocation`.`economicID`=tbleconomicCode.id and tbleconomicCode.contractGroupID='$budgettype' )
			) AS Allotted 
		FROM `tbltotalMonthlyAllocation` WHERE `year`='$year' and budgetType='$budgettype' order by month(str_to_date(left(`month`,3),'%b'))");
		return $List;
	}
	Public function LedgerCategory(){
		$List= DB::Select("SELECT * FROM `tblaccountcategory` order by`groupid`");
		return $List;
	}
	Public function LedgerCategoryPara($id){
		$List= DB::Select("SELECT * FROM `tblaccountcategory` where `id`='$id'");
		return $List;
	}
	Public function LedgerTypePara($id){
		$List= DB::Select("SELECT * FROM `tblaccounttype` where `id`='$id'");
		return $List;
	}
	Public function LedgerType($categoryId){
		$qcategoryid=1;
		if($categoryId!=''){$qcategoryid="`categoryid`='$categoryId'";}
		$List= DB::Select("SELECT *,
		(SELECT `category` FROM `tblaccountcategory` WHERE `tblaccountcategory`.`id`=`tblaccounttype`.`categoryid`) as Category
		 FROM `tblaccounttype` WHERE $qcategoryid order by `categoryid`,`id`");
		return $List;
	}
	Public function Ledger($typeId,$categoryId){
		$qcategoryid=1;
		if($categoryId!=''){$qcategoryid="`categoryid`='$categoryId'";}
		$qtypeId=1;
		if($typeId!=''){$qtypeId="`typeid`='$typeId'";}
		$List= DB::Select("SELECT *
		,(SELECT `category` FROM `tblaccountcategory` WHERE `tblaccountcategory`.`id`=`tblaccountledger`.`categoryid`) as Category
		,(SELECT `category` FROM `tblaccounttype` WHERE `tblaccounttype`.`id`=`tblaccountledger`.`typeid`) as accounttype
		 FROM `tblaccountledger` WHERE $qcategoryid and $qtypeId order by `categoryid`,`typeid`,`id`");
		return $List;
	}
Public function NextTypeCode($code,$id){
	    $myData= DB::Select("SELECT max(`typecode`) as maxcode FROM `tblaccounttype` WHERE `categoryid`='$id'");
	    if($myData[0]->maxcode==''){ 
	    return $code.'00001';
   	 }
   	 $codelent=strlen($code);
   	 $intc=strlen($myData[0]->maxcode);
    $data=substr($myData[0]->maxcode, $codelent, ($intc-$codelent));
    $newcode=$data+1;
    while(strlen($newcode)<5)
     {
        $newcode="0".$newcode;
     }
    return $code.$newcode;
    }
Public function NextLedgerCode($code,$id){
	$myData= DB::Select("SELECT max(`accountNo`) as maxcode FROM `tblaccountledger` WHERE `typeid`='$id'");
	if($myData[0]->maxcode==''){ 
	return $code.'00001';
	}
	$codelent=strlen($code);
	$intc=strlen($myData[0]->maxcode);
	$data=substr($myData[0]->maxcode, $codelent, ($intc-$codelent));
	$newcode=$data+1;
	while(strlen($newcode)<5)
	{
	$newcode="0".$newcode;
	}
	return $code.$newcode;
    }
    Public function VoteBookRecord($voteid,$fromdate,$todate,$period){
    	$timedate= "(DATE_FORMAT(`trandate`,'%Y-%m-%d') BETWEEN '$fromdate' AND '$todate')";
	$Data= DB::Select("SELECT * FROM `tblvotebookrecord` WHERE $timedate and `ecoID`='$voteid' and `period`='$period'");
	return $Data;
    }
    Public function isTitle($data){
    	 switch  ($data){
	  case '(MR)':
	  return true;
	  case '(MRS)':
	  return true;
	  case 'ESQ':
	  return true;
	  case '(MISS)':
	  return true;
	  case '(MS)':
	  return true;
    	}
    	return false;
	
	}
	Public function Titlecode($data){
    	 switch  ($data){
	  case '(MR)':
	  return 1;
	  case '(MRS)':
	  return 2;
	  case 'ESQ':
	  return 4;
	  case '(MISS)':
	  return 3;
	  case '(MS)':
	  return 3;
    	}
    	return 0;
	
	}
Public function AccountFormat($num) {
while(strlen($num)<10)
{$num="0".$num;}
return $num;
}
Public function BankCode($data){
$bankdetails=DB::table('tblbanklist')->select('bankID')->where('Bankcode', '=', $data)->first();
	if($bankdetails){
	return $bankdetails-> bankID;
	}
    	 
   return 0;
	
}
protected  function AccessNotGranted($route){
	$userid=Auth::user()->id;
	$role=DB::table('assign_user_role')->where('userID', '=', $userid);
	if($role){
		$userrole=$role->value('roleID');
		if(DB::Select("SELECT null FROM `assign_module_role` WHERE `roleID`='$userrole' 
		and exists(SELECT null FROM `submodule` WHERE `submodule`.`submoduleID`=`assign_module_role`.`submoduleID` and `route`='$route')")) 
		{return false;}
		return true;
	
	}
	 return true;	
	}
	
Public function LendBorrowReport($id){
    $period=$this->ActivePeriod();
$db=DB::Select("SELECT *
,(SELECT concat(`economicCode`,': ',`description` ) FROM `tbleconomicCode` WHERE tbleconomicCode.`ID`=`eco_source` ) AS SOURCE
,(SELECT concat(`economicCode`,': ',`description` ) FROM `tbleconomicCode` WHERE tbleconomicCode.`ID`=`eco_destination` ) AS DEST
,(CASE WHEN `eco_destination`='$id' THEN `amount` ELSE '0' END) debit
,(CASE WHEN `eco_source`='$id' THEN `amount` ELSE '0' END) credit
FROM `tbllendborrow` WHERE (`eco_source`='$id' or `eco_destination`='$id') and `period`='$period'");
		 
   return $db;
	
}
Public function UnsettleFund($id){
    
    $period=$this->ActivePeriod();
    $db=DB::Select("SELECT * FROM `tbllendborrow` WHERE `period`='$period'  and exists( SELECT null FROM `tbleconomicCode` WHERE tbleconomicCode.`ID`=eco_destination and `contractGroupID`='$id') group by `eco_destination` ");
	$dat=array();
	foreach ($db as $value) {
	    $eco=$value->eco_destination;
	     $db2=DB::Select("SELECT *
	    ,Sum(CASE WHEN `eco_destination`='$eco' THEN -`amount` ELSE `amount` END) val 
	    ,(CASE WHEN `eco_destination`='$eco' THEN `eco_source` ELSE `eco_destination` END) targeteco 
	    FROM `tbllendborrow` WHERE(`eco_source`='$eco' or `eco_destination`='$eco') and `period`='$period'  group by targeteco having val < 0");
	    
	  foreach ($db2 as $value2) {
 	   $dt['debitid']=$eco;	  $dt['creditid']=$value2->targeteco;
	   $dt['debit']=$this->VoteDescription($eco);
	  $dt['credit']=$this->VoteDescription($value2->targeteco);
	  $dt['amount']=$value2->val;
	  array_push($dat,$dt);   
	  }
	    
	}
   return $dat;
	
}
Public function VoteDescription($id){
   $db=DB::Select("SELECT Concat(`economicCode`,':',`description`) as des FROM `tbleconomicCode` WHERE `ID`='$id'");
   if($db) return $db[0]->des;
   return null;
	
}
Public function TaxMetterDescription(){
   return DB::Select("SELECT * FROM `tax_matter_description` WHERE `active`=1");
}
Public function TaxMatterReport($from,$to,$element,$rtype,$rc){
    switch($element){
        case 1: 
            $element_value="`VATValue`  as element_value";
            $element_="'VAT'  as element_";
            $element_per="`VAT` as element_per";
            $element_value1="`VATValue`>0";
            break;
            case 2: 
            $element_="'WHT'  as element_";
            $element_value="`WHTValue`  as element_value";
            $element_per="`WHT` as element_per";
            $element_value1="`WHTValue`>0";
            break;
            case 3: 
            $element_="'STD'  as element_";
            $element_value="`stampduty` as element_value";
            $element_value1="`stampduty`>0";
            $element_per="`stampdutypercentage` as element_per";
            break;
            default: return [];
    }
    switch($rc){
        case "Recurrent": 
            $qrc="`department_voucher`='Recurrent'";
            break;
            case "Capital": 
            $qrc="`department_voucher`='Capital'";
            break;
            default: $qrc="1";
    }
    switch($rtype){
        case 1: 
            $timedate= "(DATE_FORMAT(`datePrepared`,'%Y-%m-%d') BETWEEN '$from' AND '$to')";
             $rtype_query="tblpaymentTransaction.`status`<=1";
             $transdate='`datePrepared`  as transdate';
            break;
            case 2: 
            $timedate= "(DATE_FORMAT(`dateTakingLiability`,'%Y-%m-%d') BETWEEN '$from' AND '$to')";
             $rtype_query="tblpaymentTransaction.`status`>1";
             $transdate='`dateTakingLiability`  as transdate';
            break;
            case 3: 
            $timedate= "(DATE_FORMAT(`dateTakingLiability`,'%Y-%m-%d') BETWEEN '$from' AND '$to')";
             $rtype_query="tblpaymentTransaction.`status`=6";
             $transdate='`dateTakingLiability`  as transdate';
            break;
            default:
            $rtype_query="1";
            $timedate= "(DATE_FORMAT(`datePrepared`,'%Y-%m-%d') BETWEEN '$from' AND '$to')";
            $transdate='`datePrepared` as transdate';
    }
   
   //die("SELECT *, $element_value, $element_per, $transdate FROM `tblpaymentTransaction` WHERE `companyID`<>13 and $rtype_query and $timedate and $element_value1");
  return DB::Select("SELECT tblpaymentTransaction.*, $element_value, $element_per, $transdate,$element_,
    tblcontractor.contractor,
    tblcontractor.TIN,
    tblVATWHTPayee.address
    ,tax_matter_description.tax_description
    FROM `tblpaymentTransaction`  
    left join tblcontractor on `tblpaymentTransaction`.`companyID`=tblcontractor.id  
    left join tblVATWHTPayee on tblVATWHTPayee.ID=tblpaymentTransaction.VAT
    left join tax_matter_description on tax_matter_description.descriptionID=`tax_report_description`
    WHERE `companyID`<>13 and $rtype_query and $timedate and $element_value1 and $qrc");
  
	
}
Public function Recurrent_Capital() {
	    return json_decode('[{"id":"Recurrent","text":"Recurrent"},{"id":"Capital","text":"Capital"}]');
	}

	Public function Rolelist(){
		$List= DB::Select("SELECT * FROM `user_role`");
		return $List;
	}
	Public function Statuslist(){
		$List= DB::Select("SELECT * FROM `tbluserstatus`");
		return $List;
	}
	Public function Userlists(){
		$List= DB::Select("SELECT users.*, tbluserstatus.status as statustext,assign_user_role.roleID as role, user_role.`rolename` as roletext FROM `users` 
		left join tbluserstatus on tbluserstatus.id=users.status 
		left join assign_user_role on assign_user_role.userID=users.id
		left join user_role on user_role.roleID=assign_user_role.roleID
		WHERE `user_type`<>'TECHNICAL'  order by user_role.`rolename`");
		return $List;
	}
		Public function VoteBalRangeReport($yearmoth,$allocationsource,$budgettype,$economichead,$economiccode,$month,$period,$fro, $to){
	 $tomonth=date('F',strtotime($to)) ;  
	    //dd($tomonth);
	$qallocationsource=1;
	if($allocationsource!=''){$qallocationsource="`allocationType`='$allocationsource'";} 
	$qbudgettype=1;
	if($budgettype!=''){$qbudgettype="`economicGroupID`='$budgettype'";} 
	$qeconomichead=1;
	if($economichead!=''){$qeconomichead="`economicHeadID`='$economichead'";} 
	$qeconomiccode=1;
	if($economiccode!=''){$qeconomiccode="`economicCodeID`='$economiccode'";} 
	$timedate= "(DATE_FORMAT(`tblpaymentTransaction`.`dateTakingLiability`,'%Y-%m-%d') BETWEEN '$fro' AND '$to')";
	//$timedate= " DATE_FORMAT(`tblpaymentTransaction`.`dateTakingLiability`,'%Y-%m') ='$yearmoth'";
	$timedatetodate= " DATE_FORMAT(`tblpaymentTransaction`.`dateTakingLiability`,'%Y-%m-%d') <='$to'";
	$ydate= " DATE_FORMAT(`tblpaymentTransaction`.`dateTakingLiability`,'%Y') ='$period'";
	$preperiod=$period-1;
	// replace ,0 as preval with (SELECT `allocationValue` FROM `tblbudget` prebudget WHERE prebudget.`Period`='$preperiod' and `economicCodeID`) as preval later
	$ret="SELECT *
	,(SELECT `allocation` FROM `tblallocation_type` WHERE `tblallocation_type`.`ID`=`tblbudget`.`allocationType`) as allocationsource
	, (SELECT `allocationValue` FROM `tblbudget` prebudget WHERE prebudget.`Period`='2019' and prebudget.`economicCodeID`=`tblbudget`.`economicCodeID`) as preval
	,(SELECT `contractType` FROM `tblcontractType` WHERE `tblcontractType`.`ID`=`tblbudget`.`economicGroupID`) as economicgroup 
	,(SELECT `economicHead` FROM `tbleconomicHead` WHERE `tbleconomicHead`.`ID`=`tblbudget`.`economicHeadID`) as economichead
	,(SELECT `Code` FROM `tbleconomicHead` WHERE `tbleconomicHead`.`ID`=`tblbudget`.`economicHeadID`) as economicheadcode
	,(SELECT `economicCode` FROM `tbleconomicCode` WHERE `tbleconomicCode`.`ID`=`tblbudget`.`economicCodeID`) as economiccode
	,(SELECT `description` FROM `tbleconomicCode` WHERE `tbleconomicCode`.`ID`=`tblbudget`.`economicCodeID`) as economicdisc
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and month(str_to_date(left(`month`,3),'%b'))<=month(str_to_date(left('$tomonth',3),'%b'))) as receivedallocation
	,(SELECT IFNULL(sum(`amount`),'0') FROM `tblmonthlyAllocation` WHERE `tblmonthlyAllocation`.`economicID`=`tblbudget`.`economicCodeID` and `year`='$period' and `status`='1' and month(str_to_date(left(`month`,3),'%b'))=month(str_to_date(left('$tomonth',3),'%b'))) as receivedthismonth
	,(SELECT IFNULL( sum( `totalPayment` ) , 0 ) FROM `tblpaymentTransaction` 
	WHERE `tblpaymentTransaction`.`status`>'1' 
	and `tblpaymentTransaction`.`economicCodeID` =`tblbudget`.`economicCodeID` and  $timedate) 
	as expenditurebtw
	,(SELECT IFNULL( sum( `totalPayment` ) , 0 ) FROM `tblpaymentTransaction` 
	WHERE `tblpaymentTransaction`.`status`>'1' 
	and `tblpaymentTransaction`.`economicCodeID` =`tblbudget`.`economicCodeID` and  $timedatetodate and $ydate) 
	as expendtodate
	
	FROM `tblbudget` WHERE `Period`='$period' and $qallocationsource and  $qbudgettype and $qeconomichead and $qeconomiccode order by `economicGroupID`,`economicHeadID`,`economicCodeID`";
	//die($ret);
	$List= DB::Select($ret);
	return $List;
	
	}
}
