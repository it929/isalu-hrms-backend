<?php

namespace App\Http\Controllers\funds;

use App\Http\Requests;
use App\Role;
use App\User;
use Auth;
use Entrust;
use App\Permission;
use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\Controller;




class AuditLogController extends ParentController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
  
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    
  public function create()
  {
      $divisions = DB::select('select * from tbldivision');
      //dd($users);
      return view('auditLog.create',['divisions'=>$divisions]);    
  }

  public function finduser(Request $request)
  {
      $query = $request->input('division');
      $search = DB::table('users')->select('id', 'name')
      ->where('divisionID', '=', $query)->get();
      $return_array = null;
      foreach($search as $s)
      {
        $return_array[]  =  ["value"=>$s->name, "data"=>$s->id];
      } 
     return response()->json($return_array);
  }

 public function userQuery(Request $request)
  {
      $query = trim($request->input('query'));
      $this->validate
            ($request,[       
          'query' => 'required',    
                     ]);
     $data['audit_detail'] = DB::table('users')
                      ->join('tbldivision', 'tbldivision.divisionID', '=', 'users.divisionID')
                       ->join('audit_log', 'users.id', '=', 'audit_log.user_id')
                      ->where('audit_log.operation', 'LIKE', '%' .$query. '%')->orderBy('audit_log.date', 'Dsc')
                       ->get(array(
                                    'users.id',
                                    'users.name',
                                    'users.username',
                                    'audit_log.operation',
                                    'audit_log.date',
                                    'audit_log.referer',
                                    'tbldivision.division',
                                        ));
    return view('auditLog.details', $data);
    }       

      public function userDetails(Request $request)
      {
      $divisionid = $request->input('division');
      $userid = $request->input('userName');
      $startdate = $request->input('startDate');
      $enddate = $request->input('endDate');
      $this->validate
            ($request,[       
          'userName' => 'required|integer',    
          'startDate' => 'required|Date',
          'endDate' => 'required|Date',
             ]);            
     $data['audit_detail'] = DB::table('users')

                      ->join('tbldivision', 'tbldivision.divisionID', '=', 'users.divisionID')
                       ->join('audit_log', 'users.id', '=', 'audit_log.user_id')
                      ->where('user_id', '=', $userid)
                      ->where('date', '>=', $startdate)
                      ->where('date', '<=', $enddate)->orderBy('audit_log.date', 'Dsc')
                      ->get(array(
                                    'users.id',
                                    'users.name',
                                    'users.username',
                                    'audit_log.operation',
                                    'audit_log.date',
                                    'audit_log.referer',
                                    'tbldivision.division',
                                        ));
    return view('auditLog.details', $data);
    }
}