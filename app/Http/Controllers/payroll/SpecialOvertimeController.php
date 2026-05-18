<?php

namespace App\Http\Controllers\payroll;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use Session;
class SpecialOvertimeController extends functions22Controller
{

   public function index()
   {
       return view('specialOvertime.index');
   }
   
   public function report(Request $request)
   {
       $data['OTReport'] = DB::table('tblseparate_special_overtime')
       ->join('tblper','tblper.ID','=','tblseparate_special_overtime.staffid')
       ->where('year','=',$request->year)->where('month','=',$request->month)->orderby('tblseparate_special_overtime.grade', 'desc')->get();
    //    dd($data);
       return view('specialOvertime.report',$data);
   }
   
    public function indexMandate()
   {
       return view('specialOvertime.indexmandate');
   }
   
   public function mandateReport(Request $request)
   {
       $month = $request->month;
       $year = $request->year;
       $data['bat'] = DB::table('tblcouncil_bat')
           ->where('year', $year)
           ->where('month', $month)
           ->first();
       $data['epayment_detail'] = DB::table('tblseparate_special_overtime')
       ->join('tblper','tblper.ID','=','tblseparate_special_overtime.staffid')
       ->join('tblbanklist','tblper.bankID','=','tblbanklist.bankID')
       ->where('year','=',$request->year)->where('month','=',$request->month)->orderBy('tblbanklist.bank')->orderby('tblseparate_special_overtime.grade', 'desc')->get();
       
       $sumGross = DB::table('tblseparate_special_overtime')
       ->join('tblper','tblper.ID','=','tblseparate_special_overtime.staffid')
       ->join('tblbanklist','tblper.bankID','=','tblbanklist.bankID')
       ->where('year','=',$request->year)->where('month','=',$request->month)->sum('gross');
       $sumTax = DB::table('tblseparate_special_overtime')
       ->join('tblper','tblper.ID','=','tblseparate_special_overtime.staffid')
       ->join('tblbanklist','tblper.bankID','=','tblbanklist.bankID')
       ->where('year','=',$request->year)->where('month','=',$request->month)->sum('tax');
       $data['totalNet'] = $sumGross - $sumTax;
       
       $data['month'] = $request->month;
       $data['year'] = $request->year;
       //dd($data['month']);
       return view('specialOvertime.mandateReport',$data);
   }
    public function indexTax()
   {
       return view('specialOvertime.indexTax');
   }
   
   public function taxReport(Request $request)
   {
       $data['OTReport'] = DB::table('tblseparate_special_overtime')
       ->join('tblper','tblper.ID','=','tblseparate_special_overtime.staffid')
       ->where('year','=',$request->year)->where('month','=',$request->month)->get();
       return view('specialOvertime.taxReport',$data);
   }
}