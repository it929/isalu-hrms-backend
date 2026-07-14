<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = DB::select('SHOW TABLES');
$dbName = env('DB_DATABASE', 'forge');
$keyName = "Tables_in_" . $dbName;

echo "Searching database for 'SUPREME COURT'...\n";

foreach ($tables as $tableObj) {
    $tableName = $tableObj->$keyName ?? null;
    if (!$tableName) {
        // Fallback for sqlite or different driver
        $tableName = current((array)$tableObj);
    }
    
    // Skip audit_log table if we just want to see configuration tables, or include it
    // Let's include all tables
    try {
        $columns = Schema::getColumnListing($tableName);
        foreach ($columns as $column) {
            // Only search string/text type columns if possible, or just search
            $results = DB::table($tableName)
                ->where($column, 'LIKE', '%SUPREME COURT%')
                ->get();
            
            if ($results->isNotEmpty()) {
                echo "Table: {$tableName}, Column: {$column}, Matches: " . $results->count() . "\n";
                // Print the first few matches
                foreach ($results->take(3) as $match) {
                    print_r($match);
                }
            }
        }
    } catch (\Throwable $e) {
        // Ignore table errors
    }
}
