<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdviceToResignApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_advice_to_resign_full_lifecycle()
    {
        // 1. Create a test staff member
        $staffId = DB::table('tblper')->insertGetId([
            'fileNo' => 'T-ADVICE-01',
            'surname' => 'Ogunleye',
            'first_name' => 'Babatunde',
            'othernames' => 'Samson',
            'rank' => 1,
            'staff_status' => 1,
            'status_value' => 'active',
            'department' => 1,
            'designation' => 1,
            'created_at' => now(),
        ]);

        $adminUserId = 99999;
        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $adminUserId,
            'roleID' => 1,
        ]);

        $headers = ['X-User-Id' => $adminUserId];

        // 2. Test staff endpoint
        $staffRes = $this->getJson('/api/nextjs/advice-to-resign/staff?search=Ogunleye', $headers);
        $staffRes->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $staffList = collect($staffRes->json('data.staff'));
        $this->assertTrue($staffList->contains('id', $staffId));

        // 3. Issue Advice to Resign
        $issuePayload = [
            'staff_id' => $staffId,
            'issue_date' => now()->format('Y-m-d'),
            'deadline_date' => now()->addDays(2)->format('Y-m-d'),
            'reason' => 'Unsatisfactory Performance',
            'query_reference' => 'QRY/2026/09/99',
            'details' => 'Repeated clinical audit discrepancies and failure to adhere to standard procedures.',
            'consequence_if_defaulted' => 'Summary termination of appointment with loss of benefits.',
        ];

        $storeRes = $this->postJson('/api/nextjs/advice-to-resign', $issuePayload, $headers);
        $storeRes->assertStatus(201)
            ->assertJson([
                'status' => 'success',
            ]);

        $recordId = $storeRes->json('id');
        $referenceNo = $storeRes->json('reference_no');
        $this->assertNotEmpty($referenceNo);
        $this->assertStringContainsString('IHL/ATR/', $referenceNo);

        $this->assertDatabaseHas('advice_to_resigns', [
            'id' => $recordId,
            'staff_id' => $staffId,
            'status' => 'pending',
            'reason' => 'Unsatisfactory Performance',
        ]);

        // 4. Test Index Listing & Search
        $listRes = $this->getJson('/api/nextjs/advice-to-resign?search=Ogunleye', $headers);
        $listRes->assertStatus(200)
            ->assertJson(['status' => 'success']);
        $this->assertCount(1, $listRes->json('data.records'));
        $this->assertEquals($staffId, $listRes->json('data.records.0.staff_id'));
        $this->assertArrayNotHasKey('file_no', $listRes->json('data.records.0'));

        // 5. Test Letter Data Generation
        $letterRes = $this->getJson("/api/nextjs/advice-to-resign/{$recordId}/letter", $headers);
        $letterRes->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'reference_no' => $referenceNo,
                    'reason' => 'Unsatisfactory Performance',
                ],
            ]);
        $this->assertStringContainsString('ISALU HOSPITALS LIMITED', $letterRes->json('data.hospital.name'));
        $this->assertStringContainsString('Ogunleye', $letterRes->json('data.staff.name'));
        $this->assertEquals($staffId, $letterRes->json('data.staff.staff_id'));
        $this->assertArrayNotHasKey('file_no', $letterRes->json('data.staff'));
        $this->assertEquals('ANIFOWOSHE MONSURAT', $letterRes->json('data.signatory.name'));
        $this->assertNotEmpty($letterRes->json('data.signatory.signature_url'));

        // 6. Test Outcome Action: Complied + Auto Convert to Resignation Settlement
        $actionPayload = [
            'status' => 'complied',
            'compliance_date' => now()->format('Y-m-d'),
            'resolution_remarks' => 'Staff tendered voluntary resignation letter within the 48-hour deadline.',
            'convert_to_resignation' => true,
        ];

        $actionRes = $this->postJson("/api/nextjs/advice-to-resign/{$recordId}/action", $actionPayload, $headers);
        $actionRes->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseHas('advice_to_resigns', [
            'id' => $recordId,
            'status' => 'complied',
        ]);

        // Verify that resignation_request was created with approved status
        $adviceRec = DB::table('advice_to_resigns')->where('id', $recordId)->first();
        $this->assertNotNull($adviceRec->resignation_request_id);

        $this->assertDatabaseHas('resignation_requests', [
            'id' => $adviceRec->resignation_request_id,
            'staff_id' => $staffId,
            'status' => 1,
            'admin_status' => 1,
        ]);

        // Verify staff status updated to resignation
        $staffRec = DB::table('tblper')->where('ID', $staffId)->first();
        $this->assertEquals('resignation', $staffRec->status_value);

        // Verify User Activity Log was generated
        $log = DB::table('user_activity_logs')
            ->where('action', 'like', "%{$referenceNo}%")
            ->first();
        $this->assertNotNull($log);
    }

    public function test_advice_to_resign_end_to_end_governance_workflow()
    {
        // 1. Create a test staff member with salary structure
        $staffId = DB::table('tblper')->insertGetId([
            'fileNo' => 'T-ADVICE-02',
            'surname' => 'Adeyemi',
            'first_name' => 'Olumide',
            'othernames' => 'David',
            'rank' => 1,
            'staff_status' => 1, // Currently Active on Payroll
            'status_value' => 'active',
            'department' => 1,
            'designation' => 1,
            'created_at' => now(),
        ]);

        $adminUserId = 88888;
        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $adminUserId,
            'roleID' => 1, // Super Admin
        ]);

        $headers = [
            'X-User-Id' => $adminUserId,
            'X-User-Role' => 'super admin'
        ];

        // 2. Issue Advice to Resign Notice
        $issueRes = $this->postJson('/api/nextjs/advice-to-resign', [
            'staff_id' => $staffId,
            'issue_date' => now()->format('Y-m-d'),
            'deadline_date' => now()->addDays(3)->format('Y-m-d'),
            'reason' => 'Disciplinary Committee Recommendation',
            'details' => 'Administrative review confirmed consistent failure to adhere to clinical protocols.',
        ], $headers);
        $issueRes->assertStatus(201);
        $adviceId = $issueRes->json('id');
        $refNo = $issueRes->json('reference_no');

        // 3. Step 1: Staff Applies (Tenders Resignation pursuant to Advice)
        $applyRes = $this->postJson("/api/nextjs/advice-to-resign/{$adviceId}/staff-apply", [
            'applied_date' => now()->format('Y-m-d'),
            'staff_remarks' => 'I hereby submit my formal resignation following the advice notice.',
        ], $headers);
        $applyRes->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('advice_to_resigns', [
            'id' => $adviceId,
            'status' => 'applied',
        ]);

        // 4. Step 2: HR Head Approves -> Removes staff from payroll (staff_status=0) & calculates settlement
        $approveRes = $this->postJson("/api/nextjs/advice-to-resign/{$adviceId}/hr-approve", [
            'hr_remarks' => 'Resignation accepted. Immediate exit settlement authorized.',
        ], $headers);
        $approveRes->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // Assert staff is removed from active payroll
        $updatedStaff = DB::table('tblper')->where('ID', $staffId)->first();
        $this->assertEquals(0, (int)$updatedStaff->staff_status, 'Staff must be removed from active payroll (staff_status = 0)');
        $this->assertEquals('resignation', $updatedStaff->status_value);

        // Assert advice status updated to hr_approved
        $updatedAdvice = DB::table('advice_to_resigns')->where('id', $adviceId)->first();
        $this->assertEquals('hr_approved', $updatedAdvice->status);
        $this->assertNotNull($updatedAdvice->resignation_request_id);
        $this->assertNotNull($updatedAdvice->settlement_summary);

        // 5. Test Settlement Breakdown Endpoint
        $settlementRes = $this->getJson("/api/nextjs/advice-to-resign/{$adviceId}/settlement", $headers);
        $settlementRes->assertStatus(200)
            ->assertJson(['status' => 'success']);
        $this->assertArrayHasKey('settlement', $settlementRes->json('data'));
        $settlement = $settlementRes->json('settlement');
        $this->assertTrue($settlement['timeline']['is_advice_to_resign']);
        $this->assertEquals('advice_to_resign', $settlement['timeline']['resignation_rule']);
        $this->assertCount(1, $settlement['notice_earnings']['breakdown']);
        $this->assertStringContainsString('without 1-month notice requirement', $settlement['timeline']['rule_description']);
        $this->assertArrayHasKey('clearance_workflow', $settlement);
        $this->assertEquals(1, $settlement['clearance_workflow']['hr_approval']['status']);

        // 6. Step 3: Audit Head Reviews and Approves Settlement
        $auditRes = $this->postJson("/api/nextjs/advice-to-resign/{$adviceId}/audit-review", [
            'action' => 'approve',
            'audit_remarks' => 'All accounts reconciled and approved for payment disbursement.',
        ], $headers);
        $auditRes->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $auditedAdvice = DB::table('advice_to_resigns')->where('id', $adviceId)->first();
        $this->assertEquals('audit_approved', $auditedAdvice->status);
        $this->assertEquals(1, (int)$auditedAdvice->audit_status);

        // 7. Step 4: Finance Head Pays Settlement
        $financeRes = $this->postJson("/api/nextjs/advice-to-resign/{$adviceId}/finance-pay", [
            'payment_reference' => 'TXN-IHL-EXIT-9988',
            'payment_date' => now()->format('Y-m-d'),
            'finance_remarks' => 'Paid via Central Bank NIBSS transfer.',
        ], $headers);
        $financeRes->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $paidAdvice = DB::table('advice_to_resigns')->where('id', $adviceId)->first();
        $this->assertEquals('paid', $paidAdvice->status);
        $this->assertEquals(1, (int)$paidAdvice->finance_status);
        $this->assertEquals('TXN-IHL-EXIT-9988', $paidAdvice->payment_reference);

        // Verify synced with linked resignation_requests
        $linkedResignation = DB::table('resignation_requests')->where('id', $paidAdvice->resignation_request_id)->first();
        $this->assertEquals(1, (int)$linkedResignation->audit_status);
        $this->assertEquals(1, (int)$linkedResignation->finance_status);
        $this->assertEquals('TXN-IHL-EXIT-9988', $linkedResignation->payment_reference);
    }
}
