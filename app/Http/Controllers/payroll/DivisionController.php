<?php

namespace App\Http\Controllers\payroll;

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
        $this->validate($request, [
          'division' => 'required|regex:/[a-zA-Z.]/|unique:tbldivision',
          //'abbrv' => 'required' 
        ]);
        $name = $request->input('division');
        //$abv = $request->input('abbrv');
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
      $this->validate($request,[       
          'division' => 'required|integer'
      ]);
      $dv = DB::table('tbldivision')->where('tbldivision.divisionID', $divisionid)->first();
      $request->session()->put('divisionID', $dv->divisionID);
      $request->session()->put('division', $dv->division); 
      // $request->session()->put('activeMonth', $dv->month);
      // $request->session()->put('activeYear', $dv->year);
      return back()->with('message', 'Division was changed successfully!');
    }
    
    public function getDivisionAccount()
    {

      $details = DB::table('tbldivision')
              ->leftjoin('tblbanklist','tblbanklist.bankID','=','tbldivision.bankID')
      ->get();
      //dd($details);
      $data['division'] = DB::table('tbldivision')->get();
      $data['bankList'] = DB::table('tblbanklist')->get();

      return view('divisionAccount.index', compact('details'), $data);
    }


    public function updateDivisionAccount(Request $request)
    {
      $this->validate ( $request, [
          'divisionID' => 'required',
          'abbrv' => 'required',
          'acctNo' => 'required',
          'acctName' => 'required',
          'bankID' => 'required'
      ]);

      $division       = $request['divisionID'];
      $abbrv       = $request['abbrv'];
      $acctNo         = $request['acctNo'];
      $acctName        = $request['acctName'];
      $bank           = $request['bankID'];


      DB::table('tbldivision')->where('divisionID', $division)
        ->update(['abbrv' => $abbrv,'acctNo' => $acctNo, 'acctName' => $acctName, 'bankID' => $bank]);
      return redirect()->back()->with('msg', 'Update successful!');
    }

    public function updateDivAccount(Request $request)
    {
      $divUpdate = DB::table('tbldivision')
            ->where('divisionID', $request['recordID'])
            ->update([
                'acctNo' => $request->acctNo,
                'acctName' => $request->acctName,
                'bankID' => $request->bankID
            ]);

        if($divUpdate){
            return redirect()->back()->with('msg', 'Record updated!');
        }

        return back()->with('err', 'record already exist!'); 
    }
    
}