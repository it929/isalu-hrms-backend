<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use Session;
use App\Retirement;
use Carbon\Carbon;

class RetirementController extends Controller
{
    public function getRetirements($search){
        $age = 60;
        $served = 35;
        $check = "MONTH";
        $check_value = 1;
        $retirements = DB::table('tblper')
            ->where('staff_status', '!=', 0)
            // ->join('users', 'users.id', "=", 'tblper.UserID')
            ->join('tbldesignation', 'tbldesignation.id', "=", 'tblper.designation')
           ->select("*", DB::raw("TIMESTAMPDIFF(YEAR, DATE(dob), current_date) AS age,TIMESTAMPADD(YEAR,60,dob) AS age_due, TIMESTAMPDIFF(YEAR, DATE(appointment_date), current_date) AS served,TIMESTAMPADD(YEAR,35,appointment_date) AS served_due"))
            ->select("*", DB::raw("TIMESTAMPDIFF(YEAR, DATE(dob), '$search') AS age,TIMESTAMPADD(YEAR,60,dob) AS age_due, TIMESTAMPDIFF(YEAR, DATE(appointment_date), '$search') AS served,TIMESTAMPADD(YEAR,35,appointment_date) AS served_due"))
            ->get(); // 30
        
    // dd($retirements);

            $new_retirements = array();
            for($i = 0; $i<count($retirements); $i++){
                $retirements[$i]->retirement_date = "";
                if($retirements[$i]->age >= $age || $retirements[$i]->served >= $served){
                    $new_retirements[]=$retirements[$i];
                    if($retirements[$i]->age >=$age && $retirements[$i]->served >= $served){
                        if($retirements[$i]->served_due < $retirements[$i]->age_due){
                            $retirements[$i]->retirement_date = $retirements[$i]->served_due;
                        } else{
                            $retirements[$i]->retirement_date = $retirements[$i]->age_due;
                        }
                    }else{
                        if($retirements[$i]->served >= $served){
                            $retirements[$i]->retirement_date = $retirements[$i]->served_due;
                        }
                        if($retirements[$i]->age >= $age){
                            $retirements[$i]->retirement_date = $retirements[$i]->age_due;
                        }                        
                    }                    
                }
            }
            //FROM TODAY
            $today = date_create();
            $today_date = $today->format('Y-m-d');
            //dd($today_date);
            $new_retirements_from_today = array();
            for($j = 0; $j<count($new_retirements); $j++){
                if($new_retirements[$j]->retirement_date > $today_date){                 
                    $new_retirements_from_today[] = $new_retirements[$j];
                }
            }
          //  $retirements =  $new_retirements;
            $retirements =  $new_retirements_from_today;
            return $retirements;
    }

    public function index() 
    {
        $today = date_create();
        $today_date = $today->format('Y-m-d');
        $search = date('Y-m-d',strtotime($today_date. ' + 1 month'));
        $retirements = $this->getRetirements($search);
        
        return view('retirement.index', ['retirements'=>$retirements,'search'=>$search]);
    } 

    public function searchRecord(Request $request)
    {
        $search = $request->get('search');
        $retirements = $this->getRetirements($search);

        return view('retirement.index', ['retirements'=>$retirements,'search'=>$search]);
    }
}
