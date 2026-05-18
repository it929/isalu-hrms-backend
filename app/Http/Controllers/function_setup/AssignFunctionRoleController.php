<?php

namespace App\Http\Controllers\function_setup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use DB;
use session;

class AssignFunctionRoleController extends Controller
{
     public function __construct()
    {   
        $this->middleware(function ($request, $next) {
            $this->roleid = $request->session()->get('current_role');
            return $next($request);
        });
    }

    public function create()
    {
        $data['subfunctions']    = DB::table('function')
        ->join('subfunction','subfunction.functionID','=','function.functionID')
        ->get();
        $data['roles']         = DB::table('user_role')->get();
        $data['assignroles']   = DB::table('assign_function_role')->get();
        $data['getrole']       = DB::table('user_role')->where('roleID','=',$this->roleid)->first();
        $data['functions']       = DB::table('function')->get();
        return view('function_setup/assignFunction/assign',$data);
    }

    
    public function assignSubFunction(Request $request)
    {
        $this->validate($request, [
            'role'                   => 'required|numeric',
            ]);

           $roleID                   = $request['role'];
           $ID                       = $request['subFunction'];
           $date                     = date('Y-m-d H:i:s');
         
         //insert assigned roles
        foreach ($ID as $key => $ID) 
        {
            $IDs                     = $request['subFunction'][$key];
            $functionID                = $request['modu'][$key];

            $data = DB::table('assign_function_role')->where('roleID','=',$roleID)->where('subfunctionID','=',$IDs)->count();
           if($data >= 1)  
           {
            DB::table('assign_function_role')->where('roleID', $roleID)->delete();
                
            DB::table('assign_function_role')->insert(array( 
            'roleID'                  => $roleID,
            'subfunctionID'           => $IDs,
            'functionID'              => $functionID,
            'created_at'              => $date,
             ));
            //}
           }
           else
           {
             DB::table('assign_function_role')->insert(array( 
            'roleID'                  => $roleID,
            'subfunctionID'           => $IDs,
            'functionID'              => $functionID,
            'created_at'              => $date,
             ));
           }
           
        }
        return redirect('assign-function/create')->with('message','Function Assigned Successfully');

    }

   public function sessionset(Request $request)
    {
         $roleid = $request['role'];
         $ses    = Session::put('current_role', $roleid);
         if($ses)
         {
            return response()->json("Successfully Set");
         }
         else
         {
         return response()->json("Not Set");
         }

    }

    
}
