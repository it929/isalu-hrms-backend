<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameIndicesToTblperTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tblper', function (Blueprint $table) {
            // Check if the index doesn't exist yet before adding it (we manually added it during diagnosis, but this prevents errors)
            $conn = Schema::getConnection();
            $dbSchemaManager = $conn->getDoctrineSchemaManager();
            $doctrineTable = $dbSchemaManager->listTableDetails('tblper');
            
            if (!$doctrineTable->hasIndex('idx_tblper_names_cov')) {
                $table->index(['surname', 'first_name', 'othernames', 'fileNo'], 'idx_tblper_names_cov');
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
        Schema::table('tblper', function (Blueprint $table) {
            $table->dropIndex('idx_tblper_names_cov');
        });
    }
}
