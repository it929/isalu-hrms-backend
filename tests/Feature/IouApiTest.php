<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IouApiTest extends TestCase
{
    use DatabaseTransactions;

    private function getHeaders($userId)
    {
        return [
            'X-User-Id' => $userId
        ];
    }

    /**
     * Test retrieving the staff list with gross salary and max allowed IOU amount.
     */
    public function test_get_staff_list_with_salary_and_limit()
    {
        $user = DB::table('users')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in DB.');
        }

        // Fetch a staff record or mock one
        $staff = DB::table('tblper')->first();
        if (!$staff) {
            $this->markTestSkipped('No staff record found in tblper.');
        }

        // Clean up any existing IOU records to avoid state pollution
        DB::table('iou_records')->where('staff_id', $staff->ID)->delete();

        // Establish a mock salary structure for the staff
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $staff->ID],
            [
                'basic_salary' => 100000.00,
                'housing_allowance' => 40000.00,
                'transport_allowance' => 20000.00,
                'medical_allowance' => 10000.00,
                'utility_allowance' => 15000.00,
                'meal_allowance' => 15000.00,
                'can_take_iou' => 1,
                'max_iou_amount' => 0.00,
            ]
        );

        $headers = $this->getHeaders($user->id);
        $response = $this->getJson('/api/nextjs/payroll/ious/staff', $headers);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success'
            ]);

        $data = $response->json('data');
        $this->assertIsArray($data);

        // Find the staff we configured
        $matching = collect($data)->firstWhere('id', $staff->ID);
        $this->assertNotNull($matching);
        $this->assertEquals(200000.00, $matching['salary']); // 100k + 40k + 20k + 10k + 15k + 15k
        $this->assertEquals(140000.00, $matching['max_iou']); // 70% of 200k
    }

    /**
     * Test full IOU CRUD flow, approval workflow, and 70% salary limit validation check.
     */
    public function test_iou_crud_and_approvals_workflow()
    {
        // Find or seed a super admin user role
        $user = DB::table('users')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in DB.');
        }

        // Force user to be Super Admin (roleID = 1)
        DB::table('assign_user_role')->updateOrInsert(
            ['userID' => $user->id, 'roleID' => 1],
            ['created_at' => now()]
        );

        // Fetch a staff record
        $staff = DB::table('tblper')->first();
        if (!$staff) {
            $this->markTestSkipped('No staff record found in tblper.');
        }

        // Clean up any existing IOU records to avoid state pollution
        DB::table('iou_records')->where('staff_id', $staff->ID)->delete();

        // Establish a mock salary structure: gross salary = 160,000. 70% limit = 112,000
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $staff->ID],
            [
                'basic_salary' => 80000.00,
                'housing_allowance' => 30000.00,
                'transport_allowance' => 20000.00,
                'medical_allowance' => 10000.00,
                'utility_allowance' => 10000.00,
                'meal_allowance' => 10000.00,
                'can_take_iou' => 1,
                'max_iou_amount' => 0.00,
            ]
        );

        $headers = $this->getHeaders($user->id);
        $testDate = '2034-01-15';

        // 1. Submit IOU exceeding the 70% limit (e.g. 120,000 > 112,000) -> should fail
        $payloadExceeding = [
            'staff_id'       => $staff->ID,
            'amount'         => 120000.00,
            'reason'         => 'Test exceeding limit reason',
            'iou_date'       => $testDate,
            'repayment_date' => '2034-02-15',
        ];

        $responseExceeding = $this->postJson('/api/nextjs/payroll/ious', $payloadExceeding, $headers);
        $responseExceeding->assertStatus(422)
            ->assertJson([
                'status' => 'error'
            ]);
        $this->assertStringContainsString('exceeds the maximum allowed limit of 70%', $responseExceeding->json('message'));

        // 2. Submit IOU within the 70% limit (e.g. 70,000 <= 112,000) -> should succeed
        $payloadValid = [
            'staff_id'       => $staff->ID,
            'amount'         => 70000.00,
            'reason'         => 'Test valid IOU application reason',
            'iou_date'       => $testDate,
            'repayment_date' => '2034-02-15',
        ];

        $responseValid = $this->postJson('/api/nextjs/payroll/ious', $payloadValid, $headers);
        $responseValid->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'IOU application submitted successfully.'
            ]);

        $iouId = $responseValid->json('id');
        $this->assertGreaterThan(0, $iouId);

        // Verify inserted correctly
        $inserted = DB::table('iou_records')->where('id', $iouId)->first();
        $this->assertNotNull($inserted);
        $this->assertEquals(70000.00, $inserted->amount);
        $this->assertEquals(0, $inserted->status);
        $this->assertEquals(0, $inserted->hod_status);
        $this->assertEquals(0, $inserted->finance_status);
        $this->assertEquals(0, $inserted->admin_status);
        $this->assertEquals(0, $inserted->audit_status);

        // 3. Update IOU application (still pending)
        $payloadUpdate = [
            'id'             => $iouId,
            'staff_id'       => $staff->ID,
            'amount'         => 60000.00, // modify amount
            'reason'         => 'Updated reason',
            'iou_date'       => $testDate,
            'repayment_date' => '2034-02-15',
        ];

        $responseUpdate = $this->postJson('/api/nextjs/payroll/ious', $payloadUpdate, $headers);
        $responseUpdate->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'IOU application updated successfully.'
            ]);

        $updated = DB::table('iou_records')->where('id', $iouId)->first();
        $this->assertEquals(60000.00, $updated->amount);
        $this->assertEquals('Updated reason', $updated->reason);

        // 4. Test HOD Approval Tier
        $responseHod = $this->getJson("/api/nextjs/payroll/ious/hod-approve/{$iouId}?remarks=HOD+approved+this", $headers);
        $responseHod->assertStatus(200)
            ->assertJson([
                'status' => 'success'
            ]);

        $afterHod = DB::table('iou_records')->where('id', $iouId)->first();
        $this->assertEquals(1, $afterHod->hod_status);
        $this->assertEquals(0, $afterHod->admin_status);
        $this->assertEquals(0, $afterHod->status);

        // Assert approval log created
        $logHod = DB::table('iou_approvals')->where('iou_id', $iouId)->where('level', 'HOD')->first();
        $this->assertNotNull($logHod);
        $this->assertEquals(1, $logHod->status);

        // 5. Test HR Approval Tier
        $responseHr = $this->getJson("/api/nextjs/payroll/ious/hr-approve/{$iouId}?remarks=HR+recommended", $headers);
        $responseHr->assertStatus(200)
            ->assertJson([
                'status' => 'success'
            ]);

        $afterHr = DB::table('iou_records')->where('id', $iouId)->first();
        $this->assertEquals(1, $afterHr->admin_status);
        $this->assertEquals(0, $afterHr->audit_status);
        $this->assertEquals(0, $afterHr->finance_status);
        $this->assertEquals(0, $afterHr->status);

        // Assert approval log created
        $logHr = DB::table('iou_approvals')->where('iou_id', $iouId)->where('level', 'HR')->first();
        $this->assertNotNull($logHr);
        $this->assertEquals(1, $logHr->status);

        // 6. Test Finance cannot approve before Audit
        $responseFinanceBeforeAudit = $this->getJson("/api/nextjs/payroll/ious/finance-approve/{$iouId}?remarks=Fully+approved", $headers);
        $responseFinanceBeforeAudit->assertStatus(400);

        // 7. Test Audit Approval Tier
        $responseAudit = $this->getJson("/api/nextjs/payroll/ious/audit-approve/{$iouId}?remarks=Audit+recommended", $headers);
        $responseAudit->assertStatus(200)
            ->assertJson([
                'status' => 'success'
            ]);

        $afterAudit = DB::table('iou_records')->where('id', $iouId)->first();
        $this->assertEquals(1, $afterAudit->audit_status);
        $this->assertEquals(0, $afterAudit->finance_status);
        $this->assertEquals(0, $afterAudit->status);

        // Assert approval log created
        $logAudit = DB::table('iou_approvals')->where('iou_id', $iouId)->where('level', 'Audit')->first();
        $this->assertNotNull($logAudit);
        $this->assertEquals(1, $logAudit->status);

        // 8. Test Finance Final Approval Tier (Marked as Paid)
        $responseFinance = $this->getJson("/api/nextjs/payroll/ious/finance-approve/{$iouId}?remarks=Fully+approved", $headers);
        $responseFinance->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'IOU application marked as paid.'
            ]);

        $afterFinance = DB::table('iou_records')->where('id', $iouId)->first();
        $this->assertEquals(1, $afterFinance->finance_status);
        $this->assertEquals(1, $afterFinance->status); // Overall status becomes 1 (paid)

        // Assert user activity log captured for approvals with staff name
        $actLog = DB::table('user_activity_logs')
            ->where('user_id', $user->id)
            ->where('activity_type', 'approval')
            ->where('action', 'like', 'Finance Approved & Paid IOU Application%')
            ->first();
        $this->assertNotNull($actLog);

        // 9. Delete the record
        $responseDelete = $this->deleteJson("/api/nextjs/payroll/ious/{$iouId}", [], $headers);
        $responseDelete->assertStatus(200)
            ->assertJson([
                'status' => 'success'
            ]);

        $this->assertDatabaseMissing('iou_records', ['id' => $iouId]);
        $this->assertDatabaseMissing('iou_approvals', ['iou_id' => $iouId]);
    }

    /**
     * Test retrieving cumulative used limit details for a staff member.
     */
    public function test_get_used_limit_endpoint()
    {
        $user = DB::table('users')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in DB.');
        }

        $staff = DB::table('tblper')->first();
        if (!$staff) {
            $this->markTestSkipped('No staff record found in tblper.');
        }

        // Clean up any existing IOU records to avoid state pollution
        DB::table('iou_records')->where('staff_id', $staff->ID)->delete();

        // Gross salary = 200,000 (50% limit = 100,000)
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $staff->ID],
            [
                'basic_salary' => 100000.00,
                'housing_allowance' => 40000.00,
                'transport_allowance' => 20000.00,
                'medical_allowance' => 10000.00,
                'utility_allowance' => 15000.00,
                'meal_allowance' => 15000.00,
                'can_take_iou' => 1,
                'max_iou_amount' => 0.00,
            ]
        );

        $date = '2035-05-15';
        $headers = $this->getHeaders($user->id);

        // Seed some IOUs for this month
        // 1. Pending IOU of 30,000
        $iouId1 = DB::table('iou_records')->insertGetId([
            'staff_id' => $staff->ID,
            'amount' => 30000.00,
            'reason' => 'First pending request',
            'iou_date' => '2035-05-10',
            'status' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 2. Approved IOU of 25,000
        $iouId2 = DB::table('iou_records')->insertGetId([
            'staff_id' => $staff->ID,
            'amount' => 25000.00,
            'reason' => 'Second approved request',
            'iou_date' => '2035-05-12',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 3. Rejected IOU of 40,000 (should be excluded)
        $iouId3 = DB::table('iou_records')->insertGetId([
            'staff_id' => $staff->ID,
            'amount' => 40000.00,
            'reason' => 'Third rejected request',
            'iou_date' => '2035-05-14',
            'status' => 2,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->getJson("/api/nextjs/payroll/ious/used-limit?staff_id={$staff->ID}&date={$date}", $headers);

        try {
            $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'data' => [
                        'gross_salary' => 200000.00,
                        'max_limit' => 140000.00,
                        'used_amount' => 55000.00, // 30k + 25k (40k rejected is excluded)
                        'remaining_limit' => 85000.00,
                        'month_name' => 'May 2035',
                    ]
                ]);
        } finally {
            // Clean up seeded records for this test specifically
            DB::table('iou_records')->whereIn('id', [$iouId1, $iouId2, $iouId3])->delete();
        }
    }

    public function test_cumulative_limit_check_across_multiple_applications()
    {
        $user = DB::table('users')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in DB.');
        }

        DB::table('assign_user_role')->updateOrInsert(
            ['userID' => $user->id, 'roleID' => 1],
            ['created_at' => now()]
        );

        $staff = DB::table('tblper')->first();
        if (!$staff) {
            $this->markTestSkipped('No staff record found in tblper.');
        }

        // Clean up any existing IOU records to avoid state pollution
        DB::table('iou_records')->where('staff_id', $staff->ID)->delete();

        // Gross salary = 160,000 (50% limit = 80,000)
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $staff->ID],
            [
                'basic_salary' => 80000.00,
                'housing_allowance' => 30000.00,
                'transport_allowance' => 20000.00,
                'medical_allowance' => 10000.00,
                'utility_allowance' => 10000.00,
                'meal_allowance' => 10000.00,
                'can_take_iou' => 1,
                'max_iou_amount' => 0.00,
            ]
        );

        $headers = $this->getHeaders($user->id);
        $monthStr = '2036-05';
        $createdIds = [];

        try {
            // 1. Submit first IOU of 50,000 (Pending)
            $payload1 = [
                'staff_id' => $staff->ID,
                'amount' => 50000.00,
                'reason' => 'IOU Request #1',
                'iou_date' => "{$monthStr}-01",
            ];
            $res1 = $this->postJson('/api/nextjs/payroll/ious', $payload1, $headers);
            $res1->assertStatus(200);
            if ($res1->json('id')) $createdIds[] = $res1->json('id');

            // 2. Submit second IOU of 50,000 (Pending). Cumulative = 100,000 <= 112,000. Should succeed.
            $payload2 = [
                'staff_id' => $staff->ID,
                'amount' => 50000.00,
                'reason' => 'IOU Request #2',
                'iou_date' => "{$monthStr}-10",
            ];
            $res2 = $this->postJson('/api/nextjs/payroll/ious', $payload2, $headers);
            $res2->assertStatus(200);
            if ($res2->json('id')) $createdIds[] = $res2->json('id');

            // 3. Submit third IOU of 20,000. Cumulative = 120,000 > 112,000. Should fail.
            $payload3 = [
                'staff_id' => $staff->ID,
                'amount' => 20000.00,
                'reason' => 'IOU Request #3 (exceeding)',
                'iou_date' => "{$monthStr}-15",
            ];
            $response = $this->postJson('/api/nextjs/payroll/ious', $payload3, $headers);
            $response->assertStatus(422);
            $this->assertStringContainsString('plus already applied IOUs', $response->json('message'));

            // 4. Submit IOU of 20,000 in a DIFFERENT month (June). Should succeed.
            $payloadDifferentMonth = [
                'staff_id' => $staff->ID,
                'amount' => 20000.00,
                'reason' => 'IOU Request in different month',
                'iou_date' => '2036-06-01',
            ];
            $resDiff = $this->postJson('/api/nextjs/payroll/ious', $payloadDifferentMonth, $headers);
            $resDiff->assertStatus(200);
            if ($resDiff->json('id')) $createdIds[] = $resDiff->json('id');

        } finally {
            // Clean up seeded records for this test specifically
            if (!empty($createdIds)) {
                DB::table('iou_records')->whereIn('id', $createdIds)->delete();
            }
        }
    }

    /**
     * Test that an IOU application is declined if it causes the staff's net pay to reach 0.00 or negative balance.
     */
    public function test_netpay_balance_cannot_be_negative_on_iou_application()
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
            'fileNo' => 'TEST_IOU_NETPAY_001',
            'surname' => 'Test',
            'first_name' => 'NetPay',
            'rank' => 1,
            'staff_status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Salary structure: Gross = 100,000 (50% max limit = 50,000)
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $staffId],
            [
                'basic_salary' => 50000.00,
                'housing_allowance' => 20000.00,
                'transport_allowance' => 10000.00,
                'medical_allowance' => 10000.00,
                'utility_allowance' => 5000.00,
                'meal_allowance' => 5000.00,
                'can_take_iou' => 1,
                'max_iou_amount' => 0.00,
            ]
        );

        // Add a surcharge deduction setup of 80,000 for 2036-07 so available net pay is ~15,000 (100k - 80k - 5k tax)
        $monthStr = '2036-07';
        DB::table('surcharge_deduction_setups')->updateOrInsert(
            ['staffId' => $staffId, 'start_month' => $monthStr],
            [
                'deduction_type' => 'Test Surcharge',
                'total_amount' => 80000.00,
                'duration_months' => 1,
                'monthly_deduction' => 80000.00,
                'balance_remaining' => 80000.00,
                'end_month' => $monthStr,
                'is_active' => 1,
                'updated_at' => now(),
            ]
        );

        $headers = $this->getHeaders($user->id);

        try {
            // Gross = 100k, PAYE Tax = 5k, Surcharge = 80k => Available Net Pay = 15k
            $limitRes = $this->getJson("/api/nextjs/payroll/ious/used-limit?staff_id={$staffId}&date={$monthStr}-01", $headers);
            $limitRes->assertStatus(200);
            $this->assertEquals(15000.00, (float)$limitRes->json('data.available_net_pay'));

            // 1. Applying for 25,000 (which is <= 50% limit of 50,000, BUT > available net pay of 15,000)
            // Should be declined with the exact specified message
            $payloadExceedingNetPay = [
                'staff_id' => $staffId,
                'amount' => 25000.00,
                'reason' => 'Testing IOU exceeding net pay',
                'iou_date' => "{$monthStr}-05",
            ];
            $res1 = $this->postJson('/api/nextjs/payroll/ious', $payloadExceedingNetPay, $headers);
            $res1->assertStatus(422);
            $this->assertStringContainsString('can not be negative', $res1->json('message'));

            // 2. Applying for 15,000 (which would leave 0.00 net pay balance)
            // Should also be declined
            $payloadZeroNetPay = [
                'staff_id' => $staffId,
                'amount' => 15000.00,
                'reason' => 'Testing IOU leaving 0 net pay',
                'iou_date' => "{$monthStr}-05",
            ];
            $res2 = $this->postJson('/api/nextjs/payroll/ious', $payloadZeroNetPay, $headers);
            $res2->assertStatus(422);
            $this->assertStringContainsString('can not be negative', $res2->json('message'));

            // 3. Applying for 10,000 (leaves 5,000 net pay balance > 0)
            // Should succeed
            $payloadValid = [
                'staff_id' => $staffId,
                'amount' => 10000.00,
                'reason' => 'Testing valid IOU within net pay',
                'iou_date' => "{$monthStr}-05",
            ];
            $res3 = $this->postJson('/api/nextjs/payroll/ious', $payloadValid, $headers);
            $res3->assertStatus(200);

        } finally {
            DB::table('iou_records')->where('staff_id', $staffId)->delete();
            DB::table('surcharge_deduction_setups')->where('staffId', $staffId)->delete();
            DB::table('salary_structures')->where('staffId', $staffId)->delete();
            DB::table('tblper')->where('ID', $staffId)->delete();
        }
    }

    /**
     * Test that remarks are compulsory when rejecting an IOU application.
     */
    public function test_iou_rejection_requires_compulsory_remarks()
    {
        $user = DB::table('users')->first();
        if (!$user) {
            $this->markTestSkipped('No user found.');
        }

        DB::table('assign_user_role')->updateOrInsert(
            ['userID' => $user->id, 'roleID' => 1],
            ['created_at' => now()]
        );

        $staffId = DB::table('tblper')->insertGetId([
            'fileNo' => 'REJ-' . time(),
            'surname' => 'Reject',
            'first_name' => 'Test',
            'staff_status' => 1,
            'rank' => 1,
            'departmentID' => 1,
        ]);

        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $staffId],
            [
                'basic_salary' => 100000.00,
                'can_take_iou' => 1,
            ]
        );

        $headers = $this->getHeaders($user->id);

        $iouId = DB::table('iou_records')->insertGetId([
            'staff_id' => $staffId,
            'amount' => 20000.00,
            'reason' => 'Testing rejection remarks requirement',
            'iou_date' => '2026-08-01',
            'status' => 0,
            'hod_status' => 0,
            'admin_status' => 0,
            'audit_status' => 0,
            'finance_status' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1. Attempt HOD rejection without remarks -> should fail with 422
        $resNoRemarks = $this->getJson("/api/nextjs/payroll/ious/hod-reject/{$iouId}", $headers);
        $resNoRemarks->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Rejection remarks are compulsory when rejecting an IOU application.'
            ]);

        // 2. Attempt HOD rejection with empty whitespace remarks -> should fail with 422
        $resEmptyRemarks = $this->getJson("/api/nextjs/payroll/ious/hod-reject/{$iouId}?remarks=%20%20", $headers);
        $resEmptyRemarks->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Rejection remarks are compulsory when rejecting an IOU application.'
            ]);

        // 3. Attempt HOD rejection with valid remarks -> should succeed
        $resWithRemarks = $this->getJson("/api/nextjs/payroll/ious/hod-reject/{$iouId}?remarks=Department+budget+exceeded", $headers);
        $resWithRemarks->assertStatus(200)
            ->assertJson([
                'status' => 'success'
            ]);

        $record = DB::table('iou_records')->where('id', $iouId)->first();
        $this->assertEquals(2, $record->status);
        $this->assertEquals(2, $record->hod_status);
        $this->assertEquals('Department budget exceeded', $record->remarks);
    }
}
