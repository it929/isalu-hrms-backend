<?php

namespace App\Http\Controllers\funds;



use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ReportsController extends functionController
{
	public function __construct()
	{
        $this->middleware(function ($request, $next) {
            $this->middleware('auth');
		// $this->username = Session::get('userName');
            return $next($request);
        });
    } //

	public function TotalMonthlyAllocation(Request $request)
	{
		$data['error'] = "";
		$data['warning'] = "";
		$data['success'] = "";
		$data['economicGroup'] = trim($request['economicGroup']);
		// if ($this->AccessNotGranted("allocation/totalmonthly")) {
		// 	return redirect('/')->with('message', 'Sorry! You do not have permission to access this page!!');
		// }
		$data['period']	= $request->input('period'); //$request['period'];
		//dd(get(old('period')));
		if ($data['period'] == '') {
			$data['period'] = session('period');
		}
		if ($data['economicGroup'] == '') {
			$data['economicGroup'] = session('economicGroup');
		}
		$data['month']	= $request['month'];
		$data['amount']	= $request['amount'];
		$data['remarks']	= $request['remarks'];
		if (isset($_POST['update'])) {
			$this->validate($request, [
				'period'      	    => 'required',
				'month'      	    => 'required',
				'amount'      	    => 'required|numeric|between:0,9999999999999999.99',
				'economicGroup'    => 'required',

			]);

			if (!DB::table('tbltotalMonthlyAllocation')->where('year', '=', $data['period'])->where('month', '=', $data['month'])->where('budgetType', '=', $data['economicGroup'])->first()) {
				DB::table('tbltotalMonthlyAllocation')
					->insert([
						'year'          => $data['period'],
						'month'         => $data['month'],
						'amount'        => $data['amount'],
						'budgetType'    => $data['economicGroup'],
						'remarks'    => $data['remarks']
					]);
			} else {
				DB::table('tbltotalMonthlyAllocation')
					->where('year', '=', $data['period'])
					->where('month', '=', $data['month'])->where('budgetType', '=', $data['economicGroup'])->update([
						'amount' => ($data['amount'] == "") ? 0 : $data['amount'],
						'remarks'    => $data['remarks']
					]);
			}
			$data['success'] = "successfully updated";
			Session::put('period',  $data['period']);
			Session::put('economicGroup',  $data['economicGroup']);
			//session('period')=$data['period'];
			return redirect()->back()->withInput();
			//return Redirect::back()->withInput(Input::all());
		}

		$data['QReport'] = $this->TotalMonthAllocation($data['period'], $data['economicGroup']);
		//dd($data['QReport']);
		$data['YearPeriod'] = $this->YearPeriod();
		$data['EconomicGroup'] = $this->GetEconomicGroup();
		return view('funds.allocation.monthlytotalallocation', $data);
	}
	public function VoultBalanceReport(Request $request)
	{

		//die($this->VoultBalance('20')) ;
		$data['error'] = "";
		$data['warning'] = "";
		$data['success'] = "";


		$period = trim($request['period']);
		$allocationsource = trim($request['allocationsource']);
		$budgettype = trim($request['budgettype']);
		$economichead = trim($request['economichead']);
		$economiccode = trim($request['economiccode']);

		$data['allocationsource'] = $allocationsource;
		$data['period'] = $period;
		$data['budgettype'] = $budgettype;
		$data['economichead'] = $economichead;
		$data['economiccode'] = $economiccode;


		$data['EconomicHead'] = $this->EconomicHead($budgettype);
		$data['AllocationSource'] = $this->AllocationSource();
		$data['BudgetType'] = $this->BudgetType();
		$data['YearPeriod'] = $this->YearPeriod();
		$data['EconomicCode'] = $this->EconomicCode($allocationsource, $economichead);
		$data['QueryVoultReport'] = $this->QueryVoultReport($period, $allocationsource, $budgettype, $economichead, $economiccode);

		return view('funds.Report.VoultBalance', $data);
	}

	public function VoultTransReport(Request $request)
	{

		$data['error'] = "";
		$data['warning'] = "";
		$data['success'] = "";


		$period = trim($request['period']);
		$allocationsource = trim($request['allocationsource']);
		$budgettype = trim($request['budgettype']);
		$economichead = trim($request['economichead']);
		$economiccode = trim($request['economiccode']);

		$data['allocationsource'] = $allocationsource;
		$data['period'] = $period;
		$data['budgettype'] = $budgettype;
		$data['economichead'] = $economichead;
		$data['economiccode'] = $economiccode;


		$data['EconomicHead'] = $this->EconomicHead($budgettype);
		$data['AllocationSource'] = $this->AllocationSource();
		$data['BudgetType'] = $this->BudgetType();
		$data['YearPeriod'] = $this->YearPeriod();
		$data['EconomicCode'] = $this->EconomicCode($allocationsource, $economichead);
		$data['QueryVoultReport'] = $this->QueryVoultReport($period, $allocationsource, $budgettype, $economichead, $economiccode);

		return view('funds.Report.votetrans2', $data);
	}

	public function VoultTransReport2(Request $request)
	{


		$data['error'] = "";
		$data['warning'] = "";
		$data['success'] = "";


		$period = trim($request['period']);
		$allocationsource = "5"; //trim($request['allocationsource']);
		$budgettype = trim($request['budgettype']);
		$economichead = trim($request['economichead']);
		$economiccode = trim($request['economiccode']);
		$status = trim($request['status']);

		$data['allocationsource'] = $allocationsource;
		$data['period'] = $period;
		$data['budgettype'] = $budgettype;
		$data['economichead'] = $economichead;
		$data['economiccode'] = $economiccode;
		$data['status'] = $status;

		$data['Statuss'] =  DB::Select("SELECT * FROM `tblstatus` WHERE `fundstatus`=1 ");
		$data['EconomicHead'] = $this->EconomicHead($budgettype);
		$data['AllocationSource'] = $this->AllocationSource();
		$data['BudgetType'] = $this->BudgetType();
		$data['YearPeriod'] = $this->YearPeriod();
		$data['EconomicCode'] = $this->EconomicCode($allocationsource, $economichead);
		switch ($data['status']) {
			case '0':
				$data['VoteTrans'] = DB::Select("SELECT * FROM `tblpaymentTransaction` WHERE `economicCodeID`='$economiccode' and `period`='$period' and status<=1 order by datePrepared");
				break;
			case '2':
				$data['VoteTrans'] = DB::Select("SELECT * FROM `tblpaymentTransaction` WHERE `economicCodeID`='$economiccode' and `period`='$period' and status>1 order by datePrepared");
				break;

			default:
				$data['VoteTrans'] = DB::Select("SELECT * FROM `tblpaymentTransaction` WHERE `economicCodeID`='$economiccode' and `period`='$period' order by datePrepared");
		}
		//dd( $data['VoteTrans']);
		return view('funds.Report.votetrans2', $data);
	}
	public function MonthlyVoultBalanceReport(Request $request)
	{

		$data['error'] = "";
		$data['warning'] = "";
		$data['success'] = "";


		$month = trim($request['month']);
		$period = trim($request['period']);
		$allocationsource = trim($request['allocationsource']);
		$budgettype = trim($request['budgettype']);
		$economichead = trim($request['economichead']);
		$economiccode = trim($request['economiccode']);

		$data['allocationsource'] = $allocationsource;
		$data['month'] = $month;
		$data['period'] = $period;
		$data['budgettype'] = $budgettype;
		$data['economichead'] = $economichead;
		$data['economiccode'] = $economiccode;


		$data['EconomicHead'] = $this->EconomicHead($budgettype);
		$data['AllocationSource'] = $this->AllocationSource();
		$data['BudgetType'] = $this->BudgetType();
		$data['YearPeriod'] = $this->YearPeriod();
		$data['EconomicCode'] = $this->EconomicCode($allocationsource, $economichead);
		$yearmoth = $period . "-" . date("m", strtotime($month));
		//die($yearmoth);
		$data['QueryVoultReport'] = $this->QueryVoultReportmonth($yearmoth, $allocationsource, $budgettype, $economichead, $economiccode, $month, $period);

		return view('funds.Report.VoultBalancemonthly', $data);
	}
	public function RangeExpenditureReport(Request $request)
	{

		$data['from']	= $request->input('from');
		//if($data['from']=='') {$data['from']=session('from');}
		//if($data['from']=='') {$data['from']=Carbon::now()->subMonth()->format('Y-m-d');}
		if ($data['from'] == '') {
			$data['from'] = Carbon::now()->format('Y-m-d');
		}
		//Session::put('from',  $data['from']);
		$data['to']	= $request->input('to');
		//if($data['to']=='') {$data['to']=session('to');}
		if ($data['to'] == '') {
			$data['to'] = Carbon::now()->format('Y-m-d');
		}
		//Session::put('to',  $data['to']);
		//die(date('F', strtotime($data['to'])));

		$month = trim($request['month']);
		$period = trim($request['period']);
		if ($period == '') {
			$period = '2020';
		}
		$allocationsource = trim($request['allocationsource']);
		$budgettype = trim($request['budgettype']);
		$economichead = trim($request['economichead']);
		$economiccode = trim($request['economiccode']);

		$data['allocationsource'] = $allocationsource;
		$data['month'] = date('F', strtotime($data['to']));
		$data['period'] = $period;
		$data['budgettype'] = $budgettype;
		$data['economichead'] = $economichead;
		$data['economiccode'] = $economiccode;


		$data['EconomicHead'] = $this->EconomicHead($budgettype);
		$data['AllocationSource'] = $this->AllocationSource();
		$data['BudgetType'] = $this->BudgetType();
		$data['YearPeriod'] = $this->YearPeriod();
		$data['EconomicCode'] = $this->EconomicCode($allocationsource, $economichead);
		$yearmoth = $period . "-" . date("m", strtotime($month));
		$data['QueryVoultReport'] = $this->VoteBalRangeReport($yearmoth, $allocationsource, $budgettype, $economichead, $economiccode, date('F', strtotime($data['to'])), $period, $data['from'], $data['to']);
		$data['TotalAllocationTodate'] = $this->TotalAllocationTodate($budgettype, date('F', strtotime($data['to'])), $period);
		$data['allotext'] = DB::table('tblcontractType')->where('ID', $budgettype)->value('contractType');
		return view('funds.Report.general_expenditure', $data);
	}
	public function RangeExpenditureReportBalance(Request $request)
	{

		$data['from']	= $request->input('from');
		//if($data['from']=='') {$data['from']=session('from');}
		//if($data['from']=='') {$data['from']=Carbon::now()->subMonth()->format('Y-m-d');}
		if ($data['from'] == '') {
			$data['from'] = Carbon::now()->format('Y-m-d');
		}
		//Session::put('from',  $data['from']);
		$data['to']	= $request->input('to');
		//if($data['to']=='') {$data['to']=session('to');}
		if ($data['to'] == '') {
			$data['to'] = Carbon::now()->format('Y-m-d');
		}
		//Session::put('to',  $data['to']);
		//die(date('F', strtotime($data['to'])));

		$month = trim($request['month']);
		$period = trim($request['period']);
		if ($period == '') {
			$period = '2020';
		}
		$allocationsource = trim($request['allocationsource']);
		$budgettype = trim($request['budgettype']);
		$economichead = trim($request['economichead']);
		$economiccode = trim($request['economiccode']);

		$data['allocationsource'] = $allocationsource;
		$data['month'] = date('F', strtotime($data['to']));
		$data['period'] = $period;
		$data['budgettype'] = $budgettype;
		$data['economichead'] = $economichead;
		$data['economiccode'] = $economiccode;


		$data['EconomicHead'] = $this->EconomicHead($budgettype);
		$data['AllocationSource'] = $this->AllocationSource();
		$data['BudgetType'] = $this->BudgetType();
		$data['YearPeriod'] = $this->YearPeriod();
		$data['EconomicCode'] = $this->EconomicCode($allocationsource, $economichead);
		$yearmoth = $period . "-" . date("m", strtotime($month));
		$data['QueryVoultReport'] = $this->VoteBalRangeReport($yearmoth, $allocationsource, $budgettype, $economichead, $economiccode, date('F', strtotime($data['to'])), $period, $data['from'], $data['to']);
		$data['TotalAllocationTodate'] = $this->TotalAllocationTodate($budgettype, date('F', strtotime($data['to'])), $period);
		$data['allotext'] = DB::table('tblcontractType')->where('ID', $budgettype)->value('contractType');
		return view('funds.Report.general_expenditure_balance', $data);
	}
	public function VoultExpendictureReport333(Request $request)
	{

		$data['error'] = "";
		$data['warning'] = "";
		$data['success'] = "";


		$month = $request['month'];
		$period = $request['period'];
		$data['fromdate'] = $request['fromdate'];
		$data['todate'] = $request['todate'];
		$allocationsource = $request['allocationsource'];
		$budgettype = $request['budgettype'];
		$economichead = $request['economichead'];
		$economiccode = $request['economiccode'];

		$data['allocationsource'] = $allocationsource;
		$data['month'] = $month;
		$data['period'] = $period;
		$data['budgettype'] = $budgettype;
		$data['economichead'] = $economichead;
		$data['economiccode'] = $economiccode;


		$data['EconomicHead'] = $this->EconomicHead($budgettype);
		$data['AllocationSource'] = $this->AllocationSource();
		$data['BudgetType'] = $this->BudgetType();
		$data['YearPeriod'] = $this->YearPeriod();
		$data['EconomicCode'] = $this->EconomicCode($allocationsource, $economichead);
		$yearmoth = $period . "-" . date("m", strtotime($month));
		//die($yearmoth);
		$data['QueryVoultReport'] = $this->QueryVoultReportmonth($yearmoth, $allocationsource, $budgettype, $economichead, $economiccode, $month, $period);

		return view('funds.Report.VoultExpenditure', $data);
	}

	public function VoultExpendictureReport2026(Request $request)
	{

		$data['error'] = "";
		$data['warning'] = "";
		$data['success'] = "";
		$dateToday  = date('Y-m-d');
		$data['fromdate'] = $request['fromdate'];
		$data['todate'] = $request['todate'];
		$data['period'] = $request['period'];
		if ($data['fromdate'] == '') {
			$data['fromdate'] = date('Y-m-d', strtotime($dateToday . ' - 1 month'));
		}
		if ($data['todate'] == '') {
			$data['todate'] = $dateToday;
		}
		if ($data['period'] == '') {
			$data['period'] = $this->ActivePeriod();
		}


		$data['allocationsource'] = $request['allocationsource'];
		$data['budgettype'] = $request['budgettype'];
		$data['economichead'] = $request['economichead'];
		$data['economiccode'] = $request['economiccode'];
		$data['EconomicHead'] = $this->EconomicHead($data['budgettype']);
		$data['AllocationSource'] = $this->AllocationSource();
		$data['BudgetType'] = $this->BudgetType();
		$data['YearPeriod'] = $this->YearPeriod();
		$data['EconomicCode'] = $this->EconomicCode($data['allocationsource'], $data['economichead']);
		$data['VoteBookRecord'] = $this->VoteBookRecord($data['economiccode'], $data['fromdate'], $data['todate'], $data['period']);

		return view('funds.Report.votebookrecord', $data);
	}


	
	public function VoultExpendictureReport29012026(Request $request)
	{
		$data['error'] = "";
		$data['warning'] = "";
		$data['success'] = "";

		$dateToday  = date('Y-m-d');
		$data['fromdate'] = $request['fromdate'];
		$data['todate'] = $request['todate'];
		$data['period'] = $request['period'];

		if ($data['fromdate'] == '') {
			$data['fromdate'] = date('Y-m-d', strtotime($dateToday . ' - 1 month'));
		}

		if ($data['todate'] == '') {
			$data['todate'] = $dateToday;
		}

		if ($data['period'] == '') {
			$data['period'] = $this->ActivePeriod();
		}

		$data['allocationsource'] = $request['allocationsource'];
		$data['budgettype'] = $request['budgettype'];
		$data['economichead'] = $request['economichead'];
		$data['economiccode'] = $request['economiccode'];

		$data['EconomicHead'] = $this->EconomicHead($data['budgettype']);
		$data['AllocationSource'] = $this->AllocationSource();
		$data['BudgetType'] = $this->BudgetType();
		$data['YearPeriod'] = $this->YearPeriod();
		$data['EconomicCode'] = $this->EconomicCode($data['allocationsource'], $data['economichead']);

		// ✅ QUERY BUILDER ORDERING (ASC BY ID)
		$data['VoteBookRecord'] = $this->VoteBookRecord(
			$data['economiccode'],
			$data['fromdate'],
			$data['todate'],
			$data['period']
		);

		// dd($data);

		return view('funds.Report.votebookrecord', $data);
	}


	public function VoultExpendictureReport(Request $request)
	{
		$data['error'] = "";
		$data['warning'] = "";
		$data['success'] = "";

		$dateToday = date('Y-m-d');

		// 🔑 READ OLD INPUT FIRST (THIS IS THE FIX)
		$data['fromdate'] = old('fromdate', $request->fromdate);
		$data['todate']   = old('todate', $request->todate);
		$data['period']   = old('period', $request->period);

		if ($data['fromdate'] == '') {
			$data['fromdate'] = date('Y-m-d', strtotime($dateToday . ' - 1 month'));
		}

		if ($data['todate'] == '') {
			$data['todate'] = $dateToday;
		}

		if ($data['period'] == '') {
			$data['period'] = $this->ActivePeriod();
		}

		// 🔑 REST OF FILTERS
		$data['allocationsource'] = old('allocationsource', $request->allocationsource);
		$data['budgettype']       = old('budgettype', $request->budgettype);
		$data['economichead']     = old('economichead', $request->economichead);
		$data['economiccode']     = old('economiccode', $request->economiccode);

		// 🔑 LOAD DROPDOWNS BASED ON FILTERS
		$data['EconomicHead']     = $this->EconomicHead($data['budgettype']);
		$data['AllocationSource'] = $this->AllocationSource();
		$data['BudgetType']       = $this->BudgetType();
		$data['YearPeriod']       = $this->YearPeriod();
		$data['EconomicCode']     = $this->EconomicCode(
			$data['allocationsource'],
			$data['economichead']
		);

		// 🔑 FETCH REPORT DATA
		$data['VoteBookRecord'] = $this->VoteBookRecord(
			$data['economiccode'],
			$data['fromdate'],
			$data['todate'],
			$data['period']
		);

		// dd($data['VoteBookRecord']);

		return view('funds.Report.votebookrecord', $data);
	}



	
	public function toggleCancel(Request $request, $id)
	{
		$record = DB::table('tblvotebookrecord')->where('id', $id)->first();

		if (!$record) {
			return redirect()->back()
				->with('error', 'Record not found')
				->withInput();
		}

		$newStatus = $record->cancel_status == 1 ? 0 : 1;

		DB::table('tblvotebookrecord')
			->where('id', $id)
			->update(['cancel_status' => $newStatus]);

		return redirect()->back()
			->with('success', 'Status updated successfully')
			->withInput(); 
	}



	public function GetEconomicGroup()
	{

		$bank = DB::table('tblcontractType')->select('*')->get(); //Select all banks form database
		return $bank;
	}
}
 