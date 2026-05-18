<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Library\AnyFileUploadClass;
use App\Library\RandomAlphaNumericClass;
use App\Http\Requests;
use Session;
use File;
use Auth;
use Illuminate\Support\Facades\DB;

class CloseVolumeFileController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->middleware('auth');
                return $next($request);
        });
    }

    //Load page
    public function create()
    {
        try {
            $data['getVolume'] =  DB::table('tblvolume')->get();
            $data['getCloseRecords']        = DB::table('tblclose_volume')->leftJoin('tblvolume', 'tblvolume.ID', '=', 'tblclose_volume.new_volumeID')->orderBy('created_at', 'desc')->paginate(30);
            $data['getOldVolume'] = Session::get('getFile');
            $data['getFileIDSession']    = Session::get('getFileID');
            $data['files']  =  DB::table('tblfiles') //->where('tblfiles.file_category', '=', 2)
                ->leftJoin('tblvolume', 'tblvolume.ID', '=', 'tblfiles.volume')
                ->leftJoin('tblfile_category', 'tblfile_category.Id', '=', 'tblfiles.file_category')
                ->select('*', 'tblfiles.ID as file_ID')
                ->get();
        } catch (\Throwable $e) {
        }
        return view('openRegistry.closeVolume', $data);
    }

    //get volume from file
    public function getVolumeForFile(Request $request)
    {
        Session::forget('getFile');
        Session::forget('getFileID');

        $getFile = DB::table('tblfiles')->where('ID', $request['getFileID'])->first();
        Session::put('getFile',  $getFile);
        Session::put('getFileID', $request['getFileID']);
        return redirect('/open-registry-close-volume');
    }

    //save and upload file
    public function save(Request $request)
    {
        $is_saved = 0;
        $saveVolume = 0;
        $this->validate(
            $request,
            [
                'fileID'     => 'required',
                'oldVolume'       => 'required',
                'newVolume'       => 'required',
            ]
        );
        try {
            if ($request['oldVolume'] == $request['newVolume']) {
                return redirect()->back()->with('danger', 'Sorry you cannot use the same volume for old and new volume. Please, review and try again.');
            }

            //check if volume has been closed before
            $volumeClosed = DB::table('tblclose_volume')->where('fileID', $request['fileID'])->where('old_volumeID', $request['oldVolume'])->first();
            
            if($volumeClosed){
                return back()->with("danger", "Please you have already closed this volume");
            }

            $saveVolume = DB::table('tblfiles')->where('ID', $request['getFileID'])->update([
                'volume' => $request['newVolume']
            ]);

            $is_saved = DB::table('tblclose_volume')->insert(array(
                'fileID'             => $request['fileID'],
                'old_volumeID'       => $request['oldVolume'],
                'new_volumeID'       => $request['newVolume'],
                'staff_name'         => DB::table('tblfiles')->where('ID', $request['fileID'])->value('file_description'),
                'userID'             => (Auth::check() ? Auth::user()->id : null)
            ));
        } catch (\Throwable $e) {
        }
        Session::forget('getFile');
        Session::forget('getFileID');
        if ($is_saved) {
            return redirect('/open-registry-close-volume')->with('message', 'Your record was updated successfully');
        }
        return redirect('/open-registry-close-volume')->with('danger', 'Sorry, an error occurred when processing your record. Please try again.');
    }
}//end class
