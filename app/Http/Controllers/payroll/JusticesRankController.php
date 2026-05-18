<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
class JusticesRankController extends Controller
{
    public function index() 
    {
        
        $listjudges = DB::table('tblper')
                    ->where('rank', '=', 2)
                    ->orderBy('judge_rank', 'ASC')
                    ->orderBy('surname', 'ASC')
                    ->orderBy('first_name', 'ASC')
                    ->orderBy('othernames', 'ASC')
                    ->leftjoin('users','users.id','=','tblper.UserID')
                    ->get();
       
            return view('judges.index', ['listjudges'=>$listjudges]); 
    }   
    
    public function store(Request $request)
    {
        $this->validate($request, [
            'judgeRank' => 'required',
            'justiceName' => 'required'
        ]);

        $isSaved = DB::table('tblper')
                    ->where('ID', $request['justiceName'])
                    ->update(['judge_rank' => $request['judgeRank']]);
        if($isSaved){
            return redirect()->back()->with('msg', 'record updated!');
        }
        return redirect()->back()->with('err', 'Could not update record, already asigned!');

    }
}
