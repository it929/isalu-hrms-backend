<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPenActToSalaryStructuresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salary_structures', function (Blueprint $table) {
            $table->tinyInteger('pen_act')->default(0)->after('pension_rate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('salary_structures', function (Blueprint $table) {
            $table->dropColumn('pen_act');
        });
    }
}
