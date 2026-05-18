<?php

namespace App\Http\Controllers\hr;



use Illuminate\Http\Request;
use App\Helpers\FileUploadHelper;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\hr\DatabaseDocumentationController;

class DocumentationController extends DatabaseDocumentationController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //When you click the Staff Documentation Link
    public function candidate()
    {

        $data['candidateDetails'] = DB::table('tblcandidate')
            ->leftjoin('tblstates', 'tblcandidate.state', '=', 'tblstates.StateID')
            ->leftjoin('lga', 'tblcandidate.lga', '=', 'lga.lgaId')
            ->leftjoin('tblinterview_score_sheet', 'tblinterview_score_sheet.candidateID', 'tblcandidate.candidateID')
            ->where('tblcandidate.candidate_status', 1)
            ->where('tblcandidate.approval_status', '=', 1)
            ->where('tblcandidate.documentation_status', 0)
            ->where('tblinterview_score_sheet.is_final_approval', 1)
            ->get();

        $data['newEmployees'] = DB::table('tblcandidate')
            ->leftjoin('tblstates', 'tblcandidate.state', '=', 'tblstates.StateID')
            ->leftjoin('lga', 'tblcandidate.lga', '=', 'lga.lgaId')
            ->leftjoin('tblinterview_score_sheet', 'tblinterview_score_sheet.candidateID', 'tblcandidate.candidateID')
            ->where('tblcandidate.candidate_status', 1)
            ->where('tblcandidate.approval_status', '=', 1)
            ->where('tblcandidate.documentation_status', 0)
            ->where('tblinterview_score_sheet.is_final_approval', 1)
            ->get();

        return view('hr.candidate.successful_candidate', $data);
    }
    public function candidateSearch(Request $request)
    {

        $data['candidateDetails'] = DB::table('tblcandidate')
            ->where('candidate_status', 1)
            ->where('approval_status', '=', 1)
            ->where('tblcandidate.candidateID', '=', $request->search)
            ->leftjoin('tblstates', 'tblcandidate.state', '=', 'tblstates.StateID')
            ->leftjoin('lga', 'tblcandidate.lga', '=', 'lga.lgaId')
            ->get();

        $data['newEmployees'] = DB::table('tblcandidate')
            ->where('candidate_status', 1)
            ->where('approval_status', '=', 1)
            ->get();

        return view('hr.candidate.successful_candidate', $data);
    }

    /////////
    public function documentStaff($id)
    {
        $details = DB::table('tblcandidate')->where('candidate_status', 1)->where('candidateID', $id)->first();
        $exists =  DB::table('tblper')
            // ->where('surname', $details->surname)
            // ->where('first_name', $details->first_name)
            // ->where('othernames', $details->othernames)
            ->where('interviewCandidateId', $id)
            ->exists();

        if ($exists) {

            $perID =  DB::table('tblper')
                // ->where('surname', $details->surname)
                // ->where('first_name', $details->first_name)
                // ->where('othernames', $details->othernames)
                ->where('interviewCandidateId', $id)
                ->first();

            $data = $this->getTabLevel(1);
            $data['StaffList'] = $this->getStaffList();

            Session::put('StaffList', $data['StaffList']);
            Session::put('fileNo', $perID->ID);

            $data['fileNo'] = $perID->ID;
            $data['staffID'] = '';
            $data['StaffNames'] = DB::table('tblcandidate')->where('candidate_status', 1)->where('candidateID', $id)->first();
            $data['progress'] = '';
            $data['prog'] = '';
            
            return redirect('http://localhost:3000/dashboard/hr/employees/documentation/' . $perID->ID);
        } else {

            $perID = DB::table('tblper')->insertGetId([
                'surname' => $details->surname,
                'first_name' => $details->first_name,
                'othernames' => $details->othernames,
                'interviewCandidateId' => $id,
                // 'resumption_date' => date('Y-m-d'),
            ]);

            $data = $this->getTabLevel(1);
            $data['StaffList'] = $this->getStaffList();

            Session::put('StaffList', $data['StaffList']);
            Session::put('fileNo', $perID);

            $data['fileNo'] = $perID;
            $data['staffID'] = '';
            $data['StaffNames'] = DB::table('tblcandidate')->where('candidate_status', 1)->where('candidateID', $id)->first();
            $data['progress'] = '';
            $data['prog'] = '';
            
            return redirect('http://localhost:3000/dashboard/hr/employees/documentation/' . $perID);
        }
    }

    public function constinueStaffDocumentation($id)
    {
        $perID =  DB::table('tblper')
            ->where('ID', $id)
            ->first();

        $data = $this->getTabLevel(1);
        Session::put('fileNo', $perID->ID);

        $data['fileNo'] = $perID->ID;
        $data['staffID'] = '';
        $data['progress'] = '';
        $data['prog'] = '';
        
        return redirect('http://localhost:3000/dashboard/hr/employees/documentation/' . $perID->ID);
    }

    public function index()
    {
        //dd('ddd');
        // Session::forget('fileNo');
        // Session::forget('StaffList');
        // Session::forget('StaffNames');
        //dd(Session::get('fileNo'));

        $data = $this->getTabLevel(1);
        $data['StaffList'] = $this->getStaffList();

        Session::put('StaffList', $data['StaffList']);

        //$data['ID'] = $id;
        $data['fileNo'] = Session::get('fileNo');
        $data['staffID'] = '';
        $data['StaffNames'] = DB::table('tblper')->where('ID', Session::get('fileNo'))->first();

        $data['progress'] = '';
        $data['prog'] = '';


        return view('documentation.StaffDoc', $data);
    }

    //Controller for when you search for a particular Staff
    public function getStaffInfo(Request $request)
    {

        $data['fileNo'] = $request->input('fileNo');

        //$data['staffID'] = $request->input('staffID');
        //dd($data['fileNo']);
        Session::put('fileNo', $data['fileNo']);
        //Session::put('staffID',$data['staffID']);

        $data['StaffList'] = $this->getStaffList();
        Session::put('StaffList', $data['StaffList']);
        $staff = $this->getStaff($data['fileNo']);
        //dd($staff);
        if (!$staff) return redirect('/start-documentation');
        //dd($data['fileNo']);
        $data['StaffNames'] = DB::table('tblper')->where('ID', Session::get('fileNo'))->first();

        Session::put('StaffNames', $data['StaffNames']);

        $progress = $this->getProgress($data['fileNo']);
        //redirects you to where the staff left off
        switch ($progress) {
            case 6:
                return redirect('/documentation-basic-info');
                break;
            case 7:
                return redirect('/documentation-contact');
                break;
            case 8:
                return redirect('/documentation-placeofbirth');
                break;
            case 9:
                return redirect('/documentation-education');
                break;
            case 10:
                return redirect('/documentation-marital-status');
                break;
            case 11:
                return redirect('/documentation-nextofkin');
                break;
            case 12:
                return redirect('/documentation-children');
                break;
            case 13:
                return redirect('/documentation-previous-employment');
                break;
            case 14:
                return redirect('/documentation-attachment');
                break;
            case 15:
                return redirect('/documentation-account');
                break;
            case 16:
                return redirect('/documentation-others');
                break;
            case 17:
                return redirect('/documentation-preview');
                break;
            case 18:
                return redirect('/documentation-preview');
                break;
            default:

                return redirect('/documentation');
        }
    }

    public function getBasicInfo()
    {
        //dd(Session::get('fileNo'));
        $data = $this->getTabLevel(2);
        $fileNo = Session::get('fileNo');
        //$userID = Session::get('userID');
        $check = DB::table('tblper')->where('ID', '=', $fileNo)->where('courtID', '=', 0)->exists();

        if ($check) {
            $uid = DB::table('tblper')->where('ID', '=', $fileNo)->first();
        } else {
            $uid = DB::table('tblper')->where('ID', '=', $fileNo)->first();
        }
        //dd($userID);
        $data['fileNo'] = Session::get('fileNo');
        $data['staffFileNo'] = DB::table('tblper')->where('ID', '=', $fileNo)->first();
        if ($data['staffFileNo']->fileNo == '') {
            $data['mainStaffFileNo'] = $this->generateNextStaffFileNo();
        } else {
            $data['mainStaffFileNo'] = $data['staffFileNo']->fileNo;
        }


        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
        $data['progress'] = $this->getProgress($data['fileNo']);
        $data['fillUpForm'] = $this->fillUpForm($data['fileNo']);
        //Employment Type
        $data['employmentType'] = DB::table('tblemployment_type')->where('active', 1)->get();
        $data['hrEmploymentType'] = DB::table('hr_employment_type')->get();
        $data['departments'] = DB::table('tbldepartment')->get();
        // $data['designation'] = DB::table('tbldesignation')->where('courtID', '=', $uid->courtID)->get();
        $data['designation'] = DB::table('tbldesignation')->where('departmentID', '=', $data['staffFileNo']->departmentID)->get();
        // dd($data['StaffNames']->designationID);
        $data['StateList'] = DB::Select("SELECT * FROM `tblstates`");

        Session::put('progress', $data['progress']);
        //dd($fileNo);
        if (!empty($fileNo)) {
            $data['prog'] = 6;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
            return view('hr.documentation.StaffDoc', $data);
        } else {
            return redirect('/start-documentation');
        }
    }

    //processing ajax for populating of designation
    public function loadDesignation(Request $request)
    {
        $deptId = $request->get('dept_id');

        $data = DB::table('tbldesignation')->where('departmentID', '=', $deptId)->get();
        return response()->json($data);
    }

    public function submitBasicInfo(Request $request)
    {
        // dd($request->all());
        $fileNo         =   Session::get('fileNo');
        $fileNox        =   $request->input('fileNox');
        
        if (empty($fileNox) && !empty($fileNo)) {
            $existing = DB::table('tblper')->where('ID', $fileNo)->value('fileNo');
            if (!empty($existing)) {
                $fileNox = $existing;
            } else {
                $fileNox = $this->generateNextStaffFileNo();
            }
        }
        
        $title          =   $request->input('title');
        $gender         =   $request->input('gender');
        $dateofBirth    =   $request->input('dateofBirth');
        $placeofBirth   =   $request->input('placeofBirth');
        
        $employmentType =   1;
        $hremploymentType =   $request->input('hremploymentType');
        $officeshift         =   $request->input('officeshift');
        
        $department     =   $request->input('department');
        $departmentID     =   $request->input('department');
        $designation    =   $request->input('designation');
        $designationID    =   $request->input('designation');
        $presentApptmnt =   $request->input('presentAppointment2');
        // $fristApptmnt   =   $request->input('firstAppointment2');
        // $resumptionDate   =   $request->input('dateofResumption2');
        // dd($designation);
        //dd(date('Y-m-d', strtotime($presentApptmnt)));

        if (!empty($fileNo)) {

            $this->basicSetUp($fileNox, $fileNo, $title, $gender, date('Y-m-d', strtotime($dateofBirth)), $placeofBirth, $employmentType, $hremploymentType,  $department, $departmentID, $designation, $designationID, date('Y-m-d', strtotime($presentApptmnt)),$officeshift, );
            $d = Session::get('progress');
            if ($d < 7) {
                $this->setProgress($fileNo, 7);
            }
            return redirect('/documentation-contact');
        }
        return redirect('/documentation')->with('error', 'An error occured');
    }

    //Controller to go to the 'contact Address' Page.
    public function getContact_11_03_2026()
    {
        $data = $this->getTabLevel(3);
        $data['fileNo'] = Session::get('fileNo');
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
        $data['progress'] = $this->getProgress($data['fileNo']);
        Session::put('progress', $data['progress']);

        if (!empty($data['fileNo'])) {
            $data['prog'] = 7;
            $data['staffInfo'] = DB::table('tblper')->where('ID', $data['fileNo'])->first();
            if ($data['staffInfo']->interviewCandidateId != '') {
                $data['staffCandidateInfo'] = DB::table('tblcandidate')->where('candidateID', $data['staffInfo']->interviewCandidateId)->first();
            }

            return view('hr.documentation.StaffDoc', $data);
        } else {
            return redirect('/documentation');
        }
    }

    public function getContact()
    {
        $data = $this->getTabLevel(3);

        $data['fileNo'] = Session::get('fileNo');
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = DB::table('tblper')->where('ID', $data['fileNo'])->first();
        $data['progress'] = $this->getProgress($data['fileNo']);

        Session::put('progress', $data['progress']);

        // If fileNo is empty, redirect back
        if (empty($data['fileNo'])) {
            return redirect('/documentation');
        }

        // Staff primary record
        $data['prog'] = 7;
        $data['staffInfo'] = DB::table('tblper')->where('ID', $data['fileNo'])->first();

        // Always define variable to avoid undefined variable error
        $data['staffCandidateInfo'] = null;

        // If staff has interviewCandidateId, get candidate info
        if (!empty($data['staffInfo']->interviewCandidateId)) {
            $data['staffCandidateInfo'] = DB::table('tblcandidate')
                ->where('candidateID', $data['staffInfo']->interviewCandidateId)
                ->first();
        }

        return view('hr.documentation.StaffDoc', $data);
    }


    //Controller to go to the 'contact Address' Page.
    public function submitContact(Request $request)
    {

        $fileNo = Session::get('fileNo');

        $email = $request->input('email');
        $alternateEmail = $request->input('alternateEmail');
        $phone = $request->input('phone');
        $alternativePhone = $request->input('alternativePhone');
        $physicalAddress = $request->input('physicalAddress');


        if (!empty($fileNo)) {

            $this->contactSetUp(
                $fileNo,
                $email,
                $alternateEmail,
                $phone,
                $alternativePhone,
                $physicalAddress
            );
            $d = Session::get('progress');
            if ($d < 8) {
                $this->setProgress($fileNo, 8);
            }
            return redirect('/documentation-placeofbirth');
        }
        return redirect('/documentation')->with('error', 'An error occured');
    }

    public function getPlaceOfBirth()
    {
        $data = $this->getTabLevel(4);
        $fileNo = Session::get('fileNo');

        $data['fileNo'] = Session::get('fileNo');
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
        $data['progress'] = $this->getProgress($data['fileNo']);
        Session::put('progress', $data['progress']);

        if (!empty($fileNo)) {
            $data['prog'] = 8;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();

            $data['StateList'] = DB::Select("SELECT * FROM `tblstates`");
            $lgaID = DB::Table('tblper')->where('ID', $data['fileNo'])->value('lgaID');
            $data['Lga'] = DB::Table('lga')->where('lgaid', $lgaID)->get();
            //dd( $data['Lga']);

            return view('hr.documentation.StaffDoc', $data);
        } else {
            return redirect('/documentation');
        }
    }

    public function submitPlaceOfBirth(Request $request)
    {
        $fileNo = Session::get('fileNo');
        $state = $request->input('state');
        $lga = $request->input('lga');
        $address = $request->input('address');

        if (!empty($fileNo)) {

            $this->validate($request, [
                'address' => 'required|string',
            ]);

            $this->placeofBirthSetUp($fileNo, $state, $lga, $address);

            $d = Session::get('progress');
            if ($d < 9) {
                $this->setProgress($fileNo, 9);
            }

            return redirect('/documentation-education');
        }

        return redirect('/documentation')->with('error', 'An error occured');
    }

    public function LGA(Request $request)
    {
        $stateId = $request['id'];

        $data = DB::table('lga')->where('stateId', '=', $stateId)->get();
        return response()->json($data);
    }

    //education
    public function getEducation()
    {

        $data = $this->getTabLevel(5);
        $fileNo = Session::get('fileNo');

        $data['list'] = DB::table('tbleducation_category')->get();
        $data['fileNo'] = Session::get('fileNo');
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
        $data['progress'] = $this->getProgress($data['fileNo']);
        Session::put('progress', $data['progress']);

        if (!empty($fileNo)) {

            $data['id'] = 0;
            $data['staffid'] = 0;
            $data['prog'] = 9;

            $data['staffDETAILS'] = DB::table('tbleducations')
                ->leftjoin('tbleducation_category', 'tbleducations.categoryID', '=', 'tbleducation_category.edu_categoryID')
                //->select('tblstaffAttachment.staffID','tblstaffAttachment.filedesc','tblstaffAttachment.filepath','tblstaffAttachment.id')
                ->where('staffid', '=', $data['fileNo'])
                ->orderby('categoryID', 'asc')
                ->get();

            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();

            return view('hr.documentation.StaffDoc', $data);
        } else {
            return redirect('/documentation');
        }
    }


    //save document
    public function saveDocument(Request $request)
    {

        $this->validate($request, [

            'category'   => 'required|string',
            'school'      => 'required|string',
            'from'        => 'required|date|before_or_equal:to',
            'to'          => 'required|date|after_or_equal:from',
            'description' => 'required|string',
            'class_of_qualification'  => 'required|string',
            'certificate' => 'mimes:pdf,doc,docx,jpeg,jpg,gif,png,bmp|max:2048',

        ]);

        //processing insert into attachment table

        if ($request->hasfile('certificate')) {

            $file = $request->file('certificate');
            $customName = $this->RefNo() . '.' . $file->getClientOriginalExtension();

            // Use helper (automatically stores to local or S3)
            $fileUrl = FileUploadHelper::upload($file, 'CertificatesHeld', $customName);

            $getID = DB::table('tbleducations')->insertGetId([

                'staffid'        => $request['fileNo'],
                'categoryID'     => $request['category'],
                'schoolattended' => $request['school'],
                'schoolfrom'     => $request['from'],
                'schoolto'       => $request['to'],
                'certificateheld' => $request['description'],
                'degreequalification' => $request['class_of_qualification'],
                'document' => $fileUrl,

            ]);

            //}
        } else {

            $getID = DB::table('tbleducations')->insertGetId([

                'staffid'        => $request['fileNo'],
                'categoryID'     => $request['category'],
                'schoolattended' => $request['school'],
                'schoolfrom'     => $request['from'],
                'schoolto'       => $request['to'],
                'certificateheld' => $request['description'],
                'degreequalification' => $request['class_of_qualification'],

            ]);
        }


        return back()->with('message', 'Education Details Saved successfully!');
    }

    public function submitEducation(Request $request)
    {
        $fileNo = Session::get('fileNo');

        if (!empty($fileNo)) {

            $d = Session::get('progress');
            if ($d < 10) {
                $this->setProgress($fileNo, 10);
            }
            return redirect('/documentation-marital-status');
        }

        return redirect('/documentation')->with('error', 'An error occured');
    }

    //delete attachement
    public function deleteDocument($id)
    {
        //dd($id);
        DB::table('tbleducations')->where('id', $id)->delete();

        return back()->with('message', 'Document Removed!');
    }


    //Controller to go to the 'Marital Information' Page.
    public function getMarital()
    {

        $data = $this->getTabLevel(6);
        $data['fileNo'] = Session::get('fileNo');
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
        $data['progress'] = $this->getProgress($data['fileNo']);
        Session::put('progress', $data['progress']);

        if (!empty($data['fileNo'])) {
            $data['prog'] = 10;
            $data['status'] = DB::Table('tblmaritalStatus')->get();
            $data['maritalStatus'] = DB::Table("tbldateofbirth_wife")->where('staffid', $data['fileNo'])->first();
            $data['relationship'] = DB::Table("tblper")->where('ID', $data['fileNo'])->value('maritalstatus');
            //dd($data['relationship']);
            return view('hr.documentation.StaffDoc', $data);
        } else {
            return redirect('/documentation');
        }
    }



    //Controller for submitting the 'Marital Information' Page.
    public function submitMarital(Request $request)
    {

        //dd($request->all());
        $fileNo = Session::get('fileNo');
        //dd($fileNo);
        $marital_Status = $request->input('status');
        $maritalStatus = DB::Table("tblper")->where('tblper.ID', $fileNo)
            ->leftjoin('tblmaritalStatus', 'tblper.maritalstatus', '=', 'tblmaritalStatus.ID')
            ->value('marital_status');
        // dd($maritalStatus);
        $dom = $request->input('dataOfMarriage');
        //dd($dom);
        $fullname = $request->input('spouseName');
        $dob = $request->input('spouseDateOfBirth');
        $address = $request->input('spouseAddress');

        if ($maritalStatus == 'Married') {
            $this->validate(
                $request,
                [
                    'dataOfMarriage' => 'required',
                    'spouseName' => 'required',
                    'spouseDateOfBirth' => 'required',
                    'spouseAddress' => 'required',
                ]
            );
        }
        if (!empty($fileNo)) {

            $this->relationshipSetUp($fileNo, $marital_Status, $marital_Status, date('Y-m-d', strtotime($dom)), $fullname, date('Y-m-d', strtotime($dob)), $address);

            $d = Session::get('progress');
            if ($d < 11) {
                $this->setProgress($fileNo, 11);
            }
            return redirect('/documentation-nextofkin');
        }

        return redirect('/documentation')->with('error', 'An error occured');
    }


    public function getNextOfKin()
    {
        $data = $this->getTabLevel(7);
        $fileNo = Session::get('fileNo');

        $data['fileNo'] = Session::get('fileNo');
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
        $data['progress'] = $this->getProgress($data['fileNo']);
        Session::put('progress', $data['progress']);

        if (!empty($fileNo)) {
            $data['prog'] = 11;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
            //$data['nextOfKin'] = DB::Table("tblnextofkin")->where('staffid', $data['fileNo'])->first();
            $data['nextOfKins'] = DB::Table("tblnextofkin")->where('staffid', $data['fileNo'])->get();
            //die(json_encode($data['nextOfKins']));
            switch (count($data['nextOfKins'])) {
                case 0:
                    $data['nextOfKins'][] = json_decode('{"kinID":"","fileNo":"","uniqueID":"","fullname":"","relationship":"","address":"","phoneno":"","updated_at":"","staffid":""}');
                    $data['nextOfKins'][] = json_decode('{"kinID":"","fileNo":"","uniqueID":"","fullname":"","relationship":"","address":"","phoneno":"","updated_at":"","staffid":""}');
                    break;
                case 1:
                    $data['nextOfKins'][] = json_decode('{"kinID":"","fileNo":"","uniqueID":"","fullname":"","relationship":"","address":"","phoneno":"","updated_at":"","staffid":""}');
                    break;

                default:
            }
            //dd($data['nextOfKins']);
            $data['relationship'] = DB::Table('tbldependant_relationship')->get();



            return view('hr.documentation.StaffDoc', $data);
        } else {
            return redirect('/documentation');
        }
    }

    public function submitNextOfKin(Request $request)
    {

        $fileNo = Session::get('fileNo');
        $fullName = $request->input('fullName');
        $phoneNumber = $request->input('phoneNumber');
        $physicalAddress = $request->input('physicalAddress');
        $relationship = $request->input('relationship');


        if (!empty($fileNo)) {
            DB::Delete("DELETE FROM `tblnextofkin` WHERE `staffid`='$fileNo'");

            for ($i = 1; $i <= count($_POST['fullName']); $i++) {
                //echo $_POST['fullName'][$i];
                $this->nextOfKinSetUp($fileNo, $_POST['fullName'][$i], $_POST['phoneNumber'][$i], $_POST['physicalAddress'][$i], $_POST['relationship'][$i]);
            }

            $d = Session::get('progress');
            if ($d < 12) {
                $this->setProgress($fileNo, 12);
            }
            return redirect('/documentation-children');
        }

        return redirect('/documentation')->with('error', 'An error occured');
    }


    public function getChildren()
    {

        $data = $this->getTabLevel(8);
        $fileNo = Session::get('fileNo');
        $data['fileNo'] = Session::get('fileNo');
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
        $data['progress'] = $this->getProgress($data['fileNo']);
        Session::put('progress', $data['progress']);

        if (!empty($fileNo)) {
            $data['prog'] = 12;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
            $data['children'] = DB::Table("tblchildren_particulars")->where('staffid', $data['fileNo'])->get();

            return view('hr.documentation.StaffDoc', $data);
        } else {
            return redirect('/documentation');
        }
    }

    public function submitChildren(Request $request)
    {    //dd($request->all());

        $fileNo = Session::get('fileNo');
        //dd($data['staffID']);
        $childrenname =  $request->input('fullname');
        $childrendob =  $request->input('childDateOfBirth');
        $childrengender =  $request->input('gender');

        if (!empty($fileNo)) {
            $this->childrenSetUp($fileNo, $childrenname, $childrendob,  $childrengender);

            $d = Session::get('progress');
            if ($d < 13) {
                $this->setProgress($fileNo, 13);
            }
            return redirect('/documentation-previous-employment');
        }

        return redirect('/documentation')->with('error', 'An error occured');
    }


    public function getPrevEmployment()
    {
        $data = $this->getTabLevel(9);
        $fileNo = Session::get('fileNo');

        $data['fileNo'] = Session::get('fileNo');
        //dd($data['staffID']);
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
        $data['progress'] = $this->getProgress($data['fileNo']);
        $data['authCheckby'] = DB::table('users')->where('id', Auth()->user()->id)->first();
        Session::put('progress', $data['progress']);

        if (!empty($fileNo)) {
            $data['prog'] = 13;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
            $data['prevEmployment'] = DB::Table("previous_servicedetails")->where('staffid', $data['fileNo'])->get();

            /*
            $prevEmploymentPeriod = DB::table('previous_servicedetails')->where('staffid', $data['staffID'])->pluck('period');
             //dd($prevEmploymentPeriod);
            $i=0;
             foreach ($prevEmploymentPeriod as $thePeriods) {
		     $dates= (explode(" / ",$thePeriods));

			$i++;
			$data['dates'][$i]=$dates;
			}*/

            return view('hr.documentation.StaffDoc', $data);
        } else {
            return redirect('/documentation');
        }
    }

    public function submitPrevEmployment(Request $request)
    {
        //DD($request->all());
        //$staffID = Session::get('staffID');
        $fileNo = Session::get('fileNo');
        $prevemp =  $request->input('employment');
        $previousPay =  $request->input('previousPay');
        $fromPrevEmp = $request->input('fromPrevEmp');
        $toPrevEmp = $request->input('toPrevEmp');
        $filePage = $request->input('filePage');
        $checkBy = $request->input('checkedBy');
        $appt_count = $request->input('appt_count');

        //dd($fromPrevEmp);
        if (!empty($fileNo)) {
            //DB::DELETE("DELETE FROM previous_servicedetails WHERE `staffid` = '$staffID'");
            $this->previousEmploymentSetUp($fileNo, $prevemp, $previousPay, $fromPrevEmp, $toPrevEmp, $filePage, $checkBy);
            /*
     	$fileNo=$this->fileD($staffID);
     	$numofemploy = count($prevemp);

            for($i = 0; $i < $appt_count; $i++){

                $employment = $prevemp[$i];
                $pay  = $previousPay[$i];
                $from = $fromPrevEmp[$i];
                $to   = $toPrevEmp[$i];
                $filePage = $filePage[$i];
                $checkBy  = $checkBy[$i];

                if(!empty($employment) && !empty($pay)){
                DB::table('previous_servicedetails')->insert(array(
        		'staffid'	    => $staffID,
        		'fileNo'    	=> $fileNo,
        		'previousSchudule'    	=> $employment,
        		'totalPreviousPay'    	=> $pay,
        		'fromDate'              => date('Y-m-d', strtotime($from)),
        		'toDate'                => date('Y-m-d', strtotime($to)),
        		'filePageRef'           => $filePage,
        		'checkedby'             => $checkBy,
            	));

                 $data['message'] = 'Employment record has been saved!';
                        }
                    }

            */
            $d = Session::get('progress');
            if ($d < 14) {
                $this->setProgress($fileNo, 14);
            }
            return redirect('/documentation-attachment');
        }

        return redirect('/documentation')->with('error', 'An error occured');
    }

    //attachment
    public function getAttachment()
    {

        $data = $this->getTabLevel(10);
        $fileNo = Session::get('fileNo');

        $data['fileNo'] = Session::get('fileNo');
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
        $data['progress'] = $this->getProgress($data['fileNo']);
        Session::put('progress', $data['progress']);

        if (!empty($fileNo)) {

            $data['id'] = 0;
            $data['staffid'] = 0;
            $data['prog'] = 14;

            $data['staffDETAILS'] = DB::table('tblstaffAttachment')
                ->select('tblstaffAttachment.staffID', 'tblstaffAttachment.filedesc', 'tblstaffAttachment.filepath', 'tblstaffAttachment.id')
                ->where('staffID', '=', $data['fileNo'])
                ->get();

            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();

            $data['prevEmployment'] = DB::Table("tblpreviousemployment_rec")->where('staffid', $data['fileNo'])->get();

            $prevEmploymentPeriod = DB::table('tblpreviousemployment_rec')->where('staffid', $data['fileNo'])->pluck('period');
            //dd($prevEmploymentPeriod);
            $i = 0;
            foreach ($prevEmploymentPeriod as $thePeriods) {
                $dates = (explode(" / ", $thePeriods));

                $i++;
                $data['dates'][$i] = $dates;
            }

            return view('hr.documentation.StaffDoc', $data);
        } else {
            return redirect('/documentation');
        }
    }

    public function submitAttachment(Request $request)
    {
        $fileNo = Session::get('fileNo');

        if (!empty($fileNo)) {

            $d = Session::get('progress');
            if ($d < 15) {
                $this->setProgress($fileNo, 15);
            }
            return redirect('/documentation-account');
        }

        return redirect('/documentation')->with('error', 'An error occured');
    }

    //save attachment
    public function saveAttachment(Request $request)
    {

        $this->validate($request, [

            'description'      => 'required|string',
            'filename.*' => 'mimes:pdf,doc,docx,jpeg,jpg,gif,png,bmp|max:2048',

        ]);
        $staffid = trim($request['fileNo']);
        $desc = trim($request['description']);

        //processing insert into attachment table

        if ($request->hasfile('filename')) {
            // foreach ($request->file('filename') as $file) {
            // $name = time() . '.' . $file->getClientOriginalExtension();
            // $file->move(public_path('staffattachments'), $name);

            //$file->move(public_path('attachments'), $name);

            $file = $request->file('filename');
            $customName = $this->RefNo() . '.' . $file->getClientOriginalExtension();

            // Use helper (automatically stores to local or S3)
            $fileUrl = FileUploadHelper::upload($file, 'staffattachments', $customName);

            $getID = DB::table('tblstaffAttachment')->insertGetId([

                'filepath' => $fileUrl,
                'filedesc' => $desc,
                'staffID' => $staffid,

            ]);
            // }
        }

        return back()->with('message', 'File Uploaded!');
    }

    //delete attachement
    public function deleteAttachement($id)
    {
        //dd($id);
        DB::table('tblstaffAttachment')->where('id', $id)->delete();
        return back()->with('message', 'File Removed!');
    }

    public function getAccount()
    {
        $data = $this->getTabLevel(11);
        $fileNo = Session::get('fileNo');

        $data['fileNo'] = Session::get('fileNo');
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
        $data['progress'] = $this->getProgress($data['fileNo']);
        Session::put('progress', $data['progress']);

        if (!empty($fileNo)) {
            $data['prog'] = 15;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $fileNo)->first();
            $data['BankList'] = DB::Select("SELECT * FROM `tblbanklist`");

            return view('hr.documentation.StaffDoc', $data);
        } else {
            return redirect('/documentation');
        }
    }

    public function PayrollActivePeriod($court)
    {
        $cvdata =  DB::select("SELECT * FROM `tblactivemonth` WHERE `courtID`='$court'");

        if ($cvdata) {
            return $cvdata[0];
        } else {

            $cvdata =  DB::select("select '' as 'month','' as 'year'");
            return $cvdata[0];
        }
    }

    public function submitAccount(Request $request)
    {

        $fileNo = Session::get('fileNo');
        $bankID = $request->input('bankName');
        $accountNumber = $request->input('accountNumber');
        $myPer = DB::table('tblper')->where('ID', $fileNo)->first();

        // dd($myPer);

        if (!empty($fileNo)) {
            $this->accountSetUp($fileNo, $bankID, $accountNumber);

            $existingCandidate = DB::table('half_pay_staff')->where('staffid', $fileNo)->first();
            if (!$existingCandidate) {
                $data['PayrollActivePeriod'] = $this->PayrollActivePeriod(9);
                $insert = DB::table('half_pay_staff')->insert(array(
                    'staffid'                 => $fileNo,
                    'interviewCandidateId' => $myPer->interviewCandidateId,
                    'fileNo'      => $myPer->fileNo,
                    'courtID'                => 9,
                    'due_date'               => $myPer->doj,
                    'month_payment'          => $data['PayrollActivePeriod']->month,
                    'year_payment'           => $data['PayrollActivePeriod']->year,
                ));
            }

            $d = Session::get('progress');
            if ($d < 16) {
                $this->setProgress($fileNo, 16);
            }
            return redirect('/documentation-passport-signature');
        }

        return redirect('/documentation')->with('error', 'An error occured');
    }

    public function getPassportSignature()
    {

        $data = $this->getTabLevel(12);
        $fileNo = Session::get('fileNo');

        $data['fileNo'] = Session::get('fileNo');
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
        $data['progress'] = $this->getProgress($data['fileNo']);
        Session::put('progress', $data['progress']);

        if (!empty($fileNo)) {
            $data['prog'] = 16;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
            $data['passportPreviewUrl'] = $data['staffInfo']->passport_url ?? '';
            $data['signaturePreviewUrl'] = $data['staffInfo']->signature_url ?? '';

            return view('hr.documentation.StaffDoc', $data);
        } else {
            return redirect('/documentation');
        }
    }

    public function submitPassportSignature(Request $request)
    {
        $fileNo = Session::get('fileNo');
        if (empty($fileNo)) {
            return redirect('/documentation')->with('error', 'An error occured');
        }

        $passportUrl = null;
        $signatureUrl = null;

        // 1) Handle uploaded passport file (preferred)
        if ($request->hasFile('passport_file')) {
            $file = $request->file('passport_file');
            $customName = $this->RefNo() . '_passport.' . $file->getClientOriginalExtension();
            $passportUrl = FileUploadHelper::upload($file, 'staffattachments', $customName);
        } elseif ($request->filled('passport_data')) {
            // 2) Handle base64 camera capture (dataURL) -> convert to temp UploadedFile then use helper
            $data = $request->input('passport_data');
            if (preg_match('/^data:(image\/[a-zA-Z]+);base64,/', $data, $m)) {
                $mime = $m[1]; // e.g. image/png
                $ext = explode('/', $mime)[1];
                if ($ext === 'jpeg') $ext = 'jpg';
                $base64 = preg_replace('/^data:image\/[a-zA-Z]+;base64,/', '', $data);
                $decoded = base64_decode($base64);
                if ($decoded !== false) {
                    $filename = $this->RefNo() . '_passport.' . $ext;
                    $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
                    file_put_contents($tmpPath, $decoded);
                    try {
                        $symFile = new SymfonyUploadedFile($tmpPath, $filename, $mime, null, true);
                        $passportUrl = FileUploadHelper::upload($symFile, 'staffattachments', $filename);
                    } catch (\Exception $e) {
                        Log::error('Passport upload failed: ' . $e->getMessage());
                    } finally {
                        if (file_exists($tmpPath)) @unlink($tmpPath);
                    }
                }
            }
        }

        // 3) Handle uploaded signature file (preferred)
        if ($request->hasFile('signature_file')) {
            $file = $request->file('signature_file');
            $customName = $this->RefNo() . '_signature.' . $file->getClientOriginalExtension();
            $signatureUrl = FileUploadHelper::upload($file, 'staffattachments', $customName);
        } elseif ($request->filled('signature_data')) {
            // signature_data is expected as dataURL from SignaturePad -> convert and upload
            $data = $request->input('signature_data');
            if (preg_match('/^data:(image\/[a-zA-Z]+);base64,/', $data, $m)) {
                $mime = $m[1];
                $ext = explode('/', $mime)[1];
                if ($ext === 'jpeg') $ext = 'jpg';
                $base64 = preg_replace('/^data:image\/[a-zA-Z]+;base64,/', '', $data);
                $decoded = base64_decode($base64);
                if ($decoded !== false) {
                    $filename = $this->RefNo() . '_signature.' . $ext;
                    $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
                    file_put_contents($tmpPath, $decoded);
                    try {
                        $symFile = new SymfonyUploadedFile($tmpPath, $filename, $mime, null, true);
                        $signatureUrl = FileUploadHelper::upload($symFile, 'staffattachments', $filename);
                    } catch (\Exception $e) {
                        Log::error('Signature upload failed: ' . $e->getMessage());
                    } finally {
                        if (file_exists($tmpPath)) @unlink($tmpPath);
                    }
                }
            }
        }

        // Update tblper with whichever URLs were produced
        $updateData = [];
        if ($passportUrl !== null) $updateData['passport_url'] = $passportUrl;
        if ($signatureUrl !== null) $updateData['signature_url'] = $signatureUrl;

        if (!empty($updateData)) {
            DB::table('tblper')->where('ID', $fileNo)->update($updateData);
        }

        // advance progress and redirect
        $d = Session::get('progress');
        if ($d < 17) {
            $this->setProgress($fileNo, 17);
        }

        return redirect('/documentation-others');
    }

    public function getOthers()
    {

        $data = $this->getTabLevel(13);
        $fileNo = Session::get('fileNo');

        $data['fileNo'] = Session::get('fileNo');
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
        $data['progress'] = $this->getProgress($data['fileNo']);
        Session::put('progress', $data['progress']);
        $data['religions'] = DB::Table('tblreligion')->get();

        if (!empty($fileNo)) {
            $data['prog'] = 17;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
            $data['otherInfo'] = DB::Table("tblotherinfoforstaffdocumentation")->where('staffid', $data['fileNo'])->first();
            $data['religions'] = DB::Table('tblreligion')->get();

            return view('hr.documentation.StaffDoc', $data);
        } else {
            return redirect('/documentation');
        }
    }

    public function submitOthers(Request $request)
    {

        $fileNo = Session::get('fileNo');
        $convict =  $request->input('convict');
        $convictReason =  $request->input('convict-reason');
        $illness =  $request->input('illness');
        $illnessReason =  $request->input('illness-reason');
        $repay =  $request->input('repay');
        $jugdement =  $request->input('jugdement');
        $judgementReason =  $request->input('judgement-reason');
        $detailInForce =  $request->input('detail-in-force');
        $decoration =  $request->input('decoration');
        $religion =  $request->input('religion');
        $agree = $request->input('agree');

        if (!empty($fileNo)) {

            $this->othersSetUp(
                $fileNo,
                $convict,
                $convictReason,
                $illness,
                $illnessReason,
                $repay,
                $jugdement,
                $judgementReason,
                $detailInForce,
                $decoration,
                $religion,
                $agree
            );

            $d = Session::get('progress');
            // dd($d);
            if ($d < 18) {
                $this->setProgress($fileNo, 18);
            }
            return redirect('/documentation-preview');
        }

        return redirect('/documentation')->with('error', 'An error occured');
    }

    public function getPreview()
    {
        $fileNo = Session::get('fileNo');
        $data = $this->getTabLevel(14);
        $data['fileNo'] = Session::get('fileNo');
        //dd($data['staffID']);
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
        $data['progress'] = $this->getProgress($data['fileNo']);
        Session::put('progress', $data['progress']);
        $fileNoExists = DB::Table("tblper")->where('ID', $data['fileNo'])->first();

        if ($fileNoExists->fileNo == null) {

            $data['staffFileNo'] = $this->generateFileNo('tblper', 'fileNo', 9);
        } else {

            $data['staffFileNo'] = DB::Table("tblper")->where('ID', $data['fileNo'])->value('fileNo');
        }

        if (!empty($fileNo)) {

            $data['data'] = DB::Table("tblper")->where('ID', $data['fileNo'])->first();
            $data['prog'] = 18;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
            $data['maritalStatus'] = DB::Table("tbldateofbirth_wife")->where('staffid', $data['fileNo'])->first();
            $data['relationship'] = DB::Table("tblper")
                ->leftjoin('tblmaritalStatus', 'tblper.maritalstatus', '=', 'tblmaritalStatus.marital_status')
                ->where('tblper.ID', $data['fileNo'])->value('marital_status');
            //dd($data['staffID']);
            $data['nextOfKin'] = DB::Table("tblnextofkin")->where('staffid', $data['fileNo'])->get();
            $data['UserState'] = DB::Table("tblstates")->where('StateID', $data['data']->stateID)->first();

            $data['UserLga'] = DB::Table("lga")->where('lgaId', $data['data']->lgaID)->first();
            $data['UserBank'] = DB::Table("tblbanklist")->where('bankID', $data['data']->bankID)->first();
            $data['empType'] = DB::Table("tblemployment_type")->where('id', $data['data']->employee_type)->first();
            $data['dept'] = DB::Table("tbldepartment")->where('id', $data['data']->department)->first();
            $data['design'] = DB::Table("tbldesignation")->where('id', $data['data']->Designation)->first();

            $data['prevEmployment'] = DB::Table("previous_servicedetails")->where('staffid', $data['fileNo'])->get();
            $data['children'] = DB::Table("tblchildren_particulars")->where('staffid', $data['fileNo'])->get();
            $data['otherInfo'] = DB::Table("tblotherinfoforstaffdocumentation")->where('staffid', $data['fileNo'])->exists();

            if ($data['otherInfo']) {
                $data['otherInfo'] = DB::Table("tblotherinfoforstaffdocumentation")->where('staffid', $data['fileNo'])->first();
            } else {
                $data['otherInfo'] = '';
            }
            $data['staffAttachment'] = DB::table('tblstaffAttachment')
                ->select('tblstaffAttachment.staffID', 'tblstaffAttachment.filedesc', 'tblstaffAttachment.filepath', 'tblstaffAttachment.id')
                ->where('tblstaffAttachment.staffID', '=', $data['fileNo'])
                ->get();

            $data['education'] = DB::table('tbleducations')
                ->leftjoin('tbleducation_category', 'tbleducations.categoryID', '=', 'tbleducation_category.edu_categoryID')
                ->where('staffid', '=', $data['fileNo'])
                //->groupby('tbleducations.categoryID')
                ->orderby('categoryID', 'asc')
                ->get();

            return view('hr.documentation.StaffDoc', $data);
        } else {
            return redirect('/documentation');
        }
    }

    public function submitPreview(Request $request)
    {
        //dd($request->staffFileNo);
        $fileNo = Session::get('fileNo');
        $d = Session::get('progress');
        $exists = DB::table('users')->where('username', $fileNo)->exists();
        if (DB::table('tblper')->where('fileNo', $request->staffFileNo)->count() > 1) {
            return redirect()->back()->with('err', 'File number already exists for another staff. Please choose a different file number.');
        }

        $getInterviewCandidateId = DB::table('tblper')->where('ID', $fileNo)->first();
        DB::table('tblcandidate')->where('candidateID', $getInterviewCandidateId->interviewCandidateId)->update([
            'documentation_status' => 1
        ]);

        if ($exists) {
        } else {
            $getID = DB::table('users')->insertGetId(['name' => $request->fullname, 'username' => $request->staffFileNo, 'password' => bcrypt(12345), 'courtID' => 9]);
            DB::table('tblper')->where('ID', $fileNo)->update(['UserID' => $getID, 'fileNo' => $request->staffFileNo]);
        }

        if ($d < 18) {
            $this->setProgress($fileNo, 18);
        }

        return redirect('/report/staff-list');
        // return redirect('/candidate');
    }


    public function getComplete()
    {
        $fileNo = Session::get('fileNo');
        $data['fileNo'] = Session::get('fileNo');

        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
        $data['progress'] = $this->getProgress($data['fileNo']);

        if (!empty($fileNo)) {
            $data['prog'] = 19;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();

            return view('documentation.StaffDoc', $data);
        } else {
            return redirect('/documentation');
        }
    }

    public function submitComplete(Request $request)
    {
        //dd($request->staffFileNo);
        $fileNo = Session::get('fileNo');
        $d = Session::get('progress');
        $exists = DB::table('users')->where('username', $fileNo)->exists();

        if ($exists) {
        } else {
            $getID = DB::table('users')->insertGetId(['name' => $request->fullaname, 'username' => $request->staffFileNo, 'password' => bcrypt(12345), 'courtID' => 9]);
            DB::table('tblper')->where('ID', $fileNo)->update(['UserID' => $getID, 'fileNo' => $request->staffFileNo]);
        }

        if ($d < 19) {
            $this->setProgress($fileNo, 19);
        }

        return redirect('/candidate');
    }

    public function getTabLevel($x)
    {
        $data['tabLevel1'] = '';
        $data['tabLevel2'] = '';
        $data['tabLevel3'] = '';
        $data['tabLevel4'] = '';
        $data['tabLevel5'] = '';
        $data['tabLevel6'] = '';
        $data['tabLevel7'] = '';
        $data['tabLevel8'] = '';
        $data['tabLevel9'] = '';
        $data['tabLevel10'] = '';
        $data['tabLevel11'] = '';
        $data['tabLevel12'] = '';
        $data['tabLevel13'] = '';
        $data['tabLevel14'] = '';
        $data['tabLevel15'] = '';
        switch ($x) {
            case 1:
                $data['tabLevel1'] = 'active';
                break;
            case 2:
                $data['tabLevel2'] = 'active';
                break;
            case 3:
                $data['tabLevel3'] = 'active';
                break;
            case 4:
                $data['tabLevel4'] = 'active';
                break;
            case 5:
                $data['tabLevel5'] = 'active';
                break;
            case 6:
                $data['tabLevel6'] = 'active';
                break;
            case 7:
                $data['tabLevel7'] = 'active';
                break;
            case 8:
                $data['tabLevel8'] = 'active';
                break;
            case 9:
                $data['tabLevel9'] = 'active';
                break;
            case 10:
                $data['tabLevel10'] = 'active';
                break;
            case 11:
                $data['tabLevel11'] = 'active';
                break;
            case 12:
                $data['tabLevel12'] = 'active';
                break;
            case 13:
                $data['tabLevel13'] = 'active';
                break;
            case 14:
                $data['tabLevel14'] = 'active';
                break;
            case 15:
                $data['tabLevel15'] = 'active';
                break;
            default:
                dd('Please go back....');
        }

        return $data;
    }

    public function generateFileNo($tableName, $generateIDfieldName, $courtid)
    {        //dd(1);
        //GET NEW FILE NUMBER
        $lastFileNoPerCourt = DB::table('tblper')->where('courtID', $courtid)->orderBy('fileNo', 'Desc')->value('fileNo');
        $courAbbr = DB::table('tbl_court')->where('id', $courtid)->select('courtAbbr', 'file_abbr')->first();

        $courtCode       = ($courAbbr->courtAbbr);
        $courtFileAbbr   = ($courAbbr->courtAbbr);

        //dd($lastFileNoPerCourt);
        if (empty($lastFileNoPerCourt)) {
            $newFileNo = '00001';
        } else {
            $splitArr = explode("/P", $lastFileNoPerCourt);
            $newFileNo = '00001'; //defualt
            $initial = '0000';
            //dd($splitArr[1]);
            if (count($splitArr) < 1) {
                $newFileNo = ($courtFileAbbr) ? ($courtFileAbbr . '/' . '00001') : '00001';
                //dd($newFileNo);
            } else if (count($splitArr) == 1) {
                $newFileNo = intval($splitArr[0]) + 1;
                //dd($newFileNo);
            } else if (count($splitArr) == 2) {
                $newFileNo = intval($splitArr[1]) + 1;
            } else if (count($splitArr) == 3) {
                $newFileNo = ($courtFileAbbr) ? ($courtFileAbbr . '/' . intval($splitArr[2] + 1)) : (intval($splitArr[2] + 1));
            } else { //i.e > 3 ..save code

                $newFileNo = ($courtFileAbbr) ? ($courtFileAbbr . '/' . intval(end($splitArr) + 1)) : (intval(end($splitArr) + 1));
                //dd($newFileNo);
            }
            //dd($newFileNo);
        }
        $newFileNoAllotted = $courtCode . '/P' . $initial . $newFileNo;
        //dd($newFileNoAllotted);
        if (!DB::table('tblper')->where('fileNo', $newFileNoAllotted)->exists()) { //FallBack
            return $newFileNoAllotted;
        } else {
            // dd($courtCode .'/P'. intval(end($splitArr) + 1));
            return ($courtCode . '/P' . intval(end($splitArr) + 1));
        }
    }
}
