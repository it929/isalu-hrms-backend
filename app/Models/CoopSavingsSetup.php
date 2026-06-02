<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoopSavingsSetup extends Model
{
    use HasFactory;

    protected $table = 'coop_savings_setups';

    protected $fillable = [
        'staffId',
        'monthly_saving',
        'saving_balance',
        'start_month',
        'is_active',
    ];

    /**
     * Get the staff member associated with the savings setup.
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'staffId', 'ID');
    }
}
