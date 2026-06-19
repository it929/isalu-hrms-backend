<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResignationRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resignation_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('staff_id');
            $table->text('reason');
            $table->date('resignation_date');
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('hod_status')->default(0);
            $table->integer('hod_id')->nullable();
            $table->dateTime('hod_date')->nullable();
            $table->tinyInteger('admin_status')->default(0);
            $table->integer('admin_id')->nullable();
            $table->dateTime('admin_date')->nullable();
            $table->tinyInteger('finance_status')->default(0);
            $table->integer('finance_id')->nullable();
            $table->dateTime('finance_date')->nullable();
            $table->text('remarks')->nullable();
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
        Schema::dropIfExists('resignation_requests');
    }
}
