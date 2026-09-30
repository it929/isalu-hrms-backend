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
            'remarks' => 'Staff training and uniform deduction',
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
            'remarks' => 'Staff training and uniform deduction',
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

    public function test_multiple_other_deductions_in_same_month_captured_in_salary_breakdown()
    {
        $user = DB::table('tblper')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in tblper to run this test');
            return;
        }

        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $user->UserID ?? 1,
            'roleID' => 1,
        ]);

        $headers = ['X-User-Id' => $user->UserID ?? 1];

        // Ensure salary structure exists for user
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $user->ID],
            [
                'basic_salary' => 100000.00,
                'housing_allowance' => 0.00,
                'transport_allowance' => 0.00,
                'medical_allowance' => 0.00,
                'utility_allowance' => 0.00,
                'meal_allowance' => 0.00,
                'pension_rate' => 0.00,
                'tax_rate' => 0.00,
            ]
        );

        // Delete any existing other deduction setups and conpt for test month (December 2026)
        DB::table('payroll_conpt')
            ->where('staffID', $user->ID)
            ->where('month', 12)
            ->where('year', 2026)
            ->delete();

        DB::table('other_deduction_setups')
            ->where('staffId', $user->ID)
            ->where('start_month', '2026-12')
            ->delete();

        // Create two other deduction setups in December 2026 for the same staff member
        DB::table('other_deduction_setups')->insert([
            'staffId' => $user->ID,
            'deduction_type' => 'one_time',
            'calculation_mode' => 'days',
            'deduction_days' => 4,
            'daily_rate' => 5666.67,
            'monthly_salary' => 170000.00,
            'days_in_month' => 31,
            'total_amount' => 22666.67,
            'duration_months' => 1,
            'monthly_deduction' => 22666.67,
            'balance_remaining' => 22666.67,
            'start_month' => '2026-12',
            'end_month' => '2026-12',
            'remarks' => 'break rule',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('other_deduction_setups')->insert([
            'staffId' => $user->ID,
            'deduction_type' => 'one_time',
            'calculation_mode' => 'days',
            'deduction_days' => 1,
            'daily_rate' => 5666.67,
            'monthly_salary' => 170000.00,
            'days_in_month' => 31,
            'total_amount' => 5666.67,
            'duration_months' => 1,
            'monthly_deduction' => 5666.67,
            'balance_remaining' => 5666.67,
            'start_month' => '2026-12',
            'end_month' => '2026-12',
            'remarks' => 'gghnh',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Fetch salary breakdown for December 2026
        $response = $this->getJson("/api/nextjs/payroll/salary-breakdown?staff_id={$user->ID}&month=12&year=2026", $headers);
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals('success', $data['status']);

        $otherDeductions = $data['deductions']['other_deductions'];
        // Both deductions must be captured and summed: 22666.67 + 5666.67 = 28333.34
        $this->assertEquals(28333.34, (float)$otherDeductions['amount']);
        $this->assertCount(2, $otherDeductions['items']);
        $this->assertStringContainsString('break rule', $otherDeductions['remarks']);
        $this->assertStringContainsString('gghnh', $otherDeductions['remarks']);
    }
}
