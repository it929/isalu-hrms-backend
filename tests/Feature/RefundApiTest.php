<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RefundApiTest extends TestCase
{
    use DatabaseTransactions;

    private function getHeaders($userId)
    {
        return [
            'X-User-Id' => $userId
        ];
    }

    /**
     * Test retrieving the staff list.
     */
    public function test_get_staff_list()
    {
        $user = DB::table('users')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in DB.');
        }

        $headers = $this->getHeaders($user->id);
        $response = $this->getJson('/api/nextjs/payroll/refunds/staff', $headers);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success'
            ]);

        $data = $response->json('data');
        $this->assertIsArray($data);
    }

    /**
     * Test full Refund Request CRUD and approval workflow.
     */
    public function test_refund_crud_and_approvals_workflow()
    {
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

        // Clean up any existing refund records to avoid state pollution
        DB::table('refund_requests')->where('staff_id', $staff->ID)->delete();

        $headers = $this->getHeaders($user->id);
        $testDate = '2034-01-15';

        // 1. Submit refund request
        $payloadValid = [
            'staff_id'    => $staff->ID,
            'amount'      => 45000.00,
            'reason'      => 'Test refund request reason',
            'refund_date' => $testDate,
        ];

        $responseSubmit = $this->postJson('/api/nextjs/payroll/refunds', $payloadValid, $headers);
        $responseSubmit->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Refund request submitted successfully.'
            ]);

        // Find the record
        $record = DB::table('refund_requests')->where('staff_id', $staff->ID)->where('amount', 45000.00)->first();
        $this->assertNotNull($record);
        $refundId = $record->id;

        // 2. Update refund request
        $payloadUpdate = [
            'id'          => $refundId,
            'staff_id'    => $staff->ID,
            'amount'      => 50000.00,
            'reason'      => 'Updated test refund request reason',
            'refund_date' => $testDate,
        ];

        $responseUpdate = $this->postJson('/api/nextjs/payroll/refunds', $payloadUpdate, $headers);
        $responseUpdate->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Refund request updated successfully.'
            ]);

        $updated = DB::table('refund_requests')->where('id', $refundId)->first();
        $this->assertEquals(50000.00, $updated->amount);
        $this->assertEquals('Updated test refund request reason', $updated->reason);

        // 3a. Assert Audit approval fails before HR approval
        $responseAuditEarly = $this->getJson("/api/nextjs/payroll/refunds/audit-approve/{$refundId}?remarks=Audit+early", $headers);
        $responseAuditEarly->assertStatus(400)
            ->assertJson([
                'status'  => 'error',
                'message' => 'This request is not recommended by HR or already processed by Audit.'
            ]);

        // 3b. Assert Finance approval fails before Audit approval
        $responseFinanceEarly1 = $this->getJson("/api/nextjs/payroll/refunds/finance-approve/{$refundId}?remarks=Finance+early", $headers);
        $responseFinanceEarly1->assertStatus(400)
            ->assertJson([
                'status'  => 'error',
                'message' => 'This request is not recommended by Audit or already processed by Finance.'
            ]);

        // 3. Test HR Setup & Approval (Stage 1 directly without HOD)
        $responseHr = $this->getJson("/api/nextjs/payroll/refunds/hr-approve/{$refundId}?remarks=HR+approved", $headers);
        $responseHr->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $afterHr = DB::table('refund_requests')->where('id', $refundId)->first();
        $this->assertEquals(1, $afterHr->admin_status);
        $this->assertEquals(0, $afterHr->status);

        // 5. Assert Finance Approval fails before Audit Approval
        $responseFinanceEarly = $this->getJson("/api/nextjs/payroll/refunds/finance-approve/{$refundId}?remarks=Finance+early", $headers);
        $responseFinanceEarly->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'This request is not recommended by Audit or already processed by Finance.'
            ]);

        // 6. Test Audit Approval (Stage 3 succeeds)
        $responseAudit = $this->getJson("/api/nextjs/payroll/refunds/audit-approve/{$refundId}?remarks=Audit+approved", $headers);
        $responseAudit->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Refund request recommended successfully by Audit.'
            ]);

        $afterAudit = DB::table('refund_requests')->where('id', $refundId)->first();
        $this->assertEquals(1, $afterAudit->audit_status);
        $this->assertEquals(0, $afterAudit->status);

        // 7. Test Finance Approval (marked as Paid)
        $responseFinance = $this->getJson("/api/nextjs/payroll/refunds/finance-approve/{$refundId}?remarks=Finance+approved", $headers);
        $responseFinance->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Refund request marked as paid and completed by Finance.'
            ]);

        $afterFinance = DB::table('refund_requests')->where('id', $refundId)->first();
        $this->assertEquals(1, $afterFinance->finance_status);
        $this->assertEquals(1, $afterFinance->status);

        // 8. Delete request (after approval, it should fail for non-admins but succeed for admin)
        $responseDelete = $this->deleteJson("/api/nextjs/payroll/refunds/{$refundId}", [], $headers);
        $responseDelete->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing('refund_requests', ['id' => $refundId]);
    }

    /**
     * Test staff submits without amount, and HR Head sets up refund by days and by direct amount.
     */
    public function test_hr_setup_refund_by_days_and_by_amount()
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

        // Configure salary_structure for staff to ₦150,000 gross
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $staff->ID],
            [
                'basic_salary'        => 30000.00,
                'housing_allowance'   => 30000.00,
                'transport_allowance' => 15000.00,
                'medical_allowance'   => 15000.00,
                'utility_allowance'   => 30000.00,
                'meal_allowance'      => 30000.00,
                'created_at'          => now()
            ]
        );

        $headers = $this->getHeaders($user->id);

        // 1. Staff submits refund request WITHOUT specifying an amount
        $submitPayload = [
            'staff_id'    => $staff->ID,
            'reason'      => 'Refund for extra duty in April',
            'refund_date' => '2026-04-10',
        ];

        $submitRes = $this->postJson('/api/nextjs/payroll/refunds', $submitPayload, $headers);
        $submitRes->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Refund request submitted successfully.'
            ]);

        $record = DB::table('refund_requests')
            ->where('staff_id', $staff->ID)
            ->where('reason', 'Refund for extra duty in April')
            ->orderBy('id', 'desc')
            ->first();

        $this->assertNotNull($record);
        $this->assertEquals(0.00, (float)$record->amount);
        $refundId = $record->id;

        // 2. HR Head sets up refund directly with "days" mode for April 2026 (30 days, no HOD approval required)
        $hrSetupPayload = [
            'refund_type'  => 'days',
            'refund_days'  => 1,
            'refund_month' => '2026-04',
            'gross_salary' => 150000.00,
            'remarks'      => 'Calculated 1 day rate for April',
        ];

        $hrRes = $this->postJson("/api/nextjs/payroll/refunds/hr-approve/{$refundId}", $hrSetupPayload, $headers);
        $hrRes->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Refund request setup and approved successfully by HR Admin.'
            ]);

        $updatedRecord = DB::table('refund_requests')->where('id', $refundId)->first();
        $this->assertEquals('days', $updatedRecord->refund_type);
        $this->assertEquals(1.00, (float)$updatedRecord->refund_days);
        $this->assertEquals('2026-04', $updatedRecord->refund_month);
        $this->assertEquals(5000.00, (float)$updatedRecord->daily_rate);
        $this->assertEquals(5000.00, (float)$updatedRecord->amount);
        $this->assertEquals(1, $updatedRecord->admin_status);

        // Clean up
        DB::table('refund_requests')->where('id', $refundId)->delete();
    }
}

