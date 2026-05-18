<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;

use App\Models\User;
use App\Http\Requests;
use App\Models\TblPer;
use Illuminate\Http\Request;
use App\Notifications\SentFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
//use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Notification;



class BulkFileMovementController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->middleware('auth');
        $this->division    = $request->session()->get('division');
        $this->divisionID  = $request->session()->get('divisionID');
                return $next($request);
        });
    }

    public function create()
    {

        // dd(request()->resendBulkID);
        $data['details']    = DB::table('division_registry')->get();
        $data['department'] = DB::table('tbldepartment')->get();
        //$data['recipient']  = DB::table('users')->where('user_section','=','hr')->get();
        $data['recipient']  = DB::table('users')->get();
        
        return view('bulkFileMovement.create', $data);
    }

    public function getStaff(Request $request)
    {
        $fileNo = $request->input('nameID');
        DB::enableQueryLog();

        $check = DB::table('tblbulk_select_log')->where('fileNo', '=', $fileNo)->count();
        if ($check > 0) {
            return response()->json($check);
        } else {
            $data = DB::table('tblbulk_select_log')->insert(array(
                'fileNo'         => $fileNo,
                'date'           => date('Y-m-d'),

            ));

            return response()->json($data);
        }
    }

    public function getUsers(Request $request)
    {
        $sectionID = $request['sectionID'];

        $data = DB::table('tblper')->where('department', '=', $sectionID)->where('staff_status', '=', 1)->get();
        return response()->json($data);
    }

    public function tempGet()
    {
        $data = DB::table('tblfiles')
            ->join('tblbulk_select_log', 'tblbulk_select_log.fileNo', '=', 'tblfiles.fileNo')
            ->join('tblvolume', 'tblvolume.ID', '=', 'tblfiles.volume')
            //->where('fileNo', '=', $term)
            //->where('file_destination_section', '=', 40)
            //->select('*','surname', 'first_name', 'othernames', 'Designation')
            ->get();

        return response()->json($data);
    }

    public function deleteTemp(Request $request)
    {
        $fileNo = $request['fileNo'];
        DB::table('tblbulk_select_log')->where('fileNo', '=', $fileNo)->delete();
        return response()->json("Deleted");
    }


    public function saveBulk(Request $request)
    {
        $this->validate(
            $request,
            [
                'fileNo'          => 'required',
                'volume'          => 'required',
                'lastPage'        => 'required',
                'recipient'       => 'required',
                'destination'     => 'required',
            ]
        );
        $newDate = date("Y-m-d", strtotime($request['returnDate']));

        $fileNo            = $request['fileNo'];
        $volume            = $request['volume'];
        $lastpage          = $request['lastPage'];
        $recipient         = $request['recipient'];
        $purpose           = $request['purpose'];
        $destination       = $request['destination'];
        $returndate        = $newDate;
        $date              = date("Y-m-d h:i:s");

        //check if file has already been sent by logged user
        $fileSentBefore = DB::table('tblbulk_file_movement')->where('fileNo', $request->fileNo)->where('transfered_by', '!=', null)->get();
        
        foreach($fileSentBefore as $fS){
            if($fS){
                $sentTo = $fS->recipient;
                $sentToName = DB::table('tblper')->where('UserID', $sentTo)->first();
                $sentToResult = $sentToName->surname. ' '.$sentToName->first_name;
                return back()->with('err', "File already moved to $sentToResult");
            }
        }
        

        DB::table('tblbulk_select_log')->delete();
        foreach ($fileNo as $key => $value) {
            $check = DB::table('tblbulk_file_movement')->where('fileNo', '=', $fileNo)->count();
            /*if($check > 0)
            {
              return null;
            }
            else
            {*/
            $fromDept = DB::table('tblfiles')
                            //->join('tbldepart','tbldepartment.id','=', 'tblfiles.file_destination_section')
                            ->where('fileNo', '=', $request['fileNo'][$key])->first();
            //dd($fromDept->file_destination_section);

            $lastID = DB::table('tblbulk_file_movement')->insertGetId(array(
                'fileNo'                  => $request['fileNo'][$key],
                'volume'                  => $request['volume'][$key],
                'last_page'               => $request['lastPage'][$key],
                'recipient'               => $recipient,
                'purpose'                 => $purpose,
                'origin_dept'             => $fromDept->file_destination_section,
                'destination'             => $destination,
                'return_date'             => $returndate,
                'status'                  => 0,
                'status_description'      => 'Pending Acceptance',
                'transfered_by'           => Auth::user()->id,
                'date_transfered'         => $date,
                'date_accepted'           => $date,

            ));

            DB::table('tblfiles')->where('fileNo', '=', $request['fileNo'][$key])->update(array(

                'file_destination_section'        => $destination,

            ));

            // Notification::send($recipients, new SentFile($lastID,"bulk-movement/accept"));

        }
        //}
        $recipient_staff = db::table('tblper')->where('UserID', $recipient)->first();
        //  $loggedUser = User::where('ID', $loggedPer->UserID)->first();
        $rName = $recipient_staff->surname.' '.$recipient_staff->first_name;
          $recipients = User::where('id', $recipient_staff->UserID)->first();
          $recipients->notify(new SentFile($recipients,"bulk-movement/accept"));

        return redirect('/bulk-movement/create')->with('msg', "File successfully moved to $rName");
    }



    public function confirm(Request $request)
    {
        $bulkID = $request->input('bulkID');
        $value = $request->input('value');

        if ($value == 'confirm') {
            $check = DB::table('tblbulk_file_movement')
                ->where('bulkID', '=', $bulkID)
                ->first();

            DB::table('tblbulk_file_movement')->where('bulkID', '=', $bulkID)->update(array(
                'status'                     => 1,
                'status_description'         => "File Accepted",
                'accepted_by'                => Auth::user()->id,
            ));
            //return response()->json("Successfully Accepted");
            return back()->with('msg', 'successfully');
        } elseif ($value == 'reject') {
            DB::table('tblbulk_file_movement')->where('bulkID', '=', $bulkID)->update(array(
                'status'                     => 4,
                'status_description'         => "File Rejected",

            ));
            return response()->json("Successfully Rejected");
        }
    }
    public function rejectFile(Request $request)
    {
        $bulkID = $request->input('bulkID');
        $fileID = $request->input('fileID');
        $comment = $request['comment'];
        $fileNo  = $request['fileNo'];
        // dd($bulkID);
        $authDept = DB::table('tblper')->where('UserID', Auth::user()->id)->value('department');
        $initialDept = DB::table('tblbulk_file_movement')->where('bulkID', '=', $bulkID)->first();

        DB::table('tblbulk_file_movement')->where('bulkID', '=', $bulkID)->update(array(
            'transfered_by' => $initialDept->transfered_by,
            'recipient' => Auth::user()->id,
            'destination' => $initialDept->origin_dept,
            'origin_dept' => $authDept,
            'status'                     => 4,
            'status_description'         => "File Rejected",

        ));

        DB::table('tblfiles')->where('fileNo', '=', $request['fileNo'])->update([
            'file_destination_section'        => $initialDept->origin_dept,
        ]);

        DB::table('tbltracking_comments')->insert(array(

            'comment'                     => $comment,
            'fileNo'                      => $fileNo,
            'bulkID'                      => $bulkID,
            'file_id'                     => $fileID,
            'updated_at'                  => date('Y-m-d'),
            'comment_by'                  => Auth::user()->id,
        ));
        return back()->with('msg', 'You just rejected a file');
    }

    public function resend(Request $request)
    {
        $bulkID = $request->input('bulkID');
        $fileID = $request->input('fileID');
        $comment = $request['comment'];
        $fileNo  = $request['fileNo'];
        //dd($bulkID);
        DB::table('tblbulk_file_movement')->where('bulkID', '=', $bulkID)->update(array(
            'status'                     => 0,
            'status_description'         => "File Resent",
            'is_resent'                   => 1,
        ));
        DB::table('tbltracking_comments')->insert(array(

            'comment'                     => $comment,
            'fileNo'                      => $fileNo,
            'bulkID'                      => $bulkID,
            'file_id'                     => $fileID,
            'updated_at'                  => date('Y-m-d'),
            'comment_by'                  => Auth::user()->id,
            'is_resent'                   => 1,
        ));
        return back()->with('msg', 'Successfully resent');
    }

    public function editAndSend($bulkID)
    {
        $d = base64_decode($bulkID);
        $data['department'] = DB::table('tbldepartment')->get();
        $data['bulkID'] = $d;
        $data['file'] = DB::table('tblbulk_file_movement')
            ->join('tblfiles', 'tblfiles.fileNo', '=', 'tblbulk_file_movement.fileNo')
            ->where('tblbulk_file_movement.bulkID', '=', $d)
            ->select('*', 'tblbulk_file_movement.volume as fileVolume', 'tblbulk_file_movement.last_page as fileLastPage')
            ->first();
        $data['recipient']  = DB::table('tblper')->where('UserID', '=', $data['file']->recipient)->first();
        //dd($data['file']);
        return view('bulkFileMovement.bulkTransferEdit', $data);
    }
    public function updateAndSend(Request $request)
    {
        $this->validate(
            $request,
            [
                'fileNo'          => 'required',
                'volume'          => 'required',
                'lastPage'        => 'required',
                'recipient'       => 'required',
                'destination'     => 'required',
                'purpose'         => 'required',
            ]
        );
        $newDate = date("Y-m-d", strtotime($request['returnDate']));

        $fileNo            = $request['fileNo'];
        $volume            = $request['volume'];
        $lastpage          = $request['lastPage'];


        $recipient         = $request['recipient'];
        $purpose           = $request['purpose'];
        $destination       = $request['destination'];
        $returndate        = $newDate;
        $date              = date("Y-m-d h:i:s");
        $bulkID            = $request['bulkID'];


        $fromDept = DB::table('tblfiles')
            ->where('fileNo', '=', $request['fileNo'])->first();

        $lastID = DB::table('tblbulk_file_movement')->where('bulkID', '=', $bulkID)->update(array(
            'fileNo'                  => $request['fileNo'],
            'volume'                  => $request['volume'],
            'last_page'               => $request['lastPage'],
            'recipient'               => $recipient,
            'purpose'                 => $purpose,
            'origin_dept'             => $fromDept->file_destination_section,
            'destination'             => $destination,
            'return_date'             => $returndate,
            'status'                  => 0,
            'status_description'      => 'Pending Acceptance',
            'transfered_by'           => Auth::user()->id,
            'date_transfered'         => $date,
            'date_accepted'           => $date,

        ));

        DB::table('tblfiles')->where('fileNo', '=', $request['fileNo'])->update(array(

            'file_destination_section'        => $destination,

        ));
        $recipient_staff = db::table('tblper')->where('UserID', $recipient)->first();
        $rName = $recipient_staff->surname.' '.$recipient_staff->first_name;

        return redirect(url('/bulk-movement/accept'))->with('msg', "You have sent file to $rName");
    }


    public function cancel(Request $request)
    {
        $fileNo = $request->input('fileNo');
        DB::table('tblfiles')->where('fileNo', '=', $fileNo)->update(array(
            'file_destination_section'                     => 40,
        ));

        DB::table('tblbulk_file_movement')->where('fileNo', '=', $fileNo)->where('status', '=', 4)->where('transfered_by', '=', Auth::user()->id)->delete();
        //return response()->json("Successfully Cancelled");
        return back()->with('msg', 'Successfull');
    }


    public function acceptance()
    {

        $logedin_user = Auth::user()->id;
        $data['user'] = DB::table('users')
            ->where('id', '=', $logedin_user)
            ->first();
        $data['userSection'] = DB::table('tblper')
            ->where('UserID', '=', $logedin_user)
            ->first();
        $data['authUser'] = Auth::user()->id;
        $data['acceptance_view'] = DB::table('tblbulk_file_movement')
            ->join('tblfiles', 'tblfiles.fileNo', '=', 'tblbulk_file_movement.fileNo')
            ->join('tblvolume', 'tblvolume.ID', '=', 'tblbulk_file_movement.volume')
            ->where('tblbulk_file_movement.status', '=', 1)
            ->where('tblfiles.isArchived', 0)
            //->orWhere('tblbulk_file_movement.status','=',0)
            ->orWhere(function ($query) {
                $query->where('tblbulk_file_movement.status', '=', 0);
            })
            ->where('tblfiles.isArchived', 0)
            //->orWhere('tblbulk_file_movement.transfered_status','=',0)
            ->orderBy('tblbulk_file_movement.bulkID', 'DESC')
            ->select('*', 'tblbulk_file_movement.volume as fileVolume', 'tblbulk_file_movement.last_page as fileLastPage', 'tblbulk_file_movement.status as bulkStatus', 'tblfiles.ID as fileID')
            ->get();
        // dd('here3');
        //dd($data['acceptance_view']);
        return view('bulkFileMovement/accept', $data);
    }


    //Return files url
    public function transfer()
    {
        $data['details']    = DB::table('division_registry')->get();
        $data['department'] = DB::table('tbldepartment')->get();

        $data['recipient']  = DB::table('users')->get();

        $logedin_user = Auth::user()->id;
        $data['recipientSection']  = DB::table('tblper')->where('UserID', '=', $logedin_user)->first();
        $data['user'] = DB::table('users')
            ->where('id', '=', $logedin_user)
            ->first();
        $data['authUser'] = Auth::user()->id;

        $data['acceptance_view'] = DB::table('tblbulk_file_movement')
            ->join('tblfiles', 'tblfiles.fileNo', '=', 'tblbulk_file_movement.fileNo')
            ->join('tbldepartment', 'tbldepartment.id', '=', 'tblbulk_file_movement.destination')
            ->where('tblbulk_file_movement.status', '=', 1)
            //->where('transfered_status','=',0)
            ->where('status_description', '=', 'File Accepted')
            //->orWhere('status','=',0)
            ->select('*', 'tblbulk_file_movement.volume as fileVolume', 'tblbulk_file_movement.last_page as fileLastPage')
            ->get();
        //dd($data['acceptance_view']);
        $data['count'] = DB::table('tblbulk_file_movement')
            ->join('tblfiles', 'tblfiles.fileNo', '=', 'tblbulk_file_movement.fileNo')
            ->join('tbldepartment', 'tbldepartment.id', '=', 'tblbulk_file_movement.destination')
            ->where('tblbulk_file_movement.status', '>', 0)
            //->where('transfered_status','=',0)
            ->where('status_description', '=', 'File Accepted')
            //->orWhere('status','=',0)
            ->count();

        $data['transfered_files'] = DB::table('tblbulk_file_movement')
            ->join('tblfiles', 'tblfiles.fileNo', '=', 'tblbulk_file_movement.fileNo')
            ->join('tblvolume', 'tblvolume.ID', '=', 'tblbulk_file_movement.volume')
            ->join('tbldepartment', 'tbldepartment.id', '=', 'tblbulk_file_movement.destination')
            ->where('transfered_status', '=', 1)
            //->selectRaw('')

            //->where('recipient','=',Auth::user()->id)
            // ->orWhere('status','=',0)
            //->orWhere('transfered_status','=',0)
            ->select('*', 'tblbulk_file_movement.volume as fileVolume', 'tblbulk_file_movement.last_page as fileLastPage')
            ->get();

        return view('bulkFileMovement.transfer', $data);
    }

    public function trackFile()
    {
        $data['section'] = DB::table('tbldepartment')->get();

        $data['transfered_files'] = DB::table('tblfiles')
            ->join('tblbulk_file_movement', 'tblbulk_file_movement.fileNo', '=', 'tblfiles.fileNo')
            ->join('tblper', 'tblper.UserID', '=', 'tblbulk_file_movement.recipient')
            ->join('tblvolume', 'tblvolume.ID', '=', 'tblbulk_file_movement.volume')
            ->join('tbldepartment', 'tbldepartment.id', '=', 'tblfiles.file_destination_section')
            ->where('tblfiles.file_destination_section', '!=', 40)
            ->where('tblbulk_file_movement.status', '!=', 4)
            ->selectRaw('tblfiles.file_description, tblfiles.fileNo, tblbulk_file_movement.recipient, tblbulk_file_movement.bulkID, tbldepartment.department, tblbulk_file_movement.volume,tblbulk_file_movement.origin_dept, tblbulk_file_movement.last_page, tblbulk_file_movement.destination, tblbulk_file_movement.date_transfered, tblper.surname, tblper.first_name, tblper.othernames, tblvolume.volume_name')
            ->get();

        //dd($data['transfered_files'] );

        return view('bulkFileMovement.track', $data);
    }

    public function postTrackFile(Request $request)
    {
        // if ($request->isMethod('get')) {
        //     return redirect('/bulk-transfer/track');
        // }

        $data['section'] = DB::table('tbldepartment')->get();
        $from        = $request['from'];
        $to         = $request['to'];
        $fromDate        = date('Y-m-d', strtotime(trim($request['from'])));
        $toDate          = date('Y-m-d', strtotime(trim($request['to'])));
        $fileNo          = $request['nameID'];
        $section         = $request['section'];
        // dd($fileNo);

        if (($fileNo == '') && ($from == '' && $to == '') && ($section == '')) {
            return back()->with('err', "No field selected, Please search from any of these sections");
        }

        if (($section != '') && ($from == '' && $to == '') && ($fileNo == '')) {
            // dd($section);
            $data['transfered_files'] = DB::table('tblfiles')
                ->join('tblbulk_file_movement', 'tblbulk_file_movement.fileNo', '=', 'tblfiles.fileNo')
                ->leftjoin('tbldepartment', 'tbldepartment.id', '=', 'tblfiles.file_destination_section')
                ->where('tblfiles.file_destination_section', '=', $section)
                //->selectRaw('tblfiles.file_description, tblper.fileNo, tbldepartment.department, tblbulk_file_movement.volume, tblbulk_file_movement.last_page, tblbulk_file_movement.date_transfered')
                ->get();
            // dd($data['transfered_files']);
            
            return view('bulkFileMovement.track', $data);

        }elseif (($from != '' && $to != '') && ($section == '') && ($fileNo == '')) {
         
            $data['transfered_files'] = DB::table('tblfiles')
                ->join('tblbulk_file_movement', 'tblbulk_file_movement.fileNo', '=', 'tblfiles.fileNo')
                ->join('tbldepartment', 'tbldepartment.id', '=', 'tblfiles.file_destination_section')
                // ->where('tblfiles.file_destination_section', '!=', 40)
                ->whereBetween('date_transfered', [$fromDate, $toDate])
                //->selectRaw('tblper.first_name, tblper.surname, tblper.othernames, tblper.fileNo, tblsection.section, tblbulk_file_movement.volume, tblbulk_file_movement.last_page, tblbulk_file_movement.date_transfered')
                ->get();
            //dd($fromDate);
        
            return view('bulkFileMovement.track', $data);

        }elseif (($fileNo != '') && ($from == '' && $to == '') && ($section == '')) {
            //dd('yrtyrt7y3');
            $data['transfered_files'] = DB::table('tblfiles')
                ->join('tblbulk_file_movement', 'tblbulk_file_movement.fileNo', '=', 'tblfiles.fileNo')
                ->join('tbldepartment', 'tbldepartment.id', '=', 'tblfiles.file_destination_section')
                ->where('tblfiles.fileNo', '=', $fileNo)
                // ->where('tblfiles.file_destination_section', '!=', 40)
                // ->selectRaw('tblper.first_name, tblper.surname, tblper.othernames, tblper.fileNo, tblsection.section, tblbulk_file_movement.volume, tblbulk_file_movement.last_page, tblbulk_file_movement.date_transfered')
                ->get();
            //dd($data['transfered_files'] );
        
            return view('bulkFileMovement.track', $data);

        }elseif (($fileNo != '') && ($from != '' && $to != '') && ($section != '')) {
            //dd('yrtyrt7y3');
            $data['transfered_files'] = DB::table('tblfiles')
                ->join('tblbulk_file_movement', 'tblbulk_file_movement.fileNo', '=', 'tblfiles.fileNo')
                ->join('tbldepartment', 'tbldepartment.id', '=', 'tblfiles.file_destination_section')
                ->where('tblfiles.fileNo', '=', $fileNo)
                ->whereBetween('date_transfered', [$fromDate, $toDate])
                ->where('tblfiles.file_destination_section', '=', $section)
                // ->where('tblfiles.file_destination_section', '!=', 40)
                // ->selectRaw('tblper.first_name, tblper.surname, tblper.othernames, tblper.fileNo, tblsection.section, tblbulk_file_movement.volume, tblbulk_file_movement.last_page, tblbulk_file_movement.date_transfered')
                ->get();
            //dd($data['transfered_files'] );
            return view('bulkFileMovement.track', $data);

        }elseif (($fileNo == '') && ($from != '' && $to != '') && ($section != '')) {
            //dd('yrtyrt7y3');
            $data['transfered_files'] = DB::table('tblfiles')
                ->join('tblbulk_file_movement', 'tblbulk_file_movement.fileNo', '=', 'tblfiles.fileNo')
                ->join('tbldepartment', 'tbldepartment.id', '=', 'tblfiles.file_destination_section')
                ->whereBetween('date_transfered', [$fromDate, $toDate])
                ->where('tblfiles.file_destination_section', '=', $section)
                // ->where('tblfiles.file_destination_section', '!=', 40)
                // ->selectRaw('tblper.first_name, tblper.surname, tblper.othernames, tblper.fileNo, tblsection.section, tblbulk_file_movement.volume, tblbulk_file_movement.last_page, tblbulk_file_movement.date_transfered')
                ->get();
            //dd($data['transfered_files'] );
            return view('bulkFileMovement.track', $data);

        }else{
            return back()->with('err', 'Something went wrong, Please search again');
        }
    }

    public function postTransfer(Request $request)
    {
        if (isset($request['checkname'])) {
            $check             = $request['checkname'];
            //dd($check);
            $volume            = $request['volume'];
            $lastpage          = $request['lastPage'];

            $id                = $request['id'];
            $recipients         = $request['recipient'];
            $purpose           = $request['purpose'];
            $destination       = $request['destination'];
            $returndate        = $request['returnDate'];
            $date              = date("Y-m-d h:i:s");
            if ($recipients == '') {
                $recipient = 0;
            } else {
                $recipient         = $request['recipient'];
            }
            //dd($recipient);

            foreach ($check as $key => $value) {
                // $countRows = DB::table('scores')->where('studentId','=',$request['student'][$val])->count();
                //if($countRows > 0)
                //{
                $fromDept = DB::table('tblfiles')
                    ->where('fileNo', '=', $request['fileNo'][$key])->first();
                DB::table('tblbulk_file_movement')->insert(array(
                    'fileNo'                  => $request['fileNo'][$key],
                    'volume'                  => $request['volume'][$key],
                    'last_page'               => $request['lastPage'][$key],
                    'recipient'               => $recipient,
                    'purpose'                 => $purpose,
                    'origin_dept'             => $fromDept->file_destination_section,
                    'destination'             => $destination,
                    'return_date'             => $returndate,
                    'status'                  => 0,
                    'transfered_by'           => Auth::user()->id,
                    'status_description'      => 'Pending Acceptance',
                    'date_transfered'         => $date,
                    'transfered_status'       => 3,

                ));

                DB::table('tblbulk_file_movement')->where('bulkID', '=', $id[$key])->update(array(

                    'status'                  => 2,
                    'transfered_status'       => 1,

                ));

                DB::table('tblfiles')->where('fileNo', '=', $request['fileNo'][$key])->update(array(

                    'file_destination_section'        => $destination,

                ));
            }
            return redirect('/bulk-transfer/move')->with('message', 'successfully Entered');
        } else {
            return back()->with('msg', 'Please,Select File to transfer');
        }
    }

    public function filesSent()
    {
        $logedin_user = Auth::user()->id;
        $data['user'] = DB::table('users')
            ->where('id', '=', $logedin_user)
            ->first();
        $data['authUser'] = Auth::user()->id;
        $data['sent_files'] = DB::table('tblbulk_file_movement')
            ->join('tblfiles', 'tblfiles.fileNo', '=', 'tblbulk_file_movement.fileNo')
            ->where('tblbulk_file_movement.transfered_by', '=', Auth::user()->id)
            ->orderBy('bulkID', 'DESC')
            ->select('*', 'tblbulk_file_movement.volume as fileVolume', 'tblbulk_file_movement.status as bulkStatus', 'tblbulk_file_movement.last_page as fileLastPage')
            //->orWhere('transfered_status','=',0)
            ->paginate(20);
        return view('bulkFileMovement/filesTransfered', $data);
    }


    public function filter_staff(Request $request)
    {
        $filterBy = trim($request['fileNo']);
        $filterDivision = trim($request['filterDivision']);

        if ($filterDivision == "" && $filterBy == "") {

            return redirect('/staff-report/view');
        }
        if ($filterDivision == "") {
            $data['users'] = DB::table('tblfiles')
                ->Join('tbldivision', 'tblfiles.divisionID', '=', 'tbldivision.divisionID')
                ->where('tblfiles.file_description', 'LIKE', '%' . $filterBy . '%')
                //->orWhere('tblper.first_name', 'LIKE', '%'.$filterBy.'%')
                ->orWhere('tblper.fileNo', '=', $filterBy)
                //->where('tblper.staff_status', 1)
                //->where('tblper.employee_type', '<>', 'JUDGES')
                //->where('tblper.divisionID', $this->divisionID)
                //->orderBy('tblper.grade', 'Desc')
                //->orderBy('tblper.step', 'Desc')
                //->orderBy('tblper.appointment_date', 'Asc')
                ->paginate(20);
            $data['getDivision'] = DB::table('tbldivision')->get();
            $data['filterDivision'] = "";
            return view('openRegistry.viewStaff', $data);
        } else if ($filterDivision != "") {
            $data['users'] = DB::table('tblfiles')
                ->Join('tbldivision', 'tblper.divisionID', '=', 'tbldivision.divisionID')
                ->where('tblfiles.staff_status', 1)
                ->where('tblfiles.employee_type', '<>', 'JUDGES')
                ->where('tblfiles.divisionID', $filterDivision)
                ->orderBy('tblfiles.grade', 'Desc')
                ->orderBy('tblfiles.step', 'Desc')
                ->orderBy('tblfiles.appointment_date', 'Asc')
                ->paginate(20);
            $data['getDivision'] = DB::table('tbldivision')->get();
            $getDivFilter = DB::table('tbldivision')->where('divisionID', $filterDivision)->first();
            $data['filterDivision'] = ' IN ' . $getDivFilter->division . ' DIVISION';
            return view('openRegistry.viewStaff', $data);
        } else {
            return redirect('/staff-report/view');
        }
    }


    public function indexview()
    {
        $data['details'] = DB::table('division_registry')->get();
        $data['profile'] = DB::table('tblfiles')
            ->where('divisionID', '=', $this->divisionID)
            ->where('grade', '<>', "")
            ->where('divisionID', '<>', null)
            ->orderBy('tblfiles.fileNo', 'Desc')
            ->get();
        $data['registry'] = DB::table('openregistry')->get();

        return view('openRegistry.create', $data);
    }

    public function autocomplete(Request $request)
    {
        $query = $request->input('query');
        $search = DB::table('tblfiles')->where('file_description', 'LIKE', '%' . $query . '%')
                                    //->orWhere('first_name', 'LIKE', '%'.$query.'%')
                                    ->where('file_destination_section', '=', 40)
                                    ->orWhere('fileNo', 'LIKE', '%' . $query . '%')->take(15)
                                    ->where('isArchived', 0)
                                    ->get();
        $return_array = null;
        foreach ($search as $s) {
            $return_array[]  =  ["value" => $s->file_description . ' - ' . $s->fileNo, "data" => $s->fileNo];
        }
        return response()->json(array("suggestions" => $return_array));
    }



    public function showAll(Request $request)
    {
        $term = $request->input('nameID');
        DB::enableQueryLog();
        $data = DB::table('tblfiles')
            ->leftJoin('tbldivision', 'tblfiles.divisionID', '=', 'tbldivision.divisionID')
            ->where('tblfiles.fileNo', '=', $term)
            ->select('fileNo', 'surname', 'first_name', 'othernames', 'fileNo', 'division', 'Designation', 'gender')
            ->get();
        return response()->json($data);
    }
    public function personalFileData(Request $request)
    {
        $filenum = $request->input('fileno');
        DB::enableQueryLog();
        $data = DB::table('openregistry')
            ->where('fileNo', '=', $filenum)
            ->select('FileNo', 'staffname', 'gender', 'division', 'nameOfRecepient', 'Designation', 'returnedDate', 'in_out', 'volumes', 'lastPageNumber', 'dateOpen', 'destination', 'purposeOfMovement')
            ->get();
        return response()->json($data);
    }

    public function store(Request $request)
    {

        $this->validate(
            $request,
            [
                'staffname'          => 'required',
                'gender'             => 'required',
                'fileno'             => 'required|string',
                'designation'        => 'required',
            ]
        );
        $fullName                = trim($request['staffname']);
        $gender                  = trim($request['gender']);
        $fileno                  = trim($request['fileno']);
        $designation             = trim($request['designation']);
        $dateopen                = date('Y-m-d', strtotime(trim($request['dateopen'])));
        $divreg                  = trim($request['divreg']);
        $inout                   = trim($request['inout']);
        $volume                  = trim($request['volume']);
        $lastpage                = trim($request['lastpage']);
        $recipient               = trim($request['recipient']);
        $returndate              = date('Y-m-d', strtotime(trim($request['returndate'])));
        $purpose                 = trim($request['purpose']);
        $destination             = trim($request['destination']);
        $date                    = date("Y-m-d");
        //check if record exist
        $check_record = DB::table('openregistry')
            ->where('fileNo', '=', $fileno)
            ->first();

        if ($inout == "Incoming") {
            DB::table('openregistry')->insert(array(
                'FileNo'               => $fileno,
                'staffname'            => $fullName,
                'gender'               => $gender,
                'division'             => $divreg,
                'nameOfRecepient'      => $recipient,
                'Designation'          => $designation,
                'returnedDate'         => $returndate,
                'in_out'               => $inout,
                'volumes'              => $volume,
                'lastPageNumber'       => $lastpage,

                'dateOpen'             => $dateopen,
                'updated_at'           => $date
            ));
            $this->addLog('New Personal file Record has been Entered With Division: ' . $this->division);

            return redirect('/openregistry/create/')->with('msg', 'Operation was done successfully.');
        } elseif ($inout == "Outgoing") {
            DB::table('openregistry')->insert(array(
                'FileNo'               => $fileno,
                'staffname'            => $fullName,
                'gender'               => $gender,
                'division'             => $divreg,
                'nameOfRecepient'      => $recipient,
                'Designation'          => $designation,
                'purposeOfMovement'    => $purpose,
                'in_out'               => $inout,
                'volumes'              => $volume,
                'lastPageNumber'       => $lastpage,
                'destination'          => $destination,
                'dateOpen'             => $dateopen,
                'updated_at'           => $date
            ));
            $this->addLog('New Personal file Record has been Entered With Division: ' . $this->division);
        }

        return redirect('/openregistry/create/')->with('msg', 'Operation was done successfully.');
    }

    public function edit($id)
    {

        $data['divisions'] = DB::table('division_registry')->get();
        $data['registry'] = DB::table('openregistry')
            ->where('pfrID', '=', $id)
            ->first();
        //dd($data);
        return view('openRegistry.editout', $data);
    }

    public function update(Request $request)
    {
        $pfrid                 = trim($request['pfrid']);
        $fullName              = trim($request['staffname']);
        $gender                = trim($request['gender']);
        $fileno                = trim($request['fileno']);
        $designation           = trim($request['designation']);
        $dateopen              = date('Y-m-d', strtotime(trim($request['dateopen'])));
        $divreg                = trim($request['divreg']);
        $inout                 = trim($request['inout']);
        $volume                = trim($request['volume']);
        $lastpage              = trim($request['lastpage']);
        $recipient             = trim($request['recipient']);
        $returndate            = date('Y-m-d', strtotime(trim($request['returndate'])));
        $purpose               = trim($request['purpose']);
        $destination           = trim($request['destination']);
        $date                  = date("Y-m-d");
        if ($inout == "Outgoing") {
            DB::table('openregistry')->where('pfrID', '=', $pfrid)->update(array(
                'FileNo'            => $fileno,
                'staffname'         => $fullName,
                'gender'            => $gender,
                'division'          => $divreg,
                'nameOfRecepient'   => $recipient,
                'Designation'       => $designation,
                'destination'      => $destination,
                'in_out'            => $inout,
                'volumes'           => $volume,
                'lastPageNumber'    => $lastpage,
                'purposeOfMovement' => $purpose,
                'dateOpen'          => $dateopen,
                'updated_at'        => $date
            ));

            return redirect('/openregistry/editout/' . $pfrid . '')->with('msg', 'Operation was done successfully.');
        } elseif ($inout == "Incoming") {
            DB::table('openregistry')->where('pfrID', '=', $pfrid)->update(array(
                'FileNo'              => $fileno,
                'staffname'           => $fullName,
                'gender'              => $gender,
                'division'            => $divreg,
                'nameOfRecepient'     => $recipient,
                'Designation'         => $designation,
                'in_out'              => $inout,
                'volumes'             => $volume,
                'lastPageNumber'      => $lastpage,
                'dateOpen'            => $dateopen,
                'returnedDate'        => $returndate,
                'updated_at'          => $date
            ));
            $this->addLog('New Personal file Record has been Entered With Division: ' . $this->division);

            return redirect('/openregistry/editout/' . $pfrid . '')->with('msg', 'Operation was done successfully.');
        }
    }

    //Delete
    public function destroy($fileno)
    {
        //delete
        DB::table('openregistry')->where('fileNo', '=', $fileno)->delete();
        $this->addLog('Personal File Registry Record deleted and division: ' . $this->division);

        return redirect('/openregistry/create/')->with('msg', 'Operation was done successfully.');
    }


    //File Details
    public function newFile()
    {
        $data = [];
        try {
            $data['getCategory'] =  DB::table('tblfile_category')->get();
            $data['getVolume'] =  DB::table('tblvolume')->get();
            $data['files'] =  DB::table('tblfiles') //->where('tblfiles.file_category', '=', 2)
                ->leftJoin('tblvolume', 'tblvolume.ID', '=', 'tblfiles.volume')
                ->leftJoin('tblfile_category', 'tblfile_category.Id', '=', 'tblfiles.file_category')
                ->select('*', 'tblfiles.ID as file_ID')
                ->orderBy('tblfiles.ID', 'Desc')
                ->simplePaginate(7);
        } catch (\Throwable $e) {

        }
        return view('bulkFileMovement/createFile', $data);
    }


    //Get to created files page
    public function createdFiles()
    {
        $data = [];
        try {
            $data['getCategory'] =  DB::table('tblfile_category')->get();
            $data['getVolume'] =  DB::table('tblvolume')->get();
            $data['files'] =  DB::table('tblfiles') //->where('tblfiles.file_category', '=', 2)
                ->leftJoin('tblvolume', 'tblvolume.ID', '=', 'tblfiles.volume')
                ->leftJoin('tblfile_category', 'tblfile_category.Id', '=', 'tblfiles.file_category')
                ->select('*', 'tblfiles.ID as file_ID')
                ->orderBy('tblfiles.ID', 'Desc')
                ->simplePaginate(7);
        } catch (\Throwable $e) {

        }

        return view('bulkFileMovement/getCreatedFiles', $data);
    }

    //Get Record to edit
    public function editFile($id)
    {
        $data = [];
        try {
            $data['getCategory'] =  DB::table('tblfile_category')->get();
            $data['getVolume'] =  DB::table('tblvolume')->get();
            $data['editRecord'] =  DB::table('tblfiles')->where('tblfiles.ID', $id)
                ->leftJoin('tblvolume', 'tblvolume.ID', '=', 'tblfiles.volume')
                ->leftJoin('tblfile_category', 'tblfile_category.Id', '=', 'tblfiles.file_category')
                ->select('*', 'tblfiles.ID as file_ID')
                ->first();
        } catch (\Throwable $e) {
        }
        return view('bulkFileMovement/editFile', $data);
    }

    //Save New File
    public function saveNewFile(Request $request)
    {
        $is_saved = 0;
        $this->validate(
            $request,
            [
                'fileName'                 => 'required|unique:tblfiles,fileNo',
                'fileCategory'             => 'required',
                'volume'                   => 'required',
                'description'              => 'required',
                'shelfNo'                   => 'integer',
            ]
        );

        try {
            $is_saved = DB::table('tblfiles')->insertGetId(array(
                'fileNo'                              => $request['fileName'],
                'shelfNo'                              => $request['shelfNo'],
                'file_category'                       => $request['fileCategory'],
                'volume'                              => $request['volume'],
                'file_description'                    => $request['description'],
                'file_destination_section'            => 42,
            ));
        } catch (\Throwable $e) {

        }
        if ($is_saved) {
            return redirect()->back()->with('message', 'Your record was saved successfully');
        }
        return redirect()->back()->with('danger', 'Sorry, an error occurred when processing your record. Please try again.');
    }


    //Update File
    public function updateFile(Request $request)
    {
        $is_saved = 0;
        $this->validate(
            $request,
            [
                'fileName'                 => 'required',
                'shelfNo'                   => 'integer',
                'fileCategory'             => 'required',
                'volume'                   => 'required',
                'description'              => 'required',
                'recordID'                 => 'required'
            ]
        );
        try {
            $is_saved = DB::table('tblfiles')->where('ID', $request['recordID'])->update(array(
                'fileNo'                              => $request['fileName'],
                'shelfNo'                              => $request['shelfNo'],
                'file_category'                       => $request['fileCategory'],
                'volume'                              => $request['volume'],
                'file_description'                    => $request['description'],
                //'file_destination_section'            => 42,
            ));
        } catch (\Throwable $e) {
        }
        if ($is_saved) {
            return redirect('/created-files')->with('message', 'Your record was updated successfully');
        }
        return redirect('/created-files')->with('danger', 'Sorry, No update occurred or an error occurred when processing your record. Please try again.');
    }

    //delete file
    public function deleteFile(Request $request, $id)
    {
        $checkIfFileDocumentExist = DB::table('file_document')->where('fileID', $id)->exists();
        if($checkIfFileDocumentExist){
            return back()->with('danger', 'Document already exist for this File');
        }
        $delete = DB::table('tblfiles')->where('ID', $id)->delete();
        if ($delete) {
            return back()->with('message', 'The File has been deleted');
        }
        return back()->with('danger', 'Sorry, File could not be deleted');

    }

    public function review()
    {
        $data['files'] =  DB::table('tblfiles')->where('file_category', '=', 2)->get();
        return view('bulkFileMovement/reviewFiles', $data);
    }

    public function recall(Request $request)
    {
        $fileNo = $request->input('fileNo');

        $data = DB::table('tblbulk_file_movement')->where('fileNo', '=', $fileNo)->first();
        DB::table('tblfiles')->where('fileNo', '=', $fileNo)->update(array(
            'file_destination_section'                     => $data->origin_dept,
        ));

        // DB::table('tblbulk_file_movement')->where('fileNo', '=', $fileNo)->where('accepted_by', '=', 0)->where('transfered_by', '=', Auth::user()->id)->delete();
        DB::table('tblbulk_file_movement')->where('fileNo', '=', $fileNo)->where('transfered_by', '=', Auth::user()->id)->delete();
        return response()->json("Successfully Recalled");
    }

    public function viewDocuments($id, $vol)
    {
        $data['documents'] = DB::table('file_document')->where('fileID', '=', $id)->where('volumeID', '=', $vol)->get();
        return view('bulkFileMovement.viewDocument', $data);
    }
}
