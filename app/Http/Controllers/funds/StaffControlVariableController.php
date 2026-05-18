<?php

namespace App\Http\Controllers\funds;
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
        $data['courtList']          = $this->getCourts();
        $data['courtdivision']  = [];
        $data['getedj']         = [];
        $data['fileNo']         = ($request['fileNo']) ? $request['fileNo'] : $request['fileNofordelete'];
        $data['court']          = $request['court'];
        $data['division']       = $request['division'];
        $data['courtstaff']     = [];
        $data['cvdesc']         = $this->getDesc();
        $data['cvdesc1']        = $request['cvdesc'];
        $data['amount']         = $request['amount'];
        
        $data['staff'] = $this->getStaffInfo($request['fileNo']);
        $data['tablecontent']   = $this->getTableContent($data['fileNo'], $request['court'], $request['division']);
        
        if($request['court'] !== null){
            
            $data['courtdivision'] = $this->getCourtDivision( $request['court'] );
            if($request['division'] !== null){
                
                $data['courtstaff'] = $this->getStaffinDivision( $request['court'], $request['division'] );

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
                    $data['tablecontent']   = $this->getTableContent($data['fileNo'], $request['court'], $request['division']);
                    //dd($data['staff']);
                }
                

            }

            if(isset($_POST['add'])){
                if((!empty($request['amount']) && is_numeric($request['amount'])) && !empty($request['cvdesc']) && !empty($request['fileNo'])){
                    
                    //submit this control variable
                    $court = $request['court'];
                    $division = $request['division'];
                    $fileno = $request['fileNo'];
                    $cvid = $request['cvdesc'];
                    $amount2 = $request['amount'];
                    //$amount = number_format($amount, 2, '.', ',');
                    if($this->checker($court, $division, $fileno)){

                        //check if the cvID already exists in the DB
                        $count = DB::table('tblstaffCV')->where('cvID', $cvid)->where('fileNo', $fileno)->count();
                        if($count > 0){
                            $data['error'] = 'You cannot add more of such Control variable, you can only update!';
                        } else {
                            if(DB::insert("INSERT INTO tblstaffCV (`courtID`, `divisionID`, `fileNo`, `cvID`, `amount`) 
                            VALUES ('$court', '$division', '$fileno', '$cvid', '$amount2')")){
                                // Store a piece of data in the session...
                                $data['success'] = 'Staff Control Variable was added successfully!';
                                $data['cvdesc1'] = '';
                                $data['amount']         = '';
                                $data['tablecontent']   = $this->getTableContent($request['fileNo'], $request['court'], $request['division']);
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
            //     if(!empty($request['court']) && !empty($request['division']) && (!empty($request['fileNo']) || !empty($request['fileNofordelete']))){
            //         $data['error'] = 'You have empty fields';
            //     }
            // }
        }
        
        return view('CVModule.cvmodule', $data);
	}
}