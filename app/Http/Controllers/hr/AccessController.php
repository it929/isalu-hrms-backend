<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;

use App\Http\Requests;
use App\Role;
use App\User;
use Auth;
use Entrust;
use App\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as rep;
use DB;

class AccessController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

     public function create_role()
   {
    if (Rep::isMethod('post')) {
       

        $name = rep::get('name');
        $display_name = rep::get('display_name');
        $description = rep::get('description');

        $existingName = Role::where('name', '=', $name)->first();


        if ($existingName) {

            return redirect("role")->with('error_message','Role Name already exist');
    
            }

        // Storing Role using Enthrust Laravel
        $owner = new Role();
        $owner->name         = $name;
        $owner->display_name = $display_name; // optional
        $owner->description  = $description; // optional
    
            if($owner->save())
            {
    
        return redirect("role")->with('message','Operation was performed successfully');
            }else{
          
        return redirect("role")->with('error_message','Operation failed ');
    
            }
 
    } //End Post
        
   
   }


     public function create_permission()
   {
    if (Rep::isMethod('post')) {
       

        $name = Rep::get('name');
        $display_name = Rep::get('display_name');
        $description = Rep::get('description');

        //Check if permission exist
        $existingName = Permissions::where('name', '=', $name)->first();

        if ($existingName) {

            return redirect("perm")->with('error_message','Permission Name already exist');
    
            }

             // Storing Permission using Enthrust Laravel
            $owner = new Permission();
            $owner->name         = $name;
            $owner->display_name = $display_name; // optional
            $owner->description  = $description; // optional

            if($owner->save())
            {

                return redirect("perm")->with('message','successful'    );
             }else{

             return redirect("perm")->with('error_message','failed');
    
              }
        
             } //End Post
        
   
   }





    public function attach_role()
   {
        if (Rep::isMethod('post')) {
       
        $userid = Rep::get('uid');
        $roleid = Rep::get('roleid');
        
        $user = User::where('id', '=', $userid)->first();
       

           // $affected=$user->attachRole($roleid);
            $user->attachRole($roleid);


                return redirect("user_role")->with('message','successful'  );
               
                }
        
             

             } //End Post
        
   
   




 public function attach_per_to_role()
   {
        if (Rep::isMethod('post')) 
        {
       

            //$userid = Request::get('id');
            $roleid = Rep::get('roleid');
            $permid = Rep::get('permid');

            $admin = new Role();
            $admin->id=$roleid;
            $admin->attachPermission($permid);
         
            return redirect("role_perm")->with('message','successful'    );

         }//end post
        
               
               
   } //End method
        
   
   




    //Returning Role_permission Form
    public function role_perm_form()
        {
            $data['error_message']=null;
            $data['message']=null;

     // Populate permission Name
     $data['perm_name'] = DB::table('permissions')->select('id', 'name')->orderBy('name', 'asc')->get();
  //populate Rolename dropdown
     $data['role_name'] = DB::table('roles')->select('id', 'name')->orderBy('name', 'asc')->get();

     
    return view('access.role_permission', $data);
    }






        //Returning user_role Form
        public function user_role_form()
        
        {
          
        //popoulate username  dropdown
        $data['user_name'] = DB::table('users')->select('id', 'name')->orderBy('name')->get();

        //populate Rolename dropdown
        $data['role_name'] = DB::table('roles')->select('id', 'name')->orderBy('name')->get();

        return view('access.user_role', $data);
        
        }

        //Returning Permission Form
        public function perm_form()
        { 
            $data['error_message']=null;
            $data['message']=null;
        
            return view('access.permission');
        }

        //Returning Role Form
          public function role_form()
        { 
            $data['error_message']=null;
            $data['message']=null;
            return view('access.role');
        }


}
