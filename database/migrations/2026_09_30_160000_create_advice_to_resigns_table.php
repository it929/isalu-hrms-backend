<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdviceToResignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('advice_to_resigns')) {
            Schema::create('advice_to_resigns', function (Blueprint $table) {
                $table->id();
                $table->string('reference_no', 50)->unique();
                $table->integer('staff_id')->index();
                $table->date('issue_date');
                $table->date('deadline_date');
                $table->string('reason');
                $table->text('details')->nullable();
                $table->string('query_reference', 100)->nullable();
                $table->string('consequence_if_defaulted', 255)->default('Termination of Employment');
                $table->enum('status', ['pending', 'complied', 'terminated', 'withdrawn'])->default('pending');
                $table->date('compliance_date')->nullable();
                $table->unsignedBigInteger('resignation_request_id')->nullable();
                $table->text('resolution_remarks')->nullable();
                $table->integer('issued_by')->nullable();
                $table->integer('action_by')->nullable();
                $table->dateTime('action_date')->nullable();
                $table->timestamps();
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
        Schema::dropIfExists('advice_to_resigns');
    }
}
