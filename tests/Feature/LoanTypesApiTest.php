<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LoanTypesApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanUpTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanUpTestData();
        parent::tearDown();
    }

    private function cleanUpTestData()
    {
        $names = [
            'Temp Loan Type',
            'Updated Temp Loan Type',
            'Duplicate Loan Type Test',
            'In Use Loan Type',
            'Type to Delete'
        ];

        // Delete test loans referencing these names
        DB::table('employee_loans')->whereIn('loan_type', $names)->delete();

        // Delete the loan types
        DB::table('loan_types')->whereIn('name', $names)->delete();
        DB::table('loan_types')->where('name', 'like', 'Test Automated Loan Type %')->delete();
    }

    /**
     * Test GET /api/nextjs/payroll/loans/types returns seeded loan types.
     */
    public function test_get_loan_types()
    {
        $response = $this->getJson('/api/nextjs/payroll/loans/types');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success'
            ]);

        $data = $response->json('data');
        $names = array_column($data, 'name');

        $this->assertContains('Personal Loan', $names);
        $this->assertContains('Medical Loan', $names);
        $this->assertContains('Car Loan', $names);
    }

    /**
     * Test POST /api/nextjs/payroll/loans/types creates a new loan type.
     */
    public function test_store_loan_type_creates_new_type()
    {
        $newTypeName = 'Test Automated Loan Type ' . uniqid();

        $response = $this->postJson('/api/nextjs/payroll/loans/types', [
            'name' => $newTypeName
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Loan type created successfully.'
            ]);

        $this->assertDatabaseHas('loan_types', [
            'name' => $newTypeName
        ]);
    }

    /**
     * Test POST /api/nextjs/payroll/loans/types updates an existing loan type.
     */
    public function test_store_loan_type_updates_existing_type()
    {
        // Insert a temporary type
        $id = DB::table('loan_types')->insertGetId([
            'name' => 'Temp Loan Type',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $updatedName = 'Updated Temp Loan Type';

        $response = $this->postJson('/api/nextjs/payroll/loans/types', [
            'id' => $id,
            'name' => $updatedName
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Loan type updated successfully.'
            ]);

        $this->assertDatabaseHas('loan_types', [
            'id' => $id,
            'name' => $updatedName
        ]);
    }

    /**
     * Test POST /api/nextjs/payroll/loans/types unique validation.
     */
    public function test_store_loan_type_validates_unique()
    {
        // Create one first
        $name = 'Duplicate Loan Type Test';
        DB::table('loan_types')->insert([
            'name' => $name,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Attempt to create another with the same name
        $response = $this->postJson('/api/nextjs/payroll/loans/types', [
            'name' => $name
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'A loan type with this name already exists.'
            ]);
    }

    /**
     * Test DELETE /api/nextjs/payroll/loans/types/{id} deletes a type.
     */
    public function test_destroy_loan_type()
    {
        $name = 'Type to Delete';
        $id = DB::table('loan_types')->insertGetId([
            'name' => $name,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->deleteJson("/api/nextjs/payroll/loans/types/{$id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Loan type deleted successfully.'
            ]);

        $this->assertDatabaseMissing('loan_types', [
            'id' => $id
        ]);
    }

    /**
     * Test DELETE /api/nextjs/payroll/loans/types/{id} is blocked if assigned to a loan.
     */
    public function test_destroy_loan_type_blocked_if_in_use()
    {
        $name = 'In Use Loan Type';
        $id = DB::table('loan_types')->insertGetId([
            'name' => $name,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Find a staff member
        $staff = DB::table('tblper')->first();
        $this->assertNotNull($staff, 'Require at least one staff member in tblper to run this test');

        // Create an employee loan assigning this loan type
        $loanId = DB::table('employee_loans')->insertGetId([
            'staffId' => $staff->ID,
            'loan_type' => $name,
            'loan_amount' => 100000.00,
            'balance' => 100000.00,
            'monthly_deduction' => 5000.00,
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Attempt to delete the type
        $response = $this->deleteJson("/api/nextjs/payroll/loans/types/{$id}");

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'This loan type cannot be deleted because it is currently assigned to one or more employee loans.'
            ]);

        // Verify the type is still in the database
        $this->assertDatabaseHas('loan_types', [
            'id' => $id
        ]);
    }
}
