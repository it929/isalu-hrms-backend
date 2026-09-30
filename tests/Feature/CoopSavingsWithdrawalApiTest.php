<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\CoopSavingsWithdrawal;

class CoopSavingsWithdrawalApiTest extends TestCase
{
    protected $superAdmin;
    protected $staffMember;
    protected $savingsSetup;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create or fetch a super admin user
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

        // Assign Super Admin role (roleID = 1)
        DB::table('assign_user_role')->updateOrInsert(
            ['userID' => $this->superAdmin->id, 'roleID' => 1],
            ['created_at' => now()]
        );

        // 2. Fetch or create a test staff in tblper
        $this->staffMember = DB::table('tblper')->where('ID', 1546)->first();
        if (!$this->staffMember) {
            $staffId = DB::table('tblper')->insertGetId([
                'ID' => 99999,
                'fileNo' => 'TEST99999',
                'surname' => 'TEST_SURNAME',
                'first_name' => 'TEST_FIRSTNAME',
                'othernames' => 'TEST_OTHER',
                'staff_status' => 1,
                'rank' => 0,
            ]);
            $this->staffMember = DB::table('tblper')->where('ID', $staffId)->first();
        }

        // 3. Ensure an active savings setup exists with 500,000 balance
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
            'monthly_deduction' => 25000.00,
            'is_active' => 1,
            'created_at' => now(),
        ]);

        try {
            // Attempt full withdrawal while active loan exists -> must fail with 422
            $fullFail = $this->withHeaders(['X-User-Id' => $this->superAdmin->id])
                ->postJson('/api/nextjs/payroll/coop-savings-withdrawal/apply', [
                    'staffId' => $this->staffMember->ID,
                    'withdrawal_type' => 'full',
                    'requested_amount' => 500000.00,
                    'reason' => 'Relocation liquidation test',
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
                    'reason' => 'Emergency medical expenses',
                    'bank_name' => 'Zenith Bank',
                    'account_number' => '1234567890',
                    'account_name' => 'Test Account',
                ]);

            $partialFail->assertStatus(422);
            $this->assertStringContainsString('exceeds maximum withdrawable balance', $partialFail->json('message'));

            // Partial withdrawal of 100,000 (under 200,000 free balance) -> must succeed!
            $partialSuccess = $this->withHeaders(['X-User-Id' => $this->superAdmin->id])
                ->postJson('/api/nextjs/payroll/coop-savings-withdrawal/apply', [
                    'staffId' => $this->staffMember->ID,
                    'withdrawal_type' => 'partial',
                    'requested_amount' => 100000.00,
                    'reason' => 'Emergency school fees payment',
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

        // 2. HR Head Review -> Approve
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

        // 4. Finance Payout -> Pay
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
