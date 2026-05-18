<?php

namespace App\Http\Controllers\funds;

use Illuminate\Http\Request;
use App\Http\Requests;
use DB;
use Illuminate\Support\Str;

class CreateStaffController extends ParentController
{
	//
    public $division;
    public $divisionID;

    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->division = $request->session()->get('division');
        $this->divisionID = $request->session()->get('divisionID');
                return $next($request);
        });
    }

    public function create()
    {
        $data['staffList'] = $this->getStaffList();
        $data['countStaffList'] = $this->getCountStaffPerDivision();
        $data['StateList'] = DB::table('tblstates')->select('StateID', 'State')->orderBy('State')->get();
        $data['bankList'] = DB::table('tblbanklist')->select('bankID', 'bank')
        ->orderBy('bank', 'asc')->get();
        $data['lastInserted'] = DB::table('tblper')->orderBy('ID', 'DESC')->select('fileNo')->first();
        $data['readonly'] ="readonly";
        $data['disable'] ="disabled";
        return view('createstaff.create', $data);
    }

    public function findStaff(Request $request)
    {       
        $this->validate($request, [
            'staffList' => 'required|numeric',
        ]);
        $fileNo = $request->input('staffList');
        $staffRecord = DB::table('tblper')
        ->select('ID', 'fileNo', 'surname', 'first_name', 'othernames', 'title', 'Designation', 'rank', 
                 'grade', 'step', 'bankID', 'bankGroup', 'bank_branch', 'AccNo', 'section', 'appointment_date', 'dob', 
                 'home_address', 'government_qtr', 'employee_type', 'gender','divisionID', 'current_state', 
                 'incremental_date', 'nhfNo')
        ->where('fileNo', '=', $fileNo)
        ->first();
        return response()->json($staffRecord);
    }

    
    public function store(Request $request)
    {       
        $this->validate($request, [
            'oldFileNo'            => 'numeric',
            'title'                => 'required|regex:/[a-zA-Z.]/',
            'surname'              => 'required|regex:/^[\pL\s\-]+$/u',
            'firstName'            => 'required|alpha_num',
            'otherNames'           => 'regex:/^[\pL\s\-]+$/u',
            'designation'          => 'required|regex:/^[a-zA-Z0-9,.!?\)\( ]*$/',
            'grade'                => 'required|numeric',
            'step'                 => 'required|numeric',
            'bankID'               => 'required|numeric',
            'bankGroup'            => 'required|numeric',
            'bankBranch'           => 'regex:/^[a-zA-Z0-9,.!? ]*$/',
            'accountNo'            => 'required|numeric',
            'section'              => 'required|regex:/^[\pL\s\-]+$/u',
            'appointmentDate'      => 'required|date',
            'incrementalDate'      => 'required|date',
            'dateofBirth'          => 'required|date',
            'homeAddress'          => 'required|string', 
            'GovernmentQuaters'    => 'string',
            'employeeType'         => 'required|alpha',
            'gender'               => 'required|alpha',
            'currentState'         => 'required|regex:/^[\pL\s\-]+$/u',
            'button'               => 'required|regex:/^[\pL\s\-]+$/u',
            'firstArrivalDate'     => 'date',
            'nationality'          => 'regex:/^[a-zA-Z0-9,.!?\)\( ]*$/',
            'maritalStatus'        => 'alpha',
            'placeOfBirth'         => 'string',
        ]);

        $fileNo           = trim($request['fileNo']);
        $ID               = trim($request['updateValue']);
        $getRedirect      = trim($request['getRedirect']);
        $oldfileNo        = trim($request['oldFileNo']);
        $title            = trim($request['title']);
        $surname          = trim($request['surname']);
        $firstName        = trim($request['firstName']);
        $otherNames       = trim($request['otherNames']);
        $designation      = trim($request['designation']);
        $grade            = trim($request['grade']);
        $step             = trim($request['step']);
        $bankID           = trim($request['bankID']);
        $bankGroup        = trim($request['bankGroup']);
        $bankBranch       = trim($request['bankBranch']);
        $accountNo        = trim($request['accountNo']);
        $section          = trim($request['section']);
        $appointmentDate  = trim($request['appointmentDate']);
        $incrementalDate  = trim($request['incrementalDate']);
        $dateofBirth      = trim($request['dateofBirth']);
        $homeAddress      = trim($request['homeAddress']);
        $quater           = trim($request['GovernmentQuaters']);
        $employeeType     = trim($request['employeeType']);
        $gender           = trim($request['gender']);
        $currentState     = trim($request['currentState']);
        $nationality      = trim($request['nationality']);
        $firstArrivalDate = trim($request['firstArrivalDate']);
        $placeOfBirth     = trim($request['placeOfBirth']);
        $maritalStatus    = trim($request['maritalStatus']);
        $button           = $request['button'];

        //check if staff belongs to this this->division
        $getstaffDiv = DB::table('tblper')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->where('tblper.fileNo', '=', $fileNo)
                ->select('division')
                ->first();
         if(!(DB::table('tblper')->select('divisionID')->where('fileNo', '=', $fileNo)->where('divisionID', $this->divisionID)->count()))
         {
            return back()->with('err', 'Staff Details cannot be viewed in this Division. Staff belongs to '. $getstaffDiv->division .'  division. This means, Staff can only be viewed from '. $getstaffDiv->division .' division');
         }
        //end checking
        
        //insert data into database -> table -> 'tblper'
        if($button == 'Add new staff')
        {
            DB::beginTransaction();
            $fileNo = DB::table('tblper')->insertGetId(array( 
            //'fileNo'            => $fileNo, 
            'title'             => $title,
            'surname'           => Str::title($surname),
            'first_name'        => Str::title($firstName),
            'othernames'        => Str::title($otherNames),
            'Designation'       => $designation,
            'grade'             => $grade,
            'step'              => $step,
            'bankID'            => $bankID,
            'bankGroup'         => $bankGroup,
            'bank_branch'       => $bankBranch,
            'AccNo'             => $accountNo,
            'section'           => $section,
            'appointment_date'  => $appointmentDate,
            'incremental_date'  => $incrementalDate,
            'dob'               => $dateofBirth,
            'home_address'      => $homeAddress,
            'government_qtr'    => $quater,
            'employee_type'     => $employeeType,
            'gender'            => $gender,
            'firstarrival_date' => $firstArrivalDate,
            'nationality'       => $nationality,
            'current_state'     => $currentState,
            'divisionID'        => $this->divisionID,
            'maritalstatus'     => $maritalStatus,
            'placeofbirth'      => $placeOfBirth,
            'created_at'        => (date('Y-m-d')),
            'updated_at'        => (date('Y-m-d'))         
            ));
            //insert FileNo
            DB::table('tblper')->where('ID', $fileNo)->update(array( 
                'fileNo'            => $fileNo
            ));
            DB::insert('insert into tblcv (fileNo) values (?)', [$fileNo]);
            $this->addLog('new staff added with fileno = '.$fileNo);
            DB::commit();
            $fullname           = Str::title($surname) ." ". Str::title($firstName);
            return redirect('/staff/create')->with('msg', 'New staff added successfully. '. $fullname." FileNo is: ". $fileNo.".  Your can also search for staff by name to see his/her File Number.");

        }
        else if($button == 'Update staff')
        {
            $this->validate($request, [
                'fileNo'               => 'required|numeric',
                'updateValue'          => 'required|numeric',
            ]);

            DB::table('tblper')->where('ID', $ID)->update(array( 
            'fileNo'            => $fileNo, 
            'title'             => $title,
            'surname'           => Str::title($surname),
            'first_name'        => Str::title($firstName),
            'othernames'        => Str::title($otherNames),
            'Designation'       => $designation,
            'grade'             => $grade,
            'step'              => $step,
            'bankID'            => $bankID,
            'bankGroup'         => $bankGroup,
            'bank_branch'       => $bankBranch,
            'AccNo'             => $accountNo,
            'section'           => $section,
            'appointment_date'  => $appointmentDate,
            'incremental_date'  => $incrementalDate,
            'dob'               => $dateofBirth,
            'home_address'      => $homeAddress,
            'government_qtr'    => $quater,
            'employee_type'     => $employeeType,
            'gender'            => $gender,
            'firstarrival_date' => $firstArrivalDate,
            'nationality'       => $nationality,
            'current_state'     => $currentState,
            'staff_status'      => 1,
            'maritalstatus'     => $maritalStatus,
            'placeofbirth'      => $placeOfBirth,
            'divisionID'        => $this->divisionID,
            'updated_at'        => (date('Y-m-d'))           
            )); 
            $this->addLog('staff modified/Updated with fileno = '.$fileNo);
            if($getRedirect <> "")
            {
                return redirect($getRedirect)->with('msg', 'Staff record successfully updated!');  
            }
            return redirect('/staff/create')->with('msg', 'Staff record successfully updated!');       
        } 
             
    }

    //Bio-Data Report
    public function report($fileNo = null)
    {   
        if(is_null($fileNo)){
            return redirect('profile/details');
        }
        if( !(DB::table('tblper')->where('fileNo', '=', $fileNo)->first())){
            return redirect('profile/details');
        }else{
            $data['staffFullDetails'] = DB::table('tblper')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->where('tblper.fileNo', '=', $fileNo)
                ->first();
        }
        return view('Report.BioDataReport', $data);
    }


    //Account Details Report
    public function accountReport($fileNo = null)
    {   
        if(is_null($fileNo)){
            return redirect('profile/details');
        }
        if( !(DB::table('tblper')->where('fileNo', '=', $fileNo)->first())){
            return redirect('profile/details');
        }else{
            $data['staffFullDetails'] = DB::table('tblper')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->where('tblper.fileNo', '=', $fileNo)
                ->first();
        }
        return view('Report.AccountDetailsReport', $data);
    }



}
