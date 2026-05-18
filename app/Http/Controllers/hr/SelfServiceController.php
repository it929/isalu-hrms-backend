<?php

namespace App\Http\Controllers\hr;

use Illuminate\Http\Request;
use File;
use App\Http\Requests;
use Carbon\Carbon;
use Session;
use Illuminate\Support\Facades\Input;
use DB;
use Auth;

class SelfServiceController extends ParentController
{

    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->middleware(function ($request, $next) {
            $this->division = $request->session()->get('division');
        $this->divisionID = $request->session()->get('divisionID');
        Session::put('this_division', $this->division);
        //Session::forget('hideAlert');
            return $next($request);
                    return $next($request);
        });
    });
    }

    public function view()
    {
        //$data['getTitles']=DB::table('tbltitle')->get();
        return view('profile.searchStaff');
    }


    public function autocomplete(Request $request)
    {

        $query = $request->input('query');

        $search = DB::table('tblper')
                ->where('divisionID', $this->divisionID)
                 ->where('first_name', 'like', "%$query%")
	             ->orWhere('surname', 'like', "%$query%")
	             ->orWhere('fileNo', 'like', "%$query%")
                ->take(50)
                ->orderby('ID','desc')
                ->get();

        //$search_result=preg_match("/".$query."/", $search);

        $return_array = null;
        foreach($search as $s)
        {
          $return_array[]  =  ["value"=>$s->surname.' '.$s->first_name.' '.$s->othernames.' - '.$s->fileNo, "data"=>$s->ID, "fileNo"=>$s->fileNo];
        }
        return response()->json(array("suggestions"=>$return_array));
    }

   //UPDATE BIODATA
   public function updateBIODATA(Request $request){

       $fileID      =     $request->input('fileID');
       $fileNo      =     $request->input('fileNo');
       $division    =     $request->input('division');
       $title       =     $request->input('title');
       $surname     =     $request->input('surname');
       $firstname   =     $request->input('firstname');
       $othernames  =     $request->input('othernames');
       $address     =     $request->input('address');
       $gender      =     $request->input('gender');
       $currentstate =    $request->input('currentstate');
       $phone       =     $request->input('phone');
       $nationality =     $request->input('nationality');
       $status      =     $request->input('status');

       DB::table('tblper')->where('ID','=',$fileID)
                  ->update(['fileNo'=>$fileNo,'divisionID'=>$division,'title'=>$title,
                  'surname'=>$surname,'first_name'=>$firstname,'othernames'=>$othernames,'home_address'=>$address,
                  'gender'=>$gender,'stateID'=>$currentstate,'phone'=>$phone,'nationality'=>$nationality,'status_value'=>$status]);

       $result=$this->biodataFunction($fileID); //function to reload and display data on form

       return $result;
   }

   //UPDATE PARTICULARS OF BIRTH
    public function updatePOB(Request $request){

       $fileID   =     $request->input('fileID');
       $dob      =     $request->input('dob');
       $pob      =     $request->input('pob');
       $ms       =     $request->input('ms');

       DB::table('tblper')
            ->where('ID','=',$fileID)
            ->update(['dob'=>$dob,'placeofbirth'=>$pob,'maritalstatus'=>$ms]);

       $result=$this->biodataFunction($fileID); //function to reload and display data on form
       return $result;
   }

   //UPDATE SALARY DETAILS
   public function updateSALARYDETAILS(Request $request){

       $fileID          =    $request->input('fileID');
       $appdate         =    $request->input('appointment_date');
       $firstdate       =    $request->input('firstarrival_date');
       $emptype         =    $request->input('employee_type');
       $designation     =    $request->input('designation');
       $department      =    $request->input('department');
       $section         =    $request->input('section');
       $grade           =    $request->input('grade');
       $step            =    $request->input('step');
       $bank            =    $request->input('bank');
       $bankgroup       =    $request->input('bankgroup');
       $bankbranch      =    $request->input('bankbranch');
       $accno           =    $request->input('accno');
       $nhfno           =    $request->input('nhfno');
       $incrementaldate =    $request->input('incrementaldate');

       DB::table('tblper')->where('ID','=',$fileID)
                  ->update(['appointment_date'=>$appdate,'firstarrival_date'=>$firstdate,
                  'employee_type'=>$emptype,'designation'=>$designation,'department'=>$department,'section'=>$section,
                  'grade'=>$grade,'step'=>$step,'bankID'=>$bank,'bankGroup'=>$bankgroup,'bank_branch'=>$bankbranch,
                  'AccNo'=>$accno,'nhfNo'=>$nhfno,'incremental_date'=>$incrementaldate]);

       $result=$this->biodataFunction($fileID); //function to reload and display data on form

       return $result;
   }

   //UPDATE PARTICULARS OF EDUCATION
    public function updateEDU(Request $request){

       $eduID           =   $request->input('eduID');
       $sID             =   $request->input('staffID');
       $degree          =   $request->input('degree');
       $schoolattended  =   $request->input('schoolattended');
       $from            =   $request->input('from');
       $to              =   $request->input('to');
       $certificate     =   $request->input('certificate');


       DB::table('tbleducations')
            ->where('id','=',$eduID)
            ->where('staffid','=',$sID)
            ->update(['degreequalification'=>$degree,'schoolattended'=>$schoolattended,'schoolfrom'=>$from,'schoolto'=>$to,'certificateheld'=>$certificate]);

       $result=$this->biodataFunction($sID); //function to reload and display data on form
       return $result;
   }

   //UPDATE PARTICULARS OF LANGUAGE SPOKEN
    public function updateLANGUAGE(Request $request){

       $langID             =   $request->input('langID');
       $stID               =   $request->input('staffID');
       $language           =   $request->input('language');
       $spoken             =   $request->input('spoken');
       $written            =   $request->input('written');
       $exam_qualified     =   $request->input('exam_qualified');
       $checkedby         =   $request->input('checkedby');


       DB::table('languages')
            ->where('langid','=',$langID)
            ->where('staffid','=',$stID)
            ->update(['language'=>$language,'spoken'=>$spoken,'written'=>$written,'exam_qualified'=>$exam_qualified,'checkedby'=>$checkedby]);

       $result=$this->biodataFunction($stID); //function to reload and display data on form
       return $result;
   }

  //UPDATE PARTICULARS OF CHILDREN
    public function updateCHILDREN(Request $request){

       $recordID             =   $request->input('recordID');
       $parentID             =   $request->input('parentID');
       $fullname             =   $request->input('fullname');
       $gender2              =   $request->input('gender2');
       $dateofbirth          =   $request->input('dateofbirth');
       $checked_children_particulars     =   $request->input('checked_children_particulars');

       DB::table('tblchildren_particulars')
                ->where('id','=',$recordID)
                ->where('staffid','=',$parentID)
                ->update(['fullname'=>$fullname,'gender'=>$gender2,'dateofbirth'=>$dateofbirth,'checked_children_particulars'=>$checked_children_particulars]);

       $result=$this->biodataFunction($parentID); //function to reload and display data on form
       return $result;
   }

   //UPDATE NEXT OF KIN
    public function updateNOK(Request $request){

       $nokID                   =   $request->input('nokID');
       $nokparentID             =   $request->input('nokparentID');
       $nokfullname             =   $request->input('nokfullname');
       $nokaddress              =   $request->input('nokaddress');
       $nokrelationship         =   $request->input('nokrelationship');
       $nokphoneno              =   $request->input('nokphoneno');

       DB::table('tblnextofkin')
                ->where('kinID','=',$nokID)
                ->where('staffid','=',$nokparentID)
                ->update(['fullname'=>$nokfullname,'relationship'=>$nokrelationship,'address'=>$nokaddress,'phoneno'=>$nokphoneno]);

       $result=$this->biodataFunction($nokparentID); //function to reload and display data on form
       return $result;
   }

   //UPDATE WIFE DETAILS
    public function updateWIFE(Request $request){

       $wifeID                   =   $request->input('wifeID');
       $husbandID                =   $request->input('husbandID');
       $wifefullname             =   $request->input('wifefullname');
       $wifedob                  =   $request->input('wifedob');
       $marriagedate             =   $request->input('marriagedate');

       DB::table('tbldateofbirth_wife')
            ->where('particularID','=',$wifeID)
            ->where('staffid','=',$husbandID)
            ->update(['wifename'=>$wifefullname,'wifedateofbirth'=>$wifedob,'dateofmarriage'=>$marriagedate]);

       $result=$this->biodataFunction($husbandID); //function to reload and display data on form
       return $result;
   }

   //UPDATE PREVIOUS PUBLICSERVICE
    public function updatePUBLICSERVICE(Request $request){

       $serviceID                   =   $request->input('serviceID');
       $userID                      =   $request->input('userID');
       $preemployer                 =   $request->input('preemployer');
       $prefrom                     =   $request->input('prefrom');
       $preto                       =   $request->input('preto');
       $prepay                      =   $request->input('prepay');
       $prefileref                  =   $request->input('prefileref');
       $precheckedby                =   $request->input('precheckedby');

       DB::table('previous_servicedetails')
                ->where('doppsid','=',$serviceID)
                ->where('staffid','=',$userID)
                ->update(['previousSchudule'=>$preemployer,'fromDate'=>$prefrom,'toDate'=>$preto,'totalPreviousPay'=>$prepay,'filePageRef'=>$prefileref,'checkedby'=>$precheckedby]);

       $result=$this->biodataFunction($userID); //function to reload and display data on form
       return $result;
   }

   //UPDATE CENSORES AND COMMENDATIONS
    public function updateCENSORESANDCOMMENDATION(Request $request){

       $censorID                            =   $request->input('censorID');
       $censoruserID                        =   $request->input('censorUserID');
       $leavetype                           =   $request->input('leavetype');
       $leavefrom                           =   $request->input('leavefrom');
       $leaveto                             =   $request->input('leaveto');
       $numberdate                          =   $request->input('numberdate');
       $commendationdate                    =   $request->input('commendationdate');
       $censorfileref                       =   $request->input('censorfileref');
       $summary                             =   $request->input('summary');
       $checked_commendation                =   $request->input('checked_commendation');

       DB::table('tblcensures_commendations')
                ->where('id','=',$censorID)
                ->where('staffid','=',$censoruserID)
                ->update(['typeleave'=>$leavetype,'leavefrom'=>$leavefrom,'leaveto'=>$leaveto,'numberday'=>$numberdate,'commendationdate'=>$commendationdate,
                'fileref'=>$censorfileref,'summary'=>$summary,'checked_commendation'=>$checked_commendation]);

       $result=$this->biodataFunction($censoruserID); //function to reload and display data on form
       return $result;
   }

   //UPDATE GRATUITY
    public function updateGRATUITY(Request $request){

       $this->validate($request, [
           'periodOfYears' => 'required|numeric|min:1|max:35',
           'periodOfMonths' => 'required|numeric|min:1|max:12',
           'periodOfDays' => 'required|numeric|min:1|max:31',
           ]);


       $gratuityID                            =   $request->input('gratuityID');
       $gratuityUserID                        =   $request->input('gratuityUserID');
       $dateofpayment                         =   $request->input('dateofpayment');
       $periodfrom                            =   $request->input('periodfrom');
       $periodto                              =   $request->input('periodto');
       $periodyear                            =   $request->input('periodOfYears');
       $periodmonth                           =   $request->input('periodOfMonths');
       $periodday                             =   $request->input('periodOfDays');
       $rateofgratuity                        =   $request->input('rateofgratuity');
       $amountpaid                            =   $request->input('amountpaid');
       $pageref                               =   $request->input('pageref');
       $gratuitycheckedby                     =   $request->input('gratuitycheckedby');

       DB::table('tblgratuity_payment')
                ->where('id','=',$gratuityID)
                ->where('staffid','=',$gratuityUserID)
                ->update(['dateofpayment'=>$dateofpayment,'periodfrom'=>$periodfrom,'periodto'=>$periodto,'periodyear'=>$periodyear,'periodmonth'=>$periodmonth,
                'periodday'=>$periodday,'rateofgratuity'=>$rateofgratuity,'amountpaid'=>$amountpaid,'pageref'=>$pageref,'gratuitycheckedby'=>$gratuitycheckedby]);

       $result=$this->biodataFunction($gratuityUserID); //function to reload and display data on form
       return $result;
   }

   //UPDATE TERMINATION OF SERVICE DETAILS
   public function updateTERMINATIONSERVICE(Request $request){

       $terminateID                 =    $request->input('terminateID');
       $terminateUserID             =    $request->input('terminateUserID');
       $dateTerminated              =    $request->input('dateTerminated');
       $pension_contract_terminate  =    $request->input('pension_contract_terminate');
       $pensionAmount               =    $request->input('pensionAmount');
       $pensionperanumfrom          =    $request->input('pensionperanumfrom');
       $gratuity                    =    $request->input('gratuity');
       $contractGratuity            =    $request->input('contractGratuity');
       $dateOfDeath                 =    $request->input('dateOfDeath');
       $gratuityPaidEstate          =    $request->input('gratuityPaidEstate');
       $widowsPension               =    $request->input('widowsPension');
       $widowsPensionFrom           =    $request->input('widowsPensionFrom');
       $orphanPension               =    $request->input('orphanPension');
       $orphanPensionFrom           =    $request->input('orphanPensionFrom');
       $dateOfTransfer              =    $request->input('dateOfTransfer');
       $pension_contract_transfer   =    $request->input('pension_contract_transfer');
       $aggregateYears              =    $request->input('aggregateYears');
       $aggregateMonths             =    $request->input('aggregateMonths');
       $aggregateDays               =    $request->input('aggregateDays');
       $aggregateSalary             =    $request->input('aggregateSalary');

       DB::table('service_termination')
                  ->where('terminateID','=',$terminateID)
                  ->where('staffid','=',$terminateUserID)
                  ->update(['dateTerminated'=>$dateTerminated,'pension_contract_terminate'=>$pension_contract_terminate,'pensionAmount'=>$pensionAmount,
                  'pensionperanumfrom'=>$pensionperanumfrom,'gratuity'=>$gratuity,'contractGratuity'=>$contractGratuity,'dateOfDeath'=>$dateOfDeath,
                  'gratuityPaidEstate'=>$gratuityPaidEstate,'widowsPension'=>$widowsPension,'widowsPensionFrom'=>$widowsPensionFrom,'orphanPension'=>$orphanPension,
                  'orphanPensionFrom'=>$orphanPensionFrom,'dateOfTransfer'=>$dateOfTransfer,'pension_contract_transfer'=>$pension_contract_transfer,'aggregateYears'=>$aggregateYears,
                  'aggregateMonths'=>$aggregateMonths,'aggregateDays'=>$aggregateDays,'aggregateSalary'=>$aggregateSalary]);

       $result=$this->biodataFunction($terminateUserID); //function to reload and display data on form

       return $result;

   }

   //UPDATE TOUR AND LEAVE
   public function updateTOURLEAVE(Request $request){

       $tourLeaveID                 =    $request->input('tourLeaveID');
       $leaveUserID                 =    $request->input('leaveUserID');
       $dateTourStarted             =    $request->input('dateTourStarted');
       $tourGezetteNumber           =    $request->input('tourGezetteNumber');
       $lengthOfTour                =    $request->input('lengthOfTour');
       $leaveDueDate                =    $request->input('leaveDueDate');
       $leaveDepartDate             =    $request->input('leaveDepartDate');
       $leaveGezetteNumber          =    $request->input('leaveGezetteNumber');
       $leaveReturnDate             =    $request->input('leaveReturnDate');
       $dateExtensionGranted        =    $request->input('dateExtensionGranted');
       $salaryRuleForExt            =    $request->input('salaryRuleForExt');
       $dateResumedDuty             =    $request->input('dateResumedDuty');
       $toUK                        =    $request->input('toUK');
       $fromUK                      =    $request->input('fromUK');
       $residentMonths              =    $request->input('residentMonths');
       $residentDays                =    $request->input('residentDays');
       $leaveMonths                 =    $request->input('leaveMonths');
       $leaveDays                   =    $request->input('leaveDays');

       DB::table('tourleave_record')
                  ->where('tourLeaveID','=',$tourLeaveID)
                  ->where('staffid','=',$leaveUserID)
                  ->update(['dateTourStarted'=>$dateTourStarted,'tourGezetteNumber'=>$tourGezetteNumber,'lengthOfTour'=>$lengthOfTour,
                  'leaveDueDate'=>$leaveDueDate,'leaveDepartDate'=>$leaveDepartDate,'leaveGezetteNumber'=>$leaveGezetteNumber,'leaveReturnDate'=>$leaveReturnDate,
                  'dateExtensionGranted'=>$dateExtensionGranted,'salaryRuleForExt'=>$salaryRuleForExt,'dateResumedDuty'=>$dateResumedDuty,'toUK'=>$toUK,
                  'fromUK'=>$fromUK,'residentMonths'=>$residentMonths,'residentDays'=>$residentDays,'leaveMonths'=>$leaveMonths,
                  'leaveDays'=>$leaveDays]);

       $result=$this->biodataFunction($leaveUserID); //function to reload and display data on form

       return $result;

   }

   //UPDATE RECORD OF SERVICE
   public function updateSERVICERECORD(Request $request){

       $data['getIDs']=0;
       $recID                 =    $request->input('recID');
       $serviceUserID         =    $request->input('serviceUserID');
       $entryDate             =    $request->input('entryDate');
       $detail                =    $request->input('detail');
       $signature             =    $request->input('signature');
       $namestamp             =    $request->input('namestamp');

       DB::table('recordof_service')
                  ->where('recID','=',$recID)
                  ->where('staffid','=',$serviceUserID)
                  ->update(['entryDate'=>$entryDate,'detail'=>$detail,'signature'=>$signature,'namestamp'=>$namestamp]);

       $result=$this->biodataFunction($serviceUserID); //function to reload and display data on form
       return $result;


   }

   //UPDATE RECORD OF EMOLUMENT
   public function updateEMOLUMENT(Request $request){

       $data['getIDs']=0;
       $emolumentID            =    $request->input('emolumentID');
       $emolumentUserID        =    $request->input('emolumentUserID');
       $eentryDate             =    $request->input('eentryDate');
       $salaryScale            =    $request->input('salaryScale');
       $basicSalaryPA          =    $request->input('basicSalaryPA');
       $inducementPayPA        =    $request->input('inducementPayPA');
       $datePaidFrom           =    $request->input('datePaidFrom');
       $month                  =    $request->input('month');
       $year                   =    $request->input('year');
       $authority              =    $request->input('authority');
       $ssignature             =    $request->input('ssignature');

       DB::table('recordof_emolument')
                  ->where('emolumentID','=',$emolumentID)
                  ->where('staffid','=',$emolumentUserID)
                  ->update(['entryDateMade'=>$eentryDate,'salaryScale'=>$salaryScale,'basicSalaryPA'=>$basicSalaryPA,'inducementPayPA'=>$inducementPayPA,
                  'datePaidFrom'=>$datePaidFrom,'month'=>$month,'year'=>$year,'authority'=>$authority,'signature'=>$ssignature]);

       $result=$this->biodataFunction($emolumentUserID); //function to reload and display data on form
       return $result;


   }

   //UPDATE PROFILE PICTURE
   public function updatePROFILEPICTURE(Request $request){

        $this->validate($request, [

          'fileNo'      => 'required|string',
          'filename' => 'mimes:jpeg,jpg,gif,png,bmp|max:8300',

            ]);


       $fileID          =    $request->input('fileNo');
       $appdate         =    $request->input('appointment_date');
       $firstdate       =    $request->input('firstarrival_date');
       $emptype         =    $request->input('employee_type');
       $designation     =    $request->input('designation');
       $department      =    $request->input('department');
       $section         =    $request->input('section');
       $grade           =    $request->input('grade');
       $step            =    $request->input('step');
       $bank            =    $request->input('bank');
       $bankgroup       =    $request->input('bankgroup');
       $bankbranch      =    $request->input('bankbranch');
       $accno           =    $request->input('accno');
       $nhfno           =    $request->input('nhfno');
       $incrementaldate =    $request->input('incrementaldate');
       $data['getIDs']=0;

       if($request->hasfile('filename'))
		         {
		            $file=$request->file('filename');

		               $name=$file->getClientOriginalName();
		                $file->move(public_path().'/../../payroll.njc.gov.ng/passport/', $name);

		               DB::table('tblper')
		               ->where('ID','=',$fileID)
		               ->update([
		               'picture' => $name,
		               //'fileNo' => $fileID,
		               ]);


		         }


       DB::table('tblper')->where('ID','=',$fileID)
                  ->update(['appointment_date'=>$appdate,'firstarrival_date'=>$firstdate,
                  'employee_type'=>$emptype,'designation'=>$designation,'department'=>$department,'section'=>$section,
                  'grade'=>$grade,'step'=>$step,'bankID'=>$bank,'bankGroup'=>$bankgroup,'bank_branch'=>$bankbranch,
                  'AccNo'=>$accno,'nhfNo'=>$nhfno,'incremental_date'=>$incrementaldate]);

       $result=$this->biodataFunction($fileID); //function to reload and display data on form

       return $result;
   }

    public function details(Request $request)
    {


        $data['getTitles']=DB::table('tbltitle')->get();
        $data['getDivision']=DB::table('tbldivision')->get();
        $data['getGender']=DB::table('tblgender')->get();
        $data['getState']=DB::table('tblstates')->get();
        $data['getMS']=DB::table('tblmaritalStatus')->get();
        $data['getEmpType']=DB::table('tblemployment_type')->get();
        $data['getDesignation']=DB::table('tbldesignation')->get();
        $data['getDepartment']=DB::table('tbldepartment')->get();
        $data['getGrade']=DB::table('tblgrades')->get();
        $data['getStep']=DB::table('tblsteps')->get();
        $data['getBank']=DB::table('tblbanklist')->get();

        //$this->validate($request, [
            //'fileNo' => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
        //]);
        $data['getIDs']=DB::table('tblprofileconfig')->first();

        //DD(Auth::user()->id);
        $getStaffID=DB::table('tblper')->where('UserID', Auth::user()->id)->first();
        if(!$getStaffID){
            return back();
        }
        //dd($getStaffID->ID);
        $fileNo = ($getStaffID ? $getStaffID->ID : '');

        //check if staff belongs to this this->division
        $getstaffDiv = DB::table('tblper')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->where('tblper.ID', '=', $fileNo)
                ->select('division')
                ->first();

          //check if you can view
         /*if(!(DB::table('tblper')->select('divisionID')->where('fileNo', '=', $fileNo)->where('divisionID', $this->divisionID)->count())){
            return back()->with('err', 'Staff Details cannot be viewed in this Division. Staff belongs to '. $getstaffDiv->division .'  division. This means, Staff can only be viewed from '. $getstaffDiv->division .' division');
         }*/
        //end checking

        //Bid-Data
        if(DB::table('tblper')->where('ID', $fileNo)->count() > 0){
                $data['staffFullDetails'] = DB::table('tblper')
                ->leftjoin('tblstatus', 'tblstatus.id', '=', 'tblper.staff_status')
                ->leftjoin('tblstates', 'tblstates.id', '=', 'tblper.stateID')
                ->leftjoin('tbldesignation', 'tblper.Designation', '=', 'tbldesignation.id')
                ->leftjoin('tblsections', 'tblsections.id', '=', 'tblper.section')
                ->leftjoin('tbltitle', 'tbltitle.id', '=', 'tblper.title')
                ->leftjoin('tblgender', 'tblgender.ID', '=', 'tblper.gender')
                ->leftjoin('tblemployment_type', 'tblemployment_type.id', '=', 'tblper.employee_type')
                ->leftjoin('tbldepartment', 'tbldepartment.id', '=', 'tblper.department')
                ->leftjoin('tblmaritalStatus', 'tblmaritalStatus.ID', '=', 'tblper.maritalstatus')
                ->leftjoin('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->leftjoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->select('*', 'tblper.ID as staffID','tbltitle.ID as titleID','tblgender.ID as genderID','tblstates.ID as stateID','tbldivision.divisionID as divID','tblmaritalStatus.ID as msID','tblemployment_type.id as empID','tbldepartment.id as deptID','tblbanklist.bankID as bankID')
                ->where('tblper.ID', '=', $fileNo)
                ->first();

        }else{
            $data['staffFullDetails']    = "";
        }




        //check for profile image
        if(File::exists(base_path() . '/public/passport/' . ($data['staffFullDetails'] ? $data['staffFullDetails']->fileNo .'.jpg' : '') )) //check folder
        {
            $data['fileNoImage'] = ($data['staffFullDetails'] ? $data['staffFullDetails']->fileNo .".jpg" : '');
        }else{
            $data['fileNoImage'] = "default.png";
        }


        //Education
        if($count=DB::table('tbleducations')->where('staffid', $fileNo)->count() > 0){
            //if( $count==1 ){
                //$data['countedu']=DB::table('tbleducations')->where('fileNo', $fileNo)->count();
                $data['staffFullDetailsEducation'] = DB::table('tbleducations')->where('staffid', $fileNo)->get();
                //$daastaffFullDetailsEducation = DB::table('tbleducations')->where('fileNo', $fileNo)->first();
            //}
            //else{

                //$data['staffFullDetailsEducation2'] = DB::table('tbleducations')->where('fileNo', $fileNo)->get();
           //}
            //,'{{ $edu->degreequalification }}','{{ $edu->schoolattended }}','{{ $edu->schoolfrom }}','{{ $edu->schoolto }}','{{ $edu->certificateheld }}'
        }else{

                $data['staffFullDetailsEducation']    = "";
                //$data['staffFullDetailsEducation'] = DB::table('tbleducations')->where('fileNo', $fileNo)->first();
        }


        //Languages
        if(DB::table('languages')->where('staffid', $fileNo)->count() > 0){
            $data['staffFullDetailsLanguage']    = DB::table('languages')->where('staffid', $fileNo)->get();
        }else{
            $data['staffFullDetailsLanguage']    = "";
        }

        //particulars of Children
        if(DB::table('tblchildren_particulars')->where('staffid', $fileNo)->count() > 0){
            $data['staffFullDetailsChildren']    = DB::table('tblchildren_particulars')
                                                        ->where('fileNo', $fileNo)
                                                        ->leftjoin('tblgender', 'tblchildren_particulars.gender', '=', 'tblgender.ID')
                                                        ->select('*','tblgender.ID as gID')
                                                        ->get();

        }else{
            $data['staffFullDetailsChildren']    = "";
        }

        //Details of previous service
        if(DB::table('previous_servicedetails')->where('staffid', $fileNo)->count() > 0){
            $data['staffFullDetailsPreviousService']    = DB::table('previous_servicedetails')
                                                          ->where('staffid', $fileNo)
                                                          ->get();
        }else{
            $data['staffFullDetailsPreviousService']    = "";
        }


        //Details of service in the forces
        if(DB::table('detailsofservice')->where('staffid', $fileNo)->count() > 0){
            $data['staffFullDetailsDetailsService']    = DB::table('detailsofservice')->where('staffid', $fileNo)->get();
        }else{
            $data['staffFullDetailsDetailsService']    = "";
        }


        //Record of censures and recommendations
        if(DB::table('tblcensures_commendations')->where('staffid', $fileNo)->count() > 0){
            $data['staffFullDetailsCensure']    = DB::table('tblcensures_commendations')->where('staffid', $fileNo)->get();
        }else{
            $data['staffFullDetailsCensure']    = "";
        }

        //Gratuity Payment
        if(DB::table('tblgratuity_payment')->where('staffid', $fileNo)->count() > 0){
            $data['staffFullDetailsGratuityPayment']    = DB::table('tblgratuity_payment')->where('staffid', $fileNo)->get();
        }else{
            $data['staffFullDetailsGratuityPayment']    = "";
        }


        //Particular of termination of service
        if(DB::table('service_termination')->where('staffid', $fileNo)->count() > 0){
            $data['staffFullDetailsTerminationService']    = DB::table('service_termination')->where('staffid', $fileNo)->get();
        }else{
            $data['staffFullDetailsTerminationService']    = "";
        }

        //Particular of tour and leave
        if(DB::table('tourleave_record')->where('fileNo', $fileNo)->count() > 0){
            $data['staffFullDetailsTourLeaveRecord']    = DB::table('tourleave_record')->where('staffid', $fileNo)->get();
        }else{
            $data['staffFullDetailsTourLeaveRecord'] = "";
        }


        //Record of service
        if(DB::table('recordof_service')->where('staffid', $fileNo)->count() > 0){
            $data['staffFullDetailsRecordService']    = DB::table('recordof_service')->where('staffid', $fileNo)->get();
        }else{
            $data['staffFullDetailsRecordService'] = "";
        }


        //Record of record of emolument
        if(DB::table('recordof_emolument')->where('staffid', $fileNo)->count() > 0){
            $data['staffFullDetailsRecordEmolument'] = DB::table('recordof_emolument')
                                                       //->join('recordof_service', 'recordof_service.recID', '=', 'recordof_emolument.entryDateMade')
                                                       ->where('recordof_emolument.staffid', $fileNo)
                                                       ->get();
        }else{
            $data['staffFullDetailsRecordEmolument'] = "";
        }

        //Next of Kin
        if(DB::table('tblnextofkin')->where('staffid', $fileNo)->count() > 0){

            $data['nextOfKin'] = DB::table('tblnextofkin')
                                           ->where('staffid', $fileNo)
                                           ->get();

        }else{
            $data['nextOfKin']       = "";
         }

        //get particular of wife
        if(DB::table('tbldateofbirth_wife')->where('staffid', $fileNo)->count() > 0){

            $data['staffFullDetailsWife'] = DB::table('tbldateofbirth_wife')
                                                ->where('staffid', $fileNo)
                                                ->get();

        }else{
            $data['staffFullDetailsWife']       = "";

        }

        /*GET TOTAL RECORDS*/
        //count Next of kin
        $data['totalNextofKin']             = DB::table('tblnextofkin')->where('staffid', $fileNo)->count();
        //count education
        $data['totalEducation']             = DB::table('tbleducations')->where('staffid', $fileNo)->count();
        //count particular of wife
        $data['totalParticularOfWife']      = DB::table('tbldateofbirth_wife')->where('staffid', $fileNo)->count();
        //count languages
        $data['totallanguages']             = DB::table('languages')->where('staffid', $fileNo)->count();
        //count children
        $data['totalChildren']              = DB::table('tblchildren_particulars')->where('staffid', $fileNo)->count();
        //count Details of service in the forces
        $data['totalDetailsService']        = DB::table('detailsofservice')->where('staffid', $fileNo)->count();
        //count Details of previous service
        $data['totalPreviousService']       = DB::table('previous_servicedetails')->where('staffid', $fileNo)->count();
        //count Details of previous service
        $data['totalRecordCensures']        = DB::table('tblcensures_commendations')->where('staffid', $fileNo)->count();
        //count gratuity payment
        $data['totalGratuityPayment']       = DB::table('tblgratuity_payment')->where('staffid', $fileNo)->count();
        //count Termination of service
        $data['totalTerminationService']    = DB::table('service_termination')->where('staffid', $fileNo)->count();
        //count Termination of service
        $data['totalTourLeave']             = DB::table('tourleave_record')->where('staffid', $fileNo)->count();
        //count Record of services
        $data['totalRecordService']         = DB::table('recordof_service')->where('staffid', $fileNo)->count();
        //count Record of Emoluments
        $data['totalRecordEmolument']       = DB::table('recordof_emolument')->where('staffid', $fileNo)->count();

        return view('profile.selfservice', $data);
    }


    //STAFF LIST BY CADRE
    public function view_ALL_CADRE_LIST_REFRESH()
    {
        $filterCadre    = Session::forget('filterCadre');      //get/pull SESSION CADER
        $filterDivision = Session::forget('filterDivision');  //get/pull SESSION DIVISION
        $getMonthDay    = Session::forget('getMonthDay');        //get/pull SESSION MONTH AND DAY SEARCH
        $filterBy       = Session::forget('filterBy');         //get/pull SESSION AUTO-SEARCH by NAME or FILENO

        return redirect()->route('recordVariationLoadCadre');
    }

    //STAFF LIST BY CADRE
    public function view_ALL_CADRE_LIST()
    {
        $filterCadre    = Session::get('filterCadre');      //get/pull SESSION CADER
        $filterDivision = Session::get('filterDivision');  //get/pull SESSION DIVISION
        $getMonthDay    = Session::get('getMonthDay');        //get/pull SESSION MONTH AND DAY SEARCH
        $filterBy       = Session::get('filterBy');         //get/pull SESSION AUTO-SEARCH by NAME or FILENO
        $data['getAllAlert'] = $this->getAlertIncrementPromotion();

        if($filterDivision =="" and $filterCadre == "" and $getMonthDay <> "")
        {
            //get All staff due for INCREMENT IN $this->division and $this->month (by form $_GET)
            $data['getCentralList'] = DB::table('tblper')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->WhereMonth('tblper.appointment_date', '=', (Carbon::today()->month))
                ->WhereDay('tblper.appointment_date', '=', (Carbon::today()->day))
                ->where('tblper.divisionID', '=', $this->divisionID)
                ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
                ->where('tblper.staff_status', 1)
                ->orderBy('tblper.grade', 'Desc')
                ->orderBy('tblper.step', 'Desc')
                ->orderBy('tblper.appointment_date', 'Asc')
                ->paginate(10);
            $data['getDivision'] = DB::table('tbldivision')->orderBy('division', 'Asc')->get();
            $data['headFile'] = "LIST OF ALL STAFF DUE FOR INCREMENT THIS MONTH IN ".$this->division;
            return view('profile.allStaffList', $data);
        }

        //search staff record based on POST from auto-search
        if(($getMonthDay == "") and ($filterDivision == "") and ($filterCadre == "") and ($filterBy <> ""))
        {
            $data['getCentralList'] = DB::table('tblper')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->where('tblper.surname', 'LIKE', '%'.$filterBy.'%')
                ->orWhere('tblper.first_name', 'LIKE', '%'.$filterBy.'%')
                ->orWhere('tblper.fileNo', 'LIKE','%'.$filterBy.'%')
                ->orWhere('tblper.appointment_date', 'LIKE','%'.$filterBy.'%')
                ->where('tblper.divisionID', '=', $this->divisionID)
                ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
                ->where('tblper.staff_status', 1)
                ->orderBy('tblper.grade', 'Desc')
                ->orderBy('tblper.step', 'Desc')
                ->orderBy('tblper.appointment_date', 'Asc')
                ->paginate(10);
            $data['getDivision'] = DB::table('tbldivision')->orderBy('division', 'Asc')->get();
            $data['headFile'] = "All STAFF LIST";
            return view('profile.allStaffList', $data);
        }

        if($filterDivision =="" and $filterCadre <> "")
        {
            $data['getCentralList'] = DB::table('tblper')
                    ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                    ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                    ->where('tblper.section', $filterCadre)
                    ->where('tblper.divisionID', '=', $this->divisionID)
                    ->where('tblper.staff_status', 1)
                    ->orderBy('tblper.grade', 'Desc')
                    ->orderBy('tblper.step', 'Desc')
                    ->orderBy('tblper.appointment_date', 'Asc')
                    ->paginate(10);
            $data['getcadre'] = DB::table('tblper')->select('section')->orderBy('section', 'Asc')->distinct()->get();
            $data['headFile'] = 'STAFF LIST for ' . $filterCadre . ' CADRE';
            $data['getDivision'] = DB::table('tbldivision')->orderBy('division', 'Asc')->get();
            return view('profile.allStaffList', $data);
        }else if($filterDivision <> "" and $filterCadre <> ""){
            $data['getCentralList'] = DB::table('tblper')
                    ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                    ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                    ->where('tblper.section', $filterCadre)
                    ->where('tblper.divisionID', $filterDivision)
                    ->where('tblper.staff_status', 1)
                    ->orderBy('tblper.grade', 'Desc')
                    ->orderBy('tblper.step', 'Desc')
                    ->orderBy('tblper.appointment_date', 'Asc')
                    ->paginate(10);
            $getDivName = DB::table('tbldivision')->select('division')->where('divisionID', $filterDivision)->first();
            $data['getcadre'] = DB::table('tblper')->select('section')->orderBy('section', 'Asc')->distinct()->get();
            $data['headFile'] = 'STAFF LIST for ' . $filterCadre . ' CADRE - ' . $getDivName->division .' Division';
            $data['getDivision'] = DB::table('tbldivision')->orderBy('division', 'Asc')->get();
            return view('profile.allStaffList', $data);
        }else if($filterCadre == "" and $filterDivision <> ""){
            $data['getCentralList'] = DB::table('tblper')
                    ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                    ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                    ->where('tblper.divisionID', $filterDivision)
                    ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
                    ->where('tblper.staff_status', 1)
                    ->orderBy('tblper.grade', 'Desc')
                    ->orderBy('tblper.step', 'Desc')
                    ->orderBy('tblper.appointment_date', 'Asc')
                    ->paginate(10);
            $data['getcadre'] = DB::table('tblper')->select('section')->orderBy('section', 'Asc')->distinct()->get();
            $getDivName = DB::table('tbldivision')->select('division')->where('divisionID', $filterDivision)->first();
            $data['headFile'] = 'STAFF LIST for ' . $getDivName->division . ' Division';
            $data['getDivision'] = DB::table('tbldivision')->orderBy('division', 'Asc')->get();
            return view('profile.allStaffList', $data);
        }else{
            $data['getCentralList'] = DB::table('tblper')
                    ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                    ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                    ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
                    ->where('tblper.divisionID', '=', $this->divisionID)
                    ->where('tblper.staff_status', 1)
                    ->orderBy('tblper.grade', 'Desc')
                    ->orderBy('tblper.step', 'Desc')
                    ->orderBy('tblper.appointment_date', 'Asc')
                    ->paginate(10);
            $data['getcadre'] = DB::table('tblper')->select('section')->orderBy('section', 'Asc')->distinct()->get();
             $data['headFile'] = 'All STAFF LIST';
            $data['getDivision'] = DB::table('tbldivision')->orderBy('division', 'Asc')->get();
            return view('profile.allStaffList', $data);
        }
    }

    public function view_ALL_CADRE_LIST_FILTER(Request $request)
    {
        $filterCadre_raw    = trim($request['filterCadre']);
        $filterDivision_raw = trim($request['filterDivision']);
        $monthDay           = trim($request['monthDay']);
        $filterBy           = trim($request['fileNo']);
        $data['getAllAlert'] = '';

       //search by today's day
        if(($filterCadre_raw == "") and ($filterDivision_raw == "") and ($monthDay != ""))
        {
            //get All staff due for INCREMENT IN $this->division and $this->month (by form $_POST)
            Session::forget('filterCadre');
            Session::forget('filterDivision');
            Session::put('getMonthDay',  Carbon::today()->month);    // PUT/SET session Division
            return redirect('/record-variation/view/cadre');
        }

        if($filterDivision_raw =="" and $filterCadre_raw <> "")
        {
            Session::forget('filterDivision');
            Session::forget('getMonthDay');
            Session::forget('filterBy');
            Session::put('filterCadre',  $filterCadre_raw);
            return redirect('/record-variation/view/cadre');
        }else if($filterDivision_raw <> "" and $filterCadre_raw <> "")
        {
            Session::forget('getMonthDay');
            Session::forget('filterBy');
            Session::put('filterDivision',  $filterDivision_raw);
            Session::put('filterCadre',  $filterCadre_raw);
            return redirect('/record-variation/view/cadre');
        }else if($filterCadre_raw == "" and $filterDivision_raw <> "")
        {
            Session::forget('getMonthDay');
            Session::forget('filterBy');
            Session::forget('filterCadre');
            Session::put('filterDivision',  $filterDivision_raw);
            return redirect('/record-variation/view/cadre');
        }else if( ($filterCadre_raw == "") and ($filterDivision_raw == "") and ($monthDay =="") )
        {
            // PUT/SET session for filterBy fileNos or Names
            Session::forget('filterCadre');
            Session::forget('filterDivision');
            Session::forget('getMonthDay');
            Session::put('filterBy',  $filterBy);
            return redirect('/record-variation/view/cadre');
        }
        else{
            // PUT/SET session for filterBy fileNos or Names
            Session::forget('filterCadre');
            Session::forget('filterDivision');
            Session::forget('getMonthDay');
            Session::forget('filterBy');
            return redirect('/record-variation/view/cadre');
        }
    }



    //STAFF LIST that due for increament
    public function view_ALL_INCREMENT_SO_FAR()
    {
        Session::forget('filterCadre');
        Session::forget('filterDivision');
        Session::forget('getMonthDay');
        Session::forget('filterBy');
        $data['getAllAlert'] = $this->getAlertIncrementPromotion();

        //get All staff due for INCREMENT SO FAR
        $data['getCentralList'] = DB::table('tblper')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->Where('tblper.step', '<>', 'tblper.stepalert')
                ->Where('tblper.stepalert', '<>', "")
                ->where('tblper.divisionID', '=', $this->divisionID)
                ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
                ->where('tblper.staff_status', 1)
                ->orderBy('tblper.grade', 'Desc')
                ->orderBy('tblper.step', 'Desc')
                ->orderBy('tblper.appointment_date', 'Asc')
                ->paginate(10);
        $data['getDivision'] = DB::table('tbldivision')->orderBy('division', 'Asc')->get();
        $data['headFile'] = "LIST OF ALL STAFF DUE FOR INCREMENT THIS MONTH IN ".$this->division;
        Session::put('hideAlert', 1);
        return view('profile.allStaffList', $data);
    }




    public function showAll(Request $request)
    {
    dd('dwade');
        $term=$request->input('nameID');
        DB::enableQueryLog();
        $data = DB::table('tblper')
            ->leftJoin('tblnextofkin', 'tblper.fileNo', '=', 'tblnextofkin.fileNo')
            ->where('tblper.fileNo', '=', $term)
            ->select('*')
            ->get();
        return response()->json($data);
    }    //end function ShowAll


    //reload data on form function after editing
    public function biodataFunction($ID)
    {
        $data['getIDs']=0;
        $data['getTitles']=DB::table('tbltitle')->get();
        $data['getDivision']=DB::table('tbldivision')->get();
        $data['getGender']=DB::table('tblgender')->get();
        $data['getState']=DB::table('tblstates')->get();
        $data['getMS']=DB::table('tblmaritalStatus')->get();
        $data['getEmpType']=DB::table('tblemployment_type')->get();
        $data['getDesignation']=DB::table('tbldesignation')->get();
        $data['getDepartment']=DB::table('tbldepartment')->get();
        $data['getGrade']=DB::table('tblgrades')->get();
        $data['getStep']=DB::table('tblsteps')->get();
        $data['getBank']=DB::table('tblbanklist')->get();

        $fileNo = $ID;

        //check if staff belongs to this this->division
        $getstaffDiv = DB::table('tblper')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->where('tblper.ID', '=', $fileNo)
                ->select('division')
                ->first();

          //check if you can view
         /*if(!(DB::table('tblper')->select('divisionID')->where('fileNo', '=', $fileNo)->where('divisionID', $this->divisionID)->count())){
            return back()->with('err', 'Staff Details cannot be viewed in this Division. Staff belongs to '. $getstaffDiv->division .'  division. This means, Staff can only be viewed from '. $getstaffDiv->division .' division');
         }*/
        //end checking

        //Bid-Data
        if(DB::table('tblper')->where('ID', $fileNo)->count() > 0){
                $data['staffFullDetails'] = DB::table('tblper')
                ->leftjoin('tblstatus', 'tblstatus.id', '=', 'tblper.staff_status')
                ->leftjoin('tblstates', 'tblstates.id', '=', 'tblper.stateID')
                ->leftjoin('tbltitle', 'tbltitle.id', '=', 'tblper.title')
                //->leftjoin('profilepicture', 'tblper.ID', '=', 'profilepicture.fileNo')
                ->leftjoin('tblgender', 'tblgender.ID', '=', 'tblper.gender')
                ->leftjoin('tblemployment_type', 'tblemployment_type.id', '=', 'tblper.employee_type')
                ->leftjoin('tbldepartment', 'tbldepartment.id', '=', 'tblper.department')
                ->leftjoin('tblmaritalStatus', 'tblmaritalStatus.ID', '=', 'tblper.maritalstatus')
                ->leftjoin('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->leftjoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->select('*', 'tblper.ID as staffID','tbltitle.ID as titleID','tblgender.ID as genderID','tblstates.ID as stateID','tbldivision.divisionID as divID','tblmaritalStatus.ID as msID','tblemployment_type.id as empID','tbldepartment.id as deptID','tblbanklist.bankID as bankID')
                ->where('tblper.ID', '=', $fileNo)
                ->first();

                //$data['staffID']=DB::table('tblper')->where('tblper.ID', '=', $fileNo)->first();
        }else{
            $data['staffFullDetails']    = "";
        }


        //check for profile image
        if(File::exists(base_path() . '/public/passport/' . $data['staffFullDetails']->fileNo .'.jpg' )) //check folder
        {
            $data['fileNoImage'] = $data['staffFullDetails']->fileNo .".jpg";
        }else{
            $data['fileNoImage'] = "default.png";
        }


        //Education
        if(DB::table('tbleducations')->where('staffid', $fileNo)->count() > 0){
            $data['staffFullDetailsEducation']    = DB::table('tbleducations')->where('staffid', $fileNo)->get();
        }else{
            $data['staffFullDetailsEducation']    = "";
        }


        //Languages
        if(DB::table('languages')->where('staffid', $fileNo)->count() > 0){
            $data['staffFullDetailsLanguage']    = DB::table('languages')->where('staffid', $fileNo)->get();
        }else{
            $data['staffFullDetailsLanguage']    = "";
        }


        //particulars of Children
        if(DB::table('tblchildren_particulars')->where('staffid', $fileNo)->count() > 0){
            $data['staffFullDetailsChildren']    = DB::table('tblchildren_particulars')
                                                        ->where('staffid', $fileNo)
                                                        ->leftjoin('tblgender', 'tblchildren_particulars.gender', '=', 'tblgender.ID')
                                                        ->select('*','tblgender.ID as gID')
                                                        ->get();

        }else{
            $data['staffFullDetailsChildren']    = "";
        }

        //Details of previous service
        if(DB::table('previous_servicedetails')->where('staffid', $fileNo)->count() > 0){
            $data['staffFullDetailsPreviousService']    = DB::table('previous_servicedetails')->where('staffid', $fileNo)->get();
        }else{
            $data['staffFullDetailsPreviousService']    = "";
        }


        //Details of service in the forces
        if(DB::table('detailsofservice')->where('staffid', $fileNo)->count() > 0){
            $data['staffFullDetailsDetailsService']    = DB::table('detailsofservice')->where('staffid', $fileNo)->get();
        }else{
            $data['staffFullDetailsDetailsService']    = "";
        }


        //Record of censures and recommendations
        if(DB::table('tblcensures_commendations')->where('staffid', $fileNo)->count() > 0){
            $data['staffFullDetailsCensure']    = DB::table('tblcensures_commendations')->where('staffid', $fileNo)->get();
        }else{
            $data['staffFullDetailsCensure']    = "";
        }

        //Gratuity Payment
        if(DB::table('tblgratuity_payment')->where('staffid', $fileNo)->count() > 0){
            $data['staffFullDetailsGratuityPayment']    = DB::table('tblgratuity_payment')->where('staffid', $fileNo)->get();
        }else{
            $data['staffFullDetailsGratuityPayment']    = "";
        }


        //Particular of termination of service
        if(DB::table('service_termination')->where('staffid', $fileNo)->count() > 0){
            $data['staffFullDetailsTerminationService']    = DB::table('service_termination')->where('staffid', $fileNo)->get();
        }else{
            $data['staffFullDetailsTerminationService']    = "";
        }

        //Particular of termination of service
        if(DB::table('tourleave_record')->where('staffid', $fileNo)->count() > 0){
            $data['staffFullDetailsTourLeaveRecord']    = DB::table('tourleave_record')->where('staffid', $fileNo)->get();
        }else{
            $data['staffFullDetailsTourLeaveRecord'] = "";
        }


        //Record of service
        if(DB::table('recordof_service')->where('staffid', $fileNo)->count() > 0){
            $data['staffFullDetailsRecordService']    = DB::table('recordof_service')->where('staffid', $fileNo)->get();
        }else{
            $data['staffFullDetailsRecordService'] = "";
        }


        //Record of emolument
        if(DB::table('recordof_emolument')->where('staffid', $fileNo)->count() > 0){
            $data['staffFullDetailsRecordEmolument'] = DB::table('recordof_emolument')
                                                       //->join('recordof_service', 'recordof_service.recID', '=', 'recordof_emolument.entryDateMade')
                                                       ->where('recordof_emolument.staffid', $fileNo)
                                                       ->get();
        }else{
            $data['staffFullDetailsRecordEmolument'] = "";
        }

        //Next of Kin
        if(DB::table('tblnextofkin')->where('staffid', $fileNo)->count() > 0){

            $data['nextOfKin'] = DB::table('tblnextofkin')
                                    ->where('staffid', $fileNo)
                                    ->get();

        }else{
            $data['nextOfKin'] = "";
        }

       //get particular of wife
        if(DB::table('tbldateofbirth_wife')->where('staffid', $fileNo)->count() > 0){

            $data['staffFullDetailsWife'] = DB::table('tbldateofbirth_wife')
                                                ->where('staffid', $fileNo)
                                                ->get();

        }else{
            $data['staffFullDetailsWife']       = "";

        }

        /*GET TOTAL RECORDS*/
        //count Next of kin
        $data['totalNextofKin']             = DB::table('tblnextofkin')->where('staffid', $fileNo)->count();
        //count education
        $data['totalEducation']             = DB::table('tbleducations')->where('staffid', $fileNo)->count();
        //count particular of wife
        $data['totalParticularOfWife']      = DB::table('tbldateofbirth_wife')->where('staffid', $fileNo)->count();
        //count languages
        $data['totallanguages']             = DB::table('languages')->where('staffid', $fileNo)->count();
        //count children
        $data['totalChildren']              = DB::table('tblchildren_particulars')->where('staffid', $fileNo)->count();
        //count Details of service in the forces
        $data['totalDetailsService']        = DB::table('detailsofservice')->where('staffid', $fileNo)->count();
        //count Details of previous service
        $data['totalPreviousService']       = DB::table('previous_servicedetails')->where('staffid', $fileNo)->count();
        //count Details of previous service
        $data['totalRecordCensures']        = DB::table('tblcensures_commendations')->where('staffid', $fileNo)->count();
        //count gratuity payment
        $data['totalGratuityPayment']       = DB::table('tblgratuity_payment')->where('staffid', $fileNo)->count();
        //count Termination of service
        $data['totalTerminationService']    = DB::table('service_termination')->where('staffid', $fileNo)->count();
        //count Termination of service
        $data['totalTourLeave']             = DB::table('tourleave_record')->where('staffid', $fileNo)->count();
        //count Record of services
        $data['totalRecordService']         = DB::table('recordof_service')->where('staffid', $fileNo)->count();
        //count Record of Emoluments
        $data['totalRecordEmolument']       = DB::table('recordof_emolument')->where('staffid', $fileNo)->count();

        return view('profile.selfservice', $data);
    }

    //processing json for populating of educational details
    public function loadEducation(Request $request)
    {
          $fileID = Input::get('fileID');

          $data = DB::table('tbleducations')->where('fileNo', '=',$fileID)->get();
          return response()->json($data);
    }

     //processing json for populating of dob details
    public function loadSalaryInfo(Request $request)
    {
          $fileID = Input::get('fileID');

          $data = DB::table('tblper')->where('fileNo', '=',$fileID)->get();
          return response()->json($data);
    }


} //end class ProfileController
