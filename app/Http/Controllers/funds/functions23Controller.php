<?php


namespace App\Http\Controllers\funds;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;
use Session;

class functions23Controller extends Controller{

	 public function addLog($operation)
    {
        $ip = Request::ip();
        $url = Request::fullUrl();
        $userID = Auth::user()->id;
        $nowInNigeria = Carbon::now('Africa/Lagos');
        $cmpname = php_uname('a');
        $host = $_SERVER['HTTP_HOST'];
        DB::table('audit_log')->insert(
            ['comp_name' => $cmpname, 'user_id' => $userID, 'date' => $nowInNigeria, 'ip_addr' => $ip, 'operation' => $operation,
            'host' => $host, 'referer' => $url]);
        return;
    }
    
	public function getList($table){
		$list = DB::table($table)->get();
		return $list;
	}

	public function getsubmodule($category)
	{
		$list = DB::table('submodule')
		->where('moduleID', '=', $category)
		->get();
		return $list;
	}

	public function getTechStaff()
	{
		$list = DB::table('users')
		->where('user_type', '=', 'TECHNICAL')
		->get();
		return $list;
	}

	public function getData($username, $category, $module)
	{
			if($category == "" && $module == ""){
				$list = DB::Table('tbltechdocumentation')
				->where('tbltechdocumentation.developedby', $username)
				->leftjoin('module', 'tbltechdocumentation.categoryID', '=', 'module.moduleID')
				->leftjoin('submodule', 'tbltechdocumentation.moduleID', '=', 'submodule.submoduleID')
				->select('tbltechdocumentation.*', 'submodule.submodulename', 'module.modulename')
				->get();
				return $list;
			} else {
				$list = DB::Table('tbltechdocumentation')
				->where('tbltechdocumentation.developedby', $username)
				->where('tbltechdocumentation.categoryID', $category)
				->where('tbltechdocumentation.moduleID', $module)
				->leftjoin('module', 'tbltechdocumentation.categoryID', '=', 'module.moduleID')
				->leftjoin('submodule', 'tbltechdocumentation.moduleID', '=', 'submodule.submoduleID')
				->select('tbltechdocumentation.*', 'submodule.submodulename', 'module.modulename')
				->get();
				return $list;
			}		
	}

	public function getInfo($id)
	{
		$res = DB::table('tbltechdocumentation')
		->where('id', $id)
		->leftjoin('module', 'tbltechdocumentation.categoryID', '=', 'module.moduleID')
		->leftjoin('submodule', 'tbltechdocumentation.moduleID', '=', 'submodule.submoduleID')
		->select('tbltechdocumentation.*', 'module.modulename', 'submodule.submodulename')
		->get();
		return $res;
	}

	public function getDataModification($moduleID){
		$res = DB::table('tbltechdocumentationmodification')
		->where('moduleID', $moduleID)
		->get();
		return $res;
	}

}