<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CoopLoanDeductionSetupApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_download_template()
    {
        $employee = DB::table('tblper')->first();
        if (!$employee) {
            $this->markTestSkipped('No user found');
        }
        $headers = ['X-User-Id' => $employee->UserID ?? 1];

        $response = $this->get('/api/nextjs/payroll/coop-loan-deduction-setups/template', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringContainsString('Staff ID', $content);
        $this->assertStringContainsString('Amount Deduct Monthly', $content);
        $this->assertStringContainsString('Balance', $content);
    }

    public function test_bulk_import_with_amount_deduct_monthly_and_balance()
    {
        $employee = DB::table('tblper')->first();
        if (!$employee) {
            $this->markTestSkipped('No user found');
        }

        // Add role assignments so context resolves super admin privileges
        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $employee->UserID ?? 1,
            'roleID' => 1 // Super Admin
        ]);

        $headers = ['X-User-Id' => $employee->UserID ?? 1];
        
        $fileData = "Staff ID,Amount Deduct Monthly,Balance,Start Month (YYYY-MM)\n" .
                    "{$employee->ID},25000.00,150000.00,2026-06\n";
                    
        $uploadedFile = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'coop_loan_setup_import.csv',
            $fileData
        );

        $response = $this->postJson('/api/nextjs/payroll/coop-loan-deduction-setups/import', [
            'file' => $uploadedFile
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('coop_loan_deduction_setups', [
            'staffId' => $employee->ID,
            'monthly_deduction' => 25000.00,
            'balance_remaining' => 150000.00,
            'duration_months' => 6,
            'start_month' => '2026-06',
            'is_active' => 1,
        ]);
    }

    public function test_only_super_admin_can_manually_deactivate_cooperative_loan_setup()
    {
        $employee = DB::table('tblper')->first();
        if (!$employee) {
            $this->markTestSkipped('No user found');
        }

        // 1. Create an active setup
        $setupId = DB::table('coop_loan_deduction_setups')->insertGetId([
            'staffId' => $employee->ID,
            'loan_amount' => 100000.00,
            'interest_rate' => 0,
            'duration_months' => 5,
            'monthly_deduction' => 20000.00,
            'balance_remaining' => 100000.00,
            'start_month' => '2026-06',
            'end_month' => '2026-10',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a non-super-admin user (e.g. role 2)
        $nonAdminUserId = DB::table('users')->insertGetId([
            'name' => 'Regular HR Staff',
            'username' => 'regular_hr_' . uniqid(),
            'email' => 'regular_hr_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('assign_user_role')->insert([
            'userID' => $nonAdminUserId,
            'roleID' => 2 // Non super admin
        ]);

        $superAdminUserId = DB::table('users')->insertGetId([
            'name' => 'Super Admin User',
            'username' => 'super_admin_' . uniqid(),
            'email' => 'super_admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('assign_user_role')->insert([
            'userID' => $superAdminUserId,
            'roleID' => 1 // Super Admin
        ]);

        // 2. Finance Head user setup
        $financeUserId = DB::table('users')->insertGetId([
            'name' => 'Finance Head User',
            'email' => 'finhead_' . uniqid() . '@isalu.gov.ng',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('assign_user_role')->insert([
            'userID' => $financeUserId,
            'roleID' => 36 // Finance Head
        ]);

        // 3. Non-authorized user (e.g. regular staff) tries to toggle -> should be 403 Forbidden
        $responseNonAdmin = $this->postJson("/api/nextjs/payroll/coop-loan-deduction-setups/toggle/{$setupId}", [], [
            'X-User-Id' => $nonAdminUserId
        ]);
        $responseNonAdmin->assertStatus(403);
        $responseNonAdmin->assertJsonFragment([
            'status' => 'error',
            'message' => 'Permission denied: Only Super Administrators and Finance Head are authorized to activate and deactivate Cooperative Loan Deduction Setup.'
        ]);

        // Assert database record is still active (1)
        $this->assertEquals(1, DB::table('coop_loan_deduction_setups')->where('id', $setupId)->value('is_active'));

        // 4. Non-authorized user tries to update is_active to 0 via store endpoint -> should also be 403 Forbidden
        $responseStoreNonAdmin = $this->postJson("/api/nextjs/payroll/coop-loan-deduction-setups", [
            'id' => $setupId,
            'staffId' => $employee->ID,
            'loan_amount' => 100000.00,
            'interest_rate' => 0,
            'duration_months' => 5,
            'monthly_deduction' => 20000.00,
            'balance_remaining' => 100000.00,
            'start_month' => '2026-06',
            'end_month' => '2026-10',
            'is_active' => 0,
        ], [
            'X-User-Id' => $nonAdminUserId
        ]);
        $responseStoreNonAdmin->assertStatus(403);

        // 5. Super admin toggles to inactive (0) -> should succeed
        $responseSuperAdmin = $this->postJson("/api/nextjs/payroll/coop-loan-deduction-setups/toggle/{$setupId}", [], [
            'X-User-Id' => $superAdminUserId
        ]);
        $responseSuperAdmin->assertStatus(200);
        $this->assertEquals(0, DB::table('coop_loan_deduction_setups')->where('id', $setupId)->value('is_active'));

        // 6. Finance Head activates inactive setup (1) -> should succeed
        $responseFinanceActivate = $this->postJson("/api/nextjs/payroll/coop-loan-deduction-setups/toggle/{$setupId}", [], [
            'X-User-Id' => $financeUserId
        ]);
        $responseFinanceActivate->assertStatus(200);
        $this->assertEquals(1, DB::table('coop_loan_deduction_setups')->where('id', $setupId)->value('is_active'));

        // 7. Finance Head deactivates setup (0) -> should succeed
        $responseFinanceDeactivate = $this->postJson("/api/nextjs/payroll/coop-loan-deduction-setups/toggle/{$setupId}", [], [
            'X-User-Id' => $financeUserId
        ]);
        $responseFinanceDeactivate->assertStatus(200);
        $this->assertEquals(0, DB::table('coop_loan_deduction_setups')->where('id', $setupId)->value('is_active'));
    }
}
