<?php

namespace App\Http\Controllers\payroll;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;

class DetailOfServiceController extends ParentController
{

    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->division    = $request->session()->get('division');
        $this->divisionID  = $request->session()->get('divisionID');
                return $next($request);
        });
    }   

   
    public function index($fileNo = Null)
    {
        //check if parameters are Null
        if(is_null($fileNo) && (DB::table('tblper')->where('fileNo', '=', $fileNo)->first())){
            return redirect('/profile/details');
        }
         //set session
         Session::put('fileNo', $fileNo);
        $data['names'] = DB::table('tblper')->where('fileNo', '=', $fileNo)->first();
        if(!(DB::table('detailsofservice')->where('fileNo', '=', $fileNo)->first())){
            $data['doservice']    = '';
            $data['dosList']      = '';
            return view('DetailsOfService.update', $data);
        }
        else{
            $data['doservice']  = "";
            $data['dosList']    = DB::table('detailsofservice')->where('fileNo', '=', $fileNo)->get();
            //
            return view('DetailsOfService.update', $data);
        }
    }



     public function view($dosid = Null)
    {
        //
        $fileNo = Session::get('fileNo');
        if(is_null($dosid)){
            return redirect('/update/detailofservice/'.$fileNo);
        }
        if( !(DB::table('detailsofservice')->where('fileNo', '=', $fileNo)->where('dosid', $dosid)->first())){
           return redirect('/update/detail-service/'.$fileNo);
        }//
        $data['names'] = DB::table('tblper')->where('fileNo', '=', $fileNo)->first();
        if(!(DB::table('detailsofservice')->where('dosid', '=', $dosid)->first())){
            $data['dosList']    = "";
            $data['doservice']  =  "";
            return view('DetailsOfService.update', $data);
        }
        else{
            $data['doservice']  = DB::table('detailsofservice')->where('fileNo','=',$fileNo)->where('dosid','=',$dosid)->first();
            $data['dosList']    = DB::table('detailsofservice')->where('fileNo','=',$fileNo)->get();
            //
            return view('DetailsOfService.update', $data);
        }
    }




    public function update(Request $request)
    {
        $fileNo = Session::get('fileNo');
         $this->validate($request, 
        [
            'armofservice'          => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
            'servicenum'            => 'regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
            'lastunit'              => 'regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
            'reason'                => 'required|string',
        ]);
        $arm                        = trim($request['armofservice']);
        $servicenum                 = trim($request['servicenum']);
        $lastunit                   = trim($request['lastunit']);
        $reason                     = trim($request['reason']);
        $dosid                      = trim($request['dosid']);
        $hiddenName                 = trim($request['hiddenName']);
        $date                       = date("Y-m-d");
        
        if($hiddenName <> ""){
            DB::table('detailsofservice')->where('dosid', '=', $dosid)->where('fileNo', '=', $fileNo)->update(array(  
                'fileNo'             => $fileNo, 
                'serviceNumber'      =>$servicenum,
                'armOfservice'       => $arm, 
                'lastUnit'           => $lastunit, 
                'reasonForLeaving'   => $reason
            ));
            $this->addLog('Details of service updated ID: '.$dosid .' and division: ' . $this->division);
        }
        else{                                           
           //insert if hidden Name is empty
            DB::table('detailsofservice')->insert(array( 
                'fileNo'             => $fileNo,
                'serviceNumber'      => $servicenum, 
                'armOfservice'       => $arm, 
                'lastUnit'           => $lastunit, 
                'reasonForLeaving'   => $reason
            ));
            $this->addLog('Details of service added and division: ' . $this->division);
        }
        return redirect('/update/detail-service/'.$fileNo)->with('msg', 'Operation was done successfully.');
    }

    
    public function destroy($fileNo,$dosid)
    {
        $fileNo = Session::get('fileNo');
        if(is_null($fileNo) || is_null($dosid)){
            $data['doservice']    = DB::table('detailsofservice')->where('fileNo', '=', $fileNo)->first();
            $data['dosList']      = DB::table('detailsofservice')->where('fileNo', '=', $fileNo)->get();
            return view('detailofservice.update', $data);
        }
        //delete
        DB::table('detailsofservice')->where('fileNo', '=', $fileNo)->where('dosid', '=', $dosid)->delete();
        $this->addLog('detail of Service deleted and division: ' . $this->division);
        //check if parameters exist in DB
        if(!(DB::table('detailsofservice')->where('fileNo', '=', $fileNo)->first())){
            return view('main.userArea');
        }
        //populate return view('main.userArea');
        //$data['nextOfKin']   = DB::table('tblnextofkin')->where('userID', '=', $userID)->first();
        $data['dosList']     = DB::table('detailsofservice')->where('fileNo', '=', $fileNo)->get();
        $data['doservice']   = "";
        return view('details-service.update', $data)->with('msg', 'Operation was done successfully.');

    }


    //Details of service in force Report
    public function report($fileNo = null)
    {
        if(is_null($fileNo)){
            return redirect('profile/details');
        }
        if( !(DB::table('tblper')->where('fileNo', '=', $fileNo)->first())){
            return redirect('profile/details');
        }else{
            $data['staffFullDetailsDetailsService'] = DB::table('detailsofservice')
                    ->where('fileNo', '=', $fileNo)
                    ->orderBy('dosid', 'Desc')
                    ->get();
            $data['staffFullDetails'] = DB::table('tblper')
                    ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                    ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                    ->where('tblper.fileNo', '=', $fileNo)
                    ->first();
        }
        return view('Report.DetailsServiceForceReport', $data);
    }


}
