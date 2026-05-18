<?php

namespace App\Http\Controllers\payroll;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;

class PensionManagerController extends ParentController
{
   public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->division    = $request->session()->get('division');
        $this->divisionID  = $request->session()->get('divisionID');
                return $next($request);
        });
    }  

   /* public function index()
    {
        $data['getAllPFA'] = DB::table('tblpension_manager')->where('active', 1)->get();
        return view('pensionManager.create');
    }

    
    public function store(Request $request)
    {
        $this->validate($request, 
        [
            'pensionmgr'          => 'required|string',            
        ]);
        $mgr                      = trim($request['pensionmgr']);
        $date                     = date("Y-m-d H:s:i");
        DB::table('tblpension_manager')->insert(array( 
            'pensionManager'      => $mgr, 
            'created_at'          => $date,
            'updated_at'          => $date,
        ));
        $this->addLog('New Pension Manager Added and division: '. $this->division);
        return redirect('/pensionmanager/create')->with('msg', 'Operation was done successfully.');
                
    }

    
    public function viewmanager()
    {
        $data['show'] = DB::table('tblpension_manager')->get();
        return view('pensionmanager/list',$data);
    }*/

   
    public function edit($id)
    {
        //
    }

    
    public function update(Request $request, $id)
    {
        //
    }

    
    public function destroy($id)
    {
        //
    }
}
