<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Http\Requests\ContactRequest;
use mail;
class ContactController extends Controller
{
     public function show()
    {
        return view("pages/contact");

    }

     public function mailPost(Request $request)
    {

          //dd($request->input('message'));

         $to = "abolarinbabatunde88@gmail.com";
        $subject= $request->input('subject');
        $from =  $request->input('email');
        $header = "From:".$from."\r\n";
        $header .= "MIME-Version: 1.0 \r\n";
        $header .= "Content-type: text/html \r\n";
        $message= $request->input('message');
        $retval = mail ($to,$subject,$message,$header); 

        return view("pages/contact")->with('msg',"message sent successfully");
        
    }
}
