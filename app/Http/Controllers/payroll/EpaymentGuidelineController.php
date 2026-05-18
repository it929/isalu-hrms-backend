<?php

namespace App\Http\Controllers\payroll;



use Illuminate\Http\Request;

use App\Http\Requests;

use Illuminate\Support\Str;
use Fpdf;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class EpaymentGuidelineController extends Controller
{

    public function CourtInfo()
    {
        $List = DB::Select("SELECT * FROM `tblsole_court`");

        // Ensure there's at least one record before accessing index 0
        return count($List) > 0 ? $List[0] : null;
        // return $List[0];
    }

    public function curDivision($userId)
    {
        $currentDivision = DB::table("users")
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'users.divisionID')
            ->where('users.id', '=', $userId)
            ->select('tbldivision.division', 'tbldivision.divisionID')
            ->first();
        return $currentDivision;
    }

    public function index()
    {

        $data['courts'] =  DB::table('tbl_court')->get();
        $courtSessionId = session('anycourt');
        $data['courtDivisions']  = DB::table('tbldivision')
            ->where('courtID', '=', $courtSessionId)
            ->get();

        $data['CourtInfo'] = $this->CourtInfo();

        $data['bank_name'] = '';
        $data['grandTotal'] = 0;
        $data['curr_date'] = '';
        $data['myDivisions'] = [];

        Session::forget('year');
        Session::forget('month');
        Session::forget('bankName');

        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        $data['courtDivisions']  = DB::table('tbldivision')->get();

        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        if ($data['CourtInfo']) {
            $data['allbanklist']  = DB::table('tblbanklist')->get();
        }

        return view("payroll.epayment.guideline.index", $data);
    }

    public function Retrieve(Request $request)
    {

        ///////////////////////////// start to get all bank //////////////////////////////////
        $data['courts'] =  DB::table('tbl_court')->get();

        $courtSessionId = session('anycourt');
        $data['courtDivisions']  = DB::table('tbldivision')
            ->where('courtID', '=', $courtSessionId)
            ->get();

        $data['CourtInfo'] = $this->CourtInfo();
        // dd($data);
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        $data['courtDivisions']  = DB::table('tbldivision')->get();

        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        // if (count($data['CourtInfo']) > 0) {
        //     $data['allbanklist']  = DB::table('tblbanklist')->get();
        // }

        if ($data['CourtInfo'] !== null) { // Instead of count()
            $data['allbanklist']  = DB::table('tblbanklist')->get();
        }


        ///////////////////////////// end to get all bank //////////////////////////////////

        $data['year']    =   $request->input('year');
        $data['month']   =   $request->input('month');
        $data['bankName']   =   $request->input('bankName');


        Session::put('year', $data['year']);
        Session::put('month', $data['month']);
        Session::put('bankName',  $data['bankName']);

        $data['division'] = DB::table('tbldivision')->get();
        // dd($data['bankName']);
        if ($data['bankName'] == "") {
            # code...
            $data['myDivisions'] = DB::table('tblpayment_consolidated')
                ->leftjoin('tbldivision', 'tblpayment_consolidated.divisionID', '=', 'tbldivision.divisionID')
                ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
                ->where('tblpayment_consolidated.month', $data['month'])
                ->where('tblpayment_consolidated.year', $data['year'])
                // ->where('tblpayment_consolidated.bank',$data['bankName'])
                ->where('tblpayment_consolidated.employment_type', '!=', 2)
                ->groupBy('tblpayment_consolidated.divisionID')
                ->select("*", DB::raw("SUM(tblpayment_consolidated.NetPay) AS myEarn"))
                ->get();
        } else {
            $data['myDivisions'] = DB::table('tblpayment_consolidated')
                ->leftjoin('tbldivision', 'tblpayment_consolidated.divisionID', '=', 'tbldivision.divisionID')
                ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
                ->where('tblpayment_consolidated.month', $data['month'])
                ->where('tblpayment_consolidated.year', $data['year'])
                ->where('tblpayment_consolidated.bank', $data['bankName'])
                ->where('tblpayment_consolidated.employment_type', '!=', 2)
                ->groupBy('tblpayment_consolidated.divisionID')
                ->select("*", DB::raw("SUM(tblpayment_consolidated.NetPay) AS myEarn"))
                ->get();
        }

        // dd($data['myDivisions']);

        $gTotals = 0;
        $bank_name = "";

        foreach ($data['myDivisions'] as $gTotal) {
            // dd($gTotal->myEarn);
            $gTotals += $gTotal->myEarn;
            $bank_name = $gTotal->bank;
        };

        // $data['grandTotal'] = money_format('%i', $gTotals);
        // $data['grandTotal'] = $gTotals;
        $data['grandTotal'] = number_format($gTotals, 2, '.', ',');
        if ($data['bankName'] == "") {
            $data['bank_name'] = "";
        } else {
            $data['bank_name'] = $bank_name;
        }

        $data['curr_date'] = Carbon::now()->format('d F, Y');

        // dd($data);
        return view("payroll.epayment.guideline.index", $data);
        // return redirect()->back()->with($data);
    }
}
