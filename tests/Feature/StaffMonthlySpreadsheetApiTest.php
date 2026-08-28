<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StaffMonthlySpreadsheetApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_staff_monthly_spreadsheet_generation()
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
                'basic_salary' => 29000.00,
                'housing_allowance' => 29000.00,
                'transport_allowance' => 14500.00,
                'medical_allowance' => 7250.00,
                'utility_allowance' => 7250.00,
                'meal_allowance' => 7250.00,
                'declare_salary' => 94250.00,
                'tax_rate' => 0.00,
                'pension_rate' => 8.00,
                'pen_act' => 1
            ]
        );

        // Seed computed records for 2025 (12 months)
        for ($m = 1; $m <= 12; $m++) {
            DB::table('payroll_conpt')->insert([
                'payroll_run_id' => 992025,
                'month' => $m,
                'year' => 2025,
                'staffID' => $user->ID,
                'basic' => 29000.00,
                'housing' => 29000.00,
                'transport' => 14500.00,
                'medical' => 7250.00,
                'utility' => 7250.00,
                'meal' => 7250.00,
                'paid_days' => 30,
                'gross_pay' => 94250.00,
                'declare_income' => 94250.00,
                'paye_tax' => 0.00,
                'loan_deduction' => 0.00,
                'pension' => 0.00,
                'coop_savings' => 0.00,
                'other_deductions' => 0.00,
                'total_deductions' => 0.00,
                'net_pay' => 94250.00,
                'created_at' => now(),
            ]);
        }

        // Seed computed records for 2026 months 1 to 8 (Jan to Aug)
        for ($m = 1; $m <= 8; $m++) {
            DB::table('payroll_conpt')->insert([
                'payroll_run_id' => 992026,
                'month' => $m,
                'year' => 2026,
                'staffID' => $user->ID,
                'basic' => 29000.00,
                'housing' => 29000.00,
                'transport' => 14500.00,
                'medical' => 7250.00,
                'utility' => 7250.00,
                'meal' => 7250.00,
                'paid_days' => 30,
                'gross_pay' => 94250.00,
                'declare_income' => 94250.00,
                'paye_tax' => 0.00,
                'loan_deduction' => 0.00,
                'pension' => 0.00,
                'coop_savings' => 0.00,
                'other_deductions' => 0.00,
                'total_deductions' => 0.00,
                'net_pay' => 94250.00,
                'created_at' => now(),
            ]);
        }

        $headers = ['X-User-Id' => $user->UserID ?? 1];

        // 1. Test getStaffMonthlySpreadsheet endpoint for 2025 to 2026
        $response = $this->getJson("/api/nextjs/payroll/staff-spreadsheet?staff_id={$user->ID}&from_year=2025&to_year=2026", $headers);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'from_year' => 2025,
                'to_year' => 2026,
            ]);

        $data = $response->json();
        $this->assertArrayHasKey('years', $data);
        $this->assertCount(2, $data['years']); // 2025 and 2026

        // Check 2025 has 12 months (past year has all 12 computed months)
        $year2025 = $data['years'][0];
        $this->assertEquals(2025, $year2025['year']);
        $this->assertCount(12, $year2025['months']);
        $this->assertEquals('January', $year2025['months'][0]['month_name']);
        $this->assertEquals('December', $year2025['months'][11]['month_name']);

        // Check monthly values
        $jan2025 = $year2025['months'][0];
        $this->assertEquals(29000.00, (float)$jan2025['basic']);
        $this->assertEquals(29000.00, (float)$jan2025['housing']);
        $this->assertEquals(94250.00, (float)$jan2025['gross_salary']);
        $this->assertArrayHasKey('full_breakdown', $jan2025);

        // Check 2025 year totals (12 full months)
        $this->assertArrayHasKey('year_totals', $year2025);
        $this->assertEquals(29000.00 * 12, (float)$year2025['year_totals']['basic']);
        $this->assertEquals(29000.00 * 12, (float)$year2025['year_totals']['housing']);
        $this->assertEquals(94250.00 * 12, (float)$year2025['year_totals']['gross_salary']);

        // Check 2026 future months (Sept to Dec are Upcoming and 0.00 when active month is August 2026)
        $year2026 = $data['years'][1];
        $this->assertEquals(2026, $year2026['year']);
        $months2026 = $year2026['months'];
        $this->assertEquals(29000.00, (float)$months2026[7]['basic']); // August 2026 (index 7, computed record)
        $this->assertEquals(0.00, (float)$months2026[8]['basic']); // September 2026 (index 8) is Upcoming
        $this->assertEquals('Upcoming', $months2026[8]['status']);
        $this->assertEquals(0.00, (float)$months2026[11]['basic']); // December 2026 (index 11) is Upcoming
        $this->assertEquals('Upcoming', $months2026[11]['status']);

        // Check 2026 totals (8 computed months @ 29000 = 232000)
        $this->assertEquals(29000.00 * 8, (float)$year2026['year_totals']['basic']);

        // Check grand totals (12 months @ 29000 + 8 months @ 29000 = 20 months @ 29000 = 580000)
        $this->assertArrayHasKey('grand_totals', $data);
        $this->assertEquals(29000.00 * 20, (float)$data['grand_totals']['basic']);

        // 2. Test exportStaffMonthlySpreadsheet endpoint
        $exportResponse = $this->get("/api/nextjs/payroll/staff-spreadsheet/export?staff_id={$user->ID}&from_year=2025&to_year=2026", $headers);
        $exportResponse->assertStatus(200);
        $this->assertTrue(str_contains($exportResponse->headers->get('content-type'), 'text/csv'));
    }

    public function test_staff_employed_on_may_12_2026_zeros_jan_to_apr_and_prorates_may(): void
    {
        $newStaff = DB::table('tblper')->insertGetId([
            'surname' => 'EGBEYEMI',
            'first_name' => 'KOLAWOLE',
            'othernames' => 'MAY',
            'UserID' => 99912,
            'fileNo' => 'ISL/MAY26',
            'departmentID' => 1,
            'designation' => 1,
            'doj' => '2026-05-12',
            'appointment_date' => '2026-05-12',
            'staff_status' => 1,
            'rank' => 1,
        ]);

        DB::table('salary_structures')->insert([
            'staffId' => $newStaff,
            'basic_salary' => 31000.00,
            'housing_allowance' => 31000.00,
            'transport_allowance' => 0.00,
            'medical_allowance' => 0.00,
            'utility_allowance' => 0.00,
            'meal_allowance' => 0.00,
            'declare_salary' => 62000.00,
            'pen_act' => 0,
        ]);

        // Mock compute for May 2026
        DB::table('payroll_conpt')->insert([
            'payroll_run_id' => 992026,
            'month' => 5,
            'year' => 2026,
            'staffID' => $newStaff,
            'basic' => 31000.00,
            'housing' => 31000.00,
            'transport' => 0.00,
            'medical' => 0.00,
            'utility' => 0.00,
            'meal' => 0.00,
            'paid_days' => 20,
            'gross_pay' => 62000.00,
            'declare_income' => 62000.00,
            'paye_tax' => 0.00,
            'loan_deduction' => 0.00,
            'pension' => 0.00,
            'coop_savings' => 0.00,
            'other_deductions' => 0.00,
            'total_deductions' => 0.00,
            'net_pay' => 40000.00,
            'created_at' => now(),
        ]);

        $adminUser = DB::table('tblper')->first();
        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $adminUser->UserID ?? 1,
            'roleID' => 1,
        ]);

        $headers = [
            'X-User-Id' => $adminUser->UserID ?? 1,
        ];

        $response = $this->get("/api/nextjs/payroll/staff-spreadsheet?staff_id={$newStaff}&from_year=2026&to_year=2026", $headers);
        $response->assertStatus(200);

        $data = $response->json();
        $year2026 = $data['years'][0];
        $months = $year2026['months'];

        // Jan, Feb, Mar, Apr (months index 0 to 3) MUST be 0.00 and 'Not Employed'
        for ($i = 0; $i < 4; $i++) {
            $this->assertEquals(0.00, (float)$months[$i]['basic'], "Month index {$i} basic should be 0");
            $this->assertEquals(0.00, (float)$months[$i]['gross_salary'], "Month index {$i} gross should be 0");
            $this->assertEquals(0.00, (float)$months[$i]['total_deductions'], "Month index {$i} deductions should be 0");
            $this->assertEquals(0.00, (float)$months[$i]['net_pay'], "Month index {$i} net pay should be 0");
            $this->assertEquals(0, $months[$i]['paid_days'], "Month index {$i} paid days should be 0");
            $this->assertEquals('Not Employed', $months[$i]['status']);
        }

        // May 2026 (index 4) - Joined May 12: Computed record present (20 paid days)
        $may = $months[4];
        $this->assertEquals('May', $may['month_name']);
        $this->assertEquals(20, $may['paid_days']);
        $this->assertEquals(62000.00, (float)$may['gross_salary']);
        $this->assertEquals('Computed', $may['status']);

        // June 2026 (index 5) - Past month without compute record -> 'Not Computed' (0.00)
        $june = $months[5];
        $this->assertEquals('June', $june['month_name']);
        $this->assertEquals(0, $june['paid_days']);
        $this->assertEquals(0.00, (float)$june['basic']);
        $this->assertEquals('Not Computed', $june['status']);

        // August 2026 (index 7) - Current active month -> 'Estimate'
        $august = $months[7];
        $this->assertEquals('August', $august['month_name']);
        $this->assertEquals(31, $august['paid_days']);
        $this->assertEquals(31000.00, (float)$august['basic']);
        $this->assertEquals('Estimate', $august['status']);

        // Sept to Dec 2026 (index 8 to 11) - Future months beyond active August 2026 -> 'Upcoming' (0.00)
        for ($i = 8; $i < 12; $i++) {
            $this->assertEquals(0.00, (float)$months[$i]['basic'], "Future month index {$i} basic should be 0");
            $this->assertEquals('Upcoming', $months[$i]['status']);
        }
    }
}
