<?php
//
namespace App\Http\Controllers\funds;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;

class InactiveStaffController extends ParentController
{
	public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
    	$this->divisionID = $request->session()->get('divisionID');
                return $next($request);
        });
    }
    public function loadView()
   {
   	    return view('inactiveStaff.inactiveStaff');
   }
   
   public function loadReport(Request $request)
    { 
		$this->validate($request, 
		[
			'staffStatus'    => 'required|string',
		]);
		$staffStatus         = trim($request['staffStatus']);
		$data['staffStatus'] = DB::select('select p.fileNo, p.title, p.surname, p.first_name, p.othernames, p.rank, p.grade, p.step, p.bankID, b.bank, p.bankGroup from tblper p join tblbanklist b on b.bankID = p.bankID and p.status_value ="'. $staffStatus .'" 
		 					    and p.divisionID ="'.$this->divisionID . '" and p.employee_type <> \'CONSOLIDATED\' order by p.bankGroup, p.fileNo ASC');

		if(!($data['staffStatus']))
		{
			return back()->with('message', 'Record not found!');
		}
		else
		{
			//total
			$totalStatus      = 0.0;
			foreach ($data['staffStatus'] as $row) 
			{
				$totalStatus   += 1;
			}//end foreach
			$data['totalStatus'] = $totalStatus; 
			$data['reportHeader'] = $staffStatus;
			return view('inactiveStaff.report', $data);
		}
		return back()->with('message', 'Empty field selected!');
		
	}

}