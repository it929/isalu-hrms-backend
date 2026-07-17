<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class PayerIdApiController extends Controller
{
    /**
     * GET /api/nextjs/payroll/payer-id
     * Fetch existing active staff with file number, name, and Payer ID.
     */
    public function index(Request $request)
    {
        try {
            $search = trim($request->input('search', ''));
            $query = DB::table('tblper')
                ->where('staff_status', 1)
                ->where('rank', '!=', 2)
                ->select(
                    'ID as staffId',
                    'fileNo',
                    'surname',
                    'first_name',
                    'othernames',
                    'payer_id'
                );

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('fileNo', 'like', "%{$search}%")
                      ->orWhere('surname', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%")
                      ->orWhere('othernames', 'like', "%{$search}%");
                });
            }

            $records = $query->orderBy('ID', 'desc')->get()->map(function ($row) {
                $row->name = trim("{$row->surname} {$row->first_name} {$row->othernames}");
                return $row;
            });

            return response()->json([
                'status' => 'success',
                'data' => $records
            ]);
        } catch (\Throwable $th) {
            Log::error('PayerIdAPI index: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/payer-id
     * Save/update a single staff member's Payer ID.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'staffId' => 'required|integer',
                'payer_id' => 'nullable|string|max:100',
            ]);

            // Ensure the staff member exists in tblper
            $staffExists = DB::table('tblper')->where('ID', $validated['staffId'])->exists();
            if (!$staffExists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The selected staff member does not exist.'
                ], 422);
            }

            // Update payer_id in tblper
            DB::table('tblper')
                ->where('ID', $validated['staffId'])
                ->update([
                    'payer_id' => $validated['payer_id'],
                ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payer ID updated successfully.'
            ]);
        } catch (\Throwable $th) {
            Log::error('PayerIdAPI store: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/payroll/payer-id/import
     * Bulk update Payer IDs via Excel/CSV matching staff by staffId.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file'
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'file' => ['The file must be a file of type: xlsx, xls, csv.']
                ]
            ], 422);
        }

        try {
            $data = Excel::toArray([], $request->file('file'))[0];
            if (empty($data)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The uploaded file is empty.'
                ], 422);
            }

            // Headers mapping to match column order
            $headers = array_map(function ($h) {
                return strtolower(trim($h));
            }, $data[0]);

            $staffIdIndex = -1;
            $payerIdIndex = -1;

            foreach ($headers as $index => $header) {
                if (strpos($header, 'staff') !== false || strpos($header, 'id') !== false) {
                    if ($staffIdIndex === -1) $staffIdIndex = $index;
                } elseif (strpos($header, 'payer') !== false) {
                    if ($payerIdIndex === -1) $payerIdIndex = $index;
                }
            }

            // Fallbacks to default column indices
            if ($staffIdIndex === -1) $staffIdIndex = 0;
            if ($payerIdIndex === -1) $payerIdIndex = 1;

            unset($data[0]); // Remove the header row

            $updatedCount = 0;
            $warnings = [];

            DB::beginTransaction();

            foreach ($data as $rowIndex => $row) {
                // Skip empty row or if staffId is missing
                if (!isset($row[$staffIdIndex]) || trim($row[$staffIdIndex]) === '') {
                    continue;
                }

                $staffId = intval(trim($row[$staffIdIndex]));
                $payerVal = trim($row[$payerIdIndex] ?? '');

                // Check if staff exists in tblper
                $staff = DB::table('tblper')->where('ID', $staffId)->first();
                if (!$staff) {
                    $warnings[] = "Row " . ($rowIndex + 1) . ": Staff with identifier '{$staffId}' does not exist.";
                    continue;
                }

                // Update payer_id
                DB::table('tblper')
                    ->where('ID', $staffId)
                    ->update([
                        'payer_id' => $payerVal !== '' ? $payerVal : null,
                    ]);

                $updatedCount++;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Successfully processed {$updatedCount} records.",
                'updated_count' => $updatedCount,
                'warnings' => $warnings
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('PayerIdAPI import: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
