<?php
//
namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\payroll\ParentController;

class TreasuryF1Controller extends ParentController
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
            ->select('StateID', 'State', 'id')
            ->distinct()
            ->orderBy('State', 'Asc')
            ->get();

        $data['courts'] =  DB::table('tbl_court')->get();

        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        $data['courtDivisions']  = DB::table('tbldivision')->get();

        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        $data['cvSetup'] = DB::table('tblcvSetup')->select('ID', 'description')->get();

        $data['reporttype'] = DB::table('tbladmincode')->select('addressName', 'determinant')->get();

        return view('payroll.treasuryF1.treasury', $data);
    }

    //view for treasuryF1 justices
    public function loadJusticesView()
    {
        $data['bank']  = DB::table('tblbanklist')
            ->select('bank', 'bankID')
            ->orderBy('bank', 'Asc')
            ->get();

        $data['workingstate'] = DB::table('tblstates')
            ->select('StateID', 'State', 'id')
            ->distinct()
            ->orderBy('State', 'Asc')
            ->get();

        $data['courts'] =  DB::table('tbl_court')->get();

        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        $data['courtDivisions']  = DB::table('tbldivision')->get();

        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        $data['cvSetup'] = DB::table('tblcvSetup')->select('ID', 'description')->get();

        $data['reporttype'] = DB::table('tbladmincode')->select('addressName', 'determinant')->get();

        return view('payroll.treasuryJustices.treasuryF1Justices', $data);
    }
    //end view for treasuryF1 justices

    public function view(Request $request)
    {
        $this->validate(
            $request,
            [
                'reportType'    => 'required|string',
                'month'         => 'required|alpha',
                'year'          => 'required|numeric',
                //'bank'          => 'required|string',
                //'bankGroup'     => 'required|numeric',
                //'workingState'  => 'required_if:reportType,tax|string',
            ]
        );
        if (trim($request['reportType']) == "TAX") {
            $this->validate($request, ['workingstate'  => 'required']);
        }

        $data['selectedWorkingstate'] = "";

        if ($request['workingstate']) {
            $data['selectedWorkingstate'] = DB::table('tblstates')
                ->where('id', $request['workingstate'])
                ->first(['State']);
        }

        $type            = trim($request['reportType']);
        $month          = trim($request['month']);
        $year           = trim($request['year']);
        $bank           = trim($request['bank']);
        $division       = $request['division'];

        $bankgroup      = trim($request['bankgroup']);
        $working_state   = trim($request['workingstate']);
        $data['selectedMonth'] = $month;
        $data['divisionName'] = DB::table('tbldivision')->where('divisionID', $division)->value('division');
        DB::enableQueryLog();
        if ($type == 'gross') {
            $rtype = 'NetPay';
        } elseif ($type == 'TAX') {
            $rtype = 'TAX';
        } else {
            $rtype = trim($request['reportType']);
        }
        $data['banklist'] = $data['bank']  = DB::table('tblbanklist')->where('bankID', '=', $bank)->first();
        $data['rtype'] = $type;

        if (is_numeric($rtype)) {

            if ($bank == '') {
                $data['reportTitle'] = DB::table('tblcvSetup')->where('ID', '=', $type)->select('description as desc', 'address')->first();

                $data['payment'] = DB::table('tblotherEarningDeduction')
                    ->join('tblpayment_consolidated', 'tblpayment_consolidated.staffid', '=', 'tblotherEarningDeduction.staffid')
                    ->select('tblpayment_consolidated.name as fullname', "tblotherEarningDeduction.amount as amt")
                    ->where('tblotherEarningDeduction.CVID', '=', $rtype)
                    ->where('tblotherEarningDeduction.month', '=', $month)
                    ->where('tblotherEarningDeduction.year', '=', $year)
                    ->where('tblpayment_consolidated.rank', '!=', 2)
                    ->groupBY('tblotherEarningDeduction.staffid')
                    ->where('tblotherEarningDeduction.divisionID', $division ? '=' : '<>', $division)
                    ->get();
            } else {
                $data['reportTitle'] = DB::table('tbladmincode')->where('determinant', '=', $request['reportType'])->select('address as desc')->first();

                $data['payment'] = DB::table('tblotherEarningDeduction')
                    ->join('tblpayment_consolidated', 'tblpayment_consolidated.staffid', '=', 'tblotherEarningDeduction.staffid')
                    ->select('tblpayment_consolidated.name as fullname', "tblotherEarningDeduction.amount as amt")
                    ->where('tblotherEarningDeduction.CVID', '=', $rtype)
                    ->where('tblotherEarningDeduction.month', '=', $month)
                    ->where('tblotherEarningDeduction.year', '=', $year)
                    ->where('tblpayment_consolidated.bank', '=', $bank)
                    ->where('tblpayment_consolidated.rank', '!=', 2)
                    ->groupBY('tblotherEarningDeduction.staffid')
                    ->where('tblotherEarningDeduction.divisionID', $division ? '=' : '<>', $division)
                    ->get();
            }
        } else {
            if ($bank == '') {
                $data['reportTitle'] = DB::table('tbladmincode')->where('determinant', '=', $type)->select('addressName as desc')->first();
                $data['payment'] = DB::table('tblpayment_consolidated')->select('name as fullname', "$rtype as amt", 'PEC')->where('month', '=', $month)
                    ->where('year', '=', $year)
                    ->where('rank', '!=', 2)
                    ->where('divisionID', $division ? '=' : '<>', $division)
                    ->get();
            } else {
                $data['reportTitle'] = DB::table('tbladmincode')->where('determinant', '=', $type)->select('addressName as desc')->first();
                $data['payment'] = DB::table('tblpayment_consolidated')->select('name as fullname', "$rtype as amt", 'PEC')->where('month', '=', $month)->where('year', '=', $year)
                    ->where('rank', '!=', 2)->where('bank', '=', $bank)
                    ->where('divisionID', $division ? '=' : '<>', $division)->get();
            }

            if ($rtype == "TAX") {
                if ($working_state) {
                    if ($bank == '') {
                        $data['reportTitle'] = DB::table('tbladmincode')->where('determinant', '=', $type)->select('addressName as desc')->first();
                        $data['payment'] = DB::table('tblpayment_consolidated')->select('name as fullname', "$rtype as amt", 'PEC')
                            ->where('tblpayment_consolidated.current_state', '=', $working_state)
                            ->where('month', '=', $month)
                            ->where('year', '=', $year)
                            ->where('tblpayment_consolidated.rank', '!=', 2)
                            ->where('divisionID', $division ? '=' : '<>', $division)
                            ->get();
                    } else {
                        $data['reportTitle'] = DB::table('tbladmincode')->where('determinant', '=', $type)->select('addressName as desc')->first();
                        $data['payment'] = DB::table('tblpayment_consolidated')->select('name as fullname', "$rtype as amt", 'PEC')
                            ->where('tblpayment_consolidated.current_state', '=', $working_state)
                            ->where('month', '=', $month)
                            ->where('year', '=', $year)
                            ->where('bank', '=', $bank)
                            ->where('tblpayment_consolidated.rank', '!=', 2)
                            ->where('divisionID', $division ? '=' : '<>', $division)
                            ->get();
                    }
                } else {
                    return back()->with('err', 'Please select working state');
                }
            }
        }
        $data['selectedYear'] = $year;
        $data['month'] = $month;

        if (is_null($data['payment']))
            return back()->with('msg', 'Record not found!');
        $noRecord = 0;
        $amount     = 0.0;
        $total = 0.00;
        $jusuTotal = 0.00;
        $totalUser  = 0;
        $data['payeAddress'] = $working_state . ' STATE INTERNAL REVENUE, ' . $working_state;
        foreach ($data['payment'] as $row) {
            $amount += ($row->amt);
            if (($amount <> 0)) {
                $totalUser   += 1;
                $total       += ($row->amt);
            }
            $data['record']   =  $row;
        } //end foreach
        if ($totalUser > 1)
            $userStatus = "and $totalUser others";
        else
            $userStatus = "";
        $data['getValue'] =  $rtype;
        $data['totalSum'] =  $total;
        $data['jusuSum']  = $jusuTotal;
        $data['details']  = $data['payment'];
        $data['getStatus']  =  $userStatus;
        // dd($data);
        if ($data['payment'] && $data['totalSum'] <> 0)
            return view('payroll.treasuryF1.tf1voucher', $data);
        else
            return back()->with('msg', 'Record not found/Empty Record!');
    }

    //retrieve treasuryF1 justices
    public function viewJustices(Request $request)
    {
        $this->validate(
            $request,
            [
                'reportType'    => 'required|string',
                'month'         => 'required|alpha',
                'year'          => 'required|numeric',
                //'bank'          => 'required|string',
                //'bankGroup'     => 'required|numeric',
                //'workingState'  => 'required_if:reportType,tax|string',
            ]
        );
        $type            = trim($request['reportType']);
        $month          = trim($request['month']);
        $year           = trim($request['year']);
        $bank           = trim($request['bank']);
        $division       = $request['division'];

        $bankgroup      = trim($request['bankgroup']);
        $working_state   = trim($request['workingstate']);
        $data['selectedMonth'] = $month;
        $data['divisionName'] = DB::table('tbldivision')->where('divisionID', $division)->value('division');
        DB::enableQueryLog();
        if ($type == 'gross') {
            $rtype = 'NetPay';
        } elseif ($type == 'TAX') {
            $rtype = 'TAX';
        } else {
            $rtype = trim($request['reportType']);
        }
        $data['banklist'] = $data['bank']  = DB::table('tblbanklist')->where('bankID', '=', $bank)->first();
        $data['rtype'] = $type;

        if (is_numeric($rtype)) {

            if ($bank == '') {
                $data['reportTitle'] = DB::table('tblcvSetup')->where('ID', '=', $type)->select('description as desc', 'address')->first();

                $data['payment'] = DB::table('tblotherEarningDeduction')
                    ->join('tblpayment_consolidated', 'tblpayment_consolidated.staffid', '=', 'tblotherEarningDeduction.staffid')
                    ->select('tblpayment_consolidated.name as fullname', "tblotherEarningDeduction.amount as amt")
                    ->where('tblotherEarningDeduction.CVID', '=', $rtype)
                    ->where('tblotherEarningDeduction.month', '=', $month)
                    ->where('tblotherEarningDeduction.year', '=', $year)
                    ->where('tblpayment_consolidated.rank', '=', 2)
                    ->groupBY('tblotherEarningDeduction.staffid')
                    ->where('tblotherEarningDeduction.divisionID', $division ? '=' : '<>', $division)
                    ->get();
            } else {
                $data['reportTitle'] = DB::table('tbladmincode')->where('determinant', '=', $request['reportType'])->select('address as desc')->first();

                $data['payment'] = DB::table('tblotherEarningDeduction')
                    ->join('tblpayment_consolidated', 'tblpayment_consolidated.staffid', '=', 'tblotherEarningDeduction.staffid')
                    ->select('tblpayment_consolidated.name as fullname', "tblotherEarningDeduction.amount as amt")
                    ->where('tblotherEarningDeduction.CVID', '=', $rtype)
                    ->where('tblotherEarningDeduction.month', '=', $month)
                    ->where('tblotherEarningDeduction.year', '=', $year)
                    ->where('tblpayment_consolidated.bank', '=', $bank)
                    ->where('tblpayment_consolidated.rank', '=', 2)
                    ->groupBY('tblotherEarningDeduction.staffid')
                    ->where('tblotherEarningDeduction.divisionID', $division ? '=' : '<>', $division)
                    ->get();
            }
        } else {
            if ($bank == '') {
                $data['reportTitle'] = DB::table('tbladmincode')->where('determinant', '=', $type)->select('addressName as desc')->first();
                $data['payment'] = DB::table('tblpayment_consolidated')->select('name as fullname', "$rtype as amt", 'PEC')->where('month', '=', $month)
                    ->where('year', '=', $year)
                    ->where('rank', '=', 2)
                    ->where('divisionID', $division ? '=' : '<>', $division)
                    ->get();
            } else {
                $data['reportTitle'] = DB::table('tbladmincode')->where('determinant', '=', $type)->select('addressName as desc')->first();
                $data['payment'] = DB::table('tblpayment_consolidated')->select('name as fullname', "$rtype as amt", 'PEC')->where('month', '=', $month)->where('year', '=', $year)
                    ->where('rank', '=', 2)->where('bank', '=', $bank)
                    ->where('divisionID', $division ? '=' : '<>', $division)->get();
            }

            if ($working_state) {
                if ($bank == '') {
                    $data['reportTitle'] = DB::table('tbladmincode')->where('determinant', '=', $type)->select('addressName as desc')->first();
                    $data['payment'] = DB::table('tblpayment_consolidated')->select('name as fullname', "$rtype as amt", 'PEC')
                        ->where('tblpayment_consolidated.current_state', '=', $working_state)
                        ->where('month', '=', $month)
                        ->where('year', '=', $year)
                        ->where('tblpayment_consolidated.rank', '=', 2)
                        ->where('divisionID', $division ? '=' : '<>', $division)
                        ->get();
                } else {
                    $data['reportTitle'] = DB::table('tbladmincode')->where('determinant', '=', $type)->select('addressName as desc')->first();
                    $data['payment'] = DB::table('tblpayment_consolidated')->select('name as fullname', "$rtype as amt", 'PEC')
                        ->where('tblpayment_consolidated.current_state', '=', $working_state)
                        ->where('month', '=', $month)
                        ->where('year', '=', $year)
                        ->where('bank', '=', $bank)
                        ->where('tblpayment_consolidated.rank', '=', 2)
                        ->where('divisionID', $division ? '=' : '<>', $division)
                        ->get();
                }
            } else {
                return back()->with('err', 'Please select working state');
            }
        }
        $data['selectedYear'] = $year;
        $data['month'] = $month;

        if (is_null($data['payment']))
            return back()->with('msg', 'Record not found!');
        $noRecord = 0;
        $amount     = 0.0;
        $total = 0.00;
        $jusuTotal = 0.00;
        $totalUser  = 0;
        $data['payeAddress'] = $working_state . ' STATE INTERNAL REVENUE, ' . $working_state;
        foreach ($data['payment'] as $row) {
            $amount += ($row->amt);
            if (($amount <> 0)) {
                $totalUser   += 1;
                $total       += ($row->amt);
            }
            $data['record']   =  $row;
        } //end foreach
        if ($totalUser > 1)
            $userStatus = "and $totalUser others";
        else
            $userStatus = "";
        $data['getValue'] =  $rtype;
        $data['totalSum'] =  $total;
        $data['jusuSum']  = $jusuTotal;
        $data['details']  = $data['payment'];
        $data['getStatus']  =  $userStatus;
        if ($data['payment'] && $data['totalSum'] <> 0)
            return view('payroll.treasuryF1.tf1voucher', $data);
        else
            return back()->with('msg', 'Record not found/Empty Record!');
    }
    //end retrieve treasuryF1 justices

    public function curDivision($userId)
    {
        $currentDivision = DB::table("users")
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'users.divisionID')
            ->where('users.id', '=', $userId)
            ->select('tbldivision.division', 'tbldivision.divisionID')
            ->first();
        return $currentDivision;
    }
}
