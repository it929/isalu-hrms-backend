<?php

namespace App\Http\Controllers\funds;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;

class UnitsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['getUsers'] = DB::table('users')->get();
        return view('units.createUnit', $data);
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
            'user' =>'required|string',
            'unit' =>'required|string',
        ]);

        $user= trim($request['user']);
        $unit= trim($request['unit']);
    
        $reallyStore = DB::table('tblstaff_section')->insert(array(
            'section' => $unit,
            'user_id'   => $user,
        ));

       
            return redirect('/staff/unit')->with('msg','Successfully Added');
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $data['view'] = DB::table('tblstaff_section')->paginate(50);
        return view('units.list',$data);
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
        $title = trim($request['titleChange']);
        $titleID = trim($request['titleid']);
        $reallyUpdate= DB::table('tbltitle')->where('ID',$titleID)->update(['title' => $title]);
        if($reallyUpdate)
        {
            return redirect('/title')->with('message','Title successfully edited');
        }else{
            return redirect('/title')->with('error','Title could not be edited');
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id,$title)
    {
    	$titleExists = DB::table('tblper')->where('title', $title)->exists();
        if($titleExists)
        {
            return redirect('/title')
            ->with('alert','Title could not be deleted as a staff is currently bearing said title');
        }
        
        $reallyDel = DB::table('tbltitle')->where('ID', $id)->delete();

        if($reallyDel)
        {
            return redirect('/title')->with('message','Title successfully deleted');
        }else{
            return redirect('/title')->with('error','Title could not be deleted');
        }
            
        
    }
}
