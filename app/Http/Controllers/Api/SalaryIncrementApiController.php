<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SalaryIncrementApiController extends Controller
{
    /**
     * Helper: compute salary breakdown percentages from gross.
     */
    private function calculateSalaryFields(float $gross): array
    {
        return [
            'basic_salary' => round($gross * 0.20, 2),
            'housing_allowance' => round($gross * 0.20, 2),
            'transport_allowance' => round($gross * 0.10, 2),
            'medical_allowance' => round($gross * 0.10, 2),
            'utility_allowance' => round($gross * 0.20, 2),
            'meal_allowance' => round($gross * 0.20, 2),
            'pension_rate' => 8.00,
        ];
    }

    /**
     * Helper: extract user context from X-User-Id
     */
    private function getUserContext(Request $request): ?object
    {
        $userId = $request->header('X-User-Id');
        if (!$userId) return null;
        return DB::table('users')->where('id', $userId)->first();
    }

    /**
     * GET /api/nextjs/payroll/salary-increments/staff
     * Retrieve all active staff with their current salary structure and department.
     */
    public function getStaff(Request $request)
    {
        try {
            $staff = DB::table('tblper as p')
                ->leftJoin('tbldepartment as dept', 'dept.id', '=', 'p.departmentID')
                ->leftJoin('tbldesignation as des', 'des.id', '=', 'p.designation')
                ->leftJoin('salary_structures as ss', 'ss.staffId', '=', 'p.ID')
                ->where('p.rank', '!=', 2)
                ->where('p.staff_status', 1)
                ->select(
                    'p.ID as id',
                    DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as name"),
                    'p.fileNo as file_no',
                    'dept.id as department_id',
                    'dept.department',
                    'des.designation',
                    'p.incremental_date',
                    'ss.basic_salary',
                    'ss.housing_allowance',
                    'ss.transport_allowance',
                    'ss.medical_allowance',
                    'ss.utility_allowance',
                    'ss.meal_allowance',
                    'ss.declare_salary'
                )
                ->orderBy('p.surname', 'asc')
                ->get()
                ->map(function ($r) {
                    $basic = (float)($r->basic_salary ?? 0);
                    $housing = (float)($r->housing_allowance ?? 0);
                    $transport = (float)($r->transport_allowance ?? 0);
                    $medical = (float)($r->medical_allowance ?? 0);
                    $utility = (float)($r->utility_allowance ?? 0);
                    $meal = (float)($r->meal_allowance ?? 0);
                    $gross = $basic + $housing + $transport + $medical + $utility + $meal;

                    return [
                        'id' => $r->id,
                        'name' => trim($r->name),
                        'label' => trim($r->name) . " (ID: {$r->id})",
                        'department_id' => $r->department_id,
                        'department' => $r->department ?? 'General',
                        'designation' => $r->designation ?? 'Staff',
                        'incremental_date' => $r->incremental_date,
                        'has_structure' => ($r->basic_salary !== null),
                        'current_gross' => round($gross, 2),
                        'current_basic' => round($basic, 2),
                        'breakdown' => [
                            'basic' => round($basic, 2),
                            'housing' => round($housing, 2),
                            'transport' => round($transport, 2),
                            'medical' => round($medical, 2),
                            'utility' => round($utility, 2),
                            'meal' => round($meal, 2),
                        ],
                    ];
                });

            $departments = DB::table('tbldepartment')
                ->select('id', 'department as name')
                ->orderBy('department', 'asc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'staff' => $staff,
                    'departments' => $departments,
                    'total_staff' => $staff->count(),
                    'total_payroll' => round($staff->sum('current_gross'), 2),
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('SalaryIncrementAPI getStaff: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/salary-increments/history
     * Get paginated salary increment history.
     */
    public function getHistory(Request $request)
    {
        try {
            $search = trim($request->query('search', ''));
            $departmentId = $request->query('department_id');
            $status = $request->query('status');
            $perPage = (int)$request->query('per_page', 20);

            $query = DB::table('salary_increments as si')
                ->join('tblper as p', 'p.ID', '=', 'si.staff_id')
                ->leftJoin('tbldepartment as dept', 'dept.id', '=', 'p.departmentID')
                ->leftJoin('tbldesignation as des', 'des.id', '=', 'p.designation')
                ->leftJoin('users as u', 'u.id', '=', 'si.created_by');

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where(DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, ''))"), 'like', "%{$search}%")
                      ->orWhere('si.staff_id', 'like', "%{$search}%")
                      ->orWhere('si.reason', 'like', "%{$search}%");
                });
            }

            if (!empty($departmentId)) {
                $query->where('p.departmentID', $departmentId);
            }

            if (!empty($status)) {
                $query->where('si.status', $status);
            }

            $total = $query->count();

            $records = $query->select(
                'si.*',
                DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as staff_name"),
                'dept.department',
                'des.designation',
                'u.name as created_by_name'
            )
            ->orderBy('si.id', 'desc')
            ->paginate($perPage);

            $summary = [
                'total_increments' => $total,
                'total_increase_amount' => round(DB::table('salary_increments')->where('status', 'applied')->sum('increase_amount'), 2),
            ];

            return response()->json([
                'status' => 'success',
                'data' => $records->items(),
                'pagination' => [
                    'current_page' => $records->currentPage(),
                    'last_page' => $records->lastPage(),
                    'per_page' => $records->perPage(),
                    'total' => $records->total(),
                ],
                'summary' => $summary,
            ]);
        } catch (\Throwable $th) {
            Log::error('SalaryIncrementAPI getHistory: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/salary-increments/single
     * Apply increment to a single staff member.
     */
    public function applySingle(Request $request)
    {
        $validated = $request->validate([
            'staff_id' => 'required|integer',
            'increment_type' => 'required|string|in:percentage,fixed_amount,new_gross',
            'percentage' => 'nullable|numeric|min:0',
            'amount' => 'nullable|numeric|min:0',
            'new_gross' => 'nullable|numeric|min:0',
            'effective_date' => 'nullable|string',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $ctx = $this->getUserContext($request);
            $userId = $ctx ? $ctx->id : null;

            $staff = DB::table('tblper')->where('ID', $validated['staff_id'])->first();
            if (!$staff) {
                return response()->json(['status' => 'error', 'message' => 'Staff member not found.'], 404);
            }

            // Get existing structure
            $existing = DB::table('salary_structures')->where('staffId', $validated['staff_id'])->first();
            $prevGross = 0.00;
            $prevBasic = 0.00;

            if ($existing) {
                $prevBasic = (float)$existing->basic_salary;
                $prevGross = $prevBasic + (float)$existing->housing_allowance + (float)$existing->transport_allowance +
                             (float)$existing->medical_allowance + (float)$existing->utility_allowance + (float)$existing->meal_allowance;
            }

            $type = $validated['increment_type'];
            $newGross = 0.00;
            $percentage = null;
            $amount = null;

            if ($type === 'percentage') {
                $percentage = (float)($validated['percentage'] ?? 0);
                if ($percentage <= 0) {
                    return response()->json(['status' => 'error', 'message' => 'Percentage increase must be greater than 0.'], 422);
                }
                $newGross = round($prevGross * (1.0 + ($percentage / 100.0)), 2);
            } elseif ($type === 'fixed_amount') {
                $amount = (float)($validated['amount'] ?? 0);
                if ($amount <= 0) {
                    return response()->json(['status' => 'error', 'message' => 'Fixed amount increase must be greater than 0.'], 422);
                }
                $newGross = round($prevGross + $amount, 2);
            } else { // new_gross
                $newGross = (float)($validated['new_gross'] ?? 0);
                if ($newGross <= 0) {
                    return response()->json(['status' => 'error', 'message' => 'New Gross Salary must be greater than 0.'], 422);
                }
            }

            $increaseAmount = round($newGross - $prevGross, 2);
            $calculatedFields = $this->calculateSalaryFields($newGross);

            DB::beginTransaction();

            // Update or insert salary_structures
            $structureData = array_merge([
                'staffId' => $validated['staff_id'],
            ], $calculatedFields);

            if ($existing) {
                DB::table('salary_structures')->where('staffId', $validated['staff_id'])->update($structureData);
            } else {
                $structureData['created_at'] = now();
                DB::table('salary_structures')->insert($structureData);
            }

            // Ensure staff_status = 1
            DB::table('tblper')->where('ID', $validated['staff_id'])->update(['staff_status' => 1]);

            // Log increment audit history
            $incrementId = DB::table('salary_increments')->insertGetId([
                'staff_id' => $validated['staff_id'],
                'increment_type' => $type,
                'percentage' => $percentage,
                'amount' => $amount,
                'previous_gross_salary' => $prevGross,
                'new_gross_salary' => $newGross,
                'increase_amount' => $increaseAmount,
                'previous_basic' => $prevBasic,
                'new_basic' => $calculatedFields['basic_salary'],
                'effective_date' => $validated['effective_date'] ?? date('Y-m-d'),
                'reason' => $validated['reason'] ?? 'Individual salary increment adjustment',
                'created_by' => $userId,
                'status' => 'applied',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Salary increment applied successfully. New Gross: ₦" . number_format($newGross, 2),
                'data' => [
                    'increment_id' => $incrementId,
                    'staff_id' => $validated['staff_id'],
                    'previous_gross' => $prevGross,
                    'new_gross' => $newGross,
                    'increase_amount' => $increaseAmount,
                    'breakdown' => $calculatedFields,
                ]
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('SalaryIncrementAPI applySingle: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/salary-increments/bulk
     * Apply percentage or fixed amount increment across all staff or filtered by department.
     */
    public function applyBulk(Request $request)
    {
        $validated = $request->validate([
            'target_type' => 'required|string|in:all,department',
            'department_id' => 'nullable|integer',
            'increment_type' => 'required|string|in:percentage,fixed_amount',
            'percentage' => 'nullable|numeric|min:0.01',
            'amount' => 'nullable|numeric|min:0.01',
            'effective_date' => 'nullable|string',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $ctx = $this->getUserContext($request);
            $userId = $ctx ? $ctx->id : null;

            $staffQuery = DB::table('tblper')
                ->where('rank', '!=', 2)
                ->where('staff_status', 1);

            if ($validated['target_type'] === 'department' && !empty($validated['department_id'])) {
                $staffQuery->where('departmentID', $validated['department_id']);
            }

            $staffMembers = $staffQuery->pluck('ID')->toArray();

            if (empty($staffMembers)) {
                return response()->json(['status' => 'error', 'message' => 'No active staff members found for the selected criteria.'], 422);
            }

            $type = $validated['increment_type'];
            $percentage = ($type === 'percentage') ? (float)$validated['percentage'] : null;
            $amount = ($type === 'fixed_amount') ? (float)$validated['amount'] : null;

            $structures = DB::table('salary_structures')
                ->whereIn('staffId', $staffMembers)
                ->get()
                ->keyBy('staffId');

            $batchId = 'BATCH-' . strtoupper(Str::random(10));
            $effectiveDate = $validated['effective_date'] ?? date('Y-m-d');
            $reason = $validated['reason'] ?? "Bulk {$type} adjustment across {$validated['target_type']}";

            $appliedCount = 0;
            $totalPrevPayroll = 0.00;
            $totalNewPayroll = 0.00;

            DB::beginTransaction();

            foreach ($staffMembers as $sid) {
                $struct = $structures[$sid] ?? null;
                $prevGross = 0.00;
                $prevBasic = 0.00;

                if ($struct) {
                    $prevBasic = (float)$struct->basic_salary;
                    $prevGross = $prevBasic + (float)$struct->housing_allowance + (float)$struct->transport_allowance +
                                 (float)$struct->medical_allowance + (float)$struct->utility_allowance + (float)$struct->meal_allowance;
                }

                if ($prevGross <= 0) continue; // Skip unconfigured structures in bulk

                $newGross = 0.00;
                if ($type === 'percentage') {
                    $newGross = round($prevGross * (1.0 + ($percentage / 100.0)), 2);
                } else {
                    $newGross = round($prevGross + $amount, 2);
                }

                $increaseAmount = round($newGross - $prevGross, 2);
                $calculatedFields = $this->calculateSalaryFields($newGross);

                $structureData = array_merge(['staffId' => $sid], $calculatedFields);

                if ($struct) {
                    DB::table('salary_structures')->where('staffId', $sid)->update($structureData);
                } else {
                    $structureData['created_at'] = now();
                    DB::table('salary_structures')->insert($structureData);
                }

                DB::table('salary_increments')->insert([
                    'staff_id' => $sid,
                    'increment_type' => $type,
                    'percentage' => $percentage,
                    'amount' => $amount,
                    'previous_gross_salary' => $prevGross,
                    'new_gross_salary' => $newGross,
                    'increase_amount' => $increaseAmount,
                    'previous_basic' => $prevBasic,
                    'new_basic' => $calculatedFields['basic_salary'],
                    'effective_date' => $effectiveDate,
                    'reason' => $reason,
                    'batch_id' => $batchId,
                    'created_by' => $userId,
                    'status' => 'applied',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $appliedCount++;
                $totalPrevPayroll += $prevGross;
                $totalNewPayroll += $newGross;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Bulk salary increment successfully applied to {$appliedCount} staff members.",
                'data' => [
                    'batch_id' => $batchId,
                    'affected_count' => $appliedCount,
                    'previous_total_payroll' => round($totalPrevPayroll, 2),
                    'new_total_payroll' => round($totalNewPayroll, 2),
                    'total_monthly_increase' => round($totalNewPayroll - $totalPrevPayroll, 2),
                ]
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('SalaryIncrementAPI applyBulk: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/salary-increments/upload
     * Bulk upload increment spreadsheet (.xlsx, .xls, .csv).
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            $ctx = $this->getUserContext($request);
            $userId = $ctx ? $ctx->id : null;

            $data = Excel::toArray([], $request->file('file'))[0];
            if (empty($data) || count($data) < 2) {
                return response()->json(['status' => 'error', 'message' => 'The uploaded file contains no data rows.'], 422);
            }

            $headers = array_map(fn($h) => strtolower(trim((string)$h)), $data[0]);
            unset($data[0]);

            $staffIdIdx = -1;
            $newGrossIdx = -1;
            $percentIdx = -1;
            $reasonIdx = -1;
            $effectiveDateIdx = -1;

            foreach ($headers as $i => $h) {
                if (str_contains($h, 'staff') || str_contains($h, 'id') || str_contains($h, 'idno')) {
                    if ($staffIdIdx === -1) $staffIdIdx = $i;
                } elseif (str_contains($h, 'new') || str_contains($h, 'gross') || str_contains($h, 'salary')) {
                    if ($newGrossIdx === -1) $newGrossIdx = $i;
                } elseif (str_contains($h, 'percent') || str_contains($h, '%')) {
                    if ($percentIdx === -1) $percentIdx = $i;
                } elseif (str_contains($h, 'reason') || str_contains($h, 'remark')) {
                    if ($reasonIdx === -1) $reasonIdx = $i;
                } elseif (str_contains($h, 'date') || str_contains($h, 'effective')) {
                    if ($effectiveDateIdx === -1) $effectiveDateIdx = $i;
                }
            }

            if ($staffIdIdx === -1) $staffIdIdx = 0;
            if ($newGrossIdx === -1 && $percentIdx === -1) $newGrossIdx = 1;

            $updatedCount = 0;
            $warnings = [];
            $batchId = 'IMPORT-' . strtoupper(Str::random(10));

            DB::beginTransaction();

            foreach ($data as $rowNum => $row) {
                $rawId = trim((string)($row[$staffIdIdx] ?? ''));
                if ($rawId === '') continue;

                $staff = DB::table('tblper')->where('ID', $rawId)->first();
                if (!$staff) {
                    $warnings[] = "Row " . ($rowNum + 1) . ": Staff with ID '{$rawId}' does not exist.";
                    continue;
                }

                $struct = DB::table('salary_structures')->where('staffId', $rawId)->first();
                $prevGross = 0.00;
                $prevBasic = 0.00;
                if ($struct) {
                    $prevBasic = (float)$struct->basic_salary;
                    $prevGross = $prevBasic + (float)$struct->housing_allowance + (float)$struct->transport_allowance +
                                 (float)$struct->medical_allowance + (float)$struct->utility_allowance + (float)$struct->meal_allowance;
                }

                $newGross = 0.00;
                $type = 'new_gross';
                $percentage = null;

                if ($percentIdx !== -1 && !empty($row[$percentIdx]) && is_numeric($row[$percentIdx])) {
                    $type = 'percentage';
                    $percentage = (float)$row[$percentIdx];
                    $newGross = round($prevGross * (1.0 + ($percentage / 100.0)), 2);
                } else {
                    $cleanGross = str_replace(',', '', (string)($row[$newGrossIdx] ?? 0));
                    $newGross = is_numeric($cleanGross) ? (float)$cleanGross : 0;
                }

                if ($newGross <= 0) {
                    $warnings[] = "Row " . ($rowNum + 1) . ": Invalid new gross amount for Staff ID '{$rawId}'.";
                    continue;
                }

                $increaseAmount = round($newGross - $prevGross, 2);
                $calculatedFields = $this->calculateSalaryFields($newGross);
                $reason = ($reasonIdx !== -1 && !empty($row[$reasonIdx])) ? trim($row[$reasonIdx]) : 'Spreadsheet bulk increment';
                $effectiveDate = ($effectiveDateIdx !== -1 && !empty($row[$effectiveDateIdx])) ? trim($row[$effectiveDateIdx]) : date('Y-m-d');

                $structureData = array_merge(['staffId' => $rawId], $calculatedFields);

                if ($struct) {
                    DB::table('salary_structures')->where('staffId', $rawId)->update($structureData);
                } else {
                    $structureData['created_at'] = now();
                    DB::table('salary_structures')->insert($structureData);
                }

                DB::table('tblper')->where('ID', $rawId)->update(['staff_status' => 1]);

                DB::table('salary_increments')->insert([
                    'staff_id' => $rawId,
                    'increment_type' => $type,
                    'percentage' => $percentage,
                    'amount' => null,
                    'previous_gross_salary' => $prevGross,
                    'new_gross_salary' => $newGross,
                    'increase_amount' => $increaseAmount,
                    'previous_basic' => $prevBasic,
                    'new_basic' => $calculatedFields['basic_salary'],
                    'effective_date' => $effectiveDate,
                    'reason' => $reason,
                    'batch_id' => $batchId,
                    'created_by' => $userId,
                    'status' => 'applied',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $updatedCount++;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Successfully processed {$updatedCount} salary increment records.",
                'updated_count' => $updatedCount,
                'warnings' => $warnings,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('SalaryIncrementAPI upload: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/salary-increments/revert
     * Revert a specific increment back to previous salary.
     */
    public function revert(Request $request)
    {
        $validated = $request->validate([
            'increment_id' => 'required|integer',
        ]);

        try {
            $increment = DB::table('salary_increments')->where('id', $validated['increment_id'])->first();
            if (!$increment) {
                return response()->json(['status' => 'error', 'message' => 'Increment record not found.'], 404);
            }

            if ($increment->status === 'reverted') {
                return response()->json(['status' => 'error', 'message' => 'This increment has already been reverted.'], 422);
            }

            $prevGross = (float)$increment->previous_gross_salary;
            if ($prevGross <= 0) {
                return response()->json(['status' => 'error', 'message' => 'Cannot revert: Previous gross salary was 0.'], 422);
            }

            $calculatedFields = $this->calculateSalaryFields($prevGross);

            DB::beginTransaction();

            DB::table('salary_structures')->where('staffId', $increment->staff_id)->update($calculatedFields);

            DB::table('salary_increments')->where('id', $increment->id)->update([
                'status' => 'reverted',
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Salary increment reverted successfully. Restored gross: ₦" . number_format($prevGross, 2),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('SalaryIncrementAPI revert: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/salary-increments/export
     * Export salary increment audit history to Excel (.xlsx).
     */
    public function exportHistory(Request $request)
    {
        try {
            $search = trim($request->query('search', ''));
            $departmentId = $request->query('department_id');

            $query = DB::table('salary_increments as si')
                ->join('tblper as p', 'p.ID', '=', 'si.staff_id')
                ->leftJoin('tbldepartment as dept', 'dept.id', '=', 'p.departmentID')
                ->leftJoin('tbldesignation as des', 'des.id', '=', 'p.designation')
                ->leftJoin('users as u', 'u.id', '=', 'si.created_by');

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where(DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, ''))"), 'like', "%{$search}%")
                      ->orWhere('si.staff_id', 'like', "%{$search}%");
                });
            }

            if (!empty($departmentId)) {
                $query->where('p.departmentID', $departmentId);
            }

            $records = $query->select(
                'si.*',
                DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as staff_name"),
                'dept.department',
                'des.designation',
                'u.name as created_by_name'
            )
            ->orderBy('si.id', 'desc')
            ->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Salary Increments');

            $columns = [
                'ID', 'STAFF ID', 'STAFF NAME', 'DEPARTMENT', 'DESIGNATION',
                'TYPE', 'PREVIOUS GROSS (₦)', 'NEW GROSS (₦)', 'INCREASE AMOUNT (₦)',
                'EFFECTIVE DATE', 'REASON / REMARKS', 'APPLIED BY', 'STATUS', 'DATE RECORDED'
            ];

            $totalCols = count($columns);
            $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

            // Row 1: Title
            $sheet->mergeCells("A1:{$lastColLetter}1");
            $sheet->setCellValue('A1', 'ISALU HRMS — SALARY INCREMENT AUDIT REPORT');
            $sheet->getStyle('A1')->applyFromArray([
                'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '000000']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension(1)->setRowHeight(28);

            // Row 2: Subtitle
            $sheet->mergeCells("A2:{$lastColLetter}2");
            $sheet->setCellValue('A2', 'Generated on ' . date('F j, Y, g:i a'));
            $sheet->getStyle('A2')->applyFromArray([
                'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '000000']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension(2)->setRowHeight(20);

            // Row 3: Headers
            foreach ($columns as $i => $colName) {
                $cl = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
                $sheet->setCellValue("{$cl}3", $colName);
            }
            $sheet->getStyle("A3:{$lastColLetter}3")->applyFromArray([
                'font'      => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '000000']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
            ]);
            $sheet->getRowDimension(3)->setRowHeight(26);

            $rowNum = 4;
            foreach ($records as $r) {
                $sheet->setCellValue("A{$rowNum}", $r->id);
                $sheet->setCellValue("B{$rowNum}", $r->staff_id);
                $sheet->setCellValue("C{$rowNum}", $r->staff_name);
                $sheet->setCellValue("D{$rowNum}", $r->department ?? 'General');
                $sheet->setCellValue("E{$rowNum}", $r->designation ?? 'Staff');
                $sheet->setCellValue("F{$rowNum}", strtoupper(str_replace('_', ' ', $r->increment_type)));
                $sheet->setCellValue("G{$rowNum}", (float)$r->previous_gross_salary);
                $sheet->setCellValue("H{$rowNum}", (float)$r->new_gross_salary);
                $sheet->setCellValue("I{$rowNum}", (float)$r->increase_amount);
                $sheet->setCellValue("J{$rowNum}", $r->effective_date ?? '—');
                $sheet->setCellValue("K{$rowNum}", $r->reason ?? '—');
                $sheet->setCellValue("L{$rowNum}", $r->created_by_name ?? 'Admin');
                $sheet->setCellValue("M{$rowNum}", strtoupper($r->status));
                $sheet->setCellValue("N{$rowNum}", $r->created_at);

                $sheet->getRowDimension($rowNum)->setRowHeight(16);
                $rowNum++;
            }

            $endRow = $rowNum - 1;
            if ($endRow >= 4) {
                $sheet->getStyle("G4:I{$endRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("G4:I{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("A4:B{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("J4:J{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("M4:M{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("A4:{$lastColLetter}{$endRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                    'font'    => ['size' => 8],
                ]);

                // Highlight increase amount in Green
                $sheet->getStyle("I4:I{$endRow}")->applyFromArray([
                    'font' => ['color' => ['rgb' => '008000'], 'bold' => true, 'size' => 8],
                ]);
            }

            for ($c = 1; $c <= $totalCols; $c++) {
                $cl = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
                $sheet->getColumnDimension($cl)->setAutoSize(true);
            }

            $sheet->freezePane('A4');
            $sheet->setAutoFilter("A3:{$lastColLetter}3");

            $filename = "Salary_Increments_" . date('Y_m_d') . ".xlsx";
            $writer = new Xlsx($spreadsheet);

            ob_start();
            $writer->save('php://output');
            $content = ob_get_clean();

            return response($content, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Expires'             => '0',
            ]);
        } catch (\Throwable $th) {
            Log::error('SalaryIncrementAPI exportHistory: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
