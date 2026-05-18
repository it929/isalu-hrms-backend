<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $columns = DB::select("DESCRIBE tblper");
    $names = array_map(fn($col) => $col->Field, $columns);
    echo json_encode($names, JSON_PRETTY_PRINT);
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage();
}
