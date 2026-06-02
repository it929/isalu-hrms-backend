<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ActiveMonthLockApiController extends Controller
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

        $employee = DB::table('tblper')->where('UserID', $userId)->first();

        return [
            'userId'       => $userId,
            'isSuperAdmin' => $isSuperAdmin,
            'employee'     => $employee,
        ];
    }

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

            // Check if verification stage has progressed beyond stage 1 globally
            $hasAdvancedStage = DB::table('payroll_conpt')
                ->where('year', $year)
                ->where('month', $monthInt)
                ->where('vstage', '>', 1)
                ->exists();

            if ($hasAdvancedStage) {
                return response()->json(['status' => 'error', 'message' => 'Cannot unlock: payroll verification has progressed beyond stage 1.'], 400);
            }

            $updated = DB::table('payroll_conpt')
                ->where('year', $year)
                ->where('month', $monthInt)
                ->update([
                    'salary_lock' => 0,
                    'vstage'      => 0
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
}
