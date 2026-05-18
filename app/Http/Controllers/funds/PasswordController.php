<?php

namespace App\Http\Controllers\funds;

use Illuminate\Http\Request;
//use Auth;
use App\Http\Requests;
use DB;
use Auth;
use Session;
class PasswordController extends functionController
{
	public function __construct()
    {
        //$this->middleware('auth');
    }//
	
	
	
   public function userForgetPassword()
   
	{	//die($_SERVER[HTTP_HOST]);
		$data['error'] = "";
		$data['warning'] = "";
		$data['success'] = "";
   		return view('Password.forgetpassword',$data);
   }
   public function userResetPassword(Request $request)
   {   
		$data['warning'] = "";
		$data['success'] = "";
	    $fileNo= trim($request['username']);
		$staffAuth=DB::table('tblper')
		->select('UserID', 'fileNo', 'surname', 'first_name', 'othernames','email')
		
      // ->where([
        //   ['fileNo', '=', $fileNo],
         //  ['fileNo', '<>', ''],
         //  ])
	//->orWhere([
         //  ['userID', '=', $fileNo],
          // ['userID', '<>', ''],
           //])
	//->first();
	
   
		   ->where('tblper.fileNo', '=', $fileNo)->first();
	   if(!($staffAuth))
	   {
			$data['error'] = "$fileNo does not exist";
			$data['warning'] ="$fileNo does not exist";
	   }
	   else
	   {
	   $userid=$staffAuth->UserID;
	   $email=$staffAuth->email;
	   $staffname=$staffAuth->surname." " .$staffAuth->first_name." ".$staffAuth->othernames;
	   //$newpass = $this->RandomPassword();
	   $token = md5($this->UserName($userid)). md5($this->RandomPassword());
	
	// Send Password function here ($newpass,$fileNo);
	//$encriptpass=bcrypt($newpass);
		   DB::table('users')->where('id', '=' , $userid)->update(array(  
            'resettoken' => $token,'token_status'=>'1'         
            )); 
		$data['error'] = "Password for $staffname with staff ID $fileNo successfully reset and sent to the email: $email";
		$data['success'] ="Dear $staffname, a message have been sent to your email address: $email for password reset. \r\n Kindly check your email";
		$username=$this->UserName($userid);
		$to = $email;
		$subject="Password Reset";
		
		$header = "From:".$this->senderemail()."\r\n";
	        $header .= "MIME-Version: 1.0 \r\n";
	        $header .= "Content-type: text/html \r\n";
		$message="Dear $staffname, <br> Kindly click on <a href='https://50.31.138.137/jippis/password-reset/resets/$token'>here</a>.  to change your password after login";
		$retval = mail ($to,$subject,$message,$header);	
		
	   }
   		return view('Password.forgetpassword',$data);
   }
    public function ResetPassword(Request $request,$token)
   {   
		$data['warning'] = "";
		$data['success'] = "";
		$data['invalidtoken'] = "";
		
	    $newpass= trim($request['password']);
	    //die($token$newpass);
		$staffAuth=DB::table('users')
		->select('id')
		   ->where('users.resettoken', '=', $token)
		   ->where('users.token_status', '=', '1')
		   ->where('users.resettoken', '<>', '')->first();
	   if(!($staffAuth))
	   {
			$data['invalidtoken'] = "The session does not exist or has expired";
	   }
	   else
	   {
		   
		   $userid=$staffAuth->id;
		   if ( isset( $_POST['update'] ) ) {
		   $this->validate($request, [
   			'password'	=> 'required|confirmed|min:5',
   			'confirmpassword'	=> 'required'
			]);
			$encriptpass=bcrypt($newpass);
			DB::table('users')->where('id', '=' , $userid)->update(array( 'password' => $encriptpass,'token_status'=>'0' ));
			return redirect('/login');
		}
			
	   }
	   //die($data['invalidtoken']);
   	return view('Password.forgetresetpassword',$data);
   }  

}