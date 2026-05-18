<?php

namespace App\Http\Controllers\hr;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;

class RecordOfEmolumentsController extends parentController
{
     public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->division    = $request->session()->get('division');
        $this->divisionID  = $request->session()->get('divisionID');
                return $next($request);
        });
    }   


    public function index($fileNo = Null, $emolumentID = Null)
    {
        $data['entryList'] = DB::table('recordof_service')->get();
        $data['entdate']="";
        $data['recordDetail'] = DB::table('recordof_service')->where('fileNo', '=', $fileNo)->first();
        $data['names'] = DB::table('tblper')->where('fileNo', '=', $fileNo)->first();
        if(is_null($fileNo)){
            return view('main.userArea');
        }
        if(!(DB::table('recordof_emolument')->where('fileNo', '=', $fileNo)->first())){
            //set session
            Session::put('fileNo', $fileNo);
            $data['emolument']    = '';
            $data['emolumentList']      = '';
            $data['entdate'] = "";
           
            return view('recordOfEmolument.update', $data);
        }
        else{
            //set session
            Session::put('fileNo', $fileNo);

            if(is_null($emolumentID)){
                $data['emolument']  = "";
                $data['entdate'] = "";
            }
            else{
                //check if dosid parameters exist in DB
               if(!(DB::table('recordof_emolument')->where('emolumentID', '=', $emolumentID)->first())){
                   return view('main.userArea');
                }
                $data['entdate'] = DB::table('recordof_emolument')->where('fileNo', '=', $fileNo)->first();
                $data['emolument'] = DB::table('recordof_emolument')
                ->join('recordof_service','recordof_service.recID','=','recordof_emolument.entryDateMade')
                ->where('recordof_emolument.fileNo','=',$fileNo)
                ->where('recordof_emolument.emolumentID','=',$emolumentID)
                ->first();
            }
            //$data['entdate'] = DB::table('recordof_emolument')->where('fileNo', '=', $fileNo)->first();
            $data['emolumentList']       = DB::table('recordof_emolument')
            ->join('recordof_service','recordof_service.recID','=','recordof_emolument.entryDateMade')
            ->where('recordof_emolument.fileNo', '=', $fileNo)->get();
            //
            return view('recordOfEmolument.update', $data);
        }
    }

    
     public function getDetail(request $request)
     {
        $id = $request['entrydate'];
        $data =DB::table('recordof_service')->where('recID', '=', $id)->first();
         return response()->json($data);
     }
    
    public function update(Request $request)
    {
            $userID = Session::get('fileNo');
        if(is_null($userID)){
            return view('main.userArea');
        }
         $this->validate($request, 
        [
            'entrydate'             => 'required|string',
            'salaryscale'           => 'numeric',
            'basicsalarypa'         => 'numeric',
            'inducement'            => 'regex:/^[A-Za-z0-9\-\s]+$/',
            'datepaidfrom'          => 'date',
            //'month_year'            => 'date',
            'authority'             => 'regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
            'signature'             => 'regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',  
        ]);
        $entrydate                  = trim($request['entrydate']);
        $salaryscale                = trim($request['salaryscale']);
        $basicsalarypa              = trim($request['basicsalarypa']);
        $inducement                 = trim($request['inducement']);
        $month_year                 = date('Y-m-d', strtotime(trim($request['month_year'])));
        $month                      = date("m", strtotime($month_year));
        $year                       = date("Y", strtotime($month_year));
        $datepaidfrom               = date('Y-m-d', strtotime(trim($request['datepaidfrom'])));
        $signature                  = trim($request['signature']);
       $authority                   = trim($request['authority']);
        $emolid                     = trim($request['emolid']);
        $hiddenName                 = trim($request['hiddenName']);
        $date                       = date("Y-m-d");
        if($hiddenName <> ""){
            DB::table('recordof_emolument')->where('emolumentID', '=', $emolid)->where('fileNo', '=', $userID)->update(array( 
                'fileNo'            => $userID, 
                'entryDateMade'     => $entrydate ,
                'salaryScale'       => $salaryscale,
                'basicSalaryPA'     => $basicsalarypa,
                'inducementPayPA'   => $inducement,
                'datePaidFrom'      =>  $datepaidfrom,
                'month'             => $month,
                'year'              => $year,
                'authority'         => $authority,
                'signature'         => $signature,
                'updated_at'        => $date
            ));
            $this->addLog('Record Of emolument was updated with ID: '.$emolid .' and division: ' . $this->division);
        }
        else{
           //insert if hidden Name is empty
            DB::table('recordof_emolument')->insert(array( 
                'fileNo'                => $userID, 
                'entryDateMade'         => $entrydate ,
                'salaryScale'           => $salaryscale, 
                'basicSalaryPA'         => $basicsalarypa,
                'inducementPayPA'       => $inducement,
                'datePaidFrom'          =>  $datepaidfrom,
                'month'                 => $month,
                'year'                  => $year,
                'authority'             => $authority,
                'signature'             => $signature,
                'updated_at'            => $date  
            ));
            $this->addLog('New Record of Emolument was added and division: ' . $this->division);
        }
        
        $data['emolument']              = "";
        $data['emolumentList']          = DB::table('recordof_emolument')
        ->join('recordof_service','recordof_service.recID','=','recordof_emolument.entryDateMade')
        ->where('recordof_emolument.fileNo', '=', $userID)->get();
        return redirect('/update/recordofemolument/'.$userID)->with('msg', 'record was updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($fileNo,$dosid)
    {
        $userID = Session::get('fileNo');
        if(is_null($userID) || is_null($fileNo)){
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
        //populate return view('main.userArea');
        //$data['nextOfKin']   = DB::table('tblnextofkin')->where('userID', '=', $userID)->first();
        $data['recordList']     = DB::table('recordof_service')->where('fileNo', '=', $userID)->get();
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
            $data['staffFullDetailsRecordEmolument'] = DB::table('recordof_emolument')
                    ->join('recordof_service','recordof_service.recID','=','recordof_emolument.entryDateMade')
                    ->where('recordof_emolument.fileNo', '=', $fileNo)
                    //->where('recordof_emolument.emolumentID','=',$emolumentID)
                    ->orderBy('recordof_emolument.emolumentID', 'Desc')
                    ->get();
            $data['staffFullDetails'] = DB::table('tblper')
                    ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                    ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                    ->where('tblper.fileNo', '=', $fileNo)
                    ->first();
        }
        return view('Report.EmolumentReport', $data);
    }
}
