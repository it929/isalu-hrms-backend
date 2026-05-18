<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use Session;
use App\EmploymentType;

class EmploymentTypeController extends Controller
{
    public function index() 
    {
        $employmentTypes = DB::table('tblemployment_type')->get();
        //dd($employmentTypes);
            return view('employmentType.index', ['employmentTypes'=>$employmentTypes]); 
            
    }   

    public function store(Request $request)
    {
        $check = $request->get('employmentType');
        
        $this->validate($request, [
            'employmentType' => 'required'
        ]);
        
        if(DB::table('tblemployment_type')->where('employmentType',$check)->count()>0)
          {
               return back()->with('err', 'record already exist!');
          }
          else{
        
        $employmentType = new EmploymentType([
           'employmentType'=> $request->get('employmentType'),
           'active'=> 1
        ]);
        
        $employmentType->save();
        return redirect()->back()->with('msg', 'Employment Type saved!');
          }
    }
    
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'employmentType' => 'required',
            'active' => 'required'
        ]);
        $employmentType = EmploymentType::where('id', $id)->update([
            'employmentType' => $request->employmentType,
            'active' => $request->active
        ]);

        return redirect()->back()->with('msg', 'Employment Type updated!'); 
    }
    
}
