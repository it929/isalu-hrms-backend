<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests;
use File;
use DB;

class addCourtController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['getAllCourts'] = DB::table('tbl_court')->where('active',1)->get();
        return view('court.addCourt', $data);
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
            'court_name' =>'required|unique:tbl_court,court_name',
            'courtAbbr' =>'required|unique:tbl_court,courtAbbr',
        ]);

        $court_name= trim($request['court_name']);
        $courtAbbr= trim($request['courtAbbr']);
        $reallyStore = DB::table('tbl_court')->insert(array(
            'court_name' => $court_name,
            'courtAbbr' => $courtAbbr,
            'active' => 1,
        ));
        
        if($request->hasFIle('courtImage')){
            $filenameWithExt = $request->file('courtImage')->getClientOriginalName();
            
            $path = $request->file('courtImage')->move(
                base_path('../public_html/jippis/courtLogo'), $filenameWithExt
            );

            

            DB::table('tbl_court')->where('court_name',$court_name)
            ->update(['logoPath' => $path,'logoName' => $filenameWithExt,]);

        }else{
            $path = base_path('../public_html/jippis/courtLogo/noimage.png');

            DB::table('tbl_court')->where('court_name',$court_name)
            ->update(['logoPath' => $path,'logoName' => 'noimage.png',]);
        }
      

        

        if($reallyStore)
        {
            return redirect('court/add-court')->with('message','New Court Added');
        }else{
            return redirect('court/add-court')->with('error','Court not added');
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
            'courtName' =>'required',
            'court_abbriviation' =>'required',
        ]);
       
         $upcourtid = trim($request['courtid']);
         $upcourt_name= trim($request['courtName']);
         $upcourtAbbr= trim($request['court_abbriviation']);

         DB::table('tbl_court')->where('id',$upcourtid)
            ->update(['courtAbbr' => $upcourtAbbr,'court_name' => $upcourt_name,]);
            
            $file_path = base_path('../public_html/jippis/courtLogo/');

         if($request->hasFIle('courtImageEdit')){
            $filenameWithExt = $request->file('courtImageEdit')->getClientOriginalName();
            
            $pic = DB::table('tbl_court')->where('id',$upcourtid)
            ->select('logoName')->value('logoName');
            
            if($pic!='noimage.png')
            {
                 
                 $delete_path = $file_path . $pic;
                 File::delete($delete_path);
            }
            
            $path = $request->file('courtImageEdit')->move(
                base_path('../public_html/jippis/courtLogo'), $filenameWithExt
            );

            

            DB::table('tbl_court')->where('id',$upcourtid)
            ->update(['logoPath' => $path,'logoName' => $filenameWithExt,]);

        }

        
        return redirect('court/add-court')->with('message', 'court edited');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $CourtExist = DB::table('tbldivision')->where('courtID',$id)->exists();

        if($CourtExist)
        {
            return redirect('court/add-court')->with('error', 'cant delete a court that staff are still assigned to');
        }else{
        
         $pic = DB::table('tbl_court')->where('id',$id)
            ->select('logoName')->value('logoName');
            
         $file_path = base_path('../public_html/jippis/courtLogo/');
 
            if($pic!='noimage.png')
            {
                 
                 $delete_path = $file_path . $pic;
                 File::delete($delete_path);
            }
            
            DB::table('tbl_court')->where('id',$id)->delete();
            return redirect('court/add-court')->with('message','court deleted');
        }
        


       
    }


}
