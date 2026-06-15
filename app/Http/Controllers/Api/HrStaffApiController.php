<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HrStaffApiController extends Controller
{
    /**
     * Return all dropdowns needed by the Add New Staff form:
     * states and departments only (staff list is loaded separately via /list).
     */
    public function getFormData()
    {
        try {
            $departments = DB::table('tbldepartment')
                ->orderBy('department', 'asc')
                ->get()
                ->map(fn($d) => ['id' => $d->id, 'name' => $d->department]);

            $states = DB::table('tblstates')
                ->orderBy('State', 'asc')
                ->get()
                ->map(fn($s) => ['id' => $s->StateID, 'name' => $s->State]);

            return response()->json([
                'status'      => 'success',
                'departments' => $departments,
                'states'      => $states,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get designations for a department (used in cascading dropdown).
     */
    public function getDesignations($deptID)
    {
        $designations = DB::table('tbldesignation')
            ->where('departmentID', $deptID)
            ->orderBy('designation', 'asc')
            ->get(['id', 'designation as name']);

        return response()->json($designations);
    }

    /**
     * Get units for a department (used in cascading dropdown).
     */
    public function getUnits($deptID)
    {
        $units = DB::table('tblunits')
            ->where('departmentID', $deptID)
            ->orderBy('unit', 'asc')
            ->get(['unitID as id', 'unit as name']);

        return response()->json($units);
    }

    /**
     * Get LGAs for a state (used in cascading dropdown).
     */
    public function getLgas($stateID)
    {
        $lgas = DB::table('lga')
            ->where('StateID', $stateID)
            ->orderBy('lga', 'asc')
            ->get(['lgaId as id', 'lga as name']);

        return response()->json($lgas);
    }

    /**
     * Save a new staff record (mirrors adminSaveNewStaff logic).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'          => 'required|string',
            'surname'        => 'required|string',
            'firstname'      => 'required|string',
            'othernames'     => 'nullable|string',
            'email'          => 'nullable|email|unique:tblper,email',
            'phoneNo'        => 'nullable|string',
            'sex'            => 'required|string',
            'maritalStatus'  => 'required|string',
            'department_id'  => 'required|integer',
            'unit_id'        => 'required|integer',
            'designation_id' => 'required|integer',
            'iou'            => 'nullable|numeric',
            'date_of_birth'  => 'required|date',
            'date_of_joining'=> 'required|date',
            'address'        => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            DB::table('tblper')->insert([
                'title'         => strtoupper($request->title),
                'surname'       => strtoupper($request->surname),
                'first_name'    => strtoupper($request->firstname),
                'othernames'    => $request->othernames ? strtoupper($request->othernames) : null,
                'rank'          => 0,
                'bankGroup'     => 1,
                'bank_branch'   => 'ABJ',
                'courtID'       => 9,
                'divisionID'    => 1,
                'phone'         => $request->phoneNo,
                'email'         => $request->email,
                'dob'           => $request->date_of_birth,
                'doj'           => $request->date_of_joining,
                'staff_status'  => 0,
                'status_value'  => 'active service',
                'gender'        => $request->sex,
                'maritalstatus' => $request->maritalStatus,
                'departmentID'  => $request->department_id,
                'unitID'        => $request->unit_id,
                'designationID' => $request->designation_id,
                'dob'           => $request->date_of_birth,
                'doj'           => $request->date_of_joining,
                'home_address'  => $request->address,
                'iou_cap'       => $request->iou,
                'isClaimed'     => 1,
                'isAdmin'       => 1,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Staff record added successfully!',
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Could not add staff record: ' . $th->getMessage(),
            ], 500);
        }
    }

    /**
     * List all admin staff.
     */
    public function list()
    {
        try {
            $staff = DB::table('tblper')
                ->where('isAdmin', 1)
                ->leftJoin('tbldesignation', 'tblper.designationID', '=', 'tbldesignation.id')
                ->leftJoin('tbldepartment', 'tblper.departmentID', '=', 'tbldepartment.id')
                ->leftJoin('lga', 'tblper.lgaID', '=', 'lga.lgaId')
                ->leftJoin('tblstates', 'tblper.stateID', '=', 'tblstates.StateID')
                ->select(
                    'tblper.ID as id',
                    'tblper.fileNo as pf_num',
                    'tblper.title',
                    'tblper.surname',
                    'tblper.first_name',
                    'tblper.othernames',
                    'tblper.gender',
                    'tblper.phone',
                    'tblper.email',
                    'tblper.dob',
                    'tblper.doj',
                    'tblper.maritalstatus',
                    'tbldesignation.designation',
                    'tbldepartment.department',
                    'lga.lga as lga_name',
                    'tblstates.State as state_name',
                    'tblper.progress_regID'
                )
                ->orderBy('tblper.surname', 'asc')
                ->get();

            return response()->json(['status' => 'success', 'staff' => $staff]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
                'staff'   => [],
            ], 500);
        }
    }

    /**
     * Get all documentation data for a specific staff member.
     */
    public function getDocumentation($id)
    {
        try {
            $staff = DB::table('tblper')->where('ID', $id)->first();
            if (!$staff) return response()->json(['status' => 'error', 'message' => 'Staff not found'], 404);

            $data = [
                'basic' => $staff,
                'education' => DB::table('tbleducations')
                    ->leftJoin('tbleducation_category', 'tbleducations.categoryID', '=', 'tbleducation_category.edu_categoryID')
                    ->select('tbleducations.*', 'tbleducation_category.category')
                    ->where('staffid', $id)
                    ->orderBy('categoryID', 'asc')
                    ->get(),
                'marital' => DB::table('tbldateofbirth_wife')->where('staffid', $id)->first(),
                'nextOfKin' => DB::table('tblnextofkin')->where('staffid', $id)->get(),
                'children' => DB::table('tblchildren_particulars')->where('staffid', $id)->get(),
                'experience' => DB::table('previous_servicedetails')->where('staffid', $id)->get(),
                'others' => DB::table('tblotherinfoforstaffdocumentation')->where('staffid', $id)->first(),
                'attachments' => DB::table('tblstaffAttachment')->where('staffID', $id)->get(),
                'lookups' => [
                    'states' => DB::table('tblstates')->select('StateID as id', 'State as name')->get(),
                    'departments' => DB::table('tbldepartment')->select('id', 'department as name')->get(),
                    'hrEmploymentTypes' => DB::table('hr_employment_type')->select('id', 'name')->get(),
                    'educationCategories' => DB::table('tbleducation_category')->select('edu_categoryID as id', 'category as name')->get(),
                    'maritalStatuses' => DB::table('tblmaritalStatus')->select('ID as id', 'marital_status as name')->get(),
                    'banks' => DB::table('tblbanklist')->select('bankID as id', 'bank as name')->orderBy('bank', 'asc')->get(),
                    'religions' => DB::table('tblreligion')->select('Religion as id', 'Religion as name')->get(),
                    'relationships' => DB::table('tbldependant_relationship')->select('id', 'relationship as name')->orderBy('relationship', 'asc')->get(),
                ]
            ];

            return response()->json(['status' => 'success', 'data' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Step 1 & 3: Save Basic Info & Origin.
     */
    public function saveBasicInfo(Request $request, $id)
    {
        try {
            $currentProgress = DB::table('tblper')->where('ID', $id)->value('progress_regID') ?? 0;
            
            $fileNox = $request->fileNo;
            if (empty($fileNox)) {
                $existingFileNo = DB::table('tblper')->where('ID', $id)->value('fileNo');
                if (!empty($existingFileNo)) {
                    $fileNox = $existingFileNo;
                } else {
                    // Generate next staff file number
                    $records = DB::table('tblper')->pluck('fileNo');
                    $numbers = $records->map(function ($fNo) {
                        preg_match('/(\d+)$/', $fNo, $matches);
                        return isset($matches[1]) ? (int) $matches[1] : 0;
                    });
                    $maxNumber = $numbers->max();
                    $nextNumber = $maxNumber + 1;
                    $fileNox = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                }
            }

            DB::table('tblper')->where('ID', $id)->update([
                'fileNo' => $fileNox,
                'title' => $request->title,
                'gender' => $request->gender,
                'dob' => $request->dob,
                'placeofbirth' => $request->placeofbirth,
                'employee_type' => 1,
                'hremploymentType' => $request->hremploymentType,
                'office_shift' => $request->office_shift,
                'department' => $request->departmentID,
                'departmentID' => $request->departmentID,
                'Designation' => $request->designationID,
                'designationID' => $request->designationID,
                'date_present_appointment' => $request->date_present_appointment,
                'staff_status' => 0,
                'status_value' => "new staff",
                'rank' => 0,
                'divisionID' => 1,
                'progress_regID' => max(7, $currentProgress)
            ]);
            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Step 3: Save Origin Info.
     */
    public function saveOrigin(Request $request, $id)
    {
        try {
            $currentProgress = DB::table('tblper')->where('ID', $id)->value('progress_regID') ?? 0;

            DB::table('tblper')->where('ID', $id)->update([
                'stateID' => $request->stateID,
                'lgaID' => $request->lgaID,
                'permanent_addr' => $request->permanent_addr,
                'progress_regID' => max(9, $currentProgress)
            ]);
            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Step 2: Save Contact Info.
     */
    public function saveContact(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'nullable|email|unique:tblper,email,' . $id . ',ID',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $currentProgress = DB::table('tblper')->where('ID', $id)->value('progress_regID') ?? 0;

            DB::table('tblper')->where('ID', $id)->update([
                'email' => $request->email,
                'alternate_email' => $request->alternate_email,
                'phone' => $request->phone,
                'alternate_phone' => $request->alternate_phone,
                'home_address' => $request->home_address,
                'progress_regID' => max(8, $currentProgress)
            ]);

            // Synchronize email update to the users table
            $userId = DB::table('tblper')->where('ID', $id)->value('UserID');
            if ($userId) {
                DB::table('users')->where('id', $userId)->update([
                    'email' => $request->email,
                    'updated_at' => now()
                ]);
            } else {
                DB::table('users')
                    ->where('username', (string)$id)
                    ->update([
                        'email' => $request->email,
                        'updated_at' => now()
                    ]);
            }

            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Step 4: Save Education.
     */
    public function saveEducation(Request $request, $id)
    {
        try {
            $data = [
                'staffid' => $id,
                'categoryID' => $request->categoryID,
                'schoolattended' => $request->schoolattended,
                'schoolfrom' => $request->schoolfrom,
                'schoolto' => $request->schoolto,
                'certificateheld' => $request->certificateheld,
                'degreequalification' => $request->degreequalification,
            ];
            
            if ($request->hasFile('document')) {
                $file = $request->file('document');
                
                // Generate a 6-char random alphanumeric string like RefNo()
                $alphabet = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $pass = [];
                for ($i = 0; $i < 6; $i++) {
                    $pass[] = $alphabet[rand(0, strlen($alphabet) - 1)];
                }
                $customName = implode('', $pass) . '.' . $file->getClientOriginalExtension();

                // Use the exact same helper to get the exact same URL format
                $data['document'] = \App\Helpers\FileUploadHelper::upload($file, 'CertificatesHeld', $customName);
            }

            DB::table('tbleducations')->insert($data);
            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete an Education Record.
     */
    public function deleteEducation($id, $educationId)
    {
        try {
            DB::table('tbleducations')->where('id', $educationId)->delete();
            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Step 5: Save Marital Info.
     */
    public function saveMarital(Request $request, $id)
    {
        try {
            $marital_Status = $request->maritalStatus;

            DB::table('tblper')->where('ID', $id)->update(['maritalstatus' => $marital_Status]);

            if ($marital_Status === 'Single') {
                DB::table('tbldateofbirth_wife')->where('staffid', $id)->delete();
            } else if ($marital_Status === 'Married') {
                $fileNo = DB::table("tblper")->where('ID', $id)->value('fileNo');
                
                $dom = $request->dateofmarriage ? date('Y-m-d', strtotime($request->dateofmarriage)) : null;
                $dob = $request->wifedateofbirth ? date('Y-m-d', strtotime($request->wifedateofbirth)) : null;

                DB::table('tbldateofbirth_wife')->updateOrInsert(
                    ['staffid' => $id],
                    [
                        'fileNo' => $fileNo,
                        'wifename' => $request->wifename,
                        'wifedateofbirth' => $dob,
                        'dateofmarriage' => $dom,
                        'homeplace' => $request->homeplace,
                        'maritalstatus' => $marital_Status
                    ]
                );
            }
            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Step 6: Save Next of Kin.
     */
    public function saveNextOfKin(Request $request, $id)
    {
        try {
            DB::table('tblnextofkin')->where('staffid', $id)->delete();
            $fileNo = DB::table("tblper")->where('ID', $id)->value('fileNo');
            $currentProgress = DB::table('tblper')->where('ID', $id)->value('progress_regID') ?? 0;
            
            foreach ($request->kins as $kin) {
                if (!empty($kin['fullname'])) {
                    DB::table('tblnextofkin')->insert([
                        'staffid' => $id,
                        'fileNo' => $fileNo,
                        'fullname' => $kin['fullname'],
                        'phoneno' => $kin['phoneno'],
                        'relationship' => $kin['relationship'],
                        'address' => $kin['address'] ?? '',
                        'updated_at' => now()
                    ]);
                }
            }
            
            DB::table('tblper')->where('ID', $id)->update([
                'progress_regID' => max(11, $currentProgress)
            ]);
            
            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Step 7: Save Children.
     */
    public function saveChildren(Request $request, $id)
    {
        try {
            DB::table('tblchildren_particulars')->where('staffid', $id)->delete();
            $fileNo = DB::table("tblper")->where('ID', $id)->value('fileNo');
            $currentProgress = DB::table('tblper')->where('ID', $id)->value('progress_regID') ?? 0;
            
            foreach ($request->children as $child) {
                if (!empty($child['fullname'])) {
                    DB::table('tblchildren_particulars')->insert([
                        'staffid' => $id,
                        'fileNo' => $fileNo,
                        'fullname' => $child['fullname'],
                        'gender' => $child['gender'],
                        'dateofbirth' => $child['dateofbirth'] ? date('Y-m-d', strtotime($child['dateofbirth'])) : null,
                    ]);
                }
            }
            
            DB::table('tblper')->where('ID', $id)->update([
                'progress_regID' => max(12, $currentProgress)
            ]);
            
            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Step 10: Save Account Details.
     */
    public function saveAccount(Request $request, $id)
    {
        try {
            $myPer = DB::table('tblper')->where('ID', $id)->first();
            if (!$myPer) {
                return response()->json(['status' => 'error', 'message' => 'Staff not found'], 404);
            }

            DB::table('tblper')->where('ID', $id)->update([
                'bankID' => $request->bankID,
                'AccNo' => $request->accountNumber,
                'bankGroup' => 1
            ]);

            // Add new staff to half payment if they do not already exist
            $existingCandidate = DB::table('half_pay_staff')
                ->where('staffid', $id)
                ->first();

            if (!$existingCandidate) {
                // Fetch Active Period
                $cvdata = DB::select("SELECT * FROM `tblactivemonth` WHERE `courtID`='9'");
                $month = $cvdata ? $cvdata[0]->month : '';
                $year = $cvdata ? $cvdata[0]->year : '';

                DB::table('half_pay_staff')->insert([
                    'staffid' => $id,
                    'interviewCandidateId' => $myPer->interviewCandidateId,
                    'fileNo' => $myPer->fileNo,
                    'courtID' => 9,
                    'due_date' => $myPer->doj,
                    'month_payment' => $month,
                    'year_payment' => $year,
                ]);
            }

            $currentProgress = DB::table('tblper')->where('ID', $id)->value('progress_regID') ?? 0;
            DB::table('tblper')->where('ID', $id)->update([
                'progress_regID' => max(16, $currentProgress)
            ]);

            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Step 8: Save Previous Employment.
     */
    public function saveExperience(Request $request, $id)
    {
        try {
            DB::table('previous_servicedetails')->where('staffid', $id)->delete();
            $fileNo = DB::table("tblper")->where('ID', $id)->value('fileNo');
            $currentProgress = DB::table('tblper')->where('ID', $id)->value('progress_regID') ?? 0;
            
            foreach ($request->experience as $exp) {
                $employer = $exp['employer'] ?? $exp['previousSchudule'] ?? null;
                $pay = $exp['pay'] ?? $exp['totalPreviousPay'] ?? null;
                $from = $exp['from'] ?? $exp['fromDate'] ?? null;
                $to = $exp['to'] ?? $exp['toDate'] ?? null;
                
                if (!empty($employer)) {
                    DB::table('previous_servicedetails')->insert([
                        'staffid' => $id,
                        'fileNo' => $fileNo,
                        'previousSchudule' => $employer,
                        'totalPreviousPay' => !empty($pay) ? str_replace(',', '', $pay) : null,
                        'fromDate' => !empty($from) ? date('Y-m-d', strtotime($from)) : null,
                        'toDate' => !empty($to) ? date('Y-m-d', strtotime($to)) : null,
                        'filePageRef' => $exp['filePageRef'] ?? null,
                        'checkedby' => $exp['checkedby'] ?? null,
                    ]);
                }
            }
            
            DB::table('tblper')->where('ID', $id)->update([
                'progress_regID' => max(13, $currentProgress)
            ]);
            
            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Step 11: Save Passport and Signature.
     */
    public function savePassportSignature(Request $request, $id)
    {
        try {
            $update = [];
            if ($request->hasFile('passport')) {
                $file = $request->file('passport');
                $customName = $this->RefNo() . '_passport.' . $file->getClientOriginalExtension();
                $update['passport_url'] = \App\Helpers\FileUploadHelper::upload($file, 'staffattachments', $customName);
            }
            if ($request->hasFile('signature')) {
                $file = $request->file('signature');
                $customName = $this->RefNo() . '_signature.' . $file->getClientOriginalExtension();
                $update['signature_url'] = \App\Helpers\FileUploadHelper::upload($file, 'staffattachments', $customName);
            }
            
            if (!empty($update)) {
                DB::table('tblper')->where('ID', $id)->update($update);
            }

            $currentProgress = DB::table('tblper')->where('ID', $id)->value('progress_regID') ?? 0;
            DB::table('tblper')->where('ID', $id)->update([
                'progress_regID' => max(17, $currentProgress)
            ]);

            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Step 12: Save Questionnaire (Others).
     */
    public function saveOthers(Request $request, $id)
    {
        try {
            $fileNo = DB::table('tblper')->where('ID', $id)->value('fileNo') ?? '';
            DB::table('tblotherinfoforstaffdocumentation')->updateOrInsert(
                ['staffid' => $id],
                [
                    'fileNo' => $fileNo,
                    'qtn1' => $request->qtn1 ?? '',
                    'qtn2' => $request->qtn2 ?? '',
                    'qtn3' => $request->qtn3 ?? '',
                    'qtn4' => $request->qtn4 ?? '',
                    'qtn5' => $request->qtn5 ?? '',
                    'qtn6' => $request->qtn6 ?? '',
                    'qtn7' => $request->qtn7 ?? '',
                    'qtn8' => $request->qtn8 ?? '',
                    'qtn9' => $request->qtn9 ?? '',
                    'qtn10' => $request->qtn10 ?? '',
                    'qtn11' => $request->qtn11 ?? '',
                ]
            );

            $currentProgress = DB::table('tblper')->where('ID', $id)->value('progress_regID') ?? 0;
            DB::table('tblper')->where('ID', $id)->update([
                'progress_regID' => max(18, $currentProgress)
            ]);

            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Save attachment document.
     */
    public function saveAttachment(Request $request, $id)
    {
        try {
            $request->validate([
                'description' => 'required|string',
                'filename' => 'required|file|mimes:pdf,doc,docx,jpeg,jpg,gif,png,bmp|max:2048',
            ]);

            if ($request->hasFile('filename')) {
                $file = $request->file('filename');
                $customName = $this->RefNo() . '.' . $file->getClientOriginalExtension();

                $fileUrl = \App\Helpers\FileUploadHelper::upload($file, 'staffattachments', $customName);

                $insertedId = DB::table('tblstaffAttachment')->insertGetId([
                    'filepath' => $fileUrl,
                    'filedesc' => $request->description,
                    'staffID' => $id,
                ]);

                $attachment = DB::table('tblstaffAttachment')->where('id', $insertedId)->first();

                return response()->json(['status' => 'success', 'attachment' => $attachment]);
            }
            return response()->json(['status' => 'error', 'message' => 'No file uploaded'], 400);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete attachment document.
     */
    public function deleteAttachment($id, $attachmentId)
    {
        try {
            DB::table('tblstaffAttachment')->where('id', $attachmentId)->where('staffID', $id)->delete();
            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    /**
     * Step 9: Complete Attachments (Advance progress to 15).
     */
    public function completeAttachment($id)
    {
        try {
            $currentProgress = DB::table('tblper')->where('ID', $id)->value('progress_regID') ?? 0;
            DB::table('tblper')->where('ID', $id)->update([
                'progress_regID' => max(15, $currentProgress)
            ]);
            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Step 13: Final Submission & Account Creation.
     */
    public function submitDocumentation($id)
    {
        try {
            $staff = DB::table('tblper')->where('ID', $id)->first();
            if (!$staff) {
                return response()->json(['status' => 'error', 'message' => 'Staff record not found'], 404);
            }

            if (empty($staff->fileNo)) {
                return response()->json(['status' => 'error', 'message' => 'Staff File Number is missing. Please complete the Basic Info step first.'], 422);
            }

            // Check if user already exists
            $fullname = trim($staff->surname . ' ' . $staff->first_name . ' ' . ($staff->othernames ?? ''));
            $rawUsername = (string)$staff->ID;
            $user = null;
            if ($staff->UserID) {
                $user = DB::table('users')->where('id', $staff->UserID)->first();
            }
            if (!$user) {
                $paddedUsername = str_pad($rawUsername, 4, '0', STR_PAD_LEFT);
                $user = DB::table('users')->where('username', $paddedUsername)->first();
            }
            if (!$user) {
                $user = DB::table('users')->where('username', $rawUsername)->first();
            }

            if ($user) {
                // Update existing user
                DB::table('users')->where('id', $user->id)->update([
                    'name' => strtoupper($fullname),
                    'username' => $rawUsername,
                    'email' => $staff->email ?: ($rawUsername . '@isalu.gov.ng'),
                    'user_type' => 'staff',
                    'updated_at' => now()
                ]);
                $userId = $user->id;
                DB::table('tblper')->where('ID', $id)->update(['UserID' => $userId]);
            } else {
                // Create new user
                $userId = DB::table('users')->insertGetId([
                    'name' => strtoupper($fullname),
                    'username' => $rawUsername,
                    'email' => $staff->email ?: ($rawUsername . '@isalu.gov.ng'),
                    'password' => bcrypt('12345'),
                    'courtID' => 9,
                    'user_type' => 'staff',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                DB::table('tblper')->where('ID', $id)->update(['UserID' => $userId]);
            }

            // Ensure role is assigned
            $staffRole = DB::table('user_role')
                ->whereRaw('LOWER(rolename) = ?', ['staff'])
                ->first();
            $staffRoleId = $staffRole ? $staffRole->roleID : 2;

            $roleExists = DB::table('assign_user_role')
                ->where('userID', $userId)
                ->where('roleID', $staffRoleId)
                ->exists();
            if (!$roleExists) {
                DB::table('assign_user_role')->insert([
                    'userID' => $userId,
                    'roleID' => $staffRoleId,
                    'created_at' => now()
                ]);
            }

            // Update candidate documentation status
            if ($staff->interviewCandidateId) {
                DB::table('tblcandidate')->where('candidateID', $staff->interviewCandidateId)->update([
                    'documentation_status' => 1
                ]);
            }

            // Set final progress to 19 (complete)
            $currentProgress = DB::table('tblper')->where('ID', $id)->value('progress_regID') ?? 0;
            DB::table('tblper')->where('ID', $id)->update([
                'progress_regID' => max(19, $currentProgress)
            ]);

            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function RefNo()
    {
        $alphabet = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 6; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }

    public function getProfileDetails($id)
    {
        try {
            $staff = DB::table('tblper')->where('ID', $id)->first();
            if (!$staff) {
                return response()->json(['status' => 'error', 'message' => 'Staff record not found'], 404);
            }

             // Bid-Data
            $staffFullDetails = DB::table('tblper')
                ->leftjoin('tblstatus', 'tblstatus.id', '=', 'tblper.staff_status')
                ->leftjoin('tblstates', 'tblstates.id', '=', 'tblper.stateID')
                ->leftjoin('tblstates as pob_states', 'pob_states.id', '=', 'tblper.placeofbirth')
                ->leftjoin('tbldesignation', 'tblper.Designation', '=', 'tbldesignation.id')
                ->leftjoin('tblsections', 'tblsections.id', '=', 'tblper.section')
                ->leftjoin('tbltitle', 'tbltitle.id', '=', 'tblper.title')
                ->leftjoin('tblgender', 'tblgender.ID', '=', 'tblper.gender')
                ->leftjoin('tblemployment_type', 'tblemployment_type.id', '=', 'tblper.employee_type')
                ->leftjoin('tbldepartment', 'tbldepartment.id', '=', 'tblper.department')
                ->leftjoin('tblmaritalStatus', 'tblmaritalStatus.ID', '=', 'tblper.maritalstatus')
                ->leftjoin('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->leftjoin('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->select(
                    'tblper.*',
                    'tblper.grade as staffGrade',
                    'tblper.ID as staffID',
                    'tbltitle.ID as titleID',
                    'tblper.title as title',
                    'tblper.gender as gender',
                    'tblgender.ID as genderID',
                    'tblstates.ID as stateID',
                    'tbldivision.divisionID as divID',
                    'tblmaritalStatus.ID as msID',
                    'tblemployment_type.id as empID',
                    'tbldepartment.id as deptID',
                    'tblbanklist.bankID as bankID',
                    'tblstates.State as State',
                    'pob_states.State as place_of_birth_name',
                    'tbldepartment.department as department',
                    'tbldesignation.designation as designation',
                    'tblbanklist.bank as bank'
                )
                ->where('tblper.ID', '=', $id)
                ->first();

            // Check for profile image
            $fileNoImage = "default.png";
            if ($staffFullDetails && $staffFullDetails->fileNo) {
                if (\Illuminate\Support\Facades\File::exists(public_path('passport/' . $staffFullDetails->fileNo . '.jpg'))) {
                    $fileNoImage = $staffFullDetails->fileNo . ".jpg";
                }
            }

            // Education
            $education = DB::table('tbleducations')->where('staffid', $id)->get();

            // Languages
            $languages = DB::table('languages')
                ->select(
                    'languages.*',
                    'languages.language as language_name',
                    'languages.spoken as spoken_title',
                    'languages.written as written_title'
                )
                ->where('staffid', $id)
                ->get();

            // Particulars of Children
            $children = DB::table('tblchildren_particulars')
                ->where('staffid', $id)
                ->leftJoin('tblgender', 'tblchildren_particulars.gender', '=', 'tblgender.ID')
                ->select(
                    'tblchildren_particulars.*',
                    'tblgender.gender as gender_name',
                    'tblgender.ID as gID'
                )
                ->get();

            // Details of previous service
            $previousService = DB::table('previous_servicedetails')->where('staffid', $id)->get();

            // Details of service in the forces
            $detailsService = DB::table('detailsofservice')->where('staffid', $id)->get();

            // Record of censures and recommendations
            $censure = DB::table('tblcensures_commendations')->where('staffid', $id)->get();

            // Gratuity Payment
            $gratuityPayment = DB::table('tblgratuity_payment')->where('staffid', $id)->get();

            // Particular of termination of service
            $terminationService = DB::table('service_termination')->where('staffid', $id)->get();

            // Particular of tour and leave
            $tourLeaveRecord = DB::table('tourleave_record')->where('staffid', $id)->get();

            // Record of service
            $recordService = DB::table('recordof_service')->where('staffid', $id)->get();

            // Record of emolument
            $recordEmolument = DB::table('recordof_emolument')->where('staffid', $id)->get();

            // Next of Kin
            $nextOfKin = DB::table('tblnextofkin')->where('staffid', $id)->get();

            // Wife/Spouse details
            $wifeDetails = DB::table('tbldateofbirth_wife')->where('staffid', $id)->get();

            // Attachments
            $attachments = DB::table('tblstaffAttachment')->where('staffID', $id)->get();

            return response()->json([
                'status' => 'success',
                'staffFullDetails' => $staffFullDetails,
                'fileNoImage' => $fileNoImage,
                'education' => $education,
                'languages' => $languages,
                'children' => $children,
                'previousService' => $previousService,
                'detailsService' => $detailsService,
                'censure' => $censure,
                'gratuityPayment' => $gratuityPayment,
                'terminationService' => $terminationService,
                'tourLeaveRecord' => $tourLeaveRecord,
                'recordService' => $recordService,
                'recordEmolument' => $recordEmolument,
                'nextOfKin' => $nextOfKin,
                'wifeDetails' => $wifeDetails,
                'attachments' => $attachments
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all leave types.
     */
    public function getLeaveTypes()
    {
        try {
            $leaveTypes = DB::table('tblleave_type')
                ->orderBy('id', 'desc')
                ->get();
            return response()->json([
                'status' => 'success',
                'data' => $leaveTypes
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new leave type.
     */
    public function storeLeaveType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'leave' => 'required|string',
            'days'  => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $id = DB::table('tblleave_type')->insertGetId([
                'leaveType' => $request->leave,
                'days' => $request->days,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'New leave type was added successfully.',
                'data' => [
                    'id' => $id,
                    'leaveType' => $request->leave,
                    'days' => $request->days
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a leave type.
     */
    public function updateLeaveType(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'leave' => 'required|string',
            'days'  => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::table('tblleave_type')->where('id', $id)->update([
                'leaveType' => $request->leave,
                'days' => $request->days,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Leave type was successfully updated.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a leave type.
     */
    public function deleteLeaveType($id)
    {
        try {
            DB::table('tblleave_type')->where('id', $id)->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Deleted successfully'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/nextjs/hr/add-staff/import
     * Bulk import staff via Excel/CSV spreadsheet.
     */
    public function importStaff(Request $request)
    {
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
                return strtolower(trim((string)$h));
            }, $rows[0]);

            $fieldMap = [
                'staffID' => -1,
                'title' => -1,
                'surname' => -1,
                'firstname' => -1,
                'othernames' => -1,
                'sex' => -1,
                'maritalstatus' => -1,
                'date_of_birth' => -1,
                'phoneno' => -1,
                'email' => -1,
                'address' => -1,
                'department' => -1,
                'unit' => -1,
                'designation' => -1,
                'date_of_joining' => -1,
                'iou' => -1,
            ];

            foreach ($headers as $index => $header) {
                $cleanHeader = str_replace([' ', '_', '-'], '', $header);
                
                if (in_array($cleanHeader, ['staffid', 'id'])) {
                    $fieldMap['staffID'] = $index;
                } elseif (in_array($cleanHeader, ['title'])) {
                    $fieldMap['title'] = $index;
                } elseif (in_array($cleanHeader, ['surname', 'lastname'])) {
                    $fieldMap['surname'] = $index;
                } elseif (in_array($cleanHeader, ['firstname', 'firstname', 'first_name'])) {
                    $fieldMap['firstname'] = $index;
                } elseif (in_array($cleanHeader, ['othernames', 'othername', 'middlename', 'middle_name'])) {
                    $fieldMap['othernames'] = $index;
                } elseif (in_array($cleanHeader, ['sex', 'gender'])) {
                    $fieldMap['sex'] = $index;
                } elseif (in_array($cleanHeader, ['maritalstatus', 'marital'])) {
                    $fieldMap['maritalstatus'] = $index;
                } elseif (in_array($cleanHeader, ['dateofbirth', 'dob', 'birthdate'])) {
                    $fieldMap['date_of_birth'] = $index;
                } elseif (in_array($cleanHeader, ['phoneno', 'phone_no', 'phone', 'telephone'])) {
                    $fieldMap['phoneno'] = $index;
                } elseif (in_array($cleanHeader, ['email', 'emailaddress'])) {
                    $fieldMap['email'] = $index;
                } elseif (in_array($cleanHeader, ['address', 'homeaddress', 'home_address'])) {
                    $fieldMap['address'] = $index;
                } elseif (in_array($cleanHeader, ['department', 'dept', 'departmentid'])) {
                    $fieldMap['department'] = $index;
                } elseif (in_array($cleanHeader, ['unit', 'unitid'])) {
                    $fieldMap['unit'] = $index;
                } elseif (in_array($cleanHeader, ['designation', 'desig', 'designationid'])) {
                    $fieldMap['designation'] = $index;
                } elseif (in_array($cleanHeader, ['dateofjoining', 'doj', 'joiningdate'])) {
                    $fieldMap['date_of_joining'] = $index;
                } elseif (in_array($cleanHeader, ['iou', 'ioucap', 'ioulimit'])) {
                    $fieldMap['iou'] = $index;
                }
            }

            $getValue = function($row, $field) use ($fieldMap) {
                $idx = $fieldMap[$field];
                if ($idx !== -1 && isset($row[$idx])) {
                    return trim((string)$row[$idx]);
                }
                return null;
            };

            $departments = DB::table('tbldepartment')->get()->keyBy(function($item) {
                return strtolower(trim($item->department));
            });
            $departmentsById = DB::table('tbldepartment')->get()->keyBy('id');

            $units = DB::table('tblunits')->get();
            $designations = DB::table('tbldesignation')->get();

            // Calculate starting fileNo sequence
            $records = DB::table('tblper')->pluck('fileNo');
            $numbers = $records->map(function ($fNo) {
                preg_match('/(\d+)$/', $fNo, $matches);
                return isset($matches[1]) ? (int) $matches[1] : 0;
            });
            $maxNumber = $numbers->max();
            $nextFileNumber = $maxNumber + 1;

            $insertedCount = 0;
            $warnings = [];

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

                $surname = $getValue($row, 'surname');
                $firstname = $getValue($row, 'firstname');
                if (empty($surname) || empty($firstname)) {
                    $warnings[] = "Row " . ($r + 1) . ": Surname and First Name are required. Skipping row.";
                    continue;
                }

                // Resolve Department
                $deptVal = $getValue($row, 'department');
                $deptId = null;
                if (!empty($deptVal)) {
                    if (is_numeric($deptVal) && isset($departmentsById[$deptVal])) {
                        $deptId = intval($deptVal);
                    } else {
                        $cleanDeptVal = strtolower($deptVal);
                        if (isset($departments[$cleanDeptVal])) {
                            $deptId = $departments[$cleanDeptVal]->id;
                        } else {
                            foreach ($departments as $name => $d) {
                                if (strpos($name, $cleanDeptVal) !== false || strpos($cleanDeptVal, $name) !== false) {
                                    $deptId = $d->id;
                                    break;
                                }
                            }
                        }
                    }
                }

                if (!$deptId) {
                    $warnings[] = "Row " . ($r + 1) . ": Department '{$deptVal}' could not be resolved. Skipping row.";
                    continue;
                }

                // Resolve Unit
                $unitVal = $getValue($row, 'unit');
                $unitId = null;
                if (!empty($unitVal)) {
                    if (is_numeric($unitVal)) {
                        $foundUnit = $units->firstWhere('unitID', intval($unitVal));
                        if ($foundUnit) {
                            $unitId = $foundUnit->unitID;
                        }
                    } else {
                        $cleanUnitVal = strtolower($unitVal);
                        $foundUnit = $units->first(function($u) use ($cleanUnitVal, $deptId) {
                            return $u->departmentID == $deptId && strtolower(trim($u->unit)) === $cleanUnitVal;
                        });
                        if (!$foundUnit) {
                            $foundUnit = $units->first(function($u) use ($cleanUnitVal) {
                                return strtolower(trim($u->unit)) === $cleanUnitVal;
                            });
                        }
                        if (!$foundUnit) {
                            $foundUnit = $units->first(function($u) use ($cleanUnitVal, $deptId) {
                                $name = strtolower(trim($u->unit));
                                return $u->departmentID == $deptId && (strpos($name, $cleanUnitVal) !== false || strpos($cleanUnitVal, $name) !== false);
                            });
                        }
                        if (!$foundUnit) {
                            $foundUnit = $units->first(function($u) use ($cleanUnitVal) {
                                $name = strtolower(trim($u->unit));
                                return strpos($name, $cleanUnitVal) !== false || strpos($cleanUnitVal, $name) !== false;
                            });
                        }
                        
                        if ($foundUnit) {
                            $unitId = $foundUnit->unitID;
                        }
                    }
                }

                if (!$unitId) {
                    $warnings[] = "Row " . ($r + 1) . ": Unit '{$unitVal}' could not be resolved. Skipping row.";
                    continue;
                }

                // Resolve Designation
                $desigVal = $getValue($row, 'designation');
                $desigId = null;
                if (!empty($desigVal)) {
                    if (is_numeric($desigVal)) {
                        $foundDesig = $designations->firstWhere('id', intval($desigVal));
                        if ($foundDesig) {
                            $desigId = $foundDesig->id;
                        }
                    } else {
                        $cleanDesigVal = strtolower($desigVal);
                        $foundDesig = $designations->first(function($d) use ($cleanDesigVal, $deptId) {
                            return $d->departmentID == $deptId && strtolower(trim($d->designation)) === $cleanDesigVal;
                        });
                        if (!$foundDesig) {
                            $foundDesig = $designations->first(function($d) use ($cleanDesigVal) {
                                return strtolower(trim($d->designation)) === $cleanDesigVal;
                            });
                        }
                        if (!$foundDesig) {
                            $foundDesig = $designations->first(function($d) use ($cleanDesigVal, $deptId) {
                                $name = strtolower(trim($d->designation));
                                return $d->departmentID == $deptId && (strpos($name, $cleanDesigVal) !== false || strpos($cleanDesigVal, $name) !== false);
                            });
                        }
                        if (!$foundDesig) {
                            $foundDesig = $designations->first(function($d) use ($cleanDesigVal) {
                                $name = strtolower(trim($d->designation));
                                return strpos($name, $cleanDesigVal) !== false || strpos($cleanDesigVal, $name) !== false;
                            });
                        }

                        if ($foundDesig) {
                            $desigId = $foundDesig->id;
                        }
                    }
                }

                if (!$desigId) {
                    $warnings[] = "Row " . ($r + 1) . ": Designation '{$desigVal}' could not be resolved. Skipping row.";
                    continue;
                }

                // Resolve Gender
                $sexVal = trim(strtolower((string)$getValue($row, 'sex')));
                $gender = 'Male';
                if ($sexVal === 'female' || $sexVal === 'f') {
                    $gender = 'Female';
                }

                // Resolve Marital Status
                $maritalVal = trim(strtolower((string)$getValue($row, 'maritalstatus')));
                $maritalStatus = 'Single';
                if ($maritalVal === 'married') {
                    $maritalStatus = 'Married';
                } elseif ($maritalVal === 'divorced') {
                    $maritalStatus = 'Divorced';
                } elseif ($maritalVal === 'widowed') {
                    $maritalStatus = 'Widowed';
                }

                // Resolve DOB
                $dobVal = $getValue($row, 'date_of_birth');
                $dob = $this->parseSpreadsheetDate($dobVal);
                if (!$dob) {
                    $warnings[] = "Row " . ($r + 1) . ": Invalid or missing Date of Birth '{$dobVal}'. Skipping row.";
                    continue;
                }

                // Resolve DOJ
                $dojVal = $getValue($row, 'date_of_joining');
                $doj = $this->parseSpreadsheetDate($dojVal);
                if (!$doj) {
                    $warnings[] = "Row " . ($r + 1) . ": Invalid or missing Date of Joining '{$dojVal}'. Skipping row.";
                    continue;
                }

                // Resolve Address
                $address = $getValue($row, 'address');
                if (empty($address)) {
                    $warnings[] = "Row " . ($r + 1) . ": Address is required. Skipping row.";
                    continue;
                }

                // Title
                $title = strtoupper((string)($getValue($row, 'title') ?: 'MR.'));

                // Phone & Email & Othernames
                $phone = $getValue($row, 'phoneno');
                $email = $getValue($row, 'email');
                $othernames = $getValue($row, 'othernames');

                if (!empty($email)) {
                    $emailExists = DB::table('tblper')->where('email', $email)->exists();
                    if ($emailExists) {
                        $warnings[] = "Row " . ($r + 1) . ": Email '{$email}' is already taken. Skipping row.";
                        continue;
                    }
                }

                // IOU
                $iouVal = $getValue($row, 'iou');
                $iou = is_numeric($iouVal) ? floatval($iouVal) : 0.00;

                // Resolve Staff ID
                $staffIdVal = $getValue($row, 'staffID');
                $staffId = is_numeric($staffIdVal) ? intval($staffIdVal) : null;
                if (!$staffId) {
                    $warnings[] = "Row " . ($r + 1) . ": Invalid or missing Staff ID '{$staffIdVal}'. Skipping row.";
                    continue;
                }

                $staffExists = DB::table('tblper')->where('ID', $staffId)->exists();
                if ($staffExists) {
                    $warnings[] = "Row " . ($r + 1) . ": Staff with ID '{$staffId}' already exists in tblper. Skipping row.";
                    continue;
                }

                // File number
                $fileNox = str_pad($nextFileNumber, 4, '0', STR_PAD_LEFT);
                $nextFileNumber++;

                // Insert staff record
                DB::table('tblper')->insert([
                    'ID'            => $staffId,
                    'fileNo'        => $fileNox,
                    'title'         => $title,
                    'surname'       => strtoupper($surname),
                    'first_name'    => strtoupper($firstname),
                    'othernames'    => $othernames ? strtoupper($othernames) : null,
                    'rank'          => 0,
                    'bankGroup'     => 1,
                    'bank_branch'   => 'ABJ',
                    'courtID'       => 9,
                    'divisionID'    => 1,
                    'phone'         => $phone,
                    'email' => $email,
                    'dob' => $dob,
                    'doj' => $doj,
                    'staff_status' => 1,
                    'status_value' => 'active service',
                    'gender' => $gender,
                    'maritalstatus' => $maritalStatus,
                    'departmentID' => $deptId,
                    'unitID' => $unitId,
                    'designationID' => $desigId,
                    'home_address' => $address,
                    'iou_cap' => $iou,
                    'isClaimed' => 1,
                    'isAdmin' => 1,
                    'progress_regID' => 7
                ]);

                // Create user account in the users table if not exists
                // Save tblper ID of the staff for the username column in users table, user_type column should be staff
                $rawUsername = (string)$staffId;
                $userExists = DB::table('users')->where('username', $rawUsername)->exists();
                if (!$userExists) {
                    $fullname = trim($surname . ' ' . $firstname . ' ' . ($othernames ?? ''));
                    $userId = DB::table('users')->insertGetId([
                        'name' => strtoupper($fullname),
                        'username' => $rawUsername,
                        'email' => $email ?: ($rawUsername . '@isalu.gov.ng'),
                        'password' => bcrypt('12345'),
                        'courtID' => 9,
                        'user_type' => 'staff',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    DB::table('tblper')->where('ID', $staffId)->update(['UserID' => $userId]);

                    // Assign default role mapping
                    $staffRole = DB::table('user_role')
                        ->whereRaw('LOWER(rolename) = ?', ['staff'])
                        ->first();
                    $staffRoleId = $staffRole ? $staffRole->roleID : 2;

                    $roleExists = DB::table('assign_user_role')
                        ->where('userID', $userId)
                        ->where('roleID', $staffRoleId)
                        ->exists();
                    if (!$roleExists) {
                        DB::table('assign_user_role')->insert([
                            'userID' => $userId,
                            'roleID' => $staffRoleId,
                            'created_at' => now()
                        ]);
                    }
                }

                $insertedCount++;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Successfully imported {$insertedCount} staff records.",
                'imported_count' => $insertedCount,
                'warnings' => $warnings
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to import staff: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Parses multiple formats of spreadsheet dates into Y-m-d.
     */
    private function parseSpreadsheetDate($val)
    {
        if (empty($val)) {
            return null;
        }

        // If it's a numeric Excel serial date
        if (is_numeric($val)) {
            if (class_exists('\PhpOffice\PhpSpreadsheet\Shared\Date')) {
                return date('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($val));
            } else {
                return date('Y-m-d', ($val - 25569) * 86400);
            }
        }

        $val = trim((string)$val);

        // Try standard PHP strtotime first
        $ts = strtotime($val);
        if ($ts !== false) {
            return date('Y-m-d', $ts);
        }

        // Try parsing with various standard formats using DateTime::createFromFormat
        $formats = [
            'Y-m-d', 'm/d/Y', 'd-m-Y', 'd/m/Y', 'Y/m/d',
            'j/n/Y', 'n/j/Y', 'j-n-Y', 'n-j-Y',
            'Y-m-d H:i:s', 'Y/m/d H:i:s', 'd-m-Y H:i:s', 'd/m/Y H:i:s'
        ];

        foreach ($formats as $format) {
            try {
                $d = \DateTime::createFromFormat($format, $val);
                if ($d && $d->format($format) === $val) {
                    return $d->format('Y-m-d');
                }
            } catch (\Throwable $th) {}
        }

        // Fallback: try swapping delimiters to satisfy strtotime's delimiter expectation
        if (strpos($val, '/') !== false) {
            $ts = strtotime(str_replace('/', '-', $val));
            if ($ts !== false) {
                return date('Y-m-d', $ts);
            }
        } else {
            $ts = strtotime(str_replace('-', '/', $val));
            if ($ts !== false) {
                return date('Y-m-d', $ts);
            }
        }

        return null;
    }

    public function serveUploadedFile($path)
    {
        $path = str_replace('..', '', $path);
        
        // Try storage/app/public first (new uploads)
        $storagePath = storage_path('app/public/' . $path);
        if (file_exists($storagePath)) {
            return response()->file($storagePath);
        }

        // Fallback to public_path (backward compatibility for old uploads)
        $fullPath = public_path($path);
        if (file_exists($fullPath)) {
            return response()->file($fullPath);
        }

        abort(404);
    }
}
