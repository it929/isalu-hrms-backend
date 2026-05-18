<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Session;
use App\Http\Requests;
use DB;

class UpdateUnionController extends Controller
{
   
    public function index()
    {
        //
    }

    public function updateUnion()
    {
        $basic = DB::table('basicsalaryconsolidated_04_03_2020')->where('employee_type','=',1)->where('grade','<',14)->get();

       
        foreach ($basic as $value) {
         
          $basicSal = $value->basic;
          $union = (4/100) * $basicSal;
          
         $update = DB::table('basicsalaryconsolidated_04_03_2020')
         ->where('employee_type','=',1)
         ->where('grade','=',$value->grade)
         ->where('step','=',$value->step)
         ->update(array(  
        
        'unionDues'            => $union, 
        
       ));

        }
        dd('Successfully Updated');
       // substr($mynumber, 0, 2)
    }

    
    public function destroy($id)
    {
        //
    }
}
