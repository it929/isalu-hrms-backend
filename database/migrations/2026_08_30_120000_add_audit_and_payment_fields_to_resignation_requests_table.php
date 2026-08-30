<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuditAndPaymentFieldsToResignationRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('resignation_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('resignation_requests', 'audit_status')) {
                $table->tinyInteger('audit_status')->default(0)->after('admin_date');
            }
            if (!Schema::hasColumn('resignation_requests', 'audit_id')) {
                $table->integer('audit_id')->nullable()->after('audit_status');
            }
            if (!Schema::hasColumn('resignation_requests', 'audit_date')) {
                $table->dateTime('audit_date')->nullable()->after('audit_id');
            }
            if (!Schema::hasColumn('resignation_requests', 'audit_remarks')) {
                $table->text('audit_remarks')->nullable()->after('audit_date');
            }
            if (!Schema::hasColumn('resignation_requests', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('finance_date');
            }
            if (!Schema::hasColumn('resignation_requests', 'finance_remarks')) {
                $table->text('finance_remarks')->nullable()->after('payment_reference');
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
        Schema::table('resignation_requests', function (Blueprint $table) {
            $cols = ['audit_status', 'audit_id', 'audit_date', 'audit_remarks', 'payment_reference', 'finance_remarks'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('resignation_requests', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
