<?php

namespace App\Http\Controllers\hr;

use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;
use App\Models\LanguageDetail;
use App\Models\FluencyDetail;

class LanguagesController extends ParentController
{
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        // $this->division    = $request->session()->get('division');
        // $this->divisionID  = $request->session()->get('divisionID');
                return $next($request);
        });
    }

    public function indexOLD($staffid = Null)
    {

        //
        if (is_null($staffid)) {
            return redirect('profile/details');
        }
        if (!(DB::table('tblper')->where('ID', '=', $staffid)->first())) {
            return redirect('profile/details');
        } //
        Session::put('staffid', $staffid);
        $data['names'] = DB::table('tblper')->where('ID', '=', $staffid)->first();
        if (!(DB::table('languages')->where('staffid', '=', $staffid)->first())) {
            //set session

            $data['langList']     = '';
            $data['languages']    = '';

            return view('hr.languages.update', $data);
        } else {

            $data['languages']    = '';
            $data['langList']     = DB::table('languages')
                ->leftJoin('language_details', 'language_details.languageID', '=', 'languages.language')
                ->leftJoin('fluency_details as spoken', 'spoken.fluencyID', '=', 'languages.spoken')
                ->leftJoin('fluency_details as written', 'languages.written', '=', 'written.fluencyID')
                ->where('staffid', '=', $staffid)->select('langid', 'languages.staffid', 'languages.language', 'languages.written', 'language_name', 'languages.exam_qualified', 'languages.checkedby', 'spoken.fluency_title as spoken_title', 'written.fluency_title as written_title')
                ->orderBy('languages.langid', 'asc')
                ->get();
            $data['allLangData']    =   LanguageDetail::get();
            $data['allFluencyData']    =   FluencyDetail::get();

            //
            return view('hr.languages.update', $data);
        }
    }

    public function indexOLD2($staffid = Null)
    {
        if (is_null($staffid)) {
            return redirect('profile/details');
        }

        if (!(DB::table('tblper')->where('ID', '=', $staffid)->first())) {
            return redirect('profile/details');
        }

        Session::put('staffid', $staffid);
        $data['names'] = DB::table('tblper')->where('ID', '=', $staffid)->first();

        if (!(DB::table('languages')->where('staffid', '=', $staffid)->first())) {
            // No existing record
            $data['langList']        = '';
            $data['languages']       = '';
            $data['allLangData']     = LanguageDetail::get();
            $data['allFluencyData']  = FluencyDetail::get();

            return view('hr.languages.update', $data);
        } else {
            // Existing record
            $data['languages']    = '';
            $data['langList']     = DB::table('languages')
                ->leftJoin('language_details', 'language_details.languageID', '=', 'languages.language')
                ->leftJoin('fluency_details as spoken', 'spoken.fluencyID', '=', 'languages.spoken')
                ->leftJoin('fluency_details as written', 'languages.written', '=', 'written.fluencyID')
                ->where('staffid', '=', $staffid)
                ->select('langid', 'languages.staffid', 'languages.language', 'languages.written', 'language_name', 'languages.exam_qualified', 'languages.checkedby', 'spoken.fluency_title as spoken_title', 'written.fluency_title as written_title')
                ->orderBy('languages.langid', 'asc')
                ->get();

            $data['allLangData']     = LanguageDetail::get();
            $data['allFluencyData']  = FluencyDetail::get();

            return view('hr.languages.update', $data);
        }
    }

    public function index($staffid = Null)
    {
        if (is_null($staffid)) {
            return redirect('profile/details');
        }

        if (!(DB::table('tblper')->where('ID', '=', $staffid)->first())) {
            return redirect('profile/details');
        }

        Session::put('staffid', $staffid);
        $data['names'] = DB::table('tblper')->where('ID', '=', $staffid)->first();

        if (!(DB::table('languages')->where('staffid', '=', $staffid)->first())) {
            // No existing record
            $data['langList']        = '';
            $data['languages']       = '';
            $data['allLangData']     = DB::table('language_details')->get();
            $data['allFluencyData']  = DB::table('fluency_details')->get();

            return view('hr.languages.update', $data);
        } else {
            // Existing record
            $data['languages']    = '';
            $data['langList']     = DB::table('languages')
                ->leftJoin('language_details', 'language_details.languageID', '=', 'languages.language')
                ->leftJoin('fluency_details as spoken', 'spoken.fluencyID', '=', 'languages.spoken')
                ->leftJoin('fluency_details as written', 'languages.written', '=', 'written.fluencyID')
                ->where('staffid', '=', $staffid)
                ->select(
                    'langid',
                    'languages.staffid',
                    'languages.language',
                    'languages.written',
                    'language_name',
                    'languages.exam_qualified',
                    'languages.checkedby',
                    'spoken.fluency_title as spoken_title',
                    'written.fluency_title as written_title'
                )
                ->orderBy('languages.langid', 'asc')
                ->get();

            $data['allLangData']     = DB::table('language_details')->get();
            $data['allFluencyData']  = DB::table('fluency_details')->get();

            return view('hr.languages.update', $data);
        }
    }




    public function viewOLD($langid = Null)
    {

        $staffid = Session::get('staffid');
        dd($staffid, $langid);

        if (is_null($langid)) {
            return redirect('/update/languages/' . $staffid);
        }
        if (!(DB::table('languages')->where('staffid', '=', $staffid)->where('langid', $langid)->first())) {
            return redirect('/update/languages/' . $staffid);
        }
        $data['names'] = DB::table('tblper')->where('ID', '=', $staffid)->first();
        if (!(DB::table('languages')->where('staffid', '=', $staffid)->first())) {
            //set session
            $data['languages']    = '';
            $data['langList']     = '';
            return view('hr.languages.update', $data);
        } else {
            $data['languages'] = DB::table('languages')->where('staffid', '=', $staffid)->where('langid', '=', $langid)->first();
            $data['langList']     = DB::table('languages')
                ->leftJoin('language_details', 'language_details.languageID', '=', 'languages.language')
                ->leftJoin('fluency_details as spoken', 'spoken.fluencyID', '=', 'languages.spoken')
                ->leftJoin('fluency_details as written', 'languages.written', '=', 'written.fluencyID')
                ->where('staffid', '=', $staffid)->select('langid', 'languages.language', 'languages.written', 'language_name', 'languages.exam_qualified', 'languages.checkedby', 'spoken.fluency_title as spoken_title', 'written.fluency_title as written_title')
                ->orderBy('languages.langid', 'asc')
                ->get();
            $data['allLangData']     = DB::table('language_details')->get();
            $data['allFluencyData']  = DB::table('fluency_details')->get();
            //
            return view('hr.languages.update', $data);
        }
    }

    public function view($langid = null)
    {
        $staffid = Session::get('staffid');

        if (!$staffid) {
            return redirect('/login')->with('error', 'Session expired, please log in again.');
        }

        if (is_null($langid)) {
            return redirect('/update/languages/' . $staffid);
        }

        $language = DB::table('languages')
            ->where('staffid', $staffid)
            ->where('langid', $langid)
            ->first();

        if (!$language) {
            return redirect('/update/languages/' . $staffid)->with('error', 'Language record not found.');
        }

        $data = [
            'names' => DB::table('tblper')->where('ID', $staffid)->first(),
            'languages' => $language,
            'langList' => DB::table('languages')
                ->leftJoin('language_details', 'language_details.languageID', '=', 'languages.language')
                ->leftJoin('fluency_details as spoken', 'spoken.fluencyID', '=', 'languages.spoken')
                ->leftJoin('fluency_details as written', 'languages.written', '=', 'written.fluencyID')
                ->where('staffid', $staffid)
                ->select(
                    'langid',
                    'languages.language',
                    'languages.written',
                    'language_name',
                    'languages.exam_qualified',
                    'languages.checkedby',
                    'spoken.fluency_title as spoken_title',
                    'written.fluency_title as written_title'
                )
                ->orderBy('languages.langid', 'asc')
                ->get(),
            'allLangData' => DB::table('language_details')->get(),
            'allFluencyData' => DB::table('fluency_details')->get(),
            'staffid' => $staffid,
        ];

        return view('hr.languages.update', $data);
    }



    public function update(Request $request)
    {
        $staffid = Session::get('staffid');


        if (is_null($staffid)) {
            return redirect('/update/languages/' . $staffid);
        }
        $this->validate(
            $request,
            [
                'lang'                  => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
                'spoken'                => 'regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
                'written'               => 'regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
                'exam_qualified'        => 'regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
                'checkedby'             => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
            ]
        );
        $lang                       = trim($request['lang']);
        $spoken                     = trim($request['spoken']);
        $written                    = trim($request['written']);
        $examquali                  = trim($request['exam_qualified']);
        $checkedby                  = trim($request['checkedby']);
        $date                       = date("Y-m-d");
        $langid                     = trim($request['langid']);
        $hiddenName                 = trim($request['hiddenName']);


        if (!empty($langid)) {
            //dd('ok1');
            DB::table('languages')->where('langid', '=', $langid)->where('staffid', '=', $staffid)->update(array(
                'staffid'           => $staffid,
                'language'         => $lang,
                'spoken'           => $spoken,
                'written'          => $written,
                'exam_qualified'   => $examquali,
                'checkedby'        => $checkedby,
                'updated_at'       => $date,
            ));
            $this->addLog('language was updated with ID: ' . $langid . ' and Staff ID: ' . $staffid);
        } else {
            //insert if hidden Name is empty
            //dd('ok2');
            $check = DB::table('languages')->where('staffid', '=', $staffid)->where('language', '=', $lang)->count();
            if ($check > 0) {
                return back()->with('err', 'Language Already Entered');
            }
            DB::table('languages')->insert(array(
                'staffid'           => $staffid,
                'language'         => $lang,
                'spoken'           => $spoken,
                'written'          => $written,
                'exam_qualified'   => $examquali,
                'checkedby'        => $checkedby,
                'updated_at'       => $date,
            ));
            $this->addLog('New language was added and Staff ID: ' . $staffid);
        }
        $data['languages']     = "";
        $data['langList']      = DB::table('languages')->where('staffid', '=', $staffid)->get();
        return redirect('/update/languages/' . $staffid)->with('msg', 'Operation was done successfully.');
    }

    public function destroyOLD($fileNo, $langid)
    {
        $staffid = Session::get('staffid');
        if (is_null($staffid) || is_null($langid)) {
            $data['languages']    = DB::table('languages')->where('staffid', '=', $staffid)->first();
            $data['langList']      = DB::table('languages')->where('staffid', '=', $staffid)->get();
            return view('hr.languages.update', $data);
        }
        //delete
        DB::table('languages')->where('staffid', '=', $staffid)->where('langid', '=', $langid)->delete();
        $this->addLog('One Language was deleted and staffid: ' . $staffid);
        if (!(DB::table('languages')->where('staffid', '=', $staffid)->first())) {
            return view('main.userArea');
        }
        $data['langList']     = DB::table('languages')->where('$staffid', '=', $staffid)->get();
        $data['languages']     = "";
        return view('hr.languages.update', $data)->with('msg', 'Operation was done successfully.');
    }

    public function destroyOLD2($fileNo, $langid)
    {
        $staffid = Session::get('staffid');

        if (is_null($staffid) || is_null($langid)) {
            $data['languages'] = DB::table('languages')->where('staffid', '=', $staffid)->first();
            $data['langList'] = DB::table('languages')->where('staffid', '=', $staffid)->get();
            return view('hr.languages.update', $data);
        }

        // delete record
        DB::table('languages')->where('staffid', '=', $staffid)->where('langid', '=', $langid)->delete();
        $this->addLog('One Language was deleted and staffid: ' . $staffid);

        // check if any record remains
        if (!(DB::table('languages')->where('staffid', '=', $staffid)->first())) {
            return view('hr.main.userArea');
        }

        $data['langList'] = DB::table('languages')->where('staffid', '=', $staffid)->get();
        $data['languages'] = "";

        return view('hr.languages.update', $data)->with('msg', 'Operation was done successfully.');
    }

    public function destroyOLD3($fileNo, $langid)
    {
        $staffid = Session::get('staffid');

        if (is_null($staffid) || is_null($langid)) {
            return redirect()->back()->with('error', 'Invalid delete request.');
        }

        // delete record
        DB::table('languages')
            ->where('staffid', '=', $staffid)
            ->where('langid', '=', $langid)
            ->delete();

        $this->addLog('One Language was deleted and staffid: ' . $staffid);

        return redirect('/update/languages/' . $staffid)->with('msg', 'Language record deleted successfully.');
    }

    public function destroy($langid = Null)
    {
        $staffid = Session::get('staffid');
        if (is_null($staffid)) {
            return redirect('/update/languages/' . $staffid);
        }
        //delete
        DB::table('languages')->where('langid', '=', $langid)->where('staffid', '=', $staffid)->delete();
        $this->addLog('One Language was deleted and staffid: ' . $staffid);
        return redirect('/update/languages/' . $staffid)->with('msg', 'Language record deleted successfully.');
    }





    //Language Report
    public function report($staffid = null)
    {
        if (is_null($staffid)) {
            return redirect('profile/details');
        }
        if (!(DB::table('tblper')->where('ID', '=', $staffid)->first())) {
            return redirect('profile/details');
        } else {
            $data['staffFullDetailsLanguage'] = DB::table('languages')
                ->where('staffid', '=', $staffid)
                ->orderBy('langid', 'Desc')
                ->get();
            $data['staffFullDetails'] = DB::table('tblper')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->where('tblper.staffid', '=', $staffid)
                ->first();
        }
        return view('Report.LanguageReport', $data);
    }
}
