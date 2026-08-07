<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$newPassword = $argv[1] ?? '123456';
$hashed = Hash::make($newPassword);

// Reset password for Super Admin accounts (User ID 6 and 10016)
DB::table('users')->whereIn('id', [6, 10016])->update([
    'password' => $hashed,
    'first_login' => 1,
    'updated_at' => now()
]);

echo "Successfully updated password for Super Admin accounts to: {$newPassword}\n";
