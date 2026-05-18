<?php

namespace App\Http\Controllers\hr;

use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Illuminate\Support\Str;
use Auth;
use DB;
use file;


class OpenRegistryController extends NewStaffQueryController
{
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->division    = $request->session()->get('division');
        $this->divisionID  = $request->session()->get('divisionID');
                return $next($request);
        });
    }  


    public function viewRegistrationForm()
    {
        $data = $this->getStaffInformation();
        $data['tabLevel1'] = 'active';
        $data2['tabPage'] = 1;
        return view('openRegistry.newStaff', $data);
    }


    //Court Information
    public function postCourtTab(Request $request)
    {
        Session::forget('courtIDNew');
        Session::forget('divisionIDNew');
        
        $this->validate($request, [
            'courtName'    => 'required|string',
            'division'     => 'required|string',
        ],['division'=>'location']);
        $courtName         = trim($request['courtName']);
        $division          = trim($request['division']);
        //Pass Info to next Tab
        Session::put('CourtID', $courtName);
        Session::put('divisionID', $division);
        Session::put('courtIDNew', $courtName);
        Session::put('divisionIDNew', $division);

        return redirect()->route('getBasicTab');
    }
    
    
    public function getCourtTab()
    {   
        return redirect()->route('newStaff_court');
    }


    //Basic Information
    public function getBasicTab()
    {   
        $data = $this->getcurrentStaffGet();
        $data = $this->getStaffInformation();
        $data2['tabLevel2'] = 'active';
        $data2['tabPage'] = 2;
        $data['fillUpForm'] = $this->fillUpForm(Session::get('userID'));
        return view('openRegistry.newStaff', $data)->with($data2);

    }
    public function postBasicTab(Request $request)
    {
        $this->validate($request, [
            //'title'         => 'required|alpha_num',
            'surname'       => 'required|alpha_num',
            'firstName'     => 'required|alpha_num',
            //'otherNames'    => 'required|alpha_num',
            'gender'        => 'required|alpha',
            'maritalStatus' => 'required|alpha_dash',
            'dateOfBirth'   => 'required|date',
            //'placeOfBirth'  => 'required|string',
        ]);
        $title              = trim($request['title']);
        $surname            = trim($request['surname']);
        $firstName          = trim($request['firstName']);
        $otherNames         = trim($request['otherNames']);
        $gender             = trim($request['gender']);
        $maritalStatus      = trim($request['maritalStatus']);
        $dateOfBirth        = trim($request['dateOfBirth']);
        $placeOfBirth       = trim($request['placeOfBirth']);
        $courtID            = Session::get('courtIDNew');
        $divisionID         = Session::get('divisionIDNew');
        $progress_regID     = 3;
        $saved = $this->saveBasicInfo($title, $firstName, $otherNames, $surname, $gender, $maritalStatus,
            $dateOfBirth, $placeOfBirth, $courtID, $divisionID, $progress_regID);
        if($saved)
        {
            return redirect()->route('getEmploymentTab');
         }else{
            return redirect()->route('getBasicTab')->with('error', 'Sorry, we cannot process your information. Please try again');
         }
       
    }


     //Employment Information
    public function getContactTab()
    {   
        $data = $this->getcurrentStaffGet();
        $data = $this->getStaffInformation();
        $data2['tabLevel3'] = 'active';
        $data2['tabPage'] = 4;  
        if( session::get('userID') == '')
        {
            return redirect()->route('newStaff_court');
        }
        $data['fillUpForm'] = $this->fillUpForm(Session::get('userID'));
        return view('openRegistry.newStaff', $data)->with($data2);
    }
    
    public function postContactTab(Request $request)
    {
        $this->validate($request, [
            'email'             => 'required|email',
            //'alternateEmail'    => 'required|email',
            'phone'             => 'required|alpha_num',
            //'atternativePhone'  => 'required|alpha_num',
            //'physicalAddress'   => 'required|string',
        ]);
        $email                  = trim($request['email']);
        $alternateEmail         = trim($request['alternateEmail']);
        $phone                  = trim($request['phone']);
        $atternativePhone       = trim($request['atternativePhone']);
        $physicalAddress        = trim($request['physicalAddress']);
        $progress_regID         = 5;
        $saved = $this->saveContactInfo($email, $alternateEmail, $phone, $atternativePhone, $physicalAddress, $progress_regID);
        if($saved)
        {
            return redirect()->route('getPreviewTab');
         }else{
            return redirect()->route('getContactTab')->with('error', 'Sorry, we cannot process your information. Please try again');
         }
    
    }


    //Employment Information
    public function getEmploymentTab()
    {
        $data = $this->getcurrentStaffGet();
        $data = $this->getStaffInformation();
        $data2['tabLevel4'] = 'active';
        $data2['tabPage'] = 3; 
        if(empty(session::get('userID')) )
        {
            return redirect()->route('newStaff_court');
        }
        $data['fillUpForm'] = $this->fillUpForm(Session::get('userID'));
        return view('openRegistry.newStaff', $data)->with($data2);
    }
    
    
    public function postEmploymentTab(Request $request)
    {
        $this->validate($request, [
            'grade'                 => 'required|numeric',
            'step'                  => 'required|numeric',
            'department'            => 'required|string',
            'presentAppointment'    => 'required|date',
            'firstAppointment'      => 'required|date',
            'employmentType'        => 'required|string',
        ]);
        $grade                  = trim($request['grade']);
        $step                   = trim($request['step']);
        $department             = trim($request['department']);
        $presentAppointment     = trim($request['presentAppointment']);
        $firstAppointment       = trim($request['firstAppointment']);
        $employmentType         = trim($request['employmentType']);
        $progress_regID         = 4;

        $saved = $this->saveEmploymentInfo($grade, $step, $department, $presentAppointment, $firstAppointment, $employmentType, $progress_regID);

        if($saved)
        {
            return redirect()->route('getContactTab');
         }else{
            return redirect()->route('getEmploymentTab')->with('error', 'Sorry, we cannot process your information. Please try again');
         }

    }


    //Preview Information
    public function getPreviewTab()
    { 
        $data = $this->getcurrentStaffGet();
        $data2['tabLevel5'] = 'active';
        $data2['tabPage'] = 5;
        if( session::get('userID') == '')
        {
            return redirect()->route('newStaff_court');
        }
        return view('openRegistry.newStaff', $data)->with($data2);
    }


    //set court and staff for Ongoing registration
    public function postCurrentStaffCourtID(Request $request)
    {	
	Session::forget('staffcourtID');
        $this->validate($request, [
            'staffCourt'  => 'required|numeric',
        ]);
        $courtID          = trim($request['staffCourt']);
        session::put('staffcourtID', $courtID);
        return redirect()->route('getCurrentStaff');
    }
    
    public function postCurrentStaff(Request $request)
    {	
         session::forget('userID');
         
        $this->getFreshRegistration();
        $this->validate($request, [
            'staffName'   => 'required|numeric',
        ], ['staffName'=>'Staff Name or Registration number(File No)',]);
        $userID           = trim($request['staffName']);
        if($userID == '000000')
        {
            return redirect()->route('newRegistration')->with('info', 'Start new registration');
        }
        session::put('userID', $userID);
        return redirect()->route('getCurrentStaff');
    }
	
	
    public function getCurrentStaff()
    {	
        $data = $this->getcurrentStaffGet();  
        return view('openRegistry.newStaff', $data);
    }
    
    //end court and ongoing staff


    public function finalRegistration()
    {
        $submitted = $this->fininalSubmittion();
        if($submitted)
        {
            $this->getFreshRegistration();
            return redirect()->route('getCoutTab')->with('message', 'Congratulations! Your Registration has been Completed');
         }else{
            return redirect()->route('getPreviewTab')->with('error', 'Sorry, we cannot submit your final registration. Please try again');
         }
         return redirect()->route('getCoutTab');
    }


    //New Registration
    public function newRegistration()
    {
        $this->getFreshRegistration();
        $data   = $this->getStaffInformation();

        return view('openRegistry.newStaff', $data);
    }
    

    //
    public function getDesignationJson(Request $request)
    {
        $this->validate($request, [
            'grade'          => 'required|numeric',
            'department'     => 'required|numeric',
        ]);
        $grade               = trim($request['grade']);
        $departmentID        = trim($request['department']);
        $data = $this->queryDesignation($grade, $departmentID);
        return response()->json($data);
    }

    //delete Ongoing Registation
    public function deleteOngoingRegistration(Request $request)
    {
       
        $deleted = $this->deleteOngoingReg(Session::get('userID'));
        if($deleted)
        { 
            return redirect()->route('getCoutTab')->with('message', 'A staff with ongoing registration was deleted successfully');
        }else{
            return redirect()->route('getCoutTab')->with('error', 'Sorry, we cannot delete this staff from our system. Please try again');
        }
        return redirect()->route('getCoutTab')->with('error', 'Sorry, we cannot delete this staff from our system. Please try again');
    }


    //PICTURE UPLOAD
    public function loadUploadView()
    {   
        //$data = $this->getStaffInformation();
        $data = $this->getcurrentStaffGet();
        $data['tabPage'] = "uploadPicture";
        $data['getFolderPath'] = $this->getFolderPath();
        return view('openRegistry.newStaff', $data);
       
    }


    public function uploadBrowsedPicture(Request $request)
    {
        $userID = Session::get('userID');
        if(!$userID)
        {
            return redirect()->route('uploadFile')->with('error', 'Sorry, we cannot find the details of this staff. Select a staff from the list and try again');
        }
        $this->validate($request, [  
            'photography'  => 'image|mimes:png,jpg,jpe,jpeg,gif|max: 2000',
        ]);
        $photograph                 = $request['photography'];
        //
        $newImageExtension          = '.jpg';
        $file                       = $photograph;
        $imageNewName               = str_replace('/', '_', $this->getStaffFileNo()) . $newImageExtension;
        $operationID = $userID;
        $path =  $this->getCourtFolderPath();
        if($path == '')
        {
            return redirect()->route('uploadFile')->with('error', 'Sorry, we are having issue with a location while saving this photo. Pls try again');
        }
        if($file)
        {
            /**if(file_exists($path.$imageNewName))
            {
                //Delete existing file
                delete_files($path.$imageNewName);
            }**/
            if($file->move($path, $imageNewName))
            {
                
                $saved = $this->updateStaffPhoto($userID, $imageNewName);
                if($saved)
                {
                    return redirect()->route('getPreviewTab')->with('message', 'Your profile has been updated successfully');
                }else{
                    return redirect()->route('uploadFile')->with('error', 'Sorry, We are having issue while updating your profile photo. Please try again');
                }

            }else{
                return redirect()->route('uploadFile')->with('error', 'Sorry, We are having issue uploading this photograph. Please try again');
            }
        }else{
             return redirect()->route('uploadFile')->with('error', 'We cannot find any file to upload!');
        }
}









    /////////////////////////////////////////////////////////////////////////////////////


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
        //die(json_encode($data['users']));
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
                    die(json_encode($data['users']));
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
                    die(json_encode($data['users']));
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
        $data = DB::table('tblper')
            ->leftJoin('tbldivision', 'tblper.divisionID', '=', 'tbldivision.divisionID')
            ->where('tblper.fileNo', '=', $term)
            ->select('fileNo','surname', 'first_name', 'othernames', 'fileNo', 'division','Designation','gender','ID') 
            ->get();
        return response()->json($data);
    } 
    public function showAllData(Request $request)
    {
        $term=$request->input('nameID');
        $data = DB::table('tblper')
            ->leftJoin('tbldivision', 'tblper.divisionID', '=', 'tbldivision.divisionID')
            ->where('tblper.ID', '=', $term)
            ->select('fileNo','surname', 'first_name', 'othernames', 'fileNo', 'division','Designation','gender','ID') 
            ->first();
        
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
        $fullName            = trim($request['staffname']);
        $gender              = trim($request['gender']);
        $fileno              = trim($request['fileno']);
        $designation         = trim($request['designation']);
        $dateopen            = date('Y-m-d', strtotime(trim($request['dateopen'])));
        $divreg              = trim($request['divreg']);
        $inout               = trim($request['inout']);
        $volume              = trim($request['volume']);
        $lastpage            = trim($request['lastpage']);
        $recipient           = trim($request['recipient']);
        $returndate          = date('Y-m-d', strtotime(trim($request['returndate'])));
        $purpose             = trim($request['purpose']);
        $destination         = trim($request['destination']);
        $date                = date("Y-m-d");
        //check if record exist
         $check_record = DB::table('openregistry')
        ->where('fileNo', '=', $fileno)
        ->first();
    
        if($inout == "Incoming")
        {
        DB::table('openregistry')->insert(array( 
            'FileNo'             => $fileno, 
            'staffname'          => $fullName, 
            'gender'             => $gender, 
            'division'           => $divreg,
            'nameOfRecepient'    => $recipient,
            'Designation'        => $designation,
            'returnedDate'       => $returndate,
            'in_out'             => $inout,
            'volumes'            => $volume,
            'lastPageNumber'     => $lastpage,
            
            'dateOpen'           => $dateopen,
            'updated_at'         => $date
        ));
        $this->addLog('New Personal file Record has been Entered With Division: ' . $this->division);
        
    return redirect('/openregistry/create/')->with('msg', 'Operation was done successfully.');        
                
    }elseif($inout == "Outgoing")
    {
          DB::table('openregistry')->insert(array( 
            'FileNo'              => $fileno, 
            'staffname'           => $fullName, 
            'gender'              => $gender, 
            'division'            => $divreg,
            'nameOfRecepient'     => $recipient,
            'Designation'         => $designation,
            'purposeOfMovement'   => $purpose,
            'in_out'              => $inout,
            'volumes'             => $volume,
            'lastPageNumber'      => $lastpage,
            'destination'         => $destination,
            'dateOpen'            => $dateopen,
            'updated_at'          => $date
        ));
        $this->addLog('New Personal file Record has been Entered With Division: ' . $this->division);
    }
    
    return redirect('/openregistry/create/')->with('msg', 'Operation was done successfully.');        
               
    }

    public function edit($id)
    {
               
       $data['divisions'] = DB::table('division_registry')->get();
       $data['registry']  = DB::table('openregistry')
                          ->where('pfrID','=',$id)
                          ->first();
      //dd($data);
       return view('openRegistry.editout', $data);
    }

    public function update(Request $request)
    {
        $pfrid              = trim($request['pfrid']);
        $fullName           = trim($request['staffname']);
        $gender             = trim($request['gender']);
        $fileno             = trim($request['fileno']);
        $designation        = trim($request['designation']);
        $dateopen           = date('Y-m-d', strtotime(trim($request['dateopen'])));
        $divreg             = trim($request['divreg']);
        $inout              = trim($request['inout']);
        $volume             = trim($request['volume']);
        $lastpage           = trim($request['lastpage']);
        $recipient          = trim($request['recipient']);
        $returndate         = date('Y-m-d', strtotime(trim($request['returndate'])));
        $purpose            = trim($request['purpose']);
        $destination        = trim($request['destination']);
        $date               = date("Y-m-d");
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
      
    }elseif($inout == "Incoming")
    {
          DB::table('openregistry')->where('pfrID', '=', $pfrid)->update(array( 
            'FileNo'           => $fileno, 
            'staffname'        => $fullName, 
            'gender'           => $gender, 
            'division'         => $divreg,
            'nameOfRecepient'  => $recipient,
            'Designation'      => $designation,
            'in_out'           => $inout,
            'volumes'          => $volume,
            'lastPageNumber'   => $lastpage,
            'dateOpen'         => $dateopen,
            'returnedDate'     => $returndate,
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

     public function getCourt(Request $request)
    {
        $courtID = $request['courtID'];
        $data = DB::table('tbldivision')->where('courtID', '=', $courtID)->get();
        return response()->json($data);
    }

     public function getDepartments(Request $request)
    {
        $courtID = $request['courtID'];
        $data = DB::table('tbldepartment')->where('courtID', '=', $courtID)->get();
        return response()->json($data);
    }

     public function getDesignations(Request $request)
    {
        $courtID = $request['courtID'];
        $data = DB::table('tbldesignation')->where('courtID', '=', $courtID)->get();
        return response()->json($data);
    }

}
