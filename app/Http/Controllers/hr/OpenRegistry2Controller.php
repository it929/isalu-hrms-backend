<?php

namespace App\Http\Controllers\hr;

use DB;
use Auth;
use Session;
use Carbon\Carbon;
use App\Models\User;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Notifications\SentFile;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\File;


class OpenRegistry2Controller extends ParentController
{
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->division    = $request->session()->get('division');
        $this->divisionID  = $request->session()->get('divisionID');
                return $next($request);
        });
    }
    public function closingFileIndex()
    {
        $data['details'] = DB::table('division_registry')->get();
        $data['profile'] = DB::table('tblper')->where('divisionID', '=', $this->divisionID)->get();
        $data['closedVolumes'] = DB::table('closingfile')
            ->join('users', 'users.id', '=', 'closingfile.userId')
            ->join('tblper', 'tblper.ID', '=', 'closingfile.staffid')
            ->paginate(15);

        return view('openFileRegistry2/clossingFile', $data);
    }

    public function saveClosingFile(Request $request)
    {
        $this->validate(
            $request,
            [
                'fileNo'           => 'required',
                'staffName'        => 'required|string',
                'volume'           => 'required|string',
                'purpose'          => 'required|string',
                'dateClosed'       => 'required|date',
            ]
        );
        $fileNo            = trim($request['fileNo']);
        $staffname         = trim($request['staffName']);
        $volume            = trim($request['volume']);
        $purpose           = trim($request['purpose']);
        $date              = date('Y-m-d', strtotime(trim($request['dateClosed'])));
        $user              = Auth::user()->id;
        $data['details'] = DB::table('closingfile')->insert(array(
            'fileNo'           => $fileNo,
            'fullname'         => $staffname,
            'volume'           => $volume,
            'purpose'          => $purpose,
            'created_at'       => date('Y-m-d'),
            'userId'           => $user,
            'dateclosed'       => $date,
            'staffid'          => $request['staffid'],
        ));
        return redirect('/open-file-registry/create')->with('msg', "File successussfully closed");
    }

    public function updateClosingFile(Request $request)
    {
        $this->validate(
            $request,
            [
                'fileNo'           => 'required',
                'staffName'        => 'required|string',
                'volume'           => 'required|string',
                'purpose'          => 'required|string',
                'dateClosed'       => 'required|date',
                'notification'     => 'required'
            ]
        );
        $fileNo            = trim($request['fileNo']);
        $staffname         = trim($request['staffName']);
        $volume            = trim($request['volume']);
        $purpose           = trim($request['purpose']);
        $date              = date('Y-m-d', strtotime(trim($request['dateClosed'])));
        $user              = Auth::user()->id;
        $data['details'] = DB::table('closingfile')->where('staffid', '=', $request['staffid'])->update(array(
            'fileNo'           => $fileNo,
            'fullname'         => $staffname,
            'volume'           => $volume,
            'purpose'          => $purpose,
            'created_at'       => date('Y-m-d'),
            'userId'           => $user,
            'dateclosed'       => $date,
            'staffid'          => $request['staffid'],
        ));
        return redirect('/open-file-registry/create')->with('msg', "Updated");
    }


    public function incomingLetterIndex()
    {
        $user =  Auth::user()->id;
        $user = db::table('tblaction_stages')->where('userID', $user)->first();
        if ($user == null) {
            return back();
        }

        $data['loggedInUserName'] = DB::table('tblper')->where('UserID', Auth::user()->id)->first(['surname', 'first_name']);
        $department = $user->action_stageID;
        $department = 2;
        $data['userDepartment'] = $department;
        $data['files'] = db::table('tblfiles')->get();
        $data['division'] = DB::table('tbldivision')->get();
        $data['departments'] = DB::table('tbldepartment')->get();
        if ($department == 2 || $department == 3) {
            $data['details'] = DB::table('incoming_letter')
                ->leftjoin('tbldepartment', 'incoming_letter.departmentID', '=', 'tbldepartment.id')

                // ->join('tbldivision','tbldivision.divisionID', '=', 'incoming_letter.division')
                ->leftJoin('users', 'users.id', '=', 'incoming_letter.received_by')
                ->select('incoming_letter.*', 'tbldepartment.department as departmentName', 'users.*')
                ->orderBy('incoming_letter.Id', 'desc')
                ->paginate(25);
        } else {
            $data['details'] = DB::table('incoming_letter')
                ->where('incoming_letter.departmentID', $department)
                // ->join('tbldivision','tbldivision.divisionID', '=', 'incoming_letter.division')
                ->leftJoin('users', 'users.id', '=', 'incoming_letter.received_by')
                ->leftjoin('tbldepartment', 'incoming_letter.departmentID', '=', 'tbldepartment.id')
                ->select('incoming_letter.*', 'tbldepartment.department as departmentName', 'users.*')
                ->orderBy('incoming_letter.Id', 'desc')
                ->paginate(25);
        }

        foreach ($data['details'] as $attachments) {
            //dd($attachments->id);
            $to = Carbon::createFromFormat('Y-m-d', $attachments->kiv);

            $to->subDay($attachments->notification);
            $dt  = Carbon::now();

            $diff = $to->diffInDays($dt);

            if ($diff > 0 && $to > $dt) {
                $attachments->kivstatus = false;
            } else {
                $attachments->kivstatus = true;
            }
            $attachment = db::table('mailattachments')->where('mailID', $attachments->Id)->get();
            $attachments->attachments = $attachment;
        }

        return view('openFileRegistry2/incomingLetter', $data);
    }

    public function moveIncomingLetterIndex(Request $request)
    {
        $this->validate(
            $request,
            [
                'id'             => 'required',
                'departments'           => 'required|string',
                'designation'        => 'required|string',
                'dateOuts'           => 'required|string',
                'timeOuts'          => 'required|string',
                'comment'          => 'required|string',

            ]
        );


        // db::table('mailmovement')->where('mailID', '=', $request['id'])->update([
        //     'status' => 0,
        // ]);

        db::table('mailmovement')->insert([
            'mailID' => $request->id,
            'department' => $request->departments,
            'designation' => $request->designation,
            'recipient' => $request->moveRecipient,
            'timeOut' =>  trim($request['timeOuts']),
            'dateOut' => date('Y-m-d', strtotime(trim($request['dateOuts']))),
            'status' => 1
        ]);

        db::table('incoming_letter')->where('Id', '=', $request['id'])->update([
            'departmentID' => $request->departments,
            'timeOut' =>  trim($request['timeOuts']),
            'comment' => $request->comment,
            'dateOut' => date('Y-m-d', strtotime(trim($request['dateOuts']))),
        ]);

        $request->session()->flash('msg', 'Moved Successfully');
        return redirect('/open-file-registry/incoming-letter');
        // return view('openFileRegistry2/selectRecipient', $data);
    }

    //getDeptsDesignation
    public function getDepts($id)
    {
        $data = DB::table('tbldesignation')
            ->join('tbldepartment', 'tbldepartment.id', '=', 'tbldesignation.departmentID')
            ->select('*')
            ->where('departmentID', $id)
            ->get();

        return response()->json(['data' => $data]);
    }

    // getDeptID and retireve staffs under that department
    public function getRecipients($deptId)
    {
        $recipients = DB::table('tblper')->where('department', $deptId)
            ->get(['ID', 'UserID', 'fileNo', 'title', 'surname', 'first_name', 'othernames', 'Designation', 'department']);

        return response()->json($recipients);
    }


    public function selectmoveRecipient(Request $request)
    {

        $this->validate(
            $request,
            [
                'id'             => 'required',
                'user'           => 'required|string',
            ]
        );


        db::table('mailmovement')->where('movementID', '=', $request['id'])
            ->where('status', '=', 3)
            ->update([
                'status' => 1,
                'recipient' => $request->user
            ]);

        $recipient = db::table('tblper')->where('ID', $request->user)->first();
        //  $loggedUser = User::where('ID', $loggedPer->UserID)->first();

        $receiver = User::where('id', $recipient->UserID)->first();




        $receiver->notify(new SentFile($receiver, "open-file-registry/view-incoming"));

        return redirect('/open-file-registry/incoming-letter')->with('msg', 'Mail moved and recipient was added successfully');
    }

    public function saveIncomingLetter(Request $request)
    {
        $user =  Auth::user()->id;
        $user = db::table('tblper')->where('UserID', $user)->first();
        $department = $user->department;
        $department = 42;
        $this->validate(
            $request,
            [
                // 'name'             => 'required|string',
                'detail'           => 'required|string',
                // 'dateIn'           => 'required|string',
                'timeIn'           => 'required|string',
                // 'dateOut'           => 'required|string'
                // 'timeOut'          => 'required|string',
                'organization'     => 'required|string',
                'dateR'             => 'required|string',
                'kiv'               => 'required',
            ]
        );
        // $name              = trim($request['name']);
        $detail              = trim($request['detail']);
        $dateOut              = trim($request['dateOut']);
        $timeIn              = trim($request['timeIn']);
        $timeOut            = trim($request['timeOut']);
        $organization            = trim($request['organization']);
        $sender         = trim($request['sender']);
        $date_recieved     = date('Y-m-d', strtotime(trim($request['dateR'])));
        // dd($request->dateR);
        $date              = date('Y-m-d');

        //**************** Calculating the reminder date ***************\\

        $numOfDays = $request->notification;  //number of days to the reminder day
        $currentDay = date('d-M-Y', time());
        $reminderDate = date('d-M-Y', strtotime($currentDay . ' + ' . $numOfDays . ' days'));


        //***************************  END  *****************************\\

        $data['insert']    = DB::table('incoming_letter')->insertGetId(array(
            'fullname'         => Auth::user()->name,
            'details'          => $detail,
            'sender'         => $sender,
            'date_recieved'    => $date_recieved,
            'received_by'      => Auth::user()->id,
            'created_at'       => $date,
            'departmentID'     => 2,
            // 'dateOut' =>         date('Y-m-d', strtotime(trim($request['dateOut']))),
            'kiv' => date('Y-m-d', time()),
            'kiv_status' => $request['kiv'],  //takes value from the kiv inputs
            'notification' => $request->notification,
            'timeIn'            => $timeIn,
            'timeOut'           => $timeOut,
            'organization'      => $organization,
            'reminder_date'     => $reminderDate
        ));


        if ($request->hasfile('filenames')) {
            foreach ($request->file('filenames') as $key => $file) {

                $name = time() . '' . $key . '.' . $file->extension();

                $file->move(public_path('/mailattachmentfiles/'), $name);
                db::table('mailattachments')->insert([
                    'mailID' => $data['insert'],
                    'location' => $name,
                ]);

                $data[] = $name;
            }
        }


        //  $file= new File();
        //  $file->filenames=json_encode($data);
        //  $file->save();

        return redirect('/open-file-registry/incoming-letter')->with('msg', "successfully saved");
    }

    public function attachIncomingLetter(Request $request)
    {
        $user =  Auth::user()->id;
        $location = env('UPLOADPATHROOT', null) . '/documents';
        $this->validate(
            $request,
            [
                'file'             => 'required|string',
                'description'           => 'required|string',
                'attachment'           => 'required|'

            ]
        );
        $file              = trim($request['file']);
        $description              = trim($request['description']);
        $attachment          = trim($request['attachment']);
        $volume = db::table('tblfiles')->where('ID', $file)->first();
        $volume = $volume->volume;
        $insertID    = DB::table('file_document')->insertGetId(array(
            'fileID'         => $request->file,
            'volumeID'          => $volume,
            'userID'         => $user,
            'document_description'    => $request->description,
        ));


        if ($request->hasfile('attachment')) {
            $file = $request->attachment;
            $name = time() . '.' . $file->extension();

            $file->move($location, $name);
            db::table('file_document')->where('ID', $insertID)->update([
                'document_part' => $name
            ]);
        }


        //  $file= new File();
        //  $file->filenames=json_encode($data);
        //  $file->save();

        return redirect('/open-file-registry/incoming-letter')->with('msg', "Successfully attached");
    }



    public function removeAttachment($id)
    {
        $location = db::table('mailattachments')->where('attachmentID', $id)->get();
        $mailID = $location[0]->mailID;
        $location = $location[0]->location;
        $file_path = asset('mailattachmentfiles/') . $location;
        @unlink($file_path);  //TODO Added @ to unlink
        db::table('mailattachments')->where('attachmentID', $id)->delete();
        return back()->with('msg', 'The selected attachement was removed !!');
    }
    public function updateIncomingLetter(Request $request)
    {
        $this->validate(
            $request,
            [
                'name'             => 'required|string',
                'detail'           => 'required|string',

                // 'dateOut'           => 'required|string',
                'timeIns'           => 'required|string',
                // 'timeOut'          => 'required|string',
                'organizations'     => 'required|string',
                'date'             => 'required|date',
                'kiv'        => 'required',
            ]
        );
        $name              = trim($request['name']);
        $detail            = trim($request['detail']);
        $sender         = trim($request['sender']);
        $dateOut              = trim($request['dateOuts']);
        $timeIn              = trim($request['timeIns']);
        $timeOut            = trim($request['timeOuts']);
        // dd($timeOut);
        $organization            = trim($request['organizations']);
        $date_recieved     = date('Y-m-d', strtotime(trim($request['date'])));
        $date              = date('Y-m-d');

        $data['insert']    = DB::table('incoming_letter')->where('Id', '=', $request['id'])->update(array(
            'fullname'         => $name,
            'details'          => $detail,
            'sender'         => $sender,
            'date_recieved'    => $date_recieved,
            'received_by'      => Auth::user()->id,
            'created_at'       => $date,
            // 'dateOut' =>         date('Y-m-d', strtotime(trim($request['dateOut']))),
            'timeIn'            => $timeIn,
            'timeOut'           => $timeOut,
            'organization'      => $organization,
            'kiv' => date('Y-m-d', time()),
            'kiv_status' => $request['kiv'],  //takes values from the kiv inputs
            'notification' => $request->notification,

        ));
        if ($request->hasfile('filenames')) {

            foreach ($request->file('filenames') as $key => $file) {

                $name = time() . '' . $key . '.' . $file->extension();

                $file->move(public_path('/mailattachmentfiles/'), $name);
                db::table('mailattachments')->insert([
                    'mailID' => $request['id'],
                    'location' => $name,
                ]);

                $data[] = $name;
            }
        }
        return redirect('/open-file-registry/incoming-letter')->with('msg', "successfully saved");
    }

    public function outgoingLetterIndex()
    {

        $data['division'] = DB::table('tbldivision')->get();
        $data['details'] = DB::table('outgoing_letter')
            ->paginate(15);
        return view('openFileRegistry2/outgoingLetter', $data);
    }

    public function saveoutgoingLetter(Request $request)
    {
        $this->validate(
            $request,
            [
                'sender'           => 'required|string',
                'recipient'        => 'required|string',
                'detail'           => 'required|string',
                'phone'            => 'required|numeric',

            ]
        );
        $ownername         = trim($request['sender']);
        $detail            = trim($request['detail']);
        $phone             = trim($request['phone']);
        $collector         = trim($request['recipient']);
        $date              = date('Y-m-d');
        $data['insert']    = DB::table('outgoing_letter')->insert(array(
            'owner_name'       => $ownername,
            'details'          => $detail,
            'collector_name'   => $collector,
            'phone'            => $phone,
            'created_at'       => $date,
            //'received_by'      => Auth::user()->id,
        ));
        return redirect('/open-file-registry/outgoing-letter')->with('msg', "successfully saved");
    }

    public function updateOutgoingLetter(Request $request)
    {
        $this->validate(
            $request,
            [
                'sender'           => 'required|string',
                'recipient'        => 'required|string',
                'detail'           => 'required|string',
                'phone'            => 'required|numeric',

            ]
        );
        $ownername         = trim($request['sender']);
        $detail            = trim($request['detail']);
        $phone             = trim($request['phone']);
        $collector         = trim($request['recipient']);
        $date              = date('Y-m-d');
        $data['insert']    = DB::table('outgoing_letter')->where('id', '=', $request['id'])->update(array(
            'owner_name'       => $ownername,
            'details'          => $detail,
            'collector_name'   => $collector,
            'phone'            => $phone,
            'created_at'       => $date,
            //'received_by'      => Auth::user()->id,
        ));
        return redirect('/open-file-registry/outgoing-letter')->with('msg', "successfully Updated");
    }

    public function mailIndex()
    {

        $data['division'] = DB::table('tbldivision')->get();
        return view('openFileRegistry2/mail', $data);
    }

    public function saveMail(Request $request)
    {
        $this->validate(
            $request,
            [
                'ownerName'        => 'required|string',
                'collectorName'    => 'required|string',
                'dateDispatched'   => 'required|date',
                'dateRecieved'     => 'required|date',

            ]
        );
        $ownername         = trim($request['ownerName']);
        $dateDispatched    = date('Y-m-d', strtotime(trim($request['dateDispatched'])));
        $dateRecieved      = date('Y-m-d', strtotime(trim($request['dateRecieved'])));
        $collector         = trim($request['collectorName']);
        $date              = date('Y-m-d');
        $data['insert']    = DB::table('mail')->insert(array(
            'owner_name'       => $ownername,
            'date_dispatched'  => $dateDispatched,
            'collector_name'   => $collector,
            'date_recieved'    => $dateRecieved,
            'created_at'       => $date,
        ));
        return redirect('/open-file-registry/mail')->with('msg', "successfully saved");
    }
    public function viewMails()
    {
        $data['details']    = DB::table('mail')->paginate(15);
        return view('openFileRegistry2/viewmails', $data);
    }
    public function autocomplete(Request $request)
    {
        $query  = $request->input('query');
        $search = DB::table('mail')->where('owner_name', 'LIKE', '%' . $query . '%')->orWhere('collector_name', 'LIKE', '%' . $query . '%')->take(15)->get();
        $return_array = null;
        foreach ($search as $s) {
            $return_array[]  =  ["value" => $s->owner_name];
        }
        return response()->json(array("suggestions" => $return_array));
    }
    public function filter_mails(Request $request)
    {
        $filterBy = trim($request['q']);
        if ($filterBy == null) {
            return redirect('/open-file-registry/view-mails')->with('err', 'No record found !');
        }
        $data['details'] = DB::table('mail')
            ->where('owner_name', 'LIKE', '%' . $filterBy . '%')
            ->orWhere('collector_name', 'LIKE', '%' . $filterBy . '%')
            ->paginate(20);
        return view('openFileRegistry2.viewmails', $data);
    }
    public function viewClosedFiles()
    {
        $data['details'] = DB::table('closingfile')
            ->join('users', 'users.id', '=', 'closingfile.userId')
            ->paginate(15);
        return view('openFileRegistry2/viewClosingFile', $data);
    }
    public function auto(Request $request)
    {
        $query  = $request->input('query');
        if ($query == "") {
            return view('openFileRegistry2.viewClosingFile')->with('msg', 'No search item provided');
        } else {
            $search = DB::table('closingfile')
                ->where('fullname', 'LIKE', '%' . $query . '%')
                ->orWhere('fileNo', 'LIKE', '%' . $query . '%')
                ->take(15)->get();
            $return_array = null;
            foreach ($search as $s) {
                $return_array[]  =  ["value" => $s->fullname];
            }
            return response()->json(array("suggestions" => $return_array));
        }
        //return auto($query,'fullname','fileNo','closingfile');
    }
    public function filterClosedFiles(Request $request)
    {
        $this->validate(
            $request,
            [
                'q'        => 'required|string',
            ]
        );
        $filterBy = trim($request['q']);
        if ($filterBy == null) {
            return redirect('/open-file-registry/view-closed-files')->with('err', 'No record found !');
        }
        $data['details'] = DB::table('closingfile')
            ->join('users', 'users.id', '=', 'closingfile.userId')
            ->where('closingfile.fullname', 'LIKE', '%' . $filterBy . '%')
            ->orWhere('closingfile.fileNo', 'LIKE', '%' . $filterBy . '%')
            ->paginate(20);
        return view('openFileRegistry2.viewClosingFile', $data);
    }

    public function viewOutgoing()
    {
        $data['details'] = DB::table('outgoing_letter')
            ->paginate(15);
        return view('openFileRegistry2/viewOutgoing', $data);
    }
    public function autocompleteOutgoing(Request $request)
    {
        $query  = $request->input('query');
        $search = DB::table('outgoing_letter')
            ->where('owner_name', 'LIKE', '%' . $query . '%')
            ->orWhere('collector_name', 'LIKE', '%' . $query . '%')
            ->take(15)->get();
        $return_array = null;
        foreach ($search as $s) {
            $return_array[]  =  ["value" => $s->owner_name];
        }
        return response()->json(array("suggestions" => $return_array));
        //return auto($query,'fullname','fileNo','closingfile');
    }
    public function filterOutgoing(Request $request)
    {
        $this->validate(
            $request,
            [
                'q'        => 'required|string',
            ]
        );
        $filterBy = trim($request['q']);
        if ($filterBy == null) {
            return redirect('/open-file-registry/view-outgoing')->with('err', 'No record found !');
        }
        $data['details'] = DB::table('outgoing_letter')
            ->where('owner_name', 'LIKE', '%' . $filterBy . '%')
            ->orWhere('collector_name', 'LIKE', '%' . $filterBy . '%')
            ->paginate(20);
        return view('openFileRegistry2.viewOutgoing', $data);
    }

    public function viewIncoming()
    {
        $staffID = DB::table('tblper')->where('userID', Auth::user()->id)->first();

        $data['details'] = DB::table('incoming_letter')
            // ->join('tbldivision','tbldivision.divisionID', '=', 'incoming_letter.division')
            ->leftJoin('users as logger', 'logger.id', '=', 'incoming_letter.received_by')
            ->leftJoin('mailmovement', 'mailID', '=', 'incoming_letter.id')
            ->leftJoin('tblper', 'tblper.id', '=', 'mailmovement.recipient')
            ->leftJoin('users as receiver', 'receiver.id', '=', 'tblper.UserID')
            ->where('mailmovement.recipient', $staffID->ID)
            ->select('incoming_letter.*', 'logger.name as name')
            ->paginate(25);

        //  dd($data);
        return view('openFileRegistry2/viewIncoming', $data);
    }
    public function autocompleteIncoming(Request $request)
    {
        $query  = $request->input('query');
        $search = DB::table('incoming_letter')
            ->where('fullname', 'LIKE', '%' . $query . '%')
            ->take(15)->get();
        $return_array = null;
        foreach ($search as $s) {
            $return_array[]  =  ["value" => $s->fullname];
        }
        return response()->json(array("suggestions" => $return_array));
        //return auto($query,'fullname','fileNo','closingfile');
    }
    public function filterIncoming(Request $request)
    {
        $this->validate(
            $request,
            [
                'q'       => 'required|string',
            ]
        );
        $filterBy = trim($request['q']);
        if ($filterBy == null) {
            return redirect('/open-file-registry/view-outgoing')->with('err', 'No record found !');
        }
        $data['details'] = DB::table('incoming_letter')
            //->join('tbldivision','tbldivision.divisionID', '=', 'incoming_letter.division')
            ->leftJoin('users', 'users.id', '=', 'incoming_letter.received_by')
            ->where('incoming_letter.fullname', 'LIKE', '%' . $filterBy . '%')
            ->paginate(20);
        return view('openFileRegistry2.viewIncoming', $data);
    }
}
