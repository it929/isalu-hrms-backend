<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PensionActivationApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_pension_activation_endpoints_and_bulk_toggle()
    {
        $user = DB::table('tblper')->first();
        if (!$user) {
            $this->markTestSkipped('No user found in tblper to run this test');
            return;
        }

        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $user->UserID ?? 1,
            'roleID' => 1, // Super Admin
        ]);

        $headers = ['X-User-Id' => $user->UserID ?? 1];

        // 1. Fetch index list
        $response = $this->getJson('/api/nextjs/payroll/pension-activation', $headers);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // 2. Single toggle
        $toggleRes = $this->postJson('/api/nextjs/payroll/pension-activation/toggle', [
            'staff_id' => $user->ID,
            'pen_act' => 1,
        ], $headers);
        $toggleRes->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertEquals(1, DB::table('salary_structures')->where('staffId', $user->ID)->value('pen_act'));

        // 3. Bulk toggle
        $staffIds = DB::table('tblper')->where('rank', '!=', 2)->limit(3)->pluck('ID')->toArray();
        if (!empty($staffIds)) {
            $bulkRes = $this->postJson('/api/nextjs/payroll/pension-activation/bulk-toggle', [
                'staff_ids' => $staffIds,
                'pen_act' => 1,
            ], $headers);

            $bulkRes->assertStatus(200)
                ->assertJson(['status' => 'success']);

            foreach ($staffIds as $sid) {
                $this->assertEquals(1, DB::table('salary_structures')->where('staffId', $sid)->value('pen_act'));
            }

            // Bulk deactivate
            $bulkDeactRes = $this->postJson('/api/nextjs/payroll/pension-activation/bulk-toggle', [
                'staff_ids' => $staffIds,
                'pen_act' => 0,
            ], $headers);

            $bulkDeactRes->assertStatus(200)
                ->assertJson(['status' => 'success']);

            foreach ($staffIds as $sid) {
                $this->assertEquals(0, DB::table('salary_structures')->where('staffId', $sid)->value('pen_act'));
            }
        }
    }
}
