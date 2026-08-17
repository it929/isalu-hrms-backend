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

    protected function setUp(): void
    {
        parent::setUp();

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
            'departmentID' => 79,
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
            'departmentID' => 79,
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
            'department_id' => 79,
            'increment_type' => 'percentage',
            'percentage' => 10,
            'effective_date' => '2026-08-01',
            'reason' => 'General department wage increment',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $this->assertGreaterThanOrEqual(2, $response->json('data.affected_count'));

        // Check history endpoint
        $historyRes = $this->getJson('/api/nextjs/payroll/salary-increments/history?department_id=79');
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
        $response = $this->get('/api/nextjs/payroll/salary-increments/export');
        $response->assertStatus(200);
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
    }
}
