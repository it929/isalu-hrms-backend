<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use DB;
use Auth;
class APITestController extends Controller
{
     public function test(Request $request)
    {
        
    //$url = "http://websvcapi.nortify.com.ng/api/bundle/getbundle/token";
    $url = "http://41.73.15.98:8080/document/getRecentDocuments";
    

//Consumming bundle

    $headers = array('Accept:applicantion/json', 'Content-Type:application/json',);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	$response=curl_exec($ch);
    
    $result = json_decode($response);
    $display="";
    
    dd($result);
    }
    
    
    
}