<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait ResolveUserContextTrait
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

        $userRoles = DB::table('assign_user_role')
            ->leftJoin('user_role', 'assign_user_role.roleID', '=', 'user_role.roleID')
            ->where('assign_user_role.userID', $userId)
            ->select('assign_user_role.roleID', 'user_role.rolename')
            ->get();

        $roleIds = $userRoles->pluck('roleID')->toArray();
        $roleNames = $userRoles->pluck('rolename')->filter()->map(function ($role) {
            return strtolower($role);
        })->toArray();

        $isSuperAdmin = in_array(1, $roleIds) || in_array('super administrator', $roleNames);

        $adminStaff = in_array(48, $roleIds) || in_array('hr head', $roleNames);

        $isAuditStaff = in_array(34, $roleIds) || in_array(35, $roleIds) || in_array('audit head', $roleNames);

        $isFinanceStaff = in_array(36, $roleIds) || in_array(37, $roleIds) || in_array('finance head', $roleNames);

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
}
