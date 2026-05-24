<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CvSetupApiController extends Controller
{
    /**
     * Resolve the current user context from the X-User-Id header.
     */
    private function getUserContext(Request $request): ?array
    {
        $userId = $request->header('X-User-Id');

        if (!$userId) {
            return null;
        }

        $isSuperAdmin = DB::table('assign_user_role')
            ->where('userID', $userId)
            ->where('roleID', 1) // Super Admin
            ->exists();

        $adminStaff = DB::table('assign_user_role')
            ->where('userID', $userId)
            ->where('roleID', 48) // Admin Staff
            ->exists();

        $employee = DB::table('tblper')->where('UserID', $userId)->first();

        return [
            'userId'       => $userId,
            'isSuperAdmin' => $isSuperAdmin,
            'isAdminStaff' => $adminStaff,
            'employee'     => $employee,
        ];
    }

    /**
     * GET /api/nextjs/payroll/cv-setups/banks
     */
    public function getBanks(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $banks = DB::table('tblbanklist')
                ->select('bankID as id', 'bank as name')
                ->orderBy('bank', 'asc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $banks
            ]);
        } catch (\Throwable $th) {
            Log::error('CvSetupApiController getBanks: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/cv-setups
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $search = trim($request->input('search', ''));
            $query = DB::table('tblcvSetup as cv')
                ->leftJoin('tblearningParticular as lp', 'lp.ID', '=', 'cv.particularID')
                ->leftJoin('tblbanklist as bl', 'bl.bankID', '=', 'cv.bank')
                ->select(
                    'cv.*',
                    'lp.Particular as variable_type_name',
                    'bl.bank as bankName'
                );

            if ($search !== '') {
                $query->where('cv.description', 'like', "%{$search}%");
            }

            $records = $query->orderBy('cv.ID', 'desc')->get()->map(function ($row) {
                return [
                    'id'                 => $row->ID,
                    'particularID'       => $row->particularID,
                    'variable_type_name' => $row->variable_type_name ?? 'Unknown',
                    'description'        => $row->description,
                    'bank'               => $row->bank,
                    'bankName'           => $row->bankName ?? '',
                    'account_name'       => $row->account_name,
                    'account_number'     => $row->account_number,
                    'status'             => (int) ($row->status ?? 1),
                ];
            });

            return response()->json([
                'status'       => 'success',
                'data'         => $records,
                'isSuperAdmin' => $ctx['isSuperAdmin'],
                'isAdminStaff' => $ctx['isAdminStaff'],
            ]);
        } catch (\Throwable $th) {
            Log::error('CvSetupApiController index: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/cv-setups
     */
    public function store(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            // Only admin roles can modify setup variables
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                return response()->json(['status' => 'error', 'message' => 'Forbidden – Administrative rights required.'], 403);
            }

            $id = $request->input('id');

            $request->validate([
                'particularID'   => 'required|integer|in:1,2',
                'description'    => 'required|string|unique:tblcvSetup,description,' . ($id ?? 'NULL') . ',ID',
                'bank'           => 'nullable',
                'account_name'   => 'nullable|string',
                'account_number' => 'nullable|string',
                'status'         => 'boolean',
            ]);

            $particularID  = $request->input('particularID');
            $description   = trim($request->input('description'));
            $bank          = $request->input('bank');
            $accountName   = $request->input('account_name');
            $accountNumber = $request->input('account_number');
            $status        = $request->input('status', true) ? 1 : 0;

            $data = [
                'particularID'   => $particularID,
                'description'    => $description,
                'bank'           => $bank,
                'account_name'   => $accountName,
                'account_number' => $accountNumber,
                'status'         => $status,
                'economiccode'   => 1, // Default value from legacy controller
            ];

            if ($id) {
                // Update
                $exists = DB::table('tblcvSetup')->where('ID', $id)->exists();
                if (!$exists) {
                    return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
                }

                DB::table('tblcvSetup')->where('ID', $id)->update($data);
                $message = 'Setup variable updated successfully.';
            } else {
                // Insert
                DB::table('tblcvSetup')->insert($data);
                $message = 'Setup variable created successfully.';
            }

            return response()->json([
                'status'  => 'success',
                'message' => $message,
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed.',
                'errors'  => $ve->errors()
            ], 422);
        } catch (\Throwable $th) {
            Log::error('CvSetupApiController store: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/nextjs/payroll/cv-setups/{id}
     */
    public function destroy(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                return response()->json(['status' => 'error', 'message' => 'Forbidden.'], 403);
            }

            $record = DB::table('tblcvSetup')->where('ID', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
            }

            // Deletion Safety check: verify if the Setup ID is in use in related tables
            $inStaffCv = DB::table('tblstaffCV')->where('cvID', $id)->exists();
            $inOtherED = DB::table('tblotherEarningDeduction')->where('CVID', $id)->exists();
            
            // Check in our new Next.js staffEarningAndDeduction table if it exists
            $inStaffED = false;
            if (\Illuminate\Support\Facades\Schema::hasTable('staffEarningAndDeduction')) {
                $inStaffED = DB::table('staffEarningAndDeduction')->where('cv_setup_id', $id)->exists();
            }

            if ($inStaffCv || $inOtherED || $inStaffED) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'This variable setup is currently assigned to employee profiles and cannot be deleted.'
                ], 400);
            }

            DB::table('tblcvSetup')->where('ID', $id)->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Setup variable deleted successfully.',
            ]);
        } catch (\Throwable $th) {
            Log::error('CvSetupApiController destroy: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
