<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FinanceHeadPayrollWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_finance_head_can_compute_recompute_lock_and_forward_to_audit()
    {
        // 1. Find or create an employee with Finance Head role
        $financeEmployee = DB::table('tblper')
            ->where('rank', '!=', 2)
            ->where('staff_status', 1)
            ->first();

        if (!$financeEmployee) {
            $this->markTestSkipped('No active employee found to run this test');
            return;
        }

        $userId = $financeEmployee->UserID ?? 1;

        // Assign Finance Head role (roleID: 36 or 69 or name: 'Finance Head')
        DB::table('assign_user_role')->where('userID', $userId)->delete();
        DB::table('assign_user_role')->insert([
            'userID' => $userId,
            'roleID' => 69 // Finance Head
        ]);

        $headers = ['X-User-Id' => $userId];

        // Setup active month config
        DB::table('tblactivemonth')->delete();
        DB::table('tblactivemonth')->insert([
            'month'   => 'NOVEMBER',
            'year'    => 2026,
            'courtID' => 9
        ]);

        // Ensure salary structure exists for employee
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $financeEmployee->ID],
            [
                'basic_salary'        => 120000.00,
                'housing_allowance'   => 30000.00,
                'transport_allowance' => 15000.00,
                'medical_allowance'   => 5000.00,
                'utility_allowance'   => 5000.00,
                'meal_allowance'      => 5000.00,
                'tax_rate'            => 5.00,
                'pension_rate'        => 8.00,
                'pen_act'             => 1,
                'reten_act'           => 0
            ]
        );

        // 2. Fetch lock-active-month metadata and assert Finance Head context
        $res = $this->getJson('/api/nextjs/payroll/lock-active-month', $headers);
        $res->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('userCtx.isFinanceStaff', true)
            ->assertJsonPath('userCtx.canManage', true);

        // 3. Run Salary Compute as Finance Head
        $computeRes = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'NOVEMBER',
            'year'  => '2026'
        ], $headers);

        $computeRes->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('payroll_conpt', [
            'staffID'     => $financeEmployee->ID,
            'month'       => 11,
            'year'        => 2026,
            'salary_lock' => 0
        ]);

        // 4. Recompute salaries (before locking) as Finance Head
        $recomputeRes = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'NOVEMBER',
            'year'  => '2026'
        ], $headers);

        $recomputeRes->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // 5. Lock Active Month as Finance Head
        $lockRes = $this->postJson('/api/nextjs/payroll/lock-active-month/lock', [
            'month' => 'NOVEMBER',
            'year'  => 2026
        ], $headers);

        $lockRes->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('payroll_conpt', [
            'staffID'     => $financeEmployee->ID,
            'month'       => 11,
            'year'        => 2026,
            'salary_lock' => 1,
            'vstage'      => 1
        ]);

        // 6. Recompute with force_unlock when period is locked
        $recomputeLockedRes = $this->postJson('/api/nextjs/payroll/compute', [
            'month'        => 'NOVEMBER',
            'year'         => '2026',
            'force_unlock' => true
        ], $headers);

        $recomputeLockedRes->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // Re-lock the period before forwarding to audit
        $this->postJson('/api/nextjs/payroll/lock-active-month/lock', [
            'month' => 'NOVEMBER',
            'year'  => 2026
        ], $headers);

        // 7. Forward Payroll to Audit Head as Finance Head
        $forwardRes = $this->postJson('/api/nextjs/payroll/lock-active-month/forward-to-audit', [
            'month' => 'NOVEMBER',
            'year'  => 2026
        ], $headers);

        $forwardRes->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Payroll forwarded to audit successfully.');

        $this->assertDatabaseHas('payroll_conpt', [
            'staffID' => $financeEmployee->ID,
            'month'   => 11,
            'year'    => 2026,
            'vstage'  => 2
        ]);
    }
}
