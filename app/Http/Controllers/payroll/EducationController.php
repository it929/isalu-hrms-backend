<?php
//
namespace App\Http\Controllers\payroll;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use Carbon\Carbon;
use Auth;
use DB;
use file;

class EducationController extends ParentController
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
	    	if((DB::table('tbleducations')->where('fileNo', '=', $fileNo)->first()))
	    	{
	    		$data['details']    		= "";
				$data['educationList']    	= DB::table('tbleducations')
											  ->where('tbleducations.fileNo', '=', $fileNo)
											  ->join('tblper', 'tblper.fileNo', '=', 'tbleducations.fileNo')
											  ->get();
				$data['qualificationList']    = DB::table('tblqualification')
											  ->where('active', '=', 1)
											  ->get();
				$data['getStaff'] 			= $getStaff;
	    		return view('Education.create', $data);
	    	}else{
	    		$data['details']    		= "";
				$data['educationList']    	= "";
				$data['qualificationList']    = DB::table('tblqualification')
											  ->where('active', '=', 1)
											  ->get();
				$data['getStaff'] 			= $getStaff;
	    		return view('Education.create', $data);
	    	}
    	}
    }//->


    public function view($id = Null)
    { //->
    	
    	$fileNo = Session::get('fileNo');
    	$getStaff = DB::table('tblper')->where('fileNo', '=', $fileNo)->first();
    	if(is_null($id)){
    		$data['getStaff'] 			= $getStaff;
    		return redirect('/education/create');
    	}else{ 
    	
	    	if((DB::table('tbleducations')->where('fileNo', '=', $fileNo)->first()))
	    	{
	    		$data['details']    		= DB::table('tbleducations')->where('id', $id)->first();
				$data['educationList']    	= DB::table('tbleducations')
											  ->where('tbleducations.fileNo', '=', $fileNo)
											  ->join('tblper', 'tblper.fileNo', '=', 'tbleducations.fileNo')
											  ->get();
				$data['qualificationList']    = DB::table('tblqualification')
											  ->where('active', '=', 1)
											  ->get();
				$data['getStaff'] 			= $getStaff;
	    		return view('Education.create', $data);
	    	}else{
	    		$data['details']    		= "";
				$data['childrenList']    	= "";
				$data['getStaff'] 			= $getStaff;
				$data['qualificationList']    = DB::table('tblqualification')
											  ->where('active', '=', 1)
											  ->get();
	    		return view('Education.create', $data);
	    	}
    	}
    }//->


     public function delete($id = Null)
    {
    	$fileNo = Session::get('fileNo');
    	if(is_null($fileNo))
    	{
   			return redirect('/education/create/'.$fileNo);
    	}
    	//delete
		DB::table('tbleducations')->where('id', '=', $id)->where('fileNo', '=', $fileNo)->delete();
		$this->addLog('Education details deleted: ' . $this->division);
		return redirect('/education/create/'.$fileNo)->with('msg', 'Education record was deleted successfully');
    }


    public function store(Request $request)
    { 	
    	$fileNo = Session::get('fileNo');
    	if(is_null($fileNo)){
    		return redirect('profile/details');
    	}
		$this->validate($request, 
		[
			'degreeQualification'   => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
			'schoolAttended'        => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
			'schoolFrom'      		=> 'required|date',
			'schoolTo' 				=> 'required|date',
			'certificateHeld'    	=> 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
			'checkedEducation'      => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
			'document'      		=> 'image|mimes:png,jpg,jpeg,gif,pdf|max: 4000',
		]);
		$degreeQualification        = trim($request['degreeQualification']);
		$schoolAttended             = trim($request['schoolAttended']);
		$schoolFrom       	   		= trim($request['schoolFrom']);
		$schoolTo 					= trim($request['schoolTo']);
		$certificateHeld         	= trim($request['certificateHeld']);
		$checkedEducation           = trim($request['checkedEducation']);
		$file       	   			= $request['document'];
		$id          	   			= trim($request['id']);
		$date    		       		= date("Y-m-d");
		//Update if hidden Name/id NOT empty
		if($id <> ""){
			DB::table('tbleducations')->where('id', $id)->where('fileNo', $fileNo)->update(array( 
				'degreequalification'   => $degreeQualification,
				'schoolattended'        => $schoolAttended,
				'schoolfrom'       		=> $schoolFrom,
				'schoolto'  			=> $schoolTo,
				'certificateheld'       => $certificateHeld,
				'checkededucation'      => $checkedEducation,
				'updated_at'            => $date
			));
			$recordSaved = $id;
			$logMessage = 'Education details updated';
			$message = 'Education details updated successfully';
		}else{
			//insert if hidden Name/id is empty (but directly updating record)
			$recordSaved = DB::table('tbleducations')->insertGetId(array( 
				'fileNo'				=> $fileNo,
				'degreequalification'   => $degreeQualification,
				'schoolattended'        => $schoolAttended,
				'schoolfrom'       		=> $schoolFrom,
				'schoolto'  			=> $schoolTo,
				'certificateheld'       => $certificateHeld,
				'checkededucation'      => $checkedEducation,         
				'created_at'            => $date,
				'updated_at'            => $date
			));
			$logMessage = 'Education details created';
			$message = 'Education record created successfully';
		}
		//
		//upload document
		if((($file && $recordSaved) || ($recordSaved != "")) && ($file != Null || $file != ""))
        {
			$originalExtension   = $file->getClientOriginalExtension();
			$imageNewName        = $fileNo .'-'. rand() . '.'.$originalExtension;
	        $path                = base_path() . '/public/document/';
	        //delete old file if user tends to update his/her records
	        if($id <> "")
	        {
		        $oldName = DB::table('tbleducations')->where('fileNo', $fileNo)->where('id', $recordSaved)->select('document')->first(); 
		        $oldFileName = $oldName->document;
		        /*if((File::exists($path . $oldFileName))) //check folder
				{	
					File::delete($path . $oldFileName);
				}*/
			}
	        if($file->move($path , $imageNewName))
			{
				DB::table('tbleducations')->where('fileNo', $fileNo)->where('id', $recordSaved)->update(array( 
					'document' 			  => $imageNewName
				));
				$this->addLog($message.' and document was uploaded');
				return redirect('/education/create/'.$fileNo)->with('msg', $message.' and document was uploaded');
			}
			else
			{
				return redirect('/education/create/'.$fileNo)->with('err', $message.' but document was NOT uploaded');
			}
		}

		$this->addLog($logMessage.' with '.$fileNo);
		return redirect('/education/create/'.$fileNo)->with('msg', $message);	
	}


	//Education Report
    public function report($fileNo = null)
    {
    	if(is_null($fileNo)){
    		return redirect('profile/details');
    	}
    	if( !(DB::table('tblper')->where('fileNo', '=', $fileNo)->first())){
    		return redirect('profile/details');
    	}else{
	        $data['staffFullDetailsEducation'] = DB::table('tbleducations')
	    			->where('fileNo', '=', $fileNo)
	                ->orderBy('id', 'Desc')
	                ->get();
	        $data['staffFullDetails'] = DB::table('tblper')
	                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
	                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
	                ->where('tblper.fileNo', '=', $fileNo)
	                ->first();
        }
        return view('Report.EducationReport', $data);
    }

}