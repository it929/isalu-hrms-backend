<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeaveOfAbsenceDeductionToPayrollConptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_conpt', function (Blueprint $table) {
            $table->decimal('leave_of_absence_deduction', 15, 2)->default(0.00)->after('coop_loan_rpyt');
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
            $table->dropColumn('leave_of_absence_deduction');
        });
    }
}
