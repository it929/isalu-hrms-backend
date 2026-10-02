<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SalaryIncrementApiTest extends TestCase
{
    use DatabaseTransactions;

    private $testEmployeeId = null;
    private $testEmployeeId2 = null;
    private $testDepartmentId = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Create isolated test department
        $this->testDepartmentId = DB::table('tbldepartment')->insertGetId([
            'courtID' => '9',
            'department' => 'TEST_INC_DEPT',
            'head' => 0,
        ]);

        // Create test employees
        $this->testEmployeeId = DB::table('tblper')->insertGetId([
            'title' => 'MR.',
            'surname' => 'INCREMENT_TEST_1',
            'first_name' => 'JOHN',
            'othernames' => 'DOE',
            'rank' => 0,
            'staff_status' => 1,
            'fileNo' => 'INC9901',
            'courtID' => 9,
            'divisionID' => 1,
            'departmentID' => $this->testDepartmentId,
            'unitID' => 21,
            'designation' => 'Doctor',
        ]);

        $this->testEmployeeId2 = DB::table('tblper')->insertGetId([
            'title' => 'MRS.',
            'surname' => 'INCREMENT_TEST_2',
            'first_name' => 'JANE',
            'othernames' => 'DOE',
            'rank' => 0,
            'staff_status' => 1,
            'fileNo' => 'INC9902',
            'courtID' => 9,
            'divisionID' => 1,
            'departmentID' => $this->testDepartmentId,
            'unitID' => 21,
            'designation' => 'Nurse',
        ]);

        // Seed initial salary structures (₦1,000,000 gross)
        DB::table('salary_structures')->insert([
            'staffId' => $this->testEmployeeId,
            'basic_salary' => 200000.00,
            'housing_allowance' => 200000.00,
            'transport_allowance' => 100000.00,
            'medical_allowance' => 100000.00,
            'utility_allowance' => 200000.00,
            'meal_allowance' => 200000.00,
            'pension_rate' => 8.00,
            'created_at' => now(),
        ]);

        DB::table('salary_structures')->insert([
            'staffId' => $this->testEmployeeId2,
            'basic_salary' => 200000.00,
            'housing_allowance' => 200000.00,
            'transport_allowance' => 100000.00,
            'medical_allowance' => 100000.00,
            'utility_allowance' => 200000.00,
            'meal_allowance' => 200000.00,
            'pension_rate' => 8.00,
            'created_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->testEmployeeId) {
            DB::table('salary_increments')->where('staff_id', $this->testEmployeeId)->delete();
            DB::table('salary_structures')->where('staffId', $this->testEmployeeId)->delete();
            DB::table('tblper')->where('ID', $this->testEmployeeId)->delete();
        }
        if ($this->testEmployeeId2) {
            DB::table('salary_increments')->where('staff_id', $this->testEmployeeId2)->delete();
            DB::table('salary_structures')->where('staffId', $this->testEmployeeId2)->delete();
            DB::table('tblper')->where('ID', $this->testEmployeeId2)->delete();
        }
        if ($this->testDepartmentId) {
            DB::table('tbldepartment')->where('id', $this->testDepartmentId)->delete();
        }
        parent::tearDown();
    }

    public function test_get_staff_list_for_increment()
    {
        $response = $this->getJson('/api/nextjs/payroll/salary-increments/staff');
        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');

        $staffList = collect($response->json('data.staff'));
        $matched = $staffList->firstWhere('id', $this->testEmployeeId);

        $this->assertNotNull($matched);
        $this->assertEquals(1000000.00, (float)$matched['current_gross']);
        $this->assertEquals(200000.00, (float)$matched['current_basic']);
    }

    public function test_apply_single_percentage_increment()
    {
        // Apply +15% increment on ₦1,000,000 gross -> new gross should be ₦1,150,000
        $response = $this->postJson('/api/nextjs/payroll/salary-increments/single', [
            'staff_id' => $this->testEmployeeId,
            'increment_type' => 'percentage',
            'percentage' => 15,
            'effective_date' => '2026-08-01',
            'reason' => 'Annual performance increment',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.new_gross', 1150000);
        $response->assertJsonPath('data.increase_amount', 150000);

        // Verify database structure
        $updatedStruct = DB::table('salary_structures')->where('staffId', $this->testEmployeeId)->first();
        $this->assertNotNull($updatedStruct);
        $this->assertEquals(230000.00, (float)$updatedStruct->basic_salary); // 20% of 1,150,000
        $this->assertEquals(230000.00, (float)$updatedStruct->housing_allowance);
        $this->assertEquals(115000.00, (float)$updatedStruct->transport_allowance); // 10% of 1,150,000

        // Verify audit log
        $incRecord = DB::table('salary_increments')->where('staff_id', $this->testEmployeeId)->first();
        $this->assertNotNull($incRecord);
        $this->assertEquals('percentage', $incRecord->increment_type);
        $this->assertEquals(15.00, (float)$incRecord->percentage);
        $this->assertEquals(1000000.00, (float)$incRecord->previous_gross_salary);
        $this->assertEquals(1150000.00, (float)$incRecord->new_gross_salary);
        $this->assertEquals('applied', $incRecord->status);
    }

    public function test_apply_single_fixed_amount_increment()
    {
        // Apply +₦100,000 fixed increase on ₦1,000,000 gross -> new gross = ₦1,100,000
        $response = $this->postJson('/api/nextjs/payroll/salary-increments/single', [
            'staff_id' => $this->testEmployeeId,
            'increment_type' => 'fixed_amount',
            'amount' => 100000,
            'effective_date' => '2026-08-01',
            'reason' => 'Cost of living adjustment',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.new_gross', 1100000);
        $response->assertJsonPath('data.increase_amount', 100000);
    }

    public function test_apply_single_new_gross_increment()
    {
        // Directly set gross salary to ₦1,600,000
        $response = $this->postJson('/api/nextjs/payroll/salary-increments/single', [
            'staff_id' => $this->testEmployeeId,
            'increment_type' => 'new_gross',
            'new_gross' => 1600000,
            'effective_date' => '2026-08-01',
            'reason' => 'Promotion adjustment',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.new_gross', 1600000);
        $response->assertJsonPath('data.breakdown.basic_salary', 320000); // 20% of 1,600,000
        $response->assertJsonPath('data.breakdown.transport_allowance', 160000); // 10% of 1,600,000
    }

    public function test_apply_bulk_increment_and_history()
    {
        $response = $this->postJson('/api/nextjs/payroll/salary-increments/bulk', [
            'target_type' => 'department',
            'department_id' => $this->testDepartmentId,
            'increment_type' => 'percentage',
            'percentage' => 10,
            'effective_date' => '2026-08-01',
            'reason' => 'General department wage increment',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $this->assertGreaterThanOrEqual(2, $response->json('data.affected_count'));

        // Check history endpoint
        $historyRes = $this->getJson("/api/nextjs/payroll/salary-increments/history?department_id={$this->testDepartmentId}");
        $historyRes->assertStatus(200);
        $historyRes->assertJsonPath('status', 'success');
        $this->assertNotEmpty($historyRes->json('data'));
    }

    public function test_revert_salary_increment()
    {
        // 1. Apply increment
        $applyRes = $this->postJson('/api/nextjs/payroll/salary-increments/single', [
            'staff_id' => $this->testEmployeeId,
            'increment_type' => 'new_gross',
            'new_gross' => 1500000,
            'effective_date' => '2026-08-01',
            'reason' => 'Temporary increase',
        ]);
        $incId = $applyRes->json('data.increment_id');

        // 2. Revert increment
        $revertRes = $this->postJson('/api/nextjs/payroll/salary-increments/revert', [
            'increment_id' => $incId
        ]);

        $revertRes->assertStatus(200);
        $revertRes->assertJsonPath('status', 'success');

        // Verify salary reverted back to ₦1,000,000
        $revertedStruct = DB::table('salary_structures')->where('staffId', $this->testEmployeeId)->first();
        $this->assertEquals(200000.00, (float)$revertedStruct->basic_salary);

        $incRecord = DB::table('salary_increments')->where('id', $incId)->first();
        $this->assertEquals('reverted', $incRecord->status);
    }

    public function test_export_increment_history()
    {
        // 1. XLSX
        $responseXlsx = $this->get('/api/nextjs/payroll/salary-increments/export?format=xlsx');
        $responseXlsx->assertStatus(200);
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $responseXlsx->headers->get('content-type'));

        // 2. CSV
        $responseCsv = $this->get('/api/nextjs/payroll/salary-increments/export?format=csv');
        $responseCsv->assertStatus(200);
        $this->assertStringContainsString('text/csv', $responseCsv->headers->get('content-type'));
    }

    public function test_download_template_xlsx_and_csv()
    {
        // 1. XLSX
        $resXlsx = $this->get('/api/nextjs/payroll/salary-increments/template?format=xlsx');
        $resXlsx->assertStatus(200);
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $resXlsx->headers->get('content-type'));

        // 2. CSV
        $resCsv = $this->get('/api/nextjs/payroll/salary-increments/template?format=csv');
        $resCsv->assertStatus(200);
        $this->assertEquals('text/csv; charset=UTF-8', $resCsv->headers->get('content-type'));
        $content = $resCsv->streamedContent();
        $firstLine = strtok($content, "\r\n");
        $this->assertStringContainsString('Staff ID', $firstLine);
        $this->assertStringContainsString('Department', $firstLine);
        $this->assertStringContainsString('Increment Amount', $firstLine);
        $this->assertStringContainsString('Increment Percentage', $firstLine);
        $this->assertStringContainsString('Effective Date', $firstLine);
        $this->assertStringNotContainsString("fileNo", $content);
        $this->assertStringNotContainsString("Current Gross", $content);
        $this->assertStringNotContainsString("New Gross", $content);
    }

    public function test_apply_multi_department_increments()
    {
        $payload = [
            'effective_date' => '2026-10-01',
            'default_reason' => 'Annual Departmental Adjustment',
            'departments' => [
                [
                    'department_id' => $this->testDepartmentId,
                    'increment_type' => 'fixed_amount',
                    'amount' => 50000,
                    'percentage' => null,
                    'reason' => 'Medical dept ₦50,000 allowance bump'
                ]
            ]
        ];

        $response = $this->postJson('/api/nextjs/payroll/salary-increments/multi-department', $payload);
        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $this->assertGreaterThanOrEqual(2, $response->json('data.affected_count'));

        // Verify that staff in dept 79 got their new gross (1,000,000 + 50,000 = 1,050,000)
        $struct = DB::table('salary_structures')->where('staffId', $this->testEmployeeId)->first();
        $this->assertEquals(210000.00, (float)$struct->basic_salary); // 20% of 1,050,000
    }

    public function test_preview_and_upload_bulk_spreadsheet()
    {
        // 5-column streamlined bulk template (Staff ID, Department, Increment Amount, Increment Percentage, Effective Date)
        $csvContent = "Staff ID,Department,Increment Amount,Increment Percentage,Effective Date\n";
        $csvContent .= "{$this->testEmployeeId},Medical,25000,,2026-10-01\n";
        $csvContent .= "{$this->testEmployeeId2},Medical,,10,2026-10-01\n";

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('salary_increments.csv', $csvContent);

        // 1. Preview
        $previewRes = $this->postJson('/api/nextjs/payroll/salary-increments/preview-upload', [
            'file' => $file
        ]);
        $previewRes->assertStatus(200);
        $previewRes->assertJsonPath('status', 'success');
        $this->assertEquals(2, $previewRes->json('data.valid_count'));

        // 2. Upload
        $uploadFile = \Illuminate\Http\UploadedFile::fake()->createWithContent('salary_increments.csv', $csvContent);
        $uploadRes = $this->postJson('/api/nextjs/payroll/salary-increments/upload', [
            'file' => $uploadFile
        ]);
        $uploadRes->assertStatus(200);
        $uploadRes->assertJsonPath('status', 'success');
        $this->assertEquals(2, $uploadRes->json('data.updated_count'));

        // Verify records
        $struct1 = DB::table('salary_structures')->where('staffId', $this->testEmployeeId)->first();
        // 1,000,000 + 25,000 = 1,025,000 -> basic = 205,000
        $this->assertEquals(205000.00, (float)$struct1->basic_salary);

        $struct2 = DB::table('salary_structures')->where('staffId', $this->testEmployeeId2)->first();
        // 1,000,000 * 1.10 = 1,100,000 -> basic = 220,000
        $this->assertEquals(220000.00, (float)$struct2->basic_salary);
    }

    public function test_apply_single_decrement_fixed_and_percentage()
    {
        // 1. Decrement fixed by ₦100,000 (1,000,000 -> 900,000)
        $fixedDecRes = $this->postJson('/api/nextjs/payroll/salary-increments/single', [
            'staff_id' => $this->testEmployeeId,
            'increment_type' => 'decrement_fixed',
            'amount' => 100000,
            'effective_date' => '2026-10-01',
            'reason' => 'Voluntary salary reduction',
        ]);

        $fixedDecRes->assertStatus(200);
        $fixedDecRes->assertJsonPath('status', 'success');
        $fixedDecRes->assertJsonPath('data.new_gross', 900000);
        $fixedDecRes->assertJsonPath('data.increase_amount', -100000);

        // Verify salary structure
        $struct = DB::table('salary_structures')->where('staffId', $this->testEmployeeId)->first();
        $this->assertEquals(180000.00, (float)$struct->basic_salary); // 20% of 900,000

        // Verify audit log has negative increase_amount
        $log = DB::table('salary_increments')->where('staff_id', $this->testEmployeeId)->latest('id')->first();
        $this->assertEquals(-100000.00, (float)$log->increase_amount);
        $this->assertEquals(900000.00, (float)$log->new_gross_salary);

        // 2. Decrement by percentage (10% cut on testEmployeeId2: 1,000,000 -> 900,000)
        $pctDecRes = $this->postJson('/api/nextjs/payroll/salary-increments/single', [
            'staff_id' => $this->testEmployeeId2,
            'increment_type' => 'decrement_percentage',
            'percentage' => 10,
            'effective_date' => '2026-10-01',
            'reason' => 'Restructuring adjustment',
        ]);

        $pctDecRes->assertStatus(200);
        $pctDecRes->assertJsonPath('status', 'success');
        $pctDecRes->assertJsonPath('data.new_gross', 900000);
        $pctDecRes->assertJsonPath('data.increase_amount', -100000);

        // 3. Validation: prevent reducing salary below 0
        $invalidDecRes = $this->postJson('/api/nextjs/payroll/salary-increments/single', [
            'staff_id' => $this->testEmployeeId,
            'increment_type' => 'decrement_fixed',
            'amount' => 1500000, // Exceeds current gross of 900,000
            'effective_date' => '2026-10-01',
        ]);

        $invalidDecRes->assertStatus(422);
    }

    public function test_preview_and_upload_bulk_spreadsheet_with_decrements()
    {
        // Test mixed file: Employee 1 gets -₦50,000 reduction, Employee 2 gets -10% reduction
        $csvContent = "Staff ID,Department,Increment Amount,Increment Percentage,Effective Date\n";
        $csvContent .= "{$this->testEmployeeId},Medical,-50000,,2026-10-01\n";
        $csvContent .= "{$this->testEmployeeId2},Medical,,-10%,2026-10-01\n";

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('salary_decrements.csv', $csvContent);

        // 1. Preview
        $previewRes = $this->postJson('/api/nextjs/payroll/salary-increments/preview-upload', [
            'file' => $file
        ]);
        $previewRes->assertStatus(200);
        $previewRes->assertJsonPath('status', 'success');
        $this->assertEquals(2, $previewRes->json('data.valid_count'));
        $this->assertEquals(2, $previewRes->json('data.decrements_count'));
        $this->assertEquals(0, $previewRes->json('data.increments_count'));
        $this->assertLessThan(0, $previewRes->json('data.total_monthly_increase'));

        // 2. Upload
        $uploadFile = \Illuminate\Http\UploadedFile::fake()->createWithContent('salary_decrements.csv', $csvContent);
        $uploadRes = $this->postJson('/api/nextjs/payroll/salary-increments/upload', [
            'file' => $uploadFile
        ]);
        $uploadRes->assertStatus(200);
        $uploadRes->assertJsonPath('status', 'success');
        $this->assertEquals(2, $uploadRes->json('data.updated_count'));
        $this->assertEquals(2, $uploadRes->json('data.decrements_count'));

        // Verify Employee 1: 1,000,000 - 50,000 = 950,000 -> basic 190,000
        $struct1 = DB::table('salary_structures')->where('staffId', $this->testEmployeeId)->first();
        $this->assertEquals(190000.00, (float)$struct1->basic_salary);

        // Verify Employee 2: 1,000,000 - 10% = 900,000 -> basic 180,000
        $struct2 = DB::table('salary_structures')->where('staffId', $this->testEmployeeId2)->first();
        $this->assertEquals(180000.00, (float)$struct2->basic_salary);
    }
}


