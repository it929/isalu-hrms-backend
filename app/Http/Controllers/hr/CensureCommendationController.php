<?php
//
namespace App\Http\Controllers\hr;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Carbon\Carbon;
use DateTime;
use Auth;
use DB;

class CensureCommendationController extends ParentController
{
	public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
    	$this->division    = $request->session()->get('division');
		$this->divisionID  = $request->session()->get('divisionID');
	            return $next($request);
        });
    }	



    public function index($staffid = Null)
    {//->
    	//check if parameters are Null
    	if(is_null($staffid)){
    		return redirect('profile/details');
    	}
    	
    	$data['staffid'] = $staffid;
    	
    	if( !(DB::table('tblper')->where('ID', '=', $staffid)->first())){
    		return redirect('profile/details');
    	}else{
    		Session::put('staffid', $staffid);//set session 
    		$fileNo = Session::get('staffid');
    		$data['getStaff'] = DB::table('tblper')->where('ID', '=', $staffid)->first();
	    	if((DB::table('tblcensures_commendations')->where('staffid', '=', $staffid)->first()))
	    	{
	    		$data['details']    		= "";
				$data['commendationList']   = DB::table('tblcensures_commendations')
											->where('tblcensures_commendations.staffid', '=', $staffid)
											->join('tblper', 'tblper.ID', '=', 'tblcensures_commendations.staffid')
											->get();
	    		return view('CensureCommendation.create', $data);
	    	}else{
	    		$data['details']    		= "";
				$data['commendationList']   = "";
	    		return view('CensureCommendation.create', $data);
	    	}
    	}
    }//->


    public function view($id = Null)
    { //->
    	$staffid = Session::get('staffid');
    	$data['getStaff'] = DB::table('tblper')->where('ID', '=', $staffid)->first();
    	$data['staffid'] = Session::get('staffid');
    	if(is_null($id)){
    		return redirect('/commendations/create');
    	}else{ 
    	
	    	if((DB::table('tblcensures_commendations')->where('staffid', '=', $staffid)->first()))
	    	{
	    		$data['details']    		= DB::table('tblcensures_commendations')->where('id', $id)->first();
				$data['commendationList']   = DB::table('tblcensures_commendations')
											->where('tblcensures_commendations.staffid', '=', $staffid)
											->join('tblper', 'tblper.ID', '=', 'tblcensures_commendations.staffid')
											->get();
	    		return view('CensureCommendation.create', $data);
	    	}else{
	    		$data['details']    		= "";
				$data['commendationList']   = "";
	    		return view('CensureCommendation.create', $data);
	    	}
    	}
    }//->


     public function delete($id = Null)
    {
    	$fileNo = Session::get('fileNo');
    	if(is_null($fileNo))
    	{
   			return redirect('/commendations/create/'.$fileNo);
    	}
    	//delete
		DB::table('tblcensures_commendations')->where('id', '=', $id)->where('fileNo', '=', $fileNo)->delete();
		$this->addLog('censures and commendations deleted: ' . $this->division);
		return redirect('/commendations/create/'.$fileNo)->with('msg', 'Censures and commendations record deleted successfully');
    }


    public function store(Request $request)
    { 	
    	$staffid = Session::get('staffid');
    	if(is_null($staffid)){
    		return redirect('profile/details');
    	}
		$this->validate($request, 
		[
			'commendationDate'    	=> 'required|date',
			'summary'        		=> 'required|string',
			'compiledBy'      		=> 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
			'typeOfLeave'      		=> 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
			'leaveFrom'      		=> 'required|date',
			'leaveTo'      		    => 'required|date',
		]);
		$commendationDate         	= trim($request['commendationDate']);
		$summary             		= trim($request['summary']);
		$compiledBy       	   		= trim($request['compiledBy']);
		$id          	   			= trim($request['id']);
		$date   		       		= date("Y-m-d");
		$typeOfLeave             	= trim($request['typeOfLeave']);
		$leaveFrom             		= trim($request['leaveFrom']);
		$leaveTo             		= trim($request['leaveTo']);
		//Date conversion
		$date1 			= new DateTime($leaveFrom);
		$date2 			= new DateTime($leaveTo);
		$periodDay 		= floor($date1->diff($date2)->days + 1);

		//Update if hidden Name/id NOT empty
		if($id <> ""){
			DB::table('tblcensures_commendations')->where('id', $id)->where('staffid', $staffid)->update(array( 
				'commendationdate'         	=> $commendationDate,
				'summary'             		=> $summary,
				'checked_commendation'      => $compiledBy,
				'typeleave'      			=> $typeOfLeave,
				'leavefrom'      			=> $leaveFrom,
				'leaveto'      				=> $leaveTo,
				'numberday'      			=> $periodDay,
				'updated_at'            	=> $date
			));
			$this->addLog('Censure Commendation Record updated for Staff ID: '.$staffid .' Staff: ' . $staffid);
			$message = 'Censure Commendation Record updated successfully';
		}else{
			//insert if hidden Name/id is empty (but directly updating record)
			DB::table('tblcensures_commendations')->insert(array( 
				'staffid'					=> $staffid,
				'commendationdate'         	=> $commendationDate,
				'summary'             		=> $summary,
				'checked_commendation'      => $compiledBy,
				'typeleave'      			=> $typeOfLeave,
				'leavefrom'      			=> $leaveFrom,
				'leaveto'      				=> $leaveTo,
				'numberday'      			=> $periodDay,
				'created_at'            	=> $date,
				'updated_at'            	=> $date
			));
			$this->addLog('Censure Commendation Record created successfully Staff ID: '.$staffid .'Division: ' . $this->division);
			$message = 'Censure Commendation Record created successfully';
		}
		//
		return redirect('/commendations/create/'.$staffid)->with('msg', $message);	
	}


	//Record of Censures and commendations Report
    public function report($fileNo = null)
    {
        if(is_null($fileNo)){
            return redirect('profile/details');
        }
        if( !(DB::table('tblper')->where('fileNo', '=', $fileNo)->first())){
            return redirect('profile/details');
        }else{
            $data['staffFullDetailsCensuresCommendations'] = DB::table('tblcensures_commendations')
                    ->where('fileNo', '=', $fileNo)
                    ->orderBy('id', 'Desc')
                    ->get();
            $data['staffFullDetails'] = DB::table('tblper')
                    ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                    ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                    ->where('tblper.fileNo', '=', $fileNo)
                    ->first();
        }
        return view('Report.CensureCommendationReport', $data);
    }


}