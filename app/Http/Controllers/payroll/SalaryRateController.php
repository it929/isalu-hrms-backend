<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
class SalaryRateController extends Controller
{
    public function index()
    {
        $data['salaryRates'] = DB::table('tblsalaryfunction')->get();
        //dd($data);
        return view('SalaryRate.salaryRateFunction', $data);
    }
    
    public function edit(Request $request)
    {
        $this->validate($request,[
           'rateChange' =>'required|numeric'
        ]);

        $rate = $request->input('rateChange');
        $id = $request->input('salaryid');
        //dd($id);
        DB::table('tblsalaryfunction')->where('id',$id)->update(['rate'=>$rate]);

        return redirect('/salary-rate')->with('message', 'Rate Edited');

    }
}
