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
            'increment_type' => 'required|string|in:percentage,fixed_amount,new_gross,decrement_percentage,decrement_fixed,decrement_amount',
            'percentage' => 'nullable|numeric',
            'amount' => 'nullable|numeric',
            'new_gross' => 'nullable|numeric|min:0.01',
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

            if ($type === 'decrement_percentage' || ($type === 'percentage' && isset($validated['percentage']) && (float)$validated['percentage'] < 0)) {
                $rawPct = abs((float)($validated['percentage'] ?? 0));
                if ($rawPct <= 0 || $rawPct >= 100) {
                    return response()->json(['status' => 'error', 'message' => 'Percentage reduction must be between 0.01% and 99.99%.'], 422);
                }
                $percentage = -$rawPct;
                $type = 'decrement_percentage';
                $newGross = round($prevGross * (1.0 - ($rawPct / 100.0)), 2);
            } elseif ($type === 'percentage') {
                $percentage = (float)($validated['percentage'] ?? 0);
                if ($percentage <= 0) {
                    return response()->json(['status' => 'error', 'message' => 'Percentage increase must be greater than 0.'], 422);
                }
                $newGross = round($prevGross * (1.0 + ($percentage / 100.0)), 2);
            } elseif ($type === 'decrement_fixed' || $type === 'decrement_amount' || ($type === 'fixed_amount' && isset($validated['amount']) && (float)$validated['amount'] < 0)) {
                $rawAmt = abs((float)($validated['amount'] ?? 0));
                if ($rawAmt <= 0) {
                    return response()->json(['status' => 'error', 'message' => 'Fixed amount reduction must be greater than ₦0.00.'], 422);
                }
                $amount = -$rawAmt;
                $type = 'decrement_fixed';
                $newGross = round($prevGross - $rawAmt, 2);
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

            if ($newGross <= 0) {
                return response()->json(['status' => 'error', 'message' => 'Resulting salary gross must be greater than ₦0.00.'], 422);
            }

            $increaseAmount = round($newGross - $prevGross, 2);
            $actionWord = ($increaseAmount < 0) ? 'decrement' : 'increment';
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
                'message' => "Salary {$actionWord} applied successfully. New Gross: ₦" . number_format($newGross, 2),
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
            'increment_type' => 'required|string|in:percentage,fixed_amount,decrement_percentage,decrement_fixed,decrement_amount',
            'percentage' => 'nullable|numeric',
            'amount' => 'nullable|numeric',
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
            $isDecrementPct = ($type === 'decrement_percentage' || ($type === 'percentage' && isset($validated['percentage']) && (float)$validated['percentage'] < 0));
            $isDecrementAmt = ($type === 'decrement_fixed' || $type === 'decrement_amount' || ($type === 'fixed_amount' && isset($validated['amount']) && (float)$validated['amount'] < 0));

            $percentage = null;
            $amount = null;

            if ($isDecrementPct) {
                $rawPct = abs((float)($validated['percentage'] ?? 0));
                if ($rawPct <= 0 || $rawPct >= 100) {
                    return response()->json(['status' => 'error', 'message' => 'Percentage reduction must be between 0.01% and 99.99%.'], 422);
                }
                $percentage = -$rawPct;
                $type = 'decrement_percentage';
            } elseif ($type === 'percentage') {
                $percentage = (float)($validated['percentage'] ?? 0);
                if ($percentage <= 0) {
                    return response()->json(['status' => 'error', 'message' => 'Percentage increase must be greater than 0.'], 422);
                }
            } elseif ($isDecrementAmt) {
                $rawAmt = abs((float)($validated['amount'] ?? 0));
                if ($rawAmt <= 0) {
                    return response()->json(['status' => 'error', 'message' => 'Fixed amount reduction must be greater than ₦0.00.'], 422);
                }
                $amount = -$rawAmt;
                $type = 'decrement_fixed';
            } else {
                $amount = (float)($validated['amount'] ?? 0);
                if ($amount <= 0) {
                    return response()->json(['status' => 'error', 'message' => 'Fixed amount increase must be greater than 0.'], 422);
                }
            }

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
                if ($type === 'decrement_percentage') {
                    $newGross = round($prevGross * (1.0 - (abs($percentage) / 100.0)), 2);
                } elseif ($type === 'percentage') {
                    $newGross = round($prevGross * (1.0 + ($percentage / 100.0)), 2);
                } elseif ($type === 'decrement_fixed') {
                    $newGross = round($prevGross - abs($amount), 2);
                } else {
                    $newGross = round($prevGross + $amount, 2);
                }

                if ($newGross <= 0) continue; // Cannot decrement salary to 0 or below

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

            $actionWord = ($totalNewPayroll < $totalPrevPayroll) ? 'decrement' : 'increment';

            return response()->json([
                'status' => 'success',
                'message' => "Bulk salary {$actionWord} successfully applied to {$appliedCount} staff members.",
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
     * Helper: Parse and validate an uploaded salary increment spreadsheet.
     * Returns structured parsed rows, warnings, and department summary.
     */
    private function parseIncrementSpreadsheet($file)
    {
        $sheetData = Excel::toArray([], $file)[0] ?? [];
        if (empty($sheetData) || count($sheetData) < 2) {
            throw new \Exception('The uploaded spreadsheet contains no data rows.');
        }

        // Find header row (handles row 0 or row 2/3 if instructions banner is present)
        $headerRowIdx = -1;
        $headers = [];
        foreach ($sheetData as $rIdx => $row) {
            $lowerRow = array_map(fn($v) => strtolower(trim((string)$v)), $row);
            $hasStaff = false;
            $hasSalaryOrAmount = false;
            foreach ($lowerRow as $cell) {
                if (str_contains($cell, 'staff') || str_contains($cell, 'id') || str_contains($cell, 'file')) {
                    $hasStaff = true;
                }
                if (str_contains($cell, 'gross') || str_contains($cell, 'salary') || str_contains($cell, 'amount') || str_contains($cell, 'percent') || str_contains($cell, 'bump')) {
                    $hasSalaryOrAmount = true;
                }
            }
            if ($hasStaff && $hasSalaryOrAmount) {
                $headerRowIdx = $rIdx;
                $headers = $lowerRow;
                break;
            }
        }

        if ($headerRowIdx === -1) {
            $headerRowIdx = 0;
            $headers = array_map(fn($v) => strtolower(trim((string)$v)), $sheetData[0]);
        }

        // Identify column indices
        $staffIdIdx = -1;
        $fileNoIdx = -1;
        $staffNameIdx = -1;
        $deptIdx = -1;
        $currentGrossIdx = -1;
        $amountIdx = -1;
        $percentIdx = -1;
        $decrementAmountIdx = -1;
        $decrementPercentIdx = -1;
        $newGrossIdx = -1;
        $effectiveDateIdx = -1;
        $reasonIdx = -1;

        foreach ($headers as $i => $h) {
            if ($h === '') continue;
            if (str_contains($h, 'staff id') || $h === 'id' || $h === 'staff_id' || $h === 'staffid' || $h === 'idno') {
                if ($staffIdIdx === -1) $staffIdIdx = $i;
            } elseif (str_contains($h, 'file') || str_contains($h, 'fileno') || str_contains($h, 'file_no')) {
                if ($fileNoIdx === -1) $fileNoIdx = $i;
            } elseif (str_contains($h, 'name') || str_contains($h, 'employee')) {
                if ($staffNameIdx === -1) $staffNameIdx = $i;
            } elseif (str_contains($h, 'department') || str_contains($h, 'dept')) {
                if ($deptIdx === -1) $deptIdx = $i;
            } elseif (str_contains($h, 'current') || str_contains($h, 'old') || str_contains($h, 'prev')) {
                if ($currentGrossIdx === -1) $currentGrossIdx = $i;
            } elseif (str_contains($h, 'decrement amount') || str_contains($h, 'decrease amount') || str_contains($h, 'reduction amount') || str_contains($h, 'deduct amount')) {
                if ($decrementAmountIdx === -1) $decrementAmountIdx = $i;
            } elseif (str_contains($h, 'decrement percent') || str_contains($h, 'decrease percent') || str_contains($h, 'reduction percent') || str_contains($h, 'reduction %') || str_contains($h, 'decrement %')) {
                if ($decrementPercentIdx === -1) $decrementPercentIdx = $i;
            } elseif (str_contains($h, 'amount') || str_contains($h, 'bump') || str_contains($h, 'fixed') || str_contains($h, 'add') || str_contains($h, 'increase amount') || str_contains($h, 'increment amount')) {
                if ($amountIdx === -1) $amountIdx = $i;
            } elseif (str_contains($h, 'percent') || str_contains($h, '%') || str_contains($h, 'rate') || str_contains($h, 'increment percentage')) {
                if ($percentIdx === -1) $percentIdx = $i;
            } elseif (str_contains($h, 'new') || str_contains($h, 'target gross') || str_contains($h, 'new_gross') || str_contains($h, 'new gross')) {
                if ($newGrossIdx === -1) $newGrossIdx = $i;
            } elseif (str_contains($h, 'date') || str_contains($h, 'effective')) {
                if ($effectiveDateIdx === -1) $effectiveDateIdx = $i;
            } elseif (str_contains($h, 'reason') || str_contains($h, 'remark') || str_contains($h, 'note') || str_contains($h, 'comment')) {
                if ($reasonIdx === -1) $reasonIdx = $i;
            }
        }

        if ($staffIdIdx === -1 && $fileNoIdx === -1) {
            $staffIdIdx = 0;
        }

        // Query active staff
        $staffDb = DB::table('tblper as p')
            ->leftJoin('tbldepartment as dept', 'dept.id', '=', 'p.departmentID')
            ->leftJoin('tbldesignation as des', 'des.id', '=', 'p.designation')
            ->leftJoin('salary_structures as ss', 'ss.staffId', '=', 'p.ID')
            ->where('p.rank', '!=', 2)
            ->where('p.staff_status', 1)
            ->select(
                'p.ID as id',
                'p.fileNo as file_no',
                DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as name"),
                'dept.id as department_id',
                'dept.department',
                'des.designation',
                'ss.basic_salary',
                'ss.housing_allowance',
                'ss.transport_allowance',
                'ss.medical_allowance',
                'ss.utility_allowance',
                'ss.meal_allowance'
            )
            ->get();

        $staffById = $staffDb->keyBy('id');
        $staffByFileNo = $staffDb->whereNotNull('file_no')
            ->filter(fn($s) => trim((string)$s->file_no) !== '')
            ->keyBy(fn($s) => strtolower(trim((string)$s->file_no)));

        $dataRows = array_slice($sheetData, $headerRowIdx + 1);
        $parsedRows = [];
        $warnings = [];
        $deptAggregates = [];
        $totalPrevPayroll = 0.00;
        $totalNewPayroll = 0.00;
        $validCount = 0;
        $incrementsCount = 0;
        $decrementsCount = 0;
        $skippedCount = 0;

        $parseSignedNumber = function($raw) {
            if ($raw === null) return null;
            $str = trim((string)$raw);
            if ($str === '' || $str === '-' || $str === '—') return null;
            $isNeg = false;
            if (str_starts_with($str, '-') || (str_starts_with($str, '(') && str_ends_with($str, ')'))) {
                $isNeg = true;
            }
            $clean = preg_replace('/[^\d.]/', '', $str);
            if ($clean === '' || !is_numeric($clean)) return null;
            $val = (float)$clean;
            return $isNeg ? -$val : $val;
        };

        foreach ($dataRows as $rOffset => $row) {
            $rowNum = $headerRowIdx + 1 + $rOffset + 1; // 1-based spreadsheet row number
            
            $rawStaffId = ($staffIdIdx !== -1 && isset($row[$staffIdIdx])) ? trim((string)$row[$staffIdIdx]) : '';
            $rawFileNo = ($fileNoIdx !== -1 && isset($row[$fileNoIdx])) ? trim((string)$row[$fileNoIdx]) : '';

            $nonEmptyCells = array_filter($row, fn($c) => trim((string)$c) !== '');
            if (empty($nonEmptyCells)) {
                continue;
            }

            if ($rawStaffId === '' && $rawFileNo === '') {
                continue;
            }

            $matchedStaff = null;
            if ($rawStaffId !== '' && isset($staffById[$rawStaffId])) {
                $matchedStaff = $staffById[$rawStaffId];
            } elseif ($rawStaffId !== '' && isset($staffByFileNo[strtolower($rawStaffId)])) {
                $matchedStaff = $staffByFileNo[strtolower($rawStaffId)];
            } elseif ($rawFileNo !== '' && isset($staffByFileNo[strtolower($rawFileNo)])) {
                $matchedStaff = $staffByFileNo[strtolower($rawFileNo)];
            } elseif ($rawFileNo !== '' && isset($staffById[$rawFileNo])) {
                $matchedStaff = $staffById[$rawFileNo];
            }

            if (!$matchedStaff) {
                $identifier = $rawStaffId ?: $rawFileNo;
                $warnings[] = "Row {$rowNum}: Active staff with identifier '{$identifier}' not found in records.";
                $parsedRows[] = [
                    'row_num' => $rowNum,
                    'staff_id' => $rawStaffId ?: $rawFileNo,
                    'file_no' => $rawFileNo,
                    'staff_name' => ($staffNameIdx !== -1 && isset($row[$staffNameIdx])) ? trim((string)$row[$staffNameIdx]) : 'Unknown',
                    'department' => ($deptIdx !== -1 && isset($row[$deptIdx])) ? trim((string)$row[$deptIdx]) : 'Unknown',
                    'designation' => '—',
                    'status' => 'warning',
                    'error_message' => "Staff not found in active records",
                    'current_gross' => 0,
                    'new_gross' => 0,
                    'increase_amount' => 0,
                    'increment_type' => null,
                    'increment_value' => null,
                    'effective_date' => null,
                    'reason' => null,
                ];
                continue;
            }

            $prevBasic = (float)($matchedStaff->basic_salary ?? 0);
            $prevGross = $prevBasic + (float)($matchedStaff->housing_allowance ?? 0) + (float)($matchedStaff->transport_allowance ?? 0)
                         + (float)($matchedStaff->medical_allowance ?? 0) + (float)($matchedStaff->utility_allowance ?? 0)
                         + (float)($matchedStaff->meal_allowance ?? 0);

            $numAmount = ($amountIdx !== -1 && isset($row[$amountIdx])) ? $parseSignedNumber($row[$amountIdx]) : null;
            $numPercent = ($percentIdx !== -1 && isset($row[$percentIdx])) ? $parseSignedNumber($row[$percentIdx]) : null;
            $numDecAmount = ($decrementAmountIdx !== -1 && isset($row[$decrementAmountIdx])) ? $parseSignedNumber($row[$decrementAmountIdx]) : null;
            $numDecPercent = ($decrementPercentIdx !== -1 && isset($row[$decrementPercentIdx])) ? $parseSignedNumber($row[$decrementPercentIdx]) : null;
            $numNewGross = ($newGrossIdx !== -1 && isset($row[$newGrossIdx])) ? $parseSignedNumber($row[$newGrossIdx]) : null;

            if ($numDecAmount !== null && $numDecAmount != 0) {
                $numAmount = -abs($numDecAmount);
            }
            if ($numDecPercent !== null && $numDecPercent != 0) {
                $numPercent = -abs($numDecPercent);
            }

            $rawEffectiveDate = ($effectiveDateIdx !== -1 && isset($row[$effectiveDateIdx])) ? trim((string)$row[$effectiveDateIdx]) : '';
            $rawReason = ($reasonIdx !== -1 && isset($row[$reasonIdx])) ? trim((string)$row[$reasonIdx]) : '';

            $incType = null;
            $incVal = null;
            $newGross = 0.00;
            $increaseAmount = 0.00;

            if ($numAmount !== null && $numAmount != 0) {
                if ($numAmount < 0) {
                    $incType = 'decrement_fixed';
                    $incVal = abs($numAmount);
                    $increaseAmount = round($numAmount, 2);
                    $newGross = round($prevGross + $numAmount, 2);
                } else {
                    $incType = 'fixed_amount';
                    $incVal = $numAmount;
                    $increaseAmount = round($numAmount, 2);
                    $newGross = round($prevGross + $numAmount, 2);
                }
            } elseif ($numPercent !== null && $numPercent != 0) {
                if ($numPercent < 0) {
                    $incType = 'decrement_percentage';
                    $incVal = abs($numPercent);
                    $increaseAmount = round($prevGross * ($numPercent / 100.0), 2);
                    $newGross = round($prevGross + $increaseAmount, 2);
                } else {
                    $incType = 'percentage';
                    $incVal = $numPercent;
                    $increaseAmount = round($prevGross * ($numPercent / 100.0), 2);
                    $newGross = round($prevGross + $increaseAmount, 2);
                }
            } elseif ($numNewGross !== null && $numNewGross > 0) {
                $newGross = round($numNewGross, 2);
                $increaseAmount = round($newGross - $prevGross, 2);
                $incType = ($increaseAmount < 0) ? 'decrement_new_gross' : 'new_gross';
                $incVal = $numNewGross;
            } else {
                $skippedCount++;
                $parsedRows[] = [
                    'row_num' => $rowNum,
                    'staff_id' => $matchedStaff->id,
                    'file_no' => $matchedStaff->file_no,
                    'staff_name' => trim($matchedStaff->name),
                    'department' => $matchedStaff->department ?? 'General',
                    'designation' => $matchedStaff->designation ?? 'Staff',
                    'status' => 'skipped',
                    'current_gross' => round($prevGross, 2),
                    'new_gross' => round($prevGross, 2),
                    'increase_amount' => 0,
                    'increment_type' => 'none',
                    'increment_value' => 0,
                    'effective_date' => $rawEffectiveDate ?: date('Y-m-d'),
                    'reason' => 'No adjustment specified',
                ];
                continue;
            }

            if ($newGross <= 0) {
                $warnings[] = "Row {$rowNum}: Resulting gross for '{$matchedStaff->name}' (ID: {$matchedStaff->id}) would be ₦" . number_format($newGross, 2) . ", which is invalid. Minimum gross must be greater than ₦0.00.";
                continue;
            }

            $validCount++;
            if ($increaseAmount > 0) {
                $incrementsCount++;
            } elseif ($increaseAmount < 0) {
                $decrementsCount++;
            }

            $totalPrevPayroll += $prevGross;
            $totalNewPayroll += $newGross;

            $deptName = $matchedStaff->department ?? 'General';
            if (!isset($deptAggregates[$deptName])) {
                $deptAggregates[$deptName] = [
                    'department' => $deptName,
                    'staff_count' => 0,
                    'total_increase' => 0.00,
                    'prev_payroll' => 0.00,
                    'new_payroll' => 0.00,
                    'increments_count' => 0,
                    'decrements_count' => 0,
                ];
            }
            $deptAggregates[$deptName]['staff_count']++;
            $deptAggregates[$deptName]['total_increase'] += $increaseAmount;
            $deptAggregates[$deptName]['prev_payroll'] += $prevGross;
            $deptAggregates[$deptName]['new_payroll'] += $newGross;
            if ($increaseAmount > 0) {
                $deptAggregates[$deptName]['increments_count']++;
            } elseif ($increaseAmount < 0) {
                $deptAggregates[$deptName]['decrements_count']++;
            }

            $parsedRows[] = [
                'row_num' => $rowNum,
                'staff_id' => $matchedStaff->id,
                'file_no' => $matchedStaff->file_no,
                'staff_name' => trim($matchedStaff->name),
                'department' => $deptName,
                'designation' => $matchedStaff->designation ?? 'Staff',
                'status' => 'valid',
                'current_gross' => round($prevGross, 2),
                'new_gross' => round($newGross, 2),
                'increase_amount' => round($increaseAmount, 2),
                'increment_type' => $incType,
                'increment_value' => $incVal,
                'effective_date' => $rawEffectiveDate ?: date('Y-m-d'),
                'reason' => $rawReason ?: ($increaseAmount < 0 ? "Spreadsheet decrement ({$deptName})" : "Spreadsheet increment ({$deptName})"),
                'prev_basic' => $prevBasic,
            ];
        }

        $deptSummaryList = array_values(array_map(function($d) {
            return [
                'department' => $d['department'],
                'staff_count' => $d['staff_count'],
                'total_increase' => round($d['total_increase'], 2),
                'prev_payroll' => round($d['prev_payroll'], 2),
                'new_payroll' => round($d['new_payroll'], 2),
                'increments_count' => $d['increments_count'],
                'decrements_count' => $d['decrements_count'],
                'avg_increase' => $d['staff_count'] > 0 ? round($d['total_increase'] / $d['staff_count'], 2) : 0,
            ];
        }, $deptAggregates));

        usort($deptSummaryList, fn($a, $b) => $b['staff_count'] <=> $a['staff_count']);

        return [
            'total_rows' => count($dataRows),
            'valid_count' => $validCount,
            'increments_count' => $incrementsCount,
            'decrements_count' => $decrementsCount,
            'skipped_count' => $skippedCount,
            'warning_count' => count($warnings),
            'total_prev_payroll' => round($totalPrevPayroll, 2),
            'total_new_payroll' => round($totalNewPayroll, 2),
            'total_monthly_increase' => round($totalNewPayroll - $totalPrevPayroll, 2),
            'departments_summary' => $deptSummaryList,
            'preview_rows' => array_slice($parsedRows, 0, 100),
            'all_rows' => $parsedRows,
            'warnings' => $warnings,
        ];
    }

    /**
     * GET /api/nextjs/payroll/salary-increments/template
     * Download pre-filled Excel or CSV template for bulk increments.
     */
    public function downloadTemplate(Request $request)
    {
        try {
            $format = strtolower($request->query('format', 'xlsx'));
            $departmentId = $request->query('department_id');

            $query = DB::table('tblper as p')
                ->leftJoin('tbldepartment as dept', 'dept.id', '=', 'p.departmentID')
                ->leftJoin('tbldesignation as des', 'des.id', '=', 'p.designation')
                ->leftJoin('salary_structures as ss', 'ss.staffId', '=', 'p.ID')
                ->where('p.rank', '!=', 2)
                ->where('p.staff_status', 1)
                ->select(
                    'p.ID as id',
                    'p.fileNo as file_no',
                    DB::raw("CONCAT(p.surname, ' ', p.first_name, ' ', COALESCE(p.othernames, '')) as name"),
                    'dept.id as department_id',
                    'dept.department',
                    'des.designation',
                    'ss.basic_salary',
                    'ss.housing_allowance',
                    'ss.transport_allowance',
                    'ss.medical_allowance',
                    'ss.utility_allowance',
                    'ss.meal_allowance'
                )
                ->orderBy('dept.department', 'asc')
                ->orderBy('p.surname', 'asc');

            if (!empty($departmentId)) {
                $query->where('p.departmentID', $departmentId);
            }

            $staff = $query->get()->map(function ($r) {
                $gross = (float)($r->basic_salary ?? 0) + (float)($r->housing_allowance ?? 0) +
                         (float)($r->transport_allowance ?? 0) + (float)($r->medical_allowance ?? 0) +
                         (float)($r->utility_allowance ?? 0) + (float)($r->meal_allowance ?? 0);
                return [
                    'id' => $r->id,
                    'file_no' => $r->file_no ?? '',
                    'name' => trim($r->name),
                    'department' => $r->department ?? 'General',
                    'designation' => $r->designation ?? 'Staff',
                    'current_gross' => round($gross, 2),
                ];
            });

            if ($format === 'csv') {
                $headers = [
                    'Content-Type'        => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="salary_increment_template.csv"',
                    'Pragma'              => 'no-cache',
                    'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                    'Expires'             => '0',
                ];

                $columns = [
                    'Staff ID', 'Department', 'Increment Amount', 'Increment Percentage', 'Effective Date'
                ];

                $callback = function () use ($columns, $staff) {
                    $handle = fopen('php://output', 'w');
                    fputcsv($handle, $columns);
                    foreach ($staff as $s) {
                        fputcsv($handle, [
                            $s['id'],
                            $s['department'],
                            '',
                            '',
                            date('Y-m-d'),
                        ]);
                    }
                    fclose($handle);
                };

                return response()->stream($callback, 200, $headers);
            }

            // Excel (.xlsx) format
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Salary Increment Template');

            $columns = [
                'Staff ID', 'Department', 'Increment Amount (₦)', 'Increment Percentage (%)', 'Effective Date (YYYY-MM-DD)'
            ];
            $totalCols = count($columns);
            $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

            // Row 1: Title
            $sheet->mergeCells("A1:{$lastColLetter}1");
            $sheet->setCellValue('A1', 'ISALU HOSPITALS — SALARY INCREMENT BULK UPLOAD TEMPLATE');
            $sheet->getStyle('A1')->applyFromArray([
                'font'      => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F172A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension(1)->setRowHeight(26);

            // Row 2: Instructions
            $sheet->mergeCells("A2:{$lastColLetter}2");
            $sheet->setCellValue('A2', 'Instructions: Enter Increment (+) or Decrement (-) Amount (₦) [e.g. 25000 or -15000] OR Percentage (%) [e.g. 10 or -5]. You can set different amounts/percentages for different departments. Leave rows blank or 0 for unchanged staff.');
            $sheet->getStyle('A2')->applyFromArray([
                'font'      => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '1E293B']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ]);
            $sheet->getRowDimension(2)->setRowHeight(28);

            // Row 3: Headers
            foreach ($columns as $i => $colName) {
                $cl = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
                $sheet->setCellValue("{$cl}3", $colName);
            }
            $sheet->getStyle("A3:{$lastColLetter}3")->applyFromArray([
                'font'      => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
            ]);
            $sheet->getRowDimension(3)->setRowHeight(24);

            $rowNum = 4;
            $defaultDate = date('Y-m-d');
            foreach ($staff as $s) {
                $sheet->setCellValue("A{$rowNum}", $s['id']);
                $sheet->setCellValue("B{$rowNum}", $s['department']);
                $sheet->setCellValue("C{$rowNum}", '');
                $sheet->setCellValue("D{$rowNum}", '');
                $sheet->setCellValue("E{$rowNum}", $defaultDate);

                $sheet->getRowDimension($rowNum)->setRowHeight(18);
                $rowNum++;
            }

            $endRow = $rowNum - 1;
            if ($endRow >= 4) {
                $sheet->getStyle("C4:C{$endRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("D4:D{$endRow}")->getNumberFormat()->setFormatCode('#,##0.0');

                $sheet->getStyle("A4:A{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C4:D{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("E4:E{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("C4:D{$endRow}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0FDF4']],
                ]);

                $sheet->getStyle("A4:{$lastColLetter}{$endRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                    'font'    => ['size' => 9],
                ]);
            }

            for ($c = 1; $c <= $totalCols; $c++) {
                $cl = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
                $sheet->getColumnDimension($cl)->setAutoSize(true);
            }

            $sheet->freezePane('A4');
            $sheet->setAutoFilter("A3:{$lastColLetter}3");

            $filename = "Salary_Increment_Bulk_Template_" . date('Y_m_d') . ".xlsx";
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
            Log::error('SalaryIncrementAPI downloadTemplate: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/salary-increments/preview-upload
     * Parse and preview bulk increment spreadsheet without committing.
     */
    public function previewUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt'
        ]);

        try {
            $parsed = $this->parseIncrementSpreadsheet($request->file('file'));

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_rows' => $parsed['total_rows'],
                    'valid_count' => $parsed['valid_count'],
                    'increments_count' => $parsed['increments_count'] ?? 0,
                    'decrements_count' => $parsed['decrements_count'] ?? 0,
                    'skipped_count' => $parsed['skipped_count'],
                    'warning_count' => $parsed['warning_count'],
                    'total_prev_payroll' => $parsed['total_prev_payroll'],
                    'total_new_payroll' => $parsed['total_new_payroll'],
                    'total_monthly_increase' => $parsed['total_monthly_increase'],
                    'departments_summary' => $parsed['departments_summary'],
                    'preview_rows' => $parsed['preview_rows'],
                    'warnings' => $parsed['warnings'],
                ]
            ]);
        } catch (\Throwable $th) {
            Log::error('SalaryIncrementAPI previewUpload: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 422);
        }
    }

    /**
     * POST /api/nextjs/payroll/salary-increments/upload
     * Bulk upload increment spreadsheet (.xlsx, .xls, .csv).
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt',
            'default_effective_date' => 'nullable|string',
            'default_reason' => 'nullable|string',
        ]);

        try {
            $ctx = $this->getUserContext($request);
            $userId = $ctx ? $ctx->id : null;

            $parsed = $this->parseIncrementSpreadsheet($request->file('file'));

            if ($parsed['valid_count'] === 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No valid salary increments found in the spreadsheet to apply.',
                    'warnings' => $parsed['warnings'],
                ], 422);
            }

            $batchId = 'IMPORT-' . strtoupper(Str::random(10));
            $defaultDate = $request->input('default_effective_date') ?: date('Y-m-d');
            $defaultReason = $request->input('default_reason') ?: 'Spreadsheet bulk increment';

            $updatedCount = 0;

            DB::beginTransaction();

            foreach ($parsed['all_rows'] as $r) {
                if ($r['status'] !== 'valid') continue;

                $sid = $r['staff_id'];
                $newGross = (float)$r['new_gross'];
                $prevGross = (float)$r['current_gross'];
                $increaseAmount = (float)$r['increase_amount'];
                $incType = $r['increment_type'];
                $incVal = $r['increment_value'];

                $percentage = ($incType === 'percentage' || $incType === 'decrement_percentage') ? (float)($incType === 'decrement_percentage' ? -$incVal : $incVal) : null;
                $amount = ($incType === 'fixed_amount' || $incType === 'decrement_fixed') ? (float)($incType === 'decrement_fixed' ? -$incVal : $incVal) : null;

                $effectiveDate = !empty($r['effective_date']) ? $r['effective_date'] : $defaultDate;
                $reason = !empty($r['reason']) ? $r['reason'] : $defaultReason;

                $calculatedFields = $this->calculateSalaryFields($newGross);

                $structureData = array_merge(['staffId' => $sid], $calculatedFields);

                $exists = DB::table('salary_structures')->where('staffId', $sid)->exists();
                if ($exists) {
                    DB::table('salary_structures')->where('staffId', $sid)->update($structureData);
                } else {
                    $structureData['created_at'] = now();
                    DB::table('salary_structures')->insert($structureData);
                }

                DB::table('tblper')->where('ID', $sid)->update(['staff_status' => 1]);

                DB::table('salary_increments')->insert([
                    'staff_id' => $sid,
                    'increment_type' => $incType,
                    'percentage' => $percentage,
                    'amount' => $amount,
                    'previous_gross_salary' => $prevGross,
                    'new_gross_salary' => $newGross,
                    'increase_amount' => $increaseAmount,
                    'previous_basic' => $r['prev_basic'] ?? $calculatedFields['basic_salary'],
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

            $actionText = ($parsed['decrements_count'] > 0 && $parsed['increments_count'] === 0) ? 'salary decrements' : (($parsed['decrements_count'] > 0 && $parsed['increments_count'] > 0) ? 'salary adjustments (increments & decrements)' : 'salary increments');

            return response()->json([
                'status' => 'success',
                'message' => "Successfully processed and applied {$actionText} to {$updatedCount} staff members across " . count($parsed['departments_summary']) . " departments.",
                'data' => [
                    'batch_id' => $batchId,
                    'updated_count' => $updatedCount,
                    'increments_count' => $parsed['increments_count'] ?? 0,
                    'decrements_count' => $parsed['decrements_count'] ?? 0,
                    'skipped_count' => $parsed['skipped_count'],
                    'warnings' => $parsed['warnings'],
                    'total_prev_payroll' => $parsed['total_prev_payroll'],
                    'total_new_payroll' => $parsed['total_new_payroll'],
                    'total_monthly_increase' => $parsed['total_monthly_increase'],
                    'departments_summary' => $parsed['departments_summary'],
                    'applied_rows' => array_values(array_filter($parsed['all_rows'], fn($r) => $r['status'] === 'valid')),
                ]
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('SalaryIncrementAPI upload: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/salary-increments/multi-department
     * Apply department-by-department increments in a single batch.
     */
    public function applyMultiDepartment(Request $request)
    {
        $validated = $request->validate([
            'departments' => 'required|array|min:1',
            'departments.*.department_id' => 'required|integer',
            'departments.*.increment_type' => 'required|string|in:percentage,fixed_amount,decrement_percentage,decrement_fixed,decrement_amount',
            'departments.*.percentage' => 'nullable|numeric',
            'departments.*.amount' => 'nullable|numeric',
            'departments.*.reason' => 'nullable|string|max:500',
            'effective_date' => 'nullable|string',
            'default_reason' => 'nullable|string|max:500',
        ]);

        try {
            $ctx = $this->getUserContext($request);
            $userId = $ctx ? $ctx->id : null;
            $batchId = 'MULTI-DEPT-' . strtoupper(Str::random(10));
            $effectiveDate = $validated['effective_date'] ?? date('Y-m-d');
            $defaultReason = $validated['default_reason'] ?? 'Departmental bulk salary review';

            $totalApplied = 0;
            $totalPrevPayroll = 0.00;
            $totalNewPayroll = 0.00;
            $deptResults = [];

            DB::beginTransaction();

            foreach ($validated['departments'] as $deptConfig) {
                $deptId = $deptConfig['department_id'];
                $incType = $deptConfig['increment_type'];
                $rawPercentage = isset($deptConfig['percentage']) ? (float)$deptConfig['percentage'] : null;
                $rawAmount = isset($deptConfig['amount']) ? (float)$deptConfig['amount'] : null;
                $deptReason = $deptConfig['reason'] ?? $defaultReason;

                $isDecrementPct = ($incType === 'decrement_percentage' || ($incType === 'percentage' && $rawPercentage !== null && $rawPercentage < 0));
                $isDecrementAmt = ($incType === 'decrement_fixed' || $incType === 'decrement_amount' || ($incType === 'fixed_amount' && $rawAmount !== null && $rawAmount < 0));

                $percentage = null;
                $amount = null;

                if ($isDecrementPct) {
                    $pct = abs($rawPercentage ?? 0);
                    if ($pct <= 0 || $pct >= 100) continue;
                    $percentage = -$pct;
                    $incType = 'decrement_percentage';
                } elseif ($incType === 'percentage') {
                    if ($rawPercentage === null || $rawPercentage <= 0) continue;
                    $percentage = $rawPercentage;
                } elseif ($isDecrementAmt) {
                    $amt = abs($rawAmount ?? 0);
                    if ($amt <= 0) continue;
                    $amount = -$amt;
                    $incType = 'decrement_fixed';
                } else {
                    if ($rawAmount === null || $rawAmount <= 0) continue;
                    $amount = $rawAmount;
                }

                $staffMembers = DB::table('tblper')
                    ->where('departmentID', $deptId)
                    ->where('rank', '!=', 2)
                    ->where('staff_status', 1)
                    ->pluck('ID')
                    ->toArray();

                if (empty($staffMembers)) continue;

                $deptName = DB::table('tbldepartment')->where('id', $deptId)->value('department') ?? "Dept #{$deptId}";

                $structures = DB::table('salary_structures')
                    ->whereIn('staffId', $staffMembers)
                    ->get()
                    ->keyBy('staffId');

                $deptStaffCount = 0;
                $deptPrevPayroll = 0.00;
                $deptNewPayroll = 0.00;

                foreach ($staffMembers as $sid) {
                    $struct = $structures[$sid] ?? null;
                    $prevGross = 0.00;
                    $prevBasic = 0.00;

                    if ($struct) {
                        $prevBasic = (float)$struct->basic_salary;
                        $prevGross = $prevBasic + (float)$struct->housing_allowance + (float)$struct->transport_allowance +
                                     (float)$struct->medical_allowance + (float)$struct->utility_allowance + (float)$struct->meal_allowance;
                    }

                    if ($prevGross <= 0) continue;

                    $newGross = 0.00;
                    if ($incType === 'decrement_percentage') {
                        $newGross = round($prevGross * (1.0 - (abs($percentage) / 100.0)), 2);
                    } elseif ($incType === 'percentage') {
                        $newGross = round($prevGross * (1.0 + ($percentage / 100.0)), 2);
                    } elseif ($incType === 'decrement_fixed') {
                        $newGross = round($prevGross - abs($amount), 2);
                    } else {
                        $newGross = round($prevGross + $amount, 2);
                    }

                    if ($newGross <= 0) continue;

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
                        'increment_type' => $incType,
                        'percentage' => $percentage,
                        'amount' => $amount,
                        'previous_gross_salary' => $prevGross,
                        'new_gross_salary' => $newGross,
                        'increase_amount' => $increaseAmount,
                        'previous_basic' => $prevBasic,
                        'new_basic' => $calculatedFields['basic_salary'],
                        'effective_date' => $effectiveDate,
                        'reason' => $deptReason,
                        'batch_id' => $batchId,
                        'created_by' => $userId,
                        'status' => 'applied',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $deptStaffCount++;
                    $deptPrevPayroll += $prevGross;
                    $deptNewPayroll += $newGross;
                }

                $totalApplied += $deptStaffCount;
                $totalPrevPayroll += $deptPrevPayroll;
                $totalNewPayroll += $deptNewPayroll;

                $deptResults[] = [
                    'department_id' => $deptId,
                    'department' => $deptName,
                    'staff_count' => $deptStaffCount,
                    'increment_type' => $incType,
                    'increment_value' => ($incType === 'percentage' || $incType === 'decrement_percentage') ? $percentage : $amount,
                    'total_increase' => round($deptNewPayroll - $deptPrevPayroll, 2),
                ];
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Multi-department salary increment successfully applied to {$totalApplied} staff members across " . count($deptResults) . " departments.",
                'data' => [
                    'batch_id' => $batchId,
                    'affected_count' => $totalApplied,
                    'previous_total_payroll' => round($totalPrevPayroll, 2),
                    'new_total_payroll' => round($totalNewPayroll, 2),
                    'total_monthly_increase' => round($totalNewPayroll - $totalPrevPayroll, 2),
                    'departments' => $deptResults,
                ]
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('SalaryIncrementAPI applyMultiDepartment: ' . $th->getMessage());
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

                $amountColor = ((float)$r->increase_amount < 0) ? 'DC2626' : '008000';
                $sheet->getStyle("I{$rowNum}")->applyFromArray([
                    'font' => ['color' => ['rgb' => $amountColor], 'bold' => true, 'size' => 8],
                ]);

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
