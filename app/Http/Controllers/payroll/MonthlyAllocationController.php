<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class MonthlyAllocationController extends functionFundsController
{
  
  
  public function index(Request $request)
  {

   	$data['error'] = "";
	$data['warning'] = "";
	$data['success'] = "";
   	$month = trim($request['month']);
   	$data['month'] = $month;
   	$year = trim($request['year']);
   	$data['budgettype'] = trim($request['budgettype']);
   	$data['year'] = $year;
      $single = trim($request['single']);
      $data['single'] = $single;
      $id = trim($request['B_id']);

      $updated_date = date('Y-m-d H:i:s');
      $updated_by = Auth::user()->username;

      $data['BudgetType'] = $this->BudgetType();
      $data['budget'] = $this->getBudget($year,$month,$single,$data['budgettype']);
      $data['BudgetSingle'] =  $this->getBudgetSingle($data['year'],$data['budgettype']);

   	if(isset($_POST['insert'])){

            $checkbox = $request['checkbox'];

            $updated_date = date('Y-m-d H:i:s');
            $updated_by = Auth::user()->username;

             if (empty($checkbox)){

                     $year = trim($request['year']);
                     $month = trim($request['month']);

                     $data['year'] = $year; 
                     $data['month'] = $month; 
                     $data['warning'] = "Please click on the checkbox beside the item";
                     $data['budget'] = $this->getBudget($year,$month,$single,$data['budgettype']);
                     return view('allocation.monthly', $data);

               } else{


                  foreach($checkbox as $i) {


                        $fetch = DB::table('tblmonthlyAllocation')->where('ID','=',$i)->first();

                        $year = trim($request['year']);
                        $month = trim($request['month']);
                        $data['year'] = $year;
                        $data['month'] = $month;

                        $B_id = $fetch->budgetID;
                        $ecoid=$fetch->economicID;
                        $get = DB::table('tblbudget')->where('b_id','=',$B_id)->first();

            			$thisyearbudget= DB::Select("SELECT IFNULL(sum(`allocationValue`),0) as allocationValue FROM `tblbudget` WHERE `Period`='$year' and `economicCodeID`='$ecoid' and `AllocationStatus`=1")[0]->allocationValue;
            			
            			$thisyearalloted= DB::Select("SELECT IFNULL(sum(`amount`),0) as totalalloted FROM `tblmonthlyAllocation` WHERE `year`='$year' and `month`<>'$month' and `economicID`='$ecoid' and `status`=1")[0]->totalalloted ;
            			$VoteInfo=$this->VoteInfo($ecoid);
            			$control="yes";
            			switch($control)
            			{
            			case "yes":
            			
            			if(round(($thisyearalloted+$fetch->amount),2)>round($thisyearbudget,2)){
            			
            			$data['warning'] .=" \r\n  $VoteInfo->description cannot be updated Amount Overflow || $thisyearalloted has already been alloted";
            			break;
            			}
			
                        DB::table('tblmonthlyAllocation')->where('ID',$i)->update(array(
                            'status' => '1',
                            'updatedBy' => $updated_by,
                            'updatedDate' => $updated_date,
                        ));
                        $amount=$fetch->amount;
                        $remark="Vote Funding";
                        $this->VotebookUpdate($ecoid,$i,$remark,$amount,Date('Y-m-d'),3,$year);
                        $data['success'] .=" \n $VoteInfo->description updated; ";
                        }
        
                  }

                   $data['success'] = "Update complete: ". $data['success'];
                        $data['budget'] = $this->getBudget($year,$month,$single,$data['budgettype']);
                        return view('allocation.monthly', $data);
               }
        
        //Disapprove Transactions
   	    } elseif(isset($_POST['disapproveTransaction']))
   	    {
   	         $checkbox = $request['checkbox'];
   	         
   	         if (empty($checkbox)){

                     $year = trim($request['year']);
                     $month = trim($request['month']);

                     $data['year'] = $year; 
                     $data['month'] = $month; 
                     $data['warning'] = "Please click on the checkbox beside the item";
                     $data['budget'] = $this->getBudget($year,$month,$single,$data['budgettype']);
                     return redirect()->back(); //view('allocation.monthly', $data);
               } else{
   	            foreach($checkbox as $i) {
                        $fetch = DB::table('tblmonthlyAllocation')->where('ID','=',$i)->first();
                        $year = trim($request['year']);
                        $month = trim($request['month']);
                        $data['year'] = $year;
                        $data['month'] = $month;

                        $B_id = $fetch->budgetID;
                        $ecoid=$fetch->economicID;
                        $get = DB::table('tblbudget')->where('b_id','=',$B_id)->first();

            			$thisyearbudget= DB::Select("SELECT IFNULL(sum(`allocationValue`),0) as allocationValue FROM `tblbudget` WHERE `Period`='$year' and `economicCodeID`='$ecoid' and `AllocationStatus`=1")[0]->allocationValue;
            			
            			$thisyearalloted= DB::Select("SELECT IFNULL(sum(`amount`),0) as totalalloted FROM `tblmonthlyAllocation` WHERE `year`='$year' and `month`<>'$month' and `economicID`='$ecoid' and `status`=1")[0]->totalalloted ;
            			$VoteInfo=$this->VoteInfo($ecoid);
            			$control="yes";
            			switch($control)
            			{
            			case "yes":
            			
            			if(round(($thisyearalloted+$fetch->amount),2)>round($thisyearbudget,2)){
            			
            			$data['warning'] .=" \r\n  $VoteInfo->description cannot be updated Amount Overflow || $thisyearalloted has already been alloted";
            			break;
            			}
			
                        DB::table('tblmonthlyAllocation')->where('ID',$i)->update(array(
                            'status' => '0',
                            'updatedBy' => $updated_by,
                            'updatedDate' => $updated_date,
                        ));
                        $amount=$fetch->amount;
                        $remark="Vote Funding";
                        $this->VotebookUpdate($ecoid,$i,$remark,$amount,Date('Y-m-d'),3,$year);
                        $data['success'] .=" \n $VoteInfo->description updated; ";
                        }
                  }
               }

                   $data['success'] = "Update complete: ". $data['success'];
                        $data['budget'] = $this->getBudget($year,$month,$single,$data['budgettype']);
                        return view('allocation.monthly', $data);
   	    
        } elseif(isset($_POST['update'])){
             
             

                     $i = $request['E_id'];
                     $amount = $request['amount'];
                     $oldamount = $request['oldamount'];
                     $budgetID = $request['budgetID'];
                     $updated_date = date('Y-m-d H:i:s');
                     $updated_by = Auth::user()->username;

                     //Get the monthly allocation details using the ID
                    $fetch = DB::table('tblmonthlyAllocation')->where('ID','=',$i)->first();
                    $year = $fetch->year;
                    $month = $fetch->month;
                    $ecoID = $fetch->economicID;
                    $data['year'] = $year;
                    $data['month'] = $month;

                    

                    $B_id = $fetch->budgetID;
                    $get = DB::table('tblbudget')->where('b_id','=',$B_id)->first();

                    //$total = $get->total_allocation_received + $amount;
                    $Check = $this->TotalUnallocated($ecoID,$month,$year);
                    $control="yes";
        			switch($control){
        			case "yes":
                    if (round($amount,2) > round($Check,2)) {

                        $data['warning'] = " Amount is greater than yearly allocation value";
                        $data['budget'] = $this->getBudget($year,$month,$single,$data['budgettype']);
                        return view('allocation.monthly', $data);
                        break;
                    } 
			$balance=$this->RealVoultBalance($ecoID ,$year);
			//$total_allocaction=DB::table('tblbudget')->where('economicCodeID','=',$ecoID)->where('Period','=',$year)->value('allocationValue');
			//dd($fetch->status . ' '. $fetch->amount.' '. $amount. ' '.$balance);
			//if(($fetch->status==1) && (($fetch->amount-$amount)>$balance )){			
			//$data['warning'] = "This operation cannot be performed|| The balance left in the economic vote is $balance";
			//break;
			//}
			
                  	DB::table('tblmonthlyAllocation')->where('ID',$i)->update([                
                      	'amount' => $amount,
                      	'updatedBy' => $updated_by,
                      	'updatedDate' => $updated_date,
                 	 ]);
			
			if($fetch->status==1){
			$variaceamount=$fetch->amount-$amount;
			if($variaceamount<0){
			$variaceamount=0-$variaceamount;
			$trantype=3;
			$remark="Vote Funding allotment increment";
			}
			else{
			$trantype=4;
			$remark="Vote Funding allotment reduction";
			}
                        if($variaceamount != 0){ 
                         $this->VotebookUpdate($ecoID ,$i,$remark,$variaceamount,Date('Y-m-d'),$trantype,$year);
                        }
                  	}
			
			

                  $data['success'] = "successfully Edited";
                  $data['budget'] = $this->getBudget($year,$month,$single,$data['budgettype']);
                  return view('allocation.monthly', $data);

               }


         } else {

               if(isset($_POST['newinsert'])){

                    $i = $request['U_id'];
                    $updated_date = date('Y-m-d H:i:s');
                    $updated_by = Auth::user()->username;
                    $fetch = DB::table('tblmonthlyAllocation')->where('ID','=',$i)->first();
                    $year = $fetch->year;
                    $month = $fetch->month;
                    $data['year'] = $year;
                    $data['month'] = $month;
                    $ecoid=$fetch->economicID;
                    $thisyearbudget= DB::Select("SELECT IFNULL(sum(`allocationValue`),0) as allocationValue FROM `tblbudget` WHERE `Period`='$year' and `economicCodeID`='$ecoid' and `AllocationStatus`=1")[0]->allocationValue;
                    $thisyearalloted= DB::Select("SELECT IFNULL(sum(`amount`),0) as totalalloted FROM `tblmonthlyAllocation` WHERE `year`='$year' and `month`<>'$month' and `economicID`='$ecoid' and `status`=1")[0]->totalalloted ;
    			$VoteInfo=$this->VoteInfo($ecoid);
    			if(($thisyearalloted+$fetch->amount)>$thisyearbudget){
    			$data['warning'] .=" \r\n  $VoteInfo->description cannot be updated Amount Overflow || $thisyearalloted has already been alloted";
			}
			else{
                  	DB::table('tblmonthlyAllocation')->where('ID',$i)->update([       
                      'status' => '1',
                      'updatedBy' => $updated_by,
                      'updatedDate' => $updated_date,
                  ]);
			        $amount=$fetch->amount;
                    $remark="Vote Funding";
                    $this->VotebookUpdate($ecoid,$i,$remark,$amount,Date('Y-m-d'),3,$year);
                    $data['success'] = "successfully approved";
                  }
                  $data['budget'] = $this->getBudget($year,$month,$single,$data['budgettype']);
                  return view('allocation.monthly', $data);


            } elseif (isset($_POST['delete'])) {
               # code...
               $id = trim($request['B_id']);
               $status = trim($request['status']);

               $confirm = $this->checkStatus($id);

               if ($confirm == TRUE) {
                  
                  $data['warning'] = "Budget has been approved and therefore not be deleted";
               $data['budget'] = $this->getBudget($year,$month,$single,$data['budgettype']);
               return view('allocation.monthly', $data);

               } else {

                  DB::table('tblbudget')->where('b_id', $id)->delete();
                  $data['success'] = " successfully Deleted";
               $data['budget'] = $this->getBudget($year,$month,$single,$data['budgettype']);
               return view('allocation.monthly', $data);

            }
            }

         }



   	return view('allocation.monthly', $data);

   }




   /**********  ***************/

   public function GetAllocationType(){

   	$db = DB::table('tblallocation_type')->select('*')->get(); 
   	return $db;

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

    public function GetEconomicCodeOld($allocationID, $contractGroupID, $economicHead ){

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


	 public function getBudget($year,$month,$single,$budgettype){

      if($single <> ''){
          //dd("dnn1");
          $list = DB::table('tblmonthlyAllocation')
               ->join('tblbudget', 'tblbudget.b_id', '=', 'tblmonthlyAllocation.budgetID')
               ->join('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblbudget.economicCodeID')
               ->select('*',  'tblmonthlyAllocation.status as stat', 'tblmonthlyAllocation.ID as mID')
               ->where('year', $year)
               ->where('month', $month)
               ->where('budgetID', $single)
               ->where('tblbudget.AllocationStatus', '1')
               ->orderby('tblmonthlyAllocation.ID', 'ASC')
               ->orderby('tbleconomicCode.economicCode', 'ASC')
               ->paginate(100);
          
      	
      	

      } elseif($budgettype==''){
//dd("dnn2");
         $list = DB::table('tblmonthlyAllocation')
               ->join('tblbudget', 'tblbudget.b_id', '=', 'tblmonthlyAllocation.budgetID')
               ->join('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblbudget.economicCodeID')
               ->select('*',  'tblmonthlyAllocation.status as stat', 'tblmonthlyAllocation.ID as mID')
               ->where('year', $year)
               ->where('month', $month)
               ->where('tblbudget.AllocationStatus', '1')
               ->orderby('tblmonthlyAllocation.ID', 'ASC')
               ->orderby('tbleconomicCode.economicCode', 'ASC')
               ->paginate(100);

      }
      else{
          //dd("dnn3");
         $list = DB::table('tblmonthlyAllocation')
               ->join('tblbudget', 'tblbudget.b_id', '=', 'tblmonthlyAllocation.budgetID')
               ->join('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblbudget.economicCodeID')
               ->select('*',  'tblmonthlyAllocation.status as stat', 'tblmonthlyAllocation.ID as mID')
               ->where('year', $year)
               ->where('month', $month)
               ->where('tblbudget.economicGroupID', '=', $budgettype)
               ->where('tblbudget.AllocationStatus', '1')
               ->orderby('tblmonthlyAllocation.ID', 'ASC')
               ->orderby('tbleconomicCode.economicCode', 'ASC')
               ->paginate(100);

      }
      foreach ($list as $key => $value) {
    	$lis = (array) $value;
    	$ecoid= $value->economicID;
    	$lis['thisyearalloted']= DB::Select("SELECT IFNULL(sum(`amount`),0) as totalalloted FROM `tblmonthlyAllocation` WHERE `year`='$year' and `economicID`='$ecoid' and `status`=1")[0]->totalalloted ;
    	$lis['allotedtodate']= DB::Select("SELECT IFNULL(sum(`amount`),0) as totalalloted FROM `tblmonthlyAllocation` WHERE `year`='$year' and `economicID`='$ecoid' and `status`=1 and month(str_to_date(left(`month`,3),'%b'))<=month(str_to_date(left('$month',3),'%b'))")[0]->totalalloted ;
    	$value = (object) $lis;
    	$list[$key]  = $value;	
	}
      //dd($list);
      return $list;
   }


   public function getBudgetSingle($period,$budgettype){
       //dd($budgettype);
        if($budgettype==''){
            //dd($budgettype."hchcch");
              $list = DB::table('tblbudget')
            ->join('tblallocation_type', 'tblallocation_type.ID', '=', 'tblbudget.allocationType')
            ->join('tblcontractType', 'tblcontractType.ID', '=', 'tblbudget.economicGroupID')
            ->join('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblbudget.economicCodeID')
            ->join('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tblbudget.economicHeadID')
            ->select('*')
            ->where('tblbudget.AllocationStatus', '1')
            ->where('tblbudget.Period', $period)
            ->orderby('tblbudget.b_id', 'DESC')
            ->get();
    
        }
            else
            {
                //dd($budgettype."hchcch5555");
            $list = DB::table('tblbudget')
            ->join('tblallocation_type', 'tblallocation_type.ID', '=', 'tblbudget.allocationType')
            ->join('tblcontractType', 'tblcontractType.ID', '=', 'tblbudget.economicGroupID')
            ->join('tbleconomicCode', 'tbleconomicCode.ID', '=', 'tblbudget.economicCodeID')
            ->join('tbleconomicHead', 'tbleconomicHead.ID', '=', 'tblbudget.economicHeadID')
            ->select('*')
            ->where('tblbudget.AllocationStatus', '1')
            ->where('tblbudget.Period', $period)
            ->where('tblbudget.economicGroupID', $budgettype)
            ->orderby('tblbudget.b_id', 'DESC')
            ->get();    
        }
      return $list;
   }

	
   
}