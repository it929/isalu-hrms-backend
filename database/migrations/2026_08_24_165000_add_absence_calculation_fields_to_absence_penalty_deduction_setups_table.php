<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAbsenceCalculationFieldsToAbsencePenaltyDeductionSetupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('absence_penalty_deduction_setups')) {
            Schema::table('absence_penalty_deduction_setups', function (Blueprint $table) {
                if (!Schema::hasColumn('absence_penalty_deduction_setups', 'absent_days')) {
                    $table->integer('absent_days')->nullable()->after('deduction_type');
                }
                if (!Schema::hasColumn('absence_penalty_deduction_setups', 'penalty_multiplier')) {
                    $table->integer('penalty_multiplier')->default(3)->after('absent_days');
                }
                if (!Schema::hasColumn('absence_penalty_deduction_setups', 'penalty_days')) {
                    $table->integer('penalty_days')->nullable()->after('penalty_multiplier');
                }
                if (!Schema::hasColumn('absence_penalty_deduction_setups', 'daily_salary')) {
                    $table->decimal('daily_salary', 15, 2)->nullable()->after('penalty_days');
                }
                if (!Schema::hasColumn('absence_penalty_deduction_setups', 'monthly_salary')) {
                    $table->decimal('monthly_salary', 15, 2)->nullable()->after('daily_salary');
                }
                if (!Schema::hasColumn('absence_penalty_deduction_setups', 'remarks')) {
                    $table->text('remarks')->nullable()->after('end_month');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('absence_penalty_deduction_setups')) {
            Schema::table('absence_penalty_deduction_setups', function (Blueprint $table) {
                $columns = ['absent_days', 'penalty_multiplier', 'penalty_days', 'daily_salary', 'monthly_salary', 'remarks'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('absence_penalty_deduction_setups', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
}
