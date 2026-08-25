<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AbsencePenaltyDeductionSetupApiTest extends TestCase
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
        $response = $this->getJson('/api/nextjs/payroll/absence-penalty-deduction-setups', $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // Test staff salary retrieval
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $user->ID],
            [
                'basic_salary' => 120000.00,
                'housing_allowance' => 30000.00,
                'transport_allowance' => 0.00,
                'medical_allowance' => 0.00,
                'utility_allowance' => 0.00,
                'meal_allowance' => 0.00,
                'created_at' => now(),
            ]
        );

        $salaryRes = $this->getJson("/api/nextjs/payroll/absence-penalty-deduction-setups/staff-salary/{$user->ID}?month=06&year=2026", $headers);
        $salaryRes->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'staff_id' => $user->ID,
                    'monthly_salary' => 150000.00,
                    'days_in_month' => 30,
                    'daily_salary' => 5000.00,
                    'penalty_multiplier' => 3,
                ]
            ]);

        // Test February (28 days in 2026) -> Daily = 150,000 / 28 = 5,357.14
        $febSalaryRes = $this->getJson("/api/nextjs/payroll/absence-penalty-deduction-setups/staff-salary/{$user->ID}?month=02&year=2026", $headers);
        $febSalaryRes->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'staff_id' => $user->ID,
                    'monthly_salary' => 150000.00,
                    'days_in_month' => 28,
                    'daily_salary' => 5357.14,
                    'penalty_multiplier' => 3,
                ]
            ]);

        // Create setup with absent_days = 2 (Penalty = 6 days = 30,000.00)
        $response = $this->postJson('/api/nextjs/payroll/absence-penalty-deduction-setups', [
            'staffId' => $user->ID,
            'month' => '06',
            'year' => '2026',
            'absent_days' => 2,
            'remarks' => 'Absent 2 days without approval',
            'is_active' => 1,
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('absence_penalty_deduction_setups', [
            'staffId' => $user->ID,
            'absent_days' => 2,
            'penalty_multiplier' => 3,
            'penalty_days' => 6,
            'total_amount' => 30000.00,
            'monthly_deduction' => 30000.00,
            'start_month' => '2026-06',
            'end_month' => '2026-06',
            'remarks' => 'Absent 2 days without approval',
        ]);

        // Get the setup ID
        $setup = DB::table('absence_penalty_deduction_setups')->where('staffId', $user->ID)->first();
        $this->assertNotNull($setup);

        // Toggle status
        $response = $this->postJson("/api/nextjs/payroll/absence-penalty-deduction-setups/toggle/{$setup->id}", [], $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // Delete setup
        $response = $this->deleteJson("/api/nextjs/payroll/absence-penalty-deduction-setups/{$setup->id}", [], $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing('absence_penalty_deduction_setups', ['id' => $setup->id]);
    }
}
