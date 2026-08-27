<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leave_record', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_record', 'is_recalled')) {
                $table->tinyInteger('is_recalled')->default(0)->after('status');
            }
            if (!Schema::hasColumn('leave_record', 'original_end_date')) {
                $table->date('original_end_date')->nullable()->after('is_recalled');
            }
            if (!Schema::hasColumn('leave_record', 'recall_date')) {
                $table->date('recall_date')->nullable()->after('original_end_date');
            }
            if (!Schema::hasColumn('leave_record', 'days_used')) {
                $table->integer('days_used')->nullable()->after('recall_date');
            }
            if (!Schema::hasColumn('leave_record', 'unused_days_returned')) {
                $table->integer('unused_days_returned')->nullable()->after('days_used');
            }
            if (!Schema::hasColumn('leave_record', 'recall_reason')) {
                $table->text('recall_reason')->nullable()->after('unused_days_returned');
            }
            if (!Schema::hasColumn('leave_record', 'recalled_by')) {
                $table->unsignedBigInteger('recalled_by')->nullable()->after('recall_reason');
            }
            if (!Schema::hasColumn('leave_record', 'recalled_at')) {
                $table->timestamp('recalled_at')->nullable()->after('recalled_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_record', function (Blueprint $table) {
            $columns = [
                'is_recalled',
                'original_end_date',
                'recall_date',
                'days_used',
                'unused_days_returned',
                'recall_reason',
                'recalled_by',
                'recalled_at',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('leave_record', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
