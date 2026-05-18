<?php

namespace App\Http\Controllers\funds;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Illuminate\Support\Str;
use Auth;
use DB;


class OpenRegistryController extends ParentController
{
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->division    = $request->session()->get('division');
        $this->divisionID  = $request->session()->get('divisionID');
                return $next($request);
        });
    }  


    public function NEW_STAFF()
    {
        $data['staffList']      = $this->getStaffList();
        $data['countStaffList'] = $this->getCountStaffPerDivision();
        $data['StateList']      = DB::table('tblstates')->select('StateID', 'State')->orderBy('State')->get();
        $data['bankList']       = DB::table('tblbanklist')->select('bankID', 'bank')->orderBy('bank', 'asc')->get();
        $data['lastInserted']   = DB::table('tblper')->max('fileNo');
        $data['getAllDivision'] = DB::table('tbldivision')->orderBy('division', 'DESC')->get();
        $data['readonly']       ="readonly";
        $data['disable']        ="disabled";
        return view('openRegistry.newStaff', $data);
    }


     public function store_NEW_STAFF(Request $request)
    {       
        $this->validate($request, [
            'title'                => 'regex:/[a-zA-Z.]/',
            'surname'              => 'required|regex:/^[\pL\s\-]+$/u',
            'firstName'            => 'required|alpha_num',
            'otherNames'           => 'regex:/^[\pL\s\-]+$/u',
            'division'             => 'required|numeric',
            'grade'                => 'required|numeric',
            'step'                 => 'required|numeric',
        ]);
        $title            = trim($request['title']);
        $surname          = trim($request['surname']);
        $firstName        = trim($request['firstName']);
        $otherNames       = trim($request['otherNames']);
        $division         = trim($request['division']);
        $grade            = trim($request['grade']);
        $step             = trim($request['step']);
        $newFileNo        = 0;
        if($data['checkForExistence'] = DB::table('tblper')
            ->where('surname', $surname)
            ->where('first_name', $firstName)
            ->first())
        {
            $getExistDetails = DB::table('tblper')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->where('surname', $surname)
                ->where('first_name', $firstName)
                ->first();
            return back()->with('err', "Conflict In Names! - We already have record for this names: " . 
                $surname . " ". $firstName ." | The existing record has: - ".
                "Surname: ".    $getExistDetails->surname ." - ".
                "First Name: ". $getExistDetails->first_name ." - ".
                "Division: ".   $getExistDetails->division ." - ".
                "File No: ".    $getExistDetails->fileNo );
        }
        else
        {
            DB::beginTransaction();
                $ID = DB::table('tblper')->insertGetId(array( 
                    'title'             => $title,
                    'surname'           => Str::title($surname),
                    'first_name'        => Str::title($firstName),
                    'othernames'        => Str::title($otherNames),
                    'divisionID'        => ($division),
                    'grade'             => ($grade),
                    'step'              => ($step),
                    'staff_status'      => 9,
                    'date'              => (date('Y-m-d')),
                    'created_at'        => (date('Y-m-d')),
                    'updated_at'        => (date('Y-m-d'))         
                ));
                //check again incase fileNo exist (if yes escape from deadLuck)
                $getLastFileNo = (DB::table('tblper')->max('fileNo') + 1);
                if(DB::table('tblper')->where('fileNo', $getLastFileNo)->first()){
                    $newFileNo = ($getLastFileNo + 1);
                }else{
                    $newFileNo = $getLastFileNo;
                }
                //insert FileNo
                DB::table('tblper')->where('ID', $ID)->update(array( 
                        'fileNo'            => $newFileNo
                ));
                /*DB::table('tblcv')->insert(array( 
                    'fileNo'            => $fileNo,
                    'date'              => date('Y-m-d')
                ));*/
                $this->addLog('new staff was added with fileNo = '.$newFileNo);
            DB::commit();
            $fullname               = Str::title($surname) ." ". Str::title($firstName);
            return redirect('/new-staff/create')->with('msg', 'New staff file has been successfully created. '.$fullname." FileNo is: ".$newFileNo);
        } 
    }


    public function autocomplete_STAFF(Request $request)
    {
        $query = $request->input('query');
        $search = DB::table('tblper')
                ->where('divisionID', $this->divisionID)
                ->orWhere('first_name', 'LIKE', '%'.$query.'%')
                ->orwhere('othernames', 'LIKE', '%'.$query.'%')
                ->orwhere('surname', 'LIKE', '%'.$query.'%')
                ->orWhere('fileNo', 'LIKE','%'.$query.'%')
                ->take(6)
                ->get();
        $return_array = null;
        foreach($search as $s)
        {
          $return_array[]  =  ["value"=>$s->surname.' '.$s->first_name.' '.$s->othernames.' - '.$s->fileNo, "data"=>$s->fileNo];
        }   
        return response()->json(array("suggestions"=>$return_array));
    }


    //GET ALL STAFF MASTER LIST
    public function listAll()
    {
        $data['users'] = DB::table('tblper')
            ->Join('tbldivision', 'tblper.divisionID', '=', 'tbldivision.divisionID')
            ->where('tblper.staff_status', 1)
            ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
            ->orderBy('tblper.grade', 'Desc')
            ->orderBy('tblper.step', 'Desc')
            ->orderBy('tblper.appointment_date', 'Asc')
            ->paginate(20);
        $data['getDivision'] = DB::table('tbldivision')->get();
        $data['filterDivision'] = "";
        return view('openRegistry.viewStaff', $data);
    }


    public function filter_staff(Request $request)
    {
        $filterBy = trim($request['fileNo']);
        $filterDivision = trim($request['filterDivision']); 
        
        if($filterDivision == "")
        {
            if($filterBy == null){
                return redirect('/staff-report/view');
            }
            $data['users'] = DB::table('tblper')
                    ->Join('tbldivision', 'tblper.divisionID', '=', 'tbldivision.divisionID')
                    ->where('surname', 'LIKE', '%'.$filterBy.'%')
                    ->orWhere('first_name', 'LIKE', '%'.$filterBy.'%')
                    ->orWhere('fileNo', 'LIKE','%'.$filterBy.'%')
                    ->where('tblper.staff_status', 1)
                    ->where('tblper.employee_type', '<>', 'JUDGES')
                    ->where('tblper.divisionID', $this->divisionID)
                    ->orderBy('tblper.grade', 'Desc')
                    ->orderBy('tblper.step', 'Desc')
                    ->orderBy('tblper.appointment_date', 'Asc')
                    ->paginate(20);
            $data['getDivision'] = DB::table('tbldivision')->get();
            $data['filterDivision'] = "";
            return view('openRegistry.viewStaff', $data);
        }else if($filterDivision <> "")
        {
            $data['users'] = DB::table('tblper')
                    ->Join('tbldivision', 'tblper.divisionID', '=', 'tbldivision.divisionID')
                    ->where('tblper.staff_status', 1)
                    ->where('tblper.employee_type', '<>', 'JUDGES')
                    ->where('tblper.divisionID', $filterDivision)
                    ->orderBy('tblper.grade', 'Desc')
                    ->orderBy('tblper.step', 'Desc')
                    ->orderBy('tblper.appointment_date', 'Asc')
                    ->paginate(20);
            $data['getDivision'] = DB::table('tbldivision')->get();
            $getDivFilter = DB::table('tbldivision')->where('divisionID', $filterDivision)->first();
            $data['filterDivision'] = ' IN ' . $getDivFilter->division . ' DIVISION';
            return view('openRegistry.viewStaff', $data);
        }else{
            return redirect('/staff-report/view');
        }
    }



    public function index()
    {
        $data['details'] = DB::table('division_registry')->get();
        $data['profile'] = DB::table('tblper')
        ->where('divisionID', '=', $this->divisionID)
        ->where('grade', '<>', "")
        ->where('divisionID', '<>', null)
        ->get();
        $data['registry'] = DB::table('openregistry')->paginate(3);
        return view('openRegistry.list',$data);
    }

    public function indexview()
    {
        $data['details'] = DB::table('division_registry')->get();
        $data['profile'] = DB::table('tblper')
            ->where('divisionID', '=', $this->divisionID)
            ->where('grade', '<>', "")
            ->where('divisionID', '<>', null)
            ->orderBy('tblper.fileNo', 'Desc')
            ->get();
        $data['registry'] = DB::table('openregistry')->get();

        return view('openRegistry.create',$data);
    }

       public function autocomplete(Request $request)
    {
        $query = $request->input('query');
        $search = DB::table('tblper')->where('surname', 'LIKE', '%'.$query.'%')->
            orWhere('first_name', 'LIKE', '%'.$query.'%')->orWhere('fileNo', 'LIKE','%'.$query.'%')->take(15)->get();
        $return_array = null;
        foreach($search as $s)
        {
          $return_array[]  =  ["value"=>$s->surname.' '.$s->first_name.' '.$s->othernames.' - '.$s->fileNo, "data"=>$s->fileNo];
        }   
        return response()->json(array("suggestions"=>$return_array));
    }

    public function showAll(Request $request)
    {
        $term=$request->input('nameID');
        DB::enableQueryLog();
        $data = DB::table('tblper')
            ->leftJoin('tbldivision', 'tblper.divisionID', '=', 'tbldivision.divisionID')
            ->where('tblper.fileNo', '=', $term)
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
            'gender'      => 'required',
            'fileno'           => 'required|string',
            'designation'       => 'required',
        ]);
        $fullName              = trim($request['staffname']);
        $gender          = trim($request['gender']);
        $fileno               = trim($request['fileno']);
        $designation           = trim($request['designation']);
        $dateopen           = date('Y-m-d', strtotime(trim($request['dateopen'])));
        $divreg           = trim($request['divreg']);
        $inout           = trim($request['inout']);
        $volume           = trim($request['volume']);
        $lastpage           = trim($request['lastpage']);
        $recipient           = trim($request['recipient']);
        $returndate           = date('Y-m-d', strtotime(trim($request['returndate'])));
        $purpose           = trim($request['purpose']);
        $destination           = trim($request['destination']);
        $date                  = date("Y-m-d");
        //check if record exist
         $check_record = DB::table('openregistry')
        ->where('fileNo', '=', $fileno)
        ->first();
    
        if($inout == "Incoming")
        {
        DB::table('openregistry')->insert(array( 
            'FileNo'           => $fileno, 
            'staffname'         => $fullName, 
            'gender'     => $gender, 
            'division'      => $divreg,
            'nameOfRecepient'          => $recipient,
            'Designation'          => $designation,
            'returnedDate'          => $returndate,
            'in_out'          => $inout,
            'volumes'          => $volume,
            'lastPageNumber'          => $lastpage,
            
            'dateOpen'          => $dateopen,
            'updated_at'       => $date
        ));
        $this->addLog('New Personal file Record has been Entered With Division: ' . $this->division);
        
    return redirect('/openregistry/create/')->with('msg', 'Operation was done successfully.');        
                
    }elseif($inout == "Outgoing")
    {
          DB::table('openregistry')->insert(array( 
            'FileNo'           => $fileno, 
            'staffname'         => $fullName, 
            'gender'     => $gender, 
            'division'      => $divreg,
            'nameOfRecepient'          => $recipient,
            'Designation'          => $designation,
            'purposeOfMovement'          => $purpose,
            'in_out'          => $inout,
            'volumes'          => $volume,
            'lastPageNumber'          => $lastpage,
            'destination'          => $destination,
            'dateOpen'          => $dateopen,
            'updated_at'       => $date
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
        $pfrid              = trim($request['pfrid']);
        $fullName              = trim($request['staffname']);
        $gender          = trim($request['gender']);
        $fileno               = trim($request['fileno']);
        $designation           = trim($request['designation']);
        $dateopen           = date('Y-m-d', strtotime(trim($request['dateopen'])));
        $divreg           = trim($request['divreg']);
        $inout           = trim($request['inout']);
        $volume           = trim($request['volume']);
        $lastpage           = trim($request['lastpage']);
        $recipient           = trim($request['recipient']);
        $returndate           = date('Y-m-d', strtotime(trim($request['returndate'])));
        $purpose           = trim($request['purpose']);
        $destination           = trim($request['destination']);
        $date                  = date("Y-m-d");
        if($inout == "Outgoing")
        {
    DB::table('openregistry')->where('pfrID', '=', $pfrid)->update(array( 
            'FileNo'           => $fileno, 
            'staffname'         => $fullName, 
            'gender'     => $gender, 
            'division'      => $divreg,
            'nameOfRecepient'          => $recipient,
            'Designation'          => $designation,
             'destination'          => $destination,
            'in_out'          => $inout,
            'volumes'          => $volume,
            'lastPageNumber'          => $lastpage,
            'purposeOfMovement'          => $purpose,
            'dateOpen'          => $dateopen,
            'updated_at'       => $date
        ));
       
    return redirect('/openregistry/editout/'.$pfrid.'')->with('msg', 'Operation was done successfully.');        
      
    }elseif($inout == "Incoming")
    {
          DB::table('openregistry')->where('pfrID', '=', $pfrid)->update(array( 
            'FileNo'           => $fileno, 
            'staffname'         => $fullName, 
            'gender'     => $gender, 
            'division'      => $divreg,
            'nameOfRecepient'          => $recipient,
            'Designation'          => $designation,
            'in_out'          => $inout,
            'volumes'          => $volume,
            'lastPageNumber'          => $lastpage,
            'dateOpen'          => $dateopen,
            'returnedDate'          => $returndate,
            'updated_at'       => $date
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
}
