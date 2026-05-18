<?php
//
namespace App\Http\Controllers\hr;

use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use Carbon\Carbon;
use Auth;
use DB;
use file;

class EducationController extends ParentController
{
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->middleware(function ($request, $next) {
            // $this->division    = $request->session()->get('division');
        // $this->divisionID  = $request->session()->get('divisionID');
        $this->division = Session::get('division');
            return $next($request);
                    return $next($request);
        });
    });
    }

    public function index($staffid = Null)
    { //->
        //check if parameters are Null
        if (is_null($staffid)) {
            return redirect('profile/details');
        }
        $data['staffid'] = $staffid;
        if (!(DB::table('tblper')->where('ID', '=', $staffid)->first())) {
            return redirect('profile/details');
        } else {
            Session::put('staffid', $staffid); //set session
            $getStaff = DB::table('tblper')->where('ID', '=', $staffid)->first();
            if ((DB::table('tbleducations')->where('staffid', '=', $staffid)->first())) {
                $data['details']            = "";
                $data['educationList']        = DB::table('tbleducations')
                    ->where('tbleducations.staffid', '=', $staffid)
                    ->join('tblper', 'tblper.ID', '=', 'tbleducations.staffid')
                    ->get();
                $data['qualificationList']    = DB::table('tblqualification')
                    ->where('active', '=', 1)
                    ->get();
                $data['getStaff']             = $getStaff;
                return view('hr.Education.create', $data);
            } else {
                $data['details']            = "";
                $data['educationList']        = "";
                $data['qualificationList']    = DB::table('tblqualification')
                    ->where('active', '=', 1)
                    ->get();
                $data['getStaff']             = $getStaff;
                return view('hr.Education.create', $data);
            }
        }
    } //->


    public function view($id = Null)
    {

        $staffid = Session::get('staffid');
        $getStaff = DB::table('tblper')->where('ID', '=', $staffid)->first();
        if (is_null($id)) {
            $data['getStaff']             = $getStaff;
            return redirect('/education/create');
        } else {

            if ((DB::table('tbleducations')->where('staffid', '=', $staffid)->first())) {
                $data['details']            = DB::table('tbleducations')->where('id', $id)->first();
                $data['educationList']        = DB::table('tbleducations')
                    ->where('tbleducations.staffid', '=', $staffid)
                    ->join('tblper', 'tblper.ID', '=', 'tbleducations.staffid')
                    ->get();
                $data['qualificationList']    = DB::table('tblqualification')
                    ->where('active', '=', 1)
                    ->get();
                $data['getStaff']             = $getStaff;
                $data['staffid'] = $staffid;

                return view('hr.Education.create', $data);
            } else {
                $data['details']            = "";
                $data['childrenList']        = "";
                $data['getStaff']             = $getStaff;
                $data['staffid'] = $staffid;
                $data['qualificationList']    = DB::table('tblqualification')
                    ->where('active', '=', 1)
                    ->get();
                return view('hr.Education.create', $data);
            }
        }
    }


    public function delete($id = Null)
    {
        $staffid = Session::get('staffid');
        if (is_null($staffid)) {
            return redirect('/education/create/' . $staffid);
        }
        //delete
        DB::table('tbleducations')->where('id', '=', $id)->where('staffid', '=', $staffid)->delete();
        $this->addLog('Education details deleted: ' . $staffid);
        return redirect('/education/create/' . $staffid)->with('msg', 'Education record was deleted successfully');
    }


    public function store(Request $request)
    {
        $staffid = Session::get('staffid');
        if (is_null($staffid)) {
            return redirect('profile/details');
        }
        $this->validate(
            $request,
            [
                'degreeQualification'   => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
                'schoolAttended'        => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
                'schoolFrom'              => 'required|date',
                'schoolTo'                 => 'required|date',
                'certificateHeld'        => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
                'checkedEducation'      => 'required|regex:/^[A-Za-z0-9\-! ,\'\"\/@\.:\(\)]+$/',
                'document'              => 'image|mimes:png,jpg,jpeg,gif,pdf|max: 4000',
            ]
        );
        $degreeQualification        = trim($request['degreeQualification']);
        $schoolAttended             = trim($request['schoolAttended']);
        $schoolFrom                      = trim($request['schoolFrom']);
        $schoolTo                     = trim($request['schoolTo']);
        $certificateHeld             = trim($request['certificateHeld']);
        $checkedEducation           = trim($request['checkedEducation']);
        $file                          = $request['document'];
        $id                             = trim($request['id']);
        $date                           = date("Y-m-d");
        //Update if hidden Name/id NOT empty
        if ($id <> "") {
            DB::table('tbleducations')->where('id', $id)->where('staffid', $staffid)->update(array(
                'degreequalification'   => $degreeQualification,
                'schoolattended'        => $schoolAttended,
                'schoolfrom'               => $schoolFrom,
                'schoolto'              => $schoolTo,
                'certificateheld'       => $certificateHeld,
                'checkededucation'      => $checkedEducation,
                'updated_at'            => $date
            ));
            $recordSaved = $id;
            $logMessage = 'Education details updated';
            $message = 'Education details updated successfully';
        } else {
            //insert if hidden Name/id is empty (but directly updating record)
            $recordSaved = DB::table('tbleducations')->insertGetId(array(
                'staffid'                => $staffid,
                'degreequalification'   => $degreeQualification,
                'schoolattended'        => $schoolAttended,
                'schoolfrom'               => $schoolFrom,
                'schoolto'              => $schoolTo,
                'certificateheld'       => $certificateHeld,
                'checkededucation'      => $checkedEducation,
                'created_at'            => $date,
                'updated_at'            => $date
            ));
            $logMessage = 'Education details created';
            $message = 'Education record created successfully';
        }
        //
        //upload document
        if ((($file && $recordSaved) || ($recordSaved != "")) && ($file != Null || $file != "")) {
            $originalExtension   = $file->getClientOriginalExtension();
            $imageNewName        = $staffid . '-' . rand() . '.' . $originalExtension;
            $path                = base_path() . '/public/document/';
            //delete old file if user tends to update his/her records
            if ($id <> "") {
                $oldName = DB::table('tbleducations')->where('staffid', $staffid)->where('id', $recordSaved)->select('document')->first();
                $oldFileName = $oldName->document;
                /*if((File::exists($path . $oldFileName))) //check folder
				{
					File::delete($path . $oldFileName);
				}*/
            }
            if ($file->move($path, $imageNewName)) {
                DB::table('tbleducations')->where('staffid', $staffid)->where('id', $recordSaved)->update(array(
                    'document'               => $imageNewName
                ));
                $this->addLog($message . ' and document was uploaded');
                return redirect('/education/create/' . $staffid)->with('msg', $message . ' and document was uploaded');
            } else {
                return redirect('/education/create/' . $staffid)->with('err', $message . ' but document was NOT uploaded');
            }
        }

        $this->addLog($logMessage . ' with Staff ID ' . $staffid);
        return redirect('/education/create/' . $staffid)->with('msg', $message);
    }


    //Education Report
    public function report($staffid = null)
    {
        if (is_null($staffid)) {
            return redirect('profile/details');
        }
        if (!(DB::table('tblper')->where('ID', '=', $staffid)->first())) {
            return redirect('profile/details');
        } else {
            $data['staffFullDetailsEducation'] = DB::table('tbleducations')
                ->where('staffid', '=', $staffid)
                ->orderBy('id', 'Desc')
                ->get();
            $data['staffFullDetails'] = DB::table('tblper')
                ->join('tbldivision', 'tbldivision.divisionID', '=', 'tblper.divisionID')
                ->join('tblbanklist', 'tblbanklist.bankID', '=', 'tblper.bankID')
                ->where('tblper.ID', '=', $staffid)
                ->first();
        }
        return view('Report.EducationReport', $data);
    }
}
