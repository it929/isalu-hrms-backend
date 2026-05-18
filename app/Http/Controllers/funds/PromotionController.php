<?php

namespace App\Http\Controllers\funds;

use Illuminate\Http\Request;
//use Auth;
use App\Http\Requests;
use DB;
use Auth;
use Session;
class PromotionController extends functionController
{
	public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->username = Session::get('userName');
        $this->middleware('auth');
            return $next($request);
        });
    }
	
	
	
   public function GetPromotion()
   {
		
		$data['error'] = "";
		$data['warning'] = "";
		$data['success'] = "";
		$data['staffid'] = "";
		$data['prevgrade'] = "";	
		$data['prevstep'] = "";	
		$data['newgrade'] = "";	
		$data['newstep'] = "";	
		$data['approvedate'] = "";			
		$data['staffList']=DB::Select("SELECT UserID, fileNo, surname, first_name, othernames,email FROM tblper");
		$data['staffPromotion']=DB::Select("SELECT * ,(Select CONCAT(tblper.surname,' ', tblper.first_name ,' ', tblper.othernames) from tblper where tblper.fileNo=tblstaff_promotion.staffid ) as `StaffName`FROM `tblstaff_promotion` WHERE 1");
		//die("jfrjfj7888rrff");
		return view('Promotion.promotion',$data);
   }
   public function PostPromotion(Request $request)
   {   
		$data['error'] = "";
		$data['warning'] = "";
		$data['success'] = "";
		$data['staffid'] = trim($request['staffid']);
		$data['prevgrade'] = trim($request['prevgrade']);
		$data['prevstep'] = trim($request['prevstep']);
		$data['newgrade'] = trim($request['newgrade']);
		$data['newstep'] = trim($request['newstep']);
		$data['approvedate'] = trim($request['approvedate']);		
		
		$promodate		= trim($request['approvedate']);
		$prevgrade		= trim($request['prevgrade']);
		$prevstep		= trim($request['prevstep']);
		$newgrade		= trim($request['newgrade']);
		$newstep		= trim($request['newstep']);
		$staffid		= trim($request['staffid']);
		$delcode		= trim($request['delcode']);
		$updated_date	= (date('Y-m-d'));
		$updatedby 		= $this->username;
		DB::delete("DELETE FROM `tblstaff_promotion` WHERE `promoid`='$delcode'");
		if ( isset( $_POST['add'] ) ) {
		DB::insert("INSERT INTO `tblstaff_promotion`( `promodate`, `prevgrade`, `prevstep`, `newgrade`, `newstep`, `staffid`, `updated_date`, `updatedby`) 
		VALUES ('$promodate','$prevgrade','$prevstep','$newgrade','$newstep','$staffid','$updated_date','$updatedby')");
		}
		$data['staffPromotion']=DB::Select("SELECT *
		,(Select CONCAT(tblper.surname,' ', tblper.first_name ,' ', tblper.othernames) from tblper where tblper.fileNo=tblstaff_promotion.staffid ) as `StaffName`
		FROM `tblstaff_promotion` WHERE `tblstaff_promotion`.`staffid`='$staffid'");
		$data['staffList']=DB::Select("SELECT UserID, fileNo, surname, first_name, othernames,email FROM tblper");
		return view('Promotion.promotion',$data);
		//$data['staffList']=DB::table('tblper')->Get();
		//,(Select tblper.surname from tblper where tblper.fileNo=tblstaff_promotion.staffid ) as `StaffName`
		$data['staffList']=DB::Select("SELECT UserID, fileNo, surname, first_name, othernames,email FROM tblper");
	    $fileNo= trim($request['username']);
		$staffAuth=DB::table('tblper')
		->select('UserID', 'fileNo', 'surname', 'first_name', 'othernames','email')
		   ->where('tblper.fileNo', '=', $fileNo)->first();
		   
	   if(!($staffAuth))
	   {
		   $data['warning'] = "$fileNo does not exist";
			$data['error'] = "";
	   }
	   else
	   {
	   
	   
	   $email=$staffAuth->email;
	   
	   $staffname=$staffAuth->surname." " .$staffAuth->first_name." ".$staffAuth->othernames;
		if ( isset( $_POST['find'] ) ) {
			$userid=$staffAuth->UserID;
			$data['userid'] = $userid;
			 $data['error'] = "You are about to reset password for $staffname with staff ID $fileNo . The password will be sent to the email address: $email";
			 $data['showreset'] =true;
			return view('Password.resetpassword',$data); 
		 }
		$newpass = $this->RandomPassword();
	
	// Send Password function here ($newpass,$fileNo);
	$userid= trim($request['userid']);
	$encriptpass=bcrypt($newpass);
		   DB::table('users')->where('id', '=' , $userid)->update(array(  
            'password' => $encriptpass,         
            ));
		
		$username=$this->UserName($userid);
		$to = $email;
		$subject="Password Reset";
		
		$header = "From:".$this->senderemail()."\r\n";
        $header .= "MIME-Version: 1.0 \r\n";
        $header .= "Content-type: text/html \r\n";
		$message="Dear $staffname, <br> Your password have been reset. <br> your login details is as follow: <br> User Name: $username <br> Password: $newpass <br> Kindly Change your password after login";
		//$retval = mail ($this->senderemail(),$to,$subject,$message,$header);	
		
		$data['success'] = "Password for $staffname with staff ID $fileNo successfully reset and sent to the email: $email";
		$data['userid'] = "";
	   }
	   $data['username'] = "";
   		return view('Promotion.promotion',$data);
   }
   

}