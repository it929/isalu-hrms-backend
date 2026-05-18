<?php

namespace App\Http\Controllers\funds;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Auth;
use Session;
use App\Http\Requests;
use DB;

class MainController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
    }//


   public function userArea()
   {
   		//confirmed if user has changed his/her password
   		$userID = Session::get('userID');
   		$users  = DB::table('users')
                ->where('users.id', $userID)
                ->select('users.first_login')
                ->first();
        //if(($users->first_login) == 0)
        //{
          //  return redirect('/user/editAccount')->with('err', 'We discovered that you have not changed your password. Please, Change your password now.');
       // }
   		return view('main.userArea');
   }//


}//end class