<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrintActiveToTblactivemonthTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tblactivemonth') && !Schema::hasColumn('tblactivemonth', 'print_active')) {
            Schema::table('tblactivemonth', function (Blueprint $table) {
                $table->boolean('print_active')->default(0);
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
        if (Schema::hasTable('tblactivemonth') && Schema::hasColumn('tblactivemonth', 'print_active')) {
            Schema::table('tblactivemonth', function (Blueprint $table) {
                $table->dropColumn('print_active');
            });
        }
    }
}
