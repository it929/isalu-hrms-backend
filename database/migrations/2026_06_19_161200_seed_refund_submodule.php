<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SeedRefundSubmodule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $exists = DB::table('submodule')
            ->where('submodulename', 'Apply for Refund')
            ->exists();

        if (!$exists) {
            $subId = DB::table('submodule')->insertGetId([
                'moduleID' => 8,
                'submodulename' => 'Apply for Refund',
                'route' => 'dashboard/payroll/apply-refund',
                'sub_module_rank' => 17,
                'status' => 1,
                'created_at' => now(),
            ]);

            $roles = DB::table('assign_module_role')
                ->where('submoduleID', 242)
                ->pluck('roleID');

            foreach ($roles as $roleId) {
                DB::table('assign_module_role')->updateOrInsert(
                    ['roleID' => $roleId, 'moduleID' => 8, 'submoduleID' => $subId],
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
            ->where('submodulename', 'Apply for Refund')
            ->first();

        if ($sub) {
            DB::table('assign_module_role')->where('submoduleID', $sub->submoduleID)->delete();
            DB::table('submodule')->where('submoduleID', $sub->submoduleID)->delete();
        }
    }
}
