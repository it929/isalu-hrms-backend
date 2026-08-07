<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$users = DB::table('users')
    ->leftJoin('assign_user_role', 'assign_user_role.userID', '=', 'users.id')
    ->leftJoin('user_role', 'user_role.roleID', '=', 'assign_user_role.roleID')
    ->select('users.id', 'users.username', 'users.user_type', 'assign_user_role.roleID', 'user_role.rolename')
    ->get();

use App\Http\Controllers\Api\RefundApiController;
use Illuminate\Http\Request;

$columns = Schema::getColumnListing('service_termination');
echo "COLUMNS IN service_termination TABLE:\n";
print_r($columns);














