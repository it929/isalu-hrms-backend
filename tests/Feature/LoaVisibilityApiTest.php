<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LoaVisibilityApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_hr_audit_finance_heads_see_all_loa_records()
    {
        // 1. Find or create 2 distinct staff members
        $users = DB::table('tblper')->limit(2)->get();
        if ($users->count() < 2) {
            $this->markTestSkipped('Need at least 2 staff in tblper to test LOA visibility');
            return;
        }

        $viewerStaff = $users[0];
        $targetStaff = $users[1];

        // Clean
        DB::table('leave_of_absent')->whereIn('staffId', [$viewerStaff->ID, $targetStaff->ID])->delete();

        // 2. Create an LOA record for targetStaff (status 0 = pending)
        $loaId = DB::table('leave_of_absent')->insertGetId([
            'staffId' => $targetStaff->ID,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'status' => 0,
            'reason_of_leave' => 'Confidential staff medical leave of absence',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Test as HR Head (roleID 68 or 48)
        DB::table('assign_user_role')->where('userID', $viewerStaff->UserID)->delete();
        DB::table('assign_user_role')->insert([
            'userID' => $viewerStaff->UserID,
            'roleID' => 68, // HR Head
        ]);

        $hrRes = $this->getJson('/api/nextjs/hr/apply-loa/records', ['X-User-Id' => $viewerStaff->UserID]);
        $hrRes->assertStatus(200);
        $hrRecords = collect($hrRes->json('loaRecords'));
        $this->assertNotNull($hrRecords->firstWhere('id', $loaId), 'HR Head must see all staff LOA records');

        // 4. Test as Audit Head (roleID 70)
        DB::table('assign_user_role')->where('userID', $viewerStaff->UserID)->delete();
        DB::table('assign_user_role')->insert([
            'userID' => $viewerStaff->UserID,
            'roleID' => 70, // Audit Head
        ]);

        $auditRes = $this->getJson('/api/nextjs/hr/apply-loa/records', ['X-User-Id' => $viewerStaff->UserID]);
        $auditRes->assertStatus(200);
        $auditRecords = collect($auditRes->json('loaRecords'));
        $this->assertNotNull($auditRecords->firstWhere('id', $loaId), 'Audit Head must see all staff LOA records');

        // 5. Test as Finance Head (roleID 69)
        DB::table('assign_user_role')->where('userID', $viewerStaff->UserID)->delete();
        DB::table('assign_user_role')->insert([
            'userID' => $viewerStaff->UserID,
            'roleID' => 69, // Finance Head
        ]);

        $finRes = $this->getJson('/api/nextjs/hr/apply-loa/records', ['X-User-Id' => $viewerStaff->UserID]);
        $finRes->assertStatus(200);
        $finRecords = collect($finRes->json('loaRecords'));
        $this->assertNotNull($finRecords->firstWhere('id', $loaId), 'Finance Head must see all staff LOA records');

        // 6. Test as Regular Staff (roleID 74) -> MUST NOT see targetStaff's LOA
        DB::table('assign_user_role')->where('userID', $viewerStaff->UserID)->delete();
        DB::table('assign_user_role')->insert([
            'userID' => $viewerStaff->UserID,
            'roleID' => 74, // Regular Staff
        ]);

        $staffRes = $this->getJson('/api/nextjs/hr/apply-loa/records', ['X-User-Id' => $viewerStaff->UserID]);
        $staffRes->assertStatus(200);
        $staffRecords = collect($staffRes->json('loaRecords'));
        $this->assertNull($staffRecords->firstWhere('id', $loaId), 'Regular staff MUST NOT see other staff LOA records');
    }
}
