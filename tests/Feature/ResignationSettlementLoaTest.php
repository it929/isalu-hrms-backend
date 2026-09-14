<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ResignationSettlementLoaTest extends TestCase
{
    use DatabaseTransactions;

    private function getHeaders($userId)
    {
        return [
            'X-User-Id' => $userId,
        ];
    }

    /**
     * Test that approved leave of absence is accurately captured as a deduction in exit settlement.
     */
    public function test_exit_settlement_captures_leave_of_absence_deduction()
    {
        $user = DB::table('users')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in DB.');
        }

        DB::table('assign_user_role')->updateOrInsert(
            ['userID' => $user->id, 'roleID' => 1],
            ['created_at' => now()]
        );

        // Create a test employee with monthly gross ₦310,000 (daily rate in 31-day month = ₦10,000)
        $staffId = DB::table('tblper')->insertGetId([
            'UserID'            => $user->id,
            'fileNo'            => 'TEST-LOA-001',
            'surname'           => 'DOE',
            'first_name'        => 'JANE',
            'othernames'        => 'EXIT',
            'office_shift'      => 2, // Shift worker (all calendar days count)
            'staff_status'      => 1,
            'status_value'      => 'active',
            'appointment_date'  => '2025-01-01',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // Set salary structure (Gross = ₦310,000)
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $staffId],
            [
                'basic_salary'        => 100000.00,
                'housing_allowance'   => 70000.00,
                'transport_allowance' => 50000.00,
                'medical_allowance'   => 30000.00,
                'utility_allowance'   => 30000.00,
                'meal_allowance'      => 30000.00,
                'pen_act'             => 0,
                'created_at'          => now(),
            ]
        );

        // Create an approved Leave of Absence for 4 days in August 2026 (August has 31 days)
        // Rate per day = 310,000 / 31 = 10,000. Deduction = 4 * 10,000 = ₦40,000.00
        DB::table('leave_of_absent')->insert([
            'staffId'         => $staffId,
            'start_date'      => '2026-08-10',
            'end_date'        => '2026-08-13', // 4 days inclusive
            'reason_of_leave' => 'Medical checkup leave',
            'status'          => 2, // Approved
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // Create an HR-approved resignation request for this staff (Early month: Aug 5, 2026)
        $resignationId = DB::table('resignation_requests')->insertGetId([
            'staff_id'         => $staffId,
            'reason'           => 'Relocation',
            'resignation_date' => '2026-08-05',
            'status'           => 1,
            'hod_status'       => 1,
            'hod_id'           => $user->id,
            'hod_date'         => now(),
            'admin_status'     => 1, // HR Approved
            'admin_id'         => $user->id,
            'admin_date'       => now(),
            'audit_status'     => 0,
            'finance_status'   => 0,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $headers = $this->getHeaders($user->id);

        // Test GET /api/nextjs/payroll/resignations/settlement/{id}
        $response = $this->getJson("/api/nextjs/payroll/resignations/settlement/{$resignationId}", $headers);
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals('success', $data['status']);

        $settlement = $data['data'];
        $itemized = collect($settlement['deductions']['itemized_deductions']);

        // Find Leave of Absence item
        $loaItem = $itemized->firstWhere('name', 'Leave of Absence (Unpaid Days)');
        $this->assertNotNull($loaItem, 'Leave of Absence deduction item must exist in itemized deductions');
        $this->assertEquals(40000.00, $loaItem['amount'], 'LOA deduction should be ₦40,000.00 for 4 days at ₦10,000/day');
        $this->assertStringContainsString('4 unpaid day(s)', $loaItem['note']);

        // Verify total deductions includes the LOA deduction
        $this->assertGreaterThanOrEqual(40000.00, $settlement['deductions']['total_deductions']);

        // Verify structured leave_of_absence key
        $this->assertArrayHasKey('leave_of_absence', $settlement);
        $this->assertEquals(4, $settlement['leave_of_absence']['total_days']);
        $this->assertEquals(40000.00, $settlement['leave_of_absence']['total_deduction']);
        $this->assertCount(1, $settlement['leave_of_absence']['records']);
        $this->assertEquals('Medical checkup leave', $settlement['leave_of_absence']['records'][0]['reason']);

        // Verify Approved Registry list also captures LOA in total_deductions
        $registryResponse = $this->getJson('/api/nextjs/payroll/resignations/approved', $headers);
        $registryResponse->assertStatus(200);

        $records = collect($registryResponse->json('data'));
        $recordRow = $records->firstWhere('id', $resignationId);
        $this->assertNotNull($recordRow);
        $this->assertGreaterThanOrEqual(40000.00, $recordRow['total_deductions']);
    }

    /**
     * Test that LOA already deducted in regular payroll (payroll_conpt) is not double-deducted in settlement.
     */
    public function test_already_deducted_loa_in_payroll_is_excluded_from_exit_settlement()
    {
        $user = DB::table('users')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in DB.');
        }

        $staffId = DB::table('tblper')->insertGetId([
            'UserID'            => $user->id,
            'fileNo'            => 'TEST-LOA-002',
            'surname'           => 'SMITH',
            'first_name'        => 'JOHN',
            'office_shift'      => 2,
            'staff_status'      => 1,
            'status_value'      => 'active',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $staffId],
            [
                'basic_salary'      => 310000.00,
                'pen_act'           => 0,
                'created_at'        => now(),
            ]
        );

        // LOA in July 2026
        DB::table('leave_of_absent')->insert([
            'staffId'         => $staffId,
            'start_date'      => '2026-07-10',
            'end_date'        => '2026-07-12', // 3 days
            'reason_of_leave' => 'July leave already deducted',
            'status'          => 2,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // Simulate that July 2026 regular payroll already deducted this LOA
        DB::table('payroll_conpt')->insert([
            'payroll_run_id'              => 1,
            'month'                       => 7,
            'year'                        => 2026,
            'staffID'                     => $staffId,
            'basic'                       => 310000.00,
            'gross_pay'                   => 310000.00,
            'leave_of_absence_deduction'  => 30000.00,
            'total_deductions'            => 30000.00,
            'net_pay'                     => 280000.00,
            'created_at'                  => now(),
        ]);

        // Resignation in August 2026
        $resignationId = DB::table('resignation_requests')->insertGetId([
            'staff_id'         => $staffId,
            'reason'           => 'New opportunity',
            'resignation_date' => '2026-08-01',
            'status'           => 1,
            'hod_status'       => 1,
            'admin_status'     => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $controller = new \App\Http\Controllers\Api\ResignationApiController();
        $settlement = $controller->computeDetailedSettlement($resignationId);

        $itemized = collect($settlement['deductions']['itemized_deductions']);
        $loaItem = $itemized->firstWhere('name', 'Leave of Absence (Unpaid Days)');

        // Because July LOA was already deducted on payroll_conpt, exit settlement should be 0.00 (Nil)
        $this->assertEquals(0.00, $loaItem['amount']);
        $this->assertEquals('Nil', $loaItem['note']);
        $this->assertEquals(0, $settlement['leave_of_absence']['total_days']);
    }

    /**
     * Test office shift 1 (Mon-Fri): weekend days on leave are excluded from deduction.
     */
    public function test_office_shift_weekends_excluded()
    {
        $user = DB::table('users')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in DB.');
        }

        // 2026-08-07 is Friday, 2026-08-08 is Saturday, 2026-08-09 is Sunday, 2026-08-10 is Monday (4 calendar days, but only 2 weekdays)
        $staffId = DB::table('tblper')->insertGetId([
            'UserID'            => $user->id,
            'fileNo'            => 'TEST-LOA-003',
            'surname'           => 'OFFICE',
            'first_name'        => 'WORKER',
            'office_shift'      => 1, // Standard office shift (Mon-Fri)
            'staff_status'      => 1,
            'status_value'      => 'active',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $staffId],
            [
                'basic_salary'      => 310000.00,
                'pen_act'           => 0,
                'created_at'        => now(),
            ]
        );

        DB::table('leave_of_absent')->insert([
            'staffId'         => $staffId,
            'start_date'      => '2026-08-07',
            'end_date'        => '2026-08-10', // Fri, Sat, Sun, Mon -> only Fri & Mon (2 working days)
            'reason_of_leave' => 'Weekend spanning leave',
            'status'          => 2,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $resignationId = DB::table('resignation_requests')->insertGetId([
            'staff_id'         => $staffId,
            'reason'           => 'Career change',
            'resignation_date' => '2026-08-01',
            'status'           => 1,
            'hod_status'       => 1,
            'admin_status'     => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $controller = new \App\Http\Controllers\Api\ResignationApiController();
        $settlement = $controller->computeDetailedSettlement($resignationId);

        $itemized = collect($settlement['deductions']['itemized_deductions']);
        $loaItem = $itemized->firstWhere('name', 'Leave of Absence (Unpaid Days)');

        // 2 weekdays at ₦10,000/day = ₦20,000.00
        $this->assertEquals(2, $settlement['leave_of_absence']['total_days']);
        $this->assertEquals(20000.00, $loaItem['amount']);
        $this->assertStringContainsString('2 unpaid day(s)', $loaItem['note']);
    }

    /**
     * Test that all deduction categories are dynamically queried from database,
     * inactive/zero balances are ignored, and IOU subtracts previously payroll-deducted amounts.
     */
    public function test_all_deductions_query_database_and_prevent_double_deduction_or_short_payment()
    {
        $user = DB::table('users')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in DB.');
        }

        $staffId = DB::table('tblper')->insertGetId([
            'UserID'            => $user->id,
            'fileNo'            => 'TEST-DEDUCT-003',
            'surname'           => 'AUDIT',
            'first_name'        => 'EXPERT',
            'office_shift'      => 1,
            'staff_status'      => 1,
            'status_value'      => 'active',
            'appointment_date'  => '2025-01-01',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $staffId],
            [
                'basic_salary'        => 150000.00,
                'housing_allowance'   => 50000.00,
                'transport_allowance' => 50000.00,
                'medical_allowance'   => 25000.00,
                'utility_allowance'   => 15000.00,
                'meal_allowance'      => 10000.00,
                'pen_act'             => 0,
                'created_at'          => now(),
            ]
        );

        // 1. Medical loan: ₦15,000 active remaining
        DB::table('medical_loan_deduction_setups')->insert([
            'staffId'           => $staffId,
            'loan_amount'       => 50000.00,
            'duration_months'   => 5,
            'monthly_deduction' => 10000.00,
            'balance_remaining' => 15000.00,
            'is_active'         => 1,
            'created_at'        => now(),
        ]);

        // 2. Coop loan: ₦25,000 active remaining
        DB::table('coop_loan_deduction_setups')->insert([
            'staffId'           => $staffId,
            'loan_amount'       => 100000.00,
            'duration_months'   => 10,
            'monthly_deduction' => 10000.00,
            'balance_remaining' => 25000.00,
            'is_active'         => 1,
            'created_at'        => now(),
        ]);

        // Inactive coop loan: ₦0 remaining (must NOT be counted)
        DB::table('coop_loan_deduction_setups')->insert([
            'staffId'           => $staffId,
            'loan_amount'       => 60000.00,
            'duration_months'   => 6,
            'monthly_deduction' => 10000.00,
            'balance_remaining' => 0.00,
            'is_active'         => 0,
            'created_at'        => now(),
        ]);

        // 3. Coop Asset Finance: ₦12,000 active remaining
        DB::table('coop_asset_finance_deduction_setups')->insert([
            'staffId'           => $staffId,
            'total_amount'      => 36000.00,
            'duration_months'   => 3,
            'monthly_deduction' => 12000.00,
            'balance_remaining' => 12000.00,
            'is_active'         => 1,
            'created_at'        => now(),
        ]);

        // 4. Surcharge: ₦8,000 active remaining
        DB::table('surcharge_deduction_setups')->insert([
            'staffId'           => $staffId,
            'total_amount'      => 16000.00,
            'duration_months'   => 2,
            'monthly_deduction' => 8000.00,
            'balance_remaining' => 8000.00,
            'is_active'         => 1,
            'created_at'        => now(),
        ]);

        // 5. Absence Penalty: ₦5,000 active remaining
        DB::table('absence_penalty_deduction_setups')->insert([
            'staffId'           => $staffId,
            'total_amount'      => 15000.00,
            'duration_months'   => 3,
            'monthly_deduction' => 5000.00,
            'balance_remaining' => 5000.00,
            'is_active'         => 1,
            'created_at'        => now(),
        ]);

        // 6. Regular Loan: ₦30,000 active remaining
        DB::table('loan_deduction_setups')->insert([
            'staffId'           => $staffId,
            'loan_amount'       => 60000.00,
            'duration_months'   => 6,
            'monthly_deduction' => 10000.00,
            'balance_remaining' => 30000.00,
            'is_active'         => 1,
            'created_at'        => now(),
        ]);

        // 7. Other Deductions: ₦7,500 active remaining
        DB::table('other_deduction_setups')->insert([
            'staffId'           => $staffId,
            'total_amount'      => 15000.00,
            'duration_months'   => 2,
            'monthly_deduction' => 7500.00,
            'balance_remaining' => 7500.00,
            'is_active'         => 1,
            'created_at'        => now(),
        ]);

        // 8. IOU: Disbursed ₦50,000 total, but ₦20,000 already deducted on payroll_conpt
        // Net un-deducted IOU must be exactly ₦30,000.00 (NOT ₦50,000.00!)
        DB::table('iou_records')->insert([
            'staff_id'   => $staffId,
            'amount'     => 50000.00,
            'reason'     => 'Emergency expense',
            'status'     => 1,
            'created_at' => now(),
        ]);

        DB::table('payroll_conpt')->insert([
            'staffID'          => $staffId,
            'month'            => '07',
            'year'             => '2026',
            'iou'              => 20000.00,
            'total_deductions' => 20000.00,
            'net_pay'          => 280000.00,
            'created_at'       => now(),
        ]);

        // Create approved resignation
        $resignationId = DB::table('resignation_requests')->insertGetId([
            'staff_id'         => $staffId,
            'reason'           => 'Personal',
            'resignation_date' => '2026-08-01',
            'status'           => 1,
            'hod_status'       => 1,
            'admin_status'     => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $controller = new \App\Http\Controllers\Api\ResignationApiController();
        $settlement = $controller->computeDetailedSettlement($resignationId);

        $itemized = collect($settlement['deductions']['itemized_deductions']);

        // Verify each deduction queried from database
        $this->assertEquals(15000.00, $itemized->firstWhere('name', 'Medical Loan')['amount']);
        $this->assertEquals(25000.00, $itemized->firstWhere('name', 'Cooperative Loan')['amount']);
        $this->assertEquals(12000.00, $itemized->firstWhere('name', 'Coop. Asset Financing')['amount']);
        $this->assertEquals(8000.00,  $itemized->firstWhere('name', 'Surcharges / Penalties')['amount']);
        $this->assertEquals(5000.00,  $itemized->firstWhere('name', 'Absence Penalty')['amount']);
        $this->assertEquals(30000.00, $itemized->firstWhere('name', 'Regular Loan Repayment')['amount']);
        $this->assertEquals(7500.00,  $itemized->firstWhere('name', 'Other Deductions')['amount']);

        // Verify IOU prevented double-deduction (50,000 - 20,000 = 30,000)
        $this->assertEquals(30000.00, $itemized->firstWhere('name', 'IOU Repayment')['amount']);

        // Verify total sum of these specific active liabilities:
        // 15000 + 25000 + 12000 + 8000 + 5000 + 30000 + 7500 + 30000 = 132,500 (+ notice PAYE/Pension if any)
        $this->assertGreaterThanOrEqual(132500.00, $settlement['deductions']['total_deductions']);
    }

    /**
     * Test that an early month resignation (Day 1-10, e.g. August 4) calculates
     * Full August Salary (31 days) + Prorated September Exit Days (4 days out of 30).
     */
    public function test_early_month_resignation_calculates_full_start_month_plus_exit_month_days()
    {
        $user = DB::table('users')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in DB.');
        }

        $staffId = DB::table('tblper')->insertGetId([
            'UserID'            => $user->id,
            'fileNo'            => 'TEST-EARLY-001',
            'surname'           => 'JOHNSON',
            'first_name'        => 'MARY',
            'office_shift'      => 1,
            'staff_status'      => 1,
            'status_value'      => 'active',
            'appointment_date'  => '2024-01-01',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // Monthly gross = ₦300,000
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $staffId],
            [
                'basic_salary'        => 150000.00,
                'housing_allowance'   => 50000.00,
                'transport_allowance' => 40000.00,
                'medical_allowance'   => 20000.00,
                'utility_allowance'   => 20000.00,
                'meal_allowance'      => 20000.00,
                'pen_act'             => 0,
                'created_at'          => now(),
            ]
        );

        // Staff resigns on August 4th, 2026 (1-month notice exits on September 4th, 2026)
        $resignationId = DB::table('resignation_requests')->insertGetId([
            'staff_id'         => $staffId,
            'reason'           => 'Career growth',
            'resignation_date' => '2026-08-04',
            'status'           => 1,
            'hod_status'       => 1,
            'admin_status'     => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $controller = new \App\Http\Controllers\Api\ResignationApiController();
        $settlement = $controller->computeDetailedSettlement($resignationId);

        $this->assertEquals('early_month', $settlement['timeline']['resignation_rule']);
        $this->assertEquals('2026-09-04', $settlement['timeline']['exit_date']);

        $breakdown = $settlement['notice_earnings']['breakdown'];
        $this->assertCount(2, $breakdown);

        // Month 1: Full August (31 days) = ₦300,000.00
        $this->assertEquals('August 2026', $breakdown[0]['month_name']);
        $this->assertEquals(31, $breakdown[0]['days_worked']);
        $this->assertEquals(300000.00, $breakdown[0]['earned_salary']);
        $this->assertFalse($breakdown[0]['is_paid_via_payroll']);

        // Month 2: September 4 days (4/30 of ₦300,000 = ₦40,000.00)
        $this->assertEquals('September 2026', $breakdown[1]['month_name']);
        $this->assertEquals(4, $breakdown[1]['days_worked']);
        $this->assertEquals(40000.00, $breakdown[1]['earned_salary']);
        $this->assertFalse($breakdown[1]['is_paid_via_payroll']);

        // Total notice salary = ₦300,000 + ₦40,000 = ₦340,000.00
        $this->assertEquals(340000.00, $settlement['notice_earnings']['total_notice_salary']);
    }

    /**
     * Test Audit reject -> HR/Finance review & forward back to Audit -> Audit approve cycle.
     */
    public function test_audit_query_and_resubmission_by_hr_or_finance_flow()
    {
        $user = DB::table('users')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in DB.');
        }

        DB::table('assign_user_role')->updateOrInsert(
            ['userID' => $user->id, 'roleID' => 1],
            ['created_at' => now()]
        );

        $staffId = DB::table('tblper')->insertGetId([
            'UserID'            => $user->id,
            'fileNo'            => 'TEST-AUDIT-RESUBMIT-001',
            'surname'           => 'AUDIT',
            'first_name'        => 'FLOW',
            'staff_status'      => 1,
            'status_value'      => 'active',
            'appointment_date'  => '2024-01-01',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $resignationId = DB::table('resignation_requests')->insertGetId([
            'staff_id'         => $staffId,
            'reason'           => 'Relocation',
            'resignation_date' => '2026-08-10',
            'status'           => 1,
            'hod_status'       => 1,
            'admin_status'     => 1, // HR Approved
            'audit_status'     => 0, // Pending Audit
            'finance_status'   => 0,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // Step 1: Audit Head queries the settlement
        $auditHeaders = [
            'X-User-Id'   => $user->id,
            'X-User-Role' => 'Head of Audit',
        ];
        $rejectResp = $this->postJson("/api/nextjs/payroll/resignations/audit-reject/{$resignationId}", [
            'remarks' => 'Please verify retention months deduction before audit approval.',
        ], $auditHeaders);

        $rejectResp->assertStatus(200);
        $rejectResp->assertJson(['status' => 'success']);

        $queriedRecord = DB::table('resignation_requests')->where('id', $resignationId)->first();
        $this->assertEquals(2, $queriedRecord->audit_status);
        $this->assertStringContainsString('Please verify retention months deduction', $queriedRecord->audit_remarks);

        // Step 2: HR Head or Finance Head reviews query, corrects it, and forwards back to Audit
        $financeHeaders = [
            'X-User-Id'   => $user->id,
            'X-User-Role' => 'Head of Finance',
        ];
        $resubmitResp = $this->postJson("/api/nextjs/payroll/resignations/resubmit-audit/{$resignationId}", [
            'remarks' => 'Retention months have been corrected to 12 months. Forwarding back for approval.',
        ], $financeHeaders);

        $resubmitResp->assertStatus(200);
        $resubmitResp->assertJson(['status' => 'success']);

        $resubmittedRecord = DB::table('resignation_requests')->where('id', $resignationId)->first();
        // audit_status must be reset to 0 so Audit Head can approve it
        $this->assertEquals(0, $resubmittedRecord->audit_status);
        $this->assertStringContainsString('Retention months have been corrected to 12 months', $resubmittedRecord->audit_remarks);
        $this->assertStringContainsString('Corrected & Forwarded to Audit', $resubmittedRecord->audit_remarks);

        // Step 3: Audit Head can now approve the settlement for payment
        $approveResp = $this->postJson("/api/nextjs/payroll/resignations/audit-approve/{$resignationId}", [
            'remarks' => 'Verified and approved for payment disbursement.',
        ], $auditHeaders);

        $approveResp->assertStatus(200);
        $approveResp->assertJson(['status' => 'success']);

        $approvedRecord = DB::table('resignation_requests')->where('id', $resignationId)->first();
        $this->assertEquals(1, $approvedRecord->audit_status);
        $this->assertNotNull($approvedRecord->audit_date);
    }

    /**
     * Test editing medical loan balance directly during exit settlement.
     */
    public function test_update_medical_loan_balance_during_exit_settlement()
    {
        $user = DB::table('users')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in DB.');
        }

        DB::table('assign_user_role')->updateOrInsert(
            ['userID' => $user->id, 'roleID' => 1],
            ['created_at' => now()]
        );

        $staffId = DB::table('tblper')->insertGetId([
            'UserID'            => $user->id,
            'fileNo'            => 'TEST-MED-LOAN-001',
            'surname'           => 'MED',
            'first_name'        => 'LOAN',
            'staff_status'      => 1,
            'status_value'      => 'active',
            'appointment_date'  => '2024-01-01',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $staffId],
            [
                'basic_salary'        => 100000.00,
                'housing_allowance'   => 50000.00,
                'transport_allowance' => 30000.00,
                'medical_allowance'   => 20000.00,
                'utility_allowance'   => 20000.00,
                'meal_allowance'      => 20000.00,
                'pen_act'             => 0,
                'created_at'          => now(),
            ]
        );

        // Initial active medical loan of ₦60,000
        DB::table('medical_loan_deduction_setups')->updateOrInsert(
            ['staffId' => $staffId],
            [
                'loan_amount'       => 60000.00,
                'balance_remaining' => 60000.00,
                'monthly_deduction' => 10000.00,
                'duration_months'   => 6,
                'start_month'       => '2026-01',
                'end_month'         => '2026-06',
                'is_active'         => 1,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]
        );

        $resignationId = DB::table('resignation_requests')->insertGetId([
            'staff_id'         => $staffId,
            'reason'           => 'Career change',
            'resignation_date' => '2026-08-10',
            'status'           => 1,
            'hod_status'       => 1,
            'admin_status'     => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $controller = new \App\Http\Controllers\Api\ResignationApiController();
        $settlementBefore = $controller->computeDetailedSettlement($resignationId);

        // Verify that medical loan is captured as ₦60,000 initially
        $medLoanItem = collect($settlementBefore['deductions']['itemized_deductions'])->firstWhere('name', 'Medical Loan');
        $this->assertNotNull($medLoanItem);
        $this->assertEquals(60000.00, $medLoanItem['amount']);

        // HR Head or Finance Head edits medical loan balance down to ₦25,000
        $headers = [
            'X-User-Id'   => $user->id,
            'X-User-Role' => 'Head of HR',
        ];

        $resp = $this->postJson('/api/nextjs/payroll/resignations/update-medical-loan-balance', [
            'staff_id'       => $staffId,
            'balance'        => 25000.00,
            'reason'         => 'Reconciled with pharmacy bills and cashier payment',
            'resignation_id' => $resignationId,
        ], $headers);

        $resp->assertStatus(200);
        $resp->assertJson(['status' => 'success']);

        // Assert database setup was updated
        $updatedSetup = DB::table('medical_loan_deduction_setups')->where('staffId', $staffId)->first();
        $this->assertEquals(25000.00, (float)$updatedSetup->balance_remaining);
        $this->assertEquals(1, (int)$updatedSetup->is_active);

        // Assert recomputed settlement reflects ₦25,000
        $settlementData = $resp->json('data.settlement');
        $medLoanUpdated = collect($settlementData['deductions']['itemized_deductions'])->firstWhere('name', 'Medical Loan');
        $this->assertEquals(25000.00, $medLoanUpdated['amount']);

        // Also test clearing medical loan to ₦0.00
        $respZero = $this->postJson('/api/nextjs/payroll/resignations/update-medical-loan-balance', [
            'staff_id'       => $staffId,
            'balance'        => 0.00,
            'reason'         => 'Full cash settlement cleared at cashier',
            'resignation_id' => $resignationId,
        ], $headers);

        $respZero->assertStatus(200);
        $clearedSetup = DB::table('medical_loan_deduction_setups')->where('staffId', $staffId)->first();
        $this->assertEquals(0.00, (float)$clearedSetup->balance_remaining);
        $this->assertEquals(0, (int)$clearedSetup->is_active);

        $settlementCleared = $respZero->json('data.settlement');
        $medLoanCleared = collect($settlementCleared['deductions']['itemized_deductions'])->firstWhere('name', 'Medical Loan');
        $this->assertEquals(0.00, $medLoanCleared['amount']);
    }

    /**
     * Test updating and reconciling staff cooperative loan balance during exit settlement.
     */
    public function test_update_coop_loan_balance_during_exit_settlement()
    {
        $user = DB::table('users')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in DB.');
        }

        $staffId = DB::table('tblper')->insertGetId([
            'UserID'            => $user->id,
            'fileNo'            => 'TEST-COOP-LOAN-001',
            'surname'           => 'COOP',
            'first_name'        => 'BORROWER',
            'staff_status'      => 1,
            'status_value'      => 'active',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $staffId],
            [
                'basic_salary'      => 200000.00,
                'pen_act'           => 0,
                'created_at'        => now(),
            ]
        );

        if (\Illuminate\Support\Facades\Schema::hasTable('coop_loan_deduction_setups')) {
            DB::table('coop_loan_deduction_setups')->insert([
                'staffId'           => $staffId,
                'loan_amount'       => 80000.00,
                'balance_remaining' => 80000.00,
                'monthly_deduction' => 20000.00,
                'interest_rate'     => 0.00,
                'duration_months'   => 4,
                'start_month'       => date('Y-m'),
                'end_month'         => date('Y-m', strtotime('+3 months')),
                'is_active'         => 1,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        $resignationId = DB::table('resignation_requests')->insertGetId([
            'staff_id'         => $staffId,
            'reason'           => 'Relocation',
            'resignation_date' => '2026-08-15',
            'status'           => 1,
            'hod_status'       => 1,
            'admin_status'     => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $controller = new \App\Http\Controllers\Api\ResignationApiController();
        $settlementBefore = $controller->computeDetailedSettlement($resignationId);

        // Verify that cooperative loan is captured as ₦80,000 initially
        $coopLoanItem = collect($settlementBefore['deductions']['itemized_deductions'])->firstWhere('name', 'Cooperative Loan');
        $this->assertNotNull($coopLoanItem);
        $this->assertEquals(80000.00, $coopLoanItem['amount']);

        // HR Head or Finance Head edits cooperative loan balance down to ₦35,000
        $headers = [
            'X-User-Id'   => $user->id,
            'X-User-Role' => 'Finance Head',
        ];

        $resp = $this->postJson('/api/nextjs/payroll/resignations/update-coop-loan-balance', [
            'staff_id'       => $staffId,
            'balance'        => 35000.00,
            'reason'         => 'Reconciled with coop passbook and offset against savings',
            'resignation_id' => $resignationId,
        ], $headers);

        $resp->assertStatus(200);
        $resp->assertJson(['status' => 'success']);

        // Assert database setup was updated
        $updatedSetup = DB::table('coop_loan_deduction_setups')->where('staffId', $staffId)->first();
        $this->assertEquals(35000.00, (float)$updatedSetup->balance_remaining);
        $this->assertEquals(1, (int)$updatedSetup->is_active);

        // Assert recomputed settlement reflects ₦35,000
        $settlementData = $resp->json('data.settlement');
        $coopLoanUpdated = collect($settlementData['deductions']['itemized_deductions'])->firstWhere('name', 'Cooperative Loan');
        $this->assertEquals(35000.00, $coopLoanUpdated['amount']);

        // Also test clearing cooperative loan to ₦0.00
        $respZero = $this->postJson('/api/nextjs/payroll/resignations/update-coop-loan-balance', [
            'staff_id'       => $staffId,
            'balance'        => 0.00,
            'reason'         => 'Full settlement approved by cooperative executive',
            'resignation_id' => $resignationId,
        ], $headers);

        $respZero->assertStatus(200);
        $clearedSetup = DB::table('coop_loan_deduction_setups')->where('staffId', $staffId)->first();
        $this->assertEquals(0.00, (float)$clearedSetup->balance_remaining);
        $this->assertEquals(0, (int)$clearedSetup->is_active);

        $settlementCleared = $respZero->json('data.settlement');
        $coopLoanCleared = collect($settlementCleared['deductions']['itemized_deductions'])->firstWhere('name', 'Cooperative Loan');
        $this->assertEquals(0.00, $coopLoanCleared['amount']);
    }

    /**
     * Test that Finance Head is authorized to edit and update deducted retention months.
     */
    public function test_finance_head_can_update_retention_months()
    {
        $user = DB::table('users')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in DB.');
        }

        $staffId = DB::table('tblper')->insertGetId([
            'UserID'            => $user->id,
            'fileNo'            => 'TEST-RETEN-FIN-001',
            'surname'           => 'FINANCE',
            'first_name'        => 'AUTHORITY',
            'staff_status'      => 1,
            'status_value'      => 'active',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        DB::table('first_salary_structure')->updateOrInsert(
            ['staffId' => $staffId],
            [
                'basic_salary'     => 150000.00,
                'num_rente_months' => 5,
                'reten_act'        => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]
        );

        $resignationId = DB::table('resignation_requests')->insertGetId([
            'staff_id'         => $staffId,
            'reason'           => 'Exit plan',
            'resignation_date' => '2026-08-20',
            'status'           => 1,
            'hod_status'       => 1,
            'admin_status'     => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $headers = [
            'X-User-Id'   => $user->id,
            'X-User-Role' => 'Finance Head',
        ];

        // 1. Test update retention months on Resignation Settlement endpoint
        $resp = $this->postJson('/api/nextjs/payroll/resignation-settlement/update-retention-months', [
            'staff_id'         => $staffId,
            'num_rente_months' => 12,
            'resignation_id'   => $resignationId,
        ], $headers);

        $resp->assertStatus(200);
        $resp->assertJson(['status' => 'success']);

        $updatedFss = DB::table('first_salary_structure')->where('staffId', $staffId)->first();
        $this->assertEquals(12, (int)$updatedFss->num_rente_months);

        // 2. Test update retention months on Retention Activation endpoint
        $respActivation = $this->postJson('/api/nextjs/payroll/retention-activation/update-months', [
            'staff_id'         => $staffId,
            'num_rente_months' => 15,
        ], $headers);

        $respActivation->assertStatus(200);
        $respActivation->assertJson(['status' => 'success']);

        $updatedFss2 = DB::table('first_salary_structure')->where('staffId', $staffId)->first();
        $this->assertEquals(15, (int)$updatedFss2->num_rente_months);
    }
}



