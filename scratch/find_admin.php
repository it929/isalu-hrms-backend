<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$admins = DB::table('users as u')
    ->join('assign_user_role as aur', 'aur.userID', '=', 'u.id')
    ->join('user_role as r', 'r.roleID', '=', 'aur.roleID')
    ->where('r.roleID', '=', 1)
    ->select('u.id', 'u.username', 'u.name', 'u.email', 'r.roleID', 'r.rolename')
    ->distinct()
    ->get();

echo "--- DISTINCT SUPER ADMIN ACCOUNTS ---\n";
foreach ($admins as $u) {
    echo "User ID: {$u->id} | Username: {$u->username} | Name: {$u->name} | Email: {$u->email} | Role: {$u->rolename}\n";
}
