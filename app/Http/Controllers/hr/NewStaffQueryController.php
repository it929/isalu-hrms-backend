<?php

namespace App\Http\Controllers\hr;

use Redirect;
use Illuminate\Support\Facades\Request;
use Carbon\Carbon;
use Auth;
use Session;
use DB;

class NewStaffQueryController extends ParentController
{
    
    public function __construct()
    {
       $this->middleware('auth');
    }


    public function getStaffInformation()
    {
        $courtID='';
        //$userID = Auth::user()->id;
        $userID = session::get('userID');
        
        $uid = DB::table('tblper')->where('UserID','=',$userID)->first();
        $data['users'] = DB::table('users')->where('id','=', Auth::user()->id)->first();
        if($uid != null)
        {
            $courtID = DB::table('tbl_court')->where('id','=',$uid->courtID)->first();
            $data['getAllDivision'] = DB::table('tbldivision')->where('courtID','=',$uid->courtID)->orderBy('division', 'DESC')->get();
            $data['departments'] = DB::table('tbldepartment')->where('courtID','=', $uid->courtID)->get();
            $data['designation'] = DB::table('tbldesignation')->where('courtID','=',$uid->courtID)->get();

            $data['courts'] = $this->getCourtByID($uid->courtID);
        }else{
            $data['departments']    = '';
            $data['getAllDivision'] = '';
            $data['designation']    = '';
            $data['courts']         = '';
        }
        $data['staffList']      = $this->getStaffList();
        $data['countStaffList'] = $this->getCountStaffPerDivision();
        $data['StateList']      = DB::table('tblstates')->select('StateID', 'State')->orderBy('State')->get();
        $data['bankList']       = DB::table('tblbanklist')->select('bankID', 'bank')->orderBy('bank', 'asc')->get();
        $data['lastInserted']   = DB::table('tblper')->max('fileNo');
        $data['getcourts'] = $this->getAllCourt();

        //get all staff with progress registrations
        $courtID = session::get('staffcourtID');
        if($courtID != '')
        {   
            $data['currentCourtName'] = DB::table('tbl_court')->where('id', $courtID)->value('court_name');
            $data['progressReg'] = DB::table('tblper')
            ->leftjoin('tbl_court', 'tbl_court.id', '=', 'tblper.courtID')
            ->where('progress_regID','<', 6)
            ->where('tbl_court.id', $courtID)
            //->orwhere('progress_regID','=', 0)
            ->orderBy('fileNo', 'DESC')
            ->get();
        }else{
            $data['currentCourtName'] = '';
            $data['progressReg'] = DB::table('tblper')
            ->leftjoin('tbl_court', 'tbl_court.id', '=', 'tblper.courtID')
            ->where('progress_regID','<', 6)
            //->orwhere('progress_regID','=', 0)
            ->orderBy('fileNo', 'DESC')
            ->get();
        }

        //
        $data['tabLevel1'] = 'disabled';
        $data['tabLevel2'] = 'disabled';
        $data['tabLevel3'] = 'disabled';
        $data['tabLevel4'] = 'disabled';
        $data['tabLevel5'] = 'disabled';
        $data['tabPage'] = 1; 
        //
        $courtID    = Session::get('CourtID');
        $divisionID = Session::get('divisionID');
        if($courtID <> "" and $divisionID <> "")
        {
            $data['getCourtDetails'] = $this->getCourtByID($courtID);
            $data['getDivisionDetails'] = $this->getDivisionByID($divisionID);
        }else{
            $data['getCourtDetails'] = "";
            $data['getDivisionDetails'] = "";
        }
        $data['fillUpForm'] = $this->fillUpForm('');

        //get Designation
        $desg = DB::table('tblper')->where('UserID','=',$userID)->first();
        $getDesignation = '';
        if($desg)
        {
            $getDesignation = DB::table('tbldesignation')
            ->where('grade', $desg->grade)
            ->where('departmentID', $desg->department)
            ->where('courtID', $desg->courtID)
            ->select('tbldesignation.designation as designation_name')
            ->first();
        }
        if($getDesignation)
        {
            $data['getDesignation'] =  $getDesignation->designation_name;
        }else{
            $data['getDesignation'] = '';
        }
        //Title
         $data['getTitle'] = DB::table('tbltitle')->get();
         $data['getAllcourt'] = $this->getAllCourt();
        //Employment Type
        $data['employmentType'] = DB::table('tblemployment_type')->where('active', 1)->get();

        return $data;
    }//


    public function getcurrentStaffGet()
    {
        $data['getUerID']       = null;
        $data['getCourtName']   = null;
        $data['dataStaff']      = [];
        
        $userID = session::get('userID');
        $data   = $this->getStaffInformation(); 
        $getStaffTab = $this->fillUpForm($userID);
        $data['fillUpForm'] =  $getStaffTab;
        //$data['getAllcourt'] = $this->getAllCourt();
        if(!empty($getStaffTab) && $getStaffTab)
        {
            Session::put('fileNo', $getStaffTab->fileNo);
            $data['tabPage']    = $getStaffTab->progress_regID; 
            $getTab = 'tabLevel'.$getStaffTab->progress_regID;
            $data['getPreviewInfo'] = $this->getPreviewDetails(Session::get('userID')); //paramiter is optional
            $data[$getTab] = 'active';
            $data['getUerID'] = $userID;
            Session::put('CourtID', $getStaffTab->courtID);
            $data['getCourtName'] = $getStaffTab->court_name;
            Session::put('divisionID', $getStaffTab->divisionID);
            $data['userDesignation'] = $this->queryDesignation($data['getPreviewInfo']->grade, $data['getPreviewInfo']->departmentID);
            $data['getFolderPath'] = $this->getFolderPath();
        }else{
            session::forget('userID');
            session::forget('fileNo');
        }
        //$data['tabPage']   = 1; 
        //$data['tabLevel1'] = 'active';
        
        return $data;
    }



    //get all court
    public function getAllCourt()
    {
        return DB::table('tbl_court')->get();
    }

    //search or get court by ID
    public function getCourtByID($courtID = null)
    {
        if(DB::table('tbl_court')->where('id', $courtID)->first())
        {
            return DB::table('tbl_court')->where('id', $courtID)->first();
        }
        return "";
    }


    public function getDivisionByID($divisionID)
    {
        if(DB::table('tbldivision')->where('divisionID', $divisionID)->first())
        {
            return DB::table('tbldivision')->where('divisionID', $divisionID)->first();
        }
        return "";
    }


    public function generateFileNo($tableName, $generateIDfieldName, $courtid)
    {       
	/*
            //$myData= DB::Select("SELECT max(`fileNo`) as LastFileNo FROM `tblper` WHERE `courtID` ='$courtid'");
            //$myData1= DB::Select("SELECT * FROM `tbl_court` WHERE `id`='$courtid'");
            //NOTE: the above commented query has been rewritten exactly as below because it seems obsolate. Thanks
            //NOTE: this works: $myData = DB::table('tblper')->latest()->first(); //but uses created_at firld
            
            $myData = DB::table('tblper')->where('courtID', $courtid)->orderBy('ID', 'Desc')->select('fileNo as LastFileNo')->first();
            $myData1 = DB::table('tbl_court')->where('id', $courtid)->select('courtAbbr', 'file_abbr')->first();
            
            if(empty($myData))
            { 
                if(empty($myData1)) //$myData1[0]->file_abbr == ''
                {
                    return $myData1->courtAbbr. '/00001';
                }else{
                    return $myData1->courtAbbr.'/'.  $myData1->file_abbr. '/00001';
                }
            }
             $LastFileNo = ($myData) ? $myData->LastFileNo : '';
             $arr = explode("/", $LastFileNo);

            if(empty($myData1)) ////$myData1[0]->file_abbr == ''
            {
                  $newcode=$arr[1]+1;
            }else{
                  $newcode=$arr[2]+1;
            }
            while(strlen($newcode) < 5)
            {
                $newcode="0".$newcode;
            }
            if(empty($myData1)) //$myData1[0]->file_abbr == ''
            {
                return $arr[0].'/'.$newcode;
            }else{
                return $arr[0].'/'.$arr[1].'/'.$newcode;
            }
           
        ///////////////////////////////////////
        $getLastFileNo = (DB::table($tableName)->max($generateIDfieldName) + 1);
        if(DB::table($tableName)->where($generateIDfieldName, $getLastFileNo)->first()){
            $newFileNo = ($getLastFileNo + 1);
        }else{
            $newFileNo = $getLastFileNo;
        }
        return $newFileNo;
        */
        
        //GET NEW FILE NUMBER
        $lastFileNoPerCourt= DB::table('tblper')->where('courtID', $courtid)->orderBy('ID', 'Desc')->value('fileNo');
        $courAbbr = DB::table('tbl_court')->where('id', $courtid)->select('courtAbbr', 'file_abbr')->first();
            
	$courtCode       = ($courAbbr ->courtAbbr);
	$courtFileAbbr   = ($courAbbr ->courtAbbr);
	if(empty($lastFileNoPerCourt)){
		$newFileNo = '00001';
	}else{
		$splitArr = explode("/", $lastFileNoPerCourt);
		$newFileNo = '00001'; //defualt
		$initial = '0000'; 
		if(count($splitArr) < 1){
			$newFileNo = ($courtFileAbbr) ? ($courtFileAbbr .'/'. '00001') : '00001';
		}else if(count($splitArr) == 1){
			$newFileNo = intval($splitArr[0]) + 1;
		}else if(count($splitArr) == 2){
			$newFileNo = intval($splitArr[1]) + 1;
		}else if(count($splitArr) == 3){
			$newFileNo = ($courtFileAbbr) ? ($courtFileAbbr .'/'. intval($splitArr[2]) + 1) : (intval($splitArr[2]) + 1);
		}else{ //i.e > 3 ..save code
			$newFileNo = ($courtFileAbbr) ? ($courtFileAbbr .'/'. intval(end($splitArr)) + 1) : (intval(end($splitArr)) + 1); 
		}
	}
	$newFileNoAllotted = $courtCode .'/'. $initial .$newFileNo;
	if(DB::table('tblper')->where('fileNo', $newFileNoAllotted )->first()){ //FallBack
		return ($courtFileAbbr) ? ($courtCode .'/'.$courtFileAbbr .'/'. intval(end($splitArr)) + 1) : ($courtCode .'/'. intval(end($splitArr)) + 1); ;
	}else{
		return $newFileNoAllotted;
	}
        
    }
    
    

     public function generatePassword()
     {
        $autoPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') , 0 , 6 );
        return $autoPassword;
     }


     public function saveUser($firstName, $surname, $courtID, $divisionID)
     {
            $getAutoPasswaord   = $this->generatePassword();
            $HarshPassword      = bcrypt($getAutoPasswaord);
            $fileNo = $this->generateFileNo('tblper', 'fileNo', $courtID); 
            //set File Number
            Session::put('fileNo', $fileNo);
            Session::forget('userID');
            $lastRecord         = DB::table('users')->insertGetId(array( 
                //'fileNo'        => $fileNo,
                'name'          => $surname.' '.$firstName,
                //'email'         => $email,
                'username'      => $fileNo,
                'password'      => $HarshPassword, 
                'temp_pass'     => $getAutoPasswaord,
                'divisionID'    => $divisionID,
                'courtID'       => $courtID,
                'user_type'     => 'NONTECHNICAL',
                'created_at'    => date('Y-m-d'),
                'updated_at'    => date('Y-m-d'),
        )); 
        Session::put('userID', $lastRecord);
        
        return $lastRecord; 
    }
             

    //save Basic Info
    public function saveBasicInfo($title, $firstName, $otherNames, $surname, $gender, $maritalStatus,
            $dateOfBirth, $placeOfBirth, $courtID, $divisionID, $progress_regID)
    {
        $lastID = 0;
        
        $userID = Session::get('userID');
       
        if($userID && DB::table('tblper')->where('UserID', $userID)->first()) //(Session::get('fileNo') <> null) and 
        { 
            DB::table('tblper')->where('UserID', Session::get('userID'))->update(array( 
                    'UserID'              => $userID,
                    'fileNo'              => Session::get('fileNo'),
                    'title'               => $title,
                    'first_name'          => $firstName,
                    'othernames'          => $otherNames,
                    'surname'             => $surname, 
                    'gender'              => $gender,
                    'maritalstatus'       => $maritalStatus,
                    'dob'                 => $dateOfBirth,
                    'placeofbirth'        => $placeOfBirth,
                    'courtID'             => $courtID,
                    'divisionID'          => $divisionID,
                    'created_at'          => date('Y-m-d'),
                    'updated_at'          => date('Y-m-d'),
                    'progress_regID'      => $progress_regID,
            ));
            $lastID = 1;
        }else{  
            $userID = $this->saveUser($firstName, $surname, $courtID, $divisionID);
            if($userID)
            {
                $lastID = DB::table('tblper')->insertGetId(array( 
                        'UserID'              => $userID,
                        'fileNo'              => Session::get('fileNo'),
                        'title'               => $title,
                        'first_name'          => $firstName,
                        'othernames'          => $otherNames,
                        'surname'             => $surname, 
                        'gender'              => $gender,
                        'maritalstatus'       => $maritalStatus,
                        'dob'                 => $dateOfBirth,
                        'placeofbirth'        => $placeOfBirth,
                        'courtID'             => $courtID,
                        'divisionID'          => $divisionID,
                        'created_at'          => date('Y-m-d'),
                        'updated_at'          => date('Y-m-d'),
                        'progress_regID'      => $progress_regID,
                ));
            }
        }
        
        if($lastID)
        {   
            return $lastID;
        }else{
            //Session::forget('userID');
            //Session::forget('fileNo');
            return 0;
        }
    }


    // Save Contact Info
    public function saveContactInfo($email, $alternateEmail, $phone, $atternativePhone, $physicalAddress, $progress_regID)
    {
        $userID = Session::get('userID');
        if($userID)
        {
            $updated = DB::table('tblper')->where('UserID', $userID)->update(array( 
                'email'              => $email,
                'alternate_email'    => $alternateEmail,
                'phone'              => $phone,
                'alternate_phone'    => $atternativePhone,
                'home_address'       => $physicalAddress, 
                'progress_regID'     => $progress_regID,
                'updated_at'         => date('Y-m-d'),
            ));
            return 1;
        }else{
             return 0;
        }
    }


    // Save Employment Info
    public function saveEmploymentInfo($grade, $step, $department, $presentAppointment, $firstAppointment,
            $employmentType, $progress_regID)
    {
        $userID = Session::get('userID');
        if($userID)
        {
            $updated = DB::table('tblper')->where('UserID', $userID)->update(array( 
                'grade'                     => $grade,
                'step'                      => $step,
                'department'                => $department,
                'date_present_appointment'  => $presentAppointment, 
                'appointment_date'          => $firstAppointment,
                'updated_at'                => date('Y-m-d'),
                'employee_type'            => $employmentType,
                'progress_regID'            => $progress_regID,
            ));
            return 1;
        }else{
            return 0;
        }
    }



    //Preview
    public function getPreviewDetails($userID)
    {
        if($userID == '')
        {
            $userID = Session::get('userID');
        }else{
            $userID = $userID;
        }
        if(DB::table('tblper')->where('UserID', $userID)->first())
        {
            $getPreview = $this->fillUpForm($userID);
            return $getPreview;
        }
        return;
    }

    //fininal submittion
    public function fininalSubmittion()
    {
        $submitted = DB::table('tblper')->where('UserID', Session::get('userID'))->update(array(   
            'progress_regID'      => 6,
        ));
        return 1;
    }

    //New Registration
    public function getFreshRegistration()
    {
        Session::forget('userID');
        Session::forget('fileNo');
        Session::forget('CourtID');
        Session::forget('divisionID');
        Session::forget('staffcourtID');

        return;
    }

    //fill up forms
    public function fillUpForm($userID)
    { 	
    	$getStaffDetails = '';
        if(!empty($userID) or $userID > 0)
        {
            $userID = Session::get('userID');
	    if(DB::table('tblper')->where('UserID', $userID)->first())
	    {
	            $getStaffDetails  = DB::table('tblper')
	            ->leftjoin('tbl_court', 'tbl_court.id', '=', 'tblper.courtID')
	            ->leftjoin('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
	            ->leftjoin('tbldepartment', 'tbldepartment.id', '=', 'tblper.department')
	            ->leftjoin('tbldesignation', 'tbldesignation.grade', '=', 'tblper.grade')
	            ->leftjoin('tbltitle', 'tbltitle.ID', '=', 'tblper.title')
	            ->leftjoin('tblemployment_type', 'tblemployment_type.id', '=', 'tblper.employee_type')
	            ->where('UserID', Session::get('userID'))
	            ->select('*', 'tbl_court.id as courtID', 'tbldepartment.id as departmentID', 'tbldesignation.designation as designation_name', 'tbltitle.title as title_name')
	            ->first();
	    }else{
	         $getStaffDetails = '';
	    }
        }
        return $getStaffDetails;
    }


    public function queryDesignation($grade, $departmentID)
    {
        if($grade and $departmentID)
        {   
            $userID = Session::get('userID');
            $courtID = DB::table('tblper')->where('UserID', $userID)->value('courtID');
            $designation  = DB::table('tbldesignation')
            ->where('grade', $grade)
            ->where('departmentID', $departmentID)
            ->where('courtID', $courtID)
            ->select('tbldesignation.designation as designation_name')
            ->first();
            return $designation;
        }
        return;
    }

    public function defaultUser()
    {
        if((Session::get('userID') == '') or (Session::get('fileNo') == ''))
        {
            $data['tabLevel1'] = 'active';
            $data['tabPage'] = 1; 
            return redirect()->route('newStaff_court');
        }
    }

    //delete Ongoin reg
    public function deleteOngoingReg($userID)
    {    $deleted_per = '';
         $deleted_users = '';
        if(DB::table('tblper')->where('UserID', $userID)->first())
        {
            $copeRecord = DB::table('tblper')->where('UserID', $userID)->first();
            $copied = DB::table('tblongoing_reg_delete')->insert(array( 
                'fileNo'                    => $copeRecord->fileNo,
                'title'                     => $copeRecord->title,
                'surname'                   => $copeRecord->surname,
                'first_name'                => $copeRecord->first_name,
                'othernames'                => $copeRecord->othernames,
                'updated_at'                => date('Y-m-d'),
                'created_at'                => date('Y-m-d'),
                'Designation'               => $copeRecord->Designation,
                'grade'                     => $copeRecord->grade,
                'step'                      => $copeRecord->step,
                'courtID'                   => $copeRecord->courtID,
                'department'                => $copeRecord->department,
                'section'                   => $copeRecord->section,
                'appointment_date'          => $copeRecord->appointment_date,
                'date_present_appointment'  => $copeRecord->date_present_appointment,
                'divisionID'                => $copeRecord->divisionID,
                'progress_regID'            => $copeRecord->progress_regID,
            ));
            if($copied)
            {
                $deleted_per = DB::table('tblper')->where('UserID', $userID)->delete();
                $deleted_users = DB::table('users')->where('id', $userID)->delete();
                session::forget('userID');
                session::forget('fileNo');
                Session::forget('CourtID');
                Session::forget('divisionID');
            }
            return 1;
        }
        else{
            return 0;
        }
           
    }

    //get staff fileNo
    public function getStaffFileNo()
    {   
        $fileNo = '';
        $userID = Session::get('userID');
        if($userID)
        {
            $fileNo = DB::table('tblper')->where('UserID', $userID)->value('fileNo'); 
        }  

        return $fileNo;     
    }


    //Get Path
    public function getFolderPath()
    {
        //create folder if not exit
        $getArray = explode('/', $this->getStaffFileNo());
        $arrayObject = $getArray[0];
        $folderName = ($arrayObject);

        return $folderName;
    }


    //Create Folder
    public function getCourtFolderPath()
    {
        //get folder and path
        //$folderName = (base_path() . '/public/passport/') . $this->getFolderPath();
        $folderName = "/home/njcgov/hr.njc.gov.ng/passport";
        
        /*if(!file_exists($folderName)) 
        {
                //create New folder
                $is_cretaed = mkdir($folderName);
                if($is_cretaed == true)
                {
                    $courtFolder = base_path() . '/passport/' . $folderName;
                }else{
                    $courtFolder = '';
                }
        }else{
                //check does it truly exist
                if(file_exists($folderName)) 
                {
                    $courtFolder = base_path() . '/passport/' . $folderName;
                }else{
                    $courtFolder = '';
                }
        }*/

        return $folderName;
    }



    public function updateStaffPhoto($userID, $imageNewName)
    {
        $saved = false;
        if(DB::table('tblper')->where('UserID', $userID)->first())
        {
            //upload image: update staff record
            DB::table('tblper')->where('UserID', $userID)->update(array( 
                'picture'               => ($imageNewName),
                'updated_at'             => (date('Y-m-d')),
            ));
            $saved = true;
        }  
        return $saved;
    }





}//end class