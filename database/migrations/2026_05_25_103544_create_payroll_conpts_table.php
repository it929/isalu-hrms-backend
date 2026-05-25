<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollConptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_conpt', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_run_id')->index();
            $table->integer('staffID')->index();
            $table->decimal('basic', 15, 2)->default(0.00);
            $table->decimal('housing', 15, 2)->default(0.00);
            $table->decimal('transport', 15, 2)->default(0.00);
            $table->decimal('medical', 15, 2)->default(0.00);
            $table->decimal('utility', 15, 2)->default(0.00);
            $table->decimal('meal', 15, 2)->default(0.00);
            $table->integer('paid_days')->default(0);
            $table->decimal('gross_pay', 15, 2)->default(0.00);
            $table->decimal('paye_tax', 15, 2)->default(0.00);
            $table->decimal('loan_deduction', 15, 2)->default(0.00);
            $table->decimal('pension', 15, 2)->default(0.00);
            $table->decimal('coop_savings', 15, 2)->default(0.00);
            $table->decimal('other_deductions', 15, 2)->default(0.00);
            $table->decimal('total_deductions', 15, 2)->default(0.00);
            $table->decimal('net_pay', 15, 2)->default(0.00);
            $table->decimal('total_income', 15, 2)->default(0.00);
            $table->decimal('declare_income', 15, 2)->default(0.00);
            $table->decimal('iou', 15, 2)->default(0.00);
            $table->decimal('absence_penalty', 15, 2)->default(0.00);
            $table->text('applied_amounts')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_conpt');
    }
}
