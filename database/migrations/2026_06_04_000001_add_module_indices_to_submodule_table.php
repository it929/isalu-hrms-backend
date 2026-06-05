<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModuleIndicesToSubmoduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('submodule', function (Blueprint $table) {
            $conn = Schema::getConnection();
            $dbSchemaManager = $conn->getDoctrineSchemaManager();
            $doctrineTable = $dbSchemaManager->listTableDetails('submodule');
            
            if (!$doctrineTable->hasIndex('idx_submodule_module')) {
                $table->index(['moduleID', 'status', 'sub_module_rank'], 'idx_submodule_module');
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
        Schema::table('submodule', function (Blueprint $table) {
            $table->dropIndex('idx_submodule_module');
        });
    }
}
