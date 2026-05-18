<?php

namespace App\Http\Controllers\payroll;

use App\Http\Requests;
use Auth;
use Session;
use Illuminate\Http\Request;

use DB;

class NHISController extends ParentController
{

    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function create()
    {
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        $data['nhis'] = DB::table('tblnhisbalances')->get();
        $data['courtDivisions']  = DB::table('tbldivision')
            //  ->where('courtID', '=', $courtSessionId)
            ->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        // dd($data);
        return view('nhis.create', $data);
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

    public function store(Request $request)
    {
        $this->validate($request, [
            'year' => 'required|numeric',
            'month' => 'required',
            'amount' => 'required|numeric',
            'purpose' => 'required',
        ]);

        //dd('ok');

        $data = DB::table('tblnhisbalances')->insert(
            [

                'year'                   => $request['year'],
                'month'                 => $request['month'],
                'amount'                   => $request['amount'],
                'purpose'                 => $request['purpose'],
                'updated_at'            => date('Y-m-d'),

            ]
        );

        return redirect('/nhis-balance/create')->with('msg', 'Successfully Added');
    }

    public function edit($id)
    {
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }


        $data['nhis'] = DB::table('tblnhisbalances')->get();
        $data['courtDivisions']  = DB::table('tbldivision')
            //  ->where('courtID', '=', $courtSessionId)
            ->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        $data['nhis'] = DB::table('tblnhisbalances')->where('id', '=', $id)->first();
        $data['nhisBal'] = DB::table('tblnhisbalances')->get();

        //  dd($data);
        return view('nhis.edit', $data);
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'year' => 'required|numeric',
            'month' => 'required',
            'amount' => 'required|numeric',
            'purpose' => 'required',
        ]);

        //dd('ok');

        $data = DB::table('tblnhisbalances')->where('id', '=', $request['id'])->update(
            [

                'year'                   => $request['year'],
                'month'                 => $request['month'],
                'amount'                   => $request['amount'],
                'purpose'                 => $request['purpose'],
                'updated_at'            => date('Y-m-d'),

            ]
        );

        return redirect('/nhis-balance/edit/' . $request['id'])->with('msg', 'Successfully Updated');
    }
    public function createAccount()
    {
        $data['nhis'] = DB::table('tblnhis_account')->get();
        $data['nhisAcct'] = DB::table('tblnhis_account')->first();
        $data['alhisanAcct'] = DB::table('tblalhisan_accounts')->first();
        $data['banks'] = DB::table('tblbanklist')->get();
        return view('nhis.nhisAccount', $data);
    }
    public function storeAccount(Request $request)
    {
        $acct = $request['account'];
        $nhis = DB::table('tblnhis_account')->count();
        if ($nhis == 0) {
            DB::table('tblnhis_account')->insert(
                [

                    'accountNo'                   => $request['account'],
                    'updated_at'                => date('Y-m-d'),

                ]
            );
            return back()->with('msg', 'Successfully Saved');
        } else {
            DB::table('tblnhis_account')->where('id', '=', 1)->update(
                [

                    'accountNo'                   => $request['account'],
                    'updated_at'                => date('Y-m-d'),

                ]
            );
            return back()->with('msg', 'Successfully Updated');
        }
    }

    public function deduction()
    {
        return view('nhis.nhisDeduction');
    }
    public function viewDeduction(Request $request)
    {
        $month = $request['month'];
        $year = $request['year'];
        $month_number = date("n", strtotime($month));
        if (strlen($month_number) == 1) $month_number = '0' . $month_number;

        $period = $year . '-' . $month_number . '-01';
        $percentages = DB::SELECT("SELECT * FROM `tbldeduction_percentage`")[0];
        $data['selectedMonth'] = $request['month'];
        $data['selectedYear'] = $request['year'];
        $data['nhisNew'] = round((DB::table('tblpayment_consolidated')->where('year', '=', $request['year'])->where('month', '=', $request['month'])->where('rank', '!=', 2)->sum('TEarn') -
            DB::table('tblpayment_consolidated')->where('year', '=', $request['year'])->where('month', '=', $request['month'])->where('rank', '!=', 2)->sum('PEC') -
            DB::table('tblpayment_consolidated')->where('year', '=', $request['year'])->where('month', '=', $request['month'])->where('rank', '!=', 2)->sum('SOT'))

            * $percentages->nhis * 0.01, 2);
        if ($period < '2021-07-01') {
            $data['nhis'] = DB::table('tblpayment_consolidated')->where('rank', '!=', 2)->where('year', '=', $request['year'])->where('month', '=', $request['month'])->orderBy('rank', 'DESC')->orderBy('grade', 'DESC')->orderBy('step', 'DESC')->get();
        } else {
            $data['nhis'] = DB::table('tblpayment_consolidated')->where('rank', '!=', 2)->where('staffid', '!=', 385)->where('year', '=', $request['year'])->where('month', '=', $request['month'])->orderBy('rank', 'DESC')->orderBy('grade', 'DESC')->orderBy('step', 'DESC')->get();

            //dd($data['nhis']);
        }
        $data['exceptThisGross'] = DB::table('tblpayment_consolidated')->where('employment_type', '=', 7)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('gross');
        $data['exceptThisSOT'] = DB::table('tblpayment_consolidated')->where('employment_type', '=', 7)->where('year', '=', $year)->where('month', '=', $month)->where('rank', '!=', 2)->sum('SOT');


        if ($period < '2021-07-01') {
            //dd("$period>'2021-08-01'");
            return view('nhis.nhisDeductionReportminusjusu', $data);
        }
        //dd(" not $period>'2021-08-01'");
        return view('nhis.nhisDeductionReport', $data);
    }

    public function storeAlhisanAccount(Request $request)
    {
        $acct = $request['account'];
        $bank = $request['bank'];
        $nhis = DB::table('tblalhisan_accounts')->count();
        if ($nhis == 0) {
            DB::table('tblalhisan_accounts')->insert(
                [

                    'account_no'                   => $request['alhisanAccount'],
                    'bank_name'                   => $request['bank'],
                    'updated_at'                => date('Y-m-d'),

                ]
            );
            return back()->with('msg', 'Successfully Saved');
        } else {
            DB::table('tblalhisan_accounts')->where('id', '=', 1)->update(
                [

                    'account_no'                   => $request['alhisanAccount'],
                    'bank_name'                   => $request['bank'],
                    'updated_at'                => date('Y-m-d'),

                ]
            );
            return back()->with('msg', 'Successfully Updated');
        }
    }
}
