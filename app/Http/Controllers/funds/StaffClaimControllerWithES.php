<?php
//normal staff userid 6 & 237 & 243
//department head userid 26 & 241
//Executive Secretary userid 28
namespace App\Http\Controllers\funds;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\Storage;
use DB;
use File;
use Auth;
use Session;

class StaffClaimController extends Controller
{
    
    //make this page accessible only by authenticated user
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->middleware('auth');
                return $next($request);
        });
    }
    
    
   //controllers for normal staff
    public function index()
    {
    	Session::put('username',Auth::User()->username);
    	
    	//Get all staff Claim

     	  $data['allClaims'] = DB::table('tblclaim')
         ->leftjoin('tbldepartment', 'tbldepartment.id', '=', 'tblclaim.department')
        ->leftjoin('users', 'tblclaim.user', '=', 'users.id')
        ->leftjoin('tblclaim_status', 'tblclaim_status.id', '=', 'tblclaim.status')
        ->select('tblclaim.*', 'users.id', 'users.name', 'tblclaim_status.status as c_status')
        ->where('tblclaim.user', Auth::user()->id)
        ->orderBy('tblclaim.ID', 'Desc')
        ->paginate(100);
    	
    	//Get Staff List
    	$data['staffDetails'] = DB::table('tblStaffInformation')
                        		->leftJoin('tbldepartment', 'tbldepartment.id', '=', 'tblStaffInformation.departmentID')
                        		->orderBy('tblStaffInformation.full_name', 'Asc')
                        		->get();
    	//Get all HOD List
    	$data['allHeadDepartment'] = DB::table('tbldepartment')
                            		->leftJoin('users', 'users.id', '=', 'tbldepartment.head')
                            		->where('tbldepartment.head', '>', 0)
                            		->orderBy('users.name', 'Asc')
                            		->get();
        //Get Attached staff list
        $attachStaff = array();
        foreach($data['allClaims'] as $keyStaffList=>$listClaim)
      	{
            $attachStaff[$keyStaffList] = DB::table('tblselectedstaffclaim')
      	    	->Join('tblclaim', 'tblclaim.ID', '=', 'tblselectedstaffclaim.claimID')
      	    	->Join('tblStaffInformation', 'tblStaffInformation.staffID', '=', 'tblselectedstaffclaim.staffID')
      	    	->where('tblselectedstaffclaim.claimID', $listClaim->ID)
      	    	->orderBy('tblStaffInformation.full_name', 'Asc')
      	    	->select('*', 'tblStaffInformation.fileNo as staffFileNo')
      	    	->get();
      	}
      	$data['attachStaff'] = $attachStaff;
      	
      	//Get all from HOD comments
      	$getStaffCommentArray = array();
      	foreach($data['allClaims'] as $keyClaim=>$listClaim)
      	{
      	    $getStaffCommentArray[$keyClaim] = DB::table('claim_comment')
		                ->leftJoin('users', 'users.id', '=', 'claim_comment.userID')
		      	      	->where('claim_comment.claimID', $listClaim->ID)
		      	      	->where('claim_comment.office', 'HOD')
		      	      	->orderBy('claim_comment.id', 'Desc')
		      	      	->get();
      	}
		$data['getStaffCommentHOD'] = $getStaffCommentArray;
		
		      	      	
        return view('StaffClaim.staffClaim', $data);
    }
    
    //Add new claim
    public function sendClaim(Request $request)
    {
        $userDetails = DB::table('users')->where('id', Auth::user()->id)->first();
        $request['amount'] 		= preg_replace('/[^\d.]/','', $request['amount']);
        $this->validate($request, [
            'title'    =>'required|string', 
            'details'  =>'required|string',
            'claimFileNo'  =>'required|string',
            //'amount'   => 'required|numeric',
            'staffFileNo' => 'array',
            //'claimReciever' => 'required|integer',
        ]);
        $getAllSelected = $request['staffFileNo']; 
        
        //check if staff has been selected
        if(!is_array($getAllSelected)){
        	return back()->with('error', 'It seems you have not selected any staff! Select at least a staff from the list.');
        }
       
        //accept only when staff is/are selected
        if(is_array($getAllSelected)){
            	 $success = DB::table('tblclaim')->insertGetid([
	        	'user' => $userDetails->id, 
	        	'title' => $request->input('title'), 
	        	'details' => $request->input('details'),
	        	'amount' => ($request['amount']  ? $request['amount']  : 0),
	        	'status' =>0,
	        	'created_at' =>date('Y-m-d'),
	        	'departmental_head' =>0,
	        	'claimFileNo' 	=> $request->input('claimFileNo'),
	         ]);
	         //save all selected staff
	        foreach($getAllSelected as $staffID){
        	    DB::table('tblselectedstaffclaim')->insert([
		        'staffID' => $staffID, 
		        'claimID' => $success, 
		        'fileNo' => DB::table('tblStaffInformation')->where('staffID', $staffID)->value('fileNo'), 
		        'created_at' =>date('Y-m-d')
		    ]);
        	}

        }else{
           //save code to fallback in case
             $success = DB::table('tblclaim')->insert([
        	'user' => $userDetails->id, 
        	'title' => $request->input('title'), 
        	'details' => $request->input('details'),
        	'amount' => ($request->input('amount') ? $request->input('amount') : 0),
        	'status' =>3,
        	'created_at' =>date('Y-m-d'),
        	//'departmental_head' => 286, //$request->input('claimReciever'),
        	'claimFileNo' 	=> $request->input('claimFileNo'),
             ]);
        }
      
         if($success)
         {
             return redirect('/staff-claim')->with('message', 'Your claim was submitted successfully.');
         }else
         {
            return redirect('/staff-claim')->with('error', 'An error occured while trying to submit your claim! Please try again.');
         }

    }
    
    //Start pushing claim to HOD
    public function pushClaimHod(Request $request)
    {
    	$userDetails = DB::table('users')->where('id', Auth::user()->id)->first();
        $this->validate($request, [
            'hodName'    	=>'required|integer', 
            'claimID'  		=>'required|string',
        ]);
        $claimID = $request['claimID'];
        $comment = $request['staffComment'];
        //Amount Validation
        $getTotalAmount 	= DB::table('tblclaim')->where('ID', $claimID)->value('amount');
        $getTotalStaffAmount	= DB::table('tblselectedstaffclaim')->where('claimID', $claimID)->sum('staffamount');
        if($getTotalAmount != $getTotalStaffAmount){ 
        	return redirect()->back()->with('error', ' There is variation in total amount and staff amount! Please review the amount allotted to each staff and try again.');
        }
        $success = DB::table('tblclaim')
            ->where('ID', $claimID)
            ->update([
		        'status' =>2,
		         'is_recallableby_applicant' =>1,
		        'departmental_head' => $request->input('hodName'),
	        ]);
    	if($claimID and ($comment != '')){
    		if( $comment != ""){
    		   $this->addComment($comment, $claimID, 'STAFF');
    		}
    		return redirect()->back()->with('message', 'Your record was pushed successfully and your comment was updated.');
    	}
        
        return redirect()->back()->with('message', 'Your record was pushed successfully.');
        
    }//end class
    
    //Add more comment
    public function AddmoreRemark(Request $request)
    {
        $this->validate($request, [
            'rid'    	=>'required|integer', 
        ]);
        if(DB::table('tblselectedstaffclaim')->where('selectedID', $request['rid'] )->update(['remarks' => $request['remark']]))
        return redirect()->back()->with('message', 'Your record remark is successfully updated.');
        
        return redirect()->back()->with('error', 'Sorry, you did not make change to the record!');
        
    }//end class
    
    
    public function pushClaimHU(Request $request)
    {
    	$userDetails = DB::table('users')->where('id', Auth::user()->id)->first();
        $this->validate($request, [
            'hodName'    	=>'required|integer', 
            'claimID'  		=>'required|string',
        ]);
        $claimID = $request['claimID'];
        $comment = $request['staffComment'];
        //Amount Validation
        $getTotalAmount 	= DB::table('tblclaim')->where('ID', $claimID)->value('amount');
        $getTotalStaffAmount	= DB::table('tblselectedstaffclaim')->where('claimID', $claimID)->sum('staffamount');
        if($getTotalAmount != $getTotalStaffAmount){ 
        	return redirect()->back()->with('error', ' There is variation in total amount and staff amount! Please review the amount allotted to each staff and try again.');
        }
        $success = DB::table('tblclaim')
            ->where('ID', $claimID)
            ->update([
		        'status' =>1,
		         'is_recallableby_applicant' =>1,
		         'unit_head' => $request->input('hodName'),
	        ]);
    	if($claimID and ($comment != '')){
    		if( $comment != ""){
    		   $this->addComment($comment, $claimID, 'STAFF');
    		}
    		return redirect()->back()->with('message', 'Your record was pushed successfully and your comment was updated.');
    	}
        
        return redirect()->back()->with('message', 'Your record was pushed successfully.');
        
    }
    
    
    //controllers for department head (HOD)
    public function claimReview( Request $request)
    {
        $data['claims'] = array();
        $status = Session::get('status');
        //get Head ID
        $departmentID = DB::table('tbldepartment')->where('head', Auth::User()->id)->value('id');
        if(empty($departmentID))
        {
        	return redirect()->back()->with('alert', 'Sorry, You are not authorised to perform this action!');
        }
        //Get all record for that department
        
        
        if( isset( $_POST['decline'] )){
         $this->validate($request, [
            'comment'    	=>'required|string',
            'claimid'  =>'required|string',
        ]);   
        $comment 	= trim($request['comment']);
    	$claimid 	= $request['claimid'];
    	$office 	= $request['HOD'];
    	$success 	= 0;
    	$message = "Not Successful! Sorry, we cannot reject this claim! Try again.";
    		$stat=0;
    		if(!DB::table('tblclaim')->where('ID', $claimid)->value('unit_head')=='') {
    		    
    		    $stat=1;
    		    $is_recallableby_headunit=0;
    		    $is_recallableby_applicant=1;
    		    $is_recallableby_hod=1;
    		    $es_recallable=1;
    		}
    		$success =	DB::table('tblclaim')->where('ID', $claimid)->update([ 
    		'status' => $stat
    		,'is_recallableby_headunit' => 0 
    		,'is_recallableby_applicant' => 0 
    		,'is_recallableby_hod' => 1
    		]);
    		$comment='Declined with reason(s):'.$comment;
    		 DB::table('claim_comment')->insert([ 
    			'comment' 	=> trim(preg_replace('/\s\s+/', ' ', $comment)),
    			'userID' 	=> Auth::user()->id,
    			'claimID' 	=> $claimid, 
    			'office' 	=> $office, 
    			'created_at' 	=> date('Y-m-d') 
    		]);
    	if($success){
    		 return back()->with('message', 'Your The claim is successfully reject.');
    	}
    		 return back()->with('error',$message);
            
        }
         if( isset( $_POST['recommendation'] )){
         $this->validate($request, [
            'claimid'  =>'required|string',
        ]);   
        $comment 	= trim($request['comment']);
    	$claimid 	= $request['claimid'];
    	$office 	= $request['HOD'];
    	$success 	= 0;
    	$message = "Not Successful! Sorry, we claim cannot be passed for action ! it is likely action have been perform on it Try again.";
    		$success =	DB::table('tblclaim')->where('ID', $claimid)->update([ 
    		'status' => 3
    		,'is_recallableby_headunit' => 0 
    		,'is_recallableby_applicant' => 0 
    		,'is_recallableby_hod' => 1
    		]);
    		if($comment=='')
    		$comment='Recommended for further action';
    		 DB::table('claim_comment')->insert([ 
    			'comment' 	=> trim(preg_replace('/\s\s+/', ' ', $comment)),
    			'userID' 	=> Auth::user()->id,
    			'claimID' 	=> $claimid, 
    			'office' 	=> $office, 
    			'created_at' 	=> date('Y-m-d') 
    		]);
    	if($success){
    		 return back()->with('message', 'Your The claim is successfully passed for further action.');
    	}
    		 return back()->with('error',$message);
            
        }
         if( isset( $_POST['recall'] )){
         $this->validate($request, [
            'claimid'  =>'required|string',
        ]);   
        $comment 	= trim($request['recall']);
    	$claimid 	= $request['claimid'];
    	$office 	= $request['HOD'];
    	$success 	= 0;
    	$message = "Not Successful! Sorry, we cannot reject this claim! Try again.";
    		$success =	DB::table('tblclaim')->where('ID', $claimid)->update([ 
    		'status' => 2
    		,'is_recallableby_headunit' => 0 
    		,'is_recallableby_applicant' => 0 
    		,'is_recallableby_hod' => 0
    		]);
    		$comment='Previous comment recalled';
    		 DB::table('claim_comment')->insert([ 
    			'comment' 	=> trim(preg_replace('/\s\s+/', ' ', $comment)),
    			'userID' 	=> Auth::user()->id,
    			'claimID' 	=> $claimid, 
    			'office' 	=> $office, 
    			'created_at' 	=> date('Y-m-d') 
    		]);
    	if($success){
    		 return back()->with('message', 'Your The claim is successfully passed for further action.');
    	}
    		 return back()->with('error',$message);
            
        }
         $data['claims'] = DB::table('tblclaim')
         ->leftjoin('tbldepartment', 'tbldepartment.id', '=', 'tblclaim.department')
        ->leftjoin('users', 'tblclaim.user', '=', 'users.id')
        ->leftjoin('tblclaim_status', 'tblclaim.status', '=', 'tblclaim_status.id')
        ->select('tblclaim.*', 'users.id', 'users.name', 'tblclaim_status.status as c_status')
        ->where('tblclaim.departmental_head', Auth::user()->id)
        ->orderBy('tblclaim.ID', 'Desc')
        ->get();
        
        $data['theStatus'] = $status;
        $sta= array(['All' => 3, 'Pending' =>0, 'Approved' =>1, 'Denied' =>2]);
        $data['statuses'] = $sta[0];
	    
       return view('StaffClaim.claimReview', $data);
    }

    

    public function selectStatus(Request $request)
    {
        
        Session::forget('status');
        $status = $request->input('choosenStatus');
        Session::put('status', $status);
        
        return redirect('claim-review');
    }



    //controllers for Executive Secretary
    public function reviewES(Request $request)
    {
        $data['claims'] = array();
        $status = Session::get('status');
        
        
        
        if( isset( $_POST['decline'] )){
         $this->validate($request, [
            'comment'    	=>'required|string',
            'claimid'  =>'required|string',
        ]);   
        $comment 	= trim($request['comment']);
    	$claimid 	= $request['claimid'];
    	$office 	= $request['HOD'];
    	$success 	= 0;
    	$message = "Not Successful! Sorry, we cannot reject this claim! Try again.";
    		$success =	DB::table('tblclaim')->where('ID', $claimid)->update([ 
    		'status' => 2
    		,'is_recallableby_headunit' => 0 
    		,'is_recallableby_applicant' => 0 
    		,'is_recallableby_hod' => 0
    		]);
    		$comment='Declined with reason(s):'.$comment;
    		 DB::table('claim_comment')->insert([ 
    			'comment' 	=> trim(preg_replace('/\s\s+/', ' ', $comment)),
    			'userID' 	=> Auth::user()->id,
    			'claimID' 	=> $claimid, 
    			'office' 	=> $office, 
    			'created_at' 	=> date('Y-m-d') 
    		]);
    	if($success){
    		 return back()->with('message', 'Your claim is successfully reject.');
    	}
    		 return back()->with('error',$message);
            
        }
         if( isset( $_POST['approve'] )){
         $this->validate($request, [
            'claimid'  =>'required|string',
        ]);   
        $comment 	= trim($request['comment']);
        $submitTo 	= trim($request['submitTo']);
    	$claimid 	= $request['claimid'];
    	$claimID=$claimid;
    	$office 	= 'Secretary';
    	$success 	= 0;
    	$message = "Not Successful! Sorry, this action is not processed to avoid duplicate action. Try again.";
    		$curstatus=DB::table('tblclaim')->where('ID', $claimid)->value('status');
    		if(!($curstatus==3 || $curstatus==5) )return back()->with('error', $message);
    		if(DB::table('tblcontractDetails')->where('claimid', $claimID)->first())return back()->with('error', 'This claim has already been approved! Duplicate record not allowed.');
    		$success =	DB::table('tblclaim')->where('ID', $claimid)->update([ 
    		'status' => 6
    		,'is_recallableby_headunit' => 0 
    		,'is_recallableby_applicant' => 0 
    		,'is_recallableby_hod' => 0
    		,'es' => 0
    		]);
    		if($comment=='')
    		$comment='Approve for payment';
    		 DB::table('claim_comment')->insert([ 
    			'comment' 	=> trim(preg_replace('/\s\s+/', ' ', $comment)),
    			'userID' 	=> Auth::user()->id,
    			'claimID' 	=> $claimid, 
    			'office' 	=> $office, 
    			'created_at' 	=> date('Y-m-d') 
    		]);
    	if($success){
    	    $userFileDetails = DB::table('tblStaffInformation')->where('userID', Auth::user()->id)->first();
	            $claimDetails = DB::table('tblclaim')->where('ID', $claimID)->first();
	            //
	            $selectedStaffClaimFullName = DB::table('tblselectedstaffclaim')
	            	->leftJoin('tblStaffInformation', 'tblStaffInformation.staffID', '=', 'tblselectedstaffclaim.staffID')
	            	->where('tblselectedstaffclaim.claimID', $claimID)
	            	->orderBy('tblStaffInformation.staffID', 'Asc')
	            	->value('tblStaffInformation.full_name');
	            $selectedStaffClaimTotal = DB::table('tblselectedstaffclaim')->where('claimID', $claimID)->count();
	            if($selectedStaffClaimTotal == 1){
	            	$beneficiary = $selectedStaffClaimFullName;
	            }else if($selectedStaffClaimTotal == 2){
	            	$beneficiary = $selectedStaffClaimFullName ." and 1 other";
	            }else if($selectedStaffClaimTotal > 2){
	            	$beneficiary = $selectedStaffClaimFullName ." and ". ($selectedStaffClaimTotal -1). " others";
	            }else{
	            	$beneficiary = "";
	            }
	            
	            DB::table('tblcontractDetails')->insert([
	        	'fileNo' => DB::table('tblclaim')->where('ID', $claimID)->value('claimFileNo'),
	        	'procurement_contractID' => $claimID, 
	        	'staffid' => $claimDetails->user,
	        	'ContractDescriptions' => $claimDetails->details,
	        	'contractValue' => $claimDetails->amount,
	        	'companyID' => 13, 
	        	'beneficiary' => $beneficiary,
	        	'dateAward' => date('Y-m-d'), 
	        	'approvedBy' => DB::table('users')->where('id', Auth::user()->id)->value('name'),
	        	'approvalStatus' => 1, 
	        	'approvalDate' => date('Y-m-d'),
	        	'datecreated' => $claimDetails->created_at, 
	        	'openclose' => 0,
	        	'paymentStatus' => 0,
	        	'awaitingActionby' => ($submitTo ? $submitTo : 'DFA'),
	        	'voucherType' =>2,
	        	'claimid' =>$claimID,
	           ]);
    		 return back()->with('message', 'The claim has successfully been approved.');
    	}
    		 return back()->with('error',$message);
            
        }
         if( isset( $_POST['recall'] )){
         $this->validate($request, [
            'claimid'  =>'required|string',
        ]);   
        $comment 	= trim($request['recall']);
    	$claimid 	= $request['claimid'];
    	$office 	= "Secretary";
    	$success 	= 0;
    	$message = "Not Successful! Sorry, this action cannot be complete! Try again.";
    		$success =	DB::table('tblclaim')->where('ID', $claimid)->update([ 
    		'status' => 3
    		,'is_recallableby_headunit' => 0 
    		,'is_recallableby_applicant' => 0 
    		,'is_recallableby_hod' => 0
    		,'es' => 0
    		]);
    		$comment='Previous comment recalled';
    		 DB::table('claim_comment')->insert([ 
    			'comment' 	=> trim(preg_replace('/\s\s+/', ' ', $comment)),
    			'userID' 	=> Auth::user()->id,
    			'claimID' 	=> $claimid, 
    			'office' 	=> $office, 
    			'created_at' 	=> date('Y-m-d') 
    		]);
    	if($success){
    		 return back()->with('message', 'Your The claim is successfully passed for further action.');
    	}
    		 return back()->with('error',$message);
            
        }
         if( isset( $_POST['inview'] )){
         $this->validate($request, [
            'claimid'  =>'required|string',
        ]);   
        $comment 	= trim($request['recall']);
    	$claimid 	= $request['claimid'];
    	$office 	= "Secretary";
    	$success 	= 0;
    	$message = "Not Successful! Sorry, this action cannot be complete! Try again.";
    		$success =	DB::table('tblclaim')->where('ID', $claimid)->update([ 
    		'status' => 5
    		,'is_recallableby_headunit' => 0 
    		,'is_recallableby_applicant' => 0 
    		,'is_recallableby_hod' => 0
    		,'es' => 1
    		]);
    		
    	if($success){
    		 return back()->with('message', 'The claim is successfully placed on-hold for further action.');
    	}
    		 return back()->with('error',$message);
            
        }
        
         if( isset( $_POST['cancelled'] )){
         $this->validate($request, [
            'comment'    	=>'required|string',
            'claimid'  =>'required|string',
        ]);   
        
        $comment 	= trim($request['comment']);
    	$claimid 	= $request['claimid'];
    	$office 	= "Secretary";
    	$success 	= 0;
    	if(DB::table('tblcontractDetails')->where('claimid', $claimid)->first())return back()->with('error', 'This claim cannot be cancelled because it has already been approved!' );
    	$message = "Not Successful! Sorry, this claim cannot cancelled! Try again.";
    		$stat=-1;
    		$success =	DB::table('tblclaim')->where('ID', $claimid)->update([ 
    		'status' => $stat
    		,'is_recallableby_headunit' => 0 
    		,'is_recallableby_applicant' => 0 
    		,'is_recallableby_hod' => 0
    		]);
    		$comment='Declined with reason(s):'.$comment;
    		 DB::table('claim_comment')->insert([ 
    			'comment' 	=> trim(preg_replace('/\s\s+/', ' ', $comment)),
    			'userID' 	=> Auth::user()->id,
    			'claimID' 	=> $claimid, 
    			'office' 	=> $office, 
    			'created_at' 	=> date('Y-m-d') 
    		]);
    	if($success){
    		 return back()->with('message', 'Your The claim is successfully reject.');
    	}
    		 return back()->with('error',$message);
            
        }
         $data['claims'] = DB::table('tblclaim')
         ->leftjoin('tbldepartment', 'tbldepartment.id', '=', 'tblclaim.department')
        ->leftjoin('users', 'tblclaim.user', '=', 'users.id')
        ->leftjoin('tblclaim_status', 'tblclaim.status', '=', 'tblclaim_status.id')
        ->select('tblclaim.*', 'users.id', 'users.name', 'tblclaim_status.status as c_status')
        ->where('tblclaim.es', 1)->orwhere('tblclaim.status', 3)->orwhere('tblclaim.status', 3)
        ->orderBy('tblclaim.ID', 'Desc')
        ->get();
        $data['allActionPayment'] = DB::table('tblaction_rank')
                            		->where('cont_payment_active', 1)
                            		->orderBy('id', 'Asc')
                            		->get();
        $data['theStatus'] = $status;
        $sta= array(['All' => 3, 'Pending' =>0, 'Approved' =>1, 'Denied' =>2]);
        $data['statuses'] = $sta[0];
	    
       return view('StaffClaim.claimsES', $data);
    }


    public function selectDep(Request $request)
    {
        Session::forget('department');
        $department = $request->input('choosenDep');
        Session::put('department', $department);
        
        return redirect('/review-es');
    }

    public function removeStaffFromList(Request $request)
    {
    	$recordID = trim($request['getSelectedStaffID']);
    	$success = 0;
    	$data['successMessage'] = "Sorry, we cannot remove this staff! Try again.";
    	$claimID = DB::table('tblselectedstaffclaim')->where('selectedID', $recordID )->value('claimID');
    	if(DB::table('tblselectedstaffclaim')->where('selectedID', $recordID )->first()){
    		$success = DB::table('tblselectedstaffclaim')->where('selectedID', $recordID )->delete();
    		$data['successMessage'] = "- was removed from the list.";
    	}
    	//Update Original Claim Amount
    	if(!empty($claimID) or $claimID > 0){
              $this->updateClaimAmount($claimID);
        }
	//
    	if($success){
    		return response()->json($data);
    	}
    	return response()->json($data);
    }
    
    
    //Add More Staff to list
    public function addMoreStaffToList(Request $request)
    {
        $getStaffID 	= $request['addMoreStaff'];
        $staffAmount 	= $request['addMoreStaffAmount']; 
    	$claimID 	= trim($request['addMoreStaffClaimID']);
    	//dd($getStaffID);
    	$success = 0;
    	$successAmount = 0;
    	$countAdded = 0;
    	$countDuplicate = 0;
    	$staffExist = null;
    
    	$i = 0;
    	if(count($getStaffID) > 0)
    	{
    	   //get all amount as array
    	    $getAmount = array();
    	    foreach ($getStaffID as $key=>$staffID) 
    	    {
    		    if(!empty($staffAmount[$staffID]))
    		    {
    		        $getAmount[$staffID] = $staffAmount[$staffID];
    		    }
    		}
    		
    	    foreach($getStaffID as $amountKey=>$staffID)
    	    {
    	    	 $staffExistFileNo = DB::table('tblStaffInformation')->where('staffID', $staffID)->value('fileNo');
    	       	 $success = DB::table('tblselectedstaffclaim')->insert([
    			   'staffID' 	 => $staffID, 
    			   'claimID' 	 => $claimID, 
    			   'staffamount' => $getAmount[$staffID], 
    			   'fileNo' 	 => $staffExistFileNo, 
    			   'created_at'  => date('Y-m-d')
    		       ]);
    		      $countAdded ++;
    		    $i ++;
    	   }
    	   //Update Original Claim Amount
    	   if(!empty($claimID) or $claimID > 0){
    	       $successAmount = $this->updateClaimAmount($claimID);
    	   }
	    }
    	if($success or $successAmount){
    		return redirect('/staff-claim')->with('message', $countAdded .' staff has/have been added successfully.');
    	}
    	return redirect('/staff-claim')->with('error', 'Sorry, we cannot add the selected staff! It seems the selected name(s) is/are already on the list. Try again');
    }
    
    
    
    //Update Staff Claim Amount
    public function updateStaffClaimAmount(Request $request)
    {	
    	$this->validate($request, [
            'staffAmount' =>'array|max:999999999',
        ]);
    	$getClaimID = $request['getAddClaimID'];
        $selectedID 	= $request['selectedID'];
        $staffAmount 	= $request['staffAmount']; 
    	$success1 = false;
    	$success2 = false;
    	$addTotalAmount =0.0;
    	$i = 0;
	$countUpdate = 0;
	//get all amount as array
	if(($staffAmount)){
		foreach ($staffAmount as $amount) {
			$arrayAmount[] = $amount;
		}
		//start updating
	    	foreach($selectedID as $staffID){
	       	      $success1 = DB::table('tblselectedstaffclaim')
	       	      ->where('selectedID', $staffID)
	       	      ->update([
			            'staffamount' => $arrayAmount[$i],
		            ]);
		     $addTotalAmount += $arrayAmount[$i];
		     $countUpdate ++;
		     $i ++;
	        }
	        //Update Original Claim Amount
	        if(!empty($getClaimID) or $getClaimID > 0){
	         	$success2 = $this->updateClaimAmount($getClaimID);
	        }
        }
	//
    	if($success1 or $success2){
    		 return redirect('/staff-claim')->with('message', $countUpdate .' staff amount was/were updated. Total Amount = '. $addTotalAmount);
    	}
    	return redirect('/staff-claim')->with('error', 'It seems there is no update occurred!');
    }
    
    
    //Update Claim Title and Description
    public function updateClaimDetails(Request $request)
    {	$this->validate($request, [
            'claimTitle'    	=>'required|string',
            'claimDescription'  =>'required|string',
        ]);
    	$title 		= $request['claimTitle'];
        $details 	= $request['claimDescription'];
        $claimID     	= $request['getClaimID'];
        $success = 0;
        if(DB::table('tblclaim')->where('ID', $claimID)->first()){
        	$success = DB::table('tblclaim')->where('ID', $claimID)->update([
		  'Title' 	=> $title,
		  'details' 	=> $details,
		  'status' 	=> 0,
	     ]);
        }
	//
    	if($success){
    		 return redirect('/staff-claim')->with('message', 'Your claim details was updated successfully');
    	}
    	 return redirect('/staff-claim')->with('error', 'It seems there is no update occurred or we are having problem updating your claim! Please try again.');
    }
    //
    
    
    //Update Amount
    public function updateClaimAmount($claimID)
    {	
    	$success = 0;
    	if(!empty($claimID)){
        	$getAllStaffAmount = 0.0;
        	$getAllStaffAmount = DB::table('tblselectedstaffclaim')->where('claimID', $claimID)->where('active', 1)->sum('staffamount');
            $success = DB::table('tblclaim')->where('ID', $claimID)->update([ 'amount' => $getAllStaffAmount, ]); //'status' => 0
	    }
	    
	    return $success;
    }
    
    
    //Rejection/Deny with comment
    public function claimRejection(Request $request)
    {	
    	$claimID 	= trim($request['claimID']);
    	$claimComment 	= $request['claimComment'];
    	$office 	= $request['office'];
    	$success 	= 0;
    	$message = "Not Successful! Sorry, we cannot reject this claim! Try again.";
    	if($claimComment ==""){
    		return response()->json("Please say something about this action !");
    	}
    	if(DB::table('tblclaim')->where('ID', $claimID)->first())
    	{
    		$success = DB::table('claim_comment')->insert([ 
    			'comment' 	=> trim(preg_replace('/\s\s+/', ' ', $claimComment)),
    			'userID' 	=> Auth::user()->id,
    			'claimID' 	=> $claimID, 
    			'office' 	=> $office, 
    			'created_at' 	=> date('Y-m-d') 
    		]);
    		$message = "Successful! Your response on this claim was successfully submitted";
    		//update claim table status
    		DB::table('tblclaim')->where('ID', $claimID)->update([ 'status' => 2 ]);
    		if($office == 'ES'){
    		    DB::table('tblclaim')->where('ID', $claimID)->update([ 'es_approval' => 2 ]);
    		}
    	}
    	if($success){
    		return response()->json($message);
    	}
    	return response()->json($message);
   
    }
    
    
    //Rejection/Deny with comment
    public function deleteClaim(Request $request)
    {	
    	$claimID 	= trim($request['claimID']);
    	$claimRemove 	= 0;
    	$success 	= 0;
    	$message = "Not Successful! Sorry, we cannot remove this claim from our record! Try again.";
    	if( (!empty($claimID)) and (DB::table('tblclaim')->where('ID', $claimID)->value('status') == 0) )
    	{	
    		$claimRemove = DB::table('tblclaim')->where('ID', $claimID)->delete();
    		$success     = DB::table('tblselectedstaffclaim')->where('claimID', $claimID)->delete();
    		$message = "Successful! Your record has been removed with all attached name(s).";
    	}else{
    		$message = "Not Successful! Sorry, we cannot remove this claim because some offices are working on this record. ";
    	}
    	if($success or $claimRemove ){
    		return response()->json($message);
    	}
    	return response()->json($message);
   
    }
    
    
     public function departmentClaim($id, $num)
    {
        $status = $num;
        
        if($status ==1)
        {
            DB::table('tblclaim')->where('ID',$id)->update(['status'=>$status]);
            return redirect('/claim-review')->with('message','Claim Approved');
        }elseif( $status ==2)
        {
            DB::table('tblclaim')->where('ID',$id)->update(['status'=>$status]);
            return redirect('/claim-review')->with('alert','Claim Rejected');
        }else{
            return redirect('/claim-review')->with('error','An error occured');
        }
        
    }//function
    
    
     //Rejection/Deny with comment
    public function approveStaffClaim(Request $request)
    {	
    	$claimID 	= trim($request['claimID']);
    	$claimComment 	= trim($request['getComment']);
    	$office 	= trim($request['office']);
    	$submitTo   = trim($request['submitTo']);
    	$success 	= 0;
    	if($claimComment == ""){
    		return response()->json("Please say something about this action !");
    	}
    	$message = "Not Successful! Sorry, we cannot approve this claim! Try again.";
    	if(DB::table('tblclaim')->where('ID', $claimID)->first())
    	{
    		$message = "Successful! Your approval was successfully submitted";
    		//update claim table status
    		DB::table('tblclaim')->where('ID', $claimID)->update([ 'status' => 1 ]);
    		if(DB::table('tblclaim')->where('ID', $claimID)->value('es_approval') <> 1){
    		   DB::table('tblclaim')->where('ID', $claimID)->update([ 'es_approval' => 0 ]);
    		}
    		if($office == 'ES'){ 
    		    //
    		    $userFileDetails = DB::table('tblStaffInformation')->where('userID', Auth::user()->id)->first();
	            $claimDetails = DB::table('tblclaim')->where('ID', $claimID)->first();
	            //
	            $selectedStaffClaimFullName = DB::table('tblselectedstaffclaim')
	            	->leftJoin('tblStaffInformation', 'tblStaffInformation.staffID', '=', 'tblselectedstaffclaim.staffID')
	            	->where('tblselectedstaffclaim.claimID', $claimID)
	            	->orderBy('tblStaffInformation.staffID', 'Asc')
	            	->value('tblStaffInformation.full_name');
	            $selectedStaffClaimTotal = DB::table('tblselectedstaffclaim')->where('claimID', $claimID)->count();
	            if($selectedStaffClaimTotal == 1){
	            	$beneficiary = $selectedStaffClaimFullName;
	            }else if($selectedStaffClaimTotal == 2){
	            	$beneficiary = $selectedStaffClaimFullName ." and 1 other";
	            }else if($selectedStaffClaimTotal > 2){
	            	$beneficiary = $selectedStaffClaimFullName ." and ". ($selectedStaffClaimTotal -1). " others";
	            }else{
	            	//save code: in case
	            	$beneficiary = "";
	            }
	            //
	            $success = DB::table('tblcontractDetails')->insert([
	        	'fileNo' => DB::table('tblclaim')->where('ID', $claimID)->value('claimFileNo'),
	        	'procurement_contractID' => $claimID, 
	        	'staffid' => $claimDetails->user,
	        	//'contract_Type' => $request->input('details'),
	        	'ContractDescriptions' => $claimDetails->details,
	        	//'economicVoult' => 1, 
	        	'contractValue' => $claimDetails->amount,
	        	'companyID' => 13, 
	        	'beneficiary' => $beneficiary,
	        	'dateAward' => date('Y-m-d'), 
	        	'approvedBy' => DB::table('users')->where('id', Auth::user()->id)->value('name'),
	        	'approvalStatus' => 1, 
	        	'approvalDate' => date('Y-m-d'),
	        	//'createdby' => 1, 
	        	'datecreated' => $claimDetails->created_at, 
	        	'openclose' => 0,
	        	'paymentStatus' => 0,
	        	//'file_ex' =>0,
	        	'awaitingActionby' => ($submitTo ? $submitTo : 'DFA'),
	        	'voucherType' =>2,
	        	'claimid' =>$claimID,
	           ]);
    		    //
    		    if($success){
    		        if($office == 'ES'){
    		        	$officeApprovealMessage = "approved";
    		        }else{
    		        	$officeApprovealMessage = "recommended";
    		        }
    		        DB::table('tblclaim')->where('ID', $claimID)->update([ 'es_approval' => 1 ]);
    		    	$success = DB::table('claim_comment')->insert([ 
	    			'comment' 	=> trim(preg_replace('/\s\s+/', ' ', $claimComment)).' (' . DB::table('tblselectedstaffclaim')->where('claimID', $claimID)->sum('staffamount') .' '.$officeApprovealMessage .')',
	    			'userID' 	=> Auth::user()->id,
	    			'claimID' 	=> $claimID, 
	    			'office' 	=> $office, 
	    			'created_at' 	=> date('Y-m-d') 
    			]);
    		    }
    		   
    		}
    	}
    	if($success){
    		return response()->json($message);
    	}
    	return response()->json($message);
    }
    
    
    
    //Add more comment
    public function addMoreCommentClaim(Request $request)
    {	
    	$getClaimID 	= trim($request['claimID']);
    	$claimComment 	= trim($request['moreComment']);
    	$office 	= trim($request['office']);
    	$success 	= 0;
    	$message = "Not Successful! Sorry, we cannot add your comment ! Try again.";
    	if( DB::table('tblclaim')->where('ID', $getClaimID)->first() and ($claimComment != '') )
    	{
    		$success = DB::table('claim_comment')->insert([ 
    			'comment' 	=> trim(preg_replace('/\s\s+/', ' ', $claimComment)),
    			'userID' 	=> Auth::user()->id,
    			'claimID' 	=> $getClaimID, 
    			'office' 	=> $office, 
    			'created_at' 	=> date('Y-m-d') 
    		]);
    		
    	}
    	if($success){
    		$message = "Successful! Your comment has been added successfully.";
    		return response()->json($message);
    	}
    	return response()->json($message);
    }
    
    
    //Add only comment to claim DB
    public function addComment($comment, $claimID, $office)
    {
    	if( $comment != "" and $claimID != "" and $office != "" ){
		$success = DB::table('claim_comment')->insert([ 
	    		'comment' 	=> trim(preg_replace('/\s\s+/', ' ', $comment)),
	    		'userID' 	=> Auth::user()->id,
	    		'claimID' 	=> $claimID, 
	    		'office' 	=> $office, 
	    		'created_at' 	=> date('Y-m-d') 
	    	]);
	}
	return;
    }
    
    
    //Attach file to contract
    
    public function basePath()
    {
        return "/home/njcgov/funds.njc.gov.ng/";
    }

    //generate random numbers
    public function randomNo()
    {
        return (uniqid().rand().uniqid());
    }

    public function uploadAttachStaffClaimFile(Request $request)
    {
        Session::put('alertMessage', 1);
        $this->validate($request, [
            //'file' => 'mimes:jpeg,jpg,bmp,png,gif,svg,doc,docx,pdf',
            'file' => 'required|mimes:jpeg,jpg,bmp,png,gif,svg,pdf|max:3000',
        ]);
        $file = $request->file('file');
        $claimID = trim($request['staffClaimID']);
        //
        $messageCode = 0;
        $message     = 'Sorry, we cannot upload this file! Check file format and try again.';
        if($file or !empty($file) or $file != null) {
            //start uploading
            $fileFolder = 'staffClaimFile'; //Live
            $filePath = $this->basePath() . $fileFolder;
            $fileOriginalExtension = $file->getClientOriginalExtension();
            if (DB::table('tblclaim')->where('ID', $claimID)->orWhere('status', 3)->orWhere('status', 2)->first())
            {
                $getFileNoDB = DB::table('tblclaim')->where('ID', $claimID)->value('claimFileNo');
                $fileNewName = $getFileNoDB . '-' .$this->randomNo() . '.' . $fileOriginalExtension;
                if ($file->move($filePath, $fileNewName)) {
                    $fileUploaded = DB::table('staffclaimfile')->insert([ 
    			'claimID' 	 => $claimID,
    			'claimFileNo' 	 => $getFileNoDB,
    			'userID' 	 => Auth::user()->id,
    			'file_name' 	 => $fileNewName, 
    			'caption' 	 => trim($request['caption']), 
    			'file_extension' => $fileOriginalExtension, 
    			'created_at' 	 => date('Y-m-d'),  
    		    ]);
                    if ($fileUploaded) {
                        $messageCode = 1;
                        $message = 'File successfully uploaded.';
                        return redirect('/staff-claim')->with('message', $message);
                    } else {
                        return redirect('/staff-claim')->with('error', $message);
                    }
                }
            }else{
                return redirect('/staff-claim')->with('error', $message);
            }
        }else{
            return redirect('/staff-claim')->with('error', $message);
        }//end if
    }
    
    
  
}//End class