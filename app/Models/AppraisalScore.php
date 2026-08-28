<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppraisalScore extends Model
{
    use HasFactory;

    protected $table = 'appraisal_scores';

    protected $fillable = [
        'submission_id',
        'criteria_item_id',
        'self_score',
        'self_comment',
        'appraiser_score',
        'appraiser_comment',
        'final_score',
    ];

    public function submission()
    {
        return $this->belongsTo(AppraisalSubmission::class, 'submission_id');
    }

    public function criteriaItem()
    {
        return $this->belongsTo(AppraisalCriteriaItem::class, 'criteria_item_id');
    }
}
