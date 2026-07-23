<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHodDelegationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hod_delegations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('hod_staff_id');
            $table->unsignedInteger('delegate_staff_id');
            $table->unsignedInteger('department_id');
            $table->text('permissions')->nullable(); // JSON array
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hod_delegations');
    }
}
