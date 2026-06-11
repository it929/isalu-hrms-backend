<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BankUpdateApiController extends Controller
{
    use ResolveUserContextTrait;

    /**
     * Fetch filter options: banks and staff list.
     */
    public function getMetadata(Request $request)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        try {
            $banks = DB::table('tblbanklist')
                ->select('bankID as id', 'bank as name')
                ->orderBy('bank', 'asc')
                ->get();

            $staff = DB::table('tblper')
                ->where('rank', '!=', 2)
                ->where('staff_status', 1)
                ->select('ID as id', 'fileNo', 'surname', 'first_name', 'othernames')
                ->orderBy('surname', 'asc')
                ->get()
                ->map(function ($row) {
                    $fullName = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                    return [
                        'id' => $row->id,
                        'fileNo' => $row->fileNo ?? '',
                        'name' => $fullName,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'banks' => $banks,
                'staff' => $staff,
            ]);
        } catch (\Throwable $th) {
            Log::error('BankUpdateApiController getMetadata: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * Update bank details for an individual staff member.
     */
    public function updateIndividual(Request $request)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx || !$ctx['isSuperAdmin']) {
            return response()->json(['status' => 'error', 'message' => 'Super Admin privileges required.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'staff_id' => 'required|integer|exists:tblper,ID',
            'bank_id' => 'required|integer|exists:tblbanklist,bankID',
            'account_number' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::table('tblper')
                ->where('ID', $request->staff_id)
                ->update([
                    'bankID' => $request->bank_id,
                    'AccNo' => $request->account_number,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Bank details updated successfully for the staff member.'
            ]);
        } catch (\Throwable $th) {
            Log::error('BankUpdateApiController updateIndividual: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * Bulk update bank details via Excel/CSV import.
     */
    public function importBulk(Request $request)
    {
        $ctx = $this->getUserContext($request);
        if (!$ctx || !$ctx['isSuperAdmin']) {
            return response()->json(['status' => 'error', 'message' => 'Super Admin privileges required.'], 403);
        }

        $request->validate([
            'excel_file' => 'required|file',
            'bank_id' => 'required|integer|exists:tblbanklist,bankID',
        ]);

        $file = $request->file('excel_file');
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid file format. Only .xlsx, .xls, and .csv files are allowed.'
            ], 422);
        }

        try {
            $rows = \Maatwebsite\Excel\Facades\Excel::toArray([], $file)[0];
            if (empty($rows) || count($rows) <= 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The uploaded spreadsheet is empty or contains no records.'
                ], 422);
            }

            // Normalize headers from the first row
            $headers = array_map(function ($h) {
                return strtolower(trim(str_replace([' ', '_', '-'], '', (string)$h)));
            }, $rows[0]);

            $staffIdIndex = -1;
            $accountNumberIndex = -1;

            foreach ($headers as $index => $header) {
                if (in_array($header, ['staffid', 'id'])) {
                    $staffIdIndex = $index;
                } elseif (in_array($header, ['accountnumber', 'accountno', 'accno', 'accountnum'])) {
                    $accountNumberIndex = $index;
                }
            }

            if ($staffIdIndex === -1 || $accountNumberIndex === -1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Spreadsheet must contain "staffId" and "Account Number" columns.'
                ], 422);
            }

            $successCount = 0;
            $notFoundIds = [];

            DB::beginTransaction();

            // Loop starting from row 1 (skipping headers)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $staffId = trim($row[$staffIdIndex] ?? '');
                $accountNumber = trim($row[$accountNumberIndex] ?? '');

                if (empty($staffId) || empty($accountNumber)) {
                    continue;
                }

                $exists = DB::table('tblper')->where('ID', $staffId)->exists();
                if (!$exists) {
                    $notFoundIds[] = $staffId;
                    continue;
                }

                DB::table('tblper')
                    ->where('ID', $staffId)
                    ->update([
                        'bankID' => $request->bank_id,
                        'AccNo' => $accountNumber,
                        'updated_at' => now(),
                    ]);

                $successCount++;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Bulk bank updates processed successfully.',
                'summary' => [
                    'updated' => $successCount,
                    'not_found' => $notFoundIds,
                ]
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('BankUpdateApiController importBulk: ' . $th->getMessage());
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
