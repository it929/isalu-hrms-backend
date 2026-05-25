<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalDeductedToStaffEarningAndDeductionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('staffEarningAndDeduction', function (Blueprint $table) {
            $table->decimal('total_deducted', 15, 2)->default(0.00)->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('staffEarningAndDeduction', function (Blueprint $table) {
            $table->dropColumn('total_deducted');
        });
    }
}
