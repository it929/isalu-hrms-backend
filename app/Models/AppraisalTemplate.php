<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppraisalTemplate extends Model
{
    use HasFactory;

    protected $table = 'appraisal_templates';

    protected $fillable = [
        'title',
        'department_id',
        'designation_id',
        'description',
        'total_weight',
        'passing_score',
        'is_active',
    ];

    public function categories()
    {
        return $this->hasMany(AppraisalCriteriaCategory::class, 'template_id')->orderBy('rank', 'asc');
    }
}
