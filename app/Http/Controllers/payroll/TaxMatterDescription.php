<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;
//use Illuminate\Http\Support\Facade\View;
use App\Http\Requests;
use DB;

class TaxMatterDescription extends ParentController
{
  
  public function displayTaxDescriptionForm()
  {
      
    $data['list']=DB::table('tax_matter_description')->get();
      
   	return view('tax_matter_description.add', $data);
      
  }
  public function addTaxDesc(Request $request){

    $this->validate($request, [
			'description'      	=> 'required',
			]);
	
   
   	$description = trim($request['description']);
   	
   	DB::table('tax_matter_description')->insertGetId(array( 
   	    
			'tax_description' 		 => $description,
			
		));
		
		return back()->with('success','Description successfully added!');

   }
   
   public function deleteTaxDesc(Request $request)
   {
       
       $cid = trim($request['C_id']);
       
       if( DB::table('tblpaymentTransaction')->where('tax_report_description',$cid)->exists() )
       {
           return back()->with('error','Cannot delete! Record is in used.');
       }
       else
       {
           DB::table('tax_matter_description')->where('descriptionID',$cid)->delete();
           
           return back()->with('success','Record deleted!');
       }
   }
   
   public function updateTaxDesc(Request $request)
   {
       
       $descID          = trim($request['descID']);
       $description     = trim($request['description']);
       
       DB::table('tax_matter_description')->where('descriptionID',$descID)->update(['tax_description'=>$description]);
           
       return back()->with('success','Record updated!');
       
   }
   
    public function disableTaxDesc($id)
   {
       
           DB::table('tax_matter_description')->where('descriptionID',$id)->update(['active'=>0]);
           
           return back()->with('success','Record disabled!');
       
   }
   
   public function enableTaxDesc($id)
   {
       
           DB::table('tax_matter_description')->where('descriptionID',$id)->update(['active'=>1]);
           
           return back()->with('success','Record enabled!');
       
   }



}