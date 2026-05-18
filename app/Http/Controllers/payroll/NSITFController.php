<?php

namespace App\Http\Controllers\payroll;

use App\Http\Requests;
use App\Role;
use App\User;
use Auth;
use Entrust;
use App\Permission;
use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\Controller;
use Session;

class NSITFController extends ParentController
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

    public function index()
    {
        $data['courts'] =  DB::table('tbl_court')->get();
        $courtSessionId = session('anycourt');

        $data['courtDivisions']  = DB::table('tbldivision')
            ->where('courtID', '=', $courtSessionId)
            ->get();

        /* $data['allbanklist']  = DB::table('tblbanklist')
         ->orderBy('tblbanklist.bank', 'Asc')
         ->get();*/
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }


        $data['courtDivisions']  = DB::table('tbldivision')
            //  ->where('courtID', '=', $courtSessionId)
            ->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        if ($data['CourtInfo'] != '') {

            $data['allbanklist']  = DB::table('tblbanklist')


                ->get();
        }
        return view('payroll.NSITF.index', $data);
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


    public function view(Request $request)
    {
        $data['year'] = $request['year'];
        $data['month'] = $request['month'];
        $year = $request['year'];
        $month = $request['month'];

        if ($request['bankName'] == '') {
            $data['staff'] = DB::table('tblpayment_consolidated')
                ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                ->leftJoin('tblstates', 'tblstates.id', '=', 'tblper.stateID')
                ->leftJoin('lga', 'lga.lgaId', '=', 'tblper.lgaID')
                ->leftJoin('tbldesignation', 'tbldesignation.id', '=', 'tblper.Designation')
                ->where('tblpayment_consolidated.year', '=', $request['year'])
                ->where('tblpayment_consolidated.month', '=', $request['month'])
                // ->where('tblpayment_consolidated.staffid', '!=', 385)
                //->where('tblpayment_consolidated.courtID','=',$request['court'])
                //->where('tblpayment_consolidated.divisionID','=',$request['division'])
                ->where('tblpayment_consolidated.rank', '!=', 2)
                ->whereIn('tblpayment_consolidated.employment_type', [1, 3, 4])
                ->orderBy('tblpayment_consolidated.rank', 'DESC')
                ->orderBy('tblpayment_consolidated.grade', 'DESC')
                ->orderBy('tblpayment_consolidated.step', 'DESC')

                ->get();
            $data['exceptThisGross'] = DB::table('tblpayment_consolidated')->where('staffid', '=', 385)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('gross');
            $data['exceptThisSOT'] = DB::table('tblpayment_consolidated')->where('staffid', '=', 385)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('SOT');
        } else {
            $data['staff'] = DB::table('tblpayment_consolidated')
                ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                ->join('tblstates', 'tblstates.id', '=', 'tblper.stateID')
                ->join('lga', 'lga.lgaId', '=', 'tblper.lgaID')
                ->join('tbldesignation', 'tbldesignation.id', '=', 'tblper.Designation')
                ->where('tblpayment_consolidated.bank', '=', $request['bankName'])
                ->where('tblpayment_consolidated.year', '=', $request['year'])
                ->where('tblpayment_consolidated.month', '=', $request['month'])
                //->where('tblpayment_consolidated.courtID','=',$request['court'])
                //->where('tblpayment_consolidated.divisionID','=',$request['division'])
                ->where('tblpayment_consolidated.rank', '!=', 2)
                ->whereIn('tblpayment_consolidated.employment_type', [1, 3, 4])
                ->orderBy('tblpayment_consolidated.rank', 'DESC')
                ->orderBy('tblpayment_consolidated.grade', 'DESC')
                ->orderBy('tblpayment_consolidated.step', 'DESC')
                ->get();
            $data['exceptThisGross'] = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.bank', '=', $request['bankName'])->where('staffid', '=', 385)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('gross');
            $data['exceptThisSOT'] = DB::table('tblpayment_consolidated')->where('tblpayment_consolidated.bank', '=', $request['bankName'])->where('staffid', '=', 385)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('SOT');
        }

        return view('payroll.NSITF.list', $data);
    }
}
