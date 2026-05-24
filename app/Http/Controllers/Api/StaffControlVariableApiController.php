<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StaffControlVariableApiController extends Controller
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

        $isAuditStaff = DB::table('assign_user_role')
            ->where('userID', $userId)
            ->whereIn('roleID', [34, 35]) // Audit Head, Audit Staff
            ->exists();

        $employee = DB::table('tblper')->where('UserID', $userId)->first();

        return [
            'userId'       => $userId,
            'isSuperAdmin' => $isSuperAdmin,
            'isAdminStaff' => $adminStaff,
            'isAuditStaff' => $isAuditStaff,
            'employee'     => $employee,
            'isHod'        => $employee && $employee->is_hod == 1,
        ];
    }

    /**
     * GET /api/nextjs/payroll/staff-control-variables/staff
     */
    public function getStaffList(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $query = DB::table('tblper')
                ->where('rank', '!=', 2)
                ->select('ID as id', 'fileNo', 'surname', 'first_name', 'othernames')
                ->orderBy('surname', 'asc');

            // Non-admins can only select themselves
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                if ($ctx['employee']) {
                    $query->where('ID', $ctx['employee']->ID);
                } else {
                    $query->where('ID', 0);
                }
            }

            $staff = $query->get()
                ->map(function ($row) {
                    $fullName = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                    return [
                        'id' => $row->id,
                        'fileNo' => $row->fileNo ?? '',
                        'name' => $fullName,
                        'label' => $fullName,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $staff
            ]);
        } catch (\Throwable $th) {
            Log::error('StaffControlVariableApiController getStaffList: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/staff-control-variables/variable-types
     */
    public function getVariableTypes(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $types = DB::table('tblearningParticular')
                ->select('ID as id', 'Particular as name')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $types
            ]);
        } catch (\Throwable $th) {
            Log::error('StaffControlVariableApiController getVariableTypes: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/staff-control-variables/descriptions/{particularId}
     */
    public function getDescriptions(Request $request, $particularId)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $descriptions = DB::table('tblcvSetup')
                ->where('particularID', $particularId)
                ->where('status', 1)
                ->select('ID as id', 'description')
                ->orderBy('description', 'asc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $descriptions
            ]);
        } catch (\Throwable $th) {
            Log::error('StaffControlVariableApiController getDescriptions: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/staff-control-variables
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $search = trim($request->input('search', ''));
            $query = DB::table('staffEarningAndDeduction as sc')
                ->join('tblper as p', 'p.ID', '=', 'sc.staffId')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->select(
                    'sc.*',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'd.department'
                );

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('p.fileNo', 'like', "%{$search}%")
                      ->orWhere('p.surname', 'like', "%{$search}%")
                      ->orWhere('p.first_name', 'like', "%{$search}%")
                      ->orWhere('p.othernames', 'like', "%{$search}%")
                      ->orWhere('sc.description', 'like', "%{$search}%");
                });
            }

            $employee = $ctx['employee'];

            if ($ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || $ctx['isAuditStaff']) {
                // Admin and Audit see all
            } elseif ($employee && $employee->is_hod == 1) {
                // HOD sees department staff
                $query->where('p.departmentID', $employee->departmentID);
            } elseif ($employee) {
                // Regular staff see only their own
                $query->where('sc.staffId', $employee->ID);
            } else {
                $query->where('sc.id', 0);
            }

            $records = $query->orderBy('sc.id', 'desc')->get()->map(function ($row) {
                return [
                    'id'            => $row->id,
                    'staffId'       => $row->staffId,
                    'name'          => trim("{$row->surname} {$row->first_name} {$row->othernames}"),
                    'fileNo'        => $row->fileNo ?? '',
                    'department'    => $row->department ?? '',
                    'variable_type' => $row->variable_type,
                    'cv_setup_id'   => $row->cv_setup_id,
                    'description'   => $row->description,
                    'amount'        => (float) $row->amount,
                    'target_amount' => $row->target_amount !== null ? (float) $row->target_amount : null,
                    'no_limit'      => (int) $row->no_limit,
                    'one_time'      => (int) $row->one_time,
                    'created_at'    => $row->created_at,
                    'updated_at'    => $row->updated_at,
                ];
            });

            return response()->json([
                'status'       => 'success',
                'data'         => $records,
                'isSuperAdmin' => $ctx['isSuperAdmin'],
                'isAdminStaff' => $ctx['isAdminStaff'],
                'isAuditStaff' => $ctx['isAuditStaff'],
                'isHod'        => $ctx['isHod'],
                'employee'     => $employee ? [
                    'id'           => $employee->ID,
                    'departmentID' => $employee->departmentID,
                ] : null,
            ]);
        } catch (\Throwable $th) {
            Log::error('StaffControlVariableApiController index: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/staff-control-variables
     */
    public function store(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $request->validate([
                'staffId'       => 'required|integer',
                'variable_type' => 'required|string',
                'cv_setup_id'   => 'required|integer',
                'amount'        => 'required|numeric|min:0',
                'target_amount' => 'nullable|numeric|min:0',
                'no_limit'      => 'boolean',
                'one_time'      => 'boolean',
            ]);

            $staffId      = $request->input('staffId');
            $variableType = $request->input('variable_type');
            $cvSetupId    = $request->input('cv_setup_id');
            $amount       = (float) $request->input('amount');
            $noLimit      = $request->input('no_limit', false) ? 1 : 0;
            $oneTime      = $request->input('one_time', false) ? 1 : 0;

            // One-Time automatically overrides target amount to amount
            $targetAmount = $oneTime ? $amount : ($request->input('target_amount') !== null ? (float) $request->input('target_amount') : null);

            // Fetch description and verify cvID
            $cvSetup = DB::table('tblcvSetup')->where('ID', $cvSetupId)->first();
            if (!$cvSetup) {
                return response()->json(['status' => 'error', 'message' => 'Invalid Description selected.'], 400);
            }

            // Verify staff member
            $staff = DB::table('tblper')->where('ID', $staffId)->first();
            if (!$staff) {
                return response()->json(['status' => 'error', 'message' => 'Invalid staff member selected.'], 400);
            }

            // If user is not admin, they can only apply for themselves
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                if (!$ctx['employee'] || $ctx['employee']->ID !== (int)$staffId) {
                    return response()->json(['status' => 'error', 'message' => 'Unauthorized to submit control variables for other staff members.'], 403);
                }
            }

            $id = $request->input('id');

            $data = [
                'staffId'       => $staffId,
                'variable_type' => $variableType,
                'cv_setup_id'   => $cvSetupId,
                'description'   => $cvSetup->description,
                'amount'        => $amount,
                'target_amount' => $targetAmount,
                'no_limit'      => $noLimit,
                'one_time'      => $oneTime,
                'updated_at'    => now(),
            ];

            if ($id) {
                // Update
                $exists = DB::table('staffEarningAndDeduction')->where('id', $id)->first();
                if (!$exists) {
                    return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
                }

                // If non-admin, ensure they own the record
                if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                    if ($exists->staffId !== $ctx['employee']->ID) {
                        return response()->json(['status' => 'error', 'message' => 'Unauthorized to edit this record.'], 403);
                    }
                }

                DB::table('staffEarningAndDeduction')->where('id', $id)->update($data);
                $message = 'Record updated successfully.';
            } else {
                // Insert
                $data['created_at'] = now();
                DB::table('staffEarningAndDeduction')->insert($data);
                $message = 'Record created successfully.';
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
            Log::error('StaffControlVariableApiController store: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/nextjs/payroll/staff-control-variables/{id}
     */
    public function destroy(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $record = DB::table('staffEarningAndDeduction')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
            }

            // Check authorization
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                if (!$ctx['employee'] || $record->staffId !== $ctx['employee']->ID) {
                    return response()->json(['status' => 'error', 'message' => 'Unauthorized to delete this record.'], 403);
                }
            }

            DB::table('staffEarningAndDeduction')->where('id', $id)->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Record deleted successfully.',
            ]);
        } catch (\Throwable $th) {
            Log::error('StaffControlVariableApiController destroy: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
