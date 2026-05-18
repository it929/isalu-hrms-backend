<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComputePercentIncreaseController extends Controller
{
    public function computeIndex()
    {
        $data['courtDivisions'] = DB::table('tbldivision')->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        $data['computedDivisions'] = DB::table('tblstaffCV')->where('cvID', 135)
            ->leftJoin('tbldivision', 'tbldivision.divisionID', '=', 'tblstaffCV.divisionID')
            ->groupBy('divisionID')
            ->select('tblstaffCV.divisionID', 'tbldivision.division')
            ->get();
        // dd($data['computedDivisions']);
        return view('computePercentageIncrease.index', $data);
    }

    public function runComputation(Request $request)
    {
        $this->validate($request, [
            'division' => 'required'
        ]);

        $staffs = DB::table('tblper')->where('staff_status', 1)->where('divisionID', $request['division'])->select('ID', 'grade', 'step', 'employee_type', 'divisionID')->get();
        // dd($staffs);
        //cvID of 40% Increment intblcvSetup = 135
        $cvId = DB::table('tblcvSetup')->where('ID', 135)->first();
        foreach ($staffs as $key => $value) {
            $consolidatedAmount = DB::table('basicsalaryconsolidated')
                                        ->where('employee_type', $value->employee_type)
                                        ->where('grade', $value->grade)
                                        ->where('step', $value->step)
                                        ->value('percent');
            if($consolidatedAmount == '' || $consolidatedAmount == null){
                return back()->with('err', 'Please Set Percentage Increase on Salary Setup');
            }else{
                if(DB::table('tblstaffCV')->where([
                    'divisionID' => $value->divisionID,
                    'staffid' => $value->ID,
                    'cvID' => $cvId->ID,
                ])->first()){
                    //update staff cv
                    DB::table('tblstaffCV')->where([
                        'divisionID' => $value->divisionID,
                        'staffid' => $value->ID,
                        'cvID' => $cvId->ID,
                    ])->update([
                        'courtID' => 9,
                        'divisionID' => $value->divisionID,
                        'staffid' => $value->ID,
                        'cvtype' => $cvId->particularID,
                        'cvID' => $cvId->ID,
                        'amount' => $consolidatedAmount,
                        'last_update' => date('Y-m-d'),
                        'status' =>  $cvId->status,
                        'recycling' => 1,
                    ]);
                }else{
                    //save to staff cv
                    DB::table('tblstaffCV')->insert(array(
                        'courtID' => 9,
                        'divisionID' => $value->divisionID,
                        'staffid' => $value->ID,
                        'cvtype' => $cvId->particularID,
                        'cvID' => $cvId->ID,
                        'amount' => $consolidatedAmount,
                        'last_update' => date('Y-m-d'),
                        'status' =>  $cvId->status,
                        'recycling' => 1,
                    ));
                }
            }
            
        }
        return back()->with('message', '40% Increment Successfully Computed');
    }

    public function removePercentageIncrement(Request $request)
    {
        $cvId = DB::table('tblcvSetup')->where('ID', 135)->first();
        if(DB::table('tblstaffCV')->where('divisionID', $request['division'])->where('cvID', $cvId->ID)->delete()){
            return back()->with('message', 'Undo Computation Percentage successfull');
        }else{
            return back()->with('err', 'Sorry! Error occured...');
        }
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
}
