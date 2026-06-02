<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalaryLockAndVstageToPayrollConptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_conpt', function (Blueprint $table) {
            $table->tinyInteger('salary_lock')->default(0)->after('net_pay');
            $table->integer('vstage')->default(0)->after('salary_lock');
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
            $table->dropColumn(['salary_lock', 'vstage']);
        });
    }
}
