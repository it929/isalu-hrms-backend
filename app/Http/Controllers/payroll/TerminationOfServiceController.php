<?php

namespace App\Http\Controllers\payroll;

namespace App\Http\Controllers\payroll;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;

class TerminationOfServiceController extends ParentController
{
     public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->division    = $request->session()->get('division');
        $this->divisionID  = $request->session()->get('divisionID');
                return $next($request);
        });
    }   

    public function index($fileNo = Null, $terminateID = Null)
    {
            if(is_null($fileNo)){
            return view('main.userArea');
        }
        $data['names'] = DB::table('tblper')->where('fileNo', '=', $fileNo)->first();
        if(!(DB::table('service_termination')->where('fileNo', '=', $fileNo)->first())){
            //set session
            Session::put('fileNo', $fileNo);
            $data['terminationservice']    = '';
            $data['terminationList']      = '';
            return view('terminationOfService.update', $data);
        }
        else{
            //set session
            Session::put('fileNo', $fileNo);

            if(is_null($terminateID)){
                $data['terminationservice']  = "";
            }
            else{
               if(!(DB::table('service_termination')->where('terminateID', '=', $terminateID)->first())){
                   return view('main.userArea');
                }
                $data['terminationservice'] = DB::table('service_termination')->where('fileNo','=',$fileNo)->where('terminateID','=',$terminateID)->first();
            }
            $data['terminationList']       = DB::table('service_termination')->where('fileNo', '=', $fileNo)->first();
            //
            return view('terminationOfService.update', $data);
        }
    }


    public function getRecord(Request $request)
    {       
        $uid = $request->input('usid');
        $staffRecord = DB::table('service_termination')->where('fileNo', '=',$uid )->first();
        return response()->json($staffRecord);
    }


    public function update(Request $request)
    {
            $userID = Session::get('fileNo');
        if(is_null($userID)){
            return redirect('profile/details');
        }
        $terminationdate=date('Y-m-d', strtotime(trim($request['terminationdate'])));
        $pencont=trim($request['pencont']);
        $penamt=trim($request['penamt']);
        $peranum=date('Y-m-d', strtotime(trim($request['peranum']))); 
        $gratuity=trim($request['gratuity']);
        $contractgratuity=trim($request['contractgratuity']);
        $re              = trim($request['resignation']);
        $date            = date("Y-m-d");
        
        if($re=='resignation'){
            $count = DB::table('service_termination')->where('fileNo', '=',$userID )->count();
            if($count ==1){
                DB::table('service_termination')->where('fileNo', '=', $userID)->update(array( 
                    'dateTerminated'=>$terminationdate,
                    'pension_contract_terminate'=>$pencont ,
                    'pensionAmount'=>$penamt,
                    'pensionperanumfrom' => $peranum, 
                    'gratuity'=>$gratuity,
                    'contractGratuity'=>$contractgratuity,
                    'updated_at' => $date,
                ));
            }
            else{
                DB::table('service_termination')->insert(array( 
                    'fileNo'           => $userID, 
                    'dateTerminated'=>$terminationdate,
                    'pension_contract_terminate'=>$pencont ,
                    'pensionAmount'=>$penamt,
                    'pensionperanumfrom' => $peranum, 
                    'gratuity'=>$gratuity,
                    'contractGratuity'=>$contractgratuity,
                    'updated_at' => $date,
                ));
            }
        }
        return redirect('/update/termination/'.$userID)->with('msg', 'Operation was done successfully.');
    }
    public function editRecords(request $request)
    {
         $userID = Session::get('fileNo');
        if(is_null($userID)){
            return view('main.userArea');
        }

         $transferdate=date('Y-m-d', strtotime(trim($request['transferdate'])));
        $transpencon=trim($request['transpencon']);
        $years=trim($request['years']);
        $months=trim($request['months']); 
        $days=trim($request['days']);
        $aggrsalary=trim($request['aggrsalary']);

        
        $re            = trim($request['resignation']);
        $date                  = date("Y-m-d");
        $tr            = trim($request['bytransfer']);

        if($tr == 'bytransfer')
        {
             $count = DB::table('service_termination')->where('fileNo', '=',$userID )->count();
           
            if($count ==1){

                  DB::table('service_termination')->where('fileNo', '=', $userID)->update(array( 
                //'userID'           => $userID, 
                //'userId'           => $userID, 
                
                'updated_at' => $date,

        'dateOfTransfer'=>$transferdate,
        'pension_contract_transfer'=>$transpencon,
        'aggregateYears'=>$years,
        'aggregateMonths'=>$months, 
        'aggregateDays'=>$days,
        'aggregateSalary'=>$aggrsalary,
           
        

        ));
        }
        else{

              DB::table('service_termination')->insert(array( 
                //'userID'           => $userID, 
                'fileNo'           => $userID, 
                
                'updated_at' => $date,
        'dateOfTransfer'=>$transferdate,
        'pension_contract_transfer'=>$transpencon,
        'aggregateYears'=>$years,
        'aggregateMonths'=>$months, 
        'aggregateDays'=>$days,
        'aggregateSalary'=>$aggrsalary,
           
));
         
        }


    }

    return redirect('/update/termination/'.$userID)->with('msg', 'Operation was done successfully.');
        

    }


    public function modifyRecords(request $request)
    {
         $userID = Session::get('fileNo');
        if(is_null($userID)){
            return view('main.userArea');
        }
       
         $dateofdeath=date('Y-m-d', strtotime(trim($request['dateOfDeath'])));
        $gratuityestate =trim($request['gratuityPaidEstate']);
        $widowspension=trim($request['widowsPension']);
        $widperanum =date('Y-m-d', strtotime(trim($request['widowsPensionfrom']))); 
        $orphanpen =trim($request['orphanPension']);
        $orpanperanum = date('Y-m-d', strtotime(trim($request['orphanPensionFrom'])));

        $date                  = date("Y-m-d");
        $tr            = trim($request['hiddenName']);
        if($tr == 'terminateby')
        {
             $count = DB::table('service_termination')->where('fileNo', '=',$userID )->count();
           
           if($count ==1){

                DB::table('service_termination')->where('fileNo', '=', $userID)->update(array( 
                'updated_at' => $date,
                'dateOfDeath'=>$dateofdeath,
                'gratuityPaidEstate'=>$gratuityestate,
                'widowsPension' => $widowspension,
                'widowsPensionfrom'=>$widperanum, 
                'orphanPension'=>$orphanpen,
                'orphanPensionFrom'=>$orpanperanum,
                ));
            }
            else{
                DB::table('service_termination')->insert(array( 
                    //'userID'           => $userID, 
                    'fileNo'           => $userID, 
                    'updated_at' => $date,        
                    'dateOfDeath'=>$dateofdeath,
                    'gratuityPaidEstate'=>$gratuityestate,
                    'widowsPension' => $widowspension,
                    'widowsPensionfrom'=>$widperanum, 
                    'orphanPension'=>$orphanpen,
                    'orphanPensionFrom'=>$orpanperanum,
                ));
            }
        }
        return redirect('/update/termination/'.$userID)->with('msg', 'Operation was done successfully.');
    }

    public function destroy($userId,$terminateID)
    {
        $userID = Session::get('fileNo');
        if(is_null($userID) || is_null($terminateID)){
            $data['terminationservice']    = DB::table('service_termination')->where('fileNo', '=', $userID)->first();
            $data['terminationList']      = DB::table('service_termination')->where('fileNo', '=', $userID)->get();
            return view('TerminationOfService.update', $data);
        }
        //delete
        DB::table('service_termination')->where('fileNo', '=', $userID)->where('terminateID', '=', $terminateID)->delete();
        $this->addLog('Termination of Service deleted and division: ' . $this->division);
        //check if parameters exist in DB
       
        //populate return view('main.userArea');
        //$data['nextOfKin']   = DB::table('tblnextofkin')->where('userID', '=', $userID)->first();
        $data['terminationList']     = DB::table('service_termination')->where('fileNo', '=', $userID)->first();
        //$data['terminationservice']     = "";
        return view('terminationOfService.update', $data)->with('msg', 'Operation was done successfully.');
    }



    //Termination Report
    public function report($fileNo = null)
    {
        if(is_null($fileNo)){
            return redirect('profile/details');
        }
        if( !(DB::table('tblper')->where('fileNo', '=', $fileNo)->first())){
            return redirect('profile/details');
        }else{
            $data['staffFullDetailsTerminationService'] = DB::table('service_termination')
                    ->where('fileNo', '=', $fileNo)
                    ->orderBy('terminateid', 'Desc')
                    ->get();
            $data['staffFullDetails'] = DB::table('tblper')
                    ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                    ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                    ->where('tblper.fileNo', '=', $fileNo)
                    ->first();
        }
        return view('Report.TerminationServiceReport', $data);
    }


}
