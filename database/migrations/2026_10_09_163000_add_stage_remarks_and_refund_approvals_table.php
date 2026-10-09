<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('refund_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('refund_requests', 'hod_remarks')) {
                $table->text('hod_remarks')->nullable()->after('hod_date');
            }
            if (!Schema::hasColumn('refund_requests', 'admin_remarks')) {
                $table->text('admin_remarks')->nullable()->after('admin_date');
            }
            if (!Schema::hasColumn('refund_requests', 'audit_remarks')) {
                $table->text('audit_remarks')->nullable()->after('audit_date');
            }
            if (!Schema::hasColumn('refund_requests', 'finance_remarks')) {
                $table->text('finance_remarks')->nullable()->after('finance_date');
            }
        });

        if (!Schema::hasTable('refund_approvals')) {
            Schema::create('refund_approvals', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('refund_id')->index();
                $table->string('level', 50); // 'HOD', 'HR', 'Audit', 'Finance'
                $table->unsignedBigInteger('approver_id')->nullable();
                $table->tinyInteger('status')->default(1); // 1 = Approved, 2 = Rejected
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }

        // Backfill data for existing refund requests
        try {
            // Specific known audit trail from user activity logs:
            // Refund #25: HR ("salary refund") on 2026-10-09 13:33:39 and Audit ("cooperative refund") on 2026-10-09 13:34:52
            DB::table('refund_requests')->where('id', 25)->update([
                'admin_remarks' => 'salary refund',
                'audit_remarks' => 'cooperative refund',
            ]);

            DB::table('refund_approvals')->insert([
                [
                    'refund_id'   => 25,
                    'level'       => 'HR',
                    'approver_id' => 6,
                    'status'      => 1,
                    'remarks'     => 'salary refund',
                    'created_at'  => '2026-10-09 13:33:39',
                    'updated_at'  => '2026-10-09 13:33:39',
                ],
                [
                    'refund_id'   => 25,
                    'level'       => 'Audit',
                    'approver_id' => 6,
                    'status'      => 1,
                    'remarks'     => 'cooperative refund',
                    'created_at'  => '2026-10-09 13:34:52',
                    'updated_at'  => '2026-10-09 13:34:52',
                ]
            ]);

            // Refund #22: HOD ("jhdjdvbdvgdh") on 2026-09-15 18:47:31
            DB::table('refund_requests')->where('id', 22)->update([
                'hod_remarks' => 'jhdjdvbdvgdh',
            ]);

            DB::table('refund_approvals')->insert([
                [
                    'refund_id'   => 22,
                    'level'       => 'HOD',
                    'approver_id' => 6,
                    'status'      => 1,
                    'remarks'     => 'jhdjdvbdvgdh',
                    'created_at'  => '2026-09-15 18:47:31',
                    'updated_at'  => '2026-09-15 18:47:31',
                ]
            ]);

            // General backfill for any other refunds that have an overall remark
            $refunds = DB::table('refund_requests')->get();
            foreach ($refunds as $rf) {
                if ($rf->id == 25 || $rf->id == 22) continue;

                $rem = !empty($rf->remarks) ? $rf->remarks : null;
                if (!$rem) continue;

                if ($rf->finance_status == 1 && empty($rf->finance_remarks)) {
                    DB::table('refund_requests')->where('id', $rf->id)->update(['finance_remarks' => $rem]);
                    DB::table('refund_approvals')->insert([
                        'refund_id'   => $rf->id,
                        'level'       => 'Finance',
                        'approver_id' => $rf->finance_id,
                        'status'      => 1,
                        'remarks'     => $rem,
                        'created_at'  => $rf->finance_date ?: now(),
                        'updated_at'  => $rf->finance_date ?: now(),
                    ]);
                } elseif ($rf->audit_status == 1 && empty($rf->audit_remarks)) {
                    DB::table('refund_requests')->where('id', $rf->id)->update(['audit_remarks' => $rem]);
                    DB::table('refund_approvals')->insert([
                        'refund_id'   => $rf->id,
                        'level'       => 'Audit',
                        'approver_id' => $rf->audit_id,
                        'status'      => 1,
                        'remarks'     => $rem,
                        'created_at'  => $rf->audit_date ?: now(),
                        'updated_at'  => $rf->audit_date ?: now(),
                    ]);
                } elseif ($rf->admin_status == 1 && empty($rf->admin_remarks)) {
                    DB::table('refund_requests')->where('id', $rf->id)->update(['admin_remarks' => $rem]);
                    DB::table('refund_approvals')->insert([
                        'refund_id'   => $rf->id,
                        'level'       => 'HR',
                        'approver_id' => $rf->admin_id,
                        'status'      => 1,
                        'remarks'     => $rem,
                        'created_at'  => $rf->admin_date ?: now(),
                        'updated_at'  => $rf->admin_date ?: now(),
                    ]);
                } elseif ($rf->hod_status == 1 && empty($rf->hod_remarks)) {
                    DB::table('refund_requests')->where('id', $rf->id)->update(['hod_remarks' => $rem]);
                    DB::table('refund_approvals')->insert([
                        'refund_id'   => $rf->id,
                        'level'       => 'HOD',
                        'approver_id' => $rf->hod_id,
                        'status'      => 1,
                        'remarks'     => $rem,
                        'created_at'  => $rf->hod_date ?: now(),
                        'updated_at'  => $rf->hod_date ?: now(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Refund remarks migration backfill notice: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_approvals');

        Schema::table('refund_requests', function (Blueprint $table) {
            $cols = ['hod_remarks', 'admin_remarks', 'audit_remarks', 'finance_remarks'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('refund_requests', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
