<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppraisalSubmission extends Model
{
    use HasFactory;

    protected $table = 'appraisal_submissions';

    protected $fillable = [
        'period_id',
        'staff_id',
        'appraiser_id',
        'reviewer_id',
        'template_id',
        'status',
        'self_submitted_at',
        'appraiser_submitted_at',
        'hr_reviewed_at',
        'approved_at',
        'staff_acknowledged_at',
        'self_total_score',
        'appraiser_total_score',
        'final_weighted_score',
        'performance_grade',
        'staff_key_accomplishments',
        'staff_challenges',
        'staff_training_needs',
        'appraiser_strengths',
        'appraiser_areas_for_growth',
        'recommendation_type',
        'recommendation_details',
        'hr_comments',
        'staff_feedback',
    ];

    public function period()
    {
        return $this->belongsTo(AppraisalPeriod::class, 'period_id');
    }

    public function template()
    {
        return $this->belongsTo(AppraisalTemplate::class, 'template_id');
    }

    public function scores()
    {
        return $this->hasMany(AppraisalScore::class, 'submission_id');
    }
}
