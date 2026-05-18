<?php

namespace App\Http\Controllers\hr;



use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Response;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CandidateController extends functionController
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->middleware('auth');
        $this->username = Session::get('userName');
            return $next($request);
        });
    }

    public function candidate()
    {

        $data['candidateDetails'] = DB::table('tblcandidate')
            ->where('candidate_status', 1)
            ->leftjoin('tblstates', 'tblcandidate.state', '=', 'tblstates.StateID')
            ->leftjoin('lga', 'tblcandidate.lga', '=', 'lga.lgaId')
            ->get();

        return view('hr.candidate.successful_candidate', $data);
    }

    public function documentation($id)
    {

        return view('documentation.StaffDoc');
    }

    public function interview()
    {

        $data['interviewDetails'] = DB::table('tblinterview')
            ->orderby('interviewID', 'desc')
            ->get();
        return view('hr.candidate.interview', $data);
    }

    public function saveInterview(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|string',
            'date'  => 'required|date',
            'description' => 'required',
            'filenames' => 'required'
        ]);

        $date = date('Y-m-d', strtotime($request->date));
        // $filename= "";


        // if ($request->hasFile('memo')) {
        //   $image = $request->file('memo');
        //   $extension = $image->getClientOriginalExtension();

        //   $filename = $image->getClientOriginalName();

        //   $location = public_path('interviewMemos/');

        // }

        $data['insert'] = DB::table('tblinterview')->insertGetId(['title' => $request->title, 'date' => $date]);


        if ($request->hasfile('filenames')) {

            foreach ($request->file('filenames') as $key => $file) {
                $name = time() . '' . $key . '.' . $file->extension();

                $file->move(public_path('/interviewAttachmentfiles/'), $name);
                DB::table('interviewattachments')->insert([
                    'interviewID' => $data['insert'],
                    'description' => $request->description[$key],
                    'attachment' => $name,
                ]);

                $data[] = $name;
            }
        }

        // if ($request->hasFile('memo')) {
        //   $move = $request->file('memo')->move($location,$filename);
        // }

        return redirect()->back();
    }

    public function candidateShorlisted($id)
    {

        $data['state'] = DB::table('tblstates')->get();
        $data['interviewDetails'] = DB::table('tblinterview')->where('interviewID', $id)->first();
        // dd($id);
        $data['interviewList'] = DB::table('tblcandidate')
            ->where('interview_titleID', $id)
            ->leftjoin('tblstates', 'tblcandidate.state', '=', 'tblstates.StateID')
            ->leftjoin('lga', 'tblcandidate.lga', '=', 'lga.lgaId')
            ->get();

        return view('hr.candidate.candidate', $data);
    }

    public function saveCandidateShorlisted(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'surname' => 'required|string',
            'firstname'  => 'required|string',
            'othernames' => '',
            'email' => 'required',
            'phoneNo' => 'required',
            'sex'   => 'required|string',
            'maritalStatus' => 'required',
            'state' => 'required|numeric',
            'lga'   => 'required|numeric',
            'address'  => 'required|string',
        ]);
        // dd(345454);
        DB::table('tblcandidate')->insert([

            'interview_titleID'   =>  $request->interviewID,
            'candidate_title' => $request->title,
            'surname'       =>  $request->surname,
            'first_name'    =>  $request->firstname,
            'othernames'    =>  $request->othernames ?? '',
            'sex'           =>  $request->sex,
            'maritalStatus' => $request->maritalStatus,
            'phoneNo' => $request->phoneNo,
            'email' => $request->email,
            'state'         =>  $request->state,
            'lga'           =>  $request->lga,
            'address'       =>  $request->address,
        ]);

        return redirect()->back()->with('message', 'Record added!');
    }

    public function adminAddNewStaff()
    {
        $data['state'] = DB::table('tblstates')->get();

        $data['DepartmentList'] = DB::table('tbldepartment')
            ->orderBy('id', 'desc')
            ->get();  // 👈 you must add get()

        $data['UnitList'] = DB::table('tblunits')
            ->orderBy('unitID', 'desc')
            ->get();  // 👈 you must add get()


        return view('hr.candidate.newStaff', $data);
    }

    public function adminSaveNewStaff(Request $request)
    {
        // dd($request->all());
        $request->validate([
            "title" => "required",
            "surname" => "required",
            "firstname" => "required",
            "othernames" => "nullable",
            "email" => "nullable",
            "phoneNo" => "nullable",
            "sex" => "required",
            "maritalStatus" => "required"
        ]);
        try {
            DB::table('tblper')->insert([
                'title' => strtoupper($request->title),
                'surname' => strtoupper($request->surname),
                'first_name' => strtoupper($request->firstname),
                'othernames' => strtoupper($request->othernames),
                'rank' => 0,
                'bankGroup' => 1,
                'bank_branch' => 'ABJ',
                'courtID' => 9,
                'divisionID' => 1,
                'phone' => $request->phoneNo,
                'email' => $request->email,
                'grade' => $request->grade,
                'step' => $request->step,
                'staff_status' => 0,
                'status_value' => 'active service',
                'gender' => $request->sex,
                'maritalstatus' => $request->maritalStatus,
                'isClaimed' => 1,
                'isAdmin' => 1,
            ]);
            return redirect()->back()->with('message', 'Record added!');
        } catch (\Throwable $th) {
            //throw $th;
            dd($th->getMessage());
            return redirect()->back()->with('error_message', 'Could not add staff record!');
        }
    }

    public function listShortlistedCandidate()
    {

        $data['data'] =  DB::table('tblcandidate')->where('candidate_status', 1)
            ->leftjoin('tblstates', 'tblcandidate.state', '=', 'tblstates.StateID')
            ->leftjoin('lga', 'tblcandidate.lga', '=', 'lga.lgaId')
            ->get();

        return view('hr.candidate.shortlisted', $data);
    }

    // delete candidate
    public function deleteCandidate(Request $request)
    {

        $id = $request->candidateID;

        // Log::info($id);
        DB::table('tblcandidate')
            ->where('candidateID', $id)
            ->delete();

        return back()->with("success", "Delete successfull");
    }

    //edit candidate
    public function editCandidate($id)
    {

        $data['state'] = DB::table('tblstates')->get();

        $data['candidate'] = DB::table('tblcandidate')
            ->where('tblcandidate.candidateID', $id)
            ->leftjoin('tblstates', 'tblcandidate.state', '=', 'tblstates.StateID')
            ->leftjoin('lga', 'tblcandidate.lga', '=', 'lga.lgaId')
            ->select('tblstates.State as candidateState', 'tblcandidate.*', 'lga.lga as candidateLga')
            ->first();

        $data['lga'] = DB::table('lga')->where('stateId', $data['candidate']->state)->get();
        return view('hr.candidate.editcandidate', $data);
    }

    // update candidate
    public function updateCandidateShorlisted(Request $request)
    {

        DB::table('tblcandidate')->where('candidateID', $request->candidateID)->update([

            'candidate_title' => $request->title,
            'surname'       =>  $request->surname,
            'first_name'    =>  $request->firstname,
            'othernames'    =>  $request->othernames ?? '',
            'sex'           =>  $request->sex,
            'maritalStatus' => $request->maritalStatus,
            'phoneNo' => $request->phoneNo,
            'email' => $request->email,
            'state'         =>  $request->state,
            'lga'           =>  $request->lga,
            'address'       =>  $request->address,
        ]);

        return redirect()->back()->with('message', 'Record updated!');
    }


    public function loadLGA(Request $request)
    {
        $stateId = $request->get('state_id');
        // Log::info($stateId);
        $data = DB::table('lga')->where('stateId', '=', $stateId)->get();

        return response()->json($data);
    }

    public function closeCandidate($id)
    {

        $data = DB::table('tblinterview')->where('interviewID', '=', $id)->update(['close_candidate' => 0]);

        return redirect()->back()->with('message', 'Closed!');
    }

    public function openCandidate($id)
    {

        $data = DB::table('tblinterview')->where('interviewID', '=', $id)->update(['close_candidate' => 1]);

        return redirect()->back()->with('message', 'Open!');
    }

    public function closeInterview($id)
    {

        $data = DB::table('tblinterview')->where('interviewID', '=', $id)->update([
            'interview_status' => 0,
            'close_candidate' => 0
        ]);

        return redirect()->back()->with('message', 'Closed!');
    }

    public function openInterview($id)
    {

        $data = DB::table('tblinterview')->where('interviewID', '=', $id)->update([
            'interview_status' => 1,
            'close_candidate' => 1
        ]);

        return redirect()->back()->with('message', 'Open!');
    }

    //show interview
    public function showInterviewAndEdit($id)
    {
        $data['interview'] = DB::table('tblinterview')->where('interviewID', '=', $id)->first();
        $editDate = $data['interview']->date;
        $data['newDate'] = date('d-m-Y', strtotime($editDate));

        //check if candidate already exist for an interview
        $data['candidateExist'] = DB::table('tblcandidate')->where('interview_titleID', '=', $id)->first();

        $data['interviewAttachments'] = DB::table('interviewattachments')->where('interviewID', '=', $id)->get();

        return view('hr.candidate.showInterviewAndEdit', $data);
    }

    public function updateInterview(Request $request, $id)
    {
        $existingDate = DB::table('tblinterview')->where('interviewID', $id)->first();
        $oldDate = date('d-m-Y', strtotime($existingDate->date));

        $request->validate([
            'title' => 'required',
            // 'date' => 'required|date',
        ]);

        $date = date('Y-m-d', strtotime($request->date));

        if ($request->hasfile('filenames')) {
            foreach ($request->file('filenames') as $key => $file) {

                $name = time() . '' . $key . '.' . $file->extension();

                $file->move(public_path('/interviewAttachmentfiles/'), $name);
                DB::table('interviewattachments')->insert([
                    'interviewID' => $id,
                    'description' => $request->description[$key],
                    'attachment' => $name,
                ]);

                $data[] = $name;
            }

            try {
                DB::table('tblinterview')->where('interviewID', $id)->update([
                    'title' => $request->title,
                    'date' => $date ?? $oldDate
                ]);
            } catch (\Throwable $th) {
                //throw $th;
            }
        } else {
            try {
                DB::table('tblinterview')->where('interviewID', $id)->update([
                    'title' => $request->title,
                    'date' => $date ?? $oldDate
                ]);
            } catch (\Throwable $th) {
                //throw $th;
            }
        }

        return redirect(url('interview'))->with('message', 'Successfully updated');
    }

    public function deleteCandidateInterview($id)
    {
        $deleteCandidate = DB::table('tblinterview')->where('interviewID', $id)->delete();

        //check if interview Docs Exist
        $docs = DB::table('interviewattachments')->where('interviewID', $id)->get();
        if ($docs) {
            foreach ($docs as $d) {
                DB::table('interviewattachments')->where('interviewID', $id)->delete();

                if (File::exists(public_path('/interviewAttachmentfiles/' . $d->attachment))) {
                    File::delete(public_path('/interviewAttachmentfiles/' . $d->attachment));
                }
            }
        }

        if ($deleteCandidate) {
            return redirect(url('interview'))->with('message', 'You have deleted an interview');
        }
    }

    public function deleteInterviewDocument($id)
    {
        $deleteDoc = DB::table('interviewattachments')->where('id', $id)->delete();
        if ($deleteDoc) {
            return back()->with('message', 'You have deleted an interview document');
        }
    }

    //===================  Show Interview Candidates Modal ======================\\
    public function showCandidates($id)
    {
        $data['interviewDetails'] = DB::table('tblinterview')->where('interviewID', $id)->first();

        $data['interviewList'] = DB::table('tblcandidate')
            ->where('interview_titleID', $id)
            ->leftjoin('tblstates', 'tblcandidate.state', '=', 'tblstates.StateID')
            ->leftjoin('lga', 'tblcandidate.lga', '=', 'lga.lgaId')
            ->select('tblstates.State as candidateState', 'tblcandidate.*', 'lga.lga as candidateLga')
            ->get();
        return view('hr.candidate.enteredForInterview', $data);
    }

    //================== SHOW CANDIDATE DELETE ALERT MODAL ===================\\
    public function showDeleteModal($id)
    {
        $candidate = DB::table('tblcandidate')->where('candidateID', $id)->first();
        return redirect()->back()->with('interview-candidate-delete', $candidate);
    }


    /////=============================Secretary Report=================================================
    public function secretaryFinalApproval()
    {
        $userID = (Auth::check() ? Auth::user()->id : null);
        $data['getAllcandidates'] = [];
        $data['getRecords']       = [];
        $interviewID = Session::get('interviewID');
        $data['getInterviewName'] = DB::table('tblinterview')->where('interview_status', 1)->get();
        // if($interviewID)
        // {
        //     $data['getAllcandidates'] = DB::table('tblcandidate')->where('interview_titleID', $interviewID)->where('candidate_status', 1)->get();
        // }
        if (DB::table('tblaction_stages')->where('userID', $userID)->first()) {
            $data['getRecords'] = DB::table('tblinterview_score_sheet')
                ->leftJoin('tblinterview', 'tblinterview.interviewID', '=', 'tblinterview_score_sheet.interviewID')
                ->leftJoin('tblcandidate', 'tblcandidate.candidateID', '=', 'tblinterview_score_sheet.candidateID')
                ->where('tblinterview_score_sheet.stages_id', 6)
                ->where('tblcandidate.candidate_status', 1)
                ->where('tblinterview.interviewID', $interviewID)
                ->where('tblinterview_score_sheet.status', 1)
                //->where('tblinterview_score_sheet.is_approved', 0)
                ->get();

            $data['interviewAttachments'] = DB::table('interviewattachments')->where('interviewID', $interviewID)->get();
        }
        //check if any candidate has been approved
        $data['checkForAnyCandidateApproval'] = DB::table('tblinterview_score_sheet')
            ->where('is_approved', 1)->where('interviewID', $interviewID)
            ->where('tblinterview_score_sheet.stages_id', 6)
            ->get();

        //check for approved candidate to hide revert button if nobody is approved
        $data['checkForAnyApproved'] = DB::table('tblinterview_score_sheet')
            ->where('is_approved', 1)->where('interviewID', $interviewID)
            ->where('tblinterview_score_sheet.stages_id', 6)
            ->first();


        //get all comment on this interview
        $data['getComments'] = DB::table('tblinterview_comment')->where('interviewID', $interviewID)->orderBy('commentID', 'Desc')->get();
        $data['getInterviewID'] = $interviewID;
        $data['getAttachments'] = DB::table('interviewattachments')->where('interviewID', $interviewID)->get();

        // return $data;

        return view('hr.InterviewScoreSheet.secretaryFinalApproval', $data);
    }

    //Push approved candidate to open registry by secretary
    public function pushBack(Request $request)
    {

        $isSaved = null;
        try {
            $userID         = (Auth::check() ? Auth::user()->id : null);
            $interviewID    = $request['getInterviewID'];
            if ($interviewID) {
                $getCandidate = DB::table('tblinterview_score_sheet')->where('is_approved', 1)->where('interviewID', $interviewID)->get();
                if ($getCandidate) {
                    try {
                        if ($request['getComment']) {
                            DB::table('tblinterview_comment')->insertGetId([
                                'userID'        => $userID,
                                'interviewID'   => $interviewID,
                                'comment'       => $request['getComment']
                            ]);
                        }
                    } catch (\Throwable $e) {
                    }
                    foreach ($getCandidate as $key => $value) {
                        $isSaved = DB::table('tblinterview_score_sheet')->where('score_sheetID', $value->score_sheetID)->update([
                            'stages_id'         => 6,
                            'is_final_approval' => 1,
                            'status'            => 1
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
        }
        if ($isSaved) {
            return redirect()->back()->with('success', 'The list of the selected candidate scores have been sent.');
        }
        return redirect()->back()->with('danger', 'Sorry, we cannot process your record. Please try again.');
    }

    //Approve candidates
    public function approveCandidate(Request $request)
    {
        $isSaved = null;
        $validated = $request->validate([
            'selectedCandidate' => 'required|array',
        ]);

        try {
            $userID         = (Auth::check() ? Auth::user()->id : null);
            $interviewID    = Session::get('interviewID');
            if ($request['selectedCandidate']) {
                foreach ($request['selectedCandidate'] as $key => $value) {

                    if (DB::table('tblinterview_score_sheet')->where('score_sheetID', $value)->value('is_approved') == 0) {
                        $isSaved = DB::table('tblinterview_score_sheet')->where('score_sheetID', $value)->update([
                            'is_approved'   => 1,
                            'approval_userID' => $userID
                        ]);
                        if ($isSaved) {
                            //update candidate approval status
                            DB::table('tblcandidate')->where('candidateID', DB::table('tblinterview_score_sheet')->where('score_sheetID', $value)->value('candidateID'))->update([
                                'approval_status'   => 1
                            ]);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
        }
        if ($isSaved) {
            return redirect()->back()->with('success', 'The list of the selected candidate has been approved successfully.');
        }
        return redirect()->back()->with('danger', 'Sorry, we cannot process your record. Please try again.');
    }

    //revert approved candidates
    public function revertApprovedCandidate(Request $request)
    {
        $isSaved = null;
        $validated = $request->validate([
            'revertSelected' => 'required',
        ]);

        $getArr = array();
        $getArr = $request->revertSelected;
        $na = explode(',', $getArr[0]);
        // dd($na);

        try {
            $userID         = (Auth::check() ? Auth::user()->id : null);
            $interviewID    = Session::get('interviewID');
            if ($request['revertSelected']) {
                foreach ($na as $value) {
                    // dd($value);
                    if (DB::table('tblinterview_score_sheet')->where('score_sheetID', $value)->where('is_approved', 1)->exists()) {
                        $isSaved = DB::table('tblinterview_score_sheet')->where('score_sheetID', $value)->update([
                            'is_approved'   => 0,
                            'approval_userID' => $userID,
                            'is_final_approval' => 0,
                        ]);
                        if ($isSaved) {
                            //update candidate approval status
                            DB::table('tblcandidate')->where('candidateID', DB::table('tblinterview_score_sheet')->where('score_sheetID', $value)->value('candidateID'))->update([
                                'approval_status'   => 0
                            ]);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
        }
        if ($isSaved) {
            return redirect()->back()->with('success', 'You have successfully reverted.');
        }
        return redirect()->back()->with('danger', 'Sorry, we cannot process your record. Please try again.');
    }


    //===========================shortlisted Report==================================================
    public function shortlistedReport()
    {
        $userID = (Auth::check() ? Auth::user()->id : null);
        $data['getRecords']       = [];
        $interviewID = Session::get('interviewID');
        $data['getInterviewName'] = DB::table('tblinterview')->where('interview_status', 1)->get();
        if (DB::table('tblaction_stages')->where('userID', $userID)->first()) {
            $data['getRecords'] = DB::table('tblinterview_score_sheet')
                ->leftJoin('tblinterview', 'tblinterview.interviewID', '=', 'tblinterview_score_sheet.interviewID')
                ->leftJoin('tblcandidate', 'tblcandidate.candidateID', '=', 'tblinterview_score_sheet.candidateID')
                ->where('tblinterview_score_sheet.stages_id', 6)
                ->where('tblcandidate.candidate_status', 1)
                ->where('tblinterview.interviewID', $interviewID)
                ->where('tblinterview_score_sheet.status', 1)
                ->where('tblinterview_score_sheet.is_approved', 1)
                ->where('tblinterview_score_sheet.is_final_approval', 1)
                ->get();
        }
        $data['getInterviewID'] = $interviewID;

        return view('hr.InterviewScoreSheet.shortlistedReport', $data);
    }


    //===========================candidate appointment letter==================================================
    public function candidateAppointmentLetter()
    {
        $userID = (Auth::check() ? Auth::user()->id : null);
        $data['getRecords']       = [];
        $interviewID = Session::get('interviewID');
        $data['getInterviewName'] = DB::table('tblinterview')->where('interview_status', 1)->get();
        if (DB::table('tblaction_stages')->where('userID', $userID)->first()) {
            $data['getRecords'] = DB::table('tblinterview_score_sheet')
                ->leftJoin('tblinterview', 'tblinterview.interviewID', '=', 'tblinterview_score_sheet.interviewID')
                ->leftJoin('tblcandidate', 'tblcandidate.candidateID', '=', 'tblinterview_score_sheet.candidateID')
                ->leftJoin('users', 'users.id', '=', 'tblinterview_score_sheet.approval_userID')
                //->where('tblinterview_score_sheet.stages_id', 6)
                //->where('tblcandidate.candidate_status', 1)
                ->where('tblinterview.interviewID', $interviewID)
                ->where('tblinterview_score_sheet.status', 1)
                ->where('tblinterview_score_sheet.is_approved', 1)
                ->get();
        }
        $data['getInterviewID'] = $interviewID;

        return view('InterviewScoreSheet.candidateAppointmentLetter', $data);
    }

    //================================Push candidate to registry=============================
    public function pushCandidateToRegistry($candidate = null)
    {
        $isSaved = null;
        try {
            $userID         = (Auth::check() ? Auth::user()->id : null);
            if ($candidate) {
                $isSaved = DB::table('tblcandidate')->where('candidateID', $candidate)->update([
                    'registry_status'   => 1
                ]);
            }
        } catch (\Throwable $e) {
        }
        if ($isSaved) {
            return redirect()->back()->with('success', 'Candidate record was successfully.');
        }
        return redirect()->back()->with('danger', 'Sorry, we cannot process your record. Please try again.');
    }

    //==================================Score Sheet delete modal warning=========================
    public function showWarningModal($id)
    {
        return redirect()->back()->with('deleteModal', $id);
    }


    public function specialCandidateShorlisted(Request $request)
    {

        $data['state'] = DB::table('tblstates')->get();

        // fetch interview titles for dropdown
        $data['interviews'] = DB::table('tblinterview')
            ->select('interviewID', 'title', 'interview_status', 'close_candidate')
            ->where('interview_status', 1)
            ->where('close_candidate', 1)
            ->orderBy('title')->get();


        // $data['interviewList'] = DB::table('tblcandidate_cr')
        //     ->where('approval_status', 0)
        //     ->leftjoin('tblstates', 'tblcandidate_cr.state', '=', 'tblstates.StateID')
        //     ->leftjoin('lga', 'tblcandidate_cr.lga', '=', 'lga.lgaId')
        //     ->get();


        $data['interviewList'] = DB::table('tblcandidate_cr')
            ->where('tblcandidate_cr.approval_status', 0)
            ->leftJoin('tblstates', 'tblcandidate_cr.state', '=', 'tblstates.StateID')
            ->leftJoin('lga', 'tblcandidate_cr.lga', '=', 'lga.lgaId')
            ->leftJoin('tblinterview', 'tblcandidate_cr.interview_titleID', '=', 'tblinterview.interviewID') // 👈 join interview table
            ->select(
                'tblcandidate_cr.*',
                'tblstates.State as state_name',
                'lga.lga as lga_name',
                'lga.lgaId as lgaId',
                'tblinterview.title as interview_title',
                'tblinterview.interview_status',
                'tblinterview.close_candidate'
            )
            ->orderBy('tblcandidate_cr.candidateID', 'desc')
            ->paginate(10);


        return view('hr.candidate.special-candidate', $data);
    }


    public function specialCandidateShorlistedImport13122025(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv',
            'selected_interview' => 'nullable|integer|exists:tblinterview,interviewID'
        ]);

        $file = $request->file('excel_file');
        $rows = Excel::toArray([], $file)[0]; // first sheet

        if (empty($rows) || count($rows) <= 1) {
            return back()->with('error', 'Excel file is empty or contains no data rows.');
        }

        // Expected headers (case-insensitive)
        $expectedHeaders = [
            'interview_titleid',
            'candidate_title',
            'surname',
            'first_name',
            'othernames',
            'sex',
            'maritalstatus',
            'phoneno',
            'email',
            'address',
            'state',
            'lga'
        ];

        // Normalize headers from first row
        $headers = array_map(function ($h) {
            return strtolower(trim((string)$h));
        }, $rows[0]);

        // Determine if Excel includes interview_titleID
        $hasInterviewInExcel = in_array('interview_titleid', $headers);

        // If not in Excel, selected_interview must be provided
        if (!$hasInterviewInExcel && !$request->filled('selected_interview')) {
            return back()->with('error', 'Excel does not contain interview_titleID. Please select an Interview from the dropdown.');
        }

        // Build header => index map
        $headerIndex = [];
        foreach ($headers as $i => $h) $headerIndex[$h] = $i;

        // Validate that required non-interview headers exist
        $required = ['candidate_title', 'surname', 'first_name', 'sex', 'maritalstatus', 'phoneno', 'address', 'state', 'lga'];
        foreach ($required as $req) {
            if (!array_key_exists($req, $headerIndex)) {
                return back()->with('error', "Missing required column in Excel: {$req}");
            }
        }

        $errors = [];
        $inserted = 0;

        // Process rows (skip header)
        for ($r = 1; $r < count($rows); $r++) {
            $row = $rows[$r];

            // Skip completely empty rows
            $isEmptyRow = true;
            foreach ($headerIndex as $k => $idx) {
                if (isset($row[$idx]) && trim((string)$row[$idx]) !== '') {
                    $isEmptyRow = false;
                    break;
                }
            }
            if ($isEmptyRow) continue;

            // Determine interview id for this row
            $interviewId = $hasInterviewInExcel ? (int) ($row[$headerIndex['interview_titleid']] ?? 0) : (int) $request->selected_interview;

            // Row-level validation
            $rowData = [
                'interview_titleID' => $interviewId,
                'candidate_title'   => $row[$headerIndex['candidate_title']] ?? null,
                'surname'           => $row[$headerIndex['surname']] ?? null,
                'first_name'        => $row[$headerIndex['first_name']] ?? null,
                'othernames'        => $row[$headerIndex['othernames']] ?? null,
                'sex'               => $row[$headerIndex['sex']] ?? null,
                'maritalStatus'     => $row[$headerIndex['maritalstatus']] ?? null,
                'phoneNo'           => $row[$headerIndex['phoneno']] ?? null,
                'email'             => $row[$headerIndex['email']] ?? null,
                'address'           => $row[$headerIndex['address']] ?? null,
                'state'             => $row[$headerIndex['state']] ?? null,
                'lga'               => $row[$headerIndex['lga']] ?? null,
            ];

            $v = Validator::make($rowData, [
                'interview_titleID' => 'required|integer|exists:tblinterview,interviewID',
                'candidate_title'   => 'required|string|max:255',
                'surname'           => 'required|string|max:100',
                'first_name'        => 'required|string|max:100',
                'sex'               => 'required|string|max:20',
                'maritalStatus'     => 'required|string|max:100',
                'phoneNo'           => 'required|string|max:50',
                'address'           => 'required',
                'state'             => 'required|integer',
                'lga'               => 'required|integer',
                'email'             => 'nullable|email'
            ]);

            if ($v->fails()) {
                $errors[] = [
                    'row' => $r + 1, // human-friendly line number
                    'errors' => $v->errors()->all()
                ];
                continue;
            }

            // insert
            DB::table('tblcandidate_cr')->insert(array_merge($rowData, [
                'candidate_status' => 1,
                'approval_status' => 0,
                'registry_status' => 0,
                'uploaded_by' => auth()->id(),
                'uploaded_at' => now(),
                'created_at'        => now(),
                // 'updated_at'        => now(),
            ]));

            $inserted++;
        }

        $msg = "{$inserted} candidates uploaded.";
        if (!empty($errors)) {
            // store errors in session to show on the page
            Log::info($errors);

            return back()->with('warning', $msg . ' But some rows had errors.')->with('row_errors', $errors);
        }

        return back()->with('success', $msg);
    }


    public function specialCandidateShorlistedImport(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv',
            'selected_interview' => 'nullable|integer|exists:tblinterview,interviewID'
        ]);

        $file = $request->file('excel_file');
        $rows = Excel::toArray([], $file)[0]; // First sheet

        if (empty($rows) || count($rows) <= 1) {
            return back()->with('error', 'Excel file is empty or contains no data rows.');
        }

        // Normalize headers
        $headers = array_map(fn($h) => strtolower(trim((string) $h)), $rows[0]);
        $hasInterviewInExcel = in_array('interview_titleid', $headers);

        if (!$hasInterviewInExcel && !$request->filled('selected_interview')) {
            return back()->with('error', 'Excel does not contain interview_titleID. Please select an Interview.');
        }

        $headerIndex = [];
        foreach ($headers as $i => $h) $headerIndex[$h] = $i;

        $required = [
            'candidate_title',
            'surname',
            'first_name',
            'sex',
            'maritalstatus',
            'phoneno',
            'email',
            'address',
            'state',
            'lga'
        ];

        foreach ($required as $col) {
            if (!array_key_exists($col, $headerIndex)) {
                return back()->with('error', "Missing required column in Excel: {$col}");
            }
        }

        $errors = [];
        $inserted = 0;

        for ($r = 1; $r < count($rows); $r++) {
            $row = $rows[$r];

            // Skip empty row
            $isEmpty = true;
            foreach ($headerIndex as $idx) {
                if (isset($row[$idx]) && trim((string)$row[$idx]) !== '') {
                    $isEmpty = false;
                    break;
                }
            }
            if ($isEmpty) continue;

            // Determine interview ID
            $interviewId = $hasInterviewInExcel
                ? (int) ($row[$headerIndex['interview_titleid']] ?? 0)
                : (int) $request->selected_interview;

            // Normalize email
            $email = isset($row[$headerIndex['email']])
                ? strtolower(trim($row[$headerIndex['email']]))
                : null;

            // Check email uniqueness per interview
            if (
                $email && DB::table('tblcandidate_cr')
                ->where('email', $email)
                ->where('interview_titleID', $interviewId)
                ->exists()
            ) {
                $errors[] = [
                    'row' => $r + 1,
                    'errors' => ["Email '{$email}' already exists for this interview."]
                ];
                continue;
            }

            // Convert state abbreviation to ID
            $stateAbbr = strtoupper(trim($row[$headerIndex['state']] ?? ''));
            $stateRecord = DB::table('tblstates')
                ->whereRaw('UPPER(REPLACE(abr, " ", "")) = ?', [str_replace(' ', '', $stateAbbr)])
                ->first();
            // Log::info($stateRecord);

            if (!$stateRecord) {
                $errors[] = [
                    'row' => $r + 1,
                    'errors' => ["State abbreviation '{$stateAbbr}' is invalid."]
                ];
                continue;
            }
            $stateId = $stateRecord->StateID;

            Log::info($stateId);

            // Find LGA ID by name and state
            $lgaName = trim($row[$headerIndex['lga']] ?? '');
            $lgaRecord = DB::table('lga')
                ->where('stateId', $stateId)
                ->whereRaw('REPLACE(UPPER(lga), " ", "") = ?', [str_replace(' ', '', $lgaName)])
                ->first();

            if (!$lgaRecord) {
                $errors[] = [
                    'row' => $r + 1,
                    'errors' => ["LGA '{$lgaName}' is invalid for state '{$stateAbbr}'."]
                ];
                continue;
            }
            $lgaId = $lgaRecord->lgaId;

            // Prepare row data
            $rowData = [
                'interview_titleID' => $interviewId,
                'candidate_title'   => trim($row[$headerIndex['candidate_title']] ?? ''),
                'surname'           => trim($row[$headerIndex['surname']] ?? ''),
                'first_name'        => trim($row[$headerIndex['first_name']] ?? ''),
                'othernames'        => trim($row[$headerIndex['othernames']] ?? ''),
                'sex'               => trim($row[$headerIndex['sex']] ?? ''),
                'maritalStatus'     => trim($row[$headerIndex['maritalstatus']] ?? ''),
                'phoneNo'           => trim($row[$headerIndex['phoneno']] ?? ''),
                'email'             => $email,
                'address'           => trim($row[$headerIndex['address']] ?? ''),
                'state'             => $stateId,
                'lga'               => $lgaId,
            ];

            // Validate row data
            $validator = Validator::make($rowData, [
                'interview_titleID' => 'required|integer|exists:tblinterview,interviewID',
                'candidate_title'   => 'required|string|max:255',
                'surname'           => 'required|string|max:100',
                'first_name'        => 'required|string|max:100',
                'sex'               => 'required|string|max:20',
                'maritalStatus'     => 'required|string|max:100',
                'phoneNo'           => 'required|string|max:50',
                'email'             => 'required|email|max:255',
                'address'           => 'required|string',
                'state'             => 'required|integer|exists:tblstates,StateID',
                'lga'               => 'required|integer|exists:lga,lgaId',
            ]);

            if ($validator->fails()) {
                $errors[] = [
                    'row' => $r + 1,
                    'errors' => $validator->errors()->all(),
                ];
                continue;
            }

            // Insert candidate
            DB::table('tblcandidate_cr')->insert([
                'interview_titleID' => $rowData['interview_titleID'],
                'candidate_title'   => $rowData['candidate_title'],
                'surname'           => $rowData['surname'],
                'first_name'        => $rowData['first_name'],
                'othernames'        => $rowData['othernames'],
                'sex'               => $rowData['sex'],
                'maritalStatus'     => $rowData['maritalStatus'],
                'phoneNo'           => $rowData['phoneNo'],
                'email'             => $rowData['email'],
                'address'           => $rowData['address'],
                'state'             => $rowData['state'],
                'lga'               => $rowData['lga'],
                'candidate_status'  => 1,
                'approval_status'   => 0,
                'registry_status'   => 0,
                'uploaded_by'       => auth()->id(),
                'uploaded_at'       => now(),
                'created_at'        => now(),
            ]);

            $inserted++;
        }

        $message = "{$inserted} candidates uploaded successfully.";

        if (!empty($errors)) {
            Log::info('Candidate import errors', $errors);
            return back()
                ->with('warning', $message)
                ->with('row_errors', $errors);
        }

        return back()->with('success', $message);
    }


    public function editCrCandidate(Request $request)
    {
        try {
            // ✅ Validate input
            $validated = $request->validate([
                'candidate_id'   => 'required|integer',
                'interviewID'    => 'required|integer',
                'title'          => 'nullable|string|max:10',
                'surname'        => 'required|string|max:50',
                'first_name'     => 'required|string|max:50',
                'othernames'     => 'nullable|string|max:50',
                'email'          => 'nullable|email|max:100',
                'phoneNo'        => 'nullable|string|max:20',
                'sex'            => 'nullable|string|max:10',
                'maritalStatus'  => 'nullable|string|max:20',
                'state'          => 'nullable|integer',
                'lga'            => 'nullable|integer',
                'address'        => 'nullable|string',
            ]);

            // ✅ Update record
            DB::table('tblcandidate_cr')
                ->where('candidateID', $request->candidate_id)
                ->update([
                    'interview_titleID' => $request->interviewID,
                    'candidate_title'   => $request->title,
                    'surname'           => $request->surname,
                    'first_name'        => $request->first_name,
                    'othernames'        => $request->othernames,
                    'email'             => $request->email,
                    'phoneNo'           => $request->phoneNo,
                    'sex'               => $request->sex,
                    'maritalStatus'     => $request->maritalStatus,
                    'state'             => $request->state,
                    'lga'               => $request->lga,
                    'address'           => $request->address,
                    'updated_at'        => now(),
                ]);

            return back()->with('success', 'Candidate details updated successfully.');
        } catch (\Exception $e) {
            Log::info($e->getMessage());

            return back()->with('error', 'Failed to update candidate: ' . $e->getMessage());
        }
    }


    // delete candidate
    public function deleteCrCandidate(Request $request)
    {

        $id = $request->candidateID;

        // Log::info($id);
        DB::table('tblcandidate_cr')
            ->where('candidateID', $id)
            ->delete();

        return back()->with("success", "Delete successful");
    }


    public function bulkApprove(Request $request)
    {
        $ids = $request->input('selected_candidates', []);

        if (empty($ids)) {
            return back()->with('error', 'No candidate selected for approval.');
        }
        // Log::info($request);
        DB::beginTransaction();
        try {
            // 🔹 Fetch selected candidates from CR table
            $candidates = DB::table('tblcandidate_cr')
                ->whereIn('candidateID', $ids)
                ->get();


            // Log::info($request);
            foreach ($candidates as $c) {

                // ✅ Ensure no nulls for required fields
                $othernames = $c->othernames ?? '';
                $email = $c->email ?? '';
                $phone = $c->phoneNo ?? '';
                $address = $c->address ?? '';
                $title = $c->candidate_title ?? '';
                $maritalStatus = $c->maritalStatus ?? '';
                $sex = $c->sex ?? '';

                // 🔹 Insert into main candidate table
                DB::table('tblcandidate')->insert([
                    'surname'       => $c->surname,
                    'first_name'    => $c->first_name,
                    'othernames'    => $c->othernames,
                    'sex'           => $c->sex,
                    'email'         => $c->email,
                    'phoneNo'       => $c->phoneNo,
                    'state'         => $c->state,
                    'lga'           => $c->lga,
                    'address'       => $c->address,
                    'maritalStatus' => $c->maritalStatus,
                    'candidate_title' => $c->candidate_title,
                    'interview_titleID'   => $c->interview_titleID,
                    'candidate_source'   => "CR",
                    'candidate_status'   => 1,
                    'approval_status'   => 1,
                    'registry_status'   => 1,
                ]);
            }

            // 🔹 Mark as approved in CR table
            DB::table('tblcandidate_cr')
                ->whereIn('candidateID', $ids)
                ->update([
                    'approval_status' => 1,
                    'updated_at' => now()
                ]);

            DB::commit();

            return back()->with('success', 'Selected candidates have been approved and moved to the main table successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            // ✅ Return human-readable message
            $errorMessage = $e->getMessage();

            if (str_contains($errorMessage, 'Integrity constraint violation')) {
                $errorMessage = 'One or more required fields (like surname, firstname, or othernames) are missing or invalid. Please check candidate data before approving.';
            }

            return back()->with('error', 'Error approving candidates: ' . $errorMessage);
        }
    }
}
