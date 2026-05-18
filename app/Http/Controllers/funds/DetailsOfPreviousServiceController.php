<?php

namespace App\Http\Controllers\funds;
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
        $this->division    = $request->session()->get('division');
        $this->divisionID  = $request->session()->get('divisionID');
                return $next($request);
        });
    }   

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($fileNo = Null, $doppsid = Null)
    {
            if(is_null($fileNo)){
            return redirect('/profile/details');
        }

        $data['names'] = DB::table('tblper')->where('fileNo', '=', $fileNo)->first();
        if(!(DB::table('previous_servicedetails')->where('fileNo', '=', $fileNo)->first())){
            //set session
            Session::put('fileNo', $fileNo);
            $data['doppservice']    = '';
            $data['doppList']      = '';
            return view('DetailsOfPreviousService.update', $data);
        }
        else{
            //set session
            Session::put('fileNo', $fileNo);

            if(is_null($doppsid)){
                $data['doppservice']  = "";
            }
            else{
                //check if kinID parameters exist in DB
               if(!(DB::table('previous_servicedetails')->where('doppsid', '=', $doppsid)->first())){
                    return redirect('/profile/details');
                }
                $data['doppservice'] = DB::table('previous_servicedetails')->where('fileNo','=',$fileNo)->where('doppsid','=',$doppsid)->first();
            }
            $data['doppList']       = DB::table('previous_servicedetails')->where('fileNo', '=', $fileNo)->get();
            //
            
            return view('DetailsOfPreviousService.update', $data);
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
            $userID = Session::get('fileNo');
        if(is_null($userID)){
             return redirect('/profile/details');
        }
         $this->validate($request, 
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
           

        ]);
        $prevemp              = trim($request['previousemployers']);
        $fromdate          = date('Y-m-d', strtotime(trim($request['fromdate'])));
        $todate              = date('Y-m-d', strtotime(trim($request['todate'])));
        $years           = trim($request['years']);
        $days                 = trim($request['days']);
        $months            = trim($request['months']);
        $prevpay            = trim($request['prevpay']);
        $filepage            = trim($request['filepage']);
        $checkedby            = trim($request['checkedby']);
        
        $date                  = date("Y-m-d");
        $doppsid                 = trim($request['doppsid']);
        $hiddenName            = trim($request['hiddenName']);
        
        if($hiddenName <> ""){

            DB::table('previous_servicedetails')->where('doppsid', '=', $doppsid)->where('fileNo', '=', $userID)->update(array( 
                //'userID'           => $userID, 
                'fileNo'           => $userID, 
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
                
                
                //'updated_at'       => $date
            ));
            $this->addLog('Detail of Previous Public Service was updated with ID: '.$doppsid .' and division: ' . $this->division);
        
            
               }
        else{

           //insert if hidden Name is empty
            DB::table('previous_servicedetails')->insert(array( 
                'fileNo'           => $userID, 
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
            $this->addLog('New Details Of Previous Public Service was added and division: ' . $this->division);
     
        

        }
        //$data['nextOfKin']     = DB::table('tblnextofkin')->where('userID', '=', $userID)->first();
        $data['doppservice']     = "";
        $data['doppList']       = DB::table('previous_servicedetails')->where('fileNo', '=', $userID)->get();
        return redirect('/update/detailofprevservice/'.$userID)->with('msg', 'Operation was done successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($fileNo,$doppsid)
    {
        $userID = Session::get('fileNo');
        if(is_null($userID) || is_null($doppsid)){
            $data['doppservice']    = DB::table('previous_servicedetails')->where('fileNo', '=', $userID)->first();
            $data['doppList']      = DB::table('previous_servicedetails')->where('fileNo', '=', $userID)->get();
            return view('DetailsOfPreviousService.update', $data);
        }
        //delete
        DB::table('previous_servicedetails')->where('fileNo', '=', $userID)->where('doppsid', '=', $doppsid)->delete();
        $this->addLog('detail of Service deleted and division: ' . $this->division);
        //check if parameters exist in DB
        if(!(DB::table('previous_servicedetails')->where('fileNo', '=', $userID)->first())){
            return view('main.userArea');
        }
        //populate return view('main.userArea');
        //$data['nextOfKin']   = DB::table('tblnextofkin')->where('userID', '=', $userID)->first();
        $data['doppList']     = DB::table('previous_servicedetails')->where('fileNo', '=', $userID)->get();
        $data['doppservice']     = "";
        return view('DetailsOfPreviousService.update', $data)->with('msg', 'Operation was done successfully.');

    }


    //previouse service Report
    public function report($fileNo = null)
    {
        if(is_null($fileNo)){
            return redirect('profile/details');
        }
        if( !(DB::table('tblper')->where('fileNo', '=', $fileNo)->first())){
            return redirect('profile/details');
        }else{
            $data['staffFullDetailsPreviousService'] = DB::table('previous_servicedetails')
                    ->where('fileNo', '=', $fileNo)
                    ->orderBy('doppsid', 'Desc')
                    ->get();
            $data['staffFullDetails'] = DB::table('tblper')
                    ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                    ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                    ->where('tblper.fileNo', '=', $fileNo)
                    ->first();
        }
        return view('Report.PreviousServiceReport', $data);
    }


}
