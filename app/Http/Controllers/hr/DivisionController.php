<?php

namespace App\Http\Controllers\hr;

use App\Http\Requests;
use App\Role;
use App\User;
use Auth;
use Entrust;
use App\Permission;
use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\Controller;

class DivisionController extends ParentController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */


    //viewing division create form
    public function create()
    {
       $data['division'] = DB::table('tbldivision')->select('divisionID', 'division')
       ->orderBy('division', 'Asc')->get();   
       return view('division.create', $data);    
    } 

    //inserting data for division
    public function store(Request $request)
    { 
        $this->validate ( $request, [
          'division' => 'required|regex:/[a-zA-Z.]/|unique:tbldivision' 
          ]);
        $name = $request->input('division');
        $year=date('Y');
        DB::table('tbldivision')->insert(['division'=>$name]);
        $divisionid = DB::table('tbldivision')->select('divisionID')->where('division',$name)->pluck('divisionID');

        DB::table('tblactivemonth')->insert(array(
            'divisionID' =>$divisionid[0],
            'month'=>'JANUARY',
            'year'=>$year
            )); 
        $this->addLog('new division with name = '.$name." and active month table also inserted into");
        return back()->with('message', 'Division Was Successfully Created!');
    }

    public function destroy($id = Null)
    {
            //delete
        if($id != Null){

            DB::table('tbldivision')->where([
                ['divisionID', '=', $id]
                ])->delete();
            DB::table('tblactivemonth')->where([
                ['divisionID', '=', $id]
                ])->delete();

            $data['division'] = DB::table('tbldivision')->select('divisionID', 'division')
            ->orderBy('division', 'Asc')->get();

            $this->addLog('division with id='.$id." name='' removed");    
        }      
        return back()->with($data);  
    }

  public function changeDivisionCreate()
    {
      $divisions = DB::select('select * from tbldivision');
      return view('division.changeDivision', ['divisions'=>$divisions]);
    }


  public function changeDivisionStore(Request $request)
    {
      $divisionid = $request->input('division');
      $this->validate
            ($request,[       
          'division' => 'required|integer'
             ]);
      $dv = DB::table('tbldivision')->where('tbldivision.divisionID', $divisionid)->first();
      $request->session()->put('divisionID', $dv->divisionID);
      $request->session()->put('division', $dv->division); 
      // $request->session()->put('activeMonth', $dv->month);
      // $request->session()->put('activeYear', $dv->year);
      return back()->with('message', 'Division was changed successfully!');
    }
}