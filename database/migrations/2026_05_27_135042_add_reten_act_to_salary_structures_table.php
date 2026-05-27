<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRetenActToSalaryStructuresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salary_structures', function (Blueprint $table) {
            $table->tinyInteger('reten_act')->default(0)->after('pen_act');
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
            $table->dropColumn('reten_act');
        });
    }
}
