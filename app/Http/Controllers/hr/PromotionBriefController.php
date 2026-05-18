<?php

namespace App\Http\Controllers\hr;



use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PromotionBriefController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // $this->username = Session::get('userName');
        $this->middleware('auth');
            return $next($request);
        });
    }

    public function promotionBrief($id)
    {
        $data['promotion'] = "";
        $data['lists'] = DB::table('tblper')
            //->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->join('tbldesignation', 'tbldesignation.id', '=', 'tblper.Designation')
            ->where('tblper.ID', '=', $id)
            ->first();
        //dd( $data['list']);

        $data['primary']   = DB::table('tbleducations')->where('staffid', '=', $id)->where('categoryID', '=', 1)->get();
        $data['secondary'] = DB::table('tbleducations')->where('staffid', '=', $id)->where('categoryID', '=', 2)->get();
        $data['tertiary']  = DB::table('tbleducations')->where('staffid', '=', $id)->where('categoryID', '=', 3)->get();
        $data['postGraduate']  = DB::table('tbleducations')->where('staffid', '=', $id)->where('categoryID', '=', 4)->get();
        $data['professional']  = DB::table('tbleducations')->where('staffid', '=', $id)->where('categoryID', '=', 5)->get();
        $data['qualification']  = DB::table('tbleducations')
            ->join('tbleducation_category', 'tbleducation_category.edu_categoryID', '=', 'tbleducations.categoryID')

            ->groupBy('tbleducations.categoryID')
            ->where('staffid', '=', $id)->get();

        $data['educations'] = DB::table('tbleducations')
            ->where('staffid', '=', $id)
            ->get();

        $data['records'] = DB::table('recordof_service')
            ->where('staffid', '=', $id)
            ->get();

        $data['promotion'] = DB::table('promotion_detail')
            ->where('staffid', '=', $id)
            ->where('active', '=', 1)
            ->first();

        $data['convert'] = DB::table('conversion_advancement')
            ->where('staffid', '=', $id)
            ->where('active', '=', 1)
            ->first();
        return view('hr.Promotion.promotionBrief', $data);
    }
}
