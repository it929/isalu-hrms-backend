<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use Session;
use Carbon\Carbon;
use DateTime;
use Log;
class ContractorRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $getCompany = Session::get('contractorList');
        $getStatus = Session::get('statusList');

        $getTime1 = Session::get('datepicker1');
        $getTime2 = Session::get('datepicker2');
        //dd($getTime1);
        if($getTime1==null)
        {
            $getTime1 = Carbon::now()->subMonth();
            
        }

        if($getTime2==null)
        {
            $getTime2 = Carbon::now();

        }
        

        $data['picker1']=$getTime1;
        $data['picker2']=$getTime2;
        //dd( $data['picker1']);

        $data['companyId'] = $getCompany;
        $data['statusId'] = $getStatus;
        $data['contractor']= DB::table('tblcontractor')->where( 'type' ,'1')->select('id', 'contractor')->get();
        $data['getContractorTable'] = $this->QueryTable($getCompany,$getStatus,$getTime1 ,$getTime2);

        return view('contractorRecord.contractorRecord',$data);
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        Session::forget('contractorList');
        Session::forget('statusList');
        Session::forget('datepicker1');
        Session::forget('datepicker2');
        $getCompany = $request->input('getCompany');
        $getStatus = $request->input('getStatus');
        $getTime1 = $request->input('getTime1');
        $getTime2 = $request->input('getTime2');


        Session::put('contractorList', $getCompany);
        Session::put('statusList', $getStatus);
        Session::put('datepicker1', $getTime1);
        Session::put('datepicker2', $getTime2);

        return redirect('/contractor-record');
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function view($ContID)
    {
        //
        $data['error'] = "";
	   	$data['warning'] = "";
	   	$data['success'] = "";
         $data['transactions'] = $this->GetTransactions($ContID);
         $description = $this->ContractDescription($ContID);
         $data['description'] = $description;

         return view('contractorRecord.view', $data);
    }

    public function ContractDescription($ContID)
    {
        //

        $list = DB::select(" SELECT * FROM  tblcontractDetails WHERE `ID` = '$ContID'");
    			
	            return $list;
    }

    public function GetTransactions($ContID){

    	$list = DB::table('tblpaymentTransaction')
    			->join('tblcontractor', 'tblcontractor.id', '=', 'tblpaymentTransaction.companyID')
	            ->where('contractID', $ContID)
	            ->select('*', 'tblpaymentTransaction.status as stat')
	            ->orderby('tblpaymentTransaction.ID', 'DESC')
	            ->paginate(50);
	            return $list;
    }


    Public function QueryTable($getcompanyId,$getstatusId, $getTime1, $getTime2){
        $qcompany=1;
        if($getcompanyId!=''){$qcompany="`companyID`='$getcompanyId'";}

        $qstatus=1;
        if($getstatusId!=''){$qstatus="`approvalStatus`='$getstatusId'";}

        $List= DB::Select("SELECT tblcontractDetails.companyID, tblcontractDetails.ID as ContID, fileNo, 
        contractor, ContractDescriptions, contractValue, dateAward, 
        (SELECT IFNULL(sum(`totalPayment`),0) FROM `tblpaymentTransaction` WHERE 
        `tblpaymentTransaction`.`contractID`=`tblcontractDetails`.`ID` and  
        (`tblpaymentTransaction`.`status`=6 or `tblpaymentTransaction`.`status`=2) )as grosspayment
         FROM tblcontractDetails INNER JOIN tblcontractor ON 
        tblcontractDetails.companyID = tblcontractor.id WHERE $qcompany and $qstatus and (dateAward BETWEEN '$getTime1' AND '$getTime2') and voucherType=1");
        return $List;
        }
}
