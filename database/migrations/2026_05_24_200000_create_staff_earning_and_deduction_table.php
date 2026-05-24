<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffEarningAndDeductionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staffEarningAndDeduction', function (Blueprint $table) {
            $table->id();
            $table->integer('staffId');
            $table->string('variable_type');
            $table->integer('cv_setup_id');
            $table->string('description');
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->decimal('target_amount', 15, 2)->nullable();
            $table->tinyInteger('no_limit')->default(0);
            $table->tinyInteger('one_time')->default(0);
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
        Schema::dropIfExists('staffEarningAndDeduction');
    }
}
