<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;

class SalaryStructureApiTest extends TestCase
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
            'staff_status' => 0,
            'fileNo' => 'TEST9999',
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
     * Test POST /api/nextjs/payroll/salary-structures updates staff_status to 1.
     */
    public function test_salary_structure_store_updates_staff_status()
    {
        $superAdminRole = DB::table('assign_user_role')->where('roleID', 1)->first();
        if (!$superAdminRole) {
            $this->markTestSkipped('No superadmin.');
            return;
        }

        $headers = ['X-User-Id' => $superAdminRole->userID];

        $response = $this->postJson('/api/nextjs/payroll/salary-structures', [
            'staffId' => $this->testEmployeeId,
            'basic_salary' => 120000.00,
            'housing_allowance' => 30000.00,
            'transport_allowance' => 15000.00,
            'medical_allowance' => 10000.00,
            'utility_allowance' => 10000.00,
            'meal_allowance' => 10000.00,
            'pension_rate' => 8.00,
            'tax_rate' => 5.00
        ], $headers);

        $response->assertStatus(200);

        // Verify salary structure exists
        $this->assertDatabaseHas('salary_structures', [
            'staffId' => $this->testEmployeeId,
            'basic_salary' => 120000.00
        ]);

        // Verify staff_status is updated to 1
        $this->assertDatabaseHas('tblper', [
            'ID' => $this->testEmployeeId,
            'staff_status' => 1
        ]);
    }

    /**
     * Test POST /api/nextjs/payroll/salary-structures/upload updates staff_status to 1.
     */
    public function test_salary_structure_upload_updates_staff_status()
    {
        $superAdminRole = DB::table('assign_user_role')->where('roleID', 1)->first();
        if (!$superAdminRole) {
            $this->markTestSkipped('No superadmin.');
            return;
        }

        $headers = ['X-User-Id' => $superAdminRole->userID];

        // Create temporary CSV file
        $csvContent = "staffId,basic_salary,housing_allowance,transport_allowance,medical_allowance,utility_allowance,meal_allowance,pension_rate,tax_rate\n";
        $csvContent .= "{$this->testEmployeeId},140000.00,35000.00,20000.00,12000.00,12000.00,12000.00,8.00,5.00\n";
        
        $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('test_csv_', true) . '.csv';
        file_put_contents($tempFile, $csvContent);

        $uploadedFile = new UploadedFile(
            $tempFile,
            'salary_structures.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->postJson('/api/nextjs/payroll/salary-structures/upload', [
            'file' => $uploadedFile
        ], $headers);

        $response->assertStatus(200);

        // Verify salary structure exists
        $this->assertDatabaseHas('salary_structures', [
            'staffId' => $this->testEmployeeId,
            'basic_salary' => 140000.00
        ]);

        // Verify staff_status is updated to 1
        $this->assertDatabaseHas('tblper', [
            'ID' => $this->testEmployeeId,
            'staff_status' => 1
        ]);

        unlink($tempFile);
    }
}
