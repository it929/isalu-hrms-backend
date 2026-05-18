<?php
namespace App\Http\Controllers\hr;
use App\Http\Requests;
use App\Role;
use App\User;
use Auth;
use Entrust;
use App\Permission;
use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\Controller;
class RoleController extends ParentController
{
/**
* Create a new controller instance.
*
* @return void
*/
public function __construct()
{
//$this->middleware('auth');
}
/**
* Show the application dashboard.
*
* @return \Illuminate\Http\Response
*/
//viewing Role users
public function index()
{
  $users = DB::select('select a.id, a.name, username,c.id as roleid, division, c.name as roleName, c.description FROM users a left join role_user b on a.id = b.user_id left join roles c on b.role_id = c.id left join tbldivision d on d.divisionID = a.divisionID 
    order By d.division, a.name asc');
// dd($users);
  return view('UserRole.viewUser',['users'=>$users]);    
}
//Returning user_role Form
public function userRoleCreate()
{
//popoulate username  dropdown
  $data['user_name'] = DB::table('users')->select('id', 'name')->orderBy('name')->get();
//populate Rolename dropdown
  $data['role_name'] = DB::table('roles')->select('id', 'name')->orderBy('name')->get();
  return view('UserRole.userRole', $data);
}
//attach Role to user
public function userRoleStore(Request $request)
{
//dd('a');
  $userid = $request->input('user');
  $roleid = $request->input('role');
  $this->validate($request,[ 
    'user' => 'required|integer',
    'role' => 'required|integer|unique:role_user,role_id,NULL,id,user_id,' . $userid,
    ]);
  $user = User::where('id', '=', $userid)->first();

// $affected=$user->attachRole($roleid);
  $user->attachRole($roleid);

//adding audit Log to the operation
  $this->addLog(" attach user with id ".$userid." to role with id ".$roleid);
  return redirect("/role/userRole")->with('message','Operation was successfully performed '  );     
}   
//Returning Role Form
public function create()
{ 
  return view('UserRole.create');
}
//inserting data for roles
public function store(Request $request)
{
  $this->validate ( $request, [
    'name' => 'required|regex:/^[\pL\s\-]+$/u|unique:roles' 
    ]);
  $name = $request->input('name');
  $display_name = $request->input('display_name');
  $description = $request->input('description');
// Storing Role using Enthrust Laravel
  $owner = new Role();
  $owner->name         = $name;
$owner->display_name = $display_name; // optional
$owner->description  = $description; // optional
if($owner->save())
{
//adding audit Log to the operation
  $this->addLog(" creating role with name ".$name);
  return redirect("/role/create")->with('message',' Operation was performed successfully');
}
else
{
  return redirect("/role/create")->with('error_message',' Operation failed. Please try again ');
}
}
//Deleting Role for a particular user
public function destroy($id,$userid)
{
  $role = DB::table('role_user')->where([
    ['user_id', '=', $userid],
    ['role_id', '=', $id],
    ])->delete();
  $url="/role/viewUser/";
  if($role){   
    return redirect($url)-> with('message','Role was removed from user successfully ');
  }else{
    return redirect($url)-> with('error_message','Unable to remove role '); 
  }
}
}
