<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class SurchargeDeductionSetupApiController extends Controller
{
    /**
     * Resolve the current user context from the X-User-Id header.
     */
    private function getUserContext(Request $request): ?array
    {
        $userId = $request->header('X-User-Id');

        if (!$userId) {
            return null;
        }

        $isSuperAdmin = DB::table('assign_user_role')
            ->where('userID', $userId)
            ->where('roleID', 1) // Super Admin
            ->exists();

        $adminStaff = DB::table('assign_user_role')
            ->where('userID', $userId)
            ->where('roleID', 48) // Admin Staff
            ->exists();

        $isAuditStaff = DB::table('assign_user_role')
            ->where('userID', $userId)
            ->whereIn('roleID', [34, 35]) // Audit Head, Audit Staff
            ->exists();

        $employee = DB::table('tblper')->where('UserID', $userId)->first();

        return [
            'userId'       => $userId,
            'isSuperAdmin' => $isSuperAdmin,
            'isAdminStaff' => $adminStaff,
            'isAuditStaff' => $isAuditStaff,
            'employee'     => $employee,
            'isHod'        => $employee && $employee->is_hod == 1,
        ];
    }

    /**
     * GET /api/nextjs/payroll/surcharge-deduction-setups
     * Fetch existing surcharge setups.
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $search = trim($request->input('search', ''));
            $query = DB::table('surcharge_deduction_setups as sds')
                ->join('tblper as p', 'p.ID', '=', 'sds.staffId')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->select(
                    'sds.*',
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
                $query->where('sds.staffId', $employee->ID);
            } else {
                $query->where('sds.id', 0); // fallback empty
            }

            $records = $query->orderBy('sds.id', 'desc')->get()->map(function ($row) {
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
            Log::error('SurchargeDeductionSetupApiController index: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/surcharge-deduction-setups
     * Save or update a surcharge deduction setup.
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
                'deduction_type' => 'required|string|in:one_time,spread',
                'total_amount' => 'required|numeric|min:0',
                'duration_months' => 'nullable|integer|min:1',
                'monthly_deduction' => 'required|numeric|min:0',
                'balance_remaining' => 'nullable|numeric|min:0',
                'start_month' => 'required|string|regex:/^\d{4}-\d{2}$/',
                'end_month' => 'nullable|string|regex:/^\d{4}-\d{2}$/',
                'is_active' => 'nullable|integer|in:0,1',
            ]);

            // Restriction check: Only Admins can modify settings
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access denied: Only administrators can configure surcharge deduction setups.'
                ], 403);
            }

            $id = $validated['id'] ?? null;
            $totalAmount = (float) $validated['total_amount'];
            $deductionType = $validated['deduction_type'];
            
            if ($deductionType === 'one_time') {
                $durationMonths = 1;
                $monthlyDeduction = $totalAmount;
                $endMonth = $validated['start_month'];
            } else {
                $durationMonths = (int) ($validated['duration_months'] ?? 1);
                $monthlyDeduction = (float) $validated['monthly_deduction'];
                $endMonth = $validated['end_month'];
            }

            $balanceRemaining = isset($validated['balance_remaining']) ? (float) $validated['balance_remaining'] : $totalAmount;

            $data = [
                'staffId' => $validated['staffId'],
                'deduction_type' => $deductionType,
                'total_amount' => $totalAmount,
                'duration_months' => $durationMonths,
                'monthly_deduction' => $monthlyDeduction,
                'balance_remaining' => $balanceRemaining,
                'start_month' => $validated['start_month'],
                'end_month' => $endMonth,
                'is_active' => $validated['is_active'] ?? 1,
                'updated_at' => now(),
            ];

            if ($id) {
                $exists = DB::table('surcharge_deduction_setups')->where('id', $id)->exists();
                if (!$exists) {
                    return response()->json(['status' => 'error', 'message' => 'Deduction setup not found.'], 404);
                }
                DB::table('surcharge_deduction_setups')->where('id', $id)->update($data);
                $message = 'Surcharge deduction setup updated successfully.';
            } else {
                $data['created_at'] = now();
                DB::table('surcharge_deduction_setups')->insert($data);
                $message = 'Surcharge deduction setup created successfully.';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message
            ]);
        } catch (\Throwable $th) {
            Log::error('SurchargeDeductionSetupApiController store: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/surcharge-deduction-setups/toggle/{id}
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

            $setup = DB::table('surcharge_deduction_setups')->where('id', $id)->first();
            if (!$setup) {
                return response()->json(['status' => 'error', 'message' => 'Setup not found.'], 404);
            }

            $newStatus = $setup->is_active == 1 ? 0 : 1;

            DB::table('surcharge_deduction_setups')->where('id', $id)->update([
                'is_active' => $newStatus,
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => $newStatus == 1 ? 'Deduction setup activated successfully.' : 'Deduction setup deactivated successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('SurchargeDeductionSetupApiController toggleStatus: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/nextjs/payroll/surcharge-deduction-setups/{id}
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

            $exists = DB::table('surcharge_deduction_setups')->where('id', $id)->exists();
            if (!$exists) {
                return response()->json(['status' => 'error', 'message' => 'Setup not found.'], 404);
            }

            DB::table('surcharge_deduction_setups')->where('id', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Surcharge deduction setup deleted successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('SurchargeDeductionSetupApiController destroy: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/surcharge-deduction-setups/template
     * Download public CSV template.
     */
    public function downloadTemplate(Request $request)
    {
        try {
            $headers = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="surcharge_import_template.csv"',
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Expires'             => '0',
            ];

            $columns = ['Staff ID', 'Deduction Type (one_time/spread)', 'Total Amount', 'Duration Months', 'Start Month (YYYY-MM)'];
            $exampleRow = ['1024', 'spread', '30000.00', '3', '2026-06'];

            $callback = function () use ($columns, $exampleRow) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $columns);
                fputcsv($handle, $exampleRow);
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Throwable $th) {
            Log::error('SurchargeDeductionSetupApiController downloadTemplate: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/surcharge-deduction-setups/import
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
            $typeIdx = -1;
            $amountIdx = -1;
            $durationIdx = -1;
            $startIdx = -1;

            foreach ($headers as $index => $header) {
                if (strpos($header, 'staff id') !== false || strpos($header, 'staff_id') !== false || $header === 'id' || $header === 'staffid') {
                    $staffIdIdx = $index;
                } elseif (strpos($header, 'file') !== false || $header === 'fileno' || $header === 'file_no') {
                    $fileNoIdx = $index;
                } elseif (strpos($header, 'type') !== false || strpos($header, 'deduction') !== false) {
                    $typeIdx = $index;
                } elseif (strpos($header, 'amount') !== false || strpos($header, 'total') !== false) {
                    $amountIdx = $index;
                } elseif (strpos($header, 'duration') !== false || strpos($header, 'month') !== false) {
                    $durationIdx = $index;
                } elseif (strpos($header, 'start') !== false || strpos($header, 'period') !== false) {
                    $startIdx = $index;
                }
            }

            // Fallback checking by column position
            if ($staffIdIdx === -1 && $fileNoIdx === -1) $staffIdIdx = 0;
            if ($typeIdx === -1) $typeIdx = 1;
            if ($amountIdx === -1) $amountIdx = 2;
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

                DB::table('surcharge_deduction_setups')->updateOrInsert(
                    ['staffId' => $staff->ID],
                    [
                        'deduction_type' => $deductionType,
                        'total_amount' => $totalAmount,
                        'duration_months' => $durationMonths,
                        'monthly_deduction' => $monthlyDeduction,
                        'balance_remaining' => $totalAmount,
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
                'message' => "Bulk import completed. {$importedCount} surcharge setups imported successfully.",
                'warnings' => $warnings,
                'imported_count' => $importedCount
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('SurchargeDeductionSetupApiController import: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
