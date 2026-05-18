<?php

namespace App\Http\Controllers\funds;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests;
use Session;
use DB;

class lgaCoveredController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       // Session::forget('getStateID');

        $stateID = Session::get('getStateID');
        $data['getStates'] = DB::table('tblstates')->get();
        $data['getLGA'] = DB::table('lga')->where('stateid',$stateID)->get();
        $aStateID = DB::table('tblstates')->where('StateID', $stateID)->value('StateID');
        $aState = DB::table('tblstates')->where('StateID', $stateID)->value('State');
        $data['StateID']=$stateID;
        $data['State']=$aState;
        return view('lga.lgacovered', $data);
    }

    public function getLgaState(Request $request)
    {
        Session::forget('getStateID');
        $stateID = trim($request['getState']);
        Session::put('getStateID', $stateID);
        return redirect('lga/covered');
    }

    public function Clear()
    {
        Session::forget('getStateID');
        return redirect('lga/covered');
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
            'state' =>'required',
            'localGovernmentArea' =>'required',
         ]);

         $stateID        = trim($request['state']);
         $lga     = trim($request['localGovernmentArea']);

         $lgaExist = DB::table('lga')
         ->where('lga', $lga)->where('stateId', $stateID)->exists();

         if($lgaExist)
         {
            Session::put('getStateID', $stateID);
            return redirect('lga/covered')
            ->with('error', 'Local Government Area already added');
         }


         $reallyStore = DB::table('lga')->insert(array(
                'stateid' =>$stateID,
                'lga' => $lga,
         ));

         if($reallyStore)
        {
            
            Session::put('getStateID', $stateID);
            return redirect('lga/covered')->with('message', 'Local Government Area successfully added');
    
        }else{
            
            return redirect('lga/covered')->with('error', 'info not added');
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
        $lgaName = trim($request['lgaChange']);
        $lgaId = trim($request['lgaid']);
        DB::table('lga')->where('lgaId',$lgaId)->update(['lga' => $lgaName]);
        return redirect('lga/covered')->with('message','LGA successfully updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($lgaId)
    {
        $lgaExists=DB::table('tblper')->where('lgaID', $lgaId)->exists();

        if($lgaExists)
        {
            return redirect('lga/covered')
            ->with('alert', 'Cannot delete LGA because a staff is still assigned to it');
        }
        else
        {
            DB::table('lga')->where('lgaId', $lgaId)->delete();
            return redirect('lga/covered')->with('message','LGA successfully deleted');
        }

       
    }
}
