<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResignationSettlementRoleAccessTest extends TestCase
{
    public function test_audit_approval_and_finance_pay_strict_role_access()
    {
        $unique = time() . '_' . rand(100, 999);

        // 1. Create a test employee
        $staffId = DB::table('tblper')->insertGetId([
            'fileNo' => 'ROLE-TEST-' . $unique,
            'surname' => 'Exit',
            'first_name' => 'Tester',
            'staff_status' => 1,
            'status_value' => 'active',
            'departmentID' => 1,
            'rank' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Create an HR-approved resignation request
        $resignationId = DB::table('resignation_requests')->insertGetId([
            'staff_id' => $staffId,
            'reason' => 'Testing role access',
            'resignation_date' => now()->toDateString(),
            'status' => 1,
            'hod_status' => 1,
            'admin_status' => 1, // Approved by HR
            'audit_status' => 0, // Pending Audit
            'finance_status' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Create test users: HR Head, Audit Head, Finance Head, Super Admin
        $hrUserId = DB::table('users')->insertGetId([
            'username' => 'hr_head_' . $unique,
            'name' => 'HR Head User',
            'email' => 'hr_' . $unique . '@isalu.test',
            'password' => Hash::make('password123'),
            'user_type' => 'staff',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('assign_user_role')->insertOrIgnore(['userID' => $hrUserId, 'roleID' => 68]); // HR HEAD

        $auditUserId = DB::table('users')->insertGetId([
            'username' => 'audit_head_' . $unique,
            'name' => 'Audit Head User',
            'email' => 'audit_' . $unique . '@isalu.test',
            'password' => Hash::make('password123'),
            'user_type' => 'staff',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('assign_user_role')->insertOrIgnore(['userID' => $auditUserId, 'roleID' => 70]); // AUDIT HEAD

        $financeUserId = DB::table('users')->insertGetId([
            'username' => 'finance_head_' . $unique,
            'name' => 'Finance Head User',
            'email' => 'finance_' . $unique . '@isalu.test',
            'password' => Hash::make('password123'),
            'user_type' => 'staff',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('assign_user_role')->insertOrIgnore(['userID' => $financeUserId, 'roleID' => 69]); // FINANCE HEAD

        $superAdminUserId = DB::table('users')->insertGetId([
            'username' => 'superadmin_' . $unique,
            'name' => 'Super Admin User',
            'email' => 'superadmin_' . $unique . '@isalu.test',
            'password' => Hash::make('password123'),
            'user_type' => 'Technical',
            'is_global' => 1,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('assign_user_role')->insertOrIgnore(['userID' => $superAdminUserId, 'roleID' => 1]); // Super Administrator

        $hrHeaders = ['X-User-Id' => $hrUserId, 'X-User-Role' => 'HR Head'];
        $auditHeaders = ['X-User-Id' => $auditUserId, 'X-User-Role' => 'Audit Head'];
        $financeHeaders = ['X-User-Id' => $financeUserId, 'X-User-Role' => 'Finance Head'];
        $superAdminHeaders = ['X-User-Id' => $superAdminUserId, 'X-User-Role' => 'Super Administrator'];

        // --- TEST AUDIT APPROVE PERMISSIONS ---
        // HR Head trying to audit approve -> DENIED (403)
        $resHrAudit = $this->postJson("/api/nextjs/payroll/resignations/audit-approve/{$resignationId}", [
            'remarks' => 'Attempt by HR'
        ], $hrHeaders);
        $resHrAudit->assertStatus(403);

        // Finance Head trying to audit approve -> DENIED (403)
        $resFinAudit = $this->postJson("/api/nextjs/payroll/resignations/audit-approve/{$resignationId}", [
            'remarks' => 'Attempt by Finance'
        ], $financeHeaders);
        $resFinAudit->assertStatus(403);

        // Audit Head trying to audit approve -> ALLOWED (200)
        $resAudit = $this->postJson("/api/nextjs/payroll/resignations/audit-approve/{$resignationId}", [
            'remarks' => 'Approved by Audit Head'
        ], $auditHeaders);
        $resAudit->assertStatus(200);

        // --- TEST FINANCE MARK AS PAID PERMISSIONS ---
        // HR Head trying to mark as paid -> DENIED (403)
        $resHrPay = $this->postJson("/api/nextjs/payroll/resignations/finance-pay/{$resignationId}", [
            'payment_reference' => 'TEST-REF-HR'
        ], $hrHeaders);
        $resHrPay->assertStatus(403);

        // Audit Head trying to mark as paid -> DENIED (403)
        $resAuditPay = $this->postJson("/api/nextjs/payroll/resignations/finance-pay/{$resignationId}", [
            'payment_reference' => 'TEST-REF-AUDIT'
        ], $auditHeaders);
        $resAuditPay->assertStatus(403);

        // Finance Head trying to mark as paid -> ALLOWED (200)
        $resFinPay = $this->postJson("/api/nextjs/payroll/resignations/finance-pay/{$resignationId}", [
            'payment_reference' => 'TEST-REF-FINANCE',
            'payment_date' => now()->toDateString(),
            'remarks' => 'Disbursed by Finance'
        ], $financeHeaders);
        $resFinPay->assertStatus(200);

        // --- RESET AND TEST SUPER ADMIN ON BOTH ---
        DB::table('resignation_requests')->where('id', $resignationId)->update([
            'audit_status' => 0,
            'finance_status' => 0,
        ]);

        // Super Admin audit approve -> ALLOWED (200)
        $resSuperAudit = $this->postJson("/api/nextjs/payroll/resignations/audit-approve/{$resignationId}", [
            'remarks' => 'Approved by Super Admin'
        ], $superAdminHeaders);
        $resSuperAudit->assertStatus(200);

        // Super Admin finance pay -> ALLOWED (200)
        $resSuperPay = $this->postJson("/api/nextjs/payroll/resignations/finance-pay/{$resignationId}", [
            'payment_reference' => 'TEST-REF-SUPERADMIN',
            'payment_date' => now()->toDateString(),
            'remarks' => 'Disbursed by Super Admin'
        ], $superAdminHeaders);
        $resSuperPay->assertStatus(200);

        // Cleanup
        DB::table('resignation_requests')->where('id', $resignationId)->delete();
        DB::table('tblper')->where('ID', $staffId)->delete();
        DB::table('assign_user_role')->whereIn('userID', [$hrUserId, $auditUserId, $financeUserId, $superAdminUserId])->delete();
        DB::table('users')->whereIn('id', [$hrUserId, $auditUserId, $financeUserId, $superAdminUserId])->delete();
    }
}
