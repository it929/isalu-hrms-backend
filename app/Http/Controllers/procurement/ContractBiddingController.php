<?php

namespace App\Http\Controllers\procurement;



use App\Helpers\FileUploadHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ContractBiddingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function create1()
    {
        $data['contractor'] = DB::table('tblcontractor_registration')->where('status', '=', 1)->get();
        $data['contract'] = DB::table('tblcontract_details')->where('status', '=', 1)->get();
        $data['requiredDocs'] = DB::table('tblbid_required_docs')->get();
        $data['requiredDocsFinancial'] = DB::table('tblbid_required_docs')->where('doc_type', '=', 'Financial')->get();
        $data['requiredDocsTechnical'] = DB::table('tblbid_required_docs')->where('doc_type', '=', 'Technical')->get();
        //dd($data['requiredDocsFinancial']);
        return view('procurement.ContractBidding.add', $data);
    }


    public function create()
    {
        // Get records from tblcontractor_registration
        $contractorReg = DB::table('tblcontractor_registration')
            ->where('status', '=', 1)
            ->select(
                'contractor_registrationID as id', 
                'company_name as name', 
                DB::raw("'Registration' as source")
                // Removed email and phone
            )
            ->get();
        
        // Get records from tblcontractor (without email)
        $contractor = DB::table('tblcontractor')
            ->where('status', '=', 1)
            ->select(
                'id', 
                'contractor as name', 
                DB::raw("'Main' as source")
                // Removed emailAddress and phoneNo
            )
            ->get();
        
        // Merge both collections
        $data['allContractors'] = $contractorReg->merge($contractor);
        
        // Sort by name if needed
        $data['allContractors'] = $data['allContractors']->sortBy('name');
        
        $data['contract'] = DB::table('tblcontract_details')->where('status', '=', 1)->get();
        $data['requiredDocs'] = DB::table('tblbid_required_docs')->get();
        $data['requiredDocsFinancial'] = DB::table('tblbid_required_docs')->where('doc_type', '=', 'Financial')->get();
        $data['requiredDocsTechnical'] = DB::table('tblbid_required_docs')->where('doc_type', '=', 'Technical')->get();
        
        return view('procurement.ContractBidding.add', $data);
    }

    public function getContractorDocuments111($id)
    {
        // 1️⃣ All required Technical documents
        $requiredDocs = DB::table('tblbid_required_docs')
            ->where('doc_type', 'Technical') // matches your table
            ->orderBy('bid_doc_description', 'ASC')
            ->get();

        // 2️⃣ Contractor uploaded documents
        $uploadedDocs = DB::table('tblcontractor_document')
            ->leftJoin('tblbid_required_docs as cr', 'tblcontractor_document.bidRequiredDocsId', '=', 'cr.id')
            ->where('contractorID', $id)
            ->where('cr.doc_type', 'Technical')
            ->where('status', 1)
            ->select('cd.*', 'cr.bid_doc_description', 'cr.doc_type')
            ->get();

        // 3️⃣ Get uploaded doc IDs
        $uploadedIds = $uploadedDocs->pluck('bidRequiredDocsId')->toArray();


        // 4️⃣ Missing docs (required but not uploaded)
        $missingDocs = $requiredDocs
            ->whereNotIn('id', $uploadedIds)
            ->values();

        return response()->json([
            'uploaded' => $uploadedDocs,
            'missing'  => $missingDocs
        ]);
    }

    public function getContractorDocuments($contractorId, $contractId)
    {
        // 1) Required docs for this contract (Technical)
        $requiredDocs = DB::table('contract_required_documents as crd')
            ->join('tblbid_required_docs as rd', 'crd.tblbid_required_doc_id', '=', 'rd.id')
            ->where('crd.tblcontract_detail_id', $contractId)
            ->where('rd.doc_type', 'Technical')
            ->select(
                'crd.id as pivotId',                 // pivot row id (56,57,...)
                'rd.id as docId',                    // ✅ required doc id (1,2,3,7,...)
                'rd.bid_doc_description',
                'rd.doc_type'
            )
            ->orderBy('rd.bid_doc_description', 'ASC')
            ->get();

        // 2) Contractor library docs (uploaded once)
        $uploadedDocs = DB::table('tblcontractor_document as cd')
            ->join('tblbid_required_docs as rd', 'cd.bidRequiredDocsId', '=', 'rd.id')
            ->where('cd.contractorID', $contractorId)
            ->where('cd.status', 1)
            ->where('rd.doc_type', 'Technical')
            ->select(
                'cd.contractor_documentID',
                'rd.id as docId',                    // ✅ same id system as required docs
                'rd.bid_doc_description',
                'rd.doc_type',
                'cd.file_name'
            )
            ->get();

        $uploadedIds = $uploadedDocs->pluck('docId')->toArray();

        // 3) Missing = required docs not in contractor library
        $missingDocs = $requiredDocs->whereNotIn('docId', $uploadedIds)->values();

        return response()->json([
            'uploaded' => $uploadedDocs,
            'missing'  => $missingDocs,
        ]);
    }


    public function deleteDocument($id)
    {
        // Log::info($id);
        $doc = DB::table('tblcontractor_document')->where('contractor_documentID', $id)->first();

        if ($doc) {
            DB::table('tblcontractor_document')->where('contractor_documentID', $id)->delete();
        }

        return response()->json(['success' => true]);
    }


    public function saveBiddingBackup19_02_2026(Request $request)
    {
        //dd($request->all());
        Session::flash('contractorSess', $request['contractor']);
        Session::flash('contractSess', $request['contract']);
        Session::flash('contractRemarkSess', $request['contractorRemark']);
        Session::flash('amountSess', str_replace(",", "", $request['biddingAmount']));
        Session::flash('dateSess', $request['date']);
        // Session::flash('descSess',$request['description'][0]);
        $this->validate($request, [
            'contract' => 'required',
            'contractor' => 'required',
            'contractorRemark' => 'required',
            'biddingAmount' => 'required',
            'date' => 'required',
        ]);

        $isclosed =  $check = DB::table('tblcontract_details')->where('contract_detailsID', '=', $request['contract'])->where('closed_bidding', '=', 1)->count();
        if ($isclosed == 1) {
            return back()->with('err', 'Bidding for this contract is closed, You cannot enter new bid');
        }

        $check = DB::table('tblcontract_bidding')->where('contractID', '=', $request['contract'])->where('contractorID', '=', $request['contractor'])->count();
        if ($check > 0) {
            return back()->with('err', 'Bid already entered');
        }

        $file = $request->file('document');
        //$extension = $file->getClientOriginalExtension();
        $ext = array("jpg", "gif", "png", "pdf", "doc", "docx");

        if ($file != '') {
            foreach ($file as $key => $val) {
                $extension = $val->getClientOriginalExtension();
                //dd($extension);

                /* if($extension != 'jpg' || $extension != 'png' || $extension != 'gif' || $extension != 'pdf' || $extension != 'doc' || $extension != 'docx')*/
                if (!in_array($extension, $ext)) {
                    return back()->with('err', 'File not Allowed !. choose either an Image, pdf or Word document to upload');
                }
                //   dd($val->getSize());
                $d = $val->getSize();
                if ($d > 1000000000) {
                    return back()->with('err', 'File too large');
                }

                // if($request['description'][$key] == '')
                // {
                //     return back()->with('err','Please, Type in the document description');
                // }
            }
        }

        $lastid = DB::table('tblcontract_bidding')->insertGetId([
            'contractID' => $request['contract'],
            'contractorID' => $request['contractor'],
            'contractor_remark' => $request['contractorRemark'],
            'bidding_amount' => str_replace(",", "", $request['biddingAmount']),
            'awarded_amount' => str_replace(",", "", $request['biddingAmount']),
            'date_submitted' => date('Y-m-d', strtotime($request['date'])),
            'created_by' => Auth::user()->id,
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
        ]);

        if ($lastid) {
            $d = base64_encode($lastid);

            if ($file != '') {
                //dd($file->getClientOriginalName());
                //$filename = array();
                $docDescIds = $request->input('docDescId');
                $location = public_path('BiddingDocument');
                foreach ($file as $key => $document) {
                    // Check if there is a corresponding docDescId for the current document
                    if (isset($docDescIds[$key])) {
                        $docDescId = $docDescIds[$key];
                        $filename = $document->getClientOriginalName();
                        $document->move($location, $filename);

                        DB::table('tblcontractor_contract_bid_document')->insert([
                            'biddingID' => $lastid,
                            'contractorID' => $request['contractor'],
                            'contractID' => $request['contract'],
                            'bidDocID' => $docDescId,
                            'bidDocument' => $filename,
                            'updated_at' => date('Y-m-d')
                        ]);
                    } else {
                    }
                }
                // foreach($file as $key=>$val)
                // {
                //     //dd($val);
                //      $filename = $val->getClientOriginalName();
                //      //dd($request['description'][$key]);
                //     DB::table('tblcontractor_bidding_document')->insert([
                //     'biddingID' => $lastid,
                //     'file_name' => $filename,
                //     'file_description' => $request['description'][$key],
                //     'created_by' => Auth::user()->id,
                //     'created_at' => date('Y-m-d'),
                //     'updated_at' => date('Y-m-d'),
                //     ]);
                //     $move = $val->move($location,$filename);
                // }


            }
            return redirect('edit/bid/' . $d)->with('msg', 'Successfully Added');
        } else {
            return redirect('/add-bidding');
        }

        // return redirect('/add-bidding');
    }

    public function saveBidding1(Request $request)
    {

        // Log::info("Received saveBidding request", ['request_data' => $request->all()]);
        // dd($request->all());

        // Preserve form inputs in session
        Session::flash('contractorSess', $request['contractor']);
        Session::flash('contractSess', $request['contract']);
        Session::flash('contractRemarkSess', $request['contractorRemark']);
        Session::flash('amountSess', str_replace(",", "", $request['biddingAmount']));
        Session::flash('dateSess', $request['date']);

        $request->merge([
            'biddingAmount'           => str_replace(',', '', $request->biddingAmount),
        ]);


        // Validate request
        $this->validate($request, [
            'contract' => 'required',
            'contractor' => 'required',
            'contractorRemark' => 'required',
            'biddingAmount' => 'required',
            'date' => 'required',
        ]);


        // Check if bidding is closed for the selected contract
        $isClosed = DB::table('tblcontract_details')
            ->where('contract_detailsID', $request['contract'])
            ->where('closed_bidding', 1)
            ->count();

        if ($isClosed == 1) {
            return back()->with('err', 'Bidding for this contract is closed, You cannot enter new bid');
        }
        // dd($request->all());


        // Check if contractor has already submitted a bid
        $existingBid  = DB::table('tblcontract_bidding')
            ->where('contractID', $request['contract'])
            ->where('contractorID', $request['contractor'])
            ->count();

        if ($existingBid  > 0) {
            return back()->with('err', 'Bid already entered for this contractor.');
        }

        // Validate uploaded files
        $files = $request->file('document');
        $allowedExtensions = ["jpg", "gif", "png", "pdf", "doc", "docx"];

        if (!empty($files)) {
            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension();
                if (!in_array($extension, $allowedExtensions)) {
                    return back()->with('err', 'File not allowed! Choose either an image, PDF, or Word document.');
                }

                if ($file->getSize() > 1000000000) { // 1GB
                    return back()->with('err', 'File too large.');
                }
            }
        }

        // Insert bid record
        $lastId = DB::table('tblcontract_bidding')->insertGetId([
            'contractID' => $request['contract'],
            'contractorID' => $request['contractor'],
            'contractor_remark' => $request['contractorRemark'],
            'bidding_amount' => str_replace(",", "", $request['biddingAmount']),
            'awarded_amount' => str_replace(",", "", $request['biddingAmount']),
            'date_submitted' => date('Y-m-d', strtotime($request['date'])),
            'created_by' => Auth::user()->id,
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
        ]);

        if (!$lastId) {
            return redirect('/add-bidding');
        }

        $encodedId = base64_encode($lastId);

        // Insert existing contractor documents into bid documents table
        $existingDocs = DB::table('tblcontractor_document')
            ->where('contractorID', $request['contractor'])
            ->get();

        if ($existingDocs) {
            foreach ($existingDocs as $doc) {
                DB::table('tblcontractor_contract_bid_document')->insert([
                    'biddingID' => $lastId,
                    'contractorID' => $request['contractor'],
                    'contractID' => $request['contract'],
                    'bidDocID' => $doc->bidRequiredDocsId,
                    'bidDocument' => $doc->file_name,
                    'updated_at' => date('Y-m-d')
                ]);
            }
        }

        // Upload new files if any
        if (!empty($files)) {
            $docDescIds = $request->input('docDescId');
            foreach ($files as $key => $file) {
                if (isset($docDescIds[$key])) {
                    $docDescId = $docDescIds[$key];

                    // Generate custom file name
                    $customName = FileUploadHelper::refNo() . '.' . $file->getClientOriginalExtension();

                    // Upload the file
                    $fileUrl = FileUploadHelper::upload($file, 'contractors', $customName);

                    // Insert uploaded file into bid documents table
                    DB::table('tblcontractor_contract_bid_document')->insert([
                        'biddingID' => $lastId,
                        'contractorID' => $request['contractor'],
                        'contractID' => $request['contract'],
                        'bidDocID' => $docDescId,
                        'bidDocument' => $fileUrl,
                        'updated_at' => date('Y-m-d')
                    ]);
                }
            }
        }

        return redirect('edit/bid/' . $encodedId)->with('msg', 'Successfully added bid.');
    }


    public function saveBidding(Request $request)
    {
        // Preserve form inputs in session
        Session::flash('contractorSess', $request['contractor']);
        Session::flash('contractSess', $request['contract']);
        Session::flash('contractRemarkSess', $request['contractorRemark']);
        Session::flash('amountSess', str_replace(",", "", $request['biddingAmount']));
        Session::flash('dateSess', $request['date']);

        $request->merge([
            'biddingAmount' => str_replace(',', '', $request->biddingAmount),
        ]);

        // Validate request
        $this->validate($request, [
            'contract' => 'required',
            'contractor' => 'required',
            'contractorRemark' => 'required',
            'biddingAmount' => 'required',
            'date' => 'required',
        ]);

        // Check if bidding is closed for the selected contract
        $isClosed = DB::table('tblcontract_details')
            ->where('contract_detailsID', $request['contract'])
            ->where('closed_bidding', 1)
            ->count();

        if ($isClosed == 1) {
            return back()->with('err', 'Bidding for this contract is closed, You cannot enter new bid');
        }

        // Check if contractor has already submitted a bid
        $existingBid = DB::table('tblcontract_bidding')
            ->where('contractID', $request['contract'])
            ->where('contractorID', $request['contractor'])
            ->count();

        if ($existingBid > 0) {
            $contractorName = $this->getContractorName($request['contractor']);
            return back()->with('err', "Bid already entered for contractor: {$contractorName}. Each contractor can only submit one bid per contract.");
        }

        // Validate uploaded files
        $files = $request->file('document');
        $allowedExtensions = ["jpg", "gif", "png", "pdf", "doc", "docx"];

        if (!empty($files)) {
            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension();
                if (!in_array($extension, $allowedExtensions)) {
                    return back()->with('err', 'File not allowed! Choose either an image, PDF, or Word document.');
                }

                if ($file->getSize() > 1000000000) { // 1GB
                    return back()->with('err', 'File too large.');
                }
            }
        }

        // Insert bid record
        $lastId = DB::table('tblcontract_bidding')->insertGetId([
            'contractID' => $request['contract'],
            'contractorID' => $request['contractor'],
            'contractor_remark' => $request['contractorRemark'],
            'bidding_amount' => str_replace(",", "", $request['biddingAmount']),
            'awarded_amount' => str_replace(",", "", $request['biddingAmount']),
            'date_submitted' => date('Y-m-d', strtotime($request['date'])),
            'created_by' => Auth::user()->id,
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
        ]);

        if (!$lastId) {
            return redirect('/add-bidding')->with('err', 'Failed to save bid. Please try again.');
        }

        // Verify the record was inserted
        $insertedRecord = DB::table('tblcontract_bidding')
            ->where('contract_biddingID', $lastId)
            ->first();

        if (!$insertedRecord) {
            \Log::error('Bid record not found after insert. ID: ' . $lastId);
            return redirect('/add-bidding')->with('err', 'Bid saved but record not found. Please contact admin.');
        }

        $encodedId = base64_encode($lastId);
        
        // Log for debugging
        \Log::info('Bid saved successfully. Original ID: ' . $lastId . ', Encoded: ' . $encodedId);

        // Insert existing contractor documents into bid documents table
        $existingDocs = DB::table('tblcontractor_document')
            ->where('contractorID', $request['contractor'])
            ->get();

        if ($existingDocs && $existingDocs->count() > 0) {
            foreach ($existingDocs as $doc) {
                DB::table('tblcontractor_contract_bid_document')->insert([
                    'biddingID' => $lastId,
                    'contractorID' => $request['contractor'],
                    'contractID' => $request['contract'],
                    'bidDocID' => $doc->bidRequiredDocsId,
                    'bidDocument' => $doc->file_name,
                    'updated_at' => date('Y-m-d')
                ]);
            }
        }

        // Upload new files if any
        if (!empty($files)) {
            $docDescIds = $request->input('docDescId');
            foreach ($files as $key => $file) {
                if (isset($docDescIds[$key])) {
                    $docDescId = $docDescIds[$key];

                    // Generate custom file name
                    $customName = FileUploadHelper::refNo() . '.' . $file->getClientOriginalExtension();

                    // Upload the file
                    $fileUrl = FileUploadHelper::upload($file, 'contractors', $customName);

                    // Insert uploaded file into bid documents table
                    DB::table('tblcontractor_contract_bid_document')->insert([
                        'biddingID' => $lastId,
                        'contractorID' => $request['contractor'],
                        'contractID' => $request['contract'],
                        'bidDocID' => $docDescId,
                        'bidDocument' => $fileUrl,
                        'updated_at' => date('Y-m-d')
                    ]);
                }
            }
        }

        // Clear session after successful save
        Session::forget('contractorSess');
        Session::forget('contractSess');
        Session::forget('contractRemarkSess');
        Session::forget('amountSess');
        Session::forget('dateSess');

        return redirect('edit/bid/' . $encodedId)->with('msg', 'Successfully added bid.');
    }

    private function getContractorName($contractorId)
    {
        // Check in tblcontractor_registration first
        $contractor = DB::table('tblcontractor_registration')
            ->where('contractor_registrationID', $contractorId)
            ->first();
        
        if ($contractor) {
            return $contractor->company_name;
        }
        
        // If not found, check in tblcontractor
        $contractor = DB::table('tblcontractor')
            ->where('id', $contractorId)
            ->first();
        
        if ($contractor) {
            return $contractor->contractor;
        }
        
        return 'Unknown Contractor';
    }

    public function viewBidding(Request $request)
    {
        $data['contract'] = DB::table('tblcontract_details')->where('status', '=', 1)->get();
        $data['contractor'] = DB::table('tblcontractor_registration')->where('status', '=', 1)->get();
        $data['status'] = DB::table('protblstatus')->where('status', '=', 1)->get();
        if ($request->isMethod('post')) {



            if ($request['contract'] != '' && $request['contractor'] == '' && $request['status'] == '') {
                Session::flash('contractSession', $request['contract']);
                $data['display'] = DB::table('tblcontract_bidding')
                    ->join('tblcontract_details', 'tblcontract_details.contract_detailsID', '=', 'tblcontract_bidding.contractID')
                    ->join('tblcontractor_registration', 'tblcontractor_registration.contractor_registrationID', '=', 'tblcontract_bidding.contractorID')
                    ->where('contractID', '=', $request['contract'])
                    //->groupBy('tblcontract_bidding.contract_biddingID')
                    ->orderBy('tblcontract_bidding.contract_biddingID', 'DESC')
                    ->get();
            }
            if ($request['contract'] != '' && $request['contractor'] != '' && $request['status'] == '') {
                Session::flash('contractorSession', $request['contractor']);
                Session::flash('contractSession', $request['contract']);
                $data['display'] = DB::table('tblcontract_bidding')
                    ->join('tblcontract_details', 'tblcontract_details.contract_detailsID', '=', 'tblcontract_bidding.contractID')
                    //->join('tblcontractor_bidding_document','tblcontractor_bidding_document.biddingID','=','tblcontract_bidding.contract_biddingID')
                    ->join('tblcontractor_registration', 'tblcontractor_registration.contractor_registrationID', '=', 'tblcontract_bidding.contractorID')
                    ->where('contractorID', '=', $request['contractor'])
                    ->where('contractID', '=', $request['contract'])
                    //->groupBy('tblcontract_bidding.contract_biddingID')
                    ->orderBy('tblcontract_bidding.contract_biddingID', 'DESC')
                    ->get();
            }
            if ($request['contract'] != '' && $request['contractor'] != '' && $request['status'] != '') {
                Session::flash('contractorSession', $request['contractor']);
                Session::flash('contractorSession', $request['contract']);
                Session::flash('statusSession', $request['status']);
                $data['display'] = DB::table('tblcontract_bidding')
                    ->join('tblcontract_details', 'tblcontract_details.contract_detailsID', '=', 'tblcontract_bidding.contractID')
                    //->join('tblcontractor_bidding_document','tblcontractor_bidding_document.biddingID','=','tblcontract_bidding.contract_biddingID')
                    ->join('tblcontractor_registration', 'tblcontractor_registration.contractor_registrationID', '=', 'tblcontract_bidding.contractorID')
                    ->where('contractorID', '=', $request['contractor'])
                    ->where('contractID', '=', $request['contract'])
                    ->where('tblcontract_bidding.status', '=', $request['status'])
                    //->groupBy('tblcontract_bidding.contract_biddingID')
                    ->orderBy('tblcontract_bidding.contract_biddingID', 'DESC')
                    ->get();
            }
            if ($request['contract'] == '' && $request['contractor'] != '' && $request['status'] == '') {
                Session::flash('contractorSession', $request['contractor']);
                $data['display'] = DB::table('tblcontract_bidding')
                    ->join('tblcontract_details', 'tblcontract_details.contract_detailsID', '=', 'tblcontract_bidding.contractID')
                    //->join('tblcontractor_bidding_document','tblcontractor_bidding_document.biddingID','=','tblcontract_bidding.contract_biddingID')
                    ->join('tblcontractor_registration', 'tblcontractor_registration.contractor_registrationID', '=', 'tblcontract_bidding.contractorID')
                    ->where('contractorID', '=', $request['contractor'])
                    //->groupBy('tblcontract_bidding.contract_biddingID')
                    ->orderBy('tblcontract_bidding.contract_biddingID', 'DESC')
                    ->get();
            }
            if ($request['contract'] == '' && $request['contractor'] == '' && $request['status'] != '') {
                Session::flash('statusSession', $request['status']);
                $data['display'] = DB::table('tblcontract_bidding')
                    ->join('tblcontract_details', 'tblcontract_details.contract_detailsID', '=', 'tblcontract_bidding.contractID')
                    //->join('tblcontractor_bidding_document','tblcontractor_bidding_document.biddingID','=','tblcontract_bidding.contract_biddingID')
                    ->join('tblcontractor_registration', 'tblcontractor_registration.contractor_registrationID', '=', 'tblcontract_bidding.contractorID')
                    ->where('tblcontract_bidding.status', '=', $request['status'])
                    //->groupBy('tblcontract_bidding.contract_biddingID')
                    ->orderBy('tblcontract_bidding.contract_biddingID', 'DESC')
                    ->get();
            }
            if ($request['contract'] == '' && $request['contractor'] != '' && $request['status'] != '') {
                Session::flash('contractorSession', $request['contractor']);
                Session::flash('statusSession', $request['status']);
                $data['display'] = DB::table('tblcontract_bidding')
                    ->join('tblcontract_details', 'tblcontract_details.contract_detailsID', '=', 'tblcontract_bidding.contractID')
                    //->join('tblcontractor_bidding_document','tblcontractor_bidding_document.biddingID','=','tblcontract_bidding.contract_biddingID')
                    ->join('tblcontractor_registration', 'tblcontractor_registration.contractor_registrationID', '=', 'tblcontract_bidding.contractorID')
                    ->where('contractorID', '=', $request['contractor'])
                    ->where('tblcontract_bidding.status', '=', $request['status'])
                    //->groupBy('tblcontract_bidding.contract_biddingID')
                    ->orderBy('tblcontract_bidding.contract_biddingID', 'DESC')
                    ->get();
            }

            if ($request['contract'] == '' && $request['contractor'] == '' && $request['status'] == '') {

                $data['display'] = DB::table('tblcontract_bidding')
                    ->join('tblcontract_details', 'tblcontract_details.contract_detailsID', '=', 'tblcontract_bidding.contractID')
                    //->join('tblcontractor_bidding_document','tblcontractor_bidding_document.biddingID','=','tblcontract_bidding.contract_biddingID')
                    ->join('tblcontractor_registration', 'tblcontractor_registration.contractor_registrationID', '=', 'tblcontract_bidding.contractorID')
                    ->orderBy('tblcontract_bidding.contract_biddingID', 'DESC')
                    ->get();
            }
            return view('procurement.ContractBidding.viewBiddings', $data);
        }

        $data['display'] = DB::table('tblcontract_bidding')
            ->leftjoin('tblcontractor_bidding_document', 'tblcontractor_bidding_document.biddingID', '=', 'tblcontract_bidding.contract_biddingID')
            ->join('tblcontract_details', 'tblcontract_details.contract_detailsID', '=', 'tblcontract_bidding.contractID')
            ->join('tblcontractor_registration', 'tblcontractor_registration.contractor_registrationID', '=', 'tblcontract_bidding.contractorID')
            ->groupBy('tblcontract_bidding.contract_biddingID')
            ->orderBy('tblcontract_bidding.contract_biddingID', 'DESC')
            ->get();
        return view('procurement.ContractBidding.viewBiddings', $data);
    }

    public function viewBiddingDocuments($biddingID)
    {
        $data['viewDocuments'] = DB::table('tblcontractor_bidding_document')->where('biddingID', '=', $biddingID)->get();
        return view('procurement.ContractBidding.viewBiddingDocument', $data);
    }

    public function fetchBid(Request $request)
    {
        $display = DB::table('tblcontract_bidding')
            ->join('tblcontractor_registration', 'tblcontractor_registration.contractor_registrationID', '=', 'tblcontract_bidding.contractorID')
            ->join('tblcontract_details', 'tblcontract_details.contract_detailsID', '=', 'tblcontract_bidding.contractID')

            ->where('contract_biddingID', '=', $request['bidID'])
            ->first();
        return response()->json($display);
    }

    public function editBid1($id = null)
    {

        if ($id == null) {
            return back()->with('err', 'Not found');
        }
        $d = base64_decode($id);
        $data['biddingID'] = $d;
        $data['edit'] = DB::table('tblcontract_bidding')
            ->join('tblcontractor_registration', 'tblcontractor_registration.contractor_registrationID', '=', 'tblcontract_bidding.contractorID')
            ->join('tblcontract_details', 'tblcontract_details.contract_detailsID', '=', 'tblcontract_bidding.contractID')
            ->where('contract_biddingID', '=', $d)
            ->select('*', 'tblcontract_bidding.status as bidStatus')
            ->first();
        $edit = $data['edit'];
        //   $data['viewDocuments'] = DB::table('tblcontractor_bidding_document')->where('biddingID','=',$d)->get();
        // $data['viewDocumentsTechnical'] = DB::table('tblbid_required_docs')->where('doc_type', '=', 'Technical')
        //     ->leftJoin('tblcontractor_contract_bid_document', function ($join) use ($d, $edit) {
        //         $join->on('tblcontractor_contract_bid_document.bidDocID', '=', 'tblbid_required_docs.id')
        //             ->where('tblcontractor_contract_bid_document.biddingID', '=', $d)
        //             ->where('tblcontractor_contract_bid_document.contractorID', '=', $edit->contractorID)
        //             ->where('tblcontractor_contract_bid_document.contractID', '=', $edit->contractID);
        //     })
        //     ->select(
        //         'tblbid_required_docs.*',
        //         'tblcontractor_contract_bid_document.id as contractor_bidding_documentID',
        //         'tblcontractor_contract_bid_document.biddingID',
        //         'tblcontractor_contract_bid_document.contractorID',
        //         'tblcontractor_contract_bid_document.contractID',
        //         'tblcontractor_contract_bid_document.bidDocID',
        //         'tblcontractor_contract_bid_document.bidDocument',
        //         'tblcontractor_contract_bid_document.updated_at'
        //     )
        //     ->orderBy('tblbid_required_docs.id')
        //     ->get();


        $data['viewDocumentsTechnical'] = DB::table('contract_required_documents as crd')
            ->join('tblbid_required_docs as rd', 'crd.tblbid_required_doc_id', '=', 'rd.id')
            ->leftJoin('tblcontractor_contract_bid_document as cbd', function ($join) use ($d, $edit) {
                $join->on('cbd.bidDocID', '=', 'rd.id')
                    ->where('cbd.biddingID', '=', $d)
                    ->where('cbd.contractorID', '=', $edit->contractorID)
                    ->where('cbd.contractID', '=', $edit->contractID);
            })
            ->where('crd.tblcontract_detail_id', '=', $edit->contractID)   // ✅ only docs selected for this contract
            ->where('rd.doc_type', '=', 'Technical')                      // ✅ technical only
            ->select(
                'rd.id as docId',
                'rd.bid_doc_description',
                'rd.doc_type',

                'cbd.id as contractor_bidding_documentID',
                'cbd.biddingID',
                'cbd.contractorID',
                'cbd.contractID',
                'cbd.bidDocID',
                'cbd.bidDocument',
                'cbd.updated_at'
            )
            ->orderBy('rd.id')
            ->get();

        // $data['viewDocumentsFinancial'] = DB::table('tblbid_required_docs')->where('doc_type', '=', 'Financial')
        //     ->leftJoin('tblcontractor_contract_bid_document', function ($join) use ($d, $edit) {
        //         $join->on('tblcontractor_contract_bid_document.bidDocID', '=', 'tblbid_required_docs.id')
        //             ->where('tblcontractor_contract_bid_document.biddingID', '=', $d)
        //             ->where('tblcontractor_contract_bid_document.contractorID', '=', $edit->contractorID)
        //             ->where('tblcontractor_contract_bid_document.contractID', '=', $edit->contractID);
        //     })
        //     ->select(
        //         'tblbid_required_docs.*',
        //         'tblcontractor_contract_bid_document.id as contractor_bidding_documentID',
        //         'tblcontractor_contract_bid_document.biddingID',
        //         'tblcontractor_contract_bid_document.contractorID',
        //         'tblcontractor_contract_bid_document.contractID',
        //         'tblcontractor_contract_bid_document.bidDocID',
        //         'tblcontractor_contract_bid_document.bidDocument',
        //         'tblcontractor_contract_bid_document.updated_at'
        //     )
        //     ->orderBy('tblbid_required_docs.id')
        //     ->get();

        $data['viewDocumentsFinancial'] = DB::table('contract_required_documents as crd')
            ->join('tblbid_required_docs as rd', 'crd.tblbid_required_doc_id', '=', 'rd.id')
            ->leftJoin('tblcontractor_contract_bid_document as cbd', function ($join) use ($d, $edit) {
                $join->on('cbd.bidDocID', '=', 'rd.id')
                    ->where('cbd.biddingID', '=', $d)
                    ->where('cbd.contractorID', '=', $edit->contractorID)
                    ->where('cbd.contractID', '=', $edit->contractID);
            })
            ->where('crd.tblcontract_detail_id', '=', $edit->contractID)  // ✅ only docs selected for this contract
            ->where('rd.doc_type', '=', 'Financial')                      // ✅ financial only
            ->select(
                'rd.id as docId',
                'rd.bid_doc_description',
                'rd.doc_type',

                'cbd.id as contractor_bidding_documentID',
                'cbd.biddingID',
                'cbd.contractorID',
                'cbd.contractID',
                'cbd.bidDocID',
                'cbd.bidDocument',
                'cbd.updated_at'
            )
            ->orderBy('rd.id')
            ->get();

        //dd($data['viewDocumentsFinancial']);
        $data['contractor'] = DB::table('tblcontractor_registration')->where('status', '=', 1)->get();
        $data['contract'] = DB::table('tblcontract_details')->where('status', '=', 1)->get();
        return view('procurement.ContractBidding.editBid', $data);
    }

    public function editBid($id = null)
    {
        if ($id == null) {
            return back()->with('err', 'No bid ID provided');
        }
        
        try {
            $d = base64_decode($id);
            
            if (!$d) {
                return back()->with('err', 'Invalid bid ID format');
            }
            
            $data['biddingID'] = $d;
            
            // First, get the bid record without joins
            $bidRecord = DB::table('tblcontract_bidding')
                ->where('contract_biddingID', '=', $d)
                ->first();
            
            if (!$bidRecord) {
                return back()->with('err', 'Bid record not found. ID: ' . $d);
            }
            
            // Get contractor name from appropriate table
            $contractorName = $this->getContractorName($bidRecord->contractorID);
            
            // Get contract details
            $contractDetails = DB::table('tblcontract_details')
                ->where('contract_detailsID', '=', $bidRecord->contractID)
                ->first();
            
            if (!$contractDetails) {
                return back()->with('err', 'Contract details not found for ID: ' . $bidRecord->contractID);
            }
            
            // Create a combined object for the view
            $data['edit'] = new \stdClass();
            $data['edit']->contract_biddingID = $bidRecord->contract_biddingID;
            $data['edit']->contractID = $bidRecord->contractID;
            $data['edit']->contractorID = $bidRecord->contractorID;
            $data['edit']->contractor_remark = $bidRecord->contractor_remark;
            $data['edit']->bidding_amount = $bidRecord->bidding_amount;
            $data['edit']->date_submitted = $bidRecord->date_submitted;
            $data['edit']->bidStatus = $bidRecord->status;
            
            // Add contractor name
            $data['edit']->company_name = $contractorName;
            
            // Add contract details
            $data['edit']->contract_detailsID = $contractDetails->contract_detailsID;
            $data['edit']->contract_name = $contractDetails->contract_name;
            
            // Get all contractors from both tables for the dropdown
            $contractorReg = DB::table('tblcontractor_registration')
                ->where('status', '=', 1)
                ->select(
                    'contractor_registrationID as id', 
                    'company_name as name', 
                    DB::raw("'Registration' as source")
                )
                ->get();
            
            $contractor = DB::table('tblcontractor')
                ->where('status', '=', 1)
                ->select(
                    'id', 
                    'contractor as name', 
                    DB::raw("'Main' as source")
                )
                ->get();
            
            $data['allContractors'] = $contractorReg->merge($contractor)->sortBy('name');
            $data['contract'] = DB::table('tblcontract_details')->where('status', '=', 1)->get();
            
            // Get document data
            $data['viewDocumentsTechnical'] = DB::table('contract_required_documents as crd')
                ->join('tblbid_required_docs as rd', 'crd.tblbid_required_doc_id', '=', 'rd.id')
                ->leftJoin('tblcontractor_contract_bid_document as cbd', function ($join) use ($d, $bidRecord) {
                    $join->on('cbd.bidDocID', '=', 'rd.id')
                        ->where('cbd.biddingID', '=', $d)
                        ->where('cbd.contractorID', '=', $bidRecord->contractorID)
                        ->where('cbd.contractID', '=', $bidRecord->contractID);
                })
                ->where('crd.tblcontract_detail_id', '=', $bidRecord->contractID)
                ->where('rd.doc_type', '=', 'Technical')
                ->select(
                    'rd.id as docId',
                    'rd.bid_doc_description',
                    'rd.doc_type',
                    'cbd.id as contractor_bidding_documentID',
                    'cbd.biddingID',
                    'cbd.contractorID',
                    'cbd.contractID',
                    'cbd.bidDocID',
                    'cbd.bidDocument',
                    'cbd.updated_at'
                )
                ->orderBy('rd.id')
                ->get();

            $data['viewDocumentsFinancial'] = DB::table('contract_required_documents as crd')
                ->join('tblbid_required_docs as rd', 'crd.tblbid_required_doc_id', '=', 'rd.id')
                ->leftJoin('tblcontractor_contract_bid_document as cbd', function ($join) use ($d, $bidRecord) {
                    $join->on('cbd.bidDocID', '=', 'rd.id')
                        ->where('cbd.biddingID', '=', $d)
                        ->where('cbd.contractorID', '=', $bidRecord->contractorID)
                        ->where('cbd.contractID', '=', $bidRecord->contractID);
                })
                ->where('crd.tblcontract_detail_id', '=', $bidRecord->contractID)
                ->where('rd.doc_type', '=', 'Financial')
                ->select(
                    'rd.id as docId',
                    'rd.bid_doc_description',
                    'rd.doc_type',
                    'cbd.id as contractor_bidding_documentID',
                    'cbd.biddingID',
                    'cbd.contractorID',
                    'cbd.contractID',
                    'cbd.bidDocID',
                    'cbd.bidDocument',
                    'cbd.updated_at'
                )
                ->orderBy('rd.id')
                ->get();
            
            // Set session values for the form
            Session::flash('contractorSess', $bidRecord->contractorID);
            Session::flash('contractSess', $bidRecord->contractID);
            Session::flash('contractRemarkSess', $bidRecord->contractor_remark);
            Session::flash('amountSess', $bidRecord->bidding_amount);
            Session::flash('dateSess', $bidRecord->date_submitted);
            
            return view('procurement.ContractBidding.editBid', $data);
            
        } catch (\Exception $e) {
            \Log::error('Error in editBid: ' . $e->getMessage());
            return back()->with('err', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function updateBid1(Request $request)
    {
        $this->validate($request, [
            'contract' => 'required|numeric',
            'contractor' => 'required|numeric',
            'contractorRemark' => 'required',
            'biddingAmount' => 'required',
            'date' => 'required',
        ]);

        $lastid = DB::table('tblcontract_bidding')->where('contract_biddingID', '=', $request['id'])->update([
            'contractID' => $request['contract'],
            'contractorID' => $request['contractor'],
            'contractor_remark' => $request['contractorRemark'],
            'bidding_amount' => str_replace(",", "", $request['biddingAmount']),
            'awarded_amount' => str_replace(",", "", $request['biddingAmount']),
            'date_submitted' => $request['date'],
            'created_by' => Auth::user()->id,
            'updated_at' => date('Y-m-d'),
        ]);

        return redirect('/view-bidding')->with('msg', 'Successfully Updated');
    }

    public function updateBid(Request $request)
    {
        // Validate request
        $this->validate($request, [
            'bidID' => 'required',
            'contractorRemark' => 'required',
            'biddingAmount' => 'required',
            'date' => 'required',
            'status' => 'required'
        ]);

        // Remove commas from bidding amount (if any)
        $biddingAmount = str_replace(',', '', $request->biddingAmount);
        
        // Also remove any other non-numeric characters except decimal point
        $biddingAmount = preg_replace('/[^0-9.]/', '', $biddingAmount);

        // Update the bid record
        $updated = DB::table('tblcontract_bidding')
            ->where('contract_biddingID', $request->bidID)
            ->update([
                'contractor_remark' => $request->contractorRemark,
                'bidding_amount' => $biddingAmount,
                'awarded_amount' => $biddingAmount, // Update awarded amount too if needed
                'date_submitted' => date('Y-m-d', strtotime($request->date)),
                'status' => $request->status,
                'updated_at' => date('Y-m-d')
            ]);

        if ($updated) {
            $encodedId = base64_encode($request->bidID);
            return redirect('edit/bid/' . $encodedId)->with('msg', 'Bid updated successfully.');
        } else {
            return back()->with('err', 'Failed to update bid. No changes made or record not found.');
        }
    }

    public function bidUpdate(Request $request)
    {
        if ($request['modalUpdate'] === 'yes') {
            $file = $request->file('file');
            $location = public_path('BiddingDocument');
            foreach ($file as $key => $val) {
                // Generate a custom file name
                $customName = FileUploadHelper::refNo() . '.' . $val->getClientOriginalExtension();

                // Upload the file — returns full URL as string
                $fileUrl = FileUploadHelper::upload($val, 'BiddingDocument', $customName);

                DB::table('tblcontractor_contract_bid_document')
                    ->where('id', $request['contractorBidDocID'])
                    ->update([
                        'bidDocument' => $fileUrl,
                        'updated_at' => date('Y-m-d')
                    ]);

                return back()->with('msg', 'Successfully Updated');
            }
        }

        if ($request['modalUpload'] === 'yes') {
            $file = $request->file('fileUpload');
            $location = public_path('BiddingDocument');
            foreach ($file as $key => $val) {

                // Generate a custom file name
                $customName = FileUploadHelper::refNo() . '.' . $val->getClientOriginalExtension();

                // Upload the file — returns full URL as string
                $fileUrl = FileUploadHelper::upload($val, 'BiddingDocument', $customName);

                DB::table('tblcontractor_contract_bid_document')
                    ->insert([
                        'biddingID' => $request['biddingID'],
                        'contractorID' => $request['contractorID'],
                        'contractID' => $request['contractID'],
                        'bidDocID' => $request['docDescId'],
                        'bidDocument' => $fileUrl,
                        'updated_at' => date('Y-m-d')
                    ]);

                return back()->with('msg', 'Successfully Updated');
            }
        }

        if ($request['modalDelete'] === 'yes') {
            $remove = DB::table('tblcontractor_contract_bid_document')
                ->where('id', $request['docId'])
                ->delete();
            if ($remove) {
                return back()->with('msg', 'Successfully Updated');
            } else {
                return back()->with('err', 'Document could not be removed');
            }
        }

        $this->validate($request, [
            // 'contract' => 'required|numeric',
            // 'contractor' => 'required|numeric',
            'contractorRemark' => 'required',
            'biddingAmount' => 'required',
            'date' => 'required',
        ]);

        $file = $request->file('document');
        if ($file != '') {
            foreach ($file as $key => $val) {
                if ($request['description'][$key] == '') {
                    return back()->with('err', 'Please, TYpe in the document Description');
                }
            }
        }

        $lastid = DB::table('tblcontract_bidding')->where('contract_biddingID', '=', $request['bidID'])->update([
            // 'contractID' => $request['contract'],
            // 'contractorID' => $request['contractor'],
            'contractor_remark' => $request['contractorRemark'],
            'bidding_amount' => str_replace(",", "", $request['biddingAmount']),
            'date_submitted' => $request['date'],
            'status' => $request['status'],
            'created_by' => Auth::user()->id,
            'updated_at' => date('Y-m-d'),
        ]);


        if ($file != '') {
            $location = public_path('BiddingDocument');
            foreach ($file as $key => $val) {
                $ext = array("jpg", "gif", "png", "pdf", "doc", "docx");
                $extension = $val->getClientOriginalExtension();
                if (!in_array($extension, $ext)) {
                    return back()->with('err', 'File not Allowed !. choose either an Image, pdf or Word document to upload');
                }

                $d = $val->getSize();
                if ($d > 1000000000) {
                    return back()->with('err', 'File too large');
                }
                //dd($val);
                // $filename = $val->getClientOriginalName();

                // Generate a custom file name
                $customName = FileUploadHelper::refNo() . '.' . $val->getClientOriginalExtension();

                // Upload the file — returns full URL as string
                $fileUrl = FileUploadHelper::upload($val, 'BiddingDocument', $customName);
                //dd($request['description'][$key]);
                DB::table('tblcontractor_bidding_document')->insert([
                    'biddingID' => $request['bidID'],
                    'file_name' => $fileUrl,
                    'file_description' => $request['description'][$key],
                    'created_by' => Auth::user()->id,
                    'created_at' => date('Y-m-d'),
                    'updated_at' => date('Y-m-d'),
                ]);
                // $move = $val->move($location, $filename);
            }
        }

        return back()->with('msg', 'Successfully Updated');
    }

    public function deleteBiddingDoc($id)
    {
        $db = DB::table('tblcontractor_bidding_document')->where('contractor_bidding_documentID', '=', $id)->delete();
        return back()->with('msg', 'deleted');
    }

    public function comments($contractID)
    {
        $d = base64_decode($contractID);
        $data['contractID'] = $d;
        // $data['contract'] = DB::table('tblcontract_bidding')
        //  ->join('tblcontractor_registration','tblcontractor_registration.contractor_registrationID','=','tblcontract_bidding.contractorID')
        //  ->join('tblcontract_details','tblcontract_details.contract_detailsID','=','tblcontract_bidding.contractID')
        //  ->where('contract_detailsID','=',$d)
        //  ->select('*','tblcontract_bidding.status as bidStatus')
        //  ->first();
        $data['contract'] = DB::table('tblcontract_details')
            ->where('contract_detailsID', '=', $d)
            ->first();
        //  $data['comments'] = DB::table('tblcontract_comment')
        //  ->join('users','users.id','=', 'tblcontract_comment.created_by')
        //  ->where('tblcontract_comment.contractID','=',$d)->get();

        $data['comments'] = DB::table('tblcontract_comment')
            ->join('users', 'users.id', '=', 'tblcontract_comment.created_by')
            ->where('tblcontract_comment.contractID', $d)
            ->select(
                'tblcontract_comment.comment_description',
                'tblcontract_comment.created_at as comment_created_at',
                'users.name'
            )
            ->orderBy('tblcontract_comment.created_at', 'DESC')
            ->get();


        //  dd($data);
        return view('procurement.ContractBidding.comments', $data);
    }
}//end class
