<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuditAndPaidToPayrollConptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_conpt', function (Blueprint $table) {
            $table->tinyInteger('audit_checked')->default(0)->after('vstage');
            $table->tinyInteger('is_paid')->default(0)->after('audit_checked');
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
            $table->dropColumn(['audit_checked', 'is_paid']);
        });
    }
}
