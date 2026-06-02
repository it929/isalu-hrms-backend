<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSystemCodeToTblcvSetupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tblcvSetup', function (Blueprint $table) {
            $table->string('system_code')->nullable()->after('particularID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tblcvSetup', function (Blueprint $table) {
            $table->dropColumn('system_code');
        });
    }
}
