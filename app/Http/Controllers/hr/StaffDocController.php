<?php

namespace App\Http\Controllers\hr;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use Session;
use Illuminate\Support\Facades\Log;
class StaffDocController extends DatabaseDocumentationController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //When you click the Staff Documentation Link
    public function index()
    {

        Session::forget('fileNo');
        Session::forget('StaffList');
        Session::forget('StaffNames');


        $data =$this->getTabLevel(1);
        $data['StaffList'] = $this->getStaffList();

        Session::put('StaffList',$data['StaffList']);

        $data['fileNo'] = '';
        $data['staffID'] = '';
        $data['StaffNames'] = '';
        $data['progress'] = '';
        $data['prog']='';
        //dd($data['progress']);

        return view('StaffDocumentation.StaffDoc',$data);
    }


    //Controller for when you search for a particular Staff
    public function getStaffInfo(Request $request)
    {
        //dd($data['fileNo']);
        $data['fileNo'] = $request->input('staffID');
        $data['staffID'] = $request->input('staffID');

        Session::put('fileNo',$data['fileNo']);
        Session::put('staffID',$data['staffID']);

        $data['StaffList'] = $this->getStaffList();
        Session::put('StaffList', $data['StaffList']);
        $staff = $this->getStaff($data['fileNo']);

        if(!$staff) return redirect('/staff-documentation');
        //dd($data['fileNo']);
        $data['StaffNames'] = $staff[0]->surname . ' ' . $staff[0]->first_name . ' ' . $staff[0]->othernames;
        //dd($data['StaffNames']);
        Session::put('StaffNames', $data['StaffNames']);

        $progress = $this->getProgress( $data['fileNo']);
        //redirects you to where the staff left off
        switch ($progress) {
            case 6:
            return redirect('/staff-documentation-basic-info');
                break;
            case 7:
            return redirect('/staff-documentation-contact');
                break;
            case 8:
            return redirect('/staff-documentation-placeofbirth');
                break;
            case 9:
	        return redirect('/staff-documentation-marital-status');
                break;
            case 10:
            return redirect('/staff-documentation-nextofkin');
                break;
            case 11:
            	return redirect('/staff-documentation-children');
                break;
            case 12:
            return redirect('/staff-documentation-previous-employment');
                break;
            case 13:
            return redirect('/staff-documentation-attachment');
                break;
            case 14:
            return redirect('/staff-documentation-account');
                break;
            case 15:
            return redirect('/staff-documentation-others');
                break;
            case 16:
            return redirect('/staff-documentation-preview');
              break;
            case 17:
            return redirect('/staff-documentation-preview');
                break;
            default:

            return redirect('/staff-documentation');
        }
    }

    public function getBasicInfo()
    {
        //dd(Session::get('staffID'));
        $data =$this->getTabLevel(2);
        $fileNo = Session::get('fileNo');
        $userID = session::get('userID');
        $check=DB::table('tblper')->where('UserID','=',$userID)->where('courtID','=',0)->exists();

        if($check){ return back()->with('error','You dont have access here');} else { $uid = DB::table('tblper')->where('UserID','=',$userID)->first();}
        //dd($userID);
        $data['fileNo'] = Session::get('fileNo');
        $data['staffID'] = Session::get('staffID');

        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = Session::get('StaffNames');
        $data['progress'] = $this->getProgress($data['fileNo']);
        $data['fillUpForm'] = $this->fillUpForm($data['staffID']);
        //Employment Type
        $data['employmentType'] = DB::table('tblemployment_type')->where('active', 1)->get();
        $data['departments'] = DB::table('tbldepartment')->where('courtID','=', $uid->courtID)->get();
        $data['designation'] = DB::table('tbldesignation')->where('courtID','=',$uid->courtID)->get();
        $data['StateList'] = DB::Select("SELECT * FROM `tblstates`");

        Session::put('progress',$data['progress']);
        //dd($fileNo);
        if(!empty($fileNo)){
            $data['staffID'] = Session::get('staffID');
            $data['prog'] = 6;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['staffID'])->first();

            return view('StaffDocumentation.StaffDoc',$data);

        }else{
            return redirect('/staff-documentation');
        }

    }

    //processing ajax for populating of designation
     public function loadDesignation(Request $request)
    {
      $deptId = $request->get('dept_id');

      $data = DB::table('tbldesignation')->where('departmentID', '=',$deptId)->get();
      return response()->json($data);
    }

    public function submitBasicInfo(Request $request)
    {

        $fileNo         =   Session::get('fileNo');
        $title          =   $request->input('title');
        $gender         =   $request->input('gender');
        $dateofBirth    =   $request->input('dateofBirth');
        $placeofBirth   =   $request->input('placeofBirth');
        $employmentType =   $request->input('employmentType');
        $grade          =   $request->input('grade');
        $step           =   $request->input('step');
        $department     =   $request->input('department');
        $designation    =   $request->input('designation');
        $presentApptmnt =   $request->input('presentAppointment2');
        $fristApptmnt   =   $request->input('firstAppointment2');

        //dd(date('Y-m-d', strtotime($presentApptmnt)));

        if(!empty($fileNo))
        {

            $this->basicSetUp($fileNo, $title, $gender, date('Y-m-d', strtotime($dateofBirth)), $placeofBirth, $employmentType,$grade,$step,$department,$designation, date('Y-m-d', strtotime($presentApptmnt)), date('Y-m-d', strtotime($fristApptmnt)));
            $d = Session::get('progress');
            if($d<7){
            $this->setProgress($fileNo,7);}
            return redirect('/staff-documentation-contact');

        }
        return redirect('/staff-documentation')->with('error','An error occured');
    }

	   //Controller to go to the 'contact Address' Page.
    public function getContact()
    {
        $data =$this->getTabLevel(3);
        $data['fileNo'] = Session::get('fileNo');
        $data['staffID'] = Session::get('staffID');
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = Session::get('StaffNames');
        $data['progress'] = $this->getProgress( $data['fileNo']);
        Session::put('progress',$data['progress']);

        if(!empty($data['fileNo'])){
            $data['prog'] = 7;
            $data['staffInfo'] = DB::table('tblper')->where('ID', $data['staffID'])->first();

           return view('StaffDocumentation.StaffDoc',$data);
        }else{
            return redirect('/staff-documentation');
        }
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


        if(!empty($fileNo))
        {

            $this->contactSetUp($fileNo, $email, $alternateEmail,
             $phone, $alternativePhone, $physicalAddress);
             $d = Session::get('progress');
             if($d<8){
              $this->setProgress($fileNo,8);}
             return redirect('/staff-documentation-placeofbirth');

        }
        return redirect('/staff-documentation')->with('error','An error occured');

    }

     public function getPlaceOfBirth()
    {
        $data =$this->getTabLevel(4);
        $fileNo = Session::get('fileNo');

        $data['fileNo'] = Session::get('fileNo');
        $data['staffID'] = Session::get('staffID');
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = Session::get('StaffNames');
        $data['progress'] = $this->getProgress( $data['fileNo']);
        Session::put('progress',$data['progress']);

        if(!empty($fileNo)){
            $data['prog'] = 8;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['staffID'])->first();

            $data['StateList'] = DB::Select("SELECT * FROM `tblstates`");
            $lgaID = DB::Table('tblper')->where('ID', $data['staffID'])->value('lgaID');
            $data['Lga'] = DB::Table('lga')->where('lgaid', $lgaID)->get();
            //dd( $data['Lga']);


           return view('StaffDocumentation.StaffDoc',$data);
        }else{
            return redirect('/staff-documentation');
        }

    }

    public function submitPlaceOfBirth(Request $request)
    {
        $fileNo = Session::get('fileNo');
        $state = $request->input('state');
        $lga = $request->input('lga');
        $address = $request->input('address');

        if(!empty($fileNo))
        {

            $this->validate($request, [
                'address'=>'required|string',
                ]);

             $this->placeofBirthSetUp($fileNo, $state, $lga, $address);

             $d = Session::get('progress');
             if($d<9){
              $this->setProgress($fileNo,9);}
             return redirect('/staff-documentation-marital-status');
        }

        return redirect('/staff-documentation')->with('error','An error occured');

    }

    public function LGA(Request $request)
    {
      $stateId = $request['id'];

      $data = DB::table('lga')->where('stateId', '=',$stateId)->get();
      return response()->json($data);
    }


    //Controller to go to the 'Marital Information' Page.
    public function getMarital()
    {

        $data =$this->getTabLevel(5);
        $data['fileNo'] = Session::get('fileNo');
        $data['staffID'] = Session::get('staffID');

        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = Session::get('StaffNames');
        $data['progress'] = $this->getProgress( $data['fileNo']);
        Session::put('progress',$data['progress']);

        if(!empty($data['fileNo'])){
           $data['prog'] = 9;
           $data['status'] = DB::Table('tblmaritalStatus')->get();
           $data['maritalStatus'] = DB::Table("tbldateofbirth_wife")->where('staffid', $data['staffID'])->first();
           $data['relationship'] = DB::Table("tblper")->where('ID', $data['staffID'])->value('maritalstatus');
           //dd($data['relationship']);
           return view('StaffDocumentation.StaffDoc',$data);
        }else{
            return redirect('/staff-documentation');
        }
    }



     //Controller for submitting the 'Marital Information' Page.
    public function submitMarital(Request $request)
    {


       //dd($request->all());
        $staffID = Session::get('staffID');
        $fileNo = Session::get('fileNo');
        //dd($fileNo);
        $marital_Status= $request->input('status');
        $maritalStatus = DB::Table("tblper")->where('tblper.ID', $staffID)
                            ->leftjoin('tblmaritalStatus', 'tblper.maritalstatus', '=', 'tblmaritalStatus.ID')
                            ->value('marital_status');
        //dd($maritalStatus);
        $dom= $request->input('dataOfMarriage');
        //dd($dom);
        $fullname= $request->input('spouseName');
        $dob= $request->input('spouseDateOfBirth');
        $address= $request->input('spouseAddress');

      if($maritalStatus=='Married')
       {
       $this->validate($request,
        [
        'dataOfMarriage' =>'required',
        'spouseName' =>'required',
        'spouseDateOfBirth' =>'required',
        'spouseAddress' =>'required',]
        );



       }
        if(!empty($fileNo))
        {

            $this->relationshipSetUp($staffID, $marital_Status,$maritalStatus, date('Y-m-d',strtotime($dom)), $fullname, date('Y-m-d',strtotime($dob)), $address);

            $d = Session::get('progress');
            if($d<10){
             $this->setProgress($fileNo,10);}
            return redirect('/staff-documentation-nextofkin');
        }

        return redirect('/staff-documentation')->with('error','An error occured');
    }





    public function getNextOfKin()
    {
        $data =$this->getTabLevel(6);
        $fileNo = Session::get('fileNo');

        $data['fileNo'] = Session::get('fileNo');
        $data['staffID'] = Session::get('staffID');
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = Session::get('StaffNames');
        $data['progress'] = $this->getProgress( $data['fileNo']);
        Session::put('progress',$data['progress']);

        if(!empty($fileNo)){
            $data['prog'] = 10;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['staffID'])->first();
            //$data['nextOfKin'] = DB::Table("tblnextofkin")->where('staffid', $data['fileNo'])->first();
            $data['nextOfKins'] = DB::Table("tblnextofkin")->where('staffid', $data['staffID'])->get();
            //die(json_encode($data['nextOfKins']));
            switch (count($data['nextOfKins'])) {
	    case 0:
	        $data['nextOfKins'][]= json_decode('{"kinID":"","fileNo":"","uniqueID":"","fullname":"","relationship":"","address":"","phoneno":"","updated_at":"","staffid":""}');
            	$data['nextOfKins'][]= json_decode('{"kinID":"","fileNo":"","uniqueID":"","fullname":"","relationship":"","address":"","phoneno":"","updated_at":"","staffid":""}');
	        break;
	    case 1:
	        $data['nextOfKins'][]= json_decode('{"kinID":"","fileNo":"","uniqueID":"","fullname":"","relationship":"","address":"","phoneno":"","updated_at":"","staffid":""}');
	        break;

	    default:
	}
            //dd($data['nextOfKins']);
            $data['relationship'] = DB::Table('tbldependant_relationship')->get();



           return view('StaffDocumentation.StaffDoc',$data);
        }else{
            return redirect('/staff-documentation');
        }

    }

    public function submitNextOfKin(Request $request)
    {

        $fileNo = Session::get('fileNo');
        $staffID = Session::get('staffID');
        $fullName = $request->input('fullName');
        $phoneNumber = $request->input('phoneNumber');
        $physicalAddress = $request->input('physicalAddress');
        $relationship = $request->input('relationship');


        if(!empty($fileNo))
        {
            DB::Delete("DELETE FROM `tblnextofkin` WHERE `staffid`='$staffID'");

            for ($i = 1; $i <= count($_POST['fullName']); $i++) {
            //echo $_POST['fullName'][$i];
            $this->nextOfKinSetUp($staffID, $_POST['fullName'][$i], $_POST['phoneNumber'][$i], $_POST['physicalAddress'][$i], $_POST['relationship'][$i]);
            }


             $d = Session::get('progress');
             if($d<11) {
              $this->setProgress($fileNo,11);}
             return redirect('/staff-documentation-children');
        }

        return redirect('/staff-documentation')->with('error','An error occured');

    }


	public function getChildren()
    {

        $data =$this->getTabLevel(7);
        $fileNo = Session::get('fileNo');
        $data['staffID'] = Session::get('staffID');

        $data['fileNo'] = Session::get('fileNo');
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = Session::get('StaffNames');
        $data['progress'] = $this->getProgress($data['fileNo']);
        Session::put('progress',$data['progress']);

        if(!empty($fileNo)){
            $data['prog'] = 11;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['staffID'])->first();
            $data['children'] = DB::Table("tblchildren_particulars")->where('staffid', $data['staffID'])->get();

            return view('StaffDocumentation.StaffDoc',$data);
        }else{
            return redirect('/staff-documentation');
        }

    }

    public function submitChildren(Request $request)
    {    //dd($request->all());

        $fileNo = Session::get('fileNo');
        $data['staffID'] = Session::get('staffID');
        //dd($data['staffID']);
        $childrenname=  $request->input('fullname');
        $childrendob=  $request->input('childDateOfBirth');
        $childrengender=  $request->input('gender');

       if(!empty($fileNo))
        {
            $this->childrenSetUp($data['staffID'], $childrenname, $childrendob,  $childrengender);

            $d = Session::get('progress');
            if($d<12) { $this->setProgress($fileNo,12); }
            return redirect('/staff-documentation-previous-employment');
        }

 	return redirect('/staff-documentation')->with('error','An error occured');

    }


  public function getPrevEmployment()
    {
        $data =$this->getTabLevel(8);
        $fileNo = Session::get('fileNo');

        $data['fileNo'] = Session::get('fileNo');
        $data['staffID'] = Session::get('staffID');
        //dd($data['staffID']);
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = Session::get('StaffNames');
        $data['progress'] = $this->getProgress( $data['fileNo']);
        Session::put('progress',$data['progress']);

        if(!empty($fileNo)){
            $data['prog'] = 12;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['staffID'])->first();
            $data['prevEmployment'] = DB::Table("previous_servicedetails")->where('staffid', $data['staffID'])->get();

            /*
            $prevEmploymentPeriod = DB::table('previous_servicedetails')->where('staffid', $data['staffID'])->pluck('period');
             //dd($prevEmploymentPeriod);
            $i=0;
             foreach ($prevEmploymentPeriod as $thePeriods) {
		     $dates= (explode(" / ",$thePeriods));

			$i++;
			$data['dates'][$i]=$dates;
			}*/

           return view('StaffDocumentation.StaffDoc',$data);
        }else{
            return redirect('/staff-documentation');
        }

    }

    public function submitPrevEmployment(Request $request)
    {
    //DD($request->all());
        $staffID = Session::get('staffID');
        $fileNo = Session::get('fileNo');
        $prevemp =  $request->input('employment');
        $previousPay =  $request->input('previousPay');
        $fromPrevEmp = $request->input('fromPrevEmp');
        $toPrevEmp = $request->input('toPrevEmp');
        $filePage = $request->input('filePage');
        $checkBy = $request->input('checkedBy');
        $appt_count = $request->input('appt_count');

        //dd($fromPrevEmp);
        if(!empty($fileNo)){
        //DB::DELETE("DELETE FROM previous_servicedetails WHERE `staffid` = '$staffID'");
     	$this->previousEmploymentSetUp($staffID, $prevemp, $previousPay, $fromPrevEmp, $toPrevEmp,$filePage,$checkBy);
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
            if($d<13){
            $this->setProgress($fileNo,13);}
            return redirect('/staff-documentation-attachment');
        }

        return redirect('/staff-documentation')->with('error','An error occured');

    }

    //attachment
    public function getAttachment()
    {

        $data =$this->getTabLevel(9);
        $fileNo = Session::get('fileNo');

        $data['fileNo'] = Session::get('fileNo');
        $data['staffID'] = Session::get('staffID');

        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = Session::get('StaffNames');
        $data['progress'] = $this->getProgress( $data['fileNo']);
        Session::put('progress',$data['progress']);

        if(!empty($fileNo)){

            $data['id']=0;
            $data['staffid']=0;
            $data['prog'] = 13;

            $data['staffDETAILS'] = DB::table('tblstaffAttachment')
    	   ->select('tblstaffAttachment.staffID','tblstaffAttachment.filedesc','tblstaffAttachment.filepath','tblstaffAttachment.id')
    	   ->where('staffid','=',$data['staffID'])
    	   ->get();

            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['staffID'])->first();

            $data['prevEmployment'] = DB::Table("tblpreviousemployment_rec")->where('staffid', $data['staffID'])->get();

             $prevEmploymentPeriod = DB::table('tblpreviousemployment_rec')->where('staffid', $data['staffID'])->pluck('period');
             //dd($prevEmploymentPeriod);
            $i=0;
             foreach ($prevEmploymentPeriod as $thePeriods) {
		      $dates= (explode(" / ",$thePeriods));

			$i++;
			$data['dates'][$i]=$dates;

		}

           return view('StaffDocumentation.StaffDoc',$data);
        }else{
            return redirect('/staff-documentation');
        }

    }

    public function submitAttachment(Request $request)
    {
        $fileNo = Session::get('fileNo');

        if(!empty($fileNo)){

            $d = Session::get('progress');
            if($d<14){
             $this->setProgress($fileNo,14);}
             return redirect('/staff-documentation-account');
        }

        return redirect('/staff-documentation')->with('error','An error occured');

    }

    //save attachment
    public function saveAttachment(Request $request)
    {
        //$today = carbon::today();

        //$//date = Carbon::createFromFormat('d/m/Y', $today);

        $staffID=$request->input('staffid');
        $desc=$request->input('description');

        $this->validate($request, [

          'description'      => 'required|string',
          'filename.*' => 'mimes:pdf,doc,docx,jpeg,jpg,gif,png,bmp|max:2048',

        ]);
        $staffid= trim($request['staffid']);
        $desc= trim($request['description']);

              //processing insert into attachment table

	       if($request->hasfile('filename'))
		         {
		            foreach($request->file('filename') as $file)
		            {
		               $name=time().'.'.$file->getClientOriginalExtension();
		               //dd($name);
		               $file->move(public_path().'/../../hr.njc.gov.ng/staffattachments', $name);
		                //$file->move(public_path('attachments'), $name);

		               $getID=DB::table('tblstaffAttachment')->insertGetId([

		               'filepath' => $name,
		               'filedesc' => $desc,
		               'staffID' => $staffID,

		               ]);

		            }
		         }


              return back()->with('message', 'File Uploaded!');

     }

     //delete attachement

     public function deleteAttachement($id) {
         //dd($id);
         DB::table('tblstaffAttachment')->where('id',$id)->delete();
           return back()->with('message', 'File Removed!');

     }

    public function getAccount()
    {
        $data =$this->getTabLevel(10);
        $fileNo = Session::get('fileNo');
        $data['staffID'] = Session::get('staffID');

        $data['fileNo'] = Session::get('fileNo');
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = Session::get('StaffNames');
        $data['progress'] = $this->getProgress( $data['fileNo']);
        Session::put('progress',$data['progress']);

        if(!empty($fileNo)){
            $data['prog'] = 14;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['staffID'])->first();
            $data['BankList'] = DB::Select("SELECT * FROM `tblbanklist`");

           return view('StaffDocumentation.StaffDoc',$data);
        }else{
            return redirect('/staff-documentation');
        }

    }

    public function submitAccount(Request $request)
    {
        $fileNo = Session::get('fileNo');
        $staffID = Session::get('staffID');
        $bankID = $request->input('bankName');
        $accountNumber = $request->input('accountNumber');

        if(!empty($fileNo))
        {
             $this->accountSetUp($staffID, $bankID, $accountNumber);
             $d = Session::get('progress');
             if($d<15){
              $this->setProgress($fileNo,15);}
             return redirect('/staff-documentation-others');
        }

        return redirect('/staff-documentation')->with('error','An error occured');

    }

    public function getOthers()
    {

        $data =$this->getTabLevel(11);
        $fileNo = Session::get('fileNo');

        $data['fileNo'] = Session::get('fileNo');
        $data['staffID'] = Session::get('staffID');

        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = Session::get('StaffNames');
        $data['progress'] = $this->getProgress( $data['fileNo']);
        Session::put('progress',$data['progress']);

        if(!empty($fileNo)){
            $data['prog'] = 15;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['staffID'])->first();
            $data['otherInfo'] = DB::Table("tblotherinfoforstaffdocumentation")->where('staffid', $data['staffID'])->first();
            $data['religions'] = DB::Table('tblreligion')->get();




           return view('StaffDocumentation.StaffDoc',$data);
        }else{
            return redirect('/staff-documentation');
        }

    }

    public function submitOthers(Request $request)
    {
        $fileNo = Session::get('fileNo');
        $data['staffID'] = Session::get('staffID');
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

        if(!empty($fileNo))
        {

            $this->othersSetUp($data['staffID'], $convict, $convictReason, $illness, $illnessReason, $repay,
            $jugdement, $judgementReason, $detailInForce, $decoration, $religion, $agree);

            $d = Session::get('progress');
            if($d<15){
             $this->setProgress($fileNo,15);}
             return redirect('/staff-documentation-preview');
        }

        return redirect('/staff-documentation')->with('error','An error occured');

    }

    public function getPreview()
    {
        $fileNo = Session::get('fileNo');
        $data =$this->getTabLevel(12);
        $data['fileNo'] = Session::get('fileNo');
        $data['staffID'] = Session::get('staffID');
        //dd($data['staffID']);
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = Session::get('StaffNames');
        $data['progress'] = $this->getProgress( $data['fileNo']);
        Session::put('progress',$data['progress']);


        if(!empty($fileNo)){

            $data['data'] = DB::Table("tblper")->where('ID', $data['staffID'])->first();
            $data['prog'] = 16;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['staffID'])->first();
            $data['maritalStatus'] = DB::Table("tbldateofbirth_wife")->where('staffid', $data['staffID'])->first();
            $data['relationship'] = DB::Table("tblper")->where('ID', $data['staffID'])->value('maritalStatus');
            //dd($data['staffID']);
            $data['nextOfKin'] = DB::Table("tblnextofkin")->where('staffid', $data['staffID'])->first();
            $data['UserState'] = DB::Table("tblstates")->where('StateID', $data['data']->stateID)->first();

            $data['UserLga'] = DB::Table("lga")->where('lgaId', $data['data']->lgaID)->first();
            $data['UserBank'] = DB::Table("tblbanklist")->where('bankID', $data['data']->bankID)->first();
            $data['empType'] = DB::Table("tblemployment_type")->where('id', $data['data']->employee_type)->first();
            $data['dept'] = DB::Table("tbldepartment")->where('id', $data['data']->department)->first();
            $data['design'] = DB::Table("tbldesignation")->where('id', $data['data']->Designation)->first();

            $data['prevEmployment'] = DB::Table("previous_servicedetails")->where('staffid', $data['staffID'])->get();
            $data['children'] = DB::Table("tblchildren_particulars")->where('staffid', $data['staffID'])->get();
            $data['otherInfo'] = DB::Table("tblotherinfoforstaffdocumentation")->where('staffid', $data['staffID'])->exists();

            if($data['otherInfo']){
                $data['otherInfo'] = DB::Table("tblotherinfoforstaffdocumentation")->where('staffid', $data['staffID'])->first();
            }
            else{
                $data['otherInfo'] = '';
            }
            $data['staffAttachment'] = DB::table('tblstaffAttachment')
    	   ->select('tblstaffAttachment.staffID','tblstaffAttachment.filedesc','tblstaffAttachment.filepath','tblstaffAttachment.id')
    	   ->where('tblstaffAttachment.staffID','=',$data['staffID'])
    	   ->get();

           return view('StaffDocumentation.StaffDoc',$data);
        }else{
            return redirect('/staff-documentation');
        }
    }



    public function submitPreview(Request $request)
    {
        $fileNo = Session::get('fileNo');
        $data['staffID'] = Session::get('staffID');
        $d = Session::get('progress');

        if($d<15){
         $this->setProgress($fileNo,15);
         //$this->setProgress($fileNo,16);
         }

        //dd('hey');
        return redirect('/staff-documentation-basic-info');
    }




    public function getComplete()
    {
        $fileNo = Session::get('fileNo');

        $data['fileNo'] = Session::get('fileNo');
        $data['staffID'] = Session::get('staffID');

        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = Session::get('StaffNames');
        $data['progress'] = $this->getProgress( $data['fileNo']);

        if(!empty($fileNo)){
            $data['prog'] = 15;
            $data['staffInfo'] = DB::Table('tblper')->where('fileNo', $data['fileNo'])->first();

           return view('StaffDocumentation.StaffDoc',$data);
        }else{
            return redirect('/staff-documentation');
        }

    }

    public function submitComplete(Request $request)
    {
        $fileNo = Session::get('fileNo');
        $this->setProgress($fileNo,15);
        return redirect('/staff-documentation');
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
            default:
               dd('bleh');
        }

        return $data;
    }

}
