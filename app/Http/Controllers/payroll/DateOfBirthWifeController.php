<?php
//
namespace App\Http\Controllers\payroll;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Carbon\Carbon;
use Auth;
use DB;

class DateOfBirthWifeController extends ParentController
{
	public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
    	$this->division    = $request->session()->get('division');
		$this->divisionID  = $request->session()->get('divisionID');
	            return $next($request);
        });
    }	



    public function create($fileNo = Null)
    {	
    	//check if parameters are Null
    	if(is_null($fileNo) && (DB::table('tblper')->where('fileNo', '=', $fileNo)->first())){
    		return redirect('/profile/details');
    	}
    	//set session 
    	Session::put('fileNo', $fileNo);
    	$getStaff = DB::table('tblper')->where('fileNo', '=', $fileNo)->first();
    	if((DB::table('tbldateofbirth_wife')
    		->where('wifename', '<>', "")
    		->where('wifedateofbirth', '<>', "")
    		->where('wifedateofbirth', '<>', "0000-00-00")
    		->where('checkedby1', '<>', "")
    		->first())){
    		$data['details'] 	= "";
			$data['KinList'] 	= DB::table('tbldateofbirth_wife')->where('fileNo', '=', $fileNo)->get();
			$data['getStaff'] 			= $getStaff;
    		return view('dateOfBirthWife.create', $data);
    	}else{
    		$data['details'] 	= "";
			$data['KinList'] 	= "";
			$data['getStaff'] 			= $getStaff;
    		return view('dateOfBirthWife.create', $data);
   		}
    
    }


     public function delete($fileNo = Null)
    {
    	$fileNo = Session::get('fileNo');
    	$getStaff = DB::table('tblper')->where('fileNo', '=', $fileNo)->first();
    	if(is_null($fileNo))
    	{
    		$data['details']    = DB::table('tbldateofbirth_wife')->where('fileNo', '=', $fileNo)->first();
			$data['KinList']    = DB::table('tbldateofbirth_wife')->where('fileNo', '=', $fileNo)->get();
			$data['getStaff'] 			= $getStaff;
   			return view('dateOfBirthWife.create', $data);
    	}
    	//delete
		DB::table('tbldateofbirth_wife')->where('fileNo', '=', $fileNo)->update(array( 
				'maritalstatus'         => "",
				'homeplace'             => "",
				'dateofbirth'       	=> "",
				'dateofmarriage'        => "",
				'wifename'          	=> "",
				'wifedateofbirth'	    => "",
				'checkedby1'            => "",
				'checkedby2'			=> "",
				'updated_at'            => date("Y-m-d")
			));
		$this->addLog('Date of birth deleted and division: ' . $this->division);
		//
		return redirect('/particular/wife/create/'.$fileNo)->with('msg', 'You successfully deleted a record.');
    }


    public function view($ID = Null)
    { //->
    	$fileNo = Session::get('fileNo');
    	$getStaff = DB::table('tblper')->where('fileNo', '=', $fileNo)->first();
    	if(is_null($ID)){
    		return redirect('/particular/details/'.$fileNo);
    	}else{ 
    	
	    	if((DB::table('tbldateofbirth_wife')->where('fileNo', '=', $fileNo)->first()))
	    	{
	    		$data['details']        = DB::table('tbldateofbirth_wife')->where('particularID', '=', $ID)->where('fileNo', $fileNo)->first();
				$data['KinList']    	= DB::table('tbldateofbirth_wife')->where('fileNo', $fileNo)->get();
				$data['getStaff'] 		= $getStaff;
	    		return view('dateOfBirthWife.create', $data);
	    	}else{
	    		$data['details']    	= "";
				$data['KinList']    	= "";
				$data['getStaff'] 		= $getStaff;
	    		return view('dateOfBirthWife.create', $data);
	    	}
    	}
    }//->


    public function store(Request $request)
    { 	
    	$fileNo = Session::get('fileNo');
    	if(is_null($fileNo)){
    		return redirect('/profile/details/'.$fileNo);
    	}
		$this->validate($request, 
		[
			'homePlace'        => 'string',
			'dateOfMarriage'   => 'required|date',
			'wifeName'         => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
			'wifeDateOfBirth'  => 'required|date',
			'checkedBy'        => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
			'checkedBy2'       => 'regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
		]);
		$homePlace             = trim($request['homePlace']);
		$dateOfMarriage        = trim($request['dateOfMarriage']);
		$wifeName          	   = trim($request['wifeName']);
		$wifeDateOfBirth	   = trim($request['wifeDateOfBirth']);
		$checkedBy             = trim($request['checkedBy']);
		$checkedBy2			   = trim($request['checkedBy2']);
		$hiddenName			   = trim($request['hiddenName']);
		$particularID          = trim($request['particularID']);
		$date    		       = date("Y-m-d");
		
		//Update if hidden Name not empty
		if($hiddenName <> ""){
			DB::table('tbldateofbirth_wife')->where('fileNo', '=', $fileNo)->where('particularID', '=', $hiddenName)->update(array( 
				'homeplace'             => $homePlace,
				'dateofmarriage'        => $dateOfMarriage,
				'wifename'          	=> $wifeName,
				'wifedateofbirth'	    => $wifeDateOfBirth,
				'checkedby1'            => $checkedBy,
				'checkedby2'			=> $checkedBy2,
				'updated_at'            => $date
			));
			$this->addLog('Record updated for fileNo: '.$fileNo .'on  wife Date Of Birth and division: ' . $this->division);
		}else{
			//insert if hidden Name is empty (but directly updating record)
			DB::table('tbldateofbirth_wife')->insert(array( 
				'fileNo'                => $fileNo,
				'homeplace'             => $homePlace,
				'dateofmarriage'        => $dateOfMarriage,
				'wifename'          	=> $wifeName,
				'wifedateofbirth'	    => $wifeDateOfBirth,
				'checkedby1'            => $checkedBy,
				'checkedby2'			=> $checkedBy2,
				'updated_at'            => $date
			));
			$this->addLog('wife date of birth record added for fileNo: '.$fileNo .', division: ' . $this->division);
		}
		//
		return redirect('/particular/wife/create/'.$fileNo)->with('msg', 'You have successfully updated your record.');	
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
            $data['staffFullDetailsParticularWife'] = DB::table('tbldateofbirth_wife')
                    ->where('fileNo', '=', $fileNo)
                    ->orderBy('particularID', 'Desc')
                    ->get();
            $data['staffFullDetails'] = DB::table('tblper')
                    ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                    ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                    ->where('tblper.fileNo', '=', $fileNo)
                    ->first();
        }
        return view('Report.ParticularWifeReport', $data);
    }


}