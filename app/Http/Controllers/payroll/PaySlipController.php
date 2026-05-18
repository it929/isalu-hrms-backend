<?php

namespace App\Http\Controllers\payroll;

use App\Http\Requests;
use App\Role;
use App\User;
use Entrust;
use App\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\payroll\ParentController;



class PaySlipController extends ParentController
{

    public $division;
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        // $this->division = $request->session()->get('division');
        // $this->divisionID = $request->session()->get('divisionID');
                return $next($request);
        });
    }
    public function create()
    {
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        // $divisionID = $this->divisionID;    
        $divisionsession = session('divsession');
        // $data['division'] =  DB::table('tbldivision')->where('courtID','=', session('anycourt'))->get();

        $data['courtDivisions']  = DB::table('tbldivision')
            //  ->where('courtID', '=', $courtSessionId)
            ->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        if (Auth::user()->is_global==1) {
            # code...
            $data['users'] = DB::table('tblper')
            ->orderBy('surname', 'Asc')
            ->get();
        } else {
            # code...
            $data['users'] = DB::table('tblper')
            ->where('tblper.divisionID', '=', $data['curDivision']->divisionID)
            ->orderBy('surname', 'Asc')
            ->get();
        }
        
        return view('payroll.payslip.index',$data);    
    }

    public function Retrieve(Request $request)
    {
        $month  = trim($request->input('month'));
        $year   = trim($request->input('year'));
        $fileNo = trim($request->input('fileNo'));
        $division = trim($request->input('division'));
        $court = trim($request->input('court'));
        
        $this->validate($request, [
            'month'  => 'required|regex:/^[\pL\s\-]+$/u',
            'year'   => 'required|integer',
            'fileNo' => 'required|string'
        ]);

        $courtName = DB::table('tbl_court')->where('id', '=', $court)->first();
        $data['courtName'] = $courtName->court_name;
        $data['division'] = DB::table('tbldivision')->where('divisionID', '=', $division)->first();
        
        // ADD THIS: Pass the selected month
        $data['selected_month'] = $month;

        $count =  DB::table('tblpayment_consolidated')
            ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
            ->where('tblpayment_consolidated.staffid', '=', $fileNo)
            ->where('tblpayment_consolidated.year', '=', $year)
            ->where('tblpayment_consolidated.month', '=', $month)
            ->where('tblpayment_consolidated.courtID', '=', $court)
            ->count();

        if ($count == 0) {
            return redirect('/payslip/create')->with('message', 'No Record Found');
        } else {
            // UPDATED QUERY: Join with tbldesignation, tbldepartment, and tblemployment_type
            $data['reports'] = DB::table('tblpayment_consolidated')
                ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                ->leftJoin('tbldesignation', 'tbldesignation.id', '=', 'tblper.designationID')
                ->leftJoin('tbldepartment', 'tbldepartment.id', '=', 'tblper.departmentID')
                ->leftJoin('tblemployment_type', 'tblemployment_type.id', '=', 'tblpayment_consolidated.employment_type')
                ->where('tblpayment_consolidated.staffid', '=', $fileNo)
                ->where('tblpayment_consolidated.year', '=', $year)
                ->where('tblpayment_consolidated.month', '=', $month)
                ->where('tblpayment_consolidated.courtID', '=', $court)
                ->select(
                    'tblpayment_consolidated.*',
                    'tblper.*',
                    'tbldesignation.designation as rank_name',
                    'tbldepartment.department as department_name',
                    'tbldepartment.head as department_head',
                    'tblemployment_type.employmentType as employment_type_name', // Added employment type name
                    'tblpayment_consolidated.grade as staffGrade', 
                    'tblpayment_consolidated.step as staffStep'
                )
                ->first();

            // ADDED: Format grade and step with leading zeros
            if (isset($data['reports']->staffGrade)) {
                $data['reports']->formattedGrade = str_pad($data['reports']->staffGrade, 2, '0', STR_PAD_LEFT);
                $data['reports']->formattedStep = str_pad($data['reports']->staffStep, 2, '0', STR_PAD_LEFT);
            }

            $detail = DB::table('tblpayment_consolidated')
                ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                ->where('tblpayment_consolidated.staffid', '=', $fileNo)
                ->where('tblpayment_consolidated.year', '=', $year)
                ->where('tblpayment_consolidated.month', '=', $month)
                ->where('tblpayment_consolidated.courtID', '=', $court)
                ->first();

            $data['bank'] = DB::table('tblbanklist')->where('bankID', '=', $detail->bank)->first();

            $data['other_deduct'] = DB::table('tblotherEarningDeduction')
                ->join('tblcvSetup', 'tblcvSetup.ID', '=', 'tblotherEarningDeduction.CVID')
                ->where('tblotherEarningDeduction.particularID', '=', 2)
                ->where('tblotherEarningDeduction.staffid', '=', $fileNo)
                ->where('tblotherEarningDeduction.year', '=', $year)
                ->where('tblotherEarningDeduction.month', '=', $month)
                ->select(
                    DB::raw('UPPER(tblcvSetup.description) as description'),
                    DB::raw('SUM(tblotherEarningDeduction.amount) as amount')
                )
                ->groupBy('tblcvSetup.description')
                ->get();

            $data['other_earn'] = DB::table('tblotherEarningDeduction')
                ->join('tblcvSetup', 'tblcvSetup.ID', '=', 'tblotherEarningDeduction.CVID')
                ->where('tblotherEarningDeduction.particularID', '=', 1)
                ->where('tblotherEarningDeduction.year', '=', $year)
                ->where('tblotherEarningDeduction.month', '=', $month)
                ->where('tblotherEarningDeduction.staffid', '=', $fileNo)
                ->select(
                    DB::raw('UPPER(tblcvSetup.description) as description'),
                    DB::raw('SUM(tblotherEarningDeduction.amount) as amount')
                )
                ->groupBy('tblcvSetup.description')
                ->get();

            // UPDATED: Determine payslip type based on employment_type (using the ID)
            $data['payslip_type'] = 'Civil Servant'; // Default
            if (isset($data['reports']->employment_type)) {
                switch ($data['reports']->employment_type) {
                    case 1:
                        $data['payslip_type'] = 'Civil Servant';
                        break;
                    case 2:
                        $data['payslip_type'] = 'Justice';
                        break;
                    case 6:
                        $data['payslip_type'] = 'Chief Registrar';
                        break;
                    case 7:
                        $data['payslip_type'] = 'Special Assistant';
                        break;
                    default:
                        $data['payslip_type'] = 'Civil Servant';
                }
            }

            // ADDED: Determine if grade and step should be shown
            $data['show_grade_step'] = true; // Default to true
            if (isset($data['reports']->employment_type)) {
                // Don't show grade/step for employment_type 2, 6, and 7
                if (in_array($data['reports']->employment_type, [2, 6, 7])) {
                    $data['show_grade_step'] = false;
                }
            }

            return view('payroll.payslip.summary', $data);
        }
    }


    public function createStaff()
    {
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        // $divisionID = $this->divisionID;    
        $divisionsession = session('divsession');
        // $data['division'] =  DB::table('tbldivision')->where('courtID','=', session('anycourt'))->get();

        $data['courtDivisions']  = DB::table('tbldivision')
            //  ->where('courtID', '=', $courtSessionId)
            ->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        if (Auth::user()->is_global==1) {
            # code...
            $data['users'] = DB::table('tblper')
            ->orderBy('surname', 'Asc')
            ->get();
        } else {
            # code...
            $data['users'] = DB::table('tblper')
            ->where('tblper.divisionID', '=', $data['curDivision']->divisionID)
            ->orderBy('surname', 'Asc')
            ->get();
        }
        $data['user'] = DB::table('tblper')
            ->where('tblper.UserID', '=', Auth::user()->id)->first();
            
        
        return view('payroll.payslip.staffindex',$data);    
    }

    public function loadStaffWithAjax($division_id)
    {
        $data['users'] = DB::table('tblper')
            ->where('tblper.divisionID', '=', $division_id)
            ->where('tblper.staff_status', '=', 1)
            ->orderBy('surname', 'Asc')
            ->get();
        return json_encode($data['users']);
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

     
    public function RetrieveStaff(Request $request)
    {
        $month  = trim($request->input('month'));
        $year   = trim($request->input('year'));
        $fileNo = trim($request->input('fileNo'));
        $division = trim($request->input('division'));
        $court = trim($request->input('court'));
        
        $this->validate($request, [
            'month'  => 'required|regex:/^[\pL\s\-]+$/u',
            'year'   => 'required|integer',
            'fileNo' => 'required|string'
        ]);

        $courtName = DB::table('tbl_court')->where('id', '=', $court)->first();
        $data['courtName'] = $courtName->court_name;
        $data['division'] = DB::table('tbldivision')->where('divisionID', '=', $division)->first();
        
        // ADD THIS ONE LINE TO PASS THE SELECTED MONTH
        $data['selected_month'] = $month;

        $count =  DB::table('tblpayment_consolidated')
            ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
            ->where('tblpayment_consolidated.staffid', '=', $fileNo)
            ->where('tblpayment_consolidated.year', '=', $year)
            ->where('tblpayment_consolidated.month', '=', $month)
            ->where('tblpayment_consolidated.vstage', '>=', 5)
            ->count();

        if ($count == 0) {
            return redirect('/payslip/create')->with('message', 'No Record Found');
        } else {
            // UPDATED QUERY: Join with tbldesignation, tbldepartment, and tblemployment_type
            $data['reports'] = DB::table('tblpayment_consolidated')
                ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                ->leftJoin('tbldesignation', 'tbldesignation.id', '=', 'tblper.designationID')
                ->leftJoin('tbldepartment', 'tbldepartment.id', '=', 'tblper.departmentID')
                ->leftJoin('tblemployment_type', 'tblemployment_type.id', '=', 'tblpayment_consolidated.employment_type')
                ->where('tblpayment_consolidated.staffid', '=', $fileNo)
                ->where('tblpayment_consolidated.year', '=', $year)
                ->where('tblpayment_consolidated.month', '=', $month)
                ->select(
                    'tblpayment_consolidated.*',
                    'tblper.*',
                    'tbldesignation.designation as rank_name',
                    'tbldepartment.department as department_name',
                    'tbldepartment.head as department_head',
                    'tblemployment_type.employmentType as employment_type_name', // Added employment type name
                    'tblpayment_consolidated.grade as staffGrade', 
                    'tblpayment_consolidated.step as staffStep'
                )
                ->first();

            // ADDED: Format grade and step with leading zeros
            if (isset($data['reports']->staffGrade)) {
                $data['reports']->formattedGrade = str_pad($data['reports']->staffGrade, 2, '0', STR_PAD_LEFT);
                $data['reports']->formattedStep = str_pad($data['reports']->staffStep, 2, '0', STR_PAD_LEFT);
            }

            $detail = DB::table('tblpayment_consolidated')
                ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                ->where('tblpayment_consolidated.staffid', '=', $fileNo)
                ->where('tblpayment_consolidated.year', '=', $year)
                ->where('tblpayment_consolidated.month', '=', $month)
                ->first();

            $data['bank'] = DB::table('tblbanklist')->where('bankID', '=', $detail->bank)->first();

            $data['other_deduct'] = DB::table('tblotherEarningDeduction')
                ->join('tblcvSetup', 'tblcvSetup.ID', '=', 'tblotherEarningDeduction.CVID')
                ->where('tblotherEarningDeduction.particularID', '=', 2)
                ->where('tblotherEarningDeduction.staffid', '=', $fileNo)
                ->where('tblotherEarningDeduction.year', '=', $year)
                ->where('tblotherEarningDeduction.month', '=', $month)
                ->select(
                    DB::raw('UPPER(tblcvSetup.description) as description'),
                    DB::raw('SUM(tblotherEarningDeduction.amount) as amount')
                )
                ->groupBy('tblcvSetup.description')
                ->get();

            $data['other_earn'] = DB::table('tblotherEarningDeduction')
                ->join('tblcvSetup', 'tblcvSetup.ID', '=', 'tblotherEarningDeduction.CVID')
                ->where('tblotherEarningDeduction.particularID', '=', 1)
                ->where('tblotherEarningDeduction.year', '=', $year)
                ->where('tblotherEarningDeduction.month', '=', $month)
                ->where('tblotherEarningDeduction.staffid', '=', $fileNo)
                ->select(
                    DB::raw('UPPER(tblcvSetup.description) as description'),
                    DB::raw('SUM(tblotherEarningDeduction.amount) as amount')
                )
                ->groupBy('tblcvSetup.description')
                ->get();

            // Determine payslip type based on employment_type
            $data['payslip_type'] = 'Civil Servant'; // Default
            if (isset($data['reports']->employment_type)) {
                switch ($data['reports']->employment_type) {
                    case 1:
                        $data['payslip_type'] = 'Civil Servant';
                        break;
                    case 2:
                        $data['payslip_type'] = 'Justice';
                        break;
                    case 6:
                        $data['payslip_type'] = 'Chief Registrar';
                        break;
                    case 7:
                        $data['payslip_type'] = 'Special Assistant';
                        break;
                    default:
                        $data['payslip_type'] = 'Civil Servant';
                }
            }

            // Determine if grade and step should be shown
            $data['show_grade_step'] = true; // Default to true
            if (isset($data['reports']->employment_type)) {
                // Don't show grade/step for employment_type 2, 6, and 7
                if (in_array($data['reports']->employment_type, [2, 6, 7])) {
                    $data['show_grade_step'] = false;
                }
            }

            return view('payroll.payslip.summary', $data);
        }
    }
      public function personal()
    {
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        $divisionID = $this->divisionID;
        $divisionsession = session('divsession');
        $data['division'] =  DB::table('tbldivision')->where('courtID', '=', session('anycourt'))->get();
        $data['court'] = DB::table('tbl_court')->get();
        $data['users'] = DB::table('tblper')
            //->where('tblper.divisionID', '=', $divisionID)
            ->where('divisionID', '=', $divisionsession)
            ->orderBy('surname', 'Asc')
            ->get();

        return view('payroll.payslip.indexPersonal',$data);
    }

    public function getPersonal(Request $request)
    {
        //  dd(date('d/m/Y'));
        //$user = DB::table('users')->where('username','=',auth::user()->fileNo)->first();
        $user = DB::table('tblper')->where('userID', '=', auth::user()->id)->first();
        if (!($user)) {
            return back()->with('err', 'User does not exist');
        }
        $month  = trim($request->input('month'));
        $year   = trim($request->input('year'));
        $fileNo = $user->ID;
        $division = trim($request->input('division'));
        $court = trim($request->input('court'));

        //dd($month);
        //$division = $this->division;
        $this->validate($request, [
                'month'  => 'required|regex:/^[\pL\s\-]+$/u',
                'year'   => 'required|integer',
                //'fileNo' => 'required|string'
            ]);

        $courtName = DB::table('tbl_court')->where('id', '=', $court)->first();
        $data['courtName'] = $courtName->court_name;
        $data['division'] = DB::table('tbldivision')->where('divisionID', '=', $division)->first();

        $count =  DB::table('tblpayment_consolidated')
            ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
            ->where('tblpayment_consolidated.staffid', '=', $fileNo)
            ->where('tblpayment_consolidated.year', '=', $year)
            ->where('tblpayment_consolidated.month', '=', $month)
            ->where('tblpayment_consolidated.courtID', '=', $court)
            //->select();
            ->count();
        if ($count == 0) {
            return redirect('/payslip/personal')->with('message', 'No Record Found');
        } else {
            //dd($bankName);
            $data['reports'] = DB::table('tblpayment_consolidated')
                ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')

                ->where('tblpayment_consolidated.staffid', '=', $fileNo)
                ->where('tblpayment_consolidated.year', '=', $year)
                ->where('tblpayment_consolidated.month', '=', $month)
                ->where('tblpayment_consolidated.courtID', '=', $court)
                //->select();
                ->first();
            //dd($data['reports'] );

            $detail = DB::table('tblpayment_consolidated')
                ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                ->where('tblpayment_consolidated.staffid', '=', $fileNo)
                ->where('tblpayment_consolidated.year', '=', $year)
                ->where('tblpayment_consolidated.month', '=', $month)
                ->where('tblpayment_consolidated.courtID', '=', $court)
                //->select();
                ->first();

            $data['bank'] = DB::table('tblbanklist')->where('bankID', '=', $detail->bank)->first();

            $data['leave_grant'] = DB::table('basicsalary')
                ->where('basicsalary.grade', '=', $detail->grade)
                ->where('basicsalary.step', '=', $detail->step)
                ->where('basicsalary.courtID', '=', $court)
                ->where('basicsalary.employee_type', '=', 'JUDICIAL')
                ->first();

            $data['other_deduct'] = DB::table('tblotherEarningDeduction')
                ->join('tblcvSetup', 'tblcvSetup.ID', '=', 'tblotherEarningDeduction.CVID')
                ->where('tblotherEarningDeduction.particularID', '=', 2)
                ->where('tblotherEarningDeduction.staffid', '=', $fileNo)
                ->where('tblotherEarningDeduction.year', '=', $year)
                ->where('tblotherEarningDeduction.month', '=', $month)
                ->get();

            $data['other_earn'] = DB::table('tblotherEarningDeduction')
                ->join('tblcvSetup', 'tblcvSetup.ID', '=', 'tblotherEarningDeduction.CVID')
                ->where('tblotherEarningDeduction.particularID', '=', 1)
                ->where('tblotherEarningDeduction.year', '=', $year)
                ->where('tblotherEarningDeduction.month', '=', $month)
                ->where('tblotherEarningDeduction.staffid', '=', $fileNo)
                ->get();
            return view('payroll.payslip.personalSlip', $data);
        }
    }
}
