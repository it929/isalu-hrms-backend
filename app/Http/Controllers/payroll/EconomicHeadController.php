<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;
//use Illuminate\Http\Support\Facade\View;
use App\Http\Requests;
use DB;

class EconomicHeadController extends ParentController
{
  
  
  public function index(Request $request){


   	$data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
   	$allocationType = trim($request['allocationType']);
   	$data['allocationType'] = $allocationType;
   	$economicGroup = trim($request['economicGroup']);
   	$data['economicGroup'] = $economicGroup;
   	$economicHead = trim($request['economicHead']);
   	$data['economicHead'] = $economicHead;
      $code = trim($request['code']);
      $data['code'] = $code;
   	$status = trim($request['status']);
      $id = trim($request['EcoID']);

   	$data['AllocationType'] = $this->GetAllocationType();
   	$data['EconomicGroup'] = $this->GetEconomicGroup();
   	//$data['EconomicCode'] = $this->GetEconomicCode($allocationType, $economicGroup );
      $data['EconomicHead'] = $this->getEconomicHead();

   	if(isset($_POST['add'])){

          


               $confirm = $this->getStatus($economicGroup, $economicHead);
               $confirm2 = $this->getDescription($economicHead);

               if ($confirm || $confirm2 == TRUE) {
                  
                     $data['warning'] = "Sorry! Item already exists, duplicate items are not allowed";
                     $data['budget'] = $this->getEconomicHead();
                     return view('economicHead.economicHead', $data);

                     } else {
                           

                        DB::insert("INSERT INTO `tbleconomicHead`(`contractTypeID`, `economicHead`, `Code`) VALUES ('$economicGroup','$economicHead', '$code')");

                        $data['success'] = " successfully added";
                        $data['EconomicHead'] = $this->getEconomicHead();
                        return view('economicHead.economicHead', $data);
                     }
                  
         } else {

               if(isset($_POST['edit'])){



                  DB::table('tbleconomicHead')->where('ID',$id)->update([
                     
                      'economicHead' => $economicHead,
                      'Code' => $code,
                      'Status' => $status,
                  ]);

                  $data['success'] = "successfully Edited";
                  $data['EconomicHead'] = $this->getEconomicHead();
                  return view('economicHead.economicHead', $data);
               

               } elseif (isset($_POST['delete'])) {
                  # code...
                          

                              DB::table('tbleconomicHead')->where('ID', $id)->delete();
                              $data['success'] = " successfully Deleted";
                           $data['EconomicHead'] = $this->getEconomicHead();
                           return view('economicHead.economicHead', $data);

                  
               }

         } 



   	return view('economicHead.economicHead', $data);

   }




   /********** THIS FUNCTION GETS ALL BANKS TO BE DISPLAYED ON THE LAYOUT ***************/

   public function GetAllocationType(){

   	$bank = DB::table('tblallocation_type')->select('*')->get(); //Select all banks form database
   	return $bank;

   }

   public function GetEconomicGroup(){

   	$bank = DB::table('tblcontractType')
            ->select('*')
            ->get(); //Select all banks form database
   	return $bank;

   }

     public function GetEconomicCode($allocationID, $contractGroupID, $economicHead ){

   	$bank = DB::table('tbleconomicCode')
   	->select('*')
   	->where('allocationID', $allocationID)
   	->where('contractGroupID', $contractGroupID)
      ->where('economicHeadID', $economicHead)
   	->get(); //Select all banks form database
   	return $bank;



   }
   
   
   public function checkStatus($id){


      $confir= DB::Select("SELECT * FROM `tblbudget` WHERE `b_id`='$id' AND `AllocationStatus`='1'");
      if(($confir))
         {
            return TRUE;
         }
         else
         {
         return FALSE;
         }

   }

   public function getStatus($contractTypeID, $economicHead){


      $confir= DB::Select("SELECT * FROM `tbleconomicHead` WHERE `contractTypeID`='$contractTypeID' AND `economicHead`='$economicHead'");
      if(($confir))
         {
            return TRUE;
         }
         else
         {
         return FALSE;
         }

   }

   public function getDescription($economicHead){


      $confir= DB::Select("SELECT * FROM `tbleconomicHead` WHERE  `economicHead`='$economicHead'");
      if(($confir))
         {
            return TRUE;
         }
         else
         {
         return FALSE;
         }

   }


	 public function getEconomicHead(){

      $list = DB::table('tblcontractType')
            ->leftJoin('tbleconomicHead', 'tbleconomicHead.contractTypeID', '=', 'tblcontractType.ID')
            ->orderby('tbleconomicHead.ID')
            ->paginate(50);

      return $list;
   }

	
   
}