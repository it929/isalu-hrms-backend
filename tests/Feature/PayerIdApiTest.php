<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PayerIdApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_payer_id_endpoints()
    {
        $staffId = DB::table('tblper')->insertGetId([
            'fileNo' => 'T-PAYER-01',
            'surname' => 'Adebayo',
            'first_name' => 'Kola',
            'othernames' => 'John',
            'rank' => 1,
            'staff_status' => 1,
            'payer_id' => '0',
            'created_at' => now(),
        ]);

        $adminUserId = 99999;
        DB::table('assign_user_role')->insertOrIgnore([
            'userID' => $adminUserId,
            'roleID' => 1,
        ]);

        $headers = ['X-User-Id' => $adminUserId];

        // 1. Fetch index
        $res = $this->getJson('/api/nextjs/payroll/payer-id', $headers);
        $res->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        // Verify test staff is in list with payer_id '0'
        $staffData = collect($res->json('data'))->firstWhere('staffId', $staffId);
        $this->assertNotNull($staffData);
        $this->assertEquals('0', $staffData['payer_id']);

        // 2. Update to a valid Payer ID
        $updateRes = $this->postJson('/api/nextjs/payroll/payer-id', [
            'staffId' => $staffId,
            'payer_id' => 'N-12345678',
        ], $headers);

        $updateRes->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Payer ID updated successfully.',
            ]);

        $this->assertDatabaseHas('tblper', [
            'ID' => $staffId,
            'payer_id' => 'N-12345678',
        ]);

        // 3. Verify audit log captured the staff name and action
        $log = DB::table('user_activity_logs')
            ->where('action', 'like', '%Updated Staff Payer ID%')
            ->orderBy('id', 'desc')
            ->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString('Adebayo Kola', $log->action);
        $this->assertStringContainsString('N-12345678', $log->action);
    }
}
