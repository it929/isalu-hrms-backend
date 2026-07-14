<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$courts = DB::table('tbl_court')->get();
echo "COURTS:\n";
print_r($courts);

$activeMonth = DB::table('tblactivemonth')->get();
echo "\nACTIVE MONTH:\n";
print_r($activeMonth);
