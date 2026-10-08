<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MidMonthJoiningOtherDeductionTest extends TestCase
{
    use DatabaseTransactions;

    private $testStaffId = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a staff who joined on 8th October 2026
        $this->testStaffId = DB::table('tblper')->insertGetId([
            'title' => 'MR.',
            'surname' => 'OCTOBER_SURNAME',
            'first_name' => 'OCTOBER_FIRSTNAME',
            'othernames' => 'TEST_JOIN',
            'rank' => 0,
            'staff_status' => 1,
            'fileNo' => 'TEST_OCT_008',
            'courtID' => 9,
            'divisionID' => 1,
            'departmentID' => 79,
            'unitID' => 21,
            'designationID' => 5,
            'appointment_date' => '2026-10-08',
            'doj' => '2026-10-08',
            'created_at' => now(),
        ]);

        $this->cleanUpTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanUpTestData();
        if ($this->testStaffId) {
            DB::table('tblper')->where('ID', $this->testStaffId)->delete();
        }
        parent::tearDown();
    }

    private function cleanUpTestData()
    {
        if ($this->testStaffId) {
            DB::table('salary_structures')->where('staffId', $this->testStaffId)->delete();
            DB::table('first_salary_structure')->where('staffId', $this->testStaffId)->delete();
            DB::table('leave_of_absent')->where('staffId', $this->testStaffId)->delete();
            DB::table('payroll_conpt')->where('staffID', $this->testStaffId)->delete();
            DB::table('other_deduction_setups')->where('staffId', $this->testStaffId)->delete();
        }

        DB::table('payroll_runs')->where('month', 10)->where('year', 2026)->delete();
        DB::table('payroll_conpt')->where('month', 10)->where('year', 2026)->where('vstage', '>=', 4)->update(['vstage' => 1, 'salary_lock' => 0]);
    }

    public function test_mid_month_joiner_october_8_records_1st_to_7th_as_other_deduction_not_loa(): void
    {
        $superAdminRole = DB::table('assign_user_role')->where('roleID', 1)->first();
        if (!$superAdminRole) {
            $this->markTestSkipped('No superadmin user found in database.');
            return;
        }

        $headers = ['X-User-Id' => $superAdminRole->userID];

        // 1. Assign Salary Structure (Gross: 155,000.00 / month)
        // October 2026 has 31 days.
        // Daily rate = 155,000.00 / 31 = 5,000.00 / day
        // 1st to 7th unworked days = 7 days
        // Deduction amount = 7 * 5,000.00 = 35,000.00
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $this->testStaffId],
            [
                'basic_salary' => 100000.00,
                'housing_allowance' => 20000.00,
                'transport_allowance' => 10000.00,
                'medical_allowance' => 10000.00,
                'utility_allowance' => 10000.00,
                'meal_allowance' => 5000.00,
                'pension_rate' => 0.00,
                'tax_rate' => 0.00,
                'pen_act' => 0,
                'reten_act' => 0,
                'created_at' => now(),
            ]
        );

        // 2. Pre-compute salary breakdown check
        $breakdownRes = $this->getJson("/api/nextjs/payroll/salary-breakdown?staff_id={$this->testStaffId}&month=10&year=2026", $headers);
        $breakdownRes->assertStatus(200);

        $breakdown = $breakdownRes->json();
        $this->assertEquals('success', $breakdown['status']);

        // Assert LOA is 0 (NOT LOA)
        $this->assertEquals(0.00, (float)$breakdown['deductions']['leave_of_absence']['amount']);
        $this->assertEquals(0, (int)$breakdown['deductions']['leave_of_absence']['days_absent']);

        // Assert Other Deduction captured the 7 unworked days
        $otherDeduct = $breakdown['deductions']['other_deductions'];
        $this->assertEquals(35000.00, (float)$otherDeduct['amount']);
        $this->assertStringContainsString('7 unworked days', $otherDeduct['remarks']);

        // Assert Paid Days is 31 - 7 = 24
        $this->assertEquals(24, (int)$breakdown['summary']['paid_days']);

        // Assert no records in leave_of_absent table
        $this->assertEquals(0, DB::table('leave_of_absent')->where('staffId', $this->testStaffId)->count());

        // 3. Perform official Payroll Compute for October 2026
        $computeRes = $this->postJson('/api/nextjs/payroll/compute', [
            'month' => 'OCTOBER',
            'year' => '2026',
            'force_unlock' => true,
        ], $headers);
        $computeRes->assertStatus(200);

        // 4. Verify payroll_conpt table
        $conpt = DB::table('payroll_conpt')
            ->where('staffID', $this->testStaffId)
            ->where('month', 10)
            ->where('year', 2026)
            ->first();

        $this->assertNotNull($conpt);
        $this->assertEquals(24, (int)$conpt->paid_days);
        $this->assertEquals(0.00, (float)$conpt->leave_of_absence_deduction, 'LOA deduction must be 0.00 for mid-month hire unworked days');
        $this->assertEquals(35000.00, (float)$conpt->other_deductions, 'Unworked days must be recorded in other_deductions');
        $this->assertEquals(35000.00, (float)$conpt->total_deductions);
        $this->assertEquals(120000.00, (float)$conpt->net_pay, 'Net pay must be 155,000 - 35,000 = 120,000');

        // 5. Verify other_deduction_setups table has recorded the 7 unworked days
        $setup = DB::table('other_deduction_setups')
            ->where('staffId', $this->testStaffId)
            ->where('start_month', '2026-10')
            ->first();

        $this->assertNotNull($setup);
        $this->assertEquals('days', $setup->calculation_mode);
        $this->assertEquals(7, (int)$setup->deduction_days);
        $this->assertEquals(31, (int)$setup->days_in_month);
        $this->assertEquals(35000.00, (float)$setup->total_amount);
        $this->assertStringContainsString('7 unworked days', $setup->remarks);

        // 6. Verify LOA table still has 0 rows for this staff
        $this->assertEquals(0, DB::table('leave_of_absent')->where('staffId', $this->testStaffId)->count());
    }

    public function test_new_staff_store_creates_other_deduction_setup_and_not_loa(): void
    {
        $superAdminRole = DB::table('assign_user_role')->where('roleID', 1)->first();
        if (!$superAdminRole) {
            $this->markTestSkipped('No superadmin user found in database.');
            return;
        }

        $headers = ['X-User-Id' => $superAdminRole->userID];

        // Store a new staff joining on 8th October 2026
        $res = $this->postJson('/api/nextjs/hr/add-staff', [
            'title' => 'MR',
            'surname' => 'TEST_JOINER_OCT',
            'firstname' => 'MID_MONTH',
            'othernames' => 'STAFF',
            'sex' => 'Male',
            'date_of_birth' => '1998-05-15',
            'maritalStatus' => 'Single',
            'department_id' => 79,
            'unit_id' => 21,
            'designation_id' => 5,
            'date_of_joining' => '2026-10-08',
            'email' => 'midmonth_test@isalu.gov.ng',
            'phone' => '08012345678',
            'address' => 'Test Address',
        ], $headers);

        $res->assertStatus(201);

        $newStaff = DB::table('tblper')->where('email', 'midmonth_test@isalu.gov.ng')->first();
        $this->assertNotNull($newStaff);

        try {
            // Must NOT have any leave_of_absent records
            $this->assertEquals(0, DB::table('leave_of_absent')->where('staffId', $newStaff->ID)->count());

            // MUST have an other_deduction_setups record with 7 days for 2026-10
            $setup = DB::table('other_deduction_setups')
                ->where('staffId', $newStaff->ID)
                ->where('start_month', '2026-10')
                ->first();

            $this->assertNotNull($setup);
            $this->assertEquals('days', $setup->calculation_mode);
            $this->assertEquals(7, (int)$setup->deduction_days);
            $this->assertEquals(31, (int)$setup->days_in_month);
            $this->assertStringContainsString('7 unworked days', $setup->remarks);
        } finally {
            DB::table('other_deduction_setups')->where('staffId', $newStaff->ID)->delete();
            DB::table('users')->where('id', $newStaff->UserID)->delete();
            DB::table('tblper')->where('ID', $newStaff->ID)->delete();
        }
    }

    public function test_mid_month_joiner_with_subsequent_loa_maintains_both_unworked_days_and_loa_days(): void
    {
        $superAdminRole = DB::table('assign_user_role')->where('roleID', 1)->first();
        if (!$superAdminRole) {
            $this->markTestSkipped('No superadmin user found in database.');
            return;
        }

        $headers = ['X-User-Id' => $superAdminRole->userID];

        // Assign Salary Structure (155,000.00 Gross)
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $this->testStaffId],
            [
                'basic_salary' => 100000.00,
                'housing_allowance' => 20000.00,
                'transport_allowance' => 10000.00,
                'medical_allowance' => 10000.00,
                'utility_allowance' => 10000.00,
                'meal_allowance' => 5000.00,
                'pension_rate' => 0.00,
                'tax_rate' => 0.00,
                'pen_act' => 0,
                'reten_act' => 0,
            ]
        );

        // Add 2 days approved LOA (e.g. October 9th to 10th)
        DB::table('leave_of_absent')->insert([
            'staffId' => $this->testStaffId,
            'start_date' => '2026-10-09',
            'end_date' => '2026-10-10',
            'reason_of_leave' => 'Personal emergency leave',
            'status' => 2, // Approved
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $res = $this->getJson("/api/nextjs/payroll/salary-breakdown?staff_id={$this->testStaffId}&month=10&year=2026", $headers);
        $res->assertStatus(200);

        $breakdown = $res->json();

        // Must show exactly 7 unworked days before appointment (NOT 5)
        $this->assertEquals(7, (int)$breakdown['deductions']['mid_month_adjustment']['unworked_days']);
        $this->assertEquals(7, (int)$breakdown['summary']['days_unworked_before_appointment']);

        // Must show exactly 2 LOA days
        $this->assertEquals(2, (int)$breakdown['deductions']['leave_of_absence']['days_absent']);

        // Paid Days must be 31 - 7 - 2 = 22 Days (NOT 24)
        $this->assertEquals(22, (int)$breakdown['summary']['paid_days']);
        $this->assertEquals(22, (int)$breakdown['period']['paid_days']);

        // Other deductions captures the 7 unworked days
        $this->assertEquals(35000.00, (float)$breakdown['deductions']['other_deductions']['amount']);
    }
}
