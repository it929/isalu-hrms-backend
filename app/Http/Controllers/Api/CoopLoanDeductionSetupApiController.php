<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CoopLoanDeductionSetupApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * Resolve the current user context from the X-User-Id header.
     */
    

    /**
     * GET /api/nextjs/payroll/coop-loan-deduction-setups
     * Fetch existing coop loan deduction setups.
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $search = trim($request->input('search', ''));
            $query = DB::table('coop_loan_deduction_setups as clds')
                ->join('tblper as p', 'p.ID', '=', 'clds.staffId')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->select(
                    'clds.*',
                    'p.fileNo',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'd.department'
                );

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('p.fileNo', 'like', "%{$search}%")
                      ->orWhere('p.surname', 'like', "%{$search}%")
                      ->orWhere('p.first_name', 'like', "%{$search}%")
                      ->orWhere('p.othernames', 'like', "%{$search}%");
                });
            }

            $employee = $ctx['employee'];

            $records = $query->orderBy('clds.id', 'desc')->get()->map(function ($row) {
                $row->name = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                $row->is_active = (int) $row->is_active;
                return $row;
            });

            return response()->json([
                'status' => 'success',
                'data' => $records,
                'isSuperAdmin' => $ctx['isSuperAdmin'],
                'isHod' => $ctx['isHod'],
                'isAdminStaff' => $ctx['isAdminStaff'],
                'isAuditStaff' => $ctx['isAuditStaff'],
                'isFinanceStaff' => $ctx['isFinanceStaff'],
                'employee' => $employee,
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopLoanDeductionSetupApiController index: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/coop-loan-deduction-setups
     * Save or update a cooperative loan deduction setup.
     */
    public function store(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $validated = $request->validate([
                'id' => 'nullable|integer',
                'staffId' => 'required|integer|exists:tblper,ID',
                'loan_amount' => 'required|numeric|min:0',
                'interest_rate' => 'required|numeric|min:0|max:100',
                'duration_months' => 'required|integer|min:1',
                'monthly_deduction' => 'required|numeric|min:0',
                'balance_remaining' => 'nullable|numeric|min:0',
                'start_month' => 'required|string|regex:/^\d{4}-\d{2}$/',
                'end_month' => 'required|string|regex:/^\d{4}-\d{2}$/',
                'is_active' => 'nullable|integer|in:0,1',
            ]);



            $id = $validated['id'] ?? null;
            $loanAmount = (float) $validated['loan_amount'];
            $interestRate = (float) $validated['interest_rate'];
            $totalRepayment = $loanAmount * (1 + $interestRate / 100);
            $balanceRemaining = (isset($validated['balance_remaining']) && $validated['balance_remaining'] !== null) ? (float) $validated['balance_remaining'] : $totalRepayment;

            $data = [
                'staffId' => $validated['staffId'],
                'loan_amount' => $loanAmount,
                'interest_rate' => (float) $validated['interest_rate'],
                'duration_months' => (int) $validated['duration_months'],
                'monthly_deduction' => (float) $validated['monthly_deduction'],
                'balance_remaining' => $balanceRemaining,
                'start_month' => $validated['start_month'],
                'end_month' => $validated['end_month'],
                'is_active' => $validated['is_active'] ?? 1,
                'updated_at' => now(),
            ];

            if ($id) {
                $existing = DB::table('coop_loan_deduction_setups')->where('id', $id)->first();
                if (!$existing) {
                    return response()->json(['status' => 'error', 'message' => 'Deduction setup not found.'], 404);
                }
                $newIsActive = isset($validated['is_active']) ? (int)$validated['is_active'] : 1;
                $canToggle = !empty($ctx['isSuperAdmin']) || !empty($ctx['isFinanceStaff']);
                if ((int)$existing->is_active !== $newIsActive && !$canToggle) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Permission denied: Only Super Administrators and Finance Head are authorized to activate and deactivate Cooperative Loan Deduction Setup.'
                    ], 403);
                }
                DB::table('coop_loan_deduction_setups')->where('id', $id)->update($data);
                $message = 'Cooperative loan deduction setup updated successfully.';
            } else {
                $data['created_at'] = now();
                DB::table('coop_loan_deduction_setups')->insert($data);
                $message = 'Cooperative loan deduction setup created successfully.';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopLoanDeductionSetupApiController store: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/coop-loan-deduction-setups/toggle/{id}
     * Toggle activation status of cooperative loan deduction setup.
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $setup = DB::table('coop_loan_deduction_setups')->where('id', $id)->first();
            if (!$setup) {
                return response()->json(['status' => 'error', 'message' => 'Setup not found.'], 404);
            }

            $canToggle = !empty($ctx['isSuperAdmin']) || !empty($ctx['isFinanceStaff']);
            if (!$canToggle) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Permission denied: Only Super Administrators and Finance Head are authorized to activate and deactivate Cooperative Loan Deduction Setup.'
                ], 403);
            }

            $newStatus = $setup->is_active == 1 ? 0 : 1;

            DB::table('coop_loan_deduction_setups')->where('id', $id)->update([
                'is_active' => $newStatus,
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => $newStatus == 1 ? 'Deduction setup activated successfully.' : 'Deduction setup deactivated successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopLoanDeductionSetupApiController toggleStatus: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/nextjs/payroll/coop-loan-deduction-setups/{id}
     * Delete a cooperative loan deduction setup.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }



            $exists = DB::table('coop_loan_deduction_setups')->where('id', $id)->exists();
            if (!$exists) {
                return response()->json(['status' => 'error', 'message' => 'Setup not found.'], 404);
            }

            DB::table('coop_loan_deduction_setups')->where('id', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Cooperative loan deduction setup deleted successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopLoanDeductionSetupApiController destroy: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/coop-loan-deduction-setups/template
     * Download public CSV template.
     */
    public function downloadTemplate(Request $request)
    {
        try {
            $headers = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="coop_loan_setup_import_template.csv"',
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Expires'             => '0',
            ];

            $columns = ['Staff ID', 'Amount Deduct Monthly', 'Balance', 'Start Month (YYYY-MM)'];
            $exampleRow = ['1024', '25000.00', '150000.00', '2026-06'];

            $callback = function () use ($columns, $exampleRow) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $columns);
                fputcsv($handle, $exampleRow);
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Throwable $th) {
            Log::error('CoopLoanDeductionSetupApiController downloadTemplate: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/coop-loan-deduction-setups/import
     * Bulk import from spreadsheet.
     */
    public function import(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv'
            ]);

            $file = $request->file('file');
            $rows = \Maatwebsite\Excel\Facades\Excel::toArray([], $file)[0];

            if (empty($rows) || count($rows) <= 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The uploaded file is empty or contains no records.'
                ], 422);
            }

            // Normalize headers
            $staffIdIdx = -1;
            $fileNoIdx = -1;
            $monthlyDeductIdx = -1;
            $balanceIdx = -1;
            $loanAmountIdx = -1;
            $interestIdx = -1;
            $durationIdx = -1;
            $startIdx = -1;

            foreach ($rows[0] as $index => $rawHeader) {
                $h = strtolower(preg_replace('/[^a-z0-9]/', '', (string)$rawHeader));
                if (in_array($h, ['staffid', 'id', 'staff', 'employeeid', 'empid', 'staffno'])) {
                    $staffIdIdx = $index;
                } elseif (in_array($h, ['fileno', 'file', 'filenumber', 'file_no'])) {
                    $fileNoIdx = $index;
                } elseif (in_array($h, ['amountdeductmonthly', 'monthlydeduction', 'monthlydeductionamount', 'amountdeduct', 'monthlydeduct', 'monthlyamount', 'deductmonthly', 'monthly', 'monthlysavings'])) {
                    $monthlyDeductIdx = $index;
                } elseif (in_array($h, ['balance', 'balanceremaining', 'loanbalance', 'remainingbalance', 'savingbalance', 'bal'])) {
                    $balanceIdx = $index;
                } elseif (in_array($h, ['loanamount', 'amount', 'loan', 'totalloan', 'totalloanamount'])) {
                    $loanAmountIdx = $index;
                } elseif (in_array($h, ['interestrate', 'interest', 'rate'])) {
                    $interestIdx = $index;
                } elseif (in_array($h, ['durationmonths', 'duration', 'months'])) {
                    $durationIdx = $index;
                } elseif (in_array($h, ['startmonth', 'startmonthyyyymm', 'start', 'period', 'month', 'startperiod'])) {
                    $startIdx = $index;
                } else {
                    // Partial fallback check
                    $rawLower = strtolower(trim((string)$rawHeader));
                    if (strpos($rawLower, 'balance') !== false || strpos($rawLower, 'bal') !== false) {
                        $balanceIdx = $index;
                    } elseif (strpos($rawLower, 'monthly') !== false || strpos($rawLower, 'deduct') !== false) {
                        $monthlyDeductIdx = $index;
                    } elseif (strpos($rawLower, 'staff') !== false || strpos($rawLower, 'id') !== false) {
                        $staffIdIdx = $index;
                    } elseif (strpos($rawLower, 'loan') !== false || strpos($rawLower, 'amount') !== false) {
                        $loanAmountIdx = $index;
                    } elseif (strpos($rawLower, 'start') !== false || strpos($rawLower, 'month') !== false) {
                        $startIdx = $index;
                    }
                }
            }

            // Fallback checking by column position if not detected by headers:
            // Format: Staff ID, Amount Deduct Monthly, Balance, Start Month
            if ($staffIdIdx === -1 && $fileNoIdx === -1) $staffIdIdx = 0;
            if ($monthlyDeductIdx === -1 && $loanAmountIdx === -1) $monthlyDeductIdx = 1;
            if ($balanceIdx === -1 && count($rows[0]) >= 3) $balanceIdx = 2;
            if ($startIdx === -1 && count($rows[0]) >= 4) $startIdx = 3;

            $importedCount = 0;
            $warnings = [];

            DB::beginTransaction();

            for ($r = 1; $r < count($rows); $r++) {
                $row = $rows[$r];

                $isEmptyRow = true;
                foreach ($row as $cell) {
                    if (trim((string)$cell) !== '') {
                        $isEmptyRow = false;
                        break;
                    }
                }
                if ($isEmptyRow) continue;

                $staff = null;

                // Match by Staff ID
                if ($staffIdIdx !== -1 && isset($row[$staffIdIdx]) && trim((string)$row[$staffIdIdx]) !== '') {
                    $val = trim((string)$row[$staffIdIdx]);
                    $staff = DB::table('tblper')->where('ID', $val)->first();
                }

                // Match by File Number
                if (!$staff && $fileNoIdx !== -1 && isset($row[$fileNoIdx]) && trim((string)$row[$fileNoIdx]) !== '') {
                    $val = trim((string)$row[$fileNoIdx]);
                    $staff = DB::table('tblper')->where('fileNo', $val)->first();
                }

                // Fallback matching
                if (!$staff && $staffIdIdx !== -1 && isset($row[$staffIdIdx]) && trim((string)$row[$staffIdIdx]) !== '') {
                    $val = trim((string)$row[$staffIdIdx]);
                    $staff = DB::table('tblper')->where('fileNo', $val)->first();
                }

                if (!$staff) {
                    $warnings[] = "Row " . ($r + 1) . ": Employee ID/File Number not found.";
                    continue;
                }

                // Determine Monthly Deduction and Balance
                $monthlyDeduction = 0.00;
                $balanceRemaining = 0.00;
                $loanAmount = 0.00;
                $interestRate = 0.00;
                $durationMonths = 12;

                if ($monthlyDeductIdx !== -1 && isset($row[$monthlyDeductIdx]) && trim((string)$row[$monthlyDeductIdx]) !== '') {
                    $monthlyDeduction = (float)trim(str_replace([',', '₦', '$'], '', $row[$monthlyDeductIdx]));
                }

                if ($balanceIdx !== -1 && isset($row[$balanceIdx]) && trim((string)$row[$balanceIdx]) !== '') {
                    $balanceRemaining = (float)trim(str_replace([',', '₦', '$'], '', $row[$balanceIdx]));
                }

                if ($loanAmountIdx !== -1 && isset($row[$loanAmountIdx]) && trim((string)$row[$loanAmountIdx]) !== '') {
                    $loanAmount = (float)trim(str_replace([',', '₦', '$'], '', $row[$loanAmountIdx]));
                }

                if ($interestIdx !== -1 && isset($row[$interestIdx]) && trim((string)$row[$interestIdx]) !== '') {
                    $interestRate = (float)trim(str_replace([',', '%'], '', $row[$interestIdx]));
                }

                if ($durationIdx !== -1 && isset($row[$durationIdx]) && trim((string)$row[$durationIdx]) !== '') {
                    $durationMonths = (int)trim((string)$row[$durationIdx]);
                }

                // If monthly deduction was supplied
                if ($monthlyDeduction > 0) {
                    if ($balanceRemaining <= 0) {
                        $balanceRemaining = $loanAmount > 0 ? $loanAmount : $monthlyDeduction;
                    }
                    if ($loanAmount <= 0) {
                        $loanAmount = $balanceRemaining;
                    }
                    if ($durationMonths <= 0 || $durationIdx === -1) {
                        $durationMonths = max(1, (int)ceil($balanceRemaining / $monthlyDeduction));
                    }
                } elseif ($loanAmount > 0) {
                    // Legacy format: loan amount + interest rate + duration
                    $totalRepayment = $loanAmount * (1 + $interestRate / 100);
                    $durationMonths = $durationMonths > 0 ? $durationMonths : 12;
                    $monthlyDeduction = round($totalRepayment / $durationMonths, 2);
                    if ($balanceRemaining <= 0) {
                        $balanceRemaining = $totalRepayment;
                    }
                } else {
                    $warnings[] = "Row " . ($r + 1) . ": Invalid deduction amount or balance.";
                    continue;
                }

                // Parse start month
                $startMonth = ($startIdx !== -1 && isset($row[$startIdx])) ? trim((string)$row[$startIdx]) : '';
                if ($startMonth === '') {
                    $startMonth = date('Y-m');
                } else {
                    if (preg_match('/^\d{4}-\d{2}$/', $startMonth) === 0) {
                        if (is_numeric($startMonth)) {
                            $unixDate = ($startMonth - 25569) * 86400;
                            $startMonth = date('Y-m', $unixDate);
                        } else {
                            $parsedTime = strtotime($startMonth);
                            if ($parsedTime !== false) {
                                $startMonth = date('Y-m', $parsedTime);
                            } else {
                                $startMonth = date('Y-m');
                            }
                        }
                    }
                }

                // Calculate end month
                $endMonth = $startMonth;
                if ($startMonth && $durationMonths > 0) {
                    $parts = explode('-', $startMonth);
                    if (count($parts) === 2) {
                        $y = (int)$parts[0];
                        $m = (int)$parts[1];
                        $startDate = new \DateTime("$y-$m-01");
                        $startDate->modify("+" . ($durationMonths - 1) . " month");
                        $endMonth = $startDate->format('Y-m');
                    }
                }

                DB::table('coop_loan_deduction_setups')->updateOrInsert(
                    ['staffId' => $staff->ID],
                    [
                        'loan_amount' => $loanAmount,
                        'interest_rate' => $interestRate,
                        'duration_months' => $durationMonths,
                        'monthly_deduction' => $monthlyDeduction,
                        'balance_remaining' => $balanceRemaining,
                        'start_month' => $startMonth,
                        'end_month' => $endMonth,
                        'is_active' => 1,
                        'updated_at' => now(),
                        'created_at' => now()
                    ]
                );

                $importedCount++;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Bulk import completed. {$importedCount} cooperative loan deduction setups imported successfully.",
                'warnings' => $warnings,
                'imported_count' => $importedCount
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('CoopLoanDeductionSetupApiController import: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
