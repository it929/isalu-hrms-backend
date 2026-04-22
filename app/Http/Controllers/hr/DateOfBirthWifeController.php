<?php
//
namespace App\Http\Controllers\hr;

use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Carbon\Carbon;
use Auth;
use DB;

class DateOfBirthWifeController extends ParentController
{




    public function createOLD($staffid = Null)
    {
        //check if parameters are Null
        if (is_null($staffid)) {
            return redirect('/profile/details');
        }
        $data['staffid'] = $staffid;
        //set session
        Session::put('staffid', $staffid);
        $getStaff = DB::table('tblper')->where('ID', '=', $staffid)->first();
        if ((DB::table('tbldateofbirth_wife')
            ->where('wifename', '<>', "")
            ->where('wifedateofbirth', '<>', "")
            ->where('wifedateofbirth', '<>', "0000-00-00")
            ->where('checkedby1', '<>', "")
            ->first())) {
            $data['details']     = "";
            $data['KinList']     = DB::table('tbldateofbirth_wife')->where('staffid', '=', $staffid)->get();
            $data['getStaff']             = $getStaff;



            return view('hr.dateOfBirthWife.create', $data);
        } else {
            $data['details']     = "";
            $data['KinList']     = "";
            $data['getStaff']             = $getStaff;
            return view('hr.dateOfBirthWife.create', $data);
        }
    }

    public function create($staffid = null)
    {
        if (is_null($staffid)) {
            return redirect('/profile/details');
        }

        Session::put('staffid', $staffid);

        $getStaff = DB::table('tblper')->where('ID', $staffid)->first();

        $KinList = DB::table('tbldateofbirth_wife')
            ->where('staffid', $staffid)
            ->get();

        $data = [
            'staffid' => $staffid,
            'details' => "",
            'KinList' => $KinList,
            'getStaff' => $getStaff,
        ];

        return view('hr.dateOfBirthWife.create', $data);
    }


    public function deleteOLD($fileNo = Null)
    {
        $staffid = Session::get('staffid');

        $getStaff = DB::table('tblper')->where('ID', '=', $staffid)->first();
        if (is_null($staffid)) {
            $data['details']    = DB::table('tbldateofbirth_wife')->where('staffid', '=', $staffid)->first();
            $data['KinList']    = DB::table('tbldateofbirth_wife')->where('staffid', '=', $staffid)->get();
            $data['getStaff']             = $getStaff;
            return view('dateOfBirthWife.create', $data);
        }
        //delete
        DB::table('tbldateofbirth_wife')->where('staffid', '=', $staffid)->update(array(
            'maritalstatus'         => "",
            'homeplace'             => "",
            'dateofbirth'           => "",
            'dateofmarriage'        => "",
            'wifename'              => "",
            'wifedateofbirth'        => "",
            'checkedby1'            => "",
            'checkedby2'            => "",
            'updated_at'            => date("Y-m-d")
        ));
        $this->addLog('Date of birth deleted and Staff ID: ' . $staffid);
        //
        return redirect('/particular/wife/create/' . $staffid)->with('msg', 'You successfully deleted a record.');
    }

    public function delete($fileNo = Null)
    {
        $staffid = Session::get('staffid');
        if (is_null($staffid)) {
            return redirect('/particular/wife/create/' . $staffid);
        }
        //delete
        DB::table('tbldateofbirth_wife')->where('particularID', '=', $fileNo)->where('staffid', '=', $staffid)->delete();
        $this->addLog('Date of birth deleted and Staff ID: ' . $staffid);
        return redirect('/particular/wife/create/' . $staffid)->with('msg', 'You successfully deleted a record.');
    }


    public function view($ID = Null)
    { //->
        $staffid = Session::get('staffid');
        $getStaff = DB::table('tblper')->where('ID', '=', $staffid)->first();
        if (is_null($ID)) {
            return redirect('/particular/details/' . $staffid);
        } else {
            $data['staffid'] = Session::get('staffid');
            if ((DB::table('tbldateofbirth_wife')->where('staffid', '=', $staffid)->first())) {
                $data['details']        = DB::table('tbldateofbirth_wife')->where('particularID', '=', $ID)->where('staffid', $staffid)->first();
                $data['KinList']        = DB::table('tbldateofbirth_wife')->where('staffid', $staffid)->get();
                $data['getStaff']         = $getStaff;
                return view('hr.dateOfBirthWife.create', $data);
            } else {
                $data['details']        = "";
                $data['KinList']        = "";
                $data['getStaff']         = $getStaff;
                return view('hr.dateOfBirthWife.create', $data);
            }
        }
    } //->


    public function store(Request $request)
    {
        $staffid = Session::get('staffid');
        if (is_null($staffid)) {
            return redirect('/profile/details/' . $fileNo);
        }
        $this->validate(
            $request,
            [
                'homePlace'        => 'string',
                'dateOfMarriage'   => 'required|date',
                'wifeName'         => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
                'wifeDateOfBirth'  => 'required|date',
                // 'checkedBy'        => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
                // 'checkedBy2'       => 'regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
            ]
        );
        $homePlace             = trim($request['homePlace']);
        $dateOfMarriage        = trim($request['dateOfMarriage']);
        $wifeName                 = trim($request['wifeName']);
        $wifeDateOfBirth       = trim($request['wifeDateOfBirth']);
        // $checkedBy             = trim($request['checkedBy']);
        // $checkedBy2               = trim($request['checkedBy2']);
        $hiddenName               = trim($request['hiddenName']);
        $particularID          = trim($request['particularID']);
        $date                   = date("Y-m-d");

        //Update if hidden Name not empty
        if ($hiddenName <> "") {
            DB::table('tbldateofbirth_wife')->where('staffid', '=', $staffid)->where('particularID', '=', $hiddenName)->update(array(
                'homeplace'             => $homePlace,
                'dateofmarriage'        => $dateOfMarriage,
                'wifename'              => $wifeName,
                'wifedateofbirth'        => $wifeDateOfBirth,
                // 'checkedby1'            => $checkedBy,
                // 'checkedby2'            => $checkedBy2,
                'updated_at'            => $date
            ));
            $this->addLog('Record updated for Staff ID: ' . $staffid . 'on  wife Date Of Birth and division: ' . $staffid);
        } else {
            //insert if hidden Name is empty (but directly updating record)
            DB::table('tbldateofbirth_wife')->insert(array(
                'staffid'                => $staffid,
                'homeplace'             => $homePlace,
                'dateofmarriage'        => $dateOfMarriage,
                'wifename'              => $wifeName,
                'wifedateofbirth'        => $wifeDateOfBirth,
                // 'checkedby1'            => $checkedBy,
                // 'checkedby2'            => $checkedBy2,
                'updated_at'            => $date
            ));
            $this->addLog('wife date of birth record added for Staff ID: ' . $staffid . ', division: ' . $staffid);
        }
        //
        return redirect('/particular/wife/create/' . $staffid)->with('msg', 'You have successfully updated your record.');
    }


    //Details of service in force Report
    public function report($staffid = null)
    {
        if (is_null($staffid)) {
            return redirect('profile/details');
        }
        if (!(DB::table('tblper')->where('ID', '=', $staffid)->first())) {
            return redirect('profile/details');
        } else {
            $data['staffFullDetailsParticularWife'] = DB::table('tbldateofbirth_wife')
                ->where('staffid', '=', $staffid)
                ->orderBy('particularID', 'Desc')
                ->get();
            $data['staffFullDetails'] = DB::table('tblper')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->where('tblper.ID', '=', $staffid)
                ->first();
        }
        return view('Report.ParticularWifeReport', $data);
    }
}
