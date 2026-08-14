<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MedicalLoanDeductionSetupApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_api_endpoints()
    {
        // Get user context ID
        $user = DB::table('tblper')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in tblper to run this test');
            return;
        }

        // Add role assignments so the request context detects isSuperAdmin or isAdminStaff
        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $user->UserID ?? 1,
            'roleID' => 1, // Super Admin
        ]);

        $headers = ['X-User-Id' => $user->UserID ?? 1];

        // Fetch configurations
        $response = $this->getJson('/api/nextjs/payroll/medical-loan-deduction-setups', $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // Create setup
        $response = $this->postJson('/api/nextjs/payroll/medical-loan-deduction-setups', [
            'staffId' => $user->ID,
            'loan_amount' => 50000.00,
            'duration_months' => 5,
            'monthly_deduction' => 10000.00,
            'start_month' => '2026-06',
            'end_month' => '2026-10',
            'is_active' => 1,
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('medical_loan_deduction_setups', [
            'staffId' => $user->ID,
            'loan_amount' => 50000.00,
            'duration_months' => 5,
            'monthly_deduction' => 10000.00,
        ]);

        // Get the setup ID
        $setup = DB::table('medical_loan_deduction_setups')->where('staffId', $user->ID)->first();
        $this->assertNotNull($setup);

        // Toggle setup
        $response = $this->postJson("/api/nextjs/payroll/medical-loan-deduction-setups/toggle/{$setup->id}", [], $headers);
        $response->assertStatus(200);
        $this->assertEquals(0, DB::table('medical_loan_deduction_setups')->where('id', $setup->id)->value('is_active'));

        // Delete setup
        $response = $this->deleteJson("/api/nextjs/payroll/medical-loan-deduction-setups/{$setup->id}", [], $headers);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('medical_loan_deduction_setups', ['id' => $setup->id]);

        // Test Template Download
        $templateResponse = $this->get('/api/nextjs/payroll/medical-loan-deduction-setups/template');
        $templateResponse->assertStatus(200);

        // Test Import with 2-column CSV (Staff ID and Loan Amount)
        $csvContent = "Staff ID,Loan Amount\n{$user->ID},\"120,000.00\"\n";
        $tempFile = tempnam(sys_get_temp_dir(), 'med_loan_csv');
        file_put_contents($tempFile, $csvContent);

        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tempFile,
            'medical_loan_import.csv',
            'text/csv',
            null,
            true
        );

        $importResponse = $this->postJson('/api/nextjs/payroll/medical-loan-deduction-setups/import', [
            'file' => $uploadedFile
        ], $headers);

        $importResponse->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // For loan amount 120,000, fixed monthly deduction tier is 20,000 and calculated tiered duration is 9 months
        $this->assertDatabaseHas('medical_loan_deduction_setups', [
            'staffId' => $user->ID,
            'loan_amount' => 120000.00,
            'monthly_deduction' => 20000.00,
            'duration_months' => 9,
            'is_active' => 1
        ]);

        unlink($tempFile);
    }
}
