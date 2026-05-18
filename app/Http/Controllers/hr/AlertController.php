<?php

namespace App\Http\Controllers\hr;

use Session;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Http\Requests;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class AlertController extends ParentController
{

    public function __construct()
    {
        $this->middleware('auth');
    }



    public function index()
    {
        //
    }
    public function confirmationAlertList(Request $request)
    {
        DB::table('tbltime_variables')->where('id', 8)->update([
            'unit' => $request->unit,
            'period' => $request->period
        ]);
        $confirmation = DB::table('tbltime_variables')->where('id', 8)->first();
        return back()->with('msg', 'succesfully added settings');
    }
    public function confirmationList(Request $request)
    {
        $year = date('Y');
        /* $data['getCentralList'] = DB::table('tblper')
                ->leftJoin('promotion_alert', 'promotion_alert.fileNo', '=', 'tblper.fileNo')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                //->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                //->where('tblper.employee_type', '<>', 'CONSOLIDATED')
                ->where('tblper.staff_status', 1)
                ->where('tblper.fileNo','!=','SCN/000395')
                ->where('tblper.employee_type', '!=', 2)
                ->where('tblper.ID', '!=', 1)
                //->where('promotion_alert.year', '=', $year)
                //->where('promotion_alert.active', '=', 1)
                ->select('*', 'tblper.fileNo as fileNum')
                ->orderBy('tblper.grade', 'Desc')
                //->orderBy('tblper.step', 'Desc')
                //->orderBy('tblper.appointment_date', 'Asc')
                //->get();
                //dd($data['getCentralList']);
                ->paginate(50);

                dd($data['getCentralList']);
               // $howOldAmI = Carbon::createFromDate(1975, 5, 21)->age;

                // dd($howOldAmI); */

        $getTime = Carbon::now()->subYear(2);

        $getNewTime = date_format($getTime, "Y-m-d");
        // if($request->unit!=null&&$request->period!=null){
        //     db::table('tbltime_variables')->where('id',8)->insert([
        //     'unit' => $request->unit,
        //     'period' => $request->period
        //     ]);
        // $confirmation = db::table('tbltime_variables')->where('id',8)->first();
        // }
        // else{
        // $confirmation = db::table('tbltime_variables')->where('id',8)->first();
        // }
        $confirmation = db::table('tbltime_variables')->where('id', 8)->first();
        $variable = $confirmation->unit;

        $value = $confirmation->period;
        $data['variable_lone'] = $variable;
        $data['variables'] = db::table('time_units')->get();

        $data['period'] = $value;



        //dd($variable);
        if ($variable == 1) {

            $getTime = $getTime->addDay($value);
        } elseif ($variable == 2) {

            $getTime = $getTime->addMonth($value);
        } else {
            $getTime = $getTime->addYear($value);
        }
        $getTime = date_format($getTime, "Y-m-d");


        $data['getCentralList'] = DB::table('tblper')->leftJoin('lga', 'tblper.lgaID', '=', 'lga.lgaId')->leftjoin('tblstates', 'tblper.stateID', '=', 'tblstates.StateID')
            ->where('appointment_date', '<=', $getTime);

        $data['getCentralList'] = $data['getCentralList']->where('staff_status', '=', 1)->where('date_of_confirmation', '=', null)->paginate(50);

        foreach ($data['getCentralList'] as $list) {
            if ($list->appointment_date <= $getNewTime) {
                //dd($getNewTime);
                $list->check = true;
            } else {
                $list->check = false;
            }
        }
        //dd($data['getCentralList']);
        return view('estab/confirmationAlert', $data);
    }

    public function retireListOLD(Request $request)
    {
        $year = date('Y');
        /* $data['getCentralList'] = DB::table('tblper')
                ->leftJoin('promotion_alert', 'promotion_alert.fileNo', '=', 'tblper.fileNo')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                //->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                //->where('tblper.employee_type', '<>', 'CONSOLIDATED')
                ->where('tblper.staff_status', 1)
                ->where('tblper.fileNo','!=','SCN/000395')
                ->where('tblper.employee_type', '!=', 2)
                ->where('tblper.ID', '!=', 1)
                //->where('promotion_alert.year', '=', $year)
                //->where('promotion_alert.active', '=', 1)
                ->select('*', 'tblper.fileNo as fileNum')
                ->orderBy('tblper.grade', 'Desc')
                //->orderBy('tblper.step', 'Desc')
                //->orderBy('tblper.appointment_date', 'Asc')
                //->get();
                //dd($data['getCentralList']);
                ->paginate(50);

                dd($data['getCentralList']);
               // $howOldAmI = Carbon::createFromDate(1975, 5, 21)->age;

                // dd($howOldAmI); */
        $getTime = Carbon::now()->subYear(60);
        $getNewTime = date_format($getTime, "Y-m-d");
        $getWorkTime = Carbon::now()->subYear(35);
        $getNewWorkTime = date_format($getWorkTime, "Y-m-d");
        if ($request->unit == null || $request->unit == "") {
            $variable = 2;
            $value = 1;
        } else {
            $variable = $request->unit;

            $value = $request->period;
            $data['variable'] = $variable;
            $data['period'] = $value;
        }
        //dd($variable);
        if ($variable == 1) {

            $getTime = $getTime->addDay($value);
            $getWorkTime = $getWorkTime->addDay($value);
        } elseif ($variable == 2) {
            $getTime = $getTime->addMonth($value);
            $getWorkTime = $getWorkTime->addMonth($value);
        } else {
            $getTime = $getTime->addYear($value);
            $getWorkTime = $getWorkTime->addYear($value);
        }
        $getWorkTime = date_format($getWorkTime, "Y-m-d");
        $getTime = date_format($getTime, "Y-m-d");

        // $data['getCentralList'] = DB::table('tblper')->leftJoin('lga', 'tblper.lgaID', '=', 'lga.lgaId')->leftjoin('tblstates', 'tblper.stateID', '=', 'tblstates.StateID')
        //     ->where('dob', '<=', $getTime)->orWhere('appointment_date', '<=', $getWorkTime);
        // $data['getCentralList'] = $data['getCentralList']->where('staff_status', '=', 1)->where('is_retired', '=', '0')->paginate(50);
        $data['getCentralList'] = DB::table('tblper')
            ->leftJoin('lga', 'tblper.lgaID', '=', 'lga.lgaId')
            ->leftJoin('tblstates', 'tblper.stateID', '=', 'tblstates.StateID')
            ->where(function ($query) use ($getTime, $getWorkTime) {
                $query->where('dob', '>=', $getTime)
                    ->orWhere('appointment_date', '>=', $getWorkTime);
            })
            ->where('staff_status', 1)
            ->where('is_retired', 0)
            ->paginate(50);

        // $query = DB::table('tblper')
        //     ->leftJoin('lga', 'tblper.lgaID', '=', 'lga.lgaId')
        //     ->leftJoin('tblstates', 'tblper.stateID', '=', 'tblstates.StateID')
        //     ->where(function ($query) use ($getTime, $getWorkTime) {
        //         $query->where('dob', '<=', $getTime)
        //             ->orWhere('appointment_date', '<=', $getWorkTime);
        //     })
        //     ->where('staff_status', 1)
        //     ->where('is_retired', 0);

        // dd($query->toSql(), $query->getBindings());





        foreach ($data['getCentralList'] as $list) {
            if ($list->dob <= $getNewTime || $list->appointment_date <= $getNewWorkTime) {
                //dd($getNewTime);
                $list->check = true;
            } else {
                $list->check = false;
            }
        }


        return view('hr.estab.retirementAlert', $data);
    }






    public function retireList(Request $request)
    {
        $today = Carbon::now();

        $unit = $request->input('unit');    // 1 = Days, 2 = Months, 3 = Years
        $period = $request->input('period'); // e.g. 1, 2, 3 ...



        $query = DB::table('tblper')
            ->leftJoin('lga', 'tblper.lgaID', '=', 'lga.lgaId')
            ->leftJoin('tblstates', 'tblper.stateID', '=', 'tblstates.StateID')
            ->where('tblper.staff_status', 1)
            ->where('tblper.is_retired', 0)
            ->where('tblper.employee_type', 1)
            ->whereNotNull('tblper.dob');   // <-- Exclude NULL dob

        $allStaff = $query->get();

        $filtered = [];




        foreach ($allStaff as $list) {
            $dob = Carbon::parse($list->dob);
            $appointmentDate = Carbon::parse($list->appointment_date);

            $age = $dob->diffInYears($today);
            $yearsInService = $appointmentDate->diffInYears($today);

            // Default
            $list->check = false;
            $list->remaining = '';
            $list->retirement_date = null;

            if ($age >= 60 || $yearsInService >= 35) {
                // Already retired
                $list->check = true;
                $list->remaining = "Retirement Reached !!!";
                $filtered[] = $list;
            } else {
                // Determine retirement date
                $retirementByAge = $dob->copy()->addYears(60);
                $retirementByService = $appointmentDate->copy()->addYears(35);
                $retirementDate = $retirementByAge < $retirementByService
                    ? $retirementByAge
                    : $retirementByService;

                // Calculate remaining time
                $diff = $today->diff($retirementDate);
                $diffInDays = $today->diffInDays($retirementDate);
                $diffInMonths = $today->diffInMonths($retirementDate);
                $diffInYears = $today->diffInYears($retirementDate);

                $list->retirement_date = $retirementDate->format('d M Y');

                // Format remaining text
                if ($diff->y > 0) {
                    $list->remaining = sprintf(
                        "%d year%s, %d month%s, %d day%s remaining (Retires on %s)",
                        $diff->y,
                        $diff->y > 1 ? 's' : '',
                        $diff->m,
                        $diff->m > 1 ? 's' : '',
                        $diff->d,
                        $diff->d > 1 ? 's' : '',
                        $list->retirement_date
                    );
                } elseif ($diff->m > 0) {
                    $list->remaining = sprintf(
                        "%d month%s, %d day%s remaining (Retires on %s)",
                        $diff->m,
                        $diff->m > 1 ? 's' : '',
                        $diff->d,
                        $diff->d > 1 ? 's' : '',
                        $list->retirement_date
                    );
                } else {
                    $list->remaining = sprintf(
                        "%d day%s remaining (Retires on %s)",
                        $diff->d,
                        $diff->d > 1 ? 's' : '',
                        $list->retirement_date
                    );
                }

                // ✅ Filter based on form inputs

                if ($unit && $period) {
                    $diffInDays = $today->diffInDays($retirementDate);

                    switch ($unit) {
                        case '1': // Days
                            $min = ($period - 1);
                            $max = $period;
                            break;

                        case '2': // Months
                            $min = ($period - 1) * 30;
                            $max = $period * 30;
                            break;

                        case '3': // Years
                            $min = ($period - 1) * 365;
                            $max = $period * 365;
                            break;

                        default:
                            $min = 0;
                            $max = PHP_INT_MAX;
                            break;
                    }

                    // ✅ Include only those whose remaining days fall *within* this exact range
                    $include = $diffInDays > $min && $diffInDays <= $max;
                } else {
                    $include = true;
                }

                if ($include) {
                    $filtered[] = $list;
                }
            }
        }




        // ✅ Order by retirement time, grade, step, appointment date, and present appointment date
        usort($filtered, function ($a, $b) {

            // Push staff without retirement date to the bottom
            if (empty($a->retirement_date)) return 1;
            if (empty($b->retirement_date)) return -1;

            // 1️⃣ Retirement date (earliest first)
            $retA = Carbon::parse($a->retirement_date)->timestamp;
            $retB = Carbon::parse($b->retirement_date)->timestamp;
            if ($retA !== $retB) return $retA <=> $retB;

            // 2️⃣ Grade (higher grade first)
            if ((int)$a->grade !== (int)$b->grade) return (int)$b->grade <=> (int)$a->grade;

            // 3️⃣ Step (higher step first)
            if ((int)$a->step !== (int)$b->step) return (int)$b->step <=> (int)$a->step;

            // 4️⃣ Original appointment date (earlier = senior)
            $appA = Carbon::parse($a->appointment_date)->timestamp;
            $appB = Carbon::parse($b->appointment_date)->timestamp;
            if ($appA !== $appB) return $appA <=> $appB;

            // 5️⃣ Present appointment date (earlier = senior)
            $presentAppA = Carbon::parse($a->date_present_appointment)->timestamp;
            $presentAppB = Carbon::parse($b->date_present_appointment)->timestamp;
            return $presentAppA <=> $presentAppB;
        });






        // Manual pagination
        // $filteredCollection = collect($filtered);
        // $page = request()->input('page', 1);
        // $perPage = 50;

        // $data['getCentralList'] = new \Illuminate\Pagination\LengthAwarePaginator(
        //     $filteredCollection->forPage($page, $perPage),
        //     $filteredCollection->count(),
        //     $perPage,
        //     $page,
        //     ['path' => request()->url(), 'query' => request()->query()]
        // );

        // Return all data without pagination

        $data['getCentralList'] = collect($filtered);

        // Keep user selections for the form
        $data['variable'] = $unit;
        $data['period'] = $period;

        return view('hr.estab.retirementAlert', $data);
    }

    public function retireList13122025(Request $request)
    {
        $today = Carbon::now();

        $unit   = $request->input('unit');     // 1 = Days, 2 = Months, 3 = Years
        $period = $request->input('period');   // 1, 2, 3 ...

        $query = DB::table('tblper')
            ->select(
                'tblper.*',
                'tblper.grade',
                'tblper.step',
                'tblper.appointment_date',
                'lga.lga AS lga',               // ✅ LGA name
                'tblstates.state AS State'  // ✅ State name
            )
            ->leftJoin('lga', 'tblper.lgaID', '=', 'lga.lgaId')
            ->leftJoin('tblstates', 'tblper.stateID', '=', 'tblstates.StateID')
            ->where('staff_status', 1)
            ->where('is_retired', 0);

        $allStaff = $query->get();

        $filtered = [];

        foreach ($allStaff as $list) {

            $dob             = Carbon::parse($list->dob);
            $appointmentDate = Carbon::parse($list->appointment_date);

            $age             = $dob->diffInYears($today);
            $yearsInService  = $appointmentDate->diffInYears($today);

            // Defaults
            $list->check = false;
            $list->remaining = '';
            $list->retirement_date = null;
            $list->retirement_date_raw = null;

            // Already retired
            if ($age >= 60 || $yearsInService >= 35) {
                $list->check = true;
                $list->remaining = 'Retirement Reached !!!';
                $filtered[] = $list;
                continue;
            }

            // Calculate retirement date
            $retirementByAge     = $dob->copy()->addYears(60);
            $retirementByService = $appointmentDate->copy()->addYears(35);

            $retirementDate = $retirementByAge < $retirementByService
                ? $retirementByAge
                : $retirementByService;

            $list->retirement_date_raw = $retirementDate;
            $list->retirement_date = $retirementDate->format('d M Y');

            // Remaining time
            $diff = $today->diff($retirementDate);

            if ($diff->y > 0) {
                $list->remaining = "{$diff->y} year(s), {$diff->m} month(s), {$diff->d} day(s) remaining";
            } elseif ($diff->m > 0) {
                $list->remaining = "{$diff->m} month(s), {$diff->d} day(s) remaining";
            } else {
                $list->remaining = "{$diff->d} day(s) remaining";
            }

            // 🔍 Filter by unit & period
            if ($unit && $period) {

                $diffInDays = $today->diffInDays($retirementDate);

                switch ($unit) {
                    case '1': // Days
                        $min = $period - 1;
                        $max = $period;
                        break;

                    case '2': // Months
                        $min = ($period - 1) * 30;
                        $max = $period * 30;
                        break;

                    case '3': // Years
                        $min = ($period - 1) * 365;
                        $max = $period * 365;
                        break;

                    default:
                        $min = 0;
                        $max = PHP_INT_MAX;
                }

                $include = $diffInDays > $min && $diffInDays <= $max;
            } else {
                $include = true;
            }

            if ($include) {
                $filtered[] = $list;
            }
        }

        /**
         * ✅ SORTING:
         * 1. Nearest retirement
         * 2. Highest grade
         * 3. Highest step
         * 4. Earliest appointment
         */
        $filteredCollection = collect($filtered)->sortBy([
            fn($a, $b) => $a->retirement_date_raw <=> $b->retirement_date_raw,
            fn($a, $b) => $b->grade <=> $a->grade,
            fn($a, $b) => $b->step <=> $a->step,
            fn($a, $b) => strtotime($a->appointment_date) <=> strtotime($b->appointment_date),
        ])->values();

        // 📄 Manual pagination
        $page = request()->input('page', 1);
        $perPage = 50;

        $data['getCentralList'] = new LengthAwarePaginator(
            $filteredCollection->forPage($page, $perPage),
            $filteredCollection->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Preserve form values
        $data['variable'] = $unit;
        $data['period']  = $period;

        return view('hr.estab.retirementAlert', $data);
    }









    public function retireListNotifyOLD(Request $request)
    {
        $today = Carbon::now();
        $currentYear = $today->year;
        $currentMonth = $today->month;

        $allStaff = DB::table('tblper')
            ->leftJoin('lga', 'tblper.lgaID', '=', 'lga.lgaId')
            ->leftJoin('tblstates', 'tblper.stateID', '=', 'tblstates.StateID')
            ->where('staff_status', 1)
            ->where('is_retired', 0)
            ->get();


        $filtered = [];

        foreach ($allStaff as $staff) {
            $dob = Carbon::parse($staff->dob);
            $appointmentDate = Carbon::parse($staff->appointment_date);

            // Determine retirement by age (60 years) or by service (35 years)
            $retirementByAge = $dob->copy()->addYears(60);
            $retirementByService = $appointmentDate->copy()->addYears(35);

            // Whichever comes first
            $retirementDate = $retirementByAge < $retirementByService
                ? $retirementByAge
                : $retirementByService;

            $diffInDays = $today->diffInDays($retirementDate, false);

            // Include only if:
            // 1. Retirement is this month (for notify)
            // 2. Or already retired this year
            if (
                ($retirementDate->year == $currentYear && $retirementDate->month == $currentMonth)
                || ($retirementDate->year == $currentYear && $diffInDays < 0)
            ) {
                $staff->retirement_date = $retirementDate->format('d-m-Y');

                if ($diffInDays < 0) {
                    // Already retired
                    $staff->check = true;
                    $staff->remaining = "Retired (" . $retirementDate->format('d M Y') . ")";
                    $staff->can_notify = false;
                } else {
                    // Not yet retired but within current month
                    $diff = $today->diff($retirementDate);
                    $years = $diff->y;
                    $months = $diff->m;
                    $days = $diff->d;

                    // Build readable text
                    $parts = [];
                    if ($years > 0) $parts[] = "$years year" . ($years > 1 ? 's' : '');
                    if ($months > 0) $parts[] = "$months month" . ($months > 1 ? 's' : '');
                    if ($days > 0) $parts[] = "$days day" . ($days > 1 ? 's' : '');

                    $remainingText = implode(', ', $parts);

                    $staff->check = false;
                    $staff->remaining = "$remainingText remaining (" . $retirementDate->format('d M Y') . ")";
                    $staff->can_notify = true;
                }

                $filtered[] = $staff;
            }
        }

        // Manual pagination
        $filteredCollection = collect($filtered);
        $page = request()->input('page', 1);
        $perPage = 50;

        $data['getCentralList'] = new LengthAwarePaginator(
            $filteredCollection->forPage($page, $perPage),
            $filteredCollection->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('hr.estab.retirementAlertNotify', $data);
    }

    public function retireListNotify(Request $request)
    {
        $today = Carbon::now();
        $currentYear = $today->year;
        $currentMonth = $today->month;

        // Get user search inputs
        $unit = $request->input('unit'); // 1 = Days, 2 = Months, 3 = Years
        $period = $request->input('period'); // numeric value

        $allStaff = DB::table('tblper')
            ->leftJoin('lga', 'tblper.lgaID', '=', 'lga.lgaId')
            ->leftJoin('tblstates', 'tblper.stateID', '=', 'tblstates.StateID')
            ->where('staff_status', 1)
            ->where('is_retired', 0)
            ->where('tblper.employee_type', 1)
            ->whereNotNull('tblper.dob')
            ->get();

        $filtered = [];

        foreach ($allStaff as $staff) {
            $dob = Carbon::parse($staff->dob);
            $appointmentDate = Carbon::parse($staff->appointment_date);

            // Calculate retirement by age or service
            $retirementByAge = $dob->copy()->addYears(60);
            $retirementByService = $appointmentDate->copy()->addYears(35);

            // Whichever comes first
            $retirementDate = $retirementByAge < $retirementByService
                ? $retirementByAge
                : $retirementByService;

            // Days remaining till retirement
            $diffInDays = $today->diffInDays($retirementDate, false);



            // Determine retirement status
            $diffInDays = $today->diffInDays($retirementDate, false); // false keeps sign
            $staff->retirement_date = $retirementDate->format('d-m-Y');

            if ($diffInDays < 0) {
                // ✅ Already retired
                $staff->remaining = "Retirement Reached !!! (" . $retirementDate->format('d M Y') . ")";
                $staff->check = true;
                $staff->can_notify = false;
            } else {
                // ✅ Yet to retire — build remaining text
                $diff = $today->diff($retirementDate);
                $years = $diff->y;
                $months = $diff->m;
                $days = $diff->d;

                $parts = [];
                if ($years > 0) $parts[] = "$years year" . ($years > 1 ? 's' : '');
                if ($months > 0) $parts[] = "$months month" . ($months > 1 ? 's' : '');
                if ($days > 0) $parts[] = "$days day" . ($days > 1 ? 's' : '');

                $remainingText = implode(', ', $parts);
                $staff->remaining = "$remainingText remaining (" . $retirementDate->format('d M Y') . ")";
                $staff->check = false;
                $staff->can_notify = true;
            }


            // 🔍 Filter based on selected search option


            $include = false;

            if ($unit && $period) {
                $diffInDays = $today->diffInDays($retirementDate, false);

                switch ($unit) {
                    case '1': // Days
                        $min = ($period - 1);
                        $max = $period;
                        break;

                    case '2': // Months
                        $min = ($period - 1) * 30;
                        $max = $period * 30;
                        break;

                    case '3': // Years
                        $min = ($period - 1) * 365;
                        $max = $period * 365;
                        break;

                    default:
                        $min = 0;
                        $max = PHP_INT_MAX;
                        break;
                }

                // ✅ Include only those whose remaining days fall *within* this exact range
                $include = $diffInDays > $min && $diffInDays <= $max;
            } else {
                // Default behavior — show this month’s retirees
                $include =
                    ($retirementDate->year == $currentYear && $retirementDate->month == $currentMonth)
                    || ($retirementDate->year == $currentYear && $today->greaterThan($retirementDate));
            }

            if ($include) {
                $filtered[] = $staff;
            }
        }

        // Manual pagination
        $filteredCollection = collect($filtered);
        $page = $request->input('page', 1);
        $perPage = 50;

        $data['getCentralList'] = new LengthAwarePaginator(
            $filteredCollection->forPage($page, $perPage),
            $filteredCollection->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Keep form inputs for re-population
        $data['variable'] = $unit;
        $data['period'] = $period;

        return view('hr.estab.retirementAlertNotify', $data);
    }


    public function notifySalaryDepartment(Request $request)
    {
        $fileNo = $request->input('fileNo');
        $retireDate = $request->input('retireDate');

        // ✅ Convert retire date to proper MySQL format
        if ($retireDate) {
            try {
                // Handles both 29-11-2025 and 2025-11-29 formats
                if (strpos($retireDate, '-') !== false) {
                    $parts = explode('-', $retireDate);
                    if (strlen($parts[0]) == 4) {
                        // Already in YYYY-MM-DD
                        $retireDate = Carbon::parse($retireDate)->format('Y-m-d');
                    } else {
                        // Convert from DD-MM-YYYY
                        $retireDate = Carbon::createFromFormat('d-m-Y', $retireDate)->format('Y-m-d');
                    }
                } else {
                    // Fallback for other formats
                    $retireDate = Carbon::parse($retireDate)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $retireDate = null;
            }
        }

        // ✅ Fetch staff details
        $staff = DB::table('tblper')
            ->where('fileNo', $fileNo)
            ->first();

        if (!$staff) {
            return response()->json(['message' => 'Staff record not found!'], 404);
        }

        // ✅ Get active month and year from tblactivemonth
        $activeMonth = DB::table('tblactivemonth')->first();
        $monthPayment = $activeMonth->month ?? date('M'); // e.g. MAY
        $yearPayment = $activeMonth->year ?? date('Y');   // e.g. 2025

        // ✅ Prepare data to insert or update
        $data = [
            'staffid'             => $staff->ID,
            'fileNo'              => $staff->fileNo,
            'courtID'             => $staff->courtID ?? null,
            'divisionID'          => $staff->divisionID ?? null,
            'oldEmploymentType'   => $staff->employee_type ?? null,
            'old_grade'           => $staff->grade ?? null,
            'old_step'            => $staff->step ?? null,
            'due_date'            => $retireDate,
            'month_payment'       => $monthPayment,
            'year_payment'        => $yearPayment,
            'payment_status'      => 0,
            'notifiedBy'          => auth()->user()->name ?? 'System',
            'approvedBy'          => '', // ✅ avoid NULL constraint
            'approvedDate'        => null,
            'arrears_activation'  => 0,
        ];

        // ✅ Insert or update
        $exists = DB::table('tblstaff_for_retirement')->where('fileNo', $fileNo)->exists();

        if ($exists) {
            DB::table('tblstaff_for_retirement')
                ->where('fileNo', $fileNo)
                ->update($data);

            $message = 'Staff retirement record updated successfully!';
        } else {
            DB::table('tblstaff_for_retirement')->insert($data);
            $message = 'Staff added to retirement notification list successfully!';
        }

        return response()->json(['message' => $message]);
    }





    //Computing Staff Increment
    public function incrementList()
    {
        $data = [];

        //Initial
        $letGetAll1stYearTableID            = [];
        $letGetAllJanORJulyYearTableID1     = [];
        $letGetAllJanORJulyYearTableID2     = [];
        $newDateDueForIncrement             = [];

        //Get date parameters to start...
        $getTodaysDay               = date('d');
        $getTodaysMonth             = date('m'); //used
        $getTodaysYear              = date('Y'); //used

        //Get all staff
        $getStaffList = DB::table('tblper')
            ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->where('tblper.staff_status', 1)
            ->where('tblper.employee_type', '<>', 2)
            ->where('tblper.ID', '<>', 1)
            ->where('tblper.grade', '<>', 17)
            ->get();
        //->paginate(30);

        if ($getStaffList) {
            foreach ($getStaffList as $key => $item) {
                //get staff present-appointment-date
                $getPresentAppointDateYear      = date('Y', strtotime($item->incremental_date));
                $getPresentAppointDateMonth     = date('m', strtotime($item->incremental_date));
                $appointmentDate                = date('Y', strtotime($item->appointment_date));
                $getIfFirstYear                 = $getTodaysYear - $appointmentDate;
                $get1stYearIncrement            = ($getPresentAppointDateYear + 1);
                $newDateDueForIncrement[$item->ID] = date('d', strtotime($item->incremental_date)) . '-' . $getPresentAppointDateMonth . '-' . $get1stYearIncrement;

                if (($getIfFirstYear <= 1) && ($get1stYearIncrement <= $getTodaysYear) && ($getPresentAppointDateMonth <= $getTodaysMonth)) {
                    //check if increment is for the 1st year ===USE SAME MONTH POINTED TO SAME MONTH IN THE NEXT YEAR===
                    $letGetAll1stYearTableID[] = $item->ID;
                } elseif (($getIfFirstYear > 1) && ($get1stYearIncrement <= $getTodaysYear) && ($getPresentAppointDateMonth <= $getTodaysMonth)) {
                    //check if increment is greater than the 1st year. ===USE JANUARY 1ST OR JULY 1ST===
                    if ($getTodaysMonth > 0 && $getTodaysMonth < 7) {
                        //Jan - June
                        if (($getPresentAppointDateMonth > 0) && ($getPresentAppointDateMonth <= 6)) {
                            $letGetAllJanORJulyYearTableID1[] = $item->ID;
                        }
                    } else {
                        //July - Dec
                        if (($getPresentAppointDateMonth > 6) && ($getPresentAppointDateMonth <= 12)) {
                            $letGetAllJanORJulyYearTableID2[] = $item->ID;
                        }
                    }
                }
            }
        }

        $data2['newDateDueForIncrement'] = $newDateDueForIncrement;
        $getArray1              = array_unique(array_merge($letGetAll1stYearTableID, $letGetAllJanORJulyYearTableID1), SORT_REGULAR);
        $getAllIncrementTableID = array_unique(array_merge($getArray1, $letGetAllJanORJulyYearTableID2), SORT_REGULAR);
        $data['getCentralList'] = DB::table('tblper')->whereIn('ID', $getAllIncrementTableID)
            ->orderBy('tblper.grade', 'Desc')
            ->orderBy('tblper.step', 'Desc')->paginate(50);


        return view('estab/incrementAlert', $data)->with($data2);
    }



    //
    public function printDoc($id)
    {
        $data['staff'] = DB::table('tblper')->where('ID', '=', $id)->first();
        $data['varTemp'] = DB::table('tblvariation_temp')->where('staffid', '=', $id)->first();
        $data['comments'] = DB::table('tblvariation_comments')
            ->leftJoin('users', 'users.id', '=', 'tblvariation_comments.sent_by')
            ->where('staffid', '=', $id)
            ->where('reverse', '=', 0)
            ->get();

        return view('hr.estab/printDoc', $data);
    }

    public function variationOrder($id)
    {


        $staff = DB::table('tblvariation_temp')->where('staffid', '=', $id)->first();
        $data['staff'] = DB::table('tblper')
            ->join('tblvariation_temp', 'tblvariation_temp.staffid', '=', 'tblper.ID')
            ->where('staffid', '=', $id)->first();
        $data['salary'] = DB::table('basicsalaryconsolidated')
            ->where('grade', '=', $staff->new_grade)
            ->where('step', '=', $staff->new_step)
            ->first();
        $data['oldsalary'] = DB::table('basicsalaryconsolidated')
            ->where('grade', '=', $staff->old_grade)
            ->where('step', '=', $staff->old_step)
            ->first();
        return view('hr.estab/variationOrder', $data);
    }

    public function storeArrears(Requests $request)
    {
        //$list= DB::table('tblvariation_temp')->where('staffid','=', $request['staff'])->first();
        /*DB::table('tblstaff_for_arrears')->insert(array(
                            'staffid'         => $list->ID,
                            'fileNo'          => $list->fileNo,
                            'courtID'         => $list->courtID,
                            'divisionID'      => $list->divisionID,
                            'arrears_type'    => 'increment',
                            'old_grade'       => $list->grade,
                            'old_step'        => $list->step,
                            'new_grade'       => $list->grade,
                            'new_step'        => $newStep,
                            'due_date'        => $list->appointment_date,
                            'approvedBy'      => Auth::user()->name,
                            'approvedDate'    => date('Y-m-d'),
                           ));

                           DB::table('tblper')->where('ID','=',$list->ID)->update(array(
                            'grade'       => $list->grade,
                            'step'        => $list->step,

                           ));*/
        /*DB::table('tblvariation_temp')->where('staffid','=',$request['staff'])->update(array(
                            'confirm'         => 1,

                           ));
         DB::table('tblvariation_comments')->where('staffid','=',$list->staffid)->where('payment_status','=',0)->update(array(
                            'payment_status'         => 1,

                           ));
                           */
        return response()->json('Successfully Approved for Payment');
    }

    public function variationRemark(Request $request)
    {

        $staffid   = $request['staffid'];
        $data      = DB::table('tblper')->where('ID', '=', $staffid)->first();

        return response()->json($data);
    }

    public function saveRemark(Request $request)
    {

        $staffid   = $request['staffid'];
        $data      = DB::table('tblper')->where('ID', '=', $staffid)->first();

        $code = $request['staffCode'];

        //dd($code);

        if ($code == 'VO') {

            DB::table('tblvariation_comments')->insert(array(
                'staffid'         => $staffid,
                'comment'         => $request['remark'],
                'sent_by'         => Auth::user()->id,
                'sent_to'         => 'PA',
                'sentby_code'     => 'VO',
                'updated_at'      => date('Y-m-d'),
            ));

            DB::table('tblper')->where('ID', '=', $staffid)->update(array(
                'promotion_alert'         => 1,

            ));

            return back()->with('msg', 'Staff variation sent for further Approval');
        } elseif ($code == 'PA') {
            DB::table('tblvariation_comments')->insert(array(
                'staffid'         => $staffid,
                'comment'         => $request['remark'],
                'sent_by'         => Auth::user()->id,
                'sent_to'         => 'DD',
                'sentby_code'     => 'PA',
                'updated_at'      => date('Y-m-d'),
            ));

            DB::table('tblvariation_comments')
                ->where('sent_to', '=', 'PA')
                ->where('staffid', '=', $staffid)
                ->where('payment_status', '=', 0)
                ->update(array(

                    'worked_on'       => 1,
                ));
            DB::table('tblper')->where('ID', '=', $staffid)->update(array(
                'promotion_alert'         => 2,

            ));
            return back()->with('msg', 'Staff variation sent for further Approval');
        } elseif ($code == 'DD') {
            DB::table('tblvariation_comments')->insert(array(
                'staffid'         => $staffid,
                'comment'         => $request['remark'],
                'sent_by'         => Auth::user()->id,
                'sent_to'         => 'AS',
                'sentby_code'     => 'DD',
                'updated_at'      => date('Y-m-d'),
            ));

            DB::table('tblvariation_comments')
                ->where('sent_to', '=', 'DD')
                ->where('staffid', '=', $staffid)
                ->where('payment_status', '=', 0)
                ->update(array(

                    'worked_on'       => 1,
                ));
            DB::table('tblvariation_comments')
                ->where('sent_to', '=', 'PA')
                ->where('worked_on', '=', 1)
                ->where('staffid', '=', $staffid)
                ->update(array(
                    'no_recall'     => 1,
                ));
            DB::table('tblper')->where('ID', '=', $staffid)->update(array(
                'promotion_alert'         => 3,

            ));
            return back()->with('msg', 'Staff variation sent for further Approval');
        } elseif ($code == 'AS') {
            DB::table('tblvariation_comments')->insert(array(
                'staffid'         => $staffid,
                'comment'         => $request['remark'],
                'sent_by'         => Auth::user()->id,
                'sent_to'         => 'DD',
                'sentby_code'     => 'AS',
                'updated_at'      => date('Y-m-d'),
            ));
            DB::table('tblvariation_comments')
                ->where('sent_to', '=', 'AS')
                ->where('staffid', '=', $staffid)
                ->where('payment_status', '=', 0)
                ->update(array(

                    'worked_on'       => 1,
                ));
            DB::table('tblvariation_comments')
                ->where('sent_to', '=', 'DD')
                ->where('worked_on', '=', 1)
                ->where('staffid', '=', $staffid)
                ->update(array(
                    'no_recall'     => 1,
                ));

            DB::table('tblper')->where('ID', '=', $staffid)->update(array(
                'promotion_alert'         => 2,

            ));
            return back()->with('msg', 'Staff variation sent for further Approval');
        }
    }

    public function reverseRemark(Request $request)
    {

        $staffid   = $request['staffid'];
        $data      = DB::table('tblper')->where('ID', '=', $staffid)->first();
        $staff = DB::table('tblvariation_approval_staff')
            ->where('user_id', '=', Auth::user()->id)
            ->first();

        $code = $staff->code;
        //dd($code);

        if ($code == 'VO') {
            $get = DB::table('tblvariation_comments')
                ->where('sent_to', '=', 'PA')
                ->where('worked_on', '=', 0)
                ->where('staffid', '=', $staffid)
                ->first();

            $get2 = DB::table('tblvariation_comments')
                ->where('sent_to', '=', 'VO')
                //->where('sent_by','=',$sentby)
                ->where('worked_on', '=', 1)
                ->where('payment_status', '=', 0)
                ->where('staffid', '=', $staffid)
                ->first();

            if ($get != '') {
                DB::table('tblvariation_comments')->where('Id', '=', $get->Id)->update(array(
                    'reverse'         => 1,
                    //'worked_on'       => 0,
                ));
                DB::table('tblvariation_comments')->where('Id', '=', $get2->Id)->update(array(
                    //'reverse'         => 1,
                    'worked_on'       => 0,

                ));

                DB::table('tblvariation_comments')
                    ->where('sent_to', '=', 'PA')
                    ->where('worked_on', '=', 1)
                    ->where('staffid', '=', $staffid)
                    ->update(array(
                        'no_recall'     => 0,
                    ));
                DB::table('tblper')->where('ID', '=', $staffid)->update(array(
                    'promotion_alert'         => 1,

                ));
            }

            $del = DB::table('tblvariation_comments')
                ->where('sent_by', '=', $staff->user_id)
                ->where('worked_on', '=', 0)
                ->where('payment_status', '=', 0)
                ->where('staffid', '=', $staffid)
                ->delete();

            return response()->json('Successfully Reversed');
            //return back()->with('msg','Staff variation sent for further Approval');
        } elseif ($code == 'PA') {
            $get = DB::table('tblvariation_comments')
                ->where('sent_to', '=', 'DD')
                ->where('worked_on', '=', 0)
                ->where('staffid', '=', $staffid)
                ->first();

            $get2 = DB::table('tblvariation_comments')
                ->where('sent_to', '=', 'PA')
                //->where('sent_by','=',$sentby)
                ->where('worked_on', '=', 1)
                ->where('payment_status', '=', 0)
                ->where('staffid', '=', $staffid)
                ->first();

            if ($get != '') {
                DB::table('tblvariation_comments')->where('Id', '=', $get->Id)->update(array(
                    'reverse'         => 1,
                    //'worked_on'       => 0,

                ));
                DB::table('tblvariation_comments')->where('Id', '=', $get2->Id)->update(array(
                    //'reverse'         => 1,
                    'worked_on'       => 0,

                ));

                DB::table('tblper')->where('ID', '=', $staffid)->update(array(
                    'promotion_alert'         => 2,

                ));
                $del = DB::table('tblvariation_comments')
                    ->where('sent_by', '=', $staff->user_id)
                    ->where('worked_on', '=', 0)
                    ->where('payment_status', '=', 0)
                    ->where('staffid', '=', $staffid)
                    ->delete();
            }
            //return back()->with('msg','Staff variation sent for further Approval');
            return response()->json('Successfully Reversed');
        } elseif ($code == 'DD') {
            $get = DB::table('tblvariation_comments')
                ->where('sent_to', '=', 'AS')
                ->where('worked_on', '=', 0)
                ->where('staffid', '=', $staffid)
                ->first();
            $get2 = DB::table('tblvariation_comments')
                ->where('sent_to', '=', 'DD')
                //->where('sent_by','=',$sentby)
                ->where('worked_on', '=', 1)
                ->where('payment_status', '=', 0)
                ->where('staffid', '=', $staffid)
                ->first();

            if ($get != '') {
                DB::table('tblvariation_comments')->where('Id', '=', $get->Id)->update(array(
                    'reverse'         => 1,
                    //'worked_on'       => 0,

                ));
                DB::table('tblvariation_comments')->where('Id', '=', $get2->Id)->update(array(
                    //'reverse'         => 1,
                    'worked_on'       => 0,

                ));

                DB::table('tblper')->where('ID', '=', $staffid)->update(array(
                    'promotion_alert'         => 3,

                ));
                $del = DB::table('tblvariation_comments')
                    ->where('sent_by', '=', $staff->user_id)
                    ->where('worked_on', '=', 0)
                    ->where('payment_status', '=', 0)
                    ->where('staffid', '=', $staffid)
                    ->delete();
            }
            //return back()->with('msg','Staff variation sent for further Approval');
            return response()->json('Successfully Reversed');
        } elseif ($code == 'AS') {
            $sentby = Auth::user()->id;
            $get = DB::table('tblvariation_comments')
                ->where('sent_to', '=', 'DD')
                ->where('sent_by', '=', $sentby)
                ->where('worked_on', '=', 0)
                ->where('staffid', '=', $staffid)
                ->first();

            $get2 = DB::table('tblvariation_comments')
                ->where('sent_to', '=', 'AS')
                //->where('sent_by','=',$sentby)
                ->where('worked_on', '=', 1)
                ->where('payment_status', '=', 0)
                ->where('staffid', '=', $staffid)
                ->first();

            if ($get != '') {
                DB::table('tblvariation_comments')->where('Id', '=', $get->Id)->update(array(
                    'reverse'         => 1,
                    //'worked_on'       => 0,

                ));

                DB::table('tblvariation_comments')->where('Id', '=', $get2->Id)->update(array(
                    //'reverse'         => 1,
                    'worked_on'       => 0,

                ));

                DB::table('tblper')->where('ID', '=', $staffid)->update(array(
                    'promotion_alert'         => 4,

                ));
                $del = DB::table('tblvariation_comments')
                    ->where('sent_by', '=', $staff->user_id)
                    ->where('worked_on', '=', 0)
                    ->where('payment_status', '=', 0)
                    ->where('staffid', '=', $staffid)
                    ->delete();
            }
            //return back()->with('msg','Staff variation sent for further Approval');
            return response()->json('Successfully Reversed');
        }
    }


    public function reject(Request $request)
    {

        $staffid   = $request['staffid'];
        $data      = DB::table('tblper')->where('ID', '=', $staffid)->first();

        $code = $request['staffCode'];
        //dd($code);

        /*if($code == 'VO')
        {

         DB::table('tblvariation_comments')->insert(array(
                            'staffid'         => $staffid ,
                            'comment'         => $request['remark'],
                            'sent_by'         => Auth::user()->id,
                            'sent_to'         => 'PA',
                            'rejected'       => 1,
                            'updated_at'      => date('Y-m-d'),
                           ));

         DB::table('tblper')->where('ID','=',$staffid)->update(array(
                            'promotion_alert'         => 1,

                           ));

        return back()->with('msg','Staff variation sent for further Approval');
        }*/
        if ($code == 'PA') {
            DB::table('tblvariation_comments')->insert(array(
                'staffid'         => $staffid,
                'comment'         => $request['remark'],
                'sent_by'         => Auth::user()->id,
                'sent_to'         => 'VO',
                'sentby_code'     => 'PA',
                'rejected'        => 1,
                'updated_at'      => date('Y-m-d'),
            ));

            DB::table('tblper')->where('ID', '=', $staffid)->update(array(
                'promotion_alert'         => 2,

            ));
            DB::table('tblvariation_comments')
                ->where('sent_to', '=', 'PA')
                ->where('staffid', '=', $staffid)
                ->where('payment_status', '=', 0)
                ->update(array(

                    'worked_on'       => 1,
                ));
            return back()->with('msg', 'Staff variation sent for further Approval');
        } elseif ($code == 'DD') {
            DB::table('tblvariation_comments')->insert(array(
                'staffid'         => $staffid,
                'comment'         => $request['remark'],
                'sent_by'         => Auth::user()->id,
                'sent_to'         => 'PA',
                'sentby_code'     => 'DD',
                'rejected'        => 1,
                'updated_at'      => date('Y-m-d'),
            ));
            DB::table('tblvariation_comments')
                ->where('sent_to', '=', 'DD')
                ->where('staffid', '=', $staffid)
                ->where('payment_status', '=', 0)
                ->update(array(

                    'worked_on'       => 1,
                    'no_recall'       => 1,
                ));
            DB::table('tblvariation_comments')
                ->where('sent_to', '=', 'PA')
                ->where('staffid', '=', $staffid)
                ->where('payment_status', '=', 0)
                ->update(array(
                    'no_recall'       => 1,
                ));

            DB::table('tblper')->where('ID', '=', $staffid)->update(array(
                'promotion_alert'         => 3,

            ));
            return back()->with('msg', 'Staff variation sent for further Approval');
        } elseif ($code == 'AS') {
            DB::table('tblvariation_comments')->insert(array(
                'staffid'         => $staffid,
                'comment'         => $request['remark'],
                'sent_by'         => Auth::user()->id,
                'sent_to'         => 'DD',
                'sentby_code'     => 'AS',
                'rejected'        => 1,
                'updated_at'      => date('Y-m-d'),
            ));

            DB::table('tblper')->where('ID', '=', $staffid)->update(array(
                'promotion_alert'         => 2,

            ));
            DB::table('tblvariation_comments')
                ->where('sent_to', '=', 'AS')
                ->where('staffid', '=', $staffid)
                ->where('payment_status', '=', 0)
                ->update(array(

                    'worked_on'       => 1,
                ));
            DB::table('tblvariation_comments')
                ->where('sent_to', '=', 'DD')
                ->where('staffid', '=', $staffid)
                ->where('payment_status', '=', 0)
                ->update(array(
                    'no_recall'       => 1,
                ));
            return back()->with('msg', 'Staff variation sent for further Approval');
        }
    }


    public function variationApproval()
    {
        $data['approver'] = DB::table('tblvariation_approval_staff')
            ->where('user_id', '=', Auth::user()->id)
            ->first();
        // $code ='';
        $userId = Auth::user()->id;
        if ($data['approver'] == '') {
            $code = '';
        } else {
            $code = $data['approver']->code;
        }
        //dd($code);
        //$data['variationList'] = DB::table('tblvariation_comments')
        //->join('tblper','tblper.ID','=','tblvariation_comments.staffid')
        /*->where(function ($query) {
                $query->where('sent_to','=',$code)
                      ->orWhere('sent_by', '=', 'AS');
            })
         */
        //->where('payment_status','=',0)
        //->where('worked_on','=',0)

        // ->groupBy('tblvariation_comments.staffid')
        //->get();

        /*$data['variationList'] = DB::Select("select * from tblvariation_comments join tblper on tblper.ID = tblvariation_comments.staffid where (sent_to = '$code' ) and sent_by = '$userId' and payment_status =0 and (worked_on =0) group by tblvariation_comments.staffid");*/

        $data['variationList'] = DB::Select("select * from tblvariation_comments join tblper on tblper.ID = tblvariation_comments.staffid where (sent_to = '$code' ) and payment_status =0 and (worked_on =0 or no_recall = 0) and reverse=0 group by tblvariation_comments.staffid");

        //dd($data['variationList'] );

        return view('hr.estab/variationApproval', $data);
    }


    public function variationApprovalPA()
    {
        $data['approver'] = DB::table('tblvariation_approval_staff')
            ->where('user_id', '=', Auth::user()->id)
            ->first();
        // $code ='';
        $userId = Auth::user()->id;
        if ($data['approver'] == '') {
            $code = '';
        } else {
            $code = $data['approver']->code;
        }
        //dd($code);
        //$data['variationList'] = DB::table('tblvariation_comments')
        //->join('tblper','tblper.ID','=','tblvariation_comments.staffid')
        /*->where(function ($query) {
                $query->where('sent_to','=',$code)
                      ->orWhere('sent_by', '=', 'AS');
            })
         */
        //->where('payment_status','=',0)
        //->where('worked_on','=',0)

        // ->groupBy('tblvariation_comments.staffid')
        //->get();

        $data['variationList'] = DB::Select("select * from tblvariation_comments join tblper on tblper.ID = tblvariation_comments.staffid where (sent_to = '$code' ) and sent_by = '$userId' and payment_status =0 and (worked_on =0) group by tblvariation_comments.staffid");

        return view('hr.estab/variationApproval', $data);
    }

    public function variationApprovalDD()
    {
        $data['approver'] = DB::table('tblvariation_approval_staff')
            ->where('user_id', '=', Auth::user()->id)
            ->first();
        // $code ='';
        $userId = Auth::user()->id;
        if ($data['approver'] == '') {
            $code = '';
        } else {
            $code = $data['approver']->code;
        }
        //dd($code);
        //$data['variationList'] = DB::table('tblvariation_comments')
        //->join('tblper','tblper.ID','=','tblvariation_comments.staffid')
        /*->where(function ($query) {
                $query->where('sent_to','=',$code)
                      ->orWhere('sent_by', '=', 'AS');
            })
         */
        //->where('payment_status','=',0)
        //->where('worked_on','=',0)

        // ->groupBy('tblvariation_comments.staffid')
        //->get();

        $data['variationList'] = DB::Select("select * from tblvariation_comments join tblper on tblper.ID = tblvariation_comments.staffid where (sent_to = '$code' ) and sent_by = '$userId' and payment_status =0 and (worked_on =0) group by tblvariation_comments.staffid");

        return view('hr.estab.variationApproval', $data);
    }

    public function variationApprovalAudit()
    {
        $data['approver'] = DB::table('tblvariation_approval_staff')
            ->where('user_id', '=', Auth::user()->id)
            ->first();
        // $code ='';
        $userId = Auth::user()->id;
        if ($data['approver'] == '') {
            $code = '';
        } else {
            $code = $data['approver']->code;
        }
        //dd($code);
        //$data['variationList'] = DB::table('tblvariation_comments')
        //->join('tblper','tblper.ID','=','tblvariation_comments.staffid')
        /*->where(function ($query) {
                $query->where('sent_to','=',$code)
                      ->orWhere('sent_by', '=', 'AS');
            })
         */
        //->where('payment_status','=',0)
        //->where('worked_on','=',0)

        // ->groupBy('tblvariation_comments.staffid')
        //->get();

        $data['variationList'] = DB::Select("select * from tblvariation_comments join tblper on tblper.ID = tblvariation_comments.staffid where (sent_to = '$code' ) and sent_by = '$userId' and payment_status =0 and (worked_on =0) group by tblvariation_comments.staffid");

        return view('hr.estab.variationApproval', $data);
    }

    public function rejectReason(Request $request)
    {
        $approver = DB::table('tblvariation_approval_staff')
            ->where('user_id', '=', Auth::user()->id)
            ->first();
        $staffid   = $request['staffid'];
        $data      = DB::table('tblvariation_comments')
            ->join('tblper', 'tblper.ID', '=', 'tblvariation_comments.staffid')
            ->where('staffid', '=', $staffid)
            ->where('rejected', '=', 1)
            ->where('sent_to', '=', $approver->code)
            ///->select('comment')
            ->first();

        return response()->json($data);
    }

    public function promotionArrearsEntry()
    {
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        $staffses = session('staff_id');

        $data['staffData'] = DB::table('tblper')->where('courtID', '=', $data['CourtInfo']->courtid)->orderBy('surname')->get();
        $data['staff'] = DB::table('tblper')
            ->join('tblemployment_type', 'tblemployment_type.id', '=', 'tblper.employee_type')
            ->where('tblper.ID', '=', $staffses)
            ->first();
        //dd($data['staff']);
        return view('hr.estab.promotionArrearsEntry', $data);
    }

    public function createSes(Request $request)
    {

        $staffid   = $request['staff'];
        $request->session()->flash('staff_id', $staffid);
        //Session::put('judge_id',$request['id']);

        return response()->json('set');
    }

    public function saveEntry(Request $request)
    {
        //dd('ok');
        $insert = DB::table('tblvariation_temp')->insert(array(
            'staffid'                => $request['fileNo'],
            'newEmploymentType'      => $request['newEmpType'],
            'oldEmploymentType'      => $request['newEmpType'],
            'courtID'                => $request['court'],
            'old_grade'              => $request['oldGrade'],
            'old_step'               => $request['oldStep'],
            'new_grade'              => $request['newGrade'],
            'new_step'               => $request['newStep'],
            'due_date'               => $request['dueDate'],
            'divisionID'             => $request['division'],
            'arrears_type'           => $request['arrearsType'],

            'payment_status'         => 0,
        ));

        /* DB::table('tblper')->where('staffid','=',$request['fileNo'])->update(array(
       'grade_new'              => $request['newGrade'],
       'step_new'               => $request['newStep'],
      ));
      */

        /* $staff = $this->getOneStaff($request['fileNo']);
      if($staff != '')
      {
      $this->addLog("Variation added for $staff->surname $staff->first_name $staff->othernames with File Number $staff->fileNo");
      }*/
        if ($insert) {
            return redirect('/admin/promotion-arrears/entry')->with('msg', 'Successfully Entered');
        } else {
            return redirect('/admin/promotion-arrears/entry')->with('err', 'Record could not be saved');
        }
    }
    public function variationList()
    {
        $data['variationList'] = DB::table('tblvariation_temp')
            ->join('tblper', 'tblper.ID', '=', 'tblvariation_temp.staffid')
            ->where('tblvariation_temp.treated', '=', 0)->get();
        return view('estab.variationList', $data);
    }
    public function saveVariation(Request $request)
    {
        $con = $request['confirm'];
        foreach ($con as $key => $value) {
            $data =  DB::table('tblvariation_temp')->where('staffid', '=', $request['confirm'][$key])->first();

            DB::table('tblstaff_for_arrears2')->insert(array(
                'staffid'                => $data->staffid,
                'newEmploymentType'      => $data->newEmploymentType,
                'oldEmploymentType'      => $data->oldEmploymentType,
                'courtID'                => $data->courtID,
                'old_grade'              => $data->old_grade,
                'old_step'               => $data->old_step,
                'new_grade'              => $data->new_grade,
                'new_step'               => $data->new_step,
                'due_date'               => $data->due_date,
                'divisionID'             => $data->divisionID,
                'arrears_type'           => $data->arrears_type,
                'payment_status'         => 0,
            ));

            DB::table('tblvariation_temp')->where('staffid', '=', $request['confirm'][$key])->update(array(
                'treated'         => 1,
            ));

            /* DB::table('tblper')->where('staffid','=',$request['confirm'][$key])->update(array(
       'grade'              => $data->new_grade,
       'step'               => $data->new_step,
      ));
      */
        }

        return back()->with('msg', 'Successful');
    }
}
