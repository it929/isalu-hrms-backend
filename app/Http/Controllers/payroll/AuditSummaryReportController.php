<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;

class AuditSummaryReportController extends Controller
{
	public $division;
	public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
		// $this->division = $request->session()->get('division');
		// $this->divisionID = $request->session()->get('divisionID');
	            return $next($request);
        });
    }

	public function create()
	{
		return view('auditSummaryReport.index');
	}

	public function retrieve(Request $request)
	{
		$month     = trim($request->input('month'));
		$year      = trim($request->input('year'));
		$rank      = trim($request->input('rank'));
		$division  = $this->division;
		$this->validate($request, [
			'month'     => 'required|regex:/^[\pL\s\-]+$/u',
			'year'      => 'required|integer',
		]);


		$query = DB::table('tblpayment_consolidated as p')
		->join('tbldivision as d', 'p.divisionID', '=', 'd.divisionID')
		->select(
			'p.divisionID',
			'd.division',
			'p.month',
			'p.year',
			DB::raw('SUM(p.gross) as grosspay'),
			DB::raw('SUM(p.TD) as totalDeduct'),
			DB::raw('SUM(p.NetPay) as totalEmolu'),
			DB::raw('COUNT(p.divisionID) as totalStaff')
		)
			->where('p.year', $year)
			->where('p.month', $month)
			->where('p.rank', $rank) // Apply the rank filter
			->groupBy('p.divisionID', 'p.month', 'p.year', 'd.division');

		// Execute the query and get the results
		$data['summary'] = $query->get();

		// Optionally, calculate the totals for all divisions
		$totalSummary = [
			'totalDivisions' => $data['summary']->count(),
			'totalStaff' => $data['summary']->sum('totalStaff'),
			'totalGrossPay' => $data['summary']->sum('grosspay'),
			'totalDeduct' => $data['summary']->sum('totalDeduct'),
			'totalEmolu' => $data['summary']->sum('totalEmolu'),
			
			
		];

		// Add the total summary to the data
		$data['totalSummary'] = $totalSummary;

// dd($data);	


		return view('auditSummaryReport.summary', $data);
	}
}