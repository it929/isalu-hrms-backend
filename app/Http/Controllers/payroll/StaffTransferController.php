<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use DB;
use Session;
use Auth;

class StaffTransferController extends functionController
{
    public function index()
    {
       $data['due'] = DB::table('tblstaff_for_arrears')
       ->join('tbl_court','tbl_court.id','=','tblstaff_for_arrears.courtID')
       ->join('tbldivision','tbldivision.divisionID','=','tblstaff_for_arrears.divisionID')
       ->where('payment_status','=',0)->get();
       return view('dueForArrears/list',$data);
    }

   
    Public function DepartmentList($court){

      $DepartmentList= DB::Select("SELECT *,
      (SELECT `court_name`  FROM `tbl_court` WHERE `tbl_court`.`id`=`tbldepartment`.`courtID`) as court_names 
      FROM `tbldepartment` WHERE `tbldepartment`.`courtID`='$court'");
      return $DepartmentList;
    }

    Public function DivisionList2 ($court){

      $DepartmentList= DB::Select("SELECT *,
      (SELECT `court_name`  FROM `tbl_court` WHERE `tbl_court`.`id`=`tbldivision`.`courtID`) as court_names 
      FROM `tbldivision` WHERE `tbldivision`.`courtID`='$court'");
      return $DepartmentList;
    }

    


  	     public function create(Request $request){

         $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $data['CourtInfo']=$this->CourtInfo();
	 if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
	 if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

        //Variables
        $data['court']= $request['court'];
        $data['division']= $request['division'];
        $data['staffName']= $request['staffName'];
  
        $data['department']= $request['department'];
        $data['deptID'] = $request['deptID'];
 
        $eID = trim($request['eID']);
        $data['eID'] = $eID;
        $data['newDivision'] = $request['newDivision'];
        $mockstaffdetails=$this->StaffDetails2($data['court'],$data['division'], $data['staffName']);
        $data['grade']='';
        if ($mockstaffdetails){$data['grade']=$mockstaffdetails[0]->grade;}
        
       
        $effectiveDate = $request['effectiveDate'];
        $effectiveDate = strtotime($effectiveDate);
        $effectiveDate = date('Y-m-d', $effectiveDate);

        $approved_date = date('Y-m-d H:i:s');
        $approved_by = Auth::user()->username;
        $data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
        $data['DivisionList'] = $this->DivisionList1($data['court']);
        $data['staffList'] = $this->DivisionStaffList($data['court'],$data['division']);
        $data['due'] = $this->StaffDetails2($data['court'],$data['division'],$data['staffName']);

        $data['DepartmentList'] = $this->DepartmentList($data['court']);
        $data['DesignationList']= $this->GetDesignation($data['department'],$data['grade']);
         //Updating the staff transfer info
            if (isset($_POST['add'])) {
            $this->validate($request, [
            	'newDivision' => 'required',
            	'department' => 'required',
            	'effectiveDate' => 'required',
            	'staffName' => 'required',
        	]);
        	$data['warning'] = "No record saved";
              if(DB::table('tblper')->where('fileNo','=',$data['staffName'])->update(array(      
	            'divisionID'          => $data['newDivision'],
	            'department'          =>$data['department'],
             ))){
             DB::table('tbltransfer_history')->insert(array(
		   'fileNo'             => $data['staffName'],
		   'court'      	=> $data['court'],
		   'effectivedate'      => $effectiveDate ,
		   'prevdiv'            => $mockstaffdetails[0]->divisionID,
		   'curdiv'             => $data['newDivision'],
		   'prevdept'           => $mockstaffdetails[0]->department,
		   'curdept'            => $data['department'],
		   'posteddate'         => $approved_date,
		   'postedby'           => $approved_by
		  ));
		$data['due'] = $this->StaffDetails2($data['court'],$data['division'],$data['staffName']);
		$data['success'] = "The staff has been transfered successfully";
		$data['warning'] = "";
		}

            }
       
       
         return view('transfer/transfer',$data);
    }

     public function create141118(Request $request){

         $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $data['CourtInfo']=$this->CourtInfo();
	 if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
	 if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

        //Variables
        $court= trim($request['court']);
        $data['court'] = $court; 
        $division= trim($request['division']);
        $data['division'] = $division;
        $staffName= trim($request['staffName']);
        $data['staffName'] = $staffName;
        $department = trim($request['department']);
        $data['department'] = $department;
        $designation = trim($request['designation']);
        $data['designation'] = $designation;
        $deptID = trim($request['deptID']);
        $data['deptID'] = $deptID;
        $eID = trim($request['eID']);
        $data['eID'] = $eID;
        $newDivision = trim($request['newDivision']);
        $data['newDivision'] = $newDivision;
        $grade = trim($request['grade']);
        $data['grade'] = $grade;
        $employee_type = trim($request['employee_type']);
        $newStep = trim($request['newStep']);
        $designation = trim($request['designation']);
        $effectiveDate = $request['effectiveDate'];
        $effectiveDate = strtotime($effectiveDate);
        $effectiveDate = date('Y-m-d', $effectiveDate);

        $approved_date = date('Y-m-d H:i:s');
        $approved_by = Auth::user()->username;


        $data['courts'] = DB::table('tbl_court')->get();
          //$data['scale'] ='';
        $data['CourtList'] = DB::table('tbl_court')->select('id', 'court_name')->get();
        $data['DivisionList'] = $this->DivisionList1($court);
        $data['staffList'] = $this->DivisionStaffList($court,$division);
        $data['due'] = $this->StaffDetails2($court,$division,$staffName);
        $data['employee'] = $this->EmpType();
        $data['DepartmentList'] = $this->DepartmentList($court);
        $DesignationList= $this->GetDesignation($department,$grade);
        $data['DesignationList'] = $DesignationList;


            //Updating the staff transfer info
            if (isset($_POST['add'])) {
              # code...

               $fetch = DB::table('tblper')->where('fileNo','=',$staffName)->first();
               $emp_type = $fetch->employee_type;
               $grady = $fetch->grade;
               $divisiony = $fetch->divisionID;
               $stepy = $fetch->step;



              if ($employee_type == $emp_type && $grade == $grady && $newStep == $stepy && $newDivision != $divisiony ) {
                # code...For transfering staff where employment type, grade and step remains the same

				
                        $salaryCheck = $this->ConfirmSalary($employee_type, $grade, $newStep);
                        if ($salaryCheck == false) {
                          # code...

                              $data['due'] = $this->StaffDetails2($court,$division,$staffName);
                           
                            $data['warning'] = "The staff cannot be transfered because there is no salary scale for the grade and step 5";


                        } else {

                             $insert =  DB::table('tblper')->where('fileNo','=',$staffName)->update(array(      
                            'divisionID'          => $newDivision,
                            'department'          => $department,
                            
                             ));
				
	
                            $data['due'] = $this->StaffDetails2($court,$division,$staffName);
                            $data['success'] = "The staff has been transfered to new division successfully";

                            return view('transfer/transfer',$data);

                        }


              } else if($employee_type != $emp_type && $grade != $grady && $newDivision != $divisiony && $newStep != $stepy ) {

                    # code... For transfering staff where employment type, grade and step changes

                       $salaryCheck = $this->ConfirmSalary($employee_type, $grade, $newStep);

                        if ($salaryCheck == false) {
                          # code...

                              $data['due'] = $this->StaffDetails2($court,$division,$staffName);
                            $data['warning'] = "The staff cannot be transfered because there is no salary scale for the new grade and step";


                        } else {

                           $insert =  DB::table('tblper')->where('fileNo','=',$staffName)->update(array(      
                              'divisionID'            => $newDivision,
                              'step'                  => $newStep,
                              'department'            => $department,
                              'grade'                 => $grade,
                              'employee_type'         => $employee_type,
                              'laststep_update'       => $effectiveDate,
                              'date_present_appointment' => $effectiveDate,
                               ));

                            if ($insert) {
                              # code...
                                 DB::table('tblstaff_for_arrears')->insert(array(
                                   'fileNo'                 => $staffName,
                                   'newEmploymentType'      => $employee_type,
                                   'oldEmploymentType'      => $employee_type,
                                   'courtID'                => $fetch->courtID,
                                   'old_grade'              => $fetch->grade,
                                   'old_step'               => $fetch->step,
                                   'new_grade'              => $grade,
                                   'new_step'               => $newStep,
                                   'due_date'               => $effectiveDate,
                                   'divisionID'             => $newDivision,
                                   'arrears_type'           => 'INC',
                                   'payment_status'          => 0,
                                   'approvedDate'       => $approved_date,
                                   'approvedBy'       => $approved_by,

                                  ));

                            }



                            $data['due'] = $this->StaffDetails2($court,$division,$staffName);
                            $data['success'] = "The staff has been transfered to new division with new Grade and Step successfully";

                      }


                } elseif ($newDivision == $divisiony && $newStep == $stepy && $grade == $grady && $employee_type != $emp_type ) {
                  # code...

                        $salaryCheck = $this->ConfirmSalary($employee_type, $grade, $newStep);

                      if ($salaryCheck == false) {
                          # code...

                              $data['due'] = $this->StaffDetails2($court,$division,$staffName);
                            $data['warning'] = "The staff cannot be transfered because there is no salary scale for the grade and step of the new employee type";


                      } else {

                            DB::table('tblper')->where('fileNo','=',$staffName)->update(['employee_type' => $employee_type]);

                          $data['due'] = $this->StaffDetails2($court,$division,$staffName);
                      $data['success'] = "The staff employee type has been changed successfully";

                    }


                }  else {



                         $salaryCheck = $this->ConfirmSalary($emp_type, $grady, $newStep);

                      if ($salaryCheck == false) {
                          # code...

                              $data['due'] = $this->StaffDetails2($court,$division,$staffName);
                            $data['warning'] = "The staff cannot be transfered because there is no salary scale for the new grade and step";


                      } else {


                       $insert =  DB::table('tblper')->where('fileNo','=',$staffName)->update(array(      
                      'divisionID'          => $newDivision,
                      'step'          => $newStep,
                      'laststep_update'          => $effectiveDate,
                      'department'          => $department,
                       ));


                        if ($insert) {
                              # code...
                               DB::table('tblstaff_for_arrears')->insert(array(
                               'fileNo'                 => $staffName,
                               'newEmploymentType'      => $fetch->courtID,
                               'oldEmploymentType'      => $employee_type,
                               'courtID'                => $fetch->courtID,
                               'old_grade'              => $fetch->grade,
                               'old_step'               => $fetch->step,
                               'new_grade'              => $grade,
                               'new_step'               => $newStep,
                               'due_date'               => $effectiveDate,
                               'divisionID'             => $newDivision,
                               'arrears_type'           => 'INC',
                               'payment_status'          => 0,
                               'approvedDate'       => $approved_date,
                               'approvedBy'       => $approved_by,

                              ));

                            }


                        $data['due'] = $this->StaffDetails2($court,$division,$staffName);
                        $data['success'] = "The staff has been transfered with new step successfully";

                        return view('transfer/transfer',$data);


                }


              }


            }
       
       
         return view('transfer/transfer',$data);
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
 Public function EmpType(){
          $DepartmentList= DB::Select("SELECT * 
        FROM `tblemployment_type` ");
        return $DepartmentList;
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

    
   
   	Public function ConfirmSalary($employee_type, $grade,$step){
	    $confir= DB::Select("SELECT * FROM `basicsalary` WHERE `grade`='$grade' AND `step`='$step' AND `employee_type` = '$employee_type' ");
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
