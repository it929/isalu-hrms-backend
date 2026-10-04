<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PayslipSalaryAdjustmentApiTest extends TestCase
{
    use DatabaseTransactions;

    private $testStaffId;
    private $headers;
    private $originalActiveMonth;

    protected function setUp(): void
    {
        parent::setUp();

        // Backup current active month so tests do not disrupt development data
        $this->originalActiveMonth = DB::table('tblactivemonth')->first();

        // Find or create test user and staff
        $user = DB::table('users')->where('id', 1)->first();
        if (!$user) {
            $user = (object)['id' => 1, 'email' => 'admin@isalu.com'];
        }

        // Ensure user 1 has super admin role
        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $user->id,
            'roleID' => 1,
        ]);

        $this->headers = ['X-User-Id' => $user->id];

        // Create isolated test department
        $deptId = DB::table('tbldepartment')->insertGetId([
            'courtID' => '9',
            'department' => 'TEST_PAYSLIP_DEPT',
            'head' => 0,
        ]);

        // Create test staff
        $this->testStaffId = DB::table('tblper')->insertGetId([
            'title' => 'MR.',
            'surname' => 'PAYSLIP_INC_TEST',
            'first_name' => 'SAMUEL',
            'othernames' => 'L.',
            'rank' => 0,
            'staff_status' => 1,
            'fileNo' => 'PAY9988',
            'courtID' => 9,
            'divisionID' => 1,
            'departmentID' => $deptId,
            'unitID' => 21,
            'designation' => 'Pharmacist',
            'email' => 'samuel.payslip@testisalu.com',
            'AccNo' => '0123456789'
        ]);

        // Clean up any test conpt or increments for this test staff only
        DB::table('payroll_conpt')->where('staffID', $this->testStaffId)->delete();
        DB::table('salary_increments')->where('staff_id', $this->testStaffId)->delete();
    }

    protected function tearDown(): void
    {
        // Restore active month if it was modified
        if ($this->originalActiveMonth) {
            DB::table('tblactivemonth')->delete();
            DB::table('tblactivemonth')->insert((array)$this->originalActiveMonth);
        }

        // Clean up test staff records
        DB::table('payroll_conpt')->where('staffID', $this->testStaffId)->delete();
        DB::table('salary_increments')->where('staff_id', $this->testStaffId)->delete();
        DB::table('salary_structures')->where('staffId', $this->testStaffId)->delete();
        DB::table('tblper')->where('ID', $this->testStaffId)->delete();

        parent::tearDown();
    }

    public function test_payslip_shows_salary_increment_in_the_effective_month()
    {
        $run = DB::table('payroll_runs')->where('month', 10)->where('year', 2026)->first();
        $runIdOct = $run ? $run->id : DB::table('payroll_runs')->insertGetId([
            'month' => 10,
            'year' => 2026,
            'status' => 'processed'
        ]);

        // Insert paid conpt record for October 2026
        DB::table('payroll_conpt')->insert([
            'payroll_run_id' => $runIdOct,
            'staffID' => $this->testStaffId,
            'month' => 10,
            'year' => 2026,
            'basic' => 200000.00,
            'total_income' => 1000000.00,
            'salary_lock' => 1,
            'vstage' => 4,
            'is_paid' => 1
        ]);

        // Insert salary increment effective in October 2026 (2026-10-01)
        DB::table('salary_increments')->insert([
            'staff_id' => $this->testStaffId,
            'increment_type' => 'percentage',
            'percentage' => 15.00,
            'amount' => 150000.00,
            'previous_gross_salary' => 850000.00,
            'new_gross_salary' => 1000000.00,
            'increase_amount' => 150000.00,
            'previous_basic' => 170000.00,
            'new_basic' => 200000.00,
            'effective_date' => '2026-10-01',
            'reason' => 'Annual merit promotion increment',
            'status' => 'applied',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Request October 2026 payslip
        $response = $this->getJson("/api/nextjs/payroll/payslip?staff_id={$this->testStaffId}&month=OCTOBER&year=2026", $this->headers);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.salary_adjustment.has_adjustment', true)
            ->assertJsonPath('data.salary_adjustment.action', 'increment')
            ->assertJsonPath('data.salary_adjustment.is_increment', true)
            ->assertJsonPath('data.salary_adjustment.is_decrement', false)
            ->assertJsonPath('data.salary_adjustment.badge_label', 'SALARY INCREMENT')
            ->assertJsonPath('data.salary_adjustment.diff_amount', 150000)
            ->assertJsonPath('data.salary_adjustment.percentage', 15)
            ->assertJsonPath('data.salary_adjustment.previous_gross', 850000)
            ->assertJsonPath('data.salary_adjustment.new_gross', 1000000)
            ->assertJsonPath('data.salary_adjustment.reason', 'Annual merit promotion increment');
    }

    public function test_payslip_and_payroll_shows_salary_increment_with_slash_date_1_9_2026()
    {
        $run = DB::table('payroll_runs')->where('month', 9)->where('year', 2026)->first();
        $runIdSep = $run ? $run->id : DB::table('payroll_runs')->insertGetId([
            'month' => 9,
            'year' => 2026,
            'status' => 'processed'
        ]);

        DB::table('payroll_conpt')->insert([
            'payroll_run_id' => $runIdSep,
            'staffID' => $this->testStaffId,
            'month' => 9,
            'year' => 2026,
            'basic' => 120000.00,
            'gross_pay' => 600000.00,
            'total_income' => 600000.00,
            'net_pay' => 480000.00,
            'salary_lock' => 1,
            'vstage' => 4,
            'is_paid' => 1
        ]);

        // Effective date given as Nigerian/UK slash format: '1/9/2026' (or '01/09/2026') -> September 2026
        DB::table('salary_increments')->insert([
            'staff_id' => $this->testStaffId,
            'increment_type' => 'fixed_amount',
            'percentage' => null,
            'amount' => 50000.00,
            'previous_gross_salary' => 550000.00,
            'new_gross_salary' => 600000.00,
            'increase_amount' => 50000.00,
            'previous_basic' => 110000.00,
            'new_basic' => 120000.00,
            'effective_date' => '1/9/2026',
            'reason' => 'Quarterly merit increment',
            'status' => 'applied',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1. Verify payslip endpoint parses 1/9/2026 as September 2026
        $payslipRes = $this->getJson("/api/nextjs/payroll/payslip?staff_id={$this->testStaffId}&month=SEPTEMBER&year=2026", $this->headers);
        $payslipRes->assertStatus(200)
            ->assertJsonPath('data.salary_adjustment.has_adjustment', true)
            ->assertJsonPath('data.salary_adjustment.action', 'increment')
            ->assertJsonPath('data.salary_adjustment.is_increment', true)
            ->assertJsonPath('data.salary_adjustment.diff_amount', 50000)
            ->assertJsonPath('data.salary_adjustment.formatted_diff', '+₦50,000.00')
            ->assertJsonPath('data.salary_adjustment.previous_gross', 550000)
            ->assertJsonPath('data.salary_adjustment.new_gross', 600000);

        // 2. Verify payroll list endpoint returns salary_adjustment on the record
        $payrollRes = $this->getJson("/api/nextjs/payroll?month=SEPTEMBER&year=2026", $this->headers);
        $payrollRes->assertStatus(200);

        $payrollData = collect($payrollRes->json('data'));
        $testRow = $payrollData->firstWhere('IDNO', $this->testStaffId);
        $this->assertNotNull($testRow, 'Test staff record should exist in September payroll');
        $this->assertNotNull($testRow['salary_adjustment'], 'Salary adjustment should be attached to payroll record');
        $this->assertTrue($testRow['salary_adjustment']['is_increment']);
        $this->assertEquals(50000, $testRow['salary_adjustment']['diff_amount']);
    }

    public function test_payslip_and_payroll_shows_salary_decrement_with_slash_date_1_9_2026()
    {
        $run = DB::table('payroll_runs')->where('month', 9)->where('year', 2026)->first();
        $runIdSep = $run ? $run->id : DB::table('payroll_runs')->insertGetId([
            'month' => 9,
            'year' => 2026,
            'status' => 'processed'
        ]);

        DB::table('payroll_conpt')->insert([
            'payroll_run_id' => $runIdSep,
            'staffID' => $this->testStaffId,
            'month' => 9,
            'year' => 2026,
            'basic' => 71000.00,
            'gross_pay' => 355000.00,
            'total_income' => 355000.00,
            'net_pay' => 300000.00,
            'salary_lock' => 1,
            'vstage' => 4,
            'is_paid' => 1
        ]);

        // Effective date given as Nigerian/UK slash format: '01/09/2026' -> September 2026 decrement
        DB::table('salary_increments')->insert([
            'staff_id' => $this->testStaffId,
            'increment_type' => 'decrement_fixed',
            'percentage' => null,
            'amount' => -30000.00,
            'previous_gross_salary' => 385000.00,
            'new_gross_salary' => 355000.00,
            'increase_amount' => -30000.00,
            'previous_basic' => 77000.00,
            'new_basic' => 71000.00,
            'effective_date' => '01/09/2026',
            'reason' => 'Spreadsheet decrement adjustment',
            'status' => 'applied',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1. Verify payslip endpoint parses 01/09/2026 as September 2026
        $payslipRes = $this->getJson("/api/nextjs/payroll/payslip?staff_id={$this->testStaffId}&month=SEPTEMBER&year=2026", $this->headers);
        $payslipRes->assertStatus(200)
            ->assertJsonPath('data.salary_adjustment.has_adjustment', true)
            ->assertJsonPath('data.salary_adjustment.action', 'decrement')
            ->assertJsonPath('data.salary_adjustment.is_decrement', true)
            ->assertJsonPath('data.salary_adjustment.badge_label', 'SALARY DECREMENT')
            ->assertJsonPath('data.salary_adjustment.diff_amount', 30000)
            ->assertJsonPath('data.salary_adjustment.formatted_diff', '-₦30,000.00')
            ->assertJsonPath('data.salary_adjustment.previous_gross', 385000)
            ->assertJsonPath('data.salary_adjustment.new_gross', 355000);

        // 2. Verify payroll list endpoint returns decrement on the record
        $payrollRes = $this->getJson("/api/nextjs/payroll?month=SEPTEMBER&year=2026", $this->headers);
        $payrollRes->assertStatus(200);

        $payrollData = collect($payrollRes->json('data'));
        $testRow = $payrollData->firstWhere('IDNO', $this->testStaffId);
        $this->assertNotNull($testRow, 'Test staff record should exist in September payroll');
        $this->assertNotNull($testRow['salary_adjustment']);
        $this->assertTrue($testRow['salary_adjustment']['is_decrement']);
        $this->assertEquals(30000, $testRow['salary_adjustment']['diff_amount']);
    }

    public function test_increment_is_suppressed_if_previous_salary_did_not_actually_increase()
    {
        $run = DB::table('payroll_runs')->where('month', 9)->where('year', 2026)->first();
        $runIdSep = $run ? $run->id : DB::table('payroll_runs')->insertGetId([
            'month' => 9,
            'year' => 2026,
            'status' => 'processed'
        ]);

        DB::table('payroll_conpt')->insert([
            'payroll_run_id' => $runIdSep,
            'staffID' => $this->testStaffId,
            'month' => 9,
            'year' => 2026,
            'basic' => 100000.00,
            'gross_pay' => 500000.00,
            'total_income' => 500000.00,
            'net_pay' => 450000.00,
            'salary_lock' => 1,
            'vstage' => 4,
            'is_paid' => 1
        ]);

        // Erroneous entry: marked as increment, but previous gross is 500,000 and new gross is 500,000 (increase = 0)
        DB::table('salary_increments')->insert([
            'staff_id' => $this->testStaffId,
            'increment_type' => 'percentage',
            'percentage' => 0.00,
            'amount' => 0.00,
            'previous_gross_salary' => 500000.00,
            'new_gross_salary' => 500000.00,
            'increase_amount' => 0.00,
            'previous_basic' => 100000.00,
            'new_basic' => 100000.00,
            'effective_date' => '01/09/2026',
            'reason' => 'Zero change entry',
            'status' => 'applied',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payslipRes = $this->getJson("/api/nextjs/payroll/payslip?staff_id={$this->testStaffId}&month=SEPTEMBER&year=2026", $this->headers);
        $payslipRes->assertStatus(200)
            ->assertJsonPath('data.salary_adjustment', null);
    }

    public function test_decrement_is_suppressed_if_previous_salary_did_not_actually_decrease()
    {
        $run = DB::table('payroll_runs')->where('month', 9)->where('year', 2026)->first();
        $runIdSep = $run ? $run->id : DB::table('payroll_runs')->insertGetId([
            'month' => 9,
            'year' => 2026,
            'status' => 'processed'
        ]);

        DB::table('payroll_conpt')->insert([
            'payroll_run_id' => $runIdSep,
            'staffID' => $this->testStaffId,
            'month' => 9,
            'year' => 2026,
            'basic' => 100000.00,
            'gross_pay' => 500000.00,
            'total_income' => 500000.00,
            'net_pay' => 450000.00,
            'salary_lock' => 1,
            'vstage' => 4,
            'is_paid' => 1
        ]);

        // Erroneous entry: marked decrement, but new gross is >= previous gross
        DB::table('salary_increments')->insert([
            'staff_id' => $this->testStaffId,
            'increment_type' => 'decrement_fixed',
            'percentage' => null,
            'amount' => 0.00,
            'previous_gross_salary' => 500000.00,
            'new_gross_salary' => 520000.00,
            'increase_amount' => 20000.00,
            'previous_basic' => 100000.00,
            'new_basic' => 104000.00,
            'effective_date' => '01/09/2026',
            'reason' => 'Invalid decrement entry',
            'status' => 'applied',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payslipRes = $this->getJson("/api/nextjs/payroll/payslip?staff_id={$this->testStaffId}&month=SEPTEMBER&year=2026", $this->headers);
        $payslipRes->assertStatus(200)
            ->assertJsonPath('data.salary_adjustment', null);
    }
}
