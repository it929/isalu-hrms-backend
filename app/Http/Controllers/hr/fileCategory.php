<?php

namespace App\Http\Controllers\hr;

use Illuminate\Http\Request;
//use Auth;
use App\Http\Requests;
use DB;
use Auth;
use Session;
class fileCategory extends functionController
{
	public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->middleware('auth');
        $this->username = Session::get('userName');
            return $next($request);
        });
    }
    
    
    public function getNotification(Request $request)
   {  
	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	   $staffid=$this->StaffID($this->username);
	   $data['Notification']=$this->SelfNotification($staffid);
   	return view('Leave.notification', $data);
   }

   public function index()
   {
       $data['getFileCategory'] = DB::table('tblfile_category')
       ->get();
       
       return view('category.fileCategory', $data);
   }

   public function savefileCategory (Request $request)
   {
        $validated = $request->validate([
            'category' => 'required',
            // 'dept' => 'required',
            // 'stage' => 'required',
        ]);

        $category = $request->input('category');
        // $dept = $request->input('dept');
        // $stage = $request->input('stage');

       // dd($request->all());

        $category = DB::table('tblfile_category')->insert([
            'category' => $category,
            
        ]);

        if($category){
            return redirect()->back()->with('success', 'Saved, record was created successfull.');
        }
        return redirect()->back()->with('danger', 'Sorry, we cannot process your record. Please try again.');
   }

   public function editfileCategory(Request $request)
   {
    $validated = $request->validate([
        'category' => 'required',
        
    ]);

    $recordID = $request->input('recordID');
    $category = $request->input('category');
    
    //dd($request->all());

    $saved =  DB::table('tblfile_category')->where('id', $recordID)->update([
        'category' => $category,
        
    ]);
    return redirect()->back()->with('success', 'record updated!');
   }

   public function deletefileCategory($id)
   { 
       DB::table('tblfile_category')->where('id', $id)->delete();
        return back()->with('success', 'record was deleted successfully.');
   }
}