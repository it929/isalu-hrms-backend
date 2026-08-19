<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditHeadLeaveRecordsApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_audit_head_sees_all_leave_records()
    {
        // 1. Find or create two distinct staff members
        $staff1 = DB::table('tblper')->first();
        if (!$staff1) {
            $this->markTestSkipped('No staff in tblper to test');
            return;
        }

        $staff2 = DB::table('tblper')->where('ID', '!=', $staff1->ID)->first();
        if (!$staff2) {
            $this->markTestSkipped('Need at least 2 staff members to test multi-record visibility');
            return;
        }

        // 2. Assign roleID = 34 (Audit Head) or role named 'Audit Head' to staff1
        $auditRole = DB::table('user_role')->where('rolename', 'LIKE', '%Audit%')->first();
        $auditRoleId = $auditRole ? $auditRole->roleID : 34;

        DB::table('assign_user_role')->updateOrInsert(
            ['userID' => $staff1->UserID, 'roleID' => $auditRoleId],
            ['roleID' => $auditRoleId]
        );

        // 3. Create a leave record for staff2 (who is in a different department / not staff1)
        $leaveType = DB::table('tblleave_type')->first();
        $leaveTypeId = $leaveType ? $leaveType->id : 1;

        $createdLeaveId = DB::table('leave_record')->insertGetId([
            'staffId'        => $staff2->ID,
            'leave_type_id'  => $leaveTypeId,
            'start_date'     => now()->toDateString(),
            'end_date'       => now()->addDays(5)->toDateString(),
            'status'         => 0, // Pending
            'reason_of_leave'=> 'Audit Visibility Test Leave',
            'created_at'     => now(),
        ]);

        $headers = ['X-User-Id' => $staff1->UserID];

        // 4. Test apply-leave metadata returns isAuditStaff true
        $metaRes = $this->getJson('/api/nextjs/hr/apply-leave', $headers);
        $metaRes->assertStatus(200)
            ->assertJson(['status' => 'success', 'isAuditStaff' => true]);

        // 5. Test apply-leave/records returns staff2's record for staff1 (Audit Head)
        $recordsRes = $this->getJson('/api/nextjs/hr/apply-leave/records', $headers);
        $recordsRes->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $leaveRecords = collect($recordsRes->json('leaveRecords'));
        $found = $leaveRecords->firstWhere('id', $createdLeaveId);

        $this->assertNotNull($found, 'Audit Head must be able to see other staff leave records in leave_record table');
        $this->assertEquals($staff2->ID, $found['staffId']);
    }
}
