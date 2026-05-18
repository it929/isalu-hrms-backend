<?php

namespace App\Http\Controllers\payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use DB;
use Session;
use Auth;

class DueForArrearsController extends functionController
{
    public function index(Request $request)
    {
        $data['court']    = DB::table('tbl_court')->get();
        $data['year'] = $request['year'];
        //if($data['year']==''){$data['year']=session('year');}
        //Session::put('year', $data['year']);
        $data['month'] = $request['month'];
        //if($data['month']==''){$data['month']=session('month');}
        //Session::put('month', $data['month']);
        $data['dueDate'] = $request['dueDate'];
        if ($data['dueDate'] == '') {
            $data['dueDate'] = session('dueDate');
        }
        //Session::put('dueDate', $data['dueDate']);

        $ses_court = session('searchCourt');
        $ses_division = session('searchDivision');
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        $data['division'] =  DB::table('tbldivision')->where('courtID', '=', session('searchCourt'))->get();
        $division = Auth::user()->is_global ? $request['division'] : Auth::user()->divisionID;
        // dd($division);
        $data['due'] = $this->StaffForArrearList($division, $data['year'], $data['month'], $data['dueDate']);

        $data['courtDivisions']  = DB::table('tbldivision')
            ->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);


        if ($request['delid'] != '') {
            $daldata = DB::table('tblstaff_for_arrears')->where('ID', '=', $request['delid'])->first();
            if (!$daldata) {
                return redirect('/staff-due/all')->with('err', 'Ops! This record had been removed. ');
            }
            if (DB::table('tblpayment_consolidated')
                ->where('year', '=', $daldata->year_payment)
                ->where('divisionID', '=', $daldata->divisionID)
                ->where('month', '=', $daldata->month_payment)
                ->where('rank', '!=', 2)
                ->where('salary_lock', '=', 1)->first()
            ) {
                return redirect('/staff-due/all')->with('err', 'Record could not be removed. Salary have been computed and closed for the period(' . $daldata->month_payment . ' ' . $daldata->year_payment . ')');
            }
            if (DB::DELETE("DELETE FROM `tblstaff_for_arrears` WHERE `ID`='" . $request['delid'] . "'")) {
                //audit log
                $staff = $this->getOneStaff($daldata->staffid);
                $this->addLog("staff arrears deleted for $staff->surname $staff->first_name $staff->othernames with File Number $staff->fileNo from Staff For Arrears Table");
                DB::table('tblper')->where('ID', $daldata->staffid)->update([
                    'grade'         => $daldata->old_grade, 'step'         => $daldata->old_step

                ]);
                if ($daldata->month_payment != '' and  $daldata->month_payment != '') {
                    $this->SingleStaffSalaryComputation($daldata->staffid, $daldata->month_payment, $daldata->year_payment);
                }
                return redirect('/staff-due/all')->with('msg', 'successfully deleted');
            }
        }
        return view('dueForArrears.list', $data);
    }


    public function Backlog(Request $request)
    {
        $data['staff'] = '';
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }
        if (count($data['CourtInfo']) > 0) {
            $data['staffData'] = DB::table('tblper')->where('courtID', '=', $data['CourtInfo']->courtid)->orderBy('surname')->get();
        }

        $data['division'] =  DB::table('tbldivision')->where('courtID', '=', $request['court'])->get();
        $data['court'] = DB::table('tbl_court')->get();
        $data['staffList'] = DB::table('tblper')
            ->where('courtID', '=', $request['court'])
            ->where('divisionID', '=', $request['division'])->orderBy('surname')->get();

        $data['staff'] = DB::table('tblper')
            ->join('tblemployment_type', 'tblemployment_type.id', '=', 'tblper.employee_type')
            ->where('tblper.ID', '=', $request['fileNo'])->orderBy('surname')->first();
        //dd($data['staff']);
        if (isset($_POST['add'])) {

            $this->validate($request, [
                'mcount' => 'required|string',
                'fileNo' => 'required|numeric',
            ]);
            $insert = DB::table('tblbacklog')->insert(array(
                'staffid'                 => $request['fileNo'],
                'mcount'      => $request['mcount'],
            ));


            if ($insert) {
                return redirect('/staff-due/backlogs')->with('msg', 'Successfully Entered');
            } else {
                return redirect('/staff-due/backlogs')->with('err', 'Record could not be saved');
            }
        }

        return view('dueForArrears/backlog', $data);
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

    public function create20230904(Request $request)
    {
        $data['staff'] = '';
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }


        $data['courtDivisions']  = DB::table('tbldivision')
            ->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);



        if (Auth::user()->is_global == 1) {
            # code...
            $data['staffData'] = DB::table('tblper')
                ->orderBy('surname', 'Asc')
                ->get();
            //dd(auth::user());
        } else {
            # code...
            $data['staffData'] = DB::table('tblper')
                ->where('tblper.divisionID', '=', $data['curDivision']->divisionID)
                ->orderBy('surname', 'Asc')
                ->get();
            //dd(Auth::user()->is_global);
        }

        //dd(auth::user());


        $data['court'] = DB::table('tbl_court')->get();
        $data['staffList'] = DB::table('tblper')
            ->where('courtID', '=', $request['court'])
            ->where('divisionID', '=', $request['division'])->orderBy('surname')->get();

        $data['staff'] = DB::table('tblper')
            ->join('tblemployment_type', 'tblemployment_type.id', '=', 'tblper.employee_type')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->where('tblper.ID', '=', $request['fileNo'])->orderBy('surname')->first();
        //dd($data['staff']);
        if (isset($_POST['add'])) {

            $this->validate($request, [
                'newEmployeeType' => 'required|string',
                'newGrade' => 'required|numeric',
                'newStep' => 'required|numeric',
                'court' => 'required|numeric',
                'division' => 'required|numeric',
                'arrearsType' => 'required|string',
                'dueDate' => 'required|date',
            ]);
            if (($request['newStep'] == $request['oldStep']) and ($request['newGrade'] == $request['oldGrade'])) {
                return back()->with('err', 'Variation cannot be computed without change of grade  or step');
            }
            // # the task below is commented on request by mr matthew

            // $check = DB::table('tblstaff_for_arrears')
            //     ->where('courtID','=',$request['court'])
            //     ->where('new_grade','=',$request['newGrade'])
            //     ->where('new_step','=',$request['newStep'])
            //     ->where('staffid','=',$request['fileNo'])
            //     ->count();

            // if($check > 0){
            //     return back()->with('err', 'Staff already added for arears');
            // }
            //  end of comment

            $salary = DB::table('basicsalaryconsolidated')
                ->where('courtID', '=', $request['court'])
                ->where('grade', '=', $request['newGrade'])
                ->where('step', '=', $request['newStep'])
                ->where('employee_type', '=', $request['newEmpType'])
                ->count();


            // dd($salary);
            if ($salary == 0) {
                return back()->with('err', 'No Salary Structure Available');
            } else {


                $insert = DB::table('tblstaff_for_arrears')->insert(array(
                    'staffid'                 => $request['fileNo'],
                    'newEmploymentType'      => $request['newEmpType'],
                    'oldEmploymentType'      => $request['newEmpType'],
                    'courtID'                => $request['court'],
                    'old_grade'              => $request['oldGrade'],
                    'old_step'               => $request['oldStep'],
                    'new_grade'              => $request['newGrade'],
                    'new_step'               => $request['newStep'],
                    'due_date'               => $request['dueDate'],
                    'divisionID'             => $request['division'],
                    'arrears_type'           => $request['arrearsType'],
                    'payment_status'          => 0,
                ));

                DB::table('tblper')->where('ID', $request['fileNo'])->update([
                    'grade'         => $request['newGrade'], 'step'         => $request['newStep'], 'incremental_date'     => $request['dueDate']
                ]);
            }

            if ($insert) {
                return redirect('/staff-due/arrears')->with('msg', 'Successfully Entered');
            } else {
                return redirect('/staff-due/arrears')->with('err', 'Record could not be saved');
            }
        }

        if (Auth::user()->is_global == 1) {
            //   dd($data);
        }

        return view('dueForArrears.create2', $data);
    }

    public function create(Request $request)
    {
        $data['staff'] = '';
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }


        $data['courtDivisions']  = DB::table('tbldivision')
            ->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        if (Auth::user()->is_global == 1) {
            # code...
            $data['staffData'] = DB::table('tblper')
                ->orderBy('surname', 'Asc')
                ->get();
            //dd(auth::user());
        } else {
            # code...
            $data['staffData'] = DB::table('tblper')
                ->where('tblper.divisionID', '=', $data['curDivision']->divisionID)
                ->orderBy('surname', 'Asc')
                ->get();
            //dd(Auth::user()->is_global);
        }

        $data['court'] = DB::table('tbl_court')->get();
        $data['staffList'] = DB::table('tblper')
            ->where('courtID', '=', $request['court'])
            ->where('divisionID', '=', $request['division'])->orderBy('surname')->get();

        $data['staff'] = DB::table('tblper')
            ->join('tblemployment_type', 'tblemployment_type.id', '=', 'tblper.employee_type')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->where('tblper.ID', '=', $request['fileNo'])->orderBy('surname')->first();

        if (isset($_POST['add'])) {

            $this->validate($request, [
                'newEmployeeType' => 'required|string',
                'newGrade' => 'required|numeric',
                'newStep' => 'required|numeric',
                'court' => 'required|numeric',
                'division' => 'required|numeric',
                'arrearsType' => 'required|string',
                'dueDate' => 'required|date',
            ]);
            if (($request['newStep'] == $request['oldStep']) and ($request['newGrade'] == $request['oldGrade'])) {
                return back()->with('err', 'Variation cannot be computed without change of grade  or step');
            }

            $salary = DB::table('basicsalaryconsolidated')
                ->where('courtID', '=', $request['court'])
                ->where('grade', '=', $request['newGrade'])
                ->where('step', '=', $request['newStep'])
                ->where('employee_type', '=', $request['newEmpType'])
                ->count();

            if ($salary == 0) {
                return back()->with('err', 'No Salary Structure Available');
            } else {

                $insertId = DB::table('tblstaff_for_arrears')->insertGetId([
                    'staffid' => $request['fileNo'],
                    'newEmploymentType' => $request->input('newEmpType'),
                    'oldEmploymentType' => $request->input('newEmpType'),
                    'courtID' => $request->input('court'),
                    'old_grade' => $request->input('oldGrade'),
                    'old_step' => $request->input('oldStep'),
                    'new_grade' => $request->input('newGrade'),
                    'new_step' => $request->input('newStep'),
                    'due_date' => $request->input('dueDate'),
                    'divisionID' => $request->input('division'),
                    'arrears_type' => $request->input('arrearsType'),
                    'payment_status' => 0,
                ]);

                // Handle image upload
                if ($request->hasFile('doc_url')) {
                    $document = $request->file('doc_url');
                    $documentName = time() . '.' . $document->getClientOriginalExtension();

                    $location = env('UPLOAD_PATH').'arrears_document';
                    $move = $request->file('doc_url')->move($location, $documentName);

                    $save_url = 'arrears_document/' . $documentName;
                    // Update the 'image' column in the 'tblstaff_for_arrears' table with the image file name
                    DB::table('tblstaff_for_arrears')->where('id', $insertId)->update(['doc_url' => $save_url]);
                }

                // Update 'tblper' table
                DB::table('tblper')->where('ID', $request['fileNo'])->update([
                    'grade' => $request->input('newGrade'),
                    'step' => $request->input('newStep'),
                    'incremental_date' => $request->input('dueDate'),
                ]);
            }

            if ($insertId) {
                return redirect('/staff-due/arrears')->with('msg', 'Successfully Entered');
            } else {
                return redirect('/staff-due/arrears')->with('err', 'Record could not be saved');
            }
        }
        return view('dueForArrears.create2', $data);
    }



    public function createOverdue20230904(Request $request)
    {
        if ($request['fileNo'] == '') $request['fileNo'] = Session::get('fileNo');

        session::put('fileNo', $request['fileNo']);
        $data['staff'] = '';
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }


        $data['courtDivisions']  = DB::table('tbldivision')
            //  ->where('courtID', '=', $courtSessionId)
            ->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        // if(count($data['CourtInfo']) > 0){
        //     $data['staffData'] = DB::table('tblper')
        //     ->where('courtID','=',$data['CourtInfo']->courtid)->orderBy('surname')->get();
        // }

        if (Auth::user()->is_global == 1) {
            # code...
            $data['staffData'] = DB::table('tblper')
                // ->where('tblper.divisionID', '=', $data['curDivision']->divisionID)
                //->where('courtID','=', $data['CourtInfo']->courtid)
                ->orderBy('surname', 'Asc')
                ->get();
        } else {
            # code...
            $data['staffData'] = DB::table('tblper')
                ->where('tblper.divisionID', '=', $data['curDivision']->divisionID)
                ->orderBy('surname', 'Asc')
                ->get();
        }






        $data['court'] = DB::table('tbl_court')->get();

        $data['staffList'] = DB::table('tblper')
            ->where('courtID', '=', $request['court'])
            ->where('divisionID', '=', $request['division'])->orderBy('surname')->get();

        $data['staff'] = DB::table('tblper')
            ->join('tblemployment_type', 'tblemployment_type.id', '=', 'tblper.employee_type')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->where('tblper.ID', '=', $request['fileNo'])->orderBy('surname')->first();

        if (isset($_POST['add'])) {

            $this->validate($request, [
                'newEmployeeType' => 'required|string',
                'newGrade' => 'required|numeric',
                'newStep' => 'required|numeric',
                'court' => 'required|numeric',
                'division' => 'required|numeric',
                'arrearsType' => 'required|string',
                'dueDate' => 'required|date',
                'overdueDate' => 'required|date',
            ]);

            $check = DB::table('tblstaff_for_arrears_overdue')
                ->where('courtID', '=', $request['court'])
                ->where('new_grade', '=', $request['newGrade'])
                ->where('new_step', '=', $request['newStep'])
                ->where('staffid', '=', $request['fileNo'])
                ->count();
            if ($check > 0) {
                return back()->with('err', 'Staff already added for arears');
            }
            if (($request['newStep'] == $request['oldStep']) and ($request['newGrade'] == $request['oldGrade'])) {
                return back()->with('err', 'Variation cannot be computed without change of grade  or step');
            }

            if (
                DB::table('basicsalaryconsolidated')
                ->where('courtID', '=', $request['court'])
                ->where('grade', '=', $request['oldGrade'])
                ->where('step', '=', $request['oldStep'])
                ->where('employee_type', '=', $request['newEmpType'])
                ->count() == 0
            ) {
                return back()->with('err', 'No Salary Structure Available for the level');
            }

            $salary = DB::table('basicsalaryconsolidated')
                ->where('courtID', '=', $request['court'])
                ->where('grade', '=', $request['newGrade'])
                ->where('step', '=', $request['newStep'])
                ->where('employee_type', '=', $request['newEmpType'])
                ->count();

            if ($salary == 0) {
                return back()->with('err', 'No Salary Structure Available');
            } else {
                $insert = DB::table('tblstaff_for_arrears_overdue')->insert(array(
                    'staffid'                 => $request['fileNo'],
                    'newEmploymentType'      => $request['newEmpType'],
                    'oldEmploymentType'      => $request['newEmpType'],
                    'courtID'                => $request['court'],
                    'old_grade'              => $request['oldGrade'],
                    'old_step'               => $request['oldStep'],
                    'new_grade'              => $request['newGrade'],
                    'new_step'               => $request['newStep'],
                    'due_date'               => $request['dueDate'],
                    'overdueDate'               => $request['overdueDate'],
                    'divisionID'             => $request['division'],
                    'arrears_type'           => $request['arrearsType'],
                    'payment_status'          => 0,
                ));
                //DB::table('tblper')->where('ID', $request['fileNo'])->update([
                //'grade' 		=> $request['newGrade']
                //,'step' 		=> $request['newStep']
                //,'incremental_date' 	=> $request['dueDate']
                //]);
            }
            $staff = $this->getOneStaff($request['fileNo']);
            $oldGrade = $request['oldGrade'];
            $oldStep = $request['oldStep'];
            $newGrade = $request['newGrade'];
            $newStep = $request['newStep'];

            $this->addLog("Variation Over due added for $staff->surname $staff->first_name $staff->othernames with File Number $staff->fileNo Old Grade: $oldGrade Old Step: $oldStep New Grade: $newGrade New Step: $newStep ");
            if ($insert) {
                return redirect('/staff-overdue/arrears')->with('msg', 'Successfully Entered');
            } else {
                return redirect('/staff-overdue/arrears')->with('err', 'Record could not be saved');
            }
        }
        if ($request['delid'] != '') {
            $daldata = DB::table('tblstaff_for_arrears_overdue')->where('ID', '=', $request['delid'])->first();
            if (!$daldata) {
                return redirect('/staff-overdue/arrears')->with('err', 'Ops! This record had been removed. ');
            }
            if (DB::table('tblpayment_consolidated')->where('year', '=', $daldata->year_payment)->where('month', '=', $daldata->month_payment)->where('salary_lock', '=', 1)->where('divisionID', '=', $request['division'])->first()) { //
                return redirect('/staff-overdue/arrears')->with('err', 'Record could not be removed. Salary have been computed and closed for the period(' . $daldata->month_payment . ' ' . $daldata->year_payment . ')');
            }
            if (DB::DELETE("DELETE FROM `tblstaff_for_arrears_overdue` WHERE `ID`='" . $request['delid'] . "'")) {
                //audit log
                $staff = $this->getOneStaff($daldata->staffid);
                $this->addLog("staff arrears deleted for $staff->surname $staff->first_name $staff->othernames with File Number $staff->fileNo from Staff For Arrears Table");
                if ($daldata->month_payment != '' and  $daldata->month_payment != '') {
                    $this->SingleStaffSalaryComputation($daldata->staffid, $daldata->month_payment, $daldata->year_payment);
                }
                return redirect('/staff-overdue/arrears')->with('msg', 'successfully deleted');
            }
        }

        $data['StaffOverdueArrear'] = $this->StaffOverdueArrear($request['fileNo']);

        //  dd($data);
        return view('dueForArrears.createoverdue', $data);
    }

    public function createOverdue(Request $request)
    {
        if ($request['fileNo'] == '') $request['fileNo'] = Session::get('fileNo');

        session::put('fileNo', $request['fileNo']);
        $data['staff'] = '';
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }


        $data['courtDivisions']  = DB::table('tbldivision')
            //  ->where('courtID', '=', $courtSessionId)
            ->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        // if(count($data['CourtInfo']) > 0){
        //     $data['staffData'] = DB::table('tblper')
        //     ->where('courtID','=',$data['CourtInfo']->courtid)->orderBy('surname')->get();
        // }

        if (Auth::user()->is_global == 1) {
            # code...
            $data['staffData'] = DB::table('tblper')
                // ->where('tblper.divisionID', '=', $data['curDivision']->divisionID)
                //->where('courtID','=', $data['CourtInfo']->courtid)
                ->orderBy('surname', 'Asc')
                ->get();
        } else {
            # code...
            $data['staffData'] = DB::table('tblper')
                ->where('tblper.divisionID', '=', $data['curDivision']->divisionID)
                ->orderBy('surname', 'Asc')
                ->get();
        }

        $data['court'] = DB::table('tbl_court')->get();

        $data['staffList'] = DB::table('tblper')
            ->where('courtID', '=', $request['court'])
            ->where('divisionID', '=', $request['division'])->orderBy('surname')->get();

        $data['staff'] = DB::table('tblper')
            ->join('tblemployment_type', 'tblemployment_type.id', '=', 'tblper.employee_type')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->where('tblper.ID', '=', $request['fileNo'])->orderBy('surname')->first();

        if (isset($_POST['add'])) {

            $this->validate($request, [
                'newEmployeeType' => 'required|string',
                'newGrade' => 'required|numeric',
                'newStep' => 'required|numeric',
                'court' => 'required|numeric',
                'division' => 'required|numeric',
                'arrearsType' => 'required|string',
                'dueDate' => 'required|date',
                'overdueDate' => 'required|date',
            ]);

            $check = DB::table('tblstaff_for_arrears_overdue')
                ->where('courtID', '=', $request['court'])
                ->where('new_grade', '=', $request['newGrade'])
                ->where('new_step', '=', $request['newStep'])
                ->where('staffid', '=', $request['fileNo'])
                ->count();
            if ($check > 0) {
                return back()->with('err', 'Staff already added for arears');
            }
            if (($request['newStep'] == $request['oldStep']) and ($request['newGrade'] == $request['oldGrade'])) {
                return back()->with('err', 'Variation cannot be computed without change of grade  or step');
            }

            $check1 = DB::table('basicsalaryconsolidated')
                ->where('courtID', '=', $request['court'])
                ->where('grade', '=', $request['oldGrade'])
                ->where('step', '=', $request['oldStep'])
                ->where('employee_type', '=', $request['newEmpType'])
                ->count() == 0;

            if ($check1) {
                return back()->with('err', 'No Salary Structure Available for the level');
            }

            $salary = DB::table('basicsalaryconsolidated')
                ->where('courtID', '=', $request['court'])
                ->where('grade', '=', $request['newGrade'])
                ->where('step', '=', $request['newStep'])
                ->where('employee_type', '=', $request['newEmpType'])
                ->count();

            if ($salary == 0) {
                return back()->with('err', 'No Salary Structure Available');
            } else {

                $insertId = DB::table('tblstaff_for_arrears_overdue')->insertGetId([
                    'staffid'                 => $request['fileNo'],
                    'newEmploymentType'      => $request['newEmpType'],
                    'oldEmploymentType'      => $request['newEmpType'],
                    'courtID'                => $request['court'],
                    'old_grade'              => $request['oldGrade'],
                    'old_step'               => $request['oldStep'],
                    'new_grade'              => $request['newGrade'],
                    'new_step'               => $request['newStep'],
                    'due_date'               => $request['dueDate'],
                    'overdueDate'               => $request['overdueDate'],
                    'divisionID'             => $request['division'],
                    'arrears_type'           => $request['arrearsType'],
                    'payment_status'          => 0,
                ]);


                // Handle image upload
                if ($request->hasFile('doc_url')) {
                    $document = $request->file('doc_url');
                    $documentName = time() . '.' . $document->getClientOriginalExtension();

                    $location = env('UPLOAD_PATH').'arrears_document';
                    $move = $request->file('doc_url')->move($location, $documentName);

                    $save_url = 'arrears_document/' . $documentName;

                    // Update the 'image' column in the 'tblstaff_for_arrears' table with the image file name
                    DB::table('tblstaff_for_arrears_overdue')->where('id', $insertId)->update(['doc_url' => $save_url]);
                }

                // Update 'tblper' table
                //DB::table('tblper')->where('ID', $request['fileNo'])->update([
                //'grade' 		=> $request['newGrade']
                //,'step' 		=> $request['newStep']
                //,'incremental_date' 	=> $request['dueDate']
                //]);
            }
            $staff = $this->getOneStaff($request['fileNo']);
            $oldGrade = $request['oldGrade'];
            $oldStep = $request['oldStep'];
            $newGrade = $request['newGrade'];
            $newStep = $request['newStep'];

            $this->addLog("Variation Over due added for $staff->surname $staff->first_name $staff->othernames with File Number $staff->fileNo Old Grade: $oldGrade Old Step: $oldStep New Grade: $newGrade New Step: $newStep ");
            if ($insertId) {
                return redirect('/staff-overdue/arrears')->with('msg', 'Successfully Entered');
            } else {
                return redirect('/staff-overdue/arrears')->with('err', 'Record could not be saved');
            }
        }

        if ($request['delid'] != '') {
            $daldata = DB::table('tblstaff_for_arrears_overdue')->where('ID', '=', $request['delid'])->first();
            if (!$daldata) {
                return redirect('/staff-overdue/arrears')->with('err', 'Ops! This record had been removed. ');
            }
            if (DB::table('tblpayment_consolidated')->where('year', '=', $daldata->year_payment)->where('month', '=', $daldata->month_payment)->where('salary_lock', '=', 1)->where('divisionID', '=', $request['division'])->first()) { //
                return redirect('/staff-overdue/arrears')->with('err', 'Record could not be removed. Salary have been computed and closed for the period(' . $daldata->month_payment . ' ' . $daldata->year_payment . ')');
            }
            if (DB::DELETE("DELETE FROM `tblstaff_for_arrears_overdue` WHERE `ID`='" . $request['delid'] . "'")) {
                //audit log
                $staff = $this->getOneStaff($daldata->staffid);
                $this->addLog("staff arrears deleted for $staff->surname $staff->first_name $staff->othernames with File Number $staff->fileNo from Staff For Arrears Table");
                if ($daldata->month_payment != '' and  $daldata->month_payment != '') {
                    $this->SingleStaffSalaryComputation($daldata->staffid, $daldata->month_payment, $daldata->year_payment);
                }
                return redirect('/staff-overdue/arrears')->with('msg', 'successfully deleted');
            }
        }

        $data['StaffOverdueArrear'] = $this->StaffOverdueArrear($request['fileNo']);

        return view('dueForArrears.createoverdue', $data);
    }

    public function edit($id = null)
    {
        $data['staff'] = '';
        $s = DB::table('tblarrears_temp')->where('dueID', '=', $id)->first();
        $data['staffList'] = DB::table('tblarrears_temp')
            ->join('tblper', 'tblper.ID', '=', 'tblarrears_temp.fileNo')
            ->where('tblarrears_temp.staffid', '=', $s->staffid)->first();
        $data['allDivisions'] =  DB::table('tbldivision')->where('courtID', '=', $s->courtID)->get();
        $data['division'] =  DB::table('tbldivision')->where('courtID', '=', $s->courtID)->where('divisionID', '=', $s->divisionID)->first();

        $data['court']    = DB::table('tbl_court')->where('id', '=', $s->courtID)->first();
        $data['allcourts']    = DB::table('tbl_court')->get();
        $data['emptype']    = DB::table('tblemployment_type')->get();

        return view('dueForArrears/edit', $data);
    }



    public function store(Request $request)
    {
        $this->validate($request, [
            'newEmployeeType' => 'required|string',
            'newGrade' => 'required|numeric',
            'newStep' => 'required|numeric',
            'court' => 'required|numeric',
            'division' => 'required|numeric',
            'arrearsType' => 'required|string',
        ]);
        $salary = DB::table('basicsalaryconsolidated')
            ->where('courtID', '=', $request['court'])
            ->where('grade', '=', $request['newGrade'])
            ->where('step', '=', $request['newStep'])
            ->where('employee_type', '=', $request['newEmpType'])
            ->count();
        // dd($salary);

        $arearsType = $request['arrearsType'];
        if ($arearsType  == 'newAppointment') {
            $oldGrade = 0;
            $oldStep = 0;
        } else {
            $oldGrade = $request['oldGrade'];
            $oldStep  = $request['oldStep'];
        }
        if ($salary == 0) {
            return back()->with('err', 'No Salary Structure Available');
        } else {
            $insert = DB::table('tblstaff_for_arrears')->insert(array(
                'staffid'                 => $request['fileNo'],
                'newEmploymentType'      => $request['newEmpType'],
                'oldEmploymentType'      => $request['newEmpType'],
                'courtID'                => $request['court'],
                'old_grade'              => $oldGrade,
                'old_step'               => $oldStep,
                'new_grade'              => $request['newGrade'],
                'new_step'               => $request['newStep'],
                'due_date'               => $request['dueDate'],
                'divisionID'             => $request['division'],
                'arrears_type'           => $request['arrearsType'],
                'payment_status'         => 0,
            ));
            DB::table('tblper')->where('ID', $request['fileNo'])->update([
                'grade'         => $request['newGrade'], 'step'         => $request['newStep'], 'incremental_date'     => $request['dueDate']
            ]);
        }
        $negrade = $request['newGrade'];
        $newstep = $request['newStep'];

        $staff = $this->getOneStaff($request['fileNo']);
        $this->addLog("Variation Order added for $staff->surname $staff->first_name $staff->othernames with File Number $staff->fileNo Old Grade: $oldGrade Old Step: $oldStep New Grade: $new grade New Step: $newstep ");
        if ($insert) {
            return redirect('/staff-due/arrears')->with('msg', 'Successfully Entered');
        } else {
            return redirect('/staff-due/arrears')->with('err', 'Record could not be saved');
        }
    }

    public function divSession(Request $request)
    {
        /*$val = $request['val'];
      if($val == 'division')
      {
        Session::put('divsession', $request['division']);
      }
      elseif($val == 'staff')
      {
          Session::put('staffsession', $request['staff']);
      }
       elseif($val == 'search_court')
      {
          Session::forget('searchDivision');
          Session::put('searchCourt', $request['courtID']);

      }
      elseif($val == 'search_division')
      {
          Session::put('searchDivision', $request['division']);
      }

        */
        return response()->json('Set');
    }


    public function alert()
    {
        return view('layouts/_incrementAlert');
    }


    public function DivisionList1($court)
    {

        $DepartmentList = DB::Select("SELECT *,
      (SELECT `court_name`  FROM `tbl_court` WHERE `tbl_court`.`id`=`tbldivision`.`courtID`) as court_names
      FROM `tbldivision` WHERE `tbldivision`.`courtID`='$court'");
        return $DepartmentList;
    }


    public function staffDue($court, $division, $staffName)
    {

        $date = date('Y-m-d');
        $date = strtotime($date);
        $new_date = strtotime('- 1 year', $date);

        if ($staffName == '') {

            $DepartmentList = DB::Select("SELECT *
        FROM `tblper` WHERE `divisionID` ='$division' AND `courtID`='$court' && `laststep_update` <= DATE_SUB(CURRENT_DATE(), INTERVAL 1 YEAR) AND `date_present_appointment` <=  DATE_SUB(CURRENT_DATE(), INTERVAL 1 YEAR)");
            return $DepartmentList;
        } else {


            $DepartmentList = DB::Select("SELECT *
        FROM `tblper` WHERE `divisionID` ='$division' AND `courtID`='$court' AND `fileNo`= '$staffName'  ");
            return $DepartmentList;
        }
    }



    public function DivisionStaffList($court, $division)
    {
        $myData = DB::table('tblper')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
            ->select('fileNo', 'surname', 'first_name', 'othernames')
            ->where('tblper.divisionID', '=', $division)
            ->where('tblper.courtID', $court)
            ->orderBy('surname', 'Asc')->get();
        return $myData;
    }



    public function ConfirmSalary($grade, $step)
    {
        $confir = DB::Select("SELECT * FROM `basicsalaryconsolidated` WHERE `grade`='$grade' AND `step`='$step'");
        if (($confir)) {
            return true;
        } else {
            return false;
        }
    }

    public function dueForIncrement(Request $request)
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
        $data['court'] = $court;
        $division = trim($request['division']);
        $data['division'] = $division;
        $staffName = trim($request['staffName']);
        $data['staffName'] = $staffName;
        $data['courts'] = DB::table('tbl_court')->get();
        //$data['scale'] ='';
        $data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
        $data['DivisionList'] = $this->DivisionList1($court);
        $data['staffList'] = $this->DivisionStaffList($court, $division);
        $data['due'] = $this->staffDue($court, $division, $staffName);


        //Perform insert

        if (isset($_POST['insert'])) {
            $checkbox = $request['checkbox'];
            $dueDate = date('Y-01-01');

            if (isset($checkbox)) {

                foreach ($checkbox as $i) {



                    $fetch = DB::table('tblper')->where('ID', '=', $i)->first();
                    $new_step = $fetch->step;
                    $grade = $fetch->grade;
                    $new_step = $new_step + 1;
                    $datey = $fetch->laststep_update;
                    $datey = strtotime($datey);
                    $new_date = date('Y-m-d', strtotime('+1 year', $datey));
                    $approved_date = date('Y-m-d H:i:s');
                    $approved_by = Auth::user()->username;

                    $salaryCheck = $this->ConfirmSalary($grade, $new_step);
                    //dd($salaryCheck);
                    if (!$salaryCheck  == true) {

                        $court = $fetch->courtID;
                        $division = $fetch->divisionID;
                        $data['court'] = $court;
                        $data['division'] = $division;
                        $data['DivisionList'] = $this->DivisionList1($court);
                        $data['due'] = $this->staffDue($court, $division, $staffName);
                        $data['warning'] = "The staff status cannot be increased to the step! Because there is no salary scale for the step";
                        return view('dueForArrears/incrementDue', $data);
                    } else {

                        $insert = DB::table('tblstaff_for_arrears')->insert(array(
                            'fileNo'                 => $i,
                            'newEmploymentType'      => $fetch->employee_type,
                            'oldEmploymentType'      => $fetch->employee_type,
                            'courtID'                => $fetch->courtID,
                            'old_grade'              => $fetch->grade,
                            'old_step'               => $fetch->step,
                            'new_grade'              => $fetch->grade,
                            'new_step'               => $new_step,
                            'due_date'               => $new_date,
                            'divisionID'             => $fetch->divisionID,
                            'arrears_type'           => 'INC',
                            'payment_status'         => 0,
                            'approvedDate'              => $approved_date,
                            'approvedBy'              => $approved_by,
                        ));


                        if ($insert) {


                            DB::table('tblper')->where('ID', '=', $i)->update(array(
                                'laststep_update'          => $new_date,
                                'step'          => $new_step,
                            ));
                        }
                    }

                    $court = $fetch->courtID;
                    $division = $fetch->divisionID;
                    $data['court'] = $court;
                    $data['division'] = $division;
                    $data['DivisionList'] = $this->DivisionList1($court);
                    $data['due'] = $this->staffDue($court, $division, $staffName);
                    $data['success'] = "successfully approved";
                    return view('dueForArrears/incrementDue', $data);
                }
            } else {

                $court = trim($request['courty']);
                $division = trim($request['divisiony']);

                $data['court'] = $court;
                $data['division'] = $division;
                $data['due'] = $this->staffDue($court, $division, $staffName);
                $data['warning'] = "Please click on the checkbox beside the staff";
                return view('dueForArrears/incrementDue', $data);
            }
        }


        if (isset($_POST['newinsert'])) {
            # code...

            $staffNamey = $request['staffNamey'];
            //dd($staffNamey);
            $courtn = $request['courtn'];
            $divisionn = $request['divisionn'];

            //dd($staffNamey,$courtn,  $divisionn );

            $fetch = DB::table('tblper')->where('ID', '=', $staffNamey)->first();
            $new_step = $fetch->step;
            $grade = $fetch->grade;
            $new_step = $new_step + 1;
            $datey = $fetch->laststep_update;
            $datey = strtotime($datey);
            $new_date = date('Y-m-d', strtotime('+1 year', $datey));
            $approved_date = date('Y-m-d H:i:s');
            $approved_by = Auth::user()->username;

            $salaryCheck = $this->ConfirmSalary($grade, $new_step);
            //dd($salaryCheck);
            if (!$salaryCheck  == true) {

                $court = $fetch->courtID;
                $division = $fetch->divisionID;

                $data['court'] = $court;
                $data['division'] = $division;

                $data['DivisionList'] = $this->DivisionList1($court);
                $data['due'] = $this->staffDue($court, $division, $staffName);
                $data['warning'] = "The staff status cannot be increased to the step! Because there is no salary scale for the step";
                return view('dueForArrears/incrementDue', $data);
            } else {

                $insert = DB::table('tblstaff_for_arrears')->insert(array(
                    'fileNo'                 => $staffNamey,
                    'newEmploymentType'      => $fetch->employee_type,
                    'oldEmploymentType'      => $fetch->employee_type,
                    'courtID'                => $fetch->courtID,
                    'old_grade'              => $fetch->grade,
                    'old_step'               => $fetch->step,
                    'new_grade'              => $fetch->grade,
                    'new_step'               => $new_step,
                    'due_date'               => $new_date,
                    'divisionID'             => $fetch->divisionID,
                    'arrears_type'           => 'INC',
                    'payment_status'          => 0,
                    'approvedDate'              => $approved_date,
                    'approvedBy'              => $approved_by,

                ));


                if ($insert) {


                    DB::table('tblper')->where('ID', '=', $staffNamey)->update(array(
                        'laststep_update'          => $new_date,
                        'step'          => $new_step,
                    ));
                }
            }


            $court = trim($request['courtn']);
            $division = trim($request['divisionn']);
            $data['court'] = $court;
            $data['division'] = $division;
            $data['DivisionList'] = $this->DivisionList1($courtn);
            $data['due'] = $this->staffDue($courtn, $divisionn, $staffName);
            $data['success'] = "successfully approved";
            return view('dueForArrears/incrementDue', $data);
        }


        if (isset($_POST['edit'])) {
            # code...

            $staffNameEdit = $request['staffNameEdit'];
            $new_step = $request['newStep'];
            $dueDate = $request['dueDate'];
            $datey = strtotime($dueDate);
            $new_date = date('Y-m-d', $datey);
            $approved_date = date('Y-m-d H:i:s');
            $approved_by = Auth::user()->username;

            //dd($staffNamey,$courtn,  $divisionn );

            $fetch = DB::table('tblper')->where('fileNo', '=', $staffNameEdit)->first();

            $grade = $fetch->grade;

            $salaryCheck = $this->ConfirmSalary($grade, $new_step);

            if (!$salaryCheck  == true) {

                $court = $fetch->courtID;
                $division = $fetch->divisionID;

                $data['court'] = $court;
                $data['division'] = $division;

                $data['DivisionList'] = $this->DivisionList1($court);
                $data['due'] = $this->staffDue($court, $division, $staffName);
                $data['warning'] = "The staff status cannot be increased to the step! Because there is no salary scale for the step";
                return view('dueForArrears/incrementDue', $data);
            } else {

                $insert = DB::table('tblstaff_for_arrears')->insert(array(
                    'fileNo'                 => $staffNameEdit,
                    'newEmploymentType'      => $fetch->employee_type,
                    'oldEmploymentType'      => $fetch->employee_type,
                    'courtID'                => $fetch->courtID,
                    'old_grade'              => $fetch->grade,
                    'old_step'               => $fetch->step,
                    'new_grade'              => $fetch->grade,
                    'new_step'               => $new_step,
                    'due_date'               => $new_date,
                    'divisionID'             => $fetch->divisionID,
                    'arrears_type'           => 'INC',
                    'payment_status'          => 0,
                    'approvedDate'              => $approved_date,
                    'approvedBy'              => $approved_by,
                ));


                if ($insert) {


                    DB::table('tblper')->where('ID', '=', $staffNameEdit)->update(array(
                        'laststep_update'          => $new_date,
                        'step'          => $new_step,
                    ));
                }
            }


            $court = trim($request['courty']);
            $division = trim($request['divisiony']);
            $data['court'] = $court;
            $data['division'] = $division;
            $data['DivisionList'] = $this->DivisionList1($court);
            $data['due'] = $this->staffDue($court, $division, $staffName);
            $data['success'] = "successfully approved";
            return view('dueForArrears/incrementDue', $data);
        }



        return view('dueForArrears/incrementDue', $data);
    }


    public function acceptIncrement(Request $request)
    {
        $fileNo = $request['fileNo'];

        $fetch = DB::table('tblarrears_temp')->where('staffid', '=', $fileNo)->first();

        $insert = DB::table('tblstaff_for_arrears')->insert(array(
            'fileNo'                 => $i,
            'newEmploymentType'      => $fetch->newEmploymentType,
            'oldEmploymentType'      => $fetch->oldEmploymentType,
            'courtID'                => $fetch->courtID,
            'old_grade'              => $fetch->old_grade,
            'old_step'               => $fetch->old_step,
            'new_grade'              => $fetch->new_grade,
            'new_step'               => $fetch->new_step,
            'due_date'               => $fetch->due_date,
            'divisionID'             => $fetch->divisionID,
            'arrears_type'           => 'INC',
            'payment_status'          => 0,
        ));

        if ($insert) {
            DB::table('tblarrears_temp')->where('staffid', '=', $fileNo)->update(array(
                'status'          => 1,
            ));
        }

        return response()->json('Approved');
    }
}
