<?php

namespace App\Http\Controllers\hr;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use Session;
class StaffControlVariableController extends functions22Controller
{
	public function index(Request $request){
        $data['success'] = "";
        $data['error']   = "";
        $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
        $data['courtList']          = $this->getCourts();
        $data['courtdivision']  = [];
        $data['getedj']         = [];
        $data['fileNo']         = ($request['fileNo']) ? $request['fileNo'] : $request['fileNofordelete'];
        $data['court']          = $request['court'];
        $data['division']       = $request['division'];
        $data['courtstaff']     = [];
        $data['hiddenlimit']        = ($request['hiddenlimit'])? $request['hiddenlimit'] : 0;;
        $data['hiddenrecycle']        = ($request['hiddenrecycle'])? $request['hiddenrecycle'] : 0;;
        $data['cvtype']        = $request['cvtype'];
        $data['cvdesc']         = $this->getCV($data['cvtype']);
         
        $data['amount']         = $request['amount'];
        $data['tamount']         = $request['tamount'];
        
        $data['staff'] = $this->getStaffInfo($request['fileNo']);
        $data['tablecontent']   = $this->getTableContent($data['fileNo'], $data['court'], $request['division']);

      
      
	$data['courtstaff'] = $this->getStaffinDivision( $data['court'], $request['division'] );
        
        if($data['court'] !== null){
            
            $data['courtdivision'] = $this->getCourtDivision( $data['court'] );
            if($request['division'] !== null){
                
                

                if(!empty($request['fileNofordelete'])){
                    if($request['deleteid'] !== null){
                        if($this->deleteControlVariable( $request['deleteid'] )){

                            $data['success'] = 'Staff Control Variable Deleted!';

                        } else {

                            $data['error'] = 'Oops! Staff Control variable not deleted!';

                        }
                    } elseif($request['edit-hidden'] !== null){
                        $this->editControlVariable( $request['edit-hidden'], $request['amount-edit']);
                    }
                    $data['staff'] = $this->getStaffInfo($request['fileNofordelete']);
                    $data['tablecontent']   = $this->getTableContent($data['fileNo'], $data['court'], $request['division']);
                    
                }
                

            }

            if(isset($_POST['add'])){
                if((!empty($request['amount']) && is_numeric($request['amount'])) && !empty($request['cvdesc']) && !empty($request['fileNo'])){
                    
                    //submit this control variable
                    $court = $data['court'];
                    $division = $request['division'];
                    $fileno = $request['fileNo'];
                    $cvid = $request['cvdesc'];
                    $amount = $request['amount'];
                    $tamount = $request['tamount'];
		    $hiddenrecycle = $request['hiddenrecycle'];
		     $hiddenlimit= $request['hiddenlimit'];
		    //die($hiddenlimit);
                    if($this->checker($court, $division, $fileno)){

                        //check if the cvID already exists in the DB
                        $count = DB::table('tblstaffCV')->where('cvID', $cvid)->where('staffid', $fileno)->count();
                        if($count > 0){
                            $data['error'] = 'You cannot add more of such Control variable, you can only update!';
                        } else {
                        $cvtype=$data['cvtype'];
                            if(DB::insert("INSERT INTO tblstaffCV (`courtID`, `divisionID`, `staffid`, `cvID`, `amount`, `targetAmount`, `cvtype`,`recycling`) 
                            VALUES ('$court', '$division', '$fileno', '$cvid', '$amount', '$tamount', '$cvtype', '$hiddenlimit')")){
                                // Store a piece of data in the session...
                                $data['success'] = 'Staff Control Variable was added successfully!';
                                //$cv = $this->staffCV($cvid);
                               //$staff = $this->getOneStaff($request['fileNo']);
                               //$this->addLog("$cv->description with Amount of $amount updated for $staff->surname $staff->first_name $staff->othernames with File Number $staff->fileNo from Control Variable");

                                $data['hiddenlimit']  =0;
        			$data['hiddenrecycle']=0;
                                $data['amount']         = '';
                                $data['tamount']        = '';
                                $data['tablecontent']   = $this->getTableContent($request['fileNo'], $data['court'], $request['division']);
                            } else {
                                $data['error'] = 'Oops! something went wrong, Please try again later!';
                            }
                        }
                    } else {
                        $data['error'] = 'Please refresh this page';
                    }
                    
                } else {
                    $data['error'] = 'Enter a valid input';
                }
            }
            
        }
        $data['EarningDeductionType']         = $this->EarningDeductionType();
                return view('CVModule.cvmodule', $data);
	}
	
	
	public function backlogindex(Request $request){
        $data['success'] = "";
        $data['error']   = "";
        $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
      	$data['fileNo']         = ($request['fileNo']);
        $data['courtList']          = $this->getCourts();
        $data['court']          = $request['court'];
        $data['division']       = $request['division'];
        $data['courtstaff']     = [];
        
        if ( isset( $_POST['add'] ) ) {
        $this->validate($request, [
            'fileNo'      => 'required|string',
            'nmonth'      => 'required|string',
            'remarks'      => 'required|string'
        	]);
        	DB::table('tblbacklog')->insert([
	          'staffid' => $request->input('fileNo') ,
	          'remarks' => $request->input('remarks') ,
	          'mcount' => $request->input('nmonth')!=''? $request->input('nmonth'):0 ,
	          'dcount' => $request->input('nday')!=''?$request->input('nday'):0 ,
	          'of_particular_month' => $request->input('ndaycount')!=''?$request->input('ndaycount'):30 ,
	        ]);
	         //$staff = $this->getOneStaff($request['fileNo']);
            //$this->addLog("Backlog added for $staff->surname $staff->first_name $staff->othernames with File Number $staff->fileNo ");
	        return back()->with('message','Successfully added.'  );
	        }
	        
	        if ( isset( $_POST['update'] ) ) {
        $this->validate($request, [
            'nmonth'      => 'required|string',
            'remarks'      => 'required|string'
        	]);
         DB::table('tblbacklog')->where('id',$request->input('id'))->update([
	          'remarks' => $request->input('remarks') ,
	          'mcount' => $request->input('nmonth')!=''? $request->input('nmonth'):0 ,
	          'dcount' => $request->input('nday')!=''?$request->input('nday'):0 ,
	          'of_particular_month' => $request->input('ndaycount')!=''?$request->input('ndaycount'):30 ,
	        ]);
	         //$staff = $this->getOneStaff($request['fileNo']);
            //$this->addLog("Backlog added for $staff->surname $staff->first_name $staff->othernames with File Number $staff->fileNo ");
	        return back()->with('message','Successfully added.'  );
	        }
	        
	         if ( isset( $_POST['delete'] ) ) {
        $this->validate($request, [
            'id'      => 'required|string',
        	]);
        	$id=$request->input('id');
        	
        	DB::delete("DELETE FROM `tblbacklog` WHERE `id`='$id'");
	        return back()->with('message','Successfully removed. Kindly recompute to correct the payroll report'  );
	        }
        $data['staff'] = $this->getStaffInfo($request['fileNo']);
	$data['courtstaff'] = $this->getStaffinDivision( $data['court'], $request['division'] );
        $data['backloglist'] =$this->StaffBackloglist();
        return view('CVModule.backlogs', $data);
	}
	public function ActiveControlVariable(Request $request){
        $data['success'] = "";
        $data['error']   = "";
        $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
      	$data['fileNo']         = ($request['fileNo']);
        $data['courtList']          = $this->getCourts() ;
        $data['court']          = $request['court'];
        $data['division']       = $request['division'];
        $data['cv'] = $request['cv'];
        $data['cvtype']        = $request['cvtype'];
        $data['cvdesc']         = $this->getCV($data['cvtype']);
        $data['EarningDeductionType']         = $this->EarningDeductionType();
        $data['staffCVList']=$this->StaffCVlist($data['cv']);
        return view('CVModule.staffcvlist', $data);
	}
		public function overrideOvertime(Request $request){
        $data['success'] = "";
        $data['error']   = "";
        $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
      	$data['fileNo']         = ($request['fileNo']);
        $data['courtList']          = $this->getCourts();
        $data['court']          = $request['court'];
        $data['division']       = $request['division'];
        $data['courtstaff']     = [];
        
        if ( isset( $_POST['add'] ) ) {
        $this->validate($request, [
            'fileNo'      => 'required|string',
            'gross'      => 'required|numeric|between:0,9999999999999999.99',
            'tax'      => 'required|numeric|between:0,9999999999999999.99',
            'remarks'      => 'required|string'
        	]);
        	if(DB::table('tblspecial_overtime_overide')->where('staffid', $request->input('fileNo'))->first())
        	{
        	 DB::table('tblspecial_overtime_overide')->where('staffid', $request->input('fileNo'))->update(array(
			'gross'	=>$request->input('gross'),
			'tax'	=>$request->input('tax'),
			'remarks'    	=> $request->input('remarks'),
		)); 
        	}
        	else{
        	DB::table('tblspecial_overtime_overide')->insert([
	          'staffid' => $request->input('fileNo') ,
	          'gross' => $request->input('gross') ,
	          'tax' => $request->input('tax') ,
	          'remarks' => $request->input('remarks') ,
	        ]);}
	         $staff = $this->getOneStaff($request['fileNo']);
     //$this->addLog("update special overrime for $staff->surname $staff->first_name $staff->othernames with File Number $staff->fileNo ");
	        return back()->with('message','Successfully added.'  );
	        }
        $data['staff'] = $this->getStaffInfo($request['fileNo']);
	    $data['courtstaff'] = $this->getStaffinDivision( $data['court'], $request['division'] );
        $data['backloglist'] =$this->StaffOvertimeSpecial();
        return view('CVModule.overtime', $data);
	}
}