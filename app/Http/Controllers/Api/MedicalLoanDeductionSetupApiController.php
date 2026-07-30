<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class MedicalLoanDeductionSetupApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * Resolve the current user context from the X-User-Id header.
     */
    

    /**
     * GET /api/nextjs/payroll/medical-loan-deduction-setups
     * Fetch existing medical loan setups.
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $search = trim($request->input('search', ''));
            $query = DB::table('medical_loan_deduction_setups as mlds')
                ->join('tblper as p', 'p.ID', '=', 'mlds.staffId')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->select(
                    'mlds.*',
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
                // Admins see all setups
            } elseif ($employee && $employee->is_hod == 1) {
                // HOD sees department staff setups
                $query->where('p.departmentID', $employee->departmentID);
            } elseif ($employee) {
                // Regular staff see only their own
                $query->where('mlds.staffId', $employee->ID);
            } else {
                $query->where('mlds.id', 0); // fallback empty
            }

            $records = $query->orderBy('mlds.id', 'desc')->get()->map(function ($row) {
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
            Log::error('MedicalLoanDeductionSetupApiController index: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/medical-loan-deduction-setups
     * Save or update a medical loan deduction setup.
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
                    'message' => 'Access denied: Only administrators can configure medical loan deduction setups.'
                ], 403);
            }

            $id = $validated['id'] ?? null;
            $loanAmount = (float) $validated['loan_amount'];
            $balanceRemaining = isset($validated['balance_remaining']) ? (float) $validated['balance_remaining'] : $loanAmount;

            $durationMonths = (int) $validated['duration_months'];
            $monthlyDeduction = (float) $validated['monthly_deduction'];

            // Recalculate end_month matching the calculated duration
            $startMonth = $validated['start_month'];
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

            $data = [
                'staffId' => $validated['staffId'],
                'loan_amount' => $loanAmount,
                'duration_months' => $durationMonths,
                'monthly_deduction' => $monthlyDeduction,
                'balance_remaining' => $balanceRemaining,
                'start_month' => $startMonth,
                'end_month' => $endMonth,
                'is_active' => $validated['is_active'] ?? 1,
                'updated_at' => now(),
            ];

            if ($id) {
                $exists = DB::table('medical_loan_deduction_setups')->where('id', $id)->exists();
                if (!$exists) {
                    return response()->json(['status' => 'error', 'message' => 'Deduction setup not found.'], 404);
                }
                DB::table('medical_loan_deduction_setups')->where('id', $id)->update($data);
                $message = 'Medical loan deduction setup updated successfully.';
            } else {
                $data['created_at'] = now();
                DB::table('medical_loan_deduction_setups')->insert($data);
                $message = 'Medical loan deduction setup created successfully.';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message
            ]);
        } catch (\Throwable $th) {
            Log::error('MedicalLoanDeductionSetupApiController store: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/medical-loan-deduction-setups/toggle/{id}
     * Toggle status.
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access denied: Only administrators can toggle setup status.'
                ], 403);
            }

            $setup = DB::table('medical_loan_deduction_setups')->where('id', $id)->first();
            if (!$setup) {
                return response()->json(['status' => 'error', 'message' => 'Setup not found.'], 404);
            }

            $newStatus = $setup->is_active == 1 ? 0 : 1;

            DB::table('medical_loan_deduction_setups')->where('id', $id)->update([
                'is_active' => $newStatus,
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => $newStatus == 1 ? 'Deduction setup activated successfully.' : 'Deduction setup deactivated successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('MedicalLoanDeductionSetupApiController toggleStatus: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/nextjs/payroll/medical-loan-deduction-setups/{id}
     * Delete setup configuration.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access denied: Only administrators can delete setups.'
                ], 403);
            }

            $exists = DB::table('medical_loan_deduction_setups')->where('id', $id)->exists();
            if (!$exists) {
                return response()->json(['status' => 'error', 'message' => 'Setup not found.'], 404);
            }

            DB::table('medical_loan_deduction_setups')->where('id', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Medical loan deduction setup deleted successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('MedicalLoanDeductionSetupApiController destroy: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/medical-loan-deduction-setups/template
     * Download public CSV template.
     */
    public function downloadTemplate(Request $request)
    {
        try {
            $headers = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="medical_loan_import_template.csv"',
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Expires'             => '0',
            ];

            $columns = ['Staff ID', 'Loan Amount', 'Duration Months', 'Start Month (YYYY-MM)'];
            $exampleRow = ['1024', '120000.00', '12', '2026-06'];

            $callback = function () use ($columns, $exampleRow) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $columns);
                fputcsv($handle, $exampleRow);
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Throwable $th) {
            Log::error('MedicalLoanDeductionSetupApiController downloadTemplate: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/medical-loan-deduction-setups/import
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
            $rows = Excel::toArray([], $file)[0];

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
            $durationIdx = -1;
            $startIdx = -1;

            foreach ($headers as $index => $header) {
                if (strpos($header, 'staff id') !== false || strpos($header, 'staff_id') !== false || $header === 'id' || $header === 'staffid') {
                    $staffIdIdx = $index;
                } elseif (strpos($header, 'file') !== false || $header === 'fileno' || $header === 'file_no') {
                    $fileNoIdx = $index;
                } elseif (strpos($header, 'amount') !== false || strpos($header, 'loan') !== false) {
                    $amountIdx = $index;
                } elseif (strpos($header, 'duration') !== false || strpos($header, 'month') !== false) {
                    $durationIdx = $index;
                } elseif (strpos($header, 'start') !== false || strpos($header, 'period') !== false) {
                    $startIdx = $index;
                }
            }

            // Fallback checking by column position
            if ($staffIdIdx === -1 && $fileNoIdx === -1) $staffIdIdx = 0;
            if ($amountIdx === -1) $amountIdx = 1;
            if ($durationIdx === -1) $durationIdx = 2;
            if ($startIdx === -1) $startIdx = 3;

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

                // Recalculate duration and monthly deduction based on new rules
                $calc = $this->calculateDeductionAndDuration($loanAmount);
                $durationMonths = $calc['duration_months'];
                $monthlyDeduction = $calc['monthly_deduction'];

                // Calculate end month matching the calculated duration
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

                DB::table('medical_loan_deduction_setups')->updateOrInsert(
                    ['staffId' => $staff->ID],
                    [
                        'loan_amount' => $loanAmount,
                        'duration_months' => $durationMonths,
                        'monthly_deduction' => $monthlyDeduction,
                        'balance_remaining' => $loanAmount,
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
                'message' => "Bulk import completed. {$importedCount} medical loan setups imported successfully.",
                'warnings' => $warnings,
                'imported_count' => $importedCount
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('MedicalLoanDeductionSetupApiController import: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Helper to compute dynamic deduction and duration months based on loan amount.
     */
    private function calculateDeductionAndDuration($loanAmount)
    {
        $loanAmount = (float)$loanAmount;
        if ($loanAmount <= 0) {
            return ['monthly_deduction' => 0.00, 'duration_months' => 0];
        }

        $deduction = 0.00;
        if ($loanAmount < 5000) {
            $deduction = $loanAmount;
        } elseif ($loanAmount >= 5000 && $loanAmount <= 10000) {
            $deduction = 5000;
        } elseif ($loanAmount > 10000 && $loanAmount <= 30000) {
            $deduction = 10000;
        } elseif ($loanAmount > 30000 && $loanAmount <= 60000) {
            $deduction = 15000;
        } elseif ($loanAmount > 60000 && $loanAmount <= 120000) {
            $deduction = 20000;
        } elseif ($loanAmount > 120000 && $loanAmount <= 160000) {
            $deduction = 25000;
        } elseif ($loanAmount > 160000 && $loanAmount <= 300000) {
            $deduction = 30000;
        } elseif ($loanAmount > 300000 && $loanAmount <= 600000) {
            $deduction = 35000;
        } else {
            $deduction = 50000;
        }

        $tempBal = $loanAmount;
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
        }

        return [
            'monthly_deduction' => $deduction,
            'duration_months' => $durationMonths
        ];
    }
}
