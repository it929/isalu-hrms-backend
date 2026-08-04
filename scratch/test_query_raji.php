<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

// Simulate context for HOD RAJI AFEEZ (UserID 10117)
$userId = 10117;
$request = new \Illuminate\Http\Request();
$request->headers->set('X-User-Id', $userId);

$controller = new \App\Http\Controllers\Api\ResignationApiController();
$ref = new \ReflectionMethod($controller, 'getUserContext');
$ref->setAccessible(true);
$ctx = $ref->invoke($controller, $request);

echo "--- Context for 10117 ---\n";
print_r($ctx);

// Run the index query logic
$query = DB::table('resignation_requests as rr')
    ->join('tblper as p', 'p.ID', '=', 'rr.staff_id')
    ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
    ->select('rr.*', 'p.surname', 'p.first_name', 'p.departmentID');

$employee = $ctx['employee'];
if ($ctx['isSuperAdmin'] || $ctx['isFinanceStaff'] || $ctx['isAuditStaff']) {
    // all
} else {
    $query->where(function ($q) use ($ctx, $employee) {
        $hasCondition = false;
        if ($employee) {
            $q->where('rr.staff_id', $employee->ID);
            $hasCondition = true;
        }
        if ($ctx['isAdminStaff']) {
            if ($hasCondition) {
                $q->orWhere('rr.hod_status', 1)->orWhere('rr.admin_status', '!=', 0);
            } else {
                $q->where(function($sub) {
                    $sub->where('rr.hod_status', 1)->orWhere('rr.admin_status', '!=', 0);
                });
            }
            $hasCondition = true;
        }
        if ($employee && $ctx['isHod']) {
            $hodDeptId = ($ctx['isDelegatedHod'] ?? false) ? $ctx['delegated_department_id'] : $employee->departmentID;
            if ($hasCondition) {
                $q->orWhere('p.departmentID', $hodDeptId);
            } else {
                $q->where('p.departmentID', $hodDeptId);
            }
            $hasCondition = true;
        }
        if (!$hasCondition) {
            $q->where('rr.id', 0);
        }
    });
}

$results = $query->get();
echo "\n--- Query Results for 10117 ---\n";
foreach ($results as $res) {
    echo "ID: {$res->id}, Staff Name: {$res->surname} {$res->first_name}, DeptID: {$res->departmentID}, Status: {$res->status}, HOD Status: {$res->hod_status}\n";
}
