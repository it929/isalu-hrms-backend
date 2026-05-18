<?php

namespace App\Http\Controllers\payroll;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use File;
//use App\Upload;
use DB;

class pictureViewerController extends ParentController
{
	public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
    	$this->division = $request->session()->get('division');
		$this->divisionID = $request->session()->get('divisionID');
                return $next($request);
        });
    }

   public function loadView()
   {
	    $data['staffList'] = $this->getStaffList();
        $data['bankList']  = DB::table('tblper')
			 ->where('tblper.divisionID', '=', $this->divisionID)
			 ->select('tblbanklist.bank', 'tblper.bankID')
			 ->distinct()
			 ->join('tblbanklist', 'tblper.bankID', '=', 'tblbanklist.bankID')
			 ->orderBy('bank', 'Asc')
			 ->get();
		return view('pictureViewer.pictureviewer', $data);
   }

   public function findStaff(Request $request)
   {   
    	$this->validate($request, [
    		  'staffName' => 'required|numeric',
    	]);
		$fileNo = $request->input('staffName');
		$data   = DB::table('tblper')
			    ->where('fileNo',     '=', $fileNo)
		        ->where('divisionID', '=', Session::get('divisionID'))
		        ->select('fileNo', 'surname', 'first_name', 'othernames')
		        ->first();
		return response()->json($data);
    }

	public function store(Request $request)
    { 	
		$this->validate($request, 
		[
			'staffName'      => 'required|numeric',
			'photo'          => 'required|image|mimes:png,jpg,jpeg,gif|max: 2000',
		]);
		$fileNo              = trim($request['staffName']);
	    $file                = $request->file('photo');
		
		//$imageTempName     = $file->getPathname();
        //$imageName         = $file->getClientOriginalName();
		//$originalExtension = $file->getClientOriginalExtension();
		$imageNewName        = $fileNo . '.jpg';
        $path                = base_path() . '/public/passport/';
		if(File::exists(base_path() . '/public/passport/' . $imageNewName )) //check folder
		{
			File::delete(base_path() . '/public/passport/' . $imageNewName );
		}
        if($file->move($path , $imageNewName))
		{
			$this->addLog('staff picture uploaded with fileno: ' . $fileNo);
			return back()->with('msg', 'Staff picture uploaded successfully!');
		}
		else
		{
			return back()->with('err', 'Picture uploading was not successful!');
		}
	}

	public function loadReport(Request $request)
	{
		$newImageExtension   = '.jpg';
		$this->validate($request, 
		[
			'bank'          => 'required|numeric',
			'bankGroup'     => 'required|numeric',
		]);
		$bankID             = ($request['bank']);
		$bankGroup          = trim($request['bankGroup']);
		$displayButton      = ($request['display']);
		$missingButton      = ($request['missing']);

		if($displayButton == 'Display Picture' and $missingButton == '')
		{
			//getAll staff
			$data['getFileNo'] = DB::table('tblper')
					  ->select('fileNo')
					  ->where('bankID',       '=',  $bankID)
					  ->where('bankGroup',    '=',  $bankGroup)
					  ->where('divisionID',   '=',  $this->divisionID)
					  ->where('status_value', '<>', 'CONSOLIDATED')
					  ->where('tblper.staff_status', '=', 1)
					  ->get();
			$countTotal   = 0;
			$arrayFileNo  = array();

			foreach($data['getFileNo'] as $row)
			{
				//All pics were converted to 'jpg'' during upload
				if(File::exists(base_path() . '/public/passport/' . $row->fileNo . $newImageExtension)) //check folder
				{
					$arrayFileNo[]  = $row->fileNo; //(n[] + fileNo) with FIFO
					$countTotal    += 1;
				}
			}
			$count        = 0;
			$Newcount     = 0;
			while($count <= $countTotal)
			{
				if($count == $countTotal)
					$Newcount  = $count -1; //array: 0...n-1
				else
					$Newcount  = $count; // else array: 0...n
				$staffWithPic  = DB::table('tblper')
							   ->select('fileNo', 'surname', 'first_name', 'othernames', 'grade', 'step')
							   ->where('bankID',     $bankID)
							   ->where('bankGroup',  $bankGroup)
							   ->whereIn('fileNo',     $arrayFileNo)
							   ->where('divisionID',   '=',  $this->divisionID)
					           ->where('status_value', '<>', 'CONSOLIDATED')
					           ->where('tblper.staff_status', '=', 1)
							   ->orderBy('surname', 'Asc')
							   ->get();
				$count         ++; // += 1;
			}
			$data['total']          = $countTotal;
			$data['bankName']       = DB::table('tblbanklist')
                                    ->select('bank')
                                    ->where('bankID', '=', $bankID)
                                    ->first();
			$data['curDivision']    = $this->division;
			$data['displayPicture'] = $staffWithPic;
		    return view('pictureViewer/display', $data);
		}
		else if($missingButton == 'Missing Picture' and $displayButton == '')
		{
			//getAll staff
			$data['getFileNo'] = DB::table('tblper')
					  ->select('fileNo')
					  ->where('bankID',       '=',  $bankID)
					  ->where('bankGroup',    '=',  $bankGroup)
					  ->where('divisionID',   '=',  $this->divisionID)
					  ->where('status_value', '<>', 'CONSOLIDATED')
					  ->where('tblper.staff_status', '=', 1)
					  ->get();
			$countTotal   = 0;
			$arrayFileNo  = array();
			foreach($data['getFileNo'] as $row)
			{
				//All pics were converted to 'jpg'' during upload
				if(!File::exists(base_path() . '/public/passport/' . $row->fileNo . $newImageExtension)) //check folder
				{
					$arrayFileNo[] = $row->fileNo; //(n[] + fileNo) with FIFO
					$countTotal       += 1;
				}
			}
			$count        = 0;
			$Newcount     = 0;
			while($count <= $countTotal)
			{
				if($count == $countTotal)
					$Newcount  = $count -1; //array: 0...n-1
				else
					$Newcount  = $count; // else, array: 0...n
				$staffWithPic  = DB::table('tblper')
							   ->select('fileNo', 'surname', 'first_name', 'othernames', 'grade', 'step')
							   ->where('bankID',     $bankID)
							   ->where('bankGroup',  $bankGroup)
							   ->whereIn('fileNo',     $arrayFileNo)
							   ->where('divisionID',   '=',  $this->divisionID)
					           ->where('status_value', '<>', 'CONSOLIDATED')
					           ->where('tblper.staff_status', '=', 1)
							   ->orderBy('surname', 'Asc')
							   ->get();
				$count         ++; // += 1;
			}
			$data['total']          = $countTotal;
			$data['bankName']       = DB::table('tblbanklist')
                                    ->select('bank')
                                    ->where('bankID', '=', $bankID)
                                    ->first();
			$data['curDivision']    = $this->division;
			$data['displayPicture'] = $staffWithPic;
		    return view('pictureViewer/missing', $data);
		}
		else
		{
			return back()->with('err', 'No staff found in this category');
		}
		
	}

}