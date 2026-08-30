<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SeedResignationSettlementSubmodule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $exists = DB::table('submodule')
            ->where('submodulename', 'Resignation Settlement')
            ->orWhere('route', 'dashboard/payroll/resignation-settlement')
            ->exists();

        if (!$exists) {
            $subId = DB::table('submodule')->insertGetId([
                'moduleID' => 8,
                'submodulename' => 'Resignation Settlement',
                'route' => 'dashboard/payroll/resignation-settlement',
                'sub_module_rank' => 19,
                'status' => 1,
                'created_at' => now(),
            ]);

            // Assign submodule permissions across all roles with access to module 8
            $roles = DB::table('assign_module_role')
                ->where('moduleID', 8)
                ->pluck('roleID')
                ->unique();

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
            ->where('submodulename', 'Resignation Settlement')
            ->orWhere('route', 'dashboard/payroll/resignation-settlement')
            ->first();

        if ($sub) {
            DB::table('assign_module_role')->where('submoduleID', $sub->submoduleID)->delete();
            DB::table('submodule')->where('submoduleID', $sub->submoduleID)->delete();
        }
    }
}
