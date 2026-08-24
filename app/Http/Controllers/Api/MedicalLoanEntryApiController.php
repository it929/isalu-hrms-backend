<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MedicalLoanEntryApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * GET /api/nextjs/payroll/medical-loan-entries
     * Fetch medical loan entries history and overall summary statistics.
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $userId = $request->header('X-User-Id');
            $search = trim($request->input('search', ''));
            $monthFilter = trim($request->input('month', '')); // e.g. YYYY-MM
            $fromDate = trim($request->input('from_date', '')); // e.g. YYYY-MM-DD
            $toDate = trim($request->input('to_date', ''));     // e.g. YYYY-MM-DD
            $staffIdFilter = $request->input('staffId', null);

            // Fetch explicit roles from assign_user_role for this user
            $userRoles = DB::table('assign_user_role')
                ->leftJoin('user_role', 'assign_user_role.roleID', '=', 'user_role.roleID')
                ->where('assign_user_role.userID', $userId)
                ->select('assign_user_role.roleID', 'user_role.rolename')
                ->get();

            $roleIds = $userRoles->pluck('roleID')->map(fn($id) => (int)$id)->toArray();
            $roleNames = $userRoles->pluck('rolename')->filter()->map(fn($r) => strtolower(trim($r)))->toArray();

            // Strictly: Super Admin, HR Head, Audit Head, Finance Head
            $isSuperAdmin = in_array(1, $roleIds) || in_array('super administrator', $roleNames) || in_array('superadmin', $roleNames);
            $isHrHead = in_array(48, $roleIds) || in_array(68, $roleIds) || in_array('hr head', $roleNames) || in_array('head of hr', $roleNames);
            $isAuditHead = in_array(34, $roleIds) || in_array(35, $roleIds) || in_array(70, $roleIds) || in_array('audit head', $roleNames) || in_array('head of audit', $roleNames);
            $isFinanceHead = in_array(36, $roleIds) || in_array(37, $roleIds) || in_array(69, $roleIds) || in_array('finance head', $roleNames) || in_array('head of finance', $roleNames);

            $isPrivileged = $isSuperAdmin || $isHrHead || $isAuditHead || $isFinanceHead;
            $employee = $ctx['employee'];

            // Access Control: Non-privileged staff members ONLY see their own records
            $effectiveStaffId = null;
            if (!$isPrivileged) {
                $effectiveStaffId = $employee ? $employee->ID : (is_numeric($userId) ? (int)$userId : -1);
                $staffIdFilter = $effectiveStaffId;
            } else {
                if (!empty($staffIdFilter) && is_numeric($staffIdFilter)) {
                    $effectiveStaffId = intval($staffIdFilter);
                }
            }

            $query = DB::table('medical_loan_entries as mle')
                ->join('tblper as p', 'p.ID', '=', 'mle.staffId')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->leftJoin('tblper as creator', 'creator.ID', '=', 'mle.created_by')
                ->select(
                    'mle.*',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'd.department',
                    'creator.surname as creator_surname',
                    'creator.first_name as creator_first_name'
                );

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('p.fileNo', 'like', "%{$search}%")
                      ->orWhere('p.surname', 'like', "%{$search}%")
                      ->orWhere('p.first_name', 'like', "%{$search}%")
                      ->orWhere('p.othernames', 'like', "%{$search}%")
                      ->orWhere('mle.reason', 'like', "%{$search}%");
                });
            }

            if ($monthFilter !== '') {
                $query->where('mle.loan_date', 'like', "{$monthFilter}%");
            }

            if ($fromDate !== '') {
                $query->where('mle.loan_date', '>=', $fromDate);
            }

            if ($toDate !== '') {
                $query->where('mle.loan_date', '<=', $toDate);
            }

            if ($effectiveStaffId !== null) {
                $query->where('mle.staffId', $effectiveStaffId);
            }

            $records = $query->orderBy('mle.loan_date', 'desc')
                ->orderBy('mle.id', 'desc')
                ->get()
                ->map(function ($row) {
                    $row->name = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                    $row->amount = (float) $row->amount;
                    $row->balance_before = (float) $row->balance_before;
                    $row->balance_after = (float) $row->balance_after;
                    $calc = $this->calculateDeductionAndDuration($row->balance_after);
                    $row->monthly_deduction = isset($row->monthly_deduction) && (float)$row->monthly_deduction > 0
                        ? (float) $row->monthly_deduction
                        : (float) $calc['monthly_deduction'];
                    $row->duration_months = (int) $calc['duration_months'];
                    $row->creator_name = $row->creator_surname ? trim("{$row->creator_surname} {$row->creator_first_name}") : 'System / Admin';
                    return $row;
                });

            // Calculate overall and filtered summary metrics
            $totalDisbursed = (float) $records->sum('amount');
            $totalEntries = $records->count();
            
            $totalOutstanding = 0.00;
            $activeStaffCount = 0;
            $staffSetup = null;

            if (\Illuminate\Support\Facades\Schema::hasTable('medical_loan_deduction_setups')) {
                if ($staffIdFilter) {
                    $setupRow = DB::table('medical_loan_deduction_setups')
                        ->where('staffId', intval($staffIdFilter))
                        ->first();

                    if ($setupRow) {
                        $staffSetup = [
                            'id' => $setupRow->id,
                            'loan_amount' => (float) $setupRow->loan_amount,
                            'balance_remaining' => (float) $setupRow->balance_remaining,
                            'monthly_deduction' => (float) $setupRow->monthly_deduction,
                            'duration_months' => (int) $setupRow->duration_months,
                            'start_month' => $setupRow->start_month,
                            'end_month' => $setupRow->end_month,
                            'is_active' => (int) $setupRow->is_active,
                        ];
                        $totalOutstanding = (float) $setupRow->balance_remaining;
                        $activeStaffCount = $setupRow->is_active && $setupRow->balance_remaining > 0 ? 1 : 0;
                    }
                } else {
                    $totalOutstanding = (float) DB::table('medical_loan_deduction_setups')
                        ->where('is_active', 1)
                        ->where('balance_remaining', '>', 0)
                        ->sum('balance_remaining');

                    $activeStaffCount = DB::table('medical_loan_deduction_setups')
                        ->where('is_active', 1)
                        ->where('balance_remaining', '>', 0)
                        ->count();
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => $records,
                'summary' => [
                    'total_disbursed' => $totalDisbursed,
                    'total_entries' => $totalEntries,
                    'total_outstanding' => $totalOutstanding,
                    'active_staff_count' => $activeStaffCount,
                ],
                'staff_setup' => $staffSetup,
                'filter' => [
                    'from_date' => $fromDate ?: null,
                    'to_date' => $toDate ?: null,
                    'month' => $monthFilter ?: null,
                    'staffId' => $staffIdFilter ? intval($staffIdFilter) : null,
                ],
                'isSuperAdmin' => $isSuperAdmin,
                'isHod' => !empty($ctx['isHod']),
                'isAdminStaff' => $isHrHead,
                'isAuditStaff' => $isAuditHead,
                'isFinanceStaff' => $isFinanceHead,
                'isPrivileged' => $isPrivileged,
                'employee' => $employee,
            ]);
        } catch (\Throwable $th) {
            Log::error('MedicalLoanEntryApiController index: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/medical-loan-entries/staff-balance/{staffId}
     * Get staff's current medical loan balance, current monthly deduction, and simulation with new amount.
     */
    public function getStaffBalance(Request $request, $staffId)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $staff = DB::table('tblper as p')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->where('p.ID', $staffId)
                ->select('p.ID as id', 'p.fileNo', 'p.surname', 'p.first_name', 'p.othernames', 'd.department')
                ->first();

            if (!$staff) {
                return response()->json(['status' => 'error', 'message' => 'Staff member not found.'], 404);
            }

            $staffName = trim("{$staff->surname} {$staff->first_name} {$staff->othernames}");

            $setup = DB::table('medical_loan_deduction_setups')
                ->where('staffId', $staffId)
                ->first();

            $currentBalance = $setup ? (float) $setup->balance_remaining : 0.00;
            $currentDeduction = $setup ? (float) $setup->monthly_deduction : 0.00;
            $currentDuration = $setup ? (int) $setup->duration_months : 0;
            $startMonth = $setup ? $setup->start_month : null;
            $endMonth = $setup ? $setup->end_month : null;
            $isActive = $setup ? (int) $setup->is_active : 0;

            // Optional simulation if amount passed
            $simAmount = (float) $request->input('amount', 0.00);
            $newSimBalance = $currentBalance + $simAmount;
            $simCalc = $this->calculateDeductionAndDuration($newSimBalance);

            // Fetch recent medical loan history for this staff
            $history = DB::table('medical_loan_entries')
                ->where('staffId', $staffId)
                ->orderBy('loan_date', 'desc')
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'status' => 'success',
                'staff' => [
                    'id' => $staff->id,
                    'name' => $staffName,
                    'fileNo' => $staff->fileNo,
                    'department' => $staff->department,
                ],
                'current_setup' => [
                    'has_setup' => !empty($setup),
                    'balance_remaining' => $currentBalance,
                    'monthly_deduction' => $currentDeduction,
                    'duration_months' => $currentDuration,
                    'start_month' => $startMonth,
                    'end_month' => $endMonth,
                    'is_active' => $isActive,
                ],
                'simulation' => [
                    'additional_amount' => $simAmount,
                    'projected_balance' => $newSimBalance,
                    'projected_monthly_deduction' => $simCalc['monthly_deduction'],
                    'projected_duration_months' => $simCalc['duration_months'],
                ],
                'history' => $history,
            ]);
        } catch (\Throwable $th) {
            Log::error('MedicalLoanEntryApiController getStaffBalance: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/medical-loan-entries
     * Record a new medical loan taken by staff and update the deduction setup balance & monthly deduction.
     */
    public function store(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $validated = $request->validate([
                'staffId' => 'required|integer|exists:tblper,ID',
                'loan_date' => 'required|date',
                'amount' => 'required|numeric|min:1',
                'reason' => 'required|string|max:1000',
            ]);

            $staffId = (int) $validated['staffId'];
            $loanDate = $validated['loan_date'];
            $amount = (float) $validated['amount'];
            $reason = trim($validated['reason']);
            $currentUserId = $ctx['employee'] ? $ctx['employee']->ID : ($ctx['user']->id ?? null);

            DB::beginTransaction();

            // 1. Fetch current deduction setup for staff with lock
            $existingSetup = DB::table('medical_loan_deduction_setups')
                ->where('staffId', $staffId)
                ->lockForUpdate()
                ->first();

            $balanceBefore = $existingSetup ? (float) $existingSetup->balance_remaining : 0.00;
            $balanceAfter = $balanceBefore + $amount;
            $newTotalLoanAmount = $existingSetup ? ((float)$existingSetup->loan_amount + $amount) : $amount;

            // 2. Recalculate monthly deduction and duration based on the new balance remaining
            $calc = $this->calculateDeductionAndDuration($balanceAfter);
            $monthlyDeduction = $calc['monthly_deduction'];
            $durationMonths = $calc['duration_months'];

            // 3. Determine start & end months
            $loanMonth = date('Y-m', strtotime($loanDate));
            $startMonth = $loanMonth;
            
            if ($existingSetup && $existingSetup->is_active == 1 && $balanceBefore > 0) {
                // If previous loan deduction is active and ongoing, start month stays existing or current
                if (!empty($existingSetup->start_month) && $existingSetup->start_month <= $loanMonth) {
                    $startMonth = $existingSetup->start_month;
                }
            }

            // Calculate end month based on duration
            $endMonth = $loanMonth;
            if ($durationMonths > 0) {
                $parts = explode('-', $loanMonth);
                if (count($parts) === 2) {
                    $y = (int)$parts[0];
                    $m = (int)$parts[1];
                    $startDate = new \DateTime("$y-$m-01");
                    $startDate->modify("+" . ($durationMonths - 1) . " month");
                    $endMonth = $startDate->format('Y-m');
                }
            }

            // 4. Insert medical loan entry record
            $entryId = DB::table('medical_loan_entries')->insertGetId([
                'staffId' => $staffId,
                'loan_date' => $loanDate,
                'amount' => $amount,
                'reason' => $reason,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'monthly_deduction' => $monthlyDeduction,
                'created_by' => $currentUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 5. Update or insert deduction setup
            if ($existingSetup) {
                DB::table('medical_loan_deduction_setups')
                    ->where('id', $existingSetup->id)
                    ->update([
                        'loan_amount' => $newTotalLoanAmount,
                        'balance_remaining' => $balanceAfter,
                        'monthly_deduction' => $monthlyDeduction,
                        'duration_months' => $durationMonths,
                        'start_month' => $startMonth,
                        'end_month' => $endMonth,
                        'is_active' => 1,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('medical_loan_deduction_setups')->insert([
                    'staffId' => $staffId,
                    'loan_amount' => $amount,
                    'balance_remaining' => $balanceAfter,
                    'monthly_deduction' => $monthlyDeduction,
                    'duration_months' => $durationMonths,
                    'start_month' => $startMonth,
                    'end_month' => $endMonth,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Medical loan entry recorded successfully. Deduction balance and monthly deduction have been updated.',
                'entry_id' => $entryId,
                'data' => [
                    'staffId' => $staffId,
                    'amount_added' => $amount,
                    'previous_balance' => $balanceBefore,
                    'new_balance_remaining' => $balanceAfter,
                    'new_monthly_deduction' => $monthlyDeduction,
                    'duration_months' => $durationMonths,
                    'start_month' => $startMonth,
                    'end_month' => $endMonth,
                ]
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('MedicalLoanEntryApiController store: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/nextjs/payroll/medical-loan-entries/{id}
     * Delete an entry and roll back its amount from the staff deduction setup remaining balance.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $entry = DB::table('medical_loan_entries')->where('id', $id)->first();
            if (!$entry) {
                return response()->json(['status' => 'error', 'message' => 'Medical loan entry not found.'], 404);
            }

            DB::beginTransaction();

            $staffId = $entry->staffId;
            $amountToRemove = (float) $entry->amount;

            $setup = DB::table('medical_loan_deduction_setups')
                ->where('staffId', $staffId)
                ->lockForUpdate()
                ->first();

            if ($setup) {
                $newBalance = max(0.00, (float)$setup->balance_remaining - $amountToRemove);
                $newLoanAmount = max(0.00, (float)$setup->loan_amount - $amountToRemove);

                if ($newBalance <= 0) {
                    DB::table('medical_loan_deduction_setups')
                        ->where('id', $setup->id)
                        ->update([
                            'loan_amount' => $newLoanAmount,
                            'balance_remaining' => 0.00,
                            'monthly_deduction' => 0.00,
                            'duration_months' => 0,
                            'is_active' => 0,
                            'updated_at' => now(),
                        ]);
                } else {
                    $calc = $this->calculateDeductionAndDuration($newBalance);
                    DB::table('medical_loan_deduction_setups')
                        ->where('id', $setup->id)
                        ->update([
                            'loan_amount' => $newLoanAmount,
                            'balance_remaining' => $newBalance,
                            'monthly_deduction' => $calc['monthly_deduction'],
                            'duration_months' => $calc['duration_months'],
                            'is_active' => 1,
                            'updated_at' => now(),
                        ]);
                }
            }

            DB::table('medical_loan_entries')->where('id', $id)->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Medical loan entry deleted successfully. Deduction balance has been updated.'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('MedicalLoanEntryApiController destroy: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Helper to compute dynamic deduction and duration months based on loan amount / balance remaining.
     */
    private function calculateDeductionAndDuration($balance)
    {
        $balance = (float)$balance;
        if ($balance <= 0) {
            return ['monthly_deduction' => 0.00, 'duration_months' => 0];
        }

        $deduction = 0.00;
        if ($balance < 5000) {
            $deduction = $balance;
        } elseif ($balance >= 5000 && $balance <= 10000) {
            $deduction = 5000;
        } elseif ($balance > 10000 && $balance <= 30000) {
            $deduction = 10000;
        } elseif ($balance > 30000 && $balance <= 60000) {
            $deduction = 15000;
        } elseif ($balance > 60000 && $balance <= 120000) {
            $deduction = 20000;
        } elseif ($balance > 120000 && $balance <= 160000) {
            $deduction = 25000;
        } elseif ($balance > 160000 && $balance <= 300000) {
            $deduction = 30000;
        } elseif ($balance > 300000 && $balance <= 600000) {
            $deduction = 35000;
        } else {
            $deduction = 50000;
        }

        $tempBal = $balance;
        $durationMonths = 0;
        while ($tempBal > 0) {
            $currentDeduct = 0.00;
            if ($tempBal < 5000) {
                $currentDeduct = $tempBal;
            } elseif ($tempBal >= 5000 && $tempBal <= 10000) {
                $currentDeduct = 5000;
            } elseif ($tempBal > 10000 && $tempBal <= 30000) {
                $currentDeduct = 10000;
            } elseif ($tempBal > 30000 && $tempBal <= 60000) {
                $currentDeduct = 15000;
            } elseif ($tempBal > 60000 && $tempBal <= 120000) {
                $currentDeduct = 20000;
            } elseif ($tempBal > 120000 && $tempBal <= 160000) {
                $currentDeduct = 25000;
            } elseif ($tempBal > 160000 && $tempBal <= 300000) {
                $currentDeduct = 30000;
            } elseif ($tempBal > 300000 && $tempBal <= 600000) {
                $currentDeduct = 35000;
            } else {
                $currentDeduct = 50000;
            }
            
            if ($currentDeduct <= 0) break;
            $tempBal -= $currentDeduct;
            $durationMonths++;
            if ($durationMonths > 1000) break; // safety
        }

        return [
            'monthly_deduction' => $deduction,
            'duration_months' => $durationMonths
        ];
    }
}
