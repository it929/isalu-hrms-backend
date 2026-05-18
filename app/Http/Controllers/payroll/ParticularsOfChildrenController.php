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

class ParticularsOfChildrenController extends ParentController
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
    		$getStaff = DB::table('tblper')->where('fileNo', '=', $fileNo)->first();
	    	if((DB::table('tblchildren_particulars')->where('fileNo', '=', $fileNo)->first()))
	    	{
	    		$data['details']    		= "";
				$data['childrenList']    	= DB::table('tblchildren_particulars')->where('fileNo', '=', $fileNo)->get();
				$data['getStaff'] 			= $getStaff;
	    		return view('ParticularsOfChildren.create', $data);
	    	}else{
	    		$data['details']    		= "";
				$data['childrenList']    	= "";
				$data['getStaff'] 			= $getStaff;
	    		return view('ParticularsOfChildren.create', $data);
	    	}
    	}
    }//->


    public function view($id = Null)
    { //->
    	$fileNo = Session::get('fileNo');
    	$getStaff = DB::table('tblper')->where('fileNo', '=', $fileNo)->first();
    	if(is_null($id)){
    		return redirect('/children/create');
    	}else{ 
    	
	    	if((DB::table('tblchildren_particulars')->where('fileNo', '=', $fileNo)->first()))
	    	{
	    		$data['details']    		= DB::table('tblchildren_particulars')->where('id', $id)->first();
				$data['childrenList']    	= DB::table('tblchildren_particulars')->where('fileNo', $fileNo)->get();
				$data['getStaff'] 			= $getStaff;
	    		return view('ParticularsOfChildren.create', $data);
	    	}else{
	    		$data['details']    		= "";
				$data['childrenList']    	= "";
				$data['getStaff'] 			= $getStaff;
	    		return view('ParticularsOfChildren.create', $data);
	    	}
    	}
    }//->


     public function delete($id = Null)
    {
    	$fileNo = Session::get('fileNo');
    	if(is_null($fileNo))
    	{
   			return redirect('/children/create/'.$fileNo);
    	}
    	//delete
		DB::table('tblchildren_particulars')->where('id', '=', $id)->where('fileNo', '=', $fileNo)->delete();
		$this->addLog('children particulars deleted: ' . $this->division);
		return redirect('/children/create/'.$fileNo)->with('msg', 'children particulars record deleted successfully');
    }


    public function store(Request $request)
    { 	
    	$fileNo = Session::get('fileNo');
    	if(is_null($fileNo)){
    		return redirect('profile/details');
    	}
		$this->validate($request, 
		[
			'fullName'    					=> 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
			'gender'        				=> 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
			'dateOfBirth'      				=> 'required|date',
			'checkedChildrenParticulars' 	=> 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
		]);
		$fullName         			= trim($request['fullName']);
		$gender             		= trim($request['gender']);
		$dateOfBirth       	   		= trim($request['dateOfBirth']);
		$checkedChildrenParticulars = trim($request['checkedChildrenParticulars']);
		$id          	   			= trim($request['id']);
		$date    		       		= date("Y-m-d");
		
		//Update if hidden Name/id NOT empty
		if($id <> ""){
			DB::table('tblchildren_particulars')->where('id', $id)->where('fileNo', $fileNo)->update(array( 
				'fullname'         				=> $fullName,
				'gender'             			=> $gender,
				'dateofbirth'       			=> $dateOfBirth,
				'checked_children_particulars'  => $checkedChildrenParticulars,
				'updated_at'            		=> $date,
			));
			$this->addLog('Children particular Record updated for fileNo: '.$fileNo .' Division: ' . $this->division);
			$message = 'Children particular Record updated successfully';
		}else{
			//insert if hidden Name/id is empty (but directly updating record)
			DB::table('tblchildren_particulars')->insert(array( 
				'fileNo'						=> $fileNo,
				'fullname'         				=> $fullName,
				'gender'             			=> $gender,
				'dateofbirth'       			=> $dateOfBirth,
				'checked_children_particulars'  => $checkedChildrenParticulars,
				'created_at'            		=> $date,
				'updated_at'            		=> $date
			));
			$this->addLog('Children particular Record created successfully fileNo: '.$fileNo .'Division: ' . $this->division);
			$message = 'Children particular Record created successfully';
		}
		//
		return redirect('/children/create/'.$fileNo)->with('msg', $message);	
	}

	//Children Report
    public function report($fileNo = null)
    {
    	if(is_null($fileNo)){
    		return redirect('profile/details');
    	}
    	if( !(DB::table('tblper')->where('fileNo', '=', $fileNo)->first())){
    		return redirect('profile/details');
    	}else{
	        $data['staffFullDetailsChildren'] = DB::table('tblchildren_particulars')
	                ->where('fileNo', '=', $fileNo)
	                ->orderBy('id', 'Desc')
	                ->get();
	        $data['staffFullDetails'] = DB::table('tblper')
	                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
	                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
	                ->where('tblper.fileNo', '=', $fileNo)
	                ->first();
        }
        return view('Report.ChildrenReport', $data);
    }

}