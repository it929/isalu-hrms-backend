<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "tblper count: " . DB::table('tblper')->count() . "\n";
echo "assign_user_role count: " . DB::table('assign_user_role')->count() . "\n";
echo "leave_record count: " . DB::table('leave_record')->count() . "\n";

// Show indexes
$tblperIndexes = DB::select("SHOW INDEX FROM tblper");
echo "\ntblper indexes:\n";
foreach ($tblperIndexes as $idx) {
    echo "- {$idx->Key_name} (Column: {$idx->Column_name})\n";
}

$assignUserRoleIndexes = DB::select("SHOW INDEX FROM assign_user_role");
echo "\nassign_user_role indexes:\n";
foreach ($assignUserRoleIndexes as $idx) {
    echo "- {$idx->Key_name} (Column: {$idx->Column_name})\n";
}
