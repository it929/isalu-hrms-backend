<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppraisalTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('appraisal_periods')) {
            Schema::create('appraisal_periods', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('title');
                $table->enum('review_type', ['annual', 'mid_year', 'quarterly', 'probation'])->default('annual');
                $table->date('start_date');
                $table->date('end_date');
                $table->date('self_review_deadline');
                $table->date('appraiser_review_deadline');
                $table->enum('status', ['draft', 'active', 'in_review', 'completed', 'closed'])->default('draft');
                $table->unsignedInteger('created_by')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('appraisal_templates')) {
            Schema::create('appraisal_templates', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('title');
                $table->unsignedInteger('department_id')->nullable();
                $table->unsignedInteger('designation_id')->nullable();
                $table->text('description')->nullable();
                $table->decimal('total_weight', 5, 2)->default(100.00);
                $table->decimal('passing_score', 5, 2)->default(50.00);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('appraisal_criteria_categories')) {
            Schema::create('appraisal_criteria_categories', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('template_id');
                $table->string('name');
                $table->decimal('weight', 5, 2)->default(20.00);
                $table->integer('rank')->default(1);
                $table->timestamps();

                $table->foreign('template_id')->references('id')->on('appraisal_templates')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('appraisal_criteria_items')) {
            Schema::create('appraisal_criteria_items', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('category_id');
                $table->string('title');
                $table->text('description')->nullable();
                $table->integer('max_score')->default(5);
                $table->decimal('weight', 5, 2)->default(0.00);
                $table->integer('rank')->default(1);
                $table->timestamps();

                $table->foreign('category_id')->references('id')->on('appraisal_criteria_categories')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('appraisal_assignments')) {
            Schema::create('appraisal_assignments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('period_id');
                $table->integer('staff_id');
                $table->integer('appraiser_id');
                $table->integer('reviewer_id')->nullable();
                $table->unsignedBigInteger('template_id');
                $table->timestamps();

                $table->foreign('period_id')->references('id')->on('appraisal_periods')->onDelete('cascade');
                $table->foreign('template_id')->references('id')->on('appraisal_templates')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('appraisal_submissions')) {
            Schema::create('appraisal_submissions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('period_id');
                $table->integer('staff_id');
                $table->integer('appraiser_id');
                $table->integer('reviewer_id')->nullable();
                $table->unsignedBigInteger('template_id');
                $table->enum('status', [
                    'pending_self_review',
                    'pending_appraiser',
                    'pending_hr_review',
                    'pending_md_approval',
                    'approved',
                    'rejected',
                    'acknowledged'
                ])->default('pending_self_review');

                $table->dateTime('self_submitted_at')->nullable();
                $table->dateTime('appraiser_submitted_at')->nullable();
                $table->dateTime('hr_reviewed_at')->nullable();
                $table->dateTime('approved_at')->nullable();
                $table->dateTime('staff_acknowledged_at')->nullable();

                $table->decimal('self_total_score', 5, 2)->nullable();
                $table->decimal('appraiser_total_score', 5, 2)->nullable();
                $table->decimal('final_weighted_score', 5, 2)->nullable();
                $table->string('performance_grade', 20)->nullable();

                $table->text('staff_key_accomplishments')->nullable();
                $table->text('staff_challenges')->nullable();
                $table->text('staff_training_needs')->nullable();

                $table->text('appraiser_strengths')->nullable();
                $table->text('appraiser_areas_for_growth')->nullable();
                $table->enum('recommendation_type', ['increment', 'promotion', 'confirmation', 'pip', 'training', 'none'])->default('none');
                $table->text('recommendation_details')->nullable();
                $table->text('hr_comments')->nullable();
                $table->text('staff_feedback')->nullable();

                $table->timestamps();

                $table->foreign('period_id')->references('id')->on('appraisal_periods')->onDelete('cascade');
                $table->foreign('template_id')->references('id')->on('appraisal_templates')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('appraisal_scores')) {
            Schema::create('appraisal_scores', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('submission_id');
                $table->unsignedBigInteger('criteria_item_id');
                $table->decimal('self_score', 4, 2)->nullable();
                $table->text('self_comment')->nullable();
                $table->decimal('appraiser_score', 4, 2)->nullable();
                $table->text('appraiser_comment')->nullable();
                $table->decimal('final_score', 4, 2)->nullable();
                $table->timestamps();

                $table->foreign('submission_id')->references('id')->on('appraisal_submissions')->onDelete('cascade');
                $table->foreign('criteria_item_id')->references('id')->on('appraisal_criteria_items')->onDelete('cascade');
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
        Schema::dropIfExists('appraisal_scores');
        Schema::dropIfExists('appraisal_submissions');
        Schema::dropIfExists('appraisal_assignments');
        Schema::dropIfExists('appraisal_criteria_items');
        Schema::dropIfExists('appraisal_criteria_categories');
        Schema::dropIfExists('appraisal_templates');
        Schema::dropIfExists('appraisal_periods');
    }
}
