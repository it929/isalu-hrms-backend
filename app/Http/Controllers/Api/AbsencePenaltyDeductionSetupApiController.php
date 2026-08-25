<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;



class AbsencePenaltyDeductionSetupApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * Resolve the current user context from the X-User-Id header.
     */
    

    /**
     * GET /api/nextjs/payroll/absence-penalty-deduction-setups/staff-salary/{staffId}
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
                    'penalty_multiplier' => 3,
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('AbsencePenaltyDeductionSetupApiController getStaffSalary: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/absence-penalty-deduction-setups
     * Fetch existing configurations.
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $search = trim($request->input('search', ''));
            $query = DB::table('absence_penalty_deduction_setups as apds')
                ->join('tblper as p', 'p.ID', '=', 'apds.staffId')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->select(
                    'apds.*',
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
                      ->orWhere('apds.remarks', 'like', "%{$search}%");
                });
            }

            $employee = $ctx['employee'];

            $records = $query->orderBy('apds.id', 'desc')->get()->map(function ($row) {
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
            Log::error('AbsencePenaltyDeductionSetupApiController index: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/absence-penalty-deduction-setups
     * Save or update an absence penalty deduction setup.
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
                'month' => 'nullable|string',
                'year' => 'nullable',
                'absent_days' => 'required|integer|min:1',
                'start_month' => 'nullable|string|regex:/^\d{4}-\d{2}$/',
                'end_month' => 'nullable|string|regex:/^\d{4}-\d{2}$/',
                'deduction_type' => 'nullable|string|in:one_time,spread',
                'penalty_multiplier' => 'nullable|integer|min:1',
                'penalty_days' => 'nullable|integer|min:1',
                'daily_salary' => 'nullable|numeric|min:0',
                'monthly_salary' => 'nullable|numeric|min:0',
                'total_amount' => 'nullable|numeric|min:0',
                'duration_months' => 'nullable|integer|min:1',
                'monthly_deduction' => 'nullable|numeric|min:0',
                'balance_remaining' => 'nullable|numeric|min:0',
                'remarks' => 'nullable|string|max:1000',
                'is_active' => 'nullable|integer|in:0,1',
            ]);

            $id = $validated['id'] ?? null;
            $staffId = $validated['staffId'];
            $absentDays = (int)$validated['absent_days'];
            $multiplier = (isset($validated['penalty_multiplier']) && (int)$validated['penalty_multiplier'] > 0)
                ? (int)$validated['penalty_multiplier']
                : 3;
            $penaltyDays = $absentDays * $multiplier;

            // Determine start_month (YYYY-MM)
            $startMonth = $validated['start_month'] ?? null;
            if (!$startMonth && !empty($validated['month']) && !empty($validated['year'])) {
                $m = $validated['month'];
                $y = (int)$validated['year'];
                if (is_numeric($m)) {
                    $startMonth = sprintf("%04d-%02d", $y, (int)$m);
                } else {
                    $parsedTime = strtotime("1 {$m} {$y}");
                    if ($parsedTime !== false) {
                        $startMonth = date('Y-m', $parsedTime);
                    }
                }
            }
            if (!$startMonth) {
                $startMonth = date('Y-m');
            }

            // Fetch or calculate daily & monthly salary if not provided
            $dailySalary = isset($validated['daily_salary']) ? (float)$validated['daily_salary'] : 0.0;
            $monthlySalary = isset($validated['monthly_salary']) ? (float)$validated['monthly_salary'] : 0.0;

            if ($dailySalary <= 0 || $monthlySalary <= 0) {
                $struct = DB::table('salary_structures')->where('staffId', $staffId)->first();
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
                $daysInMonth = (int) date('t', strtotime("{$startMonth}-01"));
                if ($daysInMonth < 28 || $daysInMonth > 31) {
                    $daysInMonth = 30;
                }
                $dailySalary = round($monthlySalary / (float)$daysInMonth, 2);
            }

            $totalAmount = (isset($validated['total_amount']) && (float)$validated['total_amount'] > 0)
                ? (float)$validated['total_amount']
                : round($dailySalary * $penaltyDays, 2);

            $deductionType = $validated['deduction_type'] ?? 'one_time';
            $durationMonths = (int)($validated['duration_months'] ?? 1);
            $monthlyDeduction = (isset($validated['monthly_deduction']) && (float)$validated['monthly_deduction'] > 0)
                ? (float)$validated['monthly_deduction']
                : $totalAmount;
            $balanceRemaining = (isset($validated['balance_remaining']) && (float)$validated['balance_remaining'] > 0)
                ? (float)$validated['balance_remaining']
                : $totalAmount;
            $endMonth = $validated['end_month'] ?? $startMonth;

            $data = [
                'staffId' => $staffId,
                'deduction_type' => $deductionType,
                'absent_days' => $absentDays,
                'penalty_multiplier' => $multiplier,
                'penalty_days' => $penaltyDays,
                'daily_salary' => $dailySalary,
                'monthly_salary' => $monthlySalary,
                'total_amount' => $totalAmount,
                'duration_months' => $durationMonths,
                'monthly_deduction' => $monthlyDeduction,
                'balance_remaining' => $balanceRemaining,
                'start_month' => $startMonth,
                'end_month' => $endMonth,
                'remarks' => $validated['remarks'] ?? null,
                'is_active' => $validated['is_active'] ?? 1,
                'updated_at' => now(),
            ];

            if ($id) {
                $exists = DB::table('absence_penalty_deduction_setups')->where('id', $id)->exists();
                if (!$exists) {
                    return response()->json(['status' => 'error', 'message' => 'Deduction setup not found.'], 404);
                }
                DB::table('absence_penalty_deduction_setups')->where('id', $id)->update($data);
                $message = 'Absence penalty setup updated successfully.';
            } else {
                $data['created_at'] = now();
                DB::table('absence_penalty_deduction_setups')->insert($data);
                $message = 'Absence penalty setup created successfully.';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message
            ]);
        } catch (\Throwable $th) {
            Log::error('AbsencePenaltyDeductionSetupApiController store: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/absence-penalty-deduction-setups/toggle/{id}
     * Toggle status.
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }



            $setup = DB::table('absence_penalty_deduction_setups')->where('id', $id)->first();
            if (!$setup) {
                return response()->json(['status' => 'error', 'message' => 'Setup not found.'], 404);
            }

            $newStatus = $setup->is_active == 1 ? 0 : 1;

            DB::table('absence_penalty_deduction_setups')->where('id', $id)->update([
                'is_active' => $newStatus,
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => $newStatus == 1 ? 'Absence penalty setup activated successfully.' : 'Absence penalty setup deactivated successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('AbsencePenaltyDeductionSetupApiController toggleStatus: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/nextjs/payroll/absence-penalty-deduction-setups/{id}
     * Delete setup configuration.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }



            $exists = DB::table('absence_penalty_deduction_setups')->where('id', $id)->exists();
            if (!$exists) {
                return response()->json(['status' => 'error', 'message' => 'Setup not found.'], 404);
            }

            DB::table('absence_penalty_deduction_setups')->where('id', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Absence penalty setup deleted successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('AbsencePenaltyDeductionSetupApiController destroy: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/absence-penalty-deduction-setups/template
     * Download public CSV template.
     */
    public function downloadTemplate(Request $request)
    {
        try {
            $headers = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="absence_penalty_import_template.csv"',
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Expires'             => '0',
            ];

            $columns = ['Staff ID', 'Days Absent', 'Deduction Type (one_time/spread)', 'Total Amount (Optional if Days Absent is provided)', 'Duration Months', 'Start Month (YYYY-MM)', 'Remarks'];
            $exampleRow = ['1024', '1', 'one_time', '', '1', '2026-06', 'Absent 1 day without permission (3 days salary deduction)'];

            $callback = function () use ($columns, $exampleRow) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $columns);
                fputcsv($handle, $exampleRow);
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Throwable $th) {
            Log::error('AbsencePenaltyDeductionSetupApiController downloadTemplate: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/absence-penalty-deduction-setups/import
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
            $absentDaysIdx = -1;
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
                } elseif (strpos($header, 'absent') !== false || strpos($header, 'days absent') !== false || strpos($header, 'day') !== false) {
                    $absentDaysIdx = $index;
                } elseif (strpos($header, 'type') !== false || strpos($header, 'deduction') !== false) {
                    $typeIdx = $index;
                } elseif (strpos($header, 'amount') !== false || strpos($header, 'total') !== false || strpos($header, 'penalty') !== false) {
                    $amountIdx = $index;
                } elseif (strpos($header, 'duration') !== false || strpos($header, 'month') !== false) {
                    $durationIdx = $index;
                } elseif (strpos($header, 'start') !== false || strpos($header, 'period') !== false) {
                    $startIdx = $index;
                } elseif (strpos($header, 'remark') !== false || strpos($header, 'reason') !== false || strpos($header, 'note') !== false || strpos($header, 'comment') !== false) {
                    $remarksIdx = $index;
                }
            }

            // Fallback checking by column position
            if ($staffIdIdx === -1 && $fileNoIdx === -1) $staffIdIdx = 0;
            if ($absentDaysIdx === -1) $absentDaysIdx = 1;
            if ($typeIdx === -1) $typeIdx = 2;
            if ($amountIdx === -1) $amountIdx = 3;
            if ($durationIdx === -1) $durationIdx = 4;
            if ($startIdx === -1) $startIdx = 5;
            if ($remarksIdx === -1 && count($headers) > 6) $remarksIdx = 6;

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

                // Parse absent days
                $absentDays = ($absentDaysIdx !== -1 && isset($row[$absentDaysIdx]) && is_numeric(trim($row[$absentDaysIdx])))
                    ? (int)trim($row[$absentDaysIdx])
                    : null;

                // Parse deduction type
                $deductionType = ($typeIdx !== -1 && isset($row[$typeIdx])) ? strtolower(trim($row[$typeIdx])) : 'one_time';
                if (!in_array($deductionType, ['one_time', 'spread'])) {
                    $deductionType = 'one_time';
                }

                // Calculate or fetch staff monthly and daily salary
                $struct = DB::table('salary_structures')->where('staffId', $staff->ID)->first();
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
                    $firstStruct = DB::table('first_salary_structure')->where('staffId', $staff->ID)->first();
                    if ($firstStruct) {
                        $monthlySalary = (float)$firstStruct->basic_salary +
                                         (float)$firstStruct->housing_allowance +
                                         (float)$firstStruct->transport_allowance +
                                         (float)$firstStruct->medical_allowance +
                                         (float)$firstStruct->utility_allowance +
                                         (float)$firstStruct->meal_allowance;
                    }
                }
                $dailySalary = round($monthlySalary / 30.0, 2);

                $multiplier = 3;
                $penaltyDays = $absentDays ? ($absentDays * $multiplier) : null;

                // Parse total amount
                $totalAmount = ($amountIdx !== -1 && isset($row[$amountIdx])) ? (float)trim(str_replace([',', '₦', '$'], '', $row[$amountIdx])) : 0.00;
                if ($totalAmount <= 0 && $absentDays && $absentDays > 0) {
                    $totalAmount = round($dailySalary * $penaltyDays, 2);
                }

                if ($totalAmount <= 0) {
                    $warnings[] = "Row " . ($r + 1) . ": Invalid total amount and no absent days provided.";
                    continue;
                }

                // Parse duration months
                if ($deductionType === 'spread') {
                    $durationMonths = ($durationIdx !== -1 && isset($row[$durationIdx])) ? (int)trim($row[$durationIdx]) : 1;
                    if ($durationMonths <= 0) {
                        $durationMonths = 1;
                    }
                } else {
                    $durationMonths = 1;
                }

                // Parse start month
                $startMonth = ($startIdx !== -1 && isset($row[$startIdx])) ? trim($row[$startIdx]) : '';
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

                $remarks = ($remarksIdx !== -1 && isset($row[$remarksIdx])) ? trim($row[$remarksIdx]) : null;

                DB::table('absence_penalty_deduction_setups')->updateOrInsert(
                    ['staffId' => $staff->ID],
                    [
                        'deduction_type' => $deductionType,
                        'absent_days' => $absentDays,
                        'penalty_multiplier' => $multiplier,
                        'penalty_days' => $penaltyDays,
                        'daily_salary' => $dailySalary > 0 ? $dailySalary : null,
                        'monthly_salary' => $monthlySalary > 0 ? $monthlySalary : null,
                        'total_amount' => $totalAmount,
                        'duration_months' => $durationMonths,
                        'monthly_deduction' => $monthlyDeduction,
                        'balance_remaining' => $totalAmount,
                        'start_month' => $startMonth,
                        'end_month' => $endMonth,
                        'remarks' => $remarks,
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
                'message' => "Bulk import completed. {$importedCount} absence penalty setups imported successfully.",
                'warnings' => $warnings,
                'imported_count' => $importedCount
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AbsencePenaltyDeductionSetupApiController import: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
