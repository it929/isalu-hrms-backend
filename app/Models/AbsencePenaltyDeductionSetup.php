<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsencePenaltyDeductionSetup extends Model
{
    protected $table = 'absence_penalty_deduction_setups';

    protected $fillable = [
        'staffId',
        'deduction_type',
        'total_amount',
        'duration_months',
        'monthly_deduction',
        'balance_remaining',
        'start_month',
        'end_month',
        'is_active',
    ];

    protected $casts = [
        'staffId' => 'integer',
        'total_amount' => 'decimal:2',
        'duration_months' => 'integer',
        'monthly_deduction' => 'decimal:2',
        'balance_remaining' => 'decimal:2',
        'is_active' => 'integer',
    ];
}
