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

        $isHod = $employee && $employee->is_hod == 1;
        $isDelegatedHod = false;
        $delegatedPermissions = [];
        $delegatedDepartmentId = null;

        if (!$isHod && $employee) {
            $delegation = DB::table('hod_delegations')
                ->where('delegate_staff_id', $employee->ID)
                ->where('status', 'active')
                ->where(function($query) {
                    $query->whereNull('start_date')
                          ->orWhere('start_date', '<=', now()->toDateString());
                })
                ->where(function($query) {
                    $query->whereNull('end_date')
                          ->orWhere('end_date', '>=', now()->toDateString());
                })
                ->first();

            if ($delegation) {
                $isHod = true;
                $isDelegatedHod = true;
                $delegatedPermissions = json_decode($delegation->permissions, true) ?: [];
                $delegatedDepartmentId = $delegation->department_id;
            }
        }

        $isDelegatedHr = false;
        $delegatedHrPermissions = [];

        if (!$adminStaff && $employee) {
            $hrDelegation = DB::table('hr_delegations')
                ->where('delegate_staff_id', $employee->ID)
                ->where('status', 'active')
                ->where(function($query) {
                    $query->whereNull('start_date')
                          ->orWhere('start_date', '<=', now()->toDateString());
                })
                ->where(function($query) {
                    $query->whereNull('end_date')
                          ->orWhere('end_date', '>=', now()->toDateString());
                })
                ->first();

            if ($hrDelegation) {
                $adminStaff = true;
                $isDelegatedHr = true;
                $delegatedHrPermissions = json_decode($hrDelegation->permissions, true) ?: [];
            }
        }

        return [
            'userId'                 => $userId,
            'isSuperAdmin'           => $isSuperAdmin,
            'isAdminStaff'           => $adminStaff,
            'isAuditStaff'           => $isAuditStaff,
            'isFinanceStaff'         => $isFinanceStaff,
            'employee'               => $employee,
            'isHod'                  => $isHod,
            'isDelegatedHod'         => $isDelegatedHod,
            'delegatedPermissions'   => $delegatedPermissions,
            'delegated_department_id'=> $delegatedDepartmentId,
            'isDelegatedHr'          => $isDelegatedHr,
            'delegatedHrPermissions' => $delegatedHrPermissions,
        ];
    }

    /**
     * Check if the user has a specific HOD permission (direct HOD, admin, or delegated).
     */
    private function hasHodPermission($ctx, $permission)
    {
        if (!$ctx) return false;
        if ($ctx['isSuperAdmin'] || $ctx['isAdminStaff']) return true;
        if ($ctx['employee'] && $ctx['employee']->is_hod == 1) return true;

        if (isset($ctx['isDelegatedHod']) && $ctx['isDelegatedHod']) {
            return in_array($permission, $ctx['delegatedPermissions']);
        }

        return false;
    }

    /**
     * Check if the user has a specific HR permission (direct HR, admin, or delegated).
     */
    private function hasHrPermission($ctx, $permission)
    {
        if (!$ctx) return false;
        if ($ctx['isSuperAdmin']) return true;

        // If they are regular HR staff (not delegated)
        if ($ctx['isAdminStaff'] && (!isset($ctx['isDelegatedHr']) || !$ctx['isDelegatedHr'])) {
            return true;
        }

        // If they are delegated HR staff
        if (isset($ctx['isDelegatedHr']) && $ctx['isDelegatedHr']) {
            return in_array($permission, $ctx['delegatedHrPermissions']);
        }

        return false;
    }
}
