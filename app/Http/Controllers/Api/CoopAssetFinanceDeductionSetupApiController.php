<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class CoopAssetFinanceDeductionSetupApiController extends Controller
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
     * GET /api/nextjs/payroll/coop-asset-finance-deduction-setups
     * Fetch existing coop asset finance deduction setups.
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $search = trim($request->input('search', ''));
            $query = DB::table('coop_asset_finance_deduction_setups as cafds')
                ->join('tblper as p', 'p.ID', '=', 'cafds.staffId')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->select(
                    'cafds.*',
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
                $query->where('cafds.staffId', $employee->ID);
            } else {
                $query->where('cafds.id', 0); // fallback empty
            }

            $records = $query->orderBy('cafds.id', 'desc')->get()->map(function ($row) {
                $row->name = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                $row->is_active = (int) $row->is_active;
                return $row;
            });

            return response()->json([
                'status'      => 'success',
                'data'        => $records,
                'isSuperAdmin'=> $ctx['isSuperAdmin'],
                'isHod'       => $ctx['isHod'],
                'isAdminStaff'=> $ctx['isAdminStaff'],
                'isAuditStaff'=> $ctx['isAuditStaff'],
                'employee'    => $employee,
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopAssetFinanceDeductionSetupApiController index: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/coop-asset-finance-deduction-setups
     * Save or update a coop asset finance deduction setup.
     */
    public function store(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $validated = $request->validate([
                'id'               => 'nullable|integer',
                'staffId'          => 'required|integer|exists:tblper,ID',
                'total_amount'     => 'required|numeric|min:0',
                'duration_months'  => 'required|integer|min:1',
                'monthly_deduction'=> 'required|numeric|min:0',
                'balance_remaining'=> 'nullable|numeric|min:0',
                'start_month'      => 'required|string|regex:/^\d{4}-\d{2}$/',
                'end_month'        => 'nullable|string|regex:/^\d{4}-\d{2}$/',
                'is_active'        => 'nullable|integer|in:0,1',
            ]);

            // Restriction check: Only Admins can modify settings
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Access denied: Only administrators can configure coop asset finance deduction setups.'
                ], 403);
            }

            $id             = $validated['id'] ?? null;
            $totalAmount    = (float) $validated['total_amount'];
            $durationMonths = (int) $validated['duration_months'];
            $balanceRemaining = isset($validated['balance_remaining']) ? (float) $validated['balance_remaining'] : $totalAmount;

            // Auto-calculate end_month if not provided
            $endMonth = $validated['end_month'] ?? null;
            if (!$endMonth && $validated['start_month'] && $durationMonths > 0) {
                $parts = explode('-', $validated['start_month']);
                if (count($parts) === 2) {
                    $startDate = new \DateTime("{$parts[0]}-{$parts[1]}-01");
                    $startDate->modify('+' . ($durationMonths - 1) . ' month');
                    $endMonth = $startDate->format('Y-m');
                }
            }

            $data = [
                'staffId'          => $validated['staffId'],
                'total_amount'     => $totalAmount,
                'duration_months'  => $durationMonths,
                'monthly_deduction'=> (float) $validated['monthly_deduction'],
                'balance_remaining'=> $balanceRemaining,
                'start_month'      => $validated['start_month'],
                'end_month'        => $endMonth,
                'is_active'        => $validated['is_active'] ?? 1,
                'updated_at'       => now(),
            ];

            if ($id) {
                $exists = DB::table('coop_asset_finance_deduction_setups')->where('id', $id)->exists();
                if (!$exists) {
                    return response()->json(['status' => 'error', 'message' => 'Deduction setup not found.'], 404);
                }
                DB::table('coop_asset_finance_deduction_setups')->where('id', $id)->update($data);
                $message = 'Coop asset finance deduction setup updated successfully.';
            } else {
                $data['created_at'] = now();
                DB::table('coop_asset_finance_deduction_setups')->insert($data);
                $message = 'Coop asset finance deduction setup created successfully.';
            }

            return response()->json([
                'status'  => 'success',
                'message' => $message
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopAssetFinanceDeductionSetupApiController store: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/coop-asset-finance-deduction-setups/toggle/{id}
     * Toggle active status.
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
                    'status'  => 'error',
                    'message' => 'Access denied: Only administrators can toggle setup status.'
                ], 403);
            }

            $setup = DB::table('coop_asset_finance_deduction_setups')->where('id', $id)->first();
            if (!$setup) {
                return response()->json(['status' => 'error', 'message' => 'Setup not found.'], 404);
            }

            $newStatus = $setup->is_active == 1 ? 0 : 1;

            DB::table('coop_asset_finance_deduction_setups')->where('id', $id)->update([
                'is_active'  => $newStatus,
                'updated_at' => now()
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => $newStatus == 1 ? 'Setup activated successfully.' : 'Setup deactivated successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopAssetFinanceDeductionSetupApiController toggleStatus: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/nextjs/payroll/coop-asset-finance-deduction-setups/{id}
     * Delete a setup record.
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
                    'status'  => 'error',
                    'message' => 'Access denied: Only administrators can delete setups.'
                ], 403);
            }

            $exists = DB::table('coop_asset_finance_deduction_setups')->where('id', $id)->exists();
            if (!$exists) {
                return response()->json(['status' => 'error', 'message' => 'Setup not found.'], 404);
            }

            DB::table('coop_asset_finance_deduction_setups')->where('id', $id)->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Coop asset finance deduction setup deleted successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('CoopAssetFinanceDeductionSetupApiController destroy: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/coop-asset-finance-deduction-setups/template
     * Download CSV import template.
     */
    public function downloadTemplate(Request $request)
    {
        try {
            $headers = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="coop_asset_finance_import_template.csv"',
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Expires'             => '0',
            ];

            $columns    = ['Staff ID', 'Total Amount', 'Duration Months', 'Monthly Deduction', 'Start Month (YYYY-MM)'];
            $exampleRow = ['1024', '120000.00', '12', '10000.00', '2026-07'];

            $callback = function () use ($columns, $exampleRow) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $columns);
                fputcsv($handle, $exampleRow);
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Throwable $th) {
            Log::error('CoopAssetFinanceDeductionSetupApiController downloadTemplate: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/coop-asset-finance-deduction-setups/import
     * Bulk import from Excel/CSV spreadsheet.
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
                    'status'  => 'error',
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
                    'status'  => 'error',
                    'message' => 'The uploaded file is empty or contains no records.'
                ], 422);
            }

            // Normalize headers
            $headers = array_map(fn($h) => strtolower(trim((string)$h)), $rows[0]);

            $staffIdIdx   = array_search('staff id', $headers) !== false ? array_search('staff id', $headers) : 0;
            $amountIdx    = 1;
            $durationIdx  = 2;
            $monthlyIdx   = 3;
            $startIdx     = 4;

            foreach ($headers as $i => $h) {
                if (str_contains($h, 'staff') || str_contains($h, 'id')) $staffIdIdx = $i;
                if (str_contains($h, 'total') || str_contains($h, 'amount')) $amountIdx = $i;
                if (str_contains($h, 'duration') || str_contains($h, 'month') && !str_contains($h, 'start') && !str_contains($h, 'monthly')) $durationIdx = $i;
                if (str_contains($h, 'monthly') || str_contains($h, 'deduction')) $monthlyIdx = $i;
                if (str_contains($h, 'start')) $startIdx = $i;
            }

            $importedCount = 0;
            $warnings      = [];

            DB::beginTransaction();

            for ($r = 1; $r < count($rows); $r++) {
                $row = $rows[$r];

                $isEmptyRow = true;
                foreach ($row as $cell) {
                    if (trim((string)$cell) !== '') { $isEmptyRow = false; break; }
                }
                if ($isEmptyRow) continue;

                // Resolve staff
                $staff = null;
                $staffVal = isset($row[$staffIdIdx]) ? trim((string)$row[$staffIdIdx]) : '';
                if ($staffVal !== '') {
                    $staff = DB::table('tblper')->where('ID', $staffVal)->first()
                          ?? DB::table('tblper')->where('fileNo', $staffVal)->first();
                }

                if (!$staff) {
                    $warnings[] = "Row " . ($r + 1) . ": Employee not found for value '{$staffVal}'.";
                    continue;
                }

                $totalAmount    = isset($row[$amountIdx]) ? (float)str_replace([',', '₦', '$'], '', $row[$amountIdx]) : 0;
                $durationMonths = isset($row[$durationIdx]) ? max(1, (int)$row[$durationIdx]) : 1;
                $monthlyDed     = isset($row[$monthlyIdx]) ? (float)str_replace([',', '₦', '$'], '', $row[$monthlyIdx]) : round($totalAmount / $durationMonths, 2);
                $startMonth     = isset($row[$startIdx]) ? trim($row[$startIdx]) : date('Y-m');

                if (!preg_match('/^\d{4}-\d{2}$/', $startMonth)) {
                    if (is_numeric($startMonth)) {
                        $startMonth = date('Y-m', ($startMonth - 25569) * 86400);
                    } else {
                        $ts = strtotime($startMonth);
                        $startMonth = $ts !== false ? date('Y-m', $ts) : date('Y-m');
                    }
                }

                $parts = explode('-', $startMonth);
                $startDate = new \DateTime("{$parts[0]}-{$parts[1]}-01");
                $startDate->modify('+' . ($durationMonths - 1) . ' month');
                $endMonth = $startDate->format('Y-m');

                if ($totalAmount <= 0) {
                    $warnings[] = "Row " . ($r + 1) . ": Invalid total amount.";
                    continue;
                }

                DB::table('coop_asset_finance_deduction_setups')->updateOrInsert(
                    ['staffId' => $staff->ID, 'start_month' => $startMonth],
                    [
                        'total_amount'      => $totalAmount,
                        'duration_months'   => $durationMonths,
                        'monthly_deduction' => $monthlyDed,
                        'balance_remaining' => $totalAmount,
                        'end_month'         => $endMonth,
                        'is_active'         => 1,
                        'updated_at'        => now(),
                        'created_at'        => now(),
                    ]
                );

                $importedCount++;
            }

            DB::commit();

            return response()->json([
                'status'         => 'success',
                'message'        => "Bulk import completed. {$importedCount} configurations imported successfully.",
                'warnings'       => $warnings,
                'imported_count' => $importedCount
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('CoopAssetFinanceDeductionSetupApiController import: ' . $th->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
