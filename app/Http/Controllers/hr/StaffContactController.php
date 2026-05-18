<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use DB;
use App\staffModel;
use Session;

class StaffContactController extends ParentController
{
    //
    public function create()
    {	
	//$staff = new staffModel;
	//$data['staff'] = staffModel::all();
	$data['staff'] = DB::table('tblmandatesignatoryprofiles')->get();
	
	return view('staffContact/create', $data);
    }

    public function store(Request $request)
    {	
    	$this->validate($request, [
		'name'  => 'required',
		'phone' => 'required',
		]);
		
	DB::table('tblmandatesignatoryprofiles')->insert([
		    'name'  => $name,
		    'phone' => $phone,
		]);
    	return redirect('staffContact/create')->with('msg', 'staff Contact has been added');
    }
    //dd('completed ');
    
    public function update (Request $request){
    
    	
		$this->validate($request, [
   			'name'  => 'required',
			'phone' => 'required',
   		]);
   		
						   		
		$staffID = $request->input('StaffRecordID');	
		$name	 = $request->input('name');	
		$phone	 = $request->input('phone');		
				
		$data['update'] = DB::table('tblmandatesignatoryprofiles')
	            ->where('id', $staffID )
	            ->update([
	            		'name' => $name,
	            		'phone' => $phone,
	            ]);
    		return redirect('staffContact/create')->with('msg', 'staff Contact has been updated');
    }
    
    

    public function destroy($id)
    {
    
    	$staffModel = staffModel::find($id);
    	
    	$staffModel->find($id);
    	$staffModel->delete();
    	
        return redirect('staffContact/create')->with('msg', 'Contact successfully deleted');
    }
}
