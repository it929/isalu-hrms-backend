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

    public function test_medical_loan_records_date_range_filtering()
    {
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

        // Clean
        DB::table('medical_loan_deduction_setups')->where('staffId', $user->ID)->delete();
        DB::table('medical_loan_entries')->where('staffId', $user->ID)->delete();

        // Add 3 entries in different months
        DB::table('medical_loan_entries')->insert([
            [
                'staffId' => $user->ID,
                'loan_date' => '2026-01-15',
                'amount' => 12000.00,
                'reason' => 'January medical care',
                'balance_before' => 0.00,
                'balance_after' => 12000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'staffId' => $user->ID,
                'loan_date' => '2026-05-20',
                'amount' => 25000.00,
                'reason' => 'May dental care',
                'balance_before' => 12000.00,
                'balance_after' => 37000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'staffId' => $user->ID,
                'loan_date' => '2026-08-10',
                'amount' => 15000.00,
                'reason' => 'August optical checkup',
                'balance_before' => 37000.00,
                'balance_after' => 52000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        DB::table('medical_loan_deduction_setups')->insert([
            'staffId' => $user->ID,
            'loan_amount' => 52000.00,
            'balance_remaining' => 52000.00,
            'monthly_deduction' => 15000.00,
            'duration_months' => 4,
            'start_month' => '2026-08',
            'end_month' => '2026-11',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1. Query without filter -> 3 records
        $allRes = $this->getJson("/api/nextjs/payroll/medical-loan-entries?staffId={$user->ID}", $headers);
        $allRes->assertStatus(200);
        $this->assertCount(3, $allRes->json('data'));
        $this->assertEquals(52000.00, $allRes->json('summary.total_disbursed'));
        $this->assertEquals(52000.00, $allRes->json('staff_setup.balance_remaining'));

        // 2. Query from 2026-04-01 to 2026-06-30 -> should return only May entry
        $mayRes = $this->getJson("/api/nextjs/payroll/medical-loan-entries?staffId={$user->ID}&from_date=2026-04-01&to_date=2026-06-30", $headers);
        $mayRes->assertStatus(200);
        $this->assertCount(1, $mayRes->json('data'));
        $this->assertEquals(25000.00, $mayRes->json('summary.total_disbursed'));
        $this->assertEquals('May dental care', $mayRes->json('data.0.reason'));

        // 3. Query from 2026-05-01 to 2026-08-31 -> should return May & August entries
        $twoRes = $this->getJson("/api/nextjs/payroll/medical-loan-entries?staffId={$user->ID}&from_date=2026-05-01&to_date=2026-08-31", $headers);
        $twoRes->assertStatus(200);
        $this->assertCount(2, $twoRes->json('data'));
        $this->assertEquals(40000.00, $twoRes->json('summary.total_disbursed'));
    }

    public function test_regular_staff_only_sees_own_records_and_cannot_access_others()
    {
        $users = DB::table('tblper')->limit(2)->get();
        if ($users->count() < 2) {
            $this->markTestSkipped('Need at least 2 users to test staff record isolation');
            return;
        }

        $staffUser = $users[0];
        $otherUser = $users[1];

        // Ensure staffUser has a regular role (not super admin, hr, audit, or finance)
        DB::table('assign_user_role')->where('userID', $staffUser->UserID)->delete();
        DB::table('assign_user_role')->insert([
            'userID' => $staffUser->UserID,
            'roleID' => 999, // Regular employee role
        ]);

        $headers = ['X-User-Id' => $staffUser->UserID];

        // Clean
        DB::table('medical_loan_entries')->whereIn('staffId', [$staffUser->ID, $otherUser->ID])->delete();

        // Add 1 entry for staffUser
        DB::table('medical_loan_entries')->insert([
            'staffId' => $staffUser->ID,
            'loan_date' => '2026-08-01',
            'amount' => 15000.00,
            'reason' => 'Staff own medical loan',
            'balance_before' => 0.00,
            'balance_after' => 15000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Add 1 entry for otherUser
        DB::table('medical_loan_entries')->insert([
            'staffId' => $otherUser->ID,
            'loan_date' => '2026-08-05',
            'amount' => 80000.00,
            'reason' => 'Other staff confidential loan',
            'balance_before' => 0.00,
            'balance_after' => 80000.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Staff queries index without parameters -> should ONLY get their own 1 record
        $res = $this->getJson('/api/nextjs/payroll/medical-loan-entries', $headers);
        $res->assertStatus(200);
        $this->assertCount(1, $res->json('data'));
        $this->assertEquals('Staff own medical loan', $res->json('data.0.reason'));
        $this->assertEquals(15000.00, $res->json('summary.total_disbursed'));
        $this->assertFalse($res->json('isPrivileged'));

        // Staff attempts to pass otherUser's staffId -> should STILL only get their own record
        $exploitRes = $this->getJson("/api/nextjs/payroll/medical-loan-entries?staffId={$otherUser->ID}", $headers);
        $exploitRes->assertStatus(200);
        $this->assertCount(1, $exploitRes->json('data'));
        $this->assertEquals('Staff own medical loan', $exploitRes->json('data.0.reason'));
    }
}
