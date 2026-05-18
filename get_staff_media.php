<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $staff = DB::table('tblper')->orderBy('ID', 'desc')->get(['ID', 'surname', 'first_name', 'passport_url', 'signature_url']);
    echo json_encode($staff, JSON_PRETTY_PRINT);
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage();
}
