<?php

namespace App\Http\Controllers\payroll;



use App\Http\Requests;
use App\Role;
use App\User;
use Entrust;
use App\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\payroll\ParentController;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ConSalaryScaleController extends ParentController
{

    public function __construct() {}

    public function index(Request $request)
    {
        $data['count'] = '';
        $data['courts'] =  DB::table('tbl_court')->get();
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }
        $data['emptype'] =  DB::table('tblemployment_type')->where('active', '=', 1)->get();

        return view('payroll.conpayroll.SalaryScale.index', $data);
    }
    public function getSalary(Request $request)
    {
        $court = $request['court'];
        $data['courts'] =  DB::table('tbl_court')->get();
        $data['count']  = DB::table('basicsalaryconsolidated')->where('courtID', '=', $court)->count();
        Session::put('courtID', $court);

        $data['emptype'] =  DB::table('tblemployment_type')->where('active', '=', 1)->get();

        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        return view('payroll.conpayroll.SalaryScale.index', $data);
    }

    public function customPagingOLD($type, $court, Request $request)
    {
        $tableName = "basicsalaryconsolidated";
        $employee_type = $type;
        $data['employee_type'] = strtoupper($employee_type);
        $grade = "";
        $grade = $request->get('page');
        if (is_null($grade)) {
            $grade = 1;
        }
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }
        $data['current_grade'] = $grade;
        $data['report'] = DB::table($tableName)->where('employee_type', '=', $employee_type)
            ->where('courtID', '=',  $data['CourtInfo']->courtid)
            ->where('grade', '=', $grade)
            ->orderby('step')->get();
        $searchResults = DB::table('basicsalaryconsolidated')
            ->select('grade')
            ->distinct()
            ->get();
        //Get current page form url e.g. &page=6
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        //Create a new Laravel collection from the array data
        $collection = new Collection($searchResults);
        //Define how many items we want to be visible in each page
        $perPage = 1;
        //Slice the collection to get the items to display in current page
        $currentPageSearchResults = $collection->slice(($currentPage) * $perPage, $perPage)->all();
        //$currentPageSearchResults = $collection->slice($currentPage * $perPage, $perPage)->all();
        //Create our paginator and pass it to the view
        $paginatedSearchResults = new LengthAwarePaginator(
            $searchResults,
            count($collection) + 2,
            $perPage,
            Paginator::resolveCurrentPage(),
            ['path' => Paginator::resolveCurrentPath()]
        );
        return view('payroll.conpayroll.SalaryScale.salarySummary', ['results' => $paginatedSearchResults], $data);
    }

    public function customPagingOLD2($type, $court, Request $request)
    {
        $tableName = "basicsalaryconsolidated";

        // Fetch from either the URL param (?name=) or fallback to ID
        $employee_type = $request->get('employmentType', $type);
        // $employee_type = urldecode($type);

        $data['employee_type'] = strtoupper($employee_type);
        $grade = $request->get('page', 1);

        $data['CourtInfo'] = $this->CourtInfo();

        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }

        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        $data['current_grade'] = $grade;

        // $data['report'] = DB::table($tableName)
        //     ->where('employee_type', '=', $employee_type)
        //     ->where('courtID', '=', $data['CourtInfo']->courtid)
        //     ->where('grade', '=', $grade)
        //     ->orderBy('step')
        //     ->get();
        $data['report'] = DB::table($tableName)
            ->where('employee_type_id', '=', $type) // if your table uses an id reference
            ->where('courtID', '=', $data['CourtInfo']->courtid)
            ->where('grade', '=', $grade)
            ->orderBy('step')
            ->get();

        $searchResults = DB::table($tableName)
            ->select('grade')
            ->distinct()
            ->get();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $collection = new Collection($searchResults);
        $perPage = 1;
        $paginatedSearchResults = new LengthAwarePaginator(
            $searchResults,
            count($collection) + 2,
            $perPage,
            Paginator::resolveCurrentPage(),
            ['path' => Paginator::resolveCurrentPath()]
        );

        // return view(
        //     'payroll.conpayroll.SalaryScale.salarySummary',
        //     ['results' => $paginatedSearchResults],
        //     $data
        // );

        // dd($employee_type);
        // dd([
        //     'employee_type' => $employee_type,
        //     'courtID' => $data['CourtInfo']->courtid,
        //     'grade' => $grade
        // ]);

        return view(
            'payroll.conpayroll.SalaryScale.salarySummary',
            array_merge($data, ['results' => $paginatedSearchResults])
        );
    }


    public function customPaging($typeId, $court, Request $request)
    {
        $tableName = "basicsalaryconsolidated";

        $employmentType = DB::table('tblemployment_type')->where('id', $typeId)->first();

        if (!$employmentType) {
            abort(404, 'Employment type not found');
        }

        $data['employee_type'] = strtoupper($employmentType->employmentType);
        $grade = $request->get('page', 1);
        $data['CourtInfo'] = $this->CourtInfo();

        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }

        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        $data['current_grade'] = $grade;

        $data['report'] = DB::table($tableName)
            ->where('employee_type', '=', $typeId) // ✅ correct column name
            ->where('courtID', '=', $data['CourtInfo']->courtid)
            ->where('grade', '=', $grade)
            ->orderBy('step')
            ->get();

        $searchResults = DB::table($tableName)
            ->select('grade')
            ->distinct()
            ->get();

        $collection = new Collection($searchResults);
        $perPage = 1;
        $paginatedSearchResults = new LengthAwarePaginator(
            $searchResults,
            count($collection) + 2,
            $perPage,
            Paginator::resolveCurrentPage(),
            ['path' => Paginator::resolveCurrentPath()]
        );

        return view(
            'payroll.conpayroll.SalaryScale.salarySummary',
            array_merge($data, ['results' => $paginatedSearchResults])
        );
    }
}
