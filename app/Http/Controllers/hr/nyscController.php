<?php

namespace App\Http\Controllers\hr;

use DB;
//use Auth;
use Auth;
use Carbon;
use Session;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Notifications\SentFile;
use App\Notifications\RecordAdded;

class nyscController extends functionController
{

    private   $category = ['Nysc', 'IT'];
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->middleware('auth');
        $this->username = Session::get('userName');
            return $next($request);
        });
    }


    public function getNotification(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $staffid = $this->StaffID($this->username);
        $data['Notification'] = $this->SelfNotification($staffid);
        return view('Leave.notification', $data);
    }

    public function index(Request $request)
    {
        $data['category'] = $this->category;

        if ((isset($request['search_year']) && $request['search_year'] == "") && (isset($request['category']) && $request['category'] == "")) {
            $data['form'] = DB::table('tbl_nysc')
                ->orderBy('created_at', 'desc')->get();
            return view('Nysc.nysctable', $data);
        }

        if ((isset($request['search_year']) && $request['search_year'] == "")) {
            $request = $request->except('search_year');
            $data['form'] = DB::table('tbl_nysc')->Where('category', $request['category'])
                ->orderBy('created_at', 'desc')
                ->get();

            return view('Nysc.nysctable', $data);
        }
        if ((isset($request['category']) && $request['category'] == "")) {
            $data['form'] = DB::table('tbl_nysc')->whereYear('startdate', $request['search_year'])
                ->orderBy('created_at', 'desc')
                ->get();

            return view('Nysc.nysctable', $data);
        }


        if ((isset($request['search_year'])) && (isset($request['category']))) {
            $data['form'] = DB::table('tbl_nysc')->whereYear('startdate', $request['search_year'])->Where('category', $request['category'])
                ->orderBy('created_at', 'desc')
                ->get();

            return view('Nysc.nysctable', $data);
        }


        $data['form'] = DB::table('tbl_nysc')
            ->orderBy('created_at', 'desc')->get();

        return view('Nysc.nysc', $data);
    }

    public function save(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email'    => 'required',
            'phone' => 'required',
            'course'    => 'required|string',
            'statecode' => 'nullable|string',
            'startdate'    => 'required|date',
            'category' => 'required',
            'pop'    => 'required|date|after:startdate',
        ]);


        try {

            $firstname = $request->input('firstname');
            $lastname  = $request->input('lastname');
            $email     = $request->input('email');
            $phone     = $request->input('phone');
            $course    = $request->input('course');
            $category    = $request->input('category');
            $statecode = $category == "IT" ? '' : $request->input('statecode');
            $startdate = $request->input('startdate');
            $pop       = $request->input('pop');
            //dd($request->all());

            $corper = DB::table('tbl_nysc')->insert([
                'firstname' => $firstname,
                'lastname'  => $lastname,
                'email'     => $email,
                'phone'     => $phone,
                'course'    => $course,
                'category'  => $category,
                'statecode' => $statecode,
                'startdate' => $startdate,
                'pop'       => $pop,

            ]);


            if ($corper) {
                return back()->with('success', 'Saved, record was created successfull.');
            }
        } catch (\Throwable $e) {
            // dd($e);
            return back()->with('danger', 'Sorry, we cannot process your record. Please try again.');

        }
    }
    public function editNysc($id)
    {
        $data = [];
        try {
            $data['value'] =  DB::table('tbl_nysc')->where('id', $id)->first();
            $data['category'] = $this->category;
        } catch (\Throwable $e) {
        }
        return view('Nysc/editNysc', $data);
    }

    public function updatenysc(Request $request)
    {
        $validated = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email'    => 'required',
            'phone' => 'required',
            'course'    => 'required',
            'category' => 'required',
            'statecode' => 'nullable',
            'startdate'    => 'required|date',
            'pop'    => 'required|date|after:startdate',
            'recordid' => 'required',

        ]);

        $recordID = $request->input('recordid');
        $firstname = $request->input('firstname');
        $lastname  = $request->input('lastname');
        $email     = $request->input('email');
        $phone     = $request->input('phone');
        $course    = $request->input('course');
        $category    = $request->input('category');
        $statecode = $category == "IT" ? '' : $request->input('statecode');
        $start_date = $request->input('startdate');
        $pop       = $request->input('pop');

        // dd($request->all());

        $saved =  DB::table('tbl_nysc')->where('id', $recordID)->update([
            'firstname' => $firstname,
            'lastname'  => $lastname,
            'email'     => $email,
            'phone'     => $phone,
            'course'    => $course,
            'category'    => $category,
            'statecode' => $statecode,
            'startdate' => $start_date,
            'pop'       => $pop,

        ]);
        return redirect()->route('viewNysc')->with('success', 'record updated!');
    }

    public function remove($id)
    {
        $nyscId = DB::table('tbl_nysc')->where('id', $id)->delete();
        if ($nyscId) {
            return back()->with('success', 'record was removed');
        }
    }

  
}
