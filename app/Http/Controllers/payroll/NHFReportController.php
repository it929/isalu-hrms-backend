<?php

namespace App\Http\Controllers\payroll;



use Illuminate\Http\Request;
use App\Helpers\FileUploadHelper;
use App\Http\Requests;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class NHFReportController extends ParentController
{

    public function index()
    {
        $data['CourtInfo'] = $this->CourtInfo();
        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }
        $data['courts'] =  DB::table('tbl_court')->get();
        return view('nhfReport.nhfReportForm', $data);
    }

    public function retrieve(Request $request)
    {
        $data['nhf'] = DB::table('tblpayment_consolidated')
            ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
            ->where('tblpayment_consolidated.month', '=', $request['month'])
            ->where('tblpayment_consolidated.year', '=', $request['year'])
            ->where('tblpayment_consolidated.rank', '!=', 2)
            ->where('tblpayment_consolidated.NHF', '!=', 0)
            ->orderBy('tblpayment_consolidated.NHF', 'DESC')
            //->orderBy('tblpayment_consolidated.grade','DESC')
            ->get();
        $data['year'] = $request['year'];
        $data['month'] = $request['month'];

        $data['totalSum'] = DB::table('tblpayment_consolidated')
            ->where('tblpayment_consolidated.month', '=', $request['month'])
            ->where('tblpayment_consolidated.year', '=', $request['year'])
            ->where('tblpayment_consolidated.rank', '!=', 2)
            ->where('tblpayment_consolidated.NHF', '!=', 0)
            ->sum('NHF');
        return view('payroll.nhfReport.detail', $data);
    }

    public function nhfReportIndex()
    {
        $data['authUser'] = Auth::user()->divisionID;
        $data['divisions'] = DB::table('tbldivision')
            ->get();
        $data['getauthUser'] = DB::table('tbldivision')
            ->where('divisionID', '=', $data['authUser'])
            ->get();
        // dd($data);

        return view('payroll.nhfReport.nhfReportIndex', $data);
    }
    public function nhfRemittanceAttachment()
    {
        $attachments = DB::table('remittance_attachment')
                        ->orderBy('year', 'desc')
                        ->orderBy('month', 'desc')
                        ->get();

        // return view('your-view-name', compact('attachments'));
        return view('payroll.nhfReport.nhfRemittanceAttachmentIndex', compact('attachments'));
    }

    
public function nhfRemittanceAttachmentUpload(Request $request)
{
    // Validate the request
    $request->validate([
        'month' => 'required|string|max:256',
        'year' => 'required|string|max:256',
        'attachment' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:100'
    ]);

    try {
        // Handle file upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            
            // Check if attachment already exists for this month and year
            $existingAttachment = DB::table('remittance_attachment')
                ->where('month', $request->month)
                ->where('year', $request->year)
                ->first();

            // Generate custom filename using month_year_timestamp format
            $filesParam = strtoupper($request->month) . "_" . $request->year . "_" . time() . '.' . $file->getClientOriginalExtension();

            // Use helper (automatically stores to local or S3)
            $fileUrl = FileUploadHelper::upload($file, 'attachments', $filesParam);
            Log::info($fileUrl);

            if ($existingAttachment) {
                // Update existing record
                DB::table('remittance_attachment')
                    ->where('month', $request->month)
                    ->where('year', $request->year)
                    ->update([
                        'attachment' => $fileUrl,
                        'updated_at' => now(),
                    ]);

                return response()->json(['success' => 'File updated successfully!']);
            } else {
                // Insert new record
                DB::table('remittance_attachment')->insert([
                    'month' => $request->month,
                    'year' => $request->year,
                    'attachment' => $fileUrl,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return response()->json(['success' => 'File uploaded successfully!']);
            }
        }
        
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to upload attachment: ' . $e->getMessage()], 500);
    }
}

public function nhfRemittanceAttachmentDownload($id)
{
    try {
        $attachment = DB::table('remittance_attachment')->where('id', $id)->first();
        
        if (!$attachment) {
            return redirect()->back()->with('error', 'File not found in database.');
        }

        // Check if file exists in public directory
        $filePath = base_path('../public/' . $attachment->attachment);
        
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found on server: ' . $attachment->attachment);
        }

        // Get the original filename for download
        $originalName = basename($attachment->attachment);
        
        // Get file information
        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath);

        // Set headers for download
        $headers = [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $originalName . '"',
            'Content-Length' => $fileSize,
        ];

        // Return download response
        return response()->download($filePath, $originalName, $headers);
        
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Download failed: ' . $e->getMessage());
    }
}


public function nhfRemittanceAttachmentDelete($id)
{
    try {
        $attachment = DB::table('remittance_attachment')->where('id', $id)->first();
        
        if (!$attachment) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // Delete file from public directory
        $filePath = base_path('../public/' . $attachment->attachment);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete record from database
        DB::table('remittance_attachment')->where('id', $id)->delete();

        return response()->json(['success' => 'File deleted successfully!']);
        
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to delete file: ' . $e->getMessage()], 500);
    }
}

 public function nhfRemittanceAttachmentView($id)
{
    try {
        $attachment = DB::table('remittance_attachment')->where('id', $id)->first();
        
        if (!$attachment) {
            abort(404, 'File not found');
        }

        // Check if file exists in public directory
        $filePath = base_path('../public/' . $attachment->attachment);
        
        if (!file_exists($filePath)) {
            abort(404, 'File not found on server');
        }

        $mimeType = mime_content_type($filePath);

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($attachment->attachment) . '"'
        ]);
        
    } catch (\Exception $e) {
        abort(404, 'Error viewing file: ' . $e->getMessage());
    }
}

   public function nhfRemittanceReceipts()
    {
        $attachments = DB::table('remittance_attachment')
                        ->orderBy('year', 'desc')
                        ->orderBy('month', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('payroll.nhfReport.remittance-receipts', compact('attachments'));
    }

    public function nhfJusticeReportIndex()
    {
        $data['authUser'] = Auth::user()->divisionID;
        $data['divisions'] = DB::table('tbldivision')
            ->get();
        $data['getauthUser'] = DB::table('tbldivision')
            ->where('divisionID', '=', $data['authUser'])
            ->get();
        // dd($data);

        return view('payroll.nhfReport.justiceNhfReport', $data);
    }

    public function nhfReportDetails(Request $request)
    {
        Session::put('month', $request->month);
        Session::put('year', $request->year);
        if (Auth::user()->is_global == 1) {
            $month = $request['month'];
            $year = $request['year'];
            $data['month'] = $month;
            $data['year'] = $year;
            if ($request['div'] != '') {
                $data['nhf'] = DB::table('tblpayment_consolidated')
                    ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                    //->join('basicsalaryconsolidated','basicsalaryconsolidated.employee_type','=','tblpayment_consolidated.employment_type')
                    ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
                    ->where('tblpayment_consolidated.month', '=', $request['month'])
                    ->where('tblpayment_consolidated.year', '=', $request['year'])
                    ->where('tblpayment_consolidated.divisionID', '=', $request['div'])
                    ->where('tblpayment_consolidated.rank', '!=', 2)
                    ->where('tblpayment_consolidated.NHF', '!=', 0)
                    ->orderBy('tblpayment_consolidated.rank', 'DESC')
                    ->orderBy('tblpayment_consolidated.grade', 'DESC')
                    ->orderBy('tblpayment_consolidated.step', 'DESC')

                    ->select('*', 'tblbanklist.bank as bankname')
                    ->get();
                $data['division'] = DB::table('tbldivision')
                    ->where('divisionID', '=', $request['div'])
                    ->get();
            } else {
                $data['nhf'] = DB::table('tblpayment_consolidated')
                    ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                    //->join('basicsalaryconsolidated','basicsalaryconsolidated.employee_type','=','tblpayment_consolidated.employment_type')
                    ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
                    ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblpayment_consolidated.divisionID')
                    ->where('tblpayment_consolidated.month', '=', $request['month'])
                    ->where('tblpayment_consolidated.year', '=', $request['year'])
                    ->where('tblpayment_consolidated.rank', '!=', 2)
                    ->where('tblpayment_consolidated.NHF', '!=', 0)
                    ->orderBy('tblpayment_consolidated.divisionID', 'DESC')
                    ->orderBy('tblpayment_consolidated.rank', 'DESC')
                    ->orderBy('tblpayment_consolidated.grade', 'DESC')
                    ->orderBy('tblpayment_consolidated.step', 'DESC')

                    ->select('*', 'tblbanklist.bank as bankname')
                    ->get();
                $data['division'] = '';
            }
        } else {
            $user = Auth::user()->divisionID;
            $month = $request['month'];
            $year = $request['year'];
            $data['month'] = $month;
            $data['year'] = $year;
            $data['nhf'] = DB::table('tblpayment_consolidated')
                ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                //->join('basicsalaryconsolidated','basicsalaryconsolidated.employee_type','=','tblpayment_consolidated.employment_type')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
                ->where('tblpayment_consolidated.month', '=', $request['month'])
                ->where('tblpayment_consolidated.year', '=', $request['year'])
                ->where('tblpayment_consolidated.rank', '!=', 2)
                ->where('tblpayment_consolidated.divisionID', $user)
                ->where('tblpayment_consolidated.NHF', '!=', 0)
                ->orderBy('tblpayment_consolidated.rank', 'DESC')
                ->orderBy('tblpayment_consolidated.grade', 'DESC')
                ->orderBy('tblpayment_consolidated.step', 'DESC')

                ->select('*', 'tblbanklist.bank as bankname')
                ->get();
            $data['division'] = DB::table('tbldivision')
                ->where('divisionID', '=', $user)
                ->get();
        }

        //  // Fetch attachments for the current month and year
        $data['attachments'] = DB::table('remittance_attachment')
            ->where('month', '=', $request['month'])
            ->where('year', '=', $request['year'])
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('payroll.nhfReport.detailReport', $data);
    }

    public function justiceNhfReportDetails(Request $request)
    {
        Session::put('month', $request->month);
        Session::put('year', $request->year);
        if (Auth::user()->is_global == 1) {
            $month = $request['month'];
            $year = $request['year'];
            $data['month'] = $month;
            $data['year'] = $year;
            if ($request['div'] != '') {
                $data['nhf'] = DB::table('tblpayment_consolidated')
                    ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                    ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
                    ->where('tblpayment_consolidated.month', '=', $request['month'])
                    ->where('tblpayment_consolidated.year', '=', $request['year'])
                    ->where('tblpayment_consolidated.divisionID', '=', $request['div'])
                    ->where('tblpayment_consolidated.rank', '=', 2)
                    ->where('tblpayment_consolidated.NHF', '!=', 0)
                    ->orderBy('tblpayment_consolidated.rank', 'DESC')
                    ->orderBy('tblpayment_consolidated.grade', 'DESC')
                    ->orderBy('tblpayment_consolidated.step', 'DESC')

                    ->select('*', 'tblbanklist.bank as bankname')
                    ->get();
                $data['division'] = DB::table('tbldivision')
                    ->where('divisionID', '=', $request['div'])
                    ->get();
            } else {
                $data['nhf'] = DB::table('tblpayment_consolidated')
                    ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                    ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
                    ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblpayment_consolidated.divisionID')
                    ->where('tblpayment_consolidated.month', '=', $request['month'])
                    ->where('tblpayment_consolidated.year', '=', $request['year'])
                    ->where('tblpayment_consolidated.rank', '=', 2)
                    ->where('tblpayment_consolidated.NHF', '!=', 0)
                    ->orderBy('tblpayment_consolidated.divisionID', 'DESC')
                    ->orderBy('tblpayment_consolidated.rank', 'DESC')
                    ->orderBy('tblpayment_consolidated.grade', 'DESC')
                    ->orderBy('tblpayment_consolidated.step', 'DESC')
                    ->select('*', 'tblbanklist.bank as bankname')
                    ->get();
                $data['division'] = '';
            }
        } else {
            $user = Auth::user()->divisionID;
            $month = $request['month'];
            $year = $request['year'];
            $data['month'] = $month;
            $data['year'] = $year;
            $data['nhf'] = DB::table('tblpayment_consolidated')
                ->join('tblper', 'tblper.ID', '=', 'tblpayment_consolidated.staffid')
                ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblpayment_consolidated.bank')
                ->where('tblpayment_consolidated.month', '=', $request['month'])
                ->where('tblpayment_consolidated.year', '=', $request['year'])
                ->where('tblpayment_consolidated.rank', '=', 2)
                ->where('tblpayment_consolidated.divisionID', $user)
                ->where('tblpayment_consolidated.NHF', '!=', 0)
                ->orderBy('tblpayment_consolidated.rank', 'DESC')
                ->orderBy('tblpayment_consolidated.grade', 'DESC')
                ->orderBy('tblpayment_consolidated.step', 'DESC')

                ->select('*', 'tblbanklist.bank as bankname')
                ->get();
            $data['division'] = DB::table('tbldivision')
                ->where('divisionID', '=', $user)
                ->get();
        }


        return view('payroll.nhfReport.justiceDetails', $data);
    }

    public function updateNHFnumber($ID, $nhf)
    {


        $updatenfhNo = DB::table('tblper')->where('ID', $ID)->update([
            'nhfNo' => $nhf
        ]);
        if ($updatenfhNo) {
            return redirect()->back()->with('message', 'NHF number updated successfully');
        } else {
            return redirect()->back()->with('message', 'no record updated');
        }
    }
}
