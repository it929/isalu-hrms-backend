<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$sub = DB::table('tblsubmodule')->where('submodulename', 'like', '%resign%')->get();
foreach ($sub as $s) {
    echo "SubmoduleID: {$s->submoduleID}, Name: {$s->submodulename}, Route: {$s->route}, ModuleID: {$s->moduleID}\n";
}
