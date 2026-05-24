<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CvSetupApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test GET /api/nextjs/payroll/cv-setups/banks
     */
    public function test_get_banks()
    {
        $user = DB::table('users')->first();
        $headers = [];
        if ($user) {
            $headers['X-User-Id'] = $user->id;
        }

        $response = $this->getJson('/api/nextjs/payroll/cv-setups/banks', $headers);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success'
            ]);

        $data = $response->json('data');
        $this->assertIsArray($data);
    }

    /**
     * Test GET /api/nextjs/payroll/cv-setups
     */
    public function test_get_cv_setups()
    {
        $user = DB::table('users')->first();
        $headers = [];
        if ($user) {
            $headers['X-User-Id'] = $user->id;
        }

        $response = $this->getJson('/api/nextjs/payroll/cv-setups', $headers);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success'
            ]);
    }

    /**
     * Test Full CRUD flow with validation and safety constraints
     */
    public function test_crud_and_safety_checks()
    {
        $user = DB::table('users')->first();
        $headers = [];
        if ($user) {
            $headers['X-User-Id'] = $user->id;
        }

        // Get a valid bankID if one exists
        $bank = DB::table('tblbanklist')->first();
        $bankID = $bank ? $bank->bankID : null;

        // 1. Create a setup variable
        $description = 'Test Unique Setup ' . uniqid();
        $payload = [
            'particularID'   => 1, // Earning
            'description'    => $description,
            'bank'           => $bankID,
            'account_name'   => 'Test Particular Earning Account',
            'account_number' => '1234567890',
            'status'         => true
        ];

        $response = $this->postJson('/api/nextjs/payroll/cv-setups', $payload, $headers);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Setup variable created successfully.'
            ]);

        // Assert created correctly
        $created = DB::table('tblcvSetup')->where('description', $description)->first();
        $this->assertNotNull($created);
        $this->assertEquals(1, $created->particularID);
        $this->assertEquals($bankID, $created->bank);
        $this->assertEquals('Test Particular Earning Account', $created->account_name);
        $this->assertEquals('1234567890', $created->account_number);
        $this->assertEquals(1, $created->status);

        // 2. Test unique validation on store
        $responseDuplicate = $this->postJson('/api/nextjs/payroll/cv-setups', $payload, $headers);
        $responseDuplicate->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Validation failed.'
            ]);

        // 3. Test Update
        $updatedDescription = 'Updated Particular ' . uniqid();
        $payloadUpdate = [
            'id'             => $created->ID,
            'particularID'   => 2, // Change to Deduction
            'description'    => $updatedDescription,
            'bank'           => $bankID,
            'account_name'   => 'Test Particular Deduction Account',
            'account_number' => '0987654321',
            'status'         => false
        ];

        $responseUpdate = $this->postJson('/api/nextjs/payroll/cv-setups', $payloadUpdate, $headers);
        $responseUpdate->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Setup variable updated successfully.'
            ]);

        $updated = DB::table('tblcvSetup')->where('ID', $created->ID)->first();
        $this->assertEquals($updatedDescription, $updated->description);
        $this->assertEquals(2, $updated->particularID);
        $this->assertEquals('Test Particular Deduction Account', $updated->account_name);
        $this->assertEquals('0987654321', $updated->account_number);
        $this->assertEquals(0, $updated->status);

        // 4. Test Deletion Safety check: Assign CV to a staff profile and verify block delete
        $staff = DB::table('tblper')->first();
        if ($staff) {
            // Setup relation in tblstaffCV referencing our new setup ID
            $staffCvId = DB::table('tblstaffCV')->insertGetId([
                'courtID' => 1,
                'divisionID' => $staff->divisionID ?? 1,
                'staffid' => $staff->ID,
                'cvID' => $created->ID, // references our created Setup ID
                'amount' => 1000.00,
                'targetAmount' => 5000.00,
                'cvtype' => 2,
                'recycling' => 0
            ]);

            // Attempt deletion
            $responseDeleteBlocked = $this->deleteJson('/api/nextjs/payroll/cv-setups/' . $created->ID, [], $headers);
            $responseDeleteBlocked->assertStatus(400)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'This variable setup is currently assigned to employee profiles and cannot be deleted.'
                ]);

            // Clean up tblstaffCV assignment
            DB::table('tblstaffCV')->where('ID', $staffCvId)->delete();
        }

        // 5. Test successful delete when not in use
        $responseDelete = $this->deleteJson('/api/nextjs/payroll/cv-setups/' . $created->ID, [], $headers);
        $responseDelete->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Setup variable deleted successfully.'
            ]);

        $this->assertDatabaseMissing('tblcvSetup', [
            'ID' => $created->ID
        ]);
    }
}
