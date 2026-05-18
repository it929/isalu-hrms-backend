<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;

use App\Models\FluencyDetail;
use Illuminate\Http\Request;

class FluencyDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
            'fluency_title' => ['required']
        ]);

        //dd($request);

        $flTitle   =   $request->get('fluency_title');

        try {
            //code...
            $countLang = FluencyDetail::where('fluency_title', '=', $flTitle)->count();
            if($countLang > 0){
                return back()->withInput($request->all())->with('error', ' Oops! Record already exist. ');
            }else{
            FluencyDetail::create(['fluency_title' => $flTitle]);
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
     * @param  \App\Models\FluencyDetail  $fluencyDetail
     * @return \Illuminate\Http\Response
     */
    public function show(FluencyDetail $fluencyDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FluencyDetail  $fluencyDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(FluencyDetail $fluencyDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FluencyDetail  $fluencyDetail
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FluencyDetail $fluencyDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FluencyDetail  $fluencyDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(FluencyDetail $fluencyDetail)
    {
        //
    }
}
