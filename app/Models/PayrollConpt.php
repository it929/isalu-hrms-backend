<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollConpt extends Model
{
    use HasFactory;

    protected $table = 'payroll_conpt';

    const UPDATED_AT = null;

    protected $fillable = [
        'payroll_run_id',
        'staffID',
        'basic',
        'housing',
        'transport',
        'medical',
        'utility',
        'meal',
        'paid_days',
        'gross_pay',
        'paye_tax',
        'loan_deduction',
        'pension',
        'coop_savings',
        'other_deductions',
        'total_deductions',
        'net_pay',
    ];
}
