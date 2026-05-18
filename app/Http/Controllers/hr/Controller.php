<?php

namespace App\Http\Controllers\hr;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public static function getNominated()
    {
        $loggedUser = DB::table('tblper')->where('UserID', Auth::user()->id)->first();
        // dd($loggedUser->ID);
        try {
            $nominated = DB::table('tbltraining_staff')
                        ->join('tbltraining', 'tbltraining.ID', '=', 'tbltraining_staff.trainingID')
                        ->where('tbltraining_staff.staffID', '=', $loggedUser->ID)
                        ->where('tbltraining.date_concluded', '=', null)
                        ->count();
        return $nominated;

        } catch (\Throwable $th) {
            //throw $th;
        }
        
    }

}
