<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use Session;
class EarningAndDeductionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['getep'] = DB::table('tblearningParticular')->get();
        $data['getedj'] = DB::table('tblearningDeductions')
        ->join('tblearningParticular','tblearningParticular.ID','=','tblearningDeductions.particularID')
        ->select('tblearningDeductions.ID','Particular','description','status')
        ->orderBy('tblearningDeductions.particularID', 'asc')->get();
        //log::info('An informational message.');
        $data['ID'] = Session::get('particularSession');
        return view('earningDeduction.earningDeduction',$data);
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
        $this->validate($request, [
            'particulars' =>'required',
            'description' =>'required|unique:tblearningDeductions,description',
         ]);

         $particulars = trim($request['particulars']);
         $description = trim($request['description']);
         $description = str_replace('\'', '', $description); 
        log::info('char Description',  ['is' => $description]);

         $reallyStore = DB::table('tblearningDeductions')->insert(array(
            'particularID' =>$particulars,
            'description' => $description,
            'status' =>1,
     ));

     if($reallyStore)
    {
        Session::put('particularSession', $particulars);
        return redirect('/earning-deduction')->with('message', 'particular added');

    }else{
        Session::put('particularSession', $particulars);
        return redirect('/earning-deduction')->with('error', 'info not added');
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
    public function update(Request $request)
    {
        $this->validate($request, [
            'descriptions' =>'required',
         ]);

        $partDesc = trim($request['descriptions']);
        $partId = trim($request['partid']);
        $partStatus = trim($request['partStatus']);
        DB::table('tblearningDeductions')->where('ID',$partId)
        ->update(['description' => $partDesc,'status' => $partStatus]);
        return redirect('/earning-deduction')->with('message', 'particular edited');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
