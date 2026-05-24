<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmployeeLoanApiTest extends TestCase
{
    use DatabaseTransactions;

    protected $adminUserId = 9991;
    protected $hodUserId = 9992;
    protected $auditUserId = 9993;
    protected $staff1UserId = 9994;
    protected $staff2UserId = 9995;

    protected $adminStaffId;
    protected $hodStaffId;
    protected $auditStaffId;
    protected $staff1Id;
    protected $staff2Id;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTestData();
    }

    private function setUpTestData()
    {
        // 1. Create unique departments for testing (no timestamps)
        $deptId1 = DB::table('tbldepartment')->insertGetId([
            'department' => 'Test Department A'
        ]);
        $deptId2 = DB::table('tbldepartment')->insertGetId([
            'department' => 'Test Department B'
        ]);

        // 2. Insert test users
        DB::table('users')->insert([
            ['id' => $this->adminUserId, 'username' => 'test_admin', 'email' => 'admin@test.com', 'password' => bcrypt('password')],
            ['id' => $this->hodUserId, 'username' => 'test_hod', 'email' => 'hod@test.com', 'password' => bcrypt('password')],
            ['id' => $this->auditUserId, 'username' => 'test_audit', 'email' => 'audit@test.com', 'password' => bcrypt('password')],
            ['id' => $this->staff1UserId, 'username' => 'test_staff1', 'email' => 'staff1@test.com', 'password' => bcrypt('password')],
            ['id' => $this->staff2UserId, 'username' => 'test_staff2', 'email' => 'staff2@test.com', 'password' => bcrypt('password')],
        ]);

        // 3. Insert matching tblper records
        $this->adminStaffId = DB::table('tblper')->insertGetId([
            'UserID' => $this->adminUserId,
            'surname' => 'Admin',
            'first_name' => 'User',
            'departmentID' => $deptId1,
            'rank' => 1,
            'is_hod' => 0
        ]);

        $this->hodStaffId = DB::table('tblper')->insertGetId([
            'UserID' => $this->hodUserId,
            'surname' => 'Hod',
            'first_name' => 'User',
            'departmentID' => $deptId1,
            'rank' => 1,
            'is_hod' => 1
        ]);

        $this->auditStaffId = DB::table('tblper')->insertGetId([
            'UserID' => $this->auditUserId,
            'surname' => 'Audit',
            'first_name' => 'User',
            'departmentID' => $deptId2,
            'rank' => 1,
            'is_hod' => 0
        ]);

        $this->staff1Id = DB::table('tblper')->insertGetId([
            'UserID' => $this->staff1UserId,
            'surname' => 'StaffOne',
            'first_name' => 'User',
            'departmentID' => $deptId1, // Same department as HOD
            'rank' => 1,
            'is_hod' => 0
        ]);

        $this->staff2Id = DB::table('tblper')->insertGetId([
            'UserID' => $this->staff2UserId,
            'surname' => 'StaffTwo',
            'first_name' => 'User',
            'departmentID' => $deptId2, // Different department
            'rank' => 1,
            'is_hod' => 0
        ]);

        // 4. Set roles in assign_user_role (no timestamps)
        DB::table('assign_user_role')->insert([
            ['userID' => $this->adminUserId, 'roleID' => 1], // Super Admin
            ['userID' => $this->auditUserId, 'roleID' => 35], // Audit Staff
        ]);
    }

    /**
     * Test get staff list constraints.
     */
    public function test_get_staff_list_filtering()
    {
        // Admin should see all staff
        $resAdmin = $this->getJson('/api/nextjs/payroll/loans/staff', ['X-User-Id' => $this->adminUserId]);
        $resAdmin->assertStatus(200);
        $dataAdmin = $resAdmin->json('data');
        $this->assertGreaterThan(1, count($dataAdmin));

        // Regular staff should only see themselves
        $resStaff = $this->getJson('/api/nextjs/payroll/loans/staff', ['X-User-Id' => $this->staff1UserId]);
        $resStaff->assertStatus(200);
        $dataStaff = $resStaff->json('data');
        $this->assertCount(1, $dataStaff);
        $this->assertEquals($this->staff1Id, $dataStaff[0]['id']);
    }

    /**
     * Test loan retrieval filtering by roles.
     */
    public function test_loan_index_filtering()
    {
        // Insert sample loans
        $loan1Id = DB::table('employee_loans')->insertGetId([
            'staffId' => $this->staff1Id,
            'loan_type' => 'Personal Loan',
            'loan_amount' => 50000.00,
            'balance' => 50000.00,
            'monthly_deduction' => 5000.00,
            'status' => 'pending'
        ]);

        $loan2Id = DB::table('employee_loans')->insertGetId([
            'staffId' => $this->staff2Id,
            'loan_type' => 'Car Loan',
            'loan_amount' => 200000.00,
            'balance' => 200000.00,
            'monthly_deduction' => 20000.00,
            'status' => 'pending'
        ]);

        // 1. Admin/Audit sees all
        $resAdmin = $this->getJson('/api/nextjs/payroll/loans', ['X-User-Id' => $this->adminUserId]);
        $resAdmin->assertStatus(200);
        $loanIdsAdmin = array_column($resAdmin->json('data'), 'id');
        $this->assertContains($loan1Id, $loanIdsAdmin);
        $this->assertContains($loan2Id, $loanIdsAdmin);

        // 2. HOD (department A) sees staff 1's loan but not staff 2's
        $resHod = $this->getJson('/api/nextjs/payroll/loans', ['X-User-Id' => $this->hodUserId]);
        $resHod->assertStatus(200);
        $loanIdsHod = array_column($resHod->json('data'), 'id');
        $this->assertContains($loan1Id, $loanIdsHod);
        $this->assertNotContains($loan2Id, $loanIdsHod);

        // 3. Staff 1 sees only their own loan
        $resStaff = $this->getJson('/api/nextjs/payroll/loans', ['X-User-Id' => $this->staff1UserId]);
        $resStaff->assertStatus(200);
        $loanIdsStaff = array_column($resStaff->json('data'), 'id');
        $this->assertContains($loan1Id, $loanIdsStaff);
        $this->assertNotContains($loan2Id, $loanIdsStaff);
    }

    /**
     * Test apply constraints.
     */
    public function test_store_loan_restrictions()
    {
        // 1. Staff 1 tries to apply for Staff 2 (should be blocked)
        $resBlocked = $this->postJson('/api/nextjs/payroll/loans', [
            'staffId' => $this->staff2Id,
            'loan_type' => 'Medical Loan',
            'loan_amount' => 30000,
            'monthly_deduction' => 3000
        ], ['X-User-Id' => $this->staff1UserId]);
        $resBlocked->assertStatus(403);

        // 2. Staff 1 applies for themselves (should succeed)
        $resSuccess = $this->postJson('/api/nextjs/payroll/loans', [
            'staffId' => $this->staff1Id,
            'loan_type' => 'Medical Loan',
            'loan_amount' => 30000,
            'monthly_deduction' => 3000
        ], ['X-User-Id' => $this->staff1UserId]);
        $resSuccess->assertStatus(200);
        $this->assertDatabaseHas('employee_loans', [
            'staffId' => $this->staff1Id,
            'loan_type' => 'Medical Loan',
            'status' => 'pending'
        ]);
    }

    /**
     * Test the sequential approval stages.
     */
    public function test_loan_approval_stages_flow()
    {
        // Create a pending loan for Staff 1
        $loanId = DB::table('employee_loans')->insertGetId([
            'staffId' => $this->staff1Id,
            'loan_type' => 'Personal Loan',
            'loan_amount' => 100000.00,
            'balance' => 100000.00,
            'monthly_deduction' => 10000.00,
            'status' => 'pending'
        ]);

        // ── Stage 1: HOD Approve/Reject ───────────────────────────────────────
        // HOD of department B tries to approve Staff 1 (dept A) -> denied
        $this->getJson("/api/nextjs/payroll/loans/hod-approve/{$loanId}", ['X-User-Id' => $this->auditUserId]) // Audit is dept B, isHod=0
            ->assertStatus(401);

        // HOD of dept A approves -> success
        $this->getJson("/api/nextjs/payroll/loans/hod-approve/{$loanId}", ['X-User-Id' => $this->hodUserId])
            ->assertStatus(200);
        $this->assertDatabaseHas('employee_loans', ['id' => $loanId, 'status' => 'recommended']);

        // ── Stage 2: HR/Admin Approve/Reject ──────────────────────────────────
        // HOD tries to HR approve -> denied
        $this->getJson("/api/nextjs/payroll/loans/admin-approve/{$loanId}", ['X-User-Id' => $this->hodUserId])
            ->assertStatus(401);

        // Admin approves -> success
        $this->getJson("/api/nextjs/payroll/loans/admin-approve/{$loanId}", ['X-User-Id' => $this->adminUserId])
            ->assertStatus(200);
        $this->assertDatabaseHas('employee_loans', ['id' => $loanId, 'status' => 'hr_approved']);

        // ── Stage 3: Audit Final Approve/Reject ───────────────────────────────
        // HOD tries to Audit approve -> denied
        $this->getJson("/api/nextjs/payroll/loans/audit-approve/{$loanId}", ['X-User-Id' => $this->hodUserId])
            ->assertStatus(401);

        // Audit approves -> success
        $this->getJson("/api/nextjs/payroll/loans/audit-approve/{$loanId}", ['X-User-Id' => $this->auditUserId])
            ->assertStatus(200);
        $this->assertDatabaseHas('employee_loans', ['id' => $loanId, 'status' => 'approved']);
    }

    /**
     * Test loan deletion restrictions.
     */
    public function test_destroy_loan_restrictions()
    {
        // 1. Create a recommended loan
        $loanId = DB::table('employee_loans')->insertGetId([
            'staffId' => $this->staff1Id,
            'loan_type' => 'Personal Loan',
            'loan_amount' => 100000.00,
            'balance' => 100000.00,
            'monthly_deduction' => 10000.00,
            'status' => 'recommended'
        ]);

        // Regular staff tries to delete their processed (recommended) loan -> blocked
        $this->deleteJson("/api/nextjs/payroll/loans/{$loanId}", [], ['X-User-Id' => $this->staff1UserId])
            ->assertStatus(403);

        // Admin can delete it -> success
        $this->deleteJson("/api/nextjs/payroll/loans/{$loanId}", [], ['X-User-Id' => $this->adminUserId])
            ->assertStatus(200);
        $this->assertDatabaseMissing('employee_loans', ['id' => $loanId]);
    }

    /**
     * Test case-insensitivity of approval stage checks.
     */
    public function test_loan_status_case_insensitivity()
    {
        // 1. Insert loan with status 'PENDING'
        $loanId = DB::table('employee_loans')->insertGetId([
            'staffId' => $this->staff1Id,
            'loan_type' => 'Personal Loan',
            'loan_amount' => 50000.00,
            'balance' => 50000.00,
            'monthly_deduction' => 5000.00,
            'status' => 'PENDING'
        ]);

        // HOD approves 'PENDING' status -> success
        $this->getJson("/api/nextjs/payroll/loans/hod-approve/{$loanId}", ['X-User-Id' => $this->hodUserId])
            ->assertStatus(200);
        $this->assertDatabaseHas('employee_loans', ['id' => $loanId, 'status' => 'recommended']);

        // 2. Set status to 'RECOMMENDED'
        DB::table('employee_loans')->where('id', $loanId)->update(['status' => 'RECOMMENDED']);

        // HR approves 'RECOMMENDED' status -> success
        $this->getJson("/api/nextjs/payroll/loans/admin-approve/{$loanId}", ['X-User-Id' => $this->adminUserId])
            ->assertStatus(200);
        $this->assertDatabaseHas('employee_loans', ['id' => $loanId, 'status' => 'hr_approved']);

        // 3. Set status to 'HR_APPROVED'
        DB::table('employee_loans')->where('id', $loanId)->update(['status' => 'HR_APPROVED']);

        // Audit approves 'HR_APPROVED' status -> success
        $this->getJson("/api/nextjs/payroll/loans/audit-approve/{$loanId}", ['X-User-Id' => $this->auditUserId])
            ->assertStatus(200);
        $this->assertDatabaseHas('employee_loans', ['id' => $loanId, 'status' => 'approved']);
    }

    /**
     * Test that staff with an outstanding loan cannot apply for a new one.
     */
    public function test_outstanding_loan_limit()
    {
        // 1. Create an active approved loan with positive balance for staff 1
        DB::table('employee_loans')->insert([
            'staffId' => $this->staff1Id,
            'loan_type' => 'Personal Loan',
            'loan_amount' => 50000.00,
            'balance' => 30000.00,
            'monthly_deduction' => 5000.00,
            'status' => 'approved'
        ]);

        // 2. Try to apply for a new loan for staff 1 -> should fail with 400
        $this->postJson('/api/nextjs/payroll/loans', [
            'staffId' => $this->staff1Id,
            'loan_type' => 'Medical Loan',
            'loan_amount' => 20000.00,
            'monthly_deduction' => 2000.00
        ], ['X-User-Id' => $this->staff1UserId])
            ->assertStatus(400);

        // 3. Clear balance on outstanding loan
        DB::table('employee_loans')->where('staffId', $this->staff1Id)->update(['balance' => 0.00]);

        // 4. Try again -> should succeed now
        $this->postJson('/api/nextjs/payroll/loans', [
            'staffId' => $this->staff1Id,
            'loan_type' => 'Medical Loan',
            'loan_amount' => 20000.00,
            'monthly_deduction' => 2000.00
        ], ['X-User-Id' => $this->staff1UserId])
            ->assertStatus(200);
    }

    /**
     * Test that balance is 0.00 until the loan is approved.
     */
    public function test_zero_balance_until_approved()
    {
        // 1. Apply for a loan (defaults to pending)
        $this->postJson('/api/nextjs/payroll/loans', [
            'staffId' => $this->staff1Id,
            'loan_type' => 'Personal Loan',
            'loan_amount' => 60000.00,
            'balance' => 60000.00, // even if passed in input
            'monthly_deduction' => 6000.00
        ], ['X-User-Id' => $this->staff1UserId])
            ->assertStatus(200);

        // Verify balance in database is 0.00
        $loan = DB::table('employee_loans')->where('staffId', $this->staff1Id)->orderBy('id', 'desc')->first();
        $this->assertEquals(0.00, (float) $loan->balance);

        // 2. Walk through HOD -> HR approved
        $this->getJson("/api/nextjs/payroll/loans/hod-approve/{$loan->id}", ['X-User-Id' => $this->hodUserId])->assertStatus(200);
        $this->getJson("/api/nextjs/payroll/loans/admin-approve/{$loan->id}", ['X-User-Id' => $this->adminUserId])->assertStatus(200);

        // Balance should still be 0.00
        $loanAfterHR = DB::table('employee_loans')->where('id', $loan->id)->first();
        $this->assertEquals(0.00, (float) $loanAfterHR->balance);

        // 3. Final Audit approval
        $this->getJson("/api/nextjs/payroll/loans/audit-approve/{$loan->id}", ['X-User-Id' => $this->auditUserId])->assertStatus(200);

        // Verify balance in database is updated to the loan amount (60000.00)
        $loanApproved = DB::table('employee_loans')->where('id', $loan->id)->first();
        $this->assertEquals(60000.00, (float) $loanApproved->balance);
    }
}
