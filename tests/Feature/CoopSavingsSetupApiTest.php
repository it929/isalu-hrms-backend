<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CoopSavingsSetupApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_download_template()
    {
        $employee = DB::table('tblper')->first();
        if (!$employee) {
            $this->markTestSkipped('No user found');
        }
        $headers = ['X-User-Id' => $employee->UserID ?? 1];

        $response = $this->get('/api/nextjs/payroll/coop-savings-setups/template', $headers);
        $response->assertStatus(200);
        $this->assertStringContainsString('Monthly Saving Amount', $response->streamedContent());
        $this->assertStringContainsString('Saving Balance', $response->streamedContent());
    }

    public function test_bulk_import_correctly_captures_monthly_saving_and_saving_balance()
    {
        $employee = DB::table('tblper')->first();
        if (!$employee) {
            $this->markTestSkipped('No user found');
        }

        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $employee->UserID ?? 1,
            'roleID' => 1 // Super Admin
        ]);

        $headers = ['X-User-Id' => $employee->UserID ?? 1];

        // Ensure Monthly Saving Amount ($5,000) and Saving Balance ($75,000) are distinct values
        $fileData = "Staff ID,Monthly Saving Amount,Saving Balance,Start Month (YYYY-MM)\n" .
                    "{$employee->ID},5000.00,75000.00,2026-06\n";

        $uploadedFile = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'coop_savings_import.csv',
            $fileData
        );

        $response = $this->postJson('/api/nextjs/payroll/coop-savings-setups/import', [
            'file' => $uploadedFile
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('coop_savings_setups', [
            'staffId' => $employee->ID,
            'monthly_saving' => 5000.00,
            'saving_balance' => 75000.00,
            'start_month' => '2026-06',
            'is_active' => 1
        ]);
    }
}
