<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddPayerIdToPayrollTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tblper', function (Blueprint $table) {
            $table->string('payer_id')->nullable()->after('nhfNo');
        });

        Schema::table('payroll_conpt', function (Blueprint $table) {
            $table->string('payer_id')->nullable()->after('is_paid');
        });

        $exists = DB::table('submodule')
            ->where('submodulename', 'Payer ID Setup')
            ->exists();

        if (!$exists) {
            $subId = DB::table('submodule')->insertGetId([
                'moduleID' => 5,
                'submodulename' => 'Payer ID Setup',
                'route' => 'dashboard/payroll/payer-id',
                'sub_module_rank' => 5,
                'status' => 1,
                'created_at' => now(),
            ]);

            $roles = DB::table('assign_module_role')
                ->where('submoduleID', 265) // matches Declare Salary Setup permissions
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
        Schema::table('tblper', function (Blueprint $table) {
            $table->dropColumn('payer_id');
        });

        Schema::table('payroll_conpt', function (Blueprint $table) {
            $table->dropColumn('payer_id');
        });

        $sub = DB::table('submodule')
            ->where('submodulename', 'Payer ID Setup')
            ->first();

        if ($sub) {
            DB::table('assign_module_role')->where('submoduleID', $sub->submoduleID)->delete();
            DB::table('submodule')->where('submoduleID', $sub->submoduleID)->delete();
        }
    }
}
