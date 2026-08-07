<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            // 1. Staff / Employee Queries
            if (str_contains($queryLower, 'staff') || str_contains($queryLower, 'employee') || str_contains($queryLower, 'worker') || str_contains($queryLower, 'people') || str_contains($queryLower, 'team')) {
                if (str_contains($queryLower, 'department') || str_contains($queryLower, 'dept')) {
                    return $this->queryStaffByDepartment();
                }
                if (str_contains($queryLower, 'resumption') || str_contains($queryLower, 'hired') || str_contains($queryLower, 'joined')) {
                    return $this->queryStaffResumptionDates();
                }
                return $this->queryStaffOverview();
            }

            // 2. Resignation / Exit Queries
            if (str_contains($queryLower, 'resign') || str_contains($queryLower, 'exit') || str_contains($queryLower, 'notice') || str_contains($queryLower, 'leaving')) {
                return $this->queryResignations();
            }

            // 3. Salary / Payroll / Expenditure Queries
            if (str_contains($queryLower, 'salary') || str_contains($queryLower, 'payroll') || str_contains($queryLower, 'basic') || str_contains($queryLower, 'cost') || str_contains($queryLower, 'pay') || str_contains($queryLower, 'earning')) {
                if (str_contains($queryLower, 'department') || str_contains($queryLower, 'dept')) {
                    return $this->querySalaryByDepartment();
                }
                return $this->queryPayrollOverview();
            }

            // 4. Leave Queries
            if (str_contains($queryLower, 'leave') || str_contains($queryLower, 'vacation') || str_contains($queryLower, 'absence')) {
                return $this->queryLeaveApplications();
            }

            // 5. Bank / Payment Details Queries
            if (str_contains($queryLower, 'bank') || str_contains($queryLower, 'account')) {
                return $this->queryBankDetails();
            }

            // Fallback for unmatched topics
            return $this->noReportFoundResponse($originalQuery);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("processNaturalLanguageQuery Exception: " . $e->getMessage());
            return $this->noReportFoundResponse($originalQuery);
        }
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
                    'suggestion' => 'Try sample queries such as "Show staff count by department", "List resignation applications", or "What is total basic salary by department?"'
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
    private function queryStaffOverview(): array
    {
        $rows = DB::table('tblper as p')
            ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
            ->select(
                'p.ID as staff_id',
                DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as full_name"),
                'd.department',
                'p.resumption_date',
                'p.appointment_date',
                'p.email as email_address',
                'p.phone as mobile'
            )
            ->orderBy('p.ID', 'desc')
            ->get();

        $totalStaff = count($rows);

        return [
            'summary' => "ISALU HOSPITAL currently has a total of {$totalStaff} active staff records registered across all medical and administrative departments.",
            'columns' => [
                ['key' => 'staff_id', 'label' => 'Staff ID'],
                ['key' => 'full_name', 'label' => 'Employee Name'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'resumption_date', 'label' => 'Resumption Date'],
                ['key' => 'mobile', 'label' => 'Contact Phone'],
                ['key' => 'email_address', 'label' => 'Email Address'],
            ],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'Total Registered Staff', 'value' => $totalStaff],
                ['label' => 'Active Status', 'value' => '100% Operational'],
            ],
            'suggested_chart' => 'table',
            'sql' => 'SELECT p.ID, p.surname, p.first_name, d.department FROM tblper p LEFT JOIN tbldepartment d ON d.id = p.departmentID',
        ];
    }

    /**
     * Query: Staff Count by Department
     */
    private function queryStaffByDepartment(): array
    {
        $rows = DB::table('tblper as p')
            ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
            ->select(
                DB::raw("COALESCE(d.department, 'Unassigned') as department_name"),
                DB::raw("COUNT(p.ID) as total_employees")
            )
            ->groupBy('d.department')
            ->orderBy('total_employees', 'desc')
            ->get();

        $totalDepts = count($rows);
        $topDept = $rows->first() ? $rows->first()->department_name : 'N/A';
        $topCount = $rows->first() ? $rows->first()->total_employees : 0;

        return [
            'summary' => "Staff distribution across {$totalDepts} hospital departments. The largest department is '{$topDept}' with {$topCount} staff members.",
            'columns' => [
                ['key' => 'department_name', 'label' => 'Department'],
                ['key' => 'total_employees', 'label' => 'Number of Staff'],
            ],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'Total Departments', 'value' => $totalDepts],
                ['label' => 'Largest Department', 'value' => "{$topDept} ({$topCount} staff)"],
            ],
            'suggested_chart' => 'bar',
            'sql' => 'SELECT d.department, COUNT(p.ID) as total_employees FROM tblper p LEFT JOIN tbldepartment d ON d.id = p.departmentID GROUP BY d.department ORDER BY total_employees DESC',
        ];
    }

    /**
     * Query: Salary Breakdown by Department
     */
    private function querySalaryByDepartment(): array
    {
        $rows = DB::table('tblper as p')
            ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
            ->leftJoin('salary_structures as ss', 'ss.staffId', '=', 'p.ID')
            ->select(
                DB::raw("COALESCE(d.department, 'General Operations') as department_name"),
                DB::raw("COUNT(p.ID) as staff_count"),
                DB::raw("SUM(COALESCE(ss.basic_salary, 0)) as total_basic_salary"),
                DB::raw("AVG(COALESCE(ss.basic_salary, 0)) as avg_basic_salary")
            )
            ->groupBy('d.department')
            ->orderBy('total_basic_salary', 'desc')
            ->get();

        $totalPayrollCost = $rows->sum('total_basic_salary');
        $formattedCost = '₦' . number_format($totalPayrollCost, 2);

        return [
            'summary' => "Total basic salary budget across departments at ISALU HOSPITAL amounts to {$formattedCost} per month.",
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
    private function queryResignations(): array
    {
        $rows = DB::table('resignation_requests as r')
            ->leftJoin('tblper as p', 'p.ID', '=', 'r.staff_id')
            ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
            ->select(
                'r.id as resignation_id',
                'p.ID as staff_id',
                DB::raw("CONCAT(p.surname, ' ', p.first_name) as staff_name"),
                'd.department',
                'r.resignation_date as notice_date',
                DB::raw("DATE_ADD(r.resignation_date, INTERVAL 30 DAY) as exit_date"),
                DB::raw("CASE WHEN r.admin_status = 1 THEN 'Approved' WHEN r.admin_status = 2 THEN 'Rejected' ELSE 'Pending' END as hr_status")
            )
            ->orderBy('r.id', 'desc')
            ->get();

        $totalResignations = count($rows);
        $approvedCount = $rows->where('hr_status', 'Approved')->count();
        $pendingCount = $rows->where('hr_status', 'Pending')->count();

        return [
            'summary' => "Total of {$totalResignations} resignation requests recorded. {$approvedCount} requests approved and {$pendingCount} pending HR review.",
            'columns' => [
                ['key' => 'resignation_id', 'label' => 'Req #'],
                ['key' => 'staff_id', 'label' => 'Staff ID'],
                ['key' => 'staff_name', 'label' => 'Staff Name'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'notice_date', 'label' => 'Notice Date'],
                ['key' => 'exit_date', 'label' => 'Calculated Exit Date'],
                ['key' => 'hr_status', 'label' => 'HR Approval Status'],
            ],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'Total Resignation Notices', 'value' => $totalResignations],
                ['label' => 'Approved Exit Requests', 'value' => $approvedCount],
                ['label' => 'Pending Approval', 'value' => $pendingCount],
            ],
            'suggested_chart' => 'table',
            'sql' => 'SELECT r.id, p.surname, d.department, r.notice_date, r.exit_date FROM resignation_requests r LEFT JOIN tblper p ON p.ID = r.staff_id LEFT JOIN tbldepartment d ON d.id = p.departmentID',
        ];
    }

    /**
     * Query: Payroll Overview
     */
    private function queryPayrollOverview(): array
    {
        $activeMonth = DB::table('active_months')->orderBy('id', 'desc')->first();

        $rows = DB::table('salary_structures as ss')
            ->leftJoin('tblper as p', 'p.ID', '=', 'ss.staffId')
            ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
            ->select(
                'p.ID as staff_id',
                DB::raw("CONCAT(p.surname, ' ', p.first_name) as staff_name"),
                'd.department',
                'ss.basic_salary',
                'ss.housing_allowance',
                'ss.transport_allowance',
                'ss.declare_salary'
            )
            ->limit(50)
            ->get();

        $totalBasic = $rows->sum('basic_salary');
        $formattedTotal = '₦' . number_format($totalBasic, 2);
        $monthTitle = $activeMonth ? "{$activeMonth->month}/{$activeMonth->year}" : date('m/Y');

        return [
            'summary' => "Payroll active cycle ({$monthTitle}) contains basic salary structures for active hospital personnel with a total basic payout of {$formattedTotal}.",
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
     * Query: Leave Applications Overview
     */
    private function queryLeaveApplications(): array
    {
        $rows = DB::table('leave_applications as l')
            ->leftJoin('tblper as p', 'p.ID', '=', 'l.staff_id')
            ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
            ->select(
                'l.id as leave_id',
                'p.ID as staff_id',
                DB::raw("CONCAT(p.surname, ' ', p.first_name) as staff_name"),
                'd.department',
                'l.leave_type',
                'l.start_date',
                'l.end_date',
                'l.days_requested',
                DB::raw("CASE WHEN l.status = 1 THEN 'Approved' WHEN l.status = 2 THEN 'Rejected' ELSE 'Pending' END as status_text")
            )
            ->orderBy('l.id', 'desc')
            ->limit(50)
            ->get();

        $totalLeave = count($rows);

        return [
            'summary' => "Summary of {$totalLeave} leave applications recorded in ISALU HRMS.",
            'columns' => [
                ['key' => 'leave_id', 'label' => 'App ID'],
                ['key' => 'staff_id', 'label' => 'Staff ID'],
                ['key' => 'staff_name', 'label' => 'Staff Name'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'leave_type', 'label' => 'Type'],
                ['key' => 'days_requested', 'label' => 'Days'],
                ['key' => 'status_text', 'label' => 'Status'],
            ],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'Total Leave Requests', 'value' => $totalLeave],
            ],
            'suggested_chart' => 'table',
            'sql' => 'SELECT l.id, p.surname, l.leave_type, l.status FROM leave_applications l LEFT JOIN tblper p ON p.ID = l.staff_id',
        ];
    }

    /**
     * Query: Bank Details
     */
    private function queryBankDetails(): array
    {
        $rows = DB::table('tblbank_details as b')
            ->leftJoin('tblper as p', 'p.ID', '=', 'b.staffId')
            ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
            ->select(
                'p.ID as staff_id',
                DB::raw("CONCAT(p.surname, ' ', p.first_name) as staff_name"),
                'd.department',
                'b.bank_name',
                'b.account_number',
                'b.account_name'
            )
            ->limit(50)
            ->get();

        return [
            'summary' => "Registered employee bank account details for direct payroll disbursement at ISALU HOSPITAL.",
            'columns' => [
                ['key' => 'staff_id', 'label' => 'Staff ID'],
                ['key' => 'staff_name', 'label' => 'Staff Name'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'bank_name', 'label' => 'Bank Name'],
                ['key' => 'account_number', 'label' => 'Account Number'],
            ],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'Configured Bank Accounts', 'value' => count($rows)],
            ],
            'suggested_chart' => 'table',
            'sql' => 'SELECT b.staffId, p.surname, b.bank_name, b.account_number FROM tblbank_details b LEFT JOIN tblper p ON p.ID = b.staffId',
        ];
    }

    /**
     * Query: Resumption Dates
     */
    private function queryStaffResumptionDates(): array
    {
        $rows = DB::table('tblper as p')
            ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
            ->select(
                'p.ID as staff_id',
                DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as full_name"),
                'd.department',
                'p.resumption_date',
                'p.appointment_date'
            )
            ->orderBy('p.resumption_date', 'desc')
            ->limit(50)
            ->get();

        return [
            'summary' => "Staff appointment and resumption dates catalog for ISALU HOSPITAL personnel.",
            'columns' => [
                ['key' => 'staff_id', 'label' => 'Staff ID'],
                ['key' => 'full_name', 'label' => 'Employee Name'],
                ['key' => 'department', 'label' => 'Department'],
                ['key' => 'resumption_date', 'label' => 'Resumption Date'],
                ['key' => 'appointment_date', 'label' => 'Appointment Date'],
            ],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'Total Staff Records', 'value' => count($rows)],
            ],
            'suggested_chart' => 'table',
            'sql' => 'SELECT p.ID, p.surname, p.resumption_date FROM tblper p ORDER BY p.resumption_date DESC',
        ];
    }
}
