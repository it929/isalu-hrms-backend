<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Http\Requests;
use DB;
use Auth;
use Session;


class LeaveCreateController extends Controller
{
	public function allLeave()
	{
		return $data['getleave'] = DB::table('tblleave_type')->orderBy('id', 'Desc')->get();
	}
	
	public function index () { 
	
		$data['getleave'] = $this->allLeave();
		//$data['getDepartment'] = DB::table('tblleave_type')->get(); 
		//dd($data);
		return view('Leave/leavetype')->with($data);
	}
	

	public function store(Request $request) 
	{
		  $this->validate($request, [
		        'leave' =>'required',//:tblleave_type, leaveType'                      
		  ]);
		  $leave = $request->input('leave');
	    	
		  $save = DB::table('tblleave_type')->insert([
		        'leaveType' => $leave           		       	        
		  ]);	
		 		       	 
		  return redirect('Leave/leavetype')->with('message', 'New leave type was added successfully.');                     
         }
	   
	       
         public function edit ($id) {
         	$data['getleave'] = DB::table('tblleave_type')->get();
         	$data['editLeave'] = DB::table('tblleave_type')->where('id', $id)->first();
		$data['getLeaveID'] = $id;
		
         	return view('Leave/leavetype', $data);
    	}
    	
    	
    	public function update(Request $request){
    	
    			$this->validate($request, [
		        'leave' =>'required|unique:tblleave_type, leaveType'                      
		  ]);
		  
    		$data['leave']   = $request->input('leave');
    		$leaveID 	 = $request->id;
    		
    		$update = DB::table('tblleave_type')->where('id',$leaveID)->update([
    			'leaveType' => $leave,
    		]);
    		
    		return redirect('Leave/leavetype')->with('message', 'leave type was successfully Updated.');
    	
    	}

	public function delete($id) 
	{
		if(DB::table('tblleave_type')->where('id', $id)->first())
		{
			$success = DB::table('tblleave_type')->where('id', $id)->delete();
			 return redirect('Leave/leavetype')->with('message','Deleted successfully');
		}else{
			return redirect('Leave/leavetype')->with('error','Sorry, we cannot delete this record. Try again');
		}
		return redirect('Leave/leavetype')->with('error','Record not found!');
		
		
	}
}