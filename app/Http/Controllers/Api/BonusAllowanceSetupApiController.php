<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class BonusAllowanceSetupApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * GET /api/nextjs/payroll/bonus-allowance-setups/staff
     * Fetch active staff members for autocomplete.
     */
    public function getStaffList(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $staff = DB::table('tblper as p')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->where('p.rank', '!=', 2)
                ->where('p.staff_status', 1)
                ->select(
                    'p.ID as id',
                    'p.ID as staffId',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'd.department'
                )
                ->orderBy('p.surname', 'asc')
                ->get()
                ->map(function ($row) {
                    $fullName = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                    return [
                        'id' => $row->id,
                        'staffId' => $row->staffId,
                        'name' => $fullName,
                        'department' => $row->department ?? 'General',
                        'label' => "[ID: {$row->staffId}] {$fullName}",
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $staff
            ]);
        } catch (\Throwable $th) {
            Log::error('BonusAllowanceSetupApiController getStaffList: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/bonus-allowance-setups
     * Fetch existing bonus and allowance configurations.
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $search = trim($request->input('search', ''));
            $typeFilter = trim($request->input('type', 'all'));
            $freqFilter = trim($request->input('frequency', 'all'));
            $statusFilter = $request->input('status', 'all');

            $query = DB::table('staff_bonuses_and_allowances as sba')
                ->join('tblper as p', 'p.ID', '=', 'sba.staffId')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->select(
                    'sba.*',
                    'p.ID as staff_table_id',
                    'p.surname',
                    'p.first_name',
                    'p.othernames',
                    'd.department'
                );

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('sba.staffId', 'like', "%{$search}%")
                      ->orWhere('sba.title', 'like', "%{$search}%")
                      ->orWhere('sba.category', 'like', "%{$search}%")
                      ->orWhere('p.surname', 'like', "%{$search}%")
                      ->orWhere('p.first_name', 'like', "%{$search}%")
                      ->orWhere('p.othernames', 'like', "%{$search}%")
                      ->orWhere('d.department', 'like', "%{$search}%");
                });
            }

            if ($typeFilter !== 'all' && in_array($typeFilter, ['bonus', 'allowance'])) {
                $query->where('sba.type', $typeFilter);
            }

            if ($freqFilter !== 'all' && in_array($freqFilter, ['one_time', 'recurring'])) {
                $query->where('sba.frequency', $freqFilter);
            }

            if ($statusFilter !== 'all') {
                $query->where('sba.is_active', (int)$statusFilter);
            }

            $employee = $ctx['employee'];

            $records = $query->orderBy('sba.id', 'desc')->get()->map(function ($row) {
                $row->name = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                $row->amount = (float) $row->amount;
                $row->is_active = (int) $row->is_active;
                return $row;
            });

            // Calculate statistics
            $allActive = DB::table('staff_bonuses_and_allowances')->where('is_active', 1)->get();
            $totalAllowances = $allActive->where('type', 'allowance')->count();
            $totalBonuses = $allActive->where('type', 'bonus')->count();
            $totalMonthlyValue = (float) $allActive->sum('amount');
            $totalBeneficiaries = $allActive->pluck('staffId')->unique()->count();

            return response()->json([
                'status' => 'success',
                'data' => $records,
                'summary' => [
                    'totalRecords' => $records->count(),
                    'totalActiveRecords' => $allActive->count(),
                    'totalAllowances' => $totalAllowances,
                    'totalBonuses' => $totalBonuses,
                    'totalMonthlyValue' => $totalMonthlyValue,
                    'totalBeneficiaries' => $totalBeneficiaries,
                ],
                'isSuperAdmin' => $ctx['isSuperAdmin'],
                'isHod' => $ctx['isHod'],
                'isAdminStaff' => $ctx['isAdminStaff'],
                'isAuditStaff' => $ctx['isAuditStaff'],
                'employee' => $employee,
            ]);
        } catch (\Throwable $th) {
            Log::error('BonusAllowanceSetupApiController index: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/bonus-allowance-setups
     * Create or update a bonus / allowance setup.
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
                'type' => 'required|string|in:bonus,allowance',
                'category' => 'nullable|string|max:100',
                'title' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0.01',
                'frequency' => 'required|string|in:one_time,recurring',
                'start_month' => 'required|string|regex:/^\d{4}-\d{2}$/',
                'end_month' => 'nullable|string|regex:/^\d{4}-\d{2}$/',
                'notes' => 'nullable|string|max:1000',
                'is_active' => 'nullable|integer|in:0,1',
            ]);

            $id = $validated['id'] ?? null;
            $type = $validated['type'];
            $frequency = $validated['frequency'];
            $category = $validated['category'] ?: 'custom';

            $endMonth = $frequency === 'one_time' ? $validated['start_month'] : ($validated['end_month'] ?? null);

            $data = [
                'staffId' => $validated['staffId'],
                'type' => $type,
                'category' => $category,
                'title' => trim($validated['title']),
                'amount' => (float) $validated['amount'],
                'frequency' => $frequency,
                'start_month' => $validated['start_month'],
                'end_month' => $endMonth,
                'notes' => $validated['notes'] ?? null,
                'is_active' => $validated['is_active'] ?? 1,
                'updated_at' => now(),
            ];

            if ($id) {
                $exists = DB::table('staff_bonuses_and_allowances')->where('id', $id)->exists();
                if (!$exists) {
                    return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
                }
                DB::table('staff_bonuses_and_allowances')->where('id', $id)->update($data);
                $message = ucfirst($type) . ' setup updated successfully.';
            } else {
                $data['created_by'] = $ctx['employee'] ? $ctx['employee']->ID : null;
                $data['created_at'] = now();
                DB::table('staff_bonuses_and_allowances')->insert($data);
                $message = ucfirst($type) . ' setup created successfully.';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message
            ]);
        } catch (\Throwable $th) {
            Log::error('BonusAllowanceSetupApiController store: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/bonus-allowance-setups/toggle/{id}
     * Toggle active status.
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $setup = DB::table('staff_bonuses_and_allowances')->where('id', $id)->first();
            if (!$setup) {
                return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
            }

            $newStatus = $setup->is_active == 1 ? 0 : 1;

            DB::table('staff_bonuses_and_allowances')->where('id', $id)->update([
                'is_active' => $newStatus,
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => $newStatus == 1 ? 'Record activated successfully.' : 'Record deactivated successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('BonusAllowanceSetupApiController toggleStatus: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/nextjs/payroll/bonus-allowance-setups/{id}
     * Delete a bonus / allowance setup.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $exists = DB::table('staff_bonuses_and_allowances')->where('id', $id)->exists();
            if (!$exists) {
                return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
            }

            DB::table('staff_bonuses_and_allowances')->where('id', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Bonus/Allowance record deleted successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('BonusAllowanceSetupApiController destroy: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/bonus-allowance-setups/template
     * Download CSV import template.
     */
    public function downloadTemplate(Request $request)
    {
        try {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="bonus_allowance_import_template.csv"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            $columns = [
                'Staff ID',
                'Type (bonus/allowance)',
                'Category',
                'Title',
                'Amount',
                'Frequency (one_time/recurring)',
                'Start Month (YYYY-MM)',
                'End Month (YYYY-MM)',
                'Notes'
            ];

            $exampleRows = [
                ['10', 'allowance', 'hazard_allowance', 'Hazard Allowance - Lab Dept', '25000.00', 'recurring', '2026-08', '', 'Monthly standard lab hazard'],
                ['15', 'bonus', 'performance_bonus', 'Q3 Outstanding Performance Bonus', '50000.00', 'one_time', '2026-08', '2026-08', 'Management approval ref #904']
            ];

            $callback = function () use ($columns, $exampleRows) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, $columns);
                foreach ($exampleRows as $row) {
                    fputcsv($handle, $row);
                }
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Throwable $th) {
            Log::error('BonusAllowanceSetupApiController downloadTemplate: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/bonus-allowance-setups/import
     * Bulk import from Excel / CSV file.
     */
    public function import(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $request->validate([
                'file' => 'required|file'
            ]);

            $file = $request->file('file');
            $rows = Excel::toArray([], $file)[0];

            if (empty($rows) || count($rows) <= 1) {
                return response()->json(['status' => 'error', 'message' => 'The uploaded file contains no data rows.'], 422);
            }

            $created = 0;
            $skipped = 0;
            $errors = [];

            // Remove header row
            array_shift($rows);

            foreach ($rows as $index => $row) {
                $rowNum = $index + 2;
                $staffIdRaw = trim((string)($row[0] ?? ''));
                $typeRaw = strtolower(trim((string)($row[1] ?? 'allowance')));
                $categoryRaw = trim((string)($row[2] ?? 'custom'));
                $titleRaw = trim((string)($row[3] ?? ''));
                $amountRaw = (float) str_replace([',', ' '], '', (string)($row[4] ?? 0));
                $freqRaw = strtolower(trim((string)($row[5] ?? 'one_time')));
                $startMonthRaw = trim((string)($row[6] ?? ''));
                $endMonthRaw = trim((string)($row[7] ?? ''));
                $notesRaw = trim((string)($row[8] ?? ''));

                if ($staffIdRaw === '' || $amountRaw <= 0) {
                    $skipped++;
                    continue;
                }

                // Check staff exists in tblper
                $staff = DB::table('tblper')->where('ID', $staffIdRaw)->orWhere('fileNo', $staffIdRaw)->first();
                if (!$staff) {
                    $errors[] = "Row {$rowNum}: Staff '{$staffIdRaw}' not found in database.";
                    $skipped++;
                    continue;
                }

                $type = in_array($typeRaw, ['bonus', 'allowance']) ? $typeRaw : 'allowance';
                $frequency = in_array($freqRaw, ['one_time', 'recurring']) ? $freqRaw : 'one_time';
                $title = $titleRaw !== '' ? $titleRaw : ucfirst($type) . ' - ' . ucfirst(str_replace('_', ' ', $categoryRaw));

                // Validate or default start_month
                if (!preg_match('/^\d{4}-\d{2}$/', $startMonthRaw)) {
                    $startMonthRaw = date('Y-m');
                }

                if ($frequency === 'one_time') {
                    $endMonthRaw = $startMonthRaw;
                } elseif ($endMonthRaw !== '' && !preg_match('/^\d{4}-\d{2}$/', $endMonthRaw)) {
                    $endMonthRaw = null;
                }

                DB::table('staff_bonuses_and_allowances')->insert([
                    'staffId' => $staff->ID,
                    'type' => $type,
                    'category' => $categoryRaw ?: 'custom',
                    'title' => $title,
                    'amount' => $amountRaw,
                    'frequency' => $frequency,
                    'start_month' => $startMonthRaw,
                    'end_month' => $endMonthRaw ?: null,
                    'notes' => $notesRaw ?: null,
                    'is_active' => 1,
                    'created_by' => $ctx['employee'] ? $ctx['employee']->ID : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $created++;
            }

            return response()->json([
                'status' => 'success',
                'message' => "Bulk import completed: {$created} records created, {$skipped} skipped.",
                'created_count' => $created,
                'skipped_count' => $skipped,
                'errors' => array_slice($errors, 0, 10)
            ]);
        } catch (\Throwable $th) {
            Log::error('BonusAllowanceSetupApiController import: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
