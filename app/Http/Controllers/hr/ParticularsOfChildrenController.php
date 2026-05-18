<?php
//
namespace App\Http\Controllers\hr;

use Session;
use Illuminate\Http\Request;
use App\Http\Controllers\hr\ParentController;
use App\Http\Requests;
use App\Http\Controllers;
use Carbon\Carbon;
use Auth;
use DB;

class ParticularsOfChildrenController extends ParentController
{
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->middleware(function ($request, $next) {
            // $this->division    = $request->session()->get('division');
        $this->division = Session::get('division');
        // $this->divisionID  = $request->session()->get('divisionID');
            return $next($request);
                    return $next($request);
        });
    });
    }



    public function index($staffid = Null)
    { //->
        //check if parameters are Null
        if (is_null($staffid)) {
            return redirect('profile/details');
        }
        $data['staffid'] = $staffid;
        if (!(DB::table('tblper')->where('ID', '=', $staffid)->first())) {
            return redirect('profile/details');
        } else {
            Session::put('staffid', $staffid); //set session
            $getStaff = DB::table('tblper')->where('ID', '=', $staffid)->first();
            if ((DB::table('tblchildren_particulars')->where('staffid', '=', $staffid)->first())) {
                $data['details']            = "";
                $data['childrenList']        = DB::table('tblchildren_particulars')->where('staffid', '=', $staffid)->get();
                $data['getStaff']             = $getStaff;
                return view('hr.ParticularsOfChildren.create', $data);
            } else {
                $data['details']            = "";
                $data['childrenList']        = "";
                $data['getStaff']             = $getStaff;
                return view('hr.ParticularsOfChildren.create', $data);
            }
        }
    } //->


    public function view($id = Null)
    { //->
        $staffid = Session::get('staffid');
        $getStaff = DB::table('tblper')->where('ID', '=', $staffid)->first();
        if (is_null($id)) {
            return redirect('/children/create');
        } else {

            if ((DB::table('tblchildren_particulars')->where('staffid', '=', $staffid)->first())) {
                $data['details']            = DB::table('tblchildren_particulars')->where('id', $id)->first();
                $data['childrenList']        = DB::table('tblchildren_particulars')->where('staffid', $staffid)->get();
                $data['getStaff']             = $getStaff;
                $data['staffid'] = $staffid;
                return view('hr.ParticularsOfChildren.create', $data);
            } else {
                $data['details']            = "";
                $data['childrenList']        = "";
                $data['getStaff']             = $getStaff;
                $data['staffid'] = $staffid;
                return view('hr.ParticularsOfChildren.create', $data);
            }
        }
    } //->


    public function deleteOLD($id = Null)
    {
        $staffid = Session::get('staffid');
        if (is_null($staffid)) {
            return redirect('/children/create/' . $staffid);
        }
        //delete
        DB::table('tblchildren_particulars')->where('id', '=', $id)->where('staffid', '=', $staffid)->delete();
        $this->addLog('children particulars deleted with Staff ID: ' . $staffid);
        return redirect('/children/create/' . $staffid)->with('msg', 'children particulars record deleted successfully');
    }

    public function delete($id)
    {
        $staffid = Session::get('staffid');

        if (is_null($staffid)) {
            return redirect('profile/details');
        }

        // Perform delete
        DB::table('tblchildren_particulars')
            ->where('id', $id)
            ->where('staffid', $staffid)
            ->delete();

        $this->addLog('Children particulars deleted for Staff ID: ' . $staffid);

        return redirect('/children/create/' . $staffid)
            ->with('msg', 'Children particulars record deleted successfully');
    }



    public function store(Request $request)
    {
        $staffid = Session::get('staffid');
        if (is_null($staffid)) {
            return redirect('profile/details');
        }
        $this->validate(
            $request,
            [
                'fullName'                        => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
                'gender'                        => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
                'dateOfBirth'                      => 'required|date',
                // 'checkedChildrenParticulars'     => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
            ]
        );
        $fullName                     = trim($request['fullName']);
        $gender                     = trim($request['gender']);
        $dateOfBirth                      = trim($request['dateOfBirth']);
        // $checkedChildrenParticulars = trim($request['checkedChildrenParticulars']);
        $id                             = trim($request['id']);
        $date                           = date("Y-m-d");

        //Update if hidden Name/id NOT empty
        if ($id <> "") {
            DB::table('tblchildren_particulars')->where('id', $id)->where('staffid', $staffid)->update(array(
                'fullname'                         => $fullName,
                'gender'                         => $gender,
                'dateofbirth'                   => $dateOfBirth,
                // 'checked_children_particulars'  => $checkedChildrenParticulars,
                'updated_at'                    => $date,
            ));
            $this->addLog('Children particular Record updated for Staff ID: ' . $staffid . ' Division: ' . $this->division);
            $message = 'Children particular Record updated successfully';
        } else {
            //insert if hidden Name/id is empty (but directly updating record)
            DB::table('tblchildren_particulars')->insert(array(
                'staffid'                        => $staffid,
                'fullname'                         => $fullName,
                'gender'                         => $gender,
                'dateofbirth'                   => $dateOfBirth,
                // 'checked_children_particulars'  => $checkedChildrenParticulars,
                'created_at'                    => $date,
                'updated_at'                    => $date
            ));
            $this->addLog('Children particular Record created successfully Staff: ' . $staffid . 'Division: ' . $this->division);
            $message = 'Children particular Record created successfully';
        }
        //
        return redirect('/children/create/' . $staffid)->with('msg', $message);
    }

    //Children Report
    public function report($staffid = null)
    {
        if (is_null($staffid)) {
            return redirect('profile/details');
        }
        if (!(DB::table('tblper')->where('ID', '=', $staffid)->first())) {
            return redirect('profile/details');
        } else {
            $data['staffFullDetailsChildren'] = DB::table('tblchildren_particulars')
                ->where('staffid', '=', $staffid)
                ->orderBy('id', 'Desc')
                ->get();
            $data['staffFullDetails'] = DB::table('tblper')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->where('tblper.ID', '=', $staffid)
                ->first();
        }
        return view('Report.ChildrenReport', $data);
    }
}
