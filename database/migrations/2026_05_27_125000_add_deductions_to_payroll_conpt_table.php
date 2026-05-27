<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeductionsToPayrollConptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_conpt', function (Blueprint $table) {
            $table->decimal('retention', 15, 2)->default(0.00)->after('absence_penalty');
            $table->decimal('surcharges', 15, 2)->default(0.00)->after('retention');
            $table->decimal('medical_loan', 15, 2)->default(0.00)->after('surcharges');
            $table->decimal('coop_loan_rpyt', 15, 2)->default(0.00)->after('medical_loan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_conpt', function (Blueprint $table) {
            $table->dropColumn(['retention', 'surcharges', 'medical_loan', 'coop_loan_rpyt']);
        });
    }
}
