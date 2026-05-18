<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
//use Auth;
use App\Http\Requests;
use DB;
use Auth;
use Session;
class StaffDesignationController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

     public function displayForm()
     {
       $data['users']=DB::table('users')->get();
       $data['sections']=DB::table('tblsections')->get();

       $data['staffsections']=DB::table('tblstaff_section')
       ->leftjoin('users', 'users.id', '=', 'tblstaff_section.user_id')
       ->leftjoin('tblsections', 'tblsections.code', '=', 'tblstaff_section.section')
       ->select('users.name','users.username','tblsections.code','tblsections.section','tblstaff_section.id','tblstaff_section.user_id')
       ->get();

       return view('StaffDesignation.designation',$data);

     }

     public function assignDesignation(Request $request)
     {

         $users=$request->input('users');
         $sections=$request->input('sections');

         $this->validate($request, [

          'users'      => 'string',
          'sections'     => 'string',

        ]);
         if($check=DB::table('tblstaff_section')->where('section',$sections)->where('user_id',$users)->first()){

              return redirect('staff/designation')->with('message','User existed!');
           }
        else{

               if(DB::table('tblstaff_section')->where('user_id',$users)->exists())
               {

                      //DB::table('tblstaff_section')->where('user_id',$users)->delete();

        	          DB::table('tblstaff_section')->where('user_id',$users)->update([
        	          'section'          => $sections,
        	          'user_id'         => $users,

        	          ]);


               }
               else
               {
                    DB::table('tblstaff_section')->insert([
        	          'section'          => $sections,
        	          'user_id'         => $users,

        	          ]);
               }


         }
          return redirect('staff/designation')->with('message','User Assigned!');

     }

     public function deleteDesignation($id)
     {

         $delete=DB::table('tblstaff_section')->where('id',$id )->delete();
         return redirect('staff/designation')->with('message','Deleted!');


     }


}
