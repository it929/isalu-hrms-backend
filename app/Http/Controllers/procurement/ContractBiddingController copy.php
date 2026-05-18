<?php

namespace App\Http\Controllers\procurement;



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


    public function create()
    {
        $data['contractor'] = DB::table('tblcontractor_registration')->where('status', '=', 1)->get();
        $data['contract'] = DB::table('tblcontract_details')->where('status', '=', 1)->get();
        $data['requiredDocs'] = DB::table('tblbid_required_docs')->get();
        $data['requiredDocsFinancial'] = DB::table('tblbid_required_docs')->where('doc_type', '=', 'Financial')->get();
        $data['requiredDocsTechnical'] = DB::table('tblbid_required_docs')->where('doc_type', '=', 'Technical')->get();
        //dd($data['requiredDocsFinancial']);
        return view('procurement.ContractBidding.add', $data);
    }

    public function saveBidding(Request $request)
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

    public function editBid($id = null)
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
        $data['viewDocumentsTechnical'] = DB::table('tblbid_required_docs')->where('doc_type', '=', 'Technical')
            ->leftJoin('tblcontractor_contract_bid_document', function ($join) use ($d, $edit) {
                $join->on('tblcontractor_contract_bid_document.bidDocID', '=', 'tblbid_required_docs.id')
                    ->where('tblcontractor_contract_bid_document.biddingID', '=', $d)
                    ->where('tblcontractor_contract_bid_document.contractorID', '=', $edit->contractorID)
                    ->where('tblcontractor_contract_bid_document.contractID', '=', $edit->contractID);
            })
            ->select(
                'tblbid_required_docs.*',
                'tblcontractor_contract_bid_document.id as contractor_bidding_documentID',
                'tblcontractor_contract_bid_document.biddingID',
                'tblcontractor_contract_bid_document.contractorID',
                'tblcontractor_contract_bid_document.contractID',
                'tblcontractor_contract_bid_document.bidDocID',
                'tblcontractor_contract_bid_document.bidDocument',
                'tblcontractor_contract_bid_document.updated_at'
            )
            ->orderBy('tblbid_required_docs.id')
            ->get();

        $data['viewDocumentsFinancial'] = DB::table('tblbid_required_docs')->where('doc_type', '=', 'Financial')
            ->leftJoin('tblcontractor_contract_bid_document', function ($join) use ($d, $edit) {
                $join->on('tblcontractor_contract_bid_document.bidDocID', '=', 'tblbid_required_docs.id')
                    ->where('tblcontractor_contract_bid_document.biddingID', '=', $d)
                    ->where('tblcontractor_contract_bid_document.contractorID', '=', $edit->contractorID)
                    ->where('tblcontractor_contract_bid_document.contractID', '=', $edit->contractID);
            })
            ->select(
                'tblbid_required_docs.*',
                'tblcontractor_contract_bid_document.id as contractor_bidding_documentID',
                'tblcontractor_contract_bid_document.biddingID',
                'tblcontractor_contract_bid_document.contractorID',
                'tblcontractor_contract_bid_document.contractID',
                'tblcontractor_contract_bid_document.bidDocID',
                'tblcontractor_contract_bid_document.bidDocument',
                'tblcontractor_contract_bid_document.updated_at'
            )
            ->orderBy('tblbid_required_docs.id')
            ->get();

        //dd($data['viewDocumentsFinancial']);
        $data['contractor'] = DB::table('tblcontractor_registration')->where('status', '=', 1)->get();
        $data['contract'] = DB::table('tblcontract_details')->where('status', '=', 1)->get();
        return view('procurement.ContractBidding.editBid', $data);
    }

    public function updateBid(Request $request)
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

    public function bidUpdate(Request $request)
    {
        if ($request['modalUpdate'] === 'yes') {
            $file = $request->file('file');
            $location = public_path('BiddingDocument');
            foreach ($file as $key => $val) {
                $filename = $val->getClientOriginalName();
                DB::table('tblcontractor_contract_bid_document')
                    ->where('id', $request['contractorBidDocID'])
                    ->update([
                        'bidDocument' => $filename,
                        'updated_at' => date('Y-m-d')
                    ]);

                $val->move($location, $filename);
                return back()->with('msg', 'Successfully Updated');
            }
        }

        if ($request['modalUpload'] === 'yes') {
            $file = $request->file('fileUpload');
            $location = public_path('BiddingDocument');
            foreach ($file as $key => $val) {
                $filename = $val->getClientOriginalName();
                DB::table('tblcontractor_contract_bid_document')
                    ->insert([
                        'biddingID' => $request['biddingID'],
                        'contractorID' => $request['contractorID'],
                        'contractID' => $request['contractID'],
                        'bidDocID' => $request['docDescId'],
                        'bidDocument' => $filename,
                        'updated_at' => date('Y-m-d')
                    ]);

                $val->move($location, $filename);
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
                $filename = $val->getClientOriginalName();
                //dd($request['description'][$key]);
                DB::table('tblcontractor_bidding_document')->insert([
                    'biddingID' => $request['bidID'],
                    'file_name' => $filename,
                    'file_description' => $request['description'][$key],
                    'created_by' => Auth::user()->id,
                    'created_at' => date('Y-m-d'),
                    'updated_at' => date('Y-m-d'),
                ]);
                $move = $val->move($location, $filename);
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
        // $data['comments'] = DB::table('tblcontract_comment')
        //     ->join('users', 'users.id', '=', 'tblcontract_comment.created_by')
        //     ->where('tblcontract_comment.contractID', '=', $d)
        //     ->select(
        //         'tblcontract_comment.*',
        //         'users.name',
        //         'tblcontract_comment.created_at as comment_created_at'
        //     )
        //     ->get();

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
