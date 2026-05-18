<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;

use File;

use Session;
use DateTime;
use Carbon\Carbon;
// 
use App\Models\User;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Notifications\SentFile;
use App\Notifications\TrainingNomination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class TrainingController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $data['trainingType'] = DB::table('training_type')->get();

        return view('training.procurement', $data);
    }

    public function viewAllCreated()
    {
        $data['trainings'] = DB::table('tbltraining')->where('date', date('Y'))->orderBy('id', 'DESC')->paginate(20);
        $data['cadres'] = DB::table('training_status')->get();

        //get all cadre except logged in cadre
        $loggedUser = auth()->user()->id;
        $loggedUserRole = DB::table('assign_user_role')->where('userID', $loggedUser)->first();
        // $dept = DB::table('tblper')->where('UserID', $loggedUser)->value('department');
        $data['cadres'] = DB::table('training_status')->where('roleID', '!=', $loggedUserRole->roleID)->get();

        return view('training.viewAllCreated', $data);
    }

    public function createType()
    {
        $getTraining = DB::table('training_type')->get();
        return view('training.createTrainingType', [
            'getTraining' => $getTraining
        ]);
    }

    public function storeTrainingType(Request $request)
    {
        $this->validate($request, [
            'training_type' => 'required', //:tblleave_type, leaveType'
        ]);
        $trainingType = $request->input('training_type');

        $save = DB::table('training_type')->insert([
            'type_name' => $trainingType
        ]);

        return redirect('/training-type')->with('message', 'A new type of training was added successfully.');
    }

    public function updateTrainingType(Request $request)
    {
        $this->validate($request, [
            'typeID' => 'required|numeric',
            'new_training_type' => 'required'
        ]);

        $data['training_type'] = $request->input('new_training_type');
        $typeID     = $request->get('typeID');

        $update = DB::table('training_type')->where('id', $typeID)->update([
            'type_name' => $data['training_type'],
        ]);

        return redirect('/training-type')->with('message', 'training type was successfully Updated.');
    }

    public function deleteTrainingType($id)
    {
        if (DB::table('training_type')->where('id', $id)->first()) {
            $success = DB::table('training_type')->where('id', $id)->delete();
            if ($success) {
                return redirect('/training-type')->with('message', 'Deleted successfully');
            }
        } else {
            return redirect('/training-type')->with('error', 'Sorry, we cannot delete this record. Try again');
        }
        return redirect('/training-type')->with('error', 'Record not found!');
    }

    public function saveTraining(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'training_type' => 'required',
            'venue' => 'required',
            'training_date' => 'date|after:today|required',
            'training_end_date' => 'required',
            'training_time' => 'required',
            'consultant' => 'required',
            'attachment' => 'required|mimes:jpeg,png,jpg,gif,svg,pdf',

        ]);
        $imageName = time() . '.' . $request->attachment->extension();

        $currentDate = date('Y-m-d');
        if ($request->training_date <= $currentDate) {
            return back()->with('err', 'Training date cannot be less than today');
        }

        db::table('tbltraining')->insert(
            [
                'title' => $request->name,
                'date' => date('Y', strtotime($request->training_date)),
                'venue' => $request->venue,
                'training_type' => $request->training_type,
                'training_date' => $request->training_date,
                'training_end_date' => $request->training_end_date,
                'training_time' => $request->training_time,
                'consultant' => $request->consultant,
                'attachment' => $imageName
            ]
        );

        $request->attachment->move(public_path('/trainingAttachment/'), $imageName);
        return redirect(route('viewAllTraining'))->with('success', 'Training Created successfully');
    }

    public function editTraining($id)
    {
        $getTraining = DB::table('tbltraining')->where('ID', $id)->first();
        $trainingType = DB::table('training_type')->get();
        return view('training.editTraining', [
            'getTraining' => $getTraining,
            'trainingType' => $trainingType
        ]);
    }

    public function saveEditTraining(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|max:255',
            'name' => 'required|max:255',
            'type_of_training' => 'required',
            'venue' => 'required',
            'training_date' => 'date|after:today|required',
            'training_end_date' => 'required',
            'training_time' => 'required',
            'consultant' => 'required',

        ]);

        $currentDate = date('Y-m-d');
        if ($request->training_date <= $currentDate) {
            return back()->with('err', 'Training date cannot be less than today');
        }

        if ($request->attachment != null) {
            $file_name = db::table('tbltraining')->where('ID', $request->id)->first();
            $file_name = $file_name->attachment;
            //dd($file_name);
            $imageName = time() . '.' . $request->attachment->extension();
            db::table('tbltraining')->where('ID', $request->id)->update(
                [
                    'title' => $request->name,
                    'date' => date('Y', strtotime($request->training_date)),
                    'training_type' => $request->type_of_training,
                    'venue' => $request->venue,
                    'training_date' => $request->training_date,
                    'training_end_date' => $request->training_end_date,
                    'training_time' => $request->training_time,
                    'consultant' => $request->consultant,
                    'attachment' => $imageName,
                ]
            );
            $file_path = '' . public_path('/trainingAttachment/') . '' . $file_name;

            //1621434303.jpg
            File::delete(public_path('/trainingAttachment/') . $file_name);
            $request->attachment->move(public_path('/trainingAttachment/'), $imageName);
        } else {
            db::table('tbltraining')->where('ID', $request->id)->update(
                [
                    'title' => $request->name,
                    'date' => date('Y', strtotime($request->training_date)),
                    'training_type' => $request->type_of_training,
                    'venue' => $request->venue,
                    'training_date' => $request->training_date,
                    'training_time' => $request->training_time,
                    'consultant' => $request->consultant,

                ]
            );
        }
    
        return redirect(route('viewAllTraining'))->with('success', 'Training Updated successfully');
    }

    public function deleteTraining(Request $request)
    {
        //
        $this->validate($request, [
            'id'   => 'required'
        ]);
        db::table('tbltraining')->where('ID', $request->id)->delete();
        return redirect(route('viewAllTraining'))->with('success', 'Training Deleted successfully');
    }

    public function admin()
    {
        $user =  Auth::user()->id;
        $userDet = db::table('tblaction_stages')->where('tblaction_stages.userID', $user)->first();
        //dd($userDet);
        if (empty($userDet)) {
            return back()->with('success', 'You are not permitted to view this page');
        }
        $data['userDet'] = $userDet;

        //  $user = 3;
        /*$userDet = db::table('tblaction_stages')->where('tblaction_stages.userID',$user)->where(function($query){
        return $query
        ->where('tblaction_stages.action_stageID',13)
        ->orWhere('tblaction_stages.action_stageID',4)
        ->orWhere('tblaction_stages.action_stageID',12);
    })->leftjoin('tblleave_approval_stages','tblaction_stages.action_stageID','=','tblleave_approval_stages.stage')->get();
            if(count($userDet)<1){
                return back();
            }

          $data['userDet'] = $userDet;
          if($userDet[0]->stage==4){
            $trainings = db::table('tbltraining')->where('status',4)->get();
          }
            if($userDet[0]->stage==12){
            $trainings = db::table('tbltraining')->where('status',5)->get();
          }

          else{
        $trainings = db::table('tbltraining')->where('status','>',1)->get();}
        */
        $trainings = db::table('tbltraining')
            ->join('users', 'users.id', '=', 'tbltraining.sent_from')
            ->where('tbltraining.status', $userDet->action_stageID)
            // ->orwhere('tbltraining.status', 1)
            ->orwhere('tbltraining.status', 2)
            // ->orwhere('tbltraining.status', 3)
            // ->orwhere('tbltraining.status', 4)
            // ->orwhere('tbltraining.status', 6)
            ->where('tbltraining.date', date('Y'))
            ->select('tbltraining.*', 'users.*', 'tbltraining.status as tStatus', 'users.id as myUserID')
            ->get();

        //dd($trainings );
        $data['cadres'] = DB::table('training_status')->get();
        $data['trainings'] = $trainings;

        return view('training.admin', $data);
    }
    public function secretary()
    {
        $trainings = db::table('tbltraining')
            ->join('users', 'users.id', '=', 'tbltraining.sent_from')
            ->where('tbltraining.status', 3)
            ->select('tbltraining.*', 'users.*', 'tbltraining.status as tStatus', 'users.id as myUserID')
            ->get();

        $loggedUser = auth()->user()->id;
        $loggedUserRole = DB::table('assign_user_role')->where('userID', $loggedUser)->first();
        $data['cadres'] = DB::table('training_status')->where('roleID', '!=', $loggedUserRole->roleID)->get();
        // $data['cadres'] = DB::table('training_status')->get();

        $data['trainings'] = $trainings;
        // return $data;
        return view('training.secretary', $data);
    }
    public function pushForApproval(Request $request)
    {
        $request->validate([
            'comment' => 'required'
        ]);

        $trainings = db::table('tbltraining')->where('ID', $request->id)->update([
            'status' => 3, //pushes to secretary
        ]);
        $comments = db::table('tbltraining_comments')->insert([
            'comment' => $request->comment,
            'trainingID' => $request->id,
            'status' => 3,
        ]);
        return back()->with('success', 'Forwarded To Secretary For Approval');
    }
    public function pushForTraining(Request $request)
    {
        $request->validate([
            'comment' => 'required'
        ]);

        $trainings = db::table('tbltraining')->where('ID', $request->id)->update([
            'status' => 12,
        ]);
        $comments = db::table('tbltraining_comments')->insert([
            'comment' => $request->comment,
            'trainingID' => $request->id,
            'status' => 12,
        ]);
        return back()->with('success', 'Forwarded To Training Unit For Selection');
    }
    public function directorApproval(Request $request) //now head of training
    {
        $request->validate([
            'comment' => 'required'
        ]);

        $trainings = db::table('tbltraining')->where('ID', $request->id)->update([
            'status' => 4 //updates status to director status or head of training
        ]);

        $comments = db::table('tbltraining_comments')->insert([
            'comment' => $request->comment,
            'trainingID' => $request->id,
            'status' => 4
        ]);
        return back()->with('success', 'Forwarded To Assistant Director For Approval');
    }

    public function directorPage()
    {
        $trainings = db::table('tbltraining')
            ->join('users', 'users.id', '=', 'tbltraining.sent_from')
            ->select('tbltraining.*', 'users.*', 'tbltraining.status as tStatus', 'users.id as myUserID')
            ->where('tbltraining.status', 4)
            ->get();

        $loggedUser = auth()->user()->id;
        $loggedUserRole = DB::table('assign_user_role')->where('userID', $loggedUser)->first();
        $data['cadres'] = DB::table('training_status')->where('roleID', '!=', $loggedUserRole->roleID)->get();

        $data['trainings'] = $trainings;
        return view('training.director', $data);
    }

    public function adminApproval(Request $request)
    {
        $request->validate([
            'cadre' => 'required',
            'comment' => 'required'
        ]);

        $cadre = DB::table('training_status')->where('statusID', $request->cadre)->first();
        $trainings = db::table('tbltraining')->where('ID', $request->id)->update([
            'status' => $request->cadre,
            'Comment' => $request->comment,
            'sent_from' => auth()->user()->id
        ]);

        $comments = db::table('tbltraining_comments')->insert([
            'comment' => $request->comment,
            'trainingID' => $request->id,
            'status' => $request->cadre //cadre status of whom training is sent to and who will see comment
        ]);
        return back()->with('success', "Forwarded To $cadre->cadre For Approval");
    }


    public function secretaryApproval(Request $request) // approves training to finale action stage of 12
    {
        $request->validate([
            'comment' => 'required'
        ]);

        $trainings = db::table('tbltraining')->where('ID', $request->id)->update([
            'approval_status' => 1,
            'status'          => 12, //status 6 approves and complete training
            'sent_from' => auth()->user()->id
        ]);

        $comments = db::table('tbltraining_comments')->insert([
            'comment' => $request->comment,
            'trainingID' => $request->id,
            'status' => 12 //3 but changed to 12 for check later
        ]);
        return back()->with('success', 'Approved');
    }

    public function getComment($id)
    {
        $training_comment = DB::table('tbltraining_comments')->where('trainingID', $id)->get()->last();
        return response()->json([
            'data' => $training_comment
        ]);
    }

    public function revertTrainingToSender(Request $request)
    {
        //to get status cadre of where you want to revert
        $request->validate([
            'comment' => 'required'
        ]);
        $id = $request->get('revertID');
        try {
            $userRole = DB::table('assign_user_role')
                ->join('training_status', 'training_status.roleID', 'assign_user_role.roleID')
                ->select('training_status.*', 'statusID as newTrainingStatus')
                ->where('assign_user_role.userID', $request->userID)->first();
            // dd($userRole->newTrainingStatus);

            $personRevertingID = auth()->user()->id;
            $cadreBeenRevertedTo = $userRole->newTrainingStatus;

            $updateTraining = DB::table('tbltraining')->where('ID', $id)->update([
                'sent_from' => $personRevertingID,
                'status' => $cadreBeenRevertedTo,
                'approval_status' => 0
            ]);

            $updateTrainingComment = DB::table('tbltraining_comments')->insert([
                'trainingID' => $id,
                'comment' => $request->comment
            ]);

            if ($updateTraining && $updateTrainingComment) {
                return back()->with('success', 'You have successfully reverted training');
            }
        } catch (\Throwable $th) {
            //throw $th;
            return back()->with('err', 'could not revert back');
        }
    }

    public function selectStaffDepartment(Request $request, $id)
    {

        $departments = db::table('tbldepartment')->get();
        $batches = db::table('tbltraining_batches')->where('status', '!=', 0)->where('trainingID', $id)->get();
        $data['batches'] = $batches;
        $currentBatch = db::table('tbltraining_batches')->where('status', 0)->where('trainingID', $id)->first();

        if ($currentBatch == null) {
            $currentBatch = "";
        } else {
            $currentBatch = $currentBatch->batchID;
        }
        $data['departments'] = $departments;
        $cTraining = db::table('tbltraining')->where('ID', $id)->get();
        $data['cTraining'] = $cTraining;
        $data['currentTraining'] = $id;
        $data['trainingStatus'] = $cTraining[0]->status;

        $trainings = db::table('tbltraining_staff')
            ->leftjoin('tblper', 'tbltraining_staff.staffID', '=', 'tblper.ID')
            ->leftjoin('tbldepartment', 'tbltraining_staff.deptID', '=', 'tbldepartment.id')
            ->where('tbltraining_staff.trainingID', $id)
            // ->where('tbltraining_staff.deptID', $request->department)
            ->where('tbltraining_staff.batchID', $currentBatch)
            ->select('tbltraining_staff.*', 'tbldepartment.department as Dept', 'tblper.first_name', 'tblper.surname', 'tblper.othernames', 'tblper.grade')
            ->get();

        $data['trainings'] = $trainings;
        if ($request->department != null) {
            $request->session()->flash('department', $request->department);
            // dd(Session::get('department'));
            $staffs = db::table('tblper')
                // ->leftjoin('tbldepartment', 'tblper.department', '=', 'tbldepartment.id')
                ->join('tbldesignation', 'tblper.Designation', '=', 'tbldesignation.id')
                ->where('tblper.department', $request->department)
                ->select('tblper.*', 'tbldesignation.designation')
                ->get();

            foreach ($staffs as $staff) {
                $check = db::table('tbltraining_staff')->where('staffID', $staff->ID)->where('trainingID', $id)->get();
                if (count($check) > 0) {
                    $staff->selected = 1;
                } else {
                    $staff->selected = 0;
                }
            }

            $data['staffs'] = $staffs;
        }

        // return $data;
        //  Auth::user()->notify(new SentFile($discipline,"/discipline","Discipline"));
        // Auth::user()->notify(new SentFile($data,"check-nominated-training/".Auth::user()->id,"Training"));

        return view('training.selectStaff', $data);
    }
    public function batchPortions($id)
    {
        $batch = db::table('tbltraining_batches')->join('tbltraining', 'tbltraining.ID', 'tbltraining_batches.batchID')->where('batchID', $id)->first();
        $trainings = db::table('tbltraining_staff')->leftjoin('tblper', 'tbltraining_staff.staffID', '=', 'tblper.ID')
            ->leftjoin('tbldepartment', 'tbltraining_staff.deptID', '=', 'tbldepartment.id')
            ->where('tbltraining_staff.batchID', $batch->batchID)
            ->select(
                'tbltraining_staff.*',
                'tblper.first_name',
                'tblper.surname',
                'tblper.othernames',
                'tblper.grade',
                'tbldepartment.department as departmentName'
            )
            ->get();
        $data['trainings'] = $trainings;
        $data['batch'] = $batch;
        return view('training.batchView', $data);
      //  dd($trainings);
    }
    public function deSelectStaff(Request $request)
    {

        $this->validate($request, [
            'ID'   => 'required'
        ]);
        db::table('tbltraining_staff')->where('ID', $request->ID)->delete();
        return back()->with('success', 'Staff Removed successfully');
    }
    public function selectStaff(Request $request)
    {
        $this->validate($request, [
            'trainingID'   => 'required',
            'staffID'   => 'required',
            'department' => 'required'
        ]);
        $batch = db::table('tbltraining_batches')->where('trainingID', $request->trainingID)
            ->where('status', 0)->first();
        if ($batch != null) {
            $batchID = $batch->batchID;
        } else {
            $batchID = db::table('tbltraining_batches')->insertGetID([
                'trainingID' => $request->trainingID
            ]);
            // dd($batchID);
        }
       $data= db::table('tbltraining_staff')->insertGetId([
            'staffID' => $request->staffID,
            'trainingID' => $request->trainingID,
            'deptID'    => $request->department,
            'batchID'   => $batchID,
            'staff_status' => 1,
        ]);

        // $training=db::table('tbltraining')->where('id',$request->trainingID)->first();
        // // dd($data);
        // $loggedPer = db::table('tblper')->where('ID', $request->staffID)->first();
        // $loggedUser = User::where('ID', $loggedPer->UserID)->first();
        // //  dd($loggedUser);
        // $loggedUser->notify(new TrainingNomination($training,"check-nominated-training/".$loggedUser->id));

        return back()->with('success', 'Staff Added successfully');
    }
    public function concludeTraining(Request $request) //now for nomination
    {
        $this->validate($request, [
            'id'   => 'required',
            'comment' => 'required',
            // 'report' => 'required',
            // 'attachment' => 'required|mimes:jpeg,png,jpg,gif,svg,pdf',
        ]);
        $batch = db::table('tbltraining_batches')->where('trainingID', $request->id)
            ->where('status', 0)->update([
                'status' => 1,
                // 'date_concluded' => $request->date
            ]);
        $batch = db::table('tbltraining_batches')->where('trainingID', $request->id)
            ->where('status', 3)->update([
                'status' => 0,
            ]);
        // $imageName = time() . '.' . $request->attachment->extension();


        // $request->attachment->move(public_path('/trainingAttachment/'), $imageName);
        db::table('tbltraining')->where('ID', $request->id)->update([
            'status' => 12,
            // 'date_concluded' => $request->date,
            // 'attendance_attachment' => $imageName,
            'Comment' => $request->comment,
            // 'Report' => $request->report
        ]);

        $data= db::table('tbltraining_staff')->where('trainingID', $request->id)->get();
        foreach($data as $d){
            $training=db::table('tbltraining')->where('id',$request->id)->first();
            $loggedPer = db::table('tblper')->where('ID', $d->staffID)->first();
            $loggedUser = User::where('ID', $loggedPer->UserID)->first();
    //  dd($loggedUser);
            $loggedUser->notify(new TrainingNomination($training,"check-nominated-training/".$loggedUser->id,"You have been nominated for"));


        }


        return back()->with('success', 'Nomination was successful');
    }

    public function completeTrainingPage()
    {
        $today = date('Y-m-d');
        $doneTraining = DB::table('tbltraining')
                            // ->where('tbltraining.training_end_date', '<', $today)
                            ->where('tbltraining.approval_status', 1)
                            ->where('tbltraining.status', 12)
                            ->orderByDesc('tbltraining.ID')
                            ->get();

        $loggedUser = auth()->user()->id;
        $loggedUserRole = DB::table('assign_user_role')->where('userID', $loggedUser)->first();
        $cadres = DB::table('training_status')->where('roleID', '!=', $loggedUserRole->roleID)->get();

        return view('training.completeTraining', [
            'trainings' => $doneTraining,
            'cadres' => $cadres
        ]);
    }

    public function completeTrainingAndReport(Request $request)
    {
        $this->validate($request, [
            'id'   => 'required',
            'comment' => 'required',
            'report' => 'required',
            'attachment' => 'required|mimes:jpeg,png,jpg,gif,svg,pdf',
        ]);
        $batch = db::table('tbltraining_batches')->where('trainingID', $request->id)
            ->where('status', 0)->update([
                'status' => 1,
                'date_concluded' => $request->date
            ]);
        $imageName = time() . '.' . $request->attachment->extension();


        $request->attachment->move(public_path('/trainingAttachment/'), $imageName);
        db::table('tbltraining')->where('ID', $request->id)->update([
            // 'status' => 12,
            'date_concluded' => $request->date,
            'attendance_attachment' => $imageName,
            'Comment' => $request->comment,
            'Report' => $request->report,
        ]);

        return back()->with('success', 'Training Concluded');
    }

    public function forwardTrainingReport(Request $request)
    {
        $request->validate([
            'comment' => 'required'
        ]);

            $update = db::table('tbltraining')->where('ID', $request->id)->update([
                'report_from' => auth()->user()->id,
                'report_status' => $request->cadre,
                'comment' => $request->comment
            ]);

        $cadre = DB::table('training_status')->where('statusID', $request->cadre)->first();
        return back()->with('success', "Forwarded to $cadre->cadre");

    }

    public function reportHeadAdmin()
    {
        $trainings = db::table('tbltraining')
            ->join('users', 'users.id', '=', 'tbltraining.report_from')
            ->select('tbltraining.*', 'users.*', 'tbltraining.report_status as tStatus', 'users.id as myUserID')
            ->where('tbltraining.report_status', 2)
            ->get();

        $loggedUser = auth()->user()->id;
        $loggedUserRole = DB::table('assign_user_role')->where('userID', $loggedUser)->first();
        $data['cadres'] = DB::table('training_status')->where('roleID', '!=', $loggedUserRole->roleID)->get();

        $data['trainings'] = $trainings;
        return view('training.reportHeadAdmin', $data);
    }

    public function reportHeadTraining()
    {
        $trainings = db::table('tbltraining')
            ->join('users', 'users.id', '=', 'tbltraining.report_from')
            ->select('tbltraining.*', 'users.*', 'tbltraining.report_status as tStatus', 'users.id as myUserID')
            ->where('tbltraining.report_status', 4)
            ->get();

        $loggedUser = auth()->user()->id;
        $loggedUserRole = DB::table('assign_user_role')->where('userID', $loggedUser)->first();
        $data['cadres'] = DB::table('training_status')->where('roleID', '!=', $loggedUserRole->roleID)->get();

        $data['trainings'] = $trainings;
        return view('training.reportHeadTraining', $data);
    }

    public function reverseConcludeTraining(Request $request)
    {
        $this->validate($request, [
            'id'   => 'required'
        ]);
        $batch = db::table('tbltraining_batches')->where('batchID', $request->id)->first();
        $trainingID = $batch->trainingID;
        $check = db::table('tbltraining_batches')->where('trainingID', $trainingID)->where('status', 3)->get();
        if (count($check) > 0) {
            return back()->with('error', 'You currently have a training in hold');
        }
        db::table('tbltraining_batches')->where('trainingID', $trainingID)
            ->where('status', 0)->update([
                'status' => 3
            ]);
        db::table('tbltraining_batches')->where('batchID', $request->id)->update([
            'status' => 0
        ]);

        $data= db::table('tbltraining_staff')->where('batchID', $request->id)->get();
        foreach($data as $d){
            $training=db::table('tbltraining')->where('id',$d->trainingID)->first();
            $loggedPer = db::table('tblper')->where('ID', $d->staffID)->first();
            $loggedUser = User::where('ID', $loggedPer->UserID)->first();
    //  dd($loggedUser);
            $loggedUser->notify(new TrainingNomination($training,"check-nominated-training/".$loggedUser->id,"You have been removed from"));
        }


        $location = '/training-staff-department/' . $trainingID;
        return redirect($location)->with('success', 'Batch Conclusion Reversed');
    }
    public function getStaff()
    {

    }

    public function trainingReport()
    {
        return view('training.trainingReport');
    }

    public function getReport()
    {
        $allTraining = db::table('tbltraining')->where('approval_status', 1)->get();

        return response()->json([
            'data' => $allTraining
        ]);
    }

    public function searchReportByTitle($title)
    {
        $byTitle = db::table('tbltraining')->where('title', 'LIKE', "%{$title}%")->get();

        if ($byTitle) {
            return response()->json([
                'status' => 200,
                'byTitle' => $byTitle
            ]);
        } else {
            return response()->json([
                'msg' => 'No training was found'
            ]);
        }

    }

    public function searchReportByYear($year)
    {
        $byYear = db::table('tbltraining')->where('date', $year)->where('date_concluded', '!=', null)->get();

        if ($byYear) {
            return response()->json([
                'status' => 200,
                'byYear' => $byYear
            ]);
        } else {
            return response()->json([
                'msg' => 'No training was found'
            ]);
        }
    }

    public function generateReportByID($id)
    {
        $title = db::table('tbltraining')->where('ID', $id)->first();

        $getStaffs = db::table('tbltraining_staff')
            // ->join('tbltraining', 'tbltraining.ID', '=', 'tbltraining_staff.trainingID')
            ->join('tbldepartment', 'tbldepartment.id', '=', 'tbltraining_staff.deptID')
            ->join('tblper', 'tblper.id', '=', 'tbltraining_staff.staffID')
            ->select('tbldepartment.*', 'tblper.*', 'tbldepartment.department as dept', 'tblper.surname as sname', 'tblper.first_name as fname', 'tblper.othernames as others')
            ->where('trainingID', $id)
            ->get();

        return view('training.trainingReportPage', [
            'staffs' =>  $getStaffs,
            'title' => $title
        ]);
    }

    public function getStaffNominatedTraining($user)
    {
        $loggedUser = db::table('tblper')->where('UserID', $user)->first();

        try {
            $nominated = db::table('tbltraining_staff')
                ->join('tbltraining', 'tbltraining.ID', '=', 'tbltraining_staff.trainingID')
                ->where('tbltraining_staff.staffID', '=', $loggedUser->ID)
                ->where('tbltraining.date_concluded', '=', null)
                ->get();
            return view('training.showMyNominatedTraining', [
                'nominated' => $nominated,
                'loggedUser' => $loggedUser
            ]);
        } catch (\Throwable $th) {
            abort(404);
        }

    }

    public function nominationLetter($trainingID, $userID)
    {
        $getTraining = DB::table('tbltraining')->where('ID', $trainingID)->first();
        $getUser = DB::table('tblper')->where('ID', $userID)->first();
        return view('training.nominationLetter', [
            'training' => $getTraining,
            'user'  => $getUser
        ]);

    }
}
