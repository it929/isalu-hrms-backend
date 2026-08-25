<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserActivityReportApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_and_logout_records_user_activity_logs()
    {
        // Create test user
        $username = 'act_test_' . time();
        $userId = DB::table('users')->insertGetId([
            'name' => 'Activity Test User',
            'username' => $username,
            'email' => "{$username}@test.com",
            'password' => Hash::make('password123'),
            'user_type' => 'staff',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $staffId = DB::table('tblper')->insertGetId([
            'UserID' => $userId,
            'fileNo' => 'PF-' . time(),
            'surname' => 'Activity',
            'first_name' => 'Test',
            'othernames' => 'Staff',
            'staff_status' => 1,
            'rank' => 1,
            'departmentID' => 1,
        ]);

        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $userId,
            'roleID' => 1,
        ]);

        // 1. Test Login endpoint
        $loginRes = $this->postJson('/api/nextjs/login', [
            'username' => $username,
            'password' => 'password123'
        ]);

        $loginRes->assertStatus(200);

        // Verify login log in user_activity_logs
        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $userId,
            'activity_type' => 'login'
        ]);

        // 2. Test state-modifying action
        $headers = ['X-User-Id' => $userId];
        $actionRes = $this->postJson('/api/nextjs/hod-delegations', [
            'staff_id' => $staffId,
            'delegate_staff_id' => $staffId,
            'department_id' => 1,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
            'reason' => 'Audit test delegation',
            'permissions' => ['approve_leave' => true]
        ], $headers);

        $actionRes->assertStatus(200);

        // Verify activity log captured
        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $userId,
            'activity_type' => 'create'
        ]);

        // 3. Test Logout endpoint
        $logoutRes = $this->postJson('/api/nextjs/logout', [], $headers);
        $logoutRes->assertStatus(200);

        // Verify logout log in user_activity_logs
        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $userId,
            'activity_type' => 'logout'
        ]);

        // 4. Test Report endpoint
        $reportRes = $this->getJson('/api/nextjs/reports/user-activities', $headers);
        $reportRes->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['id', 'user', 'role', 'activity_type', 'action', 'module', 'method', 'date', 'ipAddress']
                ],
                'summary' => ['total_records', 'logins_today', 'logouts_today', 'actions_today', 'active_users_today']
            ]);

        $data = $reportRes->json();
        $this->assertGreaterThanOrEqual(3, count($data['data']));

        // 5. Test Export endpoint
        $exportRes = $this->get('/api/nextjs/reports/user-activities/export', $headers);
        $exportRes->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportRes->headers->get('Content-Type'));
        $csvContent = $exportRes->streamedContent();
        $this->assertStringContainsString('USER / STAFF NAME', $csvContent);
        $this->assertStringContainsString('LOGIN', $csvContent);
    }

    public function test_all_application_actions_and_approvals_are_captured()
    {
        $username = 'full_act_' . time();
        $userId = DB::table('users')->insertGetId([
            'name' => 'Complete Action User',
            'username' => $username,
            'email' => "{$username}@test.com",
            'password' => Hash::make('password123'),
            'user_type' => 'staff',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $staffId = DB::table('tblper')->insertGetId([
            'UserID' => $userId,
            'fileNo' => 'ACT-' . time(),
            'surname' => 'Auditor',
            'first_name' => 'Approve',
            'othernames' => 'Staff',
            'staff_status' => 1,
            'rank' => 1,
            'departmentID' => 1,
        ]);

        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $userId,
            'roleID' => 1,
        ]);

        $headers = ['X-User-Id' => $userId];

        // 1. Leave application creation & approval
        $leaveId = DB::table('leave_record')->insertGetId([
            'staffId' => $staffId,
            'leave_type_id' => 1,
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-10',
            'status' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson("/api/nextjs/hr/apply-leave/hod-approve/{$leaveId}", $headers);
        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $userId,
            'activity_type' => 'approval',
            'action' => 'HOD Recommended Leave Application for Auditor Approve'
        ]);

        // 2. Active month lock
        DB::table('payroll_conpt')->updateOrInsert(
            ['year' => 2026, 'month' => 8],
            ['salary_lock' => 0, 'vstage' => 0, 'created_at' => now()]
        );
        $this->postJson('/api/nextjs/payroll/lock-active-month/lock', ['year' => 2026, 'month' => 'AUGUST'], $headers);
        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $userId,
            'action' => 'Locked Active Payroll Month'
        ]);

        // 3. Staff documentation update
        $this->postJson("/api/nextjs/hr/documentation/{$staffId}/basic", [
            'title' => 'Mr',
            'surname' => 'Auditor',
            'first_name' => 'Approve',
            'gender' => 'Male',
        ], $headers);
        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $userId,
            'action' => 'Updated Staff Documentation (Basic) for Auditor Approve'
        ]);

        // 4. Update Staff IOU Limit Configuration
        $this->postJson('/api/nextjs/payroll/ious/limit-config', [
            'staff_id' => $staffId,
            'can_take_iou' => 1,
            'max_iou_amount' => 150000.00
        ], $headers);
        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $userId,
            'activity_type' => 'update',
            'action' => 'Updated Staff IOU Eligibility & Limit for Auditor Approve (Max Limit: ₦150,000.00)'
        ]);

        // 5. Update Staff Operational Status
        $this->postJson('/api/nextjs/hr/staff-status/update', [
            'fileNo' => $staffId,
            'action' => 'Update Staff Record',
            'staffStatus' => 'active service'
        ], $headers);
        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $userId,
            'activity_type' => 'update',
            'action' => 'Updated Staff Status for Auditor Approve to [Active Service]'
        ]);

        // 6. Update Staff Bank Details
        $bank = DB::table('tblbanklist')->first();
        $bankId = $bank ? $bank->bankID : null;
        $this->postJson('/api/nextjs/payroll/bank-updates/individual', [
            'staff_id' => $staffId,
            'bank_id' => $bankId,
            'account_number' => '0123456789',
            'payer_id' => 'PAY-TEST-999'
        ], $headers);
        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $userId,
            'activity_type' => 'update',
            'action' => 'Updated Staff Bank Details for Auditor Approve'
        ]);

        // 7. Salary Increment with Percentage
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $staffId],
            [
                'basic_salary' => 100000.00,
                'housing_allowance' => 20000.00,
                'transport_allowance' => 10000.00,
                'medical_allowance' => 5000.00,
                'utility_allowance' => 5000.00,
                'meal_allowance' => 5000.00,
            ]
        );

        $this->postJson('/api/nextjs/payroll/salary-increments/single', [
            'staff_id' => $staffId,
            'increment_type' => 'percentage',
            'percentage' => 10,
        ], $headers);
        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $userId,
            'activity_type' => 'update',
            'action' => 'Applied Staff Salary Increment (10% Increment) for Auditor Approve'
        ]);

        // 8. Staff Bonus & Allowance Setup
        $bonusRes = $this->postJson('/api/nextjs/payroll/bonus-allowance-setups', [
            'staffId' => $staffId,
            'type' => 'bonus',
            'category' => 'performance_bonus',
            'title' => 'Q3 Excellence Bonus',
            'amount' => 35000.00,
            'frequency' => 'one_time',
            'start_month' => '2026-08',
            'is_active' => 1,
        ], $headers);
        $bonusRes->assertStatus(200);

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $userId,
            'action' => 'Created Staff Bonus Setup (Q3 Excellence Bonus) [₦35,000.00] for Auditor Approve'
        ]);

        // 9. Cooperative Loan Deduction Setup
        $coopDeductRes = $this->postJson('/api/nextjs/payroll/coop-loan-deduction-setups', [
            'staffId' => $staffId,
            'loan_amount' => 120000.00,
            'interest_rate' => 5,
            'duration_months' => 6,
            'monthly_deduction' => 21000.00,
            'start_month' => '2026-08',
            'end_month' => '2027-01',
            'is_active' => 1,
        ], $headers);
        $coopDeductRes->assertStatus(200);

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $userId,
            'action' => 'Created Cooperative Loan Deduction Setup (₦21,000.00/month) for Auditor Approve'
        ]);

        // 10. Cooperative Savings Setup
        $coopSavingRes = $this->postJson('/api/nextjs/payroll/coop-savings-setups', [
            'staffId' => $staffId,
            'monthly_saving' => 15000.00,
            'saving_balance' => 30000.00,
            'start_month' => '2026-08',
            'is_active' => 1,
        ], $headers);
        $coopSavingRes->assertStatus(200);

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $userId,
            'action' => 'Created Cooperative Savings Setup (₦15,000.00/month) for Auditor Approve'
        ]);

        // 11. Coop Savings -> Loan Offset
        $loanSetup = DB::table('coop_loan_deduction_setups')->where('staffId', $staffId)->first();
        $savingSetup = DB::table('coop_savings_setups')->where('staffId', $staffId)->first();

        $offsetRes = $this->postJson('/api/nextjs/payroll/coop-savings-loan-offset', [
            'staffId' => $staffId,
            'offset_type' => 'savings',
            'savings_setup_id' => $savingSetup->id,
            'loan_setup_id' => $loanSetup->id,
            'offset_amount' => 10000.00,
            'notes' => 'Partial offset from savings',
        ], $headers);
        $offsetRes->assertStatus(200);

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $userId,
            'action' => 'Processed Cooperative Loan Offset via Cooperative Savings [₦10,000.00] for Auditor Approve'
        ]);

        // 12. Medical Loan Entry
        $medEntryRes = $this->postJson('/api/nextjs/payroll/medical-loan-entries', [
            'staffId' => $staffId,
            'loan_date' => '2026-08-01',
            'amount' => 50000.00,
            'reason' => 'Hospital admission test',
        ], $headers);
        $medEntryRes->assertStatus(200);

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $userId,
            'action' => 'Recorded New Medical Loan Entry [₦50,000.00] for Auditor Approve'
        ]);

        // 13. Surcharge Deduction Setup
        $surchargeRes = $this->postJson('/api/nextjs/payroll/surcharge-deduction-setups', [
            'staffId' => $staffId,
            'deduction_type' => 'spread',
            'total_amount' => 12000.00,
            'monthly_deduction' => 2000.00,
            'duration_months' => 6,
            'start_month' => '2026-08',
            'end_month' => '2027-01',
            'is_active' => 1,
        ], $headers);
        $surchargeRes->assertStatus(200);

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $userId,
            'action' => 'Created Surcharge Deduction Setup (₦2,000.00/month) for Auditor Approve'
        ]);

        // 14. Absence Penalty Deduction Setup
        $penaltyRes = $this->postJson('/api/nextjs/payroll/absence-penalty-deduction-setups', [
            'staffId' => $staffId,
            'absent_days' => 3,
            'start_month' => '2026-08',
            'monthly_deduction' => 15000.00,
            'is_active' => 1,
        ], $headers);
        $penaltyRes->assertStatus(200);

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $userId,
            'action' => 'Created Absence Penalty Deduction Setup (₦15,000.00/month) for Auditor Approve'
        ]);

        // 15. Other Deduction Setup
        $otherRes = $this->postJson('/api/nextjs/payroll/other-deduction-setups', [
            'staffId' => $staffId,
            'deduction_type' => 'spread',
            'total_amount' => 10000.00,
            'monthly_deduction' => 2500.00,
            'duration_months' => 4,
            'start_month' => '2026-08',
            'end_month' => '2026-11',
            'remarks' => 'Uniform replacement',
            'is_active' => 1,
        ], $headers);
        $otherRes->assertStatus(200);

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $userId,
            'action' => 'Created Other Deduction Setup (₦2,500.00/month) for Auditor Approve'
        ]);

        // 16. Coop Asset Finance Deduction Setup
        $assetRes = $this->postJson('/api/nextjs/payroll/coop-asset-finance-deduction-setups', [
            'staffId' => $staffId,
            'total_amount' => 150000.00,
            'duration_months' => 5,
            'monthly_deduction' => 30000.00,
            'start_month' => '2026-08',
            'end_month' => '2026-12',
            'is_active' => 1,
        ], $headers);
        $assetRes->assertStatus(200);

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $userId,
            'action' => 'Created Coop Asset Finance Deduction Setup (₦30,000.00/month) for Auditor Approve'
        ]);

        // 17. Salary Compute for all staff
        DB::table('payroll_conpt')->where('year', 2026)->where('month', 9)->delete();

        $computeRes = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 9,
            'year' => 2026,
        ], $headers);
        $computeRes->assertStatus(200);

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $userId,
            'action' => 'Computed Monthly Salary Payroll for September 2026 for All Active Staff'
        ]);
    }
}
