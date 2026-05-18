<?php

namespace App\Http\Controllers\hr;

use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;

class DetailsOfPreviousServiceController extends ParentController
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($staffid = Null, $doppsid = Null)
    {
        //dd($staffid);
        if (is_null($staffid)) {
            return back();
        }
        $data['staffid'] = $staffid;

        $data['names'] = DB::table('tblper')->where('ID', '=', $staffid)->first();
        if (!(DB::table('previous_servicedetails')->where('staffid', '=', $staffid)->first())) {
            //set session
            Session::put('staffid', $staffid);
            $data['doppservice']    = '';
            $data['doppList']      = '';
            return view('hr.DetailsOfPreviousService.update', $data);
        } else {
            //set session
            Session::put('staffid', $staffid);

            if (is_null($doppsid)) {
                $data['doppservice']  = "";
            } else {

                if (!(DB::table('previous_servicedetails')->where('doppsid', '=', $doppsid)->first())) {
                    return redirect('/profile/details');
                }
                $data['doppservice'] = DB::table('previous_servicedetails')->where('staffid', '=', $staffid)->where('doppsid', '=', $doppsid)->first();
            }
            $data['doppList']       = DB::table('previous_servicedetails')->where('staffid', '=', $staffid)->get();
            //

            return view('hr.DetailsOfPreviousService.update', $data);
        }
    }

    public function view($dopsid = Null)
    {

        //
        $staffid = Session::get('staffid');
        $data['staffid'] = Session::get('staffid');
        if (is_null($dosid)) {
            return redirect('/update/detailofservice/' . $staffid);
        }
        if (!(DB::table('previous_servicedetails')->where('staffid', '=', $staffid)->where('dosid', $dosid)->first())) {
            return redirect('/update/detail-service/' . $fileNo);
        } //
        $data['names'] = DB::table('tblper')->where('ID', '=', $staffid)->first();
        if (!(DB::table('previous_servicedetails')->where('dosid', '=', $dosid)->first())) {
            $data['dosList']    = "";
            $data['doservice']  =  "";
            return view('DetailsOfService.update', $data);
        } else {
            $data['doservice']  = DB::table('detailsofservice')->where('staffid', '=', $staffid)->where('dosid', '=', $dosid)->first();
            $data['dosList']    = DB::table('detailsofservice')->where('staffid', '=', $staffid)->get();
            //
            return view('DetailsOfService.update', $data);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }


    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $userID = Session::get('staffid');
        if (is_null($userID)) {
            return redirect('/profile/details');
        }
        $this->validate(
            $request,
            [
                'previousemployers'          => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
                'fromdate'                   => 'required|date',
                'todate'                     => 'required|date',
                'years'                      => 'numeric',
                'months'                     => 'numeric',
                'days'                       => 'numeric',
                'prevpay'                    => 'numeric',
                'filepage'                   => 'regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
                'checkedby'                  => 'regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',


            ]
        );
        $prevemp              = trim($request['previousemployers']);
        $fromdate             = date('Y-m-d', strtotime(trim($request['fromdate'])));
        $todate               = date('Y-m-d', strtotime(trim($request['todate'])));
        $years                = trim($request['years']);
        $days                 = trim($request['days']);
        $months               = trim($request['months']);
        $prevpay              = trim($request['prevpay']);
        $filepage             = trim($request['filepage']);
        $checkedby            = trim($request['checkedby']);

        $date                 = date("Y-m-d");
        $doppsid              = trim($request['doppsid']);
        $hiddenName           = trim($request['hiddenName']);

        if ($hiddenName <> "") {

            DB::table('previous_servicedetails')->where('doppsid', '=', $doppsid)->where('staffid', '=', $userID)->update(array(
                //'userID'           => $userID,
                'staffid'            => $userID,
                'previousSchudule'   => $prevemp,
                'fromDate'           => $fromdate,
                'toDate'             => $todate,
                'years'              => $years,
                'months'             => $months,
                'days'               => $days,
                'totalPreviousPay'   => $prevpay,
                'filePageRef'        => $filepage,
                'checkedby'          => $checkedby,
                'updated_at'         => $date,

            ));
            $this->addLog('Detail of Previous Public Service was updated with ID: ' . $doppsid . ' and staff ID: ' . $userID);
        } else {

            //insert if hidden Name is empty
            DB::table('previous_servicedetails')->insert(array(
                'staffid'           => $userID,
                'previousSchudule' => $prevemp,
                'fromDate'    => $fromdate,
                'toDate'     => $todate,
                'years'     => $years,
                'months'  => $months,
                'days'     => $days,
                'totalPreviousPay'     => $prevpay,
                'filePageRef'     => $filepage,
                'checkedby'     => $checkedby,
                'updated_at' => $date,


            ));
            $this->addLog('New Details Of Previous Public Service was added and Staff ID: ' . $userID);
        }
        //$data['nextOfKin']     = DB::table('tblnextofkin')->where('userID', '=', $userID)->first();
        $data['doppservice']     = "";
        $data['doppList']       = DB::table('previous_servicedetails')->where('staffid', '=', $userID)->get();
        return redirect('/update/detailofprevservice/' . $userID)->with('msg', 'Operation was done successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyOLD($fileNo, $doppsid)
    {
        $userID = Session::get('fileNo');
        if (is_null($userID) || is_null($doppsid)) {
            $data['doppservice']    = DB::table('previous_servicedetails')->where('fileNo', '=', $userID)->first();
            $data['doppList']      = DB::table('previous_servicedetails')->where('fileNo', '=', $userID)->get();
            return view('DetailsOfPreviousService.update', $data);
        }
        //delete
        DB::table('previous_servicedetails')->where('fileNo', '=', $userID)->where('doppsid', '=', $doppsid)->delete();
        $this->addLog('detail of Service deleted and division: ' . $this->division);
        //check if parameters exist in DB
        if (!(DB::table('previous_servicedetails')->where('fileNo', '=', $userID)->first())) {
            return view('main.userArea');
        }
        //populate return view('main.userArea');
        //$data['nextOfKin']   = DB::table('tblnextofkin')->where('userID', '=', $userID)->first();
        $data['doppList']     = DB::table('previous_servicedetails')->where('fileNo', '=', $userID)->get();
        $data['doppservice']     = "";
        return view('DetailsOfPreviousService.update', $data)->with('msg', 'Operation was done successfully.');
    }

    public function destroy($doppsid = Null)
    {
        $staffid = Session::get('staffid');
        if (is_null($staffid)) {
            return redirect('/update/detailofprevservice/' . $staffid);
        }
        //delete
        DB::table('previous_servicedetails')->where('doppsid', '=', $doppsid)->where('staffid', '=', $staffid)->delete();
        $this->addLog('Education details deleted: ' . $staffid);
        return redirect('/update/detailofprevservice/' . $staffid)->with('msg', 'Details was deleted successfully');
    }


    //previouse service Report
    public function report($staffid = null)
    {
        if (is_null($staffid)) {
            return redirect('profile/details');
        }
        if (!(DB::table('tblper')->where('ID', '=', $staffid)->first())) {
            return redirect('profile/details');
        } else {
            $data['staffFullDetailsPreviousService'] = DB::table('previous_servicedetails')
                ->where('staffid', '=', $staffid)
                ->orderBy('doppsid', 'Desc')
                ->get();
            $data['staffFullDetails'] = DB::table('tblper')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->join('tbldesignation', 'tbldesignation.id', '=', 'tblper.Designation')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->where('tblper.ID', '=', $staffid)
                ->first();
        }
        return view('Report.PreviousServiceReport', $data);
    }
}
