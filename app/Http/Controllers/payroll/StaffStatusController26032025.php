<?php
//
namespace App\Http\Controllers\payroll;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;

class StaffStatusController extends ParentController
{
	public $division;
	public $divisionID;

	public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
		
    	$this->division    = session('division'); //$request->session()->get('division');
		$this->divisionID  = session('divisionID'); //$request->session()->get('divisionID');
	            return $next($request);
        });
    }		

    public function loadView(Request $request)
	{
		// Fetch the staff list
		$data['staffList'] = DB::table('tblper')->get();

		// Fetch division data based on user's global status
		// $userID = Auth::id();

		// if (Auth::user()->is_global == 1) {
		// 	// If user is global, fetch all divisions
		// 	$data['division'] = DB::table('tbldivision')
		// 		->select('divisionID', 'division')
		// 		->orderBy('division', 'Asc')
		// 		->get();
		// } else {
		// 	// If user is not global, fetch only their division
		// 	$data['division'] = DB::table('users')
		// 		->join('tbldivision', 'users.divisionID', '=', 'tbldivision.divisionID')
		// 		->where('users.id', $userID)
		// 		->select('tbldivision.divisionID', 'tbldivision.division')
		// 		->get();
		// }

		$data['division'] = DB::table('tbldivision')
				->select('divisionID', 'division')
				->orderBy('division', 'Asc')
				->get();

		return view('staffStatus.staffStatus', $data);
	}
   
   public function findStaff(Request $request)
    {    	
    	$this->validate($request, [
    		  'staffName' => 'required|numeric',
    	]);
		$fileNo = $request->input('staffName');

		$data   = DB::table('tblper')
			    ->where('ID', '=', $fileNo)
				->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
		        //->where('divisionID', '=', $this->divisionID)
		        // ->select('fileNo', 'surname', 'first_name', 'othernames')
		        ->first();
		return response()->json($data);
    }

   public function update(Request $request)
    { 	
		$this->validate($request, 
		[
			'staffName'      => 'required|numeric',
			'action'         => 'required|regex:/^[\pL\s\-]+$/u',
		]);
		$fileNo              = trim($request['staffName']);
		$action              = $request['action'];
		$statusPending       = 'pending';
		$statusRejected      = 'rejected';
		$statusApproved      = 'approved';
		$date    		     = date("Y-m-d");

        $fromDiv = DB::table('tblper')->where('ID','=', $fileNo)->first();
        
		if($action == 'Update Staff Record')
		{
			$this->validate($request, 
			[
				'staffStatus'    => 'required|regex:/^[\pL\s\-]+$/u',
			]);
			$staffStatus         = trim($request['staffStatus']);

			if ( ($staffStatus == "active service") || ($staffStatus == "contract service") ||  ($staffStatus == "maternity leave") )
				$value = 1;
			else
				$value = 0;
			DB::table('tblper')->where('ID','=', $fileNo)->update([
				'status_value'    => $staffStatus,
				'staff_status'    => $value			
			]);	
			//$this->addLog('staff status updated with fileno: '.$fileNo);
			return back()->with('msg', 'Staff status updated successfully!');
		}
		else if($action == 'Transfer Staff')
		{
			$this->validate($request, 
			[
				'staffDivision'  => 'required|numeric',
			]);
			$staffDivisionTo     = trim($request['staffDivision']);
			
		
			
			
				DB::table('tbltransfer')->insert(array( 
					'fileNo'            => $fileNo, 
					'date'              => $date, 
					'divisionFrom'      => $fromDiv->divisionID,
					'divisionTo'        => $staffDivisionTo	,
					'status'            => $statusPending
				));
			// 	if(DB::table('tblper')->where('ID', $fileNo)->update(array( 
			// 		'divisionID'        => $staffDivisionTo	)))
			// 	{	
				$this->addLog('staff transferred with fileno: '.$fileNo);

				return back()->with('msg', 'Staff was transferred successfully to another division!');
			// }
			// else{
			// 	return back()->with('msg', 'This Staff was already transferred to another division!');
			// }		
		}		       
		
	}

	public function loadPending()
    { 
		// dd($this->divisionID);
		$statusPending    = 'pending';
		$statusRejected   = 'rejected';
		$statusApproved   = 'approved';
		$date    		  = date("Y-m-d");
		$data['staffPending'] = DB::table('tbltransfer')
			  ->where('tbltransfer.status', '=', $statusPending)
			  ->where('tbltransfer.divisionTo', '=', $this->divisionID)
			  ->select('tblper.fileNo',  'tblper.surname', 'tblper.first_name', 'tblper.othernames', 'tbldivision.division', 'tblper.rank', 'tbltransfer.date')
			  ->join('tblper', 'tblper.fileNo', '=', 'tbltransfer.fileNo')
			  ->join('tbldivision', 'tbldivision.divisionID', '=', 'tbltransfer.divisionFrom')
			  ->orderby('tbltransfer.date', 'ASC')
			  ->get();
		//total pending
		$totalstaff      = 0;
		foreach ($data['staffPending'] as $row) 
		{
			$totalstaff   += 1;
		}
		$data['totalStaff']   = $totalstaff;
		$data['curDivision']  = $this->division;
   		return view('staffStatus.report', $data);
   }

   public function getApprove(Request $request)
    {
		$approveButton       = $request['approve'];
		$rejectButton        = $request['reject'];
		$curDivisionID       = $this->divisionID;
		$array               = ($request['action']);
		$statusPending       = 'pending';
		$statusRejected      = 'rejected';
		$statusApproved      = 'approved';
		$date    		     = date("Y-m-d");

		if($approveButton == 'Approve Staff' and $rejectButton == '')
		{
			//$selected      = 0;
			//$selected      = count(($request['action']));
			if($array)
			{
				foreach($array as $fileNo)
				{
					DB::beginTransaction();
					DB::table('tblper')->where('fileNo', $fileNo)->update(array(  
						'divisionID'        => $curDivisionID, 
						'staff_status'      => 1,
						'status_value'      => 'active service' ///
					));
					DB::table('tbltransfer')
						->where('fileNo', $fileNo)
						 ->where('status', '=', $statusPending)
						->update(array( 
						'status'            => $statusApproved	
					));	
					$this->addLog('Approve staff transfer with fileno: '.$fileNo .' to '. $this->divisionID .' division');
					DB::commit();			
				}
				return back()->with('msg', 'Staff was/were successfully approved to this division!');	
			}
			else
			{
				return back()->with('msg', 'You have not selected any staff to approve');
			}
		}
		else if($rejectButton == 'Reject Staff' and $approveButton == '')
		{
			if($array)
			{
				foreach($array as $fileNo)
				{
					DB::beginTransaction();
					//get previous division
					$PrevData = DB::table('tbltransfer')
						  ->select('divisionFrom', 'divisionTo')
						  ->where('fileNo', '=', $fileNo)
						  ->where('status', '=', $statusPending)
						  ->first();

					$countID = 0;
					foreach($PrevData as $row)
					{
						$countID += 1;
						if($countID == 1)
						{
							$newDivisionFrom = $row; //1st 
						}
						else
						{
							 $newDivisionTo  = $row; //2nd
						}
					}
					if($countID  >  1)  
					{
						DB::table('tbltransfer')
							->where('fileNo', $fileNo)
							->where('status',  '=', $statusPending)
							->update(array( 
								'status'        => $statusRejected,
								'date'          =>  $date
						));	

						DB::table('tbltransfer')->insert(array( 
							'fileNo'            => $fileNo, 
							'date'              => $date, 
							'divisionTo'        => $newDivisionFrom, //divisionTo
							'divisionFrom'      => $newDivisionTo,   //divisionFrom
							'status'            => $statusPending
						));
					}
					$this->addLog('staff rejected with fileno = '.$fileNo .' to previous division');
					DB::commit();			
				}
				return back()->with('msg', 'Staff was/were successfully rejected to previous division!');	
			}
			else
			{
				return back()->with('msg', 'You have not selected any staff to reject');
			}
		}
   }

}