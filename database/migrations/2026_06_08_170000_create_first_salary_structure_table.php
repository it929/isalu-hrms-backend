<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFirstSalaryStructureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('first_salary_structure', function (Blueprint $table) {
            $table->id();
            $table->integer('staffId')->index();
            $table->decimal('basic_salary', 15, 2)->default(0.00);
            $table->decimal('declare_salary', 15, 2)->default(0.00);
            $table->decimal('housing_allowance', 15, 2)->default(0.00);
            $table->decimal('transport_allowance', 15, 2)->default(0.00);
            $table->decimal('medical_allowance', 15, 2)->default(0.00);
            $table->decimal('utility_allowance', 15, 2)->default(0.00);
            $table->decimal('meal_allowance', 15, 2)->default(0.00);
            $table->tinyInteger('reten_act')->default(0);
            $table->date('reten_start_date')->nullable();
            $table->date('reten_end_date')->nullable();
            $table->integer('num_rente_months')->default(0);
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
        Schema::dropIfExists('first_salary_structure');
    }
}
