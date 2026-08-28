<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AppraisalApiTest extends TestCase
{
    protected $adminUserId;
    protected $adminStaffId;
    protected $staffUserId;
    protected $staffId;
    protected $hodUserId;
    protected $hodStaffId;
    protected $periodId;
    protected $templateId;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Admin User & Staff
        $this->adminUserId = DB::table('users')->insertGetId([
            'name' => 'Admin Appraisal User',
            'username' => 'admin_appraisal_' . uniqid(),
            'email' => 'admin_appr_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('assign_user_role')->insert([
            'userID' => $this->adminUserId,
            'roleID' => 1,
            'created_at' => now(),
        ]);
        $this->adminStaffId = DB::table('tblper')->insertGetId([
            'UserID' => $this->adminUserId,
            'surname' => 'AdminSurname',
            'first_name' => 'AdminFirst',
            'fileNo' => 'ADM_' . rand(1000, 9999),
            'departmentID' => 1,
            'staff_status' => 1,
            'created_at' => now(),
        ]);

        // 2. Create HOD User & Staff
        $this->hodUserId = DB::table('users')->insertGetId([
            'name' => 'HOD Appraisal User',
            'username' => 'hod_appraisal_' . uniqid(),
            'email' => 'hod_appr_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->hodStaffId = DB::table('tblper')->insertGetId([
            'UserID' => $this->hodUserId,
            'surname' => 'HODSurname',
            'first_name' => 'HODFirst',
            'fileNo' => 'HOD_' . rand(1000, 9999),
            'departmentID' => 2,
            'is_hod' => 1,
            'staff_status' => 1,
            'created_at' => now(),
        ]);

        // 3. Create Regular Staff
        $this->staffUserId = DB::table('users')->insertGetId([
            'name' => 'Regular Staff User',
            'username' => 'staff_appraisal_' . uniqid(),
            'email' => 'staff_appr_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->staffId = DB::table('tblper')->insertGetId([
            'UserID' => $this->staffUserId,
            'surname' => 'StaffSurname',
            'first_name' => 'StaffFirst',
            'fileNo' => 'STF_' . rand(1000, 9999),
            'departmentID' => 2,
            'staff_status' => 1,
            'created_at' => now(),
        ]);


        // 4. Create a Period
        $this->periodId = DB::table('appraisal_periods')->insertGetId([
            'title' => '2026 Test Appraisal Cycle',
            'review_type' => 'annual',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'self_review_deadline' => '2026-09-30',
            'appraiser_review_deadline' => '2026-10-31',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $defaultTemplate = DB::table('appraisal_templates')->first();
        $this->templateId = $defaultTemplate ? $defaultTemplate->id : 1;
    }

    protected function tearDown(): void
    {
        DB::table('appraisal_scores')->whereIn('submission_id', function($q) {
            $q->select('id')->from('appraisal_submissions')->where('period_id', $this->periodId);
        })->delete();

        DB::table('appraisal_submissions')->where('period_id', $this->periodId)->delete();
        DB::table('appraisal_periods')->where('id', $this->periodId)->delete();

        DB::table('tblper')->whereIn('ID', [$this->adminStaffId, $this->hodStaffId, $this->staffId])->delete();
        DB::table('assign_user_role')->whereIn('userID', [$this->adminUserId, $this->hodUserId, $this->staffUserId])->delete();
        DB::table('users')->whereIn('id', [$this->adminUserId, $this->hodUserId, $this->staffUserId])->delete();

        parent::tearDown();
    }

    public function test_get_periods_and_templates()
    {
        $res = $this->withHeaders(['X-User-Id' => $this->adminUserId])
            ->getJson('/api/nextjs/appraisals/periods');
        $res->assertStatus(200);
        $res->assertJson(['status' => 'success']);

        $resTpl = $this->withHeaders(['X-User-Id' => $this->adminUserId])
            ->getJson('/api/nextjs/appraisals/templates');
        $resTpl->assertStatus(200);
        $resTpl->assertJson(['status' => 'success']);
    }

    public function test_dispatch_appraisal_and_full_workflow()
    {
        // 1. Dispatch cycle to department
        $dispatchRes = $this->withHeaders(['X-User-Id' => $this->adminUserId])
            ->postJson("/api/nextjs/appraisals/periods/{$this->periodId}/dispatch", [
                'template_id' => $this->templateId,
                'department_id' => 2,
            ]);
        $dispatchRes->assertStatus(200);

        // Verify submission record created for staff
        $submission = DB::table('appraisal_submissions')
            ->where('period_id', $this->periodId)
            ->where('staff_id', $this->staffId)
            ->first();
        $this->assertNotNull($submission);
        $this->assertEquals($this->hodStaffId, $submission->appraiser_id);
        $this->assertEquals('pending_self_review', $submission->status);

        // 2. Staff views their active appraisals
        $staffApprRes = $this->withHeaders(['X-User-Id' => $this->staffUserId])
            ->getJson('/api/nextjs/appraisals/my-active');
        $staffApprRes->assertStatus(200);

        // 3. Staff submits self-review
        $items = DB::table('appraisal_scores')->where('submission_id', $submission->id)->get();
        $scoresPayload = $items->map(function($i) {
            return [
                'criteria_item_id' => $i->criteria_item_id,
                'self_score' => 4.5,
                'self_comment' => 'Consistently delivered high standard output.',
            ];
        })->toArray();

        $selfSubmitRes = $this->withHeaders(['X-User-Id' => $this->staffUserId])
            ->postJson("/api/nextjs/appraisals/form/{$submission->id}/save-self", [
                'is_submit' => true,
                'staff_key_accomplishments' => 'Completed all clinical rotations on time with zero errors.',
                'staff_challenges' => 'High patient volume during festive period.',
                'staff_training_needs' => 'Advanced cardiac life support (ACLS) refresher.',
                'scores' => $scoresPayload,
            ]);
        $selfSubmitRes->assertStatus(200);

        // Verify status moved to pending_appraiser
        $submissionAfterSelf = DB::table('appraisal_submissions')->where('id', $submission->id)->first();
        $this->assertEquals('pending_appraiser', $submissionAfterSelf->status);
        $this->assertGreaterThan(80, $submissionAfterSelf->self_total_score);

        // 4. Appraiser views team queue
        $queueRes = $this->withHeaders(['X-User-Id' => $this->hodUserId])
            ->getJson('/api/nextjs/appraisals/team-queue');
        $queueRes->assertStatus(200);

        // 5. Appraiser submits evaluation
        $appraiserScoresPayload = $items->map(function($i) {
            return [
                'criteria_item_id' => $i->criteria_item_id,
                'appraiser_score' => 4.8,
                'appraiser_comment' => 'Exemplary clinical discipline and leadership.',
            ];
        })->toArray();

        $appraiserSubmitRes = $this->withHeaders(['X-User-Id' => $this->hodUserId])
            ->postJson("/api/nextjs/appraisals/form/{$submission->id}/save-appraiser", [
                'is_submit' => true,
                'appraiser_strengths' => 'Very reliable, detail oriented, great teamwork.',
                'appraiser_areas_for_growth' => 'Could take on more team scheduling responsibility.',
                'recommendation_type' => 'increment',
                'recommendation_details' => 'Recommend for 10% annual merit salary increment.',
                'scores' => $appraiserScoresPayload,
            ]);
        $appraiserSubmitRes->assertStatus(200);

        // Verify status moved to pending_hr_review and grade calculated
        $submissionAfterAppraiser = DB::table('appraisal_submissions')->where('id', $submission->id)->first();
        $this->assertEquals('pending_hr_review', $submissionAfterAppraiser->status);
        $this->assertEquals('A (Exceptional)', $submissionAfterAppraiser->performance_grade);

        // 6. HR Calibration & Approval
        $hrModerateRes = $this->withHeaders(['X-User-Id' => $this->adminUserId])
            ->postJson("/api/nextjs/appraisals/form/{$submission->id}/calibrate", [
                'action' => 'approve',
                'final_weighted_score' => 95.00,
                'hr_comments' => 'Approved with commendation. Outstanding performance.',
                'recommendation_type' => 'increment',
            ]);
        $hrModerateRes->assertStatus(200);

        $submissionFinal = DB::table('appraisal_submissions')->where('id', $submission->id)->first();
        $this->assertEquals('approved', $submissionFinal->status);

        // 7. Staff acknowledges approved appraisal
        $ackRes = $this->withHeaders(['X-User-Id' => $this->staffUserId])
            ->postJson("/api/nextjs/appraisals/form/{$submission->id}/acknowledge", [
                'staff_feedback' => 'Thank you for the constructive feedback and review.',
            ]);
        $ackRes->assertStatus(200);

        $submissionAck = DB::table('appraisal_submissions')->where('id', $submission->id)->first();
        $this->assertEquals('acknowledged', $submissionAck->status);
    }
}
