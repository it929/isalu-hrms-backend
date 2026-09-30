<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create cooperative savings top-ups table
        if (!Schema::hasTable('coop_savings_top_ups')) {
            Schema::create('coop_savings_top_ups', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('staffId');
                $table->unsignedBigInteger('savings_setup_id')->nullable();
                $table->string('top_up_reference', 50)->unique();
                $table->decimal('amount', 15, 2);
                $table->decimal('balance_before', 15, 2)->default(0.00);
                $table->decimal('balance_after', 15, 2)->default(0.00);
                $table->string('payment_method', 50)->default('bank_transfer');
                $table->string('payment_reference', 100)->nullable();
                $table->date('payment_date');
                $table->string('proof_of_payment', 255)->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('processed_by')->nullable();
                $table->timestamps();

                $table->index('staffId');
                $table->index('savings_setup_id');
                $table->index('payment_date');
                $table->index('processed_by');
            });
        }

        // 2. Seed submodule in module 5 (Payroll Setup)
        $existsPayroll = DB::table('submodule')
            ->where('moduleID', 5)
            ->where(function ($q) {
                $q->where('submodulename', 'Coop Savings Top-Up')
                  ->orWhere('route', 'dashboard/payroll/coop-savings-top-up');
            })
            ->first();

        if (!$existsPayroll) {
            $payrollSubId = DB::table('submodule')->insertGetId([
                'moduleID' => 5,
                'submodulename' => 'Coop Savings Top-Up',
                'route' => 'dashboard/payroll/coop-savings-top-up',
                'sub_module_rank' => 12,
                'status' => 1,
                'created_at' => now(),
            ]);

            // Assign permissions across all roles with access to module 5
            $payrollRoles = DB::table('assign_module_role')
                ->where('moduleID', 5)
                ->pluck('roleID')
                ->unique();

            foreach ($payrollRoles as $roleId) {
                DB::table('assign_module_role')->updateOrInsert(
                    ['roleID' => $roleId, 'moduleID' => 5, 'submoduleID' => $payrollSubId],
                    ['created_at' => now()]
                );
            }
        }

        // 3. Seed submodule in module 8 (Employees / Self-Service)
        $existsEmployees = DB::table('submodule')
            ->where('moduleID', 8)
            ->where(function ($q) {
                $q->where('submodulename', 'Coop Savings Top-Up')
                  ->orWhere('route', 'dashboard/payroll/coop-savings-top-up');
            })
            ->first();

        if (!$existsEmployees) {
            $empSubId = DB::table('submodule')->insertGetId([
                'moduleID' => 8,
                'submodulename' => 'Coop Savings Top-Up',
                'route' => 'dashboard/payroll/coop-savings-top-up',
                'sub_module_rank' => 20,
                'status' => 1,
                'created_at' => now(),
            ]);

            // Assign permissions across all roles with access to module 8
            $empRoles = DB::table('assign_module_role')
                ->where('moduleID', 8)
                ->pluck('roleID')
                ->unique();

            foreach ($empRoles as $roleId) {
                DB::table('assign_module_role')->updateOrInsert(
                    ['roleID' => $roleId, 'moduleID' => 8, 'submoduleID' => $empSubId],
                    ['created_at' => now()]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove submodules from assign_module_role and submodule
        $submodules = DB::table('submodule')
            ->where('route', 'dashboard/payroll/coop-savings-top-up')
            ->get();

        foreach ($submodules as $sub) {
            DB::table('assign_module_role')->where('submoduleID', $sub->submoduleID)->delete();
            DB::table('submodule')->where('submoduleID', $sub->submoduleID)->delete();
        }

        Schema::dropIfExists('coop_savings_top_ups');
    }
};
