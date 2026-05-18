<?php

namespace App\Http\Controllers\procurement;



use Illuminate\Http\Request;
use App\Http\Controllers\Repository\ContractRepoController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Helpers\FileUploadHelper;
use Illuminate\Support\Facades\Storage;

class ContractDetailsController extends Controller
{
    private $contractRepoController;

    public function __construct()
    {
        $this->middleware('auth');
        //create new object of class::ContractRepoController
        try {
            $this->contractRepoController = new ContractRepoController;
        } catch (\Throwable $e) {
            $this->contractController = [];
        }
    }



    //create contract Details

    public function createContractDetails()
    {
        try {
            $data['getContractCategory'] = $this->contractRepoController->setCategoryArray(1);
            $data['getProcurementType']  = $this->procurementType(1);
            $data['getContractDetails']  = $this->contractRepoController->setContractDetailsArray(null, 20);

            // Get memoir documents for each contract
            foreach ($data['getContractDetails'] as $contract) {
                $contract->memoir_documents = DB::table('memoir_documents')
                    ->where('contract_detailsID', $contract->contract_detailsID)
                    ->get(['id', 'file_name', 'file_path', 'created_at']);
            }

            $data['requiredDocs'] = DB::table('tblbid_required_docs')->orderBy('doc_type', 'Asc')->get();

            if (Session::has('editRecord')) {
                $data['editRecord'] = Session::get('editRecord');
                
                // Get memoir documents for edit record
                if ($data['editRecord']) {
                    $data['editRecord']->memoir_documents = DB::table('memoir_documents')
                        ->where('contract_detailsID', $data['editRecord']->contract_detailsID)
                        ->get(['id', 'file_name', 'file_path', 'created_at']);
                }
            }
        } catch (\Throwable $e) {
            // Set default values to avoid undefined variable errors
            $data['getContractCategory'] = [];
            $data['getProcurementType']  = [];
            $data['requiredDocs']  = [];
            $data['getContractDetails']  = collect();
        }

        Session::forget('editRecord');
        return view('procurement.Contract.contractDetails', $data);
    }


    //create contract Report
    public function createContractReport()
    {
        try {
            $data['getContractDetails'] = $this->contractRepoController->setContractDetailsArray(null, 50); //($status = 1, $pagination = 10)
            $data['editRecord'] = [];
        } catch (\Throwable $e) {
            $data['getContractCategory'] = [];
        }

        return view('procurement.Contract.contractList', $data);
    }

    //create contract List
    public function createContractList()
    {
        try {
            $data['getContractDetails'] = $this->contractRepoController->setContractDetailsArray(null, 50); //($status = 1, $pagination = 10)
            // dd($data['getContractDetails']);
            $data['editRecord'] = [];
        } catch (\Throwable $e) {
            $data['getContractCategory'] = [];
        }

        return view('procurement.Contract.contractList', $data);
    }


    //post contract Details
    public function postContractDetails17_02_2026(Request $request)
    {
        $this->validate($request, [
            //'lotNumber'             => 'required|max:200',
            //'sublotNumber'         => 'required|max:200',
            'procurementType'       => 'required|max:255',
            'contractTitle'         => 'required|string|max:200',
            'contractCategory'     => 'required|numeric',
            //'proposedAmount'     => 'required',
            //'approvalDate'     => 'required|date',
            //'timeFrame'          => 'required|date',
            'contractDescription'  => 'required|string',
        ]);
        //start DB insertion
        $complete = 0;

        if ($request['recordID'] > 0 && DB::table('tblcontract_details')->where('contract_detailsID', $request['recordID'])->first()) {
            try {
                DB::table('tblcontract_details')->where('contract_detailsID', $request['recordID'])->update([
                    'lot_number'                => $request['lotNumber'],
                    'sublot_number'             => $request['sublotNumber'],
                    'procurement_typeID'        => $request['procurementType'],
                    'contract_name'             => $request['contractTitle'],
                    'contract_description'      => $request['contractDescription'],
                    'contract_categoryID'       => $request['contractCategory'],
                    'proposed_time_frame'       => $request['timeFrame'],
                    'proposed_budget'           => str_replace(',', '', $request['proposedAmount']),
                    'approval_date'             => $request['approvalDate'],
                    'advert_date'               => $request['advertDate'],
                    'bidding_date'              => $request['biddingDate'],
                    'contract_period'           => $request['contractPeriod'],
                    'updated_at'                => date('Y-m-d'),
                    'created_by'                => (Auth::check() ? Auth::user()->id : null),
                ]);
                $complete = 1;
            } catch (\Throwable $e) {
                Session::forget('editRecord');
            }
        } else {
            $this->validate($request, [
                'lotNumber'  => 'required|max:255|unique:tblcontract_details,lot_number',
            ]);
            try {
                $complete = DB::table('tblcontract_details')->insertGetId([
                    'lot_number'                => $request['lotNumber'],
                    'sublot_number'             => $request['sublotNumber'],
                    'procurement_typeID'        => $request['procurementType'],
                    'contract_name'             => $request['contractTitle'],
                    'contract_description'      => $request['contractDescription'],
                    'contract_categoryID'       => $request['contractCategory'],
                    'proposed_time_frame'       => $request['timeFrame'],
                    'proposed_budget'           => str_replace(',', '', $request['proposedAmount']),
                    'approval_date'             => $request['approvalDate'],
                    'advert_date'               => $request['advertDate'],
                    'bidding_date'              => $request['biddingDate'],
                    'contract_period'           => $request['contractPeriod'],
                    'created_at'                => date('Y-m-d'),
                    'updated_at'                => date('Y-m-d'),
                    'created_by'                => (Auth::check() ? Auth::user()->id : null),
                ]);
            } catch (\Throwable $e) {
                Session::forget('editRecord');
            }
        }
        Session::forget('editRecord');
        if ($complete) {
            return redirect()->route('contractDetails')->with('message', 'Your record was created/Updated successfully.');
        }

        return redirect()->route('contractDetails')->with('error', 'Sorry, we cannot create/Update your record ! Please, try again.');
    }

    public function postContractDetailsOLD(Request $request)
    {
        // dd($request->all());
        $this->validate($request, [
            'procurementType'       => 'required|max:255',
            'contractTitle'         => 'required|string|max:200',
            'contractCategory'      => 'required|numeric',
            'contractDescription'   => 'required|string',
        ]);

        //start DB insertion
        $complete = 0;

        // Get the proposed amount and remove commas
        $proposedAmount = str_replace(',', '', $request['proposedAmount']);

        // Define threshold priority by role name (CR has highest priority)
        $thresholdPriority = ['CR', 'DTB', 'FJTB']; // Add more in order of priority

        // Find all thresholds that match the amount
        $matchingThresholds = DB::table('threshold')
            ->where('min', '<=', $proposedAmount)
            ->where('max', '>=', $proposedAmount)
            ->get();

        // If no threshold found, return with error
        if ($matchingThresholds->isEmpty()) {
            return redirect()->route('contractDetails')
                ->with('error', 'The proposed amount ' . number_format($proposedAmount) . ' does not fall within any valid threshold range. Please adjust the amount.')
                ->withInput();
        }

        // Select threshold based on priority
        $selectedThreshold = null;

        // Try to find a threshold based on priority order
        foreach ($thresholdPriority as $priorityRole) {
            $threshold = $matchingThresholds->first(function ($item) use ($priorityRole) {
                return $item->role == $priorityRole;
            });

            if ($threshold) {
                $selectedThreshold = $threshold;
                break;
            }
        }

        // If no threshold found from priority list (shouldn't happen if there are matches), use the first one
        if (!$selectedThreshold) {
            $selectedThreshold = $matchingThresholds->first();
        }

        // Log for debugging (optional)
        // Log::info('Amount: ' . $proposedAmount . ' selected threshold: ' . $selectedThreshold->role . ' (ID: ' . $selectedThreshold->id . ')');

        if ($request['recordID'] > 0 && DB::table('tblcontract_details')->where('contract_detailsID', $request['recordID'])->first()) {
            try {
                DB::table('tblcontract_details')->where('contract_detailsID', $request['recordID'])->update([
                    'lot_number'                => $request['lotNumber'],
                    'sublot_number'             => $request['sublotNumber'],
                    'procurement_typeID'        => $request['procurementType'],
                    'contract_name'             => $request['contractTitle'],
                    'contract_description'      => $request['contractDescription'],
                    'contract_categoryID'       => $request['contractCategory'],
                    'proposed_time_frame'       => $request['timeFrame'],
                    'proposed_budget'           => $proposedAmount,
                    'threshold_id'               => $selectedThreshold->id,
                    'approval_date'             => $request['approvalDate'],
                    'advert_date'               => $request['advertDate'],
                    'bidding_date'              => $request['biddingDate'],
                    'close_bidding_date'        => $request['closeBiddingDate'],
                    'reference_number'          => $request['referenceNumber'],
                    'contract_period'           => $request['contractPeriod'],
                    'updated_at'                => date('Y-m-d H:i:s'), // Changed to include time
                    'created_by'                => (Auth::check() ? Auth::user()->id : null),
                ]);
                $complete = 1;
            } catch (\Throwable $e) {
                Session::forget('editRecord');
                // Log::error('Update error: ' . $e->getMessage()); // Log the error
            }
        } else {
            $this->validate($request, [
                'lotNumber'  => 'required|max:255',
            ]);
            try {
                $complete = DB::table('tblcontract_details')->insertGetId([
                    'lot_number'                => $request['lotNumber'],
                    'sublot_number'             => $request['sublotNumber'],
                    'procurement_typeID'        => $request['procurementType'],
                    'contract_name'             => $request['contractTitle'],
                    'contract_description'      => $request['contractDescription'],
                    'contract_categoryID'       => $request['contractCategory'],
                    'proposed_time_frame'       => $request['timeFrame'],
                    'proposed_budget'           => $proposedAmount,
                    'threshold_id'               => $selectedThreshold->id,
                    'approval_date'             => $request['approvalDate'],
                    'advert_date'               => $request['advertDate'],
                    'bidding_date'              => $request['biddingDate'],
                    'close_bidding_date'        => $request['closeBiddingDate'],
                    'reference_number'          => $request['referenceNumber'],
                    'contract_period'           => $request['contractPeriod'],
                    'created_at'                => date('Y-m-d H:i:s'), // Changed to include time
                    'updated_at'                => date('Y-m-d H:i:s'), // Changed to include time
                    'created_by'                => (Auth::check() ? Auth::user()->id : null),
                ]);
            } catch (\Throwable $e) {
                Session::forget('editRecord');
                // Log::error('Insert error: ' . $e->getMessage()); // Log the error
            }
        }

        Session::forget('editRecord');

        if ($complete) {
            // Format the amount for display
            $formattedAmount = '₦' . number_format($proposedAmount, 2);
            return redirect()->route('contractDetails')->with(
                'message',
                'Record created/updated successfully! Amount: ' . $formattedAmount .
                    ' | Threshold: ' . $selectedThreshold->role .
                    ' (Range: ₦' . number_format($selectedThreshold->min) . ' - ₦' . number_format($selectedThreshold->max) . ')'
            );
        }

        return redirect()->route('contractDetails')->with('error', 'Sorry, we cannot create/update your record! Please try again.');
    }


    public function postContractDetails1(Request $request)
    {
        // dd($request->all());
        $this->validate($request, [
            'procurementType'       => 'required|max:255',
            'contractTitle'         => 'required|string|max:200',
            'contractCategory'      => 'required|numeric',
            'contractDescription'   => 'required|string',
        ]);

        // ✅ contract required documents (array of ids)
        $docIds = collect($request->input('contractRequireDocument', []))
            ->filter()
            ->unique()
            ->values()
            ->all();

        //start DB insertion
        $complete = 0;

        // Get the proposed amount and remove commas
        $proposedAmount = str_replace(',', '', $request['proposedAmount']);

        // Define threshold priority by role name (CR has highest priority)
        $thresholdPriority = ['CR', 'DTB', 'FJTB']; // Add more in order of priority

        // Find all thresholds that match the amount
        $matchingThresholds = DB::table('threshold')
            ->where('min', '<=', $proposedAmount)
            ->where('max', '>=', $proposedAmount)
            ->get();

        // If no threshold found, return with error
        if ($matchingThresholds->isEmpty()) {
            return redirect()->route('contractDetails')
                ->with('error', 'The proposed amount ' . number_format($proposedAmount) . ' does not fall within any valid threshold range. Please adjust the amount.')
                ->withInput();
        }

        // Select threshold based on priority
        $selectedThreshold = null;

        foreach ($thresholdPriority as $priorityRole) {
            $threshold = $matchingThresholds->first(function ($item) use ($priorityRole) {
                return $item->role == $priorityRole;
            });

            if ($threshold) {
                $selectedThreshold = $threshold;
                break;
            }
        }

        if (!$selectedThreshold) {
            $selectedThreshold = $matchingThresholds->first();
        }

        try {
            DB::beginTransaction();

            // ✅ UPDATE
            if ($request['recordID'] > 0 && DB::table('tblcontract_details')->where('contract_detailsID', $request['recordID'])->first()) {

                DB::table('tblcontract_details')->where('contract_detailsID', $request['recordID'])->update([
                    'lot_number'                => $request['lotNumber'],
                    'sublot_number'             => $request['sublotNumber'],
                    'procurement_typeID'        => $request['procurementType'],
                    'contract_name'             => $request['contractTitle'],
                    'contract_description'      => $request['contractDescription'],
                    'contract_categoryID'       => $request['contractCategory'],
                    'proposed_time_frame'       => $request['timeFrame'],
                    'proposed_budget'           => $proposedAmount,
                    'threshold_id'              => $selectedThreshold->id,
                    'approval_date'             => $request['approvalDate'],
                    'advert_date'               => $request['advertDate'],
                    'bidding_date'              => $request['biddingDate'],
                    'close_bidding_date'        => $request['closeBiddingDate'],
                    'reference_number'          => $request['referenceNumber'],
                    'contract_period'           => $request['contractPeriod'],
                    'updated_at'                => date('Y-m-d H:i:s'),
                    'created_by'                => (Auth::check() ? Auth::user()->id : null),
                ]);

                $contractDetailId = (int) $request['recordID'];
                $complete = 1;
            }
            // ✅ INSERT
            else {

                $this->validate($request, [
                    'lotNumber'  => 'required|max:255',
                ]);

                $contractDetailId = DB::table('tblcontract_details')->insertGetId([
                    'lot_number'                => $request['lotNumber'],
                    'sublot_number'             => $request['sublotNumber'],
                    'procurement_typeID'        => $request['procurementType'],
                    'contract_name'             => $request['contractTitle'],
                    'contract_description'      => $request['contractDescription'],
                    'contract_categoryID'       => $request['contractCategory'],
                    'proposed_time_frame'       => $request['timeFrame'],
                    'proposed_budget'           => $proposedAmount,
                    'threshold_id'              => $selectedThreshold->id,
                    'approval_date'             => $request['approvalDate'],
                    'advert_date'               => $request['advertDate'],
                    'bidding_date'              => $request['biddingDate'],
                    'close_bidding_date'        => $request['closeBiddingDate'],
                    'reference_number'          => $request['referenceNumber'],
                    'contract_period'           => $request['contractPeriod'],
                    'created_at'                => date('Y-m-d H:i:s'),
                    'updated_at'                => date('Y-m-d H:i:s'),
                    'created_by'                => (Auth::check() ? Auth::user()->id : null),
                ]);

                $complete = (int) $contractDetailId;
            }



            if (!empty($docIds)) {

                // ✅ SAVE REQUIRED DOCS (replace existing for this contract)
                DB::table('contract_required_documents')
                    ->where('tblcontract_detail_id', $contractDetailId)
                    ->delete();

                $now = date('Y-m-d H:i:s');

                $rows = array_map(function ($docId) use ($contractDetailId, $now) {
                    return [
                        'tblcontract_detail_id'  => $contractDetailId,
                        'tblbid_required_doc_id' => (int) $docId,
                        'created_at'             => $now,
                        'updated_at'             => $now,
                    ];
                }, $docIds);

                DB::table('contract_required_documents')->insert($rows);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Session::forget('editRecord');

            return redirect()->route('contractDetails')
                ->with('error', 'Sorry, we cannot create/update your record! Please try again.');
        }

        Session::forget('editRecord');

        if ($complete) {
            $formattedAmount = '₦' . number_format($proposedAmount, 2);

            return redirect()->route('contractDetails')->with(
                'message',
                'Record created/updated successfully! Amount: ' . $formattedAmount .
                    ' | Threshold: ' . $selectedThreshold->role .
                    ' (Range: ₦' . number_format($selectedThreshold->min) . ' - ₦' . number_format($selectedThreshold->max) . ')'
            );
        }

        return redirect()->route('contractDetails')->with('error', 'Sorry, we cannot create/update your record! Please try again.');
    }


    public function postContractDetails(Request $request)
    {
        $this->validate($request, [
            'procurementType'       => 'required|max:255',
            'contractTitle'         => 'required|string|max:200',
            'contractCategory'      => 'required|numeric',
            'contractDescription'   => 'required|string',
            'location'              => 'nullable|string|max:255', 
        ]);

        // Contract required documents
        $docIds = collect($request->input('contractRequireDocument', []))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $complete = 0;
        $proposedAmount = str_replace(',', '', $request['proposedAmount']);

        // Threshold logic
        $thresholdPriority = ['CR', 'DTB', 'FJTB'];
        $matchingThresholds = DB::table('threshold')
            ->where('min', '<=', $proposedAmount)
            ->where('max', '>=', $proposedAmount)
            ->get();

        if ($matchingThresholds->isEmpty()) {
            return redirect()->route('contractDetails')
                ->with('error', 'The proposed amount ' . number_format($proposedAmount) . ' does not fall within any valid threshold range. Please adjust the amount.')
                ->withInput();
        }

        $selectedThreshold = null;
        foreach ($thresholdPriority as $priorityRole) {
            $threshold = $matchingThresholds->first(function ($item) use ($priorityRole) {
                return $item->role == $priorityRole;
            });
            if ($threshold) {
                $selectedThreshold = $threshold;
                break;
            }
        }
        if (!$selectedThreshold) {
            $selectedThreshold = $matchingThresholds->first();
        }

        try {
            DB::beginTransaction();

            // UPDATE
            if ($request['recordID'] > 0 && DB::table('tblcontract_details')->where('contract_detailsID', $request['recordID'])->first()) {

                DB::table('tblcontract_details')->where('contract_detailsID', $request['recordID'])->update([
                    'lot_number'                => $request['lotNumber'],
                    'sublot_number'             => $request['sublotNumber'],
                    'procurement_typeID'        => $request['procurementType'],
                    'contract_name'             => $request['contractTitle'],
                    'contract_description'      => $request['contractDescription'],
                    'contract_categoryID'       => $request['contractCategory'],
                    'proposed_time_frame'       => $request['timeFrame'],
                    'proposed_budget'           => $proposedAmount,
                    'threshold_id'              => $selectedThreshold->id,
                    'approval_date'             => $request['approvalDate'],
                    'advert_date'               => $request['advertDate'],
                    'bidding_date'              => $request['biddingDate'],
                    'close_bidding_date'        => $request['closeBiddingDate'],
                    'location'                   => $request['location'], // Location field
                    'reference_number'          => $request['referenceNumber'],
                    'contract_period'           => $request['contractPeriod'],
                    'updated_at'                => date('Y-m-d H:i:s'),
                    'created_by'                => (Auth::check() ? Auth::user()->id : null),
                ]);

                $contractDetailsID = (int) $request['recordID'];
                
                // Handle memoir file uploads
                $this->handleMemoirFileUploads($request, $contractDetailsID);
                
                $complete = 1;
            }
            // INSERT
            else {
                $this->validate($request, ['lotNumber' => 'required|max:255']);

                $contractDetailsID = DB::table('tblcontract_details')->insertGetId([
                    'lot_number'                => $request['lotNumber'],
                    'sublot_number'             => $request['sublotNumber'],
                    'procurement_typeID'        => $request['procurementType'],
                    'contract_name'             => $request['contractTitle'],
                    'contract_description'      => $request['contractDescription'],
                    'contract_categoryID'       => $request['contractCategory'],
                    'proposed_time_frame'       => $request['timeFrame'],
                    'proposed_budget'           => $proposedAmount,
                    'threshold_id'              => $selectedThreshold->id,
                    'approval_date'             => $request['approvalDate'],
                    'advert_date'               => $request['advertDate'],
                    'bidding_date'              => $request['biddingDate'],
                    'close_bidding_date'        => $request['closeBiddingDate'],
                    'location'                   => $request['location'], // Location field
                    'reference_number'          => $request['referenceNumber'],
                    'contract_period'           => $request['contractPeriod'],
                    'created_at'                => date('Y-m-d H:i:s'),
                    'updated_at'                => date('Y-m-d H:i:s'),
                    'created_by'                => (Auth::check() ? Auth::user()->id : null),
                ]);

                // Handle memoir file uploads
                $this->handleMemoirFileUploads($request, $contractDetailsID);

                $complete = (int) $contractDetailsID;
            }

            // Save required documents
            if (!empty($docIds)) {
                DB::table('contract_required_documents')
                    ->where('tblcontract_detail_id', $contractDetailsID)
                    ->delete();

                $now = date('Y-m-d H:i:s');
                $rows = array_map(function ($docId) use ($contractDetailsID, $now) {
                    return [
                        'tblcontract_detail_id'  => $contractDetailsID,
                        'tblbid_required_doc_id' => (int) $docId,
                        'created_at'             => $now,
                        'updated_at'             => $now,
                    ];
                }, $docIds);

                DB::table('contract_required_documents')->insert($rows);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Session::forget('editRecord');
            
            return redirect()->route('contractDetails')
                ->with('error', 'Sorry, we cannot create/update your record! Please try again. Error: ' . $e->getMessage());
        }

        Session::forget('editRecord');

        if ($complete) {
            $formattedAmount = '₦' . number_format($proposedAmount, 2);
            return redirect()->route('contractDetails')->with(
                'message',
                'Record created/updated successfully! Amount: ' . $formattedAmount .
                    ' | Threshold: ' . $selectedThreshold->role .
                    ' (Range: ₦' . number_format($selectedThreshold->min) . ' - ₦' . number_format($selectedThreshold->max) . ')'
            );
        }

        return redirect()->route('contractDetails')->with('error', 'Sorry, we cannot create/update your record! Please try again.');
    }

   

    private function handleMemoirFileUploads($request, $contractDetailsID)
    {
        // Handle file deletions first
        if ($request->has('remove_memoir_files') && $request->has('keep_memoir_files')) {
            $filesToKeep = $request->input('keep_memoir_files', []);
            
            $filesToDelete = DB::table('memoir_documents')
                ->where('contract_detailsID', $contractDetailsID)
                ->whereNotIn('file_path', $filesToKeep)
                ->get();
            
            foreach ($filesToDelete as $file) {
                $filename = basename($file->file_path);
                $filePath = public_path('memoir_files/' . $filename);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                DB::table('memoir_documents')->where('id', $file->id)->delete();
            }
        }

        // Handle new file uploads
        if ($request->hasFile('memoir_file')) {
            $files = $request->file('memoir_file');
            
            if (!is_array($files)) {
                $files = [$files];
            }
            
            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    // Generate unique code ONLY (no original name)
                    $uniqueCode = $this->generateUniqueCode();
                    $extension = $file->getClientOriginalExtension();
                    
                    // Create filename with ONLY the unique code
                    // Format: MEM_20240315_123456_789012.pdf (no original name)
                    $newFilename = 'MEM_' . $uniqueCode . '.' . $extension;
                    
                    // Upload using helper
                    $fileUrl = FileUploadHelper::upload($file, 'memoir_files', $newFilename);
                    
                    // Save to database - store only the unique filename
                    DB::table('memoir_documents')->insert([
                        'contract_detailsID' => $contractDetailsID,
                        'file_name' => $newFilename, // Only the unique name
                        'file_path' => $fileUrl,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Generate a unique code for the file
     */
    private function generateUniqueCode()
    {
        // Format: YYYYMMDD + random 6 digits + timestamp
        $date = date('Ymd');
        $random = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $time = time();
        
        // Example: 20240315_123456_1712345678
        return $date . '_' . $random . '_' . substr($time, -6);
    }

    public function deleteContractDetails($id)
    {
        $id = base64_decode($id);
        
        try {
            DB::beginTransaction();
            
            // Get memoir documents to delete files
            $memoirFiles = DB::table('memoir_documents')
                ->where('contract_detailsID', $id)
                ->get();
            
            // Delete physical files
            foreach ($memoirFiles as $file) {
                $filename = basename($file->file_path);
                $filePath = public_path('memoir_files/' . $filename);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // Delete memoir documents (will cascade but doing explicitly)
            DB::table('memoir_documents')->where('contract_detailsID', $id)->delete();
            
            // Delete related required documents
            DB::table('contract_required_documents')->where('tblcontract_detail_id', $id)->delete();
            
            // Delete the contract
            DB::table('tblcontract_details')->where('contract_detailsID', $id)->delete();
            
            DB::commit();
            
            return redirect()->route('contractDetails')->with('message', 'Contract deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('contractDetails')->with('error', 'Failed to delete contract. Please try again.');
        }
    }

    public function getMemoirDocuments($contractDetailsID)
    {
        try {
            $documents = DB::table('memoir_documents')
                ->where('contract_detailsID', $contractDetailsID)
                ->select('id', 'contract_detailsID', 'file_name', 'file_path', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();
                
            return response()->json($documents);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load documents'], 500);
        }
    }

    public function uploadMemoirFilesModal1(Request $request, $contractDetailsID)
    {
        try {
            $request->validate([
                'memoir_file.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:100',
            ]);

            if ($request->hasFile('memoir_file')) {
                $files = $request->file('memoir_file');
                
                if (!is_array($files)) {
                    $files = [$files];
                }
                
                $uploadedCount = 0;
                
                foreach ($files as $file) {
                    if ($file && $file->isValid()) {
                        // Generate filename
                        $filename = FileUploadHelper::refNo() . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        
                        // Upload using helper
                        $fileUrl = FileUploadHelper::upload($file, 'memoir_files', $filename);
                        
                        // Save to database
                        DB::table('memoir_documents')->insert([
                            'contract_detailsID' => $contractDetailsID,
                            'file_name' => $file->getClientOriginalName(),
                            'file_path' => $fileUrl,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        
                        $uploadedCount++;
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'message' => $uploadedCount . ' file(s) uploaded successfully.',
                    'count' => $uploadedCount
                ]);
            }
            
            return response()->json(['success' => false, 'message' => 'No files uploaded.'], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadMemoirFilesModal(Request $request, $contractDetailsID)
    {
        try {
            $request->validate([
                'memoir_file.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:100',
            ]);

            if ($request->hasFile('memoir_file')) {
                $files = $request->file('memoir_file');
                
                if (!is_array($files)) {
                    $files = [$files];
                }
                
                $uploadedCount = 0;
                
                foreach ($files as $file) {
                    if ($file && $file->isValid()) {
                        // Generate unique code
                        $uniqueCode = $this->generateUniqueCode();
                        
                        // Get original filename without extension
                        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $extension = $file->getClientOriginalExtension();
                        
                        // Clean the original name (remove special characters, spaces)
                        $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
                        
                        // Create new filename with unique code concatenated
                        $newFilename = $cleanName . '_' . $uniqueCode . '.' . $extension;
                        
                        // Upload using helper
                        $fileUrl = FileUploadHelper::upload($file, 'memoir_files', $newFilename);
                        
                        // Save to database
                        DB::table('memoir_documents')->insert([
                            'contract_detailsID' => $contractDetailsID,
                            'file_name' => $newFilename,
                            'file_path' => $fileUrl,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        
                        $uploadedCount++;
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'message' => $uploadedCount . ' file(s) uploaded successfully.',
                    'count' => $uploadedCount
                ]);
            }
            
            return response()->json(['success' => false, 'message' => 'No files uploaded.'], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteMemoirFile($fileId)
    {
        try {
            // Get file record
            $file = DB::table('memoir_documents')->where('id', $fileId)->first();
            
            if (!$file) {
                return response()->json(['success' => false, 'message' => 'File not found.'], 404);
            }
            
            // Delete physical file
            $filename = basename($file->file_path);
            $filePath = public_path('memoir_files/' . $filename);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Delete database record
            DB::table('memoir_documents')->where('id', $fileId)->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
    }

    //Delete contract
    public function removeContractDetails($recordID = null)
    {
        $recordID = base64_decode($recordID);

        try {
            if ($recordID != null || DB::table('tblcontract_details')->where('contract_detailsID', $recordID)->first()) {
                //delete contract bidding if any and also delete contractor bid docs
                $deleteBidding = DB::table('tblcontract_bidding')->where('contractID', $recordID)->delete();
                $deleteBidDoc = DB::table('tblcontractor_contract_bid_document')->where('contractID', $recordID)->delete();
                if ($deleteBidding && $deleteBidDoc && DB::table('tblcontract_details')->where('contract_detailsID', $recordID)->delete()) {
                    return redirect()->route('contractDetails')->with('message', 'Your record was delete successfully.');
                } else {
                    return redirect()->route('contractDetails')->with('error', 'Sorry we cannot delete this record ! Try again');
                }
            } else {
                return redirect()->route('contractDetails')->with('error', 'Sorry we cannot delete this record !');
            }
        } catch (\Throwable $e) {
        }
        return redirect()->route('contractDetails')->with('error', 'Sorry we cannot delete this record !');
    }

    //Edit
    public function getEditContractDetails($recordID = null)
    {
        Session::forget('editRecord');
        $recordID = base64_decode($recordID);
        if ($recordID) {
            try {
                // $setContractDetail = DB::table('tblcontract_details')->where('tblcontract_details.contract_detailsID', $recordID)
                //     ->leftJoin('users', 'users.id', '=', 'tblcontract_details.created_by')
                //     ->leftJoin('protblstatus', 'protblstatus.status_code', '=', 'tblcontract_details.status')
                //     ->join('protblcontract_category', 'protblcontract_category.contractCategoryID', '=', 'tblcontract_details.contract_categoryID')
                //     ->join('tblprocurement_type', 'tblprocurement_type.procurement_typeID', '=', 'tblcontract_details.procurement_typeID')
                //     ->leftJoin('contract_required_documents as crd', 'crd.tblcontract_detail_id', '=', 'tblcontract_details.contract_detailsID')
                //     ->leftJoin('tblbid_required_docs as d', 'd.id', '=', 'crd.tblbid_required_doc_id')
                //     ->first();

                $setContractDetail = DB::table('tblcontract_details as c')
                    ->leftJoin('users as u', 'u.id', '=', 'c.created_by')
                    ->leftJoin('protblstatus as s', 's.status_code', '=', 'c.status')
                    ->join('protblcontract_category as cc', 'cc.contractCategoryID', '=', 'c.contract_categoryID')
                    ->join('tblprocurement_type as pt', 'pt.procurement_typeID', '=', 'c.procurement_typeID')
                    // Join pivot table
                    ->leftJoin('contract_required_documents as crd', 'crd.tblcontract_detail_id', '=', 'c.contract_detailsID')
                    ->leftJoin('tblbid_required_docs as d', 'd.id', '=', 'crd.tblbid_required_doc_id')
                    ->select(
                        'c.*',
                        'u.name as created_by_name',
                        's.status_name',
                        'cc.category_name',
                        'cc.contractCategoryID',
                        'pt.type',
                        // Aggregate selected document IDs as comma-separated string
                        DB::raw('GROUP_CONCAT(d.id) as selected_document_ids')
                    )
                    ->where('c.contract_detailsID', $recordID)
                    ->groupBy('c.contract_detailsID') // important to aggregate pivot table
                    ->first();

                // Log::info('Contract details for editing', ['contract_details' => $setContractDetail]);
                // dd($setContractDetail);
                Session::put('editRecord', $setContractDetail);
                return redirect()->route('contractDetails')->with('message', 'Your record Updated successfully.');
            } catch (\Throwable $e) {
            }
            return redirect()->route('contractDetails')->with('error', 'Sorry we cannot edit this record!');
        } else {
            return redirect()->route('contractDetails')->with('error', 'Sorry we cannot edit this record!');
        }
    }

    //Cancel Edit
    public function cancelEditContractDetails()
    {
        Session::forget('editRecord');

        return redirect()->route('contractDetails')->with('info', 'Editting was cancelled');
    }


    //Get procurement type
    public function procurementType($status = 1)
    {
        return ($status <> null ? DB::table('tblprocurement_type')->where('status', $status)->get() : DB::table('tblprocurement_type')->get());
    }


    ############### LIVE SEARCH ANY PRODUCT###########
    public function searchContractFromDB($getQuery = null)
    {
        $getAllMatch = array();
        $reservedSymbols = ['<', '>', '@', '(', ')', '~', '|', '/', '{', '}', '[', ']'];
        $searchQuery = preg_split('/\s+/', $getQuery, -1, PREG_SPLIT_NO_EMPTY);
        //$searchQuery = str_replace($reservedSymbols, '', $searchQuery);
        if (!empty($getQuery)) {
            try {
                $getAllMatch = DB::table('tblcontract_details')
                    ->leftJoin('users', 'users.id', '=', 'tblcontract_details.created_by')
                    ->leftJoin('protblstatus', 'protblstatus.statusID', '=', 'tblcontract_details.status')
                    ->leftjoin('protblcontract_category', 'protblcontract_category.contractCategoryID', '=', 'tblcontract_details.contract_categoryID')
                    ->leftjoin("tblcontract_bidding", 'tblcontract_details.contract_detailsID', '=', 'tblcontract_bidding.contractID')
                    ->leftjoin('tblcontractor_registration', 'tblcontract_bidding.contractorID', '=', 'tblcontractor_registration.contractor_registrationID')
                    ->orderBy('tblcontract_details.contract_detailsID', 'Desc')
                    ->select('tblcontract_details.*', 'tblstatus.status_name', 'name', 'id', 'contract_categoryID', 'status_code', 'category_name', 'tblcontractor_registration.company_name as contractor')
                    ->orwhere(function ($query) use ($searchQuery) {
                        foreach ($searchQuery as $value) {
                            $query->orWhere('tblcontract_details.lot_number', 'like', "%{$value}%");
                            $query->orWhere('protblstatus.status_name', 'like', "%{$value}%");
                            $query->orWhere('tblcontract_details.contract_name', 'like', "%{$value}%");
                            $query->orWhere('tblcontract_details.proposed_budget', 'like', "%{$value}%");
                            $query->orWhere('tblcontract_category.category_name', 'like', "%{$value}%");
                            $query->orWhere('tblcontract_details.contract_description', 'like', "%{$value}%");
                        }
                    })
                    ->take(20)
                    ->get();

                return $getAllMatch;
            } catch (\Throwable $errorThrown) {
            }
        }
        return $getAllMatch;
    }

    //
    public function searchContract(Request $request)
    {
        $value = str_replace(',', '', trim($request['q']));
        $data['getContractDetails'] = [];
        $startDate  = $request['startDate'];
        $endDate    = $request['endDate'];

        if (!empty($value)) {
            try {
                if ($startDate == null || $endDate == null) {
                    $data['getContractDetails'] = DB::table('tblcontract_details')
                        ->leftJoin('users', 'users.id', '=', 'tblcontract_details.created_by')
                        ->leftJoin('protblstatus', 'protblstatus.statusID', '=', 'tblcontract_details.status')
                        ->join('protblcontract_category', 'protblcontract_category.contractCategoryID', '=', 'tblcontract_details.contract_categoryID')
                        ->leftjoin("tblcontract_bidding", 'tblcontract_details.contract_detailsID', '=', 'tblcontract_bidding.contractID')
                        ->leftjoin('tblcontractor_registration', 'tblcontract_bidding.contractorID', '=', 'tblcontractor_registration.contractor_registrationID')
                        ->orderBy('tblcontract_details.contract_detailsID', 'Desc')
                        ->select('tblcontract_details.*', 'tblstatus.status_name', 'name', 'id', 'contract_categoryID', 'status_code', 'category_name', 'tblcontractor_registration.company_name as contractor')
                        ->where(function ($query) use ($value) {
                            //foreach ($searchQuery as $value)
                            //{
                            $query->orWhere('tblcontract_details.lot_number', 'like', "%{$value}%");
                            $query->orWhere('protblstatus.status_name', 'like', "%{$value}%");
                            $query->orWhere('tblcontract_details.contract_name', 'like', "%{$value}%");
                            $query->orWhere('tblcontract_details.proposed_budget', 'like', "%{$value}%");
                            $query->orWhere('protblcontract_category.category_name', 'like', "%{$value}%");
                            $query->orWhere('tblcontract_details.contract_description', 'like', "%{$value}%");
                            //}
                        })
                        ->paginate(100);
                } elseif ($value && $startDate  && $endDate) {
                    $data['getContractDetails'] = DB::table('tblcontract_details')->whereBetween('approval_date', [$startDate, $endDate])
                        ->leftJoin('users', 'users.id', '=', 'tblcontract_details.created_by')
                        ->leftJoin('protblstatus', 'protblstatus.statusID', '=', 'tblcontract_details.status')
                        ->join('protblcontract_category', 'protblcontract_category.contractCategoryID', '=', 'tblcontract_details.contract_categoryID')
                        ->leftjoin("tblcontract_bidding", 'tblcontract_details.contract_detailsID', '=', 'tblcontract_bidding.contractID')
                        ->leftjoin('tblcontractor_registration', 'tblcontract_bidding.contractorID', '=', 'tblcontractor_registration.contractor_registrationID')
                        ->orderBy('tblcontract_details.contract_detailsID', 'Desc')
                        ->select('tblcontract_details.*', 'tblstatus.status_name', 'name', 'id', 'contract_categoryID', 'status_code', 'category_name', 'tblcontractor_registration.company_name as contractor')
                        ->where(function ($query) use ($value) {
                            //foreach ($searchQuery as $value)
                            //{
                            $query->orWhere('tblcontract_details.lot_number', 'like', "%{$value}%");
                            $query->orWhere('protblstatus.status_name', 'like', "%{$value}%");
                            $query->orWhere('tblcontract_details.contract_name', 'like', "%{$value}%");
                            $query->orWhere('tblcontract_details.proposed_budget', 'like', "%{$value}%");
                            $query->orWhere('protblcontract_category.category_name', 'like', "%{$value}%");
                            $query->orWhere('tblcontract_details.contract_description', 'like', "%{$value}%");
                            //}
                        })
                        ->paginate(100);
                } elseif (($value == null) && $startDate <> null  && $endDate <> null) {
                    $data['getContractDetails'] = DB::table('tblcontract_details')->whereBetween('approval_date', [$startDate, $endDate])
                        ->leftJoin('users', 'users.id', '=', 'tblcontract_details.created_by')
                        ->leftJoin('protblstatus', 'protblstatus.statusID', '=', 'tblcontract_details.status')
                        ->join('protblcontract_category', 'protblcontract_category.contractCategoryID', '=', 'tblcontract_details.contract_categoryID')
                        ->leftjoin("tblcontract_bidding", 'tblcontract_details.contract_detailsID', '=', 'tblcontract_bidding.contractID')
                        ->leftjoin('tblcontractor_registration', 'tblcontract_bidding.contractorID', '=', 'tblcontractor_registration.contractor_registrationID')
                        ->orderBy('tblcontract_details.contract_detailsID', 'Desc')
                        ->select('tblcontract_details.*', 'tblstatus.status_name', 'name', 'id', 'contract_categoryID', 'status_code', 'category_name', 'tblcontractor_registration.company_name as contractor')
                        ->where(function ($query) use ($value) {
                            //foreach ($searchQuery as $value)
                            //{
                            $query->orWhere('tblcontract_details.lot_number', 'like', "%{$value}%");
                            $query->orWhere('protblstatus.status_name', 'like', "%{$value}%");
                            $query->orWhere('tblcontract_details.contract_name', 'like', "%{$value}%");
                            $query->orWhere('tblcontract_details.proposed_budget', 'like', "%{$value}%");
                            $query->orWhere('protblcontract_category.category_name', 'like', "%{$value}%");
                            $query->orWhere('tblcontract_details.contract_description', 'like', "%{$value}%");
                            //}
                        })
                        ->paginate(100);
                } else {
                    $data['getContractDetails'] = DB::table('tblcontract_details')
                        ->leftJoin('users', 'users.id', '=', 'tblcontract_details.created_by')
                        ->leftJoin('protblstatus', 'protblstatus.statusID', '=', 'tblcontract_details.status')
                        ->join('protblcontract_category', 'protblcontract_category.contractCategoryID', '=', 'tblcontract_details.contract_categoryID')
                        ->leftjoin("tblcontract_bidding", 'tblcontract_details.contract_detailsID', '=', 'tblcontract_bidding.contractID')
                        ->leftjoin('tblcontractor_registration', 'tblcontract_bidding.contractorID', '=', 'tblcontractor_registration.contractor_registrationID')
                        ->orderBy('tblcontract_details.contract_detailsID', 'Desc')
                        ->select('tblcontract_details.*', 'tblstatus.status_name', 'name', 'id', 'contract_categoryID', 'status_code', 'category_name', 'tblcontractor_registration.company_name as contractor')
                        ->where(function ($query) use ($value) {
                            //foreach ($searchQuery as $value)
                            //{
                            $query->orWhere('tblcontract_details.lot_number', 'like', "%{$value}%");
                            $query->orWhere('protblstatus.status_name', 'like', "%{$value}%");
                            $query->orWhere('tblcontract_details.contract_name', 'like', "%{$value}%");
                            $query->orWhere('tblcontract_details.proposed_budget', 'like', "%{$value}%");
                            $query->orWhere('protblcontract_category.category_name', 'like', "%{$value}%");
                            $query->orWhere('tblcontract_details.contract_description', 'like', "%{$value}%");
                            //}
                        })
                        ->paginate(100);
                }
            } catch (\Throwable $errorThrown) {
            }
        } else {
            $data['getContractDetails'] = $this->contractRepoController->setContractDetailsArray(null, 50);
        }
        return view('procurement.Contract.contractList', $data);
    }
    ######## END LIVE SEARCH #############

    /*
    ///Search Product From DB JSON
        Route::get('/search-contract-from-db-JSON/{query?}', 'ContractDetailsController@searchContractFromDB');
        Route::get('/search-contract',                      'ContractDetailsController@searchContract')->name('searchContract');
    */


    // Search contract payment transaction record
    // public function searchContractPayment(Request $request)
    // {
    //     return view('procurement.Contract.contractPaymentReport');
    // }

    // Autocomplete search for contract fileNo

    public function autocompleteFileNo(Request $request)
    {
        $search = $request->get('term');

        $results = \DB::table('tblcontractDetails')
            ->where('fileNo', 'LIKE', $search . '%')
            ->limit(10)
            ->pluck('fileNo');

        return response()->json($results);
    }



    public function searchContractPaymentTransaction(Request $request)
    {
        $contract = null;
        $payments = [];

        // Only search if fileNo is provided
        if ($request->has('fileNo') && $request->fileNo != '') {

            $request->validate([
                'fileNo' => 'required|string'
            ]);

            // Search for contract
            $contract = DB::table('tblcontractDetails')
                ->where('fileNo', $request->fileNo)
                ->first();

            if ($contract) {
                // Get payment transactions
                $payments = DB::table('tblpaymentTransaction')
                    ->where('contractID', $contract->ID)
                    ->orderBy('datePrepared', 'ASC')
                    ->orderBy('contractID', 'ASC')
                    ->get();
            } else {
                return back()->with('error', 'No contract found with File No: ' . $request->fileNo)
                    ->withInput();
            }
        }

        // Return one single blade for both search + results
        return view('procurement.Contract.contractPaymentReport', compact('contract', 'payments'));
    }


    public function getDocuments($id)
    {
        $documents = DB::table('contract_required_documents as crd')
            ->join('tblbid_required_docs as d', 'd.id', '=', 'crd.tblbid_required_doc_id')
            ->where('crd.tblcontract_detail_id', $id)
            ->select('d.bid_doc_description', 'd.doc_type')
            ->get();

        return response()->json($documents);
    }
}//end class
