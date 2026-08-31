<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ResignationSettlementEmailTest extends TestCase
{
    use DatabaseTransactions;

    private function getHeaders($userId)
    {
        return [
            'X-User-Id' => $userId
        ];
    }

    public function test_finance_pay_and_settlement_email_dispatch()
    {
        Mail::fake();

        // 1. Get or create test user (Super Admin)
        $user = DB::table('users')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in DB.');
        }

        DB::table('assign_user_role')->updateOrInsert(
            ['userID' => $user->id, 'roleID' => 1],
            ['created_at' => now()]
        );

        // 2. Get or create a staff with an email
        $staff = DB::table('tblper')->whereNotNull('email')->where('email', '!=', '')->first();
        if (!$staff) {
            $staff = DB::table('tblper')->first();
            if ($staff) {
                DB::table('tblper')->where('ID', $staff->ID)->update(['email' => 'teststaff@isalu.gov.ng']);
                $staff = DB::table('tblper')->where('ID', $staff->ID)->first();
            } else {
                $this->markTestSkipped('No staff record in tblper.');
            }
        }

        $headers = $this->getHeaders($user->id);

        // 3. Create an approved resignation record (HR Head approved, Audit Head approved)
        $resignationId = DB::table('resignation_requests')->insertGetId([
            'staff_id'         => $staff->ID,
            'reason'           => 'Test Exit Settlement Email Trigger',
            'resignation_date' => '2026-08-15',
            'status'           => 1,
            'hod_status'       => 1,
            'hod_id'           => $user->id,
            'hod_date'         => now(),
            'admin_status'     => 1, // HR Approved
            'admin_id'         => $user->id,
            'admin_date'       => now(),
            'audit_status'     => 1, // Audit Approved
            'audit_id'         => $user->id,
            'audit_date'       => now(),
            'finance_status'   => 0, // Pending Finance
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        try {
            // 4. Test GET settlement breakdown
            $responseBreakdown = $this->getJson("/api/nextjs/payroll/resignations/settlement/{$resignationId}", $headers);
            $responseBreakdown->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                ]);

            $this->assertArrayHasKey('salary_structure', $responseBreakdown->json('data'));
            $this->assertArrayHasKey('notice_earnings', $responseBreakdown->json('data'));
            $this->assertArrayHasKey('deductions', $responseBreakdown->json('data'));
            $this->assertArrayHasKey('settlement_summary', $responseBreakdown->json('data'));

            // 5. Test Finance Mark As Paid endpoint (POST /api/nextjs/payroll/resignations/finance-pay/{id})
            $financePayload = [
                'payment_reference' => 'TEST-TRF-2026-001',
                'payment_date'      => now()->format('Y-m-d'),
                'remarks'           => 'Test exit payment disbursed and verified.',
            ];

            $responsePay = $this->postJson("/api/nextjs/payroll/resignations/finance-pay/{$resignationId}", $financePayload, $headers);
            $responsePay->assertStatus(200)
                ->assertJson([
                    'status'     => 'success',
                    'email_sent' => true,
                ]);

            // Verify DB updated
            $updatedRecord = DB::table('resignation_requests')->where('id', $resignationId)->first();
            $this->assertEquals(1, $updatedRecord->finance_status);
            $this->assertEquals('TEST-TRF-2026-001', $updatedRecord->payment_reference);

            // 6. Test manual send settlement email endpoint (with PDF attachment)
            $responseEmail = $this->postJson("/api/nextjs/payroll/resignations/settlement/{$resignationId}/send-email", [], $headers);
            $responseEmail->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                ]);

            // 7. Test direct Download PDF Slip endpoint
            $responsePdf = $this->get("/api/nextjs/payroll/resignations/settlement/{$resignationId}/download-pdf", $headers);
            $responsePdf->assertStatus(200);
            $this->assertEquals('application/pdf', $responsePdf->headers->get('content-type'));
            $this->assertStringContainsString('.pdf', $responsePdf->headers->get('content-disposition'));
        } finally {
            DB::table('resignation_requests')->where('id', $resignationId)->delete();
        }
    }
}
