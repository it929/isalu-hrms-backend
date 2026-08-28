<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppraisalCriteriaItem extends Model
{
    use HasFactory;

    protected $table = 'appraisal_criteria_items';

    protected $fillable = [
        'category_id',
        'title',
        'description',
        'max_score',
        'weight',
        'rank',
    ];

    public function category()
    {
        return $this->belongsTo(AppraisalCriteriaCategory::class, 'category_id');
    }
}
