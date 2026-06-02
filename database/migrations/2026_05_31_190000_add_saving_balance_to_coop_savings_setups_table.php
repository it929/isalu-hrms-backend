<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSavingBalanceToCoopSavingsSetupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coop_savings_setups', function (Blueprint $table) {
            $table->decimal('saving_balance', 15, 2)->default(0.00)->after('monthly_saving');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coop_savings_setups', function (Blueprint $table) {
            $table->dropColumn('saving_balance');
        });
    }
}
