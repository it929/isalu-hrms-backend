<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CoopLoanDeductionSetupSalaryComputeTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_coop_loan_setup_compute_salary()
    {
        $employee = DB::table('tblper')
            ->where('rank', '!=', 2)
            ->where('staff_status', 1)
            ->first();

        if (!$employee) {
            $this->markTestSkipped('No active employee found for test.');
            return;
        }

        // Add role assignments so context resolves super admin privileges
        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $employee->UserID ?? 1,
            'roleID' => 1 // Super Admin
        ]);

        $headers = ['X-User-Id' => $employee->UserID ?? 1];

        // Ensure there is a salary structure
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $employee->ID],
            [
                'basic_salary' => 100000.00,
                'housing_allowance' => 20000.00,
                'transport_allowance' => 10000.00,
                'medical_allowance' => 5000.00,
                'utility_allowance' => 5000.00,
                'meal_allowance' => 5000.00,
                'tax_rate' => 5.00,
                'pension_rate' => 8.00,
                'pen_act' => 1,
                'reten_act' => 0
            ]
        );

        // Clear existing coop loan setups for this employee
        DB::table('coop_loan_deduction_setups')->where('staffId', $employee->ID)->delete();

        // Create an active coop loan setup
        $setupId = DB::table('coop_loan_deduction_setups')->insertGetId([
            'staffId' => $employee->ID,
            'loan_amount' => 50000.00,
            'interest_rate' => 0.00,
            'duration_months' => 5,
            'monthly_deduction' => 10000.00,
            'balance_remaining' => 50000.00,
            'start_month' => '2026-05',
            'end_month' => '2026-09',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Run computeSalary
        $response = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'MAY',
            'year' => '2026'
        ], $headers);

        $response->assertStatus(200);

        // Verify coop_loan_deduction_setups remaining balance has decremented to 40000.00
        $balance = DB::table('coop_loan_deduction_setups')->where('id', $setupId)->value('balance_remaining');
        $this->assertEquals(40000.00, (float)$balance);

        // Verify conpt row contains coop_loan_rpyt as 10000.00
        $runId = $response->json('payroll_run_id');
        $this->assertDatabaseHas('payroll_conpt', [
            'payroll_run_id' => $runId,
            'staffID' => $employee->ID,
            'coop_loan_rpyt' => 10000.00
        ]);

        // Run computeSalary again to verify reversion and recalculation
        $response2 = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'MAY',
            'year' => '2026'
        ], $headers);

        $response2->assertStatus(200);

        // Balance should still be 40000.00 (because the old conpt row was reverted/added back before recalculation)
        $balance2 = DB::table('coop_loan_deduction_setups')->where('id', $setupId)->value('balance_remaining');
        $this->assertEquals(40000.00, (float)$balance2);
    }
}
