<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Http\Requests;
use DB;
use Session;
use Auth;

class StaffAccountDetailsController extends Controller
{
    public function index()
    {
        //refactored by pitoff
        //check if logged user is global
        //if is global enable select drop down
        //else append the current division of logged user

        $data['division'] = '';
        $data['loggedDivision'] = '';
        $List = DB::table("tblsole_court")->where('courtid', '=', 9)->first();
        if ($List->divisionstatus == 1 && Auth::user()->is_global == 1) {
            // dd("yes global user");
            if (session('selected_court') != '') {
                $data['division'] = DB::table('tbldivision')->where('courtID', '=', session('selected_court'))->get();
                // dd($data['division']);
            }
            else {
                // dd("not global user");
                $data['loggedDivision'] = $this->curDivision(Auth::user()->id);
            }
        } else {
            // dd("not global user");
            $data['loggedDivision'] = $this->curDivision(Auth::user()->id);
        }
        //end refactor

        // $data['courtInfo'] = DB::table("tblsole_court")->where('courtid', '=', 9)->first();
        // if(session('selected_court') != '')
        // {
        //     $data['division'] = DB::table('tbldivision')->where('courtID', '=', session('selected_court'))->get();
        // }
        // else
        // {
        //     $data['division'] = '';
        // }

        $data['court'] = DB::table('tbl_court')->get();
        $data['bankList'] = DB::table('tblbanklist')->get();
        // dd($data);
        return view('accountDetails/addAccount', $data);
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

    public function courtSession(Request $request)
    {
        $court = $request['courtID'];
        $division = $request['divisionID'];
        $check = $request['check'];
        if ($check == 'court') {
            $ses    = Session::put('selected_court', $court);
            return response()->json("Successfully Set");
        } elseif ($check == 'division') {
            $ses    = Session::put('selected_division', $division);
            return response()->json("Successfully Set");
        } else {
            return response()->json("Not Set");
        }
    }


    public function store(Request $request)
    {
        $fileNo         = $request['fileNo'];
        $courtID        = $request['court'];
        $division       = $request['division'];
        $bank           = $request['bank'];
        $bankGroup      = $request['bankGroup'];
        $branch         = $request['branch'];
        $accountNo      = $request['accountNo'];

        $staff = DB::table('tblper')->where('fileNo', '=', $fileNo)->first();
        if ($staff != null) {
            DB::table('tblper')->where('fileNo', '=', $fileNo)->update(array(
                'bankID'                   => $bank,
                'bankGroup'                => $bankGroup,
                'bank_branch'              => $branch,
                'AccNo'                    => $accountNo,
                'courtID'                  => $courtID,
                'divisionID'               => $division,
                'staff_status'             => 1,
                'status_value'             => 'active service',
            ));

            DB::table('tblstaff_for_arrears')->insert(array(
                'fileNo'                   => $fileNo,
                'courtID'                  => $courtID,
                'divisionID'               => $division,
                'OldEmploymentType'        => $staff->employee_type,
                'NewEmployMentType'        => $staff->employee_type,
                'arrears_type'             => 'newAppointment',
                'old_grade'                => 0,
                'old_step'                 => 0,
                'new_grade'        => $staff->grade,
                'new_step'        => $staff->step,
                'due_date'        => $staff->appointment_date,
                'approvedBy'        => Auth::user()->username,
                'approvedDate'        => date('Y-m-d H:i:s'),
            ));
        }
        return redirect('/account-info/add')->with('msg', 'Successfully Updated');
    }


    public function getStaff(Request $request)
    {
        $division = session('selected_division');
        $court = session('selected_court');
        $query = $request->input('query');
        //$search = DB::table('tblper')
        //->where('courtID', '=', session('selected_court'))
        //->where('divisionID', '=', session('selected_division'))


        /*->orWhere(function ($s) {
                  $s->where('surname', 'LIKE', '%'.$query.'%')
                      ->where('first_name', 'LIKE', '%'.$query.'%')
                      ->where('fileNo', 'LIKE','%'.$query.'%');
            })
           ->take(15)->get();*/

        //->orWhere('surname', 'LIKE', '%'.$query.'%')
        //->orWhere('first_name', 'LIKE', '%'.$query.'%')->orWhere('fileNo', 'LIKE','%'.$query.'%')->take(15)->get();
        //$return_array = null;

        $search = DB::select("SELECT `surname`,`first_name`,`fileNo`,`othernames` FROM `tblper` WHERE (`first_name` like '%$query%' or `surname` like '%$query%' or `fileNo` like '%$query%') and `courtID`='$court' and `divisionID`='$division'");
        foreach ($search as $s) {
            $return_array[]  =  ["value" => $s->surname . ' ' . $s->first_name . ' - ' . $s->fileNo, "data" => $s->fileNo];
        }
        return response()->json(array("suggestions" => $return_array));
    }


    public function edit($id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        //
    }


    public function destroy($id)
    {
        //
    }
}
