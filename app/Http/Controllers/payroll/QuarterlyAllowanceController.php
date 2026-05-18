<?php

namespace App\Http\Controllers\payroll;

use App\Http\Requests;
use Auth;
use Entrust;
use App\Permission;
use Illuminate\Http\Request;
use DB;
use carbon\carbon;
use App\Http\Controllers\Controller;
use Session;




class QuarterlyAllowanceController extends ParentController
{
    
   public function create()
   {
   $data['allowance'] = DB::table('tblquarterly_allowance')
      ->orderBy('grade','Desc')->get();
      
       $data['court'] =  DB::table('tbl_court')->get();
      $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

      return view('quarterlyOvertime.create',$data);
   }
   public function gradeAllowance(Request $request)
   {
      $grade = $request['grade'];
      $data = DB::table('tblquarterly_allowance')
      ->where('grade', '=', $grade)->first();
      $request->session()->flash('gradelevel',$request['grade']);
     return response()->json($data);
   }
    public function store(Request $request)
   {
         $request->session()->flash('gradelevel',$request['grade']);
         $request->session()->flash('grosspay',$request['gross']);
         $request->session()->flash('paye',$request['tax']);
         
      $grade = $request['grade'];
      $court = $request['court'];
      $countData = DB::table('tblquarterly_allowance')
      ->where('grade', '=', $grade)->where('courtID', '=', $court)->count();
      
      if($countData ==1)
      {
          $data = DB::table('tblquarterly_allowance')
          ->where('grade', '=', $grade)->update(array(
          'gross' => $request['gross'],
          'tax'   => $request['tax'],
          'created_at' => date('Y-m-d'),
          ));
          return redirect('/quarterly-allowance/create')->with('msg','Successfully Updated');
      }
      else
      {
           $data = DB::table('tblquarterly_allowance')
          ->insert(array(
          'grade' => $request['grade'],
          'courtID' => $request['court'],
          'gross' => $request['gross'],
          'tax'   => $request['tax'],
          'created_at' => date('Y-m-d'),
          ));
           return redirect('/quarterly-allowance/create')->with('msg','Successfully Entered');
      }
      
     
     
   }
    
}