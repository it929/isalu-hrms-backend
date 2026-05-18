<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Http\Requests;
use DB;
use Auth;
use Session;
use App\HeadOfDept;

class DepartmentController extends Controller
{
   	 public function index()
   	{
   		$data['getDept'] = DB::table('tbldepartment')->get();
   		$data['getTheStaff'] = DB::table('tblper')->where('staff_status', 1)->get();
   		
   		$data['mergeUserID'] = DB::table('tbldepartment')  				      
			                ->join('tblper', 'tblper.UserID', '=', 'tbldepartment.head' )
			                ->select('tblper.surname', 'tblper.first_name', 'tblper.othernames', 'tbldepartment.department', 'tbldepartment.head')
			                ->get();
   					
   		

   		return view('department/departmentHead', $data);
   	}
   		
   	public function store(Request $request) 
   	{
   	 
   		$this->validate($request, [
   			'headOfDepartment' => 'required',
   			'departmentName'   => 'required',
   		]);
   		
   		$departmentID = $request->input('departmentName');
   		$staffUserID = $request->input('headOfDepartment');
   		$HeadOfDept = HeadOfDept::where('id', $departmentID)->update(array(
   			'head' => $staffUserID ,
   		));
   		
   		return redirect ('department/departmentHead')->with('message', 'staff has been assinged as Departmnent head');
   		
   	}
   	
   	
   	public function update(Request $request) 
   		{
   			$this->validate($request, [
   			'headOfDepartment' => 'required',
   			'departmentName'   => 'required',
   		]);
   			$HeadOfDept = HeadOfDept::where('id', $departmentID)->update(array(
   			'head' => $staffUserID ,
   		));
   			dd($HeadOfDept);
   			//return view ('department/EditDepartmentHead');
   		}
   		
}//end class		
   	