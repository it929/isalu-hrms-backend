<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoopLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coop_loans', function (Blueprint $table) {
            $table->id();
            $table->integer('staffId');
            $table->string('loan_type');
            $table->decimal('loan_amount', 15, 2)->default(0.00);
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->decimal('monthly_deduction', 15, 2)->default(0.00);
            $table->string('status')->default('pending');
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
        Schema::dropIfExists('coop_loans');
    }
}
