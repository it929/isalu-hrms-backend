<?php
//
namespace App\Http\Controllers\payroll;

use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;

class StaffStatusController extends ParentController
{
    public $division;
    public $divisionID;

    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {

        $this->division    = session('division'); //$request->session()->get('division');
        $this->divisionID  = session('divisionID'); //$request->session()->get('divisionID');
                return $next($request);
        });
    }

    public function curDivision($userId)
    {
        $currentDivision = DB::table("users")
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'users.divisionID')
            ->where('users.id', '=', $userId)
            ->select('tbldivision.division', 'tbldivision.divisionID')
            ->first();
        return $currentDivision;
    }

    public function loadView(Request $request)
    {
        // Fetch the staff list
        $data['staffList'] = DB::table('tblper')->get();

        $data['division'] = DB::table('tbldivision')
            ->select('divisionID', 'division')
            ->orderBy('division', 'Asc')
            ->get();

        $data['selectedDivisionID'] = Auth::user()->divisionID;

        $data['userDivision'] = DB::table('tbldivision')
            ->where('divisionID', Auth::user()->divisionID)
            ->value('division');

        return view('payroll.staffStatus.staffStatus', $data);
    }


    public function findStaff(Request $request)
    {
        $this->validate($request, [
            'staffName' => 'required|numeric',
        ]);
        $fileNo = $request->input('staffName');

        $division = (Auth::user()->is_global == 1) ? Auth::user()->divisionID : $this->curDivision(Auth::user()->id)->divisionID;

        // \Log::info("Auth User Division ID: $division");
        $data   = DB::table('tblper')
            ->where('ID', '=', $fileNo)
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
            // ->where('divisionID', '=', $division)
            // ->select('fileNo', 'surname', 'first_name', 'othernames')
            ->first();
        return response()->json($data);
    }

    public function getStaffByDivision(Request $request)
    {
        $divisionID = $request->input('divisionID');

        $staffList = DB::table('tblper')
            ->where('divisionID', $divisionID)
            ->select('ID', 'surname', 'first_name', 'othernames')
            ->get();

        return response()->json($staffList);
    }

    public function update(Request $request)
    {
        // dd($request);
        $this->validate(
            $request,
            [
                'fileNo'      => 'required|numeric',
                'action'         => 'required|regex:/^[\pL\s\-]+$/u',
            ]
        );
        $fileNo              = trim($request['fileNo']);
        $action              = $request['action'];
        $statusPending       = 'pending';
        $statusRejected      = 'rejected';
        $statusApproved      = 'approved';
        $date                 = date("Y-m-d");

        $fromDiv = DB::table('tblper')->where('ID', '=', $fileNo)->first();

        if ($action == 'Update Staff Record') {
            $this->validate(
                $request,
                [
                    'staffStatus'    => 'required|regex:/^[\pL\s\-]+$/u',
                ]
            );
            $staffStatus         = trim($request['staffStatus']);

            // if ( ($staffStatus == "active service") || ($staffStatus == "contract service") ||  ($staffStatus == "maternity leave") )
            // 	$value = 1;

            // else
            // 	$value = 0;

            // DB::table('tblper')->where('fileNo','=', $fileNo)->update([
            // 	'status_value'    => $staffStatus,
            // 	'staff_status'    => $value
            // ]);

            // Allowed statuses
            $allowedStatuses = ["active service", "contract service", "maternity leave"];

            // If staffStatus is in the allowed list, set value = 1, else 0
            $value = in_array($staffStatus, $allowedStatuses) ? 1 : 0;

            // Update record
            DB::table('tblper')
                ->where('fileNo', $fileNo)
                ->update([
                    'status_value' => $staffStatus,
                    'staff_status' => $value,
                    'isClaimed'    => $value, // 👈 this updates isClaimed also
                    'isAdmin'    => $value
                ]);

            //$this->addLog('staff status updated with fileno: '.$fileNo);
            return back()->with('msg', 'Staff status updated successfully!');
        } else if ($action == 'Transfer Staff') {
            $this->validate(
                $request,
                [
                    'staffDivision'  => 'required|numeric',
                ]
            );
            $staffDivisionTo     = trim($request['staffDivision']);

            DB::table('tbltransfer')->insert(array(
                'fileNo'            => $fileNo,
                'date'              => $date,
                'divisionFrom'      => $fromDiv->divisionID,
                'divisionTo'        => $staffDivisionTo,
                'status'            => $statusPending
            ));
            // 	if(DB::table('tblper')->where('fileNo', $fileNo)->update(array(
            // 		'divisionID'        => $staffDivisionTo	)))
            // 	{
            $this->addLog('staff transferred with fileno: ' . $fileNo);

            return back()->with('msg', 'Staff was transferred successfully to another division!');
            // }
            // else{
            // 	return back()->with('msg', 'This Staff was already transferred to another division!');
            // }
        }
    }

    public function loadPending()
    {
        $statusPending    = 'pending';
        $statusRejected   = 'rejected';
        $statusApproved   = 'approved';
        $date              = date("Y-m-d");
        $data['curDivision'] = $this->curDivision(Auth::user()->id);
        $data['staffPending'] = DB::table('tbltransfer')
            ->where('tbltransfer.status', '=', $statusPending)
            ->where('tbltransfer.divisionTo', '=', $data['curDivision']->divisionID)
            ->select('tblper.fileNo',  'tblper.surname', 'tblper.first_name', 'tblper.othernames', 'tbldivision.division', 'tblper.rank', 'tbltransfer.date', 'tbltransfer.status')
            ->join('tblper', 'tblper.fileNo', '=', 'tbltransfer.fileNo')
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'tbltransfer.divisionFrom')
            ->orderby('tbltransfer.date', 'ASC')
            ->get();
        //total pending
        $totalstaff      = 0;
        foreach ($data['staffPending'] as $row) {
            $totalstaff   += 1;
        }
        $data['totalStaff']   = $totalstaff;
        $data['curDivision']  = $this->division;
        return view('payroll.staffStatus.report', $data);
    }

    public function getApprove(Request $request)
    {
        $approveButton = $request['approve'];
        $rejectButton = $request['reject'];
        $curDivisionID = $this->curDivision(Auth::user()->id)->divisionID;
        $array = $request['action'];
        $statusPending = 'pending';
        $statusRejected = 'rejected';
        $statusApproved = 'approved';
        $date = date("Y-m-d");

        if ($approveButton == 'Approve Staff' && $rejectButton == '') {
            if ($array) {
                $validIds = DB::table('tbltransfer')
                    ->whereIn('fileNo', $array)
                    ->where('status', $statusPending)
                    ->pluck('fileNo');
                if ($validIds->isEmpty()) {
                    return back()->with('msg', 'No pending transfers found for the selected staff.');
                }

                foreach ($validIds as $fileNo) {
                    DB::beginTransaction();
                    try {
                        $transfer = DB::table('tblper')
                            ->where('fileNo', $fileNo)
                            ->update([
                                'divisionID' => $curDivisionID,
                                'staff_status' => 1,
                                'status_value' => 'Aaaactive Sssservice'
                            ]);
                        \Log::info("Updated rows in tblper for $fileNo: $transfer");

                        $updatedTransfer = DB::table('tbltransfer')
                            ->where('fileNo', $fileNo)
                            ->where('status', $statusPending)
                            ->update(['status' => $statusApproved]);
                        \Log::info("Updated rows in tbltransfer for $fileNo: $updatedTransfer");

                        $this->addLog('Approve staff transfer with fileno: ' . $fileNo . ' to ' . $curDivisionID . ' division');
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollback();
                        \Log::error('Error approving staff transfer: ' . $e->getMessage());
                        return back()->with('error', 'An error occurred while approving staff.');
                    }
                }
                return back()->with('msg', 'Staff was/were successfully approved to this division!');
            } else {
                return back()->with('msg', 'You have not selected any staff to approve');
            }
        } elseif ($rejectButton == 'Reject Staff' && $approveButton == '') {
            if ($array) {
                $validIds = DB::table('tbltransfer')
                    ->whereIn('fileNo', $array)
                    ->where('status', $statusPending)
                    ->pluck('fileNo');

                if ($validIds->isEmpty()) {
                    return back()->with('msg', 'No pending transfers found for the selected staff.');
                }

                foreach ($validIds as $fileNo) {
                    DB::beginTransaction();
                    try {
                        $updatedTransfer = DB::table('tbltransfer')
                            ->where('fileNo', $fileNo)
                            ->where('status', $statusPending)
                            ->update(['status' => $statusRejected, 'date' => $date]);
                        \Log::info("Updated rows in tbltransfer for $fileNo: $updatedTransfer");

                        $this->addLog('Staff rejected with fileno: ' . $fileNo);
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollback();
                        \Log::error('Error rejecting staff transfer: ' . $e->getMessage());
                        return back()->with('error', 'An error occurred while rejecting staff.');
                    }
                }
                return back()->with('msg', 'Staff was/were successfully rejected!');
            } else {
                return back()->with('msg', 'You have not selected any staff to reject');
            }
        }
    }
}
