<?php

namespace App\Http\Controllers\hr;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;
use Illuminate\Support\Facades\Log;

class DocumentArchivesController extends DatabaseDocumentationController
{
    public function archives() {
        
        $data['archives'] = DB::table('tblclose_volume')
        ->leftjoin('tblfiles','tblclose_volume.fileID','=','tblfiles.ID')
        ->leftjoin('tblvolume','tblclose_volume.old_volumeID','=','tblvolume.ID')
        ->get();
      
        return view('archives.create', $data);
    }

    public function postArchives(Request $request) {
        
        
        // $exists = DB::table('tblarchives')->where('fileID', $request->fileID)->where('old_volumeID',$request->volume_number)->where('shelve_number',$request->shelve_number)->exists();
        // if($exists){
        //     return back()->with('error','Archived Exists');
        // }else {
            $request->validate([
                'shelve_number' => "required"
            ]);
            try {
                $getFileNo = DB::table('tblfiles')->where('ID','=',$request->fileID)->first();
                DB::table('tblarchives')->insert([

                    'fileID' => $request->fileID,
                    'old_volumeID' => $request->volume_number,
                    'shelve_number' => $request->shelve_number,
                    'fileNo'        => $getFileNo->fileNo,
                    'file_description' =>$getFileNo->file_description,
                    'forwardedBy' => Auth::user()->id
                ]);

                //change file to isArchived true
                $archiveBulk = DB::table('tblfiles')
                                    ->where('fileNo', $getFileNo->fileNo)
                                    ->where('volume', $request->volume_number)->update(['isArchived' => 1]);

                return back()->with('message','Successfully Archived');

            } catch (\Throwable $th) {
            //    return back()->with('error', "An error occured");
            }
            
        // }
        
    }

    public function viewArchives() {
        
        $data['archives'] = DB::table('tblarchives')
        ->leftjoin('tblfiles','tblarchives.fileID','=','tblfiles.ID')
        ->leftjoin('tblvolume','tblarchives.old_volumeID','=','tblvolume.ID')
        ->get();
    
        return view('archives.view', $data);
    }

    public function searchArchives(Request $request)
    {
        if(empty($request->fileNo) && empty($request->coachNo)){
            return back()->with('error', 'Please input field for any search criteria');
        }
        if($request->fileNo && $request->coachNo){
            return back()->with('error', 'You must only search by one criteria');
        }
        if($request->fileNo){
            $fileNo = $request->fileNo;
            $getArchive = DB::table('tblarchives')
            ->leftjoin('tblfiles','tblarchives.fileID','=','tblfiles.ID')
            ->leftjoin('tblvolume','tblarchives.old_volumeID','=','tblvolume.ID')
            ->where('tblarchives.fileNo', 'LIKE', "%{$fileNo}%")->orderBy('tblarchives.archivesID', 'DESC')->get();
            return view('archives.searchArchive', [
                'fileNo' => $fileNo,
                'archiveFiles' => $getArchive
            ]);
        }elseif($request->coachNo){
            $coachNo = $request->coachNo;
            $getArchive = DB::table('tblarchives')
            ->leftjoin('tblfiles','tblarchives.fileID','=','tblfiles.ID')
            ->leftjoin('tblvolume','tblarchives.old_volumeID','=','tblvolume.ID')
            ->where('tblarchives.shelve_number', 'LIKE', "%{$coachNo}%")->orderBy('tblarchives.archivesID', 'DESC')->get();
            return view('archives.searchArchive', [
                'coachNo' => $coachNo,
                'archiveFiles' => $getArchive
            ]);
        }
    }

    public function registryToArchive()
    {
        $data['archives'] = DB::table('tblclose_volume')
                                ->leftjoin('tblfiles','tblclose_volume.fileID','=','tblfiles.ID')
                                ->leftjoin('tblvolume','tblclose_volume.old_volumeID','=','tblvolume.ID')
                                ->orderBy('tblclose_volume.colseID', 'desc')
                                ->get();
      
        return view('archives.registryToArchive', $data);
    }

    public function staffToArchive(Request $request)
    {
        //get file id
        $fileID = DB::table('tblfiles')->where('fileNo', $request->fN)->first();

        //close volume when forwarding to archive
        $addCloseVolume = DB::table('tblclose_volume')->insert([
            'fileID' => $fileID->ID,
            'old_volumeID' => $request->fV,
            'new_volumeID' => $request->fV + 1,
            'staff_name' => $fileID->file_description,
            'userID' => Auth::user()->id
        ]);

        //add to archives
        $addArchive = DB::table('tblarchives')->insert([
            'fileID' => $fileID->ID,
            'old_volumeID' => $request->fV,
            'shelve_number' => rand(1,50),
            'fileNo' => $fileID->fileNo,
            // 'file_destination_section' => ,
            'forwardedBy' => Auth::user()->id,
            'file_description' => $fileID->file_description
        ]);

        //update file to is archived
        $updateFileStatus = DB::table('tblfiles')->where(['fileNo' => $request->fN, 'volume' => $request->fV])->update([
            'isArchived' => 1
        ]);

        if($addCloseVolume && $addArchive && $updateFileStatus){
            return response()->json([
                "message" => "successfull"
            ]);
        }else{
            return response()->json([
                "message" => "error occured"
            ]);
        }
        
  
    }

}
