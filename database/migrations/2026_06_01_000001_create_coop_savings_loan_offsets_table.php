<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coop_savings_loan_offsets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staffId');
            $table->unsignedBigInteger('savings_setup_id');
            $table->unsignedBigInteger('loan_setup_id');
            $table->decimal('offset_amount', 15, 2);
            $table->decimal('savings_balance_before', 15, 2);
            $table->decimal('savings_balance_after', 15, 2);
            $table->decimal('loan_balance_before', 15, 2);
            $table->decimal('loan_balance_after', 15, 2);
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('staffId');
            $table->index('savings_setup_id');
            $table->index('loan_setup_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coop_savings_loan_offsets');
    }
};
