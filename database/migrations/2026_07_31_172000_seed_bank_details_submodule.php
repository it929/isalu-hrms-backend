<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SeedBankDetailsSubmodule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $exists = DB::table('submodule')
            ->where('submodulename', 'Staff Account Details')
            ->exists();

        if (!$exists) {
            $subId = DB::table('submodule')->insertGetId([
                'moduleID' => 5,
                'submodulename' => 'Staff Account Details',
                'route' => 'dashboard/payroll/bank-details',
                'sub_module_rank' => 6,
                'status' => 1,
                'created_at' => now(),
            ]);

            // Assign to roles that have "Payer ID Setup" or "Declare Salary Setup"
            $payerIdSub = DB::table('submodule')->where('submodulename', 'Payer ID Setup')->first();
            $targetSubId = $payerIdSub ? $payerIdSub->submoduleID : 265;

            $roles = DB::table('assign_module_role')
                ->where('submoduleID', $targetSubId)
                ->pluck('roleID');

            foreach ($roles as $roleId) {
                DB::table('assign_module_role')->updateOrInsert(
                    ['roleID' => $roleId, 'moduleID' => 5, 'submoduleID' => $subId],
                    ['created_at' => now()]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sub = DB::table('submodule')
            ->where('submodulename', 'Staff Account Details')
            ->first();

        if ($sub) {
            DB::table('assign_module_role')->where('submoduleID', $sub->submoduleID)->delete();
            DB::table('submodule')->where('submoduleID', $sub->submoduleID)->delete();
        }
    }
}
