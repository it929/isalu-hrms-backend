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

    public function test_loa_deduction_calculation_captures_days_in_month()
    {
        $staff = DB::table('tblper')->first();
        if (!$staff) {
            $this->markTestSkipped('No staff in tblper');
            return;
        }

        // Setup salary structure: 310,000 gross
        DB::table('salary_structures')->where('staffId', $staff->ID)->delete();
        DB::table('salary_structures')->insert([
            'staffId' => $staff->ID,
            'basic_salary' => 100000,
            'housing_allowance' => 50000,
            'transport_allowance' => 50000,
            'medical_allowance' => 50000,
            'utility_allowance' => 30000,
            'meal_allowance' => 30000,
            'declare_salary' => 310000,
        ]);

        // Clean LOA
        DB::table('leave_of_absent')->where('staffId', $staff->ID)->delete();

        // 1. February (28 days) LOA for 2 days
        $febId = DB::table('leave_of_absent')->insertGetId([
            'staffId' => $staff->ID,
            'start_date' => '2026-02-10',
            'end_date' => '2026-02-11',
            'status' => 2,
            'reason_of_leave' => 'Feb LOA test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. August (31 days) LOA for 3 days
        $augId = DB::table('leave_of_absent')->insertGetId([
            'staffId' => $staff->ID,
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-12',
            'status' => 2,
            'reason_of_leave' => 'Aug LOA test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Fetch records as HR Head
        DB::table('assign_user_role')->where('userID', $staff->UserID)->delete();
        DB::table('assign_user_role')->insert([
            'userID' => $staff->UserID,
            'roleID' => 68,
        ]);

        $res = $this->getJson('/api/nextjs/hr/apply-loa/records', ['X-User-Id' => $staff->UserID]);
        $res->assertStatus(200);
        $records = collect($res->json('loaRecords'));

        $febRecord = $records->firstWhere('id', $febId);
        $this->assertNotNull($febRecord);
        $this->assertEquals(28, $febRecord['days_in_month']);
        $this->assertEquals(2, $febRecord['duration_days']);
        // Daily rate: 310,000 / 28 = 11071.43, Deduction: 11071.43 * 2 = 22142.86
        $expectedFebDaily = round(310000 / 28, 2);
        $this->assertEquals($expectedFebDaily, (float)$febRecord['daily_rate']);
        $this->assertEquals(round($expectedFebDaily * 2, 2), (float)$febRecord['estimated_deduction']);

        $augRecord = $records->firstWhere('id', $augId);
        $this->assertNotNull($augRecord);
        $this->assertEquals(31, $augRecord['days_in_month']);
        $this->assertEquals(3, $augRecord['duration_days']);
        // Daily rate: 310,000 / 31 = 10000.00, Deduction: 10000.00 * 3 = 30000.00
        $expectedAugDaily = round(310000 / 31, 2);
        $this->assertEquals($expectedAugDaily, (float)$augRecord['daily_rate']);
        $this->assertEquals(round($expectedAugDaily * 3, 2), (float)$augRecord['estimated_deduction']);
    }

    /**
     * Test that Leave of Absence application is declined if it causes the staff's net pay to reach 0.00 or negative balance.
     */
    public function test_loa_application_declined_if_netpay_becomes_negative_or_zero()
    {
        $user = DB::table('users')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in DB.');
        }

        $staffId = DB::table('tblper')->insertGetId([
            'fileNo' => 'TEST_LOA_NETPAY_001',
            'surname' => 'Test',
            'first_name' => 'LOANetPay',
            'rank' => 1,
            'office_shift' => 0, // Calendar days
            'staff_status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Salary structure: Gross = 100,000
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $staffId],
            [
                'basic_salary' => 50000.00,
                'housing_allowance' => 20000.00,
                'transport_allowance' => 10000.00,
                'medical_allowance' => 10000.00,
                'utility_allowance' => 5000.00,
                'meal_allowance' => 5000.00,
            ]
        );

        // Add recurring surcharge deduction of 80,000 for 2036-07
        $monthStr = '2036-07';
        DB::table('surcharge_deduction_setups')->updateOrInsert(
            ['staffId' => $staffId, 'start_month' => $monthStr],
            [
                'deduction_type' => 'Test Surcharge',
                'total_amount' => 80000.00,
                'duration_months' => 1,
                'monthly_deduction' => 80000.00,
                'balance_remaining' => 80000.00,
                'end_month' => $monthStr,
                'is_active' => 1,
                'updated_at' => now(),
            ]
        );

        $headers = ['X-User-Id' => $user->id];

        try {
            // In July (31 days), gross is 100,000, surcharge is 80,000.
            // If staff applies for 10 days LOA: LOA deduction = (100k / 31) * 10 = ~32,258.06
            // Total deductions = 80,000 + 32,258.06 = 112,258.06 > 100,000 (Net pay < 0).
            // Should be declined with 422!
            $res1 = $this->postJson('/api/nextjs/hr/apply-loa', [
                'employee_id' => $staffId,
                'start_date' => '2036-07-01',
                'end_date' => '2036-07-10',
                'leave_reason' => 'Testing LOA excessive days',
            ], $headers);

            $res1->assertStatus(422);
            $this->assertStringContainsString('can not be negative', $res1->json('message'));

            // If staff applies for 2 days LOA: LOA deduction = (100k / 31) * 2 = 6,451.61
            // Total deductions = 80,000 + ~4,677 (tax) + 6,451.61 = ~91,128 < 100,000 (Net pay > 0).
            // Should succeed with 200!
            $res2 = $this->postJson('/api/nextjs/hr/apply-loa', [
                'employee_id' => $staffId,
                'start_date' => '2036-07-01',
                'end_date' => '2036-07-02',
                'leave_reason' => 'Testing valid LOA days',
            ], $headers);

            $res2->assertStatus(200);

        } finally {
            DB::table('leave_of_absent')->where('staffId', $staffId)->delete();
            DB::table('surcharge_deduction_setups')->where('staffId', $staffId)->delete();
            DB::table('salary_structures')->where('staffId', $staffId)->delete();
            DB::table('tblper')->where('ID', $staffId)->delete();
        }
    }

    public function test_loa_export_spreadsheet_endpoint()
    {
        $user = DB::table('tblper')->first();
        if (!$user) {
            $this->markTestSkipped('No user available for testing LOA export');
            return;
        }

        // Assign HR role to view all records
        DB::table('assign_user_role')->where('userID', $user->UserID)->delete();
        DB::table('assign_user_role')->insert([
            'userID' => $user->UserID,
            'roleID' => 68,
        ]);

        $headers = ['X-User-Id' => $user->UserID];

        $res = $this->get('/api/nextjs/hr/apply-loa/export', $headers);
        $res->assertStatus(200);
        $this->assertStringContainsString('text/csv', $res->headers->get('Content-Type'));

        $csv = $res->streamedContent();
        $this->assertStringContainsString('ISALU HRMS — LEAVE OF ABSENCE (LOA) APPLICATIONS & DEDUCTION REPORT', $csv);
        $this->assertStringContainsString('Staff ID', $csv);
        $this->assertStringContainsString('Staff Name', $csv);
        $this->assertStringContainsString('Estimated LOA Deduction (NGN)', $csv);
        $this->assertStringContainsString('TOTAL', $csv);
    }
}

