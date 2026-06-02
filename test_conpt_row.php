<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$employee = DB::table('tblper')->where('rank', '!=', 2)->where('staff_status', 1)->first();
echo "Active Employee ID: {$employee->ID}\n";

$row = DB::table('payroll_conpt')
    ->where('staffID', $employee->ID)
    ->orderBy('id', 'desc')
    ->first();

if ($row) {
    print_r($row);
} else {
    echo "No row found for employee ID {$employee->ID}\n";
}
