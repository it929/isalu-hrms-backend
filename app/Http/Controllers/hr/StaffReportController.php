<?php

namespace App\Http\Controllers\hr;

use Illuminate\Http\Request;
use App\Http\Controllers\hr\functionController;
use App\Http\Requests;
use DB;
use Auth;
use session;

class StaffReportController extends functionController
{
    public function __construct()
    {
        $this->middleware('auth');
        // $this->username = Session::get('userName');
    }

    public function staffStatusReport(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $data['statusx'] = "";
        $data['CourtInfo'] = $this->CourtInfo();
        $data['status'] = DB::table('tblstatus')->get();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        if ($request->status == "") {
            $data['QueryStaffReport'] = DB::table('tblper')
                ->where('employee_type', '<>', 2)
                ->leftjoin('lga', 'tblper.lgaID', '=', 'lga.lgaID')
                ->leftjoin('tblstates', 'tblper.stateID', '=', 'tblstates.StateID')
                ->leftjoin('tbldesignation', 'tblper.Designation', '=', 'tbldesignation.id')
                //->leftjoin('tbleducations','tblper.fileNo','=','tbleducations.fileNo')
                //->leftjoin('tbleducations','tblper.fileNo','=','tbleducations.fileNo')
                ->orderby('tblper.rank', 'DESC')
                ->get();
        } else {

            $data['statusx'] = $request->status;

            $data['QueryStaffReport'] = DB::table('tblper')
                ->where('staff_status', '=', $request->status)
                ->where('employee_type', '<>', 2)
                ->leftjoin('lga', 'tblper.lgaID', '=', 'lga.lgaID')
                ->leftjoin('tblstates', 'tblper.stateID', '=', 'tblstates.StateID')
                ->leftjoin('tbldesignation', 'tblper.Designation', '=', 'tbldesignation.id')
                ->orderby('tblper.rank', 'DESC')
                ->get();
        }
        return view('hr.Report.StaffStatus', $data);
    }

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


        $designation = trim($request['designation']);
        $division = trim($request['division']);
        $grade = trim($request['grade']);
        $court = trim($request['court']);
        //dd($court);
        $department = trim($request['department']);
        $section = trim($request['section']);
        $gender = trim($request['gender']);
        $todate = date('Y-m-d', strtotime(trim($request['todate'])));
        $fromdate = date('Y-m-d', strtotime(trim($request['fromdate'])));

        if ($todate == "1970-01-01") {
            $todate = date("Y-m-d");
        }

        if ($fromdate == "1970-01-01") {
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
        $data['QueryStaffReport'] = $this->QueryStaffReport($court, $division, $department, $designation, $grade, $gender, $fromdate, $todate, $employmenttype, $orderlist);




        $data['fieldstoview'] = $fieldstoview;







        return view('hr.Report.StaffNominalRoll', $data);
    }

    public function staffList_28_04_26_OLD(Request $request)
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
        $court = trim($request['court']);
        $department = trim($request['department']);
        $data['department'] = $department;
        $data['DesignationList'] = DB::table('tbldesignation')->where('courtID', $court)->get();
        $data['DepartmentList'] = $this->DepartmentList($court);
        // $data['QueryStaffReport'] = DB::table('tblper')
        //     // ->where('tblper.grade', '!=', 0)
        //     // ->where('tblper.step', '!=', 0)
        //     ->where('isAdmin', '=', 1)
        //     ->leftjoin('lga', 'tblper.lgaID', '=', 'lga.lgaId')
        //     ->leftjoin('tblstates', 'tblper.stateID', '=', 'tblstates.StateID')
        //     ->leftjoin('tbldesignation', 'tblper.designationID', '=', 'tbldesignation.id')
        //     ->get();

        $data['QueryStaffReport'] = DB::table('tblper')
            ->where('isAdmin', '=', 1)
            ->leftjoin('lga', 'tblper.lgaID', '=', 'lga.lgaId')
            ->leftjoin('tblstates', 'tblper.stateID', '=', 'tblstates.StateID')
            ->leftjoin('tbldesignation', 'tblper.designationID', '=', 'tbldesignation.id')
            ->when($request->department, function ($query) use ($request) {
                return $query->where('tblper.departmentID', $request->department);
            })
            ->when($request->designation, function ($query) use ($request) {
                return $query->where('tblper.designationID', $request->designation);
            })
            ->get();

        return view('hr.Report.StaffList', $data);
    }

    public function staffList(Request $request)
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

        $court = trim($request['court']);
        $department = trim($request['department']);

        $data['department'] = $department;
        $data['selectedDepartment']  = $request->department;
        $data['selectedDesignation'] = $request->designation;

        $data['DesignationList'] = DB::table('tbldesignation')->where('courtID', $court)->get();
        $data['DepartmentList'] = $this->DepartmentList($court);

        $data['QueryStaffReport'] = DB::table('tblper')
            ->where('isAdmin', '=', 1)
            ->leftjoin('lga', 'tblper.lgaID', '=', 'lga.lgaId')
            ->leftjoin('tblstates', 'tblper.stateID', '=', 'tblstates.StateID')
            ->leftjoin('tbldesignation', 'tblper.designationID', '=', 'tbldesignation.id')
            ->when($request->department, function ($query) use ($request) {
                return $query->where('tblper.departmentID', $request->department);
            })
            ->when($request->designation, function ($query) use ($request) {
                return $query->where('tblper.designationID', $request->designation);
            })
            ->get();

        return view('hr.Report.StaffList', $data);
    }

    public function staffList_28_04_26(Request $request)
    {
        $q = DB::table('tblper')
            ->where('isAdmin', 1)
            ->join('tblsteps', 'tblper.step', '=', 'tblsteps.id')
            ->join('tblgrades', 'tblper.grade', '=', 'tblgrades.id')
            ->join('lga', 'tblper.lgaID', '=', 'lga.lgaId')
            ->join('tblstates', 'tblper.stateID', '=', 'tblstates.StateID')
            ->join('tbldesignation', 'tblper.designationID', '=', 'tbldesignation.id');

        if ($request->department) {
            $q->where('tblper.departmentID', $request->department);
        }

        if ($request->designation) {
            $q->where('tblper.designationID', $request->designation);
        }

        $data['QueryStaffReport'] = $q->get();

        $data['DesignationList'] = DB::table('tbldesignation')->get();
        $data['DepartmentList'] = $this->DepartmentList($this->CourtInfo()->courtid);

        $data['selectedDepartment'] = $request->department;
        $data['selectedDesignation'] = $request->designation;

        return view('hr.Report.StaffList', $data);
    }
    public function getStaffList_28_04_2026(Request $request)
    {
        $designation = $request->designation;
        $grade = $request->grade;
        $department = $request->department;
        $q = DB::table('tblper')->where('isAdmin', '=', 1)->join('tblsteps', 'tblper.step', '=', 'tblsteps.id')
            ->join('tblgrades', 'tblper.grade', '=', 'tblgrades.id')
            ->join('lga', 'tblper.lgaID', '=', 'lga.lgaId')
            ->join('tblstates', 'tblper.stateID', '=', 'tblstates.StateID')
            ->join('tbldesignation', 'tblper.designation', '=', 'tbldesignation.id');
        if ($designation != null) {
            $q = $q->where('tblper.designation', '=', $designation);
            if ($grade != null) {
                $q = $q->where('tblper.grade', '=', $grade);
                if ($department != null) {
                    $q = $q->where('tblper.department', '=', $department);
                }
            } else {
                if ($department != null) {
                    $q = $q->where('tblper.department', '=', $department);
                }
            }
        } else {
            if ($grade != null) {
                $q = $q->where('tblper.grade', '=', $grade);
                if ($department != null) {
                    $q = $q->where('tblper.department', '=', $department);
                }
            } else {
                if ($department != null) {
                    $q = $q->where('tblper.department', '=', $department);
                }
            }
        }

        $q = $q->get();
        $data['QueryStaffReport'] = $q;
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
        $court = trim($request['court']);
        $department = trim($request['department']);
        $data['department'] = $department;
        //dd($court);
        //$data['DesignationList'] = $this->DesignationList3($court,$department);
        $data['DesignationList'] = DB::table('tbldesignation')->where('courtID', $court)->get();
        $data['DepartmentList'] = $this->DepartmentList($court);
        /*$data['QueryStaffReport'] = DB::table('tblper')->join('tblsteps','tblper.step','=','tblsteps.id')
	  ->join('tblgrades','tblper.grade','=','tblgrades.id')
	  ->join('lga','tblper.lgaID','=','lga.lgaId')
	   ->join('tblstates','tblper.stateID','=','tblstates.StateID')
	  ->join('tbldesignation','tblper.designation','=','tbldesignation.id')->get();
	  //dd($data['QueryStaffReport']); */
        return view('hr.Report.StaffList', $data);
    }

    public function getStaffList(Request $request)
    {
        $q = DB::table('tblper')
            ->where('isAdmin', 1)
            ->join('tblsteps', 'tblper.step', '=', 'tblsteps.id')
            ->join('tblgrades', 'tblper.grade', '=', 'tblgrades.id')
            ->join('lga', 'tblper.lgaID', '=', 'lga.lgaId')
            ->join('tblstates', 'tblper.stateID', '=', 'tblstates.StateID')
            ->join('tbldesignation', 'tblper.designationID', '=', 'tbldesignation.id');

        // ✅ FILTERS (clean version)
        if ($request->designation) {
            $q->where('tblper.designationID', $request->designation);
        }

        if ($request->grade) {
            $q->where('tblper.grade', $request->grade);
        }

        if ($request->department) {
            $q->where('tblper.departmentID', $request->department);
        }

        $data['QueryStaffReport'] = $q->get();

        // UI DATA
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $data['CourtInfo'] = $this->CourtInfo();

        $court = $data['CourtInfo']->courtstatus == 0
            ? $data['CourtInfo']->courtid
            : trim($request->court);

        $data['DesignationList'] = DB::table('tbldesignation')
            ->where('courtID', $court)
            ->get();

        $data['DepartmentList'] = $this->DepartmentList($court);

        // keep selected values
        $data['selectedDepartment'] = $request->department;
        $data['selectedDesignation'] = $request->designation;
        $data['selectedGrade'] = $request->grade;

        return view('hr.Report.StaffList', $data);
    }

    public function ajaxStaffFilter(Request $request)
    {
        $q = DB::table('tblper')
            ->where('isAdmin', 1)
            ->leftJoin('lga', 'tblper.lgaID', '=', 'lga.lgaId')
            ->leftJoin('tblstates', 'tblper.stateID', '=', 'tblstates.StateID')
            ->leftJoin('tbldesignation', 'tblper.designationID', '=', 'tbldesignation.id');

        if ($request->department) {
            $q->where('tblper.departmentID', $request->department);
        }

        if ($request->designation) {
            $q->where('tblper.designationID', $request->designation);
        }

        $data = $q->get();

        return response()->json($data);
    }

    public function getStaffByZones()
    {
        $zones = DB::table('tblper')
            ->leftjoin('tblstates', 'tblstates.StateID', '=', 'tblper.StateID')
            ->select('gpz', 'tblstates.StateID as StateID')
            ->groupBy('gpz')
            ->get();


        $allStaffs = DB::table('tblper')->select('*')->count();

        foreach ($zones as $zone) {
            $zone->total = DB::table('tblper')
                ->select('*')
                ->where(['gpz' => $zone->gpz])->count();

            $a = ($zone->total * 100);
            $zone->percent = ($a / $allStaffs);
        }

        return view('hr.Report.StaffDistributionZones', [
            'zones' => $zones,
            'allStaffs' => $allStaffs
        ]);
    }

    public function getStaffByStateofOrigin()
    {
        $states = DB::table('tblstates')->select('StateID', 'State')->get();
        foreach ($states as $stateRec) {
            $stateRec->total = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'isAdmin' => 1])
                ->count();
            $stateRec->grade1 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'isAdmin' => 1, 'grade' => 1])
                ->count();
            $stateRec->grade2 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'isAdmin' => 1, 'grade' => 2])
                ->count();
            $stateRec->grade3 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'isAdmin' => 1, 'grade' => 3])
                ->count();
            $stateRec->grade4 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'isAdmin' => 1, 'grade' => 4])
                ->count();
            $stateRec->grade5 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'isAdmin' => 1, 'grade' => 5])
                ->count();
            $stateRec->grade6 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'isAdmin' => 1, 'grade' => 6])
                ->count();
            $stateRec->grade7 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'isAdmin' => 1, 'grade' => 7])
                ->count();
            $stateRec->grade8 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'isAdmin' => 1, 'grade' => 8])
                ->count();
            $stateRec->grade9 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'isAdmin' => 1, 'grade' => 9])
                ->count();
            $stateRec->grade10 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'isAdmin' => 1, 'grade' => 10])
                ->count();
            $stateRec->grade11 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'isAdmin' => 1, 'grade' => 11])
                ->count();
            $stateRec->grade12 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'isAdmin' => 1, 'grade' => 12])
                ->count();
            $stateRec->grade13 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'isAdmin' => 1, 'grade' => 13])
                ->count();
            $stateRec->grade14 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'isAdmin' => 1, 'grade' => 14])
                ->count();
            $stateRec->grade15 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'isAdmin' => 1, 'grade' => 15])
                ->count();
            $stateRec->grade16 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'isAdmin' => 1, 'grade' => 16])
                ->count();
            $stateRec->grade17 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'isAdmin' => 1, 'grade' => 17])
                ->count();

            $stateRec->consolidated = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'isAdmin' => 1, 'employee_type' => 6])
                ->count();
        }

        return view('hr.Report.StaffByStateofOrigin', [
            'states' => $states
        ]);
    }

    public function getStaffByStateofOriginOld()
    {
        $states = DB::table('tblstates')->select('StateID', 'State')->get();
        foreach ($states as $stateRec) {
            $stateRec->total = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'staff_status' => 1])
                ->count();
            $stateRec->grade1 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'staff_status' => 1, 'grade' => 1])
                ->count();
            $stateRec->grade2 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'staff_status' => 1, 'grade' => 2])
                ->count();
            $stateRec->grade3 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'staff_status' => 1, 'grade' => 3])
                ->count();
            $stateRec->grade4 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'staff_status' => 1, 'grade' => 4])
                ->count();
            $stateRec->grade5 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'staff_status' => 1, 'grade' => 5])
                ->count();
            $stateRec->grade6 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'staff_status' => 1, 'grade' => 6])
                ->count();
            $stateRec->grade7 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'staff_status' => 1, 'grade' => 7])
                ->count();
            $stateRec->grade8 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'staff_status' => 1, 'grade' => 8])
                ->count();
            $stateRec->grade9 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'staff_status' => 1, 'grade' => 9])
                ->count();
            $stateRec->grade10 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'staff_status' => 1, 'grade' => 10])
                ->count();
            $stateRec->grade11 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'staff_status' => 1, 'grade' => 11])
                ->count();
            $stateRec->grade12 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'staff_status' => 1, 'grade' => 12])
                ->count();
            $stateRec->grade13 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'staff_status' => 1, 'grade' => 13])
                ->count();
            $stateRec->grade14 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'staff_status' => 1, 'grade' => 14])
                ->count();
            $stateRec->grade15 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'staff_status' => 1, 'grade' => 15])
                ->count();
            $stateRec->grade16 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'staff_status' => 1, 'grade' => 16])
                ->count();
            $stateRec->grade17 = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'staff_status' => 1, 'grade' => 17])
                ->count();

            $stateRec->consolidated = DB::table('tblper')
                ->select('*')
                ->where(['StateID' => $stateRec->StateID, 'staff_status' => 1, 'employee_type' => 3])
                ->count();
        }

        return view('hr.Report.StaffByStateofOrigin', [
            'states' => $states
        ]);
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


        $designation = trim($request['designation']);
        //dd($designation);
        $division = trim($request['division']);
        $grade = trim($request['grade']);
        $court = trim($request['court']);
        $department = trim($request['department']);
        $section = trim($request['section']);
        $gender = trim($request['gender']);
        $todate = date('Y-m-d', strtotime(trim($request['todate'])));
        $fromdate = date('Y-m-d', strtotime(trim($request['fromdate'])));
        if ($todate == "1970-01-01") {
            $todate = date("Y-m-d");
        }
        //dd($todate);
        if ($fromdate == "1970-01-01") {
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
        $data['QueryStaffReport'] = $this->QueryStaffReport($court, $division, $department, $designation, $grade, $gender, $fromdate, $todate, $employmenttype, $orderlist);
        $data['fieldstoview'] = $fieldstoview;
        return view('hr.Report.StaffNominalRollGS', $data);
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
        $todate = date('Y-m-d', strtotime(trim($request['todate'])));
        $fromdate = date('Y-m-d', strtotime(trim($request['fromdate'])));

        if ($todate == "1970-01-01") {
            $todate = date("Y-m-d");
        }
        if ($fromdate == "1970-01-01") {
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
        $data['QueryStaffReport'] = $this->QueryStaffReportFxter($court, $division, $department, $designation, $grade, $gender, $fromdate, $todate, $employmenttype, $orderlist);
        //dd($data['QueryStaffReport']);
        $data['fieldstoview'] = $fieldstoview;
        return view('hr.Report.StaffNominalnew', $data);
    }
    public function getDesignation(Request $request)
    {
        // return("hellos");
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        $court = $request['court'];
        $department = $request->department;
        $designations = db::table('tbldesignation')->where('courtID', '=', $court)
            ->where('departmentID', '=', $department)->get();
        return ($designations);
    }
}
