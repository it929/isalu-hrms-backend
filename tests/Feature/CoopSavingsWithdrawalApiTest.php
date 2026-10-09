<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\CoopSavingsWithdrawal;

class CoopSavingsWithdrawalApiTest extends TestCase
{
    protected $superAdmin;
    protected $hrHeadUser;
    protected $financeHeadUser;
    protected $regularStaffUser;
    protected $staffMember;
    protected $savingsSetup;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Super Admin user
        $this->superAdmin = User::firstOrCreate(
            ['username' => 'test_admin_csw'],
            [
                'name' => 'Test CSW SuperAdmin',
                'email' => 'test_admin_csw@isalu.gov.ng',
                'password' => bcrypt('secret'),
                'is_global' => 1,
                'user_type' => 'technical',
                'status' => 1,
            ]
        );
        DB::table('assign_user_role')->updateOrInsert(
            ['userID' => $this->superAdmin->id, 'roleID' => 1],
            ['created_at' => now()]
        );

        // 2. Create HR Head user (roleID = 68)
        $this->hrHeadUser = User::firstOrCreate(
            ['username' => 'test_hr_head_csw'],
            [
                'name' => 'Test HR Head CSW',
                'email' => 'test_hr_head_csw@isalu.gov.ng',
                'password' => bcrypt('secret'),
                'is_global' => 0,
                'user_type' => 'staff',
                'status' => 1,
            ]
        );
        DB::table('assign_user_role')->updateOrInsert(
            ['userID' => $this->hrHeadUser->id, 'roleID' => 68],
            ['created_at' => now()]
        );

        // 3. Create Finance Head user (roleID = 69)
        $this->financeHeadUser = User::firstOrCreate(
            ['username' => 'test_finance_head_csw'],
            [
                'name' => 'Test Finance Head CSW',
                'email' => 'test_finance_head_csw@isalu.gov.ng',
                'password' => bcrypt('secret'),
                'is_global' => 0,
                'user_type' => 'staff',
                'status' => 1,
            ]
        );
        DB::table('assign_user_role')->updateOrInsert(
            ['userID' => $this->financeHeadUser->id, 'roleID' => 69],
            ['created_at' => now()]
        );

        // 4. Create Regular Staff user (roleID = 74 or general staff without HR/Finance head role)
        $this->regularStaffUser = User::firstOrCreate(
            ['username' => 'test_regular_staff_csw'],
            [
                'name' => 'Test Regular Staff CSW',
                'email' => 'test_regular_staff_csw@isalu.gov.ng',
                'password' => bcrypt('secret'),
                'is_global' => 0,
                'user_type' => 'staff',
                'status' => 1,
            ]
        );
        DB::table('assign_user_role')->where('userID', $this->regularStaffUser->id)->delete();
        DB::table('assign_user_role')->insert([
            'userID' => $this->regularStaffUser->id,
            'roleID' => 74, // sTaFf
            'created_at' => now(),
        ]);

        // 5. Create or fetch isolated test staff in tblper
        $this->staffMember = DB::table('tblper')->where('fileNo', 'TEST_CSW_99998')->first();
        if (!$this->staffMember) {
            $staffId = DB::table('tblper')->insertGetId([
                'fileNo' => 'TEST_CSW_99998',
                'surname' => 'TEST_CSW',
                'first_name' => 'ISOLATED_STAFF',
                'othernames' => 'SAVINGS',
                'staff_status' => 1,
                'rank' => 0,
            ]);
            $this->staffMember = DB::table('tblper')->where('ID', $staffId)->first();
        }

        // Clean up any old test withdrawal applications for this isolated staff
        DB::table('coop_savings_withdrawals')->where('staffId', $this->staffMember->ID)->delete();

        // 6. Ensure active savings setup exists with 500,000 balance
        $existingSavings = DB::table('coop_savings_setups')
            ->where('staffId', $this->staffMember->ID)
            ->where('is_active', 1)
            ->first();

        if ($existingSavings) {
            DB::table('coop_savings_setups')
                ->where('id', $existingSavings->id)
                ->update(['saving_balance' => 500000.00]);
            $this->savingsSetup = DB::table('coop_savings_setups')->where('id', $existingSavings->id)->first();
        } else {
            $id = DB::table('coop_savings_setups')->insertGetId([
                'staffId' => $this->staffMember->ID,
                'monthly_saving' => 20000.00,
                'saving_balance' => 500000.00,
                'is_active' => 1,
                'created_at' => now(),
            ]);
            $this->savingsSetup = DB::table('coop_savings_setups')->where('id', $id)->first();
        }
    }

    public function test_staff_list_and_details_endpoints()
    {
        $response = $this->withHeaders(['X-User-Id' => $this->superAdmin->id])
            ->getJson('/api/nextjs/payroll/coop-savings-withdrawal/staff-list');

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
        $this->assertTrue($response->json('isSuperAdmin'));
        $this->assertArrayHasKey('isHrHead', $response->json());
        $this->assertArrayHasKey('isFinanceHead', $response->json());

        $detailResponse = $this->withHeaders(['X-User-Id' => $this->superAdmin->id])
            ->getJson("/api/nextjs/payroll/coop-savings-withdrawal/staff-details/{$this->staffMember->ID}");

        $detailResponse->assertStatus(200);
        $detailResponse->assertJson(['status' => 'success']);
        $this->assertArrayHasKey('saving_balance', $detailResponse->json('data'));
        $this->assertArrayHasKey('max_withdrawable', $detailResponse->json('data'));
    }

    public function test_collateral_hold_validation_with_active_loan()
    {
        // Add an active loan with 300,000 balance remaining
        $loanId = DB::table('coop_loan_deduction_setups')->insertGetId([
            'staffId' => $this->staffMember->ID,
            'loan_amount' => 300000.00,
            'balance_remaining' => 300000.00,
            'is_active' => 1,
            'created_at' => now(),
        ]);

        try {
            // Attempt full withdrawal while having active loan balance -> must fail with 422
            $fullFail = $this->withHeaders(['X-User-Id' => $this->superAdmin->id])
                ->postJson('/api/nextjs/payroll/coop-savings-withdrawal/apply', [
                    'staffId' => $this->staffMember->ID,
                    'withdrawal_type' => 'full',
                    'requested_amount' => 500000.00,
                    'reason' => 'Emergency liquidation',
                    'bank_name' => 'Zenith Bank',
                    'account_number' => '1234567890',
                    'account_name' => 'Test Account',
                ]);

            $fullFail->assertStatus(422);
            $this->assertStringContainsString('Cannot liquidate full cooperative savings while you have an active cooperative loan', $fullFail->json('message'));

            // Attempt partial withdrawal of 250,000 (when free balance is 500,000 - 300,000 = 200,000) -> must fail
            $partialFail = $this->withHeaders(['X-User-Id' => $this->superAdmin->id])
                ->postJson('/api/nextjs/payroll/coop-savings-withdrawal/apply', [
                    'staffId' => $this->staffMember->ID,
                    'withdrawal_type' => 'partial',
                    'requested_amount' => 250000.00,
                    'reason' => 'Over-limit partial withdrawal',
                    'bank_name' => 'Zenith Bank',
                    'account_number' => '1234567890',
                    'account_name' => 'Test Account',
                ]);

            $partialFail->assertStatus(422);
            $this->assertStringContainsString('exceeds maximum withdrawable balance', $partialFail->json('message'));

            // Attempt valid partial withdrawal within 200,000 free balance (e.g., 100,000) -> must succeed
            $partialSuccess = $this->withHeaders(['X-User-Id' => $this->superAdmin->id])
                ->postJson('/api/nextjs/payroll/coop-savings-withdrawal/apply', [
                    'staffId' => $this->staffMember->ID,
                    'withdrawal_type' => 'partial',
                    'requested_amount' => 100000.00,
                    'reason' => 'Valid partial withdrawal',
                    'bank_name' => 'Zenith Bank',
                    'account_number' => '1234567890',
                    'account_name' => 'Test Account',
                ]);

            $partialSuccess->assertStatus(200);
            $this->assertEquals('pending', $partialSuccess->json('data.status'));
            $cswId = $partialSuccess->json('data.id');

            // Clean up test withdrawal
            DB::table('coop_savings_withdrawals')->where('id', $cswId)->delete();
        } finally {
            DB::table('coop_loan_deduction_setups')->where('id', $loanId)->delete();
        }
    }

    public function test_hr_head_approval_role_restrictions()
    {
        // 1. Submit application
        $applyRes = $this->withHeaders(['X-User-Id' => $this->superAdmin->id])
            ->postJson('/api/nextjs/payroll/coop-savings-withdrawal/apply', [
                'staffId' => $this->staffMember->ID,
                'withdrawal_type' => 'partial',
                'requested_amount' => 30000.00,
                'reason' => 'HR approval role test',
                'bank_name' => 'GTBank',
                'account_number' => '0123456789',
                'account_name' => 'Test Staff',
            ]);

        $applyRes->assertStatus(200);
        $cswId = $applyRes->json('data.id');

        // 2. Regular staff attempts HR review -> MUST fail (403 Access denied)
        $unauthorizedHrRes = $this->withHeaders(['X-User-Id' => $this->regularStaffUser->id])
            ->postJson("/api/nextjs/payroll/coop-savings-withdrawal/hr-review/{$cswId}", [
                'action' => 'approve',
                'approved_amount' => 30000.00,
                'notes' => 'Attempt by unauthorized staff',
            ]);

        $unauthorizedHrRes->assertStatus(403);
        $this->assertStringContainsString('Only Super Admin or staff with HR Head role', $unauthorizedHrRes->json('message'));

        // 3. User with HR HEAD role (roleID 68) approves -> MUST succeed (200)
        $authorizedHrRes = $this->withHeaders(['X-User-Id' => $this->hrHeadUser->id])
            ->postJson("/api/nextjs/payroll/coop-savings-withdrawal/hr-review/{$cswId}", [
                'action' => 'approve',
                'approved_amount' => 30000.00,
                'notes' => 'Approved by authorized HR Head',
            ]);

        $authorizedHrRes->assertStatus(200);
        $this->assertEquals('hr_approved', $authorizedHrRes->json('data.status'));

        // Clean up
        DB::table('coop_savings_withdrawals')->where('id', $cswId)->delete();
    }

    public function test_finance_head_approval_role_restrictions()
    {
        // 1. Submit and HR approve application
        $applyRes = $this->withHeaders(['X-User-Id' => $this->superAdmin->id])
            ->postJson('/api/nextjs/payroll/coop-savings-withdrawal/apply', [
                'staffId' => $this->staffMember->ID,
                'withdrawal_type' => 'partial',
                'requested_amount' => 40000.00,
                'reason' => 'Finance approval role test',
                'bank_name' => 'First Bank',
                'account_number' => '1122334455',
                'account_name' => 'Test Staff',
            ]);

        $applyRes->assertStatus(200);
        $cswId = $applyRes->json('data.id');

        // HR approve
        $this->withHeaders(['X-User-Id' => $this->hrHeadUser->id])
            ->postJson("/api/nextjs/payroll/coop-savings-withdrawal/hr-review/{$cswId}", [
                'action' => 'approve',
                'approved_amount' => 40000.00,
                'notes' => 'HR Head approved',
            ])->assertStatus(200);

        // 2. Regular staff attempts Finance payout -> MUST fail (403 Access denied)
        $unauthorizedFinRes = $this->withHeaders(['X-User-Id' => $this->regularStaffUser->id])
            ->postJson("/api/nextjs/payroll/coop-savings-withdrawal/finance-payout/{$cswId}", [
                'action' => 'pay',
                'payment_method' => 'bank_transfer',
                'payment_reference' => 'UNAUTH-REF-001',
                'payment_date' => date('Y-m-d'),
                'notes' => 'Attempt by unauthorized staff',
            ]);

        $unauthorizedFinRes->assertStatus(403);
        $this->assertStringContainsString('Only Super Admin or staff with Finance Head role', $unauthorizedFinRes->json('message'));

        // 3. User with FINANCE HEAD role (roleID 69) processes payout -> MUST succeed (200)
        $authorizedFinRes = $this->withHeaders(['X-User-Id' => $this->financeHeadUser->id])
            ->postJson("/api/nextjs/payroll/coop-savings-withdrawal/finance-payout/{$cswId}", [
                'action' => 'pay',
                'payment_method' => 'bank_transfer',
                'payment_reference' => 'FIN-HEAD-TRF-001',
                'payment_date' => date('Y-m-d'),
                'notes' => 'Approved and disbursed by Finance Head',
            ]);

        $authorizedFinRes->assertStatus(200);
        $this->assertEquals('paid', $authorizedFinRes->json('data.status'));

        // Clean up
        DB::table('coop_savings_withdrawals')->where('id', $cswId)->delete();
        DB::table('coop_savings_setups')->where('id', $this->savingsSetup->id)->update(['saving_balance' => 500000.00]);
    }

    public function test_full_workflow_hr_audit_finance_payout()
    {
        // 1. Submit partial withdrawal of 50,000
        $applyRes = $this->withHeaders(['X-User-Id' => $this->superAdmin->id])
            ->postJson('/api/nextjs/payroll/coop-savings-withdrawal/apply', [
                'staffId' => $this->staffMember->ID,
                'withdrawal_type' => 'partial',
                'requested_amount' => 50000.00,
                'reason' => 'Full workflow test',
                'bank_name' => 'Access Bank',
                'account_number' => '0098765432',
                'account_name' => 'Test Staff Name',
            ]);

        $applyRes->assertStatus(200);
        $cswId = $applyRes->json('data.id');

        // 2. HR Head Review -> Approve by Super Admin
        $hrRes = $this->withHeaders(['X-User-Id' => $this->superAdmin->id])
            ->postJson("/api/nextjs/payroll/coop-savings-withdrawal/hr-review/{$cswId}", [
                'action' => 'approve',
                'approved_amount' => 50000.00,
                'notes' => 'HR Head recommends approval.',
            ]);

        $hrRes->assertStatus(200);
        $this->assertEquals('hr_approved', $hrRes->json('data.status'));

        // 3. Audit Review -> Approve
        $auditRes = $this->withHeaders(['X-User-Id' => $this->superAdmin->id])
            ->postJson("/api/nextjs/payroll/coop-savings-withdrawal/audit-review/{$cswId}", [
                'action' => 'approve',
                'notes' => 'Audit verified savings record and collateral clearance.',
            ]);

        $auditRes->assertStatus(200);
        $this->assertEquals('audit_approved', $auditRes->json('data.status'));

        // 4. Finance Payout -> Pay by Super Admin
        $financeRes = $this->withHeaders(['X-User-Id' => $this->superAdmin->id])
            ->postJson("/api/nextjs/payroll/coop-savings-withdrawal/finance-payout/{$cswId}", [
                'action' => 'pay',
                'payment_method' => 'bank_transfer',
                'payment_reference' => 'NIBSS-TRF-987654',
                'payment_date' => date('Y-m-d'),
                'notes' => 'Disbursed to staff bank account.',
            ]);

        $financeRes->assertStatus(200);
        $this->assertEquals('paid', $financeRes->json('data.status'));
        $this->assertEquals(500000.00, (float)$financeRes->json('data.balance_before_payout'));
        $this->assertEquals(450000.00, (float)$financeRes->json('data.balance_after_payout'));

        // Verify savings setup was debited in database
        $updatedSavings = DB::table('coop_savings_setups')->where('id', $this->savingsSetup->id)->first();
        $this->assertEquals(450000.00, (float)$updatedSavings->saving_balance);

        // 5. Check voucher endpoint
        $voucherRes = $this->withHeaders(['X-User-Id' => $this->superAdmin->id])
            ->getJson("/api/nextjs/payroll/coop-savings-withdrawal/voucher/{$cswId}");

        $voucherRes->assertStatus(200);
        $this->assertEquals('paid', $voucherRes->json('data.status'));

        // Clean up
        DB::table('coop_savings_withdrawals')->where('id', $cswId)->delete();
        DB::table('coop_savings_setups')->where('id', $this->savingsSetup->id)->update(['saving_balance' => 500000.00]);
    }
}
