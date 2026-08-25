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
        if (!Schema::hasTable('user_activity_logs')) {
            Schema::create('user_activity_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->integer('staff_id')->nullable()->index();
                $table->string('user_name', 255)->nullable();
                $table->string('role_name', 100)->nullable();
                $table->string('activity_type', 50)->index(); // login, logout, create, update, delete, approval, export, payroll, hr, system
                $table->string('action', 255); // Human readable description
                $table->string('module', 100)->nullable()->index(); // Authentication, HR, Payroll, Roles, Loans, Reports
                $table->string('method', 10)->default('POST');
                $table->text('url')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('details')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
    }
};
