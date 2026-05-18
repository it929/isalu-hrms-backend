<?php

namespace App\Http\Controllers\funds;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;

class AddTitleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['getAllTitles'] = DB::table('tbltitle')->get();
        return view('title.addTitle', $data);
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
            'title' =>'required|unique:tbltitle,title',
        ]);

        $title= trim($request['title']);
    
        $reallyStore = DB::table('tbltitle')->insert(array(
            'title' => $title,
        ));

        if($reallyStore)
        {
            return redirect('/title')->with('message','New title added');
        }else{
            return redirect('/title')->with('error','Title could not be added');
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
