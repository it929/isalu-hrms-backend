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
        $data['StaffNames'] = '';
        $data['progress'] = '';
        $data['prog']='';
        //dd($data['progress']);
        return view('StaffDocumentation.StaffDoc',$data);
    }
 

    //Controller for when you search for a particular Staff
    public function getStaffInfo(Request $request)
    {
       
        $data['fileNo'] = $request->input('fileNo');
        Session::put('fileNo',$data['fileNo']);
        $data['StaffList'] = $this->getStaffList();
        Session::put('StaffList', $data['StaffList']);
        $staff = $this->getStaff($data['fileNo']);
        
        if(!$staff)return redirect('/staff-documentation');
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
            return redirect('/staff-documentation-account');
                break;
            case 14:
            return redirect('/staff-documentation-others');
                break;
            case 15:
            return redirect('/staff-documentation-preview');
                break;
            default:
            return redirect('/staff-documentation');
        }
    }

    public function getBasicInfo()
    {
        
        $data =$this->getTabLevel(2);
        $fileNo = Session::get('fileNo');

        $data['fileNo'] = Session::get('fileNo');
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = Session::get('StaffNames');
        $data['progress'] = $this->getProgress( $data['fileNo']); 
        Session::put('progress',$data['progress']); 
//dd($fileNo);
        if(!empty($fileNo)){
            $data['prog'] = 6;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
            
           
           return view('StaffDocumentation.StaffDoc',$data);
        }else{
            return redirect('/staff-documentation');
        }

    }

    public function submitBasicInfo(Request $request)
    {
        
        $fileNo = Session::get('fileNo');
        $title= $request->input('title');
        $gender= $request->input('gender');
       
//dd("jksjxj");
       
        if(!empty($fileNo))
        {
       
            $this->basicSetUp($fileNo, $title, $gender);
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
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = Session::get('StaffNames');
        $data['progress'] = $this->getProgress( $data['fileNo']);
        Session::put('progress',$data['progress']); 
        
        if(!empty($data['fileNo'])){
            $data['prog'] = 7;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
          
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
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = Session::get('StaffNames');
        $data['progress'] = $this->getProgress( $data['fileNo']);
        Session::put('progress',$data['progress']); 

        if(!empty($fileNo)){
            $data['prog'] = 8;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
         
            $data['StateList'] = DB::Select("SELECT * FROM `tblstates`");
            $lgaID = DB::Table('tblper')->where('ID', $data['fileNo'])->value('lgaID');
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
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = Session::get('StaffNames');  
        $data['progress'] = $this->getProgress( $data['fileNo']); 
        Session::put('progress',$data['progress']); 

        if(!empty($data['fileNo'])){
           $data['prog'] = 9;
           $data['status'] = DB::Table('tblmaritalStatus')->pluck('marital_status');
           $data['maritalStatus'] = DB::Table("tbldateofbirth_wife")->where('staffid', $data['fileNo'])->first();
           $data['relationship'] = DB::Table("tblper")->where('ID', $data['fileNo'])->value('maritalStatus');
           return view('StaffDocumentation.StaffDoc',$data);
        }else{
            return redirect('/staff-documentation');
        }
    }

    
    
     //Controller for submitting the 'Marital Information' Page.
    public function submitMarital(Request $request)
    {
    
     
    //dd($request->all());
        $fileNo = Session::get('fileNo');
        $maritalStatus= $request->input('status');
        $dom= $request->input('dataOfMarriage');
        
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
        
        
         $this->relationshipSetUp($fileNo, $maritalStatus,$dom, $fullname, $dob, $address);
        
           
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
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = Session::get('StaffNames');
        $data['progress'] = $this->getProgress( $data['fileNo']);
        Session::put('progress',$data['progress']); 

        if(!empty($fileNo)){
            $data['prog'] = 10;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
            //$data['nextOfKin'] = DB::Table("tblnextofkin")->where('staffid', $data['fileNo'])->first();
            $data['nextOfKins'] = DB::Table("tblnextofkin")->where('staffid', $data['fileNo'])->get();
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
        
        $fullName = $request->input('fullName');
        $phoneNumber = $request->input('phoneNumber');
        $physicalAddress = $request->input('physicalAddress');
        $relationship = $request->input('relationship');
        
        
        if(!empty($fileNo))
        {
            DB::Delete("DELETE FROM `tblnextofkin` WHERE `staffid`='$fileNo'");
            for ($i = 1; $i <= count($_POST['fullName']); $i++) {
            //echo $_POST['fullName'][$i];
            $this->nextOfKinSetUp($fileNo, $_POST['fullName'][$i], $_POST['phoneNumber'][$i], $_POST['physicalAddress'][$i], $_POST['relationship'][$i]);
}

            
             $d = Session::get('progress');
             if($d<11){
              $this->setProgress($fileNo,11);}
             return redirect('/staff-documentation-children');
        }
       
        return redirect('/staff-documentation')->with('error','An error occured');
 
    }


	public function getChildren()
    {
        
        $data =$this->getTabLevel(7);
        $fileNo = Session::get('fileNo');

        $data['fileNo'] = Session::get('fileNo');
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = Session::get('StaffNames');  
        $data['progress'] = $this->getProgress( $data['fileNo']);
        Session::put('progress',$data['progress']); 

        if(!empty($fileNo)){
            $data['prog'] = 11;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
            $data['children'] = DB::Table("tblchildren_particulars")->where('staffid', $data['fileNo'])->get();
            
           
           return view('StaffDocumentation.StaffDoc',$data);
        }else{
            return redirect('/staff-documentation');
        }

    }

    public function submitChildren(Request $request)
    {    //dd($request->all());
         $fileNo = Session::get('fileNo');        
        $childrenname=  $request->input('fullname'); 
        $childrendob=  $request->input('childDateOfBirth'); 
        $childrengender=  $request->input('gender'); 
        
       if(!empty($fileNo))
        { 
       $this->childrenSetUp($fileNo, $childrenname, $childrendob,  $childrengender);
        
            $d = Session::get('progress');
            if($d<12){
             $this->setProgress($fileNo,12);}
             return redirect('/staff-documentation-previous-employment');
        }
 
 	return redirect('/staff-documentation')->with('error','An error occured');
 
    }
   
   
  public function getPrevEmployment()
    {
        $data =$this->getTabLevel(8);
        $fileNo = Session::get('fileNo');

        $data['fileNo'] = Session::get('fileNo');
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = Session::get('StaffNames');  
        $data['progress'] = $this->getProgress( $data['fileNo']);
        Session::put('progress',$data['progress']); 

        if(!empty($fileNo)){
            $data['prog'] = 12;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
            $data['prevEmployment'] = DB::Table("tblpreviousemployment_rec")->where('staffid', $data['fileNo'])->get();
            
             $prevEmploymentPeriod = DB::table('tblpreviousemployment_rec')->where('staffid', $data['fileNo'])->pluck('period');
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

    public function submitPrevEmployment(Request $request)
    {    
    //DD($request->all());
    
        $fileNo = Session::get('fileNo');        
        $prevemp =  $request->input('employment'); 
        $appointheld =  $request->input('appointmentheld'); 
        $fromPrevEmp = $request->input('fromPrevEmp');
        $toPrevEmp = $request->input('toPrevEmp'); 
        
        if(!empty($fileNo)){
        
     	$this->previousEmploymentSetUp($fileNo, $prevemp, $appointheld , $fromPrevEmp, $toPrevEmp);
            
            $d = Session::get('progress');
            if($d<13){
             $this->setProgress($fileNo,13);}
             return redirect('/staff-documentation-account');
        }
        
        return redirect('/staff-documentation')->with('error','An error occured');

    }


    public function getAccount()
    {
        $data =$this->getTabLevel(9);
        $fileNo = Session::get('fileNo');

        $data['fileNo'] = Session::get('fileNo');
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = Session::get('StaffNames');  
        $data['progress'] = $this->getProgress( $data['fileNo']);
        Session::put('progress',$data['progress']); 

        if(!empty($fileNo)){
            $data['prog'] = 13;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
            $data['BankList'] = DB::Select("SELECT * FROM `tblbanklist`");
           
           return view('StaffDocumentation.StaffDoc',$data);
        }else{
            return redirect('/staff-documentation');
        }

    }

    public function submitAccount(Request $request)
    {    
        $fileNo = Session::get('fileNo');
        $bankID = $request->input('bankName');
        $accountNumber = $request->input('accountNumber');

        if(!empty($fileNo))
        {
             $this->accountSetUp($fileNo, $bankID, $accountNumber);
             $d = Session::get('progress');
             if($d<14){
              $this->setProgress($fileNo,14);}
             return redirect('/staff-documentation-others');
        }
       
        return redirect('/staff-documentation')->with('error','An error occured');
 
    }

    public function getOthers()
    {
        
        $data =$this->getTabLevel(10);
        $fileNo = Session::get('fileNo');

        $data['fileNo'] = Session::get('fileNo');
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = Session::get('StaffNames');  
        $data['progress'] = $this->getProgress( $data['fileNo']);
        Session::put('progress',$data['progress']); 

        if(!empty($fileNo)){
            $data['prog'] = 14;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
            $data['otherInfo'] = DB::Table("tblotherinfoforstaffdocumentation")->where('staffid', $data['fileNo'])->first();
            $data['religions'] = DB::Table('tblreligion')->pluck('Religion');
           
            
            
           
           return view('StaffDocumentation.StaffDoc',$data);
        }else{
            return redirect('/staff-documentation');
        }

    }

    public function submitOthers(Request $request)
    {    
        $fileNo = Session::get('fileNo');        
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

            $this->othersSetUp($fileNo, $convict, $convictReason, $illness, $illnessReason, $repay, 
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
        $data =$this->getTabLevel(11);
        $data['fileNo'] = Session::get('fileNo');
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = Session::get('StaffNames');  
        $data['progress'] = $this->getProgress( $data['fileNo']);
        Session::put('progress',$data['progress']); 

        if(!empty($fileNo)){
            $data['prog'] = 15;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
            $data['maritalStatus'] = DB::Table("tbldateofbirth_wife")->where('staffid', $data['fileNo'])->first();
            $data['relationship'] = DB::Table("tblper")->where('ID', $data['fileNo'])->value('maritalStatus');
            $data['nextOfKin'] = DB::Table("tblnextofkin")->where('staffid', $data['fileNo'])->first();
            $data['UserState'] = $this->getValueName($data['staffInfo']->stateID,'tblstates', 'StateID');
            $data['UserLga'] = $this->getValueName($data['staffInfo']->lgaID, 'lga', 'lgaId');
            $data['UserBank'] = $this->getValueName($data['staffInfo']->bankID, 'tblbanklist', 'bankID');
            $data['prevEmployment'] = DB::Table("tblpreviousemployment_rec")->where('staffid', $data['fileNo'])->get();
            $data['children'] = DB::Table("tblchildren_particulars")->where('staffid', $data['fileNo'])->get();
            $data['otherInfo'] = DB::Table("tblotherinfoforstaffdocumentation")->where('staffid', $data['fileNo'])->first();
	//dd($data);
         
           return view('StaffDocumentation.StaffDoc',$data);
        }else{
            return redirect('/staff-documentation');
        }
    }
    
    

    public function submitPreview(Request $request)
    {     
        $fileNo = Session::get('fileNo'); 
        $d = Session::get('progress');
        if($d<16){
         $this->setProgress($fileNo,15);
         //$this->setProgress($fileNo,16);
         }
        //dd('hey');
        return redirect('/staff-documentation-complete');
    }




    public function getComplete()
    {
        $fileNo = Session::get('fileNo');

        $data['fileNo'] = Session::get('fileNo');
        $data['StaffList'] = Session::get('StaffList');
        $data['StaffNames'] = Session::get('StaffNames');  
        $data['progress'] = $this->getProgress( $data['fileNo']);

        if(!empty($fileNo)){
            $data['prog'] = 16;
            $data['staffInfo'] = DB::Table('tblper')->where('ID', $data['fileNo'])->first();
           
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
            default:
               dd('bleh');     
        }

        return $data;
    }

}
