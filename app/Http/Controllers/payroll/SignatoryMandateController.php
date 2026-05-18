<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
class SignatoryMandateController extends SignatoryMandateBaseController 
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function addLog($operation)
    {
       // $ip = Request::ip();
        $_SERVER['REMOTE_ADDR'];
        $url = Request::fullUrl();
        $userID = Auth::user()->id;
        $nowInNigeria = Carbon::now('Africa/Lagos');
        $cmpname = php_uname('a');
        $host = $_SERVER['HTTP_HOST'];
        DB::table('audit_log')->insert(
            ['comp_name' => $cmpname, 'user_id' => $userID, 'date' => $nowInNigeria, 'ip_addr' => $ip, 'operation' => $operation,
            'host' => $host, 'referer' => $url]);
        return;
    }
    
    
     
     public function displayMandateForm()
     {
       $data['mandateprofile']=DB::table('tblmandatesignatoryprofiles')->get();
       
       $sign1=DB::table('tblmandatesignatory')->where('id',1)->first();
       $sign2=DB::table('tblmandatesignatory')->where('id',2)->first(); 
       

       $data['viewsignatories']=DB::table('tblmandatesignatoryprofiles')
       ->where('tblmandatesignatoryprofiles.id',$sign1->signatoryID)
       ->orwhere('tblmandatesignatoryprofiles.id',$sign2->signatoryID)
       ->leftjoin('tblmandatesignatory', 'tblmandatesignatoryprofiles.id', '=', 'tblmandatesignatory.signatoryID')
       ->select('tblmandatesignatoryprofiles.name','tblmandatesignatory.signatoryID','tblmandatesignatory.id')
       ->get(); 

       return view('signatoryMandate.mandate',$data);
      
     }
     
     public function assignMandate(Request $request)
     {
     
         $firstSignatory =$request->input('firstSignatory');
         $secondSignatory =$request->input('secondSignatory');
     
         $this->validate($request, [
        
          'firstSignatory'      => 'string',
          'secondSignatory'     => 'string',
                    
        ]);
         if(($firstSignatory!=null)&&($secondSignatory==null)){
           $update=DB::table('tblmandatesignatory')->where('id','1')->update(['signatoryID' => $firstSignatory]);
           //$this->addLog('Salary Signatory Changed');
            return redirect('user/signatory-mandate')->with('message','Record Saved!');
         }
         elseif(($secondSignatory!=null)&&($firstSignatory==null)){
           $update2=DB::table('tblmandatesignatory')->where('id','2')->update(['signatoryID' => $secondSignatory ]);
           //$this->addLog('Salary Signatory Changed');
            return redirect('user/signatory-mandate')->with('message','Record Saved!');
         }
         elseif(($secondSignatory==null)&&($firstSignatory==null)){
           //$update=DB::table('tblmandatesignatory')->where('id','1')->update(['signatoryID' => $firstSignatory]);
           //$update2=DB::table('tblmandatesignatory')->where('id','2')->update(['signatoryID' => $secondSignatory ]);
           //$this->addLog('Salary Signatory Changed');
            return redirect('user/signatory-mandate')->with('error_message','Please select either of the field!');
         
          }
         else{
           $update=DB::table('tblmandatesignatory')->where('id','1')->update(['signatoryID' => $firstSignatory]);
           $update2=DB::table('tblmandatesignatory')->where('id','2')->update(['signatoryID' => $secondSignatory ]);
          // $this->addLog('Salary Signatory Changed');
            return redirect('user/signatory-mandate')->with('message','Record Saved!');
         
          }
     }
     
     public function deleteSignatory($id)
     {
     
         $signatoryID = $id;
         $Signatory = '0';

         $update=DB::table('tblmandatesignatory')->where('id',$signatoryID )->update(['signatoryID' => $Signatory ]);
        
         if($update)
         {
            return redirect('user/signatory-mandate')->with('message','Deleted!');
         }
         else
         {
            return redirect('user/signatory-mandate')->with('error_message','Could Not Delete!');
         }
     
     }
    

}