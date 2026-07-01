<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuditFieldsToRefundRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('refund_requests', function (Blueprint $table) {
            $table->tinyInteger('audit_status')->default(0)->after('admin_date');
            $table->unsignedBigInteger('audit_id')->nullable()->after('audit_status');
            $table->timestamp('audit_date')->nullable()->after('audit_id');
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
            $table->dropColumn(['audit_status', 'audit_id', 'audit_date']);
        });
    }
}
