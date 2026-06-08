<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;

class HrStaffImportTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test POST /api/nextjs/hr/add-staff/import creates users with staff ID as username.
     */
    public function test_staff_import_creates_users_successfully()
    {
        // 1. Set up required lookup values (department, unit, designation)
        $deptId = DB::table('tbldepartment')->insertGetId([
            'department' => 'HR Import Test Department'
        ]);

        $unitId = DB::table('tblunits')->insertGetId([
            'unit' => 'HR Import Test Unit',
            'departmentID' => $deptId
        ]);

        $desigId = DB::table('tbldesignation')->insertGetId([
            'designation' => 'HR Import Test Designation',
            'departmentID' => $deptId
        ]);

        // 2. Prepare mock CSV data
        $csvContent = "staffID,title,surname,firstname,othernames,sex,maritalStatus,date_of_birth,phoneNo,email,address,department,unit,designation,date_of_joining,iou\n";
        $csvContent .= "9899,MR.,SURE,STAFF,MIDDLE,Male,Single,1992-04-05,08099887766,sure.staff@test.com,123 import st,{$deptId},{$unitId},{$desigId},2026-06-08,10000.00\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'staff_csv');
        file_put_contents($tempFile, $csvContent);

        $uploadedFile = new UploadedFile(
            $tempFile,
            'import_staff.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->postJson('/api/nextjs/hr/add-staff/import', [
            'excel_file' => $uploadedFile
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'imported_count' => 1
            ]);

        // 3. Verify tblper record exists with ID = 9899
        $staff = DB::table('tblper')->where('ID', 9899)->first();
        $this->assertNotNull($staff);

        // 4. Verify user record exists in users table with username = staff ID (9899)
        $this->assertDatabaseHas('users', [
            'username' => '9899',
            'email' => 'sure.staff@test.com',
            'user_type' => 'staff',
            'courtID' => 9
        ]);

        // 5. Verify UserID link exists in tblper
        $user = DB::table('users')->where('username', '9899')->first();
        $this->assertEquals($user->id, $staff->UserID);

        // 6. Verify role ID 2 is assigned in assign_user_role
        $this->assertDatabaseHas('assign_user_role', [
            'userID' => $user->id,
            'roleID' => 2
        ]);

        unlink($tempFile);
    }

    /**
     * Test submitDocumentation updates users if they already exist,
     * and creates them if they don't, using staff ID as username.
     */
    public function test_submit_documentation_creates_or_updates_user()
    {
        $deptId = DB::table('tbldepartment')->insertGetId([
            'department' => 'HR Doc Dept'
        ]);

        // Scenario A: User does NOT exist in users table
        $staff1Id = DB::table('tblper')->insertGetId([
            'fileNo' => 'DOC1',
            'surname' => 'UNSYNCED',
            'first_name' => 'STAFF',
            'departmentID' => $deptId,
            'rank' => 0,
            'staff_status' => 0,
            'email' => 'unsynced@test.com'
        ]);

        $response = $this->postJson("/api/nextjs/hr/documentation/{$staff1Id}/submit");
        $response->assertStatus(200);

        // Verify user was created in users table
        $this->assertDatabaseHas('users', [
            'username' => (string)$staff1Id,
            'email' => 'unsynced@test.com',
            'user_type' => 'staff'
        ]);

        // Scenario B: User already exists, submit documentation updates it
        $userId = DB::table('users')->insertGetId([
            'name' => 'OLD NAME',
            'username' => (string)9999, // dummy username placeholder
            'email' => 'old@test.com',
            'password' => bcrypt('12345'),
            'user_type' => 'staff',
            'courtID' => 9
        ]);

        $staff2Id = DB::table('tblper')->insertGetId([
            'fileNo' => 'DOC2',
            'surname' => 'NEW',
            'first_name' => 'NAME',
            'departmentID' => $deptId,
            'rank' => 0,
            'staff_status' => 0,
            'email' => 'new@test.com',
            'UserID' => $userId
        ]);

        // Update user username mapping manually to simulate previous state
        DB::table('users')->where('id', $userId)->update(['username' => (string)$staff2Id]);

        $response = $this->postJson("/api/nextjs/hr/documentation/{$staff2Id}/submit");
        $response->assertStatus(200);

        // Verify users table was updated with new name and email
        $this->assertDatabaseHas('users', [
            'id' => $userId,
            'name' => 'NEW NAME',
            'email' => 'new@test.com',
            'username' => (string)$staff2Id,
            'user_type' => 'staff'
        ]);
    }
}
