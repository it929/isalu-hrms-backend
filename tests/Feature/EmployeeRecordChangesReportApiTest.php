<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmployeeRecordChangesReportApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_employee_record_changes_report_returns_real_database_records()
    {
        $user = DB::table('tblper')->first();
        if (!$user) {
            $this->markTestSkipped('No user in tblper');
            return;
        }

        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $user->UserID ?? 1,
            'roleID' => 1,
        ]);

        $headers = ['X-User-Id' => $user->UserID ?? 1];

        $response = $this->getJson('/api/nextjs/reports/employee-changes', $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $data = $response->json('data');
        $this->assertIsArray($data);

        if (!empty($data)) {
            $first = $data[0];
            $this->assertArrayHasKey('staff', $first);
            $this->assertArrayHasKey('field', $first);
            $this->assertArrayHasKey('oldVal', $first);
            $this->assertArrayHasKey('newVal', $first);
            $this->assertArrayHasKey('user', $first);
            $this->assertArrayHasKey('date', $first);
        }
    }
}
