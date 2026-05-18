<?php

namespace App\Http\Controllers\funds;



use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Input;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class StaffInformationSetUpController extends Controller
{

    //make this page accessible only by authenticated user
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->middleware('auth');
                return $next($request);
        });
    }

    //load create page
    public function home()
    {
        if (!Schema::hasTable('tblStaffInformation')) {
            $this->creatStaffClaimTable();
        }
        $data['bank'] = $this->getBank();
        $data['department'] = $this->getDepartment();
        $data['allStaffDetails'] = $this->getAllStaff(30);
        // dd($data['bank']);
        $data['showError'] = Session::get('showError');
        return view('funds.staffInformation.home', $data);
    }

    //Get all bank names
    public function getBank()
    {
        return DB::table('tblbanklist')->orderBy('bank', 'Asc')->get();
        //return array();
    }


    //Get all bank names
    public function getDepartment()
    {
        return DB::table('tbldepartment')->orderBy('department', 'Asc')->get();
        //return array();
    }


    //Query record
    public function getAllStaffbackup10022026($perPage)
    {
        if ($perPage > 0) {
            $getStaffClaim = DB::table('tblStaffInformation')
                //->join('users', 'users.id', '=', 'tblStaffInformation.userID')
                ->leftjoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblStaffInformation.bankID')
                ->leftjoin('tbldepartment', 'tbldepartment.id', '=', 'tblStaffInformation.departmentID')
                ->where('tblStaffInformation.active', 1)->where('tblStaffInformation.Isstaff', 0)
                ->orderBy('tblStaffInformation.staffID', 'Desc')
                ->select('*', 'tblStaffInformation.departmentID as staffDepartmentID', 'tblStaffInformation.fileNo as StaffFileNo', 'tblStaffInformation.bankID as staffBankID')
                ->paginate($perPage);
            return $getStaffClaim;
        } else {
            $getStaffClaim = DB::table('tblStaffInformation')
                //->join('users', 'users.id', '=', 'tblStaffInformation.userID')
                ->leftjoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblStaffInformation.bankID')
                ->leftjoin('tbldepartment', 'tbldepartment.id', '=', 'tblStaffInformation.departmentID')
                ->where('tblStaffInformation.active', 1)->where('tblStaffInformation.Isstaff', 0)
                ->orderBy('tblStaffInformation.staffID', 'Desc')
                ->select('*', 'tblStaffInformation.departmentID as staffDepartmentID', 'tblStaffInformation.fileNo as StaffFileNo', 'tblStaffInformation.bankID as staffBankID')
                ->get();
            return $getStaffClaim;
        }
    }

    public function getAllStaff($perPage)
    {
        if ($perPage > 0) {
            $getStaffClaim = DB::table('tblper')
                //->join('users', 'users.id', '=', 'tblper.userID')
                ->leftjoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.claimBankId')
                ->leftjoin('tbldepartment', 'tbldepartment.id', '=', 'tblper.departmentID')
                ->where('tblper.staff_status', 0)->where('tblper.isClaimed', 1)
                // ->orderBy('tblper.staffID', 'Desc')
                ->select('*', 'tblper.departmentID as staffDepartmentID', 'tblper.fileNo as StaffFileNo', 'tblper.bankID as staffBankID')
                ->paginate($perPage);
            return $getStaffClaim;
        } else {
            $getStaffClaim = DB::table('tblper')
                //->join('users', 'users.id', '=', 'tblper.userID')
                ->leftjoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.claimBankId')
                ->leftjoin('tbldepartment', 'tbldepartment.id', '=', 'tblper.departmentID')
                ->where('tblper.staff_status', 0)->where('tblper.isClaimed', 1)
                // ->orderBy('tblper.staffID', 'Desc')
                ->select('*', 'tblper.departmentID as staffDepartmentID', 'tblper.fileNo as StaffFileNo', 'tblStaffInformation.bankID as staffBankID')
                ->get();
            return $getStaffClaim;
        }
    }

    //http post (Insert new record)
    public function store(Request $httpReq)
    {
        Session::put('showError', 0);
        $this->validate($httpReq, [
            'surname'       => 'required|string|max:255',
            'firstname'     => 'required|string|max:255',
            'othernames'    => 'nullable|string|max:255',
            'bankName'      => 'required|integer',
            'accountNumber' => 'required|digits:10',
            'department'    => 'nullable',
            'sortCode'      => 'nullable|numeric',
        ], [
            'accountNumber.required' => 'Account number is required.',
            'accountNumber.digits'   => 'Nigerian account number must be exactly 10 digits.',
        ]);

        $fileNo = trim($httpReq['staffFileNo'] ?? '');

        $fileNo = $fileNo === '' ? null : $fileNo;

        if ($fileNo !== null) {
            $exists = DB::table('tblper')
                ->where('fileNo', $fileNo)
                ->exists();

            if ($exists) {
                return back()->withErrors([
                    'staffFileNo' => 'This file number already exists.'
                ])->withInput();
            }
        }

        //create staff to users Table
        DB::table('tblper')->insertGetId(array(
            'surname'     => trim($httpReq['surname']),
            'first_name'     => trim($httpReq['firstname']),
            'othernames'     => trim($httpReq['othernames']),

            'fileNo'        => $httpReq['staffFileNo'] == '' ? 'NA' : $httpReq['staffFileNo'],
            'claimBankSortCode'     => ($httpReq['sortCode']) ? $httpReq['sortCode'] : '00000',

            'claimBankId'        => ($httpReq['bankName']) ? $httpReq['bankName'] : 0,
            'claimAccountNo'    => ($httpReq['accountNumber']) ? $httpReq['accountNumber'] : '000000000',
            // 'departmentID'  => ($httpReq['department']) ? $httpReq['department'] : 0,

            'staff_status'  => 0,

            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ));
        Session::put('showError', 1);
        return redirect()->route('staffInfo')->with('message', trim($httpReq['staffName']) . "Account was created successfully.");
    }


    public function update(Request $httpReq)
    {
        Session::put('showError', 1);

        $this->validate($httpReq, [
            'surname'       => 'required|string|max:255',
            'firstname'     => 'required|string|max:255',
            'othernames'    => 'required|string|max:255',
            'bankName'      => 'required|numeric',
            'accountNumber' => 'nullable|digits:10',
            'department'    => 'nullable|numeric',
            'sortCode'      => 'nullable|numeric',
            'recordID'      => 'required|numeric',
        ]);

        $staffPERRecord = DB::table('tblper')
            ->where('ID', trim($httpReq['recordID']))
            ->where('staff_status', 0)
            ->where('isClaimed', 1)
            ->first();

        if (!$staffPERRecord) {
            return redirect()->back()->with('message', 'Staff record not found or does not meet criteria.');
        }

        $fileNo = trim($httpReq['staffFileNo'] ?? '');

        $fileNo = $fileNo === '' ? null : $fileNo;

        if ($fileNo !== null) {
            $exists = DB::table('tblper')
                ->where('fileNo', $fileNo)
                ->exists();

            if ($exists) {
                return back()->withErrors([
                    'staffFileNo' => 'This file number already exists.'
                ])->withInput();
            }
        }

        $newBankId = $httpReq['bankName'] ? $httpReq['bankName'] : 0;
        $newAccountNo = $httpReq['accountNumber'] ? $httpReq['accountNumber'] : '000000000';
        $newSortCode = $httpReq['sortCode'] ? $httpReq['sortCode'] : '0000';

        $oldBankId = $staffPERRecord->claimBankId;
        $oldAccountNo = $staffPERRecord->claimAccountNo;
        $oldSortCode = $staffPERRecord->claimBankSortCode;

        DB::beginTransaction();

        try {
            DB::table('tblper')
                ->where('ID', trim($httpReq['recordID']))
                ->where('staff_status', 0)
                ->where('isClaimed', 1)
                ->update([
                    'surname'           => trim($httpReq['surname']),
                    'first_name'        => trim($httpReq['firstname']),
                    'othernames'        => trim($httpReq['othernames']),
                    'fileNo'            => $httpReq['staffFileNo'] == '' ? 'NA' : $httpReq['staffFileNo'],
                    'claimBankId'       => $newBankId,
                    'claimAccountNo'    => $newAccountNo,
                    'claimBankSortCode' => $newSortCode,
                    'departmentID'      => trim($httpReq['department']),
                    'updated_at'        => now(),
                ]);

            $bankDetailsChanged =
                (string) $oldBankId !== (string) $newBankId ||
                (string) $oldAccountNo !== (string) $newAccountNo ||
                (string) $oldSortCode !== (string) $newSortCode;

            if ($bankDetailsChanged) {
                DB::table('claim_bank_audit_logs')->insert([
                    'tblper_id' => $staffPERRecord->ID,
                    'user_id' => Auth::id(),
                    'old_claimBankSortCode' => $oldSortCode,
                    'new_claimBankSortCode' => $newSortCode,
                    'old_claimBankId' => $oldBankId,
                    'new_claimBankId' => $newBankId,
                    'old_claimAccountNo' => $oldAccountNo,
                    'new_claimAccountNo' => $newAccountNo,
                    'action_type' => 'update',
                    'changed_at' => now(),
                    'ip_address' => $httpReq->ip(),
                    'user_agent' => $httpReq->userAgent(),
                    'remarks' => 'Claim bank details updated',
                ]);
            }

            DB::commit();

            return redirect()->back()->with('message', trim($httpReq['staffName']) . ' Account was updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->with('message', 'Update failed: ' . $e->getMessage());
        }
    }

    //DB Schema for Table (Create New Table)
    public function creatStaffClaimTable()
    {
        return Schema::create('tblStaffInformation', function ($table) {
            $table->increments('staffID');
            $table->integer('userID')->nullable();
            $table->integer('fileNo')->nullable();
            $table->string('full_name')->nullable();
            $table->integer('departmentID')->nullable();
            $table->integer('bankID')->nullable();
            $table->string('account_no', 11)->nullable();
            $table->string('sort_code', 100)->nullable();
            $table->date('created_at')->nullable();
            $table->date('updated_at')->nullable();
            $table->integer('active')->default(1);
        });
    }
    //////end it here///////



    public function accountNumberIndex()
    {
        $data['bank'] = $this->getBank();
        $data['department'] = $this->getDepartment();
        $data['allStaffDetails'] = $this->getAllStaff(30);
        // dd($data['bank']);
        $data['showError'] = Session::get('showError');
        return view('funds.staffInformation.update-account-number', $data);
    }

    public function searchStaff(Request $request)
    {
        $search = $request->term; // IMPORTANT for Select2

        $staff = DB::table('tblper')
            ->leftJoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.claimBankId')
            // ->leftJoin('tbldepartment', 'tbldepartment.id', '=', 'tblper.departmentID')
            ->where(function ($query) use ($search) {
                $query->where('tblper.fileNo', 'like', "%{$search}%")
                    ->orWhere('tblper.surname', 'like', "%{$search}%")
                    ->orWhere('tblper.first_name', 'like', "%{$search}%");
            })
            ->limit(100)
            ->select(
                'tblper.fileNo',
                'tblper.surname',
                'tblper.first_name as firstname',
                'tblper.othernames',
                'tblper.claimAccountNo as accountNumber',
                'tblper.claimBankId as bankID',
                'tblbanklist.sortcode'
            )
            ->get();

        $results = [];

        foreach ($staff as $st) {
            $results[] = [
                'id' => $st->fileNo, // this will be submitted
                'text' => $st->fileNo . ' - ' . $st->surname . ' ' . $st->firstname,
                'surname' => $st->surname,
                'firstname' => $st->firstname,
                'othernames' => $st->othernames,
                'accountNumber' => $st->accountNumber,
                'bankName' => $st->bankID,
                'sortCode' => $st->sortcode,
                'fileNo' => $st->fileNo,
            ];
        }

        // Log::info($results);
        return response()->json(['results' => $results]);
    }


    public function updateStaffAccountDetails(Request $request)
    {

        // Log::info($request->all());
        // Validate input
        $request->validate([
            'staffFileNo'  => 'required',
            'accountNumber' => 'required|string|max:11',
            'bankName'     => 'required',
            'sortCode'     => 'nullable|string|max:11',
        ]);

        $staffPERRecord = DB::table('tblper')
            ->where('fileNo', $request->staffFileNo)
            ->first();

        if (!$staffPERRecord) {
            return redirect()->back()->with('message', 'Staff record not found or does not meet criteria.');
        }

        $newBankId = $request->bankName ? $request->bankName : 0;
        $newAccountNo = $request->accountNumber ? $request->accountNumber : '000000000';
        $newSortCode = $request->sortCode ? $request->sortCode : '0000';

        $oldBankId = (string) $staffPERRecord->claimBankId;
        $oldAccountNo = (string) $staffPERRecord->claimAccountNo;
        $oldSortCode = (string)$staffPERRecord->claimBankSortCode;


        // Update using file number
        $updated = DB::table('tblper')
            ->where('fileNo', $request->staffFileNo)
            ->update([
                'claimAccountNo'        => $request->accountNumber,
                'claimBankId'           => $request->bankName,
                'claimBankSortCode'     => $request->sortCode,
                'updated_at'            => now(), // only if column exists
            ]);


        $bankDetailsChanged =
            (string) $oldBankId !== (string) $newBankId ||
            (string) $oldAccountNo !== (string) $newAccountNo ||
            (string) $oldSortCode !== (string) $newSortCode;

        if ($bankDetailsChanged) {
            DB::table('claim_bank_audit_logs')->insert([
                'tblper_id' => $staffPERRecord->ID,
                'user_id' => Auth::id(),
                'old_claimBankSortCode' => $oldSortCode,
                'new_claimBankSortCode' => $newSortCode,
                'old_claimBankId' => $oldBankId,
                'new_claimBankId' => $newBankId,
                'old_claimAccountNo' => $oldAccountNo,
                'new_claimAccountNo' => $newAccountNo,
                'action_type' => 'update',
                'changed_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'remarks' => 'Claim bank details updated',
            ]);
        }

        if ($updated) {
            return back()->with('message', 'Staff account details updated successfully.');
        }

        return back()->with('error', 'No record was updated.');
    }


    public function claimBankAuditLogs(Request $request)
    {
        $query = DB::table('claim_bank_audit_logs as a')
            ->leftJoin('users as u', 'a.user_id', '=', 'u.id')
            ->leftJoin('tblper as p', 'a.tblper_id', '=', 'p.ID')
            ->leftJoin('tblbanklist as old_bank', 'a.old_claimBankId', '=', 'old_bank.bankID')
            ->leftJoin('tblbanklist as new_bank', 'a.new_claimBankId', '=', 'new_bank.bankID')
            ->select(
                'a.*',
                'u.name as updated_by_name',
                'p.surname',
                'p.first_name',
                'p.othernames',
                'p.fileNo',
                'old_bank.bank as old_bank_name',
                'new_bank.bank as new_bank_name'
            );

        if ($request->filled('record_id')) {
            $query->where('a.tblper_id', $request->record_id);
        }

        if ($request->filled('account_no')) {
            $query->where(function ($q) use ($request) {
                $q->where('a.old_claimAccountNo', 'like', '%' . trim($request->account_no) . '%')
                    ->orWhere('a.new_claimAccountNo', 'like', '%' . trim($request->account_no) . '%');
            });
        }

        if ($request->filled('bank_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('a.old_claimBankId', $request->bank_id)
                    ->orWhere('a.new_claimBankId', $request->bank_id);
            });
        }

        if ($request->filled('sort_code')) {
            $query->where(function ($q) use ($request) {
                $q->where('a.old_claimBankSortCode', 'like', '%' . trim($request->sort_code) . '%')
                    ->orWhere('a.new_claimBankSortCode', 'like', '%' . trim($request->sort_code) . '%');
            });
        }

        if ($request->filled('tblper_id')) {
            $query->where('a.tblper_id', $request->tblper_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('a.changed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('a.changed_at', '<=', $request->date_to);
        }

        // dd($request->user_id);
        $logs = $query->orderBy('a.id', 'desc')->paginate(20)->appends($request->all());

        $users = DB::table('tblper')
            ->select('ID', 'surname', 'first_name', 'othernames', 'fileNo')
            ->orderBy('surname')
            ->get();

        $banks = DB::table('tblbanklist')->select('bankID', 'bank')->orderBy('bank')->get();

        return view('funds.staffInformation.claim_bank_logs', compact('logs', 'users', 'banks'));
    }
}//end class
