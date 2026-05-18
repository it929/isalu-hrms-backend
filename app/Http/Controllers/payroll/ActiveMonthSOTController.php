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

class ActiveMonthSOTController extends ParentController
{
/**
* Create a new controller instance.
*
* @return void
*/

	public $division; 
	public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
		$this->division = $request->session()->get('division');
	            return $next($request);
        });
    }

/**
* Show the application dashboard.
*
* @return \Illuminate\Http\Response
*/

//Returning Permission Form
	public function create()
	{ 
	//getting division value from constructor
		$division = $this->division;
//die();
		//$divisionid = DB::table('tbldivision')->select('divisionID')->where('division',$division)->pluck('divisionID');
		//$id=$divisionid[0];
		$date = date('Y');
		
		$data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

		
        $data['court'] =  DB::table('tbl_court')->get();
		$data['activemonth'] = DB::table('tblactive_sot_month')
		->join('tbl_court','tbl_court.id','=','tblactive_sot_month.courtID')
		->get();
      
		return view('activeMonth.sotactive_month',$data);
	}



	public function store(Request $request)
	{

		$this->validate($request, [ 
			'year' => 'required|integer',
			'month' => 'required|alpha'
			]);

	       $court = $request['court'];
	       $year = $request['year'];
	       $month = $request['month'];
               
              $courtname = DB::table('tbl_court')->where('id','=',$court)->first();
	      $count = DB::table('tblactive_sot_month')->where('courtID','=',$court)->count();
	      if($count == 1)
	      {
		$update= DB::table('tblactive_sot_month')
		->where('courtID', '=',$court)
		->update(array('month' => $month,'year' => $year));
		// updating current active month session
		$request->session()->put('activeMonth', $month);
		$request->session()->put('activeYear', $year);
		$request->session()->put('court', $year);

		$this->addLog(" Special overtime active month set  to ".$month."/".$year. " for ".$courtname->court_name." Court  ");
		return redirect("/sotactiveMonth/create")->with('message', ' operation was successfully performed');

         }
         elseif($count == 0)
         {
		 $update= DB::table('tblactive_sot_month')
		->insert(array('month' => $month,'year' => $year,'courtID'=>$court));
		$this->addLog(" active month set  to ".$month."/".$year. " for ".$court." Court  ");
		$request->session()->put('activeMonth', $month);
		$request->session()->put('activeYear', $year);
		$request->session()->put('court', $year);
		$this->addLog(" Special overtime active month set  to ".$month."/".$year. " for ".$courtname->court_name." Court  ");
		return redirect("/sotactiveMonth/create")->with('message', ' operation was successfully performed');

         }
		
		else 
		{
			return redirect("/sotactiveMonth/create")->with('error_message',' operation has failed. Please try again');
		}
	}
}