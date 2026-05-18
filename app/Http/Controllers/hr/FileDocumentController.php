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

class FileDocumentController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
        $this->middleware('auth');
        $this->docFolder = "documents/";
                return $next($request);
        });
    }

    //Load page
    public function create()
    {
        try {
            $data['userIDSelected'] = Session::get('getUserID');
            $data['docPath']        = $this->downloadPath() . $this->docFolder;
            $data['files']          =  DB::table('tblfiles') //->where('tblfiles.file_category', '=', 2)
                ->leftJoin('tblvolume', 'tblvolume.ID', '=', 'tblfiles.volume')
                ->leftJoin('tblfile_category', 'tblfile_category.Id', '=', 'tblfiles.file_category')
                ->select('*', 'tblfiles.ID as file_ID', 'tblfiles.fileNo as fileNo')
                ->get();

            $data['getFileDocs']    =  DB::table('file_document')
                ->leftJoin('tblfiles', 'tblfiles.ID', '=', 'file_document.fileID')
                ->leftJoin('tblvolume', 'tblvolume.ID', '=', 'file_document.volumeID')
                ->leftJoin('tblfile_category', 'tblfile_category.Id', '=', 'tblfiles.file_category')
                ->select('*', 'file_document.ID as file_doc_ID')
                ->orderBy('file_document.ID', 'Desc')
                ->where('file_document.fileID', $data['userIDSelected'])
                ->get();

        } catch (\Throwable $e) {}

        return view('UploadDocument.fileDocument', $data);
    }

    //save and upload file
    public function saveFile(Request $request)
    {
        $is_saved = 0;
        $this->validate(
            $request,
            [
                'fileID'     => 'required',
                'file'       => 'required',
            ]
        );
        try {
            if ($request->hasFile('file')) {
                $newFileName = $this->documentUploadFile($file = $request['file'], $folder = $this->docFolder);
                $is_saved = DB::table('file_document')->insert(array(
                    'fileID'                    => $request['fileID'],
                    'volumeID'                  => DB::table('tblfiles')->where('ID', $request['fileID'])->value('volume'),
                    'document_part'             => $newFileName,
                    'document_description'      => $request['description'],
                    'userID'                    => (Auth::check() ? Auth::user()->id : null)
                ));
            }
        } catch (\Throwable $e) {
        }
        if ($is_saved) {
            return redirect('/document-file-upload')->with('message', 'Your record was saved successfully');
        }
        return redirect('/document-file-upload')->with('danger', 'Sorry, an error occurred when processing your record. Please try again.');
    }

    //search for a file
    public function searchFile()
    {
        $fileData = DB::table('tblfiles')->get();
        return view('UploadDocument.fileSearch', compact('fileData'));
    }

    public function getSearchedFile(Request $request, $id)
    {
        $getFileDocuments = DB::table('tblfiles')
                        ->join('file_document', 'file_document.fileID', '=', 'tblfiles.ID')
                        ->join('tblfile_category', 'tblfile_category.Id', '=', 'tblfiles.file_category')
                        ->select('*','file_document.ID as documentID','file_document.volumeID as documentVolumeID')
                        ->where('fileNo', $id)
                        ->get();

        return response()->json(['data' => $getFileDocuments]);
    }

    //remove document
    public function removeDocument($id)
    {
        $getDocument = DB::table('file_document')->where('ID', $id)->delete();
        return back()->with('message', 'You just deleted a document');
    }

    public function rmDocument($id)
    {
        $getDoc = DB::table('file_document')->where('ID', $id)->delete();
        if($getDoc){
            return response()->json([
                'message' => 'successfully deleted document'
            ]);
        }

    }

    //edit
    public function editDocument($id)
    {
        $getVolume =  DB::table('tblvolume')->get();
        $getDocument = DB::table('file_document')
            ->leftjoin('tblfiles', 'tblfiles.ID', '=', 'file_document.fileID')
            ->select('*', 'tblfiles.fileNo as fileNo', 'tblfiles.file_category as fileCat', 'file_document.ID as documentID')
            ->where('file_document.ID', $id)
            ->first();

        return view('UploadDocument.editDocument', [
            'getDocument' => $getDocument,
            'getVolume' => $getVolume
        ]);
    }

    public function updateDocument(Request $request, $id)
    {
        request()->validate([
            'fileDescription' => 'required',
            'volume' => 'required'
        ]);

        $updateDocument = DB::table('file_document')->where('ID', $id)->update([
            'document_description' => $request->fileDescription,
            'volumeID' => $request->volume
        ]);
        if($updateDocument){
            return redirect('/document-file-upload')->with('message', "You have updated a file document");
        }
    }

    //Delete file
    public function delete($recordID = "null")
    {
        //try{
        $getFile = DB::table('file_document')->where('ID', $recordID)->first();
        if ($getFile) {
            // Delete a single file
            File::delete($this->downloadPath() . $this->docFolder . $getFile->document_part);
            DB::table('file_document')->where('ID', $recordID)->delete();
            return redirect()->back()->with('success', 'Your record was deleted successfully.');
        }
        //}catch(\Throwable $e){}

        return redirect()->back()->with('error', 'Sorry, record not found!');
    }


    //Return Array of String/Numeric - Reuseable Image File and Upload other Module
    public function uploadAnyFile($file = null, $uploadCompletePathName = null, $maxFileSize = 10, $newExtension = null, $newRadFileName = true)
    {
        $data = new AnyFileUploadClass($file, $uploadCompletePathName, $maxFileSize, $newExtension, $newRadFileName);
        return $data->return();
    } //end function


    //Return String - Upload Path
    public function uploadPath()
    {
        return env('UPLOADPATHROOT', null) . '/';
    }

    //Return String - Download Path
    public function downloadPath()
    {
        return env('DOWNLOADPATHROOT', null);
    }


    //Upload Document
    public function documentUploadFile($file = null, $folder = 'documentUpload')
    {
        $uploadCompletePathName = $this->uploadPath() . $this->docFolder;
        $getArrayResponse = null;
        if ($file) {
            $getArrayResponse = $this->uploadAnyFile($file, $uploadCompletePathName, $maxFileSize = 10, $newExtension = null, $newRadFileName = true);
        }
        return ($getArrayResponse ? $getArrayResponse['newFileName'] : null);
    }

    public function getUserFileUploaded(Request $request)
    {
        Session::forget('getUserID');
        Session::put('getUserID', $request['getUserID']);

        return redirect()->back();
    }
}//end class
