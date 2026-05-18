<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
//use Auth;
use App\Http\Requests;
use DB;
use Auth;
use Session;
class AuditUnitController extends Controller 
{

    public function auditingU(Request $request)
     {
         
    
         $month = $request['month'];
         $year =$request['year'];
         $userID = $request['staff'];
     
                     
          DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->update([
          'audit_assigned_user'          => $userID,
          ]);
        	        
          return back()->with('message','User Assigned!');
          
     }
     
     public function assignStaff()
     {
        
         $data['activemonth'] = DB::table('tblactivemonth')
		->join('tbl_court','tbl_court.id','=','tblactivemonth.courtID')
		->first();
		
       $data['users']=DB::table('users')->get();
       $data['sections']=DB::table('tblsections')->get();
       $data['view'] = DB::table('tblpayment_consolidated')->where('year','=',$data['activemonth']->year)->where('month','=',$data['activemonth']->month)->value('audit_view');
        //$data['view'] = DB::table('tblpayment_consolidated')->where('year','=',2020)->where('month','=','MARCH')->value('audit_view');
             
       $data['staffsections']=DB::table('tblstaff_section')
       ->join('users', 'users.id', '=', 'tblstaff_section.user_id')
       ->join('tblsections', 'tblsections.code', '=', 'tblstaff_section.section')
       ->where('tblstaff_section.section','=', 'AU')
       ->select('users.name','users.username','tblsections.code','tblsections.section','tblstaff_section.id','tblstaff_section.user_id')
       ->get(); 
       
       $data['staff'] = DB::table('tblpayment_consolidated')
       ->join('users','users.id','=','tblpayment_consolidated.audit_assigned_user')
       ->where('year','=',2021)->where('month','=','MARCH')
       ->select('*','users.name as staffname')
       ->first();
       
       $data['auditedComment'] = DB::table('tblaudit_comment')->where('year','=',$data['activemonth']->year)->where('month','=',$data['activemonth']->month)->first();
       
        $data['ifPushed'] = DB::table('tblpayment_consolidated')->where('year','=',$data['activemonth']->year)->where('month','=',$data['activemonth']->month)->where('audit_view','=',1)->count();
      
      //dd($data['ifPushed']);
       return view('audit.assignStaff',$data);
      
     }
     
     
     
     public function assignRecords()
     {
         
         $active = DB::table('tblactivemonth')
		->join('tbl_court','tbl_court.id','=','tblactivemonth.courtID')
		->first();
		
      $data['assigned']=DB::table('tblpayment_consolidated')
       ->where('audit_assigned_user','=', Auth::user()->id)
       ->where('tblpayment_consolidated.rank','!=',2)
       ->where('tblpayment_consolidated.year','=',$active->year)
       ->where('tblpayment_consolidated.month','=',$active->month)
       ->groupBy('month')
       ->groupBy('year')
       ->get(); 
       
       $data['councilAssigned']=DB::table('tblpayment_consolidated')
       ->where('audit_assigned_user','=', Auth::user()->id)
       ->where('tblpayment_consolidated.rank','=',2)
       ->where('tblpayment_consolidated.year','=',$active->year)
       ->where('tblpayment_consolidated.month','=',$active->month)
       ->groupBy('month')
       ->groupBy('year')
       ->get();
       
     
       
       
        $data['workingstate'] = DB::table('tblstates')
		 	 ->select('StateID', 'State')
			 ->distinct()
	    	 ->orderBy('State', 'Asc')
			 ->get();
			 
			 $data['currentstate'] = DB::table('tblcurrent_state')->get();
			 
	     
	     $data['cvSetup'] = DB::table('tblcvSetup')->select('ID', 'description')->get();

            $data['reporttype'] = DB::table('tbladmincode')  
             ->select('addressName', 'determinant') 
            //->union($cvSetup)
            ->get();
     
         
        return view('audit.records',$data);
          
     }
     
     public function confirm(Request $request)
     {
         $month    = $request['month'];
         $year     = $request['year'];
         $comment   = $request['comment'];
     
                     
          DB::table('tblpayment_consolidated')->where('year','=',$year)->where('month','=',$month)->update([
          'audit_confirmation'          => 1,
          ]);
          
          DB::table('tblaudit_comment')->insert([
          'comment'          => $comment,
          'month'            => $month,
          'year'             => $year,
          'userID'           => Auth::user()->id,
          ]);
          return back()->with('message','Successfully Confirm');
         
     }
     
     public function report(Request $request)
     {
         $cuurentYear = date('Y');
         
         if ($request->isMethod('post'))
         {
             Session::flash('yr', $request['year']);
        $data['report'] = DB::table('tblpayment_consolidated')
       ->join('users','users.id','=','tblpayment_consolidated.audit_assigned_user')
       ->where('year','=',$request['year'])
       ->where('audit_confirmation','=',1)
       ->select('*','users.name as staffname')
       ->groupBy('year')
       ->get(); 
        return view('audit.salaryAuditReport',$data);
         }
         $data['report'] = DB::table('tblpayment_consolidated')
       ->join('users','users.id','=','tblpayment_consolidated.audit_assigned_user')
       ->where('year','=',$cuurentYear)
       ->where('audit_confirmation','=',1)
       ->select('*','users.name as staffname')
       ->groupBy('year')
       ->get();
       return view('audit.salaryAuditReport',$data);
     }
     
     public function deleteDesignation($id)
     {
     
         $delete=DB::table('tblstaff_section')->where('id',$id )->delete();
         return redirect('staff/designation')->with('message','Deleted!');
         
        
        
     
     }
    

}