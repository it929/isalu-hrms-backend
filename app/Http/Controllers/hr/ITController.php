<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ITController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
   {
       $data['form'] = DB::table('tbl_IT')
       ->get();
       
       return view('Nysc.IT', $data);
   }

   public function save (Request $request)
   {
        $validated = $request->validate([
            'firstname' => 'required',
             'lastname' => 'required',
             'email'    => 'required',
             'phone' => 'required',
             'course'    => 'required',
             'startDate' => 'required',
             'endDate'    => 'required',
        ]);

        $firstname = $request->input('firstname');
        $lastname  = $request->input('lastname');
        $email     = $request->input('email');
        $phone     = $request->input('phone');
        $course    = $request->input('course');
        $start = $request->input('startDate');
        $end       = $request->input('endDate');
        
        //dd($request->all());

        $IT = DB::table('tbl_it')->insert([
            'firstname' => $firstname,
            'lastname'  => $lastname,
            'email'     => $email,
            'phonenumber'     => $phone,
            'course'    => $course,
            'start' => $start,
            'end'       => $end,
            
        ]);

        if($IT){
            return redirect()->back()->with('success', 'Saved, record was created successfull.');
        }
        return redirect()->back()->with('danger', 'Sorry, we cannot process your record. Please try again.');
   }

   public function editIT(Request $request)
   {
    $validated = $request->validate([
        'firstname' => 'required',
             'lastname' => 'required',
             'email'    => 'required',
             'phone' => 'required',
             'course'    => 'required',
             'startDate' => 'required',
             'endDate'    => 'required',
        
    ]);

    $recordID = $request->input('recordID');
    $firstname = $request->input('firstname');
        $lastname  = $request->input('lastname');
        $email     = $request->input('email');
        $phone     = $request->input('phone');
        $course    = $request->input('course');
        $start = $request->input('startDate');
        $end       = $request->input('endDate');
    
    // dd($request->all()); 

    $saved =  DB::table('tbl_it')->where('id', $recordID)->update([
        'firstname' => $firstname,
            'lastname'  => $lastname,
            'email'     => $email,
            'phonenumber'     => $phone,
            'course'    => $course,
            'start' => $start,
            'end'       => $end,
        
    ]);
    return redirect()->back()->with('success', 'record updated!');
   }

   public function remove($id)
   {
       $nyscId = DB::table('tbl_it')->where('id', $id)->delete();
       if($nyscId){
            return back()->with('success', 'Student record has been removed');
       }
               
   }
}
