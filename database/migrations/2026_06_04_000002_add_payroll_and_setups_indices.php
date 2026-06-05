<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPayrollAndSetupsIndices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tblotherEarningDeduction', function (Blueprint $table) {
            $table->index(['year', 'month', 'staffid'], 'idx_oed_yr_mo_staff');
        });

        Schema::table('tblpayment_consolidated', function (Blueprint $table) {
            $table->index(['year', 'month', 'staffid'], 'idx_pc_yr_mo_staff');
        });

        Schema::table('staffearninganddeduction', function (Blueprint $table) {
            $table->index(['staffId', 'cv_setup_id'], 'idx_sead_staff_cv');
        });

        Schema::table('employee_loans', function (Blueprint $table) {
            $table->index('staffId', 'idx_eloans_staff');
        });

        $setupTables = [
            'loan_deduction_setups',
            'coop_loan_deduction_setups',
            'coop_savings_setups',
            'medical_loan_deduction_setups',
            'surcharge_deduction_setups',
            'absence_penalty_deduction_setups',
            'other_deduction_setups',
            'coop_asset_finance_deduction_setups',
        ];

        foreach ($setupTables as $t) {
            Schema::table($t, function (Blueprint $table) use ($t) {
                $table->index(['staffId', 'is_active'], "idx_{$t}_staff_active");
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
        Schema::table('tblotherEarningDeduction', function (Blueprint $table) {
            $table->dropIndex('idx_oed_yr_mo_staff');
        });

        Schema::table('tblpayment_consolidated', function (Blueprint $table) {
            $table->dropIndex('idx_pc_yr_mo_staff');
        });

        Schema::table('staffearninganddeduction', function (Blueprint $table) {
            $table->dropIndex('idx_sead_staff_cv');
        });

        Schema::table('employee_loans', function (Blueprint $table) {
            $table->dropIndex('idx_eloans_staff');
        });

        $setupTables = [
            'loan_deduction_setups',
            'coop_loan_deduction_setups',
            'coop_savings_setups',
            'medical_loan_deduction_setups',
            'surcharge_deduction_setups',
            'absence_penalty_deduction_setups',
            'other_deduction_setups',
            'coop_asset_finance_deduction_setups',
        ];

        foreach ($setupTables as $t) {
            Schema::table($t, function (Blueprint $table) use ($t) {
                $table->dropIndex("idx_{$t}_staff_active");
            });
        }
    }
}
