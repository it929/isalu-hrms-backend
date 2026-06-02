<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToStaffEarningAndDeductionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('staffEarningAndDeduction', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1)->after('one_time'); // 1 = Active, 0 = Inactive
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
            $table->dropColumn('status');
        });
    }
}
