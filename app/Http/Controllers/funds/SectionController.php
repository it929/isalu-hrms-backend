<?php
//normal staff userid 6 & 237 & 243
//department head userid 26 & 241
//Executive Secretary userid 28
namespace App\Http\Controllers\funds;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests;
use Illuminate\Support\Facades\Storage;
use DB;
use File;
use Auth;
use Session;

class SectionController extends Controller
{
    
    //make this page accessible only by authenticated user
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->middleware('auth');
                return $next($request);
        });
    }
    
    
   //controllers for normal staff
    public function index()
    {
    	Session::put('username',Auth::User()->username);
    	
    	$data['users'] = DB::table('users')->get();
    	$data['section'] = DB::table('tblsections')->get();
    	
    	$data['staff_section'] = DB::table('tblstaff_section')
    	->leftjoin('tblsections','tblstaff_section.section','=','tblsections.code')
    	->leftjoin('users','tblstaff_section.user_id','=','users.id')
    	->select('*','tblstaff_section.id as secID','tblsections.id as sID','users.id as uID')
    	->get();
      	
        return view('addsection.add', $data);
    }
    
    //Add new claim
    public function addSection(Request $request)
    {
        $this->validate($request,[
            
            'users'=>'required',
            'section'=>'required',
            
            ]);
            
        $users      = $request->input('users');
        $sections   = $request->input('section');
        
        if(DB::table('tblstaff_section')->where('section',$sections)->where('user_id',$users)->exists())
        {
            return back()->with('error','Record exists');
        }
        else
        {
            DB::table('tblstaff_section')->insert(['section'=>$sections, 'user_id'=>$users]);
        }
        
        return back()->with('success','Added Successfully');

    }
    
    public function deleteSection($id)
    {
        DB::table('tblstaff_section')->where('Id',$id)->delete();
        return back()->with('success','Delete Successfully');
    }
    
    public function updateSection(Request $request)
    {
        $id      = $request->input('sectionID');
        $users      = $request->input('user_id');
        $sections   = $request->input('dept_id');
        
        DB::table('tblstaff_section')->where('Id',$id)->update(['section'=>$sections, 'user_id'=>$users]);
        
        return back()->with('success','Updated Successfully');
    }
    
    //Start pushing claim to HOD
    
  
}//End class