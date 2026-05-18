<?php

namespace App\Http\Controllers\hr;

use Illuminate\Http\Request;
use File;
use App\Http\Requests;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
// 
use DB;

class ManPowerController extends ParentController
{

    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        // $this->division = $request->session()->get('division');
        // $this->divisionID = $request->session()->get('divisionID');
                return $next($request);
        });
    }

    public function view_CENTRAL_LIST()
    {
        $centralByMonth = Session::get('centralByMonth');
        $centralByDay   = Session::get('centralByDay');
        $filterBy       = Session::get('filterBy');

        $centralByMonth = Session::forget('centralByMonth');
        $centralByDay   = Session::forget('centralByDay');
        $filterBy       = Session::forget('filterBy');

        //get All staff due for INCREMENT $this->month (by form $_GET)
        if (($centralByMonth <> "") and ($centralByDay <> "") and ($filterBy == "")) {
            $data['getCentralList'] = DB::table('tblper')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->WhereMonth('tblper.appointment_date', '=', $centralByMonth)
                ->WhereDay('tblper.appointment_date', '=', $centralByDay)
                ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
                ->where('tblper.staff_status', 1)
                ->orderBy('tblper.grade', 'Desc')
                ->orderBy('tblper.step', 'Desc')
                ->orderBy('tblper.appointment_date', 'Asc')
                ->paginate(10);
            $data['getDivision'] = DB::table('tbldivision')->orderBy('division', 'Asc')->get();
            $data['headFile'] = "CENTRAL NOMINAL ROLL: LIST OF STAFF DUE FOR INCREMENT IN ALL DIVISIONS";
            return view('ManPower.centralList', $data);
        } else if (($centralByMonth == "") or ($centralByDay == "") and ($filterBy <> "")) {
            $data['getCentralList'] = DB::table('tblper')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->where('tblper.surname', 'LIKE', '%' . $filterBy . '%')
                ->orWhere('tblper.first_name', 'LIKE', '%' . $filterBy . '%')
                ->orWhere('tblper.fileNo', 'LIKE', '%' . $filterBy . '%')
                ->orWhere('tblper.appointment_date', 'LIKE', '%' . $filterBy . '%')
                ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
                ->where('tblper.staff_status', 1)
                ->orderBy('tblper.grade', 'Desc')
                ->orderBy('tblper.step', 'Desc')
                ->orderBy('tblper.appointment_date', 'Asc')
                ->paginate(10);
            $data['getDivision'] = DB::table('tbldivision')->orderBy('division', 'Asc')->get();
            $data['headFile'] = "CENTRAL NOMINAL DISPOSITION LIST";
            return view('hr.ManPower.centralList', $data);
        } else {
            $data['getCentralList'] = DB::table('tblper')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->where('tblper.staff_status', 1)
                ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
                ->orderBy('tblper.grade', 'Desc')
                ->orderBy('tblper.step', 'Desc')
                ->orderBy('tblper.appointment_date', 'Asc')
                ->paginate(10);
            $data['getDivision'] = DB::table('tbldivision')->orderBy('division', 'Asc')->get();
            $data['headFile'] = "CENTRAL NOMINAL DISPOSITION LIST";
            return view('hr.ManPower.centralList', $data);
        }
    }

    // SEARCH CENTRAL LIST  by json
    public function search_CENTRAL_LIST_by_json()
    {
        $query = $request->input('query');
        $search = DB::table('tblper')
            ->where('tblper.surname', 'LIKE', '%' . $query . '%')
            ->orWhere('tblper.first_name', 'LIKE', '%' . $query . '%')
            ->orWhere('tblper.othernames', 'LIKE', '%' . $query . '%')
            ->orWhere('tblper.fileNo', 'LIKE', '%' . $query . '%')
            ->where('tblper.staff_status', 1)
            ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
            ->orderBy('tblper.grade', 'Desc')
            ->orderBy('tblper.step', 'Desc')
            ->orderBy('tblper.appointment_date', 'Asc')
            ->take(10)
            ->get();
        $return_array = null;
        foreach ($search as $s) {
            $return_array[]  =  ["value" => $s->surname . ' ' . $s->first_name . ' ' . $s->othernames . ' - ' . $s->fileNo, "data" => $s->fileNo];
        }
        return response()->json(array("suggestions" => $return_array));
    }

    // FILTER CENTRAL LIST
    public function view_CENTRAL_LIST_FILTER(Request $request)
    {
        // dd(2345);
        $filterBy = trim($request['fileNo']);
        $monthDay = trim($request['monthDay']);
        $rawValue = trim($request['q']);

        if ($filterBy == "" && ($monthDay == "")) {
            //Destroy specific session to repopulate all records
            Session::forget('centralByMonth');
            Session::forget('centralByDay');
            return redirect('/map-power/view/central'); //->with('err', 'No record found for '. $rawValue .'! (or You did not select from the suggestion lists)');
        }
        if (($filterBy == "") && ($monthDay != "")) {
            //get All staff due for INCREMENT $this->month (by form $_POST)
            Session::forget('filterBy');
            Session::put('centralByMonth',  Carbon::today()->month);    // PUT/SET session By Month
            Session::put('centralByDay',  Carbon::today()->day);    // PUT/SET session By Day
            return redirect('/map-power/view/central');
        }
        // PUT/SET session for filterBy fileNos or Names
        Session::forget('centralByMonth');
        Session::forget('centralByDay');
        Session::put('filterBy',  $filterBy);

        return redirect('/map-power/view/central');
    }



    public function view_ALL_CADRE_REFRESH()
    {
        $filterCadre    = Session::forget('filterCadre');      //get/pull SESSION CADER
        $filterDivision = Session::forget('filterDivision');  //get/pull SESSION DIVISION
        $getMonthDay    = Session::forget('getMonthDay');        //get/pull SESSION MONTH AND DAY SEARCH
        $filterBy       = Session::forget('filterBy');         //get/pull SESSION AUTO-SEARCH by NAME or FILENO
        return redirect()->route('loadCadre');
    }


    //STAFF LIST BY CADRE
    public function view_ALL_CADRE_LIST()
    {
        $filterCadre    = Session::get('filterCadre');      //get/pull SESSION CADER
        $filterDivision = Session::get('filterDivision');  //get/pull SESSION DIVISION
        $getMonthDay    = Session::get('getMonthDay');        //get/pull SESSION MONTH AND DAY SEARCH
        $filterBy       = Session::get('filterBy');         //get/pull SESSION AUTO-SEARCH by NAME or FILENO

        if ($filterDivision == "" and $filterCadre == "" and $getMonthDay <> "") {
            //get All staff due for INCREMENT IN $this->division and $this->month (by form $_GET)
            $getFrom = (date('Y') - 1) . '-' . (date('m')) . '-' . (date('d'));
            $data['getCentralList'] = DB::table('tblper')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->WhereMonth('tblper.appointment_date', '=', (Carbon::today()->month))
                ->WhereDay('tblper.appointment_date', '=', (Carbon::today()->day))
                //->whereBetween('tblper.appointment_date', [$getFrom, (date('Y-m-d'))])
                ->where('tblper.divisionID', '=', $this->divisionID)
                ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
                ->where('tblper.staff_status', 1)
                ->orderBy('tblper.grade', 'Desc')
                ->orderBy('tblper.step', 'Desc')
                ->orderBy('tblper.appointment_date', 'Asc')
                ->paginate(10);
            $data['getDivision'] = DB::table('tbldivision')->orderBy('division', 'Asc')->get();
            $data['headFile'] = "Nominal Roll: LIST OF STAFF DUE FOR INCREMENT IN " . $this->division;
            return view('ManPower.cadreList', $data);
        }

        //search staff record based on POST from auto-search
        if (($getMonthDay == "") and ($filterDivision == "") and ($filterCadre == "") and ($filterBy <> "")) {
            $data['getCentralList'] = DB::table('tblper')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->where('tblper.surname', 'LIKE', '%' . $filterBy . '%')
                ->orWhere('tblper.first_name', 'LIKE', '%' . $filterBy . '%')
                ->orWhere('tblper.fileNo', 'LIKE', '%' . $filterBy . '%')
                ->orWhere('tblper.appointment_date', 'LIKE', '%' . $filterBy . '%')
                ->where('tblper.divisionID', '=', $this->divisionID)
                ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
                ->where('tblper.staff_status', 1)
                ->orderBy('tblper.grade', 'Desc')
                ->orderBy('tblper.step', 'Desc')
                ->orderBy('tblper.appointment_date', 'Asc')
                ->paginate(10);
            $data['getDivision'] = DB::table('tbldivision')->orderBy('division', 'Asc')->get();
            $data['headFile'] = "All STAFF DISPOSITION LIST";
            return view('ManPower.cadreList', $data);
        }

        if ($filterDivision == "" and $filterCadre <> "") {
            $data['getCentralList'] = DB::table('tblper')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->where('tblper.section', $filterCadre)
                ->where('tblper.divisionID', '=', $this->divisionID)
                ->where('tblper.staff_status', 1)
                ->orderBy('tblper.grade', 'Desc')
                ->orderBy('tblper.step', 'Desc')
                ->orderBy('tblper.appointment_date', 'Asc')
                ->paginate(10);
            $data['getcadre'] = DB::table('tblper')->select('section')->orderBy('section', 'Asc')->distinct()->get();
            $data['headFile'] = 'STAFF Nominal Roll for ' . $filterCadre . ' CADRE';
            $data['getDivision'] = DB::table('tbldivision')->orderBy('division', 'Asc')->get();
            return view('ManPower.cadreList', $data);
        } else if ($filterDivision <> "" and $filterCadre <> "") {
            $data['getCentralList'] = DB::table('tblper')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->where('tblper.section', $filterCadre)
                ->where('tblper.divisionID', $filterDivision)
                ->where('tblper.staff_status', 1)
                ->orderBy('tblper.grade', 'Desc')
                ->orderBy('tblper.step', 'Desc')
                ->orderBy('tblper.appointment_date', 'Asc')
                ->paginate(10);
            $getDivName = DB::table('tbldivision')->select('division')->where('divisionID', $filterDivision)->first();
            $data['getcadre'] = DB::table('tblper')->select('section')->orderBy('section', 'Asc')->distinct()->get();
            $data['headFile'] = 'STAFF Nominal Roll for ' . $filterCadre . ' CADRE - ' . $getDivName->division . ' Division';
            $data['getDivision'] = DB::table('tbldivision')->orderBy('division', 'Asc')->get();
            return view('ManPower.cadreList', $data);
        } else if ($filterCadre == "" and $filterDivision <> "") {
            $data['getCentralList'] = DB::table('tblper')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->where('tblper.divisionID', $filterDivision)
                ->where('tblper.section', '<>', 'CONSOLIDATED')
                ->where('tblper.staff_status', 1)
                ->orderBy('tblper.grade', 'Desc')
                ->orderBy('tblper.step', 'Desc')
                ->orderBy('tblper.appointment_date', 'Asc')
                ->paginate(10);
            $data['getcadre'] = DB::table('tblper')->select('section')->orderBy('section', 'Asc')->distinct()->get();
            $getDivName = DB::table('tbldivision')->select('division')->where('divisionID', $filterDivision)->first();
            $data['headFile'] = 'STAFF Nominal Roll for ' . $getDivName->division . ' Division';
            $data['getDivision'] = DB::table('tbldivision')->orderBy('division', 'Asc')->get();
            return view('ManPower.cadreList', $data);
        } else {
            $data['getCentralList'] = DB::table('tblper')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->where('tblper.section', '<>', 'JUDGES')
                ->where('tblper.divisionID', '=', $this->divisionID)
                ->where('tblper.staff_status', 1)
                ->orderBy('tblper.grade', 'Desc')
                ->orderBy('tblper.step', 'Desc')
                ->orderBy('tblper.appointment_date', 'Asc')
                ->paginate(10);
            $data['getcadre'] = DB::table('tblper')->select('section')->orderBy('section', 'Asc')->distinct()->get();
            $data['headFile'] = 'All STAFF DISPOSITION LIST';
            $data['getDivision'] = DB::table('tbldivision')->orderBy('division', 'Asc')->get();
            return view('ManPower.cadreList', $data);
        }
    }

    public function searchCentral(Request $request)
    {
        $fileNo = $request['nameID'];
        $data['getCentralList'] = DB::table('tblper')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
            //->where('tblper.divisionID', '=', $this->divisionID)
            ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
            ->where('tblper.staff_status', 1)
            ->where('tblper.fileNo', '=', $fileNo)
            ->orderBy('tblper.grade', 'Desc')
            ->orderBy('tblper.step', 'Desc')
            ->orderBy('tblper.appointment_date', 'Asc')
            ->get();
        $data['getDivision'] = DB::table('tbldivision')->orderBy('division', 'Asc')->get();
        $data['headFile'] = "All STAFF DISPOSITION LIST";
        return view('ManPower.centralList', $data);
        return view();
    }


    public function view_ALL_CADRE_LIST_FILTER(Request $request)
    {
        $filterCadre_raw    = trim($request['filterCadre']);
        $filterDivision_raw = trim($request['filterDivision']);
        $monthDay           = trim($request['monthDay']);
        $filterBy           = trim($request['fileNo']);

        //search by today's day
        if (($filterCadre_raw == "") and ($filterDivision_raw == "") and ($monthDay != "")) {
            //get All staff due for INCREMENT IN $this->division and $this->month (by form $_POST)
            Session::forget('filterCadre');
            Session::forget('filterDivision');
            Session::put('getMonthDay',  Carbon::today()->month);    // PUT/SET session Division
            return redirect('/map-power/view/cadre');
        }

        if ($filterDivision_raw == "" and $filterCadre_raw <> "") {
            Session::forget('filterDivision');
            Session::forget('getMonthDay');
            Session::forget('filterBy');
            Session::put('filterCadre',  $filterCadre_raw);
            return redirect('/map-power/view/cadre');
        } else if ($filterDivision_raw <> "" and $filterCadre_raw <> "") {
            Session::forget('getMonthDay');
            Session::forget('filterBy');
            Session::put('filterDivision',  $filterDivision_raw);
            Session::put('filterCadre',  $filterCadre_raw);
            return redirect('/map-power/view/cadre');
        } else if ($filterCadre_raw == "" and $filterDivision_raw <> "") {
            Session::forget('getMonthDay');
            Session::forget('filterBy');
            Session::forget('filterCadre');
            Session::put('filterDivision',  $filterDivision_raw);
            return redirect('/map-power/view/cadre');
        } else if (($filterCadre_raw == "") and ($filterDivision_raw == "") and ($monthDay == "")) {
            // PUT/SET session for filterBy fileNos or Names
            Session::forget('filterCadre');
            Session::forget('filterDivision');
            Session::forget('getMonthDay');
            Session::put('filterBy',  $filterBy);
            return redirect('/map-power/view/cadre');
        } else {
            // PUT/SET session for filterBy fileNos or Names
            Session::forget('filterCadre');
            Session::forget('filterDivision');
            Session::forget('getMonthDay');
            Session::forget('filterBy');
            return redirect('/map-power/view/cadre');
        }
    }


    //STAFF LIST BY CADRE
    public function view_ALL_INCREMENT_SO_FAR()
    {
        Session::forget('filterCadre');
        Session::forget('filterDivision');
        Session::forget('getMonthDay');
        Session::forget('filterBy');
        //get All staff due for INCREMENT SO FAR
        $data['getCentralList'] = DB::table('tblper')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
            ->Where('tblper.step', '<>', 'tblper.stepalert')
            ->Where('tblper.stepalert', '<>', "")
            ->where('tblper.divisionID', '=', $this->divisionID)
            ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
            ->where('tblper.staff_status', 1)
            ->orderBy('tblper.grade', 'Desc')
            ->orderBy('tblper.step', 'Desc')
            ->orderBy('tblper.appointment_date', 'Asc')
            ->paginate(10);
        $data['getDivision'] = DB::table('tbldivision')->orderBy('division', 'Asc')->get();
        $data['headFile'] = "Nominal Roll: LIST OF STAFF DUE FOR INCREMENT SO FAR " . $this->division;
        return view('ManPower.cadreList', $data);
    }


    public function viewBudget()
    {
        return view('ManPower.budget');
    }


    //Get Number of days in a Month
    /*$number = cal_days_in_month(CAL_GREGORIAN, 8, 2003); // 31
    echo "There were {$number} days in August 2003"; */
} //end class ProfileController
