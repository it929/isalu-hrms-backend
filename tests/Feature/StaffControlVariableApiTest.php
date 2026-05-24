<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StaffControlVariableApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup any specific config / check database setup
    }

    /**
     * Test GET /api/nextjs/payroll/staff-control-variables/staff
     */
    public function test_get_staff_list()
    {
        // Mock a user ID that has admin access or check list
        // Since database has some seeded records, let's find an existing user or mock header
        $user = DB::table('users')->first();
        $headers = [];
        if ($user) {
            $headers['X-User-Id'] = $user->id;
        }

        $response = $this->getJson('/api/nextjs/payroll/staff-control-variables/staff', $headers);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success'
            ]);
    }

    /**
     * Test GET /api/nextjs/payroll/staff-control-variables/variable-types
     */
    public function test_get_variable_types()
    {
        $user = DB::table('users')->first();
        $headers = [];
        if ($user) {
            $headers['X-User-Id'] = $user->id;
        }

        $response = $this->getJson('/api/nextjs/payroll/staff-control-variables/variable-types', $headers);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success'
            ]);
    }

    /**
     * Test GET /api/nextjs/payroll/staff-control-variables/descriptions/{particularId}
     */
    public function test_get_descriptions()
    {
        $user = DB::table('users')->first();
        $headers = [];
        if ($user) {
            $headers['X-User-Id'] = $user->id;
        }

        // 1 is Earning, 2 is Deduction
        $response = $this->getJson('/api/nextjs/payroll/staff-control-variables/descriptions/1', $headers);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success'
            ]);
    }

    /**
     * Test POST and GET and DELETE flow for staff-control-variables
     */
    public function test_crud_flow()
    {
        $user = DB::table('users')->first();
        $headers = [];
        if ($user) {
            $headers['X-User-Id'] = $user->id;
        }

        // Fetch a valid staff ID from database
        $staff = DB::table('tblper')->first();
        $cvSetup = DB::table('tblcvSetup')->where('status', 1)->first();

        $this->assertNotNull($staff, 'Require at least one staff in database');
        $this->assertNotNull($cvSetup, 'Require at least one cvSetup entry in database');

        // 1. Create a record
        $payload = [
            'staffId'       => $staff->ID,
            'variable_type' => 'Earning',
            'cv_setup_id'   => $cvSetup->ID,
            'amount'        => 5000.00,
            'target_amount' => 15000.00,
            'no_limit'      => false,
            'one_time'      => false,
        ];

        $response = $this->postJson('/api/nextjs/payroll/staff-control-variables', $payload, $headers);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Record created successfully.'
            ]);

        // Assert record exists in database
        $record = DB::table('staffEarningAndDeduction')
            ->where('staffId', $staff->ID)
            ->where('cv_setup_id', $cvSetup->ID)
            ->where('amount', 5000.00)
            ->first();

        $this->assertNotNull($record);
        $this->assertEquals(15000.00, (float)$record->target_amount);
        $this->assertEquals(0, $record->one_time);

        // 2. Fetch list
        $responseList = $this->getJson('/api/nextjs/payroll/staff-control-variables', $headers);
        $responseList->assertStatus(200);
        $data = $responseList->json('data');
        $this->assertNotEmpty($data);

        // 3. Test One-Time option update
        $payloadUpdate = [
            'id'            => $record->id,
            'staffId'       => $staff->ID,
            'variable_type' => 'Earning',
            'cv_setup_id'   => $cvSetup->ID,
            'amount'        => 7500.00,
            'target_amount' => 10000.00, // Should be overridden by amount because one_time is true
            'no_limit'      => false,
            'one_time'      => true,
        ];

        $responseUpdate = $this->postJson('/api/nextjs/payroll/staff-control-variables', $payloadUpdate, $headers);
        $responseUpdate->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Record updated successfully.'
            ]);

        $updatedRecord = DB::table('staffEarningAndDeduction')->where('id', $record->id)->first();
        $this->assertEquals(7500.00, (float)$updatedRecord->amount);
        // Should equal amount (7500.00) because one_time is true
        $this->assertEquals(7500.00, (float)$updatedRecord->target_amount);
        $this->assertEquals(1, $updatedRecord->one_time);

        // 4. Delete the record
        $responseDelete = $this->deleteJson('/api/nextjs/payroll/staff-control-variables/' . $record->id, [], $headers);
        $responseDelete->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Record deleted successfully.'
            ]);

        $this->assertDatabaseMissing('staffEarningAndDeduction', [
            'id' => $record->id
        ]);
    }
}
