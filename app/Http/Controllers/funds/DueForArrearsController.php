<?php

namespace App\Http\Controllers\funds;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use DB;
use Session;
use Auth;

class DueForArrearsController extends Controller
{
    public function index()
    {
      $data['court']    = DB::table('tbl_court')->get();
      $ses_court = session('searchCourt');
      $ses_division = session('searchDivision');
      //dd($ses_division);
       $data['division'] =  DB::table('tbldivision')->where('courtID','=', session('searchCourt'))->get();
      if($ses_court !='' && $ses_division =='')
      {
         $data['due'] = DB::table('tblstaff_for_arrears')
       ->join('tbl_court','tbl_court.id','=','tblstaff_for_arrears.courtID')
       ->join('tbldivision','tbldivision.divisionID','=','tblstaff_for_arrears.divisionID')
       ->where('tblstaff_for_arrears.courtID','=',$ses_court)
       ->where('payment_status','=',0)
       ->get();
      }
      elseif($ses_division !='' && $ses_court !='')
      {
         $data['due'] = DB::table('tblstaff_for_arrears')
       ->join('tbl_court','tbl_court.id','=','tblstaff_for_arrears.courtID')
       ->join('tbldivision','tbldivision.divisionID','=','tblstaff_for_arrears.divisionID')
       ->where('tblstaff_for_arrears.courtID','=',$ses_court)
       ->where('tblstaff_for_arrears.divisionID','=',$ses_division)
       ->where('payment_status','=',0)
       ->get();
      }
       else
       {
       $data['due'] = DB::table('tblstaff_for_arrears')
       ->join('tbl_court','tbl_court.id','=','tblstaff_for_arrears.courtID')
       ->join('tbldivision','tbldivision.divisionID','=','tblstaff_for_arrears.divisionID')
       ->where('payment_status','=',0)->get();
       }
       return view('dueForArrears/list',$data);
    }

    public function create()
    {
      $data['staff'] = '';
      $divisionsession = session('divsession');
        $data['division'] =  DB::table('tbldivision')->where('courtID','=', session('anycourt'))->get();
        $data['court'] = DB::table('tbl_court')->get();
        $data['staffList'] = DB::table('tblper')
        ->where('courtID','=', session('anycourt'))
        ->where('divisionID','=', $divisionsession)->get();
        if(session('staffsession') !='')
        {
        $data['staff'] = DB::table('tblper')
        ->where('courtID','=', session('anycourt'))
        ->where('fileNo','=', session('staffsession'))->first();
        }
         return view('dueForArrears/create',$data);
    }

     public function edit($id=null)
    {
      $data['staff'] = '';
        $s = DB::table('tblarrears_temp')->where('dueID','=', $id)->first();
        $data['staffList'] = DB::table('tblarrears_temp')
        ->join('tblper','tblper.fileNo','=','tblarrears_temp.fileNo')
        ->where('tblarrears_temp.fileNo','=', $s->fileNo)->first();
        $data['allDivisions'] =  DB::table('tbldivision')->where('courtID','=', $s->courtID)->get();
        $data['division'] =  DB::table('tbldivision')->where('courtID','=', $s->courtID)->where('divisionID','=', $s->divisionID)->first();

        $data['court']    = DB::table('tbl_court')->where('id','=', $s->courtID)->first();
        $data['allcourts']    = DB::table('tbl_court')->get();
        $data['emptype']    = DB::table('tblemployment_type')->get();
       
         return view('dueForArrears/edit',$data);
    }



    public function store(Request $request)
    {
      $this->validate($request, [
    		'newEmployeeType' => 'required|string',
        'newGrade' => 'required|numeric',
        'newStep' => 'required|numeric',
        'court' => 'required|numeric',
        'division' => 'required|numeric',
        'arrearsType' => 'required|string',
    		]);
      $salary = DB::table('basicsalary')
      ->where('courtID','=',$request['court'])
      ->where('grade','=',$request['newGrade'])
      ->where('step','=',$request['newStep'])
      ->where('employee_type','=',$request['newEmployeeType'])
      ->count();
      if($salary == 0)
      {
        return back()->with('err', 'No Salary Structure Available');
      }
      else
      {
      $insert = DB::table('tblstaff_for_arrears')->insert(array(
       'fileNo'                 => $request['fileNo'],
       'newEmploymentType'      => $request['newEmployeeType'],
       'oldEmploymentType'      => $request['employeeType'],
       'courtID'                => $request['court'],
       'old_grade'              => $request['oldGrade'],
       'old_step'               => $request['oldStep'],
       'new_grade'              => $request['newGrade'],
       'new_step'               => $request['newStep'],
       'due_date'               => $request['dueDate'],
       'divisionID'             => $request['division'],
       'arrears_type'           => $request['arrearsType'],
       'payment_status'          => 0,
      ));
    }
      if($insert)
      {
        return redirect('/staff-due/arrears')->with('msg','Successfully Entered');
      }
      else {
      return redirect('/staff-due/arrears')->with('err','Record could not be saved');
      }

    }

    public function divSession(Request $request)
    {
      $val = $request['val'];
      if($val == 'division')
      {
        Session::put('divsession', $request['division']);
      }
      elseif($val == 'staff')
      {
          Session::put('staffsession', $request['staff']);
      }
       elseif($val == 'search_court')
      {
          Session::forget('searchDivision');
          Session::put('searchCourt', $request['courtID']);

      }
      elseif($val == 'search_division')
      {
          Session::put('searchDivision', $request['division']);
      }
        return response()->json('Set');
    }


    public function alert()
    {
        return view('layouts/_incrementAlert');
    }


    Public function DivisionList1 ($court){

      $DepartmentList= DB::Select("SELECT *,
      (SELECT `court_name`  FROM `tbl_court` WHERE `tbl_court`.`id`=`tbldivision`.`courtID`) as court_names 
      FROM `tbldivision` WHERE `tbldivision`.`courtID`='$court'");
      return $DepartmentList;

    }

     
     Public function staffDue ($court,$division, $staffName){

        $date = date('Y-m-d');
        $date = strtotime($date);
        $new_date = strtotime('- 1 year', $date);

        if($staffName == ''){

        $DepartmentList= DB::Select("SELECT * 
        FROM `tblper` WHERE `divisionID` ='$division' AND `courtID`='$court' && `laststep_update` <= DATE_SUB(CURRENT_DATE(), INTERVAL 1 YEAR) AND `date_present_appointment` <=  DATE_SUB(CURRENT_DATE(), INTERVAL 1 YEAR)");
        return $DepartmentList;

        } else{


          $DepartmentList= DB::Select("SELECT * 
        FROM `tblper` WHERE `divisionID` ='$division' AND `courtID`='$court' AND `fileNo`= '$staffName'  ");
        return $DepartmentList;

        }

    }
     
     
    
    Public function DivisionStaffList($court,$division){
      $myData= DB::table('tblper')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->select('fileNo', 'surname', 'first_name', 'othernames')
                ->where('tblper.divisionID','=', $division)
                ->where('tblper.courtID', $court)
                ->orderBy('surname', 'Asc')->get();
                return $myData;
      }

    
   
   Public function ConfirmSalary($grade,$step){
    $confir= DB::Select("SELECT * FROM `basicsalaryOLD` WHERE `grade`='$grade' AND `step`='$step'");
    if(($confir))
       {
         return true;
       }
       else
       {
      return false;
       }
  }

	 public function dueForIncrement(Request $request){

        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        
        $court= trim($request['court']);
        $data['court'] = $court; 
        $division= trim($request['division']);
        $data['division'] = $division;
        $staffName= trim($request['staffName']);
        $data['staffName'] = $staffName;
        $data['courts'] = DB::table('tbl_court')->get();
          //$data['scale'] ='';
        $data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
        $data['DivisionList'] = $this->DivisionList1($court);
        $data['staffList'] = $this->DivisionStaffList($court,$division);
        $data['due'] = $this->staffDue($court,$division,$staffName);

      
        //Perform insert

        if(isset($_POST['insert'])){
          $checkbox = $request['checkbox'];
          $dueDate = date('Y-01-01');

          if (isset($checkbox)){

                  foreach($checkbox as $i) {



                    $fetch = DB::table('tblper')->where('fileNo','=',$i)->first();
                    $new_step = $fetch->step;
                    $grade = $fetch->grade;
                    $new_step = $new_step + 1;
                    $datey = $fetch->laststep_update;
                    $datey = strtotime($datey);
                    $new_date = date('Y-m-d', strtotime('+1 year', $datey));
                    $approved_date = date('Y-m-d H:i:s');
                    $approved_by = Auth::user()->username;

                    $salaryCheck = $this->ConfirmSalary($grade,$new_step);

                    if ( $salaryCheck  == true){

                          $court = $fetch->courtID;
                          $division = $fetch->divisionID;
                          $data['court'] = $court; 
                          $data['division'] = $division; 
                          $data['DivisionList'] = $this->DivisionList1($court);
                          $data['due'] = $this->staffDue($court,$division,$staffName);
                          $data['warning'] = "The staff status cannot be increased to the step! Because there is no salary scale for the step";
                          return view('dueForArrears/incrementDue',$data); 

                    } else{

                     $insert = DB::table('tblstaff_for_arrears')->insert(array(
                     'fileNo'                 => $i,
                     'newEmploymentType'      => $fetch->employee_type,
                     'oldEmploymentType'      => $fetch->employee_type,
                     'courtID'                => $fetch->courtID,
                     'old_grade'              => $fetch->grade,
                     'old_step'               => $fetch->step,
                     'new_grade'              => $fetch->grade,
                     'new_step'               => $new_step,
                     'due_date'               => $new_date,
                     'divisionID'             => $fetch->divisionID,
                     'arrears_type'           => 'INC',
                     'payment_status'         => 0,
                     'approvedDate'			  => $approved_date,
                     'approvedBy'			  => $approved_by,
                    ));
                   

                    if($insert)
                      {


                       DB::table('tblper')->where('fileNo','=',$i)->update(array(      
                      'laststep_update'          => $new_date,
                      'step'          => $new_step,
                       ));
                      }
                  

                } 

                $court = $fetch->courtID;
                $division = $fetch->divisionID;
                $data['court'] = $court; 
                $data['division'] = $division; 
                $data['DivisionList'] = $this->DivisionList1($court);
                $data['due'] = $this->staffDue($court,$division,$staffName);
                $data['success'] = "successfully approved";
                return view('dueForArrears/incrementDue',$data);  

              } 


          } else{

                $court= trim($request['courty']);
                $division= trim($request['divisiony']);

                          $data['court'] = $court; 
                          $data['division'] = $division; 
                $data['due'] = $this->staffDue($court,$division,$staffName);
                $data['warning'] = "Please click on the checkbox beside the staff";
                return view('dueForArrears/incrementDue',$data); 
          }

      }


      if (isset($_POST['newinsert'])) {
        # code...

                    $staffNamey = $request['staffNamey'];
                    $courtn = $request['courtn'];
                    $divisionn = $request['divisionn'];

                   //dd($staffNamey,$courtn,  $divisionn );

                    $fetch = DB::table('tblper')->where('fileNo','=',$staffNamey)->first();
                    $new_step = $fetch->step;
                    $grade = $fetch->grade;
                    $new_step = $new_step + 1;
                    $datey = $fetch->laststep_update;
                    $datey = strtotime($datey);
                    $new_date = date('Y-m-d', strtotime('+1 year', $datey));
                     $approved_date = date('Y-m-d H:i:s');
                    $approved_by = Auth::user()->username;

                    $salaryCheck = $this->ConfirmSalary($grade, $new_step);

                    if ( $salaryCheck  == true){

                          $court = $fetch->courtID;
                          $division = $fetch->divisionID;

                           $data['court'] = $court; 
                            $data['division'] = $division; 

                          $data['DivisionList'] = $this->DivisionList1($court);
                          $data['due'] = $this->staffDue($court,$division,$staffName);
                          $data['warning'] = "The staff status cannot be increased to the step! Because there is no salary scale for the step";
                          return view('dueForArrears/incrementDue',$data); 

                    } else{

                     $insert = DB::table('tblstaff_for_arrears')->insert(array(
                     'fileNo'                 => $staffNamey,
                     'newEmploymentType'      => $fetch->employee_type,
                     'oldEmploymentType'      => $fetch->employee_type,
                     'courtID'                => $fetch->courtID,
                     'old_grade'              => $fetch->grade,
                     'old_step'               => $fetch->step,
                     'new_grade'              => $fetch->grade,
                     'new_step'               => $new_step,
                     'due_date'               => $new_date,
                     'divisionID'             => $fetch->divisionID,
                     'arrears_type'           => 'INC',
                     'payment_status'          => 0,
                     'approvedDate'			  => $approved_date,
                     'approvedBy'			  => $approved_by,
                     
                    ));
                   

                    if($insert)
                      {


                       DB::table('tblper')->where('fileNo','=',$staffNamey)->update(array(      
                      'laststep_update'          => $new_date,
                      'step'          => $new_step,
                       ));
                      }
                  

                } 

              
                $court = trim($request['courtn']);
                    $division = trim($request['divisionn']);
                          $data['court'] = $court; 
                          $data['division'] = $division; 
                $data['DivisionList'] = $this->DivisionList1($courtn);
                $data['due'] = $this->staffDue($courtn,$divisionn,$staffName);
                $data['success'] = "successfully approved";
                return view('dueForArrears/incrementDue',$data);  


      }


      if (isset($_POST['edit'])) {
        # code...

                    $staffNameEdit = $request['staffNameEdit'];
                    $new_step = $request['newStep'];
                    $dueDate = $request['dueDate'];
                    $datey = strtotime($dueDate);
                    $new_date = date('Y-m-d', $datey);
                     $approved_date = date('Y-m-d H:i:s');
                    $approved_by = Auth::user()->username;

                   //dd($staffNamey,$courtn,  $divisionn );

                    $fetch = DB::table('tblper')->where('fileNo','=',$staffNameEdit)->first();
                    
                    $grade = $fetch->grade;
                    
                    $salaryCheck = $this->ConfirmSalary($grade,$new_step);

                    if ( $salaryCheck  == true){

                          $court = $fetch->courtID;
                          $division = $fetch->divisionID;

                           $data['court'] = $court; 
                            $data['division'] = $division; 

                          $data['DivisionList'] = $this->DivisionList1($court);
                          $data['due'] = $this->staffDue($court,$division,$staffName);
                          $data['warning'] = "The staff status cannot be increased to the step! Because there is no salary scale for the step";
                          return view('dueForArrears/incrementDue',$data); 

                    } else{

                     $insert = DB::table('tblstaff_for_arrears')->insert(array(
                     'fileNo'                 => $staffNameEdit,
                     'newEmploymentType'      => $fetch->employee_type,
                     'oldEmploymentType'      => $fetch->employee_type,
                     'courtID'                => $fetch->courtID,
                     'old_grade'              => $fetch->grade,
                     'old_step'               => $fetch->step,
                     'new_grade'              => $fetch->grade,
                     'new_step'               => $new_step,
                     'due_date'               => $new_date,
                     'divisionID'             => $fetch->divisionID,
                     'arrears_type'           => 'INC',
                     'payment_status'          => 0,
                     'approvedDate'			  => $approved_date,
                     'approvedBy'			  => $approved_by,
                    ));
                   

                    if($insert)
                      {


                       DB::table('tblper')->where('fileNo','=',$staffNameEdit)->update(array(      
                      'laststep_update'          => $new_date,
                      'step'          => $new_step,
                       ));
                      }
                  

                } 

              
                $court = trim($request['courty']);
                    $division = trim($request['divisiony']);
                          $data['court'] = $court; 
                          $data['division'] = $division; 
                $data['DivisionList'] = $this->DivisionList1($court);
                $data['due'] = $this->staffDue($court,$division,$staffName);
                $data['success'] = "successfully approved";
                return view('dueForArrears/incrementDue',$data);  


      }

       

      return view('dueForArrears/incrementDue',$data);
    }
    

    public function acceptIncrement(Request $request)
    {
       $fileNo = $request['fileNo'];

       $fetch = DB::table('tblarrears_temp')->where('fileNo','=',$fileNo)->first();

       $insert = DB::table('tblstaff_for_arrears')->insert(array(
       'fileNo'                 => $i,
       'newEmploymentType'      => $fetch->newEmploymentType,
       'oldEmploymentType'      => $fetch->oldEmploymentType,
       'courtID'                => $fetch->courtID,
       'old_grade'              => $fetch->old_grade,
       'old_step'               => $fetch->old_step,
       'new_grade'              => $fetch->new_grade,
       'new_step'               => $fetch->new_step,
       'due_date'               => $fetch->due_date,
       'divisionID'             => $fetch->divisionID,
       'arrears_type'           => 'INC',
       'payment_status'          => 0,
      ));
        
        if($insert)
        {
         DB::table('tblarrears_temp')->where('fileNo','=',$fileNo)->update(array(      
        'status'          => 1,
         ));
        }

        return response()->json('Approved');


    }
}
