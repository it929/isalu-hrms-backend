<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddWorkflowFieldsToAdviceToResignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('advice_to_resigns')) {
            // Modify status column from ENUM to VARCHAR(50) so it supports extended workflow statuses
            try {
                DB::statement("ALTER TABLE advice_to_resigns MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'pending'");
            } catch (\Throwable $th) {
                // Ignore if already altered or on DB drivers not supporting raw ALTER
            }

            Schema::table('advice_to_resigns', function (Blueprint $table) {
                if (!Schema::hasColumn('advice_to_resigns', 'applied_date')) {
                    $table->date('applied_date')->nullable()->after('compliance_date');
                }
                if (!Schema::hasColumn('advice_to_resigns', 'staff_remarks')) {
                    $table->text('staff_remarks')->nullable()->after('applied_date');
                }
                if (!Schema::hasColumn('advice_to_resigns', 'hr_approved_by')) {
                    $table->integer('hr_approved_by')->nullable()->after('staff_remarks');
                }
                if (!Schema::hasColumn('advice_to_resigns', 'hr_approved_at')) {
                    $table->dateTime('hr_approved_at')->nullable()->after('hr_approved_by');
                }
                if (!Schema::hasColumn('advice_to_resigns', 'hr_remarks')) {
                    $table->text('hr_remarks')->nullable()->after('hr_approved_at');
                }
                if (!Schema::hasColumn('advice_to_resigns', 'audit_by')) {
                    $table->integer('audit_by')->nullable()->after('hr_remarks');
                }
                if (!Schema::hasColumn('advice_to_resigns', 'audit_at')) {
                    $table->dateTime('audit_at')->nullable()->after('audit_by');
                }
                if (!Schema::hasColumn('advice_to_resigns', 'audit_remarks')) {
                    $table->text('audit_remarks')->nullable()->after('audit_at');
                }
                if (!Schema::hasColumn('advice_to_resigns', 'audit_status')) {
                    $table->tinyInteger('audit_status')->default(0)->after('audit_remarks');
                }
                if (!Schema::hasColumn('advice_to_resigns', 'finance_by')) {
                    $table->integer('finance_by')->nullable()->after('audit_status');
                }
                if (!Schema::hasColumn('advice_to_resigns', 'finance_at')) {
                    $table->dateTime('finance_at')->nullable()->after('finance_by');
                }
                if (!Schema::hasColumn('advice_to_resigns', 'finance_remarks')) {
                    $table->text('finance_remarks')->nullable()->after('finance_at');
                }
                if (!Schema::hasColumn('advice_to_resigns', 'finance_status')) {
                    $table->tinyInteger('finance_status')->default(0)->after('finance_remarks');
                }
                if (!Schema::hasColumn('advice_to_resigns', 'payment_reference')) {
                    $table->string('payment_reference', 100)->nullable()->after('finance_status');
                }
                if (!Schema::hasColumn('advice_to_resigns', 'settlement_summary')) {
                    $table->longText('settlement_summary')->nullable()->after('payment_reference');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('advice_to_resigns')) {
            Schema::table('advice_to_resigns', function (Blueprint $table) {
                $cols = [
                    'applied_date',
                    'staff_remarks',
                    'hr_approved_by',
                    'hr_approved_at',
                    'hr_remarks',
                    'audit_by',
                    'audit_at',
                    'audit_remarks',
                    'audit_status',
                    'finance_by',
                    'finance_at',
                    'finance_remarks',
                    'finance_status',
                    'payment_reference',
                    'settlement_summary',
                ];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('advice_to_resigns', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
}
