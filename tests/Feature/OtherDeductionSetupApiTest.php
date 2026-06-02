<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OtherDeductionSetupApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_api_endpoints()
    {
        // Get user context ID
        $user = DB::table('tblper')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in tblper to run this test');
            return;
        }

        // Add role assignments so the request context detects isSuperAdmin or isAdminStaff
        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $user->UserID ?? 1,
            'roleID' => 1, // Super Admin
        ]);

        $headers = ['X-User-Id' => $user->UserID ?? 1];

        // Fetch configurations
        $response = $this->getJson('/api/nextjs/payroll/other-deduction-setups', $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // Create setup
        $response = $this->postJson('/api/nextjs/payroll/other-deduction-setups', [
            'staffId' => $user->ID,
            'deduction_type' => 'spread',
            'total_amount' => 45000.00,
            'duration_months' => 3,
            'monthly_deduction' => 15000.00,
            'start_month' => '2026-06',
            'end_month' => '2026-08',
            'is_active' => 1,
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('other_deduction_setups', [
            'staffId' => $user->ID,
            'deduction_type' => 'spread',
            'total_amount' => 45000.00,
            'duration_months' => 3,
            'monthly_deduction' => 15000.00,
        ]);

        // Get the setup ID
        $setup = DB::table('other_deduction_setups')->where('staffId', $user->ID)->first();
        $this->assertNotNull($setup);

        // Toggle setup
        $response = $this->postJson("/api/nextjs/payroll/other-deduction-setups/toggle/{$setup->id}", [], $headers);
        $response->assertStatus(200);
        $this->assertEquals(0, DB::table('other_deduction_setups')->where('id', $setup->id)->value('is_active'));

        // Delete setup
        $response = $this->deleteJson("/api/nextjs/payroll/other-deduction-setups/{$setup->id}", [], $headers);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('other_deduction_setups', ['id' => $setup->id]);
    }
}
