<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class SuperAdminLoginTest extends TestCase
{
    public function test_inactive_staff_is_blocked_from_logging_in()
    {
        $unique = time() . '_' . rand(100, 999);
        $username = 'staff_inactive_' . $unique;
        $email = 'inactive_' . $unique . '@isalu.test';

        $userId = DB::table('users')->insertGetId([
            'username' => $username,
            'name' => 'Inactive Staff',
            'email' => $email,
            'password' => Hash::make('password123'),
            'user_type' => 'staff',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $staffId = DB::table('tblper')->insertGetId([
            'UserID' => $userId,
            'fileNo' => 'PF-' . $unique,
            'surname' => 'Inactive',
            'first_name' => 'Staff',
            'email' => $email,
            'staff_status' => 0, // INACTIVE
            'rank' => 1,
            'departmentID' => 1,
        ]);

        $response = $this->postJson('/api/nextjs/login', [
            'username' => $username,
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Your staff account is inactive. Inactive staff are not permitted to log in to the application. Please contact HR administration.'
        ]);

        // Cleanup
        DB::table('tblper')->where('ID', $staffId)->delete();
        DB::table('users')->where('id', $userId)->delete();
    }

    public function test_super_admin_can_login_even_if_associated_with_inactive_staff_or_sharing_email()
    {
        $unique = time() . '_' . rand(100, 999);
        $adminUsername = 'superadmin_' . $unique;
        $sharedEmail = 'admin_' . $unique . '@isalu.test';

        // Create Super Admin user
        $adminUserId = DB::table('users')->insertGetId([
            'username' => $adminUsername,
            'name' => 'Super Admin User',
            'email' => $sharedEmail,
            'password' => Hash::make('secretadmin123'),
            'user_type' => 'Technical',
            'is_global' => 1,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $adminUserId,
            'roleID' => 1, // Super Administrator
        ]);

        // Create an inactive staff record with the same email (e.g. resigned staff sharing admin/developer email)
        $inactiveStaffId = DB::table('tblper')->insertGetId([
            'UserID' => 9999988,
            'fileNo' => 'PF-RESIGNED-' . $unique,
            'surname' => 'Resigned',
            'first_name' => 'Staff',
            'email' => $sharedEmail,
            'staff_status' => 0, // INACTIVE
            'rank' => 1,
            'departmentID' => 1,
        ]);

        $response = $this->postJson('/api/nextjs/login', [
            'username' => $adminUsername,
            'password' => 'secretadmin123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'role' => [
                'name' => 'Super Administrator'
            ]
        ]);

        // Cleanup
        DB::table('tblper')->where('ID', $inactiveStaffId)->delete();
        DB::table('assign_user_role')->where('userID', $adminUserId)->delete();
        DB::table('users')->where('id', $adminUserId)->delete();
    }
}
