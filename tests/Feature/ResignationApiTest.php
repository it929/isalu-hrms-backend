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

        // Fetch staff initial state
        $initialStaff = DB::table('tblper')->where('ID', $staff->ID)->first();

        // 4. Test HR Approval
        $responseHr = $this->getJson("/api/nextjs/payroll/resignations/hr-approve/{$resignationId}?remarks=HR+approved", $headers);
        $responseHr->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $afterHr = DB::table('resignation_requests')->where('id', $resignationId)->first();
        $this->assertEquals(1, $afterHr->admin_status);
        $this->assertEquals(1, $afterHr->status); // HR approval is final stage

        // Verify staff record in tblper is updated upon HR approval
        $updatedStaff = DB::table('tblper')->where('ID', $staff->ID)->first();
        $this->assertEquals('resignation', $updatedStaff->status_value);

        // 5. Delete request
        $responseDelete = $this->deleteJson("/api/nextjs/payroll/resignations/{$resignationId}", [], $headers);
        $responseDelete->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing('resignation_requests', ['id' => $resignationId]);
    }

    /**
     * Test that Approved Resignations & Settlement uses salary_structures for monthly gross salary.
     */
    public function test_approved_resignation_uses_salary_structures_for_gross_salary()
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
            $this->markTestSkipped('No staff found.');
        }

        // Set entry salary in first_salary_structure (Entry gross = 100,000)
        DB::table('first_salary_structure')->updateOrInsert(
            ['staffId' => $staff->ID],
            [
                'basic_salary'        => 50000.00,
                'housing_allowance'   => 20000.00,
                'transport_allowance' => 10000.00,
                'medical_allowance'   => 10000.00,
                'utility_allowance'   => 5000.00,
                'meal_allowance'      => 5000.00,
                'declare_salary'      => 100000.00,
                'reten_act'           => 1,
                'num_rente_months'    => 10,
            ]
        );

        // Set current active salary structure in salary_structures (Current gross = 220,000)
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $staff->ID],
            [
                'basic_salary'        => 120000.00,
                'housing_allowance'   => 40000.00,
                'transport_allowance' => 20000.00,
                'medical_allowance'   => 15000.00,
                'utility_allowance'   => 10000.00,
                'meal_allowance'      => 15000.00,
                'declare_salary'      => 220000.00,
            ]
        );

        // Create an HR-approved resignation request
        $resignationId = DB::table('resignation_requests')->insertGetId([
            'staff_id'         => $staff->ID,
            'reason'           => 'Salary Gross Check',
            'resignation_date' => '2026-08-01',
            'status'           => 1,
            'hod_status'       => 1,
            'admin_status'     => 1, // Approved
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $headers = $this->getHeaders($user->id);

        try {
            // 1. Check registry list endpoint
            $responseList = $this->getJson('/api/nextjs/payroll/resignations/approved', $headers);
            $responseList->assertStatus(200);

            $records = collect($responseList->json('data'));
            $targetRecord = $records->firstWhere('id', $resignationId);
            $this->assertNotNull($targetRecord);
            // Expected current active gross = 120k + 40k + 20k + 15k + 10k + 15k = 220,000
            $this->assertEquals(220000.00, (float)$targetRecord['monthly_gross']);
            // Retention refund is based on first_salary_structure (5% of 100k = 5,000 * 10 months = 50,000)
            $this->assertEquals(50000.00, (float)$targetRecord['retention_refund']);

            // 2. Check detailed settlement endpoint
            $responseDetail = $this->getJson("/api/nextjs/payroll/resignations/settlement/{$resignationId}", $headers);
            $responseDetail->assertStatus(200);
            $settlementData = $responseDetail->json('data');
            $this->assertEquals(220000.00, (float)$settlementData['salary_structure']['monthly_gross']);
            $this->assertEquals(5000.00, (float)$settlementData['retention_refund']['monthly_rate']);
            $this->assertEquals(50000.00, (float)$settlementData['retention_refund']['total_refund_amount']);
        } finally {
            DB::table('resignation_requests')->where('id', $resignationId)->delete();
        }
    }
}

