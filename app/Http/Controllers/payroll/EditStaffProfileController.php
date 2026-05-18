<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;
use File;
use App\Http\Requests;
use DB;

class EditStaffProfileController extends ParentController
{

    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->division = $request->session()->get('division');
        $this->divisionID = $request->session()->get('divisionID');
                return $next($request);
        });
    }

    //start loading Edit pages
    public function viewEditBioData($ID = null, $fileNo = null)
    {
        $getCheckID = DB::table('tblper')
                ->where('ID', '=', $ID)
                ->select('fileNo')
                ->first();
        if(($getCheckID->fileNo) == $fileNo){
            $data['StateList'] = DB::table('tblstates')->select('StateID', 'State')->orderBy('State')->get();
            $data['bankList'] = DB::table('tblbanklist')->select('bankID', 'bank')
                              ->orderBy('bank', 'asc')->get();
            $data['staffDetails'] = DB::table('tblper')
                                  ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                                  ->where('tblper.ID', $ID)
                                  ->where('tblper.fileNo', $fileNo)
                                  ->first();
            //check for profile image
            if(File::exists(base_path() . '/public/passport/' . $data['staffDetails']->fileNo .'.jpg' )) //check folder
            {
                $data['fileNoImage'] = $data['staffDetails']->fileNo .".jpg";
            }else{
                $data['fileNoImage'] = "default.png";
            }
        
            return view('createstaff.edit',$data);
        }
       return back()->with('err', 'Staff Details not found');
    }

    



    public function details($fileNo = null)
    {   
        
        //check if staff belongs to this this->division
        if(!(DB::table('tblper')->where('fileNo', '=', $fileNo)->first())){
             return redirect('/profile/details')->with('err', 'Staff Details not Found !');
        }
        $getstaffDiv = DB::table('tblper')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->where('tblper.fileNo', '=', $fileNo)
                ->select('division')
                ->first();
         if(!(DB::table('tblper')->select('divisionID')->where('fileNo', '=', $fileNo)->where('divisionID', $this->divisionID)->count())){
            return back()->with('err', 'Staff Details cannot be viewed in this Division. Staff belongs to '. $getstaffDiv->division .'  division. This means, Staff can only be viewed from '. $getstaffDiv->division .' division');
            //dd('Not here');
         }
        //end checking
         

       //Bid-Data
        if(DB::table('tblper')->where('fileNo', $fileNo)->count() > 0){
                $data['staffFullDetails'] = DB::table('tblper')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->where('tblper.fileNo', '=', $fileNo)
                ->first();
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
        if(DB::table('tbleducations')->where('fileNo', $fileNo)->count() > 0){
            $data['staffFullDetailsEducation']    = DB::table('tbleducations')->where('fileNo', $fileNo)->get();
        }else{
            $data['staffFullDetailsEducation']    = "";
        }


        //Languages
        if(DB::table('languages')->where('fileNo', $fileNo)->count() > 0){
            $data['staffFullDetailsLanguage']    = DB::table('languages')->where('fileNo', $fileNo)->get();
        }else{
            $data['staffFullDetailsLanguage']    = "";
        }

        //particulars of Children
        if(DB::table('tblchildren_particulars')->where('fileNo', $fileNo)->count() > 0){
            $data['staffFullDetailsChildren']    = DB::table('tblchildren_particulars')->where('fileNo', $fileNo)->get();
        }else{
            $data['staffFullDetailsChildren']    = "";
        }

        //Details of previous service 
        if(DB::table('previous_servicedetails')->where('fileNo', $fileNo)->count() > 0){
            $data['staffFullDetailsPreviousService']    = DB::table('previous_servicedetails')->where('fileNo', $fileNo)->get();
        }else{
            $data['staffFullDetailsPreviousService']    = "";
        }


        //Details of service in the forces
        if(DB::table('detailsofservice')->where('fileNo', $fileNo)->count() > 0){
            $data['staffFullDetailsDetailsService']    = DB::table('detailsofservice')->where('fileNo', $fileNo)->get();
        }else{
            $data['staffFullDetailsDetailsService']    = "";
        }


        //Record of censures and recommendations 
        if(DB::table('tblcensures_commendations')->where('fileNo', $fileNo)->count() > 0){
            $data['staffFullDetailsCensure']    = DB::table('tblcensures_commendations')->where('fileNo', $fileNo)->get();
        }else{
            $data['staffFullDetailsCensure']    = "";
        }

        //Gratuity Payment
        if(DB::table('tblgratuity_payment')->where('fileNo', $fileNo)->count() > 0){
            $data['staffFullDetailsGratuityPayment']    = DB::table('tblgratuity_payment')->where('fileNo', $fileNo)->get();
        }else{
            $data['staffFullDetailsGratuityPayment']    = "";
        }


        //Particular of termination of service 
        if(DB::table('service_termination')->where('fileNo', $fileNo)->count() > 0){
            $data['staffFullDetailsTerminationService']    = DB::table('service_termination')->where('fileNo', $fileNo)->get();
        }else{
            $data['staffFullDetailsTerminationService']    = "";
        }

        //Particular of termination of service 
        if(DB::table('tourleave_record')->where('fileNo', $fileNo)->count() > 0){
            $data['staffFullDetailsTourLeaveRecord']    = DB::table('tourleave_record')->where('fileNo', $fileNo)->get();
        }else{
            $data['staffFullDetailsTourLeaveRecord'] = "";
        }


        //Record of service 
        if(DB::table('recordof_service')->where('fileNo', $fileNo)->count() > 0){
            $data['staffFullDetailsRecordService']    = DB::table('recordof_service')->where('fileNo', $fileNo)->get();
        }else{
            $data['staffFullDetailsRecordService'] = "";
        }


        //Record of service 
        if(DB::table('recordof_emolument')->where('fileNo', $fileNo)->count() > 0){
            $data['staffFullDetailsRecordEmolument'] = DB::table('recordof_emolument')
                                                       ->join('recordof_service', 'recordof_service.recID', '=', 'recordof_emolument.entryDateMade')
                                                       ->where('recordof_emolument.fileNo', $fileNo)
                                                       ->get();
        }else{
            $data['staffFullDetailsRecordEmolument'] = "";
        }
        

        //Next of Kin
        if(DB::table('tblnextofkin')->where('fileNo', $fileNo)->count() > 0){
            $data['staffFullDetailsKin'] = DB::table('tblnextofkin')->where('fileNo', $fileNo)->first();
            $data['fullName']       = $data['staffFullDetailsKin']->fullname;
            $data['relationship']   = $data['staffFullDetailsKin']->relationship;
            $data['address']        = $data['staffFullDetailsKin']->address;
            $data['phoneNo']        = $data['staffFullDetailsKin']->phoneno;
        }else{
            $data['fullName']       = "";
            $data['relationship']   = "";
            $data['address']        = "";
            $data['phoneNo']        = "";
        }


        //get particular of wife
        if(DB::table('tbldateofbirth_wife')->where('fileNo', $fileNo)->count() > 0){
            $data['staffFullDetailsWife'] = DB::table('tbldateofbirth_wife')->where('fileNo', $fileNo)->first();
            $data['wifeName']       = $data['staffFullDetailsWife']->wifename;
            $data['wifedateofbirth']    = $data['staffFullDetailsWife']->wifedateofbirth;
            $data['dateOfMarriage'] = $data['staffFullDetailsWife']->dateofmarriage;
        }else{
            $data['wifeName']       = "";
            $data['wifedateofbirth']    = "";
            $data['dateOfMarriage'] = "";
        }


        /*GET TOTAL RECORDS*/ 
        //count Next of kin
        $data['totalNextofKin']             = DB::table('tblnextofkin')->where('fileNo', $fileNo)->count();
        //count education
        $data['totalEducation']             = DB::table('tbleducations')->where('fileNo', $fileNo)->count();
        //count particular of wife
        $data['totalParticularOfWife']      = DB::table('tbldateofbirth_wife')->where('fileNo', $fileNo)->count();
        //count languages
        $data['totallanguages']             = DB::table('languages')->where('fileNo', $fileNo)->count();
        //count children
        $data['totalChildren']              = DB::table('tblchildren_particulars')->where('fileNo', $fileNo)->count();
        //count Details of service in the forces 
        $data['totalDetailsService']        = DB::table('detailsofservice')->where('fileNo', $fileNo)->count();
        //count Details of previous service 
        $data['totalPreviousService']       = DB::table('previous_servicedetails')->where('fileNo', $fileNo)->count();
        //count Details of previous service 
        $data['totalRecordCensures']        = DB::table('tblcensures_commendations')->where('fileNo', $fileNo)->count();
        //count gratuity payment 
        $data['totalGratuityPayment']       = DB::table('tblgratuity_payment')->where('fileNo', $fileNo)->count();
        //count Termination of service 
        $data['totalTerminationService']    = DB::table('service_termination')->where('fileNo', $fileNo)->count();
        //count Termination of service 
        $data['totalTourLeave']             = DB::table('tourleave_record')->where('fileNo', $fileNo)->count();
        //count Record of services
        $data['totalRecordService']         = DB::table('recordof_service')->where('fileNo', $fileNo)->count();
        //count Record of Emoluments
        $data['totalRecordEmolument']       = DB::table('recordof_emolument')->where('fileNo', $fileNo)->count();

        return view('profile.details', $data);
    }
    
} //end class ProfileController