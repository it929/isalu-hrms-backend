<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoticeInLieuToResignationRequestsAndAdviceToResigns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('resignation_requests')) {
            Schema::table('resignation_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('resignation_requests', 'notice_in_lieu_amount')) {
                    $table->decimal('notice_in_lieu_amount', 15, 2)->default(0.00)->after('finance_remarks');
                }
                if (!Schema::hasColumn('resignation_requests', 'notice_in_lieu_days')) {
                    $table->integer('notice_in_lieu_days')->default(0)->after('notice_in_lieu_amount');
                }
                if (!Schema::hasColumn('resignation_requests', 'notice_in_lieu_mode')) {
                    $table->string('notice_in_lieu_mode', 30)->default('amount')->after('notice_in_lieu_days');
                }
                if (!Schema::hasColumn('resignation_requests', 'notice_in_lieu_source')) {
                    $table->string('notice_in_lieu_source', 100)->default('general')->after('notice_in_lieu_mode');
                }
                if (!Schema::hasColumn('resignation_requests', 'notice_in_lieu_reason')) {
                    $table->text('notice_in_lieu_reason')->nullable()->after('notice_in_lieu_source');
                }
                if (!Schema::hasColumn('resignation_requests', 'notice_in_lieu_updated_by')) {
                    $table->unsignedBigInteger('notice_in_lieu_updated_by')->nullable()->after('notice_in_lieu_reason');
                }
                if (!Schema::hasColumn('resignation_requests', 'notice_in_lieu_updated_at')) {
                    $table->dateTime('notice_in_lieu_updated_at')->nullable()->after('notice_in_lieu_updated_by');
                }
            });
        }

        if (Schema::hasTable('advice_to_resigns')) {
            Schema::table('advice_to_resigns', function (Blueprint $table) {
                if (!Schema::hasColumn('advice_to_resigns', 'notice_in_lieu_amount')) {
                    $table->decimal('notice_in_lieu_amount', 15, 2)->default(0.00)->after('finance_remarks');
                }
                if (!Schema::hasColumn('advice_to_resigns', 'notice_in_lieu_days')) {
                    $table->integer('notice_in_lieu_days')->default(0)->after('notice_in_lieu_amount');
                }
                if (!Schema::hasColumn('advice_to_resigns', 'notice_in_lieu_mode')) {
                    $table->string('notice_in_lieu_mode', 30)->default('amount')->after('notice_in_lieu_days');
                }
                if (!Schema::hasColumn('advice_to_resigns', 'notice_in_lieu_source')) {
                    $table->string('notice_in_lieu_source', 100)->default('general')->after('notice_in_lieu_mode');
                }
                if (!Schema::hasColumn('advice_to_resigns', 'notice_in_lieu_reason')) {
                    $table->text('notice_in_lieu_reason')->nullable()->after('notice_in_lieu_source');
                }
                if (!Schema::hasColumn('advice_to_resigns', 'notice_in_lieu_updated_by')) {
                    $table->unsignedBigInteger('notice_in_lieu_updated_by')->nullable()->after('notice_in_lieu_reason');
                }
                if (!Schema::hasColumn('advice_to_resigns', 'notice_in_lieu_updated_at')) {
                    $table->dateTime('notice_in_lieu_updated_at')->nullable()->after('notice_in_lieu_updated_by');
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
        $cols = [
            'notice_in_lieu_amount',
            'notice_in_lieu_days',
            'notice_in_lieu_mode',
            'notice_in_lieu_source',
            'notice_in_lieu_reason',
            'notice_in_lieu_updated_by',
            'notice_in_lieu_updated_at'
        ];

        if (Schema::hasTable('resignation_requests')) {
            Schema::table('resignation_requests', function (Blueprint $table) use ($cols) {
                foreach ($cols as $col) {
                    if (Schema::hasColumn('resignation_requests', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('advice_to_resigns')) {
            Schema::table('advice_to_resigns', function (Blueprint $table) use ($cols) {
                foreach ($cols as $col) {
                    if (Schema::hasColumn('advice_to_resigns', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
}
