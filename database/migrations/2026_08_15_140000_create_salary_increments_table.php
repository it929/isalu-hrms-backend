<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('salary_increments')) {
            Schema::create('salary_increments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('staff_id');
                $table->string('increment_type', 50)->default('percentage'); // 'percentage', 'fixed_amount', 'new_gross'
                $table->decimal('percentage', 8, 2)->nullable();
                $table->decimal('amount', 15, 2)->nullable();
                $table->decimal('previous_gross_salary', 15, 2)->default(0.00);
                $table->decimal('new_gross_salary', 15, 2)->default(0.00);
                $table->decimal('increase_amount', 15, 2)->default(0.00);
                $table->decimal('previous_basic', 15, 2)->nullable();
                $table->decimal('new_basic', 15, 2)->nullable();
                $table->string('effective_date', 50)->nullable();
                $table->text('reason')->nullable();
                $table->string('batch_id', 100)->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->string('status', 50)->default('applied'); // 'applied', 'reverted'
                $table->timestamps();

                $table->index('staff_id');
                $table->index('batch_id');
                $table->index('created_at');
            });
        }

        // Seed submodule for Salary Increment under Payroll module if module exists
        try {
            $payrollModule = DB::table('module')->where('modulename', 'like', '%Payroll%')->first();
            if ($payrollModule) {
                $exists = DB::table('submodule')
                    ->where('moduleID', $payrollModule->moduleID)
                    ->where('route', 'like', '%salary-increment%')
                    ->exists();

                if (!$exists) {
                    $maxRank = DB::table('submodule')
                        ->where('moduleID', $payrollModule->moduleID)
                        ->max('sub_module_rank') ?? 0;

                    $subId = DB::table('submodule')->insertGetId([
                        'moduleID' => $payrollModule->moduleID,
                        'submodulename' => 'Salary Increment',
                        'route' => 'dashboard/payroll/salary-increment',
                        'sub_module_rank' => $maxRank + 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Assign to Super Admin and HR/Finance roles
                    $adminRoles = DB::table('user_role')->whereIn('roleID', [1, 2, 3])->pluck('roleID');
                    foreach ($adminRoles as $roleId) {
                        DB::table('assign_module_role')->insertOrIgnore([
                            'roleID' => $roleId,
                            'moduleID' => $payrollModule->moduleID,
                            'submoduleID' => $subId,
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore if seed fails during isolated unit tests
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salary_increments');
        try {
            DB::table('submodule')->where('route', 'like', '%salary-increment%')->delete();
        } catch (\Throwable $e) {}
    }
};
