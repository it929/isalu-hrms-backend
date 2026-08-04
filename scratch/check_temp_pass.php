<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$users = DB::table('users')->whereNotNull('temp_pass')->where('temp_pass', '!=', '')->select('id', 'name', 'username', 'temp_pass', 'email')->get();
echo "Users with temp_pass:\n";
foreach ($users as $u) {
    echo "ID: {$u->id}, Name: {$u->name}, Username: {$u->username}, Email: {$u->email}, TempPass: {$u->temp_pass}\n";
}
echo "\nTotal users: " . DB::table('users')->count() . "\n";
echo "Total with non-empty temp_pass: " . $users->count() . "\n";
