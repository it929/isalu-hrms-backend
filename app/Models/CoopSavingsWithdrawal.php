<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoopSavingsWithdrawal extends Model
{
    protected $table = 'coop_savings_withdrawals';

    protected $fillable = [
        'withdrawal_reference',
        'staffId',
        'savings_setup_id',
        'withdrawal_type',
        'current_savings_balance',
        'active_loan_balance',
        'collateral_locked_amount',
        'max_withdrawable_amount',
        'requested_amount',
        'approved_amount',
        'reason',
        'bank_name',
        'account_number',
        'account_name',
        'status',
        'hr_reviewed_by',
        'hr_reviewed_at',
        'hr_notes',
        'audit_reviewed_by',
        'audit_reviewed_at',
        'audit_notes',
        'finance_paid_by',
        'finance_paid_at',
        'payment_method',
        'payment_reference',
        'payment_date',
        'payment_proof',
        'finance_notes',
        'balance_before_payout',
        'balance_after_payout',
        'rejection_reason',
        'created_by',
    ];

    protected $casts = [
        'current_savings_balance' => 'decimal:2',
        'active_loan_balance' => 'decimal:2',
        'collateral_locked_amount' => 'decimal:2',
        'max_withdrawable_amount' => 'decimal:2',
        'requested_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'balance_before_payout' => 'decimal:2',
        'balance_after_payout' => 'decimal:2',
        'hr_reviewed_at' => 'datetime',
        'audit_reviewed_at' => 'datetime',
        'finance_paid_at' => 'datetime',
        'payment_date' => 'date',
    ];

    public function staff()
    {
        return $this->belongsTo(tblper::class, 'staffId', 'ID');
    }

    public function savingsSetup()
    {
        return $this->belongsTo(CoopSavingsSetup::class, 'savings_setup_id', 'id');
    }
}
