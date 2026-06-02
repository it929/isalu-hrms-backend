<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanDeductionSetupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_deduction_setups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staffId');
            $table->decimal('loan_amount', 15, 2);
            $table->decimal('interest_rate', 5, 2)->default(0.00);
            $table->integer('duration_months');
            $table->decimal('monthly_deduction', 15, 2);
            $table->decimal('balance_remaining', 15, 2);
            $table->string('start_month', 7); // Format: YYYY-MM
            $table->string('end_month', 7);   // Format: YYYY-MM
            $table->tinyInteger('is_active')->default(1);
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
        Schema::dropIfExists('loan_deduction_setups');
    }
}
