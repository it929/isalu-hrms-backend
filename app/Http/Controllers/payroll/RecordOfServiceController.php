<?php

namespace App\Http\Controllers\payroll;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;

class RecordOfServiceController extends parentController
{
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->division    = $request->session()->get('division');
        $this->divisionID  = $request->session()->get('divisionID');
                return $next($request);
        });
    }   

    public function index($fileNo = Null, $recID = Null)
    {
            if(is_null($fileNo)){
            return view('main.userArea');
        }
        $data['names'] = DB::table('tblper')->where('fileNo', '=', $fileNo)->first();
        if(!(DB::table('recordof_service')->where('fileNo', '=', $fileNo)->first())){
            //set session
            Session::put('fileNo', $fileNo);
            $data['recordservice']    = '';
            $data['recordList']      = '';
            return view('RecordOfService.update', $data);
        }
        else{
            //set session
            Session::put('fileNo', $fileNo);

            if(is_null($recID)){
                $data['recordservice']  = "";
            }
            else{
                //check if dosid parameters exist in DB
               if(!(DB::table('recordof_service')->where('recID', '=', $recID)->first())){
                   return view('main.userArea');
                }
                $data['recordservice'] = DB::table('recordof_service')->where('fileNo','=',$fileNo)->where('recID','=',$recID)->first();
            }
            $data['recordList']       = DB::table('recordof_service')->where('fileNo', '=', $fileNo)->get();
            //
            
            return view('RecordOfService.update', $data);
        }
    }

       
    public function update(Request $request)
    {
            $userID = Session::get('fileNo');
        if(is_null($userID)){
            return view('main.userArea');
        }
         $this->validate($request, 
        [
            'entrydate'          => 'required|date',
            'detail'             => 'required|string',
            'signature'          => 'regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
        ]);
        $entrydate              = date('Y-m-d', strtotime(trim($request['entrydate'])));
        $detail                 = trim($request['detail']);
        $signature              = trim($request['signature']);
        $recid                  = trim($request['recid']);
        $hiddenName             = trim($request['hiddenName']);
        $date                   = date("Y-m-d");
        
        if($hiddenName <> ""){
            DB::table('recordof_service')->where('recID', '=', $recid)->where('fileNo', '=', $userID)->update(array( 
                'fileNo'        => $userID, 
                'entryDate'     => $entrydate,
                'detail'        => $detail, 
                'signature'     => $signature, 
                'updated_at'    => $date
            ));
            $this->addLog('Record Of Service was updated with ID: '.$recid .' and division: ' . $this->division);
        }
        else{
           //insert if hidden Name is empty
            DB::table('recordof_service')->insert(array( 
                'fileNo'         => $userID, 
                'entryDate'      => $entrydate,
                'detail'         => $detail, 
                'signature'      => $signature,         
                'updated_at'     => $date
            ));
            $this->addLog('New Next of Kin was added and division: ' . $this->division);
        }
        $data['recordservice']   = "";
        $data['recordList']      = DB::table('recordof_service')->where('fileNo', '=', $userID)->get();
        return redirect('/update/recordofservice/'.$userID)->with('msg', 'Operation was done successfully.');
    }
    public function destroy($fileNo,$dosid)
    {
        $userID = Session::get('fileNo');
        if(is_null($userID) || is_null($dosid)){
            $data['doservice']    = DB::table('recordof_service')->where('fileNo', '=', $userID)->first();
            $data['dosList']      = DB::table('recordof_service')->where('fileNo', '=', $userID)->get();
            return view('detailofservice.update', $data);
        }
        //delete
        DB::table('recordof_service')->where('fileNo', '=', $userID)->where('dosid', '=', $dosid)->delete();
        $this->addLog('detail of Service deleted and division: ' . $this->division);
        //check if parameters exist in DB
        if(!(DB::table('recordof_service')->where('fileNo', '=', $userID)->first())){
            return view('main.userArea');
        }
        $data['recordList']     = DB::table('recordof_service')->where('userID', '=', $userID)->get();
        $data['recordservice']     = "";
        return view('detailsofservice.update', $data)->with('msg', 'Operation was done successfully.');

    }


    //Record of service Report
    public function report($fileNo = null)
    {
        if(is_null($fileNo)){
            return redirect('profile/details');
        }
        if( !(DB::table('tblper')->where('fileNo', '=', $fileNo)->first())){
            return redirect('profile/details');
        }else{
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
