<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ResignationRoleVisibilityTest extends TestCase
{
    use DatabaseTransactions;

    protected array $cleanupResignationIds = [];
    protected array $cleanupUserIds = [];

    protected function tearDown(): void
    {
        if (!empty($this->cleanupResignationIds)) {
            DB::table('resignation_requests')->whereIn('id', $this->cleanupResignationIds)->delete();
        }
        if (!empty($this->cleanupUserIds)) {
            DB::table('assign_user_role')->whereIn('userID', $this->cleanupUserIds)->delete();
            DB::table('users')->whereIn('id', $this->cleanupUserIds)->delete();
        }
        parent::tearDown();
    }

    public function test_resignation_records_visibility_by_role()
    {
        // 1. Get at least two distinct active employees
        $employees = DB::table('tblper')
            ->where('rank', '!=', 2)
            ->where('staff_status', 1)
            ->take(2)
            ->get();

        if ($employees->count() < 2) {
            $this->markTestSkipped('At least 2 active staff records required for this test.');
            return;
        }

        $staff1 = $employees[0];
        $staff2 = $employees[1];

        // 2. Create sample resignation requests for staff1 and staff2
        $resignation1 = DB::table('resignation_requests')->insertGetId([
            'staff_id'         => $staff1->ID,
            'resignation_date' => '2026-10-01',
            'reason'           => 'Better Career Opportunity - Staff 1',
            'status'           => 0,
            'hod_status'       => 0,
            'admin_status'     => 0,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
        $this->cleanupResignationIds[] = $resignation1;

        $resignation2 = DB::table('resignation_requests')->insertGetId([
            'staff_id'         => $staff2->ID,
            'resignation_date' => '2026-10-05',
            'reason'           => 'Personal Reasons - Staff 2',
            'status'           => 0,
            'hod_status'       => 0,
            'admin_status'     => 0,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
        $this->cleanupResignationIds[] = $resignation2;

        // Dedicated test user to avoid mutating real users in assign_user_role
        $testUserId = DB::table('users')->insertGetId([
            'name'       => 'Test Role User',
            'username'   => 'test_role_user_' . uniqid(),
            'email'      => 'test_role_' . uniqid() . '@isalu.test',
            'password'   => bcrypt('password'),
            'created_at' => now(),
        ]);
        $this->cleanupUserIds[] = $testUserId;

        // Helper to assign a role to user and call index endpoint
        $callAsRole = function ($userId, $roleId) {
            DB::table('assign_user_role')->where('userID', $userId)->delete();
            if ($roleId !== null) {
                DB::table('assign_user_role')->insert([
                    'userID' => $userId,
                    'roleID' => $roleId
                ]);
            }
            return $this->getJson('/api/nextjs/payroll/resignations', ['X-User-Id' => $userId]);
        };

        // 3. Test Super Admin (roleID = 1): Should see both records
        $resSuperAdmin = $callAsRole($testUserId, 1);
        $resSuperAdmin->assertStatus(200);
        $idsSuperAdmin = collect($resSuperAdmin->json('data'))->pluck('id')->toArray();
        $this->assertContains($resignation1, $idsSuperAdmin);
        $this->assertContains($resignation2, $idsSuperAdmin);

        // 4. Test HR Head (roleID = 68 or 48): Should see both records
        $resHrHead = $callAsRole($testUserId, 68);
        $resHrHead->assertStatus(200);
        $idsHrHead = collect($resHrHead->json('data'))->pluck('id')->toArray();
        $this->assertContains($resignation1, $idsHrHead);
        $this->assertContains($resignation2, $idsHrHead);

        // 5. Test Finance Head (roleID = 69 or 36): Should see both records
        $resFinanceHead = $callAsRole($testUserId, 69);
        $resFinanceHead->assertStatus(200);
        $idsFinanceHead = collect($resFinanceHead->json('data'))->pluck('id')->toArray();
        $this->assertContains($resignation1, $idsFinanceHead);
        $this->assertContains($resignation2, $idsFinanceHead);

        // 6. Test Audit Head (roleID = 70 or 34): Should see both records
        $resAuditHead = $callAsRole($testUserId, 70);
        $resAuditHead->assertStatus(200);
        $idsAuditHead = collect($resAuditHead->json('data'))->pluck('id')->toArray();
        $this->assertContains($resignation1, $idsAuditHead);
        $this->assertContains($resignation2, $idsAuditHead);

        // 7. Test Regular Staff (staff1): Should see their OWN record alone (resignation1), NOT resignation2
        $testStaffUser = DB::table('users')->insertGetId([
            'name'       => 'Test Staff User',
            'username'   => 'test_staff_' . uniqid(),
            'email'      => 'test_staff_' . uniqid() . '@isalu.test',
            'password'   => bcrypt('password'),
            'created_at' => now(),
        ]);
        $this->cleanupUserIds[] = $testStaffUser;
        $origStaff1UserId = $staff1->UserID;
        DB::table('tblper')->where('ID', $staff1->ID)->update(['UserID' => $testStaffUser]);

        $resStaff1 = $this->getJson('/api/nextjs/payroll/resignations', ['X-User-Id' => $testStaffUser]);
        $resStaff1->assertStatus(200);
        $idsStaff1 = collect($resStaff1->json('data'))->pluck('id')->toArray();
        $this->assertContains($resignation1, $idsStaff1);
        $this->assertNotContains($resignation2, $idsStaff1);

        // 8. Test explicit X-User-Role: Staff header overrides any background admin roles
        $resStaffRole = $this->getJson('/api/nextjs/payroll/resignations', [
            'X-User-Id'   => $testUserId,
            'X-User-Role' => 'Staff'
        ]);
        $resStaffRole->assertStatus(200);
        $idsStaffRole = collect($resStaffRole->json('data'))->pluck('id')->toArray();
        $this->assertNotContains($resignation2, $idsStaffRole);

        // 9. Test HOD: An HOD sees staff in their department (for approval) and their own record
        DB::table('tblper')->where('ID', $staff1->ID)->update(['is_hod' => 1, 'departmentID' => $staff2->departmentID]);
        $resHod = $this->getJson('/api/nextjs/payroll/resignations', ['X-User-Id' => $testStaffUser]);
        $resHod->assertStatus(200);
        $idsHod = collect($resHod->json('data'))->pluck('id')->toArray();
        $this->assertContains($resignation1, $idsHod); // Own record
        $this->assertContains($resignation2, $idsHod); // Department staff record for HOD approval

        // Restore original staff1 fields
        DB::table('tblper')->where('ID', $staff1->ID)->update([
            'UserID'       => $origStaff1UserId,
            'is_hod'       => $staff1->is_hod,
            'departmentID' => $staff1->departmentID,
        ]);
    }

    public function test_approved_resignation_settlement_registry_visibility_by_role()
    {
        // 1. Get at least two distinct active employees with departments
        $employees = DB::table('tblper')
            ->where('rank', '!=', 2)
            ->where('staff_status', 1)
            ->whereNotNull('departmentID')
            ->take(2)
            ->get();

        if ($employees->count() < 2) {
            $this->markTestSkipped('At least 2 active staff records required for this test.');
            return;
        }

        $staff1 = $employees[0];
        $staff2 = $employees[1];

        $origDept1 = $staff1->departmentID;
        $origDept2 = $staff2->departmentID;
        $origHod1 = $staff1->is_hod;

        DB::table('tblper')->where('ID', $staff1->ID)->update(['departmentID' => 101, 'is_hod' => 0]);
        DB::table('tblper')->where('ID', $staff2->ID)->update(['departmentID' => 102, 'is_hod' => 0]);

        // 2. Insert HR-approved resignation records for both staff
        $approved1 = DB::table('resignation_requests')->insertGetId([
            'staff_id'         => $staff1->ID,
            'resignation_date' => '2026-08-01',
            'reason'           => 'Approved Settlement 1',
            'status'           => 1,
            'hod_status'       => 1,
            'admin_status'     => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
        $this->cleanupResignationIds[] = $approved1;

        $approved2 = DB::table('resignation_requests')->insertGetId([
            'staff_id'         => $staff2->ID,
            'resignation_date' => '2026-08-05',
            'reason'           => 'Approved Settlement 2',
            'status'           => 1,
            'hod_status'       => 1,
            'admin_status'     => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
        $this->cleanupResignationIds[] = $approved2;

        $testUserId = DB::table('users')->insertGetId([
            'name'       => 'Test Settlement Role User',
            'username'   => 'test_settle_user_' . uniqid(),
            'email'      => 'test_settle_' . uniqid() . '@isalu.test',
            'password'   => bcrypt('password'),
            'created_at' => now(),
        ]);
        $this->cleanupUserIds[] = $testUserId;

        $callAsRole = function ($userId, $roleId) {
            DB::table('assign_user_role')->where('userID', $userId)->delete();
            DB::table('assign_user_role')->insert([
                'userID' => $userId,
                'roleID' => $roleId,
                'created_at' => now(),
            ]);
            return $this->getJson('/api/nextjs/payroll/resignations/approved', [
                'X-User-Id' => (string)$userId,
            ]);
        };

        // 3. Super Admin (roleID 1): sees both approved records
        $resSuper = $callAsRole($testUserId, 1);
        $resSuper->assertStatus(200);
        $idsSuper = collect($resSuper->json('data'))->pluck('id')->toArray();
        $this->assertContains($approved1, $idsSuper);
        $this->assertContains($approved2, $idsSuper);

        // 4. HR Head (roleID 68): sees both approved records
        $resHr = $callAsRole($testUserId, 68);
        $resHr->assertStatus(200);
        $idsHr = collect($resHr->json('data'))->pluck('id')->toArray();
        $this->assertContains($approved1, $idsHr);
        $this->assertContains($approved2, $idsHr);

        // 5. Audit Head (roleID 70): sees both approved records
        $resAudit = $callAsRole($testUserId, 70);
        $resAudit->assertStatus(200);
        $idsAudit = collect($resAudit->json('data'))->pluck('id')->toArray();
        $this->assertContains($approved1, $idsAudit);
        $this->assertContains($approved2, $idsAudit);

        // 6. Finance Head (roleID 69): sees both approved records
        $resFinance = $callAsRole($testUserId, 69);
        $resFinance->assertStatus(200);
        $idsFinance = collect($resFinance->json('data'))->pluck('id')->toArray();
        $this->assertContains($approved1, $idsFinance);
        $this->assertContains($approved2, $idsFinance);

        // 7. Regular Staff (staff1): sees ONLY approved1, NOT approved2
        $testStaffUser = DB::table('users')->insertGetId([
            'name'       => 'Test Staff User 2',
            'username'   => 'test_staff2_' . uniqid(),
            'email'      => 'test_staff2_' . uniqid() . '@isalu.test',
            'password'   => bcrypt('password'),
            'created_at' => now(),
        ]);
        $this->cleanupUserIds[] = $testStaffUser;
        $origStaff1UserId = $staff1->UserID;
        DB::table('tblper')->where('ID', $staff1->ID)->update(['UserID' => $testStaffUser]);

        $resStaff1 = $this->getJson('/api/nextjs/payroll/resignations/approved', ['X-User-Id' => $testStaffUser]);
        $resStaff1->assertStatus(200);
        $idsStaff1 = collect($resStaff1->json('data'))->pluck('id')->toArray();
        $this->assertContains($approved1, $idsStaff1);
        $this->assertNotContains($approved2, $idsStaff1);

        // 8. HOD of Department 102 (staff2's department): sees approved2 and own
        DB::table('tblper')->where('ID', $staff1->ID)->update(['is_hod' => 1, 'departmentID' => 102]);
        $resHod = $this->getJson('/api/nextjs/payroll/resignations/approved', ['X-User-Id' => $testStaffUser]);
        $resHod->assertStatus(200);
        $idsHod = collect($resHod->json('data'))->pluck('id')->toArray();
        $this->assertContains($approved1, $idsHod); // Own record
        $this->assertContains($approved2, $idsHod); // Department 102 staff record

        // Restore original staff1 fields
        DB::table('tblper')->where('ID', $staff1->ID)->update([
            'UserID'       => $origStaff1UserId,
            'is_hod'       => $origHod1,
            'departmentID' => $origDept1,
        ]);
        DB::table('tblper')->where('ID', $staff2->ID)->update([
            'departmentID' => $origDept2,
        ]);
    }
}
