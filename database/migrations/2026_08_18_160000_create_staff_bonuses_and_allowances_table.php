<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateStaffBonusesAndAllowancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('staff_bonuses_and_allowances')) {
            Schema::create('staff_bonuses_and_allowances', function (Blueprint $table) {
                $table->id();
                $table->integer('staffId')->index();
                $table->string('type', 20)->default('allowance')->index(); // bonus, allowance
                $table->string('category', 100)->default('custom'); // performance_bonus, leave_bonus, 13th_month, hazard_allowance, etc.
                $table->string('title', 255);
                $table->decimal('amount', 15, 2)->default(0.00);
                $table->string('frequency', 20)->default('one_time'); // one_time, recurring
                $table->string('start_month', 7); // YYYY-MM
                $table->string('end_month', 7)->nullable(); // YYYY-MM
                $table->text('notes')->nullable();
                $table->tinyInteger('is_active')->default(1)->index();
                $table->integer('created_by')->nullable();
                $table->timestamps();
            });
        }

        // Seed Submodule into Payroll module
        $exists = DB::table('submodule')
            ->where('submodulename', 'Bonus & Allowance Setup')
            ->orWhere('route', 'dashboard/payroll/bonus-allowance-setup')
            ->exists();

        if (!$exists) {
            $subId = DB::table('submodule')->insertGetId([
                'moduleID' => 5,
                'submodulename' => 'Bonus & Allowance Setup',
                'route' => 'dashboard/payroll/bonus-allowance-setup',
                'sub_module_rank' => 6,
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
            ->where('submodulename', 'Bonus & Allowance Setup')
            ->orWhere('route', 'dashboard/payroll/bonus-allowance-setup')
            ->first();

        if ($sub) {
            DB::table('assign_module_role')->where('submoduleID', $sub->submoduleID)->delete();
            DB::table('submodule')->where('submoduleID', $sub->submoduleID)->delete();
        }

        Schema::dropIfExists('staff_bonuses_and_allowances');
    }
}
