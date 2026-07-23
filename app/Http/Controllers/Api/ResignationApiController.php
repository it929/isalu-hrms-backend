<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResignationApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * GET /api/nextjs/payroll/resignations/staff
     * Fetch active employees.
     */
    public function getStaffList(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $query = DB::table('tblper as p')
                ->where('p.rank', '!=', 2) // Exclude terminated/retired
                ->where('p.staff_status', 1)
                ->select(
                    'p.ID as id',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames'
                )
                ->orderBy('p.surname', 'asc');

            // Non-admins can only select themselves
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                if ($ctx['employee']) {
                    $query->where('p.ID', $ctx['employee']->ID);
                } else {
                    $query->where('p.ID', 0);
                }
            }

            $staff = $query->get()->map(function ($row) {
                $fullName = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                return [
                    'id'     => $row->id,
                    'fileNo' => $row->fileNo ?? '',
                    'name'   => $fullName,
                    'label'  => $fullName,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data'   => $staff
            ]);
        } catch (\Throwable $th) {
            Log::error('ResignationApiController getStaffList: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/resignations
     * List all resignation requests.
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $search = trim($request->input('search', ''));
            $query = DB::table('resignation_requests as rr')
                ->join('tblper as p', 'p.ID', '=', 'rr.staff_id')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->leftJoin('users as u_hod', 'u_hod.id', '=', 'rr.hod_id')
                ->leftJoin('users as u_admin', 'u_admin.id', '=', 'rr.admin_id')
                ->leftJoin('users as u_finance', 'u_finance.id', '=', 'rr.finance_id')
                ->select(
                    'rr.*',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'd.department',
                    'u_hod.name as hod_name',
                    'u_admin.name as admin_name',
                    'u_finance.name as finance_name'
                );

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('p.surname', 'like', "%{$search}%")
                      ->orWhere('p.first_name', 'like', "%{$search}%")
                      ->orWhere('p.othernames', 'like', "%{$search}%")
                      ->orWhere('rr.reason', 'like', "%{$search}%");
                });
            }

            $employee = $ctx['employee'];

            if ($ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || $ctx['isFinanceStaff'] || $ctx['isAuditStaff']) {
                // Admins, Finance and Audit see all requests
            } elseif ($employee && $ctx['isHod']) {
                // HOD sees department staff requests
                $query->where('p.departmentID', $employee->departmentID);
            } elseif ($employee) {
                // Regular staff see only their own requests
                $query->where('rr.staff_id', $employee->ID);
            } else {
                $query->where('rr.id', 0); // fallback empty
            }

            $records = $query->orderBy('rr.id', 'desc')->get()->map(function ($row) {
                $row->name = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                $row->last_day = date('Y-m-d', strtotime($row->resignation_date . ' + 30 days'));
                return $row;
            });

            return response()->json([
                'status'         => 'success',
                'data'           => $records,
                'isSuperAdmin'   => $ctx['isSuperAdmin'],
                'isAdminStaff'   => $ctx['isAdminStaff'],
                'isFinanceStaff' => $ctx['isFinanceStaff'],
                'isAuditStaff'   => $ctx['isAuditStaff'],
                'isHod'          => $ctx['isHod'],
                'employee'       => $employee,
            ]);
        } catch (\Throwable $th) {
            Log::error('ResignationApiController index: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/resignations
     * Submit or update resignation request.
     */
    public function store(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $validated = $request->validate([
                'id'               => 'nullable|integer',
                'staff_id'         => 'required|integer',
                'reason'           => 'required|string',
                'resignation_date' => 'required|date',
            ]);

            // Staff check
            $staff = DB::table('tblper')->where('ID', $validated['staff_id'])->first();
            if (!$staff) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'The selected staff member does not exist.'
                ], 404);
            }

            $id = $validated['id'] ?? null;
            $data = [
                'staff_id'         => $validated['staff_id'],
                'reason'           => $validated['reason'],
                'resignation_date' => $validated['resignation_date'],
                'updated_at'       => now(),
            ];

            if ($id) {
                $record = DB::table('resignation_requests')->where('id', $id)->first();
                if (!$record) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Resignation request not found.'
                    ], 404);
                }

                if ($record->status !== 0) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'This request has already been processed and cannot be modified.'
                    ], 422);
                }

                DB::table('resignation_requests')->where('id', $id)->update($data);
                $msg = 'Resignation request updated successfully.';
            } else {
                $data['status']         = 0;
                $data['hod_status']     = 0;
                $data['admin_status']   = 0;
                $data['finance_status'] = 0;
                $data['created_at']     = now();

                DB::table('resignation_requests')->insert($data);
                $msg = 'Resignation request submitted successfully.';
            }

            return response()->json([
                'status'  => 'success',
                'message' => $msg
            ]);
        } catch (\Throwable $th) {
            Log::error('ResignationApiController store: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/nextjs/payroll/resignations/{id}
     * Remove pending resignation request.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $record = DB::table('resignation_requests')->where('id', $id)->first();
            if (!$record) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Resignation request not found.'
                ], 404);
            }

            // Non-admins can only delete pending and self-owned
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                if (!$ctx['employee'] || $record->staff_id != $ctx['employee']->ID) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'You cannot delete another staff member\'s resignation request.'
                    ], 403);
                }

                if ($record->status !== 0) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'This request has already been processed and cannot be deleted.'
                    ], 403);
                }
            }

            DB::table('resignation_requests')->where('id', $id)->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Resignation request deleted successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('ResignationApiController destroy: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/resignations/hod-approve/{id}
     */
    public function hodApprove(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$this->hasHodPermission($ctx, 'approve_resignation'))) {
                return response()->json(['status' => 'error', 'message' => 'HOD or delegated administrative privileges required.'], 401);
            }

            $record = DB::table('resignation_requests')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Resignation request not found.'], 404);
            }

            if ($record->hod_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This request is not in a pending HOD state.'], 400);
            }

            // HOD department check
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                $employee = DB::table('tblper')->where('ID', $record->staff_id)->first();
                if (!$employee || $employee->departmentID != $ctx['employee']->departmentID) {
                    return response()->json(['status' => 'error', 'message' => 'Access denied: staff belongs to a different department.'], 403);
                }
            }

            $remarks = $request->input('remarks');
            DB::table('resignation_requests')->where('id', $id)->update([
                'hod_status' => 1,
                'hod_id'     => $ctx['userId'],
                'hod_date'   => now(),
                'remarks'    => $remarks,
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Resignation request approved by HOD.']);
        } catch (\Throwable $th) {
            Log::error('ResignationApiController hodApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/resignations/hod-reject/{id}
     */
    public function hodReject(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$this->hasHodPermission($ctx, 'approve_resignation'))) {
                return response()->json(['status' => 'error', 'message' => 'HOD or delegated administrative privileges required.'], 401);
            }

            $record = DB::table('resignation_requests')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Resignation request not found.'], 404);
            }

            if ($record->hod_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This request is not in a pending HOD state.'], 400);
            }

            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                $employee = DB::table('tblper')->where('ID', $record->staff_id)->first();
                if (!$employee || $employee->departmentID != $ctx['employee']->departmentID) {
                    return response()->json(['status' => 'error', 'message' => 'Access denied: staff belongs to a different department.'], 403);
                }
            }

            $remarks = $request->input('remarks');
            DB::table('resignation_requests')->where('id', $id)->update([
                'hod_status' => 2,
                'status'     => 2, // rejected
                'hod_id'     => $ctx['userId'],
                'hod_date'   => now(),
                'remarks'    => $remarks,
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Resignation request rejected by HOD.']);
        } catch (\Throwable $th) {
            Log::error('ResignationApiController hodReject: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/resignations/hr-approve/{id}
     */
    public function hrApprove(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || !$this->hasHrPermission($ctx, 'hr_approve_resignation')) {
                return response()->json(['status' => 'error', 'message' => 'HR or delegated administrative privileges required.'], 401);
            }

            $record = DB::table('resignation_requests')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Resignation request not found.'], 404);
            }

            if ($record->hod_status !== 1 || $record->admin_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This request is not in a pending HR state.'], 400);
            }

            $remarks = $request->input('remarks');
            DB::table('resignation_requests')->where('id', $id)->update([
                'admin_status' => 1,
                'status'       => 1, // Approved overall (HR is now final stage)
                'admin_id'     => $ctx['userId'],
                'admin_date'   => now(),
                'remarks'      => $remarks,
                'updated_at'   => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Resignation request approved by HR Admin.']);
        } catch (\Throwable $th) {
            Log::error('ResignationApiController hrApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/resignations/hr-reject/{id}
     */
    public function hrReject(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || !$this->hasHrPermission($ctx, 'hr_approve_resignation')) {
                return response()->json(['status' => 'error', 'message' => 'HR or delegated administrative privileges required.'], 401);
            }

            $record = DB::table('resignation_requests')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Resignation request not found.'], 404);
            }

            if ($record->hod_status !== 1 || $record->admin_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This request is not in a pending HR state.'], 400);
            }

            $remarks = $request->input('remarks');
            DB::table('resignation_requests')->where('id', $id)->update([
                'admin_status' => 2,
                'status'       => 2, // rejected
                'admin_id'     => $ctx['userId'],
                'admin_date'   => now(),
                'remarks'      => $remarks,
                'updated_at'   => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Resignation request rejected by HR Admin.']);
        } catch (\Throwable $th) {
            Log::error('ResignationApiController hrReject: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
