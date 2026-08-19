<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalLoanEntry extends Model
{
    use HasFactory;

    protected $table = 'medical_loan_entries';

    protected $fillable = [
        'staffId',
        'loan_date',
        'amount',
        'reason',
        'balance_before',
        'balance_after',
        'monthly_deduction',
        'created_by',
    ];

    /**
     * Get the staff member associated with the medical loan entry.
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'staffId', 'ID');
    }
}
