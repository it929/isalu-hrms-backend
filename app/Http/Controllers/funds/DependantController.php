<?php

namespace App\Http\Controllers\funds;

use Illuminate\Http\Request;
//use Auth;
use App\Http\Requests;
use DB;
use Auth;
use Session;
class DependantController extends functionController
{
	public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->middleware('auth');
        $this->username = Session::get('userName');
            return $next($request);
        });
    }//
   public function getDependant(Request $request)
   {  
	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['dependant'] = "";
	   $data['dob'] = "";
	   $data['relationship'] = "";
	   $data['gender'] = "";
	   $data['success'] = "";
	   $data['RelationList'] = DB::table('tbldependant_relationship')->select('id', 'relationship')->get();
	   $data['GenderList'] = DB::table('gender')->select('gender')->get();
	   $fileno= $this->StaffNo($this->username);
	   $data['DependantList']=$this->DependantList($fileno) ; 
	   
   	return view('Dependance.dependant', $data);
   }
   
   
   
   public function postDependant(Request $request)
   {   
   	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['dependant'] =trim($request['dependant']);
	   $data['dob'] =trim($request['dob']);
	   $data['relationship'] = trim($request['relationship']);
	   $data['gender'] = trim($request['gender']);
	   $data['success'] = "";
	   $delcode=trim($request['delcode']);
	  DB::DELETE ("DELETE FROM `tblstaff_dependants` WHERE `id`='$delcode'");
	   $data['RelationList'] = DB::table('tbldependant_relationship')->select('id', 'relationship')->get();
	   $data['GenderList'] = DB::table('gender')->select('gender')->get();
	   $fileno= $this->StaffNo($this->username);
	   $data['DependantList']=$this->DependantList($fileno) ; 
	$updatedby = $this->username;
	    $this->validate($request, [
		'dependant'      	=> 'required',
		'dob'      	   	=> 'required',
		'relationship'		=> 'required',
		'gender'	=> 'required'
		]);
		$dependantName=trim($request['dependant']);
	   
	   
		if ( isset( $_POST['add'] ) ) {
		if ($this->ConfirmDependant($fileno,$dependantName)){$data['warning'] = "This dependant $dependantName already exist with the staff";
		   return view('Dependance.dependant', $data);
		   }
		$dependantGender=trim($request['gender']);
		$dependantRelationship=trim($request['relationship']);
		$dependantDOB=trim($request['dob']);
		$dependantName=trim($request['dependant']);
		
		DB::insert("INSERT INTO `tblstaff_dependants`(`fileNo`, `dependantName`, `dependantDOB`, `RelationshipID`, `dependantGender`, `createdBy`) VALUES
		 ('$fileno','$dependantName','$dependantDOB','$dependantRelationship','$dependantGender','$updatedby')");
		$data['DependantList']=$this->DependantList($fileno) ; 
		$data['success'] = "$dependantName successfully updated";
		$data['dependant'] = "";
		$data['dob'] = "";
		$data['relationship'] = "";
		$data['gender'] = "";
			return view('Dependance.dependant', $data);
		 }
   	return view('Dependance.dependant', $data);
   }


}