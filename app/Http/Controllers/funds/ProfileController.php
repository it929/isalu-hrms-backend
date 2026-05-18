<?php

namespace App\Http\Controllers\funds;

use Illuminate\Http\Request;
use File;
use App\Http\Requests;
use Carbon\Carbon;
use Session;
use DB;

class ProfileController extends ParentController
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
        return view('profile.searchStaff');
    }


    public function autocomplete(Request $request)
    {
    
        $query = $request->input('query');
        $search = DB::table('tblper')
                ->where('divisionID', $this->divisionID)
                ->where('surname', 'LIKE', '%'.$query.'%')
                ->orWhere('first_name', 'LIKE', '%'.$query.'%')
                ->orWhere('fileNo', 'LIKE','%'.$query.'%')
                ->take(15)
                ->get();
        $return_array = null;
        foreach($search as $s)
        {
          $return_array[]  =  ["value"=>$s->surname.' '.$s->first_name.' '.$s->othernames.' - '.$s->fileNo, "data"=>$s->fileNo];
        }   
        return response()->json(array("suggestions"=>$return_array));
    }



    public function details(Request $request)
    {   
        $this->validate($request, [
            'fileNo' => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
        ]);
        $fileNo = trim($request['fileNo']); 

        //check if staff belongs to this this->division
        $getstaffDiv = DB::table('tblper')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->where('tblper.fileNo', '=', $fileNo)
                ->select('division')
                ->first();
                
          //check if you can view    
         /*if(!(DB::table('tblper')->select('divisionID')->where('fileNo', '=', $fileNo)->where('divisionID', $this->divisionID)->count())){
            return back()->with('err', 'Staff Details cannot be viewed in this Division. Staff belongs to '. $getstaffDiv->division .'  division. This means, Staff can only be viewed from '. $getstaffDiv->division .' division');
         }*/
        //end checking
         

        //Bid-Data
        if(DB::table('tblper')->where('fileNo', $fileNo)->count() > 0){
                $data['staffFullDetails'] = DB::table('tblper')
                ->leftjoin('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->leftjoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
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

  	
    
} //end class ProfileController