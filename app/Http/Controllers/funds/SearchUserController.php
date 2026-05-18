<?php
namespace App\Http\Controllers\funds;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers;
use DB;

class SearchUserController extends Controller
{
  public function create()
  {
    return View('UserSearch.create');
  }

  
  public function autocomplete(Request $request)
  {
    $query = $request->input('query');
    $search = DB::table('tblper')->where('surname', 'LIKE', '%'.$query.'%')->
    orWhere('first_name', 'LIKE', '%'.$query.'%')->orWhere('fileNo', 'LIKE','%'.$query.'%')->take(15)->get();
    $return_array = null;
    foreach($search as $s)
    {
      $return_array[]  =  ["value"=>$s->surname.' '.$s->first_name.' - '.$s->fileNo, "data"=>$s->fileNo];
    }	
    return response()->json(array("suggestions"=>$return_array));
  }


  public function retrieve(Request $request)
  {
    $term=$request->input('nameID');
    $data = DB::table('tblper')
    ->where('tblper.fileNo', '=', $term)
    ->select('tblper.fileNo', 'tblper.surname', 'tblper.first_name', 'tblper.othernames', 'tblper.title', 'tblper.Designation', 'tblper.rank', 'tblper.grade', 'tblper.step', 'tblbanklist.bank','tblper.bankGroup', 'tblper.bank_branch', 'tblper.AccNo', 'tblper.section', 'tblper.appointment_date', 'tblper.dob', 'tblper.home_address', 'tblper.government_qtr', 'tblper.employee_type',
      'tblper.gender', 'tbldivision.division', 'tblper.current_state', 'tblper.incremental_date', 'nhfNo')
    ->join('tbldivision', 'tblper.divisionID', '=', 'tbldivision.divisionID')
    ->join('tblbanklist', 'tblper.bankID', '=', 'tblbanklist.bankID')
    ->first();
    return response()->json($data);
  }

}
