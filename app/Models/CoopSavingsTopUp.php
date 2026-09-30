<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoopSavingsTopUp extends Model
{
    use HasFactory;

    protected $table = 'coop_savings_top_ups';

    protected $fillable = [
        'staffId',
        'savings_setup_id',
        'top_up_reference',
        'amount',
        'balance_before',
        'balance_after',
        'payment_method',
        'payment_reference',
        'payment_date',
        'proof_of_payment',
        'notes',
        'processed_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'balance_before' => 'float',
        'balance_after' => 'float',
        'payment_date' => 'date',
    ];

    /**
     * Get the staff member for this top up.
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'staffId', 'ID');
    }

    /**
     * Get the cooperative savings setup.
     */
    public function savingsSetup()
    {
        return $this->belongsTo(CoopSavingsSetup::class, 'savings_setup_id', 'id');
    }

    /**
     * Get the admin/processor who recorded the top up.
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by', 'id');
    }
}
