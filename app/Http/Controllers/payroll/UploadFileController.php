<?php

namespace App\Http\Controllers\payroll;

use Illuminate\Http\Request;

use App\Http\Requests;

use DB;
use Session;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;

class UploadFileController extends ParentController
{

    public function CourtInfo()
    {
        $List = DB::Select("SELECT * FROM `tblsole_court`");
        return $List[0];
    }

    public function showUploadFilePage(Request $request)
    {

        $data['year']       =   $request->input('year');
        $data['month']      =   $request->input('month');
        $data['divisionID'] =   $request->input('division');

        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $data['error_new'] = "";

        $data['CourtInfo'] = $this->CourtInfo();

        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }

        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        // Session::forget('year');
        // Session::forget('month');
        // Session::forget('divsession');

        $data['fileTypes']  = DB::table('tblaiefileuploadtype')->get();

        $data['courtDivisions']  = DB::table('tbldivision')->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);

        if (!Auth::user()->is_global) {
            $division  = Auth::user()->divisionID;
        } else {
            $division = $data['curDivision']->divisionID;
        }

        $data['uploads'] = $this->UploadFile($data['month'], $data['year'], $division);

        return view('fileUpload.fileUpload', $data);
    }

     public function UploadFile($month, $year, $division)
    {

        if ($division) {
            $List = DB::table('tblupload_file')
                ->leftjoin('tbldivision', 'tblupload_file.divisionID', '=', 'tbldivision.divisionID')
                ->leftjoin('tblaiefileuploadtype', 'tblupload_file.aieTypeID', '=', 'tblaiefileuploadtype.id')
                ->where('tblupload_file.month', $month)
                ->where('tblupload_file.year', $year)
                ->where('tblupload_file.divisionID', $division)
                ->orderBy('tblupload_file.divisionID', 'Asc')
                ->get();
        } else {
            $List = DB::table('tblupload_file')
                ->leftjoin('tbldivision', 'tblupload_file.divisionID', '=', 'tbldivision.divisionID')
                ->leftjoin('tblaiefileuploadtype', 'tblupload_file.aieTypeID', '=', 'tblaiefileuploadtype.id')
                ->where('tblupload_file.month', $month)
                ->where('tblupload_file.year', $year)
                ->orderBy('tblupload_file.divisionID', 'Asc')
                ->get();
        }

        return $List;
    }

    public function curDivision($userId)
    {
        $currentDivision = DB::table("users")
            ->join('tbldivision', 'tbldivision.divisionID', '=', 'users.divisionID')
            ->where('users.id', '=', $userId)
            ->select('tbldivision.division', 'tbldivision.divisionID')
            ->first();
        return $currentDivision;
    }

    public function RetrieveFileUpload(Request $request)
    {
        $data['error'] = "";
        $data['warning'] = "";
        $data['success'] = "";
        $data['error_new'] = "";

        $data['year']       =   $request->input('year');
        $data['month']      =   $request->input('month');
        $data['divisionID'] =   $request->input('division');
        $data['court']      =   $request->input('court');

        $data['fileTypes']  = DB::table('tblaiefileuploadtype')->get();
        $courtName = DB::table('tbl_court')->where('id', '=', $data['court'])->first();
        // $data['courtName'] = $courtName->court_name;

        Session::put('year', $data['year']);
        Session::put('month', $data['month']);
        Session::put('divsession', $data['divisionID']);
        // Session::forget('year');
        // Session::forget('month');
        // Session::forget('divsession');
        // Session::forget('description');


        $data['courtDivisions']  = DB::table('tbldivision')
            ->where('courtID', '=', $data['court'])
            ->get();

        $data['CourtInfo'] = $this->CourtInfo();

        if ($data['CourtInfo']->courtstatus == 0) {
            $request['court'] = $data['CourtInfo']->courtid;
        }
        if ($data['CourtInfo']->divisionstatus == 0) {
            $request['division'] = $data['CourtInfo']->divisionid;
        }

        $data['courtDivisions']  = DB::table('tbldivision')->get();
        $data['curDivision'] = $this->curDivision(Auth::user()->id);
        $data['uploads'] = $this->UploadFile($data['month'], $data['year'], $data['divisionID']);

        if (isset($_POST['upload_aie'])) {
            $data['error'] = "";
            $data['warning'] = "";
            $data['success'] = "";
            $data['error_new '] = "";

            $this->validate($request, [
                'aieTypeID' => 'required',
                'year' => 'required',
                'month' => 'required',
                // 'upload' => 'required|mimes:jpeg,png,gif,svg,pdf|max:20000',
                'upload' => 'mimes:jpeg,png,gif,svg,pdf|max:20000',
            ], [
                // 'upload.required' => 'pls select a file',
                'upload.max' => 'file size is more than 20gb'
            ]);

            $aieTypeID    = trim($request['aieTypeID']);
            $year           = trim($request['year']);
            $month          = trim($request['month']);
            $division       = trim($request['division']);

            // $description    = $request->old('description');
            // $year           = $request->old('year');
            // $month          = $request->old('month');
            // $division       = $request->old('division');

            Session::put('addyear', $year);
            Session::put('addmonth', $month);
            Session::put('adddivsession', $division);
            Session::put('adddescription', $aieTypeID);

            $isExist = DB::table('tblupload_file')
                ->where('divisionID', $division)
                ->where('month', $month)
                ->where('year', $year)
                ->where('aieTypeID', $aieTypeID)
                ->exists();

            if ($isExist) {
                $data['warning'] = "The File already exist for $month $year!";
                return view('fileUpload.fileUpload', $data);
            }

            if ($request->hasFile('upload')) {
                // The file was chosen, upload it
                Session::forget('addyear');
                Session::forget('addmonth');
                Session::forget('adddivsession');
                Session::forget('adddescription');

                $upload_file = $request->file("upload");
                $name_gen = hexdec(uniqid()) . '.' . $upload_file->getClientOriginalExtension();
                $save_url = 'document/kyc/' . $name_gen;
                DB::table('tblupload_file')->insert([
                    'aieTypeID' => $aieTypeID,
                    'year' => $year,
                    'month' => $month,
                    'divisionID' => $division,
                    'upload' => $save_url,
                    'upload_date' => Carbon::now(),
                ]);

                $location = env('UPLOAD_PATH').'document/kyc';
                $move = $request->file('upload')->move($location, $name_gen);

                $data['CourtInfo'] = $this->CourtInfo();
                if ($data['CourtInfo']->courtstatus == 0) {
                    $request['court'] = $data['CourtInfo']->courtid;
                }
                if ($data['CourtInfo']->divisionstatus == 0) {
                    $request['division'] = $data['CourtInfo']->divisionid;
                }
                $data['fileTypes']  = DB::table('tblaiefileuploadtype')->get();
                $data['courtDivisions']  = DB::table('tbldivision')->get();
                $data['curDivision'] = $this->curDivision(Auth::user()->id);
                if (!Auth::user()->is_global) {
                    $division  = Auth::user()->divisionID;
                }
                $data['uploads'] = $this->UploadFile($month, $year, $division);

                $data['success'] = "File Uploaded successfully!";
                return view('fileUpload.fileUpload', $data);
            } else {
                $data['error_new'] = "please choose file!";
                return view('fileUpload.fileUpload', $data);
            }
        }


        if (isset($_POST['delete_upload'])) {

            $id         =   $request->id;
            $year       =   $request->input('year');
            $month      =   $request->input('month');
            $division   =   $request->input('division');
            $data['error'] = "";
            $data['warning'] = "";
            $data['success'] = "";
            $data['error_new'] = "";

            $List = DB::table('tblpayment_consolidated')
                ->where('tblpayment_consolidated.month', $month)
                ->where('tblpayment_consolidated.year', $year)
                ->where('tblpayment_consolidated.divisionID', $division)
                ->first();

            if ($List) {
                if ($List->vstage > 1) {
                    $data['error_new'] = "You cannot delete this File!";
                    return view('fileUpload.fileUpload', $data);
                }
            }

            $image = DB::table('tblupload_file')->where('fileID', $id)->get();

            $data['fileTypes']  = DB::table('tblaiefileuploadtype')->get();
            DB::table('tblupload_file')->where('fileID', $id)->delete();

            $data['CourtInfo'] = $this->CourtInfo();

            Session::put('year', $year);
            Session::put('month', $month);
            Session::forget('divsession');

            if ($data['CourtInfo']->courtstatus == 0) {
                $request['court'] = $data['CourtInfo']->courtid;
            }

            if ($data['CourtInfo']->divisionstatus == 0) {
                $request['division'] = $data['CourtInfo']->divisionid;
            }

            $data['courtDivisions']  = DB::table('tbldivision')->get();
            $data['curDivision'] = $this->curDivision(Auth::user()->id);

            if (!Auth::user()->is_global) {
                $divisionId  = Auth::user()->divisionID;
            }
            $data['uploads'] = $this->UploadFile($month, $year, $divisionId);
            $data['success'] = "File deleted successfully!";
            return view('fileUpload.fileUpload', $data);
        }

        return view('fileUpload.fileUpload', $data);
    }
};
