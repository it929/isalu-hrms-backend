<?php

namespace App\Http\Controllers\funds;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;

class OfferOfAppointmenController extends ParentController
{
     public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->division    = $request->session()->get('division');
        $this->divisionID  = $request->session()->get('divisionID');
                return $next($request);
        });
    } 
    public function indexoffer()
    {
        $data['perList'] = DB::table('tblper')->where('staff_status', '=', 9)->get();
        return view('offerofappointment.createoffer', $data);
    }
    public function indexletter()
    {
        $data['perList'] = DB::table('tblper')->where('staff_status', '=', 9)->get();
        return view('offerofappointment.createletter', $data);
    }
    public function indexaccept()
    {
         $data['perList'] = DB::table('tblper')->where('staff_status', '=', 9)->get();
        return view('offerofappointment.acceptance',$data);
    }
    public function indexmedical()
    {
        $data['perList'] = DB::table('tblper')->where('staff_status', '=', 9)->get();
   
        return view('offerofappointment.medicalexam',$data);
    }
     public function getfileno(request $request)
    {
         $id = $request['fileno'];
         $data =DB::table('offerofappointment')->where('fileNo', '=', $id)->first();
         if (is_null($data))
         {
             $data1 =DB::table('tblper')->where('fileNo', '=', $id)->first();
             return response()->json($data1);
         }
         else
         {
            return response()->json($data);
         }
         
    }
    public function getbearer(request $request)
    {
         $id = $request['fileno'];
         $data =DB::table('tblper')->where('fileNo', '=', $id)->get();
         
            return response()->json($data);
        
         
    }
     public function letterfileno(request $request)
    {
         $id = $request['fileno'];
         $data =DB::table('letterofappointment')->where('fileNo', '=', $id)->first();
         if (is_null($data))
         {
             $data1 =DB::table('tblper')->where('fileNo', '=', $id)->first();
             return response()->json($data1);
         }
         else
         {
            return response()->json($data);
         }
         
    }

       public function medicalexam(request $request)
    {
         $id = $request['fileno'];
         $data =DB::table('medicalexam')->where('fileNo', '=', $id)->first();
         if (is_null($data))
         {
             $data1 =DB::table('tblper')->where('fileNo', '=', $id)->first();
             return response()->json($data1);
         }
         else
         {
            return response()->json($data);
         }
         
    }
       public function acceptance(request $request)
    {
         $id = $request['fileno'];
         $data =DB::table('acceptance')->where('fileNo', '=', $id)->first();
         if (is_null($data))
         {
             $data1 =DB::table('tblper')->where('fileNo', '=', $id)->first();
             return response()->json($data1);
         }
         else
         {
            return response()->json($data);
         }
         
    }
    public function storeOffer(request $request)
    {
        $add = $request['add'];
        $up = $request['update'];
        $print = $request['print'];
        $this->validate($request, 
        [
            'fileno'          => 'required',
            'salary'      => 'required',
            'address'  => 'required|string',
            'offerdate'  => 'required|date',
            'position'  =>  'required|string',
            'returndate' => 'required|date',
           'date' => 'required|date',
           'for_registrar' => 'required|string',
            'medofficer' => 'required|string',
                      
        ]);
        $position              = trim($request['position']);
        $salary         = trim($request['salary']);
        $fileno          = trim($request['fileno']);
        $address         = trim($request['address']);
        
        $offerdate          = date('Y-m-d', strtotime(trim($request['offerdate'])));
        $returndate          = date('Y-m-d', strtotime(trim($request['returndate'])));
        $date          = date('Y-m-d', strtotime(trim($request['date'])));
        $medofficer              = trim($request['medofficer']);
        $for_registrar = trim($request['for_registrar']);
         $created          = date('Y-m-d');
         $datas =DB::table('offerofappointment')->where('fileNo', '=', $fileno)->count();
         if($add == "Add")
         {
         if($datas > 0)
         {
            return redirect('/offerofappointment/createoffer')->with('err','Record Already Exist');
         }
         else
         {
             DB::table('offerofappointment')->insert(array( 
                 'fileNo'           => $fileno, 
                  'employee_address'          => $address ,
            'salary'      => $salary, 
            'dateofappointment'  => $offerdate,
            'dateissued'  => $date,
            'position'  =>  $position,
            'returndate' => $returndate,
            'medicalofficer' => $medofficer,
            'for_registrar'      => $for_registrar,
            'created_at'       => $created,
                
            ));
            $this->addLog('New offer of employment was added and division: ' . $this->division);
            return redirect('/offerofappointment/createoffer')->with('msg','Operation done Successfully');
     
         }
     }
     if($up =="Update")
     {
        DB::table('offerofappointment')->where('fileNo','=',$fileno)->update(array( 
         'fileNo'           => $fileno, 
          'employee_address'          => $address ,
            'salary'      => $salary, 
            'dateofappointment'  => $offerdate,
            'dateissued'  => $date,
            'position'  =>  $position,
            'for_registrar'      => $for_registrar,
            'returndate' => $returndate,
            'medicalofficer' => $medofficer,
            
            'updated_at'       => $created,
                
            ));
            $this->addLog('offer of employment was updated and division: ' . $this->division);
            return redirect('/offerofappointment/createoffer')->with('msg','Operation done Successfully');
     
     }
       
       if($print == "Print")
       {
        $data['offer']= DB::table('offerofappointment')->where('fileNo','=',$fileno)->get();
        //dd($list);
        return view('offerofappointment/report', $data);

       }
    }

    public function storemedicalexam(request $request)
    {
        $add = $request['add'];
        $up = $request['update'];
        $print = $request['print'];
        $this->validate($request, 
        [
            'fileno'          => 'required',
            'hospitalname'      => 'required|string',
            'bearername'  => 'required|string',
            'medofficername'  =>  'required|string',
            'signname' => 'required|string',
           'dateissued' => 'required|date',

        ]);
        $hospital              = trim($request['hospitalname']);
        $bearername         = trim($request['bearername']);
        $fileno          = trim($request['fileno']);
        $medofficername         = trim($request['medofficername']);
        $dateissued          = date('Y-m-d', strtotime(trim($request['dateissued'])));
        
        $signname              = trim($request['signname']);
        $created          = date('Y-m-d');
         $datas =DB::table('medicalexam')->where('fileNo', '=', $fileno)->count();
         if($add == "Add")
         {
         if($datas > 0)
         {
            return redirect('/offerofappointment/medicalexam')->with('err','Record Already Exist');
         }
         else
         {
             DB::table('medicalexam')->insert(array( 
                 'fileNo'           => $fileno, 
                  'signaturename'          => $signname,
            'hospital'      => $hospital, 
            'medofficername'  => $medofficername,
            'dateissued'  => $dateissued,
            'bearername'  =>  $bearername,
           
            
            'created_at'       => $created,
                
            ));
            $this->addLog('New medicalexam was added and division: ' . $this->division);
            return redirect('/offerofappointment/medicalexam')->with('msg','Operation done Successfully');
     
         }
     }
     if($up =="Update")
     {
        DB::table('medicalexam')->where('fileNo','=',$fileno)->update(array( 
            'fileNo'           => $fileno, 
            'signaturename'    => $signname,
            'hospital'         => $hospital, 
            'medofficername'   => $medofficername,
            'dateissued'       => $dateissued,
            'bearername'       =>  $bearername,
           
            
            'updated_at'       => $created,
            ));
            $this->addLog('Medical Exam was updated and division: ' . $this->division);
            return redirect('/offerofappointment/createletter')->with('msg','Operation done Successfully');
     
     }
       
       if($print == "Print")
       {
        $data['medical']= DB::table('medicalexam')->where('fileNo','=',$fileno)->get();
        //dd($list);
        return view('offerofappointment/medicalexamreport', $data);

       }
    }

    public function storeletter(request $request)
    {
        $add = $request['add'];
        $up = $request['update'];
        $print = $request['print'];
       
        
        $this->validate($request, 
        [
            'fileno'          => 'required',
            'offerdate'      => 'required|date',
            'address'  => 'required|string',
            'effectdate'  => 'required|date',
            'position'  =>  'required|string',
            'acceptdate' => 'required|date',
           'dateissued' => 'required|date',
            'emolument' => 'required',
                      
        ]);
       
        $position              = trim($request['position']);
        $refdate         = date('Y-m-d', strtotime(trim($request['offerdate'])));
        $fileno          = trim($request['fileno']);
        $address         = trim($request['address']);
        
        $acceptdate          = date('Y-m-d', strtotime(trim($request['acceptdate'])));
        $dateissued          = date('Y-m-d', strtotime(trim($request['dateissued'])));
        $effectdate          = date('Y-m-d', strtotime(trim($request['effectdate'])));
        $emolument              = trim($request['emolument']);
        $created          = date('Y-m-d');
         $datas =DB::table('letterofappointment')->where('fileNo', '=', $fileno)->count();
         if($add == "Add")
         {
         if($datas > 0)
         {
            return redirect('/offerofappointment/createletter')->with('err','Record Already Exist');
         }
         else
         {
             DB::table('letterofappointment')->insert(array( 
                 'fileNo'           => $fileno, 
                  'address'          => $address ,
            'refdate'      => $refdate, 
            'effectdate'  => $effectdate,
            'dateissued'  => $dateissued,
            'position'  =>  $position,
            'acceptdate' => $acceptdate,
            'emolument' => $emolument,
            
            'created_at'       => $created,
                
            ));
            $this->addLog('New Letter of Appointment was added and division: ' . $this->division);
            return redirect('/offerofappointment/createletter')->with('msg','Operation done Successfully');
     
         }
     }
     if($up =="Update")
     {
        DB::table('letterofappointment')->where('fileNo','=',$fileno)->update(array( 
           'fileNo'           => $fileno, 
            'address'          => $address ,
            'refdate'      => $refdate, 
            'effectdate'  => $effectdate,
            'dateissued'  => $dateissued,
            'position'  =>  $position,
            'acceptdate' => $acceptdate,
            'emolument' => $emolument,
            
            'created_at'       => $created,
            ));
            $this->addLog('Letter of Appointment was updated and division: ' . $this->division);
            return redirect('/offerofappointment/createletter')->with('msg','Operation done Successfully');
     
     }
       
       if($print == "Print")
       {
        $data['letter']= DB::table('letterofappointment')->where('fileNo','=',$fileno)->get();
        //dd($list);
        return view('offerofappointment/reportletter', $data);

       }
    }


    public function storeacceptance(request $request)
    {
        $add = $request['add'];
        $up = $request['update'];
        $print = $request['print'];
        $this->validate($request, 
        [
            'fileno'          => 'required',
            'dateaccept'      => 'required',
            'address'  => 'required|string',
            'datecertify'  => 'required|date',
            'bearer'  =>  'required|string',
            'witness'  =>'required|string',
            'witness2' => 'required|string',
            'rank1' => 'required|string',
            'rank1' => 'required|string',
           
            'medofficer' => 'required|string',
                      
        ]);
        $dateaccept              = date('Y-m-d', strtotime(trim($request['dateaccept'])));
        $datecertify         = date('Y-m-d', strtotime(trim($request['datecertify'])));
        $fileno          = trim($request['fileno']);
        $address         = trim($request['address']);
        
        $certifier          = trim($request['certifier']);
        $date          = date('Y-m-d', strtotime(trim($request['date'])));
        $medofficer              = trim($request['medofficer']);
        $witness = trim($request['witness']);
        $witness2 = trim($request['witness2']);
        $rank1 = trim($request['rank1']);
        $rank2 = trim($request['rank2']); 
        $bearer = trim($request['bearer']);
        $position = trim($request['position']);
         $created          = date('Y-m-d');
         $datas =DB::table('acceptance')->where('fileNo', '=', $fileno)->count();
         if($add == "Add")
         {
         if($datas > 0)
         {
            return redirect('/offerofappointment/acceptance')->with('err','Record Already Exist');
         }
         else
         {
             DB::table('acceptance')->insert(array( 
            'fileNo'           => $fileno, 
            'address'     => $address ,
            'dateaccepted'      => $dateaccept, 
            'bearer'  => $bearer,
            'datecertified'  => $datecertify,
            'certifier'  =>  $certifier,
            'witness1' => $witness,
            'medicalofficer' => $medofficer,
            'witness2' => $witness2,
            'rank1' => $rank1,
            'position' => $position,
            'created_at'       => $created,
            'rank2'  =>  $rank2,
                            
            ));
            $this->addLog('New offer of employment was added and division: ' . $this->division);
            return redirect('/offerofappointment/acceptance')->with('msg','Operation done Successfully');
     
         }
     }
     if($up =="Update")
     {
        DB::table('acceptance')->where('fileNo','=',$fileno)->update(array( 
            'fileNo'           => $fileno, 
            'address'     => $address ,
            'dateaccepted'      => $dateaccept, 
            'bearer'  => $bearer,
            'datecertified'  => $datecertify,
            'certifier'  =>  $certifier,
            'witness1' => $witness,
            'medicalofficer' => $medofficer,
            'witness2' => $witness2,
            'position' => $position,
            'rank1' => $rank1,
            'updated_at'       => $created,
            'rank2'  =>  $rank2,
            ));
            $this->addLog('offer of employment was updated and division: ' . $this->division);
            return redirect('/offerofappointment/createoffer')->with('msg','Operation done Successfully');
     
     }
       
       if($print == "Print")
       {
        $data['accept']= DB::table('acceptance')->where('fileNo','=',$fileno)->get();
        //dd($list);
        return view('offerofappointment/acceptancereport', $data);

       }
    }




    public function listletterprint(Request $request)
    {
        $fileno          = trim($request['fileno']);
         $print = $request['print'];
        if($print == "Print")
       {
        $data['letter']= DB::table('letterofappointment')->where('fileNo','=',$fileno)->get();
        //dd($list);
        return view('offerofappointment/reportletter', $data);

       }
        
    }
    
    public function listacceptanceprint(Request $request)
    {
        $fileno          = trim($request['fileno']);
         $print = $request['print'];
       
        $data['accept']= DB::table('acceptance')->where('fileNo','=',$fileno)->get();
        //dd($list);
        return view('offerofappointment/acceptancereport', $data);

      
        
    }
    
    public function listmedicalprint(Request $request)
    {
        $fileno          = trim($request['fileno']);
         $print = $request['print'];
        if($print == "Print")
       {
        $data['medical']= DB::table('medicalexam')->where('fileNo','=',$fileno)->get();
        
        return view('offerofappointment/medicalexamreport', $data);

       }
        
    }
    public function listofferprint(Request $request)
    {
        $fileno         = trim($request['fileno']);
         $print = $request['print'];
        if($print == "Print")
       {
        $data['offer']= DB::table('offerofappointment')->where('fileNo','=',$fileno)->get();
        //dd($list);
        return view('offerofappointment/report', $data);

       }
        
    }

   
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
