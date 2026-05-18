<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StaffDetailsComparisonController extends functions22Controller
{

    public function index(Request $request)
    {
        $data['success'] = "";
        $data['error']   = "";
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }


        $data['curDivision']    = $this->curDivision(Auth::user()->id);
        $data['courtList']      = $this->getCourts();
        $data['court']          = $request['court'];
        $data['division']       = $request['division'];

        if (Auth::user()->is_global == 1) {
            # code...
            $data['courtstaff'] = DB::table('tblper')
                ->where('tblper.staff_status', '=', 1)
                ->orderBy('surname', 'Asc')
                ->get();
        } else {
            # code...
            $data['courtstaff'] = DB::table('tblper')
                ->where('tblper.divisionID', '=', $data['curDivision']->divisionID)
                ->where('tblper.staff_status', '=', 1)
                ->orderBy('surname', 'Asc')
                ->get();
        }

        if ($data['court'] !== null) {
            $data['courtdivision'] = $this->getCourtDivision($data['court']);
        }
        // dd($data['courtDivisions']);
        return view('staffStatus.index', $data);
    }

    public function curDivision($userId)
    {
        $currentDivision = DB::table("users")
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'users.divisionID')
            ->where('users.id', '=', $userId)
            ->select('tbldivision.division', 'tbldivision.divisionID')
            ->first();
        return $currentDivision;
    }


    public function search(Request $request)
    {
        $division = $request->input('division');
        $month1 = $request->input('month1');
        $month2 = $request->input('month2');
        $year1 = $request->input('year1');
        $year2 = $request->input('year2');

        // Example query to fetch data based on division, month, and year
        $data1 = DB::table("tblpayment_consolidated")
            ->where('divisionID', '=', $division)
            ->where('month', '=', $month1)
            ->where('year', '=', $year1)
            ->get();

            
        // Example query to fetch data based on division, month, and year
        $data2 = DB::table("tblpayment_consolidated")
            ->where('divisionID', '=', $division)
            ->where('month', '=', $month2)
            ->where('year', '=', $year2)
            ->get();

        // Return data as JSON response
        return response()->json(['data1' => $data1, 'data2' => $data2]);
    }
}