<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PayrollDepartmentExportApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_payroll_department_metadata_filtering_and_export_csv()
    {
        $user = DB::table('tblper')->first();
        if (!$user) {
            $this->markTestSkipped('No user in tblper to test');
            return;
        }

        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $user->UserID ?? 1,
            'roleID' => 1,
        ]);

        $headers = ['X-User-Id' => $user->UserID ?? 1];

        // 1. Metadata check for departments
        $metaRes = $this->getJson('/api/nextjs/payroll/metadata', $headers);
        $metaRes->assertStatus(200)
            ->assertJson(['status' => 'success']);
        
        $this->assertNotEmpty($metaRes->json('departments'));

        $firstDept = DB::table('tbldepartment')->first();
        $deptId = $firstDept ? $firstDept->id : 1;

        // 2. Filter payroll by department
        $listRes = $this->getJson("/api/nextjs/payroll?month=JANUARY&year=2026&departmentID={$deptId}", $headers);
        $listRes->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // 3. Export payroll by department in CSV format
        $exportRes = $this->get("/api/nextjs/payroll/export?month=JANUARY&year=2026&departmentID={$deptId}", $headers);
        $exportRes->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportRes->headers->get('content-type'));
        
        $content = $exportRes->getContent();
        $this->assertStringContainsString('ISALU HRMS — PAYROLL SCHEDULE', $content);
        $this->assertStringContainsString('IDNO', $content);
        $this->assertStringContainsString('NET PAY', $content);
    }
}
