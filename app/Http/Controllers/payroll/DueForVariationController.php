<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use File;
use App\Http\Requests;
use Session;
use DB;

class DueForVariationController extends ParentController
{

    // public function __construct(Request $request)
    // {
    //     $this->division = $request->session()->get('division');
    //     $this->divisionID = $request->session()->get('divisionID');
    //     Session::put('this_division', $this->division);
    // }

    public function staffDueForVariation(Request $request)
    {
        if (isset($_POST['retrieve'])) {
            $this->validate($request, [
                'date' => 'required',
            ]);
            // $data['records'] = DB::table('tblper')
            //     ->where('incremental_date', '<', $request['date'])
            //     ->where('tblper.staff_status', '=', 1)
            //     // ->where('tblper.employee_type', '<>', 'CONSOLIDATED')
            //     // ->get();
            //     ->paginate(50);
            $data['records'] = DB::table('tblper')
                ->leftJoin('tblarrears', 'tblper.id', '=', 'tblarrears.staffID') // Join arrears table
                ->where('tblper.incremental_date', '<', $request['date'])
                ->where('tblper.staff_status', '=', 1)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('tblarrears')
                        ->whereRaw('tblarrears.staffID = tblper.id')
                        ->whereRaw('tblarrears.dueDate = tblper.incremental_date'); // Exclude matching dueDate
                })
                ->paginate(50);
            return view('Variation.staffs_due_for_variation', $data);
        } else {
            $data['records'] = new LengthAwarePaginator([], 0, 50, null, ['path' => url('staffs-due-for-variation')]);
            return view('Variation.staffs_due_for_variation', $data);
        }
    }
}
