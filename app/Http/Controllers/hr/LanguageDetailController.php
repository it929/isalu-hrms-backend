<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;

use App\Models\LanguageDetail;
use App\Models\FluencyDetail;
use Illuminate\Http\Request;

class LanguageDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $fetchAllLanguage   =   LanguageDetail::get();
        $fetchAllFluency   =   FluencyDetail::get();
        return view('languages.create-language')->with(['dataLang' => $fetchAllLanguage, 'dataFluency' => $fetchAllFluency]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //

        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $this->validate($request, [
            'language' => ['required']
        ]);

        //dd($request);

        $langName   =   $request->get('language');
        
        try {
            //code...
            $countLang = LanguageDetail::where('language_name', '=', $langName)->count();
            if($countLang > 0){
                return back()->withInput($request->all())->with('error', ' Oops! Record already exist. ');
            }else{
            LanguageDetail::create(['language_name' => $langName]);
            }
        } catch (\Throwable $th) {
            //throw $th;
            return back()->withInput($request->all())->with('error', ' Oops! An error occurred during record creation. ');
        }

        return back()->withInput($request->all())->with('success', ' Great! New record created successfully. ');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LanguageDetail  $languageDetail
     * @return \Illuminate\Http\Response
     */
    public function show(LanguageDetail $languageDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LanguageDetail  $languageDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(LanguageDetail $languageDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LanguageDetail  $languageDetail
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LanguageDetail $languageDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LanguageDetail  $languageDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(LanguageDetail $languageDetail)
    {
        //
    }
}
