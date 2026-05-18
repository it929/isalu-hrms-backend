<?php

namespace App\Http\Controllers\payroll;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use Carbon\Carbon;
use Entrust;
use Session;
use Excel;
use Input;
use DB;
use QrCode;


class VoteChangeController extends functionController
{

	public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->activeMonth = $request->session()->get('activeMonth');
        $this->activeYear = $request->session()->get('activeYear');
                return $next($request);
        });
    }


    



	public function ecoChange(Request $request, $contractID = null) 
	{	
	    $updateEconomicCode = null;
	    
		Session::forget('contractID');
		//Session::forget('currentDivisionID');

		if(!DB::table('tblcontractDetails')->where('ID', '=', $contractID )->first()){
			return redirect('voucherDisplay/displayVoucher')->with('err', 'Voucher not found !!!');
		}
		$data['economicsource'] = trim($request['economicsource']);
	    $data['economicdest'] = trim($request['economicdest']);
	     $data['economicGroup'] = trim($request['economicGroup']);
	   if($data['economicGroup']=='') {$data['economicGroup']=session('economicGroup');}
		$data['contractinfo']=DB::table('tblcontractDetails')
		->select('tblcontractDetails.*', 'tblcontractor.contractor','tbleconomicCode.economicCode','tbleconomicCode.description')
		->where('tblcontractDetails.ID', '=', $contractID )
		->Join('tblcontractor','tblcontractor.id','=','tblcontractDetails.companyID')
		->leftJoin('tbleconomicCode','tbleconomicCode.ID','=','tblcontractDetails.economicVoult')
		->first();
		//dd($data['contractinfo']);
		$data['contractcomments']=DB::table('tblcomments')
		->select('tblcomments.*', 'users.name')
		->Join('users','users.username','=','tblcomments.username')->where('affectedID', '=', $contractID )->get();
		
		$data['precontractcomments']=DB::table('contract_comment')
		->select('contract_comment.*', 'users.name')
		->Join('users','users.id','=','contract_comment.userID')->where('fileNoID', '=', $data['contractinfo']->fileNo)
		->where('fileNoID', '<>', '')->get();
		$data['fileattach']=$this->ContractAttachment($contractID);
		if ( isset( $_POST['update'] ) ) {
		$this->validate($request, [
		'economicsource'      	    => 'required'
		]);
			
		//update payment transaction and check if successful
		$updateEconomicCode = DB::table('tblpaymentTransaction')->where('contractID',$contractID)->where('vstage',1)->update([
                                'economicCodeID' => $data['economicsource'],
                                'allocationType' => $data['economicGroup'],
                                ]);
		if($updateEconomicCode)
        {
            DB::table('tblcontractDetails')->where('ID',$contractID)->update([
                'economicVoult' => $data['economicsource'],
                'contract_Type' => $data['economicGroup'],
            ]);
        }
                 return back()->with('message','record successfully updated.'  );
		 }
		 $data['EconomicCode'] = $this->EconomicCode2('5',$data['economicGroup']);
		$data['EconomicGroup'] = $this->BudgetType();
		return view('Report.changeecoid', $data);

		
	}



	
	



	
}//End class
