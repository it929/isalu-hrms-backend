<?php

namespace App\Http\Controllers\funds;



use App\Helpers\FileUploadHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\funds\function24Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReconciliationController extends BasefunctionController
{
    private $instanceFunction24;

    public function __construct()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '0');
        $this->instanceFunction24 = new function24Controller;
    }

    public function index()
    {
        $data['mandate'] = [];
        session::forget('reconciliation_from');
        session::forget('reconciliation_to');
        session::forget('reconciliation_bank_id');

        $data['mandateAccounts'] = DB::table('tblmandate_address_account as maa')
            ->leftJoin('tblbanklist as b', 'b.bankID', '=', 'maa.bankId')
            ->leftJoin('tblcontractType', 'tblcontractType.ID', '=', 'maa.contractTypeID')
            ->where('maa.status', 1)
            ->select(
                'maa.*',
                'b.bank',
                'tblcontractType.contractType'
            )
            ->get();



        return view('funds/reconciliation/search', $data);
    }

    public function reconciliationSearchResultold(Request $request)
    {
        Session::put('reconciliation_from', $request->dateFrom);
        Session::put('reconciliation_to', $request->dateTo);
        $data['mandate'] = DB::table('tblepayment')
            ->where('mandate_status', '!=', 3)
            ->whereBetween('date', [$request->dateFrom, $request->dateTo])
            ->get();
        // dd($data);
        return view('funds/reconciliation/search', $data);
    }

    public function reconciliationSearchResult(Request $request)
    {
        Session::put('reconciliation_from', $request->dateFrom);
        Session::put('reconciliation_to', $request->dateTo);
        Session::put('reconciliation_bank_id', $request->bank_id);

        $mandate = DB::table('tblepayment_bank_paid')
            ->where('mandate_status', '!=', 3)
            ->where('NJCAccount',  $request->bank_id)
            ->whereBetween('date', [$request->dateFrom, $request->dateTo])
            ->get();


        $mandateAccounts = DB::table('tblmandate_address_account as maa')
            ->leftJoin('tblbanklist as b', 'b.bankID', '=', 'maa.bankId')
            ->leftJoin('tblcontractType', 'tblcontractType.ID', '=', 'maa.contractTypeID')
            ->where('maa.status', 1)
            ->select(
                'maa.*',
                'b.bank',
                'tblcontractType.contractType'
            )
            ->get();



        // Trim mandate fields
        $trimmedMandates = $mandate->map(function ($m) {
            return [
                'itemid'  => (string) Str::uuid(),
                'details' => trim($m->purpose . ' ' . $m->contractor . ' ' . $m->bank),
                'amount'  => (float) $m->amount,
                'date'    => $m->date,
            ];
        });

        // Convert only trimmed data to JSON
        $json = $trimmedMandates->toJson(JSON_PRETTY_PRINT);

        // File name
        $fileName = 'reconciliation_' . $request->dateFrom . '_to_' . $request->dateTo . '.json';

        // Save JSON file
        Storage::put('reconciliation/' . $fileName, $json);

        // Pass original mandates to view (for display)
        return view('funds/reconciliation/search', compact('mandate', 'fileName', 'mandateAccounts'));
    }

    public function sendReconciliationResultold(Request $request)
    {
        $request->validate([
            'rFrom' => "required",
            'rTo' => "required",
            'doc_url' => "required|mimes:pdf",
        ]);

        $pdfFile = $request->file('doc_url');
        $mandates = DB::table('tblepayment')
            ->where('mandate_status', '!=', 3)
            ->whereBetween('date', [$request->rFrom, $request->rTo])
            ->get();

        //send to api and receive response
        // Convert mandates to array (JSON format)
        $mandateJson = $mandates->toArray();

        // Prepare endpoint
        $externalApiUrl = "https://external-api.com/receive-reconciliation";

        // Send file + JSON to external API
        $response = Http::attach(
            'file',                     // field name in API
            file_get_contents($pdfFile),
            $pdfFile->getClientOriginalName()
        )
            ->post($externalApiUrl, [
                'mandates' => json_encode($mandateJson), // Send JSON
                'rFrom'    => $request->rFrom,
                'rTo'      => $request->rTo,
            ]);

        // Check API status
        if ($response->successful()) {
            return response()->json([
                'status' => true,
                'message' => 'Reconciliation sent successfully!',
                'api_response' => $response->json()
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'API Error',
            'error' => $response->body(),
        ], 500);
    }


    public function sendReconciliationResult(Request $request)
    {
        $request->validate([
            'rFrom' => "required",
            'rTo'   => "required",
            'doc_url' => "required|mimes:pdf",
        ]);

        // File
        $pdfFile = $request->file('doc_url');
        $customName = $request->input('rFrom') . "_" . $request->input('rTo') . "_" . (string) Str::uuid() . '.' . $pdfFile->getClientOriginalExtension();


        // Correct file name format
        $jsonFileName = "reconciliation_{$request->rFrom}_to_{$request->rTo}.json";
        $jsonFilePath = "reconciliation/" . $jsonFileName;

        // Make sure file exists
        if (!Storage::exists($jsonFilePath)) {
            return back()->with('error', "JSON file not found: {$jsonFileName}");
        }

        // Load JSON
        $mandateJson = Storage::get($jsonFilePath);

        // Log::info('Preparing to send reconciliation', [
        //     'rFrom' => $request->rFrom,
        //     'rTo' => $request->rTo,
        //     'jsonFilePath' => $jsonFilePath,
        // ]);

        // API URL
        $externalApiUrl = config('services.reconciliation.url');

        // Send to external API
        $response = Http::attach(
            'file',
            file_get_contents($pdfFile),
            $pdfFile->getClientOriginalName()
        )->post($externalApiUrl, [
            'record1' => $mandateJson,
            'rFrom'    => $request->rFrom,
            'rTo'      => $request->rTo,
        ]);

        // Log::info('Reconciliation API response', [
        //     'status' => $response->status(),
        //     'body' => $response->body(),
        // ]);

        // Use helper (automatically stores to local or S3)
        $fileUrl = FileUploadHelper::upload($pdfFile, 'reconciliations', $customName);


        $results = json_decode($response->body(), true);
        $matched = $results['data']['report']['matched'] ?? [];
        $unmatchedMandates = $results['data']['report']['unmatchedInRecord1'] ?? [];
        $unmatchedBankStatements = $results['data']['report']['unmatchedInRecord2'] ?? [];

        // Log::info('Matched:', $matched);
        // Log::info('Unmatched Mandates:', $unmatchedMandates);
        // Log::info('Unmatched Bank Statements:', $unmatchedBankStatements);

        $userId = Auth::id();

        // Prepare matched records
        // Generate batch number using rFrom, rTo, and a random UUID
        $batchNumber = 'REC_' . $request->rFrom . '_' . $request->rTo . '_' . substr(Str::uuid(), 0, 8);

        $insertData = [];

        foreach ($matched as $match) {

            // Generate a unique match ID for this pair
            $matchId = Str::uuid();

            // --- RECORD 1 (MANDATE) ---
            if (!empty($match['record1'])) {
                $r1 = $match['record1'];

                $insertData[] = [
                    'userId'             => $userId,
                    'itemId'             => $r1['itemid'] ?? null,
                    'description'        => $r1['details'] ?? null,
                    'status'             => 1, // mandate match
                    'bank_statement_doc' => $fileUrl,
                    'rFrom'              => $request->rFrom,
                    'rTo'                => $request->rTo,
                    'debit'              => $r1['amount'] ?? 0,
                    'credit'             => 0,
                    'batch_number'       => $batchNumber,
                    'match_id'           => $matchId, // link to bank statement
                    'transaction_date'   => $r1['date'] ?? null,
                ];
            }

            // --- RECORD 2 (BANK STATEMENT) ---
            if (!empty($match['record2'])) {
                $r2 = $match['record2'];

                $insertData[] = [
                    'userId'             => $userId,
                    'itemId'             => $r2['itemid'] ?? null,
                    'description'        => $r2['details'] ?? null,
                    'status'             => 2, // bank statement match
                    'bank_statement_doc' => $fileUrl,
                    'rFrom'              => $request->rFrom,
                    'rTo'                => $request->rTo,
                    'debit'              => $r2['amount'] ?? 0,
                    'credit'             => 0,
                    'batch_number'       => $batchNumber,
                    'match_id'           => $matchId, // same link
                    'transaction_date'   => $r2['date'] ?? null,
                ];
            }
        }


        // Prepare unmatched mandates
        foreach ($unmatchedMandates as $unmatchedMandate) {
            $insertData[] = [
                'userId'                => $userId,
                'itemId'                => $unmatchedMandate['itemid'] ?? null,
                'description'           => $unmatchedMandate['details'] ?? null,
                'status'                => 3,
                'bank_statement_doc'    => $fileUrl,
                'rFrom'                 => $request->rFrom,
                'rTo'                   => $request->rTo,
                'debit'                 => $unmatchedMandate['amount'] ?? 0,
                'credit'                =>  0,
                'batch_number'          => $batchNumber,
                'match_id'              => NULL,
                'transaction_date'      => $unmatchedMandate['date'] ?? null,
            ];
        }

        // Prepare unmatched bank statements
        foreach ($unmatchedBankStatements as $unmatchedBankStatement) {
            $insertData[] = [
                'userId'                => $userId,
                'itemId'                => $unmatchedBankStatement['itemid'] ?? null,
                'description'           => $unmatchedBankStatement['details'] ?? null,
                'status'                => 4,
                'bank_statement_doc'    => $fileUrl,
                'rFrom'                 => $request->rFrom,
                'rTo'                   => $request->rTo,
                'debit'                 => $unmatchedBankStatement['transactionType'] === 'debit' ? $unmatchedBankStatement['amount'] : 0,
                'credit'                => $unmatchedBankStatement['transactionType'] === 'credit' ? $unmatchedBankStatement['amount'] : 0,
                'batch_number'          => $batchNumber,
                'match_id'              => NULL,
                'transaction_date'      => $unmatchedBankStatement['date'] ?? null,
            ];
        }

        // Insert all records at once
        // Wrap insert in transaction
        try {
            DB::transaction(function () use ($insertData) {
                if (!empty($insertData)) {
                    DB::table('reconciliations')->insert($insertData);
                }
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Database transaction failed',
                'error' => $e->getMessage(),
            ], 500);
        }

        // dd(222222);




        if ($response->successful()) {
            return back()->with('success', 'Reconciliation sent successfully!');
            // return response()->json([
            //     'status' => true,
            //     'message' => 'Reconciliation sent successfully!',
            //     'api_response' => $response->json()
            // ]);
        }

        return back()->with('error', 'API Error: ');
        // return response()->json([
        //     'status' => false,
        //     'message' => 'API Error',
        //     'error' => $response->body(),
        // ], 500);
    }


    public function getBankReconciliationReports(Request $request)
    {
        Session::put('batch_number', $request->batch_number); // retain session
        $rFrom = $request->input('rFrom');
        $rTo = $request->input('rTo');
        $reconcilationType = $request->input('reconcilation_type');
        $batchNumber = $request->input('batch_number');
        $bankCredits = collect(); // empty collection default

        if ($reconcilationType == 777 && $batchNumber) {
            $bankCredits = DB::table('reconciliations')
                ->where('batch_number', $batchNumber)
                ->where('credit', '>', 0)
                ->where('status', 4) // status 4 = unmatched bank statement
                ->orderBy('id', 'desc')
                ->get();
        }

        if ($reconcilationType == 888 && $batchNumber) {
            $bankCredits = DB::table('reconciliations')
                ->where('batch_number', $batchNumber)
                ->where('debit', '>', 0)
                ->where('status', 4) // status 4 = unmatched bank statement
                ->orderBy('id', 'desc')
                ->get();
        }

        if ($reconcilationType == 999 && $batchNumber) {
            $bankCredits = DB::table('reconciliations')
                ->where('batch_number', $batchNumber)
                ->where('status', 3) // status 4 = unmatched bank statement
                ->orderBy('id', 'desc')
                ->get();
        }

        if ($reconcilationType == 555 && $batchNumber) {
            $bankCredits = DB::table('reconciliations')
                ->where('batch_number', $batchNumber)
                ->whereIn('status', [1, 2])
                ->orderBy('match_id')
                ->orderBy('status')
                ->get()
                ->groupBy('match_id');   // group pair
        }

        $mandateAccountsBatch = DB::table('reconciliations')
            // ->where('status', 1)
            ->select('batch_number') // only select the grouped column
            ->groupBy('batch_number')
            ->get();

        return view('funds.reconciliation.bank-credits', compact('bankCredits', 'rFrom', 'rTo', 'mandateAccountsBatch', 'reconcilationType'));
    }
}
