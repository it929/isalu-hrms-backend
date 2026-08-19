<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class CoopSavingsSetupApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * Resolve the current user context from the X-User-Id header.
     */
    

    /**
     * GET /api/nextjs/payroll/coop-savings-setups
     * Fetch existing coop savings setups.
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $search = trim($request->input('search', ''));
            $query = DB::table('coop_savings_setups as css')
                ->join('tblper as p', 'p.ID', '=', 'css.staffId')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->select(
                    'css.*',
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

            $records = $query->orderBy('css.id', 'desc')->get()->map(function ($row) {
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
            Log::error('CoopSavingsSetupApiController index: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/coop-savings-setups
     * Save or update a cooperative savings setup.
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
                'monthly_saving' => 'required|numeric|min:0',
                'saving_balance' => 'nullable|numeric|min:0',
                'start_month' => 'required|string|regex:/^\d{4}-\d{2}$/',
                'is_active' => 'nullable|integer|in:0,1',
            ]);



            $id = $validated['id'] ?? null;
            $data = [
                'staffId' => $validated['staffId'],
                'monthly_saving' => (float) $validated['monthly_saving'],
                'saving_balance' => isset($validated['saving_balance']) ? (float) $validated['saving_balance'] : 0.00,
                'start_month' => $validated['start_month'],
                'is_active' => $validated['is_active'] ?? 1,
                'updated_at' => now(),
            ];

            if ($id) {
                $exists = DB::table('coop_savings_setups')->where('id', $id)->exists();
                if (!$exists) {
                    return response()->json(['status' => 'error', 'message' => 'Savings setup not found.'], 404);
                }
                DB::table('coop_savings_setups')->where('id', $id)->update($data);
                $message = 'Cooperative savings setup updated successfully.';
            } else {
                $data['created_at'] = now();
                DB::table('coop_savings_setups')->insert($data);
                $message = 'Cooperative savings setup created successfully.';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopSavingsSetupApiController store: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/coop-savings-setups/toggle/{id}
     * Toggle activation status of cooperative savings setup.
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }



            $setup = DB::table('coop_savings_setups')->where('id', $id)->first();
            if (!$setup) {
                return response()->json(['status' => 'error', 'message' => 'Setup not found.'], 404);
            }

            $newStatus = $setup->is_active == 1 ? 0 : 1;

            DB::table('coop_savings_setups')->where('id', $id)->update([
                'is_active' => $newStatus,
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => $newStatus == 1 ? 'Savings setup activated successfully.' : 'Savings setup deactivated successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopSavingsSetupApiController toggleStatus: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/nextjs/payroll/coop-savings-setups/{id}
     * Delete a cooperative savings setup.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }



            $exists = DB::table('coop_savings_setups')->where('id', $id)->exists();
            if (!$exists) {
                return response()->json(['status' => 'error', 'message' => 'Setup not found.'], 404);
            }

            DB::table('coop_savings_setups')->where('id', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Cooperative savings setup deleted successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopSavingsSetupApiController destroy: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/coop-savings-setups/import
     * Bulk import savings configurations using Excel/CSV.
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
            $staffIdIdx = -1;
            $fileNoIdx = -1;
            $savingIdx = -1;
            $balanceIdx = -1;
            $startIdx = -1;

            foreach ($rows[0] as $index => $rawHeader) {
                $h = strtolower(preg_replace('/[^a-z0-9]/', '', (string)$rawHeader));
                if (in_array($h, ['staffid', 'id', 'staff', 'employeeid', 'empid', 'staffno'])) {
                    $staffIdIdx = $index;
                } elseif (in_array($h, ['fileno', 'file', 'filenumber', 'file_no'])) {
                    $fileNoIdx = $index;
                } elseif (in_array($h, ['savingbalance', 'balance', 'savingbal', 'savingsbalance', 'currentbalance', 'savingbalanceamount', 'bal'])) {
                    $balanceIdx = $index;
                } elseif (in_array($h, ['monthlysavingamount', 'monthlysaving', 'savingamount', 'monthlyamount', 'saving', 'amount', 'monthly', 'monthlysavings', 'savingsamount', 'monthlysavingsamount'])) {
                    $savingIdx = $index;
                } elseif (in_array($h, ['startmonth', 'startmonthyyyymm', 'start', 'period', 'month', 'startperiod'])) {
                    $startIdx = $index;
                } else {
                    // Partial fallback check (check balance first so 'saving balance' is not captured as monthly saving)
                    $rawLower = strtolower(trim((string)$rawHeader));
                    if (strpos($rawLower, 'balance') !== false || strpos($rawLower, 'bal') !== false) {
                        $balanceIdx = $index;
                    } elseif (strpos($rawLower, 'monthly') !== false || strpos($rawLower, 'saving') !== false || strpos($rawLower, 'amount') !== false) {
                        $savingIdx = $index;
                    } elseif (strpos($rawLower, 'staff') !== false || strpos($rawLower, 'id') !== false) {
                        $staffIdIdx = $index;
                    } elseif (strpos($rawLower, 'start') !== false || strpos($rawLower, 'month') !== false) {
                        $startIdx = $index;
                    }
                }
            }

            // Fallback checking by column position:
            // Column 0 = StaffID / FileNo
            // Column 1 = Saving Amount
            // Column 2 = Saving Balance
            // Column 3 = Start Month
            if ($staffIdIdx === -1 && $fileNoIdx === -1) $staffIdIdx = 0;
            if ($savingIdx === -1) $savingIdx = 1;
            if ($balanceIdx === -1) $balanceIdx = 2;
            if ($startIdx === -1) $startIdx = 3;

            $importedCount = 0;
            $warnings = [];

            DB::beginTransaction();

            for ($r = 1; $r < count($rows); $r++) {
                $row = $rows[$r];

                // Skip empty row
                $isEmptyRow = true;
                foreach ($row as $cell) {
                    if (trim((string)$cell) !== '') {
                        $isEmptyRow = false;
                        break;
                    }
                }
                if ($isEmptyRow) continue;

                $staff = null;

                // 1. Match by Staff ID (if set)
                if ($staffIdIdx !== -1 && isset($row[$staffIdIdx]) && trim($row[$staffIdIdx]) !== '') {
                    $val = trim($row[$staffIdIdx]);
                    $staff = DB::table('tblper')->where('ID', $val)->first();
                }

                // 2. Match by File Number (if not matched yet)
                if (!$staff && $fileNoIdx !== -1 && isset($row[$fileNoIdx]) && trim($row[$fileNoIdx]) !== '') {
                    $val = trim($row[$fileNoIdx]);
                    $staff = DB::table('tblper')->where('fileNo', $val)->first();
                }

                // 3. Fallback matching (if both indexes are same, e.g. column 0)
                if (!$staff && $staffIdIdx !== -1 && isset($row[$staffIdIdx]) && trim($row[$staffIdIdx]) !== '') {
                    $val = trim($row[$staffIdIdx]);
                    $staff = DB::table('tblper')->where('fileNo', $val)->first();
                }

                if (!$staff) {
                    $warnings[] = "Row " . ($r + 1) . ": Employee ID/File Number not found.";
                    continue;
                }

                // Parse monthly saving amount
                $savingAmount = isset($row[$savingIdx]) ? (float)trim(str_replace([',', '₦', '$'], '', $row[$savingIdx])) : 0.00;
                if ($savingAmount <= 0) {
                    $warnings[] = "Row " . ($r + 1) . ": Invalid monthly saving amount.";
                    continue;
                }

                // Parse saving balance (optional)
                $savingBalance = 0.00;
                if ($balanceIdx !== -1 && isset($row[$balanceIdx]) && trim($row[$balanceIdx]) !== '') {
                    $savingBalance = (float)trim(str_replace([',', '₦', '$'], '', $row[$balanceIdx]));
                }

                // Parse start month
                $startMonth = isset($row[$startIdx]) ? trim($row[$startIdx]) : '';
                if ($startMonth === '') {
                    $startMonth = date('Y-m'); // Default to current month
                } else {
                    // Try parsing or normalizing
                    if (preg_match('/^\d{4}-\d{2}$/', $startMonth) === 0) {
                        // try to convert from date string or Excel numeric
                        if (is_numeric($startMonth)) {
                            // Excel serial date to Unix timestamp
                            $unixDate = ($startMonth - 25569) * 86400;
                            $startMonth = date('Y-m', $unixDate);
                        } else {
                            $parsedTime = strtotime($startMonth);
                            if ($parsedTime !== false) {
                                $startMonth = date('Y-m', $parsedTime);
                            } else {
                                $startMonth = date('Y-m'); // fallback
                            }
                        }
                    }
                }

                // Insert or update
                DB::table('coop_savings_setups')->updateOrInsert(
                    ['staffId' => $staff->ID],
                    [
                        'monthly_saving' => $savingAmount,
                        'saving_balance' => $savingBalance,
                        'start_month' => $startMonth,
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
            Log::error('CoopSavingsSetupApiController import: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/coop-savings-setups/template
     * Download the CSV/Excel template for bulk importing.
     */
    public function downloadTemplate(Request $request)
    {
        try {

            $headers = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="coop_savings_import_template.csv"',
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Expires'             => '0',
            ];

            $columns = ['Staff ID', 'Monthly Saving Amount', 'Saving Balance', 'Start Month (YYYY-MM)'];
            $exampleRow = ['1024', '5000.00', '25000.00', '2026-06'];

            $callback = function () use ($columns, $exampleRow) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $columns);
                fputcsv($handle, $exampleRow);
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Throwable $th) {
            Log::error('CoopSavingsSetupApiController downloadTemplate: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
