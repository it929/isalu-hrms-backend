<?php

namespace App\Http\Controllers\hr;

use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;

class RecordOfServiceController extends ParentController
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

    public function index($staffid = Null, $recID = Null)
    {
        if (is_null($staffid)) {
            return view('main.userArea');
        }
        $data['staffid'] = $staffid;
        $data['names'] = DB::table('tblper')->where('ID', '=', $staffid)->first();
        if (!(DB::table('recordof_service')->where('staffid', '=', $staffid)->first())) {
            //set session
            Session::put('staffid', $staffid);
            $data['recordservice']    = '';
            $data['recordList']      = '';
            return view('hr.RecordOfService.update', $data);
        } else {
            //set session
            Session::put('staffid', $staffid);

            if (is_null($recID)) {
                $data['recordservice']  = "";
            } else {
                //check if dosid parameters exist in DB
                if (!(DB::table('recordof_service')->where('recID', '=', $recID)->first())) {
                    return view('hr.main.userArea');
                }
                $data['recordservice'] = DB::table('recordof_service')->where('staffid', '=', $staffid)->where('recID', '=', $recID)->first();
            }
            $data['recordList']       = DB::table('recordof_service')->where('staffid', '=', $staffid)->get();
            //

            return view('hr.RecordOfService.update', $data);
        }
    }


    public function update(Request $request)
    {
        $userID = Session::get('staffid');
        if (is_null($userID)) {
            return view('main.userArea');
        }
        $this->validate(
            $request,
            [
                'entrydate'          => 'required|date',
                'detail'             => 'required|string',
                'signature'          => 'regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
            ]
        );
        $entrydate              = date('Y-m-d', strtotime(trim($request['entrydate'])));
        $detail                 = trim($request['detail']);
        $signature              = trim($request['signature']);
        $recid                  = trim($request['recid']);
        $hiddenName             = trim($request['hiddenName']);
        $date                   = date("Y-m-d");

        if ($hiddenName <> "") {
            DB::table('recordof_service')->where('recID', '=', $recid)->where('staffid', '=', $userID)->update(array(
                'staffid'        => $userID,
                'entryDate'     => $entrydate,
                'detail'        => $detail,
                'signature'     => $signature,
                'updated_at'    => $date
            ));
            $this->addLog('Record Of Service was updated with ID: ' . $recid . ' and Staff ID: ' . $userID);
        } else {
            //insert if hidden Name is empty
            DB::table('recordof_service')->insert(array(
                'staffid'         => $userID,
                'entryDate'      => $entrydate,
                'detail'         => $detail,
                'signature'      => $signature,
                'updated_at'     => $date
            ));
            $this->addLog('New Next of Kin was added and staff ID: ' . $userID);
        }
        $data['recordservice']   = "";
        $data['recordList']      = DB::table('recordof_service')->where('staffid', '=', $userID)->get();
        return redirect('/update/recordofservice/' . $userID)->with('msg', 'Operation was done successfully.');
    }
    public function destroyOLD($fileNo, $dosid)
    {
        $userID = Session::get('fileNo');
        if (is_null($userID) || is_null($dosid)) {
            $data['doservice']    = DB::table('recordof_service')->where('fileNo', '=', $userID)->first();
            $data['dosList']      = DB::table('recordof_service')->where('fileNo', '=', $userID)->get();
            return view('detailofservice.update', $data);
        }
        //delete
        DB::table('recordof_service')->where('fileNo', '=', $userID)->where('dosid', '=', $dosid)->delete();
        $this->addLog('detail of Service deleted and division: ' . $this->division);
        //check if parameters exist in DB
        if (!(DB::table('recordof_service')->where('fileNo', '=', $userID)->first())) {
            return view('main.userArea');
        }
        $data['recordList']     = DB::table('recordof_service')->where('userID', '=', $userID)->get();
        $data['recordservice']     = "";
        return view('detailsofservice.update', $data)->with('msg', 'Operation was done successfully.');
    }


    public function destroy($recID = Null)
    {
        $staffid = Session::get('staffid');
        if (is_null($staffid)) {
            return redirect('/update/recordofservice/' . $staffid);
        }
        //delete
        DB::table('recordof_service')->where('recID', '=', $recID)->where('staffid', '=', $staffid)->delete();
        $this->addLog('Record of service details deleted: ' . $staffid);
        return redirect('/update/recordofservice/' . $staffid)->with('msg', 'Operation was done successfully.');
    }


    //Record of service Report
    public function report($fileNo = null)
    {
        if (is_null($fileNo)) {
            return redirect('profile/details');
        }
        if (!(DB::table('tblper')->where('fileNo', '=', $fileNo)->first())) {
            return redirect('profile/details');
        } else {
            $data['staffFullDetailsRecordService'] = DB::table('recordof_service')
                ->where('fileNo', '=', $fileNo)
                ->orderBy('recID', 'Desc')
                ->get();
            $data['staffFullDetails'] = DB::table('tblper')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->where('tblper.fileNo', '=', $fileNo)
                ->first();
        }
        return view('Report.RecordServiceReport', $data);
    }
}
