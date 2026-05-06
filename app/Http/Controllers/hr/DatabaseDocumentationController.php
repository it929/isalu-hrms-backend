<?php

namespace App\Http\Controllers\hr;

use Illuminate\Http\Request;

use App\Http\Requests;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class DatabaseDocumentationController extends Controller
{
    public function getStaffList()
    {
        $List = DB::Select("SELECT * FROM `tblper` WHERE progress_regID >= 6");
        $data['fillUpForm'] = $this->fillUpForm('');
        return $List;
    }


    public function basicSetUp($fileNox, $fileNo, $title, $gender, $dateofBirth, $placeofBirth, $empType, $hremptype,  $department,  $departmentID, $designation, $designationID, $presentApptmnt, $officeshift,)
    {
        DB::table('tblper')->where('ID', '=', $fileNo)->update([
            'fileNo' => $fileNox,
            'title' => $title,
            'gender' => $gender,
            'dob' => $dateofBirth,
            'placeofbirth' => $placeofBirth,
            'employee_type' => $empType,
            'hremploymentType' => $hremptype,
            'office_shift' => $officeshift,
            // 'grade' => $gradeLevel,
            // 'step' => $step,
            'department' => $department,
            'departmentID' =>  $departmentID,
            'Designation' => $designation,
            'designationID' => $designationID,
            // 'appointment_date' => $firstApptmnt,
            'date_present_appointment' => $presentApptmnt,
            // 'resumption_date' => $resumptionDate,
            'staff_status' => 0,
            'status_value' => "new staff",
            'rank' => 0,
            'divisionID' => 1,

        ]);
    }


    public function getMarriageInfo($fileNo)
    {
        $list = DB::table("tbldateofbirth_wife")->where('staffid', $fileNo)->first();
        return $list;
    }


    public function getStaff($fileNo)
    {
        //dd("SELECT * FROM `tblper` WHERE `ID`='$fileNo'");
        $List = DB::Select("SELECT * FROM `tblper` WHERE `ID`='$fileNo'");
        return $List;
    }

    public function getProgress($fileNo)
    {
        $progress = DB::table("tblper")->where('ID', $fileNo)->value('progress_regID');
        return $progress;
    }

    public function setProgress($fileNo, $prog)
    {
        DB::table('tblper')->where('ID', $fileNo)->update(['progress_regID' => $prog]);
    }



    public function relationshipSetUp($staffid, $marital_Status, $maritalstatus, $dom, $fullname, $dob, $address)
    {
        //dd($marital_Status);
        DB::UPDATE("UPDATE tblper SET maritalstatus = '$marital_Status' WHERE `ID` = '$staffid'");
        if ($maritalstatus == 'Single') {
            DB::table('tbldateofbirth_wife')->where('staffid', '=', $staffid)->delete();
        }
        $fileNo = $this->fileD($staffid);
        if ($maritalstatus == 'Married') {
            $ifExists = DB::table('tbldateofbirth_wife')->where('staffid', '=', $staffid)->exists();
            if ($ifExists) {
                DB::table('tbldateofbirth_wife')->where('staffid', '=', $staffid)->update(
                    [
                        'staffid' => $staffid,
                        'maritalstatus' => $marital_Status,
                        'dateofmarriage' => $dom,
                        'maritalstatus' => $marital_Status,
                        'wifename' => $fullname,
                        'wifedateofbirth' => $dob,
                        'homeplace' => $address
                    ]
                );

                DB::table('tblper')->where('ID', $staffid)->update(
                    ['maritalstatus' => $marital_Status]
                );
            } else {
                DB::table('tbldateofbirth_wife')->insert(
                    [
                        'staffid' => $staffid,
                        'fileNo' => $fileNo,
                        'dateofmarriage' => $dom,
                        'maritalstatus' => $marital_Status,
                        'wifename' => $fullname,
                        'wifedateofbirth' => $dob,
                        'homeplace' => $address
                    ]
                );

                DB::table('tblper')->where('ID', $staffid)->update(
                    ['maritalstatus' => $marital_Status]
                );
            }
        }
    }



    public function contactSetUp(
        $fileNo,
        $email,
        $alternateEmail,
        $phone,
        $alternativePhone,
        $physicalAddress
    ) {
        DB::table('tblper')->where('ID', $fileNo)->update([
            'email' => $email,
            'alternate_email' => $alternateEmail,
            'phone' => $phone,
            'alternate_phone' => $alternativePhone,
            'home_address' => $physicalAddress,
        ]);
    }



    public function nextOfKinSetUp($staffid, $fullName, $phoneNumber, $physicalAddress, $relationship)
    {
        $fileNo = $this->fileD($staffid);
        DB::table('tblnextofkin')->insert(
            [
                'staffid' => $staffid,
                'fullname' => $fullName,
                'phoneno' => $phoneNumber,
                'relationship' => $relationship,
                'address' => $physicalAddress,
                'updated_at' => Carbon::now()
            ]
        );
    }


    public function placeofBirthSetUp($fileNo, $state, $lga, $address)
    {
        DB::table('tblper')->where('ID', '=', $fileNo)->update([
            'stateID' => $state,
            'lgaID' => $lga,
            'permanent_addr' => $address
        ]);
    }


    public function accountSetUp($fileNo, $bankID, $accountNumber)
    {
        DB::table('tblper')->where('ID', '=', $fileNo)->update([
            'bankID' => $bankID,
            'AccNo' => $accountNumber,
            'bankGroup' => 1,
        ]);
    }


    public function previousEmploymentSetUp($staffid, $prevemp, $previousPay, $fromPrevEmp, $toPrevEmp, $page, $check)
    {


        $numofemploy = count($prevemp);

        DB::DELETE("DELETE FROM previous_servicedetails WHERE `staffid` = '$staffid'");

        $fileNo = $this->fileD($staffid);
        for ($i = 0; $i < $numofemploy; $i++) {

            $employment = $prevemp[$i];
            $pay  = $previousPay[$i];
            $from = $fromPrevEmp[$i];
            $to   = $toPrevEmp[$i];
            $filePage = $page[$i];
            $checkBy  = $check[$i];

            if (!empty($employment) && !empty($pay)) {
                DB::table('previous_servicedetails')->insert(array(
                    'staffid'        => $staffid,
                    'fileNo'        => $fileNo,
                    'previousSchudule'        => $employment,
                    'totalPreviousPay'        => str_replace(',', '', $pay),
                    'fromDate'              => date('Y-m-d', strtotime($from)),
                    'toDate'                => date('Y-m-d', strtotime($to)),
                    'filePageRef'           => $filePage,
                    'checkedby'             => $checkBy,
                ));

                $data['message'] = 'Employment record has been saved!';
            }
        }
    }


    public function childrenSetUp($staffid, $childrenname, $childrendob,  $childrengender)
    {
        $numofchildren = count($childrenname);
        $fileNo = $this->fileD($staffid);

        DB::DELETE("DELETE FROM tblchildren_particulars WHERE `staffid` = '$staffid'");
        //var_dump($count);
        $arr = [];

        for ($i = 0; $i < $numofchildren; $i++) {

            $fullname   = $childrenname[$i];
            $gender     = $childrengender[$i];
            $dob        = $childrendob[$i];

            if (!empty($fullname) && !empty($gender) && !empty($dob)) {
                DB::table('tblchildren_particulars')->insert(array(
                    'staffid'        => $staffid,
                    'fileNo'        => $fileNo,
                    'fullname'        => $fullname,
                    'gender'        => $gender,
                    'dateofbirth'   => date('Y-m-d', strtotime($dob)),
                ));
                $data['message'] = 'Children information has been saved!';
            }
        }
    }

    public function othersSetUp(
        $staffid,
        $convicted,
        $convictreason,
        $illness,
        $illness_reason,
        $repay,
        $judgement,
        $judgmentr,
        $detail_in_force,
        $decoration,
        $religion,
        $agree
    ) {
        $fileNo = $this->fileD($staffid);
        $chk = DB::Select("SELECT staffid FROM tblotherinfoforstaffdocumentation WHERE `staffid` = '$staffid'");

        if ($chk) {

            DB::UPDATE("UPDATE tblotherinfoforstaffdocumentation SET `qtn1` = '$convicted', `qtn2` = '$convictreason', `qtn3` = '$illness', `qtn4` = '$illness_reason', `qtn5` = '$repay', `qtn6` = '$judgement', `qtn7` = '$judgmentr', `qtn8` = '$detail_in_force', `qtn9` = '$decoration', `qtn10` = '$religion', `qtn11` = '$agree' WHERE `staffid` = '$fileNo'");

            $data['message'] = 'Other information has been saved!';
        } else {
            DB::INSERT("INSERT INTO tblotherinfoforstaffdocumentation (`staffid`,`fileNo`, `qtn1`, `qtn2`, `qtn3`, `qtn4`, `qtn5`, `qtn6`, `qtn7`, `qtn8`, `qtn9`, `qtn10`, `qtn11`) VALUES ('$staffid','$fileNo', '$convicted', '$convictreason', '$illness', '$illness_reason', '$repay', '$judgement', '$judgmentr', '$detail_in_force', '$decoration', '$religion', '$agree')");
            $data['message'] = 'Other information has been saved!';
        }
    }

    public function getValueName($id, $table, $tableId)
    {
        if (!empty($id) && !empty($table)) {
            $result = DB::Table("{$table}")->where("{$tableId}", "{$id}")->first();
            return ($result === null) ? [] : $result;
        } else {
            return DB::select("SELECT '' as 'State' , '' as 'lga'")[0];
        }
    }
    public function fileD($id)
    {
        return DB::table("tblper")->where('ID', $id)->value('fileNo');
    }

    //fill up forms
    public function fillUpForm($userID)
    {
        $getStaffDetails = '';
        if (!empty($userID) or $userID > 0) {
            $userID = Session::get('fileNo');
            if (DB::table('tblper')->where('ID', $userID)->first()) {
                $getStaffDetails  = DB::table('tblper')
                    ->leftjoin('tbl_court', 'tblper.courtID', '=', 'tbl_court.id')
                    ->leftjoin('tbldivision', 'tblper.divisionID', '=', 'tbldivision.divisionID')
                    ->leftjoin('tbldepartment', 'tblper.department', '=', 'tbldepartment.id')
                    ->leftjoin('tbldesignation', 'tblper.grade', '=', 'tbldesignation.grade')
                    ->leftjoin('tbltitle', 'tblper.title', '=', 'tbltitle.ID')
                    ->leftjoin('tblemployment_type', 'tblper.employee_type', '=', 'tblemployment_type.id')
                    ->where('tblper.ID', Session::get('staffID'))
                    ->select('*', 'tbl_court.id as courtID', 'tbldepartment.id as departmentID', 'tbldesignation.designation as designation_name', 'tbltitle.title as title_name')
                    ->first();
            } else {
                $getStaffDetails = '';
            }
        }
        return $getStaffDetails;
    }

    public function RefNo()
    {
        $alphabet = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 6; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        //$Reference= $initcode . implode($pass);
        return implode($pass);
    }

    public function generateNextStaffFileNo()
    {
        // Get all staffFileNo values
        $records = DB::table('tblper')->pluck('fileNo');

        // Extract numeric parts and convert to integers
        $numbers = $records->map(function ($fileNo) {
            preg_match('/(\d+)$/', $fileNo, $matches);
            return isset($matches[1]) ? (int) $matches[1] : 0;
        });

        // Find the max number
        $maxNumber = $numbers->max();

        // Increment by 1
        $nextNumber = $maxNumber + 1;

        // Format with leading zeros (same as existing format)
        $nextNumberFormatted = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // Return new staffFileNo suggestion
        // return "SCN/P/" . $nextNumberFormatted;
        return $nextNumberFormatted;
    }
}
