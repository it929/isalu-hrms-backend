<?php

namespace App\Http\Controllers\payroll;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\payroll\BaseFileNumberSearchController;
use Illuminate\Support\Facades\Log;

class FileNumberController extends BaseFileNumberSearchController
{
    /**
     * Show the file number search form
     */
    public function showSearchForm()
    {
        return view('payroll.fileNumber.file-number-search');
    }

    /**
     * Search for file number and send OTP
     */
    public function searchFileNumber(Request $request)
    {
        $request->validate([
            'fileNumber' => 'required'
        ]);

        $fileNumber = $request->input('fileNumber');

        // Search for the person by file number directly from tblper
        $person = DB::table('tblper')
                    ->where('tblper.fileNo', $fileNumber)
                    ->first();

        if (!$person) {
            return redirect()->back()->withErrors(['fileNumber' => 'File number not found in our records.']);
        }

        // Check if email exists in tblper (try email first, then alternate_email)
        $email = $person->email ?? $person->alternate_email ?? null;

        if (!$email) {
            return redirect()->back()->withErrors(['fileNumber' => 'No email address found for this file number. Please contact administrator.']);
        }

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpExpiresAt = now()->addMinutes(10);

        // Store OTP and user details in session
        Session::put('otp_verification', [
            'user_id' => $person->UserID,
            'person_id' => $person->ID,
            'file_number' => $fileNumber,
            'otp' => $otp,
            'otp_expires_at' => $otpExpiresAt,
            'email' => $email
        ]);

        // Send OTP email
        try {
            $emailSent = $this->sendOtpEmail($email, $otp, $person);
            
            if ($emailSent) {
                return redirect()->route('file.number.verify.otp.form')
                    ->with('success', 'A 6-digit OTP has been sent to your registered email address.');
            } else {
                Log::error('Email sending returned false for file number: ' . $fileNumber);
                return redirect()->back()->withErrors(['fileNumber' => 'Failed to send OTP. Please check your email configuration.']);
            }
                
        } catch (\Exception $e) {
            Log::error('OTP sending failed: ' . $e->getMessage());
            return redirect()->back()->withErrors(['fileNumber' => 'Failed to send OTP: ' . $e->getMessage()]);
        }
    }

    /**
     * Send OTP email
     */
    private function sendOtpEmail($email, $otp, $person)
    {
        try {
            $name = $person->first_name . ' ' . $person->surname;
            
            Mail::send('emails.otp', [
                'name' => $name,
                'otp' => $otp,
                'fileNumber' => $person->fileNo
            ], function ($message) use ($email, $name) {
                $message->to($email)
                        ->subject('Your OTP Code - File Number Verification');
            });

            return count(Mail::failures()) === 0;
            
        } catch (\Exception $e) {
            Log::error('Email sending error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Show OTP verification form
     */
    public function showOtpVerificationForm()
    {
        if (!Session::has('otp_verification')) {
            return redirect()->route('file.number.search')->withErrors(['otp' => 'Session expired. Please search again.']);
        }

        return view('payroll.fileNumber.verify-otp');
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6'
        ]);

        $otpData = Session::get('otp_verification');

        if (!$otpData) {
            return redirect()->route('file.number.search')->withErrors(['otp' => 'Session expired. Please search again.']);
        }

        // Check if OTP has expired
        if (now()->gt($otpData['otp_expires_at'])) {
            Session::forget('otp_verification');
            return redirect()->route('file.number.search')->withErrors(['otp' => 'OTP has expired. Please request a new one.']);
        }

        // Verify OTP
        if ($request->input('otp') !== $otpData['otp']) {
            return redirect()->back()->withErrors(['otp' => 'Invalid OTP code. Please try again.']);
        }

        // OTP verified successfully - get person details directly from tblper
        $person = DB::table('tblper')
                    ->where('tblper.ID', $otpData['person_id'])
                    ->first();

        if (!$person) {
            return redirect()->route('file.number.search')->withErrors(['otp' => 'User not found. Please try again.']);
        }

        // Get email from tblper (try email first, then alternate_email)
        $email = $person->email ?? $person->alternate_email ?? null;

        // Clear OTP session
        Session::forget('otp_verification');

        // Store verified user in session
        Session::put('verified_user', [
            'person_id' => $person->ID,
            'user_id' => $person->UserID,
            'file_number' => $person->fileNo,
            'name' => $person->first_name . ' ' . $person->surname,
            'title' => $person->title,
            'designation' => $person->Designation,
            'email' => $email,
            'verified_at' => now()
        ]);

        // Redirect to payslip selection
        return redirect()->route('payslip.selection')->with('success', 'Verification successful! Please select month and year to view your payslip.');
    }

    /**
     * Resend OTP
     */
    public function resendOtp()
    {
        if (!Session::has('otp_verification')) {
            return redirect()->route('file.number.search')->withErrors(['otp' => 'Session expired. Please search again.']);
        }

        $otpData = Session::get('otp_verification');

        // Generate new OTP
        $newOtp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpExpiresAt = now()->addMinutes(10);

        // Update session with new OTP
        $otpData['otp'] = $newOtp;
        $otpData['otp_expires_at'] = $otpExpiresAt;
        Session::put('otp_verification', $otpData);

        // Get person details from tblper for email
        $person = DB::table('tblper')
                    ->where('tblper.ID', $otpData['person_id'])
                    ->first();

        // Resend email
        try {
            $this->sendOtpEmail($otpData['email'], $newOtp, $person);
            
            return redirect()->route('file.number.verify.otp.form')
                ->with('success', 'A new 6-digit OTP has been sent to your email address.');
                
        } catch (\Exception $e) {
            Log::error('OTP resend failed: ' . $e->getMessage());
            return redirect()->back()->withErrors(['otp' => 'Failed to resend OTP. Please try again.']);
        }
    }

    /**
     * Show payslip selection form
     */
    public function showPayslipSelection()
    {
        // Only check for our custom session, not Laravel auth
        if (!Session::has('verified_user')) {
            return redirect()->route('file.number.search')->withErrors(['auth' => 'Please verify your file number first.']);
        }

        $user = Session::get('verified_user');
        
        return view('payroll.fileNumber.payslip-selection', compact('user'));
    }

    /**
     * Generate payslip
     */
    public function generatePayslip(Request $request)
    {
        if (!Session::has('verified_user')) {
            return redirect()->route('file.number.search')->withErrors(['auth' => 'Please verify your file number first.']);
        }

        $user = Session::get('verified_user');

        $request->validate([
            'month' => 'required|string',
            'year' => 'required'
        ]);

        $month = $request->input('month');
        $year = $request->input('year');
        
        $fileNoToUse = $user['file_number'];
        $court = 1;

        try {
            // 1. First, verify the person exists in tblper
            $personData = DB::table('tblper')->where('fileNo', $fileNoToUse)->first();
            
            if (!$personData) {
                return redirect()->route('payslip.selection')
                    ->with('error', 'Staff information not found in system. Please contact administrator.');
            }

            // 2. Try multiple search combinations with the SELECTED MONTH AND YEAR
            $searchCombinations = [
                ['type' => 'file_number_exact', 'staffid' => $fileNoToUse, 'year' => $year, 'month' => $month, 'court' => $court],
                ['type' => 'file_number_uppercase', 'staffid' => $fileNoToUse, 'year' => $year, 'month' => strtoupper($month), 'court' => $court],
                ['type' => 'file_number_lowercase', 'staffid' => $fileNoToUse, 'year' => $year, 'month' => strtolower($month), 'court' => $court],
                ['type' => 'file_number_capitalized', 'staffid' => $fileNoToUse, 'year' => $year, 'month' => ucfirst(strtolower($month)), 'court' => $court],
                ['type' => 'person_id_exact', 'staffid' => $personData->ID, 'year' => $year, 'month' => $month, 'court' => $court],
                ['type' => 'person_id_uppercase', 'staffid' => $personData->ID, 'year' => $year, 'month' => strtoupper($month), 'court' => $court],
                ['type' => 'person_id_lowercase', 'staffid' => $personData->ID, 'year' => $year, 'month' => strtolower($month), 'court' => $court],
                ['type' => 'person_id_capitalized', 'staffid' => $personData->ID, 'year' => $year, 'month' => ucfirst(strtolower($month)), 'court' => $court],
                ['type' => 'file_number_no_court', 'staffid' => $fileNoToUse, 'year' => $year, 'month' => $month, 'court' => null],
                ['type' => 'person_id_no_court', 'staffid' => $personData->ID, 'year' => $year, 'month' => $month, 'court' => null],
            ];

            $payslipData = null;
            $foundWith = null;

            foreach ($searchCombinations as $combination) {
                $query = DB::table('tblpayment_consolidated')
                    ->where('staffid', '=', $combination['staffid'])
                    ->where('year', '=', $combination['year'])
                    ->where('month', '=', $combination['month']);

                if ($combination['court'] !== null) {
                    $query->where('courtID', '=', $combination['court']);
                }

                $result = $query->first();

                if ($result) {
                    $payslipData = $result;
                    $foundWith = $combination['type'];
                    break;
                }
            }

            // 3. If no payslip found for selected month/year, show available options
            if (!$payslipData) {
                // Check what payslips ARE available for this user
                $availablePayslips = DB::table('tblpayment_consolidated')
                    ->where(function($query) use ($fileNoToUse, $personData) {
                        $query->where('staffid', '=', $fileNoToUse)
                              ->orWhere('staffid', '=', $personData->ID);
                    })
                    ->select('month', 'year')
                    ->orderBy('year', 'desc')
                    ->orderBy('month', 'desc')
                    ->get();

                $message = "No payslip found for $month $year.";

                if ($availablePayslips->count() > 0) {
                    $availableList = $availablePayslips->map(function($item) {
                        return $item->month . ' ' . $item->year;
                    })->unique()->implode(', ');
                    
                    $message .= " Available payslips: $availableList";
                } else {
                    $message .= " No payslips available for your file number.";
                }

                // Use withInput to preserve the form selections
                return redirect()->route('payslip.selection')
                    ->with('message', $message)
                    ->withInput();
            }

            // 4. Verify that the found payslip matches the requested month/year
            $foundMonth = $payslipData->month;
            $foundYear = $payslipData->year;

            // Rest of your existing code for processing and displaying payslip...
            $mergedData = (object) array_merge((array)$payslipData, (array)$personData);
            $mergedData->staffGrade = $payslipData->grade ?? null;
            $mergedData->staffStep = $payslipData->step ?? null;
            
            $data['reports'] = $mergedData;

            // Get bank data
            if (isset($payslipData->bank) && $payslipData->bank) {
                $data['bank'] = DB::table('tblbanklist')->where('bankID', '=', $payslipData->bank)->first();
            } else {
                $data['bank'] = (object)['bank' => 'N/A'];
            }

            // Get other deductions - use the ACTUAL month and year from the payslip
            $data['other_deduct'] = DB::table('tblotherEarningDeduction')
                ->join('tblcvSetup', 'tblcvSetup.ID', '=', 'tblotherEarningDeduction.CVID')
                ->where('tblotherEarningDeduction.particularID', '=', 2)
                ->where('tblotherEarningDeduction.staffid', '=', $payslipData->staffid)
                ->where('tblotherEarningDeduction.year', '=', $foundYear)
                ->where('tblotherEarningDeduction.month', '=', $foundMonth)
                ->get();

            // Get other earnings
            $data['other_earn'] = DB::table('tblotherEarningDeduction')
                ->join('tblcvSetup', 'tblcvSetup.ID', '=', 'tblotherEarningDeduction.CVID')
                ->where('tblotherEarningDeduction.particularID', '=', 1)
                ->where('tblotherEarningDeduction.year', '=', $foundYear)
                ->where('tblotherEarningDeduction.month', '=', $foundMonth)
                ->where('tblotherEarningDeduction.staffid', '=', $payslipData->staffid)
                ->get();

            // Add user data for the view
            $data['user'] = $user;
            $data['month'] = $month;
            $data['year'] = $year;

            // Get court and division info
            $courtName = DB::table('tbl_court')->where('id', '=', $court)->first();
            $data['courtName'] = $courtName ? $courtName->court_name : 'ISALU HOSPITAL LIMITED';
            
            $divisionData = DB::table('tbldivision')->where('divisionID', '=', $user['user_id'])->first();
            $data['division'] = $divisionData;

            // Return the view
            return $this->view('payroll.fileNumber.payslip-summary', $data);

        } catch (\Exception $e) {
            return redirect()->route('payslip.selection')
                ->with('message', 'An error occurred while generating the payslip. Please try again.')
                ->withInput();
        }
    }
}