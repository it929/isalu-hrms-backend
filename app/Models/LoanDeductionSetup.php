<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanDeductionSetup extends Model
{
    use HasFactory;

    protected $table = 'loan_deduction_setups';

    protected $fillable = [
        'staffId',
        'loan_amount',
        'interest_rate',
        'duration_months',
        'monthly_deduction',
        'balance_remaining',
        'start_month',
        'end_month',
        'is_active',
    ];
}
