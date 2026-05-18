<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests;
use Illuminate\Support\Facades\Storage;
use DB;
use File;

class CompanyProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result= DB::table('tblcompany')->get();
        if($result==null)
        {
            DB::table('tblcompany')->insert([
                'companyName' =>' ']
         );
        }
        $pic = DB::table('tblcompany')->select('logoName')->limit(1)->value('logoName');
        $pic_path = base_path('../public_html/jippis/profileLogo/').$pic;
        if($pic==null||File::glob($pic_path)==null)
        {
            DB::table('tblcompany')->update(['logoName' => 'noimage.png',]);
        }
        $data['getAllDetails']= DB::table('tblcompany')->get();

        return view('companyProfile.companyProfile', $data);
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
        //
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
    //dd(asset(''));
        $this->validate($request, [
            'companyname' =>'required',
            'shortCode' =>'required',
            'telnum' =>'required',
            'emailid' =>'required',
            'address' =>'required',
            'logo' => 'image|max:1999',
    
        ]);

        $file_path =  base_path('../public_html/jippis/profileLogo/');
                     
        if($request->hasFIle('logo')){
            $filenameWithExt = $request->file('logo')->getClientOriginalName();
            
           $pic = DB::table('tblcompany')->select('logoName')->limit(1)->value('logoName');
           if($pic!='noimage.png')
           {
                
                $deletePicPath = $file_path . $pic;
                File::delete($deletePicPath );
           }

            $location = base_path('../public_html/jippis/profileLogo');
            $path = $request->file('logo')->move( $location,$filenameWithExt);

            DB::table('tblcompany')->update(['logoPath' => $path,'logoName' => $filenameWithExt,]);

        }
      

        $companyname= trim($request['companyname']);
        $shortCode= trim($request['shortCode']);
        $telnum= trim($request['telnum']);
        $emailid= trim($request['emailid']);
        $address= trim($request['address']);
       
        DB::table('tblcompany')->update(
                ['companyName' => $companyname,
                'shortCode' => $shortCode,
                'phoneNo' => $telnum,
                'emailAddress' => $emailid,
                'contactAddress' => $address,]
            );

            return redirect('/company-profile')->with('message','Form Updated');
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
