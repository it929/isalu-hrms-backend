<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppraisalCriteriaCategory extends Model
{
    use HasFactory;

    protected $table = 'appraisal_criteria_categories';

    protected $fillable = [
        'template_id',
        'name',
        'weight',
        'rank',
    ];

    public function template()
    {
        return $this->belongsTo(AppraisalTemplate::class, 'template_id');
    }

    public function items()
    {
        return $this->hasMany(AppraisalCriteriaItem::class, 'category_id')->orderBy('rank', 'asc');
    }
}
