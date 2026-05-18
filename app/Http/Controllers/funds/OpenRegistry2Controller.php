<?php

namespace App\Http\Controllers\funds;
use Illuminate\Http\Request;
use App\Http\Requests;
use Session;
use Auth;
use DB;


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
        return view('openFileRegistry2/clossingFile',$data);         
    }

    public function saveClosingFile(Request $request)
    {
         $this->validate($request, 
        [
        'fileNo'           => 'required|numeric',
        'staffName'        => 'required|string',
        'volume'           => 'required|string', 
        'purpose'          => 'required|string', 
        'dateClosed'       => 'required|date',            
        ]);
        $fileNo            = trim($request['fileNo']);
        $staffname         = trim($request['staffName']);
        $volume            = trim($request['volume']);
        $purpose           = trim($request['purpose']);
        $date              = date('Y-m-d', strtotime(trim($request['dateClosed'])));
        $user              = Auth::user()->id;
        $data['details'] = DB::table('closingfile')->insert(array( 
        'FileNo'           => $fileNo, 
        'fullname'         => $staffname, 
        'volume'           => $volume, 
        'purpose'          => $purpose,
        'created_at'       => $date,
        'userId'           => $user,
        ));
        return redirect('/open-file-registry/create')->with('msg',"File successussfully closed");
    }

    public function incomingLetterIndex()
    {

         $data['division'] = DB::table('tbldivision')->get();
         return view('openFileRegistry2/incomingLetter',$data);

    }

    public function saveIncomingLetter(Request $request)
    {
         $this->validate($request, 
        [
        'name'             => 'required|string',
        'detail'           => 'required|string', 
        'division'         => 'required|numeric',
        'date'             => 'required|date', 
                   
        ]);
        $name              = trim($request['name']);
        $detail            = trim($request['detail']);
        $division          = trim($request['division']);
        $date_recieved     = date('Y-m-d', strtotime(trim($request['date'])));
        $date              = date('Y-m-d');
        $data['insert']    = DB::table('incoming_letter')->insert(array( 
        'fullname'         => $name, 
        'details'          => $detail, 
        'division'         => $division,
        'date_recieved'    =>$date_recieved,
        'created_at'       => $date,
        ));
        return redirect('/open-file-registry/incoming-letter')->with('msg',"successfully saved");


    }

    public function outgoingLetterIndex()
    {

         $data['division'] = DB::table('tbldivision')->get();
         return view('openFileRegistry2/outgoingLetter',$data);

    }

    public function saveoutgoingLetter(Request $request)
    {
         $this->validate($request, 
        [
        'ownerName'        => 'required|string',
        'collectorName'    => 'required|string',
        'detail'           => 'required|string', 
        'phone'            => 'required|numeric', 
                   
        ]);
        $ownername         = trim($request['ownerName']);
        $detail            = trim($request['detail']);
        $phone             = trim($request['phone']);
        $collector         = trim($request['collectorName']);
        $date              = date('Y-m-d');
        $data['insert']    = DB::table('outgoing_letter')->insert(array( 
        'owner_name'       => $ownername, 
        'details'          => $detail,
        'collector_name'   => $collector,
        'phone'            => $phone,
        'created_at'       => $date,
        ));
        return redirect('/open-file-registry/outgoing-letter')->with('msg',"successfully saved");


    }
    public function mailIndex()
    {

         $data['division'] = DB::table('tbldivision')->get();
         return view('openFileRegistry2/mail',$data);

    }

    public function saveMail(Request $request)
    {
         $this->validate($request, 
        [
        'ownerName'        => 'required|string',
        'collectorName'    => 'required|string',
        'dateDispatched'   => 'required|date', 
        'dateRecieved'     => 'required|date', 
                   
        ]);
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
        return redirect('/open-file-registry/mail')->with('msg',"successfully saved");


    }
    public function viewMails()
    {
       $data['details']    = DB::table('mail')->paginate(15);
       return view('openFileRegistry2/viewmails',$data);
        
    }
       public function autocomplete(Request $request)
    {
        $query  = $request->input('query');
        $search = DB::table('mail')->where('owner_name', 'LIKE', '%'.$query.'%')->
            orWhere('collector_name', 'LIKE', '%'.$query.'%')->take(15)->get();
        $return_array = null;
        foreach($search as $s)
        {
          $return_array[]  =  ["value"=>$s->owner_name];
        }   
        return response()->json(array("suggestions"=>$return_array));
    }
    public function filter_mails(Request $request)
    {
        $filterBy = trim($request['q']); 
        if($filterBy == null){
        return redirect('/open-file-registry/view-mails')->with('err', 'No record found !');
        }
        $data['details'] = DB::table('mail')
                ->where('owner_name', 'LIKE', '%'.$filterBy.'%')
                ->orWhere('collector_name', 'LIKE', '%'.$filterBy.'%')
                ->paginate(20);
        return view('openFileRegistry2.viewmails', $data);
        
    }
     public function viewClosedFiles()
    {
       $data['details'] = DB::table('closingfile')
       ->join('users','users.id', '=', 'closingfile.userId')
       ->paginate(15);
       return view('openFileRegistry2/viewClosingFile',$data);
        
    }
    public function auto(Request $request)
    {
        $query  = $request->input('query');
        if($query == "")
        {
            return view('openFileRegistry2.viewClosingFile')->with('msg','No search item provided');
        }
        else
        {
        $search = DB::table('closingfile')
        ->where('fullname', 'LIKE', '%'.$query.'%')
        ->orWhere('fileNo', 'LIKE', '%'.$query.'%')
        ->take(15)->get();
        $return_array = null;
        foreach($search as $s)
        {
          $return_array[]  =  ["value"=>$s->fullname];
        } 
         return response()->json(array("suggestions"=>$return_array));
        }
        //return auto($query,'fullname','fileNo','closingfile');
    }
    public function filterClosedFiles(Request $request)
    {
         $this->validate($request, 
        [
        'q'        => 'required|string',                   
        ]);
        $filterBy = trim($request['q']); 
        if($filterBy == null){
        return redirect('/open-file-registry/view-closed-files')->with('err', 'No record found !');
        }
        $data['details'] = DB::table('closingfile')
        ->join('users','users.id', '=', 'closingfile.userId')
        ->where('fullname', 'LIKE', '%'.$filterBy.'%')
        ->orWhere('fileNo', 'LIKE', '%'.$filterBy.'%')
        ->paginate(20);
        return view('openFileRegistry2.viewClosingFile', $data);
    }

     public function viewOutgoing()
    {
       $data['details'] = DB::table('outgoing_letter')
       ->paginate(15);
       return view('openFileRegistry2/viewOutgoing',$data);
        
    }
    public function autocompleteOutgoing(Request $request)
    {
        $query  = $request->input('query'); 
        $search = DB::table('outgoing_letter')
        ->where('owner_name', 'LIKE', '%'.$query.'%')
        ->orWhere('collector_name', 'LIKE', '%'.$query.'%')
        ->take(15)->get();
        $return_array = null;
        foreach($search as $s)
        {
          $return_array[]  =  ["value"=>$s->owner_name];
        } 
         return response()->json(array("suggestions"=>$return_array));
        //return auto($query,'fullname','fileNo','closingfile');
    }
    public function filterOutgoing(Request $request)
    {
         $this->validate($request, 
        [
        'q'        => 'required|string',                   
        ]);
        $filterBy = trim($request['q']); 
        if($filterBy == null){
        return redirect('/open-file-registry/view-outgoing')->with('err', 'No record found !');
        }
        $data['details'] = DB::table('outgoing_letter')
        ->where('owner_name', 'LIKE', '%'.$filterBy.'%')
        ->orWhere('collector_name', 'LIKE', '%'.$filterBy.'%')
        ->paginate(20);
        return view('openFileRegistry2.viewOutgoing', $data);
    }

     public function viewIncoming()
    {
       $data['details'] = DB::table('incoming_letter')
       ->join('tbldivision','tbldivision.divisionID', '=', 'incoming_letter.division')
       ->paginate(15);
       return view('openFileRegistry2/viewIncoming',$data);
        
    }
    public function autocompleteIncoming(Request $request)
    {
        $query  = $request->input('query'); 
        $search = DB::table('incoming_letter')
        ->where('fullname', 'LIKE', '%'.$query.'%')
        ->take(15)->get();
        $return_array = null;
        foreach($search as $s)
        {
          $return_array[]  =  ["value"=>$s->fullname];
        } 
         return response()->json(array("suggestions"=>$return_array));
        //return auto($query,'fullname','fileNo','closingfile');
    }
    public function filterIncoming(Request $request)
    {
         $this->validate($request, 
        [
        'q'       => 'required|string',                   
        ]);
        $filterBy = trim($request['q']); 
        if($filterBy == null){
        return redirect('/open-file-registry/view-outgoing')->with('err', 'No record found !');
        }
        $data['details'] = DB::table('incoming_letter')
        ->join('tbldivision','tbldivision.divisionID', '=', 'incoming_letter.division')
        ->where('fullname', 'LIKE', '%'.$filterBy.'%')
        ->paginate(20);
        return view('openFileRegistry2.viewIncoming', $data);
    }



}
