<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDayDeductionFieldsToOtherDeductionSetupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('other_deduction_setups')) {
            Schema::table('other_deduction_setups', function (Blueprint $table) {
                if (!Schema::hasColumn('other_deduction_setups', 'calculation_mode')) {
                    $table->string('calculation_mode', 20)->default('amount')->after('deduction_type');
                }
                if (!Schema::hasColumn('other_deduction_setups', 'deduction_days')) {
                    $table->decimal('deduction_days', 8, 2)->nullable()->after('calculation_mode');
                }
                if (!Schema::hasColumn('other_deduction_setups', 'daily_rate')) {
                    $table->decimal('daily_rate', 15, 2)->nullable()->after('deduction_days');
                }
                if (!Schema::hasColumn('other_deduction_setups', 'monthly_salary')) {
                    $table->decimal('monthly_salary', 15, 2)->nullable()->after('daily_rate');
                }
                if (!Schema::hasColumn('other_deduction_setups', 'days_in_month')) {
                    $table->integer('days_in_month')->nullable()->after('monthly_salary');
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
        if (Schema::hasTable('other_deduction_setups')) {
            Schema::table('other_deduction_setups', function (Blueprint $table) {
                $columns = ['calculation_mode', 'deduction_days', 'daily_rate', 'monthly_salary', 'days_in_month'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('other_deduction_setups', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
}
