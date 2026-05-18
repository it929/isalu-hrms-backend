<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;
//use Auth;
use App\Http\Requests;
use DB;
use Auth;
use Session;
class PromotionSelfController extends functionController
{
	public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->username = Session::get('userName');
        $this->middleware('auth');
            return $next($request);
        });
    }//
	
	
	
   public function GetPromotion()
   {
		
		$data['error'] = "";
		$data['warning'] = "";
		$data['success'] = "";
		$data['staffid'] = "";
		$data['prevgrade'] = "";	
		$data['prevstep'] = "";	
		$data['newgrade'] = "";	
		$data['newstep'] = "";	
		$data['approvedate'] = "";			
		$staffid=$this->StaffNo($this->username);
		$data['staffPromotion']=DB::Select("SELECT * ,(Select CONCAT(tblper.surname,' ', tblper.first_name ,' ', tblper.othernames) from tblper where tblper.fileNo=tblstaff_promotion.staffid ) as `StaffName`FROM `tblstaff_promotion` WHERE `tblstaff_promotion`.`staffid`='$staffid'");
		
		return view('Promotion.staffpromotion',$data);
   }
   public function PostPromotion(Request $request)
   {   
		$data['error'] = "";
		$data['warning'] = "";
		$data['success'] = "";
		$data['staffid'] = $this->StaffNo($this->username);
		$data['prevgrade'] = trim($request['prevgrade']);
		$data['prevstep'] = trim($request['prevstep']);
		$data['newgrade'] = trim($request['newgrade']);
		$data['newstep'] = trim($request['newstep']);
		$data['approvedate'] = trim($request['approvedate']);		
		
		$promodate		= trim($request['approvedate']);
		$prevgrade		= trim($request['prevgrade']);
		$prevstep		= trim($request['prevstep']);
		$newgrade		= trim($request['newgrade']);
		$newstep		= trim($request['newstep']);
		$staffid		= $this->StaffNo($this->username);
		$delcode		= trim($request['delcode']);
		$updated_date	= (date('Y-m-d'));
		$updatedby 		= $this->username;
		DB::delete("DELETE FROM `tblstaff_promotion` WHERE `promoid`='$delcode'");
		if ( isset( $_POST['add'] ) ) {
		DB::insert("INSERT INTO `tblstaff_promotion`( `promodate`, `prevgrade`, `prevstep`, `newgrade`, `newstep`, `staffid`, `updated_date`, `updatedby`) 
		VALUES ('$promodate','$prevgrade','$prevstep','$newgrade','$newstep','$staffid','$updated_date','$updatedby')");
		}
		$data['staffPromotion']=DB::Select("SELECT *
		,(Select CONCAT(tblper.surname,' ', tblper.first_name ,' ', tblper.othernames) from tblper where tblper.fileNo=tblstaff_promotion.staffid ) as `StaffName`
		FROM `tblstaff_promotion` WHERE `tblstaff_promotion`.`staffid`='$staffid'");
		$data['staffList']=DB::Select("SELECT UserID, fileNo, surname, first_name, othernames,email FROM tblper");
		return view('Promotion.staffpromotion',$data);
		
		
   }
   

}