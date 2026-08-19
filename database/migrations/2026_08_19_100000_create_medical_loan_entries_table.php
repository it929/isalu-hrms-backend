<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateMedicalLoanEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('medical_loan_entries')) {
            Schema::create('medical_loan_entries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('staffId')->index();
                $table->date('loan_date')->index();
                $table->decimal('amount', 15, 2);
                $table->text('reason');
                $table->decimal('balance_before', 15, 2)->default(0.00);
                $table->decimal('balance_after', 15, 2)->default(0.00);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }

        // Seed Submodule into Payroll module (moduleID = 5)
        $exists = DB::table('submodule')
            ->where('submodulename', 'Medical Loan Entry')
            ->orWhere('route', 'dashboard/payroll/medical-loan-entry')
            ->exists();

        if (!$exists) {
            $subId = DB::table('submodule')->insertGetId([
                'moduleID' => 5,
                'submodulename' => 'Medical Loan Entry',
                'route' => 'dashboard/payroll/medical-loan-entry',
                'sub_module_rank' => 12,
                'status' => 1,
                'created_at' => now(),
            ]);

            // Assign submodule permissions across all roles with access to module 5
            $roles = DB::table('assign_module_role')
                ->where('moduleID', 5)
                ->pluck('roleID')
                ->unique();

            foreach ($roles as $roleId) {
                DB::table('assign_module_role')->updateOrInsert(
                    ['roleID' => $roleId, 'moduleID' => 5, 'submoduleID' => $subId],
                    ['created_at' => now()]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sub = DB::table('submodule')
            ->where('submodulename', 'Medical Loan Entry')
            ->orWhere('route', 'dashboard/payroll/medical-loan-entry')
            ->first();

        if ($sub) {
            DB::table('assign_module_role')->where('submoduleID', $sub->submoduleID)->delete();
            DB::table('submodule')->where('submoduleID', $sub->submoduleID)->delete();
        }

        Schema::dropIfExists('medical_loan_entries');
    }
}
