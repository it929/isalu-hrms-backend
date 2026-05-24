<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryStructuresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->integer('staffId');
            $table->decimal('basic_salary', 15, 2)->default(0.00);
            $table->decimal('declare_salary', 15, 2)->default(0.00);
            $table->decimal('housing_allowance', 15, 2)->default(0.00);
            $table->decimal('transport_allowance', 15, 2)->default(0.00);
            $table->decimal('medical_allowance', 15, 2)->default(0.00);
            $table->decimal('utility_allowance', 15, 2)->default(0.00);
            $table->decimal('meal_allowance', 15, 2)->default(0.00);
            $table->decimal('pension_rate', 5, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salary_structures');
    }
}
