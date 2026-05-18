<?php

namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests;

class VariationFormsController extends Controller
{
     public function letterOfApplication(Request $request)
    {
        return view('employmentForms/letterOfApplication');
    }

    
    public function applicationForm()
    {
        return view('employmentForms/applicationForm');
    }

    public function appointmentForm()
    {
        return view('employmentForms/appointmentForm');
    }
    
    public function refereeForm()
    {
        return view('employmentForms/refereeForm');
    }

    public function leaveForm()
    {
        return view('employmentForms/leaveForm');
    }
}
