<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ResignationApiTest extends TestCase
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
        $response = $this->getJson('/api/nextjs/payroll/resignations/staff', $headers);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success'
            ]);

        $data = $response->json('data');
        $this->assertIsArray($data);
    }

    /**
     * Test full Resignation Request CRUD and approval workflow.
     */
    public function test_resignation_crud_and_approvals_workflow()
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

        $headers = $this->getHeaders($user->id);
        $testDate = '2034-01-15';

        // 1. Submit resignation request
        $payloadValid = [
            'staff_id'         => $staff->ID,
            'reason'           => 'Test resignation reason',
            'resignation_date' => $testDate,
        ];

        $responseSubmit = $this->postJson('/api/nextjs/payroll/resignations', $payloadValid, $headers);
        $responseSubmit->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Resignation request submitted successfully.'
            ]);

        // Find the record
        $record = DB::table('resignation_requests')->where('staff_id', $staff->ID)->where('reason', 'Test resignation reason')->first();
        $this->assertNotNull($record);
        $resignationId = $record->id;

        // 2. Update resignation request
        $payloadUpdate = [
            'id'               => $resignationId,
            'staff_id'         => $staff->ID,
            'reason'           => 'Updated test resignation reason',
            'resignation_date' => $testDate,
        ];

        $responseUpdate = $this->postJson('/api/nextjs/payroll/resignations', $payloadUpdate, $headers);
        $responseUpdate->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Resignation request updated successfully.'
            ]);

        $updated = DB::table('resignation_requests')->where('id', $resignationId)->first();
        $this->assertEquals('Updated test resignation reason', $updated->reason);

        // 3. Test HOD Approval
        $responseHod = $this->getJson("/api/nextjs/payroll/resignations/hod-approve/{$resignationId}?remarks=HOD+approved", $headers);
        $responseHod->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $afterHod = DB::table('resignation_requests')->where('id', $resignationId)->first();
        $this->assertEquals(1, $afterHod->hod_status);
        $this->assertEquals(0, $afterHod->status);

        // 4. Test HR Approval
        $responseHr = $this->getJson("/api/nextjs/payroll/resignations/hr-approve/{$resignationId}?remarks=HR+approved", $headers);
        $responseHr->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $afterHr = DB::table('resignation_requests')->where('id', $resignationId)->first();
        $this->assertEquals(1, $afterHr->admin_status);
        $this->assertEquals(0, $afterHr->status);

        // 5. Test Finance Approval
        $responseFinance = $this->getJson("/api/nextjs/payroll/resignations/finance-approve/{$resignationId}?remarks=Finance+approved", $headers);
        $responseFinance->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $afterFinance = DB::table('resignation_requests')->where('id', $resignationId)->first();
        $this->assertEquals(1, $afterFinance->finance_status);
        $this->assertEquals(1, $afterFinance->status);

        // Verify staff record in tblper is updated (status_value = resignation, rank = 2, staff_status = 0)
        $updatedStaff = DB::table('tblper')->where('ID', $staff->ID)->first();
        $this->assertEquals('resignation', $updatedStaff->status_value);
        $this->assertEquals(2, $updatedStaff->rank);
        $this->assertEquals(0, $updatedStaff->staff_status);

        // 6. Delete request
        $responseDelete = $this->deleteJson("/api/nextjs/payroll/resignations/{$resignationId}", [], $headers);
        $responseDelete->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing('resignation_requests', ['id' => $resignationId]);
    }
}
