<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    $columns = DB::select("DESCRIBE tblotherinfoforstaffdocumentation");
    echo json_encode($columns, JSON_PRETTY_PRINT);
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage();
}
