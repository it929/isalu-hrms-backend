<?php
// namespace App\Http\Controllers\payroll;
namespace App\Http\Controllers\payroll;

use App\Http\Requests;
use App\Role;
use App\User;
use Auth;
use Entrust;
use App\Permission;
use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Session;

class SalaryScaleController extends Controller
{

    public function __construct() {}

    public function index(Request $request)
    {
        $data['count'] = '';
        $data['courts'] =  DB::table('tbl_court')->get();
        $data['emptype'] =  DB::table('tblemployment_type')->where('active', '=', 1)->get();

        return view('payroll.SalaryScale.index', $data);
    }
    public function getSalary(Request $request)
    {
        $court = $request['court'];
        $data['courts'] =  DB::table('tbl_court')->get();
        $data['count']  = DB::table('basicsalary')->where('courtID', '=', $court)->count();
        Session::put('courtID', $court);

        $data['emptype'] =  DB::table('tblemployment_type')->where('active', '=', 1)->get();

        return view('payroll.SalaryScale.index', $data);
    }

    public function customPaging($type, $court, Request $request)
    {
        $tableName = "basicsalary";
        $employee_type = $type;
        $data['employee_type'] = strtoupper($employee_type);
        $grade = "";
        $grade = $request->get('page');
        if (is_null($grade)) {
            $grade = 1;
        }
        $data['emptype'] =  DB::table('tblemployment_type')->where('active', '=', 1)->get();

        $data['current_grade'] = $grade;
        $data['report'] = DB::table($tableName)->where('employee_type', '=', $employee_type)
            ->where('courtID', '=', session('courtID'))
            ->where('grade', '=', $grade)
            ->orderby('step')->get();
        $searchResults = DB::table('basicsalary')
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
        return view('payroll.SalaryScale.salarySummary', ['results' => $paginatedSearchResults], $data);
    }
}
