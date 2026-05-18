<?php
namespace App\Http\Controllers\hr;
use App\Http\Requests;
use App\Role;
use App\User;
use Auth;
use Entrust;
use App\Permission;
use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\Controller;
use Session;

class BatNoController extends ParentController
{
/**
 * Create a new controller instance.
 *
 * @return void
 */
/**
 * Show the application dashboard.
 *
 * @return \Illuminate\Http\Response
 */
public $division; 
public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
  $this->division = $request->session()->get('division');
  $this->divisionID = $request->session()->get('divisionID');
            return $next($request);
        });
    }

public function create()
{
 $data['courts'] =  DB::table('tbl_court')->get();
      $courtSessionId = session('anycourt');
         $data['courtDivisions']  = DB::table('tbldivision')
         ->where('courtID', '=', $courtSessionId)
         ->get();

   
$data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}


   if(count($data['CourtInfo']) > 0)
      {
        

      }
      $data['bat']  = DB::table('tblbat')  
      ->paginate(36);

  return view('batNo.create',$data);    
}
public function store(Request $request)
{
 DB::table('tblbat')->insert(array( 
                'year'             => $request['year'],
                'month'            => $request['month'],
                'batNo'            => $request['batNo'],
                
            ));
            return redirect('/bat/create')->with('msg','successfully Added');

}

public function edit($id)
{
 $data['courts'] =  DB::table('tbl_court')->get();
      $courtSessionId = session('anycourt');
         $data['courtDivisions']  = DB::table('tbldivision')
         ->where('courtID', '=', $courtSessionId)
         ->get();

   
$data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

      $data['batSingle']  = DB::table('tblbat')->where('Id','=',$id) 
      ->first();
       $data['bat']  = DB::table('tblbat')  
      ->paginate(36);
  return view('batNo.edit',$data);    

}

public function update(Request $request)
{
 $id = $request['id'];
 DB::table('tblbat')->where('Id','=',$id)->update(array( 
                'year'             => $request['year'],
                'month'            => $request['month'],
                'batNo'            => $request['batNo'],
                
            ));
            return redirect('/bat/create')->with('msg','successfully Updated');

}

public function councilBatIndex()
{
     $data['courts'] =  DB::table('tbl_court')->get();
      $courtSessionId = session('anycourt');
         $data['courtDivisions']  = DB::table('tbldivision')
         ->where('courtID', '=', $courtSessionId)
         ->get();

   
$data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}


   if(count($data['CourtInfo']) > 0)
      {
        

      }
      $data['bat']  = DB::table('tblcouncil_bat')  
      ->paginate(36);

  return view('batNo.createCouncilBat',$data);
}

public function councilBatSave(Request $request)
{
 DB::table('tblcouncil_bat')->insert(array( 
                'year'             => $request['year'],
                'month'            => $request['month'],
                'batNo'            => $request['batNo'],
                
            ));
            return redirect('/council-bat/create')->with('msg','successfully Added');

}

public function councilBatEdit($id)
{
 $data['courts'] =  DB::table('tbl_court')->get();
      $courtSessionId = session('anycourt');
         $data['courtDivisions']  = DB::table('tbldivision')
         ->where('courtID', '=', $courtSessionId)
         ->get();

   
$data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

      $data['batSingle']  = DB::table('tblcouncil_bat')->where('Id','=',$id) 
      ->first();
       $data['bat']  = DB::table('tblcouncil_bat')  
      ->paginate(36);
  return view('batNo.editCouncilBat',$data);    

}

public function councilBatUpdate(Request $request)
{
 $id = $request['id'];
 DB::table('tblcouncil_bat')->where('Id','=',$id)->update(array( 
                'year'             => $request['year'],
                'month'            => $request['month'],
                'batNo'            => $request['batNo'],
                
            ));
            return redirect('/council-bat/create')->with('msg','successfully Updated');

}



}