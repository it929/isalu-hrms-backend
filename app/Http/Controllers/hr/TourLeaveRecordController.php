<?php

namespace App\Http\Controllers\hr;

use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;
use DateTime;

class TourLeaveRecordController extends ParentController
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


    public function index($staffid = Null, $tourLeaveID = Null)
    {
        if (is_null($staffid)) {
            return view('main.userArea');
        }
        $data['staffid'] = $staffid;
        $data['names'] = DB::table('tblper')->where('ID', '=', $staffid)->first();
        if (!(DB::table('tourleave_record')->where('staffid', '=', $staffid)->first())) {
            //set session
            Session::put('staffid', $staffid);
            $data['tour']    = '';
            $data['tourleaveList']      = '';
            return view('hr.tourLeaveRecord.update', $data);
        } else {
            //set session
            Session::put('staffid', $staffid);

            if (is_null($tourLeaveID)) {
                $data['tour']  = "";
            } else {
                //check if tour and leave id parameters exist in DB
                if (!(DB::table('tourleave_record')->where('tourLeaveID', '=', $tourLeaveID)->first())) {
                    return view('hr.main.userArea');
                }
                $data['tour'] = DB::table('tourleave_record')->where('staffid', '=', $staffid)->where('tourLeaveID', '=', $tourLeaveID)->first();
            }
            $data['staffid'] = $staffid;
            $data['tourleaveList'] = DB::table('tourleave_record')->where('staffid', '=', $staffid)->get();
            //
            return view('hr.tourLeaveRecord.update', $data);
        }
    }

    public function view($tourid = Null)
    {
        //
        $staffid = Session::get('staffid');
        $data['staffid'] = Session::get('staffid');
        if (is_null($tourid)) {
            return redirect('/update/tour-leave-record/' . $staffid);
        }
        if (!(DB::table('previous_servicedetails')->where('staffid', '=', $staffid)->where('tourLeaveID', $tourid)->first())) {
            return redirect('/update/detail-service/' . $fileNo);
        } //
        $data['names'] = DB::table('tblper')->where('ID', '=', $staffid)->first();
        if (!(DB::table('tourleave_record')->where('dosid', '=', $dosid)->first())) {
            $data['tourList']    = "";
            $data['tour']  =  "";
            return view('hr.DetailsOfService.update', $data);
        } else {
            $data['tour']  = DB::table('tourleave_record')->where('staffid', '=', $staffid)->where('tourLeaveID', $tourid)->first();
            $data['tourList']    = DB::table('tourleave_record')->where('staffid', '=', $staffid)->get();
            //
            return view('hr.tourLeaveRecord.update', $data);
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
                'tourstartdate'     => 'required|date',
                'tourgazette'       => 'regex:/^[A-Za-z0-9\-\s]+$/',
                'tourlength'        => 'required|regex:/^[A-Za-z0-9\-\s]+$/',
                'leaveduedate'      => 'date',
                'leavedepartdate'   => 'date',
                'leavegezettenum'   => 'regex:/^[A-Za-z0-9\-\s]+$/',
                'leavereturndate'   => 'required|date',
                'dateextgranted'    => 'required|date',
                'salaryrule'        => 'regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
                'fromuk'            => 'regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
                'touk'              => 'regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
                'residentmonths'    => 'numeric',
                'residentdays'      => 'numeric',
                'dateresume'        => 'required|date',
            ]
        );
        $tourstartdate             = date('Y-m-d', strtotime(trim($request['tourstartdate'])));
        $tourgazette               = trim($request['tourgazette']);
        $tourlength                = trim($request['tourlength']);
        $leaveduedate              = date('Y-m-d', strtotime(trim($request['leaveduedate'])));
        $leavedepartdate           = date('Y-m-d', strtotime(trim($request['leavedepartdate'])));
        $leavegezettenum           = trim($request['leavegezettenum']);
        $leavereturndate           = date('Y-m-d', strtotime(trim($request['leavereturndate'])));
        $dateextgranted            = date('Y-m-d', strtotime(trim($request['dateextgranted'])));
        $salaryrule                = trim($request['salaryrule']);
        $fromuk                    = trim($request['fromuk']);
        $touk                      = trim($request['touk']);
        $residentmonths            = trim($request['residentmonths']);
        $residentdays              = trim($request['residentdays']);
        $dateresume                = date('Y-m-d', strtotime(trim($request['dateresume'])));

        //calculate number of months for leave
        $startleave1    = new DateTime($leaveduedate);
        $startleave2    = new DateTime($leavereturndate);
        $interval       = $startleave2->diff($startleave1);
        $leavemonths    = (($interval->format('%y') * 12) + $interval->format('%m'));

        //calculate number of days for Leave
        $datediff       = strtotime($leavereturndate) - strtotime($leaveduedate);
        $leavedays      = floor($datediff / (60 * 60 * 24));
        $date           = date("Y-m-d");
        $tourLeaveID    = trim($request['tourLeaveID']);
        $hiddenName     = trim($request['hiddenName']);

        if ($hiddenName <> "") {
            DB::table('tourleave_record')->where('tourLeaveID', '=', $tourLeaveID)->where('staffid', '=', $userID)->update(array(
                'staffid'                    => $userID,
                'dateTourStarted'           => $tourstartdate,
                'tourGezetteNumber'         => $tourgazette,
                'lengthOfTour'              => $tourlength,
                'leaveDueDate'              => $leaveduedate,
                'leaveDepartDate'           => $leavedepartdate,
                'leaveGezetteNumber'        => $leavegezettenum,
                'leaveReturnDate'           => $leavereturndate,
                'dateExtensionGranted'      => $dateextgranted,
                'salaryRuleForExt'          => $salaryrule,
                'toUK'                      => $touk,
                'fromUK'                    => $fromuk,
                'residentMonths'            => $residentmonths,
                'residentDays'              => $residentdays,
                'leaveMonths'               => $leavemonths,
                'leaveDays'                 => $leavedays,
                'dateResumedDuty'           => $dateresume,
                'updated_at'                => $date,
            ));
            $this->addLog('Tour and Leave Record was updated with ID: ' . $tourLeaveID . ' and Staff ID: ' . $userID);
        } else {
            //insert if hidden Name is empty
            DB::table('tourleave_record')->insert(array(
                'staffid'                    => $userID,
                'dateTourStarted'           => $tourstartdate,
                'tourGezetteNumber'         => $tourgazette,
                'lengthOfTour'              => $tourlength,
                'leaveDueDate'              => $leaveduedate,
                'leaveDepartDate'           => $leavedepartdate,
                'leaveGezetteNumber'        => $leavegezettenum,
                'leaveReturnDate'           => $leavereturndate,
                'dateExtensionGranted'      => $dateextgranted,
                'salaryRuleForExt'          => $salaryrule,
                'toUK'                      => $touk,
                'fromUK'                    => $fromuk,
                'residentMonths'            => $residentmonths,
                'residentDays'              => $residentdays,
                'leaveMonths'               => $leavemonths,
                'leaveDays'                 => $leavedays,
                'dateResumedDuty'           => $dateresume,
                'updated_at'                => $date,
            ));
            $this->addLog('New Tour and Leave Record was added and division: ' . $this->division);
        }
        $data['tour']                = "";
        $data['tourleaveList']       = DB::table('tourleave_record')->where('staffid', '=', $userID)->get();
        return redirect('/update/tour-leave-record/' . $userID)->with('msg', 'Operation was done successfully.');
    }

    public function destroyOLD($userId, $tourLeaveID)
    {
        $userID = Session::get('userId');
        if (is_null($userID) || is_null($tourLeaveID)) {
            $data['tour']    = DB::table('tourleave_record')->where('userId', '=', $userID)->first();
            $data['tourleaveList']      = DB::table('tourleave_record')->where('userId', '=', $userID)->get();
            return view('hr.tourLeaveRecord.update', $data);
        }
        //delete
        DB::table('tourleave_record')->where('userId', '=', $userID)->where('tourLeaveID', '=', $tourLeaveID)->delete();
        $this->addLog('One Language was deleted and division: ' . $this->division);
        //check if parameters exist in DB
        if (!(DB::table('tourleave_record')->where('userId', '=', $userID)->first())) {
            return view('hr.main.userArea');
        }
        //populate return view('main.userArea');
        $data['tourleaveList']     = DB::table('tourleave_record')->where('userId', '=', $userID)->get();
        $data['tour']     = "";
        return view('hr.tourLeaveRecord.update', $data)->with('msg', 'Operation was done successfully.');
    }


    public function destroy($tourLeaveID = Null)
    {
        $staffid = Session::get('staffid');
        if (is_null($staffid)) {
            return redirect('/update/tour-leave-record/' . $staffid);
        }
        //delete
        DB::table('tbleducations')->where('tourLeaveID', '=', $tourLeaveID)->where('staffid', '=', $staffid)->delete();
        $this->addLog('Tour And Leave Record: ' . $staffid);
        return redirect('/update/tour-leave-record/' . $staffid)->with('msg', 'Operation was done successfully.');
    }


    //Tour and Leave Report
    public function report($fileNo = null)
    {
        if (is_null($fileNo)) {
            return redirect('profile/details');
        }
        if (!(DB::table('tblper')->where('fileNo', '=', $fileNo)->first())) {
            return redirect('profile/details');
        } else {
            $data['staffFullDetailsTerminationService'] = DB::table('tourleave_record')
                ->where('fileNo', '=', $fileNo)
                ->orderBy('tourLeaveID', 'Desc')
                ->get();
            $data['staffFullDetails'] = DB::table('tblper')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->where('tblper.fileNo', '=', $fileNo)
                ->first();
        }
        return view('Report.TourLeaveReport', $data);
    }
}
