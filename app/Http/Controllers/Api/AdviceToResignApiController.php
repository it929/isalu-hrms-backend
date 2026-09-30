<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdviceToResignApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * Common reason categories for Advice to Resign
     */
    const REASONS = [
        'Unsatisfactory Performance / Sub-par Competency',
        'Disciplinary Committee Recommendation',
        'Gross Misconduct / Insubordination',
        'Breach of Hospital Operational Rules & Protocols',
        'Persistent Negligence of Professional Duty',
        'Absence from Duty Without Official Permission',
        'Failure of Probationary Review Assessment',
        'Audit & Financial Irregularity Finding',
        'Medical Unfitness for Service',
        'Mutual Separation Agreement'
    ];

    /**
     * GET /api/nextjs/advice-to-resign/staff
     * Active staff list for selection.
     */
    public function getStaffList(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $search = trim($request->query('search', ''));

            $query = DB::table('tblper as p')
                ->leftJoin('tbldepartment as dept', 'dept.id', '=', 'p.departmentID')
                ->leftJoin('tbldesignation as des', 'des.id', '=', 'p.designation')
                ->where('p.rank', '!=', 2)
                ->where('p.staff_status', 1);

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where(DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, ''))"), 'like', "%{$search}%")
                      ->orWhere('p.ID', 'like', "%{$search}%")
                      ->orWhere('p.fileNo', 'like', "%{$search}%");
                });
            }

            $staff = $query->select(
                'p.ID as id',
                'p.ID as staff_id',
                'p.surname',
                'p.first_name',
                'p.othernames',
                DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as name"),
                'p.appointment_date',
                'p.doj',
                'p.email',
                'p.phone',
                'dept.department',
                'des.designation'
            )
            ->orderBy('p.surname', 'asc')
            ->limit(100)
            ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'staff' => $staff,
                    'reasons' => self::REASONS
                ],
                'staff' => $staff,
                'reasons' => self::REASONS
            ]);
        } catch (\Throwable $th) {
            Log::error('AdviceToResignApiController getStaffList: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to load staff list.'], 500);
        }
    }

    /**
     * GET /api/nextjs/advice-to-resign
     * List all Advice to Resign records with filtering and statistics.
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $activeRole = strtolower(trim($request->header('X-User-Role', '')));
            $userPerms = [
                'user_id' => $ctx['userId'] ?? null,
                'is_super_admin' => !empty($ctx['isSuperAdmin']),
                'is_hr_head' => !empty($ctx['isSuperAdmin']) || !empty($ctx['adminStaff']) || in_array($activeRole, ['hr head', 'head of hr', 'super admin', 'super administrator']),
                'is_audit_head' => !empty($ctx['isSuperAdmin']) || !empty($ctx['isAuditStaff']) || in_array($activeRole, ['audit head', 'head of audit', 'super admin', 'super administrator']),
                'is_finance_head' => !empty($ctx['isSuperAdmin']) || !empty($ctx['isFinanceStaff']) || in_array($activeRole, ['finance head', 'head of finance', 'super admin', 'super administrator']),
                'staff_id' => $ctx['employee'] ? $ctx['employee']->ID : null,
            ];

            $status = $request->query('status');
            $search = trim($request->query('search', ''));
            $departmentId = $request->query('department_id');
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            $query = DB::table('advice_to_resigns as atr')
                ->join('tblper as p', 'p.ID', '=', 'atr.staff_id')
                ->leftJoin('tbldepartment as dept', 'dept.id', '=', 'p.departmentID')
                ->leftJoin('tbldesignation as des', 'des.id', '=', 'p.designation')
                ->leftJoin('users as issuer', 'issuer.id', '=', 'atr.issued_by')
                ->leftJoin('users as actor', 'actor.id', '=', 'atr.action_by')
                ->leftJoin('users as hr_approver', 'hr_approver.id', '=', 'atr.hr_approved_by')
                ->leftJoin('users as audit_approver', 'audit_approver.id', '=', 'atr.audit_by')
                ->leftJoin('users as finance_approver', 'finance_approver.id', '=', 'atr.finance_by');

            $validStatuses = ['pending', 'applied', 'hr_approved', 'audit_approved', 'audit_rejected', 'paid', 'complied', 'terminated', 'withdrawn'];
            if (!empty($status) && in_array($status, $validStatuses)) {
                $query->where('atr.status', $status);
            }

            if (!empty($departmentId)) {
                $query->where('p.departmentID', $departmentId);
            }

            if (!empty($startDate)) {
                $query->where('atr.issue_date', '>=', $startDate);
            }

            if (!empty($endDate)) {
                $query->where('atr.issue_date', '<=', $endDate);
            }

            $isPrivileged = !empty($userPerms['is_super_admin']) || !empty($userPerms['is_hr_head']) || !empty($userPerms['is_audit_head']) || !empty($userPerms['is_finance_head']);
            if (!$isPrivileged && $ctx['employee']) {
                $query->where('atr.staff_id', $ctx['employee']->ID);
            }

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('atr.reference_no', 'like', "%{$search}%")
                      ->orWhere('atr.reason', 'like', "%{$search}%")
                      ->orWhere('atr.query_reference', 'like', "%{$search}%")
                      ->orWhere('p.ID', 'like', "%{$search}%")
                      ->orWhere('p.fileNo', 'like', "%{$search}%")
                      ->orWhere(DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, ''))"), 'like', "%{$search}%");
                });
            }

            $records = $query->select(
                'atr.*',
                'p.surname',
                'p.first_name',
                'p.othernames',
                DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as staff_name"),
                'p.email as staff_email',
                'p.phone as staff_phone',
                'p.staff_status',
                'dept.department',
                'des.designation',
                'issuer.name as issued_by_name',
                'actor.name as action_by_name',
                'hr_approver.name as hr_approver_name',
                'audit_approver.name as audit_approver_name',
                'finance_approver.name as finance_approver_name'
            )
            ->orderBy('atr.id', 'desc')
            ->get();

            $today = Carbon::today();

            // Enrich records with computed timeline and workflow metadata
            $enriched = $records->map(function ($r) use ($today) {
                $deadline = Carbon::parse($r->deadline_date);
                $isOverdue = ($r->status === 'pending' && $deadline->lt($today));
                $daysDiff = $today->diffInDays($deadline, false); // positive if future, negative if past

                $fullName = trim($r->staff_name);
                if (empty($fullName)) {
                    $fullName = trim(($r->surname ?? '') . ' ' . ($r->first_name ?? '') . ' ' . ($r->othernames ?? ''));
                }
                if (empty($fullName)) {
                    $fullName = 'Staff #' . $r->staff_id;
                }

                $staffObj = [
                    'id' => $r->staff_id,
                    'name' => $fullName,
                    'staff_id' => $r->staff_id,
                    'staff_id_code' => (string)$r->staff_id,
                    'department' => $r->department ?? 'General Operations',
                    'designation' => $r->designation ?? 'Staff',
                    'email' => $r->staff_email,
                    'phone' => $r->staff_phone,
                ];

                $settlementSummary = null;
                if (!empty($r->settlement_summary)) {
                    try {
                        $settlementSummary = is_array($r->settlement_summary) ? $r->settlement_summary : json_decode($r->settlement_summary, true);
                    } catch (\Throwable $e) {
                        $settlementSummary = null;
                    }
                }

                // If approved but settlement summary was missing or zero, compute live so Audit/Finance see real figures
                if (in_array($r->status, ['hr_approved', 'audit_approved', 'paid']) &&
                    (empty($settlementSummary) || ((float)($settlementSummary['total_earnings'] ?? 0) == 0 && (float)($settlementSummary['total_deductions'] ?? 0) == 0))) {
                    try {
                        $resCtrl = app(\App\Http\Controllers\Api\ResignationApiController::class);
                        $calc = null;
                        if ($r->resignation_request_id) {
                            $calc = $resCtrl->computeDetailedSettlement($r->resignation_request_id, true);
                        }
                        if ($calc && !empty($calc['settlement_summary'])) {
                            $sum = $calc['settlement_summary'];
                            $net = (float)($sum['net_settlement_amount'] ?? 0);
                            $settlementSummary = [
                                'net_settlement'      => $net,
                                'total_earnings'      => (float)($sum['total_final_earnings'] ?? 0),
                                'total_deductions'    => (float)($sum['total_final_deductions'] ?? 0),
                                'notice_earnings'     => (float)($sum['total_gross_notice_salary'] ?? 0),
                                'retention_balance'   => (float)($sum['total_retention_refund'] ?? 0),
                                'coop_savings_refund' => (float)($sum['total_coop_savings_refund'] ?? 0),
                                'loan_deductions'     => (float)($sum['total_final_deductions'] ?? 0),
                                'is_payable'          => $net >= 0,
                                'settlement_type'     => $sum['settlement_type'] ?? ($net >= 0 ? 'payable' : 'recoverable'),
                            ];
                            DB::table('advice_to_resigns')->where('id', $r->id)->update([
                                'settlement_summary' => json_encode($settlementSummary),
                            ]);
                        }
                    } catch (\Throwable $e) {
                        // ignore and continue
                    }
                }

                return [
                    'id' => $r->id,
                    'reference_no' => $r->reference_no,
                    'staff_id' => $r->staff_id,
                    'staff_name' => $fullName,
                    'department' => $r->department ?? 'General Operations',
                    'designation' => $r->designation ?? 'Staff',
                    'staff_email' => $r->staff_email,
                    'staff_phone' => $r->staff_phone,
                    'staff_status' => $r->staff_status,
                    'staff' => $staffObj,
                    'issue_date' => $r->issue_date,
                    'deadline_date' => $r->deadline_date,
                    'reason' => $r->reason,
                    'details' => $r->details,
                    'query_reference' => $r->query_reference,
                    'consequence_if_defaulted' => $r->consequence_if_defaulted,
                    'status' => $r->status,
                    'compliance_date' => $r->compliance_date,
                    'applied_date' => $r->applied_date,
                    'staff_remarks' => $r->staff_remarks,
                    'hr_approved_by' => $r->hr_approved_by,
                    'hr_approved_at' => $r->hr_approved_at,
                    'hr_approver_name' => $r->hr_approver_name,
                    'hr_remarks' => $r->hr_remarks,
                    'audit_status' => (int)($r->audit_status ?? 0),
                    'audit_by' => $r->audit_by,
                    'audit_at' => $r->audit_at,
                    'audit_approver_name' => $r->audit_approver_name,
                    'audit_remarks' => $r->audit_remarks,
                    'finance_status' => (int)($r->finance_status ?? 0),
                    'finance_by' => $r->finance_by,
                    'finance_at' => $r->finance_at,
                    'finance_approver_name' => $r->finance_approver_name,
                    'finance_remarks' => $r->finance_remarks,
                    'payment_reference' => $r->payment_reference,
                    'settlement_summary' => $settlementSummary,
                    'resignation_request_id' => $r->resignation_request_id,
                    'resolution_remarks' => $r->resolution_remarks,
                    'issued_by_name' => $r->issued_by_name ?? 'HR Management',
                    'action_by_name' => $r->action_by_name,
                    'action_date' => $r->action_date,
                    'created_at' => $r->created_at,
                    'is_overdue' => $isOverdue,
                    'days_remaining' => ($r->status === 'pending') ? max(0, $daysDiff) : null,
                    'days_overdue' => ($isOverdue) ? abs($daysDiff) : 0,
                ];
            });

            // Statistics across records (isolated if non-privileged)
            $allBase = DB::table('advice_to_resigns');
            if (!$isPrivileged && $ctx['employee']) {
                $allBase->where('staff_id', $ctx['employee']->ID);
            }
            $stats = [
                'total' => (clone $allBase)->count(),
                'pending' => (clone $allBase)->where('status', 'pending')->count(),
                'applied' => (clone $allBase)->where('status', 'applied')->count(),
                'hr_approved' => (clone $allBase)->where('status', 'hr_approved')->count(),
                'audit_approved' => (clone $allBase)->where('status', 'audit_approved')->count(),
                'audit_rejected' => (clone $allBase)->where('status', 'audit_rejected')->count(),
                'paid' => (clone $allBase)->where('status', 'paid')->count(),
                'complied' => (clone $allBase)->where('status', 'complied')->count(),
                'terminated' => (clone $allBase)->where('status', 'terminated')->count(),
                'withdrawn' => (clone $allBase)->where('status', 'withdrawn')->count(),
                'overdue' => (clone $allBase)->where('status', 'pending')->where('deadline_date', '<', Carbon::today()->toDateString())->count(),
            ];

            return response()->json([
                'status' => 'success',
                'data' => [
                    'records' => $enriched,
                    'stats' => $stats,
                    'reasons' => self::REASONS,
                    'user_permissions' => $userPerms
                ],
                'records' => $enriched,
                'stats' => $stats,
                'reasons' => self::REASONS,
                'user_permissions' => $userPerms
            ]);
        } catch (\Throwable $th) {
            Log::error('AdviceToResignApiController index: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to load advice records.'], 500);
        }
    }

    /**
     * POST /api/nextjs/advice-to-resign
     * Issue a new Advice to Resign.
     */
    public function store(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $activeRole = strtolower(trim($request->header('X-User-Role', '')));
            $isHr = !empty($ctx['isSuperAdmin']) || !empty($ctx['adminStaff']) || in_array($activeRole, ['hr head', 'head of hr', 'super admin', 'super administrator']);
            if (!$isHr) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: Only Super Admin and HR Head can issue Advice to Resign notices.'], 403);
            }

            $validated = $request->validate([
                'staff_id' => 'required|integer',
                'issue_date' => 'required|date',
                'deadline_date' => 'required|date|after_or_equal:issue_date',
                'reason' => 'required|string|max:255',
                'details' => 'nullable|string',
                'query_reference' => 'nullable|string|max:100',
                'consequence_if_defaulted' => 'nullable|string|max:255',
            ]);

            // Verify staff exists and is active
            $staff = DB::table('tblper')->where('ID', $validated['staff_id'])->first();
            if (!$staff) {
                return response()->json(['status' => 'error', 'message' => 'Staff record not found.'], 404);
            }

            // Check if there is already an active pending advice for this staff
            $existingPending = DB::table('advice_to_resigns')
                ->where('staff_id', $validated['staff_id'])
                ->where('status', 'pending')
                ->first();

            if ($existingPending) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'An active pending Advice to Resign already exists for this staff member (' . $existingPending->reference_no . ').'
                ], 422);
            }

            // Generate Reference Number: IHL/ATR/YYYY/XXXX
            $year = Carbon::parse($validated['issue_date'])->year;
            $countThisYear = DB::table('advice_to_resigns')
                ->whereYear('issue_date', $year)
                ->count() + 1;
            $referenceNo = sprintf("IHL/ATR/%04d/%04d", $year, $countThisYear);

            // Double check uniqueness
            while (DB::table('advice_to_resigns')->where('reference_no', $referenceNo)->exists()) {
                $countThisYear++;
                $referenceNo = sprintf("IHL/ATR/%04d/%04d", $year, $countThisYear);
            }

            $id = DB::table('advice_to_resigns')->insertGetId([
                'reference_no' => $referenceNo,
                'staff_id' => $validated['staff_id'],
                'issue_date' => $validated['issue_date'],
                'deadline_date' => $validated['deadline_date'],
                'reason' => $validated['reason'],
                'details' => $validated['details'] ?? null,
                'query_reference' => $validated['query_reference'] ?? null,
                'consequence_if_defaulted' => !empty($validated['consequence_if_defaulted']) ? $validated['consequence_if_defaulted'] : 'Immediate Termination of Employment without Notice',
                'status' => 'pending',
                'issued_by' => $ctx['userId'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $staffName = trim(($staff->surname ?? '') . ' ' . ($staff->first_name ?? '') . ' ' . ($staff->othernames ?? ''));
            $this->logActivity($ctx, 'create', "Issued Advice to Resign ({$referenceNo}) to staff {$staffName}", $validated, $validated['staff_id']);

            return response()->json([
                'status' => 'success',
                'message' => 'Advice to Resign issued successfully with Reference: ' . $referenceNo,
                'id' => $id,
                'reference_no' => $referenceNo
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json(['status' => 'error', 'message' => $ve->validator->errors()->first()], 422);
        } catch (\Throwable $th) {
            Log::error('AdviceToResignApiController store: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to issue advice: ' . $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/advice-to-resign/{id}
     * Get single record details.
     */
    public function show(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $record = DB::table('advice_to_resigns as atr')
                ->join('tblper as p', 'p.ID', '=', 'atr.staff_id')
                ->leftJoin('tbldepartment as dept', 'dept.id', '=', 'p.departmentID')
                ->leftJoin('tbldesignation as des', 'des.id', '=', 'p.designation')
                ->leftJoin('users as issuer', 'issuer.id', '=', 'atr.issued_by')
                ->leftJoin('users as actor', 'actor.id', '=', 'atr.action_by')
                ->leftJoin('users as hr_approver', 'hr_approver.id', '=', 'atr.hr_approved_by')
                ->leftJoin('users as audit_approver', 'audit_approver.id', '=', 'atr.audit_by')
                ->leftJoin('users as finance_approver', 'finance_approver.id', '=', 'atr.finance_by')
                ->where('atr.id', $id)
                ->select(
                    'atr.*',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as staff_name"),
                    'p.email as staff_email',
                    'p.phone as staff_phone',
                    'p.appointment_date',
                    'p.doj',
                    'dept.department',
                    'des.designation',
                    'issuer.name as issued_by_name',
                    'actor.name as action_by_name',
                    'hr_approver.name as hr_approver_name',
                    'audit_approver.name as audit_approver_name',
                    'finance_approver.name as finance_approver_name'
                )
                ->first();

            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
            }

            $fullName = trim($record->staff_name);
            if (empty($fullName)) {
                $fullName = trim(($record->surname ?? '') . ' ' . ($record->first_name ?? '') . ' ' . ($record->othernames ?? ''));
            }
            if (empty($fullName)) {
                $fullName = 'Staff #' . $record->staff_id;
            }

            $record->staff_name = $fullName;
            $record->staff = [
                'id' => $record->staff_id,
                'name' => $fullName,
                'staff_id' => $record->staff_id,
                'staff_id_code' => (string)$record->staff_id,
                'department' => $record->department ?? 'General Operations',
                'designation' => $record->designation ?? 'Staff',
                'email' => $record->staff_email,
                'phone' => $record->staff_phone,
            ];

            if (!empty($record->settlement_summary)) {
                try {
                    $record->settlement_summary = is_array($record->settlement_summary) ? $record->settlement_summary : json_decode($record->settlement_summary, true);
                } catch (\Throwable $e) {
                    $record->settlement_summary = null;
                }
            }

            $settlement = null;
            if ($record->resignation_request_id) {
                try {
                    $resignationCtrl = app(\App\Http\Controllers\Api\ResignationApiController::class);
                    $settlement = $resignationCtrl->computeDetailedSettlement($record->resignation_request_id, true);
                    if ($settlement && !empty($settlement['settlement_summary'])) {
                        $sum = $settlement['settlement_summary'];
                        $net = (float)($sum['net_settlement_amount'] ?? 0);
                        $record->settlement_summary = [
                            'net_settlement'      => $net,
                            'total_earnings'      => (float)($sum['total_final_earnings'] ?? 0),
                            'total_deductions'    => (float)($sum['total_final_deductions'] ?? 0),
                            'notice_earnings'     => (float)($sum['total_gross_notice_salary'] ?? 0),
                            'retention_balance'   => (float)($sum['total_retention_refund'] ?? 0),
                            'coop_savings_refund' => (float)($sum['total_coop_savings_refund'] ?? 0),
                            'loan_deductions'     => (float)($sum['total_final_deductions'] ?? 0),
                            'is_payable'          => $net >= 0,
                            'settlement_type'     => $sum['settlement_type'] ?? ($net >= 0 ? 'payable' : 'recoverable'),
                        ];
                    }
                } catch (\Throwable $th) {
                    Log::warning('computeDetailedSettlement in show error: ' . $th->getMessage());
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => $record,
                'settlement' => $settlement,
                'linked_resignation' => $settlement
            ]);
        } catch (\Throwable $th) {
            Log::error('AdviceToResignApiController show: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch details.'], 500);
        }
    }

    /**
     * PUT /api/nextjs/advice-to-resign/{id}
     * Update pending Advice to Resign details.
     */
    public function update(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $activeRole = strtolower(trim($request->header('X-User-Role', '')));
            $isHr = !empty($ctx['isSuperAdmin']) || !empty($ctx['adminStaff']) || in_array($activeRole, ['hr head', 'head of hr', 'super admin', 'super administrator']);
            if (!$isHr) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: Only Super Admin and HR Head can modify Advice to Resign notices.'], 403);
            }

            $record = DB::table('advice_to_resigns')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
            }

            if ($record->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot edit an advice record that is already ' . strtoupper($record->status) . '.'
                ], 422);
            }

            $validated = $request->validate([
                'issue_date' => 'required|date',
                'deadline_date' => 'required|date|after_or_equal:issue_date',
                'reason' => 'required|string|max:255',
                'details' => 'nullable|string',
                'query_reference' => 'nullable|string|max:100',
                'consequence_if_defaulted' => 'nullable|string|max:255',
            ]);

            DB::table('advice_to_resigns')->where('id', $id)->update([
                'issue_date' => $validated['issue_date'],
                'deadline_date' => $validated['deadline_date'],
                'reason' => $validated['reason'],
                'details' => $validated['details'] ?? null,
                'query_reference' => $validated['query_reference'] ?? null,
                'consequence_if_defaulted' => $validated['consequence_if_defaulted'] ?: 'Immediate Termination of Employment without Notice',
                'updated_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Advice to Resign updated successfully.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json(['status' => 'error', 'message' => $ve->validator->errors()->first()], 422);
        } catch (\Throwable $th) {
            Log::error('AdviceToResignApiController update: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to update advice: ' . $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/advice-to-resign/{id}/action
     * Record outcome/action: complied, terminated, or withdrawn.
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $activeRole = strtolower(trim($request->header('X-User-Role', '')));
            $isHr = !empty($ctx['isSuperAdmin']) || !empty($ctx['adminStaff']) || in_array($activeRole, ['hr head', 'head of hr', 'super admin', 'super administrator']);
            if (!$isHr) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: Only Super Admin and HR Head can record outcomes or terminate appointment.'], 403);
            }

            $record = DB::table('advice_to_resigns')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
            }

            if ($record->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This record has already been processed as ' . strtoupper($record->status) . '.'
                ], 422);
            }

            $action = strtolower(trim($request->input('action', $request->input('status', ''))));
            if (!in_array($action, ['complied', 'terminated', 'withdrawn'])) {
                return response()->json(['status' => 'error', 'message' => 'Invalid action specified. Must be complied, terminated, or withdrawn.'], 422);
            }

            $remarks = trim($request->input('remarks', $request->input('resolution_remarks', '')));
            $complianceDate = $request->input('compliance_date', Carbon::today()->toDateString());
            $convertToResignation = (bool)$request->input('convert_to_resignation', false);

            $resignationId = null;

            DB::beginTransaction();

            if ($action === 'complied') {
                // If requested, seamlessly convert and insert into resignation_requests
                if ($convertToResignation) {
                    $resignationId = DB::table('resignation_requests')->insertGetId([
                        'staff_id' => $record->staff_id,
                        'reason' => 'Advised to Resign: ' . $record->reason . (!empty($remarks) ? " - {$remarks}" : ''),
                        'resignation_date' => $complianceDate,
                        'status' => 1,
                        'hod_status' => 1,
                        'hod_id' => $ctx['userId'] ?? 1,
                        'hod_date' => now(),
                        'admin_status' => 1, // Directly HR Approved
                        'admin_id' => $ctx['userId'] ?? 1,
                        'admin_date' => now(),
                        'remarks' => "Initiated via Advice to Resign ref: {$record->reference_no}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('advice_to_resigns')->where('id', $id)->update([
                    'status' => 'complied',
                    'compliance_date' => $complianceDate,
                    'resignation_request_id' => $resignationId,
                    'resolution_remarks' => $remarks ?: 'Staff complied with Advice to Resign and tendered resignation.',
                    'action_by' => $ctx['userId'] ?? null,
                    'action_date' => now(),
                    'updated_at' => now(),
                ]);

                // Update staff status value
                DB::table('tblper')->where('ID', $record->staff_id)->update([
                    'status_value' => 'resignation',
                    'updated_at' => now(),
                ]);

                $message = 'Staff marked as Complied (Resigned).' . ($resignationId ? ' Linked Resignation Request #' . $resignationId . ' created and submitted to Exit Settlement registry.' : '');
            } elseif ($action === 'terminated') {
                // Staff defaulted and is formally terminated
                DB::table('advice_to_resigns')->where('id', $id)->update([
                    'status' => 'terminated',
                    'compliance_date' => $complianceDate,
                    'resolution_remarks' => $remarks ?: 'Staff failed to comply within stipulated deadline. Appointment terminated as indicated in Advice Notice.',
                    'action_by' => $ctx['userId'] ?? null,
                    'action_date' => now(),
                    'updated_at' => now(),
                ]);

                // Deactivate staff in tblper
                DB::table('tblper')->where('ID', $record->staff_id)->update([
                    'staff_status' => 0,
                    'status_value' => 'terminated',
                    'updated_at' => now(),
                ]);

                $message = 'Staff marked as Defaulted. Appointment terminated and staff deactivated.';
            } else {
                // Withdrawn
                DB::table('advice_to_resigns')->where('id', $id)->update([
                    'status' => 'withdrawn',
                    'resolution_remarks' => $remarks ?: 'Advice to Resign withdrawn by Management.',
                    'action_by' => $ctx['userId'] ?? null,
                    'action_date' => now(),
                    'updated_at' => now(),
                ]);

                $message = 'Advice to Resign has been withdrawn.';
            }

            DB::commit();

            $this->logActivity($ctx, 'update', "Processed Advice to Resign outcome: {$action} for ref {$record->reference_no}", ['action' => $action, 'remarks' => $remarks], $record->staff_id);

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'resignation_id' => $resignationId
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdviceToResignApiController updateStatus: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Action failed: ' . $th->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/nextjs/advice-to-resign/{id}
     * Delete an advice record (only pending records).
     */
    public function destroy(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $activeRole = strtolower(trim($request->header('X-User-Role', '')));
            $isHr = !empty($ctx['isSuperAdmin']) || !empty($ctx['adminStaff']) || in_array($activeRole, ['hr head', 'head of hr', 'super admin', 'super administrator']);
            if (!$isHr) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: Only Super Admin and HR Head can delete Advice to Resign notices.'], 403);
            }

            $record = DB::table('advice_to_resigns')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
            }

            if ($record->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete an advice record that has already been ' . strtoupper($record->status) . '.'
                ], 422);
            }

            DB::table('advice_to_resigns')->where('id', $id)->delete();

            $this->logActivity($ctx, 'delete', "Deleted Advice to Resign ref {$record->reference_no}", $record, $record->staff_id);

            return response()->json([
                'status' => 'success',
                'message' => 'Advice to Resign record deleted successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('AdviceToResignApiController destroy: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to delete record.'], 500);
        }
    }

    /**
     * GET /api/nextjs/advice-to-resign/{id}/letter
     * Payload for generating and printing official Advice to Resign Letter.
     */
    public function getLetterData(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $record = DB::table('advice_to_resigns as atr')
                ->join('tblper as p', 'p.ID', '=', 'atr.staff_id')
                ->leftJoin('tbldepartment as dept', 'dept.id', '=', 'p.departmentID')
                ->leftJoin('tbldesignation as des', 'des.id', '=', 'p.designation')
                ->where('atr.id', $id)
                ->select(
                    'atr.*',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as staff_name"),
                    'p.email as staff_email',
                    'p.phone as staff_phone',
                    'p.appointment_date',
                    'p.doj',
                    'dept.department',
                    'des.designation'
                )
                ->first();

            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
            }

            // Retrieve authorized HR Head name and signature
            // 1. Strictly search for user assigned to HR HEAD / HEAD OF HR role
            $hrUser = DB::table('users')
                ->join('assign_user_role', 'assign_user_role.userID', '=', 'users.id')
                ->join('user_role', 'user_role.roleID', '=', 'assign_user_role.roleID')
                ->where(function ($q) {
                    $q->where('user_role.rolename', 'like', '%HR HEAD%')
                      ->orWhere('user_role.rolename', 'like', '%HEAD OF HR%');
                })
                ->select('users.id', 'users.name', 'users.signature', 'user_role.rolename')
                ->orderBy('users.id', 'desc')
                ->first();

            // 2. If not found via assign_user_role, check if record was approved by an HR Head
            if (!$hrUser && !empty($record->hr_approved_by)) {
                $hrUser = DB::table('users')
                    ->where('id', $record->hr_approved_by)
                    ->select('users.id', 'users.name', 'users.signature')
                    ->first();
            }

            // 3. If not found, check tblper for HOD or Staff of Human Resource department (departmentID = 80)
            if (!$hrUser) {
                $hrStaff = DB::table('tblper')
                    ->leftJoin('tbldepartment', 'tbldepartment.id', '=', 'tblper.departmentID')
                    ->where(function ($q) {
                        $q->where('tblper.departmentID', 80)
                          ->orWhere('tbldepartment.department', 'like', '%HUMAN RESOURCE%')
                          ->orWhere('tbldepartment.department', 'like', '%HR%');
                    })
                    ->orderBy('tblper.is_hod', 'desc')
                    ->select(
                        'tblper.ID as id',
                        DB::raw("CONCAT(tblper.surname, ' ', tblper.first_name, ' ', COALESCE(tblper.othernames, '')) as name"),
                        'tblper.signature_url as signature'
                    )
                    ->first();

                if ($hrStaff && trim($hrStaff->name)) {
                    $hrUser = (object)[
                        'id' => $hrStaff->id,
                        'name' => trim($hrStaff->name),
                        'signature' => $hrStaff->signature,
                        'rolename' => 'HR HEAD'
                    ];
                }
            }

            // 4. Fallback to established hospital HR Head name
            $hrName = ($hrUser && trim($hrUser->name)) ? trim($hrUser->name) : 'ANIFOWOSHE MONSURAT';

            // 5. Retrieve authorized HR Head signature
            $hrSignature = null;
            if (!empty($hrUser->signature)) {
                $hrSignature = $hrUser->signature;
            }
            if (!$hrSignature) {
                // Check user 10210 (ANIFOWOSHE MONSURAT) directly
                $hrSignature = DB::table('users')->where('id', 10210)->whereNotNull('signature')->value('signature');
            }
            if (!$hrSignature) {
                // Check users with role HR HEAD
                $hrSignature = DB::table('users')
                    ->join('assign_user_role', 'assign_user_role.userID', '=', 'users.id')
                    ->join('user_role', 'user_role.roleID', '=', 'assign_user_role.roleID')
                    ->where('user_role.rolename', 'like', '%HR HEAD%')
                    ->whereNotNull('users.signature')
                    ->where('users.signature', '!=', '')
                    ->value('users.signature');
            }
            if (!$hrSignature) {
                // Fallback to official corporate administrator signature in users
                $hrSignature = DB::table('users')
                    ->whereNotNull('signature')
                    ->where('signature', '!=', '')
                    ->value('signature');
            }

            $issueDateCarbon = Carbon::parse($record->issue_date);
            $deadlineCarbon = Carbon::parse($record->deadline_date);

            $orgData = [
                'name' => 'ISALU HOSPITALS LIMITED',
                'sub_title' => 'Excellence in Comprehensive Healthcare & Clinical Services',
                'tagline' => 'Excellence in Healthcare Delivery & Clinical Services',
                'address' => 'Plot 11, Isalu Way, off Secretariat Road, Ikeja, Lagos, Nigeria',
                'phone' => '+234 1 234 5678 / +234 803 000 1122',
                'email' => 'hr@isaluhospitals.com | info@isaluhospitals.com',
                'website' => 'www.isaluhospitals.com',
            ];

            $letterPayload = [
                'organization' => $orgData,
                'hospital' => $orgData,
                'reference_no' => $record->reference_no,
                'letter_date' => $issueDateCarbon->format('F d, Y'),
                'issue_date_formatted' => $issueDateCarbon->format('F d, Y'),
                'deadline_date' => $deadlineCarbon->format('F d, Y'),
                'deadline_date_formatted' => $deadlineCarbon->format('F d, Y'),
                'staff' => [
                    'id' => $record->staff_id,
                    'staff_id' => $record->staff_id,
                    'staff_id_code' => (string)$record->staff_id,
                    'name' => trim($record->staff_name),
                    'department' => $record->department ?? 'General Operations',
                    'designation' => $record->designation ?? 'Staff',
                ],
                'query_reference' => $record->query_reference,
                'reason' => $record->reason,
                'details' => $record->details,
                'consequence' => $record->consequence_if_defaulted,
                'consequence_if_defaulted' => $record->consequence_if_defaulted,
                'status' => $record->status,
                'compliance_date' => $record->compliance_date ? Carbon::parse($record->compliance_date)->format('F d, Y') : null,
                'resolution_remarks' => $record->resolution_remarks,
                'signatory' => [
                    'name' => $hrName,
                    'hr_name' => $hrName,
                    'title' => 'Head of Human Resources & Corporate Services',
                    'hr_title' => 'Head of Human Resources & Corporate Services',
                    'md_title' => 'Medical Director / CEO',
                    'signature_url' => $hrSignature,
                ]
            ];

            return response()->json([
                'status' => 'success',
                'data' => $letterPayload,
                'letter' => $letterPayload
            ]);
        } catch (\Throwable $th) {
            Log::error('AdviceToResignApiController getLetterData: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to generate letter.'], 500);
        }
    }

    /**
     * POST /api/nextjs/advice-to-resign/{id}/staff-apply
     * Staff applies / tenders voluntary resignation pursuant to Advice Notice.
     */
    public function staffApply(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $record = DB::table('advice_to_resigns')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
            }

            if (!in_array($record->status, ['pending', 'audit_rejected', 'applied'])) {
                return response()->json(['status' => 'error', 'message' => 'Advice record is not in a submittable state (' . strtoupper($record->status) . ').'], 422);
            }

            $activeRole = strtolower(trim($request->header('X-User-Role', '')));
            $isSuperAdmin = !empty($ctx['isSuperAdmin']);
            $isHr = $isSuperAdmin || !empty($ctx['adminStaff']) || in_array($activeRole, ['hr head', 'head of hr', 'super admin', 'super administrator']);
            $userStaffId = $ctx['employee'] ? $ctx['employee']->ID : null;
            $isTargetStaff = ($userStaffId && (int)$userStaffId === (int)$record->staff_id);
            if (!$isTargetStaff && !$isHr) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: You can only tender resignation for your own Advice to Resign notice.'], 403);
            }

            $appliedDate = $request->input('applied_date', Carbon::today()->toDateString());
            $staffRemarks = trim($request->input('staff_remarks', $request->input('remarks', '')));
            if (empty($staffRemarks)) {
                $staffRemarks = 'Formal voluntary resignation tendered pursuant to Notice of Advice to Resign (' . $record->reference_no . ').';
            }

            DB::beginTransaction();

            DB::table('advice_to_resigns')->where('id', $id)->update([
                'status' => 'applied',
                'applied_date' => $appliedDate,
                'compliance_date' => $appliedDate,
                'staff_remarks' => $staffRemarks,
                'resolution_remarks' => 'Staff tendered voluntary resignation pursuant to Notice. Awaiting HR Head review & approval.',
                'action_by' => $ctx['userId'] ?? null,
                'action_date' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            $this->logActivity($ctx, 'apply', "Staff tendered resignation for Advice ref {$record->reference_no}", ['applied_date' => $appliedDate, 'remarks' => $staffRemarks], $record->staff_id);

            return response()->json([
                'status' => 'success',
                'message' => "Voluntary resignation application successfully submitted for {$record->reference_no}. Forwarded to HR Head for approval.",
                'data' => [
                    'id' => $record->id,
                    'status' => 'applied',
                    'applied_date' => $appliedDate,
                ]
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdviceToResignApiController staffApply: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to submit application: ' . $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/advice-to-resign/{id}/hr-approve
     * HR Head approves resignation application.
     * When HR Head approves:
     *  1. Remove the staff from payroll (tblper.staff_status = 0, status_value = 'resignation')
     *  2. Calculate all his settlement (full settlement breakdown via Resignation engine)
     *  3. Status updates to 'hr_approved', forwarded to Audit Head.
     */
    public function hrApprove(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $activeRole = strtolower(trim($request->header('X-User-Role', '')));
            $isHr = !empty($ctx['isSuperAdmin']) || !empty($ctx['adminStaff']) || in_array($activeRole, ['hr head', 'head of hr', 'super admin', 'super administrator']);
            if (!$isHr) {
                return response()->json(['status' => 'error', 'message' => 'HR Head or delegated administrative privileges required to approve resignation.'], 403);
            }

            $record = DB::table('advice_to_resigns')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
            }

            if (!in_array($record->status, ['applied', 'pending', 'audit_rejected'])) {
                return response()->json(['status' => 'error', 'message' => 'This record cannot be HR approved in its current state (' . strtoupper($record->status) . ').'], 422);
            }

            $effectiveExitDate = $request->input('effective_exit_date', $record->applied_date ?: Carbon::today()->toDateString());
            $hrRemarks = trim($request->input('hr_remarks', $request->input('remarks', '')));
            if (empty($hrRemarks)) {
                $hrRemarks = 'Advice to Resign accepted and approved by HR Head. Immediate exit settlement authorized.';
            }

            DB::beginTransaction();

            // 1. Immediately remove the staff from payroll (staff_status = 0)
            DB::table('tblper')->where('ID', $record->staff_id)->update([
                'staff_status' => 0,
                'status_value' => 'resignation',
                'updated_at' => now(),
            ]);

            // 2. Insert or update resignation_requests record
            $resignationId = $record->resignation_request_id;
            if (!$resignationId || !DB::table('resignation_requests')->where('id', $resignationId)->exists()) {
                $resignationId = DB::table('resignation_requests')->insertGetId([
                    'staff_id' => $record->staff_id,
                    'resignation_date' => $effectiveExitDate,
                    'reason' => 'Advice to Resign Compliance: ' . $record->reason,
                    'remarks' => $hrRemarks,
                    'status' => 1, // Approved
                    'hod_status' => 1,
                    'hod_id' => $ctx['userId'] ?? 1,
                    'hod_date' => now(),
                    'admin_status' => 1,
                    'admin_id' => $ctx['userId'] ?? 1,
                    'admin_date' => now(),
                    'audit_status' => 0, // Pending Audit
                    'finance_status' => 0, // Pending Finance
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('resignation_requests')->where('id', $resignationId)->update([
                    'resignation_date' => $effectiveExitDate,
                    'status' => 1,
                    'admin_status' => 1,
                    'admin_id' => $ctx['userId'] ?? 1,
                    'admin_date' => now(),
                    'audit_status' => 0,
                    'finance_status' => 0,
                    'remarks' => $hrRemarks,
                    'updated_at' => now(),
                ]);
            }

            // 3. Calculate all his settlement
            $resignationCtrl = app(\App\Http\Controllers\Api\ResignationApiController::class);
            $settlementData = $resignationCtrl->computeDetailedSettlement($resignationId, true);

            $summary = null;
            if ($settlementData) {
                $sum = $settlementData['settlement_summary'] ?? [];
                $netAmt = (float)($sum['net_settlement_amount'] ?? ($settlementData['net_settlement'] ?? 0));
                $earningsAmt = (float)($sum['total_final_earnings'] ?? ($settlementData['total_earnings'] ?? 0));
                $deductionsAmt = (float)($sum['total_final_deductions'] ?? ($settlementData['total_deductions'] ?? 0));

                $summary = [
                    'net_settlement'      => $netAmt,
                    'total_earnings'      => $earningsAmt,
                    'total_deductions'    => $deductionsAmt,
                    'notice_earnings'     => (float)($sum['total_gross_notice_salary'] ?? 0),
                    'retention_balance'   => (float)($sum['total_retention_refund'] ?? 0),
                    'coop_savings_refund' => (float)($sum['total_coop_savings_refund'] ?? 0),
                    'loan_deductions'     => $deductionsAmt,
                    'is_payable'          => $netAmt >= 0,
                    'settlement_type'     => $sum['settlement_type'] ?? ($netAmt >= 0 ? 'payable' : 'recoverable'),
                ];
            }

            // 4. Update advice_to_resigns record
            DB::table('advice_to_resigns')->where('id', $id)->update([
                'status' => 'hr_approved',
                'compliance_date' => $effectiveExitDate,
                'resignation_request_id' => $resignationId,
                'hr_approved_by' => $ctx['userId'] ?? null,
                'hr_approved_at' => now(),
                'hr_remarks' => $hrRemarks,
                'audit_status' => 0,
                'finance_status' => 0,
                'settlement_summary' => $summary ? json_encode($summary) : null,
                'resolution_remarks' => 'Approved by HR Head. Staff deactivated from monthly payroll. Exit settlement calculated and submitted to Audit Head.',
                'action_by' => $ctx['userId'] ?? null,
                'action_date' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            $this->logActivity($ctx, 'approve', "HR Head approved Advice to Resign for ref {$record->reference_no}. Staff removed from payroll.", ['settlement' => $summary], $record->staff_id);

            return response()->json([
                'status' => 'success',
                'message' => 'Resignation approved by HR Head. Staff member removed from active payroll. Complete exit settlement computed and forwarded to Audit Head.',
                'data' => [
                    'id' => $record->id,
                    'status' => 'hr_approved',
                    'resignation_request_id' => $resignationId,
                    'settlement_summary' => $summary,
                    'settlement_data' => $settlementData,
                ]
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdviceToResignApiController hrApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to approve resignation: ' . $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/advice-to-resign/{id}/audit-review
     * Audit Head reviews settlement.
     */
    public function auditReview(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $activeRole = strtolower(trim($request->header('X-User-Role', '')));
            $isAudit = !empty($ctx['isSuperAdmin']) || !empty($ctx['isAuditStaff']) || in_array($activeRole, ['audit head', 'head of audit', 'super admin', 'super administrator']);
            if (!$isAudit) {
                return response()->json(['status' => 'error', 'message' => 'Audit Head or delegated audit privileges required.'], 403);
            }

            $record = DB::table('advice_to_resigns')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
            }

            if (!in_array($record->status, ['hr_approved', 'audit_rejected'])) {
                return response()->json(['status' => 'error', 'message' => 'This record is not awaiting audit review (' . strtoupper($record->status) . ').'], 422);
            }

            $action = strtolower(trim($request->input('action', 'approve')));
            $remarks = trim($request->input('audit_remarks', $request->input('remarks', '')));

            if ($action === 'reject' && empty($remarks)) {
                return response()->json(['status' => 'error', 'message' => 'Audit remarks explaining query / objection are required.'], 422);
            }

            DB::beginTransaction();

            $isApproved = ($action === 'approve');
            $newStatus = $isApproved ? 'audit_approved' : 'audit_rejected';
            $auditStatusCode = $isApproved ? 1 : 2;

            DB::table('advice_to_resigns')->where('id', $id)->update([
                'status' => $newStatus,
                'audit_status' => $auditStatusCode,
                'audit_by' => $ctx['userId'] ?? null,
                'audit_at' => now(),
                'audit_remarks' => $remarks ?: ($isApproved ? 'Settlement verified, reconciled and approved by Internal Audit.' : 'Queried by Audit.'),
                'resolution_remarks' => $isApproved
                    ? 'Audit Head verified and approved settlement. Ready for Finance disbursement.'
                    : 'Audit Head queried settlement: ' . $remarks,
                'updated_at' => now(),
            ]);

            if ($record->resignation_request_id) {
                DB::table('resignation_requests')->where('id', $record->resignation_request_id)->update([
                    'audit_status' => $auditStatusCode,
                    'audit_id' => $ctx['userId'] ?? null,
                    'audit_date' => now(),
                    'audit_remarks' => $remarks ?: ($isApproved ? 'Approved by Audit.' : 'Queried by Audit.'),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            $msg = $isApproved
                ? 'Exit settlement audit verified & approved. Cleared for Finance Head payment.'
                : 'Exit settlement queried / returned by Audit Head with remarks.';

            $this->logActivity($ctx, 'audit', "Audit Head {$action}d settlement for Advice ref {$record->reference_no}", ['remarks' => $remarks], $record->staff_id);

            return response()->json([
                'status' => 'success',
                'message' => $msg,
                'data' => [
                    'id' => $record->id,
                    'status' => $newStatus,
                    'audit_status' => $auditStatusCode,
                    'audit_remarks' => $remarks,
                ]
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdviceToResignApiController auditReview: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to process audit review: ' . $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/advice-to-resign/{id}/finance-pay
     * Finance Head executes / disburses exit settlement payment.
     */
    public function financePay(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $activeRole = strtolower(trim($request->header('X-User-Role', '')));
            $isFinance = !empty($ctx['isSuperAdmin']) || !empty($ctx['isFinanceStaff']) || in_array($activeRole, ['finance head', 'head of finance', 'super admin', 'super administrator']);
            if (!$isFinance) {
                return response()->json(['status' => 'error', 'message' => 'Finance Head or delegated finance privileges required to disburse payment.'], 403);
            }

            $record = DB::table('advice_to_resigns')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
            }

            if ($record->audit_status != 1 && !in_array($record->status, ['audit_approved', 'hr_approved'])) {
                return response()->json(['status' => 'error', 'message' => 'This record must be Audit Approved before Finance can disburse payment.'], 422);
            }

            $paymentRef = trim($request->input('payment_reference', ''));
            if (empty($paymentRef)) {
                return response()->json(['status' => 'error', 'message' => 'Payment reference number / transaction ID is required.'], 422);
            }

            $paymentDate = $request->input('payment_date', Carbon::today()->toDateString());
            $financeRemarks = trim($request->input('finance_remarks', $request->input('remarks', '')));

            DB::beginTransaction();

            DB::table('advice_to_resigns')->where('id', $id)->update([
                'status' => 'paid',
                'finance_status' => 1,
                'finance_by' => $ctx['userId'] ?? null,
                'finance_at' => $paymentDate . ' ' . now()->toTimeString(),
                'payment_reference' => $paymentRef,
                'finance_remarks' => $financeRemarks,
                'resolution_remarks' => "Exit settlement payment disbursed successfully by Finance. Ref: {$paymentRef}",
                'updated_at' => now(),
            ]);

            if ($record->resignation_request_id) {
                DB::table('resignation_requests')->where('id', $record->resignation_request_id)->update([
                    'finance_status' => 1,
                    'finance_id' => $ctx['userId'] ?? null,
                    'finance_date' => $paymentDate,
                    'payment_reference' => $paymentRef,
                    'finance_remarks' => $financeRemarks,
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            $this->logActivity($ctx, 'finance_pay', "Finance Head paid exit settlement for Advice ref {$record->reference_no} (Ref: {$paymentRef})", ['payment_reference' => $paymentRef], $record->staff_id);

            return response()->json([
                'status' => 'success',
                'message' => "Exit settlement payment recorded and disbursed successfully. Voucher/Ref: {$paymentRef}",
                'data' => [
                    'id' => $record->id,
                    'status' => 'paid',
                    'finance_status' => 1,
                    'payment_reference' => $paymentRef,
                ]
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdviceToResignApiController financePay: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to record payment: ' . $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/advice-to-resign/{id}/settlement
     * Detailed Exit Settlement Breakdown for Advice to Resign.
     */
    public function getSettlement(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $record = DB::table('advice_to_resigns')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
            }

            $resignationCtrl = app(\App\Http\Controllers\Api\ResignationApiController::class);

            $settlement = null;
            if ($record->resignation_request_id) {
                $settlement = $resignationCtrl->computeDetailedSettlement($record->resignation_request_id, true);
            }

            if (!$settlement) {
                // If not yet approved or linked, calculate a preview directly for the staff member
                $tempResId = DB::table('resignation_requests')->insertGetId([
                    'staff_id' => $record->staff_id,
                    'resignation_date' => $record->compliance_date ?: ($record->applied_date ?: Carbon::today()->toDateString()),
                    'reason' => 'Advice to Resign Preview: ' . $record->reason,
                    'status' => 1,
                    'admin_status' => 1,
                    'hod_status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $settlement = $resignationCtrl->computeDetailedSettlement($tempResId, true);
                // Clean up preview row
                DB::table('resignation_requests')->where('id', $tempResId)->delete();
            }

            if ($settlement) {
                $hrUser = $record->hr_approved_by ? DB::table('users')->where('id', $record->hr_approved_by)->value('name') : null;
                $auditUser = $record->audit_by ? DB::table('users')->where('id', $record->audit_by)->value('name') : null;
                $financeUser = $record->finance_by ? DB::table('users')->where('id', $record->finance_by)->value('name') : null;

                $isHrApproved = in_array($record->status, ['hr_approved', 'audit_approved', 'paid']) || !empty($record->hr_approved_at);
                $payrollStatus = $isHrApproved ? 'Removed from Active Payroll' : 'Current Month Active on Regular Payroll';
                $settlement['staff']['id'] = $record->staff_id;
                $settlement['staff']['staff_id'] = $record->staff_id;
                $settlement['staff']['file_no'] = (string)$record->staff_id; // Use StaffID
                $settlement['staff']['payroll_status'] = $payrollStatus;
                $settlement['timeline']['payroll_status'] = $payrollStatus;
                $settlement['clearance_workflow'] = [
                    'hr_approval' => [
                        'status'      => $isHrApproved ? 1 : 0,
                        'approved_at' => $record->hr_approved_at,
                        'approved_by' => $hrUser ?: 'HR Head',
                        'remarks'     => $record->hr_remarks,
                    ],
                    'audit_approval' => [
                        'status'      => (int)($record->audit_status ?? 0),
                        'audited_at'  => $record->audit_at,
                        'audited_by'  => $auditUser ?: 'Audit Head',
                        'remarks'     => $record->audit_remarks,
                    ],
                    'finance_payment' => [
                        'status'            => (int)($record->finance_status ?? 0),
                        'paid_at'           => $record->finance_at,
                        'paid_by'           => $financeUser ?: 'Finance Head',
                        'payment_reference' => $record->payment_reference,
                        'remarks'           => $record->finance_remarks,
                    ],
                ];
                $settlement['advice'] = $record;
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'advice' => $record,
                    'settlement' => $settlement,
                ],
                'settlement' => $settlement,
            ]);
        } catch (\Throwable $th) {
            Log::error('AdviceToResignApiController getSettlement: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to load settlement: ' . $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/advice-to-resign/{id}/download-pdf
     * Direct PDF download of Exit Settlement Breakdown Slip.
     */
    public function downloadSettlementPdf(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $record = DB::table('advice_to_resigns')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
            }

            $resignationCtrl = app(\App\Http\Controllers\Api\ResignationApiController::class);
            $settlementData = null;
            if ($record->resignation_request_id) {
                $settlementData = $resignationCtrl->computeDetailedSettlement($record->resignation_request_id, true);
            }

            if (!$settlementData) {
                $tempResId = DB::table('resignation_requests')->insertGetId([
                    'staff_id' => $record->staff_id,
                    'resignation_date' => $record->compliance_date ?: ($record->applied_date ?: Carbon::today()->toDateString()),
                    'reason' => 'Advice to Resign Preview: ' . $record->reason,
                    'status' => 1,
                    'admin_status' => 1,
                    'hod_status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $settlementData = $resignationCtrl->computeDetailedSettlement($tempResId, true);
                DB::table('resignation_requests')->where('id', $tempResId)->delete();
            }

            if (!$settlementData) {
                return response()->json(['status' => 'error', 'message' => 'Settlement data not available.'], 404);
            }

            $pdfHtml = $resignationCtrl->buildSettlementSlipPdfHtml($settlementData);
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($pdfHtml)->setPaper('a4', 'portrait');

            $staffId = $settlementData['staff']['id'] ?? $record->staff_id;
            $staffName = preg_replace('/[^A-Za-z0-9_-]/', '_', $settlementData['staff']['name'] ?? 'Staff');
            $fileName = "Exit_Settlement_Slip_StaffID_{$staffId}_{$staffName}.pdf";

            return $pdf->download($fileName);
        } catch (\Throwable $th) {
            Log::error('AdviceToResignApiController downloadSettlementPdf: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/advice-to-resign/{id}/send-email
     * Send or resend Exit Settlement Breakdown Slip to staff email.
     */
    public function sendSettlementEmail(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $record = DB::table('advice_to_resigns')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
            }

            $resignationCtrl = app(\App\Http\Controllers\Api\ResignationApiController::class);
            $settlementData = null;
            if ($record->resignation_request_id) {
                $settlementData = $resignationCtrl->computeDetailedSettlement($record->resignation_request_id, true);
            }

            if (!$settlementData) {
                return response()->json(['status' => 'error', 'message' => 'Cannot email settlement slip before HR approval.'], 422);
            }

            $overrideEmail = trim($request->input('email', ''));
            $res = $resignationCtrl->sendSettlementBreakdownEmail($settlementData, $overrideEmail ?: null);

            return response()->json([
                'status'  => $res['sent'] ? 'success' : 'error',
                'message' => $res['sent'] ? 'Exit settlement breakdown slip emailed successfully.' : ($res['message'] ?? 'Failed to send email.')
            ]);
        } catch (\Throwable $th) {
            Log::error('AdviceToResignApiController sendSettlementEmail: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * Log action to user_activity_logs
     */
    protected function logActivity($ctx, string $type, string $action, $details = null, $staffId = null): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('user_activity_logs')) {
                DB::table('user_activity_logs')->insert([
                    'user_id' => $ctx['userId'] ?? null,
                    'staff_id' => $staffId,
                    'user_name' => $ctx['userName'] ?? 'HR Admin',
                    'role_name' => $ctx['roleName'] ?? 'Admin',
                    'activity_type' => $type,
                    'action' => $action,
                    'module' => 'HR',
                    'method' => request()->method(),
                    'url' => request()->fullUrl(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'details' => is_string($details) ? $details : json_encode($details),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('AdviceToResignApiController logActivity error: ' . $e->getMessage());
        }
    }
}
