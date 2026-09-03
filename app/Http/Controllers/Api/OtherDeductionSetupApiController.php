<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class OtherDeductionSetupApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * Resolve the current user context from the X-User-Id header.
     */
    

    /**
     * GET /api/nextjs/payroll/other-deduction-setups/staff-salary/{staffId}
     * Retrieve staff monthly salary and calculate daily rate based on days in the selected month.
     */
    public function getStaffSalary(Request $request, $staffId)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $staff = DB::table('tblper')->where('ID', $staffId)->first();
            if (!$staff) {
                return response()->json(['status' => 'error', 'message' => 'Staff not found.'], 404);
            }

            $month = $request->input('month');
            $year = (int) $request->input('year', date('Y'));

            if (!empty($month) && is_numeric($month)) {
                $monthNum = (int)$month;
            } elseif (!empty($month)) {
                $monthNum = (int) date('m', strtotime("1 {$month} {$year}"));
            } else {
                $monthNum = (int) date('m');
            }

            $daysInMonth = (int) date('t', strtotime(sprintf("%04d-%02d-01", $year, $monthNum)));
            if ($daysInMonth < 28 || $daysInMonth > 31) {
                $daysInMonth = 30;
            }

            $struct = DB::table('salary_structures')->where('staffId', $staffId)->first();
            $monthlySalary = 0.00;
            if ($struct) {
                $monthlySalary = (float)$struct->basic_salary +
                                 (float)$struct->housing_allowance +
                                 (float)$struct->transport_allowance +
                                 (float)$struct->medical_allowance +
                                 (float)$struct->utility_allowance +
                                 (float)$struct->meal_allowance;
                if ($monthlySalary <= 0 && (float)$struct->declare_salary > 0) {
                    $monthlySalary = (float)$struct->declare_salary;
                }
            }

            if ($monthlySalary <= 0) {
                $firstStruct = DB::table('first_salary_structure')->where('staffId', $staffId)->first();
                if ($firstStruct) {
                    $monthlySalary = (float)$firstStruct->basic_salary +
                                     (float)$firstStruct->housing_allowance +
                                     (float)$firstStruct->transport_allowance +
                                     (float)$firstStruct->medical_allowance +
                                     (float)$firstStruct->utility_allowance +
                                     (float)$firstStruct->meal_allowance;
                }
            }

            $dailySalary = round($monthlySalary / (float)$daysInMonth, 2);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'staff_id' => $staff->ID,
                    'file_no' => $staff->fileNo ?? '',
                    'name' => trim("{$staff->surname} {$staff->first_name} {$staff->othernames}"),
                    'monthly_salary' => $monthlySalary,
                    'days_in_month' => $daysInMonth,
                    'daily_salary' => $dailySalary,
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('OtherDeductionSetupApiController getStaffSalary: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/other-deduction-setups
     * Fetch existing setups.
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $search = trim($request->input('search', ''));
            $query = DB::table('other_deduction_setups as ods')
                ->join('tblper as p', 'p.ID', '=', 'ods.staffId')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->select(
                    'ods.*',
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
                      ->orWhere('p.othernames', 'like', "%{$search}%")
                      ->orWhere('ods.remarks', 'like', "%{$search}%");
                });
            }

            $employee = $ctx['employee'];

            $records = $query->orderBy('ods.id', 'desc')->get()->map(function ($row) {
                $row->name = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                $row->is_active = (int) $row->is_active;
                $row->calculation_mode = $row->calculation_mode ?? 'amount';
                $row->deduction_days = $row->deduction_days !== null ? (float)$row->deduction_days : null;
                $row->daily_rate = $row->daily_rate !== null ? (float)$row->daily_rate : null;
                $row->monthly_salary = $row->monthly_salary !== null ? (float)$row->monthly_salary : null;
                $row->days_in_month = $row->days_in_month !== null ? (int)$row->days_in_month : null;
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
            Log::error('OtherDeductionSetupApiController index: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/other-deduction-setups
     * Save or update an other deduction setup.
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
                'calculation_mode' => 'nullable|string|in:amount,days',
                'deduction_type' => 'required|string|in:one_time,spread',
                'deduction_days' => 'nullable|numeric|min:0.01',
                'daily_rate' => 'nullable|numeric|min:0',
                'monthly_salary' => 'nullable|numeric|min:0',
                'days_in_month' => 'nullable|integer|min:28|max:31',
                'total_amount' => 'required|numeric|min:0',
                'duration_months' => 'nullable|integer|min:1',
                'monthly_deduction' => 'required|numeric|min:0',
                'balance_remaining' => 'nullable|numeric|min:0',
                'start_month' => 'required|string|regex:/^\d{4}-\d{2}$/',
                'end_month' => 'nullable|string|regex:/^\d{4}-\d{2}$/',
                'remarks' => 'required|string|min:2|max:1000',
                'is_active' => 'nullable|integer|in:0,1',
            ]);

            $id = $validated['id'] ?? null;
            $calculationMode = $validated['calculation_mode'] ?? 'amount';
            $totalAmount = (float) $validated['total_amount'];
            $deductionType = $validated['deduction_type'];

            if ($calculationMode === 'days') {
                $deductionType = 'one_time';
                $durationMonths = 1;
                $monthlyDeduction = $totalAmount;
                $endMonth = $validated['start_month'];
            } elseif ($deductionType === 'one_time') {
                $durationMonths = 1;
                $monthlyDeduction = $totalAmount;
                $endMonth = $validated['start_month'];
            } else {
                $durationMonths = (int) ($validated['duration_months'] ?? 1);
                $monthlyDeduction = (float) $validated['monthly_deduction'];
                $endMonth = $validated['end_month'];
            }

            $balanceRemaining = (isset($validated['balance_remaining']) && (float)$validated['balance_remaining'] > 0)
                ? (float) $validated['balance_remaining']
                : $totalAmount;

            // Check if deduction amount causes net pay to become negative
            $startMonthStr = $validated['start_month'];
            $monthParts = explode('-', $startMonthStr);
            $startYear = (int)$monthParts[0];
            $startMonthNum = (int)$monthParts[1];

            $breakdownCtrl = app(\App\Http\Controllers\Api\SalaryBreakdownApiController::class);
            $adminUserId = DB::table('users')->where('is_global', 1)->value('id') ?? ($ctx['userId'] ?? 10018);
            $breakdownReq = Request::create("/api/nextjs/payroll/salary-breakdown?staff_id={$validated['staffId']}&month={$startMonthNum}&year={$startYear}", 'GET', [], [], [], [
                'HTTP_X_USER_ID' => $adminUserId,
                'HTTP_X_INTERNAL_CALL' => '1',
            ]);
            $breakdownRes = $breakdownCtrl->getBreakdown($breakdownReq);
            $breakdownData = json_decode($breakdownRes->getContent(), true);

            if (isset($breakdownData['status']) && $breakdownData['status'] === 'success' && isset($breakdownData['summary'])) {
                $currentNetPay = (float)($breakdownData['summary']['net_pay'] ?? 0.00);

                // If editing existing setup, restore the existing deduction amount so we evaluate against baseline
                if ($id) {
                    $existingSetup = DB::table('other_deduction_setups')->where('id', $id)->first();
                    if ($existingSetup && $existingSetup->is_active == 1) {
                        $existingAmt = (float)$existingSetup->monthly_deduction;
                        $currentNetPay += $existingAmt;
                    }
                }

                if ($monthlyDeduction > $currentNetPay) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Deduction declined: Monthly deduction of ₦" . number_format($monthlyDeduction, 2) . " exceeds staff member's available Net Pay (₦" . number_format($currentNetPay, 2) . "). Net pay cannot be negative."
                    ], 422);
                }
            }

            $data = [
                'staffId' => $validated['staffId'],
                'calculation_mode' => $calculationMode,
                'deduction_type' => $deductionType,
                'deduction_days' => $calculationMode === 'days' && isset($validated['deduction_days']) ? (float)$validated['deduction_days'] : null,
                'daily_rate' => $calculationMode === 'days' && isset($validated['daily_rate']) ? (float)$validated['daily_rate'] : null,
                'monthly_salary' => $calculationMode === 'days' && isset($validated['monthly_salary']) ? (float)$validated['monthly_salary'] : null,
                'days_in_month' => $calculationMode === 'days' && isset($validated['days_in_month']) ? (int)$validated['days_in_month'] : null,
                'total_amount' => $totalAmount,
                'duration_months' => $durationMonths,
                'monthly_deduction' => $monthlyDeduction,
                'balance_remaining' => $balanceRemaining,
                'start_month' => $validated['start_month'],
                'end_month' => $endMonth,
                'remarks' => $validated['remarks'] ?? null,
                'is_active' => $validated['is_active'] ?? 1,
                'updated_at' => now(),
            ];

            if ($id) {
                $exists = DB::table('other_deduction_setups')->where('id', $id)->exists();
                if (!$exists) {
                    return response()->json(['status' => 'error', 'message' => 'Deduction setup not found.'], 404);
                }
                DB::table('other_deduction_setups')->where('id', $id)->update($data);
                $message = 'Other deduction setup updated successfully.';
            } else {
                $data['created_at'] = now();
                DB::table('other_deduction_setups')->insert($data);
                $message = 'Other deduction setup created successfully.';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message
            ]);
        } catch (\Throwable $th) {
            Log::error('OtherDeductionSetupApiController store: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/other-deduction-setups/toggle/{id}
     * Toggle status.
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }



            $setup = DB::table('other_deduction_setups')->where('id', $id)->first();
            if (!$setup) {
                return response()->json(['status' => 'error', 'message' => 'Setup not found.'], 404);
            }

            $newStatus = $setup->is_active == 1 ? 0 : 1;

            DB::table('other_deduction_setups')->where('id', $id)->update([
                'is_active' => $newStatus,
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => $newStatus == 1 ? 'Deduction setup activated successfully.' : 'Deduction setup deactivated successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('OtherDeductionSetupApiController toggleStatus: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/nextjs/payroll/other-deduction-setups/{id}
     * Delete setup configuration.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }



            $exists = DB::table('other_deduction_setups')->where('id', $id)->exists();
            if (!$exists) {
                return response()->json(['status' => 'error', 'message' => 'Setup not found.'], 404);
            }

            DB::table('other_deduction_setups')->where('id', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Other deduction setup deleted successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('OtherDeductionSetupApiController destroy: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/other-deduction-setups/template
     * Download public CSV template.
     */
    public function downloadTemplate(Request $request)
    {
        try {
            $headers = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="other_deduction_import_template.csv"',
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Expires'             => '0',
            ];

            $columns = ['Staff ID', 'Deduction Type (one_time/spread)', 'Total Amount', 'Duration Months', 'Start Month (YYYY-MM)', 'Remarks'];
            $exampleRow = ['1024', 'spread', '45000.00', '3', '2026-06', 'Staff uniform and ID card replacement'];

            $callback = function () use ($columns, $exampleRow) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $columns);
                fputcsv($handle, $exampleRow);
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Throwable $th) {
            Log::error('OtherDeductionSetupApiController downloadTemplate: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/other-deduction-setups/import
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
            $typeIdx = -1;
            $amountIdx = -1;
            $durationIdx = -1;
            $startIdx = -1;
            $remarksIdx = -1;

            foreach ($headers as $index => $header) {
                if (strpos($header, 'staff id') !== false || strpos($header, 'staff_id') !== false || $header === 'id' || $header === 'staffid') {
                    $staffIdIdx = $index;
                } elseif (strpos($header, 'file') !== false || $header === 'fileno' || $header === 'file_no') {
                    $fileNoIdx = $index;
                } elseif (strpos($header, 'type') !== false || strpos($header, 'deduction') !== false) {
                    $typeIdx = $index;
                } elseif (strpos($header, 'amount') !== false || strpos($header, 'total') !== false || strpos($header, 'deduct') !== false) {
                    $amountIdx = $index;
                } elseif (strpos($header, 'duration') !== false || strpos($header, 'month') !== false) {
                    $durationIdx = $index;
                } elseif (strpos($header, 'start') !== false || strpos($header, 'period') !== false) {
                    $startIdx = $index;
                } elseif (strpos($header, 'remark') !== false || strpos($header, 'note') !== false || strpos($header, 'desc') !== false || strpos($header, 'reason') !== false) {
                    $remarksIdx = $index;
                }
            }

            // Fallback checking by column position
            if ($staffIdIdx === -1 && $fileNoIdx === -1) $staffIdIdx = 0;
            if ($typeIdx === -1) $typeIdx = 1;
            if ($amountIdx === -1) $amountIdx = 2;
            if ($durationIdx === -1) $durationIdx = 3;
            if ($startIdx === -1) $startIdx = 4;
            if ($remarksIdx === -1 && isset($headers[5])) $remarksIdx = 5;

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

                // Parse deduction type
                $deductionType = isset($row[$typeIdx]) ? strtolower(trim($row[$typeIdx])) : 'one_time';
                if (!in_array($deductionType, ['one_time', 'spread'])) {
                    $deductionType = 'one_time';
                }

                // Parse total amount
                $totalAmount = isset($row[$amountIdx]) ? (float)trim(str_replace([',', '₦', '$'], '', $row[$amountIdx])) : 0.00;
                if ($totalAmount <= 0) {
                    $warnings[] = "Row " . ($r + 1) . ": Invalid total amount.";
                    continue;
                }

                // Parse duration months
                if ($deductionType === 'spread') {
                    $durationMonths = isset($row[$durationIdx]) ? (int)trim($row[$durationIdx]) : 1;
                    if ($durationMonths <= 0) {
                        $durationMonths = 1;
                    }
                } else {
                    $durationMonths = 1;
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

                // Parse remarks
                $remarks = ($remarksIdx !== -1 && isset($row[$remarksIdx])) ? trim((string)$row[$remarksIdx]) : null;

                // Calculate end month & monthly deduction
                if ($deductionType === 'one_time') {
                    $monthlyDeduction = $totalAmount;
                    $endMonth = $startMonth;
                } else {
                    $monthlyDeduction = round($totalAmount / $durationMonths, 2);
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
                }

                DB::table('other_deduction_setups')->updateOrInsert(
                    ['staffId' => $staff->ID],
                    [
                        'deduction_type' => $deductionType,
                        'total_amount' => $totalAmount,
                        'duration_months' => $durationMonths,
                        'monthly_deduction' => $monthlyDeduction,
                        'balance_remaining' => $totalAmount,
                        'start_month' => $startMonth,
                        'end_month' => $endMonth,
                        'remarks' => $remarks ?: null,
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
                'message' => "Bulk import completed. {$importedCount} configurations imported successfully.",
                'warnings' => $warnings,
                'imported_count' => $importedCount
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('OtherDeductionSetupApiController import: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
