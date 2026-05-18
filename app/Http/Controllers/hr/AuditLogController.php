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
use carbon\carbon;
use App\Http\Controllers\Controller;
use Session;




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
    
    public function viewLog()
    {
         $ses     = Session::get('userses');
         $sesfrom  =date('Y-m-d', strtotime(Session::get('fromSes')));
         $sesto   = date('Y-m-d', strtotime(Session::get('toSes')));
        

        //$date = \Carbon\Carbon::today()->subDays(30);
        $date = date('Y-m-d', strtotime('today - 30 days'));
        $data['users']= DB::table('users')->get();
      //dd(date('Y-m-d', strtotime('today - 30 days')));
           /* if($sesfrom != '')  
            {
                 $data['getLog']= DB::table('audit_log')
                ->join('users','users.id','=','audit_log.user_id')
                ->whereBetween('audit_log.date', [$sesfrom, $sesto])
                //->where('audit_log.user_id', '=', $ses)
                ->orderBy('audit_log.date','desc')
                ->paginate(50); 
                //dd($data['getLog']);
            }else{
                $data['getLog'] = DB::table('audit_log')
                ->join('users','users.id','=','audit_log.user_id')
                ->where('date', '>=', $date)
                ->orderBy('date','desc')
                ->paginate(50);
                 dd($data['getLog']);
            }*/
            
            $data['getLog'] = DB::table('audit_log')
                ->join('users','users.id','=','audit_log.user_id')
                ->where('date', '>=', $date)
                ->orderBy('date','desc')
                ->paginate(50);
                //dd($data['getLog']);
             
        return view('auditLog.listLog', $data);
    
    }
    
    
    
    public function searchLog(Request $request)
    {
         Session::forget('fromSes');
         Session::forget('toSes');
         Session::forget('userses');
         Session::forget('searchQuery');
           
        $this->validate($request,[       
              'dateFrom' => 'required|date',
              'dateTo'   => 'required|date',        
        ]);
        $data['users']= DB::table('users')->get();
        $datefrom = date('Y-m-d H:i:s', strtotime(trim($request['dateFrom'])));
        $dateTo   = date('Y-m-d H:i:s', strtotime(trim($request['dateTo'])));
        
        //dd($dateTo);
         $user = $request['user'];
         $request->session()->flash('date_from',$request['dateFrom']);
         $request->session()->flash('date_to',$request['dateTo']);
         $request->session()->flash('user_ses',$request['user']);

         Session::put('fromSes', $datefrom);
         Session::put('toSes', $dateTo);
         Session::put('userses', $request['user']);
         
         if($datefrom != '' && $dateTo != '')
         {
         $data['getLog']= DB::table('audit_log')
                ->join('users','users.id','=','audit_log.user_id')
                ->whereBetween('audit_log.date', [$datefrom, $dateTo])
                //->where('audit_log.user_id', '=', $ses)
                ->orderBy('audit_log.date','desc')
                ->paginate(50); 
         }
          if($user != '')
         {
         $data['getLog']= DB::table('audit_log')
                ->join('users','users.id','=','audit_log.user_id')
                //->whereBetween('audit_log.date', [$datefrom, $dateTo])
                ->where('audit_log.user_id', '=', $ses)
                ->orderBy('audit_log.date','desc')
                ->paginate(50); 
         }
     
    return view('auditLog.listLog', $data);
    
    
    
  
 
    }
}