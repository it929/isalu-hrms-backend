<?php

namespace App\Http\Controllers\payroll;
use Session;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;
use DateTime;

class TechnicalDocumentationController extends functions23Controller
{
	public function index(Request $request)
	{

	$data['success']                = "";
        $data['error']                  = "";
        $data['categoryList']           = $this->getList('module');
        $data['modulelist']             = $this->getsubmodule($request['category']);
        $data['getedj']                 = [];
        $data['module']                 = $request['module'];
        $data['category']               = $request['category'];
        $data['superlist']              = $this->getTechStaff();
        $data['devby']                  = Auth::user()->username;
        $data['superby']                = $request['superby'];
        $data['duedate']              = $request['due-date'];
        $data['description']            = $request['description'];

        
        $data['tablecontent'] = $this->getData(Auth::user()->username, $request['category'], $request['module']);
        
        if($request['category'] != "" && $request['module'] != "" && $request['module'] != "All" && $request['devby'] != "" && $request['superby'] != "" && $request['due-date'] != "" && $request['due-date'] != "0000-00-00 00:00:00" && $request['description']){
                

                foreach($data as $key => $value){
                        $$key = $value;
                }

                $check = DB::table('tbltechdocumentation')
                ->where('categoryID', $category)
                ->where('moduleID', $module)
                ->get();

                if(!$check){
                        DB::table('tbltechdocumentation')
                        ->insert(
                                [
                                        'categoryID'    => $category,
                                        'moduleID'      => $module,
                                        'developedby'   => $devby,
                                        'supervisedby'  => $superby,
                                        'datecompleted' => $duedate,
                                        'description'  => $description
                                ]
                        );
                } else {
                        $data['error'] = "Cannot add to already existing description";
                }
        }
        if($request['deleting'] != ""){
                $check = DB::table('tbltechdocumentationmodification')
                ->where('moduleID', $request['deleting'])
                ->get();
                if($check){
                        $data['error'] = "Cannot delete this record, first delete all records in modification table!";
                } else {
                    DB::table('tbltechdocumentation')
                    ->where('id', $request['deleting'])
                    ->delete();
                }
                if($request['category2'] !== ""){
                        $request['category2'] = "";
                        $request['module2'] = "";

                }
                $data['category']       = $request['category2'];
                $data['module']         = $request['module2'];
        }
                
                if($request['editing'] != ""){
                        DB::table('tbltechdocumentation')
                        ->where('id', $request['editing'])
                        ->update(
                                ['description' => $request['description1']]
                        );

                        if($request['category1'] !== ""){
                                $request['category1'] = "";
                                $request['module1'] = "";
                        }
                        $data['category']       = $request['category1'];
                        $data['module']         = $request['module1'];
                }
         
        
                $data['fileNo']         = $request['fileNo'];
                
                $data['tablecontent']   = $this->getData(Auth::user()->username, $request['category'], $request['module']);
                $chk                    = [];
		return view('TechnicalDocumentation.technical', $data);
	}


        public function modify(Request $request, $id = "")
        {       
                if($id == ""){
                        return back()->withInput();
                }
                $data['success']                = "";
                $data['error']                  = "";
                $data['categoryList']           = $this->getList('module');
                $data['modulelist']             = $this->getsubmodule($request['category']);
                $data['getedj']                 = [];
                $data['module']                 = $this->getInfo($id)[0]->submodulename;
                $data['category']               = $this->getInfo($id)[0]->modulename;
                $data['titlen']                 = $this->getInfo($id)[0]->modulename .' - '. $this->getInfo($id)[0]->submodulename;;
                $data['devby']                  = $this->getInfo($id)[0]->developedby;
                $data['modby']                  = Auth::user()->username;
                $data['duedate']              = $request['due-date'];
                $data['description']            = $request['description'];

                if($request['category'] != "" && $request['module'] != "" && $request['devby'] != "" && $request['modby'] != "" && $request['due-date'] != "" && $request['description']){
                

                        foreach($data as $key => $value){
                                $$key = $value;
                        }

                        $check = DB::table('tbltechdocumentationmodification')
                        ->where('moduleID', $id)
                        ->where('datemodified', $duedate)
                        ->where('description', $description)
                        ->where('modifiedby', $modby)
                        ->get();

                        if(!$check){
                                DB::table('tbltechdocumentationmodification')
                                ->insert([
                                        'moduleID'      => $id,
                                        'description'   => $description,
                                        'datemodified'  => $duedate,
                                        'modifiedby'    => $modby

                                ]);
                        } else {
                                $data['error'] = "This exact modification already exists!";
                        }
                }

                if($request['deleting']){
                        DB::table('tbltechdocumentationmodification')
                            ->where('id', $request['deleting'])
                            ->delete();
                }
                $data['tablecontent'] = $this->getDataModification($id);
                return view('TechnicalDocumentation.modify', $data);
        }

        public function createcat(Request $request)
        {
                $data['tablecontent'] = [];
                $data['success']                = "";
                $data['error']                  = "";
                
                
                $data['getedj']                 = [];
                $data['category']               = $request['category'];               
                
                $data['addedby']                = Auth::user()->username;
                if($request['category'] != ""){
                        $check = DB::table('tblmodulecategory')
                                ->where('categoryname', $request['category'])
                                ->get();
                        if(!$check){
                                DB::table('tblmodulecategory')
                                ->insert([
                                        'categoryname' => $request['category'],
                                        'addedby'       => $request['addedby']
                                ]);
                        }
                }

                $data['tablecontent'] = DB::Table('tblmodulecategory')->get();
                return view('TechnicalDocumentation.create', $data);
        }

        public function addmodule(Request $request, $id = "")
        {
                $data['tablecontent'] = [];
                $data['success']                = "";
                $data['error']                  = "";
                $data['titlen']                 = DB::table('tblmodulecategory')->where('id', $id)->get()[0]->categoryname;
                
                
                $data['getedj']                 = [];
                $data['module']               = $request['module'];               
                
                $data['addedby']                = Auth::user()->username;
                if($request['module'] != ""){
                        $check = DB::table('tblmodule')
                                ->where('modulename', $request['module'])
                                ->get();
                        if(!$check){
                                DB::table('tblmodule')
                                ->insert([
	                                'categoryID'    => $id,
                                        'modulename' => $request['module'],
                                        'addedby'       => $request['addedby']
                                ]);
                        } else {
                                $data['error'] = "Module with exact name already exists!";
                        }
                }

                $data['tablecontent'] = DB::Table('tblmodule')->get();
                return view('TechnicalDocumentation.addmodule', $data);  
        }

        public function viewall(Request $request, $id ="", $cat = "", $mod = "")
        {
                $data['tablecontent'] = [];
                $data['success']                = "";
                $data['error']                  = "";
                $data['module']                 = $this->getInfo($id)[0]->submodulename;
                $data['category']               = $this->getInfo($id)[0]->modulename;
                $data['developedby']            = $this->getInfo($id)[0]->developedby;
                $data['date']                   = $this->getInfo($id)[0]->datecompleted;
                $data['desc']                   = $this->getInfo($id)[0]->description;
                $data['modifications']          = DB::table('tbltechdocumentationmodification')->where('moduleID', $id)->get();
                return view('TechnicalDocumentation.viewall', $data);
        }
}