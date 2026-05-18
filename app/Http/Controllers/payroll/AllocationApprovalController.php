<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;
//use Illuminate\Http\Support\Facade\View;
use App\Http\Requests;
use DB;
use Auth;

class AllocationApprovalController extends ParentController
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
         $budget = trim($request['budget']);
         $data['budget'] = $budget;
         $period = trim($request['period']);
         $data['period'] = $period;
         $stat = trim($request['stat']);
         $data['stat']= $stat;

         $data['budget'] = $this->getBudget($period, $allocationType, $economicGroup, $economicHead, $stat);
         $data['AllocationType'] = $this->GetAllocationType();
         $data['EconomicGroup'] = $this->GetEconomicGroup();
         $data['EconomicCode'] = $this->GetEconomicCode($allocationType, $economicGroup, $economicHead  );
         $data['EconomicHead'] = $this->GetEconomicHead($economicGroup); 
        
       

      
        //Perform insert

        if(isset($_POST['insert'])){
          $checkbox = $request['checkbox'];

             if (isset($checkbox)){

                     foreach($checkbox as $i) {

                        $fetch = DB::table('tblbudget')->where('b_id','=',$i)->first();
                        $allocationType = $fetch->allocationType;
                        $economicGroup = $fetch->economicGroupID;
                        $economicHead = $fetch->economicHeadID;
                        $period = $fetch->Period;
                        $status = '1';
                        $approved_date = date('Y-m-d H:i:s');
                        $approved_by = Auth::user()->username;

                        DB::table('tblbudget')->where('b_id','=',$i)->update(array(
                           'AllocationStatus' => '1',
                           'approvedBy' => $approved_by,
                           'approvedDate' => $approved_date
                        ));

                     } 


                  $data['allocationType'] = $allocationType;
                        $data['economicGroup'] = $economicGroup;
                        $data['economicHead'] = $economicHead;
                        $data['period'] = $period;
                         $data['success'] = "Approved successfully";
                        $data['budget'] = $this->getBudget($period, $allocationType, $economicGroup, $economicHead, $status);
                        $data['AllocationType'] = $this->GetAllocationType();
                        $data['EconomicGroup'] = $this->GetEconomicGroup();
                        $data['EconomicCode'] = $this->GetEconomicCode($allocationType, $economicGroup, $economicHead );
                        $data['EconomicHead'] = $this->GetEconomicHead($economicGroup); 

                   return view('allocation.approval',$data);


             } else{

                  
                   $data['allocationType'] = $allocationType;
                        $data['economicGroup'] = $economicGroup;
                        $data['economicHead'] = $economicHead;
                        $data['period'] = $period;
                  $data['warning'] = "Please click on the checkbox beside the staff";
                  $data['budget'] = $this->getBudget($period, $allocationType, $economicGroup, $economicHead, $stat);
                  $data['AllocationType'] = $this->GetAllocationType();
                  $data['EconomicGroup'] = $this->GetEconomicGroup();
                  $data['EconomicCode'] = $this->GetEconomicCode($allocationType, $economicGroup, $economicHead  );
                  $data['EconomicHead'] = $this->GetEconomicHead($economicGroup);

                   
                   return view('allocation.approval',$data); 
             }
   

         }


         if (isset($_POST['edit'])) {
        # code...

                     $i = $request['B_id'];
                     $approved_date = date('Y-m-d H:i:s');
                     $approved_by = Auth::user()->username;

                   //dd($staffNamey,$courtn,  $divisionn );

                    

                    $fetch = DB::table('tblbudget')->where('b_id','=',$i)->first();
                        $allocationType = $fetch->allocationType;
                        $economicGroup = $fetch->economicGroupID;
                        $economicHead = $fetch->economicHeadID;
                        $period = $fetch->Period;
                        $status = ' ';

                        DB::table('tblbudget')->where('b_id','=',$i)
                        ->update([
                           'AllocationStatus' => '1',
                           'approvedBy' => $approved_by,
                           'approvedDate' => $approved_date
                        ]);

                        
                        $data['allocationType'] = $allocationType;
                        $data['economicGroup'] = $economicGroup;
                        $data['economicHead'] = $economicHead;
                        $data['period'] = $period;
                         $data['success'] = "Approved successfully";
                        $data['budget'] = $this->getBudget($period, $allocationType, $economicGroup, $economicHead, $status);
                        $data['AllocationType'] = $this->GetAllocationType();
                        $data['EconomicGroup'] = $this->GetEconomicGroup();
                        $data['EconomicCode'] = $this->GetEconomicCode($allocationType, $economicGroup, $economicHead );
                        $data['EconomicHead'] = $this->GetEconomicHead($economicGroup);
                  $data['success'] = "successfully approved";
                  return view('allocation.approval',$data);  


         }

   return view('allocation.approval',$data); 

}


   /********** THIS FUNCTION GETS ALL BANKS TO BE DISPLAYED ON THE LAYOUT ***************/

   public function GetAllocationType(){
   	return DB::Select("SELECT * FROM `tblallocation_type` where status=1"); //Select all banks form database
   }

   public function GetEconomicGroup(){

   	$bank = DB::table('tblcontractType')
      ->where('Status', '1')
      ->select('*')
      ->get(); //Select all banks form database
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
      ->where('Status', '1')
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


	 public function getBudget($period, $allocationType, $economicGroup, $economicHead, $status){
$testing="->where('economicGroupID', $economicGroup)";

            if ($status == '0') {
               
                $list = DB::table('tblbudget')
            ->join('tblallocation_type', 'tblallocation_type.ID', '=', 'tblbudget.allocationType')
            ->join('tblcontractType', 'tblcontractType.ID', '=', 'tblbudget.economicGroupID')
            ->join('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tblbudget.economicHeadID')
            ->join('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblbudget.economicCodeID')
            ->where('period', $period)
            ->where('allocationType', $allocationType)
            //.$testing
            //->where(null, 1)
            ->where('economicGroupID', $economicGroup)
            ->where('AllocationStatus', '0')
            ->select('*')
            //->orderby('tblbudget.b_id', 'DESC')
            ->orderby('tbleconomicCode.economicCode')
            ->paginate(200);
            return $list;

            } elseif($status == '1'){


               $list = DB::table('tblbudget')
            ->join('tblallocation_type', 'tblallocation_type.ID', '=', 'tblbudget.allocationType')
            ->join('tblcontractType', 'tblcontractType.ID', '=', 'tblbudget.economicGroupID')
            ->join('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tblbudget.economicHeadID')
            ->join('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblbudget.economicCodeID')
            ->where('period', $period)
            ->where('allocationType', $allocationType)
            ->where('economicGroupID', $economicGroup)
            ->where('AllocationStatus', '1')
            ->select('*')
            //->orderby('tblbudget.b_id', 'DESC')
            ->orderby('tbleconomicCode.economicCode')
            ->paginate(200);
            return $list;


            } else{
     

   	     $list = DB::table('tblbudget')
            ->join('tblallocation_type', 'tblallocation_type.ID', '=', 'tblbudget.allocationType')
            ->join('tblcontractType', 'tblcontractType.ID', '=', 'tblbudget.economicGroupID')
            ->join('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tblbudget.economicHeadID')
            ->join('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblbudget.economicCodeID')
            ->where('period', $period)
            ->where('allocationType', $allocationType)
            ->where('economicGroupID', $economicGroup)
            ->select('*')
            //->orderby('tblbudget.b_id', 'DESC')
            ->orderby('tbleconomicCode.economicCode')
            ->paginate(50);
            return $list;
         }
      
   }

	
   
}