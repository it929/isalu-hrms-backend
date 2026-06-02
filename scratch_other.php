<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$setups = DB::table('other_deduction_setups')->get();
echo "OTHER DEDUCTION SETUPS:\n";
foreach ($setups as $setup) {
    echo "ID: {$setup->id}, Staff: {$setup->staffId}, Total: {$setup->total_amount}, Monthly: {$setup->monthly_deduction}, Remaining: {$setup->balance_remaining}, Active: {$setup->is_active}, Start: {$setup->start_month}, End: {$setup->end_month}\n";
}

$conpt = DB::table('payroll_conpt')->orderBy('id', 'desc')->limit(5)->get();
echo "\nPAYROLL CONPT LATEST:\n";
foreach ($conpt as $c) {
    echo "ID: {$c->id}, Staff: {$c->staffID}, Month: {$c->month}, Year: {$c->year}, Other Dedn: {$c->other_deductions}\n";
}
