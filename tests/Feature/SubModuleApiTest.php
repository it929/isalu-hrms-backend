<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SubModuleApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_submodule_api_endpoints()
    {
        // Get user context ID
        $user = DB::table('tblper')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in tblper to run this test');
            return;
        }

        // Create or get a module to associate the submodule with
        $moduleName = 'Test Module ' . rand(1000, 9999);
        $moduleId = DB::table('module')->insertGetId([
            'modulename' => $moduleName,
            'module_rank' => 99,
            'link_type' => 'HR',
            'created_at' => now(),
        ]);

        // Add role assignments so the request context detects isSuperAdmin
        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $user->UserID ?? 1,
            'roleID' => 1, // Super Admin
        ]);

        $headers = ['X-User-Id' => $user->UserID ?? 1];

        // 1. Fetch submodules list
        $response = $this->getJson('/api/nextjs/submodules', $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // 2. Create a new submodule
        $subModuleName = 'Test Submodule ' . rand(1000, 9999);
        $response = $this->postJson('/api/nextjs/submodules', [
            'moduleID' => $moduleId,
            'subModuleName' => $subModuleName,
            'route' => 'test-submodule-route/index',
            'rank' => 5,
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Sub Module Created Successfully']);

        $this->assertDatabaseHas('submodule', [
            'moduleID' => $moduleId,
            'submodulename' => $subModuleName,
            'route' => 'test-submodule-route/index',
            'sub_module_rank' => 5,
        ]);

        // Get the submodule ID
        $submodule = DB::table('submodule')->where('submodulename', $subModuleName)->first();
        $this->assertNotNull($submodule);

        // 3. Get specific submodule details
        $response = $this->getJson("/api/nextjs/submodules/{$submodule->submoduleID}", $headers);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'submoduleID' => $submodule->submoduleID,
                    'moduleID' => $moduleId,
                    'submodulename' => $subModuleName,
                    'route' => 'test-submodule-route/index',
                    'sub_module_rank' => 5,
                    'modulename' => $moduleName,
                ]
            ]);

        // 4. Update the submodule
        $updatedSubModuleName = 'Updated Submodule ' . rand(1000, 9999);
        $response = $this->postJson("/api/nextjs/submodules/update/{$submodule->submoduleID}", [
            'moduleID' => $moduleId,
            'subModuleName' => $updatedSubModuleName,
            'route' => '/updated-route/sub/',
            'rank' => 10,
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'SubModule Successfully Updated']);

        $this->assertDatabaseHas('submodule', [
            'submoduleID' => $submodule->submoduleID,
            'moduleID' => $moduleId,
            'submodulename' => $updatedSubModuleName,
            'route' => 'updated-route/sub', // trimmed of slashes
            'sub_module_rank' => 10,
        ]);

        // Create assignment record to test delete cascade
        DB::table('assign_module_role')->insert([
            'roleID' => 1,
            'submoduleID' => $submodule->submoduleID,
            'moduleID' => $moduleId,
            'created_at' => now(),
        ]);

        // 5. Delete the submodule
        $response = $this->postJson("/api/nextjs/submodules/delete/{$submodule->submoduleID}", [], $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Sub-module Successfully Deleted']);

        $this->assertDatabaseMissing('submodule', [
            'submoduleID' => $submodule->submoduleID,
        ]);

        $this->assertDatabaseMissing('assign_module_role', [
            'submoduleID' => $submodule->submoduleID,
        ]);

        // 6. Test validation failure (invalid chars)
        $response = $this->postJson('/api/nextjs/submodules', [
            'moduleID' => $moduleId,
            'subModuleName' => 'Invalid Submodule Name #@$',
            'route' => 'valid-route',
        ], $headers);

        $response->assertStatus(422)
            ->assertJson(['status' => 'error', 'message' => 'Validation error']);
    }
}
