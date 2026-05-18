<?php

namespace App\Http\Controllers\funds;



use Redirect;
use Illuminate\Support\Facades\Request;
//use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ParentController extends Controller
{

    public function __construct(){
       //$this->middleware('auth');
    }


    public function checkUserRoute($getRoute)
    {
        //$currentPath = Route::getFacadeRoot()->current()->uri();
        //$currentAction = Route::getCurrentRoute()->getActionName();
        $userModule = DB::table('assign_user_role')
                ->join('user_role', 'user_role.roleID', '=', 'assign_user_role.roleID')
                ->join('assign_module_role', 'assign_module_role.roleID', '=', 'assign_user_role.roleID')
                ->join('module', 'module.moduleID', '=', 'assign_module_role.moduleID')
                ->join('submodule', 'submodule.submoduleID', '=', 'assign_module_role.submoduleID')
                ->where('assign_user_role.userID', '=', Session::get('userID'))
                ->where('submodule.route', '=', $getRoute)
                ->whereRaw('module.moduleID = assign_module_role.moduleID')
                ->whereRaw('user_role.roleID = assign_user_role.roleID')
                ->select('submodule.route')
                ->first();
        if($userModule){
            //$routeToSave = ltrim(rtrim($userModule->route, "/"),  "/"));
            return Session::put('access_allowed', true);
        }else{
            return Session::put('access_allowed', false);
        }

        //Check Route: per click Route checking
        //if($this->check == false){
            //Session::forget('access_allowed');
            //return Redirect('/')->with('err', 'Sorry, you are not permitted to visit this link !!!');
        //}
        //
        //$currentPath = Route::getFacadeRoot()->current()->uri();
        //$this->checkUserRoute($currentPath);
        //$this->check = Session::get('access_allowed');

    }//



    public function addLog($operation)
    {
        $ip = Request::ip();
        $url = Request::fullUrl();
        $userID = Auth::user()->id;
        $nowInNigeria = Carbon::now('Africa/Lagos');
        $cmpname = php_uname('a');
        $host = $_SERVER['HTTP_HOST'];
        DB::table('audit_log')->insert(
            ['comp_name' => $cmpname, 'user_id' => $userID, 'date' => $nowInNigeria, 'ip_addr' => $ip, 'operation' => $operation,
            'host' => $host, 'referer' => $url]);
        return;
    }

    public function getStaffList()
    {
        //DB::enableQueryLog();
        $staffList = DB::table('tblper')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
            ->select('fileNo', 'surname', 'first_name', 'othernames')
            ->where('tblper.divisionID', Session::get('divisionID'))
            ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
            ->where('tblper.staff_status', 1)
            ->orderBy('surname', 'Asc')->get();
        //dd(DB::getQueryLog());
        return $staffList;
    }

    public function getCountStaffPerDivision()
    {
        //DB::enableQueryLog();
        $countStaffList = DB::table('tblper')
            ->where('tblper.divisionID', Session::get('divisionID'))
            ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
            ->where('tblper.staff_status', 1)
            ->count();
        //dd(DB::getQueryLog());
        return $countStaffList;
    }


    public function getAlertIncrementPromotion()
    {
     //(NOTE: ONLY THOSE THAT ARE DUE FOR INCREMENT, PROMOTION ETC ARE TO BE POPULATED HERE OR NEW APPOINTMENT)
        $getStaffIncrement = DB::table('tblper')
            ->where('tblper.divisionID', '=', Session::get('divisionID'))
            ->where('tblper.step', '<>', 'tblper.stepalert')
            ->where('tblper.stepalert', '<>', '')
            ->orwhere('tblper.staff_status', '=', 9)
            ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
            ->where('tblper.staff_status', '=', 1)
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
            ->orderBy('tblper.grade', 'Desc')
            ->orderBy('tblper.step', 'Desc')
            ->orderBy('tblper.appointment_date', 'Asc')
            ->get();
        Session::forget('hideAlert');
     return $getStaffIncrement;
    }

    public function getStaffInfo()
    {
        //DB::enableQueryLog();
        $staffList = DB::table('tblper')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
            ->select('fileNo', 'surname', 'first_name', 'othernames')
            ->where('tblper.divisionID', session('courtDivision'))
            ->where('tblper.courtID', session('court'))
            ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
            ->where('tblper.staff_status', 1)
            ->orderBy('surname', 'Asc')->get();
        //dd(DB::getQueryLog());
        return $staffList;
    }

}
