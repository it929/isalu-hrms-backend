<?php

namespace App\Http\Controllers\funds;

use Illuminate\Http\Request;
//use Auth;
use App\Http\Requests;
use DB;
use Auth;
use Session;
class StaffReportController extends functionController
{
	public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->middleware('auth');
        $this->username = Session::get('userName');
            return $next($request);
        });
    }//
 
   public function VoultBalanceReport(Request $request)
   {   
   	   $data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
	   $employmenttype= trim($request['employmenttype']);
	   
	   $fieldstoview = $request['fields'];
	   
	   
	   $designation= trim($request['designation']);
	   $division= trim($request['division']);
	   $grade= trim($request['grade']);
	   $court= trim($request['court']);
	   $department= trim($request['department']);
	   $section= trim($request['section']);
	   $gender= trim($request['gender']);
	   $todate= trim($request['todate']);
	   $fromdate= trim($request['fromdate']);
	   if($todate==""){$todate=date("Y-m-d");}
	   if($fromdate==""){$fromdate='1900-01-01';}
	   $orderlist= trim($request['orderlist']);
	   $data['orderlist'] = $orderlist;
	   $data['employmenttype'] = $employmenttype;
	    $data['designation'] = $designation;
	   $data['division'] = $division;
	   $data['grade'] = $grade;
	   $data['court'] = $court;
	   $data['department'] = $department;
	   $data['section'] = $section;
	   $data['fromdate'] = $fromdate;
	   $data['todate'] = $todate;
	   $data['gender'] = $gender;
	   $data['OrderList'] = $this->OrderList();
	   $data['CourtList'] = $this->CourtList();
	   $data['EmployeeTypeList'] = $this->EmployeeTypeList();
	   $data['DesignationList'] = $this->DesignationList3($court,$department);
	   $data['DepartmentList'] = $this->DepartmentList($court); 
	   $data['Gender'] = $this->Gender();
	   $data['Divisions'] = $this->DivisionList($court);
	  $data['QueryStaffReport'] = $this->QueryStaffReport($court,$division,$department,$designation,$grade,$gender,$fromdate,$todate,$employmenttype,$orderlist);
	  $data['fieldstoview'] = $fieldstoview;
   	return view('Report.StaffNominalRoll', $data);
   }
 


}