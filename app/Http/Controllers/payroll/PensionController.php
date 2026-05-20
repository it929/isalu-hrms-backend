<?php

namespace App\Http\Controllers\payroll;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;

class PensionController extends ParentController
{
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->division    = $request->session()->get('division');
        $this->divisionID  = $request->session()->get('divisionID');
                return $next($request);
        });
    }  


    public function index()
    {
        $data['staffList'] = DB::table('tblper')
            ->where('tblper.divisionID', '=', $this->divisionID)
            ->where('tblper.staff_status', 1)
            ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
            ->orderBy('tblper.surname', 'ASC')
            ->get();
        $data['details'] = DB::table('division_registry')->get();
        $data['penmgr']  = DB::table('tblpension_manager')->where('tblpension_manager.active', 1)->get();
        return view('pension.create',$data);
    }


    public function create_PFA()
    {
        $data['getAllPFA'] = DB::table('tblpension_manager')
            ->where('active', 1)
            ->where('divisionID', $this->divisionID)
            ->orderBy('created_at', 'ASC')
            ->paginate(20);
        $data['getEditID'] = '';
        $data['pensionManagerName'] = '';
        return view('pensionManager.create', $data);
    }

    public function view_edit_PFA($id = null)
    {
        $pmName = DB::table('tblpension_manager')->where('ID', $id)->first();
        $data['pensionManagerName'] = $pmName->pension_manager;
        $data['getAllPFA'] = DB::table('tblpension_manager')
            ->where('active', 1)
            ->where('divisionID', $this->divisionID)
            ->orderBy('created_at', 'ASC')
            ->paginate(20);
        $data['getEditID'] = $id;
        return view('pensionManager.create', $data);
    }

    
    public function store_PFA(Request $request)
    {
        $this->validate($request, 
        [
            'pensionManager'      => 'required|string',            
        ]);
        $pensionManager           = strtoupper(trim($request['pensionManager']));
        $pensionManagerID         = trim($request['pensionManagerID']);
        $date                     = date("Y-m-d H:s:i");
        if($pensionManagerID == '')
        {
            DB::table('tblpension_manager')->insert(array( 
                'pension_manager'     => $pensionManager, 
                'divisionID'          => $this->divisionID, 
                'created_at'          => $date,
                'updated_at'          => $date
            ));
            $this->addLog('New Pension Manager Added and division: '. $this->division);
            return redirect()->route('create_PFA')->with('msg', 'Operation was done successfully.');
        }
        else
        {
            if(DB::table('tblpension_manager')->where('ID', $pensionManagerID)->first())
            {
                DB::table('tblpension_manager')->where('ID', $pensionManagerID)->update(array( 
                    'pension_manager'     => $pensionManager, 
                    'updated_at'          => $date
                ));
                $this->addLog('Pension Manager updated with division: '. $this->division);
                return redirect()->route('create_PFA')->with('msg', 'Operation was done successfully.');
            }else{
                return back()->with('err', 'Record cannot be updated.  Details of this record cannot be located on our system !!!');
            }
            
        }
                
    }

    
    public function view_PFA()
    {
        $data['show'] = DB::table('tblpension_manager')->get();
        return view('pensionManager/list',$data);
    }



    //get staff details with JSON
    public function showAll(Request $request)
    {
        $fileNo = $request->input('nameID');
        DB::enableQueryLog();
        $data = DB::table('tblper')
            ->leftJoin('tbldivision', 'tblper.divisionID', '=', 'tbldivision.divisionID')
            ->where('tblper.fileNo', '=', $fileNo)
            ->select('fileNo','surname', 'first_name', 'othernames', 'fileNo', 'division','Designation','gender','grade','step','appointment_date','employee_type') 
            ->get();
        return response()->json($data);
    }


    public function getpension(Request $request)
    {
        $fileNo    = $request->input('ID');
        DB::enableQueryLog();
        $data    = DB::table('tblper')->where('tblper.fileNo', '=', $fileNo)->first();
        $pension = DB::table('basicsalary')
               ->where('grade', '=', $data->grade)
               ->where('step', '=', $data->step)
               ->where('employee_type', '=', strtoupper($data->employee_type))
               ->get();
        return response()->json($pension);
        
    }
    
    
    public function computePension(Request $request)
    {
        $this->validate($request, 
        [
            'remark'                          => 'string', 
            'month'                           => 'required|string',
            'year'                            => 'required|numeric',   
            'fileNo'                          => 'required',
            'grade'                           => 'required|numeric', 
            'step'                            => 'required|numeric',
            'employeeContribution'            => 'required|numeric',       
        ]);
        $fileno                               = trim($request['fileNo']);
        $grade                                = trim($request['grade']);
        $step                                 = trim($request['step']);
        $pensionmgr                           = trim($request['penmgr']);
        $rsanumber                            = trim($request['rsanumber']);
        $employeeContribution                 = trim($request['employeeContribution']);
        $employerContribution                 = trim($request['employerContribution']);
        $basicAllowance                       = trim($request['basicAllowance']);
        $remark                               = trim($request['remark']);
        $current_month                        = trim($request['month']);
        $current_year                         = trim($request['year']);
        $date                                 = date("Y-m-d H:s:i");
        $total = $employeeContribution + $employerContribution;
        $checkRecord = DB::table('tblpension')
            ->where('fileNo', '=', $fileno)
            ->where('month', '=', $current_month)
            ->where('year', '=', $current_year)
            ->first();

        //Try to update Pension Manager, TIN and Remark without loosing data  
            //PFA
            if( ($pensionmgr) == ''){
                $pensionManagerID = $checkRecord->pension_manager;
            }else{
                $pensionManagerID = $pensionmgr;
            }
            //RSA No
            if( ($rsanumber) == ''){
                 $RSA_Number = $checkRecord->rsanumber;
            }else{
                $RSA_Number = $rsanumber;
            }
            //Remark
            if( ($remark) == ''){
                 $remarkDetails = $checkRecord->remark;
            }else{
                $remarkDetails = $remark;
            }
            
        if((count($checkRecord)) > 0)
        {
            DB::table('tblpension')
            ->where('fileNo', '=', $fileno)
            ->where('month', '=', $current_month)
            ->where('year', '=', $current_year)
            ->update(array( 
                'fileNo'               => $fileno,
                'pension_manager'      => $pensionManagerID, 
                'rsanumber'            => $RSA_Number,
                'employee_pension'     => $employeeContribution,
                'employer_pension'     => $employerContribution,
                'total'                => $total,
                'year'                 => $current_year,
                'month'                => $current_month,
                'remark'               => $remarkDetails,
                'grade'                => $grade,
                'step'                 => $step,
                'updated_at'           => $date
            ));
            $this->addLog('New Pension Updated and division: '. $this->division);
            return redirect('/pension/create')->with('msg', 'Pension already computed and record was updated');
        }
        else
        {
            DB::table('tblpension')->insert(array( 
                'fileNo'               => $fileno,
                'pension_manager'      => $pensionmgr, 
                'rsanumber'            => $rsanumber,
                'employee_pension'     => $employeeContribution,
                'employer_pension'     => $employerContribution,
                'total'                => $total,
                'year'                 => $current_year,
                'month'                => $current_month,
                'remark'               => $remark,
                'grade'                => $grade,
                'step'                 => $step,
                'created_at'           => $date,
                'updated_at'           => $date
                ));
            $this->addLog('New Pension Added from division: '. $this->division);
            return redirect('/pension/create')->with('msg', 'Pension successfully computed.');
        }

    }




    public function computePensionBatch(Request $request) // In that division only
    {
        $this->validate($request, 
        [
            'month'         => 'required|alpha_num',
            'year'          => 'required|alpha_num',        
        ]);
        $current_month      = trim($request['month']);
        $current_year       = trim($request['year']);
        $date               = date("Y-m-d H:s:i");

        //Get all staff in that division
        $allStaffDetails = DB::table('tblper')->where('divisionID', '=', $this->divisionID)->where('employee_type', '<>', 'CONSOLIDATED')->get();

        //start computation
        $counter = 0;
        foreach($allStaffDetails as $details)
        {
            // get pension from salary structure for each staff
            $getPension = DB::table('basicsalary')
               ->where('grade', '=', $details->grade)
               ->where('step', '=', $details->step)
               ->where('employee_type', '=', strtoupper($details->employee_type))
               ->first();
            
            if($getPension){
                // calculate employee and employer contribution
                $employeeContribution_8_percent   = $getPension->pension;
                $basicAllowance                   = ($employeeContribution_8_percent * 12.5);
                $employerContribution_10_percent  = ($employeeContribution_8_percent * 1.25);
                $total                            = $employeeContribution_8_percent + $employerContribution_10_percent;

                //check if already computed, then update else insert
                $checkRecord = DB::table('tblpension')
                    ->where('fileNo', '=', $details->fileNo)
                    ->where('month', '=', $current_month)
                    ->where('year', '=', $current_year)
                    ->count();
                //get other details: persion Manager, RSA No, Remark (try to pick the latest details)
                $otherDetails = DB::table('tblpension')
                    ->where('fileNo', '=', $details->fileNo)
                    ->where('active', '=', 1)
                    ->orderBy('fileNo', 'DESC')
                    ->first();
                if($otherDetails){
                    $pensionManager = $otherDetails->pension_manager;
                    $RSA_No         = $otherDetails->rsanumber;
                    $remark         = $otherDetails->remark;
                }else{
                    $pensionManager = '';
                    $RSA_No         = '';
                    $remark         = '';
                }
                //
                if($checkRecord > 0)
                {
                    DB::table('tblpension')
                    ->where('fileNo', '=', $details->fileNo)
                    ->where('month', '=', $current_month)
                    ->where('year', '=', $current_year)
                    ->update(array( 
                        'fileNo'               => $details->fileNo,
                        'pension_manager'      => $pensionManager, 
                        'rsanumber'            => $RSA_No,
                        'employee_pension'     => $employeeContribution_8_percent,
                        'employer_pension'     => $employerContribution_10_percent,
                        'total'                => $total,
                        'year'                 => $current_year,
                        'month'                => $current_month,
                        'remark'               => $remark,
                        'grade'                => $details->grade,
                        'step'                 => $details->step,
                        'updated_at'           => $date
                    ));
                    $auditLogMsg = 'Pension Updated and the division is: '. $this->division;
                    $feedBack    = 'Pension already computed and records were updated for all '. $this->division .' division.';
                }
                else
                {
                    DB::table('tblpension')->insert(array( 
                        'fileNo'               => $details->fileNo,
                        'pension_manager'      => $pensionManager, 
                        'rsanumber'            => $RSA_No,
                        'employee_pension'     => $employeeContribution_8_percent,
                        'employer_pension'     => $employerContribution_10_percent,
                        'total'                => $total,
                        'year'                 => $current_year,
                        'month'                => $current_month,
                        'remark'               => $remark,
                        'grade'                => $details->grade,
                        'created_at'           => $date,
                        'updated_at'           => $date
                        ));
                    $auditLogMsg = 'Pension Added and the division is: '. $this->division;
                    $feedBack    = 'Pension was successfully computed for all '. $this->division .' division.';
                }
            }
        $counter ++;
        }  // end foreach
        $this->addLog($auditLogMsg);
        return redirect()->route('pension.create')->with('msg', $feedBack .' '.  $counter . ' Staff were affected');

    }


    
    public function pensionReport()
    {
        //get list of staff in that division
         $data['staff'] = DB::table('tblper')
            ->where('tblper.divisionID', '=', $this->divisionID)
            ->where('tblper.staff_status', 1)
            ->get();
        // Pension manager
            $data['allPensionManager'] = DB::table('tblpension_manager')
            ->where('active', '=', 1)
            ->get();

         return view('pension/report',$data);
    }


 
    public function generateReport(Request $request)
    {
        $this->validate($request, 
        [
            'fileNo' => 'required|numeric',      
        ]);
        $fileno = $request['fileNo'];
        $year   = $request['year'];
        
        $data['staff'] = $query = DB::table('tblper')   
        ->where('fileNo', '=', $fileno)
        ->first();

        if(($year == "") or $year == null)
        {
            $arrayGetYear = array();
            $query = DB::table('tblpension')   
                ->join('tblper', 'tblper.fileNo', '=','tblpension.fileNo')
                ->where('tblpension.fileNo', '=', $fileno)
                ->get();
            foreach ($query as  $value) 
            {
                $month = $value->month;
                $result[$month]['employee_pension'] = $value->employee_pension;
                $result[$month]['employer_pension'] = $value->employer_pension;
                $result[$month]['total'] = $value->total;
            }
            $fullmonth=array("JANUARY","FEBRUARY","MARCH","APRIL","MAY","JUNE","JULY", "AUGUST","SEPTEMBER","OCTOBER","NOVEMBER", "DECEMBER" );
            $rowcount=0;
            $empty=0.0;
            for ($row = 0; $row <=11; $row++) 
            {
                $currentmonth=$fullmonth[$row];
                if (!isset($result[$currentmonth]['employee_pension']))
                {
                    $result[$currentmonth]['employee_pension'] = $empty;
                    $result[$currentmonth]['employer_pension'] = $empty;
                    $result[$currentmonth]['total'] = $empty;
                } 
            }
            $data['getAllPension']    = $query;
            $data['result']  = $result;
            $viewToGenerate  = 'pension.viewAllReport';
        }else{
            $this->validate($request, 
            [
                'year'                            => 'required|numeric',       
            ]);
            $year   = $request['year'];
            $query  = DB::table('tblpension')   
                ->join('tblper', 'tblper.fileNo', '=','tblpension.fileNo')
                ->where('tblpension.fileNo', '=', $fileno)
                ->where('tblpension.year', '=', $year)
                ->get();
            $result = array();
            foreach ($query as  $value) 
            {
                $month = $value->month;
                $result[$month]['employee_pension'] = $value->employee_pension;
                $result[$month]['employer_pension'] = $value->employer_pension;
                $result[$month]['total'] = $value->total;
            }
            $fullmonth=array("JANUARY","FEBRUARY","MARCH","APRIL","MAY","JUNE","JULY", "AUGUST","SEPTEMBER","OCTOBER","NOVEMBER", "DECEMBER" );
            $rowcount=0;
            $empty=0.0;
            for ($row = 0; $row <=11; $row++) 
            {
                $currentmonth=$fullmonth[$row];
                if (!isset($result[$currentmonth]['employee_pension']))
                {
                    $result[$currentmonth]['employee_pension'] = $empty;
                    $result[$currentmonth]['employer_pension'] = $empty;
                    $result[$currentmonth]['total'] = $empty;
                } 
            }
            $data['result']   = $result;
            $data['getYear']  = $year;
            $viewToGenerate   = 'pension.viewreport';
        }

        return view($viewToGenerate, $data);
    }



    public function monthlyReport(Request $request)
    {
        $this->validate($request, 
        [
            'month'       => 'required|string',
            'year'        => 'required|numeric',              
        ]);

        $persionManagerID= trim($request['persionManager']);
        $month           = trim($request['month']);
        $year            = trim($request['year']);
        $data['month']   = $month;
        $data['year']    = $year;
        if($persionManagerID == ''){
            $data['allReportOrmonthly'] = DB::table('tblpension')   
                ->join('tblper', 'tblper.fileNo', '=','tblpension.fileNo')
                ->where('tblpension.month', '=', $month)
                ->where('tblpension.year', '=', $year)
                ->where('tblpension.active', '=', 1)
                ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
                ->where('tblper.divisionID', '=', $this->divisionID)
                ->orderBy('tblpension.grade', 'DESC')
                ->orderBy('tblpension.step', 'DESC')
                ->orderBy('tblper.appointment_date', 'ASC')
                ->get();
                $nameOfPFA = '';
        }else{
            $data['allReportOrmonthly'] = DB::table('tblpension')   
                ->join('tblper', 'tblper.fileNo', '=','tblpension.fileNo')
                ->where('tblpension.month', '=', $month)
                ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
                ->where('tblpension.year', '=', $year)
                ->where('tblpension.active', '=', 1)
                ->where('tblpension.pension_manager', '=', $persionManagerID)
                ->where('tblper.divisionID', '=', $this->divisionID)
                ->orderBy('tblpension.grade', 'DESC')
                ->orderBy('tblpension.step', 'DESC')
                ->orderBy('tblper.appointment_date', 'ASC')
                ->get();
            $getPFA = DB::table('tblpension_manager')   
                ->where('ID', '=', $persionManagerID)
                ->select('pension_manager')
                ->first();
                $nameOfPFA = 'NAME OF PFA: ' . $getPFA->pension_manager;
        }
        $data['nameOfPFA'] = $nameOfPFA;
        $data['division']  = $this->division;
        $data['penmgr']    = DB::table('tblpension_manager')->where('tblpension_manager.active', 1)->get();

        return view('pension/monthlyreport',$data);
    }



    public function allPensionReport()
    {
        $data['allReportOrmonthly'] = DB::table('tblpension')   
            ->join('tblper', 'tblper.fileNo', '=','tblpension.fileNo')
            ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
            ->where('tblper.divisionID', '=', $this->divisionID)
            ->where('tblpension.active', '=', 1)
            //->orderBy('tblpension.grade', 'DESC')
            //->orderBy('tblpension.step', 'DESC')
            //->orderBy('tblper.appointment_date', 'ASC')
            ->orderBy('tblpension.month', 'DESC')
            ->paginate(30);

        $data['nameOfPFA'] = 'ALL STAFF REPORT WITH ALL PFA';
        $data['division']  = $this->division;
        $data['penmgr']    = DB::table('tblpension_manager')->where('tblpension_manager.active', 1)->get();

        return view('pension/allRecord', $data);
    }

    

    public function updateStaffPension(Request $request) 
    {
        $this->validate($request,[ 
            'pensionID'             => 'required|numeric',         
        ]);
        $rsaNumber                  = trim($request['rsaNumber']);
        $pensionManager             = trim($request['pensionManager']);
        $remark                     = trim($request['remark']);
        $pensionID                  = trim($request['pensionID']);
        $date                       = date("Y-m-d H:s:i");
        if(DB::table('tblpension')->where('penID', '=', $pensionID)->first())
        {
            DB::table('tblpension')
                ->where('penID', '=', $pensionID)
                ->update(array( 
                    'pension_manager'      => $pensionManager, 
                    'rsanumber'            => $rsaNumber,
                    'remark'               => $remark,
                    'updated_at'           => $date
            ));
        }
        return redirect()->route('reportAll')->with('msg', 'Record was updated successfully');
    }



    public function softDeleteStaffPension(Request $request) 
    {   
        $this->validate($request, [  
            'pensionID' => 'numeric',
        ]);
        $pensionID      = (trim($request['pensionID']));
        if(DB::table('tblpension')->where('penID', '=', $pensionID)->first())
        {
            //soft delete
           /* $date           = date("Y-m-d H:s:i");
             DB::table('tblpension')
                ->where('penID', '=', $pensionID)
                ->update(array( 
                    'active'      => 0,
                    'updated_at'  => $date
                ));
            */
            //Hard or permanent deletion
            //$data = DB::table('tblpension')->where('penID', $pensionID)->delete();
            $feedBack = 'Selected Staff record was deleted successfully.';
            $alertType = 'msg';
        }else
        {
            $feedBack = 'Record cannot be deleted. Record not found on our system !';
            $alertType = 'err';
        }
        $this->addLog('Staff Pension was deleted successfully from division: '. $this->division .'with pension ID: '. $pensionID);
        return redirect()->route('reportAll')->with($alertType, $feedBack );
         
    }


}
