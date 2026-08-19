<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMonthlyDeductionToMedicalLoanEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('medical_loan_entries') && !Schema::hasColumn('medical_loan_entries', 'monthly_deduction')) {
            Schema::table('medical_loan_entries', function (Blueprint $table) {
                $table->decimal('monthly_deduction', 15, 2)->default(0.00)->after('balance_after');
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
        if (Schema::hasTable('medical_loan_entries') && Schema::hasColumn('medical_loan_entries', 'monthly_deduction')) {
            Schema::table('medical_loan_entries', function (Blueprint $table) {
                $table->dropColumn('monthly_deduction');
            });
        }
    }
}
