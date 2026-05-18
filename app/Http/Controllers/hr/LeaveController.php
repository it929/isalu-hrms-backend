<?php

namespace App\Http\Controllers\hr;



use Illuminate\Http\Request;
//use Auth;
use App\Http\Requests;
use DB;
use Auth;
use Illuminate\Support\Facades\Session;

class LeaveController extends functionController
{
	public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->middleware('auth');
        $this->username = Session::get('userName');
            return $next($request);
        });
    }


    public function getNotification(Request $request)
   {
	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	   $staffid=$this->StaffID($this->username);
	   $data['Notification']=$this->SelfNotification($staffid);
   	return view('hr.Leave.notification', $data);
   }
   public function getDefinition(Request $request)
   {
	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['dependant'] = "";
	   $data['grade'] = "";
	   $data['ald'] = "";
	   $data['gender'] = "";
	   $data['success'] = "";

        $courtid= Auth::user()->courtID;//$this->StaffCourt($this->username);
	    $data['LeaveGradetList'] = DB::table('tblgrade_leave_assignment')->where('courtID', $courtid)->get();
   	return view('hr.Leave.gradeleavedefinition', $data);
   }



   public function postDefinition(Request $request)
   {
   	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['grade'] =trim($request['grade']);
	   $data['ald'] = trim($request['ald']);
	   $data['success'] = "";
	   $delcode=trim($request['delcode']);

	  DB::DELETE ("DELETE FROM `tblgrade_leave_assignment` WHERE `id`='$delcode'");

	   $courtid= Auth::user()->courtID;//$this->StaffCourt($this->username);

		$data['LeaveGradetList']=$this->LeaveGradetList($courtid) ;
		$updatedby = $this->username;

		$grade=trim($request['grade']);
		$ald=trim($request['ald']);
		if ( isset( $_POST['Update'] ) ) {
		$this->validate($request, [
		'grade'      	=> 'required',
		'ald'      	=> 'required',
		]);
			if ($this->ConfirmGradeLeave($courtid,$grade))
			{
				DB::UPDATE ("UPDATE `tblgrade_leave_assignment` SET `noOfDays`='$ald' WHERE `courtID`='$courtid' and `grade`='$grade'");
			}
			else
			{
				DB::INSERT ("INSERT INTO `tblgrade_leave_assignment`(`courtID`, `grade`, `noOfDays`) VALUES ('$courtid','$grade','$ald')");
			}

			$data['success'] = "successfully updated";
			$data['grade'] = "";
			$data['ald'] = "";
			$data['LeaveGradetList']=$this->LeaveGradetList($courtid) ;
			return view('hr.Leave.gradeleavedefinition', $data);
		 }
   	return view('hr.Leave.gradeleavedefinition', $data);
   }
   public function Application(Request $request)
   {
	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	   $data['RstaffAction'] = "";
	   $data['status'] = "";
	   $data['startdate'] = trim($request['startdate']);
	   $data['enddate'] = trim($request['enddate']);
	   $data['nod'] = trim($request['nod']);
	   $data['leaveType'] = trim($request['leaveType']);
	   $data['releavestaff'] = trim($request['releavestaff']);
	   $data['purpose'] = trim($request['purpose']);
	   $data['address'] = trim($request['address']);
	   $startdate = trim($request['startdate']);
	   $enddate = trim($request['enddate']);
	   $nod = trim($request['nod']);
	   $leaveType = trim($request['leaveType']);
	   $releavestaff = trim($request['releavestaff']);
	   $purpose = trim($request['purpose']);
	   $address = trim($request['address']);
	   $viewnewid= trim($request['viewnewid']);



	   if ( isset( $_POST['reset'] ) ) {
	   $data['startdate'] = "";
	   $data['enddate'] = "";
	   $data['nod'] = "";
	   $data['leaveType'] = "";
	   $data['releavestaff'] = "";
	   $data['purpose'] = "";
	   $data['address'] = "";
	   $data['viewid'] = "";
	   $viewid='';
	   }
	   else
	   {
	   $data['viewid'] = trim($request['viewid']);
	   $viewid= trim($request['viewid']);
	   $leaveData=$this->AppliedLeave($viewid);
	   if ($leaveData){
	   $data['RstaffAction'] = $leaveData[0]->RstaffAction;
	   $data['status'] = $leaveData[0]->status;
	   }
	   }
	   if($viewid!='' && $viewnewid!='')
	   {
	   $leaveData=$this->AppliedLeave($viewid);
	   if ($leaveData){
	   $startdate = $leaveData[0]->startDate;
	   $enddate = $leaveData[0]->endDate;
	   $nod = $leaveData[0]->noOfDays;
	   $leaveType = $leaveData[0]->leaveType;
	   $releavestaff = $leaveData[0]->releavingStaff;
	   $purpose = $leaveData[0]->purpose;
	   $address = $leaveData[0]->addressDuringLeave;
	   $data['RstaffAction'] = $leaveData[0]->RstaffAction;
	   $data['status'] = $leaveData[0]->status;
	   $data['startdate'] = $startdate;
	   $data['enddate'] = $enddate;
	   $data['nod'] = $nod;
	   $data['leaveType'] = $leaveType;
	   $data['releavestaff'] = $releavestaff;
	   $data['purpose'] = $purpose;
	   $data['address'] = $address;
	   }
	   }


	   $courtid= $this->StaffCourt($this->username);
	   $staffgrade=$this->StaffGradeLevel($this->username);
	  $data['staffDetails']= $this->StaffDetails($this->username);
	  $staffid=$this->StaffID($this->username);
	  $data['LeaveTypeList']= $this->LeaveType();
	   $period =$this->LeavePeriod();
	   $data['period'] =$period;
	   //$data['LeaveGradetList']=$this->LeaveGradetList($courtid) ;
	   $data['totalAllowable'] = $this->leaveEntitle($courtid,$staffgrade);
	   $data['dayRem'] = $this->leaveRemain($data['totalAllowable'],$data['period'],$staffid);
	   $section=$this->StaffSection($this->username);
	   $data['ReleaveStaff']= DB::Select("SELECT * FROM `tblper` WHERE `section`='$section' and `courtID`='$courtid'" );//and `divisionID`=''
	   if ( isset( $_POST['Save'] ) ) {
	   	$validity="yes";
		$data['responsetype']="Warning";
		switch($validity){
		case "yes":
		$result = DB::select("SELECT * FROM `tblstaff_leave` WHERE `staffID`='$staffid' and `status`='Pending'");

	if($result)
	{
		$data['warning']="You still have a pending leave request";
		break;
	}
	if($staffid==''){$data['warning']="You are not legible to apply for leave";break; }
	if((int)$data['dayRem']<(int)$nod){$data['warning']="You cannot apply for leave above what you have left";break; }
	if($startdate==''){$data['warning']="When are you starting the leave";break; }
	if($enddate==''){$data['warning']="When is your leave going to end";break; }
	if($nod==''){$data['warning']="How many days of leave applying for";break; }
	if($leaveType==''){$data['warning']="YSelect the type of the leave applying for";break; }
	if($releavestaff==''){$data['warning']="You must indicate who is going to releave you during the leave period";break; }
	if($address==''){$data['warning']="Please indicate your address during the leave period";break; }
	if($purpose==''){$data['warning']="Please provide purpose for the leave applied";break; }

		$updated_date	= date('Y-m-d');
		   DB::INSERT ("INSERT INTO `tblstaff_leave`
		   (`staffID`, `startDate`, `endDate`, `noOfDays`, `leaveType`, `releavingStaff`,  `status`, `period`, `addressDuringLeave`, `purpose`, `updateTime`, `courtID`)
		   VALUES ('$staffid','$startdate','$enddate','$nod','$leaveType','$releavestaff','Pending','$period','$address','$purpose','$updated_date','$courtid')" );
		   $leaveid=$this->leaveID($staffid,$releavestaff);

		   DB::INSERT ("INSERT INTO `tblnotification`(`url`, `actionID`, `dueDate`, `transDate`, `status`, `createBy`, `notificationDesription`, `staffid`)
		   VALUES ('/self-service/releaveaction','$leaveid','$updated_date','$updated_date','Pending','$staffid','Leave Releave','$releavestaff')" );

		   $data['dayRem'] = $this->leaveRemain($data['totalAllowable'],$data['period'],$staffid);
		   $data['viewid'] ="";
		   $data['startdate'] = "";
		   $data['enddate'] = "";
		   $data['nod'] = "";
		   $data['leaveType'] = "";
		   $data['releavestaff'] = "";
		   $data['purpose'] = "";
		   $data['address'] = "";
		   $data['viewid'] = "";
		   }
	   }
	 $data['LeaveHistory'] = DB::select("SELECT *
	 ,(SELECT `leaveType` FROM `tblleave_type` WHERE `tblleave_type`.`id`=`tblstaff_leave`.`leaveType` ) as LeavesType
	 ,(SELECT CONCAT(`fileNo`,': ',`surname`,' ',`first_name`,' ',`othernames`) FROM `tblper` WHERE `tblper`.`ID`=`tblstaff_leave`.`releavingStaff`) as Rstaff FROM `tblstaff_leave` WHERE `staffID`='$staffid'");

	 $data['AppliedLeave']=$this->AppliedLeave($viewid);
   	return view('hr.Leave.leaveapplication', $data);
   }

public function ReleaveResponse(Request $request)
   {
	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	   $data['leaveid'] = trim($request['leaveid']);
	   $data['remarks'] = trim($request['remarks']);
	   $remarks= trim($request['remarks']);
	   $leaveid=trim($request['leaveid']);
	   $leaveData=$this->AppliedLeave($leaveid);
	   if ($leaveData){
	   if ( isset( $_POST['accept'] ) ) {
	   DB::UPDATE ("UPDATE `tblstaff_leave` SET `RstaffAction`='Accept', `RStaffComment`='$remarks' where `id`='$leaveid'");
	   DB::UPDATE ("UPDATE `tblnotification` SET `status`='Complete' WHERE `url`='/self-service/releaveaction' and `actionID`='$leaveid'");
	   }
	   if ( isset( $_POST['reject'] ) ) {
	   DB::UPDATE ("UPDATE `tblstaff_leave` SET `RstaffAction`='Reject', `RStaffComment`='$remarks' where `id`='$leaveid'");
	   DB::UPDATE ("UPDATE `tblnotification` SET `status`='Complete' WHERE `url`='/self-service/releaveaction' and `actionID`='$leaveid'");
	   }
	   $data['AppliedLeave'] = $this->AppliedLeave($leaveid);
	   return view('hr.Leave.reaveacceptance', $data);
	   }
	   else
	   {
	   return redirect('/self-service/notification');
	   }



   }
   public function Approval(Request $request)
   {
	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	   $data['leaveid'] = trim($request['leaveid']);
	   $data['remarks'] = trim($request['remarks']);
	   $remarks= trim($request['remarks']);
	   $leaveid=trim($request['leaveid']);
	   $staffid=$this->StaffID($this->username);
	   $leaveData=$this->AppliedLeave($leaveid);
	   if ($leaveData){
	   if ( isset( $_POST['accept'] ) ) {
	   DB::UPDATE ("UPDATE `tblstaff_leave` SET `status`='Approved', `approvalComment`='$remarks',`approvedBy`='$staffid' where `id`='$leaveid'");
	   }
	   if ( isset( $_POST['reject'] ) ) {
	   DB::UPDATE ("UPDATE `tblstaff_leave` SET `status`='Reject', `approvalComment`='$remarks',`approvedBy`='$staffid' where `id`='$leaveid'");
	   }
	   $data['AppliedLeave'] = $this->AppliedLeave($leaveid);
	   return view('hr.Leave.leaveapproval', $data);
	   }
	   else
	   {
	   return redirect('/self-service/notification');
	   }



   }
   public function LeaveQuery(Request $request)
   {
	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	   $court= trim($request['court']);
	   if ($court==""){$court=$this->StaffCourt($this->username);}
	   $period= trim($request['period']);
	   if ($period==""){$period=$this->LeavePeriod();}
	   $data['court'] = $court;
	   $data['period'] = $period;
	   $division= trim($request['division']);
	   $data['division'] = $division;
	   $status= trim($request['status']);
	   $data['status'] = $status;
	   $department = trim($request['department']);
	   $data['department']=$department ;
	   $data['courtList']=$this->CourtList();
	   $data['depatmentList']=$this->DepartmentList($court);
	   $data['Division']=$this->DivisionList($court);
	   $data['LeaveStatus']=$this->LeaveStatus();
	   $data['LeavePeriod']=$this->LeavePeriodList();

	   //die(json_encode($this->LeavesQuery($period,$court,$division,$department,$status)));
	   $data['LeaveQuery']=$this->LeavesQuery($period,$court,$division,$department,$status);

	   return view('hr.Leave.leavequery', $data);


   }


}
