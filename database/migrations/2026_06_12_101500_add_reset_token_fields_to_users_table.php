<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResetTokenFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'resettoken')) {
                $table->string('resettoken')->nullable();
            }
            if (!Schema::hasColumn('users', 'token_status')) {
                $table->string('token_status')->default('0');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('users', 'resettoken')) {
                $columns[] = 'resettoken';
            }
            if (Schema::hasColumn('users', 'token_status')) {
                $columns[] = 'token_status';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
}
