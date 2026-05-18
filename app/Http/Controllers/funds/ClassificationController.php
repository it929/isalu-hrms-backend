<?php

namespace App\Http\Controllers\funds;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use Auth;
use DB;

class ClassificationController extends ParentController
{
    public function create()
   {
	    $data['classname'] = DB::table('tbladmincode')
			->select('codeID', 'addressName')
	    	->orderBy('addressName', 'Asc')
			->get();
   	    return view('classification.classcode', $data);
   }
   
   public function store(Request $request)
    { 
		$this->validate($request, [
			'name'     => 'required|string',
			'subhead'         => 'required|numeric',
			'classification'  => 'required|numeric',
		]);
		$addressName      = ($request['name']);
		$subhead          = trim($request['subhead']);
		$classification   = trim($request['classification']);
		$action           = trim($request['action']);
		$codeID           = trim($request['findRecord']);
		if (is_null(DB::table('tbladmincode')->where('codeID', '=', $codeID)->first())) {
			DB::table('tbladmincode')->insert(array( 
				'addressName' => $addressName,
				'subhead'     => $subhead, 
				'classcode'   => $classification	
			));
			$this->addLog('New classification code added');
			return back()->with('msg', 'New Classification Code successfully added!');
		}else{
			$values = array( 
				'addressName' => $addressName,
				'subhead'     => $subhead,
				'classcode'   => $classification
			);
			DB::table('tbladmincode')->where('codeID', '=', $codeID )->update($values);
			$this->addLog('New classification code updated');
			return back()->with('msg', 'New Classification Code successfully Updated!');

		}
    }

	public function findData(Request $request)
    {    	
    	$this->validate($request, [
    		'findRecord' => 'required|numeric',
    	]);
		$codeID = $request->input('findRecord');
		$data = DB::table('tbladmincode')
			  ->select('addressName', 'subhead', 'classcode')
			  ->where('codeID', '=', $codeID)
			  ->first();
		return response()->json($data);
    }
}