<?php

namespace App\Http\Controllers\hr;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use Session;
use Auth;
class EarningDeductionController extends functions22Controller
{
    public function index(Request $request){
    	$currentuser = Auth::user()->username;        $data['success'] = "";
        $data['error']   = "";
        $data['courtList']          = $this->getCourts();
        $data['courtdivision']  = $this->getCourtDivision($request['court']);
        $data['getedj']         = [];
        $data['status']         = $request['status'];
        $data['court']          = $request['court'];
        $data['division']       = $request['division'];
        $data['courtstaff']     = [];
        $data['cvdesc']         = $this->getDesc();
        $data['cvdesc1']        = $request['cvdesc'];
        $data['amount']         = $request['amount'];
        
        $data['staff'] = $this->getStaffInfo($request['fileNo']);
        $data['tablecontent']   = $this->getEarningDeductionTableContent($request['court'], $request['division'], $request['status']);
        $data['error']   = "";
        
        $data['staff'] = [];
        $chk = [];
       
        if($request['type'] == 1){
                $filenos = $request['chk_'];
                if(!empty($filenos)){
                    foreach($filenos as $fileno){
                        //echo $fileno.',';
                        $this->updateEarningDeduction($fileno, 1, $currentuser);
                        
                    }
                } elseif($request['chosen'] !== "") {
                    $this->updateEarningDeduction($request['chosen'], 1, $currentuser);
                } else {
                    $data['error'] = "You have to select a staff by clicking the checkbox!";
                }         

        }elseif($request['type'] == 2){
                $filenos = $request['chk_'];
                if(!empty($filenos)){          
                    foreach($filenos as $fileno){
                        //echo $fileno.',';
                        $this->updateEarningDeduction($fileno, 2, $currentuser);
                        
                    }
                } elseif($request['chosen'] !== "") {
                    $this->updateEarningDeduction($request['chosen'], 2, $currentuser);
                } else {
                    $data['error'] = "You have to select a staff by clicking the checkbox!";
                }
        } elseif ($request['type'] == 3){
            $filenos = $request['chk_'];
                if(!empty($filenos)){          
                    foreach($filenos as $fileno){
                        //echo $fileno.',';
                        DB::delete("DELETE FROM tblstaffEarningDeduction WHERE `ID` = '$fileno' AND `status` != 1");
                        
                    }
                } elseif($request['chosen'] !== "") {
                    $fileno = $request['chosen'];
                    DB::delete("DELETE FROM tblstaffEarningDeduction WHERE `ID` = '$fileno' AND `status` != 1");
                } else {
                    $data['error'] = "You have to select a staff by clicking the checkbox!";
                }
        }
        $data['tablecontent']   = $this->getEarningDeductionTableContent($request['court'], $request['division'], $request['status']);
         $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

        return view('EarningDeduction.earningdeduction', $data);
    }
    
    public function edscv(Request $request, $id = ''){
        
        $data['success']            	= 	"";
        $data['error']              	= 	"";
        $data['courtList']          	= 	$this->getCourts();
        $data['courtdivision']      	= 	$this->getCourtDivision($request['court']);
        $data['getedj']             	= 	[];
        
        $data['court']              	= 	$request['court'];
        $data['division']           	= 	$request['division'];
        $data['courtstaff']         	= 	[];
        $data['cvdesc']             	= 	$this->getDesc();
        $data['cvdesc1']            	= 	$request['cvdesc'];
        $data['amount']             	= 	$request['amount'];
        $data['year']               	=  	$request['year'];
        $data['month']              	= 	(string) $request['month'];
        
        
        $data['courtstaff'] 	    	= 	$this->getStaffinDivision( $request['court'], $request['division'] );
        $data['earningtablecontent']   	= 	$this->getEarningDeductionSum($request['fileNo'], $request['year'], $request['month']);
        
        $data['controlvariablecontent'] = 	$this->getStaffControlVariableSum($request['fileNo'], $request['year'], $request['month']);
        
        $data['error']   		= 	"";
        $data['fileNo'] 		= 	$request['fileNo'];
        $data['staff']  		= 	$this->getStaffInfo($request['fileNo']);

         $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

        $chk = [];
        
        return view('EarningDeduction.edscv', $data);
    }
    
    public function gred(Request $request)
    {
        $data['success']            	= "";
        $data['error']              	= "";
        $data['courtList']          	= $this->getCourts();
        $data['courtdivision']      	= $this->getCourtDivision($request['court']);
        $data['getedj']             	= [];
        $data['earndeductionlist']  	= $this->getEarningDeductionList();
        
        $data['court']              	= $request['court'];
        $data['division']           	= $request['division'];
        $data['courtstaff']         	= [];
        $data['cvdesc']             	= $this->getDesc();
        $data['cvdesc1']            	= $request['cvdesc'];
        $data['amount']             	= $request['amount'];
        $data['year']               	= $request['year'];
        $data['month']              	= (string) $request['month'];
        $data['type'] 			= $request['type'];
        
        
        $data['courtstaff'] 		= $this->getStaffinDivision( $request['court'], $request['division'] );
        $data['tablecontent']   	= $this->MonthlyEarningDeductionReportDetails($request['court'], $request['division'], $request['type'], $request['year'], $request['month']);
        $data['courtn']     		= $this->getInfo($request['court'], 'id', 'tbl_court');
        $data['divisionn']  		= $this->getInfo($request['division'], 'divisionID', 'tbldivision');
        $data['typen']      		= $this->getInfo($request['type'], 'ID', 'tblcvSetup');
        $data['yearn']      		= $request['year'];
        $data['monthn']     		= $request['month'];
        $data['error']   		= "";
        
        $data['staff']  		= $this->getStaffInfo($request['fileNo']);
        $chk = [];

         $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}



        return view('EarningDeduction.gred', $data);
    }
    
    public function gred2(Request $request)
    {
        $data['success']            = "";
        $data['error']              = "";
        $data['courtList']          = $this->getCourts();
        $data['courtdivision']      = $this->getCourtDivision($request['court']);
        $data['getedj']             = [];
        $data['earndeductionlist']  = $this->getEarningDeductionList();
        
        $data['court']              = $request['court'];
        $data['division']           = $request['division'];
        $data['courtstaff']         = [];
        $data['cvdesc']             = $this->getDesc();
        $data['cvdesc1']            = $request['cvdesc'];
        $data['amount']             = $request['amount'];
        $data['year']               = $request['year'];
        $data['month']              = (string) $request['month'];
        $data['type'] =             $request['type'];
        
        
        $data['courtstaff'] 		= $this->getStaffinDivision( $request['court'], $request['division'] );
        $data['tablecontent']   	= $this->MonthlyEarningDeductionSummary($request['court'], $request['division'], $request['type'], $request['year'], $request['month']);
        
        $data['error']   		= "";
        
        $data['staff']      		= $this->getStaffInfo($request['fileNo']);
        $data['courtn']     		= $this->getInfo($request['court'], 'id', 'tbl_court');
        $data['divisionn']  		= $this->getInfo($request['division'], 'divisionID', 'tbldivision');
        switch($request['type'])
        {
            case "1":
            $data['typen'] = "Earning";
            break;
            case "2":
            $data['typen'] = "Deduction";
            break;
            case "":
            $data['typen'] = "";
            break;
        }
        
        $data['yearn']      		= $request['year'];
        $data['monthn']     		= $request['month'];
        $chk = [];

          $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

        return view('EarningDeduction.gred2', $data);
    }
    
    public function ogred(Request $request)
    {
        $data['success']            = "";
        $data['error']              = "";
        $data['courtList']          = $this->getCourts();
        $data['courtdivision']      = $this->getCourtDivision($request['court']);
        $data['getedj']             = [];
        $data['earndeductionlist']  = $this->getEarningDeductionList2();
        
        $data['court']              = $request['court'];
        $data['division']           = $request['division'];
        $data['courtstaff']         = [];
        $data['cvdesc']             = $this->getDesc();
        $data['cvdesc1']            = $request['cvdesc'];
        $data['amount']             = $request['amount'];
        $data['year']               = $request['year'];
        $data['month']              = (string) $request['month'];
        $data['type'] = $request['type'];
        
        
        $data['courtstaff'] = $this->getStaffinDivision( $request['court'], $request['division'] );
        $data['tablecontent']   = $this->OccasionalEarningDeductionReportDetails($request['court'], $request['division'], $request['type'], $request['year'], $request['month']);
        
        $data['error']   = "";
        
        $data['staff']      = $this->getStaffInfo($request['fileNo']);
        $data['courtn']     = $this->getInfo($request['court'], 'id', 'tbl_court');
        $data['divisionn']  = $this->getInfo($request['division'], 'divisionID', 'tbldivision');
        $data['typen']      = $this->getInfo($request['type'], 'ID', 'tblearningDeductions');
        $data['yearn']      = $request['year'];
        $data['monthn']     = $request['month'];
        $chk = [];

         $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

        return view('EarningDeduction.ogred', $data);
    }

    public function ogred2(Request $request)
    {
        $data['success']            = "";
        $data['error']              = "";
        $data['courtList']          = $this->getCourts();
        $data['courtdivision']      = $this->getCourtDivision($request['court']);
        $data['getedj']             = [];
        $data['earndeductionlist']  = $this->getEarningDeductionList();
        
        $data['court']              = $request['court'];
        $data['division']           = $request['division'];
        $data['courtstaff']         = [];
        $data['cvdesc']             = $this->getDesc();
        $data['cvdesc1']            = $request['cvdesc'];
        $data['amount']             = $request['amount'];
        $data['year']               = $request['year'];
        $data['month']              = (string) $request['month'];
        $data['type'] =             $request['type'];
        
        
        $data['courtstaff'] 		= $this->getStaffinDivision( $request['court'], $request['division'] );
        $data['tablecontent']   	= $this->OccasionalEarningDeductionSummary($request['court'], $request['division'], $request['type'], $request['year'], $request['month']);
        
        $data['error']   		= "";
        
        $data['staff']      		= $this->getStaffInfo($request['fileNo']);
        $data['courtn']     		= $this->getInfo($request['court'], 'id', 'tbl_court');
        $data['divisionn']  		= $this->getInfo($request['division'], 'divisionID', 'tbldivision');
        
        switch($request['type'])
        {
            case "1":
            $data['typen'] = "Earning";
            break;
            case "2":
            $data['typen'] = "Deduction";
            break;
            case "":
            $data['typen'] = "";
            break;
        }
        
        $data['yearn']      = $request['year'];
        $data['monthn']     = $request['month'];
        $chk = [];

         $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
        return view('EarningDeduction.ogred2', $data);
    }

}