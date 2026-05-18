<?php

namespace App\Http\Controllers\funds;

use Illuminate\Http\Request;
use App\Http\Requests;
use Session;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session as FacadesSession;

class BasicParameterController extends functionController
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->middleware('auth');
        $this->username = FacadesSession::get('userName');
            return $next($request);
        });
    } //

    public function Usermanagement(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $data['names'] = $request['names'];
        $data['email'] = $request['email'];
        $data['role'] = $request['role'];
        $data['password'] = $request['password'];
        $data['status'] = $request['status'];
        $data['id'] = $request['id'];

        if (isset($_POST['edit'])) {
            $this->validate($request, [
                'name'             => 'required|regex:/^[\pL\s\-]+$/u',
                'email'                  => 'required|email',
                'role'               => 'required',
                'status'               => 'required',
            ]);
            DB::table('users')->where('id', $request['id'])->update([
                'name' => $request['name'],
                'email' => $request['email'],
                'status' => $request['status'],
            ]);

            if ($request['password'] != '') {
                $this->validate($request, [
                    'email'                  => 'required|email',
                    'password'               => 'required|min:5',
                    'role'               => 'required',
                ]);
                DB::table('users')->where('id', $request['id'])->update([
                    'password' => bcrypt($request['password']),
                ]);
            }
            DB::table('assign_user_role')->where('userID', $request['id'])->update([
                'roleID' => $request['role'],
            ]);
            if ($request['status'] == '0') {
                DB::table('users')->where('id', $request['id'])->update([
                    'password' => 0,
                ]);
            }

            return back()->with('msg', 'User Profile successfully updated!');
        }
        $data['Rolelist'] = $this->Rolelist();
        $data['Statuslist'] = $this->Statuslist();
        $data['UserList'] = $this->UserLists();
        return view('auth.user_update', $data);
    }


    public function BankController(Request $request)
    {
        //($this->VnextNo());
        ///dd(DB::table('tblpaymentTransaction')->orderBy('vref_no', 'DESC')->value('vref_no'));
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        if (isset($_POST['add'])) {
            $this->validate($request, ['bank'          => 'required']);
            DB::table('tblbanklist')->insert(['bank' => $request['bank'],]);
            return back()->with('message', 'addedd  successfully added.');
        }
        $data['banklist'] = DB::table('tblbanklist')->orderby('bank')->get();
        return view('basicparameter.bank', $data);
    }

    public function StaffAccountUpdateController(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $data['staffid'] = $request['staffid'];
        $data['bank'] = $request['bank'];
        $data['accountno'] = $request['accountno'];
        if (isset($_POST['update'])) {
            //DD($request['staffid']);
            $this->validate($request, ['staffid'          => 'required', 'bank'          => 'required', 'accountno'          => 'required']);
            //DD($request['staffid']);
            DB::table('tblStaffInformation')->where('staffID', $request['staffid'])->update(['bankID' => $request['bank'], 'account_no' => $request['accountno']]);
            //DB::table('tblbanklist')->insert([ 'bank' => $request['bank'],]);
            return back()->with('message', 'addedd  successfully added.');
        }
        $data['selectedStaffInformation'] = db::table('tblStaffInformation')->where('staffID', $request['staffid'])->first();
        if ($data['selectedStaffInformation']) {
            $data['bank'] = $data['selectedStaffInformation']->bankID;
            $data['accountno'] = $data['selectedStaffInformation']->account_no;
        }

        $data['banklist'] = db::table('tblbanklist')->orderby('bank')->get();
        $data['StaffInformation'] = db::table('tblStaffInformation')->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblStaffInformation.bankID')->where('active', 1)->orderby('full_name')->get();
        return view('basicparameter.staffaccount', $data);
    }
    public function getDepartment(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['department'] = "";
        $data['court'] = "";
        $court = trim($request['court']);
        $data['success'] = "";

        $data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
        $data['DepartmentList'] = $this->DepartmentList($court);
        $data['showcourt'] = true;
        if ($this->UserType($this->username) == 'NONTECHNICAL') {
            $data['showcourt'] = false;
            $data['court'] = $this->StaffCourt($this->username);
            $data['courtname'] = $this->CourtName($this->StaffCourt($this->username));
        }
        return view('basicparameter.department', $data);
    }



    public function postDepartment(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['department'] = trim($request['department']);
        $data['court'] = trim($request['court']);
        $court = trim($request['court']);
        $del = trim($request['delcode']);
        $department = trim($request['department']);
        $data['success'] = "";
        $data['showcourt'] = true;
        if ($this->UserType($this->username) == 'NONTECHNICAL') {
            $data['showcourt'] = false;
            $data['court'] = $this->StaffCourt($this->username);
            $data['courtname'] = $this->CourtName($this->StaffCourt($this->username));
        }
        $data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
        $data['DepartmentList'] = $this->DepartmentList($court);
        DB::Delete("DELETE FROM `tbldepartment` WHERE `id`='$del'");
        $updatedby = $this->username;

        if ($this->ConfirmDepartment($court, $department)) {
            $data['warning'] = "$department  section already exist with the selected court";
            return view('basicparameter.department', $data);
        }

        if (isset($_POST['add'])) {
            $this->validate($request, [
                'department'          => 'required',
                'court'                 => 'required'

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
        if (session::get('departmentID', 'CourtID') == "") {
            $department = trim($request['department']);
            $court = trim($request['court']);
        } else {
            $department = session::get('departmentID');
            $court = session::get('CourtID');
        }
        $data['success'] = "";
        $data['showcourt'] = true;
        if ($this->UserType($this->username) == 'NONTECHNICAL') {
            $data['showcourt'] = false;
            $data['court'] = $this->StaffCourt($this->username);
            $data['courtname'] = $this->CourtName($this->StaffCourt($this->username));
        }
        $courtID = DB::table('tbl_court')->get();
        $data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();

        //$data['DesignationList'] = $this->DesignationList($court);

        $data['DesignationList'] = DB::table('tbldesignation')
            ->join('tbl_court', 'tbl_court.id', '=', 'tbldesignation.courtID')
            ->join('tbldepartment', 'tbldepartment.id', '=', 'tbldesignation.departmentID')
            ->where('tbldesignation.courtID', '=', $court)
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
        if (session::get('departmentID', 'CourtID') == "") {
            $department = trim($request['department']);
            $court = trim($request['court']);
        } else {
            $department = session::get('departmentID');
            $court = session::get('CourtID');
        }

        $data['department'] = $department;
        $data['grade'] = trim($request['grade']);
        $grade = trim($request['grade']);
        $data['designation'] = trim($request['designation']);
        $designation = trim($request['designation']);

        $data['success'] = "";

        if ($this->UserType($this->username) == 'NONTECHNICAL') {
            $data['showcourt'] = false;
            $data['court'] = $this->StaffCourt($this->username);
            $data['courtname'] = $this->CourtName($this->StaffCourt($this->username));
        }
        $data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
        $data['DepartmentList'] = $this->DepartmentList($court);

        $del = trim($request['delcode']);
        DB::DELETE("DELETE FROM `tblPost` WHERE `postID`='$del'");
        $data['DesignationList'] = $this->DesignationList($court);
        session::forget('departmentID');
        session::forget('CourtID');
        if (isset($_POST['add'])) {

            $this->validate($request, [
                'department'          => 'required',
                'grade'          => 'required',
            ]);

            if ($this->ConfirmGrade2($grade, $department, $designation))

            // DB::table('tblPost')->where('grade',$ID)->update(['Post' => $designation]);

            {
                $data['warning'] = "$designation or Grade $grade  already exist with the selected department";
                return view('basicparameter.designation', $data);
            } else {
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

        if (session::get('departmentID', 'CourtID') == "") {
            $department = trim($request['department']);
            $court = trim($request['court']);
        } else {
            $department = session::get('departmentID');
            $court = session::get('CourtID');
        }
        $data['designation'] = trim($request['designation']);
        $data['level'] = trim($request['level']);
        $data['court'] = trim($request['court']);

        $designation = trim($request['designation']);
        $data['department'] = trim($request['department']);
        $level = trim($request['level']);
        $data['success'] = "";
        $data['showcourt'] = true;
        $del = trim($request['delcode']);
        if ($this->UserType($this->username) == 'NONTECHNICAL') {
            $data['showcourt'] = false;
            $data['court'] = $this->StaffCourt($this->username);
            $data['courtname'] = $this->CourtName($this->StaffCourt($this->username));
        }
        $data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();

        DB::DELETE("DELETE FROM `tbldesignation` WHERE `id`='$del'");
        $data['DesignationList'] = $this->DesignationList($court);


        $updatedby = $this->username;
        $this->validate($request, [
            'designation'          => 'required',
            'court'                 => 'required',
        ]);
        if ($this->ConfirmDesignation($court, $designation)) {
            $data['warning'] = "$designation designation already exist with the selected court";
            return view('basicparameter.designation', $data);
        }
        DB::insert("INSERT INTO `tbldesignation`(`courtID`, `designation`,`departmentID`,`grade`) VALUES ('$court','$designation','$department','$level')");
        $data['DesignationList'] = $this->DesignationList($court);
        $data['designation'] = "";
        $data['success'] = "$designation section successfully updated";





        $data['DesignationList'] = DB::table('tbldesignation')
            ->join('tbl_court', 'tbl_court.id', '=', 'tbldesignation.courtID')
            ->join('tbldepartment', 'tbldepartment.id', '=', 'tbldesignation.departmentID')
            ->where('tbldesignation.courtID', '=', $court)
            ->orderBy('tbldesignation.grade', 'desc')
            ->get();
        //dd($data['DesignationList']);


        return view('basicparameter.designation', $data);
    }


    public function updateDesignation(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $CourtID = trim($request['CourtID']);
        $department = trim($request['DeptID']);
        Session::put('DepartmentID', $department);
        Session::put('CourtID', $CourtID);
        $designation = trim($request['designation']);
        $PostID = trim($request['PostID']);

        DB::table('tbldesignation')->where('id', $PostID)->update(['designation' => $designation]);
        //$data['success'] = "$designation section successfully Updated";
        //$data['DesignationList'] = $this->DesignationList2($department);
        //$data['DeptList'] = $this->DeptList2();
        //return view('basicparameter.designation', $data);
        return redirect('basic/designation')->with('message', ' successfully updated');;
    }

    public function deletePost(Request $request)
    {

        $postID = trim($request['PostID']);

        $department = trim($request['depty']);
        $court = trim($request['courty']);
        Session::put('CourtID', $court);


        DB::table('tbldesignation')->where('id', $postID)->delete();

        return redirect('basic/designation')->with('message', 'Post successfully deleted');
    }


    public function companyInfo(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $shortcode = trim($request['shortcode']);
        $data['shortcode'] = $shortcode;
        $organisationname = trim($request['organisationname']);
        $data['organisationname'] = $organisationname;
        $phoneno = trim($request['phoneno']);
        $data['phoneno'] = $phoneno;
        $email = trim($request['email']);
        $data['email'] = $email;
        $address = trim($request['address']);
        $data['address'] = $address;
        $data['imgpath'] = $address;




        if (isset($_POST['update'])) {
            if (DB::table('tblcompany')->get()) {
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
        $court = trim($request['court']);
        $del = trim($request['delcode']);
        $division = trim($request['division']);
        $data['success'] = "";
        $data['showcourt'] = true;
        if ($this->UserType($this->username) == 'NONTECHNICAL') {
            $data['showcourt'] = false;
            $data['court'] = $this->StaffCourt($this->username);
            $data['courtname'] = $this->CourtName($this->StaffCourt($this->username));
        }
        $data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
        $data['DivisionList'] = $this->DivisionList1($court);
        DB::Delete("DELETE FROM `tbldepartment` WHERE `id`='$del'");
        //$updatedby = $this->username;



        if (isset($_POST['add'])) {
            $this->validate($request, [
                'division'          => 'required',
                'court'                 => 'required'

            ]);
            if ($this->ConfirmDivision($court, $division)) {
                $data['warning'] = "$division already exist with the selected court";
                return view('basicparameter.division', $data);
            }
            DB::insert("INSERT INTO `tbldivision`( `division`, `courtID`) VALUES ('$division ','$court')");
            $data['DivisionList'] = $this->DivisionList1($court);
            $data['division'] = "";
            $data['success'] = "$division section successfully updated";
            return view('basicparameter.division', $data);
        }

        $data['DesignationList'] = DB::table('tbldesignation')
            ->join('tbl_court', 'tbl_court.id', '=', 'tbldesignation.courtID')
            ->join('tbldepartment', 'tbldepartment.id', '=', 'tbldesignation.departmentID')
            ->where('tbl_court.id', '=', $court)
            ->get();

        $data['DivisionList'] = $this->DivisionList1($court);
        return view('basicparameter.division', $data);
    }


    public function ControlVariable(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";


        $data['showcourt'] = true;



        if (session::get('CourtID') == "" and session::get('DepartmentIDID') == "") {
            $department = trim($request['department']);
            $court = trim($request['court']);
        } else {

            $court = session::get('CourtID');
            $department = session::get('DepartmentID');
        }


        $data['court'] = $court;

        $data['department'] = $department;
        $level = trim($request['level']);
        $data['level'] = $level;
        $designation = trim($request['designation']);
        $data['designation'] = $designation;

        $del = trim($request['delcode']);

        $data['success'] = "";
        $data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
        $data['DesignationList'] = $this->DesignationList2($court, $department);

        $data['DepartmentList'] = $this->DepartmentList($court);
        session::forget('CourtID');

        //$data['DesignationList'] = DB::table('tbldesignation')
        //->join('tbl_court','tbl_court.id','=','tbldesignation.courtID')
        //->join('tbldepartment','tbldepartment.id','=','tbldesignation.departmentID')
        //->where('tbldesignation.courtID','=',$court)
        //->get();


        if (isset($_POST['add'])) {

            $this->validate($request, [
                'department'          => 'required',
                'level'          => 'required',
            ]);

            if ($this->ConfirmGrade2($level, $department, $designation, $court))

            // DB::table('tblPost')->where('grade',$ID)->update(['Post' => $designation]);

            {
                $data['warning'] = "$designation or Grade $level already exist with the selected department";
                return view('basicparameter.designation', $data);
            } else {
                DB::insert("INSERT INTO `tbldesignation`( `courtId`,`departmentId`,`grade`,`designation`) VALUES ('$court','$department','$level','$designation')");
                $data['DesignationList'] = $this->DesignationList2($court, $department);
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
        $username = $request['username'];
        $data['username'] = $username;
        $designation = $request['designation'];
        $data['designation'] = $designation;
        if (isset($_POST['update'])) {

            DB::update("UPDATE `tblaction_rank` SET `userid`='$username' WHERE `code`='$designation' ");
            $data['success'] = "Information successfully updated";
        }

        $data['DesignationList'] = $this->RankDesignationList();
        $data['UserList'] = $this->UserList();
        return view('funds.basicparameter.actiondesignation', $data);

        die("In use");
        $rawdata = DB::SELECT("SELECT * FROM `TABLE 96`  ");
        foreach ($rawdata as $value) {
            //If($value->fileNo!=''){
            DB::table('tblper110219')->where('ID', $value->ID)->update([
                'AccNo'         => $this->AccountFormat(trim($value->AccNo)),
                'bankID'         => $this->BankCode(trim($value->BankName)),
                'BankName'         => $value->BankName
            ]);
            //}
        }
        die("Accounyt datails Update");
        $rawdata = DB::SELECT("SELECT * FROM `TABLE 98`  ");
        foreach ($rawdata as $value) {
            //If($value->fileNo!=''){
            DB::table('tblperformatted')->where('ID', $value->ID)->update([
                'fileNo'         => $value->StaffNo,
                'surname'         => $value->surname,
                'first_name'         => $value->first_name,
                'othernames'         => $value->othernames,
                'grade'         => $value->grade,
                'step'         => $value->step,
                'appointment_date'         => $value->appointment_date,
                'incremental_date'         => $value->incremental_date,
                'date_present_appointment'         => $value->date_present_appointment,
                'dob'         => $value->dob,
                'AccNo'         => $value->AccNo,
                'BankName'         => $value->BankName
            ]);
            //}
        }
        die("finish grade step");


        $rawdata = DB::SELECT("SELECT * FROM `TABLE96`  ");
        foreach ($rawdata as $value) {
            //If($value->fileNo!=''){
            DB::table('tblperformatted')->where('ID', $value->ID)->update([
                'fileNo'         => $value->fileNo,
                'surname'         => $value->surname,
                'first_name'         => $value->first_name,
                'othernames'         => $value->othernames,
                'grade'         => $value->grade,
                'step'         => $value->step,
                'appointment_date'         => $value->appointment_date,
                'incremental_date'         => $value->incremental_date,
                'date_present_appointment'         => $value->date_present_appointment,
                'dob'         => $value->dob,
                'AccNo'         => $value->AccNo,
                'BankName'         => $value->BankName
            ]);
            //}
        }


        die("Service in used");
        die("inused");
        $rawdata = DB::SELECT("SELECT * FROM `TABLE91` WHERE `ID`=''  ");
        foreach ($rawdata as $value) {


            DB::table('tblperformatted')->insert(array(
                'fileNo'         => $value->fileNo,
                'surname'         => $value->surname,
                'first_name'         => $value->first_name,
                'othernames'         => $value->othernames,
                'grade'         => $value->grade,
                'step'         => $value->step,
                'appointment_date'         => $value->appointment_date,
                'incremental_date'         => $value->incremental_date,
                'date_present_appointment'         => $value->date_present_appointment,
                'dob'         => $value->dob,
                'AccNo'         => $value->AccNo

            ));
        }
        die("Done");
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $rawdata = DB::SELECT("SELECT * FROM `rawnonimal`  ");
        foreach ($rawdata as $value) {


            DB::table('tblper2')->insert(array(
                'title'         => $this->Titlecode($value->title),
                'surname'         => $value->lastname,
                'first_name'         => $value->middlename,
                'othernames'         => $value->firstname

            ));
        }
        die("kdkd");
        $rawdata = DB::SELECT("SELECT * FROM `rawnonimal`  ");
        foreach ($rawdata as $value) {
            $dataval = str_replace("  ", " ", trim($value->names));
            $dataval = str_replace("  ", " ", $dataval);
            $dataval = str_replace(",", "", $dataval);
            $dataval = str_replace(".", "", $dataval);
            $Arrayval = explode(" ", $dataval);
            echo sizeof($Arrayval);
            $lastname = '';
            $middlename = '';
            $firstname = '';
            $title = '';
            switch (sizeof($Arrayval)) {
                case 0:
                    break;
                case 1:
                    if ($this->isTitle($Arrayval[0])) {
                        $title = $Arrayval[0];
                    } {
                        $lastname = $Arrayval[0];
                    }
                    break;
                case 2:
                    if ($this->isTitle($Arrayval[0])) {
                        $title = $Arrayval[0];
                    } else {
                        $lastname = $Arrayval[0];
                    }
                    if ($this->isTitle($Arrayval[1])) {
                        $title = $Arrayval[1];
                    } else {
                        $middlename = $Arrayval[1];
                    }
                    break;
                case 3:
                    if ($this->isTitle($Arrayval[0])) {
                        $title = $Arrayval[0];
                    } else {
                        $lastname = $Arrayval[0];
                    }
                    if ($this->isTitle($Arrayval[1])) {
                        $title = $Arrayval[1];
                    } else {
                        $middlename = $Arrayval[1];
                    }
                    if ($this->isTitle($Arrayval[2])) {
                        $title = $Arrayval[2];
                    } else {
                        $firstname = $Arrayval[2];
                    }

                    break;
                case 4:
                    if ($this->isTitle($Arrayval[0])) {
                        $title = $Arrayval[0];
                    } else {
                        $lastname = $Arrayval[0];
                    }
                    if ($this->isTitle($Arrayval[1])) {
                        $title = $Arrayval[1];
                    } else {
                        $middlename = $Arrayval[1];
                    }
                    if ($this->isTitle($Arrayval[2])) {
                        $title = $Arrayval[2];
                    } else {
                        $firstname = $Arrayval[2];
                    }
                    if ($this->isTitle($Arrayval[3])) {
                        $title = $Arrayval[3];
                    }
                    //else{$title=$Arrayval[3];}
                    break;
            }
            DB::table('rawnonimal')->where('id', $value->id)->update([
                'lastname'         => $lastname,
                'middlename'         => $middlename,
                'firstname'         => $firstname,
                'title'         => $title,
            ]);
        }
        die();
        $username = $request['username'];
        $data['username'] = $username;
        $designation = $request['designation'];
        $data['designation'] = $designation;
        if (isset($_POST['update'])) {

            DB::update("UPDATE `tblaction_rank` SET `userid`='$username' WHERE `code`='$designation' ");
            $data['success'] = "Information successfully updated";
        }

        $data['DesignationList'] = $this->RankDesignationList();
        $data['UserList'] = $this->UserList();
        return view('funds.basicparameter.actiondesignation', $data);
    }

    public function AccountStaffUpdate(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";





        $rawdata = DB::SELECT("SELECT * FROM `rawnonimal`  ");
        foreach ($rawdata as $value) {


            DB::table('tblper')->insert(array(

                'title'         => $value->title,
                'surname'         => $value->lastname,
                'first_name'         => $value->firstname,
                'othernames'         => $value->middlename

            ));
        }
        die();
        $rawdata = DB::SELECT("SELECT * FROM `rawnonimal`  ");
        foreach ($rawdata as $value) {
            $dataval = str_replace("  ", " ", trim($value->names));
            $dataval = str_replace("  ", " ", $dataval);
            $dataval = str_replace(",", "", $dataval);
            $dataval = str_replace(".", "", $dataval);
            $Arrayval = explode(" ", $dataval);
            echo sizeof($Arrayval);
            $lastname = '';
            $middlename = '';
            $firstname = '';
            $title = '';
            switch (sizeof($Arrayval)) {
                case 0:
                    break;
                case 1:
                    if ($this->isTitle($Arrayval[0])) {
                        $title = $Arrayval[0];
                    } {
                        $lastname = $Arrayval[0];
                    }
                    break;
                case 2:
                    if ($this->isTitle($Arrayval[0])) {
                        $title = $Arrayval[0];
                    } else {
                        $lastname = $Arrayval[0];
                    }
                    if ($this->isTitle($Arrayval[1])) {
                        $title = $Arrayval[1];
                    } else {
                        $middlename = $Arrayval[1];
                    }
                    break;
                case 3:
                    if ($this->isTitle($Arrayval[0])) {
                        $title = $Arrayval[0];
                    } else {
                        $lastname = $Arrayval[0];
                    }
                    if ($this->isTitle($Arrayval[1])) {
                        $title = $Arrayval[1];
                    } else {
                        $middlename = $Arrayval[1];
                    }
                    if ($this->isTitle($Arrayval[2])) {
                        $title = $Arrayval[2];
                    } else {
                        $firstname = $Arrayval[2];
                    }

                    break;
                case 4:
                    if ($this->isTitle($Arrayval[0])) {
                        $title = $Arrayval[0];
                    } else {
                        $lastname = $Arrayval[0];
                    }
                    if ($this->isTitle($Arrayval[1])) {
                        $title = $Arrayval[1];
                    } else {
                        $middlename = $Arrayval[1];
                    }
                    if ($this->isTitle($Arrayval[2])) {
                        $title = $Arrayval[2];
                    } else {
                        $firstname = $Arrayval[2];
                    }
                    if ($this->isTitle($Arrayval[3])) {
                        $title = $Arrayval[3];
                    }
                    //else{$title=$Arrayval[3];}
                    break;
            }
            DB::table('rawnonimal')->where('id', $value->id)->update([
                'lastname'         => $lastname,
                'middlename'         => $middlename,
                'firstname'         => $firstname,
                'title'         => $title,
            ]);
            //echo $Arrayval[0];
            //dd($Arrayval);
            //echo $value->names;
        }
        die();
    }
    public function Setting(Request $request)
    {

        //DB::table('tblcontractDetails20211201')->where('createdby',266)->select('ID'->get();
        $comment_ids = array_map(
            function ($comment) {
                return $comment->ID;
            },
            Collection(DB::table('tblcontractDetails20211201')->where('createdby', 266)->get())
            //Comment::where("post_id", $this->id)->get()->toArray()
        );
        //dd($comment_ids);

        $return = array_map(
            static function ($comment_id) {
                // DB::table('tblcontractDetails20211201')->where('ID',$comment_id)->first();
                //Comment::find($comment_id);
                $comment_id;
            },
            $comment_ids
        );
        dd($return);



        die("No action perform");
        $rawdata = DB::SELECT("SELECT * FROM `tblper20200609` WHERE `employee_type`<>2 and staff_status=1  ");
        foreach ($rawdata as $value) {

            if (!DB::table('tblStaffInformation2')->where('fileNo', $value->fileNo)->update([
                'perstaffid'         => $value->ID,
                'full_name'         => $value->surname . ' ' . $value->first_name . ' ' . $value->othernames,
                'departmentID'         => $value->department,
                'bankID'         => $value->bankID,
                'account_no'         => $value->AccNo,
                'sort_code'         => '0000'
            ]))

                DB::table('tblStaffInformation2')->insert(array(
                    'fileNo'         => $value->fileNo,
                    'perstaffid'         => $value->ID,
                    'full_name'         => $value->surname . ' ' . $value->first_name . ' ' . $value->othernames,
                    'departmentID'         => $value->department,
                    'bankID'         => $value->bankID,
                    'account_no'         => $value->AccNo,
                    'sort_code'         => '0000'

                ));
        }
        die("Process complete");
    }
}
