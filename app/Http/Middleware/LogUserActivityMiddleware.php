<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LogUserActivityMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $method = strtoupper($request->method());
        $path = $request->path();

        // Skip static asset fetches and reporting queries to avoid recursion
        if (
            str_contains($path, 'login') ||
            str_contains($path, 'logout') ||
            str_contains($path, 'uploads/') ||
            str_contains($path, 'user-activities') ||
            str_contains($path, 'sidebar-links')
        ) {
            return $response;
        }

        $isModification = in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE']);
        $isExport = ($method === 'GET' && (str_contains($path, 'export') || str_contains($path, 'download')));
        
        // Only specific GET endpoints that perform explicit approve or reject actions
        $isApprovalOrAction = ($method === 'GET' && (
            str_contains($path, '-approve') ||
            str_contains($path, '-reject') ||
            str_contains($path, '/approve-') ||
            str_contains($path, '/reject-')
        ));

        // Skip any general GET read/fetch/view endpoints
        if ($method === 'GET' && !$isExport && !$isApprovalOrAction) {
            return $response;
        }

        // Log if successful action/modification
        if (($isModification || $isExport || $isApprovalOrAction) && $response->getStatusCode() < 400) {
            $this->logActivity($request, $method, $path);
        }

        return $response;
    }

    /**
     * Extract user info and record log entry.
     */
    protected function logActivity(Request $request, string $method, string $path): void
    {
        try {
            $userId = $request->header('X-User-Id') ?: ($request->user() ? $request->user()->id : null);
            if (!$userId && $request->has('user_id')) {
                $userId = $request->input('user_id');
            }
            if (!$userId && $request->has('UserID')) {
                $userId = $request->input('UserID');
            }
            if (!$userId && ($request->has('staff_id') || $request->has('staffId'))) {
                $targetId = $request->input('staff_id') ?: $request->input('staffId');
                $st = DB::table('tblper')->where('ID', $targetId)->first();
                $userId = $st ? ($st->UserID ?: $st->ID) : null;
            }

            // Resolve User & Staff Info
            $user = $userId ? DB::table('users')->where('id', $userId)->first() : null;
            $staff = $userId ? DB::table('tblper')->where('UserID', $userId)->orWhere('ID', $userId)->first() : null;
            $staffId = $staff ? $staff->ID : null;

            $userName = 'System Administrator';
            if ($staff) {
                $staffName = trim("{$staff->surname} {$staff->first_name} {$staff->othernames}");
                if (!empty($staffName)) {
                    $userName = $staffName;
                }
            } elseif ($user) {
                $userName = $user->name ?: ($user->username ?: 'User #' . $userId);
            }

            // Resolve Role
            $roleName = 'Staff';
            if ($userId) {
                $role = DB::table('assign_user_role')
                    ->join('user_role', 'user_role.roleID', '=', 'assign_user_role.roleID')
                    ->where('assign_user_role.userID', $userId)
                    ->first();
                if ($role) {
                    $roleName = $role->rolename;
                }
            }

            // Determine Activity Type and Human-Readable Action
            $activityType = $this->determineActivityType($method, $path);
            $module = $this->determineModule($path);
            $action = $this->generateActionDescription($method, $path, $request, $userName);

            $ip = $request->ip() ?: $request->getClientIp() ?: '127.0.0.1';
            $userAgent = $request->userAgent();

            // Sanitize payload (filter out sensitive passwords/tokens)
            $payload = $request->except(['password', 'password_confirmation', 'token', '_token']);
            $details = !empty($payload) ? json_encode(array_slice($payload, 0, 15)) : null;

            DB::table('user_activity_logs')->insert([
                'user_id' => $userId,
                'staff_id' => $staffId,
                'user_name' => $userName,
                'role_name' => $roleName,
                'activity_type' => $activityType,
                'action' => $action,
                'module' => $module,
                'method' => $method,
                'url' => $request->fullUrl(),
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'details' => $details,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Keep legacy audit_log updated for backwards compatibility
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('audit_log')) {
                    DB::table('audit_log')->insert([
                        'comp_name' => 'ISALU HOSPITAL',
                        'user_id' => substr((string)($userId ?: '1'), 0, 3),
                        'date' => now(),
                        'ip_addr' => substr($ip, 0, 20),
                        'operation' => $action,
                        'host' => gethostname() ?: 'localhost',
                        'referer' => $ip,
                        'action_title' => substr($action, 0, 255),
                    ]);
                }
            } catch (\Throwable $e) { /* ignore legacy */ }
        } catch (\Throwable $th) {
            Log::error('LogUserActivityMiddleware error: ' . $th->getMessage());
        }
    }

    protected function determineActivityType(string $method, string $path): string
    {
        if (str_contains($path, 'export') || str_contains($path, 'download')) return 'export';
        if (str_contains($path, 'approve') || str_contains($path, 'reject') || str_contains($path, 'recommend')) return 'approval';
        if (str_contains($path, 'delete') || str_contains($path, 'remove') || $method === 'DELETE') return 'delete';
        if (
            str_contains($path, 'update') || 
            str_contains($path, 'toggle') || 
            str_contains($path, 'lock') || 
            str_contains($path, 'unlock') || 
            str_contains($path, 'revert') || 
            str_contains($path, 'edit') || 
            str_contains($path, 'limit-config') || 
            str_contains($path, 'setup') ||
            str_contains($path, 'increment') ||
            $method === 'PUT' || 
            $method === 'PATCH'
        ) return 'update';
        if ($method === 'POST') return 'create';
        return 'general';
    }

    protected function determineModule(string $path): string
    {
        if (str_contains($path, 'payroll') || str_contains($path, 'salary') || str_contains($path, 'deduction') || str_contains($path, 'iou') || str_contains($path, 'loan') || str_contains($path, 'bonus') || str_contains($path, 'payslip') || str_contains($path, 'pension') || str_contains($path, 'retention')) return 'Payroll';
        if (str_contains($path, 'hr') || str_contains($path, 'staff') || str_contains($path, 'employee') || str_contains($path, 'leave') || str_contains($path, 'loa') || str_contains($path, 'documentation') || str_contains($path, 'letters')) return 'HR';
        if (str_contains($path, 'role') || str_contains($path, 'module') || str_contains($path, 'permission') || str_contains($path, 'assign') || str_contains($path, 'hod') || str_contains($path, 'delegation')) return 'Roles & Security';
        if (str_contains($path, 'report')) return 'Reports';
        if (str_contains($path, 'ai')) return 'AI Assistant';
        if (str_contains($path, 'procure')) return 'Procurement';
        return 'System';
    }

    /**
     * Helper to resolve staff name and optional amount from an application record.
     */
    protected function getApplicationStaffDetails(string $type, $recordId): string
    {
        try {
            if ($type === 'iou') {
                $rec = DB::table('iou_records')->where('id', $recordId)->first();
                if ($rec) {
                    $st = DB::table('tblper')->where('ID', $rec->staff_id)->first();
                    $name = $st ? trim("{$st->surname} {$st->first_name}") : 'Staff #' . $rec->staff_id;
                    $amt = $rec->amount > 0 ? ' (₦' . number_format((float)$rec->amount, 2) . ')' : '';
                    return " for {$name}{$amt}";
                }
            } elseif ($type === 'leave') {
                $rec = DB::table('leave_record')->where('id', $recordId)->first();
                if ($rec) {
                    $st = DB::table('tblper')->where('ID', $rec->staffId)->first();
                    $name = $st ? trim("{$st->surname} {$st->first_name}") : 'Staff #' . $rec->staffId;
                    return " for {$name}";
                }
            } elseif ($type === 'loa') {
                $rec = DB::table('leave_of_absent')->where('id', $recordId)->first();
                if ($rec) {
                    $st = DB::table('tblper')->where('ID', $rec->staffId)->first();
                    $name = $st ? trim("{$st->surname} {$st->first_name}") : 'Staff #' . $rec->staffId;
                    return " for {$name}";
                }
            } elseif ($type === 'coop_loan') {
                $rec = DB::table('coop_loans')->where('id', $recordId)->first();
                if ($rec) {
                    $st = DB::table('tblper')->where('ID', $rec->staffId)->first();
                    $name = $st ? trim("{$st->surname} {$st->first_name}") : 'Staff #' . $rec->staffId;
                    $amt = isset($rec->amount) && $rec->amount > 0 ? ' (₦' . number_format((float)$rec->amount, 2) . ')' : '';
                    return " for {$name}{$amt}";
                }
            } elseif ($type === 'employee_loan') {
                $rec = DB::table('employee_loans')->where('id', $recordId)->first();
                if ($rec) {
                    $st = DB::table('tblper')->where('ID', $rec->staffId)->first();
                    $name = $st ? trim("{$st->surname} {$st->first_name}") : 'Staff #' . $rec->staffId;
                    $amt = isset($rec->loan_amount) && $rec->loan_amount > 0 ? ' (₦' . number_format((float)$rec->loan_amount, 2) . ')' : '';
                    return " for {$name}{$amt}";
                }
            } elseif ($type === 'refund') {
                $rec = DB::table('refund_requests')->where('id', $recordId)->first();
                if ($rec) {
                    $st = DB::table('tblper')->where('ID', $rec->staff_id)->first();
                    $name = $st ? trim("{$st->surname} {$st->first_name}") : 'Staff #' . $rec->staff_id;
                    $amt = isset($rec->amount) && $rec->amount > 0 ? ' (₦' . number_format((float)$rec->amount, 2) . ')' : '';
                    return " for {$name}{$amt}";
                }
            } elseif ($type === 'resignation') {
                $rec = DB::table('resignation_requests')->where('id', $recordId)->first();
                if ($rec) {
                    $st = DB::table('tblper')->where('ID', $rec->staff_id)->first();
                    $name = $st ? trim("{$st->surname} {$st->first_name}") : 'Staff #' . $rec->staff_id;
                    return " for {$name}";
                }
            }
        } catch (\Throwable $e) {
            // Ignore lookup error
        }
        return '';
    }

    protected function getStaffNameById($staffId): string
    {
        if (!$staffId) return '';
        try {
            $st = DB::table('tblper')->where('ID', $staffId)->first();
            if ($st) {
                return " for " . trim("{$st->surname} {$st->first_name}");
            }
        } catch (\Throwable $e) {
            // Ignore lookup error
        }
        return '';
    }

    protected function generateActionDescription(string $method, string $path, Request $request, string $userName): string
    {
        $cleanPath = trim(str_replace(['api/nextjs/', 'api/'], '', $path), '/');
        $id = basename($cleanPath);

        // ── 1. IOUs / Salary Advances ──
        if (str_contains($cleanPath, 'payroll/ious/limit-config')) {
            $staffInfo = $this->getStaffNameById($request->input('staff_id'));
            $limitAmount = $request->has('max_iou_amount') && (float)$request->input('max_iou_amount') > 0
                ? ' (Max Limit: ₦' . number_format((float)$request->input('max_iou_amount'), 2) . ')' 
                : '';
            return "Updated Staff IOU Eligibility & Limit{$staffInfo}{$limitAmount}";
        }
        if (str_contains($cleanPath, 'payroll/ious/hod-approve')) return "HOD Recommended IOU Application" . $this->getApplicationStaffDetails('iou', $id);
        if (str_contains($cleanPath, 'payroll/ious/hod-reject')) return "HOD Rejected IOU Application" . $this->getApplicationStaffDetails('iou', $id);
        if (str_contains($cleanPath, 'payroll/ious/hr-approve')) return "HR Recommended IOU Application" . $this->getApplicationStaffDetails('iou', $id);
        if (str_contains($cleanPath, 'payroll/ious/hr-reject')) return "HR Rejected IOU Application" . $this->getApplicationStaffDetails('iou', $id);
        if (str_contains($cleanPath, 'payroll/ious/audit-approve')) return "Audit Recommended IOU Application" . $this->getApplicationStaffDetails('iou', $id);
        if (str_contains($cleanPath, 'payroll/ious/audit-reject')) return "Audit Rejected IOU Application" . $this->getApplicationStaffDetails('iou', $id);
        if (str_contains($cleanPath, 'payroll/ious/finance-approve')) return "Finance Approved & Paid IOU Application" . $this->getApplicationStaffDetails('iou', $id);
        if (str_contains($cleanPath, 'payroll/ious/finance-reject')) return "Finance Rejected IOU Application" . $this->getApplicationStaffDetails('iou', $id);
        if (str_contains($cleanPath, 'payroll/ious') && $method === 'DELETE') return "Deleted IOU Application" . $this->getApplicationStaffDetails('iou', $id);
        if ($cleanPath === 'payroll/ious' || $cleanPath === 'payroll/ious/apply') {
            if ($method === 'POST' && $request->has('id')) {
                return "Updated IOU Application (#" . $request->input('id') . ")" . $this->getStaffNameById($request->input('staff_id'));
            }
            $targetStaff = $this->getStaffNameById($request->input('staff_id'));
            $amount = $request->input('amount') ? ' (₦' . number_format((float)$request->input('amount'), 2) . ')' : '';
            return "Applied for IOU / Salary Advance{$targetStaff}{$amount}";
        }

        // ── 2. Leave & Leave of Absence (LOA) ──
        if (str_contains($cleanPath, 'hr/apply-leave/hod-approve')) return "HOD Recommended Leave Application" . $this->getApplicationStaffDetails('leave', $id);
        if (str_contains($cleanPath, 'hr/apply-leave/hod-reject')) return "HOD Rejected Leave Application" . $this->getApplicationStaffDetails('leave', $id);
        if (str_contains($cleanPath, 'hr/apply-leave/admin-approve')) return "Admin Approved Leave Application" . $this->getApplicationStaffDetails('leave', $id);
        if (str_contains($cleanPath, 'hr/apply-leave/admin-reject')) return "Admin Rejected Leave Application" . $this->getApplicationStaffDetails('leave', $id);
        if (str_contains($cleanPath, 'hr/apply-leave') && $method === 'POST') return "Submitted Annual / Casual Leave Application" . $this->getStaffNameById($request->input('employee_id'));
        if (str_contains($cleanPath, 'hr/apply-leave') && $method === 'PUT') return "Updated Leave Application" . $this->getApplicationStaffDetails('leave', $id);

        if (str_contains($cleanPath, 'hr/apply-loa/hod-approve')) return "HOD Recommended LOA Application" . $this->getApplicationStaffDetails('loa', $id);
        if (str_contains($cleanPath, 'hr/apply-loa/hod-reject')) return "HOD Rejected LOA Application" . $this->getApplicationStaffDetails('loa', $id);
        if (str_contains($cleanPath, 'hr/apply-loa/admin-approve')) return "Admin Approved LOA Application" . $this->getApplicationStaffDetails('loa', $id);
        if (str_contains($cleanPath, 'hr/apply-loa/admin-reject')) return "Admin Rejected LOA Application" . $this->getApplicationStaffDetails('loa', $id);
        if (str_contains($cleanPath, 'hr/apply-loa') && $method === 'POST') return "Submitted Leave of Absence (LOA) Application" . $this->getStaffNameById($request->input('employee_id'));
        if (str_contains($cleanPath, 'hr/apply-loa') && $method === 'PUT') return "Updated Leave of Absence (LOA) Application" . $this->getApplicationStaffDetails('loa', $id);
        if (str_contains($cleanPath, 'hr/leave-types')) return "Modified HR Leave Types Configuration";

        // ── 3. Loans & Cooperative ──
        if (str_contains($cleanPath, 'coop-loans/hod-approve')) return "HOD Recommended Cooperative Loan" . $this->getApplicationStaffDetails('coop_loan', $id);
        if (str_contains($cleanPath, 'coop-loans/hod-reject')) return "HOD Rejected Cooperative Loan" . $this->getApplicationStaffDetails('coop_loan', $id);
        if (str_contains($cleanPath, 'coop-loans/audit-approve')) return "Audit Recommended Cooperative Loan" . $this->getApplicationStaffDetails('coop_loan', $id);
        if (str_contains($cleanPath, 'coop-loans/audit-reject')) return "Audit Rejected Cooperative Loan" . $this->getApplicationStaffDetails('coop_loan', $id);
        if (str_contains($cleanPath, 'coop-loans/admin-approve')) return "Admin Approved Cooperative Loan" . $this->getApplicationStaffDetails('coop_loan', $id);
        if (str_contains($cleanPath, 'coop-loans/admin-reject')) return "Admin Rejected Cooperative Loan" . $this->getApplicationStaffDetails('coop_loan', $id);
        if (str_contains($cleanPath, 'coop-loans') && $method === 'POST') return "Applied for Cooperative Loan" . $this->getStaffNameById($request->input('staffId'));
        if (str_contains($cleanPath, 'coop-loans') && $method === 'DELETE') return "Deleted Cooperative Loan Application" . $this->getApplicationStaffDetails('coop_loan', $id);

        if (str_contains($cleanPath, 'loans/hod-approve')) return "HOD Recommended Staff Loan" . $this->getApplicationStaffDetails('employee_loan', $id);
        if (str_contains($cleanPath, 'loans/hod-reject')) return "HOD Rejected Staff Loan" . $this->getApplicationStaffDetails('employee_loan', $id);
        if (str_contains($cleanPath, 'loans/audit-approve')) return "Audit Recommended Staff Loan" . $this->getApplicationStaffDetails('employee_loan', $id);
        if (str_contains($cleanPath, 'loans/audit-reject')) return "Audit Rejected Staff Loan" . $this->getApplicationStaffDetails('employee_loan', $id);
        if (str_contains($cleanPath, 'loans/admin-approve')) return "Admin Approved Staff Loan" . $this->getApplicationStaffDetails('employee_loan', $id);
        if (str_contains($cleanPath, 'loans/admin-reject')) return "Admin Rejected Staff Loan" . $this->getApplicationStaffDetails('employee_loan', $id);
        if (str_contains($cleanPath, 'loans') && $method === 'POST') return "Applied for Staff Loan" . $this->getStaffNameById($request->input('staffId'));
        if (str_contains($cleanPath, 'loans') && $method === 'DELETE') return "Deleted Staff Loan" . $this->getApplicationStaffDetails('employee_loan', $id);

        // ── 4. Refunds & Resignations ──
        if (str_contains($cleanPath, 'refunds/hod-approve')) return "HOD Recommended Staff Refund" . $this->getApplicationStaffDetails('refund', $id);
        if (str_contains($cleanPath, 'refunds/hod-reject')) return "HOD Rejected Staff Refund" . $this->getApplicationStaffDetails('refund', $id);
        if (str_contains($cleanPath, 'refunds/hr-approve')) return "HR Recommended Staff Refund" . $this->getApplicationStaffDetails('refund', $id);
        if (str_contains($cleanPath, 'refunds/hr-reject')) return "HR Rejected Staff Refund" . $this->getApplicationStaffDetails('refund', $id);
        if (str_contains($cleanPath, 'refunds/audit-approve')) return "Audit Recommended Staff Refund" . $this->getApplicationStaffDetails('refund', $id);
        if (str_contains($cleanPath, 'refunds/audit-reject')) return "Audit Rejected Staff Refund" . $this->getApplicationStaffDetails('refund', $id);
        if (str_contains($cleanPath, 'refunds/finance-approve')) return "Finance Approved Staff Refund" . $this->getApplicationStaffDetails('refund', $id);
        if (str_contains($cleanPath, 'refunds/finance-reject')) return "Finance Rejected Staff Refund" . $this->getApplicationStaffDetails('refund', $id);
        if (str_contains($cleanPath, 'refunds') && $method === 'POST') return "Applied for Staff Expense Refund" . $this->getStaffNameById($request->input('staff_id'));

        if (str_contains($cleanPath, 'resignations/hod-approve')) return "HOD Recommended Resignation" . $this->getApplicationStaffDetails('resignation', $id);
        if (str_contains($cleanPath, 'resignations/hod-reject')) return "HOD Rejected Resignation" . $this->getApplicationStaffDetails('resignation', $id);
        if (str_contains($cleanPath, 'resignations/hr-approve')) return "HR Approved Resignation" . $this->getApplicationStaffDetails('resignation', $id);
        if (str_contains($cleanPath, 'resignations/hr-reject')) return "HR Rejected Resignation" . $this->getApplicationStaffDetails('resignation', $id);
        if (str_contains($cleanPath, 'resignations') && $method === 'POST') return "Submitted Staff Resignation" . $this->getStaffNameById($request->input('staff_id'));

        // ── 5. Payroll Management & Lock Cycles ──
        if (str_contains($cleanPath, 'payroll/compute')) {
            $month = $request->input('month');
            $year = $request->input('year');
            $period = '';
            if ($month && $year) {
                $monthName = is_numeric($month) ? date('F', mktime(0, 0, 0, (int)$month, 1)) : ucfirst($month);
                $period = " for {$monthName} {$year}";
            }
            $targetStaffId = $request->input('staffId') ?: $request->input('staff_id');
            $staffInfo = $targetStaffId ? $this->getStaffNameById($targetStaffId) : ' for All Active Staff';
            return "Computed Monthly Salary Payroll{$period}{$staffInfo}";
        }
        if (str_contains($cleanPath, 'payroll/lock-active-month/lock')) return "Locked Active Payroll Month";
        if (str_contains($cleanPath, 'payroll/lock-active-month/unlock')) return "Unlocked Active Payroll Month";
        if (str_contains($cleanPath, 'payroll/lock-active-month/forward-to-audit')) return "Forwarded Payroll Active Month to Audit";
        if (str_contains($cleanPath, 'payroll/lock-active-month/audit-approve')) return "Audit Approved Payroll Active Month";
        if (str_contains($cleanPath, 'payroll/lock-active-month/audit-reject')) return "Audit Rejected Payroll Active Month";
        if (str_contains($cleanPath, 'payroll/lock-active-month/pay')) return "Disbursed / Marked Monthly Payroll as Paid";
        if (str_contains($cleanPath, 'payroll/salary-increments/single')) {
            $type = $request->input('increment_type');
            $detail = '';
            if ($type === 'percentage' && $request->has('percentage')) {
                $detail = " ({$request->input('percentage')}% Increment)";
            } elseif ($type === 'fixed_amount' && $request->has('amount')) {
                $detail = " (+₦" . number_format((float)$request->input('amount'), 2) . ")";
            } elseif ($type === 'new_gross' && $request->has('new_gross')) {
                $detail = " (New Gross: ₦" . number_format((float)$request->input('new_gross'), 2) . ")";
            }
            return "Applied Staff Salary Increment{$detail}" . $this->getStaffNameById($request->input('staff_id'));
        }
        if (str_contains($cleanPath, 'payroll/salary-increments/bulk')) {
            $type = $request->input('increment_type');
            $detail = '';
            if ($type === 'percentage' && $request->has('percentage')) {
                $detail = " ({$request->input('percentage')}% Increment)";
            } elseif ($type === 'fixed_amount' && $request->has('amount')) {
                $detail = " (+₦" . number_format((float)$request->input('amount'), 2) . ")";
            }
            $target = $request->input('target_type') === 'department' ? 'Department' : 'All Staff';
            return "Applied Bulk Salary Increment for {$target}{$detail}";
        }
        if (str_contains($cleanPath, 'payroll/salary-increments/revert')) return "Reverted Staff Salary Increment" . $this->getStaffNameById($request->input('staff_id'));
        if (str_contains($cleanPath, 'payroll/payslip/send-email')) return "Dispatched Payslip via Email" . $this->getStaffNameById($request->input('staff_id'));
        if (str_contains($cleanPath, 'payroll/print-activation')) return "Toggled Payslip Print Activation";
        if (str_contains($cleanPath, 'payroll/bank-updates/individual')) return "Updated Staff Bank Details" . $this->getStaffNameById($request->input('staff_id'));
        if (str_contains($cleanPath, 'payroll/bank-updates/bulk')) return "Imported Bulk Staff Bank Updates";
        if (str_contains($cleanPath, 'payroll/payer-id/import')) return "Imported Bulk Staff Payer IDs";
        if (str_contains($cleanPath, 'payroll/payer-id')) {
            $targetStaffId = $request->input('staffId') ?: $request->input('staff_id');
            $payerId = $request->input('payer_id');
            $payerSuffix = $payerId ? " [{$payerId}]" : '';
            return "Updated Staff Payer ID{$payerSuffix}" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/bonus-allowance-setups/import')) return "Imported Bulk Staff Bonuses & Allowances";
        if (str_contains($cleanPath, 'payroll/bonus-allowance-setups/toggle')) {
            $setup = DB::table('staff_bonuses_and_allowances')->where('id', $id)->first();
            $targetStaffId = $setup ? $setup->staffId : null;
            $title = $setup ? " ({$setup->title})" : '';
            return "Toggled Staff Bonus / Allowance Status{$title}" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/bonus-allowance-setups') && $method === 'DELETE') {
            $setup = DB::table('staff_bonuses_and_allowances')->where('id', $id)->first();
            $targetStaffId = $setup ? $setup->staffId : null;
            $title = $setup ? " ({$setup->title})" : '';
            return "Deleted Staff Bonus / Allowance Setup{$title}" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/bonus-allowance-setups')) {
            $type = ucfirst($request->input('type', 'Bonus / Allowance'));
            $actionType = $request->input('id') ? 'Updated' : 'Created';
            $title = $request->input('title') ? " ({$request->input('title')})" : '';
            $amt = $request->input('amount') ? ' [₦' . number_format((float)$request->input('amount'), 2) . ']' : '';
            $targetStaffId = $request->input('staffId') ?: $request->input('staff_id');
            return "{$actionType} Staff {$type} Setup{$title}{$amt}" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/coop-loan-deduction-setups/import')) return "Imported Bulk Cooperative Loan Deduction Setups";
        if (str_contains($cleanPath, 'payroll/coop-loan-deduction-setups/toggle')) {
            $setup = DB::table('coop_loan_deduction_setups')->where('id', $id)->first();
            $targetStaffId = $setup ? $setup->staffId : null;
            return "Toggled Cooperative Loan Deduction Setup Status" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/coop-loan-deduction-setups') && $method === 'DELETE') {
            $setup = DB::table('coop_loan_deduction_setups')->where('id', $id)->first();
            $targetStaffId = $setup ? $setup->staffId : null;
            return "Deleted Cooperative Loan Deduction Setup" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/coop-loan-deduction-setups')) {
            $actionType = $request->input('id') ? 'Updated' : 'Created';
            $targetStaffId = $request->input('staffId') ?: $request->input('staff_id');
            $monthly = $request->has('monthly_deduction') ? ' (₦' . number_format((float)$request->input('monthly_deduction'), 2) . '/month)' : '';
            return "{$actionType} Cooperative Loan Deduction Setup{$monthly}" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/coop-savings-setups/import')) return "Imported Bulk Cooperative Savings Setups";
        if (str_contains($cleanPath, 'payroll/coop-savings-setups/toggle')) {
            $setup = DB::table('coop_savings_setups')->where('id', $id)->first();
            $targetStaffId = $setup ? $setup->staffId : null;
            return "Toggled Cooperative Savings Setup Status" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/coop-savings-setups') && $method === 'DELETE') {
            $setup = DB::table('coop_savings_setups')->where('id', $id)->first();
            $targetStaffId = $setup ? $setup->staffId : null;
            return "Deleted Cooperative Savings Setup" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/coop-savings-setups')) {
            $actionType = $request->input('id') ? 'Updated' : 'Created';
            $targetStaffId = $request->input('staffId') ?: $request->input('staff_id');
            $monthly = $request->has('monthly_saving') ? ' (₦' . number_format((float)$request->input('monthly_saving'), 2) . '/month)' : '';
            return "{$actionType} Cooperative Savings Setup{$monthly}" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/coop-savings-loan-offset')) {
            $targetStaffId = $request->input('staffId') ?: $request->input('staff_id');
            $offsetType = $request->input('offset_type') === 'bank' ? 'Direct Bank Payment' : 'Cooperative Savings';
            $amt = $request->has('offset_amount') ? ' [₦' . number_format((float)$request->input('offset_amount'), 2) . ']' : '';
            return "Processed Cooperative Loan Offset via {$offsetType}{$amt}" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/loan-deduction-setups/import')) return "Imported Bulk Loan Deduction Setups";
        if (str_contains($cleanPath, 'payroll/loan-deduction-setups/toggle')) {
            $setup = DB::table('loan_deduction_setups')->where('id', $id)->first();
            $targetStaffId = $setup ? $setup->staffId : null;
            return "Toggled Loan Deduction Setup Status" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/loan-deduction-setups') && $method === 'DELETE') {
            $setup = DB::table('loan_deduction_setups')->where('id', $id)->first();
            $targetStaffId = $setup ? $setup->staffId : null;
            return "Deleted Loan Deduction Setup" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/loan-deduction-setups')) {
            $actionType = $request->input('id') ? 'Updated' : 'Created';
            $targetStaffId = $request->input('staffId') ?: $request->input('staff_id');
            $monthly = $request->has('monthly_deduction') ? ' (₦' . number_format((float)$request->input('monthly_deduction'), 2) . '/month)' : '';
            return "{$actionType} Loan Deduction Setup{$monthly}" . $this->getStaffNameById($targetStaffId);
        }

        // Medical Loan Deduction Setup
        if (str_contains($cleanPath, 'payroll/medical-loan-deduction-setups/import')) return "Imported Bulk Medical Loan Deduction Setups";
        if (str_contains($cleanPath, 'payroll/medical-loan-deduction-setups/toggle')) {
            $setup = DB::table('medical_loan_deduction_setups')->where('id', $id)->first();
            $targetStaffId = $setup ? $setup->staffId : null;
            return "Toggled Medical Loan Deduction Setup Status" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/medical-loan-deduction-setups') && $method === 'DELETE') {
            $setup = DB::table('medical_loan_deduction_setups')->where('id', $id)->first();
            $targetStaffId = $setup ? $setup->staffId : null;
            return "Deleted Medical Loan Deduction Setup" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/medical-loan-deduction-setups')) {
            $actionType = $request->input('id') ? 'Updated' : 'Created';
            $targetStaffId = $request->input('staffId') ?: $request->input('staff_id');
            $monthly = $request->has('monthly_deduction') ? ' (₦' . number_format((float)$request->input('monthly_deduction'), 2) . '/month)' : '';
            return "{$actionType} Medical Loan Deduction Setup{$monthly}" . $this->getStaffNameById($targetStaffId);
        }

        // Medical Loan Entries & Records
        if (str_contains($cleanPath, 'payroll/medical-loan-entries') && $method === 'DELETE') {
            $entry = DB::table('medical_loan_entries')->where('id', $id)->first();
            $targetStaffId = $entry ? $entry->staffId : null;
            $amt = $entry ? ' [₦' . number_format((float)$entry->amount, 2) . ']' : '';
            return "Deleted Medical Loan Entry{$amt}" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/medical-loan-entries')) {
            $targetStaffId = $request->input('staffId') ?: $request->input('staff_id');
            $amt = $request->has('amount') ? ' [₦' . number_format((float)$request->input('amount'), 2) . ']' : '';
            return "Recorded New Medical Loan Entry{$amt}" . $this->getStaffNameById($targetStaffId);
        }

        // Surcharge Deduction Setup
        if (str_contains($cleanPath, 'payroll/surcharge-deduction-setups/import')) return "Imported Bulk Surcharge Deduction Setups";
        if (str_contains($cleanPath, 'payroll/surcharge-deduction-setups/toggle')) {
            $setup = DB::table('surcharge_deduction_setups')->where('id', $id)->first();
            $targetStaffId = $setup ? $setup->staffId : null;
            return "Toggled Surcharge Deduction Setup Status" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/surcharge-deduction-setups') && $method === 'DELETE') {
            $setup = DB::table('surcharge_deduction_setups')->where('id', $id)->first();
            $targetStaffId = $setup ? $setup->staffId : null;
            return "Deleted Surcharge Deduction Setup" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/surcharge-deduction-setups')) {
            $actionType = $request->input('id') ? 'Updated' : 'Created';
            $targetStaffId = $request->input('staffId') ?: $request->input('staff_id');
            $monthly = $request->has('monthly_deduction') ? ' (₦' . number_format((float)$request->input('monthly_deduction'), 2) . '/month)' : '';
            return "{$actionType} Surcharge Deduction Setup{$monthly}" . $this->getStaffNameById($targetStaffId);
        }

        // Absence Penalty Deduction Setup
        if (str_contains($cleanPath, 'payroll/absence-penalty-deduction-setups/import')) return "Imported Bulk Absence Penalty Deduction Setups";
        if (str_contains($cleanPath, 'payroll/absence-penalty-deduction-setups/toggle')) {
            $setup = DB::table('absence_penalty_deduction_setups')->where('id', $id)->first();
            $targetStaffId = $setup ? $setup->staffId : null;
            return "Toggled Absence Penalty Deduction Setup Status" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/absence-penalty-deduction-setups') && $method === 'DELETE') {
            $setup = DB::table('absence_penalty_deduction_setups')->where('id', $id)->first();
            $targetStaffId = $setup ? $setup->staffId : null;
            return "Deleted Absence Penalty Deduction Setup" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/absence-penalty-deduction-setups')) {
            $actionType = $request->input('id') ? 'Updated' : 'Created';
            $targetStaffId = $request->input('staffId') ?: $request->input('staff_id');
            $monthly = $request->has('monthly_deduction') ? ' (₦' . number_format((float)$request->input('monthly_deduction'), 2) . '/month)' : '';
            return "{$actionType} Absence Penalty Deduction Setup{$monthly}" . $this->getStaffNameById($targetStaffId);
        }

        // Other Deduction Setup
        if (str_contains($cleanPath, 'payroll/other-deduction-setups/import')) return "Imported Bulk Other Deduction Setups";
        if (str_contains($cleanPath, 'payroll/other-deduction-setups/toggle')) {
            $setup = DB::table('other_deduction_setups')->where('id', $id)->first();
            $targetStaffId = $setup ? $setup->staffId : null;
            return "Toggled Other Deduction Setup Status" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/other-deduction-setups') && $method === 'DELETE') {
            $setup = DB::table('other_deduction_setups')->where('id', $id)->first();
            $targetStaffId = $setup ? $setup->staffId : null;
            return "Deleted Other Deduction Setup" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/other-deduction-setups')) {
            $actionType = $request->input('id') ? 'Updated' : 'Created';
            $targetStaffId = $request->input('staffId') ?: $request->input('staff_id');
            $monthly = $request->has('monthly_deduction') ? ' (₦' . number_format((float)$request->input('monthly_deduction'), 2) . '/month)' : '';
            return "{$actionType} Other Deduction Setup{$monthly}" . $this->getStaffNameById($targetStaffId);
        }

        // Coop Asset Finance Deduction Setup
        if (str_contains($cleanPath, 'payroll/coop-asset-finance-deduction-setups/import')) return "Imported Bulk Coop Asset Finance Deduction Setups";
        if (str_contains($cleanPath, 'payroll/coop-asset-finance-deduction-setups/toggle')) {
            $setup = DB::table('coop_asset_finance_deduction_setups')->where('id', $id)->first();
            $targetStaffId = $setup ? $setup->staffId : null;
            return "Toggled Coop Asset Finance Deduction Setup Status" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/coop-asset-finance-deduction-setups') && $method === 'DELETE') {
            $setup = DB::table('coop_asset_finance_deduction_setups')->where('id', $id)->first();
            $targetStaffId = $setup ? $setup->staffId : null;
            return "Deleted Coop Asset Finance Deduction Setup" . $this->getStaffNameById($targetStaffId);
        }
        if (str_contains($cleanPath, 'payroll/coop-asset-finance-deduction-setups')) {
            $actionType = $request->input('id') ? 'Updated' : 'Created';
            $targetStaffId = $request->input('staffId') ?: $request->input('staff_id');
            $monthly = $request->has('monthly_deduction') ? ' (₦' . number_format((float)$request->input('monthly_deduction'), 2) . '/month)' : '';
            return "{$actionType} Coop Asset Finance Deduction Setup{$monthly}" . $this->getStaffNameById($targetStaffId);
        }

        // ── 6. HR Staff Registration & Documentation ──
        if (str_contains($cleanPath, 'hr/add-staff/import')) return "Imported Bulk Employee Records";
        if (str_contains($cleanPath, 'hr/add-staff') && $method === 'POST') {
            $name = $request->input('surname') ? " ({$request->input('surname')} {$request->input('first_name')})" : '';
            return "Registered New Staff Record{$name}";
        }
        if (str_contains($cleanPath, 'hr/documentation')) {
            $sub = basename($cleanPath);
            $targetStaff = '';
            // If URL has staff id e.g. /hr/documentation/123/basic
            $segments = explode('/', $cleanPath);
            foreach ($segments as $seg) {
                if (is_numeric($seg)) {
                    $targetStaff = $this->getStaffNameById($seg);
                    break;
                }
            }
            return "Updated Staff Documentation (" . ucwords(str_replace('-', ' ', $sub)) . "){$targetStaff}";
        }
        if (str_contains($cleanPath, 'hr/staff-status/update')) {
            $fileNo = $request->input('fileNo');
            $staffName = '';
            if ($fileNo) {
                $st = DB::table('tblper')->where('fileNo', $fileNo)->orWhere('ID', $fileNo)->first();
                if ($st) {
                    $staffName = " for " . trim("{$st->surname} {$st->first_name}");
                }
            }

            $act = $request->input('action');
            if ($act === 'Update Staff Record') {
                $newStatus = ucwords(trim((string)$request->input('staffStatus', '')));
                $statusSuffix = $newStatus ? " to [{$newStatus}]" : '';
                return "Updated Staff Status{$staffName}{$statusSuffix}";
            } elseif ($act === 'Transfer Staff') {
                $divName = '';
                if ($request->has('staffDivision')) {
                    $div = DB::table('tbldivision')->where('divisionID', $request->input('staffDivision'))->first();
                    if ($div) $divName = " to {$div->division} Division";
                }
                return "Initiated Staff Division Transfer{$staffName}{$divName}";
            }
            return "Updated Staff Status Record{$staffName}";
        }
        if (str_contains($cleanPath, 'hr/staff-status/approve-transfers')) {
            $transAction = $request->input('action') === 'approve' ? 'Approved' : ($request->input('action') === 'reject' ? 'Rejected' : 'Processed');
            return "{$transAction} Staff Division Transfer Request";
        }

        // ── 7. Roles, Modules & Permissions ──
        if (str_contains($cleanPath, 'roles/update')) return "Updated System User Role";
        if (str_contains($cleanPath, 'roles') && $method === 'POST') return "Created New System User Role";
        if (str_contains($cleanPath, 'modules/update')) return "Updated System Application Module";
        if (str_contains($cleanPath, 'modules') && $method === 'POST') return "Created New System Application Module";
        if (str_contains($cleanPath, 'submodules')) return "Modified Submodule Configuration";
        if (str_contains($cleanPath, 'assign-module/assign')) return "Assigned Module Permissions to Role";
        if (str_contains($cleanPath, 'user-assign/assign')) return "Assigned Role to System User";
        if (str_contains($cleanPath, 'assign-hod')) return "Assigned Department HOD";
        if (str_contains($cleanPath, 'hod-delegations/toggle')) return "Toggled HOD Authority Delegation";
        if (str_contains($cleanPath, 'hod-delegations')) return "Created HOD Authority Delegation";
        if (str_contains($cleanPath, 'hr-delegations')) return "Created HR Authority Delegation";

        // ── 8. Exports & Downloads ──
        if (str_contains($cleanPath, 'export') || str_contains($cleanPath, 'download')) {
            return "Exported Data / Report Sheet (" . ucfirst(basename($cleanPath)) . ")";
        }

        // ── 9. Generic Fallback ──
        $verb = $method === 'DELETE' ? 'Deleted' : ($method === 'PUT' || $method === 'PATCH' || str_contains($cleanPath, 'update') || str_contains($cleanPath, 'toggle') ? 'Updated' : 'Created');
        return "{$verb} " . ucwords(str_replace(['/', '-', '_'], ' ', $cleanPath));
    }
}
