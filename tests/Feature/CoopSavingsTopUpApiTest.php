<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CoopSavingsTopUpApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_staff_list_returns_active_staff_with_savings_balances()
    {
        $employee = DB::table('tblper')->where('staff_status', 1)->first();
        if (!$employee) {
            $this->markTestSkipped('No active staff member found');
        }

        // Assign Super Admin role
        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $employee->UserID ?? 1,
            'roleID' => 1,
        ]);

        $headers = ['X-User-Id' => $employee->UserID ?? 1];

        $response = $this->getJson('/api/nextjs/payroll/coop-savings-top-up/staff-list', $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertIsArray($response->json('data'));
    }

    public function test_admin_can_record_top_up_and_balance_increments()
    {
        $employee = DB::table('tblper')->where('staff_status', 1)->first();
        if (!$employee) {
            $this->markTestSkipped('No active staff member found');
        }

        $userId = $employee->UserID ?? 1;

        // Assign Super Admin
        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $userId,
            'roleID' => 1,
        ]);

        $headers = ['X-User-Id' => $userId];

        // Ensure a starting savings balance
        $setup = DB::table('coop_savings_setups')->where('staffId', $employee->ID)->first();
        $initialBalance = $setup ? (float)$setup->saving_balance : 0.00;
        $topUpAmount = 25000.00;

        $response = $this->postJson('/api/nextjs/payroll/coop-savings-top-up', [
            'staffId'           => $employee->ID,
            'amount'            => $topUpAmount,
            'payment_method'    => 'bank_transfer',
            'payment_reference' => 'TEST-REF-' . time(),
            'payment_date'      => date('Y-m-d'),
            'notes'             => 'Test cooperative savings top-up deposit',
        ], $headers);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'amount'         => $topUpAmount,
                    'balance_before' => $initialBalance,
                    'balance_after'  => $initialBalance + $topUpAmount,
                ]
            ]);

        // Verify balance in coop_savings_setups
        $this->assertDatabaseHas('coop_savings_setups', [
            'staffId'        => $employee->ID,
            'saving_balance' => $initialBalance + $topUpAmount,
        ]);

        // Verify entry in coop_savings_top_ups
        $this->assertDatabaseHas('coop_savings_top_ups', [
            'staffId' => $employee->ID,
            'amount'  => $topUpAmount,
        ]);
    }

    public function test_history_and_receipt_endpoints()
    {
        $employee = DB::table('tblper')->where('staff_status', 1)->first();
        if (!$employee) {
            $this->markTestSkipped('No active staff member found');
        }

        $userId = $employee->UserID ?? 1;

        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $userId,
            'roleID' => 1,
        ]);

        $headers = ['X-User-Id' => $userId];

        // Record a top-up first
        $storeRes = $this->postJson('/api/nextjs/payroll/coop-savings-top-up', [
            'staffId'           => $employee->ID,
            'amount'            => 10000.00,
            'payment_method'    => 'cash',
            'payment_reference' => 'CASH-REC-' . time(),
            'payment_date'      => date('Y-m-d'),
            'notes'             => 'Cash top-up deposit',
        ], $headers);

        $storeRes->assertStatus(200);
        $topUpId = $storeRes->json('data.id');

        // Test history
        $histRes = $this->getJson('/api/nextjs/payroll/coop-savings-top-up/history?staffId=' . $employee->ID, $headers);
        $histRes->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertNotEmpty($histRes->json('data'));

        // Test receipt
        $receiptRes = $this->getJson("/api/nextjs/payroll/coop-savings-top-up/receipt/{$topUpId}", $headers);
        $receiptRes->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id'     => $topUpId,
                    'amount' => 10000.00,
                ]
            ]);
    }

    public function test_super_admin_can_reverse_top_up()
    {
        $employee = DB::table('tblper')->where('staff_status', 1)->first();
        if (!$employee) {
            $this->markTestSkipped('No active staff member found');
        }

        $userId = $employee->UserID ?? 1;

        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $userId,
            'roleID' => 1,
        ]);

        $headers = ['X-User-Id' => $userId];

        // Record a top-up
        $storeRes = $this->postJson('/api/nextjs/payroll/coop-savings-top-up', [
            'staffId'           => $employee->ID,
            'amount'            => 15000.00,
            'payment_method'    => 'direct_deposit',
            'payment_reference' => 'DEP-' . time(),
            'payment_date'      => date('Y-m-d'),
            'notes'             => 'Deposit to be reversed',
        ], $headers);

        $storeRes->assertStatus(200);
        $topUpId = $storeRes->json('data.id');
        $balanceAfter = $storeRes->json('data.balance_after');

        // Delete / Reverse
        $delRes = $this->deleteJson("/api/nextjs/payroll/coop-savings-top-up/{$topUpId}", [], $headers);
        $delRes->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing('coop_savings_top_ups', [
            'id' => $topUpId,
        ]);

        // Savings balance should be reverted
        $setup = DB::table('coop_savings_setups')->where('staffId', $employee->ID)->first();
        $this->assertEquals($balanceAfter - 15000.00, (float)$setup->saving_balance);
    }
}
