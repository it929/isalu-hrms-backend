<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserActivityLogger
{
    /**
     * Log user login activity.
     */
    public static function logLogin($user, Request $request, ?string $roleName = null): void
    {
        try {
            if (!$user) return;

            $userId = is_object($user) ? ($user->id ?? $user->UserID ?? null) : ($user['id'] ?? null);
            $userName = is_object($user) ? ($user->name ?? $user->username ?? 'User') : ($user['name'] ?? 'User');
            
            // Lookup staff record if available
            $staff = DB::table('tblper')->where('UserID', $userId)->orWhere('ID', $userId)->first();
            $staffId = $staff ? $staff->ID : null;
            if ($staff) {
                $staffFullName = trim("{$staff->surname} {$staff->first_name} {$staff->othernames}");
                if (!empty($staffFullName)) {
                    $userName = $staffFullName;
                }
            }

            if (!$roleName) {
                $role = DB::table('assign_user_role')
                    ->join('user_role', 'user_role.roleID', '=', 'assign_user_role.roleID')
                    ->where('assign_user_role.userID', $userId)
                    ->first();
                $roleName = $role ? $role->rolename : 'Staff';
            }

            $ip = $request->ip() ?: $request->getClientIp() ?: '127.0.0.1';
            $userAgent = $request->userAgent();

            DB::table('user_activity_logs')->insert([
                'user_id' => $userId,
                'staff_id' => $staffId,
                'user_name' => $userName,
                'role_name' => $roleName,
                'activity_type' => 'login',
                'action' => "User Logged In ({$userName})",
                'module' => 'Authentication',
                'method' => 'POST',
                'url' => $request->fullUrl(),
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'details' => json_encode(['login_time' => date('Y-m-d H:i:s'), 'ip' => $ip]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Also keep backwards compatibility with legacy audit_log table
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('audit_log')) {
                    DB::table('audit_log')->insert([
                        'comp_name' => 'ISALU HOSPITAL',
                        'user_id' => substr((string)$userId, 0, 3),
                        'date' => now(),
                        'ip_addr' => substr($ip, 0, 20),
                        'operation' => "User Logged In ({$userName})",
                        'host' => gethostname() ?: 'localhost',
                        'referer' => $ip,
                        'action_title' => 'Login',
                    ]);
                }
            } catch (\Throwable $e) { /* ignore legacy audit */ }
        } catch (\Throwable $th) {
            Log::error('UserActivityLogger logLogin error: ' . $th->getMessage());
        }
    }

    /**
     * Log user logout activity.
     */
    public static function logLogout($user, Request $request): void
    {
        try {
            if (!$user) return;

            $userId = is_object($user) ? ($user->id ?? $user->UserID ?? null) : ($user['id'] ?? null);
            $userName = is_object($user) ? ($user->name ?? $user->username ?? 'User') : ($user['name'] ?? 'User');
            
            $staff = DB::table('tblper')->where('UserID', $userId)->orWhere('ID', $userId)->first();
            $staffId = $staff ? $staff->ID : null;
            if ($staff) {
                $staffFullName = trim("{$staff->surname} {$staff->first_name} {$staff->othernames}");
                if (!empty($staffFullName)) {
                    $userName = $staffFullName;
                }
            }

            $role = DB::table('assign_user_role')
                ->join('user_role', 'user_role.roleID', '=', 'assign_user_role.roleID')
                ->where('assign_user_role.userID', $userId)
                ->first();
            $roleName = $role ? $role->rolename : 'Staff';

            $ip = $request->ip() ?: $request->getClientIp() ?: '127.0.0.1';
            $userAgent = $request->userAgent();

            DB::table('user_activity_logs')->insert([
                'user_id' => $userId,
                'staff_id' => $staffId,
                'user_name' => $userName,
                'role_name' => $roleName,
                'activity_type' => 'logout',
                'action' => "User Logged Out ({$userName})",
                'module' => 'Authentication',
                'method' => 'POST',
                'url' => $request->fullUrl(),
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'details' => json_encode(['logout_time' => date('Y-m-d H:i:s'), 'ip' => $ip]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Also keep backwards compatibility with legacy audit_log table
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('audit_log')) {
                    DB::table('audit_log')->insert([
                        'comp_name' => 'ISALU HOSPITAL',
                        'user_id' => substr((string)$userId, 0, 3),
                        'date' => now(),
                        'ip_addr' => substr($ip, 0, 20),
                        'operation' => "User Logged Out ({$userName})",
                        'host' => gethostname() ?: 'localhost',
                        'referer' => $ip,
                        'action_title' => 'Logout',
                    ]);
                }
            } catch (\Throwable $e) { /* ignore legacy audit */ }
        } catch (\Throwable $th) {
            Log::error('UserActivityLogger logLogout error: ' . $th->getMessage());
        }
    }

    /**
     * Log general application action.
     */
    public static function logAction(
        $userId,
        ?int $staffId,
        string $userName,
        ?string $roleName,
        string $activityType,
        string $action,
        string $module,
        Request $request,
        $details = null
    ): void {
        try {
            $ip = $request->ip() ?: $request->getClientIp() ?: '127.0.0.1';
            $userAgent = $request->userAgent();

            $detailsJson = null;
            if (is_array($details) || is_object($details)) {
                $detailsJson = json_encode($details);
            } elseif (is_string($details)) {
                $detailsJson = $details;
            }

            DB::table('user_activity_logs')->insert([
                'user_id' => $userId,
                'staff_id' => $staffId,
                'user_name' => $userName,
                'role_name' => $roleName ?: 'Staff',
                'activity_type' => $activityType,
                'action' => $action,
                'module' => $module,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'details' => $detailsJson,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $th) {
            Log::error('UserActivityLogger logAction error: ' . $th->getMessage());
        }
    }
}
