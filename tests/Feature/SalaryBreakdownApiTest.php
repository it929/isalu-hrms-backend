<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SalaryBreakdownApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_get_salary_breakdown_endpoint()
    {
        $user = DB::table('tblper')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in tblper to run this test');
            return;
        }

        // Add role assignment so user has admin access
        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $user->UserID ?? 1,
            'roleID' => 1,
        ]);

        // Ensure salary structure exists
        DB::table('salary_structures')->updateOrInsert(
            ['staffId' => $user->ID],
            [
                'basic_salary' => 40000.00,
                'housing_allowance' => 40000.00,
                'transport_allowance' => 20000.00,
                'medical_allowance' => 20000.00,
                'utility_allowance' => 40000.00,
                'meal_allowance' => 40000.00,
                'declare_salary' => 200000.00,
                'tax_rate' => 0.00,
                'pension_rate' => 8.00,
                'pen_act' => 1
            ]
        );

        $headers = ['X-User-Id' => $user->UserID ?? 1];

        // 1. Test getStaffList endpoint
        $staffListResponse = $this->getJson('/api/nextjs/payroll/salary-breakdown/staff', $headers);
        $staffListResponse->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // 2. Test getBreakdown endpoint for active month
        $response = $this->getJson('/api/nextjs/payroll/salary-breakdown?staff_id=' . $user->ID, $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonStructure([
                'status',
                'staff' => ['id', 'file_no', 'name', 'department', 'designation'],
                'period' => ['month', 'year', 'month_name', 'period_str', 'is_computed'],
                'earnings' => ['basic_salary', 'housing_allowance', 'transport_allowance', 'medical_allowance', 'utility_allowance', 'meal_allowance', 'gross_pay'],
                'deductions' => ['paye_tax', 'pension', 'retention', 'iou', 'medical_loan', 'coop_loan', 'total_deductions'],
                'summary' => ['gross_pay', 'total_deductions', 'net_pay', 'status']
            ]);

        $data = $response->json();
        $this->assertEquals(200000.00, $data['earnings']['gross_pay']);
        $this->assertGreaterThanOrEqual(0, $data['summary']['net_pay']);
        $this->assertTrue($data['can_generate_all_staff']);

        // 3. Test getAllStaffSheet endpoint
        $allStaffResponse = $this->getJson('/api/nextjs/payroll/salary-breakdown/all-staff', $headers);
        $allStaffResponse->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id', 'name', 'department', 'designation', 'basic_salary',
                        'gross_pay', 'paye_tax', 'pension', 'total_deductions', 'net_pay'
                    ]
                ],
                'summary' => ['total_staff', 'total_gross', 'total_deductions', 'total_net_pay', 'status'],
                'period' => ['month', 'year', 'month_name', 'period_str'],
                'departments'
            ]);

        // 4. Test exportAllStaffSheet endpoint
        $exportResponse = $this->get('/api/nextjs/payroll/salary-breakdown/all-staff/export', $headers);
        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('Content-Type'));

        // Verify CSV content has headers
        $csvContent = $exportResponse->streamedContent();
        $this->assertStringContainsString('IDNO,NAME,DEPARTMENT', $csvContent);
        $this->assertStringContainsString('ABSENCE PENALTY', $csvContent);
        $this->assertStringContainsString('LOA.DEDN', $csvContent);
        $this->assertStringContainsString('OTHER DEDUCTION', $csvContent);
        $this->assertStringContainsString('TOTAL DEDUCTION', $csvContent);
        $this->assertStringContainsString('NET PAY', $csvContent);
        $this->assertStringContainsString('TOTAL', $csvContent);

        // 5. Test exportStaffSheet endpoint
        $staffExportResponse = $this->get('/api/nextjs/payroll/salary-breakdown/staff/export?staff_id=' . $user->ID, $headers);
        $staffExportResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $staffExportResponse->headers->get('Content-Type'));

        $staffCsvContent = $staffExportResponse->streamedContent();
        $this->assertStringContainsString('IDNO,NAME,DEPARTMENT', $staffCsvContent);
        $this->assertStringContainsString('ABSENCE PENALTY', $staffCsvContent);
        $this->assertStringContainsString('LOA.DEDN', $staffCsvContent);
        $this->assertStringContainsString('OTHER DEDUCTION', $staffCsvContent);
        $this->assertStringContainsString('TOTAL DEDUCTION', $staffCsvContent);
        $this->assertStringContainsString('NET PAY', $staffCsvContent);

        // 6. Test getBreakdown after computation (from payroll_conpt)
        $runId = DB::table('payroll_runs')->insertGetId([
            'month' => 11,
            'year' => 2026,
            'processed_by' => $user->ID,
            'status' => 'completed',
            'created_at' => now(),
        ]);

        DB::table('payroll_conpt')->insert([
            'payroll_run_id' => $runId,
            'staffID' => $user->ID,
            'basic' => 40000.00,
            'housing' => 40000.00,
            'transport' => 20000.00,
            'medical' => 20000.00,
            'utility' => 40000.00,
            'meal' => 40000.00,
            'gross_pay' => 200000.00,
            'paye_tax' => 10000.00,
            'pension' => 8000.00,
            'absence_penalty' => 5000.00,
            'leave_of_absence_deduction' => 6666.67,
            'other_deductions' => 7000.00,
            'total_deductions' => 36666.67,
            'net_pay' => 163333.33,
            'paid_days' => 29,
            'created_at' => now(),
        ]);

        $computedResponse = $this->getJson('/api/nextjs/payroll/salary-breakdown?staff_id=' . $user->ID . '&month=11&year=2026', $headers);
        $computedResponse->assertStatus(200);
        $computedData = $computedResponse->json();

        $this->assertTrue($computedData['period']['is_computed']);
        $this->assertEquals(5000.00, $computedData['deductions']['absence_penalty']['amount']);
        $this->assertEquals(7000.00, $computedData['deductions']['other_deductions']['amount']);
        $this->assertEquals(6666.67, $computedData['deductions']['leave_of_absence']['amount']);
        $this->assertEquals('Computed Payroll', $computedData['summary']['status']);
    }
}
