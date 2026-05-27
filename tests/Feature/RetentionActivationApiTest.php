<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;

class RetentionActivationApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanUpTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanUpTestData();
        parent::tearDown();
    }

    private function cleanUpTestData()
    {
        $employee = DB::table('tblper')->where('rank', '!=', 2)->where('staff_status', 1)->first();
        if ($employee) {
            DB::table('staffEarningAndDeduction')->where('staffId', $employee->ID)->delete();
            DB::table('salary_structures')->where('staffId', $employee->ID)->delete();
            DB::table('payroll_conpt')->where('staffID', $employee->ID)->delete();
        }
        
        DB::table('payroll_runs')->where('month', 5)->where('year', 2026)->delete();
        DB::table('payroll_runs')->where('month', 6)->where('year', 2026)->delete();
        DB::table('payroll_runs')->where('year', 2024)->delete();
        DB::table('payroll_runs')->where('year', 2025)->delete();
    }

    /**
     * Test GET /api/nextjs/payroll/retention-activation validation and listing.
     */
    public function test_retention_activation_listing()
    {
        // 1. Unauthorized if no X-User-Id
        $response = $this->getJson('/api/nextjs/payroll/retention-activation');
        $response->assertStatus(401);

        // Find a superadmin user
        $superAdminRole = DB::table('assign_user_role')->where('roleID', 1)->first();
        if (!$superAdminRole) {
            $this->markTestSkipped('No superadmin user found in database to run tests.');
            return;
        }

        $headers = ['X-User-Id' => $superAdminRole->userID];

        // 2. Success listing
        $response = $this->getJson('/api/nextjs/payroll/retention-activation', $headers);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'fileNo',
                        'surname',
                        'first_name',
                        'othernames',
                        'name',
                        'reten_act',
                        'basic_salary'
                    ]
                ]
            ]);
    }

    /**
     * Test POST /api/nextjs/payroll/retention-activation/toggle.
     */
    public function test_retention_activation_toggle()
    {
        $superAdminRole = DB::table('assign_user_role')->where('roleID', 1)->first();
        if (!$superAdminRole) {
            $this->markTestSkipped('No superadmin user found in database.');
            return;
        }

        $headers = ['X-User-Id' => $superAdminRole->userID];

        $employee = DB::table('tblper')->where('rank', '!=', 2)->where('staff_status', 1)->first();
        $this->assertNotNull($employee, 'Require active employee');

        // Toggle on
        $response = $this->postJson('/api/nextjs/payroll/retention-activation/toggle', [
            'staff_id' => $employee->ID,
            'reten_act' => 1
        ], $headers);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Retention status updated successfully.'
            ]);

        $this->assertDatabaseHas('salary_structures', [
            'staffId' => $employee->ID,
            'reten_act' => 1
        ]);

        // Toggle off
        $response = $this->postJson('/api/nextjs/payroll/retention-activation/toggle', [
            'staff_id' => $employee->ID,
            'reten_act' => 0
        ], $headers);

        $response->assertStatus(200);

        $this->assertDatabaseHas('salary_structures', [
            'staffId' => $employee->ID,
            'reten_act' => 0
        ]);
    }

    /**
     * Test bulk import Excel/CSV for retention activation.
     */
    public function test_retention_activation_import()
    {
        $superAdminRole = DB::table('assign_user_role')->where('roleID', 1)->first();
        if (!$superAdminRole) {
            $this->markTestSkipped('No superadmin user.');
            return;
        }

        $headers = ['X-User-Id' => $superAdminRole->userID];

        $employee1 = DB::table('tblper')->where('rank', '!=', 2)->where('staff_status', 1)->first();
        $this->assertNotNull($employee1);

        // Reset status to 0 first
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $employee1->ID],
            ['reten_act' => 0]
        );

        // Create temporary CSV content
        $csvContent = "staffId\n{$employee1->ID}\n";
        $tempFile = tempnam(sys_get_temp_dir(), 'test_csv');
        file_put_contents($tempFile, $csvContent);

        $uploadedFile = new UploadedFile(
            $tempFile,
            'import_retention.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->postJson('/api/nextjs/payroll/retention-activation/import', [
            'excel_file' => $uploadedFile
        ], $headers);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'activated_count' => 1
            ]);

        $this->assertDatabaseHas('salary_structures', [
            'staffId' => $employee1->ID,
            'reten_act' => 1
        ]);

        unlink($tempFile);
    }

    /**
     * Test integration with compute salary:
     * - skips retention deduction when reten_act is 0
     * - applies retention deduction when reten_act is 1
     */
    public function test_salary_compute_retention_integration()
    {
        $superAdminRole = DB::table('assign_user_role')->where('roleID', 1)->first();
        if (!$superAdminRole) {
            $this->markTestSkipped('No superadmin.');
            return;
        }

        $headers = ['X-User-Id' => $superAdminRole->userID];

        $employee = DB::table('tblper')->where('rank', '!=', 2)->where('staff_status', 1)->first();
        $this->assertNotNull($employee);

        // Clean up staff earning/deductions
        DB::table('staffEarningAndDeduction')->where('staffId', $employee->ID)->delete();

        // Configure Retention Deduction
        DB::table('staffEarningAndDeduction')->insert([
            'staffId' => $employee->ID,
            'variable_type' => 'deduction',
            'cv_setup_id' => 2, // Retention
            'description' => 'Staff Retention Fee',
            'amount' => 1250.00,
            'no_limit' => 1,
            'one_time' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Scenario A: reten_act = 0
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $employee->ID],
            [
                'basic_salary' => 100000.00,
                'housing_allowance' => 20000.00,
                'transport_allowance' => 10000.00,
                'medical_allowance' => 10000.00,
                'utility_allowance' => 10000.00,
                'meal_allowance' => 10000.00,
                'pension_rate' => 0.00,
                'tax_rate' => 0.00,
                'pen_act' => 0,
                'reten_act' => 0,
                'created_at' => now()
            ]
        );

        $response = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'MAY',
            'year' => '2026'
        ], $headers);

        $response->assertStatus(200);
        $runId = $response->json('payroll_run_id');

        $this->assertDatabaseHas('payroll_conpt', [
            'payroll_run_id' => $runId,
            'staffID' => $employee->ID,
            'retention' => 0.00 // Retention is 0.00 because reten_act is 0
        ]);

        // Scenario B: reten_act = 1
        DB::table('salary_structures')->where('staffId', $employee->ID)->update([
            'reten_act' => 1
        ]);

        $response = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'MAY',
            'year' => '2026'
        ], $headers);

        $response->assertStatus(200);
        $newRunId = $response->json('payroll_run_id');

        $this->assertDatabaseHas('payroll_conpt', [
            'payroll_run_id' => $newRunId,
            'staffID' => $employee->ID,
            'retention' => 8000.00 // Retention is applied because reten_act is 1 (5% of 160000.00)
        ]);
    }

    /**
     * Test that retention deduction is capped at 20 months.
     */
    public function test_retention_capping_limit()
    {
        $superAdminRole = DB::table('assign_user_role')->where('roleID', 1)->first();
        if (!$superAdminRole) {
            $this->markTestSkipped('No superadmin.');
            return;
        }

        $headers = ['X-User-Id' => $superAdminRole->userID];

        $employee = DB::table('tblper')->where('rank', '!=', 2)->where('staff_status', 1)->first();
        $this->assertNotNull($employee);

        // Reset/configure salary structure
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $employee->ID],
            [
                'basic_salary' => 100000.00,
                'housing_allowance' => 20000.00,
                'transport_allowance' => 10000.00,
                'medical_allowance' => 10000.00,
                'utility_allowance' => 10000.00,
                'meal_allowance' => 10000.00,
                'pension_rate' => 0.00,
                'tax_rate' => 0.00,
                'pen_act' => 0,
                'reten_act' => 1,
                'created_at' => now()
            ]
        );

        // Delete any existing conpt records for this employee to start fresh
        DB::table('payroll_conpt')->where('staffID', $employee->ID)->delete();

        // Let's create 19 historical runs with retention deduction > 0.
        // We can create them starting from year 2024.
        $m = 1;
        $y = 2024;
        for ($i = 0; $i < 19; $i++) {
            $runId = DB::table('payroll_runs')->insertGetId([
                'month' => $m,
                'year' => $y,
                'status' => 'processed',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('payroll_conpt')->insert([
                'payroll_run_id' => $runId,
                'staffID' => $employee->ID,
                'month' => $m,
                'year' => $y,
                'retention' => 8000.00,
                'gross_pay' => 160000.00,
                'total_deductions' => 8000.00,
                'net_pay' => 152000.00,
                'created_at' => now()
            ]);

            $m++;
            if ($m > 12) {
                $m = 1;
                $y++;
            }
        }

        // Now compute salary for current month: e.g. June 2026.
        // Since previous deductions count is 19 (< 20), it should STILL apply retention.
        $response = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'JUNE',
            'year' => '2026'
        ], $headers);

        $response->assertStatus(200);
        $newRunId = $response->json('payroll_run_id');

        $this->assertDatabaseHas('payroll_conpt', [
            'payroll_run_id' => $newRunId,
            'staffID' => $employee->ID,
            'retention' => 8000.00
        ]);

        // Now delete the run we just created and add one more historical run to reach exactly 20.
        DB::table('payroll_conpt')->where('payroll_run_id', $newRunId)->delete();
        DB::table('payroll_runs')->where('id', $newRunId)->delete();

        $runId = DB::table('payroll_runs')->insertGetId([
            'month' => $m,
            'year' => $y,
            'status' => 'processed',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('payroll_conpt')->insert([
            'payroll_run_id' => $runId,
            'staffID' => $employee->ID,
            'month' => $m,
            'year' => $y,
            'retention' => 8000.00,
            'gross_pay' => 160000.00,
            'total_deductions' => 8000.00,
            'net_pay' => 152000.00,
            'created_at' => now()
        ]);

        // Now there are exactly 20 historical runs with retention > 0.
        // When we compute for June 2026, retention should be 0.00.
        $response = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'JUNE',
            'year' => '2026'
        ], $headers);

        $response->assertStatus(200);
        $finalRunId = $response->json('payroll_run_id');

        $this->assertDatabaseHas('payroll_conpt', [
            'payroll_run_id' => $finalRunId,
            'staffID' => $employee->ID,
            'retention' => 0.00 // Capped!
        ]);
    }
}
