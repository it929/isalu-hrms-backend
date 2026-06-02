<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AssignModuleRoleApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_assign_module_role_api_endpoints()
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

        // Create a module and submodule for assigning
        $moduleId = DB::table('module')->insertGetId([
            'modulename' => 'Test Assign Module',
            'module_rank' => 99,
            'link_type' => 'HR',
            'created_at' => now(),
        ]);

        $submoduleId = DB::table('submodule')->insertGetId([
            'moduleID' => $moduleId,
            'submodulename' => 'Test Assign Submodule',
            'route' => 'test-assign-submodule',
            'sub_module_rank' => 1,
            'created_at' => now(),
        ]);

        // Get a test user role ID
        $roleId = DB::table('user_role')->insertGetId([
            'rolename' => 'Test Assignment Role',
            'created_at' => now(),
        ]);

        // 1. Fetch metadata
        $response = $this->getJson('/api/nextjs/assign-module/metadata', $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // 2. Fetch assignments
        $response = $this->getJson("/api/nextjs/assign-module/assignments/{$roleId}", $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'assignments' => []]);

        // 3. Save new assignments
        $response = $this->postJson('/api/nextjs/assign-module/assign', [
            'roleID' => $roleId,
            'submoduleIDs' => [$submoduleId],
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Module Assigned Successfully']);

        $this->assertDatabaseHas('assign_module_role', [
            'roleID' => $roleId,
            'submoduleID' => $submoduleId,
            'moduleID' => $moduleId,
        ]);

        // 4. Fetch assignments again to verify they are returned
        $response = $this->getJson("/api/nextjs/assign-module/assignments/{$roleId}", $headers);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'assignments' => [$submoduleId]
            ]);

        // 5. Save empty assignments (should clear)
        $response = $this->postJson('/api/nextjs/assign-module/assign', [
            'roleID' => $roleId,
            'submoduleIDs' => [],
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Module Assigned Successfully']);

        $this->assertDatabaseMissing('assign_module_role', [
            'roleID' => $roleId,
            'submoduleID' => $submoduleId,
        ]);
    }
}
