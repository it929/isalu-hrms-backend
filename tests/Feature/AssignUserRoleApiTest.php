<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AssignUserRoleApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_assign_user_role_api_endpoints()
    {
        // Get user context ID
        $contextUser = DB::table('tblper')->first();
        if (!$contextUser) {
            $this->markTestSkipped('No user found in tblper to run this test');
            return;
        }

        $userId = $contextUser->UserID ?? 1;

        // Ensure user exists in users table too
        $userExists = DB::table('users')->where('id', $userId)->exists();
        if (!$userExists) {
            DB::table('users')->insertOrIgnore([
                'id' => $userId,
                'name' => 'Context Test User',
                'username' => 'context_test_user',
                'password' => bcrypt('password'),
                'user_type' => 'NONTECHNICAL',
                'created_at' => now(),
            ]);
        }

        // Add role assignments so the request context detects isSuperAdmin
        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $userId,
            'roleID' => 1, // Super Admin
        ]);

        $headers = ['X-User-Id' => $userId];

        // Create a test role
        $roleId = DB::table('user_role')->insertGetId([
            'rolename' => 'Test Assignment Role',
            'created_at' => now(),
        ]);

        // Create a test target user (must not be TECHNICAL or empty user_type)
        $targetUserId = DB::table('users')->insertGetId([
            'name' => 'Target Assignee',
            'username' => 'target_assignee',
            'password' => bcrypt('password'),
            'user_type' => 'NONTECHNICAL',
            'created_at' => now(),
        ]);

        // 1. Fetch metadata
        $response = $this->getJson('/api/nextjs/user-assign/metadata', $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonStructure([
                'status',
                'roles',
                'users'
            ]);

        // Verify target user is in the list
        $usersList = $response->json('users');
        $found = false;
        foreach ($usersList as $u) {
            if ($u['id'] === (int)$targetUserId) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Target assignee should be returned in metadata users list');

        // 2. Fetch assignments
        $response = $this->getJson('/api/nextjs/user-assign/assignments', $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonStructure([
                'status',
                'data',
                'total',
                'perPage',
                'page',
                'lastPage'
            ]);

        // 3. Save new assignment
        $response = $this->postJson('/api/nextjs/user-assign/assign', [
            'userID' => $targetUserId,
            'roleID' => $roleId,
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'User was Successfully assigned to a role']);

        $this->assertDatabaseHas('assign_user_role', [
            'userID' => $targetUserId,
            'roleID' => $roleId,
        ]);

        // 4. Fetch assignments again with search filter to verify it is returned
        $response = $this->getJson("/api/nextjs/user-assign/assignments?search=Target", $headers);
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals((int)$targetUserId, $data[0]['userID']);
        $this->assertEquals((int)$roleId, $data[0]['roleID']);

        // 5. Update assignment to a different role
        $newRoleId = DB::table('user_role')->insertGetId([
            'rolename' => 'Second Assignment Role',
            'created_at' => now(),
        ]);

        $response = $this->postJson('/api/nextjs/user-assign/assign', [
            'userID' => $targetUserId,
            'roleID' => $newRoleId,
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'User was Successfully assigned to a role']);

        // Verify database updated
        $this->assertDatabaseHas('assign_user_role', [
            'userID' => $targetUserId,
            'roleID' => $newRoleId,
        ]);
        $this->assertDatabaseMissing('assign_user_role', [
            'userID' => $targetUserId,
            'roleID' => $roleId,
        ]);

        // 6. Validation error check
        $response = $this->postJson('/api/nextjs/user-assign/assign', [
            'userID' => 999999, // Non-existent user
            'roleID' => $newRoleId,
        ], $headers);
        $response->assertStatus(422)
            ->assertJson(['status' => 'error']);
    }
}
