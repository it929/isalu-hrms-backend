<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdviceToResign extends Model
{
    protected $table = 'advice_to_resigns';

    protected $fillable = [
        'reference_no',
        'staff_id',
        'issue_date',
        'deadline_date',
        'reason',
        'details',
        'query_reference',
        'consequence_if_defaulted',
        'status',
        'compliance_date',
        'resignation_request_id',
        'resolution_remarks',
        'issued_by',
        'action_by',
        'action_date',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'deadline_date' => 'date',
        'compliance_date' => 'date',
        'action_date' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id', 'ID');
    }
}
