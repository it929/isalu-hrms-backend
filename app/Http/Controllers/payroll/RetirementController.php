<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

class RetirementController extends Controller
{
    public function getRetirements($search, $search_type = 'dob')
    {
        $age_limit = 60;
        $service_limit = 35;

        $search_date = Carbon::parse($search)->format('Y-m-d');

        $query = DB::table('tblper')
            ->select(
                'tblper.ID',
                'tblper.surname',
                'tblper.first_name',
                'tblper.othernames',
                'tblper.dob',
                'tblper.appointment_date',
                DB::raw("DATE_ADD(tblper.dob, INTERVAL $age_limit YEAR) AS age_due"),
                DB::raw("DATE_ADD(tblper.appointment_date, INTERVAL $service_limit YEAR) AS served_due")
            );

        // Search by the actual DOB or Appointment Date
        if ($search_type === 'dob') {
            $query->where('tblper.dob', '=', $search_date);
        } elseif ($search_type === 'appointment_date') {
            $query->where('tblper.appointment_date', '=', $search_date);
        }

        $retirements = $query->get();

        $new_retirements = [];

        foreach ($retirements as $retirement) {
            $retirement->name = trim("{$retirement->surname} {$retirement->first_name} {$retirement->othernames}");
            // Set retirement date based on search type
            $retirement->retirement_date = ($search_type === 'dob') ? $retirement->age_due : $retirement->served_due;

            // Include all matches since we're searching by DOB or Appointment
            $new_retirements[] = $retirement;
        }

        return $new_retirements;
    }

    public function index()
    {
        $search = ''; // No default search date
        $retirements = []; // Empty array by default

        return view('retirement.index', [
            'retirements' => $retirements,
            'search' => $search,
            'search_type' => 'dob' // Default search type
        ]);
    }

    public function searchRecord(Request $request)
    {
        $search = $request->get('search');
        $search_type = $request->get('search_type'); // Either 'dob' or 'appointment_date'
        $retirements = $this->getRetirements($search, $search_type);

        return view('retirement.index', [
            'retirements' => $retirements,
            'search' => $search,
            'search_type' => $search_type
        ]);
    }
}