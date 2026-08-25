<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemarksToOtherDeductionSetupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('other_deduction_setups') && !Schema::hasColumn('other_deduction_setups', 'remarks')) {
            Schema::table('other_deduction_setups', function (Blueprint $table) {
                $table->text('remarks')->nullable()->after('end_month');
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
        if (Schema::hasTable('other_deduction_setups') && Schema::hasColumn('other_deduction_setups', 'remarks')) {
            Schema::table('other_deduction_setups', function (Blueprint $table) {
                $table->dropColumn('remarks');
            });
        }
    }
}
