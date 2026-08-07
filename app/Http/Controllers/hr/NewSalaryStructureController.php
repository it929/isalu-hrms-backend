<?php

namespace App\Http\Controllers\hr;
use App\Http\Controllers\payroll\ParentController;
use Illuminate\Http\Request;
use Session;
use App\Http\Requests;
use DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class NewSalaryStructureController extends ParentController
{
   
    public function index()
    {
        //
    }

   
    public function create(Request $request)
    {
       $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}

      $data['emptype'] = DB::table('tblemployment_type')->get();
        //$data['count'] = '';
        $data['verify'] = '';
        $data['courts'] = DB::table('tbl_court')->get();
         //$data['scale'] ='';
          $data['scale'] = DB::table('basicsalaryconsolidated_new')
             ->where('courtID','=',session('court'))
             ->where('grade','=',session('grade'))
             ->where('step','=',session('step'))
             ->where('employee_type','=',session('employeeType'))
             ->first(); 

             //dd()
        return view('newSalaryStructure/newStructure',$data);
    }

    public function display(Request $request)
    {
        $data['courts'] = DB::table('tbl_court')->get();
        $btn                   = $request['submit'];
        $court                 = $request['court'];
        $grade                 = $request['grade'];
        $step                  = $request['step'];
        $empType               = $request['employeeType'];

       $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}


         $request->session()->forget(['court', 'step', 'employeeType', 'grade']);
         
        $data['emptype'] = DB::table('tblemployment_type')->get();

        $data['count'] = DB::table('basicsalaryconsolidated_new')
             ->where('courtID','=',$court)
             ->where('grade','=',$grade)
             ->where('step','=',$step)
             ->where('employee_type','=',$empType)
             ->count(); 
             //dd($data['count']);

             if($data['count'] == 0)
             {
                //$data['verify'] = 'add_new';
               Session::put('verify', 'add_new');

             } 
             elseif($data['count'] > 0)
             {
                //$data['verify'] = 'update';
                 Session::put('verify', 'update');
             }

             //dd($data['verify']);
        
         if($btn == 'Display')
         {
            
        Session::put('court', $court);
        Session::put('step', $step);
        Session::put('employeeType', $empType);
        Session::put('grade', $grade);
              $data['emptype'] = DB::table('tblemployment_type')->get();
              
             $data['scale'] = DB::table('basicsalaryconsolidated_new')
             ->where('courtID','=',$court)
             ->where('grade','=',$grade)
             ->where('step','=',$step)
             ->where('employee_type','=',$empType)
             ->first();
 
             return view('newSalaryStructure/newStructure',$data);
         }
    }


    

    public function saveSalary(Request $request)
    {

        $this->validate($request, [
            'basic'         => 'regex:/\b\d{1,3}(?:,?\d{3})*(?:\.\d{2})?\b/',
            //'leaveBonus'      => 'regex:/\b\d{1,3}(?:,?\d{3})*(?:\.\d{2})?\b/',
            'peculiar'         => 'regex:/\b\d{1,3}(?:,?\d{3})*(?:\.\d{2})?\b/',
            //'hoiusing'      => 'regex:/\b\d{1,3}(?:,?\d{3})*(?:\.\d{2})?\b/',
            //'transport'     => 'regex:/\b\d{1,3}(?:,?\d{3})*(?:\.\d{2})?\b/',
            //'utility'       => 'regex:/\b\d{1,3}(?:,?\d{3})*(?:\.\d{2})?\b/',
            //'furniture'     => 'regex:/\b\d{1,3}(?:,?\d{3})*(?:\.\d{2})?\b/',
            //'meal'      => 'regex:/\b\d{1,3}(?:,?\d{3})*(?:\.\d{2})?\b/',
            //'driver'        => 'regex:/\b\d{1,3}(?:,?\d{3})*(?:\.\d{2})?\b/',
            'pension'       => 'regex:/\b\d{1,3}(?:,?\d{3})*(?:\.\d{2})?\b/',
            'nhf'           => 'regex:/\b\d{1,3}(?:,?\d{3})*(?:\.\d{2})?\b/',
            'tax'       => 'regex:/\b\d{1,3}(?:,?\d{3})*(?:\.\d{2})?\b/',
            'unionDues'     => 'regex:/\b\d{1,3}(?:,?\d{3})*(?:\.\d{2})?\b/',
            //'servant'       => 'regex:/\b\d{1,3}(?:,?\d{3})*(?:\.\d{2})?\b/',

            'employeeType'     => 'required|string',
            'grade'            => 'required|numeric',
            'step'             => 'required|numeric',
            'court'           => 'required|numeric',
        ]);  

$data['emptype'] = DB::table('tblemployment_type')->get();

        $data['courts'] = DB::table('tbl_court')->get();
        $btn                   = $request['submit'];
        $court                 = $request['court'];
        $grade                 = $request['grade'];
        $step                  = $request['step'];
        $empType               = $request['employeeType'];

       
        $basic                    = $request['basic'];
        //$leaveBonus               = $request['leaveBonus'];
        $peculiar                 = $request['peculiar'];
        //$housing                  = $request['housing'];
        //$transport                = $request['transport'];

        //$utility                  = $request['utility'];
        //$furniture                = $request['furniture'];
        //$meal                     = $request['meal'];
        //$driver                   = $request['driver'];
        $tax                      = $request['tax'];
        $pension                  = $request['pension'];
        $nhf                      = $request['nhf'];
        $union                    = $request['unionDues'];
        //$servant                  = $request['servant'];

        $id                       = $request['id'];
        /*$employeeType             =  $request->session()->get('employeeType');
        $grade                    =  $request->session()->get('grade');
        $step                     =  $request->session()->get('step');
        $court                    =  $request->session()->get('court');
        */
        $date                     = date('Y-m-d');

         $data['CourtInfo']=$this->CourtInfo();
      if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
      if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}


         if($btn == 'Display')
         {
         $data['emptype'] = DB::table('tblemployment_type')->get();

           
        Session::put('court', $court);
        Session::put('step', $step);
        Session::put('employeeType', $empType);
        Session::put('grade', $grade);

        $data['count'] = DB::table('basicsalaryconsolidated_new')
             ->where('courtID','=',$court)
             ->where('grade','=',$grade)
             ->where('step','=',$step)
             ->where('employee_type','=',$empType)
             ->count(); 
             //dd($data['count']);

             if($data['count'] == 0)
             {
                //$data['verify'] = 'add_new';
               Session::put('verify', 'add_new');

             } 
             elseif($data['count'] > 0)
             {
                //$data['verify'] = 'update';
                 Session::put('verify', 'update');
             }

             //dd($data['verify']);
          $data['scale'] = DB::table('basicsalaryconsolidated_new')
             ->where('courtID','=',$court)
             ->where('grade','=',$grade)
             ->where('step','=',$step)
             ->where('employee_type','=',$empType)
             ->first();

             //dd(session('court'));

             return view('newSalaryStructure/newStructure',$data);
         
        }


    else
    {
        //dd( $court);

        if($id != '')
       {
       DB::table('basicsalaryconsolidated_new')->where('ID', $id)->update(array(  
         'amount'             => $basic, 
        'employee_type'      => $empType,
        'courtID'            => $court,
        'grade'              => $grade,
        'step'               => $step, 
        'tax'                => $tax, 
        //'servant'            => $servant,
        //'meal'               => $meal, 
        //'driver'             => $driver, 
        //'housing'            => $housing, 
        //'transport'          => $transport, 
        //'utility'            => $utility, 
        //'furniture'          => $furniture, 
        'peculiar'           => $peculiar, 
        //'leave_bonus'        => $leaveBonus, 
        'pension'            => $pension, 
        'nhf'                => $nhf, 
        'unionDues'          => $union, 
        'date'               => $date,
       ));
       // $values = array($basic,$leaveBonus,$peculiar,$housing,$transport,$utility,$furniture,$meal,$driver,$tax,$pension,$nhf,$union,$servant);
        return redirect('/new-salary/structure')->with('msg','updated Successfully');

       }
       elseif($id == '')
       {
       $insert = DB::table('basicsalaryconsolidated_new')->insert(array(  
        'amount'             => $basic, 
        'employee_type'      => $empType,
        'courtID'            => $court,
        'grade'              => $grade,
        'step'               => $step, 
        'tax'                => $tax, 
        //'servant'            => $servant,
        //'meal'               => $meal, 
        //'driver'             => $driver, 
        //'housing'            => $housing, 
        //'transport'          => $transport, 
        //'utility'            => $utility, 
        //'furniture'          => $furniture, 
        'peculiar'           => $peculiar, 
        //'leave_bonus'        => $leaveBonus, 
        'pension'            => $pension, 
        'nhf'                => $nhf, 
        'unionDues'          => $union, 
        'date'               => $date,
       ));

       if($insert)
       {
        Session::put('verify', 'update');
       }
        return redirect('/new-salary/structure')->with('msg','Added Successfully');
       }

   }
    }
    
    public function viewStructure()
    {
        $data['CourtInfo']=$this->CourtInfo();
        if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
        if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
        $data['emptype'] =  DB::table('tblemployment_type')->where('active','=',1)->get();
        return view('newSalaryStructure.viewStructure',$data);
    }
    
    public function customPaging($type, Request $request){
    $tableName = "basicsalaryconsolidated_new";
    $employee_type = $type;
    $data['employee_type']=strtoupper($employee_type);
    $grade = "";
    $grade = $request->get('page');
    if(is_null($grade))
    {
        $grade=1;
    }
    $data['CourtInfo']=$this->CourtInfo();
    if($data['CourtInfo']->courtstatus==0){$request['court']=$data['CourtInfo']->courtid;}
    if($data['CourtInfo']->divisionstatus==0){$request['division']=$data['CourtInfo']->divisionid;}
    $data['current_grade'] = $grade;
    $data['report'] = DB::table($tableName)->where('employee_type', '=', $employee_type )
    ->where('courtID', '=',  $data['CourtInfo']->courtid )
    ->where('grade', '=',$grade )
    ->orderby('step')->get();
    $searchResults = DB::table('basicsalaryconsolidated_new')     
    ->select('grade')
    ->distinct()
    ->get();
    //Get current page form url e.g. &page=6
    $currentPage = LengthAwarePaginator::resolveCurrentPage();
    //Create a new Laravel collection from the array data
    $collection = new Collection($searchResults);
    //Define how many items we want to be visible in each page
    $perPage = 1;
    //Slice the collection to get the items to display in current page
    $currentPageSearchResults = $collection->slice (($currentPage ) * $perPage, $perPage)->all();
    //$currentPageSearchResults = $collection->slice($currentPage * $perPage, $perPage)->all();
    //Create our paginator and pass it to the view       
    $paginatedSearchResults= new LengthAwarePaginator($searchResults, count($collection)+2, $perPage,Paginator::resolveCurrentPage(),['path' => Paginator::resolveCurrentPath()]
        );
    return view('newSalaryStructure.newScaleView', ['results' => $paginatedSearchResults],$data);
}

   

    public function update(Request $request, $id)
    {
        //
    }

    
    public function destroy($id)
    {
        //
    }
}
