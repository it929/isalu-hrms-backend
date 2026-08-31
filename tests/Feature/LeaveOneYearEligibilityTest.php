<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LeaveOneYearEligibilityTest extends TestCase
{
    use DatabaseTransactions;

    public function test_staff_must_have_worked_for_one_year_before_eligible_for_leave()
    {
        $admin = DB::table('users')->first();
        if (!$admin) {
            $this->markTestSkipped('No user found.');
            return;
        }

        $annualLeaveType = DB::table('tblleave_type')->where('leaveType', 'Annual')->first() 
            ?? DB::table('tblleave_type')->where('id', 5)->first();
        $annualLeaveTypeId = $annualLeaveType ? $annualLeaveType->id : 5;

        $casualLeaveType = DB::table('tblleave_type')->where('leaveType', 'Casual')->first() 
            ?? DB::table('tblleave_type')->where('id', 1)->first();
        $casualLeaveTypeId = $casualLeaveType ? $casualLeaveType->id : 1;

        // 1. Create a staff member who joined 4 months ago (< 1 year)
        $newStaffId = DB::table('tblper')->insertGetId([
            'fileNo'       => 'TEST-NEW-' . time(),
            'surname'      => 'NEWBIE',
            'first_name'   => 'JOHN',
            'doj'          => now()->subMonths(4)->toDateString(),
            'staff_status' => 1,
            'rank'         => 0,
            'departmentID' => 1,
            'gender'       => 'Male',
        ]);

        $headers = ['X-User-Id' => $admin->id];

        // 2. Attempt Annual Leave calculateEndDate -> Must be rejected (422)
        $calcRes = $this->getJson("/api/nextjs/hr/apply-leave/calculate-end-date?employee_id={$newStaffId}&leave_type={$annualLeaveTypeId}&start_date=" . now()->toDateString(), $headers);
        $calcRes->assertStatus(422)
            ->assertJson([
                'status' => 'error',
            ]);
        $this->assertStringContainsString('Annual Leave', $calcRes->json('message'));
        $this->assertStringContainsString('at least one (1) full year', $calcRes->json('message'));

        // 3. Attempt Annual Leave saveApplyLeave -> Must be rejected (422)
        $applyRes = $this->postJson('/api/nextjs/hr/apply-leave', [
            'employee_id'  => $newStaffId,
            'leave_type'   => $annualLeaveTypeId,
            'start_date'   => now()->toDateString(),
            'end_date'     => now()->addDays(5)->toDateString(),
            'leave_reason' => 'Annual Vacation test',
        ], $headers);

        $applyRes->assertStatus(422)
            ->assertJson([
                'status' => 'error',
            ]);
        $this->assertStringContainsString('Annual Leave', $applyRes->json('message'));

        // 4. Attempt Casual Leave saveApplyLeave for same new staff -> Must SUCCEED (200) because 1-year rule only applies to Annual Leave
        $casualApplyRes = $this->postJson('/api/nextjs/hr/apply-leave', [
            'employee_id'  => $newStaffId,
            'leave_type'   => $casualLeaveTypeId,
            'start_date'   => now()->toDateString(),
            'end_date'     => now()->addDays(2)->toDateString(),
            'leave_reason' => 'Urgent casual leave request',
        ], $headers);

        $casualApplyRes->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        // 5. Create an eligible staff member who joined 2 years ago (>= 1 year)
        $eligibleStaffId = DB::table('tblper')->insertGetId([
            'fileNo'       => 'TEST-OLD-' . time(),
            'surname'      => 'VETERAN',
            'first_name'   => 'JANE',
            'doj'          => now()->subYears(2)->toDateString(),
            'staff_status' => 1,
            'rank'         => 0,
            'departmentID' => 1,
            'gender'       => 'Female',
        ]);

        // 6. Eligible staff applying for Annual Leave -> Must succeed (200)
        $eligibleApplyRes = $this->postJson('/api/nextjs/hr/apply-leave', [
            'employee_id'  => $eligibleStaffId,
            'leave_type'   => $annualLeaveTypeId,
            'start_date'   => now()->toDateString(),
            'end_date'     => now()->addDays(5)->toDateString(),
            'leave_reason' => 'Eligible staff annual leave application',
        ], $headers);

        $eligibleApplyRes->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseHas('leave_record', [
            'staffId' => $eligibleStaffId,
            'leave_type_id' => $annualLeaveTypeId,
        ]);
    }
}
