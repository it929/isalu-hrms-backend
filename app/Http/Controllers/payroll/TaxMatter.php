<?php

namespace App\Http\Controllers\payroll;



use Illuminate\Http\Request;
//use Auth;
use App\Http\Requests;
use DB;
use Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
class TaxMatter extends functionFundsController
{
	

    public function Index(Request $request)
   {  

 //get Session Variables
        //$data['from']	=$request->input('from');
	   //if($data['from']=='') {$data['period']=session('period');}
	   $data['from']	=$request->input('from');
	   //if($data['from']=='') {$data['from']=session('from');}
	   if($data['from']=='') {$data['from']=Carbon::now()->subMonth();}
	   //Session::put('from',  $data['from']);
	   $data['to']	=$request->input('to');
	   //if($data['to']=='') {$data['to']=session('to');}
	   if($data['to']=='') {$data['to']=Carbon::now();}
	   //Session::put('to',  $data['to']);
        $data['element']	=$request->input('element');
        if($data['element']=='') {$data['element']=session('element');}
        Session::put('element',  $data['element']);
        $data['rtype']	=$request->input('rtype');
         if($data['rtype']=='') {$data['rtype']=session('rtype');}
        Session::put('rtype',  $data['rtype']);
        $data['rc']	=$request->input('rc');
        if($data['rc']=='') {$data['rc']=session('rc');}
        Session::put('rc',  $data['rc']);
       $data['getReportDetails']=$this->TaxMatterReport($data['from'],$data['to'],$data['element'],$data['rtype'],$data['rc']);
	    $data['TaxMetterDescription']=$this->TaxMetterDescription();
	    $data['Recurrent_Capital']=$this->Recurrent_Capital();
//dd($data['getReportDetails']);
   	return view('Report.viewTaxReport', $data);
   }
  

}