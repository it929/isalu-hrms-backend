<?php

namespace App\Http\Controllers\payroll;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class BasicParameterController extends functionController
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->middleware('auth');
        $this->username = Session::get('userName');
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
            if(DB::table('assign_user_role')->where('userID', $request['id'])->first()){
                DB::table('assign_user_role')->where('userID', $request['id'])->update([
                    'roleID' => $request['role'],
                ]);
            }else{
                DB::table('assign_user_role')->insert([
                'roleID' => $request['role'],
                'userID' => $request['id'],
            ]   );
            }
            
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

    public function getDepartment(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['department'] = "";
        $data['court'] = "";
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }
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
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }
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
        //$data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
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

        $data['error'] = "";
        $data['warning'] = "";
        $data['designation'] = "";
        $data['court'] = "";
        $data['courtList'] =  DB::table('tbl_court')->get();
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        $data['success'] = "";
        $data['showcourt'] = true;
        if ($this->UserType($this->username) == 'NONTECHNICAL') {
            $data['showcourt'] = false;
            $data['court'] = $this->StaffCourt($this->username);
            $data['courtname'] = $this->CourtName($this->StaffCourt($this->username));
        }
        $courtID = DB::table('tbl_court')->get();
        $data['DesignationList'] = DB::table('tbldesignation')
            ->join('tbl_court', 'tbl_court.id', '=', 'tbldesignation.courtID')
            ->join('tbldepartment', 'tbldepartment.id', '=', 'tbldesignation.departmentID')
            //->where('tbldesignation.courtID','=',$request['court'])
            ->orderBy('tbldesignation.grade', 'desc')
            ->get();

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

        DB::table('tbldesignation')->where('id', $PostID)->update(['designation' => $designation, 'departmentID' => $department,]);
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


    public function Designation(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";

        $data['courtList'] =  DB::table('tbl_court')->get();
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }

        $data['level'] = trim($request['level']);
        $data['designation'] = trim($request['designation']);
        $data['court'] = trim($request['court']);
        $data['department'] = trim($request['department']);
        $del = trim($request['delcode']);


        $data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();


        if (isset($_POST['add'])) {

            $this->validate($request, [
                'department'          => 'required',
                'level'              => 'required',
                'designation'          => 'required',
            ]);



            DB::table('tbldesignation')->insert(array(
                'courtId'            => $data['court'],
                'departmentId'        => $data['department'],
                'grade'            => $data['level'],
                'designation'        => $data['designation'],

            ));
            //DB::insert("INSERT INTO `tbldesignation`( `courtId`,`departmentId`,`grade`,`designation`) VALUES ('$court','$department','$level','$designation')");
            $data['designation'] = '';
            //}
        }
        DB::table('tbldesignation')->where('id', $request['PostID'])->delete();
        $data['DesignationList'] = $this->DesignationList2($data['court'], $data['department']);
        $data['DepartmentList'] = $this->DepartmentList($data['court']);
        return view('basicparameter.designation', $data);

        ///
        die("No available");
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 300);
        $data22 = DB::table('tblper')->where('staff_status', 1)->get();
        foreach ($data22 as $b) {
            $userid = DB::table('users')->insertGetId(array(
                'name'            => $b->surname . ' ' . $b->first_name . ' ' . $b->othernames,
                'username'        => $b->fileNo,
                'password'        => bcrypt('12345'),
                'email_address'  => $b->email !== null ? $b->email : 'noemail@noemail.com',
                'user_type'        => 'STAFF',
                'temp_pass'        => '12345',
                'first_login'        => 1,
            ));
            $id = $b->ID;
            DB::update("UPDATE `tblper` SET `UserID`='$userid' WHERE `ID`='$id' ");
            DB::table('assign_user_role')->insert([
                'userID' => $userid,
                'roleID' => 12,
                'created_at' => date('Y-m-d')
            ]);
        }
        ini_set('max_execution_time', 30);
        die("complete");
        $data22 = DB::table('tblper')->get();
        foreach ($data22 as $b) {
            $id = $b->ID;
            $pass = $b->ID . ".jpg";
            DB::update("UPDATE `tblper` SET `picture`='$pass' WHERE `ID`='$id' ");
        }
        ini_set('max_execution_time', 30);

        $data22 = DB::table('tblper')->get();
        foreach ($data22 as $b) {
            $id = $b->ID;
            $emp = $b->employee_type;
            DB::update("UPDATE `tblpayment_consolidated` SET `employment_type`='$emp' where `staffid`='$id' ");
        }
        die("complete");
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
        return view('basicparameter.actiondesignation', $data);
    }
    public function AccountStaffUpdate(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";



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
        return view('basicparameter.actiondesignation', $data);
    }

    public function Setup(Request $request)
    {

        $rawdata = DB::SELECT("SELECT * FROM `tblper` WHERE `staff_status`=1 and employee_type<>2 and divisionID=2 and bankID=0 ");

        foreach ($rawdata as $value) {
            $dt = db::table('tblpayment_consolidated')->where('staffid', $value->ID)->where('month', 'MAY')->value('bank');

            DB::table('tblper')->where('ID', $value->ID)->update(
                [
                    'bankID' => $dt

                ]
            );
        }
        dd("lock");

        $rawdata = DB::SELECT("SELECT * FROM `basicsalaryconsolidated` where  `employee_type`=1  ");

        foreach ($rawdata as $value) {
            $pen = round(($value->amount + $value->peculiar) * 0.08, 2);
            $nhf = round(($value->amount + $value->peculiar) * 0.025, 2);
            $nhis = round(($value->amount + $value->peculiar) * 0.05, 2);
            $nsitf = round(($value->amount + $value->peculiar) * 0.01, 2);
            DB::table('basicsalaryconsolidated')->where('ID', $value->ID)
                ->update(['pension' => $pen, 'nhf' => $nhf, 'NHIS' => $nhis, 'NSITF' => $nsitf,]);
        }

        dd("Dont re-compute");
        $rawdata = DB::SELECT("SELECT * FROM `h14`  ");
        $grade = 14;
        foreach ($rawdata as $value) {

            switch ($value->STEP) {
                case 'BASIC':
                    for ($x = 1; $x <= 9; $x++) {
                        //die($value->$x);
                        DB::table('basicsalaryconsolidated')->where('employee_type', 5)
                            ->where('grade', $grade)
                            ->where('step', $x)
                            ->update(['basic' => $value->$x,]);
                        DB::table('basicsalaryconsolidated')->where('employee_type', 5)
                            ->where('grade', $grade)
                            ->where('step', $x)
                            ->update(['amount' => $value->$x,]);
                    }
                    break;
                case 'PECULIAR':
                    for ($x = 1; $x <= 9; $x++) {
                        DB::table('basicsalaryconsolidated')->where('employee_type', 5)
                            ->where('grade', $grade)
                            ->where('step', $x)
                            ->update(['peculiar' => $value->$x,]);
                    }
                    break;
                case 'Pension':
                    for ($x = 1; $x <= 9; $x++) {
                        DB::table('basicsalaryconsolidated')->where('employee_type', 5)
                            ->where('grade', $grade)
                            ->where('step', $x)
                            ->update(['pension' => $value->$x,]);
                    }
                    break;
                case 'NHF':
                    for ($x = 1; $x <= 9; $x++) {
                        DB::table('basicsalaryconsolidated')->where('employee_type', 5)
                            ->where('grade', $grade)
                            ->where('step', $x)
                            ->update(['nhf' => $value->$x,]);
                    }
                    break;
                case 'NHIS':
                    for ($x = 1; $x <= 9; $x++) {
                        DB::table('basicsalaryconsolidated')->where('employee_type', 5)
                            ->where('grade', $grade)
                            ->where('step', $x)
                            ->update(['NHIS' => $value->$x,]);
                    }
                    break;
                case 'NSITF':
                    for ($x = 1; $x <= 9; $x++) {
                        DB::table('basicsalaryconsolidated')->where('employee_type', 5)
                            ->where('grade', $grade)
                            ->where('step', $x)
                            ->update(['NSITF' => $value->$x,]);
                    }
                    break;
            }
        }
        die('Success');
        $rawdata = DB::SELECT("SELECT * FROM `grade17`  ");
        $grade = 17;
        foreach ($rawdata as $value) {

            switch ($value->STEP) {
                case 'BASIC':
                    for ($x = 1; $x <= 9; $x++) {
                        //die($value->$x);
                        DB::table('basicsalaryconsolidated')->where('employee_type', 1)
                            ->where('grade', $grade)
                            ->where('step', $x)
                            ->update(['basic' => $value->$x,]);
                    }
                    break;
                case 'GROSS':
                    for ($x = 1; $x <= 9; $x++) {
                        DB::table('basicsalaryconsolidated')->where('employee_type', 1)
                            ->where('grade', $grade)
                            ->where('step', $x)
                            ->update(['amount' => $value->$x,]);
                    }
                    break;
                case 'Pension':
                    for ($x = 1; $x <= 9; $x++) {
                        DB::table('basicsalaryconsolidated')->where('employee_type', 1)
                            ->where('grade', $grade)
                            ->where('step', $x)
                            ->update(['pension' => $value->$x,]);
                    }
                    break;
                case 'NHF':
                    for ($x = 1; $x <= 9; $x++) {
                        DB::table('basicsalaryconsolidated')->where('employee_type', 1)
                            ->where('grade', $grade)
                            ->where('step', $x)
                            ->update(['nhf' => $value->$x,]);
                    }
                    break;
                case 'NHIS':
                    for ($x = 1; $x <= 9; $x++) {
                        DB::table('basicsalaryconsolidated')->where('employee_type', 1)
                            ->where('grade', $grade)
                            ->where('step', $x)
                            ->update(['NHIS' => $value->$x,]);
                    }
                    break;
                case 'NSITF':
                    for ($x = 1; $x <= 9; $x++) {
                        DB::table('basicsalaryconsolidated')->where('employee_type', 1)
                            ->where('grade', $grade)
                            ->where('step', $x)
                            ->update(['NSITF' => $value->$x,]);
                    }
                    break;
            }
        }


        die("Sucesss");

        $rawdata = DB::SELECT("SELECT `grade`, `step`, `tax` FROM `basicsalaryconsolidated` WHERE `employee_type`=1 ");
        foreach ($rawdata as $value) {
            DB::table('basicsalaryconsolidated')->where('employee_type', 5)
                ->where('grade', $value->grade)
                ->where('step', $value->step)
                ->update(['tax' => $value->tax,]);
        }

        die(" tax and union due updated ");
    }
}
