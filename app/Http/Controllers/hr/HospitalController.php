<?php

namespace App\Http\Controllers\hr;



use Illuminate\Http\Request;
use App\Notifications\SentFile;
use App\Notifications\RecordAdded;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SelfServiceController;
use Illuminate\Support\Facades\Session;

class HospitalController extends ParentController
{


    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->middleware(function ($request, $next) {
            // $this->division = $request->session()->get('division');
        // $this->divisionID = $request->session()->get('divisionID');
        // Session::put('this_division', $this->division);
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


        $data['form'] = DB::table('hospitals')
            ->leftJoin('nhis_hospital_categories', 'nhis_hospital_categories.id', '=', 'hospitals.category_id')
            ->select(
                'hospitals.*',
                'nhis_hospital_categories.name as category_name'
            )
            ->get();

        $data['hospitalCats'] =  DB::table('nhis_hospital_categories')->get();

        return view('hr.Hospital.hospital', $data);
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
            'hospitalCat'  => 'required',
            'name'  => 'required',
            'email' => 'required',
            'phone' => 'required',
            'address' => 'required',

        ]);

        $name = $request->input('name');;
        $address     = $request->input('address');
        $email = $request->input('email');
        $phone = $request->input('phone');
        $code = $request->input('code');
        $hospitalCat = $request->input('hospitalCat');

        $hospital = DB::table('hospitals')->insert([
            'name' => $name,
            'email'  => $email,
            'phone'     => $phone,
            'address'     => $address,
            'code' => $code,
            'category_id' => $hospitalCat,
            'user_id' => Auth::user()->id,
            'created_at'   =>  now(),
            'updated_at'   =>  now(),
        ]);


        if ($hospital) {


            return redirect()->back()->with('success', 'hospital record was created successfully.');
        }
        return redirect()->back()->with('danger', 'could not create hospital record.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data['hospital'] = DB::table('hospitals')->where('id', $id)->first();

        return view('Hospital.hospital', $data);
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

            $data['value'] = DB::table('hospitals')->where('id', $id)->first();

            $data['hospitalCats'] =  DB::table('nhis_hospital_categories')->get();

            return view('hr.Hospital.editHospital', $data);
        } catch (\Throwable $e) {
            redirect()->back()->with('success', 'record not found!');
        }
    }
    public function assign(Request $request)
    {
        try {
            $this->validate($request, [
                'staffID'  => 'required',
                'hospitalID' => 'required',

            ]);
            $staffID = $request->input('staffID');
            $hospitalID = $request->input('hospitalID');
            $hospital = DB::table('hospitals')->where('id', $hospitalID)->first();

            if ($hospital) {


                $staffHospital = DB::table('staff_hospitals')->insert([
                    'staff_id' => $staffID,
                    'hospital_id'  => $hospitalID,
                ]);

                return redirect()->route('staff-nhis')->with('success', 'hospital assigned!');
            }
            return redirect()->back()->with('danger', 'hospital record not found.');
        } catch (\Throwable $e) {

            return redirect()->back()->with('danger', 'Could not assign hospital to staff. Please try again.');
        }
    }
    public function updatehospital(Request $request)
    {
        try {

            $this->validate($request, [
                'name'  => 'required',
                'email' => 'required',
                'phone' => 'required',
                'address' => 'required',
                'id' => 'required'


            ]);

            $name = $request->input('name');

            $address     = $request->input('address');
            $email = $request->input('email');
            $phone = $request->input('phone');
            $code = $request->input('code');
            $id = $request->input('id');
            $hospitalCat = $request->input('hospitalCat');

            $hospital = DB::table('hospitals')->where('id', $id)->update([

                'name' => $name,
                'email'  => $email,
                'phone'     => $phone,
                'address'     => $address,
                'code' => $code,
                'category_id' => $hospitalCat,
            ]);



            return redirect()->route('hospital')->with('success', 'hopital record updated!');
        } catch (\Throwable $e) {
            return redirect()->back()->with('danger', 'Could not update record. Please try again.');
        }
    }






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
    public function destroy(Request $request)
    {
        try {
            $hospital = DB::table('hospitals')->where('id', $request->hsptlId);
            if ($hospital) {

                $hospital->delete();
                return back()->with('success', 'hospital  was deleted successfully.');
            }
            return back()->with('error', 'record not found');
        } catch (\Throwable $e) {

            return back()->with('error', 'could not delete hospital.');
        } //
    }


    public function getHospitals($category_id)
    {
        $hospitals = DB::table('hospitals')
            ->where('category_id', $category_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($hospitals);
    }
}
