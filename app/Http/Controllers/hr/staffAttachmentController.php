<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Http\Controllers\MasterRolePermissionController;
use App\Http\Requests;
use Session;
use DB;
use Auth;
use Carbon\carbon;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
class staffAttachmentController extends Controller
{

	public function __construct()
    {
        $this->middleware('auth');
    }


  //launching page and displaying database record
   public function displayForm()
   {

       $data['id']=0;    
       $data['staffid']=0;
       
       $data['atth'] = DB::table('tblper')
	   ->leftjoin('tblstaffAttachment', 'tblper.ID', '=', 'tblstaffAttachment.staffID')
	   ->select('tblstaffAttachment.staffID','tblstaffAttachment.filedesc','tblstaffAttachment.filepath')
	   ->get();

       
      return view('staffAttachment.upload',$data);
   }
   
   //save attachment
    public function uploadAttachment(Request $request)
    {
        //$today = carbon::today();

        //$//date = Carbon::createFromFormat('d/m/Y', $today);

        $staffID=$request->input('staffid');
        $desc=$request->input('desc');

        $this->validate($request, [

          'desc'      => 'required|string',
          'filename.*' => 'mimes:pdf,doc,docx,jpeg,jpg,gif,png,bmp|max:2048',
          //'filename2.*' => 'mimes:pdf,doc,docx,jpeg,jpg,gif,png,bmp|max:2048',
          //'filename3.*' => 'max:2048',

        ]);
        $staffid= trim($request['staffid']);
        $desc= trim($request['desc']);
        
              //processing insert into attachment table

	       if($request->hasfile('filename'))
		         {
		            foreach($request->file('filename') as $file)
		            {
		               $name=$file->getClientOriginalName();
		               //dd($name);
		               $file->move(public_path().'/../../hr.njc.gov.ng/public/staffattachments', $name);
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
    //load document from selecting staff
    public function displayRecordURL($id)
   {  
        $data['id']=$id;
	    $data['staffid']=DB::table('tblper')->where('ID','=',$id)->first();
	    
	    $data['staffDETAILS'] = DB::table('tblper')
	    ->leftjoin('tblstaffAttachment', 'tblper.ID', '=', 'tblstaffAttachment.staffID')
	    ->select('tblper.surname','tblper.first_name','tblper.othernames','tblstaffAttachment.staffID','tblstaffAttachment.filedesc','tblstaffAttachment.filepath')
	    ->where('tblper.ID','=',$id)
	    ->get();
	   
	    
	   return view('staffAttachment.upload', $data);
	    
   }
     
   //view file
    public function ViewDOC($id)
   {

                    
           $data['VIEWRECORD'] = DB::table('tblper')
           ->where('tblper.ID',$id)
	      //->leftjoin('tblstaffAttachment', 'tblper.fileNo', '=', 'tblstaffAttachment.staffID')
	       ->select('tblper.ID','tblper.fileNo','tblper.surname','tblper.first_name','tblper.othernames')
	       ->get();
	   
	       $data['attachment'] = DB::table('tblstaffAttachment')
           ->get();

           return view('staffAttachment.searchresult',$data);
   }
   
   //delete file
    public function DeleteDOC(Request $request)
   {
            $record_id=$request->input('fileid');
            
                      
            //GET THE STAFF RECORD ID
 	       $getstaffid = DB::table('tblstaffAttachment')
           ->where('id',$record_id)
           ->first();
           
           //GET THE EQUIVALENT RECORD ID
           $getrecordid = DB::table('tblper')
           ->where('fileNo',$getstaffid->staffID )
           ->first();
           
           //DELETE ATTACHMENT
	       $delete = DB::table('tblstaffAttachment')
           ->where('id',$record_id)
	       ->delete();

	   
	      //REDIRECT TO SAME PAGE
          return back();

   }
   
  
   //search for staff
     
   public function searchindex(){
    	$members['members'] = DB::table('tblper')->get();
    	return view('staffAttachment.search',$members);
    }

  public function search($getSearch){
    	//$search = $request->input('search');
    	$search = $getSearch;
	    $members = DB::table('tblper')
	       ->where('first_name', 'like', "%$search%")
	       ->orWhere('surname', 'like', "%$search%")
	       ->orWhere('othernames', 'like', "%$search%")
	       ->select('ID','first_name', 'surname', 'othernames', 'fileNo')
	       ->get();
	       
	     return $members;

	    //return view('staffAttachment.result',$members);
    }
    
    //search for staff to attach document
    public function StaffSearch($getSearch){
    	//$search = $request->input('search');
    	$search = $getSearch;
	    $members = DB::table('tblper')
	       ->where('first_name', 'like', "%$search%")
	       ->orWhere('surname', 'like', "%$search%")
	       ->orWhere('othernames', 'like', "%$search%")
	       ->select('ID','first_name', 'surname', 'othernames', 'fileNo')
	       ->get();
	       
	     return $members;

    }


 }
