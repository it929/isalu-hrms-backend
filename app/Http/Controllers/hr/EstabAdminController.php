<?php

namespace App\Http\Controllers\hr;



use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class EstabAdminController extends Controller
{

    public function index()
    {
        //
    }


    public function view_CENTRAL_LIST()
    {
        $centralByMonth = Session::get('centralByMonth');
        $centralByDay   = Session::get('centralByDay');
        $filterBy       = Session::get('filterBy');

        $centralByMonth = Session::forget('centralByMonth');
        $centralByDay   = Session::forget('centralByDay');
        $filterBy       = Session::forget('filterBy');

        //get All staff due for INCREMENT $this->month (by form $_GET)

        $data['getCentralList'] = DB::table('tblper')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')

            ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
            ->where('tblper.staff_status', '=', 1)
            ->orderBy('tblper.grade', 'Desc')
            ->orderBy('tblper.step', 'Desc')
            ->orderBy('tblper.appointment_date', 'Asc')
            ->paginate(10);

        $data['allList'] = DB::table('tblper')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')

            ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
            ->where('tblper.staff_status', '=', 1)
            ->get();
        $data['getDivision'] = DB::table('tbldivision')->orderBy('division', 'Asc')->get();
        $data['headFile'] = "CENTRAL NOMINAL ROLL: LIST OF STAFF DUE FOR INCREMENT IN ALL DIVISIONS";
        return view('hr.estab.index', $data);
    }


    public function test(Request $request)
    {

        $data['list'] = DB::table('tblper')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')

            //->where('tblper.employee_type', '<>', 'CONSOLIDATED')
            ->where('tblper.staff_status', 1)
            ->where('tblper.fileNo', '=', 183)
            /* ->orderBy('tblper.grade', 'Desc')
                ->orderBy('tblper.step', 'Desc')
                ->orderBy('tblper.appointment_date', 'Asc')*/
            ->first();
        //dd($data);
        //$data['getDivision'] = DB::table('tbldivision')->orderBy('division', 'Asc')->get();
        //$data['headFile'] = "CENTRAL NOMINAL ROLL: LIST OF STAFF DUE FOR INCREMENT IN ALL DIVISIONS";


        return view('estab.test', $data);
    }

    public function getProfile($id)
    {
        $data['promotion'] = "";
        $data['list'] = DB::table('tblper')
            //->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->where('tblper.ID', '=', $id)
            ->first();

        $data['educations'] = DB::table('tbleducations')
            ->where('staffid', '=', $id)
            ->get();

        $data['records'] = DB::table('recordof_service')
            ->where('staffid', '=', $id)
            ->get();

        $data['promotion'] = DB::table('promotion_detail')
            ->where('staffid', '=', $id)
            ->where('active', '=', 1)
            ->first();

        $data['convert'] = DB::table('conversion_advancement')
            ->where('staffid', '=', $id)
            ->where('active', '=', 1)
            ->first();

        return view('estab/promotionBrief', $data);
    }


    public function promotion()
    {
        return view('estab/promotionBrief');
    }

    public function upgrade($fileNo)
    {
        $data['list'] = DB::table('tblper')
            ->where('tblper.fileNo', '=', $fileNo)
            ->first();
        $data['educations'] = DB::table('tbleducations')
            ->where('fileNo', '=', $fileNo)
            ->get();

        $data['profbody'] = DB::table('professional_bodies')
            ->where('fileNo', '=', $fileNo)
            ->get();
        $data['previous_work'] = DB::table('previous_servicedetails')
            ->where('fileNo', '=', $fileNo)
            ->get();
        $data['upgrade'] = DB::table('upgrading_details')
            ->where('fileNo', '=', $fileNo)
            ->first();
        $data['convert'] = DB::table('conversion_advancement')
            ->where('fileNo', '=', $fileNo)
            ->first();
        //dd($data)
        return view('estab/upgradingForm', $data);
    }

    public function convert_advance($fileNo)
    {
        $data['list'] = DB::table('tblper')
            ->where('tblper.fileNo', '=', $fileNo)
            ->first();
        $data['educations'] = DB::table('tbleducations')
            ->where('fileNo', '=', $fileNo)
            ->get();

        $data['profbody'] = DB::table('professional_bodies')
            ->where('fileNo', '=', $fileNo)
            ->get();
        $data['previous_work'] = DB::table('previous_servicedetails')
            ->where('fileNo', '=', $fileNo)
            ->get();
        $data['convert'] = DB::table('upgrading_details')
            ->where('fileNo', '=', $fileNo)
            ->first();
        //dd($data)
        return view('estab/conversionForm', $data);
    }

    public function conversionList()
    {
        $data['staffList'] = DB::table('tblper')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
            ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
            ->where('tblper.staff_status', 1)
            ->orderBy('tblper.grade', 'Desc')
            ->orderBy('tblper.step', 'Desc')
            ->orderBy('tblper.appointment_date', 'Asc')
            ->paginate(10);

        return view('estab/staffConversionList', $data);
    }


    //Promotion Alert
    public function promotionSearch(Request $request)
    {
        $presentAppointmentDate  = $request['presentAppointmentDate'];
        return redirect()->route('listStaffPromotion', ['id' => $presentAppointmentDate]);
    }

    public function promotionList($presentAppointmentDate = null)
    {
        $newPromotionDate = [];
        $getAllUser_1   = [];
        $getAllUser_2   = [];
        $getAllUser   = [];
        $newDateDueForIncrement_4 = [];
        $data['getDate'] = $presentAppointmentDate;

        $monthRange     = ($presentAppointmentDate ? 0 : 1);
        $currentYear    = ($presentAppointmentDate ? date('Y', strtotime($presentAppointmentDate)) : date('Y'));
        $currentMonth   = ($presentAppointmentDate ? date('m', strtotime($presentAppointmentDate)) : 11);
        $currentDay     = ($presentAppointmentDate ? date('d', strtotime($presentAppointmentDate)) : date('d'));

        //Get all users
        $getAllUserList = DB::table('tblper')->where('tblper.staff_status', 1)->where('rank', 0)->where('grade', '<', 17)->get();

        if ($getAllUserList) {
            foreach ($getAllUserList as $key => $item) {
                $getPresentAppointDateYear          = date('Y', strtotime($item->date_present_appointment));
                $getPresentAppointDateMonth         = date('m', strtotime($item->date_present_appointment));
                $getPresentAppointDateDay           = date('d', strtotime($item->date_present_appointment));
                $getNewDate = null;

                if ($item->grade <= 7) {
                    //Promotion Year is 2 years
                    if (($getPresentAppointDateYear + 2) < $currentYear) {
                        $getAllUser[] = $item->ID;
                        $newDateDueForIncrement_4[$item->ID]  = date('d', strtotime($item->date_present_appointment)) . '-' . $getPresentAppointDateMonth . '-' . ($getPresentAppointDateYear + 2);
                        $this->updateUserRecord($item->ID, $isUserDue = 1);
                    } else if (($getPresentAppointDateYear + 2) == $currentYear) {
                        if ((($getPresentAppointDateYear + 2) <= $currentYear) && ($getPresentAppointDateMonth <= ($currentMonth + $monthRange)) && ($getPresentAppointDateDay <= $currentDay)) {
                            $getAllUser[] = $item->ID;
                            $newDateDueForIncrement_4[$item->ID]  = date('d', strtotime($item->date_present_appointment)) . '-' . $getPresentAppointDateMonth . '-' . ($getPresentAppointDateYear + 2);
                            $this->updateUserRecord($item->ID, $isUserDue = 1);
                        }
                    }
                }
                if ($item->grade <= 14) {
                    //Promotion Year is 3 years
                    if (($getPresentAppointDateYear + 3) < $currentYear) {
                        $getAllUser[] = $item->ID;
                        $newDateDueForIncrement_4[$item->ID]  = date('d', strtotime($item->date_present_appointment)) . '-' . $getPresentAppointDateMonth . '-' . ($getPresentAppointDateYear + 3);
                        $this->updateUserRecord($item->ID, $isUserDue = 1);
                    } else if (($getPresentAppointDateYear + 3) == $currentYear) {
                        if ((($getPresentAppointDateYear + 3) <= $currentYear) && ($getPresentAppointDateMonth <= ($currentMonth + $monthRange)) && ($getPresentAppointDateDay <= $currentDay)) {
                            $getAllUser[] = $item->ID;
                            $newDateDueForIncrement_4[$item->ID]  = date('d', strtotime($item->date_present_appointment)) . '-' . $getPresentAppointDateMonth . '-' . ($getPresentAppointDateYear + 3);
                            $this->updateUserRecord($item->ID, $isUserDue = 1);
                        }
                    }
                }
                if ($item->grade <= 16) {
                    //Promotion Year is 4 years
                    if (($getPresentAppointDateYear + 4) < $currentYear) {
                        $getAllUser[] = $item->ID;
                        $newDateDueForIncrement_4[$item->ID]  = date('d', strtotime($item->date_present_appointment)) . '-' . $getPresentAppointDateMonth . '-' . ($getPresentAppointDateYear + 4);
                        $this->updateUserRecord($item->ID, $isUserDue = 1);
                    } else if (($getPresentAppointDateYear + 4) == $currentYear) {
                        if ((($getPresentAppointDateYear + 4) <= $currentYear) && ($getPresentAppointDateMonth <= ($currentMonth + $monthRange)) && ($getPresentAppointDateDay <= $currentDay)) {
                            $getAllUser[] = $item->ID;
                            $newDateDueForIncrement_4[$item->ID]  = date('d', strtotime($item->date_present_appointment)) . '-' . $getPresentAppointDateMonth . '-' . ($getPresentAppointDateYear + 4);
                            $this->updateUserRecord($item->ID, $isUserDue = 1);
                        }
                    }
                }
            }
        }

        $data['newPromotionDate']   = $newDateDueForIncrement_4;

        $data['getCentralList'] = DB::table('tblper')->whereIn('ID', $getAllUser)
            ->orderBy('tblper.grade', 'Desc')
            ->orderBy('tblper.step', 'Desc')
            ->paginate(50);

        $data['designations'] = DB::table('tbldesignation')->get();
        // dd($data['getCentralList']);

        return view('hr.estab.promotionList', $data);
    }

    public function getNoMonth($date1, $date2)
    {
        $howeverManyMonths = 0;
        $date1 = $date1;
        $date2 = $date2;
        $d1 = new DateTime($date2);
        $d2 = new DateTime($date1);
        $Months = $d2->diff($d1);
        $howeverManyMonths = (($Months->y) * 12) + ($Months->m);

        return $howeverManyMonths;
    }

    //Update User due for increment
    public function updateUserRecord($getPerID = null, $isUserDue = 1)
    {
        $sccess = 0;
        if ($getPerID <> null && $isUserDue == 1) {
            try {
                $success = DB::table('tblper')->where('ID', $getPerID)->where('promotion_alert', 0)->update(['promotion_alert' => $isUserDue]);
            } catch (\Throwable $e) {
            }
        }

        return $success;
    }


    public function upgradeDetails(Request $request)
    {
        $post   = $request['position'];
        $fileNo = $request['fileNo'];
        $grade  = $request['grade'];
        $step  = $request['step'];
        $reco   = $request['recommendation'];
        $quali  = $request['qualification'];
        $date   = date('Y-m-d');

        $insert = DB::table('upgrading_details')->insert(array(
            'fileNo'                        => $fileNo,
            'additional_qualification'      => $quali,
            'post_considered'               => $post,
            'recommendations'               => $reco,
            'new_grade'                     => $grade,
            'new_step'                      => $step,
            'created_at'                    => $date,
            'active'                        => 1,

        ));


        $data = '<div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <strong>Successfully Saved! You can now click on close to exit the dialogue box </strong>
                </div> ';
        $error_saving = '<div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <strong>Not Saved!</strong>
                </div> ';
        if ($insert) {

            return response()->json($data);
        } else {
            return response()->json($error_saving);
        }
        //return response()->json($fileNo);
    }

    public function saveAdvancement(Request $request)
    {
        $post     = $request['position'];
        $fileNo   = $request['fileNo'];
        $grade    = $request['grade'];
        $effdate  = $request['effdate'];
        $type     = $request['type'];
        $step     = $request['step'];
        $date     = date('Y-m-d');

        $insert = DB::table('conversion_advancement')->insert(array(
            'fileNo'                        => $fileNo,
            'type'                          => $type,
            'proposed_post'                 => $post,
            'effective_date'                => $effdate,
            'new_grade'                     => $grade,
            'new_step'                      => $step,
            'created_at'                    => $date,

        ));


        $data = '<div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <strong>Successfully Saved! You can now click on close to exit the dialogue box </strong>
                </div> ';
        $error_saving = '<div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <strong>Not Saved!</strong>
                </div> ';
        if ($insert) {

            return response()->json($data);
        } else {
            return response()->json($error_saving);
        }
    }

    public function savePromotion(Request $request)
    {
        $post     = $request['position'];
        $fileNo   = $request['fileNo'];
        $grade    = $request['grade'];
        $effdate  = $request['effdate'];
        $type     = $request['type'];
        $step     = $request['step'];
        $date     = date('Y-m-d');

        $insert = DB::table('promotion_detail')->insert(array(
            'fileNo'                        => $fileNo,
            'reason'                        => $type,
            'proposed_post'                 => $post,
            'effective_date'                => $effdate,
            'newgrade'                      => $grade,
            'newstep'                       => $grade,
            'date_updated'                  => $date,
            'active'                        => 1,

        ));


        $data = '<div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <strong>Successfully Saved! You can now click on close to exit the dialogue box </strong>
                </div> ';
        $error_saving = '<div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <strong>Not Saved!</strong>
                </div> ';
        if ($insert) {

            return response()->json($data);
        } else {
            return response()->json($error_saving);
        }
    }

    public function autocomplete(Request $request)
    {
        $query = $request->input('query');
        $search = DB::table('tblper')->where('surname', 'LIKE', '%' . $query . '%')->orWhere('first_name', 'LIKE', '%' . $query . '%')->orWhere('fileNo', 'LIKE', '%' . $query . '%')->take(15)->get();
        $return_array = null;
        foreach ($search as $s) {
            $return_array[]  =  ["value" => $s->surname . ' ' . $s->first_name . ' ' . $s->othernames . ' - ' . $s->fileNo, "data" => $s->fileNo];
        }
        return response()->json(array("suggestions" => $return_array));
    }

    public function showAll(Request $request)
    {
        $fileNo = $request->input('nameID');

        $data['staffList'] = DB::table('tblper')
            ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
            ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
            ->where('tblper.staff_status', 1)
            ->where('tblper.fileNo', '=', $fileNo)
            ->orderBy('tblper.grade', 'Desc')
            ->orderBy('tblper.step', 'Desc')
            ->orderBy('tblper.appointment_date', 'Asc')
            ->paginate(10);

        return view('estab/staffConversionList', $data);
    }

    public function confirm(Request $request)
    {
        $fileNo = $request->input('fileNo');
        $confirm = DB::table('promotion_detail')
            ->where('fileNo', '=', $fileNo)
            ->where('active', '=', 1)
            ->first();
        $count = DB::table('promotion_detail')
            ->where('fileNo', '=', $fileNo)
            ->where('active', '=', 1)
            ->count();

        $check = DB::table('tblper')
            ->where('fileNo', '=', $fileNo)
            ->first();
        if ($check->promotion_alert == 1 && $check->stepalert == 0 && $check->gradealert == 0) {
            $message = "Can not be Reverted.Reason: Variation already computed";
        } elseif ($check->promotion_alert == 1 && $check->stepalert > 0 && $check->gradealert > 0) {

            DB::table('tblper')->where('fileNo', '=', $fileNo)->update(array(
                'stepalert'                     => 0,
                'gradealert'                    => 0,
                'variationreason'               => "",
                'promotion_alert'               => 0,


            ));
            return response()->json("Confirmation Successfull");
        } else {
            if ($count == 1) {
                DB::table('tblper')->where('fileNo', '=', $fileNo)->update(array(
                    'stepalert'                     => $confirm->newgrade,
                    'gradealert'                    => $confirm->newstep,
                    'variationreason'               => $confirm->reason,
                    'promotion_alert'               => 1,

                ));
                return response()->json("Confirmation Successfull");
            }
        }
    }

    public function promotionConfirm(Request $request)
    {
        $fileNo = $request->input('fileNo');
        $confirm = DB::table('promotion_detail')
            ->where('fileNo', '=', $fileNo)
            ->where('active', '=', 1)
            ->first();
        $count = DB::table('promotion_detail')
            ->where('fileNo', '=', $fileNo)
            ->where('active', '=', 1)
            ->count();

        $check = DB::table('tblper')
            ->where('fileNo', '=', $fileNo)
            ->first();
        if ($check->promotion_alert == 1 && $check->stepalert == 0 && $check->gradealert == 0) {
            return response()->json("Can not be Reverted.Reason: Variation already computed");
        } elseif ($check->promotion_alert == 1 && $check->stepalert > 0 && $check->gradealert > 0) {

            DB::table('tblper')->where('fileNo', '=', $fileNo)->update(array(
                'stepalert'                     => 0,
                'gradealert'                    => 0,
                'variationreason'               => "",
                'promotion_alert'               => 0,

            ));
            return response()->json("Confirmation Successfull");
        } else {
            if ($count == 1) {
                DB::table('tblper')->where('fileNo', '=', $fileNo)->update(array(
                    'stepalert'                     => $confirm->new_grade,
                    'gradealert'                    => $confirm->new_step,
                    'variationreason'               => $confirm->type,
                    'promotion_alert'               => 1,

                ));
                return response()->json("Confirmation Successfull");
            }
        }
    }

    public function getDetails(Request $request)
    {
        $fileNo = $request['fileNo'];
        $profile = DB::table('tblper')->where('fileNo', '=', $fileNo)->get();
        return response()->json($profile);
    }

    public function promotionAlert()
    {
        $currentDateTime = Carbon::parse('2019-11-20');

        $newDateTime = $currentDateTime->addYears(2)->subMonth(2);
        //$newDateTimex = $currentDateTime->addYears(3)->addMonth(2);

        $to = Carbon::createFromFormat('Y-m-d', '2021-5-5');
        $current = Carbon::now();
        $from = Carbon::createFromFormat('Y-m-d', '2021-7-5');
        $diff_in_months = $to->diffInMonths($current);
        //dd($diff_in_months);

        //dd($newDateTime);
        $data['getCentralList'] = DB::table('tblper')
            //->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
            //->where('tblper.date_present_appointment','>=',$current)
            ->where('tblper.date_present_appointment', '<=', $newDateTime)
            ->where('tblper.staff_status', 1)
            ->where('tblper.employee_type', '!=', 2)
            ->where('tblper.employee_type', '!=', 3)
            //->where('tblper.ID', '=', 3)
            //->where('tblper.increment_alert', '=', 0)
            ->orderBy('tblper.grade', 'DESC')
            ->orderBy('tblper.step', 'DESC')
            ->get();
        //dd($data['getCentralList']);
        return view('hr.estab.promotionAlert', $data);
    }

    public function promotionShortlist(Request $request)
    {
        // Log::info($request->staffid);
        // dd($request->staffid);
        $check =  DB::table('tblstaffpromotion_shortlist')->where('ID', '=', $request->staffid)->count();
        if ($check == 0) {
            DB::table('tblstaffpromotion_shortlist')->where('ID', '=', $request->staffid)->insert([

                'staffid'        => $request->staffid,
                'year'           => $request->promotionYear, //date('Y'),
                'updated_at'     => date('Y-m-d'),
                'post_sought'    => $request->postionConsidered,
                'progress_stage'          => 1 // 10,

            ]);
        }
        response()->json("Successfull");
        //return back()->
    }

    public function promotionShortlistedStaff()
    {
        $user = DB::table('tblaction_stages')->where('userID', '=', Auth::user()->id)->first();

        if (empty($user)) {
            return back()->with('message', 'Your are not permitted to view that page');
        }

        //dd($user->action_stageID);
        $data['stage'] = $user;

        $data['shortlisted'] = DB::table('tblstaffpromotion_shortlist as s')
            ->join('tblper as p', 'p.ID', '=', 's.staffid')
            ->leftJoin('tblpromotion_comments as c', 'c.promotion_shortlist_id', '=', 's.id')
            ->leftJoin('users as u', 'u.id', '=', 'c.comment_by')
            ->where('s.status', 1)
            ->where('s.progress_stage', 1)
            ->where('s.approval_status', 0)
            ->select(
                's.*',
                'p.*'
            )
            ->distinct() // <- ensures one row per staff
            ->paginate(200);


        $data['stages'] = DB::table('tblleave_approval_stages')->where('is_adminprocess', '=', 1)->get();
        $data['staff'] = DB::table('tblstaffpromotion_shortlist')->where('status', '=', 1)->first();
        // dd($data['shortlisted']);

        return view('hr.estab.shortlistedForPromotion', $data);
    }


    public function promotionShortlistedStaffDA()
    {
        // $user = DB::table('tblaction_stages')->where('userID', '=', Auth::user()->id)->first();

        // if (empty($user)) {
        //     return back()->with('message', 'Your are not permitted to view that page');
        // }

        //dd($user->action_stageID);
        // $data['stage'] = $user;
        $data['shortlisted'] = DB::table('tblstaffpromotion_shortlist as s')
            ->join('tblper as p', 'p.ID', '=', 's.staffid')
            ->leftJoin('tblpromotion_comments as c', 'c.promotion_shortlist_id', '=', 's.id')
            ->leftJoin('users as u', 'u.id', '=', 'c.comment_by')
            ->where('s.status', 1)
            ->where('s.progress_stage', 1)
            ->where('s.approval_status', 1)
            ->select(
                's.*',
                'p.*'
            )
            ->distinct() // <- ensures one row per staff
            ->paginate(200);
        $data['stages'] = DB::table('tblleave_approval_stages')->where('is_adminprocess', '=', 1)->get();
        $data['staff'] = DB::table('tblstaffpromotion_shortlist')->where('status', '=', 1)->first();
        // dd($data['shortlisted']);

        return view('hr.estab.shortlistedForPromotionDA', $data);
    }


    public function promotionShortlistedStaffCR()
    {
        // $user = DB::table('tblaction_stages')->where('userID', '=', Auth::user()->id)->first();

        // if (empty($user)) {
        //     return back()->with('message', 'Your are not permitted to view that page');
        // }

        //dd($user->action_stageID);
        // $data['stage'] = $user;
        $data['shortlisted'] = DB::table('tblstaffpromotion_shortlist as s')
            ->join('tblper as p', 'p.ID', '=', 's.staffid')
            ->leftJoin('tblpromotion_comments as c', 'c.promotion_shortlist_id', '=', 's.id')
            ->leftJoin('users as u', 'u.id', '=', 'c.comment_by')
            ->where('s.status', 1)
            ->where('s.progress_stage', 1)
            ->where('s.approval_status', 2)
            ->select(
                's.*',
                'p.*'
            )
            ->distinct() // <- ensures one row per staff
            ->paginate(200);
        $data['stages'] = DB::table('tblleave_approval_stages')->where('is_adminprocess', '=', 1)->get();
        $data['staff'] = DB::table('tblstaffpromotion_shortlist')->where('status', '=', 1)->first();
        // dd($data['shortlisted']);

        return view('hr.estab.shortlistedForPromotionCR', $data);
    }


    public function getComments($staffID)
    {
        // Log::info($staffID);
        // Fetch all comments for the staff, with commenter name
        $comments = DB::table('tblpromotion_comments')
            ->join('users', 'users.id', '=', 'tblpromotion_comments.comment_by')
            ->where('tblpromotion_comments.promotion_shortlist_id', $staffID)
            ->select(
                'tblpromotion_comments.comment',
                'users.name as commented_by',
                'tblpromotion_comments.created_at'
            )
            ->orderBy('tblpromotion_comments.created_at', 'desc')
            ->get();

        return response()->json($comments);
    }

    public function processShortlisted(Request $request)
    {
        $user = DB::table('tblaction_stages')->where('userID', '=', Auth::user()->id)->first();

        if (empty($user)) {
            return back()->with('message', 'Your are not permitted to view that page');
        }

        DB::table('tblstaffpromotion_shortlist')->where('status', '=', 1)->update([
            'progress_stage'   => $request->referTo,
            'stage_from'       => $user->action_stageID,
        ]);

        if ($request->comment) {
            DB::table('tblpromotion_comments')->insert([
                'comment'        => $request->comment,
                'promotion_year' => $request->year,
                'comment_by'     => Auth::user()->id,
                'updated_at'     => date('Y-m-d'),
                'created_at'     => date('Y-m-d'),
            ]);
        }
        return back()->with('msg', 'successfull');
    }

    public function reversal(Request $request)
    {
        $user = DB::table('tblaction_stages')->where('userID', '=', Auth::user()->id)->first();
        DB::table('tblstaffpromotion_shortlist')->where('status', '=', 1)->update([
            'progress_stage'   => $user->action_stageID,

        ]);
        return back()->with('msg', 'Reversal Successfull');
    }

    public function promotionApproval(Request $request)
    {

        $user = DB::table('tblaction_stages')->where('userID', '=', Auth::user()->id)->first();

        // dd($request->all());

        if ($request->status == 1 && $request->progress_stage == 1 && $request->approval_status == 0) {
            DB::table('tblstaffpromotion_shortlist')->where('id', '=', $request->promotion_id)->update([
                'approval_status'   => 1, // await da approval
                // 'approved_by'       => Auth::user()->id,
            ]);
        }

        if ($request->status == 1 && $request->progress_stage == 1 && $request->approval_status == 1) {
            DB::table('tblstaffpromotion_shortlist')->where('id', '=', $request->promotion_id)->update([
                'approval_status'   => 2,  // await cr approval
                // 'approved_by'       => Auth::user()->id,
            ]);
        }

        if ($request->status == 1 && $request->progress_stage == 1 && $request->approval_status == 2) {
            DB::table('tblstaffpromotion_shortlist')->where('id', '=', $request->promotion_id)->update([
                'approval_status'   => 3,  // enter scores
                // 'approved_by'       => Auth::user()->id,
            ]);
        }

        // DB::table('tblstaffpromotion_shortlist')->where('id', '=', $request->promotion_id)->update([
        //     'approval_status'   => 1,
        //     'approved_by'       => Auth::user()->id,
        // ]);

        if ($request->comment) {
            DB::table('tblpromotion_comments')->insert([
                'promotion_shortlist_id'    => $request->promotion_id,
                'comment'                   => $request->comment,
                'promotion_year'            => date('Y'),
                'comment_by'                => Auth::user()->id,
                'updated_at'                => date('Y-m-d'),
                'created_at'     => date('Y-m-d'),
            ]);
        }

        return back()->with('msg', 'Successfull');
    }

    public function promotionApprovalReversal(Request $request)
    {
        // $user = DB::table('tblaction_stages')->where('userID', '=', Auth::user()->id)->first();
        // dd($request->all());
        $request->validate([
            'promotion_id' => 'required|exists:tblstaffpromotion_shortlist,id',
        ]);

        // Log::info('Promotion Reversal Input:', $request->all());
        // Log::info('Promotion ID:', ['id' => $request->promotion_id]);
        if ($request->status == 1 && $request->progress_stage == 1 && $request->approval_status == 1) {
            DB::table('tblstaffpromotion_shortlist')->where('id', '=', $request->promotion_id)->update([
                'approval_status'   => 0,
                'approved_by'       => 0,
            ]);
        }
        if ($request->status == 1 && $request->progress_stage == 1 && $request->approval_status == 2) {
            DB::table('tblstaffpromotion_shortlist')->where('id', '=', $request->promotion_id)->update([
                'approval_status'   => 1,
            ]);
        }

        if ($request->comment) {
            DB::table('tblpromotion_comments')->insert([
                'promotion_shortlist_id'    => $request->promotion_id,
                'comment'        => $request->comment,
                'promotion_year' => date('Y'),
                'comment_by'     => Auth::user()->id,
                'updated_at'     => date('Y-m-d'),
            ]);
        }
        return back()->with('msg', 'Successfull');
    }

    public function promotionScores()
    {
        $data['shortlisted'] = DB::table('tblstaffpromotion_shortlist')
            ->join('tblper', 'tblper.ID', '=', 'tblstaffpromotion_shortlist.staffid')
            ->join('tbldepartment', 'tbldepartment.id', '=', 'tblper.department')
            ->where('tblstaffpromotion_shortlist.status', '=', 1)
            ->where('tblstaffpromotion_shortlist.progress_stage', '=', 1)
            ->where('tblstaffpromotion_shortlist.approval_status', '=', 3)

            ->paginate(200);
        return view('hr.estab.enterPromotionScores', $data);
    }

    public function confirmPromotion(Request $request)
    {
        $staffPromotionShortlist = DB::table('tblstaffpromotion_shortlist')
            ->where('tblstaffpromotion_shortlist.status', '=', 1)
            ->where('tblstaffpromotion_shortlist.approval_status', '=', 3)
            ->where('tblstaffpromotion_shortlist.confirmed_promoted', '=', 0)
            ->where('tblstaffpromotion_shortlist.staffid', '=', $request->staffid)
            ->first();

        if (!$staffPromotionShortlist) {
            return back()->with('error', 'Record not found or already confirmed.');
        }

        // Update confirmation status
        DB::table('tblstaffpromotion_shortlist')
            ->where('id', $staffPromotionShortlist->id)
            ->update([
                'confirmed_promoted' => 1,
            ]);

        // added by Adams
        DB::table('tblforpromotion')->where('promotionID', $staffPromotionShortlist->id)->update(
            [
                'qualification' => $request->qualification,
                'remark' => $request->remark,
                'status' => 1
            ]
        );
        return back()->with('msg', 'Successfully Confirmed');
    }

    public function promotedStaff()
    {
        $data['shortlisted'] = DB::table('tblstaffpromotion_shortlist')
            ->leftjoin('tblper', 'tblper.ID', '=', 'tblstaffpromotion_shortlist.staffid')
            ->leftjoin('tbldepartment', 'tbldepartment.id', '=', 'tblper.department')
            ->leftjoin('tbldesignation', 'tblstaffpromotion_shortlist.post_sought', '=', 'tbldesignation.id')
            ->where('tblstaffpromotion_shortlist.status', '=', 1)
            ->where('tblstaffpromotion_shortlist.confirmed_promoted', '=', 1)
            ->select(
                '*',
                'tblstaffpromotion_shortlist.post_sought',
                'tblstaffpromotion_shortlist.id as promotionID',
                'tbldesignation.designation as designationName',
                'tblstaffpromotion_shortlist.staffid',
                'tblstaffpromotion_shortlist.confirmed_promoted'
            )
            ->paginate(200);
        return view('hr.estab.generatePromotionLetter', $data);
    }


    public function promotionListJunior(Request $request)
    {
        $date = $request->query('date'); // get from query string
        $data = $this->generatePromotionList($date, 'junior');
        // dd($data);
        return view('hr.estab.promotionListJunior', $data);
    }



    public function promotionListSenior(Request $request)
    {
        $date = $request->query('date'); // get from query string
        $data = $this->generatePromotionList($date, 'senior');
        return view('hr.estab.promotionListSenior', $data);
    }


    private function generatePromotionList($presentAppointmentDate, $type)
    {
        $newPromotionDate = [];
        $getAllUser = [];
        $newDateDueForIncrement = [];
        // dd($presentAppointmentDate);

        $monthRange     = ($presentAppointmentDate ? 0 : 1);
        $currentYear    = ($presentAppointmentDate ? date('Y', strtotime($presentAppointmentDate)) : date('Y'));
        $currentMonth   = ($presentAppointmentDate ? date('m', strtotime($presentAppointmentDate)) : date('m'));
        $currentDay     = ($presentAppointmentDate ? date('d', strtotime($presentAppointmentDate)) : date('d'));

        // Get users by staff type
        $query = DB::table('tblper')
            ->where('tblper.staff_status', 1)
            ->where('rank', 0)
            ->where('grade', '<', 17);

        if ($type === 'junior') {
            $query->whereBetween('grade', [1, 6]);
        } else {
            $query->whereBetween('grade', [7, 16]);
        }

        $getAllUserList = $query->get();

        if ($getAllUserList) {
            foreach ($getAllUserList as $item) {
                $appointYear  = date('Y', strtotime($item->date_present_appointment));
                $appointMonth = date('m', strtotime($item->date_present_appointment));
                $appointDay   = date('d', strtotime($item->date_present_appointment));

                // Determine promotion years based on grade
                if ($type === 'junior') {
                    $promotionYears = 2;
                } else {
                    if ($item->grade >= 6 && $item->grade <= 7) {
                        $promotionYears = 2;
                    } elseif ($item->grade >= 8 && $item->grade <= 14) {
                        $promotionYears = 3;
                    } elseif ($item->grade >= 15 && $item->grade <= 16) {
                        $promotionYears = 4;
                    } else {
                        continue;
                    }
                }

                $dueYear = $appointYear + $promotionYears;

                if ($dueYear < $currentYear) {
                    $getAllUser[] = $item->ID;
                    $newDateDueForIncrement[$item->ID] = "$appointDay-$appointMonth-$dueYear";
                    $this->updateUserRecord($item->ID, $isUserDue = 1);
                } elseif ($dueYear == $currentYear) {
                    if (($appointMonth <= ($currentMonth + $monthRange)) && ($appointDay <= $currentDay)) {
                        $getAllUser[] = $item->ID;
                        $newDateDueForIncrement[$item->ID] = "$appointDay-$appointMonth-$dueYear";
                        $this->updateUserRecord($item->ID, $isUserDue = 1);
                    }
                }
            }
        }

        $data['newPromotionDate'] = $newDateDueForIncrement;

        $data['getCentralList'] = DB::table('tblper')
            ->whereIn('ID', $getAllUser)
            ->orderBy('tblper.grade', 'Desc')
            ->orderBy('tblper.step', 'Desc')
            ->paginate(50);

        $data['designations'] = DB::table('tbldesignation')->get();
        $data['type'] = ucfirst($type);

        return $data;
    }


    public function promotionSearchJunior(Request $request)
    {
        $presentAppointmentDate = $request->presentAppointmentDate;
        return redirect()->route('promotion.junior', ['date' => $presentAppointmentDate]);
    }

    public function promotionSearchSenior(Request $request)
    {
        $presentAppointmentDate = $request->presentAppointmentDate;
        return redirect()->route('promotion.senior', ['date' => $presentAppointmentDate]);
    }
}
