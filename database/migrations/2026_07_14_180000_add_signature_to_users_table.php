<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSignatureToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('users', 'signature')) {
            Schema::table('users', function (Blueprint $table) {
                $table->longText('signature')->nullable()->after('password');
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
        if (Schema::hasColumn('users', 'signature')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('signature');
            });
        }
    }
}
