<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CoopLoanDeductionSetupApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_download_template()
    {
        $employee = DB::table('tblper')->first();
        if (!$employee) {
            $this->markTestSkipped('No user found');
        }
        $headers = ['X-User-Id' => $employee->UserID ?? 1];

        $response = $this->get('/api/nextjs/payroll/coop-loan-deduction-setups/template', $headers);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Staff ID', $response->streamedContent());
    }

    public function test_bulk_import()
    {
        $employee = DB::table('tblper')->first();
        if (!$employee) {
            $this->markTestSkipped('No user found');
        }

        // Add role assignments so context resolves super admin privileges
        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $employee->UserID ?? 1,
            'roleID' => 1 // Super Admin
        ]);

        $headers = ['X-User-Id' => $employee->UserID ?? 1];
        
        $fileData = "Staff ID,Loan Amount,Interest Rate (%),Duration Months,Start Month (YYYY-MM)\n" .
                    "{$employee->ID},500000,6,12,2026-05\n";
                    
        $uploadedFile = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'coop_setup_import.csv',
            $fileData
        );

        $response = $this->postJson('/api/nextjs/payroll/coop-loan-deduction-setups/import', [
            'file' => $uploadedFile
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('coop_loan_deduction_setups', [
            'staffId' => $employee->ID,
            'loan_amount' => 500000.00,
            'interest_rate' => 6.00,
            'duration_months' => 12,
        ]);
    }
}
