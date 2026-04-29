<?php

namespace App\Http\Controllers\payroll;

use App\Http\Requests;
use Illuminate\Http\Request;
use Carbon\Carbon;

//use \DateTime;
use App\Services\Formatter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;

class HalfPayStaffController extends ParentController
{


    public function view(Request $request)
    {
        // dd("here");
        $data['staff'] = '';
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }
        //dd($data['CourtInfo']);
        // if (count($data['CourtInfo']) > 0) {
        $data['staffData'] = DB::table('tblper')->where('courtID', '=', $data['CourtInfo']->courtid)->orderBy('surname')->get();
        // }

        $divisionsession = session('divsession');
        $data['division'] =  DB::table('tbldivision')->where('courtID', '=', $request['court'])->get();
        $data['court'] = DB::table('tbl_court')->get();
        $data['staffList'] = DB::table('tblper')
            ->where('courtID', '=', $request['court'])
            ->where('divisionID', '=', $request['division'])->orderBy('surname')->get();

        $data['staff'] = DB::table('tblper')
            ->join('tblemployment_type', 'tblemployment_type.id', '=', 'tblper.employee_type')
            ->where('tblper.ID', '=', $request['fileNo'])->orderBy('surname')->first();
        // dd($data['staff']);
        if (isset($_POST['add'])) {
            $this->validate($request, [
                'dueDate' => 'required|date',
            ]);

            if (DB::table('half_pay_staff')
                ->where('staffid', '=', $request['fileNo'])->first()
            ) {
                return back()->with('err', 'This staff already entered as half pay');
            }
            $data['PayrollActivePeriod'] = $this->PayrollActivePeriod(9);

            $insert = DB::table('half_pay_staff')->insert(array(
                'staffid'                 => $request['fileNo'],
                'fileNo'      => $data['staff']->fileNo,
                'courtID'                => $data['staff']->courtID,
                'old_grade'              => $request->newGrade,
                'old_step'               => $request->newStep,
                'due_date'               => $request['dueDate'],
                'month_payment'          => $data['PayrollActivePeriod']->month,
                'year_payment'           => $data['PayrollActivePeriod']->year,
                // 'payment_status'          => 0,
            ));

            DB::table('tblper')->where('ID', $request['fileNo'])->update([
                'grade' => $request->newGrade,
                'step'   => $request->newStep,
                // 'staff_status' => 0,
            ]);

            if ($insert) {
                return redirect('/staff-half-pay/arrears')->with('msg', 'Successfully Entered');
            } else {
                return redirect('/staff-half-pay/arrears')->with('err', 'Record could not be saved');
            }
        }

        // $data['employmentType'] = DB::table('tblemployment_type')->where('active', 1)->get();
        $data['employmentType'] = DB::table('hr_employment_type')->get();

        $data['BankList'] = DB::Select("SELECT * FROM `tblbanklist`");
        $data['staffForHalfPayList'] = DB::table('half_pay_staff')
            ->join('tblper', 'tblper.ID', '=', 'half_pay_staff.staffid')
            ->orderBy('half_pay_staff.id', 'desc')
            ->select('half_pay_staff.*', 'tblper.bankID', 'tblper.bankGroup', 'tblper.bank_branch', 'tblper.AccNo', 'tblper.surname', 'tblper.first_name', 'tblper.othernames', 'tblper.employee_type')
            ->get();

        return view('payroll/dueForArrears/staffhalfpaycreate2', $data);
    }

    public function approveNewStaffSalary(Request $request)
    {
        $data['PayrollActivePeriod'] = $this->PayrollActivePeriod(9);
        // dd($request->all());

        $staffDetails = DB::table('tblper')->where('ID', $request->staffId)->first();

        // If employmentType is selected, use it; otherwise keep old one
        $employmentType = $request->employmentType ?: $staffDetails->employee_type;

        //is current active month same as the month captured on half_pay_documentation
        $getHalfPay = DB::table('half_pay_staff')->where('staffid', $request->staffId)->first();
        $isSalaryLock = DB::table('tblpayment_consolidated')->where('year', $data['PayrollActivePeriod']->year)->where('divisionID', 1)->where('rank', '!=', 2)->where('month', $data['PayrollActivePeriod']->month)->first();

        // determine incremental_date (always january/july of NEXT year)
        $incrementalDate = null;
        if (!empty($staffDetails->date_present_appointment)) {
            try {
                $appt = Carbon::parse($staffDetails->date_present_appointment);
                $nextYear = $appt->year + 1;
                // appointment on or before June 30 => Jan 1 of next year
                // appointment after June 30 => Jul 1 of next year
                // if ($appt->month < 7 || ($appt->month == 6 && $appt->day <= 30)) {
                //     $incrementalDate = Carbon::create($nextYear, 1, 1)->toDateString();
                // } else {
                $incrementalDate = Carbon::create($nextYear, $appt->month, 1)->toDateString();
                // }
            } catch (\Exception $e) {
                $incrementalDate = null;
            }
        }

        $dueDate = Carbon::parse($getHalfPay->due_date);
        $dueYear  = $dueDate->year;
        $dueMonthText = strtoupper($dueDate->format('F'));
        // if ($data['PayrollActivePeriod']->year == $getHalfPay->year_payment && $data['PayrollActivePeriod']->month == $getHalfPay->month_payment) {
        if ($data['PayrollActivePeriod']->year == $dueYear && $data['PayrollActivePeriod']->month == $dueMonthText) {
            if ($isSalaryLock == '') {
                DB::table('half_pay_staff')->where('staffid', $request->staffId)->update([
                    'payment_status' => 0,
                    'approvedBy' => Auth::user()->id,
                    'approvedDate' => Carbon::now(),
                    'arrears_activation' => 0,
                ]);
                //update per with bankgroup and bankbranch
                DB::table('tblper')->where('ID', $request->staffId)->update([
                    'bankID' => $request->bankName,
                    'AccNo' => $request->accountNumber,
                    'bank_branch' => $request->bank_branch,
                    // 'bankGroup' => $request->bankGroup,
                    'incremental_date' => $incrementalDate,
                    'staff_status' => 1,
                    'status_value' => 'active service',
                    'employee_type' => $employmentType,
                ]);
                return back()->with('message', 'Salary has been approved successfully');
            } else if ($isSalaryLock != '' && $isSalaryLock->salary_lock == 0) {
                DB::table('half_pay_staff')->where('staffid', $request->staffId)->update([
                    'payment_status' => 0,
                    'approvedBy' => Auth::user()->id,
                    'approvedDate' => Carbon::now(),
                    'arrears_activation' => 0,
                ]);
                //update per with bankgroup and bankbranch
                DB::table('tblper')->where('ID', $request->staffId)->update([
                    'bankID' => $request->bankName,
                    'AccNo' => $request->accountNumber,
                    'bank_branch' => $request->bank_branch,
                    // 'bankGroup' => $request->bankGroup,
                    'incremental_date' => $incrementalDate,
                    'staff_status' => 1,
                    'status_value' => 'active service',
                    'employee_type' => $employmentType,
                ]);
                return back()->with('message', 'Salary has been approved successfully');
            } else if ($isSalaryLock != '' && $isSalaryLock->salary_lock == 1) {
                return back()->with('error_message', 'The Selected period has been locked! 1');
            } else {
                return back()->with('error_message', 'The Selected period has been locked!');
            }
        } else {
            if ($isSalaryLock == '') {
                DB::table('tblstaff_for_arrears')->insertGetId([
                    'staffid' => $request->staffId,
                    //   'newEmploymentType' => $staffDetails->employee_type,
                    //   'oldEmploymentType' => $staffDetails->employee_type,
                    'newEmploymentType' => $employmentType,
                    'oldEmploymentType' => $staffDetails->employee_type,
                    'courtID' => 9,
                    'old_grade' => 0,
                    'old_step' => 0,
                    'new_grade' => $staffDetails->grade,
                    'new_step' => $staffDetails->step,
                    'due_date' => $getHalfPay->due_date,
                    'divisionID' => $staffDetails->divisionID,
                    'arrears_type' => "New Appointment",
                    'payment_status' => 0,
                ]);
                DB::table('half_pay_staff')->where('staffid', $request->staffId)->update([
                    'year_payment' => $data['PayrollActivePeriod']->year,
                    'month_payment' => $data['PayrollActivePeriod']->month,
                    'payment_status' => 1,
                    'approvedBy' => Auth::user()->id,
                    'approvedDate' => Carbon::now(),
                ]);
                DB::table('tblper')->where('ID', $request->staffId)->update([
                    'bankID' => $request->bankName,
                    'AccNo' => $request->accountNumber,
                    'bank_branch' => $request->bank_branch,
                    // 'bankGroup' => $request->bankGroup,
                    'incremental_date' => $incrementalDate,
                    'staff_status' => 1,
                    'status_value' => 'active service',
                    'employee_type' => $employmentType,
                ]);
                return back()->with('message', 'Salary has been approved as arrears successfully');
            } else if ($isSalaryLock != '' && $isSalaryLock->salary_lock == 0) {
                DB::table('tblstaff_for_arrears')->insertGetId([
                    'staffid' => $request->staffId,
                    //   'newEmploymentType' => $staffDetails->employee_type,
                    //   'oldEmploymentType' => $staffDetails->employee_type,
                    'newEmploymentType' => $employmentType,
                    'oldEmploymentType' => $staffDetails->employee_type,
                    'courtID' => 9,
                    'old_grade' => 0,
                    'old_step' => 0,
                    'new_grade' => $staffDetails->grade,
                    'new_step' => $staffDetails->step,
                    'due_date' => $getHalfPay->due_date,
                    'divisionID' => $staffDetails->divisionID,
                    'arrears_type' => "New Appointment",
                    'payment_status' => 0,
                ]);
                DB::table('half_pay_staff')->where('staffid', $request->staffId)->update([
                    'year_payment' => $data['PayrollActivePeriod']->year,
                    'month_payment' => $data['PayrollActivePeriod']->month,
                    'payment_status' => 1,
                    'approvedBy' => Auth::user()->id,
                    'approvedDate' => Carbon::now(),
                ]);
                DB::table('tblper')->where('ID', $request->staffId)->update([
                    'bankID' => $request->bankName,
                    'AccNo' => $request->accountNumber,
                    'bank_branch' => $request->bank_branch,
                    // 'bankGroup' => $request->bankGroup,
                    'incremental_date' => $incrementalDate,
                    'staff_status' => 1,
                    'status_value' => 'active service',
                    'employee_type' => $employmentType,
                ]);
                return back()->with('message', 'Salary has been approved as arrears successfully');
            } else if ($isSalaryLock != '' && $isSalaryLock->salary_lock == 1) {
                return back()->with('error_message', 'The Selected period has been locked! 1');
            } else {
                return back()->with('error_message', 'The Selected period has been locked! 2');
            }
        }
        //if true, check if salary has not been locked
        //if the two conditions are true proceed and approve

        //else do not approve
        //take it to staff_arrears_table before approving and updating half pay with current month and payment status as 1
    }

    public function PayrollActivePeriod($court)
    {
        return DB::table('tblactivemonth')->first();
    }
}
