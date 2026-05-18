<?php
//
namespace App\Http\Controllers\funds;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use Carbon\Carbon;
use DateTime;
use Auth;
use DB;
use file;

class GratuityPaymentController extends ParentController
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
    {//->
    	//check if parameters are Null
    	if(is_null($fileNo)){
    		return redirect('profile/details');
    	}
    	if( !(DB::table('tblper')->where('fileNo', '=', $fileNo)->first())){
    		return redirect('profile/details');
    	}else{
    		Session::put('fileNo', $fileNo);//set session 
    		$fileNo = Session::get('fileNo');
    		$data['getStaff'] = DB::table('tblper')->where('fileNo', '=', $fileNo)->first();
	    	if(count((DB::table('tblgratuity_payment')->where('fileNo', '=', $fileNo)->first())) > 0)
	    	{
	    		$data['details']    		= "";
				$data['gratuityList']    	= DB::table('tblgratuity_payment')
											->where('fileNo', '=', $fileNo)
											->get();
	    		return view('GratuityPayment.create', $data);
	    	}else{
	    		$data['details']    		= "";
				$data['gratuityList']    	= "";
	    		return view('GratuityPayment.create', $data);
	    	}
    	}
    }//->


    public function view($id = Null)
    { //->
    	$fileNo = Session::get('fileNo');
    	$data['getStaff'] = DB::table('tblper')->where('fileNo', '=', $fileNo)->first();
    	if(is_null($id)){
    		return redirect('/gratuity/create');
    	}else{ 
    	
	    	if((DB::table('tblgratuity_payment')->where('fileNo', '=', $fileNo)->first()))
	    	{
	    		$data['details']    		= DB::table('tblgratuity_payment')->where('id', $id)->first();
				$data['gratuityList']    	= DB::table('tblgratuity_payment')
											  ->where('tblgratuity_payment.fileNo', '=', $fileNo)
											  //->join('tblper', 'tblper.fileNo', '=', 'tblgratuity_payment.fileNo')
											  ->get();
	    		return view('GratuityPayment.create', $data);
	    	}else{
	    		$data['details']    		= "";
				$data['gratuityList']    	= "";
	    		return view('GratuityPayment.create', $data);
	    	}
    	}
    }//->


     public function delete($id = Null)
    {
    	$fileNo = Session::get('fileNo');
    	if(is_null($fileNo))
    	{
   			return redirect('/gratuity/create/'.$fileNo);
    	}
    	//delete
		DB::table('tblgratuity_payment')->where('id', '=', $id)->where('fileNo', '=', $fileNo)->delete();
		$this->addLog('Gratuity Payment was deleted: ' . $this->division);
		return redirect('/gratuity/create/'.$fileNo)->with('msg', 'Gratuity Payment record was deleted successfully');
    }


    public function store(Request $request)
    { 	
    	$fileNo = Session::get('fileNo');
    	if(is_null($fileNo)){
    		return redirect('profile/details');
    	}
		$this->validate($request, 
		[
			'dateOfPayment'   		=> 'required|date',
			'rateOfGratuity'        => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
			'periodFrom'      		=> 'required|date',
			'periodTo' 				=> 'required|date',
			//'periodYear'    		=> 'required|integer',
			//'periodMonth'      	=> 'required|integer',
			//'periodDay'      		=> 'image|integer',
			'amountPaid'      		=> 'required|integer',
			'pageRef'      			=> 'required|string',
			'gratuityCheckedBy'     => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
		]);
		$dateOfPayment        		= trim($request['dateOfPayment']);
		$rateOfGratuity             = trim($request['rateOfGratuity']);
		$periodFrom       	   		= trim($request['periodFrom']);
		$periodTo 					= trim($request['periodTo']);
		$amountPaid         		= trim($request['amountPaid']);
		$pageRef           			= trim($request['pageRef']);
		$gratuityCheckedBy       	= trim($request['gratuityCheckedBy']);
		$id          	   			= trim($request['id']);
		$date    		       		= date("Y-m-d");

		//Date conversion
		$date1 			= new DateTime($periodFrom);
		$date2 			= new DateTime($periodTo);
		$periodDay 		= floor($date1->diff($date2)->days + 1);
		$periodMonth 	= floor(($periodDay)/30);
		$periodYear 	= floor(($periodDay)/(30 * 12));
		//dd($periodDay ." | ". ($periodMonth) ." | ". ($periodYear));
		
		//Update if hidden Name/id NOT empty
		if($id <> ""){
			DB::table('tblgratuity_payment')->where('id', $id)->where('fileNo', $fileNo)->update(array( 
				'dateofpayment'   		=> $dateOfPayment,
				'rateofgratuity'        => $rateOfGratuity,
				'periodfrom'       		=> $periodFrom,
				'periodto'  			=> $periodTo,
				'periodyear'       		=> $periodYear,
				'periodmonth'      		=> $periodMonth,
				'periodday'            	=> $periodDay,
				'amountpaid'       		=> $amountPaid,
				'pageref'      			=> $pageRef,
				'gratuitycheckedby'     => $gratuityCheckedBy,
				'updated_at'            => $date
			));
			$recordSaved = $id;
			$logMessage = 'Gratuity Payment details updated';
			$message = 'Gratuity Payment details updated successfully';
		}else{
			//insert if hidden Name/id is empty
			$recordSaved = DB::table('tblgratuity_payment')->insertGetId(array( 
				'fileNo'   				=> $fileNo,
				'dateofpayment'   		=> $dateOfPayment,
				'rateofgratuity'        => $rateOfGratuity,
				'periodfrom'       		=> $periodFrom,
				'periodto'  			=> $periodTo,
				'periodyear'       		=> $periodYear,
				'periodmonth'      		=> $periodMonth,
				'periodday'            	=> $periodDay,
				'amountpaid'       		=> $amountPaid,
				'pageref'      			=> $pageRef,
				'gratuitycheckedby'     => $gratuityCheckedBy,
				'created_at'            => $date,
				'updated_at'            => $date
			));
			$logMessage = 'Gratuity Payment  details created';
			$message = 'Gratuity Payment record created successfully';
		}
		//
		$this->addLog($logMessage.' with '.$fileNo);
		return redirect('/gratuity/create/'.$fileNo)->with('msg', $message);	
	}



	//Gratuity Report
    public function report($fileNo = null)
    {
    	if(is_null($fileNo)){
    		return redirect('profile/details');
    	}
    	if( !(DB::table('tblper')->where('fileNo', '=', $fileNo)->first())){
    		return redirect('profile/details');
    	}else{
	        $data['staffFullDetailsGratuity'] = DB::table('tblgratuity_payment')
	    			->where('fileNo', '=', $fileNo)
	                ->orderBy('id', 'Desc')
	                ->get();
	        $data['staffFullDetails'] = DB::table('tblper')
	                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
	                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
	                ->where('tblper.fileNo', '=', $fileNo)
	                ->first();
        }
        return view('Report.GratuityReport', $data);
    }


}