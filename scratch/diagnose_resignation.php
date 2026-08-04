<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

echo "--- RESIGNATION REQUESTS ---\n";
$reqs = DB::table('resignation_requests')->get();
foreach ($reqs as $r) {
    echo "ID: {$r->id}, StaffID: {$r->staff_id}, HOD Status: {$r->hod_status}, Admin Status: {$r->admin_status}, Status: {$r->status}\n";
}

echo "\n--- HOD DELEGATIONS ---\n";
$dels = DB::table('hod_delegations')->get();
foreach ($dels as $d) {
    echo "ID: {$d->id}, Delegate Staff: {$d->delegate_staff_id}, Dept: {$d->department_id}, Status: {$d->status}, Start: {$d->start_date}, End: {$d->end_date}\n";
}

echo "\n--- DEPARTMENTS AND STAFF ---\n";
$staff = DB::table('tblper')->where('is_hod', 1)->orWhereIn('ID', $reqs->pluck('staff_id'))->get();
foreach ($staff as $s) {
    echo "ID: {$s->ID}, Name: {$s->surname} {$s->first_name}, DeptID: {$s->departmentID}, Is HOD: {$s->is_hod}, UserID: {$s->UserID}\n";
}
