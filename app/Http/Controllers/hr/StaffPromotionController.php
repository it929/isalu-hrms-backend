<?php

namespace App\Http\Controllers\hr;



use Illuminate\Http\Request;
//
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Response;

class StaffPromotionController extends functionController
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->middleware('auth');
        $this->username = Session::get('userName');
            return $next($request);
        });
    }

    public function create()
    {

        $exists = DB::table('tblper')->where('UserID', Auth::user()->id)->exists();
        $departmentID = null;
        if ($exists) {
            $departmentID = DB::table('tblper')->where('UserID', Auth::user()->id)->value('department');
        }

        $data['department'] = DB::table('tbldepartment')->get();
        $data['designation'] = DB::table('tbldesignation')->where('departmentID', $departmentID)->get();

        // dd( $departmentID);

        $data['staffDetails'] = DB::table('tblpromotion')
            ->leftjoin('tbldepartment', 'tblpromotion.department', '=', 'tbldepartment.id')
            ->leftjoin('tbldesignation', 'tblpromotion.position', '=', 'tbldesignation.id')
            ->get();

        return view('hr.Promotion.create', $data);
    }


    public function savePromotion(Request $request)
    {
        $exists = DB::table('tblper')->where('UserID', Auth::user()->id)->exists();
        if ($exists) {
            $departmentID = DB::table('tblper')->where('UserID', Auth::user()->id)->value('department');
        }

        $this->validate($request, [
            'designation' => 'required',
        ]);

        foreach ($request->designation as $position) {

            $exists = DB::table('tblpromotion')->where('position', $position)->exists();
            if ($exists) {
                //return redirect()->back()->with('error','Record exists!');
            } else {
                DB::table('tblpromotion')->insert([
                    'department'     =>  $departmentID,
                    'position'       =>  $position,
                    'user'           =>  Auth::user()->id,
                    'stage_movement' =>  3,
                ]);
            }
        }

        return redirect()->back()->with('message', 'Record added!');
    }

    public function delete(Request $request)
    {
        $id = $request->promotionId;

        DB::table('tblpromotion')->where('promotionID', $id)->delete();

        return redirect()->back()->with('message', 'Record deleted!');
    }
}
