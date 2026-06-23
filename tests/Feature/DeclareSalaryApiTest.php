<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;

class DeclareSalaryApiTest extends TestCase
{
    use DatabaseTransactions;

    private $testEmployeeId = null;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create dedicated test employee
        $this->testEmployeeId = DB::table('tblper')->insertGetId([
            'title' => 'MR.',
            'surname' => 'TEST_PHPUNIT_DECLARE_SURNAME',
            'first_name' => 'TEST_FIRST_NAME',
            'othernames' => 'TEST_OTHER_NAMES',
            'rank' => 0,
            'staff_status' => 1,
            'fileNo' => 'TEST_DECLARE_9999',
            'courtID' => 9,
            'divisionID' => 1,
            'departmentID' => 79,
            'unitID' => 21,
            'designationID' => 5,
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->testEmployeeId) {
            DB::table('salary_structures')->where('staffId', $this->testEmployeeId)->delete();
            DB::table('tblper')->where('ID', $this->testEmployeeId)->delete();
        }
        parent::tearDown();
    }

    /**
     * Test GET /api/nextjs/payroll/declare-salary lists salary structures.
     */
    public function test_get_declare_salary_list()
    {
        // Insert a salary structure for test employee
        DB::table('salary_structures')->insert([
            'staffId' => $this->testEmployeeId,
            'basic_salary' => 50000.00,
            'declare_salary' => 100000.00,
            'created_at' => now(),
        ]);

        $superAdminRole = DB::table('assign_user_role')->where('roleID', 1)->first();
        if (!$superAdminRole) {
            $this->markTestSkipped('No superadmin.');
            return;
        }

        $headers = ['X-User-Id' => $superAdminRole->userID];

        $response = $this->getJson('/api/nextjs/payroll/declare-salary', $headers);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'staffId' => $this->testEmployeeId,
            'declare_salary' => '100000.00',
        ]);
    }

    /**
     * Test POST /api/nextjs/payroll/declare-salary updates declare_salary if structure exists.
     */
    public function test_store_declare_salary_success()
    {
        // Insert a salary structure for test employee
        DB::table('salary_structures')->insert([
            'staffId' => $this->testEmployeeId,
            'basic_salary' => 50000.00,
            'declare_salary' => 0.00,
            'created_at' => now(),
        ]);

        $superAdminRole = DB::table('assign_user_role')->where('roleID', 1)->first();
        if (!$superAdminRole) {
            $this->markTestSkipped('No superadmin.');
            return;
        }

        $headers = ['X-User-Id' => $superAdminRole->userID];

        $response = $this->postJson('/api/nextjs/payroll/declare-salary', [
            'staffId' => $this->testEmployeeId,
            'declare_salary' => 150000.00,
        ], $headers);

        $response->assertStatus(200);

        // Verify database is updated
        $this->assertDatabaseHas('salary_structures', [
            'staffId' => $this->testEmployeeId,
            'declare_salary' => 150000.00,
        ]);
    }

    /**
     * Test POST /api/nextjs/payroll/declare-salary returns error if no structure exists.
     */
    public function test_store_declare_salary_fails_if_no_structure()
    {
        $superAdminRole = DB::table('assign_user_role')->where('roleID', 1)->first();
        if (!$superAdminRole) {
            $this->markTestSkipped('No superadmin.');
            return;
        }

        $headers = ['X-User-Id' => $superAdminRole->userID];

        // Do not insert salary structure. Call store directly
        $response = $this->postJson('/api/nextjs/payroll/declare-salary', [
            'staffId' => $this->testEmployeeId,
            'declare_salary' => 150000.00,
        ], $headers);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => 'error',
            'message' => 'The selected staff member has no salary structure setup.'
        ]);
    }

    /**
     * Test POST /api/nextjs/payroll/declare-salary/import updates declare_salary for valid rows, gives warning for invalid rows.
     */
    public function test_import_declare_salary()
    {
        // Insert a salary structure for test employee
        DB::table('salary_structures')->insert([
            'staffId' => $this->testEmployeeId,
            'basic_salary' => 50000.00,
            'declare_salary' => 0.00,
            'created_at' => now(),
        ]);

        $superAdminRole = DB::table('assign_user_role')->where('roleID', 1)->first();
        if (!$superAdminRole) {
            $this->markTestSkipped('No superadmin.');
            return;
        }

        $headers = ['X-User-Id' => $superAdminRole->userID];

        // Create temporary CSV file
        // Row 2: valid employee with structure
        // Row 3: non-existent employee ID
        $csvContent = "staffId,declare_salary\n";
        $csvContent .= "{$this->testEmployeeId},200000.00\n";
        $csvContent .= "9999999,300000.00\n";
        
        $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('test_csv_', true) . '.csv';
        file_put_contents($tempFile, $csvContent);

        $uploadedFile = new UploadedFile(
            $tempFile,
            'declare_salaries.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->postJson('/api/nextjs/payroll/declare-salary/import', [
            'file' => $uploadedFile
        ], $headers);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'status' => 'success',
            'updated_count' => 1
        ]);

        // Check warning exists for non-existent employee
        $this->assertNotEmpty($response['warnings']);
        $this->assertStringContainsString("9999999", $response['warnings'][0]);

        // Verify database is updated for test employee
        $this->assertDatabaseHas('salary_structures', [
            'staffId' => $this->testEmployeeId,
            'declare_salary' => 200000.00,
        ]);

        unlink($tempFile);
    }
}
