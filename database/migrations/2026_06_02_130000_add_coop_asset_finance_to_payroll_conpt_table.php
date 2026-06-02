<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoopAssetFinanceToPayrollConptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payroll_conpt', function (Blueprint $table) {
            $table->decimal('coop_asset_finance', 15, 2)->default(0.00)->after('coop_loan_rpyt');
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
            $table->dropColumn('coop_asset_finance');
        });
    }
}
