<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppraisalPeriod extends Model
{
    use HasFactory;

    protected $table = 'appraisal_periods';

    protected $fillable = [
        'title',
        'review_type',
        'start_date',
        'end_date',
        'self_review_deadline',
        'appraiser_review_deadline',
        'status',
        'created_by',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'self_review_deadline' => 'date',
        'appraiser_review_deadline' => 'date',
    ];

    public function submissions()
    {
        return $this->hasMany(AppraisalSubmission::class, 'period_id');
    }
}
