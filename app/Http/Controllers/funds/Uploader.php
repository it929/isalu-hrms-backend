<?php

namespace App\Http\Controllers\funds;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use File;
use App\Http\Requests;
use App\StaffDoc;
use Illuminate\Support\Facades\Input;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;


class Uploader extends Controller
{
    public function documentsUpload()
    {
        //fetch all documents 
        
        //get the staff id 
        if(DB::table('tblper')->Where('userID', '=', Auth::user()->id)->first())
        {
        	$staff_id = DB::table('tblper')->Where('userID', '=', Auth::user()->id)->pluck('ID')[0];
     		$staff_docs = DB::table('staff_docs')->where('staffID', '=', $staff_id)->get();
     	}else{
     		$staff_id = DB::table('users')->Where('id', '=', Auth::user()->id)->pluck('id')[0];
     		$staff_docs = DB::table('staff_docs')->where('staffID', '=', $staff_id)->get();
     	}
     	

       return view('uploads.uploader', ['all_docs' => $staff_docs]);
    }

    public function uploadDocuments(Request $request )
    {

	//dd($request);
	
	//get the staff ID; 
	$staff_id = DB::table('tblper')->where('userID', '=', Auth::user()->id )->pluck('ID');
	
	//dd( $staff_id[0] );
	
        //validate the incoming file information 
      $this->validate($request, [
            'doc_cat'=>'required',
            'file_upload'=>'required',
            'doc_description'=> 'required',
            'file_upload' => 'required|image|mimes:jpg,jpeg,png|max:5000'
            
        ], 
        [
            'doc_cat.required'=>'Select the type of document',
            'file_upload.required'=>'A document need to be provided',
            'doc_description.required'=> 'Please give a description'
        ]);
	
        //after validation, we have access to the validated data 

        //upload the file 
        $name = Input::file('file_upload')->getClientOriginalName();
        
        //get the app url 
        $app_url = env('APP_URL');
      

        $request->file('file_upload')->move('certs',  $name);
        
        //save the upload

        if ( StaffDoc::create([
            'doc_cat'=>$request->doc_cat,
            'doc_url'=>$app_url.'/certs/'.$name,
            'doc_description'=>$request->doc_description,
            'staffID'=>$staff_id[0]
        ])) {
            //emit a success message 
            Session()->flash('doc_upload', 'Document Uploaded successfully');
        }
        else{
            Session()->flash('doc_upload', 'There was an error uploading your document');
        }
       
        return redirect()->back();

    }
    
    

    public function deleteDocument(Request $request, $doc_id){

        //$doc_id = $request->id;
        $message; 
        //delete the document by the id
       if ( StaffDoc::destroy($doc_id) ) {

       $message = ['status'=>'deleted_successfully'];
       }

       else {
        $message = ['error'=>'error_deleting_document'];
       }

    	return response()->json($message);

    }

   public function adminUpload(){
  
   	$data['staffs'] = DB::table('tblper')->select('surname','first_name', 'othernames', 'ID')->get();
   	  	
    	return view('uploads.admin_upload', $data );

    }

    public function adminUploadDocument(Request $request ){

     //validate the incoming file information 
        $file = $this->validate($request, [
         'doc_cat'=>'required',
         'file_upload' => 'required|image|mimes:jpg,jpeg,png|max:5000',
         'doc_description'=> 'required',
  
     ], 
     [
         'doc_cat.required'=>'Select the type of document',
         'file_upload.required'=>'A document need to be provided',
         'doc_description.required'=> 'Please give a description'
     ]);

     //after validation, we have access to the validated data 

     //upload the file 
     $name = Input::file('file_upload')->getClientOriginalName();
     
     //get the app url 
     $app_url = env('APP_URL');

     $request->file('file_upload')->move('certs',  $name);
     
     //save the upload

     if ( StaffDoc::create([
         'doc_cat'=>$request->doc_cat,
         'doc_url'=>$app_url.'/certs/'.$name,
         'doc_description'=>$request->doc_description,
         'staffID'=> $request->staff_id
     ])) {
         //emit a success message 
         Session()->flash('doc_upload', 'Document Uploaded successfully');
     }
     else{
         Session()->flash('doc_upload', 'There was an error uploading your document');
     }
     
     //fetch the staff docs 
     $staff_docs = DB::table('staff_docs')->where('staffID', '=', $request->staff_id )->paginate(10);
     
     //flash it to session 
     Session()->flash('staff_uploaded_docs', $staff_docs );
     
     //fetch the user details 
     $staff_details = DB::table('tblper')->where('ID', '=', $request->staff_id )->select('title', 'first_name', 'surname','othernames', 'ID')->get();
    
     return redirect()->back()->with('the_staff', $staff_details);
    
    }

    function selectUserView(){

        return view('uploads.users', ['staffs'=> DB::table('tblper')->select('surname', 'first_name', 'othernames', 'ID')->get(), 'all_docs'=> StaffDoc::get() ]  );

    }
    
    //show user documents during documents upload by an admin 

    function findUserDocumentsById(Request $request){

        $staff_id = $request->staff_id;
        
        //find the staff details 
    	$staff_details = DB::table('tblper')->where('UserID', '=', $staff_id )->select('title', 'first_name', 'surname','othernames', 'ID')->get();


       	$staff_docs = DB::table('staff_docs')->where('staffID', '=', $staff_id)->paginate(10);

      	Session()->flash('staff_docs', $staff_docs  );
      	
       // dd($staff_docs );
     	 return redirect()->back()->with('the_staff' , $staff_details);
   
       
    }
    
    //show the user documents only to admin
    
    function getDocsByStaffId(Request $request, $staff_id){

    	$staff_docs = DB::table('staff_docs')->where('staffID', '=', $staff_id)->paginate(10);

      	Session()->flash('staff_docs', $staff_docs );
      	
           //find the staff details 
        $staff_details = DB::table('tblper')
                        ->where('ID', '=', $staff_id )
                        ->select('title', 'first_name', 'surname','othernames', 'ID')->get();
            //flash the staff details to session also 
        Session()->flash('the_staff', $staff_details );
        
    
    }

}