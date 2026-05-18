<?php

namespace App\Http\Controllers\payroll;

use App\Http\Requests;
use App\Role;
use App\User;
use App\OtherPayment; 
use App\OtherPaymentReport;
use Auth;
use Entrust;
use App\Permission;
use Illuminate\Http\Request;
use DB;

class OtherPaymentController extends ParentController
{

	public $division; 
	public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {   
	    $this->middleware('auth');
		$this->division = $request->session()->get('division');
	            return $next($request);
        });
    }
    
    
    //Index
    public function index()
    {
        $data['getStaff'] = DB::table('tblper')->get(['surname', 'first_name', 'othernames', 'grade', 'step', 'fileNo', 'UserID', 'ID']);
        $data['addedStaff'] = OtherPayment::leftjoin('tblper', 'tblper.ID', '=', 'other_payment_staff.userID')
                            ->select(['other_payment_staff.userID', 'other_payment_staff.amount', 'tax_amount', 'tax_rate', 'other_payment_staff.purpose', 'payment_date', 'other_payment_staff.created_at', 'compute_status', 'salary_type', 'surname', 'first_name', 'othernames'])
                            ->paginate(50);
        return view('otherPayment.create', $data);
    }



	public function store(Request $request)
	{
        $is_save = null;
		$this->validate($request, [ 
			'staffName'     => 'required|integer',
			'amount'        => 'required|numeric|between:0,9999999999999.99',
			'taxRate'       => 'required|numeric|between:0,9999999999999.99',
			'purpose'       => 'required|string',
			'salaryType'    => 'required',
			'paymentDate'   => 'required|date',
		]);
        try{
            $is_save = OtherPayment::updateOrCreate(
                [
                   'userID'         => $request['staffName'],
                   'compute_status' => 0
                ],
                [
                    'amount'        => $request['amount'],
                    'tax_amount'    => (($request['taxRate']/100) * $request['amount']),
                    'tax_rate'      => $request['taxRate'],
                    'purpose'       => $request['purpose'],
                    'payment_date'  => $request['paymentDate'],
                    'salary_type'   => $request['salaryType']
                ]
            );    
        }catch(\Throwable $e){}
        if($is_save)
        {
            return redirect()->back()->with('message', 'Your record was saved successfully.');
        }
        return redirect()->back()->with('error', 'Sorry, we are unable to save your record');
	}
	
	
	public function computePayment(Request $request)
	{
	     $is_save = null;
	    $this->validate($request, [ 
			'month'     => 'required|string',
			'year'        => 'required|numeric|between:0,9999999999999.99',
		]);
		try{
		    $allStaff = OtherPayment::get();
		    $activeMonth = DB::table('tblactivemonth')->value('month');
		    $activeYear = DB::table('tblactivemonth')->value('year');
		    if($allStaff)
		    {
		        foreach($allStaff as $key=>$staff)
		        {
		               $is_save = OtherPaymentReport::updateOrCreate(
                        [
                           'userID'         => $staff->userID,
                           'active_month'   => DB::table('tblactivemonth')->value('month'),
                           'active_year'    => DB::table('tblactivemonth')->value('year'),
                        ],
                        [
                            'amount'        => $staff->amount,
                            'tax_amount'    => $staff->tax_amount,
                            'tax_rate'      => $staff->tax_rate,
                            'purpose'       => $staff->purpose,
                            'salary_type'   => $staff->salary_type,
                            'month'          => $request['month'],
                           'year'           => $request['year'],
                           'staff_name'     => $staff->surname . ' '. $staff->first_name .' '. $staff->othernames
                        ]
                    );   
                    //Update compute_status
                    OtherPayment::where('userID', $staff->userID)->update(['compute_status' => 1]);
		        }
		        return redirect()->back()->with('message', 'Your computation was successfully.');
		    }else{
		        return redirect()->back()->with('error', 'Sorry, we are unable to complete your computation.');
		    }
        }catch(\Throwable $e){}
        return redirect()->back()->with('error', 'Sorry, we are unable to complete your computation.');
	}
	
	
	//load report
	public function createReport()
	{
	     return view('otherPayment.report');
	}
	
	
	//View report
	public function ViewReport(Request $request)
	{  
	     $data['addedStaff'] = OtherPaymentReport::where('active_month', strtoupper($request['month']))->where('active_year', $request['year'])->get();
	    
	     return view('otherPayment.report', $data);
	}
	
	
	
	
}//end class