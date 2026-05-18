<?php

namespace App\Http\Controllers\hr;

use Session;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class staffNhisController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // $this->middleware('auth');
        // $this->username = Session::get('userName');
            return $next($request);
        });
    }

    public function index(Request $request)
    {

        // $data['children'] = DB::table('tblchildren_particulars')
        //     ->selectRaw('staffid, CAST(count(*) AS UNSIGNED) as childrenCount')
        //     ->groupBy('staffid')->get();

        $children = DB::table('tblchildren_particulars')
            ->select('staffid', DB::raw('COUNT(*) as childrenCount'))
            ->groupBy('staffid')
            ->get()
            ->pluck('childrenCount', 'staffid'); // key = staffid, value = childrenCount

        $data['children'] = $children;

        // Log::info($data['children']);

        $data['hospitalCats'] =  DB::table('nhis_hospital_categories')->get();
        $data['hospitals'] =  DB::table('hospitals')->get();

        $query = $request['name'];

        if ($query) {

            $data['allStaffNhis'] = DB::table('tblper')
                ->leftJoin('tbldesignation', 'tbldesignation.id', '=', 'tblper.Designation')
                ->leftJoin('staff_hospitals', 'staff_hospitals.staff_id', '=', 'tblper.ID')
                ->leftJoin('hospitals', 'staff_hospitals.hospital_id', '=', 'hospitals.id')
                ->leftJoin('nhis_hospital_categories', 'nhis_hospital_categories.id', '=', 'hospitals.category_id')
                ->select(
                    'tblper.*',
                    'tbldesignation.courtID as designationCourtID',
                    'tbldesignation.departmentID as designationDepartmentID',
                    'tbldesignation.designation as designation',
                    'tbldesignation.grade as grade_level',
                    'hospitals.user_id as userId',
                    'hospitals.name as hospital',
                    'hospitals.address as hospital_address',
                    'hospitals.code as hospital_code',
                    'hospitals.email as hospital_email',
                    'hospitals.phone as hospital_phone',
                    'hospitals.category_id as category_id',
                    'nhis_hospital_categories.name as category_name',
                    'staff_hospitals.staff_id as staffId',
                    'staff_hospitals.hospital_id as hospitalId',
                )
                ->where('tblper.staff_status', 1)
                ->where(function ($q) use ($query) {
                    $q->where('tblper.first_name', 'like', "%$query%")
                        ->orWhere('tblper.surname', 'like', "%$query%");
                })
                ->paginate(20);
            return view('hr.nhis.nhisStaffTable', $data);
        } else {
            $data['allStaffNhis'] = DB::table('tblper')
                ->leftJoin('tbldesignation', 'tbldesignation.id', '=', 'tblper.Designation')
                ->leftJoin('staff_hospitals', 'staff_hospitals.staff_id', '=', 'tblper.ID')
                ->leftJoin('hospitals', 'staff_hospitals.hospital_id', '=', 'hospitals.id')
                ->leftJoin('nhis_hospital_categories', 'nhis_hospital_categories.id', '=', 'hospitals.category_id')
                ->select(
                    'tblper.*',

                    'tbldesignation.courtID as designationCourtID',
                    'tbldesignation.departmentID as designationDepartmentID',
                    'tbldesignation.designation as designation',
                    'tbldesignation.grade as grade_level',

                    'hospitals.user_id as userId',
                    'hospitals.name as hospital',
                    'hospitals.address as hospital_address',
                    'hospitals.code as hospital_code',
                    'hospitals.email as hospital_email',
                    'hospitals.phone as hospital_phone',
                    'hospitals.category_id as category_id',
                    'nhis_hospital_categories.name as category_name',

                    'staff_hospitals.staff_id as staffId',
                    'staff_hospitals.hospital_id as hospitalId',
                )
                ->where('tblper.staff_status', 1)
                ->paginate(20);
        }

        return view('hr.nhis.viewStaff', $data);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function child($id)
    {
        // Log::info($id);
        $data['spouse']     = DB::table('tbldateofbirth_wife')->where('staffid', $id)->get();

        $data['staffname']  = DB::table('tblper')->where('id', '=', $id)->first();

        $data['staffChild'] = DB::table('tblchildren_particulars')
            ->leftjoin('tblper', 'tblper.ID', '=', 'tblchildren_particulars.staffid')
            ->where('staffid', '=', $id)
            ->select('*', 'tblchildren_particulars.gender as childGender')
            ->get();
        return view('hr.nhis.viewstaffChild', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addchild(Request $request)
    {
        $this->validate($request, [
            'fullname'      => 'required|string',
            'gender'        => 'required',
            'dob'           => 'required',

        ]);

        $recordID = $request->input('recordID');
        $fullname = $request->input('fullname');
        $gender   = $request->input('gender');
        $dob      = $request->input('dob');
        $fileno   = $request->input('fileno');
        $save = DB::table('tblchildren_particulars')->insert([
            'staffid'       =>  $recordID,
            'fileNo'        =>  $fileno,
            'fullname'      =>  $fullname,
            'gender'        =>  $gender,
            'dateofbirth'   =>  $dob,
            'created_at'   =>  now(),
            'updated_at'   =>  now(),
        ]);
        //dd($save);
        if ($save) {
            return back()->with('success', 'Hurray! new child has been added to the family!');
        } else {
            return back()->with('error', 'Oops! something occured, please try again later!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteChild($id)
    {
        $save = DB::table('tblchildren_particulars')->where('id', $id)->delete();
        if ($save) {
            return back()->with('success', 'Child record successfully removed!');
        } else {
            return back()->with('error', 'Oops! something occured, please try again later!');
        }
    }
}
