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
        $this->assertEquals(100000.00, $matching['max_iou']); // 50% of 200k
    }

    /**
     * Test full IOU CRUD flow, approval workflow, and 50% salary limit validation check.
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

        // Establish a mock salary structure: gross salary = 160,000. 50% limit = 80,000
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $staff->ID],
            [
                'basic_salary' => 80000.00,
                'housing_allowance' => 30000.00,
                'transport_allowance' => 20000.00,
                'medical_allowance' => 10000.00,
                'utility_allowance' => 10000.00,
                'meal_allowance' => 10000.00,
            ]
        );

        $headers = $this->getHeaders($user->id);

        // 1. Submit IOU exceeding the 50% limit (e.g. 90,000 > 80,000) -> should fail
        $payloadExceeding = [
            'staff_id'       => $staff->ID,
            'amount'         => 90000.00,
            'reason'         => 'Test exceeding limit reason',
            'iou_date'       => date('Y-m-d'),
            'repayment_date' => date('Y-m-d', strtotime('+30 days')),
        ];

        $responseExceeding = $this->postJson('/api/nextjs/payroll/ious', $payloadExceeding, $headers);
        $responseExceeding->assertStatus(422)
            ->assertJson([
                'status' => 'error'
            ]);
        $this->assertStringContainsString('exceeds the maximum allowed limit of 50%', $responseExceeding->json('message'));

        // 2. Submit IOU within the 50% limit (e.g. 50,000 <= 80,000) -> should succeed
        $payloadValid = [
            'staff_id'       => $staff->ID,
            'amount'         => 50000.00,
            'reason'         => 'Test valid IOU application reason',
            'iou_date'       => date('Y-m-d'),
            'repayment_date' => date('Y-m-d', strtotime('+30 days')),
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
        $this->assertEquals(50000.00, $inserted->amount);
        $this->assertEquals(0, $inserted->status);
        $this->assertEquals(0, $inserted->hod_status);
        $this->assertEquals(0, $inserted->finance_status);
        $this->assertEquals(0, $inserted->admin_status);

        // 3. Update IOU application (still pending)
        $payloadUpdate = [
            'id'             => $iouId,
            'staff_id'       => $staff->ID,
            'amount'         => 60000.00, // modify amount
            'reason'         => 'Updated reason',
            'iou_date'       => date('Y-m-d'),
            'repayment_date' => date('Y-m-d', strtotime('+30 days')),
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
        $this->assertEquals(0, $afterHr->finance_status);
        $this->assertEquals(0, $afterHr->status);

        // Assert approval log created
        $logHr = DB::table('iou_approvals')->where('iou_id', $iouId)->where('level', 'HR')->first();
        $this->assertNotNull($logHr);
        $this->assertEquals(1, $logHr->status);

        // 6. Test Finance Final Approval Tier
        $responseFinance = $this->getJson("/api/nextjs/payroll/ious/finance-approve/{$iouId}?remarks=Fully+approved", $headers);
        $responseFinance->assertStatus(200)
            ->assertJson([
                'status' => 'success'
            ]);

        $afterFinance = DB::table('iou_records')->where('id', $iouId)->first();
        $this->assertEquals(1, $afterFinance->finance_status);
        $this->assertEquals(1, $afterFinance->status); // Overall status becomes 1 (approved)

        // 7. Delete the record
        $responseDelete = $this->deleteJson("/api/nextjs/payroll/ious/{$iouId}", [], $headers);
        $responseDelete->assertStatus(200)
            ->assertJson([
                'status' => 'success'
            ]);

        $this->assertDatabaseMissing('iou_records', ['id' => $iouId]);
        $this->assertDatabaseMissing('iou_approvals', ['iou_id' => $iouId]);
    }
}
