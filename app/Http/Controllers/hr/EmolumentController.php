<?php

namespace App\Http\Controllers\hr;

use Illuminate\Http\Request;
use File;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use App\Helpers\FileUploadHelper;

class EmolumentController extends ParentController
{

    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        // $this->division = $request->session()->get('division');
        // $this->divisionID = $request->session()->get('divisionID');

                return $next($request);
        });
    }

    public function staffToDisplay(Request $request)
    {
        $staffsByDivision = DB::table('tblper')
            ->where('divisionID', $request->divisionID)
            ->where('staff_status', 1)
            ->where('rank', 0)
            ->select('ID', 'surname', 'first_name', 'UserID', 'fileNo', 'othernames')
            ->orderBy('surname', 'asc')
            ->get();

        return response()->json($staffsByDivision);
    }

    public function create_emolument()
    {
        $data['User'] = auth()->user();
        $data['getStaff'] = DB::table('tblper')
            ->where('staff_status', 1)
            ->where('divisionID', 1)
            ->where('rank', 0)
            ->orderBy('surname', 'asc')
            ->get();
        $data['department'] = DB::table('tbldepartment')
            ->where('courtID', '=', 9)
            ->get();
        $data['getBank'] = DB::table('tblbanklist')
            ->orderBy('tblbanklist.bank', 'Asc')
            ->get();
        $data['statelist'] = DB::table('tblstates')
            ->orderBy('State', 'Asc')
            ->get();
        $data['desig'] = DB::table('tbldesignation')
            ->where('courtID', '=', 9)
            ->orderBy('grade', 'DESC')
            ->get();
        $data['employeeType'] = DB::table('tblemployment_type')
            ->where('active', '=', 1)
            ->get();
        $data['currentState'] = DB::table('tblcurrent_state')
            // ->where('status', '=', 1)
            ->get();
        $data['divisions'] = DB::table('tbldivision')
            ->get();
        $data['userDivision'] = DB::table('tbldivision')
            ->select('division', 'divisionID')
            ->where("divisionID", $data['User']->divisionID)
            ->get();
        $data['hrEmploymentType'] = DB::table('hr_employment_type')->get();

        // if user is not global
        if ($data['User']->is_global == 0) {
            session(['is_global' => 0]);
        } else {
            session(['is_global' => 1]);
        }

        //return $data['userDivision'];
        return view('hr.Emolument.create', $data);
    }

    //auto load designation
    public function getDesignations($dept_id)
    {
        $designations = DB::table('tbldesignation')->where('departmentID', $dept_id)->orderBy('designation', 'ASC')->get();


        return response()->json($designations);
    }


    //auto fill form
    public function findStaff(Request $request)
    {

        $fileNo        = $request['getStaff'];
        $staffRecord   = DB::table('tblper')
            ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->leftJoin('tblcurrent_state', 'tblcurrent_state.id', '=', 'tblper.current_state')
            ->leftJoin('tbldepartment', 'tbldepartment.id', '=', 'tblper.department')
            ->leftJoin('tbldesignation', 'tbldesignation.id', '=', 'tblper.Designation')
            ->leftJoin('tblemployment_type', 'tblemployment_type.id', '=', 'tblper.employee_type')
            ->where('tblper.ID', '=', $fileNo)
            ->select('*', 'tbldepartment.department as depart', 'tblemployment_type.id as empID', 'tbldesignation.id as desigID', 'tblper.grade as level', 'tbldepartment.id as deptID', 'tblcurrent_state.state as currentState', 'tblbanklist.bank as bankname')
            ->first();
        return response()->json($staffRecord);
    }

    public function findStaffTemp(Request $request)
    {

        $fileNo        = $request['getStaff'];

        $staffRecord   = DB::table('tblper_temp')
            ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblper_temp.bankID')
            ->leftJoin('tbldepartment', 'tbldepartment.id', '=', 'tblper_temp.department')
            ->where('tblper_temp.ID', '=', $fileNo)
            ->select('*', 'tbldepartment.department as depart')
            ->first();

        return response()->json($staffRecord);
    }


    public function update_emolument(Request $request)
    {
        // dd($request->all());
        $this->validate(
            $request,
            [
                'getStaff'                => 'required',
                //'division'              => 'required|string',
                'grade'                 => 'required',
                'bank'                  => 'required',
                //'branch'                => 'string',
                //'phoneNumber'           => 'numeric',
                //'section'               => 'required|string',
                //'appointmentDate'       => 'required|date',
                //'incrementalDate'       => 'date',
                //'dateOfBirth'           => 'date',
                //'residentialAddress'    => 'string',
                //'qurter'                => 'string',
                //'leaveAddress'          => 'string',
                'accountNo'             => 'required|numeric',
                'currentstate'             => 'required|numeric',
            ]
        );
        $id                         = trim($request['getStaff']);
        $fileNo                     = trim($request['fileNo']);
        //$division                   = trim($request['division']);
        $step                       = trim($request['step']);
        $grade                      = trim($request['grade']);
        $bank                       = trim($request['bank']);
        $branch                     = trim($request['branch']);
        $phoneNumber                = trim($request['phoneNumber']);
        $accountNo                  = trim($request['accountNo']);
        $section                    = trim($request['section']);
        $appointmentDate            = trim($request['appointmentDate']);
        $incrementalDate            = trim($request['incrementalDate']);
        $dateOfBirth                = $request['dateOfBirth'];
        $residentialAddress         = $request['residentialAddress'];
        $qurter                     = $request['qurter'];
        $leaveAddress               = $request['leaveAddress'];
        $date                       = date("Y-m-d");
        $returnBefore               = $request['returnBefore'];
        $failureReturn              = $request['failureReturn'];

        $fname                      = trim($request['firstName']);
        $sname                      = trim($request['surname']);
        $oname                      = trim($request['otherNames']);
        $nhfNo                      = trim($request['nhfNo']);


        $sd = DB::table('tblper')
            ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->leftJoin('tbldepartment', 'tbldepartment.id', '=', 'tblper.department')
            ->leftJoin('tblstates', 'tblstates.StateID', '=', 'tblper.stateID')
            ->leftJoin('lga', 'lga.lgaId', '=', 'tblper.lgaID')
            ->where('tblper.ID', '=', $id)
            //->select('*','tbldepartment.department as dept')
            ->first();
        if (count(array($sd)) == 0) {
            return back()->with('msg', 'Staff Record Not Found. Please, try agin');
        }
        //$sd = $this->getStaff($id);
        //dd($sd);

        $data = array("Surname" => $sd->surname, "Firstname" => $sd->first_name, "Othernames" => $sd->othernames, "Grade" => $sd->grade, "Step" => $sd->step, "fileNo" => $sd->fileNo, "Bank" => $sd->bank, "phone" => $sd->phone, "E-mail" => $sd->email, "AccountNo" => $sd->AccNo, "Department" => $sd->department, "Appointment Date" => $sd->appointment_date, "Incremental Date" => $sd->incremental_date, "Date of Birth" => $sd->dob, "Home Address" => $sd->home_address, "Physically Challenged" => $sd->challengestatus, "Challenged Details" => $sd->challengedetails, "Alternate Phone" => $sd->alternate_phone, "Alternate Email" => $sd->alternate_email, "Date of Present Appointment" => $sd->date_present_appointment, "Date of Confirmation" => $sd->date_of_confirmation, "Resumption Date" => $sd->resumption_date, "Marital Status" => $sd->maritalstatus, "Gender" => $sd->gender, "Religion" => $sd->religion, "Geopolitical Zone" => $sd->gpz, "State" => $sd->State, "Local Government" => $sd->lga);
        $pre_encode = json_encode($data);


        if ($grade == "Consolidated") {
            $gradeValue = 17;
            $stepValue  = 1;
        } else {
            $gradeValue = $grade;
            $stepValue  = $step;
        }

        $image = $request->file('photo');
        //$path = $request->photo->path();

        if ($image == '') {


            $checkupdate = DB::table('tblper')->where('ID', $id)->update(array(
                'grade'             => $gradeValue,
                'step'              => $stepValue,

                'fileNo'            => $fileNo,
                'bankID'            => $bank,
                'bank_branch'       => $branch,
                'phone'             => $phoneNumber,
                'AccNo'             => $accountNo,
                'department'         => trim($request['section']),
                'departmentID'         => trim($request['section']),
                'appointment_date'  => $appointmentDate,
                'incremental_date'  => $incrementalDate,
                'dob'               => $dateOfBirth,
                'home_address'      => $residentialAddress,
                'government_qtr'    => $qurter,
                'leaveaddress'      => $leaveAddress,
                'failurereturn'     => $failureReturn,
                'returnbefore'      => $returnBefore,
                'first_name'             => $fname,
                'surname'              => $sname,
                'othernames'              => $oname,
                'fileNo'            => $fileNo,
                'appointment_date'   => trim($request['appointmentfirst']),
                'challengestatus'    =>  trim($request['challenge']),
                'challengedetails'   => trim($request['challengedetails']),
                'alternate_phone'    => trim($request['altphoneno']),
                'alternate_email'    => trim($request['altemail']),
                'date_present_appointment'  => trim($request['appointmentDate']),
                'date_of_confirmation' => trim($request['confirmationDate']),
                'last_promotion_date' => trim($request['lastPromotionDate']),
                //'firstarrival_date' => trim($request['appointmentfirst']),
                'resumption_date' => trim($request['resumptionDate']),
                'incremental_date' => trim($request['incrementalDate']),
                'maritalstatus' => trim($request['mstatus']),
                'gender' => trim($request['gender']),
                'stateID' => trim($request['state']),
                'lgaID' => trim($request['lga']),
                'gpz' => trim($request['gpz']),
                'email' => trim($request['email']),
                'religion' => trim($request['religion']),
                'Designation'        => $request['designation'],
                'designationID'        => $request['designation'],
                'current_state'      => $request['currentstate'],
                'nhfNo'             => $request['nhfNo'],
                'employee_type'     => $request['employeeType'],
                'hremploymentType' => $request['hrEmploymentType'],
                //'staff_status'      =>  1,
                'updated_at'        => date("Y-m-d")
            ));
            $sd1 =  $sd = DB::table('tblper')
                ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->leftJoin('tbldepartment', 'tbldepartment.id', '=', 'tblper.department')
                ->leftJoin('tblstates', 'tblstates.StateID', '=', 'tblper.stateID')
                ->leftJoin('lga', 'lga.lgaId', '=', 'tblper.lgaID')
                ->where('tblper.ID', '=', $id)
                //->select('*','tbldepartment.department as dept')
                ->first();
            $data1 = array("Surname" => $sd1->surname, "Firstname" => $sd1->first_name, "Othernames" => $sd1->othernames, "Grade" => $sd1->grade, "Step" => $sd1->step, "fileNo" => $sd1->fileNo, "Bank" => $sd1->bank, "phone" => $sd1->phone, "E-mail" => $sd1->email, "AccountNo" => $sd1->AccNo, "Department" => $sd1->department, "Appointment Date" => $sd1->appointment_date, "Incremental Date" => $sd1->incremental_date, "Date of Birth" => $sd1->dob, "Home Address" => $sd1->home_address, "Physically Challenged" => $sd1->challengestatus, "Challenged Details" => $sd1->challengedetails, "Alternate Phone" => $sd1->alternate_phone, "Alternate Email" => $sd1->alternate_email, "Date of Present Appointment" => $sd1->date_present_appointment, "Date of Confirmation" => $sd1->date_of_confirmation, "Resumption Date" => $sd1->resumption_date, "Marital Status" => $sd1->maritalstatus, "Gender" => $sd1->gender, "Religion" => $sd1->religion, "Geopolitical Zone" => $sd1->gpz, "State" => $sd1->State, "Local Government" => $sd1->lga);
            $post_encode = json_encode($data1);


            $logMessage = 'Personal Emolument records updated';
            $message = 'Personal Emolument records updated successfully';
            if ($checkupdate) {
                $this->addLog("$logMessage for $sd1->surname $sd1->first_name $sd1->othernames from $pre_encode to $post_encode");
            }
            return redirect('/personal-emolument/create')->with('msg', $message);
        } else {

            // $filename = $image->getClientOriginalName();
            $file = $request->file('photo');
            $customName = $this->RefNo() . '_passport.' . $file->getClientOriginalExtension();
            $passportUrl = FileUploadHelper::upload($file, 'staffattachments', $customName);

            $sd =  $sd = DB::table('tblper')
                ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->leftJoin('tbldepartment', 'tbldepartment.id', '=', 'tblper.department')
                ->leftJoin('tblstates', 'tblstates.StateID', '=', 'tblper.stateID')
                ->leftJoin('lga', 'lga.lgaId', '=', 'tblper.lgaID')
                ->where('tblper.ID', '=', $id)
                //->select('*','tbldepartment.department as dept')
                ->first();

            if (!$sd) {
                return back()->with('msg', 'Staff Record Not Found. Please, try again');
            }

            $data = array("Surname" => $sd->surname, "Firstname" => $sd->first_name, "Othernames" => $sd->othernames, "Grade" => $sd->grade, "Step" => $sd->step, "fileNo" => $sd->fileNo, "Bank" => $sd->bank, "phone" => $sd->phone, "E-mail" => $sd->email, "AccountNo" => $sd->AccNo, "Department" => $sd->department, "Appointment Date" => $sd->appointment_date, "Incremental Date" => $sd->incremental_date, "Date of Birth" => $sd->dob, "Home Address" => $sd->home_address, "Physically Challenged" => $sd->challengestatus, "Challenged Details" => $sd->challengedetails, "Alternate Phone" => $sd->alternate_phone, "Alternate Email" => $sd->alternate_email, "Date of Present Appointment" => $sd->date_present_appointment, "Date of Confirmation" => $sd->date_of_confirmation, "Resumption Date" => $sd->resumption_date, "Marital Status" => $sd->maritalstatus, "Gender" => $sd->gender, "Religion" => $sd->religion, "Geopolitical Zone" => $sd->gpz, "State" => $sd->State, "Local Government" => $sd->lga);
            $pre_encode = json_encode($data);

            DB::table('tblper')->where('ID', $id)->update(array(
                'grade'             => $gradeValue,
                'step'              => $stepValue,

                'fileNo'            => $fileNo,
                'bankID'            => $bank,
                'bank_branch'       => $branch,
                'phone'             => $phoneNumber,
                'AccNo'             => $accountNo,
                'department'         => $section,
                'departmentID'         => $section,
                'appointment_date'  => $appointmentDate,
                'incremental_date'  => $incrementalDate,
                'dob'               => $dateOfBirth,
                'home_address'      => $residentialAddress,
                'government_qtr'    => $qurter,
                'leaveaddress'      => $leaveAddress,
                'failurereturn'     => $failureReturn,
                'returnbefore'      => $returnBefore,
                'first_name'             => $fname,
                'surname'              => $sname,
                'othernames'              => $oname,
                'fileNo'            => $fileNo,
                'appointment_date'   => trim($request['appointmentfirst']),
                'challengestatus'    =>  trim($request['challenge']),
                'challengedetails'   => trim($request['challengedetails']),
                'alternate_phone'    => trim($request['altphoneno']),
                'alternate_email'    => trim($request['altemail']),
                'date_present_appointment'  => trim($request['appointmentDate']),
                'date_of_confirmation' => trim($request['confirmationDate']),
                //'firstarrival_date' => trim($request['appointmentfirst']),
                'resumption_date' => trim($request['resumptionDate']),
                'incremental_date' => trim($request['incrementalDate']),
                'maritalstatus' => trim($request['mstatus']),
                'gender' => trim($request['gender']),
                'stateID' => trim($request['state']),
                'lgaID' => trim($request['lga']),
                'gpz' => trim($request['gpz']),
                'email' => trim($request['email']),
                'religion' => trim($request['religion']),
                'passport_url'    => $passportUrl,
                'Designation'        => $request['designation'],
                'designationID'        => $request['designation'],
                'nhfNo'             => $request['nhfNo'],
                'current_state'      => $request['currentstate'],
                'employee_type'     => $request['employeeType'],
                'hremploymentType' => $request['hrEmploymentType'],
                //'staff_status'      =>  1,
                'updated_at'        => date("Y-m-d")
            ));

            $sd1 =  $sd = DB::table('tblper')
                ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->leftJoin('tbldepartment', 'tbldepartment.id', '=', 'tblper.department')
                ->leftJoin('tblstates', 'tblstates.StateID', '=', 'tblper.stateID')
                ->leftJoin('lga', 'lga.lgaId', '=', 'tblper.lgaID')
                ->where('tblper.ID', '=', $id)
                //->select('*','tbldepartment.department as dept')
                ->first();

            $data1 = array("Surname" => $sd1->surname, "Firstname" => $sd1->first_name, "Othernames" => $sd1->othernames, "Grade" => $sd1->grade, "Step" => $sd1->step, "fileNo" => $sd1->fileNo, "Bank" => $sd1->bank, "phone" => $sd1->phone, "E-mail" => $sd1->email, "AccountNo" => $sd1->AccNo, "Department" => $sd1->department, "Appointment Date" => $sd1->appointment_date, "Incremental Date" => $sd1->incremental_date, "Date of Birth" => $sd1->dob, "Home Address" => $sd1->home_address, "Physically Challenged" => $sd1->challengestatus, "Challenged Details" => $sd1->challengedetails, "Alternate Phone" => $sd1->alternate_phone, "Alternate Email" => $sd1->alternate_email, "Date of Present Appointment" => $sd1->date_present_appointment, "Date of Confirmation" => $sd1->date_of_confirmation, "Resumption Date" => $sd1->resumption_date, "Marital Status" => $sd1->maritalstatus, "Gender" => $sd1->gender, "Religion" => $sd1->religion, "Geopolitical Zone" => $sd1->gpz, "State" => $sd1->State, "Local Government" => $sd1->lga);
            $post_encode = json_encode($data1);

            //$size = $request
            //$ext = $request->photo->extension();
            //$filename = $id.'.'.$ext;
            //$location = public_path('justices/photo');

            // $location = "/home/njcgov/payroll.njc.gov.ng/passport";
            // $move = $request->file('photo')->move($location, $filename);

            $logMessage = 'Personal Emolument records updated';
            $message = 'Personal Emolument records updated successfully';
            $this->addLog("$logMessage for $sd1->surname $sd1->first_name $sd1->othernames from $pre_encode to $post_encode");
            return redirect('/personal-emolument/create')->with('msg', $message);
        }

        /* }else{
            return back()->with('Staff Core details not found! Please contact your admin personel for assistance');
        }*/
    }

    public function RefNo()
    {
        $alphabet = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 6; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        //$Reference= $initcode . implode($pass);
        return implode($pass);
    }

    public function create_temp()
    {

        $data['getStaff'] = DB::table('tblper')
            ->where('staff_status', 1)
            ->orderBy('surname', 'asc')
            ->get();
        $data['department'] = DB::table('tbldepartment')
            ->where('courtID', '=', 9)
            ->get();
        $data['getBank'] = DB::table('tblbanklist')
            ->orderBy('tblbanklist.bank', 'Asc')->get();
        $data['statelist'] = DB::table('tblstates')
            ->orderBy('State', 'Asc')->get();
        return view('Emolument.createTemp', $data);
    }


    public function update_temp(Request $request)
    {
        $this->validate(
            $request,
            [
                //'getStaff'                => 'required',
                //'division'              => 'required|string',
                'grade'                 => 'required',
                'bank'                  => 'required',
                //'branch'                => 'string',
                //'phoneNumber'           => 'numeric',
                //'section'               => 'required|string',
                //'appointmentDate'       => 'required|date',
                //'incrementalDate'       => 'date',
                //'dateOfBirth'           => 'date',
                //'residentialAddress'    => 'string',
                //'qurter'                => 'string',
                //'leaveAddress'          => 'string',
                'accountNo'             => 'required|numeric',
            ]
        );
        $id                     = trim($request['getStaff']);
        $fileNo                     = trim($request['fileNo']);
        //$division                   = trim($request['division']);
        $step                       = trim($request['step']);
        $grade                      = trim($request['grade']);
        $bank                       = trim($request['bank']);
        $branch                     = trim($request['branch']);
        $phoneNumber                = trim($request['phoneNumber']);
        $accountNo                  = trim($request['accountNo']);
        $section                    = trim(strtoupper($request['section']));
        $appointmentDate            = trim($request['appointmentDate']);
        $incrementalDate            = trim($request['incrementalDate']);
        $dateOfBirth                = $request['dateOfBirth'];
        $residentialAddress         = $request['residentialAddress'];
        $qurter                     = $request['qurter'];
        $leaveAddress               = $request['leaveAddress'];
        $date                       = date("Y-m-d");
        $returnBefore               = $request['returnBefore'];
        $failureReturn              = $request['failureReturn'];

        $fname                         = trim($request['firstName']);
        $sname                      = trim($request['surname']);
        $oname                      = trim($request['otherNames']);

        if ($step == "Consolidated") {
            $gradeValue = 17;
            $stepValue  = 1;
        } else {
            $gradeValue = $grade;
            $stepValue  = $step;
        }

        $image = $request->file('photo');
        //$path = $request->photo->path();

        if ($image == '') {


            DB::table('tblper_temp')->where('ID', $id)->update(array(
                'grade'             => $gradeValue,
                'step'              => $stepValue,

                'fileNo'            => $fileNo,
                'bankID'            => $bank,
                'bank_branch'       => $branch,
                'phone'             => $phoneNumber,
                'AccNo'             => $accountNo,
                'department'         => $section,
                'appointment_date'  => $appointmentDate,
                'incremental_date'  => $incrementalDate,
                'dob'               => $dateOfBirth,
                'home_address'      => $residentialAddress,
                'government_qtr'    => $qurter,
                'leaveaddress'      => $leaveAddress,
                'failurereturn'     => $failureReturn,
                'returnbefore'      => $returnBefore,
                'first_name'             => $fname,
                'surname'              => $sname,
                'othernames'              => $oname,
                'fileNo'            => $fileNo,
                'appointment_date'   => trim($request['appointmentfirst']),
                'challengestatus'    =>  trim($request['challenge']),
                'challengedetails'   => trim($request['challengedetails']),
                'alternate_phone'    => trim($request['altphoneno']),
                'alternate_email'    => trim($request['altemail']),
                'date_present_appointment'  => trim($request['appointmentDate']),
                'date_of_confirmation' => trim($request['confirmationDate']),
                //'firstarrival_date' => trim($request['appointmentfirst']),
                'resumption_date' => trim($request['resumptionDate']),
                'incremental_date' => trim($request['incrementalDate']),
                'maritalstatus' => trim($request['mstatus']),
                'gender' => trim($request['gender']),
                'stateID' => trim($request['state']),
                'lgaID' => trim($request['lga']),
                'gpz' => trim($request['gpz']),
                'email' => trim($request['email']),
                'religion' => trim($request['religion']),
                'Designation'        => $request['designation'],


                //'staff_status'      =>  1,
                'updated_at'        => date("Y-m-d")
            ));
            $logMessage = 'Personal Emolument records updated';
            $message = 'Personal Emolument records updated successfully';
            $this->addLog($logMessage . ' Division: ' . $this->division);
            return redirect('/personal-emolument/create-temp')->with('msg', $message);
        } else {
            $filename = $image->getClientOriginalName();

            DB::table('tblper_temp')->where('ID', $id)->update(array(
                'grade'             => $gradeValue,
                'step'              => $stepValue,

                'fileNo'            => $fileNo,
                'bankID'            => $bank,
                'bank_branch'       => $branch,
                'phone'             => $phoneNumber,
                'AccNo'             => $accountNo,
                'department'         => $section,
                'appointment_date'  => $appointmentDate,
                'incremental_date'  => $incrementalDate,
                'dob'               => $dateOfBirth,
                'home_address'      => $residentialAddress,
                'government_qtr'    => $qurter,
                'leaveaddress'      => $leaveAddress,
                'failurereturn'     => $failureReturn,
                'returnbefore'      => $returnBefore,
                'first_name'             => $fname,
                'surname'              => $sname,
                'othernames'              => $oname,
                'fileNo'            => $fileNo,
                'appointment_date'   => trim($request['appointmentfirst']),
                'challengestatus'    =>  trim($request['challenge']),
                'challengedetails'   => trim($request['challengedetails']),
                'alternate_phone'    => trim($request['altphoneno']),
                'alternate_email'    => trim($request['altemail']),
                'date_present_appointment'  => trim($request['appointmentDate']),
                'date_of_confirmation' => trim($request['confirmationDate']),
                //'firstarrival_date' => trim($request['appointmentfirst']),
                'resumption_date' => trim($request['resumptionDate']),
                'incremental_date' => trim($request['incrementalDate']),
                'maritalstatus' => trim($request['mstatus']),
                'gender' => trim($request['gender']),
                'stateID' => trim($request['state']),
                'lgaID' => trim($request['lga']),
                'gpz' => trim($request['gpz']),
                'email' => trim($request['email']),
                'religion' => trim($request['religion']),
                'picture'    => $filename,

                //'staff_status'      =>  1,
                'updated_at'        => date("Y-m-d"),
                'Designation'        => $request['designation'],
            ));

            //$size = $request
            //$ext = $request->photo->extension();
            //$filename = $id.'.'.$ext;
            //$location = public_path('justices/photo');
            $location = "/home/njcgov/payroll.njc.gov.ng/passport";
            $move = $request->file('photo')->move($location, $filename);
            $logMessage = 'Personal Emolument records updated';
            $message = 'Personal Emolument records updated successfully';
            $this->addLog($logMessage . ' Division: ' . $this->division);
            return redirect('/personal-emolument/create-temp')->with('msg', $message);
        }

        /* }else{
            return back()->with('Staff Core details not found! Please contact your admin personel for assistance');
        }*/
    }



    public function report_emolument($fileNo = null)
    {
        if ($fileNo <> "") {
            if ((DB::table('tblper')->where('ID', $fileNo)->count()) > 0) {
                $data['getEmolumentReport'] = DB::table('tblper')
                    ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                    ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                    ->where('tblper.divisionID', $this->divisionID)
                    ->where('tblper.staff_status', 1)
                    ->where('tblper.fileNo', $fileNo)
                    ->first();
                return view('Emolument.report', $data);
            } else {
                $data['getNewOldStaff'] = "";
                return redirect('/personal-emolument/create')->with('err', 'Personal Emolument Record not completed. Pls, try again');
            }
        } else {
            return redirect('/personal-emolument/create')->with('err', 'Personal Emolument Record not completed. Pls, try again');
        }
    }


    public function listAll()
    {
        $data['users'] = DB::table('tblper')
            //->where('divisionID', $this->divisionID)
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
            ->where('tblper.divisionID', $this->divisionID)
            ->where('tblper.staff_status', 1)
            ->where('tblper.employee_type', '<>', 'JUDGES')
            ->orderBy('tblper.updated_at', 'Desc')
            ->paginate(10);
        return view('Emolument.view', $data);
    }
    public function getlga(Request $request)
    {

        $id        = $request['stateId'];
        $lga   = DB::table('lga')
            //->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            //->leftJoin('tbldepartment', 'tbldepartment.id', '=', 'tblper.department')
            ->where('stateId', '=', $id)
            //->select('*','tbldepartment.department as depart')
            ->get();
        return response()->json($lga);
    }

    public function autocomplete_STAFF(Request $request)
    {
        $query = $request->input('query');
        $search = DB::table('tblper')
            ->join('tblper', 'tblper.fileNo', '=', 'tblvariation.fileNo')
            //->where('divisionID', $this->divisionID)
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
            ->where('surname', 'LIKE', '%' . $query . '%')
            ->orWhere('first_name', 'LIKE', '%' . $query . '%')
            ->orWhere('fileNo', 'LIKE', '%' . $query . '%')
            ->orderBy('tblvariation.id', 'Desc')
            ->take(6)
            ->get();
        $return_array = null;
        foreach ($search as $s) {
            $return_array[]  =  ["value" => $s->surname . ' ' . $s->first_name . ' ' . $s->othernames . ' - ' . $s->fileNo, "data" => $s->fileNo];
        }
        return response()->json(array("suggestions" => $return_array));
    }


    public function filter_staff(Request $request)
    {
        $filterBy = trim($request['fileNo']);
        if ($filterBy == null) {
            return redirect('/staff/personal-emolument/view/')->with('err', 'No record found !');
        }
        $data['users'] = DB::table('tblper')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
            ->where('tblper.surname', 'LIKE', '%' . $filterBy . '%')
            ->orWhere('tblper.first_name', 'LIKE', '%' . $filterBy . '%')
            ->orWhere('tblper.fileNo', 'LIKE', '%' . $filterBy . '%')
            ->where('tblper.staff_status', 1)
            ->orderBy('tblper.surname', 'Asc')
            ->paginate(20);
        return view('Emolument.view', $data);
    }


    public function listAllNewStaff()
    {
        $data['newStaff'] = DB::table('tblper')
            ->Join('tbldivision', 'tblper.divisionID', '=', 'tbldivision.divisionID')
            ->where('tblper.staff_status', 9)
            ->where('tblper.divisionID', $this->divisionID)
            ->orderBy('tblper.grade', 'Desc')
            ->orderBy('tblper.step', 'Desc')
            ->paginate(20);
        return view('Emolument.viewNewStaff', $data);
    }


    public function newStaff()
    {
        $data['empType'] = DB::table('tblemployment_type')->get();

        return view('Emolument.newStaff', $data);
    }

    public function addNewstaff(Request $request)
    {
        $this->validate(
            $request,
            [
                //'getStaff'                => 'required',
                //'division'                => 'required|string',
                'firstName'                 => 'required',
                'surname'                   => 'required',

            ]
        );


        $fname                        = trim($request['firstName']);
        $sname                        = trim($request['surname']);
        $oname                        = trim($request['otherNames']);
        $fileNo                       = trim($request['fileNo']);
        $emptype                      = trim($request['employmentType']);
        $count = DB::table('tblper')->where('first_name', '=', $fname)->where('surname', '=', $sname)->where('othernames', '=', $oname)->count();
        if ($count > 0) {
            return back()->with('Staff Already Exist');
        } else {

            DB::table('tblper')->insert(array(
                'first_name'        => $fname,
                'surname'           => $sname,
                'othernames'        => $oname,
                'fileNo'            => $fileNo,
                //'staffID'           => $tblperID,
                'employee_type'                  => $emptype,
                'courtID'           => 9,
                'staff_status'      =>  1,
                'updated_at'        => date("Y-m-d"),
                'divisionID'        => 15,
            ));
            return redirect('/add-staff/create')->with('msg', 'Staff Successfully Created');
        }
    }

    public function populateLGA(Request $request)
    {
        $id        = $request['staffId'];
        $staffRecord   = DB::table('tblper')
            ->where('tblper.ID', '=', $id)
            ->first();
        // $request->session()->flash('lg', $staffRecord->lgaID);
        $lga   = DB::table('lga')
            ->where('stateId', '=', $staffRecord->stateID)
            ->get();

        return response()->json($lga);
    }
    public function append(Request $request)
    {
        $id        = $request['staffId'];
        $staffRecord   = DB::table('tblper')
            ->where('tblper.ID', '=', $id)
            ->first();

        $lga   = DB::table('lga')
            ->where('lgaId', '=', $staffRecord->lgaID)
            ->first();

        return response()->json($lga);
    }

    public function showDesignation()
    {
        $data['courts'] =  DB::table('tbl_court')->get();
        $courtSessionId = session('anycourt');
        $data['courtDivisions']  = DB::table('tbldivision')
            ->where('courtID', '=', $courtSessionId)
            ->get();

        $data['desig'] = DB::table('tbldesignation')
            ->where('courtID', '=', 9)
            ->orderBy('grade', 'DESC')
            ->get();

        $data['getStaff'] = DB::table('tblper')
            ->where('staff_status', 1)
            ->orderBy('surname', 'asc')
            ->get();
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        return view('Emolument.updateDesignation', $data);
    }

    public function updateDesignation(Request $request)
    {
        DB::table('tblper')->where('ID', '=', $request['id'])->update(array(
            'Designation'        => $request['designation'],
        ));
        return redirect('/staff/designation/update')->with('msg', 'Successfully Updated');
    }


    public function accountUpdate()
    {
        $data['getStaff'] = DB::table('tblper')
            ->where('staff_status', 1)
            ->orderBy('surname', 'asc')
            ->get();
        $data['department'] = DB::table('tbldepartment')
            ->where('courtID', '=', 9)
            ->get();
        $data['getBank'] = DB::table('tblbanklist')
            ->orderBy('tblbanklist.bank', 'Asc')->get();
        $data['statelist'] = DB::table('tblstates')
            ->orderBy('State', 'Asc')->get();
        $data['desig'] = DB::table('tbldesignation')
            ->where('courtID', '=', 9)
            ->orderBy('grade', 'DESC')
            ->get();
        return view('Emolument.updateAccountDetail', $data);
    }
} //end class
