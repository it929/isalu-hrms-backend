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

            if ($ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || $ctx['isAuditStaff']) {
                // Admins/Audit see all setups
            } elseif ($employee && $employee->is_hod == 1) {
                // HOD sees department staff setups
                $query->where('p.departmentID', $employee->departmentID);
            } elseif ($employee) {
                // Regular staff see only their own
                $query->where('clds.staffId', $employee->ID);
            } else {
                $query->where('clds.id', 0); // fallback empty
            }

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

            // Restriction check: Only Admins can modify settings
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access denied: Only administrators can configure cooperative loan deduction setups.'
                ], 403);
            }

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
                $exists = DB::table('coop_loan_deduction_setups')->where('id', $id)->exists();
                if (!$exists) {
                    return response()->json(['status' => 'error', 'message' => 'Deduction setup not found.'], 404);
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

            // Only Admins can modify settings
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access denied: Only administrators can toggle setup status.'
                ], 403);
            }

            $setup = DB::table('coop_loan_deduction_setups')->where('id', $id)->first();
            if (!$setup) {
                return response()->json(['status' => 'error', 'message' => 'Setup not found.'], 404);
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

            // Only Admins can delete
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access denied: Only administrators can delete setups.'
                ], 403);
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

            $columns = ['Staff ID', 'Loan Amount', 'Interest Rate (%)', 'Duration Months', 'Start Month (YYYY-MM)'];
            $exampleRow = ['9', '500000.00', '6', '12', '2026-05'];

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

            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access denied: Only administrators can import settings.'
                ], 403);
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
            $headers = array_map(function ($h) {
                return strtolower(trim((string)$h));
            }, $rows[0]);

            $staffIdIdx = -1;
            $fileNoIdx = -1;
            $amountIdx = -1;
            $interestIdx = -1;
            $durationIdx = -1;
            $startIdx = -1;

            foreach ($headers as $index => $header) {
                if (strpos($header, 'staff id') !== false || strpos($header, 'staff_id') !== false || $header === 'id' || $header === 'staffid') {
                    $staffIdIdx = $index;
                } elseif (strpos($header, 'file') !== false || $header === 'fileno' || $header === 'file_no') {
                    $fileNoIdx = $index;
                } elseif (strpos($header, 'amount') !== false || strpos($header, 'loan') !== false) {
                    $amountIdx = $index;
                } elseif (strpos($header, 'interest') !== false || strpos($header, 'rate') !== false) {
                    $interestIdx = $index;
                } elseif (strpos($header, 'start') !== false || strpos($header, 'period') !== false) {
                    $startIdx = $index;
                } elseif (strpos($header, 'duration') !== false || strpos($header, 'month') !== false) {
                    $durationIdx = $index;
                }
            }

            // Fallback checking by column position
            if ($staffIdIdx === -1 && $fileNoIdx === -1) $staffIdIdx = 0;
            if ($amountIdx === -1) $amountIdx = 1;
            if ($interestIdx === -1) $interestIdx = 2;
            if ($durationIdx === -1) $durationIdx = 3;
            if ($startIdx === -1) $startIdx = 4;

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
                if ($staffIdIdx !== -1 && isset($row[$staffIdIdx]) && trim($row[$staffIdIdx]) !== '') {
                    $val = trim($row[$staffIdIdx]);
                    $staff = DB::table('tblper')->where('ID', $val)->first();
                }

                // Match by File Number
                if (!$staff && $fileNoIdx !== -1 && isset($row[$fileNoIdx]) && trim($row[$fileNoIdx]) !== '') {
                    $val = trim($row[$fileNoIdx]);
                    $staff = DB::table('tblper')->where('fileNo', $val)->first();
                }

                // Fallback matching
                if (!$staff && $staffIdIdx !== -1 && isset($row[$staffIdIdx]) && trim($row[$staffIdIdx]) !== '') {
                    $val = trim($row[$staffIdIdx]);
                    $staff = DB::table('tblper')->where('fileNo', $val)->first();
                }

                if (!$staff) {
                    $warnings[] = "Row " . ($r + 1) . ": Employee ID/File Number not found.";
                    continue;
                }

                // Parse loan amount
                $loanAmount = isset($row[$amountIdx]) ? (float)trim(str_replace([',', '₦', '$'], '', $row[$amountIdx])) : 0.00;
                if ($loanAmount <= 0) {
                    $warnings[] = "Row " . ($r + 1) . ": Invalid loan amount.";
                    continue;
                }

                // Parse interest rate
                $interestRate = ($interestIdx !== -1 && isset($row[$interestIdx])) ? (float)trim($row[$interestIdx]) : 0.00;
                if ($interestRate < 0 || $interestRate > 100) {
                    $interestRate = 0.00;
                }

                // Parse duration months
                $durationMonths = isset($row[$durationIdx]) ? (int)trim($row[$durationIdx]) : 12;
                if ($durationMonths <= 0) {
                    $warnings[] = "Row " . ($r + 1) . ": Invalid duration months.";
                    continue;
                }

                // Parse start month
                $startMonth = isset($row[$startIdx]) ? trim($row[$startIdx]) : '';
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

                $totalRepayment = $loanAmount * (1 + $interestRate / 100);
                $monthlyDeduction = round($totalRepayment / $durationMonths, 2);

                DB::table('coop_loan_deduction_setups')->updateOrInsert(
                    ['staffId' => $staff->ID],
                    [
                        'loan_amount' => $loanAmount,
                        'interest_rate' => $interestRate,
                        'duration_months' => $durationMonths,
                        'monthly_deduction' => $monthlyDeduction,
                        'balance_remaining' => $totalRepayment,
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
