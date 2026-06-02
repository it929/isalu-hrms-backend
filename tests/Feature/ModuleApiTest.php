<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ModuleApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_module_api_endpoints()
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

        // 1. Fetch modules list
        $response = $this->getJson('/api/nextjs/modules', $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // 2. Create a new module
        $moduleName = 'Test Module ' . rand(1000, 9999);
        $response = $this->postJson('/api/nextjs/modules', [
            'moduleName' => $moduleName,
            'rank' => 12,
            'link_type' => 'HR',
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Module Created Successfully']);

        $this->assertDatabaseHas('module', [
            'modulename' => $moduleName,
            'module_rank' => 12,
            'link_type' => 'HR',
        ]);

        // Get the module ID
        $module = DB::table('module')->where('modulename', $moduleName)->first();
        $this->assertNotNull($module);

        // 3. Get specific module details
        $response = $this->getJson("/api/nextjs/modules/{$module->moduleID}", $headers);
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'moduleID' => $module->moduleID,
                    'modulename' => $moduleName,
                    'module_rank' => 12,
                    'link_type' => 'HR',
                ]
            ]);

        // 4. Update the module
        $updatedModuleName = 'Updated Module ' . rand(1000, 9999);
        $response = $this->postJson("/api/nextjs/modules/update/{$module->moduleID}", [
            'name' => $updatedModuleName,
            'rank' => 15,
            'link_type' => 'PAYROLL',
        ], $headers);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Module Successfully Updated']);

        $this->assertDatabaseHas('module', [
            'moduleID' => $module->moduleID,
            'modulename' => $updatedModuleName,
            'module_rank' => 15,
            'link_type' => 'PAYROLL',
        ]);

        // 5. Test validation regex rule (invalid link type)
        $response = $this->postJson('/api/nextjs/modules', [
            'moduleName' => 'Valid Module Name',
            'rank' => 5,
            'link_type' => 'INVALID_TYPE',
        ], $headers);

        $response->assertStatus(422)
            ->assertJson(['status' => 'error', 'message' => 'Validation error']);
    }
}
