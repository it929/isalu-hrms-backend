<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IouApiController extends Controller
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

        // Finance roles: 36 (Finance Head), 37 (Finance Staff), or any custom role ID for NHF/Collator if needed
        $isFinanceStaff = DB::table('assign_user_role')
            ->where('userID', $userId)
            ->whereIn('roleID', [36, 37])
            ->exists();

        $employee = DB::table('tblper')->where('UserID', $userId)->first();

        return [
            'userId'         => $userId,
            'isSuperAdmin'   => $isSuperAdmin,
            'isAdminStaff'   => $adminStaff,
            'isAuditStaff'   => $isAuditStaff,
            'isFinanceStaff' => $isFinanceStaff,
            'employee'       => $employee,
            'isHod'          => $employee && $employee->is_hod == 1,
        ];
    }

    /**
     * GET /api/nextjs/payroll/ious/staff
     * Fetch active employees along with their monthly gross salary and max allowed IOU (50%).
     */
    public function getStaffList(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $query = DB::table('tblper')
                ->where('rank', '!=', 2) // Exclude terminated/retired
                ->select('ID as id', 'fileNo', 'surname', 'first_name', 'othernames')
                ->orderBy('surname', 'asc');

            // Non-admins can only select themselves
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                if ($ctx['employee']) {
                    $query->where('ID', $ctx['employee']->ID);
                } else {
                    $query->where('ID', 0); // fallback empty
                }
            }

            $staff = $query->get()->map(function ($row) {
                $fullName = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                
                // Fetch salary structure
                $struct = DB::table('salary_structures')
                    ->where('staffId', $row->id)
                    ->first();
                
                $grossSalary = 0.00;
                if ($struct) {
                    $grossSalary = (float)$struct->basic_salary +
                                   (float)$struct->housing_allowance +
                                   (float)$struct->transport_allowance +
                                   (float)$struct->medical_allowance +
                                   (float)$struct->utility_allowance +
                                   (float)$struct->meal_allowance;
                }

                return [
                    'id'       => $row->id,
                    'fileNo'   => $row->fileNo ?? '',
                    'name'     => $fullName,
                    'label'    => $fullName,
                    'salary'   => $grossSalary,
                    'max_iou'  => $grossSalary * 0.50,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data'   => $staff
            ]);
        } catch (\Throwable $th) {
            Log::error('IouApiController getStaffList: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/ious
     * List all IOUs matching user credentials.
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $search = trim($request->input('search', ''));
            $query = DB::table('iou_records as ir')
                ->join('tblper as p', 'p.ID', '=', 'ir.staff_id')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->select(
                    'ir.*',
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
                      ->orWhere('ir.reason', 'like', "%{$search}%");
                });
            }

            $employee = $ctx['employee'];

            if ($ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || $ctx['isFinanceStaff'] || $ctx['isAuditStaff']) {
                // Admins, Finance, and Audit see all IOU applications
            } elseif ($employee && $employee->is_hod == 1) {
                // HOD sees staff in their department
                $query->where('p.departmentID', $employee->departmentID);
            } elseif ($employee) {
                // Regular staff see only their own
                $query->where('ir.staff_id', $employee->ID);
            } else {
                $query->where('ir.id', 0); // fallback empty
            }

            $records = $query->orderBy('ir.id', 'desc')->get()->map(function ($row) {
                $row->name = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                
                // Fetch salary structure to calculate dynamic limit details on the table rows
                $struct = DB::table('salary_structures')
                    ->where('staffId', $row->staff_id)
                    ->first();
                
                $grossSalary = 0.00;
                if ($struct) {
                    $grossSalary = (float)$struct->basic_salary +
                                   (float)$struct->housing_allowance +
                                   (float)$struct->transport_allowance +
                                   (float)$struct->medical_allowance +
                                   (float)$struct->utility_allowance +
                                   (float)$struct->meal_allowance;
                }

                $row->gross_salary = $grossSalary;
                $row->percentage_of_salary = $grossSalary > 0 ? round(((float)$row->amount / $grossSalary) * 100, 2) : 0;
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
            Log::error('IouApiController index: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/ious
     * Save or update an IOU application.
     */
    public function store(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $validated = $request->validate([
                'id'             => 'nullable|integer',
                'staff_id'       => 'required|integer',
                'amount'         => 'required|numeric|min:0.01',
                'reason'         => 'required|string',
                'iou_date'       => 'required|date',
                'repayment_date' => 'nullable|date',
            ]);

            // Staff check
            $staff = DB::table('tblper')->where('ID', $validated['staff_id'])->first();
            if (!$staff) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'The selected staff member does not exist.'
                ], 404);
            }

            // Limit check: Gross monthly salary from salary_structures
            $struct = DB::table('salary_structures')
                ->where('staffId', $validated['staff_id'])
                ->first();

            $grossSalary = 0.00;
            if ($struct) {
                $grossSalary = (float)$struct->basic_salary +
                               (float)$struct->housing_allowance +
                               (float)$struct->transport_allowance +
                               (float)$struct->medical_allowance +
                               (float)$struct->utility_allowance +
                               (float)$struct->meal_allowance;
            }

            $maxAllowed = $grossSalary * 0.50;
            $amount = (float) $validated['amount'];

            if ($amount > $maxAllowed) {
                $formattedMax = number_format($maxAllowed, 2);
                $formattedSalary = number_format($grossSalary, 2);
                return response()->json([
                    'status'  => 'error',
                    'message' => "The IOU amount (₦" . number_format($amount, 2) . ") exceeds the maximum allowed limit of 50% of the employee's salary. Maximum allowed: ₦{$formattedMax} (Gross Salary: ₦{$formattedSalary})."
                ], 422);
            }

            // Ownership check for non-admins
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                if (!$ctx['employee'] || $ctx['employee']->ID != $validated['staff_id']) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'You can only apply for your own IOUs.'
                    ], 403);
                }
            }

            $id = $validated['id'] ?? null;
            $status = 0; // Default pending

            if ($id) {
                $existing = DB::table('iou_records')->where('id', $id)->first();
                if (!$existing) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'IOU record not found.'
                    ], 404);
                }

                // Non-admins can only edit pending applications
                if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                    if ($existing->status !== 0) {
                        return response()->json([
                            'status'  => 'error',
                            'message' => 'This IOU application has already been processed and cannot be edited.'
                        ], 403);
                    }
                }
                $status = $existing->status;
            }

            $data = [
                'staff_id'       => $validated['staff_id'],
                'amount'         => $amount,
                'reason'         => trim($validated['reason']),
                'iou_date'       => $validated['iou_date'],
                'repayment_date' => $validated['repayment_date'] ?? null,
                'status'         => $status,
                'updated_at'     => now(),
            ];

            if ($id) {
                DB::table('iou_records')->where('id', $id)->update($data);
                $message = 'IOU application updated successfully.';
            } else {
                $data['hod_status']     = 0;
                $data['finance_status'] = 0;
                $data['admin_status']   = 0;
                $data['created_at']     = now();
                $id = DB::table('iou_records')->insertGetId($data);
                $message = 'IOU application submitted successfully.';
            }

            return response()->json([
                'status'  => 'success',
                'message' => $message,
                'id'      => $id
            ]);
        } catch (\Throwable $th) {
            Log::error('IouApiController store: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/nextjs/payroll/ious/{id}
     * Remove IOU application.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $record = DB::table('iou_records')->where('id', $id)->first();
            if (!$record) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'IOU record not found.'
                ], 404);
            }

            // Non-admins can only delete pending and self-owned
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                if (!$ctx['employee'] || $record->staff_id != $ctx['employee']->ID) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'You cannot delete another staff member\'s IOU application.'
                    ], 403);
                }

                if ($record->status !== 0) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'This IOU application has already been processed and cannot be deleted.'
                    ], 403);
                }
            }

            DB::table('iou_records')->where('id', $id)->delete();
            DB::table('iou_approvals')->where('iou_id', $id)->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'IOU application deleted successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('IouApiController destroy: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Helper to log detailed approvals/rejections audits.
     */
    private function logApproval(int $iouId, string $level, int $approverId, int $status, ?string $remarks)
    {
        DB::table('iou_approvals')->insert([
            'iou_id'      => $iouId,
            'level'       => $level,
            'approver_id' => $approverId,
            'status'      => $status,
            'remarks'     => $remarks,
            'created_at'  => now(),
        ]);
    }

    /**
     * GET /api/nextjs/payroll/ious/hod-approve/{id}
     */
    public function hodApprove(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$ctx['isHod'])) {
                return response()->json(['status' => 'error', 'message' => 'HOD or administrative privileges required.'], 401);
            }

            $record = DB::table('iou_records')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'IOU record not found.'], 404);
            }

            if ($record->hod_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This IOU application is not in a pending HOD state.'], 400);
            }

            // HOD department check
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                $employee = DB::table('tblper')->where('ID', $record->staff_id)->first();
                if (!$employee || $employee->departmentID != $ctx['employee']->departmentID) {
                    return response()->json(['status' => 'error', 'message' => 'Access denied: staff belongs to a different department.'], 403);
                }
            }

            $remarks = $request->input('remarks');
            DB::table('iou_records')->where('id', $id)->update([
                'hod_status' => 1,
                'hod_id'     => $ctx['userId'],
                'hod_date'   => now(),
                'remarks'    => $remarks,
                'updated_at' => now(),
            ]);

            $this->logApproval($id, 'HOD', (int)$ctx['userId'], 1, $remarks);

            return response()->json(['status' => 'success', 'message' => 'IOU recommended successfully by HOD.']);
        } catch (\Throwable $th) {
            Log::error('IouApiController hodApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/ious/hod-reject/{id}
     */
    public function hodReject(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$ctx['isHod'])) {
                return response()->json(['status' => 'error', 'message' => 'HOD or administrative privileges required.'], 401);
            }

            $record = DB::table('iou_records')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'IOU record not found.'], 404);
            }

            if ($record->hod_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This IOU application is not in a pending HOD state.'], 400);
            }

            // HOD department check
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                $employee = DB::table('tblper')->where('ID', $record->staff_id)->first();
                if (!$employee || $employee->departmentID != $ctx['employee']->departmentID) {
                    return response()->json(['status' => 'error', 'message' => 'Access denied: staff belongs to a different department.'], 403);
                }
            }

            $remarks = $request->input('remarks');
            DB::table('iou_records')->where('id', $id)->update([
                'hod_status' => 2,
                'status'     => 2, // Rejects overall application immediately
                'hod_id'     => $ctx['userId'],
                'hod_date'   => now(),
                'remarks'    => $remarks,
                'updated_at' => now(),
            ]);

            $this->logApproval($id, 'HOD', (int)$ctx['userId'], 2, $remarks);

            return response()->json(['status' => 'success', 'message' => 'IOU rejected by HOD.']);
        } catch (\Throwable $th) {
            Log::error('IouApiController hodReject: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/ious/finance-approve/{id}
     */
    public function financeApprove(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$ctx['isFinanceStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'Finance or administrative privileges required.'], 401);
            }

            $record = DB::table('iou_records')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'IOU record not found.'], 404);
            }

            // Finance approves after HR recommends (admin_status === 1)
            if ($record->admin_status !== 1 || $record->finance_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This IOU is not recommended by HR or already processed by Finance.'], 400);
            }

            $remarks = $request->input('remarks');
            DB::table('iou_records')->where('id', $id)->update([
                'finance_status' => 1,
                'status'         => 1, // Final step approves overall application
                'finance_id'     => $ctx['userId'],
                'finance_date'   => now(),
                'remarks'        => $remarks,
                'updated_at'     => now(),
            ]);

            $this->logApproval($id, 'Finance', (int)$ctx['userId'], 1, $remarks);

            return response()->json(['status' => 'success', 'message' => 'IOU application fully approved.']);
        } catch (\Throwable $th) {
            Log::error('IouApiController financeApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/ious/finance-reject/{id}
     */
    public function financeReject(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'] && !$ctx['isFinanceStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'Finance or administrative privileges required.'], 401);
            }

            $record = DB::table('iou_records')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'IOU record not found.'], 404);
            }

            if ($record->admin_status !== 1 || $record->finance_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This IOU is not recommended by HR or already processed by Finance.'], 400);
            }

            $remarks = $request->input('remarks');
            DB::table('iou_records')->where('id', $id)->update([
                'finance_status' => 2,
                'status'         => 2, // Rejects overall application immediately
                'finance_id'     => $ctx['userId'],
                'finance_date'   => now(),
                'remarks'        => $remarks,
                'updated_at'     => now(),
            ]);

            $this->logApproval($id, 'Finance', (int)$ctx['userId'], 2, $remarks);

            return response()->json(['status' => 'success', 'message' => 'IOU application rejected by Finance.']);
        } catch (\Throwable $th) {
            Log::error('IouApiController financeReject: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/ious/hr-approve/{id}
     */
    public function hrApprove(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'HR or administrative privileges required.'], 401);
            }

            $record = DB::table('iou_records')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'IOU record not found.'], 404);
            }

            // HR recommends after HOD recommends
            if ($record->hod_status !== 1 || $record->admin_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This IOU is not recommended by HOD or already processed by HR.'], 400);
            }

            $remarks = $request->input('remarks');
            DB::table('iou_records')->where('id', $id)->update([
                'admin_status' => 1,
                'admin_id'     => $ctx['userId'],
                'admin_date'   => now(),
                'remarks'      => $remarks,
                'updated_at'   => now(),
            ]);

            $this->logApproval($id, 'HR', (int)$ctx['userId'], 1, $remarks);

            return response()->json(['status' => 'success', 'message' => 'IOU recommended successfully by HR.']);
        } catch (\Throwable $th) {
            Log::error('IouApiController hrApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/ious/hr-reject/{id}
     */
    public function hrReject(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx || (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff'])) {
                return response()->json(['status' => 'error', 'message' => 'HR or administrative privileges required.'], 401);
            }

            $record = DB::table('iou_records')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'IOU record not found.'], 404);
            }

            if ($record->hod_status !== 1 || $record->admin_status !== 0 || $record->status !== 0) {
                return response()->json(['status' => 'error', 'message' => 'This IOU is not recommended by HOD or already processed by HR.'], 400);
            }

            $remarks = $request->input('remarks');
            DB::table('iou_records')->where('id', $id)->update([
                'admin_status' => 2,
                'status'       => 2, // Rejects overall application immediately
                'admin_id'     => $ctx['userId'],
                'admin_date'   => now(),
                'remarks'      => $remarks,
                'updated_at'   => now(),
            ]);

            $this->logApproval($id, 'HR', (int)$ctx['userId'], 2, $remarks);

            return response()->json(['status' => 'success', 'message' => 'IOU rejected by HR.']);
        } catch (\Throwable $th) {
            Log::error('IouApiController hrReject: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
