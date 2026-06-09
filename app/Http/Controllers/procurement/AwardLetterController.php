<?php
//
namespace App\Http\Controllers\procurement;

use Illuminate\Http\Request;
use App\Library\AnyFileUploadClass;
use App\Http\Controllers\ReuseableController;
use App\Http\Controllers\Controller;
use App\Helpers\FileUploadHelper; 
use App\Mail\ContractLetterMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Session;
use File;
use Auth;
use DB;


class AwardLetterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->getUploadPath = env('UPLOADPATHROOT', null) . 'PaymentRequestDocument/';
        $this->getDownloadPath = env('UPLOADPATHROOT', null) . 'PaymentRequestDocument/';
    }

    //function to view list of all the biddings
    public function viewContractList()
    {
        $data['getContracts'] = DB::table('tblcontract_bidding')
            //->where('tblcontract_bidding.status',3)
            ->where('tblcontract_details.status', 3)
            ->leftjoin('tblcontract_details', 'tblcontract_bidding.contractID', '=', 'tblcontract_details.contract_detailsID')
            ->select(
                'tblcontract_bidding.*',
                'tblcontract_details.*',
                'tblcontract_details.reference_number' // Explicitly select reference_number
            )
            ->groupby('tblcontract_bidding.contractID')
            ->orderby('tblcontract_bidding.contract_biddingID', 'desc')
            ->get();

        return view('procurement.Procurement.view_contracts', $data);
    }

    //function to view list of all the biddings
    public function approveBidlist($id)
    {
        $idx = base64_decode($id);
        $data['id'] = $idx;
        $data['getList'] = DB::table('tblcontract_bidding')
            ->where('tblcontract_bidding.contractID', $idx)
            ->where('tblcontract_bidding.status', 3)
            ->where('tblcontract_details.status', 3)
            ->leftjoin('tblcontract_details', 'tblcontract_bidding.contractID', '=', 'tblcontract_details.contract_detailsID')
            ->leftjoin('tblcontractor_registration', 'tblcontract_bidding.contractorID', '=', 'tblcontractor_registration.contractor_registrationID')
            ->select(
                'tblcontract_bidding.*',
                'tblcontract_details.*',
                'tblcontractor_registration.*',
                'tblcontract_bidding.project_completion as complete',
                'tblcontract_details.reference_number' // Explicitly select reference_number
            )
            ->orderby('tblcontract_bidding.is_award_letter', 'desc')
            ->get();
        
        return view('procurement.Procurement.view_contractor', $data);
    }

    //pust award letter to secretary
    public function pushtoSecretary(Request $request)
    {
        $biddingID   =   $request->input('bid');

        //dd($biddingID);
        DB::table('tblaward_letter')->where('bidding_id', $biddingID)->update(['location_unit' => 2]);
        DB::table('tblcontract_comment')->insert(['biddingID' => $biddingID, 'comment_description' => $request->input('comment'), 'created_by' => Auth::user()->id, 'created_at' => date('Y-m-d')]);
        DB::table('tblcontract_bidding')->where('contract_biddingID', $biddingID)->update(['is_award_letter' => 1]);

        return back()->with('msg', 'Successfully sent to secretary');
    }

    //type and save award letter
    public function saveAwardletter(Request $request)
    {
        $this->validate($request, [

            'letter'        => 'required',
            //  'date_approval' => 'required|date',
            'department_number' => 'required'

        ]);

        $biddingID          =   $request->input('cbid');
        $approval_amt       =   $request->input('approval_amt');
        $date_approval      =   date('Y-m-d'); //$request->input('date_approval');
        $department_number  =   $request->input('department_number');
        $letter             =   $request->input('letter');
        $check = DB::table('tblaward_letter')->where('bidding_id', $biddingID)->exists();

        if ($check == true) {
            DB::table('tblaward_letter')->where('bidding_id', $biddingID)->update(['bidding_id' => $biddingID, 'awarded_amt' => $approval_amt, 'date_issued' => $date_approval, 'department_number' => $department_number, 'award_letter' => $letter]);
        } else {
            DB::table('tblaward_letter')->insert(['bidding_id' => $biddingID, 'awarded_amt' => $approval_amt, 'date_issued' => $date_approval, 'department_number' => $department_number, 'award_letter' => $letter]);
        }

        return back()->with('msg', 'Successfully created');
    }

    //list award letters
    public function listAawardletter($id)
    {
        $bid = base64_decode($id);

        $data['getList'] = DB::table('tblaward_letter')
            ->where('tblaward_letter.bidding_id', $bid)
            ->leftjoin('tblcontract_bidding', 'tblaward_letter.bidding_id', '=', 'tblcontract_bidding.contract_biddingID')
            ->leftjoin('tblcontract_details', 'tblcontract_bidding.contractID', '=', 'tblcontract_details.contract_detailsID')
            ->leftjoin('tblcontractor_registration', 'tblcontract_bidding.contractorID', '=', 'tblcontractor_registration.contractor_registrationID')
            ->select('*', 'tblcontract_bidding.project_completion as complete')
            ->get();

        return view('Procurement.list_award_letter', $data);
    }

    public function viewletter($id)
    {
        //dd('bb');
        $bid = base64_decode($id);

        $data['getList'] = DB::table('tblaward_letter')
            ->where('tblaward_letter.bidding_id', $bid)
            ->first();

        return view('procurement.Procurement.view_award_letter', $data);
    }

    public function editletter($id)
    {
        //dd('bb');
        $bid = base64_decode($id);

        $data['getList'] = DB::table('tblaward_letter')
            ->where('tblaward_letter.bidding_id', $bid)
            ->first();

        return view('procurement.Procurement.edit_award_letter', $data);
    }

    //update award letter
    public function updateAwardletter(Request $request)
    {
        $this->validate($request, [

            'date_award'         => 'date|required',
            'department_number'  => 'required',
            'letter'             => 'required',
        ]);

        $biddingID          =   $request->input('cbid');
        $date               =   $request->input('date_award');
        $department_number  =   $request->input('department_number');
        $letter             =   $request->input('letter');

        DB::table('tblaward_letter')->where('bidding_id', $biddingID)->update(['bidding_id' => $biddingID, 'award_letter' => $letter, 'department_number' => $department_number, 'date_issued' => $date]);

        //return redirect()->route('approve_bidlist')->with('msg','Successfully updated!');
        return back()->with('msg', 'Successfully updated!');
    }
    public function uploadRequest(Request $request)
    {
        $this->validate($request, [
            'biddingID'         => 'required|numeric',
            'document.*'        => 'required|mimes:png,jpg,jpe,jpeg,pdf|max: 5000',
        ], ['biddingID' => 'record information']);

        $complete = 0;

        //try{
        $recordDetails = DB::table('tblcontract_bidding')->where('contract_biddingID', $request['biddingID'])->first();

        //Save document(s)
        if ($recordDetails && $request->hasFile('document')) {
            $descriptionArray = array();
            $getUploadDocumentPath = $this->getUploadPath;
            //get all description
            foreach ($request['description'] as $item) {
                $descriptionArray[] = $item;
            }

            foreach ($request['document'] as $keyDoc => $file) {
                $getArrayResponse = $this->uploadAnyFile($file, $getUploadDocumentPath);
                if ($getArrayResponse) {
                    if ($getArrayResponse['success']) {
                        $complete = DB::table('tblpayment_request_doc')->insertGetId([
                            'contractID'         => ($recordDetails ? $recordDetails->contractID : null),
                            'contractorID'       => ($recordDetails ? $recordDetails->contractorID : null),
                            'file_name'          => $getArrayResponse['newFileName'],
                            'file_description'   => $descriptionArray[$keyDoc],
                            'created_at'         => date('Y-m-d'),
                            'updated_at'         => date('Y-m-d'),
                            'created_by'         => (Auth::check() ? Auth::user()->id : null)
                        ]);
                    }
                }
            }
        }

        //}catch(\Throwable $e){}
        if ($complete) {
            return redirect()->route('confirm-completion')->with('message', 'Your file(s) was successfully uploaded.');
        } else {
            return redirect()->route('confirm-completion')->with('error', 'Sorry, your file(s) was not uploaded.');
        }
    }

    public function uploadAnyFile($file = null, $uploadCompletePathName = null, $maxFileSize = 10, $newExtension = null, $newRadFileName = true)
    {
        $data = new AnyFileUploadClass($file, $uploadCompletePathName, $maxFileSize, $newExtension, $newRadFileName);

        return $data->return();
    } //end function


    //notify performance evaluation for agreement letter
    public function saveAgreementletter(Request $request)
    {

        $biddingID          =   $request->input('cbid');
        $comment            =   $request->input('comment');
        $contractID = DB::table('tblcontract_bidding')->where('contract_biddingID', $biddingID)->first();

        if ($comment == null) {
            DB::table('tblcontract_bidding')->where('contract_biddingID', $biddingID)->update(['is_agreement' => 1, 'is_agreement_reverse' => 0]);
        } else {
            DB::table('tblcontract_bidding')->where('contract_biddingID', $biddingID)->update(['is_agreement' => 1, 'is_agreement_reverse' => 0]);
            DB::table('tblcontract_comment')->insert(['biddingID' => $biddingID, 'contractID' => $contractID->contractID, 'comment_description' => $comment, 'created_by' => Auth::user()->id, 'created_at' => date('Y-m-d')]);
        }


        return back()->with('msg', 'Successfully sent to Performance Evaluation');
    }

    //reverse agreement letter
    public function recallLetter(Request $request)
    {

        $biddingID          =   $request->input('bid');

        DB::table('tblcontract_bidding')->where('contract_biddingID', $biddingID)->update(['is_agreement' => 0]);

        return back()->with('msg', 'Successfully recalled');
    }

    public function viewAgreedletter($id)
    {

        $bid = base64_decode($id);
        //dd($bid);
        $data['getList'] = DB::table('tblagreement_letter')
            ->where('tblagreement_letter.bidding_id', $bid)
            ->first();
        //dd($data['getList']->agreement_letterID);
        $data['getDocExist'] = DB::table('tblagreement_documents')
            ->where('agreement_letter_id', $data['getList']->agreement_letterID)
            ->exists();

        $data['getDocList'] = DB::table('tblagreement_documents')
            ->where('agreement_letter_id', $data['getList']->agreement_letterID)
            ->get();

        return view('procurement.Procurement.view_agreement_letter', $data);
    }

    public function confirmAgreement($id)
    {

        $bid = base64_decode($id);
        //dd($bid);
        $data['getList'] = DB::table('tblagreement_letter')
            ->where('agreement_letterID', $bid)
            ->update(['accept_status' => 1]);

        return back()->with('msg', 'Successfully confirmed');
    }


    public function index01()
    {
        // Get all contracts with their bidding information
        $contracts = DB::table('tblcontract_bidding')
            ->leftJoin('tblcontract_details', 'tblcontract_bidding.contractID', '=', 'tblcontract_details.contract_detailsID')
            ->leftJoin('tblcontractor_registration', 'tblcontract_bidding.contractorID', '=', 'tblcontractor_registration.contractor_registrationID')
            ->leftJoin('tblcontract_letters', function($join) {
                $join->on('tblcontract_bidding.contract_biddingID', '=', 'tblcontract_letters.contract_bidding_id')
                    ->where('tblcontract_letters.status', 'active');
            })
            ->select(
                'tblcontract_bidding.*',  // This already includes awarded_amount
                'tblcontract_details.contract_name',
                'tblcontract_details.lot_number',
                'tblcontract_details.proposed_budget',
                // 'tblcontract_bidding.awarded_amount', // REMOVE THIS LINE - it's duplicate
                'tblcontractor_registration.company_name',
                DB::raw('GROUP_CONCAT(CASE WHEN tblcontract_letters.letter_type = "recommendation" THEN tblcontract_letters.letter_id END) as has_recommendation'),
                DB::raw('GROUP_CONCAT(CASE WHEN tblcontract_letters.letter_type = "award" THEN tblcontract_letters.letter_id END) as has_award_letter')
            )
            ->where('tblcontract_bidding.status', 3)
            ->groupBy(
                'tblcontract_bidding.contract_biddingID',
                'tblcontract_bidding.contractID',
                'tblcontract_bidding.contractorID',
                'tblcontract_bidding.contractor_remark',
                'tblcontract_bidding.bidding_amount',
                'tblcontract_bidding.awarded_amount',  // Include this in group by
                'tblcontract_bidding.date_submitted',
                'tblcontract_bidding.created_by',
                'tblcontract_bidding.created_at',
                'tblcontract_bidding.updated_at',
                'tblcontract_bidding.status',
                'tblcontract_bidding.tech_evaluation',
                'tblcontract_bidding.recommendation',
                'tblcontract_bidding.current_location',
                'tblcontract_bidding.project_completion',
                'tblcontract_bidding.role_unit_id',
                'tblcontract_bidding.is_award_letter',
                'tblcontract_bidding.is_agreement',
                'tblcontract_bidding.is_agreement_reverse',
                'tblcontract_bidding.bid_approval_unit',
                'tblcontract_details.contract_name',
                'tblcontract_details.lot_number',
                'tblcontract_details.proposed_budget',
                'tblcontractor_registration.company_name'
            )
            ->orderBy('tblcontract_bidding.created_at', 'desc')
            ->paginate(15);

        return view('procurement.Procurement.upload_letters', compact('contracts'));
    }

    public function index02()
    {
        // Get all contracts with their bidding information and letters
        $contracts = DB::table('tblcontract_bidding')
            ->leftJoin('tblcontract_details', 'tblcontract_bidding.contractID', '=', 'tblcontract_details.contract_detailsID')
            ->leftJoin('tblcontractor_registration', 'tblcontract_bidding.contractorID', '=', 'tblcontractor_registration.contractor_registrationID')
            ->leftJoin('tblcontract_letters as rec_letters', function($join) {
                $join->on('tblcontract_bidding.contract_biddingID', '=', 'rec_letters.contract_bidding_id')
                    ->where('rec_letters.letter_type', 'recommendation')
                    ->where('rec_letters.status', 'active');
            })
            ->leftJoin('tblcontract_letters as award_letters', function($join) {
                $join->on('tblcontract_bidding.contract_biddingID', '=', 'award_letters.contract_bidding_id')
                    ->where('award_letters.letter_type', 'award')
                    ->where('award_letters.status', 'active');
            })
            ->select(
                'tblcontract_bidding.*',
                'tblcontract_details.contract_name',
                'tblcontract_details.lot_number',
                'tblcontract_details.proposed_budget',
                'tblcontractor_registration.company_name',
                
                // Recommendation letter details
                'rec_letters.letter_id as rec_letter_id',
                'rec_letters.file_name as rec_file_name',
                'rec_letters.original_file_name as rec_original_name',
                'rec_letters.file_path as rec_file_path',
                'rec_letters.full_url as rec_full_url',
                
                // Award letter details
                'award_letters.letter_id as award_letter_id',
                'award_letters.file_name as award_file_name',
                'award_letters.original_file_name as award_original_name',
                'award_letters.file_path as award_file_path',
                'award_letters.full_url as award_full_url',
                
                DB::raw('CASE WHEN rec_letters.letter_id IS NOT NULL THEN 1 ELSE 0 END as has_recommendation'),
                DB::raw('CASE WHEN award_letters.letter_id IS NOT NULL THEN 1 ELSE 0 END as has_award_letter')
            )
            ->where('tblcontract_bidding.status', 3)
            ->groupBy(
                'tblcontract_bidding.contract_biddingID',
                'tblcontract_bidding.contractID',
                'tblcontract_bidding.contractorID',
                'tblcontract_bidding.contractor_remark',
                'tblcontract_bidding.bidding_amount',
                'tblcontract_bidding.awarded_amount',
                'tblcontract_bidding.date_submitted',
                'tblcontract_bidding.created_by',
                'tblcontract_bidding.created_at',
                'tblcontract_bidding.updated_at',
                'tblcontract_bidding.status',
                'tblcontract_bidding.tech_evaluation',
                'tblcontract_bidding.recommendation',
                'tblcontract_bidding.current_location',
                'tblcontract_bidding.project_completion',
                'tblcontract_bidding.role_unit_id',
                'tblcontract_bidding.is_award_letter',
                'tblcontract_bidding.is_agreement',
                'tblcontract_bidding.is_agreement_reverse',
                'tblcontract_bidding.bid_approval_unit',
                'tblcontract_details.contract_name',
                'tblcontract_details.lot_number',
                'tblcontract_details.proposed_budget',
                'tblcontractor_registration.company_name',
                'rec_letters.letter_id',
                'rec_letters.file_name',
                'rec_letters.original_file_name',
                'rec_letters.file_path',
                'rec_letters.full_url',
                'award_letters.letter_id',
                'award_letters.file_name',
                'award_letters.original_file_name',
                'award_letters.file_path',
                'award_letters.full_url'
            )
            ->orderBy('tblcontract_bidding.created_at', 'desc')
            ->paginate(15);

        return view('procurement.Procurement.upload_letters', compact('contracts'));
    }

    public function index()
    {
        // Get all contracts with their bidding information and letters
        $contracts = DB::table('tblcontract_bidding')
            ->leftJoin('tblcontract_details', 'tblcontract_bidding.contractID', '=', 'tblcontract_details.contract_detailsID')
            ->leftJoin('tblcontractor_registration', 'tblcontract_bidding.contractorID', '=', 'tblcontractor_registration.contractor_registrationID')
            ->leftJoin('tblcontract_letters as rec_letters', function($join) {
                $join->on('tblcontract_bidding.contract_biddingID', '=', 'rec_letters.contract_bidding_id')
                     ->where('rec_letters.letter_type', 'recommendation')
                     ->where('rec_letters.status', 'active');
            })
            ->leftJoin('tblcontract_letters as award_letters', function($join) {
                $join->on('tblcontract_bidding.contract_biddingID', '=', 'award_letters.contract_bidding_id')
                     ->where('award_letters.letter_type', 'award')
                     ->where('award_letters.status', 'active');
            })
            ->select(
                'tblcontract_bidding.*',
                'tblcontract_details.contract_name',
                'tblcontract_details.lot_number',
                'tblcontract_details.proposed_budget',
                'tblcontractor_registration.company_name',
                'tblcontractor_registration.email_address as contractor_email',
                'tblcontractor_registration.contact_person',
                
                // Recommendation letter details
                'rec_letters.letter_id as rec_letter_id',
                'rec_letters.file_name as rec_file_name',
                'rec_letters.original_file_name as rec_original_name',
                'rec_letters.file_path as rec_file_path',
                'rec_letters.full_url as rec_full_url',
                'rec_letters.created_at as rec_uploaded_at',
                
                // Award letter details
                'award_letters.letter_id as award_letter_id',
                'award_letters.file_name as award_file_name',
                'award_letters.original_file_name as award_original_name',
                'award_letters.file_path as award_file_path',
                'award_letters.full_url as award_full_url',
                'award_letters.created_at as award_uploaded_at',
                
                DB::raw('CASE WHEN rec_letters.letter_id IS NOT NULL THEN 1 ELSE 0 END as has_recommendation'),
                DB::raw('CASE WHEN award_letters.letter_id IS NOT NULL THEN 1 ELSE 0 END as has_award_letter')
            )
            ->where('tblcontract_bidding.status', 3)
            ->groupBy(
                'tblcontract_bidding.contract_biddingID',
                'tblcontract_bidding.contractID',
                'tblcontract_bidding.contractorID',
                'tblcontract_bidding.contractor_remark',
                'tblcontract_bidding.bidding_amount',
                'tblcontract_bidding.awarded_amount',
                'tblcontract_bidding.date_submitted',
                'tblcontract_bidding.created_by',
                'tblcontract_bidding.created_at',
                'tblcontract_bidding.updated_at',
                'tblcontract_bidding.status',
                'tblcontract_bidding.tech_evaluation',
                'tblcontract_bidding.recommendation',
                'tblcontract_bidding.current_location',
                'tblcontract_bidding.project_completion',
                'tblcontract_bidding.role_unit_id',
                'tblcontract_bidding.is_award_letter',
                'tblcontract_bidding.is_agreement',
                'tblcontract_bidding.is_agreement_reverse',
                'tblcontract_bidding.bid_approval_unit',
                'tblcontract_details.contract_name',
                'tblcontract_details.lot_number',
                'tblcontract_details.proposed_budget',
                'tblcontractor_registration.company_name',
                'tblcontractor_registration.email_address',
                'tblcontractor_registration.contact_person',
                'rec_letters.letter_id',
                'rec_letters.file_name',
                'rec_letters.original_file_name',
                'rec_letters.file_path',
                'rec_letters.full_url',
                'rec_letters.created_at',
                'award_letters.letter_id',
                'award_letters.file_name',
                'award_letters.original_file_name',
                'award_letters.file_path',
                'award_letters.full_url',
                'award_letters.created_at'
            )
            ->orderBy('tblcontract_bidding.created_at', 'desc')
            ->paginate(15);

        return view('procurement.Procurement.upload_letters', compact('contracts'));
    }
        /**
     * Show upload form for specific contract
     */
    public function create($bidding_id)
    {
        $bidding_id = base64_decode($bidding_id);
        
        $contract = DB::table('tblcontract_bidding')
            ->leftJoin('tblcontract_details', 'tblcontract_bidding.contractID', '=', 'tblcontract_details.contract_detailsID')
            ->leftJoin('tblcontractor_registration', 'tblcontract_bidding.contractorID', '=', 'tblcontractor_registration.contractor_registrationID')
            ->where('tblcontract_bidding.contract_biddingID', $bidding_id)
            ->select(
                'tblcontract_bidding.*',
                'tblcontract_details.contract_name',
                'tblcontract_details.lot_number',
                'tblcontract_details.reference_number',
                'tblcontractor_registration.company_name',
                'tblcontractor_registration.email_address as contractor_email'
            )
            ->first();

        if (!$contract) {
            return redirect()->route('upload-letters.index')->with('error', 'Contract not found');
        }

        // Get existing letters
        $letters = DB::table('tblcontract_letters')
            ->where('contract_bidding_id', $bidding_id)
            ->where('status', 'active')
            ->get();

        return view('procurement.Procurement.upload_letter_form', compact('contract', 'letters', 'bidding_id'));
    }

    /**
     * Upload single letter using FileUploadHelper
     */
    public function upload(Request $request)
    {
        $request->validate([
            'contract_bidding_id' => 'required|exists:tblcontract_bidding,contract_biddingID',
            'letter_type' => 'required|in:recommendation,award',
            'letter_file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:100', // Max 100KB
            // 'description' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $file = $request->file('letter_file');
            $originalName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            
            // Define folder based on letter type
            $folder = 'uploads/letters/' . $request->letter_type;
            
            // Generate unique filename
            $filename = time() . '_' . uniqid() . '_' . $originalName;
            
            // Use FileUploadHelper to upload
            $fileUrl = FileUploadHelper::upload($file, $folder, $filename);
            
            // Extract path from URL for database storage (remove domain)
            $filePath = parse_url($fileUrl, PHP_URL_PATH);
            
            // Save to database
            $letterId = DB::table('tblcontract_letters')->insertGetId([
                'contract_bidding_id' => $request->contract_bidding_id,
                'letter_type' => $request->letter_type,
                'file_name' => $filename,
                'original_file_name' => $originalName,
                'file_path' => $filePath,
                'full_url' => $fileUrl, // Store full URL for easy access
                'file_size' => $fileSize,
                'description' => $request->description,
                'uploaded_by' => Auth::id(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return redirect()->back()->with('success', ucfirst($request->letter_type) . ' letter uploaded successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to upload letter: ' . $e->getMessage());
        }
    }

    /**
     * Upload multiple letters at once using FileUploadHelper
     */
    public function uploadMultiple(Request $request)
    {
        $request->validate([
            'contract_bidding_id' => 'required|exists:tblcontract_bidding,contract_biddingID',
            'recommendation_letter' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:100',
            'award_letter' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:100',
            'recommendation_description' => 'nullable|string|max:500',
            'award_description' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();
            $uploadCount = 0;
            $uploadedFiles = [];

            // Upload recommendation letter if provided
            if ($request->hasFile('recommendation_letter')) {
                $result = $this->processUploadWithHelper(
                    $request->file('recommendation_letter'),
                    $request->contract_bidding_id,
                    'recommendation',
                    $request->recommendation_description
                );
                $uploadCount++;
                $uploadedFiles[] = $result;
            }

            // Upload award letter if provided
            if ($request->hasFile('award_letter')) {
                $result = $this->processUploadWithHelper(
                    $request->file('award_letter'),
                    $request->contract_bidding_id,
                    'award',
                    $request->award_description
                );
                $uploadCount++;
                $uploadedFiles[] = $result;
            }

            DB::commit();

            if ($uploadCount > 0) {
                return redirect()->back()->with('success', $uploadCount . ' letter(s) uploaded successfully!');
            } else {
                return redirect()->back()->with('error', 'No files were selected for upload.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to upload letters: ' . $e->getMessage());
        }
    }

    /**
     * Process individual file upload using FileUploadHelper
     */
    private function processUploadWithHelper($file, $biddingId, $letterType, $description = null)
    {
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        
        // Define folder based on letter type
        $folder = 'uploads/letters/' . $letterType;
        
        // Generate unique filename with timestamp to avoid conflicts
        $filename = time() . '_' . $letterType . '_' . uniqid() . '_' . $originalName;
        
        // Use FileUploadHelper to upload
        $fileUrl = FileUploadHelper::upload($file, $folder, $filename);
        
        // Extract path from URL for database storage (remove domain)
        $filePath = parse_url($fileUrl, PHP_URL_PATH);
        
        // Save to database
        DB::table('tblcontract_letters')->insert([
            'contract_bidding_id' => $biddingId,
            'letter_type' => $letterType,
            'file_name' => $filename,
            'original_file_name' => $originalName,
            'file_path' => $filePath,
            'full_url' => $fileUrl,
            'file_size' => $fileSize,
            'description' => $description,
            'uploaded_by' => Auth::id(),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return [
            'type' => $letterType,
            'filename' => $filename,
            'url' => $fileUrl
        ];
    }

    /**
     * Download letter
     */
    public function download($letter_id)
    {
        $letter = DB::table('tblcontract_letters')->where('letter_id', $letter_id)->first();

        if (!$letter) {
            return redirect()->back()->with('error', 'Letter not found');
        }

        $path = public_path(ltrim($letter->file_path, '/'));
        
        if (!file_exists($path)) {
            return redirect()->back()->with('error', 'File not found');
        }
        
        return response()->download($path, $letter->original_file_name);
    }

    /**
     * View letter in browser
     */
    public function view($letter_id)
    {
        $letter = DB::table('tblcontract_letters')->where('letter_id', $letter_id)->first();

        if (!$letter) {
            return redirect()->back()->with('error', 'Letter not found');
        }

        // If we have full URL stored, redirect to it
        if (isset($letter->full_url) && !empty($letter->full_url)) {
            return redirect($letter->full_url);
        }

        $path = public_path(ltrim($letter->file_path, '/'));
        
        if (!file_exists($path)) {
            return redirect()->back()->with('error', 'File not found');
        }
        
        return response()->file($path);
    }

    /**
     * Delete letter (soft delete - set status to inactive)
     */
    public function delete($letter_id)
    {
        try {
            $letter = DB::table('tblcontract_letters')->where('letter_id', $letter_id)->first();
            
            if (!$letter) {
                return redirect()->back()->with('error', 'Letter not found');
            }

            // Soft delete - just update status
            DB::table('tblcontract_letters')
                ->where('letter_id', $letter_id)
                ->update([
                    'status' => 'inactive',
                    'updated_at' => now()
                ]);

            return redirect()->back()->with('success', 'Letter deleted successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete letter: ' . $e->getMessage());
        }
    }

    /**
     * Get file URL for display
     */
    public function getFileUrl($letter)
    {
        if (isset($letter->full_url) && !empty($letter->full_url)) {
            return $letter->full_url;
        }
        
        return url($letter->file_path);
    }


    // Send letter email to contractor

    public function sendEmail($letter_id)
    {
        try {
            // Get letter details - use letter_id, not id
            $letter = DB::table('tblcontract_letters')
                ->where('letter_id', $letter_id)
                ->where('status', 'active')
                ->first();

            if (!$letter) {
                return redirect()->back()->with('error', 'Letter not found with ID: ' . $letter_id);
            }

            // Get contract and contractor details
            $contract = DB::table('tblcontract_bidding')
                ->leftJoin('tblcontract_details', 'tblcontract_bidding.contractID', '=', 'tblcontract_details.contract_detailsID')
                ->leftJoin('tblcontractor_registration', 'tblcontract_bidding.contractorID', '=', 'tblcontractor_registration.contractor_registrationID')
                ->where('tblcontract_bidding.contract_biddingID', $letter->contract_bidding_id)
                ->select(
                    'tblcontract_bidding.*',
                    'tblcontract_details.contract_name',
                    'tblcontract_details.lot_number',
                    'tblcontractor_registration.company_name',
                    'tblcontractor_registration.email_address as contractor_email',
                    'tblcontractor_registration.contact_person'
                )
                ->first();

            if (!$contract) {
                return redirect()->back()->with('error', 'Contract details not found');
            }

            if (empty($contract->contractor_email)) {
                return redirect()->back()->with('error', 'Contractor email address not found');
            }

            // Send email with letter
            Mail::to($contract->contractor_email)->send(
                new ContractLetterMail($contract, $letter, $letter->letter_type, $contract)
            );

            // Log the email sent
            DB::table('tblcontract_email_logs')->insert([
                'contract_bidding_id' => $contract->contract_biddingID,
                'letter_id' => $letter_id,
                'email_type' => $letter->letter_type . '_letter',
                'recipient_email' => $contract->contractor_email,
                'recipient_name' => $contract->company_name,
                'sent_by' => Auth::id(),
                'sent_at' => now(),
                'status' => 'sent',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect()->back()->with('success', ucfirst($letter->letter_type) . ' letter sent successfully to ' . $contract->contractor_email);

        } catch (\Exception $e) {
            Log::error('Email sending failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    /**
     * Send bulk emails for multiple letters
     */
    public function sendBulkEmails(Request $request)
    {
        $request->validate([
            'letter_ids' => 'required|array',
            'letter_ids.*' => 'exists:tblcontract_letters,letter_id'
        ]);

        $successCount = 0;
        $failedCount = 0;
        $failedEmails = [];

        foreach ($request->letter_ids as $letter_id) {
            try {
                $letter = DB::table('tblcontract_letters')
                    ->where('letter_id', $letter_id)
                    ->where('status', 'active')
                    ->first();

                if (!$letter) {
                    $failedCount++;
                    $failedEmails[] = "Letter ID {$letter_id}: Letter not found";
                    continue;
                }

                $contract = DB::table('tblcontract_bidding')
                    ->leftJoin('tblcontract_details', 'tblcontract_bidding.contractID', '=', 'tblcontract_details.contract_detailsID')
                    ->leftJoin('tblcontractor_registration', 'tblcontract_bidding.contractorID', '=', 'tblcontractor_registration.contractor_registrationID')
                    ->where('tblcontract_bidding.contract_biddingID', $letter->contract_bidding_id)
                    ->select(
                        'tblcontract_bidding.*',
                        'tblcontract_details.contract_name',
                        'tblcontract_details.lot_number',
                        'tblcontractor_registration.company_name',
                        'tblcontractor_registration.email_address as contractor_email',
                        'tblcontractor_registration.contact_person'
                    )
                    ->first();

                if (!$contract) {
                    $failedCount++;
                    $failedEmails[] = "Letter ID {$letter_id}: Contract details not found";
                    continue;
                }

                if (empty($contract->contractor_email)) {
                    $failedCount++;
                    $failedEmails[] = "Letter ID {$letter_id}: No email address found for contractor";
                    continue;
                }

                // REMOVE THIS FILE CHECK - let the ContractLetterMail handle it
                // $filePath = public_path('uploads/letters/' . $letter->letter_type . '/' . $letter->file_name);
                // if (!file_exists($filePath)) {
                //     $failedCount++;
                //     $failedEmails[] = "Letter ID {$letter_id}: File not found at path: " . $filePath;
                //     continue;
                // }

                // Send email - same as sendEmail function
                Mail::to($contract->contractor_email)->send(
                    new ContractLetterMail($contract, $letter, $letter->letter_type, $contract)
                );

                // Log the email sent
                DB::table('tblcontract_email_logs')->insert([
                    'contract_bidding_id' => $contract->contract_biddingID,
                    'letter_id' => $letter_id,
                    'email_type' => $letter->letter_type . '_letter',
                    'recipient_email' => $contract->contractor_email,
                    'recipient_name' => $contract->company_name,
                    'sent_by' => Auth::id(),
                    'sent_at' => now(),
                    'status' => 'sent',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $successCount++;

            } catch (\Exception $e) {
                $failedCount++;
                $failedEmails[] = "Letter ID {$letter_id}: " . $e->getMessage();
                Log::error('Bulk email failed for letter ID ' . $letter_id . ': ' . $e->getMessage());
            }
        }

        $message = "Emails sent: {$successCount} successful, {$failedCount} failed.";
        
        if ($successCount > 0) {
            return redirect()->back()->with('success', $message);
        } else {
            return redirect()->back()->with('error', $message . ' Details: ' . implode(' | ', $failedEmails));
        }
    }

    /**
     * Get email logs
     */
    public function emailLogs($contract_bidding_id = null)
    {
        $query = DB::table('tblcontract_email_logs')
            ->leftJoin('tblcontract_bidding', 'tblcontract_email_logs.contract_bidding_id', '=', 'tblcontract_bidding.contract_biddingID')
            ->leftJoin('tblcontract_details', 'tblcontract_bidding.contractID', '=', 'tblcontract_details.contract_detailsID')
            ->leftJoin('users', 'tblcontract_email_logs.sent_by', '=', 'users.id')
            ->select(
                'tblcontract_email_logs.*',
                'tblcontract_details.contract_name',
                'users.name as sent_by_name'
            );

        if ($contract_bidding_id) {
            $query->where('tblcontract_email_logs.contract_bidding_id', $contract_bidding_id);
        }

        $logs = $query->orderBy('tblcontract_email_logs.sent_at', 'desc')
            ->paginate(20);

        return view('procurement.Procurement.email_logs', compact('logs'));
    }

    public function fixFilenames()
    {
        $letters = DB::table('tblcontract_letters')->get();
        $fixed = 0;
        
        foreach ($letters as $letter) {
            $oldPath = public_path('uploads/letters/' . $letter->letter_type . '/' . $letter->file_name);
            
            // Create a new filename without spaces
            $newFileName = str_replace(' ', '_', $letter->file_name);
            $newPath = public_path('uploads/letters/' . $letter->letter_type . '/' . $newFileName);
            
            if (file_exists($oldPath) && $oldPath != $newPath) {
                if (rename($oldPath, $newPath)) {
                    // Update database with new filename
                    DB::table('tblcontract_letters')
                        ->where('letter_id', $letter->letter_id)
                        ->update([
                            'file_name' => $newFileName,
                            'original_file_name' => $letter->original_file_name, // Keep original
                            'file_path' => '/uploads/letters/' . $letter->letter_type . '/' . $newFileName,
                            'full_url' => url('/uploads/letters/' . $letter->letter_type . '/' . $newFileName)
                        ]);
                    $fixed++;
                }
            }
        }
        
        return "Fixed $fixed files";
    }

}//end class




