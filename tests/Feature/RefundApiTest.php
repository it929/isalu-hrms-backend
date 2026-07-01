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

        // 3. Test HOD Approval
        $responseHod = $this->getJson("/api/nextjs/payroll/refunds/hod-approve/{$refundId}?remarks=HOD+approved", $headers);
        $responseHod->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $afterHod = DB::table('refund_requests')->where('id', $refundId)->first();
        $this->assertEquals(1, $afterHod->hod_status);
        $this->assertEquals(0, $afterHod->status);

        // 4. Test HR Approval
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

        // 6. Test Audit Approval
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
}
