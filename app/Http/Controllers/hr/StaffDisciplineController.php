<?php

namespace App\Http\Controllers\hr;

use Session;
use Illuminate\Http\Request;
use App\Notifications\SentFile;
use App\Notifications\RecordAdded;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\payroll\ParentController;
use App\Http\Controllers\SelfServiceController;

class StaffDisciplineController extends ParentController
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
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $staff = Auth::user()->id;

        $data['form'] = DB::table('tbl_discipline')
        ->join('tblper as offender', 'offender.ID', '=', 'tbl_discipline.offenderID')
        ->join('tblper as logger', 'logger.UserID', '=', 'tbl_discipline.loggedby')
       // ->where('logger.staff_status', 1)
       // ->where('offender.staff_status', 1)
       ->select('tbl_discipline.*','offender.ID as offenderid', 'offender.surname as offendersurname', 'offender.first_name as offenderfirstname', 'offender.othernames as offenderothername','logger.ID as loggerid', 'logger.surname as loggersurname', 'logger.first_name as loggerfirstname', 'logger.othernames as loggerothername')
        ->get();

        // $data['discipline'] = new Discipline();
// dd($data['form']);

        //$data['staff']=$this->getOneStaff($data['form']->id)->get();



       // $data['category']=$this->category;

        return view('Discipline.discipline', $data);
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */




    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'offense'  => 'required',
         'discipline' => 'required',
         'startDate'=>'required',
         'endDate'=>'required|date|after:startDate',
         'offenderId'=>'required',
        ]);

     $offenderid = $request->input('offenderId');
   //  $loggedby = $request->input('loggedby');
     $offense	 = $request->input('offense');
     $discipline = $request->input('discipline');
     $startdate = $request->input('startDate');
     $enddate = $request->input('endDate');

     $dicipline = DB::table('tbl_discipline')->insert([
        'offenderid' => $offenderid,
        'offense'  => $offense,
        'discipline'     => $discipline,
        'startdate'     => $startdate,
        'enddate'=> $enddate,
        'loggedby' => Auth::user()->id,

    ]);
        // return redirect('staffContact/create')->with('msg', 'discipline has been updated');

        if($dicipline){
    // Auth::user()->notify(new RecordAdded($discipline,"/discipline","new Discipline added"));

    //         $admins = Admin::all();
    //   foreach ($admins as $admin) {
    // $admin->notify(new SentFile($discipline,"a Discipline"));
    // }

            return redirect()->back()->with('success', 'discipline record was created successfully.');
        }
        return redirect()->back()->with('danger', 'Sorry, we cannot process your record. Please try again.');
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
        $data = [];
        try {

            $data['value'] = DB::table('tbl_discipline')
            ->join('tblper as offender', 'offender.ID', '=', 'tbl_discipline.offenderid')
            ->join('tblper as logger', 'logger.UserID', '=', 'tbl_discipline.loggedby')
            // ->where('logger.staff_status', 1)
            // ->where('offender.staff_status', 1)
           ->where('tbl_discipline.id', $id)
           ->select('tbl_discipline.*','offender.ID as offenderid', 'offender.fileNo as fileNo', 'offender.surname as offendersurname', 'offender.first_name as offenderfirstname', 'offender.othernames as offenderothername','logger.ID as loggerid', 'logger.surname as loggersurname', 'logger.first_name as loggerfirstname', 'logger.othernames as loggerothername')
            ->first();

         //   $data['value'] =  DB::table('tbl_discipline')->where('id',$id)->first();
           // dd($data['value']);
        } catch (\Throwable $e) {
            redirect()->back()->with('success', 'record not found!');
        }
        return view('Discipline/editDiscipline', $data);
    }

    public function updatediscipline(Request $request)
    {
        try{
        $this->validate($request, [
         'offense'  => 'required',
         'discipline' => 'required',
         'startdate'=>'required',
         'enddate'=>'required|date|after:startdate',
         'offenderid'=>'required',
         'id'=>'required'
        ]);

     $offenderid = $request->input('offenderid');
     $id = $request->input('id');
     $offense	 = $request->input('offense');
     $discipline = $request->input('discipline');
     $startdate = $request->input('startdate');
     $enddate = $request->input('enddate');

     $dicipline = DB::table('tbl_discipline')->where('id', $id)->update([
       // 'offenderid' => $offenderid,
        'offense'  => $offense,
        'discipline'     => $discipline,
        'startdate'     => $startdate,
        'enddate'=> $enddate,
        'loggedby'  => Auth::user()->id,

    ]);
        // return redirect('staffContact/create')->with('msg', 'discipline has been updated');


            return redirect()->route('discipline')->with('success', 'record updated!');

  }catch(\Throwable $e){
        return redirect()->back()->with('danger', 'Sorry, we cannot process your record. Please try again.');
  }
    }


    // public function selectSearch(Request $request)
    // {
    // 	$movies = [];
    //     if($request->has('q')){
    //         $search = $request->q;
    //         $movies =Movie::select("id", "name")
    //         		->where('name', 'LIKE', "%$search%")
    //         		->get();
    //     }
    //     return response()->json($movies);
    // }

    public function autocomplete(Request $request)
    {
        $return_array = [];
        if($request->has('q')){
        $query = $request->input('q');

        $search = DB::table('tblper')
                ->where('staff_status', 1)
                ->where('divisionID', $this->divisionID)
                 ->where('first_name', 'like', "%$query%")
	             ->orWhere('surname', 'like', "%$query%")
	             ->orWhere('fileNo', 'like', "%$query%")
                ->take(50)
                ->orderby('ID','desc')
                ->get();

        //$search_result=preg_match("/".$query."/", $search);

        // $return_array = null;
        foreach($search as $s)
        {
          $return_array[]  =  ["value"=>$s->surname.' '.$s->first_name.' '.$s->othernames, "data"=>$s->ID, "fileNo"=>$s->fileNo];
        }
    }
        return response()->json($return_array);
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
    public function destroy(Request $request,$id)
    {
        // $id= $request->id;
        try{
       $discipline= DB::table('tbl_discipline')->where('id', $id);
       if($discipline){

       $discipline->delete();
       return back()->with('success', 'record was deleted successfully.');
       }
       return back()->with('error', 'record not found');
    }
    catch(\Throwable $e){

        return back()->with('error', 'could not delete record.');
    }//
    }
}

