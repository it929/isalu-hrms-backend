<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;

class BankUpdateApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test bank update endpoints with unauthorized and authorized users.
     */
    public function test_bank_update_api_endpoints()
    {
        // 1. Setup metadata
        $bankId1 = DB::table('tblbanklist')->insertGetId([
            'bank' => 'Test Apex Bank 1'
        ]);
        $bankId2 = DB::table('tblbanklist')->insertGetId([
            'bank' => 'Test Apex Bank 2'
        ]);

        $staffId = DB::table('tblper')->insertGetId([
            'fileNo' => 'T-10023',
            'surname' => 'Doe',
            'first_name' => 'John',
            'othernames' => 'Smith',
            'rank' => 1,
            'staff_status' => 1,
            'bankID' => $bankId1,
            'AccNo' => '0000000000',
            'created_at' => now(),
        ]);

        // Create Super Admin user
        $adminUserId = 99999;
        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $adminUserId,
            'roleID' => 1, // Super Administrator
        ]);

        // Create Non-Admin user
        $regularUserId = 88888;
        // No role assigned, or assign staff role
        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $regularUserId,
            'roleID' => 2, // Non-admin role
        ]);

        // Headers
        $adminHeaders = ['X-User-Id' => $adminUserId];
        $regularHeaders = ['X-User-Id' => $regularUserId];

        // 2. Test metadata loading
        $response = $this->getJson('/api/nextjs/payroll/bank-updates/metadata', $adminHeaders);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);
        
        $this->assertContains('Test Apex Bank 1', array_column($response->json('banks'), 'name'));

        // 3. Test individual update (unauthorized non-admin)
        $response = $this->postJson('/api/nextjs/payroll/bank-updates/individual', [
            'staff_id' => $staffId,
            'bank_id' => $bankId2,
            'account_number' => '1234567890',
        ], $regularHeaders);
        $response->assertStatus(403);

        // 4. Test individual update (authorized admin)
        $response = $this->postJson('/api/nextjs/payroll/bank-updates/individual', [
            'staff_id' => $staffId,
            'bank_id' => $bankId2,
            'account_number' => '1234567890',
        ], $adminHeaders);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseHas('tblper', [
            'ID' => $staffId,
            'bankID' => $bankId2,
            'AccNo' => '1234567890',
        ]);

        // 5. Test bulk CSV import (unauthorized non-admin)
        $csvContent = "staffId,Account Number\n";
        $csvContent .= "{$staffId},0987654321\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'bank_csv');
        file_put_contents($tempFile, $csvContent);

        $uploadedFile = new UploadedFile(
            $tempFile,
            'bulk_bank.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->postJson('/api/nextjs/payroll/bank-updates/bulk', [
            'excel_file' => $uploadedFile,
            'bank_id' => $bankId1,
        ], $regularHeaders);
        $response->assertStatus(403);

        // 6. Test bulk CSV import (authorized admin)
        $response = $this->postJson('/api/nextjs/payroll/bank-updates/bulk', [
            'excel_file' => $uploadedFile,
            'bank_id' => $bankId1,
        ], $adminHeaders);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'summary' => [
                    'updated' => 1,
                ]
            ]);

        $this->assertDatabaseHas('tblper', [
            'ID' => $staffId,
            'bankID' => $bankId1,
            'AccNo' => '0987654321',
        ]);

        unlink($tempFile);
    }
}
