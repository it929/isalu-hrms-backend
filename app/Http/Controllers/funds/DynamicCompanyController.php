<?php

namespace App\Http\Controllers\funds;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use Carbon\Carbon;
use Entrust;
use Session;
use Excel;
use Illuminate\Support\Facades\Input;
use DB;
use QrCode;
use Illuminate\Support\Facades\Crypt;


class DynamicCompanyController extends function24Controller
{
	public function index(Request $request){
		$data['dynamics'] = DB::table('tblcompany')->first();
		Session::put('company', $data['dynamics']);
		return redirect('/login2');
	}
}