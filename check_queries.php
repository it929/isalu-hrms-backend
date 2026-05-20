<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

function benchmark($name, $callback) {
    $start = microtime(true);
    $callback();
    $end = microtime(true);
    echo "$name: " . number_format(($end - $start) * 1000, 2) . " ms\n";
}

$userId = 1; // test user ID, adjust if needed

benchmark("Query 1: isSuperAdmin check", function() use ($userId) {
    DB::table('assign_user_role')
        ->where('userID', $userId)
        ->where('roleID', 1)
        ->exists();
});

benchmark("Query 2: isAdminStaff check", function() use ($userId) {
    DB::table('assign_user_role')
        ->where('userID', $userId)
        ->where('roleID', 48)
        ->exists();
});

benchmark("Query 3: tblper by UserID", function() use ($userId) {
    DB::table('tblper')->where('UserID', $userId)->first();
});

benchmark("Query 4: tblleave_type fetch", function() {
    DB::table('tblleave_type')->orderBy('id', 'DESC')->get();
});

benchmark("Query 5: tblper select (minimal) for admins", function() {
    DB::table('tblper')->select('ID', 'surname', 'first_name', 'othernames')->get();
});

benchmark("Query 6: leave_record list (all records - Admin mode)", function() {
    DB::table('leave_record')
        ->join('tblper', 'tblper.ID', '=', 'leave_record.staffId')
        ->join('tblleave_type', 'tblleave_type.id', '=', 'leave_record.leave_type_id')
        ->join('tbldepartment', 'tbldepartment.id', '=', 'tblper.departmentID')
        ->select(
            'leave_record.*',
            'tblper.surname',
            'tblper.first_name',
            'tblper.othernames',
            'tbldepartment.department',
            'tblleave_type.leaveType'
        )
        ->orderBy('leave_record.id', 'DESC')
        ->get();
});

$employee = DB::table('tblper')->where('UserID', $userId)->first();
if ($employee) {
    benchmark("Query 7: leave_record list (Staff mode - user ID {$employee->ID})", function() use ($employee) {
        DB::table('leave_record')
            ->join('tblper', 'tblper.ID', '=', 'leave_record.staffId')
            ->join('tblleave_type', 'tblleave_type.id', '=', 'leave_record.leave_type_id')
            ->join('tbldepartment', 'tbldepartment.id', '=', 'tblper.departmentID')
            ->select(
                'leave_record.*',
                'tblper.surname',
                'tblper.first_name',
                'tblper.othernames',
                'tbldepartment.department',
                'tblleave_type.leaveType'
            )
            ->where('leave_record.staffId', $employee->ID)
            ->orderBy('leave_record.id', 'DESC')
            ->get();
    });
}
