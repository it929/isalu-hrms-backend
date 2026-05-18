<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use DB;
use Auth;
class BulkFileMovementController extends Controller
{
     public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->division    = $request->session()->get('division');
        $this->divisionID  = $request->session()->get('divisionID');
                return $next($request);
        });
    }

    public function create()
    {
         
       //dd($data);
        $data['details']    = DB::table('division_registry')->get();
        $data['department'] = DB::table('tbldepartment')->get();
        //$data['recipient']  = DB::table('users')->where('user_section','=','hr')->get();
        $data['recipient']  = DB::table('users')->get();
        return view('bulkFileMovement.create',$data);
    }  

    public function getStaff(Request $request)
    {
        $fileNo=$request->input('nameID');
        DB::enableQueryLog();

         $check = DB::table('tblbulk_select_log')->where('fileNo','=', $fileNo)->count();
         if($check > 0)
         {
            return response()->json($check);
         }
         else
         {
            $data = DB::table('tblbulk_select_log')->insert(array( 
            'fileNo'         => $fileNo, 
            'date'           => date('Y-m-d'),
            
           ));
       
        return response()->json($data);
         }
    }

    public function getUsers(Request $request)
    {
        $sectionID=$request['sectionID'];
        
        $data = DB::table('tblper')->where('department','=',$sectionID)->where('staff_status','=',1)->get();
        return response()->json($data);
        
    }

     public function tempGet()
    {
      $data = DB::table('tblfiles')
            ->Join('tblbulk_select_log', 'tblbulk_select_log.fileNo', '=', 'tblfiles.fileNo')
            //->where('fileNo', '=', $term)
            // ->where('file_destination_section', '=', 2)
            //->select('fileNo','surname', 'first_name', 'othernames', 'Designation') 
            ->get();
       
        return response()->json($data);
        
    }

    public function deleteTemp(Request $request)
    {
        $fileNo = $request['fileNo'];
        DB::table('tblbulk_select_log')->where('fileNo','=',$fileNo)->delete();
        return response()->json("Deleted");
    }


     public function saveBulk(Request $request)
    {
           $this->validate($request, 
        [
            'fileNo'          => 'required',
            'volume'          => 'required',
            'lastPage'        => 'required',
            'recipient'       => 'required',
            'destination'     => 'required',
        ]);

        $fileNo            = $request['fileNo'];
        $volume            = $request['volume'];
        $lastpage          = $request['lastPage'];
        

        $recipient         = $request['recipient'];
        $purpose           = $request['purpose'];
        $destination       = $request['destination'];
        $returndate        = $request['returnDate'];
        $date              = date("Y-m-d h:i:s");
        
         
        DB::table('tblbulk_select_log')->delete();
          foreach ($fileNo as $key => $value) {
            $check = DB::table('tblbulk_file_movement')->where('fileNo','=', $fileNo)->count();
            /*if($check > 0)
            {
              return null;
            }
            else
            {*/
 $fromDept = DB::table('tblfiles')
         //->join('tbldepart','tbldepartment.id','=', 'tblfiles.file_destination_section')
         ->where('fileNo','=', $request['fileNo'][$key])->first();
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
            'status_description'      => 'Pending Acceptance',
            'transfered_by'           => Auth::user()->id,
            'date_transfered'         => $date,
            'date_accepted'           => $date,
            
           ));

           DB::table('tblfiles')->where('fileNo','=',$request['fileNo'][$key])->update(array( 

            'file_destination_section'        => $destination,
                        
           ));
         }
      //}
          return redirect('/bulk-movement/create')->with('msg','successfully Entered');
    }



    public function confirm(Request $request)
    {
        $fileNo = $request->input('fileNo'); 
        $value = $request->input('value');

        if($value == 'confirm') 
        {
        $check = DB::table('tblbulk_file_movement')
        ->where('fileNo', '=', $fileNo)
        ->first();
       
           DB::table('tblbulk_file_movement')->where('fileNo', '=', $fileNo) ->update(array( 
                'status'                     => 1,
                'status_description'         => "File Accepted",
                'accepted_by'                => Auth::user()->id, 
                ));
          return response()->json("Successfully Accepted"); 
        }
        elseif($value == 'reject') 
        {
             DB::table('tblbulk_file_movement')->where('fileNo', '=', $fileNo) ->update(array( 
                'status'                     => 4,
                'status_description'         => "File Rejected",
               
                ));
          return response()->json("Successfully Rejected"); 
        }
                       
    }

     public function cancel(Request $request)
    {
        $fileNo = $request->input('fileNo'); 
        DB::table('tblfiles')->where('fileNo', '=', $fileNo) ->update(array( 
                'file_destination_section'                     => 2,
                ));
        
           DB::table('tblbulk_file_movement')->where('fileNo', '=', $fileNo)->where('status', '=', 4)->where('transfered_by', '=', Auth::user()->id)->delete();
          return response()->json("Successfully Cancelled");              
    }


   public function acceptance()
   {
    $logedin_user = Auth::user()->id;
    $data['user'] = DB::table('users')
    ->where('id','=',$logedin_user)
    ->first();
    $data['authUser'] = Auth::user()->id;
     $data['acceptance_view'] = DB::table('tblbulk_file_movement')
     ->join('tblfiles','tblfiles.fileNo','=','tblbulk_file_movement.fileNo')
     ->where('tblbulk_file_movement.status','=',1)
     ->orWhere('tblbulk_file_movement.status','=',0)
     ->orWhere('tblbulk_file_movement.transfered_status','=',0)
     ->orderBy('tblbulk_file_movement.bulkID')
     ->get();
    return view('bulkFileMovement/accept',$data);
   }


    public function transfer()
   {
    $data['details']    = DB::table('division_registry')->get();
    $data['department'] = DB::table('tbldepartment')->get();
    //$data['recipient']  = DB::table('users')->where('user_section','=','hr')->get();
    $data['recipient']  = DB::table('users')->get();
    
    $logedin_user = Auth::user()->id;
    $data['user'] = DB::table('users')
    ->where('id','=',$logedin_user)
    ->first();
    $data['authUser']        = Auth::user()->id;
    $data['acceptance_view'] = DB::table('tblbulk_file_movement')
     ->join('tblfiles','tblfiles.fileNo','=','tblbulk_file_movement.fileNo')
     ->join('tbldepartment','tbldepartment.id','=','tblbulk_file_movement.destination')
     ->where('status','=',1)
     ->where('transfered_status','=',0)
     ->where('status_description', '=', 'File Accepted')
     //->orWhere('status','=',0)
     ->get();
     $data['count'] = DB::table('tblbulk_file_movement')
     ->join('tblfiles','tblfiles.fileNo','=','tblbulk_file_movement.fileNo')
     ->join('tbldepartment','tbldepartment.id','=','tblbulk_file_movement.destination')
     ->where('status','=',1)
     ->where('transfered_status','=',0)
     ->where('status_description', '=', 'File Accepted')
     ->orWhere('status','=',0)
     ->count();

     $data['transfered_files'] = DB::table('tblbulk_file_movement')
     ->join('tblfiles','tblfiles.fileNo','=','tblbulk_file_movement.fileNo')
     ->join('tbldepartment','tbldepartment.id','=','tblbulk_file_movement.destination')
     ->where('transfered_status','=', 3)
     //->selectRaw('')

     //->where('recipient','=',Auth::user()->id)
     // ->orWhere('status','=',0)
     //->orWhere('transfered_status','=',0)
     ->get();

     return view('bulkFileMovement.transfer',$data);
   }

    public function trackFile()
   {
     $data['section'] = DB::table('tbldepartment')->get();
   
     $data['transfered_files'] = DB::table('tblfiles')
     ->join('tblbulk_file_movement','tblbulk_file_movement.fileNo','=','tblfiles.fileNo')
     ->join('tbldepartment','tbldepartment.id','=','tblfiles.file_destination_section')
     ->where('tblfiles.file_destination_section','!=', 40)
     ->selectRaw('tblfiles.file_description, tblfiles.fileNo, tbldepartment.department, tblbulk_file_movement.volume,tblbulk_file_movement.origin_dept, tblbulk_file_movement.last_page, tblbulk_file_movement.date_transfered')
     
     ->get();
     
     dd($data['transfered_files'] );

     return view('bulkFileMovement.track',$data);
   }

    public function postTrackFile(Request $request)
   {
       if ($request->isMethod('get')) {
    return redirect('/bulk-transfer/track');
    }
    $data['section'] = DB::table('tbldepartment')->get();
   $from        = $request['from'];
    $to         = $request['to'];

    $fromDate        = date('Y-m-d', strtotime(trim($request['from'])));
    $toDate          = date('Y-m-d', strtotime(trim($request['to'])));
    $fileNo          = $request['staffNo'];
    $section         = $request['section'];
    //dd($toDate);

    if($section != '' && $from == '' && $to =='' && $fileNo == '')
   {
       //dd('yrtyrt7y1');
     $data['transfered_files'] = DB::table('tblfiles')
     ->join('tblbulk_file_movement','tblbulk_file_movement.fileNo','=','tblfiles.fileNo')
     ->join('tbldepartment','tbldepartment.id','=','tblfiles.file_destination_section')
     ->where('tblfiles.file_destination_section','=', $section)
     //->selectRaw('tblfiles.file_description, tblper.fileNo, tbldepartment.department, tblbulk_file_movement.volume, tblbulk_file_movement.last_page, tblbulk_file_movement.date_transfered')
     ->get();
    // dd($data['transfered_files']);
     return view('bulkFileMovement.track',$data);

   }
   
   elseif($from != '' && $to !='' && $section == '' && $fileNo == '')
   {
        //dd('yrtyrt7y2');
     $data['transfered_files'] = DB::table('tblfiles')
     ->join('tblbulk_file_movement','tblbulk_file_movement.fileNo','=','tblfiles.fileNo')
     ->join('tbldepartment','tbldepartment.id','=','tblfiles.file_destination_section')
     ->where('tblfiles.file_destination_section','!=', 40)
     ->whereBetween('date_transfered', [$fromDate, $toDate])
     //->selectRaw('tblper.first_name, tblper.surname, tblper.othernames, tblper.fileNo, tblsection.section, tblbulk_file_movement.volume, tblbulk_file_movement.last_page, tblbulk_file_movement.date_transfered')
     ->get();
     //dd($fromDate);
     return view('bulkFileMovement.track',$data);

   }

    elseif($fileNo != '' && $from == '' && $to =='' && $section == '')
   {
        //dd('yrtyrt7y3');
    $data['transfered_files'] = DB::table('tblfiles')
     ->join('tblbulk_file_movement','tblbulk_file_movement.fileNo','=','tblfiles.fileNo')
     ->join('tbldepartment','tbldepartment.id','=','tblfiles.file_destination_section')
     ->where('tblfiles.fileNo','=', $fileNo)
     ->where('tblfiles.file_destination_section','!=', 40)
    // ->selectRaw('tblper.first_name, tblper.surname, tblper.othernames, tblper.fileNo, tblsection.section, tblbulk_file_movement.volume, tblbulk_file_movement.last_page, tblbulk_file_movement.date_transfered')
     ->get();
     //dd($data['transfered_files'] );
     return view('bulkFileMovement.track',$data);

   }

    elseif($fileNo != '' && $from != '' && $to !='' && $section != '')
   {
        //dd('yrtyrt7y3');
     $data['transfered_files'] = DB::table('tblfiles')
     ->join('tblbulk_file_movement','tblbulk_file_movement.fileNo','=','tblfiles.fileNo')
     ->join('tbldepartment','tbldepartment.id','=','tblfiles.file_destination_section')
     ->where('tblfiles.fileNo','=', $fileNo)
     ->whereBetween('date_transfered', [$fromDate, $toDate])
     ->where('tblfiles.file_destination_section','=', $section)
     ->where('tblfiles.file_destination_section','!=', 40)
    // ->selectRaw('tblper.first_name, tblper.surname, tblper.othernames, tblper.fileNo, tblsection.section, tblbulk_file_movement.volume, tblbulk_file_movement.last_page, tblbulk_file_movement.date_transfered')
     ->get();
     //dd($data['transfered_files'] );
     return view('bulkFileMovement.track',$data);

   }
   elseif($fileNo == '' && $from != '' && $to !='' && $section != '')
   {
        //dd('yrtyrt7y3');
     $data['transfered_files'] = DB::table('tblfiles')
     ->join('tblbulk_file_movement','tblbulk_file_movement.fileNo','=','tblfiles.fileNo')
     ->join('tbldepartment','tbldepartment.id','=','tblfiles.file_destination_section')
     ->whereBetween('date_transfered', [$fromDate, $toDate])
     ->where('tblfiles.file_destination_section','=', $section)
     ->where('tblfiles.file_destination_section','!=', 40)
    // ->selectRaw('tblper.first_name, tblper.surname, tblper.othernames, tblper.fileNo, tblsection.section, tblbulk_file_movement.volume, tblbulk_file_movement.last_page, tblbulk_file_movement.date_transfered')
     ->get();
     //dd($data['transfered_files'] );
     return view('bulkFileMovement.track',$data);

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
        $recipient         = $request['recipient'];
        $purpose           = $request['purpose'];
        $destination       = $request['destination'];
        $returndate        = $request['returnDate'];
        $date              = date("Y-m-d h:i:s");

      
          foreach ($check as $key => $value) {
           // $countRows = DB::table('scores')->where('studentId','=',$request['student'][$val])->count();
            //if($countRows > 0)
            //{
            $fromDept = DB::table('tblbulk_file_movement')
         ->where('fileNo','=', $request['fileNo'][$key])->first();
          DB::table('tblbulk_file_movement')->insert(array( 
            'fileNo'                  => $request['fileNo'][$key], 
            'volume'                  => $request['volume'][$key], 
            'last_page'               => $request['lastPage'][$key],
            'recipient'               => $recipient,
            'purpose'                 => $purpose,
            'origin_dept'             => $fromDept->origin_dept,
            'destination'             => $destination,
            'return_date'             => $returndate,
            'status'                  => 0,
            'transfered_by'           => Auth::user()->id,
            'status_description'      => 'Pending Acceptance',
            'date_transfered'         => $date,
            'transfered_status'       => 3,
             
           ));

           DB::table('tblbulk_file_movement')->where('bulkID','=',$id[$key])->update(array( 
           
            'status'                  => 2,
            'transfered_status'       => 1,
            
           ));

           DB::table('tblfiles')->where('fileNo','=',$request['fileNo'][$key])->update(array( 

            'file_destination_section'        => $destination,
                        
           ));

      }
          return redirect('/bulk-transfer/move')->with('message','successfully Entered');
    } 
    

   }

   public function filesSent()
   {
    $logedin_user = Auth::user()->id;
    $data['user'] = DB::table('users')
    ->where('id','=',$logedin_user)
    ->first();
    $data['authUser'] = Auth::user()->id;
     $data['sent_files'] = DB::table('tblbulk_file_movement')
     ->join('tblfiles','tblfiles.fileNo','=','tblbulk_file_movement.fileNo')
     ->where('tblbulk_file_movement.transfered_by','=', Auth::user()->id)
     
     ->orderBy('bulkID')
     //->orWhere('transfered_status','=',0)
     ->get();
    return view('bulkFileMovement/filesTransfered',$data);
   }


    public function filter_staff(Request $request)
    {
        $filterBy = trim($request['fileNo']);
        $filterDivision = trim($request['filterDivision']); 
        
        if($filterDivision == "" && $filterBy == "")
        {
           
                return redirect('/staff-report/view');
        }
        if($filterDivision == "")
        {
            $data['users'] = DB::table('tblfiles')
                    ->Join('tbldivision', 'tblfiles.divisionID', '=', 'tbldivision.divisionID')
                    ->where('tblfiles.file_description', 'LIKE', '%'.$filterBy.'%')
                    //->orWhere('tblper.first_name', 'LIKE', '%'.$filterBy.'%')
                    ->orWhere('tblper.fileNo', '=',$filterBy)
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
        }
        else if($filterDivision != "")
        {
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
        }else{
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

        return view('openRegistry.create',$data);
    }

       public function autocomplete(Request $request)
    {
        $query = $request->input('query');
        $search = DB::table('tblfiles')->where('file_description', 'LIKE', '%'.$query.'%')
        //->orWhere('first_name', 'LIKE', '%'.$query.'%')
            ->orWhere('fileNo', 'LIKE','%'.$query.'%')->take(15)
            ->where('file_destination_section', '=', 40)->get();
        $return_array = null;
        foreach($search as $s)
        {
          $return_array[]  =  ["value"=>$s->file_description.' - '.$s->fileNo, "data"=>$s->fileNo];
        }   
        return response()->json(array("suggestions"=>$return_array));
    }



    public function showAll(Request $request)
    {
        $term=$request->input('nameID');
        DB::enableQueryLog();
        $data = DB::table('tblfiles')
            ->leftJoin('tbldivision', 'tblfiles.divisionID', '=', 'tbldivision.divisionID')
            ->where('tblfiles.fileNo', '=', $term)
            ->select('fileNo','surname', 'first_name', 'othernames', 'fileNo', 'division','Designation','gender') 
            ->get();
        return response()->json($data);
    } 
    public function personalFileData(Request $request)
    {
        $filenum=$request->input('fileno');
        DB::enableQueryLog();
        $data = DB::table('openregistry')
            ->where('fileNo', '=', $filenum)
            ->select('FileNo','staffname','gender','division','nameOfRecepient','Designation','returnedDate','in_out','volumes','lastPageNumber','dateOpen','destination','purposeOfMovement') 
            ->get();
        return response()->json($data);
    } 

    public function store(Request $request)
    {
              
        $this->validate($request, 
        [
            'staffname'          => 'required',
            'gender'             => 'required',
            'fileno'             => 'required|string',
            'designation'        => 'required',
        ]);
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
    
        if($inout == "Incoming")
        {
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
                
    }
    elseif($inout == "Outgoing")
    {
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
                        ->where('pfrID','=',$id)
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
        if($inout == "Outgoing")
        {
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
       
    return redirect('/openregistry/editout/'.$pfrid.'')->with('msg', 'Operation was done successfully.');        
      
    }
    elseif($inout == "Incoming")
    {
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

        return redirect('/openregistry/editout/'.$pfrid.'')->with('msg', 'Operation was done successfully.');
    }

    }

    public function destroy($fileno)
    {
        //delete
        DB::table('openregistry')->where('fileNo', '=', $fileno)->delete();
        $this->addLog('Personal File Registry Record deleted and division: ' . $this->division);
        
        return redirect('/openregistry/create/')->with('msg', 'Operation was done successfully.');
    }
    
   /* public function copy()
    {
      $db = DB::table('tblper')->where('staff_status','=',1)->get();
      //dd($db);
      foreach($db as $list)
      {
      $name = $list->surname.' '.$list->first_name.' '.$list->othernames;
     // dd($name);
       DB::table('tblfiles')->insert(array( 
        'FileNo'                              => $list->fileNo, 
        'file_description'                    => $name, 
        'file_category'                       => 1, 
        'file_destination_section'            => 42,
        
        
        ));
       
      }
       dd("Successfull");
    }
    */
    public function newFile()
    {
    $data['cat'] =  DB::table('tblfile_category')->get();
    return view('bulkFileMovement/createFile',$data);
    }
     public function saveNewFile(Request $request)
    {
     $this->validate($request, 
        [
            'fileNo'                   => 'required',
            'fileName'                 => 'required',
            'fileCategory'             => 'required',
            'volume'                   => 'required',
            'lastPage'                => 'required',
        ]);
    DB::table('tblfiles')->insert(array( 
        'fileNo'                              => $request['fileNo'], 
        'file_description'                    => $request['fileName'], 
        'file_category'                       => $request['fileCategory'],
        'file_destination_section'            => 42,
        'volume'                              => $request['volume'],
        'last_page'                           => $request['lastPage'],

        ));
    return redirect('/add/new-file')->with('msg','Successfully Entered');
    }
    
      public function review()
    {
    $data['files'] =  DB::table('tblfiles')->where('file_category','=',2)->get();
    return view('bulkFileMovement/reviewFiles',$data);
    }
    
     public function editFile($id)
    {
    $data['cat'] =  DB::table('tblfile_category')->get();
    $data['file'] =  DB::table('tblfiles')->where('ID','=',$id)->first();
    return view('bulkFileMovement/editFile',$data);
    }
    
     public function updateFile(Request $request)
    {
    $id = $request['id'];
        DB::table('tblfiles')->where('ID','=',$id)->update(array( 
        'fileNo'                              => $request['fileNo'], 
        'file_description'                    => $request['fileName'], 
        'file_category'                       => $request['fileCategoty'],
        //'file_destination_section'            => 42,

        ));
    return redirect("/edit/file/$id")->with('msg','Successfully updated');
    }
    
     public function recall(Request $request)
    {
        $fileNo = $request->input('fileNo'); 
        
        $data = DB::table('tblbulk_file_movement')->where('fileNo', '=', $fileNo)->first();
        DB::table('tblfiles')->where('fileNo', '=', $fileNo) ->update(array( 
                'file_destination_section'                     => $data->origin_dept,
                ));
        
           DB::table('tblbulk_file_movement')->where('fileNo', '=', $fileNo)->where('accepted_by', '=', 0)->where('transfered_by', '=', Auth::user()->id)->delete();
          return response()->json("Successfully Recalled");              
    }
    
    
    
}
