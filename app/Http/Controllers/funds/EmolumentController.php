<?php

namespace App\Http\Controllers\funds;

use Illuminate\Http\Request;
use File;
use App\Http\Requests;
use DB;

class EmolumentController extends ParentController
{

    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->division = $request->session()->get('division');
        $this->divisionID = $request->session()->get('divisionID');
                return $next($request);
        });
    }

    public function create_emolument()
    {
        $data['getNewOldStaff'] = DB::table('tblper')
            ->where('staff_status', 9)
            ->where('divisionID', $this->divisionID)
            ->orderBy('fileNo', 'Desc')
            ->get();
        $data['getDivision'] = DB::table('tbldivision')
            ->orderBy('division', 'Asc')->get();
        $data['getBank'] = DB::table('tblbank')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblbank.bankID')
            ->where('tblbank.divisionID', $this->divisionID)
            ->orderBy('tblbanklist.bank', 'Asc')->get();
        return view('Emolument.create', $data);
    }

    //auto fill form
    public function findStaff(Request $request)
    {       
        $this->validate($request, [
            'getStaff' => 'required|numeric',
        ]);
        $fileNo        = $request->input('getStaff');
        $staffRecord   = DB::table('tblper')
                       ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                       ->where('tblper.fileNo', '=', $fileNo)
                       ->first();
        return response()->json($staffRecord);
    }


    public function update_emolument(Request $request)
    {   
        $this->validate($request, 
        [
            'fileNo'                => 'required|numeric',
            'division'              => 'required|string',
            'grade'                 => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
            'bank'                  => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
            'branch'                => 'regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
            'phoneNumber'           => 'numeric', 
            'section'               => 'required|string',
            'appointmentDate'       => 'required|date',
            'incrementalDate'       => 'date',
            'dateOfBirth'           => 'date',
            'residentialAddress'    => 'string', 
            'qurter'                => 'string',
            'leaveAddress'          => 'string',
            'accountNo'             => 'required|numeric',
        ]);
        $fileNo                     = trim($request['fileNo']);
        $division                   = trim($request['division']);
        $grade                      = trim($request['grade']);
        $bank                       = trim($request['bank']);
        $branch                     = trim($request['branch']);
        $phoneNumber                = trim($request['phoneNumber']);
        $accountNo                  = trim($request['accountNo']);
        $section                    = trim(strtoupper($request['section']));
        $appointmentDate            = trim($request['appointmentDate']);
        $incrementalDate            = trim($request['incrementalDate']); 
        $dateOfBirth                = $request['dateOfBirth'];
        $residentialAddress         = $request['residentialAddress'];
        $qurter                     = $request['qurter'];
        $leaveAddress               = $request['leaveAddress']; 
        $date                       = date("Y-m-d");
        $returnBefore               = $request['returnBefore']; 
        $failureReturn               = $request['failureReturn']; 

        if($fileNo <> ""){
            DB::table('tblper')->where('fileNo', $fileNo)->update(array( 
                'grade'             => $grade,
                'divisionID'        => $division,
                'bankID'            => $bank,  
                'bank_branch'       => $branch,
                'phone'             => $phoneNumber,
                'AccNo'             => $accountNo,
                'section'           => $section,
                'appointment_date'  => $appointmentDate,
                'incremental_date'  => $incrementalDate,
                'dob'               => $dateOfBirth,
                'home_address'      => $residentialAddress,
                'government_qtr'    => $qurter,
                'leaveaddress'      => $leaveAddress,
                'failurereturn'     => $failureReturn,
                'returnbefore'      => $returnBefore,
                //'staff_status'      =>  1,
                'updated_at'        => date("Y-m-d")
            ));
            $logMessage = 'Personal Emolument records updated';
            $message = 'Personal Emolument records updated successfully. You can print your report';
            $this->addLog($logMessage.' Division: '. $this->division);
            return redirect('/staff/personal-emolument/report/'.$fileNo)->with('msg', $message);
        }else{
            return back()->with('Staff Core details not found! Please contact your admin personel for assistance');
        }
    }


    public function report_emolument($fileNo = null)
    {
        if($fileNo <> "")
        {
            if((DB::table('tblper')->where('fileNo', $fileNo)->count()) > 0)
            {
                $data['getEmolumentReport'] = DB::table('tblper')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->where('tblper.divisionID', $this->divisionID)
                ->where('tblper.staff_status', 1)
                ->where('tblper.fileNo', $fileNo)
                ->first(); 
                return view('Emolument.report', $data); 
            }else
            {
                $data['getNewOldStaff'] = "";
                return redirect('/personal-emolument/create')->with('err', 'Personal Emolument Record not completed. Pls, try again');
            }
        }else{ 
             return redirect('/personal-emolument/create')->with('err', 'Personal Emolument Record not completed. Pls, try again');
        }
    }


   public function listAll()
    {
        $data['users'] = DB::table('tblper')
            //->where('divisionID', $this->divisionID)
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
            ->where('tblper.divisionID', $this->divisionID)
            ->where('tblper.staff_status', 1)
            ->where('tblper.employee_type', '<>', 'JUDGES')
            ->orderBy('tblper.updated_at', 'Desc')
            ->paginate(10);
        return view('Emolument.view', $data);
    }


    public function autocomplete_STAFF(Request $request)
    {
        $query = $request->input('query');
        $search = DB::table('tblper')
                ->join('tblper', 'tblper.fileNo', '=', 'tblvariation.fileNo')
                //->where('divisionID', $this->divisionID)
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->where('surname', 'LIKE', '%'.$query.'%')
                ->orWhere('first_name', 'LIKE', '%'.$query.'%')
                ->orWhere('fileNo', 'LIKE','%'.$query.'%')
                ->orderBy('tblvariation.id', 'Desc')
                ->take(6)
                ->get();
        $return_array = null;
        foreach($search as $s)
        {
          $return_array[]  =  ["value"=>$s->surname.' '.$s->first_name.' '.$s->othernames.' - '.$s->fileNo, "data"=>$s->fileNo];
        }   
        return response()->json(array("suggestions"=>$return_array));
    }


    public function filter_staff(Request $request)
    {
        $filterBy = trim($request['fileNo']); 
        if($filterBy == null){
            return redirect('/staff/personal-emolument/view/')->with('err', 'No record found !');
        }
        $data['users'] = DB::table('tblper')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->where('tblper.surname', 'LIKE', '%'.$filterBy.'%')
                ->orWhere('tblper.first_name', 'LIKE', '%'.$filterBy.'%')
                ->orWhere('tblper.fileNo', 'LIKE','%'.$filterBy.'%')
                ->where('tblper.staff_status', 1)
                ->orderBy('tblper.surname', 'Asc')
                ->paginate(20);
        return view('Emolument.view', $data);
        
    }


    public function listAllNewStaff()
    {
        $data['newStaff'] = DB::table('tblper')
            ->Join('tbldivision', 'tblper.divisionID', '=', 'tbldivision.divisionID')
            ->where('tblper.staff_status', 9)
            ->where('tblper.divisionID', $this->divisionID)
            ->orderBy('tblper.grade', 'Desc')
            ->orderBy('tblper.step', 'Desc')
            ->paginate(20);
        return view('Emolument.viewNewStaff', $data);
    }
  	
    
} //end class