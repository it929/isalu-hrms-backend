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

        // 4. Try unlocking when vstage is stage 4 (Paid) -> should fail
        DB::table('payroll_conpt')
            ->where('staffID', $user->ID)
            ->where('month', 10)
            ->where('year', 2026)
            ->update(['vstage' => 4]);

        $response = $this->postJson('/api/nextjs/payroll/lock-active-month/unlock', [
            'year'       => 2026,
            'month'      => 'OCTOBER'
        ], $headers);

        $response->assertStatus(400)
            ->assertJson(['status' => 'error', 'message' => 'Cannot unlock: payroll has already been paid.']);

        // 5. Reset to stage 1 and proceed through the workflow stages
        DB::table('payroll_conpt')
            ->where('staffID', $user->ID)
            ->where('month', 10)
            ->where('year', 2026)
            ->update(['vstage' => 1, 'salary_lock' => 1]);

        // Forward to Audit
        $response = $this->postJson('/api/nextjs/payroll/lock-active-month/forward-to-audit', [
            'year'       => 2026,
            'month'      => 'OCTOBER'
        ], $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Payroll forwarded to audit successfully.']);

        $this->assertDatabaseHas('payroll_conpt', [
            'staffID' => $user->ID,
            'vstage'  => 2
        ]);

        // Audit Check Staff
        $response = $this->postJson('/api/nextjs/payroll/lock-active-month/audit-check', [
            'year'       => 2026,
            'month'      => 'OCTOBER',
            'checked'    => 1,
            'staff_ids'  => [$user->ID]
        ], $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Audit checked status updated.']);

        $this->assertDatabaseHas('payroll_conpt', [
            'staffID'       => $user->ID,
            'audit_checked' => 1
        ]);

        // Audit Reject without remarks -> should fail validation (422)
        $response = $this->postJson('/api/nextjs/payroll/lock-active-month/audit-reject', [
            'year'       => 2026,
            'month'      => 'OCTOBER'
        ], $headers);
        $response->assertStatus(422);

        // Audit Reject with remarks -> should succeed (200) and revert vstage to 1, audit_checked to 0
        $response = $this->postJson('/api/nextjs/payroll/lock-active-month/audit-reject', [
            'year'       => 2026,
            'month'      => 'OCTOBER',
            'remarks'    => 'Rejected due to discrepancies'
        ], $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Payroll rejected by Audit successfully.']);

        $this->assertDatabaseHas('payroll_conpt', [
            'staffID'       => $user->ID,
            'vstage'        => 1,
            'audit_checked' => 0
        ]);

        $this->assertDatabaseHas('audit_log', [
            'user_id'   => $expectedUserId,
            'operation' => " active month payroll rejected by audit globally for OCTOBER/2026. Remarks: Rejected due to discrepancies"
        ]);

        // Re-forward to audit so we can continue testing approval and pay steps
        $response = $this->postJson('/api/nextjs/payroll/lock-active-month/forward-to-audit', [
            'year'       => 2026,
            'month'      => 'OCTOBER'
        ], $headers);
        $response->assertStatus(200);

        // Re-check staff
        $response = $this->postJson('/api/nextjs/payroll/lock-active-month/audit-check', [
            'year'       => 2026,
            'month'      => 'OCTOBER',
            'checked'    => 1,
            'staff_ids'  => [$user->ID]
        ], $headers);
        $response->assertStatus(200);

        // Audit Approve
        $response = $this->postJson('/api/nextjs/payroll/lock-active-month/audit-approve', [
            'year'       => 2026,
            'month'      => 'OCTOBER',
            'remarks'    => 'Looks good, approved by internal audit'
        ], $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Payroll approved by Audit successfully.']);

        $this->assertDatabaseHas('payroll_conpt', [
            'staffID' => $user->ID,
            'vstage'  => 3
        ]);

        $this->assertDatabaseHas('audit_log', [
            'user_id'   => $expectedUserId,
            'operation' => " active month payroll approved by audit globally for OCTOBER/2026 Remarks: Looks good, approved by internal audit"
        ]);

        // Try fetching payslip before paid -> should fail with 400
        $response = $this->getJson('/api/nextjs/payroll/payslip?staff_id=' . $user->ID . '&month=OCTOBER&year=2026', $headers);
        $response->assertStatus(400)
            ->assertJson(['status' => 'error', 'message' => 'Payslip cannot be generated: payroll for this month has not been paid yet.']);

        // Pay
        $response = $this->postJson('/api/nextjs/payroll/lock-active-month/pay', [
            'year'       => 2026,
            'month'      => 'OCTOBER',
            'remarks'    => 'Paid successfully, payment sent to bank'
        ], $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Payroll marked as paid successfully.']);

        $this->assertDatabaseHas('payroll_conpt', [
            'staffID' => $user->ID,
            'vstage'  => 4,
            'is_paid' => 1
        ]);

        $this->assertDatabaseHas('audit_log', [
            'user_id'   => $expectedUserId,
            'operation' => " active month payroll marked as paid globally for OCTOBER/2026 Remarks: Paid successfully, payment sent to bank"
        ]);

        // Fetch payslip after paid -> should succeed with 200
        $response = $this->getJson('/api/nextjs/payroll/payslip?staff_id=' . $user->ID . '&month=OCTOBER&year=2026', $headers);
        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.payslip.basic', 150000);

        // 6. Test successful unlock from stage 2 (Forwarded to Audit)
        DB::table('payroll_conpt')
            ->where('staffID', $user->ID)
            ->where('month', 10)
            ->where('year', 2026)
            ->update(['vstage' => 2, 'salary_lock' => 1, 'audit_checked' => 1]);

        $response = $this->postJson('/api/nextjs/payroll/lock-active-month/unlock', [
            'year'       => 2026,
            'month'      => 'OCTOBER'
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Period unlocked successfully!']);

        // Verify database columns reset in payroll_conpt
        $this->assertDatabaseHas('payroll_conpt', [
            'staffID'       => $user->ID,
            'month'         => 10,
            'year'          => 2026,
            'salary_lock'   => 0,
            'vstage'        => 0,
            'audit_checked' => 0,
            'is_paid'       => 0
        ]);

        // Verify unlock audit log entry
        $this->assertDatabaseHas('audit_log', [
            'user_id' => $expectedUserId,
            'operation' => " active month unlocked globally for OCTOBER/2026 "
        ]);

        // 7. Test Save and Get HR Signature
        $response = $this->postJson('/api/nextjs/payroll/hr-signature', [
            'signature' => 'data:image/png;base64,fake-signature-string'
        ], $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Signature saved successfully.']);

        $response = $this->getJson('/api/nextjs/payroll/hr-signature', $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'signature' => 'data:image/png;base64,fake-signature-string']);

        // 8. Test Send Payslip Email before Paid -> should fail
        DB::table('tblper')->where('ID', $user->ID)->update(['email' => 'staff@example.com']);
        DB::table('payroll_conpt')
            ->where('staffID', $user->ID)
            ->where('month', 10)
            ->where('year', 2026)
            ->update(['vstage' => 3]); // Stage 3 (Audit Approved, not yet Paid)

        $response = $this->postJson('/api/nextjs/payroll/payslip/send-email', [
            'staff_id' => $user->ID,
            'month'    => 'OCTOBER',
            'year'     => 2026
        ], $headers);
        $response->assertStatus(400)
            ->assertJson(['status' => 'error', 'message' => 'Cannot send payslip: staff has not been paid yet.']);

        // Now pay it and send -> should succeed
        DB::table('payroll_conpt')
            ->where('staffID', $user->ID)
            ->where('month', 10)
            ->where('year', 2026)
            ->update(['vstage' => 4, 'is_paid' => 1]); // Paid

        // Mock Laravel Mailer to prevent real mail delivery during tests
        \Illuminate\Support\Facades\Mail::fake();

        $response = $this->postJson('/api/nextjs/payroll/payslip/send-email', [
            'staff_id' => $user->ID,
            'month'    => 'OCTOBER',
            'year'     => 2026
        ], $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Payslip emailed successfully to staff@example.com']);

        // 9. Test Send Bulk Payslip Emails
        $response = $this->postJson('/api/nextjs/payroll/payslip/send-email-bulk', [
            'month'    => 'OCTOBER',
            'year'     => 2026
        ], $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // 10. Test Get Staff Net Pay
        $response = $this->getJson("/api/nextjs/payroll/staff-netpay/{$user->ID}", $headers);
        $response->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'net_pay' => 0.00,
                'month'   => 'OCTOBER',
                'year'    => 2026
            ]);
    }
}
