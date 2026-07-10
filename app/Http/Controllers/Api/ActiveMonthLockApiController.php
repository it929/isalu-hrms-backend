<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ActiveMonthLockApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * Resolve the current user context from the X-User-Id header.
     */
    

    /**
     * Add log entry to audit_log table.
     */
    private function addLog(Request $request, $userId, $operation)
    {
        try {
            $ip = $request->ip();
            $url = $request->fullUrl();
            $nowInNigeria = Carbon::now('Africa/Lagos');
            $cmpname = php_uname('a');
            $host = $request->header('host') ?? 'localhost';

            DB::table('audit_log')->insert([
                'comp_name' => $cmpname,
                'user_id'   => $userId,
                'date'      => $nowInNigeria,
                'ip_addr'   => $ip,
                'operation' => $operation,
                'host'      => $host,
                'referer'   => $url
            ]);
        } catch (\Throwable $th) {
            Log::error('ActiveMonthLockApiController addLog: ' . $th->getMessage());
        }
    }

    /**
     * Helper to translate month string to int.
     */
    private function monthToInt($monthName)
    {
        $months = [
            'JANUARY' => 1, 'FEBRUARY' => 2, 'MARCH' => 3, 'APRIL' => 4,
            'MAY' => 5, 'JUNE' => 6, 'JULY' => 7, 'AUGUST' => 8,
            'SEPTEMBER' => 9, 'OCTOBER' => 10, 'NOVEMBER' => 11, 'DECEMBER' => 12
        ];
        return $months[strtoupper(trim($monthName))] ?? 1;
    }

    /**
     * GET /api/nextjs/payroll/lock-active-month
     * Returns global lock state for the active month.
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            // Get active month
            $payrollActivePeriod = DB::table('tblactivemonth')->first();
            if (!$payrollActivePeriod) {
                return response()->json([
                    'status'       => 'success',
                    'activePeriod' => null,
                    'total_computed' => 0,
                    'max_vstage'     => 0,
                    'lock_status'    => 'Not Computed'
                ]);
            }

            $monthInt = $this->monthToInt($payrollActivePeriod->month);
            $yearInt = (int)$payrollActivePeriod->year;

            // Fetch statistics globally for this month/year
            $stats = DB::table('payroll_conpt')
                ->where('year', $yearInt)
                ->where('month', $monthInt)
                ->select(
                    DB::raw('COUNT(*) as total_computed'),
                    DB::raw('SUM(CASE WHEN salary_lock = 1 THEN 1 ELSE 0 END) as locked_count'),
                    DB::raw('MAX(vstage) as max_vstage')
                )
                ->first();

            $total = $stats ? (int)$stats->total_computed : 0;
            $locked = $stats ? (int)$stats->locked_count : 0;
            $maxVstage = $stats ? (int)$stats->max_vstage : 0;

            $status = 'Open';
            if ($total > 0) {
                if ($locked === $total) {
                    $status = 'Locked';
                } elseif ($locked > 0) {
                    $status = 'Partially Locked';
                }
            } else {
                $status = 'Not Computed';
            }

            return response()->json([
                'status'       => 'success',
                'activePeriod' => $payrollActivePeriod,
                'total_computed' => $total,
                'max_vstage'     => $maxVstage,
                'lock_status'    => $status
            ]);
        } catch (\Throwable $th) {
            Log::error('ActiveMonthLockApiController index: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/lock-active-month/lock
     * Lock active month globally.
     */
    public function lock(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $request->validate([
                'year'  => 'required|integer',
                'month' => 'required|string'
            ]);

            $year = (int)$request->input('year');
            $month = strtoupper(trim($request->input('month')));
            $monthInt = $this->monthToInt($month);

            $updated = DB::table('payroll_conpt')
                ->where('year', $year)
                ->where('month', $monthInt)
                ->update([
                    'salary_lock' => 1,
                    'vstage'      => 1
                ]);

            if ($updated === 0) {
                return response()->json(['status' => 'error', 'message' => 'No computed payroll found for this period to lock.'], 400);
            }

            $this->addLog($request, $ctx['userId'], " active month locked globally for " . $month . "/" . $year . " ");

            return response()->json([
                'status'  => 'success',
                'message' => 'Period locked successfully!'
            ]);
        } catch (\Throwable $th) {
            Log::error('ActiveMonthLockApiController lock: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/lock-active-month/unlock
     * Unlock active month globally.
     */
    public function unlock(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $request->validate([
                'year'  => 'required|integer',
                'month' => 'required|string'
            ]);

            $year = (int)$request->input('year');
            $month = strtoupper(trim($request->input('month')));
            $monthInt = $this->monthToInt($month);

            // Check if verification stage has progressed to Stage 4 (Paid) globally
            $hasPaidStage = DB::table('payroll_conpt')
                ->where('year', $year)
                ->where('month', $monthInt)
                ->where('vstage', '>=', 4)
                ->exists();

            if ($hasPaidStage) {
                return response()->json(['status' => 'error', 'message' => 'Cannot unlock: payroll has already been paid.'], 400);
            }

            $updated = DB::table('payroll_conpt')
                ->where('year', $year)
                ->where('month', $monthInt)
                ->update([
                    'salary_lock' => 0,
                    'vstage'      => 0,
                    'audit_checked' => 0,
                    'is_paid'       => 0
                ]);

            if ($updated === 0) {
                return response()->json(['status' => 'error', 'message' => 'No computed payroll found for this period to unlock.'], 400);
            }

            $this->addLog($request, $ctx['userId'], " active month unlocked globally for " . $month . "/" . $year . " ");

            return response()->json([
                'status'  => 'success',
                'message' => 'Period unlocked successfully!'
            ]);
        } catch (\Throwable $th) {
            Log::error('ActiveMonthLockApiController unlock: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/lock-active-month/forward-to-audit
     * Forward locked payroll to Audit.
     */
    public function forwardToAudit(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $request->validate([
                'year'  => 'required|integer',
                'month' => 'required|string'
            ]);

            $year = (int)$request->input('year');
            $month = strtoupper(trim($request->input('month')));
            $monthInt = $this->monthToInt($month);

            $stats = DB::table('payroll_conpt')
                ->where('year', $year)
                ->where('month', $monthInt)
                ->select(
                    DB::raw('COUNT(*) as total_computed'),
                    DB::raw('SUM(CASE WHEN salary_lock = 1 THEN 1 ELSE 0 END) as locked_count')
                )
                ->first();

            if (!$stats || $stats->total_computed === 0) {
                return response()->json(['status' => 'error', 'message' => 'No computed payroll found for this period.'], 400);
            }

            if ($stats->locked_count < $stats->total_computed) {
                return response()->json(['status' => 'error', 'message' => 'Cannot forward: payroll active month must be locked first.'], 400);
            }

            DB::table('payroll_conpt')
                ->where('year', $year)
                ->where('month', $monthInt)
                ->update([
                    'vstage' => 2
                ]);

            $this->addLog($request, $ctx['userId'], " active month forwarded to audit globally for " . $month . "/" . $year . " ");

            return response()->json([
                'status'  => 'success',
                'message' => 'Payroll forwarded to audit successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('ActiveMonthLockApiController forwardToAudit: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/lock-active-month/audit-check
     * Toggle audit check status on payroll rows.
     */
    public function auditCheck(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            if (!$ctx['isSuperAdmin'] && !$ctx['isAuditStaff']) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: Only Audit staff can check staff.'], 403);
            }

            $request->validate([
                'year'      => 'required|integer',
                'month'     => 'required|string',
                'checked'   => 'required|integer|in:0,1',
                'staff_ids' => 'nullable|array',
                'check_all' => 'nullable|boolean'
            ]);

            $year = (int)$request->input('year');
            $month = strtoupper(trim($request->input('month')));
            $monthInt = $this->monthToInt($month);
            $checked = (int)$request->input('checked');
            $staffIds = $request->input('staff_ids');
            $checkAll = $request->input('check_all');

            $anyNonStage2 = DB::table('payroll_conpt')
                ->where('year', $year)
                ->where('month', $monthInt)
                ->where('vstage', '!=', 2)
                ->exists();

            if ($anyNonStage2) {
                return response()->json(['status' => 'error', 'message' => 'Audit check is only allowed when payroll is forwarded to Audit.'], 400);
            }

            $query = DB::table('payroll_conpt')
                ->where('year', $year)
                ->where('month', $monthInt);

            if (!$checkAll && !empty($staffIds)) {
                $query->whereIn('staffID', $staffIds);
            }

            $query->update([
                'audit_checked' => $checked
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Audit checked status updated.'
            ]);
        } catch (\Throwable $th) {
            Log::error('ActiveMonthLockApiController auditCheck: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/lock-active-month/audit-approve
     * Approve the payroll globally.
     */
    public function auditApprove(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            if (!$ctx['isSuperAdmin'] && !$ctx['isAuditStaff']) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: Only Audit staff can approve.'], 403);
            }

            $request->validate([
                'year'  => 'required|integer',
                'month' => 'required|string'
            ]);

            $year = (int)$request->input('year');
            $month = strtoupper(trim($request->input('month')));
            $monthInt = $this->monthToInt($month);

            $totalComputed = DB::table('payroll_conpt')
                ->where('year', $year)
                ->where('month', $monthInt)
                ->count();

            if ($totalComputed === 0) {
                return response()->json(['status' => 'error', 'message' => 'No computed payroll records found.'], 400);
            }

            $checkedCount = DB::table('payroll_conpt')
                ->where('year', $year)
                ->where('month', $monthInt)
                ->where('audit_checked', 1)
                ->count();

            if ($checkedCount === 0) {
                return response()->json(['status' => 'error', 'message' => 'Cannot approve: no staff members have been checked by Audit.'], 400);
            }

            DB::table('payroll_conpt')
                ->where('year', $year)
                ->where('month', $monthInt)
                ->update([
                    'vstage' => 3
                ]);

            $this->addLog($request, $ctx['userId'], " active month payroll approved by audit globally for " . $month . "/" . $year . " ");

            return response()->json([
                'status'  => 'success',
                'message' => 'Payroll approved by Audit successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('ActiveMonthLockApiController auditApprove: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/lock-active-month/pay
     * Mark checked payroll records as paid and transition period stage.
     */
    public function pay(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            if (!$ctx['isSuperAdmin'] && !$ctx['isFinanceStaff'] && !$ctx['isAdminStaff']) {
                return response()->json(['status' => 'error', 'message' => 'Access denied: Only Super Admin, HR, or Finance staff can pay.'], 403);
            }

            $request->validate([
                'year'  => 'required|integer',
                'month' => 'required|string'
            ]);

            $year = (int)$request->input('year');
            $month = strtoupper(trim($request->input('month')));
            $monthInt = $this->monthToInt($month);

            $anyNonStage3 = DB::table('payroll_conpt')
                ->where('year', $year)
                ->where('month', $monthInt)
                ->where('vstage', '!=', 3)
                ->exists();

            if ($anyNonStage3) {
                return response()->json(['status' => 'error', 'message' => 'Payment is only allowed when payroll has been approved by Audit.'], 400);
            }

            DB::table('payroll_conpt')
                ->where('year', $year)
                ->where('month', $monthInt)
                ->where('audit_checked', 1)
                ->update([
                    'is_paid' => 1,
                    'vstage'  => 4
                ]);

            DB::table('payroll_conpt')
                ->where('year', $year)
                ->where('month', $monthInt)
                ->where('audit_checked', 0)
                ->update([
                    'vstage'  => 4
                ]);

            $this->addLog($request, $ctx['userId'], " active month payroll marked as paid globally for " . $month . "/" . $year . " ");

            return response()->json([
                'status'  => 'success',
                'message' => 'Payroll marked as paid successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('ActiveMonthLockApiController pay: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
