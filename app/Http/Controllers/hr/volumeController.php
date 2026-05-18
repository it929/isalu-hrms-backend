<?php

namespace App\Http\Controllers\hr;

use Illuminate\Http\Request;
//use Auth;
use App\Http\Requests;
use DB;
use Auth;
use Session;
class volumeController extends functionController
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
       $data['getVolume'] = DB::table('tblvolume')
       ->get();
       
       return view('Volume.volume', $data);
   }

   public function saveVolume (Request $request)
   {
        $validated = $request->validate([
            'volume' => 'required',
            // 'dept' => 'required',
            // 'stage' => 'required',
        ]);

        $volume = $request->input('volume');
        // $dept = $request->input('dept');
        // $stage = $request->input('stage');

       // dd($request->all());

        $volume = DB::table('tblvolume')->insert([
            'volume_name' => $volume,
            
        ]);

        if($volume){
            return redirect()->back()->with('success', 'Saved, record was created successfull.');
        }
        return redirect()->back()->with('danger', 'Sorry, we cannot process your record. Please try again.');
   }

   public function editVolume(Request $request)
   {
    $validated = $request->validate([
        'volume' => 'required',
        
    ]);

    $recordID = $request->input('recordID');
    $volume = $request->input('volume');
    
    //dd($request->all());

    $saved =  DB::table('tblaction_stages')->where('id', $recordID)->update([
        'volume_name' => $volume,
        
    ]);
    return redirect()->back()->with('success', 'record updated!');
   }

   public function delete($id)
   {
        DB::table('tblaction_stages')->where('id', $id)->delete();
        return back()->with('success', 'record was deleted successfully.');
   }
}