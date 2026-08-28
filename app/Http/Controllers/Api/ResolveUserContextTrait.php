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

        $isSuperAdmin = in_array(1, $roleIds) 
            || in_array('super administrator', $roleNames) 
            || in_array('superadmin', $roleNames)
            || in_array('super admin', $roleNames)
            || in_array('administrator', $roleNames)
            || in_array('admin', $roleNames);

        $adminStaff = in_array(48, $roleIds) || in_array(68, $roleIds) || in_array('hr head', $roleNames) || in_array('head of hr', $roleNames) || in_array('hr', $roleNames);

        $isAuditStaff = in_array(34, $roleIds) || in_array(35, $roleIds) || in_array(70, $roleIds) || in_array('audit head', $roleNames) || in_array('head of audit', $roleNames) || in_array('audit', $roleNames);

        $isFinanceStaff = in_array(36, $roleIds) || in_array(37, $roleIds) || in_array(69, $roleIds) || in_array('finance head', $roleNames) || in_array('head of finance', $roleNames) || in_array('finance', $roleNames);

        $employee = DB::table('tblper')->where('UserID', $userId)->first();
        if (!$employee && is_numeric($userId)) {
            $employee = DB::table('tblper')->where('ID', (int)$userId)->first();
        }
        if (!$employee) {
            $userRec = DB::table('users')->where('id', $userId)->first();
            if ($userRec) {
                $employee = DB::table('tblper')->where('fileNo', $userRec->username)->first();
                if (!$employee && !empty($userRec->email)) {
                    $employee = DB::table('tblper')->where('email', $userRec->email)->first();
                }
            }
        }

        $isHod = $employee && $employee->is_hod == 1;
        $isDelegatedHod = false;
        $delegatedPermissions = [];
        $delegatedDepartmentId = null;

        $isDelegatedHr = false;
        $delegatedHrPermissions = [];

        $isDelegatedFinance = false;
        $delegatedFinancePermissions = [];

        $isDelegatedAudit = false;
        $delegatedAuditPermissions = [];

        if ($employee) {
            $delegations = DB::table('hod_delegations')
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
                ->get();

            foreach ($delegations as $del) {
                $perms = json_decode($del->permissions, true) ?: [];
                $delegatedPermissions = array_unique(array_merge($delegatedPermissions, $perms));
                if (!$delegatedDepartmentId) {
                    $delegatedDepartmentId = $del->department_id;
                }

                // Check for HR approval roles in delegation
                $hrRoles = array_filter($perms, fn($p) => is_string($p) && str_starts_with($p, 'hr_'));
                if (!empty($hrRoles)) {
                    $adminStaff = true;
                    $isDelegatedHr = true;
                    $delegatedHrPermissions = array_unique(array_merge($delegatedHrPermissions, $hrRoles));
                }

                // Check for Finance approval roles in delegation
                $finRoles = array_filter($perms, fn($p) => is_string($p) && str_starts_with($p, 'finance_'));
                if (!empty($finRoles)) {
                    $isFinanceStaff = true;
                    $isDelegatedFinance = true;
                    $delegatedFinancePermissions = array_unique(array_merge($delegatedFinancePermissions, $finRoles));
                }

                // Check for Audit approval roles in delegation
                $audRoles = array_filter($perms, fn($p) => is_string($p) && str_starts_with($p, 'audit_'));
                if (!empty($audRoles)) {
                    $isAuditStaff = true;
                    $isDelegatedAudit = true;
                    $delegatedAuditPermissions = array_unique(array_merge($delegatedAuditPermissions, $audRoles));
                }

                // Check for general HOD approval roles
                $hodRoles = array_filter($perms, fn($p) => is_string($p) && str_starts_with($p, 'approve_'));
                if (!empty($hodRoles) || !$isHod) {
                    $isHod = true;
                    $isDelegatedHod = true;
                }
            }

            // Also check hr_delegations table for backward compatibility
            $hrDelegations = DB::table('hr_delegations')
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
                ->get();

            foreach ($hrDelegations as $hrDel) {
                $perms = json_decode($hrDel->permissions, true) ?: [];
                $adminStaff = true;
                $isDelegatedHr = true;
                $delegatedHrPermissions = array_unique(array_merge($delegatedHrPermissions, $perms));
            }
        }

        return [
            'userId'                      => $userId,
            'isSuperAdmin'                => $isSuperAdmin,
            'isAdminStaff'                => $adminStaff,
            'isAuditStaff'                => $isAuditStaff,
            'isFinanceStaff'              => $isFinanceStaff,
            'employee'                    => $employee,
            'isHod'                       => $isHod,
            'isDelegatedHod'              => $isDelegatedHod,
            'delegatedPermissions'        => $delegatedPermissions,
            'delegated_department_id'     => $delegatedDepartmentId,
            'isDelegatedHr'               => $isDelegatedHr,
            'delegatedHrPermissions'      => $delegatedHrPermissions,
            'isDelegatedFinance'          => $isDelegatedFinance,
            'delegatedFinancePermissions' => $delegatedFinancePermissions,
            'isDelegatedAudit'            => $isDelegatedAudit,
            'delegatedAuditPermissions'   => $delegatedAuditPermissions,
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
