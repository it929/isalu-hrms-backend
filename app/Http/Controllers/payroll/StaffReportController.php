<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;
//use Auth;
use App\Http\Requests;
use DB;
use Auth;
use Session;

class StaffReportController extends functionController
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->middleware('auth');
        // $this->username = Session::get('userName');
            return $next($request);
        });
    } //

    public function NominalRollReport(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }
        $employmenttype = trim($request['employmenttype']);

        $fieldstoview = $request['fields'];
        $search = $request->input('add');


        $designation = trim($request['designation']);
        $division = trim($request['division']);
       

        $grade = trim($request['grade']);
        $court = trim($request['court']);
        $department = trim($request['department']);
        $section = trim($request['section']);
        $gender = trim($request['gender']);
        $todate = trim($request['todate']);
        $fromdate = trim($request['fromdate']);
        if ($todate == "") {
            $todate = date("Y-m-d");
        }
        if ($fromdate == "") {
            $fromdate = '1900-01-01';
        }
        $orderlist = trim($request['orderlist']);

        $data['orderlist'] = $orderlist;
        $data['employmenttype'] = $employmenttype;
        $data['designation'] = $designation;

        $data['division'] = $division;

        $data['grade'] = $grade;
        $data['court'] = $court;
        $data['department'] = $department;
        $data['section'] = $section;
        $data['fromdate'] = $fromdate;
        $data['todate'] = $todate;
        $data['gender'] = $gender;
        $data['OrderList'] = $this->OrderList();
        $data['CourtList'] = $this->CourtList();
        $data['EmployeeTypeList'] = $this->EmployeeTypeList();
        $data['DesignationList'] = $this->DesignationList3($court, $department);
        $data['DepartmentList'] = $this->DepartmentList($court);
        $data['Gender'] = $this->Gender();
        $data['Divisions']  = DB::table('tbldivision')->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        if (!Auth::user()->is_global) {
            $division  = Auth::user()->divisionID;
        }


        $data['QueryStaffReport'] = $this->QueryStaffReport($court, $division, $department, $designation, $grade, $gender, $fromdate, $todate, $employmenttype, $orderlist);



        $data['fieldstoview'] = $fieldstoview;


        if (isset($_POST['delete_staff'])) {
            $this->validate($request, [
                'id'      => 'required|string',
            ]);
            $id = $request->input('id');
            $staff_image = DB::table('tblper')
                ->where('ID', '=', $id)
                ->get();

           
            DB::delete("DELETE FROM `tblper` WHERE `ID`='$id'");

            return back()->with('message', 'staff successfully deleted.');
        }

        return view('Report.StaffNominalRoll', $data);
    }
    public function JusticeNominalRollReport(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }
        $data['employmenttype'] = trim($request['employmenttype']) ?? "";

        $fieldstoview = $request['fields'];
        $search = $request->input('add');


        $designation = trim($request['designation']);
        $division = trim($request['division']);
      

        $grade = trim($request['grade']);
        $court = trim($request['court']);
        $department = trim($request['department']);
        $section = trim($request['section']);
        $gender = trim($request['gender']);
        $todate = trim($request['todate']);
        $fromdate = trim($request['fromdate']);
        if ($todate == "") {
            $todate = date("Y-m-d");
        }
        if ($fromdate == "") {
            $fromdate = '1900-01-01';
        }
        $orderlist = trim($request['orderlist']);

        $data['orderlist'] = $orderlist;
        
        $data['designation'] = $designation;

        $data['division'] = $division;
        $data['rank'] = 2;
        $data['grade'] = $grade;
        $data['court'] = $court;
        $data['department'] = $department;
        $data['section'] = $section;
        $data['fromdate'] = $fromdate;
        $data['todate'] = $todate;
        $data['gender'] = $gender;
        $data['OrderList'] = $this->OrderList();
        $data['CourtList'] = $this->CourtList();
        $data['EmployeeTypeList'] = $this->EmployeeTypeList();
        $data['DesignationList'] = $this->DesignationList3($court, $department);
        $data['DepartmentList'] = $this->DepartmentList($court);
        $data['Gender'] = $this->Gender();
        $data['Divisions']  = DB::table('tbldivision')->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        if (!Auth::user()->is_global) {
            $division  = Auth::user()->divisionID;
        }
      

         $data['QueryStaffReport'] = $this->JUsticeReport($court, $division, $department, $designation, $grade, $gender, $fromdate, $todate, $data['employmenttype'], $orderlist);

       
       
        $data['fieldstoview'] = $fieldstoview;


        if (isset($_POST['delete_staff'])) {
            $this->validate($request, [
                'id'      => 'required|string',
            ]);
            $id = $request->input('id');
            $staff_image = DB::table('tblper')
                ->where('ID', '=', $id)
                ->get();
            DB::delete("DELETE FROM `tblper` WHERE `ID`='$id'");

            return back()->with('message', 'staff successfully deleted.');
        }

        return view('Report.StaffNominalRoll', $data);
    }

    public function JudgesNominalRollReport(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $data['CourtInfo'] = $this->CourtInfo();

        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }
        $employmenttype = trim($request['employmenttype']);

        $fieldstoview = $request['fields'];
        $search = $request->input('add');


        $designation = trim($request['designation']);
        $division = trim($request['division']);
        // dd($division);

        $grade = trim($request['grade']);
        $court = trim($request['court']);
        $department = trim($request['department']);
        $section = trim($request['section']);
        $gender = trim($request['gender']);
        $todate = trim($request['todate']);
        $fromdate = trim($request['fromdate']);
        if ($todate == "") {
            $todate = date("Y-m-d");
        }
        if ($fromdate == "") {
            $fromdate = '1900-01-01';
        }
        $orderlist = trim($request['orderlist']);

        $data['orderlist'] = $orderlist;
        $data['employmenttype'] = $employmenttype;
        $data['designation'] = $designation;

        $data['division'] = $division;

        $data['grade'] = $grade;
        $data['court'] = $court;
        $data['department'] = $department;
        $data['section'] = $section;
        $data['fromdate'] = $fromdate;
        $data['todate'] = $todate;
        $data['gender'] = $gender;
        $data['OrderList'] = $this->OrderList();
        $data['CourtList'] = $this->CourtList();
        $data['EmployeeTypeList'] = $this->EmployeeTypeList();
        $data['DesignationList'] = $this->DesignationList3($court, $department);
        $data['DepartmentList'] = $this->DepartmentList($court);
        $data['Gender'] = $this->Gender();
        $data['Divisions']  = DB::table('tbldivision')->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);
        if (!Auth::user()->is_global) {
            $division  = Auth::user()->divisionID;
        }


        $data['QueryStaffReport'] = $this->QueryJusticeReport($court, $division, $department, $designation, $grade, $gender, $fromdate, $todate, $employmenttype, $orderlist);



        $data['fieldstoview'] = $fieldstoview;
        return view('Report.justiceNominalRoll', $data);
    }

    public function curDivision($userId)
    {
        $currentDivision = DB::table("users")
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'users.divisionID')
            ->where('users.id', '=', $userId)
            ->select('tbldivision.division', 'tbldivision.divisionID')
            ->first();
        return $currentDivision;
    }

    public function NominalRollWithGradeStep(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }
        $employmenttype = trim($request['employmenttype']);

        $fieldstoview = $request['fields'];

        $search = $request->input('add');
        $designation = trim($request['designation']);
        $division = trim($request['division']);
        $grade = trim($request['grade']);
        $court = trim($request['court']);
        $department = trim($request['department']);
        $section = trim($request['section']);
        $gender = trim($request['gender']);
        $todate = trim($request['todate']);
        $fromdate = trim($request['fromdate']);
        if ($todate == "") {
            $todate = date("Y-m-d");
        }
        if ($fromdate == "") {
            $fromdate = '1900-01-01';
        }
        $orderlist = trim($request['orderlist']);
        $data['orderlist'] = $orderlist;
        $data['employmenttype'] = $employmenttype;
        $data['designation'] = $designation;
        $data['division'] = $division;
        $data['grade'] = $grade;
        $data['court'] = $court;
        $data['department'] = $department;
        $data['section'] = $section;
        $data['fromdate'] = $fromdate;
        $data['todate'] = $todate;
        $data['gender'] = $gender;
        $data['OrderList'] = $this->OrderList();
        $data['CourtList'] = $this->CourtList();
        $data['EmployeeTypeList'] = $this->EmployeeTypeList();
        $data['DesignationList'] = $this->DesignationList3($court, $department);
        $data['DepartmentList'] = $this->DepartmentList($court);
        $data['Gender'] = $this->Gender();
        $data['Divisions'] = $this->DivisionList($court);
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        $data['QueryStaffReport'] = $this->QueryStaffReport($court, $division, $department, $designation, $grade, $gender, $fromdate, $todate, $employmenttype, $orderlist);
        $data['fieldstoview'] = $fieldstoview;
        return view('Report.StaffNominalRollGS', $data);
    }
    public function NominalRollNew(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }
        $employmenttype = trim($request['employmenttype']);
        $fieldstoview = $request['fields'];
        $designation = trim($request['designation']);
        $division = trim($request['division']);
        $grade = trim($request['grade']);
        $court = trim($request['court']);
        $department = trim($request['department']);
        $section = trim($request['section']);
        $gender = trim($request['gender']);
        $todate = trim($request['todate']);
        $fromdate = trim($request['fromdate']);
        if ($todate == "") {
            $todate = date("Y-m-d");
        }
        if ($fromdate == "") {
            $fromdate = '1900-01-01';
        }
        $orderlist = trim($request['orderlist']);
        $data['orderlist'] = $orderlist;
        $data['employmenttype'] = $employmenttype;
        $data['designation'] = $designation;
        $data['division'] = $division;
        $data['grade'] = $grade;
        $data['court'] = $court;
        $data['department'] = $department;
        $data['section'] = $section;
        $data['fromdate'] = $fromdate;
        $data['todate'] = $todate;
        $data['gender'] = $gender;
        $data['OrderList'] = $this->OrderList();
        $data['CourtList'] = $this->CourtList();
        $data['EmployeeTypeList'] = $this->EmployeeTypeList();
        $data['DesignationList'] = $this->DesignationList3($court, $department);
        $data['DepartmentList'] = $this->DepartmentList($court);
        $data['Gender'] = $this->Gender();
        $data['Divisions'] = $this->DivisionList($court);
        $data['curDivision'] = $this->curDivision(Auth::user()->id);
        $data['QueryStaffReport'] = $this->QueryStaffReportFxter($court, $division, $department, $designation, $grade, $gender, $fromdate, $todate, $employmenttype, $orderlist);
        //dd($data['QueryStaffReport']);
        $data['fieldstoview'] = $fieldstoview;
        return view('Report.StaffNominalnew', $data);
    }
}
