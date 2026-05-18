<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;
use File;
use App\Http\Requests;
use Session;
use DB;

class VariationController extends ParentController
{

    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->middleware(function ($request, $next) {
            $this->division = $request->session()->get('division');
        $this->divisionID = $request->session()->get('divisionID');
        Session::put('this_division', $this->division);
        //Session::forget('hideAlert');
            return $next($request);
                    return $next($request);
        });
    });
    }

    public function create_variation()
    {   
        //(NOTE: ONLY THOSE THAT ARE DUE FOR INCREMENT, PROMOTION etc ARE TO BE POPULATED HERE)
        $data['getStaffIncrement'] = DB::table('tblper')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
            ->where('tblper.divisionID', $this->divisionID)
            ->where('tblper.step', '<>', 'tblper.stepalert')
            ->where('tblper.stepalert', '<>', '')
            ->orwhere('tblper.staff_status', '=', 9)
            //->orwhere('tblper.promotion_alert', '=', 1)
            ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
            ->orderBy('tblper.grade', 'Desc')
            ->orderBy('tblper.step', 'Desc')
            ->orderBy('tblper.appointment_date', 'Asc')
            ->get();
            $data['varR']               = '';
            $data['varNewAmount']       = '';
            $data['this_division']      = $this->division;
            $data['varOldAmount']       = '';
            $data['amountOfVariation']  = 0.0;
        $data['getAllAlert'] = $this->getAlertIncrementPromotion();
        return view('Variation.create', $data);
    }


    //auto fill Variation form STAFF DETAILS
    public function getFindStaff()
    {   
        return redirect('/computer/variation/create');
    }


    public function findStaff(Request $request)
    {   
        $data['getAllAlert'] = '';
        session::forget('staffFileNo');
        $this->validate($request, [
            'staffName' => 'required|numeric',
        ]);
        $fileNo = $request->input('staffName');
        session::put('staffFileNo', $fileNo);
        $data['varR'] = DB::table('tblper')
            ->where('tblper.fileNo', '=',  session::get('staffFileNo'))
            ->first();
            //dd($data['varR']->gradealert);
        $data['varNewAmount'] = DB::table('basicsalary')
                ->where('basicsalary.grade', '=', $data['varR']->gradealert)
                ->where('basicsalary.step',  '=', $data['varR']->stepalert)
                ->first();
               
        $data['varOldAmount'] = DB::table('basicsalary')
                ->where('basicsalary.grade', '=', $data['varR']->grade)
                ->where('basicsalary.step',  '=', $data['varR']->step)
                ->first();
        if($data['varNewAmount'] <> '')
        {
            $data['amountOfVariation'] = ((($data['varNewAmount']->amount) - ($data['varOldAmount']->amount)) * 12);
        }
        else
        {
            $data['amountOfVariation'] = 0.0;
        }
        //get default population
        $data['getStaffIncrement'] = DB::table('tblper')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
            ->where('tblper.divisionID', $this->divisionID)
            ->where('tblper.step', '<>', 'tblper.stepalert')
            ->where('tblper.stepalert', '<>', '')
            ->orwhere('tblper.staff_status', '=', 9)
            //->orwhere('tblper.promotion_alert', '=', 1)
            ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
            ->orderBy('tblper.grade', 'Desc')
            ->orderBy('tblper.step', 'Desc')
            ->orderBy('tblper.appointment_date', 'Asc')
            ->get();
        $data['getAllAlert'] = 0;
        return view('Variation.create', $data);
    }


    //auto fill Variation form STAFF VARIATION
    public function findVariation(Request $request) 
    {   
        $newGrade        = $request->input('newGrade'); 
        $newstep         = $request->input('newstep');
        $variationType   = $request->input('variationType');
        $staffNewAmount = DB::table('basicsalary')
            ->where('basicsalary.grade', '=', session::get('oldGrade'))
            ->where('basicsalary.step',  '=', $newstep)
            ->first();
        return response()->json($staffNewAmount);
    }



    public function update_variation(Request $request)
    {   
        
        /*$this->validate($request, [
            'fileNo'                => 'required|numeric',
            'fileNo'                => 'required|integer|unique:tblvariation,fileNo,year,year,month,' . $month,
            'oldGrade9'              => 'required|numeric',
            'oldStep'               => 'required|numeric',
            'newGrade'              => 'required|numeric',
            'newStep'               => 'required|numeric',
            'newSalary'             => 'required|numeric',
            'amount'                => 'required|string',
            'reasonForVariation'    => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
            'effectiveFrom'         => 'required|date',
            'authority'             => 'regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
            'remark'                => 'string', 
            'variationOrderNo'      => 'required|string|unique:tblvariation,variationorderno',
            'endedDate'             => 'required|date',
            'laterThan'             => 'required|date',
        ]);*/

        $month                = date('m');
        $year                 = date('Y');
        
        $fileNo                     = trim($request['fileNumber']);
        $rank                       = trim($request['rank']);
        $oldGrade                   = trim($request['oldGrade']);
        $oldStep                    = trim($request['oldStep']);
        $newGrade                   = trim($request['newGrade']);
        $newStep                    = trim($request['newStep']);
        $newSalary                  = trim($request['newSalary']);
        $amount                     = trim($request['amount']);
        $reasonForVariation         = trim($request['reasonForVariation']);
        $effectiveFrom              = trim($request['effectiveFrom']);
        $authority                  = trim($request['authority']); 
        $remark                     = $request['remark'];
        $variationOrderNo           = $request['variationOrderNo'];
        $endedDate                  = $request['endedDate'];
        $laterThan                  = $request['laterThan'];
        $date                       = date("Y-m-d");
        
        if($variationOrderNo == ""){
            return redirect('/computer/variation/create')->with('err', 'Variation Order Number cannot be empty');
        } 
        if(DB::table('tblvariation')->where('variationorderno' , '=', $variationOrderNo)->first()){
            return redirect('/computer/variation/create')->with('err', 'Variation Order Number Already in use');
        }

        if($fileNo == ""){
            return redirect('/computer/variation/create')->with('err', 'File Number cannot be empty');
        }
        if( (DB::table('tblvariation')->where('fileNo' , '=', $fileNo)->where('month' , '=', $month)->where('year', '=', $year)->first()) and ($oldGrade == $newGrade) ){
            return redirect('/computer/variation/create')->with('err', 'You have already computed variation for this staff this year');
        }

        if($remark == ""){
            return redirect('/computer/variation/create')->with('err', 'Remark cannot be empty');
        }
        if($newSalary == ""){
            return redirect('/computer/variation/create')->with('err', 'New Salary cannot be empty');
        }
        if($amount == ""){
            return redirect('/computer/variation/create')->with('err', 'Amount of Variation cannot be empty');
        }
        if($reasonForVariation == ""){
            return redirect('/computer/variation/create')->with('err', 'Reason For Variation cannot be empty');
        }
        if($effectiveFrom == ""){
            return redirect('/computer/variation/create')->with('err', 'Effective Date cannot be empty');
        }
        
        $recordSaved = DB::table('tblvariation')->insertGetId(array( 
                'fileNo'            => $fileNo,
                'grade'             => $oldGrade,
                'gradenew'          => $newGrade,
                'step'              => $oldStep,
                'stepnew'           => $newStep,
                'newsalary'         => $newSalary,
                'amount'            => $amount,
                'reason'            => $reasonForVariation,
                'effectivedate'     => $effectiveFrom,
                'authority'         => $authority,
                'remark'            => $remark,
                'rank'              => $rank,
                'variationorderno'  => $variationOrderNo,
                'endeddate'         => $endedDate,
                'laterthan'         => $laterThan,
                'year'              => date('Y'),
                'month'             => date('m'),       
                'v_created_at'      => date("Y-m-d"),
                'v_updated_at'      => date("Y-m-d")
        ));
        $get = DB::table('promotio_detail')->where('fileNo', '=', $fileNo)->first();
        DB::table('tblper')->where('fileNo', '=', $fileNo)->update(array( 
                'gradealert'                     => 0,
                'stepalert'                      => 0,
                'date_present_appointment'       => $get->effective_date,
                //'staff_status'     => 1
        ));
        DB::table('promotion_alert')->where('fileNo', '=', $fileNo)->update(array( 
                'active'            => 0,
        ));
        $logMessage = 'Variation details created';
        $message = 'Variation record was created successfully';
        
       // $this->addLog($logMessage.' Division: '. $this->division);
        return redirect('/staff/variation/report/'.$fileNo.'/'.$recordSaved)->with('msg', $message);

    }

    //generate variation report for new computed or old variation
    public function report_variation($fileNo = null,  $variationID = null)
    {
        if($variationID == null)
        {
            if((DB::table('tblvariation')->where('fileNo', $fileNo)->count()) > 0)
            {
                $data['getNewOldStaff'] = DB::table('tblvariation')
                ->join('tblper', 'tblper.fileNo', '=', 'tblvariation.fileNo')
                //->where('divisionID', $this->divisionID)
                ->where('tblvariation.fileNo', $fileNo)
                ->where('tblvariation.id', $variationID)
                ->first(); 
                return view('Variation.report', $data); 
            }else
            {
                $data['getNewOldStaff'] = "";
                return redirect('/computer/variation/create')->with('err', 'Record not computed. Pls, try again');
            }
        }else{ 
            $data['getNewOldStaff'] = DB::table('tblvariation')
            ->join('tblper', 'tblper.fileNo', '=', 'tblvariation.fileNo')
            //->where('divisionID', $this->divisionID)
            ->where('tblvariation.fileNo', '=', $fileNo)
            ->where('tblvariation.id', '=', $variationID)
            ->first();
            return view('Variation.report', $data);
        }
    }


   public function listAll()
    {
        $data['users'] = DB::table('tblvariation')
            ->join('tblper', 'tblper.fileNo', '=', 'tblvariation.fileNo')
            ->where('tblper.divisionID', $this->divisionID)
            ->orwhere('tblper.staff_status', '=', 1)
            ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
            ->orderBy('tblvariation.v_created_at', 'Desc')
            ->paginate(10);
        return view('Variation.view', $data);
    }


    public function autocomplete_STAFF(Request $request)
    {
        $query = $request->input('query');
        $search = DB::table('tblvariation')
                ->join('tblper', 'tblper.fileNo', '=', 'tblvariation.fileNo')
                ->where('tblper.divisionID', $this->divisionID)
                ->orwhere('tblper.staff_status', '=', 1)
                ->orwhere('tblper.employee_type', '<>', 'CONSOLIDATED')
                ->orwhere('tblper.surname', 'LIKE', '%'.$query.'%')
                ->orWhere('tblper.first_name', 'LIKE', '%'.$query.'%')
                ->orwhere('othernames', 'LIKE', '%'.$query.'%')
                ->orWhere('tblper.fileNo', 'LIKE','%'.$query.'%')
                ->orWhere('tblvariation.variationorderno', 'LIKE','%'.$query.'%')
                ->orderBy('tblper.grade', 'Desc')
                ->orderBy('tblper.step', 'Desc') 
                ->orderBy('tblper.appointment_date', 'Asc')
                ->take(15)
                ->get();
        $return_array = null;
        foreach($search as $s)
        {
          $return_array[]  =  ["value"=>$s->surname.' : '.$s->fileNo.' - '.$s->variationorderno, "data"=>$s->fileNo];
        }   
        return response()->json(array("suggestions"=>$return_array));
    }


    public function filter_staff(Request $request)
    {
        $query = trim($request['fileNo']); 
        if($query == null){
            return redirect('/staff/variation/view/');
        }
        $data['users'] = DB::table('tblvariation')
                ->join('tblper', 'tblper.fileNo', '=', 'tblvariation.fileNo')
                ->where('tblper.divisionID', $this->divisionID)
                ->where('tblper.staff_status', '=', 1)
                ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
                ->Where('tblper.fileNo', 'LIKE','%'.$query.'%')
                ->orderBy('tblper.grade', 'Desc')
                ->orderBy('tblper.step', 'Desc') 
                ->orderBy('tblper.appointment_date', 'Asc')
                ->paginate(10);
        return view('Variation.view', $data);
        
    }


    //promotion Variation

    public function promotionVariation()
    {   
        //(NOTE: ONLY THOSE THAT ARE DUE FOR INCREMENT, PROMOTION etc ARE TO BE POPULATED HERE)
        $data['getStaffIncrement'] = DB::table('tblper')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
            ->where('tblper.divisionID', $this->divisionID)
            ->where('tblper.step', '<>', 'tblper.stepalert')
            ->where('tblper.stepalert', '<>', '')
            ->orwhere('tblper.staff_status', '=', 9)
            ->orwhere('tblper.promotion_alert', '=', 1)
            ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
            ->orderBy('tblper.grade', 'Desc')
            ->orderBy('tblper.step', 'Desc')
            ->orderBy('tblper.appointment_date', 'Asc')
            ->get();
            $data['varR']               = '';
            $data['varNewAmount']       = '';
            $data['this_division']      = $this->division;
            $data['varOldAmount']       = '';
            $data['amountOfVariation']  = 0.0;
        $data['getAllAlert'] = $this->getAlertIncrementPromotion();
        return view('Variation.create', $data);
    }
  	
    
} //end class ProfileController