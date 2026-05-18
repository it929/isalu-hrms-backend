<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

//gazetting status;
// 1 = retired, 2 = demoted, 3 = desconded, 4 = transferred, 
//5 = died, 6 =resigned, 7 = condolation, 8= merger service, 9 = new appointment 

class GazetteController extends Controller
{
    public function index()
    {
        $data['gazetteStatus'] = DB::table('gazette_status')->get();
        return view('gazette.getStaff', $data);
    }

    public function generateGazette(Request $request)
    {
        $stat = $request->gazetteStatus;
        $request->validate([
            'fileNo' => 'required',
            'gazetteStatus' => 'required'
        ]);

        if ($request->gazetteStatus == 9) {
            try {
                $data['staff'] = DB::table('tblper')
                    ->join('tbldesignation', 'tbldesignation.id', 'tblper.Designation')
                    ->where('tblper.ID', $request->fileNo)
                    // ->where('tblper.progress_regID', 17)
                    ->select('tblper.*', 'tbldesignation.designation as staffDesignation')
                    ->first();
                $data['salary'] = DB::table('basicsalaryconsolidated')->where(['grade' => $data['staff']->grade, 'step' => $data['staff']->step])->first();
                $data['gazetteStatus'] = $stat;
                if (!$data['staff']) {
                    return back()->with('error', 'Staff does not exist or Not eligible for Gazetting');
                }

                return view('gazette.newAppointment', $data);
            } catch (\Throwable $th) {
                //throw $th;
                return back()->with('error', 'Something went wrong');
            }
        }

        if ($request->gazetteStatus == 2 || $request->gazetteStatus == 3 || $request->gazetteStatus == 4 || $request->gazetteStatus == 7 || $request->gazetteStatus == 8) {
            try {
                $data['staff'] = DB::table('tblper')
                    ->join('tbldepartment', 'tbldepartment.id', 'tblper.department')
                    ->where('tblper.ID', $request->fileNo)
                    // ->where('tblper.progress_regID', 17)
                    ->select('tblper.*', 'tbldepartment.department as staffDept')
                    ->first();
                $data['gazetteStatus'] = DB::table('gazette_status')->where('id', $stat)->first();
                if (!$data['staff']) {
                    return back()->with('error', 'Staff does not exist or Not eligible for Gazetting');
                }

                return view('gazette.staffStatus', $data);
            } catch (\Throwable $th) {
                //throw $th;
                return back()->with('error', 'Something went wrong');
            }
        }

        if($request->gazetteStatus == 1){ //for retired
            try {
                $data['staff'] = DB::table('tblper')
                    ->join('tbldepartment', 'tbldepartment.id', 'tblper.department')
                    ->where('tblper.ID', $request->fileNo)
                    ->where('tblper.is_retired', 1)
                    ->select('tblper.*', 'tbldepartment.department as staffDept')
                    ->first();
                $data['gazetteStatus'] = DB::table('gazette_status')->where('id', $stat)->first();
                if (!$data['staff']) {
                    return back()->with('error', 'Staff cannot be Gazetted for retirement, Please make sure status has been updated');
                }

                return view('gazette.staffStatus', $data);
            } catch (\Throwable $th) {
                //throw $th;
                return back()->with('error', 'Something went wrong');
            }
        }

        if($request->gazetteStatus == 6){ //for resigned
            try {
                $data['staff'] = DB::table('tblper')
                    ->join('tbldepartment', 'tbldepartment.id', 'tblper.department')
                    ->where('tblper.ID', $request->fileNo)
                    ->where('tblper.status_value', 'resignation')
                    ->select('tblper.*', 'tbldepartment.department as staffDept')
                    ->first();
                $data['gazetteStatus'] = DB::table('gazette_status')->where('id', $stat)->first();
                if (!$data['staff']) {
                    return back()->with('error', 'Please make update staff status to enable you Gazette for resignation');
                }

                return view('gazette.staffStatus', $data);
            } catch (\Throwable $th) {
                //throw $th;
                return back()->with('error', 'Something went wrong');
            }
        }

        if($request->gazetteStatus == 5){ //for died/deceased
            try {
                $data['staff'] = DB::table('tblper')
                    ->join('tbldepartment', 'tbldepartment.id', 'tblper.department')
                    ->where('tblper.ID', $request->fileNo)
                    ->where('tblper.status_value', 'deceased')
                    ->select('tblper.*', 'tbldepartment.department as staffDept')
                    ->first();
                $data['gazetteStatus'] = DB::table('gazette_status')->where('id', $stat)->first();
                if (!$data['staff']) {
                    return back()->with('error', 'Staff cannot be Gazetted as deceased, Make sure status has been updated');
                }

                return view('gazette.staffStatus', $data);
            } catch (\Throwable $th) {
                //throw $th;
                return back()->with('error', 'Something went wrong');
            }
        }

    }

    public function gazetteStaff(Request $request)
    {
        $r = 0;
        if($request->gazetteStatus == 9){
            $r = 9;
        }

        if(DB::table('gazettedstaff')->where(['fileNo' => $request->fileNo, 'gazetteID' => $r])->exists()){
            
            return redirect(url('gazette-new-staff'))->with('error', 'Staff already gazetted');

        }elseif(DB::table('gazettedstaff')->where('fileNo',$request->fileNo)->where('gazetteID', 1)->orwhere('gazetteID', 5)->orwhere('gazetteID', 6)->exists()){

            return redirect(url('gazette-new-staff'))->with('error', 'Staff cannot be gazetted');            
        
        }else{

            $createGazette = DB::table('gazettedstaff')->insert([
                'fileNo' => $request->fileNo,
                'gazetteID' => $request->gazetteStatus,
                'date_gazetted' => date('Y-m-d')
            ]);
            if ($createGazette) {
                return redirect(url('gazette-new-staff'))->with('success', 'Gazetting was successfull');
            }

        }

    }

    public function officialManuscript()
    {
        $data['promoted'] = DB::table('tblstaffpromotion_shortlist')
                        ->join('tblper', 'tblper.ID', 'tblstaffpromotion_shortlist.staffid')
                        ->join('tbldesignation', 'tbldesignation.id', 'tblstaffpromotion_shortlist.post_sought')
                        ->where('tblstaffpromotion_shortlist.is_gazetted', 0)
                        ->where('tblstaffpromotion_shortlist.confirmed_promoted','=',1)
                        ->select('tblstaffpromotion_shortlist.*', 'tblper.*', 'tbldesignation.designation as currentRank')
                        ->get();
        return view('gazette.officialManuscript', $data);
    }

    public function saveGazettedPromoted(Request $request)
    {
        for ($i = 0; $i < count($request->staffid); $i++) {
            $answers[] = [
                'staffid' => $request->staffid[$i],
                'promoted_rank' => $request->rank[$i],
                'upgrade_advance_date' => $request->upgradeAdvanceDate[$i],
                'date_gazetted' => date('d-m-Y')
                
            ];
            DB::table('tblstaffpromotion_shortlist')->where('staffid', $request->staffid[$i])->update(['is_gazetted' => 1]);
        }
        DB::table('gazette_promoted')->insert($answers);
        

        return back()->with('message', 'you have successfully gazetted promoted staffs');
    }

    public function searchGazetted()
    {
        return view('gazette.searchGazette');
    }

    public function showGazetted(Request $request)
    {
        $fileNo = $request->fileNo;
        $data['staffName'] = DB::table('tblper')->where('ID', $fileNo)->first();
        $data['gazette'] = DB::table('gazettedstaff')
                    ->join('gazette_status', 'gazette_status.id', 'gazettedstaff.gazetteID')
                    ->where('gazettedstaff.fileNo', $fileNo)
                    ->select('gazette_status.status_name as gazetteName', 'gazettedstaff.date_gazetted as dateGazetted')
                    ->orderBy('gazettedstaff.date_gazetted', 'DESC')   
                    ->get();

        return view('gazette.showGazetted', $data);
    }

}
