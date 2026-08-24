<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SeedMedicalLoanRecordsSubmodule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Seed Submodule into Payroll module (moduleID = 5)
        $exists = DB::table('submodule')
            ->where('submodulename', 'Medical Loan Records')
            ->orWhere('route', 'dashboard/payroll/medical-loan-records')
            ->exists();

        if (!$exists) {
            $subId = DB::table('submodule')->insertGetId([
                'moduleID' => 5,
                'submodulename' => 'Medical Loan Records',
                'route' => 'dashboard/payroll/medical-loan-records',
                'sub_module_rank' => 13,
                'status' => 1,
                'created_at' => now(),
            ]);

            // Assign submodule permissions across all roles with access to module 5
            $roles = DB::table('assign_module_role')
                ->where('moduleID', 5)
                ->pluck('roleID')
                ->unique();

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
            ->where('submodulename', 'Medical Loan Records')
            ->orWhere('route', 'dashboard/payroll/medical-loan-records')
            ->first();

        if ($sub) {
            DB::table('assign_module_role')->where('submoduleID', $sub->submoduleID)->delete();
            DB::table('submodule')->where('submoduleID', $sub->submoduleID)->delete();
        }
    }
}
