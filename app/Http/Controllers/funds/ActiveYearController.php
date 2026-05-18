<?php

namespace App\Http\Controllers\funds;



use Illuminate\Http\Request;

use App\Http\Requests;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ActiveYearController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $getContractSess = Session::get('contractSess');
        $getYearSess = Session::get('yearSess');
        $data['currentContract'] = $getContractSess;
        $data['currentYear'] = $getYearSess;

        $data['contracttype'] = DB::table('tblcontractType')->get();
        $data['years'] = $this->getYears(2024,2050);
        $data['contractTable'] = DB::table('tblactiveperiod')
        ->join('tblcontractType','tblcontractType.ID','=','tblactiveperiod.contractTypeID')
        ->orderBy('tblactiveperiod.year', 'asc')->get();
        //dd( $data['contractTable']);
        return view('funds.activeYear.activeYear',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //dd($request->all());

        $this->validate($request, [
            'contractType' =>'required',
            'year' =>'required',
         ]);
            
         
         $contractType  = $request->input('contractType');
        //  dd( $contractType);
         $year     = $request->input('year');

         Session::forget('contractSess');
         Session::forget('yearSess');

         Session::put('contractSess', $contractType);
         Session::put('yearSess', $year);

         $contractExist=DB::table('tblactiveperiod')->where('contractTypeID', $contractType)->exists();

         if($contractExist)
         {
            DB::table('tblactiveperiod')->where('contractTypeID',$contractType)
        ->update(['year' => $year]);
        
        return redirect('/active-year')
        ->with('message', 'Active Period was added to list');
         }
 
         $reallyStore = DB::table('tblactiveperiod')->insert(array(
             'contractTypeID'     => $contractType,
             'year'      => $year,
         ));
         //
 
         if($reallyStore)
         {
             return redirect('/active-year')->with('message', 'Active Period was added to list');
         }else{
             return redirect('/active-year')->with('error', 'info not added');
         }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       $reallyDelete = DB::table('tblactiveperiod')->where('periodID', $id)->delete();
       if($reallyDelete==1)
       {
        return redirect('/active-year')->with('message', 'Active Period successfully deleted');

       } else{
           
        return redirect('/active-year')->with('error', 'Active Period could not be deleted');
       }
    }

    public function getYears($x,$y)
    {   
        $i=0;
       $year=array(100);
        foreach (range($x, $y) as $number) {
            $year[$i++]= $number;
        }
        return $year;
    }

}
