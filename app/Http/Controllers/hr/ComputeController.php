<?php

namespace App\Http\Controllers\hr;

use Illuminate\Http\Request;
//use Illuminate\Http\Support\Facade\View;
use App\Http\Requests;
use DB;

class ComputeController extends ParentController
{
    public function loadView($value = 'all')
    {
    	$data['staffList'] = $this->getStaffList();
        $data['courts']     = DB::table('tbl_court')->get();

    	if (view()->exists('compute.'.$value))
    	{
    		return view('compute.'.$value, $data)->render();
    	}
    	else
			return redirect('/');
    }

    public function getActiveMonth(Request $request)
    {
        $court = $request['courtID'];
        $active     = DB::table('tblactivemonth')->where('courtID','=', $court)->orderBy('activemonthID','DESC')->get();
        return response()->json($active);
    }

    public function getDivisions(Request $request)
    {
        $court   = $request['courtID'];
        $get     = DB::table('tbldivision')->where('courtID','=', $court)->get();
        return response()->json($get);
    }

    public function getStaff(Request $request)
    {
        $div     = $request['divisionID'];
        $get     = DB::table('tblper')->where('divisionID','=', $div)->get();
        return response()->json($get);
    }

     
}