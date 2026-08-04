<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$modules = DB::table('module')->select('modulename', 'link_type')->distinct()->orderBy('link_type')->get();
foreach ($modules as $m) {
    echo "modulename: {$m->modulename}, link_type: " . var_export($m->link_type, true) . " (type: " . gettype($m->link_type) . ")\n";
}
