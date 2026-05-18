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

        //$divisionid = DB::table('tbldivision')->select('divisionID')->where('division',$division)->pluck('divisionID');
        //$id=$divisionid[0];
        $date = date('Y');

        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }


        $data['courts'] =  DB::table('tbl_court')->get();
        $data['activemonth'] = DB::table('tblactivemonth')
            ->join('tbl_court', 'tbl_court.id', '=', 'tblactivemonth.courtID')
            //->where('tblactivemonth.year','=',$date)
            ->get();
        //dd($data['activemonth']);
        return view('activeMonth.active_month', $data);
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

        $courtname = DB::table('tbl_court')->where('id', '=', $court)->first();
        $count = DB::table('tblactivemonth')->where('courtID', '=', $court)->count();
        if ($count == 1) {
            $update = DB::table('tblactivemonth')
                ->where('courtID', '=', $court)
                ->update(array('month' => $month, 'year' => $year));
            // updating current active month session
            $request->session()->put('activeMonth', $month);
            $request->session()->put('activeYear', $year);
            $request->session()->put('court', $year);

            $this->addLog(" active month set  to " . $month . "/" . $year . " for " . $courtname->court_name . " Court  ");
            return redirect("/activeMonth/create")->with('message', ' operation was successfully performed');
        } elseif ($count == 0) {
            $update = DB::table('tblactivemonth')
                ->insert(array('month' => $month, 'year' => $year, 'courtID' => $court));
            $this->addLog(" active month set  to " . $month . "/" . $year . " for " . $court . " Court  ");
            $request->session()->put('activeMonth', $month);
            $request->session()->put('activeYear', $year);
            $request->session()->put('court', $year);
            $this->addLog(" active month set  to " . $month . "/" . $year . " for " . $courtname->court_name . " Court  ");
            return redirect("/activeMonth/create")->with('message', ' operation was successfully performed');
        } else {
            return redirect("/activeMonth/create")->with('error_message', ' operation h failed. Please try again');
        }
    }
}
