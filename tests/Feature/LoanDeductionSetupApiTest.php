<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LoanDeductionSetupApiTest extends TestCase
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
        $user = DB::table('tblper')->first();
        if ($user) {
            DB::table('employee_loans')->where('staffId', $user->ID)->delete();
            DB::table('loan_deduction_setups')->where('staffId', $user->ID)->delete();
        }
    }

    public function test_api_endpoints()
    {
        // Get user context ID
        $user = DB::table('tblper')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in tblper to run this test');
            return;
        }

        // Add role assignments so the request context detects isSuperAdmin
        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $user->UserID ?? 1,
            'roleID' => 1, // Super Admin
        ]);

        $headers = ['X-User-Id' => $user->UserID ?? 1];

        // Insert a dummy approved loan into employee_loans
        DB::table('employee_loans')->insert([
            'staffId' => $user->ID,
            'loan_type' => 'revolving',
            'loan_amount' => 100000.00,
            'balance' => 100000.00,
            'monthly_deduction' => 10000.00,
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Test GET approved loan amount endpoint
        $response = $this->getJson("/api/nextjs/payroll/loan-deduction-setups/approved-amount/{$user->ID}", $headers);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'loan_amount' => 100000.00,
                'balance' => 100000.00
            ]);

        // Fetch configurations
        $response = $this->getJson('/api/nextjs/payroll/loan-deduction-setups', $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // Create setup
        $response = $this->postJson('/api/nextjs/payroll/loan-deduction-setups', [
            'staffId' => $user->ID,
            'loan_amount' => 100000.00,
            'interest_rate' => 10.0,
            'duration_months' => 10,
            'monthly_deduction' => 11000.00,
            'start_month' => '2026-06',
            'end_month' => '2027-03',
            'is_active' => 1,
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('loan_deduction_setups', [
            'staffId' => $user->ID,
            'loan_amount' => 100000.00,
            'interest_rate' => 10.0,
            'duration_months' => 10,
            'monthly_deduction' => 11000.00,
        ]);

        // Get the setup ID
        $setup = DB::table('loan_deduction_setups')->where('staffId', $user->ID)->first();
        $this->assertNotNull($setup);

        // Toggle setup
        $response = $this->postJson("/api/nextjs/payroll/loan-deduction-setups/toggle/{$setup->id}", [], $headers);
        $response->assertStatus(200);
        $this->assertEquals(0, DB::table('loan_deduction_setups')->where('id', $setup->id)->value('is_active'));

        // Delete setup
        $response = $this->deleteJson("/api/nextjs/payroll/loan-deduction-setups/{$setup->id}", [], $headers);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('loan_deduction_setups', ['id' => $setup->id]);
    }

    public function test_template_download()
    {
        $user = DB::table('tblper')->first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }
        $headers = ['X-User-Id' => $user->UserID ?? 1];
        
        $response = $this->get('/api/nextjs/payroll/loan-deduction-setups/template', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Staff ID', $response->streamedContent());
    }

    public function test_bulk_import()
    {
        $user = DB::table('tblper')->first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }
        $headers = ['X-User-Id' => $user->UserID ?? 1];
        
        $fileData = "Staff ID,Loan Amount,Interest Rate (%),Duration Months,Start Month (YYYY-MM)\n" .
                    "{$user->ID},150000,5,10,2026-07\n";
                    
        $uploadedFile = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'loan_setup_import_template.csv',
            $fileData
        );

        $response = $this->postJson('/api/nextjs/payroll/loan-deduction-setups/import', [
            'file' => $uploadedFile
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('loan_deduction_setups', [
            'staffId' => $user->ID,
            'loan_amount' => 150000.00,
            'interest_rate' => 5.00,
            'duration_months' => 10,
        ]);
    }
}
