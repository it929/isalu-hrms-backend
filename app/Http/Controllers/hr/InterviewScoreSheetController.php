<?php

namespace App\Http\Controllers\hr;



use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Helpers\FileUploadHelper;
use Illuminate\Support\Facades\Log;

class InterviewScoreSheetController extends functionController
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function create()
    {
        $data['getAllcandidates'] = [];
        $interviewID = Session::get('interviewID');

        $data['getInterviewName'] = DB::table('tblinterview')->where('interview_status', 1)->get();
        if ($interviewID) {
            $data['getAllcandidates'] = DB::table('tblcandidate')
                ->leftjoin('tblinterview_score_sheet', 'tblinterview_score_sheet.candidateID', 'tblcandidate.candidateID')
                //check where score_sheet for candidate already exist
                ->whereNull('tblinterview_score_sheet.candidateID')
                ->where('tblcandidate.interview_titleID', $interviewID)
                ->where('tblcandidate.candidate_status', 1)
                ->select('tblcandidate.*')
                ->get();
        }
        $data['getRecords'] = DB::table('tblinterview_score_sheet')
            ->leftJoin('tblinterview', 'tblinterview.interviewID', '=', 'tblinterview_score_sheet.interviewID')
            ->leftJoin('tblcandidate', 'tblcandidate.candidateID', '=', 'tblinterview_score_sheet.candidateID')
            ->where('tblinterview_score_sheet.stages_id', 6)
            ->where('tblcandidate.candidate_status', 1)
            ->where('tblinterview_score_sheet.status', 0)
            ->where('tblinterview_score_sheet.is_approved', 0)
            ->where('tblinterview_score_sheet.interviewID', $interviewID)
            ->get();
        $data['getInterviewID'] = $interviewID;



        return view('hr.InterviewScoreSheet.scoreCandidate', $data);
    }

    //Save
    public function save(Request $request)
    {
        $isSaved = 0;

        $validated = $request->validate([
            'interviewName' => 'required|numeric',
            'candidateName' => 'required|string',
            'appearanceMark' => 'required|numeric|min:0|max:5',
            'comportmentMark' => 'required|numeric|min:0|max:5',
            'fiveQuestionsEach' => 'required|numeric|min:0|max:10',
            'totalMark' => 'required|numeric',
            //'review' => 'required|string',
            'interview_score_file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:100',
        ]);

        if ($request['totalMark'] > 20) {
            return redirect()->back()->with('danger', 'Sorry, you cannot enter more than the maximum score of 20');
        }

        $tblinterview = DB::table('tblinterview')->where('interviewID', $request['interviewName'])->where('close_candidate', 0)->first();

        if ($tblinterview) {
            if (DB::table('tblinterview_score_sheet')->where('interviewID', $request['interviewName'])->where('candidateID', $request['candidateName'])->first()) {
                return redirect()->back()->with('danger', 'Sorry, this candidate has already been added to this interview.');
            } else {
                $file = $request->file('interview_score_file');

                // Generate a proper filename like in nhfRemittanceAttachmentUpload
                // You might want to include candidate name or interview ID for clarity
                $filesParam = 'interview_' . $request['interviewName'] . '_candidate_' . $request['candidateName'] . '_' . time() . '.' . $file->getClientOriginalExtension();

                // Use helper (automatically stores to local or S3)
                $fileUrl = FileUploadHelper::upload($file, 'interviewscorefiles', $filesParam);
                Log::info($fileUrl);

                $isSaved = DB::table('tblinterview_score_sheet')->insertGetId([
                    'interviewID'           => $request['interviewName'],
                    'candidateID'           => $request['candidateName'],
                    'appearance_mark'       => $request['appearanceMark'],
                    'comportment_mark'      => $request['comportmentMark'],
                    'question_each_mark'    => $request['fiveQuestionsEach'],
                    'total_mark'            => ($request['appearanceMark'] + $request['comportmentMark'] + $request['fiveQuestionsEach']),
                    'review'                => $request['review'],
                    'interview_score_file'  => $fileUrl, // Full URL is stored here
                    'stages_id'             => 6,
                    'tblinterview_score_sheet.status' => 0,
                    'tblinterview_score_sheet.is_approved' => 0
                ]);
            }
        } else {
            return redirect()->back()->with('danger', 'Sorry, you cannot add more candidate on this interview.');
        }

        if ($isSaved) {
            return redirect()->back()->with('success', 'Your record was created successfull and is ready to be pushed to the next officer.');
        }
        return redirect()->back()->with('danger', 'Sorry, we cannot process your record. Please try again.');
    }

    //Get Candidate interview
    public function getCandidateInterview(Request $request)
    {
        Session::forget('interviewID');
        Session::put('interviewID', $request['inverviewName']);

        return redirect()->back();
    }


    //Push to Director of Admin
    public function pushNext(Request $request)
    {
        $isSaved = null;
        $validated = $request->validate([
            'selectedCandidate' => 'required|array',
        ]);
        try {
            $userID = (Auth::check() ? Auth::user()->id : null);
            if ($request['selectedCandidate']) {
                try {
                    if ($request['getComment']) {
                        DB::table('tblinterview_comment')->insertGetId([
                            'userID'        => $userID,
                            'interviewID'   => $request['getInterviewID'],
                            'comment'       => $request['getComment']
                        ]);
                    }
                } catch (\Throwable $e) {
                }
                foreach ($request['selectedCandidate'] as $key => $value) {
                    $isSaved = DB::table('tblinterview_score_sheet')->where('score_sheetID', $value)->update([
                        'stages_id'   => 3,
                        'tblinterview_score_sheet.status' => 1
                    ]);
                }
            }
        } catch (\Throwable $e) {
        }
        if ($isSaved) {
            return redirect()->back()->with('success', 'The list of the selected candidate scores have been sent.');
        }
        return redirect()->back()->with('danger', 'Sorry, we cannot process your record. Please try again.');
    }

    //=============================Admin Report=================================================
    public function adminFinalApproval()
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
        // if (DB::table('tblaction_stages')->where('userID', $userID)->first()) {
        $data['getRecords'] = DB::table('tblinterview_score_sheet')
            ->leftJoin('tblinterview', 'tblinterview.interviewID', '=', 'tblinterview_score_sheet.interviewID')
            ->leftJoin('tblcandidate', 'tblcandidate.candidateID', '=', 'tblinterview_score_sheet.candidateID')
            ->where('tblinterview_score_sheet.stages_id', 3)
            ->where('tblcandidate.candidate_status', 1)
            ->where('tblinterview.interviewID', $interviewID)
            ->where('tblinterview_score_sheet.status', 1)
            ->where('tblinterview_score_sheet.is_approved', 0)
            ->get();

        $data['interviewAttachments'] = DB::table('interviewattachments')->where('interviewID', $interviewID)->get();
        // }
        //check if any candidate has been approved
        $data['checkForAnyCandidateApproval'] = DB::table('tblinterview_score_sheet')
            ->where('is_approved', 1)->where('interviewID', $interviewID)
            ->where('tblinterview_score_sheet.stages_id', 3)
            ->get();
        //get all comment on this interview
        $data['getComments'] = DB::table('tblinterview_comment')->where('interviewID', $interviewID)->orderBy('commentID', 'Desc')->get();
        $data['getInterviewID'] = $interviewID;
        $data['getAttachments'] = DB::table('interviewattachments')->where('interviewID', $interviewID)->get();

        // return $data;
        return view('hr.InterviewScoreSheet.scoreAdminApproval', $data);
    }



    public function rejectCandidate(Request $request, $id)
    {
        $interviewID = Session::get('interviewID');
        DB::table('tblinterview_score_sheet')
            ->where('score_sheetID', $id)
            ->update([
                'status' => 0,
                'stages_id' => 6
            ]);

        DB::table('tblinterview_comment')->insert([
            'userID'        => auth()->id(),
            'interviewID'   => $interviewID,
            'comment'       => $request->comment,
            'comment_status' => 0,
            'created_at'    => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Candidate rejected successfully'
        ]);
    }




    public function rejectSelectedCandidates13122025(Request $request)
    {
        $selected = $request->input('selectedCandidates', []);

        if (empty($selected)) {
            return response()->json(['success' => false, 'message' => 'No candidates selected']);
        }

        DB::table('tblinterview_score_sheet')
            ->whereIn('score_sheetID', $selected)
            ->update([
                'status' => 0,
                'stages_id' => 6,
            ]);

        return response()->json(['success' => true, 'message' => 'Selected candidates rejected successfully']);
    }

    public function rejectSelectedCandidates(Request $request)
    {
        $interviewID = Session::get('interviewID');
        $selected = $request->selectedCandidates;
        $comment  = $request->comment;

        if (empty($selected)) {
            return response()->json(['success' => false, 'message' => 'No candidates selected']);
        }

        DB::table('tblinterview_score_sheet')
            ->whereIn('score_sheetID', $selected)
            ->update([
                'status' => 0,
                'stages_id' => 6
            ]);

        // $data = [];
        // foreach ($selected as $id) {
        //     $data[] = [
        //         'userID'         => auth()->id(),
        //         'interviewID'    => $interviewID,
        //         'comment'        => $comment,
        //         'comment_status' => 0,
        //         'created_at'     => now(),
        //     ];
        // }

        // DB::table('tblinterview_comment')->insert($data);
        DB::table('tblinterview_comment')->insert([
            'userID'         => auth()->id(),
            'interviewID'    => $interviewID,
            'comment'        => $comment,
            'comment_status' => 0,
            'created_at'     => now(),
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Selected candidates rejected successfully'
        ]);
    }





    // delete candidate
    public function deleteScoreSheet($id)
    {
        DB::table('tblinterview_score_sheet')
            ->where('score_sheetID', $id)
            ->delete();

        return back()->with("success", "Delete successfull");
    }

    //edit candidate
    public function editScoreSheet($id)
    {

        $data['candidate'] = DB::table('tblinterview_score_sheet')
            ->where('score_sheetID', $id)
            ->leftJoin('tblinterview', 'tblinterview.interviewID', '=', 'tblinterview_score_sheet.interviewID')
            ->leftJoin('tblcandidate', 'tblcandidate.candidateID', '=', 'tblinterview_score_sheet.candidateID')
            ->first();

        return view('hr.InterviewScoreSheet.editadminsheet', $data);
    }
    // update
    public function updateScoreSheet(Request $request)
    {
        $validated = $request->validate([
            'candidateName' => 'required|string',
            'appearanceMark' => 'required|numeric|min:0|max:5',
            'comportmentMark' => 'required|numeric|min:0|max:5',
            'fiveQuestionsEach' => 'required|numeric|min:0|max:10',
            'totalMark' => 'required|numeric',
            //'review' => 'required|string',
        ]);

        if ($request['totalMark'] > 20) {
            return redirect()->back()->with('danger', 'Sorry, you cannot enter more than the maximum score of 20');
        }

        DB::table('tblinterview_score_sheet')->where('score_sheetID', $request['score_sheetID'])->update([
            'appearance_mark'       => $request['appearanceMark'],
            'comportment_mark'      => $request['comportmentMark'],
            'question_each_mark'    => $request['fiveQuestionsEach'],
            'total_mark'            => ($request['appearanceMark'] + $request['comportmentMark'] + $request['fiveQuestionsEach']), //$request['totalMark'],
            'review'                => $request['review'],
        ]);
        // return redirect(url('/interview-score-sheet'))->with("success", "Update successfull");
        $redirectUrl = $request->redirect_url ?? url('/interview-score-sheet');
        return redirect($redirectUrl)->with('success', 'Update successful');
        // return redirect()->back()->with("success", "Update successfull");
    }


    //Admin pushes to Secretary
    public function pushToSecretaryFromAdmin(Request $request)
    {
        $isSaved = null;
        $validated = $request->validate([
            'selectedCandidate' => 'required|array',
        ]);
        try {
            $userID = (Auth::check() ? Auth::user()->id : null);
            if ($request['selectedCandidate']) {
                try {
                    if ($request['getComment']) {
                        DB::table('tblinterview_comment')->insertGetId([
                            'userID'        => $userID,
                            'interviewID'   => $request['getInterviewID'],
                            'comment'       => $request['getComment']
                        ]);
                    }
                } catch (\Throwable $e) {
                }
                foreach ($request['selectedCandidate'] as $key => $value) {
                    $isSaved = DB::table('tblinterview_score_sheet')->where('score_sheetID', $value)->update([
                        'stages_id'   => 6,
                    ]);
                }
            }
        } catch (\Throwable $e) {
        }
        if ($isSaved) {
            return redirect()->back()->with('success', 'The list of the selected candidate scores have been approved.');
        }
        return redirect()->back()->with('danger', 'Sorry, we cannot process your record. Please try again.');
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

        // dd($userID);
        // if (DB::table('tblaction_stages')->where('userID', $userID)->first()) {
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
        // }
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
                    // if(DB::table('tblinterview_score_sheet')->where('score_sheetID', $value)->value('is_approved') == 1)
                    // {
                    //     $isSaved = DB::table('tblinterview_score_sheet')->where('score_sheetID', $value)->update([
                    //         'is_approved'   => 0,
                    //         'approval_userID' => $userID,
                    //         'is_final_approval' => 0,
                    //     ]);
                    //     if($isSaved)
                    //     {
                    //         //update candidate approval status
                    //         DB::table('tblcandidate')->where('candidateID', DB::table('tblinterview_score_sheet')->where('score_sheetID', $value)->value('candidateID') )->update([
                    //             'approval_status'   => 0
                    //         ]);
                    //     }
                    // }else{
                    //     $isSaved = DB::table('tblinterview_score_sheet')->where('score_sheetID', $value)->update([
                    //         'is_approved'   => 1,
                    //         'approval_userID' => $userID
                    //     ]);
                    //     if($isSaved)
                    //     {
                    //        //update candidate approval status
                    //         DB::table('tblcandidate')->where('candidateID', DB::table('tblinterview_score_sheet')->where('score_sheetID', $value)->value('candidateID') )->update([
                    //             'approval_status'   => 1
                    //         ]);
                    //     }
                    // }

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

        return view('hr.InterviewScoreSheet.candidateAppointmentLetter', $data);
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
}//end class
