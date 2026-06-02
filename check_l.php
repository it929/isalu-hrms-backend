<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('staffEarningAndDeduction as sc')
    ->leftJoin('tblcvSetup as cv', 'cv.ID', '=', 'sc.cv_setup_id')
    ->where('sc.staffId', 9)
    ->where(function($q) {
        $q->where('cv.system_code', 'loan_deduction')
          ->orWhere('sc.description', 'like', '%loan%')
          ->orWhere('sc.description', 'like', '%debt%')
          ->orWhereIn('sc.cv_setup_id', [3, 7]);
    })
    ->select('sc.id', 'sc.description', 'sc.amount', 'sc.target_amount', 'sc.total_deducted', 'sc.no_limit')
    ->get();

foreach ($rows as $r) {
    $bal = ($r->target_amount !== null && $r->target_amount > $r->total_deducted) ? ($r->target_amount - $r->total_deducted) : 0;
    echo "ID: {$r->id} | Desc: {$r->description} | Target: {$r->target_amount} | Deducted: {$r->total_deducted} | Calculated Bal: {$bal}\n";
}
