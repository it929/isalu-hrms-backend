<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddHrSetupFieldsToRefundRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Modify amount column to default to 0.00
        DB::statement('ALTER TABLE refund_requests MODIFY amount DECIMAL(12,2) NOT NULL DEFAULT 0.00');

        // 2. Add setup metadata columns if they do not already exist
        Schema::table('refund_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('refund_requests', 'refund_type')) {
                $table->string('refund_type', 20)->nullable()->after('amount'); // 'amount' or 'days'
            }
            if (!Schema::hasColumn('refund_requests', 'refund_days')) {
                $table->decimal('refund_days', 5, 2)->nullable()->after('refund_type');
            }
            if (!Schema::hasColumn('refund_requests', 'refund_month')) {
                $table->string('refund_month', 10)->nullable()->after('refund_days'); // 'YYYY-MM'
            }
            if (!Schema::hasColumn('refund_requests', 'daily_rate')) {
                $table->decimal('daily_rate', 12, 2)->nullable()->after('refund_month');
            }
            if (!Schema::hasColumn('refund_requests', 'gross_salary')) {
                $table->decimal('gross_salary', 15, 2)->nullable()->after('daily_rate');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('refund_requests', function (Blueprint $table) {
            $cols = ['refund_type', 'refund_days', 'refund_month', 'daily_rate', 'gross_salary'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('refund_requests', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
