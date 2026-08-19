<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MedicalLoanEntryApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_medical_loan_entry_flow()
    {
        // 1. Get a test user context
        $user = DB::table('tblper')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in tblper to run this test');
            return;
        }

        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $user->UserID ?? 1,
            'roleID' => 1, // Super Admin
        ]);

        $headers = ['X-User-Id' => $user->UserID ?? 1];

        // Clean any existing medical loan setup for this user
        DB::table('medical_loan_deduction_setups')->where('staffId', $user->ID)->delete();
        DB::table('medical_loan_entries')->where('staffId', $user->ID)->delete();

        // 2. Fetch index
        $response = $this->getJson('/api/nextjs/payroll/medical-loan-entries', $headers);
        $response->assertStatus(200)->assertJson(['status' => 'success']);

        // 3. Check staff balance before entry
        $balRes = $this->getJson("/api/nextjs/payroll/medical-loan-entries/staff-balance/{$user->ID}", $headers);
        $balRes->assertStatus(200)
            ->assertJsonPath('current_setup.balance_remaining', 0);

        // 4. Record first medical loan entry of 10,000
        $entry1 = $this->postJson('/api/nextjs/payroll/medical-loan-entries', [
            'staffId' => $user->ID,
            'loan_date' => '2026-08-10',
            'amount' => 10000.00,
            'reason' => 'Emergency prescription & treatment',
        ], $headers);

        $entry1->assertStatus(200)->assertJson(['status' => 'success']);

        // Verify setup created with balance 10,000 and monthly deduction 5,000
        $setup1 = DB::table('medical_loan_deduction_setups')->where('staffId', $user->ID)->first();
        $this->assertNotNull($setup1);
        $this->assertEquals(10000.00, (float)$setup1->balance_remaining);
        $this->assertEquals(5000.00, (float)$setup1->monthly_deduction);

        // 5. Now record a second medical loan entry of 50,000 for the same staff
        // (As requested by user: e.g. balance 10,000 + 50,000 = 60,000, new monthly deduction = 15,000)
        $entry2 = $this->postJson('/api/nextjs/payroll/medical-loan-entries', [
            'staffId' => $user->ID,
            'loan_date' => '2026-08-19',
            'amount' => 50000.00,
            'reason' => 'Hospital admission & surgery support',
        ], $headers);

        $entry2->assertStatus(200)->assertJson(['status' => 'success']);

        // Verify setup updated with balance remaining = 60,000 and monthly deduction = 15,000
        $setup2 = DB::table('medical_loan_deduction_setups')->where('staffId', $user->ID)->first();
        $this->assertEquals(60000.00, (float)$setup2->balance_remaining);
        $this->assertEquals(15000.00, (float)$setup2->monthly_deduction);
        $this->assertEquals(1, $setup2->is_active);

        // Verify entries table has 2 records
        $entries = DB::table('medical_loan_entries')->where('staffId', $user->ID)->get();
        $this->assertCount(2, $entries);

        // 6. Delete entry 2 and verify balance rolls back from 60,000 to 10,000
        $lastEntry = DB::table('medical_loan_entries')->where('staffId', $user->ID)->orderBy('id', 'desc')->first();
        $delRes = $this->deleteJson("/api/nextjs/payroll/medical-loan-entries/{$lastEntry->id}", [], $headers);
        $delRes->assertStatus(200);

        $setup3 = DB::table('medical_loan_deduction_setups')->where('staffId', $user->ID)->first();
        $this->assertEquals(10000.00, (float)$setup3->balance_remaining);
        $this->assertEquals(5000.00, (float)$setup3->monthly_deduction);
    }
}
