<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SalaryComputeApiTest extends TestCase
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
            DB::table('leave_of_absent')->where('staffId', $employee->ID)->delete();
            DB::table('payroll_conpt')->where('staffID', $employee->ID)->delete();
            DB::table('coop_loan_deduction_setups')->where('staffId', $employee->ID)->delete();
            DB::table('coop_savings_setups')->where('staffId', $employee->ID)->delete();
            DB::table('medical_loan_deduction_setups')->where('staffId', $employee->ID)->delete();
            DB::table('surcharge_deduction_setups')->where('staffId', $employee->ID)->delete();
            DB::table('absence_penalty_deduction_setups')->where('staffId', $employee->ID)->delete();
            DB::table('other_deduction_setups')->where('staffId', $employee->ID)->delete();
            DB::table('employee_loans')->where('staffId', $employee->ID)->delete();
            DB::table('loan_deduction_setups')->where('staffId', $employee->ID)->delete();
        }
        
        DB::table('payroll_runs')->where('month', 5)->where('year', 2026)->delete();
        DB::table('payroll_runs')->where('month', 6)->where('year', 2026)->delete();
    }

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

        // Clear existing staff earning/deductions for this employee to ensure clean run
        DB::table('staffEarningAndDeduction')->where('staffId', $employee->ID)->delete();

        // Ensure retention activation is deactivated for the baseline success run
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $employee->ID],
            ['reten_act' => 0]
        );

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

        // Verify that payroll details were inserted for the active employee with correct month and year
        $this->assertDatabaseHas('payroll_conpt', [
            'payroll_run_id' => $payrollRunId,
            'staffID' => $employee->ID,
            'month' => 5,
            'year' => 2026,
        ]);

        $row = DB::table('payroll_conpt')->where('payroll_run_id', $payrollRunId)->where('staffID', $employee->ID)->first();
        $this->assertNotNull($row);
        $this->assertEquals(0.00, $row->retention);
        $this->assertEquals(0.00, $row->surcharges);
        $this->assertEquals(0.00, $row->medical_loan);
        $this->assertEquals(0.00, $row->coop_loan_rpyt);
    }

    /**
     * Test pension deduction behavior based on pen_act in salary_structures.
     */
    public function test_compute_salary_pension_deduction()
    {
        $superAdminRole = DB::table('assign_user_role')->where('roleID', 1)->first();
        if (!$superAdminRole) {
            $this->markTestSkipped('No superadmin user found in database to test execution.');
            return;
        }

        $headers = ['X-User-Id' => $superAdminRole->userID];

        // Find or create active employee
        $employee = DB::table('tblper')->where('rank', '!=', 2)->where('staff_status', 1)->first();
        $this->assertNotNull($employee, 'Require at least one active employee');

        // Create a pension setup CV Setup if it doesn't exist
        $cvSetup = DB::table('tblcvSetup')
            ->where('particularID', 2) // Deduction
            ->where('description', 'like', '%pension%')
            ->where('status', 1)
            ->first();

        if (!$cvSetup) {
            $cvSetupId = DB::table('tblcvSetup')->insertGetId([
                'particularID' => 2,
                'description' => 'Pension Cont.',
                'status' => 1,
                'economiccode' => 1
            ]);
            $cvSetup = DB::table('tblcvSetup')->where('ID', $cvSetupId)->first();
        }

        // Add pension deduction flag to employee in staffEarningAndDeduction
        DB::table('staffEarningAndDeduction')->where('staffId', $employee->ID)->delete();
        DB::table('staffEarningAndDeduction')->insert([
            'staffId' => $employee->ID,
            'variable_type' => 'deduction',
            'cv_setup_id' => $cvSetup->ID,
            'description' => $cvSetup->description,
            'amount' => 5000.00,
            'no_limit' => 1,
            'one_time' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Scenario 1: pen_act is 0 (Pension should NOT be deducted)
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $employee->ID],
            [
                'basic_salary' => 100000.00,
                'housing_allowance' => 20000.00,
                'transport_allowance' => 10000.00,
                'pension_rate' => 8.00, // 8% pension
                'pen_act' => 0,
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
            'pension' => 0.00 // Pension should be 0.00 because pen_act is 0
        ]);

        // Scenario 2: pen_act is 1 (Pension SHOULD be deducted)
        DB::table('salary_structures')->where('staffId', $employee->ID)->update([
            'pen_act' => 1
        ]);

        // Run compute again (it deletes previous conpt records inside the route)
        $response = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'MAY',
            'year' => '2026'
        ], $headers);

        $response->assertStatus(200);

        $row = DB::table('payroll_conpt')->where('payroll_run_id', $runId)->where('staffID', $employee->ID)->first();
        $this->assertNotNull($row);

        // Pension calculation: grossPay * (pension_rate / 100.0)
        $expectedPension = round((float)$row->gross_pay * 0.08, 2);
        
        $this->assertDatabaseHas('payroll_conpt', [
            'payroll_run_id' => $runId,
            'staffID' => $employee->ID,
            'pension' => $expectedPension // Pension should be calculated dynamically when pen_act is 1
        ]);
    }

    /**
     * Test that specific deductions (Retention, Surcharges, Medical Loan, Coop. Loan Repayment)
     * are correctly parsed, saved to database, and fetched.
     */
    public function test_compute_salary_with_specific_deductions()
    {
        $superAdminRole = DB::table('assign_user_role')->where('roleID', 1)->first();
        if (!$superAdminRole) {
            $this->markTestSkipped('No superadmin user found in database to test execution.');
            return;
        }

        $headers = ['X-User-Id' => $superAdminRole->userID];

        // Find or create active employee
        $employee = DB::table('tblper')->where('rank', '!=', 2)->where('staff_status', 1)->first();
        $this->assertNotNull($employee, 'Require at least one active employee');

        // Delete any existing setups for this employee
        DB::table('staffEarningAndDeduction')->where('staffId', $employee->ID)->delete();
        DB::table('surcharge_deduction_setups')->where('staffId', $employee->ID)->delete();
        DB::table('medical_loan_deduction_setups')->where('staffId', $employee->ID)->delete();
        DB::table('coop_loan_deduction_setups')->where('staffId', $employee->ID)->delete();
        DB::table('coop_savings_setups')->where('staffId', $employee->ID)->delete();

        // Insert surcharge setup
        DB::table('surcharge_deduction_setups')->insert([
            'staffId' => $employee->ID,
            'total_amount' => 5000.00,
            'monthly_deduction' => 500.00,
            'balance_remaining' => 5000.00,
            'start_month' => '2026-05',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Insert medical loan setup
        DB::table('medical_loan_deduction_setups')->insert([
            'staffId' => $employee->ID,
            'loan_amount' => 20000.00,
            'duration_months' => 10,
            'monthly_deduction' => 2000.00,
            'balance_remaining' => 20000.00,
            'start_month' => '2026-05',
            'end_month' => '2027-02',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Insert coop loan setup
        DB::table('coop_loan_deduction_setups')->insert([
            'staffId' => $employee->ID,
            'loan_amount' => 35000.00,
            'interest_rate' => 0.0,
            'duration_months' => 10,
            'monthly_deduction' => 3500.00,
            'balance_remaining' => 35000.00,
            'start_month' => '2026-05',
            'end_month' => '2027-02',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Insert coop savings setup
        DB::table('coop_savings_setups')->insert([
            'staffId' => $employee->ID,
            'monthly_saving' => 4500.00,
            'saving_balance' => 0.00,
            'start_month' => '2026-05',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Ensure salary structure exists and has reten_act set to 1
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $employee->ID],
            [
                'basic_salary' => 100000.00,
                'housing_allowance' => 20000.00,
                'transport_allowance' => 10000.00,
                'medical_allowance' => 0.00,
                'utility_allowance' => 0.00,
                'meal_allowance' => 0.00,
                'pension_rate' => 0.00,
                'tax_rate' => 0.00,
                'pen_act' => 0,
                'reten_act' => 1,
                'created_at' => now()
            ]
        );

        // Run compute
        $response = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'MAY',
            'year' => '2026'
        ], $headers);

        $response->assertStatus(200);
        $runId = $response->json('payroll_run_id');
        // Assert database values
        $this->assertDatabaseHas('payroll_conpt', [
            'payroll_run_id' => $runId,
            'staffID' => $employee->ID,
            'retention' => 6500.00, // 5% of (100000 + 20000 + 10000)
            'surcharges' => 500.00,
            'medical_loan' => 2000.00,
            'coop_loan_rpyt' => 3500.00,
            'coop_savings' => 4500.00,
        ]);

        // Run compute again (re-compute)
        $response2 = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'MAY',
            'year' => '2026'
        ], $headers);
        $response2->assertStatus(200);

        // Verify that balances remain correctly decremented/incremented once (not twice)
        $surchargeBal = DB::table('surcharge_deduction_setups')->where('staffId', $employee->ID)->value('balance_remaining');
        $medLoanBal = DB::table('medical_loan_deduction_setups')->where('staffId', $employee->ID)->value('balance_remaining');
        $coopLoanBal = DB::table('coop_loan_deduction_setups')->where('staffId', $employee->ID)->value('balance_remaining');
        $coopSavingsBal = DB::table('coop_savings_setups')->where('staffId', $employee->ID)->value('saving_balance');

        $this->assertEquals(4500.00, (float)$surchargeBal);
        $this->assertEquals(18000.00, (float)$medLoanBal);
        $this->assertEquals(31500.00, (float)$coopLoanBal);
        $this->assertEquals(4500.00, (float)$coopSavingsBal);

        // Get list response and verify mapping
        $listResponse = $this->getJson("/api/nextjs/payroll?month=MAY&year=2026", $headers);
        $listResponse->assertStatus(200);

        $data = $listResponse->json('data');
        $employeeRow = collect($data)->firstWhere('IDNO', $employee->fileNo);
        $this->assertNotNull($employeeRow);

        $this->assertEquals('6500.00', $employeeRow['RETENTION']);
        $this->assertEquals('500.00', $employeeRow['SURGHARGES']);
        $this->assertEquals('2000.00', $employeeRow['MEDICAL LOAN']);
        $this->assertEquals('3500.00', $employeeRow['COOP. LOAN RPYT']);
        $this->assertEquals('4500.00', $employeeRow['COOP. SAVING']);
    }

    /**
     * Test that approved leave of absence calculates leave_of_absence_deduction,
     * and unapproved absence penalty is correctly parsed from staffEarningAndDeduction.
     */
    public function test_leave_of_absence_and_absence_penalty()
    {
        $superAdminRole = DB::table('assign_user_role')->where('roleID', 1)->first();
        if (!$superAdminRole) {
            $this->markTestSkipped('No superadmin.');
            return;
        }

        $headers = ['X-User-Id' => $superAdminRole->userID];

        $employee = DB::table('tblper')->where('rank', '!=', 2)->where('staff_status', 1)->first();
        $this->assertNotNull($employee);

        // Delete any existing staff earning/deductions/loans/LOA for this employee
        DB::table('staffEarningAndDeduction')->where('staffId', $employee->ID)->delete();
        DB::table('leave_of_absent')->where('staffId', $employee->ID)->delete();
        DB::table('payroll_conpt')->where('staffID', $employee->ID)->delete();

        // 1. Configure salary structure (Gross Monthly Salary = 150000.00)
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $employee->ID],
            [
                'basic_salary' => 100000.00,
                'housing_allowance' => 20000.00,
                'transport_allowance' => 10000.00,
                'medical_allowance' => 10000.00,
                'utility_allowance' => 5000.00,
                'meal_allowance' => 5000.00,
                'pension_rate' => 0.00,
                'tax_rate' => 0.00,
                'pen_act' => 0,
                'reten_act' => 0,
                'created_at' => now()
            ]
        );

        // 2. Add an approved leave of absence (status = 2) for 6 days in May 2026
        DB::table('leave_of_absent')->insert([
            'staffId' => $employee->ID,
            'start_date' => '2026-05-10',
            'end_date' => '2026-05-15', // 6 days inclusive
            'status' => 2, // Approved
            'created_at' => now()
        ]);

        // 3. Add an unapproved absence penalty in setups
        DB::table('absence_penalty_deduction_setups')->where('staffId', $employee->ID)->delete();
        DB::table('absence_penalty_deduction_setups')->insert([
            'staffId' => $employee->ID,
            'deduction_type' => 'one_time',
            'total_amount' => 5000.00,
            'duration_months' => 1,
            'monthly_deduction' => 5000.00,
            'balance_remaining' => 5000.00,
            'start_month' => '2026-05',
            'end_month' => '2026-05',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Run compute for MAY 2026
        $response = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'MAY',
            'year' => '2026'
        ], $headers);

        $response->assertStatus(200);
        $runId = $response->json('payroll_run_id');

        // Gross Pay = 150000.00
        // Paid Days = 30 - 6 = 24
        // LOA Deduction = (150000.00 / 30) * 6 = 30000.00
        // Absence Penalty (unapproved) = 5000.00
        // Total Deductions = 30000.00 + 5000.00 = 35000.00
        // Net Pay = 150000.00 - 35000.00 = 115000.00

        $this->assertDatabaseHas('payroll_conpt', [
            'payroll_run_id' => $runId,
            'staffID' => $employee->ID,
            'paid_days' => 24,
            'leave_of_absence_deduction' => 30000.00,
            'absence_penalty' => 5000.00,
            'total_deductions' => 35000.00,
            'net_pay' => 115000.00
        ]);

        // Verify report response columns
        $listResponse = $this->getJson("/api/nextjs/payroll?month=MAY&year=2026", $headers);
        $listResponse->assertStatus(200);

        $data = $listResponse->json('data');
        $employeeRow = collect($data)->firstWhere('IDNO', $employee->fileNo);
        $this->assertNotNull($employeeRow);

        $this->assertEquals(24, $employeeRow['PAID DAYS']);
        $this->assertEquals('30000.00', $employeeRow['LEAVE OF ABSENCE DEDUCTION']);
        $this->assertEquals('5000.00', $employeeRow['ABSENCE PENALTY']);
    }

    /**
     * Test revolving loan balance displays and deduction limits.
     */
    public function test_revolving_loan_balance()
    {
        $superAdminRole = DB::table('assign_user_role')->where('roleID', 1)->first();
        if (!$superAdminRole) {
            $this->markTestSkipped('No admin user');
        }
        $headers = ['X-User-Id' => $superAdminRole->userID];

        $employee = DB::table('tblper')->where('rank', '!=', 2)->where('staff_status', 1)->first();
        if (!$employee) {
            $this->markTestSkipped('No active employee');
        }

        // Setup a basic structure for this employee
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $employee->ID],
            [
                'basic_salary' => 100000.00,
                'housing_allowance' => 0.00,
                'transport_allowance' => 0.00,
                'medical_allowance' => 0.00,
                'utility_allowance' => 0.00,
                'meal_allowance' => 0.00,
                'pension_rate' => 0.00,
                'tax_rate' => 0.00,
                'pen_act' => 0,
                'reten_act' => 0,
                'created_at' => now()
            ]
        );

        DB::table('employee_loans')->where('staffId', $employee->ID)->delete();
        DB::table('coop_loan_deduction_setups')->where('staffId', $employee->ID)->delete();

        // Insert revolving loan into employee_loans
        DB::table('employee_loans')->insert([
            'staffId' => $employee->ID,
            'loan_amount' => 50000.00,
            'balance' => 50000.00,
            'monthly_deduction' => 10000.00,
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Insert coop loan setup to ensure it is NOT counted in REVOLVING LOAN BAL
        DB::table('coop_loan_deduction_setups')->insert([
            'staffId' => $employee->ID,
            'loan_amount' => 30000.00,
            'interest_rate' => 0.0,
            'duration_months' => 3,
            'monthly_deduction' => 10000.00,
            'balance_remaining' => 30000.00,
            'start_month' => '2026-05',
            'end_month' => '2026-07',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Compute payroll
        $response = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'MAY',
            'year' => '2026'
        ], $headers);

        $response->assertStatus(200);

        // Verify report returns REVOLVING LOAN BAL as 40000.00 (50000 - 10000) and COP. LONE BAL as 20000.00 (30000 - 10000)
        $listResponse = $this->getJson("/api/nextjs/payroll?month=MAY&year=2026", $headers);
        $listResponse->assertStatus(200);

        $data = $listResponse->json('data');
        $employeeRow = collect($data)->firstWhere('IDNO', $employee->fileNo);
        $this->assertNotNull($employeeRow);
        $this->assertEquals('40000.00', $employeeRow['REVOLVING LOAN BAL']);
        $this->assertEquals('20000.00', $employeeRow['COP. LONE BAL']);
    }

    /**
     * Test coop loan balance displays and deduction limits.
     */
    public function test_coop_loan_balance()
    {
        $superAdminRole = DB::table('assign_user_role')->where('roleID', 1)->first();
        if (!$superAdminRole) {
            $this->markTestSkipped('No admin user');
        }
        $headers = ['X-User-Id' => $superAdminRole->userID];

        $employee = DB::table('tblper')->where('rank', '!=', 2)->where('staff_status', 1)->first();
        if (!$employee) {
            $this->markTestSkipped('No active employee');
        }

        // Setup a basic structure for this employee
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $employee->ID],
            [
                'basic_salary' => 100000.00,
                'housing_allowance' => 0.00,
                'transport_allowance' => 0.00,
                'medical_allowance' => 0.00,
                'utility_allowance' => 0.00,
                'meal_allowance' => 0.00,
                'pension_rate' => 0.00,
                'tax_rate' => 0.00,
                'pen_act' => 0,
                'reten_act' => 0,
                'created_at' => now()
            ]
        );

        DB::table('coop_loan_deduction_setups')->where('staffId', $employee->ID)->delete();

        // Insert a coop loan setup
        $coopLoanId = DB::table('coop_loan_deduction_setups')->insertGetId([
            'staffId' => $employee->ID,
            'loan_amount' => 30000.00,
            'interest_rate' => 0.0,
            'duration_months' => 3,
            'monthly_deduction' => 10000.00,
            'balance_remaining' => 30000.00,
            'start_month' => '2026-05',
            'end_month' => '2026-07',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Compute payroll
        $response = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'MAY',
            'year' => '2026'
        ], $headers);

        $response->assertStatus(200);

        // Verify balance_remaining has been decremented by 10000
        $coopVar = DB::table('coop_loan_deduction_setups')->where('id', $coopLoanId)->first();
        $this->assertEquals(20000.00, (float)$coopVar->balance_remaining);

        // Verify report returns COP. LONE BAL as 20000.00 (30000 - 10000)
        $listResponse = $this->getJson("/api/nextjs/payroll?month=MAY&year=2026", $headers);
        $listResponse->assertStatus(200);

        $data = $listResponse->json('data');
        $employeeRow = collect($data)->firstWhere('IDNO', $employee->fileNo);
        $this->assertNotNull($employeeRow);
        $this->assertEquals('20000.00', $employeeRow['COP. LONE BAL']);
    }

    /**
     * Test auto deactivation of deduction setups when balance reaches zero, and reactivation on revert.
     */
    public function test_deduction_setups_auto_deactivate_and_reactivate()
    {
        $superAdminRole = DB::table('assign_user_role')->where('roleID', 1)->first();
        if (!$superAdminRole) {
            $this->markTestSkipped('No superadmin user found in database.');
            return;
        }

        $headers = ['X-User-Id' => $superAdminRole->userID];

        $employee = DB::table('tblper')->where('rank', '!=', 2)->where('staff_status', 1)->first();
        if (!$employee) {
            $this->markTestSkipped('No active employee');
            return;
        }

        // Setup a basic structure for this employee
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $employee->ID],
            [
                'basic_salary' => 100000.00,
                'housing_allowance' => 0.00,
                'transport_allowance' => 0.00,
                'medical_allowance' => 0.00,
                'utility_allowance' => 0.00,
                'meal_allowance' => 0.00,
                'pension_rate' => 0.00,
                'tax_rate' => 0.00,
                'pen_act' => 0,
                'reten_act' => 0,
                'created_at' => now()
            ]
        );

        DB::table('other_deduction_setups')->where('staffId', $employee->ID)->delete();

        // Insert an other deduction setup with remaining balance exactly equal to monthly deduction
        $otherDeductionId = DB::table('other_deduction_setups')->insertGetId([
            'staffId' => $employee->ID,
            'deduction_type' => 'one_time',
            'total_amount' => 5000.00,
            'duration_months' => 1,
            'monthly_deduction' => 5000.00,
            'balance_remaining' => 5000.00,
            'start_month' => '2026-05',
            'end_month' => '2026-05',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Compute payroll first time - should reduce balance to 0 and deactivate
        $response = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'MAY',
            'year' => '2026'
        ], $headers);

        $response->assertStatus(200);

        // Verify balance_remaining is 0 and is_active is 0
        $setup = DB::table('other_deduction_setups')->where('id', $otherDeductionId)->first();
        $this->assertEquals(0.00, (float)$setup->balance_remaining);
        $this->assertEquals(0, $setup->is_active);

        // Check conpt record is present with 5000.00 other_deductions
        $conpt = DB::table('payroll_conpt')
            ->where('staffID', $employee->ID)
            ->where('month', 5)
            ->where('year', 2026)
            ->first();
        $this->assertNotNull($conpt);
        $this->assertEquals(5000.00, (float)$conpt->other_deductions);

        // Compute payroll second time (recompute for the same month)
        // Revert phase should restore balance to 5000.00 and reactivate (is_active = 1).
        // Calculation phase should then compute it again, ending up as 0 and deactivated.
        $response = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'MAY',
            'year' => '2026'
        ], $headers);

        $response->assertStatus(200);

        // Verify setup is still balance_remaining = 0 and is_active = 0
        $setup = DB::table('other_deduction_setups')->where('id', $otherDeductionId)->first();
        $this->assertEquals(0.00, (float)$setup->balance_remaining);
        $this->assertEquals(0, $setup->is_active);

        // Verify conpt record is still present with 5000.00 (which confirms it was reactivated and computed again)
        $conpt = DB::table('payroll_conpt')
            ->where('staffID', $employee->ID)
            ->where('month', 5)
            ->where('year', 2026)
            ->first();
        $this->assertNotNull($conpt);
        $this->assertEquals(5000.00, (float)$conpt->other_deductions);
    }
}
