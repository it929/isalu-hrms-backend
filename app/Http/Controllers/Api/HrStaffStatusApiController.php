<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class HrStaffStatusApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * Resolve the current user context from the X-User-Id header.
     */
    

    /**
     * Get division of user by user ID.
     */
    private function curDivision($userId)
    {
        return DB::table("users")
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'users.divisionID')
            ->where('users.id', '=', $userId)
            ->select('tbldivision.division', 'tbldivision.divisionID')
            ->first();
    }

    /**
     * Fetch staff status view form metadata.
     */
    public function getStaffStatusData(Request $request)
    {
        $userContext = $this->getUserContext($request);
        if (!$userContext) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        try {
            $staffList = DB::table('tblper')
                ->select('ID', 'fileNo', 'surname', 'first_name', 'othernames')
                ->orderBy('surname', 'asc')
                ->get();

            $divisions = DB::table('tbldivision')
                ->select('divisionID', 'division')
                ->orderBy('division', 'Asc')
                ->get();

            $curDiv = $this->curDivision($userContext['userId']);

            return response()->json([
                'status'            => 'success',
                'staffList'         => $staffList,
                'divisions'         => $divisions,
                'curDivision'       => $curDiv ? $curDiv->division : null,
                'curDivisionID'     => $curDiv ? $curDiv->divisionID : null,
                'isSuperAdmin'      => $userContext['isSuperAdmin'],
                'isAdminStaff'      => $userContext['isAdminStaff'],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve metadata: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Search staff member details.
     */
    public function findStaff(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staffName' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $staffId = $request->input('staffName');

            $staff = DB::table('tblper')
                ->where('tblper.ID', '=', $staffId)
                ->leftJoin('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->select(
                    'tblper.ID',
                    'tblper.fileNo',
                    'tblper.surname',
                    'tblper.first_name',
                    'tblper.othernames',
                    'tblper.staff_status',
                    'tblper.status_value',
                    'tblper.divisionID',
                    'tbldivision.division as divisionName'
                )
                ->first();

            if (!$staff) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Staff record not found.',
                ], 442);
            }

            return response()->json([
                'status' => 'success',
                'data'   => $staff,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to find staff: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Get staff by division.
     */
    public function getStaffByDivision(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'divisionID' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $staffList = DB::table('tblper')
                ->where('divisionID', $request->input('divisionID'))
                ->select('ID', 'surname', 'first_name', 'othernames')
                ->orderBy('surname', 'asc')
                ->get();

            return response()->json([
                'status'    => 'success',
                'staffList' => $staffList,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to load staff: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Update Status or Transfer Staff.
     */
    public function updateStatusOrTransfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fileNo' => 'required',
            'action' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $fileNo = trim($request->input('fileNo'));
            $action = $request->input('action');
            $date   = date("Y-m-d");

            $employee = DB::table('tblper')
                ->where('fileNo', '=', $fileNo)
                ->orWhere('ID', '=', $fileNo)
                ->first();

            if (!$employee) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Staff record not found.',
                ], 442);
            }

            if ($action === 'Update Staff Record') {
                $statusValidator = Validator::make($request->all(), [
                    'staffStatus' => 'required|string',
                ]);

                if ($statusValidator->fails()) {
                    return response()->json([
                        'status'  => 'error',
                        'errors'  => $statusValidator->errors(),
                    ], 422);
                }

                $staffStatus = trim($request->input('staffStatus'));
                $allowedStatuses = ["active service", "contract service", "maternity leave"];
                $value = in_array(strtolower($staffStatus), $allowedStatuses) ? 1 : 0;

                DB::table('tblper')
                    ->where('fileNo', $employee->fileNo)
                    ->update([
                        'status_value' => $staffStatus,
                        'staff_status' => $value,
                        'isClaimed'    => $value,
                        'isAdmin'      => $value
                    ]);

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Staff status updated successfully!',
                ]);
            } else if ($action === 'Transfer Staff') {
                $transferValidator = Validator::make($request->all(), [
                    'staffDivision' => 'required|numeric',
                ]);

                if ($transferValidator->fails()) {
                    return response()->json([
                        'status'  => 'error',
                        'errors'  => $transferValidator->errors(),
                    ], 422);
                }

                $staffDivisionTo = trim($request->input('staffDivision'));

                DB::table('tbltransfer')->insert([
                    'fileNo'       => $employee->fileNo,
                    'date'         => $date,
                    'divisionFrom' => $employee->divisionID,
                    'divisionTo'   => $staffDivisionTo,
                    'status'       => 'pending'
                ]);

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Staff transfer initiated successfully! Pending approval from destination division.',
                ]);
            }

            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid action type specified.',
            ], 422);

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to process request: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Pending Transfers.
     */
    public function getPendingTransfers(Request $request)
    {
        $userContext = $this->getUserContext($request);
        if (!$userContext) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        try {
            $curDiv = $this->curDivision($userContext['userId']);
            if (!$curDiv) {
                return response()->json([
                    'status'       => 'success',
                    'staffPending' => [],
                    'totalStaff'   => 0,
                    'curDivision'  => 'Unknown'
                ]);
            }

            $staffPending = DB::table('tbltransfer')
                ->where('tbltransfer.status', '=', 'pending')
                ->where('tbltransfer.divisionTo', '=', $curDiv->divisionID)
                ->join('tblper', 'tblper.fileNo', '=', 'tbltransfer.fileNo')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tbltransfer.divisionFrom')
                ->select(
                    'tblper.fileNo',
                    'tblper.surname',
                    'tblper.first_name',
                    'tblper.othernames',
                    'tbldivision.division as divisionFrom',
                    'tblper.rank',
                    'tbltransfer.date',
                    'tbltransfer.status'
                )
                ->orderBy('tbltransfer.date', 'ASC')
                ->get();

            return response()->json([
                'status'       => 'success',
                'staffPending' => $staffPending,
                'totalStaff'   => count($staffPending),
                'curDivision'  => $curDiv->division
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve pending transfers: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve or Reject Transfers.
     */
    public function approveOrRejectTransfers(Request $request)
    {
        $userContext = $this->getUserContext($request);
        if (!$userContext) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'action'   => 'required|in:approve,reject',
            'staffIds' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $action = $request->input('action');
            $staffIds = $request->input('staffIds');
            $curDiv = $this->curDivision($userContext['userId']);
            $curDivisionID = $curDiv ? $curDiv->divisionID : null;
            $date = date("Y-m-d");

            if (empty($curDivisionID)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Your user account does not have a valid division assignment.',
                ], 422);
            }

            $validIds = DB::table('tbltransfer')
                ->whereIn('fileNo', $staffIds)
                ->where('status', '=', 'pending')
                ->where('divisionTo', '=', $curDivisionID)
                ->pluck('fileNo');

            if ($validIds->isEmpty()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'No pending transfers found for the selected staff.',
                ], 422);
            }

            $successCount = 0;
            foreach ($validIds as $fileNo) {
                DB::beginTransaction();
                try {
                    if ($action === 'approve') {
                        // Replicate legacy status update values
                        DB::table('tblper')
                            ->where('fileNo', $fileNo)
                            ->update([
                                'divisionID'   => $curDivisionID,
                                'staff_status' => 1,
                                'status_value' => 'Aaaactive Sssservice'
                            ]);

                        DB::table('tbltransfer')
                            ->where('fileNo', $fileNo)
                            ->where('status', 'pending')
                            ->update(['status' => 'approved']);

                        Log::info("API Transfer Approved for fileno {$fileNo} to division ID {$curDivisionID}");
                    } else {
                        // Reject staff
                        DB::table('tbltransfer')
                            ->where('fileNo', $fileNo)
                            ->where('status', 'pending')
                            ->update(['status' => 'rejected', 'date' => $date]);

                        Log::info("API Transfer Rejected for fileno {$fileNo}");
                    }
                    DB::commit();
                    $successCount++;
                } catch (\Exception $e) {
                    DB::rollback();
                    Log::error("API Error processing transfer for {$fileNo}: " . $e->getMessage());
                }
            }

            $verb = $action === 'approve' ? 'approved' : 'rejected';
            return response()->json([
                'status'  => 'success',
                'message' => "Successfully {$verb} {$successCount} staff transfers.",
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => 'An error occurred processing approvals: ' . $th->getMessage(),
            ], 500);
        }
    }
}
