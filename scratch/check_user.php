<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$user = DB::table('users')->where('id', 10214)->first();
echo "User 10214 Email: " . $user->email . "\n";
echo "User 10214 Username: " . $user->username . "\n";
// Let's set the password to 'password' (bcrypt) so we can log in
DB::table('users')->where('id', 10214)->update([
    'password' => password_hash('password', PASSWORD_BCRYPT)
]);
echo "Password reset to 'password' successfully.\n";
