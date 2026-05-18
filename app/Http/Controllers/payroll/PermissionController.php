<?php

namespace App\Http\Controllers\payroll;

use App\Http\Requests;
use App\Role;
use App\User;
use Auth;
use Entrust;
use App\Permission;
use Illuminate\Http\Request;
use DB;

class PermissionController extends ParentController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
     //   $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

   //Returning Permission Form
    public function index()
    { 
        return view('UserPermission.permission');
    }

    public function store(Request $request)
    {
       
            $this->validate($request, [
            'name' => 'required|regex:/^[\pL\s\-]+$/u|unique:permissions'
            ]);

            $name = $request->input('name');
            $display_name = $request->input('display_name');
            $description = $request->input('description');
            
                // Storing Permission using Enthrust Laravel
                $owner = new Permission();
                $owner->name         = $name;
                $owner->display_name = $display_name; // optional
                $owner->description  = $description; // optional

                if($owner->save())
                {
                //adding audit Log to the operation
                $this->addLog(" creating permission with name ".$name);
                
            return redirect("/permission/create")->with('message',' operation was successfully performed');
             
                }else{

            return redirect("/permission/create")->with('error_message','operation failed. Please try again');    
            }     
   }

    public function permRoleStore(Request $request)
    {
        $roleid = $request->input('role');
        $permid = $request->input('permission');
        $this->validate($request, [
        'role' => 'required|integer',
        'permission' => 'required|integer|unique:permission_role,permission_id,NULL,id,role_id,' . $roleid,        
        ]);

        $admin = new Role();
        $admin->id=$roleid;
        $admin->attachPermission($permid);
        //adding audit Log to the operation
        $this->addLog(" attach Role with id ".$roleid." to permission with id ".$permid);

        return redirect("/permission/permRole")->with('message','operation was successfully performed.'  );
     
    } //End method
        

             //Returning Role_permission Form
    public function permRoleCreate()
    {
        //Populate permission Name
        $data['perm_name'] = DB::table('permissions')->select('id', 'name')->orderBy('name', 'asc')->get();
        //populate Rolename dropdown
        $data['role_name'] = DB::table('roles')->select('id', 'name')->orderBy('name', 'asc')->get();

        return view('UserPermission.rolePermission', $data);
    }   

}