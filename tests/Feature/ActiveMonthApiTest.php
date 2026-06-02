<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ActiveMonthApiTest extends TestCase
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

        // Add role assignments so the request context detects isSuperAdmin
        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $user->UserID ?? 1,
            'roleID' => 1, // Super Admin
        ]);

        // Insert mock court if not exists
        $courtId = DB::table('tbl_court')->insertGetId([
            'court_name' => 'Test High Court',
            'courtAbbr' => 'THC',
            'active' => 1
        ]);

        // Insert tblsole_court entry if none exists
        $soleCourtCount = DB::table('tblsole_court')->count();
        if ($soleCourtCount == 0) {
            DB::table('tblsole_court')->insert([
                'courtid'        => $courtId,
                'courtstatus'    => 1,
                'divisionid'     => 1,
                'divisionstatus' => 1
            ]);
        }

        $headers = ['X-User-Id' => $user->UserID ?? 1];

        // 1. Fetch metadata configuration
        $response = $this->getJson('/api/nextjs/payroll/active-month', $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // 2. Set Active Month (should insert or update)
        $response = $this->postJson('/api/nextjs/payroll/active-month', [
            'court' => $courtId,
            'year'  => 2027,
            'month' => 'SEPTEMBER'
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Active month successfully updated!']);

        // 3. Verify in database
        $this->assertDatabaseHas('tblactivemonth', [
            'courtID' => $courtId,
            'year'    => 2027,
            'month'   => 'SEPTEMBER'
        ]);

        // 4. Verify audit log entry (user_id is varchar(3) in audit_log schema, so it truncates to 3 chars)
        $expectedUserId = substr((string) ($user->UserID ?? 1), 0, 3);
        $this->assertDatabaseHas('audit_log', [
            'user_id' => $expectedUserId,
            'operation' => " active month set  to SEPTEMBER/2027 for Test High Court Court  "
        ]);
    }
}
