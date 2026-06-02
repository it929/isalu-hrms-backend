<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class StaffControlVariableApiController extends Controller
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
     * GET /api/nextjs/payroll/staff-control-variables/staff
     */
    public function getStaffList(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized – X-User-Id header is required.'], 401);
            }

            $query = DB::table('tblper')
                ->where('rank', '!=', 2)
                ->where('staff_status', 1)
                ->select('ID as id', 'fileNo', 'surname', 'first_name', 'othernames')
                ->orderBy('surname', 'asc');

            // Non-admins can only select themselves
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                if ($ctx['employee']) {
                    $query->where('ID', $ctx['employee']->ID);
                } else {
                    $query->where('ID', 0);
                }
            }

            $staff = $query->get()
                ->map(function ($row) {
                    $fullName = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                    return [
                        'id' => $row->id,
                        'fileNo' => $row->fileNo ?? '',
                        'name' => $fullName,
                        'label' => $fullName,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $staff
            ]);
        } catch (\Throwable $th) {
            Log::error('StaffControlVariableApiController getStaffList: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/staff-control-variables/variable-types
     */
    public function getVariableTypes(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $types = DB::table('tblearningParticular')
                ->select('ID as id', 'Particular as name')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $types
            ]);
        } catch (\Throwable $th) {
            Log::error('StaffControlVariableApiController getVariableTypes: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/staff-control-variables/descriptions/{particularId}
     */
    public function getDescriptions(Request $request, $particularId)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $descriptions = DB::table('tblcvSetup')
                ->where('particularID', $particularId)
                ->where('status', 1)
                ->select('ID as id', 'description')
                ->orderBy('description', 'asc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $descriptions
            ]);
        } catch (\Throwable $th) {
            Log::error('StaffControlVariableApiController getDescriptions: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/nextjs/payroll/staff-control-variables
     */
    public function index(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $search = trim($request->input('search', ''));
            $query = DB::table('staffEarningAndDeduction as sc')
                ->join('tblper as p', 'p.ID', '=', 'sc.staffId')
                ->leftJoin('tbldepartment as d', 'd.id', '=', 'p.departmentID')
                ->select(
                    'sc.*',
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
                      ->orWhere('sc.description', 'like', "%{$search}%");
                });
            }

            $employee = $ctx['employee'];

            if ($ctx['isSuperAdmin'] || $ctx['isAdminStaff'] || $ctx['isAuditStaff']) {
                // Admin and Audit see all
            } elseif ($employee && $employee->is_hod == 1) {
                // HOD sees department staff
                $query->where('p.departmentID', $employee->departmentID);
            } elseif ($employee) {
                // Regular staff see only their own
                $query->where('sc.staffId', $employee->ID);
            } else {
                $query->where('sc.id', 0);
            }

            $records = $query->orderBy('sc.id', 'desc')->get()->map(function ($row) {
                return [
                    'id'            => $row->id,
                    'staffId'       => $row->staffId,
                    'name'          => trim("{$row->surname} {$row->first_name} {$row->othernames}"),
                    'fileNo'        => $row->fileNo ?? '',
                    'department'    => $row->department ?? '',
                    'variable_type' => $row->variable_type,
                    'cv_setup_id'   => $row->cv_setup_id,
                    'description'   => $row->description,
                    'amount'        => (float) $row->amount,
                    'target_amount' => $row->target_amount !== null ? (float) $row->target_amount : null,
                    'no_limit'      => (int) $row->no_limit,
                    'one_time'      => (int) $row->one_time,
                    'status'        => (int) ($row->status ?? 1),
                    'created_at'    => $row->created_at,
                    'updated_at'    => $row->updated_at,
                ];
            });

            return response()->json([
                'status'       => 'success',
                'data'         => $records,
                'isSuperAdmin' => $ctx['isSuperAdmin'],
                'isAdminStaff' => $ctx['isAdminStaff'],
                'isAuditStaff' => $ctx['isAuditStaff'],
                'isHod'        => $ctx['isHod'],
                'employee'     => $employee ? [
                    'id'           => $employee->ID,
                    'departmentID' => $employee->departmentID,
                ] : null,
            ]);
        } catch (\Throwable $th) {
            Log::error('StaffControlVariableApiController index: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/staff-control-variables
     */
    public function store(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $request->validate([
                'staffId'       => 'required|integer',
                'variable_type' => 'required|string',
                'cv_setup_id'   => 'required|integer',
                'amount'        => 'required|numeric|min:0',
                'target_amount' => 'nullable|numeric|min:0',
                'no_limit'      => 'boolean',
                'one_time'      => 'boolean',
                'status'        => 'nullable|boolean',
            ]);

            $staffId      = $request->input('staffId');
            $variableType = $request->input('variable_type');
            $cvSetupId    = $request->input('cv_setup_id');
            $amount       = (float) $request->input('amount');
            $noLimit      = $request->input('no_limit', false) ? 1 : 0;
            $oneTime      = $request->input('one_time', false) ? 1 : 0;
            $status       = $request->input('status', true) ? 1 : 0;

            // One-Time automatically overrides target amount to amount
            $targetAmount = $oneTime ? $amount : ($request->input('target_amount') !== null ? (float) $request->input('target_amount') : null);

            // Fetch description and verify cvID
            $cvSetup = DB::table('tblcvSetup')->where('ID', $cvSetupId)->first();
            if (!$cvSetup) {
                return response()->json(['status' => 'error', 'message' => 'Invalid Description selected.'], 400);
            }

            // Verify staff member
            $staff = DB::table('tblper')->where('ID', $staffId)->first();
            if (!$staff) {
                return response()->json(['status' => 'error', 'message' => 'Invalid staff member selected.'], 400);
            }

            // If user is not admin, they can only apply for themselves
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                if (!$ctx['employee'] || $ctx['employee']->ID !== (int)$staffId) {
                    return response()->json(['status' => 'error', 'message' => 'Unauthorized to submit control variables for other staff members.'], 403);
                }
            }

            $id = $request->input('id');

            $data = [
                'staffId'       => $staffId,
                'variable_type' => $variableType,
                'cv_setup_id'   => $cvSetupId,
                'description'   => $cvSetup->description,
                'amount'        => $amount,
                'target_amount' => $targetAmount,
                'no_limit'      => $noLimit,
                'one_time'      => $oneTime,
                'status'        => $status,
                'updated_at'    => now(),
            ];

            if ($id) {
                // Update
                $exists = DB::table('staffEarningAndDeduction')->where('id', $id)->first();
                if (!$exists) {
                    return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
                }

                // If non-admin, ensure they own the record
                if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                    if ($exists->staffId !== $ctx['employee']->ID) {
                        return response()->json(['status' => 'error', 'message' => 'Unauthorized to edit this record.'], 403);
                    }
                }

                DB::table('staffEarningAndDeduction')->where('id', $id)->update($data);
                $message = 'Record updated successfully.';
            } else {
                // Insert
                $data['created_at'] = now();
                DB::table('staffEarningAndDeduction')->insert($data);
                $message = 'Record created successfully.';
            }

            return response()->json([
                'status'  => 'success',
                'message' => $message,
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed.',
                'errors'  => $ve->errors()
            ], 422);
        } catch (\Throwable $th) {
            Log::error('StaffControlVariableApiController store: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/nextjs/payroll/staff-control-variables/{id}
     */
    public function destroy(Request $request, $id)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            $record = DB::table('staffEarningAndDeduction')->where('id', $id)->first();
            if (!$record) {
                return response()->json(['status' => 'error', 'message' => 'Record not found.'], 404);
            }

            // Check authorization
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                if (!$ctx['employee'] || $record->staffId !== $ctx['employee']->ID) {
                    return response()->json(['status' => 'error', 'message' => 'Unauthorized to delete this record.'], 403);
                }
            }

            DB::table('staffEarningAndDeduction')->where('id', $id)->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Record deleted successfully.',
            ]);
        } catch (\Throwable $th) {
            Log::error('StaffControlVariableApiController destroy: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/staff-control-variables/import
     * Bulk import staff control variables (earnings and deductions) via Excel/CSV.
     */
    public function import(Request $request)
    {
        try {
            $ctx = $this->getUserContext($request);
            if (!$ctx) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
            }

            // Administrative rights check
            if (!$ctx['isSuperAdmin'] && !$ctx['isAdminStaff']) {
                return response()->json(['status' => 'error', 'message' => 'Forbidden – Administrative rights required.'], 403);
            }

            $request->validate([
                'excel_file' => 'required|file'
            ]);

            $file = $request->file('excel_file');
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid file format. Only .xlsx, .xls, and .csv files are allowed.'
                ], 422);
            }

            $rows = Excel::toArray([], $file)[0];
            if (empty($rows) || count($rows) <= 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The uploaded spreadsheet is empty or contains no records.'
                ], 422);
            }

            // Normalize headers from the first row
            $headers = array_map(function ($h) {
                return strtolower(trim((string)$h));
            }, $rows[0]);

            $staffIndex = -1;
            $descIndex = -1;
            $amountIndex = -1;
            $targetIndex = -1;
            $noLimitIndex = -1;
            $oneTimeIndex = -1;

            foreach ($headers as $index => $header) {
                if (in_array($header, ['staff id', 'staff_id', 'id', 'staffid'])) {
                    $staffIndex = $index;
                } elseif (in_array($header, ['description', 'cv setup', 'setup', 'variable', 'name'])) {
                    $descIndex = $index;
                } elseif (in_array($header, ['amount', 'value', 'amt', 'monthly amount'])) {
                    $amountIndex = $index;
                } elseif (in_array($header, ['target amount', 'target_amount', 'limit', 'target'])) {
                    $targetIndex = $index;
                } elseif (in_array($header, ['no limit', 'no_limit', 'unlimited'])) {
                    $noLimitIndex = $index;
                } elseif (in_array($header, ['one time', 'one_time', 'one-time'])) {
                    $oneTimeIndex = $index;
                }
            }

            // Default fallback indices if headers didn't match
            if ($staffIndex === -1) $staffIndex = 0;
            if ($descIndex === -1) $descIndex = 1;
            if ($amountIndex === -1) $amountIndex = 2;
            if ($targetIndex === -1) $targetIndex = 3;
            if ($noLimitIndex === -1) $noLimitIndex = 4;
            if ($oneTimeIndex === -1) $oneTimeIndex = 5;

            $successCount = 0;
            $warnings = [];

            // Pre-fetch all active setup variables and variable types to limit DB queries
            $cvSetups = DB::table('tblcvSetup')->where('status', 1)->get();
            $varTypes = DB::table('tblearningParticular')->get()->pluck('Particular', 'ID')->toArray();

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

                $staffVal = isset($row[$staffIndex]) ? trim((string)$row[$staffIndex]) : '';
                $descVal = isset($row[$descIndex]) ? trim((string)$row[$descIndex]) : '';
                $amountVal = isset($row[$amountIndex]) ? trim((string)$row[$amountIndex]) : '';
                $targetVal = isset($row[$targetIndex]) ? trim((string)$row[$targetIndex]) : '';
                $noLimitVal = isset($row[$noLimitIndex]) ? trim((string)$row[$noLimitIndex]) : '';
                $oneTimeVal = isset($row[$oneTimeIndex]) ? trim((string)$row[$oneTimeIndex]) : '';

                if ($staffVal === '' || $descVal === '' || $amountVal === '') {
                    $warnings[] = "Row " . ($r + 1) . ": Missing required fields (Staff ID, Description, or Amount).";
                    continue;
                }

                // Match Staff by ID
                $staff = null;
                if (is_numeric($staffVal)) {
                    $staff = DB::table('tblper')->where('ID', intval($staffVal))->first();
                } else {
                    // Fallback to fileNo search if non-numeric
                    $staff = DB::table('tblper')->where('fileNo', $staffVal)->first();
                }

                if (!$staff) {
                    $warnings[] = "Row " . ($r + 1) . ": Staff with identifier '{$staffVal}' not found in database.";
                    continue;
                }

                // Match Description case-insensitively
                $matchedCv = null;
                foreach ($cvSetups as $cv) {
                    if (strcasecmp($cv->description, $descVal) === 0) {
                        $matchedCv = $cv;
                        break;
                    }
                }

                if (!$matchedCv) {
                    $warnings[] = "Row " . ($r + 1) . ": CV Description '{$descVal}' is invalid or inactive.";
                    continue;
                }

                // Validate Amount
                if (!is_numeric($amountVal) || floatval($amountVal) < 0) {
                    $warnings[] = "Row " . ($r + 1) . ": Invalid Amount value '{$amountVal}'.";
                    continue;
                }
                $amount = floatval($amountVal);

                // Checkboxes
                $oneTime = 0;
                if ($oneTimeVal !== '') {
                    $otLower = strtolower($oneTimeVal);
                    if ($otLower === 'yes' || $otLower === '1' || $otLower === 'true') {
                        $oneTime = 1;
                    }
                }

                $noLimit = 0;
                $targetAmount = null;

                if ($oneTime) {
                    $noLimit = 0;
                    if ($targetVal !== '' && is_numeric($targetVal)) {
                        $targetAmount = floatval($targetVal);
                    } else {
                        $targetAmount = $amount;
                    }
                } else {
                    // Check if No Limit is explicitly specified
                    $explicitNoLimit = null;
                    if ($noLimitVal !== '') {
                        $nlLower = strtolower($noLimitVal);
                        if ($nlLower === 'yes' || $nlLower === '1' || $nlLower === 'true') {
                            $explicitNoLimit = true;
                        } elseif ($nlLower === 'no' || $nlLower === '0' || $nlLower === 'false') {
                            $explicitNoLimit = false;
                        }
                    }

                    if ($explicitNoLimit === true) {
                        $noLimit = 1;
                        if ($targetVal !== '' && is_numeric($targetVal)) {
                            $targetAmount = floatval($targetVal);
                        } else {
                            $targetAmount = null;
                        }
                    } elseif ($explicitNoLimit === false) {
                        $noLimit = 0;
                        if ($targetVal !== '' && is_numeric($targetVal)) {
                            $targetAmount = floatval($targetVal);
                        } else {
                            $targetAmount = null;
                        }
                    } else {
                        // If not explicitly specified, deduce from Target Amount
                        if ($targetVal !== '' && is_numeric($targetVal) && floatval($targetVal) > 0) {
                            $noLimit = 0;
                            $targetAmount = floatval($targetVal);
                        } else {
                            $noLimit = 1;
                            $targetAmount = null;
                        }
                    }
                }

                // Map Variable Type
                $particularID = $matchedCv->particularID;
                $variableType = isset($varTypes[$particularID]) ? $varTypes[$particularID] : 'Earning';

                // Check if existing record exists
                $existing = DB::table('staffEarningAndDeduction')
                    ->where('staffId', $staff->ID)
                    ->where('cv_setup_id', $matchedCv->ID)
                    ->first();

                $data = [
                    'staffId'       => $staff->ID,
                    'variable_type' => $variableType,
                    'cv_setup_id'   => $matchedCv->ID,
                    'description'   => $matchedCv->description,
                    'amount'        => $amount,
                    'target_amount' => $targetAmount,
                    'no_limit'      => $noLimit,
                    'one_time'      => $oneTime,
                    'updated_at'    => now(),
                ];

                if ($existing) {
                    DB::table('staffEarningAndDeduction')
                        ->where('id', $existing->id)
                        ->update($data);
                } else {
                    $data['created_at'] = now();
                    $data['total_deducted'] = 0.00;
                    DB::table('staffEarningAndDeduction')->insert($data);
                }

                $successCount++;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Successfully imported {$successCount} control variable records.",
                'imported_count' => $successCount,
                'warnings' => $warnings
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('StaffControlVariableApiController import: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
