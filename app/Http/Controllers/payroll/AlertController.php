<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;

use App\Http\Requests;
use Carbon\Carbon;
use Session;
use DB;
use DateTime;
use Auth;

class AlertController extends ParentController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function retireList()
    {
         
        $year = date('Y');
       $data['getCentralList'] = DB::table('tblper')
                ->leftJoin('promotion_alert', 'promotion_alert.fileNo', '=', 'tblper.fileNo')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                //->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                //->where('tblper.employee_type', '<>', 'CONSOLIDATED')
                ->where('tblper.staff_status', 1)
                ->where('tblper.fileNo','!=','NJC/000395')
                ->where('tblper.employee_type', '!=', 2)
                ->where('tblper.ID', '!=', 1)
                //->where('promotion_alert.year', '=', $year)
                //->where('promotion_alert.active', '=', 1)
                ->select('*', 'tblper.fileNo as fileNum')
                ->orderBy('tblper.grade', 'Desc')
                //->orderBy('tblper.step', 'Desc')
                //->orderBy('tblper.appointment_date', 'Asc')
                //->get();
                //dd($data['getCentralList']);
                ->paginate(50);
                
                //dd($data['getCentralList']);
               // $howOldAmI = Carbon::createFromDate(1975, 5, 21)->age;

                // dd($howOldAmI);
                return view('estab.retirementAlert',$data);
    }

    public function incrementList()
    {
         
        $year = date('Y');
       $data['getCentralList'] = DB::table('tblper')
                //->leftJoin('promotion_alert', 'promotion_alert.fileNo', '=', 'tblper.fileNo')
                ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                //->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                //->where('tblper.employee_type', '<>', 'CONSOLIDATED')
                ->where('tblper.staff_status', 1)
                 ->where('tblper.employee_type', '!=', 2)
                ->where('tblper.ID', '!=', 1)
                //->where('tblper.fileNo','=','NJC/P000002')
                //->where('promotion_alert.year', '=', $year)
                //->where('promotion_alert.active', '=', 1)
                //->select('*', 'tblper.fileNo as fileNum')
                ->orderBy('tblper.grade', 'Desc')
                //->orderBy('tblper.step', 'Desc')
                //->orderBy('tblper.appointment_date', 'Asc')
                //->get();
                //dd($data['getCentralList']);
                ->paginate(30);
                

               // return view('layouts/increment_alert',$data);
                return view('estab/incrementAlert',$data);
    }

    public function printDoc($id)
    {
        $data['staff'] = DB::table('tblper')->where('ID','=', $id)->first();
        $data['varTemp'] = DB::table('tblvariation_temp')->where('staffid','=', $id)->first();
        $data['comments'] = DB::table('tblvariation_comments')
        ->leftJoin('users','users.id','=','tblvariation_comments.sent_by')
        ->where('staffid','=', $id)
        ->where('reverse','=', 0)
        ->get();

        return view('estab/printDoc',$data);
    }

    public function variationOrder($id)
    {
        $staff = DB::table('tblvariation_temp')->where('staffid','=', $id)->first();
        $data['staff'] = DB::table('tblper')
        ->join('tblvariation_temp','tblvariation_temp.staffid','=','tblper.ID')
        ->where('staffid','=', $id)->first();
        $data['salary'] = DB::table('basicsalaryconsolidated')
        ->where('grade','=', $staff->new_grade)
        ->where('step','=', $staff->new_step)
        ->first();
         $data['oldsalary'] = DB::table('basicsalaryconsolidated')
        ->where('grade','=', $staff->old_grade)
        ->where('step','=', $staff->old_step)
        ->first();
        return view('estab/variationOrder',$data);
    }

    public function storeArrears(Requests $request)
    {
        $list= DB::table('tblvariation_temp')->where('staffid','=', $request['staff'])->first();
        /*DB::table('tblstaff_for_arrears')->insert(array(
                            'staffid'         => $list->ID,
                            'fileNo'          => $list->fileNo,
                            'courtID'         => $list->courtID,
                            'divisionID'      => $list->divisionID,
                            'arrears_type'    => 'increment',
                            'old_grade'       => $list->grade,
                            'old_step'        => $list->step,
                            'new_grade'       => $list->grade,
                            'new_step'        => $newStep,
                            'due_date'        => $list->appointment_date,
                            'approvedBy'      => Auth::user()->name,
                            'approvedDate'    => date('Y-m-d'),
                           ));

                           DB::table('tblper')->where('ID','=',$list->ID)->update(array(
                            'grade'       => $list->grade,
                            'step'        => $list->step,
                            
                           ));*/
        DB::table('tblvariation_temp')->where('staffid','=',$list->staffid)->update(array(
                            'confirm'         => 1,
                            
                           ));
         DB::table('tblvariation_comments')->where('staffid','=',$list->staffid)->where('payment_status','=',0)->update(array(
                            'payment_status'         => 1,
                            
                           ));
    return response()->json('Successfully Approved for Payment');
    }

    public function variationRemark(Request $request)
    {
        
        $staffid   = $request['staffid'];
        $data      = DB::table('tblper')->where('ID','=',$staffid)->first();

        return response()->json($data);
        
    }

     public function saveRemark(Request $request)
    {
        
        $staffid   = $request['staffid'];
        $data      = DB::table('tblper')->where('ID','=',$staffid)->first();

        $code = $request['staffCode'];

        //dd($code);

        if($code == 'VO')
        {

         DB::table('tblvariation_comments')->insert(array(
                            'staffid'         => $staffid ,
                            'comment'         => $request['remark'],
                            'sent_by'         => Auth::user()->id,
                            'sent_to'         => 'PA',
                            'sentby_code'     => 'VO',
                            'updated_at'      => date('Y-m-d'),
                           ));
         
         DB::table('tblper')->where('ID','=',$staffid)->update(array(
                            'promotion_alert'         => 1,
                           
                           ));

        return back()->with('msg','Staff variation sent for further Approval');
        }
        elseif($code == 'PA')
        {
             DB::table('tblvariation_comments')->insert(array(
                            'staffid'         => $staffid,
                            'comment'         => $request['remark'],
                            'sent_by'         => Auth::user()->id,
                            'sent_to'         => 'DD',
                            'sentby_code'     => 'PA',
                            'updated_at'      => date('Y-m-d'),
                           ));

              DB::table('tblvariation_comments')
             ->where('sent_to','=','PA')
             ->where('staffid','=',$staffid)
             ->where('payment_status','=',0) 
             ->update(array(
                            
                            'worked_on'       => 1,
                             ));
         DB::table('tblper')->where('ID','=',$staffid)->update(array(
                            'promotion_alert'         => 2,
                           
                           ));
          return back()->with('msg','Staff variation sent for further Approval');
        }

        elseif($code == 'DD')
        {
             DB::table('tblvariation_comments')->insert(array(
                            'staffid'         => $staffid ,
                            'comment'         => $request['remark'],
                            'sent_by'         => Auth::user()->id,
                            'sent_to'         => 'AS',
                            'sentby_code'     => 'DD',
                            'updated_at'      => date('Y-m-d'),
                           ));
            
             DB::table('tblvariation_comments')
             ->where('sent_to','=','DD')
             ->where('staffid','=',$staffid)
             ->where('payment_status','=',0) 
             ->update(array(
                            
                            'worked_on'       => 1,
                             ));
           DB::table('tblvariation_comments')
         ->where('sent_to','=','PA')
         ->where('worked_on','=',1)
         ->where('staffid','=',$staffid)
          ->update(array(
           'no_recall'     => 1,
            ));
         DB::table('tblper')->where('ID','=',$staffid)->update(array(
                            'promotion_alert'         => 3,
                           
                           ));
          return back()->with('msg','Staff variation sent for further Approval');
        }
        elseif($code == 'AS')
        {
             DB::table('tblvariation_comments')->insert(array(
                            'staffid'         => $staffid ,
                            'comment'         => $request['remark'],
                            'sent_by'         => Auth::user()->id,
                            'sent_to'         => 'DD',
                            'sentby_code'     => 'AS',
                            'updated_at'      => date('Y-m-d'),
                           ));
               DB::table('tblvariation_comments')
             ->where('sent_to','=','AS')
             ->where('staffid','=',$staffid)
             ->where('payment_status','=',0) 
             ->update(array(
                            
                            'worked_on'       => 1,
                             ));
              DB::table('tblvariation_comments')
         ->where('sent_to','=','DD')
         ->where('worked_on','=',1)
         ->where('staffid','=',$staffid)
         ->update(array(
           'no_recall'     => 1,
            ));

         DB::table('tblper')->where('ID','=',$staffid)->update(array(
                            'promotion_alert'         => 2,
                           
                           ));
          return back()->with('msg','Staff variation sent for further Approval');
        }

        
    }

     public function reverseRemark(Request $request)
    {
        
        $staffid   = $request['staffid'];
        $data      = DB::table('tblper')->where('ID','=',$staffid)->first();
        $staff = DB::table('tblvariation_approval_staff')
         ->where('user_id','=',Auth::user()->id)
         ->first();

        $code = $staff->code;
        //dd($code);

        if($code == 'VO')
        {
         $get = DB::table('tblvariation_comments')
         ->where('sent_to','=','PA')
         ->where('worked_on','=',0)
         ->where('staffid','=',$staffid)
         ->first();

         $get2 = DB::table('tblvariation_comments')
         ->where('sent_to','=','VO')
         //->where('sent_by','=',$sentby)
         ->where('worked_on','=',1)
         ->where('payment_status','=',0)
         ->where('staffid','=',$staffid)
         ->first();

         if($get != '')
         {
          DB::table('tblvariation_comments')->where('Id','=',$get->Id)->update(array(
                            'reverse'         => 1,
                            //'worked_on'       => 0,
                           ));
          DB::table('tblvariation_comments')->where('Id','=',$get2->Id)->update(array(
                            //'reverse'         => 1,
                            'worked_on'       => 0,
                           
                           ));

          DB::table('tblvariation_comments')
         ->where('sent_to','=','PA')
         ->where('worked_on','=',1)
         ->where('staffid','=',$staffid)
          ->update(array(
           'no_recall'     => 0,
            ));
           DB::table('tblper')->where('ID','=',$staffid)->update(array(
                            'promotion_alert'         => 1,
                           
                           ));
         }
         
         $del = DB::table('tblvariation_comments')
         ->where('sent_by','=',$staff->user_id)
         ->where('worked_on','=',0)
         ->where('payment_status','=',0)
         ->where('staffid','=',$staffid)
         ->delete();
                  
          return response()->json('Successfully Reversed');
        //return back()->with('msg','Staff variation sent for further Approval');
        }
        elseif($code == 'PA')
        {
             $get = DB::table('tblvariation_comments')
         ->where('sent_to','=','DD')
         ->where('worked_on','=',0)
         ->where('staffid','=',$staffid)
         ->first();

         $get2 = DB::table('tblvariation_comments')
         ->where('sent_to','=','PA')
         //->where('sent_by','=',$sentby)
         ->where('worked_on','=',1)
         ->where('payment_status','=',0)
         ->where('staffid','=',$staffid)
         ->first();

         if($get != '')
         {
          DB::table('tblvariation_comments')->where('Id','=',$get->Id)->update(array(
                            'reverse'         => 1,
                            //'worked_on'       => 0,
                           
                           ));
          DB::table('tblvariation_comments')->where('Id','=',$get2->Id)->update(array(
                            //'reverse'         => 1,
                            'worked_on'       => 0,
                           
                           ));

           DB::table('tblper')->where('ID','=',$staffid)->update(array(
                            'promotion_alert'         => 2,
                           
                           ));
         $del = DB::table('tblvariation_comments')
         ->where('sent_by','=',$staff->user_id)
         ->where('worked_on','=',0)
         ->where('payment_status','=',0)
         ->where('staffid','=',$staffid)
         ->delete();
         }
         //return back()->with('msg','Staff variation sent for further Approval');
           return response()->json('Successfully Reversed');       
        }

        elseif($code == 'DD')
        {
             $get = DB::table('tblvariation_comments')
         ->where('sent_to','=','AS')
         ->where('worked_on','=',0)
         ->where('staffid','=',$staffid)
         ->first();
         $get2 = DB::table('tblvariation_comments')
         ->where('sent_to','=','DD')
         //->where('sent_by','=',$sentby)
         ->where('worked_on','=',1)
         ->where('payment_status','=',0)
         ->where('staffid','=',$staffid)
         ->first();

         if($get != '')
         {
          DB::table('tblvariation_comments')->where('Id','=',$get->Id)->update(array(
                            'reverse'         => 1,
                            //'worked_on'       => 0,
                           
                           ));
          DB::table('tblvariation_comments')->where('Id','=',$get2->Id)->update(array(
                            //'reverse'         => 1,
                            'worked_on'       => 0,
                           
                           ));

           DB::table('tblper')->where('ID','=',$staffid)->update(array(
                            'promotion_alert'         => 3,
                           
                           ));
            $del = DB::table('tblvariation_comments')
         ->where('sent_by','=',$staff->user_id)
         ->where('worked_on','=',0)
         ->where('payment_status','=',0)
         ->where('staffid','=',$staffid)
         ->delete();
         }
          //return back()->with('msg','Staff variation sent for further Approval');
         return response()->json('Successfully Reversed');
        }
        elseif($code == 'AS')
        {
            $sentby = Auth::user()->id;
             $get = DB::table('tblvariation_comments')
         ->where('sent_to','=','DD')
         ->where('sent_by','=',$sentby)
         ->where('worked_on','=',0)
         ->where('staffid','=',$staffid)
         ->first();

         $get2 = DB::table('tblvariation_comments')
         ->where('sent_to','=','AS')
         //->where('sent_by','=',$sentby)
         ->where('worked_on','=',1)
         ->where('payment_status','=',0)
         ->where('staffid','=',$staffid)
         ->first();

         if($get != '')
         {
          DB::table('tblvariation_comments')->where('Id','=',$get->Id)->update(array(
                            'reverse'         => 1,
                            //'worked_on'       => 0,
                           
                           ));

           DB::table('tblvariation_comments')->where('Id','=',$get2->Id)->update(array(
                            //'reverse'         => 1,
                            'worked_on'       => 0,
                           
                           ));

           DB::table('tblper')->where('ID','=',$staffid)->update(array(
                            'promotion_alert'         => 4,
                           
                           ));
         $del = DB::table('tblvariation_comments')
         ->where('sent_by','=',$staff->user_id)
         ->where('worked_on','=',0)
         ->where('payment_status','=',0)
         ->where('staffid','=',$staffid)
         ->delete();
         }
          //return back()->with('msg','Staff variation sent for further Approval');
         return response()->json('Successfully Reversed');
        }

        
    }


    public function reject(Request $request)
    {
        
        $staffid   = $request['staffid'];
        $data      = DB::table('tblper')->where('ID','=',$staffid)->first();

        $code = $request['staffCode'];
        //dd($code);

        /*if($code == 'VO')
        {

         DB::table('tblvariation_comments')->insert(array(
                            'staffid'         => $staffid ,
                            'comment'         => $request['remark'],
                            'sent_by'         => Auth::user()->id,
                            'sent_to'         => 'PA',
                            'rejected'       => 1,
                            'updated_at'      => date('Y-m-d'),
                           ));
          
         DB::table('tblper')->where('ID','=',$staffid)->update(array(
                            'promotion_alert'         => 1,
                           
                           ));

        return back()->with('msg','Staff variation sent for further Approval');
        }*/
        if($code == 'PA')
        {
             DB::table('tblvariation_comments')->insert(array(
                            'staffid'         => $staffid,
                            'comment'         => $request['remark'],
                            'sent_by'         => Auth::user()->id,
                            'sent_to'         => 'VO',
                            'sentby_code'     => 'PA',
                            'rejected'        => 1,
                            'updated_at'      => date('Y-m-d'),
                           ));
            
         DB::table('tblper')->where('ID','=',$staffid)->update(array(
                            'promotion_alert'         => 2,
                           
                           ));
         DB::table('tblvariation_comments')
             ->where('sent_to','=','PA')
             ->where('staffid','=',$staffid)
             ->where('payment_status','=',0) 
             ->update(array(
                            
                            'worked_on'       => 1,
                             ));
          return back()->with('msg','Staff variation sent for further Approval');
        }

        elseif($code == 'DD')
        {
             DB::table('tblvariation_comments')->insert(array(
                            'staffid'         => $staffid ,
                            'comment'         => $request['remark'],
                            'sent_by'         => Auth::user()->id,
                            'sent_to'         => 'PA',
                            'sentby_code'     => 'DD',
                            'rejected'        => 1,
                            'updated_at'      => date('Y-m-d'),
                           ));
             DB::table('tblvariation_comments')
             ->where('sent_to','=','DD')
             ->where('staffid','=',$staffid)
             ->where('payment_status','=',0) 
             ->update(array(
                            
                            'worked_on'       => 1,
                            'no_recall'       => 1,
                             ));
              DB::table('tblvariation_comments')
             ->where('sent_to','=','PA')
             ->where('staffid','=',$staffid)
             ->where('payment_status','=',0) 
             ->update(array(                        
                            'no_recall'       => 1,
                             ));
             
         DB::table('tblper')->where('ID','=',$staffid)->update(array(
                            'promotion_alert'         => 3,
                           
                           ));
          return back()->with('msg','Staff variation sent for further Approval');
        }
        elseif($code == 'AS')
        {
             DB::table('tblvariation_comments')->insert(array(
                            'staffid'         => $staffid ,
                            'comment'         => $request['remark'],
                            'sent_by'         => Auth::user()->id,
                            'sent_to'         => 'DD',
                            'sentby_code'     => 'AS',
                            'rejected'        => 1,
                            'updated_at'      => date('Y-m-d'),
                           ));
               
         DB::table('tblper')->where('ID','=',$staffid)->update(array(
                            'promotion_alert'         => 2,
                           
                           ));
         DB::table('tblvariation_comments')
             ->where('sent_to','=','AS')
             ->where('staffid','=',$staffid)
             ->where('payment_status','=',0) 
             ->update(array(
                            
                            'worked_on'       => 1,
                             ));
              DB::table('tblvariation_comments')
             ->where('sent_to','=','DD')
             ->where('staffid','=',$staffid)
             ->where('payment_status','=',0) 
             ->update(array(                        
                            'no_recall'       => 1,
                             ));
          return back()->with('msg','Staff variation sent for further Approval');
        }

        
    }


    public function variationApproval()
    {
       $data['approver'] = DB::table('tblvariation_approval_staff')
         ->where('user_id','=',Auth::user()->id)
         ->first();
        // $code ='';
      $userId = Auth::user()->id;
         if( $data['approver'] == '')
         {
          $code = '';
         }
         else
         {
          $code = $data['approver']->code;
         }
         //dd($code);
          //$data['variationList'] = DB::table('tblvariation_comments')
         //->join('tblper','tblper.ID','=','tblvariation_comments.staffid')
          /*->where(function ($query) {
                $query->where('sent_to','=',$code)
                      ->orWhere('sent_by', '=', 'AS');
            })
         */
         //->where('payment_status','=',0)
         //->where('worked_on','=',0)
         
        // ->groupBy('tblvariation_comments.staffid')
         //->get();

         /*$data['variationList'] = DB::Select("select * from tblvariation_comments join tblper on tblper.ID = tblvariation_comments.staffid where (sent_to = '$code' ) and sent_by = '$userId' and payment_status =0 and (worked_on =0) group by tblvariation_comments.staffid");*/

         $data['variationList'] = DB::Select("select * from tblvariation_comments join tblper on tblper.ID = tblvariation_comments.staffid where (sent_to = '$code' ) and payment_status =0 and (worked_on =0 or no_recall = 0) and reverse=0 group by tblvariation_comments.staffid");

 //dd($data['variationList'] );

         return view('estab/variationApproval',$data);
        
    }


    public function variationApprovalPA()
    {
       $data['approver'] = DB::table('tblvariation_approval_staff')
         ->where('user_id','=',Auth::user()->id)
         ->first();
        // $code ='';
      $userId = Auth::user()->id;
         if( $data['approver'] == '')
         {
          $code = '';
         }
         else
         {
          $code = $data['approver']->code;
         }
         //dd($code);
          //$data['variationList'] = DB::table('tblvariation_comments')
         //->join('tblper','tblper.ID','=','tblvariation_comments.staffid')
          /*->where(function ($query) {
                $query->where('sent_to','=',$code)
                      ->orWhere('sent_by', '=', 'AS');
            })
         */
         //->where('payment_status','=',0)
         //->where('worked_on','=',0)
         
        // ->groupBy('tblvariation_comments.staffid')
         //->get();

         $data['variationList'] = DB::Select("select * from tblvariation_comments join tblper on tblper.ID = tblvariation_comments.staffid where (sent_to = '$code' ) and sent_by = '$userId' and payment_status =0 and (worked_on =0) group by tblvariation_comments.staffid");

         return view('estab/variationApproval',$data);
        
    }

    public function variationApprovalDD()
    {
       $data['approver'] = DB::table('tblvariation_approval_staff')
         ->where('user_id','=',Auth::user()->id)
         ->first();
        // $code ='';
      $userId = Auth::user()->id;
         if( $data['approver'] == '')
         {
          $code = '';
         }
         else
         {
          $code = $data['approver']->code;
         }
         //dd($code);
          //$data['variationList'] = DB::table('tblvariation_comments')
         //->join('tblper','tblper.ID','=','tblvariation_comments.staffid')
          /*->where(function ($query) {
                $query->where('sent_to','=',$code)
                      ->orWhere('sent_by', '=', 'AS');
            })
         */
         //->where('payment_status','=',0)
         //->where('worked_on','=',0)
         
        // ->groupBy('tblvariation_comments.staffid')
         //->get();

         $data['variationList'] = DB::Select("select * from tblvariation_comments join tblper on tblper.ID = tblvariation_comments.staffid where (sent_to = '$code' ) and sent_by = '$userId' and payment_status =0 and (worked_on =0) group by tblvariation_comments.staffid");

         return view('estab/variationApproval',$data);
        
    }

    public function variationApprovalAudit()
    {
       $data['approver'] = DB::table('tblvariation_approval_staff')
         ->where('user_id','=',Auth::user()->id)
         ->first();
        // $code ='';
      $userId = Auth::user()->id;
         if( $data['approver'] == '')
         {
          $code = '';
         }
         else
         {
          $code = $data['approver']->code;
         }
         //dd($code);
          //$data['variationList'] = DB::table('tblvariation_comments')
         //->join('tblper','tblper.ID','=','tblvariation_comments.staffid')
          /*->where(function ($query) {
                $query->where('sent_to','=',$code)
                      ->orWhere('sent_by', '=', 'AS');
            })
         */
         //->where('payment_status','=',0)
         //->where('worked_on','=',0)
         
        // ->groupBy('tblvariation_comments.staffid')
         //->get();

         $data['variationList'] = DB::Select("select * from tblvariation_comments join tblper on tblper.ID = tblvariation_comments.staffid where (sent_to = '$code' ) and sent_by = '$userId' and payment_status =0 and (worked_on =0) group by tblvariation_comments.staffid");

         return view('estab/variationApproval',$data);
        
    }

    public function rejectReason(Request $request)
    {
        $approver = DB::table('tblvariation_approval_staff')
         ->where('user_id','=',Auth::user()->id)
         ->first();
        $staffid   = $request['staffid'];
        $data      = DB::table('tblvariation_comments')
         ->join('tblper','tblper.ID','=','tblvariation_comments.staffid')
        ->where('staffid','=',$staffid)
        ->where('rejected','=',1)
        ->where('sent_to','=',$approver->code)
        ///->select('comment')
        ->first();

        return response()->json($data);
    }

    public function promotionArrearsEntry()
    {
    $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
      
      $staffses = session('staff_id');
   
     $data['staffData'] = DB::table('tblper')->where('courtID','=',$data['CourtInfo']->courtid)->orderBy('surname')->get();
     $data['staff'] = DB::table('tblper')
        ->join('tblemployment_type','tblemployment_type.id','=','tblper.employee_type')
        ->where('tblper.ID','=', $staffses)
        ->first();
        //dd($data['staff']);
      return view('estab.promotionArrearsEntry',$data);
    }
    
     public function createSes(Request $request)
    {
        
        $staffid   = $request['staff'];
        $request->session()->flash('staff_id',$staffid);
        //Session::put('judge_id',$request['id']);

        return response()->json('set');
        
    }
    
    public function saveEntry(Request $request)
    {
        //dd('ok');
         $insert = DB::table('tblvariation_temp')->insert(array(
       'staffid'                => $request['fileNo'],
       'newEmploymentType'      => $request['newEmpType'],
       'oldEmploymentType'      => $request['newEmpType'],
       'courtID'                => $request['court'],
       'old_grade'              => $request['oldGrade'],
       'old_step'               => $request['oldStep'],
       'new_grade'              => $request['newGrade'],
       'new_step'               => $request['newStep'],
       'due_date'               => $request['dueDate'],
       'divisionID'             => $request['division'],
       'arrears_type'           => $request['arrearsType'],
       
       'payment_status'         => 0,
      ));
      
      /* DB::table('tblper')->where('staffid','=',$request['fileNo'])->update(array(
       'grade_new'              => $request['newGrade'],
       'step_new'               => $request['newStep'],
      ));
      */
      
     /* $staff = $this->getOneStaff($request['fileNo']);
      if($staff != '')
      {
      $this->addLog("Variation added for $staff->surname $staff->first_name $staff->othernames with File Number $staff->fileNo");
      }*/
      if($insert)
      {
        return redirect('/admin/promotion-arrears/entry')->with('msg','Successfully Entered');
      }
      else {
        return redirect('/admin/promotion-arrears/entry')->with('err','Record could not be saved');
      }
      
    }
    public function variationList()
    {
        $data['variationList'] = DB::table('tblvariation_temp')
        ->join('tblper','tblper.ID','=','tblvariation_temp.staffid')
        ->where('tblvariation_temp.treated','=',0)->get();
        return view('estab.variationList',$data);
    }
    public function saveVariation(Request $request)
    {
        $con = $request['confirm'];
        foreach($con as $key => $value)
        {
         $data =  DB::table('tblvariation_temp')->where('staffid','=',$request['confirm'][$key])->first();
            
        DB::table('tblstaff_for_arrears2')->insert(array(
       'staffid'                => $data->staffid,
       'newEmploymentType'      => $data->newEmploymentType,
       'oldEmploymentType'      => $data->oldEmploymentType,
       'courtID'                => $data->courtID,
       'old_grade'              => $data->old_grade,
       'old_step'               => $data->old_step,
       'new_grade'              => $data->new_grade,
       'new_step'               => $data->new_step,
       'due_date'               => $data->due_date,
       'divisionID'             => $data->divisionID,
       'arrears_type'           => $data->arrears_type,
       'payment_status'         => 0,
      ));
      
       DB::table('tblvariation_temp')->where('staffid','=',$request['confirm'][$key])->update(array(
       'treated'         => 1,
      ));
      
     /* DB::table('tblper')->where('staffid','=',$request['confirm'][$key])->update(array(
       'grade'              => $data->new_grade,
       'step'               => $data->new_step,
      ));
      */
        }
        
        return back()->with('msg','Successful');
    }




}
