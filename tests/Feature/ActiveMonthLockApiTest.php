<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ActiveMonthLockApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_lock_unlock_api_endpoints()
    {
        // 1. Get user context ID
        $user = DB::table('tblper')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in tblper to run this test');
            return;
        }

        // Add role assignments so request context detects isSuperAdmin
        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $user->UserID ?? 1,
            'roleID' => 1, // Super Admin
        ]);

        $headers = ['X-User-Id' => $user->UserID ?? 1];

        // Ensure we have a division setup
        $divisionId = $user->divisionID;
        $divisionName = DB::table('tbldivision')->where('divisionID', $divisionId)->value('division') ?? 'Test Division';

        // Setup active month config
        DB::table('tblactivemonth')->delete();
        DB::table('tblactivemonth')->insert([
            'month'   => 'OCTOBER',
            'year'    => 2026,
            'courtID' => 9
        ]);

        // Setup a payroll run
        DB::table('payroll_runs')->delete();
        $runId = DB::table('payroll_runs')->insertGetId([
            'month' => 10,
            'year'  => 2026,
            'status' => 'processed'
        ]);

        // Insert computed staff payroll conpt record
        DB::table('payroll_conpt')->delete();
        DB::table('payroll_conpt')->insert([
            'payroll_run_id' => $runId,
            'staffID'        => $user->ID,
            'month'          => 10,
            'year'           => 2026,
            'basic'          => 150000.00,
            'salary_lock'    => 0,
            'vstage'         => 0
        ]);

        // 2. Fetch index and check current status
        $response = $this->getJson('/api/nextjs/payroll/lock-active-month', $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // 3. Lock period
        $response = $this->postJson('/api/nextjs/payroll/lock-active-month/lock', [
            'year'       => 2026,
            'month'      => 'OCTOBER'
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Period locked successfully!']);

        // Verify database columns updated in payroll_conpt
        $this->assertDatabaseHas('payroll_conpt', [
            'staffID'     => $user->ID,
            'month'       => 10,
            'year'        => 2026,
            'salary_lock' => 1,
            'vstage'      => 1
        ]);

        // Verify audit log entry
        $expectedUserId = substr((string) ($user->UserID ?? 1), 0, 3);
        $this->assertDatabaseHas('audit_log', [
            'user_id' => $expectedUserId,
            'operation' => " active month locked globally for OCTOBER/2026 "
        ]);

        // 4. Try unlocking when vstage is higher (vstage > 1) -> should fail
        DB::table('payroll_conpt')
            ->where('staffID', $user->ID)
            ->where('month', 10)
            ->where('year', 2026)
            ->update(['vstage' => 2]);

        $response = $this->postJson('/api/nextjs/payroll/lock-active-month/unlock', [
            'year'       => 2026,
            'month'      => 'OCTOBER'
        ], $headers);

        $response->assertStatus(400)
            ->assertJson(['status' => 'error', 'message' => 'Cannot unlock: payroll verification has progressed beyond stage 1.']);

        // 5. Unlock successfully after resetting vstage <= 1
        DB::table('payroll_conpt')
            ->where('staffID', $user->ID)
            ->where('month', 10)
            ->where('year', 2026)
            ->update(['vstage' => 1]);

        $response = $this->postJson('/api/nextjs/payroll/lock-active-month/unlock', [
            'year'       => 2026,
            'month'      => 'OCTOBER'
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Period unlocked successfully!']);

        // Verify database columns reset in payroll_conpt
        $this->assertDatabaseHas('payroll_conpt', [
            'staffID'     => $user->ID,
            'month'       => 10,
            'year'        => 2026,
            'salary_lock' => 0,
            'vstage'      => 0
        ]);

        // Verify unlock audit log entry
        $this->assertDatabaseHas('audit_log', [
            'user_id' => $expectedUserId,
            'operation' => " active month unlocked globally for OCTOBER/2026 "
        ]);
    }
}
