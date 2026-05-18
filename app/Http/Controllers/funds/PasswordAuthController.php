<?php

namespace App\Http\Controllers\funds;

use Illuminate\Http\Request;
//use Auth;
use App\Http\Requests;
use DB;
use Auth;
use Session;
class PasswordAuthController extends functionController
{
	public function __construct()
    {
        $this->middleware('auth');
    }//
	
	
	
   public function userForgetPassword()
   {
   
	   
	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['userid'] = "";
	   $data['username'] = "";
	   $data['success'] = "";
	   $data['staffList'] = $this->getUserBasicData();
	   $data['showreset'] = false;
	   
	   //dd($data['staffList']);
	   //Die("hfhffh");
	   //$data['showreset'] = false;
	  // 
   	return view('Password.resetpassword', $data);
   }
   
   
   
   public function userResetPassword(Request $request)
   {   
   $data['username'] =trim($request['username']);
    $data['showreset'] =false;
	$data['warning'] = "";
	$data['success'] = "";
		$data['staffList'] = $this->getUserBasicData();
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
		$message="Dear $staffname, <br> Your password have been reset by admin name. <br> your login details is as follow: <br> User Name: $username <br> Password: $newpass <br> Kindly Change your password after login";
		//$retval = mail ($this->senderemail(),$to,$subject,$message,$header);	
		$retval = mail ($to,$subject,$message,$header);	
		
		$data['success'] = "Password for $staffname with staff ID $fileNo successfully reset and sent to the email: $email";
		$data['userid'] = "";
	   }
	   $data['username'] = "";
   		return view('Password.resetpassword',$data);
   }
   

}