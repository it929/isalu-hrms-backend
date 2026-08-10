<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AiQueryApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * Handle Natural Language HR Data Queries
     */
    public function askQuestion(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                $ctx = [
                    'userId' => $request->header('X-User-Id') ?? 1,
                    'isSuperAdmin' => true,
                ];
            }

            $userQuery = trim((string)$request->input('query', ''));
            if (empty($userQuery)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Please enter a question or query for the AI Data Analyst.'
                ], 422);
            }

            // 1. Determine Query Intent & Target Table
            $queryLower = strtolower($userQuery);
            $aiResult = $this->processNaturalLanguageQuery($queryLower, $userQuery);

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'query'            => $userQuery,
                    'summary'          => $aiResult['summary'],
                    'columns'          => $aiResult['columns'],
                    'rows'             => $aiResult['rows'],
                    'metrics'          => $aiResult['metrics'],
                    'suggested_chart'  => $aiResult['suggested_chart'],
                    'generated_sql'    => $aiResult['sql'] ?? null,
                ]
            ]);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("AI Query Error: " . $e->getMessage());
            return response()->json([
                'status' => 'success',
                'data'   => $this->noReportFoundResponse($request->input('query', 'Query'))
            ]);
        }
    }

    /**
     * Natural Language Query Processor & Data Resolver
     */
    private function processNaturalLanguageQuery(string $queryLower, string $originalQuery): array
    {
        try {
            // Extract Filters
            $deptFilter = $this->extractDepartmentFilter($queryLower);
            $statusFilter = $this->extractStatusFilter($queryLower);

            // 1. Resignation / Exit Queries
            if (str_contains($queryLower, 'resign') || str_contains($queryLower, 'exit') || str_contains($queryLower, 'notice') || str_contains($queryLower, 'leaving')) {
                return $this->queryResignations($statusFilter, $deptFilter);
            }

            // 2. IOU / Salary Advance Queries
            if (str_contains($queryLower, 'iou') || str_contains($queryLower, 'advance')) {
                return $this->queryIouApplications($statusFilter, $deptFilter);
            }

            // 3. Refund Queries
            if (str_contains($queryLower, 'refund') || str_contains($queryLower, 'reimbur')) {
                return $this->queryRefundApplications($statusFilter, $deptFilter);
            }

            // 4. Loan Queries
            if (str_contains($queryLower, 'loan') || str_contains($queryLower, 'asset finance') || str_contains($queryLower, 'coop')) {
                return $this->queryLoansOverview($deptFilter);
            }

            // 5. Leave Queries
            if (str_contains($queryLower, 'leave') || str_contains($queryLower, 'vacation') || str_contains($queryLower, 'absence')) {
                return $this->queryLeaveApplications($statusFilter, $deptFilter, $queryLower);
            }

            // 6. Bank / Payment Details Queries
            if (str_contains($queryLower, 'bank') || str_contains($queryLower, 'account')) {
                return $this->queryBankDetails($deptFilter, $queryLower);
            }

            // 7. Salary / Payroll / Expenditure Queries
            if (str_contains($queryLower, 'salary') || str_contains($queryLower, 'payroll') || str_contains($queryLower, 'basic') || str_contains($queryLower, 'cost') || str_contains($queryLower, 'pay') || str_contains($queryLower, 'earning') || str_contains($queryLower, 'emolument')) {
                if (str_contains($queryLower, 'department') || str_contains($queryLower, 'dept') || !empty($deptFilter)) {
                    return $this->querySalaryByDepartment($deptFilter);
                }
                return $this->queryPayrollOverview($deptFilter);
            }

            // 8. Designations / Roles
            if (str_contains($queryLower, 'designation') || str_contains($queryLower, 'title') || str_contains($queryLower, 'job role')) {
                return $this->queryDesignations();
            }

            // 9. Units
            if (str_contains($queryLower, 'unit') && !str_contains($queryLower, 'community')) {
                return $this->queryUnits();
            }

            // 10. Departments List / Catalog
            if ((str_contains($queryLower, 'department') || str_contains($queryLower, 'dept')) && (str_contains($queryLower, 'list') || str_contains($queryLower, 'all') || str_contains($queryLower, 'catalog') || str_contains($queryLower, 'show'))) {
                if (!str_contains($queryLower, 'count') && !str_contains($queryLower, 'staff') && !str_contains($queryLower, 'employee') && !str_contains($queryLower, 'salary')) {
                    return $this->queryDepartmentCatalog();
                }
            }

            // 11. Staff / Employee Queries
            if (str_contains($queryLower, 'staff') || str_contains($queryLower, 'employee') || str_contains($queryLower, 'worker') || str_contains($queryLower, 'people') || str_contains($queryLower, 'personnel') || str_contains($queryLower, 'team') || str_contains($queryLower, 'nurse') || str_contains($queryLower, 'doctor') || !empty($deptFilter)) {
                if (str_contains($queryLower, 'count') || (str_contains($queryLower, 'department') && empty($deptFilter))) {
                    return $this->queryStaffByDepartment($deptFilter);
                }
                if (str_contains($queryLower, 'resumption') || str_contains($queryLower, 'hired') || str_contains($queryLower, 'joined') || str_contains($queryLower, 'date')) {
                    return $this->queryStaffResumptionDates($deptFilter);
                }
                return $this->queryStaffOverview($deptFilter, $statusFilter, $originalQuery);
            }

            // Fallback: General Staff Search matching query
            return $this->queryStaffOverview(null, null, $originalQuery);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("processNaturalLanguageQuery Exception: " . $e->getMessage());
            return $this->noReportFoundResponse($originalQuery);
        }
    }

    /**
     * Extract Department Name from query string
     */
    private function extractDepartmentFilter(string $queryLower): ?string
    {
        $stopwords = [
            'staff', 'employee', 'employees', 'personnel', 'worker', 'workers',
            'people', 'team', 'department', 'departments', 'dept', 'depts',
            'hospital', 'isalu', 'all', 'list', 'show', 'count', 'view', 'total',
            'amount', 'apply', 'applied', 'application', 'applications',
            'request', 'requests', 'record', 'records', 'how', 'many', 'iou',
            'loan', 'loans', 'leave', 'leaves', 'refund', 'refunds', 'salary'
        ];

        try {
            $depts = DB::table('tbldepartment')->pluck('department')->toArray();
        } catch (\Throwable $e) {
            $depts = [];
        }

        if (empty($depts)) {
            $depts = ['Nursing', 'Pharmacy', 'Accounts', 'Medical', 'Laboratory', 'Administration', 'Finance', 'HR', 'IT', 'Surgery', 'Security', 'Radiology', 'Maintenance', 'Porters'];
        }

        foreach ($depts as $d) {
            if (empty($d)) continue;
            $dLower = strtolower(trim($d));
            if (in_array($dLower, $stopwords)) {
                continue;
            }
            if (strlen($dLower) >= 3 && str_contains($queryLower, $dLower)) {
                return $d;
            }
        }

        // Common shorthand aliases
        $aliases = [
            'nursing'   => 'Nursing',
            'nurse'     => 'Nursing',
            'nurses'    => 'Nursing',
            'pharmacy'  => 'Pharmacy',
            'chemist'   => 'Pharmacy',
            'account'   => 'Accounts',
            'accounts'  => 'Accounts',
            'medical'   => 'Medical',
            'doctor'    => 'Medical',
            'doctors'   => 'Medical',
            'lab'       => 'Laboratory',
            'laboratory'=> 'Laboratory',
            'admin'     => 'Administration',
            'radiology' => 'Radiology',
            'security'  => 'Security',
            'surgery'   => 'Surgery',
        ];

        foreach ($aliases as $alias => $realName) {
            if (str_contains($queryLower, $alias)) {
                return $realName;
            }
        }

        return null;
    }

    /**
     * Extract Status Filter (pending, approved, rejected, active, inactive)
     */
    private function extractStatusFilter(string $queryLower): ?string
    {
        if (str_contains($queryLower, 'pending') || str_contains($queryLower, 'awaiting') || str_contains($queryLower, 'unapproved')) {
            return 'pending';
        }
        if (str_contains($queryLower, 'approved') || str_contains($queryLower, 'granted') || str_contains($queryLower, 'cleared')) {
            return 'approved';
        }
        if (str_contains($queryLower, 'rejected') || str_contains($queryLower, 'declined') || str_contains($queryLower, 'denied')) {
            return 'rejected';
        }
        if (str_contains($queryLower, 'active')) {
            return 'active';
        }
        if (str_contains($queryLower, 'inactive') || str_contains($queryLower, 'retired') || str_contains($queryLower, 'terminated')) {
            return 'inactive';
        }
        return null;
    }

    /**
     * Graceful Response for Unmatched or Invalid Queries
     */
    private function noReportFoundResponse(string $userQuery): array
    {
        return [
            'summary' => "No matching HR report or data found for: \"{$userQuery}\". Please try asking about staff, departments, salaries, resignations, bank details, or leave applications.",
            'columns' => [
                ['key' => 'status', 'label' => 'Query Result'],
                ['key' => 'suggestion', 'label' => 'Suggested Action']
            ],
            'rows' => [
                [
                    'status' => 'No Report Found',
                    'suggestion' => 'Please ask about staff, departments, salaries, resignations, bank details, or leave applications.'
                ]
            ],
            'metrics' => [
                ['label' => 'Query Status', 'value' => 'No Report Found'],
                ['label' => 'Available Datasets', 'value' => 'Staff, Payroll, Resignations, Leaves, Banks']
            ],
            'suggested_chart' => 'table',
            'sql' => null,
        ];
    }

    /**
     * Query: Staff Overview
     */
    private function queryStaffOverview(?string $deptFilter = null, ?string $statusFilter = null, ?string $userQuery = null): array
    {
        $q = DB::table('tblper as p')
            ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
            ->leftJoin('tbldesignation as des', 'des.id', '=', 'p.designationID')
            ->select(
                'p.ID as staff_id',
                'p.fileNo',
                DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as full_name"),
                DB::raw("COALESCE(d.department, 'Unassigned') as department"),
                'des.designation',
                'p.resumption_date',
                'p.email as email_address',
                'p.phone as mobile',
                DB::raw("CASE WHEN p.staff_status = 1 THEN 'Active' ELSE 'Inactive' END as status")
            );

        if (!empty($deptFilter)) {
            $q->where('d.department', 'like', "%{$deptFilter}%");
        }

        if ($statusFilter === 'active') {
            $q->where('p.staff_status', 1);
        } elseif ($statusFilter === 'inactive') {
            $q->where('p.staff_status', '!=', 1);
        }

        if (!empty($userQuery) && empty($deptFilter) && empty($statusFilter)) {
            $clean = preg_replace('/[^\w\s]/', '', $userQuery);
            $words = array_filter(explode(' ', strtolower($clean)), fn($w) => strlen($w) > 2 && !in_array($w, ['show', 'list', 'staff', 'count', 'what', 'view', 'with', 'from', 'have', 'more', 'about']));
            if (!empty($words)) {
                $q->where(function($sub) use ($words) {
                    foreach ($words as $w) {
                        $sub->orWhere('p.surname', 'like', "%{$w}%")
                            ->orWhere('p.first_name', 'like', "%{$w}%")
                            ->orWhere('p.fileNo', 'like', "%{$w}%")
                            ->orWhere('d.department', 'like', "%{$w}%");
                    }
                });
            }
        }

        $rows = $q->orderBy('p.ID', 'desc')->get();
        $totalStaff = count($rows);

        $deptClause = $deptFilter ? " in the '{$deptFilter}' department" : " across hospital departments";
        $statusClause = $statusFilter ? " (Status: " . ucfirst($statusFilter) . ")" : "";
        $summary = "Found {$totalStaff} registered staff records{$deptClause}{$statusClause}.";

        return [
            'summary' => $summary,
            'columns' => [
                ['key' => 'staff_id', 'label' => 'Staff ID'],
                ['key' => 'full_name', 'label' => 'Employee Name'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'designation', 'label' => 'Designation'],
                ['key' => 'mobile', 'label' => 'Contact Phone'],
                ['key' => 'status', 'label' => 'Status'],
            ],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'Matching Records', 'value' => $totalStaff],
                ['label' => 'Department Filter', 'value' => $deptFilter ?? 'All Departments'],
            ],
            'suggested_chart' => 'table',
            'sql' => 'SELECT p.ID, p.surname, p.first_name, d.department FROM tblper p LEFT JOIN tbldepartment d ON d.id = p.departmentID',
        ];
    }

    /**
     * Query: Staff Count by Department
     */
    private function queryStaffByDepartment(?string $deptFilter = null): array
    {
        $q = DB::table('tblper as p')
            ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
            ->select(
                DB::raw("COALESCE(d.department, 'Unassigned') as department_name"),
                DB::raw("COUNT(p.ID) as total_employees")
            );

        if (!empty($deptFilter)) {
            $q->where('d.department', 'like', "%{$deptFilter}%");
        }

        $rows = $q->groupBy('d.department')
            ->orderBy('total_employees', 'desc')
            ->get();

        $totalDepts = count($rows);
        $topDept = $rows->first() ? $rows->first()->department_name : 'N/A';
        $topCount = $rows->first() ? $rows->first()->total_employees : 0;
        $totalStaff = $rows->sum('total_employees');

        if ($deptFilter) {
            $summary = "Staff count for department matching '{$deptFilter}': Total of {$totalStaff} staff members.";
        } else {
            $summary = "Staff distribution across {$totalDepts} hospital departments. Total staff: {$totalStaff}. The largest department is '{$topDept}' with {$topCount} members.";
        }

        return [
            'summary' => $summary,
            'columns' => [
                ['key' => 'department_name', 'label' => 'Department'],
                ['key' => 'total_employees', 'label' => 'Number of Staff'],
            ],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'Total Staff', 'value' => $totalStaff],
                ['label' => 'Departments Listed', 'value' => $totalDepts],
            ],
            'suggested_chart' => 'bar',
            'sql' => 'SELECT d.department, COUNT(p.ID) FROM tblper p LEFT JOIN tbldepartment d ON d.id = p.departmentID GROUP BY d.department',
        ];
    }

    /**
     * Query: Salary Breakdown by Department
     */
    private function querySalaryByDepartment(?string $deptFilter = null): array
    {
        $q = DB::table('tblper as p')
            ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
            ->leftJoin('salary_structures as ss', 'ss.staffId', '=', 'p.ID')
            ->select(
                DB::raw("COALESCE(d.department, 'General Operations') as department_name"),
                DB::raw("COUNT(p.ID) as staff_count"),
                DB::raw("SUM(COALESCE(ss.basic_salary, 0)) as total_basic_salary"),
                DB::raw("AVG(COALESCE(ss.basic_salary, 0)) as avg_basic_salary")
            );

        if (!empty($deptFilter)) {
            $q->where('d.department', 'like', "%{$deptFilter}%");
        }

        $rows = $q->groupBy('d.department')
            ->orderBy('total_basic_salary', 'desc')
            ->get();

        $totalPayrollCost = $rows->sum('total_basic_salary');
        $formattedCost = '₦' . number_format($totalPayrollCost, 2);

        $deptText = $deptFilter ? "for '{$deptFilter}' department" : "across departments at ISALU HOSPITAL";
        $summary = "Total basic salary budget {$deptText} amounts to {$formattedCost} per month.";

        return [
            'summary' => $summary,
            'columns' => [
                ['key' => 'department_name', 'label' => 'Department'],
                ['key' => 'staff_count', 'label' => 'Staff Count'],
                ['key' => 'total_basic_salary', 'label' => 'Total Monthly Basic (₦)', 'format' => 'currency'],
                ['key' => 'avg_basic_salary', 'label' => 'Average Basic Salary (₦)', 'format' => 'currency'],
            ],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'Total Basic Salary Budget', 'value' => $formattedCost],
                ['label' => 'Departments Evaluated', 'value' => count($rows)],
            ],
            'suggested_chart' => 'pie',
            'sql' => 'SELECT d.department, COUNT(p.ID), SUM(ss.basic_salary) FROM tblper p LEFT JOIN tbldepartment d ON d.id = p.departmentID LEFT JOIN salary_structures ss ON ss.staffId = p.ID GROUP BY d.department',
        ];
    }

    /**
     * Query: Resignations Analysis
     */
    private function queryResignations(?string $statusFilter = null, ?string $deptFilter = null): array
    {
        $q = DB::table('resignation_requests as r')
            ->leftJoin('tblper as p', 'p.ID', '=', 'r.staff_id')
            ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
            ->select(
                'r.id as resignation_id',
                'p.ID as staff_id',
                'p.fileNo',
                DB::raw("CONCAT(p.surname, ' ', p.first_name) as staff_name"),
                'd.department',
                'r.resignation_date as notice_date',
                DB::raw("DATE_ADD(r.resignation_date, INTERVAL 30 DAY) as exit_date"),
                DB::raw("CASE WHEN r.admin_status = 1 THEN 'Approved' WHEN r.admin_status = 2 THEN 'Rejected' ELSE 'Pending' END as hr_status")
            );

        if (!empty($deptFilter)) {
            $q->where('d.department', 'like', "%{$deptFilter}%");
        }

        if ($statusFilter === 'pending') {
            $q->where('r.admin_status', 0);
        } elseif ($statusFilter === 'approved') {
            $q->where('r.admin_status', 1);
        } elseif ($statusFilter === 'rejected') {
            $q->where('r.admin_status', 2);
        }

        $rows = $q->orderBy('r.id', 'desc')->get();
        $totalResignations = count($rows);

        $statusText = $statusFilter ? " (" . ucfirst($statusFilter) . ")" : "";
        $summary = "Total of {$totalResignations} resignation requests recorded{$statusText}.";

        return [
            'summary' => $summary,
            'columns' => [
                ['key' => 'staff_id', 'label' => 'Staff ID'],
                ['key' => 'staff_name', 'label' => 'Staff Name'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'notice_date', 'label' => 'Notice Date'],
                ['key' => 'exit_date', 'label' => 'Calculated Exit Date'],
                ['key' => 'hr_status', 'label' => 'HR Approval Status'],
            ],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'Total Resignation Requests', 'value' => $totalResignations],
                ['label' => 'Filter Status', 'value' => $statusFilter ? ucfirst($statusFilter) : 'All Statuses'],
            ],
            'suggested_chart' => 'table',
            'sql' => 'SELECT r.id, p.surname, d.department, r.resignation_date FROM resignation_requests r LEFT JOIN tblper p ON p.ID = r.staff_id',
        ];
    }

    /**
     * Query: Payroll Overview
     */
    private function queryPayrollOverview(?string $deptFilter = null): array
    {
        $activeMonth = DB::table('active_months')->orderBy('id', 'desc')->first();

        $q = DB::table('salary_structures as ss')
            ->leftJoin('tblper as p', 'p.ID', '=', 'ss.staffId')
            ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
            ->select(
                'p.ID as staff_id',
                'p.fileNo',
                DB::raw("CONCAT(p.surname, ' ', p.first_name) as staff_name"),
                'd.department',
                'ss.basic_salary',
                'ss.housing_allowance',
                'ss.transport_allowance',
                'ss.declare_salary'
            );

        if (!empty($deptFilter)) {
            $q->where('d.department', 'like', "%{$deptFilter}%");
        }

        $rows = $q->limit(50)->get();

        $totalBasic = $rows->sum('basic_salary');
        $formattedTotal = '₦' . number_format($totalBasic, 2);
        $monthTitle = $activeMonth ? "{$activeMonth->month}/{$activeMonth->year}" : date('m/Y');

        return [
            'summary' => "Payroll active cycle ({$monthTitle}) basic salary structures with total basic payout of {$formattedTotal}.",
            'columns' => [
                ['key' => 'staff_id', 'label' => 'Staff ID'],
                ['key' => 'staff_name', 'label' => 'Staff Name'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'basic_salary', 'label' => 'Basic Salary (₦)', 'format' => 'currency'],
                ['key' => 'housing_allowance', 'label' => 'Housing (₦)', 'format' => 'currency'],
                ['key' => 'transport_allowance', 'label' => 'Transport (₦)', 'format' => 'currency'],
            ],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'Active Month Cycle', 'value' => $monthTitle],
                ['label' => 'Total Basic Payout', 'value' => $formattedTotal],
            ],
            'suggested_chart' => 'table',
            'sql' => 'SELECT ss.staffId, p.surname, ss.basic_salary FROM salary_structures ss LEFT JOIN tblper p ON p.ID = ss.staffId',
        ];
    }

    /**
     * Query: IOU / Salary Advances
     */
    private function queryIouApplications(?string $statusFilter = null, ?string $deptFilter = null): array
    {
        $q = DB::table('iou_records as i')
            ->leftJoin('tblper as p', 'p.ID', '=', 'i.staff_id')
            ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
            ->select(
                DB::raw("COALESCE(i.staff_id, p.ID, p.fileNo) as staff_id"),
                DB::raw("CONCAT(p.surname, ' ', p.first_name) as staff_name"),
                'd.department',
                'i.amount',
                'i.reason',
                'i.iou_date',
                'i.repayment_date',
                DB::raw("CASE WHEN i.status = 1 THEN 'Approved' WHEN i.status = 2 THEN 'Rejected' ELSE 'Pending' END as status_text")
            );

        if (!empty($deptFilter)) {
            $q->where('d.department', 'like', "%{$deptFilter}%");
        }

        if ($statusFilter === 'pending') {
            $q->where('i.status', 0);
        } elseif ($statusFilter === 'approved') {
            $q->where('i.status', 1);
        } elseif ($statusFilter === 'rejected') {
            $q->where('i.status', 2);
        }

        $rows = $q->orderBy('i.id', 'desc')->get();
        $totalIou = count($rows);
        $uniqueStaffCount = $rows->pluck('fileNo')->filter()->unique()->count();
        $totalAmount = $rows->sum('amount');
        $formattedAmount = '₦' . number_format($totalAmount, 2);

        $statusText = $statusFilter ? " (" . ucfirst($statusFilter) . ")" : "";
        $deptText = $deptFilter ? " in '{$deptFilter}' department" : "";
        $summary = "A total of {$uniqueStaffCount} staff member(s) have submitted {$totalIou} IOU / Salary Advance application(s){$deptText}{$statusText} with a combined value of {$formattedAmount}.";

        return [
            'summary' => $summary,
            'columns' => [
                ['key' => 'staff_id', 'label' => 'Staff ID'],
                ['key' => 'staff_name', 'label' => 'Staff Name'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'amount', 'label' => 'Amount (₦)', 'format' => 'currency'],
                ['key' => 'reason', 'label' => 'Reason / Purpose'],
                ['key' => 'iou_date', 'label' => 'Application Date'],
                ['key' => 'status_text', 'label' => 'Status'],
            ],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'Staff Who Applied', 'value' => $uniqueStaffCount],
                ['label' => 'Total IOU Applications', 'value' => $totalIou],
                ['label' => 'Total Amount Requested', 'value' => $formattedAmount],
            ],
            'suggested_chart' => 'table',
            'sql' => 'SELECT i.id, p.surname, i.amount, i.status FROM iou_records i LEFT JOIN tblper p ON p.ID = i.staff_id',
        ];
    }

    /**
     * Query: Refund Applications
     */
    private function queryRefundApplications(?string $statusFilter = null, ?string $deptFilter = null): array
    {
        $q = DB::table('refund_requests as r')
            ->leftJoin('tblper as p', 'p.ID', '=', 'r.staff_id')
            ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
            ->select(
                DB::raw("COALESCE(r.staff_id, p.ID, p.fileNo) as staff_id"),
                DB::raw("CONCAT(p.surname, ' ', p.first_name) as staff_name"),
                'd.department',
                'r.amount',
                'r.reason',
                'r.refund_date as request_date',
                DB::raw("CASE WHEN r.finance_status = 1 THEN 'Approved' WHEN r.finance_status = 2 THEN 'Rejected' ELSE 'Pending' END as status_text")
            );

        if (!empty($deptFilter)) {
            $q->where('d.department', 'like', "%{$deptFilter}%");
        }

        if ($statusFilter === 'pending') {
            $q->where('r.finance_status', 0);
        } elseif ($statusFilter === 'approved') {
            $q->where('r.finance_status', 1);
        } elseif ($statusFilter === 'rejected') {
            $q->where('r.finance_status', 2);
        }

        $rows = $q->orderBy('r.id', 'desc')->get();
        $totalRefunds = count($rows);
        $totalAmt = $rows->sum('amount');

        return [
            'summary' => "Total of {$totalRefunds} refund applications recorded with total amount of ₦" . number_format($totalAmt, 2) . ".",
            'columns' => [
                ['key' => 'staff_id', 'label' => 'Staff ID'],
                ['key' => 'staff_name', 'label' => 'Staff Name'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'amount', 'label' => 'Amount (₦)', 'format' => 'currency'],
                ['key' => 'reason', 'label' => 'Reason / Purpose'],
                ['key' => 'status_text', 'label' => 'Finance Status'],
            ],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'Total Refund Applications', 'value' => $totalRefunds],
                ['label' => 'Total Claim Amount', 'value' => '₦' . number_format($totalAmt, 2)],
            ],
            'suggested_chart' => 'table',
            'sql' => 'SELECT r.id, p.surname, r.amount FROM refund_requests r LEFT JOIN tblper p ON p.ID = r.staff_id',
        ];
    }

    /**
     * Query: Loans Overview
     */
    private function queryLoansOverview(?string $deptFilter = null): array
    {
        $q = DB::table('employee_loans as el')
            ->leftJoin('tblper as p', 'p.ID', '=', 'el.staffId')
            ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
            ->select(
                DB::raw("COALESCE(el.staffId, p.ID, p.fileNo) as staff_id"),
                DB::raw("CONCAT(p.surname, ' ', p.first_name) as staff_name"),
                'd.department',
                'el.loan_type',
                'el.loan_amount',
                'el.monthly_deduction',
                'el.balance',
                'el.status'
            );

        if (!empty($deptFilter)) {
            $q->where('d.department', 'like', "%{$deptFilter}%");
        }

        $rows = $q->orderBy('el.id', 'desc')->get();
        $totalLoans = count($rows);
        $totalVal = $rows->sum('loan_amount');

        return [
            'summary' => "Found {$totalLoans} employee loan accounts with total principal value of ₦" . number_format($totalVal, 2) . ".",
            'columns' => [
                ['key' => 'staff_id', 'label' => 'Staff ID'],
                ['key' => 'staff_name', 'label' => 'Staff Name'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'loan_type', 'label' => 'Loan Type'],
                ['key' => 'loan_amount', 'label' => 'Principal Amount (₦)', 'format' => 'currency'],
                ['key' => 'monthly_deduction', 'label' => 'Monthly Deduction (₦)', 'format' => 'currency'],
                ['key' => 'balance', 'label' => 'Outstanding Balance (₦)', 'format' => 'currency'],
                ['key' => 'status', 'label' => 'Status'],
            ],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'Active Loans', 'value' => $totalLoans],
                ['label' => 'Total Loan Portfolio', 'value' => '₦' . number_format($totalVal, 2)],
            ],
            'suggested_chart' => 'table',
            'sql' => 'SELECT el.id, p.surname, el.loan_amount FROM employee_loans el LEFT JOIN tblper p ON p.ID = el.staffId',
        ];
    }

    /**
     * Query: Leave Applications Overview
     */
    private function queryLeaveApplications(?string $statusFilter = null, ?string $deptFilter = null, ?string $queryLower = null): array
    {
        $specificType = null;

        if ($queryLower) {
            if (str_contains($queryLower, 'annual')) $specificType = 'Annual';
            elseif (str_contains($queryLower, 'casual')) $specificType = 'Casual';
            elseif (str_contains($queryLower, 'sick')) $specificType = 'Sick';
            elseif (str_contains($queryLower, 'maternity')) $specificType = 'Maternity';
            elseif (str_contains($queryLower, 'paternity')) $specificType = 'Paternity';
            elseif (str_contains($queryLower, 'study') || str_contains($queryLower, 'exam')) $specificType = 'Study';
        }

        $allRows = collect([]);

        // 1. Check leave_applications table
        if (Schema::hasTable('leave_applications')) {
            $q = DB::table('leave_applications as l')
                ->leftJoin('tblper as p', 'p.ID', '=', 'l.staff_id')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->select(
                    DB::raw("COALESCE(l.staff_id, p.ID, p.fileNo) as staff_id"),
                    DB::raw("CONCAT(p.surname, ' ', p.first_name) as staff_name"),
                    'd.department',
                    'l.leave_type',
                    'l.start_date',
                    'l.end_date',
                    'l.days_requested',
                    DB::raw("CASE WHEN l.status = 1 THEN 'Approved' WHEN l.status = 2 THEN 'Rejected' ELSE 'Pending' END as status_text")
                );

            if (!empty($deptFilter)) {
                $q->where('d.department', 'like', "%{$deptFilter}%");
            }

            if ($specificType) {
                $q->where('l.leave_type', 'like', "%{$specificType}%");
            }

            if ($statusFilter === 'pending') {
                $q->where('l.status', 0);
            } elseif ($statusFilter === 'approved') {
                $q->where('l.status', 1);
            } elseif ($statusFilter === 'rejected') {
                $q->where('l.status', 2);
            }

            $allRows = $allRows->concat($q->orderBy('l.id', 'desc')->get());
        }

        // 2. Check annual_leave table
        if (Schema::hasTable('annual_leave')) {
            $q = DB::table('annual_leave as l')
                ->leftJoin('tblper as p', 'p.ID', '=', 'l.staffid')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->select(
                    DB::raw("COALESCE(l.staffid, p.ID, p.fileNo) as staff_id"),
                    DB::raw("CONCAT(p.surname, ' ', p.first_name) as staff_name"),
                    'd.department',
                    DB::raw("COALESCE(l.leavetype, 'Annual Leave') as leave_type"),
                    'l.startdate as start_date',
                    'l.enddate as end_date',
                    'l.nod as days_requested',
                    DB::raw("CASE WHEN l.statusid = 2 THEN 'Approved' WHEN l.statusid = 3 THEN 'Rejected' ELSE 'Pending' END as status_text")
                );

            if (!empty($deptFilter)) {
                $q->where('d.department', 'like', "%{$deptFilter}%");
            }

            if ($specificType && $specificType !== 'Annual') {
                $q->where('l.leavetype', 'like', "%{$specificType}%");
            }

            $allRows = $allRows->concat($q->orderBy('l.id', 'desc')->get());
        }

        // 3. Check tourleave_record table
        if (Schema::hasTable('tourleave_record')) {
            $q = DB::table('tourleave_record as l')
                ->leftJoin('tblper as p', 'p.ID', '=', 'l.staffid')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->select(
                    DB::raw("COALESCE(l.staffid, p.ID, p.fileNo) as staff_id"),
                    DB::raw("CONCAT(p.surname, ' ', p.first_name) as staff_name"),
                    'd.department',
                    DB::raw("'Annual Leave' as leave_type"),
                    'l.leaveDepartDate as start_date',
                    'l.leaveReturnDate as end_date',
                    'l.leaveDays as days_requested',
                    DB::raw("'Approved' as status_text")
                );

            if (!empty($deptFilter)) {
                $q->where('d.department', 'like', "%{$deptFilter}%");
            }

            $allRows = $allRows->concat($q->orderBy('l.tourLeaveID', 'desc')->get());
        }

        $rows = $allRows;
        $totalLeave = count($rows);
        $uniqueStaff = $rows->pluck('staff_id')->filter()->unique()->count();

        $typeLabel = $specificType ? "{$specificType} Leave" : "leave";
        $summary = "Found {$totalLeave} {$typeLabel} application(s) from {$uniqueStaff} staff member(s) recorded in ISALU HRMS" . ($statusFilter ? " (" . ucfirst($statusFilter) . ")" : "") . ".";

        return [
            'summary' => $summary,
            'columns' => [
                ['key' => 'staff_id', 'label' => 'Staff ID'],
                ['key' => 'staff_name', 'label' => 'Staff Name'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'leave_type', 'label' => 'Type'],
                ['key' => 'start_date', 'label' => 'Start Date'],
                ['key' => 'end_date', 'label' => 'End Date'],
                ['key' => 'days_requested', 'label' => 'Days'],
                ['key' => 'status_text', 'label' => 'Status'],
            ],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'Staff Applicants', 'value' => $uniqueStaff],
                ['label' => 'Total Applications', 'value' => $totalLeave],
            ],
            'suggested_chart' => 'table',
            'sql' => 'SELECT l.id, p.surname, l.leave_type, l.status FROM leave_applications l LEFT JOIN tblper p ON p.ID = l.staff_id',
        ];
    }

    /**
     * Query: Bank Details
     */
    private function queryBankDetails(?string $deptFilter = null, ?string $queryLower = null): array
    {
        $q = DB::table('tblbank_details as b')
            ->leftJoin('tblper as p', 'p.ID', '=', 'b.staffId')
            ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
            ->select(
                'p.ID as staff_id',
                'p.fileNo',
                DB::raw("CONCAT(p.surname, ' ', p.first_name) as staff_name"),
                'd.department',
                'b.bank_name',
                'b.account_number',
                'b.account_name'
            );

        if (!empty($deptFilter)) {
            $q->where('d.department', 'like', "%{$deptFilter}%");
        }

        $rows = $q->orderBy('p.ID', 'desc')->get();
        $total = count($rows);

        $deptText = $deptFilter ? " in '{$deptFilter}' department" : "";
        $summary = "Registered employee bank account details{$deptText} for direct payroll disbursement at ISALU HOSPITAL.";

        return [
            'summary' => $summary,
            'columns' => [
                ['key' => 'staff_id', 'label' => 'Staff ID'],
                ['key' => 'staff_name', 'label' => 'Staff Name'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'bank_name', 'label' => 'Bank Name'],
                ['key' => 'account_number', 'label' => 'Account Number'],
            ],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'Configured Accounts', 'value' => $total],
            ],
            'suggested_chart' => 'table',
            'sql' => 'SELECT b.staffId, p.surname, b.bank_name, b.account_number FROM tblbank_details b LEFT JOIN tblper p ON p.ID = b.staffId',
        ];
    }

    /**
     * Query: Resumption Dates
     */
    private function queryStaffResumptionDates(?string $deptFilter = null): array
    {
        $q = DB::table('tblper as p')
            ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
            ->select(
                'p.ID as staff_id',
                'p.fileNo',
                DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as full_name"),
                'd.department',
                'p.resumption_date',
                'p.appointment_date'
            );

        if (!empty($deptFilter)) {
            $q->where('d.department', 'like', "%{$deptFilter}%");
        }

        $rows = $q->orderBy('p.resumption_date', 'desc')->get();
        $total = count($rows);

        $deptText = $deptFilter ? " in '{$deptFilter}' department" : "";
        $summary = "Staff appointment and resumption dates catalog{$deptText} for ISALU HOSPITAL personnel.";

        return [
            'summary' => $summary,
            'columns' => [
                ['key' => 'staff_id', 'label' => 'Staff ID'],
                ['key' => 'full_name', 'label' => 'Employee Name'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'resumption_date', 'label' => 'Resumption Date'],
                ['key' => 'appointment_date', 'label' => 'Appointment Date'],
            ],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'Catalog Records', 'value' => $total],
            ],
            'suggested_chart' => 'table',
            'sql' => 'SELECT p.ID, p.surname, p.resumption_date FROM tblper p ORDER BY p.resumption_date DESC',
        ];
    }

    /**
     * Query: Department Catalog
     */
    private function queryDepartmentCatalog(): array
    {
        $rows = DB::table('tbldepartment as d')
            ->leftJoin('tblper as p', 'p.departmentID', '=', 'd.id')
            ->select(
                'd.id as dept_id',
                'd.department as department_name',
                DB::raw("COUNT(p.ID) as total_staff")
            )
            ->groupBy('d.id', 'd.department')
            ->orderBy('d.department', 'asc')
            ->get();

        return [
            'summary' => "Catalog of all " . count($rows) . " configured departments at ISALU HOSPITAL.",
            'columns' => [
                ['key' => 'dept_id', 'label' => 'Dept ID'],
                ['key' => 'department_name', 'label' => 'Department Name'],
                ['key' => 'total_staff', 'label' => 'Registered Staff'],
            ],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'Total Departments', 'value' => count($rows)],
            ],
            'suggested_chart' => 'table',
            'sql' => 'SELECT id, department FROM tbldepartment',
        ];
    }

    /**
     * Query: Designations
     */
    private function queryDesignations(): array
    {
        $rows = DB::table('tbldesignation as des')
            ->leftJoin('tblper as p', 'p.designationID', '=', 'des.id')
            ->select(
                'des.id as des_id',
                'des.designation as designation_title',
                DB::raw("COUNT(p.ID) as staff_count")
            )
            ->groupBy('des.id', 'des.designation')
            ->orderBy('des.designation', 'asc')
            ->get();

        return [
            'summary' => "Catalog of " . count($rows) . " employee designations and position titles at ISALU HOSPITAL.",
            'columns' => [
                ['key' => 'des_id', 'label' => 'ID'],
                ['key' => 'designation_title', 'label' => 'Designation Title'],
                ['key' => 'staff_count', 'label' => 'Staff Assigned'],
            ],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'Total Designations', 'value' => count($rows)],
            ],
            'suggested_chart' => 'table',
            'sql' => 'SELECT id, designation FROM tbldesignation',
        ];
    }

    /**
     * Query: Units
     */
    private function queryUnits(): array
    {
        $rows = DB::table('tblunit as u')
            ->leftJoin('tbldepartment as d', 'd.id', '=', 'u.department_id')
            ->leftJoin('tblper as p', 'p.unit_id', '=', 'u.id')
            ->select(
                'u.id as unit_id',
                'u.unit_name',
                DB::raw("COALESCE(d.department, 'Unassigned') as department"),
                DB::raw("COUNT(p.ID) as staff_count")
            )
            ->groupBy('u.id', 'u.unit_name', 'd.department')
            ->orderBy('u.unit_name', 'asc')
            ->get();

        return [
            'summary' => "Catalog of " . count($rows) . " hospital units across departments.",
            'columns' => [
                ['key' => 'unit_id', 'label' => 'Unit ID'],
                ['key' => 'unit_name', 'label' => 'Unit Name'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'staff_count', 'label' => 'Staff Assigned'],
            ],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'Total Units', 'value' => count($rows)],
            ],
            'suggested_chart' => 'table',
            'sql' => 'SELECT id, unit_name FROM tblunit',
        ];
    }
}
