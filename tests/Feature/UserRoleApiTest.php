<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserRoleApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_role_api_endpoints()
    {
        // Get user context ID
        $user = DB::table('tblper')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in tblper to run this test');
            return;
        }

        // Add role assignments so the request context detects isSuperAdmin
        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $user->UserID ?? 1,
            'roleID' => 1, // Super Admin
        ]);

        $headers = ['X-User-Id' => $user->UserID ?? 1];

        // 1. Fetch user roles list
        $response = $this->getJson('/api/nextjs/roles', $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // 2. Create a new role
        $roleName = 'Test Custom Role ' . rand(1000, 9999);
        $response = $this->postJson('/api/nextjs/roles', [
            'roleName' => $roleName,
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'New Role Created Successfully']);

        $this->assertDatabaseHas('user_role', [
            'rolename' => $roleName,
        ]);

        // Get the role ID
        $role = DB::table('user_role')->where('rolename', $roleName)->first();
        $this->assertNotNull($role);

        // 3. Get specific role
        $response = $this->getJson("/api/nextjs/roles/{$role->roleID}", $headers);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'roleID' => $role->roleID,
                    'rolename' => $roleName,
                ]
            ]);

        // 4. Update the role
        $updatedRoleName = 'Updated Role ' . rand(1000, 9999);
        $response = $this->postJson("/api/nextjs/roles/update/{$role->roleID}", [
            'roleName' => $updatedRoleName,
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Role Successfully Updated']);

        $this->assertDatabaseHas('user_role', [
            'roleID' => $role->roleID,
            'rolename' => $updatedRoleName,
        ]);

        // 5. Test validation regex rule (invalid characters like @ or #)
        $response = $this->postJson('/api/nextjs/roles', [
            'roleName' => 'Invalid@RoleName#',
        ], $headers);

        $response->assertStatus(422)
            ->assertJson(['status' => 'error', 'message' => 'Validation error']);
    }
}
