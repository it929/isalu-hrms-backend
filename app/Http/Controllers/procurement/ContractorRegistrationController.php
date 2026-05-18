<?php

namespace App\Http\Controllers\procurement;



use App\Helpers\FileUploadHelper;
use Illuminate\Http\Request;
use App\Library\AnyFileUploadClass;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use File;
use Illuminate\Support\Facades\Log;

class ContractorRegistrationController extends Controller
{
    private $getUploadPath;
    private $getDownloadPath;
    private $uploadCompletePathName;

    public function __construct()
    {
        $this->middleware('auth');
        $this->getUploadPath = env('UPLOADPATH', null) . 'contractorDocuments/';
        $this->getDownloadPath = env('DOWNLOADPATH', null) . 'contractorDocuments/';
    }


    //create contractor Registration
    public function createContractorRegistrationOLD()
    {   //$codesource = file_get_contents('https://www.google.fr/search?tbm=isch&q=voiture');
        //dd($codesource);
        try {
            $data['getAllContractor'] = $this->getContractorList($status = 1, $paginate = 10);
            $data['getState'] = $this->getStateList($status = 1, $pagination = 0);
            $data['bankList'] = DB::table('tblbanklist')->orderBy('bank', 'Asc')->get();
            if (Session::get('editRecord')) {
                $getData = Session::get('editRecord');
                $data['editRecord']     = $getData['setContractDetails'];
                $data['editBankList']       = $getData['setBankDetails'];
                $data['editDocumentList']   = $getData['setDocumentDetails'];
            }
            $data['filePath'] = $this->getDownloadPath;
            //(Session::get('editRecord') ? $data['editRecord'] = Session::get('editRecord') : '');
        } catch (\Throwable $e) {
            $data['getContractCategory'] = [];
        }

        //kill edit session
        Session::forget('editRecord');

        // dd($data['getAllContractor']);
        return view('procurement.Contractor.registrationForm', $data);
    }

    public function createContractorRegistration($id = null)
    {

        try {
            $data['getAllContractor'] = $this->getContractorListNew($status = 1, $paginate = 100);

            // dd($data['getAllContractor']);
            $data['getState'] = $this->getStateList($status = 1, $pagination = 0);
            $data['bankList'] = DB::table('tblbanklist')->orderBy('bank', 'Asc')->get();
            $data['contractCategories'] = DB::table('tblcontractor_category')->get();
            $data['filePath'] = $this->getDownloadPath;
            $data['requiredDocs'] = DB::table('tblbid_required_docs')
                ->where('doc_type', 'Technical')
                ->orderBy('doc_type', 'Asc')->get();

            // If editing, load contractor record
            if ($id) {
                $recordID = base64_decode($id);
                $contractor = DB::table('tblcontractor_registration')
                    ->where('contractor_registrationID', $recordID)
                    ->first();

                if ($contractor) {
                    $data['editRecord'] = $contractor;
                    $data['editBankList'] = DB::table('tblbank_details')
                        ->where('contractorID', $recordID)
                        ->get();
                    $data['editDocumentList'] = DB::table('tblcontractor_document')
                        ->where('contractorID', $recordID)
                        ->get();
                } else {
                    return redirect()->route('createContractorRegistration')
                        ->with('error', 'Record not found!');
                }
            }
        } catch (\Throwable $e) {
            $data['contractCategories'] = [];
            $data['getAllContractor'] = [];
            $data['getState'] = [];
            $data['bankList'] = [];
            // $data['requiredDocs'] = [];
            $data['requiredDocs'] = collect();
        }

        return view('procurement.Contractor.registrationForm', $data);
    }





    //Get all contactors
    public function getContractorList($status = 1, $pagination = 0)
    {
        try {
            if ($pagination > 0) {
                // return DB::table('tblcontractor_registration')
                //     ->leftJoin('protblstate', 'protblstate.stateID', '=', 'tblcontractor_registration.current_stateID')
                //     ->leftJoin('users', 'users.id', '=', 'tblcontractor_registration.created_by')
                //     ->leftJoin('protblstatus', 'protblstatus.statusID', '=', 'tblcontractor_registration.status')
                //     ->where('tblcontractor_registration.status', $status)
                //     ->orderBy('tblcontractor_registration.contractor_registrationID', 'Desc')
                //     ->paginate($pagination);
                return DB::table('tblcontractor_registration')
                    ->leftJoin('protblstate', 'protblstate.stateID', '=', 'tblcontractor_registration.current_stateID')
                    ->leftJoin('users', 'users.id', '=', 'tblcontractor_registration.created_by')
                    ->leftJoin('protblstatus', 'protblstatus.statusID', '=', 'tblcontractor_registration.status')
                    ->where('tblcontractor_registration.status', $status)
                    ->orderBy('tblcontractor_registration.contractor_registrationID', 'Desc')
                    ->select(
                        'tblcontractor_registration.*',
                        'protblstate.state_name',
                        'protblstatus.status_name',
                        'users.name as created_by_name'
                    )
                    ->paginate($pagination);
            } else {
                return DB::table('tblcontractor_registration')
                    ->leftJoin('protblstate', 'protblstate.stateID', '=', 'tblcontractor_registration.current_stateID')
                    ->leftJoin('users', 'users.id', '=', 'tblcontractor_registration.created_by')
                    ->leftJoin('protblstatus', 'protblstatus.statusID', '=', 'tblcontractor_registration.status')
                    ->where('tblcontractor_registration.status', $status)
                    ->orderBy('tblcontractor_registration.contractor_registrationID', 'Desc')
                    ->get();
            }
        } catch (\Throwable $e) {
            return [];
        }
    }



    public function getContractorListNew($status = 1, $pagination = 10, $categoryId = null)
    {
        try {
            $query = DB::table('tblcontractor')
                ->leftJoin('tblstates', 'tblstates.StateID', '=', 'tblcontractor.procurementCurrentStateID')
                ->leftJoin('tblcontractor_registration', 'tblcontractor_registration.contractor_registrationID', '=', 'tblcontractor.procurementContractorRegistrationId')
                ->leftJoin('users', 'users.id', '=', 'tblcontractor.procurementCreatedBy')
                ->leftJoin('protblstatus', 'protblstatus.statusID', '=', 'tblcontractor.procurementStatus')
                ->orderBy('tblcontractor.procurementContractorRegistrationId', 'Desc')
                ->select(
                    'tblcontractor.*',
                    'tblstates.State as state_name',
                    'protblstatus.status_name',
                    'users.name as created_by_name',
                    'tblcontractor_registration.current_stateID as current_stateID',
                    'tblcontractor_registration.city as city',
                    'tblcontractor_registration.bankId as bankId',
                    'tblcontractor_registration.accountNo as accountNo'
                );

            if (!empty($categoryId)) {
                $query->whereIn('tblcontractor.procurementContractorRegistrationId', function ($sub) use ($categoryId) {
                    $sub->select('contractor_registrationID')
                        ->from('contract_category_contractor_registration')
                        ->where('contract_category_id', $categoryId);
                });
            }

            if ($pagination > 0) {
                $result = $query->paginate($pagination)->appends(request()->query());
                $items = $result->getCollection();
            } else {
                $items = $query->get();
            }

            $registrationIds = $items->pluck('procurementContractorRegistrationId')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $categoryMap = collect();

            if (!empty($registrationIds)) {
                $categoryMap = DB::table('contract_category_contractor_registration as cccr')
                    ->leftJoin('tblcontract_category as cc', 'cc.id', '=', 'cccr.contract_category_id')
                    ->whereIn('cccr.contractor_registrationID', $registrationIds)
                    ->select(
                        'cccr.contractor_registrationID',
                        DB::raw('GROUP_CONCAT(DISTINCT cc.id ORDER BY cc.id SEPARATOR ",") as category_ids'),
                        DB::raw('GROUP_CONCAT(DISTINCT cc.category ORDER BY cc.category SEPARATOR ", ") as category_names')
                    )
                    ->groupBy('cccr.contractor_registrationID')
                    ->get()
                    ->keyBy('contractor_registrationID');
            }

            $items->transform(function ($item) use ($categoryMap) {
                $categories = $categoryMap->get($item->procurementContractorRegistrationId);

                $item->category_ids = $categories->category_ids ?? '';
                $item->category_names = $categories->category_names ?? '';

                return $item;
            });

            if ($pagination > 0) {
                $result->setCollection($items);
                return $result;
            }

            return $items;
        } catch (\Throwable $e) {
            return [];
        }
    }


    //Get all status
    public function getStateList($status = 1, $pagination = 0)
    {
        try {
            if ($pagination > 0) {
                return DB::table('protblstate')->where('status', $status)->paginate($pagination);
            } else {
                return DB::table('protblstate')->where('status', $status)->get();
            }
        } catch (\Throwable $e) {
            return [];
        }
    }


    //post contract Details
    public function postContractorRegistrationbackup17022026(Request $request)
    {
        $complete = 0;
        $recordID =  $request['recordID'];
        $accountNumberArray = [];
        $accountNameArray   = [];
        $bankListIDArray    = [];

        ///////////////////////////////////START VALIDATION///////////////////////////
        $this->validate($request, [
            'contractorName'       => 'required|string|max:200',
            //'currentState'         => 'required|numeric',
            'emailAddress'         => 'required|email|max:200',
            'phoneNumber'          => 'required|string',
            'contactPerson'        => 'required|string|max:200',
            'tinNumber'            => 'required|string',
            //'address'            => 'required|string',
        ]);
        //Validate Bank details
        if ($request['bankName']) {
            $this->validate($request, [
                'bankName'              => 'required',
                'accountNumber'         => 'required|max: 10',
                //'accountName'         => 'string|max: 200',
            ]);
        }
        //Validate File documents
        if ($request->hasFile('document')) {
            $this->validate($request, [
                'document.*'            => 'required|mimes:png,jpg,jpe,jpeg,pdf,doc,docx|max: 5000',
                'description.*'         => 'required|string',
            ], ['description.*'   => 'one of the description']);
        }
        ///////////////////////////////////END VALIDATION///////////////////////////

        ///////////////////////////////////START DB TRANSACTION///////////////////////////
        //check if contractor exist
        if ((DB::table('tblcontractor_registration')->where('company_name', $request['contractorName'])->first() || DB::table('tblcontractor_registration')->where('email_address', $request['emailAddress'])->first()) && ($recordID == null)) {
            return redirect()->route('createContractorRegistration')->with('error', 'Contractor already exist or Email exist!');
        }

        DB::beginTransaction();
        try {
            ///////START UPDATION///////
            if ($recordID > 0 && DB::table('tblcontractor_registration')->where('contractor_registrationID', $recordID)->first()) {
                //Update contractor details
                DB::table('tblcontractor_registration')->where('contractor_registrationID', $recordID)->update([
                    'company_name'          => $request['contractorName'],
                    'address'               => $request['address'],
                    'phone_number'          => $request['phoneNumber'],
                    'email_address'         => $request['emailAddress'],
                    'contact_person'        => $request['contactPerson'],
                    'tin_number'            => $request['tinNumber'],
                    'current_stateID'       => $request['currentState'],
                    'city'                  => $request['city'],
                    'updated_at'            => date('Y-m-d'),
                    'created_by'            => (Auth::check() ? Auth::user()->id : null),
                ]);

                //update bank details
                if ($recordID && $request['bankName']) {
                    //bank ID
                    foreach ($request['bankName'] as $item) {
                        $bankListIDArray[] = $item;
                    }
                    //account name
                    /*foreach($request['accountName'] as $item)
                    {
                        $accountNameArray[] = $item;
                    }*/
                    //account number
                    foreach ($request['accountNumber'] as $item) {
                        $accountNumberArray[] = $item;
                    }
                    foreach ($request['bankRecordID'] as $keyBank => $getBankRecordID) {
                        if (DB::table('tblbank_details')->where('bank_detailsID', $getBankRecordID)->first()) {
                            DB::table('tblbank_details')->where('bank_detailsID', $getBankRecordID)->update([
                                'contractorID'          => $recordID,
                                'bankID'                => $bankListIDArray[$keyBank],
                                'account_number'        => $accountNumberArray[$keyBank],
                                //'account_name'          => $accountNameArray[$keyBank],
                                'updated_at'            => date('Y-m-d'),
                                'created_by'            => (Auth::check() ? Auth::user()->id : null),
                            ]);
                        } else {
                            DB::table('tblbank_details')->insertGetId([
                                'contractorID'          => $recordID,
                                'bankID'                => $bankListIDArray[$keyBank],
                                'account_number'        => $accountNumberArray[$keyBank],
                                //'account_name'          => $accountNameArray[$keyBank],
                                'created_at'            => date('Y-m-d'),
                                'updated_at'            => date('Y-m-d'),
                                'created_by'            => (Auth::check() ? Auth::user()->id : null),
                            ]);
                        }
                    }
                } //end bank

                //Save document(s)
                if ($recordID && $request->hasFile('document')) {
                    $descriptionArray = array();
                    $getUploadDocumentPath = $this->getUploadPath;
                    //get all description
                    foreach ($request['description'] as $item) {
                        $descriptionArray[] = $item;
                    }

                    foreach ($request['document'] as $keyDoc => $file) {
                        //$newFileName = (rand(11111,99999) . time()) . '.'.strtolower($file->getClientOriginalExtension());
                        $getArrayResponse = $this->uploadAnyFile($file, $getUploadDocumentPath);
                        if ($getArrayResponse) //if($file->move($getUploadDocumentPath, $newFileName))
                        {
                            if ($getArrayResponse['success']) {
                                DB::table('tblcontractor_document')->insertGetId([
                                    'contractorID'          => $recordID,
                                    'file_name'             => $getArrayResponse['newFileName'], // $newFileName,
                                    'file_description'      => $descriptionArray[$keyDoc],
                                    'created_at'            => date('Y-m-d'),
                                    'updated_at'            => date('Y-m-d'),
                                    'created_by'            => (Auth::check() ? Auth::user()->id : null),
                                ]);
                            }
                        }
                    }
                }

                //successfully updated
                $complete = 1;
                Session::forget('editRecord');
            } else {
                ///////START INSERTION///////
                $complete = DB::table('tblcontractor_registration')->insertGetId([
                    'company_name'          => $request['contractorName'],
                    'address'               => $request['address'],
                    'phone_number'          => $request['phoneNumber'],
                    'email_address'         => $request['emailAddress'],
                    'contact_person'        => $request['contactPerson'],
                    'tin_number'            => $request['tinNumber'],
                    'current_stateID'       => $request['currentState'],
                    'city'                  => $request['city'],
                    'created_at'            => date('Y-m-d'),
                    'updated_at'            => date('Y-m-d'),
                    'created_by'            => (Auth::check() ? Auth::user()->id : null),
                ]);

                //Save document(s)
                if ($complete && $request->hasFile('document')) {
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
                                DB::table('tblcontractor_document')->insertGetId([
                                    'contractorID'          => $complete,
                                    'file_name'             => $getArrayResponse['newFileName'],
                                    'file_description'      => $descriptionArray[$keyDoc],
                                    'created_at'            => date('Y-m-d'),
                                    'updated_at'            => date('Y-m-d'),
                                    'created_by'            => (Auth::check() ? Auth::user()->id : null),
                                ]);
                            }
                        }
                    }
                }

                //Save bank details
                if ($complete) {
                    $accountNumberArray = [];
                    $accountNameArray   = [];

                    //account number
                    foreach ($request['accountNumber'] as $item) {
                        $accountNumberArray[] = $item;
                    }
                    foreach ($request['bankName'] as $keyBank => $getBankName) {
                        DB::table('tblbank_details')->insertGetId([
                            'contractorID'          => $complete,
                            'bankID'                => $getBankName,
                            'account_number'        => $accountNumberArray[$keyBank],
                            //'account_name'          => $accountNameArray[$keyBank],
                            'created_at'            => date('Y-m-d'),
                            'updated_at'            => date('Y-m-d'),
                            'created_by'            => (Auth::check() ? Auth::user()->id : null),
                        ]);
                    }
                }
                Session::forget('editRecord');
            } //end insert to DB
            DB::commit();
        } catch (\Throwable $e) {
            Session::forget('editRecord');
            DB::rollback();
        }
        ///////////////////////////////////END DB TRANSATION///////////////////////////

        if ($complete) {
            return redirect()->route('createContractorRegistration')->with('message', 'Your record was created/Updated successfully.');
        }

        return redirect()->route('createContractorRegistration')->with('error', 'Sorry, we cannot create/Update your record ! Please, try again.');
    }


    public function postContractorRegistration(Request $request)
    {
        $rules = [
            'contractorName' => 'required|string|max:200',
            'emailAddress'   => 'required|email|max:200',
            'phoneNumber'    => 'required|string',
            'contactPerson'  => 'required|string|max:200',
            'tinNumber'      => 'required|string',
            'accountNumber'  => 'nullable|max:10',
            'bank'           => 'nullable|exists:tblbanklist,bankID', // if you have a banks table
            'document_type.*' => 'nullable|exists:tblbid_required_docs,id',
            'document.*'      => 'nullable|mimes:png,jpg,jpe,jpeg,pdf,doc,docx|max:5000',
            'contractCategory'  => 'required|array|min:1',

        ];

        // Optionally require bank/account if bank is selected
        if ($request->filled('bank')) {
            $rules['accountNumber'] = 'required|max:10';
        }

        $this->validate($request, $rules);

        // dd($request->all());
        DB::beginTransaction();

        try {
            // Insert contractor registration
            $contractorId = DB::table('tblcontractor_registration')->insertGetId([
                'company_name'    => $request->contractorName,
                'address'         => $request->address,
                'phone_number'    => $request->phoneNumber,
                'email_address'   => $request->emailAddress,
                'contact_person'  => $request->contactPerson,
                'tin_number'      => $request->tinNumber,
                'current_stateID' => $request->currentState,
                'city'            => $request->city,
                'bankId'          => $request->bank,
                'accountNo'       => $request->accountNumber,
                'sortCode'        => $request->sortcode,
                'created_at'      => now(),
                'updated_at'      => now(),
                'created_by'      => Auth::id(),
            ]);

            // Save pivot categories
            $categoryRows = [];
            foreach ($request->contractCategory as $categoryId) {
                $categoryRows[] = [
                    'contractor_registrationID' => $contractorId,
                    'contract_category_id'      => $categoryId,
                    'created_at'                => now(),
                    'updated_at'                => now(),
                ];
            }

            if (!empty($categoryRows)) {
                DB::table('contract_category_contractor_registration')->insert($categoryRows);
            }

            DB::table('tblcontractor')->insert([
                'contractor'            => $request['contractorName'],
                'address'               => $request['address'],
                'phoneNo'               => $request['phoneNumber'],
                'emailAddress'          => $request['emailAddress'],
                'Banker'                => $request['bank'],
                'AccountNo'             => $request['accountNumber'],
                'sortCode'              => $request['sortcode'],
                'TIN'                   => $request['tinNumber'],
                'status'                => 1,

                'isFromProcurement'     => 1,
                'procurementContractorRegistrationId'     => $contractorId,
                'procurementCreatedBy'  => Auth::check() ? Auth::user()->id : null,
                'procurementStatus'     => 1,
                'procurementCurrentStateID' => $request['currentState'],
                'procurementContactPerson'  => $request['contactPerson'],
            ]);

            // Insert bank details if provided
            if ($request->has('bank') && is_array($request->bank)) {
                foreach ($request->bank as $key => $bankID) {
                    $accountNo = $request->accountNumber[$key] ?? null;
                    if ($bankID && $accountNo) {
                        DB::table('tblbank_details')->insert([
                            'contractorID'   => $contractorId,
                            'bankID'         => $bankID,
                            'account_number' => $accountNo,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                            'created_by'     => Auth::id(),
                        ]);
                    }
                }
            }

            // Log::info($request->all());
            // Handle document uploads

            if ($request->hasFile('document')) {
                $documentTypes = $request->document_type ?? [];

                foreach ($request->file('document') as $key => $file) {

                    $docTypeId = $documentTypes[$key] ?? null;

                    // Only query required doc if type exists
                    $requiredDoc = $docTypeId
                        ? DB::table('tblbid_required_docs')->where('id', $docTypeId)->first()
                        : null;

                    // Generate a custom file name
                    $customName = FileUploadHelper::refNo() . '.' . $file->getClientOriginalExtension();

                    // Upload the file — returns full URL as string
                    $fileUrl = FileUploadHelper::upload($file, 'contractors', $customName);

                    // Save document record
                    DB::table('tblcontractor_document')->insert([
                        'contractorID'      => $contractorId,
                        'bidRequiredDocsId' => $docTypeId,
                        'file_name'         => $fileUrl, // save the full URL
                        'file_description'  => $requiredDoc->bid_doc_description ?? null,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                        'created_by'        => Auth::id(),
                    ]);
                }
            }


            DB::commit();

            return redirect()
                ->route('createContractorRegistration')
                ->with('message', 'Contractor created successfully.');
        } catch (\Throwable $e) {
            DB::rollback();
            Log::error('Contractor creation failed: ' . $e->getMessage());

            return redirect()
                ->route('createContractorRegistration')
                ->with('error', 'Failed to create contractor. Please try again.');
        }
    }

    protected function updateContractorRegistration(Request $request)
    {
        // Validate input
        $request->validate([
            'contractor_id' => 'required|exists:tblcontractor,procurementContractorRegistrationId',
            'contractorName' => 'required|string|max:255',
            'tinNumber' => 'required|string|max:50',
            'phoneNumber' => 'required|string|max:20',
            'emailAddress' => 'required|email|max:255',
            'contactPerson' => 'required|string|max:255',
            'contractCategory'    => 'required|array|min:1',
            'contractCategory.*'  => 'required|exists:tblcontract_category,id',
        ]);
        DB::beginTransaction();
        try {
            $recordID = $request->contractor_id;

            // Find the contractor
            $contractor = DB::table('tblcontractor_registration')
                ->where('contractor_registrationID', $recordID)
                ->first();

            if (!$contractor) {
                return redirect()->back()->with('error', 'Contractor not found.');
            }


            // Update main contractor registration
            DB::table('tblcontractor_registration')
                ->where('contractor_registrationID', $recordID)
                ->update([
                    'company_name'    => $request['contractorName'],
                    'address'         => $request['address'],
                    'phone_number'    => $request['phoneNumber'],
                    'email_address'   => $request['emailAddress'],
                    'contact_person'  => $request['contactPerson'],
                    'tin_number'      => $request['tinNumber'],
                    'current_stateID' => $request['currentState'],
                    'city'            => $request['city'],
                    'updated_at'      => now(),
                    'created_by'      => Auth::id(),
                    'bankId'          => $request['bank'] ?? null,
                    'accountNo'       => $request['account'] ?? null,
                    'sortCode'        => $request['sortcode'] ?? null,
                ]);

            // Update tblcontractor
            DB::table('tblcontractor')
                ->where('procurementContractorRegistrationId', $recordID)
                ->update([
                    'contractor'                        => $request['contractorName'],
                    'address'                           => $request['address'],
                    'phoneNo'                           => $request['phoneNumber'],
                    'emailAddress'                      => $request['emailAddress'],
                    'Banker'                            => $request['bank'] ?? null,
                    'AccountNo'                         => $request['account'] ?? null,
                    'sortCode'                          => $request['sortcode'] ?? null,
                    'TIN'                               => $request['tinNumber'],
                    'status'                            => 1,
                    'isFromProcurement'                 => 1,
                    'procurementContractorRegistrationId' => $recordID,
                    'procurementCreatedBy'              => Auth::id(),
                    'procurementCurrentStateID'         => $request['currentState'],
                    'procurementContactPerson'          => $request['contactPerson'],
                ]);


            // update contractor categories pivot
            DB::table('contract_category_contractor_registration')
                ->where('contractor_registrationID', $recordID)
                ->delete();

            $categoryRows = [];
            foreach (array_unique($request->contractCategory ?? []) as $categoryId) {
                $categoryRows[] = [
                    'contractor_registrationID' => $recordID,
                    'contract_category_id'      => $categoryId,
                    'created_at'                => now(),
                    'updated_at'                => now(),
                ];
            }

            if (!empty($categoryRows)) {
                DB::table('contract_category_contractor_registration')->insert($categoryRows);
            }

            DB::commit();
            return redirect()
                ->back()
                ->with('message', 'Contractor updated successfully.');
        } catch (\Throwable $e) {
            DB::rollback();
            Log::error('Contractor update failed: ' . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Failed to update contractor. Please try again.');
        }
    }





    //Delete contract
    public function removeContractorRecordbackup17022026($recordID = null)
    {
        $recordID = base64_decode($recordID);
        try {
            $getUploadDocumentPath = $this->getUploadPath;
            if ($recordID != null || DB::table('tblcontractor_registration')->where('contractor_registrationID', $recordID)->first()) {
                //Delete all instances of bank
                DB::table('tblbank_details')->where('contractorID', $recordID)->delete();
                //unlink doc. 1st
                $getAllFiles = DB::table('tblcontractor_document')->where('contractorID', $recordID)->get();

                if ($this->unlinkAnyFile($getFile = $getAllFiles, $getFullPath = $getUploadDocumentPath)) {
                }

                //Delete all instance of document
                DB::table('tblcontractor_document')->where('contractorID', $recordID)->delete();

                if (DB::table('tblcontractor_registration')->where('contractor_registrationID', $recordID)->delete()) {
                    return redirect()->route('createContractorRegistration')->with('message', 'Your record was deleted successfully.');
                } else {
                    return redirect()->route('createContractorRegistration')->with('error', 'Sorry we cannot delete this record ! Try again');
                }
            } else {
                return redirect()->route('createContractorRegistration')->with('error', 'Sorry we cannot delete this record !');
            }
        } catch (\Throwable $e) {
        }
        return redirect()->route('createContractorRegistration')->with('error', 'Sorry we cannot delete this record !');
    }

    public function removeContractorRecord($recordID = null)
    {

        $recordID = base64_decode($recordID);
        // dd($recordID);

        if (!$recordID) {
            return redirect()
                ->route('createContractorRegistration')
                ->with('error', 'Invalid record ID.');
        }

        try {
            // Start a transaction to ensure DB integrity
            DB::beginTransaction();

            // Check if contractor exists
            $recordExists = DB::table('tblcontract_bidding')
                ->where('contractorID', $recordID)
                ->exists();

            if ($recordExists) {
                return redirect()
                    ->route('createContractorRegistration')
                    ->with('error', 'Delete blocked: this contractor cannot be deleted because they already have bidding records for one or more contracts.');
            }

            // Check if contractor exists
            $recordExists = DB::table('tblcontractor_registration')
                ->where('contractor_registrationID', $recordID)
                ->exists();

            if (!$recordExists) {
                return redirect()
                    ->route('createContractorRegistration')
                    ->with('error', 'Record not found.');
            }

            $uploadPath = $this->getUploadPath;

            /* ========================
            | Delete Bank Records
            ======================== */
            DB::table('tblbank_details')
                ->where('contractorID', $recordID)
                ->delete();

            /* ========================
            | Delete Document Records
            ======================== */
            $documents = DB::table('tblcontractor_document')
                ->where('contractorID', $recordID)
                ->get();

            // Optionally unlink files from storage
            if ($documents->isNotEmpty()) {
                $this->unlinkAnyFile($documents, $uploadPath);
            }

            DB::table('tblcontractor_document')
                ->where('contractorID', $recordID)
                ->delete();

            /* ========================
            | Delete Contractor Record
            ======================== */
            DB::table('tblcontractor_registration')
                ->where('contractor_registrationID', $recordID)
                ->delete();

            // Also delete related records in tblcontractor
            DB::table('tblcontractor')
                ->where('procurementContractorRegistrationId', $recordID)
                ->delete();

            DB::commit();

            Log::info("Contractor record deleted successfully: ID {$recordID}");

            return redirect()
                ->route('createContractorRegistration')
                ->with('message', 'Your record was deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollback();
            Log::error("Failed to delete contractor record: ID {$recordID}. Error: {$e->getMessage()}");

            return redirect()
                ->route('createContractorRegistration')
                ->with('error', 'Sorry, we cannot delete this record! Please try again.');
        }
    }



    //Edit
    public function getEditContractorRecordOLD($recordID = null)
    {
        // Session::forget('editRecord');


        $recordID = base64_decode($recordID);
        if ($recordID && DB::table('tblcontractor_registration')->where('contractor_registrationID', $recordID)->first()) {
            try {
                //Get Contractor Details
                $data['setContractDetails'] = DB::table('tblcontractor_registration')
                    ->leftJoin('protblstate', 'protblstate.stateID', '=', 'tblcontractor_registration.current_stateID')
                    ->leftJoin('users', 'users.id', '=', 'tblcontractor_registration.created_by')
                    ->where('tblcontractor_registration.contractor_registrationID', $recordID)
                    ->first();

                //Get contractor bank detatils
                $data['setBankDetails'] = DB::table('tblbank_details')->where('contractorID', $recordID)->get();

                //Get contractor document
                $data['setDocumentDetails'] = DB::table('tblcontractor_document')->where('contractorID', $recordID)->get();

                //Set Data
                Session::put('editRecord', $data);

                return redirect()->route('createContractorRegistration');
            } catch (\Throwable $e) {
            }
            return redirect()->route('createContractorRegistration')->with('error', 'Sorry we cannot edit this record!');
        } else {
            return redirect()->route('createContractorRegistration')->with('error', 'Sorry we cannot edit this record!');
        }
    }

    public function getEditContractorRecord($recordID = null)
    {
        $recordID = base64_decode($recordID);

        if (!$recordID) {
            return redirect()->route('createContractorRegistration')
                ->with('error', 'Invalid record!');
        }

        $record = DB::table('tblcontractor_registration')
            ->where('contractor_registrationID', $recordID)
            ->first();

        if (!$record) {
            return redirect()->route('createContractorRegistration')
                ->with('error', 'Record not found!');
        }

        try {
            // Get Contractor Details
            $data['setContractDetails'] = DB::table('tblcontractor_registration')
                ->leftJoin('protblstate', 'protblstate.stateID', '=', 'tblcontractor_registration.current_stateID')
                ->leftJoin('users', 'users.id', '=', 'tblcontractor_registration.created_by')
                ->where('tblcontractor_registration.contractor_registrationID', $recordID)
                ->first();

            // Get contractor bank details
            $data['setBankDetails'] = DB::table('tblbank_details')
                ->where('contractorID', $recordID)->get();

            // Get contractor documents
            $data['setDocumentDetails'] = DB::table('tblcontractor_document')
                ->where('contractorID', $recordID)->get();

            dd($data);

            // **Set data in session**
            Session::put('editRecord', $data);



            // **Redirect to the form page**
            return redirect()->route('createContractorRegistration');
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return redirect()->route('createContractorRegistration')
                ->with('error', 'Sorry, we cannot edit this record!');
        }
    }


    //Cancel Edit
    public function cancelEditContractorRecord()
    {
        Session::forget('editRecord');

        return redirect()->route('createContractorRegistration')->with('info', 'Editting was cancelled');
    }


    //create contractor Report
    public function createContractorReport(Request $request)
    {
        try {
            $categoryId = request('category_id');

            // $data['getAllContractor'] = $this->getContractorList($status = 1, $paginate = 50);
            $data['getAllContractor'] = $this->getContractorListNew($status = 1, $paginate = 100, $categoryId);
            $data['getState'] = $this->getStateList($status = 1, $pagination = 0);
            $data['bankList'] = DB::table('tblbanklist')->orderBy('bank', 'Asc')->get();
            $data['requiredDocs'] = DB::table('tblbid_required_docs')->orderBy('bid_doc_description', 'Asc')->get();
            $data['contractCategories'] = DB::table('tblcontractor_category')->get();

            $data['editRecord'] = [];
        } catch (\Throwable $e) {
            $data['contractCategories'] = [];
            $data['getAllContractor'] = [];
            $data['getState'] = [];
            $data['bankList'] = [];
            // $data['requiredDocs'] = [];
            $data['requiredDocs'] = collect();
        }

        // dd($data['getAllContractor']);
        return view('procurement.Contractor.contractorReport', $data);
    }


    //Reuseable Image File Upload Module
    public function uploadAnyFile($file = null, $uploadCompletePathName = null, $maxFileSize = 10, $newExtension = null, $newRadFileName = true)
    {
        $data = new AnyFileUploadClass($file, $uploadCompletePathName, $maxFileSize, $newExtension, $newRadFileName);

        return $data->return();
    } //end function

    //Try to unlink and file. (The function takes array of file or single file)
    public function unlinkAnyFile($getFile = null, $getFullPath = null)
    {
        $data['message'] = 'Unlink not successful !';
        $data['status']  = 0;
        try {
            if ($getFile && $getFullPath) {
                if (is_iterable($getFile) && $getFile) {
                    foreach ($getFile as $item) {
                        $path = $getFullPath . $item;
                        chown($path, 666);
                        if (unlink($path)) {
                            $data['message'] = 'Unlink was successful.';
                            $data['status']  = 1;
                        } else {
                            $data['status']  = 0;
                        }
                    }
                } else {
                    $path = $getFullPath . $getFile;
                    chown($path, 666);
                    if (unlink($path)) {
                        $data['message'] = 'Unlink was successful.';
                        $data['status']  = 1;
                    } else {
                        $data['status']  = 0;
                    }
                }
            } else {
                $data['status']  = 0;
            }
        } catch (\Throwable $e) {
            $data['status']  = 0;
        }

        return $data;
    }

    //Delete Bank
    public function deleteContractorBank($bankID = null, $contractorID = null)
    {
        $bankID = base64_decode($bankID);
        $contractorID = base64_decode($contractorID);
        $message = 0;
        if (($bankID != null) && ($contractorID != null)) {
            if (DB::table('tblbank_details')->where('bank_detailsID', $bankID)->first() && DB::table('tblcontractor_registration')->where('contractor_registrationID', $contractorID)->first()) {
                $message = DB::table('tblbank_details')->where('bank_detailsID', $bankID)->where('contractorID', $contractorID)->delete();
            }
        }
        if ($message) {
            $this->getEditContractorRecord(base64_encode($contractorID));
            return redirect()->route('createContractorRegistration')->with('message', 'A bank details was removed successfully.');
        } else {
            $this->getEditContractorRecord(base64_encode($contractorID));
            return redirect()->route('createContractorRegistration')->with('error', 'Sorry, your bank cannot be removed now! Try again.');
        }
    }

    //Delete Document
    public function deleteContractorDocument($documentID = null, $contractorID = null)
    {
        $documentID = base64_decode($documentID);
        $contractorID = base64_decode($contractorID);
        $message = 0;
        if (($documentID != null) && ($contractorID != null)) {
            if (DB::table('tblcontractor_document')->where('contractor_documentID', $documentID)->first() && DB::table('tblcontractor_registration')->where('contractor_registrationID', $contractorID)->first()) {
                $message = DB::table('tblcontractor_document')->where('contractor_documentID', $documentID)->where('contractorID', $contractorID)->delete();
            }
        }
        if ($message) {
            $this->getEditContractorRecord(base64_encode($contractorID));
            return redirect()->route('createContractorRegistration')->with('message', 'A document was removed successfully.');
        } else {
            $this->getEditContractorRecord(base64_encode($contractorID));
            return redirect()->route('createContractorRegistration')->with('error', 'Sorry, your document cannot be removed now! Try again.');
        }
    }

    public function deleteContractorDocumentNew($id)
    {
        try {
            $document = DB::table('tblcontractor_document')->where('contractor_documentID', $id)->first();

            if (!$document) {
                return redirect()->back()->with('error', 'Document not found.');
            }

            // // Delete the file from storage
            // $filePath = public_path('uploads/contractors/' . $document->file_name);
            // if (file_exists($filePath)) {
            //     unlink($filePath);
            // }

            // Delete the record from DB
            DB::table('tblcontractor_document')->where('contractor_documentID', $id)->delete();

            return redirect()->back()->with('message', 'Document deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('Error deleting document: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete document.');
        }
    }


    public function getDocuments($id)
    {
        // Log::info($id);
        $documents = DB::table('tblcontractor_document')
            ->where('contractorID', $id)
            ->get()
            ->map(function ($doc) {
                return [
                    'contractor_documentID' => $doc->contractor_documentID,
                    'file_description' => $doc->file_description,
                    'file_url' => $doc->file_name, // for S3
                ];
            });

        // Log::info($documents);

        return response()->json($documents);
    }


    public function remainingDocumentTypes($contractorId)
    {
        // Get all document types
        $allDocs = DB::table('tblbid_required_docs')->where('doc_type', 'Technical')->get();

        // Get already uploaded document types for this contractor
        $uploadedDocs = DB::table('tblcontractor_document')
            ->where('contractorID', $contractorId)
            ->pluck('bidRequiredDocsId')
            ->toArray();

        Log::info("Uploaded document type IDs for contractor {$contractorId}: " . implode(', ', $uploadedDocs));

        // Filter out uploaded documents
        $remainingDocs = $allDocs->filter(function ($doc) use ($uploadedDocs) {
            return !in_array($doc->id, $uploadedDocs);
        })->values();

        return response()->json($remainingDocs);
    }


    public function addContractorDocument(Request $request)
    {
        // Log::info($request->all());
        // Validate request
        $request->validate([
            'contractor_id'      => 'required|exists:tblcontractor_registration,contractor_registrationID',
            'document_type'   => 'required|exists:tblbid_required_docs,id',
            'document_file'           => 'required|file|mimes:pdf,jpg,jpeg,png|max:5000',
        ], [
            'document_type.exists' => 'Selected document type is invalid.'
        ]);

        DB::beginTransaction();

        try {
            $contractorId = $request->input('contractor_id');
            $docTypeId    = $request->input('document_type');
            $file         = $request->file('document_file');

            // Get the description from the document type table
            $docType = DB::table('tblbid_required_docs')->where('id', $docTypeId)->first();


            // Generate a custom file name
            $customName = FileUploadHelper::refNo() . '.' . $file->getClientOriginalExtension();

            // Upload the file — returns full URL as string
            $fileUrl = FileUploadHelper::upload($file, 'BiddingDocument', $customName);


            // Save record in DB
            DB::table('tblcontractor_document')->insert([
                'contractorID'      => $contractorId,
                'bidRequiredDocsId' => $docTypeId,
                'file_name'         => $fileUrl, // S3 path
                'file_description'  => $docType->bid_doc_description ?? null,
                'created_at'        => now(),
                'updated_at'        => now(),
                'created_by'        => Auth::id(),
            ]);

            DB::commit();

            return back()->with('message', 'Document uploaded successfully.');
        } catch (\Throwable $e) {
            DB::rollback();
            Log::error('Add Contractor Document Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to upload document. Please try again.');
        }
    }
}//end class
