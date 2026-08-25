<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;

class RetentionActivationApiTest extends TestCase
{
    use DatabaseTransactions;

    private $testEmployeeId = null;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create dedicated test employee
        $this->testEmployeeId = DB::table('tblper')->insertGetId([
            'title' => 'MR.',
            'surname' => 'TEST_PHPUNIT_SURNAME',
            'first_name' => 'TEST_FIRST_NAME',
            'othernames' => 'TEST_OTHER_NAMES',
            'rank' => 0,
            'staff_status' => 1,
            'fileNo' => 'TEST9999',
            'courtID' => 9,
            'divisionID' => 1,
            'departmentID' => 79,
            'unitID' => 21,
            'designationID' => 5,
        ]);
        
        $this->cleanUpTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanUpTestData();
        if ($this->testEmployeeId) {
            DB::table('tblper')->where('ID', $this->testEmployeeId)->delete();
        }
        parent::tearDown();
    }

    private function cleanUpTestData()
    {
        if ($this->testEmployeeId) {
            DB::table('staffEarningAndDeduction')->where('staffId', $this->testEmployeeId)->delete();
            DB::table('salary_structures')->where('staffId', $this->testEmployeeId)->delete();
            DB::table('first_salary_structure')->where('staffId', $this->testEmployeeId)->delete();
            DB::table('payroll_conpt')->where('staffID', $this->testEmployeeId)->delete();
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
                        'basic_salary',
                        'num_rente_months',
                        'remaining_months',
                        'total_retention_deducted'
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

        // Toggle on
        $response = $this->postJson('/api/nextjs/payroll/retention-activation/toggle', [
            'staff_id' => $this->testEmployeeId,
            'reten_act' => 1
        ], $headers);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Retention status updated successfully.'
            ]);

        $this->assertDatabaseHas('first_salary_structure', [
            'staffId' => $this->testEmployeeId,
            'reten_act' => 1
        ]);

        // Toggle off as Super Admin -> Succeeds (200)
        $response = $this->postJson('/api/nextjs/payroll/retention-activation/toggle', [
            'staff_id' => $this->testEmployeeId,
            'reten_act' => 0
        ], $headers);

        $response->assertStatus(200);

        $this->assertDatabaseHas('first_salary_structure', [
            'staffId' => $this->testEmployeeId,
            'reten_act' => 0
        ]);

        // Toggle on as Super Admin
        $this->postJson('/api/nextjs/payroll/retention-activation/toggle', [
            'staff_id' => $this->testEmployeeId,
            'reten_act' => 1
        ], $headers);

        // Non-superadmin user trying to deactivate -> Must fail (403)
        $nonSuperUserId = DB::table('users')->insertGetId([
            'username' => 'regular_staff_' . time(),
            'email' => 'regular_' . time() . '@example.com',
            'password' => bcrypt('password'),
        ]);
        DB::table('assign_user_role')->insert([
            'userID' => $nonSuperUserId,
            'roleID' => 9999,
        ]);

        $nonSuperHeaders = ['X-User-Id' => $nonSuperUserId];
        $failResponse = $this->postJson('/api/nextjs/payroll/retention-activation/toggle', [
            'staff_id' => $this->testEmployeeId,
            'reten_act' => 0
        ], $nonSuperHeaders);

        $failResponse->assertStatus(403)
            ->assertJson([
                'status' => 'error'
            ]);
        $this->assertStringContainsString('Super Administrators', $failResponse->json('message'));
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

        // Reset status to 0 first
        DB::table('first_salary_structure')->updateOrInsert(
            ['staffId' => $this->testEmployeeId],
            ['reten_act' => 0]
        );

        // Create temporary CSV content with new column headers
        // First salary 200,000 => monthly retention is 10,000 (5%).
        // Total deducted = 20,000.00 => 2 months deducted. Balance = 180,000.00 => 18 months remaining.
        $csvContent = "staffId,first_salary,total_deducted,balance_to_be_deduct\n{$this->testEmployeeId},200000.00,20000.00,180000.00\n";
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
 
        $this->assertDatabaseHas('first_salary_structure', [
            'staffId' => $this->testEmployeeId,
            'basic_salary' => 40000.00,
            'housing_allowance' => 40000.00,
            'transport_allowance' => 20000.00,
            'medical_allowance' => 20000.00,
            'utility_allowance' => 40000.00,
            'meal_allowance' => 40000.00,
            'reten_act' => 1,
            'num_rente_months' => 2
        ]);
 
        unlink($tempFile);

        // Test 2: Balance '-' or empty means retention is completed (20 months)
        $csvContent2 = "staffId,first_salary,total_deducted,balance_to_be_deducted\n{$this->testEmployeeId},\"250,000.00\",\"250,000.00\",-\n";
        $tempFile2 = tempnam(sys_get_temp_dir(), 'test_csv2');
        file_put_contents($tempFile2, $csvContent2);

        $uploadedFile2 = new UploadedFile(
            $tempFile2,
            'import_retention_completed.csv',
            'text/csv',
            null,
            true
        );

        $response2 = $this->postJson('/api/nextjs/payroll/retention-activation/import', [
            'excel_file' => $uploadedFile2
        ], $headers);

        $response2->assertStatus(200);

        $this->assertDatabaseHas('first_salary_structure', [
            'staffId' => $this->testEmployeeId,
            'reten_act' => 1,
            'num_rente_months' => 20
        ]);

        unlink($tempFile2);
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

        // Configure First Salary Structure (Total: 160,000.00)
        DB::table('first_salary_structure')->updateOrInsert(
            ['staffId' => $this->testEmployeeId],
            [
                'basic_salary' => 100000.00,
                'housing_allowance' => 20000.00,
                'transport_allowance' => 10000.00,
                'medical_allowance' => 10000.00,
                'utility_allowance' => 10000.00,
                'meal_allowance' => 10000.00,
                'reten_act' => 0,
                'num_rente_months' => 0,
                'created_at' => now()
            ]
        );

        // Configure salary structure as well so it's active in payroll_runs
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $this->testEmployeeId],
            [
                'basic_salary' => 100000.00,
                'housing_allowance' => 20000.00,
                'transport_allowance' => 10000.00,
                'medical_allowance' => 10000.00,
                'utility_allowance' => 10000.00,
                'meal_allowance' => 10000.00,
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
            'staffID' => $this->testEmployeeId,
            'retention' => 0.00 // Retention is 0.00 because reten_act is 0
        ]);

        // Scenario B: reten_act = 1
        DB::table('first_salary_structure')->where('staffId', $this->testEmployeeId)->update([
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
            'staffID' => $this->testEmployeeId,
            'retention' => 8000.00 // Retention is applied because reten_act is 1 (5% of 160000.00)
        ]);

        $this->assertDatabaseHas('first_salary_structure', [
            'staffId' => $this->testEmployeeId,
            'num_rente_months' => 1 // Incremented!
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

        // Configure First Salary Structure (Total: 160,000.00) with 19 months already deducted
        DB::table('first_salary_structure')->updateOrInsert(
            ['staffId' => $this->testEmployeeId],
            [
                'basic_salary' => 100000.00,
                'housing_allowance' => 20000.00,
                'transport_allowance' => 10000.00,
                'medical_allowance' => 10000.00,
                'utility_allowance' => 10000.00,
                'meal_allowance' => 10000.00,
                'reten_act' => 1,
                'num_rente_months' => 19,
                'created_at' => now()
            ]
        );

        // Configure salary structure as well so it's active in payroll_runs
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $this->testEmployeeId],
            [
                'basic_salary' => 100000.00,
                'housing_allowance' => 20000.00,
                'transport_allowance' => 10000.00,
                'medical_allowance' => 10000.00,
                'utility_allowance' => 10000.00,
                'meal_allowance' => 10000.00,
                'created_at' => now()
            ]
        );

        // Compute payroll for JUNE 2026.
        // Since num_rente_months is 19 (< 20), it should STILL apply retention and increment.
        $response = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'JUNE',
            'year' => '2026'
        ], $headers);

        $response->assertStatus(200);
        $newRunId = $response->json('payroll_run_id');

        $this->assertDatabaseHas('payroll_conpt', [
            'payroll_run_id' => $newRunId,
            'staffID' => $this->testEmployeeId,
            'retention' => 8000.00
        ]);

        $this->assertDatabaseHas('first_salary_structure', [
            'staffId' => $this->testEmployeeId,
            'num_rente_months' => 20
        ]);

        // Compute payroll for JULY 2026 (or just re-compute/compute next month).
        // Since num_rente_months is 20, retention should be 0.00.
        $response = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'JULY',
            'year' => '2026'
        ], $headers);

        $response->assertStatus(200);
        $finalRunId = $response->json('payroll_run_id');

        $this->assertDatabaseHas('payroll_conpt', [
            'payroll_run_id' => $finalRunId,
            'staffID' => $this->testEmployeeId,
            'retention' => 0.00 // Capped!
        ]);
    }

    /**
     * Test that retention deduction amount remains locked to the first computed salary structure percentage
     * even when the salary structure is increased later.
     */
    public function test_retention_remains_locked_after_salary_increase()
    {
        $superAdminRole = DB::table('assign_user_role')->where('roleID', 1)->first();
        if (!$superAdminRole) {
            $this->markTestSkipped('No superadmin.');
            return;
        }

        $headers = ['X-User-Id' => $superAdminRole->userID];

        // Delete any existing conpt records for this employee to start fresh
        DB::table('payroll_conpt')->where('staffID', $this->testEmployeeId)->delete();

        // 1. Configure first salary structure (Total: 160,000.00)
        DB::table('first_salary_structure')->updateOrInsert(
            ['staffId' => $this->testEmployeeId],
            [
                'basic_salary' => 100000.00,
                'housing_allowance' => 20000.00,
                'transport_allowance' => 10000.00,
                'medical_allowance' => 10000.00,
                'utility_allowance' => 10000.00,
                'meal_allowance' => 10000.00,
                'reten_act' => 1,
                'num_rente_months' => 0,
                'created_at' => now()
            ]
        );

        // 2. Configure salary structure (Total: 160,000.00 initially)
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $this->testEmployeeId],
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
                'created_at' => now()
            ]
        );

        // 2. Compute payroll for MAY 2026
        $response = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'MAY',
            'year' => '2026'
        ], $headers);

        $response->assertStatus(200);
        $runIdMay = $response->json('payroll_run_id');

        // Verify the first retention is 5% of 160,000.00 = 8,000.00
        $this->assertDatabaseHas('payroll_conpt', [
            'payroll_run_id' => $runIdMay,
            'staffID' => $this->testEmployeeId,
            'retention' => 8000.00
        ]);

        // 3. Increase salary structure (Total: 300,000.00)
        DB::table('salary_structures')->where('staffId', $this->testEmployeeId)->update([
            'basic_salary' => 200000.00,
            'housing_allowance' => 40000.00,
            'transport_allowance' => 20000.00,
            'medical_allowance' => 15000.00,
            'utility_allowance' => 15000.00,
            'meal_allowance' => 10000.00,
        ]);

        // 4. Compute payroll for JUNE 2026
        $response = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'JUNE',
            'year' => '2026'
        ], $headers);

        $response->assertStatus(200);
        $runIdJune = $response->json('payroll_run_id');

        // Verify retention remains locked to the first computed amount of 8,000.00
        // instead of 5% of 300,000.00 (which would be 15,000.00)
        $this->assertDatabaseHas('payroll_conpt', [
            'payroll_run_id' => $runIdJune,
            'staffID' => $this->testEmployeeId,
            'retention' => 8000.00
        ]);
    }
}
