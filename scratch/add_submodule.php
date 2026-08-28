<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$route = 'dashboard/payroll/staff-monthly-spreadsheet';
$exists = DB::table('submodule')->where('route', $route)->first();

if (!$exists) {
    $maxId = (int)DB::table('submodule')->max('submoduleID') + 1;
    DB::table('submodule')->insert([
        'submoduleID' => $maxId,
        'moduleID' => 5,
        'submodulename' => 'Staff Monthly Spreadsheet',
        'route' => $route,
        'sub_module_rank' => 10,
        'status' => 'active'
    ]);
    
    $rolesWithPayroll = DB::table('assign_module_role')->where('moduleID', 5)->pluck('roleID')->unique();
    foreach ($rolesWithPayroll as $rId) {
        DB::table('assign_module_role')->insertOrIgnore([
            'roleID' => $rId,
            'moduleID' => 5,
            'submoduleID' => $maxId
        ]);
    }
    echo "Successfully inserted submodule ID: {$maxId} and assigned to " . count($rolesWithPayroll) . " roles.\n";
} else {
    echo "Submodule already exists with ID: {$exists->submoduleID}\n";
    $rolesWithPayroll = DB::table('assign_module_role')->where('moduleID', 5)->pluck('roleID')->unique();
    foreach ($rolesWithPayroll as $rId) {
        DB::table('assign_module_role')->insertOrIgnore([
            'roleID' => $rId,
            'moduleID' => 5,
            'submoduleID' => $exists->submoduleID
        ]);
    }
}
