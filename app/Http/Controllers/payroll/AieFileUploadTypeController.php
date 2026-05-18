<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;

use App\Aie;
use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use Session;

class AieFileUploadTypeController extends Controller
{
    public function index() 
    {
        $aiefileuploadtypes = DB::table('tblaiefileuploadtype')->get();
        //dd($aiefileuploadtypes);
            return view('aiefileuploadtype.index', ['aiefileuploadtypes'=>$aiefileuploadtypes]); 
            
    }   

    public function store(Request $request)
    {
        $check = $request->get('fileType');
        
        $this->validate($request, [
            'fileType' => 'required',
            'status' => 'required'
        ]);
        
        $fileType = $request->input('fileType');
        $status = $request->input('status');

        if(DB::table('tblaiefileuploadtype')->where('fileType',$check)->count()>0)
          {
               return back()->with('err', 'record already exist!');
          }
          else{ 

           
        $aiefileupload = new Aie([
           'fileType'=> $request->get('fileType'),
           'status'=> $request->get('status'),
        ]);
        
        $aiefileupload->save();
        return redirect()->back()->with('msg', 'Record successfully saved!');
          }
    }
    
    public function update(Request $request)
    {
        $this->validate($request, [
            'fileType' => 'required',
            'status' => 'required'
        ]);
        

        $aiefileupload = DB::table('tblaiefileuploadtype')
            ->where('id', $request['recordID'])
            ->update([
                'fileType' => $request->fileType,
                'status' => $request->status
            ]);

        if($aiefileupload){
            return redirect()->back()->with('msg', 'Record updated!');
        }

        return back()->with('err', 'record already exist!'); 
    }
    
}
