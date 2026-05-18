<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;
//use Illuminate\Http\Support\Facade\View;
use App\Http\Requests;
use DB;
use Auth;

class AllocationController extends ParentController
{
  
  
  public function index(Request $request){


   	$data['error'] = "";
	   $data['warning'] = "";
	   $data['success'] = "";
   	$allocationType = trim($request['allocationType']);
   	$data['allocationType'] = $allocationType;
   	$economicGroup = trim($request['economicGroup']);
   	$data['economicGroup'] = $economicGroup;
   	$economicCode = trim($request['economicCode']);
   	$data['economicCode'] = $economicCode;
      $economicHead = trim($request['economicHead']);
      $data['economicHead'] = $economicHead;
   	$budget = is_numeric($request['budget'])? $request['budget']:0 ;
      $data['budget'] = $budget;
      $monthly = round($budget/12, 2);
   	$period = trim($request['period']);
      $data['period'] = $period;
   	$status = trim($request['status']);
       $id = trim($request['B_id']);

       $approved_date = date('Y-m-d H:i:s');
        $approved_by = Auth::user()->username;

   	$data['AllocationType'] = $this->GetAllocationType();
   	$data['EconomicGroup'] = $this->GetEconomicGroup();
   	$data['EconomicCode'] = $this->GetEconomicCode($allocationType, $economicGroup, $economicHead );
      $data['EconomicHead'] = $this->GetEconomicHead($economicGroup); 
      $data['budget'] = $this->getBudget($period,$data['economicGroup']);

   	if(isset($_POST['add'])){

          $this->validate($request, [
         'allocationType'         => 'required',
         'economicCode'              => 'required',
         'budget'              => 'required|regex:/^\d+(\.\d{1,2})?$/',
         //'budget'             => 'required|between:0,99.99', either or the above will work for double value validation
         ]);


               $confirm = $this->getStatus($period, $economicCode);

                  if ($confirm == TRUE) {
                  
                     // $data['warning'] = "Sorry! Item already exist";
                     // $data['budget'] = $this->getBudget($period,$data['economicGroup']);
                     // return view('allocation.allocation', $data);
                     
                     //updated if exists
                     $Months = array('January','February','March','April','May','June','July','August','September','October','November','December', );
                     $budgetID = DB::table('tblbudget')->where('Period', $period)->where('economicCodeID', $economicCode)->value('b_id');
                     // dd($economicHead);
                     $updatebudgetID =  DB::table('tblbudget')->where([
                        'Period'   => $period,
                        'economicCodeID'   => $economicCode,
                     ])->update([
                              'allocationValue'       => $budget,
                              'createdby'    => $approved_by,
                              'createdDate'   => $approved_date,
                           ]);
                     if($updatebudgetID){
                           foreach($Months as $m) {
                              DB::table('tblmonthlyAllocation')->where([
                                 'budgetID'  => $budgetID,
                                 'economicID'  => $economicCode,
                                 'year'    => $period,
                                 'month'    => $m,
                              ])->update([ 
                                  'amount'   => $monthly,
                                 ]);
    
                           }
                     }

                     $data['success'] = " successfully updated for $period";
                     $data['budget'] = $this->getBudget($period,$data['economicGroup']);
                     return view('allocation.allocation', $data);

                     } else {

                        $Months = array('January','February','March','April','May','June','July','August','September','October','November','December', );

                       /* DB::insertGetId("INSERT INTO `tblbudget`(`allocationType`, `economicGroupID`, `economicHeadID`,`economicCodeID`, `allocationValue`, `Period`, `AllocationStatus`,`createdby`,`createdDate`) VALUES ('$allocationType','$economicGroup','$economicHead','$economicCode','$budget','$period','0', '$approved_by','$approved_date')"); */

                     $budgetID =  DB::table('tblbudget')->insertGetId([ 
                              'allocationType'       => $allocationType,
                              'economicGroupID'    => $economicGroup,
                              'economicHeadID'    => $economicHead,
                              'economicCodeID'   => $economicCode,
                              'allocationValue'       => $budget,
                              'Period'   => $period,
                              'AllocationStatus'    => '0',
                              'createdby'    => $approved_by,
                              'createdDate'   => $approved_date,
                           ]);



                       foreach($Months as $m) {
                          DB::table('tblmonthlyAllocation')->insert(array( 
                              'budgetID'  => $budgetID,
                              'economicID'  => $economicCode,
                              'year'    => $period,
                              'month'    => $m,
                              'amount'   => $monthly,
                              'status'       => '0',
                           ));

                       }

                        $data['success'] = " successfully added";
                        $data['budget'] = $this->getBudget($period,$data['economicGroup']);
                        return view('allocation.allocation', $data);
                  }
         } 

               if(isset($_POST['edit'])){

                  $budget = trim($request['budget']);

                  $this->validate($request, [
                  'budget'              => 'required|regex:/^\d+(\.\d{1,2})?$/',
                  ]);

                  $newBudget = $budget / 12;
                  $newBudget = round($newBudget, 2);

                  //$confirm = $this->checkStatus($id);
                //   if ($confirm == TRUE) {
                  
                //      $data['warning'] = "Budget has been approved and therefore not be Edited";
                //      $data['budget'] = $this->getBudget($period);
                //      return view('allocation.allocation', $data);

                //      } else{
                


                  DB::table('tblbudget')->where('b_id',$id)->update([
                     
                      'allocationValue' => $budget,
                  ]);

                  DB::table('tblmonthlyAllocation')->where('budgetID',$id)->where('status',0)->update([
                     
                            'amount' =>  $newBudget,
                        ]);

                  $data['success'] = "successfully Edited";
                  $data['budget'] = $this->getBudget($period,$data['economicGroup']);
                  return view('allocation.allocation', $data);
               //}

            } 
            if (isset($_POST['delete'])) {
               # code...
               $id = trim($request['B_id']);
               $status = trim($request['status']);

               $confirm = $this->checkStatus($id);

               if ($confirm == TRUE) {
                  
                  $data['warning'] = "Allocation have been received on this budget therefore cannot be deleted";
               $data['budget'] = $this->getBudget($period,$data['economicGroup']);
               return view('allocation.allocation', $data);

               } else {

                  DB::table('tblbudget')->where('b_id', $id)->delete();
                  DB::table('tblmonthlyAllocation')->where('budgetID', $id)->delete();
                  $data['success'] = " successfully Deleted";
               $data['budget'] = $this->getBudget($period,$data['economicGroup']);
               return view('allocation.allocation', $data);

            }
            }

         



   	return view('allocation.allocation', $data);

   }




   /********** THIS FUNCTION GETS ALL BANKS TO BE DISPLAYED ON THE LAYOUT ***************/

   public function GetAllocationType(){

   	$bank = DB::table('tblallocation_type')
      ->where('status', 1)
      ->select('*')->get(); //Select all banks form database
   	return $bank;

   }

   public function GetEconomicGroup(){

   	$bank = DB::table('tblcontractType')->select('*')->get(); //Select all banks form database
   	return $bank;

   }

   public function GetEconomicHead($contractID){

      $bank = DB::table('tbleconomicHead')
      ->select('*')
      ->where('contractTypeId', $contractID)
      ->where('Status', '1')
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
      //$confir= DB::Select("SELECT * FROM `tblbudget` WHERE `b_id`='$id' AND `AllocationStatus`='1'");
       $confir= DB::Select("SELECT * FROM `tblmonthlyAllocation` WHERE `budgetID`='$id' and `status`=1");
      if(($confir)){return TRUE; } else { return FALSE;}
   }

   public function getStatus($period, $economicCode){


      $confir= DB::Select("SELECT * FROM `tblbudget` WHERE `Period`='$period' AND `economicCodeID`='$economicCode'");
      if(($confir))
         {
            return TRUE;
         }
         else
         {
         return FALSE;
         }

   }


	 public function getBudget($period,$budgettype){
if($budgettype==''){
   	$list = DB::table('tblbudget')
            ->join('tblallocation_type', 'tblallocation_type.ID', '=', 'tblbudget.allocationType')
            ->join('tblcontractType', 'tblcontractType.ID', '=', 'tblbudget.economicGroupID')
            ->join('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblbudget.economicCodeID')
            ->join('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tblbudget.economicHeadID')
            ->where('Period', $period)
            ->select('*')
            //->orderby('tblbudget.b_id', 'ASC')
            ->orderby('tbleconomicCode.economicCode')
            ->paginate(100);
    
}else{
 $list = DB::table('tblbudget')
            ->join('tblallocation_type', 'tblallocation_type.ID', '=', 'tblbudget.allocationType')
            ->join('tblcontractType', 'tblcontractType.ID', '=', 'tblbudget.economicGroupID')
            ->join('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblbudget.economicCodeID')
            ->join('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tblbudget.economicHeadID')
            ->where('Period', $period)
            ->where('economicGroupID', $budgettype)
            ->select('*')
            //->orderby('tblbudget.b_id', 'ASC')
            ->orderby('tbleconomicCode.economicCode')
            ->paginate(100);   
}
            
   	return $list;
   }

	
   
}