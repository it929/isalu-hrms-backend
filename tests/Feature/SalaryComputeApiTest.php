<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SalaryComputeApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test POST /api/nextjs/payroll/compute validation.
     */
    public function test_compute_salary_validation()
    {
        // 1. Unauthorized if no X-User-Id
        $response = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'MAY',
            'year' => '2026'
        ]);
        $response->assertStatus(403);

        // Find a superadmin user (assign_user_role roleID = 1)
        $superAdminRole = DB::table('assign_user_role')->where('roleID', 1)->first();
        if (!$superAdminRole) {
            $this->markTestSkipped('No superadmin user found in database to run full validation tests.');
            return;
        }

        $headers = ['X-User-Id' => $superAdminRole->userID];

        // 2. Missing fields
        $response = $this->postJson('/api/nextjs/payroll/compute', [], $headers);
        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Month and Year are required.'
            ]);

        // 3. Invalid month
        $response = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'INVALID_MONTH',
            'year' => '2026'
        ], $headers);
        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Invalid month specified.'
            ]);
    }

    /**
     * Test POST /api/nextjs/payroll/compute success and DB insertion.
     */
    public function test_compute_salary_success()
    {
        $superAdminRole = DB::table('assign_user_role')->where('roleID', 1)->first();
        if (!$superAdminRole) {
            $this->markTestSkipped('No superadmin user found in database to test execution.');
            return;
        }

        $headers = ['X-User-Id' => $superAdminRole->userID];

        // Check if there is at least one active employee (rank != 2, staff_status = 1)
        $employee = DB::table('tblper')->where('rank', '!=', 2)->where('staff_status', 1)->first();
        if (!$employee) {
            $this->markTestSkipped('No active employee records to run salary computation.');
            return;
        }

        // Run computation
        $response = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'MAY',
            'year' => '2026'
        ], $headers);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Salary payroll run computed and saved successfully for all active staff.'
            ]);

        $payrollRunId = $response->json('payroll_run_id');
        $this->assertNotNull($payrollRunId);

        // Verify that the run exists in the database
        $this->assertDatabaseHas('payroll_runs', [
            'id' => $payrollRunId,
            'month' => 5,
            'year' => 2026,
            'status' => 'processed',
        ]);

        // Verify that payroll details were inserted for the active employee
        $this->assertDatabaseHas('payroll_conpt', [
            'payroll_run_id' => $payrollRunId,
            'staffID' => $employee->ID,
        ]);
    }
}
