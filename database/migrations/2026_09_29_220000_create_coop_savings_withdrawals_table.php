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
        // 1. Create coop_savings_withdrawals table
        if (!Schema::hasTable('coop_savings_withdrawals')) {
            Schema::create('coop_savings_withdrawals', function (Blueprint $table) {
                $table->id();
                $table->string('withdrawal_reference', 40)->unique();
                $table->unsignedBigInteger('staffId');
                $table->unsignedBigInteger('savings_setup_id');
                $table->enum('withdrawal_type', ['partial', 'full'])->default('partial');
                $table->decimal('current_savings_balance', 15, 2)->default(0.00);
                $table->decimal('active_loan_balance', 15, 2)->default(0.00);
                $table->decimal('collateral_locked_amount', 15, 2)->default(0.00);
                $table->decimal('max_withdrawable_amount', 15, 2)->default(0.00);
                $table->decimal('requested_amount', 15, 2);
                $table->decimal('approved_amount', 15, 2)->nullable();
                $table->text('reason')->nullable();

                // Bank details for disbursement
                $table->string('bank_name', 100)->nullable();
                $table->string('account_number', 50)->nullable();
                $table->string('account_name', 150)->nullable();

                // Approval stage: pending -> hr_approved -> audit_approved -> paid (or rejected at any stage)
                $table->enum('status', [
                    'pending',
                    'hr_approved',
                    'hr_rejected',
                    'audit_approved',
                    'audit_rejected',
                    'paid',
                    'finance_rejected'
                ])->default('pending');

                // HR Head review
                $table->unsignedBigInteger('hr_reviewed_by')->nullable();
                $table->timestamp('hr_reviewed_at')->nullable();
                $table->text('hr_notes')->nullable();

                // Audit review
                $table->unsignedBigInteger('audit_reviewed_by')->nullable();
                $table->timestamp('audit_reviewed_at')->nullable();
                $table->text('audit_notes')->nullable();

                // Finance payment
                $table->unsignedBigInteger('finance_paid_by')->nullable();
                $table->timestamp('finance_paid_at')->nullable();
                $table->string('payment_method', 50)->nullable(); // bank_transfer, cheque, cash
                $table->string('payment_reference', 100)->nullable();
                $table->date('payment_date')->nullable();
                $table->string('payment_proof', 255)->nullable();
                $table->text('finance_notes')->nullable();

                // Settlement balance audit
                $table->decimal('balance_before_payout', 15, 2)->nullable();
                $table->decimal('balance_after_payout', 15, 2)->nullable();

                // Rejection details
                $table->text('rejection_reason')->nullable();

                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                // Indexes
                $table->index('staffId');
                $table->index('savings_setup_id');
                $table->index('status');
                $table->index('withdrawal_type');
                $table->index('created_at');
            });
        }

        // 2. Seed submodule in module 5 (Payroll Setup)
        $existsPayroll = DB::table('submodule')
            ->where('moduleID', 5)
            ->where(function ($q) {
                $q->where('submodulename', 'Coop Savings Withdrawal')
                  ->orWhere('route', 'dashboard/payroll/coop-savings-withdrawal');
            })
            ->first();

        if (!$existsPayroll) {
            $payrollSubId = DB::table('submodule')->insertGetId([
                'moduleID' => 5,
                'submodulename' => 'Coop Savings Withdrawal',
                'route' => 'dashboard/payroll/coop-savings-withdrawal',
                'sub_module_rank' => 13,
                'status' => 1,
                'created_at' => now(),
            ]);

            // Assign permissions across all roles with access to module 5
            $payrollRoles = DB::table('assign_module_role')
                ->where('moduleID', 5)
                ->pluck('roleID')
                ->unique();

            foreach ($payrollRoles as $roleId) {
                DB::table('assign_module_role')->updateOrInsert(
                    ['roleID' => $roleId, 'moduleID' => 5, 'submoduleID' => $payrollSubId],
                    ['created_at' => now()]
                );
            }
        }

        // 3. Seed submodule in module 8 (Employees / Self-Service)
        $existsEmployees = DB::table('submodule')
            ->where('moduleID', 8)
            ->where(function ($q) {
                $q->where('submodulename', 'Coop Savings Withdrawal')
                  ->orWhere('route', 'dashboard/payroll/coop-savings-withdrawal');
            })
            ->first();

        if (!$existsEmployees) {
            $empSubId = DB::table('submodule')->insertGetId([
                'moduleID' => 8,
                'submodulename' => 'Coop Savings Withdrawal',
                'route' => 'dashboard/payroll/coop-savings-withdrawal',
                'sub_module_rank' => 21,
                'status' => 1,
                'created_at' => now(),
            ]);

            // Assign permissions across all roles with access to module 8
            $empRoles = DB::table('assign_module_role')
                ->where('moduleID', 8)
                ->pluck('roleID')
                ->unique();

            foreach ($empRoles as $roleId) {
                DB::table('assign_module_role')->updateOrInsert(
                    ['roleID' => $roleId, 'moduleID' => 8, 'submoduleID' => $empSubId],
                    ['created_at' => now()]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete assigned submodule roles and submodules
        $submoduleIds = DB::table('submodule')
            ->whereIn('route', ['dashboard/payroll/coop-savings-withdrawal', '/dashboard/payroll/coop-savings-withdrawal'])
            ->orWhere('submodulename', 'Coop Savings Withdrawal')
            ->pluck('submoduleID');

        if ($submoduleIds->isNotEmpty()) {
            DB::table('assign_module_role')->whereIn('submoduleID', $submoduleIds)->delete();
            DB::table('submodule')->whereIn('submoduleID', $submoduleIds)->delete();
        }

        Schema::dropIfExists('coop_savings_withdrawals');
    }
};
