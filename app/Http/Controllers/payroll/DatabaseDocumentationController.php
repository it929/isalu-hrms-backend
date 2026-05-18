<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use Carbon\Carbon;
class DatabaseDocumentationController extends Controller
{
    Public function getStaffList(){
		$List= DB::Select("SELECT * FROM `tblper` WHERE progress_regID >= 6");
		return $List; 
    }



    public function basicSetUp($fileNo, $title, $gender){
        DB::table('tblper')->where('ID', '=', $fileNo)->update(['title'=>$title,
        'gender'=>$gender]);
    }


    
    public function getMarriageInfo( $fileNo ){
		$list = DB::table("tbldateofbirth_wife")->where('fileNo', $fileNo)->first();
        return $list;
    }


    
    Public function getStaff($fileNo){
    //dd("SELECT * FROM `tblper` WHERE `ID`='$fileNo'");
		$List= DB::Select("SELECT * FROM `tblper` WHERE `ID`='$fileNo'");
		return $List;
    }


  
    Public function getProgress($fileNo){
      $progress= DB::table("tblper")->where('ID', $fileNo)->value('progress_regID');
      return $progress;
    }


  
    Public function setProgress($fileNo, $prog){
      DB::table('tblper')->where('ID',$fileNo)->update(['progress_regID'=>$prog]);		
    }


  
    Public function relationshipSetUp($staffid, $maritalstatus,$dom, $fullname, $dob, $address)
    {
      DB::UPDATE("UPDATE tblper SET maritalstatus = '$maritalstatus' WHERE `ID` = '$staffid'");
          if($maritalstatus == 'Single')
          {
              DB::table('tbldateofbirth_wife')->where('staffid', '=', $staffid)->delete();
              
          }
          $fileNo=$this->fileD($staffid);
          if($maritalstatus == 'Married'){
              $ifExists = DB::table('tbldateofbirth_wife')->where('fileNo', '=', $fileNo)->exists();
              if($ifExists){    
                  DB::table('tbldateofbirth_wife')->where('staffid', '=', $staffid)->update(
                      ['staffid' => $staffid,'dateofmarriage' => $dom,
                      'wifename' => $fullname, 'wifedateofbirth' => $dob, 'homeplace' => $address]
                  );     
              }else{
                 
                  DB::table('tbldateofbirth_wife')->insert(
                      ['staffid' => $staffid,'fileNo' => $fileNo, 'dateofmarriage' => $dom,
                      'wifename' => $fullname, 'wifedateofbirth' => $dob, 'homeplace' => $address]
                  );     
              }
          }
    }



    Public function contactSetUp($fileNo, $email, $alternateEmail,
    $phone, $alternativePhone, $physicalAddress)
    {
      DB::table('tblper')->where('ID',$fileNo)->update(['email'=>$email,
      'alternate_email'=>$alternateEmail,'phone'=>$phone,
      'alternate_phone'=>$alternativePhone,'home_address'=>$physicalAddress,]);
    }



    Public function nextOfKinSetUp($staffid, $fullName,$phoneNumber, $physicalAddress, $relationship)
    {
    $fileNo=$this->fileD($staffid); 
          DB::table('tblnextofkin')->insert(
              ['staffid' => $staffid,'fileNo' => $fileNo, 'fullname' => $fullName, 'phoneno' => $phoneNumber,
              'relationship' => $relationship, 'address' => $physicalAddress, 'updated_at' => Carbon::now()]
          );     
      
    }


    Public function placeofBirthSetUp($fileNo, $state, $lga, $address)
    {
     DB::table('tblper')->where('ID', '=', $fileNo)->update(['stateID'=>$state,
     'lgaID'=>$lga,'placeofbirth'=>$address]);
    }


    Public function accountSetUp($fileNo, $bankID, $accountNumber)
    {
        DB::table('tblper')->where('ID', '=', $fileNo)->update(['bankID'=>$bankID,
     'AccNo'=>$accountNumber]);
    }


    public function previousEmploymentSetUp($staffid, $prevemp, $appointheld , $fromPrevEmp, $toPrevEmp)
    {
    
    
  	$numofemploy = count($prevemp);
     
        DB::DELETE("DELETE FROM tblpreviousemployment_rec WHERE `staffid` = '$staffid'");
      
	$fileNo=$this->fileD($staffid);
            for($i = 0; $i < $numofemploy; $i++){
           
                $employment = $prevemp[$i];
                $appointments = $appointheld[$i];
                $periods =  $fromPrevEmp[$i]." / ".$toPrevEmp[$i];
                if(!empty($employment) && !empty($appointments) && !empty($periods)){
                DB::table('tblpreviousemployment_rec')->insert(array(
		'staffid'	=> $staffid,
		'fileNo'    	=> $fileNo,
		'employment'    	=> $employment,
		'appointmentheld'    	=> $appointments,
		'period'   => $periods,		
	));
                
     $data['message'] = 'Employment record has been saved!';
                        } 
                    }     
                    
      
    }


    public function childrenSetUp($staffid, $childrenname, $childrendob,  $childrengender)
    {
       $numofchildren = count($childrenname);
                $fileNo=$this->fileD($staffid);
                 DB::DELETE("DELETE FROM tblchildren_particulars WHERE `staffid` = '$staffid'");
                //var_dump($count);
                $arr = [];

                    for($i = 0; $i < $numofchildren; $i++){

                        $fullname   = $childrenname[$i];
                        $gender     = $childrengender[$i];
                        $dob        = $childrendob[$i];

                        if(!empty($fullname) && !empty($gender) && !empty($dob)){
                        DB::table('tblchildren_particulars')->insert(array(
			'staffid'	=> $staffid,
			'fileNo'    	=> $fileNo,
			'fullname'    	=> $fullname,
			'gender'    	=> $gender,
			'dateofbirth'   => $dob,		
		));
                            $data['message'] = 'Children information has been saved!';                            
                        }
                    }
     
    }

    public function othersSetUp($staffid, $convicted, $convictreason, $illness, $illness_reason, $repay, 
    $judgement, $judgmentr, $detail_in_force, $decoration,  $religion, $agree)
    {
    $fileNo=$this->fileD($staffid);
         $chk = DB::Select("SELECT staffid FROM tblotherinfoforstaffdocumentation WHERE `staffid` = '$staffid'");
                                                    
                    if($chk){                                                                                                  

                        DB::UPDATE("UPDATE tblotherinfoforstaffdocumentation SET `qtn1` = '$convicted', `qtn2` = '$convictreason', `qtn3` = '$illness', `qtn4` = '$illness_reason', `qtn5` = '$repay', `qtn6` = '$judgement', `qtn7` = '$judgmentr', `qtn8` = '$detail_in_force', `qtn9` = '$decoration', `qtn10` = '$religion', `qtn11` = '$agree' WHERE `fileNo` = '$fileNo'");
                                                               
                        $data['message'] = 'Other information has been saved!';
                    
                    } else {
                        DB::INSERT("INSERT INTO tblotherinfoforstaffdocumentation (`staffid`,`fileNo`, `qtn1`, `qtn2`, `qtn3`, `qtn4`, `qtn5`, `qtn6`, `qtn7`, `qtn8`, `qtn9`, `qtn10`, `qtn11`) VALUES ('$staffid','$fileNo', '$convicted', '$convictreason', '$illness', '$illness_reason', '$repay', '$judgement', '$judgmentr', '$detail_in_force', '$decoration', '$religion', '$agree')");
                        $data['message'] = 'Other information has been saved!';
                    
                    }
                    
    }

    public function getValueName($id, $table, $tableId)
    {
        if(!empty($id) && !empty($table)){
            $result = DB::Table("{$table}")->where("{$tableId}", "{$id}")->first();
            return ($result === null) ? [] : $result;
        }
        else
        { return DB::select("SELECT '' as 'State' , '' as 'lga'")[0];
	 }
    }
     public function fileD($id){
     return DB::table("tblper")->where('ID', $id)->value('fileNo');
     }
}
