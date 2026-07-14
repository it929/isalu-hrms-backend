<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReplaceCourtNameWithIsaluHospitalLimited extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Update tbl_court ID 9 to ISALU HOSPITAL LIMITED
        if (Schema::hasTable('tbl_court')) {
            DB::table('tbl_court')
                ->where('id', 9)
                ->update([
                    'court_name' => 'ISALU HOSPITAL LIMITED',
                    'courtAbbr'  => 'ISALU'
                ]);
        }

        // 2. Update existing audit log entries
        if (Schema::hasTable('audit_log')) {
            DB::table('audit_log')
                ->where('operation', 'LIKE', '%SUPREME COURT OF NIGERIA%')
                ->get()
                ->each(function ($log) {
                    $newOp = str_ireplace('SUPREME COURT OF NIGERIA', 'ISALU HOSPITAL LIMITED', $log->operation);
                    DB::table('audit_log')
                        ->where('auditID', $log->auditID)
                        ->update(['operation' => $newOp]);
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
        // Restore SCN
        if (Schema::hasTable('tbl_court')) {
            DB::table('tbl_court')
                ->where('id', 9)
                ->update([
                    'court_name' => 'SUPREME COURT OF NIGERIA',
                    'courtAbbr'  => 'SCN'
                ]);
        }
    }
}
