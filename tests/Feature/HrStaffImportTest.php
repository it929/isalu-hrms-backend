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
        // Get or seed a custom staff role to verify dynamic lookup (and not hardcoded ID 2)
        $staffRole = DB::table('user_role')
            ->whereRaw('LOWER(rolename) = ?', ['staff'])
            ->first();
        if ($staffRole) {
            $customRoleId = $staffRole->roleID;
        } else {
            $customRoleId = DB::table('user_role')->insertGetId([
                'rolename' => 'sTaFf',
                'created_at' => now(),
            ]);
        }

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

        // 6. Verify dynamic role ID is assigned in assign_user_role
        $this->assertDatabaseHas('assign_user_role', [
            'userID' => $user->id,
            'roleID' => $customRoleId
        ]);

        unlink($tempFile);
    }

    /**
     * Test submitDocumentation updates users if they already exist,
     * and creates them if they don't, using staff ID as username.
     */
    public function test_submit_documentation_creates_or_updates_user()
    {
        // Get or seed a custom staff role to verify dynamic lookup (and not hardcoded ID 2)
        $staffRole = DB::table('user_role')
            ->whereRaw('LOWER(rolename) = ?', ['staff'])
            ->first();
        if ($staffRole) {
            $customRoleId = $staffRole->roleID;
        } else {
            $customRoleId = DB::table('user_role')->insertGetId([
                'rolename' => 'sTaFf',
                'created_at' => now(),
            ]);
        }

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

        $user1 = DB::table('users')->where('username', (string)$staff1Id)->first();
        $this->assertDatabaseHas('assign_user_role', [
            'userID' => $user1->id,
            'roleID' => $customRoleId
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

        $this->assertDatabaseHas('assign_user_role', [
            'userID' => $userId,
            'roleID' => $customRoleId
        ]);
    }

    /**
     * Test POST /api/nextjs/hr/documentation/{id}/contact updates users table email too.
     */
    public function test_save_contact_updates_users_table_email()
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'SYNC TEST',
            'username' => '7777',
            'email' => 'old-email@test.com',
            'password' => bcrypt('12345'),
            'user_type' => 'staff',
            'courtID' => 9
        ]);

        $staffId = DB::table('tblper')->insertGetId([
            'ID' => 7777,
            'fileNo' => 'SYNC1',
            'surname' => 'SYNC',
            'first_name' => 'TEST',
            'rank' => 0,
            'staff_status' => 0,
            'email' => 'old-email@test.com',
            'UserID' => $userId
        ]);

        $response = $this->postJson("/api/nextjs/hr/documentation/{$staffId}/contact", [
            'email' => 'new-email@test.com',
            'alternate_email' => 'alt@test.com',
            'phone' => '1234567890',
            'alternate_phone' => '0987654321',
            'home_address' => '456 main st'
        ]);

        $response->assertStatus(200);

        // Verify users table email was updated
        $this->assertDatabaseHas('users', [
            'id' => $userId,
            'email' => 'new-email@test.com'
        ]);

        // Verify tblper table email was updated
        $this->assertDatabaseHas('tblper', [
            'ID' => $staffId,
            'email' => 'new-email@test.com'
        ]);
    }

    /**
     * Test store method rejects duplicate email addresses.
     */
    public function test_store_rejects_duplicate_email()
    {
        // Insert a staff first with an email
        $deptId = DB::table('tbldepartment')->insertGetId(['department' => 'Store Test Dept']);
        $unitId = DB::table('tblunits')->insertGetId(['unit' => 'Store Test Unit', 'departmentID' => $deptId]);
        $desigId = DB::table('tbldesignation')->insertGetId(['designation' => 'Store Test Designation', 'departmentID' => $deptId]);

        DB::table('tblper')->insert([
            'fileNo' => 'E001',
            'surname' => 'FIRST',
            'first_name' => 'STAFF',
            'email' => 'taken@test.com',
            'departmentID' => $deptId,
            'unitID' => $unitId,
            'designationID' => $desigId,
            'rank' => 0,
            'staff_status' => 0,
        ]);

        // Attempt to create another staff with the same email
        $response = $this->postJson('/api/nextjs/hr/add-staff', [
            'title' => 'MR.',
            'surname' => 'SECOND',
            'firstname' => 'STAFF',
            'email' => 'taken@test.com',
            'sex' => 'Male',
            'maritalStatus' => 'Single',
            'department_id' => $deptId,
            'unit_id' => $unitId,
            'designation_id' => $desigId,
            'date_of_birth' => '1995-05-05',
            'date_of_joining' => '2026-06-15',
            'address' => '123 Test St',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test saveContact rejects duplicate email addresses.
     */
    public function test_save_contact_rejects_duplicate_email()
    {
        $deptId = DB::table('tbldepartment')->insertGetId(['department' => 'Contact Test Dept']);

        // Insert first staff with email 'taken@test.com'
        $staff1Id = DB::table('tblper')->insertGetId([
            'fileNo' => 'C001',
            'surname' => 'STAFF',
            'first_name' => 'ONE',
            'email' => 'taken@test.com',
            'departmentID' => $deptId,
            'rank' => 0,
            'staff_status' => 0,
        ]);

        // Insert second staff with email 'other@test.com'
        $staff2Id = DB::table('tblper')->insertGetId([
            'fileNo' => 'C002',
            'surname' => 'STAFF',
            'first_name' => 'TWO',
            'email' => 'other@test.com',
            'departmentID' => $deptId,
            'rank' => 0,
            'staff_status' => 0,
        ]);

        // Try updating staff2 with staff1's email 'taken@test.com' -> should fail
        $response = $this->postJson("/api/nextjs/hr/documentation/{$staff2Id}/contact", [
            'email' => 'taken@test.com',
            'alternate_email' => 'alt@test.com',
            'phone' => '1234567890',
            'alternate_phone' => '0987654321',
            'home_address' => '456 main st'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);

        // Try updating staff2 with their own email 'other@test.com' -> should succeed
        $response2 = $this->postJson("/api/nextjs/hr/documentation/{$staff2Id}/contact", [
            'email' => 'other@test.com',
            'alternate_email' => 'alt@test.com',
            'phone' => '1234567890',
            'alternate_phone' => '0987654321',
            'home_address' => '456 main st'
        ]);

        $response2->assertStatus(200);
    }

    /**
     * Test bulk import skips rows with duplicate emails.
     */
    public function test_import_skips_duplicate_emails()
    {
        $deptId = DB::table('tbldepartment')->insertGetId(['department' => 'Import Dup Dept']);
        $unitId = DB::table('tblunits')->insertGetId(['unit' => 'Import Dup Unit', 'departmentID' => $deptId]);
        $desigId = DB::table('tbldesignation')->insertGetId(['designation' => 'Import Dup Designation', 'departmentID' => $deptId]);

        // Seed a staff with the email first
        DB::table('tblper')->insert([
            'fileNo' => 'E002',
            'surname' => 'EXISTING',
            'first_name' => 'STAFF',
            'email' => 'dup@test.com',
            'departmentID' => $deptId,
            'unitID' => $unitId,
            'designationID' => $desigId,
            'rank' => 0,
            'staff_status' => 0,
        ]);

        // Prepare mock CSV data containing duplicate email
        $csvContent = "staffID,title,surname,firstname,othernames,sex,maritalStatus,date_of_birth,phoneNo,email,address,department,unit,designation,date_of_joining,iou\n";
        $csvContent .= "9901,MR.,SURE,STAFF1,MIDDLE,Male,Single,1992-04-05,08099887766,dup@test.com,123 import st,{$deptId},{$unitId},{$desigId},2026-06-08,10000.00\n";
        $csvContent .= "9902,MR.,SURE,STAFF2,MIDDLE,Male,Single,1992-04-05,08099887766,unique@test.com,123 import st,{$deptId},{$unitId},{$desigId},2026-06-08,10000.00\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'staff_csv');
        file_put_contents($tempFile, $csvContent);

        $uploadedFile = new UploadedFile(
            $tempFile,
            'import_staff_dup.csv',
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

        // Verify the warning is present
        $this->assertStringContainsString("Email 'dup@test.com' is already taken. Skipping row.", json_encode($response->json('warnings')));

        // Verify 9902 was imported
        $this->assertDatabaseHas('tblper', ['ID' => 9902, 'email' => 'unique@test.com']);
        // Verify 9901 was NOT imported
        $this->assertDatabaseMissing('tblper', ['ID' => 9901]);

        unlink($tempFile);
    }
}
