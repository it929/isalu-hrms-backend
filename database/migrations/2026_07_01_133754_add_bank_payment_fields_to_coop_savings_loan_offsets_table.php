<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coop_savings_loan_offsets', function (Blueprint $table) {
            $table->string('offset_type', 50)->default('savings')->after('loan_setup_id');
            $table->string('proof_of_payment', 255)->nullable()->after('offset_type');
        });

        // Make savings fields nullable to support direct bank payments (no savings account required)
        DB::statement('ALTER TABLE coop_savings_loan_offsets MODIFY savings_setup_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE coop_savings_loan_offsets MODIFY savings_balance_before DECIMAL(15,2) NULL');
        DB::statement('ALTER TABLE coop_savings_loan_offsets MODIFY savings_balance_after DECIMAL(15,2) NULL');
    }

    public function down(): void
    {
        // Revert columns to not nullable (requires default or clean DB state)
        DB::statement('ALTER TABLE coop_savings_loan_offsets MODIFY savings_setup_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE coop_savings_loan_offsets MODIFY savings_balance_before DECIMAL(15,2) NOT NULL');
        DB::statement('ALTER TABLE coop_savings_loan_offsets MODIFY savings_balance_after DECIMAL(15,2) NOT NULL');

        Schema::table('coop_savings_loan_offsets', function (Blueprint $table) {
            $table->dropColumn(['offset_type', 'proof_of_payment']);
        });
    }
};
