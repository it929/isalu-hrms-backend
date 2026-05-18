<?php
//
namespace App\Http\Controllers\hr;

use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;

class NextOfKinController extends ParentController
{
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->middleware(function ($request, $next) {
            // $this->division    = $request->session()->get('division');
        // $this->divisionID  = $request->session()->get('divisionID');
        $this->division = Session::get('division');
            return $next($request);
                    return $next($request);
        });
    });
    }



    public function index($staffid = Null, $kinID = Null)
    {
        //check if parameters are Null
        if (is_null($staffid)) {
            return back();
        }
        $data['staffid'] = $staffid;
        //not exist, Add new
        if (!(DB::table('tblnextofkin')->where('staffid', '=', $staffid)->first())) {
            //set session
            Session::put('staffid', $staffid); //put
            //$staffid = Session::get('staffid');  //get
            $getStaff = DB::table('tblper')->where('ID', '=', $staffid)->first();

            $data['nextOfKin']    = '';
            $data['KinList']       = '';
            $data['fileNoCallBack'] = Session::get('staffid');
            $data['getStaff']             = $getStaff;


            return view('hr.nextOfKin.update', $data);
        } else {
            //set session
            // Session::put('$staffid', $staffid);
            Session::put('staffid', $staffid);
            $staffid = Session::get('staffid');
            $getStaff = DB::table('tblper')->where('ID', '=', $staffid)->first();

            if (is_null($kinID)) {
                $data['nextOfKin']     = "";
            } else {
                //check if kinID parameters exist in DB
                if (!(DB::table('tblnextofkin')->where('kinID', '=', $kinID)->first())) {
                    return redirect('/profile/details/' . $staffid);
                }
                $data['nextOfKin'] = DB::table('tblnextofkin')->where('staffid', '=', $staffid)->where('kinID', '=', $kinID)->first();
            }
            $data['KinList']        = DB::table('tblnextofkin')->where('staffid', '=', $staffid)->get();
            $data['fileNoCallBack'] = Session::get('staffid');
            $data['getStaff']             = $getStaff;

            //  dd($data['getStaff']);
            return view('hr.nextOfKin.update', $data);
        }
    }


    public function view($kinID = Null)
    {
        $staffid = Session::get('staffid');  //get
        $getStaff = DB::table('tblper')->where('ID', '=', $staffid)->first();
        if (!(DB::table('tblnextofkin')->where('staffid', '=', $staffid)->where('kinID', '=', $kinID)->first())) {
            $data['nextOfKin']    = '';
            $data['KinList']       = '';
            $data['fileNoCallBack'] = Session::get('staffid');
            $data['getStaff']             = $getStaff;
            $data['staffid'] = $staffid;
            return view('hr.nextOfKin.update', $data);
        } else {
            $data['nextOfKin'] = DB::table('tblnextofkin')->where('staffid', $staffid)->where('kinID', $kinID)->first();
            $data['KinList']        = DB::table('tblnextofkin')->where('staffid', '=', $staffid)->get();
            $data['fileNoCallBack'] = Session::get('staffid');
            $data['getStaff']             = $getStaff;
            $data['staffid'] = $staffid;
            return view('hr.nextOfKin.update', $data);
        }
    }



    public function deleteOLD2($fileNo = Null, $kinID = Null)
    {
        $fileNo = Session::get('fileNo');
        $getStaff = DB::table('tblper')->where('fileNo', '=', $fileNo)->first();
        if (is_null($fileNo) || is_null($kinID)) {
            $data['nextOfKin']    = DB::table('tblnextofkin')->where('fileNo', '=', $fileNo)->first();
            $data['KinList']      = DB::table('tblnextofkin')->where('fileNo', '=', $fileNo)->get();
            $data['fileNoCallBack'] = Session::get('fileNo');
            $data['getStaff']             = $getStaff;
            return view('hr.nextOfKin.update', $data);
        }
        //delete
        DB::table('tblnextofkin')->where('fileNo', '=', $fileNo)->where('kinID', '=', $kinID)->delete();
        $this->addLog('Next of Kin deleted and division: ' . $this->division);
        //check if parameters exist in DB
        if (!(DB::table('tblnextofkin')->where('fileNo', '=', $fileNo)->first())) {
            return redirect('/profile/details/' . $fileNo);
        }
        //populate return view('main.userArea');
        //$data['nextOfKin']   = DB::table('tblnextofkin')->where('fileNo', '=', $fileNo)->first();
        $data['KinList']     = DB::table('tblnextofkin')->where('fileNo', '=', $fileNo)->get();
        $data['nextOfKin']     = "";
        $data['fileNoCallBack'] = Session::get('fileNo');
        $data['getStaff']             = $getStaff;
        return view('hr.nextOfKin.update', $data)->with('msg', 'Operation was done successfully.');;
    }

    public function deleteOLD($fileNo, $kinID)
    {
        // dd(345678);
        // \Log::info('FileNo in session: ' . $fileNo);
        // \Log::info('KinID passed: ' . $kinID);
        if (is_null($fileNo) || is_null($kinID)) {
            return redirect()->back()->with('error', 'Invalid request.');
        }

        // Check if record exists
        $exists = DB::table('tblnextofkin')
            ->where('fileNo', $fileNo)
            ->where('kinID', $kinID)
            ->exists();

        if (!$exists) {
            return redirect()->back()->with('error', 'Record not found.');
        }

        // Delete
        DB::table('tblnextofkin')
            ->where('fileNo', $fileNo)
            ->where('kinID', $kinID)
            ->delete();

        $this->addLog('Next of Kin deleted for fileNo: ' . $fileNo);

        return redirect('/update/next-of-kin/' . $fileNo)->with('msg', 'Next of Kin deleted successfully.');
    }


    public function delete($kinID)
    {
        $staffid = Session::get('staffid');

        if (is_null($staffid)) {
            return redirect('profile/details');
        }

        // Perform delete
        DB::table('tblnextofkin')
            ->where('kinID', $kinID)
            ->where('staffid', $staffid)
            ->delete();

        $this->addLog('Next of Kin deleted for Staff ID: ' . $staffid);

        return redirect('/update/next-of-kin/' . $staffid)
            ->with('msg', 'Next of Kin deleted successfully.');
    }




    public function store(Request $request)
    {
        $staffid = Session::get('staffid');
        $this->validate(
            $request,
            [
                'fullName'          => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/|unique:tblnextofkin,fullname',
                'relationship'      => 'required|regex:/^[\pL\s\-]+$/u',
                'address'              => 'required|string',
                'phoneNumber'       => 'numeric',
            ]
        );

        $fullName              = trim($request['fullName']);
        $relationship          = trim($request['relationship']);
        $address                  = trim($request['address']);
        $phoneNumber           = trim($request['phoneNumber']);
        $date                   = date("Y-m-d");
        DB::table('tblnextofkin')->insert(array(
            'staffid'           => $staffid,
            'fullname'         => $fullName,
            'relationship'     => $relationship,
            'address'             => $address,
            'phoneno'          => $phoneNumber,
            'updated_at'       => $date
        ));
        $this->addLog('New Next of Kin was added and division: ' . $this->division);
        $data['nextOfKin']        = DB::table('tblnextofkin')->where('fileNo', '=', $fileNo)->first();
        $data['KinList']       = DB::table('tblnextofkin')->where('fileNo', '=', $fileNo)->get();
        $data['fileNoCallBack'] = Session::get('fileNo');
        $data['getStaff']             = $getStaff;
        return view('hr.nextOfKin.update', $data);
    }


    public function update(Request $request)
    {
        $staffid = Session::get('staffid');
        if (is_null($staffid)) {
            return redirect('/profile/details/' . $fileNo);
        }
        $this->validate(
            $request,
            [
                'fullName'          => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
                'relationship'      => 'required|regex:/^[\pL\s\-]+$/u',
                'address'              => 'required|string',
                'phoneNumber'       => 'numeric',
            ]
        );
        $fullName              = trim($request['fullName']);
        $relationship          = trim($request['relationship']);
        $address                  = trim($request['address']);
        $phoneNumber           = trim($request['phoneNumber']);
        $kinID                 = trim($request['kinID']);
        $hiddenName               = trim($request['hiddenName']);
        $date                   = date("Y-m-d");
        //dd($hiddenName);
        //check if user can still add more next of kin to list and return with list
        $KinList               = DB::table('tblnextofkin')->where('staffid', '=', $staffid)->get();
        if ((count($KinList) >= 2) && ($hiddenName == "")) {
            return redirect('/update/next-of-kin/' . $staffid)->with('err', 'You are allow to add only two (2) next of kin details to your profile.');
        }
        //Update if hidden Name not empty
        if ($hiddenName <> "") {
            DB::table('tblnextofkin')->where('kinID', '=', $kinID)->where('staffid', '=', $staffid)->update(array(
                //'fileNo'           => $fileNo,
                'fullname'         => $fullName,
                'relationship'     => $relationship,
                'address'             => $address,
                'phoneno'          => $phoneNumber,
                'updated_at'       => $date
            ));
            $this->addLog('Next of Kin was updated with ID: ' . $kinID . ' and division: ' . $this->division);
        } else {
            //insert if hidden Name is empty
            DB::table('tblnextofkin')->insert(array(
                'staffid'           => $staffid,
                'fullname'         => $fullName,
                'relationship'     => $relationship,
                'address'             => $address,
                'phoneno'          => $phoneNumber,
                'updated_at'       => $date
            ));
            $this->addLog('New Next of Kin was added and Staff ID: ' . $staffid);
        }
        //$data['nextOfKin']     = DB::table('tblnextofkin')->where('fileNo', '=', $fileNo)->first();
        $data['nextOfKin']     = "";
        $data['KinList']       = DB::table('tblnextofkin')->where('staffid', '=', $staffid)->get();
        $data['fileNoCallBack'] = Session::get('staffid');
        return redirect('/update/next-of-kin/' . $staffid)->with('msg', 'Operation was done successfully.');
    }

    //Next of Kin Report
    public function report($fileNo = null)
    {
        if (is_null($fileNo)) {
            return redirect('profile/details');
        }
        if (!(DB::table('tblper')->where('fileNo', '=', $fileNo)->first())) {
            return redirect('profile/details');
        } else {
            $data['staffFullDetailsNextKin'] = DB::table('tblnextofkin')
                ->where('fileNo', '=', $fileNo)
                ->orderBy('kinID', 'Desc')
                ->get();
            $data['staffFullDetails'] = DB::table('tblper')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->where('tblper.fileNo', '=', $fileNo)
                ->first();
        }
        return view('Report.NextKinReport', $data);
    }
}
