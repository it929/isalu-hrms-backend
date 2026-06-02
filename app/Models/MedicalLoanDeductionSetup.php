<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalLoanDeductionSetup extends Model
{
    use HasFactory;

    protected $table = 'medical_loan_deduction_setups';

    protected $fillable = [
        'staffId',
        'loan_amount',
        'duration_months',
        'monthly_deduction',
        'balance_remaining',
        'start_month',
        'end_month',
        'is_active',
    ];

    /**
     * Get the staff member associated with the medical loan setup.
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'staffId', 'ID');
    }
}
