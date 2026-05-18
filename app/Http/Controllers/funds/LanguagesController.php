<?php

namespace App\Http\Controllers\funds;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;

class LanguagesController extends ParentController
{
      public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->division    = $request->session()->get('division');
        $this->divisionID  = $request->session()->get('divisionID');
                return $next($request);
        });
    }   

    public function index($fileNo = Null)
    {
        //
        if(is_null($fileNo)){
            return redirect('profile/details');
        }
        if( !(DB::table('tblper')->where('fileNo', '=', $fileNo)->first())){
            return redirect('profile/details');
        }//
        Session::put('fileNo', $fileNo);
        $data['names'] = DB::table('tblper')->where('fileNo', '=', $fileNo)->first();
        if(!(DB::table('languages')->where('fileNo', '=', $fileNo)->first())){
            //set session
            $data['langList']     = '';
            $data['languages']    = '';
            return view('languages.update', $data);
        }
        else{
            $data['languages']    = '';  
            $data['langList']     = DB::table('languages')->where('fileNo', '=', $fileNo)->get();
            //
            return view('languages.update', $data);
        }
    }


     public function view($langid = Null)
    {
        //
        $fileNo = Session::get('fileNo');
        if(is_null($langid)){
            return redirect('/update/languages/'.$fileNo);
        }
        if( !(DB::table('languages')->where('fileNo', '=', $fileNo)->where('langid', $langid)->first())){
           return redirect('/update/languages/'.$fileNo);
        }//
        $data['names'] = DB::table('tblper')->where('fileNo', '=', $fileNo)->first();
        if(!(DB::table('languages')->where('fileNo', '=', $fileNo)->first())){
            //set session
            $data['languages']    = '';
            $data['langList']     = '';
            return view('languages.update', $data);
        }
        else{
            $data['languages'] = DB::table('languages')->where('fileNo','=',$fileNo)->where('langid','=',$langid)->first();
            $data['langList']  = DB::table('languages')->where('fileNo', '=', $fileNo)->get();
            //
            return view('languages.update', $data);
        }
    }


    public function update(Request $request)
    {
        $userID = Session::get('fileNo');
        if(is_null($userID)){
           return redirect('/update/languages/'.$fileNo);
        }
         $this->validate($request, 
        [
            'lang'                  => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
            'spoken'                => 'regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
            'written'               => 'regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
            'exam_qualified'        => 'regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
            'checkedby'             => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
        ]);
        $lang                       = trim($request['lang']);
        $spoken                     = trim($request['spoken']);
        $written                    = trim($request['written']);
        $examquali                  = trim($request['exam_qualified']);
        $checkedby                  = trim($request['checkedby']);
        $date                       = date("Y-m-d");
        $langid                     = trim($request['langid']);
        $hiddenName                 = trim($request['hiddenName']);
        if($hiddenName <> ""){
            DB::table('languages')->where('langid', '=', $langid)->where('fileNo', '=', $userID)->update(array( 
                'fileNo'           => $userID, 
                'language'         => $lang,
                'spoken'           => $spoken, 
                'written'          => $written,
                'exam_qualified'   => $examquali,     
                'checkedby'        => $checkedby,
                'updated_at'       => $date,
            ));
            $this->addLog('language was updated with ID: '.$langid .' and division: ' . $this->division);
        }
        else{
           //insert if hidden Name is empty
            DB::table('languages')->insert(array( 
                'fileNo'           => $userID, 
                'language'         => $lang,
                'spoken'           => $spoken, 
                'written'          => $written,
                'exam_qualified'   => $examquali,     
                'checkedby'        => $checkedby,
                'updated_at'       => $date, 
            ));
            $this->addLog('New language was added and division: ' . $this->division);
        }
        $data['languages']     = "";
        $data['langList']      = DB::table('languages')->where('fileNo', '=', $userID)->get();
        return redirect('/update/languages/'.$userID)->with('msg', 'Operation was done successfully.');
    }

    public function destroy($fileNo,$langid)
    {
        $userID = Session::get('fileNo');
        if(is_null($userID) || is_null($langid)){
            $data['languages']    = DB::table('languages')->where('fileNo', '=', $userID)->first();
            $data['langList']      = DB::table('languages')->where('fileNo', '=', $userID)->get();
            return view('languages.update', $data);
        }
        //delete
        DB::table('languages')->where('fileNo', '=', $userID)->where('langid', '=', $langid)->delete();
        $this->addLog('One Language was deleted and division: ' . $this->division);
        if(!(DB::table('languages')->where('fileNo', '=', $userID)->first())){
            return view('main.userArea');
        }
        $data['langList']     = DB::table('languages')->where('fileNo', '=', $userID)->get();
        $data['languages']     = "";
        return view('languages.update', $data)->with('msg', 'Operation was done successfully.');

    }


    //Language Report
    public function report($fileNo = null)
    {
        if(is_null($fileNo)){
            return redirect('profile/details');
        }
        if( !(DB::table('tblper')->where('fileNo', '=', $fileNo)->first())){
            return redirect('profile/details');
        }else{
            $data['staffFullDetailsLanguage'] = DB::table('languages')
                    ->where('fileNo', '=', $fileNo)
                    ->orderBy('langid', 'Desc')
                    ->get();
            $data['staffFullDetails'] = DB::table('tblper')
                    ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                    ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                    ->where('tblper.fileNo', '=', $fileNo)
                    ->first();
        }
        return view('Report.LanguageReport', $data);
    }


}
