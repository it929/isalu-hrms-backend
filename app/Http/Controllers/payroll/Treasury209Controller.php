<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\payroll\ParentController;

class Treasury209Controller extends ParentController
{
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        // $this->division   = $request->session()->get('division');
        // $this->divisionID = $request->session()->get('divisionID');
                return $next($request);
        });
    }
    public function loadView()
    {
        $data['bank']  = DB::table('tblbanklist')
            ->select('bank', 'bankID')
            ->orderBy('bank', 'Asc')
            ->get();
        /*$data['reporttype'] = DB::table('tbladmincode')
			 ->select('codeID', 'addressName', 'determinant')
	    	 ->orderBy('addressName', 'Asc')->get();*/

        $data['workingstate'] = DB::table('tblstates')
            ->select('StateID', 'State')
            ->distinct()
            ->orderBy('State', 'Asc')
            ->get();

        $data['currentstate'] = DB::table('tblcurrent_state')->get();
        $data['allDivisions'] = DB::table('tbldivision')->get();
        $data['cvSetup'] = DB::table('tblcvSetup')->where('particularID', 2)->select('ID', 'description')->get();
        $data['reporttype'] = DB::table('tbladmincode')->select('addressName', 'determinant')->get();

        //Division
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }
        $data['courts'] =  DB::table('tbl_court')->get();
        $courtSessionId = session('anycourt');
        $data['courtDivisions']  = DB::table('tbldivision')->where('courtID', '=', $courtSessionId)->get();
        $data['courtDivisions']  = DB::table('tbldivision')->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        return view('payroll.treasury209.treasury', $data);
    }
    public function loadReportView()
    {
        $data['bank']  = DB::table('tblbanklist')
            ->select('bank', 'bankID')
            ->orderBy('bank', 'Asc')
            ->get();
        /*$data['reporttype'] = DB::table('tbladmincode')
			 ->select('codeID', 'addressName', 'determinant')
	    	 ->orderBy('addressName', 'Asc')->get();*/

        $data['workingstate'] = DB::table('tblstates')
            ->select('StateID', 'State')
            ->distinct()
            ->orderBy('State', 'Asc')
            ->get();

        $data['currentstate'] = DB::table('tblcurrent_state')->get();
        $data['allDivisions'] = DB::table('tbldivision')->get();
        $data['cvSetup'] = DB::table('tblcvSetup')->where('particularID', 2)->select('ID', 'description')->get();
        $data['reporttype'] = DB::table('tbladmincode')->select('addressName', 'determinant')->get();

        //Division
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }
        $data['courts'] =  DB::table('tbl_court')->get();
        $courtSessionId = session('anycourt');
        $data['courtDivisions']  = DB::table('tbldivision')->where('courtID', '=', $courtSessionId)->get();
        $data['courtDivisions']  = DB::table('tbldivision')->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        return view('payroll.treasury209.treasuryReport', $data);
    }

    public function pensionView()
    {
        $data['bank']  = DB::table('tblbanklist')
            ->select('bank', 'bankID')
            ->orderBy('bank', 'Asc')
            ->get();
        /*$data['reporttype'] = DB::table('tbladmincode')
			 ->select('codeID', 'addressName', 'determinant')
	    	 ->orderBy('addressName', 'Asc')->get();*/

        $data['workingstate'] = DB::table('tblstates')
            ->select('StateID', 'State')
            ->distinct()
            ->orderBy('State', 'Asc')
            ->get();

        $data['currentstate'] = DB::table('tblcurrent_state')->get();

        $data['allDivisions'] = DB::table('tbldivision')->get();

        $data['cvSetup'] = DB::table('tblcvSetup')->where('particularID', 2)->select('ID', 'description')->get();

        $data['reporttype'] = DB::table('tbladmincode')->where('codeID', 1)->select('addressName', 'determinant')->get();

        return view('payroll.treasury209.treasurypension', $data);
    }

    //view for treasury209 justices
    public function loadViewJustices()
    {
        $data['bank']  = DB::table('tblbanklist')
            ->select('bank', 'bankID')
            ->orderBy('bank', 'Asc')
            ->get();

        //   $data['bank'] = DB::table('tblbanklist')
        //         ->join('tblpayment_consolidated', 'tblpayment_consolidated.bank', '=', 'tblbanklist.bankID')
        //         ->where('tblpayment_consolidated.rank', '=', 2)
        //         ->groupBy('tblpayment_consolidated.bank')
        //         ->orderBy('tblbanklist.bank', 'Asc')
        //         ->select('tblbanklist.bank', 'tblbanklist.bankID')
        //         ->get();

        $data['workingstate'] = DB::table('tblstates')
            ->select('StateID', 'State')
            ->distinct()
            ->orderBy('State', 'Asc')
            ->get();

        $data['currentstate'] = DB::table('tblcurrent_state')->get();

        $data['allDivisions'] = DB::table('tbldivision')->get();

        $data['cvSetup'] = DB::table('tblcvSetup')->where('particularID', 2)->select('ID', 'description')->get();

        $data['reporttype'] = DB::table('tbladmincode')->select('addressName', 'determinant')->get();

        return view('payroll.treasuryJustices.treasury209Justices', $data);
    }
    //end view for treasury209 justices


    public function curDivision($userId)
    {
        $currentDivision = DB::table("users")
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'users.divisionID')
            ->where('users.id', '=', $userId)
            ->select('tbldivision.division', 'tbldivision.divisionID')
            ->first();
        return $currentDivision;
    }

    public function loadViewCouncil()
    {
        $data['bank']  = DB::table('tblbanklist')
            ->select('bank', 'bankID')
            ->orderBy('bank', 'Asc')
            ->get();
        /*$data['reporttype'] = DB::table('tbladmincode')
			 ->select('codeID', 'addressName', 'determinant')
	    	 ->orderBy('addressName', 'Asc')->get();*/

        $data['workingstate'] = DB::table('tblstates')
            ->select('StateID', 'State')
            ->distinct()
            ->orderBy('State', 'Asc')
            ->get();

        $data['currentstate'] = DB::table('tblcurrent_state')->get();
        $data['allDivisions'] = DB::table('tbldivision')->get();
        $data['cvSetup'] = DB::table('tblcvSetup')->select('ID', 'description')->get();

        $data['reporttype'] = DB::table('tbladmincode')->select('addressName', 'determinant')->get();

        return view('payroll.treasury209.treasury_council', $data);
    }

    public function load()
    {
        $data['bank']  = DB::table('tblbanklist')
            ->select('bank', 'bankID')
            ->orderBy('bank', 'Asc')
            ->get();
        /*$data['reporttype'] = DB::table('tbladmincode')
			 ->select('codeID', 'addressName', 'determinant')
	    	 ->orderBy('addressName', 'Asc')->get();*/

        $data['workingstate'] = DB::table('tblstates')
            ->select('StateID', 'State')
            ->distinct()
            ->orderBy('State', 'Asc')
            ->get();


        $data['cvSetup'] = DB::table('tblcvSetup')->select('ID', 'description')->get();

        $data['reporttype'] = DB::table('tbladmincode')
            ->select('addressName', 'determinant')
            //->union($cvSetup)
            ->get();
        return view('payroll.treasury209.treasury2', $data);
    }

    public function viewCouncil(Request $request)
    {
        $this->validate($request, [

            'month'         => 'required|alpha',
            'year'          => 'required|numeric',
        ]);
        $type            = trim($request['reporttype']);
        $month          = trim($request['month']);
        $year           = trim($request['year']);
        $bank           = trim($request['bank']);

        $data['year'] = $year;
        $data['month'] = $month;
        $bankgroup      = trim($request['bankgroup']);
        $working_state   = trim($request['workingstate']);
        $data['selectedMonth'] = $month;
        $currentstate      = trim($request['currentState']);
        $division = $request['division'];
        //dd($rtype );

        if ($currentstate != "" && $division != "") {
            $reportess = DB::table('tblpayment_consolidated')->where('month', $data['month'])->where('current_state', $currentstate)->where('divisionID', $division)->where('rank', 2)->get();
        } elseif ($currentstate == "" && $division != "") {
            $reportess = DB::table('tblpayment_consolidated')->where('month', $data['month'])->where('divisionID', $division)->where('rank', 2)->get();
        } elseif ($currentstate != "" && $division === "") {
            $reportess = DB::table('tblpayment_consolidated')->where('month', $data['month'])->where('current_state', $currentstate)->where('rank', 2)->get();
        } else {
            $reportess = DB::table('tblpayment_consolidated')->where('month', $data['month'])->where('rank', 2)->get();
        }
        // 		$reportess = DB::table('tblpayment_consolidated')->where('month', $data['month'])->where('year', $data['year'])->where('rank', 2)->get();
        if ($type == 'TAX' && $working_state != "") {
            $data['payeeCaption'] = DB::table('tblcurrent_state')->where('id', '=', $currentstate)->first();
        } else {
            $data['payeeCaption'] = DB::table('tblcurrent_state')->where('id', '=', 1)->first();
        }
        $data['payment'] = $reportess;
        $data['Tr2019Head'] = $this->Tr2019Head($type);
        $data['selectedMonth'] = $request['month'];
        $data['selectedYear'] = $request['year'];
        $data['currentstate'] = DB::table('tblcurrent_state')->where('id', $currentstate)->first();
        $data['division'] = DB::table('tbldivision')->where('divisionID', $division)->first();
        $data['reportType'] = $type;

        return view('payroll.treasury209.councilReport', $data);
        //dd($reportess);

    }
    public function view(Request $request)
    {
        $this->validate($request, [
            'reporttype'    => 'required',
            'month'         => 'required|alpha',
            'year'          => 'required|numeric',
            //'division'      => 'required|numeric',
            //'bank'          => 'required|numeric',
            //'bankgroup'     => 'required|numeric',
            //'workingstate'  => 'required_if:reportType,tax|string',
        ]);
        $type            = trim($request['reporttype']);
        $month          = trim($request['month']);
        $year           = trim($request['year']);
        $bank           = trim($request['bank']);
        $division       = trim($request['division']);

        $data['year'] = $year;
        $data['month'] = $month;
        $bankgroup      = trim($request['bankgroup']);
        $working_state   = trim($request['workingstate']);
        $data['selectedMonth'] = $month;
        $data['divisionName'] = DB::table('tbldivision')->where('divisionID', $division)->value('division');
        $currentstate      = trim($request['currentState']);

        $reportess = $this->Tr2019('', $division, $year, $month, $type, $bank, $currentstate);
        if ($type == 'TAX' && $working_state != "") {
            $data['payeeCaption'] = DB::table('tblcurrent_state')->where('id', '=', $currentstate)->first();
        } else {
            $data['payeeCaption'] = DB::table('tblcurrent_state')->where('id', '=', 1)->first();
        }
        $data['payment'] = $reportess;
        $data['Tr2019Head'] = $this->Tr2019Head($type);
        $data['selectedMonth'] = $request['month'];
        $data['selectedYear'] = $request['year'];
        $data['reportType'] = DB::table('tbladmincode')->where('determinant', '=', $type)->first();


        return view('payroll.treasury209.testReport', $data);
    }
    public function viewNew_22_02_2026(Request $request)
    {
        $this->validate($request, [
            'reporttype'    => 'required',
            'month'         => 'required|alpha',
            'year'          => 'required|numeric',
            //'division'      => 'required|numeric',
            //'bank'          => 'required|numeric',
            //'bankgroup'     => 'required|numeric',
            //'workingstate'  => 'required_if:reportType,tax|string',
        ]);
        $type            = trim($request['reporttype']);
        $month          = trim($request['month']);
        $year           = trim($request['year']);
        $bank           = trim($request['bank']);
        $division       = trim($request['division']);

        $data['year'] = $year;
        $data['month'] = $month;
        $bankgroup      = trim($request['bankgroup']);
        $working_state   = trim($request['workingstate']);
        $data['selectedMonth'] = $month;
        $data['divisionName'] = DB::table('tbldivision')->where('divisionID', $division)->value('division');
        $currentstate      = trim($request['currentState']);

        $reportess = $this->Tr2019('', $division, $year, $month, $type, $bank, $currentstate);
        if ($type == 'TAX' && $working_state != "") {
            $data['payeeCaption'] = DB::table('tblcurrent_state')->where('id', '=', $currentstate)->first();
        } else {
            $data['payeeCaption'] = DB::table('tblcurrent_state')->where('id', '=', 1)->first();
        }
        $data['payment'] = $reportess;
        $data['Tr2019Head'] = $this->Tr2019Head($type);
        $data['selectedMonth'] = $request['month'];
        $data['selectedYear'] = $request['year'];
        $data['reportType'] = DB::table('tbladmincode')->where('determinant', '=', $type)->first();
        $data['banklist'] = $data['bank']  = DB::table('tblbanklist')->where('bankID', '=', $bank)->first();
        // dd($data['reportType']);
        // dd($data);


        return view('payroll.treasury209.testReportNew', $data);
    }
    public function viewNew(Request $request)
    {
        $this->validate($request, [
            'reporttype'    => 'required',
            'month'         => 'required|alpha',
            'year'          => 'required|numeric',
            //'division'      => 'required|numeric',
            //'bank'          => 'required|numeric',
            //'bankgroup'     => 'required|numeric',
            //'workingstate'  => 'required_if:reportType,tax|string',
        ]);
        $type            = trim($request['reporttype']);
        // dd($type);
        $month          = trim($request['month']);
        $year           = trim($request['year']);
        $bank           = trim($request['bank']);
        $division       = trim($request['division']);

        $data['year'] = $year;
        $data['month'] = $month;
        $bankgroup      = trim($request['bankgroup']);
        $working_state   = trim($request['workingstate']);
        $data['selectedMonth'] = $month;
        $data['divisionName'] = DB::table('tbldivision')->where('divisionID', $division)->value('division');
        $currentstate      = trim($request['currentState']);

        $reportess = $this->Tr2019('', $division, $year, $month, $type, $bank, $currentstate);
        // if ($type == 'TAX' && $working_state != "") {
        // 	$data['payeeCaption'] = DB::table('tblcurrent_state')->where('id', '=', $currentstate)->first();
        // } else {
        // 	$data['payeeCaption'] = DB::table('tblcurrent_state')->where('id', '=', 1)->first();
        // }
        if ($type == 'TAX' && $currentstate != "") {
            $data['payeeCaption'] = DB::table('tblcurrent_state')->where('id', '=', $currentstate)->first();
        }

        $data['payeeCaption'] = DB::table('tblcurrent_state')->where('id', '=', $currentstate)->first();
        $data['payment'] = $reportess;
        $data['Tr2019Head'] = $this->Tr2019Head($type);
        $data['selectedMonth'] = $request['month'];
        $data['selectedYear'] = $request['year'];
        $data['reportType'] = DB::table('tbladmincode')->where('determinant', '=', $type)->first();
        $data['banklist'] = $data['bank']  = DB::table('tblbanklist')->where('bankID', '=', $bank)->first();
        // dd($data['payment'],);
        // dd($data);


        return view('payroll.treasury209.testReportNew', $data);
    }


    public function viewReportNew(Request $request)
    {
        $this->validate($request, [
            'reporttype'    => 'required',
            'month'         => 'required|alpha',
            'year'          => 'required|numeric',
            //'division'      => 'required|numeric',
            //'bank'          => 'required|numeric',
            //'bankgroup'     => 'required|numeric',
            //'workingstate'  => 'required_if:reportType,tax|string',
        ]);
        $type            = trim($request['reporttype']);
        $month          = trim($request['month']);
        $year           = trim($request['year']);
        $bank           = trim($request['bank']);
        $division       = trim($request['division']);

        $data['year'] = $year;
        $data['month'] = $month;
        $bankgroup      = trim($request['bankgroup']);
        $working_state   = trim($request['workingstate']);
        $data['selectedMonth'] = $month;
        $data['divisionName'] = DB::table('tbldivision')->where('divisionID', $division)->value('division');
        $currentstate      = trim($request['currentState']);

        $reportess = $this->Tr2019('', $division, $year, $month, $type, $bank, $currentstate);
        if ($type == 'TAX' && $working_state != "") {
            $data['payeeCaption'] = DB::table('tblcurrent_state')->where('id', '=', $currentstate)->first();
        } else {
            $data['payeeCaption'] = DB::table('tblcurrent_state')->where('id', '=', 1)->first();
        }
        $data['payment'] = $reportess;
        $data['Tr2019Head'] = $this->Tr2019Head($type);
        $data['selectedMonth'] = $request['month'];
        $data['selectedYear'] = $request['year'];
        $data['reportType'] = DB::table('tbladmincode')->where('determinant', '=', $type)->first();
        $data['banklist'] = $data['bank']  = DB::table('tblbanklist')->where('bankID', '=', $bank)->first();
        // dd($data['reportType']);
        // dd($data);


        return view('payroll.treasury209.testReportNew2', $data);
    }

    public function Tr201906022026($court, $division, $year, $month, $para, $bank, $residential = '')
    {
        $qresidential = 1;
        if ($residential != '') {
            $qresidential = "`tblpayment_consolidated`.`current_state`='$residential'";
        }

        $qdivision = 1;
        if ($division != '') {
            $qdivision = "`tblpayment_consolidated`.`divisionID`='$division'";
        }

        if ($para == '' || $para == 'Select') {
            return [];
        }

        $qbank = 1;
        $vpara = '';
        $qpara = 1;

        if ($bank != '') {
            $qbank = "`bank`='$bank'";
        }

        $vpara = " ,`$para` as Vpara";

        $List = DB::Select("SELECT
			tblpayment_consolidated.*,
			tblpayment_consolidated.fileNo,  -- ADDED THIS LINE
			tblper.first_name,
			tblper.othernames,
			tblper.surname,
			tblper.dob,
			tblper.appointment_date,
			tblper.date_present_appointment,
			tbldesignation.designation
			$vpara
		FROM `tblpayment_consolidated`
		JOIN tblper ON tblpayment_consolidated.staffid = tblper.ID
		LEFT JOIN tbldesignation ON tbldesignation.id = tblper.Designation
		WHERE `year`='$year'
			AND `month`='$month'
			AND $qbank
			AND $qpara
			AND tblpayment_consolidated.rank<>2
			AND $qresidential
			AND $qdivision
		ORDER BY tblpayment_consolidated.rank DESC,
			tblpayment_consolidated.grade DESC,
			tblpayment_consolidated.step DESC");

        return $List;
    }

    public function Tr2019_25_02_2026($court, $division, $year, $month, $para, $bank, $residential = '')
    {
        $qresidential = 1;
        if ($residential != '') {
            $qresidential = "`tblpayment_consolidated`.`current_state`='$residential'";
        }

        $qdivision = 1;
        if ($division != '') {
            $qdivision = "`tblpayment_consolidated`.`divisionID`='$division'";
        }

        if ($para == '' || $para == 'Select') {
            return [];
        }

        $qbank = 1;

        if ($bank != '') {
            $qbank = "`bank`='$bank'";
        }

        // Handle different types of parameters
        if (is_numeric($para)) {
            // This is from cvSetup - need to join with otherEarningDeduction table
            $List = DB::Select("SELECT
				tblpayment_consolidated.*,
				tblpayment_consolidated.fileNo,
				tblper.first_name,
				tblper.othernames,
				tblper.surname,
				tblper.dob,
				tblper.appointment_date,
				tblper.date_present_appointment,
				tbldesignation.designation,
				COALESCE(tblotherEarningDeduction.amount, 0) as Vpara
			FROM `tblpayment_consolidated`
			JOIN tblper ON tblpayment_consolidated.staffid = tblper.ID
			LEFT JOIN tbldesignation ON tbldesignation.id = tblper.Designation
			LEFT JOIN tblotherEarningDeduction ON tblotherEarningDeduction.staffid = tblpayment_consolidated.staffid
				AND tblotherEarningDeduction.year = tblpayment_consolidated.year
				AND tblotherEarningDeduction.month = tblpayment_consolidated.month
				AND tblotherEarningDeduction.CVID = '$para'
			WHERE `tblpayment_consolidated`.`year`='$year'
				AND `tblpayment_consolidated`.`month`='$month'
				AND $qbank
				AND $qresidential
				AND $qdivision
				AND tblpayment_consolidated.rank<>2
			HAVING Vpara > 0
			ORDER BY tblpayment_consolidated.rank DESC,
				tblpayment_consolidated.grade DESC,
				tblpayment_consolidated.step DESC");
        } else {
            // This is from admincode - use column from consolidated table
            $List = DB::Select("SELECT
				tblpayment_consolidated.*,
				tblpayment_consolidated.fileNo,
				tblper.first_name,
				tblper.othernames,
				tblper.surname,
				tblper.dob,
				tblper.appointment_date,
				tblper.date_present_appointment,
				tbldesignation.designation,
				`$para` as Vpara
			FROM `tblpayment_consolidated`
			JOIN tblper ON tblpayment_consolidated.staffid = tblper.ID
			LEFT JOIN tbldesignation ON tbldesignation.id = tblper.Designation
			WHERE `year`='$year'
				AND `month`='$month'
				AND $qbank
				AND `$para` > 0
				AND tblpayment_consolidated.rank<>2
				AND $qresidential
				AND $qdivision
			ORDER BY tblpayment_consolidated.rank DESC,
				tblpayment_consolidated.grade DESC,
				tblpayment_consolidated.step DESC");
        }



        return $List;
    }

    public function Tr2019($court, $division, $year, $month, $para, $bank, $residential = '')
    {
        $qresidential = 1;
        if ($residential != '') {
            $qresidential = "`tblpayment_consolidated`.`current_state`='$residential'";
        }

        $qdivision = 1;
        if ($division != '') {
            $qdivision = "`tblpayment_consolidated`.`divisionID`='$division'";
        }

        if ($para == '' || $para == 'Select') {
            return [];
        }

        $qbank = 1;
        if ($bank != '') {
            $qbank = "`bank`='$bank'";
        }

        // If CVID (numeric)
        if (is_numeric($para)) {

            // FIXED QUERY: SUM deductions per staff BEFORE joining
            $List = DB::select("
            SELECT
                tblpayment_consolidated.*,
                tblpayment_consolidated.fileNo,
                tblper.first_name,
                tblper.othernames,
                tblper.surname,
                tblper.dob,
                tblper.appointment_date,
                tblper.date_present_appointment,
                tbldesignation.designation,
                COALESCE(oed.total_amount, 0) AS Vpara
            FROM tblpayment_consolidated
            JOIN tblper
                ON tblpayment_consolidated.staffid = tblper.ID
            LEFT JOIN tbldesignation
                ON tbldesignation.id = tblper.Designation

            /* GROUPED SUBQUERY SO STAFF APPEARS ONLY ONCE */
            LEFT JOIN (
                SELECT staffid, year, month, SUM(amount) AS total_amount
                FROM tblotherEarningDeduction
                WHERE CVID = '$para'
                GROUP BY staffid, year, month
            ) AS oed
                ON oed.staffid = tblpayment_consolidated.staffid
                AND oed.year = tblpayment_consolidated.year
                AND oed.month = tblpayment_consolidated.month

            WHERE tblpayment_consolidated.year = '$year'
                AND tblpayment_consolidated.month = '$month'
                AND $qbank
                AND $qresidential
                AND $qdivision
                AND tblpayment_consolidated.rank <> 2

            HAVING Vpara > 0

            ORDER BY tblpayment_consolidated.rank DESC,
                     tblpayment_consolidated.grade DESC,
                     tblpayment_consolidated.step DESC
        ");
        } else {
            // From admincode (non-numeric)
            $List = DB::select("
            SELECT
                tblpayment_consolidated.*,
                tblpayment_consolidated.fileNo,
                tblper.first_name,
                tblper.othernames,
                tblper.surname,
                tblper.dob,
                tblper.appointment_date,
                tblper.date_present_appointment,
                tbldesignation.designation,
                `$para` AS Vpara
            FROM tblpayment_consolidated
            JOIN tblper
                ON tblpayment_consolidated.staffid = tblper.ID
            LEFT JOIN tbldesignation
                ON tbldesignation.id = tblper.Designation
            WHERE year = '$year'
                AND month = '$month'
                AND $qbank
                AND `$para` > 0
                AND tblpayment_consolidated.rank <> 2
                AND $qresidential
                AND $qdivision
            ORDER BY tblpayment_consolidated.rank DESC,
                     tblpayment_consolidated.grade DESC,
                     tblpayment_consolidated.step DESC
        ");
        }

        return $List;
    }

    public function Tr2019Head06022026($para)
    {
        if ($para == '' || $para == 'Select') {
            return '';
        }

        if (intval($para) > 0) {
            return DB::table('tblcvSetup')->where('ID', $para)->value('description');
        } else {
            return DB::table('tbladmincode')->where('determinant', $para)->value('addressName');
        }

        $List = DB::Select("SELECT * $vpara FROM `tblpayment_consolidated` WHERE `year`='$year' and `month`='$month' and $qbank and $qpara and rank<>2");
        return $List;
    }

    public function Tr2019Head($para)
    {
        if ($para == '' || $para == 'Select') {
            return '';
        }

        if (is_numeric($para)) {
            return DB::table('tblcvSetup')->where('ID', $para)->value('description');
        } else {
            return DB::table('tbladmincode')->where('determinant', $para)->value('addressName');
        }
    }

    //retrieve records for treasury209 justices
    public function viewJustices(Request $request)
    {
        $this->validate($request, [
            'reporttype'    => 'required',
            'month'         => 'required|alpha',
            'year'          => 'required|numeric',
            //'division'      => 'required|numeric',
            //'bank'          => 'required|numeric',
            //'bankgroup'     => 'required|numeric',
            //'workingstate'  => 'required_if:reportType,tax|string',
        ]);
        $type            = trim($request['reporttype']);
        $month          = trim($request['month']);
        $year           = trim($request['year']);
        $bank           = trim($request['bank']);
        $division       = trim($request['division']);

        $data['year'] = $year;
        $data['month'] = $month;
        $bankgroup      = trim($request['bankgroup']);
        $working_state   = trim($request['workingstate']);
        $data['selectedMonth'] = $month;
        $data['divisionName'] = DB::table('tbldivision')->where('divisionID', $division)->value('division');
        $currentstate      = trim($request['currentState']);

        $reportess = $this->Tr2019Justices('', $division, $year, $month, $type, $bank, $currentstate);
        if ($type == 'TAX' && $working_state != "") {
            $data['payeeCaption'] = DB::table('tblcurrent_state')->where('id', '=', $currentstate)->first();
        } else {
            $data['payeeCaption'] = DB::table('tblcurrent_state')->where('id', 'Tr2019=', 1)->first();
        }
        $data['payment'] = $reportess;
        $data['Tr2019Head'] = $this->Tr2019HeadJustices($type);
        $data['selectedMonth'] = $request['month'];
        $data['selectedYear'] = $request['year'];
        $data['reportType'] = DB::table('tbladmincode')->where('determinant', '=', $type)->first();

        return view('payroll.treasury209.testReport', $data);
    }

    public function Tr2019Justices($court, $division, $year, $month, $para, $bank, $residential = '')
    {
        $qresidential = 1;
        if ($residential != '') {
            $qresidential = "`tblpayment_consolidated`.`current_state`='$residential'";
        }
        $qdivision = 1;
        if ($division != '') {
            $qdivision = "`tblpayment_consolidated`.`divisionID`='$division'";
        }
        if ($para == '' || $para == 'Select') {
            return [];
        }
        $qbank = 1;
        $vpara = '';
        $qpara = 1;
        if ($bank != '') {
            $qbank = "`bank`='$bank'";
        }


        $vpara = " ,`$para` as Vpara";


        $List = DB::Select("SELECT tblpayment_consolidated.*,
			tblper.first_name,
			tblper.othernames,
			tblper.surname,
			tblper.dob,
			tblper.appointment_date,
			tblper.date_present_appointment,
			tbldesignation.designation
			$vpara FROM `tblpayment_consolidated`
			join tblper on tblpayment_consolidated.staffid = tblper.ID
			left join tbldesignation on tbldesignation.id = tblper.Designation
			WHERE `year`='$year' and `month`='$month' and $qbank and $qpara and tblpayment_consolidated.rank = 2 and $qresidential  and $qdivision
			order by tblpayment_consolidated.rank DESC, tblpayment_consolidated.grade DESC, tblpayment_consolidated.step DESC");

        return $List;
    }

    public function Tr2019HeadJustices($para)
    {
        if ($para == '' || $para == 'Select') {
            return '';
        }

        if (intval($para) > 0) {
            return DB::table('tblcvSetup')->where('ID', $para)->value('description');
        } else {
            return DB::table('tbladmincode')->where('determinant', $para)->value('addressName');
        }

        $List = DB::Select("SELECT * $vpara FROM `tblpayment_consolidated` WHERE `year`='$year' and `month`='$month' and $qbank and $qpara and rank = 2");
        return $List;
    }

    //end retrieve records for treasury209 justices
}
