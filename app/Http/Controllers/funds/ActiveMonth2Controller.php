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

class ActiveMonthController extends ParentController
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

		$divisionid = DB::table('tbldivision')->select('divisionID')->where('division',$division)->pluck('divisionID');
		$id=$divisionid[0];
		$data['activemonth']=null; 

		$data['activemonth']= DB::table('tblactivemonth')
		->select('month','year')
		->where('divisionID','=',$id)
		->get();

		return view('activeMonth.active_month',$data);
	}



	public function store(Request $request)
	{

		$this->validate($request, [ 
			'year' => 'required|integer',
			'month' => 'required|alpha'
			]);

	//getting division value from constructor
		$division = $this->division;

		$divisionid = DB::table('tbldivision')->select('divisionID')->where('division',$division)->pluck('divisionID');
		$divisionID=$divisionid[0]; 
		$year = $request->input('year');
		$month = $request->input('month');

		$update= DB::table('tblactivemonth')
		->where('divisionID', '=',$divisionID)
		->update(array('month' => $month,'year' => $year));
		// updating current active month session
		$request->session()->put('activeMonth', $month);
		$request->session()->put('activeYear', $year);

		if($update)
		{
			//adding audit Log to the operation
			$this->addLog(" active month set  to ".$month."/".$year. " for ".$division." division  ");
			return redirect("/activeMonth/create")->with('message', ' operation was successfully performed');
		} 
		else 
		{
			return redirect("/activeMonth/create")->with('error_message',' operation h failed. Please try again');
		}
	}
}