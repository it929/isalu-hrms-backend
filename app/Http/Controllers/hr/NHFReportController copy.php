<?php

namespace App\Http\Controllers\hr;

use Illuminate\Http\Request;

use App\Http\Requests;
use Carbon\Carbon;
use Session;
use DateTime;
use Auth;
use Illuminate\Support\Facades\DB;

class NHFReportController extends ParentController
{

  public function index()
  {
    $data['CourtInfo'] = $this->CourtInfo();
    if ($data['CourtInfo']->courtstatus == 0) {
      $request['court'] = $data['CourtInfo']->courtid;
    }
    if ($data['CourtInfo']->divisionstatus == 0) {
      $request['division'] = $data['CourtInfo']->divisionid;
    }
    $data['courts'] =  DB::table('tbl_court')->get();
    return view('hr.nhfReport.nhfReportForm', $data);
  }

  public function retrieve(Request $request)
  {
    $data['nhf'] = DB::table('tblpayment_consolidated')
      ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
      ->where('tblpayment_consolidated.month', '=', $request['month'])
      ->where('tblpayment_consolidated.year', '=', $request['year'])
      ->where('tblpayment_consolidated.rank', '!=', 2)
      ->where('tblpayment_consolidated.NHF', '!=', 0)
      ->orderBy('tblpayment_consolidated.NHF', 'DESC')
      //->orderBy('tblpayment_consolidated.grade','DESC')
      ->get();
    $data['year'] = $request['year'];
    $data['month'] = $request['month'];

    $data['totalSum'] = DB::table('tblpayment_consolidated')
      ->where('tblpayment_consolidated.month', '=', $request['month'])
      ->where('tblpayment_consolidated.year', '=', $request['year'])
      ->where('tblpayment_consolidated.rank', '!=', 2)
      ->where('tblpayment_consolidated.NHF', '!=', 0)
      ->sum('NHF');
    return view('hr.nhfReport.detail', $data);
  }

  public function staffList()
  {
    $data['nhfStaffList'] = DB::table('tblper')
      ->join('tbldepartment', 'tbldepartment.id', 'tblper.department')
      ->select('tblper.*', 'tbldepartment.department as Dept')
      ->paginate(50);
    return view('hr.nhfReport.nhfStaffList', $data);
  }

  public function editStaffNhfNo(Request $request)
  {
    // dd($request->fileNo);
    $data['nhfStaff'] = DB::table('tblper')
      ->join('tbldepartment', 'tbldepartment.id', 'tblper.department')
      ->where('tblper.ID', $request->fileNo)
      ->select('tblper.*', 'tbldepartment.department as Dept')
      ->first();

    return view('hr.nhfReport.editNhfNo', $data);
  }

  public function updateStaffNhfNo(Request $request, $id)
  {
    $request->validate([
      'nhfNo' => 'required'
    ]);
    $updateNhfNo = DB::table('tblper')
      ->where('tblper.ID', $id)
      ->update(['nhfNo' => $request->nhfNo]);
    if (!$updateNhfNo) {
      return back()->with('error_message', 'Nhf No. could not be updated');
    }
    return redirect('/nhf-staff-list')->with('message', 'You have successfully updated Nhf No.');
  }

  public function nhfStaffDeduction()
  {
    return view('nhfReport.nhfStaffDeduction');
  }

  public function staffMonthlyDeduction(Request $request)
  {
    $request->validate([
      'month' => 'required',
      'year'  => 'required'
    ]);

    $data['month'] = $request->month;
    $data['year'] = $request->year;
    $fileNo = $request->fileNo;

    if (empty($request->fileNo)) {
      $data['monthlyDeduction'] = DB::table('tblpayment_consolidated')
        ->join('tblper', 'tblper.ID', 'tblpayment_consolidated.staffid')
        ->join('tbldepartment', 'tbldepartment.id', 'tblper.department')
        ->where(['tblpayment_consolidated.year' => $data['year'], 'tblpayment_consolidated.month' => $data['month']])
        ->select('tblper.surname', 'tblper.first_name', 'tblper.othernames', 'tblper.ID as perID', 'tblpayment_consolidated.*', 'tbldepartment.department as Dept')
        ->orderBy('tblper.ID', 'ASC')
        ->paginate(50);
      return view('nhfReport.viewNhfStaffMonthlyDeduction', $data);
    } else {
      $data['monthlyDeduction'] = DB::table('tblpayment_consolidated')
        ->join('tblper', 'tblper.ID', 'tblpayment_consolidated.staffid')
        ->join('tbldepartment', 'tbldepartment.id', 'tblper.department')
        ->where(['tblpayment_consolidated.staffid' => $fileNo, 'tblpayment_consolidated.year' => $data['year'], 'tblpayment_consolidated.month' => $data['month']])
        ->select('tblper.surname', 'tblper.first_name', 'tblper.othernames', 'tblper.ID as perID', 'tblpayment_consolidated.*', 'tbldepartment.department as Dept')
        ->first();
      return view('nhfReport.viewNhfSingleStaffDeduction', $data);
    }
  }

  public function updateStaffMonthlyDeduction(Request $request, $staffid)
  {

    $request->validate([
      'deduction' => 'required'
    ]);

    $data['deduction'] = DB::table('tblpayment_consolidated')->where(['staffid' => $staffid, 'month' => $request->month, 'year' => $request->year])->update([
      'NHF' => $request->deduction
    ]);

    if ($data['deduction'] !== null) {
      return back()->with('message', 'successfully updated');
    }
  }
}
