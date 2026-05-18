<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Carbon\Carbon;
use Session;
use DB;
use DateTime;

class EstabAdminController extends Controller
{
    
    public function index()
    {
        //
    }

    
   public function view_CENTRAL_LIST()
    {   
        $centralByMonth = Session::get('centralByMonth');
        $centralByDay   = Session::get('centralByDay');
        $filterBy       = Session::get('filterBy'); 

        $centralByMonth = Session::forget('centralByMonth');
        $centralByDay   = Session::forget('centralByDay');
        $filterBy       = Session::forget('filterBy'); 
    
        //get All staff due for INCREMENT $this->month (by form $_GET)
        
            $data['getCentralList'] = DB::table('tblper')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
               
                ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
                ->where('tblper.staff_status','=', 1)
                ->orderBy('tblper.grade', 'Desc')
                ->orderBy('tblper.step', 'Desc')
                ->orderBy('tblper.appointment_date', 'Asc')
                ->paginate(10);

                $data['allList'] = DB::table('tblper')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
               
                ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
                ->where('tblper.staff_status','=', 1)
                ->get();
            $data['getDivision'] = DB::table('tbldivision')->orderBy('division', 'Asc')->get();
            $data['headFile'] = "CENTRAL NOMINAL ROLL: LIST OF STAFF DUE FOR INCREMENT IN ALL DIVISIONS";
            return view('estab.index', $data);

            
           
    }

   
    public function test(Request $request)
    {
        
            $data['list'] = DB::table('tblper')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                
                //->where('tblper.employee_type', '<>', 'CONSOLIDATED')
                ->where('tblper.staff_status', 1)
                ->where('tblper.fileNo','=', 183)
               /* ->orderBy('tblper.grade', 'Desc')
                ->orderBy('tblper.step', 'Desc')
                ->orderBy('tblper.appointment_date', 'Asc')*/
                ->first();
                //dd($data);
            //$data['getDivision'] = DB::table('tbldivision')->orderBy('division', 'Asc')->get();
            //$data['headFile'] = "CENTRAL NOMINAL ROLL: LIST OF STAFF DUE FOR INCREMENT IN ALL DIVISIONS";
           
        
        return view('estab.test',$data);
    
    }

    public function getProfile($id)
    {
        $data['promotion'] = "";
         $data['list'] = DB::table('tblper')
         //->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
         ->where('tblper.fileNo','=', $id)
         ->first();

          $data['educations'] = DB::table('tbleducations')
         ->where('fileNo','=', $id)
         ->get();

         $data['records'] = DB::table('recordof_service')
         ->where('fileNo','=', $id)
         ->get();

         $data['promotion'] = DB::table('promotion_detail')
         ->where('fileNo','=', $id)
         ->where('active','=', 1)
         ->first();

          $data['convert'] = DB::table('conversion_advancement')
         ->where('fileNo','=', $id)
         ->where('active','=', 1)
         ->first();

          return view('estab/promotionBrief',$data);
    }

   
     public function promotion()
    {
       return view('estab/promotionBrief');
    }

    public function upgrade($fileNo)
    {
         $data['list'] = DB::table('tblper')
         ->where('tblper.fileNo','=', $fileNo)
         ->first();
          $data['educations'] = DB::table('tbleducations')
         ->where('fileNo','=', $fileNo)
         ->get();

         $data['profbody'] = DB::table('professional_bodies')
         ->where('fileNo','=', $fileNo)
         ->get();
         $data['previous_work'] = DB::table('previous_servicedetails')
         ->where('fileNo','=', $fileNo)
         ->get();
           $data['upgrade'] = DB::table('upgrading_details')
         ->where('fileNo','=', $fileNo)
         ->first();
          $data['convert'] = DB::table('conversion_advancement')
         ->where('fileNo','=', $fileNo)
         ->first();
         //dd($data)
       return view('estab/upgradingForm',$data);
    }

    public function convert_advance($fileNo)
    {
         $data['list'] = DB::table('tblper')
         ->where('tblper.fileNo','=', $fileNo)
         ->first();
          $data['educations'] = DB::table('tbleducations')
         ->where('fileNo','=', $fileNo)
         ->get();

         $data['profbody'] = DB::table('professional_bodies')
         ->where('fileNo','=', $fileNo)
         ->get();
         $data['previous_work'] = DB::table('previous_servicedetails')
         ->where('fileNo','=', $fileNo)
         ->get();
           $data['convert'] = DB::table('upgrading_details')
         ->where('fileNo','=', $fileNo)
         ->first();
         //dd($data)
       return view('estab/conversionForm',$data);
    }
    
    public function conversionList()
    {
       $data['staffList'] = DB::table('tblper')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
                ->where('tblper.staff_status', 1)
                ->orderBy('tblper.grade', 'Desc')
                ->orderBy('tblper.step', 'Desc')
                ->orderBy('tblper.appointment_date', 'Asc')
                ->paginate(10);

                return view('estab/staffConversionList',$data);
    }

    public function promotionList()
    {
     //$dated = date('Y-m-d', strtotime('2/9/2010'));
     
     //dd($dated);
    
     
     
    
        $year = date('Y');
       $data['getCentralList'] = DB::table('tblper')
                ->leftJoin('promotion_alert', 'promotion_alert.fileNo', '=', 'tblper.fileNo')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                //->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                //->where('tblper.employee_type', '<>', 'CONSOLIDATED')
                ->where('tblper.staff_status', 1)
                ->where('tblper.fileNo','!=','NJC/000395')
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

                return view('estab/promotionList',$data);
    }
 

    public function upgradeDetails(Request $request)
    {
        $post   = $request['position'];
        $fileNo = $request['fileNo'];
        $grade  = $request['grade'];
        $step  = $request['step'];
        $reco   = $request['recommendation'];
        $quali  = $request['qualification'];
        $date   = date('Y-m-d');

        $insert = DB::table('upgrading_details')->insert(array( 
                        'fileNo'                        => $fileNo,
                        'additional_qualification'      => $quali, 
                        'post_considered'               => $post,
                        'recommendations'               => $reco,
                        'new_grade'                     => $grade,
                        'new_step'                      => $step,
                        'created_at'                    => $date,
                        'active'                        => 1,
                        
                        ));


        $data = '<div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <strong>Successfully Saved! You can now click on close to exit the dialogue box </strong> 
                </div> ';
        $error_saving = '<div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <strong>Not Saved!</strong> 
                </div> ';
        if($insert)
        {

           return response()->json($data);
        }
        else
        {
           return response()->json($error_saving);
        }
        //return response()->json($fileNo);
    }

    public function saveAdvancement(Request $request)
    {
        $post     = $request['position'];
        $fileNo   = $request['fileNo'];
        $grade    = $request['grade'];
        $effdate  = $request['effdate'];
        $type     = $request['type'];
        $step     = $request['step'];
        $date     = date('Y-m-d');

        $insert = DB::table('conversion_advancement')->insert(array( 
                        'fileNo'                        => $fileNo,
                        'type'                          => $type, 
                        'proposed_post'                 => $post,
                        'effective_date'                => $effdate,
                        'new_grade'                     => $grade,
                        'new_step'                      => $step,
                        'created_at'                    => $date,
                        
                        ));


        $data = '<div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <strong>Successfully Saved! You can now click on close to exit the dialogue box </strong> 
                </div> ';
        $error_saving = '<div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <strong>Not Saved!</strong> 
                </div> ';
        if($insert)
        {

           return response()->json($data);
        }
        else
        {
           return response()->json($error_saving);
        }
    }

    public function savePromotion(Request $request)
    {
        $post     = $request['position'];
        $fileNo   = $request['fileNo'];
        $grade    = $request['grade'];
        $effdate  = $request['effdate'];
        $type     = $request['type'];
        $step     = $request['step'];
        $date     = date('Y-m-d');

        $insert = DB::table('promotion_detail')->insert(array( 
                        'fileNo'                        => $fileNo,
                        'reason'                        => $type, 
                        'proposed_post'                 => $post,
                        'effective_date'                => $effdate,
                        'newgrade'                      => $grade,
                        'newstep'                       => $grade,
                        'date_updated'                  => $date,
                        'active'                        => 1,
                        
                        ));


        $data = '<div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <strong>Successfully Saved! You can now click on close to exit the dialogue box </strong> 
                </div> ';
        $error_saving = '<div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <strong>Not Saved!</strong> 
                </div> ';
        if($insert)
        {

           return response()->json($data);
        }
        else
        {
           return response()->json($error_saving);
        }
    }

    public function autocomplete(Request $request)
    {
        $query = $request->input('query');
        $search = DB::table('tblper')->where('surname', 'LIKE', '%'.$query.'%')->
            orWhere('first_name', 'LIKE', '%'.$query.'%')->orWhere('fileNo', 'LIKE','%'.$query.'%')->take(15)->get();
        $return_array = null;
        foreach($search as $s)
        {
          $return_array[]  =  ["value"=>$s->surname.' '.$s->first_name.' '.$s->othernames.' - '.$s->fileNo, "data"=>$s->fileNo];
        }   
        return response()->json(array("suggestions"=>$return_array));
    }

    public function showAll(Request $request)
    {
        $fileNo=$request->input('nameID');

        $data['staffList'] = DB::table('tblper')
        ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
        ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
        ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
        ->where('tblper.staff_status', 1)
        ->where('tblper.fileNo', '=', $fileNo)
        ->orderBy('tblper.grade', 'Desc')
        ->orderBy('tblper.step', 'Desc')
        ->orderBy('tblper.appointment_date', 'Asc')
        ->paginate(10);  

        return view('estab/staffConversionList',$data);
    }

    public function confirm(Request $request)
    {
        $fileNo = $request->input('fileNo');   
        $confirm = DB::table('promotion_detail')
        ->where('fileNo', '=', $fileNo)
        ->where('active', '=', 1)
        ->first();
         $count = DB::table('promotion_detail')
        ->where('fileNo', '=', $fileNo)
        ->where('active', '=', 1)
        ->count();

        $check = DB::table('tblper')
        ->where('fileNo', '=', $fileNo)
        ->first();
        if($check->promotion_alert ==1 && $check->stepalert==0 && $check->gradealert ==0)
        {
            $message = "Can not be Reverted.Reason: Variation already computed";
        }

        elseif($check->promotion_alert ==1 && $check->stepalert > 0 && $check->gradealert > 0)
        {
            
               DB::table('tblper')->where('fileNo', '=', $fileNo) ->update(array( 
                            'stepalert'                     => 0,
                            'gradealert'                    => 0,
                            'variationreason'               => "",
                            'promotion_alert'               => 0,
                           
                            
                            ));
               return response()->json("Confirmation Successfull");
           
        }

           else
          {
            if($count ==1)
            {
               DB::table('tblper')->where('fileNo', '=', $fileNo) ->update(array( 
                            'stepalert'                     => $confirm->newgrade,
                            'gradealert'                    => $confirm->newstep,
                            'variationreason'               => $confirm->reason,
                            'promotion_alert'               => 1,
                            
                            ));
               return response()->json("Confirmation Successfull");
           }
         }
              
    }

    public function promotionConfirm(Request $request)
    {
        $fileNo = $request->input('fileNo');   
        $confirm = DB::table('promotion_detail')
        ->where('fileNo', '=', $fileNo)
        ->where('active', '=', 1)
        ->first();
         $count = DB::table('promotion_detail')
        ->where('fileNo', '=', $fileNo)
        ->where('active', '=', 1)
        ->count();

        $check = DB::table('tblper')
        ->where('fileNo', '=', $fileNo)
        ->first();
        if($check->promotion_alert ==1 && $check->stepalert==0 && $check->gradealert ==0)
        {
            return response()->json( "Can not be Reverted.Reason: Variation already computed");
        }

        elseif($check->promotion_alert ==1 && $check->stepalert > 0 && $check->gradealert > 0)
        {
            
               DB::table('tblper')->where('fileNo', '=', $fileNo) ->update(array( 
                            'stepalert'                     => 0,
                            'gradealert'                    => 0,
                            'variationreason'               => "",
                            'promotion_alert'               => 0,
                           
                            
                            ));
               return response()->json("Confirmation Successfull");
           
        }

           else
          {
            if($count ==1)
            {
               DB::table('tblper')->where('fileNo', '=', $fileNo) ->update(array( 
                            'stepalert'                     => $confirm->new_grade,
                            'gradealert'                    => $confirm->new_step,
                            'variationreason'               => $confirm->type,
                            'promotion_alert'               => 1,
                            
                            ));
               return response()->json("Confirmation Successfull");
           }
         }
              
    }

    public function getDetails(Request $request)
    {
        $fileNo = $request['fileNo'];
        $profile = DB::table('tblper')->where('fileNo', '=', $fileNo)->get();
        return response()->json($profile);
    }



}
