<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ActiveMonthApiController extends Controller
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
            Log::error('ActiveMonthApiController addLog: ' . $th->getMessage());
        }
    }

    /**
     * GET /api/nextjs/payroll/active-month
     * Returns courts list, sole court status, and current active months configuration.
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            // Get Sole Court config
            $courtInfoRaw = DB::select("SELECT * FROM `tblsole_court`");
            $courtInfo = !empty($courtInfoRaw) ? $courtInfoRaw[0] : null;

            // Get all courts
            $courts = DB::table('tbl_court')
                ->select('id', 'court_name')
                ->get();

            // Get current active months
            $activeMonths = DB::table('tblactivemonth')
                ->join('tbl_court', 'tbl_court.id', '=', 'tblactivemonth.courtID')
                ->select('tblactivemonth.month', 'tblactivemonth.year', 'tblactivemonth.courtID', 'tblactivemonth.courtID as id', 'tbl_court.court_name')
                ->get();

            return response()->json([
                'status'       => 'success',
                'courtInfo'    => $courtInfo,
                'courts'       => $courts,
                'activeMonths' => $activeMonths
            ]);
        } catch (\Throwable $th) {
            Log::error('ActiveMonthApiController index: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/active-month
     * Update or insert active month configuration.
     */
    public function store(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $request->validate([
                'year'  => 'required|integer',
                'month' => 'required|string',
                'court' => 'nullable|integer'
            ]);

            // Resolve court ID
            $courtId = $request->input('court');
            
            $courtInfoRaw = DB::select("SELECT * FROM `tblsole_court`");
            $courtInfo = !empty($courtInfoRaw) ? $courtInfoRaw[0] : null;

            if (empty($courtId) && $courtInfo) {
                $courtId = $courtInfo->courtid;
            }

            if (empty($courtId)) {
                return response()->json(['status' => 'error', 'message' => 'Court selection is required.'], 422);
            }

            $year = (int) $request->input('year');
            $month = strtoupper(trim($request->input('month')));

            $courtNameRow = DB::table('tbl_court')->where('id', $courtId)->first();
            $courtName = $courtNameRow ? $courtNameRow->court_name : "Unknown Court (ID: {$courtId})";

            $count = DB::table('tblactivemonth')->where('courtID', $courtId)->count();

            if ($count == 1) {
                DB::table('tblactivemonth')
                    ->where('courtID', $courtId)
                    ->update([
                        'month' => $month,
                        'year'  => $year
                    ]);
                
                // Write session for legacy compatibility if available
                if ($request->hasSession()) {
                    $request->session()->put('activeMonth', $month);
                    $request->session()->put('activeYear', $year);
                    $request->session()->put('court', $year); // note: Laravel controller does put('court', $year) - preserving this legacy quirk!
                }

                $this->addLog($request, $ctx['userId'], " active month set  to " . $month . "/" . $year . " for " . $courtName . " Court  ");
            } else {
                DB::table('tblactivemonth')->insert([
                    'month'   => $month,
                    'year'    => $year,
                    'courtID' => $courtId
                ]);

                // Write session for legacy compatibility if available
                if ($request->hasSession()) {
                    $request->session()->put('activeMonth', $month);
                    $request->session()->put('activeYear', $year);
                    $request->session()->put('court', $year);
                }

                $this->addLog($request, $ctx['userId'], " active month set  to " . $month . "/" . $year . " for " . $courtName . " Court  ");
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Active month successfully updated!'
            ]);
        } catch (\Throwable $th) {
            Log::error('ActiveMonthApiController store: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
